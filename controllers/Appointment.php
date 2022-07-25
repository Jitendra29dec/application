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


class Appointment extends CI_Controller {

    function __construct()
    {
    	
        // Construct the parent class
        parent::__construct();
		$this->load->helper('db');
		$this->load->library('SendFcm');
		$this->load->library('SendFcm1');
		//error_reporting(0);
		
    }
	
	
	public function add_old(){
		//echo "djd";die;
		$vendor_id = $this->input->post('vendor_id');
		$service = $this->input->post('service');
		$stylist = $this->input->post('stylist');
		$appointment_date = $this->input->post('appointment_date');
		$appointment_time = $this->input->post('appointment_time');
		$price = $this->input->post('price');
		$duration = $this->input->post('duration');
		
		$customer_id = $this->input->post('customer');
		$appointment_note = $this->input->post('appointment_note');
		$appointment_type = 1;
	
		
		$appointment_date = date('Y-m-d',strtotime($appointment_date));
	//	print_r($stylist);die;
		//if (is_array($service)){ 
		
		
				
		$start_time=$appointment_time[0];
		$min = $duration[0];
		$endtime_new = strtotime("+$min minutes",strtotime($start_time));
		
		if(!empty($vendor_id) && !empty($service) && !empty($stylist) && !empty($appointment_date) && !empty($appointment_time) && !empty($price) && !empty($duration) && !empty($customer_id)){
	
		$check_result=$this->db->query('select A.* from appointment as A inner join appointment_service as B on A.appointment_id=B.appointment_id where A.date="'.$appointment_date.'" and B.appointment_time<="'.$endtime_new.'" and B.appointment_end_time>="'.$start_time.'"  and B.stylist_id="'.$stylist[0].'" and A.color_code!="7" and A.vendor_id="'.$vendor_id.'" ')->result();
			
		if(count($check_result)<=0){
			
			$qry = $this->db->query("insert into appointment set created_by='".$this->session->userdata("login_id")."', author='1', date='".$appointment_date."', customer_id='".$customer_id."', created_date='".date('Y-m-d H:i:s')."',note='".addslashes($appointment_note)."',color_code='9',rendering='back', vendor_id='".$vendor_id."', is_active='1', appointment_type='".$appointment_type."', status='1' ");
			$insert_id = $this->db->insert_id();
		//	echo $this->db->last_query();exit;
			$qqr = $this->db->query("insert into appointment_status set appointment_id='".$insert_id."', status='1', created_date='".date('Y-m-d H:i:s')."' ");
			
			if($insert_id){
				
				// for($i=0;$i<count($service);$i++){
					
					if($service!=0){
						
					$service_id = $service;
					$stylist_id = $stylist;
					$appointment_time_entry = $appointment_time;
					$price_entry = $price;
					$duration_entry = $duration;
					$points_entry = $points;
					
					$time = strtotime($appointment_time);
					$apt_end_time = date("H:i", strtotime("+$duration_entry minutes", $time));
					
					$ad_service = $this->db->query("insert into appointment_service set appointment_id='".$insert_id."', service_id='".$service_id."', stylist_id='".$stylist_id."', appointment_time='".$appointment_time_entry."',appointment_end_time='".$apt_end_time."',  price='".$price_entry."',duration='".$duration_entry."', customer_id='".$customer_id."' ");
					
					
					
					}
			//	} 
				
				
				
				$qr = $this->db->query("select max(token_no) as token_no from appointment");
				$re = $qr->row();
				$token = $re->token_no+1;
				$this->db->query("update appointment set `token_no`='".$token."' where appointment_id='".$insert_id."' ");
				
				
				
				$customer_detail = $this->getCustomerById($customer_id);
				$customer_name = $customer_detail->customer_name;
				$fcm_token = $customer_detail->fcm_token;
				$to = $customer_detail->email;
				$data['customer_name'] =  $customer_name; 
				$data['appointment_date'] = $appointment_date; 
				$data['appointment_time'] = $appointment_time; 
				$data['service'] = $service; 
				
				$appointment_time = $start_time.'  '.$endtime_new;
				$appointment_date = date('M d Y',strtotime($appointment_date));
				if($start_time>'11:59'){
					
					$time_format = 'AM';
				}ELSE{
					
					$time_format = 'PM';
				}
				
				
				// Send Push Notification
				$this->load->library('SendFcm');
				$subject="BookNPay Appointment";
				$message="Your appointment is booked with bookNPay.";
				$subtitle="Testing";
				$this->load->library('SendFcm');
				$fcm_responce=$this->sendfcm->sendNotification($title,$message, array($fcm_token,$subtitle));
					
				
			//setup SMTP configurion
		$config = Array(    
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
		$this->email->from('info@booknpay.com', 'BookNPay');

		$data = array(
			'stylist_name'=> $stylist_name,
			'customer_name'=> $customer_name,
			'appointment_date'=> $appointment_date,
			'service_name'=> $service,
			'appointment_date'=> $appointment_date,
			'appointment_date'=> $appointment_date,
			'start_time'=> $start_time,
			'end_time'=> $endtime_new,
			'time_format'=> $time_format
			

        );
		
		
		$this->email->to($to); // replace it with receiver email id
		$this->email->subject($subject); // replace it with email subject
		///////////$message = $this->load->view('new_appointment',$data,TRUE);
		$memssage = 'New Appointment';
		$this->email->message($message); 
		$this->email->send();
			
				
				
				
				// email to stylist
				
				$stylist_info = $this->getStylistById($stylist[0]);
				$stylist_email = $stylist_info->email;
				$stylist_name = $stylist_info->stylist_name;
				
				$subject = 'New Appontment for you on BookNPay';
				
				$headers = "From: info@booknpay.com\r\n";
				$headers .= "Reply-To:  info@booknpay.com\r\n";
				$headers .= "MIME-Version: 1.0\r\n";
				$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
				//$message = $this->load->view('emailtemplate/new_appointment',$data,TRUE);
				//$this->email->message($emailTemplate);
				
				$message2 = "Dear $stylist_name <br/>New appointment is booked for you by a customer $customer_name on $appointment_date at $start_time $time_format<br/><br/>Thank You<br/>Team BookNPay";
				
				mail($stylist_email, $subject, $message2, $headers);
				
				
				$response['status'] = 1;
				$response['message'] = 'Appointment booked successfully';
			
			}else{
				$response['status'] = 0;
				$response['message'] = 'Something went wrong';
			}
			
		/* }else{
			echo "Required parameter missing!"; 
		} */
		
		}else{
		
			$response['status'] = 0;
			$response['message'] = 'Booking is not allowed at this time';
		}
		
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
	}
	
	
	
	
	public function show(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		$stylist_id = $this->input->post('stylist_id');
		
		$view_type = $this->input->post('view_type');
		$start_date = $this->input->post('start_date');
		$end_date = $this->input->post('end_date');
		
		$and = '';
		$stylist_id = @$_POST['stylist_id'];
		
		$stylist_id = str_replace(","," OR aps.stylist_id=",$stylist_id);
		if($stylist_id==''){
			$and = "";
		}else{
			$and = " AND ( aps.stylist_id=$stylist_id )";
		}
		//IF(aps.appointment_time IS NULL,CONCAT(a.date,'','00:00'),CONCAT(a.date,' ',aps.appointment_time)) AS date,
										
		
		if(!empty($vendor_id)){
			
			if($view_type=='day' || $view_type=='month'){
			/*CONCAT(time_format(aps.appointment_time,'%h %i %p'),'\n\n', s.service_name,'\n\n','S-', st.firstname,' ',st.lastname,'\n','C-',c.firstname,' ',c.lastname,'\n\n',s.service_name) as title,*/
			$query = $this->db->query("select 
										a.appointment_type,
										a.token_no
										,a.appointment_id as id,
										 CONCAT(a.date,' ',aps.appointment_time) as start,
                    					CONCAT(a.date,' ',aps.appointment_end_time) as end,
										'apt_data' as title,
										(case when (aps.appointment_time='') then CONCAT(a.date,'-','00:00') else CONCAT(a.date,' ',aps.appointment_time) end) AS date,
										s.service_name as service_name,
										CONCAT(c.firstname,' ',c.lastname) as customer_name,
										CONCAT(st.firstname,' ',st.lastname) as stylist_name,
										st.stylist_id as stylist,
										(case when a.appointment_type=3 then a.group_leader_id else a.customer_id end) as customer_id,
										aps.duration as duration,
										(case when a.appointment_type=1 then 'S' when a.appointment_type=2 then 'M' else 'G' end) as rendering,
										cs.color_code as color,
										cs.color_code as backgroundColor,
										a.color_code as color_id,
										a.deposit_id as deposit_id,
										a.is_checkout
										from appointment a
										INNER JOIN appointment_service aps
										ON aps.appointment_id=a.appointment_id
										INNER JOIN service s
										on s.service_id=aps.service_id
										INNER JOIN stylist st
										ON st.stylist_id=aps.stylist_id
										INNER JOIN customer c
										ON c.customer_id=a.customer_id
										INNER JOIN color_settings cs
										ON cs.color_id=a.color_code
										where a.vendor_id='".$vendor_id."'
										and a.is_delete='0' and a.is_active='1'  
										$and  
										order by a.appointment_id desc");
		
			$result = $query->result();
			
			$result2=$this->db->query("select A.event_name as title,B.deposit_id as token1,A.id as token2,CONCAT(date_format(B.start_date,'%Y-%m-%d'),' ',TIME_FORMAT(B.start_time,'%H:%i')) as start,CONCAT(date_format(B.start_date,'%Y-%m-%d'),' ',TIME_FORMAT(B.end_time,'%H:%i')) as end,CONCAT(c.firstname,' ',c.lastname) as customer_name,cs.color_code as color,'D' as rendering,A.customer_total as total_customer,A.customer_id as group_incharge_id from deposit_customer as A inner join deposit_customer_schedule as B on A.id=B.deposit_id inner join customer as c on A.customer_id=c.customer_id inner join color_settings as cs on B.color_id=cs.color_id where B.is_appointment=0 and A.status=1 AND A.vendor_id='".$vendor_id."'")->result();
			
			}else if($view_type=='week'){
				/*(SELECT COUNT(dc.id) from deposit_customer dc INNER JOIN deposit_customer_schedule dcs ON dcs.deposit_id=dc.id where dc.vendor_id='".$vendor_id."' AND dcs.start_date=a.date AND hour(dcs.start_time)=hour(aps.appointment_time)) AS deposit_count,*/
				//AND hour(aps.appointment_time)=aps.appointment_time
				$query = $this->db->query("select 
									a.appointment_id as id,
									a.customer_id,
									a.note as appointment_note,
									'' as rendering,
									'apt_data' as title,
									aps.stylist_id as resourceId,
									CONCAT(a.date,' ',aps.appointment_time) as start, 
									CONCAT(a.date,' ',aps.appointment_end_time) as end,
									s.service_name as service_name,
									CONCAT(st.firstname) as stylist_name,
									st.stylist_id as stylist_id,
									COUNT(a.appointment_id) as total_apt,
									COUNT(CASE WHEN a.appointment_type = 1  then 1 else NULL end) AS `single`,
									COUNT(CASE WHEN a.appointment_type = 2  then 1 else NULL end) AS `multiple`,
									COUNT(CASE WHEN a.appointment_type = 3  then 1 else NULL end) AS `group`,
									'#7FB3D5' as color,
									a.deposit_id as deposit_id,
									a.color_code,'week' as viewtype from appointment a
   									INNER JOIN appointment_service aps
   									ON aps.appointment_id=a.appointment_id
   									LEFT JOIN service s
   									ON aps.service_id=s.service_id
   									INNER JOIN color_settings cs
   									ON cs.color_id=a.color_code
   									INNER JOIN stylist st ON st.stylist_id=aps.stylist_id
   									INNER JOIN customer c ON c.customer_id=aps.customer_id
   									where aps.is_addon=0
   									AND a.vendor_id='".$vendor_id."'
   									AND a.is_active='1'
   									AND a.is_draft='0'
   									AND a.color_code!='7' 
									".$and."
									group by a.token_no,hour(aps.appointment_time),a.date
									 order by a.date desc
   									
   									");
				$result = $query->result();
				$result2=$this->db->query("select COUNT(A.id) as total_deposit,B.deposit_id as token1,A.id as token2,CONCAT(date_format(B.start_date,'%Y-%m-%d'),' ',TIME_FORMAT(B.start_time,'%H:%i')) as start,CONCAT(date_format(B.start_date,'%Y-%m-%d'),' ',TIME_FORMAT(B.end_time,'%H:%i')) as end,'#7FB3D5' as color,A.customer_total as total_customer from deposit_customer A INNER JOIN deposit_customer_schedule B ON B.deposit_id=A.id inner join color_settings as cs on B.color_id=cs.color_id where 
				 A.status=1 AND A.vendor_id='".$vendor_id."' group by hour(B.start_time),B.start_date")->result();
					$result = $query->result();			
				/* 
				$result2 = '';					
				$response['status'] = 1;
				$response['result'] = $result;
				//$response['deposit'] = $result2;
				$response['message'] = ''; */
			}
			
			$week_day_start=$this->db->query('select value from settings where vendor_id="'.$resultData->vendor_id.'" and field="week_start_day"')->row();
			if(!empty($week_day_start)){
				$start_day=$week_day_start->value;
			}else{
				$start_day='Monday';
			}
			$apt_rules = $this->db->query("select * from appointment_rules where vendor_id='".$vendor_id."' ")->row();

			$query_batch_close_time=$this->db->query('select value as is_batch_close, first_time as batch_close_time from settings where vendor_id="'.$resultData->vendor_id.'" and field="batch_close_time"')->row();
			
			$is_batch_close=$query_batch_close_time->is_batch_close;
			if($is_batch_close=='1'){

				$batch_close_time=$query_batch_close_time->batch_close_time;

			}else{
				$batch_close_time = "";
			}
			
		
			
			$response['status'] = 1;
			$response['result'] = $result;
			$response['deposit'] = $result2;
			$response['start_day']=$start_day;
			$response['apt_rules ']=$apt_rules ;
			$response['batch_close_time ']=$batch_close_time ;
			$response['message'] = '';
			
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
	}
		public function show_new(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		$stylist_id = $this->input->post('stylist_id');
		
		$view_type = $this->input->post('view_type');
		$start_date = $this->input->post('start_date');
		$end_date = $this->input->post('end_date');
		
		$and = '';
		$stylist_id = @$_POST['stylist_id'];
		
		$stylist_id = str_replace(","," OR aps.stylist_id=",$stylist_id);
		if($stylist_id==''){
			$and = "";
		}else{
			$and = " AND ( aps.stylist_id=$stylist_id )";
		}
		//IF(aps.appointment_time IS NULL,CONCAT(a.date,'','00:00'),CONCAT(a.date,' ',aps.appointment_time)) AS date,
								
		
		if(!empty($vendor_id)){
			
			if($view_type=='month' || $view_type=='day1'){
			/*CONCAT(time_format(aps.appointment_time,'%h %i %p'),'\n\n', s.service_name,'\n\n','S-', st.firstname,' ',st.lastname,'\n','C-',c.firstname,' ',c.lastname,'\n\n',s.service_name) as title,*/
			$query = $this->db->query("select 
										a.appointment_type,
										a.token_no
										,a.appointment_id as id,
										 CONCAT(a.date,' ',aps.appointment_time) as start,
                    					CONCAT(a.date,' ',aps.appointment_end_time) as end,
										'apt_data' as title,
										(case when (aps.appointment_time='') then CONCAT(a.date,'-','00:00') else CONCAT(a.date,' ',aps.appointment_time) end) AS date,
										s.service_name as service_name,
										CONCAT(c.firstname,' ',c.lastname) as customer_name,
										CONCAT(st.firstname,' ',st.lastname) as stylist_name,
										st.stylist_id as stylist,
										(case when a.appointment_type=3 then a.group_leader_id else a.customer_id end) as customer_id,
										aps.duration as duration,
										(case when a.appointment_type=1 then 'S' when a.appointment_type=2 then 'M' else 'G' end) as rendering,
										cs.color_code as color,
										cs.color_code as backgroundColor,
										a.color_code as color_id,
										a.deposit_id as deposit_id,
										a.is_checkout
										from appointment a
										INNER JOIN appointment_service aps
										ON aps.appointment_id=a.appointment_id
										INNER JOIN service s
										on s.service_id=aps.service_id
										INNER JOIN stylist st
										ON st.stylist_id=aps.stylist_id
										INNER JOIN customer c
										ON c.customer_id=a.customer_id
										INNER JOIN color_settings cs
										ON cs.color_id=a.color_code
										where a.vendor_id='".$vendor_id."'
										and a.is_delete='0' and a.is_active='1'  
										$and  
										order by a.appointment_id desc");
		
			$result = $query->result();
			
			$result2=$this->db->query("select A.event_name as title,B.deposit_id as token1,A.id as token2,CONCAT(date_format(B.start_date,'%Y-%m-%d'),' ',TIME_FORMAT(B.start_time,'%H:%i')) as start,CONCAT(date_format(B.start_date,'%Y-%m-%d'),' ',TIME_FORMAT(B.end_time,'%H:%i')) as end,CONCAT(c.firstname,' ',c.lastname) as customer_name,cs.color_code as color,'D' as rendering,A.customer_total as total_customer,A.customer_id as group_incharge_id from deposit_customer as A inner join deposit_customer_schedule as B on A.id=B.deposit_id inner join customer as c on A.customer_id=c.customer_id inner join color_settings as cs on B.color_id=cs.color_id where B.is_appointment=0 and A.status=1 AND A.vendor_id='".$vendor_id."' and B.start_date BETWEEN '".$start_date."' AND '".$end_date."'")->result();
			
			}

			else if($view_type=='day'){
				//echo 'select concat(st.firstname,"",st.lastname) as stylist_name,aps.stylist_id from appointment as a inner join appointment_service as aps on a.appointment_id=aps.appointment_id inner join stylist as st on aps.stylist_id=st.stylist_id where a.date="'.$start_date.'" and a.vendor_id="'.$vendor_id.'" group by aps.stylist_id';die;
				if($_SERVER['HTTP_HOST']=='localhost'){
					$path = site_url().'/assets/img/stylist/thumb/';
				}else{
					$path = "http://".$_SERVER['HTTP_HOST'].'/salon/assets/img/stylist/thumb/';
				}
				$getStylist=$this->db->query("select concat(st.firstname,' ',st.lastname) as stylist_name,st.stylist_id,CONCAT('$path','/',if(st.photo=' ','noimage.png',st.photo)) as photo from stylist as st inner join login as l on st.login_id=l.login_id inner join stylist_schedule as ss on st.stylist_id=ss.stylist_id where ss.start_date='".$start_date."' and l.vendor_id='".$vendor_id."' and l.is_active=1 group by stylist_name")->result();
				
				//echo "<pre>";print_r($getStylist);exit;

				$result=array();
				$result_array_new=array();

				if(!empty($getStylist)){
				foreach($getStylist as $val){

					$getScheduleData=$this->db->query("select
										a.appointment_id as id, 
										a.appointment_type,
										a.token_no,
										
										aps.appointment_time as start,
                    					aps.appointment_end_time  as end,
										 CONCAT('C: ',c.firstname,' ',c.lastname)  as title,
										(case when (aps.appointment_time='') then CONCAT(a.date,'-','00:00') else CONCAT(a.date,' ',aps.appointment_time) end) AS date,
										s.service_name as service_name,
										CONCAT(c.firstname,' ',c.lastname) as customer_name,
										CONCAT(st.firstname,' ',st.lastname) as stylist_name,
										st.stylist_id as stylist,
										(case when a.appointment_type=3 then a.group_leader_id else a.customer_id end) as customer_id,
										aps.duration as duration,
										(case when a.appointment_type=1 then 'S' when a.appointment_type=2 then 'M' else 'G' end) as rendering,
										cs.color_code as color,
										cs.color_code as backgroundColor,
										a.color_code as color_id,
										
										a.is_checkout,
										cs.color_id
										from appointment a
										INNER JOIN appointment_service aps
										ON aps.appointment_id=a.appointment_id
										INNER JOIN service s
										on s.service_id=aps.service_id
										INNER JOIN stylist st
										ON st.stylist_id=aps.stylist_id
										INNER JOIN customer c
										ON c.customer_id=a.customer_id
										INNER JOIN color_settings cs
										ON cs.color_id=a.color_code
										where a.vendor_id='".$vendor_id."'
										and a.is_delete='0' and a.is_active='1' and a.date = '".$start_date."' and aps.stylist_id='".$val->stylist_id."' ".$and."   order by aps.appointment_time asc")->result();
					     $result['employee'][]=array('name'=>$val->stylist_name,'employee_image'=>$val->photo,'schedule'=>$getScheduleData);
				}
			}else{
				 $result['employee']=array();
			}

			}

			else if($view_type=='event'){
				/*echo 'select e.id as event_id,e.event_name,sch.start_date,sch.start_time,sch.end_time,cs.color_code as backgroundColor from deposit_customer as e inner join deposit_customer_schedule as sch on e.id=sch.deposit_id inner JOIN color_settings cs
										ON cs.color_id=sch.color_id where sch.start_date="'.$start_date.'" and e.vendor_id="'.$vendor_id.'" group by sch.start_date,sch.deposit_id';die;*/
				$getEventData=$this->db->query('select e.id as event_id,e.event_name,sch.start_date,sch.start_time,sch.end_time,cs.color_code as backgroundColor from deposit_customer as e inner join deposit_customer_schedule as sch on e.id=sch.deposit_id inner JOIN color_settings cs
										ON cs.color_id=sch.color_id where sch.start_date="'.$start_date.'" and e.vendor_id="'.$vendor_id.'" group by sch.start_date,sch.deposit_id')->result();
				if(!empty($getEventData)){
					 $result=$getEventData;
				}else{
					$result=array();
				}
			}
			else if($view_type=='week'){
				//#7FB3D5
				/*(SELECT COUNT(dc.id) from deposit_customer dc INNER JOIN deposit_customer_schedule dcs ON dcs.deposit_id=dc.id where dc.vendor_id='".$vendor_id."' AND dcs.start_date=a.date AND hour(dcs.start_time)=hour(aps.appointment_time)) AS deposit_count,*/
				//AND hour(aps.appointment_time)=aps.appointment_time
				$query = $this->db->query("select 
									a.appointment_id as id,
									a.customer_id,
									a.note as appointment_note,
									'' as rendering,
									'apt_data' as title,
									aps.stylist_id as resourceId,
									CONCAT(a.date,' ',aps.appointment_time) as start, 
									CONCAT(a.date,' ',aps.appointment_end_time) as end,
									s.service_name as service_name,
									CONCAT(st.firstname) as stylist_name,
									st.stylist_id as stylist_id,
									COUNT(a.appointment_id) as total_apt,
									COUNT(CASE WHEN a.appointment_type = 1  then 1 else NULL end) AS `single`,
									COUNT(CASE WHEN a.appointment_type = 2  then 1 else NULL end) AS `multiple`,
									COUNT(CASE WHEN a.appointment_type = 3  then 1 else NULL end) AS `group`,
									'#007bff' as color,
									a.deposit_id as deposit_id,
									a.color_code,'week' as viewtype from appointment a
   									INNER JOIN appointment_service aps
   									ON aps.appointment_id=a.appointment_id
   									LEFT JOIN service s
   									ON aps.service_id=s.service_id
   									INNER JOIN color_settings cs
   									ON cs.color_id=a.color_code
   									INNER JOIN stylist st ON st.stylist_id=aps.stylist_id
   									INNER JOIN customer c ON c.customer_id=aps.customer_id
   									where aps.is_addon=0
   									AND a.vendor_id='".$vendor_id."'
   									AND a.is_active='1'
   									AND a.is_draft='0'
   									and a.date BETWEEN '".$start_date."' AND '".$end_date."' 
									".$and."
									group by a.token_no,hour(aps.appointment_time),a.date
									 order by a.date desc
   									
   									");
				$result = $query->result();
				$result2=$this->db->query("select ap.appointment_id,COUNT(A.id) as total_deposit,B.deposit_id as token1,A.id as token2,CONCAT(date_format(B.start_date,'%Y-%m-%d'),' ',TIME_FORMAT(B.start_time,'%H:%i')) as start,CONCAT(date_format(B.start_date,'%Y-%m-%d'),' ',TIME_FORMAT(B.end_time,'%H:%i')) as end,'#007bff' as color,A.customer_total as total_customer from deposit_customer A INNER JOIN deposit_customer_schedule B ON B.deposit_id=A.id left join color_settings as cs on B.color_id=cs.color_id inner join appointment as ap on A.id=ap.deposit_id where A.status=1 AND A.vendor_id='".$vendor_id."' AND B.start_date BETWEEN '".$start_date."' AND '".$end_date."' group by hour(B.start_time),B.start_date")->result();
					$result = $query->result();			
				/* 
				$result2 = '';					
				$response['status'] = 1;
				$response['result'] = $result;
				//$response['deposit'] = $result2;
				$response['message'] = ''; */
			}
			/*permission*/
			$login_id=$this->input->post('login_id');
		$getRoleId=$this->db->query('select role_id from login where login_id="'.$login_id.'"')->row();	
			if($getRoleId->role_id=='3'){
					//$q = $this->db->query("select sp.permission_id from stylist_permission sp WHERE role_id='".$resultData->role_id."' AND sp.vendor_id='".$resultData->vendor_id."' ");
					$getRoleIdNew=$this->db->query('select title_id from stylist where login_id="'.$login_id.'"')->row();
					$q = $this->db->query("select sp.permission_id from stylist_permission sp WHERE role_id='".$getRoleIdNew->title_id."' AND sp.vendor_id='".$vendor_id."' ");
	
					
					
					
				}elseif($getRoleId->role_id=='1'){
					$q = $this->db->query("select id as permission_id from permission ");
					
					
				}else{
					$q = $this->db->query("select sp.permission_id from stylist_permission sp  WHERE role_id='".$getRoleId->role_id."' AND sp.vendor_id='".$resultData->vendor_id."' ");
					
				}
				$permission = $q->result();
				//echo "<pre>";print_r($permission);exit;
			/*permission*/
			$week_day_start=$this->db->query('select value from settings where vendor_id="'.$resultData->vendor_id.'" and field="week_start_day"')->row();
			if(!empty($week_day_start)){
				$start_day=$week_day_start->value;
			}else{
				$start_day='Monday';
			}
			$apt_rules = $this->db->query("select * from appointment_rules where vendor_id='".$vendor_id."' ")->row();
			
			$response['status'] = 1;
			$response['result'] = $result;
			$response['deposit'] = $result2;
			$response['start_day']=$start_day;
			$response['apt_rules ']=$apt_rules ;
			$response['permission'] = $permission;
			$response['message'] = '';
			
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
	}
	function viewAptDetail(){
		
		$event_date = $this->input->post('event_date');
		$event_time = $this->input->post('event_time');
		$vendor_id = $this->input->post('vendor_id');
		$event_date =  date('Y-m-d',strtotime($event_date));
		
		$et = explode(':',$event_time);
		$eet = $et[0]+1;
		if($eet <=9){
			$ee1='0';
		}else{
			$ee1='';
		}
		$event_end_time = $ee1.$eet.':00';
	
		$query = $this->db->query("select a.date as appointment_date,a.token_no,a.customer_id, aps.appointment_time, aps.appointment_end_time, CONCAT(c.firstname,' ',c.lastname) as customer_name,c.note, s.service_name, CONCAT(st.firstname,' ',st.lastname) as stylist_name, aps.price, aps.duration, a.appointment_id, a.appointment_type,cs.name  as status,cs.color_code,a.is_checkout,cs.color_id from appointment a INNER JOIN appointment_service aps ON aps.appointment_id=a.appointment_id INNER JOIN customer c ON c.customer_id=aps.customer_id INNER JOIN stylist st ON st.stylist_id=aps.stylist_id INNER JOIN service s ON s.service_id=aps.service_id INNER JOIN color_settings cs ON cs.color_id=a.color_code WHERE a.date='".$event_date."' AND aps.appointment_time>='".$event_time."' AND aps.appointment_time<'".$event_end_time."' AND a.vendor_id='".$vendor_id."' AND a.appointment_type='1' AND a.is_active='1'
   									AND a.is_draft='0'  ");
		$data['result'] = $query->result();
		if(!empty($data['result'] )){
			$response['single_appointment']=$data['result'];
			//$response['message']="Data found";
		}else{
			$response['single_appointment']=array();
			///$response['message']="NO data found";
		}
		//AND aps.appointment_time>='".$event_time."' AND aps.appointment_time<'".$event_end_time."'
		$query2 = $this->db->query("select a.date as appointment_date,a.token_no,a.customer_id, aps.appointment_time, aps.appointment_end_time, CONCAT(c.firstname,' ',c.lastname) as customer_name,c.note, s.service_name, CONCAT(st.firstname,' ',st.lastname) as stylist_name, aps.price, aps.duration, a.appointment_id, a.appointment_type,cs.name  as status,cs.color_code,a.is_checkout,cs.color_id from appointment a INNER JOIN appointment_service aps ON aps.appointment_id=a.appointment_id INNER JOIN customer c ON c.customer_id=aps.customer_id INNER JOIN stylist st ON st.stylist_id=aps.stylist_id INNER JOIN service s ON s.service_id=aps.service_id INNER JOIN color_settings cs ON cs.color_id=a.color_code WHERE a.date='".$event_date."'   AND aps.appointment_time>='".$event_time."' and aps.appointment_time<'".$event_end_time."' AND a.vendor_id='".$vendor_id."' AND a.appointment_type='2' AND a.is_active='1'
   									AND a.is_draft='0'  ");
		$data['result2'] = $query2->result();
		if(!empty($data['result2'] )){
			$response['multiple_appointment']=$data['result2'];
			//$response['message']="Data found";
		}else{
			$response['multiple_appointment']=array();
			//$response['message']="No data found";
		}
		$groupDataArray=array();
		$getDataGroup=$this->db->query("select a.token_no,CONCAT(c.firstname,' ',c.lastname) as group_head_name,a.deposit_id as deposit_id from appointment a INNER JOIN appointment_service aps ON aps.appointment_id=a.appointment_id INNER JOIN customer c ON c.customer_id=a.group_leader_id WHERE a.date='".$event_date."' AND aps.appointment_time>='".$event_time."' AND aps.appointment_time < '".$event_end_time."' AND a.vendor_id='".$vendor_id."' AND a.appointment_type='3' AND a.is_active='1'
   									AND a.is_draft='0' group by a.token_no,a.date")->result();
		//echo "select a.token_no,CONCAT(c.firstname,' ',c.lastname) as group_head_name from appointment a INNER JOIN appointment_service aps ON aps.appointment_id=a.appointment_id LEFT JOIN customer c ON c.customer_id=aps.customer_id WHERE a.date='".$event_date."' AND aps.appointment_time>='".$event_time."' AND aps.appointment_time < '".$event_end_time."' AND a.vendor_id='".$vendor_id."' AND a.appointment_type='3' group by a.token_no";die;
		foreach($getDataGroup as $key=> $groupData){
			
		$groupDataArray[$groupData->group_head_name]= $this->db->query("select a.date as appointment_date,a.token_no,a.customer_id,a.group_leader_id,aps.appointment_time, aps.appointment_end_time, CONCAT(c.firstname,' ',c.lastname) as customer_name,c.note, s.service_name, CONCAT(st.firstname,' ',st.lastname) as stylist_name, aps.price, aps.duration, a.appointment_id, a.appointment_type,cs.name  as status,cs.color_code,a.is_checkout,cs.color_id,a.deposit_id,a.is_checkout,(case when a.deposit_id !=0 && a.deposit_id IS NOT NULL then (select event_name from deposit_customer where id=a.deposit_id) else '-' end ) as event_name from appointment a INNER JOIN appointment_service aps ON aps.appointment_id=a.appointment_id INNER JOIN customer c ON c.customer_id=a.customer_id INNER JOIN stylist st ON st.stylist_id=aps.stylist_id INNER JOIN service s ON s.service_id=aps.service_id INNER JOIN color_settings cs
			ON cs.color_id=a.color_code WHERE a.token_no='".$groupData->token_no."' AND a.is_active='1'
   									AND a.is_draft='0' ")->result();
		
		if($groupData->deposit_id !=0 &&  !empty($groupData->deposit_id)){
			$checkData=$this->db->query('select appointment_id from appointment where is_checkout=0 and deposit_id="'.$groupData->deposit_id.'" and token_no="'.$groupData->token_no.'"')->num_rows();
			$checkDeposit=$this->db->query('select require_deposit,	balance_used from deposit_customer where id="'.$groupData->deposit_id.'" ')->row();
			$getDepositAmountCustomer=$this->db->query('select id,sum(amount) as total_amount from deposit_installment where is_active=1 and deposit_id="'.$groupData->deposit_id.'"')->row();
			if(!empty($getDepositAmountCustomer)){
				//echo "harsh";
				$deposit_amount=$getDepositAmountCustomer->total_amount - $checkDeposit->balance_used;
			}else{
				//echo "vishal";
				$deposit_amount=0;
			}
			//echo $checkData."-".$deposit_amount;
			if($checkData <=0 && $deposit_amount==0){
				//echo "harsh";
				$groupDataArray[$groupData->group_head_name][0]->button="hide";
				$groupDataArray[$groupData->group_head_name][0]->return_amount="0";
			}else if($checkData <=0  && $deposit_amount > 0){
				//echo "vishal";
				$groupDataArray[$groupData->group_head_name][0]->button="final";
				$groupDataArray[$groupData->group_head_name][0]->return_amount=$deposit_amount;
			}
			else{
				//echo "jitu";
					$groupDataArray[$groupData->group_head_name][0]->button="show";
					$groupDataArray[$groupData->group_head_name][0]->return_amount="0";

			}	

		}else{
			$checkData=$this->db->query('select appointment_id from appointment where is_checkout=0 and token_no="'.$groupData->token_no.'"')->num_rows();
			if($checkData <=0 ){
				$groupDataArray[$groupData->group_head_name][0]->button="hide";
				$groupDataArray[$groupData->group_head_name][0]->return_amount="0";
			}else{
					$groupDataArray[$groupData->group_head_name][0]->button="show";
					$groupDataArray[$groupData->group_head_name][0]->return_amount="0";
			}
			
		}
			

		}
		
		if(!empty($groupDataArray)){
			$response['group_appointment']=$groupDataArray;
			//$response['message']="Data found";
		}else{
			$response['group_appointment']=(Object)[];
			//$response['message']="No data found";
		}

		/*Group Appointment*/

		/* Event Group*/
		$query4 = $this->db->query("select a.id as token2, CONCAT(c.firstname,' ',c.lastname) as customer_name,c.note,a.customer_id,a.customer_total as total_customer, a.event_name, b.start_date, b.start_time from deposit_customer a left JOIN deposit_customer_schedule b ON b.deposit_id=a.id LEFT JOIN customer c ON c.customer_id=a.customer_id where a.vendor_id='".$vendor_id."' AND b.start_date='".$event_date."' AND b.start_time >='".$event_time."' AND b.start_time<'".$event_end_time."' and a.status=1 group by b.deposit_id  ");
	//	echo "select a.id as token2, CONCAT(c.firstname,' ',c.lastname) as customer_name,c.note,a.customer_id,a.customer_total as total_customer, a.event_name, b.start_date, b.start_time from deposit_customer a INNER JOIN deposit_customer_schedule b ON b.deposit_id=a.id LEFT JOIN customer c ON c.customer_id=a.customer_id where a.vendor_id='".$vendor_id."' AND b.start_date='".$event_date."' AND b.start_time >='".$event_time."' AND b.start_time<'".$event_end_time."' and a.status=1 group by b.deposit_id";die;
			$result4 = $query4->result();
			$getEventArray=array();
			foreach($result4  as $resVal){

				$getEventArray[$resVal->event_name]=$this->db->query("select a.id as deposit_id, CONCAT(c.firstname,' ',c.lastname) as customer_name,c.note,a.customer_id,b.no_of_customer as total_customer, a.event_name, date_format(b.start_date,'%m-%d-%Y') as event_date, b.start_time from deposit_customer a INNER JOIN deposit_customer_schedule b ON b.deposit_id=a.id LEFT JOIN customer c ON c.customer_id=a.customer_id where b.deposit_id='".$resVal->token2."'  ")->result();
			}
		
		if(!empty($getEventArray )){
			$response['event']=$getEventArray;
			//$response['message']="Data found";
		}else{
			$response['event']=(Object)[];
			//$response['message']="No data found";
		}

		/*Event Group*/
		echo json_encode($response);
		
	}
	
	public function detail(){
		
		$vendor_id = $this->input->post('vendor_id');
		$appointment_id = $this->input->post('appointment_id');
		
		
		if(!empty($vendor_id) && !empty($appointment_id)){
			
			$query = $this->db->query("select a.appointment_id as id,UPPER(s.service_name) as title, s.service_id, CONCAT(a.date,' ',aps.appointment_time) as date, a.appointment_duration as duration, a.rendering, a.color_code as color, a.color_code as backgroundColor, st.stylist_id, CONCAT(st.firstname,' ',st.lastname) as stylist_name, a.customer_id, a.note from appointment a INNER JOIN appointment_service aps ON aps.appointment_id=a.appointment_id INNER JOIN service s on s.service_id=aps.service_id INNER JOIN stylist st ON st.stylist_id=aps.stylist_id where a.vendor_id='".$vendor_id."' AND a.appointment_id='".$appointment_id."' AND a.is_active='1' AND a.is_delete='0' ");
			
			$result = $query->result();
			$response['result'] = $result;
			$response['message'] = '';
			
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
	}
	
	
	
	function edit(){
		
		
		  $country = $this->ip_info("Visitor", "Country");
		if($country=='India'){
			date_default_timezone_set("Asia/Kolkata");
		}else{
			
			date_default_timezone_set("America/Los_Angeles");
		}
		
		
		$vendor_id = $this->input->post('vendor_id');
		$appointment_id = $this->input->post('appointment_id');
		$appointment_date = $this->input->post('appointment_date');
		$appointment_time = $this->input->post('appointment_time');
		$appointment_time_end = $this->input->post('appointment_time_end');
		$service_id = $this->input->post('service_id');
		$stylist_id = $this->input->post('stylist_id');
		$customer_id = $this->input->post('customer_id');
		$note = $this->input->post('note');
		
		$response['status'] = 0;
		$response['message'] = '';
		
		if(!empty($appointment_id) && !empty($appointment_date) && !empty($appointment_time)){
		$update = $this->db->query("update appointment set customer_id='".$customer_id."',date='".$appointment_date."', appointment_date='".$appointment_date."',note='".$note."' where appointment_id='".$appointment_id."' and vendor_id='".$vendor_id."' ");
		if($update){
			$serviceInfo = $this->getServiceById($service_id);
				$price = $serviceInfo->price;
				$duration = $serviceInfo->duration;
				$endTime = strtotime("+".$serviceInfo->duration."minutes", strtotime($appointment_time));
					$new_end_time=date('H:i', $endTime);
			$update_time = $this->db->query("update appointment_service set appointment_time='".$appointment_time."', appointment_end_time='".$new_end_time."', service_id='".$service_id."', stylist_id='".$stylist_id."',customer_id='".$customer_id."',price='".$price."' where appointment_id='".$appointment_id."' ");
			if($update_time){
			/*Mail Content*/
							/*Mail Content*/
							$customer_detail = $this->getCustomerById($customer_id);
							
					          $customer_name = $customer_detail->customer_name;
					          $to = $customer_detail->email;
					         // echo $to;die;
							/*if(!empty($service_id)){*/
							//echo "test";die;
					          $data['customer_name'] =  $customer_name; 
					          $data['appointment_date'] = $appointment_date; 
					          $data['appointment_time'] = $appointment_time; 
					          $data['phone'] = $customer_detail->mobile_phone;
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
					          $getAppointmentData=$this->db->query("select group_concat(concat(DAYNAME(a.date),' - ',date_format(a.date,'%c-%d-%Y')) SEPARATOR  ' /n ') as date,group_concat(time_format(a.date,'%c-%d-%Y')) as newDate,group_concat(concat(sr.service_name,' with ', concat(st.firstname,' ',st.lastname)) SEPARATOR  '-') as service_name,group_concat(aps.duration SEPARATOR  ' /n ') as duration,group_concat(time_format(aps.appointment_time,'%l:%i %p') SEPARATOR  ' /n ') as ap_time,group_concat(time_format(aps.appointment_time,'%l:%i %p')) as new_time,group_concat(concat(st.firstname,'',st.lastname)) as stylist_name,a.token_no from appointment as a inner join appointment_service as aps on aps.appointment_id=a.appointment_id inner join service as sr on sr.service_id=aps.service_id inner join stylist as st on st.stylist_id=aps.stylist_id where a.appointment_id='".$appointment_id."'")->row();
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

					     $newAppointemnt=$this->db->query('select email_subject,email_content from email_settings where slug="appointment_update" and is_active=1 and vendor_id="'.$vendor_id.'"')->row();
					     //echo "<pre>";print_r($newAppointemnt);exit;
					      if(!empty($newAppointemnt)){
					      	$data['template_type']='appointment_update';
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
						  $this->send_mail->sendMail($to, $subject, $message, $fileName=false, $filePath=false, $cc=false);
						  /*push Notification*/
						  $search1  = array('{Customer First Name}', '{Date}','{Time}','{Business Name}');
						  $replace1 = array($customer_name, $customer_detail->vendor_name);
						 	 $getDataNew=getImageTemplate($vendor_id,'new_appointment');
							$getNotification=str_replace($search1, $replace1, $getDataNew->sms_content);
						  $this->sendNotificationNew($customer_detail->login_id,"Edit Appointment",$getNotification,$getAppointmentData->token_no);
						  /*Push notification*/
						  
					      }
					/*  }*/

				$response['status'] = 1;
				$response['message'] = 'Appointment updated successfully';
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Something went wrong';
		}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	
	/* public function edit(){
		
		$vendor_id = $this->input->post('vendor_id');
		$appointment_id = $this->input->post('appointment_id');
		$appointment_date = $this->input->post('appointment_date'); 
		$appointment_time = $this->input->post('appointment_time'); 
		//$duration = $this->input->post('duration'); 
		$note = $this->input->post('note'); 
		$customer_id = $this->input->post('customer_id'); 
		$service_stylist = $this->input->post('service_stylist'); 
		$appointment_date = date('Y-m-d',strtotime($appointment_date));
		//echo $appointment_date;die;
		if(!empty($vendor_id) && !empty($appointment_date) && !empty($service_stylist) && !empty($customer_id) && !empty($appointment_id)){
			
			
			$qry = $this->db->query("update appointment set date='".$appointment_date."', note='".$note."', customer_id='".$customer_id."', color_code='1', rendering='back' where appointment_id='".$appointment_id."' ");
			
			$this->db->query("delete from appointment_service where appointment_id='".$appointment_id."' ");
			
			$service_stylist = json_decode($service_stylist);
			foreach($service_stylist as $ss){
				$qry2 = $this->db->query("insert into appointment_service set appointment_id='".$appointment_id."', service_id='".$ss->service_id."', stylist_id='".$ss->stylist_id."',appointment_time='".$appointment_time."', customer_id='".$customer_id."' ");
			}
		
				$response['success'] = 1;
				$response['message'] = 'Appointment updated successfully';
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
			
		}
		
		echo json_encode($response);
		
	} */
	
	
	public function changeStatus_jitu(){
		
		$appointment_id = $this->input->post('appointment_id');
		$status = $this->input->post('status');
		
		$response['status'] = 0;
		$response['message'] = '';
			
		if(!empty($status) && !empty($appointment_id)){
			
			if($status=='accept'){
				$color_code = '1';
			}
			if($status=='deny'){
				$color_code = '2';
			}
			if($status=='confirm'){
				$color_code = '3';
			}
			if($status=='shows'){
				$color_code = '4';
			}
			if($status=='no_show'){
				$color_code = '5';
			}
			if($status=='service_in_process'){
				$color_code = '6';
			}
			if($status=='cancel'){
				$color_code = '7';
			}
			if($status=='checkout'){
				$color_code = '8';
			}
			if($status=='mark_new'){
				$color_code = '9';
			}
			
			$qry = $this->db->query("update appointment set color_code='".$color_code."' where appointment_id='".$appointment_id."' ");
			
			
			$response['status'] = 1;
			$response['message'] = 'Appointment updated successfully! ';
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
	}
	
	public function getService(){
		
		$vendor_id = $this->input->post('vendor_id');
		$appointment_id = $this->input->post('appointment_id');
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
		if(!empty($appointment_id) && !empty($vendor_id)){
			//echo "select aps.service_id, s.service_name,s.price as service_price, s.duration as service_duration, ss.stylist_id,ss.price as stylist_price,ss.duration as stylist_duration from appointment_service aps INNER JOIN service s ON s.service_id=aps.service_id INNER JOIN stylist_service ss ON ss.service_id=aps.service_id where aps.appointment_id='".$appointment_id."' ";die;
			$query = $this->db->query("select
										a.appointment_id,
										aps.service_id,
										aps.stylist_id,
										s.service_name,
										CONCAT(st.firstname,' ',st.lastname) as stylist_name
										from appointment a 
										INNER JOIN  appointment_service aps
										ON aps.appointment_id=a.appointment_id
										INNER JOIN service s 
										ON s.service_id=aps.service_id
										INNER JOIN stylist st
										ON st.stylist_id=aps.stylist_id
										WHERE a.appointment_id='".$appointment_id."'
										AND a.vendor_id='".$vendor_id."'
										AND a.is_active='1'
										AND a.is_delete='0' ");
			
			$result = $query->result();
			
			$response['status'] = 1;
			$response['result'] = $result;
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
			
		}
		echo json_encode($response);
	}
	
	
	public function getAppointmentById($id){
		
		$query = $this->db->query("select * from appointment where appointment_id='".$id."' AND is_active='1' AND is_delete='0' ");
		$result = $query->row();
		return $result;
		
	}
	
	public function addCart(){
		
		
		$product_id = $this->input->post('product_id');
		$quantity = $this->input->post('quantity');
		
		$appointment_id = $this->input->post('appointment_id');
		$customer_id = $this->input->post('customer_id');
		
		
		if(!empty($product_id) && !empty($quantity)){
			
		$product_ids  = explode(',',$product_id);
		$qty  = explode(',',$quantity);
		
		if($appointment_id==0 || $appointment_id==NULL){
			
			$appointment_id = 0;
			$customer_id = $customer_id;
		}else{
			$appointment_data = $this->getAppointmentById($appointment_id);
			$customer_id = $appointment_data->customer_id;
			$appointment_id = $appointment_id;
		}
		
		for($i=0;$i<count($product_ids);$i++){
			
			$this->db->query("insert into cart set product_id='".$product_ids[$i]."', quantity='".$qty[$i]."', appointment_id='".$appointment_id."', customer_id='".$customer_id."', created_date='".date('Y-m-d H:i:s')."' ");
		}
		$response['status'] = 1;
		$response['message'] = 'Product added successfully';
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
        echo json_encode($response);
		
	}
	
	public function getProduct(){
		$path = "http://159.203.182.165/salon/assets/img/product/thumb";
		$selQuery = "select p.product_id, p.product_name, CONCAT('$path','/',p.main_image) as product_image, p.barcode_id, p.description,p.price_retail, c.category_name from product p INNER JOIN category c ON c.category_id=p.category_id where p.is_active='1'  and p.is_delete='0' order by product_id desc ";
		 $result = $this->db->query($selQuery)->result();
		 
		 $response['status'] = 1;
		 $response['result'] = $result;
		 
        echo json_encode($response);
		
	}
	
	public function getCart(){
		
		$appointment_id = $this->input->post('appointment_id');
		$customer_id = $this->input->post('customer_id');
		
		$path = "http://159.203.182.165/salon/assets/img/product/thumb";
		
		$response['status'] = 0;
		$response['message'] = '';
		
		if($appointment_id!='0'){
			
			$qry = $this->db->query("select customer_id from appointment where appointment_id='".$appointment_id."' ");
			$res = $qry->row();
			$customer_id = $res->customer_id;
			$AND = " AND c.appointment_id='".$appointment_id."' AND c.customer_id='".$customer_id."' ";
		}else{
			$AND = "  AND c.customer_id='".$customer_id."' ";
		}
			
		if(!empty($customer_id)){
		 
		 $selQuery = "select c.cart_id, c.quantity, p.product_name, CONCAT('$path','/',p.main_image) as product_image, p.barcode_id, p.price_retail from cart c INNER JOIN product p ON p.product_id=c.product_id where p.is_active='1'  and p.is_delete='0' $AND order by c.cart_id desc ";
		 
		 $result = $this->db->query($selQuery)->result();
		 $response['status'] = 1;
		 $response['result'] = $result;
		
		}
		
		else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		 
        echo json_encode($response);
		
	
	
	}
	
	
	public function getCheckoutList(){
		
		$appointment_id = $this->input->post('appointment_id');
		$customer_id = $this->input->post('customer_id');
		$vendor_id = $this->input->post('vendor_id');
		
		$path = "http://159.203.182.165/salon/assets/img/product/thumb";
		
		if($appointment_id!='0'){
			
			$qry = $this->db->query("select customer_id from appointment where appointment_id='".$appointment_id."' ");
			$res = $qry->row();
			$customer_id = $res->customer_id;
			$AND = " AND c.appointment_id='".$appointment_id."' AND c.customer_id='".$customer_id."' ";
		}else{
			$AND = "  AND c.customer_id='".$customer_id."' ";
		}
		
		if(!empty($appointment_id) || !empty($customer_id)){
			
			
			
			if($appointment_id!='0'){
				
				$appointment_data = $this->getAppointmentById($appointment_id);
				$customer_id = $appointment_data->customer_id;
			
				//"select aps.service_id, s.service_name,s.price as service_price, s.duration as service_duration, ss.stylist_id,ss.price as stylist_price,ss.duration as stylist_duration from appointment_service aps INNER JOIN service s ON s.service_id=aps.service_id INNER JOIN stylist_service ss ON ss.service_id=aps.service_id where aps.appointment_id='".$appointment_id."' "
				
			$query = $this->db->query("select
										a.appointment_id,
										aps.service_id,
										aps.stylist_id,
										s.service_name,
										CONCAT(st.firstname,' ',st.lastname) as stylist_name,
										s.price as service_price,
										s.duration as service_duration,
										ss.price as stylist_price,
										ss.duration as stylist_duration
										from appointment a
										INNER JOIN  appointment_service aps
										ON aps.appointment_id=a.appointment_id
										INNER JOIN service s
										ON s.service_id=aps.service_id
										INNER JOIN stylist st
										ON st.stylist_id=aps.stylist_id
										INNER JOIN stylist_service ss
										ON ss.stylist_id=aps.stylist_id
										WHERE a.appointment_id='".$appointment_id."'
										AND a.vendor_id='".$vendor_id."'
										AND aps.service_id=ss.service_id
										AND aps.stylist_id=ss.stylist_id
										AND a.is_active='1'
										AND a.is_delete='0'
										
									 ");
			
			$services = $query->result();
			$response['service'] = $services;
			foreach($services as $s){
				if($s->service_price!=0 || $s->service_price!='' || $s->service_price!=NULL){
					$price += $s->service_price;
				}else{
					$price = $s->service_price;
				}
			}
			$service_total = $price;
			
			$response['service_total'] = $service_total;
			
			}else{
				
				$service_total = 0;
				$response['service_total'] = $service_total;
				$response['service'] = [];
				$appointment_id = 0;
			}
			
			$response['status'] = 1;
			
		
			$query2 = $this->db->query("select
										c.cart_id,
										c.quantity, p.product_name,
										CONCAT('$path','/',p.main_image) as product_image,
										p.barcode_id,
										p.price_retail
										from cart c
										INNER JOIN product p
										ON p.product_id=c.product_id
										where p.is_active='1'
										and p.is_delete='0'
										$AND
										order by c.cart_id desc  ");
			
			$cart = $query2->result();
			$response['cart'] = $cart;
			
			foreach($cart as $c){
				$cart_price += $c->price_retail;
			}
			$cart_total = $cart_price;
			$final_total = $service_total+$cart_total;
			
			
			$response['cart_total'] = $cart_total;
			$response['final_total'] = $final_total;
			
			
			
			
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		 
        echo json_encode($response);
	}
	
	
	public function deleteCart(){
		
		$cart_id = $this->input->post('cart_id');
		
		$response['status'] = 0;
		$response['message'] = '';
			
		if(!empty($cart_id)){
			
			$del = $this->db->query("delete from cart where cart_id='".$cart_id."' ");
			if($del){
				
				$response['status'] = 1;
				$response['message'] = 'Item removed successfully';
			}
			
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		 
        echo json_encode($response);
		
	}
	
	public function delete(){
		
		$vendor_id = $this->input->post('vendor_id');
		$appointment_id = $this->input->post('appointment_id');
		
		$response['status'] = 0;
		$response['message'] = '';
		
		if(!empty($vendor_id) && !empty($appointment_id)){
			
			$delete = $this->db->query("update appointment set is_delete='1' where appointment_id='".$appointment_id."' and vendor_id='".$vendor_id."' ");
			if($delete){
				$response['status'] = 1;
				$response['message'] = 'Appointment deleted successfully';
			}else{
				$response['status'] = 0;
				$response['message'] = 'Something went wrong';
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
	}
	
	
	function discount(){
	
		$vendor_id = $this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$query = $this->db->query("select discount, coupon_type as discount_type, start_date, end_date, min_amount, main_image as discount_image from coupon where is_active='1' and is_delete='0' and vendor_id='".$vendor_id."' and discount_for='2' ");
		$result = $query->result();
		
		$query2 = $this->db->query("select discount, coupon_type as discount_type, start_date, end_date, min_amount, main_image as discount_image from coupon where is_active='1' and is_delete='0' and vendor_id='".$vendor_id."' and discount_for='1' ");
		$result2 = $query2->result();
		
			$response['status'] = 1;
			$response['serviceDiscount'] = $result;
			$response['productDiscount'] = $result2;
		}
		else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';			
		}
		echo json_encode($response);
			
		
	}
	public function updateNotes(){
	
		$vendor_id = $this->input->post('vendor_id');
		$appointment_id = $this->input->post('appointment_id');
		$note = $this->input->post('note');
		
		$response['status'] = 0;
		$response['message'] = '';
			
		if(!empty($vendor_id) && !empty($appointment_id)){
		
			$query = $this->db->query("update appointment set note='".$note."' where appointment_id='".$appointment_id."' and vendor_id='".$vendor_id."' ");
			
			if($query){
				$response['status'] = 1;
				$response['message'] = 'Note updated successfully';
			}else{
				$response['status'] = 0;
				$response['message'] = 'Something went wrong';
			}
		}else{
		
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
			
		}
		echo json_encode($response);
	}
	
	function getNotesByAppointmentId(){
	
		$vendor_id = $this->input->post('vendor_id');
		$appointment_id = $this->input->post('appointment_id');
		
		$response['status'] = 0;
		$response['message'] = '';
		
		if(!empty($vendor_id) && !empty($appointment_id)){
		
			$query = $this->db->query("select note from appointment where appointment_id='".$appointment_id."' and vendor_id='".$vendor_id."' ");
			$result = $query->row();
			
			$response['status'] = 1;
			$response['result'] = $result;
			$response['message'] = '';
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
		
	}
	
	public function personal_task()
	{
		$stylist_id = $this->input->post("stylist_id");
		$type_id = $this->input->post("type_id");
		$task_date = $this->input->post("task_date");
		$task_time_from = $this->input->post("task_time_from");
		$task_time_to = $this->input->post("task_time_to");
		$block_booking  = $this->input->post("block_booking");
		$comment  = $this->input->post("comment");
		$dayoff  = $this->input->post("dayoff");
		$created_date  = date('Y-m-d');
		
		//$service_id = explode(',',$service_id);
		$response['status'] = 0;
		$response['message'] = "";
		$response['data'] = "";
		//$url = $this->url();
		//$path = $url."/salonpos/assets/img/stylist/thumb/";
		
		if(!empty($stylist_id) && !empty($type_id) && !empty($task_date) && !empty($block_booking) && !empty($comment) && !empty($created_date))
		{
			
			if($sql = $this->db->query("INSERT INTO personal_task(stylist_id,type_id,task_date,task_time_from,task_time_to,block_booking,comment,created_date) values('$stylist_id','$type_id','$task_date','$task_time_from','$task_time_to','$block_booking','$comment','$created_date')"))
			{
				$response['status'] = 1;
				$response['message'] = "Your personal task has added successfully .";
			}
			else{
			$response['status'] = 0;
			$response['message'] = "Something Went Wrong!";
			}
		}
		else{
				$response['status'] = 0;
				$response['message'] = "Required parameter missing!";
			}
	 

		echo json_encode($response);	
	}
	
	public function list_personal_task_type()
	{
		$response['status'] = 0;
		$response['message'] = "";
		$response['data'] = "";
		//$url = $this->url();
		//$url = $url."/salonpos/assets/img/client/thumb/";
		
		if($sql = $this->db->query("SELECT * FROM personal_task_type"))
		{
			$response['status'] = 1;
			$response['message'] = "Data fetched successfully.";
			$response['data'] = $sql->result();
			//$response['url'] = $url;
		}
		else
		{
			$response['status'] = 0;
			$response['message'] = "Unable to fetch data.";
			$response['data'] = "No data.";
		}
		echo json_encode($response);
		
	}
	
	function getServiceById($service_id){
	
		$query = $this->db->query("select s.service_id,s.service_name,s.price,s.duration from service s where s.service_id='".$service_id."'");
		$result = $query->row();
		return $result;
	}
	
	
	function getBusinessHour(){
		
		$vendor_id = $this->input->post('vendor_id');
		
		$response['status'] = 0;
		$response['result'] = '';
		$response['message'] = '';
			
		if(!empty($vendor_id)){
		$selQuery = "SELECT
                     bh.*
                    FROM business_hour AS bh
					where bh.vendor_id='".$vendor_id."' AND bh.switch='0'
					ORDER BY bh.business_hour_id ASC
					";
        $result = $this->db->query($selQuery)->result();
			
			$response['status'] = 1;
			$response['result'] = $result;
			$response['message'] = '';
		}else{
			$response['status'] = 0;
			$response['message'] = 'Require parameter missing';
		}
		
		echo json_encode($response);
        
		
	}
	
	
	public function add_group(){
		
		  $country = $this->ip_info("Visitor", "Country");
		if($country=='India'){
			date_default_timezone_set("Asia/Kolkata");
		}else{
			
			date_default_timezone_set("America/Los_Angeles");
		}
		
		
		$vendor_id = $this->input->post('vendor_id');
		
		$appointment_date = $this->input->post('appointment_date'); 
		$appointment_time = $this->input->post('appointment_time'); 
		$service_id = $this->input->post('service_id'); 
		$stylist_id = $this->input->post('stylist_id'); 
		$duration = $this->input->post('duration'); 
		$note = $this->input->post('note'); 
		$customer_id = $this->input->post('customer_id');
		$group_leader_id = $this->input->post('group_leader_id'); 
		$deposit_id = $this->input->post('deposit_id'); 
   		$deposit_schedule_id = $this->input->post('deposit_schedule_id');
		//echo '<pre>';print_r($appointment_date);die;
		
		//$appointment_date =  date('Y-m-d',strtotime($appointment_date));
		$appointment_date = explode(',',$appointment_date);
		$appointment_time = explode(',',$appointment_time);
		$service_id = explode(',',$service_id);
		$stylist_id = explode(',',$stylist_id);
		
		$customer_id = explode(',',$customer_id);
		
		//print_r(json_decode($service_stylist));die;
		if(!empty($vendor_id) && !empty($appointment_date) && !empty($customer_id)){
			//echo '<pre>';print_r($service_id);die;
			//$customer_id = json_decode($customer_id);
			
			$qr = $this->db->query("select max(token_no) as token from appointment");
			$re = $qr->row();
			$token = ($re->token)+1;
			
			$color_id = $this->getColorIdByColorType('confirm',$vendor_id);
			
			for($j=0;$j<count($service_id);$j++){
			$qry = $this->db->query("insert into appointment set vendor_id='".$vendor_id."', date='".$appointment_date[$j]."', appointment_duration='".$duration[$j]."', note='".$note."', customer_id='".$customer_id[$j]."',group_leader_id='".$group_leader_id."',color_code='".$color_id."', rendering='back', created_date='".date('Y-m-d')."', appointment_type='3', token_no='".$token."', deposit_id='".$deposit_id."' ");
			$insert_id = $this->db->insert_id();
			
				$serviceInfo = $this->getServiceById($service_id[$j]);
				$price = $serviceInfo->price;
				$duration = $serviceInfo->duration;
				$endTime = strtotime("+".$serviceInfo->duration."minutes", strtotime($appointment_time[$j]));
					$new_end_time=date('H:i', $endTime);
					//echo $new_end_time
				$qry2 = $this->db->query("insert into appointment_service set appointment_id='".$insert_id."', service_id='".$service_id[$j]."', stylist_id='".$stylist_id[$j]."', appointment_time='".$appointment_time[$j]."',appointment_end_time='".$new_end_time."', customer_id='".$customer_id[$j]."', price='".$price."', duration='".$duration."' ");
			}
			
			if($insert_id){
				//$this->db->query('update deposit_customer_schedule set is_appointment=1 where id="'.$deposit_schedule_id.'"');
				$this->db->query('update deposit_customer set status=0 where id="'.$deposit_id.'"');
				$response['success'] = 1;
				$response['message'] = 'Appointment added successfully';
			}else{
				
				$response['success'] = 0;
				$response['message'] = 'Something wrong.';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
			
		}
		
		echo json_encode($response);
		
	}
	
	
	public function edit_group(){
		
		$vendor_id = $this->input->post('vendor_id');
		$appointment_id = $this->input->post('appointment_id');
		
		$appointment_date = $this->input->post('appointment_date'); 
		$appointment_time = $this->input->post('appointment_time');
		$service_id = $this->input->post('service_id'); 
		$stylist_id = $this->input->post('stylist_id'); 
		$duration = $this->input->post('duration'); 
		$note = $this->input->post('note'); 
		$customer_id = $this->input->post('customer_id'); 
		$token = $this->input->post('token_no'); 
		$deposit_id = $this->input->post('deposit_id'); 
		//echo '<pre>';print_r($appointment_date);die;
		$group_leader_id=$this->input->post('group_leader_id');
		//$appointment_date =  date('Y-m-d',strtotime($appointment_date));
		$appointment_date = explode(',',$appointment_date);
		$appointment_time = explode(',',$appointment_time);
		$service_id = explode(',',$service_id);
		$stylist_id = explode(',',$stylist_id);
		$customer_id = explode(',',$customer_id);
		
		$appointment_id = explode(',',$appointment_id);
		//echo "<pre>";print_r($_POST);exit;
		//echo 'hello '.print_r(($stylist_id);die;
		if(!empty($vendor_id) && !empty($appointment_date) && !empty($customer_id)){
			//echo '<pre>';print_r($service_id);die;
			//$customer_id = json_decode($customer_id);
			
			
			for($j=0;$j<count($service_id);$j++){
				$this->db->query('delete from appointment where appointment_id="'.$appointment_id[$j].'"');
				$this->db->query('delete from appointment_service where appointment_id="'.$appointment_id[$j].'"');
				/*$qr = $this->db->query("select max(token_no) as token from appointment");
			$re = $qr->row();
			$token = ($re->token)+1;*/
			$qry = $this->db->query("insert into appointment set vendor_id='".$vendor_id."', date='".$appointment_date[$j]."', appointment_duration='".$duration[$j]."', note='".$note."', customer_id='".$customer_id[$j]."',group_leader_id='".$group_leader_id."',color_code='9', rendering='back', created_date='".date('Y-m-d')."', appointment_type='3', token_no='".$token."', deposit_id='".$deposit_id."' ");
			$insert_id = $this->db->insert_id();
			
				$serviceInfo = $this->getServiceById($service_id[$j]);
				$price = $serviceInfo->price;
				$duration = $serviceInfo->duration;
				$endTime = strtotime("+".$serviceInfo->duration."minutes", strtotime($appointment_time[$j]));
					$new_end_time=date('H:i', $endTime);
					//echo $new_end_time
				$qry2 = $this->db->query("insert into appointment_service set appointment_id='".$insert_id."', service_id='".$service_id[$j]."', stylist_id='".$stylist_id[$j]."', appointment_time='".$appointment_time[$j]."',appointment_end_time='".$new_end_time."', customer_id='".$customer_id[$j]."', price='".$price."', duration='".$duration."' ");
			
				/*$qry = $this->db->query("update appointment set date='".$appointment_date[$j]."', appointment_duration='".$duration[$j]."', note='".$note."', customer_id='".$customer_id[$j]."', color_code='9', rendering='back' where appointment_id='".$appointment_id[$j]."' ");
			
				$serviceInfo = $this->getServiceById($service_id[$j]);
				$price = $serviceInfo->price;
				$duration = $serviceInfo->duration;
				$qry2 = $this->db->query("update appointment_service set appointment_id='".$appointment_id[$j]."', service_id='".$service_id[$j]."', stylist_id='".$stylist_id[$j]."', appointment_time='".$appointment_time[$j]."', customer_id='".$customer_id[$j]."', price='".$price."', duration='".$duration."' where appointment_id='".$appointment_id[$j]."' ");*/
			
			}
			
			if($qry2){
				/*Mail Content*/
							$customer_detail = $this->getCustomerById($customer_id);
							
					          $customer_name = $customer_detail->customer_name;
					          $to = $customer_detail->email;
					         // echo $to;die;
							if(!empty($service_id1)){
							//echo "test";die;
					          $data['customer_name'] =  $customer_name; 
					          $data['appointment_date'] = $appointment_date; 
					          $data['appointment_time'] = $appointment_time; 
					          $data['phone'] = $customer_detail->mobile_phone;
					          //$data['service'] = $service; 
					          
					         // $appointment_time = $start_time.'  '.$endtime_new;
					         // $appointment_date = date('M d Y',strtotime($appointment_date));
					          $config = Array(    
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
					      $this->email->from('info@booknpay.com', 'BookNPay');
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
					          $getAppointmentData=$this->db->query("select group_concat(concat(DAYNAME(a.date),' - ',date_format(a.date,'%c-%d-%Y')) SEPARATOR  ' /n ') as date,group_concat(time_format(a.date,'%c-%d-%Y')) as newDate,group_concat(concat(sr.service_name,' with ', concat(st.firstname,' ',st.lastname)) SEPARATOR  '-') as service_name,group_concat(aps.duration SEPARATOR  ' /n ') as duration,group_concat(time_format(aps.appointment_time,'%l:%i %p') SEPARATOR  ' /n ') as ap_time,group_concat(time_format(aps.appointment_time,'%l:%i %p')) as new_time,group_concat(concat(st.firstname,'',st.lastname)) as stylist_name from appointment as a inner join appointment_service as aps on aps.appointment_id=a.appointment_id inner join service as sr on sr.service_id=aps.service_id inner join stylist as st on st.stylist_id=aps.stylist_id where a.token_no='".$token."'")->row();
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
					        
					   
					           );

					     $newAppointemnt=$this->db->query('select email_subject,email_content from email_settings where slug="appointment_update" and is_active=1 and vendor_id="'.$vendor_id.'"')->row();
					     //echo "<pre>";print_r($newAppointemnt);exit;
					      if(!empty($newAppointemnt)){
					      	$data['template_type']='appointment_update';
					      	$data['vendor_id']=$vendor_id;
					      	$this->load->library('email');
                  			 $this->email->initialize($config);
                  			 $this->email->from('info@booknpay.com', 'Hubwallet');
					        //$data['new_content']=$newAppointemnt->email_content;
					        $this->email->to($to); // replace it with receiver email id
					      $this->email->subject($newAppointemnt->email_subject); // replace it with email subject
					      $message = $this->load->view('email_template/new_appointment',$data,TRUE);
					     // echo $message;die;
					      $this->email->message($message); 
					      $this->email->send();
					      }
					  }
				$response['success'] = 1;
				$response['message'] = 'Appointment updated successfully';
			}else{
				
				$response['success'] = 0;
				$response['message'] = 'Something wrong.';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
			
		}
		
		echo json_encode($response);
		
	}
	
	/*public function get_appointment_date(){
		
		//$vendor_id = $this->input->post('vendor_id');
		$appointment_id = $this->input->post('appointment_id');
		
		
		if(!empty($vendor_id) && !empty($token_no)){
			
			$query = $this->db->query("select a.appointment_id as id,UPPER(s.service_name) as title, s.service_id, CONCAT(a.date,' ',aps.appointment_time) as date, aps.duration as duration, a.rendering, a.color_code as color, a.color_code as backgroundColor, st.stylist_id, CONCAT(st.firstname,' ',st.lastname) as stylist_name, a.customer_id, a.note from appointment a INNER JOIN appointment_service aps ON aps.appointment_id=a.appointment_id INNER JOIN service s on s.service_id=aps.service_id INNER JOIN stylist st ON st.stylist_id=aps.stylist_id where  a.appointment_id='".$appointment_id."' AND a.is_active='1' AND a.is_delete='0'  ");
			
			$result = $query->result();
			$response['result'] = $result;
			$response['message'] = '';
			$response['status'] = 1;
			
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
	}
	
*/	
	
	public function group_apt_detail(){
		
		$vendor_id = $this->input->post('vendor_id');
		$token_no = $this->input->post('token_no');
		
		
		if(!empty($vendor_id) && !empty($token_no)){
			
			$query = $this->db->query("select a.appointment_id as id,UPPER(s.service_name) as title, s.service_id, a.date as appointment_date, aps.appointment_time as start_time, aps.duration as duration, a.rendering, a.color_code as color, a.color_code as backgroundColor, st.stylist_id, CONCAT(st.firstname,' ',st.lastname) as stylist_name, CONCAT(cu.firstname,' ',cu.lastname) as customer_name,CONCAT(grpin.firstname,' ',grpin.lastname) as group_incharge_name,a.group_leader_id as group_incharge_id,a.customer_id, a.note,de.event_name,de.customer_total as total_customer,de.id as deposit_id from appointment a INNER JOIN appointment_service aps ON aps.appointment_id=a.appointment_id INNER JOIN service s on s.service_id=aps.service_id INNER JOIN stylist st ON st.stylist_id=aps.stylist_id INNER JOIN deposit_customer de ON de.id=a.deposit_id
			INNER JOIN customer cu ON cu.customer_id=a.customer_id left JOIN customer grpin ON grpin.customer_id=a.group_leader_id where a.vendor_id='".$vendor_id."' AND a.token_no='".$token_no."' AND a.is_active='1' AND a.is_delete='0' AND a.appointment_type='3' ");
			
			$result = $query->result();
			$response['result'] = $result;
			$response['message'] = 'data found';
			$response['status'] = 1;
			
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
	}
	public function get_apt_detail(){
		
		$vendor_id = $this->input->post('vendor_id');
		$token_no = $this->input->post('token_no');
		
		
		if(!empty($vendor_id) && !empty($token_no)){
			
			$query = $this->db->query("select a.appointment_id as id,UPPER(s.service_name) as title, s.service_id,a.date as appointment_date, aps.appointment_time as start_time, aps.duration as duration, a.rendering, a.color_code as color, a.color_code as backgroundColor, st.stylist_id, CONCAT(st.firstname,' ',st.lastname) as stylist_name, a.customer_id, a.note from appointment a INNER JOIN appointment_service aps ON aps.appointment_id=a.appointment_id INNER JOIN service s on s.service_id=aps.service_id INNER JOIN stylist st ON st.stylist_id=aps.stylist_id where a.vendor_id='".$vendor_id."' AND a.token_no='".$token_no."' AND a.is_active='1' AND a.is_delete='0' ");
			
			$result = $query->result();
			$response['result'] = $result;
			$response['message'] = 'data found';
			$response['status'] = 1;
			
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
	}
	function getCustomerById($id){
		
		$query = $this->db->query("select c.login_id,l.email,c.firstname as customer_name,CONCAT(c.firstname,' ',c.lastname) as customer_name1,v.receipt_address_line1,st.name,v.zipcode,v.city,v.vendor_name,v.phone,l.fcm_token,c.mobile_phone from login l INNER JOIN customer c ON c.login_id=l.login_id inner join vendor as v on l.vendor_id=v.vendor_id left join states as st on st.id=v.state_id where c.customer_id='".$id."'");
		$result = $query->row();
		return $result;
		
	}
	
	
	function getStylistById($id){
		
		
		$query = $this->db->query("select l.email,CONCAT(s.firstname,' ',s.lastname) as stylist_name from login l INNER JOIN stylist s ON s.login_id=l.login_id where s.stylist_id='".$id."'");
		$result = $query->row();
		return $result;
		
	
	}

	function getDepositByid (){
		$deposit_id=$this->input->post('deposit_id');
		if(!empty($deposit_id)){
			$result2=$this->db->query("select A.event_name as title,B.deposit_id as token1,B.id as token2,A.customer_id,CONCAT(date_format(B.start_date,'%Y-%m-%d'),' ',TIME_FORMAT(B.start_time,'%H:%i')) as start,CONCAT(date_format(B.start_date,'%Y-%m-%d'),' ',TIME_FORMAT(B.end_time,'%H:%i')) as end,CONCAT(c.firstname,' ',c.lastname) as customer_name,cs.color_code as color,'D' as rendering,A.deposit_amount,A.checkbox_1,A.checkbox_2,A.checkbox_3,A.deposit_date,A.event_note,A.distribute_in,A.distribute_amount,dt.name as deposit_type,A.customer_total as total_customer,A.require_deposit from deposit_customer as A inner join deposit_customer_schedule as B on A.id=B.deposit_id inner join customer as c on A.customer_id=c.customer_id inner join color_settings as cs on B.color_id=cs.color_id left join distribution_type as dt on dt.dt_id=A.deposit_type where B.is_appointment=0 and A.status=1 AND A.id='".$deposit_id."'")->result();
			$response['status'] = 1;
			$response['data']=$result2;
		   $response['message'] = 'Data found';

		}else{
			$response['status'] = 0;
		$response['message'] = 'Required parameter missing';
		}	
			echo json_encode($response);
	}

	function checkdeposit_event(){
	
		$event_name=$this->input->post('event_name');
		if(!empty($event_name)){
		$total_row=$this->db->query('select id from deposit_customer where event_name="'.$event_name.'"')->num_rows();
		if($total_row >0){
			$response['status'] = 0;
			$response['message'] = 'Event name already exists';
		}else{

	    $response['status'] = 1;
		$response['message'] = 'Not exsist';
		}
	}else{
		$response['status'] = 0;
		$response['message'] = 'Required parameter missing';	
	}
		echo json_encode($response);
	
	}
	function update_deposit_old_jitu(){
		$random=$this->incrementalHash();
		$query=$this->db->query('update deposit_customer set status=1,deposit_token="'.$random.'" where id="'.$_POST['deposit_last_id'].'"');
		$data['recipte']=$this->db->query('select A.id,A.customer_total,A.deposit_amount,A.event_name,concat(B.firstname,"",B.lastname) as cust_name,C.email,B.mobile_phone,A.event_name,A.event_note from deposit_customer as A inner join customer as B on A.customer_id=B.customer_id INNER JOIN login as C ON C.login_id=B.login_id where A.id="'.$_POST['deposit_last_id'].'" ')->row();
			if($query){
				$htmlData1="You have deposited an amount of "."$".number_format($data['recipte']->deposit_amount,2);
				$htmlData1.=" for a Event ";
				$htmlData1.=" <br/> Event Name: ".$data['recipte']->event_name;
				$htmlData1.=" <br/> Event Date: ".$data['recipte']->st_date;
				$htmlData1.=" <br/> Event Note: ".$data['recipte']->event_note;
					$headers = "From: info@booknpay.com\r\n";
					$headers .= "Reply-To:  info@booknpay.com\r\n";
					$headers .= "MIME-Version: 1.0\r\n";
					$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

							$message_email = $htmlData1;
					
					$mail=mail($data['recipte']->email, $subject, $message_email, $headers);
					//$response['desposit_id'] = $last_id;
					$response['message'] = 'update succesfully';
					$response['recipte']=$data['recipte'];
					$response['status'] = 1;

			}else {
				//$response['desposit_id'] = $last_id;
					$response['message'] = 'Fail';
					$response['status'] = 0;
			}
			
			echo json_encode($response);
		
	}
	
	 public function incrementalHash($len = 5){
	  $charset = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	  $base = strlen($charset);
	  $result = '';

	  $now = explode(' ', microtime())[1];
	  while ($now >= $base){
		$i = $now % $base;
		$result = $charset[$i] . $result;
		$now /= $base;
	  }
	  return substr($result, -5);
	}
	
	 public function getColorIdByColorType($color_type,$vendor_id){
	  
	  $query = $this->db->query("select color_id from color_settings where vendor_id='".$vendor_id."' and color_type='".$color_type."' ");
	  
	  $res = $query->row()->color_id;
	  return $res;
  }
	
	//addMultiple
	public function add(){
		
		  $country = $this->ip_info("Visitor", "Country");
		if($country=='India'){
			date_default_timezone_set("Asia/Kolkata");
		}else{
			
			date_default_timezone_set("America/Los_Angeles");
		}
		
		
		
		//echo "<pre>";print_r($_POST);exit;
		$vendor_id = $this->input->post('vendor_id');
		
		$appointment_date = $this->input->post('appointment_date'); 
		$appointment_time = $this->input->post('appointment_time'); 
		$service_id = $this->input->post('service_id'); 
		$stylist_id = $this->input->post('stylist_id'); 
		$duration = $this->input->post('duration'); 
		$note = $this->input->post('note'); 
		$customer_id = $this->input->post('customer_id'); 
		$appointment_type = $this->input->post('appointment_type'); 
		//echo '<pre>';print_r($appointment_date);die;
		$myVar=array(
		'vendor_id' => $this->input->post('vendor_id'),
		
		'appointment_date' => $this->input->post('appointment_date'),
		'appointment_time' => $this->input->post('appointment_time'), 
		'service_id' => $this->input->post('service_id'),
		'stylist_id' => $this->input->post('stylist_id'), 
		'duration' => $this->input->post('duration'), 
		'note' => $this->input->post('appointment_note'), 
		'customer_id' =>$this->input->post('customer_id') 

		);
		//$appointment_date =  date('Y-m-d',strtotime($appointment_date));
		///$appointment_date = explode(',',$appointment_date);
		$appointment_time1 = explode(',',$appointment_time);
		$service_id1 = explode(',',$service_id);
		$stylist_id1 = explode(',',$stylist_id);
		
	///	$customer_id = explode(',',$customer_id);
		$duration1 = explode(',',$duration);

		/*echo "Service-<pre>";print_r($service_id1);
		echo "stylist_id-<pre>";print_r($stylist_id1);
		echo "app-<pre>";print_r($appointment_time1);
		echo "duration-<pre>";print_r($duration1);
		exit;*/

		//print_r(json_decode($service_stylist));die;
		if(!empty($vendor_id) && !empty($appointment_date) && !empty($customer_id)){

			//echo '<pre>';print_r($service_id);die;
			//$customer_id = json_decode($customer_id);
			
			$qr = $this->db->query("select max(token_no) as token from appointment");
			$re = $qr->row();
			$token = ($re->token)+1;
			
			for($j=0;$j<count($service_id1);$j++){
				
				$color_id = $this->getColorIdByColorType('confirm',$vendor_id);
			
			 $qry = $this->db->query("insert into appointment set vendor_id='".$vendor_id."', date='".$appointment_date."', appointment_duration='".$duration1[$j]."', note='".$note."', customer_id='".$customer_id."', color_code='".$color_id."', rendering='back', created_date='".date('Y-m-d')."', appointment_type='".$appointment_type."', token_no='".$token."',status=3 ");
			// $response['appointment_query']=$this->db->last_query();
				$insert_id = $this->db->insert_id();
				$serviceInfo = $this->getServiceById($service_id1[$j]);
				$price = $serviceInfo->price;
				$duration = $serviceInfo->duration;
				$endTime = strtotime("+".$serviceInfo->duration."minutes", strtotime($appointment_time1[$j]));
					$new_end_time=date('H:i', $endTime);
					//echo $
				$qry2 = $this->db->query("insert into appointment_service set appointment_id='".$insert_id."', service_id='".$service_id1[$j]."', stylist_id='".$stylist_id1[$j]."', appointment_time='".$appointment_time1[$j]."',appointment_end_time='".$new_end_time."', customer_id='".$customer_id."', price='".$price."', duration='".$duration."' ");
				//$response['appointment_service_query']=$this->db->last_query();
			}
			
			if($insert_id){
							/*Mail Content*/
							$customer_detail = $this->getCustomerById($customer_id);
							
					          $customer_name = $customer_detail->customer_name;
					          $to = $customer_detail->email;
					         // echo $to;die;
							if(!empty($service_id1)){
							//echo "test";die;
					          $data['customer_name'] =  $customer_name; 
					          $data['appointment_date'] = $appointment_date; 
					          $data['appointment_time'] = $appointment_time; 
					           $data['phone'] = $customer_detail->mobile_phone;
					          //$data['service'] = $service; 
					        
					          $getAppointmentData=$this->db->query("select group_concat(concat(DAYNAME(a.date),' - ',date_format(a.date,'%c-%d-%Y')) SEPARATOR  ' /n ') as date,group_concat(time_format(a.date,'%c-%d-%Y')) as newDate,group_concat(concat(sr.service_name,' with ', concat(st.firstname,' ',st.lastname)) SEPARATOR  '-') as service_name,group_concat(aps.duration SEPARATOR  ' /n ') as duration,group_concat(time_format(aps.appointment_time,'%l:%i %p') SEPARATOR  ' /n ') as ap_time,group_concat(time_format(aps.appointment_time,'%l:%i %p')) as new_time,group_concat(concat(st.firstname,'',st.lastname)) as stylist_name from appointment as a inner join appointment_service as aps on aps.appointment_id=a.appointment_id inner join service as sr on sr.service_id=aps.service_id inner join stylist as st on st.stylist_id=aps.stylist_id where a.token_no='".$token."'")->row();
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

					     $newAppointemnt=$this->db->query('select email_subject,email_content from email_settings where slug="new_appointment" and is_active=1 and vendor_id="'.$vendor_id.'"')->row();
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
					      	$businessName=$this->db->query('select vendor_name from vendor where vendor_id="'.$vendor_id.'"')->row();
					      	$subject=str_replace('{Business Name}',$businessName->vendor_name,$newAppointemnt->email_subject);
					      $message = $this->load->view('email_template/new_appointment',$data,TRUE);
					      $this->load->library('Send_mail');
						  $this->send_mail->sendMail($to, $subject, $message, $fileName=false, $filePath=false, $cc=false);
						  /*push Notification*/
						  //$search1=
						  $search1  = array('{Customer First Name}', '{Date}','{Time}','{Business Name}');
						  $replace1 = array($customer_name, $customer_detail->vendor_name);
						 	 $getDataNew=getImageTemplate($vendor_id,'new_appointment');
							$getNotification=str_replace($search1, $replace1, $getDataNew->sms_content);
						  $this->sendNotificationNew($customer_detail->login_id,"New Appointment",$getNotification,$token);
						  /*Push notification*/
					      }
					  }

					  else{
					     $service_detail = $this->getServiceById($service_id);
   					$service_data .= $service_detail->service_name.' - '.$service_detail->price.' - '.$service_detail->duration; 
   					
   				
   			
					  }
					  /*Mail Content*/
				$response['success'] = 1;
				$response['myVar']=$myVar;
				$response['message'] = 'Appointment added successfully';
			}else{
				
				$response['success'] = 0;
				$response['message'] = 'Something wrong.';
			}
			
		}else{
			
			$response['success'] = 0;
			$response['message'] = 'Required parameter missing';
			
		}
		
		echo json_encode($response);
		
	}
	
	public function editMultiple(){
		
		$vendor_id = $this->input->post('vendor_id');
		$appointment_id = $this->input->post('appointment_id');
		
		$appointment_date = $this->input->post('appointment_date'); 
		$appointment_time = $this->input->post('appointment_time');
		$service_id = $this->input->post('service_id'); 
		$stylist_id = $this->input->post('stylist_id'); 
		$duration = $this->input->post('duration'); 
		$note = $this->input->post('note'); 
		$customer_id = $this->input->post('customer_id'); 
		$token = $this->input->post('token_no'); 
		//echo '<pre>';print_r($appointment_date);die;
		
		//$appointment_date =  date('Y-m-d',strtotime($appointment_date));
	//	$appointment_date = explode(',',$appointment_date);
		$appointment_time = explode(',',$appointment_time);
		$service_id = explode(',',$service_id);
		$stylist_id = explode(',',$stylist_id);
		//$customer_id = explode(',',$customer_id);
		$duration=explode(',', $duration);
		$appointment_id = explode(',',$appointment_id);
		//echo "<pre>";print_r($_POST);exit;
		//echo 'hello '.print_r(($stylist_id);die;
		if(!empty($vendor_id) && !empty($appointment_date) && !empty($customer_id)){
			//echo '<pre>';print_r($service_id);die;
			//$customer_id = json_decode($customer_id);
			
			
			/*$qr = $this->db->query("select max(token_no) as token from appointment");
			$re = $qr->row();
			$token = ($re->token)+1;*/
			$getAppointmentId=$this->db->query('select group_concat(appointment_id) as appointment_id from appointment where token_no="'.$token_no.'"')->row();
			$color_id = $this->getColorIdByColorType('confirm',$vendor_id);
			
			$this->db->query('delete from appointment_service where appointment_id IN ('.$getAppointmentId->appointment_id.')');
			$this->db->query('delete from appointment where token_no="'.$token.'"');
			for($j=0;$j<count($service_id);$j++){
			
			 $qry = $this->db->query("insert into appointment set vendor_id='".$vendor_id."', date='".$appointment_date."', appointment_duration='".$duration[$j]."', note='".$note."', customer_id='".$customer_id."', color_code='".$color_id."', rendering='back', created_date='".date('Y-m-d')."', appointment_type=2, token_no='".$token."' ");
			// $response['appointment_query']=$this->db->last_query();
				$insert_id = $this->db->insert_id();
				$serviceInfo = $this->getServiceById($service_id[$j]);
				$price = $serviceInfo->price;
				$duration = $serviceInfo->duration;
				$endTime = strtotime("+".$serviceInfo->duration."minutes", strtotime($appointment_time[$j]));
					$new_end_time=date('H:i', $endTime);
					//echo $
				$qry2 = $this->db->query("insert into appointment_service set appointment_id='".$insert_id."', service_id='".$service_id[$j]."', stylist_id='".$stylist_id[$j]."', appointment_time='".$appointment_time[$j]."',appointment_end_time='".$new_end_time."', customer_id='".$customer_id."', price='".$price."', duration='".$duration."' ");
				//$response['appointment_service_query']=$this->db->last_query();
			}
			
			if($qry2){
				/*Mail Content*/
							$customer_detail = $this->getCustomerById($customer_id);
							
					          $customer_name = $customer_detail->customer_name;
					          $to = $customer_detail->email;
					         // echo $to;die;
							if(!empty($service_id)){
							//echo "test";die;
					          $data['customer_name'] =  $customer_name; 
					          $data['appointment_date'] = $appointment_date; 
					          $data['appointment_time'] = $appointment_time; 
					          $data['phone'] = $customer_detail->mobile_phone;
					          //$data['service'] = $service; 
					          
					         // $appointment_time = $start_time.'  '.$endtime_new;
					         // $appointment_date = date('M d Y',strtotime($appointment_date));
					         /* $config = Array(    
					        'protocol' => 'sendmail',
					        'smtp_host' => 'smtp.gmail.com',
					        'smtp_port' => 25,
					        'smtp_user' => 'booknpaysalon@gmail.com',
					        'smtp_pass' => 'bnp@2019$$',
					        'smtp_timeout' => '4',
					        'mailtype' => 'html',
					        'charset' => 'iso-8859-1'
					      );*/
					   
/*					      $this->load->library('email', $config); // Load email template
					      $this->email->set_newline("\r\n");
					      $this->email->from('info@booknpay.com', 'BookNPay');*/
					     
					          $getAppointmentData=$this->db->query("select group_concat(concat(DAYNAME(a.date),' - ',date_format(a.date,'%c-%d-%Y')) SEPARATOR  ' /n ') as date,group_concat(time_format(a.date,'%c-%d-%Y')) as newDate,group_concat(concat(sr.service_name,' with ', concat(st.firstname,' ',st.lastname)) SEPARATOR  '-') as service_name,group_concat(aps.duration SEPARATOR  ' /n ') as duration,group_concat(time_format(aps.appointment_time,'%l:%i %p') SEPARATOR  ' /n ') as ap_time,group_concat(time_format(aps.appointment_time,'%l:%i %p')) as new_time,group_concat(concat(st.firstname,'',st.lastname)) as stylist_name from appointment as a inner join appointment_service as aps on aps.appointment_id=a.appointment_id inner join service as sr on sr.service_id=aps.service_id inner join stylist as st on st.stylist_id=aps.stylist_id where a.token_no='".$token."'")->row();
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
					        	'newTime'=>$getAppointmentData->duration,
					        
					   
					           );

					     $newAppointemnt=$this->db->query('select email_subject,email_content from email_settings where slug="appointment_update" and is_active=1 and vendor_id="'.$vendor_id.'"')->row();
					     //echo "<pre>";print_r($newAppointemnt);exit;
					      if(!empty($newAppointemnt)){
					      	$data['template_type']='appointment_update';
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
						  $this->send_mail->sendMail($to, $subject, $message, $fileName=false, $filePath=false, $cc=false);
						  /*push Notification*/
						  $search1  = array('{Customer First Name}', '{Date}','{Time}','{Business Name}');
						  $replace1 = array($customer_name, $customer_detail->vendor_name);
						 	 $getDataNew=getImageTemplate($vendor_id,'appointment_update');
							$getNotification=str_replace($search1, $replace1, $getDataNew->sms_content);
						  	$this->sendNotificationNew($customer_detail->login_id,"Edit Appointment",$getNotification,$token_no);
						  /*Push notification*/
					      }
					  }
				$response['success'] = 1;
				$response['message'] = 'Appointment updated successfully';
			}else{
				
				$response['success'] = 0;
				$response['message'] = 'Something wrong.';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
			
		}
		
		echo json_encode($response);
		
	}
	
	public function searchAppointment(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$search_val = $this->input->post('search_val');
		if(!empty($vendor_id) && !empty($search_val)){
		$current_date = date('Y-m-d');
		
		$query = $this->db->query("select a.event_name, a.date as appointment_date, CONCAT(c.firstname,' ',c.lastname) as customer_name,c.mobile_phone from appointment a INNER JOIN customer c ON c.customer_id=a.customer_id where a.event_name like '%$search_val%' OR c.firstname LIKE '%$search_val%' OR c.lastname LIKE '%$search_val%' OR c.mobile_phone LIKE '%$search_val%' group by a.date ");
		
		//$query = $this->db->query("select a.event_name, a.date as appointment_date from appointment a where a.event_name like '%$search_val%' group by a.date ");
		
		
		$num = $query->num_rows();
		
		if($num>0){
			$response['status'] = 1;
			$response['result'] = $query->result();
			$response['message'] = '';
			
			
		}else{
			
			$response['status'] = 0;
			$response['result'] = '';
			$response['message'] = 'No result found';
			
			
		}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		

	}
	  function getOptions(){
    $vendor_id=$this->input->post('vendor_id');
    if(!empty($vendor_id)){
         //$getData=$this->db->query('select color_id,name,color_code from color_settings where is_active=1 and vendor_id="'.$vendor_id.'" order by sort asc')->result();
         $getData=$this->db->query('select color_id,name,color_code from color_settings where is_active=1 AND vendor_id="'.$vendor_id.'"  order by sort asc')->result();
          $response['getData'] = $getData;
          $response['message'] = 'succ';
    }else{
      $response['message'] = 'Required parameter missing'; 
      $response['status']=0;
    }
   echo json_encode($response);
  }
  function getStatus(){
  	$vendor_id=$this->input->post('vendor_id');
  	if(!empty($vendor_id)){
  		$result=$this->db->query('select color_id,name,color_type,color_code,text_color from color_settings where is_active=1 and vendor_id="'.$vendor_id.'" AND (color_type="checked_in" OR color_type="service_in_process" OR color_type="hold" OR color_type="cancel" OR color_type="no_show") order by sort ')->result();
  		if(!empty($result)){
  		$response['status'] = 1;
  		$response['data']=$result;
		$response['message'] = 'Data found';
  		}else{
  			$response['status'] = 0;
  		$response['data']=array();
		$response['message'] = 'No data found';
  		}
  		
  	}else{
  	$response['status'] = 0;
    $response['message'] = 'Required parameter missing';	
  	}
  	 echo json_encode($response);
  }
  public function getStatusName($color_id){
  	$getData=$this->db->query('select color_type from color_settings where color_id="'.$color_id.'"')->row();
  	return $getData->color_type;
  }
  function changeStatus(){
    $type=$this->input->post('type');
    $appointment_id=$this->input->post('appointment_id');
    $color_id=$this->input->post('color_id');
	 $customer=$this->db->query('select A.customer_id,l.email as customer_email,concat(C.firstname," ",C.lastname) as customer_name,C.mobile_phone from appointment as A inner join customer as C on A.customer_id=C.customer_id inner join login as l on l.login_id=C.login_id where A.appointment_id="'.$appointment_id.'"')->row();
	 $stylist=$this->db->query('select l.email as stylist_email,concat(S.firstname," ",S.lastname) as stylist_name from appointment as A inner join appointment_service as aps on A.appointment_id=aps.appointment_id inner join stylist as S on aps.stylist_id=S.stylist_id inner join login as l on l.login_id=S.login_id where A.appointment_id="'.$appointment_id.'"')->row();
	 if($type=='delete'){
	 	$this->db->query('delete from appointment where appointment_id="'.$appointment_id.'"');
	 }
    if(!empty($appointment_id)){
    	$query=$this->db->query('update appointment set color_code="'.$color_id.'" where appointment_id="'.$appointment_id.'" ');
    	$color_type=$this->getStatusName($color_id);
    	if($color_type=='confirm'){
    		$this->db->query('update appointment set status=3 where appointment_id="'.$appointment_id.'"');
    	}
    	if($color_type=='checked_in'){
    		$this->db->query('update appointment set status=6 where appointment_id="'.$appointment_id.'"');
    	}
    	if($color_type=='checkout'){
    		$this->db->query('update appointment set status=7 where appointment_id="'.$appointment_id.'"');
    	}
    	if($color_type=='hold'){
    		$this->db->query('update appointment set status=9 where appointment_id="'.$appointment_id.'"');
    	}
    	if($color_type=='cancel'){
    		$this->db->query('update appointment set status=8 where appointment_id="'.$appointment_id.'"');
    	}
    	if($color_type=='no_show'){
    		$this->db->query('update appointment set status=5 where appointment_id="'.$appointment_id.'"');
    	}
        if($query){
           $response['status'] =1;
          $response['message'] = 'Status updated successfully';
         /// $receiver_email = $customer->customer_email;

                $sender_email = 'info@booknpay.com';
                $initial_time = time();
                $customer_detail = $this->getCustomerById($customer->customer_id);
             //   echo "<pre>";print_r($customer_detail);die;
                $customer_name = $customer->customer_name;
			    $to = $customer->customer_email;
			    $getAppointmentData=$this->db->query("select group_concat(concat(DAYNAME(a.date),' - ',date_format(a.date,'%c-%d-%Y')) SEPARATOR  ' /n ') as date,group_concat(time_format(a.date,'%c-%d-%Y')) as newDate,group_concat(concat(sr.service_name,' with ', concat(st.firstname,' ',st.lastname)) SEPARATOR  '-') as service_name,group_concat(aps.duration SEPARATOR  ' /n ') as duration,group_concat(time_format(aps.appointment_time,'%l:%i %p') SEPARATOR  ' /n ') as ap_time,group_concat(time_format(aps.appointment_time,'%l:%i %p')) as new_time,group_concat(concat(st.firstname,'',st.lastname)) as stylist_name,a.vendor_id from appointment as a inner join appointment_service as aps on aps.appointment_id=a.appointment_id inner join service as sr on sr.service_id=aps.service_id inner join stylist as st on st.stylist_id=aps.stylist_id where a.appointment_id='".$appointment_id."'")->row();
  					$getCancellationPolicy=$this->db->query('select policy_text from cancellation_policy where apt_type=1 and vendor_id="'.$getAppointmentData->vendor_id.'"')->row();
  					 $data['getData'] = array(
					        'stylist_name'=> $getAppointmentData->stylist_name,
					        'customer_name'=> $customer_name,
					        'customer_email'=> $to,
					        'store_name'=> $customer_detail->vendor_name,
					        'city'=> $customer_detail->city,
					        'state'=>$customer_detail->name,
					        'zipcode'=> $customer_detail->pincode,
					        
					      //  'start_time'=> $appointment_time,
					      //  'end_time'=> $endtime_new,
					        'appointment_date'=> $getAppointmentData->date,
						        'service_name'=> $getAppointmentData->service_name,
						        'start_time'=> $getAppointmentData->ap_time,
						        'business_phone'=>$customer_detail->phone,
						       'policy'=>$getCancellationPolicy->policy_text,
					        	'newdate'=>$getAppointmentData->newDate,
					        	'newTime'=>$getAppointmentData->new_time,
					        	'duration'=>$getAppointmentData->duration
					        
					   
					           );
  					 if($type=='cancel'){
  					   		$color_type1='cancel_appointment';
  					   	}
  					   	if($type=='no_show'){
  					   		$color_type1='no_show';
  					   	}
  					  //echo 	'select email_subject,email_content from email_settings where slug="'.$color_type1.'" and is_active=1 and vendor_id="'.$getAppointmentData->vendor_id.'"';die;
  					   $newAppointemnt=$this->db->query('select email_subject,email_content from email_settings where slug="'.$color_type1.'" and is_active=1 and vendor_id="'.$getAppointmentData->vendor_id.'"')->row();
  					   if(!empty($newAppointemnt)){
  					   	

					      	$data['template_type']=$color_type1;

					      	$data['vendor_id']=$getAppointmentData->vendor_id;
					      	$data['phone'] = $customer->mobile_phone;
					         //	$this->load->library('email');
                  			// $this->email->initialize($config);
                  			// $this->email->from('info@booknpay.com', 'Hubwallet');
					        //$data['new_content']=$newAppointemnt->email_content;
					      //  $this->email->to($to); // replace it with receiver email id
					     // $this->email->subject($newAppointemnt->email_subject); // replace it with email subject
					      	$businessName=$this->db->query('select vendor_name from vendor where vendor_id="'.$getAppointmentData->vendor_id.'"')->row();
					      	$subject=str_replace('{Business Name}',$businessName->vendor_name,$newAppointemnt->email_subject);
					     //echo $subject;die;
					      if($color_type!='checked_in' || $color_type!='service_in_process'){
					      	 $message = $this->load->view('email_template/new_appointment',$data,TRUE);
					      	// echo $message;die;
					     	 $this->load->library('Send_mail');
						 	 $send=$this->send_mail->sendMail($to, $subject, $message, $fileName=false, $filePath=false, $cc=false);
						 	/* if($send){
						 	 	echo "ddd";die;
						 	 }else{
						 	 	echo "fail";die;
						 	 }*/
						 	 /*push Notification*/
						  $replace1 = array($customer_name, $customer_detail->vendor_name);
						 	 $getDataNew=getImageTemplate($getAppointmentData->vendor_id,'new_appointment');
							$getNotification=str_replace($search1, $replace1, $getDataNew->sms_content);
						  $this->sendNotificationNew($customer_detail->login_id,"Change appointment status",$getNotification);
						  /*Push notification*/
							}
					      }
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
                );
                $this->load->library('email');
                $this->email->initialize($config);
                $this->email->from('info@booknpay.com', 'Hubwallet');
                $this->email->to($receiver_email);
                $this->email->subject('Change Status');
                $this->email->message("Dear $customer->customer_name <br/>Your appointment status has been updated successfully.<br/>Thank You<br/>Team Hubwallet");
                $this->email->send();


                $this->email->to($stylist->stylist_email);
                $this->email->subject('Change Status');
                $this->email->message("Dear $stylist->stylist_name<br/> Appointment status has been updated successfully. <br/><br/>Thank You<br/>Team Hubwallet");
                $this->email->send();*/

        }else{
          $response['status'] =0;
          $response['message'] = 'Something wrong';
        }

      /*if($type=='accept'){
        $query=$this->db->query('update appointment set status=1,color_code="'.$color_id.'" where appointment_id="'.$appointment_id.'" ');
        if($query){
           $response['status'] =1;
          $response['message'] = 'Update successfully';
        }else{
          $response['status'] =0;
          $response['message'] = 'Something wrong';
        }
      }
       if($type=='deny'){
        $query=$this->db->query('update appointment set status=2,color_code="'.$color_id.'" where appointment_id="'.$appointment_id.'" ');
        if($query){
           $response['status'] =1;
          $response['message'] = 'Update successfully';
        }else{
          $response['status'] =0;
          $response['message'] = 'Something wrong';
        }
      }
      if($type=='confirm'){
        $query=$this->db->query('update appointment set status=3,color_code="'.$color_id.'" where appointment_id="'.$appointment_id.'" ');
        if($query){
           $response['status'] =1;
          $response['message'] = 'Update successfully';
        }else{
          $response['status'] =0;
          $response['message'] = 'Something wrong';
        }
      }
      if($type=='show'){
        $query=$this->db->query('update appointment set status=4,color_code="'.$color_id.'" where appointment_id="'.$appointment_id.'" ');
        if($query){
           $response['status'] =1;
          $response['message'] = 'Update successfully';
        }else{
          $response['status'] =0;
          $response['message'] = 'Something wrong';
        }
      }
      if($type=='no_show'){
        $query=$this->db->query('update appointment set status=5,color_code="'.$color_id.'" where appointment_id="'.$appointment_id.'" ');
        if($query){
           $response['status'] =1;
          $response['message'] = 'Update successfully';
        }else{
          $response['status'] =0;
          $response['message'] = 'Something wrong';
        }
      }
      if($type=='checked_in'){
		  
        $query=$this->db->query('update appointment set status=6,color_code="'.$color_id.'", is_checkin="1", checkin_time="'.date('Y-m-d H:i:s').'" where appointment_id="'.$appointment_id.'" ');
        if($query){
           $response['status'] =1;
          $response['message'] = 'Update successfully';
        }else{
          $response['status'] =0;
          $response['message'] = 'Something wrong';
        }
      }
      if($type=='cancel'){
        $query=$this->db->query('update appointment set status=8,color_code="'.$color_id.'" where appointment_id="'.$appointment_id.'" ');
        if($query){
           $response['status'] =1;
          $response['message'] = 'Update successfully';
        }else{
          $response['status'] =0;
          $response['message'] = 'Something wrong';
        }
      }
      if($type=='delete'){
        $query=$this->db->query('update appointment set status=0,color_code="'.$color_id.'" where appointment_id="'.$appointment_id.'" ');
        if($query){
           $response['status'] =1;
          $response['message'] = 'Update successfully';
        }else{
          $response['status'] =0;
          $response['message'] = 'Something wrong';
        }
      }*/
    }else{
       $response['message'] = 'Required parameter missing'; 
      $response['status']=0;
    }
	
	
    echo json_encode($response);
  }


	public function unconfirmAppointmentCount(){
		
		$vendor_id = $this->input->post('vendor_id');
		$response['status']=0;
		$response['message'] = ''; 
		$response['unconfirmAppointmentCount']=0;
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select count(a.appointment_id) as num from appointment a inner join appointment_service aps on aps.appointment_id=a.appointment_id inner join service s on s.service_id=aps.service_id inner join customer c on c.customer_id=a.customer_id inner join stylist st on aps.stylist_id=st.stylist_id where a.status=1 and a.vendor_id='".$vendor_id."' and a.is_delete=0");
			$res = $query->row();
			$num = $res->num;
			
			$response['status']=1;
			$response['unconfirmAppointmentCount']=$num;
			$response['message'] = ''; 
			
		}else{
			$response['status']=0;
			$response['message'] = 'Required parameter missing'; 
			
		}
		echo json_encode($response);
	}
	
	public function onlinePaymentRequestCount(){
		
		$vendor_id = $this->input->post('vendor_id');
		$response['status']=0;
		$response['message'] = ''; 
		$response['requestCount']=0;
		
		if(!empty($vendor_id)){
			$time = time();
			
			$query=$this->db->query('select "'.$time.'" as unique_id, a.appointment_id,a.token_no,c.customer_id,concat(c.firstname," ",c.lastname) as customer_name,c.mobile_phone,c.email,(CASE when a.appointment_type=1 THEN "single" WHEN a.appointment_type=2 THEN "Multiple" else "Group" end) as appointment_type,(CASE when a.appointment_from=1 THEN "webpos" WHEN a.appointment_from=2 THEN "posapp" WHEN a.appointment_from=3 THEN "custpmerapp"  else "Website" end) as appointment_from,(select group_concat(s.service_name) from  appointment_service as aps inner join service as s on aps.service_id=s.service_id where a.appointment_id=aps.appointment_id) as service_name,(select sum(s.price) from  appointment_service as aps inner join service as s on aps.service_id=s.service_id where a.appointment_id=aps.appointment_id) as service_price from appointment as a inner join customer as c on a.customer_id=c.customer_id where a.is_checkout=0 AND  a.vendor_id="'.$vendor_id.'" and a.appointment_from=3');
			$num = $query->num_rows();
			
			$response['status']=1;
			$response['requestCount']=$num;
			$response['message'] = ''; 
			
		}else{
			$response['status']=0;
			$response['message'] = 'Required parameter missing'; 
			
		}
		echo json_encode($response);
	}
	public function unconfirmApt(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$data=$this->db->query("select a.appointment_id,a.token_no,a.appointment_type, s.service_name,a.date as apt_date, time_format(aps.appointment_time,'%h:%i %p') as appointment_time,time_format(aps.appointment_end_time,'%h:%i %p') as appointment_end_time, CONCAT(c.firstname,' ',c.lastname) as customer_name, c.mobile_phone as phone, CONCAT(st.firstname,' ',st.lastname) as stylist_name from appointment a inner join appointment_service aps on aps.appointment_id=a.appointment_id inner join service s on s.service_id=aps.service_id inner join customer c on c.customer_id=a.customer_id inner join stylist st on aps.stylist_id=st.stylist_id where a.status=1 and a.vendor_id='".$vendor_id."' and a.is_delete=0 ")->result();
		if(!empty($data)){
			$response['status']=1;
			$response['data']=$data;
			$response['message'] = 'Unconfirmed list'; 
		}else{
			$response['status']=0;
			$response['data']=array();
			$response['message'] = 'No unconfirmed list available'; 
		}
	}else{
		$response['status']=0;
			$response['message'] = 'Required parameter missing'; 
	}
	echo json_encode($response);
	}
	
	
	
	function deposit_distribution_type(){
		
		$response['status']=0;
		$response['result']='[]';
		$response['msg']='';
		
		$vendor_id = $this->input->post('vendor_id');
		
		$query = $this->db->query("select * from distribution_type order by dt_id asc");
		$result = $query->result();
		
		if($query->num_rows()>0){
			/*$getServiceCharge=$this->db->query('select value,first_time as payment_type from settings where field="service_charge" and vendor_id="'.$vendor_id.'"')->row();*/
			$getServiceCharge=$this->db->query('select cash_discount_percentage as value,cash_discount_display_name as display_name from vendor where cash_discount_is_active="1" and vendor_id="'.$vendor_id.'"')->row();
			
			
			
			 if(empty($getServiceCharge)){
            $getServiceCharge=(Object)[];
			}
		 
			$response['status']=1;
			$response['result']=$result;
			$response['getServiceCharge']=$getServiceCharge;
			$response['msg']='Success';
		}else{
			
			$response['status']=0;
			$response['result']='[]';
			$response['msg']='No data found';
		}
		
		echo json_encode($response);
		
	
	}
	
	function add_deposit(){
		
		//old api parameters:  cust_id,customer_total,deposit_amount,pool,overdue,iou_limit,event_name,  deposit_type,event_note,vendor_id,start_date(array),start_time(array),end_time(array)
		
		
		 // new param: cust_id, require_deposit, deposit_distribution, distribute_in (percent/dollar), distribute_amount, event_name, vendor_id, no_of_customer (array),  appt_date (array), start_time (array), end_time (array), deposit_date, deposit_amount, event_note, checkbox_1, checkbox_2, checkbox_2
		 
		
		 
		
	/* 	if(isset($_POST['pool'])){
   	$pool=1;
   }else{
   		$pool=0;
   }
   if(isset($_POST['overdue'])){
   	$overdue=1;
   }else{
   	$overdue=0;
   } */
   /* if(isset($_POST['iou_limit'])){
   	$iou_limit=$_POST['iou_limit'];
   }else{
   	$iou_limit=0;
   } */
   
   
  
   
   
  // echo "<pre>";print_r($start_date);exit;
   if(!empty($_POST['cust_id']) && !empty($_POST['event_name'])){
	   
	    
		 $require_deposit = $_POST['require_deposit'];
		 $distrubute_in = $_POST['distribute_in'];
		 $distrubute_amount = $_POST['distribute_amount'];
		 $deposit_date = date('Y-m-d',strtotime($_POST['deposit_date']));
		 $event_note = $_POST['event_note'];
		 $checkbox_1 = $_POST['checkbox_1'];
		 $checkbox_2 = $_POST['checkbox_2'];
		 $checkbox_3 = $_POST['checkbox_3'];
		 
		 if($require_deposit>0){
			 
			 $is_require_deposit = 1;
		 }else{
			 $is_require_deposit = 0;
		 }
		 
		 $start_date=explode(",",$_POST['appt_date']);
   $end_time=explode(",",$_POST['end_time']);
   $start_time=explode(",",$_POST['start_time']);
		
   
   $query=$this->db->query('insert into deposit_customer set customer_id="'.$_POST['cust_id'].'",customer_total="'.$_POST['no_of_customer'].'",deposit_amount="'.$_POST['deposit_amount'].'", event_name="'.$_POST['event_name'].'",deposit_type="'.$_POST['deposit_distribution'].'",vendor_id="'.$_POST['vendor_id'] .'",status="0" ,require_deposit="'.$require_deposit.'", is_require_deposit="1", distribute_in="'.$distrubute_in.'", distribute_amount="'.$distrubute_amount.'", deposit_date="'.$deposit_date.'", event_note="'.$event_note.'", checkbox_1="'.$checkbox_1.'", checkbox_2="'.$checkbox_2.'", checkbox_3="'.$checkbox_3.'" ');
   //echo $this->db->last_query();die;
					$last_id = $this->db->insert_id();
					///$data['last_id']=$last_id;
					if($last_id){
					foreach ($start_date as $key => $value) {
						$this->db->query('insert into deposit_customer_schedule set deposit_id="'.$last_id.'",start_date="'.$value.'",start_time="'.$start_time[$key].'",end_time="'.$end_time[$key].'",color_id="12",status=1');
					}
					$response['deposit_id'] = $last_id;
					$response['message'] = 'Deposit added successfully';
					$response['status'] = 1;
				}else{
					$response['deposit_id'] = 0;
					$response['message'] = 'Fail';
					$response['status'] = 0;
				}
				
	}else{
		$response['status'] = 0;
		$response['message'] = 'Required parameter missing';
		
		
	}
				
				echo json_encode($response);

	}
function update_deposit(){
		  
		 $require_deposit = $_POST['require_deposit'];
		 $distrubute_in = $_POST['distribute_in'];
		 $distrubute_amount = $_POST['distribute_amount'];
		 $deposit_date = date('Y-m-d',strtotime($_POST['deposit_date']));
		 $event_note = $_POST['event_note'];
		 $checkbox_1 = $_POST['checkbox_1'];
		 $checkbox_2 = $_POST['checkbox_2'];
		 $checkbox_3 = $_POST['checkbox_3'];
		 $deposit_id = $_POST['deposit_id'];
		//echo "<pre>";print_r($_POST);exit;
   if(!empty($deposit_id)){
	   
	  
		 
		 if($require_deposit>0){
			 
			 $is_require_deposit = 1;
		 }else{
			 $is_require_deposit = 0;
		 }
		 
		 $start_date=explode(",",$_POST['appt_date']);
   $end_time=explode(",",$_POST['end_time']);
   $start_time=explode(",",$_POST['start_time']);
		
   
   $query=$this->db->query('update deposit_customer set customer_id="'.$_POST['cust_id'].'",customer_total="'.$_POST['no_of_customer'].'",deposit_amount="'.$_POST['deposit_amount'].'", event_name="'.$_POST['event_name'].'",deposit_type="'.$_POST['deposit_distribution'].'",vendor_id="'.$_POST['vendor_id'] .'",require_deposit="'.$require_deposit.'", is_require_deposit="1", distribute_in="'.$distrubute_in.'", distribute_amount="'.$distrubute_amount.'", deposit_date="'.$deposit_date.'", event_note="'.$event_note.'", checkbox_1="'.$checkbox_1.'", checkbox_2="'.$checkbox_2.'", checkbox_3="'.$checkbox_3.'" where id="'.$deposit_id.'"');
   //echo $this->db->last_query();die;
					$last_id = $this->db->insert_id();
					///$data['last_id']=$last_id;
					if($query){
						$this->db->query('delete from deposit_customer_schedule where deposit_id="'.$deposit_id.'"');
					foreach ($start_date as $key => $value) {
						$this->db->query('insert into deposit_customer_schedule set deposit_id="'.$deposit_id.'",start_date="'.$value.'",start_time="'.$start_time[$key].'",end_time="'.$end_time[$key].'",color_id="12",status=1');
					}
					$response['desposit_id'] = $last_id;
					$response['message'] = 'Deposit updated successfully';
					$response['status'] = 1;
				}else{
					$response['desposit_id'] = 0;
					$response['message'] = 'Fail';
					$response['status'] = 0;
				}
				
	}else{
		$response['status'] = 0;
		$response['message'] = 'Required parameter missing';
		
		
	}
				
				echo json_encode($response);

	}
	function get_stylist(){
	//	echo "<pre>";print_r($_POST);exit;
	$vendor_id = $this->input->post('vendor_id');
	$service_id = $this->input->post('service_id');
	$appointment_date = $this->input->post('appointment_date');
	$appointment_time = $this->input->post('appointment_time');
	$appointment_end_time = $this->input->post('appointment_end_time');
	$customer_id = $this->input->post('customer_id');
	$appointment_id = $this->input->post('appointment_id');
	$type = $this->input->post('type');
	$token_no = $this->input->post('token');
		if(!empty($vendor_id)){
		$response['status'] = 0;
		$response['message'] = '';
			
			if($_SERVER['HTTP_HOST']=='localhost'){
			$path = site_url().'/assets/img/stylist/thumb/';
				}else{
					$path = "http://".$_SERVER['HTTP_HOST'].'/salon/assets/img/stylist/thumb/';
				}
				if(!empty($appointment_date)){
					$con="and ss.start_date='".$appointment_date."'";
				}else{
					$con="";
				}
				if($type=='edit'){
					$getStylistNewEdit=$this->db->query('select group_concat(aps.stylist_id) as stylist_id from appointment as a inner join appointment_service as aps on a.appointment_id=aps.appointment_id  where  a.token_no="'.$token_no.'"')->row();
					$conNew="and aps.stylist_id NOT IN (".$getStylistNewEdit->stylist_id.")";
				}else{
					$conNew="";
				}
				//'.$conNew.'
				//echo 'select group_concat(aps.stylist_id) as stylist_id from appointment as a inner join appointment_service as aps on a.appointment_id=aps.appointment_id  where a.date="'.$appointment_date.'" and aps.appointment_time <="'.$appointment_end_time.'" and aps.appointment_end_time > "'.$appointment_time.'" and a.vendor_id="'.$vendor_id.'"  and aps.stylist_id !=" " and a.status!=8';die;
			//	echo 'select group_concat(aps.stylist_id) as stylist_id from appointment as a inner join appointment_service as aps on a.appointment_id=aps.appointment_id  where a.date="'.$appointment_date.'" and aps.appointment_time <="'.$appointment_end_time.'" and aps.appointment_end_time > "'.$appointment_time.'" and a.vendor_id="'.$vendor_id.'"  and aps.stylist_id !=" " '.$conNew.' and a.status!=8';die;
				$getStylistNew=$this->db->query('select group_concat(aps.stylist_id) as stylist_id from appointment as a inner join appointment_service as aps on a.appointment_id=aps.appointment_id  where a.date="'.$appointment_date.'" and aps.appointment_time <="'.$appointment_end_time.'" and aps.appointment_end_time > "'.$appointment_time.'" and a.vendor_id="'.$vendor_id.'"  and aps.stylist_id !=" " '.$conNew.'  and a.status!=8')->row();
				//'.$conNew.'

		//and ss.start_date='".$Currentdate."'
				if($getStylistNew->stylist_id !=''){
					$con2="and s.stylist_id NOT IN (".$getStylistNew->stylist_id.")";
				}else{
					$con2="";
				}
				//".$con2."
		$Currentdate = date('Y-m-d');
			//$getSchedule=$this->db->query('select GROUP_CONCAT()')
			/*$query = $this->db->query("select s.login_id,s.stylist_id,st.role_name as title,l.email,CONCAT(s.firstname,' ',s.lastname) as stylist_name, s.phone, s.alternate_phone,s.type, IF(l.is_active=1,'Active','Inactive') as status,l.pin,l.username, DATE_FORMAT(l.created_date,'%M %d %Y') AS registered_on, CONCAT('$path','/',if(s.photo='','noimage.png',s.photo)) as photo from login l INNER JOIN stylist s ON s.login_id=l.login_id INNER JOIN role st ON st.role_id=s.title_id inner join stylist_schedule as ss on s.stylist_id=ss.stylist_id where l.is_delete='0' and l.vendor_id='".$vendor_id."' and ss.start_date='".$Currentdate."' ORDER BY s.stylist_id DESC ");*/
		//	echo "select s.stylist_id,CONCAT(s.firstname,' ',s.lastname) as stylist_name from stylist_service as ssr inner join stylist as s on ssr.stylist_id=s.stylist_id inner join stylist_schedule as ss on ssr.stylist_id=ss.stylist_id inner join login as l on l.login_id=s.login_id where l.is_delete='0' and l.vendor_id='".$vendor_id."'  and ssr.service_id='".$service_id."' and l.is_active=1 ".$con." ".$con2." group by ss.stylist_id ORDER BY s.stylist_id DESC";die;
			$query=$this->db->query("select s.stylist_id,CONCAT(s.firstname,' ',s.lastname) as stylist_name from stylist_service as ssr inner join stylist as s on ssr.stylist_id=s.stylist_id inner join stylist_schedule as ss on ssr.stylist_id=ss.stylist_id inner join login as l on l.login_id=s.login_id where l.is_delete='0' and l.vendor_id='".$vendor_id."'  and ssr.service_id='".$service_id."' and l.is_active=1 ".$con." ".$con2." group by ss.stylist_id ORDER BY s.stylist_id DESC " );
			$getAppointmentDataCustomer=$this->db->query('select count(a.appointment_id) as count from appointment as a inner join appointment_service as aps on a.appointment_id=aps.appointment_id  where a.date="'.$appointment_date.'" and aps.appointment_time <="'.$appointment_end_time.'" and aps.appointment_end_time > "'.$appointment_time.'" and a.vendor_id="'.$vendor_id.'"  and a.customer_id ="'.$customer_id.'"  and a.status!=8')->row();
			//echo 'select count(a.appointment_id) as total from appointment as a inner join appointment_service as aps on a.appointment_id=aps.appointment_id  where a.date="'.$appointment_date.'" and aps.appointment_time <="'.$appointment_end_time.'" and aps.appointment_end_time > "'.$appointment_time.'" and a.vendor_id="'.$vendor_id.'"  and a.customer_id ="'.$customer_id.'"  and a.status!=8';
			//".$con2.";
			$res = $query->result();
			if($appointment_id !='' && $appointment_id !=0){
				$getAppointmentTime=$this->db->query('select aps.appointment_time,aps.appointment_end_time from appointment_service where aps.appointment_id="'.$appointment_id.'"')->row();

			}else{
				$getAppointmentTime=(object)[];
			}
			//echo "harsh".$getAppointmentDataCustomer->count;die;
			if(!empty($getAppointmentTime)){
				if($type=='edit' && $getAppointmentTime->appointment_time==$appointment_time && $getAppointmentTime->appointment_end_time==$appointment_end_time){
					if($res){
						$response['status'] = 1;
					$response['result'] = $res;
					$response['message'] = 'Data found';
				}else{
					$response['status'] = 0;
					$response['result'] = array();
					$response['message'] = 'No data found';
				}
					
				
				}else if($getAppointmentDataCustomer->count >0){
					$response['status'] = 0;
					$response['result'] = array();
					$response['message'] = 'Customer is already booked for this date and time';
				}else{
					if($res){
					$response['status'] = 1;
					$response['result'] = $res;
					$response['message'] = 'Data found';
				}else{
					$response['status'] = 0;
					$response['result'] = array();
					$response['message'] = 'No data found';
				}
					
				
				
				}
			}
			
			else if($getAppointmentDataCustomer->count >0 && $type !='edit'){
				$response['status'] = 0;
				$response['result'] = array();
				$response['message'] = 'Customer is already booked for this date and time';
			}
			else if($res){
				
				$response['status'] = 1;
				$response['result'] = $res;
				$response['message'] = 'Data found';
				
			}else{
				$response['status'] = 0;
				$response['message'] = 'No data found';
				$response['result'] = array();
			}
			}else{
				$response['status'] = 0;
				$response['message'] = 'Required parameter missing';
			}
		
		echo json_encode($response);
	}
	
	
	
	function get_stylist_group(){
	$vendor_id = $this->input->post('vendor_id');
	//$service_id = $this->input->post('service_id');
		if(!empty($vendor_id)){
		$response['status'] = 0;
		$response['message'] = '';
			
			if($_SERVER['HTTP_HOST']=='localhost'){
			$path = site_url().'/assets/img/stylist/thumb/';
				}else{
					$path = "http://".$_SERVER['HTTP_HOST'].'/salon/assets/img/stylist/thumb/';
				}
		
		$Currentdate = date('Y-m-d');
		$query1 = $this->db->query("select s.service_id, s.service_name, s.sku, sc.category_name, s.price,s.duration, if(s.is_active=1,'Active','Inactive') as status,s.commission_type,s.commission_amount as commission from service s INNER JOIN service_category sc ON sc.category_id=s.category_id where s.is_delete='0' and s.is_active='1' and s.vendor_id='".$vendor_id."' order by s.service_id DESC");
			$res = $query1->result();
			//echo "<pre>";print_r($res);exit;
			$getData=array();
			foreach($res as $key=>$service){
			$query=$this->db->query("select s.stylist_id,CONCAT(s.firstname,' ',s.lastname) as stylist_name from stylist_service as ssr inner join stylist as s on ssr.stylist_id=s.stylist_id inner join stylist_schedule as ss on ssr.stylist_id=ss.stylist_id inner join login as l on l.login_id=s.login_id where l.is_delete='0' and l.vendor_id='".$vendor_id."' and ss.start_date='".$Currentdate."' and ssr.service_id='".$service->service_id."' group by ss.stylist_id ORDER BY s.stylist_id DESC " );
				$res1 = $query->result();
				$getData['service_data']=$res;
				$getData['service_data'][$key]->stylist_name=$res1;
				//$getData['service_stylist']->$service_name=$res1;
			}
			//echo "<pre>";print_r($getData);exit;
			if($getData){
				
				$response['status'] = 1;
				$response['result'] = $getData;
				$response['message'] = 'Data found';
				
			}else{
				$response['status'] = 0;
				$response['message'] = 'No data found';
				$response['result'] = array();
			}	

			
			}else{
				$response['status'] = 0;
				$response['message'] = 'Required parameter missing';
			}
		
		echo json_encode($response);
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
	public function checkCustomerCard(){
		$customer_id=$this->input->post('customer_id');
		if(!empty($customer_id)){
		$customerData=$this->db->query('select card_number,cvv from customer where customer_id="'.$customer_id.'" and card_number!=" " and cvv!=" " ')->row();
		//echo 'select card_number,cvv from customer where customer_id="'.$customer_id.'" and card_number=" " and cvv=" " ';exit;
		if(!empty($customerData)){
			     $response['status'] = 0;
				$response['message'] = 'Customer card details already exist';
		}else{
			  $response['status'] = 1;
				$response['message'] = "Customer card details don't exist";
		}
	}else{
				$response['status'] = 0;
				$response['message'] = 'Required parameter missing';
			}
			echo json_encode($response);
	}
	public function UpdateCustmerCard(){
		$customer_id=$this->input->post('customer_id');
		$card_holder_name = $this->input->post('card_holder_name'); 
		$card_number = $this->input->post('card_number'); 
		$cvv = $this->input->post('cvv'); 
		$expiry_month = $this->input->post('expiry_month'); 
		$expiry_year = $this->input->post('expiry_year'); 
		$card_type = $this->input->post('card_type');
		if(!empty($customer_id) && !empty($card_number) && !empty($cvv) && !empty($expiry_month) && !empty($expiry_year)){
			$checkCustomerCard=$this->db->query("select * from customer_card where  where customer_id='".$customer_id."'")->num_rows();
			if($checkCustomerCard >0){
				$query = $this->db->query("update customer_card set card_holder_name='".$card_holder_name."', card_number='".$card_number."', cvv='".$cvv."', card_month='".$expiry_month."', card_year='".$expiry_year."',card_type='".$card_type."' where customer_id='".$customer_id."' ");
			}else{
				$query = $this->db->query("insert into customer_card set card_holder_name='".$card_holder_name."', card_number='".$card_number."', cvv='".$cvv."', card_month='".$expiry_month."', card_year='".$expiry_year."',card_type='".$card_type."',is_default=1 ");
			}
				
				if($query){
					$response['status'] = 1;
				$response['message'] = 'Customer card details added successfully';
				}else{
					$response['status'] = 0;
				$response['message'] = 'Something went wrong';
				}
			}else{
				$response['status'] = 0;
				$response['message'] = 'Required parameter missing';
			}
			echo json_encode($response);
	}
	function add_group_appointment(){
	$appointment_data = $this->input->post('appointment_data');
		 $country = $this->ip_info("Visitor", "Country");
		if($country=='India'){
			date_default_timezone_set("Asia/Kolkata");
		}else{
			
			date_default_timezone_set("America/Los_Angeles");
		}
		//$getColorCode=$this->db->query('select color_id from color_settings where vendor_id="'.$appointment_data_check->vendor_id.'" and color_type="confirm"')->row();
		/*$appointment_data='{
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
}';*/
		$appointment_data_check = json_decode($appointment_data);
		//echo "<pre>";print_r($appointment_data_check);exit;
		if(!empty($appointment_data_check))
		{
			$color_id = $this->getColorIdByColorType('confirm',$appointment_data_check->vendor_id);
			if(!empty($appointment_data_check->event_name)){
				//echo 'insert into deposit_customer set customer_id="'.$appointment_data_check->customer_data->group_head.'",customer_total="'.$appointment_data_check->total_customer.'",deposit_amount="'.$appointment_data_check->deposit_data->deposit_amount_require.'", event_name="'.$appointment_data_check->event_name.'",deposit_type="'.$appointment_data_check->deposit_data->distribute_in.'",vendor_id="'.$appointment_data_check->vendor_id .'",status="1" ,require_deposit="'.$appointment_data_check->deposit_data->deposit_amount_require.'", is_require_deposit="1", distribute_in="'.$appointment_data_check->deposit_data->distribute_in.'",distribute_amount="'.$appointment_data_check->deposit_data->amount.'",status=1';die;
					 $query=$this->db->query('insert into deposit_customer set customer_id="'.$appointment_data_check->customer_data->group_head.'",customer_total="'.$appointment_data_check->total_customer.'",deposit_amount="'.$appointment_data_check->deposit_data->deposit_amount_require.'", event_name="'.$appointment_data_check->event_name.'",deposit_type="'.$appointment_data_check->deposit_data->distribute_in.'",vendor_id="'.$appointment_data_check->vendor_id .'",status="1" ,require_deposit="'.$appointment_data_check->deposit_data->deposit_amount_require.'", is_require_deposit="1", distribute_in="'.$appointment_data_check->deposit_data->distribute_in.'",distribute_amount="'.$appointment_data_check->deposit_data->amount.'"');
   //echo $this->db->last_query();die;
					$deposit_id = $this->db->insert_id();
					if(!empty($appointment_data_check->event_data)){
					foreach($appointment_data_check->event_data as $Eventval){
							$this->db->query('insert into deposit_customer_schedule set deposit_id="'.$deposit_id.'",start_date="'.$Eventval->date.'",start_time="'.$Eventval->start_time.'",end_time="'.$Eventval->end_time.'",no_of_customer="'.$Eventval->no_of_customer.'",color_id="'.$color_id.'",status=1');
					}
					if(!empty($appointment_data_check->deposit_data->deposit_amount_require)){
						foreach($appointment_data_check->deposit_data->deposit_installment as $instVal){
							$Currentdate=date('Y-m-d');
							if($Currentdate==$instVal->date)
							{
								$getFcmToken=$this->db->query('select l.login_id,l.fcm_token from vendor as v inner join login as l on l.login_id=v.login_id where v.vendor_id="'.$appointment_data_check->vendor_id.'"')->row();
								$this->load->library('SendFcm_tab');
								$title="New Event Transaction";
							$subtitle="New Event Transaction";
							$message="New Event Transaction";
							$fcm_responce2=$this->sendfcm_tab->sendNotification($title,$message,array($getFcmToken->fcm_token),$subtitle);
							}
						$this->db->query('insert into deposit_installment set deposit_id="'.$deposit_id.'",deposit_date="'.$instVal->date.'",amount="'.$instVal->amount.'",is_active=0');
						}
					}

				}
				}else{
					$deposit_id=0;
				}

			
		if(!empty($appointment_data_check->customer_data->data)){
			
			$qr = $this->db->query("select max(token_no) as token from appointment");
			$re = $qr->row();
			$token = ($re->token)+1;
			$getDone=array();
			foreach($appointment_data_check->customer_data->data as $val){
		    	foreach($val->services as $valData){
		    		$appointment_date=date("Y-m-d", strtotime($valData->date) );
						$qry = $this->db->query("insert into appointment set vendor_id='".$appointment_data_check->vendor_id."', date='".$appointment_date."', appointment_duration='".$valData->duration."', customer_id='".$val->customer_id."',group_leader_id='".$appointment_data_check->customer_data->group_head."',color_code='".$color_id."', rendering='back', created_date='".date('Y-m-d')."', appointment_type='3', token_no='".$token."', deposit_id='".$deposit_id."' ");
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
				/*Mail Message*/
				/*Mail Content*/
							$customer_detail = $this->getCustomerById($customer_id);
							
					          $customer_name = $customer_detail->customer_name;
					          $to = $customer_detail->email;
					         // echo $to;die;
							/*if(!empty($service_id1)){*/
							//echo "test";die;
					          $data['customer_name'] =  $customer_name; 
					          $data['appointment_date'] = $appointment_date; 
					          $data['appointment_time'] = $appointment_time; 
					          $data['phone'] = $customer_detail->mobile_phone;
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
					      );*/
					   
					     // $this->load->library('email', $config); // Load email template
					     // $this->email->set_newline("\r\n");
					    //  $this->email->from('info@booknpay.com', 'BookNPay');
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
					          $getAppointmentData=$this->db->query("select group_concat(concat(DAYNAME(a.date),' - ',date_format(a.date,'%c-%d-%Y')) SEPARATOR  ' /n ') as date,group_concat(time_format(a.date,'%c-%d-%Y')) as newDate,group_concat(concat(sr.service_name,' with ', concat(st.firstname,' ',st.lastname)) SEPARATOR  '-') as service_name,group_concat(aps.duration SEPARATOR  ' /n ') as duration,group_concat(time_format(aps.appointment_time,'%l:%i %p') SEPARATOR  ' /n ') as ap_time,group_concat(time_format(aps.appointment_time,'%l:%i %p')) as new_time,group_concat(concat(st.firstname,'',st.lastname)) as stylist_name from appointment as a inner join appointment_service as aps on aps.appointment_id=a.appointment_id inner join service as sr on sr.service_id=aps.service_id inner join stylist as st on st.stylist_id=aps.stylist_id where a.token_no='".$token."'")->row();
  							$getCancellationPolicy=$this->db->query('select policy_text from cancellation_policy where apt_type=1 and vendor_id="'.$appointment_data_check->vendor_id.'"')->row();
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

					     $newAppointemnt=$this->db->query('select email_subject,email_content from email_settings where slug="new_appointment" and is_active=1 and vendor_id="'.$vendor_id.'"')->row();
					     //echo "<pre>";print_r($newAppointemnt);exit;
					      if(!empty($newAppointemnt)){
					      	$data['template_type']='new_appointment';
					      	$data['vendor_id']=$appointment_data_check->vendor_id;
					      //	$this->load->library('email');
                  			// $this->email->initialize($config);
                  			// $this->email->from('info@booknpay.com', 'Hubwallet');
					        //$data['new_content']=$newAppointemnt->email_content;
					     //   $this->email->to($to); // replace it with receiver email id
					    //  $this->email->subject($newAppointemnt->email_subject); // replace it with email subject
					      	$businessName=$this->db->query('select vendor_name from vendor where vendor_id="'.$appointment_data_check->vendor_id.'"')->row();
					      	$subject=str_replace('{Business Name}',$businessName->vendor_name,$newAppointemnt->email_subject);
					      $message = $this->load->view('email_template/new_appointment',$data,TRUE);
					      $this->load->library('Send_mail');
						  $this->send_mail->sendMail($to, $subject, $message, $fileName=false, $filePath=false, $cc=false);
					      $this->load->library('Send_mail');
						$this->send_mail->sendMail($to, $newAppointemnt->email_subject, $message, $fileName=false, $filePath=false, $cc=false);
					      }
					  /*}*/
				$response['status']=1;
				$response['message']='Group appointment booked successfully';
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
	function getAppointmentEditData(){
		$appointment_id=$this->input->post('appointment_id');
		if(!empty($appointment_id)){
			$getData=$this->db->query('select a.appointment_id,a.date,a.deposit_id,a.customer_id,concat(c.firstname," ",c.lastname) as group_leader_name,aps.service_id,aps.stylist_id,a.token_no,aps.appointment_time,aps.appointment_end_time,aps.price,aps.duration from appointment as a inner join appointment_service as aps on a.appointment_id=aps.appointment_id inner join customer as c on a.group_leader_id=c.customer_id where a.appointment_id="'.$appointment_id.'"')->row();
			if(!empty($getData)){
				$start_date=$this->db->query('select start_date from  deposit_customer_schedule where status=1 and deposit_id="'.$getData->deposit_id.'" order by id asc limit 1')->row();
				$end_date=$this->db->query('select start_date from  deposit_customer_schedule where status=1 and deposit_id="'.$getData->deposit_id.'" order by id desc limit 1')->row();
				if(!empty($start_date)){
					$start_date_new=$start_date->start_date;
				}else{
					$start_date='';
				}
				if(!empty($end_date)){
					$end_date_new=$end_date->start_date;
				}else{
					$end_date='';
				}
				$response['status']=1;
				$response['appointment_data']=$getData;
				$response['start_date']=$start_date_new;
				$response['end_date']=$end_date_new;
				$response['message']='Data found';
			}else{
				$response['start_date']=$start_date_new;
				$response['end_date']=$end_date_new;
				$response['status']=0;
				$response['appointment_data']=(Object)[];
				$response['message']='No data found';
			}
		}else{

				$response['status'] = 0;
				$response['message'] = 'Required parameter missing';
			}
			echo json_encode($response);
	}
	public function update_group_appointment(){
		$appointment_id=explode(",",$this->input->post('appointment_id'));
		$appointment_date=$this->input->post('appointment_date');
		$appointment_time=$this->input->post('appointment_time');
		$appointment_end_time=explode(",",$this->input->post('appointment_end_time'));
		$service_id=explode(",",$this->input->post('service_id'));
		$stylist_id=explode(",",$this->input->post('stylist_id'));
		$price=explode(",",$this->input->post('price'));
		$duration=explode(",",$this->input->post('duration'));
		$customer_id=$this->input->post('customer_id');
		$token_no=$this->input->post('token');
	///	echo "<pre>";print_r($_POST);exit;
		if(!empty($appointment_id)){
			$getId=array();
			$getAppointmentId=$this->db->query('select * from appointment where token_no="'.$token_no.'"')->result();
			foreach($getAppointmentId as $val){
				$getId[]=$val->appointment_id;
			}
			foreach($appointment_id as $key=>$val){
				$endTime = strtotime("+".$duration[$key]."minutes", strtotime($appointment_time));
						$new_end_time=date('H:i', $endTime);
				//echo $val;
				//echo "<pre>";print_r($getId);exit;
				if(in_array($val,$getId)){
					//echo "harsh";die;
						$query=$this->db->query('update appointment set date="'.$appointment_date.'",customer_id="'.$customer_id.'" where appointment_id="'.$val.'"');
		
					$this->db->query('update appointment_service  set appointment_time="'.$appointment_time.'",appointment_end_time="'.$new_end_time.'",service_id="'.$service_id[$key].'",stylist_id="'.$stylist_id[$key].'",price="'.$price[$key].'",duration="'.$duration[$key].'",customer_id="'.$customer_id.'" where appointment_id="'.$val.'"');
					//echo $this->db->last_query();
					if($query){
						$status='done';
					}
				}else{
					//echo "vishal";die;
					$endTime = strtotime("+".$duration[$key]."minutes", strtotime($appointment_time));
						$new_end_time=date('H:i', $endTime);
					$query=$this->db->query('insert into appointment set date="'.$appointment_date.'",customer_id="'.$getAppointmentId[0]->customer_id.'",token_no="'.$token_no.'",group_leader_id="'.$getAppointmentId[0]->group_leader_id.'",is_active=1,is_delete=0,color_code="'.$getAppointmentId[0]->color_code.'",appointment_type=3,vendor_id="'.$getAppointmentId[0]->vendor_id.'",deposit_id="'.$getAppointmentId[0]->deposit_id.'"');
					$insert_id = $this->db->insert_id();
			if($query){
						$status='done';
					}
			$this->db->query('insert into appointment_service  set appointment_id="'.$insert_id .'",appointment_time="'.$appointment_time.'",appointment_end_time="'.$new_end_time[$key].'",service_id="'.$service_id[$key].'",stylist_id="'.$stylist_id[$key].'",price="'.$price[$key].'",duration="'.$duration[$key].'",customer_id="'.$getAppointmentId[0]->customer_id.'" ');
				}
			
			}
			
				if(!empty($status)){
					$customer_detail = $this->getCustomerById($customer_id);
							
					          $customer_name = $customer_detail->customer_name;
					          $to = $customer_detail->email;
					         // echo $to;die;
							/*if(!empty($service_id1)){*/
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
					      );*/
					   
/*					      $this->load->library('email', $config); // Load email template
					      $this->email->set_newline("\r\n");
					      $this->email->from('info@booknpay.com', 'BookNPay');*/
					     
					          $getAppointmentData=$this->db->query("select group_concat(concat(DAYNAME(a.date),' - ',date_format(a.date,'%m-%d-%Y')) SEPARATOR  ' /n ') as date,group_concat(time_format(a.date,'%m-%d-%Y')) as newDate,group_concat(concat(sr.service_name,' with ', concat(st.firstname,' ',st.lastname)) SEPARATOR  '-') as service_name,group_concat(aps.duration SEPARATOR  ' /n ') as duration,group_concat(time_format(aps.appointment_time,'%H:%i %p') SEPARATOR  ' /n ') as ap_time,group_concat(time_format(aps.appointment_time,'%H:%i %p')) as new_time,group_concat(concat(st.firstname,'',st.lastname)) as stylist_name from appointment as a inner join appointment_service as aps on aps.appointment_id=a.appointment_id inner join service as sr on sr.service_id=aps.service_id inner join stylist as st on st.stylist_id=aps.stylist_id where a.token_no='".$token_no."'")->row();
  							$getCancellationPolicy=$this->db->query('select policy_text from cancellation_policy where apt_type=1 and vendor_id="'.$getAppointmentId[0]->vendor_id.'"')->row();
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

					     $newAppointemnt=$this->db->query('select email_subject,email_content from email_settings where slug="appointment_update" and is_active=1 and vendor_id="'.$vendor_id.'"')->row();
					     //echo "<pre>";print_r($newAppointemnt);exit;
					      if(!empty($newAppointemnt)){
					      	$data['template_type']='appointment_update';
					      	$data['vendor_id']=$getAppointmentId[0]->vendor_id;
					      //	$this->load->library('email');
                  			// $this->email->initialize($config);
                  			// $this->email->from('info@booknpay.com', 'Hubwallet');
					        //$data['new_content']=$newAppointemnt->email_content;
					      //  $this->email->to($to); // replace it with receiver email id
					     // $this->email->subject($newAppointemnt->email_subject); // replace it with email subject
					     $businessName=$this->db->query('select vendor_name from vendor where vendor_id="'.$getAppointmentId[0]->vendor_id.'"')->row();
					      	$subject=str_replace('{Business Name}',$businessName->vendor_name,$newAppointemnt->email_subject);
					      $message = $this->load->view('email_template/new_appointment',$data,TRUE);
					      $this->load->library('Send_mail');
						  $this->send_mail->sendMail($to, $subject, $message, $fileName=false, $filePath=false, $cc=false);
					      $this->load->library('Send_mail');
						$this->send_mail->sendMail($to, $newAppointemnt->email_subject, $message, $fileName=false, $filePath=false, $cc=false);
					      }
					 /* }*/
					$response['status'] = 1;
					$response['message'] = 'Appointment updated successfully';	
				}else{
					$response['status'] = 0;
					$response['message'] = 'Something went wrong';
				}
				
		
			}else{
						$response['status'] = 0;
						$response['message'] = 'Required parameter missing';
				}
			echo json_encode($response);
	}

	function getEventData(){
		$deposit_id=$this->input->post('deposit_id');
		if(!empty($appointment_id)){
			$getEventdata=$this->db->query('select id,event_name,require_deposit from deposit_customer where id="'.$deposit_id.'"')->row();
			if(!empty($getEventdata)){
				$response['status']=1;
				$response['deposit_data']=$getEventdata;
				$getDepositSchedule=$this->db->query('select id as desposit_schedule_id,start_date,start_time,end_time from deposit_customer_schedule where deposit_id="'.$deposit_id.'"')->result();
				if(!empty($getDepositSchedule)){
					$response['deposit_schedule']=$getDepositSchedule;
				}else{
					$response['deposit_schedule']=array();
				}
				$getDepositInstallment=$this->db->query('select id as desposit_installment_id,deposit_date,amount from deposit_installment where deposit_id="'.$deposit_id.'" and is_active=0')->result();
				if(!empty($getDepositInstallment)){
					$response['deposit_installment']=$getDepositInstallment;
				}else{
					$response['deposit_installment']=array();
				}

			}else{
				$response['status']=0;
				$response['deposit_data']=(Object)[];
				$response['deposit_schedule']=array();
				$response['deposit_installment']=array();
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);

	}
	function getBusinessTime(){
		$vendor_id=$this->input->post('vendor_id');
		$appointment_date=$this->input->post('appointment_date');
		if(!empty($vendor_id) && !empty($appointment_date)){
			$dayName= date('l', strtotime($appointment_date));	
			$getTime=$this->db->query('select start_time,end_time from business_hour where vendor_id="'.$vendor_id.'" and days="'.$dayName.'" and switch=1')->row();
			if(!empty($getTime)){
				$response['status'] = 1;
				$response['getData'] =$getTime;
				$response['message'] = 'Data found';	
			}else{
				$response['status'] = 1;
				$response['getData'] =(object)[];
				$response['message'] = 'Data found';	
	
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}

	public function getDataByEventId(){
		$vendor_id=$this->input->post('vendor_id');
		$deposit_id=$this->input->post('deposit_id');
		if(!empty($vendor_id) && !empty($deposit_id)){
			$getEvendata=$this->db->query('select id as event_id,event_name,customer_total,require_deposit,distribute_in,distribute_amount from deposit_customer where id="'.$deposit_id.'"')->row();
			if(!empty($getEvendata)){
				$getScheduleData=$this->db->query('select id as schedule_id,start_date,start_time,end_time from deposit_customer_schedule where deposit_id="'.$deposit_id.'"')->result();
					$getInstallmentData=$this->db->query('select id as installment_id,deposit_date,amount from deposit_installment where deposit_id="'.$deposit_id.'"')->result();
					$getStartDate=$this->db->query('select start_date from deposit_customer_schedule where deposit_id="'.$deposit_id.'" order by start_date asc')->row();
					$getEndDate=$this->db->query('select start_date as end_date from deposit_customer_schedule where deposit_id="'.$deposit_id.'" order by start_date desc')->row();
					$groupHeadName=$this->db->query('select concat(c.firstname," ",c.lastname) as group_head_name from appointment as a inner join customer as c on c.customer_id=a.group_leader_id where a.deposit_id="'.$deposit_id.'"')->row();
					$totalBooked=$this->db->query('select a.appointment_id from appointment as a  where a.deposit_id="'.$deposit_id.'" group by a.customer_id')->num_rows();
					$remaining=$getEvendata->customer_total - $totalBooked;
					$response['status']=1;
					$response['eventData']=$getEvendata;
					if(!empty($getScheduleData)){
						$response['scheduleData']=$getScheduleData;
					}else{
						$response['scheduleData']=array();
					}
					if(!empty($getInstallmentData)){
						$response['installmentData']=$getInstallmentData;
					}else{
						$response['installmentData']=array();
					}
					$response['start_date']=$getStartDate->start_date;
					$response['end_date']=$getEndDate->end_date;
					$response['group_head_name']=$groupHeadName->group_head_name;
					$response['group_head_name']=$groupHeadName->group_head_name;
					$response['remaining']=$remaining;
					$response['message']="Data found";
			}else{
				$response['status']=1;
				$response['eventData']=(object)[];
				$response['scheduleData']=array();
				$response['start_date']="";
					$response['end_date']="";
					$response['group_head_name']="";
				$response['installmentData']=array();
				$response['remaining']=0;
				$response['message']="Something went wrong";
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);

	}
	public function update_event_data(){

	$appointment_data = $this->input->post('appointment_data');
		 $country = $this->ip_info("Visitor", "Country");
		if($country=='India'){
			date_default_timezone_set("Asia/Kolkata");
		}else{
			
			date_default_timezone_set("America/Los_Angeles");
		}
		
		$appointment_data_check = json_decode($appointment_data);
		//echo "<pre>";print_r($appointment_data_check);exit;
		if(!empty($appointment_data_check))
		{

			if(!empty($appointment_data_check->event_name)){
				//echo 'insert into deposit_customer set customer_id="'.$appointment_data_check->customer_data->group_head.'",customer_total="'.$appointment_data_check->total_customer.'",deposit_amount="'.$appointment_data_check->deposit_data->deposit_amount_require.'", event_name="'.$appointment_data_check->event_name.'",deposit_type="'.$appointment_data_check->deposit_data->distribute_in.'",vendor_id="'.$appointment_data_check->vendor_id .'",status="1" ,require_deposit="'.$appointment_data_check->deposit_data->deposit_amount_require.'", is_require_deposit="1", distribute_in="'.$appointment_data_check->deposit_data->distribute_in.'",distribute_amount="'.$appointment_data_check->deposit_data->amount.'",status=1';die;
					 $query=$this->db->query('update deposit_customer set customer_total="'.$appointment_data_check->total_customer.'",deposit_amount="'.$appointment_data_check->deposit_data->deposit_amount_require.'", event_name="'.$appointment_data_check->event_name.'",deposit_type="'.$appointment_data_check->deposit_data->distribute_in.'",vendor_id="'.$appointment_data_check->vendor_id .'",status="1" ,require_deposit="'.$appointment_data_check->deposit_data->deposit_amount_require.'", is_require_deposit="1", distribute_in="'.$appointment_data_check->deposit_data->distribute_in.'",distribute_amount="'.$appointment_data_check->deposit_data->amount.'" where id="'.$appointment_data_check->event_id.'"');
   //echo $this->db->last_query();die;
					
					if(!empty($appointment_data_check->event_data)){
					foreach($appointment_data_check->event_data as $Eventval){
								$checkSchedule_id=$this->db->query('select id from deposit_customer_schedule where id="'.$Eventval->schedule_id.'"')->num_rows();
								if($checkSchedule_id > 0){
										$this->db->query('update deposit_customer_schedule set start_date="'.$Eventval->date.'",start_time="'.$Eventval->start_time.'",end_time="'.$Eventval->end_time.'" where id="'.$Eventval->schedule_id.'" ');
								}else{
								$this->db->query('insert into deposit_customer_schedule set deposit_id="'.$appointment_data_check->event_id.'",start_date="'.$Eventval->date.'",start_time="'.$Eventval->start_time.'",end_time="'.$Eventval->end_time.'",no_of_customer="'.$Eventval->no_of_customer.'",color_id="12",status=1');	
								}
							
					}
					if(!empty($appointment_data_check->deposit_data->deposit_amount_require)){
						foreach($appointment_data_check->deposit_data->deposit_installment as $instVal){
							$checkInstallment_id=$this->db->query('select id from deposit_installment where id="'.$instVal->installment_id.'"')->num_rows();

							if($checkInstallment_id >0){
									$this->db->query('update deposit_installment set deposit_date="'.$instVal->date.'",amount="'.$instVal->amount.'" where id="'.$instVal->installment_id.'"');
							}else{
								$this->db->query('insert into deposit_installment set deposit_id="'.$appointment_data_check->event_id.'",deposit_date="'.$instVal->date.'",amount="'.$instVal->amount.'",is_active=0');
							}


								
						}
					}

				}
				}else{
					$deposit_id=0;
				}

			
		if(!empty($appointment_data_check->customer_data->data)){
			$color_id = $this->getColorIdByColorType('confirm',$appointment_data_check->vendor_id);
			/*$qr = $this->db->query("select max(token_no) as token from appointment");
			$re = $qr->row();
			$token = ($re->token)+1;*/
			$getDone=array();
			$getAppointmentData=$this->db->query('select token_no,group_leader_id from appointment where deposit_id="'.$appointment_data_check->event_id.'"')->row();
			foreach($appointment_data_check->customer_data->data as $val){
		    	foreach($val->services as $valData){
		    		$appointment_date=date("Y-m-d", strtotime($valData->date) );
						$qry = $this->db->query("insert into appointment set vendor_id='".$appointment_data_check->vendor_id."', date='".$appointment_date."', appointment_duration='".$valData->duration."', customer_id='".$val->customer_id."',group_leader_id='".$getAppointmentData->group_leader_id."',color_code='".$color_id."', rendering='back', created_date='".date('Y-m-d')."', appointment_type='3', token_no='".$getAppointmentData->token."', deposit_id='".$appointment_data_check->event_id."' ");
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
				$response['message']='Event updated  successfully';
			}else{
					$response['status']=1;
				$response['message']='Something went wrong';
			}

		}else{
			$response['status']=1;
			$response['message']='Event updated  successfully';
		}
	}else{
			$response['status']=0;
			$response['message']='json data not valid';
	}

		echo json_encode($response);

	
	}
 /*public function CancellationPolicy(){
 	//$color_id,$cancel_type
 	$color_type=$this->input->post('color_id');
 	$cancel_type=$this->input->post('status_type');
  	$customer_id=$this->input->post('customer_id');
  	$appointment_id=$this->input->post('appointment_id');
  	$vendor_id=$this->input->post('vendor_id');
  	$apt_type=$this->input->post('apt_type');
  	$getCancellationPolicySingle=$this->db->query('select field1,field2,field2_type,field3,field3_type from cancellation_policy where vendor_id="'.$vendor_id.'" and apt_type="1"')->row();
  	$getCancellationPolicyGroup=$this->db->query('select field1,field2,field2_type,field3,field3_type from cancellation_policy where vendor_id="'.$vendor_id.'" and apt_type="2"')->row();
  	$getCustomer_card=$this->db->query('select cardholder_name,card_number,expiry_month,expiry_year,cvv,zipcode from customer_card where customer_id="'.$customer_id.'" and is_default=1')->row();
  	$getAmount=$this->db->query('select concat(a.date," ",aps.appointment_time) as appointment_date,sum(aps.price) as total_price from appointment as a inner join appointment_service as aps on a.appointment_id=aps.appointment_id where a.appointment_id="'.$appointment_id.'"')->row();
  	$Currentdate = date('Y-m-d h:i');
  	if(!empty($getCustomer_card)){
  		$getCard=$getCustomer_card;
  	}else{
  		$getCard=(Object)[];
  	}
  //	echo $Currentdate."---".$getAmount->appointment_date;
  	$timestamp1=strtotime($Currentdate);
  	$timestamp2=strtotime($getAmount->appointment_date);
  	$hour_new = abs($timestamp2 - $timestamp1)/(60*60);
  	$hour= round($hour);
  	if($cancel_type=='cancel' && $apt_type !=3 && $hour < $getCancellationPolicySingle->field1){
  		if($getCancellationPolicySingle->field2_type=='Amount'){
  			$amountValue=$getCancellationPolicySingle->field2;
  		}else{
  			$amountValueNew=$getCancellationPolicySingle->field2 * $getAmount->total_price;
  			$amountValue=$amountValueNew /100;
  		}
  		$response['message']="";
  	}else if($cancel_type=='cancel' && $apt_type ==3 && $hour < $getCancellationPolicyGroup->field1){
  		if($getCancellationPolicyGroup->field2_type=='Amount'){
  			$amountValue=$getCancellationPolicyGroup->field2;
  		}else{
  			$amountValueNew=$getCancellationPolicyGroup->field2 * $getAmount->total_price;
  			$amountValue=$amountValueNew /100;
  		}
  		$response['message']="";
  	}
  	else if($cancel_type=='no_show' && $apt_type !=3 && $hour < $getCancellationPolicySingle->field1){
  		if($getCancellationPolicySingle->field3_type=='Amount'){
  			$amountValue=$getCancellationPolicySingle->field3;
  		}else{
  			$amountValueNew=$getCancellationPolicySingle->field3 * $getAmount->total_price;
  			$amountValue=$amountValueNew /100;
  		}
  		$response['message']="";
  	}
  	else if($cancel_type=='no_show' && $apt_type ==3 && $hour < $getCancellationPolicyGroup->field1){
  		if($getCancellationPolicyGroup->field3_type=='Amount'){
  			$amountValue=$getCancellationPolicyGroup->field3;
  		}else{
  			$amountValueNew=$getCancellationPolicyGroup->field3 * $getAmount->total_price;
  			$amountValue=$amountValueNew /100;
  		}
  		$response['message']="";
  	}else{
  		$amountValue=0;
  		$response['message']="Appointment status change succesfully";
  	}
  	$response['amount']=$amountValue;
  	$response['status']=1;
  	$response['customer_card']=$getCard;
  	echo json_encode($response);
  }*/
  public function sendNotificationNew($login_id,$text,$message,$token_no){
		
		$getToken=$this->db->query('select fcm_token from login where login_id="'.$login_id.'"')->row();
		$title1=$text;
		$message1=$message;
		$subtitle1=$text;
		$fcm_responce=$this->sendfcm->sendNotification($title1,$message1, array($getToken->fcm_token),$subtitle1);
		$getStylistId=$this->db->query('select group_concat(a.appointment_id) as appointment_id,group_concat(aps.stylist_id) as stylist_id from appointment as a inner join appointment_service as aps on a.appointment_id=aps.appointment_id where a.token_no="'.$token_no.'"')->row();
		$getLoginId=$this->db->query('select group_concat(l.login_id) as login_id from login as l inner join stylist as s on l.login_id=s.login_id where s.stylist_id IN ("'.$getStylistId->stylist_id.'")')->row();
		$login_id_new=explode(",",$getLoginId->login_id);
		$appointment_id=explode(",",$getStylistId->appointment_id);
		$this->db->query('insert into send_notifications set login_id="'.$login_id.'",notification_type="appointment",subtitle="'.$subtitle1.'",message="'.$message1.'",seen_status=0');
		foreach($login_id_new as $key=> $val){
			$getAppointmentDetail=$this->db->query('select CONCAT(c.firstname,"",c.lastname) as customer_name,date_format(a.date,"%M %d,%Y") as ap_date from appointment as a inner join customer as c on a.customer_id=c.customer_id where a.appointment_id="'.$appointment_id[$key].'"')->row();
			$subtitle="New Appointment";
			$title="Hubwallet";
			$message="Mr ".$getAppointmentDetail->customer_name."&nbsp; has been booked new appointment dated on ".$getAppointmentDetail->ap_date;
			$getFcmToken=$this->db->query('select fcm_token from login where login_id="'.$val.'"')->row();
			$fcm_responce2=$this->sendfcm->sendNotification($title,$message,array($getFcmToken->fcm_token),$subtitle);
			$fcm_responce3=$this->sendfcm1->sendNotification($title,$message,array($getFcmToken->fcm_token),$subtitle);
			$this->db->query('insert into send_notifications set login_id="'.$val.'",notification_type="appointment",subtitle="'.$subtitle.'",message="'.$message.'",seen_status=0');
		}
			
		//echo "<pre>";print_r($fcm_responce);
	}
	public function sendNotificationNewTest(){
		//$login_id,$text,$message
		$getToken=$this->db->query('select fcm_token from login where login_id="2647"')->row();
		$title='New Appointment';
		$message="i am testing";
		$subtitle='New Appointment';
		$fcm_responce=$this->sendfcm->sendNotification($title,$message, array($getToken->fcm_token),$subtitle);
		echo "<pre>";print_r($fcm_responce);
	}
}

?>