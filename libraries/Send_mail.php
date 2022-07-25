<?php    
    defined('BASEPATH') OR exit('No direct script access allowed');
    class Send_mail{

		
		public function sendMail($to, $sub, $body, $fileName=false, $filePath=false, $cc=false){
		
			//require_once('class.phpmailer.php');
			require_once  APPPATH . 'third_party/mailer/class.phpmailer.php';
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
       

    }
?>