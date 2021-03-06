<?php
/*********
* Author: 
* Date  : 
* Modified By: 
* Modified Date:
* 
* Purpose:
*  Controller For Registration Page 
* 
* 
*/

require_once(APPPATH.'libraries/recaptchaLib/recaptchalib.php');
include(APPPATH.'controllers/base_controller.php');

class Register extends Base_controller
{
    
    
    public function __construct()
     {
        try
        {
            parent::__construct();
            parent::_non_accessible_by_logged(); // put this code on those pages which are not accessable by logged in user
			# loading reqired model & helpers...
            $this->load->model('users_model');
        }
        catch(Exception $err_obj)
        {
            show_error($err_obj->getMessage());
        }  

    }

    
    public function index($s_member_type = '') 
    {
        try
        {
			      
            $posted=array();
            $this->data["posted"]=$posted;/*don't change*/    
            $data = $this->data;      
			$data['error_zone'] = '';
			$data['error_title'] ='';
			$data['error_password'] = '';
			$data['error_cpassword'] = '';
            #pr($data);
            $selected_lang = $this->ses_user['current_language'];
            parent::_set_title('::: COGTIME Xtian network :::');
            parent::_set_meta_desc('');
            parent::_set_meta_keywords('');
		
			
            parent::_add_js_arr( array( 'js/jquery.autofill.js',
										'js/lightbox.js',
										
										//'js/jquery-1.5.2.js',
										//'js/no-conflict.js',
                                        'js/jquery.dd.js',
										
										
										));
            parent::_add_css_arr( array('css/recaptcha.css', 'css/dd.css') );
            
           
			
			if($this->input->post('is_submitted') == 'Y')
			 {
			    #pr($_POST);
				
				$posted["title"]=trim($this->input->post("title"));
				$posted["txt_email"]=trim($this->input->post("txt_email"));
				$posted["txt_password"]=trim($this->input->post("txt_password"));
                $posted["txt_cpassword"]=trim($this->input->post("txt_cpassword"));
				$posted["txt_first_name"]=ucfirst(trim($this->input->post("txt_first_name")));
				$posted["txt_last_name"]=ucfirst(trim($this->input->post("txt_last_name")));
				$posted["gender"]=ucfirst(trim($this->input->post("gender")));
				$posted["time_zone"]=trim($this->input->post("time_zone"));
				
						
				$posted["s_ssc"]=trim(decrypt($this->input->post("ssc")));
                $posted["s_security_code"]=trim($this->input->post("security_code"));
              	$posted["txt_terms_cond"]=intval($this->input->post("txt_terms_cond"));
				
				$posted["txt_chat_display_name"]=ucfirst(trim($this->input->post("txt_chat_display_name")));
				
				
				//pr($posted);
               
				$this->form_validation->set_message('required', "Please provide"." %s");
				$this->form_validation->set_message('valid_email', "must contain a valid email address.");
				//$this->form_validation->set_message('matches', "* Password verification failed.");
				$this->form_validation->set_message('required', '* Required Field.');	// added *required message
				
				
				$this->form_validation->set_rules('txt_email', 'Email', 'trim|required|valid_email|callback_email_available'); 
			//	$this->form_validation->set_rules('txt_password', "Password", 'trim|required');
			//	$this->form_validation->set_rules('txt_cpassword', "Confirm password", 'trim|required|matches[txt_password]');
				$this->form_validation->set_rules('txt_first_name', "Confirm password", 'trim|required');
				$this->form_validation->set_rules('txt_last_name', "Confirm password", 'trim|required');
				$this->form_validation->set_rules('gender', "Gender", 'trim|required');
				//$this->form_validation->set_rules('time_zone', 'time_zone', 'trim|callback_timezone_check'); 
				
				$this->form_validation->set_rules('txt_chat_display_name', 'txt_chat_display_name', 'trim|required|callback_check_availability'); 
				
				
				
				if($posted["txt_password"] == ''){
					$data['error_password'] = "* Required Field.";
				}
				if($posted["txt_cpassword"] == ''){
					$data['error_cpassword'] = "* Required Field.";
				}
				if(($posted["txt_password"] != $posted["txt_cpassword"]) && ($posted["txt_password"] != '' || $posted["txt_cpassword"] != '') ){
					$data['error_cpassword'] = "* Password verification failed.";
				}
				
				
				if($posted["s_ssc"]!=$posted["s_security_code"])
                {
                    $this->form_validation->set_rules('security_code', 'Verification Code','trim|required|matches[ssc]');
                }
				
				if($posted["gender"]=='-1')
                {
                   // $this->form_validation->set_rules('gender', 'Gender','trim|required');
				    $data['error_gender'] = '* Required Field.';
                }
				
				if($posted["title"]=='-1')
                {
                     $data['error_title'] = '* Required Field.';
					 
                }
				
				if($posted["time_zone"]== '-1')
                {
                   $data['error_zone'] = '* Required Field.';
                }
				
				if($posted["txt_terms_cond"] != 1)
                {
                   $data['error_terms_cond'] = '* Required Field.';
                }
				   
				
				
				$this->form_validation->set_rules('txt_terms_cond', 'Terms & Conditions', 'required');
								
		
				if ($this->form_validation->run() == FALSE || $data['error_zone'] != '' || $data['error_title']!= '' || $data['error_password'] !='' || $data['error_password'] != '')
				{
				    ////////Display the add form with posted values within it////
                    $this->data["posted"]=$posted;/*don't change*/
					
				}
				else
				{
				  $info = array();
				  
				  $info['s_email'] = $posted['txt_email'];
				  $info['s_password'] = get_salted_password($posted['txt_password']); 
				  $info['s_first_name'] = get_formatted_string($posted['txt_first_name']);
				  $info['s_last_name'] = get_formatted_string($posted['txt_last_name']);
				  $info['s_time'] 	= get_formatted_string($posted["time_zone"]);
				  $info['e_gender'] 	= get_formatted_string($posted["gender"]);
				  $info['e_title'] 	= get_formatted_string($posted["title"]);
				 
				 
				 
				  $activation_random             = md5(rand(1000, 9999).time());
				  
                  $info['s_verification_code']  = $activation_random;
                  $info['i_user_type'] = 1; // member
				  $info['i_status'] = 2; //approved // Pending
                  $info['dt_created_on'] = get_db_datetime();
				  $info['s_profile_url_suffix']   = get_unique_profile_name($info['s_first_name']);
				  $info['s_tweet_id'] =   get_unique_twiter_profile_name($info['s_first_name'],$info['s_last_name']);
				  
				  
				  ### added new for chat
				  $info['s_chat_display_name'] = get_formatted_string($posted['txt_chat_display_name']); 
				 
				 //  pr($info);
				  $USER_ID = $this->users_model->sign_up($info);
				  
				  
				  //echo $this->db->last_query();
				  # pr($info); 
				// echo $USER_ID; exit;
				  
				  if($USER_ID > 0)
				  {
				          
							  
						## inserting into user alert table
						$this->load->model('user_alert_model');
						$user_alert['i_user_id'] = $USER_ID;
						$user_alert['dt_created_on'] = get_db_datetime();
						$_ret = $this->user_alert_model->insert($user_alert);
						
						## end ##
						
						//EMAIL SENDING CODE.[start]
						
																	
						$this->load->model('mail_contents_model');
						$mail_info = $this->mail_contents_model->get_by_name("registration_en");
						$body = htmlspecialchars_decode($mail_info['body'], ENT_QUOTES);
						
						
						
						$activation_link = base_url().'signup-confirm/'. $USER_ID .'/'.$activation_random;
						$body = sprintf3( $body, array('activation_link'=>$activation_link,
								   'email'=>$info["s_email"],
								   'password'=>$posted["txt_password"],
								   'member_name'=>$info["s_first_name"]) );
						
						
						//echo $body;
								   
						$arr['subject'] 	= htmlspecialchars_decode($mail_info['subject'], ENT_QUOTES);
						$arr['to']         = $info["s_email"];
						
						#$arr['cc']         = "123deb321@gmail.com";
						$arr['bcc']    = '123deb321@gmail.com';
						$arr['from_email'] = 'no-reply@cogtime.com';
						$arr['from_name'] = 'admin@cogtime.com';
						$arr['message'] = $body;
								   #pr($arr); exit;
							
									send_mail($arr);
							   
							   //EMAIL SENDING CODE.[end]
												   
							   unset($info,$posted);
							   //$SUCCESS_PG = base_url() ."signup-success.html";
							   //show_dialog('terms_condition');
							  // set_success_msg('You have Successfully Registered.');
							  
							  ## AUTO LOGIN for user ##
							  
							  /*$info = $this->users_model->fetch_this($USER_ID);
							  //pr($info,1);;
							  $this->session->set_userdata('login_referrer', ''); 
							  $this->session->set_userdata('loggedin', true);
							  $this->session->set_userdata('user_id', encrypt($USER_ID));
							  $this->session->set_userdata('username', $info["s_first_name"]);
							  $this->session->set_userdata('user_type',$info["i_user_type"]);
							  $this->session->set_userdata('email', $info["s_email"]);
							  $this->session->set_userdata('user_lastname', $info["s_last_name"]);
							  $this->session->set_userdata('is_admin', $info["i_is_admin"]);
							  
							  #### first login show salavation message ###
							  $this->session->set_userdata('first_login', 'yes');
							  $this->users_model->set_user_online($USER_ID, $_SERVER['REMOTE_ADDR']);
									   
							  
							 // $this->set_user_online($USER_ID, $_SERVER['REMOTE_ADDR']);
							  
							  $SUCCESS_PG = base_url().'my-wall.html' ;*/#."inscription-success.html";
							  
							  
							  
							  header("location:".base_url().'successfully-registered');
//exit;
							   
							   
							   
				  }
				  
				        
				  
				  
				  
				}
			 }
			  			
			 /////captcha///
        
            $this->load->library('antispam');
            $configs = array(
                    'img_path' => './captcha/',
                    'img_url' => base_url() . 'captcha/',
                    'font_path'     =>  './fonts/',
                    'font_name'        => 'VeraBd.ttf',
                    'img_height' => '35',
                    
                    'font_size'        =>     15
                    
                );            
            $data["captcha"] = $this->antispam->get_antispam_image($configs);
			
			//pr($data["captcha"]);
			

        /* ******************** Generating recaptcha html ********************* */
            $data['recaptcha_html'] = recaptcha_get_html($this->config->item('recaptcha_public_key'));
        /* ********************************************************************* */
		
			### fetch private policy and terms and conditions
			
			//$data['cms_data']= ;
			
            # view file...
            $VIEW = "register.phtml"; 
            parent::_render($data, $VIEW);
		}
        catch(Exception $err_obj)
        {
           
        } 

    }   
	
