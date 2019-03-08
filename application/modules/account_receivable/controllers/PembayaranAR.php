<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class PembayaranAR extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix3');
		$this->load->model('model_pembayaranar', 'm');
		$this->load->model('accounting/model_acc_mutasi_jurnal', 'jurnal');
		$this->load->model('accounting/model_acc_mutasi_jurnal_detail', 'jurnal_detail');
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
		
		$params['where'][] = "a.is_deleted = 0";
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		
  		$newData = array();	
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				if($s['invoice_status'] == 'progress'){
					$s['invoice_status_text'] = '<span style="color:blue;">Progress</span>';
				}else 
				if($s['invoice_status'] == 'done'){
					$s['invoice_status_text'] = '<span style="color:red;">Done</span>';
				}
				
				$s['created'] = date("d-m-Y",strtotime($s['created']));
				$s['total_tagihan_show'] = 'Rp. '.priceFormat($s['total_tagihan']);
				$s['total_bayar_show'] = 'Rp. '.priceFormat($s['total_bayar']);
				
				$s['invoice_name_customer'] = $s['invoice_name'];
				if(!empty($s['customer_id'])){
					$s['invoice_name_customer'] = $s['invoice_name']." (Customer)";
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
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetail(){
		
		$this->table_pembayaran_ar = $this->prefix.'pembayaran_ar';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, b.invoice_no, b.invoice_name, b.total_tagihan, b.total_bayar, c.no_registrasi, c.no_jurnal, d.autoposting_name",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table_pembayaran_ar.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'invoice as b','b.id = a.invoice_id','LEFT'),
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
		$invoice_id = $this->input->post('invoice_id');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('b.invoice_no' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.invoice_no LIKE '%".$searching."%' OR b.invoice_name LIKE '%".$searching."%')";
		}
		if(!empty($invoice_id)){
			$params['where'][] = array('a.invoice_id' => $invoice_id);
		}
		
		$params['where'][] = array('a.is_deleted' => 0);
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
		$newData = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['total_tagihan_show'] = 'Rp '.priceFormat($s['total_tagihan']);
				$s['total_bayar_show'] = 'Rp '.priceFormat($s['total_bayar']);
				$s['pembayaran_total_show'] = 'Rp '.priceFormat($s['pembayaran_total']);
				
				$s['no_ref'] = '-';
				if($s['pembayaran_status'] == 'jurnal'){
					$s['no_ref'] = $s['no_registrasi'];
					$s['pembayaran_status_text'] = '<span style="color:blue;">Jurnal</span>';
				}else 
				if($s['pembayaran_status'] == 'posting'){
					$s['no_ref'] = $s['no_jurnal'];
					$s['pembayaran_status_text'] = '<span style="color:green;">Posting</span>';
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
	
	
	public function saveDetail(){
		
		$this->prefix = config_item('db_prefix3');
		$this->table_pembayaran_ar = $this->prefix.'pembayaran_ar';
		$session_user = $this->session->userdata('user_username');
		
		$r = array('success' => false,"info" => "Save Pembayaran Failed!");
		
		$id = $this->input->post('id');
		$invoice_id = $this->input->post('invoice_id');
		$pembayaran_date = $this->input->post('pembayaran_date');
		$pembayaran_no = $this->input->post('pembayaran_no');
		$pembayaran_notes = $this->input->post('pembayaran_notes');
		$no_bukti = $this->input->post('no_bukti');
		//$pembayaran_status = $this->input->post('pembayaran_status');
		$pembayaran_total = $this->input->post('pembayaran_total');
		$autoposting_id = $this->input->post('autoposting_id');	

		
		//account_receivable_non_accounting
		$opt_val = array(
			'account_receivable_non_accounting'
		);
		
		$get_opt = get_option_value($opt_val);
		
		if(!empty($get_opt['account_receivable_non_accounting'])){
			$account_receivable_non_accounting  = $get_opt['account_receivable_non_accounting'];
		}else{
			$account_receivable_non_accounting = 0;
		}	
		
		if(empty($autoposting_id)){
			$r = array('success' => false, "info" => "Select AutoPosting!");
			die(json_encode($r));
		}
		
		if(empty($pembayaran_total)){
			$r = array('success' => false, "info" => "Pembayaran Total empty!");
			die(json_encode($r));
		}
		
		if(empty($pembayaran_total)){
			$pembayaran_total = 0;
		}
			
		$r = '';
		if($this->input->post('form_type_pembayaranARDetail', true) == 'add')
		{
			
			$pembayaran_no = $this->m->generate_pembayaran_number();

			$var = array(
				'fields'	=>	array(
				    'invoice_id'  		=> 	$invoice_id,
				    'pembayaran_no'  		=> 	$pembayaran_no,
				    'pembayaran_date' 		=> 	$pembayaran_date,
				    //'pembayaran_status' 		=> 	'jurnal',
				    'pembayaran_notes' 		=> 	$pembayaran_notes,
				    'no_bukti' 		=> 	$no_bukti,
				    'pembayaran_total' 	=> 	$pembayaran_total,
				    'autoposting_id' 	=> 	$autoposting_id,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				),
				'table'		=>  $this->table_pembayaran_ar
			);	
			
			if($account_receivable_non_accounting == 1){
				$var['fields']['pembayaran_status'] = 'posting';
			}
			
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
				$q = $this->m->add($var);
				$insert_id = $this->m->get_insert_id();
			$this->lib_trans->commit();			
			if($q)
			{  
				$r = array('success' => true, 'id' => $insert_id, 'pembayaran_no'	=> $pembayaran_no); 
				
				$invoice_total_tagihan = $this->update_total_pembayaran($invoice_id);
				
				if($account_receivable_non_accounting == 1){
					$this->m->update_status_invoice($pembayaran_no);
				}else{
					$this->save_to_jurnal($insert_id);
				}
				 
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_pembayaranARDetail', true) == 'edit'){
			
			$var = array('fields'	=>	array(
					'invoice_id'  		=> 	$invoice_id,
					//'pembayaran_no'  		=> 	$pembayaran_no,
				    'pembayaran_date' 		=> 	$pembayaran_date,
				    //'pembayaran_status' 		=> 	$pembayaran_status,
				    'pembayaran_notes' 		=> 	$pembayaran_notes,
				    'no_bukti' 		=> 	$no_bukti,
				    'autoposting_id' 		=> 	$autoposting_id,
				    'pembayaran_total' 	=> 	$pembayaran_total,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				),
				'table'			=>  $this->table_pembayaran_ar,
				'primary_key'	=>  'id'
			);
			
			if($account_receivable_non_accounting == 1){
				$var['fields']['pembayaran_status'] = 'posting';
			}
			
			$id = $this->input->post('id', true);
			
			//CEK OLD DATA
			$this->db->from($this->table_pembayaran_ar);
			$this->db->where("id = '".$id."'");
			$get_dt = $this->db->get();	
			if($get_dt->num_rows() > 0){
				$old_data = $get_dt->row_array();
			}	
			
			if($old_data['pembayaran_status'] == 'posting' AND $account_receivable_non_accounting == 0){
				$r = array('success' => false, 'info' => 'Update Gagal, Pembayaran sudah di Posting!'); 
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
				$invoice_total_tagihan = $this->update_total_pembayaran($invoice_id);
				
				if($account_receivable_non_accounting == 1){
					$this->m->update_status_invoice($pembayaran_no);
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
		
		$this->table_pembayaran_ar = $this->prefix.'pembayaran_ar';	
		$this->table_invoice = $this->prefix.'invoice';	
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
			
			//get data account_receivable
			$this->db->select("a.*, b.rek_id_debet, b.rek_id_kredit, c.invoice_no, c.invoice_name");
			$this->db->from($this->table_pembayaran_ar." as a");
			$this->db->join($this->table_autoposting." as b","b.id = a.autoposting_id","LEFT");
			$this->db->join($this->table_invoice." as c","c.id = a.invoice_id","LEFT");
			$this->db->where("a.id", $id);
			$get_pembayaran = $this->db->get();
			
			if($get_pembayaran->num_rows() > 0){
				$dt_pembayaran = $get_pembayaran->row();
				
				if(empty($dt_pembayaran->autoposting_id)){
					$r = array('success' => false, "info" => "AutoPosting Cannot Empty!");
					die(json_encode($r));
				}
				
				if($dt_pembayaran->pembayaran_status == 'jurnal'){
					
					$this->db->from($this->table_jurnal_header);
					$this->db->where("ref_no", $dt_pembayaran->pembayaran_no);
					$this->db->where("jurnal_from", 'account_receivable');
					$get_jurnal = $this->db->get();
					
					if($get_jurnal->num_rows() > 0){
						$dt_jurnal = $get_jurnal->row();
					}
					
					
					//CREATE JURNAL--------------------
					$opt_val = array(
						'kd_tipe_jurnal_pembayaran_ar'
					);
					
					$get_opt = get_option_value($opt_val);
					
					if(!empty($get_opt['kd_tipe_jurnal_pembayaran_ar'])){
						$kd_tipe_jurnal  = $get_opt['kd_tipe_jurnal_pembayaran_ar'];
					}else{
						$r = array('success' => false,"info" => "Option Variable: kd_tipe_jurnal_pembayaran_ar not found!");
						die(json_encode($r));
					}	
					
					$rek_id_debet  = $dt_pembayaran->rek_id_debet;
					$rek_id_kredit  = $dt_pembayaran->rek_id_kredit;
					
					$nama_tipe_jurnal = '';
					$tgl_registrasi = date("Y-m-d");
					$keterangan = 'Pembayaran AR: '.$dt_pembayaran->pembayaran_no;
					$total = $dt_pembayaran->pembayaran_total;
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
							'jurnal_from' 		=> 'pembayaran_account_receivable',
							'ref_no' 			=> $dt_pembayaran->pembayaran_no,
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
					
							//ID AKUN PIUTANG
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
								'nama_tujuan'	=> $dt_pembayaran->invoice_name,
								'no_transaksi'	=> $dt_pembayaran->invoice_no
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
								'nama_tujuan'	=> $dt_pembayaran->invoice_name,
								'no_transaksi'	=> $dt_pembayaran->invoice_no
							);
							
							$q_det = $this->jurnal_detail->mjDetail($dt_detail, $insert_id);
							
							//update status to jurnal
							$set_status = array("pembayaran_status" => 'jurnal', 'jurnal_id' => $insert_id);
							$this->db->update($this->table_pembayaran_ar, $set_status, "id = '".$id."'");
							
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
							'jurnal_from' 		=> 'pembayaran_account_receivable',
							'ref_no' 			=> $dt_pembayaran->pembayaran_no,
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
					
							//ID AKUN PIUTANG
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
											'nama_tujuan'	=> $dt_pembayaran->invoice_name,
											'no_transaksi'	=> $dt_pembayaran->invoice_no
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
											'nama_tujuan'	=> $dt_pembayaran->invoice_name,
											'no_transaksi'	=> $dt_pembayaran->invoice_no
										);
									}
								}
								
								$q_det = $this->jurnal_detail->mjDetail($dt_detail, $insert_id);
								
							}
							
							//update status to jurnal
							$set_status = array("pembayaran_status" => 'jurnal', 'jurnal_id' => $insert_id);
							$this->db->update($this->table_pembayaran_ar, $set_status, "id = '".$id."'");
							
						}  
						else
						{  
							$r = array('success' => false);
						}
						
						
					}
					
					
					
				}else{
					
					$r = array('success' => false,"info" => "Save to Jurnal Failed!<br/>Status AR: ".ucwords($dt_pembayaran->pembayaran_status));
					
				}
				
			}
			
		}
		
		return json_encode(($r==null or $r=='')? array('success'=>false) : $r);
		
	}
	
	public function generate_pembayaran_number(){
		$this->table = $this->prefix.'pembayaran_ar';		

		$getDate = date("ym");
		
		$this->db->from($this->table);
		$this->db->where("pembayaran_no LIKE 'PB".$getDate."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_pl = $get_last->row();
			$pembayaran_no = str_replace("PB".$getDate,"", $data_pl->pembayaran_no);
			$pembayaran_no = str_replace("PB","", $pembayaran_no);
						
			$pembayaran_no = (int) $pembayaran_no;			
		}else{
			$pembayaran_no = 0;
		}
		
		$pembayaran_no++;
		$length_no = strlen($pembayaran_no);
		switch ($length_no) {
			case 3:
				$pembayaran_no = $pembayaran_no;
				break;
			case 2:
				$pembayaran_no = '0'.$pembayaran_no;
				break;
			case 1:
				$pembayaran_no = '00'.$pembayaran_no;
				break;
			default:
				$pembayaran_no = '00'.$pembayaran_no;
				break;
		}
				
		return 'PB'.$getDate.$pembayaran_no;				
	}
	
	public function delete()
	{
		
		$this->table = $this->prefix.'pembayaran_ar';
		$this->table2 = $this->prefix.'pembayaran_ar';
		
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
			if($data_pl->pembayaran_status == 'posting'){
				$r = array('success' => false, 'info' => 'Status Pembayaran sudah Posting!'); 
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
            $r = array('success' => false, 'info' => 'Delete Pembayaran Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function deleteDetail(){
		$this->table = $this->prefix.'pembayaran_ar';
		$this->table_jurnal_header = $this->prefix.'jurnal_header';
		
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
	
		//account_receivable_non_accounting
		$opt_val = array(
			'account_receivable_non_accounting'
		);
		
		$get_opt = get_option_value($opt_val);
		
		if(!empty($get_opt['account_receivable_non_accounting'])){
			$account_receivable_non_accounting  = $get_opt['account_receivable_non_accounting'];
		}else{
			$account_receivable_non_accounting = 0;
		}	
		
		$data_det = array();
		if($get_data->num_rows() > 0){
			
			$data_det = $get_data->row();
			
			
			if(empty($account_receivable_non_accounting)){
				
				if($data_det->pembayaran_status == 'posting'){
					$r = array('success' => false, 'info' => 'Pembayaran sudah di Posting!<br/>lakukan Unposting pada mutasi jurnal (akunting)'); 
					die(json_encode($r));
				}
				
				//jurnal_from, ref_no, 
				$this->db->select('*');
				$this->db->from($this->table_jurnal_header);
				$this->db->where("ref_no = '".$data_det->pembayaran_no."'");
				$this->db->where("jurnal_from = 'pembayaran_account_receivable'");
				$get_jurnal = $this->db->get();
				
				if($get_jurnal->num_rows() > 0){
					$data_jurnal = $get_jurnal->row();
					if($data_jurnal->status == 'posting' OR $data_jurnal->is_posting){
						$r = array('success' => false, 'info' => 'Pembayaran sudah di Posting!<br/>lakukan Unposting pada mutasi jurnal (akunting)'); 
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
			$invoice_total_tagihan = $this->update_total_pembayaran($data_det->invoice_id);
			
			if(empty($invoice_total_tagihan)){
				$invoice_total_tagihan = 0;
			}
			
			
			if(empty($account_receivable_non_accounting)){
				//Update Acc
				$update_data = array(
					'is_deleted'=> 1
				);
				
				$this->db->update($this->table_jurnal_header, $update_data, "ref_no = '".$data_det->pembayaran_no."' AND jurnal_from = 'pembayaran_account_receivable'");
			}else{
				$this->m->update_status_invoice($data_det->pembayaran_no);
			}
			
            $r = array('success' => true, 'invoice_id' => $data_det->invoice_id, 'invoice_total_tagihan' => $invoice_total_tagihan); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Invoice Detail Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function update_total_pembayaran($invoice_id){
		
		$this->table_invoice = $this->prefix.'invoice';	
		$this->table_pembayaran_ar = $this->prefix.'pembayaran_ar';	
		
		$this->db->select('SUM(pembayaran_total) as pembayaran_total_all');
		$this->db->from($this->table_pembayaran_ar);
		$this->db->where('invoice_id', $invoice_id);
		$this->db->where('pembayaran_status', 'posting');
		$this->db->where('is_deleted', 0);
		$get_tot = $this->db->get();
		
		$pembayaran_total_all = 0;
		if($get_tot->num_rows() > 0){
			$data_kb = $get_tot->row();
			$pembayaran_total_all = $data_kb->pembayaran_total_all;
		}
		
		//Update KB
		$update_KB = array(
			'total_bayar'  => $pembayaran_total_all				
		);
		
		$this->db->update($this->table_invoice, $update_KB, "id = ".$invoice_id);
		
		return $pembayaran_total_all;
	}
	
	public function printPembayaranPiutang(){
		
		$this->prefix_pos = config_item('db_prefix2');
		$this->table_invoice = $this->prefix.'invoice';	
		$this->table_pembayaran_ar = $this->prefix.'pembayaran_ar';	
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
			'po_data'	=> array(),
			'po_detail'	=> array(),
			'report_name'	=> 'PEMBAYARAN INVOICE',
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
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['invoice_data'] = $get_dt->row_array();
				
				//get detail
				$this->db->select("a.*, b.invoice_no, b.invoice_name, b.total_tagihan, b.total_bayar, c.no_registrasi, c.no_jurnal, d.autoposting_name");
				$this->db->from($this->table_pembayaran_ar." as a");
				$this->db->join($this->prefix.'invoice as b','b.id = a.invoice_id','LEFT');
				$this->db->join($this->prefix.'jurnal_header as c','c.id = a.jurnal_id','LEFT');
				$this->db->join($this->prefix.'autoposting as d','d.id = a.autoposting_id','LEFT');
				$this->db->where("a.invoice_id = '".$invoice_id."'");
				$this->db->where("a.pembayaran_status = 'posting'");
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
		
		$useview = 'printPembayaranAR';
		if($do == 'excel'){
			$useview = 'excelPembayaranAR';
		}
		
		$this->load->view('../../account_receivable/views/'.$useview, $data_post);
		
	}
}