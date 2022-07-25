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


class Payment_group extends CI_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $country = $this->ip_info("Visitor", "Country");
		if($country=='India'){
			date_default_timezone_set("Asia/Kolkata");
		}else{
			
			date_default_timezone_set("America/Los_Angeles");
		}
        $this->load->library('zend');
		//load in folder Zend
		$this->zend->load('Zend/Barcode');
       // error_reporting(0);
    }
   
       
       
		
		
    
   
 


function update_deposit(){
        $id=$this->input->post('id');
        //$amount=$this->input->post('amount');
       // $balance_due=$this->input->post('balance_due');
        if(!empty($id)){
         
               $query= $this->db->query('update deposit_installment set is_active=1 where id="'.$id.'"');
               if($query){
                $response['reciptedata']=$this->order_invoice_deposit($id);
               }else{
                $response['reciptedata']='Something Wrong';
               }
            
            
        }else{
        $response['status'] = 0;
        $response['message'] = 'Required parameter missing!';
        
        
    }
                
                echo json_encode($response);
    }
	
	  public function getColorIdByColorType($color_type,$vendor_id){
	  	 	$query = $this->db->query("select color_id from color_settings where vendor_id='".$vendor_id."' and color_type='".$color_type."' ");
	  		$res = $query->row()->color_id;
	  		return $res;
 	 }
	
 function ordernow(){
 //	echo "fmg";die;
//	echo "<pre>";print_r($_POST);exit;
	 $customer_id = $this->input->post('customer_id');
	 $appointment_id = $this->input->post('appointment_id'); 
	 $token = $this->input->post('token'); 
	 $payment_type = $this->input->post('payment_type');
	 $cuppon_number = $this->input->post('cuppon_number');
	 $cuppon_value = $this->input->post('cuppon_value');
	 $service_charge = $this->input->post('service_charge');
	  $deposit_id = $this->input->post('deposit_id');
	  $deposit_amount = $this->input->post('deposit_amount');
	 $signature_img = $this->input->post('signature_img');
	 
		
		
	 if(!empty($signature_img) || $signature_img!=NULL || $signature_img!="" || $signature_img!=null){
			$path = '../assets/img/signature/';
			$file = time().'.jpeg';
			$signature_img = $this->base64_to_jpeg($signature_img,$path.$file);
			$signature = $file;
		
		}else{
			$signature = '';
		}

			 $appointment_id_new=$appointment_id;
		  if($appointment_id!=0 || $appointment_id!='' || $appointment_id!=NULL){
		    $appointment_id = explode(',',$appointment_id);
		  }else{
		    $appointment_id = 0;
		  }
    $appointment_row_amount = explode(',',$this->input->post('appointment_row_amount')); 
	$appointment_tax_amount = explode(',',$this->input->post('appointment_tax_amount')); 
	$appointment_discount_amount = explode(',',$this->input->post('appointment_discount_amount')); 
	$appointment_total_amount= explode(',',$this->input->post('appointment_total_amount'));


	$cart_id =  explode(',',$this->input->post('cart_id')); 
  $product_id = explode(',',$this->input->post('product_id')); 
	$product_row_amount = explode(',',$this->input->post('product_row_amount'));
	$product_stylist_id = explode(',',$this->input->post('product_stylist_id')); 
	$product_tax_amount = explode(',',$this->input->post('product_tax_amount')); 
	$product_discount_amount = explode(',',$this->input->post('product_discount_amount')); 
	$product_total_amount= explode(',',$this->input->post('product_total_amount'));
	$product_row_quantity= explode(',',$this->input->post('product_row_quantity'));
	$gift_certificate_id = $this->input->post('gift_certificate_id');
	$gift_certificate_amount = explode(',',$this->input->post('gift_certificate_amount'));
	if($gift_certificate_id!=0 || $gift_certificate_id!='' || $gift_certificate_id!=NULL){
		    $gift_certificate_id_new = explode(',',$gift_certificate_id);
		  }else{
		    $gift_certificate_id_new = 0;
		  }
	$gift_cart_id = $this->input->post('gift_cart_id');
	$gift_cart_amount = explode(',',$this->input->post('gift_cart_amount'));
	if($gift_cart_id!=0 || $gift_cart_id!='' || $gift_cart_id!=NULL){
		    $gift_cart_id_new = explode(',',$gift_cart_id);
		  }else{
		    $gift_cart_id_new = 0;
		  }
 $total_tax_value = $this->input->post('total_tax_value');
 $total_discount=$_POST['total_discount'];
 $other_discount=$_POST['other_discount'];
 $tip_value = $this->input->post('tip_value');
 $cash_value = $this->input->post('cash_value');
 $credit_value = $this->input->post('credit_value');
 $gift_card_number = $this->input->post('gift_number');
 $gift_card_value = $this->input->post('gift_value');
 $certificate_number = $this->input->post('certificate_number');
 $certificate_value= $this->input->post('certificate_value');
 $iou_value = $this->input->post('iou_value');
 $login_id=$this->input->post('login_id');
 $reward_value=$_POST['reward_value'];
 $iou_id=$this->input->post('iou_id');
 $total_amount=$this->input->post('total_amount');
 $vendor_id=$_POST['vendor_id'];
 $balance_due=$_POST['balance_due'];
 $payment_type_final=$this->input->post('payment_type_final');
 $vendor_id=$this->input->post('vendor_id');
if($payment_type==1){
    $payment_status = 2; //success
    $status_id=2;
    $order_number = 'ORD' . date('mdYHis');
    $bank_txn_id = "";
    $response_code = "";
    $currency = "$";
    $message = "Payment Received";
    $transaction_id = 'TXNQ' . date('mdYHis');
  } else {
    $payment_status = 2; //success
    $status_id=1;
    $order_number = 'ORD' . date('mdYHis');
    $bank_txn_id = "";
    $response_code = "";
    $currency = "$";
    $message = "Payment Pending";
    $transaction_id = 'TXNQ' . date('mdYHis');
  } 
  $get_deposit_key=$this->input->post('get_deposit_key');
  if($get_deposit_key=='extra_deposit'){
  	$extra_deposit=1;
  }else{
  	$extra_deposit=0;
  }
  $return_amount=$this->input->post('return_amount');
  if($return_amount==''){
  	$return_amount=0;
  }else{
  	$return_amount=$return_amount;
  }
  if($payment_type_final=='process' && $balance_due==0){
  	$status_id=2;
  }else{
  	$status_id=1;
  }
  $amount_paid=$total_amount -$balance_due;
 if($payment_type_final=='process'){
 	//echo "dlkkdl";die;
   $query3 = $this->db->query("insert into orders set vendor_id='".$this->input->post('vendor_id')."',login_id='".$this->input->post('login_id')."', customer_id='".$customer_id."',status_id='".$status_id."', order_type='1', order_number='".$order_number."', order_amount='".$total_amount."',tip_amount='".$tip_value."',discount_amount='".$discount_value."',iou_amount='".$iou_value."',credit_card_amount='".$credit_value."',cash_amount='".$cash_value."',gift_cert_amount='".$certificate_value."',gift_cart_amount='".$gift_card_value."',rewards_money='".$reward_value."',tax_amount='".$total_tax_value."',cuppon_value='".$cuppon_value."',is_active='1',is_delete='0',deposit_id='".$deposit_id."',diposite_amount='".$deposit_amount."',is_extra_deposit='".$extra_deposit."',return_amount='".$return_amount."',created_date='".date('Y-m-d h:i:s')."' ");
 	//echo $this->db->last_query();exit;
	
 	   $order_id = $this->db->insert_id();
 	 if($order_id){
		 
 	   	$query2 = $this->db->query("insert into payment set order_id='".$order_id."',payment_type='".$payment_type."', status_id='2',payment_status=1,transaction_id='".$transaction_id."', bank_txn_id='".$bank_txn_id."', response_code='".$response_code."', amount='".$amount_paid."', message='".$message."', created_date='".date('Y-m-d h:i:s')."', customer_id='".$customer_id."',vendor_id='".$vendor_id."', signature='".$signature."' ");  
  		
			$payment_id = $this->db->insert_id();

			/*if($iou_id !=''){
		      $this->db->query('update customer_iou_amount set status=1 where id="'.$iou_id.'"');
		    }
		    if(($iou_value !='' || $iou_value !=0)){
		      $this->db->query('insert into customer_iou_amount set customer_id="'.$customer_id.'",order_id="'.$order_id.'",iou_amount="'.$iou_value.'",status=0');
		    }*/
		    if($query3){
			/*if(!empty($product_id)){*/
		      /*$query31 = $this->db->query("select * from cart where cart_id IN(".$cart_id.") ");
		      $cart_data = $query31->result();
			*/

		      if(!empty($cart_id)){
		        foreach($cart_id as $key=>$cd){
		        	$getCommission=$this->db->query('select commission_type,commission from stylist where stylist_id="'.$product_stylist_id[$key].'"')->row();
		        	if(!empty($getCommission->commission_type !='')){
		        		if($getCommission->commission_type=='Percentage'){
		        			$product_commission=$product_total_amount[$key] * $getCommission->commission/100;
		        		}else{
		        			$product_commission=$getCommission->commission;
		        		}
		        	}else{
		        		$product_commission=0;
		        	}
				
		          $product_data=$this->db->query('select quantity from product where product_id="'.$product_id[$key].'"')->row();
		          $qaunt=$product_data->quantity-$cd->quantity;
		         // $this->db->query("update product set quantity='".$qaunt."' where product_id='".$product_id[$key]."' ");
		        
				$query32 = $this->db->query("insert into order_detail set order_id='".$order_id."', product_id='".$product_id[$key]."',tax_amount='".$product_tax_amount[$key]."',discount_amount='".$product_discount_amount[$key]."',actual_amount='".$product_row_amount[$key]."',total_paid_amount='".$product_total_amount[$key]."', quantity='".$product_row_quantity[$key]."',stylist_id='".$product_stylist_id[$key]."',product_commission='".$product_commission."',sale_type=2");
      			 }
      		}
		}

			      if(!empty($gift_certificate_id)){
			     if($query3){
			    
			      $ids=explode(",",$gift_certificate_id);
			      $countId=count(@$ids);
			      for($i=0;$i<$countId;$i++){
			      $query32 = $this->db->query("insert into order_detail set order_id='".$order_id."', product_id='".$ids[$i]."',actual_amount='".$gift_certificate_amount[$i]."',total_paid_amount='".$gift_certificate_amount[$i]."', quantity='0',sale_type=3");

			    }
			    /*$this->db->query('update gift_certificate set is_active=1 where gift_id IN('.$gift_certificate_id.')');*/
			  
			  } 
			  }
			  if(!empty($gift_cart_id)){ 
			  if($query3){
			    
			      $ids=explode(",",$gift_cart_id);
			      $countId=count(@$ids);
			      for($i=0;$i<$countId;$i++){
			      $query32 = $this->db->query("insert into order_detail set order_id='".$order_id."', product_id='".$ids[$i]."',actual_amount='".$gift_cart_amount[$i]."',total_paid_amount='".$gift_cart_amount[$i]."', quantity='0',sale_type=4");
			    }
			      
			    /*$this->db->query('update gift_card set is_active=1 where card_id IN('.$gift_cart_id.')');*/
			  
			  } 
			}
			if($appointment_id_new!=0 && $appointment_id_new!=null){
				$stylist_id=$this->db->query('select stylist_id from appointment_service where appointment_id IN ('.$appointment_id_new.')')->result();
			      for($i=0;$i<count($appointment_id);$i++){
			      if($appointment_id_new!=0){
			        $stylist_ids = $stylist_id[$i]->stylist_id;
			        }else{
			        $stylist_ids = 0;
			        }
			        $getCommission=$this->db->query('select commission_type,commission from stylist where stylist_id="'.$stylist_ids.'"')->row();
		        	if(!empty($getCommission->commission_type !='')){
		        		if($getCommission->commission_type=='Percent'){
		        			$service_commission=$appointment_total_amount[$i] * $getCommission->commission/100;
		        		}else{
		        			$service_commission=$getCommission->commission;
		        		}
		        	}else{
		        		$service_commission=0;
		        	}
			        $queryy = $this->db->query("insert into order_detail set order_id='".$order_id."', product_id='".$appointment_id[$i]."',tax_amount='".$appointment_tax_amount[$i]."',discount_amount='".$appointment_discount_amount[$i]."',actual_amount='".$appointment_row_amount[$i]."',total_paid_amount='".$appointment_total_amount[$i]."',sale_type=1,stylist_id='".$stylist_ids."',service_commission='".$service_commission."' ");
			      }
			}
		if($payment_type==1){
			$status_id=2;
		}else{
			$status_id=1;
		}

	if($balance_due <=0 ){
 	 	if($payment_type==1){
 	 		//echo "jdhdj";
      $this->db->query('update payment set status_id=2 where payment_id="'.$payment_id.'"');
      $this->OrderUpdate($order_id);
      if(!empty($deposit_id) && $deposit_id!=0 && $deposit_amount!=0){
      	 $this->depositUpdate($deposit_id,$deposit_amount,$order_id);
      }
     
       if($iou_id !=''){
              $this->db->query('update customer_iou_amount set status=1 where id="'.$iou_id.'"');
            }
            if(($iou_value !='' || $iou_value !=0)){
              $this->db->query('insert into customer_iou_amount set customer_id="'.$customer_id.'",order_id="'.$order_id.'",iou_amount="'.$iou_value.'",status=0');
            }
 	 		$response['status']=1;
 	 		$response['message']="Order created successfully!";
 	 		$response['order_number'] = $order_number;
 	 		$response['balance_due'] = $balance_due;
 	 		$response['reciptedata']=$this->order_invoice($order_id,$vendor_id);

 	 	}else{
 	 		$this->setServiceCharge($order_id,$payment_id,$service_charge);
 	 		//echo "kddk";die;
 	 		$payment_tra=$this->db->query('select transaction_id from payment where payment_id="'.$payment_id.'"')->row();
            $response['status'] = 1;
            $response['reciptedata']=(Object)[];
            $response['transaction_id'] =$payment_tra->transaction_id;
            $response['order_id'] = $order_id;
            $response['payment_id']=$payment_id;
             $response['deposit_id']=$deposit_id;
              $response['deposit_amount']=$deposit_amount;
             $response['balance_due']=$balance_due;
            $response['message'] = "Card Status Pending";
 	 	}

 	 }else{
 	 	if($payment_type==1){
 	 		//echo "jdhdj";
 	  if(!empty($deposit_id) && $deposit_id!=0 && $deposit_amount!=0){
      	 $this->depositUpdate($deposit_id,$deposit_amount,$order_id);
      }
      $this->db->query('update payment set status_id=2 where payment_id="'.$payment_id.'"');
 	 		$payment_tra=$this->db->query('select transaction_id from payment where payment_id="'.$payment_id.'"')->row();
            $response['status'] = 1;
            $response['reciptedata']=(Object)[];
            $response['transaction_id'] =$payment_tra->transaction_id;
            $response['order_id'] = $order_id;
            $response['payment_id']=$payment_id;
             $response['balance_due']=$balance_due;
            $response['message'] = "Payment Pending";
 	 	}else{
 	 		//echo "kddk";die;
 	 		$this->setServiceCharge($order_id,$payment_id,$service_charge);
 	 		$payment_tra=$this->db->query('select transaction_id from payment where payment_id="'.$payment_id.'"')->row();
            $response['status'] = 1;
            $response['reciptedata']=(Object)[];
            $response['transaction_id'] =$payment_tra->transaction_id;
            $response['order_id'] = $order_id;
            $response['payment_id']=$payment_id;
            $response['deposit_id']=$deposit_id;
              $response['deposit_amount']=$deposit_amount;
             $response['balance_due']=$balance_due;

            $response['message'] = "Card Status Pending";
 	 	}
 	 }
 	 }else{
 	 	$response['status']=0;
 	 	$response['reciptedata']=array();
 	 	$response['message']="order Not Created";
 	 }

 }else if($payment_type_final=='due' && $balance_due > 0){
 			$query2 = $this->db->query("insert into payment set order_id='".$_POST['order_id']."',payment_type='".$payment_type."', status_id='".$status_id."',payment_status=1,transaction_id='".$transaction_id."', bank_txn_id='".$bank_txn_id."', response_code='".$response_code."', amount='".$amount_paid."', message='".$message."', created_date='".date('Y-m-d h:i:s')."', customer_id='".$customer_id."',vendor_id='".$vendor_id."' ");  
 			$payment_id = $this->db->insert_id();
 			if($payment_type==1){
        $this->db->query('update payment set status_id=2 where payment_id="'.$payment_id.'"');
 				$payment_tra=$this->db->query('select transaction_id from payment where payment_id="'.$payment_id.'"')->row();
            $response['status'] = 1;
            $response['reciptedata']=(Object)[];
            $response['transaction_id'] =$payment_tra->transaction_id;
            $response['order_id'] = $_POST['order_id'];
            $response['payment_id']=$payment_id;
            $response['balance_due'] = $balance_due;
             $response['deposit_id']=0;
              $response['deposit_amount']=0;
            $response['message'] = "Card Status Pending";
 			}else{
 			$payment_tra=$this->db->query('select transaction_id from payment where payment_id="'.$payment_id.'"')->row();
 			$this->setServiceCharge($_POST['order_id'],$payment_id,$service_charge);
            $response['status'] = 1;
           $response['reciptedata']=(Object)[];
            $response['transaction_id'] =$payment_tra->transaction_id;
            $response['order_id'] = $_POST['order_id'];
            $response['payment_id']=$payment_id;
            $response['balance_due'] = $balance_due;
             $response['deposit_id']=0;
              $response['deposit_amount']=0;
            $response['message'] = "Card Status Pending";
 			}
 			
 }else{
 	$query2 = $this->db->query("insert into payment set order_id='".$_POST['order_id']."',payment_type='".$payment_type."', status_id='".$status_id."',payment_status=1,transaction_id='".$transaction_id."', bank_txn_id='".$bank_txn_id."', response_code='".$response_code."', amount='".$amount_paid."', message='".$message."', created_date='".date('Y-m-d h:i:s')."', customer_id='".$customer_id."',vendor_id='".$vendor_id."' ");  
 	$payment_id = $this->db->insert_id();
 	$order=$this->db->query('select order_number from orders where order_id="'.$_POST['order_id'].'"')->row();
 		if($payment_type==1){
      $this->db->query('update payment set status_id=2 where payment_id="'.$payment_id.'"');
      $this->OrderUpdate($_POST['order_id']);
      /*if($iou_id !=''){
              $this->db->query('update customer_iou_amount set status=1 where id="'.$iou_id.'"');
            }
            if(($iou_value !='' || $iou_value !=0)){
              $this->db->query('insert into customer_iou_amount set customer_id="'.$customer_id.'",order_id="'.$order_id.'",iou_amount="'.$iou_value.'",status=0');
            }*/
 	 		$response['status']=1;
 	 		$response['message']="Order Created Succesfully";
 	 		$response['order_number'] = $order->order_number;
 	 		$response['reciptedata']=$this->order_invoice($_POST['order_id'],$vendor_id);
 	 		 $response['balance_due'] = $balance_due;
 	 	}else{
 	 		$this->setServiceCharge($_POST['order_id'],$payment_id,$service_charge);
 	 		$payment_tra=$this->db->query('select transaction_id from payment where payment_id="'.$payment_id.'"')->row();
            $response['status'] = 1;
           $response['reciptedata']=(Object)[];
            $response['transaction_id'] =$payment_tra->transaction_id;
            $response['balance_due'] = $balance_due;
            $response['order_id'] = $_POST['order_id'];
            $response['payment_id']=$payment_id;
             $response['deposit_id']=0;
              $response['deposit_amount']=0;
            $response['message'] = "Card Status Pending";

 	 	}
 }
 echo json_encode($response);
}
 function getManageData(){
    /*open appointement*/
    $deposit_id=$this->input->post('deposit_id');
    if(!empty($deposit_id)){
      $query = $this->db->query('select A.customer_id,concat(C.firstname," ",C.lastname) as cust_name from appointment as A inner join customer as C on A.customer_id=C.customer_id where A.deposit_id="'.$deposit_id.'" and A.is_checkout=0 group by customer_id');
    
    
    $result = $query->result();
    foreach($result as $key=> $res){
      $customer[]['app']=$this->db->query('select A.appointment_id,A.customer_id,date_format(A.date,"%m/%d/%Y") as dt,A.appointment_type,B.price,concat(C.firstname," ",C.lastname) as cust_name,D.service_name,concat(e.firstname," ",e.lastname) as styl_name from appointment as A inner join appointment_service as B on A.appointment_id=B.appointment_id inner join customer as C on A.customer_id=C.customer_id inner join service as D on B.service_id=D.service_id inner join stylist as e on B.stylist_id=e.stylist_id where A.customer_id="'.$res->customer_id.'" and deposit_id="'.$deposit_id.'" and A.is_checkout=0')->result();
      $customer[$key]['customer_name']=$res->cust_name;
      $customer[$key]['customer_id']=$res->customer_id;
      $customer[$key]['deposit']=$this->db->query('select deposit_amount as dep_amount from deposit_customer where customer_id="'.$res->customer_id.'" and deposit_id="'.$deposit_id.'"')->row();
    }
    $service_group=$this->db->query('select count(B.service_id) as total_service,sum(B.price) as total_amount,C.service_name as ser_name from appointment as A inner join appointment_service as B on A.appointment_id=B.appointment_id inner join service as C on B.service_id=C.service_id where A.deposit_id="'.$deposit_id.'" and A.is_checkout=0 group by B.service_id')->result();
    $deposit_data=$this->db->query('select A.id,A.deposit_amount,A.deposit_type,A.customer_total,concat(B.firstname," ",B.lastname) as cust_name,B.mobile_phone from deposit_customer as A inner join customer as B on A.customer_id=B.customer_id where A.id="'.$deposit_id.'" and A.status=1')->row();
    $service_data=$customer;
/*open appointment*/


/*close apointment*/
$customer1=array();
$query1 = $this->db->query('select A.customer_id,concat(C.firstname," ",C.lastname) as cust_name from appointment as A inner join customer as C on A.customer_id=C.customer_id where A.deposit_id="'.$deposit_id.'" and A.is_checkout=1 group by customer_id');
    
    $result1 = $query1->result();
    foreach($result1 as $key1=> $res1){
      $customer1[]['app']=$this->db->query('select A.appointment_id,A.customer_id,date_format(A.date,"%m/%d/%Y") as dt,A.appointment_type,B.price,concat(C.firstname," ",C.lastname) as cust_name,D.service_name,concat(e.firstname," ",e.lastname) as styl_name from appointment as A inner join appointment_service as B on A.appointment_id=B.appointment_id inner join customer as C on A.customer_id=C.customer_id inner join service as D on B.service_id=D.service_id inner join stylist as e on B.stylist_id=e.stylist_id where A.customer_id="'.$res1->customer_id.'" and token_no="'.$deposit_id.'" and A.is_checkout=1')->result();
      $customer1[$key1]['customer_name']=$res1->cust_name;
      $customer1[$key1]['customer_id']=$res1->customer_id;
      //echo 'select deposit_amount as dep_amount from deposit_customer where customer_id="'.$res1->customer_id.'" and deposit_id="'.$deposit_id.'"';die;
      $customer1[$key1]['deposit']=$this->db->query('select deposit_amount as dep_amount from deposit_customer where customer_id="'.$res1->customer_id.'" and deposit_id="'.$deposit_id.'"')->row();
      $customer1[$key1]['customer_id']=$res1->customer_id;
    }
    
    $service_data_close=$customer1;
/*close appointment*/

if(!empty($service_data) || !empty($service_data_close)){
  $response['status']=1;
  $response['sidebar_data'] = $service_group; 
  $response['service_data'] = $service_data; 
  $response['deposit_data'] = $deposit_data; 
   $response['service_data_close'] = $service_data_close; 
   $response['msg']=""; 
  

}else{
$response['status']=0;
  $response['sidebar_data'] =''; 
  $response['service_data'] = ''; 
  $response['deposit_data'] = '';
  $response['service_data_close'] = '';
  $response['msg']="No data available"; 
}

}else{
  $response['message'] = 'Required parameter missing!'; 
  $response['status']=0;
}
 echo json_encode($response);
}
/*Get Sidebar update data*/
public function get_data(){
    $ids=$_POST['ids'];
  //  $token_no=$_POST['token'];
    $deposit_id=$_POST['deposit_id'];
    if(!empty($deposit_id)){
    if($ids!=0){
      $con="and A.customer_id IN(".$ids.")";
      $con2="and customer_id IN(".$ids.") and status=1";
    }else{
      $con="";
    }
  //  $data['token']=$token_no;
   // $data['deposit_id']=$deposit_id;
    ///echo 'select count(B.service_id) as total_service,sum(B.price) as total_amount,C.service_name as ser_name from appointment as A inner join appointment_service as B on A.appointment_id=B.appointment_id inner join service as C on B.service_id=C.service_id where A.deposit_id="'.$deposit_id.'" '.$con.' and A.is_checkout=0 group by B.service_id';die;
    $service_group=$this->db->query('select count(B.service_id) as total_service,sum(B.price) as total_amount,C.service_name as ser_name from appointment as A inner join appointment_service as B on A.appointment_id=B.appointment_id inner join service as C on B.service_id=C.service_id where A.deposit_id="'.$deposit_id.'" '.$con.' and A.is_checkout=0 group by B.service_id')->result();
   // $data['deposit']=$this->db->query('select deposit_amount as dep_amount from deposit_customer where deposit_id="'.$deposit_id.'"'.$con2)->result();
   // $data['deposit_main']=$this->db->query('select * from deposit_customer where id="'.$deposit_id.'"')->row();
    //print_r($data['service_group']);exit;
  if(!empty($service_group)){
    $response['status']=1;
  $response['sidebar_data'] = $service_group; 
}else{
  $response['status']=1;
  $response['sidebar_data'] =''; 
}

    //echo $ids;
  }else{
     $response['message'] = 'Required parameter missing!'; 
  $response['status']=0;
  }
}
function checkpin(){
	$pin=$this->input->post('pin');
	if(!empty($pin)){
	$checkRow=$this->db->query('select login_id from login where pin="'.$pin.'"')->row();
	if(!empty($checkRow)){
		$response['status']=1;
  	$response['login_id'] =$checkRow->login_id;
  	$response['msg']="Login successfully!";
	}else{
		$response['status']=1;
  	$response['msg'] ='Pin does not exists';
	}
}else{
	$response['status']=0;
  $response['message'] = 'Required parameter missing!';
}

echo json_encode($response);
}

