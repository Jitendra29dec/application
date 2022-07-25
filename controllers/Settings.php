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
				$response['message'] = 'Something went wrong';
				
			}
			}else{
				$response['status'] = 0;
				$response['message'] = 'Required parameter missing';
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
				    $response['message'] = 'Required parameter missing';
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
				    $response['message'] = 'Required parameter missing';
        
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
	    $response['message'] = 'Required parameter missing';
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
	      $response['message'] = 'Required parameter missing';
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
		   $response['message'] ="Discount updated successfully!";
        }
        else {
            	$response['status'] = 0;
				$response['message'] = 'Something wrong';

        }
	}else{
		$response['status'] = 0;
	    $response['message'] = 'Required parameter missing';
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
					  AND c.vendor_id='".$vendor_id."'
					  ORDER BY c.coupon_id DESC
					  ");
		    	$result = $query->result();
			    $response['status'] = 1;
			    $response['result'] = $result;
			    $response['message']="succ";
			}else{
				$response['status'] = 0;
				$response['result'] = array();
				$response['message'] = 'Required parameter missing';
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
	$status = $this->input->post('status');
	
		if(!empty($vendor_id) && !empty($discount_for)  && !empty($discount_type)  && !empty($discount)  && !empty($start_date) && !empty($end_date)){
			
			if(!empty($photo)){
			$path = '../assets/img/discount/thumb/';
			$file = time().'.jpeg';
			$photo = $this->base64_to_jpeg($photo,$path.$file);
			$photo_name = $file;
		
		}else{
			
			$photo_name = 'avtar.png';
		}
		
			$query = $this->db->query("insert into coupon set coupon_type='".$discount_type."', discount_for='".$discount_for."', discount='".$discount."', start_date='".$start_date."', end_date='".$end_date."', min_amount='".$min_amount."', description='".$description."', main_image='".$photo_name."', is_active='1', is_delete='0', created_date='".date('Y-m-d')."', vendor_id='".$vendor_id."', is_active='".$status."' ");
			if($query){
				$response['status'] = 1;
				$response['message']="Discount added successfully!";
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
			$qry = $this->db->query("select *, is_active as status from coupon where coupon_id='".$discount_id."' ");
			$res = $qry->row();
		
			$response['status'] = 1;
			$response['result'] = $res;
			$response['message'] = '';
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
		
	}
	
	function editDiscount(){
		
		$response['status'] = 0;
		$response['message'] = '';
				
	$discount_id = $this->input->post('discount_id');
	$vendor_id = $this->input->post('vendor_id');
	$discount_for = $this->input->post('discount_for');
	$discount_type = $this->input->post('coupon_type');
	$discount = $this->input->post('discount');
	$start_date = date('Y-m-d H:i:s',strtotime($this->input->post('start_date')));
	$end_date = date('Y-m-d H:i:s',strtotime($this->input->post('end_date')));
	$min_amount = $this->input->post('min_amount');
	$description = $this->input->post('description');
	$category_id = $this->input->post('category_id');
	$photo = $this->input->post('image');
	$status = $this->input->post('status');
	
		
	
		if(!empty($discount_id) && !empty($vendor_id) ){
			
		if(!empty($photo)){
			$path = '../assets/img/discount/thumb/';
			$file = time().'.jpeg';
			$photo = $this->base64_to_jpeg($photo,$path.$file);
			$photo_name = $file;
		
		}/*else{
			
			$editData = $this->getDiscountData($discount_id);
			
			$photo_name = $editData->main_image;
		}*/
		

		
			$query = $this->db->query("update coupon set coupon_type='".$discount_type."', discount_for='".$discount_for."', discount='".$discount."', start_date='".$start_date."', end_date='".$end_date."', min_amount='".$min_amount."', description='".$description."', main_image='".$photo_name."',  modified_date='".date('Y-m-d')."', is_active='".$status."', category_id='".$category_id."' where  coupon_id='".$discount_id."' ");
		    if($query){
			    $response['status'] = 1;
			    $response['message']="Discount updated successfully!";
			}else{
				$response['status'] = 0;
				$response['message'] = 'Something went wrong in query';
			}
			}else{
				$response['status'] = 0;
				$response['message'] = 'Required parameter missing';
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
				$response['message'] = 'No Employee Found';
			}
		}else{
				$response['status'] = 0;
				$response['message'] = 'Required parameter missing';
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
				$response['message'] = 'No Module Found';
			}
		}else{
				$response['status'] = 0;
				$response['message'] = 'Required parameter missing';
			}
		
		echo json_encode($response);
	}
	
	public function getBusinessProfile(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
			//$path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/client/';
			$path = "https://".$_SERVER['HTTP_HOST'].'/assets/img/client';
			$data=$this->db->query("select A.vendor_name as store_name,A.owner_name as name,A.phone,l.email,A.alternate_phone,A.address,A.city,A.zipcode,A.state_id, st.name as state_name,c.name as country, A.description, CONCAT('$path','/',if(A.photo='','noimage.png',A.photo)) as photo from vendor as A inner join login as l on l.login_id=A.login_id left join states as st on st.id=A.state_id LEFT JOIN countries c ON c.id=A.country_id where A.vendor_id='".$vendor_id."' ")->row();
			$response['status'] = 1;
				$response['result'] = $data;
				
				$response['message'] = 'data found';
		}else{
			$response['status'] = 0;
				$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	

	public function getBusinessHour(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("SELECT
                     bh.business_hour_id,bh.days,bh.switch,time_format(bh.start_time,'%l:%i %p') as start_time,time_format(bh.end_time,'%l:%i %p') as end_time,bh.vendor_id,bh.min_type,time_format(bh.start_time_12,'%l:%i %p') as start_time_12,time_format(bh.end_time_12,'%l:%i %p') as end_time_12
                    FROM business_hour AS bh
					where bh.vendor_id='".$vendor_id."'
					ORDER BY bh.business_hour_id ASC
					");
			
			if($query->num_rows()>0){
				$result = $query->result();
				$week_start_date=$this->db->query("select value as day from settings where field='week_start_day' and vendor_id='".$vendor_id."' ")->row();
				$getLunchtime=$query = $this->db->query("select time_format(first_time,'%l:%i %p') as lunch_start_time, time_format(second_time,'%l:%i %p') as lunch_end_time from settings where field='lunch_time' and vendor_id='".$vendor_id."' ")->row();
				$calender_start_time=$this->db->query("select settings_id,time_format(first_time,'%l:%i %p') as erliest_open,time_format(second_time,'%l:%i %p') as latest_closed from settings where field='calendar_start_end_time' and vendor_id='".$vendor_id."'  ")->row();
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
				$response['message'] = 'Required parameter missing';
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
		
		echo '<pre>';print_r($days);die;
		
		if(!empty($vendor_id)){
				
		//	$query = $this->db->query("delete from business_hour where vendor_id='".$vendor_id."' ");
			
			for($i=0;$i<count($days);$i++){
			
				if($days[$i]->switch=='1'){
					$switch = '1';
				}else{
					$switch = '0';
				}
				
				
				//if($query){
				$start_time  = date("H:i", strtotime($days[$i]->from));
				$end_time  = date("H:i", strtotime($days[$i]->to));
				
					$qry = $this->db->query("update business_hour set days='".trim($days[$i]->day)."', switch='".$switch."', start_time='".$start_time."', end_time='".$end_time."' where vendor_id='".$vendor_id."' AND days='".$days[$i]->day."' "); 
				//}
			
			}
		
		
				$response['status'] = 1;
				$response['message'] = 'Business hours updated successfully';
		
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
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
			$response['message'] = 'Required parameter missing';
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
		//echo "<pre>";print_r($_POST);exit;
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
				$response['message'] = 'Something went wrong';
				
			}
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
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
				
				$query2 = $this->db->query("select value as is_screen_lock from settings where field='is_screen_lock' and vendor_id='".$vendor_id."' ");
				$is_screen_lock = $query2->row()->is_screen_lock;
				
				$result = $query->row();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['is_screen_lock'] = $is_screen_lock;
				$response['message'] = '';
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
	
	public function updateScreenLockTime(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$lock_time = $this->input->post('lock_time');
		$is_screen_lock = $this->input->post('is_screen_lock');
		
		if(!empty($vendor_id) && !empty($lock_time)){
			
		
			
			$query = $this->db->query("update settings set value='".$lock_time."' where field='screen_lock_time' and vendor_id='".$vendor_id."' ");
			
			$query2 = $this->db->query("update settings set value='".$is_screen_lock."' where field='is_screen_lock' and vendor_id='".$vendor_id."' ");
			
			if($query){
			
				$response['status'] = 1;
				$response['message'] = 'Screen lock updated successfully';
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
	
	
	public function getCalendarColorSettings(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select color_id, name as status_name, color_type, color_code, text_color from color_settings where vendor_id='".$vendor_id."' and is_active='1' AND (color_type='checked_in' OR color_type='service_in_process' OR color_type='hold' OR color_type='cancel' OR color_type='no_show' OR color_type='checkout' OR color_type='confirm') ");
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
			$response['message'] = 'Required parameter missing';
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
		
		$color_detail = json_decode($this->input->post('color_detail'));
		// [{"color_id":"1","color_code":"#ffkdss","text_color":"#00000"},{"color_id":"2","color_code":"#ffkdss","text_color":"#00000"}]
		
		if(!empty($vendor_id)){
			
			foreach($color_detail as $detail){
				
					$query = $this->db->query("update color_settings set color_code='".$detail->color_code."', text_color='".$detail->text_color."' where color_id='".$detail->color_id."' and vendor_id='".$vendor_id."' ");
			}
			
		
				
				$response['status'] = 1;
				$response['message'] = 'Color updated successfully';
			
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
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
				$response['message'] = 'IOU setting updated';
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
			$response['message'] = 'Required parameter missing';
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
				$response['message'] = 'Credit certificate updated successfully';
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
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	
	public function getTVscreen(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = (Object)[];
		$vendor_id = $this->input->post('vendor_id');
		
		//$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
		//$tvscreen = $actual_link.'/salon/assets/img/tv/';
		$path = "http://159.203.182.165/salon/assets/img/tv/";
		if(!empty($vendor_id)){
			
			
			$query = $this->db->query("select screen_id, CONCAT('$path',wallpaper) AS wallpaper, video_url, video_time_interval, IF(is_active=1,'Active','Deactive') as status from tv_screen where vendor_id='".$vendor_id."' ");
			if($query->num_rows()>0){
				
				$result = $query->row();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			}else{
				$response['status'] = 0;
				$response['result'] = $object;
				$response['message'] = 'No data found';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	
	public function updateTVscreen(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		
		$video_url = $this->input->post('video_url');
		$video_time_interval = $this->input->post('video_time_interval');
		$status = $this->input->post('status');
		
		$updated_date = date('Y-m-d h:i:s');
		
		if(!empty($vendor_id) && !empty($video_url)){
		
		
		$photo = $_FILES['wallpaper']['name'];
		$tmp_photo = $_FILES['wallpaper']['tmp_name'];
		
		$path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/tv/';
		
		
		if(!empty($photo)){
			
			$file = time().$photo;
			$photo = move_uploaded_file($tmp_photo,$path.$file);
			$photo_name = $file;
			
			
		}else{
			$photo_name = 'avtar.png';
			
		}
		
			$query = $this->db->query("update tv_screen set wallpaper='".$photo_name."', video_url='".$video_url."', video_time_interval='".$video_time_interval."', updated_date='".$updated_date."', is_active='".$status."' where vendor_id='".$vendor_id."' ");
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'TV screen updated successfully';
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
			$response['message'] = 'Required parameter missing';
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
				$response['message'] = 'Tax updated successfully';
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
	
	
	public function getFeaturesList(){
		
		$response['status'] = 0;
		$response['message'] = '';
		$object = '{}';
		$object_array = [];
		$vendor_id = $this->input->post('vendor_id');
		
		/*if(!empty($vendor_id)){*/
			
			$query = $this->db->query("select id as permission_id, permission from permission where status='1' order by sort ASC ");
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
			$response['message'] = 'Required parameter missing';
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
				$response['message'] = 'No data found';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['result'] = $object_array;
			$response['message'] = 'Required parameter missing';
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
				
				$response['message'] = 'Permissions updated successfully';
			
			
		}else{
			
			$response['status'] = 0;
			
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	public function showPermission(){
		$query=$this->db->query('select id,heading,permission from permission order by sort ASC')->result();
		if($query){
				$response['status'] = 1;
				$response['result'] = $query;
				$response['message'] = '';
			}else{
				$response['status'] = 0;
			
				$response['message'] = 'No data found';
				
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
					$response['message'] = 'Notification updated successfully';
					
				}else{
					
					$response['status'] = 0;
					$response['result'] = $object_array;
					$response['message'] = 'Something went wrong';
				}
			
		}else{
			
			$response['status'] = 0;
			$response['result'] = $object_array;
			$response['message'] = 'Required parameter missing';
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
				$response['message'] = 'No data found';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['result'] = $object;
			$response['message'] = 'Required parameter missing';
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
				$response['message'] = 'No data found';
				
			}
			
		}else{
			
			$response['status'] = 0;
			$response['result'] = $object_array;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	public function appSettings(){
		
		$response['status'] = 0;
		$response['result'] = '[]';
		$response['message'] = 'Required parameter missing';
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
			$response['message'] = 'Required parameter missing';
			
		}
		echo json_encode($response);
	}
	
	public function updateAppSettings(){
		$response['status'] = 0;
		$response['result'] = '[]';
		$response['message'] = 'Required parameter missing';
		
		$vendor_id = $this->input->post('vendor_id');
		$setting_name = $this->input->post('setting_name');
		$value = $this->input->post('value');
		
		if(!empty($vendor_id) && !empty($setting_name)){
			
			$query = $this->db->query("update settings set value='".$value."' where field='".$setting_name."' ");
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Settings updated successfully';
			}else{
				$response['status'] = 0;
				$response['message'] = 'Something went wrong';
			}
		}else{
			
			$response['status'] = 0;
			$response['result'] = '[]';
			$response['message'] = 'Required parameter missing';
			
		}
		echo json_encode($response);
		
	}

	public function getVendorByVendorId($vendor_id){

		$query = $this->db->query("select * from vendor where vendor_id='".$vendor_id."' ");
		$result = $query->row();
		return $result;
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
		
		}else{
			$vendor_info = $this->getVendorByVendorId($vendor_id);

			$photo_name = $vendor_info->photo;
		}
		$query=$this->db->query('update vendor set vendor_name="'.$vendor_name.'",owner_name="'.$owner_name.'",description="'.$about_store.'",photo="'.$photo_name.'",address="'.$address.'" where vendor_id="'.$vendor_id.'"');
		if($query){
			 $response['status'] = 1;
		   	$response['message'] = 'Business profile updated successfully';
		   }else{
		   	 $response['status'] = 0;
		   	$response['message'] = 'Required parameter missing';
		   }
	}else{
		     $response['status'] = 0;
		   	$response['message'] = 'Required parameter missing';
	}
	echo json_encode($response);
	}
	public function getBillingInformation(){
	    $vendor_id=$this->input->post('vendor_id');
	     if(!empty($vendor_id)){
		$query = $this->db->query("select A.*,l.email,B.phone,s.name,A.city from billing_info as A inner join vendor as B on A.vendor_id=B.vendor_id inner join login as l on l.login_id=B.login_id left join states as s on s.id=A.state where A.vendor_id='".$vendor_id."' ");
		
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
		   	$response['message'] = 'Required parameter missing';
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
        $is_accept_card = $this->input->post("is_accept_card");
       
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
            "is_accept" =>$is_accept,
            "is_accept_card" =>$is_accept_card
            
        );
				
		$this->db->where('vendor_id', $vendor_id);
		$update = $this->db->update('billing_info', $data);
			if($update){
        		$response['status'] = 1;
		   		$response['message'] = 'Billing information updated successfully';
		
		     }else{
		     	$response['status'] = 1;
		   		$response['message'] = 'Something went wrong';
		     }
		 }else{
		     $response['status'] = 0;
		   	$response['message'] = 'Required parameter missing';
	}
	echo json_encode($response);
    }
	
    function getPaymentHistory(){
		$vendor_id=$this->input->post('vendor_id');
		 if(!empty($vendor_id)){


				$query = $this->db->query("select '4/20/2022' as payment_date, '100' as order_amount, 'Complete' as status from appointment limit 0,12 ");

		
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
		   	$response['message'] = 'Required parameter missing';	
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
			
		//$this->db->query("update settings set value='".$week_start_day."' where field='week_start_day' and vendor_id='".$vendor_id."'");
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
				$response['message'] = 'Business hours updated successfully';
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
	
public function changePassword()
    {
     
        
        $vendor_id= $this->input->post('vendor_id');
        if(!empty($vendor_id)){


        $get_loginid=$this->db->query('select l.login_id, l.pin from vendor v inner join login l on l.login_id=v.login_id where v.vendor_id="'.$vendor_id.'"');
        $get_result=$get_loginid->row();
		
        $get_pas_conv= $get_result->pin;
        
        $old_passwd = $this->input->post("old_passwd");
      //  echo $chang_pass_editId."--".$old_passwd ."--".$get_pas_conv;die;
        $new_pass = $this->input->post("new_pass");
        $conf_pass = $this->input->post("conf_pass");

        if($old_passwd==$get_pas_conv){
			$pin_num = $this->db->query("select count(pin) as num from login where pin='".$new_pass."' ")->row();
			

				if($new_pass==$conf_pass){

					if($pin_num->num=='0'){


					$conf_md5_pass=$conf_pass;
					$run_up_pass=$this->db->query('update login set pin="'.$conf_md5_pass.'" where login_id="'.$get_result->login_id.'"');
	
					if($run_up_pass){
					   // echo "1";
					$response['status'] = 1;
					 $response['message'] = 'Pin updated successfully';	
					}else{

						$response['status'] = 0;
					 $response['message'] = 'Pin not updated';	
					}

					}else{

						$response['status'] = 0;
						$response['message'] = 'Please fill another pin';	
					}

				}else{
					$response['status'] = 0;
					 $response['message'] = 'Pin does not match';	
					
					
				}

			
            
        }else{
           
            $response['status'] = 0;
		    $response['message'] = 'Wrong pin';
        }
    	}else{
		 $response['status'] = 0;
		$response['message'] = 'Required parameter missing';	
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
		   	$response['message'] = 'Required parameter missing';	
		}
		echo json_encode($response);
	
	}

	function calnder_config(){
		
		$vendor_id=$this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			$response['status'] = 1;
			$response['message'] = '';
			
			$response['calendar_start_time'] = $this->db->query("select TIME_FORMAT(first_time,'%h:%i %p') as start_time, TIME_FORMAT(second_time,'%h:%i %p') as end_time from settings where field='calendar_start_end_time' AND vendor_id='".$vendor_id."'  ")->row();
			$response['last_apt_time'] = $this->db->query("select first_time as hour, second_time as minute from settings where field='last_appointment_time' and vendor_id='".$vendor_id."'  ")->row();
			$response['slot_duration'] = $this->db->query("select value as slot_duration from settings where field='calendar_slot_duration' and vendor_id='".$vendor_id."'  ")->row();
			$response['row_height'] = $this->db->query("select value as row_height from settings where field='calendar_row_height' and vendor_id='".$vendor_id."'  ")->row();
			
			$response['week_start_day'] = $this->db->query("select value as week_start_day from settings where field='week_start_day' and vendor_id='".$vendor_id."'  ")->row();
			
			
			
			
			
			
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Required parameter missing';	
		}
		echo json_encode($response);
	}
	public function save_calender_config(){
		$vendor_id = $this->input->post('vendor_id');
		$last_apt_time_hour = $this->input->post('last_apt_time_hour');
		$last_apt_time_minute = $this->input->post('last_apt_time_minute');
		$time_slot = $this->input->post('time_slot');
		$week_start_day = $this->input->post('week_start_day');
		
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
		
		$this->db->query("update settings set value='".$week_start_day."' where field='week_start_day' and vendor_id='".$vendor_id."'");
		
		$q = $this->db->query("update settings set value='".$time_slot."' where field='calendar_slot_duration' and vendor_id='".$vendor_id."' ");
		
		$q2 = $this->db->query("update settings set value='".$row_height."' where field='calendar_row_height' and vendor_id='".$vendor_id."' ");
		
		$query = $this->db->query("update settings set first_time='".$last_apt_time_hour."', second_time='".$last_apt_time_minute."' where field='last_appointment_time' and vendor_id='".$vendor_id."'  ");
		
		
		
		if($query){
			$response['status'] = 1;
		    $response['message'] = 'Calendar updated successfully';	
			
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Calendar not updated';	
			
		}
	}else{
		$response['status'] = 0;
		    $response['message'] = 'Required parameter missing';	
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
		    $response['message'] = 'Required parameter missing';	
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
			$response['status'] = 1;
		    $response['message'] = 'Appointment rules updated successfully';
		}else{
			
			$response['status'] = 0;
		    $response['message'] = 'Something went wrong';
		}
	}
			
			else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing';	
	}
			echo json_encode($response);
	}
	public function getColor(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$query = $this->db->query("select * from color_settings where vendor_id='".$vendor_id."' and is_active='1' AND (color_type='checked_in' OR color_type='service_in_process' OR color_type='hold' OR color_type='cancel' OR color_type='no_show') ");
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
		    $response['message'] = 'Required parameter missing';	
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
		 $response['message'] = 'Colors updated successfully';	
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
					
					$duration1 = $res->duration;
				}else{
					//$duration = ($res->duration)/24;
					$duration1 = $res->duration;
					//$duration1=number_format($duration,2);
				}
				$arr[$key]->duration = $duration1;
			}
			
			
		   $response['status'] = 1;
		   $response['result']=$arr;
		   $response['duration_type']=$nct;
		    $response['message'] = 'Data found';		
		}else{
		$response['status'] = 0;
		   
		    $response['message'] = 'No data found';	
		}
		}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing';	
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
		    $response['message'] = 'Required parameter missing';	
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
		    $response['message'] = 'Required parameter missing';	
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
		    $response['message'] = 'Work hours updated successfully';	
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
    public function getEmployeeType(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$sqlQuery = $this->db->query("select * from employee_type where is_active='1' and  vendor_id='".$vendor_id."' order by emp_type_id desc ");
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
		    $response['message'] = 'Required parameter missing';	
	}
			echo json_encode($response);
		
	
	}
	
	public function checkIfEmployeeTypeExisit($emp_type,$vendor_id){
		
		$query=$this->db->query("select * from employee_type where type='".$emp_type."' and vendor_id='".$vendor_id."' ");
		$num = $query->num_rows();
		return $num;
	}
	public function update_employee_type(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$pc = $this->input->post('employee_type');
		$role_id = $this->input->post('role_id');
		
		if(!empty($role_id)){
				$query = $this->db->query("update employee_type set type='".$pc."' where emp_type_id='".$role_id."' ");
		 $response['status'] = 1;
		    $response['message'] = 'Employee type updated successfully';
		}else{
		$num = $this->checkIfEmployeeTypeExisit($pc,$vendor_id);
		if($num==0){
			
			$query2 = $this->db->query("insert into employee_type set type='".$pc."', is_active='1', vendor_id='".$vendor_id."', date_created='".date('Y-m-d H:i')."' ");
			if($query2){
			$response['status'] = 1;
		    $response['message'] = 'Employee type updated successful';	
			}else{
				$response['status'] = 0;
				$response['message'] = 'Something went wrong';	
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Employee type already exists';	
		}
		
		 
		}
		
	}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing';	
	}
			echo json_encode($response);
		

	}
	function getpermissionCategory(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$query = $this->db->query("select * from role where is_active='1' and role_id NOT IN(5) and vendor_id='".$vendor_id."' order by role_id desc ");
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
		    $response['message'] = 'Required parameter missing';	
	}
			echo json_encode($response);
	}
	
	public function checkIfPositionExist($role,$vendor_id){
		
		$query = $this->db->query("select role_id from role where role_name='".$role."' and vendor_id='".$vendor_id."' ");
		$num = $query->num_rows();
		return $num;
	}
	
	public function update_permissionCategory(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$pc = $this->input->post('permission_category');
		$role_id = $this->input->post('role_id');
		
		if(!empty($role_id)){
		$query = $this->db->query("update role set role_name='".$pc."' where role_id='".$role_id."' ");
		$response['status'] = 1;
		$response['message'] = 'Position updated successfully';	
		
		}else{
		$num = $this->checkIfPositionExist($pc,$vendor_id);
		if($num==0){
			$query = $this->db->query("insert into role set role_name='".$pc."', is_active='1', vendor_id='".$vendor_id."' ");
			if($query){
			$response['status'] = 1;
		    $response['message'] = 'Position added successfully';	
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Something went wrong';	
		}
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Position already exists';	
		}
		
		}
		
	}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing';	
	}
			echo json_encode($response);
		

	}
	public function getPosition(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		$result = $this->db->query('select role_id,role_name from role where is_active="1" and vendor_id="'.$vendor_id.'" and role_id NOT IN(5) order by role_id desc')->result();
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
		    $response['message'] = 'Required parameter missing';	
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
			$num = $this->checkIfPositionExist($position_name,$vendor_id);
			if($num==0){
		$query = $this->db->query("insert into role set role_name='".$position_name."', is_active='1', vendor_id='".$vendor_id."' ");
		
		if($query){
			$response['status'] = 1;
		    $response['message'] = 'Position added successfully';	
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Something went wrong';	
		}
		
			}else{
				$response['status'] = 0;
		    $response['message'] = 'Position already exist';	
			}
		
		}else{
			$query = $this->db->query("update role set role_name='".$position_name."' where role_id='".$position_id."' ");
		if($query){
			$response['status'] = 1;
		    $response['message'] = 'Position updated successfully';	
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
	function deletePosition(){
		$position_id=$this->input->post('position_id');
		if(!empty($position_id)){
			$query=$this->db->query("delete from role where role_id='".$position_id."'");
			if($query){
			$response['status'] = 1;
		    $response['message'] = 'Position deleted successfully';	
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Something went wrong';	
		}
		}
		else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing';	
	}
			echo json_encode($response);

	}
	function deleteEmployeeType(){
		$employee_type_id=$this->input->post('employee_type_id');
		if(!empty($employee_type_id)){
			$query=$this->db->query("delete from employee_type where emp_type_id='".$employee_type_id."'");
			if($query){
			$response['status'] = 1;
		    $response['message'] = 'Employee type deleted successfully';	
		}else{
			$response['status'] = 0;
		    $response['message'] = 'Something went wrong';	
		}
		}
		else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing';	
	}
			echo json_encode($response);

	}
	public function getCancellationPolicy(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
		
		$query = $this->db->query("select *, is_active as is_enable from cancellation_policy  where vendor_id='".$vendor_id."' and apt_type=1 ");
		
		$result = $query->row();
		$query1 = $this->db->query("select * from cancellation_policy where vendor_id='".$vendor_id."' and apt_type=2 ")->row();
		$getArray=(Object)[];

			if(!empty($result)){
				
					/*if(!empty($query1)){
						$groupData=$query1;
					}else{
						$groupData=(Object)[];
					}*/
					
					$getArray=$result;
					$getArray->field1_group=$query1->field1;
					$getArray->field2_group=$query1->field2;
					$getArray->field2_type_group=$query1->field2_type;
					$getArray->field3_group=$query1->field3;
					$getArray->field3_type_group=$query1->field3_type;
					$getArray->policy_text_group=$query1->policy_text;
					$getArray->is_sms_group=$query1->is_sms;
					$getArray->is_email_group=$query1->is_email;
					$getArray->apt_type_group=$query1->apt_type;
					$getArray->is_enable_group=$query1->is_active;
			$response['status'] = 1;
		    $response['message'] = 'Data found';
		    $response['result']=$getArray;	
		   // $response['group_data']=$groupData;
		}else{
			$response['status'] = 1;
			  $response['result']=$getArray;	
		    $response['message'] = '';	
		}
		
	}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing';	
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
			$response['status'] = 0;
		    $response['message'] = '';	
		}
		}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing';	
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
		    $response['message'] = 'Required parameter missing';	
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
		    $response['message'] = 'Update successful';
		  	
        } else {
           $response['status'] = 0;
		    $response['message'] = 'Something went wrong';
		    
        }
	}else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing';	
	}
			echo json_encode($response);
      
    }
    public function giftcard(){
    	$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
			$path = "https://".$_SERVER['HTTP_HOST'].'/assets/img/giftcard';
		$response['amount']=$this->db->query("select * from giftcard_settings where vendor_id='".$vendor_id."' ")->row();
		$response['giftcard_images']=$this->db->query("select gc_image_id,CONCAT('$path','/',if(image='','noimage.png',image)),type  from giftcard_images order by gc_image_id desc ")->result();
		
   		 }else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing';	
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

       // $active_inactive = $this->input->post("active_inactive");

		//$active_inactive = [{"image:id":"1","is_active":"1"},{"image:id":"2","is_active":"1"},{"image:id":"3","is_active":"0"}]
		
		$update = $this->db->query("update giftcard_settings set preset_amount_1='".$preset_amount_1."', preset_amount_2='".$preset_amount_2."', preset_amount_3='".$preset_amount_3."', preset_amount_4='".$preset_amount_4."' where vendor_id='".$vendor_id."' ");
       
		if ($update) {
			
			

            $response['status'] = 1;
		    $response['message'] = 'Gift card updated successfully';
		  	
        } else {
  	        $response['status'] = 0;
		    $response['message'] = 'Something went wrong';
	    }
        }else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing';	
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
		    $response['message'] = 'Required parameter missing';	
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
        $category_id = $this->input->post("category_id");
		
		
		
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
            "vendor_id" => $vendor_id,
            "category_id" => $category_id
        );
        $query=$this->db->insert('coupon',$insert_data);
        if ($query) {
            $response['status'] = 1;
		    $response['message'] = 'Discount added successfully';
		  	
        } else {
  	        $response['status'] = 0;
		    $response['message'] = 'Something went wrong';
	    }
	    }else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing';	
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
		    $response['message'] = 'Required parameter missing';	
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
		    $response['message'] = 'Discount added successfully';
		  	
        } else {
  	        $response['status'] = 0;
		    $response['message'] = 'Something went wrong';
	    }
	   }else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing';	
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
		    $response['message'] = 'Discount updated successfully';
		  	
        } else {
  	        $response['status'] = 0;
		    $response['message'] = 'Something went wrong';
	    }
	   }else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing';	
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
		    $response['message'] = 'Required parameter missing';	
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
		    $response['message'] = 'IOU added successfully';
		  	
        } else {
  	        $response['status'] = 0;
		    $response['message'] = 'Something went wrong';
	    }
         }else{
		    $response['status'] = 0;
		    $response['message'] = 'Required parameter missing';	
		}
		echo json_encode($response);
        
    }
    public function getGalleryImage(){
    	$vendor_id=$this->input->post('vendor_id');
    	if(!empty($vendor_id)){
    		$path = "https://".$_SERVER['HTTP_HOST'].'/assets/img/gallery';
		$query = $this->db->query("select galary_id,galary_name,CONCAT('$path','/',if(main_image='','noimage.png',main_image)) as photo, note from gallery where vendor_id='".$vendor_id."'");
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
		    $response['message'] = 'Required parameter missing';	
		}
		echo json_encode($response);
		
	}
	public function add_photo_gallery()
    {
        
       	$vendor_id=$this->input->post('vendor_id');
       	$note=$this->input->post('note');
       	$gallery_id=$this->input->post('gallery_id');
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
       
       
	   if($gallery_id==''){
        $insert_data = array(
                    'galary_name' => $gal_name,
                    'main_image' => $photo_name,
                    'vendor_id'=>$vendor_id,
                    'note'=>$note
                    
                 );
        $query=$this->db->insert('gallery', $insert_data);
		$msg = "Photo added successfully!";
		
	   }else{
		   $query = $this->db->query("update gallery set galary_name='".$gal_name."', note='".$note."' where galary_id='".$gallery_id."' ");
		   $msg = "Photo updated successfully!";
	   }
          if($query){
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
    public function deleteimage(){
    	$gallery_id=$this->input->post('gallery_id');
    	$vendor_id=$this->input->post('vendor_id');
    	if(!empty($gallery_id)){
        $query=$this->db->query('DELETE FROM gallery WHERE  galary_id="'.$gallery_id.'" ');
        if($query){
           $response['status'] = 1;
		    $response['message'] = 'Photo deleted successfully';
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
		    $response['message'] = 'Required parameter missing';	
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
		    $response['message'] = 'Required parameter missing';	
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
		$response['message'] = 'Appointment confirmed successfully';
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
		    $response['message'] = 'Required parameter missing';	
		}
		echo json_encode($response);
    }
	
	
	
	public function updateNotificationCriteria()
    {
		
		//echo "<pre>";print_r($_POST);exit;
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
		// $notification_criteria_type = $this->input->post('notification_criteria_type');
		
		
		//[{"nc_id":"1","text_notify":"0","email_notify":"1","push_notify":"1","ducation":"72","duration_type","1"}]
		
		
		if(!empty($vendor_id)&& !empty($notification)){
			
		//	$q = $this->db->query("update settings set value='".$notification_criteria_type."' where field='notification_criteria_type' and vendor_id='".$vendor_id."' ");

			$notification_data = json_decode($notification);
			
			foreach($notification_data as $nd){
				
				
				$duration = $nd->$duration;
				//echo "update notification_criteria set duration='".$nd->duration."',duration_type='".$nd->duration_type."', text_notify='".$nd->text_notify."',email_notify='".$nd->email_notify."', push_notify='".$nd->push_notify."' where nc_id='".$nd->nc_id."' ";
			  $query = $this->db->query("update notification_criteria set duration='".$nd->duration."',duration_type='".$nd->duration_type."', text_notify='".$nd->text_notify."', email_notify='".$nd->email_notify."', push_notify='".$nd->push_notify."' where nc_id='".$nd->nc_id."' ");
			}
			
			$response['status'] = 1;
			$response['message'] = 'Notification criteria updated successfully';
			
		}
		
		else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
			
			
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
		$vendor_id = $this->input->post('vendor_id');
		$policy_text = $this->input->post('policy_text');
		$is_sms = $this->input->post('is_sms');
		$is_email = $this->input->post('is_email');
		$is_enable = $this->input->post('is_enable');
		
		$field1_group = $this->input->post('field1_group');
		$field2_group = $this->input->post('field2_group');
		$field2_type_group = $this->input->post('field2_type_group');
		$field3_group = $this->input->post('field3_group');
		$field3_type_group = $this->input->post('field3_type_group');
		
		$policy_text_group = $this->input->post('policy_text_group');
		$is_sms_group = $this->input->post('is_sms_group');
		$is_email_group = $this->input->post('is_email_group');
		$is_enable_group = $this->input->post('is_enable_group');
		
		
		
		if(!empty($vendor_id)){
		
		$query = $this->db->query("update cancellation_policy set field1='".$field1."', field2='".$field2."', field2_type='".$field2_type."', field3='".$field3."', field3_type='".$field3_type."', policy_text='".$policy_text."', is_sms='".$is_sms."', is_email='".$is_email."',is_active='".$is_enable."' where vendor_id='".$vendor_id."' and apt_type='1'  ");
		
		$query2 = $this->db->query("update cancellation_policy set field1='".$field1_group."', field2='".$field2_group."', field2_type='".$field2_type_group."', field3='".$field3_group."', field3_type='".$field3_type_group."', policy_text='".$policy_text_group."', is_sms='".$is_sms_group."', is_email='".$is_email_group."', is_active='".$is_enable_group."' where vendor_id='".$vendor_id."' and apt_type='2' ");
		
		if($query){
			
			$response['status'] = 1;
			$response['message'] = 'Cancellation policy updated successfully';
		}
		else{
			
			$response['status'] = 0;
			$response['message'] = 'Something went wrong';
		
		}
		
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		
		}
		
		echo json_encode($response);
	}
	
	
	public function addTax(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$product_tax = $this->input->post('product_tax');
		//$service_tax = $this->input->post('service_tax');
		
		$product_tax_future = $this->input->post('product_tax_future');
		//$service_tax_future = $this->input->post('service_tax_future');
		
		
		
		if(!empty($vendor_id)){
			
			
// product_tax = [{"tax_type":"tax1","tax_rate":"2.00","start_date":"2021-09-11","description":"this is tax","tax_name":"tax a"},{"tax_type":"tax2","tax_rate":"3.00","start_date":"2021-09-11","description":"this is tax","tax_name":"tax a"},{"tax_type":"tax3","tax_rate":"4.00","start_date":"2021-09-11","description":"this is tax","tax_name":"tax a"}]

// service_tax = [{"tax_type":"tax1","tax_rate":"2.00","start_date":"2021-09-11","description":"this is tax"},{"tax_type":"tax2","tax_rate":"3.00","start_date":"2021-09-11","description":"this is tax"},{"tax_type":"tax3","tax_rate":"4.00","start_date":"2021-09-11","description":"this is tax"}]
			
			$product_tax = json_decode($product_tax);
			//$service_tax = json_decode($service_tax);
			$product_tax_future = json_decode($product_tax_future);
			//$service_tax_future = json_decode($service_tax_future);
			
		//	echo '<pre>';print_r($product_tax_future);die;
			foreach($product_tax as $pt){
				
				
			
				//$start_date = date('Y-m-d',strtotime($pt->start_date));
				
				$tax_update_qty = $this->db->query("update tax_product set tax_rate='".$pt->tax_rate."', type='current', description='".$pt->tax_name."' where tax_type='".$pt->tax_type."' and vendor_id='".$vendor_id."' and type='current' ");
			
			}
			
			/* foreach($service_tax as $st){
				
				$start_date = date('Y-m-d',strtotime($st->start_date));
				$tax_update_qty = $this->db->query("update tax_service set tax_rate='".$st->tax_rate."', type='current', description='".$st->tax_name."' where tax_type='".$st->tax_type."' and vendor_id='".$vendor_id."',  and type='current' ");
			
			} */
			
			
			// udpate future tax
			
			foreach($product_tax_future as $ptf){
				
			
			// [{"tax_name":"pr","tax_type":"Tax1","tax_rate":"3","start_date":"2021-12-22","description":""}, {"tax_name":"","tax_type":"Tax2","tax_rate":"","start_date":"","description":""}, {"tax_name":"","tax_type":"Tax3","tax_rate":"","start_date":"","description":""}]
				//$start_date = date('Y-m-d',strtotime($pt->start_date));
				
				$tax_update_qty = $this->db->query("update tax_product set tax_rate='".$ptf->tax_rate."', type='future', description='".$ptf->tax_name."',start_date='".$ptf->start_date."' where tax_type='".ucfirst($ptf->tax_type)."' and vendor_id='".$vendor_id."' and type='future' ");
			
			}
			
			/* foreach($service_tax_future as $st){
				
				$start_date = date('Y-m-d',strtotime($st->start_date));
				$tax_update_qty = $this->db->query("update tax_service set tax_rate='".$st->tax_rate."', type='future', description='".$st->tax_name."',start_date='".$start_date."' where tax_type='".$st->tax_type."' and vendor_id='".$vendor_id."'  and type='future' ");
			
			} */
			
			$response['status'] = 1;
			$response['message'] = 'Tax updated successfully';
			
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		
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
				$response['message'] = 'Receipt updated successfully';
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
			$response['message'] = 'Required parameter missing';
			
		}
		
		echo json_encode($response);
	}
	
	public function DeleteData(){
		$vendor_id = ""; // it should come from api
		$table_name=$this->input->post('table_name');
		$row_id=$this->input->post('row_id');
		$status=$this->input->post('status'); // status 1 for active 0 for inactive
		$type=$this->input->post('type');//type 1 for delete, 2 for status
		
		if($table_name=='product'){
			$row_name='product_id';
			$con2 = "Product ";
			$sub_table = "";
		}
		if($table_name=='brand'){
			$row_name='brand_id';
			$con2 = "Brand ";
			$sub_table = "";
		}
		if($table_name=='supplier'){
			$row_name='supplier_id';
			$con2 = "Vendor ";
			$sub_table = "";
		}
		if($table_name=='category'){
			$row_name='category_id';
			$con2 = "Product category ";
			$sub_table = "product";
		}
		if($table_name=='service_category'){
			$row_name='category_id';
			$con2 = "Service category ";
			$sub_table = "service";
		}
		
		
		if(!empty($table_name) && !empty($row_id) && !empty($type)){
			
			if($type==1){
			//echo 'update "'.$table_name.'" set is_delete=1 where "'.$row_name.'"="'.$row_id.'" ';die;
			$delete=$this->db->query('update '.$table_name.' set is_delete=1 where '.$row_name.'="'.$row_id.'" ');
			if($sub_table!=''){
					$this->db->query("update $sub_table set is_delete='1' where $row_name='".$row_id."' and vendor_id='".$vendor_id."' ");
				}
			
			$msg = $con2." deleted successfully!";
			}else if($type==2){
				
				$delete=$this->db->query('update '.$table_name.' set is_active='.$status.' where '.$row_name.'="'.$row_id.'" ');
				if($sub_table!=''){
					$this->db->query("update $sub_table set is_active='".$status."' where $row_name='".$row_id."' and vendor_id='".$vendor_id."' ");
				}

				if($status==1){
					
					$con = "activated";
				}else{
					$con = "Inactivated";
				}
				$msg = $con2 ." $con successfully!";
				
			}
			if($delete){
				$response['status'] = 1;
				$response['message'] = $msg;
			}else{
				$response['status'] = 0;
				$response['message'] = 'Something Wrong';
			}
		}
		else{
			$response['status'] = 0;
			$response['result'] = [];
			$response['message'] = 'Required parameter missing';
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
				$response['message'] = 'Discount deleted successfully';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
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
				$response['message'] = 'Discount deleted successfully';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
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
		     	$response['message'] = 'Pin updated successfully';	
                }
            }else{
                $response['status'] = 0;
		     	$response['message'] = 'Pin does not match';	
                
                
            }
        }else{
           
            $response['status'] = 0;
		    $response['message'] = 'Wrong current pin';
        }
    	}else{
		 $response['status'] = 0;
		   	$response['message'] = 'Required parameter missing';	
		}
		//$response['message'] = $old_passwd;
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
				$response['message'] = 'Data not found';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
	}

	
	
	public function getGiftcardTemplate(){
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$type = $this->input->post('type');
		
		if(!empty($vendor_id)){
			
			if($type=='checkout'){
				$is_active=  " AND is_active='1' ";
			}else{
				$is_active = "";
			}
			
			$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
			$path = $actual_link.'/assets/img/giftcard/';
		
			
			$query = $this->db->query("select gc_image_id, CONCAT('$path',image) as giftcard_image, IFNULL(type,'') as type,is_active from giftcard_images where vendor_id='".$vendor_id."' $is_active order by gc_image_id desc ");
			//echo $this->db->last_query();die;
			if($query->num_rows()>0){
				
				$result = $query->result();
				
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
				
			}else{
				
				$response['status'] = 0;
				$response['result'] = [];
				$response['message'] = 'Data not found';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
	}

	
	public function getGiftCertificateTemplate(){
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$type = $this->input->post('type');

		if($type=='checkout'){
			$is_active = " and is_active=1";
		}else{
			$is_active = "";
		}
		
		if(!empty($vendor_id)){
			
			
			$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
			$path = $actual_link.'/assets/img/certificate/';
		
			
			$query = $this->db->query("select image_id, CONCAT('$path',image) as certificate_image, IFNULL(name,'') as type,is_active from gift_certificate_images where vendor_id='".$vendor_id."' $is_active order by image asc ");
			//echo $this->db->last_query();die;
			if($query->num_rows()>0){
				
				$result = $query->result();
				
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
				
			}else{
				
				$response['status'] = 0;
				$response['result'] = [];
				$response['message'] = 'Data not found';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
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
		$active_inactive = $this->input->post('active_inactive');
		
		if(!empty($vendor_id) && !empty($preset_amount_1) && !empty($preset_amount_2) && !empty($preset_amount_3) && !empty($preset_amount_4)){
			
			
			$query = $this->db->query("update giftcard_settings set preset_amount_1='".$preset_amount_1."', preset_amount_2='".$preset_amount_2."', preset_amount_3='".$preset_amount_3."', preset_amount_4='".$preset_amount_4."', modified_date='".date('Y-m-d H:i:s')."' where vendor_id='".$vendor_id."' ");
			//echo $this->db->last_query();die;
			if($query){

				$active_inactive = json_decode($active_inactive);

			foreach($active_inactive as $toggle){

				$this->db->query("update giftcard_images set is_active='".$toggle->is_active."' where gc_image_id='".$toggle->image_id."' ");
			}
				
				$response['status'] = 1;
				$response['message'] = 'Gift card updated successfully';
				
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
	
	
	
	public function updateBatchCloseTime(){
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$time = $this->input->post('time');
		$is_auto_close = $this->input->post('is_auto_close'); // 0 - Off 1- Onn
		
		if(!empty($vendor_id) && !empty($time)){
			
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
				$response['message'] = 'Batch updated successfully';
				
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
	
	public function getBatchCloseTime(){
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select value as is_auto_close, TIME_FORMAT(first_time,'%h:%i %p') as batch_close_time from settings where field='batch_close_time' and vendor_id='".$vendor_id."' ");
			
			if($query->num_rows()>0){
				$result = $query->row();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
				
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
	
	

	
	
	public function getGiftCertificateSetings(){
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
		
		$path = $actual_link.'/assets/img/certificate/';
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select max_amount, 'A Credit Certificate is an electronic voucher issued to customers via email for one-time use. The amount issued is redeemed on a single transaction.' as note from gift_settings where vendor_id='".$vendor_id."' ");
			
			$query2 = $this->db->query("select image_id, CONCAT('$path',image) AS image, name,is_active from gift_certificate_images where vendor_id='".$vendor_id."' order by image_id desc");
			
				$result = $query->row();
				$certificate_images = $query2->result();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['certificate_images'] = $certificate_images;
				$response['message'] = '';
				
			
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
	}
	
	
	public function updateGiftCertifcateSettings(){
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$max_amount = $this->input->post('max_amount');
		$active_inactive = $this->input->post('active_inactive');
		
		//json data
		//$active_inactive = [{"image:id":"1","is_active":"1"},{"image:id":"2","is_active":"1"},{"image:id":"3","is_active":"0"}]

		
		if(!empty($vendor_id) && !empty($max_amount)){
			
			

			$query = $this->db->query("update gift_settings set max_amount='".$max_amount."' where vendor_id='".$vendor_id."' ");
			//echo $this->db->last_query();die;
			if($query){

				$active_inactive = json_decode($active_inactive);

				foreach($active_inactive as $toggle){

					$this->db->query("update gift_certificate_images set is_active='".$toggle->is_active."' where image_id='".$toggle->image_id."' ");
				}
				
				$response['status'] = 1;
				$response['message'] = 'Gift certificate updated successfully';
				
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
	

	
	public function equipmentList(){
		
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		
		if(!empty($vendor_id)){
			
			$path = "https://".$_SERVER['HTTP_HOST'].'/assets/img/';
			$query = $this->db->query("select equipment_id, CONCAT('$path',image) as equipment_image from equipment where device_type='1' and vendor_id='".$vendor_id."' ");
			//echo $this->db->last_query();die;
			if($query->num_rows()>0){
				$result = $query->result();
				$response['status'] = 1;
				$response['result'] = $result;
				
			}else{
				
				$response['status'] = 0;
				$response['message'] = 'No data found';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
		
	}

	
	
	function importCustomer_test(){
		//error_reporting(1);
		
		$response['status'] = 0;
		$response['message'] = '';
		mb_internal_encoding("8bit");
		$vendor_id = $this->input->post('vendor_id');
		  $excel=$_FILES['excel']['name'];
		  //error_reporting(E_ALL ^ E_NOTICE);
		  require_once(APPPATH.'libraries/Excel1/reader.php');
		  $data = new Spreadsheet_Excel_Reader();
		  $data->setOutputEncoding("utf-8", "windows-1251");
		//echo APPPATH;die;
	
		/* if($_SERVER['HTTP_HOST']=='localhost'){
			$upload_path = 'D:/xampp/htdocs/salon/salon/assets/customer_data/';
		}else{
			$upload_path = '/home/admin/web/ec2-13-234-125-210.ap-south-1.compute.amazonaws.com/public_html/salonpos/assets/customer_data/';
		} */
		
		$upload_path =  getcwd().'/assets/customer_data/';
		
          move_uploaded_file($_FILES["excel"]["tmp_name"],"$upload_path".$excel);
		//  echo 'hello';die;
          $target_path = "$upload_path".$excel;
	      $data->read($target_path);
	      setlocale(LC_ALL, 'en_GB');
	      $new_data = $data->sheets[0]['cells'];
	      $i=1; //$x=1;   
		  $s=1;
		//echo sizeof($new_data);die;
        for($x=2; $x <=sizeof($new_data); $x++) 
		{ // loop start
           $i++; 
          /***********************************************************Excel  ********************************/
		   if(isset($data->sheets[0]["cells"][$x][1])){
			$name = $data->sheets[0]["cells"][$x][1];
			$customer_name = explode(' ',$name);
			$firstname = $customer_name[0];
		    $lastname = $customer_name[1];
		   
		   }
		   
		   
		   if(isset($data->sheets[0]["cells"][$x][2])){
				$email = $data->sheets[0]["cells"][$x][2];
		   }
           if(isset($data->sheets[0]["cells"][$x][3])){
			$phone = $data->sheets[0]["cells"][$x][3];
		   }
		   if(isset($data->sheets[0]["cells"][$x][4])){
			$gender = $data->sheets[0]["cells"][$x][4];
		   }
		   if(isset($data->sheets[0]["cells"][$x][5])){
			$address = $data->sheets[0]["cells"][$x][5];
			
		   }
		   if(isset($data->sheets[0]["cells"][$x][6])){
		   $city = $data->sheets[0]["cells"][$x][6];
		 
		   }
		   if(isset($data->sheets[0]["cells"][$x][7])){
		    $state = $data->sheets[0]["cells"][$x][7];
			
		   }
		   
		   if(isset($data->sheets[0]["cells"][$x][8])){
		   $zip = $data->sheets[0]["cells"][$x][8];
		   }
		   if(isset($data->sheets[0]["cells"][$x][9])){
		   $customer_note = $data->sheets[0]["cells"][$x][9];
		   }
		   if(isset($data->sheets[0]["cells"][$x][10])){
		   $emergency_name = $data->sheets[0]["cells"][$x][10];
		   }
		   if(isset($data->sheets[0]["cells"][$x][11])){
		   $emergency_relation = $data->sheets[0]["cells"][$x][11];
		   }
		   if(isset($data->sheets[0]["cells"][$x][12])){
		   $emergency_contact = $data->sheets[0]["cells"][$x][12];
		   }
		   if(isset($data->sheets[0]["cells"][$x][13])){
			$birthday = $data->sheets[0]["cells"][$x][13];
		   }
		   if(isset($data->sheets[0]["cells"][$x][14])){
		   $anniversary = $data->sheets[0]["cells"][$x][14];
		   }
		   if(isset($data->sheets[0]["cells"][$x][15])){
		   $occupation = $data->sheets[0]["cells"][$x][15];
		   }
		  
		 // echo $email;die;
		  
		
		
			//mysql_set_charset('utf8');
		      $file_name=$_FILES['excel']['name'];
		    // echo $file_name;die;
			  $crDate=date('Y-m-d H:i:s');
			  $num = $this->customer->checkEmailExists($email);
			  if($num==0){
			  $query = $this->db->query("insert into login set email='".$email."', role_id='2', created_date='$crDate', vendor_id='".$vendor_id."',is_delete='0', is_active='0' ");
			  $login_id = $this->db->insert_id();
			  
			  $query2= $this->db->query("insert into customer set firstname='$firstname', lastname='$lastname', email='$email', mobile_phone='$phone', gender='$gender', address='$address', city='$city', state='$state', pincode='$zip',note='$customer_note', emergency_contact_name='$emergency_name', emergency_relationship='$emergency_relation', emergency_phone='$emergency_contact',birthday='$birthday', anniversary='$anniversary', occupation='$occupation', login_id='$login_id'  ");
			  
			  if($query2){
				  
				  $response['status'] = 1;
				  $response['messsage'] = 'Data imported successfully';
			  }else{
				  
				  $response['status'] = 1;
				  $response['messsage'] = 'Something went wrong';
				  
			  }
			  
			  
			  
				}  
				
				echo json_encode($response);
			
	  } // else end
	  
	  
      
	
	}



	
	function importCustomer(){
		
		$vendor_id = $this->input->post('vendor_id');
		$type = $this->input->post('type');
		$excel=$_FILES['excel']['name'];


			//error_reporting(1);
			require_once(APPPATH.'libraries/Excel1/reader.php'); //this need to be uncomment, after uncomment its giving error on server
			
			//error_reporting(E_ALL ^ E_NOTICE);
			$data = new Spreadsheet_Excel_Reader();
			$data->setOutputEncoding("utf-8", "windows-1251");
		  //echo APPPATH;die;
	  
		  

		if($type=='customer'){

			/* if($_SERVER['HTTP_HOST']=='localhost'){
				$upload_path = 'D:/xampp/htdocs/salon/salon/assets/customer_data/';
			}else{
				$upload_path =  getcwd().'/assets/customer_data/';
				
			} */

			$upload_path =  getcwd().'/assets/customer_data/';
		

  //echo $upload_path;die;
	  
		move_uploaded_file($_FILES["excel"]["tmp_name"],"$upload_path".$excel);
	  
		$target_path = "$upload_path".$excel;
		$data->read($target_path);
		setlocale(LC_ALL, 'en_GB');
		$new_data = $data->sheets[0]['cells'];
		$i=1; //$x=1;   
		$s=1;
  //	echo sizeof($new_data);die;
	  for($x=2; $x <=sizeof($new_data); $x++) 
	  { // loop start
		 $i++; 
		/***********************************************************Excel  ********************************/
		 if(isset($data->sheets[0]["cells"][$x][1])){
		  $firstname = $data->sheets[0]["cells"][$x][1];
		  $lastname = $data->sheets[0]["cells"][$x][2];
		  
		 
		 }

		 
		 
		 if(isset($data->sheets[0]["cells"][$x][3])){
			  $email = $data->sheets[0]["cells"][$x][3];
		 }
		 if(isset($data->sheets[0]["cells"][$x][4])){
		  $phone = $data->sheets[0]["cells"][$x][4];
		 }
		 if(isset($data->sheets[0]["cells"][$x][5])){
		  $gender = $data->sheets[0]["cells"][$x][5];
		 }
		 
		 
		 if(isset($data->sheets[0]["cells"][$x][6])){
		 $address = $data->sheets[0]["cells"][$x][6];
		 }
		 if(isset($data->sheets[0]["cells"][$x][7])){
		 $city = $data->sheets[0]["cells"][$x][7];
		 }
		 if(isset($data->sheets[0]["cells"][$x][8])){
		 $state = $data->sheets[0]["cells"][$x][8];
		 }
		 if(isset($data->sheets[0]["cells"][$x][9])){
		 $zip = $data->sheets[0]["cells"][$x][9];
		 }
		 if(isset($data->sheets[0]["cells"][$x][10])){
		 $customer_note = $data->sheets[0]["cells"][$x][10];
		 }
		 if(isset($data->sheets[0]["cells"][$x][11])){
		  $emergency_name = $data->sheets[0]["cells"][$x][11];
		 }
		 if(isset($data->sheets[0]["cells"][$x][12])){
		 $emergency_relation = $data->sheets[0]["cells"][$x][12];
		 }
		 if(isset($data->sheets[0]["cells"][$x][13])){
		 $emergency_contact = $data->sheets[0]["cells"][$x][13];
		 }
		 if(isset($data->sheets[0]["cells"][$x][14])){
		 $birthday = $data->sheets[0]["cells"][$x][14];
		 $birthday = date('Y-m-d',strtotime($birthday));
		 }
		 $country = 231; // country_id 231 is usa
		 $state = 3924;
		
	   // echo $email;die;
		
		 $password = $this->generateRandomNumber(6);
	  
		  //mysql_set_charset('utf8');
			$file_name=$_FILES['excel']['name'];
		  // echo $file_name;die;
			$crDate=date('Y-m-d H:i:s');
			$num = $this->checkEmailExists($email,$vendor_id,'2');
			if($num==0){
			$query = $this->db->query("insert into login set email='".$email."', password='".md5($password)."', role_id='2', created_date='$crDate', vendor_id='".$vendor_id."',is_delete='0', is_active='1' ");
			$login_id = $this->db->insert_id();
			
	  $query2= $this->db->query("insert into customer set firstname='$firstname', lastname='$lastname', email='$email', mobile_phone='$phone', gender='$gender', address='$address', city='$city', state_id='$state', pincode='$zip',note='$customer_note', emergency_contact_name='$emergency_name', emergency_relationship='$emergency_relation', emergency_phone='$emergency_contact',  birthday='".$birthday."', login_id='$login_id'  ");
			
			  }  
		  
	} // else end
	
	
	
	 // set_flash('customer_info', "Customer imported successfully.", 1);
	  
	  //redirectToAdmin('customer');
		$response['status'] = 1;
		$response['message'] = 'Customers imported successfully';
	  	

		}else if($type=='employee'){

			

			/* if($_SERVER['HTTP_HOST']=='localhost'){
				$upload_path = 'D:/xampp/htdocs/salon/salon/assets/customer_data/';
			}else{
				$upload_path =  getcwd().'/assets/customer_data/';
				
			} */

			$upload_path =  getcwd().'/assets/employee_data/';
		

  //echo $upload_path;die;
	// echo $upload_path.$excel;die;
		move_uploaded_file($_FILES["excel"]["tmp_name"],"$upload_path".$excel);
		
		
	  
		$target_path = "$upload_path".$excel;
		$data->read($target_path);
		setlocale(LC_ALL, 'en_GB');
		$new_data = $data->sheets[0]['cells'];
		$i=1; //$x=1;   
		$s=1;
  //	echo sizeof($new_data);die;
	  for($x=2; $x <=sizeof($new_data); $x++) 
	  { // loop start
		 $i++; 
		/***********************************************************Excel  ********************************/
		 if(isset($data->sheets[0]["cells"][$x][1])){
		  $firstname = $data->sheets[0]["cells"][$x][1];
		  $lastname = $data->sheets[0]["cells"][$x][2];
		  
		 
		 }

		 
		 
		 if(isset($data->sheets[0]["cells"][$x][3])){
			  $email = $data->sheets[0]["cells"][$x][3];
		 }
		 if(isset($data->sheets[0]["cells"][$x][4])){
		  $phone = $data->sheets[0]["cells"][$x][4];
		 }
		 if(isset($data->sheets[0]["cells"][$x][5])){
		  $address = $data->sheets[0]["cells"][$x][5];
		 }
		 
		 
		 if(isset($data->sheets[0]["cells"][$x][6])){
		 $city = $data->sheets[0]["cells"][$x][6];
		 }
		 if(isset($data->sheets[0]["cells"][$x][7])){
		 $state = $data->sheets[0]["cells"][$x][7];
		 }
		 if(isset($data->sheets[0]["cells"][$x][8])){
		 $zip = $data->sheets[0]["cells"][$x][8];
		 }
		 if(isset($data->sheets[0]["cells"][$x][9])){
			$start_date = $data->sheets[0]["cells"][$x][9];
			$start_date = date('Y-m-d',strtotime($start_date));
			if($start_date=='1970-01-01'){
				$start_date = '';
			}else{
				$start_date = $start_date;
		 	}
		 }
		 else{
			$start_date = '';
		}

		 if(isset($data->sheets[0]["cells"][$x][10])){
			$pay_rate = $data->sheets[0]["cells"][$x][10];
			
		 }
		 if(isset($data->sheets[0]["cells"][$x][11])){
			$pay_type = $data->sheets[0]["cells"][$x][11];
			if($pay_type=='Contract'){
				$pay_type = 1;
			}elseif($pay_type=='Commission'){
				$pay_type = 2;
			}elseif($pay_type=='Hourly'){
				$pay_type = 3;
			}elseif($pay_type=='Salary'){
				$pay_type = 4;
			}else{
				$pay_type = '';
			}
		 }
		 if(isset($data->sheets[0]["cells"][$x][12])){
			$notes = $data->sheets[0]["cells"][$x][12];
		 }
		 if(isset($data->sheets[0]["cells"][$x][13])){
			$employee_position = $data->sheets[0]["cells"][$x][13];
			$position_id = $this->getPositionIdByName($employee_position,$vendor_id); // role_id , title_id, position id are same
		 }

		 if(isset($data->sheets[0]["cells"][$x][14])){
			$employee_type = $data->sheets[0]["cells"][$x][14];
			$employee_type_id = $this->geteEmployeeTypeIdByName($employee_type,$vendor_id); 
		 }

		

		 $country = 231; // country_id 231 is usa
		 $state = 3924;
		
	  
		
		 $password = $this->generateRandomNumber(6);
	  
		  //mysql_set_charset('utf8');
			$file_name=$_FILES['excel']['name'];
		  // echo $file_name;die;
			$crDate=date('Y-m-d H:i:s');
			$num = $this->checkEmailExists($email,$vendor_id,'3');
			if($num==0){
			$query = $this->db->query("insert into login set email='".$email."', password='".md5($password)."', role_id='3', created_date='$crDate', vendor_id='".$vendor_id."',is_delete='0', is_active='1' ");
			$login_id = $this->db->insert_id();
			
	  $query2= $this->db->query("insert into stylist set firstname='$firstname', lastname='$lastname', email='$email', phone='$phone', address='$address', city='$city', country_id='$country', state_id='$state', postal_code='$zip',note='$notes', start_date='$start_date', pay_rate='$pay_rate', pay_type_id='$pay_type',  title_id='".$employee_position."', login_id='$login_id', type='".$employee_type."'  ");

	  $stylist_id = $this->db->insert_id();


	  // Add employee availibility code start here

				  $business_hour = $this->db->query("select days, switch, start_time, end_time from business_hour where vendor_id='".$vendor_id."' order by business_hour_id asc ")->result();

				  foreach($business_hour as $bh){

					$schdule_id=$this->db->query("insert into schedule set stylist_id='".$stylist_id."',days='".$bh->days."',switch='".$bh->switch."',from_time='". $bh->start_time."',to_time='".$bh->end_time."',created_date='". date('Y-m-d h:i:s')."',vendor_id='".$vendor_id."'");

				  }

	

	  // add employee availibility code end here
			
			  }  
		
	} // else end
	
	
	
	 // set_flash('customer_info', "Customer imported successfully.", 1);
	  
	  //redirectToAdmin('customer');
		$response['status'] = 1;
		$response['message'] = 'Employees imported successfully';
	  	


		}else if($type=='vendor'){

			

			/* if($_SERVER['HTTP_HOST']=='localhost'){
				$upload_path = 'D:/xampp/htdocs/salon/salon/assets/customer_data/';
			}else{
				$upload_path =  getcwd().'/assets/customer_data/';
				
			} */

			$upload_path =  getcwd().'/assets/vendor_data/';
		

  //echo $upload_path;die;
	// echo $upload_path.$excel;die;
		move_uploaded_file($_FILES["excel"]["tmp_name"],"$upload_path".$excel);
		
		
	  
		$target_path = "$upload_path".$excel;
		$data->read($target_path);
		setlocale(LC_ALL, 'en_GB');
		$new_data = $data->sheets[0]['cells'];
		$i=1; //$x=1;   
		$s=1;
  //	echo sizeof($new_data);die;
	  for($x=2; $x <=sizeof($new_data); $x++) 
	  { // loop start
		 $i++; 
		/***********************************************************Excel  ********************************/
		 if(isset($data->sheets[0]["cells"][$x][1])){
		  $vendor_name = $data->sheets[0]["cells"][$x][1];
		  
		 
		 }

		 
		 
		 if(isset($data->sheets[0]["cells"][$x][2])){
			$vendor_code = $data->sheets[0]["cells"][$x][2];
	     }

		 if(isset($data->sheets[0]["cells"][$x][3])){
			  $account_number = $data->sheets[0]["cells"][$x][3];
		 }
		 if(isset($data->sheets[0]["cells"][$x][4])){
		  $phone = $data->sheets[0]["cells"][$x][4];
		 }
		 if(isset($data->sheets[0]["cells"][$x][5])){
		  $email = $data->sheets[0]["cells"][$x][5];
		 }
		 
		 
		 if(isset($data->sheets[0]["cells"][$x][6])){
		 $sales_rep_name = $data->sheets[0]["cells"][$x][6];
		 }
		 if(isset($data->sheets[0]["cells"][$x][7])){
		 $sales_rep_phone = $data->sheets[0]["cells"][$x][7];
		 }
		 if(isset($data->sheets[0]["cells"][$x][8])){
		 $address = $data->sheets[0]["cells"][$x][8];
		 }

		if(isset($data->sheets[0]["cells"][$x][9])){
			$city = $data->sheets[0]["cells"][$x][9];
	    }
		if(isset($data->sheets[0]["cells"][$x][10])){
			$state = $data->sheets[0]["cells"][$x][10];
	    }
		if(isset($data->sheets[0]["cells"][$x][11])){
			$zipcode = $data->sheets[0]["cells"][$x][11];
	    }
		if(isset($data->sheets[0]["cells"][$x][12])){
			$website = $data->sheets[0]["cells"][$x][12];
	    }
		if(isset($data->sheets[0]["cells"][$x][13])){
			$order_day_1 = $data->sheets[0]["cells"][$x][13];

	    }
		if(isset($data->sheets[0]["cells"][$x][14])){
			$order_day_2 = $data->sheets[0]["cells"][$x][14];
	    }
		if(isset($data->sheets[0]["cells"][$x][15])){
			$payment_type = $data->sheets[0]["cells"][$x][15];
			
	    }
		if(isset($data->sheets[0]["cells"][$x][16])){
			$credit_term = $data->sheets[0]["cells"][$x][16];
	    }
		


		 

		

		 $country = 231; // country_id 231 is usa
		 $state = 3924;
		
	   // echo $email;die;
		
		 //$password = $this->generateRandomNumber(6);
	  
		  //mysql_set_charset('utf8');
			$file_name=$_FILES['excel']['name'];
		  // echo $file_name;die;
			$crDate=date('Y-m-d H:i:s');
			$email_num = $this->checkIfVendorEmailAlreadyExist($email,$vendor_id);
			$vendor_code_num = $this->checkIfVendorCodeAlreadyExist($email,$vendor_id);
			if($email_num==0 && $vendor_code_num==0){
		
			
	  $query2= $this->db->query("insert into supplier set supplier_logo='noimage.png', supplier_name='$vendor_name', supplier_code='$vendor_code', account_no='$account_number', phone='$phone', email='$email', sales_rep_name='$sales_rep_name', sales_rep_phone='$sales_rep_phone', address='$address',city='$city', state='$state', pincode='$zipcode', website='$website',  order_day1='".$order_day_1."', order_day2='$order_day_2', payment_option='".$payment_type."', credit_term_days='".$credit_term."', vendor_id='".$vendor_id."'  ");
			}
			  
		  
	} // else end
	
	
	
	 // set_flash('customer_info', "Customer imported successfully.", 1);
	  
	  //redirectToAdmin('customer');
		$response['status'] = 1;
		$response['message'] = 'Vendors imported successfully';
	  	


	}else if($type=='product'){

		

		/* if($_SERVER['HTTP_HOST']=='localhost'){
			$upload_path = 'D:/xampp/htdocs/salon/salon/assets/customer_data/';
		}else{
			$upload_path =  getcwd().'/assets/customer_data/';
			
		} */

		$upload_path =  getcwd().'/assets/product_data/';
	

//echo $upload_path;die;
// echo $upload_path.$excel;die;
move_uploaded_file($_FILES["excel"]["tmp_name"],"$upload_path".$excel);
	
  
	$target_path = "$upload_path".$excel;
	$data->read($target_path);
	setlocale(LC_ALL, 'en_GB');
	$new_data = $data->sheets[0]['cells'];
	$i=1; //$x=1;   
	$s=1;
//	echo sizeof($new_data);die;
  for($x=2; $x <=sizeof($new_data); $x++) 
  { // loop start
	 $i++; 
	/***********************************************************Excel  ********************************/
	 if(isset($data->sheets[0]["cells"][$x][1])){
	  $product_name = $data->sheets[0]["cells"][$x][1];
	 
	 }

	 
	 
	 if(isset($data->sheets[0]["cells"][$x][2])){
		$brand = $data->sheets[0]["cells"][$x][2];
		
		$brand_id = $this->getBrandId($brand,$vendor_id);
		
	 }
	

	 if(isset($data->sheets[0]["cells"][$x][3])){
		  $category = $data->sheets[0]["cells"][$x][3];
		  $category_id = $this->getCategoryId($category,$vendor_id);
		 
	 }
	 if(isset($data->sheets[0]["cells"][$x][4])){
	  $barcode_id = $data->sheets[0]["cells"][$x][4];
	  
	 }
	 if(isset($data->sheets[0]["cells"][$x][5])){
	  $sku = $data->sheets[0]["cells"][$x][5];
	  
	 }
	 
	 
	 if(isset($data->sheets[0]["cells"][$x][6])){
	 $current_cost = $data->sheets[0]["cells"][$x][6];
	 
	 }
	 if(isset($data->sheets[0]["cells"][$x][7])){
	 $item_selling_price = $data->sheets[0]["cells"][$x][7];
	 
	 }
	 if(isset($data->sheets[0]["cells"][$x][8])){
	 $qty_in_stock = $data->sheets[0]["cells"][$x][8];
	 
	 }

	if(isset($data->sheets[0]["cells"][$x][9])){
		$low_qty_alert = $data->sheets[0]["cells"][$x][9];
		
	}
	if(isset($data->sheets[0]["cells"][$x][10])){
		$minimum_on_hand = $data->sheets[0]["cells"][$x][10];
		
	}
	if(isset($data->sheets[0]["cells"][$x][11])){
		$par_value = $data->sheets[0]["cells"][$x][11];
		
	}
	if(isset($data->sheets[0]["cells"][$x][12])){
		$commission_type = $data->sheets[0]["cells"][$x][12];
		
	}
	if(isset($data->sheets[0]["cells"][$x][13])){
		$commission = $data->sheets[0]["cells"][$x][13];
		

	}
	if(isset($data->sheets[0]["cells"][$x][14])){
		$business_use = $data->sheets[0]["cells"][$x][14];
	
		if($business_use=='Yes'){
			$business_use = '1';
		}else{
			$business_use = '0';
		}
	}


	  //mysql_set_charset('utf8');
		$file_name=$_FILES['excel']['name'];
	  // echo $file_name;die;
		$crDate=date('Y-m-d H:i:s');
	
	
	$check_name = $this->checkIfProductNameAlreadyExist($product_name,$vendor_id);
	if($check_name=='0'){
		$query2= $this->db->query("insert into product set product_name='".$product_name."',main_image='noimage.png',barcode_id='".$barcode_id."',brand_id='".$brand_id."',category_id='".$category_id."',sku='".$sku."',quantity='".$qty_in_stock."',purchase_price='".$current_cost."',price_retail='".$item_selling_price."',low_qty_warning='".$low_qty_alert."',minimum_on_hand='".$minimum_on_hand."',par_value='".$par_value."',commission_type='".$commission_type."',commission_amount='".$commission."',business_use='".$business_use."',vendor_id='".$vendor_id."', is_active='1' ");

	}
  

 
	  
} // else end



 // set_flash('customer_info', "Customer imported successfully.", 1);
  
  //redirectToAdmin('customer');
	$response['status'] = 1;
	$response['message'] = 'Products imported successfully';
	  


}else if($type=='service'){

		

	/* if($_SERVER['HTTP_HOST']=='localhost'){
		$upload_path = 'D:/xampp/htdocs/salon/salon/assets/customer_data/';
	}else{
		$upload_path =  getcwd().'/assets/customer_data/';
		
	} */

	$upload_path =  getcwd().'/assets/service_data/';


//echo $upload_path;die;
// echo $upload_path.$excel;die;
move_uploaded_file($_FILES["excel"]["tmp_name"],"$upload_path".$excel);


$target_path = "$upload_path".$excel;
$data->read($target_path);
setlocale(LC_ALL, 'en_GB');
$new_data = $data->sheets[0]['cells'];
$i=1; //$x=1;   
$s=1;
//	echo sizeof($new_data);die;
for($x=2; $x <=sizeof($new_data); $x++) 
{ // loop start
 $i++; 
/***********************************************************Excel  ********************************/
 if(isset($data->sheets[0]["cells"][$x][1])){
  $service_name = $data->sheets[0]["cells"][$x][1];
 
 }

 
 
 if(isset($data->sheets[0]["cells"][$x][2])){
	$category = $data->sheets[0]["cells"][$x][2];
	$category_id = $this->getServiceCategoryId($category,$vendor_id);

 }

 if(isset($data->sheets[0]["cells"][$x][3])){
	  $price = $data->sheets[0]["cells"][$x][3];
	 
	 
 }
 if(isset($data->sheets[0]["cells"][$x][4])){
  $duration = $data->sheets[0]["cells"][$x][4];
  
 }
 if(isset($data->sheets[0]["cells"][$x][5])){
	$commission_type = $data->sheets[0]["cells"][$x][5];
	
}
if(isset($data->sheets[0]["cells"][$x][6])){
	$commission = $data->sheets[0]["cells"][$x][6];
	

}
 

  //mysql_set_charset('utf8');
	$file_name=$_FILES['excel']['name'];
  // echo $file_name;die;
	$crDate=date('Y-m-d H:i:s');


$check_name = $this->checkIfServiceNameAlreadyExist($service_name,$vendor_id);
if($check_name=='0'){
	$query2= $this->db->query("insert into service set service_name='".$service_name."', category_id='".$category_id."', price='".$price."', duration='".$duration."', commission_type='".$commission_type."', commission_amount='".$commission."', vendor_id='".$vendor_id."', is_active='1' ");

}



  
} // else end



// set_flash('customer_info', "Customer imported successfully.", 1);

//redirectToAdmin('customer');
$response['status'] = 1;
$response['message'] = 'Services imported successfully';
  


}

		echo json_encode($response);
		

}


	public function checkIfServiceNameAlreadyExist($service_name,$vendor_id){

		$query = $this->db->query("select count(service_id) as num from service where service_name='".$service_name."' and vendor_id='".$vendor_id."' ");
		$num = $query->row()->num;
		return $num;

	}


	public function getServiceCategoryId($category,$vendor_id){
		
		$query = $this->db->query("select category_id from service_category where category_name='".$category."' and vendor_id='".$vendor_id."' ");
		if($query->num_rows()>0){

			$category_id = $query->row()->category_id;
		}else{

			$q = $this->db->query("insert into service_category set category_name='".$category."', vendor_id='".$vendor_id."', is_active='1' ");
			$category_id = $this->db->insert_id();
		}
		return $category_id;
	}

	public function checkIfProductNameAlreadyExist($product_name,$vendor_id){

		$query = $this->db->query("select count(product_id) as num from product where product_name='".$product_name."' and vendor_id='".$vendor_id."' ");
		$num = $query->row()->num;
		return $num;
	}

	public function getBrandId($brand,$vendor_id){

		$query = $this->db->query("select brand_id from brand where brand_name='".$brand."'  and vendor_id='".$vendor_id."' ");

		if($query->num_rows()>0){
			$brand_id = $query->row()->brand_id;
			
		}else{
			$q = $this->db->query("insert into brand set brand_name='".$brand."', is_active='1', is_delete='0', vendor_id='".$vendor_id."' ");
			$brand_id = $this->db->insert_id();
		}
		
		
		return $brand_id;
	}

	public function getCategoryId($category,$vendor_id){

		$query = $this->db->query("select category_id from category where category_name='".$category."' and vendor_id='".$vendor_id."'");
		if($query->num_rows()>0){
			$category_id = $query->row()->category_id;
		}else{
			$q = $this->db->query("insert into category set category_name='".$category."', is_active='1', is_delete='0', vendor_id='".$vendor_id."' ");
			$category_id = $this->db->insert_id();
		}
		
		return $category_id;
	}

	public function checkIfVendorEmailAlreadyExist($email,$vendor_id){

		$query = $this->db->query("select count(s.supplier_id) as num from supplier s where s.email='".$email."' and s.vendor_id='".$vendor_id."'  ")->row();
		$num = $query->num;
		return $num;

	}

	public function checkIfVendorCodeAlreadyExist($vendor_code,$vendor_id){

		$query = $this->db->query("select count(s.supplier_id) as num from supplier s where s.supplier_code='".$vendor_code."' and s.vendor_id='".$vendor_id."'  ")->row();
		$num = $query->num;
		return $num;

	}

	public function checkEmailExists($email,$vendor_id,$role_id){

		$query = $this->db->query("select count(l.email) as num from login l where l.email='".$email."' and l.role_id='".$role_id."' and l.vendor_id='".$vendor_id."'  ")->row();
		$num = $query->num;
		return $num;

	}

	public function getPositionIdByName($name,$vendor_id){

		$query = $this->db->query("select role_id as position_id from role where vendor_id='".$vendor_id."' and role_name='".$name."'  ")->row();
		$position_id = $query->position_id;
		return $position_id;
	}
	

	public function geteEmployeeTypeIdByName($name,$vendor_id){

		$query = $this->db->query("select emp_type_id as employee_type_id from employee_type where vendor_id='".$vendor_id."' and type='".$name."'  ")->row();
		$employee_type_id = $query->employee_type_id;
		return $employee_type_id;
	}

	private function generateRandomNumber($numberRand)
    {
        //To Pull 7 Unique Random Values Out Of AlphaNumeric

        //removed number 0, capital o, number 1 and small L
        //Total: keys = 32, elements = 33
        $characters = array(
            "A", "B", "C", "D", "E", "F", "G", "H", "J", "K", "L", "M",
            "N", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z",
            "1", "2", "3", "4", "5", "6", "7", "8", "9");

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


	public function checkIFdeviceAlreadyExist($device_type,$vendor_id){

		$query = $this->db->query("select count(equipment_id) as num from equipment where vendor_id='".$vendor_id."' and device_type='".$device_type."' ")->row();

		$num = $query->num;
		return $num;
	}
	
	public function updateEquipment(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$type = $this->input->post('type'); // type 1= usb, 2= external terminal
		$ip_address = $this->input->post('ip_address');
		$port = $this->input->post('port');
		$device_type = $this->input->post('device_type'); // 1 for payment, 2 for printer 
		
		if(!empty($vendor_id)){
			
			if($type=='1' && $device_type==''){
				$ip_address = '';
				$port = '';
			}else{
				
				$ip_address = $ip_address;
				$port = $port;
			}
			if($device_type!=''){
				$device_type = $device_type;
			}else{
				$device_type='1';
			}
			
			$check_device = $this->checkIFdeviceAlreadyExist($device_type,$vendor_id);
			if($check_device>0){

				$query = $this->db->query("update equipment set type='".$type."', ip_address='".$ip_address."', port='".$port."' where vendor_id='".$vendor_id."' and device_type='".$device_type."' ");
			}else{
				$query = $this->db->query("insert into equipment set type='".$type."', ip_address='".$ip_address."', port='".$port."', device_type='".$device_type."', vendor_id='".$vendor_id."' ");
			}
			
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Equipment updated successfully';
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
	
	public function getEquipment(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$device_type = $this->input->post('device_type');
		
		if(!empty($vendor_id)){

			 if($device_type!=''){
				$device_type=$device_type;
			}else{
				$device_type = '1';
			} 


			$query = $this->db->query("select * from equipment where vendor_id='".$vendor_id."'  and device_type='".$device_type."' ");
			if($query->num_rows()>0){
			$result = $query->row();
				
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			
			}else{
				$response['status'] = 0;
				$response['result'] = [];
				$response['message'] = '';
			}
			
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
		
	}
	
	
	
	public function addHoliday(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$holiday_name = $this->input->post('holiday_name');
		$holiday_date = $this->input->post('holiday_date');
		$start_time = $this->input->post('start_time');
		$end_time = $this->input->post('end_time');
		$is_open = $this->input->post('is_open');
		
		if(!empty($vendor_id) && !empty($holiday_name) && !empty($holiday_date)){
			//$holiday_date  = date('Y-m-d',strtotime($holiday_date));
			//if($is_open=='1'){
				$start_time = date('H:i',strtotime($start_time));
				$end_time = date('H:i',strtotime($end_time));
				$timing = " start_time='".$start_time."', end_time='".$end_time."', ";
				
			/* }else{
				$timing = '';
			} */
			$query = $this->db->query("insert into holiday set holiday_name='".$holiday_name."', holiday_date='".$holiday_date."', $timing vendor_id='".$vendor_id."', is_open='".$is_open."' ");
			
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Holiday added succcessfully';
				
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
	
	
	public function getHoliday(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select h.holiday_id,h.holiday_name, h.holiday_date,CONCAT(TIME_FORMAT(h.start_time,'%l:%i %p'),' - ',TIME_FORMAT(h.end_time,'%l:%i %p')) as hour, h.is_open from holiday h where h.vendor_id='".$vendor_id."' order by h.holiday_id desc ");
			if($query->num_rows()>0){
			$result = $query->result();
				
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			
			}else{
				$response['status'] = 1;
				$response['result'] = [];
				$response['message'] = 'No holiday list available';
			}
			
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
		
	}
	
	
	public function getHolidayById(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$holiday_id = $this->input->post('holiday_id');
		
		if(!empty($vendor_id) && !empty($holiday_id)){
			
			$query = $this->db->query("select h.holiday_id, h.holiday_name,h.holiday_date, time_format(h.start_time,'%l:%i %p') as start_time, time_format(h.end_time,'%l:%i %p') as end_time, h.is_open from holiday h where h.vendor_id='".$vendor_id."' and h.holiday_id='".$holiday_id."' ");
			if($query->num_rows()>0){
			$result = $query->row();
				
				$response['status'] = 1;
				$response['result'] = $result;
				$response['message'] = '';
			
			}else{
				$response['status'] = 0;
				$response['result'] = [];
				$response['message'] = 'No holiday list available';
			}
			
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	
	public function editHoliday(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$holiday_id = $this->input->post('holiday_id');
		$holiday_name = $this->input->post('holiday_name');
		$holiday_date = $this->input->post('holiday_date');
		$start_time = $this->input->post('start_time');
		$end_time = $this->input->post('end_time');
		$is_open = $this->input->post('is_open');
		
		if(!empty($vendor_id)  && !empty($holiday_id) && !empty($holiday_name)  && !empty($holiday_date)){
			
			$start_time = date('H:i',strtotime($start_time));
				$end_time = date('H:i',strtotime($end_time));
			
			$query = $this->db->query("update holiday set holiday_name='".$holiday_name."', holiday_date='".$holiday_date."', start_time='".$start_time."', end_time='".$end_time."', is_open='".$is_open."' where holiday_id='".$holiday_id."' ");
			if($query){
				$response['status'] = 1;
				$response['message'] = 'Holiday updated successfully';
			
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
	
	
	public function deleteHoliday(){
		
		$holiday_id = $this->input->post('holiday_id');
		
		if(!empty($holiday_id)){
			
			$query = $this->db->query("delete from holiday where holiday_id='".$holiday_id."'");
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Holiday deleted successfully';
			}
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	
	public function serviceCharge(){
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select cash_discount_is_active, cash_discount_percentage, cash_discount_display_name from vendor where vendor_id='".$vendor_id."' ");
			if($query->num_rows()>0){
				$result = $query->row();
				
				$q = $this->db->query("select * from cash_discount_display_name where vendor_id='".$vendor_id."' ");
				$display_name = $q->result();
				$response['status'] = 1;
				$response['result'] = $result;
				$response['display_name'] = $display_name;
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
	
	
	public function updateServiceCharge(){
		
		$vendor_id = $this->input->post('vendor_id');
		$is_active = $this->input->post('is_active');
		$display_name = $this->input->post('display_name');
		
		if(!empty($vendor_id)){
			
			
			
			$query = $this->db->query("update vendor set cash_discount_is_active='".$is_active."', cash_discount_display_name='".$display_name."' where vendor_id='".$vendor_id."' ");
			if($query){
				$response['status'] = 1;
				$response['message'] = 'Cash discount updated successfully';
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
	
	
	
	public function updateCashTip(){
		
		$vendor_id = $this->input->post('vendor_id');
		$is_cash_tip = $this->input->post('is_cash_tip');
		
		if(!empty($vendor_id)){
			
			
			
			$query = $this->db->query("update vendor set is_cash_tip='".$is_cash_tip."' where vendor_id='".$vendor_id."' ");
			if($query){
				$response['status'] = 1;
				$response['message'] = 'Cash tip updated successfully';
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
	
	
	public function cashTip(){
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select is_cash_tip from vendor where vendor_id='".$vendor_id."' ");
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
	
	
	public function notificationMessages(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$customer = $this->db->query("select e_id as message_id, email_heading as title, email_subject as subject, slug, email_content, sms_content, is_auto from email_settings where email_type='1' and vendor_id='".$vendor_id."' and is_delete='0' and is_active='1' order by sort asc  ");
			if($customer->num_rows()>0){
				$customer_data = $customer->result();	
			}else{
				$customer_data = '[]';
			}
			
			$employee = $this->db->query("select e_id as message_id, email_heading as title, email_subject as subject, slug, email_content, sms_content, is_auto from email_settings where email_type='2' and vendor_id='".$vendor_id."' and is_delete='0' and is_active='1' order by sort asc  ");
			if($employee->num_rows()>0){
				$employee_data = $employee->result();	
			}else{
				$employee_data = '[]';
			}
			
			$appointment = $this->db->query("select e_id as message_id, email_heading as title, email_subject as subject, slug, email_content, sms_content, is_auto from email_settings where email_type='3' and vendor_id='".$vendor_id."' and is_delete='0' and is_active='1' order by sort asc  ");
			if($appointment->num_rows()>0){
				$appointment_data = $appointment->result();	
			}else{
				$appointment_data = '[]';
			}
			
			$supplier = $this->db->query("select e_id as message_id, email_heading as title, email_subject as subject, slug, email_content, sms_content, is_auto from email_settings where email_type='4' and vendor_id='".$vendor_id."' and is_delete='0' and is_active='1' order by sort asc  ");
			if($supplier->num_rows()>0){
				$supplier_data = $supplier->result();	
			}else{
				$supplier_data = '[]';
			}
			
			$admin = $this->db->query("select e_id as message_id, email_heading as title, email_subject as subject, slug, email_content, sms_content, is_auto from email_settings where email_type='5' and vendor_id='".$vendor_id."' and is_delete='0' and is_active='1' order by sort asc ");
			if($admin->num_rows()>0){
				$admin_data = $admin->result();	
			}else{
				$admin_data = '[]';
			}
			
			
			$response['status'] = 1;
			$response['customer'] = $customer_data;
			$response['employee'] = $employee_data;
			$response['appointment'] = $appointment_data;
			$response['supplier'] = $supplier_data;
			$response['admin'] = $admin_data; 
			$response['message'] = '';
			
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	
	public function updateMessages(){
		
		$vendor_id = $this->input->post('vendor_id');
		$message_id = $this->input->post('message_id');
		$customer_id = $this->input->post('customer_id');
		$title = $this->input->post('title');
		$subject = $this->input->post('subject');
		$email_content = $this->input->post('email_content');
		$sms_content = $this->input->post('sms_content');
		$is_email = $this->input->post('is_email');
		
		if(!empty($vendor_id) && !empty($message_id)){
			
			if($is_email=='1'){
				
			}
			 $query = $this->db->query("update email_settings set email_heading='".$title."', email_subject='".$subject."', email_content='".$email_content."', sms_content='".$sms_content."' where vendor_id='".$vendor_id."' and e_id='".$message_id."' ");
			 if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Content updated successfully';				
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

	
	public function autoUpdateMessage(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$is_auto = $this->input->post('is_auto');
		$message_id = $this->input->post('message_id');

	//	$is_auto = [{"message_id":"1","is_auto":"1"},{"message_id":"2","is_auto":"1"},{"message_id":"3","is_auto":"0"},{"message_id":"4","is_auto":"0"},]

	$is_auto_arr = json_decode($is_auto);
		
		if(!empty($vendor_id)){
			
		//	$query = $this->db->query("update email_settings set is_auto='".$is_auto."'  where e_id='".$message_id."' ");

		foreach($is_auto_arr as $auto){

			$this->db->query("update email_settings set is_auto='".$auto->is_auto."' where e_id='".$auto->message_id."' ");
		}
			
			$response['status'] = 1;
			$response['message'] = 'Message updated successfully';
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		echo json_encode($response);
	}
	
	
	
	public function addGiftcardImage(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$type = $this->input->post('type');
		$gc_image_id = $this->input->post('gc_image_id');
		
		$photo = $_FILES['image']['name'];
		$tmp_photo = $_FILES['image']['tmp_name'];
		
		$path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/giftcard/';
		
		
		
		//echo $path;die;
		if(!empty($vendor_id)){
			
			
		
			if($gc_image_id==''){
				
				if(!empty($photo)){
			
			$file = time().$photo;
			$image = move_uploaded_file($tmp_photo,$path.$file);
			$image = $file;
			
			
			}else{
				$image = 'avtar.png';
				
			}
			
		
			$query = $this->db->query("insert into  giftcard_images set image='".$image."', type='".$type."',vendor_id='".$vendor_id."' ");
			$msg = 'Gift Card added sucessfully';
			}else{
				
				if(!empty($photo)){
			
			$file = time().$photo;
			$image = move_uploaded_file($tmp_photo,$path.$file);
			$image = $file;
			
			
			}else{

				$old_image = $this->db->query("select image from giftcard_images where gc_image_id='".$gc_image_id."' ")->row()->image;
				$image = $old_image;
				
			}
			
				$query = $this->db->query("update  giftcard_images set image='".$image."',  type='".$type."' where gc_image_id='".$gc_image_id."' ");
				$msg = 'Gift card updated successfully';
			}
			
			if($query){
				
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
	
	
	public function editGiftcardImage(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$type = $this->input->post('type');
		$gc_image_id = $this->input->post('gc_image_id');
		
		$photo = $_FILES['image']['name'];
		$tmp_photo = $_FILES['image']['tmp_name'];
		
		$path = $_SERVER['DOCUMENT_ROOT'] . '/salon/assets/img/giftcard/';
		
		
		
		
		if(!empty($vendor_id)){
			
			if(!empty($photo)){
			
			$file = time().$photo;
			$image = move_uploaded_file($tmp_photo,$path.$file);
			$image = $file;
			
			
		}else{
			$image = 'avtar.png';
			
		}
		
			$query = $this->db->query("update giftcard_images set image='".$image."', type='".$type."' where gc_image_id='".$gc_image_id."' ");
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Gift card updated successfully';
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
	
	public function deleteGiftcardImage(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$gc_image_id = $this->input->post('gc_image_id');
		
		$photo = $_FILES['image']['name'];
		$tmp_photo = $_FILES['image']['tmp_name'];
		
		$path = $_SERVER['DOCUMENT_ROOT'] . '/salon/assets/img/giftcard/';
		$is_active=$this->input->post('is_active');
		
		
		
		if(!empty($vendor_id)){
			
			
		
			$query = $this->db->query("update  giftcard_images set is_active='".$is_active."' where gc_image_id='".$gc_image_id."'");
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Gift card updated successfully';
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
	
	
	public function addCertificateImage(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$name = $this->input->post('name');
		$image_id = $this->input->post('image_id');
		
		$photo = $_FILES['image']['name'];
		$tmp_photo = $_FILES['image']['tmp_name'];
		
		$path = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/certificate/';
		
		
		
		if(!empty($vendor_id)){
			
		if($image_id==''){
			if(!empty($photo)){
			
			$file = time().$photo;
			$image = move_uploaded_file($tmp_photo,$path.$file);
			$image = $file;
			
			
		}else{
			$image = 'avtar.png';
			
		}
		
			$query = $this->db->query("insert into  gift_certificate_images set image='".$image."', name='".$name."',vendor_id='".$vendor_id."' ");
			$msg = 'Certificate added successfully';
		}else{
			
			if(!empty($photo)){
			
			$file = time().$photo;
			$image = move_uploaded_file($tmp_photo,$path.$file);
			$image = $file;
			
			
		}else{

			$old_img = $this->db->query("select image from gift_certificate_images where image_id='".$image_id."'")->row()->image;
			$image = $old_img;
			
		}
		
		
			$query = $this->db->query("update gift_certificate_images set image='".$image."', name='".$name."' where image_id='".$image_id."' ");
			$msg = 'Certificate updated successfully';
		}
			if($query){
				
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
	
	
	public function editCertificateImage(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$name = $this->input->post('name');
		$image = $this->input->post('image_id');
		
		$photo = $_FILES['image']['name'];
		$tmp_photo = $_FILES['image']['tmp_name'];
		
		$path = $_SERVER['DOCUMENT_ROOT'] . '/salon/assets/img/certificate/';
		
		if(!empty($vendor_id)){
			
			if(!empty($photo)){
			
			$file = time().$photo;
			$image = move_uploaded_file($tmp_photo,$path.$file);
			$image = $file;
			
			
		}else{
			$image = 'avtar.png';
			
		}
		
			$query = $this->db->query("update gift_certificate_images set image='".$image."', name='".$name."' where image_id='".$image_id."' ");
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'Certificate updated successfully';
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
	
	
	public function deleteCertificateImage(){
		
		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$image_id = $this->input->post('image_id');
		
		$photo = $_FILES['image']['name'];
		$tmp_photo = $_FILES['image']['tmp_name'];
		
		$path = $_SERVER['DOCUMENT_ROOT'] . '/salon/assets/img/certificate/';
		$is_active=$this->input->post('is_active');
		
		
		
		if(!empty($vendor_id)){
			
			if($is_active=='1'){
				$msg = "Certifictate image activated successfully!";
			}else{
				$msg = "Certificate image deactivated successfully!";
			}
		
			$query = $this->db->query("update gift_certificate_images set is_active='".$is_active."' where image_id='".$image_id."'");
			if($query){
				
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
	
	public function getVendorId(){
		
		$vendor_id = $this->input->post('vendor_id');
		$id = $this->input->post('id');
		return $vendor_id;
		$data = array('vendor_id'=>$vendor_id,'id'=>$id);
		return $data;
		
	}
	
	public function getEmailSettingsApp($vendor_id){
	
		$sqlQuery=$this->db->query("select * from email_settings where  is_active=1 and email_type=3 and vendor_id='".$vendor_id."' order by sort asc");
		$result=$sqlQuery->result();
		return $result;
		
	}
	public function getEmailSettingsCust(){
	
		$sqlQuery=$this->db->query("select * from email_settings where  is_active=1 and email_type=1 and vendor_id='".$vendor_id."' order by sort asc");
		$result=$sqlQuery->result();
		return $result;
		
	}
	public function getEmailSettingsSty(){
	
		$sqlQuery=$this->db->query("select * from email_settings where  is_active=1 and email_type=2 and vendor_id='".$vendor_id."' order by e_id asc");
		$result=$sqlQuery->result();
		return $result;
		
	}
	
	public function getEmailContentById($id){
		
		$query = $this->db->query("select * from email_settings where e_id='".$id."' ");
		$result = $query->row();
		return $result;
		
	}
	
	public function getAllCustomer(){
		
		$query = $this->db->query("select c.customer_id, concat(c.firstname,' ',c.lastname) as customer_name from customer c inner join login l on l.login_id=c.login_id where l.vendor_id='166'  order by c.customer_id desc");
		$result = $query->result();
		return $result;
	}
	
	public function notificationMessageWebView(){
		
		$vendor_id = $_GET['vendor_id'];
	
		$id = $_GET['id'];
		
		$data['id'] = $id;
		$data['content'] = $this->getEmailContentById($id);
		$data['customer_list'] = $this->getAllCustomer(); 
		$this->load->view('notification_message/edit',$data);
	}
	
	public function editMessageWebView($id){
		
		$email_subject = addslashes($this->input->post("email_subject"));
        $description = addslashes($this->input->post("description"));
        $sms_description = addslashes($this->input->post("sms_description"));
	//	echo "update email_settings set email_subject='".$email_subject."',  email_content='".$description."',sms_content='".$sms_description."'  where e_id='".$id."'  ";die;
		$query = $this->db->query("update email_settings set email_subject='".$email_subject."',  email_content='".$description."',sms_content='".$sms_description."'  where e_id='".$id."'  ");
		
		$data['id'] = $id;
		$data['content'] = $this->getEmailContentById($id);
		$data['customer_list'] = $this->getAllCustomer(); 
		$this->load->view('notification_message/edit',$data);
		
	}
	
	
	public function updateCustomerPayment(){
		
		$vendor_id = $this->input->post('vendor_id');
		$is_payment = $this->input->post('is_payment');
		
		if(!empty($vendor_id)){
			
			
			
			$query = $this->db->query("update vendor set is_customer_payment='".$is_payment."' where vendor_id='".$vendor_id."' ");
			if($query){
				$response['status'] = 1;
				$response['message'] = 'Payment info updated successfully';
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
	
	public function customerPaymentOption(){
		
		$vendor_id = $this->input->post('vendor_id');
		
		if(!empty($vendor_id)){
			
			$query = $this->db->query("select is_customer_payment from vendor where vendor_id='".$vendor_id."' ");
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
	public function getDevice(){
		$vendor_id=$this->input->post('vendor_id');
		if(!empty($vendor_id)){
			$getData=$this->db->query('select licence_id,model,manufacturing,serial_no,station,licence_key from licence where vendor_id="'.$vendor_id.'" and status=1 and is_delete=0')->result();	
			if(!empty($getData)){
				$response['status']=1;
				$response['getData']=$getData;
				$response['message']='Data Found';
			}else{
				$response['status']=1;
				$response['getData']=array();
				$response['message']='Data Found';
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';	
		}
		echo json_encode($response);	

	}
	public function getDeviceById(){
		$licence_id=$this->input->post('licence_id');
		if(!empty($licence_id)){
			$getData=$this->db->query('select licence_id,model,manufacturing,serial_no,station,licence_key from licence where licence_id="'.$licence_id.'"')->row();	
			if(!empty($getData)){
				$response['status']=1;
				$response['getData']=$getData;
				$response['message']='Data Found';
			}else{
				$response['status']=1;
				$response['getData']=(object)[];
				$response['message']='Data Found';
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';	
		}
		echo json_encode($response);	

	}
	public function updateDeviceData(){
		$licence_id=$this->input->post('licence_id');
		$model=$this->input->post('model');
		$manufacturing=$this->input->post('manufacturing');
		$station=$this->input->post('station');
		$licence_key=$this->input->post('licence_key');
		if(!empty($licence_id)){
			$query=$this->db->query('update licence set model="'.$model.'",manufacturing="'.$manufacturing.'",station="'.$station.'",licence_key="'.$licence_key.'" where licence_id="'.$licence_id.'"');
			if(!empty($query)){
				$response['status']=1;
				//$response['getData']=$getData;
				$response['message']='Device data updated successfully';
			}else{
				$response['status']=0;
				//$response['getData']=(object)[];
				$response['message']='Something went wrong';
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';	
		}
		echo json_encode($response);	

	}

	public function deactivateDevice(){
		$licence_id=$this->input->post('licence_id');
		
		if(!empty($licence_id)){
			$query=$this->db->query('update licence set status=0,is_delete=0 where licence_id="'.$licence_id.'"');
			if(!empty($query)){
				$response['status']=1;
				//$response['getData']=$getData;
				$response['message']='Device deactivated successfully';
			}else{
				$response['status']=0;
				//$response['getData']=(object)[];
				$response['message']='Something went wrong';
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';	
		}
		echo json_encode($response);	

	}


	public function getEquipmentType(){

		$vendor_id = $this->input->post('vendor_id');	
		
		if(!empty($vendor_id)){

			$query = $this->db->query("select * from equipment_type");
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


	// get devices for auto batch close
	public function getDevices(){

		$query = $this->db->query("select l.serial_no as device_id, l.vendor_id, s.first_time as batch_close_time, s.value as is_auto_close, IFNULL(e.ip_address,'') AS ip_address, IFNULL(e.port,'') as port from licence l INNER JOIN settings s ON s.vendor_id=l.vendor_id INNER JOIN equipment e ON e.vendor_id=l.vendor_id where s.field='batch_close_time' and e.device_type='1' group by l.serial_no");

		if($query->num_rows()>0){
			$result = $query->result();
			$response['status'] = 1;
			$response['result'] = $result;
			$response['message'] = '';	
		}else{
			$response['status'] = 0;
			$response['result'] = [];
			$response['message'] = 'No data found';	
		}
		echo json_encode($response);
	}

	public function updateMailchimpKey(){

		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		$api_key = $this->input->post('api_key');
		
		
		if(!empty($vendor_id)){
			
			
			$query = $this->db->query("update settings set value='".$api_key."' where vendor_id='".$vendor_id."' and field='mailchimp'  ");
			//echo $this->db->last_query();die;
			if($query){
				
				$response['status'] = 1;
				$response['message'] = 'API Key updated successfully';
				
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

	public function getMailchimpKey(){

		$response['status'] = 0;
		$response['message'] = '';
		
		$vendor_id = $this->input->post('vendor_id');
		
		
		
		if(!empty($vendor_id)){
			
			
			$query = $this->db->query("select value as api_key from  settings where vendor_id='".$vendor_id."' and field='mailchimp'  ");
			//echo $this->db->last_query();die;
			if($query->num_rows()>0){
				$api_key = $query->row()->api_key;
				$response['status'] = 1;
				$response['api_key'] = $api_key;
				$response['message'] = '';
				
			}else{
				
				$response['status'] = 0;
				$response['message'] = 'No data found';
			}
			
		}else{
			
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing';
		}
		
		echo json_encode($response);
	}
	public function device_deactivate(){
		$device_id=$this->input->post('device_id');
		if(!empty($device_id)){
			$query=$this->db->query('update licence set is_delete=1,status=0 where serial_no="'.$device_id.'"');
			if($query){
				$response['status'] = 1;
				$response['message'] = 'Device deactivated successfully';
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
	public function updateScreenView(){
		$vendor_id=$this->input->post('vendor_id');
		$view_type=$this->input->post('view_type');
		if(!empty($vendor_id)){
			$query=$this->db->query('update settings set value="'.$view_type.'" where field="screen_view" and vendor_id="'.$vendor_id.'"');
			if($query){
				$response['status'] = 1;
				$response['message'] = 'Calender view updated successfully';
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