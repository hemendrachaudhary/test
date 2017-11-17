<?php defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
class Dbfunction extends CI_Model {
	function __construct() {
		parent::__construct ();
                $this->load->database();
	}
	public function insertAll($table, $data) {
		// $this->db->last_query();;
		$this->db->insert($table, $data);
		$insert_id = $this->db->insert_id();
			if ($insert_id > 0) {
				return $insert_id;
			} else {
				return false;
			}
	}
	public function getAllResult($table){
              	$allData = $this->db->get($table)->result();
		return $allData;
	}
	
	public function getAllResultArray($table, $where) {
		$allData = $this->db->get_where($table, $where)->result();
                return $allData;    
	}
	
	public function  getRowResultArray($table, $where){
		$allData = $this->db->get_where($table, $where)->row();
		  
                return $allData;
                
	}
	
	public function getAllResultWhereOrderBy($table, $where, $colum) { 
	
		if(empty($where)){
			$allData = $this->db->order_by($colum, "desc")->get($table)->result();
			return $allData;
		}else{
			$allData = $this->db->order_by($colum, "desc")->get_where($table, $where)->result();
			return $allData;
		}
	}

	public function getAllResultTwoDate($table, $toDate, $fromDate, $colum)
	{
		$this->db->select('*');
		$this->db->from($table);
		$this->db->where('sales_date <=', $fromDate);
		$this->db->where('sales_date >=', $toDate);
		$this->db->order_by($colum, "desc");

		 return $result = $this->db->get()->result();
		 $this->db->last_query();
	}
	
	public function getAllResultTwoDateWithWhere($table, $toDate, $fromDate, $colum, $where)
	{
		$this->db->where('sales_date <=', $fromDate);
		$this->db->where('sales_date >=', $toDate);
		$this->db->order_by($colum, "desc");

		 return $result = $this->db->get_where($table, $where)->result();
		 $this->db->last_query();
	}

	public function showCustomerPaymentsReport($table, $toDate, $fromDate, $colum, $where)
	{
		$this->db->where('created_at BETWEEN "'. date("Y-m-d 00:00:00", strtotime($toDate)). '" and "'. date("Y-m-d 23:59:00", strtotime($fromDate)).'"');
		$this->db->order_by($colum, "desc");

		 return $result = $this->db->get_where($table, $where)->result();
		  $this->db->last_query();
	}
	
	public function getAllResultWhereColumGroupBy($table, $where, $colum) {
		
		if(!empty($where)){
			$allData = $this->db->group_by($colum)->get_where($table, $where)->result();
			return $allData;
		}else{
			$allData = $this->db->group_by($colum)->get($table)->result();
			return $allData;
			
		}
	}
	
	public function getAllResultJoin($tableA, $tableAid, $tableB, $tableBid){
		$this->db->select('*');
		$this->db->from($tableA);
		$this->db->join($tableB, $tableAid = $tableBid);
		$allData = $this->db->get()->result();
		return $allData;
		
	}
        

	public function getAllResultRightJoin($tableA, $tableAid, $tableB, $tableBid){
		$this->db->select('*');
		$this->db->from($tableA);
		$this->db->join($tableB, $tableAid = $tableBid , 'outer');
		$allData = $this->db->get()->result();
		return $allData;
		
	}
	
	public function getAllResultJoinWithWhere($tableA, $tableAid, $tableB, $tableBid, $data){
		$this->db->select('*');
		$this->db->from($tableA);
		$this->db->join($tableB, $tableAid = $tableBid);
		$this->db->where($data);
		$allData = $this->db->get()->result();
		return $allData;
		
	}
	
	public function updateAllResultWhere($table, $where, $data) {
		$this->db->where($where)->update($table, $data);
		$afftectedRows = $this->db->affected_rows();
              
		return $afftectedRows;
	}
	
	public function getMaxNumber($table, $data){
		$allData = $this->db->select_max($data)->get($table)->row();
		return $allData;
		 
	}
	
	public function totalCount($table, $where){
		$allData = $this->db->where($where)->count_all_results($table);
		  $this->db->last_query();
		return $allData;  
	}
	
	public function totalSum($sum, $table, $where){
		
		$allData = $this->db->select_sum($sum)->get_where($table, $where)->row();
		return $allData;
	}

	public function deleteArray($table,$data){
		$allData = $this->db->where($data)->delete($table);
		return $allData;
		
	}

   function getProductsLike($q){

       $this->db->select('*');
    $this->db->like('productName', $q);
    $query = $this->db->get('product');
    
    return $query->result();
 }

 function getProNameByOrderID($id){
    $where= array('order_id'=>$id);
   $proName = $this->Dbfunction->getRowResultArray('orderdetails',$where);
    
    if(count($proName)>0){

      return $proName->productName;
    }
    else{

      return " ";
    }
 }

