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

class Tv extends CI_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
		
		//error_reporting(0);
    }
	
	
	
	public function waitingList(){
		
		$vendor_id = $this->input->post('vendor_id');
		
		$response['status'] = 0;
		$response['message'] = '';
			
		if(!empty($vendor_id)){
			$sql = "SELECT CONCAT (ap.date,' ',ap_s.appointment_time) as DateTimes , CONCAT (sty.firstname,' ',sty.lastname) as StylistName , CONCAT (cus.firstname,' ',cus.lastname) as CustomerName FROM appointment ap INNER JOIN appointment_service ap_s ON ap.appointment_id = ap_s.appointment_id INNER JOIN stylist sty ON sty.stylist_id = ap_s.stylist_id INNER JOIN customer cus ON cus.customer_id = ap_s.customer_id WHERE ap.vendor_id='".$vendor_id."' ";

		$results = $this->db->query($sql)->result();
		
		foreach ($results as $date_times) {
			
			$date1 = strtotime($date_times->DateTimes);
			$date2 = strtotime(date('Y-m-d H:i:s'));

			// Formulate the Difference between two dates 
			$diff = abs($date2 - $date1); 


			// To get the year divide the resultant date into 
			// total seconds in a year (365*60*60*24) 
			$years = floor($diff / (365*60*60*24)); 


			// To get the month, subtract it with years and 
			// divide the resultant date into 
			// total seconds in a month (30*60*60*24) 
			$months = floor(($diff - $years * 365*60*60*24) 
										/ (30*60*60*24)); 


			// To get the day, subtract it with years and 
			// months and divide the resultant date into 
			// total seconds in a days (60*60*24) 
			$days = floor(($diff - $years * 365*60*60*24 - 
						$months*30*60*60*24)/ (60*60*24)); 


			// To get the hour, subtract it with years, 
			// months & seconds and divide the resultant 
			// date into total seconds in a hours (60*60) 
			$hours = floor(($diff - $years * 365*60*60*24 
				- $months*30*60*60*24 - $days*60*60*24) 
											/ (60*60)); 


			// To get the minutes, subtract it with years, 
			// months, seconds and hours and divide the 
			// resultant date into total seconds i.e. 60 
			$minutes = floor(($diff - $years * 365*60*60*24 
					- $months*30*60*60*24 - $days*60*60*24 
									- $hours*60*60)/ 60); 


			// To get the minutes, subtract it with years, 
			// months, seconds, hours and minutes 
			$seconds = floor(($diff - $years * 365*60*60*24 
					- $months*30*60*60*24 - $days*60*60*24 
							- $hours*60*60 - $minutes*60)); 

			// Print the result 

			// printf("%d years, %d months, %d days, %d hours, " 
			// 	. "%d minutes, %d seconds", $years, $months, 
			// 			$days, $hours, $minutes, $seconds);
			
			$cusMin[]= $date_times->CustomerName;
			$cusMin[]= $minutes;
			$waiting_array['CustomerName']= $cusMin;
		}
		
		
		 $response['status'] = 1;
		 $response['result'] = $waiting_array;
		}else{
			$response['status'] = 0;
			$response['message'] = 'Required parameter missing!';
		}
        echo json_encode($response);
		
	}
	
		
}
