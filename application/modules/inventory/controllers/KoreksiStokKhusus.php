<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class KoreksiStokKhusus extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_koreksistokkhusus', 'm');
		$this->load->model('model_koreksistokkhususdetail', 'm2');
		$this->load->model('model_stock', 'stock');
	}
	
	public function gridData()
	{
		$this->table_closing = $this->prefix.'closing';
		
		//generate_status_text
		$sortAlias = array(
			//'closing_status_text' => 'closing_status'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table_closing.' as a',
			'where'			=> array('a.tipe' => 'inventory'),
			'order'			=> array('a.tanggal' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$generate_status = $this->input->post('generate_status');
		$closing_status = $this->input->post('closing_status');
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
				if(empty($date_till)){ $date_till = date('Y-m-t'); }
				
				$mktime_dari = strtotime($date_from);
				$mktime_sampai = strtotime($date_till);
							
				$qdate_from = date("Y-m-d",strtotime($date_from));
				$qdate_till = date("Y-m-d",strtotime($date_till));
				
				$params['where'][] = "(a.tanggal >= '".$qdate_from."' AND a.tanggal <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.tanggal' => 'DESC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.tanggal LIKE '%".$searching."%')";
		}		
		if(!empty($generate_status)){
			$params['where'][] = "a.generate_status = '".$generate_status."'";
		}
		if(!empty($closing_status)){
			$params['where'][] = "a.closing_status = '".$closing_status."'";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		
  		$newData_update = array();		
		$all_id = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				if(!in_array($s['id'], $all_id)){
					$all_id[] = $s['id'];
				}
			}
		}
		
		$data_tanggal = array();
		$stock_rekap_start_date = '';
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['tanggal'] = date("d-m-Y", strtotime($s['tanggal']));
				
				if(!in_array($s['tanggal'], $data_tanggal)){
					$data_tanggal[] = $s['tanggal'];
				}
				
				if(empty($stock_rekap_start_date)){
					$stock_rekap_start_date = $s['tanggal'];
				}
				
				
				if($s['closing_status'] == 1){
					$s['closing_status_text'] = '<span style="color:green;">Yes</span>';
				}else{
					$s['closing_status_text'] = '<span style="color:red;">No</span>';
				}
				
				if($s['generate_status'] == 1){
					$s['generate_status_text'] = '<span style="color:green;">Yes</span>';
				}else{
					$s['generate_status_text'] = '<span style="color:red;">No</span>';
				}
				
				//echo 'tanggal = '.$s['tanggal'].'<br>';
				if(empty($newData_update[$s['tanggal']])){
					$newData_update[$s['tanggal']] = array();
				}
				
				$newData_update[$s['tanggal']] = $s;
				
				//array_push($newData_update, $s);
			}
		}
		
		//echo '<pre>';
		//print_r($newData_update);
		//die();
		
		//if empty check on opt = stock_rekap_start_date
		$opt_value = array(
			'stock_rekap_start_date'
		);
		$get_opt = get_option_value($opt_value);
		
		
		if(!empty($get_opt['stock_rekap_start_date'])){
			$stock_rekap_start_date = $get_opt['stock_rekap_start_date'];
		}
		
		if(empty($stock_rekap_start_date)){
			$stock_rekap_start_date = date("d-m-Y");
		}
		
		$today_date = date("d-m-Y");
		$today_mktime = strtotime($today_date);
		$closing_mktime = strtotime($stock_rekap_start_date);
		$date_from_mktime = strtotime($date_from);
		$date_till_mktime = strtotime($date_till);
		
		if($date_from_mktime <= $closing_mktime){
			$date_from_mktime = $closing_mktime;
		}
		
		$total_day = 0;
		if(!empty($date_from_mktime)){
			$total_day = ($date_till_mktime - $date_from_mktime) / ONE_DAY_UNIX;
		}
		
		/*echo '$get_opt = '.$get_opt['stock_rekap_start_date'].'<br>';
		echo '$closing_mktime = '.$closing_mktime.'<br>';
		echo '$date_from_mktime = '.$date_from_mktime.'<br>';
		echo '$date_till_mktime = '.$date_till_mktime.'<br>';
		echo '$stock_rekap_start_date = '.$stock_rekap_start_date.'<br>';
		echo '$date_from = '.$date_from.'<br>';
		echo '$date_till = '.$date_till.'<br>';
		echo '$total_day = '.$total_day.'<br>';
		die();*/
		
		$newData = array();	
		if(!empty($total_day)){
			for($i=$total_day; $i >= 0; $i--){
				
				$tanggal = date("d-m-Y", ($date_from_mktime + ($i*ONE_DAY_UNIX)));
				
				if(($date_from_mktime + ($i*ONE_DAY_UNIX)) <= $today_mktime){
					
					$dt_push = array(
						'tanggal'	=> $tanggal,
						'closing_status'	=> 0,
						'closing_status_text'	=> '<span style="color:red;">No</span>',
						'generate_status'	=> 0,
						'generate_status_text'	=> '<span style="color:red;">No</span>'
					);
					
					
					if(!in_array($tanggal, $data_tanggal)){
						$data_tanggal[] = $tanggal;
						array_push($newData, $dt_push);
					}else{
						if(!empty($newData_update[$tanggal])){
							
							$dt_push = array(
								'tanggal'	=> $tanggal,
								'closing_status'	=> $newData_update[$tanggal]['closing_status'],
								'closing_status_text'	=> $newData_update[$tanggal]['closing_status_text'],
								'generate_status'	=> $newData_update[$tanggal]['generate_status'],
								'generate_status_text'	=> $newData_update[$tanggal]['generate_status_text']
							);
							
							array_push($newData, $dt_push);
						}
					}
					
				}
				
			}
		}
		
		
		$get_data['data'] = $newData;
		$get_data['totalCount'] = count($newData);
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetail()
	{
		
		$this->table_stock_rekap = $this->prefix.'stock_rekap';
		$this->table_stock_koreksi = $this->prefix.'stock_koreksi';
		$this->table_items = $this->prefix.'items';
		$this->table_unit = $this->prefix.'unit';
		$session_client_id = $this->session->userdata('client_id');	
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		$closing_date = $this->input->post('closing_date');
		if(empty($closing_date)){
			$keterangan = array('id' => '', 'keterangan' => 'Choose Closing Date!', 'total' => '');
			die(json_encode(array('data' => array(0 => $keterangan), 'totalCount' => 1)));
		}
		
		$tanggal = date("Y-m-d", strtotime($closing_date));
		
		$storehouse_id = $this->input->post('storehouse_id');
		if(empty($storehouse_id)){
			$storehouse_id = $this->stock->get_primary_storehouse();
		}
		
		// Default Parameter
		$this->db->select('a.*, b.item_name, b.item_code, b.item_price, b.unit_id, c.unit_name');
		$this->db->from($this->table_stock_rekap.' as a');
		$this->db->join($this->table_items.' as b',"b.id = a.item_id","LEFT");
		$this->db->join($this->table_unit.' as c',"c.id = b.unit_id","LEFT");
		$this->db->where("a.trx_date", $tanggal);
		$this->db->where("a.storehouse_id", $storehouse_id);
		$this->db->where("b.is_deleted = 0");
		$this->db->order_by("b.item_code", "ASC");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			$all_data_detail = array();
			foreach($dt_closing->result_array() as $dt){
				
				$dt['item_hpp_show'] = priceFormat($dt['item_hpp']);
				
				
				$getID = $tanggal.'-'.$storehouse_id.$dt['item_id'];
				$all_data_detail[$getID] = $dt;
				
				$all_data_detail[$getID]['total_stock_awal'] = $dt['total_stock'];
				$all_data_detail[$getID]['total_stock_koreksi'] = 0;
				$all_data_detail[$getID]['total_stock_akhir'] = $dt['total_stock'];
				$all_data_detail[$getID]['keterangan'] = '';
				
				
				if($dt['total_stock'] < 0){
					$dt['item_name'] = '<b style="color:red">'.$dt['item_name'].'</b>';
					$dt['total_stock'] = '<b style="color:red">'.$dt['total_stock'].'</b>';
				}
				
				if($dt['total_stock_kemarin'] < 0){
					$dt['total_stock_kemarin'] = '<b style="color:red">'.$dt['total_stock_kemarin'].'</b>';
				}
				
			}
			//$get_data = array('data' => $all_data_detail, 'totalCount' => count($all_data_detail));
			
		}else{
			$keterangan = array('id' => '', 'item_name' => 'No Data or Not Been Generated!');
			$get_data = array('data' => array( 0 => $keterangan), 'totalCount' => 1);
		}
		
		
		//koreksi table
		$this->db->select('a.*');
		$this->db->from($this->table_stock_koreksi.' as a');
		$this->db->where("a.trx_date", $tanggal);
		$this->db->where("a.storehouse_id", $storehouse_id);
		$dt_koreksi = $this->db->get();
		if($dt_koreksi->num_rows() > 0){
			foreach($dt_koreksi->result_array() as $dt){
				$getID = $tanggal.'-'.$storehouse_id.$dt['item_id'];
				if(!empty($all_data_detail[$getID])){
					
					$old_stok_awal = $all_data_detail[$getID]['total_stock_awal'];
					
					if($dt['total_stock_awal'] == ($all_data_detail[$getID]['total_stock_awal']-$dt['total_stock_koreksi'])){
						//if($tanggal == date("Y-m-d")){
							if($dt['trx_type'] == 'in'){
								$all_data_detail[$getID]['total_stock_awal'] -= $dt['total_stock_koreksi'];
							}else{
								$all_data_detail[$getID]['total_stock_awal'] += ($dt['total_stock_koreksi']*-1);
							}
						//}
					}else{
						if($dt['trx_type'] == 'in'){
							if(($all_data_detail[$getID]['total_stock_awal']-$dt['total_stock_koreksi']) != $dt['total_stock_akhir']){
								$all_data_detail[$getID]['total_stock_awal'] = $old_stok_awal;
							}
						}else{
							if(($all_data_detail[$getID]['total_stock_awal']+$dt['total_stock_koreksi']) != $dt['total_stock_akhir']){
								$all_data_detail[$getID]['total_stock_awal'] = $old_stok_awal;
							}
						}
					}
					
					$all_data_detail[$getID]['total_stock'] = $all_data_detail[$getID]['total_stock_awal'];
					if($all_data_detail[$getID]['total_stock'] < 0){
						$all_data_detail[$getID]['total_stock'] = '<b style="color:red">'.$all_data_detail[$getID]['total_stock'].'</b>';
					}
					
					//$all_data_detail[$getID]['total_stock_awal'] = $dt['total_stock_awal'];
					$all_data_detail[$getID]['total_stock_koreksi'] = $dt['total_stock_koreksi'];
					$all_data_detail[$getID]['total_stock_akhir'] = $dt['total_stock_akhir'];
					$all_data_detail[$getID]['keterangan'] = $dt['keterangan'];
				}
			}
		}
		
		if(!empty($all_data_detail)){
			$new_all_data_detail = array();
			foreach($all_data_detail as $dt){
				$new_all_data_detail[] = $dt;
			}
			
			$get_data = array('data' => $new_all_data_detail, 'totalCount' => count($new_all_data_detail));
		}
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table_stock_koreksi = $this->prefix.'stock_koreksi';				
		$session_user = $this->session->userdata('user_username');
		
		$total_stock_akhir = $this->input->post('total_stock_akhir');
		$total_stock_awal = $this->input->post('total_stock_awal');
		$keterangan = $this->input->post('keterangan');
		$tanggal = $this->input->post('trx_date');
		$storehouse_id = $this->input->post('storehouse_id');
		$item_hpp = $this->input->post('item_hpp');
		$item_id = $this->input->post('item_id');
		$unit_id = $this->input->post('unit_id');
		$is_reset = $this->input->post('is_reset');
		
		if(empty($total_stock_awal)){
			$total_stock_awal = 0;
		}
		if(empty($total_stock_akhir)){
			$total_stock_akhir = 0;
		}
		
		$total_stock_koreksi = $total_stock_akhir - $total_stock_awal;
		
		$trx_type = 'in';
		if($total_stock_koreksi < 0){
			//out
			$trx_type = 'out';
		}
		
		$update_stock = false;
		$rollback_stock = false;
		$ks_id = 0;
		
		$r = '';
		$this->db->select('a.*');
		$this->db->from($this->table_stock_koreksi.' as a');
		$this->db->where("a.trx_date", $tanggal);
		$this->db->where("a.storehouse_id", $storehouse_id);
		$this->db->where("a.item_id", $item_id);
		$dt_koreksi = $this->db->get();
		if($dt_koreksi->num_rows() > 0){
			
			$rollback_stock = true;
		
			$get_dt = $dt_koreksi->row();
			$ks_id = $get_dt->id;
			
			if($is_reset == 0){
				
				$update_stock = true;
				$data_save = array(
					'item_id'  	=> 	$item_id,
					'total_stock_awal'  	=> 	$total_stock_awal,
					'total_stock_akhir'	=>	$total_stock_akhir,
					'total_stock_koreksi'	=>	$total_stock_koreksi,
					'keterangan'	=>	$keterangan,
					'trx_date'	=>	$tanggal,
					'storehouse_id'	=>	$storehouse_id,
					'item_hpp'	=>	$item_hpp,
					'unit_id'	=>	$unit_id,
					'trx_type'	=>	$trx_type,
				);
				
				//UPDATE
				$this->db->update($this->table_stock_koreksi, $data_save, "id = ".$ks_id);
				
			}
			
			
		}else{
			
			$ks_id = 0;
			
			if($is_reset == 0){
				$update_stock = true;
				
				$data_save = array(
					'item_id'  	=> 	$item_id,
					'total_stock_awal'  	=> 	$total_stock_awal,
					'total_stock_akhir'	=>	$total_stock_akhir,
					'total_stock_koreksi'	=>	$total_stock_koreksi,
					'keterangan'	=>	$keterangan,
					'trx_date'	=>	$tanggal,
					'storehouse_id'	=>	$storehouse_id,
					'item_hpp'	=>	$item_hpp,
					'unit_id'	=>	$unit_id,
					'trx_type'	=>	$trx_type,
				);
				
				$this->db->insert($this->table_stock_koreksi, $data_save);
				$insert_id = $this->db->insert_id();
				$ks_id = $insert_id;
				
			}
			
		}
		
		
		$r = array('success' => true, 'id' => $ks_id);
		$get_ks_number = 'KS'.$ks_id;
		
		if($is_reset == 1){
			//RESET
			$this->db->where("item_id", $item_id);
			$this->db->where("trx_date", $tanggal);
			$this->db->where("storehouse_id", $storehouse_id);
			$this->db->delete($this->table_stock_koreksi); 
		}
		
		if($rollback_stock == true){
			
			//DELETE ALL STOCK
			$this->db->where("trx_ref_data", $get_ks_number);
			$this->db->delete($this->prefix."stock"); 
			
		}
		
		if($update_stock == true){
				
			if($total_stock_koreksi >= 0){
				$trx_type = 'in';
				$trx_qty = $total_stock_koreksi;
			}else{
				$trx_type = 'out';
				$trx_qty = $total_stock_koreksi*-1;
			}
			
			$dtInsert_stock = array(
				"item_id" => $item_id,
				"trx_date" => $tanggal,
				"trx_type" => $trx_type,
				"trx_qty" => $trx_qty,
				"unit_id" => $unit_id,
				"storehouse_id" => $storehouse_id,
				"trx_nominal" => $item_hpp,
				"trx_note" => 'Koreksi Stok',
				"trx_ref_data" => $get_ks_number,
				"trx_ref_det_id" => $ks_id,
				"is_sto" => 0
			);
			
			$this->db->insert($this->prefix.'stock', $dtInsert_stock);
						
		}
		
		if($update_stock == true OR $rollback_stock == true){
			
			$storehouse_item = array($storehouse_id => array());
			$storehouse_item[$storehouse_id][] = $item_id;
			
			$post_params = array(
				'storehouse_item'	=> $storehouse_item,
				//'date'	=> $tanggal --> karena update stok otomatis perhari ini saja!
			);
			
			$updateStock = $this->stock->update_stock_rekap($post_params);
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function generate()
	{
		$this->table_closing = $this->prefix.'closing';
		$this->table_stock_rekap = $this->prefix.'stock_rekap';
		$session_client_id = $this->session->userdata('client_id');	
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'User Session Expired, Please Re-Login!');
			die(json_encode($r));
		}
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		$is_check = $this->input->post('is_check');
		$current_total = $this->input->post('current_total');
		$total_storehouse = $this->input->post('total_storehouse');
		$current_total_storehouse = $this->input->post('current_total_storehouse');
		$data_storehouse = $this->input->post('data_storehouse');
		$data_storehouse = json_decode($data_storehouse);
		
		$closing_date = $this->input->post('closing_date');
		$closing_date = json_decode($closing_date);
		$tanggal = array();
		if(is_array($closing_date)){
			$tanggal = $closing_date;
		}else{
			$tanggal[] = $closing_date;
		}
		
		if(!empty($tanggal)){
			$tanggal_new = $tanggal;
			$tanggal = array();
			foreach($tanggal_new as $tgl){
				$tanggal[] = date("Y-m-d", strtotime($tgl));
			}
		}
		
		$tanggal_txt = implode("','", $tanggal);
		
		$updated_closing_date = array();
		//cek is been closing
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'inventory'");
		$this->db->where("a.tanggal IN ('".$tanggal_txt."')");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result() as $dtC){
				if($dtC->closing_status == 1){
					$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
					$r = array('success' => false, 'info'	=> 'Inventory on Date '.$date_txt.' Been Closing!');
					die(json_encode($r));
				}else{
					if(!in_array($dtC->tanggal, $updated_closing_date)){
						$updated_closing_date[] = $dtC->tanggal;
					}
				}
			}
			
		}
		
		//SORTING DATE -> LOW - HIGH
		$dt_tanggal = array();
		foreach($tanggal as $dt){
			$dt_tanggal[strtotime($dt)] = $dt;
		}
		
		ksort($dt_tanggal);
		
		$total_date = count($dt_tanggal);
		if($total_date > 1){
			//$r = array('success' => false, 'info'	=> 'Max Generate is 1 Day!');
			//die(json_encode($r));
		}
		
		//CEK FIRST TO START DATE
		$opt_value = array(
			'stock_rekap_start_date'
		);
		$get_opt = get_option_value($opt_value);
		
		
		if(!empty($get_opt['stock_rekap_start_date'])){
			$stock_rekap_start_date = $get_opt['stock_rekap_start_date'];
			$stock_rekap_start_date = date("Y-m-d", strtotime($stock_rekap_start_date));
		}
		
		if(empty($stock_rekap_start_date)){
			$stock_rekap_start_date = date("Y-m-d");
		}
		
		$today_date = date("Y-m-d");
		$date_current_total = '';
		$date_from = '';
		$date_till = '';
		$i = 0;
		if(!empty($dt_tanggal)){
			foreach($dt_tanggal as $dtT){
				$i++;
				
				if($i == 1){
					$date_from = $dtT;
				}
				
				if(count($dt_tanggal) == $i){
					$date_till = $dtT;
				}
				
				if($i == $current_total){
					$date_current_total = $dtT;
				}
			}
		}
		
		
		
		$this->table_storehouse = $this->prefix.'storehouse';
		$this->db->from($this->table_storehouse);
		$this->db->where("is_active = 1");
		$this->db->where("is_deleted = 0");
		$getStorehouse = $this->db->get();
	
		$all_id_storehouse = array();
		$storehouse_name = array();
		if($getStorehouse->num_rows() > 0){
			foreach($getStorehouse->result() as $dt){
				if(!in_array($dt->id, $all_id_storehouse)){
					$all_id_storehouse[] = $dt->id;
					$storehouse_name[$dt->id] = $dt->storehouse_name;
				}
			}
		}
		
		
		
		$current_storehouse_id = 0;
		$i = 0;
		if(!empty($data_storehouse)){
			foreach($data_storehouse as $idS){
				$i++;
				
				if($i == $current_total_storehouse){
					$current_storehouse_id = $idS;
				}
			}
		}
		
		if(strtotime($date_till) > strtotime($today_date)){
			$r = array('success' => false, 'info'	=> 'Max Generate Date is Today: '.date("d-m-Y").'!');
			die(json_encode($r));
		}
		
		$allowed_generate = false;
		
		//check on db < $date_from
		if($date_from >= $stock_rekap_start_date){
			//allowed
			$allowed_generate = true;
			//echo 'allowed_generate = stock_rekap_start_date<br/>';
		}else{
			$r = array('success' => false, 'info'	=> 'Date Generate/Fix Stock From '.$stock_rekap_start_date.'!');
			die(json_encode($r));
		}
		
		if(!empty($is_check)){
			$r = array('success' => true, 'total_hari'	=> count($dt_tanggal), 'total_storehouse' => count($all_id_storehouse), 'storehouse' => $all_id_storehouse);
			die(json_encode($r));
		}
		
		$storehouse_name_display = '';
		if(empty($current_storehouse_id)){
			
			if(!empty($storehouse_name[$current_total_storehouse])){
				$storehouse_name_display = $storehouse_name[$current_total_storehouse];
			}
			
			$r = array('success' => false, 'info'	=> 'Generate failed on storehouse: '.$storehouse_name_display);
			die(json_encode($r));
		}
		
		if(!empty($storehouse_name[$current_storehouse_id])){
			$storehouse_name_display = $storehouse_name[$current_storehouse_id];
		}
		
		//CURRENT Date
		$date_from = $date_current_total;
		$date_till = $date_current_total;
		
		
		$data_generate = array();
		
		//BEGIN GENERATE STOCK /DAY
		$this->table_items = $this->prefix.'items';
		$this->table_storehouse = $this->prefix.'storehouse';
		$this->table_stock_opname = $this->prefix.'stock_opname';
		$this->table_stock_opname_detail = $this->prefix.'stock_opname_detail';
		$this->table_stock = $this->prefix.'stock';
		$this->table_stock_rekap = $this->prefix.'stock_rekap';
		
		$mktime_dari = strtotime($date_from);
		$mktime_sampai = strtotime($date_till);
		
		$mktime_dari_kemarin = $mktime_dari - ONE_DAY_UNIX;
		$qdate_kemarin = date("Y-m-d",$mktime_dari_kemarin);
		$qdate_from = date("Y-m-d",strtotime($date_from));
		$qdate_till = date("Y-m-d",strtotime($date_till));
		//$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
		$qdate_till_max = date("Y-m-d",strtotime($date_till));
		
		//echo '$qdate_from = '.$qdate_from.'<br/>';
		//echo '$qdate_kemarin = '.$qdate_kemarin.'<br/>';
		
		//ITEM - PER WAREHOUSE
		$item_warehouse = array();
		$this->db->select("a.*");
		$this->db->from($this->table_storehouse." as a");
		$this->db->where("a.is_active = 1");
		$this->db->where("a.is_deleted = 0");
		$this->db->where("a.id = ".$current_storehouse_id);
		$get_storehouse = $this->db->get();
		if($get_storehouse->num_rows() > 0){
			foreach($get_storehouse->result_array() as $dtS){
				if(empty($item_warehouse[$dtS['id']])){
					$item_warehouse[$dtS['id']] = array();
				}
			}
		}
		
		/*GET STOREHOUSE ITEM BY ALL DATA ITEM
		$this->db->select("a.*");
		$this->db->from($this->table_items." as a");
		$this->db->where("a.is_active = 1");
		$this->db->where("a.is_deleted = 0");
		$get_items = $this->db->get();
		if($get_items->num_rows() > 0){
			foreach($get_items->result_array() as $dtI){
				
				foreach($item_warehouse as $dtS => $dtItem){
					if(empty($item_warehouse[$dtS][$dtI['id']])){
						$item_warehouse[$dtS][$dtI['id']] = array(
							'item_id'				=> $dtI['id'],
							'storehouse_id'			=> $dtS,
							'trx_date'				=> $qdate_from,
							'total_stock_kemarin'	=> 0,
							'total_stock_in'		=> 0,
							'total_stock_out'		=> 0,
							'total_stock'			=> 0,
							'item_hpp'				=> $dtI['item_hpp']
						);
					}
				}
				
			}				
		}*/
		
		//GET STOREHOUSE ITEM BY TRX STOCK
		$this->db->select("a.item_id, a.storehouse_id, b.item_hpp");
		$this->db->from($this->table_stock." as a");
		$this->db->join($this->table_items.' as b',"b.id = a.item_id");
		$this->db->where("a.storehouse_id = ".$current_storehouse_id);
		$this->db->where('b.is_deleted = 0');
		$this->db->where('b.is_active = 1');
		$this->db->group_by('a.item_id');
		$this->db->group_by('a.storehouse_id');
		$get_item = $this->db->get();
		if($get_item->num_rows() > 0){
			foreach($get_item->result_array() as $s){
				if(empty($item_warehouse[$s['storehouse_id']][$s['item_id']])){
					$item_warehouse[$s['storehouse_id']][$s['item_id']] = array(
						'item_id'				=> $s['item_id'],
						'storehouse_id'			=> $s['storehouse_id'],
						'trx_date'				=> $qdate_from,
						'total_stock_kemarin'	=> 0,
						'total_stock_in'		=> 0,
						'total_stock_out'		=> 0,
						'total_stock'			=> 0,
						'item_hpp'				=> $s['item_hpp']
					);
				}
			}
		}
		
		//REKAP - KEMARIN
		$dt_rekap_kemarin = array();
		$add_where = "(a.trx_date = '".$qdate_kemarin."')";
		$this->db->select("a.*");
		$this->db->from($this->table_stock_rekap." as a");
		$this->db->where($add_where);
		$this->db->where("a.storehouse_id = ".$current_storehouse_id);
		$get_rekap = $this->db->get();
		if($get_rekap->num_rows() > 0){
			foreach($get_rekap->result_array() as $dtR){
				if(!empty($item_warehouse[$dtR['storehouse_id']][$dtR['item_id']])){
					$item_warehouse[$dtR['storehouse_id']][$dtR['item_id']]['total_stock_kemarin'] = $dtR['total_stock'];
					$item_warehouse[$dtR['storehouse_id']][$dtR['item_id']]['total_stock'] = $dtR['total_stock'];
				}
			}
		}
		
		
		//cek OPNAME -- FIX NO OPNAME MORE THAN 1 /storehouse
		$add_where = "(b.sto_date = '".$qdate_kemarin."')";
		$this->db->select("a.*, b.storehouse_id");
		$this->db->from($this->table_stock_opname_detail." as a");
		$this->db->join($this->table_stock_opname." as b","b.id = a.sto_id","LEFT");
		//$this->db->where("b.storehouse_id > 0");
		$this->db->where("b.storehouse_id = ".$current_storehouse_id);
		$this->db->where("b.sto_status = 'done'");
		$this->db->where($add_where);
		$this->db->order_by("b.sto_date","DESC");
		$get_sto = $this->db->get();
		if($get_sto->num_rows() > 0){
			foreach($get_sto->result_array() as $dtO){
				if(!empty($item_warehouse[$dtO['storehouse_id']][$dtO['item_id']])){
					//echo $dtO['storehouse_id'].' -- '.$dtO['item_id'].' = '.$dtO['jumlah_fisik'].'<br/>';
					$item_warehouse[$dtO['storehouse_id']][$dtO['item_id']]['total_stock_kemarin'] = $dtO['jumlah_fisik'];
					$item_warehouse[$dtO['storehouse_id']][$dtO['item_id']]['total_stock'] = $dtO['jumlah_fisik'];
				}
			}			
		}
		
		//echo '<pre>';
		//print_r($item_warehouse);
		//die();
		
		
		//cek TRX STOK - SELECTED DATE
		$add_where = "(a.trx_date >= '".$qdate_from."' AND a.trx_date <= '".$qdate_till_max."')";
		$this->db->select("a.*");
		$this->db->from($this->table_stock." as a");
		$this->db->where($add_where);
		$this->db->where("a.storehouse_id = ".$current_storehouse_id);
		$this->db->where("a.trx_note != 'Stock Opname'");
		$get_stock = $this->db->get();
		if($get_stock->num_rows() > 0){
			foreach($get_stock->result_array() as $dtS){
				if(!empty($item_warehouse[$dtS['storehouse_id']][$dtS['item_id']])){
					
					if($dtS['trx_type'] == 'in'){
						$item_warehouse[$dtS['storehouse_id']][$dtS['item_id']]['total_stock_in'] += $dtS['trx_qty'];
						$item_warehouse[$dtS['storehouse_id']][$dtS['item_id']]['total_stock'] += $dtS['trx_qty'];
					}
					
					if($dtS['trx_type'] == 'out'){
						$item_warehouse[$dtS['storehouse_id']][$dtS['item_id']]['total_stock_out'] += $dtS['trx_qty'];
						$item_warehouse[$dtS['storehouse_id']][$dtS['item_id']]['total_stock'] -= $dtS['trx_qty'];
					}
					
				}
			}				
		}
		
		
		//OVERRULED STOK
		//STOK OPNAME HARI INI
		$add_where = "(b.sto_date = '".$qdate_from."')";
		$this->db->select("a.*, b.storehouse_id");
		$this->db->from($this->table_stock_opname_detail." as a");
		$this->db->join($this->table_stock_opname." as b","b.id = a.sto_id","LEFT");
		//$this->db->where("b.storehouse_id > 0");
		$this->db->where("b.storehouse_id = ".$current_storehouse_id);
		$this->db->where("b.sto_status = 'done'");
		$this->db->where($add_where);
		$this->db->order_by("b.sto_date","DESC");
		$get_sto = $this->db->get();
		if($get_sto->num_rows() > 0){
			foreach($get_sto->result_array() as $dtO){
				if(!empty($item_warehouse[$dtO['storehouse_id']][$dtO['item_id']])){
					//echo $dtO['storehouse_id'].' -- '.$dtO['item_id'].' = '.$dtO['jumlah_fisik'].'<br/>';
					//$item_warehouse[$dtO['storehouse_id']][$dtO['item_id']]['total_stock_kemarin'] = $dtO['jumlah_fisik'];
					$selisih_in = $dtO['jumlah_fisik'] - $item_warehouse[$dtO['storehouse_id']][$dtO['item_id']]['total_stock_kemarin'] + $item_warehouse[$dtO['storehouse_id']][$dtO['item_id']]['total_stock_in'] - $item_warehouse[$dtO['storehouse_id']][$dtO['item_id']]['total_stock_out'];
					$selisih_out = $item_warehouse[$dtO['storehouse_id']][$dtO['item_id']]['total_stock_out'];
					if($dtO['jumlah_fisik'] < $selisih_in){
						$selisih_out = $selisih_in - $dtO['jumlah_fisik'];
					}
					
					$item_warehouse[$dtO['storehouse_id']][$dtO['item_id']]['total_stock_in'] = $selisih_in;
					$item_warehouse[$dtO['storehouse_id']][$dtO['item_id']]['total_stock_out'] = $selisih_out;
					$item_warehouse[$dtO['storehouse_id']][$dtO['item_id']]['total_stock'] = $dtO['jumlah_fisik'];
				}
			}			
		}
		
		
		$insert_stock_rekap = array();
		if(!empty($item_warehouse)){
			foreach($item_warehouse as $storehouse_id => $dtItem){
				
				foreach($dtItem as $itemId => $dt){
					
					//$dt['created'] = date('Y-m-d H:i:s');
					//$dt['createdby'] = $session_user;
					//$dt['updated'] = date('Y-m-d H:i:s');
					//$dt['updatedby'] = $session_user;
					
					$insert_stock_rekap[] = $dt;
				}
				
			}
		}	
		
		
		//echo '<pre>';
		//print_r($insert_stock_rekap);
		//die();
		
		if(!empty($insert_stock_rekap)){
			//remove if available
			$this->db->delete($this->table_stock_rekap, "trx_date IN ('".$qdate_from."') and storehouse_id = ".$current_storehouse_id);
			$this->db->insert_batch($this->table_stock_rekap, $insert_stock_rekap);
		}
		
		
		if($date_from == $date_till){
			$r = array('success' => true, 'info'	=> 'Warehouse: '.$storehouse_name_display.', Stok on Date '.$date_from.' Been Generated!', 'curr_date' => $date_from);
			die(json_encode($r));
		}
		
		$r = array('success' => true, 'info'	=> 'Warehouse: '.$storehouse_name_display.', Stok on Date From '.$date_from.' ~ '.$date_till.' Been Generated!', 'curr_date' => $date_from);
		die(json_encode($r));
				
	}
	
}