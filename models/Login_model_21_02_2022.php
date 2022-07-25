<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
class Login_Model extends CI_Model
{
	
	public function updateSettingRecords($field,$value,$first_time,$second_time,$vendor_id,$is_active){
		
		$query = $this->db->query("select count(settings_id) as num from settings where field='".$field."' and vendor_id='".$vendor_id."' ");
		
		$num = $query->row()->num;
		if($num==0){
			$q1 = $this->db->query("insert into settings set field='".$field."', value='".$value."', first_time='".$first_time."', second_time='".$second_time."', vendor_id='".$vendor_id."', is_active='".$is_active."'  ");
		}
	}	
	
	public function updateAppointmentRules($vendor_id){
		
		$query = $this->db->query("select count(id) as num from appointment_rules where vendor_id='".$vendor_id."' ");
		$num = $query->row()->num;
		if($num==0){
			$q = $this->db->query("insert into appointment_rules set field1='80', field2='100', vendor_id='".$vendor_id."', is_active='1' ");
		}
	}
	
	public function updateBillingInfo($vendor_id){
		
		$query = $this->db->query("select count(billing_info_id) as num from billing_info where vendor_id='".$vendor_id."' ");
		$num = $query->row()->num;
		if($num==0){
			$q = $this->db->query("insert into billing_info set vendor_id='".$vendor_id."' ");
		}
	}
	
	public function updateBusinessHour($vendor_id){
		
		$query = $this->db->query("select count(business_hour_id) as num from business_hour where vendor_id='".$vendor_id."' ");
		$num = $query->row()->num;
		if($num==0){
			for($i=1;$i<=7;$i++){
				if($i==1){$day='Monday';}
				if($i==2){$day='Tuesday';}
				if($i==3){$day='Wednesday';}
				if($i==4){$day='Thursday';}
				if($i==5){$day='Friday';}
				if($i==6){$day='Saturday';}
				if($i==7){$day='Sunday';}
				
				$q = $this->db->query("insert into business_hour set days='".$day."', switch='1', start_time='09:00', end_time='22:00', vendor_id='".$vendor_id."', sort='".$i."' ");
			}
		}
	}
	
	public function updateCancellationPolicy($vendor_id){
		
		$query = $this->db->query("select count(id) as num from cancellation_policy where vendor_id='".$vendor_id."' ");
		$num = $query->row()->num;
		if($num==0){
			$q = $this->db->query("insert into cancellation_policy set vendor_id='".$vendor_id."', field1='0', field2='0', field2_type='percent', field3='0', field3_type='percent', is_active='1' ");
		}
	}
	
	
	public function updateCalendarColor($vendor_id){
		
		$query = $this->db->query("select count(color_id) as num from color_settings where vendor_id='".$vendor_id."' ");
		$num = $query->row()->num;
		if($num==0){
			 $this->db->query("insert into color_settings set vendor_id='".$vendor_id."', name='Unconfirm', color_type='unconfirm', color_code='#ec13c8', text_color='#556161', sort='1', is_active='1' ");
			 
			 $this->db->query("insert into color_settings set vendor_id='".$vendor_id."', name='Confirm', color_type='confirm', color_code='#936969', text_color='#556161', sort='2', is_active='1' ");
			 
			 $this->db->query("insert into color_settings set vendor_id='".$vendor_id."', name='No show', color_type='no_show', color_code='#E81818', text_color='#556161', sort='3', is_active='1' ");
			  
			 $this->db->query("insert into color_settings set vendor_id='".$vendor_id."', name='Checked In', color_type='checked_in', color_code='#dea21b', text_color='#ffffff', sort='4', is_active='1' ");
			 
			 $this->db->query("insert into color_settings set vendor_id='".$vendor_id."', name='Cancel', color_type='cancel', color_code='#556161', text_color='#556161', sort='5', is_active='1' ");
			 
			 $this->db->query("insert into color_settings set vendor_id='".$vendor_id."', name='Checkout', color_type='checkout', color_code='#0115cb', text_color='#556161', sort='6', is_active='1' ");
			 
			 $this->db->query("insert into color_settings set vendor_id='".$vendor_id."', name='Delete', color_type='delete', color_code='#F30E0E', text_color='#970bf4', sort='7', is_active='1' ");
			 
			 $this->db->query("insert into color_settings set vendor_id='".$vendor_id."', name='Service In Process', color_type='service_in_process', color_code='#22aacc', text_color='#ffffff', sort='8', is_active='1' ");
			 
			 $this->db->query("insert into color_settings set vendor_id='".$vendor_id."', name='Prepaid', color_type='prepaid', color_code='#008000', text_color='#ffffff', sort='8', is_active='1' ");
			 
			 
		}
		
		$query2 = $this->db->query("select count(color_id) as num from color_settings where vendor_id='".$vendor_id."' and color_type='hold' ");
		$num2 = $query2->row()->num;
		if($num2==0){
			$this->db->query("insert into color_settings set vendor_id='".$vendor_id."', name='Hold', color_type='hold', color_code='#F4F119', text_color='#ffffff', sort='8', is_active='1' ");
		}
	}
	
