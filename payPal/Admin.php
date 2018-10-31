<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller
{
  /*For localhost*/
 
 // public $sidebar= 'common/sidebarlocal.php';
    /*For sever*/
  public $sidebar='common/sidebar.php';
    public function __construct()
    {
        parent::__construct();
        $this -> load -> library('session');
        $this -> load -> helper('form');
        $this -> load -> helper('url');
        $this -> load -> database();
        $this -> load->library('api');
        $this -> load -> library('form_validation');
        $this -> load -> model('Comman_model');
        $this -> load -> model('Api_model');
    }

    public function index()
    {
      redirect('Admin/home');
    }
    
    public function home()
    {
        /*if(isset($_SESSION['name'])) 
        { */
            $data['artist']=$this->Api_model->getCountAll('artist');
            $data['user']=$this->Api_model->getCount('user', array('role'=>2));
            $data['total_revenue']=$this->Api_model->getSum('total_amount', 'booking_invoice');

            $getInvoice= $this->Api_model->getAllDataLimit('booking_invoice',5);

            $getInvoices = array();
            foreach ($getInvoice as $getInvoice)
            {
              $getBooking= $this->Api_model->getSingleRow('artist_booking', array('id'=>$getInvoice->booking_id));

              $getInvoice->booking_time= $getBooking->booking_time;
              $getInvoice->booking_date= $getBooking->booking_date;

              $getUser= $this->Api_model->getSingleRow('user', array('user_id'=>$getInvoice->user_id));

              $getInvoice->userName= $getUser->name;
              $getInvoice->address= $getUser->address;

              if($getUser->image)
              {
                $getInvoice->userImage= base_url().$getUser->image;
              }
              else
              {
                $getInvoice->userImage= base_url().'assets/images/a.png';
              }

              $get_artists= $this->Api_model->getSingleRow('artist', array('user_id'=>$getInvoice->artist_id));

              $get_cat=$this->Api_model->getSingleRow('category', array('id'=>$get_artists->category_id));

              $getInvoice->ArtistName=$get_artists->name;
              $getInvoice->categoryName=$get_cat->cat_name;

              if($get_artists->image)
              {
                $getInvoice->artistImage= base_url().$get_artists->image;
              }
              else
              {
                $getInvoice->artistImage= base_url().'assets/images/a.png';
              }

              array_push($getInvoices, $getInvoice);
            }

            $tickets= array();
            $ticket=$this->Api_model->getAllDataLimit('ticket',5);
            foreach ($ticket as $ticket) 
            {
              $getUser= $this->Api_model->getSingleRow('user', array('user_id'=>$ticket->user_id));
              $ticket->userName= $getUser->name;
              $ticket->userImage= $getUser->image;
              array_push($tickets, $ticket);
            }
            $data['active_user']=$this->Api_model->getCount('user', array('status'=>1,));
            $data['deactive_user']=$this->Api_model->getCount('user', array('status'=>0,));

            $users= $this->Api_model->getAllData('user');
            if($users)
            {
              $data['monthly_user']=$this->Api_model->getMontlyUserCount();
              $data['monthly_revenue']=$this->Api_model->getMontlyRevenue();
            }
            else
            {
              $data['monthly_user']=array();
              $data['monthly_revenue']=array();
            }
            $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
            $data['currency_type']= $currency_setting->currency_symbol;

            $data['page']='home';
            $data['tickets']=$tickets;
            $data['getInvoices']=$getInvoices;
            $this -> load -> view('common/head.php');
            $this -> load -> view($this->sidebar, $data);
            $this -> load ->view('dashboard.php', $data);
            $this -> load -> view('common/footer.php');
       /* }
        else
        {
            redirect('');
        }*/
    }

    /*Get all revenu*/

    public function warningUser()
    {
        /*if(isset($_SESSION['name']))
        {*/
            $user_id= $_GET['user_id'];
            $data['user_id']= $user_id;
            $data['created_at']= time();

            $getUserId=$this->Api_model->insertGetId('user_warning',$data);

            $totalWarning=$this->Api_model->getCountWhere('user_warning', array('user_id'=>$user_id));

            if($totalWarning==3)
            {
                $msg='Now you blocked by admin';
                $this->firebase_notification($user_id, "Warning" ,$msg);

                $where = array('user_id'=>$user_id);
                $datas = array('status'=>0);
                $update= $this->Api_model->updateSingleRow('user', $where, $datas);
            }
            
            redirect('Admin/warning');     
        /*}
        else
        {
            redirect();
        } */ 
    }


    public function jobs()
    {
      $get_jobs= $this->Api_model->getAllData('post_job');


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
          $get_jobs->user_image = $user->image;
          $get_jobs->user_name = $user->name;
          $get_jobs->user_address = $user->address;
          $get_jobs->user_mobile = $user->mobile;
            
          array_push($job_list, $get_jobs);
        }

        $data['job_list']= $job_list;
        $data['page']='jobs';
        $this -> load -> view('common/head.php');
        $this -> load -> view($this->sidebar, $data);
        $this -> load ->view('post_job.php', $data);
        $this -> load -> view('common/footer.php');
    }


    public function ViewJobDetails()
    { 
       $job_id= $_GET['job_id'];

      $get_jobs= $this->Api_model->getAllDataWhere(array('job_id'=>$job_id),'applied_job');

        $job_list = array();
        foreach ($get_jobs as $get_jobs) 
        {
          $table= 'user';       
          $condition = array('user_id'=>$get_jobs->artist_id);  
          $user = $this->Api_model->getSingleRow($table, $condition);  
          $user->image= base_url().$user->image;

          $get_jobs->user_image = $user->image;
          $get_jobs->user_name = $user->name;
          $get_jobs->user_address = $user->address;
          $get_jobs->user_mobile = $user->mobile;
          $get_jobs->user_email = $user->email_id;
            
          array_push($job_list, $get_jobs);
        }
      
        $data['job_list']= $job_list;
        $data['page']='jobs';
        $this -> load -> view('common/head.php');
        $this -> load -> view($this->sidebar, $data);
        $this -> load ->view('ViewJobDetails.php', $data);
        $this -> load -> view('common/footer.php');
    }
    
    public function all_revenue()
    {
      $cars = array(1, 2, 3);
      print_r($cars);
    }

    /*All Artists*/
    public function artists()
    {
        /*if(isset($_SESSION['name'])) 
        { */
            $artist=$this->Api_model->getAllData('artist');

            $artists= array();
            foreach ($artist as $artist) 
            {
              $getUser= $this->Api_model->getSingleRow('user', array('user_id'=>$artist->user_id));
              $wallent= $this->Api_model->getSingleRow('artist_wallet', array('artist_id'=>$artist->user_id));
              if($wallent)
              {
                $artist->amount=$wallent->amount;
              }
              else
              {
                $artist->amount=0;
              }
              $artist->email_id=$getUser->email_id;
              $artist->status=$getUser->status;
              $artist->approval_status=$getUser->approval_status;
              array_push($artists, $artist);
            }

            $data['artist']= $artists;
            $data['page']='artists';
            $this -> load -> view('common/head.php');
            $this -> load -> view($this->sidebar, $data);
            $this -> load ->view('artist.php', $data);
            $this -> load -> view('common/footer.php');
       /* }
        else
        {
          redirect('');
        }*/
    }


    /*All Artists*/
    public function ViewTicket()
    {
      /*if(isset($_SESSION['name'])) 
      { */
        $ticket_id= $_GET['id'];
        $ticket= $this->Api_model->getSingleRow('ticket', array('id'=>$ticket_id));
       
          $getUser= $this->Api_model->getSingleRow('user', array('user_id'=>$ticket->user_id));

          $ticket->userName= $getUser->name;
          $ticket->userImage= $getUser->image;
  
          $data['ticket'] = $ticket;
          $ticket_comments=$this->Api_model->getAllDataWhere(array('ticket_id'=>$ticket_id), 'ticket_comments');
          $ticket_comment= array();
          foreach ($ticket_comments as $ticket_comments) 
          {
            if($ticket_comments->user_id !=0)
            {
              $getUser= $this->Api_model->getSingleRow('user', array('user_id'=>$ticket_comments->user_id));
              $ticket_comments->userName=$getUser->name;
            }
            else
            {
              $ticket_comments->userName="Admin";
            }
            array_push($ticket_comment, $ticket_comments);
          }

          $data['page']='ticket';
          $data['ticket_comment']=$ticket_comment;
          $this -> load -> view('common/head.php');
          $this -> load -> view($this->sidebar, $data);
          $this -> load ->view('ViewTicket.php', $data);
          $this -> load -> view('common/footer.php');
      /*}
      else
      {
        redirect('');
      }*/
    }

    public function addComment()
    {
      /*if(isset($_SESSION['name'])) 
        {*/
          $data['comment']= $this->input->post('comment', TRUE);
          $data['ticket_id']= $this->input->post('ticket_id', TRUE);
          $data['role']= "Admin";
          $data['user_id']= 0;
          $data['created_at']=time();

          $this->Api_model->insertGetId('ticket_comments',$data);
          redirect('Admin/ViewTicket?id='.$data['ticket_id']);
       /* }
        else
        {
          redirect('');
        }*/
    }
      /*All Artists*/
     public function ticket()
      {
        /*if(isset($_SESSION['name'])) 
        { */
          $tickets= array();
          $ticket=$this->Api_model->getAllData('ticket');
          foreach ($ticket as $ticket) 
          {
            $getUser= $this->Api_model->getSingleRow('user', array('user_id'=>$ticket->user_id));
            $ticket->userName= $getUser->name;
            $ticket->userImage= $getUser->image;
            array_push($tickets, $ticket);
          }

          $data['ticket']= array_reverse($tickets);
          $data['page']='ticket';
          $this -> load -> view('common/head.php');
          $this -> load -> view($this->sidebar, $data);
          $this -> load ->view('ticket.php', $data);
          $this -> load -> view('common/footer.php');
        /*}
        else
        {
          redirect('');
        }*/
      }
   
     /*All User*/
    public function user()
    {
        /*if(isset($_SESSION['name'])) 
        {*/
          $user= $this->Api_model->getAllDataWhere(array('role'=>2), 'user');

          $data['user']= $user;
          $data['page']='user';
          $this -> load -> view('common/head.php');
          $this -> load -> view($this->sidebar, $data);
          $this -> load ->view('user.php', $data);
          $this -> load -> view('common/footer.php');
        /*}
        else
        {
          redirect('');
        }*/
    }

     /*All Warning User*/
    public function warning()
    {
       /* if(isset($_SESSION['name'])) 
        {*/
          $User=$this->Api_model->getAllDataDistinct('user_warning');
            $users= array();
            foreach ($User as $User) 
            {
                $userMedia=$this->Api_model->getAllDataWhere(array('user_id'=>$User->user_id),'user_warning');

                $checkUser= $this->Api_model->getSingleRow('user', array('user_id'=>$User->user_id));
                $count= $this->Api_model->getCountWhere('user_warning', array('user_id'=>$User->user_id));
                $checkUser->count= $count;

                array_push($users, $checkUser);
            }

            $data['user']=$users;

          $data['page']='warningUser';
          $this -> load -> view('common/head.php');
          $this -> load -> view($this->sidebar, $data);
          $this -> load ->view('warningUser.php', $data);
          $this -> load -> view('common/footer.php');
        /*}
        else
        {
          redirect('');
        }*/
    }
    

     /*All User*/
     public function broadcast_msg()
    {
      /*if(isset($_SESSION['name'])) 
      {*/
          $coupon= $this->Api_model->getAllDataWhere(array('type'=>"All"),'notifications');

          $data['coupon']= $coupon;
          $data['page']='broadcast';
          $this -> load -> view('common/head.php');
          $this -> load -> view($this->sidebar, $data);
          $this -> load ->view('broadcast_msg.php', $data);
          $this -> load -> view('common/footer.php');
      /*}
      else
      {
          redirect('');
      }*/
    }

      /*All User*/
     public function setting()
    {
     /* if(isset($_SESSION['name'])) 
      {*/
        $commission_setting= $this->Api_model->getSingleRow('commission_setting',array('id'=>1));
        $data['currency_setting']=$this->Api_model->getAllData(CRYSET_TBL);

        $data['commission_setting']= $commission_setting;
        $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
        $data['currency_type']= $currency_setting->currency_symbol;

        $data['page']='setting';
        $this -> load -> view('common/head.php');
        $this -> load -> view($this->sidebar, $data);
        $this -> load ->view('setting.php', $data);
        $this -> load -> view('common/footer.php');
     /* }
      else
      {
        redirect('');
      }*/
    }

    public function commissionSetting()
    {
      $id= $this->input->post('id', TRUE);
      $data['commission_type']= $this->input->post('commission_type', TRUE);

      if($data['commission_type']==1)
      {
        $data['flat_amount']= $this->input->post('flat_amount', TRUE);
      $data['flat_type']= $this->input->post('flat_type', TRUE);
      }

      $this->Api_model->updateSingleRow('commission_setting', array('id'=>$id), $data);
      $this->session->set_flashdata('msg', 'Commission changed successfully.');
      redirect('Admin/setting');
    }

    public function currency_setting()
    {
      $currency_id=$this->input->post('currency', TRUE);
      $this->Api_model->updateSingleRow(CRYSET_TBL, array('status'=>1), array('status'=>0));
      $this->Api_model->updateSingleRow(CRYSET_TBL, array('id'=>$currency_id), array('status'=>1));
      $this->session->set_flashdata('msg1', 'Currency Type changed successfully.');
      redirect('Admin/setting');
    }
     /*All User*/
     public function coupon()
    {
      /*if(isset($_SESSION['name'])) 
      {*/
          $coupon= $this->Api_model->getAllData('discount_coupon');
          $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
          $data['currency_type']= $currency_setting->currency_symbol;

          $data['coupon']= $coupon;
          $data['page']='coupon';
          $this -> load -> view('common/head.php');
          $this -> load -> view($this->sidebar, $data);
          $this -> load ->view('coupon.php', $data);
          $this -> load -> view('common/footer.php');
      /*}
      else
      {
          redirect('');
      }*/
    }

     /*All User*/
     public function addArtist()
    {
      /*if(isset($_SESSION['name'])) 
      {*/
          $data['page']='artist';
          $this -> load -> view('common/head.php');
          $this -> load -> view($this->sidebar, $data);
          $this -> load ->view('addArtist.php', $data);
          $this -> load -> view('common/footer.php');
      /*}
      else
      {
          redirect('');
      }*/
    }

     public function proBusiness()
    {
      /*if(isset($_SESSION['name'])) 
      {*/
          $data['page']='proBusiness';
          $data['pro_business_setting'] = $this->Api_model->getAllData('pro_business_setting');
          $this -> load -> view('common/head.php');
          $this -> load -> view($this->sidebar, $data);
          $this -> load ->view('proBusiness.php', $data);
          $this -> load -> view('common/footer.php');
      /*}
      else
      {
          redirect('');
      }*/
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

    public function addArtistAction()
    {
        $name= $this->input->post('name', TRUE);
        $email_id= $this->input->post('email_id', TRUE);
        $password= $this->input->post('password', TRUE);

        $referral_code=$this->api->random_num(6);
        $data = array('name'=>$name,'email_id'=>$email_id,'password'=>$password,'role'=>1,'status'=>0,'created_at'=>time(),'updated_at'=>time(),'referral_code'=>$referral_code,'approval_status'=>1);
        $table='user';
        $get_user=$this->Api_model->getSingleRow($table, array('email_id'=>$email_id));

        if(!$get_user)
        {
            $getUserId=$this->Api_model->insertGetId($table,$data);
            if($getUserId)
            {
                $url= base_url().'Webservice/userActive?user_id='.$getUserId;
                $msg='Thanks for signing up! Your account has been created, you can login with the following credentials after you have activated your account by pressing the url below. Please click ' .$url;
              
                $this->send_email($email_id, "FabArtist Registration", $msg);
                $data1['user_id']= $getUserId;
                $data1['name']= $name;
                $data1['created_at']= time();
                $data1['updated_at']= time();
                $this->Api_model->insertGetId('artist',$data1);
            }
            redirect('Admin/artists');
          }
          else
          {
            redirect('Admin/artists');
          }
    }

     /*All User*/
     public function category()
    {
      /*if(isset($_SESSION['name'])) 
      {*/
          $category= $this->Api_model->getAllData('category');

          $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
          $data['currency_type']= $currency_setting->currency_symbol;

          $data['category']= $category;
          $data['page']='category';
          $this -> load -> view('common/head.php');
          $this -> load -> view($this->sidebar, $data);
          $this -> load ->view('addCategory.php', $data);
          $this -> load -> view('common/footer.php');
      /*}
      else
      {
        sredirect('');
      }*/
    }

    public function editCategory()
    {
      /*if(isset($_SESSION['name'])) 
      {*/
        $id= $_GET['id'];
        $data['category']= $this->Api_model->getSingleRow('category', array('id'=>$id));
        $data['page']='category';
        $this -> load -> view('common/head.php');
        $this -> load -> view($this->sidebar, $data);
        $this -> load ->view('editCategory.php', $data);
        $this -> load -> view('common/footer.php');
      /*}
      else
      {
        sredirect('');
      }*/
    }


     /*All User*/
    public function skills()
    {
      /*if(isset($_SESSION['name'])) 
      {*/
        $skills= $this->Api_model->getAllData('skills');
        $skill= array();
        foreach ($skills as $skills) 
        {
          $category= $this->Api_model->getSingleRow('category', array('id'=>$skills->cat_id));
          $skills->cat_name= $category->cat_name;
          array_push($skill, $skills);
        }
        $data['category']= $this->Api_model->getAllData('category');
        $data['skills']= $skill;
        $data['page']='skills';
        $this -> load -> view('common/head.php');
        $this -> load -> view($this->sidebar, $data);
        $this -> load ->view('addSkills.php', $data);
        $this -> load -> view('common/footer.php');
      /*}
      else
      {
        sredirect('');
      }*/
    }

    public function editSkills()
    {
      /*if(isset($_SESSION['name'])) 
      {*/
        $id= $_GET['id'];
        $data['category']= $this->Api_model->getAllData('category');
        $data['skills']= $this->Api_model->getSingleRow('skills', array('id'=>$id));
        $data['page']='skills';
        $this -> load -> view('common/head.php');
        $this -> load -> view($this->sidebar, $data);
        $this -> load ->view('editSkills.php', $data);
        $this -> load -> view('common/footer.php');
     /* }
      else
      {
        sredirect('');
      }*/
    }

    /*Add coupon*/
    public function addSkillsAction()
    {
        $data['skill']= $this->input->post('skill', TRUE);
        $data['cat_id']= $this->input->post('cat_id', TRUE);
        $data['created_at']=time();
        $data['updated_at']=time();

        $this->Api_model->insertGetId('skills',$data);
        redirect('Admin/skills');
    }


    /*Add coupon*/
    public function editSkillsAction()
    {
        $id= $this->input->post('id', TRUE);
        $data['skill']= $this->input->post('skill', TRUE);
        $data['cat_id']= $this->input->post('cat_id', TRUE);
        $data['updated_at']=time();

        $this->Api_model->updateSingleRow('skills', array('id'=>$id), $data);
        redirect('Admin/skills');
    }

    /*All Admin*/
    public function manager()
    {
      /*if(isset($_SESSION['name'])) 
      {*/
          $admin=$this->Api_model->getAllDataWhere(array('role'=>1), 'admin');

          $data['admin']= $admin;
          $data['page']='admin';
          $this -> load -> view('common/head.php');
          $this -> load -> view($this->sidebar, $data);
          $this -> load ->view('manager.php', $data);
          $this -> load -> view('common/footer.php');
      /*}
      else
      {
          redirect('');
      }*/
    }

    /*Request For Amount*/
    public function requestAmount()
    {
      /*if(isset($_SESSION['name'])) 
      {*/
          $wallet_request=$this->Api_model->getAllData('wallet_request');

          $wallet_requests = array();
          foreach ($wallet_request as $wallet_request) 
          {
            $getUser= $this->Api_model->getSingleRow('user', array('user_id'=>$wallet_request->artist_id));
            $wallet_request->email_id=$getUser->email_id;
            $wallet_request->name=$getUser->name;

            array_push($wallet_requests, $wallet_request);

            $getCommission= $this->Api_model->getSingleRow('artist_wallet',array('artist_id'=>$wallet_request->artist_id));

            $onlineEarning=$this->Api_model->getSumWhere('artist_amount', IVC_TBL,array('artist_id'=>$wallet_request->artist_id,'payment_type'=>0));

            $offlineEarning=$this->Api_model->getSumWhere('artist_amount', IVC_TBL,array('artist_id'=>$wallet_request->artist_id,'payment_type'=>1));

            $onlineEarning=round($onlineEarning->artist_amount, 2);
            $cashEarning=round($offlineEarning->artist_amount, 2);

             $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
            $wallet_request->currency_code=$currency_setting->code;
            if($getCommission)
            {
              $wallet_request->walletAmount= $getCommission->amount;
            }
            else
            {
              $wallet_request->walletAmount= $onlineEarning - $cashEarning;
            }
          }
          $data['wallet_requests']= $wallet_requests;
          $data['page']='requestAmount';
          $this -> load -> view('common/head.php');
          $this -> load -> view($this->sidebar, $data);
          $this -> load ->view('requestAmount.php', $data);
          $this -> load -> view('common/footer.php');
      /*}
      else
      {
          redirect('');
      }*/
    }

      /*All User*/
    public function artistDetails()
    {
       /* if(isset($_SESSION['name'])) 
        {*/
            $user_id= $_GET['id'];
            $role= $_GET['role'];
            $artist_name= $_GET['artist_name'];

            $artist=$this->Api_model->getSingleRow('artist',array('user_id'=>$user_id));
             $get_reviews=  $this->Api_model->getAllDataWhere(array('artist_id'=>$user_id), 'rating');
            $review = array();
            foreach ($get_reviews as $get_reviews) {
              $get_user = $this->Api_model->getSingleRow('user', array('user_id'=>$get_reviews->user_id));
              $get_reviews->name= $get_user->name;
              $get_reviews->image= base_url()."assets/images/1520435084.png";

              array_push($review, $get_reviews);
            }

            $get_gallery=  $this->Api_model->getAllDataWhere(array('user_id'=>$user_id), 'gallery');

            $jobDone=$this->Api_model->getTotalWhere('artist_booking',array('artist_id'=>$user_id,'booking_flag'=>4));

            $data['total']=$this->Api_model->getTotalWhere('artist_booking',array('artist_id'=>$user_id));
            if($data['total']==0)
            {
                $data['percentages']=0;
            }
            else
            {
                $data['percentages']=($jobDone*100) / $data['total'];
            }
            
            $data['jobDone']=$jobDone;

            if($role==1)
            {      
              $where=array('artist_id'=>$user_id);   

              $get_appointment=$this->Api_model->getAllDataWhere($where,'artist_booking');

              $get_appointments = array();
              foreach ($get_appointment as $get_appointment) 
              {
                $get_user= $this->Api_model->getSingleRow('user', array('user_id'=>$get_appointment->user_id));

                $get_appointment->name= $get_user->name;
                $get_appointment->image= base_url().$get_user->image;
                $get_appointment->address= $get_user->address;

                array_push($get_appointments, $get_appointment);
              }
            }
            elseif($role==2)
            {
              $where=array('user_id'=>$user_id);

              $get_appointment=$this->Api_model->getAllDataWhere($where,'artist_booking');

              $get_appointments = array();
              foreach ($get_appointment as $get_appointment) 
              {
                $get_user= $this->Api_model->getSingleRow('artist', array('user_id'=>$get_appointment->artist_id));
                $get_user->image= base_url().$get_user->image;
                $get_appointment->name= $get_user->name;
                $get_appointment->image= base_url().$get_user->image;
                $get_appointment->address= $get_user->address;

                array_push($get_appointments, $get_appointment);
              } 
            }

            $where=array('user_id'=>$user_id);

            $get_products=$this->Api_model->getAllDataWhere($where,'products');

            $data['get_products']= $get_products;

            $where=array('artist_id'=>$user_id);

            $getInvoice=$this->Api_model->getAllDataWhere($where,'booking_invoice');

            $getInvoices = array();
            foreach ($getInvoice as $getInvoice)
            {
              $getBooking= $this->Api_model->getSingleRow('artist_booking', array('id'=>$getInvoice->booking_id));

              $getInvoice->booking_time= $getBooking->booking_time;
              $getInvoice->booking_date= $getBooking->booking_date;

              $getUser= $this->Api_model->getSingleRow('user', array('user_id'=>$getInvoice->user_id));

              $getInvoice->userName= $getUser->name;
              $getInvoice->address= $getUser->address;

              if($getUser->image)
              {
                $getInvoice->userImage= base_url().$getUser->image;
              }
              else
              {
                $getInvoice->userImage= base_url().'assets/images/a.png';
              }

              $get_artists= $this->Api_model->getSingleRow('artist', array('user_id'=>$getInvoice->artist_id));

              $get_cat=$this->Api_model->getSingleRow('category', array('id'=>$get_artists->category_id));

              $getInvoice->ArtistName=$get_artists->name;
              $getInvoice->categoryName=$get_cat->cat_name;

              if($get_artists->image)
              {
                $getInvoice->artistImage= base_url().$get_artists->image;
              }
              else
              {
                $getInvoice->artistImage= base_url().'assets/images/a.png';
              }
              array_push($getInvoices, $getInvoice);
            }

            $data['get_invoice']= $getInvoices;
            $data['get_appointments']= $get_appointments;
            $data['get_reviews']= $review;
            $data['get_gallery']= $get_gallery;
            $data['artist_name']= $artist_name;
            $data['user']= $artist;
            $data['page']='artist';
            $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
            $data['currency_type']= $currency_setting->currency_symbol;

            $this -> load -> view('common/head.php');
            $this -> load -> view($this->sidebar, $data);
            $this -> load ->view('artistDetails.php', $data);
            $this -> load -> view('common/footer.php');
       /* }
        else
        {
            redirect('');
        }*/
      }

    /*Add coupon*/
    public function addCategoryAction()
    {
        $data['cat_name']= $this->input->post('cat_name', TRUE);
        $data['price']= $this->input->post('price', TRUE);
        $data['created_at']=time();
        $data['updated_at']=time();

        $this->Api_model->insertGetId('category',$data);
        redirect('Admin/category');
    }


    /*Add coupon*/
    public function editCategoryAction()
    {
        $id= $this->input->post('id', TRUE);
        $data['cat_name']= $this->input->post('cat_name', TRUE);
        $data['price']= $this->input->post('price', TRUE);
        $data['updated_at']=time();

        $this->Api_model->updateSingleRow('category', array('id'=>$id), $data);
        redirect('Admin/category');
    }

    /*Add coupon*/
    public function add_coupon()
    {
        $data['coupon_code']= $this->input->post('coupon_code', TRUE);
        $data['description']= $this->input->post('description', TRUE);
        $data['discount_type']= $this->input->post('discount_type', TRUE);
        $data['discount']= $this->input->post('discount', TRUE);

        $data['created_at']=time();
        $data['updated_at']=time();

        /*$user=$this->Api_model->getAllData('user');
        if($user)
        {
          $title="New Coupon";
          $msg1=$data['coupon_code'].' use this coupon code for '.$data['description'];
          $this->firebase_notification($user->user_id,$title,$msg1);
        }     

        $dataNotification['title']= $title;
        $dataNotification['msg']= $msg1;
        $dataNotification['type']= "All";
        $dataNotification['created_at']=time(); 
        $this->Api_model->insertGetId('notifications',$dataNotification);*/

        $this->Api_model->insertGetId('discount_coupon',$data);
        redirect('Admin/coupon');
    }
    /* Add Pro Business */

    public function add_Pro_Business()
    {
        
        $data['flat_amount']= $this->input->post('flat_amount', TRUE);
        $data['created_at']=time();
        $proCount = $this->Api_model->getSingleRow('pro_business_setting',array('id'=>1));
        
        if(count($proCount)==0){
          
        $this->Api_model->insertGetId('pro_business_setting',$data);
        }else{

        $where = $proCount->id;
        $this->Api_model->updateSingleRow('pro_business_setting',array('id'=>$where),$data);
      }
        redirect('Admin/proBusiness');
      }
    

    /*Add coupon*/
    public function add_broadcast()
    {
        $data['title']= $this->input->post('title', TRUE);
        $data['msg']= $this->input->post('msg', TRUE);
        $data['type']= "All";
        $data['created_at']=time(); 
        
        $users=$this->Api_model->getAllData('user');
        foreach ($users as $users) 
        {
          $title=$data['title'];
          $msg1=$data['msg'];
          $this->firebase_notification($users->user_id,$title,$msg1);
        }

        $this->Api_model->insertGetId('notifications',$data);
        redirect('Admin/broadcast_msg');
    }

     /*Add coupon*/
    public function add_manager()
    {
        $data['name']= $this->input->post('name', TRUE);
        $data['email']= $this->input->post('email', TRUE);
        $data['password']= $this->input->post('password', TRUE);

        $data['created_on']=time();
        $data['updated_on']=time();

        if($this->Api_model->getSingleRow('admin', array('email'=>$data['email'])))
        {
          redirect('Admin/manager');
        }
        else
        {
          $this->Api_model->insertGetId('admin',$data);
          redirect('Admin/manager');
        }        
    }

    /*Add coupon*/
    public function edit_manager()
    {
        $id= $this->input->post('id', TRUE);
        $data['name']= $this->input->post('name', TRUE);
        $data['email']= $this->input->post('email', TRUE);
        $data['password']= $this->input->post('password', TRUE);

        $data['created_on']=time();
        $data['updated_on']=time();

        $this->Api_model->updateSingleRow('admin', array('id'=>$id), $data);
        redirect('Admin/manager');  
    }

    /*All Admin*/
     public function editmanager($id)
    {
      /*if(isset($_SESSION['name'])) 
      {*/
          $admin= $this->Api_model->getSingleRow('admin', array('id'=>$id));

          $data['admin']= $admin;
          $data['page']='manager';
          $this -> load -> view('common/head.php');
          $this -> load -> view($this->sidebar, $data);
          $this -> load ->view('editmanager.php', $data);
          $this -> load -> view('common/footer.php');
      /*}
      else
      {
          redirect('');
      }*/
    }

    /*Change Status Invoice*/
    public function change_status_invoice()
    {
        $id= $_GET['id'];
        $status= $_GET['status'];
        $where = array('id'=>$id);
        $data = array('flag'=>$status,'payment_status'=>1);

        $getInvoice=$this->Api_model->getSingleRow('booking_invoice',array('id'=>$id));
    
        $getBooking= $this->Api_model->getSingleRow('artist_booking', array('id'=>$getInvoice->booking_id));

        $getInvoice->booking_time= $getBooking->booking_time;
        $getInvoice->booking_date= $getBooking->booking_date;

        $getUser= $this->Api_model->getSingleRow('user', array('user_id'=>$getInvoice->user_id));

        $getInvoice->userName= $getUser->name;
        $getInvoice->userEmail= $getUser->email_id;
        $getInvoice->address= $getUser->address;

        $get_artists= $this->Api_model->getSingleRow('artist', array('user_id'=>$getInvoice->artist_id));
        $getArt= $this->Api_model->getSingleRow('user', array('user_id'=>$getInvoice->artist_id));

        $get_cat=$this->Api_model->getSingleRow('category', array('id'=>$get_artists->category_id));

        $getInvoice->ArtistName=$get_artists->name;
        $getInvoice->ArtistEmail=$getArt->email_id;
        $getInvoice->ArtistLocation=$get_artists->location;
        $getInvoice->categoryName=$get_cat->cat_name;

        $subject='FabArtist Invoice';
        $this->send_invoice($getInvoice->userEmail, $subject, $getInvoice);
        $this->send_invoice($getInvoice->ArtistEmail, $subject, $getInvoice);
        $update= $this->Api_model->updateSingleRow('booking_invoice', $where, $data);
        redirect('Admin/allInvoice');    
    }

     /*Change Status Artist*/
     public function change_status_rating()
    {
        $id=$this->input->post('id', TRUE);
        $rating_id=$this->input->post('rating_id', TRUE);

        $where = array('id'=>$rating_id);
        $data = array('status'=>$id);

        $update= $this->Api_model->updateSingleRow('rating', $where, $data);    
    }

     /*Change Status Artist*/
    public function change_status_coupon()
    {
        $id= $_GET['id'];
        $status= $_GET['status'];
        $request= $_GET['request'];
        $where = array('id'=>$id);
        $data = array('status'=>$status);

        $update= $this->Api_model->updateSingleRow('discount_coupon', $where, $data);
        redirect('Admin/coupon');    
    }


      /*Change Status Artist*/
    public function change_status_wallet()
    {
      $id= $_GET['id'];
      $artist_id= $_GET['artist_id'];
      $where = array('id'=>$id);
      $data = array('status'=>1);

      $update= $this->Api_model->updateSingleRow('wallet_request', $where, $data);

      $getArt= $this->Api_model->getSingleRow('artist_wallet', array('artist_id'=>$artist_id));
      if($getArt)
      {
        $this->Api_model->updateSingleRow('artist_wallet', array('artist_id'=>$artist_id), array('amount'=>0));
      }
      redirect('Admin/requestAmount');    
    }


    /*Change Status Artist*/
     public function change_status_category()
    {
        $id= $_GET['id'];
        $status= $_GET['status'];
        $request= $_GET['request'];
        $where = array('id'=>$id);
        $data = array('status'=>$status);

        $update= $this->Api_model->updateSingleRow('category', $where, $data);
     
        redirect('Admin/category');    
    }

     /*Change Status Artist*/
     public function change_status_skills()
    {
        $id= $_GET['id'];
        $status= $_GET['status'];
        $request= $_GET['request'];
        $where = array('id'=>$id);
        $data = array('status'=>$status);

        $update= $this->Api_model->updateSingleRow('skills', $where, $data);
     
        redirect('Admin/skills');    
    }

    public function notifaction()
    {
        $data['user']= $this->Api_model->getAllData('user');
        $data['page']='notification';

        $this -> load -> view('common/head.php');
        $this -> load -> view($this->sidebar, $data);
        $this -> load ->view('notification.php', $data);
        $this -> load -> view('common/footer.php');
    }

     /*All Appointment*/
     public function appointments()
    {
        $user_id= $_GET['id'];
        $role= $_GET['role'];
        $artist_name= $_GET['artist_name'];

      if($role==1)
      {      
        $where=array('artist_id'=>$user_id);   

          $get_appointment=$this->Api_model->getAllDataWhere($where,'artist_booking');

        $get_appointments = array();
        foreach ($get_appointment as $get_appointment) 
        {
          $get_user= $this->Api_model->getSingleRow('user', array('user_id'=>$get_appointment->user_id));

          $get_appointment->name= $get_user->name;
          $get_appointment->image= base_url().$get_user->image;
          $get_appointment->address= $get_user->address;

          array_push($get_appointments, $get_appointment);
        }

      }
      elseif($role==2)
      {
        $where=array('user_id'=>$user_id);

        $get_appointment=$this->Api_model->getAllDataWhere($where,'artist_booking');

            $get_appointments = array();
            foreach ($get_appointment as $get_appointment) 
            {
              $get_user= $this->Api_model->getSingleRow('artist', array('user_id'=>$get_appointment->artist_id));
              $get_user->image= base_url().$get_user->image;

              $get_appointment->name= $get_user->name;
              $get_appointment->image= base_url().$get_user->image;
              $get_appointment->address= $get_user->address;

              array_push($get_appointments, $get_appointment);
            } 
       }

        $data['get_appointments']= $get_appointments;
        $data['artist_name']= $artist_name;
        $data['page']='artist';
        $this -> load -> view('common/head.php');
        $this -> load -> view($this->sidebar, $data);
        $this -> load ->view('appointments.php', $data);
        $this -> load -> view('common/footer.php');
    }

    /*Get All Products*/
    public function products()
    {
        $user_id= $_GET['id'];
        $role= $_GET['role'];
        $artist_name= $_GET['artist_name'];

        $where=array('user_id'=>$user_id);

        $get_products=$this->Api_model->getAllDataWhere($where,'products');

        $data['get_products']= $get_products;
        $data['artist_name']= $artist_name;
        $data['page']='artist';
        $this -> load -> view('common/head.php');
        $this -> load -> view($this->sidebar, $data);
        $this -> load ->view('products.php', $data);
        $this -> load -> view('common/footer.php');
    }

    /*Get All Invoice*/
    public function invoice()
    {
        $user_id= $_GET['id'];
        $artist_id= $_GET['artist_id'];
        $artist_name= $_GET['artist_name'];

        $where=array('artist_id'=>$user_id);

        $getInvoice=$this->Api_model->getAllDataWhere($where,'booking_invoice');

        $getInvoices = array();
        foreach ($getInvoice as $getInvoice)
        {
          $getBooking= $this->Api_model->getSingleRow('artist_booking', array('id'=>$getInvoice->booking_id));

          $getInvoice->booking_time= $getBooking->booking_time;
          $getInvoice->booking_date= $getBooking->booking_date;

          $getUser= $this->Api_model->getSingleRow('user', array('user_id'=>$getInvoice->user_id));

          $getInvoice->userName= $getUser->name;
          $getInvoice->address= $getUser->address;

          if($getUser->image)
          {
            $getInvoice->userImage= base_url().$getUser->image;
          }
          else
          {
            $getInvoice->userImage= base_url().'assets/images/a.png';
          }

          $get_artists= $this->Api_model->getSingleRow('artist', array('user_id'=>$getInvoice->artist_id));

          $get_cat=$this->Api_model->getSingleRow('category', array('id'=>$get_artists->category_id));

          $getInvoice->ArtistName=$get_artists->name;
          $getInvoice->categoryName=$get_cat->cat_name;

          if($get_artists->image)
          {
            $getInvoice->artistImage= base_url().$get_artists->image;
          }
          else
          {
            $getInvoice->artistImage= base_url().'assets/images/a.png';
          }

          array_push($getInvoices, $getInvoice);
        }


        $data['get_invoice']= $getInvoices;
        $data['artist_name']= $artist_name;
      
        $data['page']='artist';
        $this -> load -> view('common/head.php');
        $this -> load -> view($this->sidebar, $data);
        $this -> load ->view('invoice.php', $data);
        $this -> load -> view('common/footer.php');
    }

    public function allInvoice()
    {
        $getInvoice=$this->Api_model->getAllData('booking_invoice');
        $getInvoices = array();
        foreach ($getInvoice as $getInvoice)
        {
          $getBooking= $this->Api_model->getSingleRow('artist_booking', array('id'=>$getInvoice->booking_id));

          $getInvoice->booking_time= $getBooking->booking_time;
          $getInvoice->booking_date= $getBooking->booking_date;

          $getUser= $this->Api_model->getSingleRow('user', array('user_id'=>$getInvoice->user_id));

          $getInvoice->userName= $getUser->name;
          $getInvoice->address= $getUser->address;

          if($getUser->image)
          {
            $getInvoice->userImage= base_url().$getUser->image;
          }
          else
          {
            $getInvoice->userImage= base_url().'assets/images/a.png';
          }

          $get_artists= $this->Api_model->getSingleRow('artist', array('user_id'=>$getInvoice->artist_id));

          $get_cat=$this->Api_model->getSingleRow('category', array('id'=>$get_artists->category_id));

          $getInvoice->ArtistName=$get_artists->name;
          $getInvoice->categoryName=$get_cat->cat_name;

          if($get_artists->image)
          {
            $getInvoice->artistImage= base_url().$get_artists->image;
          }
          else
          {
            $getInvoice->artistImage= base_url().'assets/images/a.png';
          }
          array_push($getInvoices, $getInvoice);
        }

        $data['a_amount']=$this->Api_model->getSum('artist_amount', 'booking_invoice');
        $data['c_amount']=$this->Api_model->getSum('category_amount', 'booking_invoice');
        $data['t_amount']=$this->Api_model->getSum('total_amount', 'booking_invoice');
        $data['p_amount']=$this->Api_model->getSumWhere('total_amount', 'booking_invoice', array('flag'=>1));
        $data['u_amount']=$this->Api_model->getSumWhere('total_amount', 'booking_invoice', array('flag'=>0));
        $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
        $data['currency_type']= $currency_setting->currency_symbol;
        $data['getInvoices']= array_reverse($getInvoices);
        $data['page']='allInvoice';
        $this -> load -> view('common/head.php');
        $this -> load -> view($this->sidebar, $data);
        $this -> load ->view('allInvoice.php', $data);
        $this -> load -> view('common/footer.php');
    }

    public function booking()
    {
      $getBooking= $this->Api_model->getAllData('artist_booking');
      $getBookings= array();

      foreach ($getBooking as $getBooking) 
      {
          $get_reviews=  $this->Api_model->getAllDataWhere(array('artist_id'=>$getBooking->artist_id,'status'=>1), 'rating');
          $review = array();
          foreach ($get_reviews as $get_reviews) 
          {
            $get_user = $this->Api_model->getSingleRow('user', array('user_id'=>$get_reviews->user_id));
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
            $get_artists=$this->Api_model->getSingleRow('artist',$where);

            $get_cat=$this->Api_model->getSingleRow('category', array('id'=>$get_artists->category_id));
          if($get_artists->image)
          {
            $getBooking->artistImage=base_url().$get_artists->image;
          }
          else
          {
            $getBooking->artistImage=base_url()."assets/images/image.png";
          }
            $getBooking->category_name=$get_cat->cat_name;
            $getBooking->artistName=$get_artists->name;
            $getBooking->artistLocation=$get_artists->location;

            $getUser= $this->Api_model->getSingleRow('user', array('user_id'=>$getBooking->user_id));
            $getBooking->userName= $getUser->name;
            $getBooking->address= $getUser->address;

            $where= array('artist_id'=>$getBooking->user_id, 'status'=>1);
            $ava_rating=$this->Api_model->getAvgWhere('rating', 'rating',$where);
          if($ava_rating[0]->rating==null)
          {
            $ava_rating[0]->rating="0";
          }
          $getBooking->ava_rating=$ava_rating[0]->rating;

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
          array_push($getBookings, $getBooking);
      }
      
        $data['getBookings']= $getBookings;
        $data['page']='allBooking';
        $this -> load -> view('common/head.php');
        $this -> load -> view($this->sidebar, $data);
        $this -> load ->view('allBooking.php', $data);
        $this -> load -> view('common/footer.php');
    }

     public function accept_booking($booking_id)
    {
      $data['booking_flag'] =1;

      $getBooking= $this->Api_model->getSingleRow('artist_booking', array('id'=>$booking_id));
      if($getBooking)
      {
        $updateBooking=$this->Api_model->updateSingleRow('artist_booking',array('id'=>$booking_id),$data);

        if($updateBooking)
        {
          $checkUser= $this->Api_model->getSingleRow('user', array('user_id'=>$getBooking->artist_id));
          $msg=$checkUser->name.': accepted your appointment.';
          $this->firebase_notification($getBooking->user_id, "Accept Appointment" ,$msg);

          redirect('Admin/booking');
        }
        else
        {
          redirect('Admin/booking');
        }
      }
      else
        {
          redirect('Admin/booking');
        }
    }

     /*Start Booking*/
    public function start_booking($booking_id)
    {
      $data['booking_flag'] =3;
      $data['start_time'] =time();

      $getBooking= $this->Api_model->getSingleRow('artist_booking', array('id'=>$booking_id));
      if($getBooking)
      {
        $updateBooking=$this->Api_model->updateSingleRow('artist_booking',array('id'=>$booking_id),$data);

        if($updateBooking)
        {
          $checkUser= $this->Api_model->getSingleRow('user', array('user_id'=>$getBooking->artist_id));
          $msg='Your booking started successfully.';
          $this->firebase_notification($getBooking->user_id, "Start Appointment" ,$msg);

          redirect('Admin/booking');
        }
        else
        {
          redirect('Admin/booking');
        }
      }
      else
      {
        redirect('Admin/booking');
      }
    }

     /*Complete Booking (End)*/
    public function end_booking($booking_id)
    {
      $data['booking_flag'] = 4;
      $data['end_time'] =time();

      $getBooking= $this->Api_model->getSingleRow('artist_booking', array('id'=>$booking_id));
      if($getBooking)
      {
        $updateBooking=$this->Api_model->updateSingleRow('artist_booking',array('id'=>$booking_id),$data);

        if($updateBooking)
        {
          $artist_id=$getBooking->artist_id;
          $user_id=$getBooking->user_id;
          $updateUser=$this->Api_model->updateSingleRow('artist',array('user_id'=>$artist_id),array('booking_flag'=>0));

          $getBooking= $this->Api_model->getSingleRow('artist_booking', array('id'=>$booking_id));
          $working_min= (float)round(abs($getBooking->start_time - $getBooking->end_time) / 60,2);
          $min_price = ($getBooking->price)/60;
          $getArtist= $this->Api_model->getSingleRow(ART_TBL, array('user_id'=>$artist_id));

          if($getArtist->artist_commission_type==1)
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
          if($commission_setting->commission_type==0)
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
          }

          $datainvoice['artist_id']= $artist_id;
          $datainvoice['artist_amount']= round($f_amount, 2);
          $datainvoice['user_id']= $user_id;
          $datainvoice['invoice_id']= strtoupper($this->api->strongToken());
          $datainvoice['booking_id']= $booking_id;
          $datainvoice['working_min']= (float)round($working_min,2);
          $datainvoice['total_amount']= round($total_amount,2);
          $datainvoice['tax']= 0;
          $currency_setting= $this->Api_model->getSingleRow('currency_setting',array('status'=>1));
          $datainvoice['currency_type']= $currency_setting->currency_symbol;
          $date= date('Y-m-d');
          $datainvoice['created_at']=strtotime($date);
          $datainvoice['updated_at']=time();

          $invoiceId= $this->Api_model->insertGetId('booking_invoice', $datainvoice);

          $getUser= $this->Api_model->getSingleRow('user', array('user_id'=>$getBooking->user_id));
          $getBooking->userName= $getUser->name;
          $getBooking->address= $getUser->address;
          $getBooking->total_amount= $total_amount;
          $getBooking->working_min= (float)$working_min;

          if($getUser->image)
          {
            $getBooking->userImage= base_url().$getUser->image;
          }
          else
          {
            $getBooking->userImage= base_url().'assets/images/image.png';
          }

          $checkUser= $this->Api_model->getSingleRow('user', array('user_id'=>$getBooking->artist_id));
          $msg='Your booking end successfully.';
          $this->firebase_notification($getBooking->user_id, "End Appointment" ,$msg);

          $dataNotification['user_id']= $getBooking->user_id;
          $dataNotification['title']= "End Appointment";
          $dataNotification['msg']= $msg;
          $dataNotification['type']= "Individual";
          $dataNotification['created_at']=time(); 
          $this->Api_model->insertGetId('notifications',$dataNotification);

          redirect('Admin/booking');
        }
        else
        {
          redirect('Admin/booking');
        }
      }
      else
      {
        redirect('Admin/booking');
      }
    }

    public function decline_booking()
    {
      $booking_id= $_GET['id'];
      $data['decline_by'] ="";
      $data['decline_reason'] ="Decline by admin";
      $data['booking_flag'] =2;

      $getBooking= $this->Api_model->getSingleRow('artist_booking', array('id'=>$booking_id));
      if($getBooking)
      {
        $updateBooking=$this->Api_model->updateSingleRow('artist_booking',array('id'=>$booking_id),$data);

        if($updateBooking)
        {
          $checkUser= $this->Api_model->getSingleRow('user', array('user_id'=>$getBooking->artist_id));
          $msg=$checkUser->name.': is decline your appointment.';
          $this->firebase_notification($getBooking->user_id, "Decline Appointment" ,$msg);

          $updateUser=$this->Api_model->updateSingleRow('artist',array('user_id'=>$getBooking->artist_id),array('booking_flag'=>0));
          redirect('Admin/booking');
        }
        else
        {
          redirect('Admin/booking');
        }
      }
      else
        {
          redirect('Admin/booking');
        }
    }

     /*Case 1 accept booking 2 start booking 3 end booking*/
    public function booking_operation()
    {
        $booking_id= $_GET['id'];
        $request= $_GET['request'];

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
           redirect('Admin/booking');
      }
    }

    /*Change Status Artist*/
     public function change_status_ticket()
     {
        $id= $_GET['id'];
        $status= $_GET['status'];
        $where = array('id'=>$id);
        $data = array('status'=>$status);

        $get_user=$this->Api_model->getSingleRow('ticket', array('id'=>$id));
        $user_id= $get_user->user_id;

        if($status==1)
        {
          $title="Ticket ".$id;
          $msg1="We are working on your issue";
          $this->firebase_notification($user_id,$title,$msg1);

          $data1['user_id']= $get_user->user_id;
          $data1['title']= $title;
          $data1['msg']= $msg1;
          $data1['type']= "Individual";
          $data1['created_at']= time();
          $this->Api_model->insertGetId('notifications',$data1);
        }
        elseif($status==2)
        {
          $title="Ticket ".$id;
          $msg1="Your issue has been resolved successfully. Please view it. The ticket is closed now.";
          $data1['user_id']= $get_user->user_id;
          $data1['title']= $title;
          $data1['msg']= $msg1;
          $data1['type']= "Individual";
          $data1['created_at']= time();

          $this->Api_model->insertGetId('notifications',$data1);
          $this->firebase_notification($user_id,$title,$msg1);
        }

        $update= $this->Api_model->updateSingleRow('ticket', $where, $data);
        redirect('Admin/ticket');      
     }

    /*Change Status Artist*/
     public function change_status_admin()
     {
        $id= $_GET['id'];
        $status= $_GET['status'];
        $where = array('id'=>$id);
        $data = array('status'=>$status);

        $update= $this->Api_model->updateSingleRow('admin', $where, $data);
        redirect('Admin/manager');      
     }


    /*Change Status Artist*/
    public function change_status_artist()
    {
        $id= $_GET['id'];
        $status= $_GET['status'];
        $request= $_GET['request'];
        $where = array('user_id'=>$id);
        $data = array('status'=>$status);

        $update= $this->Api_model->updateSingleRow('user', $where, $data);

        if($request==1)
        {
             redirect('Admin/user');
        }
        elseif ($request==2)
        {
           redirect('Admin/artists');
        }        
    }


    /*Change Status Artist*/
    public function change_status_featured()
    {
        $id= $_GET['id'];
        $status= $_GET['status'];
        $request= $_GET['request'];
        $where = array('user_id'=>$id);
        $data = array('featured'=>$status);

        $update= $this->Api_model->updateSingleRow('artist', $where, $data);

        redirect('Admin/artists');      
    }

     /*Change Status Artist*/
    public function artist_approve()
    {
      $id= $_GET['id'];
      $where = array('user_id'=>$id);
      $data = array('approval_status'=>1);

      $update= $this->Api_model->updateSingleRow('user', $where, $data);
      redirect('Admin/artists');      
    }

   public function login()
    {
      $email= $this->input->post('email', TRUE);
      $password=$this->input->post('password', TRUE);

      $data['email']= $email;
      $data['password']= $password;
      $sess_array=array();
      $getdata=$this->Api_model->getSingleRow('admin',$data);
      if($getdata)
      {           
        if($getdata->status==1)
        {
          $this->session->unset_userdata($sess_array);
          $sess_array = array(
           'name' => $getdata->name,
           'id' => $getdata->id,
         );

         $this->session->set_userdata( $sess_array);
          $dataget['get_data'] =$getdata;
          $dataget['see_data'] =$sess_array;
          redirect('Admin/home');    
        }
        else
        {
          $this->session->set_flashdata('block', 'You action has been block. Contact to admin.');
            redirect('');
        }
      }
      else
      {
        $this->session->set_flashdata('msg', 'Please enter valid Username or Password');
        redirect('');
      }
    }

    /* View Artist Profile*/
    public function profile_artist()
    {
        $user_id= $_GET['id'];

        $artist=$this->Api_model->getSingleRow('artist',array('user_id'=>$user_id));

        $data['user']= $artist;
        $data['page']='artist';
        $this -> load -> view('common/head.php');
        $this -> load -> view($this->sidebar, $data);
        $this -> load ->view('profile.php', $data);
        $this -> load -> view('common/footer.php');
    }
    public function signup()
    {
        $this -> load -> view('common/head.php');
        $this -> load ->view('signup.php');
        $this -> load -> view('common/footer.php');
    }

    public function forgotpassword()
    {
        $this -> load -> view('common/head.php');
        $this->load ->view('forgotpassword.php');
        $this -> load -> view('common/footer.php');
    }

    public function logout()
    {
      $this->session->sess_destroy();         
        redirect('../', 'refresh');
    }

    public function payufailure()
    {
      $datas['artist_id'] = $_GET['getUserId'];
      $datas['request_id'] = $_GET['getUserId'];
      $datas['amount'] = $_GET['getpost'];
      $datas['status'] = 0;
      $datas['created_at'] = time();
      $data['page']='artist';
      $this->Api_model->insertGetId('paymentHistory',$datas);

      $this->Api_model->updateSingleRow('wallet_request', array('id'=>$datas['request_id']), array('status'=>0));

      $this -> load -> view('common/head.php');
      $this -> load -> view($this->sidebar, $data);
      $this -> load ->view('payufailure.php', $data);
      $this -> load -> view('common/footer.php');
    }


    public function payusuccess()
    {
      $datas['artist_id'] = $_GET['getUserId'];
      $datas['request_id'] = $_GET['request_id'];
      $datas['amount'] = $_GET['getpost'];
      $datas['status'] = 1;
      $datas['created_at'] = time();
      $data['page']='artist';
      $this->Api_model->insertGetId('paymentHistory',$datas);

      $this->Api_model->updateSingleRow('wallet_request', array('id'=>$datas['request_id']), array('status'=>1));
      $this -> load -> view('common/head.php');
      $this -> load -> view($this->sidebar, $data);
      $this -> load ->view('payusuccess.php', $data);
      $this -> load -> view('common/footer.php');
    }
    /*Firebase for notification*/
    public function firebase_notification($user_id,$title,$msg1)
    {
     
      $get_data= $this->Api_model->getSingleRow('user',array('user_id'=>$user_id));

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
                  'to'        => $registrationIds,
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
        else
        {
            
        }
    }

    public function firebase()
    {
  
      $mobile=$this->input->post('mobile');
      $title=$this->input->post('title');
      $msg1=$this->input->post('msg');

        for($i=0;$i<count($mobile);$i++)
        {
            $user = $this->db->where('email_id',$mobile[$i])->get('user')->row();
            $deviceToken = $user->device_token;
            $mobile_sent = $mobile[$i];
            $title_sent = $title;
            $msg_sent = $msg1;

            $user=$this->Api_model->getSingleRow('user',array('email_id'=>$mobile_sent));

            $data['user_id']= $user->user_id;
            $data['title']= $title;
            $data['msg']= $msg1;
            $data['type']= "Individual";
            $data['created_at']= time();

            $this->Api_model->insertGetId('notifications',$data);

          if($user->role==1)
          {
             $API_ACCESS_KEY= ARTIST_FIREBASE_KEY;
          }
          else
          {
             $API_ACCESS_KEY= USER_FIREBASE_KEY;
          }

          $registrationIds =$user->device_token;

          $msg = array
              (
                  'body'    => $msg1,
                  'title'   => $title,
                  'icon'    => 'myicon',/*Default Icon*/
                  'sound'   =>  'mySound'/*Default sound*/
            );
          $fields = array
              (
                  'to'        => $registrationIds,
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
        //return $result;
    } 

    /**********************Packages*************************/

    /*Show Packages*/
    public function packages()
    {   
        $data['packages']=  $this->Api_model->getAllData('packages');
        $data['page']='packages';
        $this -> load -> view('common/head.php');
        $this -> load -> view($this->sidebar, $data);
        $this -> load ->view('packages.php', $data);
        $this -> load -> view('common/footer.php');
    }

    public function add_packages()
    {   
        $data['page']='packages';
        $this -> load -> view('common/head.php');
        $this -> load -> view($this->sidebar, $data);
        $this -> load ->view('add_packages.php', $data);
        $this -> load -> view('common/footer.php');
    }

    public function packageAction()
    {   
       $data['title']=$this->input->post('title');
       $data['description']=$this->input->post('description');
       $data['price']=$this->input->post('price');
       $data['subscription_type']=$this->input->post('type');
       $this->Api_model->insertGetId('packages',$data);
       redirect('Admin/packages','refresh');
    }    

     /*Change Status Artist*/
    public function change_status_package()
    {
      $id= $_GET['id'];
      $status= $_GET['status'];
      $where = array('id'=>$id);
      $data = array('status'=>$status);

      $update= $this->Api_model->updateSingleRow('packages', $where, $data);

      redirect('Admin/packages');    
    }

    public function edit_package($id)
    {   
        $data['get_package']=$this->Api_model->getSingleRow('packages', array('id'=>$id));
        $data['page']='packages';
        $this -> load -> view('common/head.php');
        $this -> load -> view($this->sidebar, $data);
        $this -> load ->view('edit_packages.php', $data);
        $this -> load -> view('common/footer.php');
    }

    public function editPackageAction()
    {   
       $data['title']=$this->input->post('title');
       $id=$this->input->post('id');
       $data['description']=$this->input->post('description');
       $data['price']=$this->input->post('price');
       $data['subscription_type']=$this->input->post('type');
       $this->Api_model->updateSingleRow('packages',array('id'=> $id),$data);
       redirect('Admin/packages','refresh');
    } 
}