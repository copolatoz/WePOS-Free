<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class UserProfile extends MY_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('systems/model_userdata', 'm');
	}
	
	/*SERVICES*/
	public function updateProfile()
	{
		$this->table = $this->prefix.'users';				
		$id_user = $this->session->userdata('id_user');
		
		$firstname = $this->input->post('firstname');
		$lastname = $this->input->post('lastname');
		$email = $this->input->post('email');
		$phone = $this->input->post('phone');
		$mobile = $this->input->post('mobile');
		$address = $this->input->post('address');
		$user_pin = $this->input->post('user_pin');
		$change_pin = $this->input->post('change_pin');
		
		//password
		$old_pass = $this->input->post('old_pass');
		$new_pass = $this->input->post('new_pass');
		$pass_confirm = $this->input->post('pass_confirm');
		
		if(!empty($old_pass)){
		
			if((empty($new_pass) OR empty($pass_confirm)) OR ($new_pass != $pass_confirm)){
				$r = array('success' => false, 'info' => 'Update Password Failed!');
				die(json_encode($r));
			}
		}
			
		$r = '';
		$var = array('fields'	=>	array(
				'user_firstname'=> 	$firstname,
				'user_lastname' => 	$lastname,
				'user_email'	=>	$email,
				'user_phone'	=>	$phone,
				'user_mobile'	=>	$mobile,
				'user_address'	=>	$address
			),
			'table'			=>  $this->table,
			'primary_key'	=>  'id'
		);
		
		if(!empty($old_pass)){
			if(!empty($new_pass)){
				$var['fields']['user_password'] = md5($new_pass);
				
				//check if valid old-pass
				$this->db->from($this->table);
				$this->db->where('user_password', md5($old_pass));
				$this->db->where('id',$id_user);
				$getvalidPass = $this->db->get();
				if($getvalidPass->num_rows() == 0){
					$r = array('success' => false, 'info' => 'Wrong Old Password!');
					die(json_encode($r));
				}
			}
		}
		
		if(!empty($change_pin)){
			
			for($i=0; $i<10; $i++){
				//check have pin
				$user_pin = rand(1111,9999);
				//$user_pin = rand(1111,9999).rand(1111,9999);
				$this->db->from($this->table);
				$this->db->where('user_pin', $user_pin);
				$getuser_pin = $this->db->get();
				if($getuser_pin->num_rows() == 0){
					$i += 10;
				}
			}
			
			$var['fields']['user_pin'] = $user_pin;
			
		}
		
		//UPDATE
		$this->lib_trans->begin();
			$update = $this->m->save($var, $id_user);
		$this->lib_trans->commit();
				
		//GET USER
		$this->db->select("user_username as username, user_email as email, 
							user_firstname as firstname, user_lastname as lastname, avatar, user_phone as phone, user_mobile as mobile,
							user_address as address, user_pin
						", false);
		$this->db->from($this->table);
		$this->db->where('id',$id_user);
		$getUser = $this->db->get();
		
		if($update AND !empty($getUser->num_rows()) > 0)
		{  
			$dtUser = $getUser->row_array();
			$dtUser['fullname'] = $dtUser['firstname'].' '.$dtUser['lastname'];
			$r = array('success' => true, 'id' => $id_user, 'user' => $dtUser);
			
			$r['info'] = 'Profile Updated!';
			
			if(!empty($new_pass)){
				$r['info'] = 'Password been Changed!';
			}
		}  
		else
		{  
			$r = array('success' => false, 'info' => 'Update Profile Failed!');
		}
		//print_r($r);
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function saveWallpaper()
	{
		$this->table = $this->prefix.'users_desktop';				
		$session_user_id = $this->session->userdata('id_user');
		$session_client_id = $this->session->userdata('client_id');
		
		$wallpaper_img = $this->input->post('wallpaper_img', true);	
		$stretch = $this->input->post('stretch', true);	
		
		if(empty($stretch)){
			$stretch = 'false';
		}
		
		$stretch_save = 0;
		if($stretch == 'true'){
			$stretch_save = 1;
		}
		
		$data_update = array(
			'wallpaper'	=> $wallpaper_img,
			'wallpaperStretch'	=> $stretch_save
		);
		
		//delete data (TEMP)
		$this->db->where("user_id IN (".$session_user_id.")");
		$q = $this->db->update($this->table, $data_update);
		
		if($q)  
        {  
            $r = array('success' => true); 
        }else
        {  
            $r = array('success' => false, 'info' => 'Save Wallpaper User Failed!'); 
        }
		die(json_encode($r));
	}
	
}
