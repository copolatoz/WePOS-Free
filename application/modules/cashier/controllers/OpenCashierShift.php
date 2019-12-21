<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class OpenCashierShift extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_opencashiershift', 'm');
	}
	
	public function saveOpenShift(){
		
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
		
		if(empty($tanggal_shift)){
			$r = array('success' => false, 'info' => 'Set Tanggal Shift!');
			echo json_encode($r);
			die();
		}
		
		$tanggal_shift = date("Y-m-d",strtotime($tanggal_shift));
		
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
		$tipe_shift = 'open';
			
		//LOAD USER OPEN SHIFT
		$get_data = $this->loadOpenShift(true); //array
		if(!empty($get_data['id'])){
			$update_openShift = array(
				'kasir_user'	=> $session_user,
				'spv_user'		=> $spv_user,
				'tipe_shift'	=> $tipe_shift,
				'tanggal_shift'	=> $tanggal_shift,
				'jam_shift'		=> $jam_shift,
				'user_shift'	=> $user_shift,
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
			
			$save_openShift = $this->db->update($this->table, $update_openShift, 'id = '.$get_data['id']);
			
			$this->cekShiftLog();
			
			$r = array('success' => true, 'openShiftData' => $get_data, 'nama_shift' => $nama_shift);
			
		}else
		{
			//Cek jika shift < belum di close = warning
			if($user_shift > 1){
				$this->cekCloseShift(true);
			}
			
			$insert_openShift = array(
				'kasir_user'	=> $session_user,
				'spv_user'		=> $spv_user,
				'tipe_shift'	=> $tipe_shift,
				'tanggal_shift'	=> $tanggal_shift,
				'jam_shift'		=> $jam_shift,
				'user_shift'	=> $user_shift,
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
			
			$save_openShift = $this->db->insert($this->table, $insert_openShift);
			$get_data['id'] = $this->db->insert_id();
			
			$this->cekShiftLog();
			
			if($save_openShift){
				$r = array('success' => true, 'openShiftData' => $get_data, 'id = '.$get_data['id'], 'nama_shift' => $nama_shift);
			}else{
				$r = array('success' => false, 'Save Open Cashier: '.$nama_shift.' Gagal!', 'nama_shift' => $nama_shift);
			}
		}
		
		die(json_encode($r));
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
		
		$tanggal_shift_post = $tanggal_shift;
		$mk_shift_post = strtotime($tanggal_shift_post." ".$jam_shift);
		$tanggal_shift = date("Y-m-d",strtotime($tanggal_shift));
		
		//CEK JAM OPEN SHIFT PALING KECIL
		$this->db->select('a.*');
		$this->db->from($this->table.' as a');
		$this->db->where("a.tanggal_shift", $tanggal_shift);
		$this->db->where("a.tipe_shift", $tipe_shift);
		$this->db->where("a.user_shift = ".$user_shift);
		$this->db->order_by("a.id", 'DESC');
		$get_openCloseShift = $this->db->get();
		if($get_openCloseShift->num_rows() > 0){
			foreach($get_openCloseShift->result_array() as $dt){
				
				$mk_shift_cek = strtotime($tanggal_shift_post." ".$dt['jam_shift']);
				
				if($mk_shift_cek <= $mk_shift_post){
					$mk_shift_post = $mk_shift_cek;
				}
				
			}
		}
		
		$jam_shift = date("H:i", $mk_shift_post);
		$tanggal_jam_shift = date("Y-m-d H:i", $mk_shift_post).":00";
		
		//CEK SHIFT LOG
		$this->db->select('a.*');
		$this->db->from($this->prefix2.'shift_log as a');
		$this->db->where("a.tanggal_shift", $tanggal_shift);
		//$this->db->where("a.tipe_shift", 'open');
		$this->db->where("a.user_shift = ".$user_shift);
		$this->db->order_by("a.id", 'DESC');
		
		$get_openShiftLog = $this->db->get();
		if($get_openShiftLog->num_rows() > 0){
			
			$get_dataShiftLog = $get_openShiftLog->row();
			
			//shift_log
			$update_shift_log = array(
				'jam_shift_start'	=> $jam_shift,
				'tanggal_jam_start'	=> $tanggal_jam_shift,
				'updated'			=> $date_now,
				'updatedby'			=> $session_user
			);
			
			$save_shift_log = $this->db->update($this->prefix.'shift_log', $update_shift_log, "user_shift = ".$user_shift." AND tanggal_shift = '".$tanggal_shift."'");
			
		}else{
			
			//other close
			$close_shift_log = array("tipe_shift" => "close");
			$save_shift_log = $this->db->update($this->prefix.'shift_log', $close_shift_log, "tanggal_shift = '".$tanggal_shift."'");
			
			
			//shift_log
			$insert_shift_log = array(
				'tipe_shift'		=> $tipe_shift,
				'tanggal_shift'		=> $tanggal_shift,
				'jam_shift_start'	=> $jam_shift,
				'tanggal_jam_start'	=> $tanggal_jam_start,
				'user_shift'	=> $user_shift,
				'created'		=>	$date_now,
				'createdby'		=>	$session_user,
				'updated'		=>	$date_now,
				'updatedby'		=>	$session_user
			);
			$save_shift_log = $this->db->insert($this->prefix.'shift_log', $insert_shift_log);
			
			//shift_active
			$data_option = array("shift_active" => $user_shift);
			$update_option = update_option($data_option);
			
		}
	}
	
	public function cekCloseShift(){
			
		$this->table = $this->prefix.'open_close_shift';
		$this->prefix2 = config_item('db_prefix2'); //pos_
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		
		$user_shift = $this->input->post('user_shift', true);
		$jam_shift_start = $this->input->post('jam_shift_start', true);
		$tanggal_shift = $this->input->post('tanggal_shift', true);
		
		$closeShiftData = array();
		$tanggal_shift = date("Y-m-d",strtotime($tanggal_shift));
		
		$this->db->select('a.*');
		$this->db->from($this->prefix2.'shift_log as a');
		$this->db->where("a.tanggal_shift", $tanggal_shift);
		$this->db->where("a.tipe_shift", 'close');
		
		if(!empty($user_shift)){
			$user_shift_before = $user_shift-1;
			$this->db->where("a.user_shift = ".$user_shift_before);
		}
		
		$this->db->order_by("a.id", 'DESC');
		
		$get_closeShift = $this->db->get();
		if($get_closeShift->num_rows() == 0){
			
			$this->db->select('a.*');
			$this->db->from($this->prefix2.'shift as a');
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
	
	public function loadOpenShift($is_return = false, $id_open = ''){
				
		$this->table = $this->prefix.'open_close_shift';
		$this->prefix2 = config_item('db_prefix2'); //pos_
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		
		$user_shift = $this->input->post('user_shift', true);
		$jam_shift_start = $this->input->post('jam_shift_start', true);
		$tanggal_shift = $this->input->post('tanggal_shift', true);
		
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$openShiftData = array();
		$get_opt = get_option_value(array('role_id_kasir'));
		$role_id_kasir = 0;
		
		if(!empty($get_opt['role_id_kasir'])){
			$role_id_kasir = $get_opt['role_id_kasir'];
		}else{
			$r = array('success' => false, 'info' => 'Harus Role: Cashier agar bisa menggunakan module ini!');
			echo json_encode($r);
			die();
		}
		
		//get open close data
		//$tanggal_shift = date("d-m-Y");
		$jam_shift = date("H:i");
		$get_date = date("Y-m-d", strtotime($tanggal_shift));
		
		$this->db->select('a.*, b.nama_shift');
		$this->db->from($this->table.' as a');
		$this->db->join($this->prefix2.'shift as b',"b.id = a.user_shift");
		
		if(!empty($id_open)){
			$this->db->where("a.id", $id_open);
		}else{
			
			$this->db->where("a.tanggal_shift", $get_date);
			$this->db->where("a.tipe_shift", 'open');
			
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
		
		$get_openShift = $this->db->get();
		
		if($get_openShift->num_rows() > 0){
			$openShiftData = $get_openShift->row_array();
		}else{
			
			if(empty($jam_shift_start)){
				$jam_shift_start = $jam_shift;
			}
			
			$openShiftData = array(
				'id'			=> '',
				'spv_user'		=> '',
				'kasir_user'	=> $session_user,
				'tipe_shift'	=> 'open',
				'tanggal_shift'	=> $tanggal_shift,
				'jam_shift'		=> $jam_shift_start,
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
			
			return $openShiftData;
		}
		
		$r = array('success' => true, 'openShiftData' => $openShiftData);
		
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
		
		$id = $this->input->post('id', true);	
		
		//LOAD USER OPEN SHIFT
		$get_data = $this->loadOpenShift(true, $id); //array
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
					
					$info_data .= "[size=0][align=0]".$data_name."[tab]".$new_val."\n";
					
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
			
			
			$print_attr = array(
				"{tipe_openclose}"		=> 'Open',
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
				"{spv_user}"			=> $new_data['spv_user']['value'],
				"{summary_data}"		=> '',
				"{payment_data}"		=> ''
			);
			
			$print_content_cashierReceipt = strtr($cashierReceipt_openclose_layout, $print_attr);
			
			//echo '<pre>';
			//print_r($get_data);
			//die();
			
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
				printer_start_doc($ph, "OPEN CASHIER");
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