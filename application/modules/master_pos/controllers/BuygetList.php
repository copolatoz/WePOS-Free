<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class BuygetList extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_buygetlist', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'discount_buyget';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'a.is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, b.product_name as buy_item_name, c.product_name as get_item_name',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'product as b','b.id = a.buy_item','LEFT'),
										array($this->prefix.'product as c','c.id = a.get_item','LEFT'),
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('a.id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$sales_type = $this->input->post('sales_type');
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$show_all_text = $this->input->post('show_all_text');
		$discount_id = $this->input->post('discount_id');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.id' => 'DESC');
		}
		
		if(!empty($discount_id)){
			$params['where'][] = "a.discount_id = ".$discount_id;
		}
		
		if(!empty($searching)){
			//$params['where'][] = "(voucher_no LIKE '%".$searching."%')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($is_dropdown)){
			
			$show_txt = '-- NO DATA --';
			if(!empty($show_all_text)){
				$show_txt = '-- ALL BUY & GET --';
			}
			
			$s = array(
				'id'			=> 0,
				'discount_id'	=> 	0,
				'voucher_no'	=> $show_txt,		
				'voucher_status'=> 0,		
				'date_used'		=> '',		
				'is_active'		=> 0	
			);
			array_push($newData, $s);
		}
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
					
				if(!empty($s['date_used']) AND $s['date_used'] != '0000-00-00'){
					$s['date_used'] = date("d-m-Y", strtotime($s['date_used']));
				}else{
					$s['date_used'] = '';
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
		$this->table = $this->prefix.'discount_buyget';				
		$session_user = $this->session->userdata('user_username');
		
		$buy_item = $this->input->post('buy_item');
		$buy_qty = $this->input->post('buy_qty');
		$get_item = $this->input->post('get_item');
		$get_qty = $this->input->post('get_qty');
		$get_percentage = $this->input->post('get_percentage');
		$discount_id = $this->input->post('discount_id');
		$buyget_tipe = $this->input->post('buyget_tipe');
		
		if(empty($discount_id) OR empty($buy_item) OR empty($buy_qty)){
			$r = array('success' => false);
			die(json_encode($r));
		}	
		
		if(empty($get_qty) AND empty($get_percentage)){
			$r = array('success' => false, 'info' => 'Choose Get Item or Percentage');
			die(json_encode($r));
		}
		
		if($get_percentage > 100){
			$r = array('success' => false, 'info' => 'Max Percentage is 100');
			die(json_encode($r));
		}
		
		if(empty($buyget_tipe)){
			$buyget_tipe = 'item';
		}
		
		if(empty($get_item) OR $get_item == 0){
			$get_item = $buy_item;
		}
		
		if(empty($get_qty)){
			
			$get_qty = 1;
			if($buyget_tipe == 'percentage'){
				$get_qty = 0;
			}
			
		}
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
		if($buyget_tipe == 'item'){
			$get_percentage = 0;
		}else{
			$get_qty = 0;
		}
		
		//UPDATE
		$id = $this->input->post('id', true);	
			
		$r = '';
		if($this->input->post('form_type_buygetList', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'discount_id' 	=> 	$discount_id,
				    'buyget_tipe' 	=> $buyget_tipe,
				    'buy_item' 		=> $buy_item,
				    'buy_qty'		=> $buy_qty,
				    'get_item' 		=> $get_item,
				    'get_qty'		=> $get_qty,
				    'get_percentage'=> $get_percentage,
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
		if($this->input->post('form_type_buygetList', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'buy_item' 		=> $buy_item,
				    'buyget_tipe'	=> $buyget_tipe,
				    'buy_qty'		=> $buy_qty,
				    'get_item' 		=> $get_item,
				    'get_qty'		=> $get_qty,
				    'get_percentage'=> $get_percentage,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
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
		$this->table = $this->prefix.'discount_buyget';
		
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
            $r = array('success' => false, 'info' => 'Delete Buy & Get Rule Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function verifyBuyget()
	{
		$this->table = $this->prefix.'discount_buyget';
		
		$buyget_id = $this->input->post('buyget_id', true);		
		$product_id = $this->input->post('product_id', true);		
		$order_qty = $this->input->post('order_qty', true);		
		$buyget_tipe = $this->input->post('buyget_tipe', true);		
		
		$this->db->select("a.*, b.discount_name, b.discount_percentage, b.discount_price, 
		b.min_total_billing, b.discount_max_price, b.discount_type");
		$this->db->from($this->table.' as a');
		$this->db->join($this->prefix.'discount as b',"b.id = a.discount_id", "LEFT");
		$this->db->where("b.id = '".$buyget_id."'");
		$this->db->where("a.buy_item = '".$product_id."'");
		$this->db->where("a.buyget_tipe = '".$buyget_tipe."'");
		$this->db->order_by("a.buy_qty", "ASC");
		$get_dt = $this->db->get();
		
		$buyget_data = array();
		$r = '';
		if($get_dt->num_rows() > 0)  
        {  
	
			$default_get_item = 0;
			$get_varian_item = 0;
			$get_percentage = 0;
			$get_qty = 0;
			$default_get_qty = 0;
			foreach($get_dt->result() as $dt){
				$buyget_data[] = $dt;
					
				if($product_id != $dt->get_item){
					$get_varian_item++;
					if(empty($default_get_item)){
						$default_get_item = $dt->get_item;
					}
				}
					
				if($order_qty >= $dt->buy_qty){
					$get_percentage = $dt->get_percentage;
				}
				
				if(empty($default_get_qty)){
					$default_get_qty = $dt->get_qty;
				}
			}
			
			if($buyget_tipe == 'percentage'){
				
				$get_percentage = str_replace(".00","",$get_percentage);
				$r = array('success' => true, 'get_percentage' => $get_percentage); 
				
			}else{
				
				if($order_qty >= $dt->buy_qty){
					$get_qty = $default_get_qty;
					
					if($order_qty > $dt->buy_qty){
						$get_qty = floor($order_qty/$dt->buy_qty)*$default_get_qty;
						//$sisa = ($order_qty%$dt->buy_qty);
					}
					
				}
				
				$r = array('success' => true, 'get_qty' => $get_qty, 'default_get_item' => $default_get_item, 'get_varian_item' => $get_varian_item); 
			}
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Verify Failed!'); 
        }
		die(json_encode($r));
	}
	
}