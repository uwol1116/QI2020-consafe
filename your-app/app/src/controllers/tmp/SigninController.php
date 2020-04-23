<?php
namespace App\Controller;

error_reporting(E_ALL);
ini_set("display_errors", 1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class SigninController extends BaseController{

	public function signin(Request $request, Response $response, $args){
		$this->view->render($response,'signin.twig');
		return $response;
	}

	

	//세션 생성 추가해야함
	public function handlesignin(Request $request, Response $response, $args){
		$Email = $_POST['email'];
		$Password = $_POST['password'];
		
		//가입 여부 확인
		$sql= "select usn,hpassword from user where email='".$Email."'";	
		$stmt = $this->em->getConnection()->query($sql);
		$result = $stmt->fetchAll();
		
		if(empty($result)){
			echo "<script>alert('Sign up first');</script>";
		}
		
		else{
		         //flag bit 확인
			$fbsql= "select fb from user where (email='".$Email."')";       
                        $fbstmt = $this->em->getConnection()->query($fbsql);
                        $fbresult = $fbstmt->fetchColumn();
			//password 확인 
                         if($fbresult == 1){
                                if (password_verify($Password,$result[0]['hpassword'])){
                                        echo "valid"; 
				}
                                else {  
                                        echo "invalid"; 
				}
			}
			else{
				echo"<script>alert('Activate your account first');</script>";
			}

		}
		
		//exit;
	}	
}