   function getLikeWhere($q, $where, $tab){

       $this->db->select('*');
       $this->db-> where($where);
    $this->db->like('first_name', $q);
    
    $query = $this->db->get($tab);
    return $query->result();
 }
 
 function getResource($q, $where, $tab){

       $this->db->select('*');
       $this->db-> where($where);
    $this->db->like('name', $q);
    $query = $this->db->get($tab);
    
    return $query->result();
 }
        
    function getProducts($q){
    $this->db->select('*');
    $this->db->like('productName', $q);
    $query = $this->db->get('product');
    if($query->num_rows() > 0){
      foreach ($query->result_array() as $row){
       
        $new_row['label']=htmlentities(stripslashes($row['productName']));
        $new_row['id']=htmlentities(stripslashes($row['id']));
        $new_row['value']=htmlentities(stripslashes($row['id']));
        $new_row['picture']=htmlentities(stripslashes($row['picture']));
        $new_row['price']=htmlentities(stripslashes($row['price']));
        $new_row['quantity']=htmlentities(stripslashes($row['quantity']));
        $new_row['tax']=htmlentities(stripslashes($row['gst']));
        $new_row['discount']=htmlentities(stripslashes($row['discount']));
        $new_row['cost']=htmlentities(stripslashes($row['cost']));
        $row_set[] = $new_row; //build an array
      }
      echo json_encode($row_set); //format the array into json data
    }
  }

  function ajaxProducts($vendor){
	  
	  if($vendor=="")
	  {
		   $this -> db -> select('*');
			$this -> db -> from('product');
			$query = $this -> db ->get();
	  }
	  else
	  {
	  
	   $this -> db -> select('*');
		$this -> db -> from('product');
		$this -> db -> where('createdBy',$vendor);
		$query = $this -> db ->get();
	  }
    return $query -> result();

  }
  function ajaxGallery($id){
	  
	  
		   $this -> db -> select('*');
			$this -> db -> from('images');
		$this -> db -> where(array('contentID'=>$id, 'contentType'=>'product'));
			$query = $this -> db ->get();
	 
    return $query -> result();

  }
  
  function getSpec($q){
    $this->db->select('*');
    $this->db->like('name', $q);
    $query = $this->db->get('specialization');
    if($query->num_rows() > 0){
      foreach ($query->result_array() as $row){
       
        $new_row['label']=htmlentities(stripslashes($row['name']));
        $row_set[] = $new_row; //build an array
      }
      echo json_encode($row_set); //format the array into json data
    }
  }
  function getQua($q){
    $this->db->select('*');
    $this->db->like('name', $q);
    $query = $this->db->get('education');
    if($query->num_rows() > 0){
      foreach ($query->result_array() as $row){
       
        $new_row['label']=htmlentities(stripslashes($row['name']));
        $row_set[] = $new_row; //build an array
      }
      echo json_encode($row_set); //format the array into json data
    }
  }
 function getCollege($q){
    $this->db->select('*');
    $this->db->like('college', $q);
    $query = $this->db->get('doc_qlf');
    if($query->num_rows() > 0){
      foreach ($query->result_array() as $row){
       
        $new_row['label']=htmlentities(stripslashes($row['college']));
       $row_set[] = $new_row; //build an array
      }
      echo json_encode($row_set); //format the array into json data
    }
  }
    
