<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MasterFloorplan extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_MasterFloorplan', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'floorplan';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'is_active'
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
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$keywords = $this->input->post('keywords');
		$purpose = $this->input->post('purpose');
		
		if(!empty($keywords)){
			$searching = $keywords;
		}
		if(!empty($is_dropdown) OR $purpose == 'floorplanList'){
			$params['order'] = array('list_no' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(floorplan_name LIKE '%".$searching."%' OR floorplan_desc LIKE '%".$searching."%')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		
  		$newData = array();
		
		if($purpose == 'floorplanList'){
			$floorplan_info = '<div style="font-size:12px; margin:5px">Floorplan/lantai:</div>';
			$floorplan_info .= '<div style="font-size:16px; margin:5px 0px 15px;"><b>Semua Lantai/Floorplan</b></div>';
			$floorplan_info .= '<div style="font-size:10px;">Klik u/ lihat Table</div>';
			$dt = array('id' => '0', 'floorplan_name' => 'Semua Floorplan/Lantai', 'floorplan_info' => $floorplan_info);
			array_push($newData, $dt);
		}
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				if($purpose == 'floorplanList'){
					$s['floorplan_info'] = '<div style="font-size:12px; margin:5px">Floorplan/lantai:</div>';
					$s['floorplan_info'] .= '<div style="font-size:18px; margin:5px 0px 15px;"><b>'.$s['floorplan_name'].'</b></div>';
					$s['floorplan_info'] .= '<div style="font-size:10px;">Klik u/ lihat Table</div>';
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
		$this->table = $this->prefix.'floorplan';				
		$session_user = $this->session->userdata('user_username');
		
		$floorplan_name = $this->input->post('floorplan_name');
		$floorplan_desc = $this->input->post('floorplan_desc');
		$floorplan_image = $this->input->post('floorplan_image');
		
		if(empty($floorplan_name)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
			
		$r = '';
		if($this->input->post('form_type_masterFloorplan', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'floorplan_name'  	=> 	$floorplan_name,
					'floorplan_desc'	=>	$floorplan_desc,
					'floorplan_image'	=>	$floorplan_image,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'	=>	$is_active
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
		if($this->input->post('form_type_masterFloorplan', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'floorplan_name'  	=> 	$floorplan_name,
					'floorplan_desc'	=>	$floorplan_desc,
					'floorplan_image'	=>	$floorplan_image,
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
		$this->table = $this->prefix.'floorplan';
		
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
            $r = array('success' => false, 'info' => 'Delete Floor Plan Failed!'); 
        }
		die(json_encode($r));
	}
	
}