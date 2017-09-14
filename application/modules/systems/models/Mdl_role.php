<?php
class Mdl_role extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'roles';
	}
	
	function moduleData($id_role = 0)
	{
		$prefix = $this->prefix;
		
		$result = '';
		if($id_role == 0){
			$this->db->from($prefix.'modules');
			$this->db->where('is_active',1);
			//$this->db->where('show_on_start_menu',1);
			$dt_module = $this->db->get();
			if($dt_module->num_rows() > 0){
				$result = $dt_module->result();
			}
		}else{
			
			//get curr role module
			$this->db->select('module_id');
			$this->db->from($prefix.'roles_module');
			$this->db->where('role_id',$id_role);
			$dt_roles_module = $this->db->get();
			if($dt_roles_module->num_rows() > 0){
				$items = $dt_roles_module->result_array();
				$all_module = array();
				if(!empty($items)){				
					foreach($items as $item){
						$all_module[] = $item['module_id'];
					}
				}
				
				$imp_module = implode(",",$all_module);
				
			}
			
			$this->db->from($prefix.'modules');
			$this->db->where('is_active',1);
			//$this->db->where('show_on_start_menu',1);
			
			if(!empty($imp_module)){
				$this->db->where('id NOT IN ('.$imp_module.')');
			}
			
			$dt_module = $this->db->get();
			if($dt_module->num_rows() > 0){
				$result = $dt_module->result();
			}
		}
		
		return $result;
	}
	
	function moduleRoles($id_role = 0)
	{
		$prefix = $this->prefix;
		
		$type_check = $this->input->post('type_check', true);		
		
		$result = '';
		$this->db->select('a.id, a.module_name, a.module_folder');
		$this->db->from($prefix.'modules as a');
		$this->db->join($prefix.'roles_module as b','b.module_id = a.id','left');
		$this->db->where('a.is_active',1);
		//$this->db->where('a.show_on_start_menu',1);
		$this->db->where('b.role_id',$id_role);
		
		if(!empty($type_check)){
			if($type_check == 'dekstopShortcut'){
				$this->db->where('a.show_on_shorcut_desktop', 1);
			}
			if($type_check == 'quickStart'){
				$this->db->where('a.show_on_start_menu = 1 OR a.show_on_context_menu = 1');
			}
		}
		$dt_module = $this->db->get();
		
		if($dt_module->num_rows() > 0){
			$result = $dt_module->result();
		}
		
		return $result;
	}
	
	function add_module_widget($p = '', $id = '')
	{
		$prefix = $this->prefix;
		$session_user = $this->session->userdata('user_username');
		
		if(empty($p) OR empty($id)){
			return false;
		}		
		
		//---Role Modules
		//get old module
		$old_module_id = array();
		$this->db->select("*");
		$this->db->from($prefix.'roles_module');
		$this->db->where('role_id', $id);
		$get_old_module = $this->db->get();	
		if($get_old_module->num_rows() > 0){
			foreach($get_old_module->result() as $dt){
				if(!in_array($dt->module_id, $old_module_id)){
					$old_module_id[] = $dt->module_id;
				}
			}
		}
		
		$new_module_id = array();
		$new_module_data = array();
		$all_module_data = array();
		
		if(!empty($p['modules'])){
			for($i=0; $i<count($p['modules']); $i++)
			{	
				
				if(!in_array($p['modules'][$i], $old_module_id)){
					
					if(!in_array($p['modules'][$i], $new_module_id)){
						$new_module_id[] = $p['modules'][$i];
						
						$new_module_data[] = array(
							'role_id'		=>	$id,
							'module_id'		=>	$p['modules'][$i],
							'created'		=>	date('Y-m-d H:i:s'),
							'createdby'		=>	$session_user,
							'updated'		=>	date('Y-m-d H:i:s'),
							'updatedby'		=>	$session_user
						);
					}
					
					if(!in_array($p['modules'][$i], $all_module_data)){
						$all_module_data[] = $p['modules'][$i];
					}
					
				}else{
				
					if(!in_array($p['modules'][$i], $all_module_data)){
						$all_module_data[] = $p['modules'][$i];
					}
					
				}
			}
			
			$do_add = false;
			if(!empty($new_module_data)){
				$do_add = $this->db->insert_batch($prefix.'roles_module', $new_module_data);
				log_message('INFO', $this->db->last_query());
			}			
		}
			
		//delete old data
		$delete_module_id = array();
		foreach($old_module_id as $dt){
			if(!in_array($dt, $all_module_data)){
				if(!in_array($dt, $delete_module_id)){
					$delete_module_id[] = $dt;
				}
			}
		}
		
		if(!empty($delete_module_id)){
			$all_delete_module_id = implode("','", $delete_module_id);
			$this->db->where("module_id IN ('".$all_delete_module_id."')");
			$do_delete = $this->db->delete($prefix.'roles_module');
		}
		
		//----Role Widget
		//get old widget
		$old_widget_id = array();
		$this->db->select("*");
		$this->db->from($prefix.'roles_widget');
		$this->db->where('role_id', $id);
		$get_old_widget = $this->db->get();
		if($get_old_widget->num_rows() > 0){
			foreach($get_old_widget->result() as $dt){
				if(!in_array($dt->widget_id, $old_widget_id)){
					$old_widget_id[] = $dt->widget_id;
				}
			}
		}
		
		$new_widget_id = array();
		$new_widget_data = array();
		$all_widget_data = array();
		
		if(!empty($p['widgets'])){			
			for($i=0; $i<count($p['widgets']); $i++)
			{				
				if(!in_array($p['widgets'][$i], $old_widget_id)){
					if(!in_array($p['widgets'][$i], $new_widget_id)){
						$new_widget_id[] = $p['widgets'][$i];						
						
						$new_widget_data[] = array(
							'role_id'		=>	$id,
							'widget_id'		=>	$p['widgets'][$i],
							'created'		=>	date('Y-m-d H:i:s'),
							'createdby'		=>	$session_user,
							'updated'		=>	date('Y-m-d H:i:s'),
							'updatedby'		=>	$session_user
						);
					}
					if(!in_array($p['widgets'][$i], $all_widget_data)){
						$all_widget_data[] = $p['widgets'][$i];
					}
				}else{
					if(!in_array($p['widgets'][$i], $all_widget_data)){
						$all_widget_data[] = $p['widgets'][$i];
					}
				}
			}
			
			$do_add = false;
			if(!empty($new_widget_data)){
				$do_add = $this->db->insert_batch($prefix.'roles_widget', $new_widget_data);
				log_message('INFO', $this->db->last_query());
			}	
		}
		
		//delete old data
		$widget_delete_id = array();
		foreach($old_widget_id as $dt){
			if(!in_array($dt, $all_widget_data)){
				if(!in_array($dt, $widget_delete_id)){
					$widget_delete_id[] = $dt;
				}
			}
		}
		
		if(!empty($widget_delete_id)){
			$all_widget_delete_id = implode("','", $widget_delete_id);
			$this->db->where("widget_id IN ('".$all_widget_delete_id."')");
			$do_delete = $this->db->delete($prefix.'roles_widget');
		}
		
		return true;
	}
	
	function delete_detail($p, $id)
	{
		$prefix = $this->prefix;
		
		$this->db->where('role_id', $id);
		$do_delete = $this->db->delete($prefix.'roles_module');
		
		$this->db->where('role_id', $id);
		$do_delete = $this->db->delete($prefix.'roles_widget');		
		
		return $do_delete;
	}
	
	function widgetData($id_role = 0)
	{
		$prefix = $this->prefix;
		
		$result = '';
		if($id_role == 0){
			$this->db->from($prefix.'widgets');
			$dt_widget = $this->db->get();
			if($dt_widget->num_rows() > 0){
				$result = $dt_widget->result();
			}
		}else{
			
			//get curr role widget
			$this->db->select('widget_id');
			$this->db->from($prefix.'roles_widget');
			$this->db->where('role_id',$id_role);
			$dt_roles_widget = $this->db->get();
			if($dt_roles_widget->num_rows() > 0){
				$items = $dt_roles_widget->result_array();
				$all_widget = array();
				if(!empty($items)){				
					foreach($items as $item){
						$all_widget[] = $item['widget_id'];
					}
				}
				
				$imp_widget = implode(",",$all_widget);
				
			}
			
			$this->db->from($prefix.'widgets');
			
			if(!empty($imp_widget)){
				$this->db->where('id NOT IN ('.$imp_widget.')');
			}
			
			$dt_widget = $this->db->get();
			if($dt_widget->num_rows() > 0){
				$result = $dt_widget->result();
			}
		}
		
		return $result;
	}
	
	function widgetRoles($id_role = 0)
	{
		$prefix = $this->prefix;
		
		$result = '';
		$this->db->select('a.id, a.widget_name, a.widget_controller');
		$this->db->from($prefix.'widgets as a');
		$this->db->join($prefix.'roles_widget as b','b.widget_id = a.id','left');
		$this->db->where('b.role_id',$id_role);
		$dt_widget = $this->db->get();
		
		if($dt_widget->num_rows() > 0){
			$result = $dt_widget->result();
		}
		
		return $result;
	}
} 