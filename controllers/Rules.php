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


class Rules extends CI_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
		$this->load->helper('db');
		//error_reporting(0);
    }
	
	function notification(){
			
			$vendor_id=$this->input->post('vendor_id');
			$date=date('Y-m-d');
			 $low_quantity=$this->db->query('select product_id from product where quantity <=low_qty_warning and vendor_id="'.$vendor_id.'"')->num_rows();
			 $unconfirm_count = $this->db->query("select count(a.appointment_id) as num from appointment a inner join appointment_service aps on aps.appointment_id=a.appointment_id inner join service s on s.service_id=aps.service_id inner join customer c on c.customer_id=a.customer_id inner join stylist st on aps.stylist_id=st.stylist_id where a.status=1 and a.vendor_id='".$vendor_id."' and a.is_delete=0 ")->row();
			 $event_payment_count=$this->db->query('select count(B.id) as num from deposit_installment as B inner join deposit_customer as A on A.id=B.deposit_id inner join customer as c on A.customer_id=c.customer_id where A.vendor_id="'.$vendor_id.'" and B.is_active=0 and B.deposit_date="'.$date.'"')->row();
			 $cancel_count=$this->db->query('select count(id) as num from  customer_cancellation_amount  where vendor_id="'.$vendor_id.'" and status=0')->row();
			//  $total_count=$low_quantity + $event_payment_count + $cancel_count;
			//  $this->sendNotificationNew($vendor_id,'notification_count',$total_count);
			$response['status'] = 1;
			$response['low_quantity_count']=$low_quantity;
			$response['unconfirm_count']=$unconfirm_count->num;
			$response['event_payment_count']=$event_payment_count->num;
			$response['customer_cancel_count']=$cancel_count->num;
			$response['message'] = 'Data found';
		
		
		echo json_encode($response);
	}
	/*function notification_tab(){
			
			$vendor_id=$this->input->post('vendor_id');
			$date=date('Y-m-d');
			 $low_quantity=$this->db->query('select product_id from product where quantity <=low_qty_warning and vendor_id="'.$vendor_id.'"')->num_rows();
			 $unconfirm_count = $this->db->query("select count(a.appointment_id) as num from appointment a inner join appointment_service aps on aps.appointment_id=a.appointment_id inner join service s on s.service_id=aps.service_id inner join customer c on c.customer_id=a.customer_id inner join stylist st on aps.stylist_id=st.stylist_id where a.status=1 and a.vendor_id='".$vendor_id."' and a.is_delete=0 ")->row();
			 $event_payment_count=$this->db->query('select count(B.id) as num from deposit_installment as B inner join deposit_customer as A on A.id=B.deposit_id inner join customer as c on A.customer_id=c.customer_id where A.vendor_id="'.$vendor_id.'" and B.is_active=0 and B.deposit_date="'.$date.'"')->row();
			 $cancel_count=$this->db->query('select count(id) as num from  customer_cancellation_amount  where vendor_id="'.$vendor_id.'" and status=0')->row();
			 $total_count=$low_quantity + $unconfirm_count +
			$response['status'] = 1;
			$response['low_quantity_count']=$low_quantity;
			$response['unconfirm_count']=$unconfirm_count->num;
			$response['event_payment_count']=$event_payment_count->num;
			$response['customer_cancel_count']=$cancel_count->num;
			$response['message'] = 'Data found';
		
		
		echo json_encode($response);
	}*/

	public function sendNotificationNew($vendor_id,$text,$message){
		$getFcmToken=$this->db->query('select l.login_id,l.fcm_token from vendor as v inner join login as l on l.login_id=v.login_id where v.vendor_id="'.$vendor_id.'"')->row();

		$fcm_responce2=$this->sendfcm->sendNotification($title,$message,array($getFcmToken->fcm_token),$subtitle);
			

	}
	function checkPax(){
		$api = new CloudMessageServiceApi("https://api.paxstore.us/p-market-api", "459HJB236JNZH5XQN80Z","CCM7HMZLKE7M8BF9TT4G4U9M37PSA702R2QXXQOQ");
		echo "<pre>";print_r($api);
	}
	function getPaymentNotificationData(){
		$vendor_id=$this->input->post('vendor_id');
		$date=date('Y-m-d');
		if(!empty($vendor_id)){
		
		$getData=$this->db->query('select B.id,concat(c.firstname," ",c.lastname) as customer_name,A.event_name,B.amount from deposit_installment as B inner join deposit_customer as A on A.id=B.deposit_id inner join customer as c on A.customer_id=c.customer_id  where A.vendor_id="'.$vendor_id.'" and B.is_active=0 and B.deposit_date="'.$date.'"')->result();
		 $getServiceCharge=$this->db->query('select cash_discount_percentage as value,cash_discount_display_name as display_name from vendor where cash_discount_is_active="1" and vendor_id="'.$vendor_id.'"')->row();
		if(!empty($getData)){
			$response['status']=1;
			$response['deposit_data']=$getData;
			$response['getServiceCharge']=$getServiceCharge;
			$response['message']='Data found';
		}else{
			$response['status']=0;
			$response['desosit_data']=array();
			$response['getServiceCharge']=$getServiceCharge;
			$response['message']='No data found';

		}
		
	}else{
		$response['status']=0;
		$response['desposit_data']=array();
		$response['message']='Required parameter missing!';
	}
	echo json_encode($response);
 }
	public function low_quantity_list(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$low_quantity=$this->db->query('select product_id,product_name,sku,quantity,low_qty_warning from product where quantity<=low_qty_warning and vendor_id="'.$vendor_id.'"')->result();
		if(!empty($low_quantity)){
			$response['status']=1;
			$response['data']=$low_quantity;
			$response['message'] = 'Product list'; 
		}else{
			$response['status']=0;
			$response['data']=array();
			$response['message'] = 'Product list not available'; 
		}
	}else{
		$response['status']=0;
			$response['message'] = 'Required parameter missing!'; 
	}
	echo json_encode($response);
	}
	public function unconfirmApt(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$data=$this->db->query('select a.appointment_id,a.token_no,a.appointment_type, s.service_name,a.date as apt_date,time_format(aps.appointment_time,"%h:%i %p") as appointment_time,time_format(aps.appointment_end_time,"%h:%i %p") as appointment_end_time, CONCAT(c.firstname," ",c.lastname) as customer_name, c.mobile_phone as phone, CONCAT(st.firstname," ",st.lastname) as stylist_name from appointment a inner join appointment_service aps on aps.appointment_id=a.appointment_id inner join service s on s.service_id=aps.service_id inner join customer c on c.customer_id=a.customer_id inner join stylist st on aps.stylist_id=st.stylist_id where a.status=1 and a.vendor_id="'.$vendor_id.'" and a.is_delete=0 ')->result();

		if(!empty($data)){
			$response['status']=1;
			$response['data']=$data;
			$response['message'] = 'Unconfirmed list'; 
		}else{
			$response['status']=0;
			$response['data']=array();
			$response['message'] = 'Unconfirm list not available'; 
		}
	}else{
			$response['status']=0;
			$response['message'] = 'Required parameter missing!'; 
	}
	echo json_encode($response);
	}
	public function confirmApt(){
		
		$appointment_id = $this->input->post('appointment_id');
        $type = $this->input->post('type');
        if(!empty($appointment_id)){
		if($type=='confirm'){
            $query = $this->db->query("update appointment set status='3' where appointment_id='".$appointment_id."' ");
            $query2 = $this->db->query("insert into appointment_status set appointment_id='".$appointment_id."', status='3' ");
        }else{
             $query = $this->db->query("update appointment set is_delete='1' where appointment_id='".$appointment_id."' ");
            $query2 = $this->db->query("insert into appointment_status set appointment_id='".$appointment_id."', status='9' ");
        }
		
		if($query2){
			
			$response['status']=1;
			$response['message'] = 'Appointment status updated successfully!'; 
		}else{
			$response['status']=1;
			$response['message'] = 'Something went wrong'; 
		}
	}else{
		$response['status']=0;
		$response['message'] = 'Required parameter missing!'; 
	}
	echo json_encode($response);
}
public function getSalonDetail($vendor_id){

		$query = $this->db->query("select v.vendor_name as business_name, v.phone from vendor v where v.vendor_id='".$vendor_id."' ");
		$result = $query->row();
		return $result;
	}
