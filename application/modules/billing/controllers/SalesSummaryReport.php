<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class SalesSummaryReport extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_databilling', 'm');
		$this->load->model('model_billingdetail', 'm2');
	}
	
	public function print_salesSummaryReport(){
		
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('User Session Expired, Please Re-Login!');
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
			'report_name'	=> 'SALES SUMMARY REPORT',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'user_fullname'	=> $user_fullname,
			'sorting'	=> $sorting,
			'diskon_sebelum_pajak_service' => 0
		);
		
		$get_opt = get_option_value(array('report_place_default','diskon_sebelum_pajak_service','cashier_max_pembulatan','cashier_pembulatan_keatas','pembulatan_dinamis'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(!empty($get_opt['diskon_sebelum_pajak_service'])){
			$data_post['diskon_sebelum_pajak_service'] = $get_opt['diskon_sebelum_pajak_service'];
		}else{
			$get_opt['diskon_sebelum_pajak_service'] = 0;
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
		
		if(empty($date_from) OR empty($date_till)){
			die('Billing Paid Not Found!');
		}else{
				
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till = date("Y-m-d",strtotime($date_till));
			$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
			
			$add_where = "(a.payment_date >= '".$qdate_from." 07:00:01' AND a.payment_date <= '".$qdate_till_max." 06:00:00')";
			
			$billing_buyget = array();
			$billing_buyget_discount_id = array();
			$billing_promo = array();
			$billing_promo_discount_id = array();
			//if(!empty($discount_type)){
				//if(!in_array($discount_type, array('no_promo','billing'))){
					//BUYGET 
					$this->db->select('b.billing_id, b.is_buyget, b.buyget_id, b.is_promo, b.promo_id');
					$this->db->from($this->table2.' as b');
					$this->db->join($this->table.' as a',"a.id = b.billing_id","LEFT");
					$this->db->where("a.billing_status", 'paid');
					$this->db->where("a.is_deleted", 0);
					$this->db->where("(b.is_buyget = 1 OR b.is_promo = 1)");
					$this->db->where($add_where);
					$get_buyget = $this->db->get();
					if($get_buyget->num_rows() > 0){
						foreach($get_buyget->result() as $dtRow){
							
							if($dtRow->is_buyget == 1){
								if(!in_array($dtRow->billing_id, $billing_buyget)){
									$billing_buyget[] = $dtRow->billing_id;
									$billing_buyget_discount_id[$dtRow->billing_id] = $dtRow->buyget_id;
								}
							}
							
							if($dtRow->is_promo == 1){
								if(!in_array($dtRow->billing_id, $billing_buyget)){
									$billing_promo[] = $dtRow->billing_id;
									$billing_promo_discount_id[$dtRow->billing_id] = $dtRow->promo_id;
								}
							}
						}
					}
				//}
			//}
			
			$this->db->select("a.*, a.id as billing_id, a.updated as billing_date, d.payment_type_name, e.bank_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT');
			$this->db->join($this->prefix.'bank as e','e.id = a.bank_id','LEFT');
			$this->db->where("a.billing_status", 'paid');
			$this->db->where("a.is_deleted", 0);
			$this->db->where($add_where);
			
			if(!empty($discount_type)){
				if($discount_type == 'no_promo'){
					$this->db->where("((a.discount_id = 0 OR a.discount_id IS NULL) AND (a.discount_total = 0 OR a.discount_total IS NULL))");
				}
				if($discount_type == 'item'){
					$this->db->where("((a.discount_id > 0 AND discount_perbilling = 0) OR  (a.discount_id = 0 AND (a.discount_total != 0 OR a.discount_total IS NOT NULL)))");
				}
				if($discount_type == 'billing'){
					$this->db->where("((a.discount_id > 0 AND discount_perbilling = 1) OR  (a.discount_id = 0 AND (a.discount_total != 0 OR a.discount_total IS NOT NULL)))");
				}
				
				if(!empty($billing_buyget)){
					$billing_buyget_all = implode(",", $billing_buyget);
					$this->db->or_where("a.id IN (".$billing_buyget_all.")");
				}
				
				if(!empty($billing_promo)){
					$billing_promo_all = implode(",", $billing_promo);
					$this->db->or_where("a.id IN (".$billing_promo_all.")");
				}
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
			
			
			//DATA DISCOUNT
			$discount_data = array();
			$discount_data[0] = 'NO PROMO';
			$this->db->from($this->prefix.'discount');
			$get_discount = $this->db->get();
			if($get_discount->num_rows() > 0){
				foreach($get_discount->result() as $dtRow){
					$discount_data[$dtRow->id] = $dtRow->discount_name;
				}
			}
			$all_discount_id = array();
			
			
			$qdate_from_mk = strtotime($date_from." 00:00:01");
			$qdate_till_mk = strtotime($date_till." 23:59:59");
			$total_day = ceil(($qdate_till_mk-$qdate_from_mk) / ONE_DAY_UNIX);
			
			$data_post['summary_data'] = array(
				'total_billing'	=> 0,
				'total_discount_item'	=> 0,
				'total_discount_billing'	=> 0,
				'net_sales'	=> 0,
				'service_total'	=> 0,
				'tax_total'	=> 0,
				'total_pembulatan'	=> 0,
				'grand_total'	=> 0,
				'total_of_item_discount'	=> 0,
				'total_of_billing'	=> 0,
				'total_of_guest'	=> 0,
				'total_day'	=> $total_day,
				'sales_without_service'	=> 0,
				'sales_without_tax'	=> 0,
				'sales_per_guest'	=> 0,
				'sales_per_bill'	=> 0,
				'average_daily_guest'	=> 0,
				'average_daily_billing'	=> 0,
				'average_daily_sales'	=> 0,
			);
			
			
			//SUMMARY SALES PER PERIODE
			$periode_cat = array(
				1 => '00:00 - 05:59',
				2 => '06:00 - 11:59',
				3 => '12:00 - 17:59',
				4 => '18:00 - 23:59'
			);
		
			$summary_sales_periode = array();
			$summary_sales_periode_billing_id = array();
			$summary_promo = array();
			$summary_promo_bill_id = array(); //asumsi billing id 
			$summary_paket = array();
			$summary_payment = array();
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
					
					if(!empty($s['is_compliment'])){
						$s['total_billing'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
						$s['service_total'] = 0;
						$s['tax_total'] = 0;
					}
					
					//diskon_sebelum_pajak_service
					if($get_opt['diskon_sebelum_pajak_service'] == 0){
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
					if($get_opt['diskon_sebelum_pajak_service'] == 0){
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
					$data_post['summary_data']['grand_total'] += $s['grand_total'];
					$data_post['summary_data']['total_of_guest'] += $s['total_guest'];
					$data_post['summary_data']['total_of_billing'] += 1;
					
					if($s['service_total'] == 0){
						$data_post['summary_data']['sales_without_service'] += $s['grand_total'];
					}
					if($s['tax_total'] == 0){
						$data_post['summary_data']['sales_without_tax'] += $s['grand_total'];
					}
					
					//SUMMARY PERIODE
					$jam_billing = date("Hi",strtotime($s['created']));
					$jam_billing += 10000;
					$jam_billing -= 10000;
					
					$tipe_periode = 1;
					if($jam_billing >= 600 AND $jam_billing <= 1159){
						$tipe_periode = 2;
					}else
					if($jam_billing >= 1200 AND $jam_billing <= 1759){
						$tipe_periode = 3;
					}else
					if($jam_billing >= 1800 AND $jam_billing <= 2359){
						$tipe_periode = 4;
					}
					
					if(empty($summary_sales_periode[$tipe_periode])){
						
						$periode_name = '00:00 - 05:59';
						if(!empty($periode_cat[$tipe_periode])){
							$periode_name = $periode_cat[$tipe_periode];
						}
						
						$summary_sales_periode[$tipe_periode] = array(
							'tipe_periode'	=> $tipe_periode,
							'periode_name'	=> $periode_name,
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
								$summary_sales_periode[$tipe_periode]['payment_'.$key_id] = 0;	
								$summary_sales_periode[$tipe_periode]['payment_'.$key_id.'_show'] = 0;						
							}
						}
						
					}
					
					$summary_sales_periode[$tipe_periode]['total_billing'] += $s['total_billing'];
					$summary_sales_periode[$tipe_periode]['discount_total'] += $s['discount_total'];
					$summary_sales_periode[$tipe_periode]['discount_billing_total'] += $s['discount_billing_total'];
					$summary_sales_periode[$tipe_periode]['tax_total'] += $s['tax_total'];
					$summary_sales_periode[$tipe_periode]['service_total'] += $s['service_total'];
					$summary_sales_periode[$tipe_periode]['sub_total'] += $s['sub_total'];
					$summary_sales_periode[$tipe_periode]['net_sales'] += $s['net_sales'];
					$summary_sales_periode[$tipe_periode]['total_pembulatan'] += $s['total_pembulatan'];
					$summary_sales_periode[$tipe_periode]['total_compliment'] += $s['total_compliment'];
					$summary_sales_periode[$tipe_periode]['grand_total'] += $s['grand_total'];
					$summary_sales_periode[$tipe_periode]['compliment_total'] += $s['compliment_total'];
					$summary_sales_periode[$tipe_periode]['total_dp'] += $s['total_dp'];
					$summary_sales_periode[$tipe_periode]['total_qty'] += 1;
					
					//all billing id -> has tipe periode
					if(empty($summary_sales_periode_billing_id[$tipe_periode])){
						$summary_sales_periode_billing_id[$tipe_periode] = array();
					}
					$summary_sales_periode_billing_id[$tipe_periode][] = $s['billing_id'];
					
					//SUMMARY PROMO
					if(empty($s['discount_id'])){
						$s['discount_id'] = 0;
					}
					$var_promo = $s['discount_id'];
					if(empty($summary_promo[$var_promo])){
						
						$discount_name = 'NO PROMO';
						if(!empty($discount_data[$s['discount_id']])){
							$discount_name = $discount_data[$s['discount_id']];
						}
						
						$summary_promo[$var_promo] = array(
							'discount_id'	=> $s['discount_id'],
							'discount_name'	=> $discount_name,
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
								$summary_promo[$var_promo]['payment_'.$key_id] = 0;	
								$summary_promo[$var_promo]['payment_'.$key_id.'_show'] = 0;						
							}
						}
						
					}
					
					if(!empty($billing_buyget_discount_id[$s['id']])){
						$summary_promo_bill_id[$s['id']] = $s;
					}else{
						
						if(!empty($billing_promo_discount_id[$s['id']])){
							$summary_promo_bill_id[$s['id']] = $s;
						}else{
							
							if(!empty($s['discount_id'])){
								$summary_promo[$var_promo]['total_qty'] += 1;
								$summary_promo[$var_promo]['total_billing'] += $s['total_billing'];
								$summary_promo[$var_promo]['discount_total'] += $s['discount_total'];
								$summary_promo[$var_promo]['discount_billing_total'] += $s['discount_billing_total'];
								$summary_promo[$var_promo]['tax_total'] += $s['tax_total'];
								$summary_promo[$var_promo]['service_total'] += $s['service_total'];
								$summary_promo[$var_promo]['sub_total'] += $s['sub_total'];
								$summary_promo[$var_promo]['net_sales'] += $s['net_sales'];
								$summary_promo[$var_promo]['total_pembulatan'] += $s['total_pembulatan'];
								$summary_promo[$var_promo]['total_compliment'] += $s['total_compliment'];
								$summary_promo[$var_promo]['grand_total'] += $s['grand_total'];
								$summary_promo[$var_promo]['compliment_total'] += $s['compliment_total'];
								$summary_promo[$var_promo]['total_dp'] += $s['total_dp'];
							}
							
						}
						
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
					$summary_payment[$var_payment]['total_compliment'] += $s['total_compliment'];
					$summary_payment[$var_payment]['grand_total'] += $s['grand_total'];
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
							
							if(!empty($s['discount_id'])){
								$summary_promo[$var_promo]['payment_'.$key_id] += $tot_payment;
							}
							
							$summary_payment[$var_payment]['payment_'.$key_id] += $tot_payment;
							$summary_sales_periode[$tipe_periode]['payment_'.$key_id] += $tot_payment;
															
						}
					}
					
					
					$newData[$s['id']] = $s;
					
				}
			}
			
			//echo '<pre>TOT:'.count($summary_promo);
			//print_r($summary_promo);
			//die();
			
			//DETAIL BILLING
			$billing_id_qty_hpp = array();
			$all_billing_id_on_summary = array();
			$billing_id_summary_group = array();
			$konversi_pembulatan_billing = array();
			$balancing_discount_billing = array();
			$all_product_data = array();
			$all_product_data_package = array();
			$total_hpp = array();
			$discount_item = array();
			$all_discount_id = array();
			$summary_promo_bill_id_done = array();
			if(!empty($all_bil_id)){
				$all_bil_id_txt = implode(",",$all_bil_id);
				$this->db->select("a.*, b.payment_date, b.billing_no, b.total_billing, b.discount_perbilling, b.payment_id, 
								b.discount_percentage as billing_discount_percentage, b.discount_total as billing_discount_total, 
								b.total_pembulatan as billing_total_pembulatan, b.discount_id as billing_discount_id, b.bank_id,
								c.product_name, c.product_type, c.product_group, c.category_id, d.product_category_name as category_name");
				$this->db->from($this->table2." as a");
				$this->db->join($this->prefix.'billing as b','b.id = a.billing_id','LEFT');
				$this->db->join($this->prefix.'product as c','c.id = a.product_id','LEFT');
				$this->db->join($this->prefix.'product_category as d','d.id = c.category_id','LEFT');
				$this->db->where("a.is_deleted", 0);
				$this->db->where("b.is_deleted", 0);
				$this->db->where("b.billing_status", "paid");			
				$this->db->order_by("c.product_name", 'ASC');
				
				$this->db->where('a.billing_id IN ('.$all_bil_id_txt.')');
				
				$get_detail = $this->db->get();
				if($get_detail->num_rows() > 0){
					foreach($get_detail->result_array() as $s){
						
						$total_qty = $s['order_qty'];
						//HPP
						if(empty($total_hpp[$s['billing_id']])){
							$total_hpp[$s['billing_id']] = 0;
						}
						$total_hpp[$s['billing_id']] += $s['product_price_hpp'] * $s['order_qty'];
						
						//HPP PROMO & QTY
						if(empty($s['billing_discount_id'])){
							$s['billing_discount_id'] = 0;
						}
						
						if(!empty($summary_payment[$s['bank_id']])){
							//$summary_payment[$s['bank_id']]['total_qty'] += $s['order_qty'];
							$summary_payment[$s['bank_id']]['total_hpp'] += ($s['product_price_hpp'] * $s['order_qty']);
						}
						
						if(empty($billing_id_qty_hpp[$s['billing_id']])){
							$billing_id_qty_hpp[$s['billing_id']] = array('total_qty' => 0, 'total_hpp'	=> 0);
						}
						$billing_id_qty_hpp[$s['billing_id']]['total_qty'] += $s['order_qty'];
						$billing_id_qty_hpp[$s['billing_id']]['total_hpp'] += ($s['product_price_hpp'] * $s['order_qty']);

						$has_discount_on_detail = 0;
						
						//PROMO BUY GET
						if(!empty($summary_promo_bill_id[$s['billing_id']])){
							if(!in_array($s['billing_id'], $summary_promo_bill_id_done)){
								$has_discount_on_detail = $s['discount_id'];
								
								if(empty($has_discount_on_detail)){
									$has_discount_on_detail = 0;
								}
								
								if($s['is_buyget'] == 1 AND !empty($s['buyget_id'])){
									$has_discount_on_detail = $s['buyget_id'];
								}
								
								if($s['is_promo'] == 1 AND !empty($s['promo_id'])){
									$has_discount_on_detail = $s['promo_id'];
								}
								
								if(!empty($billing_buyget_discount_id[$s['billing_id']])){
									$has_discount_on_detail = $billing_buyget_discount_id[$s['billing_id']];
								}
								
								if(!empty($billing_promo_discount_id[$s['billing_id']])){
									$has_discount_on_detail = $billing_promo_discount_id[$s['billing_id']];
								}
								
								if(!empty($has_discount_on_detail)){
									$summary_promo_bill_id_done[] = $s['billing_id'];
								}
								
								$dt_bill = $summary_promo_bill_id[$s['billing_id']];
								
								$new_var = $has_discount_on_detail;
								
								$get_discount_type = 'NO PROMO';
								$get_discount_type_var = 'no_promo';
								
								if(!empty($has_discount_on_detail)){
									
									if(!in_array($has_discount_on_detail, $all_discount_id)){
										$all_discount_id[] = $has_discount_on_detail;
									}
									
									if($dt_bill['discount_perbilling'] == 1){
										$get_discount_type = 'BILLING';
										$get_discount_type_var = 'billing';
									}else{
										$get_discount_type = 'ITEM';
										$get_discount_type_var = 'item';
									}
								}
								
								$var_promo = $has_discount_on_detail;
								if(!empty($has_discount_on_detail)){
									
									$discount_item[$s['billing_id']] = $has_discount_on_detail;
									$data_post['summary_data']['total_of_item_discount'] += 1;
							
									//SUMMARY PROMO
									$var_promo = $has_discount_on_detail;
									if(empty($summary_promo[$var_promo])){
								
										$discount_name = 'NO PROMO';
										if(!empty($discount_data[$has_discount_on_detail])){
											$discount_name = $discount_data[$has_discount_on_detail];
										}
										
										$summary_promo[$var_promo] = array(
											'discount_id'	=> $has_discount_on_detail,
											'discount_name'	=> $discount_name,
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
												$summary_promo[$var_promo]['payment_'.$key_id] = 0;	
												$summary_promo[$var_promo]['payment_'.$key_id.'_show'] = 0;						
											}
										}
										
									}
									
									$all_billing_discount_id[$s['billing_id']] = $var_promo;
									$summary_promo[$var_promo]['total_billing'] += $dt_bill['total_billing'];
									$summary_promo[$var_promo]['discount_total'] += $dt_bill['discount_total'];
									$summary_promo[$var_promo]['discount_billing_total'] += $dt_bill['discount_billing_total'];
									$summary_promo[$var_promo]['tax_total'] += $dt_bill['tax_total'];
									$summary_promo[$var_promo]['service_total'] += $dt_bill['service_total'];
									$summary_promo[$var_promo]['sub_total'] += $dt_bill['sub_total'];
									$summary_promo[$var_promo]['net_sales'] += $dt_bill['net_sales'];
									$summary_promo[$var_promo]['total_pembulatan'] += $dt_bill['total_pembulatan'];
									$summary_promo[$var_promo]['total_compliment'] += $dt_bill['total_compliment'];
									$summary_promo[$var_promo]['grand_total'] += $dt_bill['grand_total'];
									$summary_promo[$var_promo]['compliment_total'] += $dt_bill['compliment_total'];
									$summary_promo[$var_promo]['total_dp'] += $dt_bill['total_dp'];
									//$summary_promo[$var_promo]['total_qty'] += 1;
									//$summary_promo[$var_promo]['total_qty'] += $total_qty;
									
									//echo '<br/>BILLING #'.$dtRow->billing_id.' -> '.$has_discount_on_detail.' :'.$newData[$new_var]['total_qty_billing'];
									
									if(!empty($payment_data)){
										foreach($payment_data as $key_id => $dtPay){
									
											$tot_payment = 0;
											$tot_payment_show = 0;
											if($dt_bill['payment_id'] == $key_id){
												//$tot_payment = $dt_bill['grand_total'];
												//$tot_payment_show = $dt_bill['grand_total_show'];
												
												if($key_id == 3 OR $key_id == 2){
													$tot_payment = $dt_bill['total_credit'];	
												}else{
													$tot_payment = $dt_bill['total_cash'];	
												}
												
												$tot_payment_show = priceFormat($tot_payment);
												
												//credit half payment
												if(!empty($dt_bill['is_half_payment']) AND $key_id != 1){
													$tot_payment = $dt_bill['total_credit'];
													$tot_payment_show = priceFormat($dt_bill['total_credit']);
												}else{
													
													$tot_payment_show = priceFormat($tot_payment);	
												}
													
											}else{
												//cash
												if(!empty($dt_bill['is_half_payment']) AND $key_id == 1){
													$tot_payment = $dt_bill['total_cash'];
													$tot_payment_show = priceFormat($dt_bill['total_cash']);
												}
											}
									
											if(empty($grand_total_payment[$key_id])){
												$grand_total_payment[$key_id] = 0;
											}
									
											if(!empty($dt_bill['is_compliment'])){
												$tot_payment = 0;
												$tot_payment_show = 0;
											}
											
											if(!empty($var_promo)){
												$summary_promo[$var_promo]['payment_'.$key_id] += $tot_payment;
											}
																			
										}
									}
									
								}
						
								if(!empty($summary_promo[$has_discount_on_detail])){
									//if(!in_array($dtRow->billing_id, $summary_promo_bill_id_done)){
										$summary_promo[$has_discount_on_detail]['total_qty'] += 1;
										$summary_promo[$has_discount_on_detail]['total_hpp'] += ($s['product_price_hpp'] * $s['order_qty']);
									//}
								}
								
							}
							
						}else{
							if(!empty($all_billing_discount_id[$s['billing_id']])){
								$get_disc_id = $all_billing_discount_id[$s['billing_id']];
								if(!empty($summary_promo[$get_disc_id])){
									$summary_promo[$get_disc_id]['total_qty'] += 1;
									$summary_promo[$get_disc_id]['total_hpp'] += ($s['product_price_hpp'] * $s['order_qty']);
								}
							}
						}
						
						//SUMMARY BILLING
						$billing_tipe = 'DINE IN';
						if($s['is_takeaway'] == 1){
							$billing_tipe = 'TAKE AWAY';
						}
						
						if(!in_array($s['billing_id'], $all_billing_id_on_summary)){
							$all_billing_id_on_summary[] = $s['billing_id'];
							
							if(empty($billing_id_summary_group[$billing_tipe])){
								$billing_id_summary_group[$billing_tipe] = array();
							}
							
							if(!in_array($s['billing_id'], $billing_id_summary_group[$billing_tipe])){
								$billing_id_summary_group[$billing_tipe][] = $s['billing_id'];
							}
							
							if(!empty($summary_billing[$billing_tipe])){
								//$summary_billing[$billing_tipe]['total_qty'] += $s['order_qty'];
								$summary_billing[$billing_tipe]['total_hpp'] += ($s['product_price_hpp'] * $s['order_qty']);
							}
							
						}
						
						
						if(empty($all_product_data[$s['product_id']] )){
						
							$all_product_data[$s['product_id']] = array(
								'billing_id'	=> $s['billing_id'],
								'product_id'	=> $s['product_id'],
								'product_name'	=> $s['product_name'],
								'product_group'	=> $s['product_group'],
								'category_id'	=> $s['category_id'],
								'category_name'	=> $s['category_name'],
								'total_qty'	=> 0,
								'total_billing'	=> 0,
								'total_billing_show'	=> 0,
								'sub_total'	=> 0,
								'sub_total_show'	=> 0,
								'net_sales'	=> 0,
								'net_sales_show'	=> 0,
								'grand_total'	=> 0,
								'grand_total_show'	=> 0,
								'tax_total'	=> 0,
								'tax_total_show'	=> 0,
								'total_pembulatan'	=> 0,
								'total_pembulatan_show'	=> 0,
								'service_total'	=> 0,
								'service_total_show'	=> 0,
								'discount_total'	=> 0,
								'discount_total_show'	=> 0,
								'discount_billing_total'	=> 0,
								'discount_billing_total_show'	=> 0,
								'total_hpp'	=> 0,
								'total_hpp_show'	=> 0,
								'total_profit'	=> 0,
								'total_profit_show'	=> 0,
								'is_takeaway'	=> 0,
								'is_compliment'	=> 0,
								'compliment_total'	=> 0,
								'compliment_total_show'	=> 0,
								'total_dp'	=> 0,
								'total_dp_show'	=> 0,
							);
							
						}
						
						$all_product_data[$s['product_id']]['total_qty'] += $s['order_qty'];
						
						
						//PAKET
						if($s['product_type'] == 'package'){
							if(!in_array($s['product_type'], $all_product_data_package)){
								$all_product_data_package[] = $s['product_id'];
							}
						}
						
						//CHECK IF INCLUDE TAX AND SERVICE
						$is_include = false;
						$all_percentage = 100;
						if($s['include_tax'] == 1){
							$is_include = true;
							$all_percentage += $s['tax_percentage'];
						}
						
						if($s['include_service'] == 1){
							$is_include = true;		
							$all_percentage += $s['service_percentage'];		
						}
						
						$grand_total_order = 0;
						if(!empty($s['is_compliment'])){
							$s['tax_total'] = 0;
							$s['service_total'] = 0;
						}
						
						$include_tax = $s['include_tax'];
						$include_service = $s['include_service'];
						$tax_percentage = $s['tax_percentage'];
						$service_percentage = $s['service_percentage'];
						$tax_total = 0;
						$service_total = 0;
						$product_price_real = 0;
						
						//cek if discount is disc billing
						$total_discount_product = 0;
						if($s['discount_perbilling'] == 1){
							$get_percentage = $s['billing_discount_percentage'];
							if(empty($s['billing_discount_percentage']) OR $s['billing_discount_percentage'] == '0.00'){
								$get_percentage = ($s['billing_discount_total'] / $s['total_billing']) * 100;
								$get_percentage = number_format($get_percentage,0);
							}
							
							$s['discount_total'] = priceFormat(($s['product_price_real']*($get_percentage/100)), 0, ".", "");
							$all_product_data[$s['product_id']]['discount_billing_total'] += ($s['discount_total']*$s['order_qty']);
							$total_discount_product = ($s['discount_total']*$s['order_qty']);
							
						}else{
							$all_product_data[$s['product_id']]['discount_total'] += $s['discount_total'];
							$total_discount_product = ($s['discount_total']);
						}
						
						if(!empty($include_tax) OR !empty($include_service)){
							
							if($get_opt['diskon_sebelum_pajak_service'] == 1){
								
								$all_product_data[$s['product_id']]['grand_total'] += ($s['product_price_real']*$s['order_qty']) - $s['discount_total'];
								$grand_total_order = ($s['product_price_real']*$s['order_qty']) - $s['discount_total'];
								
							}else{
								
								$all_product_data[$s['product_id']]['grand_total'] += ($s['product_price_real']*$s['order_qty']);
								$grand_total_order = ($s['product_price_real']*$s['order_qty']);
								
							}
							
							$all_product_data[$s['product_id']]['total_billing'] += ($s['product_price_real']*$s['order_qty']);
							$all_product_data[$s['product_id']]['tax_total'] += $s['tax_total'];
							$all_product_data[$s['product_id']]['service_total'] += $s['service_total'];
							
						}else
						{
								
							if($get_opt['diskon_sebelum_pajak_service'] == 1){
								
								$all_product_data[$s['product_id']]['grand_total'] += ($s['product_price']*$s['order_qty']) - $s['discount_total'];
								$grand_total_order = ($s['product_price']*$s['order_qty']) - $s['discount_total'];
							
							}else{
								
								$all_product_data[$s['product_id']]['grand_total'] += ($s['product_price']*$s['order_qty']);
								$grand_total_order = ($s['product_price']*$s['order_qty']);
							
							}
							
							$all_product_data[$s['product_id']]['total_billing'] += ($s['product_price']*$s['order_qty']);
							$all_product_data[$s['product_id']]['tax_total'] += $s['tax_total'];
							$all_product_data[$s['product_id']]['service_total'] += $s['service_total'];
						}
						
						$all_product_data[$s['product_id']]['total_hpp'] += ($s['product_price_hpp']*$s['order_qty']);
						
						
						$all_product_data[$s['product_id']]['grand_total'] += $s['tax_total'];
						$all_product_data[$s['product_id']]['grand_total'] += $s['service_total'];
						
						//BALANCING TOTAL BILLING
						$all_product_data[$s['product_id']]['net_sales'] += $grand_total_order;
						$total_billing = $grand_total_order + $s['discount_total'];
						$grand_total_order += $s['tax_total'];
						$grand_total_order += $s['service_total'];
						$sub_total = $grand_total_order;
						$all_product_data[$s['product_id']]['sub_total'] += $grand_total_order;
						
						
						//PEMBULATAN				
						/*$total_pembulatan = 0;
						$max_pembulatan = $get_opt['cashier_max_pembulatan'];
						$pembulatan_keatas = $get_opt['cashier_pembulatan_keatas'];
						$pembulatan_dinamis = $get_opt['pembulatan_dinamis'];
						$last2digit = substr($grand_total_order,-2);
						$last2digit = intval($last2digit);
						
						//dibawah max pembulatan
						if($last2digit > 0){
							if(empty($pembulatan_keatas)){
								
								//$total_pembulatan = $last2digit;
								$total_pembulatan = $last2digit*-1;
								
								if(!empty($pembulatan_dinamis)){
									if($last2digit <= 50){
										$total_pembulatan = $last2digit*-1;
									}else{
										$total_pembulatan = $max_pembulatan - $last2digit;
									}
								}
							}else{
								
								$total_pembulatan = $max_pembulatan - $last2digit;
								
							}
						}
						
						if($total_pembulatan == $max_pembulatan OR $total_pembulatan == 0){
							$total_pembulatan = 0;
						}
						*/
						
						//OVERRIDE PEMBULATAN PERITEM
						$total_pembulatan = 0;
							
						$all_product_data[$s['product_id']]['total_pembulatan'] += $total_pembulatan;
						$all_product_data[$s['product_id']]['grand_total'] += $total_pembulatan;
						
						$grand_total_order += $total_pembulatan;
						
						
						if(!empty($s['is_compliment'])){
							$all_product_data[$s['product_id']]['compliment_total'] += $grand_total_order;
						}
						
						if(!empty($s['payment_id'])){
							if(empty($all_product_data[$s['product_id']]['payment_'.$s['payment_id']])){
								$all_product_data[$s['product_id']]['payment_'.$s['payment_id']] = 0;
							}
							
							$all_product_data[$s['product_id']]['payment_'.$s['payment_id']] += $grand_total_order;
							
						}
						
						//BALANCING DISKON
						if(!empty($s['billing_discount_total'])){
							if(empty($balancing_discount_billing[$s['billing_id']])){
								$balancing_discount_billing[$s['billing_id']] = array(
									'discount_total'	=> $s['billing_discount_total'],
									'discount_detail_total'	=> 0,
									'payment_id'	=> 0,
									'discount_perbilling'	=> $s['discount_perbilling'],
									'discount_detail'	=> array()
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
						}
						
						//KONVERSI PEMBULATAN PER-ITEM
						if(empty($konversi_pembulatan_billing[$s['billing_id']])){
							$konversi_pembulatan_billing[$s['billing_id']] = array(
								'total_qty'	=> 0,
								'billing_total_pembulatan'	=> $s['billing_total_pembulatan'],
								'total_pembulatan_product'	=> array()
							);
						}
						
						$konversi_pembulatan_billing[$s['billing_id']]['total_qty'] += $s['order_qty'];
						if(empty($konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']])){
							$konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']] = array(
								'total_pembulatan'	=> 0,
								'payment'	=> array()
							);
						}
						$konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']]['total_pembulatan'] = $total_pembulatan;
						if(!empty($s['payment_id'])){
							if(empty($konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']]['payment'][$s['payment_id']])){
								$konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']]['payment'][$s['payment_id']] = 0;
							}
							$konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']]['payment'][$s['payment_id']] += $total_pembulatan;
						}
						
						
					}
				}
			}
			
			//echo '<pre>TOT:'.count($summary_promo);
			//print_r($summary_promo);
			//die();
			
			$data_post['summary_data']['sales_per_guest'] = 0;
			$data_post['summary_data']['average_daily_guest'] = 0;
			$data_post['summary_data']['sales_per_bill'] = 0;
			$data_post['summary_data']['average_daily_billing'] = 0;
			if(!empty($data_post['summary_data']['total_of_guest'])){
				$data_post['summary_data']['sales_per_guest'] = $data_post['summary_data']['grand_total'] / $data_post['summary_data']['total_of_guest'];
				$data_post['summary_data']['average_daily_guest'] = floor($data_post['summary_data']['total_of_guest'] / $total_day);
			}
			
			if(!empty($data_post['summary_data']['total_of_billing'])){
				$data_post['summary_data']['sales_per_bill'] = $data_post['summary_data']['grand_total'] / $data_post['summary_data']['total_of_billing'];
				$data_post['summary_data']['average_daily_billing'] = floor($data_post['summary_data']['total_of_billing'] / $total_day);
			}
			
			if(empty($total_day)){
				$total_day = 1;
			}
			$data_post['summary_data']['average_daily_sales'] = $data_post['summary_data']['grand_total'] / $total_day;
			
			
			//PEMBAGIAN PEMBULATAN AVERAGE
			$konversi_pembulatan_product = array();
			$konversi_pembulatan_product_payment = array();
			$pembulatan_awal_product = array();
			$pembulatan_awal_product_payment = array();
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
									'total_pembulatan' => 0
								);
							}
							if(empty($pembulatan_awal_product[$product_id])){
								$pembulatan_awal_product[$product_id] = 0;
							}
							
							$pembulatan_awal_product[$product_id] += $data['total_pembulatan'];
							
							$konversi_pembulatan_product[$product_id]['total_pembulatan'] += $pembagian_pembulatan;
							if($no == 1 AND $selisih_pembagian != 0){
								$konversi_pembulatan_product[$product_id]['total_pembulatan'] -= $selisih_pembagian;
							}
							
							//PAYMENT
							if(!empty($data['payment'])){
								foreach($data['payment'] as $payment_id => $dtP){
									if(empty($konversi_pembulatan_product_payment[$product_id][$payment_id])){
										$konversi_pembulatan_product_payment[$product_id][$payment_id] = 0;
									}
									if(empty($pembulatan_awal_product_payment[$product_id][$payment_id])){
										$pembulatan_awal_product_payment[$product_id][$payment_id] = 0;
									}
									$pembulatan_awal_product_payment[$product_id][$payment_id] += $dtP;
									
									$konversi_pembulatan_product_payment[$product_id][$payment_id] += $pembagian_pembulatan;
									if($no == 1 AND $selisih_pembagian != 0){
										$konversi_pembulatan_product_payment[$product_id][$payment_id] -= $selisih_pembagian;
									}
									
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
			$data_balancing_diskon = array();
			$data_balancing_diskon_payment = array();
			$data_selisih_diskon = array();
			$data_selisih_diskon_payment = array();
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
									'item'	=> 0,
									'billing'	=> 0
								);
							}
							if(empty($data_balancing_diskon[$product_id])){
								$data_balancing_diskon[$product_id] = array(
									'item'	=> 0,
									'billing'	=> 0
								);
							}
							
							
							if($dt['discount_perbilling'] == 1){
								$data_diskon_awal[$product_id]['billing'] += $dt_diskon['total_discount'];
							}else{
								$data_diskon_awal[$product_id]['item'] += $dt_diskon['total_discount'];
							}
							
							if($dt['discount_perbilling'] == 1){
								$data_balancing_diskon[$product_id]['billing'] += ($dt_diskon['total_discount']+$selisih_diskon_perproduct);
							}else{
								$data_balancing_diskon[$product_id]['item'] += ($dt_diskon['total_discount']+$selisih_diskon_perproduct);
							}
							
							$balancing_discount_billing[$billing_id]['discount_detail'][$product_id]['total_discount_balance'] = ($dt_diskon['total_discount']+$selisih_diskon_perproduct);
							
							if($no == count($dt['discount_detail'])){
								if($discount_detail_total != $dt['discount_total']){
									$selisih_akhir = $dt['discount_total'] - $discount_detail_total;
									
									if($dt['discount_perbilling'] == 1){
										$data_balancing_diskon[$product_id]['billing'] += $selisih_akhir;
									}else{
										$data_balancing_diskon[$product_id]['item'] += $selisih_akhir;
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
									$data_selisih_diskon[$product_id] = 0;
								}
								
								$data_selisih_diskon[$product_id] += $sub_total_selisih;
								
								if(empty($data_selisih_diskon_payment[$product_id])){
									$data_selisih_diskon_payment[$product_id] = array();;
								}
								
								if(empty($data_selisih_diskon_payment[$product_id][$dt['payment_id']])){
									$data_selisih_diskon_payment[$product_id][$dt['payment_id']] = 0;
								}
								
								//echo $product_id.' -> '.$dt['payment_id'].' <br/>';
								$data_selisih_diskon_payment[$product_id][$dt['payment_id']] += $sub_total_selisih;
								
							}
						}
					}
				}
			}
			
			//echo '<pre>';
			//print_r($data_selisih_diskon_payment);
			//die();
			
			$data_post['discount_item'] = $discount_item;
			$data_post['discount_data'] = $discount_data;
			
			//SUMMARY BILLING
			$summary_billing = array();
			$summary_billing['DINE IN'] = array(
				'billing_type'	=> 'DINE IN',
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
			
			$summary_billing['TAKE AWAY'] = array(
				'billing_type'	=> 'TAKE AWAY',
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
					$summary_billing['DINE IN']['payment_'.$key_id] = 0;	
					$summary_billing['DINE IN']['payment_'.$key_id.'_show'] = 0;
					$summary_billing['TAKE AWAY']['payment_'.$key_id] = 0;	
					$summary_billing['TAKE AWAY']['payment_'.$key_id.'_show'] = 0;						
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
					
					//SUMMARY BILLING
					foreach($billing_id_summary_group as $tipe => $billing_id){
						if(in_array($dt['billing_id'], $billing_id_summary_group[$tipe])){
							
							$summary_billing[$tipe]['total_qty'] += 1;
							$summary_billing[$tipe]['total_billing'] += $dt['total_billing'];
							$summary_billing[$tipe]['discount_total'] += $dt['discount_total'];
							$summary_billing[$tipe]['discount_billing_total'] += $dt['discount_billing_total'];
							$summary_billing[$tipe]['tax_total'] += $dt['tax_total'];
							$summary_billing[$tipe]['service_total'] += $dt['service_total'];
							$summary_billing[$tipe]['sub_total'] += $dt['sub_total'];
							$summary_billing[$tipe]['net_sales'] += $dt['net_sales'];
							$summary_billing[$tipe]['total_pembulatan'] += $dt['total_pembulatan'];
							$summary_billing[$tipe]['total_compliment'] += $dt['total_compliment'];
							$summary_billing[$tipe]['grand_total'] += $dt['grand_total'];
							$summary_billing[$tipe]['compliment_total'] += $dt['compliment_total'];
							$summary_billing[$tipe]['total_dp'] += $dt['total_dp'];
							
							
							if(!empty($payment_data)){
								foreach($payment_data as $key_id => $dtPay){
							
									$tot_payment = 0;
									$tot_payment_show = 0;
									if($dt['payment_id'] == $key_id){
										//$tot_payment = $dt['grand_total'];
										//$tot_payment_show = $dt['grand_total_show'];
										
										if($key_id == 3 OR $key_id == 2){
											$tot_payment = $dt['total_credit'];	
										}else{
											$tot_payment = $dt['total_cash'];	
										}
										
										$tot_payment_show = priceFormat($tot_payment);
										
										//credit half payment
										if(!empty($dt['is_half_payment']) AND $key_id != 1){
											$tot_payment = $dt['total_credit'];
											$tot_payment_show = priceFormat($dt['total_credit']);
										}else{
											
											$tot_payment_show = priceFormat($tot_payment);	
										}
											
									}else{
										//cash
										if(!empty($dt['is_half_payment']) AND $key_id == 1){
											$tot_payment = $dt['total_cash'];
											$tot_payment_show = priceFormat($dt['total_cash']);
										}
									}
							
									if(empty($grand_total_payment[$key_id])){
										$grand_total_payment[$key_id] = 0;
									}
							
									if(!empty($dt['is_compliment'])){
										$tot_payment = 0;
										$tot_payment_show = 0;
									}
									
									$summary_billing[$tipe]['payment_'.$key_id] += $tot_payment;
																	
								}
							}
							
						}
					}
					
				}
			}
			
			
			
			
			//SUMMARY BILLING
			$summary_billing_new = $summary_billing;
			$summary_billing = array();
			if(!empty($summary_billing_new)){
				foreach($summary_billing_new as $key => $detail){
					
					$detail['total_billing_show'] = priceFormat($detail['total_billing']);
					$detail['sub_total_show'] = priceFormat($detail['sub_total']);
					$detail['net_sales_show'] = priceFormat($detail['net_sales']);
					$detail['tax_total_show'] = priceFormat($detail['tax_total']);
					$detail['service_total_show'] = priceFormat($detail['service_total']);
					$detail['total_pembulatan_show'] = priceFormat($detail['total_pembulatan']);
					$detail['grand_total_show'] = priceFormat($detail['grand_total']);
					$detail['discount_total_show'] = priceFormat($detail['discount_total']);
					$detail['discount_billing_total_show'] = priceFormat($detail['discount_billing_total']);
					$detail['total_dp_show'] = priceFormat($detail['total_dp']);
					$detail['compliment_total_show'] = priceFormat($detail['compliment_total']);
					$detail['total_hpp_show'] = priceFormat($detail['total_hpp']);
					$detail['total_profit_show'] = priceFormat($detail['total_profit']);
					
					$detail['total_profit'] = $detail['total_billing']-$detail['total_hpp'];
					$detail['total_hpp_show'] = priceFormat($detail['total_hpp']);
					$detail['total_profit_show'] = priceFormat($detail['total_profit']);
					
					$summary_billing[$key] = $detail;					
				}
				
			}
			
			$data_post['summary_billing'] = $summary_billing;
			
			$data_product = array();
			$sort_qty = array();
			$sort_profit = array();
			$no = 1;
			if(!empty($all_product_data)){
				foreach($all_product_data as $dt){
					$dt['item_no'] = $no;
					
					$sort_qty[$dt['product_id']] = $dt['total_qty'];
					
					
					//BALANCING DISKON
					if(!empty($data_diskon_awal[$dt['product_id']])){
						$dt['discount_total'] -= $data_diskon_awal[$dt['product_id']]['item'];
						$dt['discount_billing_total'] -= $data_diskon_awal[$dt['product_id']]['billing'];
					}
					
					if(!empty($data_balancing_diskon[$dt['product_id']])){
						$dt['discount_total'] += $data_balancing_diskon[$dt['product_id']]['item'];
						$dt['discount_billing_total'] += $data_balancing_diskon[$dt['product_id']]['billing'];
					}
					
					if(!empty($data_selisih_diskon[$dt['product_id']])){
						$dt['sub_total'] -= $data_selisih_diskon[$dt['product_id']];
						$dt['grand_total'] -= $data_selisih_diskon[$dt['product_id']];
					}
					
					//BALANCING DISKON PAYMENT
					if(!empty($data_selisih_diskon_payment[$dt['product_id']])){
						foreach($data_selisih_diskon_payment[$dt['product_id']] as $payment_id => $dtP){
							if(!empty($dt['payment_'.$payment_id])){
								$dt['payment_'.$payment_id] -= $dtP;
							}
						}
					}
					
					
					//KONVERSI PEMBULATAN
					$selisih_pembulatan = 0;
					if(!empty($pembulatan_awal_product[$dt['product_id']])){
						$selisih_pembulatan -= $pembulatan_awal_product[$dt['product_id']];
						$dt['grand_total'] -= $pembulatan_awal_product[$dt['product_id']];
					}
					
					
					if(!empty($konversi_pembulatan_product[$dt['product_id']])){
						$dt['total_pembulatan'] = $konversi_pembulatan_product[$dt['product_id']]['total_pembulatan'];
						$dt['grand_total'] += $konversi_pembulatan_product[$dt['product_id']]['total_pembulatan'];
						$selisih_pembulatan += $konversi_pembulatan_product[$dt['product_id']]['total_pembulatan'];
					}
					
					if(!empty($dt['compliment_total'])){
						$dt['compliment_total'] += $selisih_pembulatan;
					}
					
					//KONVERSI PEMBULATAN PAYMENT
					if(!empty($pembulatan_awal_product_payment[$dt['product_id']])){
						foreach($pembulatan_awal_product_payment[$dt['product_id']] as $payment_id => $dtP){
							if(!empty($dt['payment_'.$payment_id])){
								$dt['payment_'.$payment_id] -= $dtP;
							}
						}
					}
					
					if(!empty($konversi_pembulatan_product_payment[$dt['product_id']])){
						foreach($konversi_pembulatan_product_payment[$dt['product_id']] as $payment_id => $dtP){
							if(!empty($dt['payment_'.$payment_id])){
								$dt['payment_'.$payment_id] += $dtP;
							}
						}
					}
					
					
					$dt['total_billing_show'] = priceFormat($dt['total_billing']);
					$dt['grand_total_show'] = priceFormat($dt['grand_total']);
					$dt['sub_total_show'] = priceFormat($dt['sub_total']);
					$dt['net_sales_show'] = priceFormat($dt['net_sales']);
					$dt['tax_total_show'] = priceFormat($dt['tax_total']);
					$dt['service_total_show'] = priceFormat($dt['service_total']);
					
					$dt['total_pembulatan_show'] = priceFormat($dt['total_pembulatan']);
					$dt['discount_total_show'] = priceFormat($dt['discount_total']);
					$dt['discount_billing_total_show'] = priceFormat($dt['discount_billing_total']);
					$dt['compliment_total_show'] = priceFormat($dt['compliment_total']);
					
					$dt['total_profit'] = $dt['total_billing']-$dt['total_hpp'];
					$dt['total_hpp_show'] = priceFormat($dt['total_hpp']);
					$dt['total_profit_show'] = priceFormat($dt['total_profit']);
					$sort_profit[$dt['product_id']] = $dt['total_profit'];
										
					$data_product[$dt['product_id']] = $dt;
					$no++;
				}
			}
			
			$data_post['data_product'] = $data_product;
			
			
			//SUMMARY FNB
			$summary_fnb = array();
			foreach($data_product as $key => $dt){
				
				$ProdGroup = strtoupper($dt['product_group']);
				
				if(empty($summary_fnb[$ProdGroup])){
					$summary_fnb[$ProdGroup] = array(
						'group_name'	=> $ProdGroup,
						'total_qty'	=> 0,
						'total_billing'	=> 0,
						'total_billing_show'	=> 0,
						'sub_total'	=> 0,
						'sub_total_show'	=> 0,
						'net_sales'	=> 0,
						'net_sales_show'	=> 0,
						'grand_total'	=> 0,
						'grand_total_show'	=> 0,
						'tax_total'	=> 0,
						'tax_total_show'	=> 0,
						'total_pembulatan'	=> 0,
						'total_pembulatan_show'	=> 0,
						'service_total'	=> 0,
						'service_total_show'	=> 0,
						'discount_total'	=> 0,
						'discount_total_show'	=> 0,
						'discount_billing_total'	=> 0,
						'discount_billing_total_show'	=> 0,
						'total_hpp'	=> 0,
						'total_hpp_show'	=> 0,
						'total_profit'	=> 0,
						'total_profit_show'	=> 0,
						'is_takeaway'	=> 0,
						'is_compliment'	=> 0,
						'compliment_total'	=> 0,
						'compliment_total_show'	=> 0,
						'total_dp'	=> 0,
						'total_dp_show'	=> 0
					);
					
					if(!empty($payment_data)){
						foreach($payment_data as $key_id => $dtPay){
							$summary_fnb[$ProdGroup]['payment_'.$key_id] = 0;
							$summary_fnb[$ProdGroup]['payment_'.$key_id.'_show'] = 0;
						}
					}
				}
				
				if(!empty($total_hpp[$key])){
					$dt['total_hpp'] = $total_hpp[$key];
				}

				$dt['total_profit'] = $dt['total_billing']-$dt['total_hpp'];
				
				//SUMMARY FNB
				$summary_fnb[$ProdGroup]['total_qty'] += $dt['total_qty'];
				$summary_fnb[$ProdGroup]['total_billing'] += $dt['total_billing'];
				$summary_fnb[$ProdGroup]['sub_total'] += $dt['sub_total'];
				$summary_fnb[$ProdGroup]['net_sales'] += $dt['net_sales'];
				$summary_fnb[$ProdGroup]['grand_total'] += $dt['grand_total'];
				$summary_fnb[$ProdGroup]['tax_total'] += $dt['tax_total'];
				$summary_fnb[$ProdGroup]['service_total'] += $dt['service_total'];
				$summary_fnb[$ProdGroup]['total_pembulatan'] += $dt['total_pembulatan'];
				$summary_fnb[$ProdGroup]['discount_total'] += $dt['discount_total'];
				$summary_fnb[$ProdGroup]['discount_billing_total'] += $dt['discount_billing_total'];
				$summary_fnb[$ProdGroup]['total_hpp'] += $dt['total_hpp'];
				$summary_fnb[$ProdGroup]['total_profit'] += $dt['total_profit'];
				$summary_fnb[$ProdGroup]['compliment_total'] += $dt['compliment_total'];
				$summary_fnb[$ProdGroup]['total_dp'] += $dt['total_dp'];
				
				if(!empty($payment_data)){
					foreach($payment_data as $key_id => $dtPay){
						
						if(!empty($dt['payment_'.$key_id])){
							$summary_fnb[$ProdGroup]['payment_'.$key_id] += $dt['payment_'.$key_id];
						}
														
					}
				}
				
			}
			
			
			//SUMMARY FNB
			$summary_fnb_new = $summary_fnb;
			$summary_fnb = array();
			if(!empty($summary_fnb_new)){
				foreach($summary_fnb_new as $key => $detail){
					
					$detail['total_billing_show'] = priceFormat($detail['total_billing']);
					$detail['sub_total_show'] = priceFormat($detail['sub_total']);
					$detail['net_sales_show'] = priceFormat($detail['net_sales']);
					$detail['tax_total_show'] = priceFormat($detail['tax_total']);
					$detail['service_total_show'] = priceFormat($detail['service_total']);
					$detail['total_pembulatan_show'] = priceFormat($detail['total_pembulatan']);
					$detail['grand_total_show'] = priceFormat($detail['grand_total']);
					$detail['discount_total_show'] = priceFormat($detail['discount_total']);
					$detail['discount_billing_total_show'] = priceFormat($detail['discount_billing_total']);
					$detail['total_dp_show'] = priceFormat($detail['total_dp']);
					$detail['compliment_total_show'] = priceFormat($detail['compliment_total']);
					$detail['total_hpp_show'] = priceFormat($detail['total_hpp']);
					
					$detail['total_profit'] = $detail['total_billing']-$detail['total_hpp'];
					$detail['total_hpp_show'] = priceFormat($detail['total_hpp']);
					$detail['total_profit_show'] = priceFormat($detail['total_profit']);
					
					$summary_fnb[$key] = $detail;					
				}
				
			}
			$data_post['summary_fnb'] = $summary_fnb;
			
			
			
			//SUMMARY FNB CAT
			$summary_fnb_category = array();
			foreach($data_product as $key => $dt){
				
				$ProdGroup = strtoupper($dt['product_group']);
				$category_id = $dt['category_id'];
				$category_name = strtoupper($dt['category_name']);
				
				if(empty($summary_fnb_category[$ProdGroup])){
					$summary_fnb_category[$ProdGroup] = array();
				}
				if(empty($summary_fnb_category[$ProdGroup][$category_id])){
					$summary_fnb_category[$ProdGroup][$category_id] = array(
						'group_name'	=> $ProdGroup,
						'category_id'	=> $category_id,
						'category_name'	=> $category_name,
						'total_qty'	=> 0,
						'total_billing'	=> 0,
						'total_billing_show'	=> 0,
						'sub_total'	=> 0,
						'sub_total_show'	=> 0,
						'net_sales'	=> 0,
						'net_sales_show'	=> 0,
						'grand_total'	=> 0,
						'grand_total_show'	=> 0,
						'tax_total'	=> 0,
						'tax_total_show'	=> 0,
						'total_pembulatan'	=> 0,
						'total_pembulatan_show'	=> 0,
						'service_total'	=> 0,
						'service_total_show'	=> 0,
						'discount_total'	=> 0,
						'discount_total_show'	=> 0,
						'discount_billing_total'	=> 0,
						'discount_billing_total_show'	=> 0,
						'total_hpp'	=> 0,
						'total_hpp_show'	=> 0,
						'total_profit'	=> 0,
						'total_profit_show'	=> 0,
						'is_takeaway'	=> 0,
						'is_compliment'	=> 0,
						'compliment_total'	=> 0,
						'compliment_total_show'	=> 0,
						'total_dp'	=> 0,
						'total_dp_show'	=> 0
					);
					
					if(!empty($payment_data)){
						foreach($payment_data as $key_id => $dtPay){
							$summary_fnb_category[$ProdGroup][$category_id]['payment_'.$key_id] = 0;
							$summary_fnb_category[$ProdGroup][$category_id]['payment_'.$key_id.'_show'] = 0;
						}
					}
				}
				
				if(!empty($total_hpp[$key])){
					$dt['total_hpp'] = $total_hpp[$key];
				}

				$dt['total_profit'] = $dt['total_billing']-$dt['total_hpp'];
				
				//SUMMARY FNB
				$summary_fnb_category[$ProdGroup][$category_id]['total_qty'] += $dt['total_qty'];
				$summary_fnb_category[$ProdGroup][$category_id]['total_billing'] += $dt['total_billing'];
				$summary_fnb_category[$ProdGroup][$category_id]['sub_total'] += $dt['sub_total'];
				$summary_fnb_category[$ProdGroup][$category_id]['net_sales'] += $dt['net_sales'];
				$summary_fnb_category[$ProdGroup][$category_id]['grand_total'] += $dt['grand_total'];
				$summary_fnb_category[$ProdGroup][$category_id]['tax_total'] += $dt['tax_total'];
				$summary_fnb_category[$ProdGroup][$category_id]['service_total'] += $dt['service_total'];
				$summary_fnb_category[$ProdGroup][$category_id]['total_pembulatan'] += $dt['total_pembulatan'];
				$summary_fnb_category[$ProdGroup][$category_id]['discount_total'] += $dt['discount_total'];
				$summary_fnb_category[$ProdGroup][$category_id]['discount_billing_total'] += $dt['discount_billing_total'];
				$summary_fnb_category[$ProdGroup][$category_id]['total_hpp'] += $dt['total_hpp'];
				$summary_fnb_category[$ProdGroup][$category_id]['total_profit'] += $dt['total_profit'];
				$summary_fnb_category[$ProdGroup][$category_id]['compliment_total'] += $dt['compliment_total'];
				$summary_fnb_category[$ProdGroup][$category_id]['total_dp'] += $dt['total_dp'];
				
				if(!empty($payment_data)){
					foreach($payment_data as $key_id => $dtPay){
						
						if(!empty($dt['payment_'.$key_id])){
							$summary_fnb_category[$ProdGroup][$category_id]['payment_'.$key_id] += $dt['payment_'.$key_id];
						}
														
					}
				}
				
				
			}
			
			//SUMMARY FNB CAT
			$summary_fnb_category_new = $summary_fnb_category;
			$summary_fnb_category = array();
			if(!empty($summary_fnb_category_new)){
				foreach($summary_fnb_category_new as $fnb_group => $dtCat){
					
					if(!empty($dtCat)){
						foreach($dtCat as $cat_id => $detail){
							
							$detail['total_billing_show'] = priceFormat($detail['total_billing']);
							$detail['sub_total_show'] = priceFormat($detail['sub_total']);
							$detail['net_sales_show'] = priceFormat($detail['net_sales']);
							$detail['tax_total_show'] = priceFormat($detail['tax_total']);
							$detail['service_total_show'] = priceFormat($detail['service_total']);
							$detail['total_pembulatan_show'] = priceFormat($detail['total_pembulatan']);
							$detail['grand_total_show'] = priceFormat($detail['grand_total']);
							$detail['discount_total_show'] = priceFormat($detail['discount_total']);
							$detail['discount_billing_total_show'] = priceFormat($detail['discount_billing_total']);
							$detail['total_dp_show'] = priceFormat($detail['total_dp']);
							$detail['compliment_total_show'] = priceFormat($detail['compliment_total']);
							$detail['total_hpp_show'] = priceFormat($detail['total_hpp']);
							
							$detail['total_profit'] = $detail['total_billing']-$detail['total_hpp'];
							$detail['total_hpp_show'] = priceFormat($detail['total_hpp']);
							$detail['total_profit_show'] = priceFormat($detail['total_profit']);
							
							if(empty($summary_fnb_category[$fnb_group])){
								$summary_fnb_category[$fnb_group] = array();
							}
							
							$summary_fnb_category[$fnb_group][$cat_id] = $detail;	
						}
					}	
					
				}
				
			}
			$data_post['summary_fnb_category'] = $summary_fnb_category;
			
			
			//SUMMARY SALES PERIODE
			$summary_sales_periode_new = $summary_sales_periode;
			$summary_sales_periode = array();
			if(!empty($summary_sales_periode_new)){
				foreach($summary_sales_periode_new as $key => $detail){
					
					$detail['total_billing_show'] = priceFormat($detail['total_billing']);
					$detail['sub_total_show'] = priceFormat($detail['sub_total']);
					$detail['net_sales_show'] = priceFormat($detail['net_sales']);
					$detail['tax_total_show'] = priceFormat($detail['tax_total']);
					$detail['service_total_show'] = priceFormat($detail['service_total']);
					$detail['total_pembulatan_show'] = priceFormat($detail['total_pembulatan']);
					$detail['grand_total_show'] = priceFormat($detail['grand_total']);
					$detail['discount_total_show'] = priceFormat($detail['discount_total']);
					$detail['discount_billing_total_show'] = priceFormat($detail['discount_billing_total']);
					$detail['total_dp_show'] = priceFormat($detail['total_dp']);
					$detail['compliment_total_show'] = priceFormat($detail['compliment_total']);
					
					//$detail['total_qty'] = 0;
					$detail['total_hpp'] = 0;
					if(!empty($summary_sales_periode_billing_id[$key])){
						foreach($summary_sales_periode_billing_id[$key] as $billing_id){
							if(!empty($billing_id_qty_hpp[$billing_id])){
								//$detail['total_qty'] += $billing_id_qty_hpp[$billing_id]['total_qty'];
								$detail['total_hpp'] += $billing_id_qty_hpp[$billing_id]['total_hpp'];
							}
						}
					}
					
					$detail['total_hpp_show'] = priceFormat($detail['total_hpp']);
					$detail['total_profit_show'] = priceFormat($detail['total_profit']);
					
					$detail['total_profit'] = $detail['total_billing']-$detail['total_hpp'];
					$detail['total_hpp_show'] = priceFormat($detail['total_hpp']);
					$detail['total_profit_show'] = priceFormat($detail['total_profit']);
					
					$summary_sales_periode[$key] = $detail;					
				}
				
			}
			ksort($summary_sales_periode);
			$data_post['summary_sales_periode'] = $summary_sales_periode;
			
			//SUMMARY PROMO
			$summary_promo_new = $summary_promo;
			$summary_promo = array();
			if(!empty($summary_promo_new)){
				foreach($summary_promo_new as $key => $detail){
					
					$detail['total_billing_show'] = priceFormat($detail['total_billing']);
					$detail['sub_total_show'] = priceFormat($detail['sub_total']);
					$detail['net_sales_show'] = priceFormat($detail['net_sales']);
					$detail['tax_total_show'] = priceFormat($detail['tax_total']);
					$detail['service_total_show'] = priceFormat($detail['service_total']);
					$detail['total_pembulatan_show'] = priceFormat($detail['total_pembulatan']);
					$detail['grand_total_show'] = priceFormat($detail['grand_total']);
					$detail['discount_total_show'] = priceFormat($detail['discount_total']);
					$detail['discount_billing_total_show'] = priceFormat($detail['discount_billing_total']);
					$detail['total_dp_show'] = priceFormat($detail['total_dp']);
					$detail['compliment_total_show'] = priceFormat($detail['compliment_total']);
					$detail['total_hpp_show'] = priceFormat($detail['total_hpp']);
					$detail['total_profit_show'] = priceFormat($detail['total_profit']);
					
					$detail['total_profit'] = $detail['total_billing']-$detail['total_hpp'];
					$detail['total_hpp_show'] = priceFormat($detail['total_hpp']);
					$detail['total_profit_show'] = priceFormat($detail['total_profit']);
					
					if(!empty($detail['grand_total'])){
						$summary_promo[$key] = $detail;	
					}
					
				}
				
			}
			ksort($summary_promo);
			$data_post['summary_promo'] = $summary_promo;
			
			//echo '<pre>';
			//print_r($summary_promo);
			//die();
			
			//SUMMARY PAYMENT
			$summary_payment_new = $summary_payment;
			$summary_payment = array();
			if(!empty($summary_payment_new)){
				foreach($summary_payment_new as $key => $detail){
					
					$detail['total_billing_show'] = priceFormat($detail['total_billing']);
					$detail['sub_total_show'] = priceFormat($detail['sub_total']);
					$detail['net_sales_show'] = priceFormat($detail['net_sales']);
					$detail['tax_total_show'] = priceFormat($detail['tax_total']);
					$detail['service_total_show'] = priceFormat($detail['service_total']);
					$detail['total_pembulatan_show'] = priceFormat($detail['total_pembulatan']);
					$detail['grand_total_show'] = priceFormat($detail['grand_total']);
					$detail['discount_total_show'] = priceFormat($detail['discount_total']);
					$detail['discount_billing_total_show'] = priceFormat($detail['discount_billing_total']);
					$detail['total_dp_show'] = priceFormat($detail['total_dp']);
					$detail['compliment_total_show'] = priceFormat($detail['compliment_total']);
					$detail['total_hpp_show'] = priceFormat($detail['total_hpp']);
					$detail['total_profit_show'] = priceFormat($detail['total_profit']);
					
					$detail['total_profit'] = $detail['total_billing']-$detail['total_hpp'];
					$detail['total_hpp_show'] = priceFormat($detail['total_hpp']);
					$detail['total_profit_show'] = priceFormat($detail['total_profit']);
					
					$summary_payment[$detail['payment_name']][] = $detail;					
				}
				
			}
			ksort($summary_payment);
			$data_post['summary_payment'] = $summary_payment;
			
			//SUMMARY PACKAGE
			$summary_sales_package = array();
			if(!empty($all_product_data_package)){
				foreach($all_product_data_package as $id){
					if(!empty($all_product_data[$id])){
						$detail = $all_product_data[$id];
						
						$detail['total_billing_show'] = priceFormat($detail['total_billing']);
						$detail['sub_total_show'] = priceFormat($detail['sub_total']);
						$detail['net_sales_show'] = priceFormat($detail['net_sales']);
						$detail['tax_total_show'] = priceFormat($detail['tax_total']);
						$detail['service_total_show'] = priceFormat($detail['service_total']);
						$detail['total_pembulatan_show'] = priceFormat($detail['total_pembulatan']);
						$detail['grand_total_show'] = priceFormat($detail['grand_total']);
						$detail['discount_total_show'] = priceFormat($detail['discount_total']);
						$detail['discount_billing_total_show'] = priceFormat($detail['discount_billing_total']);
						$detail['total_dp_show'] = priceFormat($detail['total_dp']);
						$detail['compliment_total_show'] = priceFormat($detail['compliment_total']);
						$detail['total_hpp_show'] = priceFormat($detail['total_hpp']);
						$detail['total_profit_show'] = priceFormat($detail['total_profit']);
						
						$detail['total_profit'] = $detail['total_billing']-$detail['total_hpp'];
						$detail['total_hpp_show'] = priceFormat($detail['total_hpp']);
						$detail['total_profit_show'] = priceFormat($detail['total_profit']);
						
						$summary_sales_package[$id] = $detail;
					}
				}
			}
			$data_post['summary_sales_package'] = $summary_sales_package;
			
			$data_post['report_data'] = $newData;
			$data_post['payment_data'] = $dt_payment_name;
			//$data_post['total_hpp'] = $total_hpp;
		}
		
		//echo '<pre>';
		//print_r($summary_fnb_category);
		//die();
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		$useview = 'print_salesSummaryReport';
		$data_post['report_name'] = 'SALES SUMMARY REPORT';
		
		if($do == 'excel'){
			$useview = 'excel_salesSummaryReport';
		}
		
		$this->load->view('../../billing/views/'.$useview, $data_post);	
	}
	
}