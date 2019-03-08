<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Fixbugs extends MY_Controller {
	
	function __construct()
	{
		parent::__construct();
	}

	public function index($call_tools = '', $var2 = '')
	{
		if(empty($call_tools)){
			echo 'go away human??';
		}else{
			//check files
			//echo 'checking file.. '.MODULE_PATH.'fixbugs/models/'. $call_tools.'.php';
			if(file_exists(MODULE_PATH.'fixbugs/models/'. $call_tools.'.php')){
				echo ' CALLED TOOLS '.$call_tools.' --> OK ..<br/>';
				$this->load->model($call_tools, 'get_tools');
				$this->get_tools->generate($var2);
			}else{
				echo ' wrong fixbugs execution!<br/>';
				echo MODULE_PATH.'fixbugs/models/'. $call_tools.'.php <br/>';
				echo 'File Not Exist!';
			}			
			
		}
		
	}
	
}
