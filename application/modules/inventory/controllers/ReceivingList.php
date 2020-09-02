<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ReceivingList extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->prefix_acc = config_item('db_prefix3');
		$this->load->model('model_receivinglist', 'm');
		$this->load->model('model_receivedetail', 'm2');
		$this->load->model('purchase/model_purchaseorderdetail', 'm3');
		$this->load->model('purchase/model_purchaseorder', 'm4');
		$this->load->model('model_stock', 'stock');
		$this->load->model('account_payable/model_account_payable', 'account_payable');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'receiving';
		
		//receive_status_text
		$sortAlias = array(
			'receive_status_text' => 'a.receive_status'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, b.po_number, c.supplier_name, d.storehouse_name',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'po as b','a.po_id = b.id','LEFT'),
										array($this->prefix.'supplier as c','a.supplier_id = c.id','LEFT'),
										array($this->prefix.'storehouse as d','a.storehouse_id = d.id','LEFT')
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
		$receive_id = $this->input->post('receive_id');
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
				if(empty($date_till)){ $date_till = date('Y-m-t'); }
				
				$mktime_dari = strtotime($date_from);
				$mktime_sampai = strtotime($date_till);
							
				$qdate_from = date("Y-m-d",strtotime($date_from));
				$qdate_till = date("Y-m-d",strtotime($date_till));
				
				$params['where'][] = "(a.receive_date >= '".$qdate_from."' AND a.receive_date <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('b.receive_number' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.receive_number LIKE '%".$searching."%' OR b.po_number LIKE '%".$searching."%' OR c.supplier_name LIKE '%".$searching."%')";
		}
		if(!empty($receive_id)){
			$params['where'] = array('a.id' => $receive_id);
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				if($s['receive_status'] == 'cancel'){
					$s['receive_status_text'] = '<span style="color:red;">Cancel</span>';
				}else
				if($s['receive_status'] == 'done'){
					$s['receive_status_text'] = '<span style="color:green;">Done</span>';
				}else{
					$s['receive_status_text'] = '<span style="color:blue;">Progress</span>';
				}
				
				//$s['total_price_text'] = 'Rp '.priceFormat($s['total_price']);
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetail($get_receive_id = '', $direct = 0)
	{
		
		$this->table = $this->prefix.'receive_detail';
		$this->table_receiving = $this->prefix.'receiving';
		$this->table3 = $this->prefix.'po';
		$this->table4 = $this->prefix.'po_detail';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, a2.receive_status, b.id as item_id_real, b.item_code, b.item_name, 
			b.item_price, b.item_image, b.use_stok_kode_unik, c.unit_name, c.unit_code, a2.receive_number",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'receiving as a2','a.receive_id = a2.id','LEFT'),
										array($this->prefix.'supplier_item as b2','b2.id = a.supplier_item_id','LEFT'),
										array($this->prefix.'items as b','a.item_id = b.id','LEFT'),
										array($this->prefix.'unit as c','a.unit_id = c.id','LEFT')
									) 
								),
			'order'			=> array('a.id' => 'ASC'),
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$receive_id = $this->input->post('receive_id');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('b.item_name' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.item_name LIKE '%".$searching."%')";
		}
		if(!empty($receive_id)){
			$params['where'] = array('a.receive_id' => $receive_id);
		}
		
		if(!empty($get_receive_id)){
			$params['where'] = array('a.receive_id' => $get_receive_id);
		}
		
		//get data -> data, totalCount
		$get_data = $this->m2->find_all($params);
		  
		
		$newData = array();
		$all_po_det_id = array();
		$all_po_det_qty = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['receive_det_purchase_show'] = 'Rp '.priceFormat($s['receive_det_purchase']);
				$s['receive_det_date'] = date('d-m-Y', strtotime($s['receive_det_date']));
				$s['receive_det_qty_before'] = $s['receive_det_qty'];
				$s['item_code_name'] = $s['item_code'].' / '.$s['item_name'];
				
				$s['use_stok_kode_unik_text'] = '<font color="red">Tidak</font>';
				if(!empty($s['use_stok_kode_unik'])){
					$s['use_stok_kode_unik_text'] = '<font color="green">Ya</font>';
				}
				
				if(!in_array($s['po_detail_id'], $all_po_det_id)){
					$all_po_det_id[] = $s['po_detail_id'];
					$all_po_det_qty[$s['po_detail_id']] = array();
				}
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
		$recheck_po_detail = array();
		//PO DETAIL
		if(!empty($all_po_det_id)){
			$all_po_det_id_sql = implode(",", $all_po_det_id);
			$this->db->select("a.*");
			$this->db->from($this->table4." as a");
			$this->db->join($this->table3." as a2","a2.id = a.po_id");
			$this->db->where("a.id IN (".$all_po_det_id_sql.")");
			$this->db->where("a2.is_deleted", 0);
			$get_po_det = $this->db->get();
			if($get_po_det->num_rows() > 0){
				foreach($get_po_det->result() as $det_po){
					
					if($det_po->po_receive_qty > $det_po->po_detail_qty){
						//$det_po->po_receive_qty = $det_po->po_detail_qty;
						if(!in_array($det_po->id, $recheck_po_detail )){
							$recheck_po_detail[] = $det_po->id;
						}
					}
					
					$all_po_det_qty[$det_po->id] = array(
						'po_detail_qty'	=> $det_po->po_detail_qty,
						'po_receive_qty'	=> $det_po->po_receive_qty
					);
				}
			}
		}
		
		if(!empty($recheck_po_detail)){
			
			$all_po_det_rec_qty = array();
			
			$recheck_po_detail_txt = implode(",", $recheck_po_detail);
			$this->db->select("a.*");
			$this->db->from($this->table." as a");
			$this->db->join($this->table_receiving." as b","b.id = a.receive_id","LEFT");
			$this->db->where("b.is_deleted", 0);
			$this->db->where("b.receive_status", "done");
			$this->db->where("a.po_detail_id IN ($recheck_po_detail_txt)");
			$get_rec_det = $this->db->get();
			if($get_rec_det->num_rows() > 0){
				foreach($get_rec_det->result() as $det_rec){
					if(empty($all_po_det_rec_qty[$det_rec->po_detail_id])){
						$all_po_det_rec_qty[$det_rec->po_detail_id] = 0;
					}
					
					$all_po_det_rec_qty[$det_rec->po_detail_id] += $det_rec->receive_det_qty;
				}
			}
		}
		
		if(!empty($all_po_det_rec_qty)){
			$update_po_qty = array();
			foreach($all_po_det_rec_qty as $key => $dt){
				
				if(!empty($all_po_det_qty[$key])){
					$all_po_det_qty[$key]['po_receive_qty'] = $dt;
					
					$update_po_qty[] = array(
						'id'			=> $key,
						'po_receive_qty'=> $dt
					);
				}
				
			}
			
			if(!empty($update_po_qty)){
				$this->db->update_batch($this->table4, $update_po_qty, "id");
			}
			
		}
		
		//echo '<pre>';
		//print_r($recheck_po_detail);
		//print_r($all_po_det_qty);
		//die();
		
		if(!empty($get_data['data'])){
			$newData = array();
			foreach($get_data['data'] as $s){
				
				$s['po_detail_qty'] = 0;
				$s['po_receive_qty'] = 0;
				$s['po_detail_qty_sisa'] = 0;
				if(!empty($all_po_det_qty[$s['po_detail_id']])){
					$s['po_detail_qty'] = $all_po_det_qty[$s['po_detail_id']]['po_detail_qty'];
					
					/*
					if($s['receive_status'] == 'done'){
						$s['po_receive_qty'] = $all_po_det_qty[$s['po_detail_id']]['po_receive_qty'];
					}else{
						$s['po_receive_qty'] = $all_po_det_qty[$s['po_detail_id']]['po_receive_qty'] - $s['receive_det_qty'];
					}
					*/
					
					$s['po_receive_qty'] = $all_po_det_qty[$s['po_detail_id']]['po_receive_qty'];
					
					//echo '<pre>';
					//print_r($s);
		
				}
				
				$s['po_detail_qty_sisa'] = $s['po_detail_qty'] - $s['po_receive_qty'];
				
				array_push($newData, $s);
			}
			$get_data['data'] = $newData;
		}
		
		//die();
		
		if(!empty($direct)){
			return $get_data['data'];
		}
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetailKodeUnik()
	{
		
		$this->table = $this->prefix.'receive_kode_unik';
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
		$receive_id = $this->input->post('receive_id');
		$received_id = $this->input->post('received_id');
		$po_detail_id = $this->input->post('po_detail_id');
		$tipe = $this->input->post('tipe');
		
		//if(!empty($is_dropdown)){
			$params['order'] = array('a.id' => 'DESC');
		//}
		if(!empty($searching)){
			$params['where'][] = "(a.kode_unik LIKE '%".$searching."%')";
		}
		
		//add new
		if($tipe == 'add'){
			$params['where'][] = "(a.received_id = '' AND a.po_detail_id =  ".$po_detail_id.")";
		}else{
			if(empty($received_id)){
				$params['where'][] = "(a.received_id = '' AND a.po_detail_id =  ".$po_detail_id.")";
			}else{
				$params['where'][] = array('a.received_id' => $received_id);
			}
		}
		
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
		$this->table = $this->prefix.'receiving';	
		$this->table2 = $this->prefix.'receive_detail';				
		$this->table3 = $this->prefix.'po';	
		$this->table4 = $this->prefix.'po_detail';				
		$this->table_receive_kode_unik = $this->prefix.'receive_kode_unik';	
		$session_user = $this->session->userdata('user_username');		
		$id_user = $this->session->userdata('id_user');		
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			die(json_encode($r));
		}
		
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$supplier_id = $this->input->post('supplier_id');
		$receive_date = $this->input->post('receive_date');
		$receive_memo = $this->input->post('receive_memo');
		$po_id = $this->input->post('po_id');
		$total_price = $this->input->post('total_price');
		$receive_ship_to = $this->input->post('receive_ship_to');
		$receive_project = $this->input->post('receive_project');
		$receive_status = $this->input->post('receive_status');
		$storehouse_id = $this->input->post('storehouse_id');
		$no_surat_jalan = $this->input->post('no_surat_jalan');
		$form_type_receivingList = $this->input->post('form_type_receivingList', true);
		$receive_id = $this->input->post('id', true);
		
		if(empty($receive_status)){
			$receive_status = 'progress';
		}
		
		if(empty($storehouse_id)){
			$storehouse_id = $this->stock->get_primary_storehouse();
		}
		
		if(!empty($storehouse_id)){
			$this->stock->cek_storehouse_access($storehouse_id);
		}
		
		if(empty($storehouse_id)){
			$r = array('success' => false, 'info' => 'Tidak ditemukan Gudang Utama/Primary! Silahkan setup data gudang'); 
			die(json_encode($r));
		}
		
		$all_unik_kode = array();
		$all_unik_kode_perkey = array();
		$same_unik_kode = array();
		$message_same_unik_kode = array();
		
		$temp_id = 'new-'.$id_user.'-'.$po_id;
		
		//get all detail
		if($form_type_receivingList == 'edit' AND !empty($receive_id)){
			$this->db->select('a.*');
			$this->db->from($this->table_receive_kode_unik.' as a');
			$this->db->join($this->table2.' as b',"b.id = a.received_id","LEFT");
			$this->db->where("b.receive_id = '".$receive_id."'");
			$get_kodeunik = $this->db->get();
		}else{
			$this->db->select('a.*');
			$this->db->from($this->table_receive_kode_unik.' as a');
			$this->db->where("temp_id LIKE '".$temp_id."%'");
			$get_kodeunik = $this->db->get();
		}
		
		$data_kodeunik = array();
		$all_kodeunik = array();
		if($get_kodeunik->num_rows() > 0){
			foreach($get_kodeunik->result_array() as $dt){
				if(!empty($dt['received_id'])){
					$received_id = $dt['received_id'];
				}else{
					//$received_id_exp = explode("-", $dt['temp_id']);
					//unset($received_id_exp[3]);
					//$received_id = implode("-",$received_id_exp);
					$received_id = $dt['temp_id'];
				}
				
				if(empty($data_kodeunik[$received_id])){
					$data_kodeunik[$received_id] = array();
				}
				
				$data_kodeunik[$received_id][] = $dt;
				
			}
		}
		
		
		//receiveDetail
		$receiveDetail = $this->input->post('receiveDetail', true);
		$receiveDetail = json_decode($receiveDetail, true);
		
		$total_receive_item = 0;
		if(!empty($receiveDetail)){
			$total_item = count($receiveDetail);
			foreach($receiveDetail as $key => $dtDet){
				$total_receive_item += $dtDet['receive_det_qty'];
				//$dtDet['data_stok_kode_unik'] = trim($dtDet['data_stok_kode_unik']);
				
				if($form_type_receivingList == 'edit' AND !empty($receive_id)){
					$received_id = $dtDet['id'];
				}else{
					//$received_id_exp = explode("-", $dtDet['temp_id']);
					//unset($received_id_exp[3]);
					//$received_id = implode("-",$received_id_exp);
					$received_id = $dtDet['temp_id'];
				}
				
				//UNIK KODE
				if($dtDet['use_stok_kode_unik'] == 1){
					
					if(!empty($data_kodeunik[$received_id])){
						foreach($data_kodeunik[$received_id] as $dtD){
							if(!in_array($dtD['kode_unik'], $all_unik_kode)){
								$all_unik_kode[] = $dtD['kode_unik'];
								if(empty($all_unik_kode_perkey[$key])){
									$all_unik_kode_perkey[$key] = array();
								}
								$all_unik_kode_perkey[$key][] = $dtD['kode_unik'];
								
							}else{
								$same_unik_kode[] = $dtD['kode_unik'];
								if(empty($message_same_unik_kode)){
									$r = array('success' => false, 'info' => 'Unik Kode (SN/IMEI): <b>'.$dtD['kode_unik'].'</b> lebih dari 1 data<br/>Cek pada Item: '.$dtDet['item_name']); 
									die(json_encode($r));
								}
							}
						}
					}else{
						$r = array('success' => false, 'info' => 'Kode (SN/IMEI) pada Item: '.$dtDet['item_name'].' tidak boleh kosong!'); 
						die(json_encode($r));
					}
					
				}
				
				if(!empty($all_unik_kode_perkey[$key])){
					//$receiveDetail[$key]['data_stok_kode_unik'] = implode("\n", $all_unik_kode_perkey[$key]);
					
					if($dtDet['receive_det_qty'] != count($all_unik_kode_perkey[$key])){
						$r = array('success' => false, 'info' => 'Total Unik Kode (SN/IMEI) pada Item: '.$dtDet['item_name'].' tidak sesuai dengan Total Qty yang diterima'); 
						die(json_encode($r));
					}
				
				}
				
			}
		}
		
		$get_receive_number = $this->generate_receive_number();
		
		if(empty($get_receive_number)){
			$r = array('success' => false);
			die(json_encode($r));
		}	
		
		$warning_update_stok = false;
			
		$r = '';
		if($form_type_receivingList == 'add')
		{
			
			//CLOSING DATE
			$var_closing = array(
				'xdate'	=> $receive_date,
				'xtipe'	=> 'purchasing'
			);
			$is_closing = is_closing($var_closing);
			if($is_closing){
				$r = array('success' => false, 'info' => 'Transaksi untuk Purchasing & Receiving pada tanggal: '.$receive_date.' sudah ditutup!'); 
				die(json_encode($r));
			}
			
			$var = array(
				'fields'	=>	array(
				    'receive_number'  	=> 	$get_receive_number,
				    'supplier_id'  	=> 	$supplier_id,
				    'storehouse_id'  	=> 	$storehouse_id,
				    'receive_date'  => 	$receive_date,
				    'no_surat_jalan'  => 	$no_surat_jalan,
				    'receive_memo'  => 	$receive_memo,
				    'total_qty'  	=> 	0,
				    'total_price'  	=> $total_price,
				    'receive_status'  => $receive_status,
				    'po_id'  		=> 	$po_id,
				    'receive_project'  	=> 	$receive_project,
				    'receive_ship_to'  	=> 	$receive_ship_to,
				    'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				),
				'table'		=>  $this->table
			);	
			
			$do_update_stok = false;
			$do_update_rollback_stok = false;
			$do_update_status_po = false;
			
			$update_stok = '';
			if($receive_status == 'done'){
				
				if(!empty($all_unik_kode)){
					$this->cek_unik_kode($all_unik_kode);
				}
				
				//cek warehouse
				$default_warehouse = $this->stock->get_primary_storehouse();
				if(empty($default_warehouse)){
					$r = array('success' => false, 'info' => 'Tidak ditemukan Gudang Utama/Primary! Silahkan setup data gudang'); 
					die(json_encode($r));
				}
				
				$do_update_stok = true;
				$do_update_status_po = true;
				
				$update_stok = 'update';
				
				if($total_receive_item == 0){
					$r = array('success' => false, 'info' => 'Total Receive item = 0!'); 
					die(json_encode($r));
				}
				
				//check if PO status not done!
				$this->db->from($this->table3);
				$this->db->where("id = '".$po_id."'");
				$this->db->where("po_status = 'done'");
				$get_stat_po = $this->db->get();	
				if($get_stat_po->num_rows() > 0){
					$r = array('success' => false, 'info' => 'Tidak boleh update status ke Done/Selesai<br/>Silahkan Cek Status PO.. Kemungkinan Barang sudah diterima!'); 
					die(json_encode($r));
				}
				
				if($receive_date != date("Y-m-d")){
					$warning_update_stok = true;
				}
				
			}
			
			
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
			$save_data = $this->m->add($var);
			$insert_id = $this->m->get_insert_id();
			$this->lib_trans->commit();			
			if($save_data)
			{  
				$id = $insert_id;
			}
      		
		}else
		if($form_type_receivingList == 'edit'){
			
			
			$var = array('fields'	=>	array(
				    //'supplier_id'  	=> 	$supplier_id,
				    'storehouse_id'  => 	$storehouse_id,
				    'receive_date'  => 	$receive_date,
				    'no_surat_jalan'  => 	$no_surat_jalan,
				    'receive_memo'  => 	$receive_memo,
				    'total_price'  	=> $total_price,
					'receive_status'  => $receive_status,
				    'receive_project'  	=> 	$receive_project,
				    'receive_ship_to'  	=> 	$receive_ship_to,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			//UPDATE
			$id = $this->input->post('id', true);
			
			
			$do_update_stok = false;
			$do_update_rollback_stok = false;
			$do_update_status_po = false;
			
			//CEK OLD DATA
			$this->db->from($this->table);
			$this->db->where("id = '".$id."'");
			$get_dt = $this->db->get();	
			if($get_dt->num_rows() > 0){
				$old_data = $get_dt->row_array();
			}	
			
			//CLOSING DATE
			$var_closing = array(
				//'xdate'	=> $old_data['receive_date'],
				'xdate'	=> $receive_date,
				'xtipe'	=> 'purchasing'
			);
			$is_closing = is_closing($var_closing);
			if($is_closing){
				$r = array('success' => false, 'info' => 'Transaksi untuk Purchasing & Receiving pada tanggal: '.$old_data['receive_date'].' sudah ditutup!'); 
				die(json_encode($r));
			}
			
			
			if($old_data['receive_status'] == 'progress' AND $receive_status == 'done'){
				
				if(!empty($all_unik_kode)){
					$this->cek_unik_kode($all_unik_kode);
				}
				
				//cek warehouse
				$default_warehouse = $this->stock->get_primary_storehouse();
				if(empty($default_warehouse)){
					$r = array('success' => false, 'info' => 'Tidak ditemukan Gudang Utama/Primary! Silahkan setup data gudang'); 
					die(json_encode($r));
				}
				
				$do_update_stok = true;
				$do_update_status_po = true;
				
				if($total_receive_item == 0){
					$r = array('success' => false, 'info' => 'Total Receive item = 0!'); 
					die(json_encode($r));
				}
				
				//check if PO status not done!
				$this->db->from($this->table3);
				$this->db->where("id = '".$po_id."'");
				$this->db->where("po_status = 'done'");
				$get_stat_po = $this->db->get();	
				if($get_stat_po->num_rows() > 0){
					$r = array('success' => false, 'info' => 'Tidak boleh update status ke Done/Selesai<br/>Silahkan Cek Status PO.. Kemungkinan Barang sudah diterima!'); 
					die(json_encode($r));
				}	
				
				if($receive_date != date("Y-m-d")){
					$warning_update_stok = true;
				}
				
			}
			
			if($old_data['receive_status'] == 'done' AND $receive_status == 'progress'){
				
				if(!empty($all_unik_kode)){
					$this->cek_unik_kode($all_unik_kode, true);
				}
				
				$do_update_rollback_stok = true;
				$do_update_status_po = true;
				
				if($receive_date != date("Y-m-d")){
					$warning_update_stok = true;
				}
				
				//CEK PEMBAYARAN AP != kontrabon
				$this->db->from($this->prefix_acc.'account_payable');
				$this->db->where("po_id = '".$po_id."'");
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
			
			$r = array('success' => true, 'id' => $id);
			
			$form_type = $form_type_receivingList;
			//$update_stok = 'update';
					
			//from add
			if($form_type == 'add')
			{
				$q_det = $this->m2->receiveDetail($receiveDetail, $id, $form_type, $data_kodeunik);
				if($q_det == false){
					$r = array('success' => false, 'info' => 'Add Detail Receiving Gagal!'); 
					die(json_encode($r));
				}
			}
				
			$old_status = '';
			if(!empty($old_data['receive_status'])){
				$old_status = $old_data['receive_status'];
			}
				
			if($form_type != 'add' OR ($receive_status == 'done' AND $old_status != 'done')){
				
				//update kode unik
				$this->db->select('a.*');
				$this->db->from($this->table_receive_kode_unik.' as a');
				$this->db->join($this->table2.' as b',"b.id = a.received_id","LEFT");
				$this->db->where("b.receive_id = '".$id."'");
				$get_kodeunik = $this->db->get();
				$data_kodeunik = array();
				if($get_kodeunik->num_rows() > 0){
					foreach($get_kodeunik->result_array() as $dt){
						if(!empty($dt['received_id'])){
							$received_id = $dt['received_id'];
						}else{
							$received_id = $dt['temp_id'];
						}
						
						if(empty($data_kodeunik[$received_id])){
							$data_kodeunik[$received_id] = array();
						}
						
						$data_kodeunik[$received_id][] = $dt;
						
					}
				}
				
				if($receive_status == 'done' AND $old_status != 'done'){
					//get/update ID -> $usageItemDetail
					$item_id_prod = array();
					$this->db->from($this->prefix.'receive_detail');
					$this->db->where("receive_id", $id);
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
					
					$receiveDetail_BU = $receiveDetail;
					$receiveDetail = array();
					foreach($receiveDetail_BU as $dtD){
						
						if(!empty($item_id_prod[$dtD['item_id']])){
							$dtD['id'] = $item_id_prod[$dtD['item_id']];
							$receiveDetail[] = $dtD;
						}
						
					}
					
					//$r['receiveDetail_done'] = $receiveDetail;
					
				}
					
				$q_det = $this->m2->receiveDetail($receiveDetail, $id, $update_stok, $data_kodeunik);
				if($q_det == false){
					$r = array('success' => false, 'info' => 'Update Detail Receiving Gagal!'); 
					die(json_encode($r));
				}
			}
			
			//$r['det_info'] = $q_det;
			
			if($warning_update_stok){
				$r['is_warning'] = 1;
				$r['info'] = 'Silahkan Re-Generate/Perbaiki Stok Transaksi pada List Stock Module!<br/>Perbaiki Stok dari: '.$receive_date;
			}
			
			if(!empty($q_det['dtReceive']['receive_number'])){
				$r['receive_number'] = $q_det['dtReceive']['receive_number'];
			}
			
			if(!empty($q_det['update_stock'])){
				
				$post_params = array(
					'storehouse_item'	=> $q_det['update_stock']
				);
				
				$updateStock = $this->stock->update_stock_rekap($post_params);
				
			}
			
			$updatePO = $this->m4->update_status_PO($po_id);
			$updateAP = $this->account_payable->set_account_payable_PO($po_id);
			//$r['success'] = false;
		}  
		else
		{  
			$r = array('success' => false);
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
		
	public function delete()
	{
		
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table = $this->prefix.'receiving';
		$this->table2 = $this->prefix.'receive_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//Get Receive Data
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		$get_receive = $this->db->get();
		
		//Get Receive Detail
		$this->db->select('*');
		$this->db->from($this->table2);
		$this->db->where("receive_id IN ('".$sql_Id."')");
		$get_receive_detail = $this->db->get();
		
		
		//delete data
		$update_data = array(
			'receive_status'	=> 'cancel',
			'is_deleted'=> 1
		);
		
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->update($this->table, $update_data);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
			
			//delete detail too
			//$this->db->where("receive_id IN ('".$sql_Id."')");
			//$this->db->delete($this->table2);
			
			$this->lib_trans->begin();
				//UPDATE PO Status
				foreach($get_receive->result() as $row){
					$var4 = array('fields'	=>	array(
							'po_status'  => 'progress'
						),
						'table'			=>  $this->prefix.'po',
						'primary_key'	=>  'id'
					);
					$update = $this->m4->save($var4, $row->po_id);					
				}
				
				//UPDATE Stock
				$dtUpdate_Stock = array();
				$dtUpdate_Items = array();
				foreach($get_receive_detail->result_array() as $dt){
				
					$dtUpdate_Stock[] = array(
						"item_id" => $dt['item_id'],
						"trx_ref_det_id" => $dt['id'],
						"is_active" => "0",
					);
					
					//Get Stock Before
					$this->db->select("total_qty_stok")->from($this->prefix."items")->where("id", $dt['item_id']);
					$q_items = $this->db->get();
					$dt_items = $q_items->row();
					$current_stock = $dt_items->total_qty_stok;
				
					$dtUpdate_Items[] = array(
						"id" => $dt['item_id'],
						"total_qty_stok" => $current_stock - $dt['receive_det_qty']
					);
										
				}				
				
				//UPDATE BATCH total Stock
				if(!empty($dtUpdate_Stock)){
					$this->db->update_batch($this->prefix."stock", $dtUpdate_Stock, "trx_ref_det_id");
				}
				
				//UPDATE BATCH total Items
				if(!empty($dtUpdate_Items)){
					$this->db->update_batch($this->prefix."items", $dtUpdate_Items, "id");
				}
				
			$this->lib_trans->commit();
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Hapus Receiving List Gagal!'); 
        }
		die(json_encode($r));
	}
	
	public function deleteDetail()
	{
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table = $this->prefix.'receive_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		//Get receive_id
		$this->db->select('receive_id');
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		$get_data = $this->db->get();
		$data_receive_id = $get_data->row();
		
		//delete data
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->delete($this->table);
		
		$r = '';
		if($q)  
        {  
			$total_price = $this->get_total_price($data_receive_id->receive_id);
            $r = array('success' => true, 'total_price' => $total_price); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Hapus Receiving List Detail Gagal!'); 
        }
		die(json_encode($r));
	}
	
	public function get_total_qty($receive_id){
		$this->table = $this->prefix.'receive_detail';	
		
		$this->db->select('SUM(receive_det_qty) as total_qty');
		$this->db->from($this->table);
		$this->db->where('receive_id', $receive_id);
		$get_tot = $this->db->get();
		
		$total_qty = 0;
		if($get_tot->num_rows() > 0){
			$data_po = $get_tot->row();
			$total_qty = $data_po->total_qty;
		}
		
		return $total_qty;
	}
	
	public function cek_unik_kode($all_unik_kode = '', $is_rollback = false){
		$this->table = $this->prefix.'item_kode_unik';	
		
		if(!empty($all_unik_kode)){
			
			$all_unik_kode_sql = implode("','", $all_unik_kode);
			if($is_rollback == true){
				$this->table = $this->prefix.'item_kode_unik_log';	
				
				$this->db->select('b.kode_unik');
				$this->db->from($this->prefix."item_kode_unik_log as a");
				$this->db->join($this->prefix."item_kode_unik as b","b.id = a.kode_unik","LEFT");
				$this->db->where("b.kode_unik IN ('".$all_unik_kode_sql."') AND is_deleted = 0 AND is_active = 1");
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
	
	public function get_total_price($receive_id){
		$this->table = $this->prefix.'receive_detail';	
		
		$this->db->select('SUM(receive_det_qty * receive_det_purchase) as total_price');
		$this->db->from($this->table);
		$this->db->where('receive_id', $receive_id);
		$get_tot = $this->db->get();
		
		$total_price = 0;
		if($get_tot->num_rows() > 0){
			$data_po = $get_tot->row();
			$total_price = $data_po->total_price;
		}
		
		return $total_price;
	}
	
	public function generate_receive_number(){
		$this->table = $this->prefix.'receiving';						
		
		$default_RL = "RL".date("ym");
		$this->db->from($this->table);
		$this->db->where("receive_number LIKE '".$default_RL."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_rl = $get_last->row();
			$receive_number = str_replace($default_RL,"", $data_rl->receive_number);
						
			$receive_number = (int) $receive_number;			
		}else{
			$receive_number = 0;
		}
		
		$receive_number++;
		$length_no = strlen($receive_number);
		switch ($length_no) {
			case 3:
				$receive_number = $receive_number;
				break;
			case 2:
				$receive_number = '0'.$receive_number;
				break;
			case 1:
				$receive_number = '00'.$receive_number;
				break;
			default:
				$receive_number = $receive_number;
				break;
		}
				
		return $default_RL.$receive_number;				
	}

	public function printReceiving(){
		
		$this->table  = $this->prefix.'receiving'; 
		$this->table2 = $this->prefix.'receive_detail';
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
		
		$data_post = array(
			'do'	=> '',
			'receive_data'	=> array(),
			'receive_detail'	=> array(),
			'report_place_default'	=> '',
			'user_fullname'	=> $user_fullname,
			'client'	=> $dt_client
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($receive_id)){
			die('Receiving List Not Found!');
		}else{
			
			$this->db->select("a.*, b.supplier_name, c.po_number");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix."supplier as b","b.id = a.supplier_id","LEFT");
			$this->db->join($this->prefix."po as c","c.id = a.po_id","LEFT");
			$this->db->where("a.id = '".$receive_id."'");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['receive_data'] = $get_dt->row_array();
				
				//get detail
				$this->db->select("a.*, b.item_code, b.item_name, b.item_type, c.unit_code, c.unit_name");
				$this->db->from($this->table2." as a");
				$this->db->join($this->prefix."items as b","b.id = a.item_id","LEFT");
				$this->db->join($this->prefix."unit as c","c.id = a.unit_id","LEFT");
				$this->db->where("a.receive_id = '".$receive_id."'");
				$get_det = $this->db->get();
				if($get_det->num_rows() > 0){
					$data_post['receive_detail'] = $get_det->result_array();
				}
				
			}else{
				die('Receiving List Not Found!');
			}
		}
		
		//get all receive po_detail
		$all_po_det_id = array();
		if(!empty($data_post['receive_detail'])){
			foreach($data_post['receive_detail'] as $dtR){
				if(!in_array($dtR['po_detail_id'], $all_po_det_id)){
					$all_po_det_id[] = $dtR['po_detail_id'];
				}
			}
		}
		
		if(!empty($all_po_det_id)){
			$all_po_det_id_sql = implode(",", $all_po_det_id);
			$this->db->select("a.*");
			$this->db->from($this->prefix."receive_detail as a");
			$this->db->join($this->prefix."receiving as a2","a2.id = a.receive_id","LEFT");
			$this->db->where("po_detail_id IN (".$all_po_det_id_sql.")");
			$this->db->where("a2.is_deleted", 0);
			$get_rec_po_det = $this->db->get();
			if($get_rec_po_det->num_rows() > 0){
				foreach($get_rec_po_det->result() as $det_rec){
					if(empty($all_receive_po_det_qty[$det_rec->po_detail_id])){
						$all_receive_po_det_qty[$det_rec->po_detail_id] = 0;
					}
						
					$all_receive_po_det_qty[$det_rec->po_detail_id] += $det_rec->receive_det_qty;
				}
			}
		}
		
		$data_post['all_receive_po_det_qty'] = $all_receive_po_det_qty;
		
		//print_r($all_receive_po_det_qty);
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}
		
		$this->load->view('../../inventory/views/printReceiving', $data_post);
		
	}
	
	
	public function saveKodeUnik(){
		
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table = $this->prefix.'receiving';				
		$this->table2 = $this->prefix.'receive_detail';				
		$this->table3 = $this->prefix.'receive_kode_unik';				
		$this->table_items = $this->prefix.'items';				
		$this->table_varian_item = $this->prefix.'varian_item';				
		
		$session_user = $this->session->userdata('user_username');
		$session_client_id = $this->session->userdata('client_id');
		
		$receive_id = $this->input->post('receive_id');
		$received_id = $this->input->post('received_id');
		$temp_id = $this->input->post('temp_id');
		$po_detail_id = $this->input->post('po_detail_id');
		$kode_unik_id = $this->input->post('kode_unik_id');
		$varian_name = $this->input->post('varian_name');
		$kode_unik = $this->input->post('kode_unik');
		$tipe = $this->input->post('tipe');
		
		$varian_name = strtoupper($varian_name);
		
		//check data main if been validated
		$storehouse_id = 0;
		$receive_date = 0;
		$dt_sto = array();
		$this->db->from($this->table);
		$this->db->where("id = ".$receive_id);
		//$this->db->where("receive_status IN ('done')");
		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			
			$dt_sto = $get_dt->row();
			$storehouse_id = $dt_sto->storehouse_id;
			$receive_date = $dt_sto->receive_date;
			if($dt_sto->receive_status == 'done'){
				$r = array('success' => false, 'info' => 'Tidak Bisa Update, Status Receiving sudah selesai!'); 
				die(json_encode($r));	
			}
				
		}
		
		//$temp_id = '';
		if(!empty($receive_id) AND !empty($received_id)){
			//$temp_id = $received_id;
			//$received_id = '';
			$temp_id = '';
		}
		
		if(empty($receive_id) AND !empty($received_id)){
			$received_id = '';
		}
			
		
		if($tipe == 'edit'){
			if((empty($receive_id) AND empty($temp_id)) OR empty($kode_unik) OR empty($session_client_id)){
				$r = array('success' => false, 'info' => 'Simpan Kode Unik Gagal!');
				die(json_encode($r));
			}else
			if((!empty($receive_id) AND !empty($received_id)) AND (empty($kode_unik) OR empty($session_client_id))){
				$r = array('success' => false, 'info' => 'Simpan Kode Unik Gagal!');
				die(json_encode($r));
			}	
		}else{
			if(empty($temp_id) AND (empty($kode_unik) OR empty($session_client_id))){
				$r = array('success' => false, 'info' => 'Simpan Kode Unik Gagal!');
				die(json_encode($r));
			}
		}
		
		
		if((empty($receive_id) AND !empty($temp_id)) OR (!empty($receive_id) AND !empty($received_id)) AND !empty($kode_unik)){
			
			$available_info = '';
			$this->db->select("a.*");
			$this->db->from($this->table3." as a");
			if($tipe == 'edit'){
				if(empty($received_id) AND !empty($temp_id)){
					//$tempID_exp = explode("-",$temp_id);
					//unset($tempID_exp[3]);
					//$tempID_imp = implode("-",$tempID_exp);
					$this->db->where("(a.received_id = '' AND a.temp_id = '".$temp_id."')");
				}else{
					$this->db->where("a.received_id = ".$received_id);
				}
				
			}else{
				//$tempID_exp = explode("-",$temp_id);
				//unset($tempID_exp[3]);
				//$tempID_imp = implode("-",$tempID_exp);
				$this->db->where("(a.received_id = '' AND a.temp_id = '".$temp_id."')");
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
			
		}
		
		$var = array('fields'	=>	array(
				'received_id'		=> 	$received_id,
				'po_detail_id'		=> 	$po_detail_id,
				'temp_id'		=> 	$temp_id,
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
	
	public function deleteKodeUnik()
	{
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table_sto = $this->prefix.'receiving';
		$this->table_receive_kodeunik = $this->prefix.'receive_kode_unik';
		
		$receive_id = $this->input->post('receive_id', true);	
		$received_id = $this->input->post('received_id', true);	
		$po_detail_id = $this->input->post('po_detail_id', true);	
		$tipe = $this->input->post('tipe');
		$get_id = $this->input->post('id', true);	
		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}

		
		//check data main if been done
		$this->db->where("id IN ('".$receive_id."')");
		$this->db->where("receive_status = 'done'");
		$get_dt = $this->db->get($this->table_sto);
		if($get_dt->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Tidak bisa hapus data, Status Receiving sudah selesai!'); 
			die(json_encode($r));			
		}
		
		//delete data
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->delete($this->table_receive_kodeunik);
		
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