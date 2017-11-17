<?php

class Site_model extends CI_Model {
	
	
	function __construct()
	{
		parent:: __construct();
		
	}
	
	function clinic_row($id)
	{
	    	$this->db->select('*');
            $this->db->where('id',$id);      
            $this->db->where('type',1);      
            $query=$this->db->get('resource');
		//	echo $this->db->last_query();
		 	return $query->row();
	}
	
	function qlf_row($id)
	{
			$this->db->select('*');
            $this->db->where('id',$id);      
            $query=$this->db->get('education');
			return $query->row();
	}
	
	function doc_slots($cln,$doc)
	{
			$this->db->select('mon,tue,wed,thu	,fri,sat,sun');
            $this->db->where('clinic_id',$cln);      
            $this->db->where('doc_id',$doc);      
            $this->db->where('is_active',1);      
            $query=$this->db->get('doc_slot');
			 
			 
		 	return $query->result_array();
	}
	
	function maxlen_LOCATION()
	{
		$query = $this->db->query("SELECT max(CHAR_LENGTH (location)) as count FROM `doctor` order by 'location' desc");
		if($query->num_rows()>0)
		{
			return $query->row();
		}
	}
	
	function maxlen_CITY()
	{
		$query = $this->db->query("SELECT max(CHAR_LENGTH (city)) as count FROM `doctor` order by 'city' desc");
		if($query->num_rows()>0)
		{
			return $query->row();
		}
	}
	 
	 function maxlen_ZIP()
	{
		$query = $this->db->query("SELECT max(CHAR_LENGTH (pin_code)) as count FROM `doctor` order by 'pin_code' desc");
		if($query->num_rows()>0)
		{
			return $query->row();
		}
	}
	
	public function list_spe()
	{	$this ->db->select('*');
        // $this ->db ->where('status', 1);
        $query = $this->db->get('doctor');
		$this->db->last_query(); 
       
 return $query->result();
	} 
    
    public function list_res()
	{	$this ->db->select('*');
        $this ->db ->where('status', 1);
        $query = $this->db->get('resource_type');
		//$this->db->last_query(); 
       
 return $query->result();
	}
    
    public function list_cat()
	{	$this ->db->select('*');
        $this ->db ->where('isActive', 1);
        $query = $this->db->get('category');
		//$this->db->last_query(); 
       
 return $query->result();
	}
    
        public function getPageSlug($slug)
        {
        $this ->db->select('*');
        $this ->db ->where('slug', $slug);
        $query = $this->db->get('pages');
	
        return $query->result();
        }

public function getAllPages()
        {
        $this ->db->select('*');
        $query = $this->db->get('pages');
	return $query->result();
        }
public function orderData($id)
{
	$this->db->select('*');
	$this->db->where('orderID',$id);
	$query = $this->db->get('orderdetails');
	return $query->result();
	
	
}

public function getTransaction($userID)
{
	$this->db->select('*');
	$this->db->where('custID',$userID);
	$query = $this->db->get('transaction');
	return $query->result();
 	
}

public function getPrescription($userID)
{
	$this->db->select('*');
	$this->db->where('custID',$userID);
	$query = $this->db->get('prescription');
	return $query->result();
 	
}
public function getDoctorName($doctorID)
{
	$this->db->select('*');
	$this->db->where('id',$doctorID);
	$query = $this->db->get('doctor');	
	return $query->row()->first_name;
 	
}

public function getClinicName($clinicID)
{
	$this->db->select('*');
	$this->db->where('id',$clinicID);
	$query = $this->db->get('clinic');
	return $query->row()->name;
 	
}

public function prescriptionDetails($prescriptionID)
{
	$this->db->select('*');
	$this->db->where('prescriptionID',$prescriptionID);
	$query = $this->db->get('prescriptiondetails');
	return $query->result();
 	
}

public function appointmentVisitor($userID)
{
	$this->db->select('*');
	$this->db->where('custID',$userID);
	$query = $this->db->get('appointment');
	return $query->result();
 	
}
function getClinicDoctorID($doctorID)
{
	$query = $this->db->query(
	' select doc_clinic.doc_id , resource.name , doc_clinic.clinic_id
	from doc_clinic
	inner join resource ON doc_clinic.clinic_id = resource.id
	where resource.type = 1 and doc_clinic.doc_id = '.$doctorID.' ;
	
	');
	return $query->result();
}

public function getproductcategoryID($categoryID)
{
	$query = $this->db->query(
	' select product.catID 	from product
	inner join category ON product.catId = product.id
	where product.id = 1 and category.catID = '.$categoryID.' ;
	
	');
	return $query->result();
}

function getTimeSlots($data, $day)
{
    $this->db->select($day.' as day', False);
    $this->db->where('doc_id',$data['doctorID']);
    $this->db->where('clinic_id',$data['clinicID']);
    $query = $this->db->get('doc_slot');
    return $query->row();

}
 function getcategory()
{   

    //$this->db->where('id',$id);
    $query = $this->db->get('category');
    return $query->result();
}

function getproduct()
{
    $query = $this->db->get('product')->result;
   
    return $query;
}


function getPage($pageSlug)
{
    $this->db->where('slug', $pageSlug);
    $query = $this->db->get('pages');
    return $query->row();
}

function getPages()
{
    $query = $this->db->get('pages');
    return $query->result();
}

 function productByCat($catID)
{
	$this->db->where('catID', $catID);
 	$query = $this->db->get('product');
 	 
	
 	return $query->result();
 }

 function productByres($id)
{
	$this->db->where('type', $id);
 	$query = $this->db->get('resource');
 	 
 	return $query->result();
 }

 function getVisitor($loginID)
{
	$this->db->where('loginID', $loginID);

 	$query = $this->db->get('visitor');
 	return $query->row();
 }
 
 function getProductDiscount()
 {			

	        $this->db->select('round((discount/price)*100) discount');
			$this->db->DISTINCT('discount'); 
	        $query = $this->db->get('product',10);

                return $query->result();
 }
  function getProductBrand()
 {
	   $this->db->select('brand');
		$this->db->group_by('brand'); 
	 $query = 	$this->db->get('product', 10);
		return $query->result();
 }

 function getProductPrice()
 {
 	$query=$this->db->query("SELECT  min(price) as minprice, max(price) as maxprice  FROM `product`");
	// echo $this->db->last_query();die;
    return $query->row();
 }

 function getRelRes($id,$type)
   {
		$query = $this->db->query("SELECT * FROM `services` WHERE res_type = ".$type." and service in (select service from services where createdBy = ".$id.") and createdBy !=".$id." "); 
	 
                return $query->result(); 
   }
 
}

?>