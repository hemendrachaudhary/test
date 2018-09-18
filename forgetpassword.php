function forgotPassword(){
        $this->form_validation->set_rules('email','Email','required');
        if($this->form_validation->run()==FALSE){
       $this->output->set_status_header('400'); //Triggers the jQuery error callback
       $this->data['message'] = validation_errors();
       echo json_encode($this->data); 
      }
      else{
          $this->data['userInfo'] = $this->My_model->getRowResultArray('user_info',array('email'=>$this->input->post('email')));
          if(count($this->data['userInfo'])>0){
          $str = str_shuffle("wt@4545gddfg54");
          $formData= array(
          'password'=> md5($str)
        );

      $id = $this->My_model->updateAllResultWhere('user_info',array('email'=>$this->input->post('email')),$formData);

      $config['protocol']     = 'smtp';
                $config['smtp_host'] = 'mail.itsolution.co.in';
                $config['smtp_port'] = 587;
                $config['smtp_user'] = 'hemendra@itsolution.co.in';
                $config['smtp_pass'] = 'hardwork@123';
                $config['charset'] = 'utf-8';
                $config['mailtype'] = "html";
                $config['newline'] = "\r\n";
    
                $this->load->library('email');
                $this->email->initialize($config);  
                $this->email->from('hemendra@itsolution.co.in', 'Travel Companions');
        
        // $data = array(
        //    'userName'=> $this->input->post('name'),
        //    'active_code'=> $str
        //      );
      $this->email->to($this->input->post('email')); // replace it with receiver mail id
      $this->email->subject('Reset Password'); // replace it with relevant subject
      // $body = $this->load->view('emails/accountactivation.php',$data,TRUE);
      $this->email->message('<body style="font-family: "Lato", sans-serif;font-weight: normal;word-break: break-word;margin:0px;padding:0px;">
    <div class="main_background" style="float: left;width: 100%;">
        <div class="temp_container" style="width:86%;margin-left: auto;margin-right: auto;">
            <div class="logo_area" style="float: left;width: 100%;text-align: center;padding: 2% 14% 2%;box-sizing: border-box;background-color: #eaeaea;">
                <img src="http://itsolution.co.in/travel-companions//assets/img/logo.jpg" alt="logo" style="width: 190px;">
            </div>
            <div class="temp_container_in" style="float: left;width: 100%;box-sizing: border-box;margin: 0px 0px 20px;">
                <div class="temp_text" style="float: left;width: 100%;padding:0% 14% 2.5%;box-sizing: border-box;background-color: #eaeaea;border-radius: 2px;">
                    <div class="temp_text_in" style="background-color: white;padding: 2% 4% 1%;box-sizing: border-box;float: left;width: 100%;border-radius: 3px;">
                        
                        
                        <h1 style="color: #505050;font-size: 24px;margin: 0;padding: 0;font-weight: normal;">Hello  '.ucfirst($this->data['userInfo']->userName).'</h1>
                        <br>
                        <p style="color: #505050;font-size: 16px;margin: 0;padding: 0;">Forget your password? Reset your password now...</p>
                        <br>
                        <p style="color: #505050;font-size: 16px;margin: 0;padding: 0;">Your login password is <b> '.$str.' </b> please login this password.</p>
                        <div class="btn_area" style="padding: 30px 25% 20px;">
                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="temp_footer" style="float: left;width: 100%;padding: 0px 0px 10px;box-sizing: border-box;text-align: center;border-bottom: 25px solid #EDEDED;">
                <ul style="margin:0px 0px 10px;padding: 0;list-style: none;text-align: center;">
                    <li style="display: inline-block;margin: 0px 5px;"><a href="#"><img src="<?= base_url(); ?>assets/img/facebook.png"></a></li>
                    <li style="display: inline-block;margin: 0px 5px;"><a href="#"><img src="<?= base_url(); ?>assets/img/twitter.png"></a></li>
                    <li style="display: inline-block;margin: 0px 5px;"><a href="#"><img src="<?= base_url(); ?>assets/img/linkdin.png"></a></li>
                    <li style="display: inline-block;margin: 0px 5px;"><a href="#"><img src="<?= base_url(); ?>assets/img/pintrest.png"></a></li>
                    <li style="display: inline-block;margin: 0px 5px;"><a href="#"><img src="<?= base_url(); ?>assets/img/googleplus.png"></a></li>
                </ul>
                <h6 style="margin: 0px; padding: 10px 0px 15px;font-weight: 300;font-size: 11px;color: #848484;">© 2018 TravelCompanion. All Rights Reserved.</h6>
            </div>
        </div>
    </div>
</body>'); 
      if ($this->email->send()) {
        echo "All OK";
        $this->session->set_flashdata('msg','You have received your password via email.You have also check indox and spam.');
        redirect('loginUser');   
      }else{ 
        echo $this->email->print_debugger();
      }
 

}
 else{
    $this->session->set_flashdata('mailErr', 'Email Id Not Exits.');
     $this->load->view('forgot',$this->data);
   }
  
}
}


**********************************************
uplad photo
**********************************************
 function uploadPhoto(){
  	$this->checkLogin();
    $des="";
        if($_FILES['img']['name']!=""){
            $tmpName = $_FILES['img']['tmp_name'];
            $des =  "uploads/".$_FILES['img']['name'];
        if(move_uploaded_file($tmpName,$des)){
            $formData = array(
              'p_name'=> $des,
              'user_id' => $this->session->userdata('userId'),
              'date' => date('Y-m-d H:i'),
             );
           $id = $this->My_model->insertAll('user_photo',$formData);
           if($id){
            redirect('managephotoUser');
           }
          }
        }else{
           redirect('managephotoUser');
        }
  }
  

<form name="upload" id="upload" method="post" action="<?= base_url()?>User_controller/uploadPhoto" enctype="multipart/form-data">
                                    <div class="picture">
                                        <?php if($user_info->image==''){?>
                                        <img src="<?= base_url();?>assets/images/user1.png" class="picture-src" id="wizardPicturePreview" title="">
                                    <?php }else{?>
                                     <img src="<?= base_url();?><?php echo $user_info->image?>" class="picture-src" id="wizardPicturePreview" title="">
                                 <?php } ?>
                                        <input type="file" name="img" id="wizard-picture" class="" onchange="this.form.submit()">
                                        <span><i class="fas fa-camera"></i></span>
                                    </div>
                                     </form>

  
  ***************************************************
  Change passwrd
  *******************************************
  
  function changePassword(){
  $this->checkLogin();
  $this->data['userInfo'] = $this->My_model->getRowResultArray('user_info',array('userID'=>$this->session->userdata('userId')));

  // if($this->data['userInfo']->password!=md5($this->input->post('oldPassword'))){
  // 	$this->form_validation->set_message('oldPassword','Old Password Not Match');
  // }
  $old_password_hash = md5($this->input->post('oldPassword'));
  $old_password_db_hash = $this->data['userInfo']->password;
  if($old_password_hash != $old_password_db_hash)
   {
   	$this->session->set_flashdata('oldPassword', 'Old Password Not Match.');
   } 
  $this->form_validation->set_rules('newPassword','New Password','required');
  $this->form_validation->set_rules('cPassword','Password','required|matches[newPassword]');
 if($this->form_validation->run()==FALSE){
 	$this->load->view('setting',$this->data);
 }else{
 	$formData= array(
    'password'=> md5($this->input->post('newPassword'))
 	);
 	 $id = $this->My_model->updateAllResultWhere('user_info',array('userID'=>$this->session->userdata('userId')),$formData);
     redirect('logoutUser');
 }
}
*******************************************************************
add Fav 
*******************************************************************
function addfav(){
	$this->checkLogin();
   if(isset($_POST['favuser_id'])){
   	$this->data['favoriteCount'] = $this->My_model->getRowResultArray('favorite_tbl',array('f_p_id'=>$this->input->post('favuser_id')));
   	if(count($this->data['favoriteCount'])==0){
    $formData= array(
    	'f_p_id'=>$this->input->post('favuser_id'),
    	'user_id'=> $this->session->userdata('userId'),
    	'date' => date('Y-m-d')
    );
 $id = $this->My_model->insertAll('favorite_tbl',$formData);
 if($id){
    $this->data['userPageInfo'] = $this->My_model->getRowResultArray('user_info',array('userId'=>$this->input->post('favuser_id')));
 	  $config['protocol']     = 'smtp';
    $config['smtp_host'] = 'mail.itsolution.co.in';
		$config['smtp_port'] = 587;
		$config['smtp_user'] = 'hemendra@itsolution.co.in';
		$config['smtp_pass'] = 'hardwork@123';
		$config['charset'] = 'utf-8';
		$config['mailtype'] = "html";
		$config['newline'] = "\r\n";
		
		$this->load->library('email');
		$this->email->initialize($config);	
		
		$this->email->from('hemendra@itsolution.co.in', 'Travel Companions');
		$this->email->to($this->data['userPageInfo']->email);
		//$this->email->cc('another@example.com');
		//$this->email->bcc('one-another@example.com');
		$this->email->subject('Add Favourite');
		$this->email->message('<body style="font-family: "Lato", sans-serif;font-weight: normal;word-break: break-word;margin:0px;padding:0px;">
    <div class="main_background" style="float: left;width: 100%;">
        <div class="temp_container" style="width:86%;margin-left: auto;margin-right: auto;">
            <div class="logo_area" style="float: left;width: 100%;text-align: center;padding: 2% 14% 2%;box-sizing: border-box;background-color: #eaeaea;">
                <img src="http://itsolution.co.in/travel-companions//assets/img/logo.jpg" alt="logo" style="width: 190px;">
            </div>
            <div class="temp_container_in" style="float: left;width: 100%;box-sizing: border-box;margin: 0px 0px 20px;">
                <div class="temp_text" style="float: left;width: 100%;padding:0% 14% 2.5%;box-sizing: border-box;background-color: #eaeaea;border-radius: 2px;">
                    <div class="temp_text_in" style="background-color: white;padding: 2% 4% 1%;box-sizing: border-box;float: left;width: 100%;border-radius: 3px;">
                        
                        
                        <h1 style="color: #505050;font-size: 24px;margin: 0;padding: 0;font-weight: normal;">Hello  '.ucfirst($this->data['userPageInfo']->userName).'</h1>
                        <br>
                        <p style="color: #505050;font-size: 16px;margin: 0;padding: 0;">I saw you recentaly become a customer of ours - thank you .</p>
                        <br>
                        <div class="btn_area" style="padding: 30px 25% 20px;">
                            <a href='.base_url()."User_controller/userPage/".$this->session->userdata('userId').' value="activate" style="display: block;border: none;box-shadow: none;color: #fff;padding: 9px 30px 9px;background-color: #05cbf5;margin: 0px auto 0px;outline: none;cursor: pointer;font-size: 15px;border-radius: 1px;font-weight: 600;text-transform: uppercase; text-align:center;text-decoration:none;letter-spacing:2px;">View Profile</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="temp_footer" style="float: left;width: 100%;padding: 0px 0px 10px;box-sizing: border-box;text-align: center;border-bottom: 25px solid #EDEDED;">
                <ul style="margin:0px 0px 10px;padding: 0;list-style: none;text-align: center;">
                    <li style="display: inline-block;margin: 0px 5px;"><a href="#"><img src="<?= base_url(); ?>assets/img/facebook.png"></a></li>
                    <li style="display: inline-block;margin: 0px 5px;"><a href="#"><img src="<?= base_url(); ?>assets/img/twitter.png"></a></li>
                    <li style="display: inline-block;margin: 0px 5px;"><a href="#"><img src="<?= base_url(); ?>assets/img/linkdin.png"></a></li>
                    <li style="display: inline-block;margin: 0px 5px;"><a href="#"><img src="<?= base_url(); ?>assets/img/pintrest.png"></a></li>
                    <li style="display: inline-block;margin: 0px 5px;"><a href="#"><img src="<?= base_url(); ?>assets/img/googleplus.png"></a></li>
                </ul>
                <h6 style="margin: 0px; padding: 10px 0px 15px;font-weight: 300;font-size: 11px;color: #848484;">© 2018 TravelCompanion. All Rights Reserved.</h6>
            </div>
        </div>
    </div>
</body>');
		//$this->email->attach($file);
		// The email->send() statement will return a true or false
		// If true, the email will be sent
		if ($this->email->send()) {
		  echo "Add To Favorites";

		} else { 
		  echo $this->email->print_debugger();
		}
         
 }
}
 else{
 	$this->My_model->deleteArray('favorite_tbl',array('f_p_id'=>$this->input->post('favuser_id')));

 }
   }

}
