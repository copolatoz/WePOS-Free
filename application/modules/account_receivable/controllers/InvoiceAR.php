<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class InvoiceAR extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix3');
		$this->load->model('model_invoicear', 'm');
		$this->load->model('model_invoiceardetail', 'm2');
	}	
	
	public function gridData(){
		
		$this->table = $this->prefix.'invoice';
		
		$sortAlias = array(
			'invoice_status_text'	=> 'invoice_status',
			'total_tagihan_show'	=> 'total_tagihan'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										//array($this->prefix.'jurnal_header as b','b.id = a.jurnal_id','LEFT'),
										//array($this->prefix.'autoposting as c','c.id = a.autoposting_id','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted = 0'),
			'order'			=> array('a.id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$status = $this->input->post('status');
		$skip_date = $this->input->post('skip_date');
		$from_kartuPiutang = $this->input->post('from_kartuPiutang');
		$invoicename = $this->input->post('invoicename');
		$group_invoicename = $this->input->post('group_invoicename');
		$show_all_text = $this->input->post('show_all_text');
		$show_choose_text = $this->input->post('show_choose_text');
		
		//FILTER
		$date_from = $this->input->post('date_from');
		$date_till = $this->input->post('date_till');
		$keywords = $this->input->post('keywords');
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if(empty($skip_date)){
			if(empty($date_from) AND empty($date_till)){
				$skip_date = true;
			}
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
							
				$qdate_from = date("Y-m-d 00:00:00",strtotime($date_from));
				$qdate_till = date("Y-m-d 23:59:59",strtotime($date_till));
				
				$params['where'][] = "(a.created >= '".$qdate_from."' AND a.created <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.invoice_no' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.invoice_no LIKE '%".$searching."%' OR a.invoice_name LIKE '%".$searching."%')";
		}		
		if(!empty($status)){
			$params['where'][] = "a.invoice_status = '".$status."'";
		}
		if(!empty($from_kartuPiutang)){
			$params['where'][] = "a.invoice_status = 'progress'";
		}
		
		//$params['where'][] = "a.is_deleted = 0";
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		
  		$newData = array();	
		
		if(!empty($show_all_text)){
			$dt = array('id' => '-1', 'invoice_name' => 'Semua Invoice');
			array_push($newData, $dt);
		}else{
			if(!empty($show_choose_text)){
				$dt = array('id' => '', 'invoice_name' => 'Pilih Invoice');
				array_push($newData, $dt);
			}
		}
		
		if(!empty($group_invoicename)){
			$newData = array();
		}
		
		if(!empty($get_data['data'])){
			
			$all_invoice_id = array();
			foreach ($get_data['data'] as $s){
				$all_invoice_id[] = $s['id'];
			}
			
			$used_invoice_id = array();
			if(!empty($all_invoice_id)){
				$this->table_pembayaran_ar = $this->prefix.'pembayaran_ar';	
				$this->db->select('invoice_id');
				$this->db->from($this->table_pembayaran_ar);
				$this->db->where("invoice_id IN ('".implode(",", $all_invoice_id)."') AND is_deleted = 0");
				$get_dt = $this->db->get();
				if($get_dt->num_rows() > 0){
					foreach($get_dt->result() as $dt){
						if(!in_array($dt->invoice_id, $used_invoice_id)){
							$used_invoice_id[] = $dt->invoice_id;
						}
					}
				}
			}
			
			foreach ($get_data['data'] as $s){
				
				if($s['invoice_status'] == 'progress'){
					$s['invoice_status_text'] = '<span style="color:blue;">Progress</span>';
					
					if(in_array($s['id'],$used_invoice_id)){
						$s['invoice_status_text'] = '<span style="color:green;">Used</span>';
					}
					
				}else 
				if($s['invoice_status'] == 'done'){
					$s['invoice_status_text'] = '<span style="color:red;">Done</span>';
				}
				
				$s['created'] = date("d-m-Y",strtotime($s['created']));
				$s['total_tagihan_show'] = 'Rp. '.priceFormat($s['total_tagihan']);
				$s['total_bayar_show'] = 'Rp. '.priceFormat($s['total_bayar']);
				
				$s['invoice_name_customer'] = $s['invoice_name'];
				$s['invoice_name_customer_id'] = $s['invoice_name'];
				if(!empty($s['customer_id'])){
					$s['invoice_name_customer'] = $s['invoice_name']." (Customer)";
					$s['invoice_name_customer_id'] = $s['invoice_name']."_".$s['customer_id'];
				}
				
				if(empty($s['tanggal_jatuh_tempo']) OR $s['tanggal_jatuh_tempo'] == '0000-00-00'){
					$s['tanggal_jatuh_tempo'] = '-';
				}else{
					$s['tanggal_jatuh_tempo'] = date("d-m-Y",strtotime($s['tanggal_jatuh_tempo']));
				}
				
				
				$s['status_pembayaran'] = '<span style="color:red;">Belum Lunas</span>';
				if($s['total_bayar'] >= $s['total_tagihan']){
					$s['status_pembayaran'] = '<span style="color:green;">Lunas</span>';
				}
				
				array_push($newData, $s);
			}
			
			
			if(!empty($group_invoicename)){
				$newData_old = $newData;
				$group_newData = array();
				$invoice_name_customer_id = array();
				$no = 0;
				
				
				if(!empty($show_all_text)){
					$dtCustomer = array(
						'no' => 0,
						'invoice_name' => 'Semua Customer',
						'invoice_name_customer' => 'Semua Customer',
						'invoice_name_customer_id' => '',
					);
					array_push($group_newData, $dtCustomer);
				}else{
					if(!empty($show_choose_text)){
						$dtCustomer = array(
							'no' => 0,
							'invoice_name' => 'Pilih Customer',
							'invoice_name_customer' => 'Pilih Customer',
							'invoice_name_customer_id' => '',
						);
						array_push($group_newData, $dtCustomer);
					}
				}
				
				//echo '<pre>';
				//print_r($newData_old);
				//die();
				
				foreach($newData_old as $dt){
					
					if(!in_array($dt['invoice_name_customer_id'], $invoice_name_customer_id)){
						$invoice_name_customer_id[] = $dt['invoice_name_customer_id'];
						
						$no++;
						$dtCustomer = array(
							'no' => $no,
							'invoice_name' => $dt['invoice_name'],
							'invoice_name_customer' => $dt['invoice_name_customer'],
							'invoice_name_customer_id' => $dt['invoice_name_customer_id'],
						);
						array_push($group_newData, $dtCustomer);
					}
					
				}
				
				$newData = $group_newData;
			}
		}
		
		$get_data['data'] = $newData;
		$get_data['totalCount'] = count($newData);
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetail(){
		
		$this->table_invoice_detail = $this->prefix.'invoice_detail';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, b.ar_no, b.ar_name, b.ar_date, b.no_ref, b.customer_id, b.ar_notes",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table_invoice_detail.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'account_receivable as b','b.id = a.ar_id','LEFT')
									) 
								),
			'order'			=> array('a.id' => 'DESC'),
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$ar_id = $this->input->post('ar_id');
		$invoice_id = $this->input->post('invoice_id');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('b.ar_no' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.ar_no LIKE '%".$searching."%' OR b.ar_name LIKE '%".$searching."%')";
		}
		if(!empty($ar_id)){
			$params['where'][] = array('a.ar_id' => $ar_id);
		}
		if(!empty($invoice_id)){
			$params['where'][] = array('a.invoice_id' => $invoice_id);
		}
		
		//get data -> data, totalCount
		$get_data = $this->m2->find_all($params);
		  		
		$newData = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['total_tagihan_show'] = 'Rp '.priceFormat($s['total_tagihan']);
				$s['total_bayar_show'] = 'Rp '.priceFormat($s['total_bayar']);
				
				$s['ar_date'] = date("d-m-Y",strtotime($s['ar_date']));
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetail_AR(){
		
		
		$this->prefix_pos = config_item('db_prefix2');
		$this->table = $this->prefix.'_invoice';
		$this->table_account_receivable = $this->prefix.'account_receivable';
		$this->table_customer = $this->prefix_pos.'customer';
		
		$get_opt = get_option_value(array("auto_add_customer_ap"));
		
		$auto_add_customer_ap = 0;
		if(!empty($get_opt['auto_add_customer_ap'])){
			$auto_add_customer_ap = 1;
		}
		
		$ar_name = $this->input->post('ar_name');
		$customer_id = $this->input->post('customer_id');
		$all_ar_no = $this->input->post('all_ar_no');
		
		$this->db->select("a.*, a.id as ar_id, b.customer_name");
		$this->db->from($this->table_account_receivable." as a");
		$this->db->join($this->table_customer." as b", "b.id = a.customer_id", "LEFT");
		$this->db->where("a.ar_used = 0");
		$this->db->where("a.ar_status = 'posting'");
		$this->db->where("a.is_deleted = 0");
		
		if(empty($all_ar_no)){
			
			$this->db->where("a.ar_name = '".$ar_name."'");
				
			if(!empty($customer_id)){
				$this->db->where("a.customer_id = '".$customer_id."'");
			}
			
		}
		
		$get_ar = $this->db->get();
		
		
		$nama_customer_id = array();
		$newData = array();
		if($get_ar->num_rows() > 0){
			foreach($get_ar->result() as $dt){
				
				if(empty($dt->customer_id)){
					$dt->customer_id = 0;
				}
				
				if(empty($all_ar_no)){
					
					if($auto_add_customer_ap == 1){
						
						$dt->ar_name_customer = $dt->ar_name." ".$dt->customer_name;
							
						if(!empty($dt->customer_name)){
							$dt->ar_name_customer = $dt->customer_name." (Customer)";
						}
						
						$dt->total_tagihan_show = priceFormat($dt->total_tagihan);
						
						$dt->total_bayar = 0;
						$dt->total_bayar_show = priceFormat($dt->total_bayar);
						$dt->ar_date = date("d-m-Y",strtotime($dt->ar_date));
						
						$dt->id = 'new_'.$dt->id;
						
						$newData[] = $dt;
						
					}else{
						$nama_customer_id_cek = $dt->ar_name." ".$dt->customer_id;
						if(!in_array($nama_customer_id_cek, $nama_customer_id)){
							$nama_customer_id[] = $nama_customer_id_cek;
							$dt->ar_name_customer = $dt->ar_name." ".$dt->customer_name;
							
							if(!empty($dt->customer_name)){
								$dt->ar_name_customer = $dt->customer_name." (Customer)";
							}
							
							$dt->total_tagihan_show = priceFormat($dt->total_tagihan);
							
							$dt->total_bayar = 0;
							$dt->total_bayar_show = priceFormat($dt->total_bayar);
							$dt->ar_date = date("d-m-Y",strtotime($dt->ar_date));
							
							$dt->id = 'new_'.$dt->id;
							
							$newData[] = $dt;
						}
					}
					
					
					
				}else{
					
					$dt->ar_name_customer = $dt->ar_name." ".$dt->customer_name;
						
					if(!empty($dt->customer_name)){
						$dt->ar_name_customer = $dt->customer_name." (Customer)";
					}
					
					$dt->ar_no_name = $dt->ar_no." / ".$dt->ar_name_customer;
					
					
					$dt->total_tagihan_show = priceFormat($dt->total_tagihan);
					
					$dt->total_bayar = 0;
					$dt->total_bayar_show = priceFormat($dt->total_bayar);
					$dt->ar_date = date("d-m-Y",strtotime($dt->ar_date));
					
					$dt->id = 'new_'.$dt->id;
					
					$newData[] = $dt;
					
				}
				
				
				
			}
		}
		
		$get_data = array();
		$get_data['data'] = $newData;
		$get_data['totalCount'] = count($newData);
		
      	die(json_encode($get_data));
	}
	
	public function save(){
		
		$this->prefix = config_item('db_prefix3');
		$this->table_account_receivable = $this->prefix.'account_receivable';	
		$this->table_invoice = $this->prefix.'invoice';		
		$session_user = $this->session->userdata('user_username');
		
		$r = array('success' => false,"info" => "Save Invoice Failed!");
		
		$id = $this->input->post('id');
		$invoice_date = $this->input->post('invoice_date');
		$tanggal_jatuh_tempo = $this->input->post('tanggal_jatuh_tempo');
		$invoice_name = $this->input->post('invoice_name');
		$invoice_address = $this->input->post('invoice_address');
		$invoice_phone = $this->input->post('invoice_phone');
		$invoice_no = $this->input->post('invoice_no');
		$customer_id = $this->input->post('customer_id');
		$invoice_notes = $this->input->post('invoice_notes');
		$invoice_status = $this->input->post('invoice_status');
		$total_tagihan = $this->input->post('total_tagihan');
		$total_bayar = $this->input->post('total_bayar');
		$created = $this->input->post('created');
		
		if(empty($invoice_name)){
			$r = array('success' => false, "info" => "Invoice Name cannot empty!");
			die(json_encode($r));
		}	
		
		if(empty($total_tagihan)){
			$r = array('success' => false, "info" => "Total Tagihan empty!");
			die(json_encode($r));
		}
		
		if(empty($total_bayar)){
			$total_bayar = 0;
		}
		
		//poDetail				
		$invoiceDetail = $this->input->post('invoiceDetail');
		$invoiceDetail = json_decode($invoiceDetail, true);
		if(!empty($invoiceDetail)){
			$total_ap = count($invoiceDetail);
		}
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
			
		$r = '';
		if($this->input->post('form_type_invoiceAR', true) == 'add')
		{
			
			$invoice_no = $this->m->generate_invoice_number();
			
			$var = array(
				'fields'	=>	array(
				    'invoice_no'  		=> 	$invoice_no,
				    'invoice_date' 		=> 	$invoice_date,
				    'tanggal_jatuh_tempo' 		=> 	$tanggal_jatuh_tempo,
				    'invoice_name' 		=> 	$invoice_name,
				    'invoice_address' 	=> 	$invoice_address,
				    'invoice_phone' 		=> 	$invoice_phone,
				    'customer_id' 		=> 	$customer_id,
				    'invoice_status' 		=> 	$invoice_status,
				    'invoice_notes' 		=> 	$invoice_notes,
				    'total_tagihan' => 	$total_tagihan,
				    'total_bayar' => 	$total_bayar,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					//'is_active'	=>	$is_active
				),
				'table'		=>  $this->table_invoice
			);	
			
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
				$q = $this->m->add($var);
				$insert_id = $this->m->get_insert_id();
			$this->lib_trans->commit();			
			if($q)
			{  
				$r = array('success' => true, 'id' => $insert_id, 'invoice_no'	=> '-', 'det_info' => array()); 		
				$q_det = $this->m2->invoiceDetail($invoiceDetail, $insert_id);
				if(!empty($q_det['dtAR']['invoice_no'])){
					$r['invoice_no'] = $q_det['dtAR']['invoice_no'];
				}
				$r['det_info'] = $q_det;
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_invoiceAR', true) == 'edit'){
			
			$var = array('fields'	=>	array(
					'invoice_date'=> 	$invoice_date,
					'tanggal_jatuh_tempo'=> 	$tanggal_jatuh_tempo,
					'invoice_name'=> 	$invoice_name,
				    'invoice_address' 	=> 	$invoice_address,
				    'invoice_phone' 		=> 	$invoice_phone,
					'customer_id' 	=> 	$customer_id,
					'invoice_notes' 		=> 	$invoice_notes,
					'total_tagihan' => 	$total_tagihan,
					'total_bayar' => 	$total_bayar,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				),
				'table'			=>  $this->table_invoice,
				'primary_key'	=>  'id'
			);
			
			$id = $this->input->post('id', true);
			
			//CEK OLD DATA
			$this->db->from($this->table_invoice);
			$this->db->where("id = '".$id."'");
			$get_dt = $this->db->get();	
			if($get_dt->num_rows() > 0){
				$old_data = $get_dt->row_array();
			}		
			
			if($old_data['invoice_status'] == 'done'){
				$r = array('success' => false, 'info' => 'Cannot Update, Invoice Been Done!'); 
				die(json_encode($r));
			}
			
			$invoice_used = $this->check_invoice_used($id);
			if($invoice_used == true){
				//$r = array('success' => false, 'info' => 'Tidak bisa diubah<br/>Invoice sedang digunakan pada pembayaran!'); 
				//die(json_encode($r));
				
				unset($var['fields']['total_tagihan']);
				unset($var['fields']['total_bayar']);
				
			}
			
			
			//UPDATE
			$this->lib_trans->begin();
				$update = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				$r = array('success' => true, 'id' => $id, 'det_info' => array());
				
				
				$is_status_done = false;
				
				//check data main if been take
				$this->db->from($this->table_invoice);
				$this->db->where("id IN ('".$id."')");
				$this->db->where("invoice_status = 'done'");
				$get_dt = $this->db->get();
				if($get_dt->num_rows() > 0){
					//status is DONE!
					$is_status_done = true;
				}
				
				if($invoice_used == true){
					$is_status_done = false;
				}
				
				if($is_status_done == false){
					$q_det = $this->m2->invoiceDetail($invoiceDetail, $id);
					$r['det_info'] = $q_det;
				}
				
				
			}  
			else
			{  
				$r = array('success' => false);
			}
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
		
		
	}
	
	public function closing_invoiceAR()
	{
		
		$this->table_invoice = $this->prefix.'invoice';
		$this->table_invoice_detail = $this->prefix.'invoice_detail';
		$this->table_account_receivable = $this->prefix.'account_receivable';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//Get PO
		$this->db->select('*');
		$this->db->from($this->table_invoice);
		$this->db->where("id IN ('".$sql_Id."')");
		$get_inv = $this->db->get();
		
		//delete data
		$update_data = array(
			'invoice_status'	=> 'done'
		);
		
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->update($this->table_invoice, $update_data);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
			
			$this->db->select('*');
			$this->db->from($this->table_invoice_detail);
			$this->db->where("invoice_id IN ('".$sql_Id."')");
			$get_invoice_det = $this->db->get();
			$ar_id = array();
			
			if($get_invoice_det->num_rows() > 0){
				foreach($get_invoice_det->result() as $dt){
					if($dt->invoiced_status == 'paid'){
						if(!in_array($dt->ar_id, $ar_id)){
							$ar_id[] = $dt->ar_id;
						}
					}
				}
			}
			
			if(!empty($ar_id)){
				$ar_id_txt = implode(",", $ar_id);
				
				
				$update_data = array(
					'ar_status'	=> 'pembayaran'
				);
				$this->db->where("id IN ('".$ar_id_txt."')");
				$q = $this->db->update($this->table_account_receivable, $update_data);
				
			}
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Closing Invoice Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function generate_invoice_number(){
		$this->table = $this->prefix.'invoice';		

		$getDate = date("ym");
		
		$this->db->from($this->table);
		$this->db->where("invoice_no LIKE 'INV".$getDate."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_kb = $get_last->row();
			$invoice_no = str_replace("INV".$getDate,"", $data_kb->invoice_no);
			$invoice_no = str_replace("INV","", $invoice_no);
						
			$invoice_no = (int) $invoice_no;			
		}else{
			$invoice_no = 0;
		}
		
		$invoice_no++;
		$length_no = strlen($invoice_no);
		switch ($length_no) {
			case 3:
				$invoice_no = $invoice_no;
				break;
			case 2:
				$invoice_no = '0'.$invoice_no;
				break;
			case 1:
				$invoice_no = '00'.$invoice_no;
				break;
			default:
				$invoice_no = '00'.$invoice_no;
				break;
		}
				
		return 'INV'.$getDate.$invoice_no;				
	}
	
	public function delete()
	{
		
		$this->table = $this->prefix.'invoice';
		$this->table2 = $this->prefix.'invoice_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		$invoice_used = $this->check_invoice_used($sql_Id);
		if($invoice_used == true){
			$r = array('success' => false, 'info' => 'Cannot Delete, Status Invoice Used on Pembayaran!'); 
			die(json_encode($r));
		}
		
		//Get INV
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		$get_inv = $this->db->get();
		
		$data_kb = array();
		if($get_inv->num_rows() > 0){
			
			$data_kb = $get_inv->row();
			if($data_kb->invoice_status == 'done'){
				$r = array('success' => false, 'info' => 'Status Invoice Been Paid!'); 
			}
			
		}else{
			$r = array('success' => false, 'info' => 'Data Not Found!'); 
		}
		
		if(empty($data_kb)){
			$r = array('success' => false, 'info' => 'Data Not Found!'); 
		}
		
		//get detail
		$this->db->select('*');
		$this->db->from($this->table2);
		$this->db->where("invoice_id IN ('".$sql_Id."')");
		$get_data = $this->db->get();
		
		$all_ar_id = array();
		if($get_data->num_rows() > 0){
			
			foreach($get_data->result() as $det){
				if($det->invoiced_status == 'paid'){
					$r = array('success' => false, 'info' => 'Status Detail been Paid!'); 
					die(json_encode($r));
				}
				
				if(!in_array($det->ar_id, $all_ar_id)){
					$all_ar_id[] = $det->ar_id;
				}
			}
			
		}
		
		
		//delete data
		$update_data = array(
			'invoice_status'	=> 'cancel',
			'is_deleted'=> 1
		);
		
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->update($this->table, $update_data);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
			
			if(!empty($all_ar_id)){
				$all_ar_id_txt  = implode(", ", $all_ar_id);
				
				//Update AR
				$update_AR = array(
						'ar_status'  => 'posting',
						'ar_used'  => 0				
				);
				
				$this->lib_trans->begin();
					$this->db->where("id IN (".$all_ar_id_txt.")");
					$this->db->update($this->prefix.'account_receivable', $update_AR);
				$this->lib_trans->commit();
				
			}
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Invoice Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function deleteDetail(){
		$this->table = $this->prefix.'invoice_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//Get ar_id
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		$get_data = $this->db->get();
		
		$data_det = array();
		if($get_data->num_rows() > 0){
			
			$data_det = $get_data->row();
			if($data_det->invoiced_status == 'paid'){
				$r = array('success' => false, 'info' => 'Status Detail been Paid!'); 
				die(json_encode($r));
			}
			
		}else{
			$r = array('success' => false, 'info' => 'Data Not Found!'); 
			die(json_encode($r));
		}
		
		if(empty($data_det)){
			$r = array('success' => false, 'info' => 'Data Not Found!'); 
			die(json_encode($r));
		}
		
		if(!empty($data_det->invoice_id)){
			$invoice_used = $this->check_invoice_used($data_det->invoice_id);
			if($invoice_used == true){
				$r = array('success' => false, 'info' => 'Cannot Delete, Status Invoice Used on Pembayaran!'); 
				die(json_encode($r));
			}
		}
		
		//delete data
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->delete($this->table);
		
		$r = '';
		if($q)  
        {  
			//Update AR
			$update_AR = array(
					'ar_status'  => 'posting',
					'ar_used'  => 0				
			);
			
			$this->lib_trans->begin();
				$this->db->where("id IN ('".$data_det->ar_id."')");
				$this->db->update($this->prefix.'account_receivable', $update_AR);
			$this->lib_trans->commit();
			
			//Update detail calc
			$invoice_total_tagihan = $this->update_total_tagihan($data_det->invoice_id);
			
            $r = array('success' => true, 'ar_id' => $data_det->ar_id, 'invoice_total_tagihan' => $invoice_total_tagihan); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Invoice Detail Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function check_invoice_used($invoice_id){
		
		if(!empty($invoice_id)){
			$this->table_pembayaran_ar = $this->prefix.'pembayaran_ar';	
			$this->db->select('id');
			$this->db->from($this->table_pembayaran_ar);
			$this->db->where("invoice_id IN ('".$invoice_id."') AND is_deleted = 0");
			$get_tot = $this->db->get();
			if($get_tot->num_rows() > 0){
				return true;
			}else{
				return false;
			}
			
		}else{
			return false;
		}
		
	}
	
	public function update_total_tagihan($invoice_id){
		
		$this->table_invoice = $this->prefix.'invoice';	
		$this->table_invoice_detail = $this->prefix.'invoice_detail';	
		
		$this->db->select('SUM(total_tagihan) as total_tagihan_all');
		$this->db->from($this->table_invoice_detail);
		$this->db->where('invoice_id', $invoice_id);
		$get_tot = $this->db->get();
		
		$total_tagihan_all = 0;
		if($get_tot->num_rows() > 0){
			$data_kb = $get_tot->row();
			$total_tagihan_all = $data_kb->total_tagihan_all;
		}
		
		//Update INV
		$update_INV = array(
			'total_tagihan'  => $total_tagihan_all				
		);
		
		$this->db->update($this->table_invoice, $update_INV, "id = ".$invoice_id);
		
		return $total_tagihan_all;
	}
	
	public function printInvoice(){
		$this->prefix_pos = config_item('db_prefix2');
		$this->table_invoice = $this->prefix.'invoice';	
		$this->table_invoice_detail = $this->prefix.'invoice_detail';	
		$this->table_account_receivable = $this->prefix.'account_receivable';	
		$this->table_customer = $this->prefix_pos.'customer';	
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
			'so_data'	=> array(),
			'so_detail'	=> array(),
			'report_name'	=> 'INVOICE',
			'report_place_default'	=> '',
			'user_fullname'	=> $user_fullname,
			'client'	=> $dt_client
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($invoice_id)){
			die('Invoice Not Found!');
		}else{
			
			$this->db->select("a.*, b.customer_name, b.customer_address, b.customer_phone, b.customer_fax, b.customer_email, b.customer_contact_person");
			$this->db->from($this->table_invoice." as a");
			$this->db->join($this->table_customer." as b","b.id = a.customer_id", "LEFT");
			$this->db->where("a.id = '".$invoice_id."'");
			$this->db->where("a.is_deleted = 0");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['invoice_data'] = $get_dt->row_array();
				
				//get detail
				$this->db->select("a.*, b.ar_no, b.ar_name, b.ar_date, b.no_ref, b.ar_notes");
				$this->db->from($this->table_invoice_detail." as a");
				$this->db->join($this->table_account_receivable." as b","b.id = a.ar_id", "LEFT");
				$this->db->where("a.invoice_id = '".$invoice_id."'");
				$this->db->where("a.is_deleted = 0");
				$get_det = $this->db->get();
				if($get_det->num_rows() > 0){
					$data_post['invoice_detail'] = $get_det->result_array();
				}
				
			}else{
				die('Invoice Detail Not Found!');
			}
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		$useview = 'printInvoice';
		if($do == 'excel'){
			$useview = 'excelInvoice';
		}
		
		$this->load->view('../../account_receivable/views/'.$useview, $data_post);
		
	}
}