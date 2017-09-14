<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class PelunasanAP extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix3');
		$this->load->model('model_pelunasanap', 'm');
		$this->load->model('accounting/model_acc_mutasi_jurnal', 'jurnal');
		$this->load->model('accounting/model_acc_mutasi_jurnal_detail', 'jurnal_detail');
	}
	
	public function gridData(){
		
		$this->table = $this->prefix.'kontrabon';
		
		$sortAlias = array(
			'kb_status_text'	=> 'kb_status',
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
				if(empty($date_till)){ $date_till = date('Y-m-td'); }
				
				$mktime_dari = strtotime($date_from);
				$mktime_sampai = strtotime($date_till);
							
				$qdate_from = date("Y-m-d 00:00:00",strtotime($date_from));
				$qdate_till = date("Y-m-d 23:59:59",strtotime($date_till));
				
				$params['where'][] = "(a.created >= '".$qdate_from."' AND a.created <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.kb_no' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.kb_no LIKE '%".$searching."%' OR a.kb_name LIKE '%".$searching."%')";
		}		
		if(!empty($status)){
			$params['where'][] = "a.kb_status = '".$status."'";
		}
		
		$params['where'][] = "a.is_deleted = 0";
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		
  		$newData = array();	
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				if($s['kb_status'] == 'progress'){
					$s['kb_status_text'] = '<span style="color:blue;">Progress</span>';
				}else 
				if($s['kb_status'] == 'done'){
					$s['kb_status_text'] = '<span style="color:red;">Done</span>';
				}
				
				$s['created'] = date("d-m-Y",strtotime($s['created']));
				$s['total_tagihan_show'] = 'Rp. '.priceFormat($s['total_tagihan']);
				$s['total_bayar_show'] = 'Rp. '.priceFormat($s['total_bayar']);
				
				$s['kb_name_supplier'] = $s['kb_name'];
				if(!empty($s['supplier_id'])){
					$s['kb_name_supplier'] = $s['kb_name']." (Supplier)";
				}
				
				if(empty($s['tanggal_jatuh_tempo']) OR $s['tanggal_jatuh_tempo'] == '0000-00-00'){
					$s['tanggal_jatuh_tempo'] = '-';
				}else{
					$s['tanggal_jatuh_tempo'] = date("d-m-Y",strtotime($s['tanggal_jatuh_tempo']));
				}
				
				$s['status_pelunasan'] = '<span style="color:red;">Belum Lunas</span>';
				if($s['total_bayar'] >= $s['total_tagihan']){
					$s['status_pelunasan'] = '<span style="color:green;">Lunas</span>';
				}
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetail(){
		
		$this->table_pelunasan_ap = $this->prefix.'pelunasan_ap';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, b.kb_no, b.kb_name, b.total_tagihan, b.total_bayar, c.no_registrasi, c.no_jurnal, d.autoposting_name",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table_pelunasan_ap.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'kontrabon as b','b.id = a.kb_id','LEFT'),
										array($this->prefix.'jurnal_header as c','c.id = a.jurnal_id','LEFT'),
										array($this->prefix.'autoposting as d','d.id = a.autoposting_id','LEFT')
									) 
								),
			'order'			=> array('a.id' => 'DESC'),
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$kb_id = $this->input->post('kb_id');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('b.kb_no' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.kb_no LIKE '%".$searching."%' OR b.kb_name LIKE '%".$searching."%')";
		}
		if(!empty($kb_id)){
			$params['where'][] = array('a.kb_id' => $kb_id);
		}
		
		$params['where'][] = array('a.is_deleted' => 0);
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
		$newData = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['total_tagihan_show'] = 'Rp '.priceFormat($s['total_tagihan']);
				$s['total_bayar_show'] = 'Rp '.priceFormat($s['total_bayar']);
				$s['pelunasan_total_show'] = 'Rp '.priceFormat($s['pelunasan_total']);
				
				$s['no_ref'] = '-';
				if($s['pelunasan_status'] == 'jurnal'){
					$s['no_ref'] = $s['no_registrasi'];
					$s['pelunasan_status_text'] = '<span style="color:blue;">Jurnal</span>';
				}else 
				if($s['pelunasan_status'] == 'posting'){
					$s['no_ref'] = $s['no_jurnal'];
					$s['pelunasan_status_text'] = '<span style="color:green;">Posting</span>';
				}
				
				if(empty($s['no_ref'])){
					$s['no_ref'] = '-';
				}
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*public function gridDataDetail_AP(){
		
		
		$this->prefix_pos = config_item('db_prefix2');
		$this->table = $this->prefix.'_kontrabon';
		$this->table_account_payable = $this->prefix.'account_payable';
		$this->table_supplier = $this->prefix_pos.'supplier';
		
		
		$ap_name = $this->input->post('ap_name');
		$supplier_id = $this->input->post('supplier_id');
		$all_ap_no = $this->input->post('all_ap_no');
		
		$this->db->select("a.*, a.id as ap_id, b.supplier_name");
		$this->db->from($this->table_account_payable." as a");
		$this->db->join($this->table_supplier." as b", "b.id = a.supplier_id", "LEFT");
		$this->db->where("a.ap_used = 0");
		$this->db->where("a.ap_status = 'posting'");
		
		if(empty($all_ap_no)){
			$this->db->where("a.ap_name = '".$ap_name."'");
			
			if(!empty($supplier_id)){
				$this->db->where("a.supplier_id = '".$supplier_id."'");
			}
		}
		
		$get_ap = $this->db->get();
		
		$nama_supplier_id = array();
		$newData = array();
		if($get_ap->num_rows() > 0){
			foreach($get_ap->result() as $dt){
				
				if(empty($dt->supplier_id)){
					$dt->supplier_id = 0;
				}
				
				if(empty($all_ap_no)){
					
					$nama_supplier_id_cek = $dt->ap_name." ".$dt->supplier_id;
					if(!in_array($nama_supplier_id_cek, $nama_supplier_id)){
						$nama_supplier_id[] = $nama_supplier_id_cek;
						$dt->ap_name_supplier = $dt->ap_name." ".$dt->supplier_name;
						
						if(!empty($dt->supplier_name)){
							$dt->ap_name_supplier = $dt->supplier_name." (Supplier)";
						}
						
						$dt->total_tagihan_show = priceFormat($dt->total_tagihan);
						
						$dt->total_bayar = 0;
						$dt->total_bayar_show = priceFormat($dt->total_bayar);
						
						$dt->id = 'new_'.$dt->id;
						
						$newData[] = $dt;
					}
					
				}else{
					
					$dt->ap_name_supplier = $dt->ap_name." ".$dt->supplier_name;
						
					if(!empty($dt->supplier_name)){
						$dt->ap_name_supplier = $dt->supplier_name." (Supplier)";
					}
					
					$dt->ap_no_name = $dt->ap_no." / ".$dt->ap_name_supplier;
					
					
					$dt->total_tagihan_show = priceFormat($dt->total_tagihan);
					
					$dt->total_bayar = 0;
					$dt->total_bayar_show = priceFormat($dt->total_bayar);
					
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
		$this->table_account_payable = $this->prefix.'account_payable';	
		$this->table_kontrabon = $this->prefix.'kontrabon';		
		$session_user = $this->session->userdata('user_username');
		
		$r = array('success' => false,"info" => "Save Kontrabon Failed!");
		
		$id = $this->input->post('id');
		$kb_date = $this->input->post('kb_date');
		$tanggal_jatuh_tempo = $this->input->post('tanggal_jatuh_tempo');
		$kb_name = $this->input->post('kb_name');
		$kb_no = $this->input->post('kb_no');
		$supplier_id = $this->input->post('supplier_id');
		$kb_notes = $this->input->post('kb_notes');
		$kb_status = $this->input->post('kb_status');
		$total_tagihan = $this->input->post('total_tagihan');
		$total_bayar = $this->input->post('total_bayar');
		$created = $this->input->post('created');
		
		if(empty($kb_name)){
			$r = array('success' => false, "info" => "KB name cannot empty!");
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
		$kbDetail = $this->input->post('kbDetail');
		$kbDetail = json_decode($kbDetail, true);
		if(!empty($kbDetail)){
			$total_ap = count($kbDetail);
		}
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
			
		$r = '';
		if($this->input->post('form_type_kontrabonAP', true) == 'add')
		{
			
			$kb_no = $this->m->generate_kb_number();

			$var = array(
				'fields'	=>	array(
				    'kb_no'  		=> 	$kb_no,
				    'kb_date' 		=> 	$kb_date,
				    'tanggal_jatuh_tempo' 		=> 	$tanggal_jatuh_tempo,
				    'kb_name' 		=> 	$kb_name,
				    'supplier_id' 		=> 	$supplier_id,
				    'kb_status' 		=> 	$kb_status,
				    'kb_notes' 		=> 	$kb_notes,
				    'total_tagihan' => 	$total_tagihan,
				    'total_bayar' => 	$total_bayar,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'	=>	$is_active
				),
				'table'		=>  $this->table_kontrabon
			);	
			
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
				$q = $this->m->add($var);
				$insert_id = $this->m->get_insert_id();
			$this->lib_trans->commit();			
			if($q)
			{  
				$r = array('success' => true, 'id' => $insert_id, 'kb_no'	=> '-', 'det_info' => array()); 		
				$q_det = $this->m->kbDetail($kbDetail, $insert_id);
				if(!empty($q_det['dtAP']['kb_no'])){
					$r['kb_no'] = $q_det['dtAP']['kb_no'];
				}
				$r['det_info'] = $q_det;
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_kontrabonAP', true) == 'edit'){
			
			$var = array('fields'	=>	array(
					'kb_date'=> 	$kb_date,
					'tanggal_jatuh_tempo'=> 	$tanggal_jatuh_tempo,
					'kb_name'=> 	$kb_name,
					'supplier_id' 		=> 	$supplier_id,
					'kb_notes' 		=> 	$kb_notes,
					'total_tagihan' => 	$total_tagihan,
					'total_bayar' => 	$total_bayar,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				),
				'table'			=>  $this->table_kontrabon,
				'primary_key'	=>  'id'
			);
			
			$id = $this->input->post('id', true);
			
			//CEK OLD DATA
			$this->db->from($this->table_kontrabon);
			$this->db->where("id = '".$id."'");
			$get_dt = $this->db->get();	
			if($get_dt->num_rows() > 0){
				$old_data = $get_dt->row_array();
			}	
			
			if($old_data['kb_status'] == 'done'){
				$r = array('success' => false, 'info' => 'Cannot Update, Kontrabon Been Done!'); 
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
				
				//check data main if been take
				$this->db->from($this->table_kontrabon);
				$this->db->where("id IN ('".$id."')");
				$this->db->where("kb_status = 'done'");
				$get_dt = $this->db->get();
				if($get_dt->num_rows() > 0){
					//status is DONE!
					$is_status_done = true;
				}
				
				if($is_status_done == false){
					$q_det = $this->m->kbDetail($kbDetail, $id);
					$r['det_info'] = $q_det;
				}
				
				
			}  
			else
			{  
				$r = array('success' => false);
			}
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
		
		
	}*/
	
	public function saveDetail(){
		
		$this->prefix = config_item('db_prefix3');
		$this->table_pelunasan_ap = $this->prefix.'pelunasan_ap';
		$session_user = $this->session->userdata('user_username');
		
		$r = array('success' => false,"info" => "Save Pelunasan Failed!");
		
		$id = $this->input->post('id');
		$kb_id = $this->input->post('kb_id');
		$pelunasan_date = $this->input->post('pelunasan_date');
		$pelunasan_no = $this->input->post('pelunasan_no');
		$pelunasan_notes = $this->input->post('pelunasan_notes');
		$no_bukti = $this->input->post('no_bukti');
		//$pelunasan_status = $this->input->post('pelunasan_status');
		$pelunasan_total = $this->input->post('pelunasan_total');
		$autoposting_id = $this->input->post('autoposting_id');	

		
		//account_payable_non_accounting
		$opt_val = array(
			'account_payable_non_accounting'
		);
		
		$get_opt = get_option_value($opt_val);
		
		if(!empty($get_opt['account_payable_non_accounting'])){
			$account_payable_non_accounting  = $get_opt['account_payable_non_accounting'];
		}else{
			$account_payable_non_accounting = 0;
		}	
		
		if(empty($autoposting_id)){
			$r = array('success' => false, "info" => "Select AutoPosting!");
			die(json_encode($r));
		}
		
		if(empty($pelunasan_total)){
			$r = array('success' => false, "info" => "Pelunasan Total empty!");
			die(json_encode($r));
		}
		
		if(empty($pelunasan_total)){
			$pelunasan_total = 0;
		}
			
		$r = '';
		if($this->input->post('form_type_pelunasanAPDetail', true) == 'add')
		{
			
			$pelunasan_no = $this->m->generate_pelunasan_number();

			$var = array(
				'fields'	=>	array(
				    'kb_id'  		=> 	$kb_id,
				    'pelunasan_no'  		=> 	$pelunasan_no,
				    'pelunasan_date' 		=> 	$pelunasan_date,
				    //'pelunasan_status' 		=> 	'jurnal',
				    'pelunasan_notes' 		=> 	$pelunasan_notes,
				    'no_bukti' 		=> 	$no_bukti,
				    'pelunasan_total' 	=> 	$pelunasan_total,
				    'autoposting_id' 	=> 	$autoposting_id,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				),
				'table'		=>  $this->table_pelunasan_ap
			);	
			
			if($account_payable_non_accounting == 1){
				$var['fields']['pelunasan_status'] = 'posting';
			}
			
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
				$q = $this->m->add($var);
				$insert_id = $this->m->get_insert_id();
			$this->lib_trans->commit();			
			if($q)
			{  
				$r = array('success' => true, 'id' => $insert_id, 'pelunasan_no'	=> $pelunasan_no); 
				
				$kb_total_tagihan = $this->update_total_pelunasan($kb_id);
				
				if($account_payable_non_accounting == 1){
					$this->m->update_status_kb($pelunasan_no);
				}else{
					$this->save_to_jurnal($insert_id);
				}
				 
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_pelunasanAPDetail', true) == 'edit'){
			
			$var = array('fields'	=>	array(
					'kb_id'  		=> 	$kb_id,
					//'pelunasan_no'  		=> 	$pelunasan_no,
				    'pelunasan_date' 		=> 	$pelunasan_date,
				    //'pelunasan_status' 		=> 	$pelunasan_status,
				    'pelunasan_notes' 		=> 	$pelunasan_notes,
				    'no_bukti' 		=> 	$no_bukti,
				    'autoposting_id' 		=> 	$autoposting_id,
				    'pelunasan_total' 	=> 	$pelunasan_total,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				),
				'table'			=>  $this->table_pelunasan_ap,
				'primary_key'	=>  'id'
			);
			
			if($account_payable_non_accounting == 1){
				$var['fields']['pelunasan_status'] = 'posting';
			}
			
			$id = $this->input->post('id', true);
			
			//CEK OLD DATA
			$this->db->from($this->table_pelunasan_ap);
			$this->db->where("id = '".$id."'");
			$get_dt = $this->db->get();	
			if($get_dt->num_rows() > 0){
				$old_data = $get_dt->row_array();
			}	
			
			if($old_data['pelunasan_status'] == 'posting' AND $account_payable_non_accounting == 0){
				$r = array('success' => false, 'info' => 'Update Gagal, Pelunasaan sudah di Posting!'); 
				die(json_encode($r));
			}
			
			
			//UPDATE
			$this->lib_trans->begin();
				$update = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				$r = array('success' => true, 'id' => $id);
				
				$is_status_done = false;
				
				//JIKA ALL DONE => UPDATE KB
				$kb_total_tagihan = $this->update_total_pelunasan($kb_id);
				
				if($account_payable_non_accounting == 1){
					$this->m->update_status_kb($pelunasan_no);
				}
				
			}  
			else
			{  
				$r = array('success' => false);
			}
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
		
		
	}
	
	public function save_to_jurnal($id = ''){
		
		$this->table_pelunasan_ap = $this->prefix.'pelunasan_ap';	
		$this->table_kontrabon = $this->prefix.'kontrabon';	
		$this->table_autoposting = $this->prefix.'autoposting';	
		$this->table_jurnal_header = $this->prefix.'jurnal_header';			
		$this->table_jurnal_detail = $this->prefix.'jurnal_detail';			
		$this->table_tipe_jurnal = $this->prefix.'tipe_jurnal';			
		$session_user = $this->session->userdata('user_username');
		
		//$id = $this->input->post('id');
		if(empty($id)){
			return array('success' => false,"info" => "No ID!");
		}
		
		$r = $r = array('success' => false,"info" => "Save to Jurnal Failed!");
		
		if(!empty($id)){
			
			//get data account_payable
			$this->db->select("a.*, b.rek_id_debet, b.rek_id_kredit, c.kb_no, c.kb_name");
			$this->db->from($this->table_pelunasan_ap." as a");
			$this->db->join($this->table_autoposting." as b","b.id = a.autoposting_id","LEFT");
			$this->db->join($this->table_kontrabon." as c","c.id = a.kb_id","LEFT");
			$this->db->where("a.id", $id);
			$get_pelunasan = $this->db->get();
			
			if($get_pelunasan->num_rows() > 0){
				$dt_pelunasan = $get_pelunasan->row();
				
				if(empty($dt_pelunasan->autoposting_id)){
					$r = array('success' => false, "info" => "AutoPosting Cannot Empty!");
					die(json_encode($r));
				}
				
				if($dt_pelunasan->pelunasan_status == 'jurnal'){
					
					$this->db->from($this->table_jurnal_header);
					$this->db->where("ref_no", $dt_pelunasan->pelunasan_no);
					$this->db->where("jurnal_from", 'account_payable');
					$get_jurnal = $this->db->get();
					
					if($get_jurnal->num_rows() > 0){
						$dt_jurnal = $get_jurnal->row();
					}
					
					
					//CREATE JURNAL--------------------
					$opt_val = array(
						'kd_tipe_jurnal_pelunasan_ap'
					);
					
					$get_opt = get_option_value($opt_val);
					
					if(!empty($get_opt['kd_tipe_jurnal_pelunasan_ap'])){
						$kd_tipe_jurnal  = $get_opt['kd_tipe_jurnal_pelunasan_ap'];
					}else{
						$r = array('success' => false,"info" => "Option Variable: kd_tipe_jurnal_pelunasan_ap not found!");
						die(json_encode($r));
					}	
					
					$rek_id_debet  = $dt_pelunasan->rek_id_debet;
					$rek_id_kredit  = $dt_pelunasan->rek_id_kredit;
					
					$nama_tipe_jurnal = '';
					$tgl_registrasi = date("Y-m-d");
					$keterangan = 'Pelunasan AP: '.$dt_pelunasan->pelunasan_no;
					$total = $dt_pelunasan->pelunasan_total;
					$is_balance = 1;
					
					$exp_tgl = explode("-",$tgl_registrasi);
					$periode = $exp_tgl[1];
					$tahun = $exp_tgl[0];
					
					//get tipe jurnal
					$this->db->from($this->table_tipe_jurnal);
					$this->db->where("kd_tipe_jurnal", $kd_tipe_jurnal);
					$get_tipe_jurnal = $this->db->get();
					
					if($get_tipe_jurnal->num_rows() > 0){
						$dt_tipe_jurnal = $get_tipe_jurnal->row();
						$nama_tipe_jurnal = $dt_tipe_jurnal->nama_tipe_jurnal;
					}
					
					if(empty($dt_jurnal)){
						//ADD NEW
						if(empty($no_registrasi) OR strtolower($no_registrasi) == 'auto'){
							$no_registrasi = $this->jurnal->generate_no_registrasi();
						}
						
						if(empty($no_registrasi)){
							$r = array('success' => false, 'info' => $no_registrasi." Gagal Create No Jurnal!");
							die(json_encode($r));
						}	
						
						$data_jurnal = array(
							'no_registrasi'  	=> $no_registrasi,
							'kd_tipe_jurnal'  	=> $kd_tipe_jurnal,
							'tgl_registrasi'  	=> $tgl_registrasi,
							'keterangan'  		=> $keterangan,
							'total'  			=> $total,
							'status' 			=> 'jurnal',
							'jurnal_from' 		=> 'pelunasan_account_payable',
							'ref_no' 			=> $dt_pelunasan->pelunasan_no,
							'is_posting' 		=> 0,
							'is_balance' 		=> $is_balance,
							'ket_periode' 		=> $nama_tipe_jurnal,
							'periode'  			=> $periode,
							'tahun'  			=> $tahun,
							'created'			=> date('Y-m-d H:i:s'),
							'createdby'			=> $session_user,
							'updated'			=> date('Y-m-d H:i:s'),
							'updatedby'			=> $session_user
						);	
						
						//SAVE
						$insert_id = false;
						$this->lib_trans->begin();
							$q = $this->db->insert($this->table_jurnal_header, $data_jurnal);
							$insert_id = $this->db->insert_id();
						$this->lib_trans->commit();			
						if($q)
						{  
					
							//ID AKUN HUTANG
							$r = array('success' => true, 'id' => $insert_id, 'no_registrasi'	=> $no_registrasi); 
							
							$dt_detail = array();
							
							//DEBET
							$dt_detail[] = array(
								'id'			=> 'new_1',
								'jurnal_header_id'	=> $insert_id,
								'rek_id'		=> $rek_id_debet,
								'tgl_transaksi'	=> $tgl_registrasi,
								'posisi'		=> 'D',
								'jml_debet'		=> $total,
								'jml_kredit'	=> 0,
								'keterangan'	=> $keterangan,
								'detail_status'	=> 'jurnal',
								'created'		=> date('Y-m-d H:i:s'),
								'createdby'		=> $session_user,
								'updated'		=> date('Y-m-d H:i:s'),
								'updatedby'		=> $session_user,
								'nama_tujuan'	=> $dt_pelunasan->kb_name,
								'no_transaksi'	=> $dt_pelunasan->kb_no
							);
							
							//KREDIT
							$dt_detail[] = array(
								'id'			=> 'new_2',
								'jurnal_header_id'	=> $insert_id,
								'rek_id'		=> $rek_id_kredit,
								'tgl_transaksi'	=> $tgl_registrasi,
								'posisi'		=> 'K',
								'jml_debet'		=> 0,
								'jml_kredit'	=> $total,
								'keterangan'	=> $keterangan,
								'detail_status'	=> 'jurnal',
								'created'		=> date('Y-m-d H:i:s'),
								'createdby'		=> $session_user,
								'updated'		=> date('Y-m-d H:i:s'),
								'updatedby'		=> $session_user,
								'nama_tujuan'	=> $dt_pelunasan->kb_name,
								'no_transaksi'	=> $dt_pelunasan->kb_no
							);
							
							$q_det = $this->jurnal_detail->mjDetail($dt_detail, $insert_id);
							
							//update status to jurnal
							$set_status = array("pelunasan_status" => 'jurnal', 'jurnal_id' => $insert_id);
							$this->db->update($this->table_pelunasan_ap, $set_status, "id = '".$id."'");
							
						}  
						else
						{  
							$r = array('success' => false);
						}
						
					}else{
						
						$no_registrasi = $dt_jurnal->no_registrasi;
						$data_jurnal = array(
							'kd_tipe_jurnal'  	=> $kd_tipe_jurnal,
							'tgl_registrasi'  	=> $tgl_registrasi,
							'keterangan'  		=> $keterangan,
							'total'  			=> $total,
							'status' 			=> 'jurnal',
							'jurnal_from' 		=> 'pelunasan_account_payable',
							'ref_no' 			=> $dt_pelunasan->pelunasan_no,
							'is_posting' 		=> 0,
							'is_balance' 		=> $is_balance,
							'ket_periode' 		=> $nama_tipe_jurnal,
							'periode'  			=> $periode,
							'tahun'  			=> $tahun,
							'updated'			=> date('Y-m-d H:i:s'),
							'updatedby'			=> $session_user
						);	
						
						//SAVE
						$insert_id = false;
						$this->lib_trans->begin();
							$q = $this->db->update($this->table_jurnal_header, $data_jurnal, "id = '".$dt_jurnal->id."'");
							$insert_id = $dt_jurnal->id;
						$this->lib_trans->commit();			
						if($q)
						{  
					
							//ID AKUN HUTANG
							$r = array('success' => true, 'id' => $insert_id, 'no_registrasi'	=> $no_registrasi); 
							
							//get_detail
							$this->db->from($this->table_jurnal_detail);
							$this->db->where("jurnal_header_id", $insert_id);
							$get_jurnal_detail = $this->db->get();
							
							$dt_detail = array();
							if($get_jurnal_detail->num_rows() > 0){
								foreach($get_jurnal_detail->result() as $dt){
									if($dt->posisi == 'D'){
										//DEBET
										$dt_detail[] = array(
											'id'			=> $dt->id,
											'jurnal_header_id'	=> $dt->jurnal_header_id,
											'rek_id'		=> $rek_id_debet,
											'tgl_transaksi'	=> $tgl_registrasi,
											'posisi'		=> 'D',
											'jml_debet'		=> $total,
											'jml_kredit'	=> 0,
											'keterangan'	=> $keterangan,
											'detail_status'	=> 'jurnal',
											'created'		=> date('Y-m-d H:i:s'),
											'createdby'		=> $session_user,
											'updated'		=> date('Y-m-d H:i:s'),
											'updatedby'		=> $session_user,
											'nama_tujuan'	=> $dt_pelunasan->kb_name,
											'no_transaksi'	=> $dt_pelunasan->kb_no
										);
									}else{
										//KREDIT
										$dt_detail[] = array(
											'id'			=> $dt->id,
											'jurnal_header_id'	=> $dt->jurnal_header_id,
											'rek_id'		=> $rek_id_kredit,
											'tgl_transaksi'	=> $tgl_registrasi,
											'posisi'		=> 'K',
											'jml_debet'		=> 0,
											'jml_kredit'	=> $total,
											'keterangan'	=> $keterangan,
											'detail_status'	=> 'jurnal',
											'created'		=> date('Y-m-d H:i:s'),
											'createdby'		=> $session_user,
											'updated'		=> date('Y-m-d H:i:s'),
											'updatedby'		=> $session_user,
											'nama_tujuan'	=> $dt_pelunasan->kb_name,
											'no_transaksi'	=> $dt_pelunasan->kb_no
										);
									}
								}
								
								$q_det = $this->jurnal_detail->mjDetail($dt_detail, $insert_id);
								
							}
							
							//update status to jurnal
							$set_status = array("pelunasan_status" => 'jurnal', 'jurnal_id' => $insert_id);
							$this->db->update($this->table_pelunasan_ap, $set_status, "id = '".$id."'");
							
						}  
						else
						{  
							$r = array('success' => false);
						}
						
						
					}
					
					
					
				}else{
					
					$r = array('success' => false,"info" => "Save to Jurnal Failed!<br/>Status AP: ".ucwords($dt_pelunasan->pelunasan_status));
					
				}
				
			}
			
		}
		
		return json_encode(($r==null or $r=='')? array('success'=>false) : $r);
		
	}
	
	public function generate_pelunasan_number(){
		$this->table = $this->prefix.'pelunasan_ap';		

		$getDate = date("ym");
		
		$this->db->from($this->table);
		$this->db->where("pelunasan_no LIKE 'PL".$getDate."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_pl = $get_last->row();
			$pelunasan_no = str_replace("PL".$getDate,"", $data_pl->pelunasan_no);
			$pelunasan_no = str_replace("PL","", $pelunasan_no);
						
			$pelunasan_no = (int) $pelunasan_no;			
		}else{
			$pelunasan_no = 0;
		}
		
		$pelunasan_no++;
		$length_no = strlen($pelunasan_no);
		switch ($length_no) {
			case 3:
				$pelunasan_no = $pelunasan_no;
				break;
			case 2:
				$pelunasan_no = '0'.$pelunasan_no;
				break;
			case 1:
				$pelunasan_no = '00'.$pelunasan_no;
				break;
			default:
				$pelunasan_no = '00'.$pelunasan_no;
				break;
		}
				
		return 'PL'.$getDate.$pelunasan_no;				
	}
	
	public function delete()
	{
		
		$this->table = $this->prefix.'pelunasan_ap';
		$this->table2 = $this->prefix.'pelunasan_ap';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//Get KB
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		$get_pl = $this->db->get();
		
		$data_pl = array();
		if($get_pl->num_rows() > 0){
			
			$data_pl = $get_pl->row();
			if($data_pl->pelunasan_status == 'posting'){
				$r = array('success' => false, 'info' => 'Status Pelunasaan sudah Posting!'); 
			}
			
		}else{
			$r = array('success' => false, 'info' => 'Data Not Found!'); 
		}
		
		if(empty($data_pl)){
			$r = array('success' => false, 'info' => 'Data Not Found!'); 
		}
		
		
		//delete data
		$update_data = array(
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
            $r = array('success' => false, 'info' => 'Delete Pelunasan Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function deleteDetail(){
		$this->table = $this->prefix.'pelunasan_ap';
		$this->table_jurnal_header = $this->prefix.'jurnal_header';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//Get ap_id
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		$get_data = $this->db->get();
	
		//account_payable_non_accounting
		$opt_val = array(
			'account_payable_non_accounting'
		);
		
		$get_opt = get_option_value($opt_val);
		
		if(!empty($get_opt['account_payable_non_accounting'])){
			$account_payable_non_accounting  = $get_opt['account_payable_non_accounting'];
		}else{
			$account_payable_non_accounting = 0;
		}	
		
		$data_det = array();
		if($get_data->num_rows() > 0){
			
			$data_det = $get_data->row();
			
			
			if(empty($account_payable_non_accounting)){
				
				if($data_det->pelunasan_status == 'posting'){
					$r = array('success' => false, 'info' => 'Pelunasan sudah di Posting!<br/>lakukan Unposting pada mutasi jurnal (akunting)'); 
					die(json_encode($r));
				}
				
				//jurnal_from, ref_no, 
				$this->db->select('*');
				$this->db->from($this->table_jurnal_header);
				$this->db->where("ref_no = '".$data_det->pelunasan_no."'");
				$this->db->where("jurnal_from = 'pelunasan_account_payable'");
				$get_jurnal = $this->db->get();
				
				if($get_jurnal->num_rows() > 0){
					$data_jurnal = $get_jurnal->row();
					if($data_jurnal->status == 'posting' OR $data_jurnal->is_posting){
						$r = array('success' => false, 'info' => 'Pelunasan sudah di Posting!<br/>lakukan Unposting pada mutasi jurnal (akunting)'); 
						die(json_encode($r));
					}
				}
			}
			
		}else{
			$r = array('success' => false, 'info' => 'Data Not Found!'); 
			die(json_encode($r));
		}
		
		if(empty($data_det)){
			$r = array('success' => false, 'info' => 'Data Not Found!'); 
			die(json_encode($r));
		}
		
		//delete data
		//$this->db->where("id IN ('".$sql_Id."')");
		//$q = $this->db->delete($this->table);
		
		//delete data
		$update_data = array(
			'is_deleted'=> 1
		);
		
		$q = $this->db->update($this->table, $update_data, "id IN ('".$sql_Id."')");
		
		$r = '';
		if($q)  
        {  
			
			
			//Update detail calc
			$kb_total_tagihan = $this->update_total_pelunasan($data_det->kb_id);
			
			if(empty($kb_total_tagihan)){
				$kb_total_tagihan = 0;
			}
			
			
			if(empty($account_payable_non_accounting)){
				//Update Acc
				$update_data = array(
					'is_deleted'=> 1
				);
				
				$this->db->update($this->table_jurnal_header, $update_data, "ref_no = '".$data_det->pelunasan_no."' AND jurnal_from = 'pelunasan_account_payable'");
			}else{
				$this->m->update_status_kb($data_det->pelunasan_no);
			}
			
            $r = array('success' => true, 'kb_id' => $data_det->kb_id, 'kb_total_tagihan' => $kb_total_tagihan); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Kontrabon Detail Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function update_total_pelunasan($kb_id){
		
		$this->table_kontrabon = $this->prefix.'kontrabon';	
		$this->table_pelunasan_ap = $this->prefix.'pelunasan_ap';	
		
		$this->db->select('SUM(pelunasan_total) as pelunasan_total_all');
		$this->db->from($this->table_pelunasan_ap);
		$this->db->where('kb_id', $kb_id);
		$this->db->where('pelunasan_status', 'posting');
		$this->db->where('is_deleted', 0);
		$get_tot = $this->db->get();
		
		$pelunasan_total_all = 0;
		if($get_tot->num_rows() > 0){
			$data_kb = $get_tot->row();
			$pelunasan_total_all = $data_kb->pelunasan_total_all;
		}
		
		//Update KB
		$update_KB = array(
			'total_bayar'  => $pelunasan_total_all				
		);
		
		$this->db->update($this->table_kontrabon, $update_KB, "id = ".$kb_id);
		
		return $pelunasan_total_all;
	}
	
	public function printPelunasan(){
		
		$this->prefix_pos = config_item('db_prefix2');
		$this->table_kontrabon = $this->prefix.'kontrabon';	
		$this->table_pelunasan_ap = $this->prefix.'pelunasan_ap';	
		$this->table_account_payable = $this->prefix.'account_payable';	
		$this->table_supplier = $this->prefix_pos.'supplier';	
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
			die('User Session Expired, Please Re-Login!');
		}
		
		extract($_GET);
		
		$data_post = array(
			'do'	=> '',
			'po_data'	=> array(),
			'po_detail'	=> array(),
			'report_name'	=> 'PELUNASAN KONTRABON',
			'report_place_default'	=> '',
			'user_fullname'	=> $user_fullname,
			'client'	=> $dt_client
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($kontrabon_id)){
			die('Kontrabon Not Found!');
		}else{
			
			$this->db->select("a.*, b.supplier_name, b.supplier_address, b.supplier_phone, b.supplier_fax, b.supplier_email, b.supplier_contact_person");
			$this->db->from($this->table_kontrabon." as a");
			$this->db->join($this->table_supplier." as b","b.id = a.supplier_id", "LEFT");
			$this->db->where("a.id = '".$kontrabon_id."'");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['kb_data'] = $get_dt->row_array();
				
				//get detail
				$this->db->select("a.*, b.kb_no, b.kb_name, b.total_tagihan, b.total_bayar, c.no_registrasi, c.no_jurnal, d.autoposting_name");
				$this->db->from($this->table_pelunasan_ap." as a");
				$this->db->join($this->prefix.'kontrabon as b','b.id = a.kb_id','LEFT');
				$this->db->join($this->prefix.'jurnal_header as c','c.id = a.jurnal_id','LEFT');
				$this->db->join($this->prefix.'autoposting as d','d.id = a.autoposting_id','LEFT');
				$this->db->where("a.kb_id = '".$kontrabon_id."'");
				$this->db->where("a.pelunasan_status = 'posting'");
				$this->db->where("a.is_deleted = 0");
				$get_det = $this->db->get();
				if($get_det->num_rows() > 0){
					$data_post['kb_detail'] = $get_det->result_array();
				}
				
			}else{
				die('Kontrabon Detail Not Found!');
			}
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		$useview = 'printPelunasanAP';
		if($do == 'excel'){
			$useview = 'excelPelunasanAP';
		}
		
		$this->load->view('../../account_payable/views/'.$useview, $data_post);
		
	}
}