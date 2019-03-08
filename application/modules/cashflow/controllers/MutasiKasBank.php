<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MutasiKasBank extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix3');
		$this->load->model('model_mutasi_kas_bank', 'm');
		$this->load->model('accounting/model_acc_mutasi_jurnal', 'jurnal');
		$this->load->model('accounting/model_acc_mutasi_jurnal_detail', 'jurnal_detail');
	}
	
	public function gridData(){
		
		$this->table = $this->prefix.'mutasi_kas_bank';
		
		$sortAlias = array(
			'mkb_status_text'	=> 'mkb_status',
			'mkb_total_show'	=> 'mkb_total',
			'mkb_used_text'	=> 'mkb_used'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, b.no_registrasi, b.no_jurnal, c.autoposting_name, d.tujuan_cashflow_name as mkb_tujuan_text',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'jurnal_header as b','b.id = a.jurnal_id','LEFT'),
										array($this->prefix.'autoposting as c','c.id = a.autoposting_id','LEFT'),
										array($this->prefix.'tujuan_cashflow as d','d.id = a.mkb_tujuan','LEFT')
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
				
				$params['where'][] = "(a.mkb_date >= '".$qdate_from."' AND a.mkb_date <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.mkb_no' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.no_ap LIKE '%".$searching."%' OR a.mkb_name LIKE '%".$searching."%' OR a.no_ref LIKE '%".$searching."%')";
		}		
		if(!empty($status)){
			$params['where'][] = "a.mkb_status = '".$status."'";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		
  		$newData = array();	
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				if($s['mkb_status'] == 'pengakuan'){
					$s['mkb_status_text'] = '<span style="color:blue;">Pengakuan</span>';
				}else 
				if($s['mkb_status'] == 'jurnal'){
					$s['mkb_status_text'] = '<span style="color:blue;">Jurnal</span>';
				}else 
				if($s['mkb_status'] == 'posting'){
					$s['mkb_status_text'] = '<span style="color:orange;">Posting</span>';
				}else{
					$s['mkb_status_text'] = '<span style="color:red;">Cancel</span>';
				}
				
				$s['old_mkb_tujuan'] = $s['mkb_tujuan'];
				
				$s['mkb_date'] = date("d-m-Y",strtotime($s['mkb_date']));
				$s['mkb_total_show'] = priceFormat($s['mkb_total']);
				
				
				if(empty($s['no_posting'])){
					$s['no_posting'] = $s['no_jurnal'];
				}
				
				if(empty($s['no_jurnal'])){
					$s['no_jurnal'] = $s['no_registrasi'];
				}
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function save(){
		
		$this->table_mutasi_kas_bank = $this->prefix.'mutasi_kas_bank';	
		$this->table_jurnal_header = $this->prefix.'jurnal_header';			
		$this->table_tipe_jurnal = $this->prefix.'tipe_jurnal';			
		$session_user = $this->session->userdata('user_username');
		
		$r = array('success' => false,"info" => "Simpan Mutasi Kas Bank Gagal!");
		
		$id = $this->input->post('id');
		$mkb_tujuan = $this->input->post('mkb_tujuan');
		$old_mkb_tujuan = $this->input->post('old_mkb_tujuan');
		$autoposting_id = $this->input->post('autoposting_id');
		$mkb_no = $this->input->post('mkb_no');
		$mkb_date = $this->input->post('mkb_date');
		$mkb_name = $this->input->post('mkb_name');
		$no_ref = $this->input->post('no_ref');
		$mkb_notes = $this->input->post('mkb_notes');
		$mkb_total = $this->input->post('mkb_total');
		$mkb_status = $this->input->post('mkb_status');
		
		
		//cashflow_non_accounting
		$opt_val = array(
			'cashflow_non_accounting'
		);
		
		$get_opt = get_option_value($opt_val);
		
		if(!empty($get_opt['cashflow_non_accounting'])){
			$cashflow_non_accounting  = $get_opt['cashflow_non_accounting'];
		}else{
			$cashflow_non_accounting = 0;
		}	
		
		
		if(empty($mkb_name)){
			$r = array('success' => false, "info" => "Nama/Pelaksana tidak boleh kosong!");
			die(json_encode($r));
		}		
		
		if(empty($no_ref)){
			$r = array('success' => false, "info" => "No Ref tidak boleh kosong!");
			die(json_encode($r));
		}		
		
		if(empty($autoposting_id)){
			$r = array('success' => false, "info" => "Pilih AutoPosting!");
			die(json_encode($r));
		}	
		
		
		if(empty($mkb_total)){
			$r = array('success' => false, "info" => "Total Keluar tidak boleh kosong!");
			die(json_encode($r));
		}
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
			
		$r = '';
		if($this->input->post('form_type_mutasiKasBank', true) == 'add')
		{
			
			$mkb_no = $this->m->generate_mkb_number();

			$var = array(
				'fields'	=>	array(
				    'mkb_no'  		=> 	$mkb_no,
					'mkb_date'		=>	$mkb_date,
				    'mkb_tujuan' 		=> 	$mkb_tujuan,
				    'autoposting_id'=> 	$autoposting_id,
				    'mkb_name' 		=> 	$mkb_name,
				    'no_ref' 		=> 	$no_ref,
				    'mkb_notes' 		=> 	$mkb_notes,
				    'mkb_total' 		=> 	$mkb_total,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'	=>	$is_active
				),
				'table'		=>  $this->table_mutasi_kas_bank
			);	
			
			if($cashflow_non_accounting == 1){
				$var['fields']['mkb_status'] = 'posting';
			}
			
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
				$q = $this->m->add($var);
				$insert_id = $this->m->get_insert_id();
			$this->lib_trans->commit();			
			if($q)
			{  
				$r = array('success' => true, 'id' => $insert_id, 'mkb_no' => $mkb_no); 				
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_mutasiKasBank', true) == 'edit'){
			
			if($mkb_status == 'pengakuan'){
				$var = array('fields'	=>	array(
						'autoposting_id'=> 	$autoposting_id,
						'mkb_tujuan' 		=> 	$mkb_tujuan,
						'mkb_date'		=>	$mkb_date,
						'mkb_name' 		=> 	$mkb_name,
						'no_ref' 		=> 	$no_ref,
						'mkb_notes' 		=> 	$mkb_notes,
						'mkb_total' => 	$mkb_total,
						'updated'		=>	date('Y-m-d H:i:s'),
						'updatedby'		=>	$session_user
					),
					'table'			=>  $this->table_mutasi_kas_bank,
					'primary_key'	=>  'id'
				);	
		
				if($cashflow_non_accounting == 1){
					$var['fields']['mkb_status'] = 'posting';
				}
			}else
			if($mkb_status == 'posting'){
				
				if($cashflow_non_accounting == 1){
					$var = array('fields'	=>	array(
							'autoposting_id'=> 	$autoposting_id,
							'mkb_tujuan' 		=> 	$mkb_tujuan,	
							'mkb_date'		=>	$mkb_date,
							'mkb_name' 		=> 	$mkb_name,
							'no_ref' 		=> 	$no_ref,
							'mkb_notes' 		=> 	$mkb_notes,
							'mkb_total' => 	$mkb_total,
							'updated'		=>	date('Y-m-d H:i:s'),
							'updatedby'		=>	$session_user
						),
						'table'			=>  $this->table_mutasi_kas_bank,
						'primary_key'	=>  'id'
					);	
				}
				
			}else{
				$var = array('fields'	=>	array(
						'autoposting_id'=> 	$autoposting_id,
						'mkb_tujuan' 		=> 	$mkb_tujuan,
						'mkb_date'		=>	$mkb_date,
						'mkb_name' 		=> 	$mkb_name,
						'no_ref' 		=> 	$no_ref,
						'mkb_notes' 		=> 	$mkb_notes,
						'mkb_total' => 	$mkb_total,
						'updated'		=>	date('Y-m-d H:i:s'),
						'updatedby'		=>	$session_user
					),
					'table'			=>  $this->table_mutasi_kas_bank,
					'primary_key'	=>  'id'
				);	
		
				if($cashflow_non_accounting == 1){
					$var['fields']['mkb_status'] = 'posting';
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
		
		$this->table_mutasi_kas_bank = $this->prefix.'mutasi_kas_bank';	
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
				$update_ap = $this->db->update($this->table_mutasi_kas_bank, $update_autoposting,"id = ".$id);
			}
			
			//get data mutasi_kas_bank
			$this->db->select("a.*, b.rek_id_debet, b.rek_id_kredit");
			$this->db->from($this->table_mutasi_kas_bank." as a");
			$this->db->join($this->table_autoposting." as b","b.id = a.autoposting_id","LEFT");
			$this->db->where("a.id", $id);
			$get_ap = $this->db->get();
			
			if($get_ap->num_rows() > 0){
				$dt_ap = $get_ap->row();
				
				if(empty($dt_ap->autoposting_id)){
					$r = array('success' => false, "info" => "AutoPosting Cannot Empty!");
					die(json_encode($r));
				}
				
				if($dt_ap->mkb_status == 'pengakuan'){
					
					$this->db->from($this->table_jurnal_header);
					$this->db->where("ref_no", $dt_ap->mkb_no);
					$this->db->where("jurnal_from", 'mutasi_kas_bank');
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
					$keterangan = 'Mutasi Kas Bank: '.$dt_ap->mkb_no;
					$total = $dt_ap->mkb_total;
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
							'jurnal_from' 		=> 'mutasi_kas_bank',
							'ref_no' 			=> $dt_ap->mkb_no,
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
								'nama_tujuan'	=> $dt_ap->mkb_name,
								'no_transaksi'	=> $dt_ap->mkb_no
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
								'nama_tujuan'	=> $dt_ap->mkb_name,
								'no_transaksi'	=> $dt_ap->mkb_no
							);
							
							$q_det = $this->jurnal_detail->mjDetail($dt_detail, $insert_id);
							
							//update status to jurnal
							$set_status = array("mkb_status" => 'jurnal', 'jurnal_id' => $insert_id);
							$this->db->update($this->table_mutasi_kas_bank, $set_status, "id = '".$id."'");
							
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
							'jurnal_from' 		=> 'mutasi_kas_bank',
							'ref_no' 			=> $dt_ap->mkb_no,
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
											'nama_tujuan'	=> $dt_ap->mkb_name,
											'no_transaksi'	=> $dt_ap->mkb_no
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
											'nama_tujuan'	=> $dt_ap->mkb_name,
											'no_transaksi'	=> $dt_ap->mkb_no
										);
									}
								}
								
								$q_det = $this->jurnal_detail->mjDetail($dt_detail, $insert_id);
								
							}
							
							//update status to jurnal
							$set_status = array("mkb_status" => 'jurnal', 'jurnal_id' => $insert_id);
							$this->db->update($this->table_mutasi_kas_bank, $set_status, "id = '".$id."'");
							
						}  
						else
						{  
							$r = array('success' => false);
						}
						
						
					}
					
					
					
				}else{
					
					$r = array('success' => false,"info" => "Save to Jurnal Failed!<br/>Status MKB: ".ucwords($dt_ap->mkb_status));
					
				}
				
			}
			
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
		
	}
	
	public function delete()
	{
		
		$this->table = $this->prefix.'mutasi_kas_bank';
		
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
            $r = array('success' => false, 'info' => 'Delete MKB Failed!'); 
        }
		die(json_encode($r));
	}
	
}