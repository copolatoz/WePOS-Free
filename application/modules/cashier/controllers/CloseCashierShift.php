<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class CloseCashierShift extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_closecashiershift', 'm');
	}
	
	public function saveCloseShift(){
		
		$this->table = $this->prefix.'open_close_shift';
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$get_opt = get_option_value(array('role_id_kasir'));
		$role_id_kasir = 0;
		
		if(!empty($get_opt['role_id_kasir'])){
			$role_id_kasir = $get_opt['role_id_kasir'];
		}else{
			$r = array('success' => false, 'info' => 'Harus Role: Cashier agar bisa menggunakan module ini!');
			echo json_encode($r);
			die();
		}
		
		$get_id = $this->input->post('id', true);
		$spv_user = $this->input->post('spv_user', true);
		$tanggal_shift = $this->input->post('tanggal_shift', true);
		$jam_shift = $this->input->post('jam_shift', true);
		$user_shift = $this->input->post('user_shift', true);
		$nama_shift = $this->input->post('nama_shift', true);
		$tanggal_shift_close = $this->input->post('tanggal_shift_close', true);
		
		if(empty($tanggal_shift)){
			$r = array('success' => false, 'info' => 'Set Tanggal Shift!');
			echo json_encode($r);
			die();
		}
		
		$tanggal_shift = date("Y-m-d",strtotime($tanggal_shift));
		$tanggal_shift_close = date("Y-m-d",strtotime($tanggal_shift_close));
		
		$tanggal_jam_shift = $tanggal_shift_close.' '.$jam_shift.':00';
		
		$uang_kertas_100000 = $this->input->post('uang_kertas_100000', true);
		$uang_kertas_50000 = $this->input->post('uang_kertas_50000', true);
		$uang_kertas_20000 = $this->input->post('uang_kertas_20000', true);
		$uang_kertas_10000 = $this->input->post('uang_kertas_10000', true);
		$uang_kertas_5000 = $this->input->post('uang_kertas_5000', true);
		$uang_kertas_2000 = $this->input->post('uang_kertas_2000', true);
		$uang_kertas_1000 = $this->input->post('uang_kertas_1000', true);
		$jumlah_uang_kertas = 0;
		$jumlah_uang_kertas += ($uang_kertas_100000 * 100000);
		$jumlah_uang_kertas += ($uang_kertas_50000 * 50000);
		$jumlah_uang_kertas += ($uang_kertas_20000 * 20000);
		$jumlah_uang_kertas += ($uang_kertas_10000 * 10000);
		$jumlah_uang_kertas += ($uang_kertas_5000 * 5000);
		$jumlah_uang_kertas += ($uang_kertas_2000 * 2000);
		$jumlah_uang_kertas += ($uang_kertas_1000 * 1000);
		
		$uang_koin_1000 = $this->input->post('uang_koin_1000', true);
		$uang_koin_500 = $this->input->post('uang_koin_500', true);
		$uang_koin_200 = $this->input->post('uang_koin_200', true);
		$uang_koin_100 = $this->input->post('uang_koin_100', true);
		$jumlah_uang_koin = 0;
		$jumlah_uang_koin += ($uang_koin_1000 * 1000);
		$jumlah_uang_koin += ($uang_koin_500 * 500);
		$jumlah_uang_koin += ($uang_koin_200 * 200);
		$jumlah_uang_koin += ($uang_koin_100 * 100);
		
		$date_now = date('Y-m-d H:i:s');
		$tipe_shift = 'close';
			
		//LOAD USER OPEN SHIFT
		$get_data = $this->loadCloseShift(true); //array
		if(!empty($get_data['id'])){
			
			//autocreate if not available
			$this->cekOpenShift();
			
			$update_closeShift = array(
				'kasir_user'	=> $session_user,
				'spv_user'		=> $spv_user,
				'tipe_shift'	=> $tipe_shift,
				'tanggal_shift'	=> $tanggal_shift,
				'jam_shift'		=> $jam_shift,
				'user_shift'	=> $user_shift,
				'tanggal_jam_shift'	=> $tanggal_jam_shift,
				'uang_kertas_100000'=> $uang_kertas_100000,
				'uang_kertas_50000'	=> $uang_kertas_50000,
				'uang_kertas_20000'	=> $uang_kertas_20000,
				'uang_kertas_10000'	=> $uang_kertas_10000,
				'uang_kertas_5000'	=> $uang_kertas_5000,
				'uang_kertas_2000'	=> $uang_kertas_2000,
				'uang_kertas_1000'	=> $uang_kertas_1000,
				'jumlah_uang_kertas'	=> $jumlah_uang_kertas,
				'uang_koin_1000'	=> $uang_koin_1000,
				'uang_koin_500'	=> $uang_koin_500,
				'uang_koin_200'	=> $uang_koin_200,
				'uang_koin_100'	=> $uang_koin_100,
				'jumlah_uang_koin'	=> $jumlah_uang_koin,
				'updated'		=>	$date_now,
				'updatedby'		=>	$session_user
			);
			
			$save_closeShift = $this->db->update($this->table, $update_closeShift, 'id = '.$get_data['id']);
			
			$this->cekShiftLog();
			$this->lockBillingShift();
			
			$r = array('success' => true, 'closeShiftData' => $get_data, 'nama_shift' => $nama_shift);
			
		}else
		{	
			//autocreate if not available
			$this->cekOpenShift();
			
			$insert_closeShift = array(
				'kasir_user'	=> $session_user,
				'spv_user'		=> $spv_user,
				'tipe_shift'	=> $tipe_shift,
				'tanggal_shift'	=> $tanggal_shift,
				'jam_shift'		=> $jam_shift,
				'user_shift'	=> $user_shift,
				'tanggal_jam_shift'	=> $tanggal_jam_shift,
				'uang_kertas_100000'=> $uang_kertas_100000,
				'uang_kertas_50000'	=> $uang_kertas_50000,
				'uang_kertas_20000'	=> $uang_kertas_20000,
				'uang_kertas_10000'	=> $uang_kertas_10000,
				'uang_kertas_5000'	=> $uang_kertas_5000,
				'uang_kertas_2000'	=> $uang_kertas_2000,
				'uang_kertas_1000'	=> $uang_kertas_1000,
				'jumlah_uang_kertas'	=> $jumlah_uang_kertas,
				'uang_koin_1000'	=> $uang_koin_1000,
				'uang_koin_500'	=> $uang_koin_500,
				'uang_koin_200'	=> $uang_koin_200,
				'uang_koin_100'	=> $uang_koin_100,
				'jumlah_uang_koin'	=> $jumlah_uang_koin,
				'created'		=>	$date_now,
				'createdby'		=>	$session_user,
				'updated'		=>	$date_now,
				'updatedby'		=>	$session_user
			);
			
			$save_closeShift = $this->db->insert($this->table, $insert_closeShift);
			$get_data['id'] = $this->db->insert_id();
			
			if($save_closeShift){
				
				$this->cekShiftLog();
				$this->lockBillingShift();
			
				$r = array('success' => true, 'closeShiftData' => $get_data, 'id = '.$get_data['id'], 'nama_shift' => $nama_shift);
			}else{
				$r = array('success' => false, 'Save Close Cashier '.$nama_shift.' Failed!', 'nama_shift' => $nama_shift);
			}
			
		}
		
		die(json_encode($r));
	}
		
	public function lockBillingShift(){
		$this->table = $this->prefix.'open_close_shift';
		$this->prefix2 = config_item('db_prefix2'); //pos_
		
		$tanggal_shift = $this->input->post('tanggal_shift', true);
		$jam_shift = $this->input->post('jam_shift', true);
		$user_shift = $this->input->post('user_shift', true);
		
		$tanggal_shift_post = $tanggal_shift;
		$tanggal_shift = date("Y-m-d",strtotime($tanggal_shift));
		
		$get_opt_var = array('jam_operasional_from','jam_operasional_to','jam_operasional_extra','jumlah_shift');
		$get_opt = get_option_value($get_opt_var);
		
		$jumlah_shift = 1;
		if(!empty($get_opt['jumlah_shift'])){
			$jumlah_shift = $get_opt['jumlah_shift'];
		}
		
		//CEK SHIFT LOG
		$this->db->select('a.*');
		$this->db->from($this->prefix.'shift_log as a');
		$this->db->where("a.tanggal_shift", $tanggal_shift);
		//$this->db->where("a.tipe_shift", 'open');
		$this->db->where("a.user_shift = ".$user_shift);
		$this->db->order_by("a.id", 'DESC');
		
		$getShiftLog = $this->db->get();
		if($getShiftLog->num_rows() > 0){
			
			$dataShiftLog = $getShiftLog->row_array();
			
			$tanggal_jam_start = $dataShiftLog['tanggal_jam_start'];
			$tanggal_jam_end = $dataShiftLog['tanggal_jam_end'];
			
			$tanggal_jam_start_exp = explode(" ",$tanggal_jam_start);
			$tanggal_jam_start_exp2 = explode("-",$tanggal_jam_start_exp[0]);
			$mk_tanggal_jam_start = strtotime($tanggal_jam_start_exp2[2]."-".$tanggal_jam_start_exp2[1]."-".$tanggal_jam_start_exp2[0]." ".$tanggal_jam_start_exp[1]);
			
			$tanggal_jam_end_exp = explode(" ",$tanggal_jam_end);
			$tanggal_jam_end_exp2 = explode("-",$tanggal_jam_end_exp[0]);
			$mk_tanggal_jam_end = strtotime($tanggal_jam_end_exp2[2]."-".$tanggal_jam_end_exp2[1]."-".$tanggal_jam_end_exp2[0]." ".$tanggal_jam_end_exp[1]);
			
			//jam operasional s/d end shift 1 
			if($user_shift == 1){
				
				$jam_operasional_from = 7;
				$jam_operasional_from_Hi = '07:00';
				$mk_jam_operasional_from_Hi = 0;
				if(!empty($get_opt['jam_operasional_from'])){
					$jm_opr_mktime = strtotime($tanggal_shift_post." ".$get_opt['jam_operasional_from']);
					$jam_operasional_from = date('G',$jm_opr_mktime);
					$jam_operasional_from_Hi = date('H:i',$jm_opr_mktime);
					$mk_jam_operasional_from_Hi = $jm_opr_mktime;
				}
				
				if($mk_jam_operasional_from_Hi <= $mk_tanggal_jam_start){
					$mk_tanggal_jam_start = $mk_jam_operasional_from_Hi;
				}
			}
			
			if($user_shift == $jumlah_shift){
				
				$jam_operasional_to = 23;
				$jam_operasional_to_Hi = '23:00';
				$jam_operasional_max_Hi = '23:00';
				$mk_jam_operasional_to_Hi = 0;
				$mk_jam_operasional_max_Hi = 0;
				if(!empty($get_opt['jam_operasional_to'])){
					if($get_opt['jam_operasional_to'] == '24:00'){
						$get_opt['jam_operasional_to'] = '23:59:59';
					}
					$jm_opr_mktime = strtotime($tanggal_shift_post." ".$get_opt['jam_operasional_to']);
					$jam_operasional_to = date('G',$jm_opr_mktime);
					$jam_operasional_to_Hi = date('H:i',$jm_opr_mktime);
					$mk_jam_operasional_to_Hi = $jm_opr_mktime;
					
					if(!empty($get_opt['jam_operasional_extra'])){
						$jam_operasional_extra = $get_opt['jam_operasional_extra'];
					}
					$jm_opr_mktime += ($jam_operasional_extra*3600);
					$jam_operasional_max_Hi = date('H:i',$jm_opr_mktime);
					$mk_jam_operasional_max_Hi = $jm_opr_mktime;
				}
				
				
				if($mk_jam_operasional_max_Hi >= $mk_tanggal_jam_end){
					$mk_tanggal_jam_end = $mk_jam_operasional_max_Hi;
				}
				
			}
			
			$date_update_start = date("Y-m-d H:i:s", $mk_tanggal_jam_start);
			$date_update_end = date("Y-m-d H:i:s", $mk_tanggal_jam_end);
			
			$billing_no_shift = date("ymd",strtotime($tanggal_shift_post));
			
			//lock billing shift
			$update_shift_billing = array(
				'shift'	 => $user_shift
			);
			$update_billing_shift = $this->db->update($this->prefix.'billing', $update_shift_billing, "billing_no LIKE '".$billing_no_shift."%' AND billing_status != 'paid' AND (created >= '".$date_update_start."' AND created <= '".$date_update_end."')");
			$update_billing_shift_paid = $this->db->update($this->prefix.'billing', $update_shift_billing, "billing_no LIKE '".$billing_no_shift."%' AND billing_status = 'paid' AND (payment_date >= '".$date_update_start."' AND payment_date <= '".$date_update_end."')");
			
			//update-2011.001
			if($jumlah_shift > 1){
				if($user_shift+1 <= $jumlah_shift){
					
					//update billing shift > jam end
					$update_shiftplus_billing = array(
						'shift'	 => ($user_shift+1)
					);
					$update_billing_shift_plus = $this->db->update($this->prefix.'billing', $update_shiftplus_billing, "billing_no LIKE '".$billing_no_shift."%' AND billing_status = 'paid' AND shift = ".$user_shift." AND payment_date >= '".$date_update_end."'");
			
				}
			}
			
		}
		
		
		
	}
	
	public function cekShiftLog(){
		$this->table = $this->prefix.'open_close_shift';
		$this->prefix2 = config_item('db_prefix2'); //pos_
		
		$session_user = $this->session->userdata('user_username');
		$date_now = date('Y-m-d H:i:s');
		
		$tanggal_shift = $this->input->post('tanggal_shift', true);
		$jam_shift = $this->input->post('jam_shift', true);
		$user_shift = $this->input->post('user_shift', true);
		$nama_shift = $this->input->post('nama_shift', true);
		$tipe_shift = $this->input->post('tipe_shift', true);
		
		$get_opt_var = array('jam_operasional_from','jam_operasional_to','jam_operasional_extra','jumlah_shift');
		$get_opt = get_option_value($get_opt_var);
		
		$jumlah_shift = 1;
		if(!empty($get_opt['jumlah_shift'])){
			$jumlah_shift = $get_opt['jumlah_shift'];
		}
		
		$tanggal_shift_post = $tanggal_shift;
		
		$jam_operasional_from = 7;
		$jam_operasional_from_Hi = '07:00';
		$mk_jam_operasional_from_Hi = 0;
		if(!empty($get_opt['jam_operasional_from'])){
			$jm_opr_mktime = strtotime($tanggal_shift_post." ".$get_opt['jam_operasional_from']);
			$jam_operasional_from = date('G',$jm_opr_mktime);
			$jam_operasional_from_Hi = date('H:i',$jm_opr_mktime);
			$mk_jam_operasional_from_Hi = $jm_opr_mktime;
		}
		
		$jam_operasional_to = 23;
		$jam_operasional_to_Hi = '23:00';
		$jam_operasional_max_Hi = '23:00';
		$mk_jam_operasional_to_Hi = 0;
		$mk_jam_operasional_max_Hi = 0;
		if(!empty($get_opt['jam_operasional_to'])){
			if($get_opt['jam_operasional_to'] == '24:00'){
				$get_opt['jam_operasional_to'] = '23:59:59';
			}
			$jm_opr_mktime = strtotime($tanggal_shift_post." ".$get_opt['jam_operasional_to']);
			$jam_operasional_to = date('G',$jm_opr_mktime);
			$jam_operasional_to_Hi = date('H:i',$jm_opr_mktime);
			$mk_jam_operasional_to_Hi = $jm_opr_mktime;
			
			if(!empty($get_opt['jam_operasional_extra'])){
				$jam_operasional_extra = $get_opt['jam_operasional_extra'];
			}
			$jm_opr_mktime += ($jam_operasional_extra*3600);
			$jam_operasional_max_Hi = date('H:i',$jm_opr_mktime);
			$mk_jam_operasional_max_Hi = $jm_opr_mktime;
		}
		
		$mk_shift_start_post_awal = 0;
		$mk_shift_start_post = $mk_jam_operasional_from_Hi;
		$mk_shift_post_end = strtotime($tanggal_shift_post." ".$jam_shift);
		
		$tanggal_shift = date("Y-m-d",strtotime($tanggal_shift));
		
		$shift_range = array();
		//CEK RANGE SHIFT
		$this->db->select('a.*');
		$this->db->from($this->table.' as a');
		$this->db->where("a.tanggal_shift", $tanggal_shift);
		//$this->db->where("a.tipe_shift", $tipe_shift);
		//$this->db->where("a.user_shift = ".$user_shift);
		$this->db->order_by("a.id", 'ASC');
		$get_openCloseShift = $this->db->get();
		if($get_openCloseShift->num_rows() > 0){
			foreach($get_openCloseShift->result_array() as $dt){
				
				$mk_shift_cek = strtotime($tanggal_shift_post." ".$dt['jam_shift']);
				
				if(empty($shift_range[$dt['user_shift']])){
					$shift_range[$dt['user_shift']] = array(
						'jam_start' => '',
						'mk_jam_start' => 0,
						'jam_end' => '',
						'mk_jam_end' => 0,
					);
				}
				
				if($dt['tipe_shift'] == 'open'){
					
					if(empty($shift_range[$dt['user_shift']]['mk_jam_start'])){
						$shift_range[$dt['user_shift']]['mk_jam_start'] = $mk_shift_cek;
					}
						
					if($mk_shift_cek <= $shift_range[$dt['user_shift']]['mk_jam_start']){
						$shift_range[$dt['user_shift']]['mk_jam_start'] = $mk_shift_cek;
					}
					
					//jika shift == 1
					if($dt['user_shift'] == 1){
						if($shift_range[$dt['user_shift']]['mk_jam_start'] >= $mk_jam_operasional_from_Hi){
							$shift_range[$dt['user_shift']]['mk_jam_start'] = $mk_jam_operasional_from_Hi;
						}
					}
					
					$shift_range[$dt['user_shift']]['jam_start'] = date("Y-m-d H:i:s",$shift_range[$dt['user_shift']]['mk_jam_start']);
					
				}
				
				if($dt['tipe_shift'] == 'close'){
					
					if(empty($shift_range[$dt['user_shift']]['mk_jam_end'])){
						$shift_range[$dt['user_shift']]['mk_jam_end'] = $mk_shift_cek;
					}
						
					if(date('G', $mk_shift_cek) < $jam_operasional_from AND $user_shift == $jumlah_shift){
						//lewat hari
						$mk_shift_cek += ONE_DAY_UNIX;
					}
					
					if($mk_shift_cek >= $shift_range[$dt['user_shift']]['mk_jam_end']){
						$shift_range[$dt['user_shift']]['mk_jam_end'] = $mk_shift_cek;
					}
					
					$shift_range[$dt['user_shift']]['jam_end'] = date("Y-m-d H:i:s",$shift_range[$dt['user_shift']]['mk_jam_end']);
					
				}
				
			}
		}
		
		//re-fixing jam 
		$get_mk_shift_min1 = 0;
		$get_mk_shift = $shift_range[$user_shift];
		if($user_shift > 1){
			$get_mk_shift_min1 = $shift_range[$user_shift-1];
			$mk_shift_start_post = $get_mk_shift_min1['mk_jam_end'];
		}else{
			$mk_shift_start_post = $get_mk_shift['mk_jam_start'];
		}
		
		$mk_shift_post_end = $get_mk_shift['mk_jam_end'];
		
		$jam_shift_start = date("H:i", $mk_shift_start_post);
		$tanggal_jam_shift_start = date("Y-m-d H:i:s", $mk_shift_start_post);
		
		$jam_shift_end = date("H:i", $mk_shift_post_end);
		$tanggal_jam_shift_end = date("Y-m-d H:i:s", $mk_shift_post_end);
		
		//CEK SHIFT LOG
		$this->db->select('a.*');
		$this->db->from($this->prefix.'shift_log as a');
		$this->db->where("a.tanggal_shift", $tanggal_shift);
		//$this->db->where("a.tipe_shift", 'open');
		$this->db->where("a.user_shift = ".$user_shift);
		$this->db->order_by("a.id", 'DESC');
		
		$get_openShiftLog = $this->db->get();
		if($get_openShiftLog->num_rows() > 0){
			
			//shift_log
			$update_shift_log = array(
				'tipe_shift'	 => $tipe_shift,
				'jam_shift_start'=> $jam_shift_start,
				'tanggal_jam_start'=> $tanggal_jam_shift_start,
				'jam_shift_end'  => $jam_shift_end,
				'tanggal_jam_end'=> $tanggal_jam_shift_end,
				'updated'		 => $date_now,
				'updatedby'		 => $session_user
			);
			$save_shift_log = $this->db->update($this->prefix.'shift_log', $update_shift_log, "user_shift = ".$user_shift." AND tanggal_shift = '".$tanggal_shift."'");
			
		}else{
			
			//shift_log
			$insert_shift_log = array(
				'tipe_shift'	 => $tipe_shift,
				'tanggal_shift'	 => $tanggal_shift,
				'jam_shift_start'=> $jam_shift_start,
				'tanggal_jam_start'=> $tanggal_jam_shift_start,
				'jam_shift_end'	 => $jam_shift_end,
				'tanggal_jam_end'=> $tanggal_jam_shift_end,
				'user_shift'	=> $user_shift,
				'created'		=>	$date_now,
				'createdby'		=>	$session_user,
				'updated'		=>	$date_now,
				'updatedby'		=>	$session_user
			);
			$save_shift_log = $this->db->insert($this->prefix.'shift_log', $insert_shift_log);
		}
	}
	
	public function cekOpenShift(){
			
		$this->table = $this->prefix.'open_close_shift';
		$this->prefix2 = config_item('db_prefix2'); //pos_
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$date_now = date('Y-m-d H:i:s');
		
		$user_shift = $this->input->post('user_shift', true);
		$jam_shift_end = $this->input->post('jam_shift_end', true);
		$tanggal_shift = $this->input->post('tanggal_shift', true);
		
		$openShiftData = array();
		$tanggal_shift = date("Y-m-d",strtotime($tanggal_shift));
		$tipe_shift = 'open';
		
	
		$shiftDataBefore = array();
		if($user_shift > 1){
			$user_shift_before = $user_shift-1;
			$this->db->select('a.*');
			$this->db->from($this->table.' as a');
			$this->db->where("a.tanggal_shift", $tanggal_shift);
			$this->db->where("a.tipe_shift", 'close');
			$this->db->where("a.user_shift = ".$user_shift_before);
			$get_closeShiftBefore = $this->db->get();
			if($get_closeShiftBefore->num_rows() > 0){
				$shiftDataBefore = $get_closeShiftBefore->row();
			}else{
				
				$this->db->select('a.*');
				$this->db->from($this->prefix.'shift as a');
				$this->db->where("a.id", $user_shift_before);
				$getShift = $this->db->get();
				
				$nama_shift_sebelumnya = '-';
				if($getShift->num_rows() > 0){
					$shiftData = $getShift->row();
					$nama_shift_sebelumnya = $shiftData->nama_shift;
				}
				
				$r = array('success' => false, 'info' => 'Shift: <b>'.$nama_shift_sebelumnya.'</b> harus di Close terlebih dahulu!<br/>Lakukan di Module: Close Cashier (Shift) / Settlement');
				echo json_encode($r);
				die();
			}
		}
		
		
		//CREATE OPEN SHIFT - BALANCING
		$this->db->select('a.*');
		$this->db->from($this->table.' as a');
		$this->db->where("a.tanggal_shift", $tanggal_shift);
		$this->db->where("a.tipe_shift", $tipe_shift);
		$this->db->where("a.user_shift = ".$user_shift);
		$this->db->where("a.kasir_user = '".$session_user."'");
		$this->db->order_by("a.id", 'DESC');
		
		$get_openShift = $this->db->get();
		if($get_openShift->num_rows() == 0){
			
			$this->db->select('a.*');
			$this->db->from($this->prefix.'shift as a');
			$this->db->where("a.id", $user_shift);
			$getShift = $this->db->get();
			
			$jam_shift = '';
			if($getShift->num_rows() > 0){
				$shiftData = $getShift->row();
				$jam_shift = $shiftData->jam_shift_start;
			}
			
			if(!empty($shiftDataBefore)){
				$jam_shift = $shiftDataBefore->jam_shift;
			}
			
			
			$tanggal_jam_shift = $tanggal_shift.' '.$jam_shift.':00';
			
			//create open shift
			$insert_openShift = array(
				'kasir_user'	=> $session_user,
				'tipe_shift'	=> $tipe_shift,
				'tanggal_shift'	=> $tanggal_shift,
				'jam_shift'		=> $jam_shift,
				'tanggal_jam_shift' => $tanggal_jam_shift,
				'user_shift'	=> $user_shift,
				'created'		=>	$date_now,
				'createdby'		=>	$session_user,
				'updated'		=>	$date_now,
				'updatedby'		=>	$session_user
			);
			
			$save_openShift = $this->db->insert($this->table, $insert_openShift);
		}
		
	}
	
	public function loadCloseShift($is_return = false, $id_close = ''){
				
		$this->table = $this->prefix.'open_close_shift';
		$this->prefix2 = config_item('db_prefix2'); //pos_
		$this->table_billing = $this->prefix2.'billing';
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		
		$user_shift = $this->input->post('user_shift', true);
		$jam_shift_end = $this->input->post('jam_shift_end', true);
		$tanggal_shift = $this->input->post('tanggal_shift', true);
		
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$closeShiftData = array();
		$get_opt = get_option_value(array('role_id_kasir'));
		$role_id_kasir = 0;
		
		if(!empty($get_opt['role_id_kasir'])){
			$role_id_kasir = $get_opt['role_id_kasir'];
		}else{
			$r = array('success' => false, 'info' => 'Harus Role: Cashier agar bisa menggunakan module ini!');
			echo json_encode($r);
			die();
		}
		
		$jam_shift = date("H:i");
		$get_date = date("Y-m-d", strtotime($tanggal_shift));
		
		$this->db->select('a.*, b.nama_shift');
		$this->db->from($this->table.' as a');
		$this->db->join($this->prefix.'shift as b',"b.id = a.user_shift");
		
		if(!empty($id_close)){
			$this->db->where("a.id", $id_close);
		}else{
			
			$this->db->where("a.tanggal_shift", $get_date);
			$this->db->where("a.tipe_shift", 'close');
			
			if(!empty($user_shift)){
				$this->db->where("a.user_shift", $user_shift);
			}else{
				$this->db->where("a.user_shift", -1);
			}
			
			if(!empty($session_user)){
				$this->db->where("a.kasir_user", $session_user);
			}else{
				$this->db->where("a.kasir_user", -1);
			}
		}
		
		$this->db->order_by("a.id", 'DESC');
		
		$get_closeShift = $this->db->get();
		
		if($get_closeShift->num_rows() > 0){
			$closeShiftData = $get_closeShift->row_array();
			
			if(!empty($closeShiftData['tanggal_jam_shift'])){
				$tanggal_shift_close = date("Y-m-d", strtotime($closeShiftData['tanggal_jam_shift']));
				$closeShiftData['tanggal_shift_close'] = $tanggal_shift_close;
			}
			
		}else{
			
			if(empty($jam_shift_end)){
				$jam_shift_end = $jam_shift;
			}
			
			$tgl_shift_mk = strtotime($tanggal_shift);
			
			if(!empty($user_shift)){
				//check billing terakhir
				$tgl_shift_billing = date("ymd");
				$this->db->select('a.billing_no, a.payment_date, a.shift');
				$this->db->from($this->table_billing.' as a');
				$this->db->where("a.billing_no LIKE '".$tgl_shift_billing."%'");
				$this->db->where("a.shift = '".$user_shift."'");
				$this->db->where("a.billing_status = 'paid'");
				$this->db->where("a.is_deleted = 0");
				$this->db->order_by("a.payment_date","DESC");
				$get_lastBillingShift = $this->db->get();
				$jam_shift_end1_mk = 0;
				$jam_shift_end2_mk = 0;
				if($get_lastBillingShift->num_rows() > 0){
					$lastDt = $get_lastBillingShift->row();
					$lastDt_paymentdate = $lastDt->payment_date;
					//plus 1 menit
					$lastDt_paymentdate_mk = strtotime($lastDt->payment_date)+60;
					$tanggal_shift_x = date("d-m-Y", $lastDt_paymentdate_mk);
					$jam_shift_end_x = date("H:i", $lastDt_paymentdate_mk);
					
					$tgl_shift_mk2 = strtotime($tanggal_shift_x);
					if($tgl_shift_mk2 > $tgl_shift_mk){
						$tanggal_shift = $tanggal_shift_x;
						$jam_shift_end = $jam_shift_end_x;
					}else{
						if($tgl_shift_mk2 == $tgl_shift_mk){
							$jam_shift_end1_mk = strtotime($tanggal_shift." ".$jam_shift_end.":00");
							$jam_shift_end2_mk = strtotime($tanggal_shift_x." ".$jam_shift_end_x.":00");
							if($jam_shift_end2_mk > $jam_shift_end1_mk){
								$jam_shift_end = date("H:i", $jam_shift_end2_mk);
							}
						}
					}
					
				}
			}
			
			$closeShiftData = array(
				'id'			=> '',
				'spv_user'		=> '',
				'kasir_user'	=> $session_user,
				'tipe_shift'	=> 'close',
				'tanggal_shift'	=> $tanggal_shift,
				'jam_shift'		=> $jam_shift_end,
				'tanggal_shift_close'	=> $tanggal_shift,
				'user_shift'	=> $user_shift,
				'uang_kertas_100000'=> 0,
				'uang_kertas_50000'	=> 0,
				'uang_kertas_20000'	=> 0,
				'uang_kertas_10000'	=> 0,
				'uang_kertas_5000'	=> 0,
				'uang_kertas_2000'	=> 0,
				'uang_kertas_1000'	=> 0,
				'jumlah_uang_kertas'=> 0,
				'uang_koin_1000'	=> 0,
				'uang_koin_500'		=> 0,
				'uang_koin_200'		=> 0,
				'uang_koin_100'		=> 0,
				'jumlah_uang_koin'	=> 0
			);
		}
		
		if($is_return){
			
			return $closeShiftData;
		}
		
		$r = array('success' => true, 'closeShiftData' => $closeShiftData);
		
		die(json_encode($r));
	}
		
	public function doPrint(){
		
		header('Content-Type: text/plain; charset=utf-8');
		$this->table = $this->prefix.'open_close_shift';
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$id = $this->input->post_get('id', true);	
		$test = $this->input->post_get('test', true);	
		
		//LOAD USER OPEN SHIFT
		$get_data = $this->loadCloseShift(true, $id); //array
		if(!empty($get_data['id'])){
			
			$r = array('success' => false);
		
			$opt_value = array(
				'cashierReceipt_openclose_layout',
				'printer_ip_cashierReceipt_default',
				'printer_pin_cashierReceipt_default',
				'printer_tipe_cashierReceipt_default',
				'printer_ip_cashierReceipt_'.$ip_addr,
				'printer_pin_cashierReceipt_'.$ip_addr,
				'printer_tipe_cashierReceipt_'.$ip_addr
			);
			$get_opt = get_option_value($opt_value);
			
			//Cashier Printer ----------------------
			$printer_ip_cashierReceipt = "\\\\".$ip_addr."\\".$get_opt['printer_ip_cashierReceipt_default'];
			if(!empty($get_opt['printer_ip_cashierReceipt_'.$ip_addr])){
				$printer_ip_cashierReceipt = $get_opt['printer_ip_cashierReceipt_'.$ip_addr];			
				if(strstr($printer_ip_cashierReceipt, '\\')){
					$printer_ip_cashierReceipt = "\\\\".$printer_ip_cashierReceipt;
				}			
			}		
			
			if(empty($get_opt['cashierReceipt_openclose_layout'])){
				$get_opt['cashierReceipt_openclose_layout'] = '';
			}
			$cashierReceipt_openclose_layout = $get_opt['cashierReceipt_openclose_layout'];
			//---------------------- Cashier Printer
			
			$printer_pin_cashierReceipt = '42 CHAR';
			if(!empty($get_opt['printer_pin_cashierReceipt_'.$ip_addr])){
				$printer_pin_cashierReceipt = $get_opt['printer_pin_cashierReceipt_'.$ip_addr];
			}
			
			//trim prod name
			$max_text = 18; //42
			$max_number_1 = 9;
			$max_number_2 = 11;
			$max_number_3 = 13;

			if($printer_pin_cashierReceipt == 32){
				$max_text -= 6;
				$max_number_1 = 7;
				$max_number_2 = 8;
				$max_number_3 = 13;
			}
			if($printer_pin_cashierReceipt == 40){
				$max_text -= 2;
				$max_number_1 = 8;
				$max_number_2 = 11;
				$max_number_3 = 13;
			}
			if($printer_pin_cashierReceipt == 42){
				//$max_text -= 2;
				$max_number_1 = 8;
				$max_number_2 = 11;
				$max_number_3 = 13;
			}
			if($printer_pin_cashierReceipt == 46){
				$max_text += 2;
				$max_number_1 = 10;
				$max_number_2 = 12;
				$max_number_3 = 13;
			}
			if($printer_pin_cashierReceipt == 48){
				$max_text += 4;
				$max_number_1 = 10;
				$max_number_2 = 12;
				$max_number_3 = 13;
			}
			
			
			$info_data = "";	
			$uang_kertas_data = "";	
			$uang_koin_data = "";	
				
			$all_text_array = array();
			$new_data = array();
			$new_data_kertas = array();
			$new_data_koin = array();
			foreach($get_data as $key => $dt){
				
				$data_name = ucwords(str_replace("_"," ",$key));
				$data_name = ucwords(str_replace("Uang Kertas","nominal",$data_name));
				$data_name = ucwords(str_replace("Uang Koin","nominal",$data_name));
				
				if(strlen($data_name) > $max_text){
					//skip on last space
					$explTxt = explode(" ",$data_name);
					
					$no_exp = 1;
					$tot_txt = 0;
					$text_display = '';
					foreach($explTxt as $txt){
						$lnTxt = strlen($txt);
						$tot_txt += $lnTxt;
						
						if($tot_txt > 0){
							$tot_txt+=1; //space
						}
						
						if($tot_txt > $max_text){
							$all_text_array[] = $text_display;
							$tot_txt = 0;
							$text_display = $txt;
							
							//echo '2. '.$text_display.' '.$tot_txt.'<br/>';
							
						}else{
						
							if(empty($text_display)){
								$text_display = $txt;
							}else{
								$text_display .= ' '.$txt;										
							}
							
							//echo '1. '.$text_display.' '.$tot_txt.'<br/>';
							
						}
						
						if(count($explTxt) == $no_exp){
							$all_text_array[] = $text_display;
						}
						
						$no_exp++;
					}
					
					if(empty($all_text_array[0])){
						$data_name = substr($data_name, 0, $max_text);
					}else{
						$data_name = $all_text_array[0];
					}
				}
				
				if(strstr($key, 'uang_kertas_')){
					
					$data_name = ucwords(str_replace("_"," ",$key));
					$data_name = ucwords(str_replace("Uang Kertas","",$data_name));
					$data_name = ucwords(str_replace("Uang Koin","",$data_name));
					
					$new_data_kertas[$key] = array("name" => '', "value" => '');
					$new_data_kertas[$key]['name'] = $data_name;
					$new_data_kertas[$key]['value'] = $dt;
					
					$get_nominal = str_replace("Nominal","",$data_name);
					$value_show = printer_command_align_right(priceFormat($dt*$get_nominal), $max_number_3);
					
					if(!empty($dt)){
						if(empty($uang_kertas_data)){
							$total_uang_kertas = $get_data['jumlah_uang_kertas'];
							$total_uang_kertas = printer_command_align_right(priceFormat($total_uang_kertas), $max_number_3);
							$uang_kertas_data = "[size=0][align=0]UANG KERTAS[tab]".$total_uang_kertas."\n";
						}
						
						$strlen_x = strlen(priceFormat($get_nominal));
						$selisih_char = 7-$strlen_x;
						
						$uang_kertas_data .= "[size=0][align=0] ".priceFormat($get_nominal).str_repeat(" ",$selisih_char)." x ".$dt."[tab]".$value_show."\n"; 
					}
					
				}else
				if(strstr($key, 'uang_koin_')){
					
					$data_name = ucwords(str_replace("_"," ",$key));
					$data_name = ucwords(str_replace("Uang Kertas","",$data_name));
					$data_name = ucwords(str_replace("Uang Koin","",$data_name));
					
					$new_data_koin[$key] = array("name" => '', "value" => '');
					$new_data_koin[$key]['name'] = $data_name;
					$new_data_koin[$key]['value'] = $dt;
					
					$get_nominal = str_replace("Nominal","",$data_name);
					$value_show = printer_command_align_right(priceFormat($dt*$get_nominal), $max_number_3);
					
					if(!empty($dt)){
						if(empty($uang_koin_data)){
							$total_uang_koin = $get_data['jumlah_uang_koin'];
							$total_uang_koin = printer_command_align_right(priceFormat($total_uang_koin), $max_number_3);
							$uang_koin_data = "[size=0][align=0]UANG KOIN[tab]".$total_uang_koin."\n";
						}
						
						$strlen_x = strlen(priceFormat($get_nominal));
						$selisih_char = 5-$strlen_x;
						
						$uang_koin_data .= "[size=0][align=0] ".priceFormat($get_nominal).str_repeat(" ",$selisih_char)." x ".$dt."[tab]".$value_show."\n";
					}
					
				}else{
					$new_data[$key] = array("name" => '', "value" => '');
					$new_data[$key]['name'] = $data_name;
					
					$new_val = $dt;
					if($key == 'user_shift'){
						
						$new_val = 'Shift';
						if(!empty($get_data['nama_shift'])){
							$new_val = $get_data['nama_shift'];
						}else{
							$new_val = 'Shift '.$dt;
						}
						
						
					}
					
					if(empty($new_val)){
						$new_val = '-';
					}
					
					$new_data[$key]['value'] = $new_val;
					
					$info_data .= "[size=1][align=0]".$data_name."[tab]".$new_val."\n";
					
				}
				
				
				
			}
			
			unset($new_data['id']);
			unset($new_data['is_validate']);
			unset($new_data['createdby']);
			unset($new_data['created']);
			unset($new_data['updatedby']);
			unset($new_data['updated']);
			unset($new_data['is_deleted']);
			unset($new_data['is_deleted']);
			
			
			//TYPE PRINTER
			$printer_type_cashier = '';
			$printer_tipe_cashierReceipt_default = '';
			if(!empty($get_opt['printer_tipe_cashierReceipt_default'])){
				$printer_tipe_cashierReceipt_default = $get_opt['printer_tipe_cashierReceipt_default'];
			}
			if(!empty($get_opt['printer_tipe_cashierReceipt_'.$ip_addr])){
				$printer_type_cashier = $get_opt['printer_tipe_cashierReceipt_'.$ip_addr];
			}
			
			if(empty($printer_type_cashier)){
				$printer_type_cashier = $printer_tipe_cashierReceipt_default;
			}
			
			//TOTAL BILLING - SSR
			$data_post = array();
			$this->table_billing = $this->prefix.'billing';
			$this->table_billing_detail = $this->prefix.'billing_detail';
		
			$get_opt = get_option_value(array('report_place_default','diskon_sebelum_pajak_service',
			'cashier_max_pembulatan','cashier_pembulatan_keatas','pembulatan_dinamis',
			'jam_operasional_from','jam_operasional_to','jam_operasional_extra','jumlah_shift'));
			
			if(!empty($get_opt['report_place_default'])){
				$data_post['report_place_default'] = $get_opt['report_place_default'];
			}
			if(!empty($get_opt['diskon_sebelum_pajak_service'])){
				$data_post['diskon_sebelum_pajak_service'] = $get_opt['diskon_sebelum_pajak_service'];
			}else{
				$data_post['diskon_sebelum_pajak_service'] = 0;
			}
			if(empty($get_opt['cashier_max_pembulatan'])){
				$get_opt['cashier_max_pembulatan'] = 0;
			}
			if(empty($get_opt['cashier_pembulatan_keatas'])){
				$get_opt['cashier_pembulatan_keatas'] = 0;
			}
			if(empty($get_opt['pembulatan_dinamis'])){
				$get_opt['pembulatan_dinamis'] = 0;
			}
		
			$jumlah_shift = 1;
			if(!empty($get_opt['jumlah_shift'])){
				$jumlah_shift = $get_opt['jumlah_shift'];
			}
			
			$get_date_from = date("d-m-Y H:i:s", strtotime($new_data['tanggal_shift']['value']))." ".$new_data['jam_shift']['value'].":00";
			$get_hour = date("G", strtotime($get_date_from));
			
			$date_from = date("d-m-Y", strtotime($new_data['tanggal_shift']['value']))." ".$new_data['jam_shift']['value'];
			$date_till = date("d-m-Y", strtotime($new_data['tanggal_shift']['value']))." ".$new_data['jam_shift']['value'];
			
			if($get_hour < 4){
				$date_from = date("d-m-Y", strtotime($get_date_from)-ONE_DAY_UNIX);
				$date_till = date("d-m-Y", strtotime($get_date_from)-ONE_DAY_UNIX);
			}
			
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
				
			$ret_dt = check_report_jam_operasional($get_opt, $mktime_dari, $mktime_sampai);
			
			$datenowstr = strtotime(date("d-m-Y H:i:s"));
		
			//SHIFT
			$nama_shift = $get_data['nama_shift'];
			$tanggal_cetak = date("d/m/Y"); //d/m/Y
			$jam_cetak = date("H:i");
			$user_shift = $get_data['user_shift'];
			if($jumlah_shift > 1){
				$tanggal_shift = $date_from;
				$this->db->select('a.*, b.nama_shift');
				$this->db->from($this->prefix.'shift_log as a');
				$this->db->join($this->prefix.'shift as b',"b.id = a.user_shift","LEFT");
				$this->db->where("a.tanggal_shift", $tanggal_shift);
				$this->db->where("a.user_shift", $user_shift);
				$this->db->order_by("a.id", 'DESC');
				$getShiftLog = $this->db->get();
				if($getShiftLog->num_rows() > 0){
					$dataShiftLog = $getShiftLog->row_array();
					
					$tanggal_jam_start = $dataShiftLog['tanggal_jam_start'];
					$jam_shift_end = $dataShiftLog['jam_shift_end'];
					if(empty($jam_shift_end)){
						$jam_shift_end = date("H:i", $datenowstr);
					}
					$nama_shift = $dataShiftLog['nama_shift'];
					$jam_cetak = $jam_shift_end;
					
					//$qdate_from = $tanggal_jam_start;
					//$qdate_till = $tanggal_jam_end;
					//$qdate_till_max = $tanggal_jam_end;
					
					$user_shift = $dataShiftLog['user_shift'];
				}
				
			}
			
			//laporan = jam_operasional
			$qdate_from = $ret_dt['qdate_from'];
			$qdate_till = $ret_dt['qdate_till'];
			$qdate_till_max = $ret_dt['qdate_till_max'];
			
			$add_where = "(a.payment_date >= '".$qdate_from."' AND a.payment_date <= '".$qdate_till_max."')";
			if($jumlah_shift > 1){
				$add_where = "(a.payment_date >= '".$qdate_from."' AND a.payment_date <= '".$qdate_till_max."') AND shift = ".$user_shift;
			}
			
			$this->db->select("a.*, a.id as billing_id, a.updated as billing_date, d.payment_type_name, e.bank_name");
			$this->db->from($this->table_billing." as a");
			$this->db->join($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT');
			$this->db->join($this->prefix.'bank as e','e.id = a.bank_id','LEFT');
			$this->db->where("a.billing_status", 'paid');
			$this->db->where("a.is_deleted", 0);
			$this->db->where($add_where);
			
			//user_shift
			$this->db->where("a.shift", $get_data['user_shift']);
			
			$this->db->order_by("payment_id","ASC");
			
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
			
			//PAYMENT DATA
			$dt_payment_name = array();
			$this->db->select('*');
			$this->db->from($this->prefix.'payment_type');
			$get_dt_p = $this->db->get();
			if($get_dt_p->num_rows() > 0){
				foreach($get_dt_p->result_array() as $dtP){
					$dt_payment_name[$dtP['id']] = strtoupper($dtP['payment_type_name']);
				}
			}
			$payment_data = $dt_payment_name;
			
			$default_payment_bank = array();
			//BANK DATA
			$bank_data = array();
			$bank_data[0] = 'CASH';
			$this->db->from($this->prefix.'bank');
			$get_bank = $this->db->get();
			if($get_bank->num_rows() > 0){
				foreach($get_bank->result() as $dtRow){
					$bank_data[$dtRow->id] = $dtRow->bank_name;
					
					if(empty($default_payment_bank[$dtRow->payment_id])){
						$default_payment_bank[$dtRow->payment_id] = $dtRow->id;
					}
					
				}
			}
			
			//update-2002.003
			$all_bil_id = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					
					if(!in_array($s['id'], $all_bil_id)){
						$all_bil_id[] = $s['id'];
					}		
					
				}
			}
			
			//update-2002.003
			$total_billing = array();
			if(!empty($all_bil_id)){
				$all_bil_id_txt = implode(",",$all_bil_id);
				$this->db->from($this->table_billing_detail);
				$this->db->where('billing_id IN ('.$all_bil_id_txt.')');
				$this->db->where('is_deleted', 0);
				$get_detail = $this->db->get();
				if($get_detail->num_rows() > 0){
					foreach($get_detail->result() as $dtRow){
						
						$total_qty = $dtRow->order_qty;
						
						//update-2002.003
						if((!empty($dtRow->include_tax) AND empty($dtRow->include_service)) OR (empty($dtRow->include_tax) AND !empty($dtRow->include_service))){
							if($dtRow->product_price != ($dtRow->product_price_real+$dtRow->tax_total+$dtRow->service_total)){
								$all_percentage = 100 + $dtRow->tax_percentage + $dtRow->service_percentage;
								$dtRow->product_price_real = priceFormat(($dtRow->product_price/($all_percentage/100)), 0, ".", "");
							}
						}
						$total_billing[$dtRow->billing_id] += $dtRow->product_price_real * $total_qty;
						
					}
				}
			}
			
			//$all_bil_id = array();
			$all_discount_id = array();
			$summary_payment = array();
			$summary_payment[0] = array(
				'payment_id'	=> 1,
				'payment_name'	=> 'CASH',
				'bank_id'	=> 0,
				'bank_name'	=> 'CASH',
				'total_billing'	=> 0,
				'total_billing_show'	=> 0,
				'discount_total'	=> 0,
				'discount_total_show'	=> 0,
				'discount_billing_total'	=> 0,
				'discount_billing_total_show'	=> 0,
				'tax_total'	=> 0,
				'tax_total_show'	=> 0,
				'service_total'	=> 0,
				'service_total_show'	=> 0,
				'sub_total'	=> 0,
				'sub_total_show'	=> 0,
				'net_sales'	=> 0,
				'net_sales_show'	=> 0,
				'total_pembulatan'	=> 0,
				'total_pembulatan_show'	=> 0,
				'total_compliment'	=> 0,
				'total_compliment_show'	=> 0,
				'grand_total'	=> 0,
				'grand_total_show'	=> 0,
				'total_qty'	=> 0,
				'total_hpp'	=> 0,
				'total_hpp_show'	=> 0,
				'compliment_total'	=> 0,
				'compliment_total_show'	=> 0,
				'total_dp'	=> 0,
				'total_dp_show'	=> 0,
				'total_profit'	=> 0,
			);
			
			$konversi_pembulatan_billing = array();
			$balancing_discount_billing = array();
			
			$data_post['summary_data'] = array(
				'total_billing'	=> 0,
				'total_discount_item'	=> 0,
				'total_discount_billing'	=> 0,
				'net_sales'	=> 0,
				'service_total'	=> 0,
				'tax_total'	=> 0,
				'total_pembulatan'	=> 0,
				'compliment_total'	=> 0,
				'total_dp'	=> 0,
				'grand_total'	=> 0,
				'total_of_item_discount'	=> 0,
				'total_of_billing'	=> 0,
				'total_of_guest'	=> 0,
				'total_day'	=> 1,
				'sales_without_service'	=> 0,
				'sales_without_tax'	=> 0,
				'sales_per_guest'	=> 0,
				'sales_per_bill'	=> 0,
				'average_daily_guest'	=> 0,
				'average_daily_billing'	=> 0,
				'average_daily_sales'	=> 0,
			);
			
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					$s['billing_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['payment_date'] = date("d-m-Y H:i",strtotime($s['payment_date']));
					
					//if(!in_array($s['id'], $all_bil_id)){
					//	$all_bil_id[] = $s['id'];
					//}		
					
					$s['total_billing_awal'] = $s['total_billing'];
					
					//update-2002.003
					//CHECK REAL TOTAL BILLING
					if(!empty($s['include_tax']) OR !empty($s['include_service'])){
						//update-2002.003
						$s['total_billing'] = $total_billing[$s['id']];
						$s['total_billing_awal'] = $s['total_billing'];
					}
							
					//CHECK REAL TOTAL BILLING
					/*if(!empty($s['include_tax']) OR !empty($s['include_service'])){
						if(!empty($s['include_tax']) AND !empty($s['include_service'])){
						
							if($data_post['diskon_sebelum_pajak_service'] == 1){
								$get_total_billing = $s['total_billing'] / (($s['tax_percentage']+$s['service_percentage']+100)/100);
								$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
								$s['total_billing'] = $get_total_billing;
							}else{
								$s['total_billing'] = $s['total_billing'] - ($s['tax_total'] + $s['service_total']);
							}
							
						}else{
							if(!empty($s['include_tax'])){
								if($data_post['diskon_sebelum_pajak_service'] == 1){
									$get_total_billing = $s['total_billing'] / (($s['tax_percentage']+100)/100);
									$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
									$s['total_billing'] = $get_total_billing;
								}else{
									$s['total_billing'] = $s['total_billing'] - ($s['tax_total']);
								}
							}
							if(!empty($s['include_service'])){
								if($data_post['diskon_sebelum_pajak_service'] == 1){
									$get_total_billing = $s['total_billing'] / (($s['service_percentage']+100)/100);
									$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
									$s['total_billing'] = $get_total_billing;
								}else{
									$s['total_billing'] = $s['total_billing'] - ($s['service_total']);
								}
							}
						}
					}*/
					
					if(!empty($s['is_compliment'])){
						//$s['total_billing'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
						if($s['total_billing'] <= $s['compliment_total']){
							$s['service_total'] = 0;
							$s['tax_total'] = 0;
						}
					}
					
					//diskon_sebelum_pajak_service
					if($data_post['diskon_sebelum_pajak_service'] == 1){
						$s['sub_total'] = $s['total_billing'] - $s['discount_total'] + $s['tax_total'] + $s['service_total'] - $s['compliment_total'];
						$s['net_sales'] = $s['total_billing'] - $s['discount_total'] - $s['compliment_total'];
						
						/*if(!empty($s['include_tax']) OR !empty($s['include_service'])){
							//CHECKING BALANCE #1
							if(empty($s['discount_total'])){
								if($s['sub_total'] != $s['total_billing_awal']){
									$s['total_billing'] = ($s['total_billing_awal'] - ($s['tax_total'] + $s['service_total']));
									$s['sub_total'] = $s['total_billing'] - $s['discount_total'] + $s['tax_total'] + $s['service_total'];
								}
							}else{
								if(($s['sub_total'] + $s['total_pembulatan']) != $s['grand_total']){
									$s['sub_total'] = ($s['grand_total']-$s['total_pembulatan'])+$s['compliment_total'];
								}
								
								$cek_total_billing = $s['sub_total'] - ($s['tax_total'] + $s['service_total']) + $s['discount_total'];
								if($s['total_billing'] != $cek_total_billing){
									$s['total_billing'] = $cek_total_billing;
								}
							}
						}*/
						
						//GRAND TOTAL
						$s['grand_total'] = $s['sub_total'];
						
					}else{
						$s['sub_total'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'] - $s['compliment_total'];		
						$s['net_sales'] = $s['total_billing'] - $s['compliment_total'];
						
						//GRAND TOTAL
						$s['grand_total'] = $s['sub_total'];
						$s['grand_total'] -= $s['discount_total'];
						$s['grand_total'] -= $s['discount_billing_total'];
					}
					
					if(!empty($s['discount_id'])){
						if(!in_array($s['discount_id'], $all_discount_id)){
							$all_discount_id[] = $s['discount_id'];
						}
					}
					
					//SPLIT DISCOUNT TYPE
					if(!empty($s['discount_total']) AND $s['discount_perbilling'] == 1){
						$s['discount_billing_total'] = $s['discount_total'];
						$s['discount_total'] = 0;
					}else{
						$s['discount_billing_total'] = 0;
					}
					
					//if(!empty($s['include_tax']) OR !empty($s['include_service'])){
					//	$s['sub_total'] = $s['total_billing'];
					//}
					
					$s['grand_total'] = $s['sub_total'] + $s['total_pembulatan'];
					//$s['grand_total'] -= $s['compliment_total'];
					
					if($s['grand_total'] <= 0){
						$s['grand_total'] = 0;
					}
					
					$s['total_pembulatan_show'] = priceFormat($s['total_pembulatan']);
					
					if($s['total_pembulatan'] < 0){
						$s['total_pembulatan_show'] = "(".priceFormat($s['total_pembulatan']).")";
					}
					
					if(empty($s['net_sales'])){
						$s['net_sales'] = 0;
					}
					
					$s['sub_total_show'] = priceFormat($s['sub_total']);
					$s['net_sales_show'] = priceFormat($s['net_sales']);
					$s['grand_total_show'] = priceFormat($s['grand_total']);
					$s['total_billing_show'] = priceFormat($s['total_billing']);
					$s['total_paid_show'] = priceFormat($s['total_paid']);
					$s['tax_total_show'] = priceFormat($s['tax_total']);
					$s['service_total_show'] = priceFormat($s['service_total']);
					$s['discount_total_show'] = priceFormat($s['discount_total']);
					$s['discount_billing_total_show'] = priceFormat($s['discount_billing_total']);
					
					//DP
					$s['total_dp_show'] = priceFormat($s['total_dp']);
					
					$s['total_compliment'] = 0;
					$s['total_compliment_show'] = 0;

					$s['total_hpp'] = 0;
					$s['total_hpp_show'] = 0;
					$s['total_profit'] = 0;
					$s['total_profit_show'] = 0;
					
					//CARD NO 
					$card_no = '';
					if(strlen($s['card_no']) > 30){
						$card_no = $s['card_no'];
						$card_no = str_replace(";","",$card_no);
						$card_no = str_replace("?","",$card_no);
						$card_no_exp = explode("=", $card_no);
						if(!empty($card_no_exp[0])){
							$card_no = trim($card_no_exp[0]);
						}
					}else{
						$card_no = trim($s['card_no']);
					}
					
					//NOTES
					$s['payment_note'] = '';
					if(!empty($s['is_compliment']) OR !empty($s['compliment_total'])){
						$s['payment_note'] = 'COMPLIMENT';
						//$s['total_compliment'] = $s['grand_total'];
						$s['total_compliment'] = $s['compliment_total'];
						$s['total_compliment_show'] = priceFormat($s['total_compliment']);
						//$s['is_compliment'] = 1;
					}
					
					if(!empty($s['is_half_payment'])){
						if(!empty($s['payment_note'])){
							$s['payment_note'] .= ', ';
						}
						$s['payment_note'] .= 'HALF PAYMENT';
					}
					
					if(strtolower($s['payment_type_name']) != 'cash'){
						if(!empty($s['payment_note'])){
							$s['payment_note'] .= '<br/>';
						}
						$s['payment_note'] .= strtoupper($s['payment_type_name']) .': '.strtoupper($s['bank_name']).' '.$card_no;
					}
					
					if(!empty($s['billing_notes'])){
						if(!empty($s['payment_note'])){
							$s['payment_note'] .= '<br/>';
						}
						$s['payment_note'] .= $s['billing_notes'];
					}
					
					$data_post['summary_data']['total_billing'] += $s['total_billing'];
					$data_post['summary_data']['total_discount_item'] += $s['discount_total'];
					$data_post['summary_data']['total_discount_billing'] += $s['discount_billing_total'];
					$data_post['summary_data']['net_sales'] += $s['net_sales'];
					$data_post['summary_data']['total_dp'] += $s['total_dp'];
					$data_post['summary_data']['service_total'] += $s['service_total'];
					$data_post['summary_data']['tax_total'] += $s['tax_total'];
					$data_post['summary_data']['total_pembulatan'] += $s['total_pembulatan'];
					$data_post['summary_data']['compliment_total'] += $s['compliment_total'];
					$data_post['summary_data']['grand_total'] += $s['grand_total'];
					$data_post['summary_data']['total_of_guest'] += $s['total_guest'];
					$data_post['summary_data']['total_of_billing'] += 1;
					
					if($s['service_total'] == 0){
						$data_post['summary_data']['sales_without_service'] += $s['grand_total'];
					}
					if($s['tax_total'] == 0){
						$data_post['summary_data']['sales_without_tax'] += $s['grand_total'];
					}
					
					
					//SUMMARY PAYMENT
					if(empty($s['bank_id'])){
						$s['bank_id'] = 0;
						
						//update AR - 2019-02-15
						if($s['payment_id'] == 2){
							//if(!empty($default_payment_bank[$s['payment_id']])){
							//	$s['bank_id'] = $default_payment_bank[$s['payment_id']];
							//}
							$s['bank_id'] = 'DEBIT';
						}
						
						if($s['payment_id'] == 3){
							$s['bank_id'] = 'CREDIT';
						}
						
						if($s['payment_id'] == 4){
							$s['bank_id'] = 'AR';
						}
						
					}
					
					$var_payment = $s['bank_id'];
					if(empty($summary_payment[$var_payment])){
						
						//update AR - 2019-02-15
						$bank_name = 'CASH';
						if(!empty($bank_data[$s['bank_id']])){
							$bank_name = $bank_data[$s['bank_id']];
						}
						
						$payment_name = 'CASH';
						if(!empty($dt_payment_name[$s['payment_id']])){
							$payment_name = $dt_payment_name[$s['payment_id']];
							
							if($s['payment_id'] == 4){
								//$bank_name = 'AR / PIUTANG';
							}
						}
						
						$summary_payment[$var_payment] = array(
							'payment_id'	=> $s['payment_id'],
							'payment_name'	=> $payment_name,
							'bank_id'	=> $s['bank_id'],
							'bank_name'	=> $bank_name,
							'total_billing'	=> 0,
							'total_billing_show'	=> 0,
							'discount_total'	=> 0,
							'discount_total_show'	=> 0,
							'discount_billing_total'	=> 0,
							'discount_billing_total_show'	=> 0,
							'tax_total'	=> 0,
							'tax_total_show'	=> 0,
							'service_total'	=> 0,
							'service_total_show'	=> 0,
							'sub_total'	=> 0,
							'sub_total_show'	=> 0,
							'net_sales'	=> 0,
							'net_sales_show'	=> 0,
							'total_pembulatan'	=> 0,
							'total_pembulatan_show'	=> 0,
							'total_compliment'	=> 0,
							'total_compliment_show'	=> 0,
							'grand_total'	=> 0,
							'grand_total_show'	=> 0,
							'total_qty'	=> 0,
							'total_hpp'	=> 0,
							'total_hpp_show'	=> 0,
							'compliment_total'	=> 0,
							'compliment_total_show'	=> 0,
							'total_dp'	=> 0,
							'total_dp_show'	=> 0,
							'total_profit'	=> 0,
							'total_profit_show'	=> 0
						);
						
						if(!empty($payment_data)){
							foreach($payment_data as $key_id => $dtPay){
								$summary_payment[$var_payment]['payment_'.$key_id] = 0;	
								$summary_payment[$var_payment]['payment_'.$key_id.'_show'] = 0;						
							}
						}
						
					}
					
					$summary_payment[$var_payment]['total_qty'] += 1;
					$summary_payment[$var_payment]['total_billing'] += $s['total_billing'];
					$summary_payment[$var_payment]['discount_total'] += $s['discount_total'];
					$summary_payment[$var_payment]['discount_billing_total'] += $s['discount_billing_total'];
					$summary_payment[$var_payment]['tax_total'] += $s['tax_total'];
					$summary_payment[$var_payment]['service_total'] += $s['service_total'];
					$summary_payment[$var_payment]['sub_total'] += $s['sub_total'];
					$summary_payment[$var_payment]['net_sales'] += $s['net_sales'];
					$summary_payment[$var_payment]['total_pembulatan'] += $s['total_pembulatan'];
					$summary_payment[$var_payment]['grand_total'] += $s['grand_total'];
					$summary_payment[$var_payment]['total_compliment'] += $s['total_compliment'];
					$summary_payment[$var_payment]['compliment_total'] += $s['compliment_total'];
					$summary_payment[$var_payment]['total_dp'] += $s['total_dp'];
					
					
					if(!empty($payment_data)){
						foreach($payment_data as $key_id => $dtPay){
					
							//update-2001.200
							$tot_payment = 0;
							$tot_payment_halfpayment = 0; 
							$tot_payment_show = 0;
							if($s['payment_id'] == $key_id){
								//$tot_payment = $s['grand_total'];
								//$tot_payment_show = $s['grand_total_show'];
								
								//update AR - 2019-02-15
								if($key_id == 2 OR $key_id == 3 OR $key_id == 4){
									$tot_payment = $s['total_credit'];	
								}else{
									$tot_payment = $s['total_cash'];	
								}
								
								$tot_payment_show = priceFormat($tot_payment);
								
								//credit half payment
								if(!empty($s['is_half_payment']) AND $key_id != 1){
									$tot_payment = $s['total_credit'];
									$tot_payment_halfpayment = $s['total_cash'];
									//$tot_payment_show = priceFormat($s['total_credit']);
									$tot_payment_show = priceFormat($tot_payment+$tot_payment_halfpayment);
								}else{
									
									$tot_payment_show = priceFormat($tot_payment);	
								}
									
							}else{
								//cash
								if(!empty($s['is_half_payment']) AND $key_id == 1){
									$tot_payment = $s['total_cash'];
									$tot_payment_show = priceFormat($s['total_cash']);
								}
							}
					
							if(empty($grand_total_payment[$key_id])){
								$grand_total_payment[$key_id] = 0;
							}
					
							if(!empty($s['is_compliment'])){
								$tot_payment = 0;
								$tot_payment_show = 0;
							}
							
							$summary_payment[$var_payment]['payment_'.$key_id] += $tot_payment;
							if(!empty($tot_payment_halfpayment)){
								$summary_payment[0]['payment_1'] += $tot_payment_halfpayment;
							}
															
						}
					}
					
					//BALANCING DISKON
					if(!empty($s['billing_discount_total'])){
						if(empty($balancing_discount_billing[$s['billing_id']])){
							$balancing_discount_billing[$s['billing_id']] = array(
								'discount_total'	=> $s['billing_discount_total'],
								'discount_detail_total'	=> 0,
								'payment_id'	=> 0,
								'bank_id'	=> 0,
								'discount_perbilling'	=> $s['discount_perbilling'],
								'discount_detail'	=> array(),
								'billing_date'	=> $s['billing_date']
							);
						}
					}
					
					if(!empty($s['billing_discount_total'])){
						if(empty($balancing_discount_billing[$s['billing_id']]['discount_detail'][$s['product_id']])){
							$balancing_discount_billing[$s['billing_id']]['discount_detail'][$s['product_id']] = array(
								'total_discount'=> 0,
								'total_discount_balance'=> 0,
								'tax_total'	=> 0,
								'service_total'	=> 0,
								'total_billing'	=> 0,
								'sub_total'	=> 0,
								'sub_total_balance'=> 0,
								'discount_balance'=> 0
							);
						}
						$balancing_discount_billing[$s['billing_id']]['discount_detail'][$s['product_id']]['total_discount'] += $total_discount_product;
						$balancing_discount_billing[$s['billing_id']]['discount_detail'][$s['product_id']]['tax_total'] += $s['tax_total'];
						$balancing_discount_billing[$s['billing_id']]['discount_detail'][$s['product_id']]['service_total'] += $s['service_total'];
						$balancing_discount_billing[$s['billing_id']]['discount_detail'][$s['product_id']]['total_billing'] += $total_billing;
						$balancing_discount_billing[$s['billing_id']]['discount_detail'][$s['product_id']]['sub_total'] += $sub_total;
						$balancing_discount_billing[$s['billing_id']]['discount_detail_total'] += $total_discount_product;
						$balancing_discount_billing[$s['billing_id']]['payment_id'] = $s['payment_id'];
						$balancing_discount_billing[$s['billing_id']]['bank_id'] = $s['bank_id'];
					}
					
					//$newData[$s['id']] = $s;
					if(!empty($total_billing)){
						//KONVERSI PEMBULATAN PER-ITEM
						if(empty($konversi_pembulatan_billing[$s['billing_id']])){
							$konversi_pembulatan_billing[$s['billing_id']] = array(
								'total_qty'	=> 0,
								'billing_total_pembulatan'	=> $s['billing_total_pembulatan'],
								'total_pembulatan_product'	=> array(),
								'billing_date'	=> $s['billing_date']
							);
						}
						
						$konversi_pembulatan_billing[$s['billing_id']]['total_qty'] += $s['order_qty'];
						if(empty($konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']])){
							$konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']] = array(
								'total_pembulatan'	=> 0,
								'payment'	=> array(),
								'bank'	=> array()
							);
						}
						$konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']]['total_pembulatan'] = $total_pembulatan;
						if(!empty($s['payment_id'])){
							if(empty($konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']]['payment'][$s['payment_id']])){
								$konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']]['payment'][$s['payment_id']] = 0;
							}
							$konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']]['payment'][$s['payment_id']] += $total_pembulatan;
						}
						
						//bank_id
						if(!empty($s['bank_id'])){
							if(empty($konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']]['bank'][$s['bank_id']])){
								$konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']]['bank'][$s['bank_id']] = 0;
							}
							$konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']]['bank'][$s['bank_id']] += $total_pembulatan;
						}
					}
				}
			}
			
			
				
			//PEMBAGIAN PEMBULATAN AVERAGE
			$konversi_pembulatan_product = array();
			$konversi_pembulatan_product_payment = array();
			$konversi_pembulatan_product_bank = array();
			$pembulatan_awal_product = array();
			$pembulatan_awal_product_payment = array();
			$pembulatan_awal_product_bank = array();
			if(!empty($konversi_pembulatan_billing)){
				foreach($konversi_pembulatan_billing as $dt){
					//if($dt['billing_total_pembulatan'] != 0){
						$pembagian_pembulatan = $dt['billing_total_pembulatan'] / count($dt['total_pembulatan_product']);
						
						$pembagian_pembulatan = number_format($pembagian_pembulatan, 2);
						
						//cek selisih
						$selisih_pembagian = $pembagian_pembulatan*count($dt['total_pembulatan_product']) - $dt['billing_total_pembulatan'];
						//echo ($pembagian_pembulatan*count($dt['total_pembulatan_product'])).' - '.$dt['billing_total_pembulatan'].' = '.$selisih_pembagian.'<br/>';
						$no = 1;
						foreach($dt['total_pembulatan_product'] as $product_id => $data){
							if(empty($konversi_pembulatan_product[$product_id])){
								$konversi_pembulatan_product[$product_id] = array(
									//'total_pembulatan' => 0
								);
							}
							if(empty($konversi_pembulatan_product[$product_id][$dt['billing_date']])){
								$konversi_pembulatan_product[$product_id][$dt['billing_date']] = array(
									'total_pembulatan' => 0
								);
							}
							if(empty($pembulatan_awal_product[$product_id])){
								$pembulatan_awal_product[$product_id] = array();
							}
							if(empty($pembulatan_awal_product[$product_id][$dt['billing_date']])){
								$pembulatan_awal_product[$product_id][$dt['billing_date']] = 0;
							}
							
							$pembulatan_awal_product[$product_id][$dt['billing_date']] += $data['total_pembulatan'];
							
							$konversi_pembulatan_product[$product_id][$dt['billing_date']]['total_pembulatan'] += $pembagian_pembulatan;
							if($no == 1 AND $selisih_pembagian != 0){
								$konversi_pembulatan_product[$product_id][$dt['billing_date']]['total_pembulatan'] -= $selisih_pembagian;
							}
							
							//PAYMENT
							if(!empty($data['payment'])){
								foreach($data['payment'] as $payment_id => $dtP){
									if(empty($konversi_pembulatan_product_payment[$product_id][$dt['billing_date']])){
										$konversi_pembulatan_product_payment[$product_id][$dt['billing_date']] = array();
									}
									if(empty($konversi_pembulatan_product_payment[$product_id][$dt['billing_date']][$payment_id])){
										$konversi_pembulatan_product_payment[$product_id][$dt['billing_date']][$payment_id] = 0;
									}
									$konversi_pembulatan_product_payment[$product_id][$dt['billing_date']][$payment_id] += $pembagian_pembulatan;
									if($no == 1 AND $selisih_pembagian != 0){
										$konversi_pembulatan_product_payment[$product_id][$dt['billing_date']][$payment_id] -= $selisih_pembagian;
									}
									
									if(empty($pembulatan_awal_product_payment[$product_id][$dt['billing_date']])){
										$pembulatan_awal_product_payment[$product_id][$dt['billing_date']] = array();
									}
									if(empty($pembulatan_awal_product_payment[$product_id][$dt['billing_date']][$payment_id])){
										$pembulatan_awal_product_payment[$product_id][$dt['billing_date']][$payment_id] = 0;
									}
									$pembulatan_awal_product_payment[$product_id][$dt['billing_date']][$payment_id] += $dtP;
									
									
								}
								
							}
							//$konversi_data = $data['total_pembulatan'] - $pembagian_pembulatan;
							
							//BANK
							if(!empty($data['bank'])){
								foreach($data['bank'] as $bank_id => $dtP){
									if(empty($konversi_pembulatan_product_bank[$product_id][$dt['billing_date']])){
										$konversi_pembulatan_product_bank[$product_id][$dt['billing_date']] = array();
									}
									if(empty($konversi_pembulatan_product_bank[$product_id][$dt['billing_date']][$bank_id])){
										$konversi_pembulatan_product_bank[$product_id][$dt['billing_date']][$bank_id] = 0;
									}
									$konversi_pembulatan_product_bank[$product_id][$dt['billing_date']][$bank_id] += $pembagian_pembulatan;
									if($no == 1 AND $selisih_pembagian != 0){
										$konversi_pembulatan_product_bank[$product_id][$dt['billing_date']][$bank_id] -= $selisih_pembagian;
									}
									
									if(empty($pembulatan_awal_product_bank[$product_id][$dt['billing_date']])){
										$pembulatan_awal_product_bank[$product_id][$dt['billing_date']] = array();
									}
									if(empty($pembulatan_awal_product_bank[$product_id][$dt['billing_date']][$bank_id])){
										$pembulatan_awal_product_bank[$product_id][$dt['billing_date']][$bank_id] = 0;
									}
									$pembulatan_awal_product_bank[$product_id][$dt['billing_date']][$bank_id] += $dtP;
									
									
								}
								
							}
							//$konversi_data = $data['total_pembulatan'] - $pembagian_pembulatan;
							
							$no++;
						}
					//}
				}
			}
			
			//BALANCING DISKON
			$data_diskon_awal = array();
			$data_diskon_awal_payment = array();
			$data_diskon_awal_bank = array();
			$data_balancing_diskon = array();
			$data_balancing_diskon_payment = array();
			$data_balancing_diskon_bank = array();
			$data_selisih_diskon = array();
			$data_selisih_diskon_payment = array();
			$data_selisih_diskon_bank = array();
			if(!empty($balancing_discount_billing)){
				foreach($balancing_discount_billing as $billing_id => $dt){
					$selisih_diskon = $dt['discount_total'] - $dt['discount_detail_total'];
					$total_produk = count($dt['discount_detail']);
					
					//AVERAGE
					$selisih_diskon_perproduct = 0;
					if($selisih_diskon != 0){
						$selisih_diskon_perproduct = $selisih_diskon/$total_produk;
						$selisih_diskon_perproduct = number_format($selisih_diskon_perproduct, 2);
					}
					
					$discount_detail_total = 0;
					
					if(!empty($dt['discount_detail'])){
						
						$no = 0;
						foreach($dt['discount_detail'] as $product_id => $dt_diskon){
							$no++;
							$discount_detail_total += ($dt_diskon['total_discount']+$selisih_diskon_perproduct);
							
							
							if(empty($data_diskon_awal[$product_id])){
								$data_diskon_awal[$product_id] = array(
									//'item'	=> 0,
									//'billing'	=> 0
								);
							}
							if(empty($data_balancing_diskon[$product_id])){
								$data_balancing_diskon[$product_id] = array(
									//'item'	=> 0,
									//'billing'	=> 0
								);
							}
							
							if(empty($data_diskon_awal[$product_id][$dt['billing_date']])){
								$data_diskon_awal[$product_id][$dt['billing_date']] = array(
									'item'	=> 0,
									'billing'	=> 0
								);
							}
							if(empty($data_balancing_diskon[$product_id][$dt['billing_date']])){
								$data_balancing_diskon[$product_id][$dt['billing_date']] = array(
									'item'	=> 0,
									'billing'	=> 0
								);
							}
							
							
							if($dt['discount_perbilling'] == 1){
								$data_diskon_awal[$product_id][$dt['billing_date']]['billing'] += $dt_diskon['total_discount'];
							}else{
								$data_diskon_awal[$product_id][$dt['billing_date']]['item'] += $dt_diskon['total_discount'];
							}
							
							if($dt['discount_perbilling'] == 1){
								$data_balancing_diskon[$product_id][$dt['billing_date']]['billing'] += ($dt_diskon['total_discount']+$selisih_diskon_perproduct);
							}else{
								$data_balancing_diskon[$product_id][$dt['billing_date']]['item'] += ($dt_diskon['total_discount']+$selisih_diskon_perproduct);
							}
							
							$balancing_discount_billing[$billing_id]['discount_detail'][$product_id]['total_discount_balance'] = ($dt_diskon['total_discount']+$selisih_diskon_perproduct);
							
							if($no == count($dt['discount_detail'])){
								if($discount_detail_total != $dt['discount_total']){
									$selisih_akhir = $dt['discount_total'] - $discount_detail_total;
									
									if($dt['discount_perbilling'] == 1){
										$data_balancing_diskon[$product_id][$dt['billing_date']]['billing'] += $selisih_akhir;
									}else{
										$data_balancing_diskon[$product_id][$dt['billing_date']]['item'] += $selisih_akhir;
									}
									
									$balancing_discount_billing[$billing_id]['discount_detail'][$product_id]['total_discount_balance'] += $selisih_akhir;
									
								}
							}
							
						}
						
					}
				}
				
				//SET SELISIH DISKON
				if(!empty($balancing_discount_billing)){
					foreach($balancing_discount_billing as $billing_id => $dt){
						if(!empty($dt['discount_detail'])){
							foreach($dt['discount_detail'] as $product_id => $dt_diskon){
								
								$sub_total_balance = $dt_diskon['total_billing'] - $dt_diskon['total_discount_balance'];
								$sub_total_balance += $dt_diskon['tax_total'];
								$sub_total_balance += $dt_diskon['service_total'];
								
								$balancing_discount_billing[$billing_id]['discount_detail'][$product_id]['sub_total_balance'] = $sub_total_balance;
								
								$sub_total_selisih = $dt_diskon['sub_total'] - $sub_total_balance;
								$balancing_discount_billing[$billing_id]['discount_detail'][$product_id]['discount_balance'] = $sub_total_selisih;
								
								if(empty($data_selisih_diskon[$product_id])){
									$data_selisih_diskon[$product_id] = array();
								}
								if(empty($data_selisih_diskon[$product_id][$dt['billing_date']])){
									$data_selisih_diskon[$product_id][$dt['billing_date']] = 0;
								}
								
								$data_selisih_diskon[$product_id][$dt['billing_date']] += $sub_total_selisih;
								
								if(empty($data_selisih_diskon_payment[$product_id])){
									$data_selisih_diskon_payment[$product_id] = array();
								}
								if(empty($data_selisih_diskon_payment[$product_id][$dt['billing_date']])){
									$data_selisih_diskon_payment[$product_id][$dt['billing_date']] = array();
								}
								
								if(empty($data_selisih_diskon_payment[$product_id][$dt['billing_date']][$dt['payment_id']])){
									$data_selisih_diskon_payment[$product_id][$dt['billing_date']][$dt['payment_id']] = 0;
								}
								
								//echo $product_id.' -> '.$dt['payment_id'].' <br/>';
								$data_selisih_diskon_payment[$product_id][$dt['billing_date']][$dt['payment_id']] += $sub_total_selisih;
								
								if(empty($data_selisih_diskon_bank[$product_id])){
									$data_selisih_diskon_bank[$product_id] = array();
								}
								if(empty($data_selisih_diskon_bank[$product_id][$dt['billing_date']])){
									$data_selisih_diskon_bank[$product_id][$dt['billing_date']] = array();
								}
								
								if(empty($data_selisih_diskon_bank[$product_id][$dt['billing_date']][$dt['bank_id']])){
									$data_selisih_diskon_bank[$product_id][$dt['billing_date']][$dt['bank_id']] = 0;
								}
								
								//echo $product_id.' -> '.$dt['bank_id'].' <br/>';
								$data_selisih_diskon_bank[$product_id][$dt['billing_date']][$dt['bank_id']] += $sub_total_selisih;
								
							}
						}
					}
				}
			}
			
			//GROUP PAYMENT
			$summary_payment_group = array();
			if(!empty($summary_payment)){
				foreach($summary_payment as $dt){
					
					//BALANCING DISKON
					if(!empty($data_diskon_awal[$dt['product_id']][$billing_date])){
						$dt['discount_total'] -= $data_diskon_awal[$dt['product_id']][$billing_date]['item'];
						$dt['discount_billing_total'] -= $data_diskon_awal[$dt['product_id']][$billing_date]['billing'];
					}
					
					if(!empty($data_balancing_diskon[$dt['product_id']][$billing_date])){
						$dt['discount_total'] += $data_balancing_diskon[$dt['product_id']][$billing_date]['item'];
						$dt['discount_billing_total'] += $data_balancing_diskon[$dt['product_id']][$billing_date]['billing'];
					}
					
					if(!empty($data_selisih_diskon[$dt['product_id']][$billing_date])){
						$dt['sub_total'] -= $data_selisih_diskon[$dt['product_id']][$billing_date];
						$dt['grand_total'] -= $data_selisih_diskon[$dt['product_id']][$billing_date];
					}
					
					//BALANCING DISKON PAYMENT
					if(!empty($data_selisih_diskon_payment[$dt['product_id']][$billing_date])){
						foreach($data_selisih_diskon_payment[$dt['product_id']][$billing_date] as $payment_id => $dtP){
							if(!empty($dt['payment_'.$payment_id])){
								$dt['payment_'.$payment_id] -= $dtP;
							}
						}
					}
					
					//BALANCING DISKON BANK
					if(!empty($data_selisih_diskon_bank[$dt['product_id']][$billing_date])){
						foreach($data_selisih_diskon_bank[$dt['product_id']][$billing_date] as $bank_id => $dtP){
							if(!empty($dt['bank_'.$bank_id])){
								$dt['bank_'.$bank_id] -= $dtP;
							}
						}
					}
					
					
					//KONVERSI PEMBULATAN
					$selisih_pembulatan = 0;
					if(!empty($pembulatan_awal_product[$dt['product_id']][$billing_date])){
						$selisih_pembulatan -= $pembulatan_awal_product[$dt['product_id']][$billing_date];
						$dt['grand_total'] -= $pembulatan_awal_product[$dt['product_id']][$billing_date];
					}
					
					
					if(!empty($konversi_pembulatan_product[$dt['product_id']][$billing_date])){
						$dt['total_pembulatan'] = $konversi_pembulatan_product[$dt['product_id']][$billing_date]['total_pembulatan'];
						$dt['grand_total'] += $konversi_pembulatan_product[$dt['product_id']][$billing_date]['total_pembulatan'];
						$selisih_pembulatan += $konversi_pembulatan_product[$dt['product_id']][$billing_date]['total_pembulatan'];
					}
					
					if(!empty($dt['compliment_total'])){
						//$dt['compliment_total'] += $selisih_pembulatan;
					}
					
					//KONVERSI PEMBULATAN PAYMENT
					if(!empty($pembulatan_awal_product_payment[$dt['product_id']][$billing_date])){
						foreach($pembulatan_awal_product_payment[$dt['product_id']][$billing_date] as $payment_id => $dtP){
							if(!empty($dt['payment_'.$payment_id])){
								$dt['payment_'.$payment_id] -= $dtP;
							}
						}
					}
					
					if(!empty($konversi_pembulatan_product_payment[$dt['product_id']][$billing_date])){
						foreach($konversi_pembulatan_product_payment[$dt['product_id']][$billing_date] as $payment_id => $dtP){
							if(!empty($dt['payment_'.$payment_id])){
								$dt['payment_'.$payment_id] += $dtP;
							}
						}
					}
					
					
					//KONVERSI PEMBULATAN BANK
					if(!empty($pembulatan_awal_product_bank[$dt['product_id']][$billing_date])){
						foreach($pembulatan_awal_product_bank[$dt['product_id']][$billing_date] as $bank_id => $dtP){
							if(!empty($dt['bank_'.$bank_id])){
								$dt['bank_'.$bank_id] -= $dtP;
							}
						}
					}
					
					if(!empty($konversi_pembulatan_product_bank[$dt['product_id']][$billing_date])){
						foreach($konversi_pembulatan_product_bank[$dt['product_id']][$billing_date] as $bank_id => $dtP){
							if(!empty($dt['bank_'.$bank_id])){
								$dt['bank_'.$bank_id] += $dtP;
							}
						}
					}
					
					if(empty($summary_payment_group[$dt['payment_id']])){
						$summary_payment_group[$dt['payment_id']] = array();
					}
					
					$summary_payment_group[$dt['payment_id']][] = $dt;
				}
			}
			
			//echo '<pre>';
			//print_r($data_post['summary_data']);
			//echo '<pre>';
			//print_r($summary_payment);
			//die();
			
			$jumlah_uang_total = printer_command_align_right(($get_data['jumlah_uang_koin']+$get_data['jumlah_uang_kertas']), $max_number_3);
			
			$menu_sales = printer_command_align_right(priceFormat($data_post['summary_data']['total_billing']), $max_number_3);
			$disc_per_item = printer_command_align_right(priceFormat($data_post['summary_data']['total_discount_item']), $max_number_3);
			
			$menu_net_sales_count = ($data_post['summary_data']['total_billing']-$data_post['summary_data']['total_discount_item']);
			//$menu_net_sales = printer_command_align_right(priceFormat($menu_net_sales_count), $max_number_3);
			$disc_per_billing = printer_command_align_right(priceFormat($data_post['summary_data']['total_discount_billing']), $max_number_3);
			
			//$total_net_sales_count = ($menu_net_sales_count-$data_post['summary_data']['total_discount_item']);
			$total_net_sales_count = $menu_net_sales_count - $data_post['summary_data']['total_discount_billing'];
			$total_net_sales = printer_command_align_right(priceFormat($data_post['summary_data']['net_sales']), $max_number_3);
			
			$service_total = printer_command_align_right(priceFormat($data_post['summary_data']['service_total']), $max_number_3);
			$tax_total = printer_command_align_right(priceFormat($data_post['summary_data']['tax_total']), $max_number_3);
			$total_pembulatan = printer_command_align_right(priceFormat($data_post['summary_data']['total_pembulatan']), $max_number_3);
			$compliment_total = printer_command_align_right(priceFormat($data_post['summary_data']['compliment_total']), $max_number_3);
			$grand_total = printer_command_align_right(priceFormat($data_post['summary_data']['grand_total']), $max_number_3);
			
			$total_of_billing = printer_command_align_right(priceFormat($data_post['summary_data']['total_of_billing']), $max_number_3);
			$total_of_guest = printer_command_align_right(priceFormat($data_post['summary_data']['total_of_guest']), $max_number_3);
			$total_dp = printer_command_align_right(priceFormat($data_post['summary_data']['total_dp']), $max_number_3);
			
			$all_summary_data = "[align=0][size=1][tab]SALES SUMMARY[tab]\n";
			$all_summary_data .= "[size=0]";
			$all_summary_data .= "[align=0][tab]QTY BILLING[tab]".$total_of_billing."\n"; 
			$all_summary_data .= "[align=0][tab]TOTAL GUEST[tab]".$total_of_guest."\n"; 
			$all_summary_data .= "[align=0][tab]MENU SALES[tab]".$menu_sales."\n"; 

			if($data_post['diskon_sebelum_pajak_service'] == 1){
				$all_summary_data .= "[align=0][tab]DISC/ITEM[tab]".$disc_per_item."\n"; 
				$all_summary_data .= "[align=0][tab]DISC/BILLING[tab]".$disc_per_billing."\n"; 
				if(!empty($data_post['summary_data']['compliment_total'])){
					$all_summary_data .= "[align=0][tab]COMPLIMENT[tab]".$compliment_total."\n"; 
				}
				$all_summary_data .= "[align=0][tab]NET SALES[tab]".$total_net_sales."\n";
			}
			
			$all_summary_data .= "[align=0][tab]TAX[tab]".$tax_total."\n"; 
			$all_summary_data .= "[align=0][tab]SERVICE[tab]".$service_total."\n"; 
			
			if($data_post['diskon_sebelum_pajak_service'] == 0){
				$all_summary_data .= "[align=0][tab]DISC/ITEM[tab]".$disc_per_item."\n"; 
				$all_summary_data .= "[align=0][tab]DISC/BILLING[tab]".$disc_per_billing."\n"; 
				if(!empty($data_post['summary_data']['compliment_total'])){
					$all_summary_data .= "[align=0][tab]COMPLIMENT[tab]".$compliment_total."\n"; 
				}
				$all_summary_data .= "[align=0][tab]NET SALES[tab]".$total_net_sales."\n";
			}
			
			$all_summary_data .= "[align=0][tab]PEMBULATAN[tab]".$total_pembulatan."\n"; 
			$all_summary_data .= "[align=0][tab]TOTAL SALES[tab]".$grand_total; 
			
			//sort index
			asort($summary_payment_group);
			
			$all_payment_data = '';
			if(!empty($summary_payment_group)){
				foreach($summary_payment_group as $key => $dt_detail){
					
					$no_payment = 0;
					if(!empty($dt_detail)){
						foreach($dt_detail as $dt){
							
							$no_payment++;
							$payment_name = ucwords(str_replace("_"," ",$dt['payment_name']));
							$data_name = ucwords(str_replace("_"," ",$dt['bank_name']));
							
							//update-2001.200
							if(strlen($data_name) > $max_text){
								$data_name = substr($data_name,0,$max_text);
							}
							
							if(empty($all_payment_data)){
								$all_payment_data = "[align=0][size=1][tab]PAYMENT SUMMARY[tab]\n";
								$all_payment_data .= "[size=0]";
								if(!empty($data_post['summary_data']['total_dp'])){
									$all_payment_data .= "[align=0][tab]DOWN-PAYMENT[tab]".$total_dp."\n"; 
								}
							}
							
							$value_show = printer_command_align_right(priceFormat($dt['payment_'.$key]), $max_number_3);
							
							if($payment_name == 'CASH'){
								$all_payment_data .= "[align=0][tab]".$payment_name."[tab]".$value_show."\n"; 
							}else{
								if($no_payment == 1){
									//$all_payment_data .= $payment_name."\n";
									$all_payment_data .= "[align=0][tab]".$payment_name."[tab] \n"; 
								}
								$all_payment_data .= "[align=0][tab] *".$data_name."[tab]".$value_show."\n";
							}
							
						}
					}
					
					
				}
			}
			
			
			$print_attr = array(
				"{tipe_openclose}"		=> 'Close',
				"{user}"				=> $new_data['kasir_user']['value'],
				"{tanggal_shift}"		=> date("d/m/Y", strtotime($new_data['tanggal_shift']['value'])),
				"{jam_shift}"			=> $new_data['jam_shift']['value'],
				"{tipe_shift}"			=> strtoupper($new_data['tipe_shift']['value']),
				"{shift_kasir}"			=> $new_data['kasir_user']['value'],
				"{shift_on}"			=> $new_data['user_shift']['value'],
				"{uang_kertas_data}"	=> $uang_kertas_data,
				"{uang_koin_data}"		=> $uang_koin_data,
				"{jumlah_uang_kertas}"	=> $new_data['jumlah_uang_kertas']['value'],
				"{jumlah_uang_koin}"	=> $new_data['jumlah_uang_koin']['value'],
				"{jumlah_uang_total}"	=> $jumlah_uang_total,
				"{spv_user}"			=> $new_data['spv_user']['value'],
				"{summary_data}"			=> $all_summary_data,
				"{payment_data}"			=> $all_payment_data."\n"
			);
			
			$print_content_cashierReceipt = strtr($cashierReceipt_openclose_layout, $print_attr);
			
			//update-2001.002
			if(!empty($test)){
				//echo '<pre>';
				print_r($print_content_cashierReceipt);
				die();
			}
			
			$print_content_cashierReceipt = replace_to_printer_command($print_content_cashierReceipt, $printer_type_cashier, $printer_pin_cashierReceipt);
			
			$r = array('success' => false, 'info' => '', 'print' => array());
			
			$r['print'][] = $print_content_cashierReceipt;
			//DIRECT PRINT USING PHP - CASHIER PRINTER				
			$is_print_error = false;
			
			try {
				$ph = printer_open($printer_ip_cashierReceipt);
			} catch (Exception $e) {
				$ph = false;
			}
			
			//$ph = @printer_open($printer_ip_cashierReceipt);
			
			if($ph)
			{	
				printer_start_doc($ph, "CLOSE CASHIER");
				printer_start_page($ph);
				printer_set_option($ph, PRINTER_MODE, "RAW");
				printer_write($ph, $print_content_cashierReceipt);
				printer_end_page($ph);
				printer_end_doc($ph);
				printer_close($ph);
				$r['success'] = true;
				
			}else{
				$is_print_error = true;
			}
			
			if($is_print_error){					
				$r['info'] .= 'Communication with Printer Cashier Failed!<br/>';
			}
			
			
		}else{
			$r = array('success' => false, 'info' => 'Data not Found!');
		}
		
		echo json_encode($r);
		die();
		
	}
		
	
}