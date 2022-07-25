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


class Stylist_new extends CI_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
		$this->load->helper('db');
		//error_reporting(0);
		
    }
	
	function get(){
	$vendor_id = $this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$response['status'] = 0;
		$response['message'] = '';
			
			if($_SERVER['HTTP_HOST']=='localhost'){
			$path = site_url().'/assets/img/stylist/thumb/';
				}else{
					$path = "http://".$_SERVER['HTTP_HOST'].'/salon/assets/img/stylist/thumb/';
				}
		
		
			$query = $this->db->query("select s.login_id,s.stylist_id,st.role_name as title,l.email,CONCAT(s.firstname,' ',s.lastname) as stylist_name, s.phone, s.alternate_phone,s.type, IF(l.is_active=1,'Active','Inactive') as status,l.pin,l.username, DATE_FORMAT(l.created_date,'%M %d %Y') AS registered_on, CONCAT('$path','/',if(s.photo='','noimage.png',s.photo)) as photo from login l INNER JOIN stylist s ON s.login_id=l.login_id LEFT JOIN role st ON st.role_id=s.title_id where l.is_delete='0' and l.vendor_id='".$vendor_id."' ORDER BY s.stylist_id DESC ");
			$permission=$this->db->query('select p.permission,s.permission_id from stylist_permission as s inner join permission as p on p.id=s.permission_id where role_id=3')->result();
			if(!empty($permission)){
				$permission=$permission;
			}else{
				$permission=array();
			}
			$res = $query->result();
			if($res){
				
				$response['status'] = 1;
				$response['result'] = $res;
				$response['permission'] = $permission;
				$response['message'] = 'Data found';
				
			}else{
				$response['status'] = 0;
				$response['message'] = 'No data found';
				$response['permission'] = $permission;
				$response['result'] = array();
			}
			}else{
				$response['status'] = 0;
				$response['message'] = 'Required parameter missing';
			}
		
		echo json_encode($response);
	}
	
	
	function randomPassword() {
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$pass = array(); //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < 8; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass); //turn the array into a string
	}
	
	public function country(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$qry = $this->db->query("select id as country_d, name as country_name from countries order by id asc");
		$result = $qry->result();
		if($result){
			$response['status'] = 1;
			$response['result'] = $result;
		}else{
			$response['status'] = 0;
			$response['message'] = 'No data found';
			
		}
		
		echo json_encode($response);
	}
	
	public function state(){
		
		$country_id = "231";
		
		$response['status'] = 0;
		$response['message'] = '';
		
		
		
		$qry = $this->db->query("select id as state_id, name as state_name from states where country_id='".$country_id."' ");
		$result = $qry->result();
		if($result){
			
			$response['status'] = 1;
			$response['result'] = $result;
			$response['message'] = 'Data found';
		}else{
			$response['status'] = 0;
			$response['message'] = 'No data found';
			$response['result'] = array();
			
		}
		
		
		
		echo json_encode($response);
	}
	
	
	function base64_to_jpeg($base64_string, $output_file) {
		//echo $output_file;die;
    // open the output file for writing
    $ifp = fopen( $output_file, 'wb' ); 

    // split the string on commas
    // $data[ 0 ] == "data:image/png;base64"
    // $data[ 1 ] == <actual base64 string>
    $data = explode( ',', $base64_string );

    // we could add validation here with ensuring count( $data ) > 1
   $fwrite = fwrite( $ifp, base64_decode( $data[ 0 ] ) );

   
    // clean up the file resource
    fclose( $ifp ); 

    return $output_file; 
	}

	public function checkUserExist($vendor_id,$email){
		
			$query = $this->db->query("select l.email from login l where l.role_id='3' and l.vendor_id='".$vendor_id."' and l.email='".$email."' and l.is_delete=0 ");
			if($query->num_rows()>0){
				return 1;
			
			}else{
				return 0;
			}
	}
	
	public function checkNameExist($vendor_id,$firstname,$lastname){
		
			$fullname = $firstname.' '.$lastname;

			$query = $this->db->query("select s.stylist_id from stylist s INNER JOIN login l ON l.login_id=s.login_id where CONCAT(s.firstname,' ',s.lastname)='".$fullname."' and l.vendor_id='".$vendor_id."' and l.is_delete=0 ");
			if($query->num_rows()>0){
				return 1;
			
			}else{
				return 0;
			}
	}
	

	public function checkIfServiceAlreadyExist($service_id,$stylist_id){

		$query = $this->db->query("select count(id) as num from stylist_service where service_id='".$service_id."' and stylist_id='".$stylist_id."' ");
		$num = $query->row()->num;
		return $num;

	}
	
	public function add(){
		
		//echo "hello this is test ";
		//print_r($_FILES['image']['name']);die;



		$vendor_id = $this->input->post('vendor_id');
		$employee_type = $this->input->post('emp_type_id');
		$title_id = $this->input->post('title_id');
		$firstname = addslashes($this->input->post('firstname'));
		$lastname = addslashes($this->input->post('lastname'));
		$email = $this->input->post('email');
		$phone = $this->input->post('phone');
		$alternate_phone = $this->input->post('alternate_phone');
		$state_id = $this->input->post('state_id');
		if($state_id !=''){
		$state_id =$state_id ;
		}else{
			$state_id =0;
		}
		$city = $this->input->post('city');
		$address = $this->input->post('address');
		$hourly_rate= $this->input->post('hourly_rate');
		if($hourly_rate !=''){
		$hourly_rate =$hourly_rate ;
		}else{
			$hourly_rate =0;
		}
		$photo = $this->input->post('photo');
		$start_date=$this->input->post('start_date');
		$end_date=$this->input->post('end_date');
		$note=$this->input->post('note');
		$zipcode=$this->input->post('zipcode');
		$commission_type = $this->input->post('commission_type');
		$commission_amount = $this->input->post('commission');
		
		$pay_rate = $this->input->post('pay_rate');
		$pay_type_id = $this->input->post('pay_type_id');
		$status = $this->input->post('status');
		$inactive_date = $this->input->post('inactive_date');
		$pin = $this->input->post('pin');
		$gallery_image = $this->input->post('gallery_image');
		
		if($status=='inactive' || $status=='Inactive'){
			$status = 0;
			$inactive_date = date('Y-m-d',strtotime($inactive_date));
		}else{
			$status = 1;
			$inactive_date = null;
		}
		//$emp_type_id=$this->input->post('emp_type_id');
		
		
		//echo "<pre>";print_R($services);die;

		// parameters for availibility
		$days = explode(",",$this->input->post("days"));
        $switch =  explode(",",$this->input->post("switch"));
        $from_time =  explode(",",$this->input->post("from_time"));
        $to_time =  explode(",",$this->input->post("to_time"));

		// parameter for availibility end here
        
		
		if(!empty($vendor_id) && !empty($employee_type) && !empty($title_id) && !empty($firstname) && !empty($email) && !empty($phone)){

		$check = $this->checkUserExist($vendor_id,$email);
		$checkName = $this->checkNameExist($vendor_id,$firstname,$lastname);
		$checkPin = $this->checkIfPinAlreadyExist($pin,$vendor_id);

		if($checkName==1){
			$response['status'] = 0;
			$response['message'] = 'Employee already exist';
		}
		elseif($check==1){
			$response['status'] = 0;
			$response['message'] = 'Employee already exist';
		}elseif($checkPin==1){

			$response['status'] = 0;
			$response['message'] = 'Pin already exist';
		
		}else{


		if(!empty($_FILES['photo']['name'])){
			/*$path = '../assets/img/product/thumb/';
			$file = time().'.jpeg';
			$photo = $this->base64_to_jpeg($photo,$path.$file);
			$photo_name = $file;*/
			$photo = $_FILES['photo']['name'];
		$tmp_photo = $_FILES['photo']['tmp_name'];
		$path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/stylist/thumb/';
		$file = time().$photo;
			$photo = move_uploaded_file($tmp_photo,$path.$file);
			$photo_name = $file;
		
		}else{
			$photo_name = 'avtar.png';
		}
		
		
		//$password = $password;
		
		$generateTokenhash = $this->randomPassword();
		//$password = $this->randomPassword();

		$first_four =  substr($firstname,0,4);
		$last_four =  substr($phone,-4);
		$password = $first_four.$last_four;
		
		if($commission_amount==''){
			$commission_type = '';
			$commission_amount = 0;
		}else{
			$commission_type = $commission_type;
			$commission_amount = $commission_amount;
		}
		
		
		$query=$this->db->query("insert into login set email='".trim($email)."',password='".md5($password)."', pin='".$pin."',  role_id='3', confirmkey='".$generateTokenhash."', is_delete='0', created_date='".date("Y-m-d H:i:s")."', is_active='1', vendor_id='".$vendor_id."' ");
		
		if($query){
			$login_id = $this->db->insert_id();
			

			if($start_date==''){
				$start_date = '';
			}else{

				$start_date = date('Y-m-d',strtotime($start_date));
			}
		


		$query2=$this->db->query("insert into stylist set login_id='".$login_id."',email='".trim($email)."', firstname='".$firstname."', lastname='".$lastname."', phone='".$phone."', alternate_phone='".$alternate_phone."', country_id='231', state_id='".$state_id."', city='".$city."', address='".addslashes($address)."', type='".$employee_type."', title_id='".$title_id."',photo='".$photo_name."',note='".$note."',postal_code='".$zipcode."', commission_type='".$commission_type."', commission='".$commission_amount."', pay_rate='".$pay_rate."', pay_type_id='".$pay_type_id."', start_date='".$start_date."',  status='1', inactive_date=NULL ");
		//echo $this->db->last_query();exit;
		
		$stylist_id = $this->db->insert_id();
			}
		
		if($stylist_id){

			
			// Add employee availibility code start here
			$query_num = $this->db->query("select schedule_id as num from schedule where stylist_id='".$stylist_id."'")->num_rows();


			if($query_num==0){
				for($i=0;$i<count($days);$i++){
					
						if($switch[$i]==0){
							$from_time1 = "00:00";
							$to_time1 =  "00:00";
						}else{
							$from_time1 = date('H:i',strtotime($from_time[$i]));
							$to_time1 = date('H:i',strtotime($to_time[$i]));
						}
						$schdule_id=$this->db->query("insert into schedule set stylist_id='".$stylist_id."',days='".trim($days[$i])."',switch='".$switch[$i]."',from_time='". $from_time1."',to_time='".$to_time1."',created_date='". date('Y-m-d h:i:s')."',vendor_id='".$vendor_id."'");
					
				
					}
					
				
			}

			// add employee availibility code end here


			// add employee services code start here

		
			$services = explode(",",$this->input->post('services'));
			$price = explode(",",$this->input->post('price'));
			$duration = explode(",",$this->input->post('duration'));
			$commission = explode(",",$this->input->post('service_commission'));
			$commission_type = explode(",",$this->input->post('service_commission_type'));

		
		//	$services = json_decode($services);
			
			
			if($this->input->post('services')!=''){

			//array_unique($services);	
			for($j=0;$j<count($services);$j++){

			$service_num = $this->checkIfServiceAlreadyExist($services[$j],$stylist_id);
			if($service_num=='0'){

				$query = $this->db->query("insert into stylist_service set stylist_id='".$stylist_id."', service_id='".$services[$j]."', price='".$price[$j]."', duration='".$duration[$j]."',commission='".$commission[$j]."',commission_type='".$commission_type[$j]."' ");
			}
				
			
			}
			}
			// add employee services code end here


			// code for add gallery start here
			
			$path= $_SERVER['DOCUMENT_ROOT'] ."/salon/assets/img/stylist_gallery/".$stylist_id."/";
			
		
				
				$photo = $_FILES['image']['name'];
				$tmp_photo = $_FILES['image']['tmp_name'];

				//print_r($photo);die;
				$image_name=$this->input->post('image_name');

					foreach($photo as $key=>$p)
					{

					//	echo $photo[$p];

					if($p!=''){
				
				$path= $_SERVER['DOCUMENT_ROOT'] ."/assets/img/stylist_gallery/".$stylist_id."/";
				if (!file_exists($path)) {
						mkdir($path, 0777, true);
					}
					//echo $path;die;
					$file = time().$p;
					$photo = move_uploaded_file($tmp_photo[$key],$path.$file);
					$photo_name = $file;

				}else{
					$photo_name = 'avtar.png';
				}

			
				$this->db->query('insert into stylist_gallery set stylist_id="'.$stylist_id.'",image="'.$photo_name.'", image_name="'.$p.'", status=1');

				}
					
			
				
			
			

			// code for add gallery end here



			$response['status'] = 1;
			$response['stylist_id'] = $stylist_id;
			$response['login_id'] = $login_id;
			$response['message'] = 'Employee added successfully';
			
			
                $initial_time = time();
               
				$data['firstname'] = $firstname;
				$data['email'] = $email;
				$data['password'] = $password;
				$data['business_info'] = $this->getSalonDetail($vendor_id);
				$emailTemplate = $this->load->view('employee_registration',$data,TRUE);
				$subject = "Registration on Hubwallet";
               
				$this->load->library('Send_mail');
				$this->send_mail->sendMail($email, $subject, $emailTemplate, $fileName=false, $filePath=false, $cc=false);
				$search1  = array('{Employee First Name}','{Business Name}');
				$employee_name=$firstname." ".$lastname;
				$replace1 = array($employee_name,$data['business_info']->business_name);
				$getDataNew=getImageTemplate($vendor_id,'stylist_registration');
				$getsmsData=str_replace($search1, $replace1, $getDataNew->sms_content);
				test($getsmsData,$phone);
				
		}
		}
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response); 
		

	}

	public function getSalonDetail($vendor_id){

		$query = $this->db->query("select v.vendor_name as business_name, v.phone from vendor v where v.vendor_id='".$vendor_id."' ");
		$result = $query->row();
		return $result;
	}
	
	public function getStylistDetailById($stylist_id,$vendor_id){
		
		$qry = $this->db->query("select s.photo from stylist s where s.stylist_id='".$stylist_id."' ");
		$res = $qry->row();
		return $res;
		
	}
			
	
	public function edit(){
		//echo "<pre>";print_r($_POST);exit;
		$stylist_id = $this->input->post('stylist_id');

		$vendor_id = $this->input->post('vendor_id');
		$employee_type = $this->input->post('emp_type_id');
		$title_id = $this->input->post('title_id');
		$firstname = addslashes($this->input->post('firstname'));
		$lastname = addslashes($this->input->post('lastname'));
		$email = $this->input->post('email');
		$phone = $this->input->post('phone');
		$alternate_phone = $this->input->post('alternate_phone');
		$state_id = $this->input->post('state_id');
		if($state_id !=''){
		$state_id =$state_id ;
		}else{
			$state_id =0;
		}
		$city = $this->input->post('city');
		$address = $this->input->post('address');
		$hourly_rate= $this->input->post('hourly_rate');
		if($hourly_rate !=''){
		$hourly_rate =$hourly_rate ;
		}else{
			$hourly_rate =0;
		}
		$photo = $this->input->post('photo');
		$start_date=$this->input->post('start_date');
		$end_date=$this->input->post('end_date');
		$note=$this->input->post('note');
		$zipcode=$this->input->post('zipcode');
		$commission_type = $this->input->post('commission_type');
		$commission_amount = $this->input->post('commission');
		
		$pay_rate = $this->input->post('pay_rate');
		$pay_type_id = $this->input->post('pay_type_id');
		$status = $this->input->post('status');
		$inactive_date = $this->input->post('inactive_date');
		$pin = $this->input->post('pin');
		$gallery_image = $this->input->post('gallery_image');
		
		
		//$emp_type_id=$this->input->post('emp_type_id');
		
		
		//echo "<pre>";print_R($services);die;

		// parameters for availibility
		$schedule_id = explode(",",$this->input->post("schedule_id"));  // will be available only in case of edit
		$days = explode(",",$this->input->post("days"));
        $switch =  explode(",",$this->input->post("switch"));
        $from_time =  explode(",",$this->input->post("from_time"));
        $to_time =  explode(",",$this->input->post("to_time"));

		// parameter for availibility end here

		// parameters for services
		$emp_service_id = explode(",",$this->input->post('emp_service_id'));
		$services = explode(",",$this->input->post('services'));
		$price = explode(",",$this->input->post('price'));
		$duration = explode(",",$this->input->post('duration'));
		$service_commission = explode(",",$this->input->post('service_commission'));
		$service_commission_type = explode(",",$this->input->post('service_commission_type'));
		// parameters for services end here

		$getLoginId=$this->db->query('select login_id from stylist where stylist_id="'.$stylist_id.'"')->row();
			
		if($status=='inactive' || $status=='Inactive'){
			$status = '0';
			if($inactive_date=='' || $inactive_date==NULL){
				$inactive_date = NULL;
			}else{

				$current_date = date('Y-m-d');
				$inactive_date = date('Y-m-d',strtotime($inactive_date));

				 $getAppointment=$this->db->query('select a.appointment_id from appointment as a inner join appointment_service as b on a.appointment_id=b.appointment_id where b.stylist_id="'.$stylist_id.'" and a.date >="'.$inactive_date.'"')->result();
				if(!empty($getAppointment)){
					$this->sendMailForInactive($vendor_id,$stylist_id);
				} 

				if($current_date>=$inactive_date){
					$is_active = ", is_active=0";
				}else{
					$is_active = "";
				}
			}
			
		}else{
			$status = '1';
			$is_active=", is_active=1";
			$inactive_date = NULL;
		}
		
		//echo "<pre>";print_R($services);die;
		//echo "start_date = ".$start_date;die;
		//echo $stylist_id;die;
		if(!empty($stylist_id)){
			
			$stored_name = $this->checkNameByStylistId($stylist_id);
			$existing_name = $stored_name->firstname.' '.$stored_name->lastname;

			$new_name = $firstname.' '.$lastname;
			if($existing_name!=$new_name){
				
				$checkName = $this->checkNameExist($vendor_id,$firstname,$lastname);
				if($checkName==1){
					
					$response['status'] = 0;
					$response['message'] = 'Employee already exists';
					
					echo json_encode($response);die;
					
				}else{
					$firstname = $firstname;
					$lastname = $lastname;
				}
			}else{
				
				$firstname = $firstname;
				$lastname = $lastname;
			}

			$stored_email = $this->checkEmailByStylistId($stylist_id);
			$existing_email = $stored_email->email;
			//echo $existing_email;die;

			if($existing_email!=$email){

				$checkEmail = $this->checkUserExist($vendor_id,$email);
				if($checkEmail==1){
					$response['status'] = 0;
					$response['message'] = 'Email already exists';
					
					echo json_encode($response);die;
				}else{

					$email = $email;

					
					//$password = $this->randomPassword();
					$first_four =  substr($firstname,0,4);
					$last_four =  substr($phone,-4);
					$password = $first_four.$last_four;
					
		
				$this->db->query("update login set email='".trim($email)."',password='".md5($password)."' where   login_id='".$getLoginId."' ");


				$data['firstname'] = $firstname;
				$data['email'] = $email;
				$data['password'] = $password;
				$data['business_info'] = $this->getSalonDetail($vendor_id);
				$emailTemplate = $this->load->view('employee_registration',$data,TRUE);
				$subject = "Registration on Hubwallet";
               
				$this->load->library('Send_mail');
				$this->send_mail->sendMail($email, $subject, $emailTemplate, $fileName=false, $filePath=false, $cc=false);
				$search1  = array('{Employee First Name}','{Business Name}');
				$employee_name=$firstname." ".$lastname;
				$replace1 = array($employee_name,$data['business_info']->business_name);
				$getDataNew=getImageTemplate($vendor_id,'stylist_registration');
				$getsmsData=str_replace($search1, $replace1, $getDataNew->sms_content);
				test($getsmsData,$phone);


				}
			}else{
				$email = $email;

			}
			

			
		if(!empty($_FILES['photo']['name'])){
			/*$path = '../assets/img/product/thumb/';
			$file = time().'.jpeg';
			$photo = $this->base64_to_jpeg($photo,$path.$file);
			$photo_name = $file;*/
			$photo = $_FILES['photo']['name'];
			$tmp_photo = $_FILES['photo']['tmp_name'];
			$path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/stylist/thumb/';
			$file = time().$photo;
			$photo = move_uploaded_file($tmp_photo,$path.$file);
			$photo_name = $file;
		
		}else{
			$editData = $this->getStylistDetailById($stylist_id,$vendor_id);
			$photo_name = $editData->photo;
		}
		
	
	
		
		
		
		if($commission_amount==''){
			$commission_type = '';
			$commission_amount = 0;
		}else{
			$commission_type = $commission_type;
			$commission_amount = $commission_amount;
		}
		
	
			
	$this->db->query("update login set email='".$email."',  pin='".$pin."' $is_active where login_id='".$getLoginId->login_id."'  ");


			if($start_date==''){
				//echo "state_date empty";
				$start_date = '';
			}else{
				//echo 'start_date not empty';
				$start_date = date('Y-m-d',strtotime($start_date));
			}
			
		
		$update = $this->db->query("update stylist set firstname='".$firstname."', lastname='".$lastname."', phone='".$phone."', alternate_phone='".$alternate_phone."', country_id='231', state_id='".$state_id."',email='".$email."', city='".$city."', address='".addslashes($address)."',  title_id='".$title_id."',photo='".$photo_name."',type='".$emp_type_id."', postal_code='".$zipcode."', note='".$note."', commission_type='".$commission_type."', commission='".$commission_amount."', pay_rate='".$pay_rate."', pay_type_id='".$pay_type_id."', start_date='".$start_date."', type='".$employee_type."', status='".$status."', inactive_date='".$inactive_date."' where stylist_id='".$stylist_id."' ");

		
		if($update){


			// update availability code start here
			for($i=0;$i<count($days);$i++){
					
				if($switch[$i]==0){
					$from_time1 = "00:00";
					$to_time1 =  "00:00";
				}else{
					$from_time1 = date('H:i',strtotime($from_time[$i]));
					$to_time1 = date('H:i',strtotime($to_time[$i]));
				}

				
				$schdule_id=$this->db->query("update schedule set days='".trim($days[$i])."',switch='".$switch[$i]."',from_time='". $from_time1."',to_time='".$to_time1."' where schedule_id='".$schedule_id[$i]."' ");
			
		
			}

			// update availabillity code ends here


			// update service code start here

			for($j=0;$j<count($services);$j++){
		
				if($emp_service_id[$j]=='' && $services[$j]!=''){

					$service_num = $this->checkIfServiceAlreadyExist($services[$j],$stylist_id);

					if($service_num=='0'){

						$query = $this->db->query("insert into stylist_service set stylist_id='".$stylist_id."', service_id='".$services[$j]."', price='".$price[$j]."', duration='".$duration[$j]."',commission='".$service_commission[$j]."',commission_type='".$service_commission_type[$j]."' ");
					}
					
				}else{

					$query = $this->db->query("update stylist_service set service_id='".$services[$j]."', price='".$price[$j]."', duration='".$duration[$j]."',commission='".$service_commission[$j]."',commission_type='".$service_commission_type[$j]."'  where id='".$emp_service_id[$j]."' ");

				}
				
			
			}
			
			// update service code end here



			// code for add gallery start here
			
			$path= $_SERVER['DOCUMENT_ROOT'] ."/salon/assets/img/stylist_gallery/".$stylist_id."/";
			
		
				
				$photo = $_FILES['image']['name'];
				$tmp_photo = $_FILES['image']['tmp_name'];

				$image_name=$this->input->post('image_name');
				$image_id=$this->input->post('image_id');

					foreach($photo as $key=>$p)
					{

					//	echo $photo[$p];

					if($p!=''){


				
				$path= $_SERVER['DOCUMENT_ROOT'] ."/assets/img/stylist_gallery/".$stylist_id."/";
				if (!file_exists($path)) {
						mkdir($path, 0777, true);
					}
					//echo $path;die;
					if($image_id[$key]==''){

						$file = time().$p;
						$photo = move_uploaded_file($tmp_photo[$key],$path.$file);
						$photo_name = $file;

					}else{
						$image_name = $this->db->query("select image_name from stylist_gallery where id='".$image_id[$key]."' ")->row()->image_name;
						$photo_name = $image_name;
					}
					

				}else{
					$photo_name = 'avtar.png';
				}

				if($image_id[$key]==''){


					$this->db->query('insert into stylist_gallery set stylist_id="'.$stylist_id.'",image_name="'.$image_name[$key].'",image="'.$photo_name.'",status=1');
				}else{
					
					$this->db->query('update stylist_gallery set image_name="'.$image_name[$key].'",image="'.$photo_name.'",status=1 where id="'.$image_id[$key].'"  ');
				}
			}
			$response['status'] = 1;
			$response['stylist_id'] = $stylist_id;
			
			//$response['getAppointment']=$getAppointmentData;
			$response['message'] = 'Employee updated successfully';
		}else{
			
			$response['status'] = 0;
			$response['stylist_id'] = $stylist_id;
			$response['message'] = 'Something wend wrong';
		}
		
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response); 
		

	}
	function checkFutureAppointment(){
		$stylist_id=$this->input->post('stylist_id');
		$inactive_date=$this->input->post('inactive_date');
		$getAppointment=$this->db->query('select a.appointment_id from appointment as a inner join appointment_service as b on a.appointment_id=b.appointment_id where b.stylist_id="'.$stylist_id.'" and a.date >="'.$inactive_date.'"')->num_rows();
		if($getAppointment >0){

			$stylist_info = $this->$getStylistInfoById($stylist_id);
			$stylist_name = $stylist_info->firstname.' '.$stylist_info->lastname;
			$response['status'] = 1;
			
			$response['message'] = "$stylist_name has future appointments. Are you sure you want to make $stylist_name inactive?";

			//*EmployeeName* has future appointments. Are you sure you want to make *EmployeeName* inactive?	
		}else{
			$response['status'] = 0;
			$response['message'] = 'No data found';
		}
		echo json_encode($response); 
	}
	public function simpleMail(){
		$to = "vardhanharsh824@gmail.com";
         $subject = "This is subject";
         
         $message = "<b>This is HTML message.</b>";
         $message .= "<h1>This is headline.</h1>";
         
         $header = "From:abc@somedomain.com \r\n";
         $header .= "Cc:afgh@somedomain.com \r\n";
         $header .= "MIME-Version: 1.0\r\n";
         $header .= "Content-type: text/html\r\n";
         
         $retval = mail ($to,$subject,$message,$header);
         
         if( $retval == true ) {
            echo "Message sent successfully...";
         }else {
            print_r(error_get_last());
         }
	}
	public function sendMailForInactive($vendor_id,$stylist_id){
		//$vendor_id,$stylist_id
		/*$vendor_id='10';
		$stylist_id='6';*/
		$getEmail=$this->db->query('select l.email from login as l inner join vendor as v on l.login_id=v.login_id where v.vendor_id="'.$vendor_id.'"')->row();
		$getIncative=$this->db->query('select concat(s.firstname," ",s.lastname) as stylist_name,inactive_date from stylist as s  where s.stylist_id="'.$stylist_id.'"')->row();
		$data['stylist_name']=$getIncative->stylist_name;
		//echo $data['stylist_name'];die;
		$data['getAppointment']=$this->db->query('select date_format(a.date,"%M %d, %Y") as date_new,time_format(b.appointment_time,"%h:%i %p") as appointment_time,time_format(b.appointment_end_time,"%h:%i %p") as appointment_end_time,s.service_name,concat(c.firstname," ",c.lastname) as customer_name from appointment as a inner join appointment_service as b on a.appointment_id=b.appointment_id inner join service as s on b.service_id=s.service_id inner join customer as c on a.customer_id=c.customer_id where b.stylist_id="'.$stylist_id.'" and a.date >="'.$getIncative->inactive_date.'"')->result();
		//echo "<pre>";print_r($data['getAppointment']);exit;
		//$data=array();
		//$getEmail->email
		$receiver_email =$getEmail->email ;
                  /*  $sender_email = 'info@booknpay.com';
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
                   $this->email->subject('Inactive Employee Future Appointment');
                   
                    $emailTemplate = $this->load->view('sendMailforInactive',$data,TRUE);
					
                   $this->email->message($emailTemplate);
                   $this->email->send(); */
                   /*if($this->email->send()){
					   
                   	$response['status'] = 1; 
					$response['message'] = 'Employee edit succesfully'; 
                   }else{
                  	
                   		$response['status'] =1; 
						$response['message'] ='Employee edit succesfully'; 
                   }
                   echo json_encode($response);*/ 

				 
               
				$emailTemplate = $this->load->view('sendMailforInactive',$data,TRUE);
               	$subject = "Inactive Employee Future Appointment";
				   
				
				$this->load->library('Send_mail');
				$this->send_mail->sendMail($receiver_email, $subject, $emailTemplate, $fileName=false, $filePath=false, $cc=false);
				



	}
	
	public function getStylistById(){
		
		$vendor_id = $this->input->post('vendor_id');
		$stylist_id = $this->input->post('stylist_id');
		
		if(!empty($vendor_id) && !empty($stylist_id)){
			//$path = "http://159.203.182.165/salon/assets/img/stylist/thumb";
			$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
				$path = $actual_link.'/assets/img/stylist/thumb';
		
			$qry = $this->db->query("select l.login_id,l.role_id,s.title_id,s.stylist_id,s.type as emp_type_id,l.username,l.email,l.pin, s.firstname, s.lastname, s.nickname, s.phone, s.alternate_phone, c.id as country_id, st.id as state_id, s.city, s.postal_code, s.address, s.type as stylist_type, CONCAT('$path','/',s.photo) as photo,s.hourly_rate,DATE_FORMAT(s.start_date,'%m/%d/%Y') AS start_date,DATE_FORMAT(s.end_date,'%m/%d/%Y') AS end_date,s.note,s.postal_code as zipcode, s.commission_type, s.commission, IF(s.status=1,'Active','Inactive') as status, s.status as is_active, DATE_FORMAT(s.inactive_date,'%c/%d/%Y') AS inactive_date, s.pay_rate, s.pay_type_id from stylist s INNER JOIN login l ON l.login_id=s.login_id  LEFT JOIN countries c ON c.id=s.country_id LEFT JOIN states st ON st.id=s.state_id where l.is_delete='0' and l.vendor_id='".$vendor_id."' and s.stylist_id='".$stylist_id."' ");
			$stylistData = $qry->row();
			
			$permission=$this->db->query('select p.permission,s.permission_id from stylist_permission as s inner join permission as p on p.id=s.permission_id where role_id="'.$stylistData->title_id.'" ')->result();


			
			
			$availability=$this->db->query("select schedule_id, days, switch, (case when from_time='00:00' then from_time else time_format(from_time,'%l:%i %p') end) as from_time, (case when to_time='00:00' then to_time else time_format(to_time,'%l:%i %p') end) as to_time from schedule where vendor_id='".$vendor_id."' and stylist_id='".$stylist_id."' ")->result();

			$employee_service=$this->db->query("select id, service_id, price, duration, commission, commission_type from stylist_service where stylist_id='".$stylist_id."' ")->result();

			
			$path = "https://".$_SERVER['HTTP_HOST'].'/assets/img/stylist_gallery/'.$stylist_id;
			$employee_gallery=$this->db->query("select id, image_name, CONCAT('$path','/',if(image='','noimage.png',image)) as image from stylist_gallery where stylist_id='".$stylist_id."' ")->result();


			


			
			if(!empty($permission)){
				$permission=$permission;
			}else{
				$permission=array();
			}

			if(!empty($availability)){
				$availability=$availability;
			}else{
				$availability=array();
			}

			if(!empty($employee_service)){
				$employee_service=$employee_service;
			}else{
				$employee_service=array();
			}

			if(!empty($employee_gallery)){
				$employee_gallery=$employee_gallery;
			}else{
				$employee_gallery=array();
			}

			if(!empty($stylist_availability)){
				$stylist_availability=$stylist_availability;
			}else{
				$stylist_availability=array();
			}
			
			$response['status'] = 1;
			$response['result'] = $stylistData;
			$response['permission'] = $permission;
			$response['availability'] = $availability;
			$response['employee_service'] = $employee_service;
			$response['employee_gallery'] = $employee_gallery;
			
			/*$qry2 = $this->db->query("select ss.*, s.service_name from stylist_service ss INNER JOIN service s ON s.service_id=ss.service_id where ss.stylist_id='".$stylist_id."'  ");
			$stylistService = $qry2->result();
		
			$response['service'] = $stylistService;
			
			$qry3 = $this->db->query("select schedule_id, days, IF(switch=1,'true','false') as active, from_time, to_time from schedule where stylist_id='".$stylist_id."' and vendor_id='".$vendor_id."' order by schedule_id asc  ");
			$stylistSchedule = $qry3->result();
		
			$response['schedule'] = $stylistSchedule;
			
				$qry4 = $this->db->query("select experience_year, experience_month, work_location, note, services from stylist where stylist_id='".$stylist_id."'  ");
			$stylistBiodata = $qry4->row();
		
			$response['biodata'] = $stylistBiodata;
			*/
			
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response); 
	}
	public function checkIfStylistMobileExist(){
		
		$vendor_id = $this->input->post('vendor_id');
		$phone = $this->input->post('phone');
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
		if($role_id==2){
			$msg = 'Customer already exist';
		}elseif($role_id==3){
			$msg = 'Employee already exists';
		}else{
			$msg = 'User already exist';
		}
		
			$query = $this->db->query("select C.stylist_id from stylist as C inner join login as l on C.login_id=l.login_id where C.phone='".$phone."' and l.vendor_id='".$vendor_id."' ");
			if($query->num_rows()>0){
				
				$response['status'] = 1;
				$response['message'] = 'Employee mobile number already exists';
			
			}else{
				$response['status'] = 0;
				$response['message'] = '';
			}
			
			echo json_encode($response);
	}
	public function delete(){
		
		$vendor_id = $this->input->post('vendor_id');
		$stylist_id = $this->input->post('stylist_id');
		
		if(!empty($vendor_id) && !empty($stylist_id)){
			
			$qry = $this->db->query("select login_id from stylist where stylist_id='".$stylist_id."'");
			$res = $qry->row();
			$login_id = $res->login_id;
			
			if($login_id){
				$update = $this->db->query("update login set is_delete='1' where login_id='".$login_id."' and vendor_id='".$vendor_id."'");
				
				if($update){
					$response['status'] = 1;
					$response['message'] = 'Employee deleted successfully';
					
				}else{
					$response['status'] = 0;
					$response['message'] = 'Employee not deleted';
				}
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	public function markAttendance(){
		//date_default_timezone_set("Asia/Kolkata");
	
		//America/Los_Angeles
		$country =  $this->ip_info("Visitor", "Country"); 
		//United States
		//India
		
	
		if($country=='India'){
			date_default_timezone_set("Asia/Kolkata");
			
		}else{
			date_default_timezone_set("America/Los_Angeles");
			
		}	
	
		
		
		$clock_as = $_POST['clock_as'];
		$current_date = date('Y-m-d');
		$current_time = date('H:i:s');
		$stylist_pin = $_POST['pin'];
		$vendor_id = $_POST['vendor_id'];

		if(!empty($stylist_pin) && !empty($vendor_id) && !empty($clock_as)){

		$query1=$this->db->query("select s.stylist_id from stylist s inner join login l on l.login_id=s.login_id where l.pin='".$stylist_pin."' and l.vendor_id='".$vendor_id."' and l.is_active=1 and l.is_delete=0 ")->row();
		
		if(count($query1)>0){
			if($clock_as=='clockin'){
				$query2 = $this->db->query("select A.attendance_id from attendance as A  where A.attendance_date='".$current_date."' and A.type='1' and A.stylist_id='".$query1->stylist_id."'")->num_rows();
				if($query2 <=0){
					$q3 = $this->db->query("insert into attendance set stylist_id='".$query1->stylist_id."', attendance_date='".$current_date."', attendance_time='".$current_time."', type='1', is_emergency_clockout='0', vendor_id='".$vendor_id."' ");
					if($q3){
						echo json_encode(array('status'=>'1','msg'=>'Employee clocked in.'));
					}else{
					   echo json_encode(array('status'=>'0','msg'=>'Something Wrong'));	
					}
				}else{
						echo json_encode(array('status'=>'0','msg'=>'Employee already clocked in.'));
					}
			}
			if($clock_as=='clockout'){
				$query3 = $this->db->query("select A.attendance_id from attendance as A  where A.attendance_date='".$current_date."' and A.type='1' and A.stylist_id='".$query1->stylist_id."' and A.attendance_out_date IS NULL")->row();
				if(count($query3) >0){
					$q3=$this->db->query('update attendance set attendance_out_date="'.$current_date.'",attendance_out_time="'.$current_time.'" where attendance_id="'.$query3->attendance_id.'"');
					if($q3){
						echo json_encode(array('status'=>'1','msg'=>'Employee clocked out successfully'));
					}else{
					   echo json_encode(array('status'=>'0','msg'=>'Something Wrong'));
					}
				}else{
					echo json_encode(array('status'=>'0','msg'=>'Please Clock in first'));
				}
			}
			if($clock_as=='break_in'){
				$query2 = $this->db->query("select A.attendance_id from attendance_break_leave as A  where A.attendance_date='".$current_date."'  and A.stylist_id='".$query1->stylist_id."' and A.attendence_back_date IS NULL")->num_rows();
				//echo $this->db->last_query();exit;
				if($query2 <=0){
					$q3 = $this->db->query("insert into attendance_break_leave set stylist_id='".$query1->stylist_id."', attendance_date='".$current_date."', attendance_time='".$current_time."',vendor_id='".$vendor_id."' ");
					if($q3){
						echo json_encode(array('status'=>'1','msg'=>'Break clocked in successfully'));
					}else{
					   echo json_encode(array('status'=>'0','msg'=>'Something Wrong'));	
					}
				}else{
						echo json_encode(array('status'=>'0','msg'=>'Clockout last break first'));
					}
			}

			if($clock_as=='break_out'){
				$query3 = $this->db->query("select A.attendance_id from attendance_break_leave as A  where A.attendance_date='".$current_date."' and A.stylist_id='".$query1->stylist_id."' and A.attendence_back_date IS NULL")->row();
				//secho $this->db->last_query();exit;
				if(count($query3) >0){
					$q3=$this->db->query('update attendance_break_leave set attendence_back_date="'.$current_date.'",attendence_back_time="'.$current_time.'" where attendance_id="'.$query3->attendance_id.'"');
					//echo $this->db->last_query();exit;
					if($q3){
						echo json_encode(array('status'=>'1','msg'=>'Break clocked out'));
					}else{
					   echo json_encode(array('status'=>'0','msg'=>'Something Wrong'));
					}
				}else{
					echo json_encode(array('status'=>'0','msg'=>'Please clock in first'));
				}
			}
			if($clock_as=='emergency'){
				$query3 = $this->db->query("select A.attendance_id from attendance as A  where A.attendance_date='".$current_date."' and A.type='1' and A.stylist_id='".$query1->stylist_id."' and A.attendance_out_date IS NULL")->row();
				if(count($query3) >0){
					$q3=$this->db->query('update attendance set attendance_out_date="'.$current_date.'",attendance_out_time="'.$current_time.'",	is_emergency_clockout=1 where attendance_id="'.$query3->attendance_id.'"');
					if($q3){
						echo json_encode(array('status'=>'1','msg'=>'Emergency clocked out successfully'));
					}else{
					   echo json_encode(array('status'=>'0','msg'=>'Something Wrong'));
					}
				}else{
					echo json_encode(array('status'=>'0','msg'=>'Please clock in first'));
				}
			}
		}else{
			echo json_encode(array('status'=>'0','msg'=>'Invalid Pin'));
		}
		
		}else{
			
			echo json_encode(array('status'=>'0','msg'=>'Required parameter missing'));
			
		}


		/*if($clock_as=='clockin'){
			$query1=$this->db->query("select s.stylist_id from stylist s inner join login l on l.login_id=s.login_id where l.pin='".$stylist_pin."' and l.vendor_id='".$vendor_id."' and l.	is_active=1 and l.is_delete=0 ")->row();
			//echo count($query1);exit;
			if(count($query1) >0){
				$query2 = $this->db->query("select A.attendance_id from attendance as A  where A.attendance_date='".$current_date."' and A.type='1' and A.stylist_id='".$query1->stylist_id."'")->num_rows();
				if($query2 <=0){
					$q3 = $this->db->query("insert into attendance set stylist_id='".$query1->stylist_id."', attendance_date='".$current_date."', attendance_time='".$current_time."', type='1', is_emergency_clockout='0' and vendor_id='".$vendor_id."' ");
					if($q3){
						echo json_encode(array('status'=>'1','msg'=>'Employee clocked in.'));
					}else{
					   echo json_encode(array('status'=>'0','msg'=>'Something Wrong'));	
					}
				}else{
					echo json_encode(array('status'=>'0','msg'=>'Employee already clocked in.'));
				}
			}else{
				echo json_encode(array('status'=>'0','msg'=>'No employee associate with this PIN'));
			}
		}
*/
		/*if($clock_as=='clockout'){
			
		}*/


	}
	public function markAttendance_Old(){
	
		$clock_as = $_POST['clock_as'];
		$current_date = date('Y-m-d');
		$current_time = date('H:i');
		$stylist_pin = $_POST['pin'];
		$vendor_id = $_POST['vendor_id'];
		
		if($clock_as=='clockin'){
			
		$query1 = $this->db->query("select A.attendance_id from attendance as A inner join stylist as s on A.stylist_id=s.stylist_id inner join login as l on l.login_id=s.login_id where A.attendance_date='".$current_date."' and A.type='1' and l.vendor_id='".$vendor_id."' and l.pin='".$stylist_pin."'")->num_rows();
		//echo $query1;
		//echo $this->db->last_query();die;
		//echo $this->db->last_query();die;
		//$clockin_num = $query1->row()->clockin_num;
		
			if($query1==0){
				
				$q2 = $this->db->query("select s.stylist_id from stylist s inner join login l on l.login_id=s.login_id where l.pin='".$stylist_pin."' and l.vendor_id='".$vendor_id."' ");
				//echo $this->db->last_query();exit;	
				if($q2->num_rows()>0){
				$r2 = $q2->row();
				$stylist_id = $r2->stylist_id;
				
				$q3 = $this->db->query("insert into attendance set stylist_id='".$stylist_id."', attendance_date='".$current_date."', attendance_time='".$current_time."', type='1', is_emergency_clockout='0' and vendor_id='".$vendor_id."' ");
				
				if($q3){
					echo json_encode(array('status'=>'1','msg'=>'Employee clocked in.'));
				}
				}else{
						echo json_encode(array('status'=>'0','msg'=>'No employee associate with this PIN'));
				}
			}else{
				echo json_encode(array('status'=>'0','msg'=>'Employee already clocked in.'));
			}
		}
		
		if($clock_as=='clockout'){
		
			$query1 = $this->db->query("select A.attendance_id from attendance as A inner join stylist as s on A.stylist_id=s.stylist_id inner join login as l on l.login_id=s.login_id where A.attendance_date='".$current_date."' and A.type='2' and l.vendor_id='".$vendor_id."' and l.pin='".$stylist_pin."'")->num_rows();
			//$clockin_num = $query1->row()->clockin_num;
			if($query1==0){
				
				$q2 = $this->db->query("select s.stylist_id from stylist s inner join login l on l.login_id=s.login_id where l.pin='".$stylist_pin."' and l.vendor_id='".$vendor_id."' ");
				$r2 = $q2->row();
				$stylist_id = $r2->stylist_id;
				
				$q3 = $this->db->query("insert into attendance set stylist_id='".$stylist_id."', attendance_date='".$current_date."', attendance_time='".$current_time."', type='2', is_emergency_clockout='0', vendor_id='".$vendor_id."' ");
				
				if($q3){
					echo json_encode(array('status'=>'1','msg'=>'Employee clocked out.'));
				}
			}else{
				echo json_encode(array('status'=>'0','msg'=>'Employee already clocked out.'));
			}
		}
		
		
	}
	
	public function getStylistInfoById($stylist_id){
		
		$query = $this->db->query("select s.login_id, s.firstname,s.lastname from stylist s where s.stylist_id='".$stylist_id."' ");
		$result = $query->row();
		return $result;
		
	}
	
	public function activeDeactiveStylist(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$stylist_id = $this->input->post('stylist_id');
		$vendor_id = $this->input->post('vendor_id');
		$status = $this->input->post('status'); //status 1=ACTIVE, 0=DEACTIVE
		
		if(!empty($stylist_id) && !empty($vendor_id)){
			
			
			for($i=0;$i<count($stylist_id);$i++){
				$stylistInfo = $this->getStylistInfoById($stylist_id[$i]);
				$login_id = $stylistInfo->login_id;
				$query = $this->db->query("update login set is_active='".$status."' where login_id='".$login_id."' and vendor_id='".$vendor_id."'  ");
			}
			if($query){
				if($status==1){
					$msg = 'Employee activated successfully';
				}
				elseif($status==0){
					$msg = 'Employee deactivated successfully';
				}
				$response['status'] = 1;
				$response['message'] = $msg;
			}
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
	}
	
	public function deleteStylist(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$stylist_id = $this->input->post('stylist_id');
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($stylist_id) && !empty($vendor_id)){
			
			
			for($i=0;$i<count($stylist_id);$i++){
				$stylistInfo = $this->getStylistInfoById($stylist_id[$i]);
				$login_id = $stylistInfo->login_id;
				$query = $this->db->query("update login set is_delete='1' where login_id='".$login_id."' and vendor_id='".$vendor_id."'  ");
			}
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Employee deleted successfully';
			}
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
	}
	
	public function employeeType(){
		$response['status'] = 1;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select emp_type_id, type as employee_type from employee_type where is_active='1' and vendor_id='".$vendor_id."' ");
			$num = $query->num_rows();
			if($num>0){
				$result = $query->result();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}else{
				
				$response['status'] = 0;
				$response['result'] = [];
				$response['message'] = '';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	public function employeeTitle(){
		$response['status'] = 1;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select role_id as title_id,  role_name as title from role where vendor_id='".$vendor_id."'  ");
			$num = $query->num_rows();
			if($num>0){
				$result = $query->result();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}else{
				
				$response['status'] = 0;
				$response['result'] = [];
				$response['message'] = '';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	
	public function employeeServices(){
		$response['status'] = 1;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$stylist_id = $this->input->post('stylist_id');
		$services = explode(",",$this->input->post('services'));
		$price = explode(",",$this->input->post('price'));
		$duration = explode(",",$this->input->post('duration'));
		$commission = explode(",",$this->input->post('commission'));
		$commission_type = explode(",",$this->input->post('commission_type'));

		
		$services = json_decode($services);
		
		
		if(!empty($vendor_id) && !empty($stylist_id)){
			
			for($j=0;$j<count($services);$j++){
			
			$query = $this->db->query("insert into stylist_service set stylist_id='".$stylist_id."', service_id='".$services[$j]."', price='".$price[$j]."', duration='".$duration[$j]."',commission='".$commission[$j]."',commission_type='".$commission_type[$j]."' ");
			}
		
		
			$response['status'] = 1;
			$response['message'] = 'Service added successfully';
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	public function getEmployeeService(){
		$stylist_id=$this->input->post('stylist_id');
		if(!empty($stylist_id)){
			$query=$this->db->query('select A.*,ser.service_name from stylist_service as A inner join service as ser on A.service_id=ser.service_id where A.stylist_id="'.$stylist_id.'" order by id asc')->result();
			if(!empty($query)){
				$response['status'] = 1;
				$response['result'] = $query;
			 $response['message'] = 'Data found';
			}else{
				$response['status'] = 0;
				
			 $response['message'] = 'No data found';
			}
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	public function employeeSchedule(){
		$response['status'] = 1;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$stylist_id = $this->input->post('stylist_id');
		$days = $this->input->post('days');
		
		$days = json_decode($days);
		
		
		
		if(!empty($vendor_id) && !empty($stylist_id)){
			//print_r($days);die;
			$q = $this->db->query("delete from schedule where stylist_id='".$stylist_id."' AND vendor_id='".$vendor_id."' ");
				
			for($i=0;$i<count($days);$i++){
			
				if($days[$i]->active==1){
					$switch = 1;
				}else{
					$switch = 0;
				}
				$this->db->query("insert into schedule set vendor_id='".$vendor_id."', stylist_id='".$stylist_id."', days='".$days[$i]->days."', switch='".$switch."', from_time='".$days[$i]->from."', to_time='".$days[$i]->to."', created_date='".date('Y-m-d h:i:s')."' ");
	
			}
		
		
		
			$response['status'] = 1;
			$response['message'] = 'Schedule added successfully';
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	
	public function biodata(){
		$response['status'] = 1;
		$response['message'] = '';
		//echo "<pre>";print_r($_POST);exit;
		$vendor_id = $this->input->post('vendor_id');
		$stylist_id = $this->input->post('stylist_id');
		$experience_year = $this->input->post('experience_year');
		$experience_month = $this->input->post('experience_month');
		$work_location = $this->input->post('work_location');
		$note = $this->input->post('note');
		$services = $this->input->post('services');
		
		
		if(!empty($stylist_id)){
				$service1=explode(",", $services);
				//echo "<pre>";print_r($service1);exit;
				$service_new = implode(",",$service1);
				//echo $service_new;exit;
				$query = $this->db->query("update stylist set experience_year='".$experience_year."', experience_month='".$experience_month."', work_location='".$work_location."', note='".$note."', services='".$service_new."' where stylist_id='".$stylist_id."' ");
	
			if($query){
		
				$response['status'] = 1;
				$response['service']=$service_new;
				$response['service2']=$service1;
				$response['message'] = 'Schedule saved successfully';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	
	public function pin(){
		$response['status'] = 1;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$stylist_id = $this->input->post('stylist_id');
		$pin = $this->input->post('pin');
		
		
		
		if(!empty($stylist_id) && !empty($pin)){
			
				
				$query = $this->db->query("select login_id from stylist where stylist_id='".$stylist_id."' ");
				if($query->num_rows()>0){
					$login_id = $query->row()->login_id;
					$query2 = $this->db->query("update login set pin='".$pin."' where login_id='".$login_id."'  ");
				}
				
	
			if($query2){
		
				$response['status'] = 1;
				$response['message'] = 'PIN added successfully';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	
	public function getAttendance($filterType,$custom_from,$custom_to,$type,$vendor_id){
		
		if($filterType=='week'){
		 $con="and a.attendance_date >= DATE(NOW()) - INTERVAL 7 DAY";
		}
		else if($filterType=='month'){
			$con="and MONTH(a.attendance_date) = MONTH(CURRENT_DATE())";
		}
		else if($filterType=='year'){
			$con="and YEAR(a.attendance_date) = YEAR(CURRENT_DATE())";
		}
		else if($custom_from !='' && $custom_to){
			$custom_from_new=date('Y-m-d',strtotime($custom_from));
			$custom_to_new=date('Y-m-d',strtotime($custom_to));
			$con="and  date(a.attendance_date) BETWEEN '".$custom_from_new."' and '".$custom_to_new."'";
		}else if($type=='checkout' && $filterType !=''){
			$con="and a.is_emergency_clockout='".$filterType."'";
		}
		else{
			$con='';
		}
		$query = $this->db->query("select a.attendance_id,a.stylist_id,a.attendance_date as dt1,a.attendance_time as tm, st.title_name,concat(s.firstname,' ',s.lastname) as stylist_name,concat(attendance_date,' ',TIME_FORMAT(attendance_time,'%H:%i')) as clockin,concat(attendance_out_date,' ',TIME_FORMAT(attendance_out_time,'%H:%i')) as clockout,
		  (select TIME_FORMAT(attendance_time, '%H:%i')  from attendance_break_leave where type=1 and stylist_id=a.stylist_id order by attendance_id desc limit 1) as breaklev,
		  (select TIME_FORMAT(attendence_back_time, '%H:%i') from attendance_break_leave where type=1 and stylist_id=a.stylist_id order by attendance_id desc limit 1) as breakback,(case when (select attendance_id from attendance where is_emergency_clockout=1 and stylist_id=a.stylist_id) then 'yes' else 'No' END) as emergency_clock,(case when (select f.from_time from schedule as f where stylist_id=a.stylist_id limit 1) is null then 'No Schedule Set' else (select time_format(from_time,'%h:%i %p') from schedule  where stylist_id=a.stylist_id limit 1) end ) as sch_from_time,(case when (select to_time from schedule where stylist_id=a.stylist_id limit 1) is null then 'No Schedule Set' else (select time_format(to_time,'%h:%i %p') from schedule where stylist_id=a.stylist_id limit 1) end ) as sch_to_time from attendance a inner join stylist s on s.stylist_id=a.stylist_id inner join stylist_title st on st.title_id=s.title_id where a.attendance_id IN (SELECT MAX(attendance_id) FROM attendance GROUP BY stylist_id) and a.vendor_id='".$vendor_id."' ".$con." order by clockin desc, clockout desc");
		/*if($filterType==1){
			//echo  $type;
		 echo $this->db->last_query();
	   }*/
		$result = $query->result();
		return $result;
		
	}
	
	public function attendanceList(){
		$filterType='';  
          $custom_to='';
          $custom_from='';
          $type='';
         
        $data = array();
		
		
		$vendor_id = $this->input->post('vendor_id');
		$filter_type = $this->input->post('filterType');
		$date_from = $this->input->post('date_from');
		$date_to = $this->input->post('date_to');
		$is_emergency_clockout = $this->input->post('is_emergency_clockout');
		
		if(!empty($vendor_id)){
		if(@$filter_type!=''){
			$filterType= $filter_type;
                $type= @$is_emergency_clockout; 
		}
            if(@$date_from !=''){
                    $custom_from=$date_to;
            }else{
               $custom_from=''; 
            }
            if(@$date_from!=''){
                    $custom_to=$date_to;
            }else{
               $custom_to=''; 
            }
           
                
               // echo $filterType;
                
                 $data['filterType']=$filterType;
                 $data['date_from']=$custom_from;
                 $data['date_to']=$custom_to;
				 $data['is_emergency_clockout']=$type;

                $response['status'] = 1;
				 $response['attendance'] = $this->getAttendance($filterType,$custom_from,$custom_to,$type,$vendor_id);
       /* } 
        else {
          $filterType='';  
          $custom_to='';
          $custom_from='';
          $type='';
         
        $data = array();
        
       
        //echo '<pre>';print_r($data['stylist_list']);die;
		
		$response['status'] = 1;
		$response['attendance'] = $this->getAttendance($filterType,$custom_from,$custom_to,$type,$vendor_id);
        }*/
		
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
	}
	
	
	public function getAttendanceById($stylist,$filterType,$custom_from,$custom_to){
		//echo "harsh";
		if($filterType=='week'){
		 $con="and a.attendance_date >= DATE(NOW()) - INTERVAL 7 DAY";
		}
		else if($filterType=='month'){
			$con="and MONTH(a.attendance_date) = MONTH(CURRENT_DATE())";
		}
		else if($filterType=='year'){
			$con="and YEAR(a.attendance_date) = YEAR(CURRENT_DATE())";
		}
		else if($custom_from !='' && $custom_to !=''){
			$custom_from_new=date('Y-m-d',strtotime($custom_from));
			$custom_to_new=date('Y-m-d',strtotime($custom_to));
			$con="and  date(a.attendance_date) BETWEEN '".$custom_from."' and '".$custom_to."'";
		}
		else{
			 $con="and a.attendance_date >= DATE(NOW()) - INTERVAL 7 DAY";
		}
		$query_new=$this->db->query("select concat(firstname,' ',lastname) as stylist_name from stylist where stylist_id='".$stylist."'")->row();
		$query = $this->db->query("select a.attendance_id,concat(a.attendance_date,' ',TIME_FORMAT(a.attendance_time,'%H:%i')) as clockin,concat(a.attendance_out_date,' ',TIME_FORMAT(a.attendance_out_time,'%H:%i')) as clockout,CONCAT(
MOD(HOUR(TIMEDIFF(concat(a.attendance_date,' ',TIME_FORMAT(a.attendance_time,'%H:%i')), concat(a.attendance_out_date,' ',TIME_FORMAT(a.attendance_out_time,'%H:%i')))), 24), ' hours ',
MINUTE(TIMEDIFF(concat(a.attendance_date,' ',TIME_FORMAT(a.attendance_time,'%H:%i')), concat(a.attendance_out_date,' ',TIME_FORMAT(a.attendance_out_time,'%H:%i')))), ' minutes') AS total_hours from attendance a inner join stylist s on s.stylist_id=a.stylist_id inner join stylist_title st on st.title_id=s.title_id where  a.stylist_id='".$stylist."' ".$con." and a.type=1 ");
		$result = $query->result();

		/*CONCAT(
MOD(HOUR(TIMEDIFF(concat(a.attendance_date,' ',TIME_FORMAT(a.attendance_time,'%H:%i')), concat(a.attendence_back_date,' ',TIME_FORMAT(a.attendence_back_time,'%H:%i')))), 24), ' hours ',
MINUTE(TIMEDIFF(concat(a.attendance_date,' ',TIME_FORMAT(a.attendance_time,'%H:%i')), concat(a.attendence_back_date,' ',TIME_FORMAT(a.attendence_back_time,'%H:%i')))), ' minutes'))*/
		/*(select sum(CONCAT(

MINUTE(TIMEDIFF(concat(a.attendance_date,' ',TIME_FORMAT(a.attendance_time,'%H:%i')), concat(a.attendence_back_date,' ',TIME_FORMAT(a.attendence_back_time,'%H:%i')))), ' minutes')) from attendance_break_leave where attendance_date=a.attendance_date GROUP by attendance_date ) as t_hour*/
		$query3 = $this->db->query("SELECT a.attendance_date,count(a.attendance_id) as total_break
  FROM attendance_break_leave as a where  a.stylist_id='".$stylist."'  ".$con." and a.type=1 GROUP by a.attendance_date ");
		//echo $this->db->last_query();
		$result3 = $query3->result();

		

		$attend=array('clockin'=>$result,'break_leave'=>$result3 );
		return $attend;
	}
	
	public function getAttendanceBreadUp(){
		
		$stylist = $this->input->post('stylist_id');
		$custom_from = '';
		$custom_to = '';
		$filterType = '';
		
		
		if(@$_POST['filterType1'] !=''){
            if(@$_POST['custom_from'] !=''){
                    $custom_from=$_POST['custom_from'];
            }else{
               $custom_from=''; 
            }
            if(@$_POST['custom_to'] !=''){
                    $custom_to=$_POST['custom_to'];
            }else{
               $custom_to=''; 
            }
       
                $filterType= $_POST['filterType1'];
            }else
            {
                 $filterType='';
            }
               
         $attendance = $this->getAttendanceById($stylist,$filterType,$custom_from,$custom_to);
         if(!empty($attendance)){
         	$response['status'] = 1;
			$response['data'] = $attendance;
		}else{
			$response['status'] = 0;
			$response['message'] = 'No data found';
		}
		 echo json_encode($response);
		 
		   // $html=$this->load->view('getStylistData',$data,true);
          //$newData=json_encode(array('dataAtt'=>$html));
         //echo $newData;
		 
	}
	public function getTotalBreakDateWise(){
		$stylist=$this->input->post('stylist_id');
		$new_date=date('Y-m-d',strtotime($this->input->post('new_date')));
		$query = $this->db->query("select a.attendance_id,a.attendance_date,a.attendance_time,a.attendence_back_time,CONCAT(
MOD(HOUR(TIMEDIFF(concat(a.attendance_date,' ',TIME_FORMAT(a.attendance_time,'%H:%i')), concat(a.attendence_back_date,' ',TIME_FORMAT(a.attendence_back_time,'%H:%i')))), 24), ' hours ',
MINUTE(TIMEDIFF(concat(a.attendance_date,' ',TIME_FORMAT(a.attendance_time,'%H:%i')), concat(a.attendence_back_date,' ',TIME_FORMAT(a.attendence_back_time,'%H:%i')))), ' minutes') AS total_hours from  attendance_break_leave as a inner join stylist s on s.stylist_id=a.stylist_id inner join stylist_title st on st.title_id=s.title_id where  date(a.attendance_date)='".$new_date."' and a.stylist_id='".$stylist."'   and a.type=1 ");
		$result = $query->result();
		if(!empty($result)){
				$response['status'] = 1;
			$response['data'] = $result;
		}else{
			$response['status'] = 0;
			$response['message'] = 'No data found';
		}
			 echo json_encode($response);
	}
	
	public function editEmployeeServices(){
		$response['status'] = 1;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$stylist_id = $this->input->post('stylist_id');
		$id = explode(",",$this->input->post('id'));
		$services = explode(",",$this->input->post('services'));
		$price = explode(",",$this->input->post('price'));
		$duration = explode(",",$this->input->post('duration'));
		$commission = explode(",",$this->input->post('commission'));
		$commission_type = explode(",",$this->input->post('commission_type'));
		$key=$this->input->post('getkey');
		//$services = json_decode($services);
		$service_new1=array_unique($services);
		//echo '<pre>';print_r($service_new1);die;
		if(!empty($vendor_id) && !empty($stylist_id)){
			
			
				for($j=0;$j<count($service_new1);$j++){
				
					if($id[$j]=='0' || $id[$j]=='' || $id[$j]==0 ){
						$query = $this->db->query("insert into stylist_service set stylist_id='".$stylist_id."', service_id='".$service_new1[$j]."', price='".$price[$j]."', duration='".$duration[$j]."',commission='".$commission[$j]."',commission_type='".$commission_type[$j]."' ");
					}else{
						$query = $this->db->query("update stylist_service set stylist_id='".$stylist_id."', service_id='".$service_new1[$j]."', price='".$price[$j]."', duration='".$duration[$j]."',commission='".$commission[$j]."',commission_type='".$commission_type[$j]."' where id='".$id[$j]."' ");
					}
					
					
				}
				
				$response['status'] = 1;
				$response['message'] = 'Service updated successfully';
			
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}	
	
	function add_gallery(){
	//	$path = $_SERVER['DOCUMENT_ROOT'] . '/salon/new/assets/img/stylist/thumb/';
		//$image=$_FILES['image']['name'];
    $photo=$_FILES['image']['name'];
    $stylist_id=$this->input->post('stylist_id');
    $image_name=$this->input->post('image_name');
    $id=$this->input->post('id');
    $type=$this->input->post('type');
    if(!empty($stylist_id)){
    	if($type=='stylist'){
    		$path= $_SERVER['DOCUMENT_ROOT'] ."/salon/assets/img/stylist_gallery/".$stylist_id."/";
			//$path = '../assets/img/stylist_gallery/'.$stylist_id;
    	}else{
    		$path= $_SERVER['DOCUMENT_ROOT'] ."/salon/assets/img/salon_gallery/";
			//$path = '../assets/img/stylist_gallery/';
    	}
     
            // $tmp_photo=$_FILES['image']['tmp_name'];
            /* if (!file_exists($path)) {
                mkdir($path, 0777, true);
                }*/
          /* if(!empty($image)){
			$file = time().$image;
			$photo = move_uploaded_file($tmp_photo,$path.$file);
			$photo_name = $file;

			} */ 
		
		//echo $path;die;

		if($id==''){
		if(!empty($photo)){
			
			/*$file = time().'.jpeg';
			$photo = $this->base64_to_jpeg($photo,$path.$file);
			$photo_name = $file;*/
				$photo = $_FILES['image']['name'];
		$tmp_photo = $_FILES['image']['tmp_name'];
		$path= $_SERVER['DOCUMENT_ROOT'] ."/assets/img/stylist_gallery/".$stylist_id."/";
		 if (!file_exists($path)) {
                mkdir($path, 0777, true);
                }
		//echo $path;die;
		$file = time().$photo;
			$photo = move_uploaded_file($tmp_photo,$path.$file);
			$photo_name = $file;


		
		}else{
			
			$photo_name = 'avtar.png';
		}
		}
		
		
		
		
		if($type=='stylist'){
			if($id==''){
		$insert=$this->db->query('insert into stylist_gallery set stylist_id="'.$stylist_id.'",image_name="'.$image_name.'",image="'.$photo_name.'",status=1');
			}else{
				$insert=$this->db->query('update stylist_gallery set image_name="'.$image_name.'" where id="'.$id.'" ');
			}
		}else{
			$insert=$this->db->query('insert into salon_gallery set name="'.$image_name.'",img="'.$photo_name.'",status=1');
		}
		if($insert){
			$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
			$image_url = $actual_link.'/salon/new/assets/img/stylist/thumb/';
			//$result=$this->db->query("select concat('".$image_url."',photo) as stylist_image from stylist  where stylist_id='".$stylist_id."'")->row();
		$response['status'] = 1;
		$response['message'] = 'Photo added successfully';
		//$response['profile_image']=$result->stylist_image;
		//$response['image_url']=	$image_url;
		}else{
			$response['status'] = 0;
		   $response['message'] = 'Something went wrong!';
		}
	}else{
		$response['status'] = 0;
		$response['message'] = 'Required parameter missing';
	}
echo json_encode($response);
	}
	function removeGallery(){
		$image_id=$this->input->post('image_id');
		if(!empty($image_id)){
			$this->db->query('delete from  stylist_gallery where id="'.$image_id.'"');
			$response['status'] = 1;
		   $response['message'] = 'Photo deleted successfully';
		}else{
			$response['status'] = 0;
		   $response['message'] = 'Something Wrong';
		}
		echo json_encode($response);
	}
	function getStylistGallery(){
		$stylist_id=$this->input->post('stylist_id');
		$type=$this->input->post('type');
		
		if(!empty($type)){
			if($type=='stylist'){
				$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
				$path = $actual_link.'/assets/img/stylist_gallery/'.$stylist_id.'/';
				$result=$this->db->query('select id,image_name, concat("'.$path.'",image) as image from stylist_gallery where stylist_id="'.$stylist_id.'"')->result();
				if(!empty($result)){
						$response['status'] = 1;
				//$response['image_url']=$path;
				$response['result']=$result;
				$response['message'] = 'succ';
				}else{
				$response['status'] = 1;
				//$response['image_url']=$path;
			    $response['message'] = 'No Image Found';		
				}
			}else{
				//echo "hj";die;
				$actual_link1 = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
				$path1 = $actual_link1.'/salon/new/assets/img/salon_gallery/';
				$result1=$this->db->query('select id,name, concat("'.$path1.'",img) as stylist_image from salon_gallery where status=1')->result();
				//echo $this->db->last_query();die;
				if(!empty($result1)){
						$response['status'] = 1;
						//$response['image_url']=$path1;
						$response['result']=$result1;
						$response['message'] = 'succ';
				}else{
						$response['status'] = 1;
				//$response['image_url']=$path;
			    		$response['message'] = 'No Image Found';		
				}
			}
				
			
		}else{
				$response['status'] = 0;
			//$response['result'] = $object;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	
	}
	
	
	public function drawer(){
		
		$response['status'] = 0; 
		$response['message'] = ''; 
		
		$stylist_id = $this->input->post('stylist_id');
		$role_id = $this->input->post('role_id');
		$vendor_id = $this->input->post('vendor_id');
		$amount = $this->input->post('amount');
		$reason = $this->input->post('reason');
		$deposit_withdraw = $this->input->post('deposit_withdraw');
		
		
		if(!empty($vendor_id) && !empty($stylist_id) && !empty($role_id) && !empty($amount) && !empty($deposit_withdraw)){
			
			$query = $this->db->query("insert into drawer set stylist_id='".$stylist_id."', role_id='".$role_id."', amount='".$amount."', reason='".$reason."', deposit_withdraw='".$deposit_withdraw."', vendor_id='".$vendor_id."', created_date='".date('Y-m-d H:i:s')."' ");
			if($query){
				if($deposit_withdraw==1){
					$val = 'Deposit';
				}elseif($deposit_withdraw==2){
					$val = 'Withdraw';
				}
				$response['status'] = 1; 
				$response['message'] = "Cash $val."; 
				
			}else{
				$response['status'] = 0; 
				$response['message'] = 'something went wrong'; 
			}
			
		}else{
			
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing'; 
		}
		
		echo json_encode($response);
	}
	function checkEmailExsistStylist(){
        $email=$this->input->post('email');
        $num_row=$this->db->query('select email from login where email="'.$email.'" and role_id=3')->num_rows();
        if($num_row >0){
                $response['status'] = 1; 
				$response['message'] = "Email already exists"; 
        }else{
            $response['status'] = 0; 
				$response['message'] = "Not exsist"; 
        }
        echo json_encode($response);
    }
	function availability_new(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){

		   $query=$this->db->query('select A.stylist_id,concat(B.firstname," ",B.lastname) as stylist_name,B.phone from schedule as A inner join stylist as B on A.stylist_id=B.stylist_id where vendor_id="'.$vendor_id.'" group by A.stylist_id')->result();

		$dayNameNew=array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday',"Sunday");
		$getData=array();
 					$days=[];
                   $days1=[];
                   $days2=[];
                   $i=0;
 foreach ($query as $key=> $va11){
                        //$va11->stylist_name
 	                    $getData[$key]->stylist_id=$va11->stylist_id;
 						 $getData[$key]->name=$va11->stylist_name;
 						 $getData[$key]->phone=$va11->phone;
                        $getData[$key]->week=$this->db->query('select A.days as dayname,A.switch,time_format(A.from_time,"%h:%i %p") as from_time,time_format(A.to_time,"%h:%i %p") as to_time from schedule as A inner join stylist as B on A.stylist_id=B.stylist_id where A.vendor_id="'.$vendor_id.'" and A.stylist_id="'.$va11->stylist_id.'"')->result();
                       
                        //echo "harsh".$va11->stylist_name;
                            
                     }
                    $result=$getData;
                 	//echo "<pre>";print_r($result);exit;
					//$response['result']=$r;
                    if(!empty($result)){
                    $response['status'] = 1; 
                    $response['result'] =$result ; 
					$response['message'] = 'Data found'; 
					}else{
						$response['status'] = 0; 
						$response['message'] = 'No data found'; 
					}	
                    
	                }else{
	                	$response['status'] = 0; 
				$response['message'] = 'Required parameter missing'; 
	                }
	                echo json_encode($response);
    
}

function stylist_availability(){
	
	$response['status'] = 0; 
	 $response['result'] =[] ; 
	$response['message'] = ''; 
						
		$stylist_id=$this->input->post('stylist_id');
		$vendor_id=$this->input->post('vendor_id');
		//if(!empty($stylist_id)){

		
				if($stylist_id!=''){
                        
						$qa = $this->db->query('select count(A.schedule_id) as num from schedule as A where  A.stylist_id="'.$stylist_id.'"');
						$schedule_num = $qa->row()->num;
						if($schedule_num>0){
                        
							$getData=$this->db->query('select A.schedule_id,B.phone,A.days as dayname,A.switch,A.from_time as new_time,(case when A.from_time="00:00" then A.from_time else time_format(A.from_time,"%l:%i %p") end) as from_time,(case when A.to_time="00:00" then A.to_time else time_format(A.to_time,"%l:%i %p") end) as to_time from schedule as A inner join stylist as B on A.stylist_id=B.stylist_id where  A.stylist_id="'.$stylist_id.'"')->result();
                        $result=$getData;
						}else{
							$getData=$this->db->query('select "" as schedule_id, "" as phone, A.days as dayname,A.switch,A.start_time as new_time,(case when A.switch="0" then "00:00" else time_format(A.start_time,"%l:%i %p") end) as from_time,(case when A.switch="0" then "00:00" else time_format(A.end_time,"%l:%i %p") end) as to_time from business_hour as A  where  A.vendor_id="'.$vendor_id.'"')->result();
							$result=$getData;
						}
				}else{

					/* $checkData=$this->db->query("select days, switch, TIME_FORMAT(start_time,'%l:%i %p') AS start_time, TIME_FORMAT(end_time,'%l:%i %p') AS end_time from business_hour where vendor_id='".$vendor_id."' order by business_hour_id asc ")->result(); */

					$getData=$this->db->query('select "" as schedule_id, "" as phone, A.days as dayname,A.switch,A.start_time as new_time,(case when A.switch="0" then "00:00" else time_format(A.start_time,"%l:%i %p") end) as from_time,(case when A.switch="0" then "00:00" else time_format(A.end_time,"%l:%i %p") end) as to_time from business_hour as A  where  A.vendor_id="'.$vendor_id.'"')->result();
					$result=$getData;
				}
                     
                    if(!empty($result)){
                    $response['status'] = 1; 
                    $response['result'] =$result ; 
					$response['message'] = 'Data found'; 
					}else{
						$response['status'] = 0; 
						 $response['result'] =[] ; 
						$response['message'] = 'No data found'; 
					}
                    
	               /* }else{
	                	$response['status'] = 0; 
	                	$response['result'] = []; 
						$response['message'] = 'Required parameter missing'; 
	                }*/
	                echo json_encode($response);
    
}

function save_availablity(){
      // echo "test";exit;
	    //echo "<pre>";print_r($_POST);exit;
        $stylist_id=$this->input->post("stylist_id");
        $days = explode(",",$this->input->post("days"));
        $switch =  explode(",",$this->input->post("switch"));
        $from_time =  explode(",",$this->input->post("from_time"));
        $to_time =  explode(",",$this->input->post("to_time"));
         $vendor_id =  $this->input->post("vendor_id");
		//echo "<pre>";print_r($days);exit;
		if(!empty($stylist_id)){
		//check if available exist
		$query = $this->db->query("select schedule_id as num from schedule where stylist_id='".$stylist_id."'")->num_rows();

		//$num = $query->row()->num;
		if($query==0){
       for($i=0;$i<count($days);$i++){
        
            /*if(!in_array($i,$switch)){
                @$switch_val = 0;
            }else{
                @$switch_val = 1;
            }*/
            
            //$from_time1 = date('H:i',strtotime($from_time[$i]));
            //$to_time1 = date('H:i',strtotime($to_time[$i]));
            if($switch[$i]==0){
            	$from_time1 = "00:00";
            $to_time1 =  "00:00";
        }else{
        		$from_time1 = date('H:i',strtotime($from_time[$i]));
            $to_time1 = date('H:i',strtotime($to_time[$i]));
        }
			$schdule_id=$this->db->query("insert into schedule set stylist_id='".$stylist_id."',days='".trim($days[$i])."',switch='".$switch[$i]."',from_time='". $from_time1."',to_time='".$to_time1."',created_date='". date('Y-m-d h:i:s')."',vendor_id='".$vendor_id."'");
        
		//	echo $this->db->last_query();
            /*$schdule_id = $this->schedule->insert(array(
                "stylist_id" => $stylist_id,
                "days" => trim($days[$i]),
                "switch" => trim($switch_val),
                "from_time" => trim($from_time1),
                "to_time" => trim($to_time1),
                "created_date" => date('Y-m-d h:i:s'),
                "vendor_id" => $this->session->userdata('vendor_id')
                
            ));*/
          //  echo $this->db->
    //  $j++;
        }
		
        if($schdule_id){
           $response['status'] = 1; 
			$response['message'] = 'Availability added successfully';  
        }else{
            $response['status'] = 0; 
			$response['message'] = 'Something went wrong!';  
        }
		}else{
			$schedule_id = explode(",",$this->input->post("edit_id"));
			
			for($i=0;$i<count($days);$i++){
        
            if($switch[$i]==0){
            	$from_time2 = "00:00";
            $to_time2 =  "00:00";
        }else{
        		$from_time2 = date('H:i',strtotime($from_time[$i]));
            $to_time2 = date('H:i',strtotime($to_time[$i]));
        }
            
			/*$from_time2 = date('H:i',strtotime($from_time[$i]));
            $to_time2 = date('H:i',strtotime($to_time[$i]));*/
           
		  $this->db->query("update schedule set days='".$days[$i]."', switch='".$switch[$i]."', from_time='".$from_time2."', to_time='".$to_time2."', modified_date='".date('Y-m-d h:i:s')."' where schedule_id='".$schedule_id[$i]."' and vendor_id='".$vendor_id."' ");
		 // echo $this->db->last_query();exit;
        }
			
			$response['status'] = 1; 
			$response['message'] = 'Availability updated successfully';  
			 
		}
	}else{
		$response['status'] = 0; 
	    $response['message'] = 'Required parameter missing'; 
	}
	echo json_encode($response);
    } 
	
	
	function deleteStylistService(){
		
		$response['status'] = 0; 
	    $response['message'] = '';

		$stylist_service_id = $this->input->post('stylist_service_id');
		
		if(!empty($stylist_service_id)){
			
			$query = $this->db->query("delete from stylist_service where id='".$stylist_service_id."' ");
			if($query){
				
				$response['status'] = 1; 
				$response['message'] = 'Service deleted successfully';  
				
			}else{
				$response['status'] = 1; 
				$response['message'] = 'Something went wrong';  
			}
		}else{
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing'; 
		}
		echo json_encode($response);
	}

	function getScheduleData(){
			$vendor_id = $this->input->post('vendor_id');
			$start_date = $this->input->post('start_date');
			$end_date = $this->input->post('end_date');
			$type = $this->input->post('type');
		
		if(!empty($vendor_id)){
			$getStylist=$this->db->query("select st.stylist_id as id, concat(st.firstname,' ',st.lastname) as stylist_name from stylist as st inner join login as l on l.login_id=st.login_id  inner join role as r on r.role_id=st.title_id where l.vendor_id='".$vendor_id."' order by st.stylist_id DESC ")->result();

	/*get Weekly Date*/
			 date_default_timezone_set('America/Los_Angeles');
			 if($type==''){
			 	$dayName=$this->db->query('Select (case when value=0 then "sunday" when value=1 then "monday" when value=2 then "tuesday" when value=3 then "wednesday" when value=4 then "thursday" when value=5 then "friday" when value=6 then "saturday" else "monday" end) as week_day from settings where field="schedule_week_start_day" and vendor_id="'.$vendor_id.'"')->row();

    		$monday = strtotime("last"." ".$dayName->week_day);
			$monday = date('w', $monday)==date('w') ? $monday+7*86400 : $monday;
			$sunday = strtotime(date("Y-m-d",$monday)." +6 days");
			$this_week_sd = date("Y-m-d",$monday);    
			$this_week_ed = date("Y-m-d",$sunday);
			
		}else if($type=='next'){
			$getdate=$this->input->post('getdate');
			$start_date = date("Y-m-d",strtotime($getdate."+1 day"));
			$end_date = date("Y-m-d",strtotime($getdate."+7 day"));

			$this_week_sd = $start_date;   
			$this_week_ed =$end_date;
		}else{
			$getdate=$this->input->post('getdate');
			$start_date = date("Y-m-d",strtotime($getdate."-7 day"));
			$end_date = date("Y-m-d",strtotime($getdate."-1 day"));

			$this_week_sd = $start_date;   
			$this_week_ed =$end_date;
		}
    		$response['DateRange'] = $this->getDatesFromRange($this_week_sd, $this_week_ed);
			//echo "Current week range from".$this_week_sd. "to".$this_week_ed;die;
		/*get Weekly Date*/
		foreach ($getStylist as $va11){
                   					/*echo 'select A.stylist_id,A.id,concat(B.firstname," ",B.lastname) as stylist_name,A.start_date as start,concat(time_format(A.start_time,"%h:%i %p")," - ",time_format(A.end_time,"%h:%i %p"))  as end,date_format(A.start_date,"%a") as dayname,TIME(SUM(TIMEDIFF(A.end_time, A.start_time))) AS totalHour,(select time_format(SEC_TO_TIME(SUM(TIME_TO_SEC(attendance_out_time) - TIME_TO_SEC(attendance_time))),"%H:%i")  from attendance where stylist_id="'.$va11->id.'" and  attendance_date="'.$this_week_sd.'" ) as attendance_hour from stylist_schedule as A inner join stylist as B on A.stylist_id=B.stylist_id inner join role as r on r.role_id=B.title_id where A.start_date between "'.$this_week_sd.'" and "'.$this_week_ed.'" and A.stylist_id="'.$va11->id.'" and A.title_new="Schedule" group by A.start_date';die;*/
                   					$getData[$va11->stylist_name]['stylist_id']=$va11->id;
                        $getData[$va11->stylist_name]['schedule']=$this->db->query('select A.stylist_id,A.id,concat(B.firstname," ",B.lastname) as stylist_name,A.start_date as start,concat(time_format(A.start_time,"%h:%i %p")," - ",time_format(A.end_time,"%h:%i %p"))  as end,date_format(A.start_date,"%a") as dayname,TIME(SUM(TIMEDIFF(A.end_time, A.start_time))) AS totalHour,(select time_format(SEC_TO_TIME(SUM(TIME_TO_SEC(attendance_out_time) - TIME_TO_SEC(attendance_time))),"%H:%i")  from attendance where stylist_id="'.$va11->id.'" and  attendance_date="'.$this_week_sd.'" ) as attendance_hour from stylist_schedule as A inner join stylist as B on A.stylist_id=B.stylist_id inner join role as r on r.role_id=B.title_id where A.start_date between "'.$this_week_sd.'" and "'.$this_week_ed.'" and A.stylist_id="'.$va11->id.'" and A.title_new="Schedule" and A.vendor_id="'.$vendor_id.'" group by A.start_date desc')->result();

                        $getData[$va11->stylist_name]['leave_time']=$this->db->query('select A.id,A.start_date as start,group_concat(concat(time_format(A.start_time,"%h:%i %p")," - ",time_format(A.end_time,"%h:%i %p")) SEPARATOR "\n") as leave_time from stylist_schedule as A inner join stylist as B on A.stylist_id=B.stylist_id inner join role as r on r.role_id=B.title_id where A.start_date between "'.$this_week_sd.'" and "'.$this_week_ed.'" and A.stylist_id="'.$va11->id.'" and A.title_new!="Schedule" and A.vendor_id="'.$vendor_id.'" group by A.start_date desc')->result();

                        	$getData[$va11->stylist_name]['attendance']=$this->db->query('select A.attendance_date as start,A.attendance_time as attendance_time_new,group_concat(concat(time_format(A.attendance_time,"%h:%i %p")," - ",time_format(A.attendance_out_time,"%h:%i %p")) SEPARATOR "\n") as attendence_time from attendance as A inner join stylist as B on A.stylist_id=B.stylist_id where A.attendance_date between "'.$this_week_sd.'" and "'.$this_week_ed.'" and A.stylist_id="'.$va11->id.'" and A.vendor_id="'.$vendor_id.'" group by A.attendance_date ORDER BY A.attendance_id desc')->result();
                        //echo "harsh".$va11->stylist_name;
                            
                     }
                     if(!empty($getData)){
                     $response['newData']=$getData;	
                 }else{
                 	$response['newData']=array();
                 }
                    


		}
			else{
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing'; 
		}
		echo json_encode($response);
	}
	function getScheduleDataDay(){

			$vendor_id = $this->input->post('vendor_id');
			$start_date = $this->input->post('start_date');
			$end_date = $this->input->post('end_date');
			$type = $this->input->post('type');
		
		if(!empty($vendor_id)){
			$getStylist=$this->db->query("select st.stylist_id as id, concat(st.firstname,' ',st.lastname) as stylist_name from stylist as st inner join login as l on l.login_id=st.login_id  inner join role as r on r.role_id=st.title_id where l.vendor_id='".$vendor_id."' order by st.stylist_id DESC ")->result();

	/*get Weekly Date*/
			 date_default_timezone_set('America/Los_Angeles');
			 if($type==''){
			 	$dayName=$this->db->query('Select (case when value=0 then "sunday" when value=1 then "monday" when value=2 then "tuesday" when value=3 then "wednesday" when value=4 then "thursday" when value=5 then "friday" when value=6 then "saturday" else "monday" end) as week_day from settings where field="schedule_week_start_day" and vendor_id="'.$vendor_id.'"')->row();

    		/*$monday = strtotime("last"." ".$dayName->week_day);
			$monday = date('w', $monday)==date('w') ? $monday+7*86400 : $monday;
			$sunday = strtotime(date("Y-m-d",$monday)." +0 days");*/
			$this_week_sd = date("Y-m-d");    
			$this_week_ed =date("Y-m-d");
			
		}else if($type=='next'){
			$getdate=$this->input->post('getdate');
			///$start_date = date("Y-m-d",strtotime($getdate."+1 day"));
			//$end_date = date("Y-m-d",strtotime($getdate."+1 day"));

			$this_week_sd = $getdate;   
			$this_week_ed =$getdate;
		}else{
			$getdate=$this->input->post('getdate');
			//$start_date = date("Y-m-d",strtotime($getdate."-1 day"));
		//	$end_date = date("Y-m-d",strtotime($getdate."-1 day"));

			$this_week_sd = $getdate;   
			$this_week_ed =$getdate;
		}
    		$response['DateRange'] = $this->getDatesFromRange($this_week_sd, $this_week_ed);
			//echo "Current week range from".$this_week_sd. "to".$this_week_ed;die;
		/*get Weekly Date*/
		foreach ($getStylist as $va11){
                   
                        $getData[$va11->stylist_name]['schedule']=$this->db->query('select A.stylist_id,A.id,concat(B.firstname," ",B.lastname) as stylist_name,A.start_date as start,concat(time_format(A.start_time,"%h:%i %p")," - ",time_format(A.end_time,"%h:%i %p")) as end,date_format(A.start_date,"%a") as dayname,TIME(SUM(TIMEDIFF(A.end_time, A.start_time))) AS totalHour,(select time_format(SEC_TO_TIME(SUM(TIME_TO_SEC(attendance_out_time) - TIME_TO_SEC(attendance_time))),"%H:%i")  from attendance where stylist_id="'.$va11->id.'" and  attendance_date="'.$this_week_sd.'" ) as attendance_hour from stylist_schedule as A inner join stylist as B on A.stylist_id=B.stylist_id inner join role as r on r.role_id=B.title_id where A.start_date between "'.$this_week_sd.'" and "'.$this_week_ed.'" and A.stylist_id="'.$va11->id.'" and A.title_new="Schedule" and A.vendor_id="'.$vendor_id.'" group by A.start_date ')->result();

                        $getData[$va11->stylist_name]['leave_time']=$this->db->query('select A.id,A.start_date as start,group_concat(concat(time_format(A.start_time,"%h:%i %p")," - ",time_format(A.end_time,"%h:%i %p")) SEPARATOR "\n") as leave_time from stylist_schedule as A inner join stylist as B on A.stylist_id=B.stylist_id inner join role as r on r.role_id=B.title_id where A.start_date between "'.$this_week_sd.'" and "'.$this_week_ed.'" and A.stylist_id="'.$va11->id.'" and A.title_new!="Schedule" and A.vendor_id="'.$vendor_id.'" group by A.start_date')->result();

                        	$getData[$va11->stylist_name]['attendance']=$this->db->query('select A.attendance_date as start,A.attendance_time as attendance_time_new,group_concat(concat(time_format(A.attendance_time,"%h:%i %p")," - ",time_format(A.attendance_out_time,"%h:%i %p")) SEPARATOR "\n") as attendence_time from attendance as A inner join stylist as B on A.stylist_id=B.stylist_id where A.attendance_date between "'.$this_week_sd.'" and "'.$this_week_ed.'" and A.stylist_id="'.$va11->id.'" and A.vendor_id="'.$vendor_id.'" group by A.attendance_date ORDER BY A.attendance_id desc')->result();
                        //echo "harsh".$va11->stylist_name;
                            
                     }
                     if(!empty($getData)){
                     $response['newData']=$getData;	
                 }else{
                 	$response['newData']=array();
                 }
                    


		}
			else{
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing'; 
		}
		echo json_encode($response);
	
	}
	function getAttendenceData(){
			$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			$getStylist=$this->db->query("select st.stylist_id as id, concat(st.firstname,' ',st.lastname) as stylist_name from stylist as st inner join login as l on l.login_id=st.login_id where l.vendor_id='".$vendor_id."'   ")->result();

	/*get Weekly Date*/
			 date_default_timezone_set('America/Los_Angeles');
    		$dayName=$this->db->query('Select (case when value=0 then "sunday" when value=1 then "monday" when value=2 then "tuesday" when value=3 then "wednesday" when value=4 then "thursday" when value=5 then "friday" when value=6 then "saturday" else "monday" end) as week_day from settings where field="schedule_week_start_day" and vendor_id="'.$vendor_id.'"')->row();
    		$monday = strtotime("last"." ".$dayName->week_day);
			$monday = date('w', $monday)==date('w') ? $monday+7*86400 : $monday;
			$sunday = strtotime(date("Y-m-d",$monday)." +6 days");
			$this_week_sd = date("Y-m-d",$monday);    
			$this_week_ed = date("Y-m-d",$sunday);
			$response['DateRange'] = $this->getDatesFromRange($this_week_sd, $this_week_ed);
			//echo "Current week range from".$this_week_sd. "to".$this_week_ed;die;
		/*get Weekly Date*/
		foreach ($getStylist as $va11){
                   
                        $getData[$va11->stylist_name]=$this->db->query('select A.attendance_id,A.stylist_id,concat(B.firstname," ",B.lastname) as stylist_name,A.attendance_date as start,group_concat(concat(time_format(A.attendance_time,"%h:%i %p")," - ",time_format(A.attendance_out_time,"%h:%i %p")) SEPARATOR "\n") as end,date_format(A.attendance_date,"%a") as dayname,TIME(SUM(TIMEDIFF(A.	attendance_out_time, A.attendance_time))) AS totalHour from attendance as A inner join stylist as B on A.stylist_id=B.stylist_id where A.attendance_date between "'.$this_week_sd.'" and "'.$this_week_ed.'" and A.stylist_id="'.$va11->id.'" group by A.attendance_date ORDER BY A.attendance_id desc')->result();

                        
                        //echo "harsh".$va11->stylist_name;
                            
                     }
                     if(!empty($getData)){

                    $response['newData']=$getData;
                }else{
                	  $response['newData']=array();
                }



		}
			else{
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing'; 
		}
		echo json_encode($response);
	}
	
public function AddStylistSchedule(){
      //  echo "<pre>";print_r($_POST);exit;
	$vendor_id=$_POST['vendor_id'];
	if(!empty($vendor_id)){
         $checkradio=$this->input->post('checkradio');
         if($checkradio=='Leave'){
            $color='#800000';
            $title_new=$this->input->post('leave_reson');
         }else{
            $color='#337ab7';
            $title_new='Schedule';
         }
        /*$schedule_date=explode(",",$this->input->post('schedule_date']);
        $schedule_end_date==explode(",",$this->input->post('schedule_end_date']);
        $schedule_time==explode(",",$this->input->post('schedule_time']);
        $schedule_end_time==explode(",",$this->input->post('schedule_end_time']);*/
       $schedule_date=$this->input->post('schedule_date');
        $schedule_end_date=$this->input->post('schedule_end_date');
        $schedule_time=$this->input->post('schedule_time');
        $schedule_end_time=$this->input->post('schedule_end_time');
        //
        $stylist=$this->input->post('stylist_id');
        $DateRange= $this->getDatesFromRange($schedule_date, $schedule_end_date);
        //echo "<pre>";print_r($DateRange);exit;
        
        $getData=array();

        foreach($DateRange as $key=>$val){
        	$day = date('l', strtotime($val));
        	$getData[]=$day;
        	$query_new=$this->db->query('select schedule_id from schedule where days="'.$day.'" and switch=1 and stylist_id="'.$stylist.'"')->num_rows();
        	if($query_new >0){
        		$this->db->query('delete from stylist_schedule where stylist_id="'.$stylist.'" and start_date="'.$val.'" and title_new="Schedule"');
        		 $query=$this->db->query("insert into stylist_schedule set stylist_id='".$stylist."',start_date='".$val."',end_date='".$val."',start_time='".$schedule_time."',end_time='".$schedule_end_time."',title_new='".$title_new."',color_schedule ='".$color."',vendor_id='".$vendor_id."',status=1");
        	}
        	
        
     }
     $data2=array();
     $data1=$this->db->query('select days from schedule where switch !=1 and stylist_id="'.$stylist.'"')->result();
       
    foreach($data1 as $v1){
    	$data2[]=$v1->days;
    }
    $daysData = array_intersect($getData, $data2);
    $getDays=implode(",",$daysData);

    
         
        if(!empty($daysData)){
        	 $response['status'] = 1; 
        	 $response['message'] = 'Schedule added successfully except these days "'.$getDays.'" because employee not available on these days';
        }
        else if($query){
          	 $response['status'] = 1; 
			$response['message'] = 'Schedule added successfully'; 
        }else{
            $response['status'] = 0; 
			$response['message'] = 'Something went wrong'; 
        }
        }
			else{
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing'; 
		}
		echo json_encode($response);
    }
    function updateSchedule(){
    $row_id=	$_POST['row_id'];
    if(!empty($row_id)){
   $schedule_end_time_new=date("H:i", strtotime($_POST['schedule_end_time']));
             $schedule_start_time_new=date("H:i", strtotime($_POST['schedule_time']));
  if($_POST['checkradio']=='Schedule'){
  $query=$this->db->query('update stylist_schedule set start_time="'.$schedule_start_time_new.'",end_time="'.$schedule_end_time_new.'",color_schedule="#337ab7",title_new="Schedule" where id="'.$_POST['row_id'].'"');
}else{
   $query=$this->db->query('update stylist_schedule set start_time="'.$schedule_start_time_new.'",end_time="'.$schedule_end_time_new.'",title_new="'.$_POST['leave_reson'].'",color_schedule="#800000" where id="'.$row_id.'"');
}
if($query){
  			$response['status'] = 1; 
			$response['message'] = 'Schedule updated successfully'; 
}else{
 			$response['status'] = 0; 
			$response['message'] = 'Something went wrong'; 
}
}
	else{
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing'; 
	}
		echo json_encode($response);
}
public function CopySchedule(){
        //    echo "<pre>";print_r($_POST);
 	$vendor_id=$_POST['vendor_id'];
	if(!empty($vendor_id)){
            $copy_dt=$_POST['copy_dt'];
            $newDt1=explode("to", $copy_dt);
            $paste_dt=$_POST['paste_dt'];
            $newDt2=explode("to", $paste_dt);
            if(!empty(@$_POST['overwrite'])){
                $overwrite=$_POST['overwrite'];
            }else{
               $overwrite=0;
            }
            $d1=explode(",",$newDt1[0]);
            $d2=explode(",",$newDt1[1]);
            $d3=explode(",",$newDt2[0]);
            $d4=explode(",",$newDt2[1]);
            $new1=explode("-",$d1[1]);
            $new2=explode("-",$d2[1]);
            $new3=explode("-",$d3[1]);
            $new4=explode("-",$d4[1]);
            //echo "<pre>";print_r($new1);
            //print_r($new1);
             $dat1=str_replace(' ','',$new1[2]."-".$new1[0]."-".$new1[1]);
             $dat2=str_replace(' ','',$new2[2]."-".$new2[0]."-".$new2[1]);
             $dat3=str_replace(' ','',$new3[2]."-".$new3[0]."-".$new3[1]);
             $dat4=str_replace(' ','',$new4[2]."-".$new4[0]."-".$new4[1]);
              /*echo $dat1."<br>".$dat2."<br>".$dat3."<br>".$dat4;
              exit;*/
             //exit;
             //echo $newDate;exit;  
            //echo $overwrite;die;
            $newArray=array();
           // echo "<pre>";print_r($newDt1);exit;
            /*$week_copy_from=$_POST['week_copy_from'];
            $week_copy_to=$_POST['week_copy_to'];
            $overwrite=$_POST['overwrite'];*/
             $dateRange = $this->getDatesFromRange($dat1, $dat2);
             //echo "<pre>";print_r($dateRange);exit;
            $dataQuery=$this->db->query('SELECT stylist_id,date_format(start_date,"%Y-%m-%d") as st_date,date_format(start_date,"%a") as day_name,start_time,end_time FROM  stylist_schedule WHERE start_date between "'.$dat1.'" and "'.$dat2.'" and vendor_id="'.$vendor_id.'"  ORDER BY id asc')->result();
            //echo $this->db->last_query();exit;
            //echo "<pre>";print_r($dataQuery);exit;
            //echo $this->db->last_query();exit;
            if(!empty($dataQuery)){
            foreach($dataQuery as $vl){
               $newArray[$vl->st_date][]=$vl;

            }
            
             $finalArr=array();
             foreach($dateRange as $vl){
               $finalArr[]=($newArray[$vl]) ? $newArray[$vl] : array();

            }
            $dateRange2 = $this->getDatesFromRange($dat3, $dat4);
           // echo "<pre>";print_r($dateRange2);exit;
            foreach ($dateRange2 as $key => $schedule) {
                if($overwrite==1){
                            
                            $this->db->query('delete from stylist_schedule where date(start_date)="'.$schedule.'" and vendor_id="'.$vendor_id.'"');
                        }
                        
                 if(count($finalArr[$key])>0){
                    foreach ($finalArr[$key] as $key => $copySchedule) {
                 			//echo "<pre>";print_r($copySchedule);
                    	//echo 'insert into  stylist_schedule set stylist_id="'.$copySchedule->stylist_id.'",start_date="'.$schedule.'",end_date="'.$schedule.'",start_time="'.$copySchedule->start_time.'",end_time="'.$copySchedule->end_time.'",vendor_id="'.$vendor_id.'",title_new="Schedule",color_schedule="#337ab7",status=1';
        			 $query= $this->db->query('insert into  stylist_schedule set stylist_id="'.$copySchedule->stylist_id.'",start_date="'.$schedule.'",end_date="'.$schedule.'",start_time="'.$copySchedule->start_time.'",end_time="'.$copySchedule->end_time.'",vendor_id="'.$vendor_id.'",title_new="Schedule",color_schedule="#337ab7",status=1');
                    }
                    
                  
                 }
                  
                 
            }

          	$response['status'] = 1; 
			$response['message'] = 'Schedule copied successfully'; 
      }else{
        $response['status'] = 0; 
			$response['message'] = 'No data found'; 
      }

      }
	else{
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing'; 
	}
		echo json_encode($response);
    }


  function EditStylistAttendence(){
    $stylist_id=$_POST['stylist_id'];
    $vendor_id=$_POST['vendor_id'];
    $attendance_id=$_POST['attendance_id'];
if(!empty($stylist_id)){
    $clock_in_date=$_POST['clock_in_date'];
    $clock_in_date_new=date("Y-m-d", strtotime($clock_in_date));
     $clock_in_time=date("H:i", strtotime($_POST['clock_in_time']));
     $clock_out_date=$_POST['clock_out_date'];
      $clock_out_date_new=date("Y-m-d", strtotime($clock_out_date));
     $clock_out_time=date("H:i", strtotime($_POST['clock_out_time']));
     /*$this->db->query('delete from attendance where stylist_id="'.$stylist_id.'" and attendance_date="'.$clock_in_date_new.'"');
    $query=$this->db->query('insert into attendance set stylist_id="'.$stylist_id.'",attendance_date="'.$clock_in_date_new.'",attendance_time="'.$clock_in_time.'", attendance_out_date="'.$clock_in_date.'",attendance_out_time="'.$clock_out_time.'", color="#006AFF",vendor_id="'.$vendor_id.'",type=0');*/
    $query=$this->db->query('update attendance set attendance_time="'.$clock_in_time.'",attendance_out_time="'.$clock_out_time.'" where attendance_id="'.$attendance_id.'" ');
   // $break_start_time=$_POST['break_start_time'];
   
    /*if(!empty($break_start_time)){*/
        
        /*foreach ($break_start_time as $key => $value) {
            $break_start_time_new=date("H:i", strtotime($value));
        $break_out_time_new=date("H:i", strtotime($_POST['break_end_time'][$key]));
             $this->db->query('insert into attendance set stylist_id="'.$stylist_id.'",attendance_date="'.$clock_in_date_new.'",attendance_time="'.$break_start_time_new.'", attendance_out_date="'.$clock_in_date_new.'",attendance_out_time="'.$break_out_time_new.'",color="#800000",type=1,vendor_id="'.$this->session->userdata('vendor_id').'"');
            # code...
        }*/
        if($query){
               $response['status'] = 1; 
			$response['message'] = 'Attendance updated succesfully'; 
        }else{
        	$response['status'] = 0; 
			$response['message'] = 'Something went wrong'; 
        }
    /*}*/
    }
	else{
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing'; 
	}
		echo json_encode($response);
}
	
/*function NextDate($vendor_id){
	//$vendor_id=57;
	$dayName=$this->db->query('Select (case when value=0 then "sunday" when value=1 then "monday" when value=2 then "tuesday" when value=3 then "wednesday" when value=4 then "thursday" when value=5 then "friday" when value=6 then "saturday" else "monday" end) as week_day from settings where field="schedule_week_start_day" and vendor_id="'.$vendor_id.'"')->row();

    		$monday = strtotime("last"." ".$dayName->week_day);
			$monday = date('w', $monday)==date('w') ? $monday+7*86400 : $monday;
			$sunday = strtotime(date("Y-m-d",$monday)." +6 days");
			$this_week_sd = date("Y-m-d",$monday);    
			$this_week_ed = date("Y-m-d",$sunday);
			$start_date=date("Y-m-d",strtotime($this_week_ed."+1 day"));
			$end_date = date("Y-m-d",strtotime($this_week_ed."+7 day"));
			$DateRange= $this->getDatesFromRange($start_date, $end_date );
			//echo "<pre>";print_r($DateRange);exit;
			return $DateRange;
			

}
function PreviousDate($vendor_id){
	$vendor_id=57;
	$dayName=$this->db->query('Select (case when value=0 then "sunday" when value=1 then "monday" when value=2 then "tuesday" when value=3 then "wednesday" when value=4 then "thursday" when value=5 then "friday" when value=6 then "saturday" else "monday" end) as week_day from settings where field="schedule_week_start_day" and vendor_id="'.$vendor_id.'"')->row();

    		$monday = strtotime("last"." ".$dayName->week_day);
			$monday = date('w', $monday)==date('w') ? $monday+7*86400 : $monday;
			$sunday = strtotime(date("Y-m-d",$monday)." +6 days");
			$this_week_sd = date("Y-m-d",$monday);    
			$this_week_ed = date("Y-m-d",$sunday);
			$start_date=date("Y-m-d",strtotime($this_week_sd."-7 day"));
			$end_date = date("Y-m-d",strtotime($this_week_sd."-1 day"));
			$DateRange= $this->getDatesFromRange($start_date, $end_date);
			//echo $this_week_sd."-".$end_date ;exit;
			//echo "<pre>";print_r($DateRange);
			return $DateRange;
			

}
*/
function getDatesFromRange($start, $end, $format = 'Y-m-d') {
      
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



public function checkIfPinAlreadyExist($login_pin,$vendor_id){
	
	$query = $this->db->query("select login_id from login where pin='".$login_pin."' and vendor_id='".$vendor_id."' ");
	$num = $query->num_rows();
	return $num;
		
}




public function save_pin(){
      //  print_r($_POST);exit;
	$login_id=$this->input->post('login_id');
	$login_pin=$this->input->post('login_pin');
	
	if(!empty($login_id) && !empty($login_pin)){
		
		$check_pin = $this->checkIfPinAlreadyExist($login_pin,$vendor_id);
	
		if($check_pin==0){
		$query=$this->db->query('update login set pin="'.$login_pin.'" where login_id="'.$login_id.'"');
            //echo $this->db->last_query();exit;
            if($query){
              $response['status'] = 1; 
			$response['message'] = 'Pin updated successfully'; 
            }else{
               $response['status'] = 0; 
			$response['message'] = 'Something went wrong'; 
            }
		}else{
			 $response['status'] = 0; 
			 $response['message'] = 'PIN already exists, Please choose another pin.'; 
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

function testmail(){
	$receiver_email = 'vardhanharsh824@gmail.com';
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
                $data=array();
                $message=$this->load->view('account_confirmation',$data,true);
                $this->load->library('email');
                $this->email->initialize($config);
                $this->email->from('info@booknpay.com', 'BookNPay');
                $this->email->to($receiver_email);
                $this->email->subject('Registration on BookNPay');

                $this->email->message($message);
                $this->email->send();
}


public function checkNameByStylistId($stylist_id){
		
		$query = $this->db->query("select stylist_id,firstname, lastname from stylist where stylist_id='".$stylist_id."'  ");
		$res = $query->row();
		//$firstname = $res->firstname;
		return $res;
	}

	

	public function checkEmailByStylistId($stylist_id){
		
		$query = $this->db->query("select s.stylist_id,l.email from stylist s inner join login l on l.login_id=s.login_id where s.stylist_id='".$stylist_id."'  ");
		$res = $query->row();
		//$firstname = $res->firstname;
		return $res;
	}
	
	
	
	public function payType(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		$query = $this->db->query("select * from pay_type order by pay_type_id asc");
		$result = $query->result();
		
		$response['status'] = 1;
		$response['result'] = $result;
		$response['message'] = '';
		
		echo json_encode($response);
	}

	function checkAvaliblity(){
		$check_date=$this->input->post('check_date');
		$stylist_id=$this->input->post('stylist_id');
		$dayname=date('l', strtotime($check_date));
		if(!empty($check_date) && !empty($stylist_id)){
			$checkData=$this->db->query('select "00:00" as from_time,"23:55" as to_time from schedule where stylist_id="'.$stylist_id.'" and days="'.$dayname.'" and switch=1')->row();

			if(!empty($checkData)){
				$response['status'] = 1;
				$response['result'] = $checkData;
				$response['message'] = 'Data found';
			}else{
				$response['status'] = 0;
				$response['result'] = (object)[];
				$response['message'] = 'Employee is not available for this date';	
			}
		}else{
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing'; 
		
		
		}
		echo json_encode($response);
	}

	function checkIfNameAlreadyExist(){
		$firstname=$this->input->post('firstname');
		$lastname=$this->input->post('lastname');
		$vendor_id=$this->input->post('vendor_id');
	
		if(!empty($vendor_id)){
			
			$name = $firstname.' '.$lastname;
			$checkData=$this->db->query("select count(stylist_id) as num from stylist where CONCAT(firstname,' ',lastname)='".$name."' and vendor_id='".$vendor_id."' ")->row();
			if($heckData->num==0){

				$response['status'] = 1;
				$response['message'] = 'Employee already exists';

			}
		}else{
			$response['status'] = 0; 
			$response['message'] = 'Required parameter missing'; 
		
		
		}
		echo json_encode($response);
	}

}

