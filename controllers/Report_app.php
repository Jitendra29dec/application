<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . 'libraries/REST_Controller.php';
//


if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers:{$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}


class Report_app extends CI_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
		
		//error_reporting(0);
    }
	
	

	
	function Login(){
		
		$email = $this->input->post('email');
		$password = $this->input->post('password');
		
		
		$response['status'] = 0;
		$path = "https://".$_SERVER['HTTP_HOST'].'/assets/img/client';
		
		if(!empty($email) && !empty($password)){
			
		
		$query = $this->db->query("select l.login_id,l.email, v.vendor_name as business_name, v.phone, v.address, v.city, s.name as state, c.name as country  from login l INNER JOIN vendor v on v.login_id=l.login_id LEFT JOIN states s ON s.id=v.state_id LEFT JOIN countries c ON c.id=v.country_id  where l.email='".$email."' and l.password='".md5($password)."' and  l.role_id='1'  and l.is_active='1' and l.is_delete='0'");

			//$resultData->username
			if($query->num_rows()>0){
				
				$resultData = $query->row();
				
				$response['status'] = 1;
				$response['result'] = $query->row();
				$response['message'] = '';

			
				
			}else{
				$response['status'] = 0;
				$response['message'] = 'Incorrect login credential!';
			}
		
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Please enter email & password!';
		}
		
		echo json_encode($response);
	}

	public function testemail(){


/* 
		// the message
$msg = "testing email";

// use wordwrap() if lines are longer than 70 characters
$msg = wordwrap($msg,70);

// send email
$sent = mail("jitendra29dec@gmail.com","My subject",$msg);

if($sent){
	echo "mail sent";
}else{
	echo "mail not sent";
}

die;   */
		

		$config = Array(
			'protocol' => 'smtp',
			'smtp_host' => 'smtp.office365.com',
			'validation'=>TRUE,
			'smtp_timeout'=>30,
			'smtp_port' => '587',
			'smtp_user' => 'noreply@hubwallet.com', // change it to yours
			'smtp_pass' => 'Aepass@123', // change it to yours
			'smtp_crypto' => 'TLS', // change it to yours
			'mailtype' => 'html',
			'charset' => 'iso-8859-1'
		);


		$this->load->library('email');
		$this->email->initialize($config);
		$this->email->from('noreply@hubwallet.com', 'Hubwallet');
		$this->email->to("jitendra29dec@gmail.com");
		$this->email->subject('Registration on Hubwallet');
		
		$this->email->message("this is testing email");
		$is_sent = $this->email->send();
		if($is_sent){
			echo "mail sent";
		}else{
			echo "mail not sent";
		}

		echo $this->email->print_debugger();  


	}


	/* public function sendMail($to, $sub, $body, $fileName=false, $filePath=false, $cc=false){
		
		//require_once('class.phpmailer.php');
		require APPPATH . 'third_party/mailer/class.phpmailer.php';
		$mail             = new PHPMailer();

		$mail->IsSMTP(); // telling the class to use SMTP
		$mail->Host       = "smtp.office365.com";//"10.56.131.8";//"172.30.196.37"; // SMTP server
		$mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
														   // 1 = errors and messages
														   // 2 = messages only
		$mail->SMTPAuth   = true;                  // enable SMTP authentication
		$mail->SMTPSecure = "tls";                 // sets the prefix to the servier
		$mail->Port       = 587;//25;                   // set the SMTP port for the GMAIL server
		$mail->Username   = "noreply@hubwallet.com";  // GMAIL username
		$mail->Password   = 'Aepass@123';     // GMAIL password

		$mail->SetFrom('noreply@hubwallet.com', 'Hubwallet');//
		if(!empty($cc)){
				error_log("cc mail is [".print_r($cc,true)."]");
			foreach($cc as $ccMail){
				error_log("cc mail is [".$ccMail."]");
				$mail->AddCC($ccMail);
			}
		}

		$mail->Subject    = $sub;

		$mail->AltBody    = "Sorry!, Mail content can not be loaded"; // optional, comment out and test

$mail->MsgHTML($body);
 $mail->AddAddress($to);
		if(!empty($fileName)){
				$path = '';
				if(!empty($filePath))
						$path .= $filePath;
				$path .= $fileName;
				$mail->AddAttachment($path);      // attachment
		}
		//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment
		//$mail->Send();
		//return false;

		if(!$mail->Send()) {
				error_log("Mail is not send successfully. Error is [".$mail->ErrorInfo."]");
				return false;
				//echo "Mailer Error: " . $mail->ErrorInfo;
				
		} else {
				error_log("Mail is send successfully");
				return true;
				//echo "Message sent!";
				
		}
}
 */

function testemail2(){
	$this->load->library('Send_mail');
	$ret = $this->sendMail('jitendra29dec@gmail.com', 'hello', 'new test email', $fileName=false, $filePath=false, $cc=false);

	if($ret){
		echo "ok";
	}else{
		echo "not ok";
	}

}


	
}

?>