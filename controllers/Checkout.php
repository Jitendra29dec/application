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


class Checkout extends CI_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        
        
       // error_reporting(0);
    }
    public function getCheckoutData(){
      // echo "jddjh";die;
     // echo "<pre>";print_r($_POST);exit;
        $customer_id = $this->input->post('customer_id');
        $appointment_id = $this->input->post('appointment_id');
        $appointment_type = $this->input->post('appointment_type');
        $token_no = $this->input->post('token_no');
        $vendor_id = $this->input->post('vendor_id');
        $deposit_id = $this->input->post('deposit_id');
         $search = $this->input->post('search');
         $checkout_type = $this->input->post('checkout_type');
        // echo "<pre>";print_r($_POST);exit;
        if($appointment_id!=0 && $search=='calender'){
            if($appointment_type==3){
                if($checkout_type=='group'){
                  $where = "WHERE aps.is_addon=0 AND a.vendor_id='".$vendor_id."' and a.is_checkout='0' and a.appointment_type='3' and a.token_no='".$token_no."' and a.status NOT IN (8,4) ";  
              }else{
                $where = "WHERE aps.is_addon=0 AND a.vendor_id='".$vendor_id."' and a.is_checkout='0' and a.appointment_type='3' and a.token_no='".$token_no."' and a.customer_id='".$customer_id."' and a.status NOT IN (8,4) "; 
              }
                
            }else if($appointment_type==2){
                $where = "WHERE aps.is_addon=0 AND a.vendor_id='".$vendor_id."' and a.is_checkout='0' and a.appointment_type='2' and a.token_no='".$token_no."' and a.status NOT IN (8,4)";
            }
            else {
                $where = "WHERE aps.is_addon=0 AND a.vendor_id='".$vendor_id."' and a.is_checkout='0' and a.appointment_type='1' and a.token_no='".$token_no."' and a.status NOT IN (8,4) ";
            }
        }/*else if($appointment_id==0 && $search=='search'){
              $where = " WHERE a.customer_id='".$customer_id."'  AND aps.is_addon=0 AND a.vendor_id='".$vendor_id."' and a.is_checkout='0' ";
          } */
          else if($search=='getManagdata'){

            $deposit_id = $this->input->post('deposit_id');
              $where = " WHERE a.customer_id IN(".$customer_id.")  AND  a.vendor_id='".$vendor_id."' and a.deposit_id='".$deposit_id."' and a.is_checkout='0' ";
          }
          else{
                  $where = " WHERE  a.vendor_id='0'";
        
          }
          if(!empty($deposit_id)  && $deposit_id !='null'){
            $getDepositType=$this->db->query('select customer_total,distribute_in,distribute_amount,balance_used from deposit_customer where id="'.$deposit_id.'" and status=1')->row();
            $getDeposit=$this->db->query('select sum(amount) as total from deposit_installment where deposit_id="'.$deposit_id.'" and is_active=1')->row();
            //echo 'select customer_total,distribute_in,distribute_amount,balance_used from deposit_customer where id="'.$deposit_id.'" and status=1';die;
            if(!empty($getDepositType->distribute_in) && !empty($getDeposit) && $checkout_type!='group'){
                 //$response['text']='vishal';
                $left_amount=$getDeposit->total -$getDepositType->balance_used;
                if($getDepositType->distribute_in=='equal'){
                // echo "harsh";
                  // echo  $getDeposit->total."harsh".$getDepositType->customer_total;die;
                    $deposit_amount=$getDeposit->total/$getDepositType->customer_total;
                    $deposit_type="$";
                    $left_amount=0;
                    $deposit_text_type='equal';
                }
               else if($getDepositType->distribute_in=='equal_percent'){

                    $deposit_amount=$getDeposit->total * $getDepositType->distribute_amount/100;
                    $deposit_type="$";
                    $left_amount=0;
                    $deposit_text_type='equal_percent';
                }
               else if($getDepositType->distribute_in=='fix' && $getDeposit->total > $getDepositType->distribute_amount){
                   // if()
                    $deposit_amount1=$getDepositType->distribute_amount;
                    if($left_amount > $deposit_amount1){
                        $deposit_amount1=$getDepositType->distribute_amount;
                    }else{
                        $deposit_amount1=0;
                    }
                    $deposit_type="$";
                    $left_amount=$left_amount;
                    $deposit_text_type='fix';
                }
               else if($getDepositType->distribute_in=='deposit_percent'){
                    $deposit_amount=$getDeposit->total*$getDepositType->distribute_amount/100;
                    $deposit_type="$";
                    $left_amount=0;
                    $deposit_text_type='deposit_percent';
                }
                else if($getDepositType->distribute_in=='service'){
                    $deposit_amount=$getDepositType->distribute_amount;
                    $deposit_type="%";
                    $left_amount=$left_amount;
                     $deposit_text_type='service';
                }
                else{
                   // echo "vishal";
                    // $response['text']='rudra';
                    $deposit_amount=0;
                    $deposit_type="$";
                    $left_amount=0;
                    $deposit_text_type='';
                }
            }


            else if(empty($getDepositType->distribute_in) && !empty($getDeposit) && $checkout_type!='group'){
               // $response['text']='harsh';
                    $deposit_amount=$getDeposit->total-$getDepositType->balance_used;
                    $deposit_type="$";
            }else if($checkout_type=='group' &&  !empty($getDeposit)){
                   $response['text']='harsh';
                  $deposit_amount=$getDeposit->total - $getDepositType->balance_used;
                    $deposit_type="$";
            }

            else{
                // $response['text']='sanjeev';
                $deposit_amount=0;
                $deposit_type="$";
            }

          }else{
            
            $deposit_amount=0;
            $deposit_type="$";
          }
          // s.tax_type,
        //(CASE WHEN is_tax=1 THEN aps.price*s.tax_rate/100 else '0' END) as tax_rate,
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
                            s.is_tax,
                            (select group_concat(tax_type separator '+') from multiple_tax where sp_id=aps.service_id and type=1) as tax_type,
                            (CASE WHEN s.is_tax=1 THEN (select sum(tax_amount) from multiple_tax where sp_id=aps.service_id and type=1) ELSE 0 END) as tax_rate,
                           
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
                            left join tax_service as ts on s.tax_id=ts.tax_id
                            $where
                            ORDER BY aps.as_id DESC
                            
              ")->result();

            $otherV = '';
            foreach($result as $tx){
                if($tx->is_tax==1){


                 if ($otherV) $otherV .= ',';
                    $otherV .= $tx->service_id;
               }
           }
               if($otherV ==''){
                $otherV =0;
               }
              // echo "harsh".$otherV;die;
           //    echo 'select sum(tax_amount) as tax1 from multiple_tax where sp_id IN('.$otherV.') and tax_type="tax1" and type=1';die;
         $tax_service1=$this->db->query('select sum(tax_amount) as tax1 from multiple_tax where sp_id IN('.$otherV.') and tax_type="tax1" and type=1')->row();
          $tax_service2=$this->db->query('select sum(tax_amount) as tax2 from multiple_tax where sp_id IN('.$otherV.') and tax_type="tax2" and type=1')->row();
           $tax_service3=$this->db->query('select sum(tax_amount) as tax3 from multiple_tax where sp_id IN('.$otherV.') and tax_type="tax3" and type=1')->row();
             $selQuery = "select 
                        c.cart_id, 
                        c.quantity, 
                        p.product_id,
                        p.product_name, 
                        p.barcode_id, 
                        p.price_retail,
                        (select group_concat(tax_type separator '+') from multiple_tax where sp_id=p.product_id and type=2) as tax_type,
                        (select group_concat(tax_amount) from multiple_tax where sp_id=p.product_id and type=2) as tax_value,
                        (CASE WHEN p.is_tax=1 THEN (select sum(tax_amount) * c.quantity  from multiple_tax where sp_id=p.product_id and type=2)ELSE 0 END) as tax_rate,
                        
                        p.sku,
                        p.quantity as pro_quant,
                        p.is_tax
                        from cart c 
                        INNER JOIN product p 
                        ON p.product_id=c.product_id 
                        left join tax_product as tp on p.tax_id=tp.tax_id
                        where p.is_active='1' 
                        and p.is_delete='0' 
                        AND c.customer_id='".$customer_id."' and p.business_use=0
                        order by c.cart_id desc ";
         $result2= $this->db->query($selQuery)->result();
         
           $tax1=$tax_service1->tax1;
           $tax2=$tax_service2->tax2;
           $tax3=$tax_service3->tax3;
      //  $response['product']=$this->db->last_query();
      $certificate= $this->db->query('select A.*,B.service_name  from gift_certificate As A left join service as B on A.service_id=B.service_id where A.customer_id="'.$customer_id.'" and A.is_active=0')->result();
      $gift_card= $this->db->query('select * from gift_card where customer_id="'.$customer_id.'" and is_active=0')->result();
      $iou_data= $this->db->query('select A.*,B.order_number as order_no from customer_iou_amount as A left join orders as B on A.order_id=B.order_id where A.status=0 and A.customer_id="'.$customer_id.'" order by id desc limit 1')->row();
      $customer_packages=$this->db->query('select A.*,B.plan_name,price from customer_packages as A inner join packages_plan as B on A.plan_id=B.plan_id where A.customer_id="'.$customer_id.'" ')->row();
      $customer_membership=$this->db->query('select * from customer_membership where customer_id="'.$customer_id.'" and is_checkout=0')->row();
      $gift_card_reson=$this->db->query("select * from certificate_reason ")->result();
      $certificate_last_id=$this->db->query('select MAX(gift_id) AS Max_Id from gift_certificate')->row();
      $getServiceCharge=$this->db->query('select cash_discount_percentage as value,cash_discount_display_name as display_name from vendor where cash_discount_is_active="1" and vendor_id="'.$vendor_id.'"')->row();
       $giftcard_last_id=$this->db->query('select MAX(card_id) AS Max_Id from gift_card')->row();

       /*if($appointment_type==3){
        $deposit_data_amount=$this->db->query('select deposit_amount from deposit_customer where id="'.$deposit_id.'"')->row();
        $deposit_data=$deposit_data_amount->deposit_amount;
       }else{
        $deposit_data="";
       }*/

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
         if(empty($customer_packages)){
            $customer_packages=(Object)[];
         }
         if(empty($getServiceCharge)){
            $getServiceCharge=(Object)[];
         }
         if(empty($customer_membership)){
            $customer_membership=(Object)[];
         }
         if(empty($gift_card_reson)){
            $gift_card_reson=array();
         }
        $query_customer = $this->db->query("select l.created_date AS customer_since, (select o.created_date  from orders o WHERE o.customer_id=$customer_id order by o.created_date desc limit 1) as birthday , '' as points_money from login l INNER JOIN customer c ON l.login_id=c.login_id where c.customer_id='".$customer_id."'  ");
        
        $customer_data = $query_customer->row();
           $response['status'] = 1;
           if($search=='search'){
                 $response['services'] = array();
           }else{
                 $response['services'] = $result;
           }
		   
		   
		   $current_date = date('Y-m-d');
			
			$qq = $this->db->query("select * from tax_product where start_date='".$current_date."' and type='future' and vendor_id='".$vendor_id."' ");
			if($qq->num_rows()>0){
				
				$new_current_tax = $qq->result();
				
				foreach($new_current_tax as $nct){
					
					$this->db->query("update tax_product set tax_rate='".$nct->tax_rate."', start_date='".$nct->start_date."', description='".$nct->description."' where tax_type='".$nct->tax_type."' and type='current' and vendor_id='".$vendor_id."' ");
				}
				
				$this->db->query("update tax_product set tax_rate='0.00', start_date=NULL, description=NULL WHERE type='future' and vendor_id='".$vendor_id."'  ");
			}
			
		   $query_tax = $this->db->query("select description as tax_name, tax_rate from tax_product where type='current' and tax_rate!='0.00' and vendor_id='".$vendor_id."' ")->result();
		   
		   
		  $random_number= mt_rand(10000000,99999999);
           $random_number2= mt_rand(10000000,99999999);
           $max_amount = $this->db->query("select max_amount from gift_settings  where vendor_id='".$vendor_id."' ")->row();
           if(!empty($max_amount->max_amount)){
            $maxAmount=$max_amount->max_amount;
           }else{
            $maxAmount=0;
           }
           $tip=$this->db->query('select is_cash_tip from vendor where vendor_id="'.$vendor_id.'"')->row();
		   //"00".$giftcard_last_id->Max_Id;
          //"00".$certificate_last_id->Max_Id
            $response['product'] = $result2;
            $response['certificate'] = $certificate;
            $response['gift_card'] = $gift_card;
            $response['iou_data'] = $iou_data;
            $response['customer_packages'] = $customer_packages;
            $response['customer_membership'] = $customer_membership;
            $response['customer_data'] = $customer_data;
            $response['gift_certificate_reson'] = $gift_card_reson;
          //  $response['deposit_data']=$deposit_data;
            $response['certificate_last_id'] = $random_number;
            $response['customer_membership'] = $customer_membership;
            $response['giftcard_last_id'] = $random_number2;
            $response['certificate_max_amount']=$maxAmount;
            $response['getServiceCharge']=$getServiceCharge;
            $response['tax'] = $query_tax;
            $response['deposit_amount'] = $deposit_amount;
            $response['deposit_type'] = $deposit_type;
            $response['left_amount'] = $left_amount;
            $response['deposit_text_type'] = $deposit_text_type;
            $response['tip_active']=$tip->is_cash_tip;
            $response['tax1']= $tax1;
            $response['tax2']= $tax2;
            $response['tax3']= $tax3;
            $response['message'] = 'success';
      
        }else{
          $response['status'] =0;
          $response['message'] = 'Required parameter missing!';      
        }
          echo json_encode($response);
          }
          public function add_service(){
             $customer_id = $this->input->post('customer_id');
             $appointment_date = date('Y-m-d',strtotime($_POST['appointment_date']));
             $token = $this->input->post('token_no');
             $vendor_id = $this->input->post('vendor_id');
             $apt_type = $this->input->post('appointment_type');
             $appointment_time = $this->input->post('appointment_time');
             $duration = $this->input->post('duration');
             $endTime = strtotime("+".$duration ."minutes", strtotime($appointment_time));
             $endtime_new=date('H:i', $endTime);
             $service = $this->input->post('service');
             $stylist1 = $this->input->post('stylist1');
             $amount_service = $this->input->post('amount_service');
             if($customer_id !=''){
                $data1=array(
                'customer_id'=>$customer_id,
                'vendor_id'=>$vendor_id,
                'token_no'=>$token,
                'appointment_type'=>$apt_type,
                'date'=>$appointment_date 
                

        );
        $this->db->insert('appointment',$data1);
        //echo $this->db->last_query();exit;
        $appointment_id=$this->db->insert_id();
        $data2=array(
                'appointment_id'=>$appointment_id,
                'customer_id'=>$customer_id,
                'service_id'=>$service,
                'stylist_id '=>$stylist1,
                'appointment_time'=>$_POST['appointment_time'],
                'price'=>$amount_service,
                'duration'=>$duration,
                'appointment_end_time'=>$endtime_new,
                'is_extra'=>1

        );
        $this->db->insert('appointment_service',$data2);
    //  echo $this->db->last_query();exit;
        //$result=$this->db->query('select A.appointment_id as id,date_format(A.date,"%d %M %Y") as appointment_date,s.service_name as service_name,s.sku as sku,s.price as price,st.firstname as fname,st.lastname as lname,ap_se.service_tip as tip,ap_se.as_id as service_id, s.tax_amount from appointment as A inner  join appointment_service as ap_se on A.appointment_id=ap_se.appointment_id left join service as s on ap_se.service_id =s.service_id left join stylist as st on st.stylist_id=ap_se.stylist_id where A.customer_id ="'.$_POST['customer_id'].'" and A.appointment_id="'.$appointment_id.'"')->row();

            $response['status'] = 1;
            $response['last_id'] = $appointment_id;
      
            $response['message'] = 'Service added successfully!';
             }else{
             $response['status'] =0;
             $response['message'] = 'Required parameter missing!';   
             }
             echo json_encode($response);


          }
          public function getMembership(){
            $vendor_id= $this->input->post('vendor_id');
            if($vendor_id!=''){
            $query = $this->db->query("select plan_id, plan_name, duration, price from membership_plan where is_delete='0' and vendor_id='".$vendor_id."' ");
            $result = $query->result();
            $response['status'] = 1;
            $response['membsership'] = $result;
      
            $response['message'] = 'succ';
        }else{
            $response['status'] =0;
             $response['message'] = 'Required parameter missing!';   
          }
           echo json_encode($response);
        }
        public function getPackages(){
            $vendor_id= $this->input->post('vendor_id');
            $customer_id= $this->input->post('customer_id');
            if($vendor_id !='' && $customer_id !=''){
                $query = $this->db->query("select plan_id, plan_name, duration,price from packages_plan where is_delete='0' and vendor_id='".$vendor_id."' and plan_id NOT IN(select plan_id from customer_packages where customer_id='".$customer_id."')");
                    $result = $query->result();
                    $response['status'] = 1;
                    $response['packages'] = $result;
                    $response['message'] = 'succ';
            }else{
                $response['status'] =0;
                $response['message'] = 'Required parameter missing!'; 
            }
            echo json_encode($response);
        }
        public function getTemplates(){
            $type=$this->input->post('type');
            //$type=$this->input->post('type');
            if($type !=''){
                    $query = $this->db->query("select * from template where is_delete='0' and template_type='".$type."' order by template_id ASC");
                    $result = $query->result();
                    $response['status'] = 1;
                    $response['template'] = $result;
                    $response['message'] = 'succ';
            }else{
                $response['status'] =0;
                $response['message'] = 'Required parameter missing!'; 
            }
        echo json_encode($response);
        }
        public function getTemplateData(){
            $temp_id=$this->input->post('temp_id');

            //assets/img/template/hair/1.png
            if($temp_id !=''){
                    $query=$this->db->query("select * from template_images where template_id='".$temp_id."' and status=1 ")->result();
                    $response['status'] = 1;
                    $response['image_url']=base_url()."assets/img/template/";
                    $response['template'] = $query;
                    $response['message'] = 'succ';
        
            }else{
                $response['status'] =0;
                $response['message'] = 'Required parameter missing!'; 
            }
      echo json_encode($response);
            }
    function addProduct(){
    $product_id=explode(",",$_POST['product_id']);
    $quant=explode(",",$_POST['quant']);
    $appointment_id = 0;
    $customer_id = $_POST['customer_id'];
    if(!empty($product_id) && !empty($quant)){
     
      foreach($product_id as $key=>$val){
      
          $row=$this->db->query('select * from cart where product_id="'.$val.'" and customer_id="'.$customer_id.'"')->row();
          $quant_new=$row->quantity+$quant[$key];
          if(count($row) > 0){
              $this->db->query('update cart set quantity="'.$quant_new.'" where product_id="'.$val.'" and customer_id="'.$customer_id.'"');
          }else{
          $this->db->query("insert into cart set product_id='".$val."', quantity='".$quant[$key]."', appointment_id='".$appointment_id."',customer_id='".$customer_id."', created_date='".date('Y-m-d H:i:s')."' ");  
          }
          //$result=$this->db->query('select * from cart where product_id="'.$val.'" and customer_id="'.$customer_id.'"')->result();
          $response['status'] =1;
          $response['message'] = 'Product added successfully!'; 
        //  $response['products']=$result;
      }
    
      }else{
        $response['status'] =0;
        $response['message'] = 'Required parameter missing!'; 
        //$response['products']=array();
        //echo 'success';
      }
    echo json_encode($response);
  }
   function add_certificate()
    {
    //echo "<pre>";print_r($_POST);exit;
        $gift_certificate_number = $this->input->post("gift_certificate_no");
        $service_id = $this->input->post("service_id");
        $gift_type = $this->input->post("gift_type");
       // $customer_id=$this->input->post("customer_id");
    if($gift_type==1){
      $amount = 0;
     //$this->input->post("expire_date");
    }else{
        $amount_type=$this->input->post('amount_type');
        $amount = str_replace("$"," ",$this->input->post("amount"));
       // $expire_on = '';
    }
    $issue_date = $_POST['issue_date'];
     $expire_on = $_POST['expire_on'];
        $notifyBy=$_POST['notifyBy'];
        $template_image_id=$_POST['template_image_id'];
        
        /*$select_recipient_name = $this->input->post("select_recipient_name");
    if($select_recipient_name==0){
      $recipient_name = $this->input->post("gift_recipient_name");
      $recipient_email = $this->input->post("gift_recipient_email");
    }else{
      $exp = explode('-',$select_recipient_name);
      $name = $exp[1];
      $email = $exp[2];
      $recipient_name = $name;
      $recipient_email = $email;
    }*/
        //echo $recipient_email;
        $message = $this->input->post("message");
          $message_custom = $this->input->post("message_custom");
          if($message==''){
            $message=$message_custom;
          }else{
            $message=$message;
          }
       // $template_id = $this->input->post("template_id");
         $vendor_id = $this->input->post("vendor_id");
        $login_id = $this->input->post("login_id");
       /* if(isset($_POST['gift_recipient_phone'])){
    $gift_recipient_phone=$this->input->post("gift_recipient_phone");
        }else{
          $gift_recipient_phone=0;  
        }*/
    $customer_id = $this->input->post("customer_id");
        /*$template_image_id = $this->input->post("template_image_id");*/


        /* "recipient_name" => trim($recipient_name),
            "recipient_email" => trim($recipient_email),
             "recipient_phone" => trim($gift_recipient_phone),
                "template_id" => trim($template_id),
            "template_image_id"=>$template_image_id,
             */
  if(!empty($gift_certificate_number) && !empty($customer_id)){
    $insert_array = array(
            
            "gift_certificate_no" => trim($gift_certificate_number),
            "service_id" => trim($service_id),
            "amount_type"=>$amount_type,
            "amount" => trim($amount),
            "issue_date" => trim($issue_date),
            "expire_on" => trim($expire_on),
            "customer_id" => trim($customer_id),
            "message" => trim($message),
            "is_active"=>0,
            "is_delete"=>0,
            "notifyBy"=>$_POST['notifyBy'],
            "vendor_id"=>$vendor_id,
            "template_image_id"=>$template_image_id,
            "created_by" => $login_id,
            "created_date" => date("Y-m-d H:i:s")
        );
       $this->db->insert('gift_certificate',$insert_array);
       //echo $this->db->last_query();exit;
       //echo $this->db->last_query();exit;
       $insert_id=$this->db->insert_id();
       /* $this->user_activity->log_activity($this->session->userdata('user_name') . " added gift certificate with insertid \"" . $insert_id . "\" on " . date('d-m-Y h:i A'));*/

        if ($insert_id) {
            //$check_reson=$this->db->query()
            if($message_custom !=''){
               $this->db->query("insert into certificate_reason set reason='".$message_custom."' "); 
            }
          
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
          $response['message'] = 'Certificate added successfully'; 
          //$response['products']=$certificate;
        } else {
           $response['status'] =0;
          $response['message'] = 'Something Wrong'; 
          //$response['products']=$certificate;
           // echo "Gift Certificate not generated successfully!";
        }
      }else{
         $response['status'] =0;
          $response['message'] = 'Required parameter missing'; 
          $response['products']=$certificate;
      }

        echo json_encode($response);
}

 function add_gift_card()
    {
        $gift_card_number = $this->input->post('gift_card_number');
   // $card_issue_date = date('Y-m-d',strtotime($_POST['card_issue_date']));//$this->input->post('card_issue_date');
    $card_buyer_name = $this->input->post('card_buyer_name');
    $card_buyer_email = $this->input->post('card_buyer_email');
   // $card_phone = $this->input->post('card_phone');
    $card_message = $this->input->post('card_message');
    $customer_id = $this->input->post('card_customer_id');
    $card_amount = $this->input->post('card_amount');
        $notifyBy_card=$this->input->post('notifyBy_card');
        $myself=$this->input->post('myself');
        $recipient_name=$this->input->post('recipient_name');
        $recipient_email=$this->input->post('recipient_email');
        $template_image_id=$this->input->post('template_image_id');
        /*$template_image_id=$this->input->post('template_image_id');
        $template_id=$this->input->post('template_id')
        notifyby='".$notifyBy_card."'
        */;
        $vendor_id=$this->input->post('vendor_id');
//issue_date='".$card_issue_date."',
        //phone='".$card_phone."',
        //template_id='".$template_id."',template_image_id='".$template_image_id."',
        if(!empty($gift_card_number) && !empty($card_buyer_name)){
          $insert_id = $this->db->query("insert into gift_card set customer_id='".$customer_id."',card_number='".$gift_card_number."',  buyer_name='".$card_buyer_name."', buyer_email='".$card_buyer_email."',  message='".$card_message."',created_date='".date('Y-m-d H:i:s')."', vendor_id='".$this->input->post('vendor_id')."',intial_amount='".$card_amount."',amount='".$card_amount."',recipient_name='".$recipient_name."',recipient_email='".$recipient_email."',template_image_id='".$template_image_id."',is_active=0, issue_date='".date('Y-m-d')."',is_myself='".$myself."'  ");
        
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
                  $response['message'] = 'Gift card added successfully'; 
                     $response['status']=1;
            
        } else {
            $response['message'] = 'Somethinng wrong'; 
                     $response['status']=0;
        }
       
}
else{
 $response['message'] = 'Required parameter missing'; 
          $response['status']=0;
}
  echo json_encode($response);
}
function getServiceDiscount(){
     $country = $this->ip_info("Visitor", "Country");
        if($country=='India'){
            date_default_timezone_set("Asia/Kolkata");
        }else{
            
            date_default_timezone_set("America/Los_Angeles");
        }
  $vendor_id=$this->input->post('vendor_id');
  $current_date=date('Y-m-d');
  if(!empty($vendor_id)){
   
      $selQuery = "SELECT
                      coupon_id as discount_id, 
                      discount,
                      IF(coupon_type=1,'$','%') as discount_type,date(c.end_date) as expiry_date
                    FROM coupon AS c
                    WHERE c.is_delete = 0
          AND c.is_active=1
           AND c.discount_for=2
          AND c.vendor_id='".$vendor_id."' and date(c.start_date) <='".$current_date."' and date(c.end_date) >='".$current_date."'";
        $result = $this->db->query($selQuery)->result();
        if(!empty($result)){
          $response['status'] =1;
          $response['message'] = 'Discount Service'; 
          $response['services']=$result;
        }else{
           $response['status'] =1;
          $response['message'] = 'No data available';
        }
  
}else{
   $response['message'] = 'Required parameter missing'; 
          $response['status']=0;
}
echo json_encode($response);
}
function getProductDiscount(){
     $country = $this->ip_info("Visitor", "Country");
        if($country=='India'){
            date_default_timezone_set("Asia/Kolkata");
        }else{
            
            date_default_timezone_set("America/Los_Angeles");
        }
  $vendor_id=$this->input->post('vendor_id');
  $current_date=date('Y-m-d');
  if(!empty($vendor_id)){
      $selQuery = "SELECT
                      coupon_id as discount_id,
                      discount,
                      IF(coupon_type=1,'$','%') as discount_type,date(c.end_date) as expiry_date
                    FROM coupon AS c
                    WHERE c.is_delete = 0
          AND c.is_active=1
          AND c.discount_for=1
          AND c.vendor_id='".$vendor_id."'  and date(c.start_date) <='".$current_date."' and date(c.end_date) >='".$current_date."'";
        $result = $this->db->query($selQuery)->result();
        if(!empty($result)){
          $response['status'] =1;
          $response['message'] = 'Discount Product'; 
          $response['products']=$result;
        }else{
           $response['status'] =1;
          $response['message'] = 'No data available';
        }
  
}else{
   $response['message'] = 'Required parameter missing'; 
          $response['status']=0;
}
echo json_encode($response);
}

