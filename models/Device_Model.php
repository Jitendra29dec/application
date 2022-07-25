<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Device_Model extends CI_Model {
	
		
   function __construct() {

		parent::__construct();
    	
	}
	
	public function addDeviceDetail($deviceType,$phoneSerial,$imei,$versionName,$versionCode){
		
		$data = array(
			'device_type'=>$deviceType,
			'phone_serial'=>$phoneSerial,
			'imei'=>$imei,
			'version_name'=>$versionName,
			'version_code'=>$versionCode
		);
		
		$added = $this->db->insert('tbl_device',$data);
		if($added){
			
			$res = 1;
			
		}else{
			
			$res = 0;
		}
		
		return $res;
		
	}
	
}

?>