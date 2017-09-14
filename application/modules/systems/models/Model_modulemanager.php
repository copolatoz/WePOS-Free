<?php
class Model_ModuleManager extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'modules';
	}
	
	function add_modules_method($all_modules_method = '', $module_id = '')
	{
		$this->table_method = $this->prefix.'modules_method';
		
		if(!empty($all_modules_method) AND !empty($module_id)){
			foreach($all_modules_method as $key => $dt){				
				$all_modules_method[$key]['module_id'] = $module_id;				
			}
		}
		
		//insert batch
		$this->db->insert_batch($this->table_method, $all_modules_method);
	}
	
	function add_modules_preload($all_modules_preload = '', $module_id = '')
	{
		$this->table_preload = $this->prefix.'modules_preload';
		
		if(!empty($all_modules_preload) AND !empty($module_id)){
			foreach($all_modules_preload as $key => $dt){				
				$all_modules_preload[$key]['module_id'] = $module_id;				
			}
		}
		
		//insert batch
		$this->db->insert_batch($this->table_preload, $all_modules_preload);
	}
	
	

} 