<?php
namespace App\Controller;

error_reporting(E_ALL);
ini_set("display_errors", 1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

final class HomeController extends BaseController{
    public function dispatch(Request $request, Response $response, $args){
        $this->logger->info("Home page action dispatched");

        $this->flash->addMessage('info', 'Sample flash message');

        $this->view->render($response, 'home.twig');
        return $response;
    }
	
	public function viewPost(Request $request, Response $response, $args){
        $this->logger->info("View post using Doctrine with Slim 3");

        $messages = $this->flash->getMessage('info');

        try {
            $post = $this->em->find('App\Model\Post', intval($args['id']));
        } catch (\Exception $e) {
            echo $e->getMessage();
            die;
        }

        $this->view->render($response, 'post.twig', ['post' => $post, 'flash' => $messages]);
        return $response;
    }

	
	//Current
	public function pagecurrent(Request $request, Response $response, $args){
		try{
			$USN = $_SESSION['usn'];
			$sql = "select unix_timestamp(board.time) as inttime,board.* from board join sensorlist on board.ssn = sensorlist.ssn and sensorlist.usn=".$USN." and sensorlist.flag='1'";
			$stmt = $this->em->getConnection()->prepare($sql);
			$stmt->execute();
			$result = $stmt->fetchAll();
				if($result){
					$board_array = [];
					foreach($result as $data){
						$board_array[] = array("temp"=>$data['temp'],
						"no2"=>$data['rawno2'],"o3"=>$data['rawo3'],
						"co"=>$data['rawco'],"so2"=>$data['rawso2'],
						"pm"=>$data['rawpm'],"time"=>$data['time'],
						"inttime"=>$data['inttime'],
						"center" => array("lat" => $data['latitude'], 
						"lng" => $data['longitude']));
					};	
                	return $response->withStatus(200)
                		->withHeader('Content-Type', 'application/json')
                		->write(json_encode($board_array, JSON_NUMERIC_CHECK));
		
				} else{
					$response = $response->withStatus(404);
				}
		} catch(PDOException $e){
			echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
	}

	public function current(Request $request, Response $response, $args){
		//$this->view->render($response, 'googlemap.twig');
		
		$usn = $_SESSION['usn'];
		
		$sql = "select * from sensorlist where usn='".$usn."' and type='0'";
		$stmt = $this->em->getConnection()->query($sql);
		$result = $stmt->fetchAll();
		
		$sql2 = "select * from sensorlist where usn='".$usn."' and type='1'";
		$stmt2 = $this->em->getConnection()->query($sql2);
		$result2 = $stmt2->fetchAll();
	
		$sql3 = "select * from polar order by psn desc limit 1";
		$stmt3 = $this->em->getConnection()->query($sql3);
		$result3 = $stmt3->fetchAll();
		
		$lat = $result3[0]['latitude'];
		$lng = $result3[0]['longitude'];
		
		$this->view->render($response, 'CurrentPage.twig',['sensor'=>$result,'polarlist'=>$result2,'lat'=>$lat,'lng'=>$lng]);
		return $response;	
	}

	public function showpolar(Request $request, Response $response, $args){
		$usn = $_SESSION['usn'];

        	$sql = "select polar.hr from polar join sensorlist on sensorlist.ssn = polar.ssn and sensorlist.usn=".$usn." order by psn desc limit 1";
        	$stmt = $this->em->getConnection()->query($sql);
        	$result = $stmt->fetchColumn();
	
		$sql2 = "select flag from sensorlist where usn=".$usn." and type='1'";
		$stmt2 = $this->em->getConnection()->query($sql2);
		$result2 = $stmt2->fetchColumn();

		if($result){
			$polar = [];
			$polar = array("nowpolar"=>$result,"state"=>$result2);
               	return $response->withStatus(200)
                	->withHeader('Content-Type', 'application/json')
                	->write(json_encode($polar, JSON_NUMERIC_CHECK));
		} else{
                $response = $response->withStatus(404);
        }
	}

	public function postory(Request $request, Response $response, $args){
		$USN = $_SESSION['usn'];
		$start = date('Y-m-d') ;
		$end = date('Y-m-d');
		if(isset($_GET['fromDate'])){
			$start = $_GET['fromDate'];
			$end = $_GET['toDate'];
		}

		//$sql = "select polar.* from polar join sensorlist on sensorlist.ssn = polar.ssn and sensorlist.usn = ".$USN;
		$sql = "select polar.* from polar join sensorlist on sensorlist.ssn = polar.ssn and sensorlist.usn = ".$USN." where date(polar.time) between date('".$start."') and date('".$end."')";
		$stmt = $this->em->getConnection()->prepare($sql);
		$stmt->execute();
		$result = $stmt->fetchAll();
		if($result){
			foreach(array("s1"=>'time', "s2"=>'hr') as $polar=>$polar_label){
				$json_array['cols'] = array(array('id'=>'','label'=>'time','type'=>'string'),
									array('id'=>'','label'=>'hr','type'=>'number'));

				foreach($result as $row){
					$polar_array = array();
					$polar_array[] = array('v' => $row['time']);
					$polar_array[] = array('v' => $row['hr']);
					$rows[] = array('c'=>$polar_array);
				}	
					
				$json_array['rows'] = $rows;
				$rows = array();
				$master_array[$polar][] = $json_array;	
			}

			return $response->withHeader('Content-type', 'application/json')
				->write(json_encode($master_array, JSON_NUMERIC_CHECK))
				->withStatus(200);
		}	
	}

	public function viewpolarhistory(Request $request, Response $response, $args){
		$this->view->render($response,'calendar.twig');
		return $response;
	}

	public function polarreal(Request $request, Response $response, $args){
        $USN = $_SESSION['usn'];
        //$sql = "select polar.* from polar join sensorlist where sensorlist.ssn = polar.ssn and sensorlist.usn=".$USN." order by psn desc limit 20" ;
        $sql = "select * from (select polar.* from polar join sensorlist on sensorlist.ssn = polar.ssn and sensorlist.usn=".$USN." order by psn desc limit 60) as a order by psn asc;";

		$stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        if($result){
            $polar_array['cols'] = array(
                array('id'=>"",'label'=>'Time','type'=>'string'),
                array('id'=>"",'label'=>'Hr','type'=>'number'));
                                
            foreach($result as $row){
                $hr_array = array();
                $hr_array[] = array('v'=>$row['time']);
                $hr_array[] = array('v'=>$row['hr']);
                $rows[] = array('c'=>$hr_array);
            }

            $polar_array['rows'] = $rows;
        
            return $response->withHeader('Content-type', 'application/json')
                ->write(json_encode($polar_array, JSON_NUMERIC_CHECK))
                ->withStatus(200);

        } else{
            $response = $response->withStatus(404);
        }
	}

	public function viewpolar(Request $request, Response $response, $args){
		$this->view->render($response,'dynamicchart.twig');
		return $response;
	}

	public function calendar(Request $request, Response $response, $args){
		$this->view->render($response,'calendar.twig');
		return $response;
	}

	public function No2history(Request $request, Response $response, $args){
		$this->view->render($response,'No2history.twig');
		return $response;
	}

	public function No2map(Request $request, Response $response, $args){
        try{
            $start = date('Y-m-d') ;
            $end = date('Y-m-d');
            if(isset($_GET['fromDate'])){
                $start = $_GET['fromDate'];
                $end = $_GET['toDate'];
            }

 	        $sql = "select * from board where date(time) between '".$start."' and '".$end."'";
 	        $stmt = $this->em->getConnection()->prepare($sql);
 	        $stmt->execute();
 	        $result = $stmt->fetchAll();
 	        if($result){
 	            $no2_array = [];
 	            foreach($result as $data){
 	                $no2_array[] = array(
 	                "no2"=>$data['rawno2'],
					"time"=>$data['time'],
	                "center" => array("lat" => $data['latitude'],
	                "lng" => $data['longitude']));
	            };
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/json')
                    ->write(json_encode($no2_array, JSON_NUMERIC_CHECK));
            } else{
                $response = $response->withStatus(404);
            }
        } catch(PDOException $e){
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
	}

	public function O3history(Request $request, Response $response, $args){
		$this->view->render($response,'o3history.twig');
		return $response;
	}

	public function O3map(Request $request, Response $response, $args){
        try{
            $start = date('Y-m-d') ;
            $end = date('Y-m-d');
            if(isset($_GET['fromDate'])){
                $start = $_GET['fromDate'];
                $end = $_GET['toDate'];
            }

 	        $sql = "select * from board where date(time) between '".$start."' and '".$end."'";
 	        $stmt = $this->em->getConnection()->prepare($sql);
 	        $stmt->execute();
 	        $result = $stmt->fetchAll();
 	        if($result){
 	            $o3_array = [];
 	            foreach($result as $data){
 	                $o3_array[] = array(
 	                "o3"=>$data['rawo3'],
					"time"=>$data['time'],
	                "center" => array("lat" => $data['latitude'],
	                "lng" => $data['longitude']));
	            };
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/json')
                    ->write(json_encode($o3_array, JSON_NUMERIC_CHECK));
            } else{
                $response = $response->withStatus(404);
            }
        } catch(PDOException $e){
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
	}

	public function COhistory(Request $request, Response $response, $args){
		$this->view->render($response,'COhistory.twig');
		return $response;
	}

	public function Comap(Request $request, Response $response, $args){
        try{
            $start = date('Y-m-d') ;
            $end = date('Y-m-d');
            if(isset($_GET['fromDate'])){
                $start = $_GET['fromDate'];
                $end = $_GET['toDate'];
            }

 	        $sql = "select * from board where date(time) between '".$start."' and '".$end."'";
 	        $stmt = $this->em->getConnection()->prepare($sql);
 	        $stmt->execute();
 	        $result = $stmt->fetchAll();
 	        if($result){
 	            $co_array = [];
 	            foreach($result as $data){
 	                $co_array[] = array(
 	                "co"=>$data['rawco'],
					"time"=>$data['time'],
	                "center" => array("lat" => $data['latitude'],
	                "lng" => $data['longitude']));
	            };
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/json')
                    ->write(json_encode($co_array, JSON_NUMERIC_CHECK));
            } else{
                $response = $response->withStatus(404);
            }
        } catch(PDOException $e){
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
	}

	public function So2history(Request $request, Response $response, $args){
		$this->view->render($response,'So2history.twig');
		return $response;
	}

	public function So2map(Request $request, Response $response, $args){
        try{
            $start = date('Y-m-d') ;
            $end = date('Y-m-d');
            if(isset($_GET['fromDate'])){
                $start = $_GET['fromDate'];
                $end = $_GET['toDate'];
            }

 	        $sql = "select * from board where date(time) between '".$start."' and '".$end."'";
 	        $stmt = $this->em->getConnection()->prepare($sql);
 	        $stmt->execute();
 	        $result = $stmt->fetchAll();
 	        if($result){
 	            $so2_array = [];
 	            foreach($result as $data){
 	                $so2_array[] = array(
 	                "so2"=>$data['rawso2'],
					"time"=>$data['time'],
	                "center" => array("lat" => $data['latitude'],
	                "lng" => $data['longitude']));
	            };
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/json')
                    ->write(json_encode($so2_array, JSON_NUMERIC_CHECK));
            } else{
                $response = $response->withStatus(404);
            }
        } catch(PDOException $e){
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
	}

	public function pmhistory(Request $request, Response $response, $args){
		$this->view->render($response,'pmhistory.twig');
		return $response;
	}

	public function pmmap(Request $request, Response $response, $args){
        try{
            $start = date('Y-m-d') ;
            $end = date('Y-m-d');
            if(isset($_GET['fromDate'])){
                $start = $_GET['fromDate'];
                $end = $_GET['toDate'];
            }

 	        $sql = "select * from board where date(time) between '".$start."' and '".$end."'";
 	        $stmt = $this->em->getConnection()->prepare($sql);
 	        $stmt->execute();
 	        $result = $stmt->fetchAll();
 	        if($result){
 	            $pm_array = [];
 	            foreach($result as $data){
 	                $pm_array[] = array(
 	                "no2"=>$data['rawpm'],
					"time"=>$data['time'],
	                "center" => array("lat" => $data['latitude'],
	                "lng" => $data['longitude']));
	            };
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/json')
                    ->write(json_encode($pm_array, JSON_NUMERIC_CHECK));
            } else{
                $response = $response->withStatus(404);
            }
        } catch(PDOException $e){
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
	}

}
