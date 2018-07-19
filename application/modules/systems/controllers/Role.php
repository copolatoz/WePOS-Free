<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Role extends MY_Controller {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('mdl_role', 'm');
	}

	public function gridData()
	{
		$prefix = $this->prefix;
		$this->table = $this->prefix.'roles';
		$session_client_id = $this->session->userdata('client_id');
		
		if(empty($session_client_id)){
			$r = array('success' => false);
			die(json_encode($r));
		}

		//is_active_text
		$sortAlias = array(
				'is_active_text' => 'a.is_active'
		);
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, b.client_name',
			'primary_key'	=> 'id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'clients as b','b.id = a.client_id','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		

		$searching = $this->input->post('query');
		$keywords = $this->input->post('keywords');
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		
		if(!empty($searching)){
			$params['where'][] = "(a.role_name LIKE '%".$searching."%')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
								
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function moduleData()
	{
		$r = array('data' => '', 'totalCount'	=>	'');
		
		$id_role = 0;
		if(!empty($_POST['id_role'])){
			$id_role = $_POST['id_role'];
		}
		//if(!empty($_POST['id'])){
			$r = array(
				'data'			=>	$this->m->moduleData($id_role),
				'totalCount'	=>	''
			);
		//}
		
      	die(json_encode($r));
	}
	
	public function moduleRoles()
	{
		$r = array('data' => '', 'totalCount'	=>	'');
		if(!empty($_POST['id_role'])){
			$r = array(
				'data'			=>	$this->m->moduleRoles($_POST['id_role']),
				'totalCount'	=>	''
			);
		}
		
      	die(json_encode($r));
	}
	
	public function widgetData()
	{
		$r = array('data' => '', 'totalCount'	=>	'');
		
		$id_role = 0;
		if(!empty($_POST['id_role'])){
			$id_role = $_POST['id_role'];
		}
		//if(!empty($_POST['id'])){
			$r = array(
				'data'			=>	$this->m->widgetData($id_role),
				'totalCount'	=>	''
			);
		//}
		
      	die(json_encode($r));
	}
	
	public function widgetRoles()
	{
		$r = array('data' => '', 'totalCount'	=>	'');
		if(!empty($_POST['id_role'])){
			$r = array(
				'data'			=>	$this->m->widgetRoles($_POST['id_role']),
				'totalCount'	=>	''
			);
		}
		
      	die(json_encode($r));
	}
	
	public function save()
	{
		$prefix = $this->prefix;
		$this->table = $this->prefix.'roles';
		$session_branch_id = $this->session->userdata('id_branch');
		$session_user = $this->session->userdata('user_username');
		$session_role_id = $this->session->userdata('role_id');
		$session_client_id = $this->session->userdata('client_id');
		
		if(empty($session_client_id)){
			$r = array('success' => false);
			die(json_encode($r));
		}
		
		$id_module = '';
		if(!empty($_POST['id_module'])){
			$id_module = json_decode($_POST['id_module'], true);
		}
		
		$id_widget = '';
		if(!empty($_POST['id_widget'])){
			$id_widget = json_decode($_POST['id_widget'], true);
		}

		if($this->input->post('form_type', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'role_name'  		=> 	$this->input->post('role_name', true),
					'role_description'  => 	$this->input->post('role_description', true),
					'client_id'		  	=> 	$session_client_id,
					'created'			=>	date('Y-m-d H:i:s'),
					'createdby'			=>	$session_user,
					'updatedby'			=>	date('Y-m-d H:i:s'),
					'updatedby'			=>	$session_user,
					'is_active'			=>	$this->input->post('is_active')
				),
				'table'		=>  $this->table,
				'modules'	=>	$id_module,
				'widgets'	=>	$id_widget
			);	
			
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
				$this->m->add($var);
				$insert_id = $this->m->get_insert_id();
				$add_module_widget = $this->m->add_module_widget($var, $insert_id);
			$this->lib_trans->commit();
			if($insert_id OR $add_module_widget)
			{  
				$r = array('success' => true, 'id' => $insert_id); 
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}
		else if($this->input->post('form_type', true) == 'edit')
		{
			$var = array('fields'	=>	array(
				    'role_name'  		=> 	$this->input->post('role_name', true),
					'role_description'  => 	$this->input->post('role_description', true),
					'client_id'		  	=> 	$session_client_id,
					'updated'			=>	date('Y-m-d H:i:s'),
					'updatedby'			=>	$session_user,
					'is_active'			=>	$this->input->post('is_active')
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id',
				'modules'	=>	$id_module,
				'widgets'	=>	$id_widget
			);	
			
			//UPDATE
			$id = $this->input->post('id', true);
			$this->lib_trans->begin();
				$this->m->save($var, $id);				
				//$this->m->delete_detail($var, $id);
				$add_module_widget = $this->m->add_module_widget($var, $id);
			$this->lib_trans->commit();
			
			if($add_module_widget)
			{  
				$r = array('success' => true, 'id' => $id);
			}  
			else
			{  
				$r = array('success' => false, 'e' => $add_module_widget);
			}
		}
		else
		{
			$r = '';
		}
		die(json_encode(($r==null or $r=='')?array('success'=>false):$r));
	}
	
	public function delete()
	{
		
		$prefix = $this->prefix;
		$this->table = $this->prefix.'roles';
		
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
				
		$this->db->where("id IN (".$sql_Id.")");
		$q = $this->db->delete($this->table);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
			//roles_module
			$this->db->where("role_id IN (".$sql_Id.")");
			$q = $this->db->delete($prefix."roles_module");
			
			//roles_module
			$this->db->where("role_id IN (".$sql_Id.")");
			$q = $this->db->delete($prefix."roles_widget");
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Roles Failed!'); 
        }
		die(json_encode($r));
	}

}