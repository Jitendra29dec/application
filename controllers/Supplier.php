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


class Supplier extends CI_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
		
		//error_reporting(0);
    }
	
	
	
	public function getSupplier(){
		
		$response['status'] = 0;
		$response['message'] = '';
			
		$vendor_id = $this->input->post('vendor_id');
		$is_po = $this->input->post('is_po');  // is_po=1 for purchase order, is_po=2 for product, is_po="" for supplier
		
		$path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/supplier/thumb/';
		if(!empty($vendor_id)){
			
			if($is_po=='1'){
				
				$qry = $this->db->query("select DISTINCT(s.supplier_id), s.supplier_name, s.phone, CONCAT('$path','/',s.supplier_logo) as photo, s.email, IF(s.is_active=1,'Active','Inactive') as status from supplier s INNER JOIN product_supplier ps ON ps.supplier_id=s.supplier_id where s.vendor_id='".$vendor_id."' and s.is_delete=0  order by s.supplier_id desc  ");
				
			}elseif($is_po=='2'){
				
				//$qry = $this->db->query("select DISTINCT(s.supplier_id), s.supplier_name, s.phone, CONCAT('$path','/',s.supplier_logo) as photo, s.email, IF(s.is_active=1,'Active','Inactive') as status from supplier s INNER JOIN product_supplier ps ON ps.supplier_id=s.supplier_id where s.vendor_id='".$vendor_id."' and s.is_delete=0 and s.is_active='1' order by s.supplier_id desc  ");

				$qry = $this->db->query("select DISTINCT(s.supplier_id), s.supplier_name, s.phone, CONCAT('$path','/',s.supplier_logo) as photo, s.email, IF(s.is_active=1,'Active','Inactive') as status from supplier s  where s.vendor_id='".$vendor_id."' and s.is_delete=0 and s.is_active='1' order by s.supplier_id desc  ");
				
			}else{
				$qry = $this->db->query("select s.supplier_id, s.supplier_name, s.phone, CONCAT('$path','/',s.supplier_logo) as photo, s.email, IF(s.is_active=1,'Active','Inactive') as status from supplier s where s.vendor_id='".$vendor_id."' and s.is_delete=0  order by s.supplier_id desc  ");
		
			}
			
			$result = $qry->result();
		
		if($result){
			$response['status'] = 1;
			//$response['message'] = '';
			$response['message']=$result;
		}else{
			$response['status'] = 0;
			//$response['message'] = 'Somethig wrong';
			$response['message']=array();
		}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	function base64_to_jpeg($base64_string, $output_file) {		//echo $output_file;die;
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
	
	public function addSupplier(){
		
		//echo "<pre>";print_r($_POST);exit;
		
		$vendor_id = $this->input->post('vendor_id');
		$photo = $this->input->post('photo');
		$name = addslashes($this->input->post('name'));
		$email = $this->input->post('email');
		$phone = $this->input->post('phone');
		$state_id = $this->input->post('state_id');
		$city = $this->input->post('city');
		$zipcode = $this->input->post('zipcode');
		$address = $this->input->post('address');
		$vendor_code = $this->input->post('vendor_code');
		
		if(!empty($vendor_id)){
			
			$checkName = $this->checkIfNameExist($vendor_id,$name);
			$checkVendorCode = $this->checkIfVendorCodeExist2($vendor_id,$vendor_code);
			if($checkName==1){
				$response['status'] = 0;
				$response['message'] = 'Vendor name already exists';
			}elseif($checkVendorCode==1){
				$response['status'] = 0;
				$response['message'] = 'Vendor code already exists';
				
			}else{
			
		if(!empty($_FILES['photo']['name'])){
			/*$path = '../assets/img/product/thumb/';
			$file = time().'.jpeg';
			$photo = $this->base64_to_jpeg($photo,$path.$file);
			$photo_name = $file;*/
			$photo = $_FILES['photo']['name'];
		$tmp_photo = $_FILES['photo']['tmp_name'];
		$path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/supplier/thumb/';
		$file = time().$photo;
			$photo = move_uploaded_file($tmp_photo,$path.$file);
			$photo_name = $file;
		
		}else{
			$photo_name = 'noimage.png';
		}
		
		
		$vendor_discontinue= $this->input->post('vendor_discontinue');
		$discontinue_date = $this->input->post('discontinue_date');
		
		if($discontinue_date=='' || $discontinue_date==NULL){
			$discontinue_date = '';
		}else{
			$discontinue_date = date('Y-m-d',strtotime($this->input->post('discontinue_date')));
			
		}

		$current_date = date('Y-m-d');
		if($discontinue_date==$current_date){
			$is_active = ",is_active=0";
		}else{
			$is_active = "";
		}

		
		/*if($vendor_discontinue=='0'){
			
			//$supplier_info =  $this->getSupplierById($supplier_id);
			$discontinue_date = $supplier_info->discontinue_date;
		}else{
			
			$discontinue_date = date('Y-m-d',strtotime($this->input->post('discontinue_date')));
		}
		*/
		
		$query = $this->db->query("insert into supplier set supplier_logo='".$photo_name."', supplier_name='".$name."', phone='".$phone."', address='".$address."', city='".$city."', state='".$state_id."', pincode='".$zipcode."', country='231',email='".$email."', vendor_id='".$vendor_id."',sales_rep_name = '".trim($this->input->post('sales_rep_name'))."',sales_rep_phone = '".trim($this->input->post('sales_rep_phone'))."',
            website= '".trim($this->input->post('website'))."', order_day1='".trim($this->input->post('order_day1'))."',order_day2 = '".trim($this->input->post('order_day2'))."',payment_option = '".trim($this->input->post('payment_option'))."',credit_term_days = '".trim($this->input->post('credit_term_days'))."',vendor_discontinue='".$vendor_discontinue."',discontinue_date='".$discontinue_date."',account_no='".$this->input->post('account_no')."', supplier_code='".$vendor_code."' $is_active ");
		$insert_id = $this->db->insert_id();
		if($insert_id){
			$response['status'] = 1;
			$response['message'] = 'Vendor added successfully';
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Something went wrong';
		}
		}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
			
		}
		echo json_encode($response);
		
	}
	
	
	public function editSupplier(){
		
	/*	$response['status'] = 0;
		$response['message'] = '';*/
		
		$vendor_id = $this->input->post('vendor_id');
		$supplier_id = $this->input->post('supplier_id');
		$photo = $this->input->post('photo');
		$name = addslashes($this->input->post('name'));
		$email = $this->input->post('email');
		$phone = $this->input->post('phone');
		$state_id = $this->input->post('state_id');
		$city = $this->input->post('city');
		$zipcode = $this->input->post('zipcode');
		$address = $this->input->post('address');
		$vendor_code = $this->input->post('vendor_code');
		
		if(!empty($vendor_id) && !empty($name) && !empty($email) && !empty($phone)){
			
			
			//echo "djhjd";die;
		if(!empty($_FILES['photo']['name'])){
			/*$path = '../assets/img/product/thumb/';
			$file = time().'.jpeg';
			$photo = $this->base64_to_jpeg($photo,$path.$file);
			$photo_name = $file;*/
			$photo = $_FILES['photo']['name'];
		$tmp_photo = $_FILES['photo']['tmp_name'];
		$path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/supplier/thumb/';
		$file = time().$photo;
			$photo = move_uploaded_file($tmp_photo,$path.$file);
			$photo_name = $file;
		
		}else{
			
			$supplier_info =  $this->getSupplierInfo($supplier_id);
			
			$photo_name = $supplier_info->supplier_logo;
		}
		
		$vendor_discontinue= $this->input->post('vendor_discontinue');
		
		$current_date = date('Y-m-d');

		if($vendor_discontinue=='0'){
			
			$supplier_info =  $this->getSupplierInfo($supplier_id);
			$discontinue_date = "";
			$is_active = ", is_active=1";
		}else{
			
			$discontinue_date = date('Y-m-d',strtotime($this->input->post('discontinue_date')));
		}

		if($discontinue_date==$current_date){
			$is_active = ", is_active=0";
		}else{
			$is_active = ", is_active=1";
			
		}
		
		$query = $this->db->query("update supplier set supplier_logo='".$photo_name."', supplier_name='".$name."', phone='".$phone."', address='".$address."', city='".$city."', state='".$state_id."', pincode='".$zipcode."', country='231',email='".$email."', vendor_id='".$vendor_id."',sales_rep_name = '".trim($this->input->post('sales_rep_name'))."',sales_rep_phone = '".trim($this->input->post('sales_rep_phone'))."',
            website= '".trim($this->input->post('website'))."', order_day1='".trim($this->input->post('order_day1'))."',order_day2 ='".trim($this->input->post('order_day2'))."',payment_option = '".trim($this->input->post('payment_option'))."',credit_term_days = '".trim($this->input->post('credit_term_days'))."',vendor_discontinue='".$vendor_discontinue."',discontinue_date='".$discontinue_date."',account_no='".$this->input->post('account_no')."', supplier_code='".$vendor_code."' $is_active where supplier_id='".$supplier_id."'  ");
		if($query){
			$response['status'] = 1;
			$response['message'] = 'Vendor updated successfully';
			
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
	
	
	public function getSupplierById(){
		
		$response['status'] = 0;
		$response['message'] = '';
			
		$vendor_id = $this->input->post('vendor_id');
		$supplier_id = $this->input->post('supplier_id');
		$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
		$path = $actual_link.'/assets/img/supplier/thumb';
		if(!empty($supplier_id)){
		$qry = $this->db->query("select s.supplier_id, s.supplier_name, s.supplier_code as vendor_code, s.phone, CONCAT('$path','/',s.supplier_logo) as photo, s.email, c.name as country_name,s.state as state_id,st.name as state_name,s.city, s.pincode, s.address,s.order_day1,s.order_day2,s.vendor_discontinue, s.discontinue_date, s.credit_term_days,s.payment_option,s.sales_rep_name,s.	sales_rep_phone,s.account_no,s.website from supplier s LEFT JOIN countries c ON c.id=s.country LEFT JOIN states st on st.id=s.state  where vendor_id='".$vendor_id."' and supplier_id='".$supplier_id."'  ");
	
		
		if($qry->num_rows()>0){
				$result = $qry->row();
			$response['status'] = 1;
			$response['message'] = $result;
		}else{
			$response['status'] = 0;
			$response['message'] = 'Somethig wrong';
		}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	public function getSupplierInfo($supplier_id){
		
	
		
		if(!empty($supplier_id)){
			
		$qry = $this->db->query("select s.supplier_id, s.supplier_name, s.phone, CONCAT('$path','/',s.supplier_logo) as photo,s.supplier_logo, s.email, c.name as country_name,s.state as state_id,st.name as state_name,s.city, s.pincode, s.address,s.order_day1,s.order_day2,s.vendor_discontinue, s.discontinue_date, s.credit_term_days,s.payment_option,s.sales_rep_name,s.	sales_rep_phone,s.account_no,s.website from supplier s LEFT JOIN countries c ON c.id=s.country LEFT JOIN states st on st.id=s.state  where supplier_id='".$supplier_id."'  ");
		$result = $qry->row();
		
		return $result;
	}
 }
	public function checkIfVendorAlreadyExist(){
		
		$vendor_id = $this->input->post('vendor_id');
		$email = $this->input->post('email');
		//$role_id = $this->input->post('role_id');
		
		/*$response['status'] = 0;
		$response['message'] = '';*/
		
		
		
			$query = $this->db->query("select email from supplier  where  vendor_id='".$vendor_id."' and email='".$email."' ");
			if($query->num_rows()>0){
				
				$response['status'] = 1;
				$response['message'] = 'Vendor email already exists';
			
			}else{
				$response['status'] = 0;
				$response['message'] = '';
			}
			
			echo json_encode($response);
	}
	public function checkIfVendorMobileExist(){
		
		$vendor_id = $this->input->post('vendor_id');
		$phone = $this->input->post('phone');
		$role_id = $this->input->post('role_id');
		
		
			$query = $this->db->query("select phone from supplier  where phone='".$phone."' and vendor_id='".$vendor_id."' ");
			if($query->num_rows()>0){
				
				$response['status'] = 1;
				$response['message'] ='Vendor mobile already exists';
			
			}else{
				$response['status'] = 0;
				$response['message'] = '';
			}
			
			echo json_encode($response);
	}
	
	public function checkIfNameExist($vendor_id,$name){
		
		$query = $this->db->query("select supplier_id from supplier where supplier_name='".$name."' and vendor_id='".$vendor_id."' and is_delete=0");
		$num = $query->num_rows();
		if($num>0){
			return 1;
		}else{
			return 0;
		}
		
	}
	
	public function checkIfVendorCodeExist(){
		
		$vendor_id = $this->input->post('vendor_id');
		$vendor_code = $this->input->post('vendor_code');
		
		$query= $this->db->query("select supplier_id from supplier where supplier_code='".$vendor_code."' and vendor_id='".$vendor_id."' ");
		$num = $query->num_rows();
		if($num>0){
			$response['status'] = 1;
			$response['message'] ='Vendor code already exists';
		}else{
			$response['status'] = 0;
			$response['message'] = '';
		}
		
		echo json_encode($response);
		
		
	}
	
	public function checkIfVendorCodeExist2($vendor_id,$vendor_code){
		
		
		
		$query= $this->db->query("select supplier_id from supplier where supplier_code='".$vendor_code."' and vendor_id='".$vendor_id."' ");
		$num = $query->num_rows();
		if($num>0){
			return 1;
		}else{
			return 0;
		}
		
		
		
		
	}
	
	
	public function getPaymentType(){
		
		$vendor_id = $this->input->post('vendor_id');
		
		
			$query = $this->db->query("select * from payment_type ");
			if($query->num_rows()>0){
				$result = $query->result();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] ='';
			
			}else{
				$response['status'] = 0;
				$response['result'] = '[]';
				$response['message'] = '';
			}
			
			echo json_encode($response);
	}
	
	
	public function getCreditTerms(){
		
		$vendor_id = $this->input->post('vendor_id');
		
		
			$query = $this->db->query("select * from credit_terms ");
			if($query->num_rows()>0){
				$result = $query->result();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] ='';
			
			}else{
				$response['status'] = 0;
				$response['result'] = '[]';
				$response['message'] = '';
			}
			
			echo json_encode($response);
	}
	
		
}
