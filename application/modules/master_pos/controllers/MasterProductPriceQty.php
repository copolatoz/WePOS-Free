<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MasterProductPriceQty extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_masterproductpriceqty', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'product_price';
		
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
			'is_active_text' => 'a.is_active',
			'qty_from_show' => 'a.qty_from',
			'qty_till_show' => 'a.qty_till',
			'product_price_show' => 'a.product_price'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, e.has_varian',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array(
										array($this->prefix.'product as e','e.id = a.product_id','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('a.qty_from' => 'DESC','a.id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);		
		
		if(!empty($product_id)){
			$params['where'][] = array('product_id' => $product_id);
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
				
				$s['priceqty_id'] = $s['id'];
				$s['qty_from_show'] = priceFormat($s['qty_from']);
				$s['qty_till_show'] = priceFormat($s['qty_till']);
				$s['product_price_show'] = priceFormat($s['product_price']);
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
		$this->table = $this->prefix.'product_price';	
		$this->table_product_price = $this->prefix.'product_price';			
		$session_user = $this->session->userdata('user_username');
		
		
		$product_id = $this->input->post('product_id');
		$qty_from = $this->input->post('qty_from');
		$qty_till = $this->input->post('qty_till');
		$product_price = $this->input->post('product_price');	
		$varian_id = $this->input->post('varian_id');		
		$product_varian_id = $this->input->post('product_varian_id');		
		$has_varian = $this->input->post('has_varian');	
		$product_price_default = $this->input->post('product_price_default');	
		
		if(empty($product_id) OR empty($qty_till)){
			$r = array('success' => false);
			die(json_encode($r));
		}	
		
		
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
		
		if($qty_till < $qty_from){
			$r = array('success' => false, 'info' => 'data Qty Max < Qty Min');
			die(json_encode($r));
		}
		
		if($product_price > $product_price_default AND !empty($product_price_default)){
			$r = array('success' => false, 'info' => 'Price/Qty > default: Rp. '.priceFormat($product_price_default));
			die(json_encode($r));
		}
		
		//check from-till
		$available_qty = false;
		$this->db->select("*");
		$this->db->from($this->table_product_price);
		$this->db->where("product_id = ".$product_id." AND ((qty_from BETWEEN '".$qty_from."' AND '".$qty_till."') OR (qty_till BETWEEN '".$qty_from."' AND '".$qty_till."')) AND is_deleted = 0");
		if(!empty($id)){
			$this->db->where("id != ".$id);
		}
		
		$cek_qty = $this->db->get();
		if($cek_qty->num_rows() > 0){
			$get_data = $cek_qty->row();
			$r = array('success' => false, 'info' => 'data qty sdh tersedia: '.$get_data->qty_from.' - '.$get_data->qty_till);
			die(json_encode($r));
		}
		
		$save_ok = false;
		$r = '';
		if($this->input->post('form_type_masterProductPriceQty', true) == 'add' AND $available_qty == false)
		{
			
			$var = array(
				'fields'	=>	array(
				    'product_id'  => 	$product_id,
				    'qty_from'  	=> 	$qty_from,
				    'qty_till'  	=> 	$qty_till,
					'product_price'	=>	$product_price,
					'varian_id'			=>	$varian_id,
					'product_varian_id'	=>	$product_varian_id,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				),
				'table'		=>  $this->table_product_price
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
				$save_ok = true;
			}  
			else
			{  				
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_masterProductPriceQty', true) == 'edit' OR $available_qty == true){
			$var = array('fields'	=>	array(
				    'qty_from'  	=> 	$qty_from,
				    'qty_till'  	=> 	$qty_till,
					'product_price'	=>	$product_price,
					'varian_id'			=>	$varian_id,
					'product_varian_id'	=>	$product_varian_id,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_deleted'	=>	0,
				),
				'table'			=>  $this->table_product_price,
				'primary_key'	=>  'id'
			);
								
			//UPDATE
			
			$this->lib_trans->begin();
				$update = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				$r = array('success' => true, 'id' => $id);
				$save_ok = true;
				
			}  
			else
			{ 				
				$r = array('success' => false);
			}
		}
		
		if($save_ok == true){
			$this->db->select("*");
			$this->db->from($this->table_product_price);
			$this->db->where("product_id = ".$product_id." AND is_deleted = 0");
			$has_active_varian = $this->db->get();
			if($has_active_varian->num_rows() > 1){
				$update_product = array('has_list_price' => 1);
				$this->db->update($this->table_product,$update_product,"id = ".$product_id);
			}else{
				$update_product = array('has_list_price' => 0);
				$this->db->update($this->table_product,$update_product,"id = ".$product_id);
			}
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function delete()
	{
		$this->table_product = $this->prefix.'product';
		$this->table_product_price = $this->prefix.'product_price';
		
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
		$q = $this->db->update($this->table_product_price, $data_update, "id IN (".$sql_Id.")");
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true, 'info' => 'Hapus Price Qty Sukses!'); 
			
			//get product id
			$this->db->select("*");
			$this->db->from($this->table_product_price);
			$this->db->where("id IN (".$sql_Id.")");
			$dt_varian = $this->db->get();
			$product_id = 0;
			if($dt_varian->num_rows() > 0){
				$dt_var = $dt_varian->row();
				$product_id = $dt_var->product_id;
				
				$this->db->select("*");
				$this->db->from($this->table_product_price);
				$this->db->where("product_id = ".$product_id." AND is_deleted = 0");
				$has_active_varian = $this->db->get();
				if($has_active_varian->num_rows() > 1){
					$update_product = array('has_list_price' => 1);
					$this->db->update($this->table_product,$update_product,"id = ".$product_id);
				}else{
					$update_product = array('has_list_price' => 0);
					$this->db->update($this->table_product,$update_product,"id = ".$product_id);
				}
				
			}
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Hapus Price Qty Gagal!'); 
        }
		die(json_encode($r));
	}
	
	
	public function cekPriceQty()
	{
		$product_id = $this->input->post('product_id', true);		
		$order_qty = $this->input->post('order_qty', true);		
		$has_varian = $this->input->post('has_varian', true);		
		$varian_id = $this->input->post('varian_id', true);		
		
		$dt_post_price = array(
			'product_id' => $product_id,
			'order_qty' => $order_qty,
			'has_varian' => $has_varian,
			'varian_id' => $varian_id,
			'return_data' => false
		);
		
		$get_cekPriceQty = $this->m->cekPriceQty($dt_post_price);
		
		if(empty($get_cekPriceQty)){
			$r = array('success' => false, 'info' => 'Hapus Price Qty Gagal!'); 
		}
		
		die(json_encode($r));
	}
	
}