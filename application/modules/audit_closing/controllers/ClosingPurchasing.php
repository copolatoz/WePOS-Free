<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ClosingPurchasing extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_closingpurchasing', 'm');
		$this->load->model('model_closingpurchasingdetail', 'm2');
	}

	public function gridData()
	{
		$this->table_closing = $this->prefix.'closing';
		$this->table_closing_purchasing_detail = $this->prefix.'closing_purchasing_detail';
		
		//generate_status_text
		$sortAlias = array(
			//'closing_status_text' => 'closing_status'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table_closing.' as a',
			'where'			=> array('a.tipe' => 'purchasing'),
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
		$closing_purchasing_start_date = '';
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['tanggal'] = date("d-m-Y", strtotime($s['tanggal']));
				
				if(!in_array($s['tanggal'], $data_tanggal)){
					$data_tanggal[] = $s['tanggal'];
				}
				
				if(empty($closing_purchasing_start_date)){
					$closing_purchasing_start_date = $s['tanggal'];
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
		
		//if empty check on opt = closing_purchasing_start_date
		$opt_value = array(
			'closing_purchasing_start_date'
		);
		$get_opt = get_option_value($opt_value);
		
		
		if(!empty($get_opt['closing_purchasing_start_date'])){
			$closing_purchasing_start_date = $get_opt['closing_purchasing_start_date'];
		}
		
		if(empty($closing_purchasing_start_date)){
			$closing_purchasing_start_date = date("d-m-Y");
		}
		
		$today_date = date("d-m-Y");
		$today_mktime = strtotime($today_date);
		$closing_mktime = strtotime($closing_purchasing_start_date);
		$date_from_mktime = strtotime($date_from);
		$date_till_mktime = strtotime($date_till);
		
		if($date_from_mktime <= $closing_mktime){
			$date_from_mktime = $closing_mktime;
		}
		
		$total_day = 0;
		if(!empty($date_from_mktime)){
			$total_day = ($date_till_mktime - $date_from_mktime) / ONE_DAY_UNIX;
		}
		
		/*echo '$get_opt = '.$get_opt['closing_purchasing_start_date'].'<br>';
		echo '$closing_mktime = '.$closing_mktime.'<br>';
		echo '$date_from_mktime = '.$date_from_mktime.'<br>';
		echo '$date_till_mktime = '.$date_till_mktime.'<br>';
		echo '$closing_purchasing_start_date = '.$closing_purchasing_start_date.'<br>';
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
		
		$this->table_closing_purchasing = $this->prefix.'closing_purchasing';
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
		$this->db->from($this->table_closing_purchasing.' as a');
		$this->db->where("tanggal", $tanggal);
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			$data = $dt_closing->row();
			$all_data_detail = array();
			
			$data_detail = array(
				'Closing Purchasing Date' => date("d-m-Y", strtotime($data->tanggal)),
				'Total PO'			=> $data->po_total, 
				'Total Supplier'	=> $data->po_total_supplier, 
				'Item Order'		=> $data->po_total_item, 
				'From RO'			=> $data->po_total_ro, 
				'&nbsp;'			=> '&nbsp;',
				'<b>STATUS:<b/>'	=> '&nbsp;', 
				'PO Status Done'			=> $data->po_status_done, 
				'PO Status Progress'			=> $data->po_status_progress, 
				'&nbsp; '			=> '&nbsp;',
				'<b>TOTAL PURCHASE:<b/>'=> '&nbsp;', 
				'Total Qty Item'		=> $data->po_qty_item, 
				'Total Sub total'		=> priceFormat($data->po_sub_total),  
				'Total Discount'		=> priceFormat($data->po_discount), 
				'Total Tax'				=> priceFormat($data->po_tax),   
				'Total Shipping'		=> priceFormat($data->po_shipping),  
				'&nbsp;<b>Grand Total</b>'	=> '<b>'.priceFormat($data->po_grand_total).'</b>', 
				'&nbsp;  '			=> '&nbsp;', 
				'<b>PAYMENT:<b/>'	=> '&nbsp;', 
				'PO Cash'			=> $data->po_qty_cash, 
				'PO Credit'			=> $data->po_qty_credit,
				'Total Cash'		=> priceFormat($data->po_total_cash), 
				'Total Credit'		=> priceFormat($data->po_total_credit),
				'&nbsp;   '			=> '&nbsp;', 
				'<b>RECEIVING:<b/>'	=> '&nbsp;', 
				'Total Receiving'	=> $data->receiving_total, 
				'From PO'			=> $data->receiving_total_po, 
				'Total Supplier'	=> $data->receiving_total_supplier, 
				'Item Received'		=> $data->receiving_total_item
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
		$this->table_closing_purchasing = $this->prefix.'closing_purchasing';
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
		$this->db->where("a.tipe = 'purchasing'");
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
			'closing_purchasing_start_date'
		);
		$get_opt = get_option_value($opt_value);
		
		
		if(!empty($get_opt['closing_purchasing_start_date'])){
			$closing_purchasing_start_date = $get_opt['closing_purchasing_start_date'];
			$closing_purchasing_start_date = date("Y-m-d", strtotime($closing_purchasing_start_date));
		}
		
		if(empty($closing_purchasing_start_date)){
			$closing_purchasing_start_date = date("Y-m-d");
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
		if(!empty($date_current_total)){
			//cek is been closing
			$this->db->from($this->table_closing.' as a');
			$this->db->where("a.tipe = 'purchasing'");
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
		$this->db->where("a.tipe = 'purchasing'");
		$this->db->where("a.tanggal < '".$date_from."' AND a.generate_status = 0");
		$this->db->order_by("a.tanggal", "DESC");
		$this->db->limit(1);
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			
			$dtC = $dt_closing->row();
			$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
			//echo $dtC->tanggal.' == '.$closing_purchasing_start_date.'<br/>';
			
			if(strtotime($dtC->tanggal) < strtotime($closing_purchasing_start_date)){
				$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' or < from date '.date("d-m-Y", strtotime($date_from)).' Not Been Generated!');
				die(json_encode($r));
			}
			
			
			
		}else{
			
			if($date_from >= $closing_purchasing_start_date){
				//allowed
				$allowed_generate = true;
				//echo 'allowed_generate = closing_purchasing_start_date<br/>';
			}else{
				$r = array('success' => false, 'info'	=> 'Date Closing From '.$closing_purchasing_start_date.'!');
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
		$this->table_po = $this->prefix.'po';
		$this->table_po_detail = $this->prefix.'po_detail';
		$this->table_ro_detail = $this->prefix.'ro_detail';
		$this->table_receiving = $this->prefix.'receiving';
		$this->table_receive_detail = $this->prefix.'receive_detail';
		
		$mktime_dari = strtotime($date_from);
		$mktime_sampai = strtotime($date_till);
					
		$qdate_from = date("Y-m-d",strtotime($date_from));
		$qdate_till = date("Y-m-d",strtotime($date_till));
		//$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
		$qdate_till_max = date("Y-m-d",strtotime($date_till));
		
		$add_where = "(a.po_date >= '".$qdate_from."' AND a.po_date <= '".$qdate_till_max."')";
		
		$this->db->select("a.*");
		$this->db->from($this->table_po." as a");
		$this->db->where("a.is_deleted", 0);
		$this->db->where("a.po_status != 'cancel'");
		$this->db->where($add_where);
		$this->db->order_by("a.created","ASC");

		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			$data_po = $get_dt->result_array();				
		}
		
		$all_group_date = array();		  
		$all_po_supplier = array();	
		$all_po_id = array();	
		$all_po_id_date = array();	
		$total_done_date = array();
		$total_progress_date = array();
		$no_id = 1;
		if(!empty($data_po)){
			foreach ($data_po as $s){
				
				//REKAP TGL
				$po_date = date("Y-m-d",strtotime($s['po_date']));
				if($date_current_total == $po_date){
					if(empty($all_group_date[$po_date])){
						$all_group_date[$po_date] = array(
							'tanggal'		=> $po_date, 
							'po_total'			=> 0, 
							'po_total_supplier'	=> 0, 
							'po_total_item'		=> 0, 
							'po_total_ro'		=> 0, 
							'po_status_done'	=> 0, 
							'po_status_progress'=> 0, 
							'po_qty_item'		=> 0, 
							'po_sub_total'		=> 0, 
							'po_discount'		=> 0, 
							'po_tax'			=> 0, 
							'po_shipping'		=> 0, 
							'po_grand_total'	=> 0, 
							'po_qty_cash'		=> 0, 
							'po_total_cash'		=> 0, 
							'po_qty_credit'		=> 0, 
							'po_total_credit'	=> 0, 
							'receiving_total'	=> 0, 
							'receiving_total_po'=> 0, 
							'receiving_total_supplier'	=> 0,
							'receiving_total_item'		=> 0
						);
						
						$no_id++;
					}
					
					$all_po_id_date[$s['id']] = $po_date;
					
					if(!in_array($s['id'], $all_po_id)){
						$all_po_id[] = $s['id'];
					}
					
					$all_group_date[$po_date]['po_total'] += 1;
					
					if(!empty($s['supplier_id'])){
						if(!in_array($s['supplier_id'], $all_po_supplier)){
							$all_po_supplier[] = $s['supplier_id'];
							$all_group_date[$po_date]['po_total_supplier'] += 1;
						}
					}
					
					//berjalan
					if(empty($total_done_date[$po_date])){
						$total_done_date[$po_date] = 0;
					}
					if(empty($total_progress_date[$po_date])){
						$total_progress_date[$po_date] = 0;
					}
					
					if($s['po_status'] == 'done'){
						$total_done_date[$po_date] += 1;
					}else{
						$total_progress_date[$po_date] += 1;
					}
					
					//$all_group_date[$po_date]['po_total_item'] += 1;
					//$all_group_date[$po_date]['po_qty_item'] += 1;
					$all_group_date[$po_date]['po_sub_total'] += $s['po_sub_total'];
					$all_group_date[$po_date]['po_discount'] +=  $s['po_discount'];
					$all_group_date[$po_date]['po_tax'] +=  $s['po_tax'];
					$all_group_date[$po_date]['po_shipping'] +=  $s['po_shipping'];
					$all_group_date[$po_date]['po_grand_total'] +=  $s['po_total_price'];
					
					$updated_date = date("Y-m-d", strtotime($s['updated']));
					
					if($s['po_payment'] == 'done'){
						$all_group_date[$po_date]['po_qty_cash'] += 1;
						$all_group_date[$po_date]['po_total_cash'] += $s['po_total_price'];
					}else{
						$all_group_date[$po_date]['po_qty_credit'] += 1;
						$all_group_date[$po_date]['po_total_credit'] += $s['po_total_price'];
					}
					
					
					
					//$all_group_date[$po_date]['receiving_total'] += 1;
					//$all_group_date[$po_date]['receiving_total_po'] += 1;
					//$all_group_date[$po_date]['receiving_total_supplier'] += 1;
					//$all_group_date[$po_date]['receiving_total_item'] += 1;
					
					
					//$newData[$s['id']] = $s;
					//array_push($newData, $s);
				}
				
			}
		}
		
		//PO DETAIL
		$po_total_item = array();
		$po_total_ro = array();
		$po_status_done = array();
		if(!empty($all_po_id)){
			$all_po_id_txt = implode(",",$all_po_id);
			$this->db->select('a.*, b.ro_id');
			$this->db->from($this->table_po_detail.' as a');
			$this->db->join($this->table_ro_detail.' as b', 'b.id = a.ro_detail_id', "LEFT");
			$this->db->where('a.po_id IN ('.$all_po_id_txt.')');
			
			$get_detail = $this->db->get();
			if($get_detail->num_rows() > 0){
				foreach($get_detail->result() as $dtRow){
		
					if(!empty($all_po_id_date[$dtRow->po_id])){
						$po_date = $all_po_id_date[$dtRow->po_id];
						
						//TOTAL ITEM PO DATE
						if(empty($po_total_item[$po_date])){
							$po_total_item[$po_date] = array();
						}
						
						if(!in_array($dtRow->item_id, $po_total_item[$po_date])){
							$all_group_date[$po_date]['po_total_item'] += 1;
						}
					
						//TOTAL ITEM RO
						if(empty($po_total_ro[$po_date])){
							$po_total_ro[$po_date] = array();
						}
						
						if(!in_array($dtRow->ro_id, $po_total_ro[$po_date])){
							$all_group_date[$po_date]['po_total_ro'] += 1;
						}
					
						//TOTAL ITEM QTY
						if(empty($all_group_date[$po_date])){
							$all_group_date[$po_date] = 0;
						}
						
						$all_group_date[$po_date]['po_qty_item'] += $dtRow->po_detail_qty;
						
						
					}
		
					
				}
			}
		}
		
		//RECEIVING
		$receiving_total_po = array();
		$receiving_total_supplier = array();
		$receiving_status_progress = array();
		$all_receiving_id = array();
		$all_receiving_id_date = array();
		$add_where = "(a.receive_date >= '".$qdate_from."' AND a.receive_date <= '".$qdate_till_max."')";
		$this->db->select("a.*");
		$this->db->from($this->table_receiving." as a");
		$this->db->where("a.is_deleted", 0);
		$this->db->where("a.receive_status != 'cancel'");
		$this->db->where($add_where);
		$this->db->order_by("a.created","ASC");

		$get_receive = $this->db->get();
		if($get_receive->num_rows() > 0){
			foreach($get_receive->result() as $dtR){
				
				$tgl_recive = date("Y-m-d", strtotime($dtR->receive_date));
				$all_receiving_id_date[$dtR->id] = $tgl_recive;
				
				if($date_current_total == $tgl_recive){
					if(empty($all_group_date[$tgl_recive])){
						$all_group_date[$tgl_recive] = array(
							'tanggal'		=> $tgl_recive, 
							'po_total'			=> 0, 
							'po_total_supplier'	=> 0, 
							'po_total_item'		=> 0, 
							'po_total_ro'		=> 0, 
							'po_status_done'	=> 0, 
							'po_status_progress'=> 0, 
							'po_qty_item'		=> 0, 
							'po_sub_total'		=> 0, 
							'po_discount'		=> 0, 
							'po_tax'			=> 0, 
							'po_shipping'		=> 0, 
							'po_grand_total'	=> 0, 
							'po_qty_cash'		=> 0, 
							'po_total_cash'		=> 0, 
							'po_qty_credit'		=> 0, 
							'po_total_credit'	=> 0, 
							'receiving_total'	=> 0, 
							'receiving_total_po'=> 0, 
							'receiving_total_supplier'	=> 0,
							'receiving_total_item'		=> 0
						);
						
					}
					
					if(!empty($all_group_date[$tgl_recive])){
						
						if(!in_array($dtR->id, $all_receiving_id)){
							$all_receiving_id[] = $dtR->id;
						}
						
						$all_group_date[$tgl_recive]['receiving_total'] += 1; 
						
						//PO
						if(empty($receiving_total_po[$tgl_recive])){
							$receiving_total_po[$tgl_recive] = array();
						}
						
						if(!in_array($dtR->po_id, $receiving_total_po[$tgl_recive])){
							$receiving_total_po[$tgl_recive][] = $dtR->po_id;
							$all_group_date[$tgl_recive]['receiving_total_po'] += 1; 
						}
						
						//SUPPLIER
						if(empty($receiving_total_supplier[$tgl_recive])){
							$receiving_total_supplier[$tgl_recive] = array();
						}
						
						if(!in_array($dtR->supplier_id, $receiving_total_supplier[$tgl_recive])){
							$receiving_total_supplier[$tgl_recive][] = $dtR->supplier_id;
							$all_group_date[$tgl_recive]['receiving_total_supplier'] += 1; 
						}
						
						
					}
					
					
					if($dtR->receive_status == 'progress'){
						if(!in_array($dtR->receive_date, $receiving_status_progress)){
							$receiving_status_progress[] = $dtR->receive_date;
						}
					}
				}
				
			}		
		}
		
		//RECEIVING DETAIL
		$receiving_total_item = array();
		if(!empty($all_receiving_id)){
			$all_receiving_id_txt = implode(",",$all_receiving_id);
			$this->db->from($this->table_receive_detail);
			$this->db->where('receive_id IN ('.$all_receiving_id_txt.')');
			
			$get_detail = $this->db->get();
			if($get_detail->num_rows() > 0){
				foreach($get_detail->result() as $dtRow){
		
					if(!empty($all_receiving_id_date[$dtRow->receive_id])){
						$receive_date = $all_receiving_id_date[$dtRow->receive_id];
						
						//TOTAL ITEM PO DATE
						if(empty($receiving_total_item[$receive_date])){
							$receiving_total_item[$receive_date] = array();
						}
						
						if(!in_array($dtRow->item_id, $receiving_total_item[$receive_date])){
							$all_group_date[$receive_date]['receiving_total_item'] += 1;
						}
						
					}
		
					
				}
			}
		}
		
		//check all status done & progress < date from
		$qdate_from_min_1 = strtotime($qdate_from) - ONE_DAY_UNIX;
		$qdate_from_min_1 = date("Y-m-d", $qdate_from_min_1);
		$total_done = 0;
		$total_progress = 0;
		$add_where = "(a.tanggal = '".$qdate_from_min_1."')";
		$this->db->select("po_status_done as total_done, po_status_progress as total_progress");
		$this->db->from($this->table_closing_purchasing." as a");
		$this->db->where($add_where);
		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			$get_dt_done = $get_dt->row();	
			$total_done = $get_dt_done->total_done;		
			$total_progress = $get_dt_done->total_progress;		
		}
		//echo 'total_done: '.$total_done.'<br/>';
		//echo 'total_progress: '.$total_progress.'<br/>'; 
		
		
		if(empty($total_done)){
			$add_where = "(a.po_date < '".$qdate_from."')";
			$this->db->select("SUM(1) as total_done");
			$this->db->from($this->table_po." as a");
			$this->db->where("a.is_deleted", 0);
			$this->db->where("a.po_status = 'done'");
			$this->db->where($add_where);
			$this->db->order_by("a.created","ASC");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$get_dt_done = $get_dt->row();			
				$total_done = $get_dt_done->total_done;			
			}
		}
		//echo 'total_done: '.$total_done.'<br/>';
		
		
		if(empty($total_progress)){
			$add_where = "(a.po_date < '".$qdate_from."')";
			$this->db->select("SUM(1) as total_progress");
			$this->db->from($this->table_po." as a");
			$this->db->where("a.is_deleted", 0);
			$this->db->where("a.po_status = 'progress'");
			$this->db->where($add_where);
			$this->db->order_by("a.created","ASC");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$get_dt_done = $get_dt->row();			
				$total_progress = $get_dt_done->total_progress;			
			}
		}
		//echo 'total_progress: '.$total_progress.'<br/>'; 
		
		
		
		//check empty date
		if(!empty($dt_tanggal)){
			foreach($dt_tanggal as $key => $val){
				
				if($date_current_total == $val){
					//echo 'EMPTY: '.$val.'<br/>'; 
					if(!empty($total_done_date[$val])){
						$total_done += $total_done_date[$val];
					}
					
					if(!empty($total_progress_date[$val])){
						$total_progress += $total_progress_date[$val];
					} 
					
					if(empty($all_group_date[$val])){
						
						//echo 'EMPTY: '.$val.'<br/>';
						
						$all_group_date[$val] = array(
							'tanggal'			=> $val, 
							'po_total'			=> 0, 
							'po_total_supplier'	=> 0, 
							'po_total_item'		=> 0, 
							'po_total_ro'		=> 0, 
							'po_status_done'	=> $total_done, 
							'po_status_progress'=> $total_progress, 
							'po_qty_item'		=> 0, 
							'po_sub_total'		=> 0, 
							'po_discount'		=> 0, 
							'po_tax'			=> 0, 
							'po_shipping'		=> 0, 
							'po_grand_total'	=> 0, 
							'po_qty_cash'		=> 0, 
							'po_total_cash'		=> 0, 
							'po_qty_credit'		=> 0, 
							'po_total_credit'	=> 0, 
							'receiving_total'	=> 0, 
							'receiving_total_po'=> 0, 
							'receiving_total_supplier'	=> 0,
							'receiving_total_item'		=> 0
						);
						
					}else{
						
						$all_group_date[$val]['po_status_done'] = $total_done;
						$all_group_date[$val]['po_status_progress'] = $total_progress;
						
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
		$insert_closing_purchasing = array();
		$updated_closing_purchasing = array();
		$insert_closing = array();
		$updated_closing = array();
		if(!empty($newData)){
			foreach($newData as $dt){
				
				if($date_current_total == $dt['tanggal']){
					if(in_array($dt['tanggal'], $updated_closing_date)){
						
						$dt['updated'] = date('Y-m-d H:i:s');
						$dt['updatedby'] = $session_user;
						$updated_closing_purchasing[] = $dt;
						
						$bulan = date("m", strtotime($dt['tanggal']));
						$tahun = date("Y", strtotime($dt['tanggal']));
						
						$updated_closing[] = array(
							'tanggal'	=> $dt['tanggal'],
							'bulan'	=> $bulan,
							'tahun'	=> $tahun,
							'tipe'	=> 'purchasing',
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
						$insert_closing_purchasing[] = $dt;
						
						if(!in_array($dt['tanggal'], $insert_date)){
							$insert_date[] = $dt['tanggal'];
							
							$bulan = date("m", strtotime($dt['tanggal']));
							$tahun = date("Y", strtotime($dt['tanggal']));
							
							$insert_closing[] = array(
								'tanggal'	=> $dt['tanggal'],
								'bulan'	=> $bulan,
								'tahun'	=> $tahun,
								'tipe'	=> 'purchasing',
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
		//print_r($insert_closing_purchasing);
		//die();
		
		if(!empty($insert_closing)){
			//remove if available
			if(!empty($insert_date)){
				$insert_date_txt = implode("','", $insert_date);
				$this->db->delete($this->table_closing, "tanggal IN ('".$insert_date_txt."') AND tipe = 'purchasing'");
			}
			
			$this->db->insert_batch($this->table_closing, $insert_closing);
		}
		
		if(!empty($insert_closing_purchasing)){
			//remove if available
			if(!empty($insert_date)){
				$insert_date_txt = implode("','", $insert_date);
				$this->db->delete($this->table_closing_purchasing, "tanggal IN ('".$insert_date_txt."')");
			}
			
			$this->db->insert_batch($this->table_closing_purchasing, $insert_closing_purchasing);
		}
		
		if(!empty($updated_closing)){
			//REMOVE OLD
			$updated_closing_date_txt = implode("','", $updated_closing_date);
			$this->db->delete($this->table_closing,"tanggal IN ('".$updated_closing_date_txt."') AND tipe = 'purchasing'");
			$this->db->insert_batch($this->table_closing, $updated_closing);
			//$this->db->update_batch($this->table_closing, $updated_closing, 'tanggal');
		}
		
		if(!empty($updated_closing_purchasing)){
			//REMOVE OLD
			$updated_closing_date_txt = implode("','", $updated_closing_date);
			$this->db->delete($this->table_closing_purchasing,"tanggal IN ('".$updated_closing_date_txt."')");
			$this->db->insert_batch($this->table_closing_purchasing, $updated_closing_purchasing);
			//$this->db->update_batch($this->table_closing_purchasing, $updated_closing_purchasing, 'tanggal');
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
		$this->table_closing_purchasing = $this->prefix.'closing_purchasing';
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
		$this->db->where("a.tipe = 'purchasing'");
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
			'closing_purchasing_start_date'
		);
		$get_opt = get_option_value($opt_value);
		
		
		if(!empty($get_opt['closing_purchasing_start_date'])){
			$closing_purchasing_start_date = $get_opt['closing_purchasing_start_date'];
			$closing_purchasing_start_date = date("Y-m-d", strtotime($closing_purchasing_start_date));
		}
		
		if(empty($closing_purchasing_start_date)){
			$closing_purchasing_start_date = date("Y-m-d");
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
		
		
		$allowed_closing = false;
		
		$date_from_minus_1 = strtotime($date_from) - ONE_DAY_UNIX;
		$date_from_minus = date("Y-m-d", $date_from_minus_1);
		//check on db < $date_from
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'purchasing'");
		$this->db->where("a.tanggal = '".$date_from_minus."'");
		$this->db->order_by("a.tanggal", "DESC");
		$this->db->limit(1);
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			
			$dtC = $dt_closing->row();
			$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
			//echo $dtC->tanggal.' == '.$closing_purchasing_start_date.'<br/>';
			
			if($dtC->closing_status == 1){
				$allowed_closing = true;
			}else{
				if(strtotime($dtC->tanggal) < strtotime($closing_purchasing_start_date)){
					//max closing is < closing_purchasing_start_date
					$allowed_closing = true;
				}else{
					$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' or < from date '.date("d-m-Y", strtotime($date_from)).' Still Not Closed!');
					die(json_encode($r));
				}
				
			}
			
			
			
		}else{
			
			//echo "$date_from == $closing_purchasing_start_date";die();
			if($date_from == $closing_purchasing_start_date){
				//allowed
				$allowed_closing = true;
				//echo 'allowed_generate = closing_purchasing_start_date<br/>';
			}else{
				
				$r = array('success' => false, 'info'	=> 'Please Generate Data First, Start Date Closing From '.$closing_purchasing_start_date.'!');
				die(json_encode($r));
			}
			
			//$allowed_closing = true;
			//echo 'allowed_closing -> ON DB > '.$date_from.'br/>';
		}
		
		
		//get ID
		//check on db < $date_from
		$data_closing = array();
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'purchasing'");
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
		$this->table_closing_purchasing = $this->prefix.'closing_purchasing';
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
		$this->db->where("a.tipe = 'purchasing'");
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
			'closing_purchasing_start_date'
		);
		$get_opt = get_option_value($opt_value);
		
		
		if(!empty($get_opt['closing_purchasing_start_date'])){
			$closing_purchasing_start_date = $get_opt['closing_purchasing_start_date'];
			$closing_purchasing_start_date = date("Y-m-d", strtotime($closing_purchasing_start_date));
		}
		
		if(empty($closing_purchasing_start_date)){
			$closing_purchasing_start_date = date("Y-m-d");
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
		$this->db->where("a.tipe = 'purchasing'");
		$this->db->where("a.tanggal = '".$date_from_plus."'");
		$this->db->order_by("a.tanggal", "DESC");
		$this->db->limit(1);
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			
			$dtC = $dt_closing->row();
			$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
			//echo $dtC->tanggal.' == '.$closing_purchasing_start_date.'<br/>';
			
			if($dtC->closing_status == 0){
				$allowed_open = true;
			}else{
				if(strtotime($dtC->tanggal) < strtotime($closing_purchasing_start_date)){
					//max closing is < closing_purchasing_start_date
					$allowed_open = true;
				}else{
					$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' or > from date '.date("d-m-Y", strtotime($date_from)).' Still Closed!');
					die(json_encode($r));
				}
				
			}
			
			
		}else{
			
			//echo "$date_from == $closing_purchasing_start_date";die();
			if($date_from >= $closing_purchasing_start_date){
				//allowed
				$allowed_open = true;
				//echo 'allowed_generate = closing_purchasing_start_date<br/>';
			}else{
				
				$r = array('success' => false, 'info'	=> 'Please Generate Data First, Start Date Closing From '.$closing_purchasing_start_date.'!');
				die(json_encode($r));
			}
			
			//$allowed_open = true;
			//echo 'allowed_open -> ON DB > '.$date_from.'br/>';
		}
		
		
		//get ID
		//check on db < $date_from
		$data_closing = array();
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'purchasing'");
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