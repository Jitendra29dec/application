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


class Service extends CI_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
		
		//error_reporting(0);
    }
	
	function get(){
		
		$vendor_id = $this->input->post('vendor_id');
		$type = $this->input->post('type');
		$response['status'] = 0;
		$response['message'] = '';

		if($type=='service'){
			$is_active = "";
		}elseif($type=='employee'){
			$is_active = " and s.is_active=1";
		}else{
			$is_active = "";
		}
			
			$query = $this->db->query("select s.service_id, s.service_name, s.sku, sc.category_name, s.price,s.duration, if(s.is_active=1,'Active','Inactive') as status,s.is_active, s.commission_type,s.commission_amount as commission from service s INNER JOIN service_category sc ON sc.category_id=s.category_id where s.is_delete='0' $is_active and s.vendor_id='".$vendor_id."' order by s.service_id DESC");
			
			if($query->num_rows()>0){
				$res = $query->result();
				
				$response['status'] = 1;
				$response['result'] = $res;
				
			}else{
				$response['status'] = 0;
				$response['result'] = [];
				$response['message'] = 'No data found';
			}
		
		echo json_encode($response);
	}
	
	public function getServiceCategory(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$type = $this->input->post('type');
		if(!empty($vendor_id)){

			if($type=='service_category'){
				$is_active = "";
			}elseif($type=='service'){
				$is_active = " and c.is_active=1";
			}else{
				$is_active = "";
			}
			
		$query = $this->db->query("select c.category_id, c.category_name, IF(c.is_active=1,'Active','Inactive') as status,  IFNULL(s.service_id,'0') AS is_used from service_category c LEFT JOIN service s ON s.category_id=c.category_id where  c.is_delete='0'  $is_active and c.vendor_id='".$vendor_id."' GROUP BY c.category_id order by c.category_id desc");
		
		$num = $query->num_rows();
		if($num>0){
			$result = $query->result();
			$response['status'] = 1;
			$response['result'] = $result;
		}else{
			$response['status'] = 0;
			$response['result'] = [];
			$response['message'] = 'No data found';
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
	
	
	
	
	public function add(){
		
		$vendor_id = $this->input->post('vendor_id');
		$category_id = $this->input->post('category_id');
		$service_name = addslashes($this->input->post('service_name'));
		$service_price = $this->input->post('service_price');
		$service_duration = $this->input->post('service_duration');
		$commission_type = $this->input->post('commission_type');
		$commission_amount = $this->input->post('commission_amount');
		$is_tax = $this->input->post("is_tax");
		 $service_point=$this->input->post('service_point');
		 if($is_tax=='1'){
			 
			 //tax=[{"tax_type":"1","tax_id":"1","tax_rate":"3%"},{"tax_type":"2","tax_id":"2","tax_rate":"4%"},{"tax_type":"3","tax_id":"3","tax_rate":"5%"}]
			
			
			// $tax =json_decode($this->input->post("tax"));
			
			 $tax_id =$this->input->post("tax");
			 $tax_rate =$this->input->post("tax_rate");
			 $tax_type =$this->input->post("tax_type");
			 
			 $exp_tax_id = explode(',',$tax_id);
			 $exp_tax_rate = explode(',',$tax_rate);
			 $exp_tax_type = explode(',',$tax_type);
			
			 
			 
		}else{
			 
			 $tax_id = 0;
			 $tax_type = 0;
			 $tax_rate = 0;
			  
			  
		 }
		 
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
		if(!empty($category_id) && !empty($service_name) && !empty($service_price) && !empty($service_duration)){
			
			$checkName = $this->checkIfNameExist($vendor_id,$service_name);
			if($checkName==1){
				
				$response['status'] = 0;
				$response['message'] = 'Service name already exists';
			}else{
			
			$query = $this->db->query("insert into service set category_id='".$category_id."', service_name='".$service_name."', price='".$service_price."', duration='".$service_duration."', is_active='1', is_delete='0',vendor_id='".$vendor_id."', commission_type='".$commission_type."', commission_amount='".$commission_amount."', is_tax='".$is_tax."', service_point='".$service_point."' ");
			
			$insert_id = $this->db->insert_id();
			if($insert_id){
				
				if($is_tax=='1'){
				
				for($i=0;$i<count($exp_tax_rate);$i++){
					
					$tax_amount =($service_price*$exp_tax_rate[$i])/100;
					
					
					
					$this->db->query("insert into multiple_tax set type='1', sp_id='".$insert_id."', tax_percent='".$exp_tax_rate[$i]."', tax_type='".$exp_tax_type[$i]."', tax_id='".$exp_tax_id[$i]."', tax_amount='".$tax_amount."'  ");
				}
				
				}
				
				
				$response['status'] = 1;
				$response['message'] = 'Service added successfully';
				
			}else{
				$response['status'] = 0;
				$response['message'] = 'Service not added';
			}
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		

	}
	
	function getServiceById(){
		
		
		$service_id = $this->input->post('service_id');
		$response['status'] = 0;
		$response['message'] = '';
		
	
			if(!empty($service_id)){
			
			$query = $this->db->query("select s.service_id,s.category_id, sc.category_name, s.service_name,IFNULL(s.sku,'') AS sku, s.price as service_price, s.duration as service_duration, s.commission_amount, s.commission_type, s.service_point, s.is_tax, (select GROUP_CONCAT(id)  from multiple_tax  where sp_id='".$service_id."') as mid, (select GROUP_CONCAT(tax_type)  from multiple_tax  where sp_id='".$service_id."') as tax_type, (select GROUP_CONCAT(tax_percent)  from multiple_tax  where sp_id='".$service_id."') as tax_rate,  (select GROUP_CONCAT(tax_id)  from multiple_tax  where sp_id='".$service_id."') as tax_id from service s INNER JOIN service_category sc ON sc.category_id=s.category_id where s.service_id='".$service_id."' ");
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
			
	
	public function edit(){
		
		$service_id = $this->input->post('service_id');
		$category_id = $this->input->post('category_id');
		$sku = $this->input->post('service_sku');
		$service_name =  addslashes($this->input->post('service_name'));
		$service_price = $this->input->post('service_price');
		$service_duration = $this->input->post('service_duration');
	
		$commission_type = $this->input->post('commission_type');
		$commission_amount = $this->input->post('commission_amount');
		$is_tax = $this->input->post("is_tax");
		$service_point=$this->input->post('service_point');
		 
		if($is_tax=='1'){
			
			
			 $mid =$this->input->post("mid");
			 $tax_id =$this->input->post("tax");
			 $tax_rate =$this->input->post("tax_rate");
			 $tax_type =$this->input->post("tax_type");
			 
			 $mid = explode(',',$mid);
			 $exp_tax_id = explode(',',$tax_id);
			 $exp_tax_rate = explode(',',$tax_rate);
			 $exp_tax_type = explode(',',$tax_type);
			 
			 
		 }else{
			 
			 $tax_id = 0;
			 $tax_rate = 0;
			 $tax_type = 0;
			  
			  
		 }
		 
		
		$response['status'] = 0;
		$response['message'] = '';
		
		if(!empty($category_id) && !empty($service_name) && !empty($service_price) && !empty($service_duration)){
			
			$stored_name = $this->getServiceNameById($service_id);
			if($stored_name!=$service_name){
				
				$checkName = $this->checkIfNameExist($vendor_id,$service_name);
				if($checkName==1){
					$response['status'] = 0;
					$response['message'] = 'Service name already exist';
					echo json_encode($response);die;
				}else{
					
					$service_name = $service_name;
				}
			}else{
				
				$service_name = $stored_name;
				
			}
			
			$query = $this->db->query("update service set category_id='".$category_id."', sku='".$sku."', service_name='".$service_name."', price='".$service_price."', duration='".$service_duration."', commission_type='".$commission_type."', commission_amount='".$commission_amount."', is_tax='".$is_tax."',tax_id='".$tax_id."', service_point='".$service_point."'  where service_id='".$service_id."' ");
			
			if($is_tax=='1'){
			
				$this->db->query("delete from multiple_tax where type='1' and sp_id='".$service_id."'");
				
				for($i=0;$i<count($exp_tax_rate);$i++){
						
						$tax_amount =($service_price*$exp_tax_rate[$i])/100;
						
							$this->db->query("insert into multiple_tax set sp_id='".$service_id."', type='1',  tax_percent='".$exp_tax_rate[$i]."', tax_type='".$exp_tax_type[$i]."', tax_id='".$exp_tax_id[$i]."', tax_amount='".$tax_amount."'  ");
						
				}
			}
				
			
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Service updated successfully';
				
			}else{
				$response['status'] = 0;
				$response['message'] = 'Service not updated';
			}
			
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		

	}
	
	public function delete(){
		
		$vendor_id = $this->input->post('vendor_id');
		$service_id = $this->input->post('service_id');
		
		if(!empty($vendor_id) && !empty($service_id)){
			
			
				$update = $this->db->query("update service set is_delete='1' where service_id='".$service_id."' and vendor_id='".$vendor_id."'");
				
				if($update){
					$response['status'] = 1;
					$response['message'] = 'Service deleted successfully';
					
				}else{
					$response['status'] = 0;
					$response['message'] = 'Service not deleted';
				}
		
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	public function addCategory(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$category_name = addslashes($this->input->post('category_name'));
		
		if(!empty($vendor_id) && !empty($category_name)){
			
			
			$checkName = $this->checkIfCategoryNameExist($vendor_id,$category_name);
			if($checkName==1){
				
				$response['status'] = 0;
				$response['message'] = 'Service category name already exists';
			}else{
				
				
			$query = $this->db->query("insert into service_category set category_name='".$category_name."', is_active='1', is_delete='0', created_date='".date('Y-m-d')."', vendor_id='".$vendor_id."' ");
			
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Service category added successfully';
				
			}else{
				$response['status'] = 0;
				$response['message'] = 'Service category not added';
			}
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		

	}
	
	public function editCategory(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		
		$category_id = $this->input->post('category_id');
		$category_name = addslashes($this->input->post('category_name'));
		
		if(!empty($category_id) && !empty($category_name)){
			
			$stored_name = $this->getCategoryNameById($category_id);
			if($stored_name!=$category_name){
				
				$checkName = $this->checkIfCategoryNameExist($vendor_id,$category_name);
				if($checkName==1){
					$response['status'] = 0;
					$response['message'] = 'Service category name already exists';
					echo json_encode($response);die;
				}else{
					
					$category_name = $category_name;
				}
			}else{
				
				$category_name = $stored_name;
				
			}
			
			
			$query = $this->db->query("update service_category set category_name='".$category_name."', modified_date='".date('Y-m-d')."' where category_id='".$category_id."' ");
			
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Service category updated successfully';
				
			}else{
				$response['status'] = 0;
				$response['message'] = 'Service category not updated';
			}
			
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		

	}
	
	public function getServiceCategoryById(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$category_id = $this->input->post('category_id');
		
		if(!empty($category_id)){
			
			$query = $this->db->query("select category_id, category_name from service_category where category_id='".$category_id."' ");
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
	
	
	public function deleteServiceCategory(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$category_id = $this->input->post('category_id');
		
		if(!empty($category_id)){
			
			$query = $this->db->query("update service_category set is_delete='1' where category_id='".$category_id."' ");
			
			if($query){
			
				$response['status'] = 1;
				$response['message'] = 'Service category deleted successfully';
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
	
	public function checkIfNameExist($vendor_id,$service_name){
		
		$query = $this->db->query("select service_id from service where service_name='".$service_name."' and vendor_id='".$vendor_id."' and is_delete=0 ");
		
		$num = $query->num_rows();
		if($num>0){
			return 1;
		}else{
			return 0;
		}
		
	}
	
	public function checkIfCategoryNameExist($vendor_id,$category_name){
		
		$query = $this->db->query("select category_id from service_category where category_name='".$category_name."' and vendor_id='".$vendor_id."' and is_delete=0 ");
		
		$num = $query->num_rows();
		if($num>0){
			return 1;
		}else{
			return 0;
		}
		
	}
	
	public function getServiceNameById($service_id){
		
		$query = $this->db->query("select service_name from service where service_id='".$service_id."' ");
		
		$service_name = $query->row()->service_name;
		return $service_name;
		
	}
	
	public function getCategoryNameById($category_id){
		
		$query = $this->db->query("select category_name from service_category where category_id='".$category_id."' ");
		
		$category_name = $query->row()->category_name;
		return $category_name;
		
	}
	
	public function activeDeacativeService(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$service_id = $this->input->post('service_id');
		$is_active = $this->input->post('is_active');
		
		if(!empty($service_id)){
			
			$query = $this->db->query("update service set is_active='".$is_active."' where service_id='".$service_id."' ");
			
			if($query){
				if($is_active=='1'){
					$msg = 'Service actived successfully';
				}else{
					$msg = 'Service deactived successfully';
				}
				$response['status'] = 1;
				$response['message'] = $msg;
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
	
	
}