<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ClosingInventory extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_closinginventory', 'm');
		$this->load->model('model_closinginventorydetail', 'm2');
	}

	public function gridData()
	{
		$this->table_closing = $this->prefix.'closing';
		$this->table_closing_inventory_detail = $this->prefix.'closing_inventory_detail';
		
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
		$closing_inventory_start_date = '';
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['tanggal'] = date("d-m-Y", strtotime($s['tanggal']));
				
				if(!in_array($s['tanggal'], $data_tanggal)){
					$data_tanggal[] = $s['tanggal'];
				}
				
				if(empty($closing_inventory_start_date)){
					$closing_inventory_start_date = $s['tanggal'];
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
		
		//if empty check on opt = closing_inventory_start_date
		$opt_value = array(
			'closing_inventory_start_date'
		);
		$get_opt = get_option_value($opt_value);
		
		
		if(!empty($get_opt['closing_inventory_start_date'])){
			$closing_inventory_start_date = $get_opt['closing_inventory_start_date'];
		}
		
		if(empty($closing_inventory_start_date)){
			$closing_inventory_start_date = date("d-m-Y");
		}
		
		$today_date = date("d-m-Y");
		$today_mktime = strtotime($today_date);
		$closing_mktime = strtotime($closing_inventory_start_date);
		$date_from_mktime = strtotime($date_from);
		$date_till_mktime = strtotime($date_till);
		
		if($date_from_mktime <= $closing_mktime){
			$date_from_mktime = $closing_mktime;
		}
		
		$total_day = 0;
		if(!empty($date_from_mktime)){
			$total_day = ($date_till_mktime - $date_from_mktime) / ONE_DAY_UNIX;
		}
		
		/*echo '$get_opt = '.$get_opt['closing_inventory_start_date'].'<br>';
		echo '$closing_mktime = '.$closing_mktime.'<br>';
		echo '$date_from_mktime = '.$date_from_mktime.'<br>';
		echo '$date_till_mktime = '.$date_till_mktime.'<br>';
		echo '$closing_inventory_start_date = '.$closing_inventory_start_date.'<br>';
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
		
		$this->table_closing_inventory = $this->prefix.'closing_inventory';
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
		
		// Default Parameter
		$this->db->from($this->table_closing_inventory.' as a');
		$this->db->where("tanggal", $tanggal);
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			$data = $dt_closing->row();
			$all_data_detail = array();
			
			$data_detail = array(
				'Closing Inventory Date' => date("d-m-Y", strtotime($data->tanggal)),
				'Total Item'			=> $data->inventory_item, 
				'Total In'				=> $data->inventory_in_qty, 
				'Total HPP In'			=> $data->inventory_in_hpp, 
				'Total Out'				=> $data->inventory_out_qty, 
				'Total HPP Out'			=> $data->inventory_out_hpp, 
				'Total Stok'			=> $data->inventory_stok, 
				'Total HPP Stok'		=> priceFormat($data->inventory_hpp), 
				'&nbsp;'				=> '&nbsp;',
				'<b>Receiving (In):<b/>'=> '&nbsp;', 
				'Receiving Total'			=> $data->receiving_total, 
				'Receiving Item'			=> $data->receiving_item_total, 
				'Receiving Qty Item'		=> $data->receiving_item_qty, 
				'Receiving HPP Item'		=> priceFormat($data->receiving_item_hpp), 
				'&nbsp; '				=> '&nbsp;', 
				'<b>Usage (Out):<b/>'	=> '&nbsp;', 
				'Usage Total'			=> $data->usage_total, 
				'Usage Item'			=> $data->usage_item_total, 
				'Usage Qty Item'		=> $data->usage_item_qty, 
				'Usage HPP Item'		=> priceFormat($data->usage_item_hpp),  
				'&nbsp;  '				=> '&nbsp;', 
				'<b>Waste:<b/>'			=> '&nbsp;', 
				'Waste Total'			=> $data->waste_total, 
				'Waste Item'			=> $data->waste_item_total, 
				'Waste Qty Item'		=> $data->waste_item_qty, 
				'Waste HPP Item'		=> priceFormat($data->waste_item_hpp),  
				'Persentase'			=> $data->waste_persentase, 
				'&nbsp;   '				=> '&nbsp;',
				'<b>MUTASI:<b/>'		=> '&nbsp;', 
				'Mutasi Total'			=> $data->mutasi_total, 
				'Mutasi Item'			=> $data->mutasi_item_total, 
				'Mutasi Qty Item'		=> $data->mutasi_item_qty, 
				'Mutasi HPP Item'		=> priceFormat($data->mutasi_item_hpp)
			);
			
			$no = 0;
			foreach($data_detail as $ket => $val){
				$no++;
				$all_data_detail [] = array(
					'id'			=> $no,
					'keterangan'	=> $ket,
					'total'			=> $val
				);
			}
			
			$get_data = array('data' => $all_data_detail, 'totalCount' => count($all_data_detail));
		}else{
			$keterangan = array('id' => '', 'keterangan' => 'No Data or Not Been Generated!', 'total' => '');
			$get_data = array('data' => array( 0 => $keterangan), 'totalCount' => 1);
		}
		  		
      	die(json_encode($get_data));
	}
	
	public function generate()
	{
		$this->table_closing = $this->prefix.'closing';
		$this->table_closing_inventory = $this->prefix.'closing_inventory';
		$this->table_production_detail = $this->prefix.'production_detail';
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
		
		/*$updated_closing_date = array();
		//cek is been closing
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'inventory'");
		$this->db->where("a.tanggal IN ('".$tanggal_txt."')");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result() as $dtC){
				if($dtC->closing_status == 1){
					$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
					$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' Been Closing!');
					die(json_encode($r));
				}else{
					if(!in_array($dtC->tanggal, $updated_closing_date)){
						$updated_closing_date[] = $dtC->tanggal;
					}
				}
			}
			
		}*/
		
		//SORTING DATE -> LOW - HIGH
		$dt_tanggal = array();
		foreach($tanggal as $dt){
			$dt_tanggal[strtotime($dt)] = $dt;
		}
		
		ksort($dt_tanggal);
		
		$total_date = count($dt_tanggal);
		if($total_date > 31){
			$r = array('success' => false, 'info'	=> 'Max Generate is 31 Days!');
			die(json_encode($r));
		}
		
		//CEK FIRST TO START DATE
		$opt_value = array(
			'closing_inventory_start_date'
		);
		$get_opt = get_option_value($opt_value);
		
		
		if(!empty($get_opt['closing_inventory_start_date'])){
			$closing_inventory_start_date = $get_opt['closing_inventory_start_date'];
			$closing_inventory_start_date = date("Y-m-d", strtotime($closing_inventory_start_date));
		}
		
		if(empty($closing_inventory_start_date)){
			$closing_inventory_start_date = date("Y-m-d");
		}
		
		$today_date = date("Y-m-d");
		$date_current_total = '';
		$date_from = '';
		$date_till = '';
		$i = 0;
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
		
		
		$updated_closing_date = array();
		//cek is been closing
		if(!empty($date_current_total)){
			$this->db->from($this->table_closing.' as a');
			$this->db->where("a.tipe = 'inventory'");
			$this->db->where("a.tanggal IN ('".$date_current_total."')");
			$dt_closing = $this->db->get();
			if($dt_closing->num_rows() > 0){
				foreach($dt_closing->result() as $dtC){
					if($dtC->closing_status == 1){
						$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
						$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' Been Closing!');
						die(json_encode($r));
					}else{
						if(!in_array($dtC->tanggal, $updated_closing_date)){
							$updated_closing_date[] = $dtC->tanggal;
						}
					}
				}
				
			}
		}
		
		if(strtotime($date_till) > strtotime($today_date)){
			$r = array('success' => false, 'info'	=> 'Max Generate Date is Today: '.date("d-m-Y").'!');
			die(json_encode($r));
		}
		
		$allowed_generate = false;
		
		//check on db < $date_from
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'inventory'");
		$this->db->where("a.tanggal < '".$date_from."' AND a.generate_status = 0");
		$this->db->order_by("a.tanggal", "DESC");
		$this->db->limit(1);
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			
			$dtC = $dt_closing->row();
			$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
			//echo $dtC->tanggal.' == '.$closing_inventory_start_date.'<br/>';
			
			if(strtotime($dtC->tanggal) < strtotime($closing_inventory_start_date)){
				$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' or < from date '.date("d-m-Y", strtotime($date_from)).' Not Been Generated!');
				die(json_encode($r));
			}
			
			
			
		}else{
			
			if($date_from >= $closing_inventory_start_date){
				//allowed
				$allowed_generate = true;
				//echo 'allowed_generate = closing_inventory_start_date<br/>';
			}else{
				$r = array('success' => false, 'info'	=> 'Date Closing From '.$closing_inventory_start_date.'!');
				die(json_encode($r));
			}
			
			//$allowed_generate = true;
			//echo 'allowed_generate -> ON DB > '.$date_from.'br/>';
		}
		
		
		if(!empty($is_check)){
			$r = array('success' => true, 'total_hari'	=> count($dt_tanggal));
			die(json_encode($r));
		}
		
		//CURRENT Date
		$date_from = $date_current_total;
		$date_till = $date_current_total;
		
		
		$data_generate = array();
		
		//BEGIN GENERATE ---> FROM REPORT PO & RECEIVING
		$this->table_stock = $this->prefix.'stock';
		$this->table_production = $this->prefix.'production';
		$this->table_stock_rekap = $this->prefix.'stock_rekap';
		
		$mktime_dari = strtotime($date_from);
		$mktime_sampai = strtotime($date_till);
					
		$qdate_from = date("Y-m-d",strtotime($date_from));
		$qdate_till = date("Y-m-d",strtotime($date_till));
		//$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
		$qdate_till_max = date("Y-m-d",strtotime($date_till));
		
		$add_where = "(a.trx_date >= '".$qdate_from."' AND a.trx_date <= '".$qdate_till_max."')";
		
		$this->db->select("a.*");
		$this->db->from($this->table_stock." as a");
		$this->db->where($add_where);

		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			$data_po = $get_dt->result_array();				
		}
		
		$all_group_date = array();		  
		$all_inventory_item = array();	
		$all_receiving_total = array();	
		$all_receiving_item = array();	
		$all_usage_total = array();	
		$all_usage_item = array();	
		$all_usage_item_hpp = array();	
		$all_mutasi_total = array();	
		$all_mutasi_item = array();	
		$all_stock_id = array();	
		$all_stock_id_date = array();	
		$all_production_no = array();	
		$no_id = 1;
		if(!empty($data_po)){
			foreach ($data_po as $s){
				
				//REKAP TGL
				$trx_date = date("Y-m-d",strtotime($s['trx_date']));
				if($date_current_total == $trx_date){
					if(empty($all_group_date[$trx_date])){
						$all_group_date[$trx_date] = array(
							'tanggal'				=> $trx_date, 
							'inventory_item'		=> 0, 
							'inventory_in_qty'		=> 0, 
							'inventory_in_hpp'		=> 0, 
							'inventory_out_qty'		=> 0, 
							'inventory_out_hpp'		=> 0, 
							'inventory_stok'		=> 0, 
							'inventory_hpp'			=> 0, 
							'receiving_total'		=> 0, 
							'receiving_item_total'	=> 0, 
							'receiving_item_qty'	=> 0, 
							'receiving_item_hpp'	=> 0, 
							'usage_total'			=> 0, 
							'usage_item_total'		=> 0, 
							'usage_item_qty'		=> 0, 
							'usage_item_hpp'		=> 0, 
							'waste_total'			=> 0, 
							'waste_item_total'		=> 0, 
							'waste_item_qty'		=> 0, 
							'waste_item_hpp'		=> 0, 
							'waste_persentase'		=> 0, 
							'mutasi_total'			=> 0, 
							'mutasi_item_total'		=> 0, 
							'mutasi_item_qty'		=> 0, 
							'mutasi_item_hpp'		=> 0
						);
						
						$no_id++;
					}
					
					
					$all_stock_id_date[$s['id']] = $trx_date;
					
					if(!in_array($s['id'], $all_stock_id)){
						$all_stock_id[] = $s['id'];
					}
					
					if(!empty($s['item_id'])){
						if(!in_array($s['item_id'], $all_inventory_item)){
							$all_inventory_item[] = $s['item_id'];
							$all_group_date[$trx_date]['inventory_item'] += 1;
						}
					}
					
					if($s['trx_type'] == 'in'){
						$all_group_date[$trx_date]['inventory_in_qty'] += $s['trx_qty'];
						$all_group_date[$trx_date]['inventory_in_hpp'] += ($s['trx_qty'] * $s['trx_nominal']);
					}
					if($s['trx_type'] == 'out'){
						$all_group_date[$trx_date]['inventory_out_qty'] += $s['trx_qty'];
						$all_group_date[$trx_date]['inventory_out_hpp'] += ($s['trx_qty'] * $s['trx_nominal']);
					}
					
					if($s['trx_note'] == 'Receiving'){
						
						if(!empty($s['trx_ref_data'])){
							if(!in_array($s['trx_ref_data'], $all_receiving_total)){
								$all_receiving_total[] = $s['trx_ref_data'];
								$all_group_date[$trx_date]['receiving_total'] += 1;
							}
						}
						
						if(!empty($s['item_id'])){
							if(!in_array($s['item_id'], $all_receiving_item)){
								$all_receiving_item[] = $s['item_id'];
								$all_group_date[$trx_date]['receiving_item_total'] += 1;
							}
						}
						
						$all_group_date[$trx_date]['receiving_item_qty'] += $s['trx_qty'];
						$all_group_date[$trx_date]['receiving_item_hpp'] += ($s['trx_qty'] * $s['trx_nominal']);
					}
					
					if($s['trx_note'] == 'Production'){
						
						if(!empty($s['trx_ref_data'])){
							if(!in_array($s['trx_ref_data'], $all_usage_total)){
								$all_usage_total[] = $s['trx_ref_data'];
								$all_group_date[$trx_date]['usage_total'] += 1;
							}
						}
						
						if(!empty($s['item_id'])){
							if(!in_array($s['item_id'], $all_usage_item)){
								$all_usage_item[] = $s['item_id'];
								$all_group_date[$trx_date]['usage_item_total'] += 1;
							}
						}
						
						$all_group_date[$trx_date]['usage_item_qty'] += $s['trx_qty'];
						$all_group_date[$trx_date]['usage_item_hpp'] += ($s['trx_qty'] * $s['trx_nominal']);
						
						if(!in_array($s['trx_ref_data'], $all_production_no)){
							$all_production_no[] = $s['trx_ref_data'];
						}
						
						if(!empty($all_usage_item_hpp[$s['trx_ref_data']])){
							$all_usage_item_hpp[$s['trx_ref_data']] = array();
						}
						
						if(empty($all_usage_item_hpp[$s['trx_ref_data']][$s['item_id']])){
							$all_usage_item_hpp[$s['trx_ref_data']][$s['item_id']] = 0;
						}
						
						if(!empty($s['trx_nominal'])){
							$all_usage_item_hpp[$s['trx_ref_data']][$s['item_id']] = $s['trx_nominal'];
						}
						
					}
					
					if($s['trx_note'] == 'Distribution' AND $s['trx_type'] == 'in'){
						
						if(!empty($s['trx_ref_data'])){
							if(!in_array($s['trx_ref_data'], $all_mutasi_total)){
								$all_mutasi_total[] = $s['trx_ref_data'];
								$all_group_date[$trx_date]['mutasi_total'] += 1;
							}
						}
						
						if(!empty($s['item_id'])){
							if(!in_array($s['item_id'], $all_mutasi_item)){
								$all_mutasi_item[] = $s['item_id'];
								$all_group_date[$trx_date]['mutasi_item_total'] += 1;
							}
						}
						
						$all_group_date[$trx_date]['mutasi_item_qty'] += $s['trx_qty'];
						$all_group_date[$trx_date]['mutasi_item_hpp'] += ($s['trx_qty'] * $s['trx_nominal']);
						
					}
				}
				
			}
		}
		
		//table_production -> waste
		$all_waste_total = array();	
		$all_waste_item = array();	
		if(!empty($all_production_no)){
			$all_production_no_txt = implode(",",$all_production_no);
			$this->db->select('a.*, b.pr_number');
			$this->db->from($this->table_production_detail.' as a');
			$this->db->join($this->table_production.' as b',"b.id = a.pr_id","LEFT");
			$this->db->where("b.pr_number IN ('".$all_production_no_txt."')");
			$this->db->where('a.prd_waste > 0');
			
			$get_detail = $this->db->get();
			if($get_detail->num_rows() > 0){
				foreach($get_detail->result() as $dtRow){
		
					if(!empty($s['pr_id'])){
						if(!in_array($s['pr_id'], $all_waste_total)){
							$all_waste_total[] = $s['pr_id'];
							$all_group_date[$trx_date]['waste_total'] += 1;
						}
					}
					
					if(!empty($s['item_id'])){
						if(!in_array($s['item_id'], $all_waste_item)){
							$all_waste_item[] = $s['item_id'];
							$all_group_date[$trx_date]['waste_item_total'] += 1;
						}
					}
					
					$item_hpp = 0;
					if(!empty($all_usage_item_hpp[$s['pr_number']][$s['item_id']])){
						$item_hpp = $all_usage_item_hpp[$s['pr_number']][$s['item_id']];
					}
					
					$all_group_date[$trx_date]['waste_item_qty'] += $s['prd_waste'];
					$all_group_date[$trx_date]['waste_item_hpp'] += ($s['prd_waste'] * $item_hpp);
					
					//PERSENTASE
					$persentase = ($s['prd_used'] / $s['prd_waste']) * 100;
					if($persentase > 100){
						$persentase = 100;
					}
					
					$all_group_date[$trx_date]['waste_item_hpp'] = ($all_group_date[$trx_date]['waste_item_hpp'] + $persentase) / 2;
					
				}
			}
		}
		
		//TOTAl INVNTORY : inventory_stok, inventory_hpp
		$add_where = "(a.trx_date >= '".$qdate_from."' AND a.trx_date <= '".$qdate_till_max."')";
		$this->db->select("a.*");
		$this->db->from($this->table_stock_rekap." as a");
		$this->db->where($add_where);

		$get_receive = $this->db->get();
		if($get_receive->num_rows() > 0){
			foreach($get_receive->result() as $dtR){
				
				$trx_date = date("Y-m-d", strtotime($dtR->trx_date));
				
				if($date_current_total == $trx_date){
					if(empty($all_group_date[$trx_date])){
						$all_group_date[$trx_date] = array(
							'tanggal'				=> $trx_date, 
							'inventory_item'		=> 0, 
							'inventory_in_qty'		=> 0, 
							'inventory_in_hpp'		=> 0, 
							'inventory_out_qty'		=> 0, 
							'inventory_out_hpp'		=> 0, 
							'inventory_stok'		=> 0, 
							'inventory_hpp'			=> 0, 
							'receiving_total'		=> 0, 
							'receiving_item_total'	=> 0, 
							'receiving_item_qty'	=> 0, 
							'receiving_item_hpp'	=> 0, 
							'usage_total'			=> 0, 
							'usage_item_total'		=> 0, 
							'usage_item_qty'		=> 0, 
							'usage_item_hpp'		=> 0, 
							'waste_total'			=> 0, 
							'waste_item_total'		=> 0, 
							'waste_item_qty'		=> 0, 
							'waste_item_hpp'		=> 0, 
							'waste_persentase'		=> 0, 
							'mutasi_total'			=> 0, 
							'mutasi_item_total'		=> 0, 
							'mutasi_item_qty'		=> 0, 
							'mutasi_item_hpp'		=> 0
						);
						
					}
					
					if(!empty($all_group_date[$trx_date])){
						
						$inventory_stok = $dtR->total_stock_kemarin;
						$inventory_stok += $dtR->total_stock_in;
						$inventory_stok -= $dtR->total_stock_out;
						
						if($inventory_stok < 0){
							$inventory_stok = 0;
						}
						
						$all_group_date[$trx_date]['inventory_stok'] += $inventory_stok;
						$all_group_date[$trx_date]['inventory_stok'] += ($inventory_stok * $dtR->item_hpp);
						
					}
				}
			
			}		
		}
		
		
		
		//check empty date
		if(!empty($dt_tanggal)){
			foreach($dt_tanggal as $key => $val){
				if($date_current_total == $val){
					if(empty($all_group_date[$val])){
						
						//echo 'EMPTY: '.$val.'<br/>';
						
						$all_group_date[$val] = array(
							'tanggal'				=> $val, 
							'inventory_item'		=> 0, 
							'inventory_in_qty'		=> 0, 
							'inventory_in_hpp'		=> 0, 
							'inventory_out_qty'		=> 0, 
							'inventory_out_hpp'		=> 0, 
							'inventory_stok'		=> 0, 
							'inventory_hpp'			=> 0, 
							'receiving_total'		=> 0, 
							'receiving_item_total'	=> 0, 
							'receiving_item_qty'	=> 0, 
							'receiving_item_hpp'	=> 0, 
							'usage_total'			=> 0, 
							'usage_item_total'		=> 0, 
							'usage_item_qty'		=> 0, 
							'usage_item_hpp'		=> 0, 
							'waste_total'			=> 0, 
							'waste_item_total'		=> 0, 
							'waste_item_qty'		=> 0, 
							'waste_item_hpp'		=> 0, 
							'waste_persentase'		=> 0, 
							'mutasi_total'			=> 0, 
							'mutasi_item_total'		=> 0, 
							'mutasi_item_qty'		=> 0, 
							'mutasi_item_hpp'		=> 0
						);
						
					}
				}
			}
		}
		
		$newData = array();
		if(!empty($all_group_date)){
			foreach($all_group_date as $key => $detail){
				$newData[$key] = $detail;
				
			}
		}	
		
		
		//echo '<pre>';
		//print_r($newData);
		//die();
		
		$insert_date = array();
		$insert_closing_inventory = array();
		$updated_closing_inventory = array();
		$insert_closing = array();
		$updated_closing = array();
		if(!empty($newData)){
			foreach($newData as $dt){
				
				if($date_current_total == $dt['tanggal']){
					if(in_array($dt['tanggal'], $updated_closing_date)){
						
						$dt['updated'] = date('Y-m-d H:i:s');
						$dt['updatedby'] = $session_user;
						$updated_closing_inventory[] = $dt;
						
						$bulan = date("m", strtotime($dt['tanggal']));
						$tahun = date("Y", strtotime($dt['tanggal']));
						
						$updated_closing[] = array(
							'tanggal'	=> $dt['tanggal'],
							'bulan'	=> $bulan,
							'tahun'	=> $tahun,
							'tipe'	=> 'inventory',
							'closing_status'	=> 0,
							'generate_status'	=> 1,
							'updated'		=>	date('Y-m-d H:i:s'),
							'updatedby'		=>	$session_user,
						);
							
					}else{
						
						$dt['created'] = date('Y-m-d H:i:s');
						$dt['createdby'] = $session_user;
						$dt['updated'] = date('Y-m-d H:i:s');
						$dt['updatedby'] = $session_user;
						$insert_closing_inventory[] = $dt;
						
						if(!in_array($dt['tanggal'], $insert_date)){
							$insert_date[] = $dt['tanggal'];
							
							$bulan = date("m", strtotime($dt['tanggal']));
							$tahun = date("Y", strtotime($dt['tanggal']));
							
							$insert_closing[] = array(
								'tanggal'	=> $dt['tanggal'],
								'bulan'	=> $bulan,
								'tahun'	=> $tahun,
								'tipe'	=> 'inventory',
								'closing_status'	=> 0,
								'generate_status'	=> 1,
								'created'		=>	date('Y-m-d H:i:s'),
								'createdby'		=>	$session_user,
								'updated'		=>	date('Y-m-d H:i:s'),
								'updatedby'		=>	$session_user
							);
						}
						
					}
				}
				
			}
		}
		
		//echo '<pre>';
		//print_r($insert_closing_inventory);
		//die();
		
		if(!empty($insert_closing)){
			//remove if available
			if(!empty($insert_date)){
				$insert_date_txt = implode("','", $insert_date);
				$this->db->delete($this->table_closing, "tanggal IN ('".$insert_date_txt."') AND tipe = 'inventory'");
			}
			
			$this->db->insert_batch($this->table_closing, $insert_closing);
		}
		
		if(!empty($insert_closing_inventory)){
			//remove if available
			if(!empty($insert_date)){
				$insert_date_txt = implode("','", $insert_date);
				$this->db->delete($this->table_closing_inventory, "tanggal IN ('".$insert_date_txt."')");
			}
			
			$this->db->insert_batch($this->table_closing_inventory, $insert_closing_inventory);
		}
		
		if(!empty($updated_closing)){
			//REMOVE OLD
			$updated_closing_date_txt = implode("','", $updated_closing_date);
			$this->db->delete($this->table_closing,"tanggal IN ('".$updated_closing_date_txt."') AND tipe = 'inventory'");
			$this->db->insert_batch($this->table_closing, $updated_closing);
			//$this->db->update_batch($this->table_closing, $updated_closing, 'tanggal');
		}
		
		if(!empty($updated_closing_inventory)){
			//REMOVE OLD
			$updated_closing_date_txt = implode("','", $updated_closing_date);
			$this->db->delete($this->table_closing_inventory,"tanggal IN ('".$updated_closing_date_txt."')");
			$this->db->insert_batch($this->table_closing_inventory, $updated_closing_inventory);
			//$this->db->update_batch($this->table_closing_inventory, $updated_closing_inventory, 'tanggal');
		}
		
		if($date_from == $date_till){
			$r = array('success' => true, 'info'	=> 'Date Closing '.$date_from.' Been Generated!');
			die(json_encode($r));
		}
		
		$r = array('success' => true, 'info'	=> 'Date Closing Generated From '.$date_from.' ~ '.$date_till.'!');
		die(json_encode($r));
				
	}
	
	public function closingDate()
	{
		$this->table_closing = $this->prefix.'closing';
		$this->table_receiving = $this->prefix.'receiving';
		$this->table_distribution = $this->prefix.'distribution';
		$this->table_production = $this->prefix.'production';
		$this->table_closing_inventory = $this->prefix.'closing_inventory';
		$session_client_id = $this->session->userdata('client_id');	
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'User Session Expired, Please Re-Login!');
			die(json_encode($r));
		}
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		$from_autoclosing = $this->input->post('from_autoclosing');
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
		//cek is been generated
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'inventory'");
		$this->db->where("a.tanggal IN ('".$tanggal_txt."')");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result() as $dtC){
				if($dtC->generate_status == 0){
					$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
					$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' Should Generated First!');
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
		if($total_date > 31){
			$r = array('success' => false, 'info'	=> 'Max Closing is 31 Days!');
			die(json_encode($r));
		}
		
		//CEK FIRST TO START DATE
		$opt_value = array(
			'closing_inventory_start_date'
		);
		$get_opt = get_option_value($opt_value);
		
		
		if(!empty($get_opt['closing_inventory_start_date'])){
			$closing_inventory_start_date = $get_opt['closing_inventory_start_date'];
			$closing_inventory_start_date = date("Y-m-d", strtotime($closing_inventory_start_date));
		}
		
		if(empty($closing_inventory_start_date)){
			$closing_inventory_start_date = date("Y-m-d");
		}
		
		$today_date = date("Y-m-d");
		$date_from = '';
		$date_till = '';
		$i = 0;
		foreach($dt_tanggal as $dtT){
			$i++;
			
			if($i == 1){
				$date_from = $dtT;
			}
			
			if(count($dt_tanggal) == $i){
				$date_till = $dtT;
			}
		}
		
		if(strtotime($date_till) > strtotime($today_date)){
			$r = array('success' => false, 'info'	=> 'Max Closing Date is Today: '.date("d-m-Y").'!');
			die(json_encode($r));
		}
		
		
		//cek is progress receiving
		$is_available_receiving_id = array();
		$is_available_receiving = array();
		$this->db->select("a.*");
		$this->db->from($this->table_receiving.' as a');
		$this->db->where("a.receive_status IN ('progress')");
		$this->db->where("a.receive_date >= '".$date_from."' AND a.receive_date <= '".$date_till."'");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result() as $dtB){
				$tanggal_receiving = date("d-m-Y", strtotime($dtB->receive_date));
				if(!in_array($tanggal_receiving, $is_available_receiving)){
					$is_available_receiving[] = $tanggal_receiving;
				}
				
				if(!in_array($dtB->id, $is_available_receiving_id)){
					$is_available_receiving_id[] = $dtB->id;
				}
			}
			
		}
		
		
		
		//Auto Cancel From Auto Closing
		$autoclosing_auto_cancel_receiving = 0;
		if(!empty($get_opt['autoclosing_auto_cancel_receiving'])){
			$autoclosing_auto_cancel_receiving = $get_opt['autoclosing_auto_cancel_receiving'];
		}
		
		if(!empty($from_autoclosing)){
			if(!empty($autoclosing_auto_cancel_receiving)){
				
				$is_available_receiving = array();
				
				//AUTO CANCEL BILLING
				if(!empty($is_available_receiving_id)){
					$is_available_receiving_id_sql = implode(",", $is_available_receiving_id);
					
					$dt_auto_cancel = array(
						'receive_status' => 'cancel',
						'receive_memo' => 'Auto Cancel From Auto Closing'
					);
					
					$this->db->update($this->table_receiving, $dt_auto_cancel, "id IN (".$is_available_receiving_id_sql.")");
					
				}
				
			}
		}
		
		if(!empty($is_available_receiving)){
			$is_available_receiving_txt = implode(", ", $is_available_receiving);
			$r = array('success' => false, 'info'	=> 'Please Set Receiving with status Progress to Done/Cancel!<br/>on Date: '.$is_available_receiving_txt);
			die(json_encode($r));
		}
		
		//cek is progress distribution
		$is_available_distribution_id = array();
		$is_available_distribution = array();
		$this->db->select("a.*");
		$this->db->from($this->table_distribution.' as a');
		$this->db->where("a.dis_status IN ('progress')");
		$this->db->where("a.dis_date >= '".$date_from."' AND a.dis_date <= '".$date_till."'");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result() as $dtB){
				$tanggal_distribution = date("d-m-Y", strtotime($dtB->dis_date));
				if(!in_array($tanggal_distribution, $is_available_distribution)){
					$is_available_distribution[] = $tanggal_distribution;
				}
				
				if(!in_array($dtB->id, $is_available_distribution_id)){
					$is_available_distribution_id[] = $dtB->id;
				}
			}
			
		}
		
		//Auto Cancel From Auto Closing
		$autoclosing_auto_cancel_distribution = 0;
		if(!empty($get_opt['autoclosing_auto_cancel_distribution'])){
			$autoclosing_auto_cancel_distribution = $get_opt['autoclosing_auto_cancel_distribution'];
		}
		
		if(!empty($from_autoclosing)){
			if(!empty($autoclosing_auto_cancel_distribution)){
				
				$is_available_distribution = array();
				
				//AUTO CANCEL BILLING
				if(!empty($is_available_distribution_id)){
					$is_available_distribution_id_sql = implode(",", $is_available_distribution_id);
					
					$dt_auto_cancel = array(
						'dis_status' => 'cancel',
						'dis_memo' => 'Auto Cancel From Auto Closing'
					);
					
					$this->db->update($this->table_distribution, $dt_auto_cancel, "id IN (".$is_available_distribution_id_sql.")");
					
				}
				
			}
		}
		
		if(!empty($is_available_distribution)){
			$is_available_distribution_txt = implode(", ", $is_available_distribution);
			$r = array('success' => false, 'info'	=> 'Please Set Distribution with status Progress to Done/Cancel!<br/>on Date: '.$is_available_distribution_txt);
			die(json_encode($r));
		}
		
		
		
		//cek is progress production
		$is_available_production_id = array();
		$is_available_production = array();
		$this->db->select("a.*");
		$this->db->from($this->table_production.' as a');
		$this->db->where("a.pr_status IN ('progress')");
		$this->db->where("a.pr_date >= '".$date_from."' AND a.pr_date <= '".$date_till."'");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result() as $dtB){
				$tanggal_production = date("d-m-Y", strtotime($dtB->pr_date));
				if(!in_array($tanggal_production, $is_available_production)){
					$is_available_production[] = $tanggal_production;
				}
				
				if(!in_array($dtB->id, $is_available_production_id)){
					$is_available_production_id[] = $dtB->id;
				}
			}
			
		}
		
		//Auto Cancel From Auto Closing
		$autoclosing_auto_cancel_production = 0;
		if(!empty($get_opt['autoclosing_auto_cancel_production'])){
			$autoclosing_auto_cancel_production = $get_opt['autoclosing_auto_cancel_production'];
		}
		
		if(!empty($from_autoclosing)){
			if(!empty($autoclosing_auto_cancel_production)){
				
				$is_available_production = array();
				
				//AUTO CANCEL BILLING
				if(!empty($is_available_production_id)){
					$is_available_production_id_sql = implode(",", $is_available_production_id);
					
					$dt_auto_cancel = array(
						'pr_status' => 'cancel',
						'pr_memo' => 'Auto Cancel From Auto Closing'
					);
					
					$this->db->update($this->table_production, $dt_auto_cancel, "id IN (".$is_available_production_id_sql.")");
					
				}
				
			}
		}
		
		if(!empty($is_available_production)){
			$is_available_production_txt = implode(", ", $is_available_production);
			$r = array('success' => false, 'info'	=> 'Please Set Production with status Progress to Done/Cancel!<br/>on Date: '.$is_available_production_txt);
			die(json_encode($r));
		}
		
		
		$allowed_closing = false;
		
		$date_from_minus_1 = strtotime($date_from) - ONE_DAY_UNIX;
		$date_from_minus = date("Y-m-d", $date_from_minus_1);
		//check on db < $date_from
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'inventory'");
		$this->db->where("a.tanggal = '".$date_from_minus."'");
		$this->db->order_by("a.tanggal", "DESC");
		$this->db->limit(1);
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			
			$dtC = $dt_closing->row();
			$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
			//echo $dtC->tanggal.' == '.$closing_inventory_start_date.'<br/>';
			
			if($dtC->closing_status == 1){
				$allowed_closing = true;
			}else{
				if(strtotime($dtC->tanggal) < strtotime($closing_inventory_start_date)){
					//max closing is < closing_inventory_start_date
					$allowed_closing = true;
				}else{
					$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' or < from date '.date("d-m-Y", strtotime($date_from)).' Still Not Closed!');
					die(json_encode($r));
				}
				
			}
			
			
			
		}else{
			
			//echo "$date_from == $closing_inventory_start_date";die();
			if($date_from == $closing_inventory_start_date){
				//allowed
				$allowed_closing = true;
				//echo 'allowed_generate = closing_inventory_start_date<br/>';
			}else{
				
				$r = array('success' => false, 'info'	=> 'Please Generate Data First, Start Date Closing From '.$closing_inventory_start_date.'!');
				die(json_encode($r));
			}
			
			//$allowed_closing = true;
			//echo 'allowed_closing -> ON DB > '.$date_from.'br/>';
		}
		
		
		//get ID
		//check on db < $date_from
		$data_closing = array();
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'inventory'");
		$this->db->where("a.tanggal >= '".$date_from."' AND a.tanggal <= '".$date_till."' ");
		$this->db->order_by("a.tanggal", "DESC");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result_array() as $dt){
				$data_closing[$dt['tanggal']] = $dt['id'];
			}
		}
		
		$updated_closing = array();
		foreach($dt_tanggal as $dtT){
			
			if(!empty($data_closing[$dtT])){
				$updated_closing[] = array(
					'id' => $data_closing[$dtT],
					'closing_status' => 1,
					//'tanggal'	=> $dtT
				);
			}
			
			
		}
		
		//echo '$allowed_closing = '.$allowed_closing.'<pre>';
		//print_r($updated_closing);
		//die();
		
		if(!empty($updated_closing)){
			$this->db->update_batch($this->table_closing, $updated_closing, 'id');
		}
		
		if(count($updated_closing) == 1){
			$r = array('success' => true, 'info'	=> 'Date '.$date_from.' Been Closed!');
			die(json_encode($r));
		}
		
		$r = array('success' => true, 'info'	=> 'Date From '.$date_from.' ~ '.$date_till.' Been Closed!');
		die(json_encode($r));
		
	}
	
	public function openDate()
	{
		$this->table_closing = $this->prefix.'closing';
		$this->table_closing_inventory = $this->prefix.'closing_inventory';
		$session_client_id = $this->session->userdata('client_id');	
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'User Session Expired, Please Re-Login!');
			die(json_encode($r));
		}
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
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
		//cek is been generated
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'inventory'");
		$this->db->where("a.tanggal IN ('".$tanggal_txt."')");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result() as $dtC){
				if($dtC->closing_status == 0){
					$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
					$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' Status Not Closed!');
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
		
		krsort($dt_tanggal);
		
		$total_date = count($dt_tanggal);
		if($total_date > 31){
			$r = array('success' => false, 'info'	=> 'Max Open Date is 31 Days!');
			die(json_encode($r));
		}
		
		//CEK FIRST TO START DATE
		$opt_value = array(
			'closing_inventory_start_date'
		);
		$get_opt = get_option_value($opt_value);
		
		
		if(!empty($get_opt['closing_inventory_start_date'])){
			$closing_inventory_start_date = $get_opt['closing_inventory_start_date'];
			$closing_inventory_start_date = date("Y-m-d", strtotime($closing_inventory_start_date));
		}
		
		if(empty($closing_inventory_start_date)){
			$closing_inventory_start_date = date("Y-m-d");
		}
		
		$today_date = date("Y-m-d");
		$date_from = '';
		$date_till = '';
		$i = 0;
		foreach($dt_tanggal as $dtT){
			$i++;
			
			if($i == 1){
				$date_from = $dtT;
			}
			
			if(count($dt_tanggal) == $i){
				$date_till = $dtT;
			}
		}
		
		if(strtotime($date_from) > strtotime($today_date)){
			$r = array('success' => false, 'info'	=> 'Max Open Date is Today: '.date("d-m-Y").'!');
			die(json_encode($r));
		}
		
		$allowed_open = false;
		
		$date_from_plus_1 = strtotime($date_from) + ONE_DAY_UNIX;
		$date_from_plus = date("Y-m-d", $date_from_plus_1);
		//check on db < $date_from
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'inventory'");
		$this->db->where("a.tanggal = '".$date_from_plus."'");
		$this->db->order_by("a.tanggal", "DESC");
		$this->db->limit(1);
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			
			$dtC = $dt_closing->row();
			$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
			//echo $dtC->tanggal.' == '.$closing_inventory_start_date.'<br/>';
			
			if($dtC->closing_status == 0){
				$allowed_open = true;
			}else{
				if(strtotime($dtC->tanggal) < strtotime($closing_inventory_start_date)){
					//max closing is < closing_inventory_start_date
					$allowed_open = true;
				}else{
					$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' or > from date '.date("d-m-Y", strtotime($date_from)).' Still Closed!');
					die(json_encode($r));
				}
				
			}
			
			
		}else{
			
			//echo "$date_from == $closing_inventory_start_date";die();
			if($date_from >= $closing_inventory_start_date){
				//allowed
				$allowed_open = true;
				//echo 'allowed_generate = closing_inventory_start_date<br/>';
			}else{
				
				$r = array('success' => false, 'info'	=> 'Please Generate Data First, Start Date Closing From '.$closing_inventory_start_date.'!');
				die(json_encode($r));
			}
			
			//$allowed_open = true;
			//echo 'allowed_open -> ON DB > '.$date_from.'br/>';
		}
		
		
		//get ID
		//check on db < $date_from
		$data_closing = array();
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'inventory'");
		$this->db->where("a.tanggal >= '".$date_till."' AND a.tanggal <= '".$date_from."' ");
		$this->db->order_by("a.tanggal", "DESC");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result_array() as $dt){
				$data_closing[$dt['tanggal']] = $dt['id'];
			}
		}
		
		$updated_closing = array();
		foreach($dt_tanggal as $dtT){
			
			if(!empty($data_closing[$dtT])){
				$updated_closing[] = array(
					'id' => $data_closing[$dtT],
					'closing_status' => 0,
					//'tanggal'	=> $dtT
				);
			}
			
			
		}
		
		//echo '$allowed_open = '.$allowed_open.'<pre>';
		//print_r($updated_closing);
		//die();
		
		if(!empty($updated_closing)){
			$this->db->update_batch($this->table_closing, $updated_closing, 'id');
		}
		
		if(count($updated_closing) == 1){
			$r = array('success' => true, 'info'	=> 'Date '.$date_from.' Been Opened!');
			die(json_encode($r));
		}
		
		$r = array('success' => true, 'info'	=> 'Date From '.$date_from.' ~ '.$date_till.' Been Opened!');
		die(json_encode($r));
		
	}
}