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
		
		$tipe_shift = 'close';
			
		//LOAD USER OPEN SHIFT
		$get_data = $this->loadCloseShift(true); //array
		if(empty($get_data['id'])){
			//NEW INSERT
			$kasir_user = $session_user;
			$tanggal_shift = date("Y-m-d",strtotime($tanggal_shift));
			
			$insert_closeShift = array(
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
			
			$save_closeShift = $this->db->insert($this->table, $insert_closeShift);
			$get_data['id'] = $this->db->insert_id();
			
			if($save_closeShift){
				$r = array('success' => true, 'closeShiftData' => $get_data);
			}else{
				$r = array('success' => false, 'Save Close Cashier (Shift) Failed!');
			}
			
		}else{
			
			$insert_closeShift = array(
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
			
			$save_closeShift = $this->db->update($this->table, $insert_closeShift, 'id = '.$get_data['id']);
			$r = array('success' => true, 'closeShiftData' => $get_data);
		}
		
		die(json_encode($r));
	}
		
	public function loadCloseShift($is_return = false, $id_close = ''){
				
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
		
		$closeShiftData = array();
		$get_opt = get_option_value(array('role_id_kasir'));
		$role_id_kasir = 0;
		
		if(!empty($get_opt['role_id_kasir'])){
			$role_id_kasir = $get_opt['role_id_kasir'];
		}
		
		$user_shift = $this->input->post('user_shift', true);
		
		//get close close data
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
		$this->db->where("a.tipe_shift", 'close');
		$this->db->order_by("a.id", 'DESC');
		
		if(!empty($id_close)){
			$this->db->where("a.id", $id_close);
		}
		
		if(!empty($user_shift)){
			$this->db->where("a.user_shift", $user_shift);
		}
		
		$get_closeShift = $this->db->get();
		
		if($get_closeShift->num_rows() > 0){
			$closeShiftData = $get_closeShift->row_array();
		}else{
			
			
			$closeShiftData = array(
				'id'	=> '',
				'spv_user'	=> '',
				'kasir_user'	=> $session_user,
				'tipe_shift'	=> 'close',
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
			$r = array('success' => false, 'info' => 'User Session Expired, Please Re-Login!');
			echo json_encode($r);
			die();
		}
		
		$id = $this->input->post('id', true);	
		
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
			$max_text = 18;
			
			if($printer_pin_cashierReceipt == '32 CHAR'){
				$max_text -= 7;
			}
			if($printer_pin_cashierReceipt == '40 CHAR'){
				$max_text -= 2;
			}
			if($printer_pin_cashierReceipt == '48 CHAR'){
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
					
					if($printer_pin_cashierReceipt == '32 CHAR'){
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
					
					if($printer_pin_cashierReceipt == '32 CHAR'){
						$value_show = printer_command_align_right($dt, 8);
					}
					
					if(empty($uang_koin_data)){
						$total_uang_koin = $get_data['jumlah_uang_koin'];
						$total_uang_koin = printer_command_align_right($total_uang_koin, 9);
						$uang_koin_data = "[size=2][align=1]UANG KOIN[tab]".$total_uang_koin;
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
			
			//TOTAL BILLING - SSR
			$data_post = array();
			$this->table_billing = $this->prefix.'billing';
			$this->table_billing_detail = $this->prefix.'billing_detail';
		
			$get_opt = get_option_value(array('report_place_default','diskon_sebelum_pajak_service','cashier_max_pembulatan','cashier_pembulatan_keatas','pembulatan_dinamis'));
			if(!empty($get_opt['report_place_default'])){
				$data_post['report_place_default'] = $get_opt['report_place_default'];
			}
			
			if(!empty($get_opt['diskon_sebelum_pajak_service'])){
				$data_post['diskon_sebelum_pajak_service'] = $get_opt['diskon_sebelum_pajak_service'];
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
			
			
			$get_date_from = date("d-m-Y H:i:s", strtotime($new_data['tanggal_shift']['value']))." ".$new_data['jam_shift']['value'].":00";
			$get_hour = (int) date("H", strtotime($get_date_from));
			
			$date_from = date("d-m-Y", strtotime($new_data['tanggal_shift']['value']))." ".$new_data['jam_shift']['value'];
			$date_till = date("d-m-Y", strtotime($new_data['tanggal_shift']['value']))." ".$new_data['jam_shift']['value'];
			
			if($get_hour <= 6){
				$date_from = (int) date("d-m-Y", strtotime($get_date_from)-ONE_DAY_UNIX);
				$date_till = (int) date("d-m-Y", strtotime($get_date_from)-ONE_DAY_UNIX);
			}
			
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till = date("Y-m-d",strtotime($date_till));
			$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
			
			$add_where = "(a.payment_date >= '".$qdate_from." 07:00:01' AND a.payment_date <= '".$qdate_till_max." 06:00:00')";
			
			$this->db->select("a.*, a.id as billing_id, a.updated as billing_date, d.payment_type_name, e.bank_name");
			$this->db->from($this->table_billing." as a");
			$this->db->join($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT');
			$this->db->join($this->prefix.'bank as e','e.id = a.bank_id','LEFT');
			$this->db->where("a.billing_status", 'paid');
			$this->db->where("a.is_deleted", 0);
			$this->db->where($add_where);
			$this->db->order_by("payment_date","ASC");
			
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
			
			
			$all_bil_id = array();
			$all_discount_id = array();
			$summary_payment = array();
			
			$data_post['summary_data'] = array(
				'total_billing'	=> 0,
				'total_discount_item'	=> 0,
				'total_discount_billing'	=> 0,
				'net_sales'	=> 0,
				'service_total'	=> 0,
				'tax_total'	=> 0,
				'total_pembulatan'	=> 0,
				'compliment_total'	=> 0,
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
					
					if(!in_array($s['id'], $all_bil_id)){
						$all_bil_id[] = $s['id'];
					}		
					
					if(!empty($s['is_compliment'])){
						$s['total_billing'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
						$s['service_total'] = 0;
						$s['tax_total'] = 0;
					}
					
					//diskon_sebelum_pajak_service
					if($data_post['diskon_sebelum_pajak_service'] == 0){
						$s['sub_total'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];		
					}else{
						$s['sub_total'] = $s['total_billing'] - $s['discount_total'] + $s['tax_total'] + $s['service_total'];
						$s['net_sales'] = $s['total_billing'] - $s['discount_total'];
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
					$s['grand_total'] -= $s['compliment_total'];
					
					//diskon_sebelum_pajak_service
					if($data_post['diskon_sebelum_pajak_service'] == 0){
						$s['grand_total'] -= $s['discount_total'];
						$s['grand_total'] -= $s['discount_billing_total'];
					}
					
					if($s['grand_total'] <= 0){
						$s['grand_total'] = 0;
					}
					
					$s['total_pembulatan_show'] = priceFormat($s['total_pembulatan']);
					
					if($s['total_pembulatan'] < 0){
						$s['total_pembulatan_show'] = "(".priceFormat($s['total_pembulatan']).")";
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
					/*if($s['total_cash'] == 0){
						if($s['total_credit'] > $s['total_dp']){
							$s['total_credit'] -= $s['total_dp'];
						}
					}else{
						if($s['total_cash'] > $s['total_dp']){
							$s['total_cash'] -= $s['total_dp'];
						}
					}*/
					
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
					}else{
					
						if(!empty($s['is_half_payment'])){
							$s['payment_note'] = 'HALF PAYMENT';
						}
						
						if(strtolower($s['payment_type_name']) != 'cash'){
							$s['payment_note'] = strtoupper($s['bank_name']).' '.$card_no;
						}
					}
					
					if(!empty($s['billing_notes'])){
						if(!empty($s['payment_note'])){
							$s['payment_note'] .= '<br/>'.$s['billing_notes'];
						}else{
							$s['payment_note'] .= $s['billing_notes'];
						}
					}
					
					//if($s['billing_no'] == '1601010055'){
						//echo '<pre>';
						//print_r($s);
						//die();
					//}
					
					$data_post['summary_data']['total_billing'] += $s['total_billing'];
					$data_post['summary_data']['total_discount_item'] += $s['discount_total'];
					$data_post['summary_data']['total_discount_billing'] += $s['discount_billing_total'];
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
						
						if($s['payment_id'] == 2 OR $s['payment_id'] == 3){
							if(!empty($default_payment_bank[$s['payment_id']])){
								$s['bank_id'] = $default_payment_bank[$s['payment_id']];
							}
							
						}
						
					}
					
					$var_payment = $s['bank_id'];
					if(empty($summary_payment[$var_payment])){
						
						$bank_name = 'CASH';
						if(!empty($bank_data[$s['bank_id']])){
							$bank_name = $bank_data[$s['bank_id']];
						}
						$payment_name = 'CASH';
						if(!empty($dt_payment_name[$s['payment_id']])){
							$payment_name = $dt_payment_name[$s['payment_id']];
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
					
							$tot_payment = 0;
							$tot_payment_show = 0;
							if($s['payment_id'] == $key_id){
								//$tot_payment = $s['grand_total'];
								//$tot_payment_show = $s['grand_total_show'];
								
								if($key_id == 3 OR $key_id == 2){
									$tot_payment = $s['total_credit'];	
								}else{
									$tot_payment = $s['total_cash'];	
								}
								
								$tot_payment_show = priceFormat($tot_payment);
								
								//credit half payment
								if(!empty($s['is_half_payment']) AND $key_id != 1){
									$tot_payment = $s['total_credit'];
									$tot_payment_show = priceFormat($s['total_credit']);
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
															
						}
					}
					
					
					//$newData[$s['id']] = $s;
					
				}
			}
			
			//GROUP PAYMENT
			$summary_payment_group = array();
			if(!empty($summary_payment)){
				foreach($summary_payment as $dt){
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
			
			$jumlah_uang_total = printer_command_align_right(($get_data['jumlah_uang_koin']+$get_data['jumlah_uang_kertas']), 11);
			
			$menu_sales = printer_command_align_right($data_post['summary_data']['total_billing'], 11);
			$disc_per_item = printer_command_align_right($data_post['summary_data']['total_discount_item'], 11);
			
			$menu_net_sales_count = ($data_post['summary_data']['total_billing']-$data_post['summary_data']['total_discount_item']);
			$menu_net_sales = printer_command_align_right($menu_net_sales_count, 11);
			$disc_per_billing = printer_command_align_right($data_post['summary_data']['total_discount_billing'], 11);
			
			$total_net_sales_count = ($menu_net_sales_count-$data_post['summary_data']['total_discount_item']);
			$total_net_sales = printer_command_align_right($total_net_sales_count, 11);
			
			$service_total = printer_command_align_right($data_post['summary_data']['service_total'], 11);
			$tax_total = printer_command_align_right($data_post['summary_data']['tax_total'], 11);
			$total_pembulatan = printer_command_align_right($data_post['summary_data']['total_pembulatan'], 11);
			$compliment_total = printer_command_align_right(priceFormat($data_post['summary_data']['compliment_total']), 11);
			$grand_total = printer_command_align_right($data_post['summary_data']['grand_total'], 11);
			
			$all_summary_data = "\n\n[size=2][align=1]SALES SUMMARY[tab]\n";
			$all_summary_data .= "[size=1]";
			$all_summary_data .= "[align=0]MENU SALES[tab]".$menu_sales."\n"; 
			$all_summary_data .= "[align=0]DISC/ITEM[tab]".$disc_per_item."\n"; 
			$all_summary_data .= "[align=0]NET SALES[tab]".$menu_net_sales."\n"; 
			$all_summary_data .= "[align=0]DISC/BILLING[tab]".$disc_per_billing."\n"; 
			$all_summary_data .= "[align=0]TOTAL NET SALES[tab]".$total_net_sales."\n"; 
			$all_summary_data .= "[align=0]SERVICE[tab]".$service_total."\n"; 
			$all_summary_data .= "[align=0]TAX[tab]".$tax_total."\n"; 
			$all_summary_data .= "[align=0]PEMBULATAN[tab]".$total_pembulatan."\n"; 
			$all_summary_data .= "[align=0]TOTAL SALES[tab]".$grand_total; 
			if(!empty($data_post['summary_data']['compliment_total'])){
				$all_summary_data .= "\n[align=0][tab]COMPLIMENT[tab]".$compliment_total; 
			}
			
			$all_payment_data = '';
			if(!empty($summary_payment_group)){
				foreach($summary_payment_group as $key => $dt_detail){
					
					$no_payment = 0;
					if(!empty($dt_detail)){
						foreach($dt_detail as $dt){
							
							$no_payment++;
							$payment_name = ucwords(str_replace("_"," ",$dt['payment_name']));
							$data_name = ucwords(str_replace("_"," ",$dt['bank_name']));
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
							
							if(empty($all_payment_data)){
								$all_payment_data = "[size=2][align=1]PAYMENT SUMMARY[tab]\n";
								$all_payment_data .= "[size=1]";
							}
							
							$value_show = printer_command_align_right($dt['payment_'.$key], 11);
							
							if($no_payment == 1 AND count($dt_detail) == 1){
								$all_payment_data .= $payment_name."[tab]".$value_show."\n"; 
							}else{
								if($no_payment == 1){
									$all_payment_data .= $payment_name."\n";
								}
								$all_payment_data .= "[align=0]".$data_name."[tab]".$value_show."\n";
							}
							
							 
							
						}
					}
					
					
				}
			}
			
			
			$print_attr = array(
				"{tipe_openclose}"		=> 'Close Cashier',
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