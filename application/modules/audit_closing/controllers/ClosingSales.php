<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ClosingSales extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_closingsales', 'm');
		$this->load->model('model_closingsalesdetail', 'm2');
	}
	
	public function gridData()
	{
		$this->table_closing = $this->prefix.'closing';
		$this->table_closing_sales_detail = $this->prefix.'closing_sales_detail';
		
		//generate_status_text
		$sortAlias = array(
			//'closing_status_text' => 'closing_status'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table_closing.' as a',
			'where'			=> array('a.tipe' => 'sales'),
			'order'			=> array('a.tanggal' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$generate_status = $this->input->post('generate_status');
		$closing_status = $this->input->post('closing_status');
		$skip_date = $this->input->post('skip_date');
		
		//FILTER
		$date_from = $this->input->post('date_from');
		$date_till = $this->input->post('date_till');
		$keywords = $this->input->post('keywords');
		if(!empty($keywords)){
			$searching = $keywords;
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
							
				$qdate_from = date("Y-m-d",strtotime($date_from));
				$qdate_till = date("Y-m-d",strtotime($date_till));
				
				$params['where'][] = "(a.tanggal >= '".$qdate_from."' AND a.tanggal <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.tanggal' => 'DESC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.tanggal LIKE '%".$searching."%')";
		}		
		if(!empty($generate_status)){
			$params['where'][] = "a.generate_status = '".$generate_status."'";
		}
		if(!empty($closing_status)){
			$params['where'][] = "a.closing_status = '".$closing_status."'";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		
  		$newData_update = array();		
		$all_id = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				if(!in_array($s['id'], $all_id)){
					$all_id[] = $s['id'];
				}
			}
		}
		
		$data_tanggal = array();
		$closing_sales_start_date = '';
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['tanggal'] = date("d-m-Y", strtotime($s['tanggal']));
				
				if(!in_array($s['tanggal'], $data_tanggal)){
					$data_tanggal[] = $s['tanggal'];
				}
				
				if(empty($closing_sales_start_date)){
					$closing_sales_start_date = $s['tanggal'];
				}
				
				
				if($s['closing_status'] == 1){
					$s['closing_status_text'] = '<span style="color:green;">Yes</span>';
				}else{
					$s['closing_status_text'] = '<span style="color:red;">No</span>';
				}
				
				if($s['generate_status'] == 1){
					$s['generate_status_text'] = '<span style="color:green;">Yes</span>';
				}else{
					$s['generate_status_text'] = '<span style="color:red;">No</span>';
				}
				
				//echo 'tanggal = '.$s['tanggal'].'<br>';
				if(empty($newData_update[$s['tanggal']])){
					$newData_update[$s['tanggal']] = array();
				}
				
				$newData_update[$s['tanggal']] = $s;
				
				//array_push($newData_update, $s);
			}
		}
		
		//echo '<pre>';
		//print_r($newData_update);
		//die();
		
		//if empty check on opt = closing_sales_start_date
		$opt_value = array(
			'closing_sales_start_date'
		);
		$get_opt = get_option_value($opt_value);
		
		
		if(!empty($get_opt['closing_sales_start_date'])){
			$closing_sales_start_date = $get_opt['closing_sales_start_date'];
		}
		
		if(empty($closing_sales_start_date)){
			$closing_sales_start_date = date("d-m-Y");
		}
		
		$today_date = date("d-m-Y");
		$today_mktime = strtotime($today_date);
		$closing_mktime = strtotime($closing_sales_start_date);
		$date_from_mktime = strtotime($date_from);
		$date_till_mktime = strtotime($date_till);
		
		if($date_from_mktime <= $closing_mktime){
			$date_from_mktime = $closing_mktime;
		}
		
		$total_day = 0;
		if(!empty($date_from_mktime)){
			$total_day = ($date_till_mktime - $date_from_mktime) / ONE_DAY_UNIX;
		}
		
		/*echo '$get_opt = '.$get_opt['closing_sales_start_date'].'<br>';
		echo '$closing_mktime = '.$closing_mktime.'<br>';
		echo '$date_from_mktime = '.$date_from_mktime.'<br>';
		echo '$date_till_mktime = '.$date_till_mktime.'<br>';
		echo '$closing_sales_start_date = '.$closing_sales_start_date.'<br>';
		echo '$date_from = '.$date_from.'<br>';
		echo '$date_till = '.$date_till.'<br>';
		echo '$total_day = '.$total_day.'<br>';
		die();*/
		
		$newData = array();	
		if(!empty($total_day)){
			for($i=$total_day; $i >= 0; $i--){
				
				$tanggal = date("d-m-Y", ($date_from_mktime + ($i*ONE_DAY_UNIX)));
				
				if(($date_from_mktime + ($i*ONE_DAY_UNIX)) <= $today_mktime){
					
					$dt_push = array(
						'tanggal'	=> $tanggal,
						'closing_status'	=> 0,
						'closing_status_text'	=> '<span style="color:red;">No</span>',
						'generate_status'	=> 0,
						'generate_status_text'	=> '<span style="color:red;">No</span>'
					);
					
					
					if(!in_array($tanggal, $data_tanggal)){
						$data_tanggal[] = $tanggal;
						array_push($newData, $dt_push);
					}else{
						if(!empty($newData_update[$tanggal])){
							
							$dt_push = array(
								'tanggal'	=> $tanggal,
								'closing_status'	=> $newData_update[$tanggal]['closing_status'],
								'closing_status_text'	=> $newData_update[$tanggal]['closing_status_text'],
								'generate_status'	=> $newData_update[$tanggal]['generate_status'],
								'generate_status_text'	=> $newData_update[$tanggal]['generate_status_text']
							);
							
							array_push($newData, $dt_push);
						}
					}
					
				}
				
			}
		}
		
		
		$get_data['data'] = $newData;
		$get_data['totalCount'] = count($newData);
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetail()
	{
		
		$this->table_closing_sales = $this->prefix.'closing_sales';
		$session_client_id = $this->session->userdata('client_id');	
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		$closing_date = $this->input->post('closing_date');
		if(empty($closing_date)){
			$keterangan = array('id' => '', 'keterangan' => 'Choose Closing Date!', 'total' => '');
			die(json_encode(array('data' => array(0 => $keterangan), 'totalCount' => 1)));
		}
		
		$tanggal = date("Y-m-d", strtotime($closing_date));
		
		// Default Parameter
		$this->db->from($this->table_closing_sales.' as a');
		$this->db->where("tanggal", $tanggal);
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			$data = $dt_closing->row();
			$all_data_detail = array();
			
			$data_detail = array(
				'Closing Sales Date' => date("d-m-Y", strtotime($data->tanggal)),
				'Qty Billing'		=> $data->qty_billing, 
				'Total Guest'		=> $data->total_guest, 
				'&nbsp;'			=> '&nbsp;', 
				'<b>BILLING<b/>'	=> '&nbsp;', 
				'Total Billing'		=> priceFormat($data->total_billing), 
				'Total Tax'			=> priceFormat($data->tax_total),  
				'Total Service'		=> priceFormat($data->service_total), 
				'&nbsp;<b>Sub Total<b/>'			=> '<b>'.priceFormat($data->sub_total).'</b>', 
				'Total Pembulatan'	=> priceFormat($data->total_pembulatan),   
				'Total Discount'	=> priceFormat($data->discount_total),  
				'&nbsp;<b>Grand Total</b>'		=> '<b>'.priceFormat($data->grand_total).'</b>', 
				'Total DP'			=> priceFormat($data->total_dp),   
				'Total Compliment'	=> priceFormat($data->total_compliment), 
				'&nbsp; '			=> '&nbsp;', 
				'<b>PAYMENT:<b/>'	=> '&nbsp;', 
				'Billing Half Payment'	=> $data->qty_halfpayment, 
				'Billing Cash'	=> $data->qty_payment_1, 
				'Billing Debit Card'	=> $data->qty_payment_2,  
				'Billing Credit Card'	=> $data->qty_payment_3,
				'Total Cash'	=> priceFormat($data->total_payment_1), 
				'Total Debit Card'	=> priceFormat($data->total_payment_2),
				'Total Credit Card'	=> priceFormat($data->total_payment_3),
				'&nbsp;   '			=> '&nbsp;', 
				'<b>HPP & PROFIT:<b/>'	=> '&nbsp;', 
				'Total HPP'			=> priceFormat($data->total_hpp),  
				'Total Profit'		=> priceFormat($data->total_profit), 
			);
			
			$no = 0;
			foreach($data_detail as $ket => $val){
				$no++;
				$all_data_detail [] = array(
					'id'			=> $no,
					'keterangan'	=> $ket,
					'total'			=> $val
				);
			}
			
			$get_data = array('data' => $all_data_detail, 'totalCount' => count($all_data_detail));
		}else{
			$keterangan = array('id' => '', 'keterangan' => 'No Data or Not Been Generated!', 'total' => '');
			$get_data = array('data' => array( 0 => $keterangan), 'totalCount' => 1);
		}
		  		
      	die(json_encode($get_data));
	}
	
	public function generate()
	{
		$this->table_closing = $this->prefix.'closing';
		$this->table_closing_sales = $this->prefix.'closing_sales';
		$session_client_id = $this->session->userdata('client_id');	
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			die(json_encode($r));
		}
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		$is_check = $this->input->post('is_check');
		$current_total = $this->input->post('current_total');
		$closing_date = $this->input->post('closing_date');
		$closing_date = json_decode($closing_date, true);
		$tanggal = array();
		if(is_array($closing_date)){
			$tanggal = $closing_date;
		}else{
			$tanggal[] = $closing_date;
		}
		
		if(!empty($tanggal)){
			$tanggal_new = $tanggal;
			$tanggal = array();
			foreach($tanggal_new as $tgl){
				$tanggal[] = date("Y-m-d", strtotime($tgl));
			}
		}
		
		$tanggal_txt = implode("','", $tanggal);
		
		/*$updated_closing_date = array();
		//cek is been closing
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'sales'");
		$this->db->where("a.tanggal IN ('".$tanggal_txt."')");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result() as $dtC){
				if($dtC->closing_status == 1){
					$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
					$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' Been Closing!');
					die(json_encode($r));
				}else{
					if(!in_array($dtC->tanggal, $updated_closing_date)){
						$updated_closing_date[] = $dtC->tanggal;
					}
				}
			}
			
		}*/
		
		//SORTING DATE -> LOW - HIGH
		$dt_tanggal = array();
		foreach($tanggal as $dt){
			$dt_tanggal[strtotime($dt)] = $dt;
		}
		
		ksort($dt_tanggal);
		
		$total_date = count($dt_tanggal);
		if($total_date > 31){
			$r = array('success' => false, 'info'	=> 'Max Generate is 31 Days!');
			die(json_encode($r));
		}
		
		//CEK FIRST TO START DATE
		$opt_value = array(
			'closing_sales_start_date'
		);
		$get_opt = get_option_value($opt_value);
		
		
		if(!empty($get_opt['closing_sales_start_date'])){
			$closing_sales_start_date = $get_opt['closing_sales_start_date'];
			$closing_sales_start_date = date("Y-m-d", strtotime($closing_sales_start_date));
		}
		
		if(empty($closing_sales_start_date)){
			$closing_sales_start_date = date("Y-m-d");
		}
		
		$today_date = date("Y-m-d");
		$date_current_total = '';
		$date_from = '';
		$date_till = '';
		$i = 0;
		foreach($dt_tanggal as $dtT){
			$i++;
			
			if($i == 1){
				$date_from = $dtT;
			}
			
			if(count($dt_tanggal) == $i){
				$date_till = $dtT;
			}
			
			if($i == $current_total){
				$date_current_total = $dtT;
			}
		}
		
		$updated_closing_date = array();
		if(!empty($date_current_total)){
			
			//cek is been closing
			$this->db->from($this->table_closing.' as a');
			$this->db->where("a.tipe = 'sales'");
			$this->db->where("a.tanggal IN ('".$date_current_total."')");
			$dt_closing = $this->db->get();
			if($dt_closing->num_rows() > 0){
				foreach($dt_closing->result() as $dtC){
					if($dtC->closing_status == 1){
						$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
						$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' Been Closing!');
						die(json_encode($r));
					}else{
						if(!in_array($dtC->tanggal, $updated_closing_date)){
							$updated_closing_date[] = $dtC->tanggal;
						}
					}
				}
				
			}
			
		}
		
		
		//echo $date_current_total;
		//die();
		
		if(strtotime($date_till) > strtotime($today_date)){
			$r = array('success' => false, 'info'	=> 'Max Generate Date is Today: '.date("d-m-Y").'!');
			die(json_encode($r));
		}
		
		$allowed_generate = false;
		
		//check on db < $date_from
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'sales'");
		$this->db->where("a.tanggal < '".$date_from."' AND a.generate_status = 0");
		$this->db->order_by("a.tanggal", "DESC");
		$this->db->limit(1);
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			
			$dtC = $dt_closing->row();
			$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
			//echo $dtC->tanggal.' == '.$closing_sales_start_date.'<br/>';
			
			if(strtotime($dtC->tanggal) < strtotime($closing_sales_start_date)){
				$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' or < from date '.date("d-m-Y", strtotime($date_from)).' Not Been Generated!');
				die(json_encode($r));
			}
			
			
			
		}else{
			
			if($date_from >= $closing_sales_start_date){
				//allowed
				$allowed_generate = true;
				//echo 'allowed_generate = closing_sales_start_date<br/>';
			}else{
				$r = array('success' => false, 'info'	=> 'Date Closing From '.$closing_sales_start_date.'!');
				die(json_encode($r));
			}
			
			//$allowed_generate = true;
			//echo 'allowed_generate -> ON DB > '.$date_from.'br/>';
		}
		
		
		
		if(!empty($is_check)){
			$r = array('success' => true, 'total_hari'	=> count($dt_tanggal));
			die(json_encode($r));
		}
		
		//CURRENT Date
		$date_from = $date_current_total;
		$date_till = $date_current_total;
		
		
		$data_generate = array();
		
		//BEGIN GENERATE ---> FROM REPORT SALES
		$this->table_billing = $this->prefix.'billing';
		$this->table_billing_detail = $this->prefix.'billing_detail';
		
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
		
		if(empty($sorting)){
			$this->db->order_by("payment_date","ASC");
		}else{
			$this->db->order_by($sorting,"ASC");
		}

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
		
		
		$all_group_date = array();		  
		$all_bil_id = array();	  
		$all_bil_id_date = array();
		$newData = array();
		$dt_payment = array();
		$no_id = 1;
		if(!empty($data_post['report_data'])){
			foreach ($data_post['report_data'] as $s){
				
				if(!in_array($s['id'], $all_bil_id)){
					$all_bil_id[] = $s['id'];
				}		
				
				if(!empty($s['is_compliment'])){
					$s['total_billing'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
					//if(!empty($s['include_tax']) OR !empty($s['include_service'])){
					//	$s['total_billing'] = $s['total_billing'];
					//}
					$s['service_total'] = 0;
					$s['tax_total'] = 0;
				}
				
				
				$s['sub_total'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
				//if(!empty($s['include_tax']) OR !empty($s['include_service'])){
				//	$s['sub_total'] = $s['total_billing'];
				//}
				
				$s['grand_total'] = $s['sub_total'] + $s['total_pembulatan'] - $s['discount_total'];
				
				if($s['grand_total'] <= 0){
					$s['grand_total'] = 0;
				}
				
				

				$s['total_hpp'] = 0;
				$s['total_profit'] = 0;
				
				//REKAP TGL
				$payment_date = date("Y-m-d",strtotime($s['payment_date']));
				
				if($date_current_total == $payment_date){
					if(empty($all_group_date[$payment_date])){
						$all_group_date[$payment_date] = array(
							'tanggal'		=> $payment_date, 
							'qty_billing'		=> 0, 
							'total_guest'		=> 0, 
							'total_billing'		=> 0, 
							'tax_total'			=> 0, 
							'service_total'		=> 0,
							'discount_total'	=> 0, 
							'total_dp'			=> 0, 
							'grand_total'		=> 0, 
							'sub_total'			=> 0, 
							'total_pembulatan'	=> 0, 
							'total_compliment'	=> 0, 
							'total_hpp'			=> 0, 
							'total_profit'		=> 0, 
							'qty_halfpayment'	=> 0
						);
						
						foreach($payment_data as $key_id => $dtPay){
							$all_group_date[$payment_date]['total_payment_'.$key_id] = 0;
							$all_group_date[$payment_date]['qty_payment_'.$key_id] = 0;
						}
						
						$no_id++;
					}
					
					$all_bil_id_date[$s['billing_id']] = $payment_date;
					
					if(empty($s['total_guest'])){
						$s['total_guest'] = 1;
					}
					
					$all_group_date[$payment_date]['qty_billing'] += 1;
					$all_group_date[$payment_date]['total_guest'] += $s['total_guest'];
					$all_group_date[$payment_date]['total_billing'] += $s['total_billing'];
					$all_group_date[$payment_date]['tax_total'] += $s['tax_total'];
					$all_group_date[$payment_date]['service_total'] += $s['service_total'];
					$all_group_date[$payment_date]['discount_total'] += $s['discount_total'];
					$all_group_date[$payment_date]['total_dp'] += $s['total_dp'];
					$all_group_date[$payment_date]['grand_total'] += $s['grand_total'];
					$all_group_date[$payment_date]['sub_total'] += $s['sub_total'];
					$all_group_date[$payment_date]['total_pembulatan'] += $s['total_pembulatan'];
					$all_group_date[$payment_date]['total_compliment'] += $s['compliment_total'];
					
					
					if(!empty($s['is_compliment'])){
						$all_group_date[$payment_date]['total_compliment'] += $s['grand_total'];
					}else{
						
						
						if(!empty($payment_data)){
							foreach($payment_data as $key_id => $dtPay){
						
								$tot_payment = 0;
								$tot_payment_show = 0;
								if($s['payment_id'] == $key_id){
									
									if($key_id == 3 OR $key_id == 2){
										$tot_payment = $s['total_credit'];	
									}else{
										$tot_payment = $s['total_cash'];	
									}
									
									$all_group_date[$payment_date]['qty_payment_'.$key_id] += 1;
									
									$tot_payment_show = priceFormat($tot_payment);
									
									//credit half payment
									if(!empty($s['is_half_payment']) AND $key_id != 1){
										$all_group_date[$payment_date]['qty_payment_'.$key_id] += 1;
										$all_group_date[$payment_date]['qty_halfpayment'] += 1;
										$tot_payment = $s['total_credit'];
										$tot_payment_show = priceFormat($s['total_credit']);
									}else{
										
										$tot_payment_show = priceFormat($tot_payment);	
									}
										
								}else{
									
									
									//cash
									if(!empty($s['is_half_payment']) AND $key_id == 1){
										$all_group_date[$payment_date]['qty_payment_1'] += 1;
										$all_group_date[$payment_date]['qty_halfpayment'] += 1;
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
						
								if(!empty($s['discount_total']) AND !empty($tot_payment)){
									//$tot_payment = $tot_payment - $s['discount_total'];
									//$tot_payment_show = priceFormat($tot_payment);
								}
						
								//$grand_total_payment[$key_id] += $tot_payment;
								$all_group_date[$payment_date]['total_payment_'.$key_id] += $tot_payment;
								
																
							}
						}
						
					}
				
					//$newData[$s['id']] = $s;
					//array_push($newData, $s);
				}
				
			}
		}
		
		//calc detail
		$total_hpp = array();
		if(!empty($all_bil_id)){
			$all_bil_id_txt = implode(",",$all_bil_id);
			$this->db->from($this->table_billing_detail);
			$this->db->where('billing_id IN ('.$all_bil_id_txt.')');
			$this->db->where('is_deleted', 0);
			$get_detail = $this->db->get();
			if($get_detail->num_rows() > 0){
				foreach($get_detail->result() as $dtRow){
		
					$total_qty = $dtRow->order_qty;
					/*
					 $total_qty = $dtRow->order_qty - $dtRow->retur_qty;
					if($total_qty < 0){
					$total_qty = 0;
					}*/
					
					if(!empty($all_bil_id_date[$dtRow->billing_id])){
						$payment_date = $all_bil_id_date[$dtRow->billing_id];
						
						if(empty($total_hpp[$payment_date])){
							$total_hpp[$payment_date] = 0;
						}
						$total_hpp[$payment_date] += $dtRow->product_price_hpp * $total_qty;
					}
		
					
				}
			}
		}
		
		//check empty date
		if(!empty($dt_tanggal)){
			foreach($dt_tanggal as $key => $val){
				
				if($date_current_total == $val){
					if(empty($all_group_date[$val])){
						
						//echo 'EMPTY: '.$val.'<br/>';
						
						$all_group_date[$val] = array(
							'tanggal'		=> $val, 
							'qty_billing'		=> 0, 
							'total_guest'		=> 0, 
							'total_billing'		=> 0, 
							'tax_total'			=> 0, 
							'service_total'		=> 0,
							'discount_total'	=> 0, 
							'total_dp'			=> 0, 
							'grand_total'		=> 0, 
							'sub_total'			=> 0, 
							'total_pembulatan'	=> 0, 
							'total_compliment'	=> 0, 
							'total_hpp'			=> 0, 
							'total_profit'		=> 0, 
							'qty_halfpayment'	=> 0
						);
						
						foreach($payment_data as $key_id => $dtPay){
							$all_group_date[$val]['total_payment_'.$key_id] = 0;
							$all_group_date[$val]['qty_payment_'.$key_id] = 0;
						}
					}
				}
				
			}
		}
		
		
		
		$newData = array();
		if(!empty($all_group_date)){
			foreach($all_group_date as $key => $detail){
				
				if(!empty($total_hpp[$key])){
					$detail['total_hpp'] = $total_hpp[$key];
				}

				$detail['total_profit'] = $detail['total_billing']-$detail['total_hpp'];
				$newData[$key] = $detail;
				
			}
		}	
		
		//echo '<pre>';
		//print_r($newData);
		//die();
		
		$insert_date = array();
		$insert_closing_sales = array();
		$updated_closing_sales = array();
		$insert_closing = array();
		$updated_closing = array();
		if(!empty($newData)){
			foreach($newData as $dt){
				
				if($date_current_total == $dt['tanggal']){
					if(in_array($dt['tanggal'], $updated_closing_date)){
						
						$dt['updated'] = date('Y-m-d H:i:s');
						$dt['updatedby'] = $session_user;
						$updated_closing_sales[] = $dt;
						
						$bulan = date("m", strtotime($dt['tanggal']));
						$tahun = date("Y", strtotime($dt['tanggal']));
						
						$updated_closing[] = array(
							'tanggal'	=> $dt['tanggal'],
							'bulan'	=> $bulan,
							'tahun'	=> $tahun,
							'tipe'	=> 'sales',
							'closing_status'	=> 0,
							'generate_status'	=> 1,
							'updated'		=>	date('Y-m-d H:i:s'),
							'updatedby'		=>	$session_user,
						);
							
					}else{
						
						$dt['created'] = date('Y-m-d H:i:s');
						$dt['createdby'] = $session_user;
						$dt['updated'] = date('Y-m-d H:i:s');
						$dt['updatedby'] = $session_user;
						$insert_closing_sales[] = $dt;
						
						if(!in_array($dt['tanggal'], $insert_date)){
							$insert_date[] = $dt['tanggal'];
							
							$bulan = date("m", strtotime($dt['tanggal']));
							$tahun = date("Y", strtotime($dt['tanggal']));
							
							$insert_closing[] = array(
								'tanggal'	=> $dt['tanggal'],
								'bulan'	=> $bulan,
								'tahun'	=> $tahun,
								'tipe'	=> 'sales',
								'closing_status'	=> 0,
								'generate_status'	=> 1,
								'created'		=>	date('Y-m-d H:i:s'),
								'createdby'		=>	$session_user,
								'updated'		=>	date('Y-m-d H:i:s'),
								'updatedby'		=>	$session_user
							);
						}
						
					}
				}
				
			}
		}
		
		//echo '<pre>';
		//print_r($all_group_date);
		//die();
		
		if(!empty($insert_closing)){
			//remove if available
			if(!empty($insert_date)){
				$insert_date_txt = implode("','", $insert_date);
				$this->db->delete($this->table_closing, "tanggal IN ('".$insert_date_txt."') AND tipe = 'sales'");
			}
			
			$this->db->insert_batch($this->table_closing, $insert_closing);
		}
		
		if(!empty($insert_closing_sales)){
			//remove if available
			if(!empty($insert_date)){
				$insert_date_txt = implode("','", $insert_date);
				$this->db->delete($this->table_closing_sales, "tanggal IN ('".$insert_date_txt."')");
			}
			
			$this->db->insert_batch($this->table_closing_sales, $insert_closing_sales);
		}
		
		if(!empty($updated_closing)){
			//REMOVE OLD
			$updated_closing_date_txt = implode("','", $updated_closing_date);
			$this->db->delete($this->table_closing,"tanggal IN ('".$updated_closing_date_txt."') AND tipe = 'sales'");
			$this->db->insert_batch($this->table_closing, $updated_closing);
			//$this->db->update_batch($this->table_closing, $updated_closing, 'tanggal');
		}
		
		if(!empty($updated_closing_sales)){
			//REMOVE OLD
			$updated_closing_date_txt = implode("','", $updated_closing_date);
			$this->db->delete($this->table_closing_sales,"tanggal IN ('".$updated_closing_date_txt."')");
			$this->db->insert_batch($this->table_closing_sales, $updated_closing_sales);
			//$this->db->update_batch($this->table_closing_sales, $updated_closing_sales, 'tanggal');
		}
		
		if($date_from == $date_till){
			$r = array('success' => true, 'info'	=> 'Date Closing '.$date_from.' Been Generated!');
			die(json_encode($r));
		}
		
		$r = array('success' => true, 'info'	=> 'Date Closing Generated From '.$date_from.' ~ '.$date_till.'!');
		die(json_encode($r));
				
	}
	
	public function closingDate()
	{
		$this->table_closing = $this->prefix.'closing';
		$this->table_billing = $this->prefix.'billing';
		$this->table_closing_sales = $this->prefix.'closing_sales';
		$session_client_id = $this->session->userdata('client_id');	
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			die(json_encode($r));
		}
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		$from_autoclosing = $this->input->post('from_autoclosing');
		$closing_date = $this->input->post('closing_date');
		$closing_date = json_decode($closing_date);
		$tanggal = array();
		if(is_array($closing_date)){
			$tanggal = $closing_date;
		}else{
			$tanggal[] = $closing_date;
		}
		
		if(!empty($tanggal)){
			$tanggal_new = $tanggal;
			$tanggal = array();
			foreach($tanggal_new as $tgl){
				$tanggal[] = date("Y-m-d", strtotime($tgl));
			}
		}
		
		$tanggal_txt = implode("','", $tanggal);
		
		$updated_closing_date = array();
		//cek is been generated
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'sales'");
		$this->db->where("a.tanggal IN ('".$tanggal_txt."')");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result() as $dtC){
				if($dtC->generate_status == 0){
					$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
					$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' Should Generated First!');
					die(json_encode($r));
				}else{
					if(!in_array($dtC->tanggal, $updated_closing_date)){
						$updated_closing_date[] = $dtC->tanggal;
					}
				}
			}
			
		}
		
		//SORTING DATE -> LOW - HIGH
		$dt_tanggal = array();
		foreach($tanggal as $dt){
			$dt_tanggal[strtotime($dt)] = $dt;
		}
		
		ksort($dt_tanggal);
		
		$total_date = count($dt_tanggal);
		if($total_date > 31){
			$r = array('success' => false, 'info'	=> 'Max Closing is 31 Days!');
			die(json_encode($r));
		}
		
		//CEK FIRST TO START DATE
		$opt_value = array(
			'closing_sales_start_date',
			'autoclosing_auto_cancel_billing'
		);
		$get_opt = get_option_value($opt_value);
		
		
		if(!empty($get_opt['closing_sales_start_date'])){
			$closing_sales_start_date = $get_opt['closing_sales_start_date'];
			$closing_sales_start_date = date("Y-m-d", strtotime($closing_sales_start_date));
		}
		
		if(empty($closing_sales_start_date)){
			$closing_sales_start_date = date("Y-m-d");
		}
		
		$today_date = date("Y-m-d");
		$date_from = '';
		$date_till = '';
		$i = 0;
		foreach($dt_tanggal as $dtT){
			$i++;
			
			if($i == 1){
				$date_from = $dtT;
			}
			
			if(count($dt_tanggal) == $i){
				$date_till = $dtT;
			}
		}
		
		if(strtotime($date_till) > strtotime($today_date)){
			$r = array('success' => false, 'info'	=> 'Max Closing Date is Today: '.date("d-m-Y").'!');
			die(json_encode($r));
		}
		
		//cek is active and hold
		$is_available_active_hold_id = array();
		$is_available_active_hold = array();
		$this->db->select("a.*");
		$this->db->from($this->table_billing.' as a');
		$this->db->where("a.billing_status IN ('hold', 'unpaid')");
		$this->db->where("a.created >= '".$date_from." 00:00:00' AND a.created <= '".$date_till." 23:59:59'");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result() as $dtB){
				
				$tanggal_active_hold = date("d-m-Y", strtotime($dtB->created));
				if(!in_array($tanggal_active_hold, $is_available_active_hold)){
					$is_available_active_hold[] = $tanggal_active_hold;
				}
				
				if(!in_array($dtB->id, $is_available_active_hold_id)){
					$is_available_active_hold_id[] = $dtB->id;
				}
			}
			
		}
		
		//Auto Cancel From Auto Closing
		$autoclosing_auto_cancel_billing = 0;
		if(!empty($get_opt['autoclosing_auto_cancel_billing'])){
			$autoclosing_auto_cancel_billing = $get_opt['autoclosing_auto_cancel_billing'];
		}
		
		if(!empty($from_autoclosing)){
			if(!empty($autoclosing_auto_cancel_billing)){
				
				$is_available_active_hold = array();
				
				//AUTO CANCEL BILLING
				if(!empty($is_available_active_hold_id)){
					$is_available_active_hold_id_sql = implode(",", $is_available_active_hold_id);
					
					$dt_auto_cancel = array(
						'billing_status' => 'cancel',
						'billing_notes' => 'Auto Cancel From Auto Closing'
					);
					
					$this->db->update($this->table_billing, $dt_auto_cancel, "id IN (".$is_available_active_hold_id_sql.")");
					
				}
				
			}
		}
		
		if(!empty($is_available_active_hold)){
			$is_available_active_hold_txt = implode(", ", $is_available_active_hold);
			$r = array('success' => false, 'info'	=> 'Please Set Active & Hold Billing to Cancel!<br/>on Date: '.$is_available_active_hold_txt);
			die(json_encode($r));
		}
		
		
		$allowed_closing = false;
		
		$date_from_minus_1 = strtotime($date_from) - ONE_DAY_UNIX;
		$date_from_minus = date("Y-m-d", $date_from_minus_1);
		//check on db < $date_from
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'sales'");
		$this->db->where("a.tanggal = '".$date_from_minus."'");
		$this->db->order_by("a.tanggal", "DESC");
		$this->db->limit(1);
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			
			$dtC = $dt_closing->row();
			$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
			//echo $dtC->tanggal.' == '.$closing_sales_start_date.'<br/>';
			
			if($dtC->closing_status == 1){
				$allowed_closing = true;
			}else{
				if(strtotime($dtC->tanggal) < strtotime($closing_sales_start_date)){
					//max closing is < closing_sales_start_date
					$allowed_closing = true;
				}else{
					$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' or < from date '.date("d-m-Y", strtotime($date_from)).' Still Not Closed!');
					die(json_encode($r));
				}
				
			}
			
			
			
		}else{
			
			//echo "$date_from == $closing_sales_start_date";die();
			if($date_from == $closing_sales_start_date){
				//allowed
				$allowed_closing = true;
				//echo 'allowed_generate = closing_sales_start_date<br/>';
			}else{
				
				$r = array('success' => false, 'info'	=> 'Please Generate Data First, Start Date Closing From '.$closing_sales_start_date.'!');
				die(json_encode($r));
			}
			
			//$allowed_closing = true;
			//echo 'allowed_closing -> ON DB > '.$date_from.'br/>';
		}
		
		
		//get ID
		//check on db < $date_from
		$data_closing = array();
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'sales'");
		$this->db->where("a.tanggal >= '".$date_from."' AND a.tanggal <= '".$date_till."' ");
		$this->db->order_by("a.tanggal", "DESC");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result_array() as $dt){
				$data_closing[$dt['tanggal']] = $dt['id'];
			}
		}
		
		$updated_closing = array();
		foreach($dt_tanggal as $dtT){
			
			if(!empty($data_closing[$dtT])){
				$updated_closing[] = array(
					'id' => $data_closing[$dtT],
					'closing_status' => 1,
					//'tanggal'	=> $dtT
				);
			}
			
			
		}
		
		
		//echo '$allowed_closing = '.$allowed_closing.'<pre>';
		//print_r($updated_closing);
		//die();
		
		if(!empty($updated_closing)){
			$this->db->update_batch($this->table_closing, $updated_closing, 'id');
		}
		
		if(count($updated_closing) == 1){
			$r = array('success' => true, 'info'	=> 'Date '.$date_from.' Been Closed!');
			die(json_encode($r));
		}
		
		$r = array('success' => true, 'info'	=> 'Date From '.$date_from.' ~ '.$date_till.' Been Closed!');
		die(json_encode($r));
		
	}
	
	public function openDate()
	{
		$this->table_closing = $this->prefix.'closing';
		$this->table_closing_sales = $this->prefix.'closing_sales';
		$session_client_id = $this->session->userdata('client_id');	
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			die(json_encode($r));
		}
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		$closing_date = $this->input->post('closing_date');
		$closing_date = json_decode($closing_date);
		$tanggal = array();
		if(is_array($closing_date)){
			$tanggal = $closing_date;
		}else{
			$tanggal[] = $closing_date;
		}
		
		if(!empty($tanggal)){
			$tanggal_new = $tanggal;
			$tanggal = array();
			foreach($tanggal_new as $tgl){
				$tanggal[] = date("Y-m-d", strtotime($tgl));
			}
		}
		
		$tanggal_txt = implode("','", $tanggal);
		
		$updated_closing_date = array();
		//cek is been generated
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'sales'");
		$this->db->where("a.tanggal IN ('".$tanggal_txt."')");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result() as $dtC){
				if($dtC->closing_status == 0){
					$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
					$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' Status Not Closed!');
					die(json_encode($r));
				}else{
					if(!in_array($dtC->tanggal, $updated_closing_date)){
						$updated_closing_date[] = $dtC->tanggal;
					}
				}
			}
			
		}
		
		//SORTING DATE -> LOW - HIGH
		$dt_tanggal = array();
		foreach($tanggal as $dt){
			$dt_tanggal[strtotime($dt)] = $dt;
		}
		
		krsort($dt_tanggal);
		
		$total_date = count($dt_tanggal);
		if($total_date > 31){
			$r = array('success' => false, 'info'	=> 'Max Open Date is 31 Days!');
			die(json_encode($r));
		}
		
		//CEK FIRST TO START DATE
		$opt_value = array(
			'closing_sales_start_date'
		);
		$get_opt = get_option_value($opt_value);
		
		
		if(!empty($get_opt['closing_sales_start_date'])){
			$closing_sales_start_date = $get_opt['closing_sales_start_date'];
			$closing_sales_start_date = date("Y-m-d", strtotime($closing_sales_start_date));
		}
		
		if(empty($closing_sales_start_date)){
			$closing_sales_start_date = date("Y-m-d");
		}
		
		$today_date = date("Y-m-d");
		$date_from = '';
		$date_till = '';
		$i = 0;
		foreach($dt_tanggal as $dtT){
			$i++;
			
			if($i == 1){
				$date_from = $dtT;
			}
			
			if(count($dt_tanggal) == $i){
				$date_till = $dtT;
			}
		}
		
		if(strtotime($date_from) > strtotime($today_date)){
			$r = array('success' => false, 'info'	=> 'Max Open Date is Today: '.date("d-m-Y").'!');
			die(json_encode($r));
		}
		
		$allowed_open = false;
		
		$date_from_plus_1 = strtotime($date_from) + ONE_DAY_UNIX;
		$date_from_plus = date("Y-m-d", $date_from_plus_1);
		//check on db < $date_from
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'sales'");
		$this->db->where("a.tanggal = '".$date_from_plus."'");
		$this->db->order_by("a.tanggal", "DESC");
		$this->db->limit(1);
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			
			$dtC = $dt_closing->row();
			$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
			//echo $dtC->tanggal.' == '.$closing_sales_start_date.'<br/>';
			
			if($dtC->closing_status == 0){
				$allowed_open = true;
			}else{
				if(strtotime($dtC->tanggal) < strtotime($closing_sales_start_date)){
					//max closing is < closing_sales_start_date
					$allowed_open = true;
				}else{
					$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' or > from date '.date("d-m-Y", strtotime($date_from)).' Still Closed!');
					die(json_encode($r));
				}
				
			}
			
			
		}else{
			
			//echo "$date_from == $closing_sales_start_date";die();
			if($date_from >= $closing_sales_start_date){
				//allowed
				$allowed_open = true;
				//echo 'allowed_generate = closing_sales_start_date<br/>';
			}else{
				
				$r = array('success' => false, 'info'	=> 'Please Generate Data First, Start Date Closing From '.$closing_sales_start_date.'!');
				die(json_encode($r));
			}
			
			//$allowed_open = true;
			//echo 'allowed_open -> ON DB > '.$date_from.'br/>';
		}
		
		
		//get ID
		//check on db < $date_from
		$data_closing = array();
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'sales'");
		$this->db->where("a.tanggal >= '".$date_till."' AND a.tanggal <= '".$date_from."' ");
		$this->db->order_by("a.tanggal", "DESC");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result_array() as $dt){
				$data_closing[$dt['tanggal']] = $dt['id'];
			}
		}
		
		$updated_closing = array();
		foreach($dt_tanggal as $dtT){
			
			if(!empty($data_closing[$dtT])){
				$updated_closing[] = array(
					'id' => $data_closing[$dtT],
					'closing_status' => 0,
					//'tanggal'	=> $dtT
				);
			}
			
			
		}
		
		//echo '$allowed_open = '.$allowed_open.'<pre>';
		//print_r($data_closing);
		//die();
		
		if(!empty($updated_closing)){
			$this->db->update_batch($this->table_closing, $updated_closing, 'id');
		}
		
		if(count($updated_closing) == 1){
			$r = array('success' => true, 'info'	=> 'Date '.$date_from.' Been Opened!');
			die(json_encode($r));
		}
		
		$r = array('success' => true, 'info'	=> 'Date From '.$date_from.' ~ '.$date_till.' Been Opened!');
		die(json_encode($r));
		
	}
}