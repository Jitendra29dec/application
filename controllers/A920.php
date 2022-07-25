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


class A920 extends CI_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
		$this->load->helper('db');
		//error_reporting(0);
    }
	
	function get(){
		
		$email = $this->input->post('email');
		$password = $this->input->post('password');
		$fcm_token = $this->input->post('fcm_token');
		
		$response['status'] = 0;
		
		if(!empty($email) && !empty($password)){
			
			$query = $this->db->query("select l.login_id, l.username, l.email,l.fcm_token, l.last_login, v.vendor_id, v.vendor_name, v.owner_name, v.phone, v.alternate_phone, v.photo from login l INNER JOIN vendor v ON v.login_id=l.login_id where l.email='".$email."' and l.password='".md5($password)."' and is_active='1' and is_delete='0' ");
			$res = $query->row();
			if($res){
				
				
				$this->db->query("update login set is_login='1' where email='".$email."' ");
				
				//if($res->fcm_token==NULL || $res->fcm_token==""){
				
				$this->db->query("update login set fcm_token='".$fcm_token."' where login_id='".$res->login_id."' ");
				//}
				
				$response['status'] = 1;
				$response['message'] = 'Login Successfully';
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
	function LoginPin(){
		
		$pin = $this->input->post('pin');
		$fcm_token = $this->input->post('fcm_token');
		$email = $this->input->post('email');
		$vendor_id = $this->input->post('vendor_id');
		
		/*$password = $this->input->post('password');*/
		
		//$refresh_token = $this->input->post('refresh_token');
		
		$response['status'] = 0;
		
		if(!empty($pin)){
			
			$query = $this->db->query("select l.login_id, l.username,l.fcm_token, l.email,l.role_id, l.last_login from login l where l.pin='".$pin."' AND l.vendor_id='".$vendor_id."' and (l.role_id='1' OR l.role_id='3')  and l.is_active='1' and l.is_delete='0' ");
			$res = $query->row();
			if($res){
				
				
				if($res->role_id=='3'){
					$q = $this->db->query("select sp.permission_id from stylist_permission sp INNER JOIN stylist s ON s.stylist_id=sp.stylist_id WHERE s.login_id='".$res->login_id."' AND sp.vendor_id='".$vendor_id."' ");
					
					$q2 = $this->db->query("select s.stylist_id from stylist s where s.login_id='".$res->login_id."'");
					$stylist_id = $q2->row()->stylist_id;
					
					
				}elseif($res->role_id=='1'){
					$q = $this->db->query("select id as permission_id from permission ");
					$stylist_id = $res->login_id;
					
				}
				
				$role_id = $res->role_id;
				
				$permission = $q->result();
				
				
				/* if($res->fcm_token==NULL || $res->fcm_token==""){
				
				$this->db->query("update login set fcm_token='".$fcm_token."' where login_id='".$res->login_id."' ");
				} */
			
			
				$response['status'] = 1;
				$response['message'] = 'Login Successfully';
				$response['stylist_id'] = $stylist_id;
				$response['role_id'] = $role_id;
				$response['data'] = $res;
				$response['permission'] = $permission;
				
				$c = $this->db->query("select l.email, c.customer_id, CONCAT(c.firstname,' ',c.lastname) as customer_name from login l INNER JOIN customer c ON c.login_id=l.login_id where l.is_active='1' and l.is_delete='0' ");
				$customer_data = $c->result();
				
				$s = $this->db->query("select l.email, s.stylist_id, CONCAT(s.firstname,' ',s.lastname) as stylist_name from login l INNER JOIN stylist s ON s.login_id=l.login_id where l.is_active='1' and l.is_delete='0' ");
				$stylist_data = $s->result();
				
				$sr = $this->db->query("select service_id, service_name from service");
				$service_data = $sr->result();
				
				//$response['customer_data'] = $customer_data;
				//$response['stylist_data'] = $stylist_data;
				//$response['service_data'] = $service_data;
				
			}else{
				$response['status'] = 0;
				$response['message'] = 'Wrong Pin !';
			}
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		
		echo json_encode($response);
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
	
  public function transactionList(){
  	$vendor_id=$this->input->post('vendor_id');
  	if(!empty($vendor_id)){
  	$result=$this->db->query("SELECT
  						o.order_id,
                          o.order_number,
                          c.customer_id,
                          	IF(CONCAT(c.firstname,' ',c.lastname) IS NULL,'N/A',CONCAT(c.firstname,' ',c.lastname)) customer_name,c.email,
                      		   IF(o.status_id='1','Pending',IF(o.status_id='2','Success',IF(o.status_id='3','Reject','N.A'))) payment_status,
                          
                          o.order_amount,
                          o.created_date
                      
                        FROM orders AS o
                         
                          LEFT JOIN customer c
                            ON c.customer_id = o.customer_id
                         
                         
                        WHERE o.vendor_id='".$vendor_id."' and o.status_id=1 order by o.order_id desc")->result();
  			if(!empty($result)){
  				$response['status'] = 1;
  				$response['data']=$result; 
			$response['message'] = 'Data available';
		}else{
			$response['status'] = 1;
  				$response['data']=array(); 
			$response['message'] = 'No data available';
		}
  		}else{
  			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing!';
  		}
  		echo json_encode($response);
  }
 public function viewOrder(){
 		$order_id=$this->input->post('order_id');
  	if(!empty($order_id)){
  	$result=$this->db->query("SELECT
  						o.order_id,
                          o.order_number,
                          c.customer_id,
                          	IF(CONCAT(c.firstname,' ',c.lastname) IS NULL,'N/A',CONCAT(c.firstname,' ',c.lastname)) customer_name,c.email,
                      		   IF(o.status_id='1','Pending',IF(o.status_id='2','Success',IF(o.status_id='3','Reject','N.A'))) payment_status,
                          
                          o.order_amount,
                          o.created_date
                      
                        FROM orders AS o
                         
                          LEFT JOIN customer c
                            ON c.customer_id = o.customer_id
                         
                         
                        WHERE o.order_id='".$order_id."' ")->result();
  			if(!empty($result)){
  				$response['status'] = 1;
  				$response['data']=$result; 
			$response['message'] = 'Data available';
		}else{
			$response['status'] = 1;
  				$response['data']=array(); 
			$response['message'] = 'No data available';
		}
  		}else{
  			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing!';
  		}
  		echo json_encode($response);
 }
	
	function Paynow(){
		$order_id=$this->input->post('order_id');
		$vendor_id=$this->input->post('vendor_id');
		$customer_id=$this->input->post('customer_id');
		  $payment_status=$this->input->post('payment_status');
  $payment_msg=$this->input->post('payment_msg');
  $balance_due=$this->input->post('balance_due');
  $message=$this->input->post('message');
  $card_number=$this->input->post('card_number');
  $exp_date=$this->input->post('exp_date');
  $card_holder_name=$this->input->post('card_holder_name');
    
 	 $card_type = $this->input->post('card_type');
	 $entry_type = $this->input->post('entry_type');
	 $terminal = $this->input->post('terminal');
	 $aid = $this->input->post('aid');
	 $tvr = $this->input->post('tvr');
	 $iad = $this->input->post('iad');
	 $tsi = $this->input->post('tsi');
	 $arc = $this->input->post('arc');
	 $transaction_id = 'TXNQ' . date('mdYHis');
	 	 $tip_value = $this->input->post('tip_value');
	 	 if($payment_status==1){
	 	 	$status_id=2;
	 	 }else{
	 	 	$status_id=3;
	 	 }
		if(!empty($order_id)){
				$query2 = $this->db->query("insert into payment set order_id='".$order_id."',payment_type='2', status_id='".$status_id."',payment_status=1,transaction_id='".$transaction_id."',amount='".$amount_paid."', message='".$message."', created_date='".date('Y-m-d h:i:s')."', customer_id='".$customer_id."',vendor_id='".$vendor_id."' ");  
  		
			$payment_id = $this->db->insert_id();

			if($payment_status==1){
			$this->db->query('insert into customer_card_detail set order_id="'.$order_id.'",payment_id="'.$payment_id.'",card_holder_name="'.$card_holder_name.'",card_number="'.$card_number.'",exp_date="'.$exp_date.'", terminal="'.$terminal.'", aid="'.$aid.'", tvr="'.$tvr.'", iad="'.$iad.'", tsi="'.$tsi.'", arc="'.$arc.'", card_type="'.$card_type.'" ' );
			$this->db->query("update orders set tip_value='".$tip_value."' where order_id='".$order_id."' ");
			$response['status'] = 1; 
			$response['message'] = 'Payment successfully done';
			}else{
				$response['status'] = 0; 
			 $response['message'] = 'Payment has been rejected';
			}
		}else{
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing!';
		}	
			echo json_encode($response);
	}
	public function testPdf(){
		$html=$this->load->view('test',true);
		echo $html;
	}
	function printSchedule(){
		//echo "<pre>";print_r($_GET);exit;
    date_default_timezone_set('America/Los_Angeles');
    /*$data['dayName']=$this->db->query('Select (case when value=0 then "sunday" when value=1 then "monday" when value=2 then "tuesday" when value=3 then "wednesday" when value=4 then "thursday" when value=5 then "friday" when value=6 then "saturday" else "monday" end) as week_day from settings where field="schedule_week_start_day" and vendor_id="'.$this->session->userdata('vendor_id').'"')->row();
    //echo "last"." ".$data['dayName']->week_day;die;
    $monday = strtotime("last"." ".$data['dayName']->week_day);
$monday = date('w', $monday)==date('w') ? $monday+7*86400 : $monday;
$sunday = strtotime(date("Y-m-d",$monday)." +6 days");*/

//echo "Current week range from $this_week_sd to $this_week_ed ";
  //  die;
	/*$from_date=$this->input->post('from_date');
	$to_date=$this->input->post('to_date');*/
	$vendor_id=$_GET['vendor_id'];
	//$vendor_id='166';
	$this_week_sd = $_GET['from_date'];
	$this_week_ed = $_GET['to_date'];
	//echo $this_week_sd." ".$this_week_ed;die;
   $Date = $this->getDatesFromRange($this_week_sd,$this_week_ed);
                $date_Count= count($Date);
                $dayname_new=array();
                $date_new=array();
                 $getData=array();
                 //$getStylist=array();
                    $getStylist=$this->db->query('select A.stylist_id,concat(B.firstname," ",B.lastname) as stylist_name,group_concat(DISTINCT (date_format(A.start_date,"%a"))) as dayname from stylist_schedule as A inner join stylist as B on A.stylist_id=B.stylist_id where A.start_date between "'.$this_week_sd.'" and "'.$this_week_ed.'" and A.vendor_id="'.$vendor_id.'" group by A.stylist_id')->result(); 
                foreach ($Date as $key => $value) {
                    $yrdata= strtotime($value);
                        $newD=date("d-F", $yrdata);
                        $dayname = date('D', strtotime($value));
                         $dayname_new[]= $dayname ;
                         $date_new[$dayname]= $newD ;
                        
                         
                     }
                    // echo "<pre>";print_r($date_new);exit;
                     $data['getDay']=$date_new;
                     //echo "<pre>";print_r($getStylist);exit;
	                 //echo "<pre>";print_r($getData);exit;
	//echo "<pre>";print_r($dataQuery);
                     $stlist_id_new=array();
                     $stylist_day_name=array();
                     $dayNameNew=array('Sun','Mon','Tue','Wed','Thu','Fri','Sat','Sun');
                        //echo "<pre>";print_r($getStylist);exit;
                     foreach ($getStylist as $va11){
                        
                        $getData[$va11->stylist_name]=$this->db->query('select A.stylist_id,A.id,concat(B.firstname," ",B.lastname) as stylist_name,concat(A.start_date," - ",A.end_date) as start,group_concat(concat(time_format(A.start_time,"%h:%i %p")," - ",time_format(A.end_time,"%h:%i %p")) SEPARATOR "\n") as end,date_format(A.start_date,"%a") as dayname,TIME(SUM(TIMEDIFF(A.end_time, A.start_time))) AS totalHour from stylist_schedule as A inner join stylist as B on A.stylist_id=B.stylist_id where A.start_date between "'.$this_week_sd.'" and "'.$this_week_ed.'" and A.stylist_id="'.$va11->stylist_id.'" and A.title_new="Schedule" group by A.start_date ORDER BY A.id desc')->result();
                        
                        //echo "harsh".$va11->stylist_name;
                            
                     }
                    $data['newData']=$getData;
                        //echo "<pre>";print_r($data['newData']);
                    
                    //echo "<pre>";print_r($getData);exit;
                     
	 $html=$this->load->view('getPreviewData',$data,true);
	 echo $html;exit;
		  

}
 function getDatesFromRange($start, $end, $format = 'Y-m-d') {
      //echo $start." ".$end;exit;
    // Declare an empty array
    $array = array();
      
    // Variable that store the date interval
    // of period 1 day
    $interval = new DateInterval('P1D');
  
    $realEnd = new DateTime($end);
    $realEnd->add($interval);
  
    $period = new DatePeriod(new DateTime($start), $interval, $realEnd);
  
    // Use loop to store date into array
    foreach($period as $date) {                 
        $array[] = $date->format($format); 
    }
  
    // Return the array elements
    return $array;
}	
function chechk(){
	test();
}
function getCustomerById($id){
		
		$query = $this->db->query("select l.email,CONCAT(c.firstname,' ',c.lastname) as customer_name,c.address,st.name,c.pincode,c.city,v.vendor_name,v.phone,l.fcm_token,c.mobile_phone from login l INNER JOIN customer c ON c.login_id=l.login_id inner join vendor as v on l.vendor_id=v.vendor_id left join states as st on st.id=c.state_id where c.customer_id='".$id."'");
		$result = $query->row();
		return $result;
		
	}
	public function getCustomerCancelData(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$getData=$this->db->query('select A.id,A.customer_id,A.appointment_id,A.amount,A.status,concat(B.firstname," ",B.lastname) as customer_name from customer_cancellation_amount as A inner join customer as B on A.customer_id=B.customer_id where A.vendor_id="'.$vendor_id.'"')->result();
		$getDataNew=array();
		if(!empty($getData)){
		foreach($getData as $key=> $val){
			$getCustomerCardData=$this->db->query('select cardholder_name,card_number,expiry_month,expiry_year,cvv,card_type from  customer_card where customer_id="'.$val->customer_id.'" and is_default=1')->row();
		if(!empty($getCustomerCardData)){
			$getCustomerCardData=$getCustomerCardData;
		}else{
			$getCustomerCardData=(Object)[];
		}
			$getDataNew[]=$val;
			$getDataNew[$key]->customerCardInfo=$getCustomerCardData;
		}
		
			
			$response['status'] = 1; 
			$response['message'] = '';
			$response['result']=$getDataNew;
		}else{
			$response['status'] = 0; 
			$response['message'] = 'No data found';
			$response['result']=array();
		}
		}else{
  			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing!';
  		}
  		echo json_encode($response);

	}
	public function getCustomerDataForCancelOrder(){
		$customer_id=$this->input->post('customer_id');
		$getCustomerCardData=$this->db->query('select cardholder_name,card_number,expiry_month,expiry_year,cvv,card_type from  customer_card where customer_id="'.$customer_id.'" and is_default=1')->row();
		if(!empty($getCustomerCardData)){
			$getCustomerCardData=$getCustomerCardData;
		}else{
			$getCustomerCardData=(Object)[];
		}
		$response['status']=1;
		$response['customerCardInfo']=$getCustomerCardData;
		$response['message']="";
		echo json_encode($response);
	}
public function getCustomerDataForCancelOrder_old(){
		$customer_id=$this->input->post('customer_id');
		$token_no=$this->input->post('token_no');
		$vendor_id=$this->input->post('vendor_id');
		$appointment_id=$this->input->post('appointment_id');
		$color_id=$this->input->post('color_id');
		$type=$this->input->post('type');
		$getAppointmentData=$this->db->query('select appointment_id as id,appointment_type,deposit_id from appointment where appointment_id="'.$appointment_id.'"')->row();
		if($getAppointmentData->appointment_type==1 || $getAppointmentData->appointment_type==2){
			$ap_type=1;
		}else{
			$ap_type=2;
		}
		$getCancellationData=$this->db->query('select field1,field2,field3,field2_type,field3_type from cancellation_policy where vendor_id="'.$vendor_id.'" and apt_type="'.$ap_type.'" and is_active=1 order by id desc limit 1')->row();
		
		//echo 'select sum(price) as total_amount from appointment_service where appointment_id IN("'.$getAppointmentData->id.'")';
		$getAppointmentAmount=$this->db->query("select A.date,sum(B.price) as total_amount,B.appointment_time from appointment_service as B inner join appointment as A on A.appointment_id=B.appointment_id where B.appointment_id IN($getAppointmentData->id)")->row();
		$getDepositAmount=$this->db->query('select sum(amount) from deposit_installment where deposit_id="'.$getAppointmentData->deposit_id.'" and is_active=1')->row();
		$getCustomerCardData=$this->db->query('select cardholder_name,card_number,expiry_month,expiry_year,cvv,card_type from  customer_card where customer_id="'.$customer_id.'" and is_default=1')->row();
		if(!empty($getCustomerCardData)){
			$getCustomerCardData=$getCustomerCardData;
		}else{
			$getCustomerCardData=(Object)[];
		}
		if(!empty($getCancellationData)){
		if($type=='no_show'){
			if($getCancellationData->field3_type=='Amount' || $getCancellationData->field3_type=='amount'){
				//echo "vishal";
				if($getAppointmentData->appointment_type=='3'){

					$amount_new=$getDepositAmount->deposit_amount - $getCancellationData->field3;
					if($amount_new >0){
						$amount=$amount_new;
						$api_type="refund";
						$api_hit="yes";
					} else{
						$api_type="recive";
						$amount=0;
						$api_hit="no";
					}
				}else{
					$api_type="recive";
					$api_hit="yes";
				$amount=$getCancellationData->field3;	
				}
				
			}else{
				//echo "harsh";
				if($getAppointmentData->appointment_type=='3'){
					$newAmount=$getAppointmentAmount->total_amount * $getCancellationData->field3/100;
					$amount_new=$getDepositAmount->deposit_amount - $newAmount;
					if($amount_new >0){
						$api_type="refund";
						$amount=$amount_new;
						$api_hit="yes";
					} else{
						$api_type="recive";
						$amount=0;
						$api_hit="no";
					}
					//$amount=$newAmount;
				}else{
				   //echo $getAppointmentAmount->total_amount."--".$getCancellationData->field3;
					$newAmount=$getAppointmentAmount->total_amount * $getCancellationData->field3/100; 
					$amount=$newAmount;	
					$api_type="recive";
					$api_hit="yes";
				}
				
			}
		}
		}else{
			$api_type="recive";
			$amount=0;
			$api_hit="no";
		}
		if($type=='cancel'){
			//date_default_timezone_set("America/New_York");
			 $country = $this->ip_info("Visitor", "Country");
				if($country=='India'){
					date_default_timezone_set("Asia/Kolkata");
				}else{
					
					date_default_timezone_set("America/Los_Angeles");
				}

				$current_time=	date("Y-m-d H:i");
				$startTime = date("Y-m-d H:i");

				//$checkTime = date('Y-m-d H:i',strtotime('+'.$getCancellationData->field1.'hour',strtotime($getAppointmentAmount->date." ".$getAppointmentAmount->appointment_time)));
				$checkTime=$getAppointmentAmount->date." ".$getAppointmentAmount->appointment_time;
					//echo "harsh".$checkTime."---".$current_time;die;
				/*if($checkTime > $startTime){*/
				$time1 = new DateTime($startTime);
				$time2 = new DateTime($checkTime);
				$timediff = $time1->diff($time2);
				//echo $checkTime."---".$startTime."---".$timediff->h;die;
				$getHour= $timediff->h;
				if($getHour=='')
				{
					$getHour=0;
				}
				//echo "harsh".$getHour."--".$checkTime."--".$startTime;die;
					/*}else{
				$getHour=0;
					}*/
				//echo $checkTime."---".$startTime."----".$getCancellationData->field1."--".$getHour;die; 
				if($getCancellationData->field1 > $getHour){


			if($getCancellationData->field2_type=='Amount' || $getCancellationData->field3_type=='amount'){
				//echo "vishal";
				if($getAppointmentData->appointment_type=='3'){

					$amount_new=$getDepositAmount->deposit_amount - $getCancellationData->field2;
					if($amount_new >0){
						$amount=$amount_new;
						$api_type="refund";
						$api_hit="yes";
					} else{
						$api_type="recive";
						$amount=0;
						$api_hit="no";
					}
				}else{
					$api_type="recive";
					$api_hit="yes";
				$amount=$getCancellationData->field2;	
				}
				
			}else{
				//echo "harsh";
				if($getAppointmentData->appointment_type=='3'){
					$newAmount=$getAppointmentAmount->total_amount * $getCancellationData->field2/100;
					$amount_new=$getDepositAmount->deposit_amount - $newAmount;
					if($amount_new >0){
						$api_type="refund";
						$amount=$amount_new;
						$api_hit="yes";
					} else{
						$api_type="recive";
						$amount=0;
						$api_hit="no";
					}
					//$amount=$newAmount;
				}else{
				   //echo $getAppointmentAmount->total_amount."--".$getCancellationData->field3;
					$newAmount=$getAppointmentAmount->total_amount * $getCancellationData->field2/100; 
					$amount=$newAmount;	
					$api_type="recive";
					$api_hit="yes";
				}
				
			}
		}else{
						$api_type="recive";
						$amount=0;
						$api_hit="no";
		}
	}
		$response['status']=1;
		$response['vendor_id']=$vendor_id;
		$response['appointment_id']=$getAppointmentData->id;
		$response['customer_id']=$customer_id;
		$response['customerCardInfo']=$getCustomerCardData;
		$response['amount']=number_format($amount,2);
		$response['api_type']=$api_type;
		$response['api_hit']=$api_hit;
		$response['color_id']=$color_id;
		$response['message']="Status updated successsfully!";
		echo json_encode($response);
		
	} 
	function cacelOrder(){
		$payment_status=$this->input->post('payment_status');
		$appointment_id_new=$this->input->post('appointment_id');
		$getAptData=$this->db->query('select color_code,vendor_id from appointment where appointment_id="'.$appointment_id.'"')->row();
		$vendor_id=$getAptData->vendor_id;
		
		$customer_id=$this->input->post('customer_id');
		$amount=$this->input->post('amount');
		$api_hit="yes";
		$color_id=$getAptData->color_code;
		$customer_detail = $this->getCustomerById($customer_id);
		if($api_hit=='yes'){
		if($payment_status==1){

		    $payment_status = 2; //success
		    //$status_id=2;
		    $order_number = 'ORD' . date('mdYHis');
		    $bank_txn_id = "";
		    $response_code = "";
		    $currency = "$";
		    $message = "Payment Received";
		    $transaction_id = 'TXNQ' . date('mdYHis');
  			$signature_img = $this->input->post('signature_img');
	 
		
		
	 if(!empty($signature_img) || $signature_img!=NULL || $signature_img!="" || $signature_img!=null){
			$path = '../assets/img/signature/';
			$file = time().'.jpeg';
			$signature_img = $this->base64_to_jpeg($signature_img,$path.$file);
			$signature = $file;
		
		}else{
			$signature = '';
		}
			$query3 = $this->db->query("insert into orders set vendor_id='".$vendor_id."',login_id=0, customer_id='".$customer_id."',status_id='4', order_type='4', order_number='".$order_number."', order_amount='".$amount."',tip_amount='0',discount_amount='0',iou_amount='0',credit_card_amount='".$amount."',cash_amount='0',gift_cert_amount='0',gift_cart_amount='0',rewards_money='0',tax_amount='0',cuppon_value='0',is_active='1', is_delete='0',is_extra_deposit='0',return_amount='0',created_date='".date('Y-m-d h:i:s')."' ");
 	//echo $this->db->last_query();exit;
	
 	   $order_id = $this->db->insert_id();
 	 if($order_id){
		 
 	   	$query2 = $this->db->query("insert into payment set order_id='".$order_id."',payment_type='2', status_id='2',payment_status=1,transaction_id='".$transaction_id."', bank_txn_id='".$bank_txn_id."', response_code='".$response_code."', amount='".$amount."', message='".$message."', created_date='".date('Y-m-d h:i:s')."', customer_id='".$customer_id."',vendor_id='".$vendor_id."', signature='".$signature."' ");  
  		
			$payment_id = $this->db->insert_id();
			if($appointment_id_new!='' && $appointment_id_new!=null){
				$stylist_id=$this->db->query("select stylist_id from appointment_service where appointment_id IN($appointment_id_new)")->result();
				$appointment_id=explode(",", $appointment_id_new);
			      for($i=0;$i<count($appointment_id);$i++){
			      if($appointment_id_new!=0){
			        $stylist_ids = $stylist_id[$i]->stylist_id;
			        }else{
			        $stylist_ids = 0;
			        }
			        //echo "insert into order_detail set order_id='".$order_id."', product_id='".$appointment_id[$i]."',sale_type=1,stylist_id='".$stylist_ids."' ";die;
			        $queryy = $this->db->query("insert into order_detail set order_id='".$order_id."', product_id='".$appointment_id[$i]."',tax_amount='0',discount_amount='0',actual_amount='0',total_paid_amount='0',sale_type=1,stylist_id='".$stylist_ids."' ");
			      }
    			/*for($i=0;$i<count($appointment_id);$i++){
         			 $this->db->query("update appointment set status='7', color_code='8', checkout_time='".date('Y-m-d h:i:s')."', is_checkout='1' where appointment_id IN(".$appointment_id_new.") ");
       			 }*/
			}
			$this->db->query('update appointment set color_code="'.$color_id.'" where appointment_id IN("'.$appointment_id_new.'")');
			$this->db->query('update appointment set status=8 where appointment_id IN("'.$appointment_id_new.'")');
			$this->db->query('update customer_cancellation_amount set status=1 where appointment_id="'.$appointment_id_new.'"');
			$response['reciptedata']=$this->order_invoice($order_id,$vendor_id);
			$response['status']=1;
			$response['message']='Order cancel payment receive successfully';
			
			$customer_name = $customer_detail->customer_name;
			$to = $customer_detail->email;
								$data['customer_name'] =  $customer_name; 
					          $data['appointment_date'] = $appointment_date; 
					          $data['appointment_time'] = $appointment_time; 
					           $data['phone'] = $customer_detail->mobile_phone;
					          //$data['service'] = $service; 
					        
					          $getAppointmentData=$this->db->query("select group_concat(concat(DAYNAME(a.date),' - ',date_format(a.date,'%m-%d-%Y')) SEPARATOR  ' /n ') as date,group_concat(time_format(a.date,'%m-%d-%Y')) as newDate,group_concat(concat(sr.service_name,' with ', concat(st.firstname,' ',st.lastname)) SEPARATOR  '-') as service_name,group_concat(aps.duration SEPARATOR  ' /n ') as duration,group_concat(time_format(aps.appointment_time,'%H:%i %p') SEPARATOR  ' /n ') as ap_time,group_concat(time_format(aps.appointment_time,'%H:%i %p')) as new_time,group_concat(concat(st.firstname,'',st.lastname)) as stylist_name from appointment as a inner join appointment_service as aps on aps.appointment_id=a.appointment_id inner join service as sr on sr.service_id=aps.service_id inner join stylist as st on st.stylist_id=aps.stylist_id appointment_id ='".$appointment_id_new."' ")->row();
  							$getCancellationPolicy=$this->db->query('select policy_text from cancellation_policy where apt_type=1 and vendor_id="'.$vendor_id.'"')->row();
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
					        	'duration'=>$getAppointmentData->duration
					        
					   
					           );
					          $newAppointemnt=$this->db->query('select email_subject,email_content from email_settings where slug="cancel_appointment" and is_active=1 and vendor_id="'.$vendor_id.'"')->row();
					     //echo "<pre>";print_r($newAppointemnt);exit;
					      if(!empty($newAppointemnt)){
					      	$data['template_type']='cancel_appointment';
					      	$data['vendor_id']=$vendor_id;
					      //	$this->load->library('email');
                  			// $this->email->initialize($config);
                  			// $this->email->from('info@booknpay.com', 'Hubwallet');
					        //$data['new_content']=$newAppointemnt->email_content;
					      //  $this->email->to($to); // replace it with receiver email id
					     // $this->email->subject($newAppointemnt->email_subject); // replace it with email subject
					      	$businessName=$this->db->query('select vendor_name from vendor where vendor_id="'.$vendor_id.'"')->row();
					      	$subject=str_replace('{Business Name}',$businessName->vendor_name,$newAppointemnt->email_subject);
					      $message = $this->load->view('email_template/new_appointment',$data,TRUE);
					      $this->load->library('Send_mail');
						  $this->send_mail->sendMail('vardhanharsh824@gmail.com', $subject, $message, $fileName=false, $filePath=false, $cc=false);
					      }

		}else{
			$response['reciptedata']=array();
			$response['status']=0;
			$response['message']='Order not created';
		}
		}else{
			$response['reciptedata']=array();
			$response['status']=1;
			$response['message']='Transaction Fail';
		}
	}

		echo json_encode($response);
	
	}
	public function order_invoice($order_id = '',$vendor_id=''){
    /*p.payment_id,p.transaction_id,IF(p.payment_type="1","Cash",IF(p.payment_type="2","Debit Card",IF(p.payment_type="3","Credit Card",IF(p.payment_type="4","Net Banking",IF(p.payment_type="5","EBS Payments","N.A"))))) payment_type,
                         IF(p.status_id="1","Pending",IF(p.status_id="2","Success",IF(p.status_id="3","Reject","N.A"))) payment_status,p.amount        inner join payment as p on o.order_id=p.payment_id*/
    //echo $order_id;exit;

   $this->db->query('update orders set status_id=2 where order_id="'.$order_id.'"');
   $qqrr = $this->db->query("select count(order_id) as num from orders where created_date LIKE '%".date('Y-m-d')."%' and vendor_id='".$vendor_id."' ");
	$current_date_count = $qqrr->row()->num;
	
	$increase_count = $current_date_count+1;
	
	$data['order_number']='ORD' . date('mdY').$increase_count;

  $data['order_data'] =$this->db->query('select o.order_id,o.customer_id,o.coupon_id,o.order_number,o.order_amount,o.tax_amount,o.tip_amount,o.cash_amount,o.credit_card_amount,o.iou_amount,o.final_amount,o.vendor_id,o.rewards_money,o.diposite_amount,o.discount_amount,o.gift_cert_amount as certificate_amount,o.gift_cart_amount as gift_card_amount,o.cuppon_value,IF(o.status_id="2","Successfull",IF(o.status_id="1","in Process",IF(o.status_id="3","cancel",IF(o.status_id="4","payment fail","N.A")))) order_status from orders as o  where o.order_id="'.$order_id.'"')->row();

 //echo "<pre>";print_r($data['order_data']);exit;
 $data['payment_data']=$this->db->query('select p.payment_id,p.transaction_id,IF(p.payment_type="1","Cash",IF(p.payment_type="2","Card",IF(p.payment_type="3","Credit Card",IF(p.payment_type="4","Net Banking",IF(p.payment_type="5","EBS Payments","N.A"))))) payment_type,
                         IF(p.status_id="1","Pending",IF(p.status_id="2","Success",IF(p.status_id="3","Reject","N.A"))) payment_status,p.amount from payment as p where p.order_id="'.$order_id.'" and status_id=2')->result();
  //echo "<pre>";print_r($data['order_data']);exit;
  $data['customerInfo'] = $this->db->query('select concat(firstname," ",lastname) as customer_name,email,mobile_phone,home_phone from customer where customer_id IN ('. $data['order_data']->customer_id.')')->result();
  
   $data['cardDetail'] = $this->db->query('select card_holder_name, card_number,card_type, entry_type, terminal, aid, tvr, tsi, arc from customer_card_detail where order_id="'.$order_id.'" order by id desc limit 0,1 ')->result();
   
   $data['batchNumber'] = $this->db->query('SELECT DISTINCT(batch_no) FROM `batch` where order_id="'.$order_id.'" ')->result();
   
         $path = "http://159.203.182.165/salon/assets/img/signature/";
        
   $data['customerSignature'] = $this->db->query("select CONCAT('$path','/',signature) as signature from payment where order_id='".$order_id."' AND customer_id='".$data['order_data']->customer_id."'  ")->row();
  $data['appointmentData']=$this->db->query("select
                                    a.appointment_id,
                                    a.date as appointment_date,
                                    aps.service_id, aps.stylist_id,
                                    aps.appointment_time,
                                    aps.appointment_end_time,
                                    s.service_name,
                                    CONCAT(st.firstname,' ',st.lastname) as stylist_name,
                                    aps.price,
                                    aps.duration
                                    from appointment a
                                    INNER JOIN appointment_service aps
                                    ON aps.appointment_id=a.appointment_id
                                    INNER JOIN service s
                                    ON s.service_id=aps.service_id
                                    INNER JOIN stylist st
                                    ON st.stylist_id=aps.stylist_id
                                    inner join order_detail as o on a.appointment_id=o.product_id
                                    where o.order_id='".$order_id."' and o.sale_type=1
                                    ")->result();
 $data['productInfo']=$this->db->query('SELECT 
                      p.product_id,
                      p.product_name as name,
                      c.category_name,
                      p.price_retail as price,
                      o.quantity as quant,
                      p.description,
                      p.main_image
                    FROM product p
                    LEFT JOIN category c 
                      ON c.category_id = p.category_id
                      INNER join order_detail as o on o.product_id=p.product_id
                    WHERE o.order_id = "'.$order_id.'" and o.sale_type=2')->result();
  $data['gift_certificate']=$this->db->query('SELECT 
                      gift.gift_certificate_no as gift_no,
                      gift.amount as amount
                       FROM gift_certificate as gift
                  
                      INNER join order_detail as o on o.product_id=gift.gift_id
                    WHERE o.order_id = "'.$order_id.'" and o.sale_type=3')->result();
  $data['gift_card']=$this->db->query('SELECT 
                      gift.card_number as gift_card_no,
                      gift.intial_amount as amount
                       FROM gift_card as gift
                  
                      INNER join order_detail as o on o.product_id=gift.card_id
                    WHERE o.order_id = "'.$order_id.'" and o.sale_type=4')->result();

             return $data;
       //  echo json_encode($data);
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

	function add_group_appointment(){
		//$appointment_data = $this->input->post('appointment_data');
		$appointment_data='{
		"vendor_id": "57",
	"event_name": "asdasd",
	"total_customer": "10",
	"customer_data": {
		"group_head": "181",
		"data": [{
			"customer_id": "163",
			"services": [{
				"date": "2022-03-07",
				"start_time": "13:00",
				"service_id": "2",
				"stylist_id": "5",
				"duration": "30",
				"price": "35.00"
			}, {
				"date": "2022-03-07",
				"start_time": "13:20",
				"service_id": "1",
				"stylist_id": "1",
				"duration": "30",
				"price": "100.00"
			}]
		}, 
		{
			"customer_id": "150",
			"services": [{
				"date": "2022-03-08",
				"start_time": "14:00",
				"service_id": "1",
				"stylist_id": "1",
				"duration": "30",
				"price": "35.00"
			}]
		}, 
		{
			"customer_id": "150",
			"services": [{
				"date": "2022-03-07",
				"start_time": "16:00",
				"service_id": "1",
				"stylist_id": "5",
				"duration": "30",
				"price": "20.00"
			}]
		}]
	},
	"event_data": [
	{
		"start_time": "14:31",
		"end_time": "17:31",
		"date": "2022-03-07",
		"no_of_customer": "5"
	},
	{
		"start_time": "14:31",
		"end_time": "17:31",
		"date": "2022-03-08",
		"no_of_customer": "5"
	}

	],
	"deposit_data": {
		"deposit_amount_require": "100",
		"distribute_in": "Percent",
		"amount": "5%",
		
		
		"deposit_installment": [
		{
			"date": "2022-2-07",
			"amount": "50"
		},
		{
			"date": "2022-2-08",
			"amount": "50"
		}
		]
	}
}';
		$appointment_data_check = json_decode($appointment_data);
		if(!empty($appointment_data_check))
		{

			if(!empty($appointment_data_check->event_data)){
					 $query=$this->db->query('insert into deposit_customer set customer_id="'.$appointment_data_check->customer_data->group_head.'",customer_total="'.$appointment_data_check->total_customer.'",deposit_amount="'.$_POST['deposit_amount'].'", event_name="'.$appointment_data_check->event_name.'",deposit_type="'.$_POST['deposit_distribution'].'",vendor_id="'.$appointment_data_check->vendor_id .'",status="0" ,require_deposit="'.$appointment_data_check->deposit_data->deposit_amount_require.'", is_require_deposit="1", distribute_in="'.$appointment_data_check->deposit_data->distribute_in.'",distribute_amount="'.$appointment_data_check->deposit_data->amount.'" ');
   //echo $this->db->last_query();die;
					$deposit_id = $this->db->insert_id();
					if(!empty($appointment_data_check->event_data)){
					foreach($appointment_data_check->event_data as $Eventval){
							$this->db->query('insert into deposit_customer_schedule set deposit_id="'.$deposit_id.'",start_date="'.$Eventval->date.'",start_time="'.$Eventval->start_time.'",end_time="'.$Eventval->end_time.'",no_of_customer="'.$Eventval->no_of_customer.'",color_id="12",status=1');
					}
					if(!empty($appointment_data_check->deposit_data->deposit_amount_require)){
						foreach($appointment_data_check->deposit_data->deposit_installment as $instVal){
								$this->db->query('insert into deposit_installment set deposit_id="'.$deposit_id.'",deposit_date="'.$instVal->date.'",amount="'.$instVal->amount.'",is_active=1');
						}
					}

				}
				}else{
					$deposit_id=0;
				}

			
		if(!empty($appointment_data_check->customer_data->data)){
			$color_id = $this->getColorIdByColorType('confirm',$vendor_id);
			$qr = $this->db->query("select max(token_no) as token from appointment");
			$re = $qr->row();
			$token = ($re->token)+1;
			$getDone=array();
			foreach($appointment_data_check->customer_data->data as $val){
		    	foreach($val->services as $valData){
						$qry = $this->db->query("insert into appointment set vendor_id='".$appointment_data_check->vendor_id."', date='".$valData->date."', appointment_duration='".$valData->duration."', customer_id='".$val->customer_id."',group_leader_id='".$appointment_data_check->customer_data->group_head."',color_code='".$color_id."', rendering='back', created_date='".date('Y-m-d')."', appointment_type='3', token_no='".$token."', deposit_id='".$deposit_id."' ");
						$insert_id = $this->db->insert_id();
						if($insert_id){
							$getDone[]='Done';
						}else{
							$getDone[]="";
						}
						/*$serviceInfo = $this->getServiceById($service_id[$j]);
						$price = $serviceInfo->price;
						$duration = $serviceInfo->duration;*/
						$endTime = strtotime("+".$valData->duration."minutes", strtotime($valData->start_time));
							$new_end_time=date('H:i', $endTime);
							//echo $new_end_time
						$qry2 = $this->db->query("insert into appointment_service set appointment_id='".$insert_id."', service_id='".$valData->service_id."', stylist_id='".$valData->stylist_id."', appointment_time='".$valData->start_time."',appointment_end_time='".$new_end_time."', customer_id='".$val->customer_id."', price='".$valData->price."', duration='".$valData->duration."' ");
					}
			}
			if(!empty($getDone)){
				$response['status']=1;
				$response['message']='Group appointment is booked successfully';
			}else{
				$response['status']=1;
				$response['message']='Something went wrong';
			}

		}
	}else{
			$response['status']=0;
			$response['message']='json data not valid';
	}

		echo json_encode($response);

	}
	function getServiceById($service_id){
	
		$query = $this->db->query("select s.service_id,s.service_name,s.price,s.duration from service s where s.service_id='".$service_id."'");
		$result = $query->row();
		return $result;
	}
	public function getColorIdByColorType($color_type,$vendor_id){
	  
	  $query = $this->db->query("select color_id from color_settings where vendor_id='".$vendor_id."' and color_type='".$color_type."' ");
	  
	  $res = $query->row()->color_id;
	  return $res;
  }

  public function BatchClose(){
  	$machine_type=$this->input->post('machine_type');
  	$login_id=$this->input->post('login_id');
  	$xml_string=$this->input->post('xml_string');

  	/*$xml_string='<body><CreditSaleCount>1</CreditSaleCount><CreditSaleAmount>1</CreditSaleAmount><CreditForcedCount>0</CreditForcedCount><CreditForcedAmount>0</CreditForcedAmount><CreditReturnCount>0</CreditReturnCount><CreditReturnAmount>0</CreditReturnAmount><CreditAuthCount>0</CreditAuthCount><CreditAuthAmount>0</CreditAuthAmount><CreditPostAuthCount>0</CreditPostAuthCount><CreditPostAuthAmount>0</CreditPostAuthAmount><DebitSaleCount>0</DebitSaleCount><DebitSaleAmount>0</DebitSaleAmount><DebitReturnCount>0</DebitReturnCount><DebitReturnAmount>0</DebitReturnAmount><EBTSaleCount>0</EBTSaleCount><EBTSaleAmount>0</EBTSaleAmount><EBTReturnCount>0</EBTReturnCount><EBTReturnAmount>0</EBTReturnAmount><EBTWithdrawalCount>0</EBTWithdrawalCount><EBTWithdrawalAmount>0</EBTWithdrawalAmount><GiftSaleCount>0</GiftSaleCount><GiftSaleAmount>0</GiftSaleAmount><GiftAuthCount>0</GiftAuthCount><GiftAuthAmount>0</GiftAuthAmount><GiftPostAuthCount>0</GiftPostAuthCount><GiftPostAuthAmount>0</GiftPostAuthAmount><GiftActivateCount>0</GiftActivateCount><GiftActivateAmount>0</GiftActivateAmount><GiftIssueCount>0</GiftIssueCount><GiftIssueAmount>0</GiftIssueAmount><GiftReloadCount>0</GiftReloadCount><GiftReloadAmount>0</GiftReloadAmount><GiftReturnCount>0</GiftReturnCount><GiftReturnAmount>0</GiftReturnAmount><GiftForcedCount>0</GiftForcedCount><GiftForcedAmount>0</GiftForcedAmount><GiftCashoutCount>0</GiftCashoutCount><GiftCashoutAmount>0</GiftCashoutAmount><GiftDeactivateCount>0</GiftDeactivateCount><GiftDeactivateAmount>0</GiftDeactivateAmount><GiftAdjustCount>0</GiftAdjustCount><GiftAdjustAmount>0</GiftAdjustAmount><LoyaltyRedeemCount>0</LoyaltyRedeemCount><LoyaltyRedeemAmount>0</LoyaltyRedeemAmount><LoyaltyIssueCount>0</LoyaltyIssueCount><LoyaltyIssueAmount>0</LoyaltyIssueAmount><LoyaltyReloadCount>0</LoyaltyReloadCount><LoyaltyReloadAmount>0</LoyaltyReloadAmount><LoyaltyReturnCount>0</LoyaltyReturnCount><LoyaltyReturnAmount>0</LoyaltyReturnAmount><LoyaltyForcedCount>0</LoyaltyForcedCount><LoyaltyForcedAmount>0</LoyaltyForcedAmount><LoyaltyActivateCount>0</LoyaltyActivateCount><LoyaltyActivateAmount>0</LoyaltyActivateAmount><LoyaltyDectivateCount>0</LoyaltyDectivateCount><LoyaltyDeactivateAmount>0</LoyaltyDeactivateAmount><CashSaleCount>0</CashSaleCount><CashSaleAmount>0</CashSaleAmount><CashReturnCount>0</CashReturnCount><CashReturnAmount>0</CashReturnAmount><CheckSaleCount>0</CheckSaleCount><CheckSaleAmount>0</CheckSaleAmount><CheckAdjustCount>0</CheckAdjustCount><CheckAdjustAmount>0</CheckAdjustAmount></body>';*/
  	
	$xml = simplexml_load_string($xml_string);
	$json = json_encode($xml); // convert the XML string to JSON
	$data=json_decode($json,true);
	//echo "<pre>";print_r($data);exit;
//echo $data['CreditSaleCount'];
	if(!empty($data)){
	$query=$this->db->query('insert into batch_close_report set credit_count="'.$data['CreditSaleCount'].'",credit_amount="'.$data['CreditSaleAmount'].'",debit_count="'.$data['DebitSaleCount'].'",debit_amount="'.$data['DebitSaleAmount'].'",credit_return_count="'.$data['CreditReturnCount'].'",credit_refund_amount="'.$data['CreditReturnAmount'].'",debit_return_count="'.$data['DebitReturnCount'].'",debit_return_amount="'.$data['DebitReturnAmount'].'",login_id="'.$login_id.'",machine_type="'.$machine_type.'"');
		if($query){
					$response['status']=1;
					$response['message']='Batch close successfully';
		}else{
					$response['status']=0;
					$response['message']='Something wrong with batch close';
		}

		}else{
					$response['status']=0;
					$response['message']='No data found for batch close';
		  }
		  echo json_encode($response);
		}



		public function testMailchimp(){

			// mailchimp code start here
				
			$vendor_id = '10';
			$vendor_data = $this->db->query("select mailchimp_list_id, vendor_name from vendor where vendor_id='".$vendor_id."'")->row();
				
			$mailchimp_list_id = $vendor_data->mailchimp_list_id;
			//mailchimp_list_id = b3baa80859
			$vendor_name = $vendor_data->vendor_name;
			
			require_once(APPPATH.'libraries/mailchimp/vendor/autoload.php'); 
	//	echo $vendor_name;die;
			
			$client = new MailchimpMarketing\ApiClient();

			$client->setConfig([
				'apiKey' => 'c799b76606792859138325910554f01d-us20',
				'server' => 'us20',
			]); 

		//	 $list_id = $mailchimp_list_id;
			 $list_id = "b3baa80859";
//$email = "123abctest@gmail.com";
//$vendor_name = "123abctest vendor";
//$phone = "9865432221"; 

			try {
				$response = $client->lists->addListMember($list_id, [
					/* "email_address" => $email,
					"status" => "subscribed",
					"merge_fields" => [
					  "EMAIL" => $email,
					  "FIRST_NAME" => $firstname,
					  "LAST_NAME" => $lastname,
					  "MOBILE" => $phone,
					  "GENDER" => $gender,
					  "BIRTHDAY" => $birthday,
					  "ADDRESS" => $address,
					  "CITY" => $city,
					  "STATE" => $state_id,
					  "ZIPCODE" => $zipcode */

					  "email_address" => 'vishal@gmail.com',
					"status" => "subscribed",
					"merge_fields" => [
					  "EMAIL" => 'vishal@gmail.com',
					  "FIRST_NAME" => 'vishal',
					  "LAST_NAME" => 'saxena',
					  "MOBILE" => '(818) 466-3458',
					  "GENDER" => 'Male',
					  "BIRTHDAY" => '09-12-2009',
					  "ADDRESS" => 'test address',
					  "CITY" => 'LOS ANGLES',
					  "STATE" => 'California',
					  "ZIPCODE" => '90007'


					]
				]);
			
				print_r($response);
			} catch (MailchimpMarketing\ApiException $e) {
				echo $e->getMessage();
			}
			
			// mailchimp code end here
			
			

		}

}
