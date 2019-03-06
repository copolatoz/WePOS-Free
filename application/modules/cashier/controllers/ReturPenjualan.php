<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ReturPenjualan extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_retur', 'm');
		$this->load->model('model_returdetail', 'm2');
		$this->load->model('purchase/model_purchaseorderdetail', 'm3');
		$this->load->model('purchase/model_purchaseorder', 'm4');
		$this->load->model('inventory/model_stock', 'stock');
		$this->load->model('account_payable/model_account_payable', 'account_payable');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'retur';
		
		//retur_status_text
		$sortAlias = array(
			'retur_status_text' => 'a.retur_status'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, a.id as retur_id, b.storehouse_name',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'storehouse as b','a.storehouse_id = b.id','LEFT')
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
		$retur_id = $this->input->post('retur_id');
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
				
				$params['where'][] = "(a.retur_date >= '".$qdate_from."' AND a.retur_date <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('b.retur_number' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.retur_number LIKE '%".$searching."%')";
		}
		if(!empty($retur_id)){
			$params['where'] = array('a.id' => $retur_id);
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				if($s['retur_status'] == 'cancel'){
					$s['retur_status_text'] = '<span style="color:red;">Cancel</span>';
				}else
				if($s['retur_status'] == 'done'){
					$s['retur_status_text'] = '<span style="color:green;">Done</span>';
				}else{
					$s['retur_status_text'] = '<span style="color:blue;">Progress</span>';
				}
				
				if($s['retur_type'] == 'batal_order'){
					$s['retur_type_text'] = '<span style="color:red;">Batal Order</span>';
				}else{
					$s['retur_type_text'] = '<span style="color:blue;">Barang</span>';
				}
				
				if($s['retur_ref'] == 'pembelian'){
					$s['retur_ref_text'] = '<span style="color:blue;">Purchasing</span>';
				}else
				if($s['retur_ref'] == 'penjualan'){
					$s['retur_ref_text'] = '<span style="color:blue;">Sales/Billing</span>';
				}else
				if($s['retur_ref'] == 'penjualan_so'){
					$s['retur_ref_text'] = '<span style="color:blue;">Sales Order</span>';
				}else{
					$s['retur_ref_text'] = '<span style="color:red;">Unknown</span>';
				}
				
				$s['total_price_show'] = 'Rp '.priceFormat($s['total_price']);
				$s['total_qty_show'] = $s['total_qty'];
				$s['total_tax_show'] = 'Rp '.priceFormat($s['total_tax']);
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function loadBillingSO()
	{
		$this->table_billing = $this->prefix.'billing';
		$this->table_billing_detail = $this->prefix.'billing_detail';
		$this->table_salesorder = $this->prefix.'salesorder';
		$this->table_salesorder_detail = $this->prefix.'salesorder_detail';
		$this->table_retur = $this->prefix.'retur';
		$this->table_retur_detail = $this->prefix.'retur_detail';
		
		$session_user = $this->session->userdata('user_username');
				
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			die(json_encode($r));
		}
		
		//TIPE
		$retur_tipe = $this->input->post('retur_tipe');
		$retur_ref = $this->input->post('retur_ref');
		$ref_no = $this->input->post('ref_no');
		
		if(empty($ref_no)){
			$r = array('success' => false, 'info' => 'Input No Referensi Billing/SO');
			die(json_encode($r));
		}
		
		$no = 0;
		$ref_id = 0;
		$storehouse_id = 0;
		$storehouse_name = '';
		if($retur_ref == 'penjualan_so'){
			
			$this->db->select("a.*, a.id as returd_refd_id, c.item_code, c.item_name, c.item_price, c.item_hpp, d.unit_name, e.storehouse_name");
			$this->db->from($this->table_salesorder_detail.' as a');
			$this->db->join($this->table_salesorder.' as a2','a2.id = a.so_id','LEFT');
			$this->db->join($this->prefix.'items as c','c.id = a.item_id','LEFT');
			$this->db->join($this->prefix.'unit as d','d.id = c.unit_id','LEFT');
			$this->db->join($this->prefix.'storehouse as e','e.id = a.storehouse_id','LEFT');
			$this->db->where("a2.so_status = 'done'");
			$this->db->where("a2.so_number = '".$ref_no."'");
			$get_data = $this->db->get();
			
						
			$newData = array();
			if($get_data->num_rows() > 0){
				
				$total_qty = 0;
				
				foreach ($get_data->result_array() as $s){
					$no++;
					$s['id'] = 'new_'.$no;
					$s['retur_id'] = 0;
					$s['retur_number'] = '';
					$s['item_price_show'] = priceFormat($s['item_price']);
					
					$s['item_qty'] = $s['sod_qty'];
					$s['returd_qty'] = 0;
					$s['returd_total'] = $s['returd_qty']*$s['item_price'];
					$s['returd_total_show'] = priceFormat($s['returd_qty']*$s['item_price']);
					$s['returd_ref_id'] = $s['so_id'];
					$s['returd_refd_id'] = $s['returd_refd_id'];
					
					$s['use_stok_kode_unik'] = $s['use_stok_kode_unik'];
					$s['data_stok_kode_unik'] = $s['data_stok_kode_unik'];
					
					$total_qty += $s['returd_qty'];
					
					if(!empty($s['storehouse_id'])){
						$storehouse_id = $s['storehouse_id'];
						$storehouse_name = $s['storehouse_name'];
					}
					
					if(!empty($s['ref_id'])){
						$ref_id = $s['so_id'];
					}
					
					array_push($newData, $s);
				}
				
				$r = array('success' => true, 'info' => 'Detail No Referensi - OK');
				$r['loadData'] = $newData;
				$r['total_qty'] = $total_qty;
				$r['storehouse_id'] = $storehouse_id;
				$r['storehouse_name'] = $storehouse_name;
				$r['ref_id'] = $ref_id;
				
			}else{
				$r = array('success' => false, 'info' => 'Detail No Referensi tidak ditemukan');
				die(json_encode($r));
			}
			
				
		}else{
			
			
			//cek total retur
			$this->db->select("a.*, a2.retur_number, a2.ref_no, a2.retur_type, a2.storehouse_id, e.storehouse_name");
			$this->db->from($this->table_retur_detail.' as a');
			$this->db->join($this->table_retur.' as a2','a2.id = a.retur_id','LEFT');
			//$this->db->join($this->prefix.'product as b','b.id = a.item_product_id','LEFT');
			$this->db->join($this->prefix.'storehouse as e','e.id = a2.storehouse_id','LEFT');
			$this->db->where("a2.retur_status = 'done'");
			$this->db->where("a2.ref_no = '".$ref_no."'");
			$get_retur = $this->db->get();
			  
			$data_retur_before = array();
			if($get_retur->num_rows() > 0){
				foreach ($get_retur->result_array() as $s){
					if(empty($data_retur_before[$s['item_product_id']])){
						$data_retur_before[$s['item_product_id']] = 0;
					}
					$data_retur_before[$s['item_product_id']] += $s['returd_qty'];
				}
			}
			
			$this->db->select("a.*, a.id as returd_refd_id, b.product_code, b.product_name, b.unit_id, c.unit_code as unit_name");
			$this->db->from($this->table_billing_detail.' as a');
			$this->db->join($this->table_billing.' as a2','a2.id = a.billing_id','LEFT');
			$this->db->join($this->prefix.'product as b','b.id = a.product_id','LEFT');
			$this->db->join($this->prefix.'unit as c','c.id = b.unit_id','LEFT');
			$this->db->where("a2.billing_status = 'paid'");
			$this->db->where("a2.billing_no = '".$ref_no."'");
			$get_data = $this->db->get();
			  
			$ref_id = 0;
			$storehouse_id = 0;
			$newData = array();
			if($get_data->num_rows() > 0){
				
				$total_qty = 0;
				
				foreach ($get_data->result_array() as $s){
					$no++;
					$s['id'] = 'new_'.$no;
					$s['retur_id'] = 0;
					$s['retur_number'] = '';
					$s['unit_id'] = 0;
					$s['unit_name'] = '-';
					$s['item_code'] = $s['product_code'];
					$s['item_name'] = $s['product_name'];
					$s['item_code_name'] = $s['product_code'].'<br/>'.$s['product_name'];
					
					$retur_before = 0;
					if(!empty($data_retur_before[$s['product_id']])){
						$retur_before = $data_retur_before[$s['product_id']];
					}
					
					$returd_tax = ($s['tax_total']+$s['service_total']) / $s['order_qty'];
					
					$s['item_product_id'] = $s['product_id'];
					$s['returd_qty_before'] = $s['order_qty']-$retur_before;
					$s['returd_qty'] = 0;
					$s['returd_qty_sisa'] = $s['returd_qty_before']-$s['returd_qty'];
					$s['returd_price'] = $s['product_price'];
					$s['returd_price_show'] = priceFormat($s['returd_price']);
					$s['returd_hpp'] = $s['product_price_hpp'];
					$s['returd_hpp_show'] = priceFormat($s['returd_hpp']);
					$s['returd_tax'] = $returd_tax;
					$s['returd_tax_show'] = priceFormat($returd_tax);
					$s['returd_total'] = $s['returd_qty']*$s['returd_price'];
					$s['returd_total_show'] = priceFormat($s['returd_total']);
					$s['returd_ref_id'] = $s['billing_id'];
					$s['returd_refd_id'] = $s['returd_refd_id'];
					
					$s['use_stok_kode_unik'] = $s['use_stok_kode_unik'];
					$s['data_stok_kode_unik'] = $s['data_stok_kode_unik'];
					
					$total_qty += $s['returd_qty'];
					
					if(!empty($s['storehouse_id']) AND empty($storehouse_id)){
						$storehouse_id = $s['storehouse_id'];
					}
					
					if(!empty($s['billing_id']) AND empty($ref_id)){
						$ref_id = $s['billing_id'];
					}
					
					array_push($newData, $s);
				}
				
				$r = array('success' => true, 'info' => 'Detail No Referensi - OK');
				$r['loadData'] = $newData;
				$r['total_qty'] = $total_qty;
				$r['storehouse_id'] = $storehouse_id;
				$r['storehouse_name'] = $storehouse_name;
				$r['ref_no'] = $ref_no;
				$r['ref_id'] = $ref_id;
				
				
			}else{
				$r = array('success' => false, 'info' => 'Detail No Referensi tidak ditemukan');
				die(json_encode($r));
			}
			
			
		}
		
		die(json_encode($r));
	}
	
	public function gridDataDetail($get_retur_id = '', $direct = 0)
	{
		
		$this->table = $this->prefix.'retur_detail';
		$this->table_retur = $this->prefix.'retur';
		$this->table3 = $this->prefix.'salesorder';
		$this->table4 = $this->prefix.'salesorder_detail';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		$retur_id = $this->input->post('retur_id');
		$retur_ref = $this->input->post('retur_ref');
		if(empty($retur_ref)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		if(!empty($get_retur_id)){
			$retur_id = $get_retur_id;
		}
		
		// Default Parameter
		if($retur_ref == 'penjualan_so'){
			$params = array(
				'fields'		=> "a.*, a2.retur_status, b.id as item_id_real, b.item_code, b.item_name, 
				b.item_price, b.item_image, b.use_stok_kode_unik, c.unit_name, a2.retur_number",
				'primary_key'	=> 'a.id',
				'table'			=> $this->table.' as a',
				'join'			=> array(
										'many', 
										array( 
											array($this->prefix.'retur as a2','a.retur_id = a2.id','LEFT'),
											array($this->prefix.'items as b','a.item_id = b.id','LEFT'),
											array($this->prefix.'unit as c','b.unit_id = c.id','LEFT')
										) 
									),
				'order'			=> array('a.id' => 'ASC'),
				'single'		=> false,
				'output'		=> 'array' //array, object, json
			);
			
		}else{
			
			$params = array(
				'fields'		=> "a.*, a2.ref_no, a2.retur_type, a2.retur_number, a2.retur_status, b.product_code as item_code, b.product_name as item_name, 
									a.use_stok_kode_unik, b.unit_id, c.unit_code as unit_name",
				'primary_key'	=> 'a.id',
				'table'			=> $this->table.' as a',
				'join'			=> array(
										'many', 
										array( 
											array($this->prefix.'retur as a2','a2.id = a.retur_id','LEFT'),
											array($this->prefix.'product as b','b.id = a.item_product_id','LEFT'),
											array($this->prefix.'unit as c','c.id = b.unit_id','LEFT'),
										) 
									),
				'order'			=> array('a.id' => 'ASC'),
				'single'		=> false,
				'output'		=> 'array' //array, object, json
			);
		}
		
		
		
		if(!empty($retur_id)){
			$params['where'] = array('a.retur_id' => $retur_id);
		}
		
		//get data -> data, totalCount
		$get_data = $this->m2->find_all($params);
		  
		
		$newData = array();
		$ref_no = '';
		$all_returd_ref_id = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['returd_price_show'] = priceFormat($s['returd_price']);
				$s['returd_hpp_show'] = priceFormat($s['returd_hpp']);
				$s['returd_tax_show'] = priceFormat($s['returd_tax']);
				$s['returd_total_show'] = priceFormat($s['returd_total']);
				$s['item_code_name'] = $s['item_code'].'<br/>'.$s['item_name'];
				
				if(empty($ref_no)){
					$ref_no = $s['ref_no'];
				}
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
		
		$all_returd_refd_qty = array();
		if($retur_ref == 'penjualan'){
			
			//get ref order_qty
			if(!empty($ref_no)){
				$this->db->select("a.*");
				$this->db->from($this->prefix."billing_detail as a");
				$this->db->join($this->prefix."billing as a2","a2.id = a.billing_id");
				$this->db->where("a2.billing_no = '".$ref_no."'");
				$this->db->where("a2.is_deleted", 0);
				$get_ref_det = $this->db->get();
				if($get_ref_det->num_rows() > 0){
					foreach($get_ref_det->result() as $refd){
						//billing detail
						if(empty($all_returd_refd_qty[$refd->id])){
							$all_returd_refd_qty[$refd->id] = 0;
						}
						$all_returd_refd_qty[$refd->id] += $refd->order_qty;
					}
				}
				
			}else{
				die(json_encode(array('data' => array(), 'totalCount' => 0)));
			}
			
		}
		
		//get all retur qty
		if(!empty($ref_no)){
			
			$all_retur_qty = array();
			
			$this->db->select("a.*");
			$this->db->from($this->table." as a");
			$this->db->join($this->table_retur." as b","b.id = a.retur_id","LEFT");
			$this->db->where("b.is_deleted", 0);
			$this->db->where("b.retur_status", "done");
			$this->db->where("b.id != '".$retur_id."'");
			$this->db->where("b.ref_no = '".$ref_no."'");
			$this->db->where("b.retur_ref = '".$retur_ref."'");
			$get_retur_done = $this->db->get();
			if($get_retur_done->num_rows() > 0){
				foreach($get_retur_done->result() as $retd){
					
					if(empty($all_retur_qty[$retd->returd_refd_id])){
						$all_retur_qty[$retd->returd_refd_id] = 0;
					}
					
					$all_retur_qty[$retd->returd_refd_id] += $retd->returd_qty;
					
				}
			}
			
		}else{
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		if(!empty($get_data['data'])){
			$newData = array();
			foreach($get_data['data'] as $s){
				
				if($s['retur_status'] == 'done'){
					$order_qty = $s['returd_qty_before'];
				}else{
					//all order qty 
					$order_qty = 0;
					if(!empty($all_returd_refd_qty[$s['returd_refd_id']])){
						$order_qty = $all_returd_refd_qty[$s['returd_refd_id']];
					}
					
					//all retur = done
					$retur_qty_done = 0;
					if(!empty($all_retur_qty[$s['returd_refd_id']])){
						$retur_qty_done = $all_retur_qty[$s['returd_refd_id']];
						$order_qty -= $retur_qty_done;
					}
					
					$s['returd_qty_before'] = $order_qty;
				}
				
				$s['returd_qty_sisa'] = $order_qty - $s['returd_qty'];
				
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
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'retur';	
		$this->table2 = $this->prefix.'retur_detail';			
		$this->table3 = $this->prefix.'salesorder';	
		$this->table4 = $this->prefix.'salesorder_detail';			
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			die(json_encode($r));
		}
		
		
		$total_qty = $this->input->post('total_qty');
		$total_tax = $this->input->post('total_tax');
		$total_price = $this->input->post('total_price');
		$retur_date = $this->input->post('retur_date');
		$retur_memo = $this->input->post('retur_memo');
		$retur_ref = $this->input->post('retur_ref');
		$ref_no = $this->input->post('ref_no');
		$retur_status = $this->input->post('retur_status');
		$retur_type = $this->input->post('retur_type');
		$storehouse_id = $this->input->post('storehouse_id');
		
		if(empty($retur_status)){
			$retur_status = 'progress';
		}
		
		if(empty($retur_type)){
			$retur_type = 'barang';
		}
		
		if(empty($storehouse_id)){
			$storehouse_id = $this->stock->get_primary_storehouse();
		}
		
		if(empty($storehouse_id)){
			$r = array('success' => false, 'info' => 'Tidak ditemukan Gudang Utama/Primary! Silahkan setup data gudang'); 
			die(json_encode($r));
		}
		
		$all_unik_kode = array();
		$all_unik_kode_perkey = array();
		$same_unik_kode = array();
		$message_same_unik_kode = array();
		
		//returDetail
		$returDetail = $this->input->post('returDetail');
		$returDetail = json_decode($returDetail, true);
		$total_retur_item = 0;
		if(!empty($returDetail)){
			$total_item = count($returDetail);
			foreach($returDetail as $key => $dtDet){
				$total_retur_item += $dtDet['returd_qty'];
				
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
					$returDetail[$key]['data_stok_kode_unik'] = implode("\n", $all_unik_kode_perkey[$key]);
					
					if(!empty($dtDet['returd_qty']) AND $dtDet['returd_qty'] != count($all_unik_kode_perkey[$key])){
						$r = array('success' => false, 'info' => 'Total Unik Kode (SN/IMEI) pada Item: '.$dtDet['item_name'].' tidak sesuai dengan Total Qty yang diterima'); 
						die(json_encode($r));
					}
				
				}
				
			}
		}
		
		
		$get_retur_number = $this->generate_retur_number();
		
		if(empty($get_retur_number)){
			$r = array('success' => false);
			die(json_encode($r));
		}	
		
		$warning_update_stok = false;
			
		$r = '';
		if($this->input->post('form_type_returPenjualan', true) == 'add')
		{
			$date_now = date("Y-m-d");
			//CLOSING DATE
			$var_closing = array(
				'xdate'	=> $date_now,
				'xtipe'	=> 'sales'
			);
			$is_closing = is_closing($var_closing);
			if($is_closing){
				$r = array('success' => false, 'info' => 'Transaksi untuk Penjualan dan Retur pada tanggal: '.$date_now.' sudah ditutup!'); 
				die(json_encode($r));
			}
			
			$var = array(
				'fields'	=>	array(
				    'retur_number'  => $get_retur_number,
				    'retur_type'  	=> $retur_type,
				    'retur_date'  	=> $retur_date,
				    'retur_memo'  	=> $retur_memo,
				    'total_qty'  	=> $total_qty,
				    'total_tax'  	=> $total_tax,
				    'total_price'  	=> $total_price,
				    'retur_ref'  	=> $retur_ref,
				    'created'		=> date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'ref_no'  		=> $ref_no,
				    'retur_status'  => $retur_status,
				    'storehouse_id' => $storehouse_id,
				),
				'table'		=>  $this->table
			);	
			
			$do_update_stok = false;
			$do_update_rollback_stok = false;
			$do_update_status_retur = false;
			
			$update_stok = '';
			if($retur_status == 'done'){
				
				//cek warehouse
				$default_warehouse = $this->stock->get_primary_storehouse();
				if(empty($default_warehouse)){
					$r = array('success' => false, 'info' => 'Tidak ditemukan Gudang Utama/Primary! Silahkan setup data gudang'); 
					die(json_encode($r));
				}
				
				$do_update_stok = true;
				$do_update_status_retur = true;
				
				$update_stok = 'update';
				
				if($total_retur_item == 0){
					$r = array('success' => false, 'info' => 'Total Retur item = 0!'); 
					die(json_encode($r));
				}
				
				if($retur_date != date("Y-m-d")){
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
		if($this->input->post('form_type_returPenjualan', true) == 'edit'){
			
			
			$var = array('fields'	=>	array(
				    'retur_date'  	=> $retur_date,
				    'retur_memo'  	=> $retur_memo,
				    'total_qty'  	=> $total_qty,
				    'total_tax'  	=> $total_tax,
				    'total_price'  	=> $total_price,
					'retur_status'  => $retur_status,
				    'retur_type'  	=> $retur_type,
					'updated'		=> date('Y-m-d H:i:s'),
					'updatedby'		=> $session_user
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			//UPDATE
			$id = $this->input->post('id', true);
			
			$do_update_stok = false;
			$do_update_rollback_stok = false;
			$do_update_status_retur = false;
			
			//CEK OLD DATA
			$this->db->from($this->table);
			$this->db->where("id = '".$id."'");
			$get_dt = $this->db->get();	
			if($get_dt->num_rows() > 0){
				$old_data = $get_dt->row_array();
			}	
			
			//CLOSING DATE
			$var_closing = array(
				'xdate'	=> $old_data['retur_date'],
				'xtipe'	=> 'sales'
			);
			$is_closing = is_closing($var_closing);
			if($is_closing){
				$r = array('success' => false, 'info' => 'Transaksi untuk Penjualan dan Retur pada tanggal: '.$old_data['retur_date'].' sudah ditutup!'); 
				die(json_encode($r));
			}
			
			
			if($old_data['retur_status'] == 'progress' AND $retur_status == 'done'){
				
				
				//cek warehouse
				$default_warehouse = $this->stock->get_primary_storehouse();
				if(empty($default_warehouse)){
					$r = array('success' => false, 'info' => 'Tidak ditemukan Gudang Utama/Primary! Silahkan setup data gudang'); 
					die(json_encode($r));
				}
				
				$do_update_stok = true;
				$do_update_status_retur = true;
				
				if($total_retur_item == 0){
					$r = array('success' => false, 'info' => 'Total Retur item = 0!'); 
					die(json_encode($r));
				}
				
				if($retur_date != date("Y-m-d")){
					$warning_update_stok = true;
				}
				
			}
			
			if($old_data['retur_status'] == 'done' AND $retur_status == 'progress'){
				$do_update_rollback_stok = true;
				$do_update_status_retur = true;
				
				if($retur_date != date("Y-m-d")){
					$warning_update_stok = true;
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
			
			$q_det = $this->m2->returDetail($returDetail, $id);
			
			$old_status = '';
			if(!empty($old_data['retur_status'])){
				$old_status = $old_data['retur_status'];
			}
			
			if($retur_status == 'done' AND $old_status != 'done'){
				
				$item_id_prod = array();
				$this->db->from($this->prefix.'retur_detail');
				$this->db->where("retur_id", $id);
				$get_det = $this->db->get();
				if($get_det->num_rows() > 0){
					foreach($get_det->result_array() as $dt){
						$var_detail_id = $dt['returd_refd_id'].'-'.$dt['item_product_id'];
						$item_id_prod[$var_detail_id] = $dt['id'];
					}
				}
				
				//$update_stok = 'update_add';
				$update_stok = 'update';
				
				$returDetail_BU = $returDetail;
				$returDetail = array();
				foreach($returDetail_BU as $dtD){
					
					$var_detail_id = $dtD['returd_refd_id'].'-'.$dtD['item_product_id'];
						
					if(!empty($item_id_prod[$var_detail_id])){
						$dtD['id'] = $item_id_prod[$var_detail_id];
						$returDetail[] = $dtD;
					}
					
				}
				
				$r['returDetail_done'] = $returDetail;
			}
			
				
			$q_det = $this->m2->returDetail($returDetail, $id, $update_stok);
			if($q_det == false){
				$r = array('success' => false, 'info' => 'Input Retur Detail Gagal!'); 
				die(json_encode($r));
			}
			
			$r['det_info'] = $q_det;
			
			if($warning_update_stok){
				$r['is_warning'] = 1;
				$r['info'] = 'Silahkan Re-Generate/Perbaiki Stok Transaksi pada List Stock Module!<br/>Perbaiki Stok dari: '.$retur_date;
			}
			
			if(!empty($q_det['dtRetur']['retur_number'])){
				$r['retur_number'] = $q_det['dtRetur']['retur_number'];
			}
			
			if(!empty($q_det['update_stock'])){
				
				$post_params = array(
					'storehouse_item'	=> $q_det['update_stock']
				);
				
				$updateStock = $this->stock->update_stock_rekap($post_params);
				
			}
			
		}  
		else
		{  
			$r = array('success' => false);
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
		
	public function delete()
	{
		
		$this->table = $this->prefix.'retur';
		$this->table2 = $this->prefix.'retur_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//Get Retur Data
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		$get_receive = $this->db->get();
		
		//Get Retur Detail
		$this->db->select('*');
		$this->db->from($this->table2);
		$this->db->where("retur_id IN ('".$sql_Id."')");
		$get_retur_detail = $this->db->get();
		
		
		//delete data
		$update_data = array(
			'retur_status'	=> 'cancel',
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
            $r = array('success' => false, 'info' => 'Hapus Retur List Gagal!'); 
        }
		die(json_encode($r));
	}
	
	public function deleteDetail()
	{
		
		$this->table = $this->prefix.'retur_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		//Get retur_id
		$this->db->select('retur_id');
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		$get_data = $this->db->get();
		$data_retur_id = $get_data->row();
		
		//delete data
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->delete($this->table);
		
		$r = '';
		if($q)  
        {  
			$total_price = $this->get_total_price($data_retur_id->retur_id);
            $r = array('success' => true, 'total_price' => $total_price); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Hapus Retur List Detail Gagal!'); 
        }
		die(json_encode($r));
	}
	
	public function generate_retur_number(){
		$this->table = $this->prefix.'retur';						
		
		$default_RET = "RET".date("ym");
		$this->db->from($this->table);
		$this->db->where("retur_number LIKE '".$default_RET."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_rl = $get_last->row();
			$retur_number = str_replace($default_RET,"", $data_rl->retur_number);
						
			$retur_number = (int) $retur_number;			
		}else{
			$retur_number = 0;
		}
		
		$retur_number++;
		$length_no = strlen($retur_number);
		switch ($length_no) {
			case 3:
				$retur_number = $retur_number;
				break;
			case 2:
				$retur_number = '0'.$retur_number;
				break;
			case 1:
				$retur_number = '00'.$retur_number;
				break;
			default:
				$retur_number = $retur_number;
				break;
		}
				
		return $default_RET.$retur_number;				
	}

	public function printRetur(){
		
		$this->table  = $this->prefix.'retur'; 
		$this->table2 = $this->prefix.'retur_detail';
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
			'retur_data'	=> array(),
			'retur_detail'	=> array(),
			'report_place_default'	=> '',
			'user_fullname'	=> $user_fullname,
			'client'	=> $dt_client
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($retur_id)){
			die('Retur List Not Found!');
		}else{
			
			$this->db->select("a.*, b.customer_code, b.customer_name, b.customer_contact_person, b.customer_address, b.customer_phone, c.storehouse_code, c.storehouse_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix."customer as b","b.id = a.customer_id","LEFT");
			$this->db->join($this->prefix."storehouse as c","c.id = a.storehouse_id","LEFT");
			$this->db->where("a.id = '".$retur_id."'");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$retur_data = $get_dt->row_array();
				
				$retur_data['retur_ref_text'] = 'Sales/Billing';
				if($retur_data['retur_ref'] == 'penjualan_so'){
					$retur_data['retur_ref_text'] = 'Sales Order';
				}
				
				$retur_data['retur_type_text'] = ucwords($retur_data['retur_type']);
				if($retur_data['retur_type'] == 'batal_order'){
					$retur_data['retur_type_text'] = 'Batal Order';
				}
				
				if($retur_data['retur_ref'] == 'penjualan_so'){
					//get detail
					$this->db->select("a.*");
					$this->db->from($this->table2." as a");
					$this->db->where("a.retur_id = '".$retur_id."'");
					$get_det = $this->db->get();
					if($get_det->num_rows() > 0){
						$data_post['retur_detail'] = $get_det->result_array();
					}
					
				}else{
					
					$all_id_product = array();
					$all_id_product_item = array();
					$all_id_package = array();
					$all_id_package_varian = array();
					$all_detail_item_package = array();
					$all_detail_varian_id= array();
					$all_detail_product_varian_id= array();
					
					//get detail
					$this->db->select("a.*, b.varian_id, b.product_varian_id, c.product_type, c.product_code as item_code, c.product_name as item_name, d.varian_name");
					$this->db->from($this->table2." as a");
					$this->db->join($this->prefix."billing_detail as b","b.id = a.returd_refd_id","LEFT");
					$this->db->join($this->prefix."product as c","c.id = b.product_id","LEFT");
					$this->db->join($this->prefix."varian as d","d.id = b.varian_id","LEFT");
					$this->db->where("a.retur_id = '".$retur_id."'");
					$get_det = $this->db->get();
					if($get_det->num_rows() > 0){
						$retur_detail = $get_det->result_array();
						$data_post['retur_detail'] = $retur_detail;
						
						foreach($retur_detail as $dt){
							
							$all_detail_item_package[$dt['returd_refd_id']] = $dt['product_type'];
							$all_detail_varian_id[$dt['returd_refd_id']] = $dt['varian_id'];
							$all_detail_product_varian_id[$dt['returd_refd_id']] = $dt['product_varian_id'];
							
							if($dt['product_type'] == 'package'){
								
								$all_id_package[$dt['returd_refd_id']] = $dt['item_product_id'];
								$all_id_package_varian[$dt['returd_refd_id']] = $dt['product_varian_id'];
								
							}else{
								
								$all_id_product_item[$dt['returd_refd_id']] = $dt['item_product_id'];
								
								if(!in_array($dt['item_product_id'], $all_id_product)){
									$all_id_product[] = $dt['item_product_id'];
								}
							}
						}
						
					}
					$data_post['all_detail_item_package'] = $all_detail_item_package;
					
					//get ref detail item from package
					$product_package = array();
					$product_package_varian = array();
					$product_package_varian_peritem = array();
					$product_package_varian_productvarianid = array();
					$all_product_package_name = array();
					if(!empty($all_id_package)){
						
						$all_id_package_sql = implode(",", $all_id_package);
						
						$this->db->select("a.*, b.product_code, b.product_name, c.varian_name");
						$this->db->from($this->prefix."product_package as a");
						$this->db->join($this->prefix."product as b","b.id = a.product_id","LEFT");
						$this->db->join($this->prefix."varian as c","c.id = a.varian_id_item","LEFT");
						$this->db->where("a.package_id IN (".$all_id_package_sql.")");
						$this->db->where("a.is_deleted", 0);
						$get_package_detail = $this->db->get();
						if($get_package_detail->num_rows() > 0){
							foreach($get_package_detail->result() as $dpackage){
								
								if(empty($product_package[$dpackage->package_id])){
									$product_package[$dpackage->package_id] = array();
									$product_package_varian[$dpackage->package_id] = array();
									$product_package_varian_peritem[$dpackage->package_id] = array();
									$product_package_varian_productvarianid[$dpackage->package_id] = array();
								}
								
								if(empty($product_package_varian[$dpackage->package_id][$dpackage->product_varian_id])){
									$product_package_varian[$dpackage->package_id][$dpackage->product_varian_id] = array();
									$product_package_varian_peritem[$dpackage->package_id][$dpackage->product_varian_id] = array();
									$product_package_varian_productvarianid[$dpackage->package_id][$dpackage->product_varian_id] = array();
								}
								
								$product_package[$dpackage->package_id][] = $dpackage->product_id;
								$product_package_varian[$dpackage->package_id][$dpackage->product_varian_id][] = $dpackage->product_id;
								$product_package_varian_peritem[$dpackage->package_id][$dpackage->product_varian_id][$dpackage->product_id] = $dpackage->varian_id_item;
								$product_package_varian_productvarianid[$dpackage->package_id][$dpackage->product_varian_id][$dpackage->product_id] = $dpackage->product_varian_id_item;
								//echo 'item_product_id = '.$dpackage->package_id.', product_varian_id = '.$dpackage->product_varian_id.', product_id = '.$dpackage->product_id.', varian_id = '.$dpackage->varian_id.'<br/>'; 
								//echo 'varian_id = '.$product_package_varian_productvarianid[$dpackage->package_id][$dpackage->product_varian_id][$dpackage->product_id].'<br/>';
								if(!in_array($dpackage->product_id, $all_id_product)){
									$all_id_product[] = $dpackage->product_id;
									$all_product_package_name[$dpackage->product_id] = $dpackage;
								}
								
							}
						}
						
					}
					$data_post['product_package'] = $product_package;
					$data_post['product_package_varian'] = $product_package_varian;
					$data_post['product_package_varian_peritem'] = $product_package_varian_peritem;
					$data_post['all_product_package_name'] = $all_product_package_name;
					$data_post['product_package_varian_productvarianid'] = $product_package_varian_productvarianid;
					//print_r($all_id_product);
					//get gramasi product
					$product_gramasi = array();
					$product_gramasi_varian = array();
					if(!empty($all_id_product)){
						
						$all_id_product_sql = implode(",", $all_id_product);
						$this->db->select("a.*, b.item_code, b.item_name, b.unit_id, c.unit_code, c.unit_name");
						$this->db->from($this->prefix."product_gramasi as a");
						$this->db->join($this->prefix."items as b","b.id = a.item_id","LEFT");
						$this->db->join($this->prefix."unit as c","c.id = b.unit_id","LEFT");
						$this->db->where("a.product_id IN (".$all_id_product_sql.")");
						$this->db->where("a.is_deleted = 0");
						$get_product_gramasi = $this->db->get();
						if($get_product_gramasi->num_rows() > 0){
							foreach($get_product_gramasi->result() as $dtgramasi){
								
								if(empty($product_gramasi[$dtgramasi->product_id])){
									$product_gramasi[$dtgramasi->product_id] = array();
									$product_gramasi_varian[$dtgramasi->product_id] = array();
								}
								
								if(empty($product_gramasi_varian[$dtgramasi->product_id][$dtgramasi->product_varian_id])){
									$product_gramasi_varian[$dtgramasi->product_id][$dtgramasi->product_varian_id] = array();
								}
								
								$product_gramasi[$dtgramasi->product_id][] = $dtgramasi;
								$product_gramasi_varian[$dtgramasi->product_id][$dtgramasi->product_varian_id][] = $dtgramasi;
								
							}
						}
						
					}
					$data_post['product_gramasi'] = $product_gramasi;
					$data_post['product_gramasi_varian'] = $product_gramasi_varian;
					
				}
				
				$data_post['retur_data'] = $retur_data;
				
			}else{
				die('Retur List Not Found!');
			}
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}
		
		if(!empty($is_lx)){
			$this->load->view('../../cashier/views/printReturLX', $data_post);
		}else{
			$this->load->view('../../cashier/views/printRetur', $data_post);
		}
		
		
	}
}