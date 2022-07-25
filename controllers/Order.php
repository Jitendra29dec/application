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


class Order extends CI_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
		
		//error_reporting(0);
    }
	
	function get(){
		
		$vendor_id = $this->input->post('vendor_id');
		
		$response['status'] = 0;
		$response['message'] = '';
		if(!empty($vendor_id)){
		$selQuery = $this->db->query("SELECT
                          DISTINCT(o.order_id),
                          IF(CONCAT(c.firstname,' ',c.lastname) IS NULL,'N/A',CONCAT(c.firstname,' ',c.lastname)) customer_name,
						  IF(p.payment_type = 1, 'Cash', 'Card') AS payment_type,
                          p.status_id payment_status,
                          p.transaction_id,
                          p.response_code,
                          p.amount payment_amount,
                          p.message payment_message,
                          DATE_FORMAT(p.created_date,'%m/%d/%Y') as payment_date,
                          IF(o.status_id=1,'Pending','Completed') order_status,
                          o.order_type,
                          o.order_number,
                          o.order_amount,
                          o.coupon_id,
                          IF(o.coupon_id IS NULL,'N/A',cp.coupon_number) coupon_number,
                          od.discount_amount,
                          o.tax_amount,
                          IF(o.is_active = 1, 'Active', 'Inactive') AS is_active,
						  o.appointment_id,
						  IF(od.sale_type = 1, 'Yes', 'No') AS is_service,
                          o.created_date
                        FROM orders AS o
                          LEFT JOIN order_detail od
                            ON od.order_id = o.order_id
                          LEFT JOIN login l
                            ON l.login_id = o.customer_id
                          LEFT JOIN customer c
                            ON c.customer_id = o.customer_id
                          LEFT JOIN payment p
                            ON p.order_id = o.order_id
                          LEFT JOIN coupon cp
                            ON cp.coupon_id = o.coupon_id
                        WHERE o.is_delete = 0
                        /*//AND p.payment_id IS NOT NULL*/
						AND o.vendor_id='".$vendor_id."'
						GROUP BY o.order_id
						ORDER BY o.order_id DESC");
						
						$result = $selQuery->result();
						
						$response['status'] = 1;
						$response['result'] = $result;
		}else{
			$response['status'] = 0;
			$response['result'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	public function getCustomerByAppointmentId($apt_id){
		
		$qry = $this->db->query("select customer_id from appointment where appointment_id='".$apt_id."' ");
		return $qry->row();
	}
	
	public function add(){
		
	$payment_status ='';
	$transaction_id ='';
	$bank_txn_id ='';
	$response_code = "";
	$currency = "";
	$message = "";
		
	
	$customer_id = $this->input->post('customer_id');	
	$appointment_id = $this->input->post('appointment_id');	
	$vendor_id = $this->input->post('vendor_id');
	$payment_type = $this->input->post('payment_type');	 // 1-cash, 2-Online
	
	
	if(!empty($vendor_id)){
	
	if($appointment_id!='0'){
		$appointment_id = $appointment_id;
		$customer = $this->getCustomerByAppointmentId($appointment_id);
		$customer_id = $customer->customer_id;
	}else{
		$appointment_id = 0;
		$customer_id = $customer_id;
	}
	
	
	
	
	
	if($payment_type==1){
		
		$payment_status = "success";
		$order_number = $order_number = 'ORD' . date('mdYHis');
		$bank_txn_id = "";
		$response_code = "";
		$currency = "INR";
		$message = "Payment Received";
		$transaction_id = 'TXNQ' . date('mdYHis');
	}
	else if($payment_type==2){
		
		$payment_status = $this->input->post('status');
		$order_number = $this->input->post('order_id');
		$bank_txn_id = $this->input->post('bank_transaction_id');
		$response_code = $this->input->post('response_code');
		$currency = $this->input->post('currency');
		$message = $this->input->post('message');
		$transaction_id = 'TXNQ' . date('mdYHis');

	}
	
	$price_retail = 0;
	$amount = 0;
	$query = $this->db->query("select c.customer_id, c.product_id, c.quantity, p.price_retail, cm.mobile_phone, cm.email from cart c LEFT JOIN product p ON p.product_id=c.product_id LEFT JOIN customer cm ON cm.customer_id=c.customer_id where c.customer_id='".$customer_id."'");
	if($query->num_rows()){
		$result = $query->result();
		foreach($result as $res){
			
			$amount+=($res->price_retail*$res->quantity);
			
		}
		//echo $amount;
		
	}
	
			
	$query2 = $this->db->query("insert into payment set payment_type='".$payment_type."', status_id='2',payment_status='".$payment_status."',transaction_id='".$transaction_id."', bank_txn_id='".$bank_txn_id."', response_code='".$response_code."', amount='".$amount."', message='".$message."', created_date='".date('Y-m-d h:i:s')."', customer_id='".$customer_id."',vendor_id='".$vendor_id."' ");	
	
	$payment_id = $this->db->insert_id();
			
	$order_number = 'ORD' . date('mdYHis');
	
	if($payment_id){
		
		
		$query3 = $this->db->query("insert into orders set vendor_id='".$vendor_id."', customer_id='".$customer_id."', payment_id='".$payment_id."', status_id='2', order_type='1', order_number='".$order_number."', order_amount='".$amount."', is_active='1', is_delete='0', created_date='".date('Y-m-d h:i:s')."', appointment_id='".$appointment_id."' ");
		
		$order_id = $this->db->insert_id();
		//echo $order_id;die;
		
		
		if($query3){
			
			$query31 = $this->db->query("select * from cart where customer_id='".$customer_id."' ");
			$cart_data = $query31->result();
			if(count($cart_data)>0){
				foreach($cart_data as $cd){
					$query32 = $this->db->query("insert into order_detail set order_id='".$order_id."', product_id='".$cd->product_id."', quantity='".$cd->quantity."' ");
				}
				// delete item from cart
				$this->db->query("delete from cart where customer_id='".$customer_id."'");
				
			}
			
			// insert order in order history
			$this->db->query("insert into order_history set order_id='".$order_id."', status_id='2', order_update_date='".date('Y-m-d h:i:s')."' ");
			
			
			$response['status'] = 1;
			$response['order_number'] = $order_number;
			$response['message'] = "Order created successfully!";
			
			//Send order notification email
			
			//$vendor_detail = $this->getVendorDetailById($vendor_id);
			//$this->sendOrderEmail($order_id);
			
			$title = "New Order!";
			$title = "Sub Title";
			$message = "Dear Vendor, Please check new order.";
			//$this->load->library('SendFcm');
           // $fcm_responce=$this->sendfcm->sendNotification($title,$message, $vendor_detail->token,$subtitle);
		   
		   //echo "order created!";die;
		  // $this->layout->view_render('list');
			
			$response['status'] = 1;
			$response['order_id'] = $order_id;
			$response['message'] = 'Order created successfully!';
			
			
		}else{
			
			//echo "order not created!";die;
			
			$response['status'] = 0;
			$response['message'] = 'Order note created!';
			
			
		}
		
		
		
		
		
	}
	
	}else{
		
		$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
	}
	
	echo json_encode($response);
	
	}
	
	public function getAppointmentById($appointment_id){
		
		$query = $this->db->query("select 
									a.appointment_id,
									s.service_name,
									aps.price,
									aps.duration,
									CONCAT(a.date,' ', aps.appointment_time) as appointment_date,
									CONCAT(st.firstname,' ',st.lastname) as stylist_name,
									od.refund_amount
									from appointment a
									INNER JOIN appointment_service aps
									ON a.appointment_id=aps.appointment_id
									INNER JOIN service s 
									ON s.service_id=aps.service_id
									INNER JOIN stylist st 
									ON st.stylist_id=aps.stylist_id
									
									inner join order_detail as od on od.product=a.appointment_id
									where a.appointment_id='".$appointment_id."'
									");
		return $query->result();
	}
	
	public function get_payment_details($order_id){
		
		$query = $this->db->query("select p.payment_id, IF(p.payment_type=1,'Cash','Card') AS payment_method, IF(p.status_id=1,'Pending','Success') as payment_status, p.transaction_id from payment p where p.order_id='".$order_id."' ");
		return $query->row();
	}
	
	public function get_payment_history($order_id){
		
		$query = $this->db->query("select (select sum(amount) from payment where order_id='177' and payment_type='1') as cash_amount, (select sum(amount) from payment where order_id='".$order_id."' and payment_type='2') as credit_amount ,o.iou_amount from payment p inner join orders o ON o.order_id=p.order_id where p.order_id='".$order_id."' GROUP BY p.order_id ");
		
		
		$result = $query->row();
		return $result;
		
	}
	
	public function getCouponById($coupon_id){
		
		$query = $this->db->query("select * from coupon where coupon_id='".$coupon_id."' ");
		return $query->row();
	}
	
	public function get_tax_settings(){
		
		$query = $this->db->query("select * from settings ");
		return $query->result();
	}
	
	function get_order_details($order_id){
        $selQuery = "SELECT * FROM order_detail WHERE order_id = '".$order_id."'";
        $result = $this->db->query($selQuery)->result();
        return $result;
    }
	
	function getOrderHistory($order_id){
        $selQuery = "SELECT * FROM order_history WHERE order_id = '".$order_id."'";
        $result = $this->db->query($selQuery)->result();
        return $result;
    }
	function getTipData(){
        	$order_id=$this->input->post('order_id');
        	if(!empty($order_id)){
        		$getTip=$this->db->query("select o.tip_amount,od.stylist_id,concat(s.firstname,' ',s.lastname) as stylist_name from orders as o inner join order_detail as od on o.order_id=od.order_id inner join stylist as s on s.stylist_id=od.stylist_id where o.order_id='".$order_id."' and od.sale_type=1 group by od.stylist_id")->result();
        		if(!empty($getTip)){
        			$getTip=$getTip;
        			$response['status'] = 1;
        			$response['tip_data'] = $getTip;
					$response['message'] = 'Data found';
        		}else{
        			$response['status'] = 1;
        			$response['tip_data'] = array();
					$response['message'] = 'No data found';
        			
        		}

				
			
        	}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		
		echo json_encode($response);

            }
	function get_product_info($product_id){
        $selQuery = "SELECT 
                      p.product_id,
                      p.product_name as name,
                      c.category_name,
                      p.price_retail as price,
                      p.description,
                      p.main_image,
                      od.refund_amount
                    FROM product p
                    LEFT JOIN category c 
                      ON c.category_id = p.category_id
                      INNER JOIN order_detail as od 
									ON od.product_id=p.product_id
									
                    WHERE p.product_id = '".$product_id."'";
        $result = $this->db->query($selQuery)->row();
        return $result;
    }
	
	public function order_detail(){
		
		$order_id = $this->input->post('order_id');
		
		$data = array();


		$query = $this->db->query("select o.*, od.product_id, od.sale_type from orders o INNER JOIN order_detail od ON od.order_id=o.order_id where o.order_id='".$order_id."' ");
		$data['editData'] = $query->row();
      // print_r($data['editData']);die;
	    $query2 = $this->db->query("select * from customer where customer_id='".$data['editData']->customer_id."' ");
		$data['customerInfo'] = $query2->row();
		
        $query3 = $this->db->query("select * from login where login_id='".$data['customerInfo']->login_id."' ");
		$customerLoginInfo = $query3->row();
		
       
        $data['customerInfo']->email = $customerLoginInfo->email;
			//echo "<pre>";print_r($data['customerInfo']);die;
			
			if($data['editData']->sale_type==1){
				$appointment_id = $data['editData']->product_id;
				$data['appointmentData'] = $this->getAppointmentById($appointment_id);
			}else{
			
				$data['appointmentData'] = array();
			}
			
			$path = "http://159.203.182.165/salon/assets/img/signature/";
		
   $data['customerSignature'] = $this->db->query("select CONCAT('$path','/',signature) as signature from payment where order_id='".$order_id."' AND customer_id='".$data['editData']->customer_id."'  ")->row();
   
		
$data['paymentInfo'] = array();
       
        //if (!empty($data['editData']->payment_id)) {
            $data['paymentInfo'] = $this->get_payment_details($order_id);
        //}


$data['paymentHistory'] = array();
$data['paymentHistory'] = $this->get_payment_history($order_id);


        if (!empty($data['editData']->coupon_id)) {
            $data['couponInfo'] = $this->getCouponById(array("coupon_id" => $data['editData']->coupon_id));
        }

        $data['taxInfo'] = $this->get_tax_settings();

        if (!empty($order_id)) {
			$data['productInfo'] = array();
            $data['orderInfo'] = $this->get_order_details($order_id);
            $i = 0;
            foreach ($data['orderInfo'] as $keyOrder => $valueOrder) {
				if($valueOrder->sale_type==2){
                $productInfo = $this->get_product_info($valueOrder->product_id);
                $data['productInfo'][$i] = $productInfo;
                $data['productInfo'][$i]->quantity = $valueOrder->quantity;
                $data['productInfo'][$i]->notes = $valueOrder->notes;
                $i++;
				}else{
					$data['productInfo'] = array();
				}
            }
        }

    

        $data['orderHistoryInfo'] = $this->getOrderHistory($order_id);

        if (!empty($data['editData'])) {
            
			$response['status'] = 1;
			$response['result'] = $data;
        } else {
           $response['status'] = 0;
		   $response['result'] = 'Something went wrong!';
        }
		
		echo json_encode($response);
		
	}
	
	public function tipDistribution(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$tip_type = $this->input->post('tip_type');
		$tip_percent = explode(",",$this->input->post('tip_percent'));
		$stylist_id = explode(",",$this->input->post('stylist_id'));
		//$appointment_id =  explode(",",$this->input->post('appointment_id'));
		$order_id =  $this->input->post('order_id');
		$amount =  explode(",",$this->input->post('amount'));
		//echo "<pre>";print_r($_POST);exit;
		
		// tip_data = [{"stylist_id":"1","appointment_id":"23","tip_amount":"50"}]
		if(!empty($vendor_id) && !empty($tip_type)){
			///$tip_data = json_decode($tip_data);
			if(empty($tip_percent)){
				$tip_percent[$key]=0;
			}
			foreach($stylist_id  as $key=> $val){
				$query = $this->db->query("insert into stylist_tip_amount set stylist_id='".$val."', order_id='".$order_id."', tip_amount='".$amount[$key]."', tip_type='".$tip_type."', tip_percent='".$tip_percent[$key]."', vendor_id='".$vendor_id."', status='1', date_created='".date('Y-m-d H:i:s')."' ");
				if($query){
					$this->db->query('update orders set is_tip="1" where order_id="'.$order_id.'"');
				}
			}
			
			$response['status'] = 1;
			$response['message'] = 'Tip distributed successfully!';
				
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		
		echo json_encode($response);
		
	}
}