<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class WidgetManager extends MY_Controller {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('model_WidgetManager', 'm');
	}

	public function gridData()
	{
		$prefix = $this->prefix;
		$this->table = $this->prefix.'widgets';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' 	=> 'is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> '*',
			'primary_key'	=> 'id',
			'table'			=> $this->table,
			'where'			=> array('is_deleted' => 0),
			'order'			=> array('id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
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
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'widgets';				
		$session_user = $this->session->userdata('user_username');
		
		//widget info
		$widget_name = $this->input->post('widget_name');
		$widget_author = $this->input->post('widget_author');
		$widget_version = $this->input->post('widget_version');
		$widget_description = $this->input->post('widget_description');
		$widget_controller = $this->input->post('widget_controller');
		$widget_order = $this->input->post('widget_order');
		$is_active = $this->input->post('is_active');	
		
		if(empty($is_active)){
			$is_active = 0;
		}
		
		if(empty($widget_name) OR empty($widget_controller)){
			$r = array('success' => false);
			die(json_encode($r));
		}			
			
		$r = '';
		if($this->input->post('form_type_WidgetManager', true) == 'add')
		{	
			
			$var = array(
				'fields'	=>	array(
					'widget_name'  			=> 	$widget_name,
				    'widget_author'  		=> 	$widget_author,
				    'widget_version'  		=> 	$widget_version,
				    'widget_description'	=> 	$widget_description,
				    'widget_description'	=> 	$widget_description,
					'widget_controller'		=>	$widget_controller,
					'widget_order'			=>	$widget_order,
					'is_active'				=>	$is_active,					
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				),
				'table'		=>  $this->table
			);	
			
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
				$q = $this->m->add($var);
				$insert_id = $this->m->get_insert_id();				
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
		if($this->input->post('form_type_WidgetManager', true) == 'edit'){
			$var = array('fields'	=>	array(
					'widget_name'  			=> 	$widget_name,
				    'widget_author'  		=> 	$widget_author,
				    'widget_version'  		=> 	$widget_version,
				    'widget_description'	=> 	$widget_description,
				    'widget_description'	=> 	$widget_description,
					'widget_controller'		=>	$widget_controller,
					'widget_order'			=>	$widget_order,
					'is_active'				=>	$is_active,	
					'updated'			=>	date('Y-m-d H:i:s'),
					'updatedby'			=>	$session_user
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			//UPDATE
			$id = $this->input->post('id', true);
			$this->lib_trans->begin();
				$update = $this->m->save($var, $id);
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
		$this->table = $prefix.'widgets';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
		
		//Delete
		$this->db->where("id IN (".$sql_Id.")");
		$q = $this->db->delete($this->table);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true);						
			//roles_Widget
			$this->db->where("widget_id IN (".$sql_Id.")");
			$q = $this->db->delete($prefix."roles_widget");
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Widget Failed!'); 
        }
		die(json_encode($r));
	}
	
}