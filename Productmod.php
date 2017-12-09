<?php

class Productmod extends CI_Model{

	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	

	function get_all()
	{
		$obj=$this->db->get("product_tbl");
		return $obj;
	}
	function get_by_id($id)
	{
		$this->db->where("id", $id);
		$obj=$this->db->get("product_tbl");
		return $obj;
	}
	function insert($arr)
	{
		// print_r($arr);die;
		$this->db->insert("product_tbl", $arr);
	}
	function update($id, $arr)
	{
		$this->db->where("id", $id);
		$this->db->update("product_tbl", $arr);
	}
	function delete($id)
	{
		$this->db->where("id", $id);
		$this->db->delete("product_tbl");

	}
	function delete_by_cate_id($arr)
	{
		$this->db->delete("product_tbl", $arr);
	}
}

?>