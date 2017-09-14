<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MasterProductGramasi extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_masterproductgramasi', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'product_gramasi';
		$this->product_img_url = RESOURCES_URL.'product/thumb/';
		
		//product_id
		$product_id = $this->input->post('product_id');
		
		if(empty($product_id)){
			$r = array('success' => false);
			die(json_encode($r));
		}
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'a.is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, b.item_name, b.item_price as item_price_acuan, c.item_category_name, d.unit_name',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'items as b','b.id = a.item_id','LEFT'),
										array($this->prefix.'item_category as c','c.id = b.category_id','LEFT'),
										array($this->prefix.'unit as d','d.id = b.unit_id','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('a.id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);		
		
		if(!empty($product_id)){
			$params['where'] = array('product_id' => $product_id);
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['item_price_acuan'] = 'Rp '.priceFormat($s['item_price_acuan']);
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'product_gramasi';				
		$session_user = $this->session->userdata('user_username');
		
		
		$product_id = $this->input->post('product_id');
		$item_id = $this->input->post('item_id');
		$item_price = $this->input->post('item_price');
		$item_qty = $this->input->post('item_qty');		
		
		if(empty($product_id) OR empty($product_id)){
			$r = array('success' => false);
			die(json_encode($r));
		}	
			
		$r = '';
		if($this->input->post('form_type_masterProductGramasi', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'product_id'  => 	$product_id,
				    'item_id'  		=> 	$item_id,
					'item_price'	=>	$item_price,
					'item_qty'		=>	$item_qty,
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
		if($this->input->post('form_type_masterProductGramasi', true) == 'edit'){
			$var = array('fields'	=>	array(
				    //'product_id'  => 	$product_id,
				    'item_id'  		=> 	$item_id,
					'item_price'	=>	$item_price,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
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
		$this->table = $this->prefix.'product_gramasi';
		
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
		$q = $this->db->update($this->table, $data_update, "id IN (".$sql_Id.")");
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Product Gramasi Failed!'); 
        }
		die(json_encode($r));
	}
	
}