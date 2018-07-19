<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class DiscountPlanner extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_discountplanner', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'discount';
		
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
		$is_discBilling = $this->input->post('is_discBilling');
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$show_valid_date = $this->input->post('show_valid_date');
		$keywords = $this->input->post('keywords');
		
		
		if(!empty($keywords)){
			$searching = $keywords;
		}
		if(!empty($is_dropdown)){
			$params['order'] = array('discount_desc' => 'ASC');
		}
		
		if(!empty($is_discBilling)){
			
			if($is_discBilling == -1){
				
			}else{
				//$params['where'][] = "(is_discount_billing = 1)";
				$params['where'][] = "(discount_type = 1)";
			}
			
		}else{
			$params['where'][] = "(discount_type = 0) AND is_promo = 0 AND is_buy_get = 0";
		}
		
		if(!empty($searching)){
			$params['where'][] = "(discount_name LIKE '%".$searching."%')";
		}
		if(!empty($show_valid_date)){
			$today_date = date("Y-m-d H:i:s");
			$params['where'][] = "(discount_date_type = 'unlimited_date' OR (discount_date_type = 'limited_date' AND ('".$today_date."' BETWEEN date_start AND date_end)))";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($is_dropdown)){
			$s = array(
				'id'	=> '',
				'discount_name'	=> '-- NO DISCOUNT --'		
			);
			array_push($newData, $s);
		}
		
		$get_opt_var = array('diskon_sebelum_pajak_service');
		$get_opt = get_option_value($get_opt_var);
		
		$diskon_sebelum_pajak_service = 0;
		if(!empty($get_opt['diskon_sebelum_pajak_service'])){
			$diskon_sebelum_pajak_service = $get_opt['diskon_sebelum_pajak_service'];
		}
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				$s['is_discount_billing_text'] = ($s['is_discount_billing'] == '1') ? '<span style="color:green;">Per-Billing</span>':'<span style="color:red;">Per-Item</span>';
				
				$s['is_promo_text'] = ($s['is_promo'] == '1') ? '<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>';
				$s['is_buy_get_text'] = ($s['is_buy_get'] == '1') ? '<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>';
				
				if($s['discount_type'] == '2'){
					$s['discount_type_text'] = 'Buy & Get'; 
				}else
				if($s['discount_type'] == '1'){
					$s['discount_type_text'] = 'Per-Billing'; 
				}else{
					$s['discount_type_text'] = 'Per-Item Order'; 
				}
					
					
				if(!empty($s['date_start']) AND $s['date_start'] != '0000-00-00 00:00:00'){
					$s['date_start'] = date("d-m-Y", strtotime($s['date_start']));
				}else{
					$s['date_start'] = '';
				}
				
				if(!empty($s['date_end']) AND $s['date_end'] != '0000-00-00 00:00:00'){
					$s['date_end'] = date("d-m-Y", strtotime($s['date_end']));
				}else{
					$s['date_end'] = '';
				}
				
				$s['diskon_sebelum_pajak_service'] = $diskon_sebelum_pajak_service;
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'discount';				
		$session_user = $this->session->userdata('user_username');
		
		$discount_name = $this->input->post('discount_name');
		$discount_percentage = $this->input->post('discount_percentage');
		$discount_price = $this->input->post('discount_price');
		$discount_max_price = $this->input->post('discount_max_price');
		$min_total_billing = $this->input->post('min_total_billing');
		$discount_type = $this->input->post('discount_type');
		$discount_date_type = $this->input->post('discount_date_type');
		$discount_product = $this->input->post('discount_product');
		$discount_desc = $this->input->post('discount_desc');
		$date_start = $this->input->post('date_start');
		$date_end = $this->input->post('date_end');
		$discount_allow_day = $this->input->post('discount_allow_day');
		$use_discount_time = $this->input->post('use_discount_time');
		$discount_time_start = $this->input->post('discount_time_start');
		$discount_time_end = $this->input->post('discount_time_end');
		
		if(empty($discount_name)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		/*$is_discount_billing = $this->input->post('is_discount_billing');
		if(empty($is_discount_billing)){
			$is_discount_billing = 0;
		}*/			
		
		$is_promo = $this->input->post('is_promo');
		if(empty($is_promo)){
			$is_promo = 0;
		}	
		
		$is_buy_get = $this->input->post('is_buy_get');
		if(empty($is_buy_get)){
			$is_buy_get = 0;
		}	
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
		$is_sistem_tawar = $this->input->post('is_sistem_tawar');
		if(empty($is_sistem_tawar)){
			$is_sistem_tawar = 0;
		}
		
		if(empty($use_discount_time)){
			$use_discount_time = 0;
		}
		
		/*if($discount_product == 1 && $is_discount_billing == 1){
			$r = array('success' => false, 'info' => "Use selected Product not allowed for Disc/Billing!");
			die(json_encode($r));
		}*/
		
		if(($discount_type == 0 or $discount_type == 2) && !empty($discount_max_price)){
			if($is_sistem_tawar == 0){
				$r = array('success' => false, 'info' => "Max Disc.Price only allowed for Disc/Billing!");
				die(json_encode($r));
			}
			
		}
		
		if($discount_type == 1 && $is_promo == 1){
			$r = array('success' => false, 'info' => "Set as Promo not allowed for Disc/Billing!");
			die(json_encode($r));
		}
		
		if($discount_date_type == 'unlimited_date' && $is_promo == 1){
			$r = array('success' => false, 'info' => "Set as Promo only allowed for limited date!");
			die(json_encode($r));
		}
		
		if($is_sistem_tawar == 1 && $is_promo == 1){
			$r = array('success' => false, 'info' => "Set as Promo not allowed for Sistem Tawar!");
			die(json_encode($r));
		}
			
		if($discount_date_type == 'unlimited_date' && $is_buy_get == 1){
			$r = array('success' => false, 'info' => "Set as Buy &amp Get only allowed for limited date!");
			die(json_encode($r));
		}
		
		$date_start = $date_start.' 00:00:00';
		$date_end = $date_end.' 23:59:59';
		
		//UPDATE
		$id = $this->input->post('id', true);
			
		//check if promo + active date available	
			
		$r = '';
		if($this->input->post('form_type_discountPlanner', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'discount_name' => 	$discount_name,
				    'discount_percentage' => $discount_percentage,
				    'discount_price' => $discount_price,
				    'discount_max_price' => $discount_max_price,
				    'min_total_billing' => $min_total_billing,
				    'discount_type' => $discount_type,
				    'discount_date_type' => $discount_date_type,
				    'discount_product' => $discount_product,
					'discount_desc'	=>	$discount_desc,
					'date_start'	=>	$date_start,
					'date_end'		=>	$date_end,
					'discount_allow_day'	=>	$discount_allow_day,
					'use_discount_time'		=>	$use_discount_time,
					'discount_time_start'	=>	$discount_time_start,
					'discount_time_end'		=>	$discount_time_end,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_sistem_tawar'	=>	$is_sistem_tawar,
					'is_active'	=>	$is_active,
					'is_promo'	=>	$is_promo,
					'is_buy_get'	=>	$is_buy_get,
					//'is_discount_billing'	=>	$is_discount_billing
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
		if($this->input->post('form_type_discountPlanner', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'discount_name' => 	$discount_name,
				    'discount_percentage' => $discount_percentage,
				    'discount_price' => $discount_price,
				    'discount_max_price' => $discount_max_price,
				    'min_total_billing' => $min_total_billing,
				    'discount_type' => $discount_type,
				    'discount_date_type' => $discount_date_type,
				    'discount_product' => $discount_product,
					'discount_desc'	=>	$discount_desc,
					'date_start'	=>	$date_start,
					'date_end'		=>	$date_end,
					'discount_allow_day'	=>	$discount_allow_day,
					'use_discount_time'		=>	$use_discount_time,
					'discount_time_start'	=>	$discount_time_start,
					'discount_time_end'	=>	$discount_time_end,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_sistem_tawar'		=>	$is_sistem_tawar,
					'is_active'		=>	$is_active,
					'is_promo'		=>	$is_promo,
					'is_buy_get'		=>	$is_buy_get,
					//'is_discount_billing'		=>	$is_discount_billing
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
		$this->table = $this->prefix.'discount';
		
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
            $r = array('success' => false, 'info' => 'Delete Discount Planner Failed!'); 
        }
		die(json_encode($r));
	}
	
}