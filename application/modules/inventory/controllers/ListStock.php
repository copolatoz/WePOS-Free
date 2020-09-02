<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ListStock extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_liststock', 'm');
		$this->load->model('model_liststockdetail', 'm2');
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
	
	public function itemKodeUnik()
	{
		$this->table_items = $this->prefix.'items';
		$this->table_item_kode_unik = $this->prefix.'item_kode_unik';
		$this->table_storehouse = $this->prefix.'storehouse';
		$session_client_id = $this->session->userdata('client_id');	
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		$storehouse_id = $this->input->post('storehouse_id');
		if(empty($storehouse_id)){
			$storehouse_id = -1;
		}
		
		$params = array(
			'fields'		=> 'a.*, c.storehouse_code, c.storehouse_name',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table_item_kode_unik.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->table_items.' as b','a.id = a.item_id','LEFT'),
										array($this->table_storehouse.' as c','c.id = a.storehouse_id','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('a.date_in' => 'DESC'),
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$searching = $this->input->post('query');
		$item_id = $this->input->post('item_id');
		
		$keywords = $this->input->post('keywords');
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if(!empty($searching)){
			$params['where'][] = "(a.kode_unik LIKE '%".$searching."%' OR a.varian_name LIKE '%".$searching."%' OR a.varian_group LIKE '%".$searching."%')";
		}
		if(empty($item_id)){
			$item_id = -1;
		}
		
		$params['where'][] = "(a.item_id = ".$item_id." AND a.storehouse_id = ".$storehouse_id.")";
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  	

		$newData = array();				
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['date_in_show'] = date("d-m-Y",strtotime($s['date_in']));
				$s['item_hpp_show'] = priceFormat($s['item_hpp']);
				
				$s['status_imei'] = '<font color="green">Ada</font>';
				if(!empty($s['ref_out'])){
					$s['status_imei'] = '<font color="red">Terjual</font>';
					//$s['storehouse_name'] = '-';
					//$s['storehouse_code'] = '-';
				}
				
				array_push($newData, $s);
			}
		}
  		
		$get_data['data'] = $newData;
		$get_data['totalCount'] = count($newData);
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetail()
	{
		
		$this->table_stock_rekap = $this->prefix.'stock_rekap';
		$this->table_items = $this->prefix.'items';
		$this->table_storehouse = $this->prefix.'storehouse';
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
		
		$category_id = $this->input->post('category_id');
		if(!empty($category_id)){
			if($category_id == -1){
				$category_id = '';
			}
		}
		
		$alert_hpp_vs_sales = 0;
		
		// Default Parameter
		$this->db->select('a.*, b.item_name, b.item_code, b.item_price, b.sales_price, b.unit_id, b.use_for_sales, b.min_stock, b.use_stok_kode_unik, 
		c.unit_name, d.storehouse_code, d.storehouse_name');
		$this->db->from($this->table_stock_rekap.' as a');
		$this->db->join($this->table_items.' as b',"b.id = a.item_id","LEFT");
		$this->db->join($this->table_unit.' as c',"c.id = b.unit_id","LEFT");
		$this->db->join($this->table_storehouse.' as d',"d.id = a.storehouse_id","LEFT");
		$this->db->where("a.trx_date", $tanggal);
		$this->db->where("a.storehouse_id", $storehouse_id);
		$this->db->where("b.is_deleted = 0");
		
		if(!empty($category_id)){
			$this->db->where("b.category_id", $category_id);
		}
		
		$keywords = $this->input->post('keywords');
		if(!empty($keywords)){
			$this->db->where("(b.item_code LIKE '%".$keywords."%' OR b.item_name LIKE '%".$keywords."%')");
		}
		
		$this->db->order_by("b.item_code", "ASC");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			$all_data_detail = array();
			$all_date_item = array();
			foreach($dt_closing->result_array() as $dt){
				
				$dt['item_hpp_show'] = priceFormat($dt['item_hpp']);
				$selisih = $dt['sales_price'] - $dt['item_hpp'];
				$dt['sales_price_show'] = priceFormat($dt['sales_price']);
				
				$dt['use_stok_kode_unik_text'] = '<font color="red">Tidak</font>';
				if(!empty($dt['use_stok_kode_unik'])){
					$dt['use_stok_kode_unik_text'] = '<font color="green">Ya</font>';
				}
				
				if($selisih < 0 AND $dt['use_for_sales'] == 1){
					$dt['sales_price_show'] = '<b style="color:red">'.$dt['sales_price_show'].'</b>';
					$dt['item_hpp_show'] = '<b style="color:red">'.$dt['item_hpp_show'].'</b>';
					$alert_hpp_vs_sales++;
				}
				
				if($dt['total_stock'] < 0 OR (!empty($dt['min_stock']) AND $dt['total_stock'] < $dt['min_stock'])){
					$dt['item_name'] = '<b style="color:orange">'.$dt['item_name'].'</b>';
					$dt['total_stock'] = '<b style="color:orange">'.priceFormat($dt['total_stock']).'</b>';
				}else{
					$dt['total_stock'] = priceFormat($dt['total_stock']);
				}
				
				if($dt['total_stock_kemarin'] < 0 OR (!empty($dt['min_stock']) AND $dt['total_stock'] < $dt['min_stock'])){
					$dt['total_stock_kemarin'] = '<b style="color:orange">'.priceFormat($dt['total_stock_kemarin']).'</b>';
				}else{
					$dt['total_stock_kemarin'] = priceFormat($dt['total_stock_kemarin']);
				}
				
				$dt['total_stock_in'] = priceFormat($dt['total_stock_in']);
				$dt['total_stock_out'] = priceFormat($dt['total_stock_out']);
				
				$date_item = $dt['trx_date'].'_'.$dt['item_id'];
				if(!in_array($date_item, $all_date_item)){
					$all_date_item[] = $date_item;
					$all_data_detail[] = $dt;
				}
			}
			$get_data = array('data' => $all_data_detail, 'totalCount' => count($all_data_detail), 'alert_hpp_vs_sales' => $alert_hpp_vs_sales);
			
		}else{
			$keterangan = array('id' => '', 'item_name' => 'No Data or Not Been Generated!');
			$get_data = array('data' => array( 0 => $keterangan), 'totalCount' => 1);
		}
		  		
      	die(json_encode($get_data));
	}
	
	public function generate()
	{
		$nofity = $this->input->post('nofity');
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table_closing = $this->prefix.'closing';
		$this->table_stock_rekap = $this->prefix.'stock_rekap';
		$session_client_id = $this->session->userdata('client_id');	
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
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
		$this->db->select("a.item_id, a.storehouse_id, a.trx_nominal");
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
						'item_hpp'				=> $s['trx_nominal']
					);
				}
			}
		}
		
		//REKAP - KEMARIN
		$dt_rekap_kemarin = array();
		$add_where = "(a.trx_date = '".$qdate_kemarin."')";
		$this->db->select("a.*");
		$this->db->from($this->table_stock_rekap." as a");
		$this->db->join($this->table_items.' as b',"b.id = a.item_id");
		$this->db->where($add_where);
		$this->db->where("a.storehouse_id = ".$current_storehouse_id);
		$this->db->where('b.is_deleted = 0');
		$this->db->where('b.is_active = 1');
		$get_rekap = $this->db->get();
		if($get_rekap->num_rows() > 0){
			foreach($get_rekap->result_array() as $dtR){
				
				if(empty($item_warehouse[$dtR['storehouse_id']][$dtR['item_id']])){
					$item_warehouse[$dtR['storehouse_id']][$dtR['item_id']] = array(
						'item_id'				=> $dtR['item_id'],
						'storehouse_id'			=> $dtR['storehouse_id'],
						'trx_date'				=> $qdate_from,
						'total_stock_kemarin'	=> 0,
						'total_stock_in'		=> 0,
						'total_stock_out'		=> 0,
						'total_stock'			=> 0,
						'item_hpp'				=> $dtR['item_hpp']
					);
				}
				
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
		$this->db->join($this->table_items.' as b2',"b2.id = a.item_id");
		//$this->db->where("b.storehouse_id > 0");
		$this->db->where("b.storehouse_id = ".$current_storehouse_id);
		$this->db->where("b.sto_status = 'done'");
		$this->db->where('b2.is_deleted = 0');
		$this->db->where('b2.is_active = 1');
		$this->db->where($add_where);
		$this->db->order_by("b.sto_date","DESC");
		$get_sto = $this->db->get();
		if($get_sto->num_rows() > 0){
			foreach($get_sto->result_array() as $dtO){
				
				if(empty($item_warehouse[$dtO['storehouse_id']][$dtO['item_id']])){
					$item_warehouse[$dtO['storehouse_id']][$dtO['item_id']] = array(
						'item_id'				=> $dtO['item_id'],
						'storehouse_id'			=> $dtO['storehouse_id'],
						'trx_date'				=> $qdate_from,
						'total_stock_kemarin'	=> 0,
						'total_stock_in'		=> 0,
						'total_stock_out'		=> 0,
						'total_stock'			=> 0,
						'item_hpp'				=> $dtO['current_hpp_avg']
					);
				}
				
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
			$r = array('success' => true, 'info'	=> 'Warehouse: '.$storehouse_name_display.', Stok on Date '.$date_from.' Been Generated!', 'curr_date' => $date_from, 'item_warehouse' => $item_warehouse);
			die(json_encode($r));
		}
		
		$r = array('success' => true, 'info'	=> 'Warehouse: '.$storehouse_name_display.', Stok on Date From '.$date_from.' ~ '.$date_till.' Been Generated!', 'curr_date' => $date_from);
		die(json_encode($r));
				
	}
	
	public function lastgenerate()
	{
		$this->table_closing = $this->prefix.'closing';
		$this->table_stock_rekap = $this->prefix.'stock_rekap';
		$session_client_id = $this->session->userdata('client_id');	
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			die(json_encode($r));
		}
				
		if(empty($session_client_id)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			die(json_encode($r));
		}
		
		//CEK FIRST TO START DATE
		$opt_value = array(
			'stock_rekap_start_date',
			'stock_rekap_last_update'
		);
		$get_opt = get_option_value($opt_value);
		
		$stock_rekap_last_update = 0;
		$stock_rekap_last_update_mktime = 0;
		if(!empty($get_opt['stock_rekap_last_update'])){
			$stock_rekap_last_update = $get_opt['stock_rekap_last_update'];
			$stock_rekap_last_update_mktime = strtotime($stock_rekap_last_update);
			$stock_rekap_last_update = date("Y-m-d", $stock_rekap_last_update_mktime);
		}
		
		//now
		$date_now = date("d-m-Y H:i:s");
		$date_now_mk = strtotime($date_now);
		$date_now = date("Y-m-d", $date_now_mk);
		
		$stock_rekap_start_date = '';
		$stock_rekap_start_date_mktime = 0;
		if(!empty($get_opt['stock_rekap_start_date'])){
			$stock_rekap_start_date_mktime = strtotime($get_opt['stock_rekap_start_date']);
			$stock_rekap_start_date = date("Y-m-d", $stock_rekap_start_date_mktime);
		}
		
		$limit_day = 100;
		$tgl_mktime = strtotime(date("d-m-Y"));
		$tanggal = date("Y-m-d", $tgl_mktime);
		$tanggal_min30_mktime = $tgl_mktime-($limit_day*ONE_DAY_UNIX);
		$tanggal_min30 = date("Y-m-d", $tanggal_min30_mktime);
		
		if($tanggal_min30_mktime <= $stock_rekap_start_date_mktime){
			$tanggal_min30_mktime = $stock_rekap_start_date_mktime;
			$tanggal_min30 = $stock_rekap_start_date;
			
			$limit_day = ($tgl_mktime-$tanggal_min30_mktime) / ONE_DAY_UNIX;
		}
		
		$dt_tanggal = array();
		$dt_tanggal_mktime = array();
		for($i=0; $i<=$limit_day;$i++){
			$tanggal_mk = ($tanggal_min30_mktime+($i*ONE_DAY_UNIX));
			$tanggal_x = date("Y-m-d", $tanggal_mk);
			if(!in_array($tanggal_x,$dt_tanggal)){
				$dt_tanggal[] = $tanggal_x;
				
				$dt_tanggal_mktime[$tanggal_x] = $tanggal_mk;
			}
		}
		
		$storehouse_id = $this->input->post('storehouse_id');
		if(empty($storehouse_id)){
			$storehouse_id = $this->stock->get_primary_storehouse();
		}
		
		$last_tanggal = '';
		$na_tanggal = array();
		
		$session_user = $this->session->userdata();
		//print_r($session_user);
		
		// Default Parameter
		$this->db->select('a.trx_date, a.storehouse_id');
		$this->db->from($this->table_stock_rekap.' as a');
		$this->db->where("a.storehouse_id", $storehouse_id);
		$this->db->where("a.trx_date >= '".$tanggal_min30."'");
		$this->db->order_by("a.trx_date", "DESC");
		$this->db->group_by("a.trx_date");
		$dt_closing = $this->db->get();
		
		if($dt_closing->num_rows() > 0){
			$dt_tanggal_rekap = array();
			
			$is_gap_date = 0;
			$last_date_db = 0;
			$db_mktime_from = 0;
			foreach($dt_closing->result() as $dtR){
				if(!in_array($dtR->trx_date,$dt_tanggal_rekap)){
					$dt_tanggal_rekap[] = $dtR->trx_date;
					
					if($last_date_db == 0){
						$last_date_db = strtotime($dtR->trx_date);
						$db_mktime_from = strtotime($dtR->trx_date);
						
						//cek gap hari
						$gap_date = (($last_date_db-$tanggal_min30_mktime) / ONE_DAY_UNIX) +1;
						
						if($dt_closing->num_rows() != $gap_date){
							//echo '$tanggal_min30_mktime = '.date("d-m-Y", $tanggal_min30_mktime).'<br/>';
							//echo '$last_date_db = '.date("d-m-Y", $last_date_db).'<br/>';
							//echo $gap_date.' != '.$dt_closing->num_rows().'<br/>';
							$is_gap_date = 1;
						}
						
					}
					
					
					//echo $dtR->trx_date.'<br/>';
				}
			}
			
			$count_na = 0;
			foreach($dt_tanggal as $tanggal_x){
				if(!in_array($tanggal_x, $dt_tanggal_rekap) AND $count_na == 0){
					//$na_tanggal[] = $tanggal_x;
					$count_na++;
				}
				
				if(!empty($count_na)){
					if(!empty($stock_rekap_start_date_mktime)){
						
						if(!empty($dt_tanggal_mktime[$tanggal_x])){
							$get_mk = $dt_tanggal_mktime[$tanggal_x];
							
							if($stock_rekap_start_date_mktime <= $get_mk){
								$na_tanggal[] = $tanggal_x;
							}
							
						}
						
					}else{
						$na_tanggal[] = $tanggal_x;
					}
					
				}
				
				$last_tanggal = $tanggal_x;
			}
			
			$dt_last = $dt_closing->row();
			
			if(!empty($na_tanggal)){
				$r = array('success' => true, 'generated' => 0, 'last' => $last_tanggal, 'na_tanggal' => $na_tanggal, 't_na_tanggal' => count($na_tanggal), 'info' => 'Generate Stok: '.$last_tanggal);
			}else{
				$r = array('success' => true, 'generated' => 1, 'last' => $last_tanggal, 'na_tanggal' => $na_tanggal, 't_na_tanggal' => count($na_tanggal), 'info' => 'Generate Stok: '.$tanggal);
			}
			
			$stock_rekap_last_update_mktime = $date_now_mk;
			$stock_rekap_last_update = $date_now;
				
		}else{
			
			if(empty($stock_rekap_last_update)){
				
				$stock_rekap_last_update_mktime = $date_now_mk;
				$stock_rekap_last_update = $date_now;
				
				if($tanggal_min30_mktime <= $stock_rekap_start_date_mktime){
					$tanggal_min30_mktime = $stock_rekap_start_date_mktime;
					$tanggal_min30 = $stock_rekap_start_date;
				}
				$last_tanggal = $tanggal_min30;
				
				$na_tanggal = array();
				foreach($dt_tanggal as $tanggal_x){
					if(!empty($dt_tanggal_mktime[$tanggal_x])){
						$get_mk = $dt_tanggal_mktime[$tanggal_x];
						
						if($stock_rekap_start_date_mktime <= $get_mk){
							$na_tanggal[] = $tanggal_x;
						}
						
					}
				}
				
				$r = array('success' => true, 'generated' => 1, 'last' => $last_tanggal, 'na_tanggal' => $na_tanggal, 't_na_tanggal' => count($na_tanggal), 'info' => 'Generate Stok: '.$tanggal);
			
			}else{
				
				$stock_rekap_last_update_mktime = $date_now_mk;
				$stock_rekap_last_update = $date_now;
				
				$last_tanggal = $stock_rekap_last_update;
				$na_tanggal = array($stock_rekap_last_update);
				
				$r = array('success' => true, 'generated' => 1, 'last' => $last_tanggal, 'na_tanggal' => $na_tanggal, 't_na_tanggal' => count($na_tanggal), 'info' => 'Generate Stok: '.$tanggal);
		
			}
			
		}
		
		$opt_value = array(
			'stock_rekap_last_update' => date("d-m-Y H:i:s", $stock_rekap_last_update_mktime)
		);
		update_option($opt_value);
		
		die(json_encode($r));
		
	}
	
}