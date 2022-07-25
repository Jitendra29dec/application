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


class Settings extends CI_Controller {

    function __construct()
    {
        parent::__construct();
     //   $this->load->model('coupon/coupon_model', 'coupon');
        $this->main_image_path = "assets/img/offer";
        $this->main_image_thumb_path = "assets/img/offer/thumb";
    }
	
	function tv(){
	$vendor_id = $this->input->post('vendor_id');
		if(!empty($vendor_id)){
			
			
			$query = $this->db->query("select screen_id, wallpaper, video_url, video_time_interval from tv_screen where and is_active='1' and vendor_id='".$vendor_id."' ");
			$result = $query->row();
			
			if($query->num_rows()>0){
			
			 $interval = ($result->video_time_interval*1000*60);
			 //$interval = $result->video_time_interval*1000;
			 $vid = explode('=',$result->video_url);
			 $video_id = $vid[1];
			 $res['wallpaper'] = $result->wallpaper;
			 $res['video_url'] = $video_id;
			 $res['video_time_interval'] = $interval;
			$response['status'] = 1;
			$response['result'] = $res;
			$response['message'] = '';
			
			}else{
				$response['status'] = 0;
				$response['result'] = [];
				$response['message'] = 'Something went wrong!';
				
			}
			}else{
				$response['status'] = 0;
				$response['message'] = 'Required parameter missing!';
			}
		
		echo json_encode($response);
	}
	function getCupponList(){
		//echo "test";die;
	$vendor_id = $this->input->post('vendor_id');
	//echo "harsh".$vendor_id ;die;
		if(!empty($vendor_id)){
			
			
			//$query = $this->db->query("select * from coupon2  where vendor_id='".$vendor_id."' order by coupon_id desc ");
			$query = $this->db->query("select coupon_id, coupon_number as coupon_code, IF(discount_for=1,'Product','Service') as coupon_for, IF(coupon_type=1,'Flat','Percentage') as discount_type, discount, start_date, end_date, IF(is_active=1,'Active','Inactive') as status from coupon2  where vendor_id='".$vendor_id."' order by coupon_id desc ");
					$result = $query->result();
					$response['status'] = 1;
					$response['result'] = $result;
					$response['message'] = 'succ';
			}else{
					$response['status'] = 0;
						$response['result'] =array();
				    $response['message'] = 'Required parameter missing!';
			}
		
		  echo json_encode($response);
	}
	 /*function getCouponById($coupon_id){
	 	$coupon_id = $this->input->post('coupon_id');
	 	if(!empty($coupon_id)){
        $selQuery = "SELECT
                      *
                    FROM coupon AS c
                    WHERE c.is_delete = 0
					AND c.coupon_id='".$coupon_id."'";
        $result = $this->db->query($selQuery)->row();
        			$response['status'] = 1;
					$response['result'] = $result;
					$response['message'] = 'succ';
    }else{
    				$response['status'] = 0;
    				$response['result'] = array();
				    $response['message'] = 'Required parameter missing!';
        
    }
     echo json_encode($response);
    }*/
	function add_cuppon()
    {
    	$coupon_for = $this->input->post("coupon_for");
        $coupon_type = $this->input->post("coupon_type");
        $amount = $this->input->post("amount");
        $percent = $this->input->post("percent");
        $valid_from = $this->input->post("valid_from");
        $valid_till = $this->input->post("valid_till");
        $description = $this->input->post("description");
        $coupon_code = $this->input->post("coupon_code");
        $custom_coupon_code = $this->input->post("custom_coupon_code");
        $start_time = $this->input->post("start_time");
        $end_time = $this->input->post("end_time");
        $vendor_id = $this->input->post("vendor_id");
        $template_id = $this->input->post("template_id");
         if($custom_coupon_code!='' || !empty($custom_coupon_code)){
		   
		   $final_coupon_code = $custom_coupon_code;
	   }else{
		   $final_coupon_code = $coupon_code;
	   }
	   
	   if($coupon_type==1){
		   
		   $amount = $amount;
	   }else{
		   
		   $amount = $percent;
	   }
	   if(!empty($custom_coupon_code) && !empty($amount)){
	   $valid_from = date('Y-m-d',strtotime($valid_from));
	   $valid_till = date('Y-m-d',strtotime($valid_till));
	   $insertCoupon = $this->db->query("insert into coupon2 set coupon_number='".$final_coupon_code."', discount_for='".$coupon_for."', coupon_type='".$coupon_type."',discount='".$amount."', start_date='".$valid_from."', end_date='".$valid_till."', description='".$description."', is_active='1', vendor_id='".$vendor_id."', start_time='".$start_time."', end_time='".$end_time."', template_id='".$template_id."' ");
	   $insert_id = $this->db->insert_id();
	    if ($insert_id) {
           $response['status'] = 1;
			$response['message'] ="succ";
        }
        else {
            	$response['status'] = 0;
				$response['message'] = 'Something wrong';

        }
    }else{
    	$response['status'] = 0;
	    $response['message'] = 'Required parameter missing!';
    }
       echo json_encode($response);
    }
	
	
    function getCouponById(){
		
		 $coupon_id=$this->input->post('coupon_id');
		 if(!empty($coupon_id)){
			 
		 	//$query = $this->db->query("select * from coupon2 where coupon_id='".$coupon_id."' ");
			$query = $this->db->query("select coupon_id, coupon_number as coupon_code, discount_for as coupon_for, coupon_type, discount, start_date, end_date,start_time,end_time,template_id, IF(is_active=1,'Active','Inactive') as status, description from coupon2  where coupon_id='".$coupon_id."'  ");
		$result = $query->row();
		$response['status'] = 1;
		$response['result'] = $result;
		$response['message']='succ';
		 }else{
		 	$response['status'] = 0;
		 	$response['result'] = array();
	      $response['message'] = 'Required parameter missing!';
		 }
		
		  echo json_encode($response);
	}
    function edit_cuppon(){
    	 $editId=$this->input->post('editId');
    	  $coupon_for = $this->input->post("coupon_for");
        $coupon_type = $this->input->post("coupon_type");
        $amount = $this->input->post("amount");
        $percent = $this->input->post("percent");
        $valid_from = $this->input->post("valid_from");
        $valid_till = $this->input->post("valid_till");
        $description = $this->input->post("description");
        $coupon_code = $this->input->post("coupon_code");
        $custom_coupon_code = $this->input->post("custom_coupon_code");
        $start_time = $this->input->post("start_time");
        $end_time = $this->input->post("end_time");
        $template_id = $this->input->post("template_id");
       
	   if($custom_coupon_code!='' || !empty($custom_coupon_code)){
		   
		   $final_coupon_code = $custom_coupon_code;
	   }else{
		   $final_coupon_code = $coupon_code;
	   }
	   
	   if($coupon_type==1){
		   
		   $amount = $amount;
	   }else{
		   
		   $amount = $percent;
	   }
	   
	   $valid_from = date('Y-m-d',strtotime($valid_from));
	   $valid_till = date('Y-m-d',strtotime($valid_till));
	   
	   $vendor_id = $this->session->userdata('vendor_id');
	   if(!empty($editId)){
	   $insertCoupon = $this->db->query("update coupon2 set coupon_number='".$final_coupon_code."', discount_for='".$coupon_for."', coupon_type='".$coupon_type."',discount='".$amount."', start_date='".$valid_from."', end_date='".$valid_till."', description='".$description."', is_active='1', start_time='".$start_time."', end_time='".$end_time."', template_id='".$template_id."' where coupon_id='".$editId."' ");

	   if ($insertCoupon) {
           $response['status'] = 1;
		   $response['message'] ="update succ";
        }
        else {
            	$response['status'] = 0;
				$response['message'] = 'Something wrong';

        }
	}else{
		$response['status'] = 0;
	    $response['message'] = 'Required parameter missing!';
	}
	
	echo json_encode($response);
	  
    }
	function getDiscount(){
	$vendor_id = $this->input->post('vendor_id');
		if(!empty($vendor_id)){
			$query = $this->db->query("SELECT
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
					  AND c.vendor_id='".$vendor_id."'");
		    	$result = $query->result();
			    $response['status'] = 1;
			    $response['result'] = $result;
			    $response['message']="succ";
			}else{
				$response['status'] = 0;
				$response['result'] = array();
				$response['message'] = 'Required parameter missing!';
			}
		
		echo json_encode($response);
	}
	
	function addDiscount(){
		
		$response['status'] = 0;
		$response['message'] = '';
				
	$vendor_id = $this->input->post('vendor_id');
	$discount_for = $this->input->post('discount_for');
	$discount_type = $this->input->post('discount_type');
	$discount = $this->input->post('discount');
	$start_date = $this->input->post('start_date');
	$end_date = $this->input->post('end_date');
	$min_amount = $this->input->post('min_amount');
	$description = $this->input->post('description');
	$photo = $this->input->post('image');
	
		if(!empty($vendor_id) && !empty($discount_for)  && !empty($discount_type)  && !empty($discount)  && !empty($start_date) && !empty($end_date)){
			
			if(!empty($photo)){
			$path = '../assets/img/discount/thumb/';
			$file = time().'.jpeg';
			$photo = $this->base64_to_jpeg($photo,$path.$file);
			$photo_name = $file;
		
		}else{
			
			$photo_name = 'avtar.png';
		}
		
			$query = $this->db->query("insert into coupon set coupon_type='".$discount_type."', discount_for='".$discount_for."', discount='".$discount."', start_date='".$start_date."', end_date='".$end_date."', min_amount='".$min_amount."', description='".$description."', main_image='".$photo_name."', is_active='1', is_delete='0', created_date='".date('Y-m-d')."', vendor_id='".$vendor_id."' ");
			if($query){
				$response['status'] = 1;
				$response['message']="Discount added successfully!";
			}else{
				$response['status'] = 0;
				$response['message'] = 'Something wrong in query!';
			}
			}else{
				$response['status'] = 0;
				$response['message'] = 'Required parameter missing!';
			}
		
		echo json_encode($response);
	}
	
	public function getDiscountData($discount_id){
		
		$qry = $this->db->query("select * from coupon where coupon_id='".$discount_id."' ");
		$res = $qry->row();
		return $res;
		
	}
	
	public function getDiscountById(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$discount_id = $this->input->post('discount_id');
		if(!empty($discount_id)){
			$qry = $this->db->query("select * from coupon where coupon_id='".$discount_id."' ");
			$res = $qry->row();
		
			$response['status'] = 1;
			$response['result'] = $res;
			$response['message'] = '';
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
		
	}
	
	function editDiscount(){
		
		$response['status'] = 0;
		$response['message'] = '';
				
	$discount_id = $this->input->post('discount_id');
	$vendor_id = $this->input->post('vendor_id');
	$discount_for = $this->input->post('discount_for');
	$discount_type = $this->input->post('discount_type');
	$discount = $this->input->post('discount');
	$start_date = $this->input->post('start_date');
	$end_date = $this->input->post('end_date');
	$min_amount = $this->input->post('min_amount');
	$description = $this->input->post('description');
	$photo = $this->input->post('image');
	
		if(!empty($discount_id) && !empty($vendor_id) ){
			
		if(!empty($photo)){echo 'a';die;
			$path = '../assets/img/discount/thumb/';
			$file = time().'.jpeg';
			$photo = $this->base64_to_jpeg($photo,$path.$file);
			$photo_name = $file;
		
		}/*else{
			
			$editData = $this->getDiscountData($discount_id);
			
			$photo_name = $editData->main_image;
		}*/
		

		
			$query = $this->db->query("update coupon set coupon_type='".$discount_type."', discount_for='".$discount_for."', discount='".$discount."', start_date='".$start_date."', end_date='".$end_date."', min_amount='".$min_amount."', description='".$description."', main_image='".$photo_name."',  modified_date='".date('Y-m-d')."' where  coupon_id='".$discount_id."' ");
		    if($query){
			    $response['status'] = 1;
			    $response['message']="Discount updated successfully!";
			}else{
				$response['status'] = 0;
				$response['message'] = 'Something went wrong in query!';
			}
			}else{
				$response['status'] = 0;
				$response['message'] = 'Required parameter missing!';
			}
		
		echo json_encode($response);
	}
	
	public function getEmployee(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select s.stylist_id, CONCAT(s.firstname,' ',s.lastname) as stylist_name from stylist s inner join login l on l.login_id=s.login_id where l.vendor_id='".$vendor_id."' ");
			if($query->num_rows()>0){
				$result = $query->result();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}else{
				$response['status'] = 0;
				$response['message'] = 'No Employee Found!';
			}
		}else{
				$response['status'] = 0;
				$response['message'] = 'Required parameter missing!';
			}
		
		echo json_encode($response);
	}
	
	public function getModule(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select module_id, module_name from module where is_active=1 order by module_id asc ");
			if($query->num_rows()>0){
				$result = $query->result();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}else{
				$response['status'] = 0;
				$response['message'] = 'No Module Found!';
			}
		}else{
				$response['status'] = 0;
				$response['message'] = 'Required parameter missing!';
			}
		
		echo json_encode($response);
	}
	