  public function getdoctor(){
      
    //$this->db->where('id', '$id');   
   // $this->db->select('name');
    //$this->db->from('doctor');
    $q = $this->db->get('doctor')->result();
    
    return $q;
}

public function getdoc_clinic(){
   
    $q = $this->db->get('doc_clinic')->result();
    return $q;
}
public function getdoc_slot(){
    $q = $this->db->get('doc_slot')->result();
    return $q;
}

function createOrder($data)
    {
    
   $customer_name =$this->input->post('customer_name');
   $mobile_no = $this->input->post('mobile_no');
   $email =$this->input->post('email');
   $orderDiscount =$this->input->post('orderDiscount');
   $orderTax = $this->input->post('orderTax');
   $quantityTotal = $this->input->post('quantityTotal');
   // $orderTax =$this->input->post('orderTax');
   $paymentMethod = $this->input->post('paymentMethod');
   // $orderDiscount = $this->input->post('orderDiscount');
   $cdt= date("Y-m-d H:i:s");
   $custID = $this->input->post('custID');

   $order = array(
       'custID'=>$custID,
       'customerName'=> $customer_name,
       'custMob'=> $mobile_no,
       'custEmail'=> $email,
       'quantity'=>$quantityTotal,
       'orderTax'=>$orderTax,
       'orderDiscount' =>$orderDiscount,
       'paymentMethod' =>$paymentMethod,
       'orderDate' =>$cdt,
      
);

    $this->db->insert('order',$order);
    $insert_id = $this->db->insert_id();
   
    if($insert_id){
    $product = $this->input->post('productGP');
    $price = $this->input->post('price');
    $quantity = $this->input->post('quantity');
    $tax  = $this->input->post('tax');
    $discount = $this->input->post('discount');
    $subtotal = $this->input->post('subtotal');
       // die();
    for( $i=0; $i<count($product);$i++)
    {
    $orderDetails = array(
        'order_id' => $insert_id,
        'productID'=>$this->input->post('productGPID')[$i],
        'productName' => $product[$i],
        'price' => $price[$i],
        'quantity'=> $quantity[$i],
        'tax' => $tax[$i],
        'discount' => $discount[$i],
        'subtotal' => $subtotal[$i],
        'orderDate' => $cdt, 
        //'cost'=>$this->input->post('cost'),
        //'paymentMethod' =>$paymentMethod[$i], 
    );
   //print_r($orderDetails);exit();
    $this->db->insert('orderdetails',$orderDetails);
   }
   redirect ("List-Order");
  } 
   
}


function upadateOrder($data)
    {
    
   $customer_name =$this->input->post('customer_name');
   $mobile_no = $this->input->post('mobile_no');
   $email =$this->input->post('email');
   $orderDiscount =$this->input->post('orderDiscount');
   $orderTax = $this->input->post('orderTax');
   $quantityTotal = $this->input->post('quantityTotal');
   // $orderTax =$this->input->post('orderTax');
   $paymentMethod = $this->input->post('paymentMethod');
   // $orderDiscount = $this->input->post('orderDiscount');
   $cdt= date("Y-m-d H:i:s");
   $custID = $this->input->post('custID');

   $order = array(
       'custID'=>$custID,
       'customerName'=> $customer_name,
       'custMob'=> $mobile_no,
       'custEmail'=> $email,
       'quantity'=>$quantityTotal,
       'orderTax'=>$orderTax,
       'orderDiscount' =>$orderDiscount,
       'paymentMethod' =>$paymentMethod,
       'orderDate' =>$cdt,
      
);
        // print_r($order);die;
        $this->db->set($order)
			->where('orderID',$this->input->post('order_id'))
			->update('order', $order);
	// $this->db->insert('order',$order);
	
    // $insert_id = $this->db->insert_id();
      // die();
    if(isset($_POST['order_id'])){
    $product = $this->input->post('productGP');
    $price = $this->input->post('price');
    $quantity = $this->input->post('quantity');
    $tax  = $this->input->post('tax');
    $discount = $this->input->post('discount');
    $subtotal = $this->input->post('subtotal');
       // die();
    for( $i=0; $i<count($product);$i++)
    {
    $orderDetails = array(
        'order_id' => $this->input->post('order_id'),
        'productID'=>$this->input->post('productGPID')[$i],
        'productName' => $product[$i],
        'price' => $price[$i],
        'quantity'=> $quantity[$i],
        'tax' => $tax[$i],
        'discount' => $discount[$i],
        'subtotal' => $subtotal[$i],
        'orderDate' => $cdt, 
        //'cost'=>$this->input->post('cost'),
        //'paymentMethod' =>$paymentMethod[$i], 
    );
   // print_r($orderDetails);exit();
    $this->db->insert('orderdetails',$orderDetails);
   }
   redirect ( base_url('List-Order') );
  } 
   
}
  public function create_slug($name)
{
    $count = 0;
    $name = url_title($name);
    $slug_name = $name;             // Create temp name
    while(true) 
    {
        $this->db->where('slug', $slug_name);   // Test temp name
        $query = $this->db->get('pages');
        if ($query->num_rows() == 0) break;
        $slug_name = $name . '-' . (++$count);  // Recreate new temp name
    }
    return $slug_name;// Return temp name
}
   public function editproduct($id)
 {               
                $this->db->where('id', $id);    
                $query = $this->db->get('product');
                return $query->result();
 
 }
 
 public function getProRel($proID)
 {
	 $query = $this->db->query("SELECT * FROM `product` WHERE catID in (select catID from product where `id` = ".$proID.") and `id` != ".$proID." ");
	 return $query->result();
 }
 
 function setRating($data,$visitorID){
         
         
        $feedback = array(
                 'id' => '',
                 'res_type' =>$data['res_type'],
                 'feedback' => $data['feedback'],
                 'ratings' => $data['ratings'],
                 'createdFor'=> $data['createdFor'],
                 'givenBy' => $visitorID,
                
				  
           
             );
       
           $this->db->insert ('feedback',$feedback); 
        
          return true;
       


 }
 
 


	 } 

?>