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


class Misc extends CI_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
		
		//error_reporting(0);
    }
/*	public function BatchClose(){
  	$machine_type=$this->input->post('machine_type');
  	$login_id=$this->input->post('login_id');
  	$xml_string=$this->input->post('xml_string');
  	$xml_string='<body><CreditSaleCount>1</CreditSaleCount><CreditSaleAmount>1</CreditSaleAmount><CreditForcedCount>0</CreditForcedCount><CreditForcedAmount>0</CreditForcedAmount><CreditReturnCount>0</CreditReturnCount><CreditReturnAmount>0</CreditReturnAmount><CreditAuthCount>0</CreditAuthCount><CreditAuthAmount>0</CreditAuthAmount><CreditPostAuthCount>0</CreditPostAuthCount><CreditPostAuthAmount>0</CreditPostAuthAmount><DebitSaleCount>0</DebitSaleCount><DebitSaleAmount>0</DebitSaleAmount><DebitReturnCount>0</DebitReturnCount><DebitReturnAmount>0</DebitReturnAmount><EBTSaleCount>0</EBTSaleCount><EBTSaleAmount>0</EBTSaleAmount><EBTReturnCount>0</EBTReturnCount><EBTReturnAmount>0</EBTReturnAmount><EBTWithdrawalCount>0</EBTWithdrawalCount><EBTWithdrawalAmount>0</EBTWithdrawalAmount><GiftSaleCount>0</GiftSaleCount><GiftSaleAmount>0</GiftSaleAmount><GiftAuthCount>0</GiftAuthCount><GiftAuthAmount>0</GiftAuthAmount><GiftPostAuthCount>0</GiftPostAuthCount><GiftPostAuthAmount>0</GiftPostAuthAmount><GiftActivateCount>0</GiftActivateCount><GiftActivateAmount>0</GiftActivateAmount><GiftIssueCount>0</GiftIssueCount><GiftIssueAmount>0</GiftIssueAmount><GiftReloadCount>0</GiftReloadCount><GiftReloadAmount>0</GiftReloadAmount><GiftReturnCount>0</GiftReturnCount><GiftReturnAmount>0</GiftReturnAmount><GiftForcedCount>0</GiftForcedCount><GiftForcedAmount>0</GiftForcedAmount><GiftCashoutCount>0</GiftCashoutCount><GiftCashoutAmount>0</GiftCashoutAmount><GiftDeactivateCount>0</GiftDeactivateCount><GiftDeactivateAmount>0</GiftDeactivateAmount><GiftAdjustCount>0</GiftAdjustCount><GiftAdjustAmount>0</GiftAdjustAmount><LoyaltyRedeemCount>0</LoyaltyRedeemCount><LoyaltyRedeemAmount>0</LoyaltyRedeemAmount><LoyaltyIssueCount>0</LoyaltyIssueCount><LoyaltyIssueAmount>0</LoyaltyIssueAmount><LoyaltyReloadCount>0</LoyaltyReloadCount><LoyaltyReloadAmount>0</LoyaltyReloadAmount><LoyaltyReturnCount>0</LoyaltyReturnCount><LoyaltyReturnAmount>0</LoyaltyReturnAmount><LoyaltyForcedCount>0</LoyaltyForcedCount><LoyaltyForcedAmount>0</LoyaltyForcedAmount><LoyaltyActivateCount>0</LoyaltyActivateCount><LoyaltyActivateAmount>0</LoyaltyActivateAmount><LoyaltyDectivateCount>0</LoyaltyDectivateCount><LoyaltyDeactivateAmount>0</LoyaltyDeactivateAmount><CashSaleCount>0</CashSaleCount><CashSaleAmount>0</CashSaleAmount><CashReturnCount>0</CashReturnCount><CashReturnAmount>0</CashReturnAmount><CheckSaleCount>0</CheckSaleCount><CheckSaleAmount>0</CheckSaleAmount><CheckAdjustCount>0</CheckAdjustCount><CheckAdjustAmount>0</CheckAdjustAmount></body>';
  	
$xml = simplexml_load_string($xml_string);
$json = json_encode($xml); // convert the XML string to JSON
$data=json_decode($json,true);
echo "<pre>";print_r($data);exit;
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
		}*/
	public function batchClose(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$response['batch_no'] = '';
		$response['result'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$machine_type=$this->input->post('machine_type');
  		$login_id=$this->input->post('login_id');
  		$xml_string=$this->input->post('xml_string');
		if(!empty($vendor_id)){
			$xml = simplexml_load_string($xml_string);
			$json = json_encode($xml); // convert the XML string to JSON
			$data=json_decode($json,true);
			//echo "<pre>";print_r($data);exit;
			if(!empty($data)){
			$CreditSaleAmount=	$data['CreditSaleAmount']/100;
			$DebitSaleAmount=	$data['DebitSaleAmount']/100;
			$CreditReturnAmount=	$data['CreditReturnAmount']/100;
			$DebitReturnAmount=	$data['DebitReturnAmount']/100;
		$queryNew=$this->db->query('insert into batch_close_report set credit_count="'.$data['CreditSaleCount'].'",credit_amount="'.$CreditSaleAmount.'",debit_count="'.$data['DebitSaleCount'].'",debit_amount="'.$DebitSaleAmount.'",credit_return_count="'.$data['CreditReturnCount'].'",credit_refund_amount="'.$CreditReturnAmount.'",debit_return_count="'.$data['DebitReturnCount'].'",debit_return_amount="'.$DebitReturnAmount.'",login_id="'.$login_id.'",machine_type="'.$machine_type.'",vendor_id="'.$vendor_id.'"');
		}
			$query = $this->db->query("SELECT o.order_id,o.order_number FROM orders o LEFT JOIN batch b ON o.order_id = b.order_id WHERE b.order_id IS NULL and o.vendor_id='".$vendor_id."' AND DATE(o.created_date)='".date('Y-m-d')."'  ");
			if($query->num_rows()>0){
				
				$orderData = $query->result();
				$batch_no = $this->getLastBatchNumber();
				foreach($orderData as $order){
					
					$order_id = $order->order_id;
					$order_number = $order->order_number;
					
					$query2 = $this->db->query("insert into batch set batch_no='".$batch_no."', order_id='".$order_id."', order_number='".$order_number."', created_date='".date('Y-m-d')."', vendor_id='".$vendor_id."' ");
					
					
					if($query2){
						$response['status'] = 1;
						$response['batch_no'] = $batch_no;
						$response['message'] = 'Batch closed successfully!';
					}else{
						$response['status'] = 1;
						$response['batch_no'] = '';
						$response['message'] = 'Something went wrong!';
					}
				}
			}else{
				$response['status'] = 0;
				$response['message'] = 'Batch not found';
			}
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		
        echo json_encode($response);
	}

	
	public function getLastBatchNumber(){
		
		$query = $this->db->query("select max(batch_no) as batch_no from batch");
		if($query->num_rows()>0){
			$res = $query->row();
			$batch_no = $res->batch_no;
			$batch_no = $batch_no+1;
		}else{
			$batch_no = 1;
		}
		return $batch_no;
	}
	
	public function getBatchDetail(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post("vendor_id");
		if(!empty($vendor_id)){
				
			$query = $this->db->query("select b.batch_id, b.batch_no, b.order_id, b.order_number, DATE_FORMAT(b.created_date,'%m/%d/%Y') as batch_date from batch b INNER JOIN orders o ON o.order_id=b.order_id where o.vendor_id='".$vendor_id."' order by b.batch_id DESC");
			if($query->num_rows()>0){
				
				$result = $query->result();
				
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
				
			}else{
				$response['status'] = 0;
				$response['result'] = '[]';
				$response['message'] = 'No data found!';
			}
		}else{
			$response['status'] = 0;
			$response['result'] = '[]';
			$response['message'] = 'Required parameter missing!';
		}
		
		echo json_encode($response);
	}

	
	public function getPreview(){
		
		$vendor_id = $this->input->post('vendor_id');	
		$customer_id = $this->input->post('customer_id');	
		$appointment_id = $this->input->post('appointment_id');	
		$appointment_type = $this->input->post('appointment_type');	
		$token_no = $this->input->post('token_no');	
		$search = $this->input->post('search');	   //getCustApp, getCustManage
		
		$deposit_id = $this->input->post('deposit_id');	
		
		
		if(!empty($vendor_id)){
		if($deposit_id!=''){
			$deposit_id=$deposit_id;
		}else{
			$deposit_id='';
		}
		
		if($appointment_id!=0){
			if($appointment_type==3){
				$where = "WHERE aps.is_addon=0 AND a.vendor_id='".$vendor_id."' and a.is_checkout='0' and a.appointment_type='3' and a.token_no='".$token_no."' ";
				$con2="customer_id IN (".$customer_id.")";
			}else if($appointment_type==2){
				$where = "WHERE aps.is_addon=0 AND a.vendor_id='".$vendor_id."' and a.is_checkout='0' and a.appointment_type='2' and a.token_no='".$token_no."' ";
				$con2="customer_id IN (".$customer_id.")";
			}
			else{
				$where = " WHERE a.appointment_id IN($appointment_id) AND a.customer_id=$customer_id  AND aps.is_addon=0 AND a.vendor_id='".$vendor_id."' and a.is_checkout='0' ";
				
				$con2="customer_id IN (".$customer_id.")";
			}
		}
		else if($appointment_id==0 && $search=='getCustApp'){
			$where = " WHERE  a.vendor_id='".$vendor_id."' and a.is_checkout='0' and a.customer_id='".$customer_id."' and a.token_no='".$token_no."' ";
			$con2="customer_id=".$customer_id."";
		}
		else if($appointment_id==0 && $search=='getCustManage'){
			$where = " WHERE  a.vendor_id='".$vendor_id."' and a.is_checkout='0' and a.customer_id IN (".$customer_id.") and a.deposit_id='".$deposit_id."' ";
			$con2="customer_id IN (".$customer_id.")";
		}
		else{
			
			$where = " WHERE a.customer_id=$customer_id AND aps.is_addon=0 AND a.vendor_id='".$vendor_id."'  and a.is_checkout='0' ";
			$con2="customer_id IN (".$customer_id.")";
		}
		
		$query = $this->db->query("select
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
							CONCAT(st.firstname,' ',st.lastname) as stylist_name,
							(select GROUP_CONCAT(appointment_id) from appointment where ".$con2." and is_checkout=0) as app_id
							from appointment_service aps
							INNER JOIN appointment a
							ON a.appointment_id=aps.appointment_id
							INNER JOIN service s
							ON s.service_id=aps.service_id
							INNER JOIN stylist st
							ON st.stylist_id=aps.stylist_id
							$where
							ORDER BY aps.as_id DESC
							
		");
		//echo $this->db->last_query();exit;
		$result = $query->result();
		
		if($query->num_rows()>0){
			$response['status'] = 1;
			$response['result'] = $result;
			$response['message'] = '';
		}else{
			$response['status'] = 2;
			$response['result'] = '[]';
			$response['message'] = 'No data found!';
		}
	}else{
			$response['status'] = 0;
			$response['result'] = '[]';
			$response['message'] = 'Required parameter missing!';
	}
	
	echo json_encode($response);
	}
	function salon_gallery(){
		$vendor_id=$this->input->post('vendor_id');
		$path = "http://".$_SERVER['HTTP_HOST'].'/salon/assets/img';
		if(!empty($vendor_id)){
			$query=$this->db->query("select CONCAT('$path','/',main_image) as photo from gallery where vendor_id='".$vendor_id."' ")->result();
			if(!empty($query)){
				$response['status'] = 1;
			$response['result'] = $query;
			$response['message'] = 'salon image';
			}else{
				$response['status'] = 1;
			$response['result'] = array();
			$response['message'] = 'Images not found';
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	public function cashCount(){
		
		$response['status'] = 0;
		$response['message'] = '';
			
		$vendor_id = $this->input->post('vendor_id');
		$stylist_id = $this->input->post('stylist_id');
		$type = $this->input->post('type');
		$one_cent = $this->input->post('1_cent');
		$five_cent = $this->input->post('5_cent');
		$ten_cent = $this->input->post('10_cent');
		$twentyfive_cent = $this->input->post('25_cent');
		$one_dollar = $this->input->post('1_dollar');
		$five_dollar = $this->input->post('5_dollar');
		$ten_dollar = $this->input->post('10_dollar');
		$twenty_dollar = $this->input->post('20_dollar');
		$fifty_dollar = $this->input->post('50_dollar');
		$hundred_dollar = $this->input->post('100_dollar');
		$total = $this->input->post('total');
		
		$datetime = date('Y-m-d H:i:s');
		
		if(!empty($vendor_id) && !empty($stylist_id) && !empty($type)){
			
			$query = $this->db->query("insert into cash_count set stylist_id='".$stylist_id."', type='".$type."', datetime='".$datetime."', 1_cent='".$one_cent."', 5_cent='".$five_cent."', 10_cent='".$ten_cent."', 25_cent='".$twentyfive_cent."', 1_dollar='".$one_dollar."', 5_dollar='".$five_dollar."', 10_dollar='".$ten_dollar."', 20_dollar='".$twenty_dollar."', 50_dollar='".$fifty_dollar."', total='".$total."' ");
			//echo $this->db->last_query();die;
			if($query){
				
				$response['status'] = 1;
				$response['message'] = "Cashier $type successfully!";
			}else{
				$response['status'] = 0;
				$response['message'] = "Something went wrong!";
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}

}
