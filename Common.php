<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common {
	     private  $ID =  NULL  ;
         private  $logerID =  NULL  ;
    public function __construct()
    {
        $this->CI =& get_instance();
		$this->CI->load->model('Dbfunction');
		$this->CI->load->model('Vendor_model');
		$this->CI->load->model('Doctor_model');
		$this->CI->load->model('Site_model');
		$this->ID = $this->CI->session->userdata('role_id'); 
        $this->logerID = $this->CI->session->userdata('loger_id');
    }
	
	public function test()
	{
//		echo 'hello';
//		die();
	}
	
	public function send_email($data)
	{
		$config['protocol'] = 'smtp';
		$config['smtp_host'] = 'ssl://smtp.gmail.com';
		$config['smtp_port'] = '465';
		$config['smtp_user'] = 'rudranshu.shastri@gmail.com';
		$config['smtp_pass'] = 'Roshni@1';
		$config['charset'] = 'utf-8';
		$config['newline'] = "\r\n";
			
		// Loads the email library
		$this->CI->load->library('email');
		// FCPATH refers to the CodeIgniter install directory
		// Specifying a file to be attached with the email
		//$file = FCPATH . 'license.txt';
		// Defines the email details
		$this->CI->email->from('rudranshu.shastri@gmail.com', 'My Name');
		$this->CI->email->to('rudratosh@bridgelogicsystem.com');
		//$this->email->cc('another@example.com');
		//$this->email->bcc('one-another@example.com');
		$this->CI->email->subject('Email Test');
		$this->CI->email->message('Testing the email');
		//$this->email->attach($file);
		// The email->send() statement will return a true or false
		// If true, the email will be sent
		if ($this->CI->email->send()) {
		  echo "All OK";
		} else { 
		  echo $this->CI->email->print_debugger();
		}
  }
	  
	public function getLogerID($id, $type){
		$table = 'doctor';
		$col = 'id';
			
		if($type == 'vendor')
		{
			$table = 'vendor';
			$col = 'vendorID';
		}
		
		if($type == 'visitor')
		{
			$table = 'visitor';
			$col = 'visitorID';
		}			
		$where = array($col=>$id); 
		return $this->CI->Dbfunction->getRowResultArray($table,$where)->loginID; 
	}
	
	public function getOrderByID($id){ 
		$where = array('order_id'=>$id); 
		return $this->CI->Dbfunction->getRowResultArray('orderdetails',$where); 
		
	}
	
	public function getAttributes(){ 
		 
		return $this->CI->Dbfunction->getAllResult('attributes'); 
	}
	
	public function getCatInfo($id){ 
		 $where = array('id'=>$id);
		return $this->CI->Dbfunction->getRowResultArray('category',$where); 
	}
	
	public function getShopDP($id){ 
		$where = array('contentID'=>$id,'contentType'=>'vendor','DP'=>1);
		
		$visit = $this->CI->Dbfunction->getRowResultArray('images',$where);
		return $visit->imagePath; 	 
	}
	
	public function getVnfsImages($id){ 
		$where = array('contentID'=>$id,'contentType'=>'vendor');
		return $this->CI->Dbfunction->getAllResultArray('images',$where);
		

	}
 
	public function getResDP($id)
	{ 
		$where = array('contentID'=>$id,'contentType'=>'resource','DP'=>1);
		
		$visit = $this->CI->Dbfunction->getRowResultArray('images',$where);
		
		if($visit != '')
		{ 
			return $visit->imagePath;
		}
		else
		{
			return $this->getDefaultImg($id); 	 
		}
		 
	}
	
	function getDefaultImg($id)
	{	 
	    $Rid = $this->getResType($id);
		$where = array('id'=>$Rid); 
		if(!empty($this->CI->Dbfunction->getRowResultArray('resource_type',$where)->image))
		{
			return $this->CI->Dbfunction->getRowResultArray('resource_type',$where)->image;
		}else
		{
			return 0;
		}
		
	}
	
	
	function getDefaultImgName($name)
	{	 
	    $where = array('name'=>$name); 
		return  $this->CI->Dbfunction->getRowResultArray('resource_type',$where)->image;
	}
	
	
	function getResType($id)
	{
		$where = array('id'=>$id); 
		$res= $this->CI->Dbfunction->getRowResultArray('resource',$where);
		if(count($res)>0)
		{
			return $res->type;
		}
		else
		{
			return false;
		}
	}
        
        
	 	
	public function getRatings($id='',$type='')
	{   $tot = $count = 0;
		if($id!='' && $type!=''){
                    $where = array('createdFor'=>$id,'res_type' => $type );
                }
                else{
                    $where = array('createdFor'=>$this->ID);
                }
		$ratings = $this->CI->Dbfunction->getAllResultArray('feedback',$where);
               if(!empty($ratings)){
			foreach($ratings as $rate)
			{   
				$tot = $rate->ratings +$tot;
				$count++;
			}
			return  round((($tot/$count)* 20),1);
		}
		 else
		 {
			 return 0;
		 }
		
	}
 
        function getResData($clinicID,$clm)
	{
		$where = array('id'=>$clinicID); 
		$res =  $this->CI->Dbfunction->getRowResultArray('resource',$where);
	return $res->$clm; 
                
        }
        
	//end common functon //
	
	
/// Visitor Data //
	public function getVsDataS($id,$clm){
		
		$where = array('visitorID'=>$id);
		
		$visit = $this->CI->Dbfunction->getRowResultArray('visitor',$where);
		return $visit->$clm; 	 
	}
	
	
	public function getVsDP($id){ 
		$where = array('contentID'=>$id,'contentType'=>'visitor','DP'=>1);
		
		$visit = $this->CI->Dbfunction->getRowResultArray('images',$where);
                 if(empty($visit->imagePath))
					{
						return $this->getDefaultImgName('visitor');
					}
					else
					{
						return $visit->imagePath; 
					}	
			 
	}
	
	public function getVsImages($id){ 
		$where = array('contentID'=>$id,'contentType'=>'visitor');
		return $this->CI->Dbfunction->getAllResultArray('images',$where);
	}
	
	function getVisitorName($id)
	{
            $where = array('visitorID'=>$id); 
			$visit = $this->CI->Dbfunction->getRowResultArray('visitor',$where);
			 
			return $visit->first_name.' '.$visit->last_name; 
	}
	
	public function getVsAppointment(){ 
		$where = array('custID'=>$this->ID); 
		return $this->CI->Dbfunction->getAllResultArray('appointment',$where); 
	}
	
	public function getVsPrescription(){ 
		$where = array('custID'=>$this->ID); 
		return $this->CI->Dbfunction->getAllResultArray('prescription',$where); 
	}
	
	public function getVsOrders(){ 
		$where = array('custID'=>$this->ID); 
		return $this->CI->Dbfunction->getAllResultArray('order',$where); 
	}
		 
	public function getVsPayment(){ 
		$where = array('custID'=>$this->ID); 
		return $this->CI->Dbfunction->getAllResultArray('transaction',$where); 
	}	
	
	public function getVsFav(){ 
		$where = array('givenBy'=>$this->ID); 
		return $this->CI->Dbfunction->getAllResultArray('favorite',$where); 
	}

	function getVsPresc()
	{
		$where = array('custID'=>$this->ID); 
		return $this->CI->Dbfunction->getAllResultArray('prescription',$where); 
	}
// end visitor Data

	
/// Vendor Data //
function getVendorName($id)
	{
			$where = array('vendorID'=>$id); 
			$visit = $this->CI->Dbfunction->getRowResultArray('vendor',$where);
			 
			return $visit->first_name.' '.$visit->last_name; 
	}
	
	public function getVnDataS($id,$clm){
		
		$where = array('vendorID'=>$id);
		
		$visit = $this->CI->Dbfunction->getRowResultArray('vendor',$where);
		return $visit->$clm; 	 
	}
	
	
	public function getVnDP($id){ 
		$where = array('contentID'=>$id,'contentType'=>'vendor','DP'=>1);
		
		$visit = $this->CI->Dbfunction->getRowResultArray('images',$where);
		
					if(empty($visit->imagePath))
					{
						return $this->getDefaultImgName('vendor');
					}
					else
					{
						return $visit->imagePath; 
					}	
					
	}
	
	public function getVnImages($id){ 
		$where = array('contentID'=>$id,'contentType'=>'vendor');
		return $this->CI->Dbfunction->getAllResultArray('images',$where);
	}
 
 
	public function getVnOrders(){  
		return $this->CI->Dbfunction->getVendorOrders($this->ID); 
	}
		 
	public function getVnPayment(){ 
		$where = array('custID'=>$this->ID); 
		return $this->CI->Dbfunction->getAllResultArray('transaction',$where); 
	}	
	
	public function getVnMember(){ 
		$memberID = $this->CI->getVnDataS($this->ID,'package_id');
		$where = array('id'=>$memberID); 
		return $this->CI->Dbfunction->getRowResultArray('memberships',$where); 
	}

	public function getVnProducts(){ 
			 
			$where = array('updatedBy'=>$this->ID); 
			return $this->CI->Dbfunction->getAllResultArray('product',$where); 
		}	

	public function getVnServices(){ 
			 
			$where = array('createdBy'=>$this->ID); 
			return $this->CI->Dbfunction->getAllResultArray('services',$where); 
		}			
	
	public function getVnCats(){  
			return $this->CI->Vendor_model->getVendorCat($this->ID); 
		}	

	public function getVnShops(){  
			$where = array('vendor_id'=>$this->ID); 
			return $this->CI->Dbfunction->getAllResultArray('shop_list',$where); 
		}	

		public function getVnGifts(){  
			$where = array('vendorID'=>$this->ID); 
			return $this->CI->Dbfunction->getAllResultArray('giftcards',$where); 
		}		
					
		function getVnRel($vendorID='')
	{ 
		if($vendorID != '')
		{	
		return $this->CI->Vendor_model->getRelVn($vendorID);
		}
		else
		{
			return false;
		}
	}	
	
// end vendor Data

// DOctor Data



	function getDocRel($DocID='')
	{ 
		if($DocID != '')
		{	
		return $this->CI->Doctor_model->getRelDoc($DocID);
		}
		else
		{
			return false;
		}
	}
	function getDoctorName($id)
	{
            $where = array('id'=>$id); 
			$visit = $this->CI->Dbfunction->getRowResultArray('doctor',$where);
			 
			return $visit->first_name.' '.$visit->last_name; 
	}
	
	function getDocPList()
	{
		return $this->CI->Doctor_model->getPList($this->ID);
	}

	function getDocPres()
	{
		$where = array('docID'=>$this->ID); 
		return $this->CI->Dbfunction->getAllResultArray('prescription',$where); 
	}
        
        public function getDocDP($id){ 
            $where = array('contentID'=>$id,'contentType'=>'doctor','DP'=>1);

            $visit = $this->CI->Dbfunction->getRowResultArray('images',$where);


            if(empty($visit->imagePath))
            {
                    return $this->getDefaultImgName('doctor');
            }
            else
            {
                            return $visit->imagePath; 	 
            }
	}
        
        function getDocClinics($id='')
        {
          
              if($id =='')
              {        
             return  $this->CI->Doctor_model->getClinic($this->ID);
              }
              else
              {
                return  $this->CI->Doctor_model->getClinic($id);
              }
              
        }
        
//end doctor data 
 
//Product data
		public function getProName($id)
		{
			$where = array('id'=>$id); 
			$visit = $this->CI->Dbfunction->getRowResultArray('product',$where);
		    if(count($visit)>0)
		    {
		      //  var_dump($visit);die;
		        return $visit->productName;
		    }
		    else
		    {
		        
		        return "";
		    }
		}


		
	
	public function getVnByPro($id)
		{
			$where = array('id'=>$id); 
		return  $this->CI->Dbfunction->getRowResultArray('product',$where)->createdBy;
		}
	public function getProRel($proID)
	{
		return $this->CI->Dbfunction->getProRel1($proID);
	}
		
	//Notification

		public function setNotification($notification,$given_to,$given_by, $title, $type_id,$action)
		{	
			// echo "$notification,$given_to,$given_by";

			$data =array('notification'=>$notification,'given_to'=>$given_to,'given_by'=>$given_by, 'title'=>$title, 'type_id'=>$type_id, 'action'=>$action );
		    $this->CI->Dbfunction->insertAll('notification', $data);		

			// die();
		}
                	
        public function getproDP($id){ 
		$where = array('contentID'=>$id,'contentType'=>'product','image_type'=>'featured');
		
		$visit = $this->CI->Dbfunction->getRowResultArray('images',$where);
		
					if(empty($visit->imagePath))
					{
						return $this->getDefaultImgName('product');
					}
					else
					{
						return $visit->imagePath; 
					}	
					
	}
	
// Resources 

	function getResRel($resID)
	{ 
		$Rid = $this->getResType($resID);
		if($Rid != '')
		{	
		return $this->CI->Site_model->getRelRes($resID,$Rid);
		}
		else
		{
			return false;
		}
	}	

		
}

*********************************************Session*****************************************************************
	$config['sess_driver'] = 'files';
$config['sess_cookie_name'] = 'ci_session';
$config['sess_expiration'] = 7200;
$config['sess_save_path'] = sys_get_temp_dir();
$config['sess_match_ip'] = FALSE;
$config['sess_time_to_update'] = 300;
$config['sess_regenerate_destroy'] = FALSE;
