<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MasterProductVarian extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_masterproductvarian', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'product_varian';
		
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
			'fields'		=> 'a.*, b.varian_name',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'varian as b','b.id = a.varian_id','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('a.id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);		
		
		if(!empty($product_id)){
			$params['where'][] = array('product_id' => $product_id);
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['product_price_show'] = priceFormat($s['product_price']);
				$s['normal_price_show'] = priceFormat($s['normal_price']);
				$s['product_hpp_show'] = priceFormat($s['product_hpp']);
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table_product = $this->prefix.'product';				
		$this->table_product_gramasi = $this->prefix.'product_gramasi';				
		$this->table = $this->prefix.'product_varian';				
		$session_user = $this->session->userdata('user_username');
		
		
		$product_id = $this->input->post('product_id');
		$varian_id = $this->input->post('varian_id');
		$product_price = $this->input->post('product_price');
		$normal_price = $this->input->post('normal_price');		
		$product_hpp = $this->input->post('product_hpp');		
		
		if(empty($product_id) OR empty($varian_id)){
			$r = array('success' => false);
			die(json_encode($r));
		}	
		$id = $this->input->post('id', true);
		
		$active_old_data = false;
		if($this->input->post('form_type_masterProductVarian', true) == 'add')
		{
			$this->db->select("*");
			$this->db->from($this->table);
			$this->db->where("product_id = ".$product_id." AND varian_id = ".$varian_id." AND is_deleted = 1");
			$dt_varian = $this->db->get();
			if($dt_varian->num_rows() > 0){
				$get_prod_var = $dt_varian->row();
				$id = $get_prod_var->id;
				$active_old_data = true;
			}
		}
		
		$r = '';
		if($this->input->post('form_type_masterProductVarian', true) == 'add' AND $active_old_data == false)
		{
			
			$var = array(
				'fields'	=>	array(
				    'product_id'  => 	$product_id,
				    'varian_id'  	=> 	$varian_id,
					'product_price'	=>	$product_price,
					'normal_price'	=>	$normal_price,
					'product_hpp'	=>	$product_hpp,
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
				#update product status
				$update_product = array('has_varian' => 1);
				$this->db->update($this->table_product,$update_product,"id = ".$product_id);
				
				//check if gramasi has varian
				$this->db->from($this->table_product_gramasi);
				$this->db->where("product_id = ".$product_id." AND (varian_id = 0 OR varian_id IS NULL)");
				$get_gramasi = $this->db->get();
				if($get_gramasi->num_rows() > 0){
					//update all varian = 0
					$update_gramasi = array('varian_id' => $varian_id);
					$this->db->update($this->table_product_gramasi, $update_gramasi, "id = ".$product_id." AND (varian_id = 0 OR varian_id IS NULL) ");
				}
				
			}  
			else
			{  				
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_masterProductVarian', true) == 'edit' OR $active_old_data == true){
			$var = array('fields'	=>	array(
				    //'product_id'  => 	$product_id,
				    'varian_id'  	=> 	$varian_id,
					'product_price'	=>	$product_price,
					'normal_price'	=>	$normal_price,
					'product_hpp'	=>	$product_hpp,
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
				$update_product = array('has_varian' => 1);
				$this->db->update($this->table_product,$update_product,"id = ".$product_id);
				
				//check if gramasi has varian
				$this->db->from($this->table_product_gramasi);
				$this->db->where("product_id = ".$product_id." AND (varian_id = 0 OR varian_id IS NULL)");
				$get_gramasi = $this->db->get();
				if($get_gramasi->num_rows() > 0){
					//update all varian = 0
					$update_gramasi = array('varian_id' => $varian_id);
					$this->db->update($this->table_product_gramasi, $update_gramasi, "id = ".$product_id." AND (varian_id = 0 OR varian_id IS NULL)");
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
		$this->table_product = $this->prefix.'product';
		$this->table_product_gramasi = $this->prefix.'product_gramasi';
		$this->table_product_package = $this->prefix.'product_package';
		$this->table_product_varian = $this->prefix.'product_varian';
		
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
		$q = $this->db->update($this->table_product_varian, $data_update, "id IN (".$sql_Id.")");
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true, 'info' => 'Delete Product Varian Success!'); 
			
			//$update_gramasi = array('is_deleted' => 1);
			//$this->db->update($this->table_product_gramasi, $update_gramasi, "product_varian_id IN (".$sql_Id.")");
				
			//get product id
			$this->db->select("*");
			$this->db->from($this->table_product_varian);
			$this->db->where("id IN (".$sql_Id.")");
			$dt_varian = $this->db->get();
			$product_id = 0;
			if($dt_varian->num_rows() > 0){
				$dt_var = $dt_varian->row();
				$product_id = $dt_var->product_id;
				
				$this->db->select("*");
				$this->db->from($this->table_product_varian);
				$this->db->where("product_id = ".$product_id." AND is_deleted = 0");
				$has_active_varian = $this->db->get();
				if($has_active_varian->num_rows() > 0){
					$update_product = array('has_varian' => 1);
					$this->db->update($this->table_product,$update_product,"id = ".$product_id);
				}else{
					$update_product = array('has_varian' => 0);
					$this->db->update($this->table_product,$update_product,"id = ".$product_id);
				}
				
			}
			
			//check gramasi -> product varian = 0
			$this->db->select("a.id, a.product_varian_id");
			$this->db->from($this->table_product_gramasi.' as a');
			$this->db->join($this->table_product_varian.' as b',"b.id = a.product_varian_id","LEFT");
			$this->db->where("a.product_id IN (".$product_id.")");
			$this->db->where("b.is_deleted = 1");
			$dt_gramasi = $this->db->get();
			if($dt_gramasi->num_rows() > 0){
				$all_deleted_gramasi = array();
				foreach($dt_gramasi->result() as $dt){
					$all_deleted_gramasi[] = $dt->id;
				}
			
				if(!empty($all_deleted_gramasi)){
					$all_deleted_gramasi_sql = implode(",", $all_deleted_gramasi);
					$update_gramasi = array('is_deleted' => 1);
					$this->db->update($this->table_product_gramasi, $update_gramasi, "id IN (".$all_deleted_gramasi_sql.")");
				}
			}
			
			
			//check package -> product varian = 0
			$this->db->select("a.id, a.package_id, a.product_varian_id");
			$this->db->from($this->table_product_package.' as a');
			$this->db->join($this->table_product_varian.' as b',"b.id = a.product_varian_id","LEFT");
			$this->db->where("a.package_id IN (".$product_id.")");
			$this->db->where("b.is_deleted = 1");
			$dt_package = $this->db->get();
			if($dt_package->num_rows() > 0){
				$all_deleted_package = array();
				foreach($dt_package->result() as $dt){
					$all_deleted_package[] = $dt->id;
				}
			
				if(!empty($all_deleted_package)){
					$all_deleted_package_sql = implode(",", $all_deleted_package);
					$update_package = array('is_deleted' => 1);
					$this->db->update($this->table_product_package, $update_package, "id IN (".$all_deleted_package_sql.")");
				}
			}
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Product Varian Failed!'); 
        }
		die(json_encode($r));
	}
	
}