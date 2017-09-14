<?php

class Mdl_config extends DB_Model {
	
	function __construct()
	{
		parent::__construct();
		
	}
		
	function getMenuModules($role_id = '')
	{
		$prefix = $this->prefix;
				
		$this->db->select("r.*, r.id as id_module");
		$this->db->from($prefix."modules as r");
		$this->db->join($prefix."roles_module as g", "g.module_id = r.id");
		$this->db->where("r.is_active","1");
		//$this->db->where("r.show_on_start_menu","1");
		$this->db->where("g.role_id", $role_id);
		$this->db->order_by("r.start_menu_order", "ASC");
		$query = $this->db->get();
		
		$params = array(
			'query' 	=> $query,
			'single'	=> false, //many | single
			'return'	=> 'object' //array | object | json
		);
		
		//auto check data and return result
		return _free_result($params);
		
	}
	
	function getShortcutModules($user_id = '')
	{
		$prefix = $this->prefix;
		$this->db->select("r.*, r.id as id_module");
		$this->db->from($prefix."modules as r");
		$this->db->join($prefix."users_shortcut as g", "g.module_id = r.id");
		$this->db->where("r.is_active","1");
		//$this->db->where("r.show_on_start_menu = 1");
		$this->db->where("g.user_id	= ".$user_id);
		$this->db->order_by("r.start_menu_order", "ASC");
		$query = $this->db->get();
		
		$params = array(
			'query' 	=> $query,
			'single'	=> false, //many | single
			'return'	=> 'object' //array | object | json
		);
		
		//auto check data and return result
		return _free_result($params);
		
	}
	
	function getBackgroundModules($role_id = '')
	{
		$prefix = $this->prefix;
				
		$this->db->select("r.*, r.id as id_module");
		$this->db->from($prefix."modules as r");
		$this->db->join($prefix."roles_module as g", "g.module_id = r.id");
		$this->db->where("r.is_active","1");
		$this->db->where("r.running_background","1");
		$this->db->where("g.role_id", $role_id);
		$this->db->order_by("r.start_menu_order", "ASC");
		$query = $this->db->get();
		
		$params = array(
			'query' 	=> $query,
			'single'	=> false, //many | single
			'return'	=> 'object' //array | object | json
		);
		
		//auto check data and return result
		return _free_result($params);
		
	}
	
	function getQuickModules($user_id = '')
	{
		$prefix = $this->prefix;
		$this->db->select("r.*, r.id as id_module");
		$this->db->from($prefix."modules as r");
		$this->db->join($prefix."users_quickstart as g", "g.module_id = r.id");
		$this->db->where("r.is_active","1");
		//$this->db->where("r.show_on_start_menu = 1");
		$this->db->where("g.user_id	= ".$user_id);
		$this->db->order_by("r.start_menu_order", "ASC");
		$query = $this->db->get();
		
		//log_message('INFO', 'QUERY: '.$this->db->last_query());
		$params = array(
			'query' 	=> $query,
			'single'	=> false, //many | single
			'return'	=> 'object' //array | object | json
		);
		
		//auto check data and return result
		return _free_result($params);
		
	}
		
	function getWidgetModules($role_id = '')
	{
		$prefix = $this->prefix;
				
		$this->db->select("r.*, r.id as id_widget");
		$this->db->from($prefix."widgets as r");
		$this->db->join($prefix."roles_widget as g", "g.widget_id = r.id");
		$this->db->where("r.is_active","1");
		$this->db->where("g.role_id", $role_id);
		$this->db->order_by("r.widget_order", "ASC");
		$query = $this->db->get();
		
		$params = array(
			'query' 	=> $query,
			'single'	=> false, //many | single
			'return'	=> 'object' //array | object | json
		);
		
		//auto check data and return result
		return _free_result($params);
		
	}
	
	function desktopConfig($user_id = '')
	{
		$prefix = $this->prefix;
		$this->db->select("*");
		$this->db->from($prefix."users_desktop");
		$this->db->where("user_id = ".$user_id);
		$query = $this->db->get();
				
		if($query->num_rows() > 0){
			return $query->row();
		}else{
			//create new with default
			$desktop_config = array(
				"dock" 				=> "bottom",
				"wallpaper"			=> "default.jpg",
				"wallpaperStretch"	=> 'false',
				"wallpaper_id"		=> 1,
				"user_id"			=> $user_id,
				"window_mode"		=> 'full'
			);
			
			$this->db->insert($prefix."users_desktop", $desktop_config);			
			
			return (object)$desktop_config;
		}
		
	}
		
	function userData($user_id = '')
	{
		$prefix = $this->prefix;
		$this->db->select("*", false);
		$this->db->from($prefix."users");
		$this->db->where("id = ".$user_id);
		$query = $this->db->get();
		
		$res = array();
		if($query->num_rows() > 0){
			$res = $query->row_array();
			$res['user_fullname'] = $res['user_firstname'].' '.$res['user_lastname'];
		}
		
		//auto check data and return result
		return (object)$res;
		
	}
	
}