/*get Sidebar data*/
public function setPaymentStatus(){
  $payment_id=$this->input->post('payment_id');
   $order_id=$this->input->post('order_id');
   $vendor_id=$this->input->post('vendor_id');
  $payment_status=$this->input->post('payment_status');
  $payment_msg=$this->input->post('payment_msg');
  $balance_due=$this->input->post('balance_due');
  $deposit_id=$this->input->post('deposit_id');
  $balance_due=$this->input->post('balance_due');

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
	 
	 
	 
	 $signature_img = $this->input->post('signature_img');
	 $tip_value = $this->input->post('tip_value');
	 
		
		
	 if(!empty($signature_img) || $signature_img!=NULL || $signature_img!="" || $signature_img!=null){
			$path = '../assets/img/signature/';
			$file = time().'.jpeg';
			$signature_img = $this->base64_to_jpeg($signature_img,$path.$file);
			$signature = $file;
		
		}else{
			$signature = '';
		}

  
  
  
 /* echo "<pre>";print_r($_POST);exit;*/
  //echo "jdjd";die;
  /*$harsh=array(
                  'payment_id'=>$payment_id,
                  'order_id'=>$order_id,
                  'payment_status'=>$payment_status,
                  'payment_msg'=>$payment_msg
  );*/
//echo "<pre>";print_r($_POST);
  if($payment_id !=''){

    /*if($query){*/
      // echo "dd";
    //  $response['your_varriable']=$harsh;
    	
      
    if($payment_status==1){
    	if(!empty($deposit_id) && $deposit_id!=0 && $deposit_amount!=0){
      	 $this->depositUpdate($deposit_id,$deposit_amount,$order_id);
      }
    	$query=$this->db->query('update payment set status_id="2",message="Payment Recived" where payment_id="'.$payment_id.'"');
    $this->db->query('insert into customer_card_detail set order_id="'.$order_id.'",payment_id="'.$payment_id.'",card_holder_name="'.$card_holder_name.'",card_number="'.$card_number.'",exp_date="'.$exp_date.'", terminal="'.$terminal.'", aid="'.$aid.'", tvr="'.$tvr.'", iad="'.$iad.'", tsi="'.$tsi.'", arc="'.$arc.'", card_type="'.$card_type.'" ' );
	
	//$this->db->query("update orders set tip_value='".$tip_value."' where order_id='".$order_id."' ");

     	if($balance_due <=0){
     		//echo "harsh";
    	$response['reciptedata']=$this->order_invoice($order_id,$vendor_id);
    	 $this->OrderUpdate($order_id);	


    	}else{
    		//echo "vishal";
    	$response['reciptedata']=(Object)[];
    	//$response['order_id']=$order_id;
    	}
          $response['status']=1;
          $response['order_id']=$order_id;
        $response['balance_due'] = $balance_due;
       $response['message'] ='Payment successful';
      }
      else if($payment_status==0){
    //  echo "dff";
          $response['status']=0;
       $response['balance_due'] = $balance_due;
       $response['message'] ='Payment Fail';
      }else{
     //   echo "jj";
         $response['status']=0;
       ///$response['reciptedata']=$this->order_invoice($order_id);
       $response['message'] ='Card Response Error';
      }

    /*}else{
     // echo "mmm";
    	$response['balance_due'] = $balance_due;
        $response['status']=0;
      $response['reciptedata']=(Object)[];
       $response['msg'] ='Something Wrong';
      }*/

    
    /*if($query){
      $response['status']=1;
       $response['reciptedata']=$this->order_invoice($order_id);
     $response['msg'] ='Payment Successfull';
    }*/
  }else{
    // $query=$this->db->query('update payment set status_id=3,message="'.$payment_msg.'" where payment_id="'.$payment_id.'"');
  ///echo "sssdddd";
      $response['status']=0;
     $response['message'] ='Payment Fail';
    
  }
  echo json_encode($response);
}
public function deleteCustomerProduct(){
  $cart_id=$this->input->post('cart_id');
  if(!empty($cart_id)){
      $query=$this->db->query('update cart set is_delete=1 where cart_id="'.$cart_id.'"');
      if($query){
        $response['status']=0;
        $response['message'] = 'Product deleted successfully!';
      }else{
        $response['status']=0;
       $response['message'] = 'Something wrong';
      }
  }else{
    $response['status']=0;
  $response['message'] = 'Required parameter missing!';
  }
  echo json_encode($response);
}
public function order_invoice_deposit($id){
	
						 
  $data['customerInfo'] = $this->db->query('select A.event_name,A.deposit_amount,A.event_note,concat(B.firstname," ",B.lastname) as 	customer_name,B.email,B.mobile_phone,B.home_phone from deposit_installment as c inner join deposit_customer as A on A.id=c.deposit_id inner join customer as B on A.customer_id=B.customer_id where c.id="'.$id.'"')->row();
  
 
             return $data;
       //  echo json_encode($data);
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
public function previewRecipte(){
        //echo "<pre>";print_r($_POST);exit;
       // echo "jddjh";die;
        $customer_id = $this->input->post('customer_id');
        $appointment_id = $this->input->post('appointment_id');
        $appointment_type = $this->input->post('appointment_type');
        $token_no = $this->input->post('token_no');
        $vendor_id = $this->input->post('vendor_id');
        $discount=$this->input->post('discount');
        $gift_card_id=$this->input->post('gift_card_id');
        $discount=$this->input->post('discount');
        $addtional_discount=$this->input->post('additional_discount');
         $search = $this->input->post('search');
        if($appointment_id!=0){
            if($appointment_type==3){
                $where = "WHERE aps.is_addon=0 AND a.vendor_id='".$vendor_id."' and a.is_checkout='0' and a.appointment_type='3' and a.token_no='".$token_no."' ";
            }else if($appointment_type==2){
                $where = "WHERE aps.is_addon=0 AND a.vendor_id='".$vendor_id."' and a.is_checkout='0' and a.appointment_type='2' and a.token_no='".$token_no."' ";
            }
            else {
                $where = "WHERE aps.is_addon=0 AND a.vendor_id='".$vendor_id."' and a.is_checkout='0' and a.appointment_type='1' and a.token_no='".$token_no."' ";
            }
        }
          if($customer_id!='' && $appointment_id!=''){
            $result = $this->db->query("select
                            aps.as_id,
                            aps.appointment_id,
                            aps.appointment_time,
                            aps.price,
                            aps.duration,
                            aps.points,
                            aps.service_id,
                            aps.stylist_id,
                            aps.parent_id,
                            a.date as appointment_date,
                            a.customer_id,
                            s.service_name,
                            s.tax_amount,
                            s.tax_rate,
                            s.service_point,
                            aps.service_tip as tip,
                            CONCAT(st.firstname,' ',st.lastname) as stylist_name
                            from appointment_service aps
                            INNER JOIN appointment a
                            ON a.appointment_id=aps.appointment_id
                            INNER JOIN service s
                            ON s.service_id=aps.service_id
                            INNER JOIN stylist st
                            ON st.stylist_id=aps.stylist_id
                            $where
                            ORDER BY aps.as_id DESC
                            
              ")->result();
           // echo $this->db->last_query();exit;
             $selQuery = "select 
                        c.cart_id, 
                        c.quantity, 
                        p.product_id,
                        p.product_name, 
                        p.barcode_id, 
                        p.price_retail,
                        p.tax_amount,
                        p.tax_rate,
                        p.sku,
                        p.quantity as pro_quant
                        from cart c 
                        INNER JOIN product p 
                        ON p.product_id=c.product_id 
                        where p.is_active='1' 
                        and p.is_delete='0' 
                        AND c.customer_id='".$customer_id."' 
                        order by c.cart_id desc ";
         $result2= $this->db->query($selQuery)->result();

        $certificate= $this->db->query('select *  from gift_certificate where customer_id="'.$customer_id.'" and is_active=0')->result();
       $gift_card= $this->db->query('select * from gift_card where customer_id="'.$customer_id.'" and is_active=0')->result();
      $iou_data= $this->db->query('select A.*,B.order_number as order_no from customer_iou_amount as A left join orders as B on A.order_id=B.order_id where A.status=0 and A.customer_id="'.$customer_id.'" order by id desc limit 1')->row();
       /* $customer_packages=$this->db->query('select A.*,B.plan_name,price from customer_packages as A inner join packages_plan as B on A.plan_id=B.plan_id where A.customer_id="'.$customer_id.'" ')->row();
        $customer_membership=$this->db->query('select * from customer_membership where customer_id="'.$customer_id.'" and is_checkout=0')->row();
*/
        if(empty($result2)){
            $result2=array();
         }
         if(empty($certificate)){
            $certificate=array();
         }
         if(empty($gift_card)){
            $gift_card=array();
         }
         if(empty($iou_data)){
            $iou_data=(Object)[];
         }
        /* if(empty($customer_packages)){
            $customer_packages=(Object)[];
         }
         if(empty($customer_membership)){
            $customer_membership=(Object)[];
         }*/
     
    $query_customer = $this->db->query("select DATE_FORMAT(l.created_date,'%d %M %Y') AS customer_since, DATE_FORMAT(c.birthday,'%d %M %Y') as birthday , '' as points_money from login l INNER JOIN customer c ON l.login_id=c.login_id where c.customer_id='".$customer_id."'  ");
    
    $customer_data = $query_customer->row();
     
     
           $response['status'] = 1;
            $response['services'] = $result;
            $response['product'] = $result2;
            $response['certificate'] = $certificate;
            $response['gift_card'] = $gift_card;
            $response['iou_data'] = $iou_data;
            $response['discount']=$this->input->post('discount');
             $response['additonal_discount']=$this->input->post('additional_discount');
          
          //  $response['customer_packages'] = $customer_packages;
          //  $response['customer_membership'] = $customer_membership;
         //   $response['customer_data'] = $customer_data;
            $response['message'] = 'success';
      
        }else{
            $response['status'] =0;
          $response['message'] = 'Required parameter missing!';      
        }
         
            echo json_encode($response);
          
}
function preOrderMail(){
    //echo "<pre>";print_r($_POST);exit;
  $customer_id = $this->input->post('customer_id');
  $appointment_id = $this->input->post('appointment_id'); 
  $token = $this->input->post('token'); 
 /// $payment_type = $this->input->post('payment_type');
if(!empty($customer_id)){
  $appointment_id_new=$appointment_id;
  if($appointment_id!=0 || $appointment_id!='' || $appointment_id!=NULL){
    $appointment_id = explode(',',$appointment_id);
  }else{
    $appointment_id = 0;
  }
  $service_price = $this->input->post('service_total_price');
  $tax_value = $this->input->post('tax');
  $tip_value = $this->input->post('tip_amount');
  $cash_mode_new = $this->input->post('cash_value');
  $cash_mode_credit = $this->input->post('credit_value');
  $cash_mode_gift = $this->input->post('gift_card_value');
  $cash_mode_certificate= $this->input->post('certificate_value');
  $cash_mode_gift1 = $this->input->post('new_gift_card');
  $cash_mode_certificate1= $this->input->post('new_certificate');
  $iou_amount = $this->input->post('iou_value');
  $package_amount=$this->input->post('package_amount');
  $membership_value = $this->input->post('membership_val');
  $discount_new=$_POST['discount_value'];
  $rewards_money=$_POST['reward_value'];
  if($_POST['giftcard_db_value']!='' && $_POST['gift_card_number']){
  $giftcard_db_value=$_POST['giftcard_db_value'];
  $gift_payment=$cash_mode_gift;
  $giftCardValue=$giftcard_db_value - $gift_payment;
}else{
    $giftCardValue='';
  }
  $price_retail = 0;
  $amount = 0;
  $product_price = 0;
  
  $query = $this->db->query("select c.customer_id, c.product_id, c.quantity, p.price_retail, cm.mobile_phone, cm.email from cart c LEFT JOIN product p ON p.product_id=c.product_id LEFT JOIN customer cm ON cm.customer_id=c.customer_id where c.customer_id='".$customer_id."'");
  if($query->num_rows()){
    $result = $query->result();
    foreach($result as $res){
      
      $product_price+=($res->price_retail*$res->quantity);
      
    }
    //echo $amount;
  }
 
$final_amount=$this->input->post('total_amount');
$vendor_id=$_POST['vendor_id'];

$bank_txn_id="";
$response_code="";
$message="Payment Pending";
$query2 = $this->db->query("insert into payment set payment_type='3', status_id='1',payment_status=0,transaction_id='".$transaction_id."', bank_txn_id='".$bank_txn_id."', response_code='".$response_code."', amount='".$final_amount."', message='".$message."', created_date='".date('Y-m-d h:i:s')."', customer_id='".$customer_id."',vendor_id='".$vendor_id."' ");  
  
  $payment_id = $this->db->insert_id();
      
  $order_number = 'ORD' . date('mdYHis');
  //$iou_amount=$_POST['amount_due_val'];
    if($cash_mode_credit==''){
      $cash_mode_credit=0;
    }
    if($iou_amount==''){
      $iou_amount=0;
    }
    if($tip_value==''){
      $tip_value=0;
    }
    if($cash_mode_new==''){
      $cash_mode_new=0;
    }
    if($cash_mode_gift==''){
      $cash_mode_gift=0;
    }
    if($cash_mode_certificate==''){
      $cash_mode_certificate=0;
    }
     

  if($payment_id){
    $membership_value = 0.00;
    $query3 = $this->db->query("insert into orders set vendor_id='".$this->input->post('vendor_id')."', customer_id='".$customer_id."', payment_id='".$payment_id."', status_id='2', order_type='1', order_number='".$order_number."', order_amount='".$final_amount."',tip_amount='".$tip_value."',final_amount='".$final_amount."',discount_amount='".$discount_new."',member_ship_amount='".$membership_value."',iou_amount='".$iou_amount."',credit_card_amount='".$cash_mode_credit."',cash_amount='".$cash_mode_new."',gift_cert_amount='".$cash_mode_certificate."',gift_cart_amount='".$cash_mode_gift."',rewards_money='".$rewards_money."',tax_amount='".$tax_value."',is_active='1', is_delete='0', created_date='".date('Y-m-d h:i:s')."' ");
    
    $order_id = $this->db->insert_id();
    /*iou amoutn*/
   if($_POST['iou_id'] !=''){
      $this->db->query('update customer_iou_amount set status=1 where id="'.$_POST['iou_id'].'"');
    }
    if(($iou_amount !='' || $iou_amount !=0)){
      $this->db->query('insert into customer_iou_amount set customer_id="'.$customer_id.'",order_id="'.$order_id.'",iou_amount="'.$iou_amount.'",status=0');
    }
    /*iou amount*/
    /*if($appointment_id!=''){
      $this->db->query('update stylist_tip_amount set status=1 where appointment_id IN ('.$appointment_id.')');
    }*/
}
if($query3){
      
      $query31 = $this->db->query("select * from cart where customer_id='".$customer_id."' ");
      $cart_data = $query31->result();
      if(!empty($query31)){
        foreach($cart_data as $key=>$cd){
         /// print_r($cd);
          $product_data=$this->db->query('select quantity from product where product_id="'.$cd->product_id.'"')->row();
          $qaunt=$product_data->quantity-$cd->quantity;

          $this->db->query("update product set quantity='".$qaunt."' where product_id='".$cd->product_id."' ");
          
          $query32 = $this->db->query("insert into order_detail set order_id='".$order_id."', product_id='".$cd->product_id."', quantity='".$cd->quantity."',sale_type=2");
         // echo $this->db->last_query();
        }
        // delete item from cart
     //   $this->db->query("delete from cart where customer_id='".$customer_id."'");
        
      }
}
if($service_price>0){
    $sale_type=1;
  }else{
    $sale_type=0;
  }
/*if($_POST['gift_certificate_id'] !=''){
    //  $this->db->query("update gift_certificate set is_active=1 where gift_id IN(".$_POST['gift_certificate_id'].")");
    }
    if($_POST['gift_card_value'] !=''){
      $total_cash=$this->db->query('select amount from gift_card where card_number="'.$_POST['gift_card_number'].'"')->row();
      $left_balance=$total_cash-$_POST['gift_card_value'];
      $this->db->query("update gift_card set amount='".$left_balance."' where card_number='".$_POST['gift_card_number']."'");
      $this->db->query('insert into gift_card_history set card_number="'.$_POST['gift_card_number'].'",amount="'.$_POST['gift_card_value'].'"');
    }
 */     
    /*gift card and gift certificare*/
      if(!empty($_POST['gift_certificate_id'])){
    if($query3){
    
      $ids=explode(",",$_POST['gift_certificate_id']);
      $countId=count(@$ids);
      for($i=0;$i<$countId;$i++){
      $query32 = $this->db->query("insert into order_detail set order_id='".$order_id."', product_id='".$ids[$i]."', quantity='0',sale_type=3");
    }

    $this->db->query('update gift_certificate set is_active=1 where gift_id IN('.$_POST['gift_certificate_id'].')');
  
  } 
  }
  if(!empty($_POST['gift_card_id'])){ 
  if($query3){
    
      $ids=explode(",",$_POST['gift_card_id']);
      $countId=count(@$ids);
      for($i=0;$i<$countId;$i++){
      $query32 = $this->db->query("insert into order_detail set order_id='".$order_id."', product_id='".$ids[$i]."', quantity='0',sale_type=4");
    }
      
  //  $this->db->query('update gift_card set is_active=1 where card_id IN('.$_POST['gift_cart_id'].')');
  
  } 
}
if($appointment_id_new!=0 || $appointment_id_new!=null){
  $stylist_id=$this->db->query('select stylist_id from appointment_service where appointment_id IN ('.$appointment_id_new.')')->result();
  }
if($sale_type==1){
    
      for($i=0;$i<count($appointment_id);$i++){
      if($appointment_id_new!=0){
        $stylist_ids = $stylist_id[$i]->stylist_id;
        }else{
        $stylist_ids = 0;
        }
        //echo "insert into order_detail set order_id='".$order_id."', product_id='".$appointment_id[$i]."',sale_type=1,stylist_id='".$stylist_ids."' ";die;
        $queryy = $this->db->query("insert into order_detail set order_id='".$order_id."', product_id='".$appointment_id[$i]."',sale_type=1,stylist_id='".$stylist_ids."' ");
      }
    }
  /*gift card and gift certificare*/  
    ///$this->db->query("insert into order_history set order_id='".$order_id."', status_id='10', order_update_date='".date('Y-m-d h:i:s')."' ");
      
      // checkout appointment
      
      /*points*/
      /*if($_POST['search']!='getCustManage') {
      $customer_points=$_POST['customer_points'];
        $visit_point=$_POST['visit_point'];
        $spent_points=$_POST['spent_points'];
        $service_point=$_POST['service_point'];
        $rewards_points=$_POST['rewards_points'];
        
      $customer_reward_points=$this->db->query('select customer_points from  customer_reward_points where customer_id="'.$customer_id.'"')->row();
      if(count($customer_reward_points) <=0){
        $pointsUpdate=$this->db->query('insert into customer_reward_points set customer_points="'.$customer_points.'",customer_id="'.$customer_id.'"');
      }else if($totlPointsUse =='' || $totlPointsUse==0){
        $points=$customer_points+$customer_reward_points->customer_points;
          $pointsUpdate=$this->db->query('update customer_reward_points set customer_points="'.$points.'" where customer_id="'.$customer_id.'"');
      }else{
        $points=$customer_reward_points->customer_points-$totlPointsUse;
        $points_new=$points+$customer_points;
          $pointsUpdate=$this->db->query('update customer_reward_points set customer_points="'.$points_new.'" where customer_id="'.$customer_id.'"');
      }
      if($pointsUpdate){
        $data['customerData']=$this->customer->get_by(array('customer_id'=> $customer_id));
        $subject = 'Received Points';
        $data['new_point_recive']=$customer_points;
        $data['wallet_points']=$points;
        $data['wallet_Money']=getRewardMoney($points);
          ///$htmlData1=$this->load->view('getPointsTemplates',$data,true);
          $htmlData1="Congratulation you got".$customer_points;
          $headers = "From: info@booknpay.com\r\n";
          $headers .= "Reply-To:  info@booknpay.com\r\n";
          $headers .= "MIME-Version: 1.0\r\n";
          $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

              $message_email = $htmlData1;
          
          $mail=mail($data['customerData']->email, $subject, $message_email, $headers);
      }
    }*/
      /*points*/
    /*  $response['status'] = 1;
      $response['order_number'] = $order_number;
      $response['message'] = "Order Successfull.";*/
      /*$title = "New Order!";
      $title = "Sub Title";
      $message = "Dear Vendor, Please check new order.";*/
      /*Gift Certificate Email*/
      ///$this->load->model('gift/gift_model', 'gift');
     
     
      if($order_id !=''){
          $this->mailRecipte($order_id);
        //   $response['reciptedata']=$this->order_invoice($order_id);
            $response['status'] = 1;
            //$response['order_number'] = $order_number;
            $response['message'] = "Invoice sent successfully!";
        
      }else{
            $response['status'] = 0;
            $response['order_number'] = 0;
            $response['message'] = "Order not created,Something went wrong";
      }
    }else{
       $response['status'] = 0;
          //  $response['order_number'] = 0;
            $response['message'] = "Required parameter missing";
    }
      /*if($order_id !=''){
         // $this->mailRecipte($order_id,$vendor_id,$send_invoice);
            $response['status'] = 1;
            $response['order_number'] = $order_number;
            $response['message'] = "Order Successfull.";
      }else{
         $response['status'] = 0;
            $response['order_number'] = 0;
            $response['message'] = "Something wrong";
      }*/
      echo json_encode($response);


}
function mailRecipte($order_id = ''){
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $path_customer = $actual_link.'/salon/assets/img/customer/thumb';
    $path_stylist = $actual_link.'/salon/assets/img/stylist/thumb';
    $path_product=$actual_link.'/salon/assets/img/product/thumb';
   $data['editData']=$this->db->query("SELECT
                          
                          IF(CONCAT(c.firstname,' ',c.lastname) IS NULL,'N/A',CONCAT(c.firstname,' ',c.lastname)) customer_name,c.email,
                          COUNT(od.product_id) total_product,
                         IF(p.payment_type='1','Cash',IF(p.payment_type='2','Debit Card',IF(p.payment_type='3','Credit Card',IF(p.payment_type='4','Net Banking',IF(p.payment_type='5','EBS Payments','N.A'))))) payment_type,
                         IF(p.status_id='1','Pending',IF(p.status_id='2','Success',IF(p.status_id='3','Reject','N.A'))) payment_status,
                          p.transaction_id,
                          p.response_code,
                          p.amount payment_amount,
                          p.message payment_message,
                          date_format(p.created_date,'%d %M %Y') as payment_date,
                          o.status_id order_status,
                          o.order_type,
                          o.gift_cert_amount,
                          o.gift_cart_amount,
                          o.gift_cart_amount,
                          o.order_number,
                          o.order_amount,
                          o.customer_id,
                          dc.coupon_id,
                          o.discount_amount,
                          o.tax_amount,
                          IF(o.is_active = 1, 'Active', 'Inactive') AS is_active,
                          o.created_date
                      
                        FROM orders AS o
                         LEFT JOIN order_detail od
                            ON od.order_id = o.order_id
                        LEFT JOIN login l
                            ON l.login_id = o.customer_id
                          LEFT JOIN customer c
                            ON c.customer_id = o.customer_id
                          LEFT JOIN payment p
                            ON p.payment_id = o.payment_id
                          LEFT JOIN coupon dc
                            ON dc.coupon_id = o.coupon_id
                        WHERE o.is_delete = 0 AND p.payment_id IS NOT NULL AND o.order_id='".$order_id."' ")->row();
   /*
        if($appointment_type==3){
          $appointmentData=$this->db->query('select  C.email as cust_email from appointment as a inner join customer as C on C.customer_id=a.group_leader_id where a.token_no="'.$token.'" and a.group_leader_id !=0')->row();
         $email= $appointmentData->cust_email;
        }else{*/
           $email=$data['editData']->email;
         //  echo $email;exit;
        /*}*/
    /*$appointmentData=$this->db->query('select (case when a.appointment_type=3 THEN(select C.email from customer as C where  a.group_leader_id=C.customer_id and a.group_leader_id and C.email IS NOT NULL) else (select C.email from customer as C where a.customer_id=C.customer_id ) END) as cust_email from appointment as a where a.token_no="'.$token.'" and a.group_leader_id !=0')->row();*/
      
        $data['appointmentData']=$this->db->query("select
                                    a.appointment_id,
                                    date_format(a.date,'%d %M %Y') as app_date,
                                    time_format(aps.appointment_time,'%h:%i %p') as apt_time,
                                    aps.service_id, aps.stylist_id,
                                    s.service_name,
                                    CONCAT(st.firstname,' ',st.lastname) as stylist_name,
                                    CONCAT('$path_stylist','/',st.photo) as stylist_image,
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
        if (!empty($data['editData']->payment_id)) {
           $selQuery = "SELECT * FROM payment WHERE payment_id = '".$data['editData']->payment_id."'";
            $data['paymentInfo'] = $this->db->query($selQuery)->row();
        }
      //  $customerLoginInfo=$this->db->query('select ')
        if (!empty($data['editData']->coupon_id)) {
            $data['couponInfo'] = $this->coupon->get_by(array("coupon_id" => $data['editData']->coupon_id));
            $data['couponInfo']=$this->db->query("SELECT
                      c.coupon_id,
                      IF(c.discount_for=1,'Product','Service') as discount_for,
                      IF(c.coupon_type=1,'Flat Discount','Percentage Discount') as coupon_type,
                      c.discount,
                      c.start_date,
                      c.end_date,
                      c.min_amount,
                      IF(c.is_active=1,'Active','Inactive') as is_active
                    FROM coupon AS c
                    WHERE c.is_delete = 0
          AND c.vendor_id='".$vendor_id."' GROUP BY c.coupon_id")->row();
        }
         $data['productInfo']=$this->db->query("SELECT 
                      p.product_id,
                      p.product_name as name,
                      CONCAT('$path_product','/',p.main_image) as product_image,
                      c.category_name,
                      p.price_retail as price,
                      o.quantity as quant,
                      p.description,
                      p.main_image
                    FROM product p
                    LEFT JOIN category c 
                      ON c.category_id = p.category_id
                      INNER join order_detail as o on o.product_id=p.product_id
                    WHERE o.order_id = '".$order_id."' and o.sale_type=2")->result();
       
      $data['gift_certificate']=$this->db->query("SELECT 
                      cert.gift_certificate_no,
                      cert.amount,
                      date_format(cert.expire_on,'%d %M %Y') as expire_on,
                      s.service_name as service_name,
                      concat(c.firstname,'',c.lastname) as cust_name
                    FROM  gift_certificate as cert 
                    inner join customer as  c 
                      ON c.customer_id = cert.customer_id
                      INNER JOIN service as s on s.service_id=cert.service_id
                      INNER join order_detail as o on o.product_id=cert.gift_id
                    WHERE o.order_id = '".$order_id."' and o.sale_type=3")->result();
       $data['gift_card']=$this->db->query("SELECT 
                      cert.card_number,
                      cert.intial_amount,
                      cert.buyer_name,
                      cert.buyer_email,
                      date_format(cert.issue_date,'%d %M %Y') as  issue_date,
                      
                      concat(c.firstname,'',c.lastname) as cust_name
                    FROM  gift_card as cert 
                    inner join customer as  c 
                      ON c.customer_id = cert.customer_id
                      INNER join order_detail as o on o.product_id=cert.card_id
                    WHERE o.order_id = '".$order_id."' and o.sale_type=4")->result();
       //echo json_encode($data);
       if($order_id !=''){
          $subject = 'Pre Order Mail';
          $htmlData=$this->load->view('email_template/order',$data,true);
          $headers = "From: info@booknpay.com\r\n";
          $headers .= "Reply-To:  info@booknpay.com\r\n";
          $headers .= "MIME-Version: 1.0\r\n";
          $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

              $message_email = $htmlData;
              
          $mail=mail($email, $subject, $message_email, $headers);
          if($mail){
           $data['status']=1;
           $data['message']="Invoice sent successfully!";
          }else{
            $data['status']=0;
            $data['message']="";
          }
        }
		
		 
         echo json_encode($data);
      
 }



public function getRefundData_old(){
	$order_id=$this->input->post('order_id');
	if(!empty($order_id)) {
		$data['order_data']=$this->db->query('select order_id,customer_id,order_amount as total_amount,(select sum(amount) from payment where order_id="'.$order_id.'" and payment_type=1 limit 1) as cash_amount,(select sum(amount) from payment where order_id="'.$order_id.'" and payment_type=2 limit 1) as card_amount,gift_cart_amount from orders where order_id="'.$order_id.'"')->row();
		$data['customerInfo'] = $this->db->query('select concat(firstname," ",lastname) as 	customer_name,email,mobile_phone,home_phone from customer where customer_id IN ('. $data['order_data']->customer_id.')')->row();
  		$data['customer_card_detail']=$this->db->query('select * from customer_card_detail where order_id="'.$order_id.'"')->result();
  		$data['gift_certificate']=$this->db->query('SELECT 
                      gift.gift_certificate_no as gift_certificate_no
                       FROM gift_certificate as gift
                  
                      INNER join order_detail as o on o.product_id=gift.gift_id
                    WHERE o.order_id = "'.$order_id.'" and o.sale_type=3')->result();
  

	}	
	else{
           	$data['message']="Required Perameter Missing";
           	$data['status']=0;
           }
         echo json_encode($data);
}
public function getRefundData(){
$order_id=$this->input->post('order_id');
	/*p.payment_id,p.transaction_id,IF(p.payment_type="1","Cash",IF(p.payment_type="2","Debit Card",IF(p.payment_type="3","Credit Card",IF(p.payment_type="4","Net Banking",IF(p.payment_type="5","EBS Payments","N.A"))))) payment_type,
                         IF(p.status_id="1","Pending",IF(p.status_id="2","Success",IF(p.status_id="3","Reject","N.A"))) payment_status,p.amount        inner join payment as p on o.order_id=p.payment_id*/
	//echo $order_id;exit;
    if(!empty($order_id)) {               
  $data['order_data'] =$this->db->query('select o.order_id,o.customer_id,o.coupon_id,o.order_number,o.order_amount,o.tax_amount,o.tip_amount,o.cash_amount,o.credit_card_amount,o.iou_amount,o.final_amount,o.vendor_id,o.rewards_money,o.diposite_amount,o.discount_amount,o.gift_cert_amount as certificate_amount,o.gift_cart_amount as gift_card_amount,o.cuppon_value,o.total_refund_amount,is_refund from orders as o  where o.order_id="'.$order_id.'"')->row();
 
 $data['payment_data']=$this->db->query('select p.payment_id,p.transaction_id,IF(p.payment_type="1","Cash",IF(p.payment_type="2","Card",IF(p.payment_type="3","Credit Card",IF(p.payment_type="4","Net Banking",IF(p.payment_type="5","EBS Payments","N.A"))))) payment_type,
                         IF(p.status_id="1","Pending",IF(p.status_id="2","Success",IF(p.status_id="3","Reject","N.A"))) payment_status,p.amount from payment as p where p.order_id="'.$order_id.'"')->result();
  //echo "<pre>";print_r($data['order_data']);exit;
  $data['customerInfo'] = $this->db->query('select concat(firstname," ",lastname) as 	customer_name,email,mobile_phone,home_phone from customer where customer_id IN ('. $data['order_data']->customer_id.')')->result();
  /*if (!empty($data['editData']->payment_id)) {
           // $data['paymentInfo'] = $this->orders->get_payment_details($data['editData']->payment_id);
             $data['paymentInfo']=$this->db->query("SELECT payment_id,transaction_id,IF(payment_type='1','Cash',IF(payment_type='2','Debit Card',IF(payment_type='3','Credit Card',IF(payment_type='4','Net Banking',IF(payment_type='5','EBS Payments','N.A'))))) payment_type,
                         IF(status_id='1','Pending',IF(status_id='2','Success',IF(status_id='3','Reject','N.A'))) payment_status,amount FROM payment WHERE payment_id = '".$data['editData']->payment_id."'")->row();
        }*/
        //aps.price,
  $data['customer_card_detail']=$this->db->query('select * from customer_card_detail where order_id="'.$order_id.'"')->result();
  $data['appointmentData']=$this->db->query("select
                                    a.appointment_id,
                                    a.date as appointment_date,
                                    aps.service_id, aps.stylist_id,
                                    aps.appointment_time,
                                    aps.appointment_end_time,
                                    s.service_name,
                                    CONCAT(st.firstname,' ',st.lastname) as stylist_name,
                                    
                                    aps.duration,
                                    o.orderdetail_id,
                                    o.discount_amount,
                                    o.tax_amount,
                                    o.actual_amount,
                                    o.total_paid_amount,
                                    o.refund_amount
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
                      /*p.price_retail as price,
                      o.quantity as quant,*/
                      p.description,
                      p.main_image,
                       o.orderdetail_id,
                      o.quantity,
                      o.discount_amount,
                                    o.tax_amount,
                                    o.actual_amount,
                                    o.total_paid_amount,
                                     o.refund_amount
                                    
                    FROM product p
                    LEFT JOIN category c 
                      ON c.category_id = p.category_id
                      INNER join order_detail as o on o.product_id=p.product_id
                    WHERE o.order_id = "'.$order_id.'" and o.sale_type=2')->result();
  $data['gift_certificate']=$this->db->query('SELECT 
                      gift.gift_certificate_no as gift_certificate_no,
                    /* // gift.amount as amount,*/

                                    o.actual_amount,
                                    o.total_paid_amount,
                                     o.orderdetail_id,
                                     (case when o.refund_status=1 then "refund" else "No Refund" end) as refund_status
                       FROM gift_certificate as gift
                  
                      INNER join order_detail as o on o.product_id=gift.gift_id
                    WHERE o.order_id = "'.$order_id.'" and o.sale_type=3')->result();
  $data['gift_card']=$this->db->query('SELECT 
                      gift.card_number as gift_card_no,
                      /*gift.intial_amount as amount*/

                                    o.actual_amount,
                                    o.total_paid_amount,
                                     o.orderdetail_id,
                                     (case when o.refund_status=1 then "refund" else "No Refund" end) as refund_status
                       FROM gift_card as gift
                  
                      INNER join order_detail as o on o.product_id=gift.card_id
                    WHERE o.order_id = "'.$order_id.'" and o.sale_type=4')->result();
  	$data['message']="get successfully";
           	$data['status']=1;

           }else{
           	$data['message']="Required Perameter Missing";
           	$data['status']=0;
           }
         echo json_encode($data);
}
public function refundAmount(){
$order_id=$this->input->post('order_id');
$orderDetailId=explode(",",$this->input->post('orderDetailId'));
$refundAmount=explode(",",$this->input->post('refundAmount'));
$total_refund=$this->input->post('total_refund');
$refund_status=$this->input->post('refund_status');
$gift_certificate_no=$this->input->post('gift_certificate_no');
if(!empty($order_id)){
if($refund_status==1){
  $this->db->query('update orders set total_refund_amount="'.$total_refund.'",is_refund=1 where order_id="'.$order_id.'"' );
  $this->db->query('update gift_certificate set is_active=0 where gift_certificate_no IN ('.$gift_certificate_no.')');
  foreach ($orderDetailId as $key => $value) {
    $this->db->query('update order_detail set refund_amount="'.$refundAmount[$key].'",refund_status=1 where orderdetail_id="'.$value.'"');
  }

  $response['status']=1;
   $response['message']="Refunded succcessfully!";
}else{
  $response['status']=0;
   $response['message']="Something went wrong";
}
}else{
  $response['status']=0;
   $response['message']="Required parameter missing";
}
 echo json_encode($response);
}
function addRefundGiftcard(){
    $order_id=$this->input->post('order_id');
    $orderdetail_id=$this->input->post('orderdetail_id');
    $last_gift_card_number = $this->input->post('last_gift_card_number');
    $gift_card_number = $this->input->post('gift_card_number');
    $card_issue_date = date('Y-m-d',strtotime($_POST['card_issue_date']));//$this->input->post('card_issue_date');
    $card_buyer_name = $this->input->post('card_buyer_name');
    $card_buyer_email = $this->input->post('card_buyer_email');
    $card_phone = $this->input->post('card_phone');
    $card_message = $this->input->post('card_message');
    $customer_id = $this->input->post('card_customer_id');
    $card_amount = $this->input->post('card_amount');
        $notifyBy_card=$this->input->post('notifyBy_card');
        $template_image_id=$this->input->post('template_image_id');
        $template_id=$this->input->post('template_id');
        $vendor_id=$this->input->post('vendor_id');

        if(!empty($gift_card_number) && !empty($card_buyer_name)){
          $insert_id = $this->db->query("insert into gift_card set customer_id='".$customer_id."',card_number='".$gift_card_number."', issue_date='".$card_issue_date."', buyer_name='".$card_buyer_name."', buyer_email='".$card_buyer_email."', phone='".$card_phone."', message='".$card_message."',template_id='".$template_id."',template_image_id='".$template_image_id."',notifyby='".$notifyBy_card."',created_date='".date('Y-m-d H:i:s')."', vendor_id='".$this->input->post('vendor_id')."',intial_amount='".$card_amount."',amount='".$card_amount."',is_active=1 ");
          $this->db->query('update gift_card set is_active=0 where card_number="'.$last_gift_card_number.'"');
           $this->db->query('update order_detail set refund_status=1 where orderdetail_id="'.$orderdetail_id.'"');
           $this->db->query("insert into order_detail set order_id='".$order_id."', product_id='".$insert_id."',actual_amount='".$card_amount."',total_paid_amount='".$card_amount."', quantity='0',sale_type=4");
          //$insert_id=$this->db->insert_id();
        if ($insert_id) {
    
        /*$qry = $this->db->query("select template_name from template where template_id='".$template_id."' ");
        $template = $qry->row();
        $template_name = $template->template_name;
        $to = $recipient_email;
      
        $subject = 'Gift Certificate';
        
        $headers = "From: info@booknpay.com\r\n";
        $headers .= "Reply-To:  info@booknpay.com\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        $message_email = "Dear $recipient_name <br/>You have received a Gift Certificate.<br/><b>Gift for: </b>$template_name<br/><b>Sender Name:</b> $sender_name<br/><b>Certificate No.</b> $gift_certificate_number<br/><b>Issued On:</b> $expire_on<br><b>Amount:</b> $amount<br/><b>Message:</b> $message<br/><br/>Thank You<br/>Team BookNPay";
        
        mail($to, $subject, $message_email, $headers);*/
        
            //set_flash('gift_info', "Service added successfully.", 1);
          $response['message'] = 'Gift card added succcessfully!'; 
               $response['status']=1;
      
        } else {
            $response['message'] = 'Somethinng wrong'; 
               $response['status']=0;
        }
       
}
else{
 $response['message'] = 'Required parameter missing!'; 
          $response['status']=0;
}
  echo json_encode($response);
}
function addRefundCertificate()
    {
    //echo "<pre>";print_r($_POST);exit;
      $order_id=$this->input->post('order_id');
      $orderdetail_id=$this->input->post('orderdetail_id');
      $last_gift_certificate_number = $this->input->post("last_gift_certificate_number");
        $gift_certificate_number = $this->input->post("gift_certificate_number");
        $service_id = $this->input->post("service_id");
        $gift_type = $this->input->post("gift_type");
       // $customer_id=$this->input->post("customer_id");
    if($gift_type==1){
      $amount = 0;
      $expire_on = date('Y-m-d',strtotime($_POST['expire_date']));//$this->input->post("expire_date");
    }else{
      $amount = $this->input->post("gift_amount");
      $expire_on = '';
    }
        $notifyBy=explode(",",$_POST['notifyBy']);

        
        $select_recipient_name = $this->input->post("select_recipient_name");
    if($select_recipient_name==0){
      $recipient_name = $this->input->post("gift_recipient_name");
      $recipient_email = $this->input->post("gift_recipient_email");
    }else{
      $exp = explode('-',$select_recipient_name);
      $name = $exp[1];
      $email = $exp[2];
      $recipient_name = $name;
      $recipient_email = $email;
    }
        //echo $recipient_email;
        $message = $this->input->post("messasge");
        $template_id = $this->input->post("template_id");
         $vendor_id = $this->input->post("vendor_id");
        $login_id = $this->input->post("login_id");
        if(isset($_POST['gift_recipient_phone'])){
    $gift_recipient_phone=$this->input->post("gift_recipient_phone");
        }else{
          $gift_recipient_phone=0;  
        }
    $customer_id = $this->input->post("customer_id");
        $template_image_id = $this->input->post("template_image_id");
  if(!empty($gift_certificate_number) && !empty($select_recipient_name)){
    $insert_array = array(
            
            "gift_certificate_no" => trim($gift_certificate_number),
            "service_id" => trim($service_id),
           // "service_type" => trim($gift_type),
            "amount" => trim($amount),
            "expire_on" => trim($expire_on),
            "customer_id" => trim($customer_id),
            "recipient_name" => trim($recipient_name),
            "recipient_email" => trim($recipient_email),
             "recipient_phone" => trim($gift_recipient_phone),
            "message" => trim($message),
            "template_id" => trim($template_id),
            "template_image_id"=>$template_image_id,
      "is_active"=>1,
      "is_delete"=>0,
            "notifyBy"=>$_POST['notifyBy'],
      "vendor_id"=>$vendor_id,
      "created_by" => $login_id,
      "created_date" => date("Y-m-d H:i:s")
            
        );
       $this->db->insert('gift_certificate',$insert_array);
       //echo $this->db->last_query();exit;
       $insert_id=$this->db->insert_id();

       /* $this->user_activity->log_activity($this->session->userdata('user_name') . " added gift certificate with insertid \"" . $insert_id . "\" on " . date('d-m-Y h:i A'));*/

        if ($insert_id) {
        $query32 = $this->db->query("insert into order_detail set order_id='".$order_id."', product_id='".$insert_id."',actual_amount='".$amount."',total_paid_amount='".$amount."', quantity='0',sale_type=3");
        $this->db->query('update order_detail set refund_status=1 where orderdetail_id="'.$orderdetail_id.'"');
        $this->db->query('update gift_certificate set is_active=0 where gift_certificate_no="'.$last_gift_certificate_number.'"');
        $qry = $this->db->query("select * from gift_certificate where gift_id='".$insert_id."' ");
        $certificate = $qry->row();
        /*$template_name = $template->template_name;
        $to = $recipient_email;
      
        $subject = 'Gift Certificate';
        
        $headers = "From: info@booknpay.com\r\n";
        $headers .= "Reply-To:  info@booknpay.com\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        $message_email = "Dear $recipient_name <br/>You have received a Gift Certificate.<br/><b>Gift for: </b>$template_name<br/><b>Sender Name:</b> $sender_name<br/><b>Certificate No.</b> $gift_certificate_number<br/><b>Issued On:</b> $expire_on<br><b>Amount:</b> $amount<br/><b>Message:</b> $message<br/><br/>Thank You<br/>Team BookNPay";
        
        mail($to, $subject, $message_email, $headers);*/
        
            //set_flash('gift_info', "Service added successfully.", 1);
          $response['status'] =1;
          $response['message'] = 'Certificate added succcessfully!'; 
          //$response['products']=$certificate;
        } else {
           $response['status'] =0;
          $response['message'] = 'Something Wrong'; 
          //$response['products']=$certificate;
           // echo "Gift Certificate not generated successfully!";
        }
      }else{
         $response['status'] =0;
          $response['message'] = 'Required parameter missing!'; 
          $response['products']=$certificate;
      }

        echo json_encode($response);
}
public function getRefundList(){
$vendor_id=$this->input->post('vendor_id');
if(!empty($vendor_id)){
  $query=$this->db->query('select order_id,order_number,order_amount from orders where vendor_id="'.$vendor_id.'" and refund_request=1')->result();
  if(!empty($query)){
    $response['status'] =1;
    $response['data'] = $query;
    $response['message']='List available';
  }else{
    $response['status'] =0;
    $response['message'] = 'No refund list available'; 
  }
}else{
   $response['status'] =0;
   $response['message'] = 'Required parameter missing!'; 
}
echo json_encode($response);
}
public function OrderUpdate($order_id){
    $appointment_id=$this->db->query('select product_id from order_detail where order_id="'.$order_id.'" and sale_type=1')->result();
    //echo "<pre>";print_r($appointment_id);exit;
    $cart_id=$this->db->query('select product_id, orderdetail_id from order_detail where order_id="'.$order_id.'" and sale_type=2')->result();
    //echo "<pre>";print_r($cart_id);
    $gift_certificate_id=$this->db->query('select product_id from order_detail where order_id="'.$order_id.'" and sale_type=3')->result();
    $gift_cart_id=$this->db->query('select product_id from order_detail where order_id="'.$order_id.'" and sale_type=4')->result();
	
	$customer_id = $this->db->query("select customer_id from orders where order_id='".$order_id."' ")->row()->customer_id;
	
	$delete_cart_items = $this->db->query("delete from cart where customer_id='".$customer_id."' ");
	
	
    if(!empty($appointment_id)){
        for($i=0;$i<count($appointment_id);$i++){
        	//echo "update appointment set status='7', color_code='8', checkout_time='".date('Y-m-d h:i:s')."', is_checkout='1' where appointment_id='".$appointment_id[$i]->product_id."' ";die;
			
			$vendor_id = $this->db->query("select vendor_id from appointment where appointment_id='".$appointment_id[$i]->product_id."' ")->row()->vendor_id;
			
			$color_id = $this->getColorIdByColorType('checkout',$vendor_id);
			
                     $this->db->query("update appointment set status='7', color_code='".$color_id."', checkout_time='".date('Y-m-d h:i:s')."', is_checkout='1' where appointment_id='".$appointment_id[$i]->product_id."' ");
                 }
    }
    if(!empty($cart_id)){
        for($j=0;$j<count($cart_id);$j++){
        		//echo "<pre>";print_r($cart_id);
        	//echo 'select product_id,quantity from cart where cart_id="'.$cart_id[$j]->product_id.'"';
        			$getProductId=$this->db->query('select product_id,quantity from product where product_id="'.$cart_id[$j]->product_id.'"')->row();
        			//echo "<pre>";print_r($getProductId);
					
					//echo 'select product_id,quantity from order_detail where product_id="'.$getProductId->product_id.'"  ';
					$getProductQuant=$this->db->query('select product_id,quantity from order_detail where orderdetail_id="'.$cart_id[$j]->orderdetail_id.'" ')->row();
					//echo $getProductId->quantity.'#'.$getProductQuant->quantity;
					$final_quant = $getProductId->quantity-$getProductQuant->quantity;
					//echo "update product set quantity='".$final_quant."' where product_id='".$getProductId->product_id."' ";
					 $this->db->query("update product set quantity='".$final_quant."' where product_id='".$getProductId->product_id."' ");
		        
					
					
					//print_r($getProductQuantity);
        			//$this->db->query('update product set quantity="'.$finalquantity.'" where product_id="'.$getProductId->product_id.'"');
                    // $this->db->query("delete from cart where cart_id ='".$cart_id[$j]->product_id."'");
           }
            

    }
    if(!empty($gift_certificate_id)){
        for($i=0;$i<count($gift_certificate_id);$i++){
                     $this->db->query("update gift_certificate set is_active=1 where gift_id='".$gift_certificate_id[$i]->product_id."'");
            }
    }
    if(!empty($gift_cart_id)){
    	
        for($i=0;$i<count($gift_cart_id);$i++){
        	$this->sendGiftCardMail($gift_cart_id[$i]->product_id);
                     $this->db->query("update gift_card set is_active=1 where card_id='".$gift_cart_id[$i]->product_id."'");
            }
    }
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
	public function sendGiftCardMail($giftId){
		//$giftCardId
		//$giftId='95';
		
		$getData=$this->db->query('select A.*,B.image as gift_image from gift_card as A inner join giftcard_images as B on A.template_image_id=B.gc_image_id where A.card_id="'.$giftId.'"')->row();
		//echo "<pre>";print_r($getData);exit;
			$data['card_amount'] = $getData->amount;
			$data['gc_number'] = $getData->card_number;
			$data['message'] = $getData->message;
			//$data['recipient_name'] =$getData->recipient_name;
			$data['image']=$getData->gift_image;
			if(empty($getData->recipient_name)){
				$data['recipient_name'] =$getData->buyer_name;
			}else{
				$data['recipient_name'] =$getData->recipient_name;
			}
			if(empty($getData->recipient_email)){
				$to_email=$getData->buyer_email;
			}else{
				$to_email=$getData->recipient_email;
			}
			
			/*$code = $getData->card_number;*/
			$code = $getData->card_number;
		//generate barcode
		$imageResource = Zend_Barcode::factory('code128', 'image', array('text'=>$code), array())->draw();
		imagepng($imageResource, 'assets/barcodes/'.$code.'.png');

		$data['barcode'] = 'assets/barcodes/'.$code.'.png';

		$data['url']=IMG_URL.$data['barcode'];
		//echo $url;die;
				$receiver_email = $to_email;
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
                   $this->load->library('email');
                   $this->email->initialize($config);
                   $this->email->from('info@booknpay.com', 'BookNPay');
                   $this->email->to($receiver_email);
                   $this->email->subject('E-Gift Card');
                   
                   $emailTemplate = $this->load->view('giftcard_email_template',$data,TRUE);
                   $this->email->message($emailTemplate);
                   $this->email->send();  
		  
	}
	public function payment_pending(){
		$token_no=$this->input->post('token_no');
		$status_id=$this->input->post('status_id');
		if(!empty($token_no) && !empty($status_id)){
			$query=$this->db->query('update appointment set color_code="'.$status_id.'" where token_no="'.$token_no.'"');
			if(!empty($query)){
		    $response['status'] =1;
		    $response['message']='Payment is on pending';
		  	}else{
		    $response['status'] =0;
		    $response['message'] = 'Something went wrong'; 
		 	 }
		}else{
		   $response['status'] =0;
		   $response['message'] = 'Required parameter missing!'; 
		}
	echo json_encode($response);
}
public function setServiceCharge($order_id,$payment_id,$service_charge){
	$getServiceCharge=$this->db->query('select total_service_charge from orders where order_id="'.$order_id.'"')->row();
	if(!empty($getServiceCharge)){
		$service_charge_new=$getServiceCharge->total_service_charge;

	}else{
	$service_charge_new=0;
	}
	$final_service_charge=$service_charge_new + $service_charge;
	$this->db->query('update orders set total_service_charge="'.$final_service_charge.'" where order_id="'.$order_id.'"');
	$this->db->query('update payment set service_charge="'.$service_charge.'" where payment_id="'.$payment_id.'"');
	}

	function depositUpdate($deposit_id,$deposit_amount,$order_id){
		$depositData=$this->db->query('select balance_used from deposit_customer where id="'.$deposit_id.'"')->row();
		$newBalance=$depositData->balance_used + $deposit_amount;
		$this->db->query('update deposit_customer set balance_used="'.$newBalance.'" where id="'.$deposit_id.'"');
		$this->db->query('insert into order_deposit set order_id="'.$order_id.'",deposit_id="'.$deposit_id.'",amount="'.$deposit_amount.'",type="used"');

	}
	function getReturnDepositId(){
		$deposit_id=$this->input->post('deposit_id');
		$deposit_amount=$this->input->post('amount');
		$customer_id=$this->input->post('customer_id');
		$transaction_id = 'TXNQ' . date('mdYHis');
		if(!empty($deposit_id)){
			$response['status']=1;
			$response['deposit_id']=$deposit_id;
			$response['deposit_amount']=$deposit_amount;
			$response['transaction_id']=$transaction_id;
			$cardDetail=$this->db->query('select * from customer_card where customer_id="'.$customer_id.'" and is_default=1')->row();
			if(!empty($cardDetail)){
				$response['cardDetail']=$cardDetail;
			}else{
				$response['cardDetail']=(Object)[];
			}
			$response['message'] = 'Data found'; 
		}else{
		   $response['status'] =0;
		   $response['message'] = 'Required parameter missing!'; 
		}
		echo json_encode($response);

	}
	function Returndeposit(){
		$deposit_id=$this->input->post('deposit_id');
		$deposit_amount=$this->input->post('amount');
		if(!empty($deposit_id)){
			$depositData=$this->db->query('select balance_used from deposit_customer where id="'.$deposit_id.'"')->row();
			$newBalance=$depositData->balance_used + $deposit_amount;
			$query=$this->db->query('update deposit_customer set balance_used="'.$newBalance.'" where id="'.$deposit_id.'"');
			$this->db->query('insert into order_deposit set order_id="'.$order_id.'",deposit_id="'.$deposit_id.'",amount="'.$deposit_amount.'",type="return"');
			if($query){
				   $response['status'] =1;
		   	 $response['message']='Deposit refunded succcessfully';
			}else{
				  $response['status'] =0;
		   		 $response['message']='Something went wrong';
			}
	}else{
		   $response['status'] =0;
		   $response['message'] = 'Required parameter missing!'; 
	}
	echo json_encode($response);
	}

	public function getCardDetail(){
		$customer_id=$this->input->post('customer_id');
		if(!empty($customer_id)){
			 $cardDetail = $this->db->query('select card_number,expiry_month,expiry_year,cvv from customer_card where customer_id="'.$customer_id.'" and is_default=1 ')->row();
			 if(!empty($cardDetail)){
			 	$cardData=$cardDetail;
			 	 $response['status'] =1;
		 		  $response['message'] = 'Card found'; 
		 		  $response['cardData'] = $cardData; 
			 }else{
			 	//$cardData=(object)[];
			 	$response['status'] =0;
		 		  $response['message'] = 'No customer card available'; 
		 		  $response['cardData'] = (object)[];; 
			 }
			
		}else{
		   $response['status'] =0;
		   $response['message'] = 'Required parameter missing!'; 
	}
	echo json_encode($response);
	}
}
?>