function removeRow(){
  $row_id=$this->input->post('row_id');
  $type=$this->input->post('type');
  if(!empty($row_id) && !empty($type)){
      if($type=='product'){
        $delete=$this->db->query('delete from cart where cart_id="'.$row_id.'" ');
       // echo $this->db->last_query();exit;
        if($delete){
          $response['status'] =1;
          $response['message'] = 'Product deleted successfully'; 
        }else{
           $response['status'] =1;
          $response['message'] = 'Something Wrong'; 
        }
      }
      if($type=='certificate'){
        $delete=$this->db->query('delete from gift_certificate where gift_id="'.$row_id.'" ');
        if($delete){
          $response['status'] =1;
          $response['message'] = 'Certificate deleted successfully'; 
        }else{
           $response['status'] =1;
          $response['message'] = 'Something Wrong'; 
        }
      }
      if($type=='gift_card'){
        $delete=$this->db->query('delete from gift_card where card_id="'.$row_id.'" ');
        if($delete){
          $response['status'] =1;
          $response['message'] = 'Gift card deleted successfully'; 
        }else{
           $response['status'] =1;
          $response['message'] = 'Something Wrong'; 
        }
      }
      if($type=='service'){
        $delete=$this->db->query('delete from appointment where appointment_id="'.$row_id.'" ');
        $this->db->query('delete from appointment_service where appointment_id="'.$row_id.'" ');
        if($delete){
          $response['status'] =1;
          $response['message'] = 'Service deleted successfully'; 
        }else{
           $response['status'] =1;
          $response['message'] = 'Something Wrong'; 
        }
      }
  }else{
       $response['message'] = 'Required parameter missing'; 
       $response['status']=0;
  }
  
  echo json_encode($response);
}
function check_number(){
    $vendor_id=$_POST['vendor_id'];
    $number=$_POST['number_check'];
    $current_date=date('Y-m-d');
    $type=$_POST['type'];
    if(!empty($number)){
    if($type=='cart'){
    $getData=$this->db->query('select amount from gift_card where card_number="'.$number.'" and is_active=1 and issue_date >="'.$current_date.'" and vendor_id="'.$vendor_id.'"')->row();
    //print_r($getData);exit;

    if(!empty($getData)){
      $response['status'] =1;
        $response['amount'] = number_format($getData->amount,2); 
      //echo json_encode(array('succ'=>'1','amount'=>number_format($getData->amount,2)));
    }else{
       $response['message'] = 'Gift card not available'; 
       $response['status']=0;
    }
    }else{
      //$today=date('')
      $getData=$this->db->query('select amount from gift_certificate where gift_certificate_no="'.$number.'" and vendor_id="'.$vendor_id.'"')->row();
    if(!empty($getData)){
      $response['status'] =1;
      $response['amount'] = number_format($getData->amount,2); 
    }else{
      $response['message'] = 'Certificate not available'; 
       $response['status']=0;
    }
    }

  }else{
     if($type=='cart'){
         $response['message'] = 'Please enter gift card number'; 
     }
        else{
             $response['message'] = 'Please enter certificate number'; 
        }
      
       $response['status']=0;
  }
  echo json_encode($response);
}
function checkcuppon(){
  $cupponCode=$_POST['cupponCode'];
  if(!empty($cupponCode)){
    $cupponAmount=$this->db->query('select coupon_type as cuppon_type,discount from coupon2 where coupon_number="'.$cupponCode.'"')->row();
    //echo $this->db->last_query();exit;
    if(!empty($cupponAmount)){
         $response['status'] =1;
      $response['amount'] = number_format($cupponAmount->discount,2); 
      //echo $cupponAmount->discount;
    }else{
      $response['message'] = 'Cuppon not available'; 
       $response['status']=0;
    }
  }else{
    $response['message'] = 'Required parameter missing'; 
       $response['status']=0;
  }
    echo json_encode($response);
}
function customer_rewards(){
  $customer_id=$_POST['customer_id'];
  if(!empty($customer_id)){
    $customerPoints=$this->db->query('select * from customer_reward_points where customer_id="'.$customer_id.'"')->row();
    if(!empty($customerPoints)){
      $points=$customerPoints->customer_points;
    }else{
      $points=0;
    }
    $Points_Money=$this->getRewardMoney($points);
    if($Points_Money >0){
      $response['status'] =1;
      $response['amount'] = number_format($Points_Money,2); 
    }else{
      $response['status'] =1;
      $response['amount'] = '0.00'; 
    }
  }else{
    $response['message'] = 'Required parameter missing'; 
    $response['status']=0;
  }
  echo json_encode($response);
}
function getRewardMoney($points){
   
    $query = "select points,cash from reward_points where type=4 ";
    $res = $this->db->query($query)->row();
    $convertMoney=$points/$res->points;
    $newConvertMoney=$convertMoney*$res->cash;
    //echo $newConvertMoney;
    
    return $newConvertMoney;
  }
  function giveRewards(){
    $customer_id=$_POST['customer_id'];
    $ad_new=$_POST['total_amount'];
    $appointment_service=$_POST['total_service'];
    $total_visit=$this->db->query('select order_id from orders where customer_id="'.$customer_id.'"')->num_rows();
   $total_visit= $total_visit+1;
   $spent_per_dollar_point=$this->db->query('select points,cash from reward_points where type=1')->row();
    $visit_per_point=$this->db->query('select points,cash from reward_points where type=2')->row();
    $service_per_point=$this->db->query('select points,cash from reward_points where type=3')->row();
    $setting=$this->db->query('select setting_type from reward_points where type=5')->row();
    $visit_point=$total_visit*$visit_per_point->points;
              $spent_points=$ad_new*$spent_per_dollar_point->points;
              if($appointment_service !='' && $appointment_service !=0){
                if($setting->setting_type=='genral'){
                  $total_service=$appointment_service;
                  $service_point=$total_service*$service_per_point->points;
                }else{
                    $service_point=$total_service_points*$service_per_point->points;
                }
              }else{
                $service_point=0;
              }
              if($visit_point==''){
                $visit_point=0;
              }
              if($service_point==''){
                $service_point=0;
              }
              $total_points=$visit_point+$spent_points+$service_point;
              if($total_points !='' && $total_points !=0){
                $response['customer_points'] = $total_points; 
                $response['visit_point']=$visit_point;
                $response['spent_points']=$spent_points;
                $response['service_point']=$service_point;
                   $response['status']=1;
              }else{
                 $response['total_points'] =0; 
                   $response['status']=0;
              }
                echo json_encode($response);
  }
  
  public function getColorIdByColorType($color_type,$vendor_id){
	  
	  $query = $this->db->query("select color_id from color_settings where vendor_id='".$vendor_id."' and color_type='".$color_type."' ");
	  $res = $query->row()->color_id;
	  return $res;
  }
