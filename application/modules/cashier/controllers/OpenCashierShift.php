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
			$r = array('success' => false, 'info' => 'User Session Expired, Please Re-Login!');
			echo json_encode($r);
			die();
		}
		
		$get_id = $this->input->post('id', true);
		$spv_user = $this->input->post('spv_user', true);
		$tanggal_shift = $this->input->post('tanggal_shift', true);
		$jam_shift = $this->input->post('jam_shift', true);
		$user_shift = $this->input->post('user_shift', true);
		
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
		if(empty($get_data['id'])){
			//NEW INSERT
			$kasir_user = $session_user;
			$tanggal_shift = date("Y-m-d",strtotime($tanggal_shift));
			
			$insert_openShift = array(
				'kasir_user'	=> $kasir_user,
				'spv_user'	=> $spv_user,
				'tipe_shift'	=> $tipe_shift,
				'tanggal_shift'	=> $tanggal_shift,
				'jam_shift'	=> $jam_shift,
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
			
			if($save_openShift){
				$r = array('success' => true, 'openShiftData' => $get_data);
			}else{
				$r = array('success' => false, 'Save Open Cashier (Shift) Failed!');
			}
			
		}else{
			
			$insert_openShift = array(
				'kasir_user'	=> $session_user,
				'spv_user'	=> $spv_user,
				'tipe_shift'	=> $tipe_shift,
				'tanggal_shift'	=> $tanggal_shift,
				'jam_shift'	=> $jam_shift,
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
			
			$save_openShift = $this->db->update($this->table, $insert_openShift, 'id = '.$get_data['id']);
			$r = array('success' => true, 'openShiftData' => $get_data);
		}
		
		die(json_encode($r));
	}
		
	public function loadOpenShift($is_return = false, $id_open = ''){
				
		$this->table = $this->prefix.'open_close_shift';
		$this->prefix2 = config_item('db_prefix'); //pos_
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		
		$user_shift = $this->input->post('user_shift', true);
		
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'User Session Expired, Please Re-Login!');
			echo json_encode($r);
			die();
		}
		
		$openShiftData = array();
		$get_opt = get_option_value(array('role_id_kasir'));
		$role_id_kasir = 0;
		
		if(!empty($get_opt['role_id_kasir'])){
			$role_id_kasir = $get_opt['role_id_kasir'];
		}
		
		//get open close data
		$tanggal_shift = date("d-m-Y");
		$jam_shift = date("H:i");
		$get_date = date("Y-m-d");
		$this->db->select('a.*');
		$this->db->from($this->table.' as a');
		$this->db->join($this->prefix2.'users as b', 'b.user_username = a.kasir_user'); 
		//$this->db->join($this->prefix2.'users as b', 'b.user_username = a.kasir_user','LEFT'); //TESTING
		//$this->db->where("a.kasir_user", $session_user);
		if(!empty($role_id_kasir)){
			$this->db->where("b.role_id IN (".$role_id_kasir.")");
		}else{
			$this->db->where("b.role_id", 0);
		}
		$this->db->where("a.tanggal_shift", $get_date);
		$this->db->where("a.tipe_shift", 'open');
		$this->db->order_by("a.id", 'DESC');
		
		if(!empty($id_open)){
			$this->db->where("a.id", $id_open);
		}
		
		if(!empty($user_shift)){
			$this->db->where("a.user_shift", $user_shift);
		}
		
		$get_openShift = $this->db->get();
		
		if($get_openShift->num_rows() > 0){
			$openShiftData = $get_openShift->row_array();
		}else{
			
			
			$openShiftData = array(
				'id'	=> '',
				'spv_user'	=> '',
				'kasir_user'	=> $session_user,
				'tipe_shift'	=> 'open',
				'tanggal_shift'	=> $tanggal_shift,
				'jam_shift'	=> $jam_shift,
				'user_shift'	=> 1,
				'uang_kertas_100000'=> 0,
				'uang_kertas_50000'	=> 0,
				'uang_kertas_20000'	=> 0,
				'uang_kertas_10000'	=> 0,
				'uang_kertas_5000'	=> 0,
				'uang_kertas_2000'	=> 0,
				'uang_kertas_1000'	=> 0,
				'jumlah_uang_kertas'	=> 0,
				'uang_koin_1000'	=> 0,
				'uang_koin_500'	=> 0,
				'uang_koin_200'	=> 0,
				'uang_koin_100'	=> 0,
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
			$r = array('success' => false, 'info' => 'User Session Expired, Please Re-Login!');
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
			
			$printer_pin_cashierReceipt = 'PIN 42';
			if(!empty($get_opt['printer_pin_cashierReceipt_'.$ip_addr])){
				$printer_pin_cashierReceipt = $get_opt['printer_pin_cashierReceipt_'.$ip_addr];
			}
			
			//trim prod name
			$max_text = 18;
			
			if($printer_pin_cashierReceipt == 'PIN 32'){
				$max_text -= 7;
			}
			if($printer_pin_cashierReceipt == 'PIN 40'){
				$max_text -= 2;
			}
			if($printer_pin_cashierReceipt == 'PIN 48'){
				$max_text += 6;
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
					$new_data_kertas[$key] = array("name" => '', "value" => '');
					$new_data_kertas[$key]['name'] = $data_name;
					$new_data_kertas[$key]['value'] = $dt;
					
					
					$value_show = printer_command_align_right($dt, 9);
					
					if($printer_pin_cashierReceipt == 'PIN 32'){
						$value_show = printer_command_align_right($dt, 8);
					}
					
					if(empty($uang_kertas_data)){
						$total_uang_kertas = $get_data['jumlah_uang_kertas'];
						$total_uang_kertas = printer_command_align_right($total_uang_kertas, 9);
						$uang_kertas_data = "[size=2][align=1]UANG KERTAS[tab]".$total_uang_kertas."\n";
					}
					
					$uang_kertas_data .= "[size=1][align=0]".$data_name."[tab]X ".$value_show."\n"; 
					
				}else
				if(strstr($key, 'uang_koin_')){
					$new_data_koin[$key] = array("name" => '', "value" => '');
					$new_data_koin[$key]['name'] = $data_name;
					$new_data_koin[$key]['value'] = $dt;
					
					$value_show = printer_command_align_right($dt, 9);
					
					if($printer_pin_cashierReceipt == 'PIN 32'){
						$value_show = printer_command_align_right($dt, 8);
					}
					
					if(empty($uang_koin_data)){
						$total_uang_koin = $get_data['jumlah_uang_koin'];
						$total_uang_koin = printer_command_align_right($total_uang_koin, 9);
						$uang_koin_data = "[size=2][align=1]UANG KOIN[tab]".$total_uang_koin."\n";
					}
					
					$uang_koin_data .= "\n"."[size=1][align=0]".$data_name."[tab]X ".$value_show;
					
				}else{
					$new_data[$key] = array("name" => '', "value" => '');
					$new_data[$key]['name'] = $data_name;
					
					$new_val = $dt;
					if($key == 'user_shift'){
						
						$new_val = 'Morning Shift';
						if($dt == 2){
							$new_val = 'Evening Shift';
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
			
			
			$print_attr = array(
				"{tipe_openclose}"		=> 'Open Cashier',
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
			//print_r($print_content_cashierReceipt);
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