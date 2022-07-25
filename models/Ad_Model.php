<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ad_Model extends CI_Model {
	
		
   function __construct() {

		parent::__construct();
    	
	}
	
	Public function getAd(){
		
		$this->db->select('ad_id, name, image, description');
		$this->db->from('tbl_ads');
		$this->db->order_by('ad_id','asc');
		$query = $this->db->get();
		$result = $query->result_array();
		return $result;
	}
	
}
?>