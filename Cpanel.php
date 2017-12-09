<?php

class Cpanel extends CI_Controller{

	function __construct()
	{
		parent::__construct();
		$this->load->helper("url");
		$this->load->library("session");
		$this->load->model("categorymod");
		$this->load->model("productmod");
		$this->load->model("productcategorymod");
		$this->load->model("adminmod");

		$this->backdoor();
	}
	function backdoor()
	{
		if(! $this->session->userdata("is_admin_logged_in"))
		{
			redirect("admin");
		}
	}

	function index()
	{
		$page_data['pagename']="dash";
		$page_data['title']="Dashboard";
		$this->load->view("admin/admin_layout", $page_data);
	}
	function addpro()
	{
		$obj=$this->categorymod->get_all();
		$page_data['obj']=$obj;


		$this->load->library("form_validation");
		$this->form_validation->set_rules("pro_name", "Product Name", "required");
		$this->form_validation->set_rules("price", "Price", "required|numeric");
		$this->form_validation->set_rules("category", "Category", "required");
		$this->form_validation->set_rules("desc", "Description", "required");
		
		if($this->form_validation->run()==FALSE)
		{
			$page_data['pagename']="addpro";
			$page_data['title']="Add Product";
			$this->load->view("admin/admin_layout", $page_data);
		}
		else
		{
			$config['upload_path']="product_image/";
			$config['allowed_types']="jpg|jpeg|png|gif";
			$config['max_size']=1024;
			$config['encrypt_name']=TRUE;
			$this->load->library("upload", $config);
			if($this->upload->do_upload()==false)
			{
				$a=$this->upload->display_errors();
				$this->session->set_flashdata("msg", $a);
				redirect("cpanel/addpro");
				// die;
			}
			else
			{
				$file_data=$this->upload->data();
				$arr['image']=$file_data['file_name'];
				$arr['product_name']=$this->input->post("pro_name");
				$arr['product_category']=$this->input->post("category");
				$arr['product_desc']=$this->input->post("desc");
				$arr['price']=$this->input->post("price");
				$this->load->model("productmod");
				$this->productmod->insert($arr);
				$this->session->set_flashdata("msg", "Successfuly Added");
				redirect("cpanel/addpro");
			}




		}
	}




	function logout()
	{
		$this->session->sess_destroy();
		redirect("home");
	}


	function add_cate()
	{

		$this->load->library("form_validation");
		$this->form_validation->set_rules("cate_name", "Category Name", "required");
		if($this->form_validation->run()==FALSE)
		{

			$page_data['pagename']="add_cate";
			$page_data['title']="Add Category";
			$this->load->view("admin/admin_layout", $page_data);
		}
		else
		{
			$config['upload_path']="category_image/";
			$config['allowed_types']="jpg|jpeg|png|gif";
			$config['max_size']=1024;
			$config['encrypt_name']=TRUE;
			$this->load->library("upload", $config);
			if($this->upload->do_upload()==false)
			{
				$a=$this->upload->display_errors();
				$this->session->set_flashdata("msg", $a);
				redirect("cpanel/add_cate");
				// die;
			}
			else
			{
				$file_data=$this->upload->data();
				$arr['image']=$file_data['file_name'];
				$arr['category_name']=$this->input->post("cate_name");
				$this->categorymod->insert($arr);
				$this->session->set_flashdata("msg", "Successfuly Added");
				redirect("cpanel/add_cate");
			}
		}

	}
	function view_cate()
	{
		
		
		$obj=$this->categorymod->get_all();



		$page_data['obj']=$obj;
		$page_data['pagename']="view_cate";
		$page_data['title']="View Category";
		$this->load->view("admin/admin_layout", $page_data);
		
	}
	function edit_cate($id)
	{
		
		
		$obj=$this->categorymod->get_by_id($id);
		$this->load->library("form_validation");
		$this->form_validation->set_rules("cate_name", "Category Name", "required");
		if($this->form_validation->run()==FALSE)
		{

			$page_data['obj']=$obj;
			$page_data['pagename']="edit_cate";
			$page_data['title']="View Category";
			$this->load->view("admin/admin_layout", $page_data);
		}
		else
		{
			// echo "update here";
			$arr['category_name']=$this->input->post("cate_name");
			$this->categorymod->update($id, $arr);
			redirect("cpanel/view_cate");
		}
		
	}

	function del_cate($id)
	{
		$arr['id']=$id;
		$arr_pro['product_category']=$id;
		$this->categorymod->delete($arr);
		$this->productmod->delete_by_cate_id($arr_pro);
		redirect("cpanel/view_cate");		
	}
	function del_pro($id)
	{
		$this->productmod->delete($id);
		redirect("cpanel/viewpro");		
	}




	function viewpro()
	{
		$obj=$this->productcategorymod->get_all_prodcut_category(false);
		$page_data['obj']=$obj;
		$page_data['pagename']="view_pro";
		$page_data['title']="View Product";
		$this->load->view("admin/admin_layout", $page_data);
	}
	function edit_pro($id)
	{
		if($this->input->post("submit"))
		{
			$arr['product_name']=$this->input->post("pro_name");
			$arr['product_category']=$this->input->post("category");
			$arr['product_desc']=$this->input->post("desc");
			$arr['price']=$this->input->post("price");
			$this->productmod->update($id, $arr);
			redirect("cpanel/viewpro");
		}
		$page_data['obj']=$this->productmod->get_by_id($id);
		$page_data['obj_c']=$this->categorymod->get_all();
		$page_data['pagename']="edit_pro";
		$page_data['title']="Edit Product";
		$this->load->view("admin/admin_layout", $page_data);
	}
	function setting()
	{
		if($this->input->post("submit"))
		{
			$arr['username']=$this->input->post("username");
			$arr['password']=md5($this->input->post("password"));
			$arr['admin_type']=0;
			if($this->input->post("super"))
			{
				$arr['admin_type']=1;
			}
			
			$obj=$this->adminmod->get_all_by_username($arr['username']);
			if($obj->num_rows()==1)
			{

				$this->session->set_flashdata("msg", "This Username '".$arr['username']."' Already Exists");
			}
			else
			{
				$this->adminmod->insert($arr);
				$this->session->set_flashdata("msg", "New Admin Created");
				
			}
			redirect("cpanel/setting");

		}
		$page_data['obj']=$this->adminmod->get_all();
		$page_data['pagename']="setting";
		$page_data['title']="Adming Setting";
		$this->load->view("admin/admin_layout", $page_data);
	}
	function change_type($id, $type)
	{
		// UPDATE admin_tbl SET admin_type=1 WHERE id=$id
		if($type==1)
			$arr['admin_type']=0;
		if($type==0)
			$arr['admin_type']=1;
		$this->adminmod->update($id, $arr);
		$this->session->set_flashdata("msg", "Admin Type Changed");
		redirect("cpanel/setting");
	}

}
?>