	public function updateNotificationCriteria($vendor_id){
		
		$query = $this->db->query("select count(nc_id) as num from notification_criteria where vendor_id='".$vendor_id."' ");
		$num = $query->row()->num;
		if($num==0){
			
			for($i=1;$i<=5;$i++){
			
			if($i==1){
				$detail = "Appointment notification";
				$duration = "0";
				$text_notify = "0";
				$email_notify = "1";
				$push_notify = "0";
			}
			if($i==2){
				$detail = "Appointment modification";
				$duration = "0";
				$text_notify = "0";
				$email_notify = "1";
				$push_notify = "0";
			}
			if($i==3){
				$detail = "Appointment cancellation";
				$duration = "0";
				$text_notify = "0";
				$email_notify = "1";
				$push_notify = "0";
			}
			
			if($i==4){
				$detail = "Confirmation request to the customer before";
				$duration = "72";
				$text_notify = "0";
				$email_notify = "1";
				$push_notify = "0";
			}
			if($i==5){
				$detail = "Appointment reminder to the customer before";
				$duration = "24";
				$text_notify = "0";
				$email_notify = "1";
				$push_notify = "0";
			}
			
			
			
			$query = $this->db->query("insert into notification_criteria set detail='".$detail."', duration='".$duration."', text_notify='".$text_notify."', email_notify='".$email_notify."', push_notify='".$push_notify."', vendor_id='".$vendor_id."' ");
		}
		}
		
		
	}
	
	
	public function updateEmailSettings($vendor_id,$slug,$email_heading,$email_subject,$email_content,$email_type,$is_active){
		
		$query = $this->db->query("select count(e_id) as num from email_settings where slug='".$slug."' and vendor_id='".$vendor_id."' ");
		$num = $query->row()->num;
		if($num==0){
			
			
			$this->db->query("insert into email_settings set vendor_id='".$vendor_id."', slug='".$slug."', email_heading='".$email_heading."', email_subject='".$email_subject."', email_content='".$email_content."', email_type='".$email_type."', is_active='".$is_active."' ");
		}
		
	}
	
	
	public function allowAptOutsideBusinessHour($vendor_id){
		
		$query = $this->db->query("select count(settings_id) as num from settings where field='allow_apt_outside_business_hour' and vendor_id='".$vendor_id."' ");
		$num = $query->row()->num;
		
		if($num==0){
			
			
			$this->db->query("insert into settings set value='0', field='allow_apt_outside_business_hour', vendor_id='".$vendor_id."' ");
		}
		
	}
	
	
	public function notificationCriteriaType($vendor_id){
		
			$query = $this->db->query("select count(settings_id) as num from settings where field='notification_criteria_type' and vendor_id='".$vendor_id."' ");
		$num = $query->row()->num;
		
		if($num==0){
			
			
			$this->db->query("insert into settings set value='1', field='notification_criteria_type', vendor_id='".$vendor_id."' ");
		}
	
		
	}
	
	
	public function updateGiftCardPresetAmount($vendor_id){
		
			$query = $this->db->query("select count(gs_id) as num from giftcard_settings where  vendor_id='".$vendor_id."' ");
			$num = $query->row()->num;
		
		if($num==0){
			
			
			$this->db->query("insert into giftcard_settings set preset_amount_1='10.00', preset_amount_2='20.00', preset_amount_3='25.00', preset_amount_4='50.00', vendor_id='".$vendor_id."', modified_date='".date('Y-m-d H:i:s')."' ");
		}
	
		
	}
	
