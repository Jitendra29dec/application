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


class Login extends CI_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
		$this->load->model('login_model');
		//error_reporting(0);
    }
	
	function getVersion(){
		$getVersion=$this->db->query('select value from system_config where field_name="version"')->row();
		$response['status'] = 1;
	    $response['version'] = $getVersion->value;
		echo json_encode($response);
		
	}

	function sendOtpToUser(){

		$pin=$this->input->post('pin');
		if(!empty($pin)){
			$checkEmailExsist=$this->db->query('select login_id,email from login where pin="'.$pin.'" and role_id!=2 ')->row();
			//echo "<pre>";print_r($checkEmailExsist);exit;
			if(!empty($checkEmailExsist)){
			if(!empty($checkEmailExsist->email)){
				
				if($checkEmailExsist->role_id==1){
					$getDetail=$this->db->query('select vendor_name as name from vendor where login_id="'.$checkEmailExsist->login_id.'"')->row();
				}else{
					$getDetail=$this->db->query('select concat(firstname," ",lastname) as name from stylist where login_id="'.$checkEmailExsist->login_id.'"')->row();
				}
				$six_digit_random_number = random_int(100000, 999999);
				$update_key = $this->db->query("update login set otp='".$six_digit_random_number."' where login_id='".$$checkEmailExsist->login."' ");
				$data['pin']=$six_digit_random_number;
				$data['name']=$getDetail->name;
				//$data['pin']=$checkEmailExsist->pin;
				//$data['email']=$email;
				//$data['confirmkey']=$generateTokenhash;

					$receiver_email = $checkEmailExsist->email;
                   $sender_email = 'info@booknpay.com';
                   $initial_time = time();
                   // The mail sending protocol.
                   $config = Array(
                     'protocol' => 'sendmail',
                     'smtp_host' => 'smtp.gmail.com',
                     'validation'=>TRUE,
                     'smtp_timeout'=>30,
                     'smtp_port' => 25,
                     'smtp_user' => 'booknpaysalon@gmail.com', // change it to yours
                     'smtp_pass' => 'bnp@2019$$', // change it to yours
                     'mailtype' => 'html',
                     'mailtype' => 'html',
   				  'charset' => 'iso-8859-1'
                   );

                   $this->load->library('email');
                   $this->email->initialize($config);
                   $this->email->from('info@booknpay.com', 'Hubwallet');
                   $this->email->to($receiver_email);
                   $this->email->subject('Login Otp');
                   
                    $emailTemplate = $this->load->view('sendOtp',$data,TRUE);
					
                   $this->email->message($emailTemplate);
                   if($this->email->send()){
					   
                   	$response['status'] = 1; 
					$response['message'] = 'Otp has sent successfully to your registered email'; 
                   }else{
                  	
                   		$response['status'] =1; 
						$response['message'] ='Mail not sent'; 
                   }
                  }else{
                  	$response['status'] = 0; 
						$response['message'] = 'Wrong Pin ';
                  } 
				 //  echo $this->email->print_debugger();die;
   				
			}else{
				$response['status'] = 0; 
				$response['message'] = 'Email does not exists'; 
			}
		}
		else{
			
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing'; 
		}
		
		echo json_encode($response);
	
	}
	function get(){
		
		$email = $this->input->post('email');
		$password = $this->input->post('password');
		$fcm_token = $this->input->post('fcm_token');
		
		$response['status'] = 0;
		
		$path = "https://".$_SERVER['HTTP_HOST'].'/assets/img/client';
		
		if(!empty($email) && !empty($password)){
			
			$query = $this->db->query("select l.login_id, l.username, l.email,l.fcm_token, l.last_login, v.vendor_id, v.vendor_name, v.owner_name, v.phone, v.alternate_phone, CONCAT('$path','/',if(v.photo='','noimage.png',v.photo)) as photo from login l INNER JOIN vendor v ON v.login_id=l.login_id where l.email='".$email."' and l.password='".md5($password)."' and is_active='1' and is_delete='0' ");
			$res = $query->row();
			if($res){
				
				
				$this->db->query("update login set is_login='1' where email='".$email."' ");
				
				//if($res->fcm_token==NULL || $res->fcm_token==""){
				
				$this->db->query("update login set fcm_token='".$fcm_token."' where login_id='".$res->login_id."' ");
				//}
				
				$response['status'] = 1;
				$response['message'] = 'Login successful';
				$response['data'] = $res;
				
				$c = $this->db->query("select l.email, c.customer_id, CONCAT(c.firstname,' ',c.lastname) as customer_name from login l INNER JOIN customer c ON c.login_id=l.login_id where l.is_active='1' and l.is_delete='0' ");
				$customer_data = $c->result();
				
				$s = $this->db->query("select l.email, s.stylist_id, CONCAT(s.firstname,' ',s.lastname) as stylist_name from login l INNER JOIN stylist s ON s.login_id=l.login_id where l.is_active='1' and l.is_delete='0' ");
				$stylist_data = $s->result();
				
				$sr = $this->db->query("select service_id, service_name from service");
				$service_data = $sr->result();
				
				$response['customer_data'] = $customer_data;
				$response['stylist_data'] = $stylist_data;
				$response['service_data'] = $service_data;
				
			}else{
				$response['status'] = 0;
				$response['message'] = 'Wrong Username or Password';
			}
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
	}
	function LoginPin($pin_new=0){
		//echo date("Y-m-d H:i:s");  ;die;
		if($pin==0 || $pin==''){
			$pin = $this->input->post('pin');
		}else{
			$pin=$pin_new;
		}
		
		//echo "harrs".$pin;exit;
		$device_id= $this->input->post('device_id');
		//$response['message'] = $device_id;
		//echo json_encode($response);die;
		$checkData=$this->db->query('select vendor_id from licence where serial_no="'.$device_id.'" and status=1 and is_delete=0 limit 1')->row();
	//	echo 'select vendor_id from licence where serial_no="'.$device_id.'" and status=1 and is_delete=0 limit 1';die;
//&& $device_id!='fbb4baa8a0221ba5' && $device_id!='ba5f52a170a86e4c'
		if(count($checkData) >=1 && $device_id!='e680f84cf9cb56d7' && $device_id!='ba5f52a170a86e4c'){
			///$join='inner join licence as lnc on l.vendor_id=lnc.vendor_id';
			///$con='inner join licence as lnc on l.vendor_id=lnc.vendor_id';
			$con='and l.vendor_id="'.$checkData->vendor_id.'"';
		}else{
			$con="";
		}
		$response['status'] = 0;
		$path = "https://".$_SERVER['HTTP_HOST'].'/assets/img/client';
		//echo "harsh".$pin;die;
		$fcm_token=$this->input->post('fcm_token');
		if(!empty($pin) ){
			//$this->db->query('update licence set app_type=2,licence_key="'.$device_id.'" where vendor_id=10');
		$checkOtp=$this->db->query('select otp from login where pin="'.$pin.'"')->row();
		
	/*	if($checkOtp ==$otp){*/
	//echo "select l.login_id, CONCAT(' ',v.vendor_name) as username, CONCAT('$path','/',v.photo) as photo, l.fcm_token, l.email,l.role_id, l.last_login, l.vendor_id from login l  INNER JOIN vendor v ON v.vendor_id=l.vendor_id  where l.pin='".$pin."' and (l.role_id='1' OR l.role_id='3')  and l.is_active='1' and l.is_delete='0' ".$con." ";die;	
		$query = $this->db->query("select l.login_id, CONCAT(' ',v.vendor_name) as username, CONCAT('$path','/',v.photo) as photo, l.fcm_token, l.email,l.role_id, l.last_login, l.vendor_id from login l  INNER JOIN vendor v ON v.vendor_id=l.vendor_id  where l.pin='".$pin."' and (l.role_id='1' OR l.role_id='3')  and l.is_active='1' and l.is_delete='0' ".$con." ");
	//	echo "harsh".$query->num_rows();die;
			//$resultData->username
			if($query->num_rows()>0){
				$resultData = $query->row();
				/*if($device_id==$resultData->device_id){*/
				$this->db->query('update login set fcm_token="'.$fcm_token.'" where login_id="'.$resultData->login_id.'"');
					if($resultData->role_id=='3'){
						$getUsername=$this->db->query('select stylist_id,concat(firstname," ",lastname) as username from stylist where login_id="'.$resultData->login_id.'"')->row();
						$response['username']=$getUsername->username;
						$response['stylist_id_product']=$getUsername->stylist_id;

					}else{
					//	echo 'select s.stylist_id,concat(s.firstname," ",s.lastname) as username from stylist as s inner join login as l on l.login_id=s.login_id where l.vendor_id="'.$resultData->vendor_id.'" and s.is_default=1';die;
						$getUsername=$this->db->query('select s.stylist_id,concat(s.firstname," ",s.lastname) as username from stylist as s inner join login as l on l.login_id=s.login_id where l.vendor_id="'.$resultData->vendor_id.'" and s.is_default=1')->row();
						$response['username']='Owner';
						$response['stylist_id_product']=$getUsername->stylist_id;
					}
				if($resultData->role_id=='3'){
					//$q = $this->db->query("select sp.permission_id from stylist_permission sp WHERE role_id='".$resultData->role_id."' AND sp.vendor_id='".$resultData->vendor_id."' ");
					$getRoleId=$this->db->query('select title_id from stylist where login_id="'.$resultData->login_id.'"')->row();
					$q = $this->db->query("select sp.permission_id from stylist_permission sp WHERE role_id='".$getRoleId->title_id."' AND sp.vendor_id='".$resultData->vendor_id."' ");
					$q2 = $this->db->query("select s.stylist_id from stylist s where s.login_id='".$resultData->login_id."'");
					$stylist_id = $q2->row()->stylist_id;
					
					
				}elseif($resultData->role_id=='1'){
					$q = $this->db->query("select id as permission_id from permission ");
					$stylist_id = $resultData->login_id;
					
				}else{
					$q = $this->db->query("select sp.permission_id from stylist_permission sp  WHERE role_id='".$resultData->role_id."' AND sp.vendor_id='".$resultData->vendor_id."' ");
					$stylist_id = $resultData->login_id;
				}

				$role_id = $resultData->role_id;
				
				$permission = $q->result();
				
				
				$check_tax_product = $this->checkIfTaxExist('product',$resultData->vendor_id);
				if($check_tax_product==0){
					$this->db->query("insert into tax_product set tax_type='tax1', tax_rate='0.00', type='current', vendor_id='".$resultData->vendor_id."' ");
					
					$this->db->query("insert into tax_product set tax_type='tax2', tax_rate='0.00', type='current', vendor_id='".$resultData->vendor_id."' ");
					
					$this->db->query("insert into tax_product set tax_type='tax3', tax_rate='0.00', type='current', vendor_id='".$resultData->vendor_id."' ");
					
					$this->db->query("insert into tax_product set tax_type='tax1', tax_rate='0.00', type='future', vendor_id='".$resultData->vendor_id."' ");
					
					$this->db->query("insert into tax_product set tax_type='tax2', tax_rate='0.00', type='future', vendor_id='".$resultData->vendor_id."' ");
					
					$this->db->query("insert into tax_product set tax_type='tax3', tax_rate='0.00', type='future', vendor_id='".$resultData->vendor_id."' ");
					
				}elseif($check_tax_product==3){
					for($i=1;$i<=3;$i++){
						
					$this->db->query("insert into tax_product set tax_type='tax$i', tax_rate='0.00', type='future', vendor_id='".$resultData->vendor_id."' ");
					
					}
				}

			$this->login_model->updateSettingRecords('allow_attendance_outside_workhour',1,NULL,NULL,$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('lunch_time',NULL,'12:00','13:00',$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('break_time',15,NULL,NULL,$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('time_format',24,NULL,NULL,$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('screen_lock_time',60,NULL,NULL,$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('apt_back_time_open',3000,NULL,NULL,$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('tip_allow',1,NULL,NULL,$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('signature_allow',1,NULL,NULL,$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('calendar_start_end_time',NULL,'09:00','22:00',$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('last_appointment_time',NULL,'00','30',$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('week_start_day','Monday',NULL,NULL,$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('tax',10,NULL,NULL,$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('schedule_week_start_day',1,NULL,NULL,$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('schedule_work_hour',NULL,'09:00','22:00',$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('short_break_time','10',NULL,NULL,$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('long_break_time','30',NULL,NULL,$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('short_break_deduction','0',NULL,NULL,$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('calendar_slot_duration','60',NULL,NULL,$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('calendar_row_height','6',NULL,NULL,$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('mailchimp','',NULL,NULL,$resultData->vendor_id,1);
			$this->login_model->updateSettingRecords('screen_view','employee_view',NULL,NULL,$resultData->vendor_id,1);


			$this->login_model->updateAppointmentRules($resultData->vendor_id);
			$this->login_model->updateBillingInfo($resultData->vendor_id);
			$this->login_model->updateBusinessHour($resultData->vendor_id);
			$this->login_model->updateCancellationPolicy($resultData->vendor_id);
			$this->login_model->updateCalendarColor($resultData->vendor_id);
			$this->login_model->updateNotificationCriteria($resultData->vendor_id);
			$this->login_model->allowAptOutsideBusinessHour($resultData->vendor_id);
			$this->login_model->notificationCriteriaType($resultData->vendor_id);
			$this->login_model->updateGiftCardPresetAmount($resultData->vendor_id);
			$this->login_model->updateBatchCloseTime($resultData->vendor_id);
			$this->login_model->updateGiftCertificateSettings($resultData->vendor_id);
			$this->login_model->updateTvScreenSettings($resultData->vendor_id);
			$this->login_model->updateIsScreenLock($resultData->vendor_id);
			$this->login_model->updateServiceCharge($resultData->vendor_id);
			$this->login_model->updateWaitingStatus($resultData->vendor_id);
			$this->login_model->updateEmployeeType($resultData->vendor_id);
			$this->login_model->updatePosition($resultData->vendor_id);
			$this->login_model->updateCashDiscountDisplayName($resultData->vendor_id);
			$this->login_model->updateGiftCardImages($resultData->vendor_id);
			$this->login_model->updateCertificateImages($resultData->vendor_id);
			$this->login_model->updateDefaultSupplier($resultData->vendor_id);
			$this->login_model->updateDefaultBrand($resultData->vendor_id);
			$this->login_model->updateDefaultCategory($resultData->vendor_id);
			$this->login_model->updateEmployeeStatus($resultData->vendor_id);
			$this->login_model->updateEquipmentDevice($resultData->vendor_id);
			$this->login_model->updateEmployeePermission($resultData->vendor_id);
			$this->login_model->updateSupplierStatus($resultData->vendor_id);

			
			//$this->updateTaxes($resultData->vendor_id);

			// update future tax code start here
				$current_date_new = date('Y-n-j');
				$query_tax = $this->db->query("select * from tax_product where vendor_id='".$resultData->vendor_id."' and type='future' and start_date='".$current_date_new."' ");

				$future_tax = $query_tax->result();

				if($query_tax->num_rows()>0){
					foreach($future_tax as $ft){

						$this->db->query("update tax_product set tax_rate='".$ft->tax_rate."', start_date='".$ft->start_date."', description='".$ft->description."' where vendor_id='".$resultData->vendor_id."' and type='current' and tax_type='".$ft->tax_type."' ");
	
						$qu = $this->db->query("update tax_product set tax_rate='0.00', start_date=NULL, description=NULL WHERE  vendor_id='".$resultData->vendor_id."' and type='future' and start_date='".$current_date_new."' ");

					

					}

					/* $query_udpate2 = $this->db->query("select tax_id, tax_rate from tax_product where type='current' and vendor_id='".$resultData->vendor_id."'");

					$checkout_tax = $query_udpate2->result();
					foreach($checkout_tax as $ct){
						
						$this->db->query("update multiple_tax set tax_percent='".$ct->tax_rate."' where tax_id='".$ct->tax_id."' ");
					} */


					$service_price_data = $this->db->query("select s.service_id, s.price, tp.tax_rate, mt.tax_id, mt.id from service s INNER JOIN multiple_tax mt ON mt.sp_id=s.service_id INNER JOIN tax_product tp where mt.tax_id=tp.tax_id and tp.vendor_id='".$resultData->vendor_id."' and tp.type='current' and mt.type='1' ")->result();
						
					foreach($service_price_data as $pd){

						$tax_amount = ($pd->price*$pd->tax_rate)/100;
						$this->db->query("update multiple_tax set tax_percent='".$pd->tax_rate."', tax_amount='".$tax_amount."' where id='".$pd->id."' ");
					}

					$product_price_data = $this->db->query("select p.product_id, p.price_retail, tp.tax_rate, mt.tax_id, mt.id from product p INNER JOIN multiple_tax mt ON mt.sp_id=p.product_id INNER JOIN tax_product tp where mt.tax_id=tp.tax_id and tp.vendor_id='".$resultData->vendor_id."' and tp.type='current' and mt.type='2' ")->result();
						
					foreach($product_price_data as $pd){

						$tax_amount = ($pd->price_retail*$pd->tax_rate)/100;
						$this->db->query("update multiple_tax set tax_percent='".$pd->tax_rate."', tax_amount='".$tax_amount."' where id='".$pd->id."' ");
					}

				}

				


				
			// update future tax code end here
			
			
			$customer_registration = "Dear Sir/Mam,<br />Thank You Register With Ous Your Email Id Is: booknpay@gmail.com<br />testing<br />Thank You<br />Team Booknpay";


			$new_appointment_sms = "Hi {Customer First Name}, your appointment is booked at {Business Name} on {Date} at {Time}. Manage your appointment here (link to app). We can't wait to see you! - {Business Name}";

			$update_appointment_sms = "Hi {Customer First Name}, your appointment at {Business Name} has been updated to {Date} at {Time}. We can't wait to see you! Manage your appointment here (link to app). - {Business Name}";

			$appointment_cancellation_sms = "Hi {Customer First Name}, your appointment at {Business Name} on {Date} at {Time} has been cancelled. We hope to see you! Book a new appointment here (link to app). - {Business Name}";

			$appointment_confirmation_sms = "Hi {Customer First Name}, your appointment at {Business Name} is coming up on {Date} at {Time}. We can't wait to see you! Manage your appointment here (link to app). Reply Y to confirm or N to cancel.";

			$customer_registration_sms = "Hi {Customer First Name}, Welcome to {Business Name}! We make it easy to book and manage your appointments! Download our app to book appointments at any time (link to app). Note: the name of the app is Hubwallet. We can't wait to see you!";

			$customer_birthday_sms = "Happy Birthday {Customer First Name}! We want to wish you a very happy birthday! We hope you have a wonderful day, and we look forwarad to seeing you soon! - {Business Name}";

			$employee_registration_sms = "Hi {Employee First Name}, Welcome to {Business Name}! Download our app to view your appointments and schedule no matter where you are! Check your email for details to get started.";

			$employee_birthday_sms = "Happy Birthday {Employee First Name}! We want to wish you a very happy birthday! We hope you have a wonderful day, and we can't wait to celebrate you! - {Business Name}";

			$employee_schedule_add = "Hi {Employee First Name}, your schedule has been added! Please view your schedule on the app and let us know if there are any modifications. Thanks!";

			$employee_schedule_update = "Hi {Employee First Name}, your schedule has been updated! Please view your schedule on the app and let us know if there are any modifications. Thanks!";

			$admin_receive_po_sms = "Hi {Admin First Name}, Purchase Order {PO Number} placed on {PO Order Date} from {Vendor} is received.";





			
			$new_appointment = '<!DOCTYPE html>
			<html>
			<head>
			
			  <meta charset="utf-8">
			  <meta http-equiv="x-ua-compatible" content="ie=edge">
			  <title></title>
			  <meta name="viewport" content="width=device-width, initial-scale=1">
			  <style type="text/css">
			  /**
			   * Google webfonts. Recommended to include the .woff version for cross-client compatibility.
			   */
			  @media screen {
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 400;
				  src: local("Source Sans Pro Regular"), local("SourceSansPro-Regular"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/ODelI1aHBYDBqgeIAH2zlBM0YzuT7MdOe03otPbuUS0.woff) format("woff");
				}
			
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 700;
				  src: local("Source Sans Pro Bold"), local("SourceSansPro-Bold"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/toadOcfmlt9b38dHJxOBGFkQc6VGVFSmCnC_l7QZG60.woff) format("woff");
				}
			  }
			
			  /**
			   * Avoid browser level font resizing.
			   * 1. Windows Mobile
			   * 2. iOS / OSX
			   */
			  body,
			  table,
			  td,
			  a {
				-ms-text-size-adjust: 100%; /* 1 */
				-webkit-text-size-adjust: 100%; /* 2 */
			  }
			
			  /**
			   * Remove extra space added to tables and cells in Outlook.
			   */
			  table,
			  td {
				mso-table-rspace: 0pt;
				mso-table-lspace: 0pt;
			  }
			
			  /**
			   * Better fluid images in Internet Explorer.
			   */
			  img {
				-ms-interpolation-mode: bicubic;
			  }
			
			  /**
			   * Remove blue links for iOS devices.
			   */
			  a[x-apple-data-detectors] {
				font-family: inherit !important;
				font-size: inherit !important;
				font-weight: inherit !important;
				line-height: inherit !important;
				color: inherit !important;
				text-decoration: none !important;
			  }
			
			  /**
			   * Fix centering issues in Android 4.4.
			   */
			  div[style*="margin: 16px 0;"] {
				margin: 0 !important;
			  }
			
			  body {
				width: 100% !important;
				height: 100% !important;
				padding: 0 !important;
				margin: 0 !important;
			  }
			
			  /**
			   * Collapse table borders to avoid space between cells.
			   */
			  table {
				border-collapse: collapse !important;
			  }
			
			  a {
				color: #1a82e2;
			  }
			
			  img {
				height: auto;
				line-height: 100%;
				text-decoration: none;
				border: 0;
				outline: none;
			  }
			  </style>
			
			</head>
			<body style="background-color: #e9ecef;">
			
			  <!-- start preheader -->
			  <!-- <div class="preheader" style="display: none; max-width: 0; max-height: 0; overflow: hidden; font-size: 1px; line-height: 1px; color: #fff; opacity: 0;">
				A preheader is the short summary text that follows the subject line when an email is viewed in the inbox.
			  </div> -->
			  <!-- end preheader -->
			
			  <!-- start body -->
			  <table border="0" cellpadding="0" cellspacing="0" width="100%">
			
				<!-- start logo -->
				<tr>
				<td align="center" bgcolor="#e9ecef">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="center" valign="top" style="padding: 5px;">
						  
						<img src="https://booknpay.com/assets/img/hubwallet-logo.png" alt="Logo" border="0" style="display: block; width: 50%; height:135px">
					   
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end logo -->
			
				<!-- start hero -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 36px 24px 0; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif;">
						  <h1 style="margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -1px; line-height: 48px;">Hi {Customer First Name}</h1>
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end hero -->
			
				<!-- start copy block -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
			
					  <!-- start copy -->
					
			
					  <tr>
							<td colspan="2" align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
							
							<p>Your appointment is booked at {Business Name}</p>
							<p></p>
			
								<p><b>Date</b><br/>
								{Day Date}<br/>
								{Time}
								 </p>
					</td>
			
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
						<br/>
						
						<p><b>Location</b><br/>
						
							 {Street Address}<br/>
							{City}, {State} {Zip Code}
						</p>
						</td>
					  </tr>
			
			
					  <tr>
							<td colspan="2" align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
								<p><b>Service</b><br/>
									{Service Name} with {Employee Name}
								   
						</p>
					</td>
			
							<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
								
							<p><b>Approximate Duration</b><br/>
						
							{Duration} Min
			</p>
						</td>
			
			
					   
					  </tr>
			
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
						<p>Cancellation Policy<br/>
			
						{Cancellation Policy}<br/><br/>
			
					   
						 </p>
						<p>
						We can\'t wait to see you!<br/><br/>    
						Cheers,<br/>
						{Business Name}<br/>
						{Business Phone Number}</p>
			</td>
			</tr>
			
			
			
					  <!-- end copy -->
			
					  <!-- start button -->
					  
					  <!-- end button -->
			
					  <!-- start copy -->
					  
					  <!-- end copy -->
			
					  <!-- start copy -->
					  <!-- <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px; border-bottom: 3px solid #d4dadf">
						  <p style="margin: 0;">Cheers</p>
						</td>
					  </tr> -->
					  <!-- end copy -->
			
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end copy block -->
			
				<!-- start footer -->
				
				<!-- end footer -->
			
			  </table>
			  <!-- end body -->
			
			</body>
			</html>';
			
			$appointment_reminder = '<!DOCTYPE html>
			<html>
			<head>
			
			  <meta charset="utf-8">
			  <meta http-equiv="x-ua-compatible" content="ie=edge">
			  <title></title>
			  <meta name="viewport" content="width=device-width, initial-scale=1">
			  <style type="text/css">
			  /**
			   * Google webfonts. Recommended to include the .woff version for cross-client compatibility.
			   */
			  @media screen {
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 400;
				  src: local("Source Sans Pro Regular"), local("SourceSansPro-Regular"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/ODelI1aHBYDBqgeIAH2zlBM0YzuT7MdOe03otPbuUS0.woff) format("woff");
				}
			
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 700;
				  src: local("Source Sans Pro Bold"), local("SourceSansPro-Bold"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/toadOcfmlt9b38dHJxOBGFkQc6VGVFSmCnC_l7QZG60.woff) format("woff");
				}
			  }
			
			  /**
			   * Avoid browser level font resizing.
			   * 1. Windows Mobile
			   * 2. iOS / OSX
			   */
			  body,
			  table,
			  td,
			  a {
				-ms-text-size-adjust: 100%; /* 1 */
				-webkit-text-size-adjust: 100%; /* 2 */
			  }
			
			  /**
			   * Remove extra space added to tables and cells in Outlook.
			   */
			  table,
			  td {
				mso-table-rspace: 0pt;
				mso-table-lspace: 0pt;
			  }
			
			  /**
			   * Better fluid images in Internet Explorer.
			   */
			  img {
				-ms-interpolation-mode: bicubic;
			  }
			
			  /**
			   * Remove blue links for iOS devices.
			   */
			  a[x-apple-data-detectors] {
				font-family: inherit !important;
				font-size: inherit !important;
				font-weight: inherit !important;
				line-height: inherit !important;
				color: inherit !important;
				text-decoration: none !important;
			  }
			
			  /**
			   * Fix centering issues in Android 4.4.
			   */
			  div[style*="margin: 16px 0;"] {
				margin: 0 !important;
			  }
			
			  body {
				width: 100% !important;
				height: 100% !important;
				padding: 0 !important;
				margin: 0 !important;
			  }
			
			  /**
			   * Collapse table borders to avoid space between cells.
			   */
			  table {
				border-collapse: collapse !important;
			  }
			
			  a {
				color: #1a82e2;
			  }
			
			  img {
				height: auto;
				line-height: 100%;
				text-decoration: none;
				border: 0;
				outline: none;
			  }
			  </style>
			
			</head>
			<body style="background-color: #e9ecef;">
			
			  <!-- start preheader -->
			  <!-- <div class="preheader" style="display: none; max-width: 0; max-height: 0; overflow: hidden; font-size: 1px; line-height: 1px; color: #fff; opacity: 0;">
				A preheader is the short summary text that follows the subject line when an email is viewed in the inbox.
			  </div> -->
			  <!-- end preheader -->
			
			  <!-- start body -->
			  <table border="0" cellpadding="0" cellspacing="0" width="100%">
			
				<!-- start logo -->
				<tr>
				<td align="center" bgcolor="#e9ecef">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="center" valign="top" style="padding: 5px;">
						  
						<img src="https://booknpay.com/assets/img/hubwallet-logo.png" alt="Logo" border="0" style="display: block; width: 50%; height:135px">
					   
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end logo -->
			
				<!-- start hero -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 36px 24px 0; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif;">
						  <h1 style="margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -1px; line-height: 48px;">Hi {Customer First Name}</h1>
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end hero -->
			
				<!-- start copy block -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
			
					  <!-- start copy -->
					
			
					  <tr>
							<td colspan="2" align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
							
							<p>Your appointment at {Business Name} is coming up!</p>
							<p>Your appointment at {Business Name} is around the corner! We can\'t wait to see you!</p>
			
								<p><b>Date</b><br/>
								{Day Date}<br/>
								{Time}
								 </p>
					</td>
			
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
						<br/>
						
						<p><b>Location</b><br/>
						
							 {Street Address}<br/>
							{City}, {State} {Zip Code}
						</p>
						</td>
					  </tr>
			
			
					  <tr>
							<td colspan="2" align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
								<p><b>Service</b><br/>
									{Service Name} with {Employee Name}
								   
						</p>
					</td>
			
							<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
								
							<p><b>Approximate Duration</b><br/>
						
							{Duration} Min
			</p>
						</td>
			
			
					   
					  </tr>
			
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
						<p>Cancellation Policy<br/>
			
						{Cancellation Policy}<br/><br/>
			
					   
						 </p>
						<p>
						We can\'t wait to see you!<br/><br/>    
						Cheers,<br/>
						{Business Name}<br/>
						{Business Phone Number}</p>
			</td>
			</tr>
			
			
			
					  <!-- end copy -->
			
					  <!-- start button -->
					  
					  <!-- end button -->
			
					  <!-- start copy -->
					  
					  <!-- end copy -->
			
					  <!-- start copy -->
					  <!-- <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px; border-bottom: 3px solid #d4dadf">
						  <p style="margin: 0;">Cheers</p>
						</td>
					  </tr> -->
					  <!-- end copy -->
			
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end copy block -->
			
				<!-- start footer -->
				
				<!-- end footer -->
			
			  </table>
			  <!-- end body -->
			
			</body>
			</html>';
			
			$stylist_registration = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			
			$customer_forgot_password = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$appointment_update = '<!DOCTYPE html>
			<html>
			<head>
			
			  <meta charset="utf-8">
			  <meta http-equiv="x-ua-compatible" content="ie=edge">
			  <title></title>
			  <meta name="viewport" content="width=device-width, initial-scale=1">
			  <style type="text/css">
			  /**
			   * Google webfonts. Recommended to include the .woff version for cross-client compatibility.
			   */
			  @media screen {
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 400;
				  src: local("Source Sans Pro Regular"), local("SourceSansPro-Regular"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/ODelI1aHBYDBqgeIAH2zlBM0YzuT7MdOe03otPbuUS0.woff) format("woff");
				}
			
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 700;
				  src: local("Source Sans Pro Bold"), local("SourceSansPro-Bold"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/toadOcfmlt9b38dHJxOBGFkQc6VGVFSmCnC_l7QZG60.woff) format("woff");
				}
			  }
			
			  /**
			   * Avoid browser level font resizing.
			   * 1. Windows Mobile
			   * 2. iOS / OSX
			   */
			  body,
			  table,
			  td,
			  a {
				-ms-text-size-adjust: 100%; /* 1 */
				-webkit-text-size-adjust: 100%; /* 2 */
			  }
			
			  /**
			   * Remove extra space added to tables and cells in Outlook.
			   */
			  table,
			  td {
				mso-table-rspace: 0pt;
				mso-table-lspace: 0pt;
			  }
			
			  /**
			   * Better fluid images in Internet Explorer.
			   */
			  img {
				-ms-interpolation-mode: bicubic;
			  }
			
			  /**
			   * Remove blue links for iOS devices.
			   */
			  a[x-apple-data-detectors] {
				font-family: inherit !important;
				font-size: inherit !important;
				font-weight: inherit !important;
				line-height: inherit !important;
				color: inherit !important;
				text-decoration: none !important;
			  }
			
			  /**
			   * Fix centering issues in Android 4.4.
			   */
			  div[style*="margin: 16px 0;"] {
				margin: 0 !important;
			  }
			
			  body {
				width: 100% !important;
				height: 100% !important;
				padding: 0 !important;
				margin: 0 !important;
			  }
			
			  /**
			   * Collapse table borders to avoid space between cells.
			   */
			  table {
				border-collapse: collapse !important;
			  }
			
			  a {
				color: #1a82e2;
			  }
			
			  img {
				height: auto;
				line-height: 100%;
				text-decoration: none;
				border: 0;
				outline: none;
			  }
			  </style>
			
			</head>
			<body style="background-color: #e9ecef;">
			
			  <!-- start preheader -->
			  <!-- <div class="preheader" style="display: none; max-width: 0; max-height: 0; overflow: hidden; font-size: 1px; line-height: 1px; color: #fff; opacity: 0;">
				A preheader is the short summary text that follows the subject line when an email is viewed in the inbox.
			  </div> -->
			  <!-- end preheader -->
			
			  <!-- start body -->
			  <table border="0" cellpadding="0" cellspacing="0" width="100%">
			
				<!-- start logo -->
				<tr>
				<td align="center" bgcolor="#e9ecef">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="center" valign="top" style="padding: 5px;">
						  
						<img src="https://booknpay.com/assets/img/hubwallet-logo.png" alt="Logo" border="0" style="display: block; width: 50%; height:135px">
					   
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end logo -->
			
				<!-- start hero -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 36px 24px 0; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif;">
						  <h1 style="margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -1px; line-height: 48px;">Hi {Customer First Name}</h1>
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end hero -->
			
				<!-- start copy block -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
			
					  <!-- start copy -->
					
			
					  <tr>
							<td colspan="2" align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
							
							<p>Your appointment at {Business Name} has been updated</p>
							<p></p>
			
								<p><b>Date</b><br/>
								{Day Date}<br/>
								{Time}
								 </p>
					</td>
			
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
						<br/>
						
						<p><b>Location</b><br/>
						
							 {Street Address}<br/>
							{City}, {State} {Zip Code}
						</p>
						</td>
					  </tr>
			
			
					  <tr>
							<td colspan="2" align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
								<p><b>Service</b><br/>
									{Service Name} with {Employee Name}
								   
						</p>
					</td>
			
							<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
								
							<p><b>Approximate Duration</b><br/>
						
							{Duration} Min
			</p>
						</td>
			
			
					   
					  </tr>
			
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
						<p>Cancellation Policy<br/>
			
						{Cancellation Policy}<br/><br/>
			
					   
						 </p>
						<p>
						We can\'t wait to see you!<br/><br/>    
						Cheers,<br/>
						{Business Name}<br/>
						{Business Phone Number}</p>
			</td>
			</tr>
			
			
			
					  <!-- end copy -->
			
					  <!-- start button -->
					  
					  <!-- end button -->
			
					  <!-- start copy -->
					  
					  <!-- end copy -->
			
					  <!-- start copy -->
					  <!-- <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px; border-bottom: 3px solid #d4dadf">
						  <p style="margin: 0;">Cheers</p>
						</td>
					  </tr> -->
					  <!-- end copy -->
			
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end copy block -->
			
				<!-- start footer -->
				
				<!-- end footer -->
			
			  </table>
			  <!-- end body -->
			
			</body>
			</html>';
			
			$confirmation_appointment = '<!DOCTYPE html>
			<html>
			<head>
			
			  <meta charset="utf-8">
			  <meta http-equiv="x-ua-compatible" content="ie=edge">
			  <title></title>
			  <meta name="viewport" content="width=device-width, initial-scale=1">
			  <style type="text/css">
			  /**
			   * Google webfonts. Recommended to include the .woff version for cross-client compatibility.
			   */
			  @media screen {
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 400;
				  src: local("Source Sans Pro Regular"), local("SourceSansPro-Regular"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/ODelI1aHBYDBqgeIAH2zlBM0YzuT7MdOe03otPbuUS0.woff) format("woff");
				}
			
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 700;
				  src: local("Source Sans Pro Bold"), local("SourceSansPro-Bold"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/toadOcfmlt9b38dHJxOBGFkQc6VGVFSmCnC_l7QZG60.woff) format("woff");
				}
			  }
			
			  /**
			   * Avoid browser level font resizing.
			   * 1. Windows Mobile
			   * 2. iOS / OSX
			   */
			  body,
			  table,
			  td,
			  a {
				-ms-text-size-adjust: 100%; /* 1 */
				-webkit-text-size-adjust: 100%; /* 2 */
			  }
			
			  /**
			   * Remove extra space added to tables and cells in Outlook.
			   */
			  table,
			  td {
				mso-table-rspace: 0pt;
				mso-table-lspace: 0pt;
			  }
			
			  /**
			   * Better fluid images in Internet Explorer.
			   */
			  img {
				-ms-interpolation-mode: bicubic;
			  }
			
			  /**
			   * Remove blue links for iOS devices.
			   */
			  a[x-apple-data-detectors] {
				font-family: inherit !important;
				font-size: inherit !important;
				font-weight: inherit !important;
				line-height: inherit !important;
				color: inherit !important;
				text-decoration: none !important;
			  }
			
			  /**
			   * Fix centering issues in Android 4.4.
			   */
			  div[style*="margin: 16px 0;"] {
				margin: 0 !important;
			  }
			
			  body {
				width: 100% !important;
				height: 100% !important;
				padding: 0 !important;
				margin: 0 !important;
			  }
			
			  /**
			   * Collapse table borders to avoid space between cells.
			   */
			  table {
				border-collapse: collapse !important;
			  }
			
			  a {
				color: #1a82e2;
			  }
			
			  img {
				height: auto;
				line-height: 100%;
				text-decoration: none;
				border: 0;
				outline: none;
			  }
			  </style>
			
			</head>
			<body style="background-color: #e9ecef;">
			
			  <!-- start preheader -->
			  <!-- <div class="preheader" style="display: none; max-width: 0; max-height: 0; overflow: hidden; font-size: 1px; line-height: 1px; color: #fff; opacity: 0;">
				A preheader is the short summary text that follows the subject line when an email is viewed in the inbox.
			  </div> -->
			  <!-- end preheader -->
			
			  <!-- start body -->
			  <table border="0" cellpadding="0" cellspacing="0" width="100%">
			
				<!-- start logo -->
				<tr>
				<td align="center" bgcolor="#e9ecef">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="center" valign="top" style="padding: 5px;">
						  
						<img src="https://booknpay.com/assets/img/hubwallet-logo.png" alt="Logo" border="0" style="display: block; width: 50%; height:135px">
					   
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end logo -->
			
				<!-- start hero -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 36px 24px 0; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif;">
						  <h1 style="margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -1px; line-height: 48px;">Hi {Customer First Name}</h1>
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end hero -->
			
				<!-- start copy block -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
			
					  <!-- start copy -->
					
			
					  <tr>
							<td colspan="2" align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
							
							<p>Your appointment at {Business Name} is around the corner! Please take a moment to confirm your appointment.</p>
							<p></p>
			
								<p><b>Date</b><br/>
								{Day Date}<br/>
								{Time}
								 </p>
					</td>
			
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
						<br/>
						
						<p><b>Location</b><br/>
						
							 {Street Address}<br/>
							{City}, {State} {Zip Code}
						</p>
						</td>
					  </tr>
			
			
					  <tr>
							<td colspan="2" align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
								<p><b>Service</b><br/>
									{Service Name} with {Employee Name}
								   
						</p>
					</td>
			
							<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
								
							<p><b>Approximate Duration</b><br/>
						
							{Duration} Min
			</p>
						</td>
			
			
					   
					  </tr>
			
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
						<p>Cancellation Policy<br/>
			
						{Cancellation Policy}<br/><br/>
			
					   
						 </p>
						<p>
						We can\'t wait to see you!<br/><br/>    
						Cheers,<br/>
						{Business Name}<br/>
						{Business Phone Number}</p>
			</td>
			</tr>
			
			
			
					  <!-- end copy -->
			
					  <!-- start button -->
					  
					  <!-- end button -->
			
					  <!-- start copy -->
					  
					  <!-- end copy -->
			
					  <!-- start copy -->
					  <!-- <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px; border-bottom: 3px solid #d4dadf">
						  <p style="margin: 0;">Cheers</p>
						</td>
					  </tr> -->
					  <!-- end copy -->
			
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end copy block -->
			
				<!-- start footer -->
				
				<!-- end footer -->
			
			  </table>
			  <!-- end body -->
			
			</body>
			</html>';
										
										
			$no_show = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$checkin = '';
			
			$checkout = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$cancel_appointment = '<!DOCTYPE html>
			<html>
			<head>
			
			  <meta charset="utf-8">
			  <meta http-equiv="x-ua-compatible" content="ie=edge">
			  <title></title>
			  <meta name="viewport" content="width=device-width, initial-scale=1">
			  <style type="text/css">
			  /**
			   * Google webfonts. Recommended to include the .woff version for cross-client compatibility.
			   */
			  @media screen {
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 400;
				  src: local("Source Sans Pro Regular"), local("SourceSansPro-Regular"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/ODelI1aHBYDBqgeIAH2zlBM0YzuT7MdOe03otPbuUS0.woff) format("woff");
				}
			
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 700;
				  src: local("Source Sans Pro Bold"), local("SourceSansPro-Bold"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/toadOcfmlt9b38dHJxOBGFkQc6VGVFSmCnC_l7QZG60.woff) format("woff");
				}
			  }
			
			  /**
			   * Avoid browser level font resizing.
			   * 1. Windows Mobile
			   * 2. iOS / OSX
			   */
			  body,
			  table,
			  td,
			  a {
				-ms-text-size-adjust: 100%; /* 1 */
				-webkit-text-size-adjust: 100%; /* 2 */
			  }
			
			  /**
			   * Remove extra space added to tables and cells in Outlook.
			   */
			  table,
			  td {
				mso-table-rspace: 0pt;
				mso-table-lspace: 0pt;
			  }
			
			  /**
			   * Better fluid images in Internet Explorer.
			   */
			  img {
				-ms-interpolation-mode: bicubic;
			  }
			
			  /**
			   * Remove blue links for iOS devices.
			   */
			  a[x-apple-data-detectors] {
				font-family: inherit !important;
				font-size: inherit !important;
				font-weight: inherit !important;
				line-height: inherit !important;
				color: inherit !important;
				text-decoration: none !important;
			  }
			
			  /**
			   * Fix centering issues in Android 4.4.
			   */
			  div[style*="margin: 16px 0;"] {
				margin: 0 !important;
			  }
			
			  body {
				width: 100% !important;
				height: 100% !important;
				padding: 0 !important;
				margin: 0 !important;
			  }
			
			  /**
			   * Collapse table borders to avoid space between cells.
			   */
			  table {
				border-collapse: collapse !important;
			  }
			
			  a {
				color: #1a82e2;
			  }
			
			  img {
				height: auto;
				line-height: 100%;
				text-decoration: none;
				border: 0;
				outline: none;
			  }
			  </style>
			
			</head>
			<body style="background-color: #e9ecef;">
			
			  <!-- start preheader -->
			  <!-- <div class="preheader" style="display: none; max-width: 0; max-height: 0; overflow: hidden; font-size: 1px; line-height: 1px; color: #fff; opacity: 0;">
				A preheader is the short summary text that follows the subject line when an email is viewed in the inbox.
			  </div> -->
			  <!-- end preheader -->
			
			  <!-- start body -->
			  <table border="0" cellpadding="0" cellspacing="0" width="100%">
			
				<!-- start logo -->
				<tr>
				<td align="center" bgcolor="#e9ecef">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="center" valign="top" style="padding: 5px;">
						  
						<img src="https://booknpay.com/assets/img/hubwallet-logo.png" alt="Logo" border="0" style="display: block; width: 50%; height:135px">
					   
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end logo -->
			
				<!-- start hero -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 36px 24px 0; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif;">
						  <h1 style="margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -1px; line-height: 48px;">Hi {Customer First Name}</h1>
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end hero -->
			
				<!-- start copy block -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
			
					  <!-- start copy -->
					
			
					  <tr>
							<td colspan="2" align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
							
							<p>Your appointment at {Business Name} has been cancelled</p>
							<p></p>
			
								<p><b>Date</b><br/>
								{Day Date}<br/>
								{Time}
								 </p>
					</td>
			
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
						<br/>
						
						<p><b>Location</b><br/>
						
							 {Street Address}<br/>
							{City}, {State} {Zip Code}
						</p>
						</td>
					  </tr>
			
			
					  <tr>
							<td colspan="2" align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
								<p><b>Service</b><br/>
									{Service Name} with {Employee Name}
								   
						</p>
					</td>
			
							<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
								
							<p><b>Approximate Duration</b><br/>
						
							{Duration} Min
			</p>
						</td>
			
			
					   
					  </tr>
			
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
						<p>Cancellation Policy<br/>
			
						{Cancellation Policy}<br/><br/>
			
					   
						 </p>
						<p>
						<br/>
						{Business Name}<br/>
						{Business Phone Number}</p>
			</td>
			</tr>
			
			
			
					  <!-- end copy -->
			
					  <!-- start button -->
					  
					  <!-- end button -->
			
					  <!-- start copy -->
					  
					  <!-- end copy -->
			
					  <!-- start copy -->
					  <!-- <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px; border-bottom: 3px solid #d4dadf">
						  <p style="margin: 0;">Cheers</p>
						</td>
					  </tr> -->
					  <!-- end copy -->
			
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end copy block -->
			
				<!-- start footer -->
				
				<!-- end footer -->
			
			  </table>
			  <!-- end body -->
			
			</body>
			</html>';

			$schedule_add = '<!DOCTYPE html>
			<html>
			<head>
			
			  <meta charset="utf-8">
			  <meta http-equiv="x-ua-compatible" content="ie=edge">
			  <title></title>
			  <meta name="viewport" content="width=device-width, initial-scale=1">
			  <style type="text/css">
			  /**
			   * Google webfonts. Recommended to include the .woff version for cross-client compatibility.
			   */
			  @media screen {
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 400;
				  src: local("Source Sans Pro Regular"), local("SourceSansPro-Regular"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/ODelI1aHBYDBqgeIAH2zlBM0YzuT7MdOe03otPbuUS0.woff) format("woff");
				}
			
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 700;
				  src: local("Source Sans Pro Bold"), local("SourceSansPro-Bold"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/toadOcfmlt9b38dHJxOBGFkQc6VGVFSmCnC_l7QZG60.woff) format("woff");
				}
			  }
			
			  /**
			   * Avoid browser level font resizing.
			   * 1. Windows Mobile
			   * 2. iOS / OSX
			   */
			  body,
			  table,
			  td,
			  a {
				-ms-text-size-adjust: 100%; /* 1 */
				-webkit-text-size-adjust: 100%; /* 2 */
			  }
			
			  /**
			   * Remove extra space added to tables and cells in Outlook.
			   */
			  table,
			  td {
				mso-table-rspace: 0pt;
				mso-table-lspace: 0pt;
			  }
			
			  /**
			   * Better fluid images in Internet Explorer.
			   */
			  img {
				-ms-interpolation-mode: bicubic;
			  }
			
			  /**
			   * Remove blue links for iOS devices.
			   */
			  a[x-apple-data-detectors] {
				font-family: inherit !important;
				font-size: inherit !important;
				font-weight: inherit !important;
				line-height: inherit !important;
				color: inherit !important;
				text-decoration: none !important;
			  }
			
			  /**
			   * Fix centering issues in Android 4.4.
			   */
			  div[style*="margin: 16px 0;"] {
				margin: 0 !important;
			  }
			
			  body {
				width: 100% !important;
				height: 100% !important;
				padding: 0 !important;
				margin: 0 !important;
			  }
			
			  /**
			   * Collapse table borders to avoid space between cells.
			   */
			  table {
				border-collapse: collapse !important;
			  }
			
			  a {
				color: #1a82e2;
			  }
			
			  img {
				height: auto;
				line-height: 100%;
				text-decoration: none;
				border: 0;
				outline: none;
			  }
			  </style>
			
			</head>
			<body style="background-color: #e9ecef;">
			
			  <!-- start preheader -->
			  <!-- <div class="preheader" style="display: none; max-width: 0; max-height: 0; overflow: hidden; font-size: 1px; line-height: 1px; color: #fff; opacity: 0;">
				A preheader is the short summary text that follows the subject line when an email is viewed in the inbox.
			  </div> -->
			  <!-- end preheader -->
			
			  <!-- start body -->
			  <table border="0" cellpadding="0" cellspacing="0" width="100%">
			
				<!-- start logo -->
				<tr>
				<td align="center" bgcolor="#e9ecef">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="center" valign="top" style="padding: 5px;">
						  
						<img src="https://booknpay.com/assets/img/hubwallet-logo.png" alt="Logo" border="0" style="display: block; width: 50%; height:135px">
					   
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end logo -->
			
				<!-- start hero -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 36px 24px 0; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif;">
						  <h1 style="margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -1px; line-height: 48px;">Hi {Employee First Name},</h1>
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end hero -->
			
				<!-- start copy block -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
			
					  <!-- start copy -->
					
			
					  <tr>
							<td colspan="2" align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
							
							<p>Your schedule has been added! Please view your schedule on the app and let us know if there are any modifications.</p>
							
							</td>
			
						
					  </tr>
			
			
					 
			
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
						
						<p>
						  
						Cheers,<br/>
						{Business Name}<br/>
						</p>
			</td>
			</tr>
			
			
			
					  <!-- end copy -->
			
					  <!-- start button -->
					  
					  <!-- end button -->
			
					  <!-- start copy -->
					  
					  <!-- end copy -->
			
					  <!-- start copy -->
					  <!-- <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px; border-bottom: 3px solid #d4dadf">
						  <p style="margin: 0;">Cheers</p>
						</td>
					  </tr> -->
					  <!-- end copy -->
			
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end copy block -->
			
				<!-- start footer -->
				
				<!-- end footer -->
			
			  </table>
			  <!-- end body -->
			
			</body>
			</html>';

			$schedule_update = '<!DOCTYPE html>
			<html>
			<head>
			
			  <meta charset="utf-8">
			  <meta http-equiv="x-ua-compatible" content="ie=edge">
			  <title></title>
			  <meta name="viewport" content="width=device-width, initial-scale=1">
			  <style type="text/css">
			  /**
			   * Google webfonts. Recommended to include the .woff version for cross-client compatibility.
			   */
			  @media screen {
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 400;
				  src: local("Source Sans Pro Regular"), local("SourceSansPro-Regular"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/ODelI1aHBYDBqgeIAH2zlBM0YzuT7MdOe03otPbuUS0.woff) format("woff");
				}
			
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 700;
				  src: local("Source Sans Pro Bold"), local("SourceSansPro-Bold"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/toadOcfmlt9b38dHJxOBGFkQc6VGVFSmCnC_l7QZG60.woff) format("woff");
				}
			  }
			
			  /**
			   * Avoid browser level font resizing.
			   * 1. Windows Mobile
			   * 2. iOS / OSX
			   */
			  body,
			  table,
			  td,
			  a {
				-ms-text-size-adjust: 100%; /* 1 */
				-webkit-text-size-adjust: 100%; /* 2 */
			  }
			
			  /**
			   * Remove extra space added to tables and cells in Outlook.
			   */
			  table,
			  td {
				mso-table-rspace: 0pt;
				mso-table-lspace: 0pt;
			  }
			
			  /**
			   * Better fluid images in Internet Explorer.
			   */
			  img {
				-ms-interpolation-mode: bicubic;
			  }
			
			  /**
			   * Remove blue links for iOS devices.
			   */
			  a[x-apple-data-detectors] {
				font-family: inherit !important;
				font-size: inherit !important;
				font-weight: inherit !important;
				line-height: inherit !important;
				color: inherit !important;
				text-decoration: none !important;
			  }
			
			  /**
			   * Fix centering issues in Android 4.4.
			   */
			  div[style*="margin: 16px 0;"] {
				margin: 0 !important;
			  }
			
			  body {
				width: 100% !important;
				height: 100% !important;
				padding: 0 !important;
				margin: 0 !important;
			  }
			
			  /**
			   * Collapse table borders to avoid space between cells.
			   */
			  table {
				border-collapse: collapse !important;
			  }
			
			  a {
				color: #1a82e2;
			  }
			
			  img {
				height: auto;
				line-height: 100%;
				text-decoration: none;
				border: 0;
				outline: none;
			  }
			  </style>
			
			</head>
			<body style="background-color: #e9ecef;">
			
			  <!-- start preheader -->
			  <!-- <div class="preheader" style="display: none; max-width: 0; max-height: 0; overflow: hidden; font-size: 1px; line-height: 1px; color: #fff; opacity: 0;">
				A preheader is the short summary text that follows the subject line when an email is viewed in the inbox.
			  </div> -->
			  <!-- end preheader -->
			
			  <!-- start body -->
			  <table border="0" cellpadding="0" cellspacing="0" width="100%">
			
				<!-- start logo -->
				<tr>
				<td align="center" bgcolor="#e9ecef">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="center" valign="top" style="padding: 5px;">
						  
						<img src="https://booknpay.com/assets/img/hubwallet-logo.png" alt="Logo" border="0" style="display: block; width: 50%; height:135px">
					   
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end logo -->
			
				<!-- start hero -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 36px 24px 0; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif;">
						  <h1 style="margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -1px; line-height: 48px;">Hi {Employee First Name},</h1>
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end hero -->
			
				<!-- start copy block -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
			
					  <!-- start copy -->
					
			
					  <tr>
							<td colspan="2" align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
							
							<p>Your schedule has been updated! Please view your schedule on the app and let us know	if there are any modifications.</p>
							
							</td>
			
						
					  </tr>
			
			
					 
			
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
						
						<p>
						  
						Cheers,<br/>
						{Business Name}<br/>
						</p>
			</td>
			</tr>
			
			
			
					  <!-- end copy -->
			
					  <!-- start button -->
					  
					  <!-- end button -->
			
					  <!-- start copy -->
					  
					  <!-- end copy -->
			
					  <!-- start copy -->
					  <!-- <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px; border-bottom: 3px solid #d4dadf">
						  <p style="margin: 0;">Cheers</p>
						</td>
					  </tr> -->
					  <!-- end copy -->
			
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end copy block -->
			
				<!-- start footer -->
				
				<!-- end footer -->
			
			  </table>
			  <!-- end body -->
			
			</body>
			</html>';
			
			$delete_appointment = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$customer_birthday = '<!DOCTYPE html>
			<html>
			<head>
			
			  <meta charset="utf-8">
			  <meta http-equiv="x-ua-compatible" content="ie=edge">
			  <title></title>
			  <meta name="viewport" content="width=device-width, initial-scale=1">
			  <style type="text/css">
			  /**
			   * Google webfonts. Recommended to include the .woff version for cross-client compatibility.
			   */
			  @media screen {
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 400;
				  src: local("Source Sans Pro Regular"), local("SourceSansPro-Regular"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/ODelI1aHBYDBqgeIAH2zlBM0YzuT7MdOe03otPbuUS0.woff) format("woff");
				}
			
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 700;
				  src: local("Source Sans Pro Bold"), local("SourceSansPro-Bold"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/toadOcfmlt9b38dHJxOBGFkQc6VGVFSmCnC_l7QZG60.woff) format("woff");
				}
			  }
			
			  /**
			   * Avoid browser level font resizing.
			   * 1. Windows Mobile
			   * 2. iOS / OSX
			   */
			  body,
			  table,
			  td,
			  a {
				-ms-text-size-adjust: 100%; /* 1 */
				-webkit-text-size-adjust: 100%; /* 2 */
			  }
			
			  /**
			   * Remove extra space added to tables and cells in Outlook.
			   */
			  table,
			  td {
				mso-table-rspace: 0pt;
				mso-table-lspace: 0pt;
			  }
			
			  /**
			   * Better fluid images in Internet Explorer.
			   */
			  img {
				-ms-interpolation-mode: bicubic;
			  }
			
			  /**
			   * Remove blue links for iOS devices.
			   */
			  a[x-apple-data-detectors] {
				font-family: inherit !important;
				font-size: inherit !important;
				font-weight: inherit !important;
				line-height: inherit !important;
				color: inherit !important;
				text-decoration: none !important;
			  }
			
			  /**
			   * Fix centering issues in Android 4.4.
			   */
			  div[style*="margin: 16px 0;"] {
				margin: 0 !important;
			  }
			
			  body {
				width: 100% !important;
				height: 100% !important;
				padding: 0 !important;
				margin: 0 !important;
			  }
			
			  /**
			   * Collapse table borders to avoid space between cells.
			   */
			  table {
				border-collapse: collapse !important;
			  }
			
			  a {
				color: #1a82e2;
			  }
			
			  img {
				height: auto;
				line-height: 100%;
				text-decoration: none;
				border: 0;
				outline: none;
			  }
			  </style>
			
			</head>
			<body style="background-color: #e9ecef;">
			
			  <!-- start preheader -->
			  <!-- <div class="preheader" style="display: none; max-width: 0; max-height: 0; overflow: hidden; font-size: 1px; line-height: 1px; color: #fff; opacity: 0;">
				A preheader is the short summary text that follows the subject line when an email is viewed in the inbox.
			  </div> -->
			  <!-- end preheader -->
			
			  <!-- start body -->
			  <table border="0" cellpadding="0" cellspacing="0" width="100%">
			
				<!-- start logo -->
				<tr>
				<td align="center" bgcolor="#e9ecef">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="center" valign="top" style="padding: 5px;">
						  
						<img src="https://booknpay.com/assets/img/hubwallet-logo.png" alt="Logo" border="0" style="display: block; width: 50%; height:135px">
					   
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end logo -->
			
				<!-- start hero -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 36px 24px 0; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif;">
						  <h1 style="margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -1px; line-height: 48px;">Happy Birthday {Customer First Name}!</h1>
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end hero -->
			
				<!-- start copy block -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
			
					  <!-- start copy -->
					
			
					  <tr>
							<td colspan="2" align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
							
							<p>We want to wish you a very happy birthday! We hope you have a wonderful day and we look forward to seeing you soon!</p>
							
							</td>
			
						
					  </tr>
			
			
					 
			
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
						
						<p>
						  
						Cheers,<br/>
						{Business Name}<br/>
						{Business Phone Number}	
						</p>
			</td>
			</tr>
			
			
			
					  <!-- end copy -->
			
					  <!-- start button -->
					  
					  <!-- end button -->
			
					  <!-- start copy -->
					  
					  <!-- end copy -->
			
					  <!-- start copy -->
					  <!-- <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px; border-bottom: 3px solid #d4dadf">
						  <p style="margin: 0;">Cheers</p>
						</td>
					  </tr> -->
					  <!-- end copy -->
			
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end copy block -->
			
				<!-- start footer -->
				
				<!-- end footer -->
			
			  </table>
			  <!-- end body -->
			
			</body>
			</html>';
			
			$stylist_birthday = '<!DOCTYPE html>
			<html>
			<head>
			
			  <meta charset="utf-8">
			  <meta http-equiv="x-ua-compatible" content="ie=edge">
			  <title></title>
			  <meta name="viewport" content="width=device-width, initial-scale=1">
			  <style type="text/css">
			  /**
			   * Google webfonts. Recommended to include the .woff version for cross-client compatibility.
			   */
			  @media screen {
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 400;
				  src: local("Source Sans Pro Regular"), local("SourceSansPro-Regular"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/ODelI1aHBYDBqgeIAH2zlBM0YzuT7MdOe03otPbuUS0.woff) format("woff");
				}
			
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 700;
				  src: local("Source Sans Pro Bold"), local("SourceSansPro-Bold"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/toadOcfmlt9b38dHJxOBGFkQc6VGVFSmCnC_l7QZG60.woff) format("woff");
				}
			  }
			
			  /**
			   * Avoid browser level font resizing.
			   * 1. Windows Mobile
			   * 2. iOS / OSX
			   */
			  body,
			  table,
			  td,
			  a {
				-ms-text-size-adjust: 100%; /* 1 */
				-webkit-text-size-adjust: 100%; /* 2 */
			  }
			
			  /**
			   * Remove extra space added to tables and cells in Outlook.
			   */
			  table,
			  td {
				mso-table-rspace: 0pt;
				mso-table-lspace: 0pt;
			  }
			
			  /**
			   * Better fluid images in Internet Explorer.
			   */
			  img {
				-ms-interpolation-mode: bicubic;
			  }
			
			  /**
			   * Remove blue links for iOS devices.
			   */
			  a[x-apple-data-detectors] {
				font-family: inherit !important;
				font-size: inherit !important;
				font-weight: inherit !important;
				line-height: inherit !important;
				color: inherit !important;
				text-decoration: none !important;
			  }
			
			  /**
			   * Fix centering issues in Android 4.4.
			   */
			  div[style*="margin: 16px 0;"] {
				margin: 0 !important;
			  }
			
			  body {
				width: 100% !important;
				height: 100% !important;
				padding: 0 !important;
				margin: 0 !important;
			  }
			
			  /**
			   * Collapse table borders to avoid space between cells.
			   */
			  table {
				border-collapse: collapse !important;
			  }
			
			  a {
				color: #1a82e2;
			  }
			
			  img {
				height: auto;
				line-height: 100%;
				text-decoration: none;
				border: 0;
				outline: none;
			  }
			  </style>
			
			</head>
			<body style="background-color: #e9ecef;">
			
			  <!-- start preheader -->
			  <!-- <div class="preheader" style="display: none; max-width: 0; max-height: 0; overflow: hidden; font-size: 1px; line-height: 1px; color: #fff; opacity: 0;">
				A preheader is the short summary text that follows the subject line when an email is viewed in the inbox.
			  </div> -->
			  <!-- end preheader -->
			
			  <!-- start body -->
			  <table border="0" cellpadding="0" cellspacing="0" width="100%">
			
				<!-- start logo -->
				<tr>
				<td align="center" bgcolor="#e9ecef">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="center" valign="top" style="padding: 5px;">
						  
						<img src="https://booknpay.com/assets/img/hubwallet-logo.png" alt="Logo" border="0" style="display: block; width: 50%; height:135px">
					   
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end logo -->
			
				<!-- start hero -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 36px 24px 0; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif;">
						  <h1 style="margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -1px; line-height: 48px;">Happy Birthday {Employee First Name}!</h1>
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end hero -->
			
				<!-- start copy block -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
			
					  <!-- start copy -->
					
			
					  <tr>
							<td colspan="2" align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
							
							<p>We want to wish you a very happy birthday! We hope you have a wonderful day and can\'t wait to celebrate you!	</p>
							
							</td>
			
						
					  </tr>
			
			
					 
			
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
						
						<p>
						  
						Cheers,<br/>
						{Business Name}<br/>
						
						</p>
			</td>
			</tr>
			
			
			
					  <!-- end copy -->
			
					  <!-- start button -->
					  
					  <!-- end button -->
			
					  <!-- start copy -->
					  
					  <!-- end copy -->
			
					  <!-- start copy -->
					  <!-- <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px; border-bottom: 3px solid #d4dadf">
						  <p style="margin: 0;">Cheers</p>
						</td>
					  </tr> -->
					  <!-- end copy -->
			
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end copy block -->
			
				<!-- start footer -->
				
				<!-- end footer -->
			
			  </table>
			  <!-- end body -->
			
			</body>
			</html>';


			$po_order_sent_to_vendor = '<!DOCTYPE html>
			<html>
			<head>
			
			  <meta charset="utf-8">
			  <meta http-equiv="x-ua-compatible" content="ie=edge">
			  <title></title>
			  <meta name="viewport" content="width=device-width, initial-scale=1">
			  <style type="text/css">
			  /**
			   * Google webfonts. Recommended to include the .woff version for cross-client compatibility.
			   */
			  @media screen {
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 400;
				  src: local("Source Sans Pro Regular"), local("SourceSansPro-Regular"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/ODelI1aHBYDBqgeIAH2zlBM0YzuT7MdOe03otPbuUS0.woff) format("woff");
				}
			
				@font-face {
				  font-family: "Source Sans Pro";
				  font-style: normal;
				  font-weight: 700;
				  src: local("Source Sans Pro Bold"), local("SourceSansPro-Bold"), url(https://fonts.gstatic.com/s/sourcesanspro/v10/toadOcfmlt9b38dHJxOBGFkQc6VGVFSmCnC_l7QZG60.woff) format("woff");
				}
			  }
			
			  /**
			   * Avoid browser level font resizing.
			   * 1. Windows Mobile
			   * 2. iOS / OSX
			   */
			  body,
			  table,
			  td,
			  a {
				-ms-text-size-adjust: 100%; /* 1 */
				-webkit-text-size-adjust: 100%; /* 2 */
			  }
			
			  /**
			   * Remove extra space added to tables and cells in Outlook.
			   */
			  table,
			  td {
				mso-table-rspace: 0pt;
				mso-table-lspace: 0pt;
			  }
			
			  /**
			   * Better fluid images in Internet Explorer.
			   */
			  img {
				-ms-interpolation-mode: bicubic;
			  }
			
			  /**
			   * Remove blue links for iOS devices.
			   */
			  a[x-apple-data-detectors] {
				font-family: inherit !important;
				font-size: inherit !important;
				font-weight: inherit !important;
				line-height: inherit !important;
				color: inherit !important;
				text-decoration: none !important;
			  }
			
			  /**
			   * Fix centering issues in Android 4.4.
			   */
			  div[style*="margin: 16px 0;"] {
				margin: 0 !important;
			  }
			
			  body {
				width: 100% !important;
				height: 100% !important;
				padding: 0 !important;
				margin: 0 !important;
			  }
			
			  /**
			   * Collapse table borders to avoid space between cells.
			   */
			  table {
				border-collapse: collapse !important;
			  }
			
			  a {
				color: #1a82e2;
			  }
			
			  img {
				height: auto;
				line-height: 100%;
				text-decoration: none;
				border: 0;
				outline: none;
			  }
			  </style>
			
			</head>
			<body style="background-color: #e9ecef;">
			
			  <!-- start preheader -->
			  <!-- <div class="preheader" style="display: none; max-width: 0; max-height: 0; overflow: hidden; font-size: 1px; line-height: 1px; color: #fff; opacity: 0;">
				A preheader is the short summary text that follows the subject line when an email is viewed in the inbox.
			  </div> -->
			  <!-- end preheader -->
			
			  <!-- start body -->
			  <table border="0" cellpadding="0" cellspacing="0" width="100%">
			
				<!-- start logo -->
				<tr>
				<td align="center" bgcolor="#e9ecef">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="center" valign="top" style="padding: 5px;">
						  
						<img src="https://booknpay.com/assets/img/hubwallet-logo.png" alt="Logo" border="0" style="display: block; width: 50%; height:135px">
					   
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end logo -->
			
				<!-- start hero -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 36px 24px 0; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif;">
						  <h1 style="margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -1px; line-height: 48px;">Order for {Business Name}</h1>
						</td>
					  </tr>
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end hero -->
			
				<!-- start copy block -->
				<tr>
				  <td align="center" bgcolor="#ffffff">
					<!--[if (gte mso 9)|(IE)]>
					<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
					<tr>
					<td align="center" valign="top" width="600">
					<![endif]-->
					<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
			
					  <!-- start copy -->
					
			
					  <tr>
							<td colspan="2" align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
							
							<p>Hi {Vendor Name}</p>
							<p>I\'d like to place an order - please see the attached PO for details. 		</p>
			
								
					</td>
			
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
						<br/>
						
						</td>
					  </tr>
			
			
					 
			
					  <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
						
						<p>
						 
						Thank You<br/>
						{Business Name}<br/>
						{Business Phone Number}</p>
			</td>
			</tr>
			
			
			
					  <!-- end copy -->
			
					  <!-- start button -->
					  
					  <!-- end button -->
			
					  <!-- start copy -->
					  
					  <!-- end copy -->
			
					  <!-- start copy -->
					  <!-- <tr>
						<td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: "Source Sans Pro", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px; border-bottom: 3px solid #d4dadf">
						  <p style="margin: 0;">Cheers</p>
						</td>
					  </tr> -->
					  <!-- end copy -->
			
					</table>
					<!--[if (gte mso 9)|(IE)]>
					</td>
					</tr>
					</table>
					<![endif]-->
				  </td>
				</tr>
				<!-- end copy block -->
			
				<!-- start footer -->
				
				<!-- end footer -->
			
			  </table>
			  <!-- end body -->
			
			</body>
			</html>';
			
			$reward_point = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$group_appointment = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$multiple_appointment = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$employee_schedule_change = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$email_to_supplier_when_salon_create_po = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$email_to_supplier_when_salon_receive_po = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$email_to_salon_when_product_qty_low = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$email_to_customer_when_he_purchase_giftcard = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$email_to_customer_when_he_purchase_gift_certificate = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$stylist_forgot_password = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'customer_registration','Customer Registration','Welcome Email',$customer_registration,'1','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'new_appointment','New Appointment','New Appointment',$new_appointment,'3','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'appointment_reminder','Appointment Reminder','Appointment Reminder',$appointment_reminder,'3','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'stylist_registration','Stylist Registration','Stylist Registration',$stylist_registration,'2','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'customer_forgot_password','Forgot Password','Forgot Password',$customer_forgot_password,'1','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'appointment_update','Appointment Update','Appointment Update',$appointment_update,'3','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'confirmation_appointment','Appointment Confirmation','Appointment Confirmation',$confirmation_appointment,'3','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'checkin','Checkin','Checkin',$checkin,'3','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'checkout','Checkout','Checkout',$checkout,'3','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'cancel_appointment','Appointment Cancel','Appointment Cancel',$cancel_appointment,'3','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'delete_appointment','Delete Appointment','Delete Appointment',$delete_appointment,'3','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'customer_birthday','Customer Birthday','Customer Birthday',$customer_birthday,'1','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'stylist_birthday','Stylist Birthday','Stylist Birthday',$stylist_birthday,'2','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'reward_point','Reward Point Email','Reward Point',$reward_point,'3','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'group_appointment','Group Appointment','Group Appointment',$group_appointment,'3','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'multiple_appointment','Multiple Appointment','Multiple Appointment',$multiple_appointment,'3','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'employee_schedule_change','Employee change his/her schedule, then notification email to customer','Employee change his/her schedule, then notification email to customer',$employee_schedule_change,'2','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'email_to_supplier_when_salon_create_po','PO order sent to vendor','PO order sent to vendor',$po_order_sent_to_vendor,'4','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'email_to_supplier_when_salon_receive_po','Email to supplier when salon receive purchase order','Email to supplier when salon receive purchase order',$po_order_sent_to_vendor,'4','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'email_to_salon_when_product_qty_low','Email to salon when product quantity is low','Email to salon when product quantity is low',$email_to_salon_when_product_qty_low,'5','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'email_to_customer_when_he_purchase_giftcard','Email to customer when he/she purchase gift card','Email to customer when he/she purchase gift card',$email_to_customer_when_he_purchase_giftcard,'1','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'email_to_customer_when_he_purchase_gift_certificate','Email to customer when he purchase gift certificate','Email to customer when he purchase gift certificate',$email_to_customer_when_he_purchase_gift_certificate,'1','1');
			$this->login_model->updateEmailSettings($resultData->vendor_id,'stylist_forgot_password','Forgot Password','Forgot Password',$stylist_forgot_password,'2','1');

			$this->login_model->updateEmailSettings($resultData->vendor_id,'schedule_add','Your schedule is added','Add Schedule',$schedule_add,'2','1');

			$this->login_model->updateEmailSettings($resultData->vendor_id,'schedule_update','Your schedule is updated','Updated Schedule',$schedule_update,'2','1');

			$this->login_model->updateEmailSettings($resultData->vendor_id,'customer_birthday','Happy Birthday','Happy Birthday Birthday',$customer_birthday,'2','1');

			$this->login_model->updateEmailSettings($resultData->vendor_id,'stylist_birthday','Happy Birthday','Happy Birthday',$stylist_birthday,'2','1');



			$this->login_model->updateSmsSettings($resultData->vendor_id,'new_appointment',$new_appointment_sms,'3','1');
			
			$this->login_model->updateSmsSettings($resultData->vendor_id,'appointment_update',$update_appointment_sms,'3','1');
			$this->login_model->updateSmsSettings($resultData->vendor_id,'cancel_appointment',$appointment_cancellation_sms,'3','1');
			
			$this->login_model->updateSmsSettings($resultData->vendor_id,'confirmation_appointment',$appointment_confirmation_sms,'3','1');

			$this->login_model->updateSmsSettings($resultData->vendor_id,'customer_registration',$customer_registration_sms,'1','1');
			
			$this->login_model->updateSmsSettings($resultData->vendor_id,'customer_birthday',$customer_birthday_sms,'2','1');
			
			$this->login_model->updateSmsSettings($resultData->vendor_id,'stylist_registration',$employee_registration_sms,'2','1');
			$this->login_model->updateSmsSettings($resultData->vendor_id,'stylist_birthday',$employee_birthday_sms,'2','1');
			$this->login_model->updateSmsSettings($resultData->vendor_id,'schedule_add',$employee_schedule_add,'2','1');
			
			$this->login_model->updateSmsSettings($resultData->vendor_id,'schedule_update',$employee_schedule_update,'2','1');
			$this->login_model->updateSmsSettings($resultData->vendor_id,'email_to_admin_when_salon_receive_po',$admin_receive_po_sms,'5','1');

			

			



			
			
			$screen_lock_time=$this->db->query('select value from settings where vendor_id="'.$resultData->vendor_id.'" and field="screen_lock_time"')->row();
			if(!empty($screen_lock_time)){
				$screen_lock_time=$screen_lock_time->value;
			}else{
				$screen_lock_time='0';
			}
			 
			
			$getMachineIp=$this->db->query('select ip_address,port from  equipment where vendor_id="'.$resultData->vendor_id.'"')->row();
			if(!empty($getMachineIp)){
				$$getMachineIp=$getMachineIp;
			}else{
				$getMachineIp=(object) [];
			}
				//|| $device_id=='fbb4baa8a0221ba5' || $device_id=='ba5f52a170a86e4c'
				$getDeviceId=$this->db->query('select serial_no from licence where serial_no="'.$device_id.'" and status=1 and is_delete=0 ')->num_rows();
				$getVendorId=$this->db->query('select vendor_id from licence where serial_no="'.$device_id.'" and status=1 and is_delete=0 limit 1')->row();
				$this->db->query('delete from licence where serial_no="'.$device_id.'" and status=0');
				if($getDeviceId >=1 && $getVendorId->vendor_id==$resultData->vendor_id || $device_id=='e680f84cf9cb56d7' || $device_id=='ba5f52a170a86e4c'){
				$getStylistId=$this->db->query('select stylist_id from stylist where login_id="'.$resultData->login_id.'"')->row();
				if(!empty($getStylistId)){
					$current_date=date('Y-m-d');
					$checkAttendenceButton=$this->db->query("select button_type from attendance where attendance_date='".$current_date."' and type='0' and stylist_id='".$getStylistId->stylist_id."' and attendance_out_date IS NULL and attendance_out_time IS NULL order by attendance_id desc limit 1  ")->row();
					$checkShortButton=$this->db->query("select button_type from attendance where attendance_date='".$current_date."' and type='1' and stylist_id='".$getStylistId->stylist_id."' and attendance_out_date IS NULL and attendance_out_time IS NULL order by attendance_id desc limit 1  ")->row();
					$checkLongButton=$this->db->query("select button_type from attendance where attendance_date='".$current_date."' and type='2' and stylist_id='".$getStylistId->stylist_id."' and attendance_out_date IS NULL and attendance_out_time IS NULL order by attendance_id desc limit 1  ")->row();
					$response['checkAttendenceButton']=$checkAttendenceButton->button_type;
					$response['checkShortButton']=$checkShortButton->button_type;
					$response['checkLongButton']=$checkLongButton->button_type;
				}else{
					$response['checkAttendenceButton']=0;
					$response['checkShortButton']=0;
					$response['checkLongButton']=0;
				}
				$getScreenView=$this->db->query('select value from settings where field="screen_view" and vendor_id="'.$resultData->vendor_id.'"')->row();
				
				$response['status'] = 1;
				$response['message'] = 'Login successful';
				$response['stylist_id'] = $stylist_id;
				$response['role_id'] = $role_id;
				$response['data'] = $resultData;
				$response['screen_lock_time']=$screen_lock_time;
				$response['permission'] = $permission;
				$response['calendar_start_time'] = $this->db->query("select TIME_FORMAT(first_time,'%h:%i %p') as start_time, TIME_FORMAT(second_time,'%h:%i %p') as end_time from settings where field='calendar_start_end_time' AND vendor_id='".$resultData->vendor_id."'  ")->row();
				
				$response['employee_type'] = $this->db->query("select emp_type_id, type as employee_type from employee_type order by emp_type_id asc")->result();
				
				$response['employee_title'] = $this->db->query("select title_id, title_name from stylist_title order by title_id asc")->result();
				
				$response['states'] = $this->db->query("select id as state_id, name as state_name from states where country_id='231' order by id asc ")->result();
				
				$response['services'] = $this->db->query("select service_id, service_name from service where is_delete='0' AND vendor_id='".$resultData->vendor_id."' ")->result();
				
				$response['color_setting'] = $this->db->query("select color_id from color_settings where color_type='payment_pending' AND vendor_id='".$resultData->vendor_id."' ")->row();
				$response['getMachineIp']=$getMachineIp;
				$response['api_hit']='no';
				if(!empty($getScreenView)){
					$response['screen_view']=$getScreenView->value;
				}else{
					$response['screen_view']='employee_view';
				}

				/*}else{
					$response['status'] = 0;
					$response['message'] = 'Salon is not linked with any device';
				}*/
				}else if($getDeviceId >=1 && $getVendorId->vendor_id!=$resultData->vendor_id && $resultData->role_id !=3){
					$response['status'] = 0;
						$response['message'] = 'Device is linked with another business';
						$response['api_hit']='no';
				}else if($getDeviceId < 1 && $getVendorId->vendor_id!=$resultData->vendor_id && $resultData->role_id ==3){
						$response['status'] = 0;
						$response['message'] = 'Device is not linked with another business';
						$response['api_hit']='no';
				}

				else{
					$randomOtp=$this->intCodeRandom(4);
					$this->db->query('insert into licence set serial_no="'.$device_id.'",otp="'.$randomOtp.'",vendor_id="'.$resultData->vendor_id.'",status=0');
					 $data['business_info'] = $this->getSalonDetail($resultData->vendor_id);
					 $email= $data['business_info']->email;
					 $data['otp']=$randomOtp;
					 $data['device_id']=$device_id;
					$emailTemplate = $this->load->view('device_verification',$data,TRUE);
             	  	$subject = "Verification code";
             	  	$this->load->library('Send_mail');
					$this->send_mail->sendMail($email, $subject, $emailTemplate, $fileName=false, $filePath=false, $cc=false);
					$response['vendor_id']=$resultData->vendor_id;
					$response['device_id']=$device_id;
					$response['pin']=$pin;
					$response['api_hit']='yes';
					$response['message'] = 'Verification code sent to your registered email';

				}
			}else{
				$response['status'] = 0;
				$response['message'] = 'Wrong Pin';
				$response['api_hit']='no';

			}
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Enter Login Pin';
			$response['api_hit']='no';
		}
		
		echo json_encode($response);
	}
	public function getSalonDetail($vendor_id){

		$query = $this->db->query("select v.vendor_name as business_name, v.phone,l.email from vendor v inner join login as l on l.vendor_id=v.vendor_id where v.vendor_id='".$vendor_id."' ");
		$result = $query->row();
		return $result;
	}
	public function checkVerification(){
		$pin=$this->input->post('pin');
		$device_id=$this->input->post('device_id');
		$otp=$this->input->post('otp');
		$getOtp=$this->db->query('select otp from licence where serial_no="'.$device_id.'" and status=0 order by licence_id desc limit 1')->row();
		if($getOtp->otp==$otp){
			$this->db->query('update licence set status=1 where serial_no="'.$device_id.'" and status=0 ');
			$this->LoginPin($pin);
		}else{
			$response['status']=0;
			$response['message']='Wrong verification code';
			echo json_encode($response);
		}

	}


	/* public function updateTaxes($vendor_id){
			$current_date = date('Y-m-d');
			$query = $this->db->query("SELECT tax_id, start_date FROM `tax_product` WHERE vendor_id='".$vendor_id."' order by tax_id desc limit 0,3 ")();

			$future_tax = $query->result();
			foreach($future_tax as $ft){
				if($ft->start_date==$current_date){

					$this->db->query("update tax_product set type='current' where vendor_id='".$vendor_id."' and tax_id='".$ft->tax_id."' ");
				

				}
			}

			$query2 = $this->db->query("SELECT tax_id, start_date FROM `tax_product` WHERE vendor_id='".$vendor_id."' order by tax_id desc limit 3,3 ")();

			$future_tax = $query->result();
			foreach($future_tax as $ft){
				if($ft->start_date==$current_date){

					$this->db->query("update tax_product set type='current' where vendor_id='".$vendor_id."' and tax_id='".$ft->tax_id."' ");
				

				}
			}

			

	} */

	public function checkIfTaxExist($tax_for,$vendor_id){
		
			if($tax_for=='product'){
				
				$table = "tax_product";
			}elseif($tax_for=='service'){
				$table = "tax_service";
				
			}
			
			$query= $this->db->query("select tax_id from $table where vendor_id='".$vendor_id."' ");
			$num = $query->num_rows();
			
			return $num;
		
	}
	

	
	
	public function logout(){
		
		$login_id = $this->input->post('login_id');
		
		$response['status'] = 0;
		$response['message'] = '';
				
		if(!empty($login_id)){
			
			$update = $this->db->query("update login set is_login='0', last_login='".date('Y-m-d H:i:s')."' where login_id='".$login_id."'");
			if($update){
				
				$response['status'] = 1;
				$response['message'] = 'Logout successfully';
				
				
			}
		}else{
			
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing'; 
		}
		
		echo json_encode($response);
	}
	
	public function screenLocktime(){
		
		$vendor_id = $this->input->post('vendor_id');
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$locktime = 15;
				
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select value as locktime from settings where field='screen_lock_time' and vendor_id='".$vendor_id."' ");
			if($query->num_rows()>0){	
				$locktime = $query->row()->locktime;
				$response['status'] = 1;
				$response['active'] = 1;
				$response['locktime'] = $locktime;
				$response['warning_time'] = 5;
				$response['message'] = '';
				
			}else{
				
				$response['status'] = 0;
				$response['active'] = 0;
				$response['locktime'] = 0;
				$response['warning_time'] = 0;
				$response['message'] = '';
				
			}
				
				
			
		}else{
			
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing'; 
		}
		
		echo json_encode($response);
	}
	
	
	public function activateLicence(){
		
		$vendor_id = $this->input->post('vendor_id');
		$device_id = $this->input->post('device_id');
		$licence_key = $this->input->post('licence_key');
		
		$response['status'] = 0;
		$response['message'] = '';
				
		//if(!empty($vendor_id) && !empty($device_id) && !empty($licence_key)){
		if(!empty($licence_key)){
			
			$query = $this->db->query("select licence_id,status from licence where serial_no='".$device_id."' AND licence_key='".$licence_key."'  ");
			
			//$query = $this->db->query("select licence_id,status from licence where  licence_key='".$licence_key."'  ");
			
			if($query->num_rows()>0){
				
				$res = $query->row();
				if($res->status==1){
					$response['status'] = 1;
					$response['message'] = "App is activated";
				}
				elseif($res->status==2){
					$response['status'] = 0;
					$response['message'] = "Account is on hold";
				}
				elseif($res->status==3){
					$response['status'] = 0;
					$response['message'] = "App is suspended";
				}
				elseif($res->status==4){
					$response['status'] = 0;
					$response['message'] = "App is closed";
				}else{
					$response['status'] = 0;
					$response['message'] = "App is not activate";
				}
				
			}else{
				
				$response['status'] = 0;
				$response['message'] = 'You entered wrong license key';
				
			}
				
				
			
		}else{
			
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing'; 
		}
		
		echo json_encode($response);
	}
	
		function intCodeRandom($length)
        {
          $intMin = (10 ** $length) / 10; // 100...
          $intMax = (10 ** $length) - 1;  // 999...

          $codeRandom = mt_rand($intMin, $intMax);

          return $codeRandom;
        }

		private function generateRandomNumber($numberRand)
		{
			//To Pull 7 Unique Random Values Out Of AlphaNumeric
			//removed number 0, capital o, number 1 and small L
			//Total: keys = 32, elements = 33
			$characters = array(
				"A","B","C","D","E","F","G","H","J","K","L","M","N","P","Q","R","S","T","U","V","W","X","Y","Z","1","2","3","4","5","6","7","8","9"
			);
	  
			$keys = array();
			while (count($keys) < $numberRand)
			{
				$x = mt_rand(0, count($characters) - 1);
				if (!in_array($x, $keys))
				{
					$keys[] = $x;
				}
			}
			$random_chars = '';
			foreach ($keys as $key)
			{
				$random_chars .= $characters[$key];
			}
			return $random_chars;
		}


	public function forgotPassword(){
		$email=$this->input->post('email');
		$device_id=$this->input->post('device_id'); 
		if(!empty($email)){

			$getVendor = $this->db->query("select vendor_id from licence where serial_no='".$device_id."' ")->row();
			$vendor_id = $getVendor->vendor_id;
			$checkEmailExsist=$this->db->query('select login_id,email,pin,role_id from login where email="'.$email.'" and vendor_id="'.$vendor_id.'" and role_id!=2 ')->row();
			//echo "<pre>";print_r($checkEmailExsist);exit;
			if(!empty($checkEmailExsist->email)){
				if($checkEmailExsist->role_id==1){
					$getDetail=$this->db->query('select vendor_name as name from vendor where login_id="'.$checkEmailExsist->login_id.'"')->row();
				}else{
					$getDetail=$this->db->query('select concat(firstname," ",lastname) as name from stylist where login_id="'.$checkEmailExsist->login_id.'"')->row();
				}
				$generateTokenhash = md5($this->generateRandomNumber(7));
				$update_key = $this->db->query("update login set confirmkey2='".$generateTokenhash."' where email='".$email."' ");
				$data['name']=$getDetail->name;
				$data['pin']=$checkEmailExsist->pin;
				$data['email']=$email;
				$data['confirmkey']=$generateTokenhash;
					$receiver_email = $email;
                  // $sender_email = 'info@booknpay.com';
                   $initial_time = time();
                   // The mail sending protocol.
                  /*  $config = Array(
                     'protocol' => 'sendmail',
                     'smtp_host' => 'smtp.gmail.com',
                     'validation'=>TRUE,
                     'smtp_timeout'=>30,
                     'smtp_port' => 25,
                     'smtp_user' => 'booknpaysalon@gmail.com', // change it to yours
                     'smtp_pass' => 'bnp@2019$$', // change it to yours
                     'mailtype' => 'html',
                     'mailtype' => 'html',
   				  'charset' => 'iso-8859-1'
                   ); */

                   /* $this->load->library('email');
                   $this->email->initialize($config);
                   $this->email->from('info@booknpay.com', 'Hubwallet');
                   $this->email->to($receiver_email);
                   $this->email->subject('Forgot Pin');
                   
                    $emailTemplate = $this->load->view('forgotpassword',$data,TRUE);
					
                   $this->email->message($emailTemplate);
                   if($this->email->send()){ */

					$emailTemplate = $this->load->view('forgotpassword',$data,TRUE);
				//	$message = "this is test msg";
					$subject = "Forgot PIN";
					$this->load->library('Send_mail');
					$this->send_mail->sendMail($email, $subject, $emailTemplate, $fileName=false, $filePath=false, $cc=false);
					   
                   	$response['status'] = 1; 
					$response['message'] = 'Your pin successfully sent to your registered email'; 
                   /* }else{
                  	
                   	$response['status'] = 0; 
						$response['message'] ='Mail not sent'; 
                   } */
				 //  echo $this->email->print_debugger();die;
   				
			}else{
				$response['status'] = 0; 
				$response['message'] = 'Email does not exist'; 
			}
		}
		else{
			
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing'; 
		}
		
		echo json_encode($response);
	}
	public function checkMail(){
		
		
				
					$receiver_email = $email;
                   $sender_email = 'info@booknpay.com';
                   $initial_time = time();
                   // The mail sending protocol.
                   $config = Array(
                     'protocol' => 'sendmail',
                     'smtp_host' => 'smtp.gmail.com',
                     'validation'=>TRUE,
                     'smtp_timeout'=>30,
                     'smtp_port' => 25,
                     'smtp_user' => 'booknpaysalon@gmail.com', // change it to yours
                     'smtp_pass' => 'bnp@2019$$', // change it to yours
                     'mailtype' => 'html',
                     'mailtype' => 'html',
   				  'charset' => 'iso-8859-1'
                   );

                   $this->load->library('email');
                   $this->email->initialize($config);
                   $this->email->from('info@booknpay.com', 'Hubwallet');
                   $this->email->to($receiver_email);
                   $this->email->subject('Forgot Pin');
                   
                    $emailTemplate = "harsh test";
					
                   $this->email->message($emailTemplate);
                   if($this->email->send()){
					   
                   	$response['status'] = 1; 
					$response['message'] = 'Pin sent to your registered email'; 
                   }else{
                  	
                   	$response['status'] = 0; 
						$response['message'] ='Mail not sent'; 
                   }
				   echo $this->email->print_debugger();die;
   				
			
		
		//echo json_encode($response);
	}

}
