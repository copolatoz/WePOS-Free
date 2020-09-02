<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class itemCategory extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_itemcategory', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'item_category';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'is_active',
			'as_product_category_text' => 'as_product_category'
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
		$show_all_text = $this->input->post('show_all_text');
		$show_choose_text = $this->input->post('show_choose_text');
		$keywords = $this->input->post('keywords');
		
		if(!empty($keywords)){
			$searching = $keywords;
		}
		if(!empty($is_dropdown)){
			$params['order'] = array('item_category_name' => 'ASC');
			//$params['where'] = array('parent_id != 0');
		}
		if(!empty($searching)){
			$params['where'][] = "(item_category_name LIKE '%".$searching."%' OR item_category_desc LIKE '%".$searching."%')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($show_all_text)){
			$dt = array('id' => '-1', 'item_category_name' => 'Pilih Semua', 'item_category_code_name' => 'Pilih Semua');
			array_push($newData, $dt);
		}else{
			if(!empty($show_choose_text)){
				$dt = array('id' => '', 'item_category_name' => 'Pilih', 'item_category_code_name' => 'Pilih');
				array_push($newData, $dt);
			}
		}
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['item_category_code'] = strtoupper($s['item_category_code']);
				
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				$s['as_product_category_text'] = ($s['as_product_category'] == '1') ? '<span style="color:green;">Ya</span>':'<span style="color:red;">Tidak</span>';
				$s['as_product_category_old'] = $s['as_product_category'];
				$s['item_category_code_name'] = $s['item_category_code'].' - '.$s['item_category_name'];
				
				if(empty($s['item_category_code'])){
					//$s['item_category_code'] = substr($s['item_category_name'],0,3);
					$s['item_category_code_name'] = substr($s['item_category_name'],0,3);
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
		$this->table = $this->prefix.'item_category';				
		$this->table_product_category = $this->prefix.'product_category';				
		$session_user = $this->session->userdata('user_username');
		
		$item_category_name = $this->input->post('item_category_name');
		$item_category_code = $this->input->post('item_category_code');
		$item_category_desc = $this->input->post('item_category_desc');
		
		if(empty($item_category_name)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
		$add_product_category = false;
		$as_product_category_old = $this->input->post('as_product_category_old');
		$as_product_category = $this->input->post('as_product_category');
		if(empty($as_product_category)){
			$as_product_category = 0;
		}
		
		//CHECK CODE
		if(!empty($item_category_code)){
			$id = $this->input->post('id', true);
			$this->db->from($this->table);
			$this->db->where("item_category_code = '".$item_category_code."'");
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
		if($this->input->post('form_type_itemCategory', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'item_category_name'  	=> 	$item_category_name,
				    'item_category_code'  	=> 	$item_category_code,
					'item_category_desc'	=>	$item_category_desc,
					'as_product_category'	=>	$as_product_category,
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

				if($as_product_category == 1){
					$add_product_category = true;
					$id = $insert_id;
				}
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_itemCategory', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'item_category_name'  	=> 	$item_category_name,
				    'item_category_code'  	=> 	$item_category_code,
					'item_category_desc'	=>	$item_category_desc,
					'as_product_category'	=>	$as_product_category,
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
				
				if($as_product_category == 1 AND $as_product_category_old == 0){
					$add_product_category = true;
				}
				
				if($as_product_category == 0 AND $as_product_category_old == 1){
					$update_prodcat = array(
						'updated'			=>	date('Y-m-d H:i:s'),
						'updatedby'			=>	$session_user,
						'is_active'			=>	0,
						'is_deleted'		=>	1
					);
					$this->db->update($this->table_product_category, $update_prodcat, "from_item_category = ".$id);	
				}
				
				if($as_product_category == 1 AND $as_product_category_old == 1){
					$update_prodcat = array(
						'updated'			=>	date('Y-m-d H:i:s'),
						'updatedby'			=>	$session_user,
						'is_active'			=>	$is_active
					);
					$this->db->update($this->table_product_category, $update_prodcat, "from_item_category = ".$id);	
				}
				
			}  
			else
			{  
				$r = array('success' => false);
			}
		}
		
		//add product category
		if(!empty($id) AND $add_product_category == true){
			$this->db->from($this->table_product_category);
			$this->db->where("from_item_category = ".$id);
			$cek_prodcat = $this->db->get();
			if($cek_prodcat->num_rows() > 0){
				$update_prodcat = array(
					'updated'			=>	date('Y-m-d H:i:s'),
					'updatedby'			=>	$session_user,
					'is_active'			=>	1,
					'is_deleted'		=>	0
				);
				$this->db->update($this->table_product_category, $update_prodcat, "from_item_category = ".$id);	
			}else{
				$insert_prodcat = array(
					'product_category_name'	=> 	$item_category_name,
					'product_category_code'	=> 	$item_category_code,
					'product_category_desc'	=>	$item_category_desc,
					'from_item_category'	=>	$id,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active
				);
					
				$this->db->insert($this->table_product_category, $insert_prodcat);	
			}
			
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function delete()
	{
		$this->table = $this->prefix.'item_category';
		$this->table_product_category = $this->prefix.'product_category';			
		$session_user = $this->session->userdata('user_username');
		
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
			$update_prodcat = array(
				'updated'			=>	date('Y-m-d H:i:s'),
				'updatedby'			=>	$session_user,
				'is_active'			=>	0,
				'is_deleted'		=>	1
			);
			$this->db->update($this->table_product_category, $update_prodcat, "from_item_category IN (".$sql_Id.")");	
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Hapus Data Gagal!'); 
        }
		die(json_encode($r));
	}
	
}
