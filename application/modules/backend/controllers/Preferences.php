<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Preferences extends MY_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('model_preferences', 'm');
	}
	
	//important for service load
	function services_model_loader(){
		$dt_model = array( 'm' => '../../backend/models/model_preferences');
		return $dt_model;
	}
	
	/*SERVICES*/
	public function getData()
	{
		//glyph = fa {iconCls} fa-3x
		$prefix = $this->prefix;
		
		$role_id = $this->session->userdata('role_id');
		$this->db->select("r.*, r.id as id_module");
		$this->db->from($prefix."modules as r");
		$this->db->join($prefix."roles_module as g", "g.module_id = r.id");
		$this->db->where("r.show_on_preference","1");
		$this->db->where("g.role_id", $role_id);
		$this->db->order_by("r.start_menu_order", "ASC");
		$query = $this->db->get();
		
		$default_preferences = array();	
		if($query->num_rows() > 0){
			foreach($query->result_array() as $dtP){
				$default_preferences[] = $dtP;
			}
		}
		
		/*
		$default_preferences = array();		
		$default_preferences[] = (object) array(
									'name'		=> 'Change Profile',
									'iconCls'	=> 'fa-user',
									'module'	=> 'ExtApp.desktop.ProfileModal',
									'func'		=> 'onProfile'
								);		
		$default_preferences[] = (object) array(
									'name'		=> 'Change Wallpaper',
									'iconCls'	=> 'fa-th-large',
									'module'	=> 'ExtApp.desktop.WallpaperModal',
									'func'		=> 'onChangeWallpaper'
								);		
		$default_preferences[] = (object) array(
									'name'		=> 'About',
									'iconCls'	=> 'fa-info-circle',
									'module'	=> 'ExtApp.desktop.onAbout',
									'func'		=> 'onAbout'
								);		
		echo json_encode($default_preferences);
		*/
		
		echo json_encode($default_preferences);
		die();
	}
	
}
