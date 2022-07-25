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


class Attendence extends CI_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
		
		//error_reporting(0);
		
    }
    public function checkStylistExistence($stylist_pin,$vendor_id){
	
		$q2 = $this->db->query("select s.stylist_id from stylist s inner join login l on l.login_id=s.login_id where l.pin='".$stylist_pin."' and vendor_id='".$vendor_id."' ");
		$r2 = $q2->row();
		$stylist_id = $r2->stylist_id;
		return $stylist_id;
	}
	
	public function checkPinExistence($stylist_pin,$vendor_id){
	//and vendor_id='".$vendor_id."'
		$qq = $this->db->query("select count(login_id) as pin from login where pin='".$stylist_pin."' and vendor_id='".$vendor_id."' and is_delete='0' ");
		$pin = $qq->row()->pin;
		return $pin;		
	}
	
	public function checkAttendance($current_date,$stylist_id,$type){
	
		$query1 = $this->db->query("select count(attendance_id) as clockin_num from attendance where attendance_date='".$current_date."' and type='".$type."' and stylist_id='".$stylist_id."' and attendance_out_date IS NULL and attendance_out_time IS NULL  ");
		$clockin_num = $query1->row()->clockin_num;
		return $clockin_num;
	}
	public function checkAttendance1($current_date,$stylist_id,$type){
	
		$query1 = $this->db->query("select count(attendance_id) as clockin_num from attendance where attendance_date='".$current_date."' and type='".$type."' and stylist_id='".$stylist_id."'");
		$clockin_num = $query1->row()->clockin_num;
		return $clockin_num;
	}
	public function checkStlistOutTime_clock($current_date,$stylist_id,$type){
		
		$query = $this->db->query("select attendance_id  from  attendance where stylist_id='".$stylist_id."' and   type='".$type."' and attendance_date='".$current_date."' and attendance_out_date IS NULL and attendance_out_time IS NULL");
		$result = $query->result();
		return $result;
	}
	public function checkStlistOutBreak($current_date,$stylist_id){
		
		$query = $this->db->query("select attendance_id  from  attendance where stylist_id='".$stylist_id."' and  attendance_date='".$current_date."' and type!=0 and attendance_out_date IS NULL and attendance_out_time IS NULL");
		$result = $query->result();
		return $result;
	}
    
	   public function markAttendance(){
		   
		   $country = $this->ip_info("Visitor", "Country");
		if($country=='India'){
			date_default_timezone_set("Asia/Kolkata");
		}else{
			
			date_default_timezone_set("America/Los_Angeles");
		}
		
		$response['status']= 0;
        $response['message']= '';
		
       $clock_as = $_POST['clock_as'];
        $role_id=$this->input->post('role_id');
       $current_date=date('Y-m-d');
       $current_time = date('H:i');
       $stylist_pin = $_POST['stylist_pin'];
       $vendor_id = $_POST['vendor_id'];
    
	   if(!empty($stylist_pin)){
            if($vendor_id==''){
                //echo 'select vendor_id from login where pin="'.$pin.'"';
               $getVendor=$this->db->query('select vendor_id from login where pin="'.$stylist_pin.'"')->row();
            $vendor_id=$getVendor->vendor_id;
        }else{
            $vendor_id=$vendor_id;
        }
		
       $pin = $this->checkPinExistence($stylist_pin,$vendor_id);
      // echo $pin;die;
	  
	   if($pin>0){
       
        $stylist_id = $this->checkStylistExistence($stylist_pin,$vendor_id);
        $clockin_num = $this->checkAttendance($current_date,$stylist_id,0);
         $clockin_num1 = $this->checkAttendance1($current_date,$stylist_id,0);
        $stylist_check_out=$this->checkStlistOutTime_clock($current_date,$stylist_id,0);
        $getStylistName=$this->db->query('select l.role_id,l.pin,concat(s.firstname," ",s.lastname) as stylist_name  from stylist as s inner join login as l on s.login_id=l.login_id where stylist_id="'.$stylist_id.'"')->row();
        $totalOut=count($stylist_check_out);
        $current_dateNew=date('m/d/Y');
        $getAllowAttenence=$this->db->query('select value from settings where vendor_id="'.$vendor_id.'" and field="allow_attendance_outside_workhour"')->row();
       $dayName = date('l', strtotime($current_date));
       $getBuisnessHour=$this->db->query('select start_time,end_time from business_hour where vendor_id="'.$vendor_id.'" and days="'.$dayName.'"')->row();
       $time=date('H:i');
       if($getAllowAttenence->value!=1 && $getBuisnessHour->start_time < $time && $getBuisnessHour->end_time > $time ){
              $response['status']= 0;
              $response['message']= "You can't mark attendance outside business hour";
       }
       else if($role_id==3 && $getStylistName->pin!=$pin){
                  $response['status']= 0;
                  $response['message']= "You can't use other pin for mark attendance";
        }
        else{
        if($clock_as=='clockin'){
          if($clockin_num<=0){
              if($totalOut==0){
              $q3 = $this->db->query("insert into attendance set stylist_id='".$stylist_id."', attendance_date='".$current_date."', attendance_time='".$current_time."', type='0', is_emergency_clockout='0',color='#9ACD32',vendor_id='".$vendor_id."',button_type='1' ");
              if($q3){
               $response['status']= 1;
               $response['message']= $getStylistName->stylist_name.' shift started';
              }
            }else{
				$response['status']= 1;
              $response['message']=     $getStylistName->stylist_name.' has not ended '.$current_dateNew;
            }
          }else{
			  $response['status']= 1;
            $response['message']= $getStylistName->stylist_name.' shift already started';
          }

        }
        if($clock_as=='clockout'){
          if($clockin_num >0){
             
              $q3 = $this->db->query("update attendance set   attendance_out_date='".$current_date."', attendance_out_time='".$current_time."',button_type='0' where stylist_id='".$stylist_id."'  and attendance_out_date IS NULL and attendance_out_time IS NULL ");
              if($q3){
				  $response['status']= 1;
               $response['message']= $getStylistName->stylist_name.' shift ended';
              }
            
          }else{
			  $response['status']= 1;
            $response['message']= $getStylistName->stylist_name.' has not started shift';
          }

        }
        if($clock_as=='short_leave_for_break'){

          if($clockin_num>0){
               $stylist_check_out=$this->checkStlistOutBreak($current_date,$stylist_id);
               $totalOut1=count($stylist_check_out);
              // $response['message']= $totalOut1;die;
               if($totalOut1<=0){
              $q3 = $this->db->query("insert into attendance set stylist_id='".$stylist_id."', attendance_date='".$current_date."', attendance_time='".$current_time."', type='1', is_emergency_clockout='0',color='#FF7F50',vendor_id='".$this->session->userdata['vendor_id']."',button_type='1' ");
             // $q3 = $this->db->query("update attendance set   attendance_out_date='".$current_date."', attendance_out_time='".$current_time."' where stylist_id='".$stylist_id."' and type=0  and attendance_out_date IS NULL and attendance_out_time IS NULL ");
              if($q3){
				  $response['status']= 1;
               $response['message']= $getStylistName->stylist_name.' is on short break';
              }
            }else{
				$response['status']= 1;
               $response['message']= $getStylistName->stylist_name.' is still on short break';
            }
          }else{
			  $response['status']= 1;
            $response['message']= $getStylistName->stylist_name.' has not started shift';
          }

        
        }
        if($clock_as=='short_back_from_break'){
          if($clockin_num1 >0){
               $stylist_check_out=$this->checkStlistOutBreak($current_date,$stylist_id);
               $totalOut2=count($stylist_check_out);
                $stylist_check_out3=$this->checkStlistOutTime_clock($current_date,$stylist_id,1);
           $totalOut3=count($stylist_check_out3);
               if($totalOut2 > 0 && $totalOut3 > 0){
              $q3 = $this->db->query("update attendance set   attendance_out_date='".$current_date."', attendance_out_time='".$current_time."',button_type='0' where stylist_id='".$stylist_id."' and type=1  and attendance_out_date IS NULL and attendance_out_time IS NULL ");
             
			// $this->db->query("insert into attendance set stylist_id='".$stylist_id."', attendance_date='".$current_date."', attendance_time='".$current_time."', type='0', is_emergency_clockout='0',color='#556B2F',vendor_id='".$this->session->userdata['vendor_id']."' ");
              if($q3){
				  $response['status']= 1;
               $response['message']=$getStylistName->stylist_name.' ended short break';
              }
            }else{
				$response['status']= 1;
               $response['message']= $getStylistName->stylist_name.' break has not started';
            }
          }else{
			  $response['status']= 1;
            $response['message']= $getStylistName->stylist_name.' has not started shift';
          }

        }
        if($clock_as=='long_leave_for_break'){

          if($clockin_num1 >0){
               $stylist_check_out=$this->checkStlistOutBreak($current_date,$stylist_id);
               $totalOut=count($stylist_check_out);
               if($totalOut<=0){
              $q3 = $this->db->query("insert into attendance set stylist_id='".$stylist_id."', attendance_date='".$current_date."', attendance_time='".$current_time."', type='2', is_emergency_clockout='0',color='#FF0000', vendor_id='".$this->session->userdata['vendor_id']."',button_type='1' ");
              //$q3 = $this->db->query("update attendance set   attendance_out_date='".$current_date."', attendance_out_time='".$current_time."' where stylist_id='".$stylist_id."' and type=0 and attendance_out_date IS NULL and attendance_out_time IS NULL ");
              if($q3){
				  $response['status']= 1;
               $response['message']= $getStylistName->stylist_name.' is on long break';
              }
            }else{
				$response['status']= 1;
               $response['message']= $getStylistName->stylist_name.' is still on long break';
            }
          }else{
			  $response['status']= 1;
            $response['message']= $getStylistName->stylist_name.' has not started shift';
          }

        
        }
		
        if($clock_as=='long_back_from_break'){
          if($clockin_num1 >0){
               $stylist_check_out=$this->checkStlistOutBreak($current_date,$stylist_id);
               $totalOut=count($stylist_check_out);
               $stylist_check_out3=$this->checkStlistOutTime_clock($current_date,$stylist_id,2);
           $totalOut3=count($stylist_check_out3);
               if($totalOut > 0 && $totalOut3 > 0){
              $q3 = $this->db->query("update attendance set   attendance_out_date='".$current_date."', attendance_out_time='".$current_time."',button_type='0' where stylist_id='".$stylist_id."' and type=2  and attendance_out_date IS NULL and attendance_out_time IS NULL ");
             // $this->db->query("insert into attendance set stylist_id='".$stylist_id."', attendance_date='".$current_date."', attendance_time='".$current_time."', type='0', is_emergency_clockout='0',color='#556B2F',vendor_id='".$this->session->userdata['vendor_id']."' ");
              if($q3){
				  $response['status']= 1;
               $response['message']= $getStylistName->stylist_name.' ended long break';
              }
            }else{
				$response['status']= 1;
               $response['message']= $getStylistName->stylist_name.' is still on a brea';
            }
          }else{
			  $response['status']= 1;
            $response['message']= $getStylistName->stylist_name.' has not started shift';
          }

        }
    }
      }else{
		  $response['status']= 0;
        $response['message']= "Wrong Employee Pin!";
      }
	   }else{
		   $response['status']= 0;
		   $response['message']= 'Pin number is require';
		   
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
	