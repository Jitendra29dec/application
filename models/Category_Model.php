<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Category_Model extends CI_Model {
	
		
   function __construct() {

		parent::__construct();
    	
	}
	
	Public function getCategory($id){
		
		$this->db->select('category_id, category_name, image, description, cat_type_id');
		$this->db->from('tbl_category');
		$this->db->where('cat_type_id',$id);
		$this->db->order_by('sortorder','asc');
		$query = $this->db->get();
		$result = $query->result_array();
		return $result;
	}
	
	public function getCategoryType(){
		
		$query = $this->db->query("select * from tbl_category_type");
		return $query->result();
	}
	
}
?>