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
						$response['message'] ='Mail not sent!'; 
                   }
                  }else{
                  	$response['status'] = 0; 
						$response['message'] = 'Wrong Pin !';
                  } 
				 //  echo $this->email->print_debugger();die;
   				
			}else{
				$response['status'] = 0; 
				$response['message'] = 'Email does not exists!'; 
			}
		}
		else{
			
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing!'; 
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
				$response['message'] = 'Login successfully!';
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
				$response['message'] = 'Wrong Username or Password!';
			}
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		
		echo json_encode($response);
	}
	function LoginPin($pin_new=0){
		if($pin==0 || $pin==''){
			$pin = $this->input->post('pin');
		}else{
			$pin=$pin_new;
		}
		//echo "harrs".$pin;exit;
		$device_id= $this->input->post('device_id');
		$checkData=$this->db->query('select vendor_id from licence where serial_no="'.$device_id.'" and status=1 and is_delete=0 limit 1')->row();

		if(count($checkData) >=1 && $device_id!='fbb4baa8a0221ba5' && $device_id!='ba5f52a170a86e4c'){
			///$join='inner join licence as lnc on l.vendor_id=lnc.vendor_id';
			///$con='inner join licence as lnc on l.vendor_id=lnc.vendor_id';
			$con='and l.vendor_id="'.$checkData->vendor_id.'"';
		}else{
			$con="";
		}
		$response['status'] = 0;
		$path = "https://".$_SERVER['HTTP_HOST'].'/assets/img/client';
		
		if(!empty($pin) ){
			//$this->db->query('update licence set app_type=2,licence_key="'.$device_id.'" where vendor_id=10');
		$checkOtp=$this->db->query('select otp from login where pin="'.$pin.'"')->row();
	/*	if($checkOtp ==$otp){*/
	//	echo "select l.login_id, CONCAT(' ',v.vendor_name) as username, CONCAT('$path','/',v.photo) as photo, l.fcm_token, l.email,l.role_id, l.last_login, l.vendor_id from login l  INNER JOIN vendor v ON v.vendor_id=l.vendor_id  where l.pin='".$pin."' and (l.role_id='1' OR l.role_id='3')  and l.is_active='1' and l.is_delete='0'  ";die;
		$query = $this->db->query("select l.login_id, CONCAT(' ',v.vendor_name) as username, CONCAT('$path','/',v.photo) as photo, l.fcm_token, l.email,l.role_id, l.last_login, l.vendor_id from login l  INNER JOIN vendor v ON v.vendor_id=l.vendor_id  where l.pin='".$pin."' and (l.role_id='1' OR l.role_id='3')  and l.is_active='1' and l.is_delete='0' ".$con." ");

			//$resultData->username
			if($query->num_rows()>0){
				$resultData = $query->row();
				/*if($device_id==$resultData->device_id){*/
				
					if($resultData->role_id=='3'){
						$getUsername=$this->db->query('select concat(firstname," ",lastname) as username from stylist where login_id="'.$resultData->login_id.'"')->row();
						$response['username']=$getUsername->username;

					}else{
						$response['username']='Owner';
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
			$this->login_model->updateEquipment($resultData->vendor_id);
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
			
			
			$customer_registration = "Dear Sir/Mam,<br />Thank You Register With Ous Your Email Id Is: booknpay@gmail.com<br />testing<br />Thank You<br />Team Booknpay";
			
			$new_appointment = '';
			
			$appointment_reminder = '<p><br />
										Dear {name},<br />
										<br />
										You have made an appointment with {store}.<br />
										<br />
										Your appointment schedule is:<br />
										<br />
										Appointment Date : {appointment_date}<br />
										Appointment Time:&nbsp; {appointment_time}<br />
										<br />
										<br />
										Thank You<br />
										Team {store}</p>
										';
			
			$stylist_registration = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			
			$customer_forgot_password = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$appointment_update = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$confirmation_appointment = '<h2>Dear {name}</h2>

										<p>You are registered with {stylist} in {store}.<br />
										<br />
										Your login credential are as bellow:<br />
										<br />
										Email:&nbsp; &nbsp; &nbsp; &nbsp; {email_val}<br />
										<br />
										<br />
										Thank You<br />
										<br />
										Team {store}</p>

										<p>&nbsp;</p>
										';
										
										
			$no_show = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$checkin = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$checkout = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$cancel_appointment = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$delete_appointment = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$customer_birthday = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
			$stylist_birthday = 'Dear {name},<br /><br />You are registered with {store_name} as stylist.<br /><br />Your login credential are as bellow:<br /><br />Email:&nbsp; &nbsp; &nbsp; &nbsp; {email}<br />Password:&nbsp; {password}<br /><br />Thank You<br /><br />Team {store_name}';
			
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
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'email_to_supplier_when_salon_create_po','Email to supplier when salon create new purchase order','Email to supplier when salon create new purchase order',$email_to_supplier_when_salon_create_po,'4','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'email_to_supplier_when_salon_receive_po','Email to supplier when salon receive purchase order','Email to supplier when salon receive purchase order',$email_to_supplier_when_salon_receive_po,'4','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'email_to_salon_when_product_qty_low','Email to salon when product quantity is low','Email to salon when product quantity is low',$email_to_salon_when_product_qty_low,'5','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'email_to_customer_when_he_purchase_giftcard','Email to customer when he/she purchase gift card','Email to customer when he/she purchase gift card',$email_to_customer_when_he_purchase_giftcard,'1','1');
			
			$this->login_model->updateEmailSettings($resultData->vendor_id,'email_to_customer_when_he_purchase_gift_certificate','Email to customer when he purchase gift certificate','Email to customer when he purchase gift certificate',$email_to_customer_when_he_purchase_gift_certificate,'1','1');
			$this->login_model->updateEmailSettings($resultData->vendor_id,'stylist_forgot_password','Forgot Password','Forgot Password',$stylist_forgot_password,'2','1');
			
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
				
				$getDeviceId=$this->db->query('select serial_no from licence where serial_no="'.$device_id.'" and status=1 and is_delete=0 ')->num_rows();
				$getVendorId=$this->db->query('select vendor_id from licence where serial_no="'.$device_id.'" and status=1 and is_delete=0 limit 1')->row();
				$this->db->query('delete from licence where serial_no="'.$device_id.'" and status=0');
				if($getDeviceId >=1 && $getVendorId->vendor_id==$resultData->vendor_id || $device_id=='fbb4baa8a0221ba5' || $device_id=='ba5f52a170a86e4c'){
				

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
				/*}else{
					$response['status'] = 0;
					$response['message'] = 'Salon is not linked with any device';
				}*/
				}else if($getDeviceId >=1 && $getVendorId->vendor_id!=$resultData->vendor_id && $resultData->role_id !=3){
					$response['status'] = 0;
						$response['message'] = 'Device already linked with another salon';
						$response['api_hit']='no';
				}else if($getDeviceId < 1 && $getVendorId->vendor_id!=$resultData->vendor_id && $resultData->role_id ==3){
						$response['status'] = 0;
						$response['message'] = 'Device is not linked with any salon';
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
					$response['message'] = 'Verification code sent on your registered email';

				}
			}else{
				$response['status'] = 0;
				$response['message'] = 'Wrong Pin !';
				$response['api_hit']='no';

			}
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Please enter login pin!';
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
			$response['message']='You have entered wrong verification code';
			echo json_encode($response);
		}

	}

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
				$response['message'] = 'Logout successfully!';
				
				
			}
		}else{
			
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing!'; 
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
			$response['message'] = 'Required parameter missing!'; 
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
					$response['message'] = "App is activated!";
				}
				elseif($res->status==2){
					$response['status'] = 0;
					$response['message'] = "App is on hold!";
				}
				elseif($res->status==3){
					$response['status'] = 0;
					$response['message'] = "App is suspended!";
				}
				elseif($res->status==4){
					$response['status'] = 0;
					$response['message'] = "App is closed!";
				}else{
					$response['status'] = 0;
					$response['message'] = "App is not activate!";
				}
				
			}else{
				
				$response['status'] = 0;
				$response['message'] = 'You entered wrong license key!';
				
			}
				
				
			
		}else{
			
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing!'; 
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
	public function forgotPassword(){
		$email=$this->input->post('email');
		if(!empty($email)){
			$checkEmailExsist=$this->db->query('select login_id,email,pin,role_id from login where email="'.$email.'" and role_id!=2 ')->row();
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
                   
                    $emailTemplate = $this->load->view('forgotpassword',$data,TRUE);
					
                   $this->email->message($emailTemplate);
                   if($this->email->send()){
					   
                   	$response['status'] = 1; 
					$response['message'] = 'Your pin successfully sent to your registered email'; 
                   }else{
                  	
                   	$response['status'] = 0; 
						$response['message'] ='Mail not sent!'; 
                   }
				 //  echo $this->email->print_debugger();die;
   				
			}else{
				$response['status'] = 0; 
				$response['message'] = 'Email does not exists!'; 
			}
		}
		else{
			
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing!'; 
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
					$response['message'] = 'Your pin successfully sent to your registered email'; 
                   }else{
                  	
                   	$response['status'] = 0; 
						$response['message'] ='Mail not sent!'; 
                   }
				   echo $this->email->print_debugger();die;
   				
			
		
		//echo json_encode($response);
	}

}