	public function updateBatchCloseTime($vendor_id){
		
			$query = $this->db->query("select count(settings_id) as num from settings where field='batch_close_time' and vendor_id='".$vendor_id."' ");
			$num = $query->row()->num;
		
		if($num==0){
			
			$this->db->query("insert into settings set field='batch_close_time', value='0', first_time='00:00', vendor_id='".$vendor_id."' ");
		}
		
		
		
	}
	
	
	
	public function updateGiftCertificateSettings($vendor_id){
		
			$query = $this->db->query("select count(id) as num from gift_settings where  vendor_id='".$vendor_id."' ");
			$num = $query->row()->num;
		
		if($num==0){
			
			$this->db->query("insert into gift_settings set max_amount='100.00',  vendor_id='".$vendor_id."' ");
		}
		
	}

	
	
	public function updateTvScreenSettings($vendor_id){
		
			$query = $this->db->query("select count(screen_id) as num from tv_screen where  vendor_id='".$vendor_id."' ");
			$num = $query->row()->num;
		
		if($num==0){
			
			$this->db->query("insert into tv_screen set wallpaper='1602766529IMG_20191210_043111.jpg', video_url='https://www.youtube.com/watch?v=_ss2EdOhwig', video_time_interval='10', is_active='1', created_date='".date('Y-m-d H:i:s')."',  vendor_id='".$vendor_id."' ");
		}
		
	}
	
	
	public function updateEquipment($vendor_id){
		
		$query = $this->db->query("select count(equipment_id) as num from equipment where vendor_id='".$vendor_id."' ");
		$num = $query->row()->num;
		
		if($num==0){
			
			$this->db->query("insert into equipment set type='1', vendor_id='".$vendor_id."' ");
		}
	}
	
	
	public function updateIsScreenLock($vendor_id){
		
			$query = $this->db->query("select count(settings_id) as num from settings where field='is_screen_lock' and vendor_id='".$vendor_id."' ");
			$num = $query->row()->num;
		
		if($num==0){
			
			$this->db->query("insert into settings set field='is_screen_lock', value='0', vendor_id='".$vendor_id."' ");
		}
		
		
		
	}
	
	public function updateServiceCharge($vendor_id){
		
		$query = $this->db->query("select count(settings_id) as num from settings where field='service_charge' and vendor_id='".$vendor_id."' ");
			$num = $query->row()->num;
		
		if($num==0){
			
			$this->db->query("insert into settings set field='service_charge', value='0.00', first_time='Amount', vendor_id='".$vendor_id."' ");
		}
	}
	
	public function updateWaitingStatus($vendor_id){
		
		$query = $this->db->query("select count(color_id) as num from color_settings where vendor_id='".$vendor_id."' AND color_type='payment_pending' ");
		$num = $query->row()->num;
		if($num==0){
			 $this->db->query("insert into color_settings set vendor_id='".$vendor_id."', name='Payment Pending', color_type='payment_pending', color_code='#D44958', text_color='#ffffff', sort='9', is_active='1' ");
			 
		}
	}
	
}

?>