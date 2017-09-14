<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class DataStructure extends MY_Controller {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('model_DataStructure', 'm');		
	}

	public function gridData()
	{
		$prefix = $this->prefix;
		$this->table = $this->prefix.'clients_structure';
		$session_client_id = $this->session->userdata('client_id');
		
		if(empty($session_client_id)){
			$r = array('success' => false);
			die(json_encode($r));
		}	
		
		// Default Parameter
		$all_fields = "a.*, b.client_unit_name, c.role_name";
		$params = array(
			'fields'		=> $all_fields,
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'clients_unit as b','b.id = a.client_unit_id','LEFT'),
										array($this->prefix.'roles as c','c.id = a.role_id','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('a.id' => 'ASC'),
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//$params['where'][] = 'a.client_structure_parent != 0';
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('client_structure_name' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(client_structure_name LIKE '%".$searching."%')";
		}
		
		$get_data = $this->m->find_all($params); //get data -> data, totalCount		  
		
  		//re-assign per-parent
		$dt_parent = array();
      	$newData = array();
		
		if($is_dropdown){
			$root_data = array(
				'id' => 0,
				'client_structure_name' => '-- ROOT --',
				'client_structure_nama_show' => '-- ROOT --'
			);
			$dt_parent[0] = array();
			$dt_parent[0][] = $root_data;			
		}
		
		$newData = array();	
		if(!empty($get_data['data'])){		
			foreach ($get_data['data'] as $s){
				
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				$s['client_structure_nama_show'] = $s['client_structure_name'];
				if($s['client_structure_parent'] == 0){
					$s['client_structure_nama_show'] = '<b>'.$s['client_structure_name'].'</b>';
				}
				
				if(empty($s['client_structure_parent'])){
					$s['client_structure_parent'] = 0;
				}
				
				//array_push($newData, $s);
				if(empty($dt_parent[$s['client_structure_parent']])){
					$dt_parent[$s['client_structure_parent']] = array();
				}
				$dt_parent[$s['client_structure_parent']][] = $s;
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
		/*---------- SET PARENT - CHILD --------------------- */
		$data = array(
			'data'		=> $dt_parent,
			'parent'	=> 0,
			'level'		=> 0
		);
		$newData = DataStructure::client_structure_parent_child($data);
		/*---------- SET PARENT - CHILD --------------------- */
		
		$get_data['data'] = $newData;
		$get_data['totalCount'] = count($newData);
		
      	die(json_encode($get_data));
	}
	
	public function client_structure_parent_child($data_post){
		
		//global $all_data;
		$data_default = array(
			'data'		=> array(),
			'parent'	=> 0,
			'level'		=> 0
		);
		$data_post = array_merge($data_default, $data_post);
		extract($data_post);
		
		if($level > 1){
			if($level > 2){
				$separator = str_repeat(' &nbsp; &nbsp; &nbsp; ', ($level-2)).'+-- ';
			}else{
				$separator = '&nbsp; +-- ';
			}
		}
		
		$curr_level = $level;
		$level++;
		
		if(!empty($data[$parent])){
			$get_all_child = array();
			
			foreach($data[$parent] as $dt_child){
				
				if($curr_level > 1){
					$dt_child['client_structure_nama_show'] = $separator.$dt_child['client_structure_nama_show'];
				}else{
					$dt_child['client_structure_nama_show'] = $dt_child['client_structure_nama_show'];
				}
				
				if(!empty($dt_child['id'])){
				
					$check_parent_id = $dt_child['id'];
										
					$data_default = array(
						'data'		=> $data,
						'parent'	=> $check_parent_id,
						'level'		=> $level
					);
					
					$get_child = DataStructure::client_structure_parent_child($data_default);
				}
				
				if($curr_level > 0){
					$get_all_child[] = $dt_child;	
				}
				
				if(!empty($get_child)){
					
					foreach($get_child as $dt_get){
						
						$get_all_child[] = $dt_get;
						
					}
					
				}	
			}
			
			return $get_all_child;
			
		}else{
			//child
			return '';		
		}	
	}
	
	public function save()
	{
		$prefix = $this->prefix;
		$this->table = $this->prefix.'clients_structure';
		
		$session_user = $this->session->userdata('user_username');		
		$session_client_id = $this->session->userdata('client_id');
		
		$client_structure_name = $this->input->post('client_structure_name');
		if(empty($client_structure_name) OR empty($session_client_id)){
			$r = array('success' => false);
			die(json_encode($r));
		}
		
		$is_child = 0;
		$parent = $this->input->post('client_structure_parent');
		if(!empty($parent)){
			$is_child = 1;
		}
		
		$is_active = 0;
		if(!empty($_POST['is_active'])){
			$is_active = 1;
		}
		
		$r = '';
				
		$id = $this->input->post('id', true);
		
		if($this->input->post('form_type_DataStructure', true) == 'add'){
			$var = array(
				'fields'	=>	array(
				    'id' 			=> 	null,  
				    'client_structure_name'  	=> 	$client_structure_name,
					'client_structure_parent'	=> 	$this->input->post('client_structure_parent'),
					'client_structure_notes'	=> 	$this->input->post('client_structure_notes'),
					'client_structure_order'	=> 	$this->input->post('client_structure_order'),
					'client_unit_id'			=> 	$this->input->post('client_unit_id'),
					'role_id'					=> 	$this->input->post('role_id'),
					'client_id'					=> 	$session_client_id,
					'created'				=>	date('Y-m-d H:i:s'),
					'createdby'				=>	$session_user,
					'updated'				=>	date('Y-m-d H:i:s'),
					'updatedby'				=>	$session_user,
					'is_active'				=>	$is_active
				),
				'table'		=>  $this->table
			);	
						
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
				$q = $this->m->add($var);
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
		if($this->input->post('form_type_DataStructure', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'client_structure_name'  	=> 	$client_structure_name,
					'client_structure_parent'	=> 	$this->input->post('client_structure_parent'),
					'client_structure_notes'	=> 	$this->input->post('client_structure_notes'),
					'client_structure_order'	=> 	$this->input->post('client_structure_order'),
					'client_id'					=> 	$session_client_id,
					'client_unit_id'			=> 	$this->input->post('client_unit_id'),
					'role_id'					=> 	$this->input->post('role_id'),
					'updated'			=>	date('Y-m-d H:i:s'),
					'updatedby'			=>	$session_user,
					'is_active'			=>	$is_active
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);	
			
			//UPDATE
			$id = $this->input->post('id', true);
			$this->lib_trans->begin();
				$update = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
			if($update){  
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
		$this->table = $this->prefix.'clients_structure';
		
		$get_id = $this->input->post('id', true);	
		$id = json_decode($get_id, true);
		
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
						
		$up_parent = array();
		$this->db->from($prefix."clients_structure");
		$this->db->where("id IN (".$sql_Id.")");
		$cek_old_data = $this->db->get();
		if($cek_old_data->num_rows() > 0){
			foreach($cek_old_data->result_array() as $dt_old){
				$up_parent[$dt_old['id']] = $dt_old['client_structure_parent'];
				
			}
		}
		
		//delete menu
		//$this->db->where("id IN (".$sql_Id.")");
		//$q = $this->db->delete($this->table);
		
		$r = '';
		if($q)  
        {             
			//check has child
			$this->db->from($prefix."clients_structure");
			$this->db->where("client_structure_parent IN (".$sql_Id.")");
			$cek_child = $this->db->get();
			if($cek_child->num_rows() > 0){
				foreach($cek_child->result_array() as $dt_child){
					//update child
					$update['client_structure_parent'] = $up_parent[$dt_child['client_structure_parent']];
					$update['is_active'] = false;
					$this->db->where('id', $dt_child['id']);
					$this->db->update($this->table, $update);		
				}
			}
			
		   $r = array('success' => true); 
        
		}else
        {  
            $r = array('success' => false, 'info' => 'Delete Structure Failed!'); 
        }
		die(json_encode($r));
	}

}