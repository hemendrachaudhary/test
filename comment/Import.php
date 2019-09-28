<?php 

defined('BASEPATH') OR exit('No direct script access allowed');  
  
class Import extends CI_Controller {  

    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('import_model', 'import');
        // $this ->load-> model('Comman_model');
        $this ->load-> model('Api_model');
        $this ->load-> helper('url');
    }
      

  public function uploadCsv(){
    
    $this->load->view('upload');
  }
  public function uploadData(){

  //if ($this->input->post('submit')) {
            
            $path = 'uploads/';
            require_once APPPATH . "/third_party/PHPExcel-1.8/Classes/PHPExcel.php";
            
            $config['upload_path'] = $path;
            $config['allowed_types'] = 'xlsx|xls';
            $config['remove_spaces'] = TRUE;
            $this->load->library('upload', $config);
            $this->upload->initialize($config);            
            if (!$this->upload->do_upload('uploadFile')) {
                $error = array('error' => $this->upload->display_errors());
            } else {
                $data = array('upload_data' => $this->upload->data());
            }
            if(empty($error)){
              if (!empty($data['upload_data']['file_name'])) {
                $import_xls_file = $data['upload_data']['file_name'];
            } else {
                $import_xls_file = 0;
            }
            $inputFileName = $path . $import_xls_file;
            
            try {
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
                $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
                $flag = true;
                $i=0;
                foreach ($allDataInSheet as $value) {
                if($i>=1){	
                // project id 	
                 $project_id = '';	
                $project_master = $this->Api_model->getSingleRow('project_master',array('project_name'=>$value['B']));
                
                if(count($project_master)<=0)
                {
                    $project_masterD['project_name']        =   $value['B'];
	                $project_masterD['project_desc']        =   $value['B'];
	                $project_masterD['created_at']          =   date('Y-m-d');
	                $project_masterD['updated_at']          =   date('Y-m-d');
	                $project_id = $this->Common_model->insertGetId('project_master',$project_masterD);
                }
                else
                {
                  $project_id = $project_master->id;
                }

                 $user = $this->Api_model->getSingleRow('user',array('name'=>$value['M']));
                
                if(count($user)>0)
                {
                  
                  $user_id = $user->id;
                }
                else
                {
                   $user_id = 1;
                }
                // site id 
                $site_id = '';
                $site_master = $this->Api_model->getSingleRow('site_master',array('site_name'=>$value['C'],'project_id'=>$project_id));
                
                if(count($site_master)>0)
                {
                    $site_id = $site_master->id;
                }
                else
                {
                	$site_masterD['site_name']           =   $value['C'];
	                $site_masterD['project_id']          =   $project_id;
	                $site_masterD['site_desc']           =   $value['C'];
	                $site_masterD['created_at']          =   date('Y-m-d');
	                $site_masterD['updated_at']          =   date('Y-m-d');
	                $site_id = $this->Common_model->insertGetId('site_master',$site_masterD);
                 
                }

                // product id 
                $product_id = '';
                $product_master = $this->Api_model->getSingleRow('product_master',array('measurement_name'=>$value['O'],'product_name'=>$value['D']));
                if(count($product_master)>0)
                {
                   $product_id = $product_master->id;
                }
                else
                {
	            	  $data2['product_name']        =   $value['D'];
	                $data2['measurement_name']    =   $value['O'];
	                $data2['product_price']       =   $value['G'];
	                $data2['product_desc']        =   $value['D'];
	                $data2['created_at']          =   date('Y-m-d');
	                $data2['updated_at']          =   date('Y-m-d');
	                $data2['minimum_stock']       =   '10';
	                $product_id= $this->Common_model->insertGetId('product_master',$data2);
	              
	                $data1['product_name']       =   $value['D'];
	                $data1['messurment']         =   $value['O'];
	                $data1['product_id']         =   $product_id;
	                $data1['quantity']           =  $value['H'];
	                $this->Common_model->insertGetId('stock_master',$data1);
               
                }


                  if($flag){
                    $flag =false;
                    continue;
                  }
                  $inserdata[$i]['entry_date'] = date('Y-m-d',strtotime($value['A']));
                  $inserdata[$i]['project_id'] = $project_id;
                  $inserdata[$i]['site_id']    =  $site_id;
                  $inserdata[$i]['supplier_name'] =  $value['E'];
                  $inserdata[$i]['chalan_no'] =  $value['F'];
                  $inserdata[$i]['fare_amount'] =  $value['I'];
                  $inserdata[$i]['tax_amount'] =  $value['K'];
                  $inserdata[$i]['total_amount'] =  (float)$value['L'];
                   $inserdata[$i]['updated_by'] = $user_id;
                  $inserdata[$i]['remark'] = $value['N'];

                  $inserdata[$i]['product_id'] =  $product_id; 
                  $inserdata[$i]['amount_per_unit'] =  $value['G'];
                  $inserdata[$i]['item_quantity'] =  $value['H'];
                  $inserdata[$i]['gst'] =  $value['J']; 
                 }
                  $i++;
                }

                         
               $result = $this->import->importdata($inserdata);   
                if($result){
                 
                  $this->data['message'] = 'Imported successfully.'; 
                }else{
                 
                   $this->data['message'] = 'ERROR !'; 
                }             

          } catch (Exception $e) {
               die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME)
                        . '": ' .$e->getMessage());
            }
          }else{
             $this->data['message'] =  $error['error'];
            }
        
                $this->data['action']  = 'entry';
                echo json_encode($this->data);;
  }



  public function uploadData2(){

  //if ($this->input->post('submit')) {
            
            $path = 'uploads/';
            require_once APPPATH . "/third_party/PHPExcel-1.8/Classes/PHPExcel.php";
            
            $config['upload_path'] = $path;
            $config['allowed_types'] = 'xlsx|xls';
            $config['remove_spaces'] = TRUE;
            $this->load->library('upload', $config);
            $this->upload->initialize($config);            
            if (!$this->upload->do_upload('uploadFile')) {
                $error = array('error' => $this->upload->display_errors());
            } else {
                $data = array('upload_data' => $this->upload->data());
            }
            if(empty($error)){
              if (!empty($data['upload_data']['file_name'])) {
                $import_xls_file = $data['upload_data']['file_name'];
            } else {
                $import_xls_file = 0;
            }
            $inputFileName = $path . $import_xls_file;
            
            try {
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
                $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
                $flag = true;
                $i=0;
                foreach ($allDataInSheet as $value) {
                if($i>=1){  
                // project id   
                 $project_id = '';  
                $project_master = $this->Api_model->getSingleRow('project_master',array('project_name'=>$value['B']));
                
                if(count($project_master)<=0)
                {
                    $project_masterD['project_name']        =   $value['B'];
                  $project_masterD['project_desc']        =   $value['B'];
                  $project_masterD['created_at']          =   date('Y-m-d');
                  $project_masterD['updated_at']          =   date('Y-m-d');
                  $project_id = $this->Common_model->insertGetId('project_master',$project_masterD);
                }
                else
                {
                  $project_id = $project_master->id;
                }

                 $user = $this->Api_model->getSingleRow('user',array('name'=>$value['M']));
                
                if(count($user)>0)
                {
                  
                  $user_id = $user->id;
                }
                else
                {
                   $user_id = 1;
                }
                // site id 
                $site_id = '';
                $site_master = $this->Api_model->getSingleRow('site_master',array('site_name'=>$value['C'],'project_id'=>$project_id));
                
                if(count($site_master)>0)
                {
                    $site_id = $site_master->id;
                }
                else
                {
                  $site_masterD['site_name']           =   $value['C'];
                  $site_masterD['project_id']          =   $project_id;
                  $site_masterD['site_desc']           =   $value['C'];
                  $site_masterD['created_at']          =   date('Y-m-d');
                  $site_masterD['updated_at']          =   date('Y-m-d');
                  $site_id = $this->Common_model->insertGetId('site_master',$site_masterD);
                 
                }

                // product id 
                $product_id = '';
                $product_master = $this->Api_model->getSingleRow('product_master',array('measurement_name'=>$value['O'],'product_name'=>$value['D']));
                if(count($product_master)>0)
                {
                   $product_id = $product_master->id;
                }
                else
                {
                  $data2['product_name']        =   $value['D'];
                  $data2['measurement_name']    =   $value['O'];
                  $data2['product_price']       =   $value['G'];
                  $data2['product_desc']        =   $value['D'];
                  $data2['created_at']          =   date('Y-m-d');
                  $data2['updated_at']          =   date('Y-m-d');
                  $data2['minimum_stock']       =   '10';
                  $product_id= $this->Common_model->insertGetId('product_master',$data2);
                   
                  // $data1['product_name']       =   $value['D'];
                  // $data1['messurment']         =   $value['O'];
                  // $data1['product_id']         =   $product_id;
                  // $data1['quantity']           =  $value['H'];
                  // $this->Common_model->insertGetId('stock_master',$data1);
                
                }


                  if($flag){
                    $flag =false;
                    continue;
                  }
                  $inserdata[$i]['entry_date'] = date('Y-m-d',strtotime($value['A']));
                  $inserdata[$i]['project_id'] = $project_id;
                  $inserdata[$i]['site_id']    =  $site_id;
                  $inserdata[$i]['product_id'] =  $product_id;
                  $inserdata[$i]['supplier_name'] =  $value['E'];
                  $inserdata[$i]['chalan_no'] =  $value['F'];
                  $inserdata[$i]['amount_per_unit'] =  $value['G'];
                  $inserdata[$i]['item_quantity'] =  $value['H'];
                  $inserdata[$i]['fare_amount'] =  $value['I'];
                  $inserdata[$i]['gst'] =  $value['J'];
                  $inserdata[$i]['tax_amount'] =  $value['K'];
                  $inserdata[$i]['total_amount'] =  (float)$value['L'];
                  $inserdata[$i]['updated_by'] = $user_id;
                  $inserdata[$i]['remark'] = $value['N'];
                 }
                  $i++;
                }

                         
               $result = $this->import->importdata2($inserdata);   
                if($result){
                 
                  $this->data['message'] = 'Imported successfully.'; 
                }else{
                 
                   $this->data['message'] = 'ERROR !'; 
                }             

          } catch (Exception $e) {
               die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME)
                        . '": ' .$e->getMessage());
            }
          }else{
             $this->data['message'] =  $error['error'];
            }
        
                $this->data['action']  = 'distribute';
                echo json_encode($this->data);;
  }


   public function uploadData3(){

  //if ($this->input->post('submit')) {
            
            $path = 'uploads/';
            require_once APPPATH . "/third_party/PHPExcel-1.8/Classes/PHPExcel.php";
            
            $config['upload_path'] = $path;
            $config['allowed_types'] = 'xlsx|xls';
            $config['remove_spaces'] = TRUE;
            $this->load->library('upload', $config);
            $this->upload->initialize($config);            
            if (!$this->upload->do_upload('uploadFile')) {
                $error = array('error' => $this->upload->display_errors());
            } else {
                $data = array('upload_data' => $this->upload->data());
            }
            if(empty($error)){
              if (!empty($data['upload_data']['file_name'])) {
                $import_xls_file = $data['upload_data']['file_name'];
            } else {
                $import_xls_file = 0;
            }
            $inputFileName = $path . $import_xls_file;
            
            try {
                $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($inputFileName);
                $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
                $flag = true;
                $i=0;
                foreach ($allDataInSheet as $value) {
                if($i>=1){  
                // project id   
               


                  if($flag){
                    $flag =false;
                    continue;
                  }

                    $project_id = '';  
                $project_master = $this->Api_model->getSingleRow('project_master',array('project_name'=>$value['B']));
                
                if(count($project_master)<=0)
                {
                    $project_masterD['project_name']        =   $value['A'];
                  $project_masterD['project_desc']        =   $value['A'];
                  $project_masterD['created_at']          =   date('Y-m-d');
                  $project_masterD['updated_at']          =   date('Y-m-d');
                  $project_id = $this->Common_model->insertGetId('project_master',$project_masterD);
                }
                else
                {
                  $project_id = $project_master->id;
                }

                 $user = $this->Api_model->getSingleRow('user',array('name'=>$value['C']));
                
                if(count($user)>0)
                {
                  
                  $user_id = $user->id;
                }
                else
                {
                   $user_id = 1;
                }
                // site id 
                $site_id = '';
                $site_master = $this->Api_model->getSingleRow('site_master',array('site_name'=>$value['C'],'project_id'=>$project_id));
                
                if(count($site_master)>0)
                {
                    $site_id = $site_master->id;
                }
                else
                {
                  $site_masterD['site_name']           =   $value['B'];
                  $site_masterD['project_id']          =   $project_id;
                  $site_masterD['site_desc']           =   $value['B'];
                  $site_masterD['created_at']          =   date('Y-m-d');
                  $site_masterD['updated_at']          =   date('Y-m-d');
                  $site_id = $this->Common_model->insertGetId('site_master',$site_masterD);
                 
                }


                  $inserdata[$i]['project_id'] =  $project_id;
                  $inserdata[$i]['site_id'] =  $site_id;
                  $inserdata[$i]['updated_by'] = $user_id;
                  $inserdata[$i]['created_at'] = date('Y-m-d');
                  $inserdata[$i]['c_name'] = $value['D'];
                  $inserdata[$i]['mobile_no']    =  $value['E'];
                  
                  $inserdata[$i]['adress_city'] =  $value['F'];
                  $inserdata[$i]['address_state'] =  $value['G'];
                  $inserdata[$i]['advanced_amount'] =  (float)$value['H'];
                   $inserdata[$i]['total_amount'] =  (float)$value['I'];
                    $inserdata[$i]['const_size_in_feet'] =  (float)$value['J'];
                  $inserdata[$i]['const_price_per_feet'] =  (float)$value['K'];
                  $inserdata[$i]['const_total_amount'] =  (float)$value['L'];
                  $inserdata[$i]['const_advanced_amount'] =  (float)$value['M'];
                  $inserdata[$i]['const_remain_amount'] =  (float)$value['N'];
                  $inserdata[$i]['paymnet_method'] =      $value['O'];
                  $inserdata[$i]['remark'] =              $value['P'];
                 }
                  $i++;
                }

                         
               $result = $this->import->importdata3($inserdata);   
                if($result){
                 
                  $this->data['message'] = 'Imported successfully.'; 
                }else{
                 
                   $this->data['message'] = 'ERROR !'; 
                }             

          } catch (Exception $e) {
               die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME)
                        . '": ' .$e->getMessage());
            }
          }else{
             $this->data['message'] =  $error['error'];
            }
        
                $this->data['action']  = 'sales';
                echo json_encode($this->data);
  }
}
?>