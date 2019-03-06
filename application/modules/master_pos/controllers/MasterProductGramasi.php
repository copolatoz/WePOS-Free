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
		$product_varian_id = $this->input->post('product_varian_id');
		$varian_id = $this->input->post('varian_id');
		
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
			'fields'		=> 'a.*, b.item_name, b.item_price as item_price_acuan, c.item_category_name, d.unit_name, e.has_varian',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'items as b','b.id = a.item_id','LEFT'),
										array($this->prefix.'item_category as c','c.id = b.category_id','LEFT'),
										array($this->prefix.'unit as d','d.id = b.unit_id','LEFT'),
										array($this->prefix.'product as e','e.id = a.product_id','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('a.id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);		
		
		if(!empty($product_id)){
			$params['where'][] = array('a.product_id' => $product_id);
		}
		
		if(!empty($varian_id)){
			$params['where'][] = "(e.has_varian = 1 AND a.varian_id = ".$varian_id.")";
		}else{
			$params['where'][] = "(e.has_varian = 0 AND (a.varian_id IS NULL OR a.varian_id =0))";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['item_price_acuan'] = 'Rp '.priceFormat($s['item_price_acuan']);
				
				$s['total_item_price'] = round($s['item_qty']*$s['item_price'],2);
				$s['total_item_price_show'] = priceFormat($s['total_item_price'],2);
				
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
		$varian_id = $this->input->post('varian_id');		
		$product_varian_id = $this->input->post('product_varian_id');		
		$has_varian = $this->input->post('has_varian');		
		
		if(empty($product_id) OR empty($product_id)){
			$r = array('success' => false);
			die(json_encode($r));
		}	
		
		if(empty($product_varian_id)){
			$product_varian_id = 0;
		}	
		
		if(empty($varian_id)){
			$varian_id = 0;
		}	
		
		if(!empty($has_varian)){
			if(empty($product_varian_id) OR empty($varian_id)){
				$r = array('success' => false, 'info' => 'Silahkan Pilih Varian!');
				die(json_encode($r));
			}
		}
			
		
		$id = $this->input->post('id', true);
			
		$active_old_data = false;
		if($this->input->post('form_type_masterProductGramasi', true) == 'add')
		{
			$this->db->select("*");
			$this->db->from($this->table);
			$this->db->where("product_id = ".$product_id." AND item_id = ".$item_id." AND product_varian_id = ".$product_varian_id." AND varian_id = ".$varian_id." AND is_deleted = 0");
			$dt_varian = $this->db->get();
			if($dt_varian->num_rows() > 0){
				$get_prod_var = $dt_varian->row();
				$id = $get_prod_var->id;
				$active_old_data = true;
			}
			
			if($dt_varian->num_rows() >= 2){
				$r = array('success' => false, 'info' => 'Silahkan Hapus Item yang sama!');
				die(json_encode($r));
			}
		}
		
		$r = '';
		if($this->input->post('form_type_masterProductGramasi', true) == 'add' AND $active_old_data == false)
		{
			$var = array(
				'fields'	=>	array(
				    'product_id'  => 	$product_id,
				    'item_id'  		=> 	$item_id,
					'item_price'	=>	$item_price,
					'item_qty'		=>	$item_qty,
					'varian_id'			=>	$varian_id,
					'product_varian_id'	=>	$product_varian_id,
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
				
				//update HPP product
				$product_hpp = $this->m->product_hpp($product_id, $varian_id);
				$r['product_hpp'] = $product_hpp['product_hpp'];
				$r['varian_id'] = $product_hpp['varian_id'];
							
				$this->m->update_sales_price($product_id, $item_id, $varian_id);
				
			}  
			else
			{  				
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_masterProductGramasi', true) == 'edit' OR $active_old_data == true){
			$var = array('fields'	=>	array(
				    //'product_id'  => 	$product_id,
				    'item_id'  		=> 	$item_id,
					'item_price'	=>	$item_price,
					'item_qty'		=>	$item_qty,
					'varian_id'			=>	$varian_id,
					'product_varian_id'	=>	$product_varian_id,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_deleted'	=>	0,
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
								
			//UPDATE
			$this->lib_trans->begin();
				$update = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				$r = array('success' => true, 'id' => $id);
				
				//update HPP product
				$product_hpp = $this->m->product_hpp($product_id, $varian_id);
				$r['product_hpp'] = $product_hpp['product_hpp'];
				$r['varian_id'] = $product_hpp['varian_id'];
				
				$this->m->update_sales_price($product_id, $item_id, $varian_id);
							
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
				
		$this->db->from($this->table);
		$this->db->where("id IN (".$sql_Id.")");
		$get_product_gramasi = $this->db->get();
		
		//Delete
		$data_update = array(
			"is_deleted" => 1
		);
		$q = $this->db->update($this->table, $data_update, "id IN (".$sql_Id.")");
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true, 'info' => 'Delete Product Success!'); 
			
			$product_id = 0;
			$varian_id = 0;
			if($get_product_gramasi->num_rows() > 0){
				$dt_product_gramasi = $get_product_gramasi->row();
				$product_id = $dt_product_gramasi->product_id;
				$varian_id = $dt_product_gramasi->varian_id;
			}
			
			//update HPP product
			$product_hpp = $this->m->product_hpp($product_id, $varian_id);
			$r['product_hpp'] = $product_hpp['product_hpp'];
			$r['varian_id'] = $product_hpp['varian_id'];
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Product Gramasi Failed!'); 
        }
		die(json_encode($r));
	}
	
}