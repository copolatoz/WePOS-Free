<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MasterStoreHouse extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_masterstorehouse', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'storehouse';
		$this->storehouse_user = $this->prefix.'storehouse_users';
		$session_id_user = $this->session->userdata('id_user');
		
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
		$except_primary = $this->input->post('except_primary');
		$is_active = $this->input->post('is_active');
		$show_all_text = $this->input->post('show_all_text');
		$show_choose_text = $this->input->post('show_choose_text');
		
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('storehouse_name' => 'ASC');
		}
		
		if(!empty($searching)){
			$params['where'][] = "(storehouse_name LIKE '%".$searching."%' OR storehouse_code LIKE '%".$searching."%')";
		}
		
		if(!empty($except_primary)){
			
			//get store by user id
			$all_store_id = array();
			$this->db->from($this->storehouse_user);
			$this->db->where('user_id', $session_id_user);
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				foreach($get_dt->result() as $dt){
					if(!in_array($dt->storehouse_id, $all_store_id)){
						$all_store_id[] = $dt->storehouse_id;
					}
				}
			}
			
			if(!empty($all_store_id)){
				$all_store_id_txt = implode(",", $all_store_id);
				$params['where'][] = "id IN ($all_store_id_txt)";
			}
			
		}
		
		if(!empty($is_active)){
			$params['where'][] = "is_active = 1";
		}
		
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($show_all_text)){
			$dt = array('id' => '-1', 'storehouse_name' => 'Semua Gudang');
			array_push($newData, $dt);
		}else{
			if(!empty($show_choose_text)){
				$dt = array('id' => '', 'storehouse_name' => 'Pilih Gudang');
				array_push($newData, $dt);
			}
		}
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				$s['is_primary_text'] = ($s['is_primary'] == '1') ? '<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>';
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'storehouse';				
		$session_user = $this->session->userdata('user_username');
		
		$storehouse_code = $this->input->post('storehouse_code');
		$storehouse_name = $this->input->post('storehouse_name');
		$storehouse_desc = $this->input->post('storehouse_desc');
		
		if(empty($storehouse_name) OR empty($storehouse_name)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
		$is_primary = $this->input->post('is_primary');
		if(empty($is_primary)){
			$is_primary = 0;
		}
		
		//check code
		//Delete
		$this->db->from($this->table);
		$this->db->where("storehouse_code = '".$storehouse_code."'");
		if($this->input->post('form_type_masterStoreHouse', true) == 'edit'){
			$id = $this->input->post('id', true);
			$this->db->where("id != ".$id);
		}
		$q = $this->db->get();
		if($q->num_rows() > 0){
			$r = array('success' => false, 'info' => "Warehouse Code Available!");
			die(json_encode($r));
		}
			
		$r = '';
		if($this->input->post('form_type_masterStoreHouse', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
					'storehouse_code'	=>	$storehouse_code,
				    'storehouse_name'  	=> 	$storehouse_name,
				    'storehouse_desc'  	=> 	$storehouse_desc,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'	=>	$is_active,
					'is_primary'	=>	$is_primary
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

				if($is_primary == 1){
					
					$update_data = array(
						'is_primary'	=> 0
					);
					
					$this->db->update($this->table, $update_data, "id != ".$insert_id);
					
				}
				
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_masterStoreHouse', true) == 'edit'){
			$var = array('fields'	=>	array(
					'storehouse_code'	=>	$storehouse_code,
				    'storehouse_name'  	=> 	$storehouse_name,
				    'storehouse_desc'  	=> 	$storehouse_desc,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active,
					'is_primary'	=>	$is_primary
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

				if($is_primary == 1){
					
					$update_data = array(
						'is_primary'	=> 0
					);
					
					$this->db->update($this->table, $update_data, "id != ".$id);
					
				}
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
		$this->table = $this->prefix.'storehouse';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
		
		//Delete
		$data_update = array(
			"is_deleted" => 1
		);
		$q = $this->db->update($this->table,$data_update,"id IN (".$sql_Id.")");
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Store House Failed!'); 
        }
		die(json_encode($r));
	}
	
}