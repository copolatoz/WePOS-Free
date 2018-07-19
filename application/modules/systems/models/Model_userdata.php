<?php
class Model_UserData extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'users';
	}
	
	function user_desktop($user_desktop = '', $user_id = '', $type = 'add'){
		if(!empty($user_desktop) AND !empty($user_id)){
			$this->table_user_desktop = $this->prefix.'users_desktop';
			$user_desktop['user_id'] = $user_id;
			
			//check user_desktop
			$user_desktop_id = 0;
			$this->db->select("*");
			$this->db->where("user_id = '".$user_id."'");
			$get_user_desktop = $this->db->get($this->table_user_desktop);
			if($get_user_desktop->num_rows() > 0){
				$dt_user_desktop = $get_user_desktop->row();
				$user_desktop_id = $dt_user_desktop->id;
			}
			
			if(!empty($user_desktop_id)){				
				$this->db->update($this->table_user_desktop, $user_desktop, "id = ".$user_desktop_id);
			}else{
				$this->db->insert($this->table_user_desktop, $user_desktop);
			}
		}
	}
	
	public function userModuleRoles($role_id = -1, $user_shortcuts = array(), $type_check = '')
	{
		$prefix = $this->prefix;
		
		$result = '';
		$this->db->select('a.id, a.module_name, a.module_folder');
		$this->db->from($prefix.'modules as a');
		$this->db->join($prefix.'roles_module as b','b.module_id = a.id','left');
		//$this->db->where('a.show_on_start_menu',1);
		$this->db->where('b.role_id',$role_id);
		
		if($type_check == 'desktopShortcuts'){
			$this->db->where('a.show_on_shorcut_desktop',1);
		}
		
		if(!empty($user_shortcuts)){
			$id_shortcut = implode("','", $user_shortcuts);
			$this->db->where("b.module_id NOT IN ('".$id_shortcut."')");
		}
		
		$dt_module = $this->db->get();
		
		if($dt_module->num_rows() > 0){
			$result = $dt_module->result();
		}
		
		return $result;
	}
	
	function userDesktopShortcuts($user_id = -1)
	{
		$prefix = $this->prefix;
		
		$result = array();
		$this->db->select('a.module_id, a.module_id as id, b.module_name, b.module_folder');
		$this->db->from($prefix.'users_shortcut as a');
		$this->db->join($prefix.'modules as b','b.id = a.module_id','left');
		$this->db->where('b.show_on_shorcut_desktop',1);
		$this->db->where('a.user_id',$user_id);
		$dt_module = $this->db->get();
		
		if($dt_module->num_rows() > 0){
			$result = $dt_module->result();
		}
		
		return $result;
	}
	
	function updateUserShortcuts($dt_module = '', $id = '')
	{
		$prefix = $this->prefix;
		$session_user = $this->session->userdata('user_username');
		
		if(empty($id)){
			return false;
		}		
		
		//get old shortcut
		$old_module_id = array();
		$this->db->select("*");
		$this->db->from($prefix.'users_shortcut');
		$this->db->where('user_id', $id);
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
		
		if(!empty($dt_module)){
			
			//modules
			$allowed_module_id = array();
			$this->db->select("*");
			$this->db->from($prefix.'modules');
			$this->db->where("id IN (".implode(",", $dt_module).")");
			$get_sel_module = $this->db->get();	
			if($get_sel_module->num_rows() > 0){
				foreach($get_sel_module->result() as $dt){
					
					if($dt->show_on_shorcut_desktop == 1){
						if(!in_array($dt->id, $allowed_module_id)){
							$allowed_module_id[] = $dt->id;
						}
					}
					
				}
			}
			
			for($i=0; $i<count($dt_module); $i++)
			{	
				
				if(in_array($dt_module[$i],$allowed_module_id)){
					if(!in_array($dt_module[$i], $old_module_id)){
						
						if(!in_array($dt_module[$i], $new_module_id)){
							$new_module_id[] = $dt_module[$i];
							
							$new_module_data[] = array(
								'user_id'		=>	$id,
								'module_id'		=>	$dt_module[$i],
								'created'		=>	date('Y-m-d H:i:s'),
								'createdby'		=>	$session_user,
								'updated'		=>	date('Y-m-d H:i:s'),
								'updatedby'		=>	$session_user
							);
						}
						
						if(!in_array($dt_module[$i], $all_module_data)){
							$all_module_data[] = $dt_module[$i];
						}
						
					}else{
					
						if(!in_array($dt_module[$i], $all_module_data)){
							$all_module_data[] = $dt_module[$i];
						}
						
					}
				}
			}
			
			$do_add = false;
			if(!empty($new_module_data)){
				$do_add = $this->db->insert_batch($prefix.'users_shortcut', $new_module_data);
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
			$do_delete = $this->db->delete($prefix.'users_shortcut');
		}
		
		return true;
	}
	
	function userQuickStartShortcuts($user_id = -1)
	{
		$prefix = $this->prefix;
		
		$result = '';
		$this->db->select('a.id, a.module_name, a.module_folder');
		$this->db->from($prefix.'users_quickstart as b');
		$this->db->join($prefix.'modules as a','a.id = b.module_id','left');
		$this->db->where('a.show_on_start_menu = 1 OR a.show_on_context_menu = 1');
		$this->db->where('b.user_id',$user_id);
		$dt_module = $this->db->get();
		
		if($dt_module->num_rows() > 0){
			$result = $dt_module->result();
		}
		
		return $result;
	}
	
	function updateQuickStartShortcuts($dt_module = '', $id = '')
	{
		$prefix = $this->prefix;
		$session_user = $this->session->userdata('user_username');
		
		if(empty($id)){
			return false;
		}		
		
		//get old shortcut
		$old_module_id = array();
		$this->db->select("*");
		$this->db->from($prefix.'users_quickstart');
		$this->db->where('user_id', $id);
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
		
		if(!empty($dt_module)){
			
			//modules
			$allowed_module_id = array();
			$this->db->select("*");
			$this->db->from($prefix.'modules');
			$this->db->where("id IN (".implode(",", $dt_module).")");
			$get_sel_module = $this->db->get();	
			if($get_sel_module->num_rows() > 0){
				foreach($get_sel_module->result() as $dt){
					
					if($dt->show_on_start_menu == 1 OR $dt->show_on_context_menu == 1){
						if(!in_array($dt->id, $allowed_module_id)){
							$allowed_module_id[] = $dt->id;
						}
					}
					
				}
			}
			
			for($i=0; $i<count($dt_module); $i++)
			{	
				
				if(in_array($dt_module[$i],$allowed_module_id)){
					if(!in_array($dt_module[$i], $old_module_id)){
						
						if(!in_array($dt_module[$i], $new_module_id)){
							$new_module_id[] = $dt_module[$i];
							
							$new_module_data[] = array(
								'user_id'		=>	$id,
								'module_id'		=>	$dt_module[$i],
								'created'		=>	date('Y-m-d H:i:s'),
								'createdby'		=>	$session_user,
								'updated'		=>	date('Y-m-d H:i:s'),
								'updatedby'		=>	$session_user
							);
						}
						
						if(!in_array($dt_module[$i], $all_module_data)){
							$all_module_data[] = $dt_module[$i];
						}
						
					}else{
					
						if(!in_array($dt_module[$i], $all_module_data)){
							$all_module_data[] = $dt_module[$i];
						}
						
					}
				}
			}
			
			$do_add = false;
			if(!empty($new_module_data)){
				$do_add = $this->db->insert_batch($prefix.'users_quickstart', $new_module_data);
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
			$do_delete = $this->db->delete($prefix.'users_quickstart');
		}
		
		return true;
	}

} 