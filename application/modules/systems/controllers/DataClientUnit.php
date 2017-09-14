<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class DataClientUnit extends MY_Controller {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('model_DataClientUnit', 'm');		
	}
	public function gridData()
	{
		$prefix = $this->prefix;
		$this->table = $this->prefix.'clients_unit';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> "*, '[' + client_unit_code + '] ' + client_unit_name AS client_unit_kode_nama",
			'primary_key'	=> 'id',
			'table'			=> $this->table,
			'where'			=> array('is_deleted' => 0),
			'order'			=> array('id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('client_unit_code' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(client_unit_code LIKE '%".$searching."%' OR client_unit_name LIKE '%".$searching."%')";
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
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'clients_unit';				
		$session_user = $this->session->userdata('user_username');
		$session_client_id = $this->session->userdata('client_id');
		
		$client_unit_code = $this->input->post('client_unit_code');
		$client_unit_name = $this->input->post('client_unit_name');
		
		if(empty($client_unit_code) OR empty($client_unit_name) OR empty($session_client_id)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		$is_active = 0;
		if(!empty($_POST['is_active'])){
			$is_active = 1;
		}
			
		$r = '';
		if($this->input->post('form_type_DataClientUnit', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'id' 	=> 	null,  
				    'client_unit_code' => 	$client_unit_code,
				    'client_unit_name' => 	$client_unit_name,
				    'client_id' 	=> 	$session_client_id,
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
		if($this->input->post('form_type_DataClientUnit', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'client_unit_code'	=> 	$client_unit_code,
				    'client_unit_name' 	=> 	$client_unit_name,
				    'client_id' 		=> 	$session_client_id,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active
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
		$this->table = $this->prefix.'clients_unit';
		
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
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Unit Failed!'); 
        }
		die(json_encode($r));
	}
	
}