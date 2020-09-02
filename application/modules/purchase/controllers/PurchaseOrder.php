<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class PurchaseOrder extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_purchaseorder', 'm');
		$this->load->model('model_purchaseorderdetail', 'm2');
		$this->load->model('model_requestorderdetail', 'm3');
		$this->load->model('model_requestorder', 'm4');
		$this->load->model('account_payable/model_account_payable', 'account_payable');
	}
	
	public function gridData()
	{
		$this->table = $this->prefix.'po';
		$this->table_receiving = $this->prefix.'receiving';
		
		$use_approval_po = 0;
		$get_opt = get_option_value(array("use_approval_po"));
		if(!empty($get_opt['use_approval_po'])){
			$use_approval_po = $get_opt['use_approval_po'];
		}
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'a.is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, c.supplier_name',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'supplier as c','a.supplier_id = c.id','LEFT')
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
		$po_status = $this->input->post('po_status');
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
				
				$params['where'][] = "(a.po_date >= '".$qdate_from."' AND a.po_date <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.po_number' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.po_number LIKE '%".$searching."%' OR a.supplier_invoice LIKE '%".$searching."%' OR c.supplier_name LIKE '%".$searching."%')";
		}		
		if(!empty($is_active)){
			$params['where'][] = "a.is_active = '".$is_active."'";
		}
		if(!empty($po_status)){
			$params['where'][] = "a.po_status = '".$po_status."'";
		}
		if(!empty($not_cancel)){
			$params['where'][] = "a.po_status != 'cancel'";
		}
		if($is_rl == 1 AND $use_approval_po == 1){
			$params['where'][] = "((a.approval_status = 'done'  AND a.use_approval = 1) OR (a.approval_status = 'done'  AND a.use_approval = 0))";
		}
		
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		$get_data['use_approval_po'] = $use_approval_po;
		  		
  		$newData = array();
		
		$all_po_id = array();
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				if($s['po_status'] == 'open'){
					$s['po_status_text'] = '<span style="color:orange;">Open</span>';
				}
				else if($s['po_status'] == 'progress'){
					$s['po_status_text'] = '<span style="color:blue;">Progress</span>';
				}else{
					$s['po_status_text'] = '<span style="color:green;">Done</span>';
				}
				
				if($s['approval_status'] == 'progress'){
					$s['approval_status_text'] = '<span style="color:blue;">Progress</span>';
				}else{
					$s['approval_status_text'] = '<span style="color:green;">Done</span>';
				}
				
				$s['payment_note'] = ucfirst($s['po_payment']);
				$s['po_date_txt'] = date("d-m-Y",strtotime($s['po_date']));
				$s['po_sub_total_text'] = 'Rp '.priceFormat($s['po_sub_total']);
				$s['po_total_price_text'] = 'Rp '.priceFormat($s['po_total_price']);
				$s['po_discount_text'] = 'Rp '.priceFormat($s['po_discount']);
				$s['po_tax_text'] = 'Rp '.priceFormat($s['po_tax']);
				
				$s['use_approval'] = $use_approval_po;
				
				if(!in_array($s['id'], $all_po_id)){
					$all_po_id[] = $s['id'];
				}
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
		if(!empty($all_po_id)){
			$all_po_id_txt = implode(",", $all_po_id);
			
			//check used on receiving
			//status progress/done -> is_deleted = 0
			$po_receiving = array();
			$po_receiving_progress = array();
			$this->db->from($this->table_receiving);
			$this->db->where("po_id IN (".$all_po_id_txt.")");
			$this->db->where("is_deleted = 0");
			$get_dt_rec = $this->db->get();
			if($get_dt_rec->num_rows() > 0){
				foreach($get_dt_rec->result() as $dtR){
					if(!in_array($dtR->po_id, $po_receiving)){
						$po_receiving[] = $dtR->po_id;
					}
					
					if($dtR->receive_status == 'progress'){
						if(!in_array($dtR->po_id, $po_receiving_progress)){
							$po_receiving_progress[] = $dtR->po_id;
						}
					}
					
				}		
			}
			
			//echo '<pre>';
			//print_r($po_receiving_progress);
			//die();
			
			//if(!empty($po_receiving)){
				$newData = array();
				
				if(!empty($get_data['data'])){
					foreach ($get_data['data'] as $s){
						
						$s['used_on_receiving'] = 0;
						
						if(in_array($s['id'], $po_receiving)){
							$s['used_on_receiving'] = 1;
						}
						
						$s['po_number_rl_status'] = $s['po_number'];
						if(in_array($s['id'], $po_receiving_progress)){
							$s['po_number_rl_status'] = $s['po_number'].' (Progress)';
						}
						
						/*if(!empty($is_rl)){
							if(!in_array($s['id'], $po_receiving_progress)){
								array_push($newData, $s);
							}
						}else{
							array_push($newData, $s);
						}*/
						array_push($newData, $s);
					}
				}
				
				$get_data['data'] = $newData;
			//}
			
		}
		
		
		
      	die(json_encode($get_data));
	}
	
	public function use_approval_po()
	{
		$use_approval_po = 0;
		$approval_change_payment_po_done = 0;
		$get_opt = get_option_value(array("use_approval_po","approval_change_payment_po_done"));
		
		$get_data = array('success' => false, 'info' => 'load failed', 'use_approval_po' => $use_approval_po);
		if(!empty($get_opt['use_approval_po'])){
			$use_approval_po = $get_opt['use_approval_po'];
		}
		if(!empty($get_opt['approval_change_payment_po_done'])){
			$approval_change_payment_po_done = $get_opt['approval_change_payment_po_done'];

		}
		$get_data = array('success' => true, 'info' => 'load success', 'use_approval_po' => $use_approval_po, 'approval_change_payment_po_done' => $approval_change_payment_po_done);

		die(json_encode($get_data));
	}
	
	public function gridDataDetail()
	{
		
		$this->table = $this->prefix.'po_detail';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, b.item_code, b.item_name, b.item_price, b2.item_price as item_price_supplier, b.item_image, b.use_stok_kode_unik, c.unit_name, c.unit_code, e.ro_number",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'supplier_item as b2','b2.id = a.supplier_item_id','LEFT'),
										array($this->prefix.'items as b','a.item_id = b.id','LEFT'),
										array($this->prefix.'unit as c','a.unit_id = c.id','LEFT'),
										array($this->prefix.'ro_detail as d','d.id = a.ro_detail_id','LEFT'),
										array($this->prefix.'ro as e','e.id = d.ro_id','LEFT')
									) 
								),
			'order'			=> array('a.id' => 'DESC'),
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$po_id = $this->input->post('po_id');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('b.item_name' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.item_name LIKE '%".$searching."%')";
		}
		if(!empty($po_id)){
			$params['where'] = array('a.po_id' => $po_id);
		}
		
		//get data -> data, totalCount
		$get_data = $this->m2->find_all($params);
		  		
		$newData = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['po_detail_purchase_show'] = 'Rp '.priceFormat($s['po_detail_purchase']);
				$s['po_detail_potongan_show'] = 'Rp '.priceFormat($s['po_detail_potongan']);
				$s['po_detail_tax_show'] = 'Rp '.priceFormat($s['po_detail_tax']);
				$s['po_detail_total_show'] = 'Rp '.priceFormat($s['po_detail_total']);
				$s['item_id_real'] = $s['item_id'];
				
				if(empty($s['po_receive_qty'])){
					$s['po_receive_qty'] = 0;
				}
				
				$potongan_per_qty = $s['po_detail_potongan'] / $s['po_detail_qty'];
				$tax_per_qty = $s['po_detail_tax'] / $s['po_detail_qty'];
				
				//$s['po_receive_total'] = ($s['po_receive_qty']*$s['po_detail_purchase']);
				$s['po_receive_total'] = ($s['po_receive_qty']*$s['po_detail_purchase']);
				$s['po_receive_total'] -= ($s['po_receive_qty']*$potongan_per_qty);
				$s['po_receive_total'] += ($s['po_receive_qty']*$tax_per_qty);
				
				$s['po_receive_total_show'] = 'Rp '.priceFormat($s['po_receive_total']);
				$s['item_code_name'] = $s['item_code'].' / '.$s['item_name'];
				
				$s['use_stok_kode_unik_text'] = '<font color="red">Tidak</font>';
				if(!empty($s['use_stok_kode_unik'])){
					$s['use_stok_kode_unik_text'] = '<font color="green">Ya</font>';
				}
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetailRO()
	{
		
		$this->table = $this->prefix.'ro_detail';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}	
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.id, a.ro_id, '' as po_id, a.item_id, a.supplier_item_id, a.unit_id, a.id as ro_detail_id, 'new' as po_detail_status, 
								a.ro_detail_qty as po_detail_qty, b.item_code, b.item_name, a.item_price, b.item_image, c.unit_name, c.unit_code, 
								a.item_price as po_detail_purchase, a.item_price*a.ro_detail_qty as po_detail_total,
								a2.ro_number",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'ro as a2','a2.id = a.ro_id','LEFT'),
										array($this->prefix.'supplier_item as b2','b2.id = a.supplier_item_id','LEFT'),
										array($this->prefix.'items as b','b2.item_id = b.id','LEFT'),
										array($this->prefix.'unit as c','a.unit_id = c.id','LEFT')
									) 
								),
			'order'			=> array('a.id' => 'DESC'),
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		$params['where'][] = "a.ro_detail_status = 'validated'";
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$supplier_id = $this->input->post('supplier_id');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('b.item_name' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.item_name LIKE '%".$searching."%')";
		}
		if(!empty($supplier_id)){
			$params['where'][] = "a.supplier_id = ".$supplier_id;
		}
		
		//get data -> data, totalCount
		$get_data = $this->m2->find_all($params);
		  		
		$newData = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['po_detail_purchase_show'] = 'Rp '.priceFormat($s['po_detail_purchase']);
				$s['po_detail_total_show'] = 'Rp '.priceFormat($s['po_detail_total']);
				
				//switch
				$s['item_id_real'] = $s['item_id'];
				//$s['item_id'] = $s['supplier_item_id'];
				$s['item_code_name'] = $s['item_code'].' / '.$s['item_name'];
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetailRL()
	{
		
		$this->table = $this->prefix.'po_detail';
		$this->table_receiving = $this->prefix.'receiving';
		$this->table_receiving_detail = $this->prefix.'receive_detail';
		$session_client_id = $this->session->userdata('client_id');
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}	
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.id, a.po_id, '' AS receive_id, a.item_id, a.supplier_item_id, a.unit_id, 
								a.id AS po_detail_id, a.po_detail_qty, a.po_receive_qty,
								a.po_detail_purchase AS receive_det_purchase, 
								a.po_detail_potongan AS receive_det_potongan, 
								a.po_detail_tax AS receive_det_tax, 
								a.po_detail_status AS receive_detail_status, 
								a.po_detail_total AS  receive_det_total, 
								b.item_code, b.item_name, b.item_price, b.item_image, b.use_stok_kode_unik,
								c.unit_name, c.unit_code,
								b2.item_price as item_price_supplier, 
								DATE_FORMAT(NOW(),'%d-%m-%Y') AS receive_det_date, '' as current_stock",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'po as a2','a2.id = a.po_id','LEFT'),
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
		$po_id = $this->input->post('po_id');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('b.item_name' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.item_name LIKE '%".$searching."%')";
		}
		if(!empty($po_id)){
			$params['where'] = array('a.po_id' => $po_id, 'a2.is_deleted' => 0);
		}
		
		//get data -> data, totalCount
		$get_data = $this->m2->find_all($params);
		  		
		//get all receive with related PO	
		$all_qty_receive = array();
		$this->db->select('a.po_detail_id, a.receive_det_qty, a.item_id, a.supplier_item_id, b.po_id, b.receive_status');
		$this->db->from($this->table_receiving_detail.' as a');
		$this->db->join($this->table_receiving.' as b',"b.id = a.receive_id");
		$this->db->where('b.po_id',$po_id);
		$this->db->where('b.receive_status', 'done');
		$dt_receiving = $this->db->get();		
		if($dt_receiving->num_rows() > 0){
			foreach($dt_receiving->result() as $dt){
				if(empty($all_qty_receive[$dt->po_detail_id])){
					$all_qty_receive[$dt->po_detail_id] = 0;
				}
				
				$all_qty_receive[$dt->po_detail_id] += $dt->receive_det_qty;
			}
		}
				
		$newData = array();
		
		$no = 1;
		$mktime_now = strtotime(date("d-m-Y H:i:s"));
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				//generate id
				$s['id'] = 'new-'.$id_user.'-'.$s['id'].'-'.($mktime_now+$no);
				$s['temp_id'] = 'new-'.$id_user.'-'.$s['po_id'].'-'.$s['po_detail_id'];
				$s['receive_det_purchase'] = $s['receive_det_purchase'];
				$s['receive_det_purchase_show'] = 'Rp '.priceFormat($s['receive_det_purchase']);
				$s['po_detail_qty_sisa'] = $s['po_detail_qty'] - $s['po_receive_qty'];
				$s['receive_det_qty'] = $s['po_detail_qty_sisa'];
				$s['item_id_real'] = $s['item_id'];
				$s['item_code_name'] = $s['item_code'].' / '.$s['item_name'];
				
				$s['use_stok_kode_unik_text'] = '<font color="red">Tidak</font>';
				if(!empty($s['use_stok_kode_unik'])){
					$s['use_stok_kode_unik_text'] = '<font color="green">Ya</font>';
					$s['receive_det_qty'] = 0;
				}
				
				array_push($newData, $s);
				
				$no++;
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'po';	
		$this->table2 = $this->prefix.'po_detail';			
		$this->table_receiving = $this->prefix.'receiving';		
		
		$this->prefix_acc = config_item('db_prefix3');
		$this->table_account_payable = $this->prefix_acc.'account_payable';			
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$supplier_id = $this->input->post('supplier_id');
		$supplier_invoice = $this->input->post('supplier_invoice');
		$po_payment = $this->input->post('po_payment');
		$po_date = $this->input->post('po_date');
		$po_memo = $this->input->post('po_memo');
		$po_sub_total = $this->input->post('po_sub_total');
		$po_discount = $this->input->post('po_discount');
		$po_tax = $this->input->post('po_tax');
		$po_shipping = $this->input->post('po_shipping');
		$po_total_price = $this->input->post('po_total_price');
		$po_ship_to = $this->input->post('po_ship_to');
		$po_project = $this->input->post('po_project');
		$supplier_from_ro = $this->input->post('supplier_from_ro');
		
		$get_opt = get_option_value(array("use_approval_po","as_server_backup"));
		cek_server_backup($get_opt);
		
		$use_approval_po = 0;
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
		
		if(!empty($get_opt['use_approval_po'])){
			$use_approval_po = $get_opt['use_approval_po']; 
			
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
			$r = array('success' => false, 'info' => 'Select Supplier First!'); 
			die(json_encode($r));
		}
		
		if(empty($po_payment)){
			$po_payment = 'cash';
		}
		
		//poDetail				
		$poDetail = $this->input->post('poDetail');
		$poDetail = json_decode($poDetail, true);
		if(!empty($poDetail)){
			$total_item = count($poDetail);
			
			
			$new_item_supplier = array();
			$new_item_ID = array();
			foreach($poDetail as $key => $dt){
				
				if(empty($dt['item_hpp'])){
					$dt['item_hpp'] = 0;
				}
				if(empty($dt['from_supplier_item'])){
					$dt['from_supplier_item'] = 0;
				}
				if(empty($dt['supplier_item_id']) OR $dt['supplier_item_id'] == 'NaN'){
					$dt['supplier_item_id'] = 0;
				}
				
				if(empty($dt['from_supplier_item'])){
					$new_item_ID[] = $dt['item_id'];
					$new_item_supplier[$dt['item_id']] = array(
						'supplier_id'=> $supplier_id,
						'item_id'	=> $dt['item_id'],
						'unit_id'	=> $dt['unit_id'],
						'item_price'=> $dt['po_detail_purchase'],
						'item_hpp'	=> $dt['item_hpp'],
						'created'		=>	date('Y-m-d H:i:s'),
						'createdby'		=>	$session_user,
						'updated'		=>	date('Y-m-d H:i:s'),
						'updatedby'		=>	$session_user,
					);
				}
				
				$poDetail[$key] = array(
					"id" => $dt['id'],
					"po_id" => $dt['po_id'],
					"supplier_item_id" => $dt['supplier_item_id'],
					"item_id" => $dt['item_id'],
					"item_name" => $dt['item_name'],
					"item_price" => $dt['item_price'],
					"item_hpp" => $dt['item_hpp'],
					"item_image" => $dt['item_image'],
					"unit_id" => $dt['unit_id'],
					"unit_name" => $dt['unit_name'],
					"unit_code" => $dt['unit_code'],
					"po_detail_qty" => $dt['po_detail_qty'],
					"po_detail_status" => $dt['po_detail_status'],
					"po_detail_purchase" => $dt['po_detail_purchase'],
					"po_detail_purchase_show" => $dt['po_detail_purchase_show'],
					"po_detail_potongan" => $dt['po_detail_potongan'],
					"po_detail_tax" => $dt['po_detail_tax'],
					"po_detail_potongan_show" => $dt['po_detail_potongan_show'],
					"po_detail_tax_show" => $dt['po_detail_tax_show'],
					"po_detail_total" => $dt['po_detail_total'],
					"po_detail_total_show" => $dt['po_detail_total_show'],
					"ro_id" => 0,
					"ro_number" => 0,
					"ro_detail_id" => $dt['ro_detail_id'],
					"from_supplier_item" => $dt['from_supplier_item']
				);
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
			if(!empty($new_item_ID)){
				foreach($new_item_ID as $itemId){
					if(!in_array($itemId, $supplier_item_ID)){
						$add_item_ID[] = $itemId;
						$add_item_data[] = $new_item_supplier[$itemId];
					}
				}
			}
			
			//save to supplier_item
			$supplier_item_ID = array();
			if(!empty($add_item_data)){
				$this->db->insert_batch($this->prefix.'supplier_item', $add_item_data);
				
				//get id
				$add_item_sql = implode(",", $add_item_ID);
				$this->db->from($this->prefix.'supplier_item');
				$this->db->where("supplier_id", $supplier_id);
				$this->db->where("item_id IN (".$add_item_sql.")");
				$get_item = $this->db->get();
				if($get_item->num_rows() > 0){
					foreach($get_item->result() as $dt){
						if(!in_array($dt->item_id, $supplier_item_ID)){
							$supplier_item_ID[$dt->item_id] = $dt->id;
						}
					}
				}
				
			}
			
			if(!empty($supplier_item_ID)){
				foreach($poDetail as $key => $dt){
					if(!empty($supplier_item_ID[$dt['item_id']])){
						$poDetail[$key]['supplier_item_id'] = $supplier_item_ID[$dt['item_id']];
					}
				}
			}
			
			//echo '<pre>';
			//print_r($supplier_item_ID);
			//print_r($poDetail);
			//die();
			
		}
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
			
		$r = '';
		if($this->input->post('form_type_purchaseOrder', true) == 'add')
		{
			
			$get_po_number = $this->generate_po_number();
			
			if(empty($get_po_number)){
				$r = array('success' => false);
				die(json_encode($r));
			}		
			
			$date_now = date("Y-m-d");
			//CLOSING DATE
			$var_closing = array(
				'xdate'	=> $date_now,
				'xtipe'	=> 'purchasing'
			);
			$is_closing = is_closing($var_closing);
			if($is_closing){
				$r = array('success' => false, 'info' => 'Transaksi untuk Purchasing & Receiving pada tanggal: '.$date_now.' sudah ditutup'); 
				die(json_encode($r));
			}
			
			$var = array(
				'fields'	=>	array(
				    'po_number'  	=> 	$get_po_number,
				    'supplier_id'  	=> 	$supplier_id,
				    'supplier_invoice'  => 	$supplier_invoice,
				    'po_date'  		=> 	$po_date,
				    'po_total_qty'  => 	0,
				    'po_discount'  	=> $po_discount,
				    'po_tax'  		=> $po_tax,
				    'po_shipping'  	=> $po_shipping,
				    'po_sub_total'  => $po_sub_total,
				    'po_total_price'  => $po_total_price,
				    'po_status'  	=> 	'progress',
					'po_payment'  	=> 	$po_payment,
				    'po_memo'  		=> 	$po_memo,
				    'po_project'  	=> 	$po_project,
				    'po_ship_to'  	=> 	$po_ship_to,
				    'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active,
					'supplier_from_ro' =>	$supplier_from_ro,
					'use_approval' =>	$use_approval_po,
					'approval_status' =>	$approval_status
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
				$r = array('success' => true, 'id' => $insert_id, 'po_number'	=> '-', 'det_info' => array()); 		
				$q_det = $this->m2->poDetail($poDetail, $insert_id);
				if(!empty($q_det['dtPO']['po_number'])){
					$r['po_number'] = $q_det['dtPO']['po_number'];
				}
				$r['det_info'] = $q_det;
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_purchaseOrder', true) == 'edit'){
			$var = array('fields'	=>	array(
				    //'po_number'  	=> 	$get_po_number,
				    'supplier_id'  	=> 	$supplier_id,
				    'supplier_invoice'  => 	$supplier_invoice,
				    'po_date'  		=> 	$po_date,
				    'po_total_qty'  => 	0,
				    'po_discount'  	=> $po_discount,
				    'po_tax'  		=> $po_tax,
				    'po_shipping'  	=> $po_shipping,
				    'po_sub_total'  => $po_sub_total,
				    'po_total_price'  => $po_total_price,
					'po_payment'  	=> 	$po_payment,
				    'po_memo'  		=> 	$po_memo,
				    'po_project'  	=> 	$po_project,
				    'po_ship_to'  	=> 	$po_ship_to,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active,
					'supplier_from_ro' =>	$supplier_from_ro,
					'use_approval' =>	$use_approval_po,
					'approval_status' =>	$approval_status
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			$id = $this->input->post('id', true);
			
			//CEK OLD DATA
			$this->db->from($this->table);
			$this->db->where("id = '".$id."'");
			$get_dt = $this->db->get();	
			if($get_dt->num_rows() > 0){
				$old_data = $get_dt->row_array();
			}	
			
			//CLOSING DATE
			$var_closing = array(
				//'xdate'	=> $old_data['po_date'],
				'xdate'	=> $po_date,
				'xtipe'	=> 'purchasing'
			);
			$is_closing = is_closing($var_closing);
			if($is_closing){
				$r = array('success' => false, 'info' => 'Transaksi untuk Purchasing & Receiving pada tanggal: '.$old_data['po_date'].' sudah ditutup'); 
				die(json_encode($r));
			}
			
			
			//UPDATE
			$this->lib_trans->begin();
				$update = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				$r = array('success' => true, 'id' => $id, 'det_info' => array());
				
				$is_status_done = false;
				
				$this->db->from($this->table_receiving);
				$this->db->where("po_id IN ('".$id."')");
				$this->db->where("is_deleted = '0'");
				$get_dt_rec = $this->db->get();
				if($get_dt_rec->num_rows() > 0){
					//status is USER BY RECEIVING
					//$is_status_done = true;	
				}
				
				//check data main if been take
				$this->db->from($this->table);
				$this->db->where("id IN ('".$id."')");
				$this->db->where("po_status = 'done'");
				$get_dt = $this->db->get();
				if($get_dt->num_rows() > 0){
					//status is DONE!
					$is_status_done = true;
				}
				
				if($is_status_done == false){
					$q_det = $this->m2->poDetail($poDetail, $id);
					$r['det_info'] = $q_det;
				}
				
				if(!empty($old_data)){
					
					if($old_data['po_status'] == 'done'){
						if($old_data['po_payment'] == 'credit' AND $po_payment != 'credit'){
							$updateAP = $this->account_payable->set_account_payable_PO($id);
							
							if($updateAP === true || $updateAP === false){
								$r['updatePO'] = $old_data['po_payment'].' to '.$po_payment;
							}else
							if($updateAP == 'kontrabon'){
								
								$no_kontrabon = '-';
								$this->db->from($this->table_account_payable);
								$this->db->where("ap_tipe = 'purchasing'");
								$this->db->where("po_id = '".$id."'");
								$get_ap = $this->db->get();
								if($get_ap->num_rows() > 0){
									
									$data_AP = $get_ap->row();
									$no_kontrabon = $data_AP->no_kontrabon;
									
								}
								$r['success'] = false;
								$r['info'] = 'Silahkan Cek dan Hapus Kontrabon: '.$no_kontrabon.' terkait PO: '.$old_data['po_number'];
								$r['updatePO'] = $old_data['po_payment'].' to '.$po_payment;
								$r['updateAP'] = $updateAP;
								
								$rollback_po_status = array(
									'po_payment'	=> $old_data['po_payment']
								);
								$this->db->update($this->table, $rollback_po_status, "id = '".$id."'");
								
							}
							
						}else{
							$updateAP = $this->account_payable->set_account_payable_PO($id);
						}
					}
					
				}
				
			}  
			else
			{  
				$r = array('success' => false);
			}
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}	
	
	public function closing_PO()
	{
		
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table = $this->prefix.'po';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		//Get PO
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		$get_po = $this->db->get();
		
		//delete data
		$update_data = array(
			'po_status'	=> 'done'
		);
		
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->update($this->table, $update_data);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
			
			$updateAP = $this->account_payable->set_account_payable_PO($id);
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Closing Purchase Order Failed!'); 
        }
		die(json_encode($r));
	}
		
	public function validation_used_PO($sql_Id = ''){
		
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		if(empty($sql_Id)){
			return true;
		}
		
		//check used on receiving
		//status progress/done -> is_deleted = 0
		$this->db->from($this->table_receiving);
		$this->db->where("po_id IN ('".$sql_Id."')");
		$this->db->where("is_deleted = '0'");
		$get_dt_rec = $this->db->get();
		if($get_dt_rec->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Purchase Order been used on Receiving<br/>Please Cancel Receiving First!'); 
			die(json_encode($r));			
		}	
		
		//check data main if been take
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		$this->db->where("po_status = 'done'");
		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Purchase Order number been done / used!</br>Please Refresh List PO'); 
			die(json_encode($r));			
		}
		
		return true;
	}	
	
	public function delete()
	{
		
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table = $this->prefix.'po';
		$this->table2 = $this->prefix.'po_detail';
		$this->table_receiving = $this->prefix.'receiving';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		$validation_used_PO = $this->validation_used_PO($sql_Id);		
		
		//Get PO
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		$get_po = $this->db->get();
		
		//delete data
		$update_data = array(
			'po_status'	=> 'cancel',
			'is_deleted'=> 1
		);
		
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->update($this->table, $update_data);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
			
			//delete detail too
			//$this->db->where("po_id IN ('".$sql_Id."')");
			//$this->db->delete($this->table2);
			
			//Get RO
			$get_ro_id = 0;
			$this->db->select('DISTINCT(a.ro_id)');
			$this->db->from($this->prefix.'ro_detail as a');
			$this->db->join($this->prefix.'ro as b',"b.id = a.ro_id","LEFT");
			$this->db->where("a.take_reff_id IN ('".$sql_Id."')");
			$get_ro = $this->db->get();
			if($get_ro->num_rows() > 0){
				$get_ro_id = $get_ro->row();
				$ro_id = $get_ro_id->ro_id;
				$var4 = array(
					'ro_status'  => 'validated'		
				);
				$this->db->where("id IN ('".$ro_id."')");
				$this->db->update($this->prefix.'ro', $var4);
			}
			
			//UPDATE RO
			//foreach($get_po->result() as $row){
				
				$var4 = array(
						'ro_detail_status'  => 'validated',
						//'supplier_id'  => 0,
						'take_reff_id'  => 0,
						'take_reff_detail_id'  => 0				
				);
				
				
				$this->lib_trans->begin();
					$this->db->where("take_reff_id IN ('".$sql_Id."')");
					$this->db->update($this->prefix.'ro_detail', $var4);
				$this->lib_trans->commit();
				
			//}
			
			
			
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Purchase Order Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function deleteDetail()
	{
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table = $this->prefix.'po_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		$validation_used_PO = $this->validation_used_PO($sql_Id);		
		
		//Get po_id
		$this->db->select('po_id');
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		$get_data = $this->db->get();
		$data_po_id = $get_data->row();
		
		//delete data
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->delete($this->table);
		
		$r = '';
		if($q)  
        {  
			//Update RO
			$var4 = array(
					'ro_detail_status'  => 'request',
					'supplier_id'  => 0,
					'take_reff_id'  => 0,
					'take_reff_detail_id'  => 0				
			);
			
			$this->lib_trans->begin();
				$this->db->where("take_reff_detail_id IN ('".$sql_Id."')");
				$this->db->update($this->prefix.'ro_detail', $var4);
			$this->lib_trans->commit();
			
						
			$po_total_price = $this->get_total_price($data_po_id->po_id);
            $r = array('success' => true, 'po_total_price' => $po_total_price); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Purchase Order Detail Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function get_total_qty($po_id){
		$this->table = $this->prefix.'po_detail';	
		
		$this->db->select('SUM(po_detail_qty) as total_qty');
		$this->db->from($this->table);
		$this->db->where('po_id', $po_id);
		$get_tot = $this->db->get();
		
		$total_qty = 0;
		if($get_tot->num_rows() > 0){
			$data_po = $get_tot->row();
			$total_qty = $data_po->total_qty;
		}
		
		return $total_qty;
	}
	
	public function get_total_price($po_id){
		$this->table = $this->prefix.'po_detail';	
		
		$this->db->select('SUM(po_detail_total) as total_price');
		$this->db->from($this->table);
		$this->db->where('po_id', $po_id);
		$get_tot = $this->db->get();
		
		$total_price = 0;
		if($get_tot->num_rows() > 0){
			$data_po = $get_tot->row();
			$total_price = $data_po->total_price;
		}
		
		return $total_price;
	}
	
	public function generate_po_number(){
		$this->table = $this->prefix.'po';		

		$getDate = date("ym");
		
		$this->db->from($this->table);
		$this->db->where("po_number LIKE 'PO".$getDate."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_po = $get_last->row();
			$po_number = str_replace("PO".$getDate,"", $data_po->po_number);
			$po_number = str_replace("PO","", $po_number);
						
			$po_number = (int) $po_number;			
		}else{
			$po_number = 0;
		}
		
		$po_number++;
		$length_no = strlen($po_number);
		switch ($length_no) {
			case 3:
				$po_number = $po_number;
				break;
			case 2:
				$po_number = '0'.$po_number;
				break;
			case 1:
				$po_number = '00'.$po_number;
				break;
			default:
				$po_number = '00'.$po_number;
				break;
		}
				
		return 'PO'.$getDate.$po_number;				
	}
	
	public function generate_po_number_old(){
		$this->table = $this->prefix.'po';	
		
		$default_PO = "PO".date("ym");
		$this->db->from($this->table);
		$this->db->where("po_number LIKE '".$default_PO."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_ro = $get_last->row();
			//$po_number = $data_ro->po_number;
			$po_number = str_replace($default_PO,"", $data_ro->po_number);
						
			$po_number = (int) $po_number;			
		}else{
			$po_number = 0;
		}
		
		$po_number++;
		$length_no = strlen($po_number);
		switch ($length_no) {
			case 3:
				$po_number = $po_number;
				break;
			case 2:
				$po_number = '0'.$po_number;
				break;
			case 1:
				$po_number = '00'.$po_number;
				break;
			default:
				$po_number = $po_number;
				break;
		}
		
		return $default_PO.$po_number;			
		
	}
	
	
	public function printPO(){
		
		$this->table  = $this->prefix.'po'; 
		$this->table2 = $this->prefix.'po_detail';
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
		$data_post = array(
			'do'	=> '',
			'po_data'	=> array(),
			'po_detail'	=> array(),
			'report_name'	=> 'PURCHASE ORDER',
			'report_place_default'	=> '',
			'user_fullname'	=> $user_fullname,
			'client'	=> $dt_client,
			'qty_print'	=> $qty_print
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($po_id)){
			die('Purchase Order Not Found!');
		}else{
			
			$this->db->select("a.*, b.supplier_name, b.supplier_code, b.supplier_address, b.supplier_phone, 
			b.supplier_fax, b.supplier_email, b.supplier_contact_person");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix."supplier as b","b.id = a.supplier_id","LEFT");
			$this->db->where("a.id = '".$po_id."'");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['po_data'] = $get_dt->row_array();
				
				//get detail
				$this->db->select("a.*, b.item_code, b.item_name, b.item_type, c.unit_code, c.unit_name");
				$this->db->from($this->table2." as a");
				$this->db->join($this->prefix."items as b","b.id = a.item_id","LEFT");
				$this->db->join($this->prefix."unit as c","c.id = a.unit_id","LEFT");
				$this->db->where("a.po_id = '".$po_id."'");
				$get_det = $this->db->get();
				if($get_det->num_rows() > 0){
					$data_post['po_detail'] = $get_det->result_array();
				}
				
			}else{
				die('Purchase Order Not Found!');
			}
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		$useview = 'printPO';
		if($do == 'excel'){
			$useview = 'excelPO';
		}
		
		$this->load->view('../../purchase/views/'.$useview, $data_post);
		
	}
}