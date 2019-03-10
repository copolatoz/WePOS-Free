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
								f.item_subcategory2_name, f.item_subcategory2_code, g.item_subcategory3_name, g.item_subcategory3_code, h.item_subcategory1_name, h.item_subcategory1_code',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'unit as b','b.id = a.unit_id','LEFT'),
										array($this->prefix.'supplier as c','c.id = a.supplier_id','LEFT'),
										array($this->prefix.'item_category as d','d.id = a.category_id','LEFT'),
										array($this->prefix.'item_subcategory2 as f','f.id = a.subcategory2_id','LEFT'),
										array($this->prefix.'item_subcategory3 as g','g.id = a.subcategory3_id','LEFT'),
										array($this->prefix.'item_subcategory1 as h','h.id = a.subcategory1_id','LEFT')
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
			$params['where'][] = "(a.item_code LIKE '".$searching."%' OR a.item_name LIKE '%".$searching."%' OR d.item_category_name LIKE '%".$searching."%')";
		}
		if(!empty($supplier_id)){
			$params['where'][] = "a.supplier_id = ".$supplier_id."";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				if(empty($from_distribution)){
					if(empty($s['item_image'])){
						$s['item_image'] = 'no-image.jpg';
					}
					$s['item_image_show'] = '<img src="'.$this->item_img_url.$s['item_image'].'" style="max-width:80px; max-height:60px;"/>';
					$s['item_image_src'] = $this->item_img_url.$s['item_image'];
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
			'fields'		=> 'a.id, a.item_code, a.item_name, a.item_price, a.sales_price, a.item_hpp, a.last_in, a.unit_id, b.unit_name, a.use_stok_kode_unik',
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
		if($from_module == 'distribution' OR $from_module == 'salesorder'){
			
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
			$params['where'][] = "(item_code LIKE '%".$searching."%' OR item_name LIKE '%".$searching."%' OR c.supplier_name LIKE '%".$searching."%')";
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
		$item_desc = $this->input->post('item_desc');
		$sales_price = $this->input->post('sales_price');
		$item_price = $this->input->post('item_price');
		$unit_id = $this->input->post('unit_id');
		$category_id = $this->input->post('category_id');
		$subcategory2_id = $this->input->post('subcategory2_id');
		$subcategory3_id = $this->input->post('subcategory3_id');
		$subcategory1_id = $this->input->post('subcategory1_id');
		$item_category_code = $this->input->post('item_category_code');
		$item_subcategory2_code = $this->input->post('item_subcategory2_code');
		$item_subcategory3_code = $this->input->post('item_subcategory3_code');
		$item_subcategory1_code = $this->input->post('item_subcategory1_code');
		$supplier_id = $this->input->post('supplier_id');
		$item_image = $this->input->post('item_image');
		$item_type = $this->input->post('item_type');
		$item_hpp = $this->input->post('item_hpp');
		$id_ref_product = $this->input->post('id_ref_product');
		$persentase_bagi_hasil = $this->input->post('persentase_bagi_hasil');
		$item_manufacturer = '';
		
		
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
			$r = array('success' => false);
			die(json_encode($r));
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
		
		$total_bagi_hasil = 0;
		if($is_kerjasama == 1){
			
			if(empty($persentase_bagi_hasil) OR empty($supplier_id)){
				$r = array('success' => false, 'info' => 'Input Persentase &amp; Supplier');
				die(json_encode($r));
			}
			$total_bagi_hasil = numberFormat($sales_price*($persentase_bagi_hasil/100));
			
		}
			
		$r = '';
		if($this->input->post('form_type_masterItem', true) == 'add')
		{
			
			//cek item code
			$get_item_code = $this->generate_item_code($form_module_masterItem);
			
			$this->db->from($this->table);
			$this->db->where("item_code = '".$item_code."'");
			$this->db->where("is_deleted = 0");
			$get_last = $this->db->get();
			if($get_last->num_rows() > 0){
				
				//available
				$r = array('success' => false, 'info' => 'Code Available or been used!'); 
				
				//suggestion
				if(!empty($item_category_code) OR !empty($item_subcategory2_code) OR !empty($item_subcategory3_code) OR !empty($item_subcategory1_code)){
					$get_item_code = $this->generate_item_code($tipe);
					$r = array('success' => false, 'info' => 'Code Available or been used!<br/>Try use this code: '.$get_item_code['item_code']); 
				}
				
				die(json_encode($r));
		
			}
			
			//$r = array('success' => false, 'info' => $get_item_code, 'item_no' => $get_item_code['item_no']);
			//die(json_encode($r));
			
			$var = array(
				'fields'	=>	array(
				    'item_code' => 	$get_item_code['item_code'],
				    'item_no' => 	$get_item_code['item_no'],
				    'item_name' => 	$item_name,
					'item_desc'	=>	$item_desc,
					'unit_id'	=>	$unit_id,
					'category_id'	=>	$category_id,
					'subcategory2_id'	=>	$subcategory2_id,
					'subcategory3_id'	=>	$subcategory3_id,
					'subcategory1_id'	=>	$subcategory1_id,
					'supplier_id'	=>	$supplier_id,
					'sales_price'=>	$sales_price,
					'item_price'=>	$item_price,
					'item_hpp'	=>	$item_hpp,
					'item_type'	=>	$item_type,
					'item_manufacturer'	=>	$item_manufacturer,
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
					'use_stok_kode_unik'	=>	$use_stok_kode_unik
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
			$var = array('fields'	=>	array(
				    'item_name' => 	$item_name,
					'item_desc'	=>	$item_desc,
					'sales_price'=>	$sales_price,
					'item_price'=>	$item_price,
					'unit_id'	=>	$unit_id,
					'category_id'	=>	$category_id,
					'subcategory2_id'	=>	$subcategory2_id,
					'subcategory3_id'	=>	$subcategory3_id,
					'subcategory1_id'	=>	$subcategory1_id,
					'supplier_id'	=>	$supplier_id,
					'item_hpp'	=>	$item_hpp,
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
					'use_stok_kode_unik'	=>	$use_stok_kode_unik
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
			
			
			//UPDATE AS MENU SALES
			//harga, hpp, nama, category
			$data_product = array(
				'product_name' 	=>  $item_name,
				'product_desc'	=>	$item_desc,
				'product_price'	=>	$sales_price,
				'normal_price'	=>	$sales_price,
				'product_hpp'	=>	$item_price,
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
				'total_bagi_hasil'	=>	$total_bagi_hasil
			);
			
			$id_items = 0;
			if($this->input->post('form_type_masterItem', true) == 'edit'){
				$id_items = $id;
			}else{
				$id_items = $insert_id;
			}
			
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
		$item_category_code = $this->input->post('item_category_code');
		$item_subcategory2_code = $this->input->post('item_subcategory2_code');
		$item_subcategory3_code = $this->input->post('item_subcategory3_code');
		$item_subcategory1_code = $this->input->post('item_subcategory1_code');
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
			$r = array('success' => false, 'info' => 'Code Available or been used!'); 
			
			//suggestion
			if(!empty($item_category_code) OR !empty($item_subcategory2_code) OR !empty($item_subcategory3_code) OR !empty($item_subcategory1_code)){
				$get_item_code = $this->generate_item_code($tipe);
				$r = array('success' => false, 'info' => 'Code Available or been used!<br/>Try use this code: '.$get_item_code['item_code']); 
			}
	
		}else{
			
			$opt_value = array(
				'item_code_format',
				'item_code_separator',
				'item_no_length'
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
			
			$repl_attr = array(
				"{Cat}"		=> $item_category_code,
				"{SubCat1}"	=> $item_subcategory1_code,
				"{SubCat2}"	=> $item_subcategory2_code,
				"{SubCat3}"	=> $item_subcategory3_code
			);
			
			$item_code_format = strtr($item_code_format, $repl_attr);
			$get_exp = explode("{ItemNo}", $item_code_format);
			$first_format = '';
			if(!empty($get_exp[0])){
				$first_format = $get_exp[0];
				$first_format_length_code = strlen($first_format);
				$get_item_no = substr($item_code, $first_format_length_code, $item_no_length);
				$item_no = (int) $get_item_no;
			}
			//update 
			$update_code = array('item_code' => $item_code,'item_no' => $item_no);
			$this->db->update($this->table, $update_code, "id = ".$id);
			$r = array('success' => true, 'item_code' => $item_code, 'item_no' => $item_no);
			
		}
		
		die(json_encode($r));
	}
	
	public function generate_item_code($tipe = ''){
		
		$this->table = $this->prefix.'items';		

		$getDate = date("ym");
		
		$item_category_code = $this->input->post('item_category_code');
		$item_subcategory2_code = $this->input->post('item_subcategory2_code');
		$item_subcategory3_code = $this->input->post('item_subcategory3_code');
		$item_subcategory1_code = $this->input->post('item_subcategory1_code');
		
		$item_name = $this->input->post('item_name');
		
		$opt_value = array(
			'item_code_format',
			'item_code_separator',
			'item_no_length'
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
		
		$repl_attr = array(
			"{Cat}"		=> $item_category_code,
			"{SubCat1}"	=> $item_subcategory1_code,
			"{SubCat2}"	=> $item_subcategory2_code,
			"{SubCat3}"	=> $item_subcategory3_code
		);
		
		$item_code_format = strtr($item_code_format, $repl_attr);
		$get_exp = explode("{ItemNo}", $item_code_format);
		$first_format = '';
		if(!empty($get_exp[0])){
			$first_format = $get_exp[0];
			
			//if($tipe == 'clothing'){
				
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
			
			/*}else
			{
				//ASUMSI {Dept}{ItemNo}
				$this->db->from($this->table);
				$this->db->where("item_code LIKE '".$first_format."%'");
				$this->db->where("is_deleted = 0");
				$this->db->order_by('item_no', 'DESC');
				$this->db->order_by('item_code', 'DESC');
				$get_last = $this->db->get();
				if($get_last->num_rows() > 0){
					$data_item_code = $get_last->row();
					$first_format_length_code = strlen($first_format);
					//$item_code = substr($data_item_code->item_code, $first_format_length_code, $item_no_length);
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
			*/
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
	
}