	public function email_available($str)
    {
        $this->load->model('users_model');
        
        if($str!= '' && $this->users_model->email_exists($str,''))
        {
            $this->form_validation->set_message('email_available', "* E-mail address already exists.");
            return FALSE;
        }
        else
        {
            return TRUE;
        }
        
        
    }  
	
	public function check_availability($str)
    {
        $this->load->model('users_model');
        
        if($str!= '' && $this->users_model->Chatname_exists($str,''))
        {
            $this->form_validation->set_message('check_availability', "* Chat Name address already exists, please provide an unique name! ");
            return FALSE;
        }
        else
        {
            return TRUE;
        }
        
        
    }  
	
	
	public function registration_success()
	{
		$posted=array();
		$this->data["posted"]=$posted;/*don't change*/    
		$data = $this->data;      
		$data['error_zone'] = '';
		$data['error_title'] ='';
		$data['error_password'] = '';
		$data['error_cpassword'] = '';
		#pr($data);
		$selected_lang = $this->ses_user['current_language'];
		parent::_set_title('::: COGTIME Xtian network :::');
		parent::_set_meta_desc('');
		parent::_set_meta_keywords('');
		
		
		parent::_add_js_arr( array( 'js/jquery.autofill.js',
									'js/lightbox.js',
									
									//'js/jquery-1.5.2.js',
									//'js/no-conflict.js',
									'js/jquery.dd.js',
									
									
									));
		parent::_add_css_arr( array('css/recaptcha.css', 'css/dd.css') );
		$VIEW = "registration_success.phtml"; 
        parent::_render($data, $VIEW);
	}
	
