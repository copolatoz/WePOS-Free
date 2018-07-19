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
		$this->view_multiple_store	= $this->session->userdata('view_multiple_store');
		$this->timezone_default	= $this->session->userdata('timezone_default');
		
		//Timezone
		if(!empty($this->timezone_default)){
			$timezone_default = $this->timezone_default;
		}else{
			$timezone_default = config_item('timezone_default');
		}
		date_default_timezone_set($timezone_default);
		
		if(!empty($this->view_multiple_store)){
			
			$this->client_ip	= $this->session->userdata('client_ip');
			$this->mysql_user	= $this->session->userdata('mysql_user');
			$this->mysql_pass	= $this->session->userdata('mysql_pass');
			$this->mysql_port	= $this->session->userdata('mysql_port');
			$this->mysql_database	= $this->session->userdata('mysql_database');
			
			
			if($this->client_ip == '127.0.0.1'){
				$this->client_ip = 'localhost';
			}
			if($this->mysql_port == ''){
				$this->mysql_port = '3306';
			}
			
			if(!empty($this->client_ip) AND !empty($this->mysql_user) AND !empty($this->mysql_database)){
				$this->db->close();
				$config = array();
				$config['hostname'] = $this->client_ip;
				$config['username'] = $this->mysql_user;
				$config['password'] = $this->mysql_pass;
				$config['port'] 	= $this->mysql_port;
				$config['database'] = $this->mysql_database;
				$config['dbdriver'] = 'mysqli';
				$config['dbprefix'] = '';
				$config['pconnect'] = FALSE;
				$config['db_debug'] = (ENVIRONMENT !== 'production');
				$config['cache_on'] = FALSE;
				$config['cachedir'] = '';
				$config['char_set'] = 'utf8';
				$config['dbcollat'] = 'utf8_general_ci';
				$config['swap_pre'] = '';
				$config['encrypt'] = FALSE;
				$config['compress'] = FALSE;
				$config['stricton'] = FALSE;
				$config['failover'] = array();
				
				$this->load->database($config);
			}else{
				$this->db->close();
				echo 'CANNOT CONNECT TO DB';
				die();
			}
			
			
		}
		
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
