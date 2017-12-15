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


