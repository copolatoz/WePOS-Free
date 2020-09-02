<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Purchasing extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_purchasing', 'm');
		$this->load->model('model_purchasingdetail', 'm2');
		$this->load->model('inventory/model_stock', 'stock');
		$this->load->model('account_payable/model_account_payable', 'account_payable');
	}
	
	public function gridData()
	{
		$this->table = $this->prefix.'purchasing';
		
		$use_approval_purchasing = 0;
		$get_opt = get_option_value(array("use_approval_purchasing"));
		if(!empty($get_opt['use_approval_purchasing'])){
			$use_approval_purchasing = $get_opt['use_approval_purchasing'];
		}
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'a.is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, c.supplier_name, d.storehouse_name',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'supplier as c','a.supplier_id = c.id','LEFT'),
										array($this->prefix.'storehouse as d','d.id = a.storehouse_id','LEFT'),
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
		$is_active = $this->input->post('is_active');
		$purchasing_status = $this->input->post('purchasing_status');
		$not_cancel = $this->input->post('not_cancel');
		$skip_date = $this->input->post('skip_date');
		$is_rl = $this->input->post('is_rl');
		
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
				
				$params['where'][] = "(a.purchasing_date >= '".$qdate_from."' AND a.purchasing_date <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.purchasing_number' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.purchasing_number LIKE '%".$searching."%' OR a.supplier_invoice LIKE '%".$searching."%' OR c.supplier_name LIKE '%".$searching."%')";
		}		
		if(!empty($is_active)){
			$params['where'][] = "a.is_active = '".$is_active."'";
		}
		if(!empty($purchasing_status)){
			$params['where'][] = "a.purchasing_status = '".$purchasing_status."'";
		}
		if(!empty($not_cancel)){
			$params['where'][] = "a.purchasing_status != 'cancel'";
		}
		if($is_rl == 1 AND $use_approval_purchasing == 1){
			$params['where'][] = "((a.approval_status = 'done'  AND a.use_approval = 1) OR (a.approval_status = 'done'  AND a.use_approval = 0))";
		}
		
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		$get_data['use_approval_purchasing'] = $use_approval_purchasing;
		  		
  		$newData = array();
		
		$all_purchasing_id = array();
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				if($s['purchasing_status'] == 'open'){
					$s['purchasing_status_text'] = '<span style="color:orange;">Open</span>';
				}
				else if($s['purchasing_status'] == 'progress'){
					$s['purchasing_status_text'] = '<span style="color:blue;">Progress</span>';
				}else{
					$s['purchasing_status_text'] = '<span style="color:green;">Done</span>';
				}
				
				if($s['approval_status'] == 'progress'){
					$s['approval_status_text'] = '<span style="color:blue;">Progress</span>';
				}else{
					$s['approval_status_text'] = '<span style="color:green;">Auto</span>';
					if($s['use_approval'] == 1){
						$s['approval_status_text'] = '<span style="color:green;">Done</span>';
					}
				}
				if($s['use_tax'] == 1){
					$s['use_tax_text'] = '<span style="color:green;">Ya</span>';
				}else{
					$s['use_tax_text'] = '<span style="color:red;">Tidak</span>';
				}
				
				$s['payment_note'] = ucfirst($s['purchasing_payment']);
				$s['purchasing_date_txt'] = date("d-m-Y",strtotime($s['purchasing_date']));
				$s['purchasing_sub_total_text'] = 'Rp '.priceFormat($s['purchasing_sub_total']);
				$s['purchasing_total_price_text'] = 'Rp '.priceFormat($s['purchasing_total_price']);
				$s['purchasing_discount_text'] = 'Rp '.priceFormat($s['purchasing_discount']);
				$s['purchasing_tax_text'] = 'Rp '.priceFormat($s['purchasing_tax']);
				
				$s['use_approval'] = $use_approval_purchasing;
				
				if(!in_array($s['id'], $all_purchasing_id)){
					$all_purchasing_id[] = $s['id'];
				}
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function newTempId()
	{
		$temp_purchasing_id = '';
		$user_username = $this->session->userdata('user_username');
		$user_fullname = $this->session->userdata('user_fullname');
		$id_user = $this->session->userdata('id_user');
				
		$get_temp_no = $this->generate_purchasing_number(1);
		if(!empty($get_temp_no)){
			$temp_purchasing_id = $get_temp_no;
		}
		
		$get_data = array('success' => true, 'info' => 'load success', 'temp_purchasing_id' => $temp_purchasing_id, 'user_fullname' => $user_fullname, 'user_username' => $user_username);

		die(json_encode($get_data));
	}
	
	public function use_approval_purchasing()
	{
		$use_approval_purchasing = 0;
		$approval_change_payment_purchasing_done = 0;
		$get_opt = get_option_value(array("use_approval_purchasing","approval_change_payment_purchasing_done"));
		
		$get_data = array('success' => false, 'info' => 'load failed', 'use_approval_purchasing' => $use_approval_purchasing);
		if(!empty($get_opt['use_approval_purchasing'])){
			$use_approval_purchasing = $get_opt['use_approval_purchasing'];
		}
		if(!empty($get_opt['approval_change_payment_purchasing_done'])){
			$approval_change_payment_purchasing_done = $get_opt['approval_change_payment_purchasing_done'];

		}
		$get_data = array('success' => true, 'info' => 'load success', 'use_approval_purchasing' => $use_approval_purchasing, 'approval_change_payment_purchasing_done' => $approval_change_payment_purchasing_done);

		die(json_encode($get_data));
	}
	
	public function gridDataDetail()
	{
		
		$this->table_purchasing = $this->prefix.'purchasing';
		$this->table_purchasing_detail = $this->prefix.'purchasing_detail';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, b.item_code, b.item_name, b.item_price, b2.item_price as item_price_supplier, b.item_image, b.use_stok_kode_unik, c.unit_name, c.unit_code",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table_purchasing_detail.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'supplier_item as b2','b2.id = a.supplier_item_id','LEFT'),
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
		$purchasing_id = $this->input->post('purchasing_id');
		$show_total = $this->input->post('show_total');
		$temp_id = $this->input->post('temp_id');
		$tipe = $this->input->post('tipe');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('b.item_name' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.item_name LIKE '%".$searching."%')";
		}
		
		if(!empty($purchasing_id)){
			$temp_id = '';
			$params['where'][] = array('a.purchasing_id' => $purchasing_id);
		}
		
		if(empty($purchasing_id) AND !empty($temp_id)){
			$params['where'][] = "((a.purchasing_id = '' OR a.purchasing_id IS NULL) AND a.temp_id = '".$temp_id."')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m2->find_all($params);
		  		
		$newData = array();
		$total_detail = 0;
		$total_qty = 0;
		$total_potongan_detail = 0;
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['purchasing_detail_purchase_show'] = 'Rp '.priceFormat($s['purchasing_detail_purchase']);
				$s['purchasing_detail_potongan_show'] = 'Rp '.priceFormat($s['purchasing_detail_potongan']);
				$s['purchasing_detail_total_show'] = 'Rp '.priceFormat($s['purchasing_detail_total']);
				$s['item_id_real'] = $s['item_id'];
				$s['item_code_name'] = $s['item_code'].' / '.$s['item_name'];
				
				$s['use_stok_kode_unik_text'] = '<font color="red">Tidak</font>';
				if(!empty($s['use_stok_kode_unik'])){
					$s['use_stok_kode_unik_text'] = '<font color="green">Ya</font>';
				}
				
				$total_qty += $s['purchasing_detail_qty'];
				$total_detail += $s['purchasing_detail_total'];
				$total_potongan_detail += $s['purchasing_detail_potongan'];
				
				array_push($newData, $s);
			}
		}
		
		if($show_total == 1){
			$get_data['data'] = count($newData);
			$get_data['total_qty'] = $total_qty;
			$get_data['total_detail'] = $total_detail;
			$get_data['total_potongan_detail'] = $total_potongan_detail;
		}else{
			$get_data['data'] = $newData;
			$get_data['total_qty'] = $total_qty;
			$get_data['total_detail'] = $total_detail;
			$get_data['total_potongan_detail'] = $total_potongan_detail;
		}
		
		/*if(!empty($purchasing_id)){
			$this->db->from($this->table_purchasing);
			$this->db->where('purchasing_id',$purchasing_id);
			$getdt_p = $this->db->get();
			if($getdt_p->num_rows() > 0){
				$datap = $getdt_p->row();
				if($datap->purchasing_sub_total != $total_detail){
					//update 
					
				}
			}
		}*/
		
      	die(json_encode($get_data));
	}
	
	
	public function gridDataDetailKodeUnik()
	{
		
		$this->table_purchasing_kode_unik = $this->prefix.'purchasing_kode_unik';
		$session_client_id = $this->session->userdata('client_id');
		$id_user = $this->session->userdata('id_user');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table_purchasing_kode_unik.' as a',
			'join'			=> array(),
			'order'			=> array('a.id' => 'DESC'),
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$purchasing_id = $this->input->post('purchasing_id');
		$purchasing_number = $this->input->post('purchasing_number');
		$purchasingd_id = $this->input->post('purchasingd_id');
		$purchasingd_item_id = $this->input->post('purchasingd_item_id');
		$temp_id = $this->input->post('temp_id');
		$tipe = $this->input->post('tipe');
		$show_total = $this->input->post('show_total');
		
		if(empty($tipe)){
			$tipe = 'add';
		}
		
		if(empty($purchasingd_item_id)){
			$purchasingd_item_id = -1;
		}
		
		//if(!empty($is_dropdown)){
			$params['order'] = array('a.id' => 'DESC');
		//}
		
		if(!empty($searching)){
			$params['where'][] = "(a.kode_unik LIKE '%".$searching."%')";
		}
		
		if(!empty($purchasing_id) AND empty($purchasingd_id) AND empty($temp_id) AND $tipe == 'add'){
			
			$repeat_tot = 3-strlen($id_user);
			if($repeat_tot < 0) { $repeat_tot = 0; }
			
			$temp_id = $purchasing_number.'-'.str_repeat('0', $repeat_tot).$id_user;
			//$temp_id = $get_temp_id.'-'.$purchasingd_item_id;
		}
		
		if(empty($purchasing_id) AND empty($purchasingd_id) AND !empty($temp_id) AND $tipe == 'add'){
			
			$repeat_tot = 3-strlen($id_user);
			if($repeat_tot < 0) { $repeat_tot = 0; }
			
			$temp_id_or = str_repeat('0', $repeat_tot).$id_user.'-'.$purchasingd_item_id;
		}
		
		//add new
		if($tipe == 'add'){
			
			if(empty($purchasingd_id) AND empty($temp_id)){
				$params['where'][] = "(a.item_id =  ".$purchasingd_item_id." AND a.purchasingd_id = -1)";
			}else
			if(empty($purchasingd_id) AND !empty($temp_id)){
				if(!empty($temp_id_or)){
					$params['where'][] = "a.purchasingd_id = 0 AND (a.item_id =  ".$purchasingd_item_id." AND (a.temp_id LIKE '".$temp_id."-%') OR a.temp_id LIKE '%".$temp_id_or."')";
				}else{
					$params['where'][] = "a.purchasingd_id = 0 AND (a.item_id =  ".$purchasingd_item_id." AND a.temp_id LIKE '".$temp_id."-%')";
				}
				
			}else
			if(!empty($purchasingd_id) AND !empty($purchasingd_item_id)){
				$params['where'][] = "(a.item_id =  ".$purchasingd_item_id." AND a.purchasingd_id LIKE '".$purchasingd_id."')";
			}else
			{
				$params['where'][] = "(a.item_id =  ".$purchasingd_item_id." AND a.temp_id LIKE '".$temp_id."-%')";
			}
			
		}else{
			
			if(empty($purchasingd_id) AND empty($temp_id)){
				$params['where'][] = "(a.item_id =  ".$purchasingd_item_id." AND a.purchasingd_id = -1)";
			}else
			if(empty($purchasingd_id) AND !empty($temp_id)){
				$params['where'][] = "(a.item_id =  ".$purchasingd_item_id." AND a.temp_id LIKE '".$temp_id."-%')";
			}else
			if(!empty($purchasingd_id) AND !empty($purchasingd_item_id)){
				$params['where'][] = "(a.item_id =  ".$purchasingd_item_id." AND a.purchasingd_id LIKE '".$purchasingd_id."')";
			}else
			{
				$params['where'][] = array('a.purchasingd_id' => $purchasingd_id);
			}
		}
		
		//get data -> data, totalCount
		$get_data = $this->m2->find_all($params);
		  		
		$newData = array();
		$renew_temp_id = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				if(!empty($temp_id_or)){
					$temp_id_check = $temp_id.'-'.$purchasingd_item_id;
					if($s['temp_id'] != $temp_id_check){
						$s['temp_id'] = $temp_id_check;
						if(!in_array($s['id'], $renew_temp_id)){
							$renew_temp_id[] = $s['id'];
						}
					}
				}
				
				$s['use_tax_text'] = 'Tidak';
				if($s['use_tax'] == 1){
					$s['use_tax_text'] = 'Ya';
				}
				
				array_push($newData, $s);
			}
		}
		
		//renew temp id - kodeunik
		if(!empty($renew_temp_id)){
			$update_temp_id = array("temp_id" => $temp_id_check);
			$sqlTempId = implode(",",$renew_temp_id);
			$this->db->update($this->table_purchasing_kode_unik, $update_temp_id,"id IN (".$sqlTempId.")");
		}
		
		if($show_total == 1){
			$get_data['totalCount'] = count($newData);
		}else{
			$get_data['data'] = $newData;
		}
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'purchasing';	
		$this->table2 = $this->prefix.'purchasing_detail';		
		$this->table_purchasing_kode_unik = $this->prefix.'purchasing_kode_unik';
		$this->table_storehouse = $this->prefix.'storehouse';
		
		$this->prefix_acc = config_item('db_prefix3');
		$this->table_account_payable = $this->prefix_acc.'account_payable';			
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$purchasing_date = $this->input->post('purchasing_date');
		
		//CLOSING DATE
		$var_closing = array(
			'xdate'	=> $purchasing_date,
			'xtipe'	=> 'purchasing'
		);
		$is_closing = is_closing($var_closing);
		if($is_closing){
			$r = array('success' => false, 'info' => 'Transaksi Pembelian pada tanggal: '.$purchasing_date.' sudah ditutup!'); 
			die(json_encode($r));
		}
		
		$storehouse_id = $this->input->post('storehouse_id');
		$storehouse_code = $this->input->post('storehouse_code');
		$purchasing_status = $this->input->post('purchasing_status');
		$temp_id = $this->input->post('temp_id');
		$supplier_id = $this->input->post('supplier_id');
		$supplier_invoice = $this->input->post('supplier_invoice');
		$purchasing_payment = $this->input->post('purchasing_payment');
		$purchasing_memo = $this->input->post('purchasing_memo');
		$purchasing_sub_total = $this->input->post('purchasing_sub_total');
		$purchasing_discount = $this->input->post('purchasing_discount');
		$purchasing_tax = $this->input->post('purchasing_tax');
		$purchasing_shipping = $this->input->post('purchasing_shipping');
		$purchasing_total_price = $this->input->post('purchasing_total_price');
		$purchasing_termin = $this->input->post('purchasing_termin');
		$use_tax = $this->input->post('use_tax');
		if(empty($use_tax)){
			$use_tax = 0;
		}
		//$purchasing_ship_to = $this->input->post('purchasing_ship_to');
		//$purchasing_project = $this->input->post('purchasing_project');
		
		if(empty($storehouse_id)){
			$r = array('success' => false, 'info' => 'Input/Select Gudang');
			die(json_encode($r));
		}
		
		$get_opt = get_option_value(array("use_approval_purchasing","as_server_backup"));
		cek_server_backup($get_opt);
		
		$use_approval_purchasing = 0;
		$approval_status = $this->input->post('approval_status');
		$old_approval_status = $this->input->post('old_approval_status');
		$spv_user = $this->input->post('spv_user');
		
		$approval_status_spv = 0;
		if(!empty($spv_user)){
			$approval_status_spv = 1;
		}
		
		if($approval_status == 1 AND $approval_status_spv == 1){
			$approval_status = 'done';
		}
		
		if(!empty($get_opt['use_approval_purchasing'])){
			$use_approval_purchasing = $get_opt['use_approval_purchasing']; 
			
			if(empty($approval_status)){
				$approval_status = 'progress';
			}
			
		}else{
			
			if(empty($approval_status)){
				$approval_status = 'done';
			}
		}
		
		if($old_approval_status == 'done'){
			$approval_status = $old_approval_status;
		}
		
		if(empty($supplier_id)){
			$r = array('success' => false, 'info' => 'Pilih Supplier!'); 
			die(json_encode($r));
		}
		
		//GET PRIMARY HOUSE
		if(empty($storehouse_id)){
			$storehouse_id = 0;
			$opt_value = array(
				'warehouse_primary'
			);
			$get_opt = get_option_value($opt_value);
			if(!empty($get_opt['warehouse_primary'])){
				$storehouse_id = $get_opt['warehouse_primary'];
			}
			
			if(empty($storehouse_id)){
				$this->db->from($this->table_storehouse);
				$this->db->where("is_primary = 1");
				$get_primary_storehouse = $this->db->get();
				if($get_primary_storehouse->num_rows() > 0){
					$storehouse_dt = $get_primary_storehouse->row();
					$storehouse_id = $storehouse_dt->id;
				}
			}
		}
		
		if(empty($storehouse_id)){
			$r = array('success' => false, 'info' => 'Set Default Gudang Penerimaan Barang!'); 
			die(json_encode($r));
		}
		
		if(empty($purchasing_payment)){
			$purchasing_payment = 'cash';
		}
		
		$form_type_purchasing = $this->input->post('form_type_purchasing', true);
		$purchasing_id = $this->input->post('id', true);
		$purchasingDetail = array();
		
		//get all detail
		if($form_type_purchasing == 'edit' AND !empty($purchasing_id)){
			$this->db->select('a.*');
			$this->db->from($this->table_purchasing_kode_unik.' as a');
			$this->db->join($this->table2.' as b',"b.id = a.purchasingd_id","LEFT");
			$this->db->where("b.purchasing_id = '".$purchasing_id."'");
			$get_kodeunik = $this->db->get();
			
			//purchasingDetail
			$this->db->select('a.*');
			$this->db->from($this->table2.' as a');
			$this->db->where("a.purchasing_id = '".$purchasing_id."'");
			$get_purchasingDetail = $this->db->get();
			
		}else{
			
			$this->db->select('a.*');
			$this->db->from($this->table_purchasing_kode_unik.' as a');
			$this->db->where("a.temp_id LIKE '".$temp_id."%'");
			$get_kodeunik = $this->db->get();
			
			//purchasingDetail
			$this->db->select('a.*');
			$this->db->from($this->table2.' as a');
			$this->db->where("a.temp_id = '".$temp_id."'");
			$get_purchasingDetail = $this->db->get();
			
		}
		
		$all_unik_id = array();
		$all_unik_kode = array();
		$all_unik_kode_double = array();
		$data_kodeunik = array();
		if($get_kodeunik->num_rows() > 0){
			foreach($get_kodeunik->result_array() as $dt){
				
				if(!in_array($dt['kode_unik'], $all_unik_kode)){
					$all_unik_kode[] = $dt['kode_unik'];
				}else{
					if(!in_array($dt['kode_unik'], $all_unik_kode_double)){
						$all_unik_kode_double[] = $dt['kode_unik'];
					}
				}
				
				if(!empty($dt['purchasingd_id'])){
					$purchasingd_id = $dt['purchasingd_id'];
				}else{
					$purchasingd_id = $dt['temp_id'];
				}
				
				if(empty($data_kodeunik[$purchasingd_id])){
					$data_kodeunik[$purchasingd_id] = array();
				}
				
				$dt['use_tax'] = $use_tax;
				$all_unik_id[] = $dt['id'];
				
				$data_kodeunik[$purchasingd_id][] = $dt;
				
				
			}
		}
		
		if(!empty($all_unik_kode_double)){
			$r = array('success' => false, 'info' => 'SN/IMEI Double: '.implode(", ", $all_unik_kode_double)); 
			die(json_encode($r));
		}
		
		$new_item_supplier = array();
		$new_item_ID = array();
		$purchasingDetail = array();
		$total_purchasing_item = 0;
		if($get_purchasingDetail->num_rows() > 0){
			foreach($get_purchasingDetail->result_array() as $dt){
				
				$total_purchasing_item += $dt['purchasing_detail_qty'];
				
				if(empty($dt['from_supplier_item'])){
					$new_item_ID[] = $dt['item_id'];
					$new_item_supplier[$dt['item_id']] = array(
						'supplier_id'=> $supplier_id,
						'item_id'	=> $dt['item_id'],
						'unit_id'	=> $dt['unit_id'],
						'item_price'=> $dt['purchasing_detail_purchase'],
						'item_hpp'	=> $dt['purchasing_detail_purchase'],
						'created'		=>	date('Y-m-d H:i:s'),
						'createdby'		=>	$session_user,
						'updated'		=>	date('Y-m-d H:i:s'),
						'updatedby'		=>	$session_user,
					);
				}
				
				
				$purchasingDetail[] = $dt;
				
			}
		}
		
		//check to db
		$supplier_item_ID = array();
		if(!empty($new_item_ID)){
			$this->db->from($this->prefix.'supplier_item');
			$this->db->where("supplier_id", $supplier_id);
			$get_item = $this->db->get();
			if($get_item->num_rows() > 0){
				foreach($get_item->result() as $dt){
					if(!in_array($dt->item_id, $supplier_item_ID)){
						$supplier_item_ID[] = $dt->item_id;
					}
				}
			}
		}
		
		$add_item_ID = array();
		$add_item_data = array();
		$supplier_item_ID2 = array();
		if(!empty($new_item_ID)){
			foreach($new_item_ID as $itemId){
				if(!in_array($itemId, $supplier_item_ID)){
					$add_item_ID[] = $itemId;
					$add_item_data[] = $new_item_supplier[$itemId];
					
					if(!in_array($itemId, $supplier_item_ID2)){
						$supplier_item_ID2[] = $itemId;
					}
					
				}
			}
		}
		
		//save to supplier_item
		if(!empty($add_item_data)){
			$this->db->insert_batch($this->prefix.'supplier_item', $add_item_data);
		}
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
			
		$id = $this->input->post('id', true);
		
		$do_update_stok = false;
		$do_update_rollback_stok = false;
		$do_update_status_purchasing = false;
		
		//CEK OLD DATA
		$old_purchasing_status = '';	
		$purchasing_number = '';	
		$closing_date = $purchasing_date;
		$old_data = array();
		$this->db->from($this->table);
		$this->db->where("id = '".$id."'");
		$get_dt = $this->db->get();	
		if($get_dt->num_rows() > 0){
			$old_data = $get_dt->row_array();
			$closing_date = $old_data['purchasing_date'];
			$purchasing_number = $old_data['purchasing_number'];
			$old_purchasing_status = $old_data['purchasing_status'];
		}	
		
		//CLOSING DATE
		$var_closing = array(
			'xdate'	=> $purchasing_date,
			'xtipe'	=> 'purchasing'
		);
		$is_closing = is_closing($var_closing);
		if($is_closing){
			$r = array('success' => false, 'info' => 'Transaksi untuk Purchasing pada tanggal: '.$closing_date.' sudah ditutup'); 
			die(json_encode($r));
		}
		
		
		if(($purchasing_status == 'done' AND $form_type_purchasing == 'add') OR ($old_purchasing_status == 'progress' AND $purchasing_status == 'done' AND $form_type_purchasing == 'edit')){
			
			if(!empty($all_unik_kode)){
				$this->cek_unik_kode_purchasing($all_unik_kode);
			}
			
			//cek warehouse
			$default_warehouse = $this->stock->get_primary_storehouse();
			if(empty($default_warehouse)){
				$r = array('success' => false, 'info' => 'Tidak ditemukan Gudang Utama/Primary! Silahkan setup data gudang'); 
				die(json_encode($r));
			}
			
			$do_update_stok = true;
			$do_update_status_purchasing = true;
			
			$update_stok = 'update';
			
			if($total_purchasing_item == 0){
				$r = array('success' => false, 'info' => 'Total Detail Item = 0!'); 
				die(json_encode($r));
			}
			
			if($purchasing_date != date("Y-m-d")){
				$warning_update_stok = true;
			}
			
		}
		
		
		if($old_purchasing_status == 'done' AND $purchasing_status == 'progress' AND $form_type_purchasing == 'edit'){
			
			if(!empty($all_unik_kode)){
				$this->cek_unik_kode_purchasing($all_unik_kode, true);
			}
			
			$do_update_rollback_stok = true;
			$do_update_status_purchasing = true;
			
			if($purchasing_date != date("Y-m-d")){
				$warning_update_stok = true;
			}
			
			//CEK PEMBAYARAN AP != kontrabon
			$this->db->from($this->prefix_acc.'account_payable');
			$this->db->where("purchasing_id = '".$po_id."'");
			$this->db->where("ap_tipe = 'purchasing' AND is_deleted = 0");
			$get_stat_ap = $this->db->get();	
			if($get_stat_ap->num_rows() > 0){
				
				$dt_ap = $get_stat_ap->row();
				
				if($dt_ap->ap_status == 'pengakuan' OR $dt_ap->ap_status == 'posting'){
					
				}else
				if($dt_ap->ap_status == 'kontrabon'){
					$r = array('success' => false, 'info' => 'Tidak boleh update status ke Progress<br/>Silahkan Cek Status AP/Hutang: '.$dt_ap->ap_no.',<br/>AP/Hutang sudah dibuat kontrabon: '.$dt_ap->no_kontrabon); 
					die(json_encode($r));
				}else
				if($dt_ap->ap_status == 'pembayaran'){
					$r = array('success' => false, 'info' => 'Tidak boleh update status ke Progress<br/>Silahkan Cek Status AP/Hutang: '.$dt_ap->ap_no.',<br/>AP/Hutang sudah selesai s/d pembayaran'); 
					die(json_encode($r));
				}else{
					$r = array('success' => false, 'info' => 'Tidak boleh update status ke Progress<br/>Silahkan Cek Status AP/Hutang: '.$dt_ap->ap_no.', <br/>AP/Hutang sudah sampai tahap Jurnal/Posting ke Bag.Keuangan'); 
					die(json_encode($r));
				}
				
				
			}
		}
			
		$warning_update_stok = false;
		
		$r = '';
		if($form_type_purchasing == 'add')
		{
			
			$get_purchasing_number = $this->generate_purchasing_number();
			
			if(empty($get_purchasing_number)){
				$r = array('success' => false);
				die(json_encode($r));
			}	
			
			$purchasing_number = $get_purchasing_number;
			
			$var = array(
				'fields'	=>	array(
				    'purchasing_number'  	=> 	$get_purchasing_number,
				    'supplier_id'  			=> 	$supplier_id,
				    'supplier_invoice'  	=> 	$supplier_invoice,
				    'purchasing_date'  		=> 	$purchasing_date,
				    'purchasing_total_qty'  => 	$total_purchasing_item,
				    'purchasing_discount'  	=>  $purchasing_discount,
				    'purchasing_tax'  		=>  $purchasing_tax,
				    'purchasing_shipping'  	=>  $purchasing_shipping,
				    'purchasing_sub_total'  =>  $purchasing_sub_total,
				    'purchasing_total_price'=>  $purchasing_total_price,
				    'purchasing_status'  	=> 	$purchasing_status,
					'purchasing_payment'  	=> 	$purchasing_payment,
				    'purchasing_memo'  		=> 	$purchasing_memo,
				    'purchasing_termin'  	=> 	$purchasing_termin,
				    'use_tax'  		=> 	$use_tax,
				    //'purchasing_project'  	=> 	$purchasing_project,
				    //'purchasing_ship_to'  	=> 	$purchasing_ship_to,
				    'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active,
					'use_approval' =>	$use_approval_purchasing,
					'approval_status' =>	$approval_status,
					'storehouse_id' =>	$storehouse_id
				),
				'table'		=>  $this->table
			);	
			
			
			//SAVE
			$this->lib_trans->begin();
				$save_data = $this->m->add($var);
				$id = $this->m->get_insert_id();
			$this->lib_trans->commit();	
      		
		}else
		if($form_type_purchasing == 'edit'){
			
			$var = array('fields'	=>	array(
				    //'purchasing_number'  	=> 	$get_purchasing_number,
				    'supplier_id'  	=> 	$supplier_id,
				    'supplier_invoice'  => 	$supplier_invoice,
				    'purchasing_date'  		=> 	$purchasing_date,
				    'purchasing_total_qty'  => 	$total_purchasing_item,
				    'purchasing_discount'  	=> $purchasing_discount,
				    'purchasing_tax'  		=> $purchasing_tax,
				    'purchasing_shipping'  	=> $purchasing_shipping,
				    'purchasing_sub_total'  => $purchasing_sub_total,
				    'purchasing_total_price'  => $purchasing_total_price,
					'purchasing_payment'  	=> 	$purchasing_payment,
				    'purchasing_memo'  		=> 	$purchasing_memo,
				    'purchasing_termin'  	=> 	$purchasing_termin,
				    'use_tax'  				=> 	$use_tax,
				    'storehouse_id'  		=> 	$storehouse_id,
				    //'purchasing_project'  	=> 	$purchasing_project,
				    //'purchasing_ship_to'  	=> 	$purchasing_ship_to,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active,
					'use_approval' =>	$use_approval_purchasing,
					'approval_status' =>	$approval_status
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			if($old_purchasing_status == 'done' AND $purchasing_status == 'done'){
				$var = array('fields'	=>	array(
						'supplier_invoice'  => 	$supplier_invoice,
						'purchasing_total_qty'  => 	$total_purchasing_item,
						'purchasing_discount'  	=> $purchasing_discount,
						'purchasing_tax'  		=> $purchasing_tax,
						'purchasing_shipping'  	=> $purchasing_shipping,
						'purchasing_sub_total'  => $purchasing_sub_total,
						'purchasing_total_price'=> $purchasing_total_price,
						'purchasing_memo'  		=> 	$purchasing_memo,
						'purchasing_termin'  	=> 	$purchasing_termin,
						'use_tax'  		=> 	$use_tax,
						'updated'		=>	date('Y-m-d H:i:s'),
						'updatedby'		=>	$session_user,
						'use_approval' =>	$use_approval_purchasing,
						'approval_status' =>	$approval_status
					),
					'table'			=>  $this->table,
					'primary_key'	=>  'id'
				);
				
			}else{
				$var['fields']['purchasing_status'] = $purchasing_status;
			}
			
			//UPDATE
			$this->lib_trans->begin();
				$save_data = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
		}
		
		
		if($save_data)
		{  
	
			$update_stok = '';
			if($do_update_stok){
				$r['info'] = 'Update Stok';
				$update_stok = 'update';
			}
			
			if($do_update_rollback_stok){
				$r['info'] = 'Re-Update Stok';
				$update_stok = 'rollback';
			}
			
			$form_type = $form_type_purchasing;
			//$update_stok = 'update';
				
			$r = array('success' => true, 'id' => $id, 'purchasing_number' => $purchasing_number, 'form_type' => $form_type);
			
			//from add
			if($form_type == 'add')
			{
				/*if(!empty($temp_id)){
					$update_temp_detail = array('purchasing_id' => $id, "temp_id" => '');
					$this->db->update($this->table2, $update_temp_detail, "temp_id = '".$temp_id."' AND purchasing_id = ''");
					
					$update_temp_detail = array("temp_id" => '');
					$this->db->update($this->table_purchasing_kode_unik, $update_temp_detail, "temp_id = '".$temp_id."'");
				}*/
				
				$q_det = $this->m2->purchasingDetail($purchasingDetail, $id, $form_type, $data_kodeunik);
				if($q_det == false){
					$r = array('success' => false, 'info' => 'Add Detail Purchasing Gagal!'); 
					die(json_encode($r));
				}
				
			}
				
			$old_status = '';
			if(!empty($old_data)){
				
				if(!empty($old_purchasing_status)){
					$old_status = $old_purchasing_status;
				}
				
				if($old_purchasing_status == 'done'){
					if($old_data['purchasing_payment'] == 'credit' AND $purchasing_payment != 'credit'){
						$updateAP = $this->account_payable->set_account_payable_Purchasing($id);
						
						if($updateAP === true || $updateAP === false){
							$r['updatePurchasing'] = $old_data['purchasing_payment'].' to '.$purchasing_payment;
						}else
						if($updateAP == 'kontrabon'){
							
							$no_kontrabon = '-';
							$this->db->from($this->table_account_payable);
							$this->db->where("ap_tipe = 'purchasing'");
							$this->db->where("purchasing_id = '".$id."'");
							$get_ap = $this->db->get();
							if($get_ap->num_rows() > 0){
								
								$data_AP = $get_ap->row();
								$no_kontrabon = $data_AP->no_kontrabon;
								
							}
							$r['success'] = false;
							$r['info'] = 'Silahkan Cek dan Hapus Kontrabon: '.$no_kontrabon.' terkait Pembelian: '.$old_data['purchasing_number'];
							$r['updatePurchasing'] = $old_data['purchasing_payment'].' to '.$purchasing_payment;
							$r['updateAP'] = $updateAP;
							
							$rollback_purchasing_status = array(
								'purchasing_payment'	=> $old_data['purchasing_payment']
							);
							$this->db->update($this->table, $rollback_purchasing_status, "id = '".$id."'");
							
						}
						
					}else{
						$updateAP = $this->account_payable->set_account_payable_Purchasing($id);
					}
				}
				
			}
			
			if($form_type != 'add' OR ($purchasing_status == 'done' AND $old_status != 'done')){
				
				//update kode unik
				$this->db->select('a.*');
				$this->db->from($this->table_purchasing_kode_unik.' as a');
				$this->db->join($this->prefix.'purchasing_detail as b',"b.id = a.purchasingd_id","LEFT");
				$this->db->where("b.purchasing_id = '".$id."'");
				$get_kodeunik = $this->db->get();
				$data_kodeunik = array();
				if($get_kodeunik->num_rows() > 0){
					foreach($get_kodeunik->result_array() as $dt){
						if(!empty($dt['purchasingd_id'])){
							$purchasingd_id = $dt['purchasingd_id'];
						}else{
							$purchasingd_id = $dt['temp_id'];
						}
						
						if(empty($data_kodeunik[$purchasingd_id])){
							$data_kodeunik[$purchasingd_id] = array();
						}
						
						$dt['use_tax'] = $use_tax;
						
						$data_kodeunik[$purchasingd_id][] = $dt;
						
					}
				}
				
				if($purchasing_status == 'done' AND $old_status != 'done'){
					//get/update ID -> $usageItemDetail
					$item_id_prod = array();
					$this->db->from($this->prefix.'purchasing_detail');
					$this->db->where("purchasing_id", $id);
					$get_det = $this->db->get();
					if($get_det->num_rows() > 0){
						foreach($get_det->result_array() as $dt){
							$item_id_prod[$dt['item_id']] = $dt['id'];
						}
					}
					
					if($form_type == 'add'){
						$update_stok = 'update_add';
					}
					//$update_stok = 'update';
					
					$purchasingDetail_BU = $purchasingDetail;
					$purchasingDetail = array();
					foreach($purchasingDetail_BU as $dtD){
						
						if(!empty($item_id_prod[$dtD['item_id']])){
							$dtD['id'] = $item_id_prod[$dtD['item_id']];
							$purchasingDetail[] = $dtD;
						}
						
					}
					
					//$r['purchasingDetail_done'] = $purchasingDetail;
					
				}
					
				$q_det = $this->m2->purchasingDetail($purchasingDetail, $id, $update_stok, $data_kodeunik);
				if($q_det == false){
					$r = array('success' => false, 'info' => 'Update Detail Purchasing Gagal!'); 
					die(json_encode($r));
				}
				
				if($purchasing_date != date("Y-m-d")){
					$warning_update_stok = true;
				}
			}
			
			if(!empty($all_unik_id)){
				$all_unik_id_sql = implode(",", $all_unik_id);
				$all_update_use_tax = array(
					'use_tax'	=> $use_tax
				);
				$this->db->update($this->table_purchasing_kode_unik, $all_update_use_tax, "id IN (".$all_unik_id_sql.")");
			}
				
			//$r['det_info'] = $q_det;
			
			if($warning_update_stok AND $purchasing_status != $old_status){
				$r['is_warning'] = 1;
				$r['info'] = 'Silahkan Re-Generate/Perbaiki Stok Transaksi pada List Stock Module!<br/>Perbaiki Stok dari: '.$purchasing_date;
			}
			
			if(!empty($q_det['dtPurchasing']['purchasing_number'])){
				$r['purchasing_number'] = $q_det['dtPurchasing']['purchasing_number'];
			}
			
			if(!empty($q_det['update_stock'])){
				
				$post_params = array(
					'storehouse_item'	=> $q_det['update_stock']
				);
				
				$updateStock = $this->stock->update_stock_rekap($post_params);
				
			}
			
			//$updatePurchasing = $this->m4->update_status_Purchasing($purchasing_id);
			$updateAP = $this->account_payable->set_account_payable_Purchasing($id);
			$r['purchasing_total_price'] = $purchasing_total_price;	
			$r['updateAP'] = $updateAP;	
			$r['use_tax'] = $use_tax;	
			//$r['data_kodeunik'] = $data_kodeunik;	
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}	
	
	
	public function saveDetail()
	{
		$this->table = $this->prefix.'purchasing';	
		$this->table2 = $this->prefix.'purchasing_detail';		
		$this->table3 = $this->prefix.'purchasing_kode_unik';		
		$this->table_storehouse = $this->prefix.'storehouse';		
		
		$this->prefix_acc = config_item('db_prefix3');
		$this->table_account_payable = $this->prefix_acc.'account_payable';			
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$tipe = $this->input->post('tipe');
		$id = $this->input->post('id');
		$temp_id = $this->input->post('temp_id');
		$purchasing_id = $this->input->post('purchasing_id');
		$item_id = $this->input->post('item_id');
		$use_stok_kode_unik = $this->input->post('use_stok_kode_unik');
		$supplier_item_id = $this->input->post('supplier_item_id');
		$unit_id = $this->input->post('unit_id');
		$purchasing_detail_qty = $this->input->post('purchasing_detail_qty');
		$purchasing_detail_purchase = $this->input->post('purchasing_detail_purchase');
		$purchasing_detail_potongan = $this->input->post('purchasing_detail_potongan');
		$purchasing_detail_total = $purchasing_detail_qty*$purchasing_detail_purchase;
		
		$get_opt = get_option_value(array("use_approval_purchasing","as_server_backup"));
		cek_server_backup($get_opt);
		
		if(empty($item_id)){
			$r = array('success' => false, 'info' => 'Pilih Data Item/Barang!'); 
			die(json_encode($r));
		}
		
		//$temp_id = '';
		if(!empty($purchasing_id) AND !empty($id)){
			//$temp_id = $purchasingd_id;
			//$purchasingd_id = '';
			$temp_id = '';
		}
		
		//GET PRIMARY HOUSE
		if(empty($storehouse_id)){
			$storehouse_id = 0;
			$opt_value = array(
				'warehouse_primary'
			);
			$get_opt = get_option_value($opt_value);
			if(!empty($get_opt['warehouse_primary'])){
				$storehouse_id = $get_opt['warehouse_primary'];
			}
			
			if(empty($storehouse_id)){
				$this->db->from($this->table_storehouse);
				$this->db->where("is_primary = 1");
				$get_primary_storehouse = $this->db->get();
				if($get_primary_storehouse->num_rows() > 0){
					$storehouse_dt = $get_primary_storehouse->row();
					$storehouse_id = $storehouse_dt->id;
				}
			}
		}
		
		if(empty($storehouse_id)){
			$r = array('success' => false, 'info' => 'Set Default Gudang Penerimaan Barang!'); 
			die(json_encode($r));
		}
		
		$var = array(
			'fields'	=>	array(
				'purchasing_id'  	=> 	$purchasing_id,
				'temp_id'  			=> 	$temp_id,
				'item_id'  			=> 	$item_id,
				'unit_id'  			=> 	$unit_id,
				'purchasing_detail_purchase'  => $purchasing_detail_purchase,
				'purchasing_detail_qty'  	=> $purchasing_detail_qty,
				'purchasing_detail_potongan'  		=> $purchasing_detail_potongan,
				'purchasing_detail_total'  	=> $purchasing_detail_total,
				'supplier_item_id'  => $supplier_item_id,
				'storehouse_id'  => $storehouse_id,
				'use_stok_kode_unik'  => $use_stok_kode_unik,
			),
			'table'		=>  $this->table2,
			'primary_key'	=>  'id'
		);	
		
		
		if($tipe == 'add'){
			//SAVE
			$this->lib_trans->begin();
				$q = $this->m->add($var);
				$id = $this->m->get_insert_id();
			$this->lib_trans->commit();	
			
			if(!empty($temp_id) AND !empty($id)){
				//update kode unik = temp_id & item_id	
				$update_kodeunik = array(
					'purchasingd_id' => $id,
					'temp_id' => ''
				);
				$this->db->update($this->table3, $update_kodeunik, "item_id = ".$item_id." AND temp_id LIKE '".$temp_id."%'");
			}
			
		}else{
			//edit
			$this->lib_trans->begin();
				$q = $this->m->save($var, $id);
			$this->lib_trans->commit();	
		}
		
		if($q)
		{  
			$r = array('success' => true, 'id' => $id); 
			
		}  
		else
		{  
			$r = array('success' => false);
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function closing_Purchasing()
	{
		
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table = $this->prefix.'purchasing';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		//Get Purchasing
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		$get_po = $this->db->get();
		
		//delete data
		$update_data = array(
			'purchasing_status'	=> 'done'
		);
		
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->update($this->table, $update_data);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
			
			$updateAP = $this->account_payable->set_account_payable_Purchasing($id);
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Closing Pembelian Langsung Failed!'); 
        }
		die(json_encode($r));
	}
		
	public function validation_used_Purchasing($purchasing_id = ''){
		$this->table = $this->prefix.'purchasing';
		$this->table2 = $this->prefix.'purchasing_detail';
		
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		if(empty($purchasing_id)){
			return true;
		}
		
		//check data main if been take
		$this->db->from($this->table);
		$this->db->where("id IN ('".$purchasing_id."')");
		$this->db->where("purchasing_status = 'done'");
		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Pembelian Langsung sudah selesai</br>Please Refresh List Purchasing'); 
			die(json_encode($r));			
		}
		
		return true;
	}	
	
	public function delete()
	{
		
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table = $this->prefix.'purchasing';
		$this->table2 = $this->prefix.'purchasing_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		$validation_used_Purchasing = $this->validation_used_Purchasing($sql_Id);		
		
		//Get Purchasing
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		$get_po = $this->db->get();
		
		//delete data
		$update_data = array(
			'purchasing_status'	=> 'cancel',
			'is_deleted'=> 1
		);
		
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->update($this->table, $update_data);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Pembelian Langsung Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function deleteDetail()
	{
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table_purchasing_detail = $this->prefix.'purchasing_detail';
		$this->table_purchasing_kodeunik = $this->prefix.'purchasing_kode_unik';
		
		$purchasing_id = $this->input->post('purchasing_id', true);
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//Get purchasing_id
		$this->db->select('purchasing_id');
		$this->db->from($this->table_purchasing_detail);
		$this->db->where("id IN ('".$sql_Id."')");
		$get_data = $this->db->get();
		$data_purchasing_detail = $get_data->row();
		$purchasing_id = $data_purchasing_detail->purchasing_id;
		
		$validation_used_Purchasing = $this->validation_used_Purchasing($purchasing_id);		
		
		//delete data
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->delete($this->table_purchasing_detail);
		
		$r = '';
		if($q)  
        {  
			//delete kode unik
			$this->db->where("purchasingd_id IN ('".$sql_Id."')");
			$q2 = $this->db->delete($this->table_purchasing_kodeunik);
			
			$purchasing_total_price = $this->get_total_price($purchasing_id);
            $r = array('success' => true, 'purchasing_total_price' => $purchasing_total_price); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Pembelian Langsung Detail Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function get_total_qty($purchasing_id){
		$this->table_purchasing_detail = $this->prefix.'purchasing_detail';	
		
		$this->db->select('SUM(purchasing_detail_qty) as total_qty');
		$this->db->from($this->table_purchasing_detail);
		$this->db->where('purchasing_id', $purchasing_id);
		$get_tot = $this->db->get();
		
		$total_qty = 0;
		if($get_tot->num_rows() > 0){
			$data_po = $get_tot->row();
			$total_qty = $data_po->total_qty;
		}
		
		return $total_qty;
	}
	
	public function get_total_price($purchasing_id){
		$this->table_purchasing_detail = $this->prefix.'purchasing_detail';	
		
		$this->db->select('SUM(purchasing_detail_total) as total_price');
		$this->db->from($this->table_purchasing_detail);
		$this->db->where('purchasing_id', $purchasing_id);
		$get_tot = $this->db->get();
		
		$total_price = 0;
		if($get_tot->num_rows() > 0){
			$data_po = $get_tot->row();
			$total_price = $data_po->total_price;
		}
		
		return $total_price;
	}
	
	public function generate_purchasing_number($is_temp = false){
		$this->table = $this->prefix.'purchasing';		

		$getDate = date("ym");
		$purchasing_number_prefix = 'P-';
		
		$this->db->from($this->table);
		$this->db->where("purchasing_number LIKE '".$purchasing_number_prefix.$getDate."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_po = $get_last->row();
			$purchasing_number = str_replace($purchasing_number_prefix.$getDate,"", $data_po->purchasing_number);
			$purchasing_number = str_replace($purchasing_number_prefix,"", $purchasing_number);
						
			$purchasing_number = (int) $purchasing_number;			
		}else{
			$purchasing_number = 0;
		}
		
		$purchasing_number++;
		$length_no = strlen($purchasing_number);
		switch ($length_no) {
			case 3:
				$purchasing_number = $purchasing_number;
				break;
			case 2:
				$purchasing_number = '0'.$purchasing_number;
				break;
			case 1:
				$purchasing_number = '00'.$purchasing_number;
				break;
			default:
				$purchasing_number = '00'.$purchasing_number;
				break;
		}
			
		
		if($is_temp){
			$id_user = $this->session->userdata('id_user');
			
			$repeat_tot = 3-strlen($id_user);
			if($repeat_tot < 0) { $repeat_tot = 0; }
			
			return $purchasing_number_prefix.$getDate.$purchasing_number.'-'.str_repeat('0', $repeat_tot).$id_user;
		}else{
			return $purchasing_number_prefix.$getDate.$purchasing_number;	
		}
		
					
	}
	
	public function printPurchasing(){
		
		$this->table_purchasing  = $this->prefix.'purchasing'; 
		$this->table_purchasing_detail = $this->prefix.'purchasing_detail';
		$this->table_purchasing_kode_unik = $this->prefix.'purchasing_kode_unik';
		$this->table_client  = config_item('db_prefix').'clients';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		$client_id = $this->session->userdata('client_id');					
		
		//get client
		$this->db->from($this->table_client);
		$this->db->where("id",$client_id);
		$get_client = $this->db->get();
		$dt_client = array();
		if($get_client->num_rows() > 0){
			$dt_client = $get_client->row_array();
		}
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		if(empty($qty_print)){
			$qty_print = 0;
		}
		if(empty($printdetail)){
			$printdetail = 0;
		}
		$printdetail = intval($printdetail);
		
		$data_post = array(
			'do'	=> '',
			'purchasing_data'	=> array(),
			'purchasing_detail'	=> array(),
			'data_kodeunik'	=> array(),
			'report_name'	=> 'FAKTUR PEMBELIAN',
			'report_place_default'	=> '',
			'user_fullname'	=> $user_fullname,
			'client'	=> $dt_client,
			'qty_print'	=> $qty_print
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($purchasing_id)){
			die('Pembelian Langsung Tidak ditemukan!');
		}else{
			
			$this->db->select("a.*, b.supplier_name, b.supplier_code, b.supplier_address, b.supplier_phone, 
			b.supplier_fax, b.supplier_email, b.supplier_contact_person");
			$this->db->from($this->table_purchasing." as a");
			$this->db->join($this->prefix."supplier as b","b.id = a.supplier_id","LEFT");
			$this->db->where("a.id = '".$purchasing_id."'");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['purchasing_data'] = $get_dt->row_array();
				
				$detKodeunik = array();
				//get detail
				$this->db->select("a.*, b.item_code, b.item_name, b.item_type, c.unit_code, c.unit_name");
				$this->db->from($this->table_purchasing_detail." as a");
				$this->db->join($this->prefix."items as b","b.id = a.item_id","LEFT");
				$this->db->join($this->prefix."unit as c","c.id = a.unit_id","LEFT");
				$this->db->where("a.purchasing_id = '".$purchasing_id."'");
				$get_det = $this->db->get();
				if($get_det->num_rows() > 0){
					$data_post['purchasing_detail'] = $get_det->result_array();
					if(!empty($data_post['purchasing_detail'])){
						foreach($data_post['purchasing_detail'] as $dtx){
							if($dtx['use_stok_kode_unik'] == 1){
								$detKodeunik[] = $dtx['id'];
							}
						}
					}
				}
				
				//get SN/IMEI
				$data_kodeunik = array();
				$data_kodeunik_varian = array();
				if(!empty($detKodeunik) AND !empty($printdetail)){
					$detKodeunik_sql = implode(",", $detKodeunik);
					//get purchasing_detail_kode_unik
					$this->db->select("a.*");
					$this->db->from($this->table_purchasing_kode_unik." as a");
					$this->db->where("a.purchasingd_id IN (".$detKodeunik_sql.")");
					$get_detx = $this->db->get();
					if($get_detx->num_rows() > 0){
						foreach($get_detx->result_array() as $dtK){
							if(empty($data_kodeunik[$dtK['purchasingd_id']])){
								$data_kodeunik[$dtK['purchasingd_id']] = array();
							}
							if(empty($data_kodeunik_varian[$dtK['purchasingd_id']])){
								$data_kodeunik_varian[$dtK['purchasingd_id']] = array();
							}
							$data_kodeunik[$dtK['purchasingd_id']][] = array('varian' => $dtK['varian_name'], 'kode_unik' => $dtK['kode_unik']);
							
							
							if(empty($data_kodeunik_varian[$dtK['purchasingd_id']][$dtK['varian_name']])){
								$data_kodeunik_varian[$dtK['purchasingd_id']][$dtK['varian_name']] = array();
							}
							$data_kodeunik_varian[$dtK['purchasingd_id']][$dtK['varian_name']][] = $dtK['kode_unik'];
							
						}
					}
					
				}
				$data_post['data_kodeunik'] = $data_kodeunik;
				$data_post['data_kodeunik_varian'] = $data_kodeunik_varian;
				$data_post['printdetail'] = $printdetail;
				
			}else{
				die('Pembelian Langsung Tidak ditemukan!');
			}
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		$useview = 'printPurchasing';
		if(!empty($tipe)){
			if($tipe == 'excel'){
				$useview = 'excelPurchasing';
			}else
			if($tipe == 'printLX'){
				$useview = 'printPurchasing-LX';
			}
		}
		
		$this->load->view('../../purchase/views/'.$useview, $data_post);
		
	}
	
	
	public function cek_unik_kode_purchasing($all_unik_kode = '', $is_rollback = false){
		$this->table_item_kode_unik = $this->prefix.'item_kode_unik';	
		
		if(!empty($all_unik_kode)){
			
			$all_unik_kode_sql = implode("','", $all_unik_kode);
			if($is_rollback == true){
				$this->table_item_kode_unik = $this->prefix.'item_kode_unik_log';	
				
				$this->db->select('b.kode_unik');
				$this->db->from($this->prefix."item_kode_unik_log as a");
				$this->db->join($this->prefix."item_kode_unik as b","b.id = a.kode_unik_id","LEFT");
				$this->db->where("b.kode_unik IN ('".$all_unik_kode_sql."') AND b.is_deleted = 0 AND b.is_active = 1");
				$this->db->group_by("b.kode_unik");
				$get_cek = $this->db->get();
				if($get_cek->num_rows() > 0){
					$r = array('success' => false, 'info' => $get_cek->num_rows().' Unik Kode (SN/IMEI) sudah digunakan transaksi<br/>Silahkan Gunakan Retur Pembelian'); 
					die(json_encode($r));
				}
				
				return true;				
			}
			
			$this->db->select('id, kode_unik');
			$this->db->from($this->prefix."item_kode_unik");
			$this->db->where("kode_unik IN ('".$all_unik_kode_sql."') AND is_deleted = 0 AND is_active = 1");
			$get_cek = $this->db->get();
			if($get_cek->num_rows() > 0){
				
				$i = 0;
				$all_imei = '';
				foreach($get_cek->result() as $dt){
					$i++;
					if($i < 10){
						
						if($all_imei == ''){
							$all_imei = $dt->kode_unik;
						}else{
							$all_imei .= ', '.$dt->kode_unik;
						}
						
						break;
					}
				}
				
				$r = array('success' => false, 'info' => $get_cek->num_rows().' Unik Kode (SN/IMEI) sudah ada, Cek SN/IMEI berikut<br/>'.$all_imei); 
				die(json_encode($r));
			}
			
			
		}
	}
	
	public function saveKodeUnik(){
		
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table = $this->prefix.'purchasing';				
		$this->table2 = $this->prefix.'purchasing_detail';				
		$this->table3 = $this->prefix.'purchasing_kode_unik';				
		$this->table_items = $this->prefix.'items';				
		$this->table_varian_item = $this->prefix.'varian_item';				
		
		$session_user = $this->session->userdata('user_username');
		$session_client_id = $this->session->userdata('client_id');
		$id_user = $this->session->userdata('id_user');
		
		$purchasing_id = $this->input->post('purchasing_id');
		$purchasingd_id = $this->input->post('purchasingd_id');
		$purchasingd_item_id = $this->input->post('purchasingd_item_id');
		$get_temp_id = $this->input->post('temp_id');
		$kode_unik_id = $this->input->post('kode_unik_id');
		$use_tax = $this->input->post('use_tax');
		$varian_name = $this->input->post('varian_name');
		$kode_unik = $this->input->post('kode_unik');
		$tipe = $this->input->post('tipe');
		
		$varian_name = strtoupper($varian_name);
		if($use_tax == 'true'){
			$use_tax = 1;
		}else{
			$use_tax = 0;
		}
		
		//check data main if been validated
		$storehouse_id = 0;
		$purchasing_date = 0;
		$dt_purchasing = array();
		$this->db->from($this->table);
		$this->db->where("id = ".$purchasing_id);
		//$this->db->where("purchasing_status IN ('done')");
		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			
			$dt_purchasing = $get_dt->row();
			$storehouse_id = $dt_purchasing->storehouse_id;
			$purchasing_date = $dt_purchasing->purchasing_date;
			if($dt_purchasing->purchasing_status == 'done'){
				$r = array('success' => false, 'info' => 'Tidak Bisa Update, Status Pembelian sudah selesai!'); 
				die(json_encode($r));	
			}
				
		}
		
		$temp_id = '';
		if(!empty($get_temp_id)){
			$temp_id = $get_temp_id.'-'.$purchasingd_item_id;
		}
		
		//edit purchasing - detail
		if(!empty($purchasing_id)){
			if(!empty($purchasingd_id)){
				//$temp_id = $purchasingd_id;
				//$purchasingd_id = '';
				$temp_id = '';
			}
		}else{
			
		}
		
		//if(!empty($purchasing_id) AND !empty($purchasingd_id)){
		//	$temp_id = '';
		//}
		
		//if(empty($purchasing_id) AND !empty($purchasingd_id)){
		//	$purchasingd_id = '';
		//}
		
		//if(!empty($purchasing_id) AND empty($purchasingd_id) AND $tipe == 'add'){
		if(!empty($purchasing_id) AND $tipe == 'add'){
			
			$repeat_tot = 3-strlen($id_user);
			if($repeat_tot < 0) { $repeat_tot = 0; }
			
			$get_temp_id = $dt_purchasing->purchasing_number.'-'.str_repeat('0', $repeat_tot).$id_user;
			$temp_id = $get_temp_id.'-'.$purchasingd_item_id;
		}
			
		
		/*if($tipe == 'edit'){
			if((empty($purchasing_id) AND empty($temp_id)) OR empty($kode_unik) OR empty($session_client_id)){
				$r = array('success' => false, 'info' => 'Simpan Kode Unik Gagal!');
				die(json_encode($r));
			}else
			if((!empty($purchasing_id) AND !empty($purchasingd_id)) AND (empty($kode_unik) OR empty($session_client_id))){
				$r = array('success' => false, 'info' => 'Simpan Kode Unik Gagal!');
				die(json_encode($r));
			}	
		}else{
			if(empty($temp_id) AND (empty($kode_unik) OR empty($session_client_id))){
				$r = array('success' => false, 'info' => 'Simpan Kode Unik Gagal!');
				die(json_encode($r));
			}
		}*/
		
		if((empty($kode_unik) OR empty($session_client_id))){
			$r = array('success' => false, 'info' => 'Simpan Kode Unik Gagal!');
			die(json_encode($r));
		}
		
		
		//if((empty($purchasing_id) AND !empty($temp_id) AND $tipe == 'add') OR 
		//(!empty($purchasing_id) AND !empty($temp_id) AND $tipe == 'add') OR 
		//(!empty($purchasing_id) AND !empty($purchasingd_id) AND $tipe == 'edit') AND 
		//!empty($kode_unik)){
			
			$available_info = '';
			$this->db->select("a.*");
			$this->db->from($this->table3." as a");
			if($tipe == 'edit'){
				if(empty($purchasing_id) AND !empty($temp_id)){
					//$tempID_exp = explode("-",$temp_id);
					//unset($tempID_exp[3]);
					//$tempID_imp = implode("-",$tempID_exp);
					//$this->db->where("(a.purchasingd_id = '' AND a.temp_id LIKE '".$temp_id."')");
					$this->db->where("(a.temp_id LIKE '".$temp_id."')");
				}else{
					$this->db->where("a.purchasingd_id = ".$purchasingd_id);
				}
				
			}else{
				//$tempID_exp = explode("-",$temp_id);
				//unset($tempID_exp[3]);
				//$tempID_imp = implode("-",$tempID_exp);
				
				if(empty($purchasing_id) AND !empty($temp_id)){
					//$this->db->where("(a.purchasingd_id = '' AND a.temp_id LIKE '".$temp_id."')");
					$this->db->where("(a.temp_id LIKE '".$temp_id."')");
				}else{
					$this->db->where("a.purchasingd_id = ".$purchasingd_id);
				}
				
			}
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
			
		//}
		
		$var = array('fields'	=>	array(
				'purchasingd_id'=> 	$purchasingd_id,
				'temp_id'		=> 	$temp_id,
				'kode_unik' 	=> 	$kode_unik,
				'use_tax' 		=> 	$use_tax,
				'varian_name' 	=> 	$varian_name,
				'item_id' 		=> 	$purchasingd_item_id,
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
				$update_varian_id = $dt_varian->id;
				
			}else{
				$new_varian = true;
			}	
			
			$data_varian = array(
				'varian_name' 	=> 	$varian_name,
				'is_active' 	=> 	1,
				'is_deleted' 	=> 	0,
			);
			
			if($new_varian == true){
				$this->db->insert($this->table_varian_item,$data_varian);
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
	
	public function deleteKodeUnik()
	{
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table_sto = $this->prefix.'purchasing';
		$this->table_purchasing_kodeunik = $this->prefix.'purchasing_kode_unik';
		
		$purchasing_id = $this->input->post('purchasing_id', true);	
		$purchasingd_id = $this->input->post('purchasingd_id', true);	
		$purchasingd_item_id = $this->input->post('purchasingd_item_id', true);	
		$tipe = $this->input->post('tipe');
		$get_id = $this->input->post('id', true);	
		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}

		$validation_used_Purchasing = $this->validation_used_Purchasing($purchasing_id);
		
		//check data main if been done
		$this->db->where("id IN ('".$purchasing_id."')");
		$this->db->where("purchasing_status = 'done'");
		$get_dt = $this->db->get($this->table_sto);
		if($get_dt->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Tidak bisa hapus data, Status Pembelian sudah selesai!'); 
			die(json_encode($r));			
		}
		
		//delete data
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->delete($this->table_purchasing_kodeunik);
		
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
}