public function customer_birthday_reminder(){
	$country = $this->ip_info("Visitor", "Country");
		if($country=='India'){
			date_default_timezone_set("Asia/Kolkata");
		}else{
			
			date_default_timezone_set("America/Los_Angeles");
		}
	$current_date=date('m-d');
	$getTodayBirthday=$this->db->query('select c.firstname,c.lastname,c.customer_id,l.email,l.vendor_id,c.mobile_phone from customer as c inner join login as l on l.login_id=c.login_id where c.is_active=1 and date_format(c.birthday,"%m-%d")="'.$current_date.'"')->result();
	foreach($getTodayBirthday as $val){
	$checkReminder=$this->db->query('select id from reminder_mail_sms where common_id="'.$val->customer_id.'" and date(date_created)="'.$current_date.'" and email_type=1')->num_rows();
	//echo $checkReminder;die;
		if($checkReminder <=0){
			$data['vendor_id']=$val->vendor_id;
			$data['template_type']='customer_birthday';

			
				$subject = "Customer Birthday";
				$data['business_info'] = $this->getSalonDetail($val->vendor_id);
				$data['type']='customer';
					$customer_name=$val->firstname;
				$data['customer_name']=$customer_name;
               	$this->load->helper('db');
				$this->load->library('Send_mail');
				$email=$val->email;
			
				//echo $email;
			
				$search1  = array('{Customer First Name}','{Business Name}');
				//$customer_name=$firstname." ".$lastname;
				$emailTemplate = $this->load->view('reminder',$data,TRUE);
				$replace1 = array($customer_name,$data['business_info']->business_name);
				$getDataNew=getImageTemplate($val->vendor_id,'customer_birthday');
				$getsmsData=str_replace($search1, $replace1, $getDataNew->sms_content);
				$subject='Customer birthday';
				//$email
			//	$this->send_mail->sendMail('vardhanharsh824@gmail.com', $subject, $emailTemplate, $fileName=false, $filePath=false, $cc=false);
				//echo 'insert into reminder_mail_sms set common_id="'.$val->customer_id.'",email_type=1,email_content="'.$emailTemplate.'",sms_content="'.$getsmsData.'",is_email=1,is_sms=1';die;
			//	$this->db->query('insert into reminder_mail_sms set common_id="'.$val->customer_id.'",email_type=1,is_email=1,is_sms=1');
				//test($getsmsData,$val->mobile_phone);
			
		}	
	}
	
}


