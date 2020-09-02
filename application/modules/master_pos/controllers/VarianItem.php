<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class VarianItem extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_varianitem', 'm');
	}
	
	public function gridData()
	{
		$this->table = $this->prefix.'varian_item';
		
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
			'order'			=> array('id' => 'ASC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$show_all_text = $this->input->post('show_all_text');
		$show_choose_text = $this->input->post('show_choose_text');
		$keywords = $this->input->post('keywords');
		
		if(!empty($keywords)){
			$searching = $keywords;
		}
		if(!empty($is_dropdown)){
			$params['order'] = array('varian_name' => 'ASC');
			//$params['where'] = array('parent_id != 0');
		}
		if(!empty($searching)){
			$params['where'][] = "(varian_name LIKE '%".$searching."%')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($show_all_text)){
			$dt = array('id' => '-1', 'varian_name' => 'Pilih Semua');
			array_push($newData, $dt);
		}else{
			if(!empty($show_choose_text)){
				$dt = array('id' => '', 'varian_name' => 'Pilih Varian');
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
		$this->table = $this->prefix.'varian_item';				
		$session_user = $this->session->userdata('user_username');
		
		$varian_name = $this->input->post('varian_name');
		
		if(empty($varian_name)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
		$form_type_varianItem = $this->input->post('form_type_varianItem', true);
		$id = $this->input->post('id', true);
		$from_deleted = false;
		
		$this->db->from($this->table);
		$this->db->where("varian_name LIKE '%".$varian_name."%'");
		$q = $this->db->get();
		if($q->num_rows() > 0){
			$available_varian = false;
			foreach($q->result() as $dt){
				if(strtolower(trim($dt->varian_name)) == strtolower(trim($varian_name))){
					$available_varian = true;
					
					if($dt->is_deleted == 1){
						//update-activate
						$form_type_varianItem = 'edit';
						$id = $dt->id;
						$from_deleted = true;
					}else{
						$r = array('success' => false, 'info' => 'Varian Item: '.$varian_name.' available!');
						die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
					}
					
				}
			}
		}
			
		$r = '';
		if($form_type_varianItem == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'varian_name'  	=> 	$varian_name,
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
		if($form_type_varianItem == 'edit'){
			$var = array('fields'	=>	array(
				    'varian_name'  	=> 	$varian_name,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			//UPDATE
			if($from_deleted == true){
				$var["fields"]["is_active"] = 1;
				$var["fields"]["is_deleted"] = 0;
			}
			
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
		$this->table = $this->prefix.'varian_item';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
		
		//Delete
		//$this->db->where("id IN (".$sql_Id.")");
		//$q = $this->db->delete($this->table);
		
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
            $r = array('success' => false, 'info' => 'Hapus Data Gagal!'); 
        }
		die(json_encode($r));
	}

	public function gridDatax()
	{
		$this->table = $this->prefix.'receive_detail';
		
		$this->db->select('a.receive_det_varian_name, a.receive_det_varian_group, b.receive_status');
		$this->db->from($this->prefix.'receive_detail as a');
		$this->db->join($this->prefix.'receiving as b',"b.id = a.receive_id","LEFT");
		$this->db->where("b.receive_status = 'done'");
		$this->db->where('b.is_deleted = 0');
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$item_id = $this->input->post('item_id');
		$show_all_text = $this->input->post('show_all_text');
		$show_choose_text = $this->input->post('show_choose_text');
		
		if(!empty($is_dropdown)){
			$this->db->order_by("a.receive_det_varian_name", "ASC");
		}
		if(!empty($searching)){
			$this->db->where("(a.receive_det_varian_name LIKE '%".$searching."%')");
		}
		if(!empty($item_id)){
			$this->db->where('a.item_id = '.$item_id);
		}
		
		$this->db->group_by('a.receive_det_varian_group');
		
		//get data -> data, totalCount
		$get_data = $this->db->get();
		  		
  		$newData = array();
		
		if(!empty($show_all_text)){
			$dt = array('varian_group' => '', 'varian_name' => 'Pilih Semua');
			array_push($newData, $dt);
		}else{
			if(!empty($show_choose_text)){
				$dt = array('varian_group' => '', 'varian_name' => 'Pilih Varian');
				array_push($newData, $dt);
			}
		}
		
		if($get_data->num_rows() > 0){
			foreach($get_data->result_array() as $dt){
				$dt['varian_group'] = $dt['receive_det_varian_group'];
				$dt['varian_name'] = $dt['receive_det_varian_name'];
				array_push($newData, $dt);
			}
		}
		
		$get_data = array();
		$get_data['data'] = $newData;
		$get_data['totalCount'] = count($newData);
		
      	die(json_encode($get_data));
	}
}