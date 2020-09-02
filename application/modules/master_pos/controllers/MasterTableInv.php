<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MasterTableInv extends MY_Controller {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_tableinv', 'm');		
	}

	public function gridData()
	{
		$this->billing = $this->prefix.'billing';
		$this->floorplan = $this->prefix.'floorplan';
		$this->room = $this->prefix.'room';
		$this->table = $this->prefix.'table';
		$this->table_inventory = $this->prefix.'table_inventory';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		$this->m->cek();
		
		//MEMCACHED SESSION
		$use_memcached = $this->input->post('use_memcached');
		if($use_memcached == 1){
			//reload memcached
		}else{
			//empty memcached
			
		}
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'b.is_active',
			'floorplan_name' => 'c.floorplan_name',
			'room_name' => 'c2.room_name',
			'table_tipe_text' => 'b.table_tipe'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.id, a.id as invid, a.table_id, a.billing_no, a.tanggal, a.status, a.total_billing, b.*, 
								c.floorplan_name, c.list_no, c2.room_name, c2.room_no, 
								d.id as billing_id, d.billing_status, d.total_guest, d.qc_notes, d.table_id as billing_table, d.created as billing_created",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table_inventory.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->table.' as b','b.id = a.table_id','LEFT'),
										array($this->floorplan.' as c','c.id = b.floorplan_id','LEFT'),
										array($this->room.' as c2','c2.id = b.room_id','LEFT'),
										array($this->billing.' as d','d.billing_no = a.billing_no','LEFT')
									)
								),
			'where'			=> array('b.is_deleted' => 0),
			'order'			=> array('c.list_no' => 'ASC', 'b.id' => 'ASC', 'b.table_no' => 'ASC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$show_all_text = $this->input->post('show_all_text');
		$show_choose_text = $this->input->post('show_choose_text');
		$tanggal = $this->input->post('tanggal');
		
		//update-2001.002
		$purpose = $this->input->post('purpose');
		$floorplan_id = $this->input->post('floorplan_id');
		$floorplan_name = $this->input->post('floorplan_name');
		
		$get_opt_var = array('jam_operasional_from','jam_operasional_to','jam_operasional_extra',
		'hold_table_timer','hold_table_ayce_timer','hold_table_warning_timer');
		$get_opt = get_option_value($get_opt_var);
		
		if(empty($tanggal)){
			$tanggal = date("Y-m-d");
			
			$billing_time = date('G');
			$datenowstr = strtotime(date("d-m-Y H:i:s"));
			$datenowstr0 = strtotime(date("d-m-Y 00:00:00"));
			
			$jam_operasional_from = 7;
			$jam_operasional_from_Hi = '07:00';
			if(!empty($get_opt['jam_operasional_from'])){
				$jm_opr_mktime = strtotime(date("d-m-Y")." ".$get_opt['jam_operasional_from']);
				$jam_operasional_from = date('G',$jm_opr_mktime);
				$jam_operasional_from_Hi = date('H:i',$jm_opr_mktime);
			}
			
			$jam_operasional_to = 23;
			$jam_operasional_to_Hi = '23:00';
			if(!empty($get_opt['jam_operasional_to'])){
				if($get_opt['jam_operasional_to'] == '24:00'){
					$get_opt['jam_operasional_to'] = '23:59:59';
				}
				$jm_opr_mktime = strtotime(date("d-m-Y")." ".$get_opt['jam_operasional_to']);
				$jam_operasional_to = date('G',$jm_opr_mktime);
				$jam_operasional_to_Hi = date('H:i',$jm_opr_mktime);
			}
			
			$jam_operasional_extra = 0;
			if(!empty($get_opt['jam_operasional_extra'])){
				$jam_operasional_extra = $get_opt['jam_operasional_extra'];
			}
			
			if($billing_time < $jam_operasional_from){
				//extra / early??
	
				//check extra
				$datenowstrmin1 = $datenowstr0-ONE_DAY_UNIX;
				$datenowstr_oprfrom = strtotime(date("d-m-Y", $datenowstrmin1)." ".$jam_operasional_from_Hi.":00");
				$datenowstr_oprto_org = strtotime(date("d-m-Y", $datenowstrmin1)." ".$jam_operasional_to_Hi.":00");
				$datenowstr_oprto = strtotime(date("d-m-Y", $datenowstrmin1)." ".$jam_operasional_to_Hi.":00");
				//add extra
				if(!empty($jam_operasional_extra)){
					$datenowstr_oprto += ($jam_operasional_extra*3600);
				}
				
				if($datenowstr < $datenowstr_oprto){
					$tanggal = date('Y-m-d', $datenowstr_oprfrom);
				}else{
					$tanggal = date('Y-m-d', $datenowstr_oprfrom+ONE_DAY_UNIX);
				}
				
			}else{
	
				$datenowstr_oprfrom = strtotime(date("d-m-Y", $datenowstr0)." ".$jam_operasional_from_Hi.":00");
				$datenowstr_oprto_org = strtotime(date("d-m-Y", $datenowstr0)." ".$jam_operasional_to_Hi.":00");
				$datenowstr_oprto = strtotime(date("d-m-Y", $datenowstr0)." ".$jam_operasional_to_Hi.":00");
				//add extra
				if(!empty($jam_operasional_extra)){
					$datenowstr_oprto += ($jam_operasional_extra*3600);
				}
				
				if($datenowstr < $datenowstr_oprto){
					$tanggal = date('Y-m-d', $datenowstr_oprfrom);
				}
				
			}
		}
		
		$tanggalexp = explode("-", $tanggal);
		$tanggalmk = strtotime($tanggalexp[2].'-'.$tanggalexp[1].'-'.$tanggalexp[0]);
		
		$curr_billing = $this->input->post('curr_billing');
		
		$show_available = $this->input->post('show_available');
		if(empty($show_available)){
			$show_available = false;
		}
		
		$show_selected = $this->input->post('show_selected');
		if(empty($show_selected)){
			$show_selected = false;
		}
		
		
		if(!empty($is_dropdown)){
			$params['order'] = array('b.table_no' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.table_name LIKE '%".$searching."%' OR b.table_no LIKE '%".$searching."%')";
		}
		if(!empty($tanggal)){
			$params['where'][] = "a.tanggal = '".$tanggal."'";
		}
		
		//update-2001.002
		/*
		if($show_available == true){
			
			if(!empty($curr_billing)){
				//$params['where'][] = "(a.status = 'available' OR (a.status != 'available' AND d.id = '".$curr_billing."'))";
			}else{
				//$params['where'][] = "a.status = 'available'";
			}
			
		}*/
		
		if($show_selected == true){
			
			if(!empty($curr_billing)){
				$params['where'][] = "((a.status = 'booked' OR a.status = 'reserved')  AND d.id = '".$curr_billing."')";
			}else{
				$params['where'][] = "a.status = 'available' d.id = -1";
			}
			
		}
		
		if(!empty($floorplan_id)){
			$params['where'][] = "c.id = '".$floorplan_id."'";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		
		//update-2001.002
		//check hold billing
		$data_billing = array();
		if($show_available == true OR ($purpose == 'tableList' OR $purpose == 'loadDataViewTable')){
			//$tanggalmk = strtotime($tanggal);
			$billno = date("ymd", $tanggalmk);
			$this->db->select('*');
			$this->db->from($this->billing);
			$this->db->where("billing_no LIKE '".$billno."%' AND billing_status = 'hold' AND is_deleted = 0 AND table_id > 0");
			$get_bill = $this->db->get();
			if($get_bill->num_rows() > 0){
				foreach($get_bill->result() as $dt){
					if(empty($data_billing[$dt->table_id])){
						$data_billing[$dt->table_id] = array();
					}
					
					$data_billing[$dt->table_id][] = array(
						'billing_id'	=> $dt->id,
						'billing_no'	=> $dt->billing_no,
						'table_no'		=> $dt->table_no,
						'total_guest'	=> $dt->total_guest
					);
				}
			}
		}
  		
  		$newData = array();		
		
		if(!empty($show_all_text)){
			$dt = array('id' => '-1', 'table_name' => 'Choose All Table');
			array_push($newData, $dt);
		}else{
			if(!empty($show_choose_text)){
				$dt = array('id' => '', 'table_name' => 'Choose Table');
				array_push($newData, $dt);
			}
		}
		
		$timernow = strtotime(date("d-m-Y H:i:s"));
		$no_data = 0;
		$update_table_booked_paid = array();
		$update_table_hold = array();
		if(!empty($get_data['data'])){
		
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				if(!empty($s['kapasitas'])){
					$s['kapasitas_text'] = 'Kapasitas: '.$s['kapasitas'].' org';
				}else{
					$s['kapasitas_text'] = '';
				}
				
				$text_tipe = 'Dine In';
				if($s['table_tipe'] == 'takeaway'){
					$text_tipe = 'Take Away';
				}
				if($s['table_tipe'] == 'delivery'){
					$text_tipe = 'Delivery';
				}
				$s['table_tipe_text'] = '<span style="color:green;">'.$text_tipe.'</span>';
				
				//update-2001.002
				$add_data = true;
				if($show_available == true OR ($purpose == 'tableList' OR $purpose == 'loadDataViewTable')){
					
					if(!empty($data_billing[$s['table_id']])){
						$get_billno = '';
						if(!empty($data_billing[$s['table_id']][0]['billing_no'])){
							$get_billno = $data_billing[$s['table_id']][0]['billing_no'];
						}
						
						$total_guest = 0;
						foreach($data_billing[$s['table_id']] as $xdt){
							$total_guest += $xdt['total_guest'];
						}
						$s['total_guest'] = $total_guest;
						
						$update_table_hold[] = array(
							'id'			=> $s['invid'],
							'billing_no'	=> $get_billno,
							'total_billing'	=> count($data_billing[$s['table_id']]),
							'status'		=> 'booked'
						);
						
					}else{
					
						if($s['status'] != 'available'){
							$add_data = false;
							if(!empty($curr_billing)){
								if($curr_billing == $s['billing_id']){
									$add_data = true;
								}
							}
							
							//if booked and paid -> table should available
							if($s['status'] == 'booked' AND !empty($s['billing_id']) AND $s['billing_status'] != 'hold'){
								$add_data = true;
								$update_table_booked_paid[] = array(
									'id'		=> $s['invid'],
									'status'	=> 'available',
									'billing_no'=> ''
								);
								$s['status'] = 'available';
								$s['billing_id'] = '';
								$s['billing_no'] = '';
								$s['billing_status'] = '';
							}
							
							if($s['status'] == 'booked' AND $s['billing_table'] != $s['table_id']){
								$add_data = true;
								$update_table_booked_paid[] = array(
									'id'		=> $s['invid'],
									'status'	=> 'available',
									'billing_no'=> ''
								);
								$s['status'] = 'available';
								$s['billing_id'] = '';
								$s['billing_no'] = '';
								$s['billing_status'] = '';
							}
							
							//if booked and paid -> table should available
							if($s['status'] == 'booked' AND empty($s['billing_id']) AND empty($s['billing_status'])){
								$add_data = true;
								$update_table_booked_paid[] = array(
									'id'		=> $s['invid'],
									'status'	=> 'available',
									'billing_no'=> ''
								);
								$s['status'] = 'available';
								$s['billing_id'] = '';
								$s['billing_no'] = '';
								$s['billing_status'] = '';
							}
							
							if($purpose == 'tableList'){
								$add_data = true;
							}
						}
					}
				}
				
				$s['floorplan_button'] = 0;
				if($purpose == 'tableList'){
					
					if($no_data == 0){
						
						$backup_id = $s['id'];
						$s['id'] = 0;
						$s['table_info'] = '<div style="font-size:12px; margin:5px">Lantai yg dipilih:</div>';
						if(empty($floorplan_id)){
							$s['table_info'] .= '<div style="font-size:16px; margin:5px 0px 15px;"><b>Semua Lantai/Floorplan</b></div>';
						}else{
							$s['table_info'] .= '<div style="font-size:20px; margin:5px 0px 15px;"><b>'.$floorplan_name.'</b></div>';
						}
						$s['table_info'] .= '<div style="font-size:10px;">Klik u/ lihat Lantai Lainnya</div>';
						$s['table_color'] = '6904a9';
						$s['floorplan_button'] = 1;
						array_push($newData, $s);
						
						$s['floorplan_button'] = 0;
						$s['id'] = $backup_id;
					}
					
					
					//table_info
					$s['table_info'] = '<div style="font-size:12px;">'.$s['floorplan_name'].'</div>';
					
					
					if(!empty($s['qc_notes'])){
						$s['table_info'] .= '<div style="font-size:24px; margin:10px 0px 10px;"><b>'.$s['table_name'].'</b></div>';
					}else{
						$s['table_info'] .= '<div style="font-size:24px; margin:10px 0px 20px;"><b>'.$s['table_name'].'</b></div>';
					}

					if($s['status'] == 'available'){
						$s['table_color'] = '008abf';
						$s['table_info'] .= '<div style="font-size:12px; margin:0px;">Available/Tersedia</div>';
					}
					if($s['status'] == 'booked'){
						$s['table_color'] = '0bab00';
						if($s['total_billing'] == 1){
							$s['table_info'] .= '<div style="font-size:12px; margin:0px; line-height: 16px;"><b>No.'.$s['billing_no'].'</b></div>';
							if(!empty($s['qc_notes'])){
								$s['table_info'] .= '<div style="font-size:12px; margin:0px; line-height: 16px;"><b>'.$s['qc_notes'].'</b></div>';
							}
						}else{
							$s['table_info'] .= '<div style="font-size:12px; margin:0px; line-height: 16px;"><b>Booked: '.$s['total_billing'].' Bills</b></div>';
						}
						
					}
					if($s['status'] == 'not available'){
						$s['table_color'] = 'b70133';
						$s['table_info'] .= '<div style="font-size:12px; margin:0px;">Tidak Tersedia</div>';
					}
					if($s['status'] == 'reserved'){
						$s['table_color'] = 'e2a700';
						$s['table_info'] .= '<div style="font-size:12px; margin:0px;">Sudah di Pesan/Reserved</div>';
					}
					
					if($s['status'] == 'booked'){
						$s['table_info'] .= '<div style="font-size:10px; margin:-1px 0px 0px; line-height: 12px;">Tamu: '.$s['total_guest'].' Orang</div>';
						
						if(!empty($get_opt['hold_table_timer'])){
							$billing_created_exp = explode(" ", $s['billing_created']);
							$billing_created_exp2 = explode("-", $billing_created_exp[0]);
							$billing_created_mk = strtotime($billing_created_exp2[2]."-".$billing_created_exp2[1]."-".$billing_created_exp2[0]." ".$billing_created_exp[1]);
							
							$waktu_kunjungan_mk_div = ($timernow - $billing_created_mk) % 3600;
							$waktu_kunjungan_jam = floor(($timernow - $billing_created_mk) / 3600);
							$waktu_kunjungan_menit_full = floor(($timernow - $billing_created_mk) / 60);
							$waktu_kunjungan_menit = floor($waktu_kunjungan_mk_div/60);
							
							$waktu_kunjungan = '';
							if(!empty($waktu_kunjungan_jam)){
								$waktu_kunjungan = $waktu_kunjungan_jam.' Jam';
							}
							if(!empty($waktu_kunjungan_menit)){
								if(empty($waktu_kunjungan)){
									$waktu_kunjungan = $waktu_kunjungan_menit.' Menit';
								}else
								{
									$waktu_kunjungan .= ' '.$waktu_kunjungan_menit.' Menit';
								}
							}else{
								$waktu_kunjungan = '0 Menit';
							}
							
							//menit
							if(!empty($get_opt['hold_table_ayce_timer'])){
								if($waktu_kunjungan_menit_full >= $get_opt['hold_table_ayce_timer']){
									$s['table_color'] = 'b70133';
								}else{
									if(!empty($get_opt['hold_table_warning_timer'])){
										if($waktu_kunjungan_menit_full >= $get_opt['hold_table_warning_timer']){
											$s['table_color'] = 'e2a700';
										}
									}
								}
							
							}
							$s['table_info'] .= '<div style="font-size:10px; margin:-1px 0px 0px; line-height: 12px;">Timer: '.$waktu_kunjungan.'</div>';
						}
						
					}else{
						$s['table_info'] .= '<div style="font-size:10px; margin:0px 0px 0px; line-height: 14px;">'.$s['kapasitas_text'].'</div>';
					}
					
					$s['table_status'] = $s['status'];
				}
				
				if($purpose == 'loadDataViewTable'){
					
					//table_info
					$s['table_info'] = '<div style="font-size:14px; margin:0px 0px 5px;"><b>'.$s['table_name'].'</b></div>';

					if($s['status'] == 'available'){
						$s['table_color'] = '008abf';
						$s['table_info'] .= '<div style="font-size:12px; margin:0px;">Available/Tersedia</div>';
					}
					if($s['status'] == 'booked'){
						$s['table_color'] = '0bab00';
						if($s['total_billing'] == 1){
							$s['table_info'] .= '<div style="font-size:12px; margin:0px;"><b>Booked: '.$s['billing_no'].'</b></div>';
						}else{
							$s['table_info'] .= '<div style="font-size:12px; margin:0px;"><b>Booked: '.$s['total_billing'].' Bills</b></div>';
						}
						
					}
					if($s['status'] == 'not available'){
						$s['table_color'] = 'b70133';
						$s['table_info'] .= '<div style="font-size:12px; margin:0px;">Tidak Tersedia</div>';
					}
					if($s['status'] == 'reserved'){
						$s['table_color'] = 'e2a700';
						$s['table_info'] .= '<div style="font-size:12px; margin:0px;">Sudah di Pesan/Reserved</div>';
					}
					
					if($s['status'] == 'booked'){
						$s['table_info'] .= '<div style="font-size:10px; margin:5px 0px 0px;">Tamu: '.$s['total_guest'].' Orang</div>';
					}else{
						$s['table_info'] .= '<div style="font-size:10px; margin:5px 0px 0px;">'.$s['kapasitas_text'].'</div>';
					}
					
					$s['table_status'] = $s['status'];
				}
				
				if($add_data == true){
					array_push($newData, $s);
					$no_data++;
				}
				
			}
		}else{
			if($purpose == 'tableList'){
				$s = array();
				$s['id'] = 0;
				$s['table_info'] = '<div style="font-size:12px; margin:5px">Lantai yg dipilih:</div>';
				if(empty($floorplan_id)){
					$s['table_info'] .= '<div style="font-size:16px; margin:5px 0px 15px;"><b>Semua Lantai/Floorplan</b></div>';
				}else{
					$s['table_info'] .= '<div style="font-size:20px; margin:5px 0px 15px;"><b>'.$floorplan_name.'</b></div>';
				}
				$s['table_info'] .= '<div style="font-size:10px;">Klik u/ lihat Lantai Lainnya</div>';
				$s['table_color'] = '6904a9';
				$s['floorplan_button'] = 1;
				array_push($newData, $s);
				
			}
		}
		
		$get_data['data'] = $newData;
		$get_data['update_table_hold'] = $update_table_hold;
		
		//update-2001.002
		if(!empty($update_table_hold)){
			$this->db->update_batch($this->table_inventory, $update_table_hold, "id");
		}
		if(!empty($update_table_booked_paid)){
			$this->db->update_batch($this->table_inventory, $update_table_booked_paid, "id");
		}
		
      	die(json_encode($get_data));
	}
	
	
}