public function appointment_reminder(){
	$country = $this->ip_info("Visitor", "Country");
		if($country=='India'){
			date_default_timezone_set("Asia/Kolkata");
		}else{
			
			date_default_timezone_set("America/Los_Angeles");
		}
	$getAppointmentData=$this->db->query("select a.appointment_id,concat(a.date,' ',aps.appointment_time) as ap_time_new,a.customer_id,a.vendor_id from appointment as a inner join appointment_service as aps on aps.appointment_id=a.appointment_id inner join service as sr on sr.service_id=aps.service_id inner join stylist as st on st.stylist_id=aps.stylist_id where a.is_checkout !=1 and a.status=1 or a.status=3")->result();
	//echo "<pre>";print_r($getAppointmentData);exit;
		foreach($getAppointmentData as $val){

			/*echo $val->ap_time_new."---------------------".$current_date."<br>";*/
			
			 $time1 = new DateTime($val->ap_time_new);
   				$time2 = new DateTime($current_date);
    			$time_diff = $time1->diff($time2);
    			$hour= $time_diff->h;
   				//echo $time_diff->i.' minutes';
   				 

			///echo 'select duration from notification_criteria where detail="Appointment reminder to the customer before" and vendor_id="'.$val->vendor_id.'"';
			$gettime=$this->db->query('select duration from notification_criteria where detail="Appointment reminder to the customer before" and vendor_id="'.$val->vendor_id.'"')->row();
			$checkReminder=$this->db->query('select id from reminder_mail_sms where common_id="'.$val->customer_id.'" and date(date_created)="'.$current_date.'" and email_type=2')->num_rows();
			
			if($gettime->duration <=$hour  && $checkReminder<=0){
				$getAppointmentData=$this->db->query("select aps.appointment_time as start_time,aps.appointment_end_time as end_time,concat(a.date,' ',aps.appointment_time) as ap_time_new,group_concat(concat(DAYNAME(a.date),' - ',date_format(a.date,'%m-%d-%Y')) SEPARATOR  ' /n ') as date,group_concat(time_format(a.date,'%m-%d-%Y')) as newDate,group_concat(concat(sr.service_name,' with ', concat(st.firstname,' ',st.lastname)) SEPARATOR  '-') as service_name,group_concat(aps.duration SEPARATOR  ' /n ') as duration,group_concat(time_format(aps.appointment_time,'%H:%i %p') SEPARATOR  ' /n ') as ap_time,group_concat(time_format(aps.appointment_time,'%H:%i %p')) as new_time,group_concat(concat(st.firstname,'',st.lastname)) as stylist_name,a.customer_id,a.vendor_id from appointment as a inner join appointment_service as aps on aps.appointment_id=a.appointment_id inner join service as sr on sr.service_id=aps.service_id inner join stylist as st on st.stylist_id=aps.stylist_id where a.appointment_id='".$val->appointment_id."'")->row();
			$getCancellationPolicy=$this->db->query('select policy_text from cancellation_policy where apt_type=1 and vendor_id="'.$val->vendor_id.'"')->row();
				$customer_detail = $this->getCustomerById($val->customer_id);
				$customer_name = $customer_detail->customer_name;
				$to = $customer_detail->email;
					          $data['getData'] = array(
					        'stylist_name'=> $getAppointmentData->stylist_name,
					        'customer_name'=> $customer_name,
					        'customer_email'=> $to,
					        'store_name'=> $customer_detail->vendor_name,
					        'city'=> $customer_detail->city,
					        'state'=>$customer_detail->name,
					        'zipcode'=> $customer_detail->picode,
					        
					        'start_time'=> $getAppointmentData->start_time,
					        'end_time'=> $getAppointmentData->end_time,
					        'appointment_date'=> $getAppointmentData->date,
						        'service_name'=> $getAppointmentData->service_name,
						        'start_time'=> $getAppointmentData->ap_time,
						        'business_phone'=>$customer_detail->phone,
						       'policy'=>$getCancellationPolicy->policy_text,
					        	'newdate'=>$getAppointmentData->newDate,
					        	'newTime'=>$getAppointmentData->new_time,
					        	'duration'=>$getAppointmentData->duration
					        
					   
					           );

					     $newAppointemnt=$this->db->query('select email_subject,email_content from email_settings where slug="appointment_reminder" and is_active=1 and vendor_id="'.$val->vendor_id.'"')->row();
					     //echo "<pre>";print_r($newAppointemnt);exit;
					      if(!empty($newAppointemnt)){
					      	$data['template_type']='appointment_reminder';
					      	$data['vendor_id']=$val->vendor_id;
					      
					      	$businessName=$this->db->query('select vendor_name from vendor where vendor_id="'.$val->vendor_id.'"')->row();
					      	$subject=str_replace('{Business Name}',$businessName->vendor_name,$newAppointemnt->email_subject);
					      $message = $this->load->view('email_template/appointment_reminder',$data,TRUE);
					      $this->load->library('Send_mail');
					      //$to
						//  $this->send_mail->sendMail('vardhanharsh824@gmail.com', $subject, $message, $fileName=false, $filePath=false, $cc=false);
						  // 	$this->db->query('insert into reminder_mail_sms set common_id="'.$val->customer_id.'",email_type=2,is_email=1,is_sms=1');
						  
					      }
					  }
		}
  							
}
function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE) {
    $output = NULL;
    if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
        $ip = $_SERVER["REMOTE_ADDR"];
        if ($deep_detect) {
            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
    }
    $purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
    $support    = array("country", "countrycode", "state", "region", "city", "location", "address");
    $continents = array(
        "AF" => "Africa",
        "AN" => "Antarctica",
        "AS" => "Asia",
        "EU" => "Europe",
        "OC" => "Australia (Oceania)",
        "NA" => "North America",
        "SA" => "South America"
    );
    if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
        $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
        if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
            switch ($purpose) {
                case "location":
                    $output = array(
                        "city"           => @$ipdat->geoplugin_city,
                        "state"          => @$ipdat->geoplugin_regionName,
                        "country"        => @$ipdat->geoplugin_countryName,
                        "country_code"   => @$ipdat->geoplugin_countryCode,
                        "continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                        "continent_code" => @$ipdat->geoplugin_continentCode
                    );
                    break;
                case "address":
                    $address = array($ipdat->geoplugin_countryName);
                    if (@strlen($ipdat->geoplugin_regionName) >= 1)
                        $address[] = $ipdat->geoplugin_regionName;
                    if (@strlen($ipdat->geoplugin_city) >= 1)
                        $address[] = $ipdat->geoplugin_city;
                    $output = implode(", ", array_reverse($address));
                    break;
                case "city":
                    $output = @$ipdat->geoplugin_city;
                    break;
                case "state":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "region":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "country":
                    $output = @$ipdat->geoplugin_countryName;
                    break;
                case "countrycode":
                    $output = @$ipdat->geoplugin_countryCode;
                    break;
            }
        }
    }
    return $output;
	}
	function getCustomerById($id){
		
		$query = $this->db->query("select l.email,c.firstname as customer_name,CONCAT(c.firstname,' ',c.lastname) as customer_name1,c.address,st.name,c.pincode,c.city,v.vendor_name,v.phone,l.fcm_token,c.mobile_phone from login l INNER JOIN customer c ON c.login_id=l.login_id inner join vendor as v on l.vendor_id=v.vendor_id left join states as st on st.id=c.state_id where c.customer_id='".$id."'");
		$result = $query->row();
		return $result;
		
	}
}
