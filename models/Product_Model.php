<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product_Model extends CI_Model {
	
		
   function __construct() {

		parent::__construct();
    	
	}
	
	public function getProductByCategoryId($category_id){
		
		/* $this->db->select('p.product_id,p.name as product_name,p.main_image as product_image, pp.price as product_price, pp.unit_id as unit, pp. quantity, u.unit ');
		$this->db->from('tbl_product p');
		$this->db->join('tbl_product_price pp', 'pp.product_id=p.product_id','inner');
		$this->db->join('tbl_unit u', 'u.unit_id=pp.unit_id','inner');
		$this->db->where('p.category_id', $category_id);
		$query = $this->db->get();
		$result = $query->result_array();
		return $result; */
		
		$this->db->select('product_id,name as product_name,main_image as product_image,price,sort_description');
		$this->db->from('tbl_product');
		$this->db->where('vendor_id', $category_id);
		$query = $this->db->get();
		$result = $query->result_array();
		return $result;
	}
	
	public function getProductPriceByProductId($pid){
		$this->db->select('u.unit, pp.quantity, pp.price');
		$this->db->from('tbl_product_price pp');
		$this->db->join('tbl_unit u', 'u.unit_id=pp.unit_id','inner');
		$this->db->where('pp.product_id', $pid);
		$query = $this->db->get();
		$result = $query->result_array();
		return $result; 
	}
	
}

?>