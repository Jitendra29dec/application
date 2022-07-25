<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Locality_Model extends CI_Model {
	
		
   function __construct() {

		parent::__construct();
    	
	}
	
	Public function geLocality(){
		
		$this->db->select('area_name');
		$this->db->from('tbl_area');
		$this->db->order_by('area_id','asc');
		$query = $this->db->get();
		$result = $query->result_array();
		return $result;
	}
	
}

?>