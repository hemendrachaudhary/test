<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webservice extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this -> load -> library('session');
        $this -> load -> helper('form');
        $this -> load -> helper('url');
        $this -> load-> library('image_lib');
        $this -> load->library('api');
        $this -> load -> database();
        $this -> load -> library('form_validation');
        $this -> load -> model('Comman_model');
        $this -> load -> model('Api_model');
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    }
   /*commit*/
    public function index()
    {
        $getUserId=1;
        echo $url= base_url().'Webservice/userActive?getUserId='.$getUserId;
    }

    /*Check User Session*/
    public function checkUserKey($user_key, $user_id)
    {
        $getUser= $this->Api_model->getSingleRow('user_session', array('user_id'=>$user_id,'user_key'=>$user_key));
        if(!$getUser)
        {
            $this->api->api_message(3, IN_USER);
            exit();
        }
    }

     /*Check User Session*/
    public function checkUserStatus($user_id)
    {
        $getUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$user_id));

        if($getUser)
        {
          if($getUser->status==0)
          {
              $this->api->api_message(3, NOT_ACT);
              exit();
          }
        }
        else
        {
          $this->api->api_message(3, USER_NOT_FOUND);
          exit();
        }
    }
    

    /*Get Neareast Artist*/
    public function getNearestArtist()
    {
       $latitude = $this->input->post('latitude', TRUE);
       $longitude = $this->input->post('longitude', TRUE);
       $category_id = $this->input->post('category_id', TRUE);
       $user_id = $this->input->post('user_id', TRUE);

       $this->checkUserStatus($user_id);

       if($category_id)
       {
          $get_artists=$this->Api_model->getNearestDataWhere($latitude,$longitude,ART_TBL,array('category_id'=>$category_id),$user_id, 1);
       }
       else
       {
          $get_artists=$this->Api_model->getNearestData($latitude,$longitude,ART_TBL,$user_id);
       }      

       if($get_artists->price=='' || $get_artists->category_id==0 || $get_artists->name=='' || $get_artists->skills=='' || $get_artists->bio=='')
         {
           $this->api->api_message(0, NO_DATA);
           die();
         }
      
       if($get_artists)
          {

          $artist_id=$get_artists->user_id;
          $get_cat=$this->Api_model->getSingleRow(CAT_TBL, array('id'=>$get_artists->category_id));
          $get_artists->image=base_url().$get_artists->image;
          $get_artists->category_name=$get_cat->cat_name;

          $where= array('artist_id'=>$artist_id);
          $ava_rating=$this->Api_model->getAvgWhere('rating', 'rating',$where);
            if($ava_rating[0]->rating==null)
            {
              $ava_rating[0]->rating="0";
            }
            $get_artists->ava_rating=round($ava_rating[0]->rating,1);

            $skills=json_decode($get_artists->skills);
            $skill= array();
            if(!empty($skills))
            {
              foreach ($skills as $skills) {
              $get_skills=  $this->Api_model->getSingleRow('skills', array('id'=>$skills));
              array_push($skill, $get_skills);
              }
              $get_artists->skills= $skill;
            }
            else
            {
              $get_artists->skills= array();
            }

            $get_products=  $this->Api_model->getAllDataWhere(array('user_id'=>$get_artists->user_id), 'products');

            $products= array();
            foreach ($get_products as $get_products) {
              # code...
              $get_products->product_image=base_url().$get_products->product_image;
              array_push($products, $get_products);
            }
            $get_artists->products=$products;

            $get_reviews=  $this->Api_model->getAllDataWhere(array('artist_id'=>$artist_id,'status'=>1), 'rating');
            $review = array();
            foreach ($get_reviews as $get_reviews) {

              $get_user = $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$get_reviews->user_id));
              $get_reviews->name= $get_user->name;
              if($get_user->image)
              {
                $get_reviews->image= base_url().$get_user->image;
              }
              else
              {
                $get_reviews->image= base_url()."assets/images/image.png";
              }
              
              array_push($review, $get_reviews);
            }
            $get_artists->reviews=$review;

            $get_gallery=  $this->Api_model->getAllDataWhere(array('user_id'=>$artist_id), GLY_TBL);
            $gallery = array();
            foreach ($get_gallery as $get_gallery) {

              $get_gallery->image= base_url().$get_gallery->image;
              array_push($gallery, $get_gallery);
            }
            $get_artists->gallery=$gallery;

            $get_qualifications=  $this->Api_model->getAllDataWhere(array('user_id'=>$artist_id), 'qualifications');
            
            $get_artists->qualifications=$get_qualifications;

             $get_artists->jobDone=$this->Api_model->getTotalWhere(ABK_TBL,array('artist_id'=>$artist_id,'booking_flag'=>4));

            $get_artists->totalJob=$this->Api_model->getTotalWhere(ABK_TBL,array('artist_id'=>$artist_id));

            $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
            $get_artists->currency_type= $currency_setting->currency_symbol;

            if($get_artists->totalJob==0)
              {
                $get_artists->completePercentages=0;
              }
              else
              {
                $get_artists->completePercentages=round(($get_artists->jobDone*100) / $get_artists->totalJob);
              }

          $this->api->api_message_data(1, ALL_SKILLS,'data' , $get_artists);
      }
      else
      {
        $this->api->api_message(0, NO_DATA);
      }
    }

    

     /*SignUp User*/
    public function SignUp()
    {
        $name = $this->input->post('name', TRUE);
        $email_id = $this->input->post('email_id', TRUE); 
        $password = $this->input->post('password', TRUE); 
        $userRole = $this->input->post('role', TRUE);
        $device_id = $this->input->post('device_id', TRUE); 
        $device_token = $this->input->post('device_token', TRUE); 
        $device_type = $this->input->post('device_type', TRUE);
        $use_code = $this->input->post('use_code', TRUE);

        if($use_code)
        {
          $getCode=$this->Api_model->getSingleRow(USR_TBL, array('referral_code'=>$referral_code));
          if(!$getCode)
          {
            $this->api->api_message(0, "Please enter valid coupon code.");
          }
        }
       
        if($userRole==1)
        {
          $userStatus = '1';
          $approval_status = '1';
          $user_type = '1';
        }
        else
        {
          $userStatus = '1';
          $approval_status = '1';
          $user_type = '0';
        }
         
        $created_at=time();
        $updated_at=time();
        $table= USR_TBL;       
        $condition = array('email_id'=>$email_id);
        $columnName = 'email_id';
        $referral_code=$this->api->random_num(6);
        if($use_code)
        {
           $data = array('name'=>$name,'email_id'=>$email_id,'password'=>$password,'role'=>$userRole,'status'=>$userStatus,'created_at'=>$created_at,'updated_at'=>$updated_at,'referral_code'=>$referral_code,'approval_status'=>$approval_status,'user_referral_code'=>$use_code,'device_token'=>$device_token,'device_id'=>$device_id,'device_type'=>$device_type,'user_type'=>$user_type);
        }
       else
       {
         $data = array('name'=>$name,'email_id'=>$email_id,'password'=>$password,'role'=>$userRole,'status'=>$userStatus,'created_at'=>$created_at,'updated_at'=>$updated_at,'referral_code'=>$referral_code,'approval_status'=>$approval_status,'device_token'=>$device_token,'device_id'=>$device_id,'device_type'=>$device_type,'user_type'=>$user_type);
       }

        $get_user=$this->Api_model->getSingleRow($table, array('email_id'=>$email_id));

        if(!$get_user)
        {
            $getUserId=$this->Api_model->insertGetId($table,$data);
            if($getUserId){

               if($userRole==1)
              {
                 $msg='Thanks for registration as a artist on FabArtist! We are happy to give our best services. Please login.';
              }
              else
              {
                $url= base_url().'Webservice/userActive?user_id='.$getUserId;
                $msg='Thanks for signing up! Your account has been created, you can login with the following credentials after you have activated your account by pressing the url below. Please click ' .$url;
              }
              $this->send_email($email_id, REG_SUB, $msg);
       
            $datatag='data';
            $get_user=$this->Api_model->getSingleRow($table, array('user_id'=>$getUserId));
            $this->api->api_message_data(1, USERRAGISTER,$datatag , $get_user);
              }
              else{
                $this->api->api_message(0, TRY_AGAIN);
              }         
        }
        elseif($get_user->status ==0){

          $getUserId= $get_user->user_id;
          
          if($userRole==1)
              {
                 $msg='Thanks for signing up! Your account has been created, please wait for the admin confirmation.';
              }
              else
              {
                $url= base_url().'Webservice/userActive?user_id='.$getUserId;
                $msg='Thanks for signing up! Your account has been created, you can login with the following credentials after you have activated your account by pressing the url below. Please click ' .$url;
              }

              $this->send_email($email_id, REG_SUB, $msg);
              $this->api->api_message(0, CHECK_MAIL);
              exit();
        }
        else
        {
            $this->api->api_message(0, USERALREADY);
            exit();
        }
    }

  

    /*Use Sign in*/
    public function signIn()
    {
        $email_id = $this->input->post('email_id', TRUE); 
        $password = $this->input->post('password', TRUE); 
        $role = $this->input->post('role', TRUE); 
        $datadevice['device_id'] = $this->input->post('device_id', TRUE); 
        $datadevice['device_token'] = $this->input->post('device_token', TRUE); 
        $datadevice['device_type'] = $this->input->post('device_type', TRUE); 
        $table= USR_TBL;       
        $condition = array('email_id'=>$email_id,'role'=>$role);
        $chkUser = $this->Api_model->getSingleRow($table, $condition);

        if(!$chkUser)
        {
          $this->api->api_message(0, USER_NOT_FOUND);
          exit();
        }
        
        if($chkUser->password !=$password)
        {
          $this->api->api_message(0, PASS_NT_MTCH);
          exit();
        }

        if($chkUser->status !=1)
        {
          $this->api->api_message(0, NOT_ACTIVE);
          exit();
        }

        if($chkUser->approval_status !=1)
        {
          $this->api->api_message(0, 'Hey, Please wait for the admin approval.');
          exit();
        }

        $user_id = $chkUser->user_id;
        $name =$chkUser->name;
        $email_id = $chkUser->email_id;
        $role= $chkUser->role;
        if($chkUser)
        {    
          if($role==1)
          {
            $checkArtist = $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$user_id));

            if($checkArtist)
            {
               if($chkUser->image)
              {
                $chkUser->image= base_url().$chkUser->image;
              }  
              else
              {
                $chkUser->image= base_url()."assets/images/image.png";
              }  
            }
           else
            {
              $chkUser->image= base_url()."assets/images/image.png";
            }           
          }
          else
          {
            if($chkUser->image)
            {
              $chkUser->image= base_url().$chkUser->image;
            }  
            else
            {
              $chkUser->image= base_url()."assets/images/image.png";
            }  
          }

          $chkUser->device_id=$datadevice['device_id'];
          $chkUser->device_type=$datadevice['device_type'];
          $chkUser->device_token=$datadevice['device_token'];

          $where = array('email_id'=>$email_id);
          $updateUser= $this->Api_model->updateSingleRow(USR_TBL,$where,$datadevice);

          $datatag = 'data';
          
          $this->api->api_message_data(1, LOGINSUCCESSFULL,$datatag , $chkUser);
        }
        else
        {
         $this->api->api_message(0, LOGINFAIL);
        }
    }

    /*Get all Category*/
    public function getAllCaegory()
    {
      $user_id = $this->input->post('user_id', TRUE);
      
      $this->checkUserStatus($user_id); 
      $get_cat=$this->Api_model->getAllDataWhere(array('status'=>1),CAT_TBL);

      if($get_cat)
      {
        $get_cats= array();
        foreach ($get_cat as $get_cat) {

          $commission_setting= $this->Api_model->getSingleRow('commission_setting',array('id'=>1));
          if($commission_setting->commission_type==0)
          {
            $get_cat->price= $get_cat->price; 
          }
          elseif($commission_setting->commission_type==1)
          {
            if($commission_setting->flat_type==2)
            {
              $get_cat->price= $commission_setting->flat_amount;
            }
            elseif ($commission_setting->flat_type==1) 
            {
              $get_cat->price= $commission_setting->flat_amount;
            }
          }

        $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
        $get_cat->currency_type= $currency_setting->currency_symbol;

          array_push($get_cats, $get_cat);
        }
        $this->api->api_message_data(1, ALL_CAT,'data' , $get_cats);
      }
      else
      {
         $this->api->api_message(0, NO_DATA);
      }
    }

    /*get Skills by Category*/
    public function getSkillsByCategory()
    {
      $cat_id = $this->input->post('cat_id', TRUE);
      $user_id = $this->input->post('user_id', TRUE);
      
      $this->checkUserStatus($user_id); 

      $where=array('cat_id'=>$cat_id, 'status'=>1);

      $get_skills=$this->Api_model->getAllDataWhere($where,'skills');

      if($get_skills)
      {
        $this->api->api_message_data(1, ALL_SKILLS,'data' , $get_skills);
      }
      else
      {
         $this->api->api_message(0, NO_DATA);
      }
      
    }

    /*Add gallery for artist*/
    public function addGallery()
    {
      $user_id= $this->input->post('user_id');
      $image= $this->input->post('image');

      $this->checkUserStatus($user_id);

      $this->load->library('upload');
  
       $config['image_library'] = 'gd2';
       $config['upload_path']   = './assets/images/gallery/'; 
       $config['allowed_types'] = 'gif|jpg|jpeg|png';
       $config['max_size']      = 10000; 
       $config['file_name']     = time();
       $config['create_thumb'] = TRUE;
       $config['maintain_ratio'] = TRUE;
       $config['width'] = 250;
       $config['height'] = 250;
       $this->upload->initialize($config);
       $galleryImage="";
       if ( $this->upload->do_upload('image') && $this->load->library('image_lib', $config))
       {         
          $galleryImage='assets/images/gallery/'.$this->upload->data('file_name');
       }
       else  
        {
          //  echo $this->upload->display_errors();
        }

        $data['user_id']=$user_id;
        $data['image']=$galleryImage;
        $data['created_at']=time();
        $data['updated_at']=time();
        $getId=$this->Api_model->insertGetId(GLY_TBL,$data);
        if($getId)
        {
           $this->api->api_message(1, ADD_GALLERY);
        }
        else
        {
           $this->api->api_message(0, NO_DATA);
        }
    }

    public function send_email($email_id, $subject, $msg)
    {
      $this->load->library('email'); 
      $this->email->set_mailtype("html");
      $this->email->set_newline("\r\n");

      $from_email = SENDER_EMAIL; 
      $this->email->from($from_email, "Fab Artist"); 
      $this->email->to($email_id);
      $this->email->subject($subject); 

      $datas['msg']=$msg;
      $body = $this->load->view('main.php',$datas,TRUE);
      $this->email->message($body);
      $this->email->send();
    }

    /*Get all Category*/
    public function getAllArtists()
    {
      $latitude_app=$this->input->post('latitude');
      $longitude_app=$this->input->post('longitude');
      $category_id=$this->input->post('category_id');
      $distance=$this->input->post('distance');
      $user_id=$this->input->post('user_id');
      $page=$this->input->post('page');

      $page= isset($page) ? $page: 1;

      $this->checkUserStatus($user_id);    

      if($category_id)
      {
        $where =array('category_id'=>$category_id,'update_profile'=>1,'booking_flag'=>0,'is_online'=>1);

        $artist=$this->Api_model->getAllDataWhereLimit($where,ART_TBL,$page);
      }
      else
      {
        $artist=$this->Api_model->getAllDataWhereLimit(array('update_profile'=>1,'booking_flag'=>0,'is_online'=>1),ART_TBL,$page);
      }
      
      function distance($lat1, $lon1, $lat2, $lon2) 
      {
       
        try
        {
          $theta = $lon1 - $lon2;
          $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
          $dist = acos($dist);
          $dist = rad2deg($dist);
          $miles = $dist * 60 * 1.1515;
          return ($miles * 1.609344);
        }

        catch(Exception $e)
        {
          return (0.0);
        }
      }

      if($artist)
      {
        $artists= array();
        foreach ($artist as $artist)
         {
            $jobDone=$this->Api_model->getTotalWhere(ABK_TBL,array('artist_id'=>$artist->user_id,'booking_flag'=>4));

            $artist->total=$this->Api_model->getTotalWhere(ABK_TBL,array('artist_id'=>$artist->user_id,));
            if($artist->total==0)
            {
              $artist->percentages=0;
            }
            else
            {
                $artist->percentages=round(($jobDone*100) / $artist->total);
            }
            $artist->jobDone=$jobDone;

          $get_cat=$this->Api_model->getSingleRow(CAT_TBL, array('id'=>$artist->category_id));
         $getUser=$this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$artist->user_id));
          if($getUser->image)
          {
            $artist->image=base_url().$getUser->image;
          }
          else
          {
            $artist->image=base_url()."assets/images/image.png";   
          }
           if($get_cat)
          {
            $artist->category_name=$get_cat->cat_name;//
          }
          else
          {
            $artist->category_name="No Category";//
          }


          $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));

          $artist->currency_type=$currency_setting->currency_symbol;
          $commission_setting= $this->Api_model->getSingleRow('commission_setting',array('id'=>1));
          $artist->commission_type=$commission_setting->commission_type;
          $artist->flat_type=$commission_setting->flat_type;
          if($commission_setting->commission_type==0)
          {
            $artist->category_price=$get_cat->price;
          }
          elseif($commission_setting->commission_type==1)
          {
            if($commission_setting->flat_type==2)
            {
              $artist->category_price = $commission_setting->flat_amount;
            }
            elseif ($commission_setting->flat_type==1) 
            {
              $artist->category_price = $commission_setting->flat_amount;
            }
          }
          
          $distance =distance($latitude_app,$longitude_app,$artist->latitude,$artist->longitude);
          $distance=round($distance);
          $distance_str="$distance";
          $artist->distance=$distance_str;
          $where= array('artist_id'=>$artist->user_id);
          $ava_rating=$this->Api_model->getAvgWhere('rating', 'rating',$where);
          if($ava_rating[0]->rating==null)
          {
            $ava_rating[0]->rating="0";
          }
          $artist->ava_rating= round($ava_rating[0]->rating, 2);


                $check_fav= $this->Api_model->check_favorites($user_id,$artist->user_id);
                $artist->fav_status= $check_fav ? "1":"0";

          array_push($artists, $artist);
        }

       usort($artists, function($a,$b) {
          if($a->distance == $b->distance) return 0;
          return ($a->distance < $b->distance) ? -1 : 1;
      });


        $this->api->api_message_data(1, ALL_ARTISTS,'data' , $artists);
      }
      else
      {
         $this->api->api_message(0, NO_DATA);
      }
    }

    /*get profile by user_id*/
    public function getArtistByid()
    {
      $artist_id = $this->input->post('artist_id', TRUE);
      $user_id = $this->input->post('user_id', TRUE);

      $this->checkUserStatus($user_id);

      $where=array('user_id'=>$artist_id);
      $get_artists=$this->Api_model->getSingleRow(ART_TBL,$where);
      if($get_artists)
      {
        $get_cat=$this->Api_model->getSingleRow(CAT_TBL, array('id'=>$get_artists->category_id));
        $getUser=$this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$get_artists->user_id));
        if ($getUser->image) 
        {
          $get_artists->image=base_url().$getUser->image;
        }
        else
        {
          $get_artists->image=base_url()."assets/images/image.png";
        }
        $get_artists->category_name=$get_cat->cat_name;

        $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
        $get_artists->currency_type=$currency_setting->currency_symbol;

        $commission_setting= $this->Api_model->getSingleRow('commission_setting',array('id'=>1));
        $get_artists->commission_type=$commission_setting->commission_type;
        $get_artists->flat_type=$commission_setting->flat_type;
        if($commission_setting->commission_type==0)
        {
          $get_artists->category_price=$get_cat->price;
        }
        elseif($commission_setting->commission_type==1)
        {
          if($commission_setting->flat_type==2)
          {
            $get_artists->category_price = $commission_setting->flat_amount;
          }
          elseif ($commission_setting->flat_type==1) 
          {
            $get_artists->category_price = $commission_setting->flat_amount;
          }
        }

        $where= array('artist_id'=>$artist_id, 'status'=>1);
        $ava_rating=$this->Api_model->getAvgWhere('rating', 'rating',$where);
          if($ava_rating[0]->rating==null)
          {
            $ava_rating[0]->rating="0";
          }
          $get_artists->ava_rating=round($ava_rating[0]->rating,1);

          $skills=json_decode($get_artists->skills);
          $skill= array();
          if(!empty($skills))
          {
            foreach ($skills as $skills) 
            {
                $get_skills=  $this->Api_model->getSingleRow('skills', array('id'=>$skills));
                array_push($skill, $get_skills);
            }
          }
         
          $get_artists->skills= $skill;

          $get_products=  $this->Api_model->getAllDataWhere(array('user_id'=>$get_artists->user_id), 'products');

          $products= array();
          foreach ($get_products as $get_products) {
            $get_products->product_image=base_url().$get_products->product_image;
            $get_products->currency_type=$currency_setting->currency_symbol;
            array_push($products, $get_products);
          }
          $get_artists->products=$products;

          $get_reviews=  $this->Api_model->getAllDataWhere(array('artist_id'=>$artist_id,'status'=>1), 'rating');
          $review = array();
          foreach ($get_reviews as $get_reviews) {

            $get_user = $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$get_reviews->user_id));
            $get_reviews->name= $get_user->name;
            if($get_user->image)
            {
              $get_reviews->image= base_url().$get_user->image;
            }
            else
            {
              $get_reviews->image= base_url()."assets/images/image.png";
            }
            array_push($review, $get_reviews);
          }
          $get_artists->reviews=$review;

          $get_gallery=  $this->Api_model->getAllDataWhere(array('user_id'=>$artist_id), GLY_TBL);
          
          $gallery = array();
          foreach ($get_gallery as $get_gallery) {

            $get_gallery->image= base_url().$get_gallery->image;
            array_push($gallery, $get_gallery);
          }
          $get_artists->gallery=$gallery;

          $get_qualifications=  $this->Api_model->getAllDataWhere(array('user_id'=>$artist_id), 'qualifications');
          
          $get_artists->qualifications=$get_qualifications;

          $artist_bookings= array();
          $artist_booking1= array();
          $artist_booking=  $this->Api_model->getAllDataLimitWhere(ABK_TBL,array('artist_id'=>$artist_id, 'booking_flag'=>4), 7);
          foreach ($artist_booking as $artist_booking) 
          {
            $rat=$this->Api_model->getSingleRow('rating', array('booking_id'=>$artist_booking->id, 'status'=>1));
            if($rat)
            {
                $get_user = $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$rat->user_id));
                $artist_booking1['username']= $get_user->name;
                if($get_user->image)
                {
                  $artist_booking1['userImage']= base_url().$get_user->image;
                }
                else
                {
                  $artist_booking1['userImage']= base_url()."assets/images/image.png";
                }               
                $artist_booking1['rating']=$rat->rating;
                $artist_booking1['comment']=$rat->comment;
                $artist_booking1['ratingDate']=$rat->created_at;
            }
            else
            { 
               $get_user = $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$artist_booking->user_id));
                $artist_booking1['username']= $get_user->name;
                if($get_user->image)
                {
                  $artist_booking1['userImage']= base_url().$get_user->image;
                }
                else
                {
                  $artist_booking1['userImage']= base_url()."assets/images/image.png";
                }
                $artist_booking1['rating']="0";
                $artist_booking1['comment']="";
                $artist_booking1['ratingDate']=$artist_booking->created_at;
            }

            $artist_booking1['price']=$artist_booking->price;
            $artist_booking1['currency_type']=$currency_setting->currency_symbol;
            $artist_booking1['booking_time']=$artist_booking->booking_time;
            $artist_booking1['booking_date']=$artist_booking->booking_date;
            array_push($artist_bookings, $artist_booking1);
          }
          $get_artists->artist_booking= $artist_bookings;

          $earning=$this->Api_model->getSumWhere('total_amount', IVC_TBL,array('artist_id'=>$artist_id));

          $get_artists->earning= round($earning->total_amount, 2);

          $get_artists->jobDone=$this->Api_model->getTotalWhere(ABK_TBL,array('artist_id'=>$artist_id,'booking_flag'=>4));

          $get_artists->totalJob=$this->Api_model->getTotalWhere(ABK_TBL,array('artist_id'=>$artist_id));

          if($get_artists->totalJob==0)
            {
              $get_artists->completePercentages=0;
            }
            else
            {
              $get_artists->completePercentages=round(($get_artists->jobDone*100) / $get_artists->totalJob);
            }
        $check_fav= $this->Api_model->check_favorites($user_id,$artist_id);
        $get_artists->fav_status= $check_fav ? "1":"0";

        $this->api->api_message_data(1, "Get artist detail.",'data' , $get_artists);
      }
      else
      {
        $this->api->api_message(0, NO_DATA);
      }     
    }

    /*Update artist personal info*/
    public function generateTicket()
    {
      $data['user_id'] = $this->input->post('user_id', TRUE); 
      $data['reason'] = $this->input->post('reason', TRUE); 
      $data['craeted_at'] = time();

      $this->checkUserStatus($data['user_id']);

      $ticketId=$this->Api_model->insertGetId('ticket',$data);
      if($ticketId)
      {
        $this->api->api_message(1, "Ticket generated successfully.");
      }
      else
      {
        $this->api->api_message(0, NO_DATA);
      }
    }

  /*get Ticket*/
   public function getMyTicket()
   {
      $user_id= $this->input->post('user_id', TRUE);
      $this->checkUserStatus($user_id);

      $get_ticket= $this->Api_model->getAllDataWhere(array('user_id'=>$user_id), 'ticket');
      if($get_ticket)
      {
        $this->api->api_message_data(1, "Get my tickets.",'my_ticket', $get_ticket);
      }
      else
      {
        $this->api->api_message(0, "Not yet any tickets.");
      }
   }

   /*Add ticket Comments*/
   public function addTicketComments()
   {
      $user_id= $this->input->post('user_id', TRUE);
      $this->checkUserStatus($user_id);

      $data['ticket_id']= $this->input->post('ticket_id', TRUE);
      $data['comment']= $this->input->post('comment', TRUE);
      $data['user_id']= $user_id;
      $data['role']= 1;
      $data['created_at']= time();
      
      $ticketId=$this->Api_model->insertGetId('ticket_comments',$data);
      if($ticketId)
      {
        $this->api->api_message(1, "Thanks for the review.");
      }
      else
      {
        $this->api->api_message(0, NO_DATA);
      }
   }

   /*get Ticket Comments*/
   public function getTicketComments()
   {
      $user_id= $this->input->post('user_id', TRUE);
      $ticket_id= $this->input->post('ticket_id', TRUE);
      $this->checkUserStatus($user_id);

      $ticket_comments= $this->Api_model->getAllDataWhere(array('ticket_id'=>$ticket_id), 'ticket_comments');

      $ticket_comment= array();
        foreach ($ticket_comments as $ticket_comments) 
        {
          if($ticket_comments->user_id !=0)
          {
            $getUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$ticket_comments->user_id));
            $ticket_comments->userName=$getUser->name;
          }
          else
          {
            $ticket_comments->userName="Admin";
          }
          array_push($ticket_comment, $ticket_comments);
        }

      if($ticket_comments)
      {
        $this->api->api_message_data(1, "Get ticket comments.",'ticket_comment', $ticket_comment);
      }
      else
      {
        $this->api->api_message(0, "Not yet any tickets.");
      }
   }

    /*get conversation*/
   public function getNotifications()
   {
      $user_id= $this->input->post('user_id', TRUE);
      $this->checkUserStatus($user_id);

      $get_notifications= $this->Api_model->getAllDataWhereAndOr(array('user_id'=>$user_id), array('type'=>"All"), NTS_TBL);
      if($get_notifications)
      {
        $this->api->api_message_data(1, "Get my notifications.",'my_notifications', $get_notifications);
      }
      else
      {
        $this->api->api_message(0, "Not yet any notifications.");
      }
   }

   /*Update artist personal info*/
    public function deleteProfileImage()
    {
      $user_id = $this->input->post('user_id', TRUE); 

      $this->checkUserStatus($user_id);

      $updateUser= $this->Api_model->updateSingleRow(ART_TBL,array('user_id'=>$user_id),array('image'=>''));

      $this->api->api_message(1, "Profile image deleted successfully.");
     } 

    public function artistImage()
    {
      $user_id = $this->input->post('user_id', TRUE);
      $image = $this->input->post('image', TRUE); 

      $this->load->library('upload');
  
       $config['image_library'] = 'gd2';
       $config['upload_path']   = './assets/images/'; 
       $config['allowed_types'] = 'gif|jpg|jpeg|png';
       $config['max_size']      = 10000; 
       $config['file_name']     = time();
       $config['create_thumb'] = TRUE;
       $config['maintain_ratio'] = TRUE;
       $config['width'] = 250;
       $config['height'] = 250;
       $this->upload->initialize($config);
       $updateduserimage="";
       if ( $this->upload->do_upload('image') && $this->load->library('image_lib', $config))
       {         
          $updateduserimage='assets/images/'.$this->upload->data('file_name');
       }
       else  
        {
          //  echo $this->upload->display_errors();
        }

        $check_user = $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$user_id));
        if($check_user)
        {
        $data['image']=$updateduserimage;

        $where= array('user_id'=>$user_id);
         $updateUser= $this->Api_model->updateSingleRow(ART_TBL,$where,$data);
        if($updateUser)
        {
           $checkUser=$this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$user_id));

           $checkartist=$this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$user_id));

           if($checkartist->image)
           {
              $checkUser->image= base_url().$checkartist->image;
           }
           else
           {
            $checkUser->image= base_url().'assets/images/image.png';
           }
          $this->api->api_message_data(1, ARTIST_UPDATE,'data' , $checkUser);
        }
        else
        {
          $this->api->api_message(0, TRY_AGAIN);
        }
      }
      else{

        $this->load->library('upload');
  
         $config['image_library'] = 'gd2';
         $config['upload_path']   = './assets/images/'; 
         $config['allowed_types'] = 'gif|jpg|jpeg|png';
         $config['max_size']      = 10000; 
         $config['file_name']     = time();
         $config['create_thumb'] = TRUE;
         $config['maintain_ratio'] = TRUE;
         $config['width'] = 250;
         $config['height'] = 250;
         $this->upload->initialize($config);
         $updateduserimage="";
         if ( $this->upload->do_upload('image') && $this->load->library('image_lib', $config))
         {         
            $updateduserimage='assets/images/'.$this->upload->data('file_name');
         }
         else  
          {
            //  echo $this->upload->display_errors();
          }

          $data['user_id']=$user_id;
          if($updateduserimage)
          {
            $data['image']=$updateduserimage;
          }

        $getUserId=$this->Api_model->insertGetId(ART_TBL,$data);

        if($getUserId)
        {

           $checkUser=$this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$user_id));

           $checkartist=$this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$user_id));

           if($checkartist->image)
           {
              $checkUser->image= base_url().$checkartist->image;
           }
           else
           {
            $checkUser->image= base_url().'assets/images/image.png';
           }

         $this->api->api_message_data(1, ARTIST_UPDATE,'data' , $checkUser);
        }
        else
        {
          $this->api->api_message(0, TRY_AGAIN);
        }
      }
    }
    /*Update artist personal info*/
    public function artistPrsonalInfo()
    {
      $user_id = $this->input->post('user_id', TRUE); 
      $name = $this->input->post('name', TRUE); 
      $category_id = $this->input->post('category_id', TRUE); 
      $gender = $this->input->post('gender', TRUE); 
      $city = $this->input->post('city', TRUE); 
      $country = $this->input->post('country', TRUE); 
      $preference = $this->input->post('preference', TRUE); 
      $category_id = $this->input->post('category_id', TRUE); 
      $about_us = $this->input->post('about_us', TRUE); 
      $description = $this->input->post('description', TRUE); 
      $bio = $this->input->post('bio', TRUE); 
      $location = $this->input->post('location', TRUE); 
      $image = $this->input->post('image', TRUE); 
      $longitude = $this->input->post('longitude', TRUE); 
      $latitude = $this->input->post('latitude', TRUE); 
      $skills = $this->input->post('skills', TRUE); 
      $price = $this->input->post('price', TRUE); 
      $video_url = $this->input->post('video_url', TRUE); 

      $this->checkUserStatus($user_id);
      $check_user = $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$user_id));
      if($check_user)
      {
       $this->load->library('upload');
  
       $config['image_library'] = 'gd2';
       $config['upload_path']   = './assets/images/'; 
       $config['allowed_types'] = 'gif|jpg|jpeg|png';
       $config['max_size']      = 10000; 
       $config['file_name']     = time();
       $config['create_thumb'] = TRUE;
       $config['maintain_ratio'] = TRUE;
       $config['width'] = 250;
       $config['height'] = 250;
       $this->upload->initialize($config);
       $updateduserimage="";
       if ( $this->upload->do_upload('image') && $this->load->library('image_lib', $config))
       {         
          $updateduserimage='assets/images/'.$this->upload->data('file_name');
       }
       else  
        {
          //  echo $this->upload->display_errors();
        }

        $where= array('user_id'=>$user_id);
        $data['name']=isset($name) ? $name: $check_user->name;

        $this->Api_model->updateSingleRow(USR_TBL,$where,$data);

        $data['gender']=isset($gender) ? $gender: $check_user->gender;
        $data['city']=isset($city) ? $city: $check_user->city;
        $data['country']=isset($country) ? $country: $check_user->country;
        $data['preference']=isset($preference) ? $preference: $check_user->preference;
        $data['price']=isset($price) ? $price: $check_user->price;
        $data['skills']=isset($skills) ? $skills: $check_user->skills;
        $data['about_us']=isset($about_us) ? $about_us: $check_user->about_us;
        $data['longitude']=isset($longitude) ? $longitude: $longitude;
        $data['latitude']=isset($latitude) ? $latitude: $latitude;
        $data['description']=isset($description) ? $description: $check_user->description;
        $data['video_url']=isset($video_url) ? $video_url: $check_user->video_url;
        if($updateduserimage)
        {
          $data['image']=$updateduserimage;
        }
       
        $data['category_id']=isset($category_id) ? $category_id: $check_user->category_id;
        $data['bio']=isset($bio) ? $bio: $check_user->bio;
        $data['updated_at']=time();
        $data['update_profile']=1;
        $data['location']=isset($location) ? $location: $check_user->location;
        $updateUser= $this->Api_model->updateSingleRow(ART_TBL,$where,$data);
        if($updateUser)
        {
          $where =array('user_id'=>$user_id);
          $get_artists=$this->Api_model->getSingleRow(ART_TBL,$where);
          $get_cat=$this->Api_model->getSingleRow(CAT_TBL, array('id'=>$get_artists->category_id));

          if(!empty($get_cat))
          {
            $get_artists->category_name=$get_cat->cat_name;
            $commission_setting= $this->Api_model->getSingleRow('commission_setting',array('id'=>1));
            if($commission_setting->commission_type==0)
            {
              $get_artists->category_price=$get_cat->price;
            }
            elseif($commission_setting->commission_type==1)
            {
              if($commission_setting->flat_type==2)
              {
                $get_artists->category_price = $commission_setting->flat_amount;
              }
              elseif ($commission_setting->flat_type==1) 
              {
                $get_artists->category_price = $commission_setting->flat_amount;
              }
            }
          }
          else
          {
            $get_artists->category_name="";
            $get_artists->category_price="";
          }

         $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
         $get_artists->currency_type=$currency_setting->currency_symbol;

          $checkArtist = $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$get_artists->user_id));
      
          if($checkArtist->image)
          {
            $get_artists->image=base_url().$checkArtist->image;
          }
          else
          {
            $get_artists->image=base_url().'/assets/images/image.png';
          }

          $where= array('artist_id'=>$get_artists->user_id);
          $ava_rating=$this->Api_model->getAvgWhere('rating', 'rating',$where);
          if($ava_rating[0]->rating==null)
          {
            $ava_rating[0]->rating="0";
          }
          $get_artists->ava_rating= round($ava_rating[0]->rating,1);

          $skills=json_decode($get_artists->skills);
          $skill= array();
          if(!empty($skills))
          {
             foreach ($skills as $skills) 
             {
                $get_skills=  $this->Api_model->getSingleRow('skills', array('id'=>$skills));
                array_push($skill, $get_skills);
             }
            $get_artists->skills= $skill;
          }
          else
          {
            $get_artists->skills= array();
          }

          $get_products=  $this->Api_model->getAllDataWhere(array('user_id'=>$user_id), 'products');

          $products= array();
          foreach ($get_products as $get_products) 
          {
            $get_products->product_image=base_url().$get_products->product_image;
            array_push($products, $get_products);
          }
          $get_artists->products=$products;

          $get_reviews=  $this->Api_model->getAllDataWhere(array('artist_id'=>$user_id, 'status'=>1), 'rating');
          $review = array();
          foreach ($get_reviews as $get_reviews) 
          {
            $get_user = $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$get_reviews->user_id));
            $get_reviews->name= $get_user->name;

            if($get_user->image)
            {
              $get_reviews->image=base_url().$get_user->image;
            }
            else
            {
              $get_reviews->image=base_url().'/assets/images/image.png';
            }

            array_push($review, $get_reviews);
          }
          $get_artists->reviews=$review;

          $get_qualifications=  $this->Api_model->getAllDataWhere(array('user_id'=>$user_id), 'qualifications');
          
          $get_artists->qualifications=$get_qualifications;
        
          $get_gallery=  $this->Api_model->getAllDataWhere(array('user_id'=>$user_id), GLY_TBL);
          
          $gallery = array();
          foreach ($get_gallery as $get_gallery) {

            $get_gallery->image= base_url().$get_gallery->image;
            array_push($gallery, $get_gallery);
          }
          $get_artists->gallery=$gallery;

          $earning=$this->Api_model->getSumWhere('total_amount', IVC_TBL,array('artist_id'=>$user_id));

          $get_artists->earning= round($earning->total_amount, 2);

          $get_artists->jobDone=$this->Api_model->getTotalWhere(ABK_TBL,array('artist_id'=>$user_id,'booking_flag'=>4));

          $get_artists->totalJob=$this->Api_model->getTotalWhere(ABK_TBL,array('artist_id'=>$user_id));

          if($get_artists->totalJob==0)
            {
              $get_artists->completePercentages=0;
            }
            else
            {
              $get_artists->completePercentages=round(($get_artists->jobDone*100) / $get_artists->totalJob);
            }

            $artist_booking=  $this->Api_model->getAllDataLimitWhere(ABK_TBL,array('artist_id'=>$user_id, 'booking_flag'=>4), 7);
            $artist_bookings = array();
          foreach ($artist_booking as $artist_booking) 
          {
            $rat=$this->Api_model->getSingleRow('rating', array('booking_id'=>$artist_booking->id, 'status'=>1));
            if($rat)
            {
                $get_user = $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$rat->user_id));
                $artist_booking1['username']= $get_user->name;
                if($get_user->image)
                {
                  $artist_booking1['userImage']= base_url().$get_user->image;
                }
                else
                {
                  $artist_booking1['userImage']= base_url()."assets/images/image.png";
                }               
                $artist_booking1['rating']=$rat->rating;
                $artist_booking1['comment']=$rat->comment;
                $artist_booking1['ratingDate']=$rat->created_at;
            }
            else
            { 
               $get_user = $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$artist_booking->user_id));
                $artist_booking1['username']= $get_user->name;
                if($get_user->image)
                {
                  $artist_booking1['userImage']= base_url().$get_user->image;
                }
                else
                {
                  $artist_booking1['userImage']= base_url()."assets/images/image.png";
                }
                $artist_booking1['rating']="0";
                $artist_booking1['comment']="";
                $artist_booking1['ratingDate']=$artist_booking->created_at;
            }

            $artist_booking1['price']=$artist_booking->price;
            $artist_booking1['booking_time']=$artist_booking->booking_time;
            $artist_booking1['booking_date']=$artist_booking->booking_date;
            array_push($artist_bookings, $artist_booking1);
          }
          $get_artists->artist_booking= $artist_bookings;

          $this->api->api_message_data(1, ARTIST_UPDATE,'data' , $get_artists);
        }
        else
        {
          $this->api->api_message(0, TRY_AGAIN);
        }
      }
      else
      {
         $this->load->library('upload');
  
         $config['image_library'] = 'gd2';
         $config['upload_path']   = './assets/images/'; 
         $config['allowed_types'] = 'gif|jpg|jpeg|png';
         $config['max_size']      = 10000; 
         $config['file_name']     = time();
         $config['create_thumb'] = TRUE;
         $config['maintain_ratio'] = TRUE;
         $config['width'] = 250;
         $config['height'] = 250;
         $this->upload->initialize($config);
         $updateduserimage="";
         if ( $this->upload->do_upload('image') && $this->load->library('image_lib', $config))
         {         
            $updateduserimage='assets/images/'.$this->upload->data('file_name');
         }
         else  
          {
            //  echo $this->upload->display_errors();
          }

        $data['user_id']=$user_id;
        $data['name']=isset($name) ? $name: "";
        if($updateduserimage)
        {
          $data['image']=$updateduserimage;
        }
        $this->Api_model->updateSingleRow(USR_TBL,array('user_id'=>$user_id),$data);

        $data['created_at']=time();
        $data['update_profile']=1;
        $data['updated_at']=time();
        $data['gender']=isset($gender) ? $gender: "";
        $data['city']=isset($city) ? $city: "";
        $data['country']=isset($country) ? $country: "";
        $data['preference']=isset($preference) ? $preference: "";
        $data['about_us']=isset($about_us) ? $about_us: "";
        $data['price']=isset($price) ? $price: "";
        $data['skills']=isset($skills) ? $skills: "";
        $data['longitude']=isset($longitude) ? $longitude: "75.897542";
        $data['latitude']=isset($latitude) ? $latitude: "22.749753";
        $data['category_id']=isset($category_id) ? $category_id: "";
        $data['description']=isset($description) ? $description: "";
        $data['bio']=isset($bio) ? $bio: "";
        $data['location']=isset($location) ? $location: "";

        $getUserId=$this->Api_model->insertGetId(ART_TBL,$data);

        if($getUserId)
        {
          $where =array('user_id'=>$user_id);
          $get_artists=$this->Api_model->getSingleRow(ART_TBL,$where);

          $get_cat=$this->Api_model->getSingleRow(CAT_TBL, array('id'=>$get_artists->category_id));
          $checkArtist = $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$get_artists->user_id));
      
          if($checkArtist->image)
          {
            $get_artists->image=base_url().$checkArtist->image;
          }
          else
          {
            $get_artists->image=base_url().'/assets/images/image.png';
          }
          
          if(!empty($get_cat))
          {
            $get_artists->category_name=$get_cat->cat_name;
            $commission_setting= $this->Api_model->getSingleRow('commission_setting',array('id'=>1));
          if($commission_setting->commission_type==0)
          {
            $get_artists->category_price=$get_cat->price;
          }
          elseif($commission_setting->commission_type==1)
          {
            if($commission_setting->flat_type==2)
            {
              $get_artists->category_price = $commission_setting->flat_amount;
            }
            elseif ($commission_setting->flat_type==1) 
            {
              $get_artists->category_price = $commission_setting->flat_amount;
            }
          }
          }
          else
          {
            $get_artists->category_name="";
            $get_artists->category_price="";
          }

          $where= array('artist_id'=>$user_id);
          $ava_rating=$this->Api_model->getAvgWhere('rating', 'rating',$where);
          if($ava_rating[0]->rating==null)
          {
            $ava_rating[0]->rating="0";
          }
          $get_artists->ava_rating=round($ava_rating[0]->rating,1);

          $skills=json_decode($get_artists->skills);
          $skill= array();
          if(!empty($skills))
          {
            foreach ($skills as $skills) {
            $get_skills=  $this->Api_model->getSingleRow('skills', array('id'=>$skills));
            array_push($skill, $get_skills);
            }
            $get_artists->skills= $skill;
          }
          else
          {
            $get_artists->skills= array();
          }

          $get_products=  $this->Api_model->getAllDataWhere(array('user_id'=>$user_id), 'products');

          $products= array();
          foreach ($get_products as $get_products) {
            $get_products->product_image=base_url().$get_products->product_image;
            array_push($products, $get_products);
          }
          $get_artists->products=$products;

          $get_reviews=  $this->Api_model->getAllDataWhere(array('artist_id'=>$user_id), 'rating');
          $review = array();
          foreach ($get_reviews as $get_reviews) {

            $get_user = $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$user_id));
            $get_reviews->name= $get_user->name;
            if($get_user->image)
            {
              $get_reviews->image=base_url().$get_user->image;
            }
            else
            {
              $get_reviews->image=base_url().'/assets/images/image.png';
            }
            array_push($review, $get_reviews);
          }
          $get_artists->reviews=$review;

          $get_qualifications=  $this->Api_model->getAllDataWhere(array('user_id'=>$user_id), 'qualifications');
          
          $get_artists->qualifications=$get_qualifications;

           $get_gallery=  $this->Api_model->getAllDataWhere(array('user_id'=>$user_id), GLY_TBL);
          
          $gallery = array();
          foreach ($get_gallery as $get_gallery) {

            $get_gallery->image= base_url().$get_gallery->image;
            array_push($gallery, $get_gallery);
          }
          $get_artists->gallery=$gallery;

          $earning=$this->Api_model->getSumWhere('total_amount', IVC_TBL,array('artist_id'=>$user_id));

          $get_artists->earning= round($earning->total_amount, 2);

          $get_artists->jobDone=$this->Api_model->getTotalWhere(ABK_TBL,array('artist_id'=>$user_id,'booking_flag'=>4));

          $get_artists->totalJob=$this->Api_model->getTotalWhere(ABK_TBL,array('artist_id'=>$user_id));

          if($get_artists->totalJob==0)
            {
              $get_artists->completePercentages=0;
            }
            else
            {
              $get_artists->completePercentages=round(($get_artists->jobDone*100) / $get_artists->totalJob);
            }
        
        $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
        $get_artists->currency_type=$currency_setting->currency_symbol;

            $artist_booking=  $this->Api_model->getAllDataLimitWhere(ABK_TBL,array('artist_id'=>$user_id, 'booking_flag'=>4), 7);
            $artist_bookings = array();
          foreach ($artist_booking as $artist_booking) 
          {
            $rat=$this->Api_model->getSingleRow('rating', array('booking_id'=>$artist_booking->id, 'status'=>1));
            if($rat)
            {
                $get_user = $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$rat->user_id));
                $artist_booking1['username']= $get_user->name;
                if($get_user->image)
                {
                  $artist_booking1['userImage']= base_url().$get_user->image;
                }
                else
                {
                  $artist_booking1['userImage']= base_url()."assets/images/image.png";
                }               
                $artist_booking1['rating']=$rat->rating;
                $artist_booking1['comment']=$rat->comment;
                $artist_booking1['ratingDate']=$rat->created_at;
            }
            else
            { 
               $get_user = $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$artist_booking->user_id));
                $artist_booking1['username']= $get_user->name;
                if($get_user->image)
                {
                  $artist_booking1['userImage']= base_url().$get_user->image;
                }
                else
                {
                  $artist_booking1['userImage']= base_url()."assets/images/image.png";
                }
                $artist_booking1['rating']="0";
                $artist_booking1['comment']="";
                $artist_booking1['ratingDate']=$artist_booking->created_at;
            }

            $artist_booking1['price']=$artist_booking->price;
            $artist_booking1['booking_time']=$artist_booking->booking_time;
            $artist_booking1['booking_date']=$artist_booking->booking_date;
            array_push($artist_bookings, $artist_booking1);
          }
          $get_artists->artist_booking= $artist_bookings;

          $this->api->api_message_data(1, ARTIST_UPDATE,'data' , $get_artists);
        }
        else
        {
          $this->api->api_message(0, TRY_AGAIN);
        }
      }
    }

    /*Add product by artist*/
    public function addProduct()
    {
      $data['user_id'] = $this->input->post('user_id', TRUE); 
      $data['product_name'] = $this->input->post('product_name', TRUE); 
      $product_image = $this->input->post('product_image', TRUE); 
      $data['price'] = $this->input->post('price', TRUE); 

      $this->checkUserStatus($data['user_id']);

       $this->load->library('upload');
  
         $config['image_library'] = 'gd2';
         $config['upload_path']   = './assets/images/'; 
         $config['allowed_types'] = 'gif|jpg|jpeg|png';
         $config['max_size']      = 10000; 
         $config['file_name']     = time();
         $config['create_thumb'] = TRUE;
         $config['maintain_ratio'] = TRUE;
         $config['width'] = 250;
         $config['height'] = 250;
         $this->upload->initialize($config);
         $pruductImage="";
         if ( $this->upload->do_upload('product_image') && $this->load->library('image_lib', $config))
         {         
            $pruductImage='assets/images/'.$this->upload->data('file_name');
         }
         else  
          {
            //  echo $this->upload->display_errors();
          }

        if($pruductImage)
        {
          $data['product_image']=$pruductImage;
        }
         $data['created_at']=time();
        $data['updated_at']=time();

         $productId=$this->Api_model->insertGetId('products',$data);

        if($productId)
        {
          $this->api->api_message(1, PRODUCT_ADD);
        }
        else
        {
          $this->api->api_message(0, TRY_AGAIN);
        }

    }


     /*Add Qualification by artist*/
    public function addQualification()
    {
      $data['user_id'] = $this->input->post('user_id', TRUE); 
      $data['title'] = $this->input->post('title', TRUE); 
      $data['description'] = $this->input->post('description', TRUE); 

      $this->checkUserStatus($data['user_id']);

        $data['created_at']=time();
        $data['updated_at']=time();

         $productId=$this->Api_model->insertGetId('qualifications',$data);

        if($productId)
        {
          $this->api->api_message(1, QUALIFICATION_ADD);
        }
        else
        {
          $this->api->api_message(0, TRY_AGAIN);
        }
    }

     /*Add Rating for artist*/
    public function addRating()
    {
      $data['user_id'] = $this->input->post('user_id', TRUE); 
      $data['booking_id'] = $this->input->post('booking_id', TRUE); 
      $data['artist_id'] = $this->input->post('artist_id', TRUE); 
      $data['rating'] = $this->input->post('rating', TRUE); 
      $data['comment'] = $this->input->post('comment', TRUE); 

      $this->checkUserStatus($data['user_id']);
      $this->checkUserStatus($data['artist_id']);

      $data['created_at']=time();

      $get_rating= $this->Api_model->getSingleRow('rating', array('user_id'=>$data['user_id'],'artist_id'=>$data['artist_id'],'booking_id'=>$data['booking_id']));
      if($get_rating)
      {
        $this->api->api_message(1, "you already give review this artist.");
      }
      else
      {
         $productId=$this->Api_model->insertGetId('rating',$data);
         if($productId)
        {
          $this->api->api_message(1, ADD_COMMENT);
        }
        else
        {
          $this->api->api_message(0, TRY_AGAIN);
        }
      }
    }

    public function getToken()
    {
      echo $this->api->strongToken(6);
    }
    /*Forget PAssword*/  
    public function forgotPassword()
    {
        $email_id = $this->input->post('email_id', TRUE);

        $checkEmail = $this->Api_model->getSingleRow(USR_TBL, array('email_id'=>$email_id));      
        if($checkEmail)
        {
          $password =$this->api->strongToken(6);
          $msg='Your updated password is '.$password;
          $this->send_email($email_id, PWD_SUB, $msg);

          $data = array('password'=>$password);
          $updatePassword= $this->Api_model->updateSingleRow(USR_TBL,array('email_id'=>$email_id),$data); 
          if($updatePassword==1)
          {
            $this->api->api_message(1, FOUND);
          }
          else
          {
            $this->api->api_message(0, NOTUPDATE);
          }
        }
        else
        {
          $this->api->api_message(0, NOTFOUND);
        } 
    }

    public function userActive()
    {
       $user_id= $_GET['user_id'];
       $get_user= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$user_id));
       if($get_user)
       {
          $where = array('user_id'=>$get_user->user_id);
          $data = array('status'=>1);
          $update= $this->Api_model->updateSingleRow(USR_TBL, $where, $data);

          $this ->load->view('activeUser.php');
       }
    }

    public function onlineOffline()
    {
      $user_id = $this->input->post('user_id', TRUE);
      $is_online = $this->input->post('is_online', TRUE);
      $update= $this->Api_model->updateSingleRow(ART_TBL, array('user_id'=> $user_id), array('is_online'=>$is_online));
      if($is_online==1)
      {
        $this->api->api_message(1, "Artist online successfully.");
      }
      elseif ($is_online==0) 
      {
        $this->api->api_message(1, "Artist offline successfully.");
      }    
    }

    /*Edit User Profile */ 
    public function editPersonalInfo()
    {
        $name = $this->input->post('name', TRUE); 
        $email_id = $this->input->post('email_id', TRUE); 
        $user_id = $this->input->post('user_id', TRUE);
        $city = $this->input->post('city', TRUE);
        $country = $this->input->post('country', TRUE);
        $address = $this->input->post('address', TRUE);
        $latitude = $this->input->post('latitude', TRUE);
        $longitude = $this->input->post('longitude', TRUE);
        $gender = $this->input->post('gender', TRUE);
        $mobile = $this->input->post('mobile', TRUE);
        $office_address = $this->input->post('office_address', TRUE);
        $image = $this->input->post('image', TRUE);
        $password = $this->input->post('password', TRUE);
        $new_password = $this->input->post('new_password', TRUE);

        $this->checkUserStatus($user_id);

        $checkUser=$this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$user_id));
        if($checkUser)
        {    
          if($email_id != $checkUser->email_id)
          {
            $checkEmail=$this->Api_model->getSingleRow(USR_TBL, array('email_id'=>$email_id));

            if($checkEmail)
            {
              $this->api->api_message(0, "Email id Already Exists.");
              exit();
            }     
          }

          if($password)
          {
            $checkPass=$this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$user_id, 'password'=>$password));

            if($checkPass)
            {
              $where= array('user_id'=>$user_id);
              $data['password']= $new_password;
              $updateUser =$this->Api_model->updateSingleRow(USR_TBL, $where, $data);

              $this->api->api_message(0, EDITSUCCESSFULL);
            }
            else
            {
             $this->api->api_message(0, "Old Password does not matched.");
            }
            exit();
          }    

          $this->load->library('upload');
    
           $config['image_library'] = 'gd2';
           $config['upload_path']   = './assets/images/'; 
           $config['allowed_types'] = 'gif|jpg|jpeg|png';
           $config['max_size']      = 100000; 
           $config['file_name']     = time();
           $config['create_thumb'] = TRUE;
           $config['maintain_ratio'] = TRUE;
           $config['width'] = 250;
           $config['height'] = 250;
           $this->upload->initialize($config);
           $ProfileImage="";
           if ( $this->upload->do_upload('image') && $this->load->library('image_lib', $config))
           {         
              $ProfileImage='assets/images/'.$this->upload->data('file_name');
           }
           else  
            {
              //  echo $this->upload->display_errors();
            }

            $where= array('user_id'=>$user_id);
            $data['name']=isset($name) ? $name: $checkUser->name;
            $this->Api_model->updateSingleRow(ART_TBL,array('user_id'=>$user_id),$data);

            if($ProfileImage)
            {
              $data['image']=$ProfileImage;
            }
            
            $data['latitude']=isset($latitude) ? $latitude: $checkUser->latitude;
            $data['longitude']=isset($longitude) ? $longitude: $checkUser->longitude;
            $data['gender']=isset($gender) ? $gender: $checkUser->gender;
            $data['mobile']=isset($mobile) ? $mobile: $checkUser->mobile;
            $data['office_address']=isset($office_address) ? $office_address: $checkUser->office_address;
            $data['email_id']=isset($email_id) ? $email_id: $checkUser->email_id;
            $data['address']=isset($address) ? $address: $checkUser->address;
            $data['city']=isset($city) ? $city: $checkUser->city;
            $data['country']=isset($country) ? $country: $checkUser->country;

           $updateUser =$this->Api_model->updateSingleRow(USR_TBL, $where, $data);
           if($updateUser)
           {
              $checkUser=$this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$user_id));

             $user_id=$checkUser->user_id;
             $role=$checkUser->role;
             if($role==1)
              {
                if($checkUser->image)
                {
                  $checkUser->image= base_url().$checkUser->image;
                }  
                else
                {
                  $checkUser->image= base_url()."assets/images/image.png";
                }           
              }
              else
              {
                if($checkUser->image)
                {
                  $checkUser->image= base_url().$checkUser->image;
                }  
                else
                {
                  $checkUser->image= base_url()."assets/images/image.png";
                }  
              }
             $this->api->api_message_data(1, EDITSUCCESSFULL, 'data', $checkUser);
            }
           else
           {
              $this->api->api_message(0, EDITFAIL);
            }
          }
          else
          {
            $this->api->api_message(0, NOTAVAILABLE);
          }
    }

    /*Book Appointment*/
    public function book_appointment()
    {
      $data['user_id'] = $this->input->post('user_id', TRUE);
      $data['artist_id'] = $this->input->post('artist_id', TRUE);
      $data['date_string']= $this->input->post('date_string', TRUE);
      $data['timezone']= $this->input->post('timezone', TRUE);
      $data['appointment_date'] = date('Y-m-d', strtotime($data['date_string']));
      $data['timing'] = date('h:i a', strtotime($data['date_string']));
      $data['created_at']=time();
      $data['updated_at']=time();
      $data['appointment_timestamp']=strtotime($data['date_string']);

      $this->checkUserStatus($data['user_id']);

      $appId = $this->Api_model->insertGetId(APP_TBL, $data);

      if($appId)
      {
        $checkUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$data['user_id']));
        $msg=$checkUser->name.': booked you on'.$data['timing'];
        $this->firebase_notification($data['artist_id'], "Book Appointment" ,$msg);

        $this->api->api_message(1, BOOK_APP);
      }
      else
      {
        $this->api->api_message(0, TRY_AGAIN);
      }
    }

    /*Edit Appointment*/
    public function edit_appointment()
    {
      $appointment_id= $this->input->post('appointment_id', TRUE);
      $data['user_id'] = $this->input->post('user_id', TRUE);
      $data['artist_id'] = $this->input->post('artist_id', TRUE);
      $data['date_string']= $this->input->post('date_string', TRUE);
      $data['timezone']= $this->input->post('timezone', TRUE);
      $data['appointment_date'] = date('Y-m-d', strtotime($data['date_string']));
      $data['timing'] = date('h:i a', strtotime($data['date_string']));
      $data['status']=1;
      $data['updated_at']=time();
      $data['appointment_timestamp']=strtotime($data['date_string']);

      $this->checkUserStatus($data['user_id']);

      $appId = $this->Api_model->getSingleRow(APP_TBL, array('id'=>$appointment_id));

      if($appId)
      {
        $checkUser= $this->Api_model->updateSingleRow(APP_TBL, array('id'=>$appointment_id), $data);

        $this->api->api_message(1, BOOK_APP);
      }
      else
      {
        $this->api->api_message(0, TRY_AGAIN);
      }
    }

    /*Appointment Delete*/
    public function declineAppointment()
    {
      $appointment_id= $this->input->post('appointment_id', TRUE);
      $user_id= $this->input->post('user_id', TRUE);
      $this->checkUserStatus($user_id);

      $get_appointment = $this->Api_model->getSingleRow(APP_TBL, array('id'=>$appointment_id));
      if($get_appointment)
      {
        $this->Api_model->updateSingleRow(APP_TBL, array('id'=>$appointment_id), array('status'=>0));
        $this->api->api_message(1, APP_DECLINE);
      }
      else
      {
        $this->api->api_message(0, TRY_AGAIN);
      }
    }

    public function getAppointment()
    {
      $user_id= $this->input->post('user_id', TRUE);
      $role = $this->input->post('role', TRUE);

      $this->checkUserStatus($user_id);

      if($role==1)
      {      
        $where=array('artist_id'=>$user_id);   

          $get_appointment=$this->Api_model->getAllDataWhereoderBy($where,APP_TBL);

          if($get_appointment)
          {
            $get_appointments = array();
            foreach ($get_appointment as $get_appointment) 
            {
              $get_user= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$get_appointment->user_id));

              $get_appointment->userName= $get_user->name;
              $get_appointment->userEmail= $get_user->email_id;
              $get_appointment->userMobile= $get_user->mobile;

              if($get_user->image)
              {
                $get_appointment->userImage= base_url().$get_user->image;
              }
              else
              {
                $get_appointment->userImage= base_url()."assets/images/image.png";
              }
              $get_appointment->userAddress= $get_user->address;

              $get_artist= $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$get_appointment->artist_id));
              $get_artistDetails= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$get_appointment->artist_id));

              $get_cat=$this->Api_model->getSingleRow(CAT_TBL, array('id'=>$get_artist->category_id));
              $get_appointment->category_name=$get_cat->cat_name;
              $get_appointment->category_price=$get_cat->price;

              $get_appointment->artistName= $get_artist->name;
              $get_appointment->artistMobile= $get_artistDetails->mobile;
              $get_appointment->artistEmail= $get_artistDetails->email_id;

              $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
              $get_appointment->currency_type=$currency_setting->currency_symbol;

              if($get_artistDetails->image)
              {
                $get_appointment->artistImage= base_url().$get_artistDetails->image;
              }
              else
              {
                $get_appointment->artistImage= base_url()."assets/images/image.png";
              }
              $get_appointment->artistAddress= $get_artist->location;

              array_push($get_appointments, $get_appointment);
            }
          
            $this->api->api_message_data(1, GET_APP,'data' , $get_appointments);
          }
          else
          {
            $this->api->api_message(0, NOTFOUND);
          }     
        }
        elseif($role==2)
        {
          $where=array('user_id'=>$user_id);

          $get_appointment=$this->Api_model->getAllDataWhere($where,APP_TBL);

          if($get_appointment)
          {
            $get_appointments = array();
            foreach ($get_appointment as $get_appointment) 
            {
              $get_artist= $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$get_appointment->artist_id));

              $get_artistDetails= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$get_appointment->artist_id));
              $get_appointment->artistMobile= $get_artistDetails->mobile;
              $get_appointment->artistEmail= $get_artistDetails->email_id;

              $get_cat=$this->Api_model->getSingleRow(CAT_TBL, array('id'=>$get_artist->category_id));
              $get_appointment->category_name=$get_cat->cat_name;
              $get_appointment->category_price=$get_cat->price;

              if($get_artistDetails->image)
              {
                $get_appointment->artistImage= base_url().$get_artistDetails->image;
              }
              else
              {
                $get_appointment->artistImage= base_url()."assets/images/image.png";
              }

              $get_appointment->artistName= $get_artist->name;
              $get_appointment->artistAddress= $get_artist->location;
              $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
              $get_appointment->currency_type=$currency_setting->currency_symbol;

              $get_user= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$get_appointment->user_id));
              if($get_user->image)
              {
                $get_appointment->userImage= base_url().$get_user->image;
              }
              else
              {
                $get_appointment->userImage= base_url()."assets/images/image.png";
              }

              $get_appointment->userName= $get_user->name;
              $get_appointment->userAddress= $get_user->address;
              $get_appointment->userEmail= $get_user->email_id;
              $get_appointment->userMobile= $get_user->mobile;

              array_push($get_appointments, $get_appointment);
            }
            $this->api->api_message_data(1, GET_APP,'data' , $get_appointments);
          }
          else
          {
            $this->api->api_message(0, NOTFOUND);
          }     
        }
        else
        {
          $this->api->api_message(0, "Invalid Request");
        }
    }
    /*Update or Create artist*/
    public function artistProfile()
    {
      $user_id = $this->input->post('user_id', TRUE); 
      $name = $this->input->post('name', TRUE); 
      $category_id = $this->input->post('category_id', TRUE); 
      $description = $this->input->post('description', TRUE); 
      $about_us = $this->input->post('about_us', TRUE); 
      $qualification = $this->input->post('qualification', TRUE); 
      $skills = $this->input->post('skills', TRUE); 
      $job_done = $this->input->post('job_done', TRUE); 
      $hire_rate = $this->input->post('hire_rate', TRUE); 
      $bio = $this->input->post('bio', TRUE); 
      $longitude = $this->input->post('longitude', TRUE);
      $latitude = $this->input->post('latitude', TRUE);  
      $created_at = time(); 
      $updated_at = time();       
      $address = $this->input->post('address', TRUE); 

      $this->checkUserStatus($user_id);

      $data = array(
       'user_id' =>$user_id, 
       'name' => $name, 
       'category_id' =>$category_id, 
       'description' =>$description, 
       'about_us' =>$about_us,
       'qualification' =>$qualification,
       'skills' =>$skills,
       'job_done' =>$job_done, 
       'hire_rate' =>$hire_rate,
       'bio' =>$bio, 
       'longitude' =>$longitude,
       'latitude' =>$latitude,
       'created_at' => $created_at,
       'updated_at'=> $updated_at,       
       'address' => $address 
       );
      $table= ART_TBL;
      $columnName = 'user_id';
      $condition = array('user_id'=>$user_id);
      $checkArtist = $this->Api_model->checkData($table, $condition,$columnName);
      if($checkArtist == 1)
      {
        $this->api->api_message(0, FOUND);
        $addArtist = $this->Api_model->insert($table, $data);
      }
      else{
        $this->api->api_message(0, USERNOTFOND);
      }
    }

    /*Book artist*/
    public function book_artist()
    {
      $data['user_id'] = $this->input->post('user_id', TRUE);
      $data['artist_id'] = $this->input->post('artist_id', TRUE);
      $data['price'] = $this->input->post('price', TRUE);
      $date_string= $this->input->post('date_string', TRUE);
      $data['time_zone']= $this->input->post('timezone', TRUE);
      $data['booking_date'] = date('Y-m-d', strtotime($date_string));
      $data['booking_time'] = date('h:i a', strtotime($date_string));
      $data['created_at']=time();
      $data['updated_at']=time();
      $data['booking_timestamp']=strtotime($date_string);

      $this->checkUserStatus($data['user_id']);

      $checkArtist= $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$data['artist_id'],'booking_flag'=>1));
      if($checkArtist)
      {
        $this->api->api_message(0, "Artist Busy with another client. Please try after sometime.");
        exit();
      }
      
      $getArtist= $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$data['artist_id']));

      $category_id= $getArtist->category_id;
      $category= $this->Api_model->getSingleRow(CAT_TBL, array('id'=>$category_id));

      $commission_setting= $this->Api_model->getSingleRow('commission_setting',array('id'=>1));

      $data['commission_type']=$commission_setting->commission_type;
      $data['flat_type']=$commission_setting->flat_type;
      if($commission_setting->commission_type==0)
      {
        $data['category_price']= $category->price;
      }
      elseif($commission_setting->commission_type==1)
      {
        if($commission_setting->flat_type==2)
        {
          $data['category_price']= $commission_setting->flat_amount;
        }
        elseif ($commission_setting->flat_type==1) 
        {
          $data['category_price']= $commission_setting->flat_amount;
        }
      }
      
      $appId = $this->Api_model->insertGetId(ABK_TBL, $data);
      if($appId)
      {
        $checkUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$data['user_id']));
        $msg=$checkUser->name.': booked you on '.$date_string;
        $this->firebase_notification($data['artist_id'], "Book Appointment" ,$msg);

        $dataNotification['user_id']= $data['artist_id'];
        $dataNotification['title']= "Book Appointment";
        $dataNotification['msg']= $msg;
        $dataNotification['type']= "Individual";
        $dataNotification['created_at']=time(); 
        $this->Api_model->insertGetId(NTS_TBL,$dataNotification);

        $updateUser=$this->Api_model->updateSingleRow(ART_TBL,array('user_id'=>$data['artist_id']),array('booking_flag'=>1));
        $this->api->api_message(1, BOOK_APP);
      }
      else
      {
        $this->api->api_message(0, TRY_AGAIN);
      }
    }

    /*Decline Booking*/
    public function decline_booking()
    {
      $booking_id =$this->input->post('booking_id', TRUE);
      $user_id =$this->input->post('user_id', TRUE);
      $data['decline_by'] =$this->input->post('decline_by', TRUE);
      $data['decline_reason'] =$this->input->post('decline_reason', TRUE);
      $data['booking_flag'] =2;

      $this->checkUserStatus($user_id);

      $getBooking= $this->Api_model->getSingleRow(ABK_TBL, array('id'=>$booking_id));
      if($getBooking)
      {
        $updateBooking=$this->Api_model->updateSingleRow(ABK_TBL,array('id'=>$booking_id),$data);

        if($updateBooking)
        {
          $checkUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getBooking->artist_id));
          $msg=$checkUser->name.': is decline your appointment.';
          $this->firebase_notification($getBooking->user_id, "Decline Appointment" ,$msg);

          $updateUser=$this->Api_model->updateSingleRow(ART_TBL,array('user_id'=>$getBooking->artist_id),array('booking_flag'=>0));
          $this->api->api_message(1, "Booking Decline successfully.");
        }
        else
        {
          $this->api->api_message(0, TRY_AGAIN);
        }
      }
      else
      {
        $this->api->api_message(0, TRY_AGAIN);
      }
    }


    /*Case 1 accept booking 2 start booking 3 end booking*/
    public function booking_operation()
    {
      $request =$this->input->post('request', TRUE);
      $booking_id =$this->input->post('booking_id', TRUE);
      $user_id =$this->input->post('user_id', TRUE);

      $this->checkUserStatus($user_id);

      switch ($request) 
      {
        case 1:
           $this->accept_booking($booking_id);
        break;

        case 2:
            $this->start_booking($booking_id);
        break;

        case 3:
            $this->end_booking($booking_id);
        break;
        default:
         $this->api->api_message(0, TRY_AGAIN);
      }
    }

    /*Accept Booking*/
    public function accept_booking($booking_id)
    {
      $data['booking_flag'] =1;

      $getBooking= $this->Api_model->getSingleRow(ABK_TBL, array('id'=>$booking_id));
      if($getBooking)
      {
        $updateBooking=$this->Api_model->updateSingleRow(ABK_TBL,array('id'=>$booking_id),$data);

        if($updateBooking)
        {
          $checkUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getBooking->artist_id));
          $msg=$checkUser->name.': has accepted your appointment.';
          $this->firebase_notification($getBooking->user_id, "Booking" ,$msg);

          $this->api->api_message(1, "Booking accepted successfully.");
        }
        else
        {
          $this->api->api_message(0, TRY_AGAIN);
        }
      }
      else
      {
        $this->api->api_message(0, TRY_AGAIN);
      }
    }

     /*Start Booking*/
    public function start_booking($booking_id)
    {
      $data['booking_flag'] =3;
      $data['start_time'] =time();

      $getBooking= $this->Api_model->getSingleRow(ABK_TBL, array('id'=>$booking_id));
      if($getBooking)
      {
        $checkArtist= $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$getBooking->artist_id,'booking_flag'=>1));
        if($getBooking->booking_type !=0)
        {
          if($checkArtist)
          {
            $this->api->api_message(0, "Artist Busy with another client. Please try after sometime.");
            exit();
          }
        }

        $updateBooking=$this->Api_model->updateSingleRow(ABK_TBL,array('id'=>$booking_id),$data);

        if($updateBooking)
        {
          $checkUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getBooking->artist_id));
          $msg='Your booking started successfully.';
          $this->firebase_notification($getBooking->user_id, "Start Booking" ,$msg);

          $this->api->api_message(1, "Booking Started successfully.");
        }
        else
        {
          $this->api->api_message(0, TRY_AGAIN);
        }
      }
      else
      {
        $this->api->api_message(0, TRY_AGAIN);
      }
    }

    /*Complete Booking (End)*/
    public function end_booking($booking_id)
    {
      $data['booking_flag'] = 4;
      $data['end_time'] =time();

      $getBooking= $this->Api_model->getSingleRow(ABK_TBL, array('id'=>$booking_id));
      if($getBooking)
      {
        if($getBooking->booking_type==2)
        {
          $this->Api_model->updateSingleRow(AJB_TBL,array('job_id'=>$getBooking->job_id,'artist_id'=>$getBooking->artist_id),array('status'=>2));
        }
        if($getBooking->booking_type==1)
        {
          $this->Api_model->updateSingleRow(APP_TBL,array('id'=>$getBooking->job_id),array('status'=>3));
        }

        $updateBooking=$this->Api_model->updateSingleRow(ABK_TBL,array('id'=>$booking_id),$data);

          $artist_id=$getBooking->artist_id;
          $user_id=$getBooking->user_id;
          $updateUser=$this->Api_model->updateSingleRow(ART_TBL,array('user_id'=>$artist_id),array('booking_flag'=>0));

          $getBooking= $this->Api_model->getSingleRow(ABK_TBL, array('id'=>$booking_id));
          $working_min= (float)round(abs($getBooking->start_time - $getBooking->end_time) / 60,2);
          $min_price = ($getBooking->price)/60;
          $getArtist= $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$artist_id));

          if($getArtist->artist_commission_type==1 || $getBooking->booking_type==2)
          {
            $f_amount =$getBooking->price;
          }
          else
          {
            $f_amount =$working_min*$min_price;
          }

          $commission_setting= $this->Api_model->getSingleRow('commission_setting',array('id'=>1));

          $datainvoice['commission_type']=$commission_setting->commission_type;
          $datainvoice['flat_type']=$commission_setting->flat_type;
          /*if($commission_setting->commission_type==0)
          {
            $total_amount= $f_amount + $getBooking->category_price;
            $datainvoice['category_amount']= $getBooking->category_price;
          }
          elseif($commission_setting->commission_type==1)
          {
            if($commission_setting->flat_type==2)
            {
              $total_amount= $f_amount + $commission_setting->flat_amount;
              $datainvoice['category_amount']= $commission_setting->flat_amount;
            }
            elseif ($commission_setting->flat_type==1) 
            {
              $total_amount= $f_amount + ($f_amount*$commission_setting->flat_amount)/100;
              $datainvoice['category_amount']= ($f_amount*$commission_setting->flat_amount)/100;
            }
          }*/

          if($commission_setting->commission_type==0)
          {
            $total_amount= $f_amount;
            $datainvoice['category_amount']= $getBooking->category_price;
          }
          elseif($commission_setting->commission_type==1)
          {
            if($commission_setting->flat_type==2)
            {
              $total_amount= $f_amount;
              $datainvoice['category_amount']= round(($f_amount*$commission_setting->flat_amount)/100,2);
            }
            elseif ($commission_setting->flat_type==1) 
            {
              $total_amount= $f_amount;
              $datainvoice['category_amount']= round(($f_amount*$commission_setting->flat_amount)/100,2);
            }
          }

          $datainvoice['artist_id']= $artist_id;
          $datainvoice['artist_amount']= round($f_amount, 2) - round(($f_amount*$commission_setting->flat_amount)/100,2);

          $getCommission= $this->Api_model->getSingleRow('artist_wallet',array('artist_id'=>$artist_id));

          if($getCommission)
          {
            $amount = $datainvoice['artist_amount'];
            $updateUser=$this->Api_model->updateSingleRow("artist_wallet",array('artist_id'=>$artist_id),array('amount'=>$amount));
          }
          else
          {
             $this->Api_model->insertGetId('artist_wallet',array('artist_id'=> $artist_id, 'amount'=>$datainvoice['artist_amount']));
          }

          $datainvoice['total_amount']= round($total_amount,2);
          $datainvoice['final_amount']= round($total_amount,2);
          $datainvoice['user_id']= $user_id;
          $datainvoice['invoice_id']= strtoupper($this->api->strongToken());
          $datainvoice['booking_id']= $booking_id;
          $datainvoice['working_min']= (float)round($working_min,2);
          $datainvoice['tax']= 0;
          $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));

          $datainvoice['currency_type']= $currency_setting->currency_symbol;
          $date= date('Y-m-d');
          $datainvoice['created_at']=time();
          $datainvoice['updated_at']=time();

          $invoiceId= $this->Api_model->insertGetId(IVC_TBL, $datainvoice);

          $getUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getBooking->user_id));
          $getBooking->userName= $getUser->name;
          $getBooking->address= $getUser->address;
          $getBooking->total_amount= $total_amount;
          $getBooking->working_min= (float)$working_min;

          $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
          $getBooking->currency_type=$currency_setting->currency_symbol;

          if($getUser->image)
          {
            $getBooking->userImage= base_url().$getUser->image;
          }
          else
          {
            $getBooking->userImage= base_url().'assets/images/image.png';
          }

          $checkUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getBooking->artist_id));
          $msg='Your booking end successfully.';
         $this->firebase_notification($getBooking->user_id, "End Booking" ,$msg);
          $dataNotification['user_id']= $getBooking->user_id;
          $dataNotification['title']= "End Appointment";
          $dataNotification['msg']= $msg;
          $dataNotification['type']= "Individual";
          $dataNotification['created_at']=time(); 
          $this->Api_model->insertGetId(NTS_TBL,$dataNotification);

          $this->api->api_message_data(1, BOOKING_END, 'data', $getBooking);
      }
      else
      {
        $this->api->api_message(0, TRY_AGAIN);
      }
    }

    /*Check coupon code*/
    public function checkCoupon()
    {
      $coupon_code= $this->input->post('coupon_code', TRUE);
      $user_id= $this->input->post('user_id', TRUE);
      $invoice_id= $this->input->post('invoice_id', TRUE);

      $this->checkUserStatus($user_id);

      $getCoupon= $this->Api_model->getSingleRow(DCP_TBL, array('coupon_code'=>$coupon_code,'status'=>1));
      if($getCoupon)
      {
        $getInvoice= $this->Api_model->getSingleRow(IVC_TBL, array('invoice_id'=>$invoice_id));

        $total_amount= $getInvoice->total_amount;
        $discount=$getCoupon->discount;
       
        $discount_type=  $getCoupon->discount_type;
        if($discount_type==1)
        {
          $precentage= ($total_amount*$discount)/100;
          $final_amount= $total_amount- round($precentage, 2);
        }
        else
        {
          $final_amount= $total_amount- $discount;
        }
        if($final_amount < 0) 
        {
          $final_amount= $total_amount;
        }
        $this->api->api_message_data(1, "Applied successfully.", 'final_amount',$final_amount);
      }
      else
      {
        $this->api->api_message(0, "Coupon code not valid.");
      }
    }

    /*make payment*/
    public function makePayment()
    {
      $user_id= $this->input->post('user_id', TRUE);
      $payment_type= $this->input->post('payment_type', TRUE);
      $coupon_code= $this->input->post('coupon_code', TRUE);
      $invoice_id= $this->input->post('invoice_id', TRUE);
      $final_amount= $this->input->post('final_amount', TRUE);
      $payment_status= $this->input->post('payment_status', TRUE);
      $payment_type= $this->input->post('payment_type', TRUE);

      $this->checkUserStatus($user_id);

      $getCoupon= $this->Api_model->getSingleRow(DCP_TBL, array('coupon_code'=>$coupon_code));
      if($getCoupon)
      {
        if($getCoupon)
        {
          if($payment_status==1)
          {
            if(isset($payment_type))
            {
               $this->Api_model->updateSingleRow(IVC_TBL,array('invoice_id'=>$invoice_id),array('final_amount'=>$final_amount,'coupon_code'=>$coupon_code,'flag'=>1,'payment_status'=>$payment_status,"payment_type"=>$payment_type));
            }
            else
            {
              $this->Api_model->updateSingleRow(IVC_TBL,array('invoice_id'=>$invoice_id),array('final_amount'=>$final_amount,'coupon_code'=>$coupon_code,'flag'=>1,'payment_status'=>$payment_status));
            }
            

            $getInvoice=$this->Api_model->getSingleRow(IVC_TBL,array('invoice_id'=>$invoice_id));
            $getBooking= $this->Api_model->getSingleRow(ABK_TBL, array('id'=>$getInvoice->booking_id));

            $getInvoice->booking_time= $getBooking->booking_time;
            $getInvoice->booking_date= $getBooking->booking_date;

            $getUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getInvoice->user_id));

            $getInvoice->userName= $getUser->name;
            $getInvoice->userEmail= $getUser->email_id;
            $getInvoice->address= $getUser->address;

            $get_artists= $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$getInvoice->artist_id));
            $getArt= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getInvoice->artist_id));

            $get_cat=$this->Api_model->getSingleRow(CAT_TBL, array('id'=>$get_artists->category_id));

            $getInvoice->ArtistName=$get_artists->name;
            $getInvoice->ArtistEmail=$getArt->email_id;
            $getInvoice->ArtistLocation=$get_artists->location;
            $getInvoice->categoryName=$get_cat->cat_name;

            $subject=IVE_SUB;
            $this->send_invoice($getInvoice->userEmail, $subject, $getInvoice);
            $this->send_invoice($getInvoice->ArtistEmail, $subject, $getInvoice);

            $this->api->api_message(1, PAYMENT_CONFIRM);
          }
          else
          {
            $this->Api_model->updateSingleRow(IVC_TBL,array('invoice_id'=>$invoice_id),array('final_amount'=>$final_amount,'coupon_code'=>$coupon_code,'payment_status'=>$payment_status));
                if($payment_status==3)
                {
                 $this->api->api_message(1, "Initiate payment.");
                }
                else
                {
                  $this->api->api_message(0, "Please try again later.");
                }
          }   
        }
        else
        {
          $this->api->api_message(0, "Coupon code not valid.");
        }
      }
      else
      {
         if($payment_status==1)
          {
            $this->Api_model->updateSingleRow(IVC_TBL,array('invoice_id'=>$invoice_id),array('final_amount'=>$final_amount,'coupon_code'=>$coupon_code,'flag'=>1,'payment_status'=>$payment_status, 'payment_type'=>$payment_type));

            $getInvoice=$this->Api_model->getSingleRow(IVC_TBL,array('invoice_id'=>$invoice_id));
            $getBooking= $this->Api_model->getSingleRow(ABK_TBL, array('id'=>$getInvoice->booking_id));

            $getInvoice->booking_time= $getBooking->booking_time;
            $getInvoice->booking_date= $getBooking->booking_date;

            $getUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getInvoice->user_id));

            $getInvoice->userName= $getUser->name;
            $getInvoice->userEmail= $getUser->email_id;
            $getInvoice->address= $getUser->address;

            $get_artists= $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$getInvoice->artist_id));
            $getArt= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getInvoice->artist_id));

            $get_cat=$this->Api_model->getSingleRow(CAT_TBL, array('id'=>$get_artists->category_id));

            $getInvoice->ArtistName=$get_artists->name;
            $getInvoice->ArtistEmail=$getArt->email_id;
            $getInvoice->ArtistLocation=$get_artists->location;
            $getInvoice->categoryName=$get_cat->cat_name;

            $subject=IVE_SUB;
            $this->send_invoice($getInvoice->userEmail, $subject, $getInvoice);
            $this->send_invoice($getInvoice->ArtistEmail, $subject, $getInvoice);

            $this->api->api_message(1, PAYMENT_CONFIRM);
          }
          else
          {
            $this->Api_model->updateSingleRow(IVC_TBL,array('invoice_id'=>$invoice_id),array('final_amount'=>$final_amount,'coupon_code'=>$coupon_code,'payment_status'=>$payment_status));
            if($payment_status==3)
            {
             $this->api->api_message(1, "Initiate payment.");
            }
            else
            {
              $this->api->api_message(0, "Please try again later.");
            }
          }
      } 
    }

    /*Send Email Invoice*/
    public function send_invoice($email_id, $subject, $data)
    {
      $this->load->library('email'); 
      $this->email->set_mailtype("html");
      $this->email->set_newline("\r\n");

      $from_email = SENDER_EMAIL; 
      $this->email->from($from_email, "Fab Artist"); 
      $this->email->to($email_id);
      $this->email->subject($subject); 

      $body = $this->load->view('invoice_tmp.php',$data,TRUE);
      $this->email->message($body);
      $this->email->send();
    }

    /*Get My last Booking*/
    public function getMyCurrentBooking()
    {
      $artist_id = $this->input->post('artist_id', TRUE);

      $this->checkUserStatus($artist_id);
      $getBooking= $this->Api_model->getWhereInStatus(ABK_TBL, array('artist_id'=>$artist_id),'booking_flag',array(0,1,3));
      if($getBooking)
      {
         $get_reviews=  $this->Api_model->getAllDataWhere(array('artist_id'=>$getBooking->artist_id,'status'=>1), 'rating');
          $review = array();
          foreach ($get_reviews as $get_reviews) {
            $get_user = $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$get_reviews->user_id));
            $get_reviews->name= $get_user->name;
             if($get_user->image)
            {
              $get_reviews->image= base_url().$get_user->image;
            }
            else
            {
              $get_reviews->image= base_url()."assets/images/image.png";
            }

            array_push($review, $get_reviews);
          }
          $getBooking->reviews=$review;

        $where=array('user_id'=>$getBooking->artist_id);
        $get_artists=$this->Api_model->getSingleRow(ART_TBL,$where);
        $getAdetails= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getBooking->artist_id));
        $get_cat=$this->Api_model->getSingleRow(CAT_TBL, array('id'=>$get_artists->category_id));
        if($getAdetails->image)
        {
          $getBooking->artistImage=base_url().$getAdetails->image;
        }
        else
        {
          $getBooking->artistImage=base_url()."assets/images/image.png";
        }
        $getBooking->category_name=$get_cat->cat_name;
        $getBooking->artistName=$get_artists->name;
        $getBooking->artistLocation=$get_artists->location;

        $getUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getBooking->user_id));
        $getBooking->userName= $getUser->name;
        $getBooking->address= $getUser->address;
        $getBooking->c_latitude= $getUser->latitude;
        $getBooking->c_longitude= $getUser->longitude;
        $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
        $getBooking->currency_type=$currency_setting->currency_symbol;

        $where= array('artist_id'=>$artist_id, 'status'=>1);
        $ava_rating=$this->Api_model->getAvgWhere('rating', 'rating',$where);
        if($ava_rating[0]->rating==null)
        {
          $ava_rating[0]->rating="0";
        }
        $getBooking->ava_rating=round($ava_rating[0]->rating,1);

        if($getBooking->start_time)
        {
          $getBooking->working_min= (float)round(abs($getBooking->start_time - time()) / 60,2);
        }
        else
        {
          $getBooking->working_min=0;
        }

        if($getUser->image)
        {
          $getBooking->userImage= base_url().$getUser->image;
        }
        else
        {
          $getBooking->userImage= base_url().'assets/images/image.png';
        }

        $this->api->api_message_data(1, CURRENT_BOOKING,'data' , $getBooking);
      }
      else
      {
        $this->api->api_message(0, NO_DATA);
      }
    }

    /*Get My last Booking*/
    public function getMyCurrentBookingUser()
    {
      $user_id = $this->input->post('user_id', TRUE);
      $this->checkUserStatus($user_id);

      $getBooking= $this->Api_model->getWhereInStatusResult(ABK_TBL, array('user_id'=>$user_id),'booking_flag',array(0,1,3));

      if($getBooking)
      {
        $getBookings= array();
        foreach ($getBooking as $getBooking) 
        {
        
          $get_reviews=  $this->Api_model->getAllDataWhere(array('artist_id'=>$getBooking->artist_id,'status'=>1), 'rating');
          $review = array();
          foreach ($get_reviews as $get_reviews) 
          {
            $get_user = $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$get_reviews->user_id));
            $get_reviews->name= $get_user->name;
             if($get_user->image)
              {
                $get_reviews->image= base_url().$get_user->image;
              }
              else
              {
                $get_reviews->image= base_url()."assets/images/image.png";
              }
            array_push($review, $get_reviews);
          }
          $getBooking->reviews=$review;

          $where=array('user_id'=>$getBooking->artist_id);
          $get_artists=$this->Api_model->getSingleRow(ART_TBL,$where);
          $getAdetails= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getBooking->artist_id));
          $get_cat=$this->Api_model->getSingleRow(CAT_TBL, array('id'=>$get_artists->category_id));
          if($getAdetails->image)
          {
            $getBooking->artistImage=base_url().$getAdetails->image;
          }
          else
          {
            $getBooking->artistImage=base_url()."assets/images/image.png";
          }

          $getBooking->category_name=$get_cat->cat_name;
          $getBooking->artistName=$get_artists->name;
          $getBooking->artistLocation=$get_artists->location;
          
          $getUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getBooking->user_id));
          $getBooking->userName= $getUser->name;
          $getBooking->address= $getUser->address;

           $getBooking->jobDone=$this->Api_model->getTotalWhere(ABK_TBL,array('artist_id'=>$getBooking->artist_id,'booking_flag'=>4));
          $get_artists->totalJob=$this->Api_model->getTotalWhere(ABK_TBL,array('artist_id'=>$getBooking->artist_id)); 

          if($get_artists->totalJob==0)
            {
              $getBooking->completePercentages=0;
            }
            else
            {
              $getBooking->completePercentages=round(($getBooking->jobDone*100) / $get_artists->totalJob);
            }

            $where= array('artist_id'=>$getBooking->user_id, 'status'=>1);
            $ava_rating=$this->Api_model->getAvgWhere('rating', 'rating',$where);
            if($ava_rating[0]->rating==null)
            {
              $ava_rating[0]->rating="0";
            }
            $getBooking->ava_rating=round($ava_rating[0]->rating,1);

           if($getBooking->start_time)
           {
              if($getBooking->end_time)
              {
                $getBooking->working_min= (int)round(abs($getBooking->start_time - $getBooking->end_time) / 60,2);
              }
              else
              {
                $getBooking->working_min= (float)round(abs($getBooking->start_time - time()) / 60,2);
              }
            }
            else
            {
              $getBooking->working_min=0;
            }
            if($getUser->image)
            {
              $getBooking->userImage= base_url().$getUser->image;
            }
            else
            {
              $getBooking->userImage= base_url().'assets/images/image.png';
            }

          $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
          $getBooking->currency_type=$currency_setting->currency_symbol;

           $booking_id=$getBooking->id;

            $getPrice= $this->Api_model->getSingleRow(IVC_TBL, array('booking_id'=>$booking_id,'flag'=>0));
            if($getPrice)
            {
              $getBooking->total_amount=$getPrice->total_amount;
              $getBooking->invoice_id=$getPrice->invoice_id;
              $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
              $getBooking->currency_type=$currency_setting->currency_symbol;
            }
            else
            {
              $getBooking->total_amount=0;
              $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
              $getBooking->currency_type=$currency_setting->currency_symbol;
              $getBooking->invoice_id="";
            }

         /*$getInvoice= $this->Api_model->getSingleRow('booking_invoice', array('booking_id'=>$booking_id,'flag'=>1));
         if($getInvoice)
         {
            $this->api->api_message(0, NO_DATA);
         }
         else
         {*/
          array_push($getBookings, $getBooking);
         }
          $this->api->api_message_data(1, CURRENT_BOOKING,'data' , $getBookings);
         /*}*/        
      }
      else
      {
        $this->api->api_message(0, NO_DATA);
      }
    }
  
    /*get my earning*/
    public function myEarning()
    {
      $artist_id= $this->input->post('artist_id');
      $date= date('Y-m-d');
      $today=strtotime($date);

      $this->checkUserStatus($artist_id);

      $data= array();
      $todatDay = date('D', $today);      

      $todayDay=$this->Api_model->getSumWhere('total_amount', IVC_TBL,array('artist_id'=>$artist_id,'created_at'=>$today));
      if($todayDay->total_amount==null)
      {
        $todayDay->total_amount="0";
      }
      $todayDay->day=$todatDay;
      $data[]= $todayDay;
      
      $secondDay=date('Y-m-d',(strtotime ( '-1 day' , strtotime ( $date) ) ));
      $secondDayTime=strtotime($secondDay);
      $secondDayName = date('D', $secondDayTime);

      $second=$this->Api_model->getSumWhere('total_amount', IVC_TBL,array('artist_id'=>$artist_id,'created_at'=>$secondDayTime));
      if($second->total_amount==null)
      {
        $second->total_amount="0";
      }
      $second->day=$secondDayName;
      $data[]= $second;

      $thirdDay=date('Y-m-d',(strtotime ( '-2 day' , strtotime ( $date) ) ));
      $thirdDayTime=strtotime($thirdDay);
      $thirdDayName = date('D', $thirdDayTime);

      $third=$this->Api_model->getSumWhere('total_amount', IVC_TBL,array('artist_id'=>$artist_id,'created_at'=>$thirdDayTime));
      $third->day=$thirdDayName;
      if($third->total_amount==null)
      {
        $third->total_amount="0";
      }
      $data[]= $third;

      $fourDay=date('Y-m-d',(strtotime ( '-3 day' , strtotime ( $date) ) ));
      $fourDayTime=strtotime($fourDay);
      $fourDayName = date('D', $fourDayTime);

      $four=$this->Api_model->getSumWhere('total_amount', IVC_TBL,array('artist_id'=>$artist_id,'created_at'=>$fourDayTime));
      if($four->total_amount==null)
      {
        $four->total_amount="0";
      }
      $four->day=$fourDayName;
      $data[]= $four;

      $fiveDay=date('Y-m-d',(strtotime ( '-4 day' , strtotime ( $date) ) ));
      $fiveDayTime=strtotime($fiveDay);
      $fiveDayName = date('D', $fiveDayTime);

      $five=$this->Api_model->getSumWhere('total_amount', IVC_TBL,array('artist_id'=>$artist_id,'created_at'=>$fiveDayTime));
      if($five->total_amount==null)
      {
        $five->total_amount="0";
      }
      $five->day=$fiveDayName;
      $data[]= $five;

      $sixDay=date('Y-m-d',(strtotime ( '-5 day' , strtotime ( $date) ) ));
      $sixDayTime=strtotime($sixDay);
      $sixDayName = date('D', $sixDayTime);

      $six=$this->Api_model->getSumWhere('total_amount', IVC_TBL,array('artist_id'=>$artist_id,'created_at'=>$sixDayTime));
      if($six->total_amount==null)
      {
        $six->total_amount="0";
      }
      $six->day=$sixDayName;
      $data[]= $six;

      $sevenDay=date('Y-m-d',(strtotime ( '-6 day' , strtotime ( $date) ) ));
      $sevenDayTime=strtotime($sevenDay);
      $sevenDayName = date('D', $sevenDayTime);

      $seven=$this->Api_model->getSumWhere('total_amount', IVC_TBL,array('artist_id'=>$artist_id,'created_at'=>$sevenDayTime));
      if($seven->total_amount==null)
      {
        $seven->total_amount="0";
      }
      $seven->day=$sevenDayName;
      $data[]= $seven;

      $onlineEarning=$this->Api_model->getSumWhere('artist_amount', IVC_TBL,array('artist_id'=>$artist_id,'payment_type'=>0));

      $onlineCommission=$this->Api_model->getSumWhere('category_amount', IVC_TBL,array('artist_id'=>$artist_id,'payment_type'=>0));

      $offlineEarning=$this->Api_model->getSumWhere('artist_amount', IVC_TBL,array('artist_id'=>$artist_id,'payment_type'=>1));

      $offlineCommission=$this->Api_model->getSumWhere('category_amount', IVC_TBL,array('artist_id'=>$artist_id,'payment_type'=>1));

      $earning=$this->Api_model->getSumWhere('total_amount', IVC_TBL,array('artist_id'=>$artist_id));

      $data['onlineEarning']=round($onlineEarning->artist_amount, 2);
      $data['offlineEarning']=round($offlineEarning->artist_amount, 2);

      $getCommission= $this->Api_model->getSingleRow('artist_wallet',array('artist_id'=>$artist_id));

      if($getCommission)
      {
        $data['walletAmount']= $getCommission->amount;
      }
      else
      {
        $data['walletAmount']= $data['onlineEarning'] - $data['offlineEarning'];
      }

      $data['earning']= round($earning->total_amount, 2);

      $data['jobDone']=$this->Api_model->getTotalWhere(ABK_TBL,array('artist_id'=>$artist_id,'booking_flag'=>4));

      $data['totalJob']=$this->Api_model->getTotalWhere(ABK_TBL,array('artist_id'=>$artist_id));

      $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
      $data['currency_symbol']= $currency_setting->currency_symbol;

      if($data['totalJob']==0)
      {
        $data['completePercentages']=0;
      }
      else
      {
        $data['completePercentages']=round(($data['jobDone']*100) / $data['totalJob'], 2);
      }
     
      $this->api->api_message_data(1, "Get my earning",'data' , $data);
    }


    public function myEarning1()
    {
      $artist_id= $this->input->post('artist_id');
      $date= date('Y-m-d');
      $today=strtotime($date);

      $this->checkUserStatus($artist_id);

      $data= array();
      $todatDay = date('D', $today);      

      $earningData=$this->Api_model->getMontlyRevenue1($artist_id);

      $data['chartData']=$earningData;

      $onlineEarning=$this->Api_model->getSumWhere('artist_amount', IVC_TBL,array('artist_id'=>$artist_id,'payment_type'=>0));

      $onlineCommission=$this->Api_model->getSumWhere('category_amount', IVC_TBL,array('artist_id'=>$artist_id,'payment_type'=>0));

      $offlineEarning=$this->Api_model->getSumWhere('artist_amount', IVC_TBL,array('artist_id'=>$artist_id,'payment_type'=>1));

      $offlineCommission=$this->Api_model->getSumWhere('category_amount', IVC_TBL,array('artist_id'=>$artist_id,'payment_type'=>1));

      $earning=$this->Api_model->getSumWhere('total_amount', IVC_TBL,array('artist_id'=>$artist_id));

      $data['onlineEarning']=round($onlineEarning->artist_amount, 2);
      $data['cashEarning']=round($offlineEarning->artist_amount, 2);

      $getCommission= $this->Api_model->getSingleRow('artist_wallet',array('artist_id'=>$artist_id));

      if($getCommission)
      {
        $data['walletAmount']= $getCommission->amount;
      }
      else
      {
        $data['walletAmount']= $data['onlineEarning'] - $data['offlineEarning'];
      }

      $data['totalEarning']= round($earning->total_amount, 2);

      $data['jobDone']=$this->Api_model->getTotalWhere(ABK_TBL,array('artist_id'=>$artist_id,'booking_flag'=>4));

      $data['totalJob']=$this->Api_model->getTotalWhere(ABK_TBL,array('artist_id'=>$artist_id));

      $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
      $data['currency_symbol']= $currency_setting->currency_symbol;

      if($data['totalJob']==0)
      {
        $data['completePercentages']=0;
      }
      else
      {
        $data['completePercentages']=round(($data['jobDone']*100) / $data['totalJob'], 2);
      }
     
      $this->api->api_message_data(1, "Get my earning",'data' , $data);
    }

    /*Get My Invoice*/
    public function getMyInvoice()
    {
      $user_id= $this->input->post('user_id', TRUE);
      $role= $this->input->post('role', TRUE);

      $this->checkUserStatus($user_id);

      if($role==1)
      {
        $where =array('artist_id'=> $user_id);
      }
      elseif($role==2)
      {
        $where =array('user_id'=> $user_id);
      }

      $getInvoice= $this->Api_model->getAllDataWhereOrderTwo($where,IVC_TBL);
      if($getInvoice)
      {
        $getInvoices = array();
        foreach ($getInvoice as $getInvoice)
        {
          $getBooking= $this->Api_model->getSingleRow(ABK_TBL, array('id'=>$getInvoice->booking_id));

          $getInvoice->booking_date= $getBooking->booking_date;
          $getUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getInvoice->user_id));

          $getInvoice->userName= $getUser->name;
          $getInvoice->address= $getUser->address;

          if($getUser->image)
          {
            $getInvoice->userImage= base_url().$getUser->image;
          }
          else
          {
            $getInvoice->userImage= base_url().'assets/images/image.png';
          }

          $get_artists= $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$getInvoice->artist_id));
          $getAdetails= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getInvoice->artist_id));
          $get_cat=$this->Api_model->getSingleRow(CAT_TBL, array('id'=>$get_artists->category_id));
          $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
          $getInvoice->currency_type=$currency_setting->currency_symbol;

          $getInvoice->ArtistName=$get_artists->name;
          $getInvoice->categoryName=$get_cat->cat_name;
          if($getAdetails->image)
          {
            $getInvoice->artistImage= base_url().$getAdetails->image;
          }
          else
          {
            $getInvoice->artistImage= base_url().'assets/images/image.png';
          }
          array_push($getInvoices, $getInvoice);
        }
        $this->api->api_message_data(1, MY_INVOICE,'data' , $getInvoices);
      }
      else
      {
        $this->api->api_message(0, NO_DATA);
      }  
    }

     /*Get My Invoice*/
    public function getMyHistory()
    {
      $artist_id= $this->input->post('artist_id', TRUE);
      $this->checkUserStatus($artist_id);
      $where =array('artist_id'=> $artist_id);

      $getInvoice= $this->Api_model->getAllDataWhereOrderTwo($where,IVC_TBL);
      if($getInvoice)
      {
        $getInvoices = array();
        foreach ($getInvoice as $getInvoice)
        {
          $getBooking= $this->Api_model->getSingleRow(ABK_TBL, array('id'=>$getInvoice->booking_id));

          $getInvoice->booking_time= $getBooking->booking_time;
          $getInvoice->booking_date= $getBooking->booking_date;

          $getUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getInvoice->user_id));
          $getInvoice->userName= $getUser->name;
          $getInvoice->address= $getUser->address;
          if($getUser->image)
          {
            $getInvoice->userImage= base_url().$getUser->image;
          }
          else
          {
            $getInvoice->userImage= base_url().'assets/images/image.png';
          }

          $get_artists= $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$getInvoice->artist_id));
          $getAdetails= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getInvoice->artist_id));
          $get_cat=$this->Api_model->getSingleRow(CAT_TBL, array('id'=>$get_artists->category_id));
          $getInvoice->ArtistName=$get_artists->name;
          $getInvoice->categoryName=$get_cat->cat_name;

          $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
          $getInvoice->currency_type=$currency_setting->currency_symbol;
          if($getAdetails->image)
          {
            $getInvoice->artistImage= base_url().$getAdetails->image;
          }
          else
          {
            $getInvoice->artistImage= base_url().'assets/images/image.png';
          }
          array_push($getInvoices, $getInvoice);
        }
        $this->api->api_message_data(1, MY_INVOICE,'data' , array_reverse($getInvoices));
      }
      else
      {
        $this->api->api_message(0, NO_DATA);
      }  
    }

    /*Confirm Payment*/
    public function confirm_payment()
    {
      $invoice_id =$this->input->post('invoice_id', TRUE);
      $booking_id =$this->input->post('booking_id', TRUE);

      $getInvoice= $this->Api_model->getSingleRow(IVC_TBL, array('invoice_id'=>$invoice_id,'booking_id'=>$booking_id));
      if($getInvoice)
      {
        $updateUser=$this->Api_model->updateSingleRow(IVC_TBL,array('booking_id'=>$booking_id),array('flag'=>1));

        $this->api->api_message(1, PAYMENT_CONFIRM);
      }
      else
      {
        $this->api->api_message(0, NO_DATA);
      }
    }

    public function strongID()
    {
      $id= $this->api->strongToken();

      echo $id;
    }

    /*Add to Cart*/
   public function addTocart()
   {
      $data['user_id'] =$this->input->post('user_id', TRUE);
      $data['product_id'] =$this->input->post('product_id', TRUE);
      $data['quantity'] =$this->input->post('quantity', TRUE);

      $this->checkUserStatus($data['user_id']);

      $data['created_at']= time();
      $data['updated_at']= time();
      $getId = $this->Api_model->insertGetId('product_basket', $data);

      if($getId)
      {
        $this->api->api_message(1, "Product Add on your cart successfully.");
      }
      else
      {
        $this->api->api_message(0, NO_DATA);
      }
   }

   /*Get My Cart*/
   public function getMyCart()
   {
      $user_id= $this->input->post('user_id', TRUE);

      $this->checkUserStatus($user_id);

      $get_cart= $this->Api_model->getAllDataWhere(array('user_id'=>$user_id), 'product_basket');
      if($get_cart)
      {
        $get_carts= array();
        foreach ($get_cart as $get_cart) 
        {
          $product_id=$get_cart->product_id;
          $product= $this->Api_model->getSingleRow('products', array('id'=>$product_id));
          $quantity= $get_cart->quantity;
          $price= $product->price;

          $get_cart->product_name=$product->product_name;
          $get_cart->p_rate=$product->price;
          $get_cart->product_image=$this->config->base_url().$product->product_image;
          $get_cart->product_total_price= $price*$quantity;
          $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
          $get_cart->currency_type=$currency_setting->currency_symbol;
          array_push($get_carts, $get_cart);
        }
        $this->api->api_message_data(1, "Get my Cart.",'my_cart', $get_carts);
      }  
      else
      {
        $this->api->api_message(0, NO_DATA);
      }
   }

    /*update Cart Quantity*/
   public function updateCartQuantity()
   {
      $basket_id = $this->input->post('basket_id',TRUE);
      $quantity = $this->input->post('quantity',TRUE);  
      $user_id = $this->input->post('user_id',TRUE);  

      $this->checkUserStatus($user_id);

      $table= 'product_basket';       
      $condition = array('id'=>$basket_id, 'user_id'=> $user_id); 

      $check_basket = $this->Api_model->updateSingleRow('product_basket', array('id'=>$basket_id), array('quantity'=>$quantity));
      if ($check_basket)
      {
        $this->api->api_message(1, "CART UPDATE");
      }
      else
      {
        $this->api->api_message(0, NOT_RESPONDING);
      }
   }

   /*Remove Product from Cart*/
   public function remove_product_cart()
   {
      $basket_id = $this->input->post('basket_id',TRUE);  
      $user_id = $this->input->post('user_id',TRUE);  

      $this->checkUserStatus($user_id);

      $this->Api_model->deleteRecord(array('id'=>$basket_id, 'user_id'=>$user_id), 'product_basket');
      $this->api->api_message(1, "REMOVE_CART");       
   }

   /*Get My referral Code*/
   public function getMyReferralCode()
   {
      $user_id= $this->input->post('user_id', TRUE);

      $this->checkUserStatus($user_id);

      $code=$this->Api_model->getSingleRowCloumn('referral_code',USR_TBL, array('user_id'=>$user_id));
      if($code)
      {
        $userCode['code']= $code->referral_code;
        $userCode['description']= COUPON_TEXT;

        $this->api->api_message_data(1, "Get my Referral Code.",'my_referral_code', $userCode);
      }
      else
      {
        $this->api->api_message(0, NOT_RESPONDING);
      }
   }


   /*Send message (Chat)*/
   public function sendmsg()
   {
      $image= $this->input->post('image', TRUE);
      $chat_type=$this->input->post('chat_type', TRUE);
      if(isset($chat_type))
      {
       $data['chat_type']=$this->input->post('chat_type', TRUE);
      }
      else
      {
       $data['chat_type']='1'; 
      }
      $data['user_id']= $this->input->post('user_id', TRUE);
      $data['artist_id']= $this->input->post('artist_id', TRUE);
      $data['message']= $this->input->post('message', TRUE);
      $data['send_by']= $this->input->post('send_by', TRUE);
      $data['sender_name']= $this->input->post('sender_name', TRUE);
      $data['date']= time();
      $data['send_at']= time();

       $this->load->library('upload');
  
       $config['image_library'] = 'gd2';
       $config['upload_path']   = './assets/images/'; 
       $config['allowed_types'] = 'gif|jpg|jpeg|png';
       $config['max_size']      = 10000; 
       $config['file_name']     = time();
       $config['create_thumb'] = TRUE;
       $config['maintain_ratio'] = TRUE;
       $config['width'] = 250;
       $config['height'] = 250;
       $this->upload->initialize($config);
       $updateduserimage="";
       if ( $this->upload->do_upload('image') && $this->load->library('image_lib', $config))
       {         
          $updateduserimage='assets/images/'.$this->upload->data('file_name');
       }
       else  
      {
        //  echo $this->upload->display_errors();
      }

      if($updateduserimage)
      {
        $data['image']=$updateduserimage;
      }

      $chatId=$this->Api_model->insertGetId(CHT_TBL,$data);

      if($chatId)
      {
        if($data['send_by'] == $data['artist_id'])
        {            
          $checkUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$data['artist_id']));
          $msg=$checkUser->name.':'.$data['message'];
          $this->firebase_notification($data['user_id'], "Chat" ,$msg);
        }
        elseif ($data['send_by'] == $data['user_id']) 
        {
          $checkUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$data['user_id']));
          $msg=$checkUser->name.':'.$data['message'];

          $this->firebase_notification($data['artist_id'], "Chat" ,$msg);
        }
        $get_chat= $this->Api_model->getAllDataWhere(array('user_id'=>$data['user_id'], 'artist_id'=>$data['artist_id']), CHT_TBL);

        $get_chats = array();
        foreach ($get_chat as $get_chat) 
        {
          if($get_chat->chat_type==2)
          {
            $get_chat->image= base_url().$get_chat->image;
          }

          array_push($get_chats, $get_chat);
        }

        $this->api->api_message_data(1, "Message sent successfully",'my_chat', $get_chats);
      }
      else
      {
        $this->api->api_message(0, NOT_RESPONDING);
      }
   }

   /*get conversation*/
   public function getChat()
   {
      $user_id= $this->input->post('user_id', TRUE);
      $artist_id= $this->input->post('artist_id', TRUE);

      $this->checkUserStatus($user_id);

      $get_chat= $this->Api_model->getAllDataWhere(array('user_id'=>$user_id, 'artist_id'=>$artist_id), CHT_TBL);
      if($get_chat)
      {
        $get_chats = array();
        foreach ($get_chat as $get_chat) 
        {
          if($get_chat->chat_type==2)
          {
            $get_chat->image= base_url().$get_chat->image;
          }

          array_push($get_chats, $get_chat);
        }

        $this->api->api_message_data(1, "Get my conversation.",'my_chat', $get_chats);
      }
      else
      {
        $this->api->api_message(0, "Not yet any conversation.");
      }
   }

    /*get conversation*/
   public function getChatHistoryForArtist()
   {
      $artist_id= $this->input->post('artist_id', TRUE);

      $this->checkUserStatus($artist_id);
      $where=array('artist_id'=>$artist_id);
      $get_users= $this->Api_model->getAllDataWhereDistinct($where, CHT_TBL);
      if($get_users)
      {
        $chats= array();
        foreach ($get_users as $get_users)
        {
          $chat= $this->Api_model->getSingleRowOrderBy(CHT_TBL, array('artist_id'=>$artist_id,'user_id'=>$get_users->user_id));
          $user= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$get_users->user_id));
          $chat->userName= $user->name;
          if($user->image)
          {
            $chat->userImage= base_url().$user->image;
          }
          else
          {
            $chat->userImage= base_url()."assets/images/image.png";
          }
         
          array_push($chats, $chat);
        }

        if(count($chats) > 1)
        {
          array_multisort(array_column($chats, 'send_at'), SORT_DESC, $chats);
        }

        $this->api->api_message_data(1, "Get chat history.",'my_chat', $chats);
      }
      else
      {
        $this->api->api_message(0, "Not yet any conversation.");
      }
   }

    /*get conversation*/
   public function getChatHistoryForUser()
   {
      $user_id= $this->input->post('user_id', TRUE);
      $this->checkUserStatus($user_id);
      $where=array('user_id'=>$user_id);    
      $get_users= $this->Api_model->getAllDataWhereDistinctArtist($where, CHT_TBL);

      if($get_users)
      {
        $chats= array();
        foreach ($get_users as $get_users)
        {         
          $chat= $this->Api_model->getSingleRowOrderBy(CHT_TBL, array('artist_id'=>$get_users->artist_id,'user_id'=>$user_id));
          $getAdetails= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$get_users->artist_id));
          $user= $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$get_users->artist_id));
          $chat->artistName= $user->name;
          if($getAdetails->image)
          {
            $chat->artistImage= base_url().$getAdetails->image;
          }
          else
          {
            $chat->artistImage= base_url()."assets/images/image.png";
          }     
          array_push($chats, $chat);
        }

       if(count($chats) > 1)
        {
          array_multisort(array_column($chats, 'send_at'), SORT_DESC, $chats);
        }

        $this->api->api_message_data(1, "Get chat history.",'my_chat', $chats);
      }
      else
      {
        $this->api->api_message(0, "Not yet any conversation.");
      }
   }

   public function firebase()
    {
      $mobile=$this->input->post('mobile');
      $title=$this->input->post('title');
      $msg=$this->input->post('msg');

      for($i=0;$i<count($mobile);$i++)
      {
          $user = $this->db->where('mobile_no',$mobile[$i])->get('merchant')->row();
          $deviceToken = $user->device_token;
          $mobile_sent = $mobile[$i];
          $title_sent = $title;
          $msg_sent = $msg;

          $ch = curl_init("https://fcm.googleapis.com/fcm/send");

          //Creating the notification array.
          $notification = array('title' =>$title_sent , 'text' => $msg_sent);

          //This array contains, the token and the notification. The 'to' attribute stores the token.
          $arrayToSend = array('to' => $deviceToken, 'notification' => $notification);
          //Generating JSON encoded string form the above array.
          $json = json_encode($arrayToSend);

          //Setup headers:
          $headers = array();
          $headers[] = 'Content-Type: application/json';
          

          $headers[] = 'Authorization: key= AAAAK8LacGo:APA91bHAIIcXpf5GWEhopBsOpRZhU1CuxTGSL0J_GaT0eJ8aOB78iJl65UDeAjo9kCzsZv2_Rhs13Z_wtqv43MPR6xly39yBqfpDmLW-nn535WdwpGUzd4nvR5R7i5tFL8lDyas9p0Fq'; //server key here

          //Setup curl, add headers and post parameters.
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
          curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
          curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);       

          //Send the request
          $result = curl_exec($ch );

          //Close request
          curl_close($ch);
          //return $result;           
      } 
      return $result;
    } 

    /*Firebase for notification*/
    public function firebase_notification($user_id,$title,$msg1)
    {          
      $get_data= $this->Api_model->getSingleRow(USR_TBL,array('user_id'=>$user_id));
     
      if($get_data->device_token)
      {
        if($get_data->role==1)
        {
           $API_ACCESS_KEY= ARTIST_FIREBASE_KEY;
        }
        else
        {
           $API_ACCESS_KEY= USER_FIREBASE_KEY;
        }

        $registrationIds =$get_data->device_token;

          $msg = array
              (
                'body'    => $msg1,
                'title'   => $title,
                'icon'    => 'myicon',/*Default Icon*/
                'sound'   =>  'mySound'/*Default sound*/
            );
          $fields = array
          (
              'to' => $registrationIds,
              'notification'    => $msg
          );
          $headers = array
          (
              'Authorization: key=' . $API_ACCESS_KEY,
              'Content-Type: application/json'
          );
          #Send Reponse To FireBase Server    
          $ch = curl_init();
          curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
          curl_setopt( $ch,CURLOPT_POST, true );
          curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
          curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
          curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
          curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
          $result = curl_exec($ch );
          curl_close( $ch );     
      }
    }

    public function fire_test($id)
    {
        $msg='Lorem Ipsum is simply dummy text of the printing and type setting industry.';
        $this->firebase_notification($id, "Chat" ,$msg);
    }

    /*Artist Logout*/
    public function artistLogout()
    {
      $user_id = $this->input->post('artist_id', TRUE);

      $this->checkUserStatus($user_id);
      $update= $this->Api_model->updateSingleRow(ART_TBL, array('user_id'=> $user_id), array('is_online'=>0));
       $this->api->api_message(1, "Artist logout successfully.");   
    }

    /*Artist Location Update*/
    public function updateLocation()
    {
      
      $this->form_validation->set_rules('longitude','longitude', 'trim|required');
      $this->form_validation->set_rules('latitude','latitude', 'trim|required');

        if ($this->form_validation->run() == FALSE) 
        {
          $errors=array_values($this->form_validation->error_array());
          //$this->response(["success" => false, "message" =>$errors[0]]);
          $this->api->api_message(0, $errors[0]);
        }
        else
        {
          $user_id = $this->input->post('user_id', TRUE);
          $role = $this->input->post('role', TRUE);
          $longitude = $this->input->post('longitude', TRUE);
          $latitude = $this->input->post('latitude', TRUE);

          $this->checkUserStatus($user_id);
          if($role==1)
          {
            $table=ART_TBL;
          }
          elseif($role==2)
          {
            $table=USR_TBL;

            $checkUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$user_id));
            if(!$checkUser->latitude)
            {
              $update= $this->Api_model->updateSingleRow($table, array('user_id'=> $user_id), array('latitude'=>$latitude,'longitude'=>$longitude));
            }
          }

          $update= $this->Api_model->updateSingleRow($table, array('user_id'=> $user_id), array('live_lat'=>$latitude,'live_long'=>$longitude));
          $this->api->api_message(1, "Location updated successfully.");   
      }
    }

    /*Added By Varun*/
    function post_job()
    {
        $data['user_id'] = $this->input->post('user_id', TRUE); 
        $price = $this->input->post('price', TRUE); 
        $data['title'] = $this->input->post('title', TRUE); 
        $data['description'] = $this->input->post('description', TRUE);
        $data['category_id'] = $this->input->post('category_id', TRUE);
        $data['address'] = $this->input->post('address', TRUE);
        $data['lati'] = $this->input->post('lati', TRUE);
        $data['longi'] = $this->input->post('longi', TRUE);
        $avtar = $this->input->post('avtar', TRUE);

        $this->checkUserStatus($data['user_id']);

        if(isset($price))
        {
          $data['price']=$price;
        }

        $job_id = $this->api->random_string('alnum',6);
        $data['job_id'] = strtoupper($job_id);
        $table = 'post_job';

        $this->load->library('upload');

        $config['image_library'] = 'gd2';
        $config['upload_path']   = './assets/images/'; 
        $config['allowed_types'] = 'gif|jpg|jpeg|png';
        $config['max_size']      = 100000; 
        $config['file_name']     = time();
        $config['create_thumb'] = TRUE;
        $config['maintain_ratio'] = TRUE;
        $config['width'] = 250;
        $config['height'] = 250;
        $this->upload->initialize($config);
        $ProfileImage="";
        if ( $this->upload->do_upload('avtar') && $this->load->library('image_lib', $config))
        {         
          $ProfileImage='assets/images/'.$this->upload->data('file_name');
        }
        else  
        {

        }

        if($ProfileImage)
        {
          $data['avtar']=$ProfileImage;
        }

        $user_id = $this->Api_model->insertGetId($table, $data);

        if ($user_id) 
        {
           $this->api->api_message(1, "Job added successfully."); 
        }
        else
        {
           $this->api->api_message(0, "Not added."); 
        }      
    } 

    function edit_post_job()
    {
      $job_id = $this->input->post('job_id',TRUE);
      $title = $this->input->post('title',TRUE);
      $price = $this->input->post('price',TRUE);
      $description = $this->input->post('description', TRUE);
      $category_id = $this->input->post('category_id', TRUE);
      $address = $this->input->post('address', TRUE);
      $lati = $this->input->post('lati', TRUE);
      $longi = $this->input->post('longi', TRUE);
      $avtar = $this->input->post('avtar', TRUE);

      $table= 'post_job';       
      $condition = array('job_id'=>$job_id);

      if ($job_id!=''||$job_id!=NULL) 
      {
      
        $check_job = $this->Api_model->getSingleRow($table, $condition);
        if ($check_job)
         {
            $this->load->library('upload');
            $config['image_library'] = 'gd2';
            $config['upload_path']   = './assets/images/'; 
            $config['allowed_types'] = 'gif|jpg|jpeg|png';
            $config['max_size']      = 100000; 
            $config['file_name']     = time();
            $config['create_thumb'] = TRUE;
            $config['maintain_ratio'] = TRUE;
            $config['width'] = 250;
            $config['height'] = 250;
            $this->upload->initialize($config);
             $profileimage="";
             if ( $this->upload->do_upload('avtar'))
             {
              $profileimage='assets/images/'.$this->upload->data('file_name'); 
             }
             else   
             {

             }
            if($profileimage)
            {
              $data['avtar']= $profileimage;
            }
           
            $data['title']=isset($title) ? $title: $check_job->title;
            $data['price']=isset($price) ? $price: $check_job->price;
            $data['description']=isset($description) ? $description: $check_job->description;
            $data['category_id']=isset($category_id) ? $category_id: $check_job->category_id;
            $data['address']=isset($address) ? $address: $check_job->address;
            $data['lati']=isset($lati) ? $lati: $check_job->lati;
            $data['longi']=isset($longi) ? $longi: $check_job->longi;
            $data['updated_at'] =  date('Y-m-d H:i:s');
        
            $this->Api_model->updateSingleRow($table, array('job_id'=>$job_id), $data);      

            $getappliedArtist=  $this->Api_model->getAllDataWhere(array('job_id'=>$job_id), AJB_TBL);
            if($getappliedArtist)
            {
              foreach ($getappliedArtist as $getappliedArtist) 
              {
                 $checkUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getappliedArtist->artist_id));
                $msg=$job_id.': has updated. Please check the changes.';
                $this->firebase_notification($checkUser->user_id, "Booking" ,$msg);
              }
            }
            $this->api->api_message(1, 'Job Updated Successfully.');
         }
         else
         {
            $this->api->api_message(0, 'User not register');
         }     
      }
      else
     {
         $this->api->api_message(0, "Job id not available."); 
     }   
    }

    public function get_all_job()
    {
        $artist_id = $this->input->post('artist_id', TRUE); 
        $get_jobs = $this->Api_model->getAllJobNotAppliedByArtist($artist_id);

        if(empty($get_jobs))
        {
          $this->api->api_message(0, 'No jobs available.');
        }
        else
        {
          
          $job_list = array();
          foreach ($get_jobs as $get_jobs) 
          {
            $get_jobs->avtar= base_url().$get_jobs->avtar;
            $table= 'user';       
            $condition = array('user_id'=>$get_jobs->user_id);  
            $user = $this->Api_model->getSingleRow($table, $condition);  
            $user->image= base_url().$user->image;

            $table= 'category';       
            $condition = array('id'=>$get_jobs->category_id); 
            $cate = $this->Api_model->getSingleRow($table, $condition);  
            $get_jobs->category_name = $cate->cat_name;

            $commission_setting= $this->Api_model->getSingleRow('commission_setting',array('id'=>1));

            $get_jobs->commission_type = $commission_setting->commission_type;
            $get_jobs->flat_type = $commission_setting->flat_type;
            if($commission_setting->commission_type==0)
            {
              $get_jobs->category_price = $cate->price;
            }
            elseif($commission_setting->commission_type==1)
            {
              if($commission_setting->flat_type==2)
              {
                $get_jobs->category_price = $commission_setting->flat_amount;
              }
              elseif ($commission_setting->flat_type==1) 
              {
                $get_jobs->category_price = $commission_setting->flat_amount;
              }
            }

            $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
            $get_jobs->currency_symbol= $currency_setting->currency_symbol;

            $get_jobs->user_image = $user->image;
            $get_jobs->user_name = $user->name;
            $get_jobs->user_address = $user->address;
            $get_jobs->user_mobile = $user->mobile;
              
            array_push($job_list, $get_jobs);
          }
        $this->api->api_message_data(1, 'All Jobs Found','data' , $job_list);
      }
    }

    public function get_all_job_user()
    {
       $user_id = $this->input->post('user_id', TRUE); 
       $get_jobs=$this->Api_model->getAllDataWhereoderBy(array('user_id'=>$user_id, 'status !='=>'4'),'post_job');
        if($get_jobs)
        {
          $job_list = array();
          foreach ($get_jobs as $get_jobs) 
          {
            $get_jobs->avtar= base_url().$get_jobs->avtar;

            $job= $this->Api_model->getWhereInStatusResult('applied_job',array('job_id'=>$get_jobs->job_id),'status', array(0,1,5));

            if($job)
            {
              $get_jobs->is_edit =0;
            }
            else
            {
              $get_jobs->is_edit =1;
            }

            $table= 'category';       
            $condition = array('id'=>$get_jobs->category_id); 
            $cate = $this->Api_model->getSingleRow($table, $condition);  
            $get_jobs->category_name = $cate->cat_name;
            $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
            $get_jobs->currency_symbol= $currency_setting->currency_symbol;
            $get_jobs->category_price = $cate->price;
            array_push($job_list, $get_jobs);
          }
          $this->api->api_message_data(1, 'All Jobs Found','data' , $job_list);
        }
        else
        {
          $this->api->api_message(0, 'No jobs available.');
        }
    }

    public function applied_job()
    {
        $price = $this->input->post('price', TRUE); 
        $user_id = $this->input->post('user_id', TRUE); 
        $artist_id = $this->input->post('artist_id', TRUE); 
        $job_id = $this->input->post('job_id', TRUE);
        $description = $this->input->post('description', TRUE);

        $table = 'applied_job';
        if(!$check=$this->Api_model->check_applied_job($artist_id, $job_id))
        {
          $checkArtist= $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$artist_id));
          if(!$checkArtist)
          {
            $this->api->api_message(0, PLZ_UP_PRF);
            exit();
          }
        
          if(isset($price))
          {
             $data['price']=$price;
          }
          $data['user_id']= $user_id;
          $data['artist_id']= $artist_id;
          $data['job_id']= $job_id;
          $data['description']= $description;

          $id = $this->Api_model->insertGetId($table, $data);
          if ($id) 
          {
            $checkUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$artist_id));
            $msg='Hey, '.$checkUser->name.' applied on your job.';
            $this->firebase_notification($checkUser->user_id, "Job" ,$msg);

            $this->api->api_message(1, "Job applied successfully."); 
          }
          else
          {
            $this->api->api_message(0, "Failed."); 
          }   
        }
        else
        {
         $this->api->api_message(0, 'Allready applied');
        }      
    }

     public function get_applied_job_by_id()
    {
       $job_id = $this->input->post('job_id', TRUE); 
       $get_jobs=$this->Api_model->getAllDataWhereoderBy(array('job_id'=>$job_id),'applied_job');

        if(empty($get_jobs))
        {
          $this->api->api_message(0, 'No jobs available.');
        }
        else
        {
          
          $job_list = array();
          foreach ($get_jobs as $get_jobs) 
          {
            $table= 'artist';       
            $condition = array('user_id'=>$get_jobs->artist_id);  
            $user = $this->Api_model->getSingleRow($table, $condition);  
            $user->image= base_url().$user->image;

              $get_jobs->artist_image = $user->image;
              $get_jobs->artist_name = $user->name;

              $table= 'category';       
                  $condition = array('id'=>$user->category_id); 
                  $cate = $this->Api_model->getSingleRow($table, $condition);  
              $get_jobs->category_name = $cate->cat_name;
              $commission_setting= $this->Api_model->getSingleRow('commission_setting',array('id'=>1));
              $get_jobs->commission_type = $commission_setting->commission_type;
              $get_jobs->flat_type = $commission_setting->flat_type;
              if($commission_setting->commission_type==0)
              {
                $get_jobs->category_price = $cate->price;
              }
              elseif($commission_setting->commission_type==1)
              {
                if($commission_setting->flat_type==2)
                {
                  $get_jobs->category_price = $commission_setting->flat_amount;
                }
                elseif ($commission_setting->flat_type==1) 
                {
                  $get_jobs->category_price = $commission_setting->flat_amount;
                }
              }

            $table1= 'user';       
            $condition = array('user_id'=>$get_jobs->artist_id);  
            $artist = $this->Api_model->getSingleRow($table1, $condition);  

            $get_jobs->artist_address = $artist->address;
            $get_jobs->artist_mobile = $artist->mobile;
            $get_jobs->artist_email = $artist->email_id;

            $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
            $get_jobs->currency_symbol= $currency_setting->currency_symbol;
            $ava_rating=$this->Api_model->getAvgWhere('rating', 'rating',array('artist_id'=>$get_jobs->artist_id));
            if($ava_rating[0]->rating==null)
            {
              $ava_rating[0]->rating="0";
            }
            $get_jobs->ava_rating= round($ava_rating[0]->rating, 2);
            array_push($job_list, $get_jobs);
          }
          $this->api->api_message_data(1, 'All Jobs Found','data' , $job_list);
        }
    }

    public function job_status_user()
    {
      $aj_id = $this->input->post('aj_id', TRUE); 
      $status = $this->input->post('status', TRUE); 
      $job_id = $this->input->post('job_id', TRUE); 

      $table = 'applied_job';
      $condition = array('aj_id'=>$aj_id);

      $job = $this->Api_model->getSingleRow($table, $condition); 

     if ($status == '1') 
      {
        $update= $this->Api_model->updateSingleRow('applied_job', array('aj_id'=> $aj_id), array('status'=>$status));
        
       $this->api->api_message(1, "Job confirm successfully.");   
       $this->firebase_notification($job->artist_id, 'Job Status' ,'Your request is confirm.');
      }
      elseif($status == '2')
      {
        if ($job->status == '1') 
        {
          $update= $this->Api_model->updateSingleRow('applied_job', array('aj_id'=> $aj_id), array('status'=>$status));
          $update1= $this->Api_model->updateJob('applied_job', array('aj_id !='=> $aj_id, 'job_id' =>$job_id), '3');
          $this->Api_model->updateSingleRow('post_job', array('job_id'=> $job->job_id), array('status'=>$status));
          $this->api->api_message(1, "Job Complete successfully.");   
          $this->firebase_notification($job->artist_id, 'Job Status' ,'Job completed.');
        }
        else
        {
          $this->api->api_message(0, "Please confirm job first");   
        }  
      }
      elseif($status == '3')
      {
        if ($job->status != '2') 
        {
          $update= $this->Api_model->updateSingleRow('applied_job', array('aj_id'=> $aj_id), array('status'=>$status));
          $this->api->api_message(1, "Job Rejected successfully.");   
          $this->firebase_notification($job->artist_id, 'Job Status' ,'Job Rejected.');
        }
        else
        {
          $this->api->api_message(0, "Failed"); 
        }
      }
      else
      {
        $this->api->api_message(0, "Failed");   
      }  
    }

    public function get_applied_job_artist()
    {
        $artist_id = $this->input->post('artist_id', TRUE); 
        $get_jobs=$this->Api_model->getAllDataWhereoderBy(array('artist_id'=>$artist_id,'status !='=>'4'),'applied_job');
        if(empty($get_jobs))
        {
          $this->api->api_message(0, 'No jobs available.');
        }
        else
        { 
          $job_list = array();
          foreach ($get_jobs as $get_jobs) 
          {
            $table= 'user';       
            $condition = array('user_id'=>$get_jobs->user_id);  
            $user = $this->Api_model->getSingleRow($table, $condition);  
            $job = $this->Api_model->getSingleRow('post_job', array('job_id'=>$get_jobs->job_id));  
            $cat= $this->Api_model->getSingleRow(CAT_TBL, array('id'=>$job->category_id));  
            $user->image= base_url().$user->image;
            $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
            $get_jobs->currency_symbol= $currency_setting->currency_symbol;
            $get_jobs->category_name = $cat->cat_name;
            $get_jobs->user_image = $user->image;
            $get_jobs->user_name = $user->name;
            $get_jobs->user_address = $job->address;
            $get_jobs->user_mobile = $user->mobile;
            $get_jobs->user_email = $user->email_id;
              
            array_push($job_list, $get_jobs);
          }
          $this->api->api_message_data(1, 'Applied Jobs Found','data' , $job_list);
        }
    }

    public function job_status_artist()
    {
        $aj_id = $this->input->post('aj_id', TRUE); 
        $status = $this->input->post('status', TRUE); 

        $table = 'applied_job';
        $condition = array('aj_id'=>$aj_id);

        $job = $this->Api_model->getSingleRow($table, $condition); 
 
        if($status == '3')
        {
          if ($job->status != '2') 
          {
            $update= $this->Api_model->updateSingleRow('applied_job', array('aj_id'=> $aj_id), array('status'=>$status));
            $this->Api_model->updateSingleRow('post_job', array('job_id'=> $job->job_id), array('status'=>'0'));
            $this->api->api_message(1, "Job Rejected successfully.");   
            $this->firebase_notification($job->artist_id, 'Job Status' ,'Job Rejected.');
          }
          else
          {
            $this->api->api_message(0, "Failed"); 
          } 
        }
        else
        {
          $this->api->api_message(0, "Failed");   
        } 
    }

    public function deletejob()
    {
      $job_id = $this->input->post('job_id', TRUE); 
      $status = $this->input->post('status', TRUE); 

      $job= $this->Api_model->getWhereInStatusResult('applied_job',array('job_id'=>$job_id),'status', array(5));
      if(empty($job)) 
      {

        $getappliedArtist=  $this->Api_model->getAllDataWhere(array('job_id'=>$job_id), AJB_TBL);
        if($getappliedArtist)
        {
          foreach ($getappliedArtist as $getappliedArtist) 
          {
             $checkUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getappliedArtist->artist_id));
            $msg=$job_id.': has deleted by user.';
            $this->firebase_notification($checkUser->user_id, "Booking" ,$msg);
          }
        }

        $this->Api_model->updateSingleRow('post_job', array('job_id'=> $job_id), array('status'=>$status));
        $this->api->api_message(1, "Job deleted successfully.");   
      }
      else
      {
        $this->api->api_message(0, "Currently artist working on this job."); 
      }    
    }

   /*Case 1 accept booking 2 start booking 3 end booking*/
    public function appointment_operation()
    {
      $request =$this->input->post('request', TRUE);
      $appointment_id =$this->input->post('appointment_id', TRUE);
      $user_id =$this->input->post('user_id', TRUE);

      $this->checkUserStatus($user_id);

      switch ($request) 
      {
        case 1:
           $this->accept_appointment($appointment_id);
        break;

        case 2:
            $this->reject_appointment($appointment_id);
        break;

        case 3:
            $this->complete_appointment($appointment_id);
        break;    
        case 4:
            $this->decline_appointment($appointment_id);
        break;
        default:
         $this->api->api_message(0, TRY_AGAIN);
      }
    }

    /*Accept Booking*/
    public function accept_appointment($appointment_id)
    {
      $data['status'] =1;

      $getBooking= $this->Api_model->getSingleRow(APP_TBL, array('id'=>$appointment_id));
      if($getBooking)
      {
        $updateBooking=$this->Api_model->updateSingleRow(APP_TBL,array('id'=>$appointment_id),$data);

        if($updateBooking)
        {
          $checkUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getBooking->artist_id));
          $msg=$checkUser->name.': has accepted your appointment.';
          $this->firebase_notification($getBooking->user_id, "Appointment" ,$msg);

          $this->api->api_message(1, "Appointment accepted successfully.");
        }
        else
        {
          $this->api->api_message(0, TRY_AGAIN);
        }
      }
      else
      {
        $this->api->api_message(0, TRY_AGAIN);
      }
    }

    /*Reject Booking*/
    public function reject_appointment($appointment_id)
    {
      $data['status'] =2;

      $getBooking= $this->Api_model->getSingleRow(APP_TBL, array('id'=>$appointment_id));
      if($getBooking)
      {
        $updateBooking=$this->Api_model->updateSingleRow(APP_TBL,array('id'=>$appointment_id),$data);

        if($updateBooking)
        {
          $checkUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getBooking->artist_id));
          $msg=$checkUser->name.': has rejected your appointment.';
          $this->firebase_notification($getBooking->user_id, "Appointment" ,$msg);

          $this->api->api_message(1, "Appointment rejected successfully.");
        }
        else
        {
          $this->api->api_message(0, TRY_AGAIN);
        }
      }
      else
      {
        $this->api->api_message(0, TRY_AGAIN);
      }
    }

     /*Reject Booking*/
    public function complete_appointment($appointment_id)
    {
      $data['status'] =3;

      $getBooking= $this->Api_model->getSingleRow(APP_TBL, array('id'=>$appointment_id));
      if($getBooking)
      {
        $updateBooking=$this->Api_model->updateSingleRow(APP_TBL,array('id'=>$appointment_id),$data);
        if($updateBooking)
        {
          $checkUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getBooking->artist_id));
          $msg=$checkUser->name.': has completed your appointment.';
          $this->firebase_notification($getBooking->user_id, "Appointment" ,$msg);

          $this->api->api_message(1, "Appointment completed successfully.");
        }
        else
        {
          $this->api->api_message(0, TRY_AGAIN);
        }
      }
      else
      {
        $this->api->api_message(0, TRY_AGAIN);
      }
    }


     /*Reject Booking*/
    public function decline_appointment($appointment_id)
    {
      $data['status'] =4;

      $getBooking= $this->Api_model->getSingleRow(APP_TBL, array('id'=>$appointment_id));
      if($getBooking)
      {
        $updateBooking=$this->Api_model->updateSingleRow(APP_TBL,array('id'=>$appointment_id),$data);

        if($updateBooking)
        {
          $checkUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getBooking->artist_id));
          $msg=$checkUser->name.': has decline your appointment.';
          $this->firebase_notification($getBooking->user_id, "Appointment" ,$msg);

          $this->api->api_message(1, "Appointment decline successfully.");
        }
        else
        {
          $this->api->api_message(0, TRY_AGAIN);
        }
      }
      else
      {
        $this->api->api_message(0, TRY_AGAIN);
      }
    }


    public function add_favorites()
    {
      $user_id=$this->input->post('user_id', TRUE);
      $artist_id=$this->input->post('artist_id', TRUE);
      $data['user_id']= $user_id;
      $data['artist_id']= $artist_id;

      if($this->Api_model->check_favorites($user_id,$artist_id))
      {
        $this->api->api_message(0, "Already Favorites.");
      }
      else
      {
        $this->Api_model->add_favorites($data);
        $this->api->api_message(1, "Add Favorites Successfully.");
      }
    }

    public function remove_favorites()
    {
        $user_id=$this->input->post('user_id', TRUE);
        $artist_id=$this->input->post('artist_id', TRUE);
        $this->Api_model->remove_favorites($user_id, $artist_id);
        $this->api->api_message(1, "Remove Favorites Successfully.");
    }

    public function getLocationArtist()
    {
      $artist_id=$this->input->post('artist_id', TRUE);
      $role='1';

        $checkUser = $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$artist_id));
       $user_latlongs = array();
       if ($checkUser) 
       {
         $user_latlongs['lati'] =  $checkUser->live_lat;
         $user_latlongs['longi'] =  $checkUser->live_long;

         $this->api->api_message_data(1, 'Lat Longs','data' , $user_latlongs);
       }
       else
       {
          $this->api->api_message(0, "Failed");
       } 
    }

  public function changeCommissionArtist()
  {
    $artist_id =$this->input->post('artist_id', TRUE);
    $data['artist_commission_type'] =$this->input->post('artist_commission_type', TRUE);
    $this->checkUserStatus($artist_id);

    $checkUser = $this->Api_model->getSingleRow('user', array('user_id'=>$artist_id));

    if($checkUser)
    {
      $updateUser= $this->Api_model->updateSingleRow(ART_TBL,array('user_id'=>$artist_id),$data); 
      $this->api->api_message(1, 'Commission type change successfully.');
    }
    else
    {
       $this->api->api_message(0, TRY_AGAIN);
    } 
  }

  public function getCurrencyType()
  {
    $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
    $data['currency_type']= $currency_setting->currency_symbol;
    $this->api->api_message_data(1, 'Get currency type','currency_type' , $data);
  }


  /*Start Job*/
  public function startJob()
  {
    $job_id = $this->input->post('job_id', TRUE);
    $data['user_id'] = $this->input->post('user_id', TRUE);
    $data['artist_id'] = $this->input->post('artist_id', TRUE);
    $data['price'] = $this->input->post('price', TRUE);
    $date_string= date('Y-m-d h:i a');
    $data['time_zone']= $this->input->post('timezone', TRUE);
    $data['booking_date'] = date('Y-m-d');
    $data['booking_time'] = date('h:i a');
    $data['start_time']=time();
    $data['created_at']=time();
    $data['booking_type']=2;
    $data['booking_flag']=3;
    $data['updated_at']=time();
    $data['booking_timestamp']=strtotime($date_string);

    $this->checkUserStatus($data['user_id']);
    $this->checkUserStatus($data['artist_id']);

    $checkArtist= $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$data['artist_id'],'booking_flag'=>1));

      if($checkArtist)
      {
        $this->api->api_message(0, "Artist Busy with another client. Please try after sometime.");
        exit();
      }

    $checkJob= $this->Api_model->getSingleRow(AJB_TBL, array('job_id'=>$job_id,'status'=>1,'artist_id'=>$data['artist_id']));
    if($checkJob)
    {
      $data['job_id']= $job_id;
      $this->Api_model->updateSingleRow(AJB_TBL,array('job_id'=>$job_id,'artist_id'=>$data['artist_id']),array('status'=>5));

      $checkArtist= $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$data['artist_id'],'booking_flag'=>1));
      if($checkArtist)
      {
        $this->api->api_message(0, "Artist Busy with another client. Please try after sometime.");
        exit();
      }
      
      $getArtist= $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$data['artist_id']));

      $category_id= $getArtist->category_id;
      $category= $this->Api_model->getSingleRow(CAT_TBL, array('id'=>$category_id));

      $commission_setting= $this->Api_model->getSingleRow('commission_setting',array('id'=>1));

      $data['commission_type']=$commission_setting->commission_type;
      $data['flat_type']=$commission_setting->flat_type;
      if($commission_setting->commission_type==0)
      {
        $data['category_price']= $category->price;
      }
      elseif($commission_setting->commission_type==1)
      {
        if($commission_setting->flat_type==2)
        {
          $data['category_price']= $commission_setting->flat_amount;
        }
        elseif ($commission_setting->flat_type==1) 
        {
          $data['category_price']= $commission_setting->flat_amount;
        }
      }
      
      $appId = $this->Api_model->insertGetId(ABK_TBL, $data);
      if($appId)
      {
        $checkUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$data['user_id']));
        $msg=$checkUser->name.': start your job '.$date_string;
        $this->firebase_notification($data['artist_id'], "Start Job" ,$msg);

        $dataNotification['user_id']= $data['artist_id'];
        $dataNotification['title']= "Start Job";
        $dataNotification['msg']= $msg;
        $dataNotification['type']= "Individual";
        $dataNotification['created_at']=time(); 
        $this->Api_model->insertGetId(NTS_TBL,$dataNotification);

        $updateUser=$this->Api_model->updateSingleRow(ART_TBL,array('user_id'=>$data['artist_id']),array('booking_flag'=>1));
        $this->api->api_message(1, BOOK_APP);
      }
      else
      {
        $this->api->api_message(0, TRY_AGAIN);
      }
    }
    else
    {
      $this->api->api_message(0, "No job found in starting state. Please try after sometime.");
      exit();
    }  
  }


  /*Start Job*/
  public function startAppointment()
  {
    $appointment_id = $this->input->post('appointment_id', TRUE);
    $data['user_id'] = $this->input->post('user_id', TRUE);
    $data['artist_id'] = $this->input->post('artist_id', TRUE);
    $data['price'] = $this->input->post('price', TRUE);
    $date_string= date('Y-m-d h:i a');
    $data['time_zone']= $this->input->post('timezone', TRUE);
    $data['booking_date'] = date('Y-m-d');
    $data['booking_time'] = date('h:i a');
    $data['start_time']=time();
    $data['created_at']=time();
    $data['booking_type']=1;
    $data['booking_flag']=3;
    $data['updated_at']=time();
    $data['booking_timestamp']=strtotime($date_string);

    $this->checkUserStatus($data['user_id']);
    $this->checkUserStatus($data['artist_id']);

    $checkJob= $this->Api_model->getSingleRow(APP_TBL, array('id'=>$appointment_id,'status'=>1,'artist_id'=>$data['artist_id']));
    if($checkJob)
    {
      $data['job_id']= $appointment_id;
      $this->Api_model->updateSingleRow(APP_TBL,array('id'=>$appointment_id),array('status'=>5));

      $checkArtist= $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$data['artist_id'],'booking_flag'=>1));
      if($checkArtist)
      {
        $this->api->api_message(0, "Artist Busy with another client. Please try after sometime.");
        exit();
      }
      
      $getArtist= $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$data['artist_id']));

      $category_id= $getArtist->category_id;
      $category= $this->Api_model->getSingleRow(CAT_TBL, array('id'=>$category_id));

      $commission_setting= $this->Api_model->getSingleRow('commission_setting',array('id'=>1));

      $data['commission_type']=$commission_setting->commission_type;
      $data['flat_type']=$commission_setting->flat_type;
      if($commission_setting->commission_type==0)
      {
        $data['category_price']= $category->price;
      }
      elseif($commission_setting->commission_type==1)
      {
        if($commission_setting->flat_type==2)
        {
          $data['category_price']= $commission_setting->flat_amount;
        }
        elseif ($commission_setting->flat_type==1) 
        {
          $data['category_price']= $commission_setting->flat_amount;
        }
      }
      
      $appId = $this->Api_model->insertGetId(ABK_TBL, $data);
      if($appId)
      {
        $checkUser= $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$data['user_id']));
        $msg=$checkUser->name.': start your job '.$date_string;
        $this->firebase_notification($data['artist_id'], "Start Job" ,$msg);

        $dataNotification['user_id']= $data['artist_id'];
        $dataNotification['title']= "Start Job";
        $dataNotification['msg']= $msg;
        $dataNotification['type']= "Individual";
        $dataNotification['created_at']=time(); 
        $this->Api_model->insertGetId(NTS_TBL,$dataNotification);

        $updateUser=$this->Api_model->updateSingleRow(ART_TBL,array('user_id'=>$data['artist_id']),array('booking_flag'=>3));
        $this->api->api_message(1, BOOK_APP);
      }
      else
      {
        $this->api->api_message(0, TRY_AGAIN);
      }
    }
    else
    {
      $this->api->api_message(0, "No Appointments found in starting state. Please try after sometime.");
      exit();
    }  
  }

  /*Mark as complete*/
  public function jobComplete()
  {
    $user_id=$this->input->post('user_id', TRUE);
    $job_id=$this->input->post('job_id', TRUE);

    $this->checkUserStatus($user_id);

    $job= $this->Api_model->getWhereInStatusResult('applied_job',array('job_id'=>$job_id),'status', array(5));
    if(empty($job))
    {
      $jobs=$this->Api_model->getWhereInStatusResult('applied_job',array('job_id'=>$job_id),'status', array(0,1));
      foreach ($jobs as $jobs) 
      {
        $this->Api_model->updateSingleRow('applied_job', array('aj_id'=> $jobs->aj_id), array('status'=>3));
      }
      $this->Api_model->updateSingleRow('post_job', array('job_id'=> $job_id), array('status'=>2));
      $this->api->api_message(1, "Job finished successfully.");
    }
    else
    {
      $this->api->api_message(0, "Currently artist working on this job.");
    }
  }

  /*Request For Wallet Amount*/
  public function walletRequest()
  {
    $user_id=$this->input->post('user_id', TRUE);

    $this->checkUserStatus($user_id);

    $data['artist_id']= $user_id;
    $data['created_at']= time();
    $reqGet= $this->Api_model->getSingleRow("wallet_request", array('artist_id'=>$user_id, 'status'=>0));
    if($reqGet)
    {
      $this->api->api_message(0, TRY_AGAIN);
      exit();
    }

    $id=$this->Api_model->insertGetId("wallet_request",$data);
    if($id)
    {
      $this->api->api_message(1, "Thanks for the request. We will approved your request");
    }
    else
    {
      $this->api->api_message(0, TRY_AGAIN);
    }
  }

   /*PayPal Payment gateway*/
  public function paypal()
  {
    $pkf_name=$this->input->get('pkf_name');
    $amount=$this->input->get('amount');
    $userId=$this->input->get('userId');
    $pkgId=$this->input->get('pkgId');    
    $data = array('pkgName' =>$pkf_name ,'amount' =>$amount,'userId'=>$userId,'pkgId' =>$pkgId );
    $this->load->view('paypal', $data);
  }

   public function paypal2()
  {
    // $pkf_name=$this->input->get('pkf_name');
    $amount=$this->input->get('amount');
    $userId=$this->input->get('userId');
    // $pkgId=$this->input->get('pkgId');    
    $data = array('amount' =>$amount,'userId'=>$userId);
    $this->load->view('paypal2', $data);
  }

  public function payusuccess()
  {
    $this->load->view('payusuccess1');
  }

  public function payufailure()
  {
    $this->load->view('payufailure1');
  }

  function makeProArtist(){
    
      $user_id = $this->input->get_post('user_id',TRUE);
      $chkUser=$this->Api_model->getSingleRow('user', array('user_id' =>$user_id));
      if($chkUser->role == 1){
      $data['user_type']='2';
      $this->Api_model->updateSingleRow('user',array('user_id'=>$user_id), $data);
      $this->api->api_message(1, 'Pro Artist');
    }
  else{
    $this->api->api_message(0, NO_DATA);
  }
  }

   /*Get My favourite*/
   public function getMyFavourite()
   {
      $user_id=$this->input->post('user_id', TRUE);
      $longitude_app=$this->input->post('longitude_app', TRUE);
      $latitude_app=$this->input->post('latitude_app', TRUE);
      $this->checkUserStatus($user_id);

     function distance($lat1, $lon1, $lat2, $lon2) 
      {
        try
        {
          $theta = $lon1 - $lon2;
          $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
          $dist = acos($dist);
          $dist = rad2deg($dist);
          $miles = $dist * 60 * 1.1515;
          return ($miles * 1.609344);
        }

        catch(Exception $e)
        {
          return (0.0);
        }
      }

      $getFavourite=  $this->Api_model->getAllDataWhere(array('user_id'=>$user_id), 'favourite');
      if($getFavourite)
      {
        $artists= array();
        foreach ($getFavourite as $getFavourite) 
        {
          $artist=$this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$getFavourite->artist_id));

            $jobDone=$this->Api_model->getTotalWhere(ABK_TBL,array('artist_id'=>$artist->user_id,'booking_flag'=>4));

            $artist->total=$this->Api_model->getTotalWhere(ABK_TBL,array('artist_id'=>$artist->user_id,));
            if($artist->total==0)
            {
              $artist->percentages=0;
            }
            else
            {
                $artist->percentages=round(($jobDone*100) / $artist->total);
            }
            $artist->jobDone=$jobDone;

         $get_cat=$this->Api_model->getSingleRow(CAT_TBL, array('id'=>$artist->category_id));
         $getUser=$this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$artist->user_id));
          if($getUser->image)
          {
            $artist->image=base_url().$getUser->image;
          }
          else
          {
            $artist->image=base_url()."assets/images/image.png";   
          }
          if($get_cat)
          {
            $artist->category_name=$get_cat->cat_name;//
          }
          else
          {
            $artist->category_name="No Category";//
          }
          
          $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));

          $artist->currency_type=$currency_setting->currency_symbol;
          $commission_setting= $this->Api_model->getSingleRow('commission_setting',array('id'=>1));
          $artist->commission_type=$commission_setting->commission_type;
          $artist->flat_type=$commission_setting->flat_type;
          if($commission_setting->commission_type==0)
          {
            $artist->category_price=$get_cat->price;
          }
          elseif($commission_setting->commission_type==1)
          {
            if($commission_setting->flat_type==2)
            {
              $artist->category_price = $commission_setting->flat_amount;
            }
            elseif ($commission_setting->flat_type==1) 
            {
              $artist->category_price = $commission_setting->flat_amount;
            }
          }
          
          $distance =distance($latitude_app,$longitude_app,$artist->latitude,$artist->longitude);
          $distance=round($distance);
          $distance_str="$distance";
          $artist->distance=$distance_str;
          $where= array('artist_id'=>$artist->user_id);
          $ava_rating=$this->Api_model->getAvgWhere('rating', 'rating',$where);
          if($ava_rating[0]->rating==null)
          {
            $ava_rating[0]->rating="0";
          }
          $artist->ava_rating= round($ava_rating[0]->rating, 2);

          $check_fav= $this->Api_model->check_favorites($user_id,$artist->user_id);
          $artist->fav_status= $check_fav ? "1":"0";

         array_push($artists, $artist);
        }
        
         usort($artists, function($a,$b) {
            if($a->distance == $b->distance) return 0;
            return ($a->distance < $b->distance) ? -1 : 1;
        });
        $this->api->api_message_data(1, ALL_ARTISTS,'data' , $artists);
      }
      else
      {
         $this->api->api_message(0, NO_DATA);
      }
   }

   /***************************************Subscription****************************/

   /*Subscription*/
  public function getAllPackages()
  {
      $packages=$this->Api_model->getAllDataWhere(array('status'=>1),'packages');
      if (empty($packages))
      {
          $this->api->api_message(0, NO_DATA);
      } 
      else
      {
        $package= array();
        foreach ($packages as $packages) 
        {
              if($packages->subscription_type==0)
              {
                  $packages->subscription_name=FREE;
              }
              elseif($packages->subscription_type==1)
              {
                  $packages->subscription_name=MONTHLY;
              }
              elseif($packages->subscription_type==2)
              {
                  $packages->subscription_name=QUARTERLY;
              }
              elseif($packages->subscription_type==3)
              {
                  $packages->subscription_name=HALFYEARLY;
              }
              elseif($packages->subscription_type==4)
              {
                  $packages->subscription_name=YEARLY;
              }

          $currency=$this->Api_model->getSingleRow('currency_setting', array('status' =>1));
          $packages->currency_type= $currency->currency_symbol;
          array_push($package, $packages);
        }
          $this->api->api_message_data(1, ALL_PACKAGES, 'packages', $package);
      }
  }

    public function subscription()
    {
        $this->form_validation->set_rules('order_id','order_id','required');
        $this->form_validation->set_rules('txn_id','txn_id','required');
        $this->form_validation->set_rules('package_id','package_id','required');
        $this->form_validation->set_rules('user_id','user_id','required');
        if ($this->form_validation->run()==false) {
            $this->api->api_message(0, ALL_FIELD_MANDATORY);
            exit();
        }
        $user_id = $this->input->post('user_id', TRUE);
        $chkUser=$this->Api_model->getSingleRow('user', array('user_id' =>$user_id));
        if(!$chkUser)
        {
            $this->api->api_message(0, USER_NOT_FOUND);
            exit();
        }

        $package_id=$this->input-> post('package_id', TRUE);
        $txn_id=$this->input-> post('txn_id', TRUE);
        $order_id=$this->input-> post('order_id', TRUE);
        $chkPackage=$this->Api_model->getSingleRow('packages', array('id' =>$package_id));
        if(!$chkPackage)
        {
            $this->api->api_message(0, PKG_NOT);
            exit();
        }
    
        $date = date('Y-m-d');
        $current_date=strtotime($date);

        $get_package = $this->Api_model->getSingleRow('packages', array('id'=>$package_id));   
        if(!$get_package)
        {
            $this->api->api_message(0, NO_DATA);
            exit();
        }  
        $price= $get_package->price;  
        $subscription_type= $get_package->subscription_type;
        $currency=$this->Api_model->getSingleRow('currency_setting', array('status' =>1));
        $currency_type= $currency->currency_symbol;

        $data['user_id']=$user_id;
        $data['txn_id']=$txn_id;
        $data['order_id']=$order_id;
        $data['subscription_start_date']= $current_date;
        $data['price']=$price;
        $data['subscription_type']=$subscription_type;
        $data['currency_type']=$currency_type;
        $dataupdate = $this->Api_model->getSingleRow('user_subscription', array('user_id'=>$user_id));     

        if($dataupdate)
        {
          $check_end_date= $dataupdate->subscription_end_date;

          if($current_date >= $check_end_date)
            {
              if($subscription_type == 1) 
              {               
                $end_date = date('Y-m-d', mktime(date('h'),date('i'),date('s'),date('m'),date('d')+30,date('Y')))."\n";
                $data['subscription_end_date'] =strtotime($end_date);
              }
              elseif($subscription_type == 3) 
              {               
                $end_date = date('Y-m-d', mktime(date('h'),date('i'),date('s'),date('m'),date('d')+180,date('Y')))."\n";
                $data['subscription_end_date'] =strtotime($end_date);
              }
              elseif($subscription_type == 2) 
              {               
                $end_date = date('Y-m-d', mktime(date('h'),date('i'),date('s'),date('m'),date('d')+90,date('Y')))."\n";
                $data['subscription_end_date'] =strtotime($end_date);
              }
              elseif($subscription_type == 0) 
              {               
                $end_date = date('Y-m-d', mktime(date('h'),date('i'),date('s'),date('m'),date('d')+30,date('Y')))."\n";
                $data['subscription_end_date'] =strtotime($end_date);
              }
              elseif($subscription_type == 4) 
              {               
                $end_date = date('Y-m-d', mktime(date('h'),date('i'),date('s'),date('m'),date('d')+365,date('Y')))."\n";
                $data['subscription_end_date'] =strtotime($end_date);
              }
              $this->Api_model->updateSingleRow('user_subscription', array('user_id'=>$user_id), $data);      
              $this->Api_model->insertGetId('subscription_history', $data);      
              $this->api->api_message(1, SUB_SUCCESS);
            }
            else
            {
              if($subscription_type == 1) 
              {               
                $no_of_days=30;  
                $end_date =strtotime('+'.$no_of_days.' days', $check_end_date);
                $data['subscription_end_date'] =$end_date;
              }
              elseif($subscription_type == 3) 
              {               
                $no_of_days=180;  
                $end_date =strtotime('+'.$no_of_days.' days', $check_end_date);
                $data['subscription_end_date'] =$end_date;
              }
              elseif($subscription_type == 2) 
              {               
                $no_of_days=90;  
                $end_date =strtotime('+'.$no_of_days.' days', $check_end_date);
                $data['subscription_end_date'] =$end_date;
              }
              elseif($subscription_type == 0) 
              {               
                $no_of_days=30;  
                $end_date =strtotime('+'.$no_of_days.' days', $check_end_date);
                $data['subscription_end_date'] =$end_date;
              }
              elseif($subscription_type == 4) 
              {               
                $no_of_days=365;  
                $end_date =strtotime('+'.$no_of_days.' days', $check_end_date);
                $data['subscription_end_date'] =$end_date;
              }
              $this->Api_model->updateSingleRow('user_subscription', array('user_id'=>$user_id), $data);      
              $this->Api_model->insertGetId('subscription_history', $data);      
              $this->api->api_message(1, SUB_SUCCESS);
            }
        } 
      else
        {
          if($subscription_type == 1) 
          {               
            $end_date = date('Y-m-d', mktime(date('h'),date('i'),date('s'),date('m'),date('d')+30,date('Y')))."\n";
            $data['subscription_end_date'] =strtotime($end_date);
          }
          elseif($subscription_type ==3) 
          {               
            $end_date = date('Y-m-d', mktime(date('h'),date('i'),date('s'),date('m'),date('d')+180,date('Y')))."\n";
            $data['subscription_end_date'] =strtotime($end_date);
          }
          elseif($subscription_type == 2) 
          {               
            $end_date = date('Y-m-d', mktime(date('h'),date('i'),date('s'),date('m'),date('d')+90,date('Y')))."\n";
            $data['subscription_end_date'] =strtotime($end_date);
          }
          elseif($subscription_type ==0) 
          {               
            $end_date = date('Y-m-d', mktime(date('h'),date('i'),date('s'),date('m'),date('d')+30,date('Y')))."\n";
            $data['subscription_end_date'] =strtotime($end_date);
          }
          elseif($subscription_type == 4) 
          {               
            $end_date = date('Y-m-d', mktime(date('h'),date('i'),date('s'),date('m'),date('d')+365,date('Y')))."\n";
            $data['subscription_end_date'] =strtotime($end_date);
          }
          $this->Api_model->insertGetId('user_subscription', $data);   
          $this->Api_model->insertGetId('subscription_history', $data);   
        
          $this->api->api_message(1, SUB_SUCCESS);
        }    
    }


    public function get_my_subscription_history()
    {
        $this->form_validation->set_rules('user_id','user_id','required');
        if ($this->form_validation->run()==false) {
            $this->api->api_message(0, ALL_FIELD_MANDATORY);
            exit();
        }
        $user_id = $this->input->post('user_id', TRUE);
        $chkUser=$this->Api_model->getSingleRow('user', array('user_id' =>$user_id));
        if(!$chkUser)
        {
            $this->api->api_message(0, USER_NOT_FOUND);
            exit();
        }

        $get_my_subscription=$this->Api_model->getAllDataWhere(array('user_id' => $user_id), 'subscription_history');

        if ($get_my_subscription) 
        {
          $get_my_subscriptions= array();
          foreach ($get_my_subscription as $get_my_subscription) 
          {
            if($get_my_subscription->subscription_type==0)
            {
              $get_my_subscription->subscription_name=FREE;
            }
            elseif($get_my_subscription->subscription_type==1)
            {
              $get_my_subscription->subscription_name=MONTHLY;
            }
            elseif($get_my_subscription->subscription_type==2)
            {
              $get_my_subscription->subscription_name=QUARTERLY;
            }
            elseif($get_my_subscription->subscription_type==3)
            {
              $get_my_subscription->subscription_name=HALFYEARLY;
            }
            elseif($get_my_subscription->subscription_type==4)
            {
              $get_my_subscription->subscription_name=YEARLY;
            }

            array_push($get_my_subscriptions, $get_my_subscription);
          }
            $this->api->api_message_data(1, SUB_HISTORY,'my_subscription_history', $get_my_subscriptions);
        }
        else
        {
            $this->api->api_message(0, NO_DATA);
        }
    }  

    public function get_my_subscription()
    {
        $this->form_validation->set_rules('user_id','user_id','required');
        if ($this->form_validation->run()==false) {
            $this->api->api_message(0, ALL_FIELD_MANDATORY);
            exit();
        }
        $user_id = $this->input->post('user_id', TRUE);
        $chkUser=$this->Api_model->getSingleRow('user', array('user_id' =>$user_id));
        if(!$chkUser)
        {
            $this->api->api_message(0, USER_NOT_FOUND);
            exit();
        }

        $get_my_subscription = $this->Api_model->getSingleRow('user_subscription', array('user_id'=>$user_id)); 
        if ($get_my_subscription) 
        {           
            $date = date('Y-m-d');
            $current_date=strtotime($date);
            $end_date=$get_my_subscription->subscription_end_date;
            $get_title = $this->Api_model->getSingleRow('packages', array('subscription_type'=>$get_my_subscription->subscription_type)); 
            $get_my_subscription->subscription_title = $get_title->title;
            $datediff = $end_date - time();
            $get_my_subscription->days=round($datediff / (60 * 60 * 24));
            if($get_my_subscription->subscription_type==0)
            {
              $get_my_subscription->subscription_name=FREE;
            }
            elseif($get_my_subscription->subscription_type==1)
            {
              $get_my_subscription->subscription_name=MONTHLY;
            }
            elseif($get_my_subscription->subscription_type==2)
            {
              $get_my_subscription->subscription_name=QUARTERLY;
            }
            elseif($get_my_subscription->subscription_type==3)
            {
              $get_my_subscription->subscription_name=HALFYEARLY;
            }
            elseif($get_my_subscription->subscription_type==4)
            {
              $get_my_subscription->subscription_name=YEARLY;
            }

            if($current_date>$end_date)
            {
                $this->api->api_message(0, NO_DATA);
            }
            else
            {
                $this->api->api_message_data(1, MY_SUB,'my_subscription', $get_my_subscription);
            }
        }
        else
        {
            $this->api->api_message(0, NOT_SUB);
        }   
    } 

    /*get Job invoice*/
    public function getJobInvoice()
    {
      $artist_id = $this->input->post('artist_id', TRUE);
      $job_id = $this->input->post('job_id', TRUE);

      $getBooking = $this->Api_model->getSingleRow(ABK_TBL, array('artist_id'=>$artist_id,'job_id'=>$job_id)); 
      if ($getBooking) 
      {
        $getInvoice = $this->Api_model->getSingleRow(IVC_TBL, array('booking_id'=>$getBooking->id));
        if($getInvoice)
        {
          $getUser = $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getInvoice->user_id));

          if($getUser->image)
          {
            $getInvoice->userImage= base_url().$getUser->image;
          }
          else
          {
            $getInvoice->userImage= base_url()."assets/images/image.png";
          }

          $getInvoice->userName= $getUser->name;

          $getArtist = $this->Api_model->getSingleRow(USR_TBL, array('user_id'=>$getInvoice->artist_id));
          $get_artists=$this->Api_model->getSingleRow(ART_TBL,array('user_id'=>$getInvoice->artist_id));

         $get_cat=$this->Api_model->getSingleRow(CAT_TBL, array('id'=>$get_artists->category_id));
         $getInvoice->category_name= $get_cat->cat_name;
          if($getArtist->image)
          {
            $getInvoice->artistImage= base_url().$getArtist->image;
          }
          else
          {
            $getInvoice->artistImage= base_url()."assets/images/image.png";
          }

          $getInvoice->artistName= $getArtist->name;

          $this->api->api_message_data(1, MY_INVOICE,'data', $getInvoice);
        }
        else
        {
          $this->api->api_message(0, NO_DATA);
        }
      }
      else
      {
        $this->api->api_message(0, NO_DATA);
      }
    }

     /*fill paypal Details*/
    public function fillPaypal()
    {
      $data['artist_id'] = $this->input->post('artist_id', TRUE);
      $data['business_name'] = $this->input->post('business_name', TRUE);
      $data['paypal_id'] = $this->input->post('paypal_id', TRUE);
      $this->checkUserStatus($data['artist_id']);

      $getUserId=$this->Api_model->insertGetId(PYL_DTS_TBL,$data);
      if($getUserId)
      {
         $this->api->api_message(1, DTL_UPLD);
      }
      else
      {
         $this->api->api_message(0, TRY_AGAIN);
      }
    }

    /*get My payPal Details*/
    public function getPaypalDetails()
    {
       $artist_id = $this->input->post('artist_id', TRUE);
       $getArtist = $this->Api_model->getSingleRow(PYL_DTS_TBL, array('artist_id'=>$artist_id));
       if($getArtist)
       {
          $this->api->api_message_data(1, GET_MYPAY,'my_paypal_details', $getArtist);
       }
       else
       {
          $this->api->api_message(0, FILL_PAY);
       }
    } 

     public function getAllProBusiness()
    {
      $get_pro=$this->Api_model->getAllData('pro_business_setting');
       if($get_pro)
      {
        $this->api->api_message_data(1, ALL_PRO,'data' , $get_pro);
      }
      else
      {
         $this->api->api_message(0, NO_DATA);
      }
        
    }
      
    

}