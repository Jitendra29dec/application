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


class Report extends CI_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
		
		//error_reporting(0);
    }
	
	

	public function getBatchReport(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$getData=$this->db->query('select machine_type,credit_count,credit_amount,debit_count,debit_amount,date_format(date_created,"%M %d,%Y") as batch_date from batch_close_report where vendor_id="'.$vendor_id.'"')->result();
		if(!empty($getData)){
			$response['status'] = 1;
			$response['message'] = 'Transaction found';
			$response['getData']=$getData;
			}else{
				$response['status'] = 0;
				$response['message'] = 'No transaction found';
				$response['getData']=array();
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
	}
	public function transactionList(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post("vendor_id");
		$start_date=$_POST['start_date'];
        $end_date=$_POST['end_date'];

		$start_date = date('Y-m-d',strtotime($start_date));
		$end_date = date('Y-m-d',strtotime($end_date));
		
		if(!empty($vendor_id)){
			
			if(($start_date!='' || $end_date!='') && ($start_date!='1970-01-01' || $end_date!='1970-01-01')){
				$date_filter = " AND DATE(o.created_date)>='".$start_date."' AND DATE(o.created_date)<='".$end_date."' ";
			}else{
				$date_filter = "";
			}

			$query = $this->db->query("select o.order_id,DATE_FORMAT(o.created_date,'%c-%d-%Y') AS date, TIME_FORMAT(o.created_date,'%l:%i %p') as time, o.order_number, CASE WHEN o.status_id=1 THEN 'Pending'  WHEN o.status_id=2 THEN 'Complete' WHEN o.status_id=3 THEN 'Cancel'  ELSE 'Complete' END AS status, CONCAT(c.firstname,' ',c.lastname) as customer_name, o.order_amount as transaction_amount,o.is_refund from orders o INNER JOIN customer c ON c.customer_id=o.customer_id where o.vendor_id='".$vendor_id."'  $date_filter  order by o.order_id desc");
			
			if($query->num_rows()>0){

				$response['status'] = 1;
				$response['result'] = $query->result();
				$response['message'] = '';
			}else{
				$response['status'] = 2;
				$response['message'] = 'No transaction found';
			}

		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
	}


	public function viewInvoice(){
		
		$vendor_id = $this->input->post('vendor_id');
		$order_id = $this->input->post('order_id');
		
		$response['status'] = 0;
		$response['message'] = '';
		
		if(!empty($vendor_id) && !empty($order_id)){
		$data = array();
        $data['order_id']=$order_id;
		
		
		$data['orderInfo']= $this->db->query("select o.order_id, o.order_number, o.customer_id,o.deposit_id, DATE_FORMAT(o.created_date,'%c-%d-%Y %l:%i %p') as order_date, if(o.order_type=1,'App','Web') as order_type, CASE WHEN o.status_id=1 THEN 'Pending' WHEN o.status_id=2 THEN 'Complete' ELSE 'Cancel' END as status, o.tax_amount, o.discount_amount, GROUP_CONCAT(p.payment_type) as payment_type, CASE WHEN p.payment_type='1' THEN 'Cash' when p.payment_type='2' THEN 'Card' end as payment_method,o.is_refund,o.total_refund_amount,o.is_tip from orders  o INNER join payment p on p.order_id=o.order_id where o.order_id='".$order_id."' ")->row();
		
        ///echo "<pre>";print_r($data['editData']);exit;
        //echo 'select * from customer where customer_id IN ('. $data['editData']->customer_id.')';die;
        //$data['customerInfo'] = $this->customer->get_by(array("customer_id" => $data['editData']->customer_id));
            $data['customerInfo'] = $this->db->query('select * from customer where customer_id IN ('. $data['orderInfo']->customer_id.')')->result();
			
        
		 $customerLoginInfo = $this->db->query("select email from login where login_id='".$data['customerInfo'][0]->login_id."' ")->row();
		
		 // $data['customerInfo']->email = $customerLoginInfo->email;
		 
		//	echo "<pre>";print_r($data['customerInfo']);die;
			
			$data['customerSignature'] = $this->db->query("select signature from payment where order_id='".$order_id."' AND customer_id='".$data['orderInfo']->customer_id."' AND signature!='' ")->row();


        /*left JOIN stylist_service ss
                                    ON ss.service_id=aps.service_id
                                    AND ss.stylist_id=aps.stylist_id*/
			$data['appointmentData']=$this->db->query("select
                                    a.appointment_id,
                                    DATE_FORMAT(a.date,'%c-%d-%Y %l:%i %p') as appointment_date,
                                    aps.service_id, aps.stylist_id,
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
             if(!empty($data['appointmentData'])){
            $appointment_id1=array();
            foreach($data['appointmentData'] as $val){
                $appointment_id1[]=$val->appointment_id;
            }
            $appointment_id=implode(",", $appointment_id1);
            $data['stylist_id']=$this->db->query('select A.id,CONCAT(B.firstname," ",B.lastname) as stylist_name,B.stylist_id,A.tip_amount,date_format(A.date_created,"%c-%d-%Y") as tip_date,A.appointment_id from stylist_tip_amount as A inner join stylist as B on A.stylist_id=B.stylist_id where A.appointment_id IN('.$appointment_id.')')->result();
        }else{
        	$data['appointmentData']=array();
        }
        if(!empty($data['stylist_id'])){
        	$data['stylist_id']=$data['stylist_id'];
        }else{
        	$data['stylist_id']=array();
        }
        if (!empty($data['orderInfo']->order_id)) {
          

		$data['productInfo']=$this->db->query('select p.product_name, od.quantity, od.actual_amount as unit_price, od.total_paid_amount as total from order_detail od INNER JOIN product p on p.product_id=od.product_id where od.order_id="'.$order_id.'" and od.sale_type=2 ')->result();
		
		
		 $data['paymentInfo']=$this->db->query('select o.order_amount, o.tax_amount, o.tip_amount, o.cash_amount, o.credit_card_amount,o.discount_amount, o.iou_amount, (select sum(total_paid_amount) from order_detail where order_id=o.order_id and sale_type=3) as gift_certificate_amount, (select sum(total_paid_amount) from order_detail where order_id=o.order_id and sale_type=4) as gift_card_amount, o.total_refund_amount as refund_amount from orders o where o.order_id="'.$order_id.'" ')->row();

		 if(!empty($data['orderInfo']->deposit_id)){
		 	$getSmilarOrder=$this->db->query('select o.order_number,concat(c.firstname," ",c.lastname) as customer_name from orders as o inner join customer as c on o.customer_id=c.customer_id where o.deposit_id="'.$data['orderInfo']->deposit_id.'" and o.status_id=2 and o.order_id!="'.$order_id.'"')->result();
		 ///	echo 'select A.event_name,concat(c.firstname," ",c.lastname) as group_leader_name,o.amount,o.type from order_deposit as o inner join deposit_customer as A on A.deposit_id=o.deposit_id inner join customer as c on A.customer_id=c.customer_id where o.deposit_id="'.$data['orderInfo']->deposit_id.'"';die;
		 	$getDepositHistory=$this->db->query('select A.event_name,concat(c.firstname," ",c.lastname) as group_leader_name,o.amount,o.type from order_deposit as o inner join deposit_customer as A on A.id=o.deposit_id inner join customer as c on A.customer_id=c.customer_id where o.deposit_id="'.$data['orderInfo']->deposit_id.'"')->result();
		 	if(!empty($getSmilarOrder)){
		 		$data['getSmilarOrder']=$getSmilarOrder;
		 	}else{
		 		$data['getSmilarOrder']=array();
		 	}
		 	if(!empty($getDepositHistory)){
		 		$data['getDepositHistory']=$getDepositHistory;
		 	}else{
		 		$data['getDepositHistory']=array();
		 	}
		 }else{
		 	$data['getSmilarOrder']=array();
		 	$data['getDepositHistory']=array();
		 }

		
			$data['status'] = 1;
			$data['message'] = '';
		}else{
			$data['status'] = 0;
			$data['message'] = 'Something wrong';
		}
		}else{
			
			$data['status'] = 0;
			$data['message'] = 'Required parameter missing';
		}

       echo json_encode($data);
		
		
		
	}


	public function sendInvoice(){
		$order_id=$this->input->post('order_id');
		$vendor_id=$this->input->post('vendor_id');
		$getData=$this->db->query('select o.login_id,l.role_id from orders as o inner join login as l on o.login_id=l.login_id where o.order_id="'.$order_id.'"')->row();
		$role_id=$getData->role_id;
		$login_id=$getData->login_id;
	//$order_id = '',$vendor_id=''
	//$order_id = '',$vendor_id=''
	//$order_id='383';
//	$vendor_id='10';

//$order_id = '',$vendor_id='',$role_id='', $login_id=''

	/*$order_id='469';
	$vendor_id='10';
	$role_id='1';
	$login_id='2093';
*/
    /*p.payment_id,p.transaction_id,IF(p.payment_type="1","Cash",IF(p.payment_type="2","Debit Card",IF(p.payment_type="3","Credit Card",IF(p.payment_type="4","Net Banking",IF(p.payment_type="5","EBS Payments","N.A"))))) payment_type,
                         IF(p.status_id="1","Pending",IF(p.status_id="2","Success",IF(p.status_id="3","Reject","N.A"))) payment_status,p.amount        inner join payment as p on o.order_id=p.payment_id*/
    //echo $order_id;exit;

  $this->db->query('update orders set status_id=2 where order_id="'.$order_id.'"');
   $qqrr = $this->db->query("select count(order_id) as num from orders where created_date LIKE '%".date('Y-m-d')."%' and vendor_id='".$vendor_id."' ");
	$current_date_count = $qqrr->row()->num;
	
	$increase_count = $current_date_count+1;
	
	$data['order_number']='ORD' . date('mdY').$increase_count;
	if($role_id==2){
		$getName=$this->db->query('select s.firstname from stylist as s inner join login as l on s.login_id=l.login_id where l.login_id="'.$login_id.'"')->row();
	}else{
			$getName=$this->db->query('select v.vendor_name as stylist from vendor as v inner join login as l on v.login_id=l.login_id where l.login_id="'.$login_id.'"')->row();
	}
	if($getName==''){
		$getName1='Stylist';
	}else{
		$getName1=$getName->stylist;
	}
	$data['cashier']=$getName1;

  $data['order_data'] =$this->db->query('select o.order_id,o.customer_id,o.coupon_id,o.order_number,o.order_amount,o.tax_amount,o.tip_amount,o.cash_amount,o.credit_card_amount,o.iou_amount,o.final_amount,o.vendor_id,o.rewards_money,o.diposite_amount,o.discount_amount,o.gift_cert_amount as certificate_amount,o.gift_cart_amount as gift_card_amount,o.cuppon_value,IF(o.status_id="2","Successfull",IF(o.status_id="1","in Process",IF(o.status_id="3","cancel",IF(o.status_id="4","payment fail","N.A")))) order_status,o.return_amount from orders as o  where o.order_id="'.$order_id.'"')->row();

 //echo "<pre>";print_r($data['order_data']);exit;
 $data['payment_data']=$this->db->query('select p.payment_id,p.transaction_id,IF(p.payment_type="1","Cash",IF(p.payment_type="2","Card",IF(p.payment_type="3","Credit Card",IF(p.payment_type="4","Net Banking",IF(p.payment_type="5","EBS Payments","N.A"))))) payment_type,
                         IF(p.status_id="1","Pending",IF(p.status_id="2","Success",IF(p.status_id="3","Reject","N.A"))) payment_status,p.amount from payment as p where p.order_id="'.$order_id.'" and status_id=2')->result();
  //echo "<pre>";print_r($data['order_data']);exit;

  $data['customerInfo'] = $this->db->query('select concat(firstname," ",lastname) as customer_name,email,mobile_phone,home_phone from customer where customer_id IN ('. $data['order_data']->customer_id.')')->result();
  $customerEmail=$this->db->query('select l.email from customer as c inner join login as l on c.login_id=l.login_id where c.customer_id="'.$data['order_data']->customer_id.'"')->row();
   $data['cardDetail'] = $this->db->query('select card_holder_name, card_number,card_type, entry_type, terminal, aid, tvr, tsi, arc from customer_card_detail where order_id="'.$order_id.'" order by id desc limit 0,1 ')->result();
   
   $data['batchNumber'] = $this->db->query('SELECT DISTINCT(batch_no) FROM `batch` where order_id="'.$order_id.'" ')->result();
   
         $path = "http://159.203.182.165/salon/assets/img/signature/";
        
   $data['customerSignature'] = $this->db->query("select CONCAT('$path','/',signature) as signature from payment where order_id='".$order_id."' AND customer_id='".$data['order_data']->customer_id."'  ")->row();
  
  
  /*if (!empty($data['editData']->payment_id)) {
           // $data['paymentInfo'] = $this->orders->get_payment_details($data['editData']->payment_id);
             $data['paymentInfo']=$this->db->query("SELECT payment_id,transaction_id,IF(payment_type='1','Cash',IF(payment_type='2','Debit Card',IF(payment_type='3','Credit Card',IF(payment_type='4','Net Banking',IF(payment_type='5','EBS Payments','N.A'))))) payment_type,
                         IF(status_id='1','Pending',IF(status_id='2','Success',IF(status_id='3','Reject','N.A'))) payment_status,amount FROM payment WHERE payment_id = '".$data['editData']->payment_id."'")->row();
        }*/
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
                      o.actual_amount as price,
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
                      gift.amount as amount,
                      gift.vendor_id as vendor_id,
                      gfimg.image as gift_image
                       FROM gift_certificate as gift
                  	  INNER join order_detail as o on o.product_id=gift.gift_id
                      INNER join gift_certificate_images as gfimg on gfimg.image_id=gift.template_image_id
                    WHERE o.order_id = "'.$order_id.'" and o.sale_type=3')->result();
  $data['gift_card']=$this->db->query('SELECT 
                      gift.card_number as gift_card_no,
                      gift.intial_amount as amount,
                      gift.issue_date,
                      gift.buyer_email,
                      gift.recipient_email,
                      gift.is_myself,
                      gift.vendor_id as vendor_id,
                      gfimg.image as gift_image
                       FROM gift_card as gift
                      INNER join order_detail as o on o.product_id=gift.card_id
                      INNER join giftcard_images as gfimg on gfimg.gc_image_id=gift.template_image_id
                    WHERE o.order_id = "'.$order_id.'" and o.sale_type=4')->result();
   			$subject="New order";
			$message = $this->load->view('email_template/order',$data,TRUE);
		//	echo $message;die;
			$this->load->library('Send_mail');
			$this->send_mail->sendMail($data['customerInfo'][0]->email, $subject, $message, $fileName=false, $filePath=false, $cc=false);
			$response['status']=1;
			$response['message']="Email sent Successfull";
            
         echo json_encode($response);


	}
	

	

	
	public function eGiftCard(){

		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post("vendor_id");
		$start_date = $this->input->post("start_date");
		$end_date = $this->input->post("end_date");
		
		$start_date = date('Y-m-d',strtotime($start_date));
		$end_date = date('Y-m-d',strtotime($end_date));
		
		if(!empty($vendor_id)){

			$gift_card = $this->db->query("select DATE_FORMAT(o.created_date,'%c-%d-%Y') AS date, TIME_FORMAT(o.created_date,'%l:%i %p') as time, o.order_number, '' as checkout_by, '' as service_provider, CONCAT(c.firstname,' ',c.lastname) as customer_name, od.total_paid_amount as amount, gc.card_number, gc.message as reason FROM orders o inner join order_detail od on od.order_id=o.order_id inner join gift_card gc ON gc.card_id=od.product_id inner join customer c on c.customer_id=o.customer_id where od.sale_type='4' and  o.vendor_id='".$vendor_id."' ")->result();

			
			$response['status'] = 1;
			$response['gift_card'] = $gift_card;
			$response['message'] = '';

		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		
		echo json_encode($response);
        

	}


	public function currentStock(){

		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post("vendor_id");
		$start_date = $this->input->post("start_date");
		$end_date = $this->input->post("end_date");
		
		$start_date = date('Y-m-d',strtotime($start_date));
		$end_date = date('Y-m-d',strtotime($end_date));
		
		if(!empty($vendor_id)){

			$result = $this->db->query("select p.product_name, c.category_name, b.brand_name, (select s.supplier_name from product_supplier ps INNER JOIN supplier s ON s.supplier_id=ps.supplier_id where ps.product_id=p.product_id and ps.is_default='1') as vendor, p.sku, p.quantity as qty_in_stock, p.low_qty_warning as low_qty_alert, p.purchase_price as item_cost, (p.quantity*p.purchase_price) as total_cost from product p INNER JOIN category c on c.category_id=p.category_id INNER JOIN brand b ON b.brand_id=p.brand_id where p.vendor_id='".$vendor_id."'  ")->result();

			
			$response['status'] = 1;
			$response['current_stock'] = $result;
			$response['message'] = '';

		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		
		echo json_encode($response);
        

	}


	public function activeEmployeeList(){

		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post("vendor_id");
		$start_date = $this->input->post("start_date");
		$end_date = $this->input->post("end_date");
		
		$start_date = date('Y-m-d',strtotime($start_date));
		$end_date = date('Y-m-d',strtotime($end_date));
		
		if(!empty($vendor_id)){

			$result = $this->db->query("select s.firstname, s.lastname, s.phone as mobile_number, s.alternate_phone as alternate_number, l.email, s.city from stylist s INNER JOIN login l ON l.login_id=s.login_id where l.is_active='1' and l.vendor_id='".$vendor_id."' ")->result();

			
			$response['status'] = 1;
			$response['activeEmployees'] = $result;
			$response['message'] = '';

		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		
		echo json_encode($response);
        

	}


	public function attendance(){

		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post("vendor_id");
		$start_date = $this->input->post("start_date");
		$end_date = $this->input->post("end_date");
		
		$start_date = date('Y-m-d',strtotime($start_date));
		$end_date = date('Y-m-d',strtotime($end_date));
		
		if(!empty($vendor_id)){

			if(($start_date!='' || $end_date!='') && ($start_date!='1970-01-01' ||$end_date!='1970-01-01')){
				$con=" and (a.attendance_date>='".$start_date."' and a.attendance_date<='".$end_date."')";
			}else{
	
				$con='';
			}


							/* $clockin_time = strtotime($val->clockin);
							$clockout_time = strtotime($val->clockout);
							
							//$diff = abs($date2 - $date1); 
							$time_diff = abs($clockout_time-$clockin_time);
							
							$hours = floor(($time_diff)/(60*60)); 
							
							$minutes = floor(($time_diff-$hours*60*60)/ 60);  */

			$result=$this->db->query("select a.attendance_id,a.stylist_id,date_format(a.attendance_date,'%W, %c-%d-%Y') as date,a.attendance_time as time, s.firstname, s.lastname,TIME_FORMAT(attendance_time,'%l:%i %p') as clockin, (SELECT TIME_FORMAT(attendance_out_time,'%l:%i %p') FROM attendance ORDER BY attendance_id DESC LIMIT 0,1) as clockout,(select TIME_FORMAT(attendance_time,'%l:%i %p') from attendance where type=1 and stylist_id=a.stylist_id  limit 1) as break_in1,(select TIME_FORMAT(attendance_out_time,'%l:%i %p') from attendance where type=1 and stylist_id=a.stylist_id  limit 1) as break_out1,(select TIME_FORMAT(attendance_time,'%l:%i %p') from attendance where type=2 and stylist_id=a.stylist_id  limit 1) as break_in2,(select TIME_FORMAT(attendance_out_time,'%l:%i %p') from attendance where type=2 and stylist_id=a.stylist_id  limit 1) as break_out2, '' AS lunch_brean_in, '' as lunch_break_out, '' as total_hour from attendance a inner join stylist s on s.stylist_id=a.stylist_id where a.vendor_id='".$vendor_id."' and a.type=0 ".$con." order by a.attendance_id DESC")->result();
			//echo $this->db->last_query();die;

			
			$response['status'] = 1;
			$response['attendance'] = $result;
			$response['message'] = '';

		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		
		echo json_encode($response);
        

	}


	public function hoursAndWagesSummary(){

		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post("vendor_id");
		$start_date = $this->input->post("start_date");
		$end_date = $this->input->post("end_date");
		
		$start_date = date('Y-m-d',strtotime($start_date));
		$end_date = date('Y-m-d',strtotime($end_date));
		
		if(!empty($vendor_id)){

			$result = $this->db->query("select s.firstname, s.lastname, TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(SUBTIME(a.attendance_out_time,a.attendance_time)))),'%l:%i %p') AS total_hours, SUM(od.service_commission)+od.product_commission AS commission, '0.00' as cash_tip, '0.00' as card_tip FROM stylist s LEFT JOIN attendance a ON a.stylist_id=s.stylist_id LEFT JOIN order_detail od ON od.stylist_id=s.stylist_id LEFT JOIN login l on l.login_id=s.login_id where l.vendor_id='".$vendor_id."' ")->result();

			
			$response['status'] = 1;
			$response['hour_and_wages'] = $result;
			$response['message'] = '';

		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		
		echo json_encode($response);
        

	}

	public function salesSummary(){

		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post("vendor_id");
		$start_date = $this->input->post("start_date");
		$end_date = $this->input->post("end_date");
		
		$start_date = date('Y-m-d',strtotime($start_date));
		$end_date = date('Y-m-d',strtotime($end_date));
		
		if(!empty($vendor_id)){

			$result = $this->db->query("select s.firstname,s.lastname, (select count(o.customer_id) from orders o INNER JOIN order_detail od ON od.order_id=o.order_id where od.stylist_id=s.stylist_id) as no_of_customer, (select count(product_id) from order_detail where sale_type='1' and stylist_id=s.stylist_id) as no_of_service, (select count(product_id) from order_detail where sale_type='2' and stylist_id=s.stylist_id) as no_of_product, IFNULL((select SUM(actual_amount) as service_sales from order_detail where sale_type='1' and stylist_id=s.stylist_id ),'0.00') as service_sales, IFNULL((select SUM(actual_amount) as product_sales from order_detail where sale_type='2' and stylist_id=s.stylist_id ),'0.00') as product_sales, '0.00' as card_tip, '0.00' as cash_tip, '0.00' as sales_plus_tip from stylist s INNER JOIN login l on l.login_id=s.login_id where l.vendor_id='".$vendor_id."'")->result();

			
			$response['status'] = 1;
			$response['sales_summary'] = $result;
			$response['message'] = '';

		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		
		echo json_encode($response);
        

	}


	public function commissionSummary(){

		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post("vendor_id");
		$start_date = $this->input->post("start_date");
		$end_date = $this->input->post("end_date");
		
		$start_date = date('Y-m-d',strtotime($start_date));
		$end_date = date('Y-m-d',strtotime($end_date));
		
		if(!empty($vendor_id)){

			$result = $this->db->query("select s.firstname, s.lastname, (select count(product_id) from order_detail where sale_type='1' and stylist_id=s.stylist_id) as service_completed, IFNULL((select SUM(actual_amount) from order_detail where sale_type='1' and stylist_id=s.stylist_id),'0.00') as service_amount, IFNULL((select SUM(service_commission) from order_detail where sale_type='1' and stylist_id=s.stylist_id),'0.00') as service_commission, (select count(product_id) from order_detail where sale_type='2' and stylist_id=s.stylist_id) as product_sold, IFNULL((select SUM(actual_amount) from order_detail where sale_type='2' and stylist_id=s.stylist_id),'0.00') as product_amount, IFNULL((select SUM(product_commission) from order_detail where sale_type='2' and stylist_id=s.stylist_id),'0.00') as product_commission, IFNULL((select SUM(service_commission)+SUM(product_commission) from order_detail where stylist_id=s.stylist_id),'0.00') AS total_commission  from stylist s INNER JOIN login l ON l.login_id=s.login_id where l.vendor_id='".$vendor_id."' ")->result();

			
			$response['status'] = 1;
			$response['commission_summary'] = $result;
			$response['message'] = '';

		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		
		echo json_encode($response);
        

	}


	public function customerList(){

		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post("vendor_id");
		$start_date = $this->input->post("start_date");
		$end_date = $this->input->post("end_date");
		
		$start_date = date('Y-m-d',strtotime($start_date));
		$end_date = date('Y-m-d',strtotime($end_date));
		
		if(!empty($vendor_id)){

			$result = $this->db->query("select c.firstname, c.lastname, IFNULL(c.mobile_phone,'') as phone, l.email, IFNULL(DATE_FORMAT(c.birthday,'%c-%d-%Y'),'') AS birthday, c.gender, IFNULL(c.city,'') AS city, DATE_FORMAT(l.created_date,'%c-%d-%Y') AS customer_since, IFNULL((select DATE_FORMAT(created_date,'%c-%d-%Y') FROM orders where customer_id=c.customer_id order by created_date desc limit 0,1),'') as last_visit from customer c INNER JOIN login l ON l.login_id=c.login_id where l.vendor_id='".$vendor_id."' ")->result();

			
			$response['status'] = 1;
			$response['customer_list'] = $result;
			$response['message'] = '';

		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		
		echo json_encode($response);
        

	}


	public function creditCertificate(){

		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post("vendor_id");
		$start_date = $this->input->post("start_date");
		$end_date = $this->input->post("end_date");
		
		$start_date = date('Y-m-d',strtotime($start_date));
		$end_date = date('Y-m-d',strtotime($end_date));
		
		if(!empty($vendor_id)){

			$result = $this->db->query("select c.firstname, c.lastname, c.mobile_phone as phone, l.email, '' as service_provider, gc.gift_certificate_no as certificate_number, DATE_FORMAT(gc.issue_date,'%c-%d-%Y') as issue_date, s.service_name as service, s.price as amount, '' as reason, gc.expire_on as expire_date, '5-10-2022' as redeem_date from gift_certificate gc INNER JOIN customer c on c.customer_id=gc.customer_id INNER JOIN login l ON l.login_id=c.login_id INNER JOIN service s on s.service_id=gc.service_id where gc.vendor_id='".$vendor_id."'  ")->result();

			
			$response['status'] = 1;
			$response['credit_certificate'] = $result;
			$response['message'] = '';

		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		
		echo json_encode($response);
        

	}


	public function closeout(){

		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post("vendor_id");
		$pin = $this->input->post("pin");
		
		
		if(!empty($pin)){

			$current_date_time = date('%c-%d-%Y %l:%i %p');
			
			$timestamp = $this->db->query("select s.stylist_id, CONCAT(s.firstname,' ',s.lastname) as employee_name, DATE_FORMAT(NOW(),'%c-%d-%Y %l:%i %p') as timestamp from stylist s INNER JOIN login l ON l.login_id=s.login_id where l.pin='".$pin."'  ")->row();
			
			$stylist_id = $timestamp->stylist_id;

			$sales['service'] = $this->db->query("select (select count(od.product_id) as service_count from order_detail od where od.sale_type='1' and od.stylist_id='".$stylist_id."') as count, IFNULL((select sum(od.actual_amount) as service_amount from order_detail od where od.sale_type='1' and od.stylist_id='".$stylist_id."'),'0.00') as amount ")->row();
						
			 $sales['product'] = $this->db->query("select (select count(od.product_id) as product_count from order_detail od where od.sale_type='2' and od.stylist_id='".$stylist_id."') as count,IFNULL((select sum(od.actual_amount) as product_amount from order_detail od where od.sale_type='2' and od.stylist_id='".$stylist_id."'),'0.00')as amount ")->row(); 

			 
			$sales['tax'] = $this->db->query("select IFNULL((select sum(od.product_id) as tax_count from order_detail od where od.tax_amount!='0' and od.stylist_id='".$stylist_id."'),'0') as count,IFNULL((select sum(od.tax_amount) as tax_amount from order_detail od where od.stylist_id='".$stylist_id."'),'0.00') as amount")->row();  

			
			$payment_breakdown['cash']['sales'] = $this->db->query("select count(product_id) as count, SUM(od.actual_amount) as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='1'  and od.stylist_id='".$stylist_id."' ")->row(); 

			$payment_breakdown['cash']['refund'] = $this->db->query("select count(product_id) as count, SUM(od.actual_amount) as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='1' and od.stylist_id='".$stylist_id."' ")->row(); 

			$payment_breakdown['cash']['tips'] = $this->db->query("select count(product_id) as count, SUM(od.actual_amount) as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='1'  and od.stylist_id='".$stylist_id."' ")->row(); 

			$payment_breakdown['cash']['paid_out'] = $this->db->query("select count(product_id) as count, SUM(od.actual_amount) as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='1' and od.stylist_id='".$stylist_id."' ")->row(); 

			$payment_breakdown['cash']['deposit'] = $this->db->query("select count(product_id) as count, SUM(od.actual_amount) as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='1' and od.stylist_id='".$stylist_id."' ")->row(); 

			$payment_breakdown['cash']['gift_card_sold'] = $this->db->query("select count(product_id) as count, SUM(od.actual_amount) as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='1'  and od.stylist_id='".$stylist_id."' ")->row(); 



			// card sales
			$payment_breakdown['card']['sales'] = $this->db->query("select count(product_id) as count, IFNULL(SUM(od.actual_amount),'0.00') as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='2' and od.stylist_id='".$stylist_id."'")->row(); 

			$payment_breakdown['card']['debit'] = $this->db->query("select count(product_id) as count, IFNULL(SUM(od.actual_amount),'0.00') as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='2'  and od.stylist_id='".$stylist_id."'")->row(); 

			$payment_breakdown['card']['amex'] = $this->db->query("select count(product_id) as count, IFNULL(SUM(od.actual_amount),'0.00') as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='2'  and od.stylist_id='".$stylist_id."'")->row(); 

			$payment_breakdown['card']['disc'] = $this->db->query("select count(product_id) as count, IFNULL(SUM(od.actual_amount),'0.00') as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='2' and od.stylist_id='".$stylist_id."'")->row(); 

			$payment_breakdown['card']['mc'] = $this->db->query("select count(product_id) as count, IFNULL(SUM(od.actual_amount),'0.00') as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='2' and od.stylist_id='".$stylist_id."'")->row(); 

			$payment_breakdown['card']['visa'] = $this->db->query("select count(product_id) as count, IFNULL(SUM(od.actual_amount),'0.00') as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='2' and od.stylist_id='".$stylist_id."'")->row(); 


			// refund 

			$payment_breakdown['refund']['amex'] = $this->db->query("select count(product_id) as count, IFNULL(SUM(od.actual_amount),'0.00') as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='2'  and od.stylist_id='".$stylist_id."'")->row(); 

			$payment_breakdown['refund']['disc'] = $this->db->query("select count(product_id) as count, IFNULL(SUM(od.actual_amount),'0.00') as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='2' and od.stylist_id='".$stylist_id."'")->row(); 

			$payment_breakdown['refund']['mc'] = $this->db->query("select count(product_id) as count, IFNULL(SUM(od.actual_amount),'0.00') as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='2' and od.stylist_id='".$stylist_id."'")->row(); 

			$payment_breakdown['refund']['visa'] = $this->db->query("select count(product_id) as count, IFNULL(SUM(od.actual_amount),'0.00') as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='2' and od.stylist_id='".$stylist_id."'")->row(); 



			// batch total

			$payment_breakdown['batch_total'] = $this->db->query("select count(product_id) as count, IFNULL(SUM(od.actual_amount),'0.00') as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='2' and od.stylist_id='".$stylist_id."'")->row(); 

			// cash tips

			$payment_breakdown['cash_tips'] = $this->db->query("select count(product_id) as count, IFNULL(SUM(od.actual_amount),'0.00') as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='2' and od.stylist_id='".$stylist_id."'")->row(); 


			// credit certificate redeemed

			$payment_breakdown['credit_certificate_redeemed'] = $this->db->query("select count(product_id) as count, IFNULL(SUM(od.actual_amount),'0.00') as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='2' and od.stylist_id='".$stylist_id."'")->row(); 

			// discount

			$payment_breakdown['discounts'] = $this->db->query("select count(product_id) as count, IFNULL(SUM(od.actual_amount),'0.00') as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='2' and od.stylist_id='".$stylist_id."'")->row(); 

			//voids

			$payment_breakdown['voids'] = $this->db->query("select count(product_id) as count, IFNULL(SUM(od.actual_amount),'0.00') as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='2' and od.stylist_id='".$stylist_id."'")->row(); 


			// expected cash for deposit

			$payment_breakdown['expected_cash_for_deposit'] = $this->db->query("select count(product_id) as count, IFNULL(SUM(od.actual_amount),'0.00') as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='2' and od.stylist_id='".$stylist_id."'")->row(); 

			// cash on hand -  drawer

			$payment_breakdown['cash_on_hand'] = $this->db->query("select count(product_id) as count, IFNULL(SUM(od.actual_amount),'0.00') as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='2' and od.stylist_id='".$stylist_id."'")->row(); 

			// over/short

			$payment_breakdown['over_short'] = $this->db->query("select count(product_id) as count, IFNULL(SUM(od.actual_amount),'0.00') as amount from order_detail od inner join orders o on o.order_id=od.order_id INNER JOIN payment p ON p.order_id=o.order_id where p.payment_type='2' and od.stylist_id='".$stylist_id."'")->row(); 
			
			$response['status'] = 1;
			$response['timestamp'] = $timestamp;
			$response['sales'] = $sales;
			$response['payment_breakdown'] = $payment_breakdown;
			$response['message'] = '';

		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		
		echo json_encode($response);
        

	}


	public function salesAndPaymentSummary(){

		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post("vendor_id");
		$start_date = $this->input->post("start_date");
		$end_date = $this->input->post("end_date");
		
		$start_date = date('Y-m-d',strtotime($start_date));
		$end_date = date('Y-m-d',strtotime($end_date));
		
		if(!empty($vendor_id)){
			
			if($start_date=='' || $start_date=='1970-01-01'){
				$filter_date = " ";
				$deposit_date = "";
				
			}
			else{
				$filter_date = " AND (DATE(o.created_date)>='".$start_date."' and DATE(o.created_date)<='".$end_date."')  ";
				$deposit_date = " AND (DATE(di.deposit_date)>='".$start_date."' and DATE(di.deposit_date)<='".$end_date."')  ";
			}

		
			// sales
			$sales['service'] = $this->db->query("
			select (select count(od.product_id) as service_count from order_detail od INNER JOIN orders o ON o.order_id=od.order_id where od.sale_type='1' and o.vendor_id='".$vendor_id."' $filter_date ) as count, 
			
			IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where p.payment_type='1' and o.vendor_id='".$vendor_id."' and od.sale_type='1' $filter_date ),'0.00'  ) AS cash,

			'0.00' as debit,
			
			IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where p.payment_type='2' and o.vendor_id='".$vendor_id."' and od.sale_type='1' $filter_date ),'0.00') AS credit,
			
			'0.00' as gift_card,
			'0.00' as discount,
			IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where (p.payment_type='2' OR p.payment_type='1') and o.vendor_id='".$vendor_id."' and od.sale_type='3' $filter_date ),'0.00') as credit_certificate,
			


			ROUND(IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where p.payment_type='1' and o.vendor_id='".$vendor_id."' and od.sale_type='1' $filter_date ),'0.00') 
			+
			IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where p.payment_type='2' and o.vendor_id='".$vendor_id."' and od.sale_type='1' $filter_date ),'0.00')
			
			+
			IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where (p.payment_type='2' OR p.payment_type='1') and o.vendor_id='".$vendor_id."' and od.sale_type='3' $filter_date ),'0.00')
		,2) as total ")->row();
			
			
			// PRODUCT
			$sales['product'] = $this->db->query("
			 select (select count(od.product_id) as service_count from order_detail od INNER JOIN orders o ON o.order_id=od.order_id where od.sale_type='2' and o.vendor_id='".$vendor_id."' $filter_date ) as count, 
			 
			 IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where p.payment_type='1' and o.vendor_id='".$vendor_id."' and od.sale_type='2' $filter_date ),'0.00') AS cash,
 
			 '0.00' as debit,
			 
			 IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where p.payment_type='2' and o.vendor_id='".$vendor_id."' and od.sale_type='2' $filter_date ),'0.00') AS credit,
			 
			 '0.00' as gift_card,

			 '0.00' as discount,

			 '0.00' as credit_certificate,
			 
			 ROUND((IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where p.payment_type='1' and o.vendor_id='".$vendor_id."' and od.sale_type='2' $filter_date ),'0.00') 
			 +
			 IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where p.payment_type='2' and o.vendor_id='".$vendor_id."' and od.sale_type='2' $filter_date ),'0.00')
			 
			
		),2) as total  ")->row(); 

			// refund

			$refund['service'] = $this->db->query("
			select (select count(od.product_id) as service_count from order_detail od INNER JOIN orders o ON o.order_id=od.order_id where od.sale_type='1' and o.vendor_id='".$vendor_id."' and o.is_refund='1' $filter_date ) as count, 
			
			IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where p.payment_type='1' and o.vendor_id='".$vendor_id."' and od.sale_type='1' and o.is_refund='1' $filter_date ),'0.00'  ) AS cash,

			'0.00' as debit,
			
			IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where p.payment_type='2' and o.vendor_id='".$vendor_id."' and od.sale_type='1' and o.is_refund='1' $filter_date ),'0.00') AS credit,
			
			'0.00' as gift_card,
			'0.00' as discount,
			IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where (p.payment_type='2' OR p.payment_type='1') and o.vendor_id='".$vendor_id."' and od.sale_type='3' and o.is_refund='1' $filter_date ),'0.00') as credit_certificate,
			


			ROUND(IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where p.payment_type='1' and o.vendor_id='".$vendor_id."' and od.sale_type='1' and o.is_refund='1' $filter_date ),'0.00') 
			+
			IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where p.payment_type='2' and o.vendor_id='".$vendor_id."' and od.sale_type='1' and o.is_refund='1' $filter_date ),'0.00')
			
			+
			IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where (p.payment_type='2' OR p.payment_type='1') and o.vendor_id='".$vendor_id."' and od.sale_type='3' and o.is_refund='1' $filter_date ),'0.00')
		,2) as total  ")->row();

			$refund['product'] = $this->db->query("
			 select (select count(od.product_id) as service_count from order_detail od INNER JOIN orders o ON o.order_id=od.order_id where od.sale_type='2' and o.vendor_id='".$vendor_id."' and o.is_refund='1' $filter_date ) as count, 
			 
			 IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where p.payment_type='1' and o.vendor_id='".$vendor_id."' and od.sale_type='2' and o.is_refund='1' $filter_date ),'0.00') AS cash,
 
			 '0.00' as debit,
			 
			 IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where p.payment_type='2' and o.vendor_id='".$vendor_id."' and od.sale_type='2' and o.is_refund='1' $filter_date ),'0.00') AS credit,
			 
			 '0.00' as gift_card,

			 '0.00' as discount,

			 '0.00' as credit_certificate,
			 
			 ROUND((IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where p.payment_type='1' and o.vendor_id='".$vendor_id."' and od.sale_type='2' and o.is_refund='1' $filter_date ),'0.00') 
			 +
			 IFNULL((select sum(od.actual_amount) from order_detail od INNER JOIN payment p ON p.order_id=od.order_id INNER JOIN orders o ON o.order_id=od.order_id where p.payment_type='2' and o.vendor_id='".$vendor_id."' and od.sale_type='2' and o.is_refund='1' $filter_date ),'0.00')
			 
			
		),2) as total  ")->row();

			 // tax

			$tax_type = $this->db->query("select description from tax_product where vendor_id='".$vendor_id."' and type='current' ")->result();

			$tax[$tax_type[0]->description] = $this->db->query("select IFNULL((select SUM(o.tax1) from orders o INNER JOIN payment p ON p.order_id=o.order_id  where o.vendor_id='".$vendor_id."'  AND p.payment_type='1'  $filter_date ),'0.00') AS cash,
			'0.00' as debit, 
			IFNULL((select SUM(o.tax_amount) from orders o INNER JOIN payment p ON p.order_id=o.order_id  where o.vendor_id='".$vendor_id."'  AND p.payment_type='2'  $filter_date ),'0.00')  as credit,
			
			FORMAT(IFNULL((select SUM(o.tax1) from orders o INNER JOIN payment p ON p.order_id=o.order_id  where o.vendor_id='".$vendor_id."'  AND p.payment_type='1'  $filter_date ),'0.00')+
			IFNULL((select SUM(o.tax_amount) from orders o INNER JOIN payment p ON p.order_id=o.order_id  where o.vendor_id='".$vendor_id."'  AND p.payment_type='2'  $filter_date ),'0.00'),2)
			as total ")->row();

			$tax[$tax_type[1]->description] = $this->db->query("select IFNULL((select SUM(o.tax2) from orders o INNER JOIN payment p ON p.order_id=o.order_id  where o.vendor_id='".$vendor_id."'  AND p.payment_type='1'  $filter_date ),'0.00') AS cash, '0.00' as debit, '0.00' as credit, 
			
			FORMAT(IFNULL((select SUM(o.tax2) from orders o INNER JOIN payment p ON p.order_id=o.order_id  where o.vendor_id='".$vendor_id."'  AND p.payment_type='1'  $filter_date ),'0.00')+
			IFNULL((select SUM(o.tax_amount) from orders o INNER JOIN payment p ON p.order_id=o.order_id  where o.vendor_id='".$vendor_id."'  AND p.payment_type='2'  $filter_date ),'0.00'),2)
			as total  ")->row();

			$tax[$tax_type[2]->description] = $this->db->query("select IFNULL((select SUM(o.tax3) from orders o INNER JOIN payment p ON p.order_id=o.order_id  where o.vendor_id='".$vendor_id."'  AND p.payment_type='1'  $filter_date ),'0.00') AS cash, '0.00' as debit, '0.00' as credit,  
			
			FORMAT(IFNULL((select SUM(o.tax3) from orders o INNER JOIN payment p ON p.order_id=o.order_id  where o.vendor_id='".$vendor_id."'  AND p.payment_type='1'  $filter_date ),'0.00')+
			IFNULL((select SUM(o.tax_amount) from orders o INNER JOIN payment p ON p.order_id=o.order_id  where o.vendor_id='".$vendor_id."'  AND p.payment_type='2'  $filter_date ),'0.00'),2)
			as total ")->row();

			//tips

			$tips = $this->db->query("select (select count(o.order_id) as num from orders o where o.vendor_id='".$vendor_id."' and tip_amount!='0.00' $filter_date ) as count,
			IFNULL((select sum(o.tip_amount) from orders o INNER JOIN payment p ON p.order_id=o.order_id where o.vendor_id='".$vendor_id."' and p.payment_type='1' $filter_date ),'0.00') AS cash,
			'0.00' as debit,
			IFNULL((select sum(o.tip_amount) from orders o INNER JOIN payment p ON p.order_id=o.order_id where o.vendor_id='".$vendor_id."' and p.payment_type='2' $filter_date ),'0.00') as credit, 
			
			FORMAT(IFNULL((select sum(o.tip_amount) from orders o INNER JOIN payment p ON p.order_id=o.order_id where o.vendor_id='".$vendor_id."' and p.payment_type='1' $filter_date ),'0.00') +

			IFNULL((select sum(o.tip_amount) from orders o INNER JOIN payment p ON p.order_id=o.order_id where o.vendor_id='".$vendor_id."' and p.payment_type='2' $filter_date ),'0.00' ),2)
			as total ")->row();

			//deposit
			$deposit = $this->db->query("select count(dc.id) as count, IFNULL(sum(di.amount),'0.00') as credit, IFNULL(sum(di.amount),'0.00') as total from deposit_customer dc  INNER JOIN deposit_installment di ON di.deposit_id=dc.id where dc.vendor_id='".$vendor_id."' $deposit_date  ")->row();

			//gift card sold
			$gift_card_sold = $this->db->query("select (select count(o.order_id) as num from orders o INNER JOIN order_detail od ON od.order_id=o.order_id  where o.vendor_id='".$vendor_id."' and od.sale_type='4' $filter_date ) as count, 
			
			IFNULL((select sum(od.actual_amount) from orders o INNER JOIN order_detail od on od.order_id=o.order_id INNER JOIN payment p ON p.order_id=o.order_id where o.vendor_id='".$vendor_id."' and od.sale_type='4' and p.payment_type='1' $filter_date ),'0.00') as cash,
			
			'0.00' as debit,
			
			IFNULL((select sum(od.actual_amount) from orders o INNER JOIN order_detail od on od.order_id=o.order_id INNER JOIN payment p ON p.order_id=o.order_id where o.vendor_id='".$vendor_id."' and od.sale_type='4' and p.payment_type='2' $filter_date ),'0.00') as credit,
			
			FORMAT(IFNULL((select sum(od.actual_amount) from orders o INNER JOIN order_detail od on od.order_id=o.order_id INNER JOIN payment p ON p.order_id=o.order_id where o.vendor_id='".$vendor_id."' and od.sale_type='4' and p.payment_type='1' $filter_date ),'0.00') +
			
			IFNULL((select sum(od.actual_amount) from orders o INNER JOIN order_detail od on od.order_id=o.order_id INNER JOIN payment p ON p.order_id=o.order_id where o.vendor_id='".$vendor_id."' and od.sale_type='4' and p.payment_type='2' $filter_date ),'0.00'),2)
			AS total 
		

			  ")->row();

			//cash discount
			$cash_discount = $this->db->query("select '0' as count,  '0.00' as credit, '0.00' as total ")->row();

			//paid out
			$paid_out = $this->db->query("select '0' as count,  '0.00' as cash, '0.00' as total ")->row();

			//drawer short/over
			$drawer_short_over = $this->db->query("select  '0.00' as cash, '0.00' as total ")->row();

			//drawer short/over
		//	$grand_total = $this->db->query("select '0' as count, '0.00' as cash,'0.00' as debit,'0.00' as credit,'0.00' as gift_card,'0.00' as discount,'0.00' as credit_certificate, '0.00' as total ")->row();

		$gc_2 = $sales['service']->total+$sales['product']->total+$tax[$tax_type[0]->description]->total+$tips->total+$deposit->total+$gift_card_sold->total;
		
		$refund_total = $refund['service']->total+$refund['product']->total;

		$gc = number_format(($sales['service']->total+$sales['product']->total+$tax[$tax_type[0]->description]->total+$tax[$tax_type[1]->description]->total+$tax[$tax_type[2]->description]->total+$tips->total+$deposit->total+$gift_card_sold->total)-$refund_total,2);
		
		$refund_count = $refund['service']->count+$refund['product']->count;

		$total_count = $sales['service']->count+$sales['product']->count+$tips->count+$deposit->count+$gift_card_sold->count+$cash_discount->count-$refund_count;;
		
		//echo $gc;die;

		$transaction_count = $this->db->query("select count(o.order_id) as num from orders o where o.vendor_id='".$vendor_id."' $filter_date")->row()->num;

		//$count = $transaction_count;
		$total_cash_tax = $tax[$tax_type[0]->description]->total+$tax[$tax_type[1]->description]->total+$tax[$tax_type[2]->description]->total;

		$refund_cash = $refund['service']->cash+$refund['product']->cash;

		$total_cash = number_format(($sales['service']->cash+$sales['product']->cash+$total_cash_tax+$tips->cash+$gift_card_sold->cash)-$refund_cash,2);

		$refund_credit = $refund['service']->credit+$refund['product']->credit;

		$credit = number_format(($sales['service']->credit+$sales['product']->credit)-$refund_credit,2);

		$refund_gift_card = $refund['service']->gift_card+$refund['product']->giftcard;

		$gift_card = number_format(($sales['service']->gift_card+$sales['product']->gift_card)-$refund_gift_card,2);

		$refund_credit_certificate = $refund['service']->credit_certificate+$refund['product']->credit_certificate;

		$credit_certificate = number_format(($sales['service']->credit_certificate+$sales['product']->credit_certificate)-$refund_credit_certificate,2);

		$grand_total = array('count'=>$total_count,'cash'=>$total_cash,'debit'=>'0.00','credit'=>$credit,'gift_card'=>$gift_card,'discount'=>'0.00','credit_certificate'=>$credit_certificate,'total'=>$gc);


		//$summary = $this->db->query("select count(o.order_id) as transaction_count, SUM(o.order_amount) as gross_sales, SUM(o.order_amount) AS net_sales from orders o where o.vendor_id='".$vendor_id."' ")->row();
		//echo $tax[$tax_type[0]->description]->total;die;
		//echo "aa ".$deposit->total;die;
		$net_sales_items = $tax[$tax_type[0]->description]->total+$tips->total+$deposit->total+$cash_discount->total;
		
		$net_sales = $gc_2-$net_sales_items;



		$summary = array('transaction_count'=>$transaction_count,'gross_sales'=>$gc,'net_sales'=>number_format($net_sales,2));
			
//	echo "<pre>";print_r((object) $grand_total);die;
	

			$response['status'] = 1;
			$response['summary'] = $summary;
			$response['sales'] = $sales;
			$response['refund'] = $refund;
			$response['tax'] = $tax;
			$response['tips'] = $tips;
			$response['deposit'] = $deposit;
			$response['gift_card_sold'] = $gift_card_sold;
			$response['cash_discount'] = $cash_discount;
			$response['paid_out'] = $paid_out;
			$response['drawer_short_over'] = $drawer_short_over;
			$response['grand_total'] = $grand_total;
			$response['message'] = '';

		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		
		echo json_encode($response);
        

	}



	
	
}
