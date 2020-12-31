<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MasterItem extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_masteritem', 'm');
		$this->load->model('inventory/model_stock', 'stock');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'items';
		$this->table_stock = $this->prefix.'stock';
		$this->item_img_url = RESOURCES_URL.'items/thumb/';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'is_active',
			'item_type_name' => 'item_type'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, b.unit_name, b.unit_code, c.supplier_name, d.item_category_name, d.item_category_code,
								e.item_subcategory_name, e.item_subcategory_code',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'unit as b','b.id = a.unit_id','LEFT'),
										array($this->prefix.'supplier as c','c.id = a.supplier_id','LEFT'),
										array($this->prefix.'item_category as d','d.id = a.category_id','LEFT'),
										array($this->prefix.'item_subcategory as e','e.id = a.subcategory_id','LEFT')
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
		$supplier_id = $this->input->post('supplier_id');
		$keywords = $this->input->post('keywords');
		$is_active = $this->input->post('is_active');
		$is_last_stock = $this->input->post('is_last_stock');
		$storehouse_id = $this->input->post('storehouse_id');
		$from_distribution = $this->input->post('from_distribution');
		
		$use_current_stock = false;
		$all_item_stock = array();
		if(!empty($from_distribution)){
			
			if(!empty($storehouse_id)){
				
				$use_current_stock = true;
				$this->db->from($this->prefix.'stock_rekap');
				$this->db->where("trx_date", date("Y-m-d"));
				$this->db->where("storehouse_id", $storehouse_id);
				$get_stock = $this->db->get();
				if($get_stock->num_rows() > 0){
					foreach($get_stock->result() as $dt){
						
						if(!in_array($dt->item_id, $all_item)){
							$all_item_stock[] = $dt->item_id;
						}
						
					}
				}
				
			}else{
				$get_data = array();
				$get_data['data'] = array();
				$get_data['totalCount'] = 0;
				die(json_encode($get_data));
			}
			
		}
		
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('item_name' => 'ASC');
			$params['where'][] = array('a.is_active' => 1);
		}
		if(!empty($searching)){
			$params['where'][] = "(a.item_code LIKE '".$searching."%' OR a.item_sku LIKE '".$searching."%' OR a.item_name LIKE '%".$searching."%' OR d.item_category_name LIKE '%".$searching."%')";
		}
		if(!empty($supplier_id)){
			$params['where'][] = "a.supplier_id = ".$supplier_id."";
		}
		
		if(!empty($is_active)){
			
			if($is_active == 1){
				$params['where'][] = array('a.is_active' => 1);
			}
			
		}else{
			
			if(is_numeric($is_active)){
				$params['where'][] = array('a.is_active' => 0);
			}
			
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['item_code'] = strtoupper($s['item_code']);
				$s['item_sku'] = strtoupper($s['item_sku']);
				$s['item_category_code'] = strtoupper($s['item_category_code']);
				$s['item_subcategory_code'] = strtoupper($s['item_subcategory_code']);
				
				if(empty($from_distribution)){
					if(empty($s['item_image'])){
						$s['item_image'] = 'no-image.jpg';
					}
					//$s['item_image_show'] = '<img src="'.$this->item_img_url.$s['item_image'].'" style="max-width:80px; max-height:60px;"/>';
					//$s['item_image_src'] = $this->item_img_url.$s['item_image'];
					$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
					$s['sales_price_show'] = 'Rp '.priceFormat($s['sales_price']);
					$s['item_price_show'] = 'Rp '.priceFormat($s['item_price']);
					$s['item_hpp_show'] = 'Rp '.priceFormat($s['item_hpp']);
					$s['last_in_show'] = 'Rp '.priceFormat($s['last_in']);
					
					if(empty($s['item_type'])){
						$s['item_type'] = 'main';
					}
					
					$s['item_type_name'] = 'Bahan Baku';
					if($s['item_type'] == 'support'){
						$s['item_type_name'] = 'Bahan Pendukung';
					}
					
					$s['use_for_sales_text'] = ($s['use_for_sales'] == '1') ? '<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>';
					$s['sales_use_tax_text'] = ($s['sales_use_tax'] == '1') ? '<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>';
					$s['sales_use_service_text'] = ($s['sales_use_service'] == '1') ? '<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>';
					
					$s['total_qty_stok'] = 0;
					
					$s['is_kerjasama_text'] = ($s['is_kerjasama'] == '1') ? '<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>';
					$s['total_bagi_hasil_show'] = priceFormat($s['total_bagi_hasil']);
					
					$s['use_stok_kode_unik_text'] = ($s['use_stok_kode_unik'] == '1') ? '<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>';
					
				}
				
				$s['item_code_name'] = $s['item_code'].' / '.$s['item_name'];
				$s['item_name_code'] = $s['item_name'].' / '.$s['item_code'];
				
				if($use_current_stock == true){
					if(in_array($s['id'], $all_item_stock)){
						array_push($newData, $s);
					}
				}else{
					array_push($newData, $s);
				}
				
				
			}
		}
		
		if(!empty($is_last_stock)){
			if(!empty($storehouse_id)){
				//check last stock - stock_rekap
				$all_item = array();
				$all_item_stock = array();
				$this->db->from($this->prefix.'stock_rekap');
				$this->db->where("trx_date", date("Y-m-d"));
				$this->db->where("storehouse_id", $storehouse_id);
				$get_stock = $this->db->get();
				if($get_stock->num_rows() > 0){
					foreach($get_stock->result() as $dt){
						
						if(!in_array($dt->item_id, $all_item)){
							$all_item[] = $dt->item_id;
							$all_item_stock[$dt->item_id] = $dt->total_stock;
						}
						
					}
				}
				
				//echo '<pre>';
				//print_r($all_item_stock);
				//die();
				
				$newData_old = $newData;
				$newData = array();
				foreach($newData_old as $dt){
					
					if(!empty($all_item_stock[$dt['id']])){
						$dt['total_qty_stok'] = $all_item_stock[$dt['id']];
					}
					
					$newData[] = $dt;
				}
				
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}

	public function gridDataSKU()
	{
		$this->table = $this->prefix.'items';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'is_active',
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
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
		$keywords = $this->input->post('keywords');
		$show_all_text = $this->input->post('show_all_text');
		$show_choose_text = $this->input->post('show_choose_text');
		
		
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('item_sku' => 'ASC');
			$params['where'][] = array('a.is_active' => 1);
		}
		if(!empty($searching)){
			$params['where'][] = "(a.item_code LIKE '".$searching."%' OR a.item_sku LIKE '".$searching."%' OR a.item_name LIKE '%".$searching."%' OR a.item_sku LIKE '%".$searching."%')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$kodeSKU = array();
  		$newDataSKU = array();
		
		
		if(!empty($show_all_text)){
			$dt = array('item_sku' => '', 'item_sku_name' => 'Pilih Semua');
			array_push($newDataSKU, $dt);
		}else{
			if(!empty($show_choose_text)){
				$dt = array('item_sku' => '', 'item_sku_name' => 'Pilih');
				array_push($newDataSKU, $dt);
			}
		}
		
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['item_code'] = strtoupper($s['item_code']);
				$s['item_sku'] = strtoupper($s['item_sku']);
				$s['item_category_code'] = strtoupper($s['item_category_code']);
				$s['item_subcategory_code'] = strtoupper($s['item_subcategory_code']);
				
				$item_name = explode(" - ", $s['item_name']);
				$s['item_name'] = $item_name[0];
				
				$s['item_sku_name'] = $s['item_sku'].' / '.$s['item_name'];
				
				if(!in_array($s['item_sku'], $kodeSKU) AND !empty($s['item_sku'])){
					$kodeSKU[] = $s['item_sku'];
					$newDataSKU[] = array(
						'item_sku' => $s['item_sku'],
						'item_sku_name' => $s['item_sku_name'],
					);
				}
				
			}
		}
		
		$get_data['data'] = $newDataSKU;
		$get_data['totalCount'] = count($newDataSKU);
		
      	die(json_encode($get_data));
	}

	public function gridDataDistribution()
	{
		$this->table = $this->prefix.'items';
		$this->table_stock = $this->prefix.'stock';
		$this->item_img_url = RESOURCES_URL.'items/thumb/';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'is_active',
			'item_type_name' => 'item_type'
		);		
		
			
		// Default Parameter
		$params = array(
			'fields'		=> 'a.id, a.item_code, a.item_sku, a.item_name, a.item_price, a.sales_price, a.item_hpp, a.last_in, a.unit_id, b.unit_code, b.unit_name, a.use_stok_kode_unik',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'unit as b','b.id = a.unit_id','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('a.item_code' => 'ASC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$supplier_id = $this->input->post('supplier_id');
		$keywords = $this->input->post('keywords');
		$is_last_stock = $this->input->post('is_last_stock');
		$storehouse_id = $this->input->post('storehouse_id');
		$from_module = $this->input->post('from_module');
		
		$use_current_stock = false;
		$all_item_stock = array();
		if($from_module == 'usagewaste' OR $from_module == 'production' OR $from_module == 'distribution' OR $from_module == 'salesorder'){
			
			if(!empty($storehouse_id)){
				
				$use_current_stock = true;
				$this->db->from($this->prefix.'stock_rekap');
				$this->db->where("trx_date", date("Y-m-d"));
				$this->db->where("storehouse_id", $storehouse_id);
				$get_stock = $this->db->get();
				if($get_stock->num_rows() > 0){
					foreach($get_stock->result() as $dt){
						
						if(!in_array($dt->item_id, $all_item_stock)){
							$all_item_stock[] = $dt->item_id;
						}
						
					}
				
				}else{
						
					//GENERATE PERDAY
					$storehouse_item = array($storehouse_id => array());
					$this->db->select("a.item_id, a.storehouse_id");
					$this->db->from($this->table_stock." as a");
					$this->db->join($this->table.' as b',"b.id = a.item_id");
					$this->db->where("a.storehouse_id IN (".$storehouse_id.")");
					$this->db->where('b.is_deleted = 0');
					$this->db->where('b.is_active = 1');
					$this->db->group_by('a.item_id');
					$this->db->group_by('a.storehouse_id');
					$get_item = $this->db->get();
					if($get_item->num_rows() > 0){
						foreach($get_item->result_array() as $s){
							
							if(!in_array($s['item_id'],$storehouse_item[$storehouse_id])){
								$storehouse_item[$storehouse_id][] = $s['item_id'];
							}
							
						}
					}
					
					$post_params = array(
						'storehouse_item'	=> $storehouse_item
					);
					
					$updateStock = $this->stock->update_stock_rekap($post_params);
					
					$this->db->from($this->prefix.'stock_rekap');
					$this->db->where("trx_date", date("Y-m-d"));
					$this->db->where("storehouse_id", $storehouse_id);
					$get_stock = $this->db->get();
					if($get_stock->num_rows() > 0){
						foreach($get_stock->result() as $dt){
							
							if(!in_array($dt->item_id, $all_item_stock)){
								$all_item_stock[] = $dt->item_id;
							}
							
						}
					
					}
				}
				
			}else{
				$get_data = array();
				$get_data['data'] = array();
				$get_data['totalCount'] = 0;
				die(json_encode($get_data));
			}
			
		}
		
		if(empty($storehouse_id)){
			$get_data = array();
			$get_data['data'] = array();
			$get_data['totalCount'] = 0;
			die(json_encode($get_data));
		}
		
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('item_name' => 'ASC');
			$params['where'][] = array('a.is_active' => 1);
		}
		if(!empty($searching)){
			$params['where'][] = "(item_code LIKE '%".$searching."%' OR item_sku LIKE '%".$searching."%' OR item_name LIKE '%".$searching."%' OR c.supplier_name LIKE '%".$searching."%')";
		}
		if(!empty($supplier_id)){
			$params['where'][] = "a.supplier_id = ".$supplier_id."";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
			
  		$newData = array();
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				if($from_module == 'salesorder'){
					$s['sales_price_show'] = 'Rp '.priceFormat($s['sales_price']);
				}else{
					$s['item_price_show'] = 'Rp '.priceFormat($s['item_price']);
				}
				
				
				$s['item_hpp_show'] = 'Rp '.priceFormat($s['item_hpp']);
				$s['last_in_show'] = 'Rp '.priceFormat($s['last_in']);
				$s['item_code_name'] = $s['item_code'].' / '.$s['item_name'];
				
				$s['use_stok_kode_unik_text'] = '<font color="red">Tidak</font>';
				if(!empty($s['use_stok_kode_unik'])){
					$s['use_stok_kode_unik_text'] = '<font color="green">Ya</font>';
				}
				
				if($use_current_stock == true){
					if(in_array($s['id'], $all_item_stock)){
						array_push($newData, $s);
					}
				}else{
					array_push($newData, $s);
				}
				
				
			}
		}
		
		if(!empty($is_last_stock)){
			if(!empty($storehouse_id)){
				//check last stock - stock_rekap
				$all_item = array();
				$all_item_stock = array();
				$this->db->from($this->prefix.'stock_rekap');
				$this->db->where("trx_date", date("Y-m-d"));
				$this->db->where("storehouse_id", $storehouse_id);
				$get_stock = $this->db->get();
				if($get_stock->num_rows() > 0){
					foreach($get_stock->result() as $dt){
						
						if(!in_array($dt->item_id, $all_item)){
							$all_item[] = $dt->item_id;
							$all_item_stock[$dt->item_id] = $dt->total_stock;
						}
						
					}
				}
				
				//echo '<pre>';
				//print_r($all_item_stock);
				//die();
				
				$newData_old = $newData;
				$newData = array();
				foreach($newData_old as $dt){
					
					if(!empty($all_item_stock[$dt['id']])){
						$dt['total_qty_stok'] = $all_item_stock[$dt['id']];
					}
					
					$newData[] = $dt;
				}
				
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'items';							
		$session_user = $this->session->userdata('user_username');
				
		$item_code = $this->input->post('item_code');
		if($item_code == '- AUTO -'){
			$item_code = '';
		}
		
		$form_module_masterItem = $this->input->post('form_module_masterItem');
		$item_name = $this->input->post('item_name');
		$item_sku = $this->input->post('item_sku');
		$item_desc = $this->input->post('item_desc');
		$sales_price = $this->input->post('sales_price');
		$item_price = $this->input->post('item_price');
		$unit_id = $this->input->post('unit_id');
		$category_id = $this->input->post('category_id');
		$subcategory_id = $this->input->post('subcategory_id');
		$item_category_code = $this->input->post('item_category_code');
		$item_subcategory_code = $this->input->post('item_subcategory_code');
		$supplier_id = $this->input->post('supplier_id');
		$item_image = $this->input->post('item_image');
		$item_type = $this->input->post('item_type');
		$item_hpp = $this->input->post('item_hpp');
		$min_stock = $this->input->post('min_stock');
		$id_ref_product = $this->input->post('id_ref_product');
		$persentase_bagi_hasil = $this->input->post('persentase_bagi_hasil');
		$item_manufacturer = '';
		$tipe = $this->input->post('tipe');
		
		if(empty($item_type)){
			$item_type = 'main';
		}
		
		/*CONTENT IMAGE UPLOAD SIZE*/		
		$this->item_img_url = RESOURCES_URL.'items/';		
		$this->item_img_path_big = RESOURCES_PATH.'items/big/';
		$this->item_img_path_thumb = RESOURCES_PATH.'items/thumb/';
		$this->item_img_path_tiny = RESOURCES_PATH.'items/tiny/';
		
		$big_size_width = 640;
		$big_size_height = 480;
		$thumb_size_width = 160;
		$thumb_size_height = 120;
		$tiny_size_width = 80;
		$tiny_size_height = 60;
		$is_upload_file = false;		
		if(!empty($_FILES['upload_image']['name'])){
						
			$config['upload_path'] = $this->item_img_path_big;
			$config['allowed_types'] = 'gif|jpg|png';
			$config['max_size']	= '1024';

			$this->load->library('upload', $config);

			if(!$this->upload->do_upload("upload_image"))
			{
				$data = $this->upload->display_errors();
				$r = array('success' => false, 'info' => $data );
				die(json_encode($r));
			}
			else
			{
				$is_upload_file = true;
				$data_upload_temp = $this->upload->data();
				$r = array('success' => true, 'info' => $data_upload_temp); 
			}
		}
		
		
		if(empty($item_name)){
			$r = array('success' => false, 'info' => 'Nama Item harus diisi');
			die(json_encode($r));
		}		
		
		if(empty($item_sku)){
			//$r = array('success' => false, 'info' => 'SKU harus diisi');
			//die(json_encode($r));
		}		
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
		$use_for_sales = $this->input->post('use_for_sales');
		if(empty($use_for_sales)){
			$use_for_sales = 0;
		}
		
		$sales_use_tax = $this->input->post('sales_use_tax');
		if(empty($sales_use_tax)){
			$sales_use_tax = 0;
		}
		
		$sales_use_service = $this->input->post('sales_use_service');
		if(empty($sales_use_service)){
			$sales_use_service = 0;
		}
		
		$is_kerjasama = $this->input->post('is_kerjasama');
		if(empty($is_kerjasama)){
			$is_kerjasama = 0;
		}
		
		$use_stok_kode_unik = $this->input->post('use_stok_kode_unik');
		if(empty($use_stok_kode_unik)){
			$use_stok_kode_unik = 0;
		}
		
		//uupdate-2011.001
		$qty_unit = $this->input->post('qty_unit');
		if(empty($qty_unit)){
			$qty_unit = 1;
		}
		
		$total_bagi_hasil = 0;
		if($is_kerjasama == 1){
			
			if(empty($persentase_bagi_hasil) OR empty($supplier_id)){
				$r = array('success' => false, 'info' => 'Input Persentase &amp; Supplier');
				die(json_encode($r));
			}
			//$total_bagi_hasil = numberFormat($sales_price*($persentase_bagi_hasil/100));
			$total_bagi_hasil = ($sales_price*($persentase_bagi_hasil/100));
			
		}
		
		if(empty($sales_price)){
			$sales_price = 0;
		}
			
		
		$opt_value = array(
			'item_sku_from_code',
		);
		
		$get_opt = get_option_value($opt_value);
		
		$item_sku_from_code = 0;
		if(!empty($get_opt['item_sku_from_code'])){
			$item_sku_from_code = $get_opt['item_sku_from_code'];
		}
		
			
		$r = '';
		if($this->input->post('form_type_masterItem', true) == 'add')
		{
			$get_item_code = array('item_code' => '', 'item_no' => 1);
			$item_no = 1;
			
			$this->db->select("id");
			$this->db->from($this->table);
			$this->db->order_by("id", "DESC");
			$this->db->limit("1");
			$get_last_no = $this->db->get();
			if($get_last_no->num_rows() > 0){
				$get_last_db = $get_last_no->row();
				$item_no = $get_last_db->id;
				$item_no++;
			}
			
			if(empty($item_code)){
				
				//cek item code
				$get_item_code = $this->generate_item_code($form_module_masterItem);
				$item_code = $get_item_code['item_code'];
				$item_no = $get_item_code['item_no'];
				
			}
			
			$this->db->from($this->table);
			$this->db->where("item_code = '".$item_code."'");
			$this->db->where("is_deleted = 0");
			$get_last = $this->db->get();
			if($get_last->num_rows() > 0){
				
				//available
				$r = array('success' => false, 'info' => 'Kode sudah digunakan!'); 
				
				//suggestion
				if(!empty($item_category_code) OR !empty($item_subcategory_code)){
					$get_item_code = $this->generate_item_code($tipe);
					$r = array('success' => false, 'info' => 'Kode sudah digunakan!<br/>Try use this code: '.$get_item_code['item_code']); 
				}
				
				die(json_encode($r));
		
			}
			
			
			//$r = array('success' => false, 'info' => $get_item_code, 'item_no' => $get_item_code['item_no']);
			//die(json_encode($r));
			
			if(empty($item_sku) AND $item_sku_from_code == 1){
				$item_sku = $get_item_code['item_code'];
			}
			
			$var = array(
				'fields'	=>	array(
				    'item_code' => 	$item_code,
				    'item_no' => 	$item_no,
				    'item_sku' => 	$item_sku,
				    'item_name' => 	$item_name,
					'item_desc'	=>	$item_desc,
					'unit_id'	=>	$unit_id,
					'category_id'	=>	$category_id,
					'subcategory_id'	=>	$subcategory_id,
					'supplier_id'	=>	$supplier_id,
					'sales_price'=>	$sales_price,
					'item_price'=>	$item_price,
					'item_hpp'	=>	$item_hpp,
					'min_stock'	=>	$min_stock,
					'item_type'	=>	$item_type,
					//'item_manufacturer'	=>	$item_manufacturer,
					'use_for_sales'	=>	$use_for_sales,
					'sales_use_tax'	=>	$sales_use_tax,
					'sales_use_service'	=>	$sales_use_service,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'	=>	$is_active,
					'is_kerjasama'	=>	$is_kerjasama,
					'persentase_bagi_hasil'	=>	$persentase_bagi_hasil,
					'total_bagi_hasil'	=>	$total_bagi_hasil,
					'use_stok_kode_unik'	=>	$use_stok_kode_unik,
					'qty_unit'		=>	$qty_unit
				),
				'table'		=>  $this->table
			);				
			
			
			if($is_upload_file){
				$get_file = do_thumb($data_upload_temp, $this->item_img_path_big, $this->item_img_path_big, '', $big_size_width, $big_size_height, TRUE, 'height');
				$var['fields']['item_image'] = $get_file;
				
			}
			
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
				$q = $this->m->add($var);
				$insert_id = $this->m->get_insert_id();
			$this->lib_trans->commit();			
			if($q)
			{  
				if($is_upload_file){					
					//thumb width 
					do_thumb($data_upload_temp, $this->item_img_path_big, $this->item_img_path_thumb, '', $thumb_size_width);
					
					//tiny
					do_thumb($data_upload_temp, $this->item_img_path_big, $this->item_img_path_tiny, '', $tiny_size_width, $tiny_size_height, TRUE, 'height');
				}
				
				if($use_for_sales == 1){
					//CREATE AS MENU SALES
				}
				
				$r = array('success' => true, 'id' => $insert_id); 				
			}  
			else
			{  
				if($is_upload_file){
					//unset upload file
					@unlink($this->item_img_path_big.$data_upload_temp['file_name']);
					
				}
				
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_masterItem', true) == 'edit'){
			
			
			if(empty($item_code)){
				
				//cek item code
				$get_item_code = $this->generate_item_code($form_module_masterItem);
				$item_code = $get_item_code['item_code'];
				$item_no = $get_item_code['item_no'];
				
			}
			
			if($item_sku_from_code == 1){
				$item_sku = $item_code;
			}
			
			$var = array('fields'	=>	array(
				    'item_sku' => 	$item_sku,
				    'item_name' => 	$item_name,
					'item_desc'	=>	$item_desc,
					'sales_price'=>	$sales_price,
					'item_price'=>	$item_price,
					'unit_id'	=>	$unit_id,
					'category_id'	=>	$category_id,
					'subcategory_id'	=>	$subcategory_id,
					'supplier_id'	=>	$supplier_id,
					'item_hpp'	=>	$item_hpp,
					'min_stock'	=>	$min_stock,
					'item_type'	=>	$item_type,
					'use_for_sales'	=>	$use_for_sales,
					'sales_use_tax'	=>	$sales_use_tax,
					'sales_use_service'	=>	$sales_use_service,
					//'item_manufacturer'	=>	$item_manufacturer,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active,
					'is_kerjasama'	=>	$is_kerjasama,
					'persentase_bagi_hasil'	=>	$persentase_bagi_hasil,
					'total_bagi_hasil'	=>	$total_bagi_hasil,
					'use_stok_kode_unik'	=>	$use_stok_kode_unik,
					'qty_unit'		=>	$qty_unit
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
						
			if($is_upload_file){
				$get_file = do_thumb($data_upload_temp, $this->item_img_path_big, $this->item_img_path_big, '', $big_size_width, $big_size_height, TRUE, 'height');
				$var['fields']['item_image'] = $get_file;
			}
			
				
			//UPDATE
			$id = $this->input->post('id', true);
			$this->lib_trans->begin();
				$update = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				//update supplier item unit
				if(!empty($id)){
					$update_supplier_item = array('unit_id'	=> $unit_id);
					$this->db->update($this->prefix.'supplier_item',$update_supplier_item,"item_id = ".$id);
				}
				
				
				if($is_upload_file){					
					//thumb width 200pixel
					do_thumb($data_upload_temp, $this->item_img_path_big, $this->item_img_path_thumb, '', $thumb_size_width);
					
					//tiny
					do_thumb($data_upload_temp, $this->item_img_path_big, $this->item_img_path_tiny, '', $tiny_size_width, $tiny_size_height, TRUE, 'height');
					
					//unset old file
					if(!empty($item_image) AND $item_image != 'no-image.jpg'){
						@unlink($this->item_img_path_big.$item_image);
						@unlink($this->item_img_path_thumb.$item_image);
						@unlink($this->item_img_path_tiny.$item_image);
					}
				}
				
				$r = array('success' => true, 'id' => $id);
			}  
			else
			{  
				if($is_upload_file){
					//unset upload file
					@unlink($this->item_img_path_big.$data_upload_temp['file_name']);					
				}
				
				$r = array('success' => false);
			}
		}
			
		if($use_for_sales == 1){
			
			//cek kategori produk
			$category_product_id = 0;
			$this->db->from($this->prefix.'item_category');
			$this->db->where("id = '".$category_id."'");
			$cek_cat = $this->db->get();
			if($cek_cat->num_rows() > 0){
				$dt_cat = $cek_cat->row();
				
				$data_category = array(
					'product_category_code' =>  $dt_cat->item_category_code,
					'product_category_name' =>  $dt_cat->item_category_name,
					'product_category_desc'	=>	$dt_cat->item_category_desc,
					'updated'				=>	date('Y-m-d H:i:s'),
					'updatedby'				=>	$session_user,
					'is_active'				=>	$is_active
				);
				
				$this->db->from($this->prefix.'product_category');
				$this->db->where("product_category_name = '".$dt_cat->item_category_name."'");
				$cek_cat_product = $this->db->get();
				if($cek_cat_product->num_rows() > 0){
					
					$dt_cat_prod = $cek_cat_product->row();
					$category_product_id = $dt_cat_prod->id;
					$this->db->update($this->prefix.'product_category', $data_category, "id = ".$dt_cat_prod->id);
					
				}else{
					
					$this->db->insert($this->prefix.'product_category', $data_category);
					$category_product_id = $this->db->insert_id();
					
				}
				
			}
			
			$id_items = 0;
			if($this->input->post('form_type_masterItem', true) == 'edit'){
				$id_items = $id;
			}else{
				$id_items = $insert_id;
			}
			
			$get_item = array();
			$this->db->from($this->table);
			$this->db->where("id = '".$id_items."'");
			$this->db->where("is_deleted = 0");
			$get_item_dt = $this->db->get();
			if($get_item_dt->num_rows() > 0){
				$get_item = $get_item_dt->row();
				$item_no = $get_item->item_no;
			}
			
			//UPDATE AS MENU SALES
			//harga, hpp, nama, category
			$data_product = array(
				'product_code' 	=>  $item_code,
				'product_no' 	=>  $item_no,
				'product_name' 	=>  $item_name,
				'product_desc'	=>	$item_desc,
				'product_price'	=>	$sales_price,
				'normal_price'	=>	$sales_price,
				'product_hpp'	=>	$item_price,
				'unit_id'		=>	$unit_id,
				'category_id'	=>	$category_product_id,
				'product_type'	=>	'item',
				'product_group'	=>	'other',
				'use_tax'		=>	$sales_use_tax,
				'use_service'	=>	$sales_use_service,
				'updated'		=>	date('Y-m-d H:i:s'),
				'updatedby'		=>	$session_user,
				'is_active'		=>	$is_active,
				'from_item'		=>	1,
				'supplier_id'	=>	$supplier_id,
				'is_kerjasama'	=>	$is_kerjasama,
				'persentase_bagi_hasil'	=>	$persentase_bagi_hasil,
				'total_bagi_hasil'	=>	$total_bagi_hasil,
				'qty_unit'		=>	$qty_unit
			);
			
			if(!empty($id_items)){
				
				$data_product['id_ref_item'] = $id_items;
				
				$this->db->from($this->prefix.'product');
				$this->db->where("id_ref_item = ".$id_items);
				$cek_product = $this->db->get();
				if($cek_product->num_rows() > 0){
					//update
					$this->db->update($this->prefix.'product',$data_product, "id_ref_item = ".$id_items);
				}else{
					//create
					$data_product['created'] = date('Y-m-d H:i:s');
					$data_product['createdby'] = $session_user;
					$this->db->insert($this->prefix.'product',$data_product);
					$product_id = $this->db->insert_id();
					
					//update item id_ref_product
					$update_ref_item = array(
						'id_ref_product'	=> $product_id
					);
					$this->db->update($this->prefix.'items', $update_ref_item, "id = ".$id_items);
					
				}
				
				//cek product all_id_ref_item
				$all_id_ref_item = array();
				$all_id_ref_item[] = $id_items;
				$this->generate_product_gramasi($all_id_ref_item);
				
			}
			
			
		}else{
			
			//unlink = set not active
			if($this->input->post('form_type_masterItem', true) == 'edit'){
				$id_items = $id;
			
				$get_item = array();
				$this->db->from($this->table);
				$this->db->where("id = '".$id_items."'");
				$this->db->where("is_deleted = 0");
				$get_item_dt = $this->db->get();
				if($get_item_dt->num_rows() > 0){
					$get_item = $get_item_dt->row();
					$item_no = $get_item->item_no;
				}
				
				$data_product = array(
					'product_no' 	=>  $item_no,
					'product_name' 	=>  $item_name,
					'product_desc'	=>	$item_desc,
					'product_price'	=>	$sales_price,
					'normal_price'	=>	$sales_price,
					'product_hpp'	=>	$item_price,
					'unit_id'		=>	$unit_id,
					'product_type'	=>	'item',
					'product_group'	=>	'other',
					'use_tax'		=>	$sales_use_tax,
					'use_service'	=>	$sales_use_service,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	0,
					'from_item'		=>	1,
					'supplier_id'	=>	$supplier_id,
					'is_kerjasama'	=>	$is_kerjasama,
					'persentase_bagi_hasil'	=>	$persentase_bagi_hasil,
					'total_bagi_hasil'	=>	$total_bagi_hasil,
					'qty_unit'		=>	$qty_unit
				);
				
				$this->db->update($this->prefix.'product',$data_product, "id_ref_item = ".$id_items);
			}
			
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function delete()
	{
		$this->table = $this->prefix.'items';
		$this->table_product = $this->prefix.'product';
		$this->item_img_path_big = RESOURCES_PATH.'items/big/';
		$this->item_img_path_thumb = RESOURCES_PATH.'items/thumb/';
		$this->item_img_path_tiny = RESOURCES_PATH.'items/tiny/';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
		
		//Delete
		$this->db->where("id IN (".$sql_Id.")");
		$get_items = $this->db->get($this->table);
		
		//$this->db->where("id IN (".$sql_Id.")");
		$data_update = array(
			"is_deleted" => 1
		);
		$q = $this->db->update($this->table,$data_update,"id IN (".$sql_Id.")");
		
		$data_update = array(
			"is_deleted" => 1
		);
		$q2 = $this->db->update($this->table_product,$data_update,"from_item = 1 AND id_ref_item IN (".$sql_Id.")");
		
		$r = '';
		if($q)  
        {  
			if($get_items->num_rows() > 0){
				
				foreach($get_items->result() as $dtP){
					if(!empty($dtP->item_image)){
						@unlink($this->item_img_path_big.$dtP->item_image);
						@unlink($this->item_img_path_thumb.$dtP->item_image);
						@unlink($this->item_img_path_tiny.$dtP->item_image);
					}
					
				}
				
			}
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Item Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function updateCode(){
		$this->table = $this->prefix.'items';	
		
		$id = $this->input->post('id');
		$item_code = $this->input->post('item_code');
		$item_sku = $this->input->post('item_sku');
		$item_category_code = $this->input->post('item_category_code');
		$item_subcategory_code = $this->input->post('item_subcategory_code');
		$tipe = $this->input->post('tipe');
		
		$r = array('success' => false, 'info' => 'Update Code Failed!'); 
		if(empty($id) OR empty($item_code) OR empty($tipe)){
			die(json_encode($r));
		}
		
		
		$this->db->from($this->table);
		$this->db->where("item_code = '".$item_code."' AND id != ".$id);
		$this->db->where("is_deleted = 0");
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			
			//available
			$r = array('success' => false, 'info' => 'Kode sudah digunakan!'); 
			
			//suggestion
			if(!empty($item_category_code) OR !empty($item_subcategory_code)){
				$get_item_code = $this->generate_item_code($tipe);
				$r = array('success' => false, 'info' => 'Kode sudah digunakan!<br/>Coba Kode Berikut: '.$get_item_code['item_code']); 
			}
	
		}else{
			
			$opt_value = array(
				'item_code_format',
				'item_code_separator',
				'item_no_length',
				'item_sku_from_code'
			);
			
			$get_opt = get_option_value($opt_value);
			
			$item_code_format = '{Cat}{ItemNo}';
			if(!empty($get_opt['item_code_format'])){
				$item_code_format = $get_opt['item_code_format'];
			}
			
			$item_no_length = 5;
			if(!empty($get_opt['item_no_length'])){
				$item_no_length = $get_opt['item_no_length'];
			}
			
			$item_sku_from_code = 0;
			if(!empty($get_opt['item_sku_from_code'])){
				$item_sku_from_code = $get_opt['item_sku_from_code'];
			}
			
			if($item_sku_from_code == 1){
				$item_sku = '';
			}
			
			$repl_attr = array(
				"{SKU}"		=> $item_sku,
				"{Cat}"		=> $item_category_code,
				"{SubCat}"	=> $item_subcategory_code,
				//"{SubCat1}"	=> $item_subcategory_code,
			);
			
			$item_code_format = strtr($item_code_format, $repl_attr);
			$get_exp = explode("{ItemNo}", $item_code_format);
			$first_format = '';
			$item_no = 0;
			if(!empty($get_exp[0])){
				$first_format = $get_exp[0];
				$first_format_length_code = strlen($first_format);
				$get_item_no = substr($item_code, $first_format_length_code, $item_no_length);
				$item_no = (int) $get_item_no;
			}
			//update 
			$update_code = array('item_code' => $item_code,'item_no' => $item_no);
			
			if($item_sku_from_code == 1){
				$item_sku = $item_code;
				$update_code['item_sku'] = $item_sku;
			}
			
			$this->db->update($this->table, $update_code, "id = ".$id);
			$r = array('success' => true, 'item_sku_from_code' => $item_sku_from_code, 'item_code' => $item_code, 'item_sku' => $item_sku, 'item_no' => $item_no);
			
		}
		
		die(json_encode($r));
	}
	
	public function generate_item_code($tipe = ''){
		
		$this->table = $this->prefix.'items';		

		$getDate = date("ym");
		
		$item_category_code = $this->input->post('item_category_code');
		$item_subcategory_code = $this->input->post('item_subcategory_code');
		
		$item_name = $this->input->post('item_name');
		$item_sku = $this->input->post('item_sku');
		
		$opt_value = array(
			'item_code_format',
			'item_code_separator',
			'item_no_length',
			'item_sku_from_code'
		);
		
		$get_opt = get_option_value($opt_value);
		
		$item_code_format = '{Cat}{ItemNo}';
		if(!empty($get_opt['item_code_format'])){
			$item_code_format = $get_opt['item_code_format'];
		}
		
		$item_no_length = 5;
		if(!empty($get_opt['item_no_length'])){
			$item_no_length = $get_opt['item_no_length'];
		}
		
		$item_sku_from_code = 0;
		if(!empty($get_opt['item_sku_from_code'])){
			$item_sku_from_code = $get_opt['item_sku_from_code'];
		}
		
		if($item_sku_from_code == 1){
			$item_sku = '';
		}
		
		$repl_attr = array(
			"{SKU}"		=> $item_sku,
			"{Cat}"		=> $item_category_code,
			"{SubCat}"	=> $item_subcategory_code,
			//"{SubCat1}"	=> $item_subcategory_code,
		);
		
		$item_code_format = strtr($item_code_format, $repl_attr);
		$get_exp = explode("{ItemNo}", $item_code_format);
		$first_format = '';
		if(!empty($get_exp[0])){
			$first_format = $get_exp[0];
			
			$this->db->from($this->table);
			$this->db->where("item_code LIKE '".$first_format."%' AND item_name = '".$item_name."'");
			$this->db->where("is_deleted = 0");
			$this->db->order_by('item_no', 'DESC');
			$this->db->order_by('item_code', 'DESC');
			$get_last = $this->db->get();
			if($get_last->num_rows() > 0){
				$data_item_code = $get_last->row();
				$first_format_length_code = strlen($first_format);
				$item_code = substr($data_item_code->item_code, $first_format_length_code, $item_no_length);
				$item_no = (int) $item_code;
				
				if(!empty($data_item_code->item_no)){
					$item_no = $data_item_code->item_no;
				}
				$item_no++;
			}else{
				
				$this->db->from($this->table);
				$this->db->where("item_code LIKE '".$first_format."%'");
				$this->db->where("is_deleted = 0");
				$this->db->order_by('item_no', 'DESC');
				$this->db->order_by('item_code', 'DESC');
				$get_last = $this->db->get();
				if($get_last->num_rows() > 0){
					$data_item_code = $get_last->row();
					$first_format_length_code = strlen($first_format);
					$item_code = substr($data_item_code->item_code, $first_format_length_code, $item_no_length);
					$item_no = (int) $item_code;
				
					if(!empty($data_item_code->item_no)){
						$item_no = $data_item_code->item_no;
					}		
					
				}else{
					$item_no = 0;
				}
				
				$item_no++;
			
			}
			
			$length_no = strlen($item_no);
			if($length_no <= $item_no_length){
				$gapTxt = $item_no_length - $length_no;
				$item_code = str_repeat("0", $gapTxt).$item_no;
			}
			
			$repl_attr = array(
				"{ItemNo}"		=> $item_code
			);
			
			$item_code_format = strtr($item_code_format, $repl_attr);
		
		}else
		{
			$this->db->from($this->table);
			$this->db->where("is_deleted = 0");
			$this->db->order_by('item_no', 'DESC');
			$this->db->order_by('item_code', 'DESC');
			$get_last = $this->db->get();
			if($get_last->num_rows() > 0){
				$data_item_code = $get_last->row();
				//$item_code = substr($data_item_code->item_code, 0, $item_no_length);
				$item_code = substr($data_item_code->item_code, $item_no_length*-1);
				$item_no = (int) $item_code;	
					
				if(!empty($data_item_code->item_no)){
					$item_no = $data_item_code->item_no;
				}				
			}else{
				$item_no = 0;
			}
			
			$item_no++;
			$length_no = strlen($item_no);
			if($length_no <= $item_no_length){
				$gapTxt = $item_no_length - $length_no;
				$item_code = str_repeat("0", $gapTxt).$item_no;
			}
		}
		
		$repl_itemno = array(
			"{ItemNo}"		=> $item_code
		);
		
		$item_code = strtr($item_code_format, $repl_itemno);	
		
		return array('item_no' => $item_no, 'item_code' => $item_code);				
	}
	
	public function importDataItem()
	{
		
		$this->table_items = $this->prefix.'items';
		$this->table_item_category = $this->prefix.'item_category';
		$this->table_item_subcategory = $this->prefix.'item_subcategory';
		
		$this->table_unit = $this->prefix.'unit';
		$this->table_customer = $this->prefix.'customer';
		$this->table_product = $this->prefix.'product';
		$this->table_product_category = $this->prefix.'product_category';
		$this->table_product_gramasi = $this->prefix.'product_gramasi';
		$this->table_stock_opname = $this->prefix.'stock_opname';
		$this->table_stock_opname_detail = $this->prefix.'stock_opname_detail';
		
		$time_create_update = date('Y-m-d H:i:s');
		$session_user = $this->session->userdata('user_username');
		
		$this->file_import_item = BASE_PATH.'uploads/';
		
		$r = ''; 
		$is_upload_file = false;		
		if(!empty($_FILES['upload_file']['name'])){
						
			$config['upload_path'] = $this->file_import_item;
			$config['allowed_types'] = 'xls';
			$config['max_size']	= '1024';

			$this->load->library('upload', $config);

			if(!$this->upload->do_upload("upload_file"))
			{
				$data = $this->upload->display_errors();
				$r = array('success' => false, 'info' => $data );
				die(json_encode($r));
			}
			else
			{
				$is_upload_file = true;
				$data_upload_temp = $this->upload->data();
				
				
				// Load the spreadsheet reader library
				$this->load->library('spreadsheet_Excel_Reader');
				$xls = new Spreadsheet_Excel_Reader();
				$xls->setOutputEncoding('CP1251'); 
				$file =  $this->file_import_item.$data_upload_temp['file_name']."" ;
				$xls->read($file);
				//echo '<pre>';
				//print_r($xls->sheets);die();
				
				error_reporting(E_ALL ^ E_NOTICE);
				
				$nr_sheets = count($xls->sheets);    
				
				$this->lib_trans->begin();
				
				$all_unit = array();
				$all_unit_new = array();
				$all_unit_id = array();
				$unit_name = array();
				
				$item_no_length = 3;
				
				$opt_value = array(
					'item_code_format',
					'item_code_separator',
					'item_no_length'
				);
				
				$get_opt = get_option_value($opt_value);
				
				$item_code_format = '{Cat}{SubCat}{ItemNo}';
				$item_code_separator = '.';
				
				$no_unit = 0;
				$no_cat = 0;
				$no_prodcat = 0;
				$no_subcat = 0;
				
				$all_product_category = array();
				$all_product_category_new = array();
				$all_product_category_id = array();
				
				$all_category = array();
				$all_category_new = array();
				$all_category_code = array();
				$all_category_id = array();
				$all_category_name = array();
				
				$all_subcategory = array();
				$all_subcategory_new = array();
				$all_subcategory_id = array();
				
				$all_id_ref_item = array();
				
				$all_code = array();
				$all_item = array();
				$all_new_item = array();
				$all_product = array();
				$all_new_product = array();
				$all_edit_item = array();
				$all_edit_product = array();
				$all_item_name = array();
				$last_category_id = 0;
				$last_unit_id = 0;
				
				//ITEM
				$item_sama = array();
				$no = 0;
				$no_sheet = 0;
				
				//UNIT
				$this->db->from($this->table_unit);
				$this->db->order_by("id","ASC");
				$get_unit = $this->db->get();
				if($get_unit->num_rows() > 0){
					foreach($get_unit->result_array() as $dt){
						//$all_unit[] = $dt;
						$all_unit[$dt['id']] = strtolower($dt['unit_code']);
						$no_unit = $dt['id'];
					}
				}
				
				//PRODCAT
				$this->db->from($this->table_product_category);
				$this->db->order_by("id","ASC");
				$get_prodcat = $this->db->get();
				if($get_prodcat->num_rows() > 0){
					foreach($get_prodcat->result_array() as $dt){
						//$all_category[] = $dt;
						$all_product_category[$dt['id']] = strtolower($dt['product_category_name']);
						$no_prodcat = $dt['id'];
					}
				}
				
				//CAT
				$this->db->from($this->table_item_category);
				$this->db->order_by("id","ASC");
				$get_cat = $this->db->get();
				if($get_cat->num_rows() > 0){
					foreach($get_cat->result_array() as $dt){
						//$all_category[] = $dt;
						$all_category[$dt['id']] = strtolower($dt['item_category_name']);
						$all_category_code[$dt['id']] = $dt['item_category_code'];
						$no_cat = $dt['id'];
					}
				}
				
				//SUBCAT
				$this->db->from($this->table_item_subcategory);
				$this->db->order_by("id","ASC");
				$get_subcat = $this->db->get();
				if($get_subcat->num_rows() > 0){
					foreach($get_subcat->result_array() as $dt){
						//$all_subcategory[] = $dt;
						$all_subcategory[$dt['id']] = strtolower($dt['item_subcategory_name']);
						$all_subcategory_code[$dt['id']] = $dt['item_subcategory_code'];
						$no_subcat = $dt['id'];
					}
				}
				
				//ITEM
				$last_item_id = 0;
				$all_code = array();
				$all_item_id = array();
				$all_item_name = array();
				$this->db->from($this->table_items);
				$this->db->order_by("id","ASC");
				$get_items = $this->db->get();
				if($get_items->num_rows() > 0){
					foreach($get_items->result_array() as $dt){
						
						$item_code = $dt['item_code'];
						$item_code_exp = explode($item_code_separator, $item_code);
						if(!empty($item_code_exp[2])){
							$item_code = strtoupper($item_code_exp[0]).$item_code_separator.strtoupper($item_code_exp[1]);
							$all_code[$item_code] = intval($item_code_exp[2]);
						}
						
						$all_item_id[$item_code] = $dt['id'];
						$all_item_name[$item_code.strtolower($dt['item_name'])] = $dt['id'];
						
						$last_item_id = $dt['id'];
					}
				}
				
				$xls_sheet = 0;
				
				for ($row_num = 2; $row_num <= $xls->sheets[$xls_sheet]['numRows']; $row_num++) {
					$no++;
					
					$item_id = trim($xls->sheets[$xls_sheet]['cells'][$row_num][1]);
					$item_name = trim($xls->sheets[$xls_sheet]['cells'][$row_num][2]);
					$item_sku = trim($xls->sheets[$xls_sheet]['cells'][$row_num][3]);
					$item_code = trim($xls->sheets[$xls_sheet]['cells'][$row_num][3]);
					
					$item_unit = trim($xls->sheets[$xls_sheet]['cells'][$row_num][4]);
					$item_unit = strtolower($item_unit);
					if(!in_array($item_unit, $all_unit) AND !empty($item_unit)){
						$no_unit++;
						$all_unit[$no_unit] = $item_unit;
						
						$all_unit_new[] = array(
							'unit_code'=> $item_unit,
							'unit_name'=> $item_unit,
							'created'		=>	$time_create_update,
							'createdby'		=>	$session_user,
							'updated'		=>	$time_create_update,
							'updatedby'		=>	$session_user
						);
						
					}
					
					$item_cat_real = trim($xls->sheets[$xls_sheet]['cells'][$row_num][5]);
					$item_cat = strtolower($item_cat_real);
					if(!in_array($item_cat, $all_category) AND !empty($item_cat)){
						$no_cat++;
						$all_category[$no_cat] = $item_cat;
						$all_category_code[$no_cat] = substr($item_cat,0,1).rand(10,99);
						
						$no_prodcat++;
						$all_product_category[$no_prodcat] = $item_cat;
						
						$all_category_new[] = array(
							'item_category_code'=> $all_category_code[$no_cat],
							'item_category_name'=> $item_cat_real,
							'created'		=>	$time_create_update,
							'createdby'		=>	$session_user,
							'updated'		=>	$time_create_update,
							'updatedby'		=>	$session_user
						);
						
						$all_product_category_new[] = array(
							'product_category_name'=> $item_cat_real,
							'created'		=>	$time_create_update,
							'createdby'		=>	$session_user,
							'updated'		=>	$time_create_update,
							'updatedby'		=>	$session_user
						);
						
					}
					
					$item_subcat_real = trim($xls->sheets[$xls_sheet]['cells'][$row_num][6]);
					$item_subcat = strtolower($item_subcat_real);
					if(!in_array($item_subcat, $all_subcategory) AND !empty($item_subcat)){
						$no_subcat++;
						$all_subcategory[$no_subcat] = $item_subcat;
						$all_subcategory_code[$no_subcat] = substr($item_subcat,0,3).rand(10,99);
						
						$all_subcategory_new[] = array(
							'item_subcategory_code'=> $all_subcategory_code[$no_subcat],
							'item_subcategory_name'=> $item_subcat_real,
							'created'		=>	$time_create_update,
							'createdby'		=>	$session_user,
							'updated'		=>	$time_create_update,
							'updatedby'		=>	$session_user
						);
					}
					
					$item_desc = trim($xls->sheets[$xls_sheet]['cells'][$row_num][7]);
					$normal_price = trim($xls->sheets[$xls_sheet]['cells'][$row_num][8]);
					$sales_price = trim($xls->sheets[$xls_sheet]['cells'][$row_num][9]);
					$hpp_price = trim($xls->sheets[$xls_sheet]['cells'][$row_num][10]);
					$item_type = trim($xls->sheets[$xls_sheet]['cells'][$row_num][11]);
					$product_type = trim($xls->sheets[$xls_sheet]['cells'][$row_num][12]);
					$use_tax = trim($xls->sheets[$xls_sheet]['cells'][$row_num][13]);
					$use_service = trim($xls->sheets[$xls_sheet]['cells'][$row_num][14]);
					$use_for_sales = trim($xls->sheets[$xls_sheet]['cells'][$row_num][15]);
					$min_stock = trim($xls->sheets[$xls_sheet]['cells'][$row_num][16]);
					
					$item_type = strtolower($item_type);
					$product_type = strtolower($product_type);
					//$product_group = strtolower($product_group);
					$product_group = 'other';
					
					$item_name = trim($item_name);
					
					if(!empty($item_name)){
						
						$id_cat = array_search($item_cat, $all_category);
						$id_prodcat = array_search($item_cat, $all_product_category);
						$id_subcat = array_search($item_subcat, $all_subcategory);
						$id_unit = array_search($item_unit, $all_unit);
						
						$sesuai_xls = 0;
						if(empty($item_code)){
							$item_code = substr(strtoupper($item_cat),0,3).$item_code_separator.substr(strtoupper($item_subcat),0,3);
						}else{
							$item_code_full = $item_code;
							$item_code_exp = explode($item_code_separator, $item_code);
							if(!empty($item_code_exp[2])){
								$item_code = strtoupper($item_code_exp[0]).$item_code_separator.strtoupper($item_code_exp[1]);
							}else{
								//sesuai excel
								$sesuai_xls = 1;
							}
						}
						
						if(empty($all_code[$item_code])){
							$all_code[$item_code] = 0;
						}
						
						if($sesuai_xls == 1){
							
							$all_code[$item_code] = preg_replace('/[^0-9]/', '', $item_code);
							$item_no = $all_code[$item_code];
							
						}else{
							$all_code[$item_code]++;
							$item_no = $all_code[$item_code];
						
							$item_no_code = $item_no;
							$length_no = strlen($item_no);
							if($length_no <= $item_no_length){
								$gapTxt = $item_no_length - $length_no;
								$item_no_code = str_repeat("0", $gapTxt).$item_no;
							}
							
							$item_code_full = $item_code."-".$item_no_code;
							
						}
						
						
						$gencode = $item_code_full.'-'.rand(10000,99999).'-'.rand(100,999);

						if(empty($item_id)){
							//if(!empty($all_item_id[$item_code_full])){
							//	$item_id = $all_item_id[$item_code_full];
							//}
							
							if(!empty($all_item_name[$item_code_full.strtolower($item_name)])){
								$item_id = $all_item_name[$item_code_full.strtolower($item_name)];
							}
							//echo $item_code_full.' - '.strtolower($item_name).' = '.$item_id.'<br/>';
							
						}
						
						$data_item = array(
							'item_code'		=> $item_code_full,
							'item_sku'		=> $item_code_full,
							'item_no'		=> $item_no,
							'item_name'		=> $item_name,
							'item_type'		=> $item_type,
							'item_hpp'		=> $hpp_price,
							'item_price'	=> $hpp_price,
							'sales_price'	=> $sales_price,
							'category_id'	=> $id_cat,
							'subcategory_id'=> $id_subcat,
							'unit_id'		=> $id_unit,
							'use_for_sales'	=> $use_for_sales,
							'min_stock'		=> $min_stock,
							'sales_use_tax'	=> $use_tax,
							'sales_use_service'	=> $use_service,
							'updated'		=>	$time_create_update,
							'updatedby'		=>	$session_user,
							'item_desc'		=>	$gencode
						);
						
						$data_product = array(
							'product_name' 	=> $item_name,
							'product_price' => $sales_price,
							'normal_price' => $sales_price,
							'product_hpp' 	=> $hpp_price,
							'product_type' 	=> $product_type,
							'product_group' => $product_group,
							'use_tax' 		=> $use_tax,
							'use_service' 	=> $use_service,
							'from_item' 	=> 1,
							'id_ref_item' 	=> 0,
							'category_id'	=> $id_prodcat,
							'updated'		=> $time_create_update,
							'updatedby'		=> $session_user,
							'product_desc'	=> $gencode,
							'is_active'		=> 1
						);
						
						if(empty($item_id)){
							$data_item['created'] = $time_create_update;
							$data_item['createdby'] = $session_user;
							$all_new_item[] = $data_item;
							
							if($use_for_sales == 1){
								$all_new_product[$gencode] = $data_product;
							}
							
						}else{
							$data_item['id'] = $item_id;
							$all_edit_item[] = $data_item;
							
							$data_product['id_ref_item'] = $item_id;
							if($use_for_sales == 0){
								$data_product['is_active'] = 0;
							}
							
							$all_edit_product[] = $data_product;
							
							if(!in_array($item_id, $all_id_ref_item)){
								$all_id_ref_item[] = $item_id;
							}
							
						}
						
					}
					
				}
				
				
				if(!empty($all_unit_new)){
						
					$this->db->insert_batch($this->table_unit, $all_unit_new);
				}
				
				if(!empty($all_category_new)){
						
					$this->db->insert_batch($this->table_item_category, $all_category_new);
				}
				
				if(!empty($all_subcategory_new)){
						
					$this->db->insert_batch($this->table_item_subcategory, $all_subcategory_new);
				}
				
				$q = false;
				
				if(!empty($all_new_item)){
					
					$q = $this->db->insert_batch($this->table_items, $all_new_item);
				}
				
				if(!empty($all_edit_item)){
					
					$q = $this->db->update_batch($this->table_items, $all_edit_item, "id");
				}
				
				if(!empty($all_product_category_new)){
					$this->db->insert_batch($this->table_product_category, $all_product_category_new);
				}
				
				//GET Item
				$all_last_item = array();
				$all_last_item_id = array();
				$this->db->from($this->table_items);
				$this->db->where("id > ".$last_item_id);
				$get_items = $this->db->get();
				if($get_items->num_rows() > 0){
					foreach($get_items->result_array() as $dt){
						$all_last_item[] = $dt;
						$all_last_item_id[$dt['item_code']] = $dt['id'];
					}
				}
				
				//new product
				$all_product = array();
				if(!empty($all_last_item)){
					foreach($all_last_item as $dt){
						
						if(!empty($all_new_product[$dt['item_desc']])){
							$all_new_product[$dt['item_desc']]['id_ref_item'] = $dt['id'];
							$all_product[] = $all_new_product[$dt['item_desc']];
							
							if(!in_array($dt['id'], $all_id_ref_item)){
								$all_id_ref_item[] = $dt['id'];
							}
						}
						
					}
				}
				
				if(!empty($all_product)){
					$this->db->insert_batch($this->table_product, $all_product);
				}
				
				if(!empty($all_edit_product)){
					$this->db->update_batch($this->table_product, $all_edit_product, "id_ref_item");
				}
				
				//cek product all_id_ref_item
				if(!empty($all_id_ref_item)){
					$this->generate_product_gramasi($all_id_ref_item);
					
				}
				
				
				$this->lib_trans->commit();	
				
				if($q)
				{ 
					$r = array('success' => true); 				
				}  
				else
				{  				
					$r = array('success' => false);
				}
				
				
			}
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));	
 
	}
	
	public function generate_product_gramasi($all_id_ref_item = array()){
		
		$this->table_items = $this->prefix.'items';
		$this->table_product = $this->prefix.'product';
		$this->table_product_gramasi = $this->prefix.'product_gramasi';
		
		$time_create_update = date('Y-m-d H:i:s');
		$session_user = $this->session->userdata('user_username');
		
		if(!empty($all_id_ref_item)){
			$all_id_ref_item_sql = implode(",", $all_id_ref_item);
					
			$all_product_id = array();
			$all_product_ref_id = array();
			$all_item_price = array();
			
			$this->db->select("a.id, a.id_ref_item, b.item_price");
			$this->db->from($this->table_product." as a");
			$this->db->join($this->table_items." as b","b.id = a.id_ref_item","LEFT");
			$this->db->where("a.id_ref_item IN (".$all_id_ref_item_sql.")");
			$this->db->where("a.is_deleted = 0 AND a.has_varian = 0 AND a.product_type = 'item'");
			$get_product = $this->db->get();
			if($get_product->num_rows() > 0){
				foreach($get_product->result() as $dt){
					$all_product_id[] = $dt->id;
					$all_product_ref_id[$dt->id] = $dt->id_ref_item;
					$all_item_price[$dt->id_ref_item] = $dt->item_price;
				}
			}
			
			//cek di gramasi
			$all_gramasi_item_id = array();
			$all_gramasi_item_id_edit = array();
			
			if(!empty($all_product_id)){
				$all_product_id_sql = implode(",", $all_product_id);
				
				$this->db->from($this->table_product_gramasi);
				$this->db->where("product_id IN (".$all_product_id_sql.")");
				$this->db->where("is_deleted = 0");
				$get_product = $this->db->get();
				if($get_product->num_rows() > 0){
					foreach($get_product->result() as $dt){
						
						if(empty($all_gramasi_item_id[$dt->product_id])){
							$all_gramasi_item_id[$dt->product_id] = array();
						}
						
						$all_gramasi_item_id[$dt->product_id][] = $dt->item_id;
						$all_gramasi_item_id_edit[$dt->product_id.'_'.$dt->item_id] = $dt->id;
						
					}
				}
			}
			
			$new_gramasi_data = array();
			$update_gramasi_data = array();
			if(!empty($all_product_ref_id)){
				foreach($all_product_ref_id as $pid => $itemid){
					
					$item_price = 0;
					if(!empty($all_item_price[$itemid])){
						$item_price = $all_item_price[$itemid];
					}
					
					$do_create_gramasi = false;
					if(!empty($all_gramasi_item_id[$pid])){
						
						if(!in_array($itemid, $all_gramasi_item_id[$pid])){
							//tdk ada gramasi = item tsb
							$do_create_gramasi = true;
						}else{
							
							$grmid = 0;
							if(!empty($all_gramasi_item_id_edit[$pid.'_'.$itemid])){
								
								$update_gramasi_data[] = array(
									'id'			=> $all_gramasi_item_id_edit[$pid.'_'.$itemid],
									'product_id'	=> $pid,
									'item_id'		=> $itemid,
									'item_price'	=> $item_price,
									'updated'		=> $time_create_update,
									'updatedby'		=> $session_user,
									'is_active'		=> 1,
									'is_deleted'	=> 0
								);
								
							}
							
						}
						
					}else{
						$do_create_gramasi = true;
					}
					
					if($do_create_gramasi == true){
						$new_gramasi_data[] = array(
							'product_id'	=> $pid,
							'item_id'		=> $itemid,
							'item_price'	=> $item_price,
							'item_qty'		=> 1,
							'created'		=> $time_create_update,
							'createdby'		=> $session_user,
							'updated'		=> $time_create_update,
							'updatedby'		=> $session_user,
							'is_active'		=> 1,
							'is_deleted'	=> 0,
							'product_varian_id' => 0,
							'varian_id' => 0
						);
					}
					
				}
			}
			
			//save product_gramasi 
			if(!empty($new_gramasi_data)){
				$this->db->insert_batch($this->table_product_gramasi, $new_gramasi_data);
			}
			if(!empty($update_gramasi_data)){
				$this->db->update_batch($this->table_product_gramasi, $update_gramasi_data, "id");
			}
		}
	}
	
	public function print_masterItem(){
		
		$data_post = array();
		$this->load->view('../../master_pos/views/print_masterItem', $data_post);
		
	}
}