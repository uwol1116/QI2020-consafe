<?php
namespace App\Controller;

error_reporting(E_ALL);
ini_set("display_errors", 1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class UserController extends BaseController{

	//sign in

	public function signin(Request $request, Response $response, $args){
		$this->view->render($response,'signin.twig');
		return $response;
	}

	

	//세션 생성 추가해야함
	public function handlesignin(Request $request, Response $response, $args){
		$Email = $_POST['email'];
		$Password = $_POST['password'];
		
		//가입 여부 확인
		$sql= "select * from user where email='".$Email."'";	
		$stmt = $this->em->getConnection()->query($sql);
		$result = $stmt->fetchAll();
		
		if(empty($result)){
			$this->view->render($response,'cantfindid.twig');
			return $response;
		}
		
		else{
		        //flag bit 확인
			$fbsql= "select fb from user where (email='".$Email."')";       
                        $fbstmt = $this->em->getConnection()->query($fbsql);
                        $fbresult = $fbstmt->fetchColumn();
			
			//password 확인 
                        if($fbresult == 1){
                                if (password_verify($Password,$result[0]['hpassword'])){
					$_SESSION['usn'] = $result[0]['usn'];
                        		return $response->withRedirect('/');

				}
                                 else {  
					$this->view->render($response,'cantfindid.twig');
					return $response;
				}
			}
			else{
				$this->view->render($response,'activateaccount.twig');
				return $response;
			}

		}
	}

	//sign out
	public function signout(Request $request, Response $response, $args){
		unset( $_SESSION['usn'] );	
                return $response->withRedirect('/');
        }

	//sign up
	public function signup(Request $request, Response $response, $args){
		$this->view->render($response,'signup.twig');
		return $response;
	}

	public function handlesignup(Request $request, Response $response, $args){
		$Email = $_POST['email'];
		$Password = $_POST['password'];
		$Name = $_POST['name'];
		$Company = $_POST['company'];
		$Hashed_Password = password_hash($Password,PASSWORD_DEFAULT);
		$Date = date("YmdHis");
		$Nonce = hash('sha256', $Email.$Date);
		$Flag = 0;

		//email 존재하는지 확인
		$sql= "select * from user where email='".$Email."'";	
		$stmt = $this->em->getConnection()->query($sql);
		$result = $stmt->fetchAll();
		if($result){
			echo "<script>alert('E-mail Duplication')";
			//회원가입 페이지로 빠꾸
			return $response->withRedirect('/signup');
		}

		else{
			//정보 db에 저장
			$sql = "insert into user (email,hpassword,name,company,nonce,fb) values ('".$Email."','".$Hashed_Password."','".$Name."','".$Company."','".$Nonce."','".$Flag."')";
			$stmt = $this->em->getConnection()->query($sql);
			//email 보내는 페이지로 이동			
			$link = base64_encode("teamd-iot.calit2.net/verifymail/".$Nonce);
			$redirect = "/sendmail/".$Email."/".$link."/1";
			return $response->withRedirect($redirect);
		}	
	}
	
	// changepassword -> 유저가 바꾸길 원할 떄, 이름 혼동 주의 
	public function changepassword(Request $request, Response $response, $args){
	        $this->view->render($response,'changepassword.twig');
                return $response;
	}

	public function handlechangepassword(Request $request, Response $response, $args){
		$USN = $_SESSION['usn'];
		$Password = $_POST['password'];
		$NewPassword = $_POST['newpassword'];
	
		$sql = "select hpassword from user where usn=".$USN;
		$stmt = $this->em->getConnection()->query($sql);
		$result = $stmt->fetchColumn();
	
		if(password_verify($Password,$result)){
			$Hpassword = password_hash($NewPassword,PASSWORD_DEFAULT);
			$newsql = "update user set hpassword = '".$Hpassword."' where usn=".$USN;
			$newstmt = $this->em->getConnection()->query($newsql);
			unset($_SESSION['usn']);
			$this->view->render($response,'changesuccess.twig');
			return $response;
		}
			
		else{
			$this->view->render($response,'cantfindpw.twig');
			return $response;
		}
	}
	

	//프로필 페이지
	//device 정보 출력 부분 추가 예정
	public function profile(Request $request, Response $response, $args){
		$USN = $_SESSION['usn'];
		
		$sql = "select * from user where usn='".$USN."'";
		$stmt = $this->em->getConnection()->query($sql);
		$result = $stmt->fetchAll();
		
		$Email = $result[0]['email'];
		$Name = $result[0]['name'];
		$Company = $result[0]['company'];
	
		$sql2 = "select * from sensorlist where usn='".$USN."'";
		$stmt2 = $this->em->getConnection()->query($sql2);
		$result2 = $stmt2->fetchAll();

		$this->view->render($response,'profile.twig',['email' => $Email, 'name' => $Name, 'company' => $Company,'sensor'=>$result2]);
		return $response;

	}
		
	public function removeaccount(Request $request, Response $response,$args){
		$this->view->render($response,'inputpassword.twig');
		return $response;	
		
	}
		
	public function handleremoveaccount (Request $request, Response $response, $args){
		$USN = $_SESSION['usn'];
		$Password = $_POST['password'];

                $sql = "select hpassword from user where usn=".$USN;
                $stmt = $this->em->getConnection()->query($sql);
                $result = $stmt->fetchColumn();

                if(password_verify($Password,$result)){
                        $newsql = "update user set email='NULL',hpassword='NULL',name='NULL',company='NULL',nonce='NULL',fb=0 where usn =".$USN."" ;
                        $newstmt = $this->em->getConnection()->query($newsql);
                        
			unset($_SESSION['usn']);
			$this->view->render($response,'removesuccess.twig');
                        return $response;
                }

                else{
                        echo"<script>alert('wrong password');</script>";
                        return $response->withRedirect('/removeaccount');
                }	
	}

	public function editprofile(Request $request, Response $response, $args){
		$usn = $_SESSION['usn'];        

                $sql = "select * from user where usn='".$usn."'";        
                $stmt = $this->em->getConnection()->query($sql);
                $result = $stmt->fetchAll();

		$this->view->render($response,'editprofile.twig',['name'=>$result[0]['name'],'email'=>$result[0]['email'],'company'=>$result[0]['company']]);
		return $response;
	}

	public function storeprofile(Request $request, Response $response, $args){
		$usn = $_SESSION['usn'];			
		$name=  $_POST['name'];
		$company = $_POST['company'];
		$email = $_POST['email'];	
		
		$sql = "update user set name='".$name."',company='".$company."'";
		$stmt = $this->em->getConnection()->query($sql);	

		$sql2 = "select * from sensorlist where usn='".$usn."'";
                $stmt2 = $this->em->getConnection()->query($sql2);
                $result2 = $stmt2->fetchAll();
		
		$this->view->render($response,'profile.twig',['name'=>$name,'company'=>$company,'email'=>$email,'sensor'=>$result2]);
		return $response;
		
		
	}
	
}

