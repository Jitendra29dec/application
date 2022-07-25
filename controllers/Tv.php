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

class Tv extends CI_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
		
		//error_reporting(0);
    }
	
	public function waitingList(){
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$response['status'] = 1;
			$response['message'] = '';
		
		$current_date = date('Y-m-d');
		$response['now_serving'] = $this->db->query("select CONCAT(c.firstname,' ',c.lastname) as customer_name, CONCAT(s.firstname,' ',s.lastname) as stylist_name, aps.appointment_time, aps.duration from appointment a INNER JOIN appointment_service aps ON aps.appointment_id=a.appointment_id INNER JOIN customer c ON c.customer_id=a.customer_id INNER JOIN stylist s ON s.stylist_id=aps.stylist_id where a.vendor_id='".$vendor_id."' AND a.is_checkin=1 limit 0,3")->result();
		
		$response['waitlist'] = $this->db->query("select CONCAT(c.firstname,' ',LEFT(c.lastname,1)) as customer_name, CONCAT(s.firstname,' ',s.lastname) as stylist_name, TIME_FORMAT(aps.appointment_time,'%h:%i %p') as appointment_time, CONCAT(aps.duration,' Min') as duration from appointment a INNER JOIN appointment_service aps ON aps.appointment_id=a.appointment_id INNER JOIN customer c ON c.customer_id=a.customer_id INNER JOIN stylist s ON s.stylist_id=aps.stylist_id where a.vendor_id='".$vendor_id."' AND a.is_checkin='1' AND a.is_checkout='0' AND a.date='".$current_date."' ORDER BY aps.appointment_time ASC limit 0,6 ")->result();
		//TIME_FORMAT('17:20:25', '%r')
		$response['upcomming_apponitment'] = $this->db->query("select CONCAT(c.firstname,' ',LEFT(c.lastname,1)) as customer_name, CONCAT(s.firstname,' ',s.lastname) as stylist_name, TIME_FORMAT(aps.appointment_time,'%h:%i %p') as appointment_time, CONCAT(aps.duration,' Min') as duration from appointment a INNER JOIN appointment_service aps ON aps.appointment_id=a.appointment_id INNER JOIN customer c ON c.customer_id=a.customer_id INNER JOIN stylist s ON s.stylist_id=aps.stylist_id where a.vendor_id='".$vendor_id."' AND a.is_checkin='0' AND a.is_checkout='0' AND a.date='".$current_date."' ORDER BY aps.appointment_time ASC limit 0,6 ")->result();
		
		
		
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
        echo json_encode($response);	
	}		
	function getCustomerById($id){
		
		$query = $this->db->query("select l.email,CONCAT(c.firstname,' ',c.lastname) as customer_name,c.address,st.name,c.pincode,c.city,v.vendor_name,v.phone,l.fcm_token from login l INNER JOIN customer c ON c.login_id=l.login_id inner join vendor as v on l.vendor_id=v.vendor_id left join states as st on st.id=c.state_id where c.customer_id='".$id."'");
		$result = $query->row();
		return $result;
		
	}
	
	public function sendMail(){
		$customer_detail = $this->getCustomerById($customer_id);
							
					          $customer_name = $customer_detail->customer_name;
					          $to = $customer_detail->email;
					         // echo $to;die;
							if(!empty($service_id1)){
							//echo "test";die;
					          $data['customer_name'] =  $customer_name; 
					          $data['appointment_date'] = $appointment_date; 
					          $data['appointment_time'] = $appointment_time; 
					          //$data['service'] = $service; 
					          
					         // $appointment_time = $start_time.'  '.$endtime_new;
					         // $appointment_date = date('M d Y',strtotime($appointment_date));
					          /*$config = Array(    
					        'protocol' => 'sendmail',
					        'smtp_host' => 'smtp.gmail.com',
					        'smtp_port' => 25,
					        'smtp_user' => 'booknpaysalon@gmail.com',
					        'smtp_pass' => 'bnp@2019$$',
					        'smtp_timeout' => '4',
					        'mailtype' => 'html',
					        'charset' => 'iso-8859-1'
					      );
					   
					      $this->load->library('email', $config); // Load email template
					      $this->email->set_newline("\r\n");
					      $this->email->from('info@booknpay.com', 'BookNPay');*/
					      /*$stylist_info = $this->getStylistById($stylist_id);
					          $stylist_email = $stylist_info->email;
					          $stylist_name = $stylist_info->stylist_name;
					          $min = $duration;
					      $endtime_new = strtotime("+$min minutes",strtotime($appointment_time));
					      if($appointment_time >'11:59'){
					            
					            $time_format = 'PM';
					          }ELSE{
					            
					            $time_format = 'AM';
					          }*/
					          $getAppointmentData=$this->db->query("select group_concat(concat(DAYNAME(a.date),' - ',date_format(a.date,'%m-%d-%Y')) SEPARATOR  ' /n ') as date,group_concat(time_format(a.date,'%m-%d-%Y')) as newDate,group_concat(concat(sr.service_name,' with ', concat(st.firstname,' ',st.lastname)) SEPARATOR  '-') as service_name,group_concat(aps.duration SEPARATOR  ' /n ') as duration,group_concat(time_format(aps.appointment_time,'%H:%i %p') SEPARATOR  ' /n ') as ap_time,group_concat(time_format(aps.appointment_time,'%H:%i %p')) as new_time,group_concat(concat(st.firstname,'',st.lastname)) as stylist_name from appointment as a inner join appointment_service as aps on aps.appointment_id=a.appointment_id inner join service as sr on sr.service_id=aps.service_id inner join stylist as st on st.stylist_id=aps.stylist_id where a.token_no='103'")->row();
  							$getCancellationPolicy=$this->db->query('select policy_text from cancellation_policy where apt_type=1 and vendor_id="10"')->row();
					          $data['getData'] = array(
					        'stylist_name'=> $getAppointmentData->stylist_name,
					        'customer_name'=> $customer_name,
					        'customer_email'=> $to,
					        'store_name'=> $customer_detail->vendor_name,
					        'city'=> $customer_detail->city,
					        'state'=>$customer_detail->name,
					        'zipcode'=> $customer_detail->picode,
					        
					        'start_time'=> $appointment_time,
					        'end_time'=> $endtime_new,
					        'appointment_date'=> $getAppointmentData->date,
						        'service_name'=> $getAppointmentData->service_name,
						        'start_time'=> $getAppointmentData->ap_time,
						        'business_phone'=>$customer_detail->phone,
						       'policy'=>$getCancellationPolicy->policy_text,
					        	'newdate'=>$getAppointmentData->newDate,
					        	'newTime'=>$getAppointmentData->new_time,
					        
					   
					           );

					     $newAppointemnt=$this->db->query('select email_subject,email_content from email_settings where slug="new_appointment" and is_active=1 and vendor_id="10"')->row();
					     //echo "<pre>";print_r($newAppointemnt);exit;
					      if(!empty($newAppointemnt)){
					      	$data['template_type']='new_appointment';
					      	$data['vendor_id']=$vendor_id;
					      //	$this->load->library('email');
                  			// $this->email->initialize($config);
                  			// $this->email->from('info@booknpay.com', 'Hubwallet');
					        //$data['new_content']=$newAppointemnt->email_content;
					      //  $this->email->to($to); // replace it with receiver email id
					     // $this->email->subject($newAppointemnt->email_subject); // replace it with email subject
					      	$businessName=$this->db->query('select vendor_name from vendor where vendor_id="10"')->row();
					      	$subject=str_replace('{Business Name}',$businessName->vendor_name,$newAppointemnt->email_subject);
					      $message = $this->load->view('email_template/new_appointment',$data,TRUE);
					      $this->load->library('Send_mail');
						  $this->send_mail->sendMail('vardhanharsh824@gmail.com', $subject, $message, $fileName=false, $filePath=false, $cc=false);
					      }
					  }
	}
	
	public function sendSms(){
		$msg = "Hello test sms by code";
		$phone = "+14132135672";
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://api.twilio.com/2010-04-01/Accounts/AC50f42ad7e951316054995622f3937c96/Messages.json',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => "To=$phone&MessagingServiceSid=MG57ffc0554ac1203ecb82affcc611fda1&Body=$msg",
		  CURLOPT_HTTPHEADER => array(
		    'Authorization: Basic QUM1MGY0MmFkN2U5NTEzMTYwNTQ5OTU2MjJmMzkzN2M5Njo2ZWJlODk5NmZhY2Q3NzYyNTI0YzQ2ZmNjZDMwOWNiYQ==',
		    'Content-Type: application/x-www-form-urlencoded'
		  ),
		));

		$response = curl_exec($curl);
		curl_close($curl);
//		echo $response;




	}

	public function testing(){

		$current_date = date('Y-n-j');

		echo $current_date;die;
	}
}