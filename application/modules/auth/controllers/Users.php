<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'/libraries/REST_Controller.php';

class Users extends REST_Controller
{
    
	function __construct()
	{
		parent::__construct();
		$this->load->model('mdl_users', 'm');	
	}
	    
	function init_post()
    {
		
		$dataModules		= $this->m->getMenuModules($this->input->post('role_id', true));
        $shortcutModules	= $this->m->getShortcutModules($this->input->post('user_id', true));
        $quickModules		= $this->m->getQuickModules($this->input->post('user_id', true));
        $desktopConfig		= $this->m->desktopConfig($this->input->post('user_id', true));
        $userData			= $this->m->userData($this->input->post('user_id', true));
			
		if(empty($userData->avatar)){
			$userData->avatar = "default.png";
		}
		$user = array(
			"username"	=> $userData->user_username,
			"email"		=> $userData->user_email,
			"fullname"	=> $userData->user_fullname,
			"firstname"	=> $userData->user_firstname,
			"lastname"	=> $userData->user_lastname,
			"avatar"	=> $userData->avatar
		);
		
		$data = array(
			'modules' 	=> $dataModules,
			'shortcut' 	=> $shortcutModules,
			'quick'		=> $quickModules,
			'desktop'	=> $desktopConfig,
			'user'		=> $user
		);
        if($data)
        {
            $this->response($data, 200);
        }
        else
        {
            $this->response(array('error' => 'Couldn\'t Initializing User Data!'), 404);
        }
		
    }
    
}