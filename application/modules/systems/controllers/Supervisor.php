<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Supervisor extends MY_Controller {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('model_supervisor', 'spv');
	}
	
	/*SERVICES*/
	public function verify()
	{
		$this->table = $this->prefix.'supervisor';				
				
		$session_user = $this->session->userdata('user_username');		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}		
		
		$spv = array(
			'username'	=> '',
			'password'	=> '',
			'user_pin'	=> '',
			'pin_mode'	=> '',
			'verifyMode'=> 1, //using username + password, 0 -> just using username
			'access'	=> '', //if empty -> just verify user
			'log'		=> 0, //1 -> save to log make sure there a data to save
			'data'		=> '', //json
			'ref_id_1'		=> '', //text
			'ref_id_2'		=> '' //text
		);
		
		$username = $this->input->post('username', true);
		$password = $this->input->post('password', true);
		$user_pin = $this->input->post('user_pin', true);
		$pin_mode = $this->input->post('pin_mode', true);
		$verifyMode = $this->input->post('verifyMode', true);
		
		$spv['verifyMode'] = $verifyMode;
		$spv['username'] = $username;
		$spv['password'] = $password;
		$spv['user_pin'] = $user_pin;
		$spv['pin_mode'] = $pin_mode;
		
		if(empty($pin_mode)){
				
			if(empty($username)){
				$r = array('success' => false, 'info' => 'User not found!');
				echo json_encode($r);
				die();
			}else{
				$spv['username'] = $username;
			}
			
			if(!empty($verifyMode)){
				$spv['verifyMode'] = $verifyMode;
				
				if(empty($password)){
					$r = array('success' => false, 'info' => 'Requirement a Password!');
					echo json_encode($r);
					die();
				}else{
					$spv['password'] = $password;
				}
			}else{
				$spv['verifyMode'] = 0;
			}
		}
		
		$spv['access'] = $this->input->post('access', true);
		$spv['log'] = $this->input->post('log', true);
		$spv['data'] = $this->input->post('data', true);
		$spv['ref_id_1'] = $this->input->post('ref_id_1', true);
		$spv['ref_id_2'] = $this->input->post('ref_id_2', true);
		if(!empty($spv['log']) AND empty($spv['data'])){
			$r = array('success' => false, 'info' => 'Requirement a log data!');
			echo json_encode($r);
			die();
		}
		
		//check if user is a supervisor
		$verify = $this->spv->verify($spv);
		if(!empty($verify)){
						
			$r = array('success' => false, 'info' => 'Verify Failed!', 'confirm' => $verify);
			if(!empty($verify['confirm'])){
				if($verify['confirm'] == true){
					$r = array('success' => true, 'confirm' => $verify);
				}
			}
			
		}else{
			$r = array('success' => false, 'info' => 'Verify Failed!', 'confirm' => $spv);
		}
		
		die(json_encode($r));
	}
	
}