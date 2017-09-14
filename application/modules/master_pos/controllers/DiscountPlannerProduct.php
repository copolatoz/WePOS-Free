<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class DiscountPlannerProduct extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_discountplannerproduct', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'discount_product';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'b.is_active',
			'product_name' => 'b.product_name',
			'category_name' => 'd.product_category_name'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, a.id as discount_product_id, b.product_name, c.discount_name, d.product_category_name, d.product_category_name as category_name",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'product as b','b.id = a.product_id','LEFT'),
										array($this->prefix.'discount as c','c.id = a.discount_id','LEFT'),
										array($this->prefix.'product_category as d','d.id = b.category_id','LEFT')
									) 
								),
			'sort_alias'	=> $sortAlias,
			//'order'			=> array('b.product_name' => 'ASC'),
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		$params['where'][] = "b.is_active = 1";
		$params['where'][] = "b.is_deleted = 0";
		$params['where'][] = "a.is_deleted = 0";
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$discount_id = $this->input->post('discount_id');
		$product_id = $this->input->post('product_id');
		$keywords = $this->input->post('keywords');
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('d.supplier_name' => 'ASC');
		}
		if(!empty($discount_id)){
			$params['where'][] = "a.discount_id = ".$discount_id."";
		}		
		if(!empty($product_id)){
			$params['where'][] = "a.product_id = ".$product_id."";
		}		
		if(!empty($searching)){
			$params['where'][] = "(d.discount_name LIKE '%".$searching."%' OR b.product_name LIKE '%".$searching."%')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				if($s['product_name'] != ''){
					$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
					array_push($newData, $s);
				}
				
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function gridDataProduct()
	{
		$discount_id = $this->input->post('discount_id');
		$sort = $this->input->post('sort');
		$limit = $this->input->post('limit');
		
		if(!empty($sort)){
			$sort = json_decode($sort, true);
			$sort = $sort[0];
		}
		
		$data_disc_product = array();
		if(!empty($discount_id)){
			
			$this->db->from($this->prefix.'discount_product as a');
			$this->db->where("a.is_deleted = 0");
			$this->db->where("a.is_active = 1");
			$this->db->where("a.discount_id = ".$discount_id);
			$this->db->limit($limit);
			$getDiscProduct = $this->db->get();
			
			if($getDiscProduct->num_rows() > 0){
				foreach($getDiscProduct->result() as $dt){
					
					if(!in_array($dt->id, $data_disc_product)){
						
						$data_disc_product[] = $dt->product_id;
						
					}
					
				}
			}
		}
		
		$this->db->select("a.id as product_id, a.product_name, b.product_category_name as category_name");
		$this->db->from($this->prefix.'product as a');
		$this->db->join($this->prefix.'product_category as b',"b.id = a.category_id","LEFT");
		$this->db->where("a.is_deleted = 0");
		$this->db->where("a.is_active = 1");
		$this->db->limit($limit);
		
		$sortAlias = array(
			'is_active_text' => 'a.is_active',
			'product_name' => 'a.product_name',
			'category_name' => 'b.product_category_name'
		);	
		
		if(!empty($sort)){
			$desc_sort = 'ASC';
			if(!empty($sort['direction'])){
				$desc_sort = $sort['direction'];
			}
			
			if(!empty($sort['property'])){
				$sort['property'] = strtr($sort['property'], $sortAlias);
				$this->db->order_by($sort['property'],$desc_sort);
			}
			
		}
		
		$getProduct = $this->db->get();
		
		$data_product = array();
		if($getProduct->num_rows() > 0){
			foreach($getProduct->result() as $dt){
				if(!in_array($dt->product_id, $data_disc_product)){
					$data_product[] = $dt;
				}
			}
		}
		
		$get_data = array();
		$get_data['success'] = true;
		$get_data['data'] = $data_product;
		$get_data['totalCount'] = count($data_product);
		
      	die(json_encode($get_data));
		
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'discount_product';				
		$session_user = $this->session->userdata('user_username');
				
		$discount_id = $this->input->post('discount_id');
		$product_id = $this->input->post('product_id');
		
		if(empty($product_id) OR empty($discount_id)){
			$r = array('success' => false);
			die(json_encode($r));
		}
		
		$r = '';
		if($this->input->post('form_type_discountPlannerProduct', true) == 'add')
		{	
			
			//check supplier item
			$this->db->select('id');
			$this->db->from($this->table);
			$this->db->where('discount_id', $discount_id);
			$this->db->where('product_id', $product_id);
			$get_discount_product = $this->db->get();
			if($get_discount_product->num_rows() > 0){
				$r = array('success' => false, 'info'	=> "Product been added on list<br/>double click to edit Product");
				die(json_encode($r));
				die();
			}
			
			$var = array(
				'fields'	=>	array(
				    'discount_id'  => 	$discount_id,
				    'product_id'  => 	$product_id,
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
		if($this->input->post('form_type_discountPlannerProduct', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'discount_id'  => 	$discount_id,
				    'product_id'  => 	$product_id,
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
	
	public function addProduct()
	{
		$this->table = $this->prefix.'discount_product';				
		$session_user = $this->session->userdata('user_username');
		
		$date_add = date('Y-m-d H:i:s');
		
		$discount_id = $this->input->post('discount_id', true);		
		$product_id = $this->input->post('product_id', true);		
		$product_id = json_decode($product_id, true);
		
		if(empty($discount_id)){
			$r = array('success' => false, 'info' => 'Discount not identified!'); 
			die(json_encode($r));
		}
		
		$data_product = array();
		
		if(empty($product_id)){
			$r = array('success' => false, 'info' => 'Product Cannot Empty, Select Product!'); 
			die(json_encode($r));
		}else{
			
			foreach($product_id as $id){
				$data_product[] = array(
					'discount_id'  	=> 	$discount_id,
				    'product_id'  	=> 	$id,
					'created'		=>	$date_add,
					'createdby'		=>	$session_user,
					'updated'		=>	$date_add,
					'updatedby'		=>	$session_user
				);
			}
			
		}
		
		
		$r = '';
		$q = false;
		if(!empty($data_product)){
			$q = $this->db->insert_batch($this->table, $data_product);
		}
		
		if($q)  
        {  
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Add Discount Product Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function delete()
	{
		$this->table = $this->prefix.'discount_product';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
				
		$q = $this->db->delete($this->table, "id IN (".$sql_Id.")");
		
		/*
		$data_update = array(
			"is_deleted" => 1
		);
		$q = $this->db->update($this->table, $data_update, "id IN (".$sql_Id.")");
		*/
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Supplier Item Failed!'); 
        }
		die(json_encode($r));
	}
	
}