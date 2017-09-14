<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class SalesOrder extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_salesorder', 'm');
		$this->load->model('model_salesorderdetail', 'm2');
		$this->load->model('inventory/model_stock', 'stock');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'salesorder';
		$this->table2 = $this->prefix.'salesorder_detail';
		
		//is_active_text
		$sortAlias = array(
			//'is_active_text' => 'is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, d.storehouse_name as so_from_name, e.sales_name, e.sales_company',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'storehouse as d','d.id = a.so_from','LEFT'),
										array($this->prefix.'sales as e','e.id = a.sales_id','LEFT')
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
		//$is_active = $this->input->post('is_active');
		$so_status = $this->input->post('so_status');
		$not_cancel = $this->input->post('not_cancel');
		$skip_date = $this->input->post('skip_date');
		
		//FILTER
		$date_from = $this->input->post('date_from');
		$date_till = $this->input->post('date_till');
		$keywords = $this->input->post('keywords');
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if($skip_date == true){
		
		}else{
		
			if(empty($date_from) AND empty($date_till)){
				$date_from = date('Y-m-d');
				$date_till = date('Y-m-d');
			}
			
			if(!empty($date_from) OR !empty($date_till)){
			
				if(empty($date_from)){ $date_from = date('Y-m-d'); }
				if(empty($date_till)){ $date_till = date('Y-m-td'); }
				
				$mktime_dari = strtotime($date_from);
				$mktime_sampai = strtotime($date_till);
							
				$qdate_from = date("Y-m-d",strtotime($date_from));
				$qdate_till = date("Y-m-d",strtotime($date_till));
				
				$params['where'][] = "(a.so_date >= '".$qdate_from."' AND a.so_date <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.so_number' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.so_number LIKE '%".$searching."%' OR c.customer_name LIKE '%".$searching."%')";
		}		
		//if(!empty($is_active)){
		//	$params['where'][] = "a.is_active = '".$is_active."'";
		//}
		if(!empty($not_cancel)){
			$params['where'][] = "a.so_status != 'cancel'";
		}else{
			if(!empty($so_status)){
				$params['where'][] = "a.so_status = '".$so_status."'";
			}
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		
  		$newData = array();		
		$all_id = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				if(!in_array($s['id'], $all_id)){
					$all_id[] = $s['id'];
				}
			}
		}
		
		
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				//$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				if($s['so_status'] == 'progress'){
					$s['so_status_text'] = '<span style="color:blue;">Progress</span>';
				}else 
				if($s['so_status'] == 'done'){
					$s['so_status_text'] = '<span style="color:green;">Done</span>';
				}else{
					$s['so_status_text'] = '<span style="color:red;">Cancel</span>';
				}
				
				$s['so_total_price_show'] = priceFormat($s['so_total_price']);
				$s['so_sub_total_show'] = priceFormat($s['so_sub_total']);
				$s['so_discount_show'] = priceFormat($s['so_discount']);
				$s['so_tax_show'] = priceFormat($s['so_tax']);
				$s['so_shipping_show'] = priceFormat($s['so_shipping']);
				$s['so_dp_show'] = priceFormat($s['so_dp']);
				
				$s['so_status_old'] = $s['so_status'];
				$s['so_date_text'] = date("d-m-Y",strtotime($s['so_date']));
				//$s['total_item'] = 0;
				//if(!empty($total_item[$s['id']])){
				//	$s['total_item'] = $total_item[$s['id']];
				//}
				
				//sales
				$s['sales_name_company_fee'] = '-- NO SALES --';
				if(!empty($s['sales_id'])){
					$sales_type_simple = 'A';
					if($s['sales_type'] == 'before_tax'){
						$sales_type_simple = 'B';
					}
					if(!empty($s['sales_percentage'])){
						$jenis_fee = $s['sales_percentage'].'%';
					}else{
						$jenis_fee = $s['sales_price'];
					}
					
					$s['sales_name_company_fee'] = $s['sales_name'].' / '.$s['sales_company'].' ('.$sales_type_simple.' '.$jenis_fee.')';
				}
				
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetail()
	{
		
		$this->table = $this->prefix.'salesorder_detail';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, b.item_name, b.item_code, b.item_image, c.unit_name",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'items as b','a.item_id = b.id','LEFT'),
										array($this->prefix.'unit as c','a.unit_id = c.id','LEFT')
									) 
								),
			'order'			=> array('a.id' => 'DESC'),
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$so_id = $this->input->post('so_id');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('b.item_name' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.item_name LIKE '%".$searching."%')";
		}
		if(!empty($so_id)){
			$params['where'] = array('a.so_id' => $so_id);
		}
		
		//get data -> data, totalCount
		$get_data = $this->m2->find_all($params);
		
		$newData = array();	
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['item_hpp_show'] = priceFormat($s['item_hpp']);
				$s['sales_price_show'] = priceFormat($s['sales_price']);
				$s['sod_potongan_show'] = priceFormat($s['sod_potongan']);
				$s['sod_total_show'] = priceFormat($s['sod_total']);
				$s['item_code_name'] = $s['item_code'].' / '.$s['item_name'];
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		  		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'salesorder';	
		$this->table2 = $this->prefix.'salesorder_detail';			
		$session_user = $this->session->userdata('user_username');
		
		$so_date = $this->input->post('so_date');
		$so_memo = $this->input->post('so_memo');
		
		$so_from = $this->input->post('so_from');
		$so_status = $this->input->post('so_status');
		
		$so_customer_name = $this->input->post('so_customer_name');
		$so_customer_address = $this->input->post('so_customer_address');
		$so_customer_phone = $this->input->post('so_customer_phone');
		
		$so_sub_total = $this->input->post('so_sub_total');
		$so_discount = $this->input->post('so_discount');
		$so_tax = $this->input->post('so_tax');
		$so_shipping = $this->input->post('so_shipping');
		$so_total_price = $this->input->post('so_total_price');
		$so_dp = $this->input->post('so_dp');
		$so_payment = $this->input->post('so_payment');
		
		//sales
		$sales_id = $this->input->post('sales_id');
		$sales_percentage = $this->input->post('sales_percentage');
		$sales_price = $this->input->post('sales_price');
		$sales_type = $this->input->post('sales_type');
		
		$single_rate = $this->input->post('single_rate');
		if(empty($single_rate)){
			$single_rate = 0;
		}
		
		if(empty($so_from)){
			$r = array('success' => false, 'info' => 'Input Warehouse From');
			die(json_encode($r));
		}
		
		$all_unik_kode = array();
		$all_unik_kode_perkey = array();
		$all_unik_kode_peritemId = array();
		$same_unik_kode = array();
		$message_same_unik_kode = array();
		$item_name_kode = array();
		
		$total_item = 0;
		$total_salesorder = 0;
		//salesOrderDetail				
		$salesOrderDetail = $this->input->post('salesOrderDetail');
		$salesOrderDetail = json_decode($salesOrderDetail, true);
		if(!empty($salesOrderDetail)){
			$total_item = count($salesOrderDetail);
			foreach($salesOrderDetail as $key => $dtDet){
				$total_salesorder += $dtDet['sod_qty'];
				
				//UNIK KODE
				if($dtDet['use_stok_kode_unik'] == 1){
					$list_dt_kode = explode("\n",$dtDet['data_stok_kode_unik']);
					foreach($list_dt_kode as $dt){
						if(!empty($dt)){
							if(!in_array($dt, $all_unik_kode)){
								$all_unik_kode[] = $dt;
								if(empty($all_unik_kode_perkey[$key])){
									$all_unik_kode_perkey[$key] = array();
								}
								$all_unik_kode_perkey[$key][] = $dt;
								
								//if(empty($all_unik_kode_peritemId[$dtDet['item_id']])){
								//	$all_unik_kode_peritemId[$dtDet['item_id']] = array();
								//}
								//$all_unik_kode_peritemId[$dtDet['item_id']][] = $dt;
								
								if(empty($all_unik_kode_peritemId[$dt])){
									$all_unik_kode_peritemId[$dt] = '';
								}
								$all_unik_kode_peritemId[$dt] = $dtDet['item_id'];
								
								$item_name_kode[$dtDet['item_id']] = $dtDet['item_name'];
								
							}else{
								$same_unik_kode[] = $dt;
								if(empty($message_same_unik_kode)){
									$r = array('success' => false, 'info' => 'Unik Kode (SN/IMEI): <b>'.$dt.'</b> lebih dari 1 data<br/>Cek pada Item: '.$dtDet['item_name']); 
									die(json_encode($r));
								}
							}
						}
						
					}
					
				}
				
				if(!empty($all_unik_kode_perkey[$key])){
					$receiveDetail[$key]['data_stok_kode_unik'] = implode("\n", $all_unik_kode_perkey[$key]);
					
					if($dtDet['sod_qty'] != count($all_unik_kode_perkey[$key])){
						$r = array('success' => false, 'info' => 'Total Unik Kode (SN/IMEI) pada Item: '.$dtDet['item_name'].' tidak sesuai dengan Total Qty yang dijual'); 
						die(json_encode($r));
					}
				
				}
				
			}
		}

		if(!empty($all_unik_kode)){
			$all_unik_kode_txt = implode("','", $all_unik_kode);
			
			$this->db->from($this->prefix.'item_kode_unik');
			$this->db->where("kode_unik IN ('".$all_unik_kode_txt."')");
			$get_unik_kode = $this->db->get();
			
			$all_unik_kode_db = array();
			$all_unik_kode_db_peritem = array();
			if($get_unik_kode->num_rows() > 0){
				foreach($get_unik_kode->result() as $dt){
					if(!in_array($dt->kode_unik, $all_unik_kode_db)){
						$all_unik_kode_db[] = $dt->kode_unik;
					}
					
					//cek kode unik per-item
					if(empty($all_unik_kode_db_peritem[$dt->kode_unik])){
						$all_unik_kode_db_peritem[$dt->kode_unik] = '';
					}
					$all_unik_kode_db_peritem[$dt->kode_unik] = $dt->item_id;
					
				}
			}
			
			$all_unik_kode_na = array();
			if(!empty($all_unik_kode_peritemId)){
				foreach($all_unik_kode_peritemId as $dt => $itemID){
					
					
					if(in_array($dt, $all_unik_kode_db)){
						//cek kode berdasarkan item id
						$nok_item = false;
						if(!empty($all_unik_kode_db_peritem[$dt])){
							if($all_unik_kode_db_peritem[$dt] == $itemID){
								//ok
								$nok_item = true;
							}else{
								$nok_item = false;
							}
						}
						
						if($nok_item == false){
							$r = array('success' => false, 'info' => 'Unik Kode (SN/IMEI): '.$dt.' tidak ada pada '.$item_name_kode[$itemID]); 
							die(json_encode($r));
						}
						
					}
					
					
				}
			}
		}
		
		$get_so_number = $this->generate_so_number();
		
		if(empty($get_so_number)){
			$r = array('success' => false);
			die(json_encode($r));
		}	
		
		if($so_status == 'done'){
			
			if($total_salesorder == 0){
				$r = array('success' => false, 'info' => 'Total item masuk = 0!'); 
				die(json_encode($r));
			}
			
		}
		
		
		$get_opt = get_option_value(array('so_count_stock'));
		
		$so_count_stock = 0;
		if(!empty($get_opt['so_count_stock'])){
			$so_count_stock = $get_opt['so_count_stock'];
		}
		
		
		$form_type = $this->input->post('form_type_salesOrder', true);
		
		$r = '';
		if($form_type == 'add')
		{
			
			if($so_count_stock == 1){
				$getItemData = $this->m2->getItem($salesOrderDetail, $so_from);
				$getItemData['tipe'] = 'add';
				$getStock = $this->stock->get_item_stock($getItemData, $so_date);
				$validStock = $this->stock->validStock($getItemData, $getStock);
				
				if(!empty($validStock['info'])){
					$r = array('success' => false, 'info'	=> '<br/>'.$validStock['info']);
					die(json_encode($r));
				}
			}
			
			
			$var = array(
				'fields'	=>	array(
				    'so_number'  	=> 	$get_so_number,
				    'so_date'  		=> 	$so_date,
				    'so_memo'  		=> 	$so_memo,
				    'so_from'  		=> 	$so_from,
				    'so_status'  	=> 	$so_status,
				    'so_customer_name'  	=> 	$so_customer_name,
				    'so_customer_address'  	=> 	$so_customer_address,
				    'so_customer_phone'  	=> 	$so_customer_phone,
				    'so_total_qty'  => 	$total_salesorder,
				    'so_discount'  	=> $so_discount,
				    'so_tax'  		=> $so_tax,
				    'so_shipping'  	=> $so_shipping,
				    'so_sub_total'  => $so_sub_total,
				    'so_total_price'  => $so_total_price,
				    'so_dp'  		=> $so_dp,
					'so_payment'  	=> 	$so_payment,
					'sales_id'		=>	$sales_id,
					'sales_percentage'	=>	$sales_percentage,
					'sales_price'		=>	$sales_price,
					'sales_type'		=>	$sales_type,
					'single_rate'		=>	$single_rate,
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
				$r = array('success' => true, 'id' => $insert_id, 'so_number'	=> '-'); 		
				$return_data = $this->m2->salesOrderDetail($salesOrderDetail, $insert_id);
				if(!empty($return_data['dtRo']['so_number'])){
					$r['so_number'] = $return_data['dtRo']['so_number'];
				}
				
				
				$do_update_stok = false;
				$do_update_rollback_stok = false;
				$warning_update_stok = false;
				
				if($so_status == 'done'){
					$do_update_stok = true;
					
					if($so_date != date("Y-m-d")){
						$warning_update_stok = true;
					}
					
				}
				
				
				if($so_status == 'progress'){
					if($so_date != date("Y-m-d")){
						$warning_update_stok = true;
					}
				}
				
				
				$update_stok = '';
				if($do_update_stok){
					$r['info'] = 'Update Stok';
					$update_stok = 'update';
				}
				
				if($do_update_rollback_stok){
					$r['info'] = 'Re-Update Stok';
					$update_stok = 'rollback';
				}
				
				
				
				if($do_update_stok OR $do_update_rollback_stok){
					
					//get/update ID -> $salesOrderDetail
					$item_id_prod = array();
					$this->db->from($this->prefix.'salesorder_detail');
					$this->db->where("so_id", $insert_id);
					$get_det = $this->db->get();
					if($get_det->num_rows() > 0){
						foreach($get_det->result_array() as $dt){
							$item_id_prod[$dt['item_id']] = $dt['id'];
						}
					}
					
					$salesOrderDetail_BU = $salesOrderDetail;
					$salesOrderDetail = array();
					foreach($salesOrderDetail_BU as $dtD){
						
						if(!empty($item_id_prod[$dtD['item_id']])){
							$dtD['id'] = $item_id_prod[$dtD['item_id']];
							$salesOrderDetail[] = $dtD;
						}
						
					}
					
					$return_data = $this->m2->salesOrderDetail($salesOrderDetail, $insert_id, $update_stok);
					$r['update_stock'] = $return_data['update_stock'];
					$post_params = array(
						'storehouse_item'	=> $return_data['update_stock']
					);
					
					$updateStock = $this->stock->update_stock_rekap($post_params);
					
				}
				
				
				
				if($warning_update_stok){
					$r['is_warning'] = 1;
					$r['info'] = 'Stock Been Changed (Realtime)<br/>Please Re-Generate/Fix Stock Transaction on List Stock Module!<br/>Re-generate/fix from: '.$so_date;
				}
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($form_type == 'edit'){
			
			
			
			//UPDATE
			$id = $this->input->post('id', true);
			
			//if($so_count_stock == 1){
				$getItemData = $this->m2->getItem($salesOrderDetail, $so_from, $id);
				$getItemData['tipe'] = 'edit';
				$getStock = $this->stock->get_item_stock($getItemData, $so_date);
				$validStock = $this->stock->validStock($getItemData, $getStock);
				//echo '<pre>';
				//print_r($validStock);
				//die();
				if(!empty($validStock['info']) AND $so_status == 'done'){
					$r = array('success' => false, 'info'	=> '<br/>'.$validStock['info']);
					die(json_encode($r));
				}
			//}
			
			if(empty($id)){
				$r = array('success' => false, 'info' => 'Sales Order unidentified!'); 
				die(json_encode($r));	
			}
			
			$var = array('fields'	=>	array(
					//'so_number'  	=> 	$so_number,
					'so_date'  		=> 	$so_date,
				    'so_customer_name'  	=> 	$so_customer_name,
				    'so_customer_address'  	=> 	$so_customer_address,
				    'so_customer_phone'  	=> 	$so_customer_phone,
					'so_memo'  		=> 	$so_memo,
					'so_from'  		=> 	$so_from,
					'so_total_qty'  => 	$total_salesorder,
				    'so_discount'  	=> $so_discount,
				    'so_tax'  		=> $so_tax,
				    'so_shipping'  	=> $so_shipping,
				    'so_sub_total'  => $so_sub_total,
				    'so_total_price'  => $so_total_price,
				    'so_dp'  		=> $so_dp,
					'so_payment'  	=> 	$so_payment,
					'sales_id'		=>	$sales_id,
					'sales_percentage'	=>	$sales_percentage,
					'sales_price'		=>	$sales_price,
					'sales_type'		=>	$sales_type,
					'single_rate'		=>	$single_rate,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			
			
			$old_data = array();
			$do_update_stok = false;
			$do_update_rollback_stok = false;
			$warning_update_stok = false;
			
			//CEK OLD DATA
			$this->db->from($this->table);
			$this->db->where("id = '".$id."'");
			$get_dt = $this->db->get();
			
			if($get_dt->num_rows() > 0){
				$old_data = $get_dt->row_array();
			}
			
			
			
			if($old_data['so_status'] != $so_status){
				
				
				if($old_data['so_status'] == 'progress' AND $so_status == 'done'){
					$do_update_stok = true;
					
					if($total_salesorder == 0){
						$r = array('success' => false, 'info' => 'Total di terima = 0!'); 
						die(json_encode($r));
					}
					
					if($so_date != date("Y-m-d")){
						$warning_update_stok = true;
					}
					
				}
				
				
				if($old_data['so_status'] == 'done' AND $so_status == 'progress'){
					$do_update_rollback_stok = true;
					
					if($so_date != date("Y-m-d")){
						$warning_update_stok = true;
					}
				}
				
				
				$var = array('fields'	=>	array(
						//'so_number'	=> 	$so_number,
						'so_date'		=> 	$so_date,
						'so_memo'		=> 	$so_memo,
						'so_customer_name'  	=> 	$so_customer_name,
						'so_customer_address'  	=> 	$so_customer_address,
						'so_customer_phone'  	=> 	$so_customer_phone,
						'so_status'  	=> 	$so_status,
						'so_total_qty'  => 	$total_salesorder,
						'so_discount'  	=> $so_discount,
						'so_tax'  		=> $so_tax,
						'so_shipping'  	=> $so_shipping,
						'so_sub_total'  => $so_sub_total,
						'so_total_price'  => $so_total_price,
						'so_dp'  		=> $so_dp,
						'so_payment'  	=> 	$so_payment,
						'updated'		=>	date('Y-m-d H:i:s'),
						'updatedby'		=>	$session_user
					),
					'table'			=>  $this->table,
					'primary_key'	=>  'id'
				);
				
			}else{
				
				if($old_data['so_status'] == 'done'){
					//$r = array('success' => false, 'info' => 'Cannot Update Sales Order Data been Done!'); 
					//die(json_encode($r));	
				}
				
			}
			
			
			$this->lib_trans->begin();
				$update = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				
				$r = array('success' => true, 'id' => $id);
				
				$update_stok = '';
				if($do_update_stok){
					$r['info'] = 'Update Stok';
					$update_stok = 'update';
				}
				
				if($do_update_rollback_stok){
					$r['info'] = 'Re-Update Stok';
					$update_stok = 'rollback';
				}
				
				$return_data = $this->m2->salesOrderDetail($salesOrderDetail, $id, $update_stok);
				
				if(!empty($return_data['update_stock'])){
					
					$r['update_stock'] = $return_data['update_stock'];
					$post_params = array(
						'storehouse_item'	=> $return_data['update_stock']
					);
					
					$updateStock = $this->stock->update_stock_rekap($post_params);
					
				}
				
				if($warning_update_stok){
					$r['is_warning'] = 1;
					$r['info'] = 'Stock Been Changed (Realtime)<br/>Please Re-Generate/Fix Stock Transaction on List Stock Module!<br/>Re-generate/fix from: '.$so_date;
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
		
		$this->table = $this->prefix.'salesorder';
		$this->table2 = $this->prefix.'salesorder_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//check data main if been validated
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		$this->db->where("so_status IN ('done')");
		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Cannot Delete Sales Order, Status is been done!</br>Please Refresh List Sales Order'); 
			die(json_encode($r));		
		}		
		
		//delete data
		$update_data = array(
			'so_status'	=> 'cancel',
			'is_deleted' => 1
		);
		
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->update($this->table, $update_data);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
			
			//delete detail too
			$update_data2 = array(
				'sod_status'	=> 'cancel'
			);
			
			$this->db->where("so_id IN ('".$sql_Id."')");
			$this->db->update($this->table2, $update_data2);
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Cancel Sales Order Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function deleteDetail()
	{
		
		$this->table = $this->prefix.'salesorder_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//check data main if been done
		$this->db->where("id IN ('".$sql_Id."')");
		$this->db->where("sod_status = 1");
		$get_dt = $this->db->get($this->table);
		if($get_dt->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Cannot Delete Data, Sales Order been done!'); 
			die(json_encode($r));		
		}
		
		//delete data
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->delete($this->table);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Sales Order Detail Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function get_total_qty($so_id){
		$this->table = $this->prefix.'salesorder_detail';	
		
		$this->db->select('SUM(sod_dikirim) as total_qty');
		$this->db->from($this->table);
		$this->db->where('so_id', $so_id);
		$get_tot = $this->db->get();
		
		$total_qty = 0;
		if($get_tot->num_rows() > 0){
			$data_ro = $get_tot->row();
			$total_qty = $data_ro->total_qty;
		}
		
		return $total_qty;
	}
	
	
	public function generate_so_number(){
		$this->table = $this->prefix.'salesorder';						
		
		$default_PRD = "SO".date("ym");
		$this->db->from($this->table);
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_ro = $get_last->row();
			//$so_number = $data_ro->so_number;
			$so_number = str_replace($default_PRD,"", $data_ro->so_number);
						
			$so_number = (int) $so_number;			
		}else{
			$so_number = 0;
		}
		
		$so_number++;
		$length_no = strlen($so_number);
		switch ($length_no) {
			case 3:
				$so_number = $so_number;
				break;
			case 2:
				$so_number = '0'.$so_number;
				break;
			case 1:
				$so_number = '00'.$so_number;
				break;
			default:
				$so_number = $so_number;
				break;
		}
				
		return $default_PRD.$so_number;				
	}
	
	public function printSO(){
		
		$this->table  = $this->prefix.'salesorder'; 
		$this->table2 = $this->prefix.'salesorder_detail';
		$this->table_client  = config_item('db_prefix').'clients';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('User Session Expired, Please Re-Login!');
		}
		
		extract($_GET);
		
		$client_id = $this->session->userdata('client_id');					
		
		//get client
		$this->db->from($this->table_client);
		$this->db->where("id",$client_id);
		$get_client = $this->db->get();
		$dt_client = array();
		if($get_client->num_rows() > 0){
			$dt_client = $get_client->row_array();
		}
		
		$data_post = array(
			'do'	=> '',
			'so_data'	=> array(),
			'salesorder_detail'	=> array(),
			'report_place_default'	=> '',
			'user_fullname'	=> $user_fullname,
			'session_user'	=> $session_user,
			'client'	=> $dt_client
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($so_id)){
			die('Sales Order Not Found!');
		}else{
			
			$this->db->select("a.*, c.storehouse_code, c.storehouse_name as so_from_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix."storehouse as c","c.id = a.so_from","LEFT");
			
			$this->db->where("a.id = '".$so_id."'");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['so_data'] = $get_dt->row_array();
				
				//get detail
				$this->db->select("a.*, b.item_code, b.item_name, b.item_type,
				c1.item_subcategory1_name as subcat1, c2.item_subcategory2_name as subcat2, c3.item_subcategory3_name as subcat3");
				$this->db->from($this->table2." as a");
				$this->db->join($this->prefix."items as b","b.id = a.item_id","LEFT");
				$this->db->join($this->prefix."item_subcategory1 as c1","c1.id = b.subcategory1_id","LEFT");
				$this->db->join($this->prefix."item_subcategory2 as c2","c2.id = b.subcategory2_id","LEFT");
				$this->db->join($this->prefix."item_subcategory3 as c3","c3.id = b.subcategory3_id","LEFT");
				$this->db->where("a.so_id = '".$so_id."'");
				$get_det = $this->db->get();
				if($get_det->num_rows() > 0){
					$data_post['so_detail'] = $get_det->result_array();
				}
				
			}else{
				die('Sales Order Not Found!');
			}
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}
		
		$print_layout = 'printSO';
		if(!empty($lx_print)){
			$print_layout = 'printSO-LX';
		}
		
		$this->load->view('../../sales_order/views/'.$print_layout, $data_post);
		
	}
}