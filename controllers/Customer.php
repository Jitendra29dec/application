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


class Customer extends CI_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
		$this->load->helper('db');
		//error_reporting(0);
    }
	
	function get(){
		
		
		$vendor_id = $this->input->post('vendor_id');
		
		$response['status'] = 0;
		$response['message'] = '';
		
		
		
		if($_SERVER['HTTP_HOST']=='localhost'){
		
			$path = site_url().'/assets/img/customer/thumb/';
		
		}else{
			$path = "http://".$_SERVER['HTTP_HOST'].'/salon/assets/img/customer/thumb/';;
		}
			
		$query = $this->db->query("select c.customer_id, l.email, CONCAT(c.firstname,' ', c.lastname) AS customer_name, c.mobile_phone, CONCAT('$path','/',if(c.photo='','avtar.png',c.photo)) as photo, IF(l.is_active=1,'Active','Deactive') as status, DATE_FORMAT(l.created_date,'%M %d %Y') AS registered_on, (select DATE_FORMAT(o.created_date,'%m/%d/%Y') from orders o where o.customer_id=c.customer_id order by o.created_date desc limit 1) as last_visit, c.is_walkin from login l INNER JOIN customer c ON c.login_id=l.login_id where l.role_id='2' and l.is_delete='0' and l.vendor_id='".$vendor_id."' order by c.customer_id desc");
		
		
		
		$res = $query->result();
		if($res){
				
			$response['status'] = 1;
			$response['result'] = $res;
			$response['message'] = 'Data found';
			
		}else{
			$response['status'] = 0;
			$response['message'] = 'No data found';
			$response['result'] = array();
		}
		
		echo json_encode($response);

	}
	function getCustomerIou(){
		$customer_id=$this->input->post('customer_id');
		if(!empty($customer_id)){
			$result=$this->db->query('select sum(iou_amount) as total_iou from customer_iou_amount where customer_id="'.$customer_id.'" and status=0')->row();
			if(!empty($result)){
				$response['status']=1;
				$response['result']=$result;
				$response['msg']="Iou data found";
			}else{
				$response['status']=0;
				$response['msg']="No iou data found";

			}
		}else{
			$response['status']=0;
				$response['msg']="No iou data found";
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


	
	public function add(){
	
		$vendor_id = $this->input->post('vendor_id');
		$firstname = addslashes($this->input->post('firstname'));
   		$lastname = addslashes($this->input->post('lastname'));
   		
		$email = $this->input->post('email');
		$phone = $this->input->post('mobile');
		
		$address = $this->input->post('address');
		$zipcode = $this->input->post('zipcode');
		$city = $this->input->post('city');
		$birthday = $this->input->post('birthday');
		$notes = $this->input->post('notes');
		//$photo = $this->input->post('photo'); 
		
		$cc_email = $this->input->post('cc_email'); 
		$gender = $this->input->post('gender'); 
		$country = "231"; 
		$state_id = $this->input->post('state_id'); 
		$emergency_name = $this->input->post('emergency_name'); 
		$emergency_relation = $this->input->post('emergency_relation'); 
		$emergency_contact = $this->input->post('emergency_contact'); 
		
		$anniversary = $this->input->post('anniversary'); 
		$occupation = $this->input->post('occupation'); 
		$refered_by = $this->input->post('refered_by'); 
		$iou_limit = $this->input->post('iou_limit'); 
		$email_alert = $this->input->post('email_alert');
		$sms_alert = $this->input->post('sms_alert');
		$push_notification = $this->input->post('push_notification');
		$allow_online_booking = $this->input->post('allow_online_booking'); 
		
		
		$home_phone = $this->input->post('home_phone');
		$work_phone = $this->input->post('work_phone');
		$prefered_contact = $this->input->post('prefered_contact');
		
		$card_type = $this->input->post('card_type');
		
		/* $card_holder_name = $this->input->post('card_holder_name'); 
		$card_number = $this->input->post('card_number'); 
		$cvv = $this->input->post('cvv'); 
		$expiry_month = $this->input->post('expiry_month'); 
		$expiry_year = $this->input->post('expiry_year'); 
		$card_zipcode = $this->input->post('card_zipcode');  */
		
		$card_holder_name = $this->input->post('card_holder_name'); 
		$card_number = $this->input->post('card_number'); 
		$cvv = $this->input->post('cvv'); 
		$expiry_month = $this->input->post('expiry_month'); 
		$expiry_year = $this->input->post('expiry_year'); 
		$card_zipcode = $this->input->post('card_zipcode');
		$card_id = $this->input->post('card_id');
		
		$card_detail = $this->input->post('card_detail');
		
		
			/*$publicId=$this->db->query('select publicAccountID from vendor where vendor_id="38"')->row();
				$url='https://api.elasticemail.com/v2/contact/add?publicAccountID='.$publicId->publicAccountID.'&email='.$email.'&listName=MyList1&firstName='.$firstname.'&lastName='.$lastname.'';
				//echo $url;die;
				$jsonData = json_decode(file_get_contents($url));
				exit;*/

		//print_r($_POST);exit;
		
		$response['status'] = 0;
		$response['message'] = '';
		
		
		
		if(!empty($firstname) && !empty($lastname)  && !empty($email)){
			
				$check = $this->checkUserExist($vendor_id,$email);
				$checkName = $this->checkCustomerNameExist($vendor_id,$firstname,$lastname);
				$checkPhone = $this->checkIfPhoneExist($vendor_id,$phone);
				
				/*  if($checkName==1){
				$response['status'] = 0;
				$response['message'] = 'Customer name already exists';
			
			} */
			
			if($checkPhone>0){
				$response['status'] = 0;
				$response['message'] = 'Phone already exists';
			
			}elseif($check>0){
				$response['status'] = 0;
				$response['message'] = 'Customer already exists';
			}
			else{  

			if(!empty($_FILES['photo']['name'])){
		/*	$path = '../assets/img/customer/thumb/';
			$file = time().'.jpeg';
			$photo = $this->base64_to_jpeg($photo,$path.$file);
			$photo_name = $file;*/
			//$path = '../assets/img/customer/thumb/';
			//$file = time().'.jpeg';
			///$photo = $this->base64_to_jpeg($photo,$path.$file);
			//$photo_name = $file;
			$photo = $_FILES['photo']['name'];
		$tmp_photo = $_FILES['photo']['tmp_name'];
		$path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/customer/thumb/';
		$file = time().$photo;
			$photo = move_uploaded_file($tmp_photo,$path.$file);
			$photo_name = $file;
		
		}else{
			$photo_name = 'avtar.png';
		}
			
			//$password = $this->randomPassword();

			$first_four =  substr($firstname,0,4);
			$last_four =  substr($phone,-4);
			$password = $first_four.$last_four;
			$gender_character = mb_substr($gender, 0, 1);
				//$publicId=$this->db->query('select publicAccountID from vendor where vendor_id="'.$vendor_id.'"')->row();
		
			$this->db->query("insert into login set email='".$email."', password='".md5($password)."',role_id='2', is_active='1', is_delete='0', created_date='".date('Y-m-d H:i:s')."', created_by='1', vendor_id='".$vendor_id."'  ");
			$insert_id = $this->db->insert_id();
			
			$query = $this->db->query("insert into customer set login_id='".$insert_id."', firstname='".$firstname."', lastname='".$lastname."', email='".$email."', mobile_phone='".$phone."', address='".$address."', pincode='".$zipcode."',city='".$city."', birthday='".$birthday."', note='".addslashes($notes)."', photo='".$photo_name."', cc_appointment_email='".$cc_email."', gender='".$gender."', country_id='".$country."', state_id='".$state_id."', emergency_contact_name='".$emergency_name."', emergency_relationship='".$emergency_relation."', emergency_phone='".$emergency_contact."', anniversary='".$anniversary."', occupation='".$occupation."', referred_by='".$refered_by."', iou_limit='".$iou_limit."', is_email_receive='".$email_alert."', is_sms_receive='".$sms_alert."', is_push_notify='".$push_notification."', online_booking='".$allow_online_booking."', home_phone='".$home_phone."', work_phone='".$work_phone."',prefered_contact='".$prefered_contact."' ");
			
			$customer_id = $this->db->insert_id();
			
			$gender_character = mb_substr($gender, 0, 1);
			if($query){
				/*add Customer to elastic mail*/
				$newPhone=str_replace(' ', '', $phone);
				$publicId=$this->db->query('select publicAccountID from vendor where vendor_id="'.$vendor_id.'"')->row();
				$url='https://api.elasticemail.com/v2/contact/add?publicAccountID='.$publicId->publicAccountID.'&email='.$email.'&listName=MyList1&firstName='.$firstname.'&lastName='.$lastname.'&field_birthday='.$birthday.'&phone='.$newPhone.'&field_anniversary='.$anniversary.'&gender='.$gender_character.'';
				$jsonData = json_decode(file_get_contents($url));
				//echo "<pre>";print_r($jsonData);
				
				/*add customer to elastic email*/
				
				$card_detail = json_decode($card_detail);
			//[{'card_holder_name':'abc','card_number':'9876546543322','cvv':'4533','expiry_month':'09','expiry_year':'25','card_zipcode':'34888','is_default':'1'}]
				foreach($card_detail as $detail){
					
					if($detail->is_default=='1'){
						$this->db->query("update customer_card set is_default='0' where customer_id='".$customer_id."' ");
					}
					
					if($detail->card_id==''){
						
						//echo "insert into customer_card set customer_id='".$customer_id."', cardholder_name='".$detail->cardholder_name."', card_number='".$detail->card_number."', expiry_month='".$detail->expiry_month."', expiry_year='".$detail->expiry_year."', cvv='".$detail->cvv."', zipcode='".$detail->zipcode."', card_type='".$detail->card_type."' ";
						
						$this->db->query("insert into customer_card set customer_id='".$customer_id."', cardholder_name='".$detail->cardholder_name."', card_number='".$detail->card_number."', expiry_month='".$detail->expiry_month."', expiry_year='".$detail->expiry_year."', cvv='".$detail->cvv."', zipcode='".$detail->zipcode."', card_type='".$detail->card_type."', is_default='".$detail->is_default."' ");
					}else{
						
						//echo "update customer_card set  cardholder_name='".$detail->cardholder_name."', card_number='".$detail->card_number."', expiry_month='".$detail->expiry_month."', expiry_year='".$detail->expiry_year."', cvv='".$detail->cvv."', zipcode='".$detail->zipcode."' where card_id='".$detail->card_id."' and customer_id='".$customer_id."' ";
					
						$this->db->query("update customer_card set  cardholder_name='".$detail->cardholder_name."', card_number='".$detail->card_number."', expiry_month='".$detail->expiry_month."', expiry_year='".$detail->expiry_year."', cvv='".$detail->cvv."', zipcode='".$detail->zipcode."', is_default='".$detail->is_default."' where card_id='".$detail->card_id."' and customer_id='".$customer_id."' ");
					}
				
				}
				
				//die;
				
				$state_name = $this->getStateNameByStateId($state_id);


				// mailchimp code start here


				// create customer merge_fields start here

				
					//The type for the merge field. Possible values: "text", "number", "address", "phone", "date", "url", "imageurl", "radio", "dropdown", "birthday", or "zip".
				
				
			


				// create custoemr merge_field end here


				
				$vendor_data = $this->db->query("select mailchimp_list_id, vendor_name from vendor where vendor_id='".$vendor_id."'")->row();
				
				$mailchimp_list_id = $vendor_data->mailchimp_list_id;

				if($mailchimp_list_id!='' || $mailchimp_list_id!=NULL){

					$vendor_name = $vendor_data->vendor_name;
				
				require_once(APPPATH.'libraries/mailchimp/vendor/autoload.php'); 
			
				
				$client = new MailchimpMarketing\ApiClient();

				$client->setConfig([
					'apiKey' => 'c799b76606792859138325910554f01d-us20',
					'server' => 'us20',
				]); 

				 $list_id = $mailchimp_list_id;
				// $list_id = "400dcb3a52";
//$email = "123abctest@gmail.com";
//$vendor_name = "123abctest vendor";
//$phone = "9865432221"; 

				try {
					$respo = $client->lists->addListMember($list_id, [
						"email_address" => $email,
						"status" => "subscribed",
						"merge_fields" => [
						  "EMAIL" => $email,
						  "FNAME" => $firstname,
						  "LNAME" => $lastname,
						  "ADDRESS" => $address,
						  "PHONE" => $phone
						  
						]
					]);
					
					//print_r($response);
				} catch (MailchimpMarketing\ApiException $e) {
					//echo $e->getMessage();
				}
				
				
				}
				
				// mailchimp code end here
				 
				



			
				$response['status'] = 1;
				$response['customer_id'] = $customer_id;
				$response['message'] = 'Customer added successfully';
				$receiver_email = $email;
              
                $initial_time = time();
            
				$data['firstname']=$firstname;
                $data['lastname']=$lastname;
                $data['password']=$password;
                $data['email']=$email;
                $data['business_info'] = $this->getSalonDetail($vendor_id);
                $emailTemplate = $this->load->view('account_confirmation',$data,TRUE);
               	$subject = "Registration on Hubwallet";
				   
				
				$this->load->library('Send_mail');
				$this->send_mail->sendMail($email, $subject, $emailTemplate, $fileName=false, $filePath=false, $cc=false);
				$search1  = array('{Customer First Name}','{Business Name}');
				$customer_name=$firstname;
				$replace1 = array($customer_name, $data['business_info']->business_name);
				$getDataNew=getImageTemplate($vendor_id,'customer_registration');
				$getsmsData=str_replace($search1, $replace1, $getDataNew->sms_content);
				test($getsmsData,$phone);
				
			}else{
				$response['status'] = 0;
				$response['message'] = 'Customer not added';
			}
		}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		

	}



	public function getStateNameByStateId($state_id){

		$query = $this->db->query("select name as state_name from states where id='".$state_id."' ");

		$state_name = $query->row()->state_name;
		return $state_name;

	}

	public function getSalonDetail($vendor_id){

		$query = $this->db->query("select v.vendor_name as business_name, v.phone from vendor v where v.vendor_id='".$vendor_id."' ");
		$result = $query->row();
		return $result;
	}
	
	public function updateCustomerCard(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$customer_id = $this->input->post('customer_id');
		$vendor_id = $this->input->post('vendor_id');
		
		$card_type = $this->input->post('card_type');
		
		$card_holder_name = $this->input->post('card_holder_name'); 
		$card_number = $this->input->post('card_number'); 
		$cvv = $this->input->post('cvv'); 
		$expiry_month = $this->input->post('expiry_month'); 
		$expiry_year = $this->input->post('expiry_year'); 
		$card_zipcode = $this->input->post('card_zipcode');
		$card_id = $this->input->post('card_id');
		
		
		if(!empty($customer_id) && !empty($vendor_id) && !empty($card_holder_name) && !empty($card_number) && !empty($cvv) && !empty($expiry_month) && !empty($expiry_year)){
			
			/* if($card_id==''){
				$query = $this->db->query("insert into customer_card set customer_id='".$customer_id."', cardholder_name='".$card_holder_name."', card_number='".$card_number."', expiry_month='".$expiry_month."', expiry_year='".$expiry_year."', cvv='".$cvv."', zipcode='".$card_zipcode."', card_type='".$card_type."' ");
			
			}else{
				
				$query = $this->db->query("update customer_card set customer_id='".$customer_id."', cardholder_name='".$card_holder_name."', card_number='".$card_number."', expiry_month='".$expiry_month."', expiry_year='".$expiry_year."', cvv='".$cvv."', zipcode='".$card_zipcode."', card_type='".$card_type."' where card_id='".$card_id."' ");
			}
			if($query){
				
				
					$response['status'] = 1;
					$response['message'] = 'Card updated successfully';
					
			} */
			
			$response['status'] = 1;
					$response['message'] = 'Card updated successfully';
			
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
		
	}
	
	public function customerCardList(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$customer_id = $this->input->post('customer_id');
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($customer_id)){
			
			$query = $this->db->query("select * from customer_card where customer_id='".$customer_id."' order by card_id asc ");
			if($query->num_rows()>0){
				
				$result = $query->result();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
		
	}
	
	public function deleteCustomerCard(){
		$response['status'] = 0;
		$response['message'] = '';
		$customer_id = $this->input->post('customer_id');
		$vendor_id = $this->input->post('vendor_id');
		$card_id = $this->input->post('card_id');
		
		if(!empty($customer_id) && !empty($vendor_id) && !empty($card_id)){
			
			$query = $this->db->query("delete from customer_card where card_id='".$card_id."' ");
			if($query){
				$response['status'] = 1;
				$response['message'] = 'Card deleted successfully';
				
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
	
	public function makeCustomerCardDefault(){
		$response['status'] = 0;
		$response['message'] = '';
		$customer_id = $this->input->post('customer_id');
		$vendor_id = $this->input->post('vendor_id');
		$card_id = $this->input->post('card_id');
		
		if(!empty($customer_id) && !empty($vendor_id) && !empty($card_id)){
			
			$query = $this->db->query("update customer_card set is_default='1' where customer_id='".$customer_id."' and card_id='".$card_id."' ");
			if($query){
				$response['status'] = 1;
				$response['message'] = 'Card set to default';
				
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
	
	
	
	
	function getCustomerById(){
		
		
		$customer_id = $this->input->post('customer_id');
		$response['status'] = 0;
		$response['message'] = '';
		$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
		$path = $actual_link.'/assets/img/customer/thumb';
		
			if(!empty($customer_id)){
			$query = $this->db->query("select c.customer_id, l.email, c.firstname, c.lastname, c.mobile_phone as mobile, c.address,c.pincode as zipcode, c.city, c.birthday,c.note, c.anniversary, c.cc_appointment_email, c.gender, c.state_id, c.emergency_contact_name as emergency_contact,c.work_phone,c.home_phone, c.emergency_relationship as emergency_relation, c.emergency_phone as emergency_contact_no,c.referred_by, c.occupation,c.iou_limit, c.is_email_receive as email_alert, c.is_sms_receive as sms_alert,is_push_notify as push_notification, c.online_booking, c.card_holder_name, c.card_number, c.cvv, c.card_month as expiry_month, c.card_year as expiry_year,CONCAT('$path','/',if(c.photo='','avtar.png',c.photo)) as photo,c.card_type,c.prefered_contact from login l INNER JOIN customer c ON c.login_id=l.login_id  where l.role_id='2' and l.is_delete='0' and c.customer_id='".$customer_id."' ");
			$res = $query->row();
			
			if($res){
				
				$response['status'] = 1;
				$response['result'] = $res;
				
			}else{
				
				$response['status'] = 0;
				$response['message'] = 'Something wrong in query';
			}
			}else{
				
				$response['status'] = 0;
				$response['message'] = 'Required parameter missing';
			}
		
		echo json_encode($response);
	}
			
	
	public function getCustomerDetailById($customer_id,$vendor_id){
		
		$qry = $this->db->query("select c.photo from customer c where c.customer_id='".$customer_id."' ");
		$res = $qry->row();
		return $res;
	}
	
	public function edit(){
		///echo "<pre>";print_r($_POST);exit;
		$customer_id = $this->input->post('customer_id');
		$vendor_id = $this->input->post('vendor_id');
		$firstname = addslashes($this->input->post('firstname'));
   		$lastname = addslashes($this->input->post('lastname'));
   		
		$email = $this->input->post('email');
		$phone = $this->input->post('mobile');
		$work_phone = $this->input->post('work_phone');
		$home_phone = $this->input->post('home_phone');
		$refered_by = $this->input->post('refered_by');
		$prefered_contact = $this->input->post('prefered_contact');
		
		$address = $this->input->post('address');
		$zipcode = $this->input->post('zipcode');
		$city = $this->input->post('city');
		$birthday = $this->input->post('birthday');
		$notes = $this->input->post('notes');
		//$photo = $this->input->post('photo'); 
		
		$cc_email = $this->input->post('cc_email'); 
		$gender = $this->input->post('gender'); 
		$country = "231"; 
		$state_id = $this->input->post('state_id'); 
		$emergency_name = $this->input->post('emergency_name'); 
		$emergency_relation = $this->input->post('emergency_relation'); 
		$emergency_contact = $this->input->post('emergency_contact'); 
		
		$anniversary = $this->input->post('anniversary'); 
		$occupation = $this->input->post('occupation'); 
		
		$iou_limit = $this->input->post('iou_limit'); 
		$email_alert = $this->input->post('email_alert');
		$sms_alert = $this->input->post('sms_alert');
		$push_notification = $this->input->post('push_notification');
		$allow_online_booking = $this->input->post('allow_online_booking'); 
		
		
		/* $card_holder_name = $this->input->post('card_holder_name'); 
		$card_number = $this->input->post('card_number'); 
		$cvv = $this->input->post('cvv'); 
		$expiry_month = $this->input->post('expiry_month'); 
		$expiry_year = $this->input->post('expiry_year'); 
		$card_type=$this->input->post('card_type'); */
		
		$card_holder_name = $this->input->post('card_holder_name'); 
		$card_number = $this->input->post('card_number'); 
		$cvv = $this->input->post('cvv'); 
		$expiry_month = $this->input->post('expiry_month'); 
		$expiry_year = $this->input->post('expiry_year'); 
		$card_zipcode = $this->input->post('card_zipcode');
		$card_id = $this->input->post('card_id');
		
		$card_detail = $this->input->post('card_detail');
		
		
		
		//$response['status'] = 0;
		//$response['message'] = '';
		
		
		
		
		if(!empty($customer_id)){
			
			if(!empty($_FILES['photo']['name'])){
		/*	$path = '../assets/img/customer/thumb/';
			$file = time().'.jpeg';
			$photo = $this->base64_to_jpeg($photo,$path.$file);
			$photo_name = $file;*/
			//$path = '../assets/img/customer/thumb/';
			//$file = time().'.jpeg';
			///$photo = $this->base64_to_jpeg($photo,$path.$file);
			//$photo_name = $file;
			$photo = $_FILES['photo']['name'];
		$tmp_photo = $_FILES['photo']['tmp_name'];
		$path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/customer/thumb/';
		$file = time().$photo;
			$photo = move_uploaded_file($tmp_photo,$path.$file);
			$photo_name = $file;
			//echo $path;exit;
		}else{
			$editData = $this->getCustomerDetailById($customer_id,$vendor_id);
			$photo_name = $editData->photo;
		}
		
			
			//echo count($getEmail);exit;
			$login_id=$this->db->query('select login_id from customer where customer_id="'.$customer_id.'"')->row();
			$getEmail=$this->db->query('select email from login where login_id="'.$login_id->login_id.'" and vendor_id="'.$vendor_id.'" ')->row();
			//echo "<pre>";print_r($getEmail);exit;
		//	echo $getEmail->email."---".$email;exit;
			if($getEmail->email==$email){
			//echo "vishal";exit;
			if($login_id >0){
				$this->db->query('update login set email="'.$email.'" where login_id="'.$login_id->login_id.'"');
			}
			$query = $this->db->query("update customer set firstname='".$firstname."', lastname='".$lastname."', email='".$email."', mobile_phone='".$phone."', address='".$address."', pincode='".$zipcode."',city='".$city."', birthday='".$birthday."', note='".addslashes($notes)."', photo='".$photo_name."', cc_appointment_email='".$cc_email."', gender='".$gender."', country_id='".$country."', state_id='".$state_id."', emergency_contact_name='".$emergency_name."', emergency_relationship='".$emergency_relation."', work_phone='".$work_phone."',home_phone='".$home_phone."',referred_by='".$refered_by."',anniversary='".$anniversary."', occupation='".$occupation."', iou_limit='".$iou_limit."', is_email_receive='".$email_alert."', is_sms_receive='".$sms_alert."', is_push_notify='".$push_notification."', online_booking='".$allow_online_booking."',  card_holder_name='".$card_holder_name."', card_number='".$card_number."', cvv='".$cvv."', card_month='".$expiry_month."', card_year='".$expiry_year."', prefered_contact='".$prefered_contact."',card_type='".$card_type."' where customer_id='".$customer_id."' ");
			
		
			
		}else{
		//	echo 'select email from login where email="'.$email.'" and vendor_id="'.$vendor_id.'"';die;
			$getEmail1=$this->db->query('select email from login where email="'.$email.'" and vendor_id="'.$vendor_id.'" and role_id="2" ')->row();
			
			if(count($getEmail1) >0){
				$response['status'] = 1;
				$response['message'] = 'Email already exists';	
			}else{
		
			
				$this->db->query('update login set email="'.$email.'" where login_id="'.$login_id->login_id.'"');
				
				$query = $this->db->query("update customer set firstname='".$firstname."', lastname='".$lastname."', email='".$email."', mobile_phone='".$phone."', address='".$address."', pincode='".$zipcode."',city='".$city."', birthday='".$birthday."', note='".addslashes($notes)."', photo='".$photo_name."', cc_appointment_email='".$cc_email."', gender='".$gender."', country_id='".$country."', state_id='".$state_id."', emergency_contact_name='".$emergency_name."', emergency_relationship='".$emergency_relation."', emergency_phone='".$emergency_contact."', anniversary='".$anniversary."', occupation='".$occupation."', referred_by='".$refered_by."', iou_limit='".$iou_limit."', is_email_receive='".$email_alert."', is_sms_receive='".$sms_alert."', is_push_notify='".$push_notification."', online_booking='".$allow_online_booking."',  card_holder_name='".$card_holder_name."', card_number='".$card_number."', cvv='".$cvv."', card_month='".$expiry_month."', card_year='".$expiry_year."', home_phone='".$home_phone."', work_phone='".$work_phone."', prefered_contact='".$prefered_contact."',card_type='".$card_type."'  where customer_id='".$customer_id."' ");


				$first_four =  substr($firstname,0,4);
				$last_four =  substr($phone,-4);
				$password = $first_four.$last_four;

				$data['firstname']=$firstname;
                $data['lastname']=$lastname;
                $data['password']=$password;
                $data['email']=$email;
                $data['business_info'] = $this->getSalonDetail($vendor_id);
                $emailTemplate = $this->load->view('account_confirmation',$data,TRUE);
               	$subject = "Registration on Hubwallet";
				   
				
				$this->load->library('Send_mail');
				$this->send_mail->sendMail($email, $subject, $emailTemplate, $fileName=false, $filePath=false, $cc=false);
				$search1  = array('{Customer First Name}','{Business Name}');
				$customer_name=$firstname;
				$replace1 = array($customer_name, $data['business_info']->business_name);
				$getDataNew=getImageTemplate($vendor_id,'customer_registration');
				$getsmsData=str_replace($search1, $replace1, $getDataNew->sms_content);
				test($getsmsData,$phone);

						
			}
		}	
		
			$card_detail = json_decode($card_detail);
			
			//echo '<pre>';print_r($card_detail);die;
			//[{'cardholder_name':'abc','card_number':'9876546543322','cvv':'4533','expiry_month':'09','expiry_year':'25','zipcode':'34888','card_id':'157','is_default':'1'}]
			foreach($card_detail as $detail){
				
					if($detail->is_default=='1'){
						$this->db->query("update customer_card set is_default='0' where customer_id='".$customer_id."' ");
					}
					
					if($detail->card_id==''){
						
						
						$this->db->query("insert into customer_card set customer_id='".$customer_id."', cardholder_name='".$detail->cardholder_name."', card_number='".$detail->card_number."', expiry_month='".$detail->expiry_month."', expiry_year='".$detail->expiry_year."', cvv='".$detail->cvv."', zipcode='".$detail->zipcode."', is_default='".$detail->is_default."' ");
						
					}else{
						
						
						
						$this->db->query("update customer_card set cardholder_name='".$detail->cardholder_name."', card_number='".$detail->card_number."', expiry_month='".$detail->expiry_month."', expiry_year='".$detail->expiry_year."', cvv='".$detail->cvv."', zipcode='".$detail->zipcode."', is_default='".$detail->is_default."' where card_id='".$detail->card_id."' and customer_id='".$customer_id."' ");
					}
				
			}
			
			//die;
			
			$response['status'] = 1;
			$response['message'] = 'Customer updated successfully';
						
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		

	}
	
	
	public function delete(){
		
		$vendor_id = $this->input->post('vendor_id');
		$customer_id = $this->input->post('customer_id');
		
		if(!empty($vendor_id) && !empty($customer_id)){
			
			$qry = $this->db->query("select login_id from customer where customer_id='".$customer_id."'");
			$res = $qry->row();
			$login_id = $res->login_id;
			
			if($login_id){
				$update = $this->db->query("update login set is_delete='1' where login_id='".$login_id."' and vendor_id='".$vendor_id."'");
				
				if($update){
					$response['status'] = 1;
					$response['message'] = 'Customer deleted successfully';
					
				}else{
					$response['status'] = 0;
					$response['message'] = 'Customer not deleted';
				}
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	
	public function getCustomerInfoById($customer_id){
		
		$query = $this->db->query("select c.login_id from customer c where c.customer_id='".$customer_id."' ");
		$result = $query->row();
		return $result;
		
	}
	
	public function activeDeactiveCustomer(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$customer_id = $this->input->post('customer_id');
		$vendor_id = $this->input->post('vendor_id');
		$status = $this->input->post('status'); //status 1=ACTIVE, 0=DEACTIVE
		
		if(!empty($customer_id) && !empty($vendor_id)){
			
			
			for($i=0;$i<count($customer_id);$i++){
				$customerInfo = $this->getCustomerInfoById($customer_id[$i]);
				$login_id = $customerInfo->login_id;
				$query = $this->db->query("update login set is_active='".$status."' where login_id='".$login_id."' and vendor_id='".$vendor_id."'  ");
			}
			if($query){
				if($status==1){
					$msg = 'Customer activated successfully';
				}
				elseif($status==0){
					$msg = 'Customer deactivated successfully';
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
	
	public function deleteCustomer(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$customer_id = $this->input->post('customer_id');
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($customer_id) && !empty($vendor_id)){
			
			
			for($i=0;$i<count($customer_id);$i++){
				$customerInfo = $this->getCustomerInfoById($customer_id[$i]);
				$login_id = $customerInfo->login_id;
				$query = $this->db->query("update login set is_delete='1' where login_id='".$login_id."' and vendor_id='".$vendor_id."'  ");
			}
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Customer deleted successfully';
			}
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
	}
	
	public function viewProfile(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$customer_id = $this->input->post('customer_id');
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($customer_id) && !empty($vendor_id)){
			
			$query = $this->db->query("select l.login_id, l.email, CONCAT(c.firstname,' ',c.lastname) as customer_name, c.gender, c.mobile_phone, DATE_FORMAT(l.created_date,'%M %d %Y') AS customer_since, c.emergency_contact_name, c.emergency_relationship,c.emergency_phone, '' AS total_sales, '' AS appointments, '' AS cancellation, '' AS no_show, '' AS last_visit, '' AS wallet_points  from customer c INNER JOIN login l ON l.login_id=c.login_id where c.customer_id='".$customer_id."' and l.vendor_id='".$vendor_id."' ");
			$num = $query->num_rows();
			$result = $query->row();
			if($num>0){
				
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}else{
				$response['status'] = 0;
				$response['result'] = '';
				$response['message'] = 'No Data Found';
			}
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
	}
	
	
	public function appointment(){
		
		$response['status'] = 0;
		$response['message'] = '';
			
		$customer_id = $this->input->post('customer_id');
		if(!empty($customer_id)){
			$query = $this->db->query("select 
									a.appointment_id,
									a.date as appointment_date,
									a.note,
									a.token_no,
									a.created_by,
									a.is_checkin,
									a.checkin_time,
									a.is_checkout,
									a.checkout_time,
									a.appointment_type,
									a.status,
									a.is_cancel,
									aps.appointment_time,
									aps.price,
									aps.duration,
									aps.points,
									CONCAT(st.firstname,' ',st.lastname) as stylist_name,
									s.service_name
									from appointment a
									INNER JOIN appointment_service as aps
									ON aps.appointment_id=a.appointment_id
									INNER JOIN stylist st
									ON st.stylist_id=aps.stylist_id
									INNER JOIN service s
									ON s.service_id=aps.service_id
									where a.customer_id='".$customer_id."'
									ORDER BY a.date DESC
									");
		$result = $query->result();
		$num = $query->num_rows();
		if($num>0){
			$response['status'] = 1;
			$response['result'] = $result;
			$response['message'] = '';
		}else{
			$response['status'] = 0;
			$response['result'] = '';
			$response['message'] = 'No Appointment Available';
			
		}
			
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';	
		}
		echo json_encode($response);
		
	}
	
	public function getCountry(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$query = $this->db->query("select id as country_id, name as country_name from countries ");
		$num = $query->num_rows();
		
		if($num>0){
			$result = $query->result();
			
		$response['status'] = 1;
		$response['result'] = $result;
		$response['message'] = '';
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'No country found';
		}
		
		echo json_encode($response);
	}
	
	public function getState(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$country_id = $this->input->post('country_id');
		
		if(!empty($country_id)){
		$query = $this->db->query("select id as state_id, name as state_name from states where country_id='231' ");
		$num = $query->num_rows();
		
		if($num>0){
			$result = $query->result();
			
			$response['status'] = 1;
			$response['result'] = $result;
			$response['message'] = '';
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'No country found';
		}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	
	public function checkUserExist($vendor_id,$email){
		
			$query = $this->db->query("select l.email from login l where l.role_id='2' and l.vendor_id='".$vendor_id."' and l.email='".$email."' and l.is_delete=0 ");
			if($query->num_rows()>0){
				return 1;
			
			}else{
				return 0;
			}
	}
	
	public function checkIfPhoneExist($vendor_id,$phone){
		
		
			$query = $this->db->query("select c.mobile_phone from customer c INNER JOIN login l ON l.login_id=c.login_id where l.vendor_id='".$vendor_id."' and c.mobile_phone='".$phone."'  and l.is_delete=0 ");
			if($query->num_rows()>0){
				return 1;
			
			}else{
				return 0;
			}
	}
	
	
	public function checkCustomerNameExist($vendor_id,$firstname,$lastname){
		
			$query = $this->db->query("SELECT c.customer_id FROM `customer` c INNER JOIN login l ON l.login_id=c.login_id WHERE CONCAT(c.firstname,' ',c.lastname)='$firstname $lastname' AND l.vendor_id='".$vendor_id."' and l.is_delete=0 ");
			if($query->num_rows()>0){
				return 1;
			
			}else{
				return 0;
			}
	}
	
	
	public function checkIfUserAlreadyExist(){
		
		$vendor_id = $this->input->post('vendor_id');
		$email = $this->input->post('email');
		$role_id = $this->input->post('role_id');
		
		$response['status'] = 0;
		$response['message'] = '';
		
		if($role_id==2){
			$msg = 'Customer already exist';
		}elseif($role_id==3){
			$msg = 'Employee already exist';
		}else{
			$msg = 'User already exist';
		}
		
			$query = $this->db->query("select l.email from login l where l.role_id='".$role_id."' and l.vendor_id='".$vendor_id."' and l.email='".$email."' ");
			if($query->num_rows()>0){
				
				$response['status'] = 1;
				$response['message'] = $msg;
			
			}else{
				$response['status'] = 0;
				$response['message'] = '';
			}
			
			echo json_encode($response);
	}
	public function checkIfCustomerMobileExist(){
		
		$vendor_id = $this->input->post('vendor_id');
		$phone = $this->input->post('phone');
		$role_id = $this->input->post('role_id');
		
		$response['status'] = 0;
		$response['message'] = '';
		
		if($role_id==2){
			$msg = 'Customer already exist';
		}elseif($role_id==3){
			$msg = 'Employee already exist';
		}else{
			$msg = 'User already exist';
		}
		
			$query = $this->db->query("select C.customer_id from customer as C inner join login as l on C.login_id=l.login_id where C.mobile_phone='".$phone."' and l.vendor_id='".$vendor_id."' ");
			if($query->num_rows()>0){
				
				$response['status'] = 1;
				$response['message'] = $msg;
			
			}else{
				$response['status'] = 0;
				$response['message'] = '';
			}
			
			echo json_encode($response);
	}
	public function getNote(){
		$customer_id=$this->input->post('customer_id');
		if(!empty($customer_id)){
			$data = $this->db->query("select note from customer where customer_id='".$customer_id."'")->row();	
			if(!empty($data)){
				$response['status'] = 1;
				$response['data'] =$data;
			}else{
				$response['status'] = 1;
				$response['data'] =(Object)[];
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	public function getCustomerHistory(){
			$customer_id=$this->input->post('customer_id');
			if($customer_id !=''){
			$close_appointment = $this->db->query("select
                            aps.as_id,
                            aps.appointment_id,
                            aps.appointment_time,
                            aps.price,
                            aps.duration,
                           
                            a.date as appointment_date,
                            
                            s.service_name,
                            s.tax_amount,
                            s.tax_rate,
                           
                           
                            CONCAT(st.firstname,' ',st.lastname) as stylist_name
                            from appointment_service aps
                            INNER JOIN appointment a
                            ON a.appointment_id=aps.appointment_id
                            INNER JOIN service s
                            ON s.service_id=aps.service_id
                            INNER JOIN stylist st
                            ON st.stylist_id=aps.stylist_id
                           where a.customer_id='".$customer_id."' and a.is_checkout=1
                            ORDER BY aps.as_id DESC
                            
              ")->result();
			$upcoming_appointment = $this->db->query("select
                            aps.as_id,
                            aps.appointment_id,
                            aps.appointment_time,
                            aps.price,
                            aps.duration,
                           
                           
                        
                            a.date as appointment_date,
                          
                            s.service_name,
                            s.tax_amount,
                            s.tax_rate,
                         
                          
                            CONCAT(st.firstname,' ',st.lastname) as stylist_name
                            from appointment_service aps
                            INNER JOIN appointment a
                            ON a.appointment_id=aps.appointment_id
                            INNER JOIN service s
                            ON s.service_id=aps.service_id
                            INNER JOIN stylist st
                            ON st.stylist_id=aps.stylist_id
                            where a.customer_id='".$customer_id."' and a.is_checkout=0
                            ORDER BY aps.as_id DESC
                            
              ")->result();
			$response['status'] = 1;
			$response['close_appointment'] = $close_appointment;
			$response['upcoming_appointment'] = $upcoming_appointment;
			$response['message'] = 'successfull';
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);

	}
	public function updateCustomerNote(){
		$customer_id=$this->input->post('customer_id');
		$note=$this->input->post('note');
		if($customer_id !=''){
			$query=$this->db->query('update customer set note="'.$note.'" where customer_id="'.$customer_id.'"');
			if($query){
			       $response['status'] = 1;
			       $response['message'] = 'update successfull';
			}else{
				 $response['status'] = 0;
				 $response['message'] = 'Something Wrong';
			}
		}else{
				$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
			echo json_encode($response);
	}
	function checkEmailExsistCustomer(){
        $email=$this->input->post('email');
        $num_row=$this->db->query('select email from login where email="'.$email.'" and role_id=2')->num_rows();
        if($num_row >0){
                $response['status'] = 1; 
				$response['message'] = "Email already exsist"; 
        }else{
            $response['status'] = 0; 
				$response['message'] = "Not exsist"; 
        }
        echo json_encode($response);
    }
	
	function customerDetail(){
		
		 $response['status'] = 0;
		 $response['message'] ='';
		
		$vendor_id = $this->input->post('vendor_id');
		$customer_id = $this->input->post('customer_id');
		
       
	   if(!empty($vendor_id) && !empty($customer_id)){
       
			// upcoming appointment
			$appointment = $this->db->query("select DATE_FORMAT(a.date,'%m/%d/%Y') as appointment_date, TIME_FORMAT(aps.appointment_time,'%h:%i %p') as appointment_time, CONCAT(st.firstname,' ',st.lastname) as employee, CASE WHEN a.status=1 THEN 'AccePt' WHEN a.status=2 THEN 'Deny' WHEN a.status=3 THEN 'Confirm' WHEN a.status=4 THEN 'Show' WHEN a.status=5 THEN 'No Show' WHEN a.status=6 THEN 'Checkin' WHEN a.status=7 THEN 'Checkout' WHEN a.status=8 THEN 'Cancel' WHEN a.status=0 THEN 'Unconfirm' END AS appointment_status, s.service_name from appointment a INNER JOIN appointment_service aps ON aps.appointment_id=a.appointment_id LEFT JOIN service s ON s.service_id=aps.service_id LEFT JOIN stylist st ON st.stylist_id=aps.stylist_id where a.vendor_id='".$vendor_id."' and a.customer_id='".$customer_id."' and a.date>='".date('Y-m-d')."'  AND a.status!='7'  ORDER BY a.date asc ")->result();
			
		
			
			$current_appointment = $this->db->query("select DATE_FORMAT(a.date,'%m/%d/%Y') as appointment_date, TIME_FORMAT(aps.appointment_time,'%h:%i %p') as appointment_time, CASE WHEN a.status=1 THEN 'AccePt' WHEN a.status=2 THEN 'Deny' WHEN a.status=3 THEN 'Confirm' WHEN a.status=4 THEN 'Show' WHEN a.status=5 THEN 'No Show' WHEN a.status=6 THEN 'Checkin' WHEN a.status=7 THEN 'Checkout' WHEN a.status=8 THEN 'Cancel' WHEN a.status=0 THEN 'Unconfirm' END AS appointment_status, s.service_name from appointment a INNER JOIN appointment_service aps ON aps.appointment_id=a.appointment_id LEFT JOIN service s ON s.service_id=aps.service_id where a.vendor_id='".$vendor_id."' and a.customer_id='".$customer_id."' and a.date='".date('Y-m-d')."' and a.status='6' ORDER BY a.date asc ")->result();
			
			$customer_history = $this->db->query("select  o.order_number, DATE_FORMAT(o.created_date,'%m/%d/%Y') as order_date, CASE WHEN o.status_id=1 THEN 'Pending' WHEN o.status_id=2  THEN 'Completed' WHEN o.status_id=3 THEN 'Cancel' END as status from orders o WHERE o.customer_id='".$customer_id."' and o.vendor_id='".$vendor_id."' order by o.order_id DESC ")->result();
			
			
			
			if(count($appointment)>0){
				$response['status'] = 1;
				$response['upcoming_appointment'] = $appointment;
				$response['message'] = '';
			
			}else{
				$response['status'] = 0;
				$response['upcoming_appointment'] = array();
				$response['message'] = 'Upcoming appointment data not found';
			}
			
			if(count($current_appointment)>0){
				$response['status'] = 1;
				$response['current_appointment'] = $current_appointment;
				$response['message'] = '';
			
			}else{
				$response['status'] = 0;
				$response['current_appointment'] = array();
				$response['message'] = 'Current appointment data not found';
			}
			
			if(count($customer_history)>0){
				$response['status'] = 1;
				$response['customer_history'] = $customer_history;
				$response['message'] = '';
			
			}else{
				$response['status'] = 0;
				$response['customer_history'] = array();
				$response['message'] = 'Current appointment data not found';
			}
			
			
			$gift_certificate = $this->db->query("select gc.gift_certificate_no, gc.amount, DATE_FORMAT(gc.issue_date,'%m/%d/%Y') as issue_date, (select DATE_FORMAT(created_date,'%m/%d/%Y') from orders where gift_certificate_number=gc.gift_certificate_no AND status_id='2')  AS redeem_date from gift_certificate gc INNER JOIN customer c ON c.customer_id=gc.customer_id where gc.customer_id='".$customer_id."' AND gc.vendor_id='".$vendor_id."' ")->result();
			
			if(count($gift_certificate)>0){
				$response['gc_status'] = 1;
				$response['gift_certificate'] = $gift_certificate;
				$response['gc_message'] = '';
			}else{
				
				$response['gc_status'] = 0;
				$response['gift_certificate'] =array();
				$response['gc_message'] = 'Gift Certificate data not found';
			}
			
			$gift_card = $this->db->query("select gc.card_number, gc.amount, gc.amount as balance_amount, DATE_FORMAT(gc.issue_date,'%m/%d/%Y') as issue_date from gift_card gc INNER JOIN customer c ON c.customer_id=gc.customer_id INNER JOIN login l ON l.login_id=c.login_id where gc.customer_id='".$customer_id."' AND gc.vendor_id='".$vendor_id."' ")->result();
			
			if(count($gift_card)>0){
				
				$response['gift_status'] = 1;
				$response['gift_card'] = $gift_card;
				$response['gift_message'] = '';
				
			}else{
				
				$response['gift_status'] = 0;
				$response['gift_card'] =array();
				$response['gift_message'] = 'Gift card data not found';
			}
			
			
			/* $iou_data=$this->db->query('select A.order_id,A.iou_amount,(case when A.status=1 then "Paid" else "Pending" End) as status,date_format(A.date_created,"%d %M %Y") as dt,date_format(A.date_modified,"%d %M %Y") as md,B.order_number as order_no,B.order_amount,B.iou_amount from customer_iou_amount as A left join orders as B on A.order_id=B.order_id where  A.customer_id="'.$customer_id.'"')->result();
			
			if(count($iou_data)>0){
				
				$response['iou_status'] = 1;
				$response['iou_data'] = $iou_data;
				$response['iou_message'] = '';
				
			}else{
				
				$response['iou_status'] = 0;
				$response['iou_data'] =array();
				$response['iou_message'] = 'IOU data not found';
			}
			 */
			
			$selQuery = "SELECT
                     o.order_number as order_no,p.product_name,DATE_FORMAT(o.created_date,'%m/%d/%Y') as date
					FROM orders AS o
					INNER JOIN order_detail od
					ON o.order_id=od.order_id
					INNER JOIN product p
					ON od.product_id=p.product_id
                    WHERE o.customer_id = '".$customer_id."'
                   AND o.vendor_id='".$vendor_id."'
					ORDER BY o.order_id DESC";
			$customer_product = $this->db->query($selQuery)->result();
			
			
			
			if(count($customer_product)>0){
				
				$response['customer_product_status'] = 1;
				$response['customer_product'] = $customer_product;
				$response['customer_product_message'] = '';
				
			}else{
				
				$response['customer_product_status'] = 0;
				$response['customer_product'] =array();
				$response['customer_product_message'] = 'Customer product data not found';
			}
			
			
			/* $appointment_notes = $this->db->query("select a.date as appointment_date, a.note  from appointment a where a.vendor_id='".$vendor_id."' and a.customer_id='".$customer_id."' ")->result();
			
			
			if(count($appointment_notes)>0){
				
				$response['apt_note_status'] = 1;
				$response['apt_note'] = $appointment_notes;
				$response['apt_note_message'] = '';
				
			}else{
				
				$response['apt_note_status'] = 0;
				$response['apt_note'] =array();
				$response['apt_note_message'] = 'Appointment note data not found';
			} */
			
			
			/* $customer_history = $this->db->query("select DATE_FORMAT(a.date,'%m/%d/%Y') as appointment_date, TIME_FORMAT(aps.appointment_time,'%h:%i %p') as service_time, TIME_FORMAT(a.checkin_time,'%h:%i %p') as checkin_time, CASE WHEN a.appointment_type=1 THEN 'Single' WHEN a.appointment_type=2 THEN 'Multiple' ELSE 'Group' END AS appointment_type, CASE WHEN a.status=1 THEN 'AccePt' WHEN a.status=2 THEN 'Deny' WHEN a.status=3 THEN 'Confirm' WHEN a.status=4 THEN 'Show' WHEN a.status=5 THEN 'No Show' WHEN a.status=6 THEN 'Checkin' WHEN a.status=7 THEN 'Checkout' WHEN a.status=8 THEN 'Canel' END AS appointment_status, CONCAT(st.firstname,' ',st.lastname) as stylist_name, s.service_name, aps.price from appointment a INNER JOIN appointment_service aps ON aps.appointment_id=a.appointment_id LEFT JOIN stylist st ON st.stylist_id=aps.stylist_id LEFT JOIN service s ON s.service_id=aps.service_id where a.vendor_id='".$vendor_id."' and a.customer_id='".$customer_id."'   ")->result();
			
			
			if(count($customer_history)>0){
				$response['customer_history_status'] = 1;
				$response['customer_history'] = $customer_history;
				$response['customer_history_message'] = '';
			
			}else{
				$response['customer_history_status'] = 0;
				$response['customer_history'] =array();
				$response['customer_history_message'] = 'Customer history data not found';
			}
			 */
			
	   }
	   else{
		   
		   $response['status'] = 0;
		   $response['message'] ='Required parameter missing';
	   }
	   
	   echo json_encode($response);

	}
	
	
	public function waitingList(){
		
			$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$response['status'] = 1;
			$response['message'] = '';
		
		$current_date = date('Y-m-d');
		
		$response['now_serving'] = $this->db->query("select CONCAT(c.firstname,' ',c.lastname) as customer_name, CONCAT(s.firstname,' ',s.lastname) as stylist_name, TIME_FORMAT(a.checkin_time,'%h:%i %p') as checkin_time, CONCAT(aps.duration,' Min') as duration from appointment a INNER JOIN appointment_service aps ON aps.appointment_id=a.appointment_id INNER JOIN customer c ON c.customer_id=a.customer_id INNER JOIN stylist s ON s.stylist_id=aps.stylist_id where a.vendor_id='".$vendor_id."' AND a.is_checkin=1 AND a.status='6' AND a.is_checkout='0' AND a.date='".$current_date."' limit 0,3")->result();
		
		$response['waitlist'] = $this->db->query("select CONCAT(c.firstname,' ',LEFT(c.lastname,1)) as customer_name, CONCAT(s.firstname,' ',s.lastname) as stylist_name, TIME_FORMAT(aps.appointment_time,'%h:%i %p') as appointment_time, CONCAT(aps.duration,' Min') as duration from appointment a INNER JOIN appointment_service aps ON aps.appointment_id=a.appointment_id INNER JOIN customer c ON c.customer_id=a.customer_id INNER JOIN stylist s ON s.stylist_id=aps.stylist_id where a.vendor_id='".$vendor_id."' AND a.is_checkin='1' AND a.is_checkout='0' AND a.date='".$current_date."' ORDER BY aps.appointment_time ASC limit 0,6 ")->result();
		//TIME_FORMAT('17:20:25', '%r')
		
		$response['upcomming_apponitment'] = $this->db->query("select CONCAT(c.firstname,' ',LEFT(c.lastname,1)) as customer_name, CONCAT(s.firstname,' ',s.lastname) as stylist_name, TIME_FORMAT(aps.appointment_time,'%h:%i %p') as appointment_time, CONCAT(aps.duration,' Min') as duration from appointment a INNER JOIN appointment_service aps ON aps.appointment_id=a.appointment_id INNER JOIN customer c ON c.customer_id=a.customer_id INNER JOIN stylist s ON s.stylist_id=aps.stylist_id where a.vendor_id='".$vendor_id."' AND a.is_checkin='0' AND a.is_checkout='0' AND a.date='".$current_date."' ORDER BY aps.appointment_time ASC limit 0,6 ")->result();
		
		
		
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
        echo json_encode($response);	
	}
	
	
}