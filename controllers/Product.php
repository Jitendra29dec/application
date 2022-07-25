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


class Product extends CI_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
		$this->load->helper('db');
		//error_reporting(0);
    }
	
	public function addCart(){
		
		$product_id = $this->input->post('product_id');
		$quantity = $this->input->post('quantity');
		$appointment_id = $this->input->post('appointment_id');
		
		
		if(!empty($product_id) && !empty($quantity) && !empty($appointment_id)){
			
		$product_ids  = explode(',',$product_id);
		$qty  = explode(',',$quantity);
		for($i=0;$i<count($product_ids);$i++){
			
			$this->db->query("insert into cart set product_id='".$product_ids[$i]."', quantity='".$qty[$i]."', appointment_id='".$appointment_id."', created_date='".date('Y-m-d H:i:s')."' ");
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
		
		$vendor_id = $this->input->post('vendor_id');
		$supplier_id = $this->input->post('supplier_id');
		
		$response['status'] = 0;
		$response['message'] = '';

		$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
		$path = $actual_link.'/assets/img/product/thumb';
			
		if(!empty($vendor_id)){
			
			if(!empty($supplier_id)){
				
				
				$sq = $this->db->query("select  group_concat(DISTINCT(product_id)) as product_id, supplier_sku, pack_cost, reorder_pack_qty, qty_in_pack from product_supplier where supplier_id='".$supplier_id."' ");
				
				$product_id = $sq->row()->product_id;
				

			

				//$qty_to_order = $p->par_value-$p->minimum_on_hand;

			//	$order_pack_qty = $qty_to_order/$qty_in_pack;

				$selQuery = "select p.product_id, p.product_name, CONCAT('$path','/',p.main_image) as product_image, p.barcode_id, p.description,p.price_retail,ps.pack_cost as purchase_price, p.quantity, p.supplier_id, c.category_name,ps.supplier_sku as sku, p.supplier_code as vendor_code,ps.qty_in_pack, round(((p.par_value-p.minimum_on_hand)/ps.qty_in_pack)) as order_pack_qty, b.brand_id,b.brand_name,p.created_date,p.tax_rate,p.vendor_id,p.max_quantity,p.low_qty_warning from product p INNER JOIN category c ON c.category_id=p.category_id INNER JOIN brand b on b.brand_id=p.brand_id INNER JOIN product_supplier ps ON ps.product_id=p.product_id where p.is_active='1' and p.is_delete='0' and p.vendor_id='".$vendor_id."' and ps.is_default='1'  AND p.product_id IN ($product_id) order by product_id DESC";

			}else{
				

				$selQuery = "select p.product_id, p.product_name, CONCAT('$path','/',p.main_image) as product_image, p.barcode_id, p.description,p.price_retail,p.purchase_price, p.quantity, p.supplier_id, c.category_name,p.sku, p.supplier_code as vendor_code,p.qty_in_pack, b.brand_id, b.brand_name,p.created_date,p.tax_rate,p.vendor_id,p.max_quantity,p.low_qty_warning from product p INNER JOIN category c ON c.category_id=p.category_id INNER JOIN brand b on b.brand_id=p.brand_id where p.is_active='1' and p.is_delete='0' and p.vendor_id='".$vendor_id."'  order by product_id DESC";

			}
			
		//$path = "http://159.203.182.165/salon/assets/img/product/thumb";

		

		
		
		
					
		 $result = $this->db->query($selQuery)->result();
		 
		 if(!empty($result)){
		 	 $response['status'] = 1;
		 $response['result'] = $result;
		 $response['message']='Success';
		}else{
			 $response['status'] = 0;
		 $response['result'] = array();
		 $response['message']='No data found';
		}
		
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
        echo json_encode($response);
		
	}
	public function scanBarcode(){
		
		$vendor_id = $this->input->post('vendor_id');
		$barcode  = $this->input->post('barcode');
		$customer_id  = $this->input->post('customer_id');
		$response['status'] = 0;
		$response['message'] = '';
			//inner join tax_product as tp on p.tax_id=tp.tax_id
		//p.price_retail*tp.tax_rate/100 as tax_rate 
		if(!empty($vendor_id) && !empty($barcode)){
			
		$path = "http://159.203.182.165/salon/assets/img/product/thumb";
		  $selQuery = "select p.product_id, p.product_name, CONCAT('$path','/',p.main_image) as product_image, p.barcode_id, p.description,p.price_retail,p.quantity, c.category_name,p.sku, b.brand_name,p.created_date from product p INNER JOIN category c ON c.category_id=p.category_id INNER JOIN brand b on b.brand_id=p.brand_id  where p.is_active='1' and p.is_delete='0' and p.vendor_id='".$vendor_id."' and p.barcode_id='".$barcode."' order by product_id DESC";
		
				
		 $result = $this->db->query($selQuery)->row();
		 if(!empty($result)){
		 	$query=$this->db->query('select cart_id,quantity from cart where product_id="'.$result->product_id.'" and customer_id="'.$customer_id.'"')->row();
		 	if(count($query)<=0){
		 	$this->db->query('insert into cart set product_id="'.$result->product_id.'",customer_id="'.$customer_id.'",quantity=1,appointment_id=0');	
		 	}else{
		 		$newQuant=$query->quantity+1;
		 		$this->db->query('update cart set quantity="'.$newQuant.'" where cart_id="'.$query->cart_id.'"');
		 	}
		 	
		 $response['status'] = 1;
		$response['result'] = $result;
		 $response['message']="Product added successfully";
		}else{
		 $response['status'] = 0;
		// $response['result'] = (Object)[];
		 $response['message']="No product available";
		}
		 
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
        echo json_encode($response);
		
	}
	public function getCart(){
		
		$appointment_id = $this->input->post('appointment_id');
		$path = "http://159.203.182.165/salon/assets/img/product/thumb";
		
		$response['status'] = 0;
		$response['message'] = '';
			
		if(!empty($appointment_id)){
		 
		 $selQuery = "select c.cart_id, c.quantity, p.product_name, CONCAT('$path','/',p.main_image) as product_image, p.barcode_id, p.price_retail from cart c INNER JOIN product p ON p.product_id=c.product_id where p.is_active='1'  and p.is_delete='0' AND c.appointment_id='".$appointment_id."' order by c.cart_id desc ";
		 
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
	
	public function addProduct(){
		//echo "<pre>";print_r($_POST);exit;
		$vendor_id = $this->input->post('vendor_id');
		$barcode_id = addslashes($this->input->post('barcode_id'));
		$brand_id = $this->input->post('brand_id');
		$category_id = $this->input->post('category_id');
		$product_name = addslashes($this->input->post('product_name'));
		$sku = $this->input->post('sku');
		$description = $this->input->post('description');
		$purchase_price = $this->input->post('purchase_price');
		$retail_price = $this->input->post('retail_price');
		$wholesale_price = $this->input->post('wholesale_price');
		$low_qty_warning = $this->input->post('low_qty_warning');

		$business_use = $this->input->post('business_use');
		/*new*/
		$quantity = $this->input->post('quantity');
		$qty_in_pack = $this->input->post('qty_in_pack');
		$commission_type = $this->input->post('commission_type');
		$commission_amount = $this->input->post('commission_amount');
		$supplier_id = $this->input->post('supplier_id');
		$max_quantity = $this->input->post('max_quantity');
		$package_cost = $this->input->post('package_cost');
		$vendor_code = $this->input->post('vendor_code');
		$is_tax = $this->input->post('is_tax');
		$tax = $this->input->post('tax');
		$product_discont = $this->input->post("product_discont");
        $date_discont = $this->input->post("date_discont");
        $minimum_on_hand = $this->input->post("minimum_on_hand");
        $par_value = $this->input->post("par_value");


        $getSupplier=json_decode($this->input->post('supplier_data'));
       // echo "<pre>";print_r($_POST);exit;
		if(!empty($vendor_id) && !empty($barcode_id) && !empty($brand_id) && !empty($category_id) && !empty($product_name)){
			
			$checkName = $this->checkProductNameExist($vendor_id,$product_name);
			$checkBarcode = $this->checkBarcodeExist($vendor_id,$barcode_id);
				
				if($checkName==1){
					$response['status'] = 3;
					$response['message'] = 'Product name already exists';
				}
				elseif($checkBarcode==1){
					$response['status'] = 2;
					$response['message'] = 'Barcode ID already exists';
				}
				else{
				
		if(!empty($_FILES['photo']['name'])){
			$photo = $_FILES['photo']['name'];
		$tmp_photo = $_FILES['photo']['tmp_name'];
		$path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/product/thumb/';
		$file = time().$photo;
			$photo = move_uploaded_file($tmp_photo,$path.$file);
			$photo_name = $file;
		
		}else{
			$photo_name = 'noimage.png';
		}
		
		$slug = url_title($product_name, 'dash', true);
		
		if($is_tax=='1'){
			 $tax_id =$this->input->post("tax");
			 $tax_rate =$this->input->post("tax_rate");
			 $tax_type =$this->input->post("tax_type");
			 
			 $exp_tax_id = explode(',',$tax_id);
			 $exp_tax_rate = explode(',',$tax_rate);
			 $exp_tax_type = explode(',',$tax_type);
			 
		 }else{
			 $tax_id = 0;
			 $tax_rate = 0;
			 $tax_type = 0;
		 }
			$qry = $this->db->query("insert into product set product_name='".$product_name."', main_image='".$photo_name."',barcode_id='".$barcode_id."', brand_id='".$brand_id."', category_id='".$category_id."', description='".addslashes($description)."', sku='".$sku."', purchase_price='".$purchase_price."', price_retail='".$retail_price."', business_use='".$business_use."', low_qty_warning='".$low_qty_warning."',quantity='".$quantity."',commission_type='".$commission_type."',commission_amount='".$commission_amount."',is_active='1', is_delete='0', created_date='".date('Y-m-d H:i:s')."', slug='".$slug."', vendor_id='".$vendor_id."', supplier_id='".$supplier_id."', is_tax='".$is_tax."', product_discont = '".$product_discont."', discont_date = '".$date_discont."',max_quantity='".$max_quantity."',package_cost='".$package_cost."', supplier_code='".$vendor_code."', qty_in_pack='".$qty_in_pack."', minimum_on_hand='".$minimum_on_hand."', par_value='".$par_value."' ");
			$insert_id = $this->db->insert_id();

			if($insert_id){
				if(!empty($getSupplier)){
					foreach ($getSupplier as $key => $value) {
						$this->db->query('insert into product_supplier set product_id="'.$insert_id .'",supplier_id="'.$value->supplier_id.'",supplier_sku="'.$value->supplier_sku.'",pack_cost="'.$value->pack_cost.'",qty_in_pack="'.$value->qty_in_pack.'",is_default="'.$value->is_default.'"');
						// code...
					}
				}
				
				
				if($is_tax=='1'){
					for($i=0;$i<count($exp_tax_rate);$i++){
						
						if($exp_tax_type[$i]!='' || $exp_tax_type[$i]!=NULL){

							$tax_amount =($retail_price*$exp_tax_rate[$i])/100;
						
							$this->db->query("insert into multiple_tax set type='2', sp_id='".$insert_id."', tax_percent='".$exp_tax_rate[$i]."', tax_type='".$exp_tax_type[$i]."', tax_id='".$exp_tax_id[$i]."', tax_amount='".$tax_amount."'  ");

						}
						
					}
				
				}
				
				
				$response['status'] = 1;
				$response['message'] = 'Product added successfully';
			}
			
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	public function brand(){
		
		$response['status'] = 0;
		$response['message'] = '';
			
		$vendor_id = $this->input->post('vendor_id');
		$type = $this->input->post('type');  // brand, product
		if(!empty($vendor_id)){

		if($type=='brand'){

			$qry = $this->db->query("select b.brand_id, b.brand_name, IF(b.is_active=1,'Active','Inactive') as status,(case when (select product_id from product where brand_id=b.brand_id order by product_id desc limit 1 ) then '1' ELSE '0' end) as is_used from brand b where b.vendor_id='".$vendor_id."' and b.is_delete='0'  order by b.brand_id desc ");

		}elseif($type=='product'){
			$qry = $this->db->query("select b.brand_id, b.brand_name from brand b where b.vendor_id='".$vendor_id."' and b.is_delete='0' and b.is_active='1'  order by b.brand_id desc ");
		}else{

			$qry = $this->db->query("select b.brand_id, b.brand_name, IF(b.is_active=1,'Active','Inactive') as status,(case when (select product_id from product where brand_id=b.brand_id order by product_id desc limit 1 ) then '1' ELSE '0' end) as is_used from brand b where b.vendor_id='".$vendor_id."' and b.is_delete='0'  order by b.brand_id desc ");
		}
		
		
		
		if($qry->num_rows()>0){
			$result = $qry->result();
			$response['status'] = 1;
			$response['message'] = $result;
		}else{
			$response['status'] = 0;
			$response['message'] = array();
		}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
			
		}
		echo json_encode($response);
	}
	
	public function category(){
		
		$response['status'] = 0;
		$response['message'] = '';
			
		$vendor_id = $this->input->post('vendor_id');
		
		$qry = $this->db->query("select c.category_id,c.category_name,c.description, IF(c.is_active=1,'Active','Inactive') as status,(case when (select product_id from product where category_id=c.category_id order by product_id desc limit 1 ) then '1' ELSE '0' end) as is_used from category c where c.vendor_id='".$vendor_id."' and c.is_delete='0' order by c.category_id desc ");
		$result = $qry->result();
		
		if($result){
			$response['status'] = 1;
			$response['result'] = $result;
			$response['message'] = '';
		}else{
			$response['status'] = 0;
			$response['message'] = 'Somethig wrong';
			$response['result'] = array();
		}
		echo json_encode($response);
	}
	
	public function getProductDetailById($product_id,$vendor_id){
		
		$qry = $this->db->query("select main_image, price_retail, purchase_price from product where product_id='".$product_id."' AND vendor_id='".$vendor_id."'");
		$res = $qry->row();
		return $res;
		
	}
	public function edit(){
		
		$product_id = $this->input->post('product_id');
		$vendor_id = $this->input->post('vendor_id');
		$barcode_id = addslashes($this->input->post('barcode_id'));
		$brand_id = $this->input->post('brand_id');
		$category_id = $this->input->post('category_id');
		$product_name = addslashes($this->input->post('product_name'));
		$sku = $this->input->post('sku');
		$description = $this->input->post('description');
		$purchase_price = $this->input->post('purchase_price');
		$retail_price = $this->input->post('retail_price');
		$wholesale_price = $this->input->post('wholesale_price');
		$low_qty_warning = $this->input->post('low_qty_warning');
		$photo = $this->input->post('photo');
		$business_use = $this->input->post('business_use');
		$quantity = $this->input->post('quantity');
		$qty_in_pack = $this->input->post('qty_in_pack');
		$commission_type = $this->input->post('commission_type');
		$commission_amount = $this->input->post('commission_amount');
		$tax_amount = $this->input->post('tax_amount');
		$supplier_id = $this->input->post('supplier_id');
		$max_quantity=$this->input->post('max_quantity');
		$package_cost=$this->input->post('package_cost');
		$is_tax = $this->input->post('is_tax');
		$tax = $this->input->post('tax');
		$vendor_code = $this->input->post('vendor_code');
		$qty_reason_id = $this->input->post('qty_reason_id');

		$minimum_on_hand = $this->input->post("minimum_on_hand");
        $par_value = $this->input->post("par_value");


		$getSupplier=json_decode($this->input->post('supplier_data'));
		
		if(!empty($product_id) && !empty($vendor_id) && !empty($barcode_id) && !empty($brand_id) && !empty($category_id) && !empty($product_name)){
			
			$stored_name = $this->checkNameByProductId($product_id,$product_name);
			if($stored_name!=$product_name){
				
				//$checkName = $this->checkProductNameExist($vendor_id,$product_name);
				
					
					$product_name = $product_name;
				
			}else{
				$product_name = $stored_name;
			}
			
		if(!empty($_FILES['photo']['name'])){
			/*$path = '../assets/img/product/thumb/';
			$file = time().'.jpeg';
			$photo = $this->base64_to_jpeg($photo,$path.$file);
			$photo_name = $file;*/
			$photo = $_FILES['photo']['name'];
		$tmp_photo = $_FILES['photo']['tmp_name'];
		$path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/product/thumb/';
		$file = time().$photo;
			$photo = move_uploaded_file($tmp_photo,$path.$file);
			$photo_name = $file;
		
		}else{
			$editData = $this->getProductDetailById($product_id,$vendor_id);
			$photo_name = $editData->main_image;
		}
		
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
		 
		 if($qty_reason_id==''){
			 
			 $qty_reason_id = 0;
		 }else{
			 $qty_reason_id = $qty_reason_id;
		 }
		
		$slug = url_title($product_name, 'dash', true);
			
			$qry = $this->db->query("update product set product_name='".$product_name."', main_image='".$photo_name."',barcode_id='".$barcode_id."', brand_id='".$brand_id."', category_id='".$category_id."', description='".addslashes($description)."', sku='".$sku."', purchase_price='".$purchase_price."', price_retail='".$retail_price."',  business_use='".$business_use."', low_qty_warning='".$low_qty_warning."',quantity='".$quantity."',commission_type='".$commission_type."',commission_amount='".$commission_amount."', modified_date='".date('Y-m-d H:i:s')."', slug='".$slug."', supplier_id='".$supplier_id."', is_tax='".$is_tax."', max_quantity='".$max_quantity."',package_cost='".$package_cost."', supplier_code='".$vendor_code."', qty_in_pack='".$qty_in_pack."', qty_reason_id='".$qty_reason_id."', minimum_on_hand='".$minimum_on_hand."', par_value='".$par_value."' where product_id='".$product_id."'  ");
			if($qry){
				foreach($getSupplier as $value){
					$checkRow=$this->db->query('select ps_id from product_supplier where ps_id="'.$value->ps_id.'"')->num_rows();
					if($checkRow >0){
						$this->db->query('update product_supplier set supplier_id="'.$value->supplier_id.'",supplier_sku="'.$value->supplier_sku.'",pack_cost="'.$value->pack_cost.'",qty_in_pack="'.$value->qty_in_pack.'",is_default="'.$value->is_default.'" where ps_id="'.$value->ps_id.'"');
					}else{
						$this->db->query('insert into product_supplier set product_id="'.$product_id .'",supplier_id="'.$value->supplier_id.'",supplier_sku="'.$value->supplier_sku.'",pack_cost="'.$value->pack_cost.'",qty_in_pack="'.$value->qty_in_pack.'",is_default="'.$value->is_default.'"');
					}
				}
				
				if($is_tax=='1'){
				$this->db->query("delete from multiple_tax where type='2' and sp_id='".$product_id."'");
				
				for($i=0;$i<count($exp_tax_rate);$i++){
					
					$tax_amount =($retail_price*$exp_tax_rate[$i])/100;
					
						$this->db->query("insert into multiple_tax set type='2', sp_id='".$product_id."',  tax_percent='".$exp_tax_rate[$i]."', tax_type='".$exp_tax_type[$i]."', tax_id='".$exp_tax_id[$i]."', tax_amount='".$tax_amount."'  ");
					
				}
				}
			
			
				$response['status'] = 1;
				$response['message'] = 'Product updated successfully';
			}
			
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
	}
	public function getTaxes(){
		$vendor_id=$this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$current_date = date('Y-m-d');
			
			$qq = $this->db->query("select * from tax_product where start_date='".$current_date."' and type='future' and vendor_id='".$vendor_id."' ");
			if($qq->num_rows()>0){
				
				$new_current_tax = $qq->result();
				
				foreach($new_current_tax as $nct){
					
					$this->db->query("update tax_product set tax_rate='".$nct->tax_rate."', start_date='".$nct->start_date."', description='".$nct->description."' where tax_type='".$nct->tax_type."' and type='current' and vendor_id='".$vendor_id."' ");
				}
				
				$this->db->query("update tax_product set tax_rate='0.00', start_date=NULL, description=NULL WHERE type='future' and vendor_id='".$vendor_id."'  ");
			}
			
			
			$query1 = $this->db->query("select p.tax_id, p.tax_type, p.tax_rate, p.start_date, p.description as tax_name from tax_product p where p.vendor_id='".$vendor_id."' and p.type='current' and p.tax_rate!='0.00' order by p.tax_id asc limit 0,3");
			
			$result = $query1->result();
			
			/* $query2 = $this->db->query("select s.tax_id, s.tax_type, s.tax_rate, DATE_FORMAT(s.start_date,'%m-%d-%Y') AS start_date, s.description as tax_name from tax_service s where s.vendor_id='".$vendor_id."' and s.type='current' order by s.tax_id asc limit 0,3");
			
			$result2 = $query2->result(); */
			
			
			// product_tax_future
			
			$query3 = $this->db->query("select p.tax_id, p.tax_type, p.tax_rate, p.start_date, p.description as tax_name from tax_product p where p.vendor_id='".$vendor_id."' and p.type='future' and p.tax_rate!='0.00' order by p.tax_id asc limit 0,3");
			
			$product_tax_future = $query3->result();
			
			// service_tax_future
			
			/* $query4 = $this->db->query("select s.tax_id, s.tax_type, s.tax_rate, DATE_FORMAT(s.start_date,'%m-%d-%Y') AS start_date, s.description as tax_name from tax_service s where s.vendor_id='".$vendor_id."' and s.type='future' order by s.tax_id asc limit 0,3");
			
			$service_tax_future = $query4->result(); */
			
			
		if(!empty($result)){
			$response['status'] = 1;
		$response['result']=$result;
		//$response['service_tax']=$result2;
		
		$response['future_tax_product']=$product_tax_future;
		//$response['future_tax_service']=$service_tax_future;
		
		
		$response['message'] = 'Data found';
	}else{
		$response['status'] = 0;
		$response['result']=array();
		$response['message'] = 'No Data found';
	}
		
	}else{
		$response['status'] = 0;
	$response['message'] = 'Required parameter missing';
	}
	echo json_encode($response);	
		
	}
	public function delete(){
		
		$product_id = $this->input->post('product_id');
		
		$response['status'] = 0;
		$response['message'] = '';
				
		if(!empty($product_id)){
			
			$delete = $this->db->query("update product set is_delete='1' where product_id='".$product_id."' ");
			if($delete){
				$response['status'] = 1;
				$response['message'] = 'Product updated successfully';
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
	
	public function getProductById(){
		
		$vendor_id = $this->input->post('vendor_id');
		$product_id = $this->input->post('product_id');
		
		$response['status'] = 0;
		$response['message'] = '';
			
		if(!empty($product_id) && !empty($vendor_id)){
		$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
		$path = $actual_link.'/assets/img/product/thumb';
		
		
		 $selQuery = "select p.product_id, p.product_name,p.supplier_code as vendor_code,b.brand_name,p.qty_in_pack, CONCAT('$path','/',p.main_image) as product_image, p.barcode_id, p.description,p.purchase_price, c.category_name, p.package_cost,p.category_id,p.brand_id, p.sku, p.purchase_price as cost_price,p.price_retail as selling_price,p.low_qty_warning,p.business_use,p.quantity as quantity_In_pack, p.commission_type,p.commission_amount,p.is_tax, p.supplier_id,p.barcode_id,p.last_purchase_price,p.commission_amount,p.commission_type,p.last_purchase_price,p.tax_amount,p.max_quantity,p.product_discont,p.discont_date as discontinue_date,p.business_use, p.qty_reason_id, minimum_on_hand, par_value,  IFNULL((select GROUP_CONCAT(id)  from multiple_tax  where sp_id='".$product_id."'),'') as mid, IFNULL((select GROUP_CONCAT(tax_type)  from multiple_tax  where sp_id='".$product_id."'),'') as tax_type, IFNULL((select GROUP_CONCAT(tax_percent)  from multiple_tax  where sp_id='".$product_id."'),'') as tax_rate,  IFNULL((select GROUP_CONCAT(tax_id)  from multiple_tax  where sp_id='".$product_id."'),'') as tax_id from product p INNER JOIN category c ON c.category_id=p.category_id INNER JOIN brand b on b.brand_id=p.brand_id where p.is_active='1' and p.is_delete='0' and p.vendor_id='".$vendor_id."' and p.product_id='".$product_id."' ";
		 $result = $this->db->query($selQuery)->row();
		 $getSupplierList=$this->db->query('select ps_id,supplier_id,supplier_sku,pack_cost,qty_in_pack,reorder_pack_qty,is_default from product_supplier where product_id="'.$product_id.'"')->result();
		 if(!empty($getSupplierList)){
		 	$$getSupplierList=$getSupplierList;
		 }else{
		 	$getSupplierList=array();
		 }
		 
		 $response['status'] = 1;
		 $response['result'] = $result;
		 $response['supplier_list'] = $getSupplierList;
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
        echo json_encode($response);
		
	}
	
	public function addBrand(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$brand_name = addslashes($this->input->post('brand_name'));
		
		if(!empty($brand_name)){
			
		$checkName = $this->checkBrandNameExist($vendor_id,$brand_name);
				
				if($checkName==1){
					$response['status'] = 0;
					$response['message'] = 'Brand name already exists';
				}else{
		
		$query = $this->db->query("insert into brand set brand_name='".$brand_name."', is_active='1', vendor_id='".$vendor_id."' ");
		if($query){
			$response['status'] = 1;
			$response['message'] = 'Brand added successfully';
			
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
	
	
	public function editBrand(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$brand_id = $this->input->post('brand_id');
		$brand_name = addslashes($this->input->post('brand_name'));
		
		if(!empty($brand_name)){
		
		$stored_name = $this->getBrandNameById($brand_id);
			if($stored_name!=$brand_name){
				
				$checkName = $this->checkBrandNameExist($vendor_id,$brand_name);
				if($checkName==1){
					$response['status'] = 0;
					$response['message'] = 'Brand name already exist';
					echo json_encode($response);die;
				}else{
					
					$brand_name = $brand_name;
				}
			}else{
				
				$brand_name = $stored_name;
				
			}
			
			
		$query = $this->db->query("update brand set brand_name='".$brand_name."' where vendor_id='".$vendor_id."' and brand_id='".$brand_id."' ");
		if($query){
			$response['status'] = 1;
			$response['message'] = 'Brand updated successfully';
			
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
	
	
	public function addCategory(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$category_name = addslashes($this->input->post('category_name'));
		$description = $this->input->post('description');
		$photo = $this->input->post('photo');
		
		
		$path = $_SERVER['DOCUMENT_ROOT'] . '/salon/assets/img/category/thumb/';
		
		
		if(!empty($vendor_id)){
			
				
		$checkName = $this->checkCategoryNameExist($vendor_id,$category_name);
				
				if($checkName==1){
					$response['status'] = 0;
					$response['message'] = 'Product category name already exists';
				}else{
			
			
		if(!empty($photo)){
			
			$file = time().'.jpeg';
			$photo = $this->base64_to_jpeg($photo,$path.$file);
			$photo_name = $file;
			
			
		}else{
			$photo_name = 'avtar.png';
			
		}
		
		
		$query = $this->db->query("insert into category set category_name='".$category_name."', description='".addslashes($description)."', image='".$photo_name."', vendor_id='".$vendor_id."', is_active='1' ");
		if($query){
			$response['status'] = 1;
			$response['message'] = 'Category added successfully';
			
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
	
	
	public function editCategory(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$category_id = $this->input->post('category_id');
		$category_name = addslashes($this->input->post('category_name'));
		$description = $this->input->post('description');
		$photo = $this->input->post('photo');
		
		
		//$path = $_SERVER['DOCUMENT_ROOT'] . '/salon/assets/img/category/thumb/';
		
		
		if(!empty($category_id)){
			
			$stored_name = $this->getCategoryNameById($category_id);
			if($stored_name!=$category_name){
				
				$checkName = $this->checkCategoryNameExist($vendor_id,$category_name);
				if($checkName==1){
					$response['status'] = 0;
					$response['message'] = 'Product category name already exist';
					echo json_encode($response);die;
				}else{
					
					$category_name = $category_name;
				}
			}else{
				
				$category_name = $stored_name;
				
			}
			
			
		if(!empty($photo)){
			$path = '../assets/img/category/thumb/';
			$file = time().'.jpeg';
			$photo = $this->base64_to_jpeg($photo,$path.$file);
			$photo_name = $file;
			
		}else{
			$photo_name = 'avtar.png';
			
		}
		
		
		$query = $this->db->query("update category set category_name='".addslashes($category_name)."', description='".addslashes($description)."', image='".$photo_name."' where  category_id='".$category_id."'");
		if($query){
			$response['status'] = 1;
			$response['message'] = 'Category udpated successfully';
			
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
	
	public function getCategoryById(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$category_id = $this->input->post('category_id');
		
		if(!empty($vendor_id) && !empty($category_id)){
			
			$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
		$photo = $actual_link.'/salon/assets/img/category/thumb/';
		
		$query = $this->db->query("select category_id, category_name, description, CONCAT('$photo',image) AS photo from category where category_id='".$category_id."' and vendor_id='".$vendor_id."'");
		if($query->num_rows()>0){
			$result = $query->row();
			$response['status'] = 1;
			$response['result'] = $result;
			$response['message'] = '';
			
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
	
	
	public function getBrandById(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$brand_id = $this->input->post('brand_id');
		
		$query = $this->db->query("select brand_id, brand_name from brand where brand_id='".$brand_id."' and vendor_id='".$vendor_id."'");
		if($query->num_rows()>0){
			$result = $query->row();
			$response['status'] = 1;
			$response['result'] = $result;
			$response['message'] = '';
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Something went wrong';
		}
		echo json_encode($response);
		
	}
	
	
	public function addPO(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = '{}';
		$object_array = '[]';
		
		$vendor_id = $this->input->post('vendor_id');
		$supplier_id = $this->input->post('supplier_id');
		$po_date = $this->input->post('po_date');
		//$po_number = $this->input->post('po_number');
		$button_type = $this->input->post('button_type');
		
		$product_detail = $this->input->post('product_detail');
		
		$product_detail = json_decode($product_detail);
		$po_date = date('Y-m-d',strtotime($po_date));
		
		if(!empty($vendor_id) && !empty($supplier_id) && !empty($po_date) && !empty($product_detail)){
		
		
		$check_num = $this->db->query("select count(po_number) as num from purchase_order where vendor_id='".$vendor_id."' ")->row();
		if($check_num>0){
			
			$q = $this->db->query("select max(po_number) as po_number from purchase_order where vendor_id='".$vendor_id."' order by po_number desc limit 0,1 ");
			$pn = $q->row()->po_number;
			$po_number = $pn+1; 
		
		}else{
			$po_number = 1;
		}
	
		if($button_type=='save'){
			$status = '1';
		}else if($button_type=='save_and_send'){
			
			$status = '4';
			
		}
		
		$query = $this->db->query("insert into purchase_order set supplier_id='".$supplier_id."', po_date='".$po_date."', po_number='".$po_number."', vendor_id='".$vendor_id."', created_by='".$vendor_id."', created_date='".date('Y-m-d H:i:s')."', status='".$status."' ");
		$insert_id = $this->db->insert_id();
		
		if($insert_id){
		
			foreach($product_detail as $product){
				
				$product_id = $product->product_id;
				
				$product_info = $this->getProductDetailById($product_id,$vendor_id);

				$product_supplier_info = $this->db->query("select pack_cost from product_supplier where product_id='".$product_id."' and is_default='1' ")->row();

				$pack_cost = $product_supplier_info->pack_cost;

				//$price = $product_info->purchase_price;
				$price = $pack_cost;
				
				//$amount = $price*($product->quantity);
				$amount = $pack_cost*($product->quantity);
				
				$brand_id = $product->brand_id;
				
				
			//	$sub_total = $amount + $tax_amount;
				//[{"product_id":"1","brand_id":"25","price":"100","quantity":"10","purchase_price":"80"}]
				$query = $this->db->query("insert into purchase_order_detail set po_id='".$insert_id."', product_id='".$product_id."',brand_id='".$brand_id."', price='".$price."', quantity='".$product->quantity."', purchase_price='".$price."', amount='".$amount."'  ");
				
				if($query){
					$response['status'] = 1;
					$response['message'] = 'PO created successfully';


					if($button_type=='save_and_send'){
			
						
						
						
					//	$getPoData=$this->db->query("select v.vendor_name as admin_name, po.po_number, date_format(po.created_date,'%c-%d-%Y') AS order_date, s.supplier_name as vendor_name,v.phone as admin_phone from purchase_order po INNER JOIN vendor v ON v.vendor_id=po.vendor_id INNER JOIN supplier s ON s.supplier_id=po.supplier_id where po.po_id='".$po_id."'")->row();

					//	$data['business_info'] = $this->getSalonDetail($vendor_id);
						$supplier_info = $this->getSupplierById($supplier_id);
						$email = $supplier_info->email;
						$data['supplier_email'] = $email;
						$data['po_number'] = $po_number;
						$data['account_no'] = $supplier_info->account_no;
						$data['order_date'] = $po_date;
						$data['vendor_name'] = $po_date;
						$data['supplier_phone'] = $supplier_info->phone;
						$data['supplier_address'] = $supplier_info->address;
						$data['product_detail'] = $product_detail;
						
						$emailTemplate = $this->load->view('email_template/po_email_template',$data,TRUE);
						$subject = "Purchase Order";
						
						
						$this->load->library('Send_mail');
						$this->send_mail->sendMail($email, $subject, $emailTemplate, $fileName=false, $filePath=false, $cc=false);

						
						/* $search1  = array('{Admin First Name}','{PO Number}','{PO Order Date}','{Vendor}');
						$employee_name=$getStylistData->stylist_name;
						$replace1 = array($getPoData->admin_name,$getPoData->po_number,$getPoData->order_date,$getPoData->vendor_name);
						$getDataNew=getImageTemplate($vendor_id,'email_to_admin_when_salon_receive_po');
						$getsmsData=str_replace($search1, $replace1, $getDataNew->sms_content); */

						//test($getsmsData,$getPoData->admin_phone);
						 
						
					}


				}else{
					$response['status'] = 0;
					$response['message'] = 'Something went wrong';
				}
			}
			
			
		}
		
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
		
	}

	public function getSupplierById($supplier_id){

		$query = $this->db->query("select email,phone, account_no, address from supplier where supplier_id='".$supplier_id."' ");
		$result = $query->row();
		return $result;

	}
	
	
	public function POlist(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = '{}';
		$object_array = [];
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			//$query = $this->db->query("select po.*,DATE_FORMAT(po.po_date,'%m/%d/%Y') AS po_date,s.supplier_name from purchase_order po LEFT JOIN supplier s ON s.supplier_id=po.supplier_id where  po.vendor_id='".$vendor_id."' AND po.is_delete='0' order by po.po_id desc ");
			
			$query = $this->db->query("select po.po_id, IFNULL(po.po_number,'') AS po_number,DATE_FORMAT(po.po_date,'%c-%d-%Y') AS po_date,DATE_FORMAT(po.receive_date,'%c-%d-%Y') AS receive_date, s.supplier_id, s.supplier_name, po.status, IFNULL(SUM(pod.quantity),'0') as order_qty, IFNULL(SUM(pod.receive_qty),'0') AS receive_qty, IFNULL((sum(pod.quantity)-sum(pod.receive_qty)),'0') as pending_qty, CASE WHEN (pod.quantity-pod.receive_qty)=0 THEN 'CLOSE' WHEN (pod.quantity-pod.receive_qty)=NULL THEN 'OPEN' WHEN po.status=1 THEN 'CREATED' WHEN po.status=4 THEN 'ORDERED' WHEN po.status=5 THEN 'FORCE CLOSE' ELSE 'OPEN' END AS status2 from purchase_order po LEFT JOIN supplier s ON s.supplier_id=po.supplier_id INNER JOIN purchase_order_detail pod ON pod.po_id=po.po_id where  po.vendor_id='".$vendor_id."' AND po.is_delete='0' GROUP BY po_id order by po.po_id desc ");
			if($query->num_rows()>0){
				$result = $query->result();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}else{
				$response['status'] = 0;
				$response['result'] = $object_array;
				$response['message'] = 'No data found';
				
			}
			
		
		}else{
			
			$response['status'] = 0;
			$response['result'] = $object_array;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
	}
	
	
	function getReceivedPO(){
	
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = '{}';
		$object_array = '[]';
		
		$vendor_id = $this->input->post('vendor_id');
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select rpo.receive_id, rpo.po_id,rpo.receive_date, rpo.note,po.po_date,po.po_number from receive_purchase_order rpo inner join purchase_order po on po.po_id=rpo.po_id where po.vendor_id='".$vendor_id."' ");
			$result = $query->result();
			
			if($query->num_rows()>0){
				
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
				
			}else{
				
				$response['status'] = 0;
				$response['result'] = $object_array;
				$response['message'] = 'No record found';
				
			}
		
		}else{
			
			$response['status'] = 0;
			$response['result'] = $object_array;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
	}
	
	public function getPObyPOnumber(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = '{}';
		$object_array = '[]';
		
		$vendor_id = $this->input->post('vendor_id');
		$po_number = $this->input->post('po_number');
		
		if(!empty($vendor_id) && !empty($po_number)){
			
			$query = $this->db->query("select po.po_id, po.supplier_id, s.supplier_name, po.po_date, po.po_number from purchase_order po LEFT JOIN supplier s ON s.supplier_id=po.supplier_id where po.vendor_id='".$vendor_id."' AND po.po_number='".$po_number."' ");
			
			
			if($query->num_rows()>0){
			
				$result = $query->row();
				$po_id = $result->po_id;
			
				$q = $this->db->query("select pod.po_detail_id,po.po_id, pod.product_id, p.product_name, pod.price, pod.quantity, pod.tax_type, pod.tax_amount, pod.amount from purchase_order_detail pod LEFT JOIN product p ON p.product_id=pod.product_id INNER JOIN purchase_order po ON po.po_id=pod.po_id where pod.po_id='".$po_id."' ");
				
				$po_detail = $q->result();
				
				$response['status'] = 1;
				$response['result'] = $result;
				$response['po_detail'] = $po_detail;
				$response['message'] = '';
				
			}else{
				
				$response['status'] = 0;
				$response['result'] = $object_array;
				$response['po_detail'] = $object_array;
				$response['message'] = 'No record found';
				
			}
		
		}else{
			
			$response['status'] = 0;
			$response['result'] = $object_array;
			$response['po_detail'] = $object_array;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
	}
	
	
	public function editPO(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = '{}';
		$object_array = '[]';
		
		$vendor_id = $this->input->post('vendor_id');
		$po_number = $this->input->post('po_number');
		
		$supplier_id = $this->input->post('supplier_id');
		$po_date = $this->input->post('po_date');
		
		$product_detail = $this->input->post('product_detail');
		$po_id = $this->input->post('po_id');
		$button_type = $this->input->post('button_type');
		
		
		
		if(!empty($vendor_id) && !empty($supplier_id) && !empty($po_date) && !empty($product_detail)){
		
		$product_detail = json_decode($product_detail);
		$po_date = date('Y-m-d',strtotime($po_date));
		
		if($button_type=='save'){
			$status = '1';
		}else if($button_type=='save_and_send'){
			
			$status = '4';
		}
		
		$query = $this->db->query("update purchase_order set supplier_id='".$supplier_id."', po_date='".$po_date."', vendor_id='".$vendor_id."', modified_by='".$vendor_id."', modified_date='".date('Y-m-d H:i:s')."', status='".$status."' where po_id='".$po_id."' ");
		
		
		if($query){
	
			foreach($product_detail as $product){
				
				$product_id = $product->product_id;
				$product_info = $this->getProductDetailById($product_id,$vendor_id);
				//$price = $product_info->price_retail;
				//$amount = $price*($product->quantity);
				
				
				$product_id = $product->product_id;
				
				$product_info = $this->getProductDetailById($product_id,$vendor_id);
				//$price = $product_info->purchase_price;
				
				//$amount = $price*($product->quantity);


				$product_supplier_info = $this->db->query("select pack_cost from product_supplier where product_id='".$product_id."' and is_default='1' ")->row();

				$pack_cost = $product_supplier_info->pack_cost;

				//$price = $product_info->purchase_price;
				$price = $pack_cost;
				
				//$amount = $price*($product->quantity);
				$amount = $pack_cost*($product->quantity);
				
				$brand_id = $product->brand_id;
				
				
			//[{"po_detail_id":"16","product_id":"1","brand_id":"25","price":"100","quantity":"10","purchase_price":"80"}]
			
			if($product->po_detail_id==''){


				$query = $this->db->query("insert into purchase_order_detail set  product_id='".$product_id."', purchase_price='".$price."',price='".$price."', quantity='".$product->quantity."', amount='".$amount."', po_id='".$po_id."'  ");
			}else{

				$query = $this->db->query("update purchase_order_detail set  product_id='".$product_id."', purchase_price='".$price."',price='".$price."', quantity='".$product->quantity."', amount='".$amount."' where po_detail_id='".$product->po_detail_id."' ");
			
			}
				
				
			if($query){
					$response['status'] = 1;
					$response['message'] = 'PO updated successfully';
				}else{
					$response['status'] = 0;
					$response['message'] = 'Somethig went wrong';
				}
			}
			/* $response['status'] = 1;
			$response['message'] = 'PO updated successfully'; */
		
			
			
		}
		
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
		
	}
	
	
	public function deletePO(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$po_id = $this->input->post('po_id');
		
		if(!empty($po_id) && !empty($vendor_id)){
		
		
		
		$query = $this->db->query("DELETE purchase_order, purchase_order_detail
		FROM purchase_order
		INNER JOIN purchase_order_detail ON purchase_order.po_id = purchase_order_detail.po_id
		WHERE purchase_order.vendor_id='".$vendor_id."' and purchase_order.po_id='".$po_id."' ");
		if($query){
			
			$response['status'] = 1;
			$response['message'] = 'PO deleted successfully';
			
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
	
	
	
	public function getProductIdByPOdetailId($po_detail_id){
		
		$product = $this->db->query("select *  from purchase_order_detail where po_detail_id='".$po_detail_id."' ")->row();
		return $product;
		
	}
	
	
	public function receivePO(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$po_id = $this->input->post('po_id');
		$receive_date = $this->input->post('receive_date');
		$product_detail = $this->input->post('product_detail');
		
		if(!empty($po_id) && !empty($vendor_id) && !empty($receive_date) && !empty($product_detail)){
		
		$receive_date = date('Y-m-d',strtotime($receive_date));
		$product_detail = json_decode($product_detail);
		
		//echo '<pre>';print_r($product_detail);die;
		$query = $this->db->query("update purchase_order set status='2', receive_date='".$receive_date."' where po_id='".$po_id."' and vendor_id='".$vendor_id."' ");
		
		if($query){
		
			foreach($product_detail as $product){
				
				$amount = 0;
				$receive_qty = $product->receive_qty;
				$po_detail_id = $product->po_detail_id;
				$price = $product->price; // this is order price
				$receive_price = $product->receive_price;
				//[{"po_detail_id":"1","receive_qty":"25"}]
				
				$amount = $receive_price*$receive_qty;
				$podInfo = $this->getProductIdByPOdetailId($po_detail_id);
				
				$product_id = $podInfo->product_id;
				$brand_id = $podInfo->brand_id;

				// get qty in pack of preferred vendor code start here
				
				$preffered_vendor = $this->db->query("select qty_in_pack from product_supplier where product_id='".$product_id."'  and is_default='1' ")->row();
				$qty_in_pack = $preffered_vendor->qty_in_pack;

				$actual_receive_qty = $qty_in_pack*$receive_qty;

				// get qty in pack of preferred vendor code end here


				$this->db->query("update product set quantity=quantity+$actual_receive_qty where product_id='".$product_id."' ");
				
				$query = $this->db->query("update purchase_order_detail set receive_qty=receive_qty+$receive_qty, receive_date='".$receive_date."', receive_price='".$receive_price."', is_auto_generate='0' where po_detail_id='".$po_detail_id."' ");
				
				//$query = $this->db->query("insert into purchase_order_detail set po_id='".$po_id."', product_id='".$product_id."', brand_id='".$brand_id."', price='".$podInfo->price."', quantity='".$podInfo->quantity."', receive_qty='".$receive_qty."', receive_date='".$receive_date."', purchase_price='".$price."', receive_price='".$receive_price."', amount='".$amount."'  ");
			
			}
				$response['status'] = 1;
				$response['message'] = 'PO received successfully';
				/*$getStylistData=$this->getStylistInfoById($stylist);
        	    $data['business_info'] = $this->getSalonDetail($vendor_id);*/
        	    $getPoData=$this->db->query("select v.vendor_name as admin_name, po.po_number, date_format(po.created_date,'%c-%d-%Y') AS order_date, s.supplier_name as vendor_name,v.phone as admin_phone from purchase_order po INNER JOIN vendor v ON v.vendor_id=po.vendor_id INNER JOIN supplier s ON s.supplier_id=po.supplier_id where po.po_id='".$po_id."'")->row();
        	    $search1  = array('{Admin First Name}','{PO Number}','{PO Order Date}','{Vendor}');
				$employee_name=$getStylistData->stylist_name;
				$replace1 = array($getPoData->admin_name,$getPoData->po_number,$getPoData->order_date,$getPoData->vendor_name);
				$getDataNew=getImageTemplate($vendor_id,'email_to_admin_when_salon_receive_po');
				$getsmsData=str_replace($search1, $replace1, $getDataNew->sms_content);
				test($getsmsData,$getPoData->admin_phone);
			
		}
		
		
		}
		
		else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
	}
	
	
	public function receivePOlist(){
		
		$response['status'] = 0;
		$response['message'] = '';
		//$object = {};
		$object_array = [];
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			//select po.po_id, po.po_number, DATE_FORMAT(po.receive_date,'%m/%d/%Y') AS receive_date, (CASE WHEN IS NOT NULL SUM(pod.quantity) THEN SUM(pod.quantity) ELSE '0' END) as order_qty, (CASE WHEN IS NOT NULL SUM(pod.receive_qty) THEN SUM(pod.receive_qty) ELSE '0' END)  AS receive_qty, (CASE WHEN IS NOT NULL (sum(pod.quantity)-sum(pod.receive_qty) THEN (sum(pod.quantity)-sum(pod.receive_qty) ELSE '0' END) as pending_qty, CASE WHEN (pod.quantity-pod.receive_qty)=0 THEN 'CLOSE' WHEN (pod.quantity-pod.receive_qty)=NULL THEN 'OPEN' ELSE 'OPEN' END AS status2,DATE_FORMAT(po.po_date,'%m/%d/%Y') AS po_date,sp.supplier_name from purchase_order po LEFT JOIN purchase_order_detail pod ON pod.po_id=po.po_id inner join supplier as sp on sp.supplier_id=po.supplier_id where po.vendor_id='".$vendor_id."' and po.status='2' GROUP BY po_id order by po.po_id DESC 
			$query = $this->db->query("select po.po_id, po.po_number, DATE_FORMAT(po.receive_date,'%m/%d/%Y') AS receive_date, (CASE WHEN pod.quantity IS NOT NULL THEN SUM(pod.quantity) ELSE '0' END) as order_qty, (CASE WHEN SUM(pod.receive_qty) IS NOT NULL  THEN SUM(pod.receive_qty) ELSE '0' END)  AS receive_qty, (CASE WHEN pod.quantity IS NOT NULL  THEN (sum(pod.quantity)-sum(pod.receive_qty)) ELSE '0' END) as pending_qty, CASE WHEN (pod.quantity-pod.receive_qty)=0 THEN 'CLOSE' WHEN (pod.quantity-pod.receive_qty)=NULL THEN 'OPEN' ELSE 'OPEN' END AS status2,DATE_FORMAT(po.po_date,'%m/%d/%Y') AS po_date,sp.supplier_name from purchase_order po LEFT JOIN purchase_order_detail pod ON pod.po_id=po.po_id inner join supplier as sp on sp.supplier_id=po.supplier_id where po.vendor_id='".$vendor_id."' and (po.status='2' OR po.status='3') GROUP BY po_id order by po.po_id DESC");
			if($query->num_rows()>0){
				$result = $query->result();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}else{
				$response['status'] = 0;
				$response['result'] = $object_array;
				$response['message'] = 'No data found';
				
			}
			
		
		}else{
			
			$response['status'] = 0;
			$response['result'] = $object_array;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
	}
	
	public function deleteBrand(){
		
		$response['status'] = 0;
		$response['message'] = '';
			
		$vendor_id = $this->input->post('vendor_id');
		$brand_id = $this->input->post('brand_id');
		
		if(!empty($vendor_id) && !empty($brand_id)){
		
		$qry = $this->db->query("update brand set is_delete='1' where brand_id='".$brand_id."' AND vendor_id='".$vendor_id."' ");
		
		
		if($qry){
			$response['status'] = 1;
			$response['message'] = 'Brand deleted successfully';
		}else{
			$response['status'] = 0;
			$response['message'] = 'Somethig wrong';
		}
		}else{
			
			$response['status'] = 0;
			$response['result'] = $object_array;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	public function deleteProductCategory(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$category_id = $this->input->post('category_id');
		
		if(!empty($category_id)){
			
			$query = $this->db->query("update category set is_delete='1' where category_id='".$category_id."' ");
			
			if($query){
			
				$response['status'] = 1;
				$response['message'] = 'Product category deleted successfully';
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
	
	
	
	public function getPObyPOID(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = '{}';
		$object_array = '[]';
		
		$vendor_id = $this->input->post('vendor_id');
		$po_id = $this->input->post('po_id');
		
		if(!empty($vendor_id) && !empty($po_id)){
			
			$getMaxPoNumber = $this->db->query("select max(po_number) as max_po_number from purchase_order where vendor_id='".$vendor_id."' order by po_id desc limit 0,1")->row();

			$max_po = $getMaxPoNumber->max_po_number+1;

			$query = $this->db->query("select po.po_id, po.supplier_id, s.supplier_name, DATE_FORMAT(po.po_date,'%c-%d-%Y') as po_date, po.po_number, $max_po as po_number_new from purchase_order po LEFT JOIN supplier s ON s.supplier_id=po.supplier_id where po.vendor_id='".$vendor_id."' AND po.po_id='".$po_id."' ");
			
			
			if($query->num_rows()>0){
			
				$result = $query->row();
				
				$q = $this->db->query("select pod.po_detail_id,po.po_id, pod.product_id, p.product_name,p.sku, b.brand_id, b.brand_name, pod.purchase_price, pod.quantity as order_quantity, ps.qty_in_pack, pod.amount,IFNULL((pod.quantity-pod.receive_qty),'0') as pending_qty from purchase_order_detail pod LEFT JOIN product p ON p.product_id=pod.product_id LEFT JOIN brand b ON b.brand_id=pod.brand_id INNER JOIN purchase_order po ON po.po_id=pod.po_id INNER JOIN product_supplier ps ON ps.product_id=pod.product_id where pod.po_id='".$po_id."' AND ps.is_default='1' ");
				
				$po_detail = $q->result();
				
				$response['status'] = 1;
				$response['result'] = $result;
				$response['po_detail'] = $po_detail;
				$response['message'] = '';
				
			}else{
				
				$response['status'] = 0;
				$response['result'] = $object_array;
				$response['po_detail'] = $object_array;
				$response['message'] = 'No record found';
				
			}
		
		}else{
			
			$response['status'] = 0;
			$response['result'] = $object_array;
			$response['po_detail'] = $object_array;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
	}
	
	
	public function returnPO(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$po_id = $this->input->post('po_id');
		$return_date = $this->input->post('return_date');
		$product_detail = $this->input->post('product_detail');
		
		if(!empty($po_id) && !empty($vendor_id) && !empty($return_date) && !empty($product_detail)){
		
		$return_date = date('Y-m-d',strtotime($return_date));
		$product_detail = json_decode($product_detail);
		
		$query = $this->db->query("update purchase_order set status='3', return_date='".$return_date."' where po_id='".$po_id."' and vendor_id='".$vendor_id."' ");
		
		if($query){
		
			foreach($product_detail as $product){
				
				$return_qty = $product->return_qty;
				$po_detail_id = $product->po_detail_id;
				$reason = $product->return_reason;
				//[{"po_detail_id":"1","return_qty":"25","return_reason":"product was not good"}]
				
				$pod_info = $this->getProductIdByPOdetailId($po_detail_id);
				$product_id = $pod_info->product_id;
				$brand_id = $pod_info->brand_id;

				// get qty in pack of preferred vendor code start here
				
				$preffered_vendor = $this->db->query("select qty_in_pack from product_supplier where product_id='".$product_id."'  and is_default='1' ")->row();
				$qty_in_pack = $preffered_vendor->qty_in_pack;

				$actual_return_qty = $qty_in_pack*$return_qty;

				// get qty in pack of preferred vendor code end here


				$this->db->query("update product set quantity=quantity-$actual_return_qty where product_id='".$product_id."' ");
				
				$query = $this->db->query("update purchase_order_detail set return_qty='".$return_qty."',receive_qty=receive_qty-$return_qty, return_date='".$return_date."', return_reason='".$reason."' where po_detail_id='".$po_detail_id."' ");
			}
			$response['status'] = 1;
			$response['message'] = 'PO returned successfully';
			
		}
		
		
		}
		
		else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
	}
	
	
	public function returnPOlist(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = '{}';
		$object_array = [];
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select po.po_id, po.po_number, DATE_FORMAT(po.created_date,'%m-%d-%Y') as po_date, DATE_FORMAT(po.receive_date,'%m-%d-%Y') AS receive_date, DATE_FORMAT(po.return_date,'%m-%d-%Y') AS return_date, s.supplier_name as vendor_name from purchase_order po LEFT JOIN supplier s ON s.supplier_id=po.supplier_id where po.vendor_id='".$vendor_id."' AND po.status='3' order by po.po_id DESC ");
			if($query->num_rows()>0){
				$result = $query->result();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}else{
				$response['status'] = 0;
				$response['result'] = $object_array;
				$response['message'] = 'No data found';
				
			}
			
		
		}else{
			
			$response['status'] = 0;
			$response['result'] = $object_array;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
	}
		
		
		
	public function getReceivedPODetailByPOID(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = '{}';
		$object_array = '[]';
		

		
		$vendor_id = $this->input->post('vendor_id');
		$po_id = $this->input->post('po_id');
		
		if(!empty($vendor_id) && !empty($po_id)){
			
			$query = $this->db->query("select po.po_id, po.supplier_id, s.supplier_name, DATE_FORMAT(po.po_date,'%c/%d/%Y') AS po_date, DATE_FORMAT(po.receive_date,'%c/%d/%Y') AS receive_date, po.po_number from purchase_order po LEFT JOIN supplier s ON s.supplier_id=po.supplier_id where po.vendor_id='".$vendor_id."' AND po.po_id='".$po_id."' ");
			
			
			if($query->num_rows()>0){
			
				$result = $query->row();
				
				$q = $this->db->query("select pod.po_detail_id,po.po_id, pod.product_id, p.product_name,b.brand_id, b.brand_name, ps.qty_in_pack, pod.quantity as order_quantity,pod.receive_qty, pod.purchase_price, pod.amount, IFNULL(pod.receive_price,'0') as receive_item_price, pod.return_reason from purchase_order_detail pod LEFT JOIN product p ON p.product_id=pod.product_id LEFT JOIN brand b ON b.brand_id=pod.brand_id INNER JOIN purchase_order po ON po.po_id=pod.po_id LEFT JOIN product_supplier ps ON ps.product_id=pod.product_id where pod.po_id='".$po_id."' and ps.is_default='1' ");
				
				$po_detail = $q->result();
				
				$response['status'] = 1;
				$response['result'] = $result;
				$response['po_detail'] = $po_detail;
				$response['message'] = '';
				
			}else{
				
				$response['status'] = 0;
				$response['result'] = $object_array;
				$response['po_detail'] = $object_array;
				$response['message'] = 'No record found';
				
			}
		
		}else{
			
			$response['status'] = 0;
			$response['result'] = $object_array;
			$response['po_detail'] = $object_array;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
	}


	public function getReturnPODetailByPOID(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = '{}';
		$object_array = '[]';
		
		$vendor_id = $this->input->post('vendor_id');
		$po_id = $this->input->post('po_id');
		
		if(!empty($vendor_id) && !empty($po_id)){
			
			$query = $this->db->query("select po.po_id, po.supplier_id, s.supplier_name, DATE_FORMAT(po.po_date,'%m/%d/%Y') AS po_date, DATE_FORMAT(po.receive_date,'%m/%d/%Y') AS receive_date, DATE_FORMAT(po.return_date,'%m/%d/%Y') AS return_date, po.po_number from purchase_order po LEFT JOIN supplier s ON s.supplier_id=po.supplier_id where po.vendor_id='".$vendor_id."' AND po.po_id='".$po_id."' ");
			
			
			if($query->num_rows()>0){
			
				$result = $query->row();
				
				$q = $this->db->query("select pod.po_detail_id,po.po_id, pod.product_id, p.product_name,b.brand_id, b.brand_name, p.quantity as qty_in_pack, pod.quantity as order_quantity,pod.receive_qty,pod.return_qty, pod.purchase_price, pod.return_reason from purchase_order_detail pod LEFT JOIN product p ON p.product_id=pod.product_id LEFT JOIN brand b ON b.brand_id=pod.brand_id INNER JOIN purchase_order po ON po.po_id=pod.po_id where pod.po_id='".$po_id."' ");


				
				
				$po_detail = $q->result();
				
				$response['status'] = 1;
				$response['result'] = $result;
				$response['po_detail'] = $po_detail;
				$response['message'] = '';
				
			}else{
				
				$response['status'] = 0;
				$response['result'] = $object_array;
				$response['po_detail'] = $object_array;
				$response['message'] = 'No record found';
				
			}
		
		}else{
			
			$response['status'] = 0;
			$response['result'] = $object_array;
			$response['po_detail'] = $object_array;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
	}



	public function deletePoProductDetail(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$po_detail_id = $this->input->post('po_detail_id');
		
		if(!empty($po_detail_id)){
			
			$query = $this->db->query("delete from purchase_order_detail where po_detail_id='".$po_detail_id."' ");
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Product deleted successfully';
			
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
		
	}


	public function checkIfBarcodeIdExist(){
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$barcode_id = $this->input->post('barcode_id');
		
		if(!empty($vendor_id) && !empty($barcode_id)){
			
			$query = $this->db->query("select count(product_id) as num from product where barcode_id='".$barcode_id."' and vendor_id='".$vendor_id."' ");
			
			$res = $query->row()->num;
			
			if($res>0){
				
				$response['status'] = 1;
				$response['message'] = 'Barcode id already exists';
			}else{
				
				$response['status'] = 0;
				$response['message'] = '';
			}
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
		
	}


	public function checkIfPoNumberExist(){
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$po_number = $this->input->post('po_number');
		
		if(!empty($vendor_id) && !empty($po_number)){
			
			$query = $this->db->query("select count(po_number) as num from purchase_order where po_number='".$po_number."' and vendor_id='".$vendor_id."' ");
			
			$res = $query->row()->num;
			
			if($res==0){
				
				$response['status'] = 1;
				$response['message'] = 'New PO Number';
			}else{
				
				$response['status'] = 0;
				$response['message'] = 'PO number already exists';
			}
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
		
	}
	
	public function checkIfPoNumberExist_2($vendor_id,$po_number){
		
			$query = $this->db->query("select count(po_number) as num from purchase_order where po_number='".$po_number."' and vendor_id='".$vendor_id."' ");
			
			$num = $query->row()->num;
			return $num;
			
		
		
	}
	
	
	public function getScale(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select scale_id, scale_name from product_scale where vendor_id='".$vendor_id."' ");
			
			
			if($query->num_rows()>0){
				
				$result = $query->result();
				
				$response['status'] = 1;
				$response['scale'] = $result;
				$response['message'] = '';
				
			}else{
				
				$response['status'] = 0;
				$response['scale'] = [];
				$response['message'] = 'No data found';
			}
		}else{
			
			$response['status'] = 0;
			$response['scale'] = [];
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}

	public function checkProductNameExist($vendor_id,$product_name){
		
		$query = $this->db->query("select product_id from product where product_name='".$product_name."' and vendor_id='".$vendor_id."' and is_delete=0 ");
		$num = $query->num_rows();
		if($num>0){
			return 1;
		}else{
			return 0;
		}
		
	}

	public function checkBarcodeExist($vendor_id,$barcode_id){
		
		$query = $this->db->query("select product_id from product where barcode_id='".$barcode_id."' and vendor_id='".$vendor_id."' and is_delete=0 ");
		$num = $query->num_rows();
		if($num>0){
			return 1;
		}else{
			return 0;
		}
		
	}
	
	

	public function checkBrandNameExist($vendor_id,$brand_name){
		
		$query = $this->db->query("select brand_id from brand where brand_name='".$brand_name."' and vendor_id='".$vendor_id."' and is_delete=0 ");
		$num = $query->num_rows();
		if($num>0){
			return 1;
		}else{
			return 0;
		}
		
	}
	
	public function checkNameByProductId($product_id,$product_name){
		
		$query = $this->db->query("select product_id,product_name from product where product_name='".$product_name."' and product_id='".$product_id."' ");
		
		$res = $query->row();
		$product_name = $res->product_name;
		return $product_name;
		
		
	}
	
	
	
	public function getBrandNameById($brand_id){
		
		$query = $this->db->query("select brand_id,brand_name from brand where brand_id='".$brand_id."' ");
		
		$res = $query->row();
		$brand_name = $res->brand_name;
		return $brand_name;
		
		
	}
	
	
	
	public function getCategoryNameById($category_id){
		
		$query = $this->db->query("select category_id,category_name from category where category_id='".$category_id."' ");
		
		$res = $query->row();
		$category_name = $res->category_name;
		return $category_name;
		
		
	}
	
	
	public function checkCategoryNameExist($vendor_id,$category_name){
		
		$query = $this->db->query("select category_id from category where category_name='".$category_name."' and vendor_id='".$vendor_id."' and is_delete=0 ");
		$num = $query->num_rows();
		if($num>0){
			return 1;
		}else{
			return 0;
		}
		
	}
	
	
	
	public function reorderPO_old(){
		
		

		$response['status'] = 0;
		$response['message'] = '';
		$object = '{}';
		$object_array = '[]';
		
		$vendor_id = $this->input->post('vendor_id');
		$po_id = $this->input->post('po_id');
		
		if(!empty($vendor_id) && !empty($po_id) ){
		
	/* 	$q = $this->db->query("select max(po_number) as po_number from purchase_order");
		$pn = $q->row()->po_number;
		$po_number = $pn+1; */
		
		$po_detail = $this->getPOdetailByPOid($po_id);
		$product_detail = $this->getPOproductInfoById($po_id);
		$po_number = $po_detail->po_number+1;
		
		$query = $this->db->query("insert into purchase_order set supplier_id='".$po_detail->supplier_id."', po_date='".$po_detail->po_date."', po_number='".$po_number."', vendor_id='".$po_detail->vendor_id."', created_by='".$po_detail->vendor_id."', created_date='".date('Y-m-d H:i:s')."', status='1', reorder_id='".$po_id."' ");
		$insert_id = $this->db->insert_id();
		
		if($insert_id){
		
			foreach($product_detail as $product){
				
			//	$sub_total = $amount + $tax_amount;
				//[{"product_id":"1","brand_id":"25","price":"100","quantity":"10","purchase_price":"80"}]
				$query = $this->db->query("insert into purchase_order_detail set po_id='".$insert_id."', product_id='".$product->product_id."',brand_id='".$product->brand_id."', price='".$product->price."', quantity='".$product->quantity."', purchase_price='".$product->price."', amount='".$product->amount."'  ");
				
				if($query){
					$response['status'] = 1;
					$response['message'] = 'PO reordered successfully';
				}else{
					$response['status'] = 0;
					$response['message'] = 'Something went wrong';
				}
			}
			
			
		}
		
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
		
	}
	
	
	public function reorderPO(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = '{}';
		$object_array = '[]';
		
		$vendor_id = $this->input->post('vendor_id');
		$po_number = $this->input->post('po_number');
		
		$supplier_id = $this->input->post('supplier_id');
		$po_date = $this->input->post('po_date');
		
		$product_detail = $this->input->post('product_detail');
		$po_id = $this->input->post('po_id');
		
		$product_detail = json_decode($product_detail);
		$po_date = date('Y-m-d',strtotime($po_date));
		
		if(!empty($vendor_id) && !empty($supplier_id) && !empty($po_date) && !empty($product_detail)){
		
		
		/* $query = $this->db->query("update purchase_order set supplier_id='".$supplier_id."', po_date='".$po_date."', vendor_id='".$vendor_id."', modified_by='".$vendor_id."', modified_date='".date('Y-m-d H:i:s')."' where po_id='".$po_id."' ");
		
		
		if($query){
	
			foreach($product_detail as $product){
				
				$product_id = $product->product_id;
				$product_info = $this->getProductDetailById($product_id,$vendor_id);
				$price = $product_info->price_retail;
				$amount = $price*($product->quantity);
				
				
			//[{"po_detail_id":"16","product_id":"1","brand_id":"25","price":"100","quantity":"10","purchase_price":"80"}]
			
				$query = $this->db->query("update purchase_order_detail set  product_id='".$product_id."', purchase_price='".$price."',price='".$price."', quantity='".$product->quantity."', amount='".$amount."' where po_detail_id='".$product->po_detail_id."' ");
				
				if($query){
					$response['status'] = 1;
					$response['message'] = 'PO updated successfully';
				}else{
					$response['status'] = 0;
					$response['message'] = 'Somethig went wrong';
				}
			}
			
		
			 */
			 
			 
			 $query = $this->db->query("insert into purchase_order set supplier_id='".$supplier_id."', po_date='".$po_date."', po_number='".$po_number."', vendor_id='".$vendor_id."', created_by='".$vendor_id."', created_date='".date('Y-m-d H:i:s')."', status='1', reorder_id='".$po_id."' ");
			 $insert_id = $this->db->insert_id();
		
		if($insert_id){
		
			foreach($product_detail as $product){
				
				$product_id = $product->product_id;
				
				$product_info = $this->getProductDetailById($product_id,$vendor_id);
				//$price = $product_info->price_retail;
				
			//	$amount = $price*($product->quantity);
				

				$brand_id = $product->brand_id;



				$product_supplier_info = $this->db->query("select qty_in_pack,pack_cost from product_supplier where product_id='".$product_id."' and is_default='1' ")->row();

						$qty_in_pack = $product_supplier_info->qty_in_pack;
						$price = $product_supplier_info->pack_cost;
		
							//$qty_to_order = $par_value-$minimum_on_hand;
							
							$qty_to_order = $p->par_value-$p->minimum_on_hand;

							$order_pack_qty = $qty_to_order/$qty_in_pack;
							//$order_qty = 10;
							
							$pack_cost = $product_supplier_info->pack_cost;
							$amount = $order_pack_qty*$pack_cost;
							
				
				
			//	$sub_total = $amount + $tax_amount;
				//[{"product_id":"1","brand_id":"25","price":"100","quantity":"10","purchase_price":"80"}]
				$query = $this->db->query("insert into purchase_order_detail set po_id='".$insert_id."', product_id='".$product_id."',brand_id='".$brand_id."', price='".$price."', quantity='".$product->quantity."', purchase_price='".$price."', amount='".$amount."'  ");
				
				if($query){
					$response['status'] = 1;
					$response['message'] = 'PO reorder successful';
				}else{
					$response['status'] = 0;
					$response['message'] = 'Something went wrong';
				}
			}
			
			
		}
		
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
		
	}
	
	
	
	public function getPOdetailByPOid($po_id){
		
		$query = $this->db->query("select supplier_id, po_date, po_number, created_by, vendor_id, receive_date, return_date from purchase_order where po_id='".$po_id."' ");
		$res = $query->row();
		return $res;
		
	}
	
	public function getPOproductInfoById($po_id){
		
		$query = $this->db->query("select product_id, brand_id, price, quantity, receive_qty, amount, purchase_price from purchase_order_detail where po_id='".$po_id."' ");
		
		$result = $query->result();
		return $result;
	}
	
	
	public function viewPOdetail(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = '{}';
		$object_array = '[]';
		
		$po_id = $this->input->post('po_id');
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($po_id) && !empty($vendor_id)){
			
			$query = $this->db->query("select po.po_id, po.supplier_id, s.supplier_name as vendor, DATE_FORMAT(po.po_date,'%m-%d-%Y') as po_date, po.po_number,date_format(po.receive_date,'%m-%d-%Y') as receive_date from purchase_order po LEFT JOIN supplier s ON s.supplier_id=po.supplier_id where po.vendor_id='".$vendor_id."' AND po.po_id='".$po_id."' ");
			
			
			if($query->num_rows()>0){
			
				$result = $query->row();
				
				$q = $this->db->query("select pod.po_detail_id,po.po_id, pod.product_id,  p.product_name,p.supplier_code as vendor_code, b.brand_id, b.brand_name, pod.price, ps.qty_in_pack, pod.quantity as order_quantity, pod.amount,date_format(po.receive_date,'%m-%d-%Y') as receive_date,ps.supplier_sku as sku from purchase_order_detail pod LEFT JOIN product p ON p.product_id=pod.product_id LEFT JOIN brand b ON b.brand_id=pod.brand_id INNER JOIN purchase_order po ON po.po_id=pod.po_id INNER JOIN product_supplier ps ON ps.product_id=pod.product_id  where pod.po_id='".$po_id."' and ps.is_default='1' ");
				
				$q2 = $this->db->query("select pod.po_detail_id,po.po_id, pod.product_id, p.product_name,p.supplier_code as vendor_code,b.brand_id, b.brand_name, pod.price, pod.quantity as order_quantity, pod.amount, ps.qty_in_pack, pod.receive_qty, DATE_FORMAT(pod.receive_date,'%m-%d-%Y') as receive_date from purchase_order_detail pod LEFT JOIN product p ON p.product_id=pod.product_id LEFT JOIN brand b ON b.brand_id=pod.brand_id INNER JOIN purchase_order po ON po.po_id=pod.po_id INNER JOIN product_supplier ps ON ps.product_id=pod.product_id  where pod.po_id='".$po_id."' and po.receive_date!=''  and ps.is_default='1' ");
				
				$q3 = $this->db->query("select pod.po_detail_id,po.po_id, pod.product_id, p.product_name,p.supplier_code as vendor_code,b.brand_id, b.brand_name, pod.price, pod.quantity as order_quantity, pod.amount, ps.qty_in_pack, pod.receive_qty, pod.return_qty,pod.return_reason, DATE_FORMAT(pod.return_date,'%m-%d-%Y') as return_date from purchase_order_detail pod LEFT JOIN product p ON p.product_id=pod.product_id LEFT JOIN brand b ON b.brand_id=pod.brand_id INNER JOIN purchase_order po ON po.po_id=pod.po_id INNER JOIN product_supplier ps ON ps.product_id=pod.product_id  where pod.po_id='".$po_id."' and po.return_date!=''  and ps.is_default='1' ");
				
				
				$po_detail = $q->result();
				$receive_detail = $q2->result();
				$return_detail = $q3->result();
				
				$response['status'] = 1;
				$response['result'] = $result;
				$response['order_detail'] = $po_detail;
				$response['receive_detail'] = $receive_detail;
				$response['return_detail'] = $return_detail;
				$response['message'] = '';
				
			}else{
				
				$response['status'] = 0;
				$response['result'] = $object_array;
				$response['order_detail'] = $object_array;
				$response['message'] = 'No record found';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
	}
	
	
	private function generateRandomNumber($numberRand)
    {
        //To Pull 7 Unique Random Values Out Of AlphaNumeric

        //removed number 0, capital o, number 1 and small L
        //Total: keys = 32, elements = 33
        $characters = array("1", "2", "3", "4", "5", "6", "7", "8", "9");

        //make an "empty container" or array for our keys
        $keys = array();

        //first count of $keys is empty so "1", remaining count is 1-6 = total 7 times
        while (count($keys) < $numberRand) {
            //"0" because we use this to FIND ARRAY KEYS which has a 0 value
            //"-1" because were only concerned of number of keys which is 32 not 33
            //count($characters) = 33
            $x = mt_rand(0, count($characters) - 1);
            if (!in_array($x, $keys)) {
                $keys[] = $x;
            }
        }
        $random_chars = '';
        foreach ($keys as $key) {
            $random_chars .= $characters[$key];
        }
        return $random_chars;
    }
	

	public function checkIfProductAutoGenerated($product_id){

		$query = $this->db->query("select count(product_id) as num from purchase_order_detail where product_id='".$product_id."' AND is_auto_generate='1' ");
		$num = $query->row()->num;

		return $num;
	}
	
	public function autoGeneratePO(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = '{}';
		$object_array = '[]';
		
		$vendor_id = $this->input->post('vendor_id');
		
		
		if(!empty($vendor_id)){
		
		$low_qty_product_query = $this->db->query("select p.product_id, p.product_name, ps.supplier_id, p.brand_id, p.price_retail as price, p.minimum_on_hand, p.par_value from product p LEFT JOIN product_supplier ps ON ps.product_id=p.product_id where p.quantity<p.low_qty_warning and p.vendor_id='".$vendor_id."' and ps.is_default=1 ");
		
		if($low_qty_product_query->num_rows()>0){
			
			$low_qty_product = $low_qty_product_query->result();
			
			$po_date = date('Y-m-d');
			$po_number = $this->generateRandomNumber(5);
			$check_po = $this->checkIfPoNumberExist_2($vendor_id, $po_number);
			if($check_po==0){
				
				$i = 0;
				foreach($low_qty_product as $k=>$p){
					
					

					// check if product already auto generated

					$check_auto_generate_num = $this->checkIfProductAutoGenerated($p->product_id);
				
					if($check_auto_generate_num=='0'){ 
						$po_number_final = $po_number.$i;
						$query = $this->db->query("insert into purchase_order set supplier_id='".$p->supplier_id."', po_date='".$po_date."', po_number='".$po_number_final."', vendor_id='".$vendor_id."', created_by='".$vendor_id."', created_date='".date('Y-m-d H:i:s')."', status='1' ");

					
					
					
				
					$insert_id = $this->db->insert_id();
					
					if($insert_id){


						$product_supplier_info = $this->db->query("select qty_in_pack,pack_cost from product_supplier where product_id='".$p->product_id."' and is_default='1' ")->row();

						$qty_in_pack = $product_supplier_info->qty_in_pack;
						$pack_cost = $product_supplier_info->pack_cost;
		
							//$qty_to_order = $par_value-$minimum_on_hand;
							
							$qty_to_order = $p->par_value-$p->minimum_on_hand;

							$order_pack_qty = $qty_to_order/$qty_in_pack;
							//$order_qty = 10;
							
							$pack_cost = $product_supplier_info->pack_cost;
							$amount = $order_pack_qty*$pack_cost;
							

							//$get_auto_generated_product = $this->db->query("select pod.is_auto_generate from purchase_order_detail pod where pod.product_id='".$p->product_id."' ")->row();

							//if($get_auto_generated_product->is_auto_generate=='0'){

								$query = $this->db->query("insert into purchase_order_detail set po_id='".$insert_id."', product_id='".$p->product_id."',brand_id='".$p->brand_id."', price='".$pack_cost."', quantity='".$order_pack_qty."', purchase_price='".$pack_cost."', amount='".$amount."', is_auto_generate='1'  ");

							//}

							
							
							
						
					
					
					}
				}
				
				$i++;
				}
				
				
					$response['status'] = 1;
					$response['message'] = 'Purchase order created successfully';
							
		
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'We have enough quantity for now';
		}
		
		
		
		
		
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
		
	}
	
	public function inventoryUpdateReason(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select * from inventory_reason order by reason_id asc");
			$result = $query->result();
			
			$response['status'] = 1;
			$response['result'] = $result;
			$response['message'] = '';
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
	}


	public function forceClosePO(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$po_id = $this->input->post('po_id');
		$reason = $this->input->post('reason');
		
		if(!empty($vendor_id) && !empty($po_id)){
			
			$query = $this->db->query("update purchase_order set status='5', close_reason='".$reason."' where po_id='".$po_id."' and vendor_id='".$vendor_id."' ");
			if($query){

				$response['status'] = 1;
				$response['message'] = 'PO force closed successfully';

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
