<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class AccountPayable extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix3');
		$this->load->model('model_account_payable', 'm');
		$this->load->model('accounting/model_acc_mutasi_jurnal', 'jurnal');
		$this->load->model('accounting/model_acc_mutasi_jurnal_detail', 'jurnal_detail');
	}
	
	public function gridData(){
		
		$this->table = $this->prefix.'account_payable';
		
		$sortAlias = array(
			'ap_status_text'	=> 'ap_status',
			'total_tagihan_show'	=> 'total_tagihan',
			'ap_used_text'	=> 'ap_used'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, b.no_registrasi, b.no_jurnal, c.autoposting_name',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'jurnal_header as b','b.id = a.jurnal_id','LEFT'),
										array($this->prefix.'autoposting as c','c.id = a.autoposting_id','LEFT')
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
				
				$params['where'][] = "(a.ap_date >= '".$qdate_from."' AND a.ap_date <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.ap_no' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.no_ap LIKE '%".$searching."%' OR a.ap_name LIKE '%".$searching."%' OR a.no_ref LIKE '%".$searching."%')";
		}		
		if(!empty($status)){
			$params['where'][] = "a.ap_status = '".$status."'";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		
  		$newData = array();	
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				if($s['ap_status'] == 'pengakuan'){
					$s['ap_status_text'] = '<span style="color:blue;">Pengakuan</span>';
				}else 
				if($s['ap_status'] == 'jurnal'){
					$s['ap_status_text'] = '<span style="color:blue;">Jurnal</span>';
				}else 
				if($s['ap_status'] == 'posting'){
					$s['ap_status_text'] = '<span style="color:orange;">Posting</span>';
				}else 
				if($s['ap_status'] == 'kontrabon'){
					$s['ap_status_text'] = '<span style="color:orange;">Kontrabon</span>';
				}else 
				if($s['ap_status'] == 'pembayaran'){
					$s['ap_status_text'] = '<span style="color:green;">Pembayaran</span>';
				}else{
					$s['ap_status_text'] = '<span style="color:red;">Cancel</span>';
				}
				
				$s['ap_tipe_text'] = ucwords($s['ap_tipe']);
				$s['old_ap_tipe'] = $s['ap_tipe'];
				
				$s['ap_date'] = date("d-m-Y",strtotime($s['ap_date']));
				$s['total_tagihan_show'] = priceFormat($s['total_tagihan']);
				
				
				if(empty($s['no_posting'])){
					$s['no_posting'] = $s['no_jurnal'];
				}
				
				if(empty($s['no_jurnal'])){
					$s['no_jurnal'] = $s['no_registrasi'];
				}
				
				if(empty($s['tanggal_tempo']) OR $s['tanggal_tempo'] == '0000-00-00'){
					$s['tanggal_tempo'] = '-';
				}else{
					$s['tanggal_tempo'] = date("d-m-Y",strtotime($s['tanggal_tempo']));
				}
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function gridDataName(){
		
		$this->prefix_pos = config_item('db_prefix2');
		$this->table_account_payable = $this->prefix.'account_payable';
		$this->table_supplier = $this->prefix_pos.'supplier';
		
		$this->db->select("a.*, b.supplier_name");
		$this->db->from($this->table_account_payable." as a");
		$this->db->join($this->table_supplier." as b", "b.id = a.supplier_id", "LEFT");
		
		
		$in_edit = $this->input->post('in_edit');
		$ap_name = $this->input->post('ap_name');
		$supplier_id = $this->input->post('supplier_id');
		
		if(!empty($in_edit)){
			$this->db->where("(a.ap_used = 0 AND a.ap_status = 'posting') OR (a.ap_name = '".$ap_name."' AND a.supplier_id = '".$supplier_id."' AND a.ap_used = 1 AND a.ap_status = 'kontrabon')");
		}else{
			$this->db->where("a.ap_used = 0");
			$this->db->where("a.ap_status = 'posting'");
		}
		
		$get_ap = $this->db->get();
		
		$nama_supplier_id = array();
		$newData = array();
		if($get_ap->num_rows() > 0){
			foreach($get_ap->result() as $dt){
				
				$nama_supplier_id_cek = $dt->ap_name." ".$dt->supplier_id;
				if(!in_array($nama_supplier_id_cek, $nama_supplier_id)){
					$nama_supplier_id[] = $nama_supplier_id_cek;
					$dt->ap_name_supplier = $dt->ap_name." ".$dt->supplier_name;
					
					if(!empty($dt->supplier_name)){
						$dt->ap_name_supplier = $dt->supplier_name." (Supplier)";
					}
					
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
		
		$this->table_account_payable = $this->prefix.'account_payable';	
		$this->table_jurnal_header = $this->prefix.'jurnal_header';			
		$this->table_tipe_jurnal = $this->prefix.'tipe_jurnal';			
		$session_user = $this->session->userdata('user_username');
		
		$r = array('success' => false,"info" => "Save AP Failed!");
		
		$id = $this->input->post('id');
		$po_id = $this->input->post('po_id');
		$supplier_id = $this->input->post('supplier_id');
		$ap_tipe = $this->input->post('ap_tipe');
		$old_ap_tipe = $this->input->post('old_ap_tipe');
		$autoposting_id = $this->input->post('autoposting_id');
		$ap_no = $this->input->post('ap_no');
		$ap_date = $this->input->post('ap_date');
		$ap_name = $this->input->post('ap_name');
		$ap_address = $this->input->post('ap_address');
		$ap_phone = $this->input->post('ap_phone');
		$no_ref = $this->input->post('no_ref');
		$ap_notes = $this->input->post('ap_notes');
		$total_tagihan = $this->input->post('total_tagihan');
		$ap_status = $this->input->post('ap_status');
		
		
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
		
		
		if(empty($ap_name)){
			$r = array('success' => false, "info" => "AP name cannot empty!");
			die(json_encode($r));
		}		
		
		if(empty($no_ref)){
			$r = array('success' => false, "info" => "No Ref cannot empty!");
			die(json_encode($r));
		}		
		
		if(empty($autoposting_id)){
			$r = array('success' => false, "info" => "Select AutoPosting!");
			die(json_encode($r));
		}	
		
		
		if(empty($total_tagihan)){
			$r = array('success' => false, "info" => "Total cannot empty!");
			die(json_encode($r));
		}
		
		/*$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}*/
		
		if($old_ap_tipe == 'purchasing' AND !empty($po_id)  AND !empty($supplier_id)){
			if($old_ap_tipe == 'purchasing' AND $ap_tipe != 'purchasing'){
				$r = array('success' => false, "info" => "Cannot change AP tipe purchasing!");
				die(json_encode($r));
			}
		}
			
		$r = '';
		if($this->input->post('form_type_accountPayable', true) == 'add')
		{
			
			$ap_no = $this->m->generate_ap_number();

			$var = array(
				'fields'	=>	array(
				    'ap_no'  		=> 	$ap_no,
					'ap_date'		=>	$ap_date,
				    'ap_tipe' 		=> 	$ap_tipe,
				    'autoposting_id'=> 	$autoposting_id,
				    'ap_name' 		=> 	$ap_name,
				    'ap_address' 	=> 	$ap_address,
				    'ap_phone' 		=> 	$ap_phone,
				    'no_ref' 		=> 	$no_ref,
				    'ap_notes' 		=> 	$ap_notes,
				    'total_tagihan' => 	$total_tagihan,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					//'is_active'	=>	$is_active
				),
				'table'		=>  $this->table_account_payable
			);	
			
			if($account_payable_non_accounting == 1){
				$var['fields']['ap_status'] = 'posting';
			}
			
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
				$q = $this->m->add($var);
				$insert_id = $this->m->get_insert_id();
			$this->lib_trans->commit();			
			if($q)
			{  
				$r = array('success' => true, 'id' => $insert_id, 'ap_no' => $ap_no); 				
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_accountPayable', true) == 'edit'){
			
			//if($old_ap_tipe == 'purchasing' AND !empty($po_id)  AND !empty($supplier_id)){
			if($old_ap_tipe == 'purchasing' AND !empty($po_id)){
				$var = array('fields'	=>	array(
						'autoposting_id'=> 	$autoposting_id,
						'ap_address' 	=> 	$ap_address,
						'ap_notes' 		=> 	$ap_notes,
						'ap_date'		=>	$ap_date,
						'updated'		=>	date('Y-m-d H:i:s'),
						'updatedby'		=>	$session_user
					),
					'table'			=>  $this->table_account_payable,
					'primary_key'	=>  'id'
				);
				
				if($ap_status == 'pengakuan' OR $ap_status == 'jurnal'){
					
					if($account_payable_non_accounting == 1){
						$var['fields']['ap_status'] = 'posting';
					}
				}
				
			}else{
				
				if($old_ap_tipe != 'purchasing'){
					//JURNAL, POSTING, KONTRABON
					$var = array('fields'	=>	array(
							'autoposting_id'=> 	$autoposting_id,
							'ap_notes' 		=> 	$ap_notes,
							'ap_date'		=>	$ap_date,
							'updated'		=>	date('Y-m-d H:i:s'),
							'updatedby'		=>	$session_user
						),
						'table'			=>  $this->table_account_payable,
						'primary_key'	=>  'id'
					);
				}else{
					$var = array('fields'	=>	array(
							'autoposting_id'=> 	$autoposting_id,
							'ap_tipe' 		=> 	$ap_tipe,
							'ap_date'		=>	$ap_date,
							'ap_name' 		=> 	$ap_name,
							'ap_address' 	=> 	$ap_address,
							'ap_phone' 		=> 	$ap_phone,
							'no_ref' 		=> 	$no_ref,
							'ap_notes' 		=> 	$ap_notes,
							'total_tagihan' => 	$total_tagihan,
							'updated'		=>	date('Y-m-d H:i:s'),
							'updatedby'		=>	$session_user
						),
						'table'			=>  $this->table_account_payable,
						'primary_key'	=>  'id'
					);	
			
					if($account_payable_non_accounting == 1){
						$var['fields']['ap_status'] = 'posting';
					}
				}
				
				if($ap_status == 'pengakuan'){
					$var = array('fields'	=>	array(
							'autoposting_id'=> 	$autoposting_id,
							'ap_tipe' 		=> 	$ap_tipe,
							'ap_date'		=>	$ap_date,
							'ap_name' 		=> 	$ap_name,
							'ap_address' 	=> 	$ap_address,
							'ap_phone' 		=> 	$ap_phone,
							'no_ref' 		=> 	$no_ref,
							'ap_notes' 		=> 	$ap_notes,
							'total_tagihan' => 	$total_tagihan,
							'updated'		=>	date('Y-m-d H:i:s'),
							'updatedby'		=>	$session_user
						),
						'table'			=>  $this->table_account_payable,
						'primary_key'	=>  'id'
					);	
			
					if($account_payable_non_accounting == 1){
						$var['fields']['ap_status'] = 'posting';
					}
				}
				
				if($ap_status == 'posting'){
					
					if($account_payable_non_accounting == 1){
						$var = array('fields'	=>	array(
								'autoposting_id'=> 	$autoposting_id,
								'ap_tipe' 		=> 	$ap_tipe,	
								'ap_date'		=>	$ap_date,
								'ap_name' 		=> 	$ap_name,
								'ap_address' 	=> 	$ap_address,
								'ap_phone' 		=> 	$ap_phone,
								'no_ref' 		=> 	$no_ref,
								'ap_notes' 		=> 	$ap_notes,
								'total_tagihan' => 	$total_tagihan,
								'updated'		=>	date('Y-m-d H:i:s'),
								'updatedby'		=>	$session_user
							),
							'table'			=>  $this->table_account_payable,
							'primary_key'	=>  'id'
						);	
					}
					
				}
				
			}
			
			
			
			//UPDATE
			$this->lib_trans->begin();
				$update = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				$r = array('success' => true, 'id' => $id);
			}  
			else
			{  
				$r = array('success' => false);
			}
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
		
		
	}
	
	public function save_to_jurnal(){
		
		$this->table_account_payable = $this->prefix.'account_payable';	
		$this->table_autoposting = $this->prefix.'autoposting';	
		$this->table_jurnal_header = $this->prefix.'jurnal_header';			
		$this->table_jurnal_detail = $this->prefix.'jurnal_detail';			
		$this->table_tipe_jurnal = $this->prefix.'tipe_jurnal';			
		$session_user = $this->session->userdata('user_username');
		
		$id = $this->input->post('id');
		$autoposting_id = $this->input->post('autoposting_id');
		
		$r = array('success' => false,"info" => "Save to Jurnal Failed!");
		
		if(!empty($id)){
			
			if(!empty($autoposting_id)){
				$update_autoposting = array("autoposting_id" => $autoposting_id);
				$update_ap = $this->db->update($this->table_account_payable, $update_autoposting,"id = ".$id);
			}
			
			//get data account_payable
			$this->db->select("a.*, b.rek_id_debet, b.rek_id_kredit");
			$this->db->from($this->table_account_payable." as a");
			$this->db->join($this->table_autoposting." as b","b.id = a.autoposting_id","LEFT");
			$this->db->where("a.id", $id);
			$get_ap = $this->db->get();
			
			if($get_ap->num_rows() > 0){
				$dt_ap = $get_ap->row();
				
				if(empty($dt_ap->autoposting_id)){
					$r = array('success' => false, "info" => "AutoPosting Cannot Empty!");
					die(json_encode($r));
				}
				
				if($dt_ap->ap_status == 'pengakuan'){
					
					$this->db->from($this->table_jurnal_header);
					$this->db->where("ref_no", $dt_ap->ap_no);
					$this->db->where("jurnal_from", 'account_payable');
					$get_jurnal = $this->db->get();
					
					if($get_jurnal->num_rows() > 0){
						$dt_jurnal = $get_jurnal->row();
					}
					
					
					//CREATE JURNAL--------------------
					$opt_val = array(
						'kd_tipe_jurnal_ap'
					);
					
					$get_opt = get_option_value($opt_val);
					
					if(!empty($get_opt['kd_tipe_jurnal_ap'])){
						$kd_tipe_jurnal  = $get_opt['kd_tipe_jurnal_ap'];
					}else{
						$r = array('success' => false,"info" => "Option Variable: kd_tipe_jurnal_ap not found!");
						die(json_encode($r));
					}	
					
					$rek_id_debet  = $dt_ap->rek_id_debet;
					$rek_id_kredit  = $dt_ap->rek_id_kredit;
					
					$nama_tipe_jurnal = '';
					$tgl_registrasi = date("Y-m-d");
					$keterangan = 'Account Payable: '.$dt_ap->ap_no;
					$total = $dt_ap->total_tagihan;
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
							'jurnal_from' 		=> 'account_payable',
							'ref_no' 			=> $dt_ap->ap_no,
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
								'nama_tujuan'	=> $dt_ap->ap_name,
								'no_transaksi'	=> $dt_ap->ap_no
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
								'nama_tujuan'	=> $dt_ap->ap_name,
								'no_transaksi'	=> $dt_ap->ap_no
							);
							
							$q_det = $this->jurnal_detail->mjDetail($dt_detail, $insert_id);
							
							//update status to jurnal
							$set_status = array("ap_status" => 'jurnal', 'jurnal_id' => $insert_id);
							$this->db->update($this->table_account_payable, $set_status, "id = '".$id."'");
							
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
							'jurnal_from' 		=> 'account_payable',
							'ref_no' 			=> $dt_ap->ap_no,
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
											'nama_tujuan'	=> $dt_ap->ap_name,
											'no_transaksi'	=> $dt_ap->ap_no
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
											'nama_tujuan'	=> $dt_ap->ap_name,
											'no_transaksi'	=> $dt_ap->ap_no
										);
									}
								}
								
								$q_det = $this->jurnal_detail->mjDetail($dt_detail, $insert_id);
								
							}
							
							//update status to jurnal
							$set_status = array("ap_status" => 'jurnal', 'jurnal_id' => $insert_id);
							$this->db->update($this->table_account_payable, $set_status, "id = '".$id."'");
							
						}  
						else
						{  
							$r = array('success' => false);
						}
						
						
					}
					
					
					
				}else{
					
					$r = array('success' => false,"info" => "Save to Jurnal Failed!<br/>Status AP: ".ucwords($dt_ap->ap_status));
					
				}
				
			}
			
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
		
	}
	
	public function delete()
	{
		
		$this->table = $this->prefix.'account_payable';
		
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
		$get_ap = $this->db->get();
		
		$data_ap = array();
		if($get_ap->num_rows() > 0){
			
			$data_ap = $get_ap->row();
			if($data_ap->ap_status == 'kontrabon'){
				$r = array('success' => false, 'info' => 'Status AP Been Used on Kontrabon!'); 
				die(json_encode($r));
			}
			
		}else{
			$r = array('success' => false, 'info' => 'Data Not Found!'); 
			die(json_encode($r));
		}
		
		if(empty($data_ap)){
			$r = array('success' => false, 'info' => 'Data Not Found!'); 
			die(json_encode($r));
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
            $r = array('success' => false, 'info' => 'Delete AP Failed!'); 
        }
		die(json_encode($r));
	}
	
}