	public function getBusinessProfile(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
			//$path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/client/';
			$path = "https://".$_SERVER['HTTP_HOST'].'/assets/img/client';
			$data=$this->db->query("select A.vendor_name as store_name,A.owner_name as name,A.phone,l.email,A.alternate_phone,A.address,A.city,st.name as state_name,A.description, CONCAT('$path','/',if(A.photo='','noimage.png',A.photo)) as photo from vendor as A inner join login as l on l.login_id=A.login_id inner join states as st on st.id=A.state_id where A.vendor_id='".$vendor_id."' ")->row();
			$response['status'] = 1;
				$response['result'] = $data;
				
				$response['message'] = 'data found';
		}else{
			$response['status'] = 0;
				$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	public function getBusinessHour(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("SELECT
                     bh.business_hour_id,bh.days,bh.switch,time_format(bh.start_time,'%h:%i %p') as start_time,time_format(bh.end_time,'%h:%i %p') as end_time,bh.vendor_id,bh.min_type,time_format(bh.start_time_12,'%h:%i %p') as start_time_12,time_format(bh.end_time_12,'%h:%i %p') as end_time_12
                    FROM business_hour AS bh
					where bh.vendor_id='".$vendor_id."'
					ORDER BY bh.business_hour_id ASC
					");
			
			if($query->num_rows()>0){
				$result = $query->result();
				$week_start_date=$this->db->query("select value as day from settings where field='week_start_day' and vendor_id='".$vendor_id."' ")->row();
				$getLunchtime=$query = $this->db->query("select time_format(first_time,'%h:%i %p') as lunch_start_time, time_format(second_time,'%h:%i %p') as lunch_end_time from settings where field='lunch_time' and vendor_id='".$vendor_id."' ")->row();
				$calender_start_time=$this->db->query("select settings_id,time_format(first_time,'%h:%i %p') as erliest_open,time_format(second_time,'%h:%i %p') as latest_closed from settings where field='calendar_start_end_time' and vendor_id='".$vendor_id."'  ")->row();
				if(!empty($week_start_date)){
					$week_start_date=$week_start_date;
				}else{
					$week_start_date=(Object)[];
				}
				if(!empty($getLunchtime)){
					$getLunchtime=$getLunchtime;
				}else{
					$getLunchtime=(Object)[];
				}
				if(!empty($calender_start_time)){
					$calender_start_time=$calender_start_time;
				}else{
					$calender_start_time=(Object)[];
				}
				
				$allow_apt = $this->db->query("select value as allow_apt from settings where field='allow_apt_outside_business_hour' and vendor_id='".$vendor_id."'  ")->row();
				if(!empty($allow_apt)){
					$allow_apt=$allow_apt;
				}else{
					$allow_apt=(Object)[];
				}
				
				$response['status'] = 1;
				$response['result'] = $result;
				$response['week_start_date']=$week_start_date;
				$response['getLunchtime']=$getLunchtime;
				$response['calender_start_time']=$calender_start_time;
				$response['allow_apt']=$allow_apt;
				$response['message'] = 'Data found';
			}else{
				$response['status'] = 0;
				$response['message'] = 'No data found';
			}
		}else{
				$response['status'] = 0;
				$response['message'] = 'Required parameter missing!';
			}
		
		echo json_encode($response);
	}
	
	
	
	public function updateBusinessHour(){
	//	echo "<pre>";print_r($_POST);exit;
		$response['status'] = 1;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$days = $this->input->post('days');
		
		$days = json_decode($days);
		
		
		if(!empty($vendor_id)){
				
		//	$query = $this->db->query("delete from business_hour where vendor_id='".$vendor_id."' ");
			
			for($i=0;$i<count($days);$i++){
			
				if($days[$i]->switch==1){
					$switch = 1;
				}else{
					$switch = 0;
				}
				
				
				//if($query){
				$start_time  = date("H:i", strtotime($days[$i]->from));
				$end_time  = date("H:i", strtotime($days[$i]->to));
					$qry = $this->db->query("update business_hour set days='".trim($days[$i]->day)."', switch='".trim($switch)."', start_time='".$start_time."', end_time='".$end_time."' where vendor_id='".$vendor_id."' AND days='".$days[$i]."' "); 
				//}
			
			}
		
		
				$response['status'] = 1;
				$response['message'] = 'Business hour updated successfully!';
		
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	public function getPhotoGallery(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		if(!empty($vendor_id)){
			
			$path = "http://159.203.182.165/salon/assets/img/gallery/thumb/";
			
			$query = $this->db->query("select galary_id as gallery_id, CONCAT('$path',main_image) as photo from gallery where vendor_id='".$vendor_id."' order by gallery_id desc ");
			if($query->num_rows()>0){
				
				$result = $query->result();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	public function addPhotoGallery(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$photo = $_FILES['photo']['name'];
		$tmp_photo = $_FILES['photo']['tmp_name'];
		
		$path = $_SERVER['DOCUMENT_ROOT'] . '/salon/assets/img/gallery/thumb/';
		
		if(!empty($vendor_id)){
		if(!empty($photo)){
			
			$file = time().$photo;
			$photo = move_uploaded_file($tmp_photo,$path.$file);
			$photo_name = $file;
			
			
		}else{
			$photo_name = 'avtar.png';
			
		}
		
			
			$query = $this->db->query("insert into gallery set main_image='".$photo_name."', vendor_id='".$vendor_id."' ");
			if($query){
				
			
				$response['status'] = 1;
				$response['message'] = 'Photo added successfully';
			}else{
				$response['status'] = 0;
				$response['message'] = 'Something went wrong!';
				
			}
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	
	
	public function getScreenLockTime(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select value as lock_time from settings where field='screen_lock_time' and vendor_id='".$vendor_id."' ");
			if($query->num_rows()>0){
				
				$result = $query->row();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}else{
				$response['status'] = 0;
				$response['result'] = [];
				$response['message'] = 'No data found!';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	public function updateScreenLockTime(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$lock_time = $this->input->post('lock_time');
		
		if(!empty($vendor_id) && !empty($lock_time)){
			
			$query = $this->db->query("update settings set value='".$lock_time."' where field='screen_lock_time' and vendor_id='".$vendor_id."' ");
			if($query){
			
				$response['status'] = 1;
				$response['message'] = 'Screen lock time updated successfully!';
			}else{
				$response['status'] = 0;
				$response['message'] = 'Something went wrong';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	
	public function getCalendarColorSettings(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select color_id, name as status_name, color_type, color_code, text_color from color_settings where vendor_id='".$vendor_id."' and is_active='1' ");
			if($query->num_rows()>0){
				
				$result = $query->result();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}else{
				$response['status'] = 0;
				$response['message'] = 'Something went wrong';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	public function updateCalendarColorSettings(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$color_id = $this->input->post('color_id');
		$color_code = $this->input->post('color_code');
		$text_color = $this->input->post('text_color');
		
		if(!empty($vendor_id) && !empty($color_id)){
			
			$query = $this->db->query("update color_settings set color_code='".$color_code."', text_color='".$text_color."' where vendor_id='".$vendor_id."' and color_id='".$color_id."' ");
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Color setting updated!';
			}else{
				$response['status'] = 0;
				$response['message'] = 'Something went wrong';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	public function updateIOUsettings(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$amount = $this->input->post('amount');
		
		if(!empty($vendor_id) && !empty($amount)){
			
			$query = $this->db->query("update iou_settings set max_amount='".$amount."' where vendor_id='".$vendor_id."' ");
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'IOU setting updated!';
			}else{
				$response['status'] = 0;
				$response['message'] = 'Something went wrong';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	public function getIOUamount(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = (Object)[];
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select max_amount as amount from iou_settings where vendor_id='".$vendor_id."' ");
			if($query->num_rows()>0){
				
				$result = $query->row();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}else{
				$response['status'] = 0;
				$response['result'] = $object;
				$response['message'] = 'Something went wrong';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	
	public function updateGiftCertificateAmount(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$amount = $this->input->post('amount');
		
		if(!empty($vendor_id) && !empty($amount)){
			
			$query = $this->db->query("update gift_settings set max_amount='".$amount."' where vendor_id='".$vendor_id."' ");
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Gift certificate setting updated!';
			}else{
				$response['status'] = 0;
				$response['message'] = 'Something went wrong';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	public function getGiftCertificateAmount(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = (Object)[];
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select max_amount as amount from gift_settings where vendor_id='".$vendor_id."' ");
			if($query->num_rows()>0){
				
				$result = $query->row();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}else{
				$response['status'] = 0;
				$response['result'] = $object;
				$response['message'] = 'Something went wrong';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	
	public function getTVscreen(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = (Object)[];
		$vendor_id = $this->input->post('vendor_id');
		
		$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
		$tvscreen = $actual_link.'/salon/assets/img/tv/';
		if(!empty($vendor_id)){
			
			
			$query = $this->db->query("select screen_id, CONCAT('$tvscreen',wallpaper) AS wallpaper, video_url, video_time_interval from tv_screen where vendor_id='".$vendor_id."' ");
			if($query->num_rows()>0){
				
				$result = $query->row();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}else{
				$response['status'] = 0;
				$response['result'] = $object;
				$response['message'] = 'No data found!';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	
	public function updateTVscreen(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		
		$video_url = $this->input->post('video_url');
		$video_time_interval = $this->input->post('video_time_interval');
		
		$updated_date = date('Y-m-d h:i:s');
		
		if(!empty($vendor_id) && !empty($video_url)){
		
		
		$photo = $_FILES['wallpaper']['name'];
		$tmp_photo = $_FILES['wallpaper']['tmp_name'];
		
		$path = $_SERVER['DOCUMENT_ROOT'] . '/salon/assets/img/tv/';
		
		
		if(!empty($photo)){
			
			$file = time().$photo;
			$photo = move_uploaded_file($tmp_photo,$path.$file);
			$photo_name = $file;
			
			
		}else{
			$photo_name = 'avtar.png';
			
		}
		
			$query = $this->db->query("update tv_screen set wallpaper='".$photo_name."', video_url='".$video_url."', video_time_interval='".$video_time_interval."', updated_date='".$updated_date."' where vendor_id='".$vendor_id."' ");
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'TV Screen updated!';
			}else{
				$response['status'] = 0;
				$response['message'] = 'Something went wrong';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	public function getTaxDetail(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = '{}';
		$object_array = '[]';
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select tax_id, tax_type, tax_rate from tax_service where vendor_id='".$vendor_id."'  limit 0,3");
			if($query->num_rows()>0){
				
				$query2 = $this->db->query("select tax_id, tax_type, tax_rate from tax_product where vendor_id='".$vendor_id."'  limit 0,3");
				
				$result2 = $query2->result();
				
				$result = $query->result();
				$response['status'] = 1;
				$response['service'] = $result;
				$response['product'] = $result2;
				$response['message'] = '';
			}else{
				$response['status'] = 0;
				$response['service'] = $object;
				$response['product'] = $object;
				$response['message'] = 'Something went wrong';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['service'] = $object;
			$response['product'] = $object;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	public function updateTax(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$service_tax = $this->input->post('service_tax');
		$product_tax = $this->input->post('product_tax');
		
		$service_tax = json_decode($service_tax);
		$product_tax = json_decode($product_tax);
		
		$created_date = date('Y-m-d h:i:s');
		
		if(!empty($vendor_id) && !empty($product_tax) && !empty($service_tax)){
			
					
					$query = $this->db->query("insert into tax_service set tax1='".$service_tax->tax1."', tax2='".$service_tax->tax2."', tax3='".$service_tax->tax3."',tax1_startdate='".$service_tax->tax1_startdate."',tax2_startdate='".$service_tax->tax2_startdate."',tax3_startdate='".$service_tax->tax3_startdate."', tax1_description='".$service_tax->tax1_description."',tax2_description='".$service_tax->tax2_description."',tax3_description='".$service_tax->tax3_description."', created_date='".$created_date."', vendor_id='".$vendor_id."' ");
					
					$query2 = $this->db->query("insert into tax_product set tax1='".$product_tax->tax1."', tax2='".$product_tax->tax2."', tax3='".$product_tax->tax3."',tax1_startdate='".$product_tax->tax1_startdate."',tax2_startdate='".$product_tax->tax2_startdate."',tax3_startdate='".$product_tax->tax3_startdate."', tax1_description='".$product_tax->tax1_description."',tax2_description='".$product_tax->tax2_description."',tax3_description='".$product_tax->tax3_description."', created_date='".$created_date."', vendor_id='".$vendor_id."' ");
				
			
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Tax updated successfully!';
			}else{
				$response['status'] = 0;
				$response['message'] = 'Something went wrong';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	
	public function getFeaturesList(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = '{}';
		$object_array = [];
		$vendor_id = $this->input->post('vendor_id');
		
		/*if(!empty($vendor_id)){*/
			
			$query = $this->db->query("select id as permission_id, permission from permission where status='1' order by sort asc ");
			if($query->num_rows()>0){
				
				$result = $query->result();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}else{
				$response['status'] = 0;
				$response['result'] = $object_array;
				$response['message'] = 'Something went wrong';
				
			}
			
		/*}else{
			
			$response['status'] = 0;
			$response['result'] = $object_array;
			$response['message'] = 'Required parameter missing!';
		}*/
		echo json_encode($response);
	}
	
	public function getPermission(){
		
		$response['status'] = 0;
		$response['message'] = '';
		//$object = {};
		$object_array = [];
		$vendor_id = $this->input->post('vendor_id');
		$role_id = $this->input->post('role_id');
		
		
		if(!empty($role_id)){
			
			$query = $this->db->query("select role_id,permission_id from stylist_permission where role_id='".$role_id."' and vendor_id='".$vendor_id."' ");
			if($query->num_rows()>0){
				
				$result = $query->result();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}else{
				$response['status'] = 0;
				$response['result'] = $object_array;
				$response['message'] = 'No data found!';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['result'] = $object_array;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}

	
	public function updatePermission(){
		//echo "<pre>";print_r($_POST);exit;
		$response['status'] = 0;
		$response['message'] = '';
		$object = '{}';
		$object_array = '[]';
		$vendor_id = $this->input->post('vendor_id');
		$stylist_id = $this->input->post('stylist_id');
		$role_id = $this->input->post('role_id');
		//echo "<pre>";print_r($_POST);exit;
		$permission_id = explode(",",$this->input->post('permission_id'));
		
		//echo ""
		//$data = json_decode($days);
		
		if(!empty($role_id)){ 
			
			
			$q = $this->db->query("delete from stylist_permission where role_id='".$role_id."' and vendor_id='".$vendor_id."'");
			if(!empty($this->input->post('permission_id'))){
			foreach($permission_id as $key=> $permission){
				
				$query = $this->db->query("insert into stylist_permission set permission_id='".$permission."',stylist_id=0, role_id='".$role_id."', vendor_id='".$vendor_id."', status='1' ");
			}
		}
		
				$response['status'] = 1;
				
				$response['message'] = 'Permission Updated Successfully!';
			
			
		}else{
			
			$response['status'] = 0;
			
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	public function showPermission(){
		$query=$this->db->query('select id,heading,permission from permission order by sort desc')->result();
		if($query){
				$response['status'] = 1;
				$response['result'] = $query;
				$response['message'] = '';
			}else{
				$response['status'] = 0;
			
				$response['message'] = 'No data found!';
				
			}
				echo json_encode($response);
	}
	public function updateNotification(){
		
		 $response['status'] = 0;
		$response['message'] = '';
		$object = '{}';	
		$object_array = '[]';
		
		$vendor_id = $this->input->post('vendor_id');
		$appointment_detail = $this->input->post('appointment_detail');
		$confirmation = $this->input->post('confirmation');
		$confirmation_time = $this->input->post('confirmation_time');
		$appointment_reminder = $this->input->post('appointment_reminder');
		$appointment_reminder_time = $this->input->post('appointment_reminder_time');
		$business_notification = $this->input->post('business_notification');
		$stylist_notification = $this->input->post('stylist_notification');
		
		
		if(!empty($vendor_id)){
			
				$query = $this->db->query("update notification set appointment_detail='".$appointment_detail."', confirmation_request='".$confirmation."', confirmation_hour='".$confirmation_time."', appointment_reminder='".$appointment_reminder."', appointment_reminder_hour='".$appointment_reminder_time."', send_notification_to_business='".$business_notification."', send_notification_to_stylist='".$stylist_notification."' where vendor_id='".$vendor_id."' ");
				
				if($query){
					$response['status'] = 1;
					$response['result'] = $object_array;
					$response['message'] = 'Notification settings updated!';
					
				}else{
					
					$response['status'] = 0;
					$response['result'] = $object_array;
					$response['message'] = 'Something went wrong!';
				}
			
		}else{
			
			$response['status'] = 0;
			$response['result'] = $object_array;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
			
		
	}
	
	
	public function getNotification(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = '{}';
		$object_array = '[]';
		$vendor_id = $this->input->post('vendor_id');
		
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select notification_id, appointment_detail, confirmation_request as confirmation, confirmation_hour as confirmation_time, appointment_reminder, appointment_reminder_hour as appointment_reminder_time, send_notification_to_business as business_notification, send_notification_to_stylist as stylist_notification from notification where vendor_id='".$vendor_id."'  ");
			if($query->num_rows()>0){
				
				$result = $query->row();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}else{
				$response['status'] = 0;
				$response['result'] = $object;
				$response['message'] = 'No data found!';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['result'] = $object;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	public function getTemplate(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = '{}';
		$object_array = '[]';
		$vendor_id = $this->input->post('vendor_id');
		
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select template_id, template_name from template where is_active='1' ");
			if($query->num_rows()>0){
				
				$result = $query->result();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}else{
				$response['status'] = 0;
				$response['result'] = $object_array;
				$response['message'] = 'No data found!';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['result'] = $object_array;
			$response['message'] = 'Required parameter missing!';
		}
		echo json_encode($response);
	}
	
	public function appSettings(){
		
		$response['status'] = 0;
		$response['result'] = '[]';
		$response['message'] = 'Required parameter missing!';
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select field as setting_name, value from settings ");
			if($query->num_rows()>0){
				$result = $query->result();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}
		}else{
			
			$response['status'] = 0;
			$response['result'] = '[]';
			$response['message'] = 'Required parameter missing!';
			
		}
		echo json_encode($response);
	}
	
	public function updateAppSettings(){
		$response['status'] = 0;
		$response['result'] = '[]';
		$response['message'] = 'Required parameter missing!';
		
		$vendor_id = $this->input->post('vendor_id');
		$setting_name = $this->input->post('setting_name');
		$value = $this->input->post('value');
		
		if(!empty($vendor_id) && !empty($setting_name)){
			
			$query = $this->db->query("update settings set value='".$value."' where field='".$setting_name."' ");
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Setting udpated successfully!';
			}else{
				$response['status'] = 0;
				$response['message'] = 'Something went wrong!';
			}
		}else{
			
			$response['status'] = 0;
			$response['result'] = '[]';
			$response['message'] = 'Required parameter missing!';
			
		}
		echo json_encode($response);
		
	}
	
	/*28/07/2021*/
	function editBusinessProfile(){

		$vendor_id = $this->input->post("vendor_id");
		$owner_name = $this->input->post("name");
        $vendor_name = $this->input->post("store_name");
        $phone = $this->input->post("phone");
		$alternate_phone = $this->input->post("alternate_phone");
        $country_id = $this->input->post("country_id");
        $state_id = $this->input->post("state_id");
        $city = $this->input->post("city");
		$address = $this->input->post("address");
        
        $merchant_id = $this->input->post("merchant_id");
        $merchant_key = $this->input->post("merchant_key");
        $about_store = $this->input->post("about_store");
        if(!empty($vendor_id)){
        if(!empty($_FILES['photo']['name'])){
			/*$path = '../assets/img/product/thumb/';
			$file = time().'.jpeg';
			$photo = $this->base64_to_jpeg($photo,$path.$file);
			$photo_name = $file;*/
			$photo = $_FILES['photo']['name'];
		$tmp_photo = $_FILES['photo']['tmp_name'];
		$path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/client/';
		$file = time().$photo;
			$photo = move_uploaded_file($tmp_photo,$path.$file);
			$photo_name = $file;
		
		}
		$query=$this->db->query('update vendor set vendor_name="'.$vendor_name.'",owner_name="'.$owner_name.'",description="'.$about_store.'",photo="'.$photo_name.'",address="'.$address.'" where vendor_id="'.$vendor_id.'"');
		if($query){
			 $response['status'] = 1;
		   	$response['message'] = 'Business profile update succfully';
		   }else{
		   	 $response['status'] = 0;
		   	$response['message'] = 'Required parameter missing!';
		   }
	}else{
		     $response['status'] = 0;
		   	$response['message'] = 'Required parameter missing!';
	}
	echo json_encode($response);
	}
	public function getBillingInformation(){
	    $vendor_id=$this->input->post('vendor_id');
	     if(!empty($vendor_id)){
		$query = $this->db->query("select A.*,l.email,B.phone,s.name,B.city from billing_info as A inner join vendor as B on A.vendor_id=B.vendor_id inner join login as l on l.login_id=B.login_id left join states as s on s.id=A.state where A.vendor_id='".$vendor_id."' ");
		
		$result = $query->row();
		if(!empty($result)){
			 $response['status'] = 1;
		   	$response['result'] = $result;
		   	$response['message'] = 'Data found';
		}else{
			    $response['status'] = 0;
			    $response['result'] = (Object)[];
		    	$response['message'] = 'No data found';
		}
		
	}else{
		     $response['status'] = 0;
		   	$response['message'] = 'Required parameter missing!';
	}
	echo json_encode($response);
	}

	public function updateBillingInformation()
    {
		$vendor_id=$this->input->post('vendor_id');
        $card_no = $this->input->post("card_no");
        $card_no1=str_replace('X', '', $card_no);
        $expiry_month = $this->input->post("expiry_month");
        $expiry_year = $this->input->post("expiry_year");
        $cvv = $this->input->post("cvv");
		$firstname = $this->input->post("firstname");
        $middlename = $this->input->post("middlename");
        $lastname = $this->input->post("lastname");
        $address = $this->input->post("address");
		$apt_suite = $this->input->post("apt_suite");
        $city = $this->input->post("city");
		$state = $this->input->post("state_id");
       
	    $bank_name = $this->input->post("bank_name");
        $account_no = $this->input->post("account_no");
        $account_type = $this->input->post("account_type");
        $routing_no = $this->input->post("routing_no");
        $zipcode = $this->input->post("zipcode");
        $billing_zip = $this->input->post("billing_zip");
        $is_accept = $this->input->post("is_accept");
       
        if(!empty($vendor_id)){
        $data = array(
            
            "card_no" => trim($card_no1),
            "expiry_month" => trim($expiry_month),
            "expiry_year" => trim($expiry_year),
            "cvv" => trim($cvv),
            "firstname" => trim($firstname),
			"middlename" => trim($middlename),
			"lastname" => trim($lastname),
			"billing_address" => trim($address),
			"apt_suite" => trim($apt_suite),
            "state" => trim($state),
            "city" => trim($city),
            "bank_name" => trim($bank_name),
            "account_no" => trim($account_no),
            "account_type" => trim($account_type),
            "routing_no" => trim($routing_no),
            "zipcode" => trim($zipcode),
            "billing_zip" =>$billing_zip,
            "is_accept" =>$is_accept
            
        );
				
		$this->db->where('vendor_id', $vendor_id);
		$update = $this->db->update('billing_info', $data);
			if($update){
        		$response['status'] = 1;
		   		$response['message'] = 'Billing information update successfully';
		
		     }else{
		     	$response['status'] = 1;
		   		$response['message'] = 'Something went wrong';
		     }
		 }else{
		     $response['status'] = 0;
		   	$response['message'] = 'Required parameter missing!';
	}
	echo json_encode($response);
    }
	
    function getPaymentHistory(){
		$vendor_id=$this->input->post('vendor_id');
		 if(!empty($vendor_id)){
		$query = $this->db->query("select DATE_FORMAT(o.created_date,'%m/%d/%Y') as payment_date, o.final_amount as order_amount from orders o where o.vendor_id='".$vendor_id."' order by o.created_date desc limit 0,3 ");
		$result = $query->result();
		if(!empty($result)){
			$response['status'] = 1;
			$response['result'] = $result;
		   	$response['message'] = 'Data Found';
		   }else{
		   	$response['status'] = 0;
		   	$response['result'] = array();
		   	$response['message'] = 'No date found';
		   }
		}else{
		 $response['status'] = 0;
		   	$response['message'] = 'Required parameter missing!';	
		}
		echo json_encode($response);
	}

	public function update_business_hour()
    {
    	
			//echo "<pre>";print_r($_POST);exit;
        $vendor_id = $this->input->post("vendor_id");
	    $switch = explode(",",$this->input->post("switch"));
	    $day = explode(",",$this->input->post("day"));
	  	$min_time = $this->input->post("min_time");
        $max_time = $this->input->post("max_time");
        $week_start_day = $this->input->post("week_start_day");
        $lunch_start_time = $this->input->post("lunch_start_time");
        $lunch_end_time = $this->input->post("lunch_end_time");
        $allow_apt = $this->input->post("allow_apt");
		$day_start_time = explode(",",$this->input->post("start_time"));
		$day_end_time = explode(",",$this->input->post("end_time"));
		$lunch_start_time  = date("H:i", strtotime($lunch_start_time));
		$lunch_end_time  = date("H:i", strtotime($lunch_end_time));
		$min_time_24  = date("H:i", strtotime($min_time));
		$max_time_24  = date("H:i", strtotime($max_time));
		
		
		if(!empty($vendor_id)){
			
		$this->db->query("update settings set value='".$week_start_day."' where field='week_start_day' and vendor_id='".$vendor_id."'");
		$this->db->query("update settings set first_time='".$lunch_start_time."', second_time='".$lunch_end_time."' where field='lunch_time' and vendor_id='".$vendor_id."'");
		$this->db->query("update settings set first_time='".$min_time_24."', second_time='".$max_time_24."' where field='calendar_start_end_time' and vendor_id='".$vendor_id."'");
		for($i=0;$i<count($day);$i++){
			if(@$switch[$i]=='0'){
				@$switch_val = 0;
				
				$con = "";
				
			}else{
				@$switch_val = 1;
				$from_time = $day_start_time[$i];
				$to_time = $day_end_time[$i];
				$from_time_24  = date("H:i", strtotime($from_time));
				$to_time_24  = date("H:i", strtotime($to_time));
				
				$con = " ,start_time='".$from_time_24."', end_time='".$to_time_24."' ";
			}
			
			
			$qry = $this->db->query("update business_hour set switch='".$switch_val."' $con where days='".$day[$i]."' and vendor_id='".$vendor_id."'  ");
			$query2 = $this->db->query("update settings set value='".$allow_apt."' where field='allow_apt_outside_business_hour' and vendor_id='".$vendor_id."'  ");
			if($qry){
				$response['status'] = 1;
				$response['message'] = 'Business hour updated succefully';
			}else{
				$response['status'] = 0;
		   	$response['message'] = 'Something went wrong';
			}
		
		}
	}else{
		 $response['status'] = 0;
		   	$response['message'] = 'Required parameter missing!';	
		}
		echo json_encode($response);
   }
	
public function changePassword()
    {
     
        
        $vendor_id= $this->input->post('vendor_id');
        if(!empty($vendor_id)){


        $get_loginid=$this->db->query('select l.login_id, l.password from vendor v inner join login l on l.login_id=v.login_id where v.vendor_id="'.$vender_id.'"');
        $get_result=$get_loginid->row();
        $get_pas_conv= $get_result->password;
        
        $old_passwd = md5($this->input->post("old_passwd"));
      //  echo $chang_pass_editId."--".$old_passwd ."--".$get_pas_conv;die;
        $new_pass = $this->input->post("new_pass");
        $conf_pass = $this->input->post("conf_pass");

        if($old_passwd==$get_pas_conv){
            if($new_pass==$conf_pass){
                $conf_md5_pass=md5($conf_pass);
                $run_up_pass=$this->db->query('update login set password="'.$conf_md5_pass.'" where login_id="'.$get_result->login_id.'"');

                if($run_up_pass){
                    echo "1";
                $response['status'] = 1;
		     	$response['message'] = 'Password change successfully';	
                }
            }else{
                $response['status'] = 0;
		     	$response['message'] = 'Password mismatch!';	
                
                
            }
        }else{
           
            $response['status'] = 0;
		    $response['message'] = 'Wrong old password!';
        }
    	}else{
		 $response['status'] = 0;
		   	$response['message'] = 'Required parameter missing!';	
		}
		echo json_encode($response);
     
    
    }
    public function getHardware(){
    	 $vender_id= $this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$query = $this->db->query("select * from printer where vendor_id='".$vendor_id."' ");
		$result = $query->result();
		if(!empty($result)){
			$response['status'] = 1;
			$response['result']=$result;
		   	$response['message'] = 'Data found';	
		}else{
			$response['status'] = 0;
			$response['result']=array();
		   	$response['message'] = 'No data found';	
		}
		}else{
		    $response['status'] = 0;
		   	$response['message'] = 'Required parameter missing!';	
		}
		echo json_encode($response);
	
	}

	function calnder_config(){
		
		$vendor_id=$this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			$response['calendar_start_time'] = $this->db->query("select TIME_FORMAT(first_time,'%h:%i %p') as start_time, TIME_FORMAT(second_time,'%h:%i %p') as end_time from settings where field='calendar_start_end_time' AND vendor_id='".$vendor_id."'  ")->row();
			$response['last_apt_time'] = $this->db->query("select first_time as hour, second_time as minute from settings where field='last_appointment_time' and vendor_id='".$vendor_id."'  ")->row();
			$response['slot_duration'] = $this->db->query("select value as slot_duration from settings where field='calendar_slot_duration' and vendor_id='".$vendor_id."'  ")->row();
			$response['row_height'] = $this->db->query("select value as row_height from settings where field='calendar_row_height' and vendor_id='".$vendor_id."'  ")->row();
			
			
			
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
		}
		echo json_encode($response);
	}
	public function save_calender_config(){
		$vendor_id = $this->input->post('vendor_id');
		$last_apt_time_hour = $this->input->post('last_apt_time_hour');
		$last_apt_time_minute = $this->input->post('last_apt_time_minute');
		$time_slot = $this->input->post('time_slot');
		
		if(!empty($vendor_id)){
		if($time_slot=='15'){
			
			$row_height = '0';
		}
		else if($time_slot=='30'){
			
			$row_height = '3';
		}
		
		else if($time_slot=='60'){
			
			$row_height = '6';
		}
		
		$q = $this->db->query("update settings set value='".$time_slot."' where field='calendar_slot_duration' and vendor_id='".$vendor_id."' ");
		
		$q2 = $this->db->query("update settings set value='".$row_height."' where field='calendar_row_height' and vendor_id='".$vendor_id."' ");
		
		$query = $this->db->query("update settings set first_time='".$last_apt_time_hour."', second_time='".$last_apt_time_minute."' where field='last_appointment_time' and vendor_id='".$vendor_id."'  ");
		
		
		
		if($query){
			$response['status'] = 1;
		    $response['message'] = 'Calendar udpated successfully!';	
			
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Calendar not updated!';	
			
		}
	}else{
		$response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
	}
	echo json_encode($response);
	}

	public function getAptRules(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$query = $this->db->query("select * from appointment_rules where vendor_id='".$vendor_id."' ");
		$result = $query->row();
		if(!empty($result)){
			$response['status'] = 1;
		    $response['message'] = 'Data found';	
		    $response['result']=$result;
		}else{
			$response['status'] = 0;
		    $response['message'] = 'No data found';	
		}
	}else{
		$response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
	}
	echo json_encode($response);
	}
	function update_rules(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$field1 = $this->input->post('field1');
		$field2 = $this->input->post('field2');
		
		$query  = $this->db->query("update appointment_rules set field1='".$field1."', field2='".$field2."', is_active='1' where vendor_id='".$vendor_id."' ");
		
		if($query){
			
		    $response['message'] = 'Rules updated successfully!';
		}else{
			
			$response['status'] = 0;
		    $response['message'] = 'Something went wrong';
		}
	}
			
			else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
	}
			echo json_encode($response);
	}
	public function getColor(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$query = $this->db->query("select * from color_settings where vendor_id='".$vendor_id."' and is_active='1' ");
		$result = $query->result();
		if(!empty($result)){
		   $response['status'] = 1;
		   $response['result']=$result;
		    $response['message'] = 'Data found';		
		}else{
		$response['status'] = 0;
		   
		    $response['message'] = 'No data found';	
		}
	}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
	}
			echo json_encode($response);
		
	}
	function edit_color()
    {
		
        $accept = $this->input->post("accept");
        $deny = $this->input->post("deny");
        $confirm = $this->input->post("confirm");
		$shows = $this->input->post("show");
        $no_show = $this->input->post("no_show");
        $service_in_process = $this->input->post("service_in_process");
        $cancel = $this->input->post("cancel");
        $checkout = $this->input->post("checkout");
        $mark_new = $this->input->post("mark_new");
		//echo $shows;die;
		
		$this->db->query("update color_settings set color_code='".$accept."' where color_type='accept' ");
		$this->db->query("update color_settings set color_code='".$deny."' where color_type='deny' ");
		$this->db->query("update color_settings set color_code='".$confirm."' where color_type='confirm' ");
		$this->db->query("update color_settings set color_code='".$shows."' where color_type='shows' ");
		$this->db->query("update color_settings set color_code='".$no_show."' where color_type='no_show' ");
		$this->db->query("update color_settings set color_code='".$service_in_process."' where color_type='service_in_process' ");
		$this->db->query("update color_settings set color_code='".$checkout."' where color_type='checkout' ");
		$this->db->query("update color_settings set color_code='".$mark_new."' where color_type='mark_new' ");
		$response['status'] = 1;
		 $response['message'] = 'Update successfully';	
		 echo json_encode($response);
        
        
    }
    public function getNotification_new(){
		
		$vendor_id=$this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			

			$arr = array();
			
			$qq = $this->db->query("select value as notification_criteria_type from settings where field='notification_criteria_type' and vendor_id='".$vendor_id."' ");
			
			$nct = $qq->row()->notification_criteria_type;
			
			
		$sqlQuery = $this->db->query("select nc.*  from notification_criteria nc where nc.vendor_id='".$vendor_id."' ");
		$result = $sqlQuery->result();
		if(!empty($result)){
			
			$arr = $result;
			foreach($result as $key=>$res){
				if($nct==1){
					
					$duration = $res->duration;
				}else{
					
					$duration = ($res->duration)/24;
				}
				$arr[$key]->duration = $duration;
			}
			
			
		   $response['status'] = 1;
		   $response['result']=$arr;
		   $response['notification_criteria_type']=$nct;
		    $response['message'] = 'Data found';		
		}else{
		$response['status'] = 0;
		   
		    $response['message'] = 'No data found';	
		}
		}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
		}
			echo json_encode($response);
	
	}
	
	
	function email_settings(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$response['email_data_app'] =$this->db->query("select * from email_settings where  is_active=1 and email_type=3 and vendor_id='".$vendor_id."' order by sort asc")->result();
        $response['email_data_cust'] =$this->db->query("select * from email_settings where  is_active=1 and email_type=1 and vendor_id='".$vendor_id."' order by sort asc")->result();
        $response['email_data_sty'] = $this->db->query("select * from email_settings where  is_active=1 and email_type=2 and vendor_id='".$vendor_id."' order by e_id asc")->result();
        }else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
	}
			echo json_encode($response);
	}
	/*28/07/2021*/
	function work_hours(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$response['min_type']=$this->db->query('select TIME_FORMAT(start_time,"%h:%i %p") AS start_time, TIME_FORMAT(end_time,"%h:%i %p") AS end_time from business_hour where min_type=1 and vendor_id="'.$vendor_id.'"')->row();

		
		$response['allow_attendance'] = $this->getSettings('allow_attendance_outside_workhour',$vendor_id);
		$response['work_hour'] = $this->getSettings('schedule_work_hour',$vendor_id);
		$response['week_start_day'] = $this->getSettings('schedule_week_start_day',$vendor_id);
		
		$response['short_break_deduction'] = $this->getSettings('short_break_deduction',$vendor_id);
		}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
	}
			echo json_encode($response);
	}
	function getSettings($field,$vendor_id){
		
		$query = $this->db->query("select A.*, TIME_FORMAT(A.first_time,'%h:%i %p') AS first_time, TIME_FORMAT(A.second_time,'%h:%i %p') AS second_time from settings A where A.field='".$field."' and A.vendor_id='".$vendor_id."'  ");
		$result = $query->row();
		return $result;
	}
	public function update_work_hours()
    {
    	$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$schedule_start_time = $this->input->post('schedule_start_time');
		$schedule_end_time = $this->input->post('schedule_end_time');
		$allow_attendance_outside_workhour = $this->input->post('allow_attendance_outside_workhour');
		$week_start_day = $this->input->post('week_start_day');
		
		
		$allow_short_break_deduction = $this->input->post('allow_short_break_deduction');
		
		if($allow_attendance_outside_workhour==1){
			$aaow = 1;
		}else{
			$aaow = 0;
		}
		
		if($allow_short_break_deduction==1){
			$asbd = 1;
		}else{
			$asbd = 0;
		}
		
		$start_time = date('H:i',strtotime($schedule_start_time));
		$end_time = date('H:i',strtotime($schedule_end_time));
		
      	
		$query = $this->db->query("update settings set value='".$week_start_day."' where field='schedule_week_start_day' and vendor_id='".$vendor_id."' ");
		
		$query = $this->db->query("update settings set first_time='".$start_time."', second_time='".$end_time."' where field='schedule_work_hour' and vendor_id='".$vendor_id."' ");
		
		$query2 = $this->db->query("update settings set value='".$aaow."' where field='allow_attendance_outside_workhour' and vendor_id='".$vendor_id."' ");
		
		$query3 = $this->db->query("update settings set value='".$short_break_time."' where field='short_break_time' and vendor_id='".$vendor_id."' ");
		
		$query4 = $this->db->query("update settings set value='".$long_break_time."' where field='long_break_time' and vendor_id='".$vendor_id."' ");
		
		$query5 = $this->db->query("update settings set value='".$asbd."' where field='short_break_deduction' and vendor_id='".$vendor_id."' ");
		if($query){
			 $response['status'] = 1;
		    $response['message'] = 'Update successfully';	
		}else{
			 $response['status'] = 0;
		    $response['message'] = 'Something went wrong';	
		}
		}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
	}
			echo json_encode($response);
	   
		
    }
    public function getEmployeeType(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$sqlQuery = $this->db->query("select * from employee_type where is_active='1' and  vendor_id='".$vendor_id."' ");
		$result = $sqlQuery->result();
		if(!empty($result)){
			 $response['status'] = 1;
		    $response['message'] = 'Data found';
		    $response['result']=$result;
		}else{
			 $response['status'] = 0;
		    $response['message'] = 'No data found';
			 $response['result']=[];
		}
		}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
	}
			echo json_encode($response);
		
	
	}
	public function update_employee_type(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$pc = $this->input->post('employee_type');
		$role_id = $this->input->post('role_id');
		
		if(!empty($role_id)){
				$query = $this->db->query("update employee_type set type='".$pc."' where emp_type_id='".$role_id."' ");
		 
		}else{
		$query = $this->db->query("insert into employee_type set type='".$pc."', is_active='1', vendor_id='".$vendor_id."', date_created='".date('Y-m-d H:i')."' ");
		 
		}
		if($query){
			$response['status'] = 1;
		    $response['message'] = 'Update successfully';	
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Something went wrong';	
		}
	}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
	}
			echo json_encode($response);
		

	}
	function getpermissionCategory(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$query = $this->db->query("select * from role where is_active='1' and role_id NOT IN(5) and vendor_id='".$vendor_id."' order by role_id asc ");
		$result = $query->result();
		if(!empty($result)){
			$response['status'] = 1;
		    $response['message'] = 'Data found';
		    $response['result']=$result;	
		}else{
			$response['status'] = 1;
		    $response['message'] = '';	
			$response['result']=[];	
		}
		}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
	}
			echo json_encode($response);
	}
	public function update_permissionCategory(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$pc = $this->input->post('permission_category');
		$role_id = $this->input->post('role_id');
		
		if(!empty($role_id)){
		$query = $this->db->query("update role set role_name='".$pc."' where role_id='".$role_id."' ");
		
		}else{
		$query = $this->db->query("insert into role set role_name='".$pc."', is_active='1', vendor_id='".$vendor_id."' ");
		
		
		}
		if($query){
			$response['status'] = 1;
		    $response['message'] = 'Update successfully';	
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Something went wrong';	
		}
	}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
	}
			echo json_encode($response);
		

	}
	public function getPosition(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$result = $this->db->query('select role_id,role_name from role where is_active="1" and vendor_id="'.$vendor_id.'" and role_id NOT IN(5)')->result();
		if(!empty($result)){
			$response['status'] = 1;
		    $response['message'] = 'Data found';
		    $response['result']=$result;	
		}else{
			$response['status'] = 1;
		    $response['message'] = '';
			$response['result']=[];				
		}
		}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
	}
			echo json_encode($response);



}
function add_position(){
	//echo "<pre>";print_r($_POST);exit;
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		
		$position_name = $this->input->post('position_name');
		$position_id = $this->input->post('position_id');
		if($position_id==''){
		$query = $this->db->query("insert into role set role_name='".$position_name."', is_active='1', vendor_id='".$vendor_id."' ");
		if($query){
			$response['status'] = 1;
		    $response['message'] = 'Add successfully';	
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Something went wrong';	
		}
		}else{
			$query = $this->db->query("update role set role_name='".$position_name."' where role_id='".$position_id."' ");
		if($query){
			$response['status'] = 1;
		    $response['message'] = 'Update successfully';	
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Something went wrong';	
		}
		}

		}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
	}
			echo json_encode($response);
		
	}
	function deletePosition(){
		$position_id=$this->input->post('position_id');
		if(!empty($position_id)){
			$query=$this->db->query("delete from role where role_id='".$position_id."'");
			if($query){
			$response['status'] = 1;
		    $response['message'] = 'Delere successfully';	
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Something went wrong';	
		}
		}
		else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
	}
			echo json_encode($response);

	}
	function deleteEmployeeType(){
		$employee_type_id=$this->input->post('employee_type_id');
		if(!empty($employee_type_id)){
			$query=$this->db->query("delete from employee_type where emp_type_id='".$employee_type_id."'");
			if($query){
			$response['status'] = 1;
		    $response['message'] = 'Delere successfully';	
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Something went wrong';	
		}
		}
		else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
	}
			echo json_encode($response);

	}
	public function getCancellationPolicy(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		
		$query = $this->db->query("select * from cancellation_policy where vendor_id='".$vendor_id."' ");
		$result = $query->row();
			if(!empty($result)){
			$response['status'] = 1;
		    $response['message'] = 'Data found';
		    $response['result']=$result;	
		}else{
			$response['status'] = 1;
		    $response['message'] = 'Update successfully';	
		}
		
	}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
	}
			echo json_encode($response);
	}
	public function getNotificationCriteria(){
	$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$sqlQuery = $this->db->query("select * from notification_criteria where vendor_id='".$vendor_id."' ");
		$result = $sqlQuery->result();
		if(!empty($result)){
			$response['status'] = 1;
		    $response['message'] = 'Data found';
		    $response['result']=$result;	
		}else{
			$response['status'] = 1;
		    $response['message'] = 'Update successfully';	
		}
		}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
	}
			echo json_encode($response);
		
	
	}
	public function getGiftSetings(){
	$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$query = $this->db->query("select * from gift_settings where vendor_id='".$vendor_id."' ");
		$result = $query->row();
		if(!empty($result)){
			$response['status'] = 1;
		    $response['message'] = 'Data found';
		    $response['result']=$result;	
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Data not found';	
		}
		}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
	}
			echo json_encode($response);
	}
	public function update_gift()
    {
        $vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
        $max_amount = $this->input->post("max_amount");
        
        $update = $this->db->query("update gift_settings set max_amount='".$max_amount."' where vendor_id='".$vendor_id."' ");
        if ($update==1) {
           $response['status'] = 1;
		    $response['message'] = 'Update successfully';
		  	
        } else {
           $response['status'] = 0;
		    $response['message'] = 'Something went wrong';
		    
        }
	}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
	}
			echo json_encode($response);
      
    }
    public function giftcard(){
    	$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
			$path = "https://".$_SERVER['HTTP_HOST'].'/assets/img/giftcard';
		$response['amount']=$this->db->query("select * from giftcard_settings where vendor_id='".$vendor_id."' ")->row();
		$response['giftcard_images']=$this->db->query("select gc_image_id,CONCAT('$path','/',if(image='','noimage.png',image)),type  from giftcard_images  ")->result();
		
   		 }else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
		}
			echo json_encode($response);
    
       
        
    }
     public function update_giftcard()
    	{
        $vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
        $preset_amount_1 = $this->input->post("preset_amount_1");
        $preset_amount_2 = $this->input->post("preset_amount_2");
        $preset_amount_3 = $this->input->post("preset_amount_3");
        $preset_amount_4 = $this->input->post("preset_amount_4");
		$update = $this->db->query("update giftcard_settings set preset_amount_1='".$preset_amount_1."', preset_amount_2='".$preset_amount_2."', preset_amount_3='".$preset_amount_3."', preset_amount_4='".$preset_amount_4."' where vendor_id='".$vendor_id."' ");
        if ($update==1) {
            $response['status'] = 1;
		    $response['message'] = 'Update successfully';
		  	
        } else {
  	        $response['status'] = 0;
		    $response['message'] = 'Something went wrong';
	    }
        }else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
		}
        echo json_encode($response);
        
    }
    /*public function getDiscount(){
	$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$query = $this->db->query("SELECT
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
					AND c.vendor_id='".$vendor_id."'");
		$result = $query->result();
		if(!empty($result)){
			$response['status'] = 1;
		    $response['message'] = 'Data found';
		    $response['result']=$result;	
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Data not found';	
		}
		}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
		}
        echo json_encode($response);
	}*/
	public function add_discount()
    {
    	$vendor_id=$this->input->post('vendor_id');
	    $discount_for = $this->input->post("discount_for");
        $coupon_type = $this->input->post("coupon_type");
        $discount = $this->input->post("discount");
        $min_amount = $this->input->post("min_amount");
        $description = $this->input->post("description");
   if(!empty($vendor_id) && !empty($coupon_type)){
        $insert_data = array(
            
            "coupon_type" => trim($coupon_type),
            "discount" => trim($discount),
            "start_date" => date("Y-m-d H:i:s", strtotime($this->input->post("start_date"))),
            "end_date" => date("Y-m-d H:i:s", strtotime($this->input->post("end_date"))),
            "min_amount" => trim($min_amount),
            "description" => trim($description),
            "main_image" => trim($main_image),
            "discount_for" => trim($discount_for),
            "is_active" => 1,
            "is_delete" => 0,
            "created_date" => date("Y-m-d H:i:s"),
            "created_user" => $vendor_id,
            "vendor_id" => $vendor_id
        );
        $query=$this->db->insert('coupon',$insert_data);
        if ($query) {
            $response['status'] = 1;
		    $response['message'] = 'Insert successfully';
		  	
        } else {
  	        $response['status'] = 0;
		    $response['message'] = 'Something went wrong';
	    }
	    }else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
		}
		echo json_encode($response);
        
    }
    public function coupon(){
    	$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$query = $this->db->query("select c.*, IF(c.is_active=1,'Active','Inactive') as is_active from coupon2 c  where c.vendor_id='".$vendor_id."' and c.is_delete=0 order by c.coupon_id desc");
		$result = $query->result();
		if(!empty($result)){
			$response['status'] = 1;
		    $response['message'] = 'Data found';
		    $response['result']=$result;	
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Data not found';	
		}
		}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
		}
		echo json_encode($response);
        
    }
		public function addCoupon(){
		
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
        $coupon_for = $this->input->post("coupon_for");
        $coupon_type = $this->input->post("coupon_type");
        $amount = $this->input->post("amount");
        $percent = $this->input->post("percent");
        $valid_from = $this->input->post("valid_from");
        $valid_till = $this->input->post("valid_till");
        $description = $this->input->post("description");
        $coupon_code = $this->input->post("coupon_code");
        $custom_coupon_code = $this->input->post("custom_coupon_code");
        $start_time = $this->input->post("start_time");
        $end_time = $this->input->post("end_time");
       
	   if($custom_coupon_code!='' || !empty($custom_coupon_code)){
		   
		   $final_coupon_code = $custom_coupon_code;
	   }else{
		   $final_coupon_code = $coupon_code;
	   }
	   
	   if($coupon_type==1){
		   
		   $amount = $amount;
	   }else{
		   
		   $amount = $percent;
	   }
	   
	   $valid_from = date('Y-m-d',strtotime($valid_from));
	   $valid_till = date('Y-m-d',strtotime($valid_till));
	   $start_time = date('H:i',strtotime($start_time));
	   $end_time = date('H:i',strtotime($end_time));
	   
	  
	   
	   $insertCoupon = $this->db->query("insert into coupon2 set coupon_number='".$final_coupon_code."', discount_for='".$coupon_for."', coupon_type='".$coupon_type."',discount='".$amount."', start_date='".$valid_from."', end_date='".$valid_till."', description='".$description."', is_active='1', vendor_id='".$vendor_id."', start_time='".$start_time."', end_time='".$end_time."' ");
	   $insert = $this->db->insert_id();
	   if ($insert) {
            $response['status'] = 1;
		    $response['message'] = 'Insert successfully';
		  	
        } else {
  	        $response['status'] = 0;
		    $response['message'] = 'Something went wrong';
	    }
	   }else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
		}
		echo json_encode($response);
	 
	}
		public function editCoupon(){
		
		$editId = $this->input->post("editId");
		if(!empty($editId)){
        $coupon_for = $this->input->post("coupon_for");
        $coupon_type = $this->input->post("coupon_type");
        $amount = $this->input->post("amount");
        $percent = $this->input->post("percent");
        $valid_from = $this->input->post("valid_from");
        $valid_till = $this->input->post("valid_till");
        $description = $this->input->post("description");
        $coupon_code = $this->input->post("coupon_code");
        $custom_coupon_code = $this->input->post("custom_coupon_code");
        $start_time = $this->input->post("start_time");
        $end_time = $this->input->post("end_time");
        $template_id = $this->input->post("template_id");
       
	   if($custom_coupon_code!='' || !empty($custom_coupon_code)){
		   
		   $final_coupon_code = $custom_coupon_code;
	   }else{
		   $final_coupon_code = $coupon_code;
	   }
	   
	   if($coupon_type==1){
		   
		   $amount = $amount;
	   }else{
		   
		   $amount = $amount;
	   }
	   
	   $valid_from = date('Y-m-d',strtotime($valid_from));
	   $valid_till = date('Y-m-d',strtotime($valid_till));
	   
	   $vendor_id = $this->input->post('vendor_id');
	   
	   $insertCoupon = $this->db->query("update coupon2 set coupon_number='".$final_coupon_code."', discount_for='".$coupon_for."', coupon_type='".$coupon_type."',discount='".$amount."', start_date='".$valid_from."', end_date='".$valid_till."', description='".$description."', is_active='1', vendor_id='".$vendor_id."', start_time='".$start_time."', end_time='".$end_time."',template_id='".$template_id."' where coupon_id='".$editId."' ");
	   $insert = $this->db->insert_id();
	   if ($insertCoupon) {
            $response['status'] = 1;
		    $response['message'] = 'Update successfully';
		  	
        } else {
  	        $response['status'] = 0;
		    $response['message'] = 'Something went wrong';
	    }
	   }else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
		}
		echo json_encode($response);
       
	}

	public function getIOUsettings(){
		
		 $response['status'] = 0;
		 $response['message'] = '';	
		 $response['result'] = [];	
			
			
	$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$query = $this->db->query("select * from iou_settings where vendor_id='".$vendor_id."' ");
		$result = $query->row();
		if(!empty($result)){
			$response['status'] = 1;
		    $response['message'] = 'Data found';
		    $response['result']=$result;	
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Data not found';	
		    $response['result'] = [];	
		}
		 }else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
		    $response['result'] = [];		
		}
		echo json_encode($response);
	}
	public function add_iou()
    {
    	$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
        
        $max_amount = $this->input->post("max_amount");
        
        $update = $this->db->query("update iou_settings set max_amount='".$max_amount."' where vendor_id='".$vendor_id."' ");
        if ($update) {
            $response['status'] = 1;
		    $response['message'] = 'Update successfully';
		  	
        } else {
  	        $response['status'] = 0;
		    $response['message'] = 'Something went wrong';
	    }
         }else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
		}
		echo json_encode($response);
        
    }
    public function getGalleryImage(){
    	$vendor_id=$this->input->post('vendor_id');
    	if(!empty($vendor_id)){
    		$path = "https://".$_SERVER['HTTP_HOST'].'/assets/img/gallery';
		$query = $this->db->query("select galary_id,galary_name,CONCAT('$path','/',if(main_image='','noimage.png',main_image)) as photo from gallery where vendor_id='".$vendor_id."'");
		$result = $query->result();
		if(!empty($result)){
			$response['status'] = 1;
		    $response['message'] = 'Data found';
		    $response['result']=$result;	
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Data not found';	
		}
		}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
		}
		echo json_encode($response);
		
	}
	public function add_photo_gallery()
    {
        
       	$vendor_id=$this->input->post('vendor_id');
    	if(!empty($vendor_id)){
	   $gal_name = $this->input->post("gal_name");
       if(!empty($_FILES['photo']['name'])){
			/*$path = '../assets/img/product/thumb/';
			$file = time().'.jpeg';
			$photo = $this->base64_to_jpeg($photo,$path.$file);
			$photo_name = $file;*/
			$photo = $_FILES['photo']['name'];
		$tmp_photo = $_FILES['photo']['tmp_name'];
		$path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/gallery/';
		$file = time().$photo;
			$photo = move_uploaded_file($tmp_photo,$path.$file);
			$photo_name = $file;
		
		}else{
			$photo_name = 'avtar.png';
		}
       $vendor_id=$this->session->userdata('vendor_id');
       
        $insert_data = array(
                    'galary_name' => $gal_name,
                    'main_image' => $photo_name,
                    'vendor_id'=>$vendor_id
                    
                 );
        $query=$this->db->insert('gallery', $insert_data);
          if($query){
           $response['status'] = 1;
		    $response['message'] = 'Photo added sucessfully!';
          }else{
          	$response['status'] = 0;
		    $response['message'] = 'Something went wrong';
          }
		
	   }else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
		}
		echo json_encode($response);
	
    }
    public function deleteimage(){
    	$gallery_id=$this->input->post('gallery_id');
    	if(!empty($gallery_id)){
        $query=$this->db->query('DELETE FROM gallery WHERE  galary_id="'.$gallery_id.'" ');
        if($query){
           $response['status'] = 1;
		    $response['message'] = 'Add image successfully';
          }else{
          	$response['status'] = 0;
		    $response['message'] = 'Something went wrong';
          }
        }else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
		}
		echo json_encode($response);
    
}
public function notification(){
       // echo "djhdj";
	$vendor_id=$this->input->post('vendor_id');
	if(!empty($vendor_id)){
    $data_new=$this->db->query('select * from product where quantity <=low_qty_warning and vendor_id="'.$vendor_id.'"')->result();
    $data_new2=$this->db->query("select a.appointment_id,a.token_no,a.appointment_type, s.service_name,a.date as apt_date, aps.appointment_time, aps.appointment_end_time, CONCAT(c.firstname,' ',c.lastname) as customer_name, c.mobile_phone as phone, CONCAT(st.firstname,' ',st.lastname) as stylist_name from appointment a inner join appointment_service aps on aps.appointment_id=a.appointment_id inner join service s on s.service_id=aps.service_id inner join customer c on c.customer_id=a.customer_id inner join stylist st on aps.stylist_id=st.stylist_id where a.status=1 and a.vendor_id='".$vendor_id."' and a.is_delete=0 ")->result();
     $total=count($data_new);
    $total2=count($data_new2);
    $response['total_count']=$total+$total2;
    if(!empty($data_new)){
    	$response['quant_data']=$data_new;
    	$response['quant_count']=$total;
    	
    }else{
    		$response['quant_data']=array();
    		$response['quant_count']=0;
    		
    }
    if(!empty($data_new2)){
    	$response['appoint_data']=$data_new2;
    	$response['appoint_count']=$total2;

    }else{
    		$response['appoint_data']=array();
    		$response['appoint_count']=0;
    }

   /* if(!empty($data_new)){
    $total=count($data_new);
    $total2=count($data_new2);
    $response['status'] = 1;
	$response['message'] = 'Data found';
	$response['result']=$data_new;	
    }else{
    	$response['status'] = 0;
		$response['message'] = 'No data found';
		$response['result']=array();	
    
    }
   */ 


   		}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
		}
		echo json_encode($response);
    }
	
	public function unconfirmApt(){
        $vendor_id=$this->input->post('vendor_id');
	if(!empty($vendor_id)){
    $data_new=$this->db->query("select a.appointment_id,a.token_no,a.appointment_type, s.service_name,a.date as apt_date, aps.appointment_time, aps.appointment_end_time, CONCAT(c.firstname,' ',c.lastname) as customer_name, c.mobile_phone as phone, CONCAT(st.firstname,' ',st.lastname) as stylist_name from appointment a inner join appointment_service aps on aps.appointment_id=a.appointment_id inner join service s on s.service_id=aps.service_id inner join customer c on c.customer_id=a.customer_id inner join stylist st on aps.stylist_id=st.stylist_id where a.status=1 and a.vendor_id='".$vendor_id."' and a.is_delete=0 ")->result();
    if(!empty($data_new)){
    $total=count($data_new);
    $response['status'] = 1;
	$response['message'] = 'Data found';
	$response['total_count']=$total;
	$response['result']=$data_new;	
    }else{
    	$response['status'] = 0;
		$response['message'] = 'No data found';
		$response['total_count']='';
		$response['result']=array();	
    
    }
   	}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
		}
		echo json_encode($response);
    }
    public function confirmApt(){
		
		$appointment_id = $this->input->post('appointment_id');
        $type = $this->input->post('type');

		if($type=='confirm'){
            $query = $this->db->query("update appointment set status='3' where appointment_id='".$appointment_id."' ");
            $query2 = $this->db->query("insert into appointment_status set appointment_id='".$appointment_id."', status='3' ");
            if($query2){
			
			$response['status'] = 1;
		$response['message'] = 'Appointment confirm';
		}else{
			$response['status'] = 0;
		$response['message'] = 'Something wrong';
		}
        }else{
             $query = $this->db->query("update appointment set is_delete='1' where appointment_id='".$appointment_id."' ");
            $query2 = $this->db->query("insert into appointment_status set appointment_id='".$appointment_id."', status='9' ");
            if($query2){
			
			$response['status'] = 1;
		$response['message'] = 'Appointment deleted successfully';
		}else{
			$response['status'] = 0;
		$response['message'] = 'Something wrong';
		}
        }
		
		echo json_encode($response);
	}


	public function scan_gift_card(){
        $gift_card_number=$this->input->post('gift_card_number');
	if(!empty($gift_card_number)){
    $data_new=$this->db->query("select A.*,concat(B.firstname,' ',B.lastname) as customer_name from gift_card as A inner join customer as B on A.customer_id=B.customer_id where A.card_number='".$gift_card_number."'")->row();
    if(!empty($data_new)){
   // $total=count($data_new);
    $response['status'] = 1;
	$response['message'] = 'Data found';
	//$response['total_count']=$total;
	$response['result']=$data_new;	
    }else{
    	$response['status'] = 0;
		$response['message'] = 'No data found';
		//$response['total_count']='';
		$response['result']=(Object)[];	
    
    }
   	}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing!';	
		}
		echo json_encode($response);
    }
	
	
	
	public function updateNotificationCriteria()
    {
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
        $nc_id = $this->input->post('nc_id');
        /* $duration = $this->input->post('duration');
        $text_notify = $this->input->post('text_notify');
        $email_notify = $this->input->post('email_notify');
        $push_notify = $this->input->post('push_notify'); */
		
        $vendor_id = $this->input->post('vendor_id');
        $notification = $this->input->post('notification');
		
		//notification_criteria_type 1 for hours, 2 for days
		 $notification_criteria_type = $this->input->post('notification_criteria_type');
		
		
		//[{"nc_id":"1","text_notify":"0","email_notify":"1","push_notify":"1","ducation":"72"}]
		
		
		if(!empty($vendor_id)&& !empty($notification)){
			
			$q = $this->db->query("update settings set value='".$notification_criteria_type."' where field='notification_criteria_type' and vendor_id='".$vendor_id."' ");

			$notification_data = json_decode($notification);
			
			foreach($notification_data as $nd){
				
				if($notification_criteria_type==1){
					$duration = $nd->duration;
				}elseif($notification_criteria_type==2){
					$duration = ($nd->duration)*24;
				}
				
				$query = $this->db->query("update notification_criteria set duration='".$duration."', text_notify='".$nd->text_notify."', email_notify='".$nd->email_notify."', push_notify='".$nd->push_notify."' where nc_id='".$nd->nc_id."' ");
			}
			
			$response['status'] = 1;
			$response['message'] = 'Notification criteria updated succcessfully!';
			
		}
		
		else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
			
			
		}
		
		echo json_encode($response);
		
    }
	
	public function updateCancellationPolicy(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$field1 = $this->input->post('field1');
		$field2 = $this->input->post('field2');
		$field2_type = $this->input->post('field2_type');
		$field3 = $this->input->post('field3');
		$field3_type = $this->input->post('field3_type');
	//	$policy = $this->input->post('policy');
		$vendor_id = $this->input->post('vendor_id');
		
		
		if(!empty($vendor_id)){
		
		$query = $this->db->query("update cancellation_policy set field1='".$field1."', field2='".$field2."', field2_type='".$field2_type."', field3='".$field3."', field3_type='".$field3_type."' where vendor_id='".$vendor_id."' ");
		
		if($query){
			
			$response['status'] = 1;
			$response['message'] = 'Cancellation policy updated successfully!';
		}
		else{
			
			$response['status'] = 0;
			$response['message'] = 'Something went wrong!';
		
		}
		
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		
		}
		
		echo json_encode($response);
	}
	
	
	public function addTax(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$product_tax = $this->input->post('product_tax');
		$service_tax = $this->input->post('service_tax');
		
		if(!empty($vendor_id)){
			
			
// product_tax = [{"tax_type":"tax1","tax_rate":"2.00","start_date":"2021-09-11","description":"this is tax"},{"tax_type":"tax2","tax_rate":"3.00","start_date":"2021-09-11","description":"this is tax"},{"tax_type":"tax3","tax_rate":"4.00","start_date":"2021-09-11","description":"this is tax"}]

// service_tax = [{"tax_type":"tax1","tax_rate":"2.00","start_date":"2021-09-11","description":"this is tax"},{"tax_type":"tax2","tax_rate":"3.00","start_date":"2021-09-11","description":"this is tax"},{"tax_type":"tax3","tax_rate":"4.00","start_date":"2021-09-11","description":"this is tax"}]
			
			$product_tax = json_decode($product_tax);
			$service_tax = json_decode($service_tax);
			
			foreach($product_tax as $pt){
				
			
				$start_date = date('Y-m-d',strtotime($pt->start_date));
				
				$tax_update_qty = $this->db->query("update tax_product set tax_rate='".$pt->tax_rate."', start_date='".$start_date."',description='".$pt->description."' where tax_type='".$pt->tax_type."' and vendor_id='".$vendor_id."' ");
			
			}
			
			foreach($service_tax as $st){
				
				$start_date = date('Y-m-d',strtotime($st->start_date));
				$tax_update_qty = $this->db->query("update tax_service set tax_rate='".$st->tax_rate."', start_date='".$start_date."',description='".$st->description."' where tax_type='".$st->tax_type."' and vendor_id='".$vendor_id."' ");
			
			}
			
			$response['status'] = 1;
			$response['message'] = 'Tax updated successfully!';
			
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		
		}
		
		echo json_encode($response);
		
	}
	
	public function updateReceiptInfo(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$receipt_address_line1 = addslashes($this->input->post('address_line1'));
		$receipt_address_line2 = addslashes($this->input->post('address_line2'));
		$receipt_address_line3 = addslashes($this->input->post('address_line3'));
		$receipt_bottom_text = addslashes($this->input->post('bottom_text'));
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("update vendor set receipt_address_line1='".$receipt_address_line1."', receipt_address_line2='".$receipt_address_line2."', receipt_address_line3='".$receipt_address_line3."', receipt_bottom_text='".$receipt_bottom_text."' where vendor_id='".$vendor_id."' ");
			
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Settings updated successfully!';
			}else{
				
				$response['status'] = 0;
				$response['message'] = 'Something went wrong!';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
			
		}
		
		echo json_encode($response);
	}
	
	
	public function getReceiptInfo(){
		
		$response['status'] = 0;
		$response['result'] = [];
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
	
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select receipt_address_line1, receipt_address_line2, receipt_address_line3, receipt_bottom_text from vendor where vendor_id='".$vendor_id."' ");
			
			if($query->num_rows()>0){
				$result = $query->row();
				$response['status'] = 1;
				$response['result'] = $result;
				
			}else{
				
				$response['status'] = 0;
				$response['result'] = [];
				$response['message'] = 'No data found';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['result'] = [];
			$response['message'] = 'Required parameter missing!';
			
		}
		
		echo json_encode($response);
	}
	
	public function DeleteData(){
		$table_name=$this->input->post('table_name');
		$row_id=$this->input->post('row_id');
		if($table_name=='product'){
			$row_name='product_id';
		}
		if($table_name=='product'){
			$row_name='product_id';
		}
		if($table_name=='brand'){
			$row_name='brand_id';
		}
		if($table_name=='supplier'){
			$row_name='supplier_id';
		}
		if($table_name=='category'){
			$row_name='category_id';
		}
		if(!empty($table_name) && !empty($row_id)){
			//echo 'update "'.$table_name.'" set is_delete=1 where "'.$row_name.'"="'.$row_id.'" ';die;
			$delete=$this->db->query('update '.$table_name.' set is_delete=1 where '.$row_name.'="'.$row_id.'" ');
			if($delete){
				$response['status'] = 1;
				$response['message'] = 'Data Delete Successfully';
			}else{
				$response['status'] = 0;
				$response['message'] = 'Something Wrong';
			}
		}
		else{
			$response['status'] = 0;
			$response['result'] = [];
			$response['message'] = 'Required parameter missing!';
		}
		
		echo json_encode($response);
	}

	public function deleteDiscount(){
			
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post("vendor_id");
		$discount_id = $this->input->post("coupon_id");
		
		if(!empty($vendor_id) && !empty($discount_id)){
			
			$query = $this->db->query("update coupon set is_delete='1' where coupon_id='".$discount_id."' and vendor_id='".$vendor_id."' ");
			
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Discount deleted successfully!';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		
		echo json_encode($response);
	}
	
	
	public function deleteCoupon(){
			
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post("vendor_id");
		$coupon_id = $this->input->post("coupon_id");
		
		if(!empty($vendor_id) && !empty($coupon_id)){
			
			$query = $this->db->query("update coupon2 set is_delete='1' where coupon_id='".$coupon_id."' and vendor_id='".$vendor_id."' ");
			
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Coupon deleted successfully!';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		
		echo json_encode($response);
	}
	
	
	public function changePin()
    {
     
        
        $vendor_id= $this->input->post('vendor_id');
		 $old_passwd = $this->input->post("old_passwd");
		  $new_pass = $this->input->post("new_pass");
        $conf_pass = $this->input->post("conf_pass");
        if(!empty($vendor_id)){


        $get_loginid=$this->db->query('select l.login_id, l.pin from vendor v inner join login l on l.login_id=v.login_id where v.vendor_id="'.$vendor_id.'"');
        $get_result=$get_loginid->row();
        $get_pas_conv= $get_result->pin;
        
       
      //  echo $chang_pass_editId."--".$old_passwd ."--".$get_pas_conv;die;
       

        if($old_passwd==$get_pas_conv){
            if($new_pass==$conf_pass){
                $conf_md5_pass=$conf_pass;
                $run_up_pass=$this->db->query('update login set pin="'.$conf_md5_pass.'" where login_id="'.$get_result->login_id.'"');

                if($run_up_pass){
                   
                $response['status'] = 1;
		     	$response['message'] = 'Pin changed successfully';	
                }
            }else{
                $response['status'] = 0;
		     	$response['message'] = 'Pin mismatch!';	
                
                
            }
        }else{
           
            $response['status'] = 0;
		    $response['message'] = 'Wrong old pin!';
        }
    	}else{
		 $response['status'] = 0;
		   	$response['message'] = 'Required parameter missing!';	
		}
		echo json_encode($response);
     
    
    }
	
	
	public function getGiftCardPresetAmount(){
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select * from giftcard_settings where vendor_id='".$vendor_id."' ");
			
			if($query->num_rows()>0){
				
				$result = $query->row();
				
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
				
			}else{
				
				$response['status'] = 0;
				$response['result'] = [];
				$response['message'] = 'Data not found!';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		
		echo json_encode($response);
		
	}

	
	
	public function getGiftcardTemplate(){
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$path = "http://159.203.182.165/salon/assets/img/giftcard/";
			
			$query = $this->db->query("select gc_image_id, CONCAT('$path',image) as giftcard_image, IFNULL(type,'') as type from giftcard_images ");
			//echo $this->db->last_query();die;
			if($query->num_rows()>0){
				
				$result = $query->result();
				
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
				
			}else{
				
				$response['status'] = 0;
				$response['result'] = [];
				$response['message'] = 'Data not found!';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		
		echo json_encode($response);
		
	}
	
	public function updateGiftCardPresetAmount(){
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$preset_amount_1 = $this->input->post('preset_amount_1');
		$preset_amount_2 = $this->input->post('preset_amount_2');
		$preset_amount_3 = $this->input->post('preset_amount_3');
		$preset_amount_4 = $this->input->post('preset_amount_4');
		
		if(!empty($vendor_id) && !empty($preset_amount_1) && !empty($preset_amount_2) && !empty($preset_amount_3) && !empty($preset_amount_4)){
			
			
			$query = $this->db->query("update giftcard_settings set preset_amount_1='".$preset_amount_1."', preset_amount_2='".$preset_amount_2."', preset_amount_3='".$preset_amount_3."', preset_amount_4='".$preset_amount_4."', modified_date='".date('Y-m-d H:i:s')."' where vendor_id='".$vendor_id."' ");
			//echo $this->db->last_query();die;
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Amount updated successfully!';
				
			}else{
				
				$response['status'] = 0;
				$response['message'] = 'Something went wrong!';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		
		echo json_encode($response);
		
	}
	
	
	
	public function updateBatchCloseTime(){
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$time = $this->input->post('time');
		$is_auto_close = $this->input->post('is_auto_close'); // 0 - Off 1- Onn
		
		if(!empty($vendor_id) && !empty($time)  && !empty($is_auto_close)){
			
			if($is_auto_close==1)
			{
				$time = date('H:i',strtotime($time));
			}else{
				
				$time = '00:00';
			}
			$query = $this->db->query("update settings set value='".$is_auto_close."', first_time='".$time."' where field='batch_close_time' and vendor_id='".$vendor_id."' ");
			//echo $this->db->last_query();die;
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Batch updated successfully!';
				
			}else{
				
				$response['status'] = 0;
				$response['message'] = 'Something went wrong!';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		
		echo json_encode($response);
		
	}
	
	public function getBatchCloseTime(){
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select value as is_auto_close, first_time as batch_close_time from settings where field='batch_close_time' and vendor_id='".$vendor_id."' ");
			
			if($query->num_rows()>0){
				$result = $query->row();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
				
			}else{
				
				$response['status'] = 0;
				$response['result'] = [];
				$response['message'] = 'No data found!';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		
		echo json_encode($response);
		
	}
	
	

	
	
	public function getGiftCertificateSetings(){
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select max_amount from gift_settings where vendor_id='".$vendor_id."' ");
			
			if($query->num_rows()>0){
				$result = $query->row();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
				
			}else{
				
				$response['status'] = 0;
				$response['result'] = [];
				$response['message'] = 'No data found!';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		
		echo json_encode($response);
		
	}
	
	
	public function updateGiftCertifcateSettings(){
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$max_amount = $this->input->post('max_amount');
		
		
		if(!empty($vendor_id) && !empty($max_amount)){
			
			
			$query = $this->db->query("update gift_settings set max_amount='".$max_amount."' where vendor_id='".$vendor_id."' ");
			//echo $this->db->last_query();die;
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Gift certificate updated successfully!';
				
			}else{
				
				$response['status'] = 0;
				$response['message'] = 'Something went wrong!';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
		
		echo json_encode($response);
		
	}


	
	

	



}