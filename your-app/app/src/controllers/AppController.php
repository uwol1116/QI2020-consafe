<?php
namespace App\Controller;

error_reporting(E_ALL);
ini_set("display_errors", 1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

final class AppController extends BaseController{

        public function appsignin(Request $request, Response $response, $args){
		$Email = $_POST['email'];
		$Password = $_POST['password'];
		
		//가입 여부 확인
		$sql= "select * from user where email='".$Email."'";	
		$stmt = $this->em->getConnection()->query($sql);
		$result = $stmt->fetchAll();
		
		if(empty($result)){
			$response = array();
			$response['success'] = false;
			echo json_encode($response);	
		}
		
		else{
		        //flag bit 확인
			$fbsql= "select fb from user where (email='".$Email."')";       
                        $fbstmt = $this->em->getConnection()->query($fbsql);
                        $fbresult = $fbstmt->fetchColumn();
			
			//password 확인 
                        if($fbresult == 1){
				$response = array();
                                if (password_verify($Password,$result[0]['hpassword'])){
                                	$response["success"] = true;
					$response["userUSN"] = $result[0]['usn'];
                                	$response["userID"] = $Email;
                                	$response["userPass"] = $Password;
					$response["userName"] = $result[0]['name'];
					$response["userCompany"] = $result[0]['company'];
					echo json_encode($response); 			
				}
			}  else {
				//일치 X
        	                $response = array();
	                        $response['success'] = false;
	                        echo json_encode($response);

			}
		}
        }
	
	public function appsignup(Request $request, Response $response, $args){
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
			$response = array();
			$response["success"] = false;
			echo json_encode($response);	
		}

		else{
			//정보 db에 저장
			$sql = "insert into user (email,hpassword,name,company,nonce,fb) values ('".$Email."','".$Hashed_Password."','".$Name."','".$Company."','".$Nonce."','".$Flag."')";
			$stmt = $this->em->getConnection()->query($sql);
			
			//email 보내는 페이지로 이동			
			//안드로이드 경로 수정 필요
			$link = base64_encode("teamd-iot.calit2.net/verifymail/".$Nonce);
			$redirect = "/appsendmail/".$Email."/".$link."/1";
			return $response->withRedirect($redirect);
		}	
		exit;
	}
	
	public function appfindaccount(Request $request, Response $response, $args){
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
                        $redirect = "/appsendmail/".$Email."/".$link."/2";
                        return $response->withRedirect($redirect);
		}
		else{
		// 결과 없는 로직 추가 필요
		}
	}

        public function appchangepassword(Request $request, Response $response, $args){
                $USN = $_POST['usn'];
                $Password = $_POST['password'];
                $NewPassword = $_POST['newpassword'];

                $sql = "select hpassword from user where usn=".$USN;
                $stmt = $this->em->getConnection()->query($sql);
                $result = $stmt->fetchColumn();

		$response = array();
                
		if(password_verify($Password,$result)){
                        $Hpassword = password_hash($NewPassword,PASSWORD_DEFAULT);
                        $newsql = "update user set hpassword = '".$Hpassword."' where usn=".$USN;
                        $newstmt = $this->em->getConnection()->query($newsql);
		
                        $response['success'] = true;
                }

                else{
                       	$response['success'] = false;
                }
		
        	echo json_encode($response);

        }


	//sendmail
	//flag 1. signup 2. forgotten password
	public function appsendmail(Request $request, Response $response, $args){
		$mail = new PHPMailer(true);	
		$Usermail = $args['id'];
		$Userlink = base64_decode($args['link']);
		$flag = $args['flag'];
		$response = array();
		
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
			
                    	$response["success"] = true;
                        echo json_encode($response); 
		}	
		catch (Exception $e) {
                    	$response["success"] = false;
                        echo json_encode($response); 
		}
	}


        public function appremoveaccount(Request $request, Response $response, $args){
                $USN = $_POST['usn'];
                $Password = $_POST['password'];

                $sql = "select hpassword from user where usn=".$USN;
                $stmt = $this->em->getConnection()->query($sql);
                $result = $stmt->fetchColumn();
	
		$response = array();
                if(password_verify($Password,$result)){
                        $newsql = "update user set email='NULL',hpassword='NULL',name='NULL',company='NULL',nonce='NULL',fb=0 where usn =".$USN."" ;
                        $newstmt = $this->em->getConnection()->query($newsql);
			$response["success"] = true;
                }

                else{
			$response["success"] = false;
                }
		echo json_encode($response);
        }
	
	public function appregisensor(Request $request, Response $response, $args){
		$USN = $_POST['usn'];
		$Type = $_POST['type'];
		$Name = $_POST['name'];
		$Mac = $_POST['mac'];
	
		//이미 등록된 센서인지 확인	
		$sql = "select ssn from sensorlist where mac='".$Mac."'";
		$stmt = $this->em->getConnection()->query($sql);
		$result = $stmt->fetchColumn();

		$response = array();
		if(empty($result)){
			//할당될 ssn 확인
			$sql2 = "select auto_increment from information_schema.tables where table_name='sensorlist' and table_schema=database()";
			$stmt2 = $this->em->getConnection()->query($sql2);
			$result2 = $stmt2->fetchColumn();
			

			//센서등록
			//$sql3 = "insert into sensorlist (usn,type,name,mac) values (".$USN.",'".$Type."','".$Name."','".$Mac."')";
			$sql3 = "insert into sensorlist values (".$result2.",".$USN.",'".$Type."','".$Name."','".$Mac."','0')";
			$stmt3 = $this->em->getConnection()->query($sql3);
			
			$response["success"] = true;
			$response["ssn"] = $result2;

		} else{	
			$sql2 = "update sensorlist set usn='".$USN."',name='".$Name."' where ssn=".$result;
			$stmt2=$this->em->getConnection()->query($sql2);
			
			$response["success"] = false;
			$response["ssn"] = $result;
		}

		echo json_encode($response);
	}
	
	public function appconnect(Request $request, Response $response,$args){
		$SSN = $_POST['ssn'];
	
		$sql = "update sensorlist set flag = '1' where ssn=".$SSN;
		$stmt = $this->em->getConnection()->query($sql);
		
		$response = array();
		$response["success"] = true;
		echo json_encode($response);

	}	
	
	public function appdisconnect(Request $request, Response $response,$args){
		$SSN = $_POST['ssn'];
		
		$sql = "update sensorlist set flag = '0' where ssn=".$SSN;
		$stmt = $this->em->getConnection()->query($sql);
		
		$response = array();
		$response["success"] = true;
		echo json_encode($response);
	}


	public function storepolar(Request $request, Response $response, $args){
		$ssn = $_POST['ssn'];
		$hr = $_POST['hr'];
		$lat = $_POST['latitude'];
		$lon = $_POST['longitude'];
		$time = $_POST['time'];
	
		$sql = "select flag from sensorlist where ssn=".$ssn."";
		$stmt = $this->em->getConnection()->query($sql);
		$result = $stmt->fetchColumn();

		
		if(!empty($result)){
			$sql = "insert into polar (ssn,hr,latitude,longitude,time) values (".$ssn.",".$hr.",'".$lat."','".$lon."','".$time."')";
			$stmt = $this->em->getConnection()->query($sql);
		
			$response = array();
			$response["success"] = true;
			echo json_encode($response);
		}
		
	}
	
	public function polarhistory(Request $request, Response $response, $args){
		$ssn = $_POST['ssn'];
		$time = $_POST['time'];

		$sql = "select * from polar where ssn=".$ssn." and DATE(time)='".$time."'";
		$stmt = $this->em->getConnection()->query($sql);	
		$result = $stmt->fetchAll();

		$response = array();
		$response["history"] = $result;	
		echo json_encode($response);
	}

	
	public function boardhistory(Request $request, Response $response, $args){
		$usn = $_POST['usn'];
		$time = $_POST['time'];

		$sql = "select * from board where DATE(time)='".$time."'";
		$stmt = $this->em->getConnection()->query($sql);
		$result = $stmt->fetchAll();

		$sql2 = "select * from sensorlist where usn=".$usn."";
		$stmt2 = $this->em->getConnection()->query($sql2);
		$result2 = $stmt2->fetchAll();

		$response=array();
		$response["history"] = $result;	
		$response["mydevice"] = $result2;
		echo json_encode($response);
	}

	public function storeboard(Request $request, Response $response, $args){
		$ssn = $_POST['ssn'];
		$lat = $_POST['latitude'];
		$lng = $_POST['longitude'];
		$time = $_POST['time'];
		$temp = $_POST['temp'];
		$no2 = $_POST['no2'];
		$o3 = $_POST['o3'];
		$co = $_POST['co'];
		$so2 = $_POST['so2'];
		$pm = $_POST['pm'];
		$rawno2 = $_POST['rawno2'];
		$rawo3 = $_POST['rawo3'];
		$rawco = $_POST['rawco'];
		$rawso2 = $_POST['rawso2'];
		$rawpm = $_POST['rawpm'];
		
		$sql = "insert into board (ssn,latitude,longitude,time,temp,no2,o3,co,so2,pm,rawno2,rawo3,rawco,rawso2,rawpm) values(".$ssn.",'".$lat."','".$lng."','".$time."',".$temp.",".$no2.",".$o3.",".$co.",".$so2.",".$pm.",".$rawno2.",".$rawo3.",".$rawco.",".$rawso2.",".$rawpm.")";
		$stmt = $this->em->getConnection()->query($sql);
		
		$response = array();
		$response["success"] = true;
		echo json_encode($response);
	}
	
	public function realview(Request $request, Response $response, $args){
		$ssn = $_POST['ssn'];
	
		$sql = "select * from board where ssn='".$ssn."' order by bsn desc limit 1";
		$stmt = $this->em->getConnection()->query($sql);
		$result = $stmt->fetchAll();

		$response = array();
		$response["temp"] = $result[0]['temp'];
		$response["no2"] = $result[0]['rawno2'];
		$response["o3"] = $result[0]['rawo3'];
		$response["co"] = $result[0]['rawco'];
		$response["so2"] = $result[0]['rawso2'];
		$response["pm"] = $result[0]['rawpm'];
		echo json_encode($response);
	}
	
	public function sensorssn (Request $request, Response $response, $args){
		$usn = $_POST['usn'];
		
		$sql = "select ssn,name from sensorlist where type='0' and usn='".$usn."'";
		$stmt = $this->em->getConnection()->query($sql);
		$result = $stmt->fetchAll();

		$response = array();
		$response["sensor"] = $result;
		echo json_encode($response);
	}
	
	public function test(Request $request, Response $response, $args){
		$this->view->render($response,'test.twig');
		return $response;
	}


}
