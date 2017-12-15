///model//
public function create_visitor($data) {
	$data2 = array (
			'email' => $data['email'],
				'password' => $this->hash_password($data['password'] ),
				'authority' => 'No',
				'role' => 'visitor',
         'otp' => $this->genOtp(),
         'branch' => '',
				'ex_date' => '',
				'rol_link' => '',
		);

		$this->db->trans_begin();
  	$this->db->insert ('login', $data2 );
        $insert_id = $this->db->insert_id();

		$data3 = array(
		"loginID" => $insert_id,
		"first_name" => $data['first_name'],
		"last_name" => $data['last_name'],
	  "email" => $data['email'],
		"mobile" => $data['mobile'],

		
		'active' => '1',
                        
		
		);
// var_dump($data3);die;
	  $this->db->insert ('visitor', $data3 );
    
    
   // controller
   public

	function visitor_register()
		{

		$this->form_validation->set_rules("first_name", "First Name", "trim|required|xss_clean|min_length[4]");
		$this->form_validation->set_rules("last_name", "Last Name", "trim|required|xss_clean|min_length[4]|differs[first_name]");
		$this->form_validation->set_rules("email", "Email-ID", "trim|required|valid_email|is_unique[login.email]");
		$this->form_validation->set_rules("mobile", "mobile", "trim|numeric|required|min_length[10]|max_length[10]");
		$this->form_validation->set_rules("password", "password", "trim|required|alpha_numeric|min_length[8]");
		$this->form_validation->set_rules("cpassword", "Re-type Password", "trim|matches[password]");
		if ($this->form_validation->run() == FALSE)
			{
			$this->output->set_status_header('400'); //Triggers the jQuery error callback
			$this->data['message'] = validation_errors();
			echo json_encode($this->data);
			}
		  else
			{
			if ($this->input->post() != null)
				{

			
                 if($this->input->post("currentjob") != '1')
                 {
                     $this->data['message'] = 'Please Accept Terms and condition.';
					   // $this->data['action'] = 'web2/Auth/user_process';
			      		echo json_encode($this->data);
                     
                 }
                 else
                 {
                  
				$done = $this->User_model->create_visitor($this->input->post());
				
				            if ($done)
					{
					    $this->session->set_userdata('role_id', $done);
					 
					$this->data['message'] = 'Moving to next Process';
					// $this->data['action'] = "web2/Main/registerProcess/$done";
					
					$this->data['action'] = "web2/Main/questioner?type=basic&givenBy=0";
                                       echo json_encode($this->data);
					//redirect(base_url('user_process'));
					}
					else
					{
					  $this->data['message'] = 'Unexpected Error Occured In your registration.';
					    $this->data['action'] = 'web2/Auth/user_process';
					echo json_encode($this->data);
					}
	   
 }			
                    
				}
			  else
				{
				$this->data['title'] = "Visitor || Natayu";
				$this->load->library('template2');
				$this->template->load('default2', 'website/Visitor', $this->data);
				}
			}
		}

//controller



$this->data['title'] = 'Portfolio';
$this->load->view('static/header');
$this->form_validation->set_rules('productName','Product Name','trim|required');
$this->form_validation->set_rules('productPrice','Product Price','trim|required');
$this->form_validation->set_rules('productDiscount','Product Discount','trim|required');
$this->form_validation->set_rules('description','Product Description','trim|required');
//$this->form_validation->set_rules('userfile','Product Image','trim|required');
if($this->form_validation->run()==FALSE){
$this->load->view('create');
}
else
{

$upload_img = $this->cwUpload('image','./assets/images/gallery/','',TRUE,'./assets/images/gallery/thumb/','120','120');


		//full path of the thumbnail image
	        		// $thumb_src = './assets/images/gallery/thumb/'.$upload_img;

		//set success and error messages
	        		$message = $upload_img?"<span style='color:#008000;'>Image thumbnail have been created successfully.</span>":"<span style='color:#F00000;'>Some error occurred, please try again.</span>";

	        		
	        			// "contentID" => $this->visitorID,
	        			
	        			
				$data = array('productID'=>'',
					"imageName" => $upload_img,
					'productName'=>$this->input->post("productName"),
                    'productPrice'=>$this->input->post("productPrice"),
                    'productDiscount'=>$this->input->post("productDiscount"),
                    'productDescription' =>$this->input->post("description"),
                    
				);
				$done = $this->My_model->insert('aproduct',$data);
          if($done){
          	redirect(base_url());
          }

			}

$this->load->view('static/footer');
}

///view

	
    <section id="">
	    <br><br>
        <div class="container">
            <div class="center">        
                <h2>Add Product </h2>
                <p class="lead">Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
            </div> 
            <div class="row contact-wrap"> 
                <div class="status alert alert-success" style="display: none"></div>
                <form id="" class="" name="" method="post" enctype="multipart/form-data" action="<?php echo base_url(); ?>Auth/addProduct">
                    <div class="col-sm-4 ">
                        <div class="form-group">
                            <label>Product Name *</label>
                            <input type="text" name="productName" id="productName" class="form-control" >
                          </div>
                          <span class="err"><?php echo form_error('productName') ?></span>
                    </div>
                        <div class="col-sm-4 ">
                        <div class="form-group">
                            <label>Product Price </label>
                            <input type="text" id="productPrice" name="productPrice" class="form-control" >
                        </div>
                        <span class="err"><?php echo form_error('productPrice') ?></span>
                    </div>
                        <div class="col-sm-4 ">
                        <div class="form-group">
                            <label>Product Discount </label>
                            <input type="text" id="productDiscount" name="productDiscount" class="form-control" >
                        </div>
                        <span class="err"><?php echo form_error('productDiscount') ?></span>
                    </div>
                    <div class="col-sm-4 ">
                        <div class="form-group">
                            <label>File *</label>
                            <input type="file" name="image" id="image" class="form-control" >
                        </div>
                          <!-- <span class="err"><?php echo form_error('userfile') ?></span> -->
                    </div>
                    <div class="col-sm-4 ">
                        <div class="form-group">
                            <label>Product Description *</label>
                             <input type="text" name="description" id="description" class="form-control" >
                          
                        </div> 
                        <span class="err"><?php echo form_error('description') ?></span>
                        </div>

                        <div class="form-group pull-right">
                            
                             <button type="submit" name="submit" class="btn btn-primary btn-lg" id="error">Add</button> 
                        </div>
                    </div>
                </form> 
            </div><!--/.row-->
        </div><!--/.container-->
    </section><!--/#contact-page-->


