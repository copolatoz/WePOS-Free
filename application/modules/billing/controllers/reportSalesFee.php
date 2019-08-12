<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class reportSalesFee extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_databilling', 'm');
		$this->load->model('model_billingdetail', 'm2');
	}
	
	//important for service load
	function services_model_loader(){
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$dt_model = array( 
			'm' => '../../billing/models/model_databilling',
			'm2' => '../../billing/models/model_billingdetail'
		);
		return $dt_model;
	}	
	
	public function print_reportSalesFee(){
		
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);

		if(empty($date_from)){ $date_from = date('Y-m-d'); }
		if(empty($date_till)){ $date_till = date('Y-m-d'); }
		
		if(empty($sorting)){
			$sorting = 'payment_date';
		}
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'SALES FEE REPORT',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'user_fullname'	=> $user_fullname,
			'sorting'	=> $sorting,
			'diskon_sebelum_pajak_service' => 0
		);
		
		$get_opt = get_option_value(array('report_place_default','diskon_sebelum_pajak_service',
		'cashier_max_pembulatan','cashier_pembulatan_keatas','role_id_kasir','maxday_cashier_report',
		'jam_operasional_from','jam_operasional_to','jam_operasional_extra'));
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
		
		if(empty($date_from) OR empty($date_till)){
			die('Billing Paid Not Found!');
		}else{
				
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
						
			$ret_dt = check_maxview_cashierReport($get_opt, $mktime_dari, $mktime_sampai);
						
			//$qdate_from = date("Y-m-d",strtotime($date_from));
			//$qdate_till = date("Y-m-d",strtotime($date_till));
			//$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
			//$add_where = "(a.payment_date >= '".$qdate_from." 07:00:01' AND a.payment_date <= '".$qdate_till_max." 06:00:00')";
			
			//laporan = jam_operasional
			$qdate_from = $ret_dt['qdate_from'];
			$qdate_till = $ret_dt['qdate_till'];
			$qdate_till_max = $ret_dt['qdate_till_max'];
			$add_where = "(a.payment_date >= '".$qdate_from."' AND a.payment_date <= '".$qdate_till_max."')";
			
			$this->db->select("a.*, a.id as billing_id, a.updated as billing_date, d.payment_type_name, e.bank_name, f.sales_name, f.sales_company");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT');
			$this->db->join($this->prefix.'bank as e','e.id = a.bank_id','LEFT');
			$this->db->join($this->prefix.'sales as f','f.id = a.sales_id','LEFT');
			$this->db->where("a.billing_status", 'paid');
			$this->db->where("a.is_deleted", 0);
			$this->db->where("a.sales_id > 0");
			$this->db->where($add_where);
			
			if(!empty($sales_id)){
				$this->db->where("a.sales_id", $sales_id);
			}
			
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
			
			$sales_name_report = '';
			$all_bil_id = array();
			$newData = array();
			$dt_payment = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					$s['billing_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['payment_date'] = date("d-m-Y H:i",strtotime($s['payment_date']));
					
					if(!in_array($s['id'], $all_bil_id)){
						$all_bil_id[] = $s['id'];
					}		
					
					if(!empty($sales_id)){
						$sales_name_report = $s['sales_name'].' - '.$s['sales_company'];
					}
					
					$s['total_billing_awal'] = $s['total_billing'];
					
					//CHECK REAL TOTAL BILLING
					if(!empty($s['include_tax']) OR !empty($s['include_service'])){
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
						
						if(!empty($s['include_tax']) OR !empty($s['include_service'])){
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
					$s['grand_total_show'] = priceFormat($s['grand_total']);
					$s['total_billing_show'] = priceFormat($s['total_billing']);
					$s['total_paid_show'] = priceFormat($s['total_paid']);
					$s['tax_total_show'] = priceFormat($s['tax_total']);
					$s['service_total_show'] = priceFormat($s['service_total']);
					$s['discount_total_show'] = priceFormat($s['discount_total']);
					$s['discount_billing_total_show'] = priceFormat($s['discount_billing_total']);
					
					//SALES
					$add_info_payment_note = '';
					$s['total_sales_fee'] = 0;
					if(!empty($s['sales_id'])){
						
						$use_sales_total = $s['sub_total'];
						$add_info_payment_note = 'After Tax/Service - ';
						if($s['sales_type'] == 'before_tax'){
							$use_sales_total = $s['total_billing'];
							$add_info_payment_note = 'Before Tax/Service - ';
						}
						
						if(!empty($s['sales_price'])){
							$s['total_sales_fee'] = $s['sales_price'];
							$add_info_payment_note .= $s['sales_price'].' / billing';
						}else{
							$s['total_sales_fee'] = $use_sales_total * $s['sales_percentage']/100;
							$add_info_payment_note .= $s['sales_percentage'].'% ';
						}
						
						
					}
					
					$s['total_sales_fee_show'] = priceFormat($s['total_sales_fee']);
					
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
					
					if(!empty($add_info_payment_note)){
						
						if(!empty($s['payment_note'])){
							$s['payment_note'] = $add_info_payment_note.', '.$s['payment_note'];
						}else{
							$s['payment_note'] = $add_info_payment_note;
						}
						
					}
					
										
					$newData[$s['id']] = $s;
					//array_push($newData, $s);
					
				}
			}
			
			//calc detail
			$total_hpp = array();
			if(!empty($all_bil_id)){
				$all_bil_id_txt = implode(",",$all_bil_id);
				$this->db->from($this->table2);
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
						
						if(empty($total_hpp[$dtRow->billing_id])){
							$total_hpp[$dtRow->billing_id] = 0;
						}
						
						$total_hpp[$dtRow->billing_id] += $dtRow->product_price_hpp * $total_qty;

						
					}
				}
			}
			
			$newData_switch = $newData;
			$newData = array();
			if(!empty($newData_switch)){
				foreach($newData_switch as $dt){
					
					if(!empty($total_hpp[$dt['billing_id']])){
						$dt['total_hpp'] = $total_hpp[$dt['billing_id']];
					}
					
					$dt['total_profit'] = $dt['total_billing']-$dt['total_hpp'];
					$dt['total_hpp_show'] = priceFormat($dt['total_hpp']);
					$dt['total_profit_show'] = priceFormat($dt['total_profit']);
					
					$newData[] = $dt;
				}
			}
	
			$data_post['report_data'] = $newData;
			$data_post['payment_data'] = $dt_payment_name;
			//$data_post['total_hpp'] = $total_hpp;
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		$useview = 'print_reportSalesFee';
		$data_post['report_name'] = 'SALES FEE REPORT';
		$data_post['sales_name_report'] = $sales_name_report;
		
		if($do == 'excel'){
			$useview = 'excel_reportSalesFee';
		}
		
		$this->load->view('../../billing/views/'.$useview, $data_post);	
	}
	
	public function print_reportSalesFeeRecap(){
		
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);

		if(empty($date_from)){ $date_from = date('Y-m-d'); }
		if(empty($date_till)){ $date_till = date('Y-m-d'); }
		
		if(empty($sorting)){
			$sorting = 'payment_date';
		}
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'SALES REPORT (RECAP)',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'user_fullname'	=> $user_fullname,
			'diskon_sebelum_pajak_service' => 0
		);
		
		$get_opt = get_option_value(array('report_place_default','diskon_sebelum_pajak_service',
		'cashier_max_pembulatan','cashier_pembulatan_keatas','role_id_kasir','maxday_cashier_report',
		'jam_operasional_from','jam_operasional_to','jam_operasional_extra'));
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
		
		if(empty($date_from) OR empty($date_till)){
			die('Billing Paid Not Found!');
		}else{
				
			if(empty($date_from)){ $date_from = date('Y-m-d'); }
			if(empty($date_till)){ $date_till = date('Y-m-d'); }
			
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
						
			$ret_dt = check_maxview_cashierReport($get_opt, $mktime_dari, $mktime_sampai);
						
			//$qdate_from = date("Y-m-d",strtotime($date_from));
			//$qdate_till = date("Y-m-d",strtotime($date_till));
			//$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
			//$add_where = "(a.payment_date >= '".$qdate_from." 07:00:01' AND a.payment_date <= '".$qdate_till_max." 06:00:00')";
			
			//laporan = jam_operasional
			$qdate_from = $ret_dt['qdate_from'];
			$qdate_till = $ret_dt['qdate_till'];
			$qdate_till_max = $ret_dt['qdate_till_max'];
			$add_where = "(a.payment_date >= '".$qdate_from."' AND a.payment_date <= '".$qdate_till_max."')";
			
			$this->db->select("a.*, a.id as billing_id, a.updated as billing_date, d.payment_type_name, e.bank_name, f.sales_name, f.sales_company");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT');
			$this->db->join($this->prefix.'bank as e','e.id = a.bank_id','LEFT');
			$this->db->join($this->prefix.'sales as f','f.id = a.sales_id','LEFT');
			$this->db->where("a.billing_status", 'paid');
			$this->db->where("a.is_deleted", 0);
			$this->db->where("a.sales_id > 0");
			$this->db->where($add_where);
			
			if(!empty($sales_id)){
				$this->db->where("a.sales_id", $sales_id);
			}
			
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
			
			$sales_name_report = '';
			$all_group_date = array();		  
			$all_bil_id = array();	  
			$all_bil_id_date = array();
			$newData = array();
			$dt_payment = array();
			$no_id = 1;
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					$s['billing_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['payment_date'] = date("d-m-Y H:i",strtotime($s['payment_date']));
					
					if(!in_array($s['id'], $all_bil_id)){
						$all_bil_id[] = $s['id'];
					}			
					
					if(!empty($sales_id)){
						$sales_name_report = $s['sales_name'].' - '.$s['sales_company'];
					}
					
					//CHECK REAL TOTAL BILLING
					if(!empty($s['include_tax']) OR !empty($s['include_service'])){
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
					}
					
					if(!empty($s['is_compliment'])){
						$s['total_billing'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
						//if(!empty($s['include_tax']) OR !empty($s['include_service'])){
						//	$s['total_billing'] = $s['total_billing'];
						//}
						$s['service_total'] = 0;
						$s['tax_total'] = 0;
					}
					
					
					//diskon_sebelum_pajak_service
					if($data_post['diskon_sebelum_pajak_service'] == 0){
						$s['sub_total'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
					}else{
						$s['sub_total'] = $s['total_billing'] - $s['discount_total'] + $s['tax_total'] + $s['service_total'];
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
					$s['grand_total_show'] = priceFormat($s['grand_total']);
					$s['total_billing_show'] = priceFormat($s['total_billing']);
					$s['total_paid_show'] = priceFormat($s['total_paid']);
					$s['tax_total_show'] = priceFormat($s['tax_total']);
					$s['service_total_show'] = priceFormat($s['service_total']);
					$s['discount_total_show'] = priceFormat($s['discount_total']);
					$s['discount_billing_total_show'] = priceFormat($s['discount_billing_total']);
					
					$s['total_sales_fee'] = 0;
					if(!empty($s['sales_id'])){
						
						$use_sales_total = $s['sub_total'];
						if($s['sales_type'] == 'before_tax'){
							$use_sales_total = $s['total_billing'];
						}
						
						if(!empty($s['sales_price'])){
							$s['total_sales_fee'] = $s['sales_price'];
						}else{
							$s['total_sales_fee'] = $use_sales_total * $s['sales_percentage']/100;
						}
						
						
					}
					
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
					}else{
					
						if(!empty($s['is_half_payment'])){
							$s['payment_note'] = 'HALF PAYMENT';
						}
						
						if(strtolower($s['payment_type_name']) != 'cash'){
							$s['payment_note'] = strtoupper($s['bank_name']).' '.$card_no;
						}
					}
					
					//REKAP TGL
					$payment_date = date("d-m-Y",strtotime($s['payment_date']));
					$group_id = $s['sales_id'].'_'.$payment_date;
					if(empty($all_group_date[$group_id])){
						$all_group_date[$group_id] = array(
							'id'		=> $no_id, 
							'item_no'	=> $no_id, 
							'date'		=> $payment_date, 
							'qty_billing'		=> 0, 
							'total_billing'		=> 0, 
							'total_billing_show'=> 0,
							'tax_total'			=> 0, 
							'tax_total_show'	=> 0, 
							'service_total'		=> 0, 
							'service_total_show'=> 0,
							'discount_total'	=> 0, 
							'discount_total_show'=> 0, 
							'discount_billing_total'	=> 0, 
							'discount_billing_total_show'=> 0, 
							'total_dp'			=> 0, 
							'total_dp_show'		=> 0, 
							'total_sales_fee'		=> 0, 
							'total_sales_fee_show'	=> 0, 
							'sales_name'	=> $s['sales_name'], 
							'sales_company'	=> $s['sales_company'], 
							'grand_total'		=> 0, 
							'grand_total_show'	=> 0,
							'sub_total'		=> 0, 
							'sub_total_show'	=> 0,
							'total_pembulatan'		=> 0, 
							'total_pembulatan_show'	=> 0, 
							//'total_cash'		=> 0, 
							//'total_cash_show'	=> 0,
							//'total_debit'		=> 0, 
							//'total_debit_show'	=> 0,
							//'total_credit'		=> 0, 
							//'total_credit_show'	=> 0,
							'total_compliment'		=> 0, 
							'total_compliment_show'	=> 0,
							'total_hpp'			=> 0, 
							'total_hpp_show'	=> 0, 
							'total_profit'		=> 0, 
							'total_profit_show'=> 0
						);
						
						foreach($payment_data as $key_id => $dtPay){
							$all_group_date[$group_id]['total_payment_'.$key_id] = 0;
							$all_group_date[$group_id]['total_payment_'.$key_id.'_show'] = 0;
						}
						
						$no_id++;
					}
					
					$all_bil_id_date[$s['billing_id']] = $group_id;
					
					$all_group_date[$group_id]['qty_billing'] += 1;
					$all_group_date[$group_id]['total_billing'] += $s['total_billing'];
					$all_group_date[$group_id]['tax_total'] += $s['tax_total'];
					$all_group_date[$group_id]['service_total'] += $s['service_total'];
					$all_group_date[$group_id]['discount_total'] += $s['discount_total'];
					$all_group_date[$group_id]['discount_billing_total'] += $s['discount_billing_total'];
					$all_group_date[$group_id]['total_dp'] += $s['total_dp'];
					$all_group_date[$group_id]['total_sales_fee'] += $s['total_sales_fee'];
					$all_group_date[$group_id]['grand_total'] += $s['grand_total'];
					$all_group_date[$group_id]['grand_total'] -= $s['compliment_total'];
					$all_group_date[$group_id]['sub_total'] += $s['sub_total'];
					$all_group_date[$group_id]['total_pembulatan'] += $s['total_pembulatan'];
					$all_group_date[$group_id]['total_compliment'] += $s['compliment_total'];
					
					/* if(!empty($s['discount_total'])){
						echo '<pre>';
						print_r($s);
					} */
					
					if(!empty($s['is_compliment'])){
						$all_group_date[$group_id]['total_compliment'] += $s['grand_total'];
					}else{
					
						/* if(!empty($s['is_half_payment'])){
							$all_group_date[$group_id]['total_cash'] += $s['total_cash'];
							$all_group_date[$group_id]['total_credit'] += $s['total_credit'];
						}else{
							if($s['payment_id'] == 1){
								//cash
								$all_group_date[$group_id]['total_cash'] += $s['grand_total'];
							}else{
								$all_group_date[$group_id]['total_credit'] += $s['grand_total'];
							}
						} */
						
						if(!empty($payment_data)){
							foreach($payment_data as $key_id => $dtPay){
						
								$tot_payment = 0;
								$tot_payment_show = 0;
								if($s['payment_id'] == $key_id){
									//$tot_payment = $s['grand_total'];
									//$tot_payment_show = $s['grand_total_show'];
									
									if($key_id == 2 OR $key_id == 3 OR $key_id == 4){
										$tot_payment = $s['total_credit'];	
									}else{
										$tot_payment = $s['total_cash'];	
									}
									
									//FIX PEMBULATAN
									/*if($tot_payment < $s['grand_total']){
										$gap = ($s['grand_total'] - $s['total_dp']) - $tot_payment;
										if($gap < 100){
											$tot_payment += $s['total_pembulatan'];
										}
									}*/
									
									$tot_payment_show = priceFormat($tot_payment);
									
									//credit half payment
									if(!empty($s['is_half_payment']) AND $key_id != 1){
										$tot_payment = $s['total_credit'];
										$tot_payment_show = priceFormat($s['total_credit']);
									}else{
										
										/*if($tot_payment <= $s['grand_total']){
											//$tot_payment_show .= '='.$tot_payment.'x'.$s['grand_total'];
											$tot_payment = $s['grand_total'];
											$tot_payment_show = priceFormat($tot_payment);
												
											if(!empty($s['discount_total'])){
												$tot_payment = $tot_payment - $s['discount_total'];
												$tot_payment_show = priceFormat($tot_payment);
											}
										}else{
											$tot_payment_show .= '='.$tot_payment.'z'.$s['grand_total'];
										}
										*/
										
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
						
								if(!empty($s['discount_total']) AND !empty($tot_payment)){
									//$tot_payment = $tot_payment - $s['discount_total'];
									//$tot_payment_show = priceFormat($tot_payment);
								}
						
								//$grand_total_payment[$key_id] += $tot_payment;
								$all_group_date[$group_id]['total_payment_'.$key_id] += $tot_payment;
																
							}
						}
						
					}
				
					$newData[$s['id']] = $s;
					//array_push($newData, $s);
					
				}
			}
			
			//calc detail
			$total_hpp = array();
			if(!empty($all_bil_id)){
				$all_bil_id_txt = implode(",",$all_bil_id);
				$this->db->from($this->table2);
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
							$group_id = $all_bil_id_date[$dtRow->billing_id];
							
							if(empty($total_hpp[$group_id])){
								$total_hpp[$group_id] = 0;
							}
							$total_hpp[$group_id] += $dtRow->product_price_hpp * $total_qty;
						}
			
						
					}
				}
			}
			
			$newData = array();
			if(!empty($all_group_date)){
				foreach($all_group_date as $key => $detail){
					
					$detail['total_billing_show'] = priceFormat($detail['total_billing']);
					$detail['tax_total_show'] = priceFormat($detail['tax_total']);
					$detail['service_total_show'] = priceFormat($detail['service_total']);
					$detail['grand_total_show'] = priceFormat($detail['grand_total']);
					$detail['sub_total_show'] = priceFormat($detail['sub_total']);
					$detail['total_pembulatan_show'] = priceFormat($detail['total_pembulatan']);
					
					//$detail['total_cash_show'] = priceFormat($detail['total_cash']);
					//$detail['total_credit_show'] = priceFormat($detail['total_credit']);
					
					foreach($payment_data as $key_id => $dtPay){
						$detail['total_payment_'.$key_id.'_show'] = priceFormat($detail['total_payment_'.$key_id]);
					}
					
					$detail['discount_total_show'] = priceFormat($detail['discount_total']);
					$detail['discount_billing_total_show'] = priceFormat($detail['discount_billing_total']);
					$detail['total_dp_show'] = priceFormat($detail['total_dp']);
					$detail['total_compliment_show'] = priceFormat($detail['total_compliment']);
					$detail['total_sales_fee_show'] = priceFormat($detail['total_sales_fee']);
					

					if(!empty($total_hpp[$key])){
						$detail['total_hpp'] = $total_hpp[$key];
					}

					$detail['total_profit'] = $detail['total_billing']-$detail['total_hpp'];
					$detail['total_hpp_show'] = priceFormat($detail['total_hpp']);
					$detail['total_profit_show'] = priceFormat($detail['total_profit']);
						
					
					$newData[$key] = $detail;
					
				}
			}	
			
			$newData_switch = $newData;
			$newData = array();
			if(!empty($newData_switch)){
				foreach($newData_switch as $dt){
					$newData[] = $dt;
				}
			}
			
			//echo '<pre>';
			//print_r($newData);
			//die();
			
			$data_post['report_data'] = $newData;
			$data_post['payment_data'] = $dt_payment_name;
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		

		$useview = 'print_reportSalesFeeRecap';
		$data_post['report_name'] = 'SALES FEE REPORT (RECAP)';
		$data_post['sales_name_report'] = $sales_name_report;
		
		if($do == 'excel'){
			$useview = 'excel_reportSalesFeeRecap';
		}
		
		$this->load->view('../../billing/views/'.$useview, $data_post);	
	}
	
}