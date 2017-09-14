<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* load the MX_Loader class */
require APPPATH."third_party/MX/Controller.php";

class MY_Controller extends MX_Controller{
	
	public $prefix;
	private $parameters;
	private $rest_parameters;
	protected $autoload_helpers = array();
	protected $autoload_libraries = array();
	
	protected $id_client;
	protected $id_user;
	
	function MY_Controller(){
		parent::__construct();
		
		$this->prefix = config_item('db_prefix');
		$this->session_check();
		
		$this->id_client = $this->session->userdata('id_client');
		$this->id_user	= $this->session->userdata('id_user');
		
		//Timezone
		$timezone_default = config_item('timezone_default');
		if(!empty($get_opt['timezone_default'])){
			$timezone_default = $get_opt['timezone_default'];
		}
		date_default_timezone_set($timezone_default);
		
		//RESTFUL
		$this->set_rest_value('id_client', $this->id_client);
		$this->set_rest_value('id_user', $this->id_user);
				
		// XSS Filtering
		if(isset($_POST)){
			foreach($_POST as $key=>$value){
				$_POST[$key] = $this->input->post($key,true);
			}
		}

		$this->default_value();
	}
	
	function default_value()
	{
		$this->set_value('base_url', base_url());
	}
	
	function set_value($key,$value)
	{
		$this->parameters[$key] = $value;
	}
	
	function get_parameters()
	{
		return $this->parameters;
	}

	function session_check()
	{
		$this->load->helper($this->autoload_helpers);
		if($this->session->userdata('id_user') != '' && $this->session->userdata('id_client') != '')
		{
			return;
		}
		else 
		{
			//redirect('homepage', 'refresh');
		}
	}

	function rest_server($d)
	{
		$config = array(
			'server' 	=> config_item('rest_server_url').$d,  
		    'http_user' => config_item('rest_username'),  
		    'http_pass' => config_item('rest_password'),  
		    'http_auth' => 'basic'
		);
		
		$this->rest->initialize($config);
	}

	function set_rest_value($key,$value)
	{
		$this->rest_parameters[$key] = $value;
	}
	
	function get_rest_parameters()
	{
		return $this->rest_parameters;
	}
	
}
