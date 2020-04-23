<?php
namespace App\Controller;

error_reporting(E_ALL);
ini_set("display_errors", 1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class SignupController extends BaseController{

	public function signup(Request $request, Response $response, $args){
		$this->view->render($response,'signup.twig');
		return $response;
	}

	

	//이메일 보내는 코드 추가해야함
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
		exit;
	}
}
