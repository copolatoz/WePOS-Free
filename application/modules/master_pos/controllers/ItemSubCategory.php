<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class itemSubCategory extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_itemsubcategory', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'item_subcategory';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, b.item_category_name, b.item_category_code',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'item_category as b','b.id = a.item_category_id','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('a.id' => 'DESC'),
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
			//$params['order'] = array('item_subcategory_desc' => 'ASC');
			$params['order'] = array('id' => 'ASC');
			//$params['where'] = array('parent_id != 0');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.item_subcategory_name LIKE '%".$searching."%' OR a.item_subcategory_desc LIKE '%".$searching."%' OR b.item_category_name LIKE '%".$searching."%')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($show_all_text)){
			$dt = array('id' => '-1', 'item_subcategory_name' => 'Pilih Semua', 'item_subcategory_code_name' => 'Pilih Semua');
			array_push($newData, $dt);
		}else{
			if(!empty($show_choose_text)){
				$dt = array('id' => '', 'item_subcategory_name' => 'Pilih', 'item_subcategory_code_name' => 'Pilih');
				array_push($newData, $dt);
			}
		}
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['item_subcategory_code'] = strtoupper($s['item_subcategory_code']);
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				$s['item_subcategory_code_name'] = $s['item_subcategory_code'].' - '.$s['item_subcategory_name'];
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'item_subcategory';				
		$session_user = $this->session->userdata('user_username');
		
		$item_subcategory_name = $this->input->post('item_subcategory_name');
		$item_subcategory_code = strtoupper($this->input->post('item_subcategory_code'));
		$item_subcategory_desc = $this->input->post('item_subcategory_desc');
		$item_category_id = $this->input->post('item_category_id');
		
		if(empty($item_subcategory_name)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
		//CHECK CODE
		if(!empty($item_subcategory_code)){
			$id = $this->input->post('id', true);
			$this->db->from($this->table);
			$this->db->where("item_subcategory_code = '".$item_subcategory_code."'");
			if(!empty($id)){
				$this->db->where("id != ".$id);
			}
			$this->db->where("is_deleted = 0");
			$get_last = $this->db->get();
			if($get_last->num_rows() > 0){
				
				//available
				$r = array('success' => false, 'info' => 'Kode sudah digunakan!'); 
				die(json_encode($r));
		
			}
		}
		
			
		$r = '';
		if($this->input->post('form_type_itemSubCategory', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'item_subcategory_name'  	=> 	$item_subcategory_name,
				    'item_subcategory_code'  	=> 	$item_subcategory_code,
					'item_subcategory_desc'	=>	$item_subcategory_desc,
					'item_category_id'	=>	$item_category_id,
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
		if($this->input->post('form_type_itemSubCategory', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'item_subcategory_name'  	=> 	$item_subcategory_name,
				    'item_subcategory_code'  	=> 	$item_subcategory_code,
					'item_subcategory_desc'	=>	$item_subcategory_desc,
					'item_category_id'	=>	$item_category_id,
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
		$this->table = $this->prefix.'item_subcategory';
		
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
            $r = array('success' => false, 'info' => 'Hapus Data Gagal!'); 
        }
		die(json_encode($r));
	}
	
}