function ordernow(){
            /*$response['status'] = 1;
            $response['reciptedata']=array();
            $response['order_id'] = 10;
            $response['payment_id']=19;
            $response['message'] = "Order Status Pending";
            echo json_encode($response);exit;*/

  $customer_id = $this->input->post('customer_id');
  $appointment_id = $this->input->post('appointment_id'); 
  $token = $this->input->post('token'); 
  $payment_type = $this->input->post('payment_type');
  $send_invoice = $this->input->post('send_invoice');
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
  //exit;
   $searchForValue = ',';
 if( strpos($_POST['diposite'], $searchForValue) !== false ) {
                                           $deposit_figure1= str_replace( ',', '', $_POST['diposite']);
                                }else{
                                    $deposit_figure1=$_POST['diposite'];
                                }

$final_amount=$this->input->post('total_amount');
$vendor_id=$_POST['vendor_id'];
/*if($_POST['diposite'] > $new_final1){
  $deposit_amount=$_POST['diposite']-$new_final1;
  $this->db->query('update deposit_customer set deposit_amount="'.$mainDeposit.'" where id="'.$_POST['deposit_id'].'"');
}*/
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
$query2 = $this->db->query("insert into payment set payment_type='".$payment_type."', status_id='".$status_id."',payment_status=1,transaction_id='".$transaction_id."', bank_txn_id='".$bank_txn_id."', response_code='".$response_code."', amount='".$final_amount."', message='".$message."', created_date='".date('Y-m-d h:i:s')."', customer_id='".$customer_id."',vendor_id='".$vendor_id."' ");  
  
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
if($_POST['gift_certificate_id'] !=''){
      $this->db->query("update gift_certificate set is_active=1 where gift_id IN(".$_POST['gift_certificate_id'].")");
    }
    if($_POST['gift_card_value'] !=''){
      $total_cash=$this->db->query('select amount from gift_card where card_number="'.$_POST['gift_card_number'].'"')->row();
      $left_balance=$total_cash-$_POST['gift_card_value'];
      $this->db->query("update gift_card set amount='".$left_balance."' where card_number='".$_POST['gift_card_number']."'");
      $this->db->query('insert into gift_card_history set card_number="'.$_POST['gift_card_number'].'",amount="'.$_POST['gift_card_value'].'"');
    }
      
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
  if(!empty($_POST['gift_cart_id'])){ 
  if($query3){
    
      $ids=explode(",",$_POST['gift_cart_id']);
      $countId=count(@$ids);
      for($i=0;$i<$countId;$i++){
      $query32 = $this->db->query("insert into order_detail set order_id='".$order_id."', product_id='".$ids[$i]."', quantity='0',sale_type=4");
    }
      
    $this->db->query('update gift_card set is_active=1 where card_id IN('.$_POST['gift_cart_id'].')');
  
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
    $this->db->query("insert into order_history set order_id='".$order_id."', status_id='10', order_update_date='".date('Y-m-d h:i:s')."' ");
      
      // checkout appointment
      
      if($sale_type==1){
        for($i=0;$i<count($appointment_id);$i++){
			$color_id = $this->getColorIdByColorType('checkout',$vendor_id);
          $this->db->query("update appointment set status='7', color_code='".$color_id."', checkout_time='".date('Y-m-d h:i:s')."', is_checkout='1' where appointment_id='".$appointment_id[$i]."' ");
        }
      }
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
      $data['templateData']= $query=$this->db->query('SELECT A.*,B.image as temp_image,C.service_name as service,C.price as service_price from gift_certificate as A inner join  template_images as B on A.template_image_id=B.id inner join  service as C on A.service_id=C.service_id where A.customer_id="'.$customer_id.'" and A.is_active=1 ')->result();

      $htmlData=$this->load->view('template/getTemplates',$data,true);
      $notify=array();
      foreach ($data['templateData']as $val) {

        $notifydata=explode(",", $val->notifyBy);
          if($notval=='email'){        foreach($notifydata as $notval){

            $subject = 'Gift Certificate';
          
          $headers = "From: info@booknpay.com\r\n";
          $headers .= "Reply-To:  info@booknpay.com\r\n";
          $headers .= "MIME-Version: 1.0\r\n";
          $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

              $message_email = $htmlData;
          
          $mail=mail($val->recipient_email, $subject, $message_email, $headers);
          if($mail){
            $this->db->query('update gift_certificate set is_active=2 where gift_id="'.$val->gift_id.'"');
          }
        }
        }
        
      }
      $data['templateData_card']=$this->db->query('SELECT A.*,B.image as temp_image from  gift_card as A inner join  template_images as B on A.template_image_id=B.id  where A.customer_id="'.$customer_id.'" and is_active=1')->result(); 
      $htmlData=$this->load->view('template/getTemplate_card',$data,true);
      $notify=array();
      foreach ($data['templateData_card']as $val) {
        $notifydata=explode(",", $val->notifyBy);
        foreach($notifydata as $notval){
          if($notval=='email'){
            $subject = 'Gift Card';
          
          $headers = "From: info@booknpay.com\r\n";
          $headers .= "Reply-To:  info@booknpay.com\r\n";
          $headers .= "MIME-Version: 1.0\r\n";
          $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

              $message_email = $htmlData;
          
          $mail=mail($val->buyer_email, $subject, $message_email, $headers);
          if($mail){
            $this->db->query('update gift_card set is_active=2 where card_id="'.$val->card_id.'"');
          }
      }
        }
        
      }
      if(empty($send_invoice)){
        $send_invoice=0;
      }
      /*if($cash_mode_credit==0 || $send_invoice==1){
        $this->mailRecipte($order_id,$vendor_id,$send_invoice,$token);
      }*/

      if($order_id !=''){
        if($payment_type==1){
        //  $this->mailRecipte($order_id,$vendor_id,$send_invoice);
           $response['reciptedata']=$this->order_invoice($order_id);
            $response['status'] = 1;
            $response['order_number'] = $order_number;
            $response['message'] = "Order Successfull.";
        }else{
          $payment_tra=$this->db->query('select transaction_id from payment where payment_id="'.$payment_id.'"')->row();
           $response['status'] = 1;
            $response['reciptedata']=array();
            $response['transaction_id'] =$payment_tra->transaction_id;
            $response['order_id'] = $order_id;
            $response['payment_id']=$payment_id;
            $response['message'] = "Order Status Pending";
        }
      }else{
            $response['status'] = 0;
            $response['order_number'] = 0;
            $response['message'] = "Order not created,Something went wrong";
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
  $response['msg']="Data not available"; 
}

}else{
  $response['message'] = 'Required parameter missing'; 
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
     $response['message'] = 'Required parameter missing'; 
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
  $response['message'] = 'Required parameter missing';
}

echo json_encode($response);
}

/*get Sidebar data*/
public function setPaymentStatus(){
  $payment_id=$this->input->post('payment_id');
   $order_id=$this->input->post('order_id');
  $payment_status=$this->input->post('payment_status');
  $payment_msg=$this->input->post('payment_msg');
  //echo "jdjd";die;
  $harsh=array(
                  'payment_id'=>$payment_id,
                  'order_id'=>$order_id,
                  'payment_status'=>$payment_status,
                  'payment_msg'=>$payment_msg
  );

  if($payment_id !=''){
    $query=$this->db->query('update payment set status_id="'.$payment_status.'" where payment_id="'.$payment_id.'"');
    if($query){
      // echo "dd";
    //  $response['your_varriable']=$harsh;
      $response['reciptedata']=$this->order_invoice($order_id);
    if($payment_status==1){
     
          $response['status']=1;
        
       $response['msg'] ='Payment Successful';
      }
      else if($payment_status==0){
    //  echo "dff";
          $response['status']=0;
       
       $response['msg'] ='Payment Failed';
      }else{
     //   echo "jj";
         $response['status']=0;
       ///$response['reciptedata']=$this->order_invoice($order_id);
       $response['msg'] ='Card Response Error';
      }

    }else{
     // echo "mmm";
        $response['status']=0;
       $response['reciptedata']=array();
       $response['msg'] ='Something Wrong';
      }

    
    /*if($query){
      $response['status']=1;
       $response['reciptedata']=$this->order_invoice($order_id);
     $response['msg'] ='Payment Successfull';
    }*/
  }else{
    // $query=$this->db->query('update payment set status_id=3,message="'.$payment_msg.'" where payment_id="'.$payment_id.'"');
  ///echo "sssdddd";
      $response['status']=0;
     $response['msg'] ='Payment Failed';
    
  }
  echo json_encode($response);
}
public function deleteCustomerProduct(){
  $cart_id=$this->input->post('cart_id');
  if(!empty($cart_id)){
      $query=$this->db->query('update cart set is_delete=1 where cart_id="'.$cart_id.'"');
      if($query){
        $response['status']=0;
        $response['message'] = 'Product deleted successfully';
      }else{
        $response['status']=0;
       $response['message'] = 'Something wrong';
      }
  }else{
    $response['status']=0;
  $response['message'] = 'Required parameter missing';
  }
  echo json_encode($response);
}
public function order_invoice($order_id = ''){
  $data['order_data'] =$this->db->query('select o.order_id,o.customer_id,o.payment_id,o.coupon_id,o.order_number,o.order_amount,o.tax_amount,o.tip_amount,o.cash_amount,o.credit_card_amount,o.iou_amount,o.final_amount,o.vendor_id,o.rewards_money,o.diposite_amount,o.discount_amount,p.payment_id,p.transaction_id,IF(p.payment_type="1","Cash",IF(p.payment_type="2","Debit Card",IF(p.payment_type="3","Credit Card",IF(p.payment_type="4","Net Banking",IF(p.payment_type="5","EBS Payments","N.A"))))) payment_type,
                         IF(p.status_id="1","Pending",IF(p.status_id="2","Success",IF(p.status_id="3","Reject","N.A"))) payment_status,p.amount from orders as o inner join payment as p on o.payment_id=p.payment_id where order_id="'.$order_id.'"')->row();
  $data['customerInfo'] = $this->db->query('select concat(firstname," ",lastname) as customer_name,email,mobile_phone,home_phone from customer where customer_id IN ('. $data['order_data']->customer_id.')')->result();
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

             return $data;
       //  echo json_encode($data);
}
public function previewRecipte(){
        //echo "<pre>";print_r($_POST);exit;
       // echo "jddjh";die;
        $customer_id = $this->input->post('customer_id');
        $appointment_id = $this->input->post('appointment_id');
        $cart_id = $this->input->post('cart_id');
        $gift_certificate_id = $this->input->post('gift_certificate_id');
        $gift_card_id = $this->input->post('gift_card_id');
        $appointment_type = $this->input->post('appointment_type');
        $token_no = $this->input->post('token_no');
        $vendor_id = $this->input->post('vendor_id');
        $discount=$this->input->post('discount');
        $gift_card_id=$this->input->post('gift_card_id');
        
        $addtional_discount=$this->input->post('additional_discount');
         $search = $this->input->post('search');
       //  echo "<pre>";print_r($_POST);exit;
        if($appointment_id!=0 && $search!='search'){
            if($appointment_type==3){
                $where = "WHERE aps.is_addon=0 AND a.vendor_id='".$vendor_id."' and a.is_checkout='0' and a.appointment_id IN(".$appointment_id.") and a.appointment_type='3' and a.token_no='".$token_no."' ";
            }else if($appointment_type==2){
                $where = "WHERE aps.is_addon=0 AND a.vendor_id='".$vendor_id."' and a.is_checkout='0' and a.appointment_id IN(".$appointment_id.") and a.appointment_type='2' and a.token_no='".$token_no."' ";
            }
            else {
                $where = "WHERE aps.is_addon=0 AND a.vendor_id='".$vendor_id."' and a.is_checkout='0' and a.appointment_id IN(".$appointment_id.")   and a.appointment_type='1' and a.token_no='".$token_no."' ";
            }
        }
         if($search=='search' ){
              $where = " WHERE a.customer_id='".$customer_id."' and a.appointment_id IN(".$appointment_id.")  AND aps.is_addon=0 AND a.vendor_id='".$vendor_id."' and a.is_checkout='0' ";
          } 
          if($customer_id!=''){
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
                            CONCAT(st.firstname,' ',st.lastname) as stylist_name,
                            CONCAT(c.firstname,' ',c.lastname) as customer_name
                            from appointment_service aps
                            INNER JOIN appointment a
                            ON a.appointment_id=aps.appointment_id
                            INNER JOIN service s
                            ON s.service_id=aps.service_id
                            INNER JOIN stylist st
                            ON st.stylist_id=aps.stylist_id
                            INNER JOIN customer c
                            ON a.customer_id=c.customer_id
                            $where
                            ORDER BY aps.as_id DESC
                            
              ")->result();
         //  echo $this->db->last_query();exit;
             $selQuery = "select 
                        c.cart_id, 
                        c.quantity, 
                        c.customer_id, 
                        CONCAT(cs.firstname,' ',cs.lastname) as customer_name,
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
                        INNER JOIN customer cs 
                        ON cs.customer_id=c.customer_id                     
                        where 
                         c.cart_id IN(".$cart_id.") 
                        order by c.cart_id desc ";
                        // echo $this->db->last_query();exit;
         $result2= $this->db->query($selQuery)->result();

        $certificate= $this->db->query('select *  from gift_certificate where gift_id IN('.$gift_certificate_id.') and is_active=0')->result();
       $gift_card= $this->db->query('select * from gift_card where card_id IN('.$gift_card_id.') and is_active=0')->result();
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
          $response['message'] = 'Required parameter missing';      
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
            $response['message'] = "Order not created, Something went wrong";
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
         /* if($mail){
            echo "succ";
          }else{
            echo "fail";
          }*/
        }
      
 }

 public function deleteRow(){
  $table_name=$this->input->post('table_name');
  $id=$this->input->post('id');
  if(!empty($table_name) && !empty($id)){


  if($table_name=='customer'){
    $login_id=$this->db->query('select login_id from customer where customer_id IN('.$id.')')->result();
 ///  $query= $this->db->query('update customer set is_delete=1 where customer_id IN('.$id.')');
    foreach($login_id as $val_login){
      $query=$this->db->query('update login set is_delete=1 where login_id="'.$val_login->login_id.'"');
    }
    if($query){
      $response['status']=1;
      $response['message']="Delete customer Successfull";
    }else{
      $response['status']=0;
      $response['message']="Smething Wrong";
    }
  }
  if($table_name=='stylist'){
  $login_id=$this->db->query('select login_id from stylist where stylist_id IN('.$id.')')->result();
 ///  $query= $this->db->query('update customer set is_delete=1 where customer_id IN('.$id.')');
    foreach($login_id as $val_login){
      $query=$this->db->query('update login set is_delete=1 where login_id="'.$val_login->login_id.'"');
    }
    if($query){
      $response['status']=1;
      $response['message']="Delete stylist Successfull";
    }else{
      $response['status']=0;
      $response['message']="Smething Wrong";
    }
  }
  if($table_name=='product'){
   $query= $this->db->query('update product set is_delete=1 where product_id IN('.$id.')');
    if($query){
      $response['status']=1;
      $response['message']="Delete product Successfull";
    }else{
      $response['status']=0;
      $response['message']="Smething Wrong";
    }
  }
  if($table_name=='service'){
   $query= $this->db->query('update service set is_delete=1 where service_id IN('.$id.')');
    if($query){
      $response['status']=1;
      $response['message']="Service deleted successfully!";
    }else{
      $response['status']=0;
      $response['message']="Smething Wrong";
    }
  }
  if($table_name=='service_category'){
   $query= $this->db->query('update service_category set is_delete=1 where category_id IN('.$id.')');
    if($query){
      $response['status']=1;
      $response['message']="Service category deleted successfully!";
    }else{
      $response['status']=0;
      $response['message']="Smething Wrong";
    }
  }
  if($table_name=='category'){
   $query= $this->db->query('update category set is_delete=1 where category_id IN('.$id.')');
    if($query){
      $response['status']=1;
      $response['message']="Category deleted successfully!";
    }else{
      $response['status']=0;
      $response['message']="Smething Wrong";
    }
  }
  if($table_name=='brand'){
   $query= $this->db->query('update brand set is_delete=1 where brand_id IN('.$id.')');
    if($query){
      $response['status']=1;
      $response['message']="Brand deleted successfully!";
    }else{
      $response['status']=0;
      $response['message']="Smething Wrong";
    }
  }
}else{
   $response['status']=0;
   $response['message']="Required parameter missing";
 }
   echo json_encode($response);
}
function customerAppData(){
    
    $vendor_id=$this->input->post('vendor_id');
    $time = time();
    
    if(!empty($vendor_id)){
            $getData=$this->db->query('select "'.$time.'" as unique_id, a.appointment_id,a.token_no,c.customer_id,concat(c.firstname," ",c.lastname) as customer_name,c.mobile_phone,c.email,(CASE when a.appointment_type=1 THEN "single" WHEN a.appointment_type=2 THEN "Multiple" else "Group" end) as appointment_type,(CASE when a.appointment_from=1 THEN "webpos" WHEN a.appointment_from=2 THEN "posapp" WHEN a.appointment_from=3 THEN "custpmerapp"  else "Website" end) as appointment_from,(select group_concat(s.service_name) from  appointment_service as aps inner join service as s on aps.service_id=s.service_id where a.appointment_id=aps.appointment_id) as service_name,(select sum(s.price) from  appointment_service as aps inner join service as s on aps.service_id=s.service_id where a.appointment_id=aps.appointment_id) as service_price from appointment as a inner join customer as c on a.customer_id=c.customer_id where a.is_checkout=0 AND  a.vendor_id="'.$vendor_id.'" and a.appointment_from=3')->result();
            if(!empty($getData)){
                    $response['status']=1;
                    $response['getData']=$getData;
                    $response['message']="Succesfull";


            }else{
                     $response['status']=1;
                    $response['getData']=array();
                    $response['message']="No data found";
            }
    }else{
         $response['status']=0;
          $response['message']="Required parameter missing";
    }
      echo json_encode($response);

}
function getCardDetail(){
    $customer_id=$this->input->post('customer_id');
    $vendor_id=$this->input->post('vendor_id');
    if(!empty($vendor_id) && !empty($customer_id)){
            $getData=$this->db->query('select card_id,customer_id,cardholder_name,card_number,(case when CHAR_LENGTH(expiry_month)=1 then concat("0",expiry_month) else expiry_month end) as expiry_month,expiry_year,cvv,card_type,is_default from customer_card where customer_id="'.$customer_id.'" and is_default=1')->row();
            if(!empty($getData)){
                    $response['status']=1;
                    $response['getData']=$getData;
                    $response['message']="Succesfull";


            }else{
                     $response['status']=1;
                    $response['getData']=(Object)[];
                    $response['message']="No data found";
            }
    }else{
        $response['status']=0;
        $response['message']="Required parameter missing";
    }
    echo json_encode($response);
}

public function orderNowManualCardEntry(){
        
        $response['status']=0;
        $response['message']="";
        
        $vendor_id = $this->input->post('vendor_id');
        $customer_id = $this->input->post('customer_id');
     $appointment_id = $this->input->post('appointment_id'); 
     $token_no = $this->input->post('token_no'); 
     $payment_type = $this->input->post('payment_type');
     $payment_status=$this->input->post('payment_status');
   $payment_msg=$this->input->post('payment_msg');
     $cardholder_name = $this->input->post('cardholder_name');
     $card_number = $this->input->post('card_number');
     $exp_date = $this->input->post('exp_date');
     $exp_month = $this->input->post('exp_month');
     $exp_year = $this->input->post('exp_year');
     $card_type = $this->input->post('card_type');
     $entry_type = $this->input->post('entry_type');
     $terminal = $this->input->post('terminal');
     /*$aid = $this->input->post('aid');
     $tvr = $this->input->post('tvr');
     $iad = $this->input->post('iad');
     $tsi = $this->input->post('tsi');
     $arc = $this->input->post('arc');*/
     $card_id = $this->input->post('card_id');
     
    
   // $payment_status = 2; //success
    $status_id=2;
    $order_number = 'ORD' . date('mdYHis');
    $bank_txn_id = "";
    $response_code = "";
    $currency = "$";
    $message = "Payment Received";
    $transaction_id = 'TXNQ' . date('mdYHis');
  
        $response['status']=1;
        $response['message']="Payment processed successfully!";
        if($appointment_id!='' || $appointment_id!=null){
       // $appointment_data=
      $total_amount_data=$this->db->query('select sum(price) as total_amount from appointment_service where appointment_id IN ('.$appointment_id.')')->row();
      $query3 = $this->db->query("insert into orders set vendor_id='".$vendor_id."', customer_id='".$customer_id."',status_id='1', order_type='3', order_number='".$order_number."', order_amount='".$total_amount_data->total_amount."',tip_amount='0',discount_amount='0',iou_amount='0',credit_card_amount='".$total_amount_data->total_amount."',cash_amount='0',gift_cert_amount='0',gift_cart_amount='0',rewards_money='0',tax_amount='0',cuppon_value='0',is_active='1',is_delete='0', created_date='".date('Y-m-d h:i:s')."' ");
        $order_id = $this->db->insert_id();
        if($order_id){
        $query2 = $this->db->query("insert into payment set order_id='".$order_id."',payment_type='".$payment_type."', status_id='1',payment_status='".$payment_status."',transaction_id='".$transaction_id."', bank_txn_id='".$bank_txn_id."', response_code='".$response_code."', amount='".$total_amount_data->total_amount."', message='".$payment_msg."', created_date='".date('Y-m-d h:i:s')."', customer_id='".$customer_id."',vendor_id='".$vendor_id."' ");  
          $payment_id = $this->db->insert_id();
        }
        $appointment_data=$this->db->query('select stylist_id,service_id,price from appointment_service where appointment_id IN ('.$appointment_id.')')->result();
            for($i=0;$i<count($appointment_id);$i++){
          
              $stylist_ids = $appointment_data[$i]->stylist_id;
              $service_id[] = $appointment_data[$i]->service_id;
              $service_id_new=implode(",",$service_id);
              $service_data=$this->db->query('select tax_amount,price_after_tax,price from service where service_id IN ('.$service_id_new.')')->result();

              //echo "insert into order_detail set order_id='".$order_id."', product_id='".$appointment_id[$i]."',sale_type=1,stylist_id='".$stylist_ids."' ";die;
              $queryy = $this->db->query("insert into order_detail set order_id='".$order_id."', product_id='".$appointment_id[$i]."',tax_amount='".$service_data[$i]->tax_amount."',discount_amount='0',actual_amount='".$service_data[$i]->price."',total_paid_amount='".$appointment_data[$i]->price."',sale_type=1,stylist_id='".$stylist_ids."' ");
            }
          
             if($payment_status==1){
              $this->db->query('update payment set status_id=2 where payment_id="'.$payment_id.'"');
              for($i=0;$i<count($appointment_id);$i++){
               $this->db->query("update appointment set status='7', color_code='8', checkout_time='".date('Y-m-d h:i:s')."', is_checkout='1' where appointment_id IN(".$appointment_id.") ");
             }
                $card_data=$this->db->query('select cardholder_name,card_number,expiry_month, expiry_year,card_type from customer_card where card_id="'.$card_id.'"')->row();
                $exp=$card_data->expiry_month.$card_data->expiry_year;
             $this->db->query('insert into customer_card_detail set order_id="'.$order_id.'",card_holder_name="'.$card_data->cardholder_name.'",card_number="'.$card_data->card_number.'",exp_date="'.$exp.'", terminal="'.$terminal.'", aid="0", tvr="0", iad="0", tsi="0", arc="0", card_type="'.$card_type.'" ' );
             $response['message'] = 'Payment successfully'; 
              $response['status']=1;
              $this->mailRecipte_manual_card($order_id);
           }else{
            $this->db->query('update payment set status_id=3 where payment_id="'.$payment_id.'"');
            $this->db->query("update appointment set status='14', color_code='14', checkout_time='".date('Y-m-d h:i:s')."', is_checkout='1' where appointment_id IN(".$appointment_id.") ");
            $query=$this->db->query('update orders set status_id=4,message="'.$payment_msg.'" where order_id="'.$order_id.'"');
            $response['message'] = 'Payment failed'; 
            $response['status']=0;
           }

      }else{
        $response['message'] = 'Required parameter missing'; 
        $response['status']=0;
      }
        echo json_encode($response);
    }
  function mailRecipte_manual_card($order_id = ''){
    $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $image_url = $actual_link.'/salon/new/assets/img/stylist/thumb/';
     $data['editData']=$this->db->query("SELECT
                          
                          IF(CONCAT(c.firstname,' ',c.lastname) IS NULL,'N/A',CONCAT(c.firstname,' ',c.lastname)) customer_name,c.email,c.mobile_phone,
                          COUNT(od.product_id) total_product,
                         IF(p.payment_type='1','Cash',IF(p.payment_type='2','Debit Card',IF(p.payment_type='3','Credit Card',IF(p.payment_type='4','Net Banking',IF(p.payment_type='5','EBS Payments','N.A'))))) payment_type,

                         IF(p.status_id='1','Pending',IF(p.status_id='2','Success',IF(p.status_id='3','fail','N.A'))) payment_status,
                          p.transaction_id,
                          p.response_code,
                          p.amount payment_amount,
                          p.message payment_message,
                          date_format(p.created_date,'%d %M %Y') as payment_date,
                          o.status_id order_status,
                          o.order_type,
                             o.order_number,
                          o.order_amount,
                          o.tax_amount,
                          IF(o.is_active = 1, 'Active', 'Inactive') AS is_active,
                          date_format(o.created_date,'%d %M %Y') as order_date
                        FROM orders AS o
                         LEFT JOIN order_detail od
                            ON od.order_id = o.order_id
                        LEFT JOIN login l
                            ON l.login_id = o.customer_id
                          LEFT JOIN customer c
                            ON c.customer_id = o.customer_id
                          LEFT JOIN payment p
                            ON p.order_id = o.order_id
                          
                        WHERE o.is_delete = 0 AND p.order_id IS NOT NULL AND o.order_id='".$order_id."' ")->row();

        $data['appointmentData']=$this->db->query("select
                                    a.appointment_id,
                                    date_format(a.date,'%d %M %Y') as app_date,
                                    time_format(aps.appointment_time,'%h:%i %p') as apt_time,
                                    aps.service_id, aps.stylist_id,
                                    s.service_name,
                                    CONCAT(st.firstname,' ',st.lastname) as stylist_name,
                                    CONCAT('$image_url','/',st.photo) as stylist_image,
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
          if($order_id !=''){
          $subject = 'Order Successfull';
          $htmlData=$this->load->view('email_template/order_manual',$data,true);
          $headers = "From: info@booknpay.com\r\n";
          $headers .= "Reply-To:  info@booknpay.com\r\n";
          $headers .= "MIME-Version: 1.0\r\n";
          $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
              $email= $data['editData']->email;
              $message_email = $htmlData;
              
          $mail=mail($emai, $subject, $message_email, $headers);
         /* if($mail){
            echo "succ";
          }else{
            echo "fail";
          }*/
        }
   
  }
  public function voidStatus(){
    $order_id=$this->input->post('order_id');
    if(!empty($order_id)){
      $this->db->query('update orders set status_id=3 where order_id="'.$order_id.'"');
      $response['message'] = 'Cancel order'; 
        $response['status']=1;
    }else{
      $response['message'] = 'Required parameter missing'; 
        $response['status']=0;
    }
    echo json_encode($response);
  }
  public function updateCartQunatity(){
    $cart_id=$this->input->post('cart_id');
    $quantity=$this->input->post('quantity');
      if($cart_id !='' && $quantity !=''){
        $quantity_new=$this->db->query('select p.quantity from cart as c inner join product as p on c.product_id=p.product_id where cart_id="'.$cart_id.'"')->row();
       // echo $quantity_new->quantity ;
        if($quantity_new->quantity >=$quantity){
         // echo 'update cart set quantity="'.$quantity.'" where cart_id="'.$cart_id.'"';die;
           $query=   $this->db->query('update cart set quantity="'.$quantity.'" where cart_id="'.$cart_id.'"');
         if($query){
        $response['message'] = 'Quantity updated successfully'; 
         $response['status']=1;
       }else{
        $response['message'] = 'Something went wrong'; 
         $response['status']=0;
       }
        }else{
           $response['message'] = 'Quantity not available'; 
         $response['status']=0;
        }

        
      }else{
         $response['message'] = 'Required parameter missing'; 
         $response['status']=0;
      }
      echo json_encode($response);
    } 
    function removeReason(){
      
      $reason_id = $_POST['reason_id'];
       $query = $this->db->query("delete from certificate_reason where reason_id='".$reason_id."' ");
       if($query){
           $response['message']= 'Reason deleted successfully';
           $response['status']=1;
       }else{
           $response['message']= 'Failed';
           $response['status']=0;
       }
        echo json_encode($response);
  }
  public function stylistTipAmount(){
    if(!empty($_POST['tipVal'])){
        if(@$_POST['checkout']=='checkout'){
            $status=0;
        }else{
        $status=1;  
        }
        //echo "<pre>";print_r($_POST);exit;
        foreach($_POST['tipVal'] as $key=>$val){
            if($_POST['editTip'][0]==''){
                ///echo "djhd";die;
        $insertdata=array(
                'stylist_id'=>$_POST['stylist_id'][$key],
                'appointment_id'=>$_POST['appointment_id'][$key],
                'vendor_id'=>$_POST['vendor_id'],
                'tip_amount'=>$_POST['tipVal'][$key],
                'status'=>$status

        );
        
        $insert=$this->db->insert('stylist_tip_amount',$insertdata);
        }else{
            //echo "k";die;
         $insert=$this->db->query("update stylist_tip_amount set tip_amount='".$_POST['tipVal'][$key]."' where id='".$_POST['editTip'][$key]."' ");
        }
    }
        if($insert){
            $response['message'] = 'Tip distributed successfully'; 
         $response['status']=1;
        }else{
            $response['message'] = 'Something went wrong'; 
         $response['status']=0;
        }
        //echo base_url().'administrator/orders';
    }else{
         $response['message'] = 'Required parameter missing'; 
         $response['status']=0;
      }
      echo json_encode($response);
}
public function removeDataFromCart(){
    $customer_id=$this->input->post('customer_id');
     if(!empty($customer_id)){
        $query=$this->db->query('delete from cart where customer_id="'.$customer_id.'"');
        $query1=$this->db->query('delete from gift_certificate where customer_id="'.$customer_id.'"');
        $query2=$this->db->query('delete from gift_card where customer_id="'.$customer_id.'"');
        if($query && $query1 && $query2){
            $response['message'] = 'Checkout empty successfully'; 
         $response['status']=1;
     }else{
        $response['message'] = 'Something went wrong'; 
         $response['status']=0;
     }
     }
    else{
         $response['message'] = 'Required parameter missing'; 
         $response['status']=0;
      }
      echo json_encode($response);
}
public function getProduct(){
        
        $vendor_id = $this->input->post('vendor_id');
        $supplier_id = $this->input->post('supplier_id');
        
        $response['status'] = 0;
        $response['message'] = '';
            
        if(!empty($vendor_id)){
            
            if(!empty($supplier_id)){
                $and = " AND p.supplier_id=$supplier_id";
            }else{
                $and = "";
            }
            
        $path = "http://159.203.182.165/salon/assets/img/product/thumb";
        $selQuery = "select p.product_id, p.product_name, CONCAT('$path','/',p.main_image) as product_image, p.barcode_id, p.description,p.price_retail,p.quantity, p.supplier_id, c.category_name,p.sku, p.supplier_code as vendor_code,p.qty_in_pack, b.brand_name,p.created_date,p.tax_rate,p.vendor_id,p.max_quantity from product p INNER JOIN category c ON c.category_id=p.category_id INNER JOIN brand b on b.brand_id=p.brand_id where p.is_active='1' and p.is_delete='0' and business_use=0 and p.vendor_id='".$vendor_id."'  $and order by product_id DESC";
        
                    
         $result = $this->db->query($selQuery)->result();
         
         if(!empty($result)){
             $response['status'] = 1;
         $response['result'] = $result;
         $response['message']='Success';
        }else{
             $response['status'] = 1;
         $response['result'] = array();
         $response['message']='No data found';
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
}
?>