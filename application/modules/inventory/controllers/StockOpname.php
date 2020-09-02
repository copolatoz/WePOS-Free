<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class StockOpname extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_stockopname', 'm');
		$this->load->model('model_stockopnamedetail', 'm2');
		$this->load->model('model_stock', 'stock');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'stock_opname';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'a.is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('a.id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$is_active = $this->input->post('is_active');
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
			
				if(empty($date_from)){ $date_from = date('Y-m-01'); }
				if(empty($date_till)){ $date_till = date('Y-m-t'); }
				
				$mktime_dari = strtotime($date_from);
				$mktime_sampai = strtotime($date_till);
							
				$qdate_from = date("Y-m-01",strtotime($date_from));
				$qdate_till = date("Y-m-t",strtotime($date_till));
				
				$params['where'][] = "(a.sto_date >= '".$qdate_from."' AND a.sto_date <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.sto_number' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.sto_number LIKE '%".$searching."%' OR a.sto_memo LIKE '%".$searching."%')";
		}		
		if(!empty($is_active)){
			$params['where'][] = "a.is_active = '".$is_active."'";
		}
				
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['sto_status_text'] = ($s['sto_status'] == 'done') ? '<span style="color:green;">Done</span>':'<span style="color:red;">Progress</span>';
				$s['sto_status_old'] = $s['sto_status'];
				$s['storehouse_id_old'] = $s['storehouse_id'];
				
				array_push($newData, $s);
				
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetail()
	{
		
		$this->table = $this->prefix.'stock_opname_detail';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, b.item_name, b.item_code, b.item_price, b.item_hpp, b.item_image, b.use_stok_kode_unik, c.unit_code, c.unit_name, a2.sto_number",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'stock_opname as a2','a.sto_id = a2.id','LEFT'),
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
		$sto_id = $this->input->post('sto_id');
		
		//if(!empty($is_dropdown)){
			$params['order'] = array('b.item_name' => 'ASC');
		//}
		if(!empty($searching)){
			$params['where'][] = "(b.item_name LIKE '%".$searching."%')";
		}
		
		if(!empty($sto_id)){
			$params['where'][] = array('a.sto_id' => $sto_id);
		}
		
		//get data -> data, totalCount
		$get_data = $this->m2->find_all($params);
		  		
		$newData = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['item_price_show'] = 'Rp '.priceFormat($s['item_price']);
				$s['current_hpp_avg_show'] = 'Rp '.priceFormat($s['current_hpp_avg']);
				$s['last_in_show'] = 'Rp '.priceFormat($s['last_in']);
				
				if(empty($s['total_last_in'])){
					$s['total_last_in'] = $s['total_last_in'] * $s['jumlah_fisik'];
				}
				if(empty($s['total_hpp_avg'])){
					$s['total_hpp_avg'] = $s['current_hpp_avg'] * $s['jumlah_fisik'];
				}
				
				$s['total_last_in_show'] = 'Rp '.priceFormat($s['total_last_in']);
				$s['total_hpp_avg_show'] = 'Rp '.priceFormat($s['total_hpp_avg']);
				
				$s['item_code_name'] = $s['item_code'].' / '.$s['item_name'];
				
				$s['use_stok_kode_unik_text'] = '<font color="red">Tidak</font>';
				if(!empty($s['use_stok_kode_unik'])){
					$s['use_stok_kode_unik_text'] = '<font color="green">Ya</font>';
				}
				
				unset($s['data_stok_kode_unik']);
				array_push($newData, $s);
				
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetailKodeUnik()
	{
		
		$this->table = $this->prefix.'stock_opname_kode_unik';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(),
			'order'			=> array('a.id' => 'DESC'),
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$stod_id = $this->input->post('stod_id');
		
		//if(!empty($is_dropdown)){
			$params['order'] = array('a.id' => 'DESC');
		//}
		if(!empty($searching)){
			$params['where'][] = "(a.kode_unik LIKE '%".$searching."%')";
		}
		
		$params['where'][] = array('a.stod_id' => $stod_id);
		
		//get data -> data, totalCount
		$get_data = $this->m2->find_all($params);
		  		
		$newData = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
		
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'stock_opname';	
		$this->table2 = $this->prefix.'stock_opname_detail';			
		$this->table3 = $this->prefix.'stock_opname_kode_unik';			
		$this->table_item_kode_unik = $this->prefix.'item_kode_unik';			
		$this->table_item_kode_unik_log = $this->prefix.'item_kode_unik_log';			
		$this->table_storehouse = $this->prefix.'storehouse';			
		$this->table_items = $this->prefix.'items';			
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			die(json_encode($r));
		}
		
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$sto_date = $this->input->post('sto_date');
		$sto_memo = $this->input->post('sto_memo');		
		$storehouse_id = $this->input->post('storehouse_id');		
		$storehouse_id_old = $this->input->post('storehouse_id_old');		
		$sto_status = $this->input->post('sto_status');		
		$sto_status_old = $this->input->post('sto_status_old');		
		
		if(empty($storehouse_id)){
			$r = array('success' => false, 'info'	=> 'Please Select Warehouse!');
			die(json_encode($r));
		}		
		
		if(!empty($storehouse_id)){
			$this->stock->cek_storehouse_access($storehouse_id);
		}
		
		if(empty($sto_status_old)){
			$sto_status_old = 'progress';
		}		
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
		//stoDetail
		$all_add_item = array();		
		$stoDetail = $this->input->post('stoDetail');
		$stoDetail = json_decode($stoDetail, true);	
		if(!empty($stoDetail)){	
			foreach($stoDetail as $dt){
				if(!in_array($dt['item_id'], $all_add_item)){
					$all_add_item[] = $dt['item_id'];
				}	
			}
			
			
			if(!empty($all_add_item)){
				$all_add_item_txt = implode(",", $all_add_item);
				
				$available_info = '';
				$this->db->select("a.*,b.sto_number, c.item_name");
				$this->db->from($this->table2." as a");
				$this->db->join($this->table." as b","b.id = a.sto_id","LEFT");
				$this->db->join($this->table_items." as c","c.id = a.item_id","LEFT");
				$this->db->where("a.item_id IN (".$all_add_item_txt.")");
				$this->db->where("b.storehouse_id = ".$storehouse_id);
				$this->db->where("b.sto_date = '".$sto_date."'");
				$this->db->where("b.sto_status = 'done'");
				$get_same_item = $this->db->get();
				if($get_same_item->num_rows() > 0){
					foreach($get_same_item->result() as $dtI){
						
						if(empty($available_info)){
							$available_info = $dtI->item_name.' available on STO: '.$dtI->sto_number;
						}
						
					}
				}
				
				if(!empty($available_info)){
					$r = array('success' => false, 'info'	=> $available_info);
					die(json_encode($r));
				}
				
			}
			
			
		}
		
		$r = '';
		

		//GET MAIN STOREHOUSE
		//$storehouse_id = $this->stock->get_primary_storehouse();
		//if(empty($storehouse_id)){
		//	$r = array('success' => false, 'info'	=> 'Primary Storehouse not found!');
		//}
		
			
		$update_stock = false;
		$rollback_stock = false;
		$warning_update_stok = false;
		
		if($this->input->post('form_type_stockOpname', true) == 'add')
		{
			if($sto_date != date("Y-m-d")){
				$warning_update_stok = true;
				if($sto_status_old == 'progress' AND $sto_status == 'done'){
					//$r = array('success' => false, 'info'	=> 'Stock Opname Date Should be Today!');
					//die(json_encode($r));		
				}
				
				if($sto_status_old == 'done' AND $sto_status == 'progress'){
					//$r = array('success' => false, 'info'	=> 'Cannot Rollback Stock Opname, Date Should be Today!');
					//die(json_encode($r));	
				}
			}
			
			$get_sto_number = $this->generate_sto_number();
		
			if(empty($get_sto_number)){
				$r = array('success' => false);
				die(json_encode($r));
			}	
			
			$var = array(
				'fields'	=>	array(
				    'sto_number'  	=> 	$get_sto_number,
				    'sto_date'  	=> 	$sto_date,
				    'sto_memo'  	=> 	$sto_memo,
				    'storehouse_id' => 	$storehouse_id,
				    'sto_status'  	=> 	$sto_status,
				    'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	1
				),
				'table'		=>  $this->table
			);	
			
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
				$q = $this->m->add($var);
				$insert_id = $this->m->get_insert_id();
				$sto_id = $insert_id;
				
			if($q)
			{  
				$r = array('success' => true, 'id' => $insert_id); 
					
				if(!empty($stoDetail)){
					
					//$stoDetail = json_decode($stoDetail, true);
				
					//insert batch
					$this->db->from($this->table);
					$this->db->where("id", $insert_id);
					$get_rowguid = $this->db->get();
					if($get_rowguid->num_rows() > 0){
						$dt_rowguid = $get_rowguid->row();
					}
					
					$is_selisih = 0;
					$dtInsert = array();
					$dtUpdate_Items = array();
					if(!empty($dt_rowguid)){
						foreach($stoDetail as $dt){
						
							$is_selisih = $dt['jumlah_fisik'] - $dt['jumlah_awal'];
							
							$dtInsert[] = array(
								"sto_id" => $dt_rowguid->id,
								"item_id" => $dt['item_id'],
								"current_hpp_avg" => $dt['item_hpp'],
								"unit_id" => $dt['unit_id'],
								"jumlah_awal" => $dt['jumlah_awal'],
								"jumlah_fisik" => $dt['jumlah_fisik'],
								"selisih" => $is_selisih,
								"description" => $dt['description'],
								"last_in" => $dt['last_in'],
								"total_last_in" => $dt['last_in']*$dt['jumlah_fisik'],
								"total_hpp_avg" => $dt['item_hpp']*$dt['jumlah_fisik']
							);
								
						}
					}
										
					if(!empty($dtInsert)){
						$doinsert_batch = $this->db->insert_batch($this->table2, $dtInsert);
						
						if($sto_status == 'done'){
							
						}
						
						
					}
					
				}
				
				if($sto_status_old == 'progress' AND $sto_status == 'done'){
					$update_stock = true;
				}
				
				if(!empty($get_sto_number)){
					$r['sto_number'] = $get_sto_number;
				}
				
				if($warning_update_stok){
					$r['is_warning'] = 1;
					$r['info'] = 'Silahkan Re-Generate/Perbaiki Stok Transaksi pada List Stock Module!<br/>Perbaiki Stok dari: '.$sto_date;
				}
				
			}  
			else
			{  
				$r = array('success' => false);
			}
			
      		$this->lib_trans->commit();			
		}else
		if($this->input->post('form_type_stockOpname', true) == 'edit'){
			
			
			$sto_id = $this->input->post('id', true);
			
			//get old data
			$this->db->from($this->table);
			$this->db->where("id",$sto_id);
			$get_sto = $this->db->get();
			if(!empty($get_sto->num_rows() > 0)){
				$sto_data = $get_sto->row();
				
				if($sto_data->sto_date != date("Y-m-d")){
					$warning_update_stok = true;
					if($sto_status_old == 'progress' AND $sto_status == 'done'){
						//$r = array('success' => false, 'info'	=> 'Cannot Change Stock Opname Status, Stock Opname Date Should be Today!');
						//die(json_encode($r));			
					}
					
					if($sto_status_old == 'done' AND $sto_status == 'progress'){
						//$r = array('success' => false, 'info'	=> 'Cannot Rollback Stock Opname, Date Should be Today!');
						//die(json_encode($r));	
					}
				}
				
				if($sto_date != date("Y-m-d")){
					if($sto_status_old == 'progress' AND $sto_status == 'progress'){
						//$r = array('success' => false, 'info'	=> 'Stock Opname Date Should be Today!');
						//die(json_encode($r));			
					}else{
						//$r = array('success' => false, 'info'	=> 'Cannot Change Stock Opname Date, Date Should be Today!');
						//die(json_encode($r));	
					}
				}
				
			}else{
				$r = array('success' => false, 'info'	=> 'Stock Opname Not Found!');
				die(json_encode($r));	
			}
			
			
			if($storehouse_id_old != $storehouse_id AND $sto_status_old == 'done'){
				$r = array('success' => false, 'info'	=> 'Cannot Change Warehouse on Status Done!');
				die(json_encode($r));
			}
			
			$var = array('fields'	=>	array(
				    //'sto_number'  	=> 	$get_sto_number,
				    'sto_date'  	=> 	$sto_date,
				    'sto_memo'  	=> 	$sto_memo,
				    'storehouse_id'  	=> 	$storehouse_id,
				    'sto_status'  	=> 	$sto_status,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			if($sto_status_old == 'progress' AND $sto_status == 'progress'){
				
			}else{
				unset($var['fields']['sto_date']);
				//no change date
			}
			
			//UPDATE
			$this->lib_trans->begin();
				$update = $this->m->save($var, $sto_id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				$r = array('success' => true, 'id' => $sto_id);
					
				if($sto_status_old == 'progress' AND $sto_status == 'done'){
					$update_stock = true;
				}
				
				if($sto_status_old == 'done' AND $sto_status == 'progress'){
					$rollback_stock = true;
				}
				
				if($warning_update_stok){
					$r['is_warning'] = 1;
					$r['info'] = 'Silahkan Re-Generate/Perbaiki Stok Transaksi pada List Stock Module!<br/>Perbaiki Stok dari: '.$sto_date;
				}
				
			}  
			else
			{  
				$r = array('success' => false);
			}
		}
		
		
		$this->db->from($this->table);
		$this->db->where("id", $sto_id);
		$get_sto = $this->db->get();
		if($get_sto->num_rows() > 0){
			$dt_sto = $get_sto->row();
			$get_sto_number = $dt_sto->sto_number;
		}else{
			$update_stock = false;
			$rollback_stock = false;
		}
		
		$r['sto_id'] = $sto_id;
		$r['use_stok_kode_unik'] = 0;
		$r['totalDetail'] = 0;
		
		if($update_stock == true){
			
			$all_item_stock = array();			
			$get_curr_recap_item = array();			
			$dtInsert_stock = array();
				
			$all_stod_id = array();
			$all_stod_data = array();
			//Insert Kartu Stock
			$this->db->from($this->table2);
			$this->db->where("sto_id", $sto_id);
			$get_detail = $this->db->get();
			if($get_detail->num_rows() > 0){
				foreach($get_detail->result_array() as $dt){
					if(!in_array($dt['item_id'], $get_curr_recap_item)){
						$get_curr_recap_item[] = $dt['item_id'];
					}
				}
			}
			
			//stock_rekap
			//update-2003.001
			if(!empty($get_curr_recap_item)){
				$get_curr_recap_item_sql = implode(",", $get_curr_recap_item);
				$this->db->select("a.*");
				$this->db->from($this->prefix."stock_rekap as a");
				if(!empty($storehouse_id)){
					$this->db->where('a.storehouse_id', $storehouse_id);	
				}
				$this->db->where("(a.trx_date = '".$sto_date."')");
				$this->db->where("a.item_id IN (".$get_curr_recap_item_sql.")");
				$getItemStock = $this->db->get();
				if($getItemStock->num_rows() > 0){
					foreach($getItemStock->result_array() as $dtR){
						if(empty($all_item_stock[$dtR['item_id']])){
							$all_item_stock[$dtR['item_id']] = $dtR;
						}
					}
				}
			}
			
			$update_jumlah_awal = array();
			if($get_detail->num_rows() > 0){
				foreach($get_detail->result_array() as $dt){
					
					if(!empty($all_item_stock[$dt['item_id']])){
						if($dt['jumlah_awal'] != $all_item_stock[$dt['item_id']]['total_stock']){
							$dt['jumlah_awal'] = $all_item_stock[$dt['item_id']]['total_stock'];
							$update_jumlah_awal[] = array(
								'id'	=> $dt['id'],
								'jumlah_awal'	=> $dt['jumlah_awal']
							);
						}
					}
					
					$is_selisih = $dt['jumlah_fisik'] - $dt['jumlah_awal'];
					
					$trx_qty = $dt['jumlah_fisik'];
					if($is_selisih >= 0){
						$trx_type = 'in';
						$trx_qty = $is_selisih;
					}else{
						$trx_type = 'out';
						$trx_qty = $is_selisih*-1;
					}
					
					//$trx_type = 'sto';
					//$trx_qty = $dt['jumlah_fisik'];
					
					$dtInsert_stock[] = array(
						"item_id" => $dt['item_id'],
						"trx_date" => $sto_date,
						"trx_type" => $trx_type,
						"trx_qty" => $trx_qty,
						"unit_id" => $dt['unit_id'],
						"storehouse_id" => $storehouse_id,
						"trx_nominal" => $dt['current_hpp_avg'],
						"trx_note" => 'Stock Opname',
						"trx_ref_data" => $get_sto_number,
						"trx_ref_det_id" => $dt['id'],
						"is_sto" => "1"
					);
					
					//if(!in_array($dt['id'], $all_stod_id) AND !empty($dt['use_stok_kode_unik'])){
					if(!empty($dt['use_stok_kode_unik'])){
						//$all_stod_id[] = $dt['id'];
						//$all_stod_data[$dt['id']] = $dt;
						if($r['use_stok_kode_unik'] == 0){
							$r['use_stok_kode_unik'] = 1;
						}
						
						$r['totalDetail']++;
					}
					
				}
				
				if(!empty($dtInsert_stock)){
					$this->db->insert_batch($this->prefix.'stock', $dtInsert_stock);
				}
				
				//$r['totalDetail'] = $get_detail->num_rows();
		
			}
			
			//UPDATE ALL DETAUL STATUS
			$update_det = array(
				'stod_status'	=> 1
			);
			$this->db->update($this->table2, $update_det, "sto_id = '".$sto_id."'");
			
			if(!empty($update_jumlah_awal)){
				$this->db->update_batch($this->table2, $update_jumlah_awal, "id");
			}
						
		}
		
		if($rollback_stock == true){
			
			//DELETE ALL STOCK
			$this->db->where("trx_ref_data", $get_sto_number);
			$this->db->delete($this->prefix."stock"); 
			
			//UPDATE ALL DETAUL STATUS
			$update_det = array(
				'stod_status'	=> 0
			);
			$this->db->update($this->table2, $update_det, "sto_id = '".$sto_id."'");
			
			
			///UPDATE STOREHOUSE DEFAULT
			$default_storehouse_kode_unik = array();
			$default_kode_unik = array();
			$this->db->from($this->table_item_kode_unik_log);
			$this->db->where("ref_out", $get_sto_number);
			$get_default_log = $this->db->get();
			if($get_default_log->num_rows() > 0){
				foreach($get_default_log->result_array() as $dt){
					$default_storehouse_kode_unik[$dt['kode_unik_id']] = $dt['storehouse_id'];
					if(!in_array($dt['kode_unik_id'], $default_kode_unik)){
						$default_kode_unik[] = $dt['kode_unik_id'];
					}
				}
			}
			
			//DELETE ALL STOCK - KODE UNIK
			$this->db->where("(ref_in = '".$get_sto_number."' OR ref_out = '".$get_sto_number."')");
			$this->db->delete($this->table_item_kode_unik_log); 
			
			//DELETE ALL STOCK - KODE UNIK
			$this->db->where("(ref_in = '".$get_sto_number."' OR ref_out = '".$get_sto_number."')");
			$this->db->delete($this->table_item_kode_unik); 
			
			if(!empty($default_kode_unik)){
				$default_kode_unik_sql = implode(",", $default_kode_unik);
				
				$available_log = array();
				$available_log_storehouse = array();
				$this->db->from($this->table_item_kode_unik_log);
				$this->db->where("kode_unik_id IN (".$default_kode_unik_sql.")");
				$this->db->order_by("id","DESC");
				$get_default_log = $this->db->get();
				if($get_default_log->num_rows() > 0){
					foreach($get_default_log->result_array() as $dt){
						if(!in_array($dt['kode_unik_id'], $available_log)){
							$available_log[] = $dt['kode_unik_id'];
							$available_log_storehouse[$dt['kode_unik_id']] = $dt['storehouse_id'];
						}
					}
				}
			}
			
			if(!empty($default_storehouse_kode_unik)){
				
				$default_storehouse_kode_unik_BU = $default_storehouse_kode_unik;
				$default_storehouse_kode_unik = array();
				foreach($default_storehouse_kode_unik_BU as $kodeid => $storeid){
					if(!empty($available_log_storehouse[$kodeid])){
						$storeid = $available_log_storehouse[$kodeid];
					}
					
					$default_storehouse_kode_unik[] = array(
						'id'			=> $kodeid,
						'storehouse_id'	=> $storeid,
					);
					
				}
				
				$this->db->update_batch($this->table_item_kode_unik, $default_storehouse_kode_unik,"id");
			}
			
		}
		
		if($update_stock == true OR $rollback_stock == true){
			
			$storehouse_item = array($storehouse_id => array());
			$this->db->from($this->table2);
			$this->db->where("sto_id", $sto_id);
			$get_detail = $this->db->get();
			if($get_detail->num_rows() > 0){
				foreach($get_detail->result_array() as $dt){
					$storehouse_item[$storehouse_id][] = $dt['item_id'];
				}
			}
			
			$post_params = array(
				'storehouse_item'	=> $storehouse_item
			);
			
			$r['storehouse_item'] =  $storehouse_item;
			
			$updateStock = $this->stock->update_stock_rekap($post_params);
		}
		
		$r['sto_id'] = $sto_id;
		$r['sto_number'] = $get_sto_number;
		$r['update_stock'] = $update_stock;
		$r['rollback_stock'] = $rollback_stock;
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function updateStockKodeUnik(){
		
		$this->table = $this->prefix.'stock_opname';	
		$this->table2 = $this->prefix.'stock_opname_detail';			
		$this->table3 = $this->prefix.'stock_opname_kode_unik';			
		$this->table_item_kode_unik = $this->prefix.'item_kode_unik';			
		$this->table_item_kode_unik_log = $this->prefix.'item_kode_unik_log';			
		$this->table_storehouse = $this->prefix.'storehouse';			
		$this->table_items = $this->prefix.'items';		
		
		$sto_id = $this->input->post('sto_id');
		$sto_no = $this->input->post('sto_no');
		$perdata = $this->input->post('perdata');
		$limit = $this->input->post('limit');
		$from_limit = $limit-1;
		if($from_limit < 0){
			$from_limit = 0;
		}
		
		$storehouse_id = 0;
		$get_sto_number = '';
		$sto_date = '';
		
		$this->db->from($this->table);
		$this->db->where("id", $sto_id);
		$get_sto = $this->db->get();
		if($get_sto->num_rows() > 0){
			$dt_sto = $get_sto->row();
			$get_sto_number = $dt_sto->sto_number;
			$storehouse_id = $dt_sto->storehouse_id;
			$sto_date = $dt_sto->sto_date;
		}else{
			$r = array('success' => false, 'info'	=> 'Data Stock Opname Tidak Ada!');
			die(json_encode($r));
		}
		
		$all_stod_id = array();
		$all_stod_data = array();
		
		$this->db->from($this->table2);
		$this->db->where("sto_id = ".$sto_id." AND use_stok_kode_unik = 1");
		$this->db->limit(($limit*$perdata), ($from_limit*$perdata));
		$get_detail = $this->db->get();
		if($get_detail->num_rows() > 0){
			
			foreach($get_detail->result_array() as $dt){
				if(!in_array($dt['id'], $all_stod_id) AND !empty($dt['use_stok_kode_unik'])){
					$all_stod_id[] = $dt['id'];
					$all_stod_data[$dt['id']] = $dt;
				}
			}
			
		}else{
			$r = array('success' => false, 'info'	=> 'Detail Stock Opname Tidak Ada!');
			die(json_encode($r));
		}
		
		//update stok sn/imei
		if(!empty($all_stod_id)){
			
			$r = array('success' => true, 'info'	=> count($all_stod_id).' Data Stock SN/IMEI sudah disimpan!');
			
			$stod_id_sql = implode(",", $all_stod_id);
		
			//cari di stok sn/imei
			$all_new_sn_imei = array();
			$dtInsert_stock_kode_unik = array();
			$dtUpdate_stock_kode_unik = array();
			$dtUpdate_stock_kode_unik_log = array();
			
			$this->db->select('a.*, b.id as kode_id, b.storehouse_id');
			$this->db->from($this->table3.' as a');
			$this->db->join($this->table_item_kode_unik.' as b',"b.kode_unik = a.kode_unik","LEFT");
			$this->db->where("stod_id IN (".$stod_id_sql.")");
			$get_detail_kode_unik = $this->db->get();
			if($get_detail_kode_unik->num_rows() > 0){
				foreach($get_detail_kode_unik->result_array() as $dt){
					if(empty($dt['kode_id'])){
						
						if(!in_array($dt['kode_unik'], $all_new_sn_imei)){
							$all_new_sn_imei[] = $dt['kode_unik'];
						
							$get_det_data = $all_stod_data[$dt['stod_id']];
							if(empty($get_det_data)){
								$get_det_data['item_id'] = 0;
								$get_det_data['item_hpp'] = 0;
							}
							
							$dtInsert_stock_kode_unik[] = array(
								'item_id'	=> $get_det_data['item_id'],
								"kode_unik" => $dt['kode_unik'],
								"ref_in" => $get_sto_number,
								"date_in" => $sto_date.' '.date("H:i:s"),
								"storehouse_id" => $storehouse_id,
								"item_hpp" => $get_det_data['current_hpp_avg'],
								"varian_name" => $dt['varian_name'],
								"varian_group" => $dt['varian_name']
							);
							
							$dtInsert_stock_kode_unik_log[] = array(
								'kode_unik_id'	=> $get_det_data['item_id'],
								"ref_in" => $get_sto_number,
								"date_in" => $sto_date.' '.date("H:i:s"),
								"storehouse_id" => $storehouse_id,
								"item_hpp" => $get_det_data['current_hpp_avg'],
								"varian_name" => $dt['varian_name'],
								"varian_group" => $dt['varian_name']
							);
						}
						
					}else{
						
						if($dt['storehouse_id'] != $storehouse_id){
							
							$dtUpdate_stock_kode_unik[] = array(
								"id" => $dt['kode_id'],
								"storehouse_id" => $storehouse_id
							);
							
							$dtInsert_stock_kode_unik_log[] = array(
								'kode_unik_id'	=> $get_det_data['item_id'],
								"ref_out" => $get_sto_number,
								"date_out" => $sto_date.' '.date("H:i:s"),
								"storehouse_id" => $dt['storehouse_id'],
								"item_hpp" => $get_det_data['current_hpp_avg'],
								"varian_name" => $dt['varian_name'],
								"varian_group" => $dt['varian_name']
							);
							
							$dtInsert_stock_kode_unik_log[] = array(
								'kode_unik_id'	=> $get_det_data['item_id'],
								"ref_in" => $get_sto_number,
								"date_in" => $sto_date.' '.date("H:i:s"),
								"storehouse_id" => $storehouse_id,
								"item_hpp" => $get_det_data['current_hpp_avg'],
								"varian_name" => $dt['varian_name'],
								"varian_group" => $dt['varian_name']
							);
							
						}
						
					}
				}
			}
			
			if(!empty($dtInsert_stock_kode_unik)){
				$this->db->insert_batch($this->table_item_kode_unik, $dtInsert_stock_kode_unik);
			}
			
			if(!empty($dtUpdate_stock_kode_unik)){
				$this->db->update_batch($this->table_item_kode_unik, $dtUpdate_stock_kode_unik,"id");
			}
			
			if(!empty($dtInsert_stock_kode_unik_log)){
				$this->db->insert_batch($this->table_item_kode_unik_log, $dtInsert_stock_kode_unik_log);
			}
			
		}
			
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function saveDetail(){
		
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table = $this->prefix.'stock_opname';				
		$this->table2 = $this->prefix.'stock_opname_detail';				
		$this->table_items = $this->prefix.'items';				
		
		$session_user = $this->session->userdata('user_username');
		$session_client_id = $this->session->userdata('client_id');
		
		$form_type = $this->input->post('form_type');
		$sto_id = $this->input->post('sto_id');
		$sto_number = $this->input->post('sto_number');
		$sto_detail_id = $this->input->post('id');
		$item_id = $this->input->post('item_id');
		$item_price = $this->input->post('item_price');
		$item_hpp = $this->input->post('item_hpp');
		$unit_id = $this->input->post('unit_id');
		$jumlah_awal = $this->input->post('jumlah_awal');
		$jumlah_fisik = $this->input->post('jumlah_fisik');
		$description = $this->input->post('description');
		$last_in = $this->input->post('last_in');
		
		//check data main if been validated
		$storehouse_id = 0;
		$sto_date = 0;
		$dt_sto = array();
		$this->db->from($this->table);
		$this->db->where("id = ".$sto_id);
		//$this->db->where("sto_status IN ('done')");
		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			
			$dt_sto = $get_dt->row();
			$storehouse_id = $dt_sto->storehouse_id;
			$sto_date = $dt_sto->sto_date;
			if($dt_sto->sto_status == 'done'){
				$r = array('success' => false, 'info' => 'Tidak Bisa Update, Status STock Opname sudah selesai!'); 
				die(json_encode($r));	
			}
				
		}
		
		if(empty($sto_id) OR empty($item_id) OR empty($session_client_id)){
			$r = array('success' => false, 'info' => 'Simpan Detail Gagal!');
			die(json_encode($r));
		}		
		
		
		if(!empty($storehouse_id) AND !empty($sto_date)){
			
			$available_info = '';
			$this->db->select("a.*,b.sto_number, c.item_name");
			$this->db->from($this->table2." as a");
			$this->db->join($this->table." as b","b.id = a.sto_id","LEFT");
			$this->db->join($this->table_items." as c","c.id = a.item_id","LEFT");
			$this->db->where("a.item_id IN (".$item_id.")");
			$this->db->where("b.storehouse_id = ".$storehouse_id);
			$this->db->where("b.sto_date = '".$sto_date."'");
			$this->db->where("b.sto_status = 'done'");
			
			$this->db->where("b.id != ".$sto_id);
		
			$get_same_item = $this->db->get();
			if($get_same_item->num_rows() > 0){
				foreach($get_same_item->result() as $dtI){
					
					if(empty($available_info)){
						$available_info = $dtI->item_name.' available on STO: '.$dtI->sto_number;
					}
					
				}
			}
			
			if(!empty($available_info)){
				$r = array('success' => false, 'info'	=> $available_info);
				die(json_encode($r));
			}
			
		}
		
		$is_selisih = $jumlah_fisik - $jumlah_awal;
		
		$var = array('fields'	=>	array(
				'sto_id'		=> 	$sto_id,
				'item_id' 		=> 	$item_id,
				'current_hpp_avg' 	=> 	$item_hpp,
				'unit_id' 		=> 	$unit_id,
				'jumlah_awal' 	=> 	$jumlah_awal,
				'jumlah_fisik'	=> 	$jumlah_fisik,
				'selisih'		=> 	$is_selisih,
				'description' 	=> $description,
				"last_in" 		=> $last_in,
				"total_last_in" => $last_in*$jumlah_fisik,
				"total_hpp_avg" => $item_hpp*$jumlah_fisik
			),
			'table'			=>  $this->table2,
			'primary_key'	=>  'id'
		);
		
		//ADD/Edit		
		$this->lib_trans->begin();
			if(!empty($sto_detail_id)){
				$edit = $this->m2->save($var, $sto_detail_id);
			}else{
				$edit = $this->m2->save($var);
				$sto_detail_id = $this->m->get_insert_id();
			}
		$this->lib_trans->commit();
		
		if($edit)
		{  
				
			$r = array('success' => true, 'item_id' => $item_id);
						
		}  
		else
		{  
			$r = array('success' => false, 'info' => 'Simpan Detail Gagal!');
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function saveKodeUnik(){
		
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table = $this->prefix.'stock_opname';				
		$this->table2 = $this->prefix.'stock_opname_detail';				
		$this->table3 = $this->prefix.'stock_opname_kode_unik';				
		$this->table_items = $this->prefix.'items';				
		$this->table_varian_item = $this->prefix.'varian_item';				
		
		$session_user = $this->session->userdata('user_username');
		$session_client_id = $this->session->userdata('client_id');
		
		$sto_id = $this->input->post('sto_id');
		$stod_id = $this->input->post('stod_id');
		$kode_unik_id = $this->input->post('kode_unik_id');
		$varian_name = $this->input->post('varian_name');
		$kode_unik = $this->input->post('kode_unik');
		
		$varian_name = strtoupper($varian_name);
		
		//check data main if been validated
		$storehouse_id = 0;
		$sto_date = 0;
		$dt_sto = array();
		$this->db->from($this->table);
		$this->db->where("id = ".$sto_id);
		//$this->db->where("sto_status IN ('done')");
		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			
			$dt_sto = $get_dt->row();
			$storehouse_id = $dt_sto->storehouse_id;
			$sto_date = $dt_sto->sto_date;
			if($dt_sto->sto_status == 'done'){
				$r = array('success' => false, 'info' => 'Tidak Bisa Update, Status Stock Opname sudah selesai!'); 
				die(json_encode($r));	
			}
				
		}
		
		if(empty($sto_id) OR empty($stod_id) OR empty($kode_unik) OR empty($session_client_id)){
			$r = array('success' => false, 'info' => 'Simpan Kode Unik Gagal!');
			die(json_encode($r));
		}		
		
		
		if(!empty($stod_id) AND !empty($kode_unik)){
			
			$available_info = '';
			$this->db->select("a.*");
			$this->db->from($this->table3." as a");
			$this->db->where("a.stod_id = ".$stod_id);
			$this->db->where("a.kode_unik = '".$kode_unik."'");
		
			$get_same_kode_unik= $this->db->get();
			if($get_same_kode_unik->num_rows() > 0){
				foreach($get_same_kode_unik->result() as $dtI){
					
					if(empty($available_info)){
						$available_info = 'SN/IMEI: '.$kode_unik.' sudah ada!';
					}
					
				}
			}
			
			if(!empty($available_info)){
				$r = array('success' => false, 'info'	=> $available_info);
				die(json_encode($r));
			}
			
		}
		
		$var = array('fields'	=>	array(
				'stod_id'		=> 	$stod_id,
				'kode_unik' 	=> 	$kode_unik,
				'varian_name' 	=> 	$varian_name,
			),
			'table'			=>  $this->table3,
			'primary_key'	=>  'id'
		);
		
		//ADD/Edit		
		$this->lib_trans->begin();
			if(!empty($kode_unik_id)){
				$edit = $this->m2->save($var, $kode_unik_id);
			}else{
				$edit = $this->m2->save($var);
				$kode_unik_id = $this->m->get_insert_id();
			}
		$this->lib_trans->commit();
		
		if($edit)
		{  
				
			$r = array('success' => true, 'kode_unik_id' => $kode_unik_id);
			
			$new_varian = false;
			$update_varian_id = 0;
			
			$this->db->select("a.*");
			$this->db->from($this->table_varian_item." as a");
			$this->db->where("a.varian_name = '".$varian_name."'");
			$get_varian_name = $this->db->get();
			if($get_varian_name->num_rows() > 0){
				$dt_varian = $get_varian_name->row();
				if($dt_varian->is_active == 0 OR $dt_varian->is_deleted == 1){
					$update_varian_id = $dt_varian->id;
				}
			}else{
				$new_varian = true;
			}	
			
			$data_varian = array(
				'varian_name' 	=> 	$varian_name,
				'is_active' 	=> 	1,
				'is_deleted' 	=> 	0,
			);
			
			if($new_varian == true){
				$this->db->update($this->table_varian_item,$data_varian);
			}
			
			if(!empty($update_varian_id)){
				$this->db->update($this->table_varian_item,$data_varian,"id=".$update_varian_id);
			}
			
		}  
		else
		{  
			$r = array('success' => false, 'info' => 'Simpan Kode Unik Gagal!');
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function downloadStockOpname(){
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		
		if(empty($do)){
			die();
		}
		
		$data_post = array(
			'do'	=> '',
			'report_name'	=> 'STOCK OPNAME',
			'report_data'	=> array()
		);
		
		
		if(empty($storehouse_id)){
			$storehouse_id = $this->stock->get_primary_storehouse();
		}
		
		
		$this->table_stock = $this->prefix.'stock';
		$this->table_items = $this->prefix.'items';
		
		/*$this->db->select("a.*, x.item_id, x.storehouse_id, b.item_category_name, b.item_category_code, c.unit_name");
		$this->db->from($this->table_stock." as x");
		$this->db->join($this->table_items.' as a',"a.id = x.item_id");
		$this->db->join($this->prefix.'item_category as b','a.category_id = b.id','LEFT');
		$this->db->join($this->prefix.'unit as c','a.unit_id = c.id','LEFT');
		$this->db->where('a.is_active', 1);
		$this->db->where('a.is_deleted', 0);
		$this->db->where('x.storehouse_id', $storehouse_id);
		$this->db->group_by('x.item_id');
		$this->db->group_by('x.storehouse_id');
		$this->db->order_by('x.item_id', "ASC");
		
		//get data -> data, totalCount
		$dt_item = array();
		$get_data = $this->db->get();
		if($get_data->num_rows() > 0){
			$dt_item = $get_data->result_array();
		}else{
			
			//ASUMSI STOK AWAL - MASIH KOSONG
			$this->db->select("a.*, b.item_category_name, b.item_category_code, c.unit_name");
			$this->db->from($this->table_items.' as a');
			$this->db->join($this->prefix.'item_category as b','a.category_id = b.id','LEFT');
			$this->db->join($this->prefix.'unit as c','a.unit_id = c.id','LEFT');
			$this->db->where('a.is_active', 1);
			$this->db->where('a.is_deleted', 0);
			$this->db->order_by('a.id', "ASC");
			$get_data = $this->db->get();
			if($get_data->num_rows() > 0){
				$dt_item = $get_data->result_array();
			}

		}*/
  		
		$dt_item = array();
		//ASUMSI STOK AWAL - MASIH KOSONG
		$this->db->select("a.*, b.item_category_name, b.item_category_code, c.unit_name");
		$this->db->from($this->table_items.' as a');
		$this->db->join($this->prefix.'item_category as b','a.category_id = b.id','LEFT');
		$this->db->join($this->prefix.'unit as c','a.unit_id = c.id','LEFT');
		$this->db->where('a.is_active', 1);
		$this->db->where('a.is_deleted', 0);
		$this->db->order_by('a.id', "ASC");
		$get_data = $this->db->get();
		if($get_data->num_rows() > 0){
			$dt_item = $get_data->result_array();
		}
		
  		$newData = array();
		
		$cat_name = array();
		$storehouse_item = array($storehouse_id => array());
		if(!empty($dt_item)){
			foreach ($dt_item as $s){
				$s['total_qty_stok'] = 0;
				
				if(empty($newData[$s['category_id']])){
					$newData[$s['category_id']] = array();
				}
				
				$newData[$s['category_id']][] = $s;
				
				$storehouse_item[$storehouse_id][] = $s['id'];
				
				if(empty($cat_name[$s['category_id']])){
					$cat_name[$s['category_id']] = $s['item_category_name'];
				}
				
				//array_push($newData, $s);
				//array_push($storehouse_item[$storehouse_id], $s['id']);
			}
		}
		
		$dt_item = $newData;
		
		$storehouse_name = '';
		if(!empty($storehouse_item)){
			
			$params = array('storehouse_item' => $storehouse_item);
			
			if(empty($date)){
				$date = date("Y-m-d");
			}
			
			$get_item_stock = $this->stock->get_item_stock($params, $date);
			
			//echo '<pre>';
			//print_r($cat_name);
			//die();
			
			$newData = array();
			if(!empty($dt_item)){
				foreach ($dt_item as $cat_id => $det){
					
					if(!empty($det)){
					
						if(empty($newData[$cat_id])){
							$newData[$cat_id] = array();
						}
						
						foreach ($det as $s){
							
							
							
							$s['total_qty_stok'] = 0;
							$s['storehouse_name'] = '';
							
							if(!empty($get_item_stock[$storehouse_id][$s['id']])){
								$s['total_qty_stok'] = $get_item_stock[$storehouse_id][$s['id']]['total_qty_stok'];
								$s['storehouse_name'] = $get_item_stock[$storehouse_id][$s['id']]['storehouse_name'];
							}
							
							if(empty($storehouse_name)){
								$storehouse_name = $s['storehouse_name'];
							}
							
							$newData[$cat_id][] = $s;
							
						}
					}
					
					
					//array_push($newData, $s);
				}
			}
			$dt_item = $newData;
		}
		
		$data_post['report_data'] = $dt_item;
		$data_post['cat_name'] = $cat_name;
		
		//GET WAREHOUSE
		$this->db->select("a.*");
		$this->db->from($this->prefix."storehouse as a");
		
		if(!empty($storehouse_id)){
			$this->db->where('a.id', $storehouse_id);	
		}
		
		$getWarehouse = $this->db->get();
		if($getWarehouse->num_rows() > 0){
			$dt_warehouse = $getWarehouse->row();
			$storehouse_name = $dt_warehouse->storehouse_name;
		}
		
		
		$data_post['storehouse_name'] = $storehouse_name;
		
		//echo $storehouse_name.'<pre>';
		//print_r($data_post['report_data']);
		//die();
		
		$useview = 'excel_downloadStockOpname';		
		$this->load->view('../../inventory/views/'.$useview, $data_post);
		
	}
	
	public function saveDetailImport()
	{
		
		$session_user = $this->session->userdata('user_username');
		
		$this->file_stock_path = RESOURCES_PATH.'stock/';
		
		$r = ''; 
		$is_upload_file = false;	

		//echo '<pre>';
		//print_r($_FILES['upload_file']);
		
		if(!empty($_FILES['upload_file']['name'])){
						
			$config['upload_path'] = $this->file_stock_path;
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
				$file =  $this->file_stock_path.$data_upload_temp['file_name']."" ;
				$xls->read($file);
				//echo '<pre>';
				//print_r($xls->sheets);die();
				
				error_reporting(E_ALL ^ E_NOTICE);
				
				$nr_sheets = count($xls->sheets);    
				
				$this->lib_trans->begin();
				
				//get all item
				$this->table_items = $this->prefix.'items';
				$this->table_stock_opname_detail_upload = $this->prefix.'stock_opname_detail_upload';
				
				//TRUNCATE
				$this->db->truncate($this->table_stock_opname_detail_upload);
		
				$this->db->select("a.*, b.item_category_name, b.item_category_code, c.unit_name");
				$this->db->from($this->table_items.' as a');
				$this->db->join($this->prefix.'item_category as b','a.category_id = b.id','LEFT');
				$this->db->join($this->prefix.'unit as c','a.unit_id = c.id','LEFT');
				$this->db->where('a.is_active', 1);
				$this->db->order_by('a.item_name', "ASC");
				
				//get data -> data, totalCount
				$dt_item = array();
				$get_data = $this->db->get();
				if($get_data->num_rows() > 0){
					foreach($get_data->result_array() as $dt){
						$dt_item[$dt['id']] = $dt;
					}
				}
				
				$import_data_opname = array();
				
				for($i=0; $i<$nr_sheets; $i++) {
					//echo $xls->boundsheets[$i]['name'];
					//print_r($xls->sheets[$i]);
					
					for ($row_num = 4; $row_num <= $xls->sheets[$i]['numRows']; $row_num++) {	
						
						//echo '<pre>';
						//print_r($xls->sheets[$i]['cells'][$row_num]);
						//die();
						
						//id	item_name	unit_name	qty_awal	qty_fisik	notes
						
						$item_code = $xls->sheets[$i]['cells'][$row_num][2];								
						$item_name = $xls->sheets[$i]['cells'][$row_num][3];								
						$unit_name = $xls->sheets[$i]['cells'][$row_num][4];								
						$jumlah_awal = $xls->sheets[$i]['cells'][$row_num][5];															
						$jumlah_fisik = $xls->sheets[$i]['cells'][$row_num][6];															
						$description = '';															
						$selisih = $jumlah_fisik-$jumlah_awal;															
						$item_id = $xls->sheets[$i]['cells'][$row_num][1];	
						
						$unit_id = 0;
						$current_hpp_avg = 0;
						$last_in = 0;
						if(!empty($dt_item[$item_id])){
							$unit_id = $dt_item[$item_id]['unit_id'];	
							$current_hpp_avg = $dt_item[$item_id]['item_hpp'];
							$last_in = $dt_item[$item_id]['last_in'];						
						}
						
						$update_date = date('Y-m-d H:i:s');
						
						//INSERT									
						/*$var = array(
							'fields'	=>	array(
								'item_id'	=> 	$item_id,
								'jumlah_awal'	=>	$jumlah_awal,
								'jumlah_fisik'	=>	$jumlah_fisik,
								'selisih'		=>	$selisih,
								'description'	=>	$description,
								'unit_id'		=>	$unit_id,
								'current_hpp_avg'	=>	$current_hpp_avg,
								'last_in'	=>	$last_in,
								'total_hpp_avg'	=>	$last_in*$jumlah_fisik,
								'total_last_in'	=>	$last_in*$current_hpp_avg
							),
							'table'		=>  $this->table_stock_opname_detail_upload
						);	
						
						$q = $this->m->save($var);
						*/
						if(!empty($item_id) AND !empty($item_code) AND !empty($item_name) AND !empty($unit_name)){
							$import_data_opname[] = array(
								'item_id'	=> 	$item_id,
								'jumlah_awal'	=>	$jumlah_awal,
								'jumlah_fisik'	=>	$jumlah_fisik,
								'selisih'		=>	$selisih,
								'description'	=>	$description,
								'unit_id'		=>	$unit_id,
								'current_hpp_avg'	=>	$current_hpp_avg,
								'last_in'	=>	$last_in,
								'total_hpp_avg'	=>	$last_in*$jumlah_fisik,
								'total_last_in'	=>	$last_in*$jumlah_fisik
							);
						}
						
						
					}
					
				}    
				
				$q = false;
				if(!empty($import_data_opname)){
					$q = $this->db->insert_batch($this->table_stock_opname_detail_upload, $import_data_opname);
				}
				
				$this->lib_trans->commit();	
				
				if($q)
				{ 
					$r = array('success' => true); 	
					@unlink($file);					
				}  
				else
				{  				
					$r = array('success' => false);
				}
				
				
			}
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));	
 
	}
	
	public function loadUpload()
	{
		$this->table_stock_opname_detail_upload = $this->prefix.'stock_opname_detail_upload';
		
		$this->db->select("a.*, b.item_code, b.item_name, b.item_price, b.item_hpp, b.last_in as last_in_item, b.item_image, c.unit_name");
		$this->db->from($this->table_stock_opname_detail_upload.' as a');
		$this->db->join($this->prefix.'items as b','a.item_id = b.id','LEFT');
		$this->db->join($this->prefix.'unit as c','a.unit_id = c.id','LEFT');
		$this->db->order_by('b.item_code', "ASC");
		
		//get data -> data, totalCount
		$newData = array();
		$get_data = $this->db->get();
		if($get_data->num_rows() > 0){
			foreach($get_data->result_array() as $s){
				//$s['id_upload'] = $s['id'];
				//$s['id'] = '';
				
				$s['last_in'] = $s['last_in_item'];
				$s['current_hpp_avg'] = $s['item_hpp'];
				
				$s['item_price_show'] = 'Rp '.priceFormat($s['item_price']);
				$s['current_hpp_avg_show'] = 'Rp '.priceFormat($s['current_hpp_avg']);
				$s['last_in_show'] = 'Rp '.priceFormat($s['last_in']);
				
				if(empty($s['total_last_in'])){
					$s['total_last_in'] = $s['total_last_in'] * $s['jumlah_fisik'];
				}
				if(empty($s['total_hpp_avg'])){
					$s['total_hpp_avg'] = $s['current_hpp_avg'] * $s['jumlah_fisik'];
				}
				
				$s['total_last_in_show'] = 'Rp '.priceFormat($s['total_last_in']);
				$s['total_hpp_avg_show'] = 'Rp '.priceFormat($s['total_hpp_avg']);
				$s['item_code_name'] = $s['item_code'].' / '.$s['item_name'];
				
				
				array_push($newData, $s);
			}
		}
		
		$get_data = array();
		$get_data['data'] = $newData;
		$get_data['total'] = count($newData);
		
      	die(json_encode($get_data));
	}
		
	public function delete()
	{
		
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table = $this->prefix.'stock_opname';
		$this->table2 = $this->prefix.'stock_opname_detail';
		
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
		$this->db->where("sto_status IN ('done')");
		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Cannot Delete Stock Opname, Status sudah selesai!</br>Please Refresh List Stock Opname!'); 
			die(json_encode($r));		
		}	
		
		//Get STO Detail
		$this->db->select('a.*, b.sto_number');
		$this->db->from($this->table2.' as a');
		$this->db->join($this->table.' as b', 'a.sto_id=b.id','LEFT');
		$this->db->where("a.sto_id IN ('".$sql_Id."')");
		$get_sto_detail = $this->db->get();
		
		//delete data
		$update_data = array(
			'sto_status'=> 'cancel',
			'is_deleted'=> 1
		);
		
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->update($this->table, $update_data);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
			
			//delete data
			//$update_data = array(
			//	'stod_status'=> 0
			//);
			
			//detail
			//$this->db->where("sto_id IN ('".$sql_Id."')");
			//$q = $this->db->update($this->table2, $update_data);
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Hapus Stock Opname Gagal!'); 
        }
		die(json_encode($r));
	}
	
	public function deleteDetail()
	{
		
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table = $this->prefix.'stock_opname_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}

		
		
		//check data main if been done
		$this->db->where("id IN ('".$sql_Id."')");
		$this->db->where("stod_status = 1");
		$get_dt = $this->db->get($this->table);
		if($get_dt->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Tidak bisa hapus data, Status Stock Opname sudah selesai!'); 
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
            $r = array('success' => false, 'info' => 'Hapus Stock Opname Detail Gagal!'); 
        }
		die(json_encode($r));
	}
	
	public function deleteKodeUnik()
	{
		
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table_sto = $this->prefix.'stock_opname';
		$this->table_sto_kodeunik = $this->prefix.'stock_opname_kode_unik';
		
		$sto_id = $this->input->post('sto_id', true);	
		$stod_id = $this->input->post('stod_id', true);	
		$get_id = $this->input->post('id', true);	
		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}

		
		//check data main if been done
		$this->db->where("id IN ('".$sto_id."')");
		$this->db->where("sto_status = 'done'");
		$get_dt = $this->db->get($this->table_sto);
		if($get_dt->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Tidak bisa hapus data, Status Stock Opname sudah selesai!'); 
			die(json_encode($r));			
		}
		
		//delete data
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->delete($this->table_sto_kodeunik);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Hapus SN/IMEI Gagal!'); 
        }
		die(json_encode($r));
	}
	
	public function generate_sto_number(){
		$this->table = $this->prefix.'stock_opname';						
		$sto_format = "STO".date('ym');
		$this->db->from($this->table);
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_po = $get_last->row();
			$sto_number = str_replace($sto_format,"", $data_po->sto_number);
						
			$sto_number = (int) $sto_number;			
		}else{
			$sto_number = 0;
		}
		
		$sto_number++;
		$length_no = strlen($sto_number);
		switch ($length_no) {
			case 2:
				$sto_number = $sto_number;
				break;
			case 1:
				$sto_number = '0'.$sto_number;
				break;
			default:
				$sto_number = $sto_number;
				break;
		}
				
		return $sto_format.$sto_number;				
	}
	
	public function printStockOpname(){
		
		$this->table  = $this->prefix.'stock_opname'; 
		$this->table2 = $this->prefix.'stock_opname_detail';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		
		$data_post = array(
			'do'	=> '',
			'sto_data'	=> array(),
			'sto_detail'	=> array(),
			'report_place_default'	=> '',
			'user_fullname'	=> $user_fullname
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($sto_id)){
			die('Stock Opname Not Found!');
		}else{
			
			$this->db->select("a.*, b.storehouse_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix."storehouse as b","b.id = a.storehouse_id","LEFT");
			$this->db->where("a.id = '".$sto_id."'");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['sto_data'] = $get_dt->row_array();
				
				//get detail
				$this->db->select("a.*, b.item_name, b.item_type, c.unit_code, c.unit_name");
				$this->db->from($this->table2." as a");
				$this->db->join($this->prefix."items as b","b.id = a.item_id","LEFT");
				$this->db->join($this->prefix."unit as c","c.id = a.unit_id","LEFT");
				$this->db->where("a.sto_id = '".$sto_id."'");
				$this->db->order_by("b.item_name", "ASC");
				$get_det = $this->db->get();
				if($get_det->num_rows() > 0){
					$data_post['sto_detail'] = $get_det->result_array();
				}
				
			}else{
				die('Stock Opname Not Found!');
			}
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}
		
		$this->load->view('../../inventory/views/printStockOpname', $data_post);
		
	}
}