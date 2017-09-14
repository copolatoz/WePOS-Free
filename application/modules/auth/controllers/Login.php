<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends MX_Controller {
	
	function __construct(){
		parent::__construct();
		$this->load->model('mdl_login', 'm');
	}

	public function index()
	{
		if(!empty($_POST)){
			$this->submit();
			die();
		}
		
		if($this->session->userdata('id_user') != '' && $this->session->userdata('client_id')!=''){ redirect('backend'); }
		
		$data['title']				=	'Login | '.config_item('program_name');
		$data['meta_description'] 	=	config_item('program_name');
		$data['meta_keywords']		=	config_item('program_name');
		$data['meta_author']		=	config_item('program_author');
		$data['program_name']		=	config_item('program_name');
		
		$opt_val = array(
			'use_login_pin'
		);
		
		$get_opt = get_option_value($opt_val);
		
		if(!empty($get_opt['use_login_pin'])){
			$this->load->view('login-pin', $data);
		}else{
			$this->load->view('login', $data);
		}	
		//$this->output->enable_profiler(TRUE);
	}
		
	public function submit()
	{		
		$user_pin = $this->input->post('loginUsernamePin', true);
		$type_login = $this->input->post('type_login', true);
		$username = $this->input->post('loginUsername', true);
    	$password = $this->input->post('loginPassword', true);
		if($type_login == 'pin'){
			$r = $this->m->submit_pin($user_pin);
		}else{
			$r = $this->m->submit($username, $password);
		}
        
        if($r['count']==1)
        {
            $this->reg_session($r['data']);
			$r['success'] = true;
        }
        else
        {
            $r['success'] = false;
            $r['info'] = 'Login Failed. Try Again!';
			$r['errors'] = array('reason'=>'Login Failed. Try Again.');
        }
		
		die(json_encode($r));
	}
	
	public function logout()
	{
		$this->unreg_session();
		redirect('login');
	}
	
	private function reg_session($d)
	{
		$data = array(
			'id_user'			=>	$d->id_user,
			'client_id'				=>	$d->client_id,
			'client_name'			=>	$d->client_name,
			'client_address'		=>	$d->client_address,
			'client_phone'		=>	$d->client_phone,
			'client_fax'		=>	$d->client_fax,
			'client_email'		=>	$d->client_email,
			'client_code'			=>	$d->client_code,
			'client_logo'			=>	$d->client_logo,
			'client_structure_id'	=>	$d->client_structure_id,
			'client_structure_name'	=>	$d->client_structure_name,
			'client_unit_id'		=>	$d->client_unit_id,
			'client_unit_name'	=>	$d->client_unit_name,
			'client_unit_code'	=>	$d->client_unit_code,
			'user_username'		=>	$d->user_username,
			'user_fullname'		=>	$d->user_fullname,
			'user_firstname'	=>	$d->user_firstname,
			'user_lastname'		=>	$d->user_lastname,
			'user_pin'			=>	$d->user_pin,
			'role_id'			=>	$d->role_id,
			'role_name'			=>	$d->role_name
		);
		$this->session->set_userdata($data);
	}
	
	private function unreg_session()
	{
		$data = array(
			'id_user',
			'client_id',
			'client_name',
			'client_address',
			'client_phone',
			'client_fax',
			'client_email',
			'client_code',
			'client_logo',
			'client_structure_id',
			'client_structure_name',
			'client_unit_id',
			'client_unit_name',
			'client_unit_code',
			'user_username',
			'user_fullname',
			'user_firstname',
			'user_lastname',
			'user_pin',
			'role_id',
			'role_name'			
		);
		$this->session->unset_userdata($data);
	}
	
}