<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class UserData extends MY_Controller {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('model_UserData', 'm');
	}

	public function gridData()
	{
		$prefix = $this->prefix;
		$this->table = $this->prefix.'users';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'a.is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, b.client_name, b2.client_structure_name, c.role_name, d.window_mode, d.dock',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'clients as b','b.id = a.client_id','LEFT'),
										array($this->prefix.'clients_structure as b2','b2.id = a.client_structure_id','LEFT'),
										array($this->prefix.'roles as c','c.id = b2.role_id','LEFT'),
										array($this->prefix.'users_desktop as d','d.user_id = a.id','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('a.id' => 'ASC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		$session_user = $this->session->userdata('id_user');
		$role_name = $this->input->post('role_name');
		$role_id = $this->input->post('role_id');
		$is_dropdown = $this->input->post('is_dropdown');
		$show_all_text = $this->input->post('show_all_text');
		
		$searching = $this->input->post('query');
		$keywords = $this->input->post('keywords');
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		
		if($session_user != 1){
			$params['where'][] = 'a.is_deleted = 0 AND a.id != 1';
		}else{
			$params['where'][] = 'a.is_deleted = 0';
		}
		
		if(!empty($role_name)){
			$params['where'][] = "c.role_name = '".$role_name."'";
		}
		if(!empty($role_id)){
			$params['where'][] = "b2.role_id IN (".$role_id.")";
		}
		
		if(!empty($searching)){
			$params['where'][] = "(a.user_username LIKE '%".$searching."%' OR a.user_firstname LIKE '%".$searching."%' OR a.user_lastname LIKE '%".$searching."%' OR c.role_name LIKE '%".$searching."%')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();		
		
		if(!empty($is_dropdown)){
			if(!empty($show_all_text)){
				$add_empty = array(
					'id'	=> '',
					'user_fullname'	=> 'All User',
					'user_username'	=> ''
				);
				array_push($newData, $add_empty);
			}
		}
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				if(empty($s['user_fullname'])){
					$s['user_fullname'] = $s['user_firstname'].' '.$s['user_lastname'];
				}
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'users';				
		$session_user = $this->session->userdata('user_username');
		$session_client_id = $this->session->userdata('client_id');
		
		$user_username = $this->input->post('user_username');
		$user_password = $this->input->post('user_password');
		$user_fullname = $this->input->post('user_fullname');
		$user_firstname = $this->input->post('user_firstname');
		$user_lastname = $this->input->post('user_lastname');
		$user_email = $this->input->post('user_email');
		$user_phone = $this->input->post('user_phone');
		$user_mobile = $this->input->post('user_mobile');
		$user_address = $this->input->post('user_address');
		$client_structure_id = $this->input->post('client_structure_id');
		$role_id = $this->input->post('role_id');
		$avatar = $this->input->post('avatar');
		$is_active = $this->input->post('is_active');		
		$change_pin = $this->input->post('change_pin');		

		//password
		$old_pass = $this->input->post('old_pass');
		$new_pass = $this->input->post('new_pass');
		$pass_confirm = $this->input->post('pass_confirm');
		
		//apps setting
		$window_mode = $this->input->post('window_mode');
		if(empty($window_mode)){
			$window_mode = 'full';
		}
		
		$user_desktop = array(
			'window_mode' => $window_mode
		);
		
		//GET ROLE
		if(empty($user_username)){
			$r = array('success' => false, 'info' => 'Username Tidak Boleh Kosong');
			die(json_encode($r));
		}
		
		if(empty($role_id)){
			$r = array('success' => false, 'info' => 'Structure yang dipilih tidak mempunyai Role');
			die(json_encode($r));
		}
		
		$is_active = 0;
		if(!empty($_POST['is_active'])){
			$is_active = 1;
		}
		
		$id = $this->input->post('id', true);
		
		$dt_deleted = array();
		$form_type_UserData = $this->input->post('form_type_UserData', true);
		if($form_type_UserData == 'add')
		{
			//cchek if user is not active
			$this->db->from($this->table);
			$this->db->where('user_username', $user_username);
			$this->db->where('is_deleted', 1);
			$getuser_deleted = $this->db->get();
			if($getuser_deleted->num_rows() > 0){
				$dt_deleted = $getuser_deleted->row();
				$id = $dt_deleted->id;
				$form_type_UserData = 'edit';
				$old_pass = '';
			}
		}
		
		$r = '';
		if($form_type_UserData == 'add')
		{
			if(empty($new_pass)){
				$r = array('success' => false);
				die(json_encode($r));
			}
			
			if((empty($new_pass) OR empty($pass_confirm)) OR ($new_pass != $pass_confirm)){
				$r = array('success' => false, 'info' => 'New Password not Match!');
				die(json_encode($r));
			}
			
			$user_password = md5($new_pass);
			
			
			
			for($i=0; $i<10; $i++){
				//check have pin
				$user_pin = rand(1111,9999);
				//$user_pin = rand(1111,9999).rand(1111,9999);
				$this->db->from($this->table);
				$this->db->where('user_pin', $user_pin);
				$getuser_pin = $this->db->get();
				if($getuser_pin->num_rows() == 0){
					$i += 10;
				}
			}
			
					
			
			$var = array(
				'fields'	=>	array(
				    'user_username' => 	$user_username,
					'user_password'	=>	$user_password,
					'user_pin'	=>	$user_pin,
					'role_id'		=>	$role_id,
					'client_id'			=>	$session_client_id,
					'client_structure_id'=>	$client_structure_id,
					'user_firstname'	=>	$user_firstname,
					'user_lastname'	=>	$user_lastname,
					'user_email'	=>	$user_email,
					'user_phone'	=>	$user_phone,
					'user_mobile'	=>	$user_mobile,
					'user_address'	=>	$user_address,
					'avatar'		=>	$avatar,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active
				),
				'table'		=>  $this->table
			);	
			
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
				$q = $this->m->add($var);
				$insert_id = $this->m->get_insert_id();								
				$this->m->user_desktop($user_desktop, $insert_id, 'add');				
			$this->lib_trans->commit();			
			if($q)
			{  
				$r = array('success' => true, 'id' => $insert_id); 
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($form_type_UserData == 'edit'){
			$var = array('fields'	=>	array(
				    'user_username' => 	$user_username,
					'role_id'		=>	$role_id,
					'client_id'			=>	$session_client_id,
					'client_structure_id'=>	$client_structure_id,
					'user_firstname'	=>	$user_firstname,
					'user_lastname'	=>	$user_lastname,
					'user_email'	=>	$user_email,
					'user_phone'	=>	$user_phone,
					'user_mobile'	=>	$user_mobile,
					'user_address'	=>	$user_address,
					'avatar'		=>	$avatar,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);


			
			
			//SKIP STRUCTURE IF SUPER ADMIN
			if($id == 1){
				$var['fields']['role_id'] = 1;
				$var['fields']['client_structure_id'] = 1;
			}
			
			if(!empty($old_pass)){
				if(!empty($new_pass)){
					$var['fields']['user_password'] = md5($new_pass);
			
					//check if valid old-pass
					$this->db->from($this->table);
					$this->db->where('user_password', md5($old_pass));
					$this->db->where('id',$id);
					$getvalidPass = $this->db->get();
					if($getvalidPass->num_rows() == 0){
						$r = array('success' => false, 'info' => 'Wrong Old Password!');
						die(json_encode($r));
					}
				}
			}
			
			if(!empty($new_pass)){
				$user_password = md5($new_pass);
				$var['fields']['user_password'] = $user_password;
			}
			
			if(!empty($change_pin)){
				
				for($i=0; $i<10; $i++){
					//check have pin
					$user_pin = rand(1111,9999);
					//$user_pin = rand(1111,9999).rand(1111,9999);
					$this->db->from($this->table);
					$this->db->where('user_pin', $user_pin);
					$getuser_pin = $this->db->get();
					if($getuser_pin->num_rows() == 0){
						$i += 10;
					}
				}
				
				$var['fields']['user_pin'] = $user_pin;
				
			}
			
			if(!empty($dt_deleted)){
				$var['fields']['is_active'] = 1;
				$var['fields']['is_deleted'] = 0;
			}
						
			//UPDATE
			$this->lib_trans->begin();
				$update = $this->m->save($var, $id);
				$this->m->user_desktop($user_desktop, $id, 'edit');
			$this->lib_trans->commit();
			
			if($update)
			{  
				$r = array('success' => true, 'id' => $id);
			}  
			else
			{  
				$r = array('success' => false);
			}
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function delete()
	{
		$prefix = $this->prefix;
		$this->table = $this->prefix.'users';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
		
		//Delete (TEMP)
		//$this->db->where("id IN (".$sql_Id.")");
		//$q = $this->db->update($this->table, array('is_active' => 0));
				
		//$this->db->where("id IN (".$sql_Id.")");
		$data_update = array(
			"is_deleted" => 1
		);
		$q = $this->db->update($this->table, $data_update, "id IN (".$sql_Id.")");
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
			//users_desktop
			$this->db->where("user_id IN (".$sql_Id.")");
			$q = $this->db->delete($prefix."users_desktop");
			
			//users_quickstart
			$this->db->where("user_id IN (".$sql_Id.")");
			$q = $this->db->delete($prefix."users_quickstart");
			
			//users_shortcut
			$this->db->where("user_id IN (".$sql_Id.")");
			$q = $this->db->delete($prefix."users_shortcut");
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete User Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function userModuleRoles()
	{
		$role_id = $this->session->userdata('role_id');
		$id_user = $this->session->userdata('id_user');
		
		$type_check = '';
		if(!empty($_POST['type_check'])){
			$type_check = $_POST['type_check'];
		}
		
		$user_shortcuts = array();
		if(!empty($id_user) AND $type_check == 'quickStart'){
			$userQuickStartShortcuts =	$this->m->userQuickStartShortcuts($id_user);
			if(!empty($userQuickStartShortcuts)){
				foreach($userQuickStartShortcuts as $dt){
					$user_shortcuts[] = $dt->id;
				}
			}
			
		}
		
		$user_shortcuts = array();
		if(!empty($id_user) AND $type_check == 'desktopShortcuts'){
			$userQuickStartShortcuts =	$this->m->userDesktopShortcuts($id_user);
			if(!empty($userQuickStartShortcuts)){
				foreach($userQuickStartShortcuts as $dt){
					$user_shortcuts[] = $dt->id;
				}
			}
			
		}
		
		$r = array('data' => '', 'totalCount'	=>	'');
		if(!empty($role_id)){
			
			$get_data = $this->m->userModuleRoles($role_id, $user_shortcuts, $type_check);
			
			$r = array(
				'data'			=>	$get_data,
				'totalCount'	=>	count($get_data)
			);
		}
		
      	die(json_encode($r));
	}
	
	public function userDesktopShortcuts()
	{
		$id_user = $this->session->userdata('id_user');
		$r = array('data' => '', 'totalCount'	=>	'');
		if(!empty($id_user)){
			
			$userDesktopShortcuts = $this->m->userDesktopShortcuts($id_user);
			
			$r = array(
				'data'			=>	$userDesktopShortcuts,
				'totalCount'	=>	count($userDesktopShortcuts)
			);
		}
		
      	die(json_encode($r));
	}
	
	public function saveDesktopShortcuts(){
		$prefix = $this->prefix;
		$this->table = $this->prefix.'users_shortcut';
		$session_branch_id = $this->session->userdata('id_branch');
		$session_user = $this->session->userdata('user_username');
		$session_role_id = $this->session->userdata('role_id');
		$session_client_id = $this->session->userdata('client_id');
		$id_user = $this->session->userdata('id_user');
		
		if(empty($id_user)){
			$r = array('success' => false);
			die(json_encode($r));
		}
		
		$id_module = array();
		if(!empty($_POST['id_module'])){
			$id_module = json_decode($_POST['id_module'], true);
		}
		
		$this->lib_trans->begin();
			$updateUserShortcuts = $this->m->updateUserShortcuts($id_module, $id_user);
		$this->lib_trans->commit();
		
		if($updateUserShortcuts)
		{  
			$r = array('success' => true);
		}  
		else
		{  
			$r = array('success' => false);
		}
		
		die(json_encode($r));
	}
	
	
	public function userQuickStartShortcuts()
	{
		$id_user = $this->session->userdata('id_user');
		$r = array('data' => '', 'totalCount'	=>	'');
		if(!empty($id_user)){
			$r = array(
				'data'			=>	$this->m->userQuickStartShortcuts($id_user),
				'totalCount'	=>	''
			);
		}
		
      	die(json_encode($r));
	}
	
	public function saveQuickStartShortcuts(){
		$prefix = $this->prefix;
		$this->table = $this->prefix.'users_shortcut';
		$session_branch_id = $this->session->userdata('id_branch');
		$session_user = $this->session->userdata('user_username');
		$session_role_id = $this->session->userdata('role_id');
		$session_client_id = $this->session->userdata('client_id');
		$id_user = $this->session->userdata('id_user');
		
		if(empty($id_user)){
			$r = array('success' => false);
			die(json_encode($r));
		}
		
		$id_module = '';
		if(!empty($_POST['id_module'])){
			$id_module = json_decode($_POST['id_module'], true);
		}
		
		$this->lib_trans->begin();
			$updateUserShortcuts = $this->m->updateQuickStartShortcuts($id_module, $id_user);
		$this->lib_trans->commit();
		
		if($updateUserShortcuts)
		{  
			$r = array('success' => true);
		}  
		else
		{  
			$r = array('success' => false);
		}
		
		die(json_encode($r));
	}
	
}