<?php
namespace App\Controller;

error_reporting(E_ALL);
ini_set("display_errors", 1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

final class MailController extends BaseController{
	
	//forgotten password viewing
	public function findaccount(Request $request, Response $response, $args){
		$this->view->render($response,'forgotten_findaccount.twig');
		return $response;
	}
	//forgotten password 처리하는 부분
	public function handlefindaccount(Request $request, Response $response, $args){
		$Email = $_POST['email'];
		$sql = "select * from user where email='".$Email."'";
		$stmt = $this->em->getConnection()->query($sql);
		$result = $stmt->fetchAll();
		if($result){
			$Date = date("YmdHis");
			$Nonce = hash('sha256',$Email.$Date);
			$noncesql = "update user set nonce='".$Nonce."' where email='".$Email."'";
			$noncestmt = $this->em->getConnection()->query($noncesql);
		        $link = base64_encode("teamd-iot.calit2.net/passwordchange/".$Nonce);
                        $redirect = "/sendmail/".$Email."/".$link."/2";
                        return $response->withRedirect($redirect);
		}
		else{
			echo"<script>alert('sign up first'); location.href='/signup'</script>";
		}
		
	}

	public function passwordchange(Request $request, Response $response, $args){
		$Nonce = $args['nonce'];
		$this->view->render($response,'forgotten_passwordchange.twig',['nonce'=>$Nonce]);
		return $response;
	}	

	public function updateaccount(Request $request, Response $response, $args){
		$Nonce = $_POST['nonce'];
		$Hashed_Password = password_hash($_POST['password'],PASSWORD_DEFAULT);
		//nonce check	
		$sql = "select * from user where nonce='".$Nonce."'";
		$stmt = $this->em->getConnection()->query($sql);
		$result = $stmt->fetchColumn();
		
		//password update
		if($result){
			$sql = "update user set nonce=NULL,hpassword='".$Hashed_Password."' where nonce='".$Nonce."'";
			$stmt = $this->em->getConnection()->query($sql);
			echo "<script>alert('success');</script>";	
			$this->view->render($response,'signin.twig');
			return $response;
		}	
		else{
			echo "<script>alert('Wrong access');</script>";
		}

	}
	
	//sendmail
	//flag 1. signup 2. forgotten password
	public function sendmail(Request $request, Response $response, $args){
		$mail = new PHPMailer(true);	
		$Usermail = $args['id'];
		$Userlink = base64_decode($args['link']);
		$flag = $args['flag'];
		
		if($flag == 1){
			$mailsubject = 'Thank you for signing up';
			$mailtext = '<table border = "0">';
			$mailtext .= '<tr><td><h1>Thank you for your attention</h1></td><td><img alt="PHPMailer" src="cid:logo" width="300" height="140" align="right" ></td></tr>';
			$mailtext .= '<tr><td colspan="2">To verify your email address, <strong>please click the following link</strong></td></tr>';
			$mailtext .= '<tr><td colspan="2"><a href="'.$Userlink.'">'.$Userlink.'</a></td></tr>';
			$mailtext .= '</table>';
		
		}
		else if($flag == 2){
                        $mailsubject = 'Please verify your email to change your password';
                        $mailtext = '<table border = "0">';
                        $mailtext .= '<tr><td><h1>Thank you for your attention</h1></td><td><img alt="PHPMailer" src="cid:logo" width="300" height="140" align="right" ></td></tr>';
                        $mailtext .= '<tr><td colspan="2"><strong>Click on the link</strong> to change your password</td></tr>';
                        $mailtext .= '<tr><td colspan="2"><a href="'.$Userlink.'">'.$Userlink.'</a></td></tr>';
                        $mailtext .= '</table>';

		}

		try {
 			//Server settings
  			//$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
    			$mail->isSMTP();                                            // Send using SMTP
    			$mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
    			$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    			$mail->Username   = 'iotteamd@gmail.com';                     // SMTP username
    			$mail->Password   = 'teamdiot2020';                               // SMTP password
    			$mail->SMTPSecure = 'PHPMailer::ENCRYPTION_STARTTLS';         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
    			$mail->Port       = 587;                                    // TCP port to connect to
		
			//Recipients
			$mail->setFrom('iotteamd@gmail.com', 'Consafe');
			$mail->addAddress($Usermail);               // Name is optional
			
			$mail->AddEmbeddedImage("/var/www/teamd-iot/your-app/public/assets/images/real.png", "logo", "real.png");
			// Content
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = $mailsubject;
			$mail->Body = $mailtext;
			$mail->send();
			
			$this->view->render($response,'emailsuccess.twig');
               		return $response;
		
		}	
		catch (Exception $e) {
	    		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		}
	}
	
	//signup check email
	public function verifymail(Request $request, Response $response, $args){
		$Nonce = $args['nonce'];
		$sql = "select * from user where nonce='".$Nonce."'";
		$stmt = $this->em->getConnection()->query($sql);
		$result = $stmt->fetchColumn();

		if($result){
			//fb set=1, delete nonce
			$sql = "update user set fb='1',nonce=NULL where nonce='".$Nonce."'";
			$stmt = $this->em->getConnection()->query($sql);
			echo "<script> alert('E-mail activated.</script>";
			return $response->withRedirect('/signin');
		}
	
		else{
			echo "<script>alert('Wrong access');</script>";
		}
	}
}
