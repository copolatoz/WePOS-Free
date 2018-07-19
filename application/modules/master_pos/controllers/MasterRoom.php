<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MasterRoom extends MY_Controller {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_masterroom', 'm');		
	}

	public function gridData()
	{
		$this->table = $this->prefix.'room';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		//MEMCACHED SESSION
		$use_memcached = $this->input->post('use_memcached');
		if($use_memcached == 1){
			//reload memcached
		}else{
			//empty memcached
			
		}
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, b.floorplan_name",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'floorplan as b','b.id = a.floorplan_id','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$show_all_text = $this->input->post('show_all_text');
		$show_choose_text = $this->input->post('show_choose_text');
		$floorplan_id = $this->input->post('floorplan_id');
		$keywords = $this->input->post('keywords');

		$show_available = $this->input->post('show_available');
		if(empty($show_available)){
			$show_available = false;
		}
		
		$curr_billing = $this->input->post('curr_billing');
		
		if(!empty($keywords)){
			$searching = $keywords;
		}
		if(!empty($is_dropdown)){
			$params['order'] = array('a.room_no' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.room_name LIKE '%".$searching."%' OR a.room_no LIKE '%".$searching."%' OR b.floorplan_name LIKE '%".$searching."%')";
		}
		if(!empty($floorplan_id)){
			$params['where'][] = "(b.id = '".$floorplan_id."')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();		
		
		if(!empty($show_all_text)){
			$dt = array('id' => '-1', 'room_name' => 'Choose All Room');
			array_push($newData, $dt);
		}else{
			if(!empty($show_choose_text)){
				$dt = array('id' => '', 'room_name' => 'Choose Room');
				array_push($newData, $dt);
			}
		}
		
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
		$this->table = $this->prefix.'room';				
		$session_user = $this->session->userdata('user_username');
		$floorplan_id = $this->input->post('floorplan_id');		
		$room_name = $this->input->post('room_name');
		$room_desc = $this->input->post('room_desc');
		$room_no = $this->input->post('room_no');
		
		if(empty($room_no) OR empty($room_name) OR empty($floorplan_id)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		$is_active = 0;
		if(!empty($_POST['is_active'])){
			$is_active = 1;
		}
			
		$r = '';
		if($this->input->post('form_type_masterRoom', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'room_no' => 	$room_no,
				    'room_name' => 	$room_name,
				    'room_desc' => 	$room_desc,
				    'floorplan_id' 	=> 	$floorplan_id,
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
		if($this->input->post('form_type_masterRoom', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'room_no'		=> 	$room_no,
				    'room_name' 	=> 	$room_name,
				    'room_desc' => 	$room_desc,
				    'floorplan_id' 	=> 	$floorplan_id,
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
		$this->table = $this->prefix.'room';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
		
		//Delete
		//$this->db->where("id IN (".$sql_Id.")");
		$data_update = array(
			"is_deleted" => 1
		);
		$q = $this->db->update($this->table, $data_update, "id IN (".$sql_Id.")");
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Room Failed!'); 
        }
		die(json_encode($r));
	}
	
}