	public function signup_confirm($id,$code)
	{
		$sql	= "UPDATE {$this->db->USERS} SET i_status=1 WHERE id='".$id."' AND s_verification_code='".$code."'";
		$this->db->query($sql);
		$info = $this->users_model->fetch_this($id);
		$USER_ID = $id;
		if($info['i_status']==1)
		{
		
			## AUTO LOGIN for user ##
			
			//pr($info,1);;
			$this->session->set_userdata('login_referrer', ''); 
			$this->session->set_userdata('loggedin', true);
			$this->session->set_userdata('user_id', encrypt($USER_ID));
			$this->session->set_userdata('username', $info["s_first_name"]);
			$this->session->set_userdata('user_type',$info["i_user_type"]);
			$this->session->set_userdata('email', $info["s_email"]);
			$this->session->set_userdata('user_lastname', $info["s_last_name"]);
			$this->session->set_userdata('is_admin', $info["i_is_admin"]);
			
			#### first login show salavation message ###
			$this->session->set_userdata('first_login', 'yes');
			$this->users_model->set_user_online($USER_ID, $_SERVER['REMOTE_ADDR']);
			
			$this->session->set_userdata('upassword', $info["s_password"]);
			$this->session->set_userdata('IMuserid', ($info["id"]));
				
		    $this->session->set_userdata('unique_username', $info["s_profile_url_suffix"]);
			$this->session->set_userdata('display_username', $info["s_chat_display_name"]);
				
				//$_SESSION['username'] = 'jhon';
			### generating five fruits
			$this->load->model('bible_fruits_model');
			$this->bible_fruits_model->generate_fruit_list_per_user_id_date();
			### generating five fruits
			
			$this->session->set_userdata('is_first_login_checked', 'false');
				
			$this->load->model('mail_contents_model');
			$mail_info = $this->mail_contents_model->get_by_name("acknowledgement");
			$body = htmlspecialchars_decode($mail_info['body'], ENT_QUOTES);
			
			$body = sprintf3( $body, array('email'=>$info["s_email"],
										 /*'password'=>$posted["txt_password"],*/
										 'member_name'=>$info["s_first_name"],
										 'url'=>base_url()
										 ) );
				 
				 
			//echo $body;
										 
			$arr['subject'] 	= htmlspecialchars_decode($mail_info['subject'], ENT_QUOTES);
			$arr['to']         = $info["s_email"];
			$arr['bcc']        = 'aradhana.online19@gmail.com';
			$arr['from_email'] = 'no-reply@cogtime.com';
			$arr['from_name'] = 'admin@cogtime.com';//$this->site_settings_model->get('s_mail_from_name');
			$arr['message'] = $body;
			//pr($arr); exit;
			
			send_mail($arr);	   
			// $this->set_user_online($USER_ID, $_SERVER['REMOTE_ADDR']);
			$SUCCESS_PG = base_url().'my-wall.html' ;#."inscription-success.html";
			
			header("location:".$SUCCESS_PG);
		}
		else
		{
			header("location:".base_url());
		}
	}
	
}   // end of controller...

