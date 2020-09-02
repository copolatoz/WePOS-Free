<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ProductVarian extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_productvarian', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'product_varian';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'a.is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, c.varian_name',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'product as b','b.id = a.product_id','LEFT'),
										array($this->prefix.'varian as c','c.id = a.varian_id','LEFT')
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
		$product_id = $this->input->post('product_id');
		$show_all_text = $this->input->post('show_all_text');
		$show_choose_text = $this->input->post('show_choose_text');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.product_price' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.varian_name LIKE '%".$searching."%')";
		}
		if(!empty($product_id)){
			$params['where'][] = 'a.product_id = '.$product_id;
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		

		if(!empty($show_all_text)){
			$dt = array('id' => '-1', 'varian_name' => 'Pilih Semua');
			array_push($newData, $dt);
		}else{
			if(!empty($show_choose_text)){
				$dt = array('id' => '', 'varian_name' => 'Pilih Ukuran/Varian');
				array_push($newData, $dt);
			}
		}
		
		
		//GET PROMO
		$dt_promo = array();
		$dt_promo_id = array();
		$promo_diskon_data_product = array();
		
		$this->db->select('*');
		$this->db->from($this->prefix.'discount');
		$this->db->where('(discount_type = 0 OR discount_type = 2) AND is_promo = 1');
		$this->db->where('is_active = 1');
		$this->db->where('is_deleted = 0');
		
		$today_date = date("Y-m-d H:i:s");
		$this->db->where("(discount_date_type = 'unlimited_date' OR (discount_date_type = 'limited_date' AND ('".$today_date."' BETWEEN date_start AND date_end)))");
			
		$get_promo = $this->db->get();
		if($get_promo->num_rows() > 0){
			foreach($get_promo->result() as $dt){
				if(!in_array($dt->id, $dt_promo_id)){
					$dt_promo_id[] = $dt->id;
					$dt_promo[$dt->id] = $dt;
					
					if(empty($promo_diskon_data_product[$dt->id]) AND $dt->discount_type == 0){
						$promo_diskon_data_product[$dt->id] = array();
					}
					
				}
			}
		}
		
		
		//DISKON PRODUCT
		$promo_diskon_product_id = array();
		$promo_diskon_product = array();
		$all_on_promo = false;
		$all_on_promo_id = 0;
		
		if(!empty($dt_promo_id)){
			$dt_promo_id_sql = implode(",", $dt_promo_id);
			$this->db->select('*');
			$this->db->from($this->prefix.'discount_product');
			$this->db->where('discount_id IN ('.$dt_promo_id_sql.')');
			$get_promo_diskon = $this->db->get();
			
			if($get_promo_diskon->num_rows() > 0){
				foreach($get_promo_diskon->result() as $dt){
					if(!in_array($dt->product_id, $promo_diskon_product_id)){
						$promo_diskon_product_id[] = $dt->product_id;
						$promo_diskon_product[$dt->product_id] = $dt->discount_id;
						
						$promo_diskon_data_product[$dt->discount_id][] = $dt->product_id;
						
					}
				}
				
			}
			
		}
		
		if(!empty($promo_diskon_data_product)){
			foreach($promo_diskon_data_product as $disc_id => $dt_prod){
				if(empty($dt_prod) AND $all_on_promo == false){
					//$all_on_promo = true;
					//$all_on_promo_id = $disc_id;
				}
			}
		}
		
		//echo '<pre>'.$all_on_promo.' == '.$all_on_promo_id;
		//print_r($promo_diskon_data_product);
		//die();
		
		//DISKON BUY & GET
		/*
		$promo_buyget_product_id = array();
		$promo_buyget_product = array();
		
		if(!empty($dt_promo_id)){
			$dt_promo_id_sql = implode(",", $dt_promo_id);
			$this->db->select('*');
			$this->db->from($this->prefix.'discount_buyget');
			$this->db->where('id IN ('.$dt_promo_id_sql.')');
			$get_promo_buyget = $this->db->get();
			
			if($get_promo_buyget->num_rows() > 0){
				foreach($get_promo_buyget->result() as $dt){
					if(!in_array($dt->product_id, $promo_diskon_product_id)){
						if(!in_array($dt->product_id, $promo_buyget_product_id)){
							$promo_buyget_product_id[] = $dt->product_id;
							$promo_buyget_product[$dt->product_id] = $dt;
						}
					}
				}
			}
			
		}
		*/
		
		//get option tax and service
		$opt_var = array('include_tax','include_service',
		'default_tax_percentage','default_service_percentage',
		'takeaway_no_tax','takeaway_no_service','autohold_create_billing');
		$get_opt = get_option_value($opt_var);
		$include_tax = 0;
		if(!empty($get_opt['include_tax'])){
			$include_tax = $get_opt['include_tax'];
		}
		
		$include_service = 0;
		if(!empty($get_opt['include_service'])){
			$include_service = $get_opt['include_service'];
		}
		
		$default_tax_percentage = 10;
		if(!empty($get_opt['default_tax_percentage'])){
			$default_tax_percentage = $get_opt['default_tax_percentage'];
		}		
		
		$default_service_percentage = 5;
		if(!empty($get_opt['default_service_percentage'])){
			$default_service_percentage = $get_opt['default_service_percentage'];
		}	
		
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				$s['normal_price_show'] =  priceFormat($s['normal_price'],2);
				$s['product_price_show'] =  priceFormat($s['product_price'],2);
				$s['product_hpp_show'] =  priceFormat($s['product_hpp'],2);
				$product_price = $s['product_price'];
				//SET PROMO
				$s['promo_tipe'] = 0; //1 product, 2 buy and get
				$s['promo_id'] = 0;
				$s['is_promo'] = 0;
				$s['promo_percentage'] = 0;
				$s['promo_price'] = 0;
				$s['promo_desc'] = '';
				$no_promo = true;
				$usePromoID = 0;
				
				if(!empty($promo_diskon_product[$s['product_id']])){
					$usePromoID = $promo_diskon_product[$s['product_id']];
					$no_promo = false;
				}
				
				if($no_promo == true AND $all_on_promo){
					$usePromoID = $all_on_promo_id;
				}
				
				if(!empty($dt_promo[$usePromoID])){
					
					$s['promo_id'] = $usePromoID;
					
					$s['promo_tipe'] = 1;
					$s['is_promo'] = 1;
					$s['promo_percentage'] = $dt_promo[$usePromoID]->discount_percentage;
					$s['promo_desc'] = $dt_promo[$usePromoID]->discount_name;
					
					$promo_price = ($s['product_price'] * ($s['promo_percentage']/100));
					$product_price = $s['product_price'] - $promo_price;
					$s['product_price'] = $product_price;
					$s['promo_price'] = $promo_price;
					$s['promo_price_show'] = priceFormat($s['promo_price']);
					//$s['product_name_show'] = $s['product_name'].' <font color="orange">Promo</font>';
					$s['product_price_show'] = '<strike>'.$s['product_price_show'].'</strike> <font color="orange">'.priceFormat($s['product_price']).'</font>';
					
				}	
				
				//TAX, SERVICE, TAKE AWAY & COMPLIMENT
				$include_tax = $include_tax;
				$include_service = $include_service;
				$tax_percentage = $default_tax_percentage;
				$service_percentage = $default_service_percentage;
				
				$tax_total = 0;
				$service_total = 0;
				$product_price_real = 0;
				if(!empty($include_tax) OR !empty($include_service)){
					if(!empty($include_tax) AND !empty($include_service)){
						$all_percentage = 100 + $tax_percentage + $service_percentage;
						$one_percent = $product_price / $all_percentage;
						$tax_total = priceFormat($one_percent * $tax_percentage, 0, ".", "");
						$service_total = priceFormat($one_percent * $service_percentage, 0, ".", "");
						$product_price_real = $product_price - ($tax_total + $service_total);
						
						$tax_percent = $tax_percentage/100;
						$service_percent = $service_percentage/100;
						$tax_total = priceFormat($product_price_real * $tax_percent, 0, ".", "");
						$service_total = priceFormat($product_price_real * $service_percent, 0, ".", "");
					
					}else{
						if(!empty($include_tax)){
							$all_percentage = 100 + $tax_percentage;
							$one_percent = $product_price / $all_percentage;
							$tax_total = priceFormat($one_percent * $tax_percentage, 0, ".", "");
							$product_price_real = $product_price - ($tax_total);
							
							$tax_percent = $tax_percentage/100;
							$tax_total = priceFormat($product_price_real * $tax_percent, 0, ".", "");
							
						}
						
						if(!empty($include_service)){
							$all_percentage = 100 + $service_percentage;
							$one_percent = $product_price / $all_percentage;
							$service_total = priceFormat($one_percent * $service_percentage, 0, ".", "");
							$product_price_real = $product_price - ($service_total);
							
							$service_percent = $service_percentage/100;
							$service_total = priceFormat($product_price_real * $service_percent, 0, ".", "");
							
						}
						
					}
				}else
				{
					$product_price_real = $product_price;
					$tax_percent = $tax_percentage/100;
					$service_percent = $service_percentage/100;
					$tax_total = priceFormat($product_price* $tax_percent, 0, ".", "");
					$service_total = priceFormat($product_price* $service_percent, 0, ".", "");
				}
				
				$s['tax_price'] = $tax_total;
				$s['service_price'] = $service_total;
				
				if(!empty($show_choose_text)){
					$s['varian_name'] = $s['varian_name'].' / '.priceFormat($product_price);
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
		$this->table = $this->prefix.'product_varian';				
		$session_user = $this->session->userdata('user_username');
		
		$r = array('success' => false);
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function delete()
	{
		$this->table = $this->prefix.'product_varian';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
		
		//Delete
		//this->db->where("id IN (".$sql_Id.")");
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
            $r = array('success' => false, 'info' => 'Delete varian Failed!'); 
        }
		die(json_encode($r));
	}
	
}