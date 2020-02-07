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
			'report_name'	=> 'SALES SUMMARY REPORT',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'user_fullname'	=> $user_fullname,
			'sorting'	=> $sorting,
			'diskon_sebelum_pajak_service' => 0,
			'display_discount_type'	=> array()
		);
		
		$display_discount_type = array();

		$get_opt = get_option_value(array('report_place_default','diskon_sebelum_pajak_service','cashier_max_pembulatan',
		'cashier_pembulatan_keatas','pembulatan_dinamis','role_id_kasir','maxday_cashier_report',
		'jam_operasional_from','jam_operasional_to','jam_operasional_extra'));
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
						
			$ret_dt = check_maxview_cashierReport($get_opt, $mktime_dari, $mktime_sampai);
			
			//$qdate_from = date("Y-m-d",strtotime($date_from));
			//$qdate_till = date("Y-m-d",strtotime($date_till));
			//$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
			//$add_where = "(a.payment_date >= '".$qdate_from." 07:00:01' AND a.payment_date <= '".$qdate_till_max." 06:00:00')";
			//$add_where_or_cancel = "(".$add_where." OR ((a.updated >= '".$qdate_from." 07:00:01' AND a.updated <= '".$qdate_till_max." 06:00:00') AND a.billing_status = 'cancel'))";
			
			//laporan = jam_operasional
			$qdate_from = $ret_dt['qdate_from'];
			$qdate_till = $ret_dt['qdate_till'];
			$qdate_till_max = $ret_dt['qdate_till_max'];
			$add_where = "(a.payment_date >= '".$qdate_from."' AND a.payment_date <= '".$qdate_till_max."')";
			$add_where_or_cancel = "(".$add_where." OR ((a.updated >= '".$qdate_from."' AND a.updated <= '".$qdate_till_max."') AND a.billing_status = 'cancel'))";
			
			$billing_buyget = array();
			$billing_buyget_discount_id = array();
			$billing_promo = array();
			$billing_promo_discount_id = array();
			$billing_buyget_promo = array();
			
			//BUYGET & PROMO di BILLING DETAIL
			$this->db->select('b.billing_id, b.is_buyget, b.buyget_id, b.buyget_qty, b.is_promo, b.promo_id, b.order_qty, b.ref_order_id, b.discount_total');
			$this->db->from($this->table2.' as b');
			$this->db->join($this->table.' as a',"a.id = b.billing_id","LEFT");
			$this->db->where("a.billing_status", 'paid');
			$this->db->where("a.is_deleted", 0);
			$this->db->where("((b.is_buyget = 1 OR b.is_promo = 1) AND b.order_qty > 0 AND b.order_status != 'cancel')");
			$this->db->where($add_where);
			$get_buyget = $this->db->get();
			if($get_buyget->num_rows() > 0){
				foreach($get_buyget->result() as $dtRow){
					
					//ONLY BUYGET 
					if($dtRow->is_buyget == 1 AND $dtRow->buyget_qty > 0){
						if(!in_array($dtRow->billing_id, $billing_buyget_promo)){
							$billing_buyget_promo[] = $dtRow->billing_id;
						}
						if(!in_array($dtRow->billing_id, $billing_buyget)){
							$billing_buyget[] = $dtRow->billing_id;
							
							if(empty($billing_buyget_discount_id[$dtRow->billing_id])){
								$billing_buyget_discount_id[$dtRow->billing_id] = array();
							}
							
							$billing_buyget_discount_id[$dtRow->billing_id][] = $dtRow->buyget_id;
						}
					}
					
					
					//PROMO = ITEM
					if($dtRow->is_promo == 1){
						if(!in_array($dtRow->billing_id, $billing_buyget_promo)){
							$billing_buyget_promo[] = $dtRow->billing_id;
						}
						if(!in_array($dtRow->billing_id, $billing_promo)){
							$billing_promo[] = $dtRow->billing_id;
							
							if(empty($billing_promo_discount_id[$dtRow->billing_id])){
								$billing_promo_discount_id[$dtRow->billing_id] = array();
							}
							
							$billing_promo_discount_id[$dtRow->billing_id][] = $dtRow->promo_id;
						}
					}
					
				}
			}
			
			//echo count($billing_buyget_discount_id).'<pre>';
			//print_r($billing_buyget_discount_id);
			//die();
			
			//CHECKING BILLING
			$this->db->select("a.*, a.id as billing_id, a.updated as billing_date, d.payment_type_name, e.bank_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT');
			$this->db->join($this->prefix.'bank as e','e.id = a.bank_id','LEFT');
			$this->db->where("a.billing_status IN ('paid','cancel')");
			$this->db->where("a.is_deleted", 0);
			$this->db->where($add_where_or_cancel);
			

			if(!empty($discount_type)){
				if($discount_type == 'no_promo'){
					$this->db->where("((a.discount_id = 0 OR a.discount_id IS NULL))");
				}
				if($discount_type == 'item'){
					$this->db->where("((a.discount_id > 0 AND discount_perbilling = 0) OR  (a.discount_id = 0 AND (a.discount_total != 0 OR a.discount_total IS NOT NULL)))");
				}
				if($discount_type == 'billing'){
					$this->db->where("((a.discount_id > 0 AND discount_perbilling = 1) OR  (a.discount_id = 0 AND (a.discount_total != 0 OR a.discount_total IS NOT NULL)))");
				}
				
				if($discount_type == 'buyget'){
					if(!empty($billing_buyget)){
						$billing_buyget_all = implode(",", $billing_buyget);
						$this->db->or_where("a.id IN (".$billing_buyget_all.")");
					}
					
					if(!empty($billing_promo)){
						$billing_promo_all = implode(",", $billing_promo);
						$this->db->or_where("a.id IN (".$billing_promo_all.")");
					}
				}
			}
			
			if(empty($discount_type)){
				$discount_type = '';
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
				'compliment_total'	=> 0,
				'total_dp'	=> 0,
				'net_sales'	=> 0,
				'sub_total'	=> 0,
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
				'discount_total_before'	=> 0,
				'discount_total_before_show'	=> 0,
				'discount_billing_total_before'	=> 0,
				'discount_billing_total_before_show'	=> 0,
				'discount_total_after'	=> 0,
				'discount_total_after_show'	=> 0,
				'discount_billing_total_after'	=> 0,
				'discount_billing_total_after_show'	=> 0,
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
			$summary_promo_buyget = array();
			$summary_promo_bill_id = array(); //asumsi billing id 
			$summary_paket = array();
			$summary_payment = array();
			$summary_cancel = array();
			$all_bil_id = array();
			$newData = array();
			$dt_payment = array();


			$all_discount_id = array();
			$all_billing_discount_id = array();
			$all_billing_discount_id_buyget = array();

			$all_discount_item = array();
			$all_discount_billing = array();
			$all_discount_buyget = array();
			$all_discount_promo = array();
			
			$move_to_buyget = array();
			$billing_data = array();

			$summary_promo_sort_qty = array();
			$summary_promo_sort_qty[0] = 0;
			$summary_promo_buyget_sort_qty = array();
			$summary_promo_buyget_sort_qty[0] = 0;

			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					
					if(empty($display_discount_type[$s['diskon_sebelum_pajak_service']])){
						$display_discount_type[$s['diskon_sebelum_pajak_service']] = array();
					}
					if(!in_array($s['billing_id'], $display_discount_type[$s['diskon_sebelum_pajak_service']])){
						$display_discount_type[$s['diskon_sebelum_pajak_service']][] = $s['billing_id'];
					}
					
					if($discount_type == 'buyget'){
						if(in_array($s['id'], $billing_buyget_promo)){
							if(!in_array($s['id'], $all_bil_id)){
								$all_bil_id[] = $s['id'];
							}
						}
					}else
					{
						if(!in_array($s['id'], $all_bil_id)){
							$all_bil_id[] = $s['id'];
						}

					}		
					
					//CHECK REAL TOTAL BILLING
					/*if(!empty($s['include_tax']) OR !empty($s['include_service'])){
						if(!empty($s['include_tax']) AND !empty($s['include_service'])){
						
							if($s['diskon_sebelum_pajak_service'] == 1){
								$get_total_billing = $s['total_billing'] / (($s['tax_percentage']+$s['service_percentage']+100)/100);
								$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
								$s['total_billing'] = $get_total_billing;
							}else{
								$s['total_billing'] = $s['total_billing'] - ($s['tax_total'] + $s['service_total']);
							}
							
						}else{
							if(!empty($s['include_tax'])){
								if($s['diskon_sebelum_pajak_service'] == 1){
									$get_total_billing = $s['total_billing'] / (($s['tax_percentage']+100)/100);
									$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
									$s['total_billing'] = $get_total_billing;
								}else{
									$s['total_billing'] = $s['total_billing'] - ($s['tax_total']);
								}
							}
							if(!empty($s['include_service'])){
								if($s['diskon_sebelum_pajak_service'] == 1){
									$get_total_billing = $s['total_billing'] / (($s['service_percentage']+100)/100);
									$get_total_billing = priceFormat($get_total_billing, 0, ".", "");
									$s['total_billing'] = $get_total_billing;
								}else{
									$s['total_billing'] = $s['total_billing'] - ($s['service_total']);
								}
							}
						}
					}*/
					
					$s['total_billing_awal'] = $s['total_billing'];
					
					//update-2001.002
					//CHECK REAL TOTAL BILLING
					if(!empty($s['include_tax']) OR !empty($s['include_service'])){
						if(!empty($s['include_tax']) AND !empty($s['include_service'])){
							$s['total_billing'] = $s['total_billing'] - ($s['tax_total'] + $s['service_total']);
						}else{
							if(!empty($s['include_tax'])){
								$s['total_billing'] = $s['total_billing'] - ($s['tax_total']);
							}
							if(!empty($s['include_service'])){
								$s['total_billing'] = $s['total_billing'] - ($s['service_total']);
							}
						}
					}
					
					//update-2001.002
					//COMPLIMENT
					if(!empty($s['is_compliment']) OR !empty($s['compliment_total'])){
						//$s['total_billing'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
						if($s['total_billing'] < $s['compliment_total'] OR $s['total_billing'] == $s['compliment_total']){
							$s['service_total'] = 0;
							$s['tax_total'] = 0;
						}
					}
					
					//diskon_sebelum_pajak_service
					if($s['diskon_sebelum_pajak_service'] == 1){
						
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
						
						//update-2001.002
						if(!empty($s['include_tax']) OR !empty($s['include_service'])){
							$s['total_billing'] = ($s['total_billing_awal'] - ($s['tax_total'] + $s['service_total']));
						}
						
						//update-2001.002
						$s['sub_total'] = $s['total_billing'] - $s['discount_total'] + $s['tax_total'] + $s['service_total'] - $s['compliment_total'];;
						$s['net_sales'] = $s['total_billing'] - $s['discount_total'] - $s['compliment_total'];
						
						
						//GRAND TOTAL
						$s['grand_total'] = $s['sub_total'];
						
					}else{
						
						//update-2001.002
						$s['sub_total'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'] - $s['discount_total'] - $s['compliment_total'];
						$s['net_sales'] = $s['total_billing'] - $s['discount_total'] - $s['compliment_total'];
						
						//GRAND TOTAL
						$s['grand_total'] = $s['sub_total'];
						//$s['grand_total'] -= $s['discount_total'];
						//$s['grand_total'] -= $s['discount_billing_total'];
												
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
					
					//$s['grand_total'] = $s['sub_total'] + $s['total_pembulatan'];
					$s['grand_total'] += $s['total_pembulatan'];
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
					
					$s['total_compliment_show'] = priceFormat($s['compliment_total']);
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
					//update-2001.002
					$s['payment_note'] = '';
					if(!empty($s['is_compliment']) OR !empty($s['compliment_total'])){
						$s['payment_note'] .= 'COMPLIMENT ';
						//$s['total_compliment'] = $s['grand_total'];
						$s['total_compliment'] = $s['compliment_total'];
						$s['total_compliment_show'] = priceFormat($s['total_compliment']);
					}
					
					if(!empty($s['is_half_payment'])){
						if(!empty($s['payment_note'])){
							$s['payment_note'] .= ', ';
						}
						$s['payment_note'] .= 'HALF PAYMENT ';
					}
					
					if(strtolower($s['payment_type_name']) != 'cash'){
						if(!empty($s['payment_note'])){
							$s['payment_note'] .= '<br/>';
						}
						$s['payment_note'] .= strtoupper($s['payment_type_name']).': '.strtoupper($s['bank_name']).' '.$card_no.' ';
					}
					
					if(!empty($s['billing_notes'])){
						if(!empty($s['payment_note'])){
							$s['payment_note'] .= '<br/>';
						}
						$s['payment_note'] .= $s['billing_notes'];
					}
					
					//update 2018-03-03
					$s['billing_date'] = date("d-m-Y H:i",strtotime($s['created']));	
					if($s['billing_status'] == 'paid'){
						
						$s['payment_date'] = date("d-m-Y H:i",strtotime($s['payment_date']));
					
						$data_post['summary_data']['compliment_total'] += $s['compliment_total'];
						$data_post['summary_data']['total_dp'] += $s['total_dp'];
						$data_post['summary_data']['total_billing'] += $s['total_billing'];
						$data_post['summary_data']['total_discount_item'] += $s['discount_total'];
						$data_post['summary_data']['total_discount_billing'] += $s['discount_billing_total'];
						$data_post['summary_data']['service_total'] += $s['service_total'];
						$data_post['summary_data']['tax_total'] += $s['tax_total'];
						$data_post['summary_data']['total_pembulatan'] += $s['total_pembulatan'];
						$data_post['summary_data']['grand_total'] += $s['grand_total'];
						$data_post['summary_data']['sub_total'] += $s['sub_total'];
						$data_post['summary_data']['net_sales'] += $s['net_sales'];
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
								'total_profit_show'	=> 0,
								'discount_total_before'	=> 0,
								'discount_total_before_show'	=> 0,
								'discount_billing_total_before'	=> 0,
								'discount_billing_total_before_show'	=> 0,
								'discount_total_after'	=> 0,
								'discount_total_after_show'	=> 0,
								'discount_billing_total_after'	=> 0,
								'discount_billing_total_after_show'	=> 0,
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
						$get_discount_type = '-';
						$get_discount_type_var = 'no_promo';

						if(!empty($s['discount_id'])){

							if(!in_array($s['discount_id'], $all_discount_id)){
								$all_discount_id[] = $s['discount_id'];
							}
							
							//if(!in_array($s['discount_id'], $selected_discount_perbilling)){
							//	$selected_discount_perbilling[] = $s['discount_id'];
							//}
							
							if($s['discount_perbilling'] == 1){
								
								$get_discount_type = 'BILLING';
								$get_discount_type_var = 'billing';
								
								if(!in_array($s['id'],$all_discount_billing)){
									$all_discount_billing[] = $s['id'];
								}
								
								//MOVE TO BUYGET
								if(!empty($billing_buyget_discount_id[$s['id']]) OR !empty($billing_promo_discount_id[$s['id']])){
									
									
									//DISC BILLING HAS BUYGET
									if(!empty($billing_buyget_discount_id[$s['id']])){
										foreach($billing_buyget_discount_id[$s['id']] as $dtDisc){
											
											if(!in_array($dtDisc, $all_discount_id)){
												$all_discount_id[] = $dtDisc;
											}
											
											if(!in_array($dtDisc, $all_discount_buyget)){
												$all_discount_buyget[] = $dtDisc;
											}
											
											if(!in_array($s['id'], $move_to_buyget)){
												$move_to_buyget[] = $s['id'];
											}
										}
									}
									
									//DISC BILLING HAS ITEM PROMO
									if(!empty($billing_promo_discount_id[$s['id']])){
										foreach($billing_promo_discount_id[$s['id']] as $dtDisc){
											
											if(!in_array($dtDisc, $all_discount_id)){
												$all_discount_id[] = $dtDisc;
											}
											
											if(!in_array($dtDisc, $all_discount_buyget)){
												$all_discount_buyget[] = $dtDisc;
											}
											
											if(!in_array($s['id'], $move_to_buyget)){
												$move_to_buyget[] = $s['id'];
											}
										}
									}
									
								}
							
							
								if($s['diskon_sebelum_pajak_service'] == 1){
									$discount_billing_total_before = $s['discount_billing_total'];
									//echo 'DISK BILLING : '.$s['diskon_sebelum_pajak_service'].' -> '.$s['discount_billing_total'].'<br/>';
								}else{
									$discount_billing_total_after = $s['discount_billing_total'];
									//echo 'DISK BILLING : '.$s['diskon_sebelum_pajak_service'].' -> '.$s['discount_billing_total'].'<br/>';
								}
								
							}else{
								
								$get_discount_type = 'ITEM';
								$get_discount_type_var = 'item';
								
								if(!in_array($s['id'],$all_discount_item)){
									$all_discount_item[] = $s['id'];
								}
								
								//MOVE TO BUYGET
								if(!empty($billing_buyget_discount_id[$s['id']]) OR !empty($billing_promo_discount_id[$s['id']])){
									
									//BUYGET
									if(!empty($billing_buyget_discount_id[$s['id']])){
										foreach($billing_buyget_discount_id[$s['id']] as $dtDisc){
											
											if(!in_array($dtDisc, $all_discount_id)){
												$all_discount_id[] = $dtDisc;
											}
											
											if(!in_array($dtDisc, $all_discount_buyget)){
												$all_discount_buyget[] = $dtDisc;
											}
											
											if(!in_array($s['id'], $move_to_buyget)){
												$move_to_buyget[] = $s['id'];
											}
										}
									}
									
									//ITEM PROMO
									if(!empty($billing_promo_discount_id[$s['id']])){
										foreach($billing_promo_discount_id[$s['id']] as $dtDisc){
												
											if(!in_array($dtDisc, $all_discount_id)){
												$all_discount_id[] = $dtDisc;
											}
											
											if(!in_array($dtDisc, $all_discount_buyget)){
												$all_discount_buyget[] = $dtDisc;
											}
											
											if(!in_array($s['id'], $move_to_buyget)){
												$move_to_buyget[] = $s['id'];
											}
										}
									}
									
								}
							
								if($s['diskon_sebelum_pajak_service'] == 1){
									$discount_total_before = $s['discount_total'];
								}else{
									$discount_total_after = $s['discount_total'];
								}
							}
						}else
						{
							$s['discount_id'] = 0;

							//MOVE TO BUYGET
							if(!empty($billing_buyget_discount_id[$s['id']]) OR !empty($billing_promo_discount_id[$s['id']])){
								
								//BUYGET
								if(!empty($billing_buyget_discount_id[$s['id']])){
									foreach($billing_buyget_discount_id[$s['id']] as $dtDisc){
										
										if(!in_array($dtDisc, $all_discount_id)){
											$all_discount_id[] = $dtDisc;
										}
											
										if(!in_array($dtDisc, $all_discount_buyget)){
											$all_discount_buyget[] = $dtDisc;
										}
										
										if(!in_array($s['id'], $move_to_buyget)){
											$move_to_buyget[] = $s['id'];
										}
									}
								}
								
								//ITEM PROMO
								if(!empty($billing_promo_discount_id[$s['id']])){
									foreach($billing_promo_discount_id[$s['id']] as $dtDisc){
									
										if(!in_array($dtDisc, $all_discount_id)){
											$all_discount_id[] = $dtDisc;
										}
										
										if(!in_array($dtDisc, $all_discount_buyget)){
											$all_discount_buyget[] = $dtDisc;
										}
										
										if(!in_array($s['id'], $move_to_buyget)){
											$move_to_buyget[] = $s['id'];
										}
									}
								}
								
							}
							
							if($s['diskon_sebelum_pajak_service'] == 1){
								$discount_total_before = $s['discount_total'];
							}else{
								$discount_total_after = $s['discount_total'];
							}
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
								'discount_type'	=> $get_discount_type,
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
								'total_qty_billing'	=> 0,
								'total_qty'	=> 0,
								'total_hpp'	=> 0,
								'total_hpp_show'	=> 0,
								'compliment_total'	=> 0,
								'compliment_total_show'	=> 0,
								'total_dp'	=> 0,
								'total_dp_show'	=> 0,
								'total_profit'	=> 0,
								'total_profit_show'	=> 0,
								'discount_total_before'	=> 0,
								'discount_total_before_show'	=> 0,
								'discount_billing_total_before'	=> 0,
								'discount_billing_total_before_show'	=> 0,
								'discount_total_after'	=> 0,
								'discount_total_after_show'	=> 0,
								'discount_billing_total_after'	=> 0,
								'discount_billing_total_after_show'	=> 0,
							);
							
							if(!empty($payment_data)){
								foreach($payment_data as $key_id => $dtPay){
									$summary_promo[$var_promo]['payment_'.$key_id] = 0;	
									$summary_promo[$var_promo]['payment_'.$key_id.'_show'] = 0;						
								}
							}
							
							$summary_promo_sort_qty[$var_promo] = 0;

						}
						
						$s['discount_billing_total_before'] = $discount_billing_total_before;
						$s['discount_billing_total_after'] = $discount_billing_total_after;
						$s['discount_total_before'] = $discount_total_before;
						$s['discount_total_after'] = $discount_total_after;
						
						
						if(!empty($billing_buyget_discount_id[$s['id']])){
							$summary_promo_bill_id[$s['id']] = $s;
						}else{
							
							if(!empty($billing_promo_discount_id[$s['id']])){
								$summary_promo_bill_id[$s['id']] = $s;
							}else{
								
								//if(!empty($s['discount_id'])){
									//$summary_promo[$var_promo]['total_qty'] += 1;
									$summary_promo[$var_promo]['total_qty_billing'] += 1;
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
									
									$summary_promo[$var_promo]['discount_billing_total_before'] += $s['discount_billing_total_before'];
									$summary_promo[$var_promo]['discount_billing_total_after'] += $s['discount_billing_total_after'];
									$summary_promo[$var_promo]['discount_total_before'] += $s['discount_total_before'];
									$summary_promo[$var_promo]['discount_total_after'] += $s['discount_total_after'];
								//}
								
							}
							
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
							
							$bank_name = 'CASH';
							if(!empty($bank_data[$s['bank_id']])){
								$bank_name = $bank_data[$s['bank_id']];
							}
							
							$payment_name = 'CASH';
							if(!empty($dt_payment_name[$s['payment_id']])){
								$payment_name = $dt_payment_name[$s['payment_id']];
							}
							
							if($s['payment_id'] == 4){
								$bank_name = 'AR / PIUTANG';
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
								'total_profit_show'	=> 0,
								'discount_total_before'	=> 0,
								'discount_total_before_show'	=> 0,
								'discount_billing_total_before'	=> 0,
								'discount_billing_total_before_show'	=> 0,
								'discount_total_after'	=> 0,
								'discount_total_after_show'	=> 0,
								'discount_billing_total_after'	=> 0,
								'discount_billing_total_after_show'	=> 0,
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
						
						//ALL BEFORE AFTER DISKON
						$data_post['summary_data']['discount_billing_total_before'] += $s['discount_billing_total_before'];
						$data_post['summary_data']['discount_billing_total_after'] += $s['discount_billing_total_after'];
						$data_post['summary_data']['discount_total_before'] += $s['discount_total_before'];
						$data_post['summary_data']['discount_total_after'] += $s['discount_total_after'];
						
						$summary_sales_periode[$tipe_periode]['discount_billing_total_before'] += $s['discount_billing_total_before'];
						$summary_sales_periode[$tipe_periode]['discount_billing_total_after'] += $s['discount_billing_total_after'];
						$summary_sales_periode[$tipe_periode]['discount_total_before'] += $s['discount_total_before'];
						$summary_sales_periode[$tipe_periode]['discount_total_after'] += $s['discount_total_after'];
						
						$summary_payment[$var_payment]['discount_billing_total_before'] += $s['discount_billing_total_before'];
						$summary_payment[$var_payment]['discount_billing_total_after'] += $s['discount_billing_total_after'];
						$summary_payment[$var_payment]['discount_total_before'] += $s['discount_total_before'];
						$summary_payment[$var_payment]['discount_total_after'] += $s['discount_total_after'];
						
						if(!empty($payment_data)){
							foreach($payment_data as $key_id => $dtPay){
						
								$tot_payment = 0;
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
						
					}else{
						
						
						$tipe_cancel = 'AFTER PAYMENT';
						$tipe_name = 'AFTER PAYMENT';
						if(empty($s['payment_date'])){
							$tipe_cancel = 'BEFORE PAYMENT';
							$tipe_name = 'BEFORE PAYMENT';
						}
						
						if(empty($summary_cancel[$tipe_cancel])){
							
							$summary_cancel[$tipe_cancel] = array(
								'tipe_cancel'	=> $tipe_cancel,
								'tipe_name'	=> $tipe_name,
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
								'total_profit_show'	=> 0,
								'discount_total_before'	=> 0,
								'discount_total_before_show'	=> 0,
								'discount_billing_total_before'	=> 0,
								'discount_billing_total_before_show'	=> 0,
								'discount_total_after'	=> 0,
								'discount_total_after_show'	=> 0,
								'discount_billing_total_after'	=> 0,
								'discount_billing_total_after_show'	=> 0,
							);
							
						}
						
						$summary_cancel[$tipe_cancel]['total_billing'] += $s['total_billing'];
						$summary_cancel[$tipe_cancel]['discount_total'] += $s['discount_total'];
						$summary_cancel[$tipe_cancel]['discount_billing_total'] += $s['discount_billing_total'];
						$summary_cancel[$tipe_cancel]['tax_total'] += $s['tax_total'];
						$summary_cancel[$tipe_cancel]['service_total'] += $s['service_total'];
						$summary_cancel[$tipe_cancel]['sub_total'] += $s['sub_total'];
						$summary_cancel[$tipe_cancel]['net_sales'] += $s['net_sales'];
						$summary_cancel[$tipe_cancel]['total_pembulatan'] += $s['total_pembulatan'];
						$summary_cancel[$tipe_cancel]['total_compliment'] += $s['total_compliment'];
						$summary_cancel[$tipe_cancel]['grand_total'] += $s['grand_total'];
						$summary_cancel[$tipe_cancel]['compliment_total'] += $s['compliment_total'];
						$summary_cancel[$tipe_cancel]['total_dp'] += $s['total_dp'];
						$summary_cancel[$tipe_cancel]['total_qty'] += 1;
						
					}
					
					$newData[$s['id']] = $s;
					
					$all_billing_discount_id[$s['id']] = $var_promo;
					$billing_data[$s['id']] = $s;

				}
			}
			
			//BUYGET ---------------------------------------------------
			//summary_promo_buyget
			if(!empty($all_discount_buyget)){
				
				foreach($all_discount_buyget as $getDiscID){
					
					if(empty($summary_promo_buyget[$getDiscID])){

						$discount_name = '-';
						if(!empty($discount_data[$getDiscID])){
							$discount_name = $discount_data[$getDiscID];
						}

						$summary_promo_buyget[$getDiscID] = array(
							'discount_id'	=> $getDiscID,
							'discount_name'	=> $discount_name,
							'discount_type'	=> '-',
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
							'total_qty_billing'	=> 0,
							'total_qty'	=> 0,
							'total_hpp'	=> 0,
							'total_hpp_show'	=> 0,
							'compliment_total'	=> 0,
							'compliment_total_show'	=> 0,
							'total_dp'	=> 0,
							'total_dp_show'	=> 0,
							'total_profit'	=> 0,
							'total_profit_show'	=> 0,
							'discount_total_before'	=> 0,
							'discount_total_before_show'	=> 0,
							'discount_billing_total_before'	=> 0,
							'discount_billing_total_before_show'	=> 0,
							'discount_total_after'	=> 0,
							'discount_total_after_show'	=> 0,
							'discount_billing_total_after'	=> 0,
							'discount_billing_total_after_show'	=> 0,
						);


						if(!empty($payment_data)){
							foreach($payment_data as $key_id => $dtPay){
								$summary_promo_buyget[$getDiscID]['payment_'.$key_id] = 0;	
								$summary_promo_buyget[$getDiscID]['payment_'.$key_id.'_show'] = 0;						
							}
						}
						
						$summary_promo_buyget_sort_qty[$getDiscID] = 0;

					}
				}
			}
			//BUYGET ---------------------------------------------------

			//echo '<pre>TOT:'.count($summary_promo);
			//print_r($summary_promo);
			//die();
			
			//DETAIL BILLING
			$billing_id_qty_hpp = array();
			$all_billing_id_on_summary = array();
			$billing_id_summary_group = array();

			$data_diskon_awal = array();
			$konversi_pembulatan_billing = array();
			$balancing_discount_billing = array();
			$all_product_data = array();
			$all_product_data_package = array();
			$total_hpp = array();
			$discount_item = array();
			$all_discount_id = array();
			$summary_promo_bill_id_done = array();

			$billing_discount_data = array();
			$billing_id_buyget_detail = array();
			$used_billing_total = array();
			$balancing_payment_billing = array();

			if(!empty($all_bil_id)){
				$all_bil_id_txt = implode(",",$all_bil_id);
				$this->db->select("a.*, b.payment_date, b.billing_no, b.total_billing, b.discount_perbilling, b.payment_id, 
								b.is_half_payment, b.total_cash, b.total_credit, b.total_dp, b.compliment_total,
								b.discount_percentage as billing_discount_percentage, b.discount_total as billing_discount_total, 
								b.total_pembulatan as billing_total_pembulatan, b.discount_id as billing_discount_id, b.bank_id, b.diskon_sebelum_pajak_service,
								c.product_name, c.product_type, c.product_group, c.category_id, d.product_category_name as category_name, 
								g.nama_shift");
				$this->db->from($this->table2." as a");
				$this->db->join($this->prefix.'billing as b','b.id = a.billing_id','LEFT');
				$this->db->join($this->prefix.'product as c','c.id = a.product_id','LEFT');
				$this->db->join($this->prefix.'product_category as d','d.id = c.category_id','LEFT');
				$this->db->join($this->prefix.'shift as g','g.id = b.shift','LEFT');
				$this->db->where("(a.order_status != 'cancel' AND a.order_qty > 0)");	
				$this->db->where("a.is_deleted", 0);
				$this->db->where("b.is_deleted", 0);
				$this->db->where("b.billing_status IN ('paid')");	
				$this->db->where('a.billing_id IN ('.$all_bil_id_txt.')');
				
				//$this->db->order_by("c.product_name", 'ASC');
				$this->db->order_by("a.id", 'ASC');
				$this->db->order_by("a.order_qty", 'DESC');
				$this->db->order_by("a.product_price", 'DESC');
				
				$get_detail = $this->db->get();
				
				$billing_detail_data = array();
				if($get_detail->num_rows() > 0){
					foreach($get_detail->result_array() as $s){
						if(!empty($billing_detail_data[$s['billing_id']])){
							$billing_detail_data[$s['billing_id']] = array();
						}
						$billing_detail_data[$s['billing_id']][] = $s['product_id'];
					}
				}
				
				if($get_detail->num_rows() > 0){
					foreach($get_detail->result_array() as $s){
					
						//update-0120.001
						if(!empty($shift_billing) AND empty($data_post['user_shift'])){
							if(!empty($s['nama_shift'])){
								$data_post['user_shift'] = $s['nama_shift'];
							}
						}
						
						if(empty($all_qty_billing[$s['billing_id']])){
							$all_qty_billing[$s['billing_id']] = array(
								'billing_no'	=> $s['billing_no'],
								'qty_item'		=> 0
							);
						}

						$allow_item = true;

						//PACKAGE & PACKAGE ITEM ----------------------------------------------------
						if($s['product_type'] == 'package'){
							//add package
							$package_billing_product[$s['id']] = $s;
						}

						if($s['package_item'] == 1){
							$allow_item = false;

							//ref_order_id
							if(!empty($s['ref_order_id'])){
								if(!empty($package_billing_product[$s['ref_order_id']])){
									if(empty($package_billing_product[$s['ref_order_id']]['package_id'])){
										$package_billing_product[$s['ref_order_id']]['package_id'] = array();
									}
									$package_billing_product[$s['ref_order_id']]['package_id'][] = $s['id'];
								}
							}
						}

						if($allow_item == true){

							$total_qty = $s['order_qty'];

							//HPP
							if(empty($total_hpp[$s['billing_id']])){
								$total_hpp[$s['billing_id']] = 0;
							}
							$total_hpp[$s['billing_id']] += $s['product_price_hpp'] * $s['order_qty'];
							
							$has_discount_on_detail = 0;
							if($s['is_buyget'] == 1){
								//SKIP MAIN ORDER BUYGET
								$has_discount_on_detail = 0;
							}
							
							//buyget free item
							//echo 'discount_id='.$s['discount_id'].', ref_order_id='.$s['ref_order_id'].'<br/>';
							$has_buyget = 0;
							if(!empty($s['discount_id']) AND !empty($s['ref_order_id'])){
								$has_discount_on_detail = 1;
								$has_buyget = 1;
							}
							
							if($s['is_promo'] == 1){
								$has_discount_on_detail = 1;
							}
							
							if($total_qty == 0){
								$has_discount_on_detail = 0;
							}

							//HPP PROMO & QTY
							if(empty($s['billing_discount_id'])){
								$s['billing_discount_id'] = 0;
							}
							
							//if(!empty($summary_promo[$s['billing_discount_id']])){
							//	$summary_promo[$s['billing_discount_id']]['total_qty'] += $s['order_qty'];
							//	$summary_promo[$s['billing_discount_id']]['total_hpp'] += ($s['product_price_hpp'] * $s['order_qty']);
							//}

							
							if(!empty($summary_payment[$s['bank_id']])){
								//$summary_payment[$s['bank_id']]['total_qty'] += $s['order_qty'];
								$summary_payment[$s['bank_id']]['total_hpp'] += ($s['product_price_hpp'] * $s['order_qty']);
							}
							
							if(empty($billing_id_qty_hpp[$s['billing_id']])){
								$billing_id_qty_hpp[$s['billing_id']] = array('total_qty' => 0, 'total_hpp'	=> 0);
							}
							$billing_id_qty_hpp[$s['billing_id']]['total_qty'] += $s['order_qty'];
							$billing_id_qty_hpp[$s['billing_id']]['total_hpp'] += ($s['product_price_hpp'] * $s['order_qty']);

							
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
									'discount_total_before'	=> 0,
									'discount_total_before_show'	=> 0,
									'discount_billing_total_before'	=> 0,
									'discount_billing_total_before_show'	=> 0,
									'discount_total_after'	=> 0,
									'discount_total_after_show'	=> 0,
									'discount_billing_total_after'	=> 0,
									'discount_billing_total_after_show'	=> 0,
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
								$s['service_total'] = 0;
								$s['tax_total'] = 0;
							}
							
							$include_tax = $s['include_tax'];
							$include_service = $s['include_service'];
							$tax_percentage = $s['tax_percentage'];
							$service_percentage = $s['service_percentage'];
							$tax_total = 0;
							$service_total = 0;
							$product_price_real = 0;
							$total_billing_order = 0;
							$tax_total_order = 0;
							$service_total_order = 0;
							$sub_total = 0;
							$net_sales = 0;
							$is_balanced = false;
							
							if(!empty($include_tax) OR !empty($include_service)){
								
								//AUTOFIX-BUGS 1 Jan 2018
								if((!empty($include_tax) AND empty($include_service)) OR (empty($include_tax) AND !empty($include_service))){
									if($s['product_price'] != ($s['product_price_real']+$s['tax_total']+$s['service_total'])){
										$s['product_price_real'] = priceFormat(($s['product_price']/($all_percentage/100)), 0, ".", "");
									}
								}
								
								if(!empty($s['is_compliment'])){
									//$s['product_price_real'] = $s['product_price'];
									$s['tax_total'] = 0;
									$s['service_total'] = 0;
								}
								
								$total_billing_order = ($s['product_price_real']*$s['order_qty']);
								$tax_total_order = $s['tax_total'];
								$service_total_order = $s['service_total'];
								
								if($s['diskon_sebelum_pajak_service'] == 1){
									
									//$all_product_data[$s['product_id']]['grand_total'] += ($s['product_price_real']*$s['order_qty']) - $s['discount_total'];
									//$grand_total_order = ($s['product_price_real']*$s['order_qty']) - $s['discount_total'];
									
									$sub_total = ($s['product_price_real']*$s['order_qty']);
									//$net_sales = $sub_total;
									$sub_total += $s['tax_total'];
									$sub_total += $s['service_total'];
									
									$grand_total_order = $sub_total;
									$sub_total -= $s['discount_total'];
									//$net_sales -= $s['discount_total'];
									
								}else{
									
									//$all_product_data[$s['product_id']]['grand_total'] += ($s['product_price_real']*$s['order_qty']);
									//$grand_total_order = ($s['product_price_real']*$s['order_qty']);
									
									$sub_total = ($s['product_price_real']*$s['order_qty']);
									//$net_sales = $sub_total;
									$sub_total += $s['tax_total'];
									$sub_total += $s['service_total'];
									
									$grand_total_order = $sub_total;
									//$grand_total_order -= $s['discount_total'];
									$is_balanced = true;
								}
								
								//update-2001.002
								$net_sales = $total_billing_order - $s['discount_total'];
								$sub_total = $net_sales;
								$sub_total += $s['tax_total'];
								$sub_total += $s['service_total'];
								
								//$all_product_data[$s['product_id']]['total_billing'] += ($s['product_price_real']*$s['order_qty']);
								//$all_product_data[$s['product_id']]['tax_total'] += $s['tax_total'];
								//$all_product_data[$s['product_id']]['service_total'] += $s['service_total'];
							
							}else
							{
									
								$total_billing_order = ($s['product_price']*$s['order_qty']);
								$tax_total_order = $s['tax_total'];
								$service_total_order = $s['service_total'];
								
								if($s['diskon_sebelum_pajak_service'] == 1){
									
									//$all_product_data[$s['product_id']]['grand_total'] += ($s['product_price']*$s['order_qty']) - $s['discount_total'];
									//$grand_total_order = ($s['product_price']*$s['order_qty']) - $s['discount_total'];
									$sub_total = ($s['product_price']*$s['order_qty']);
									$net_sales = $sub_total;
									$sub_total += $s['tax_total'];
									$sub_total += $s['service_total'];
									
									$grand_total_order = $sub_total;
									$sub_total -= $s['discount_total'];
									//$net_sales -= $s['discount_total'];
									
									//update-2001.002
									//$net_sales = $total_billing_order - $s['discount_total'];
								
								}else{
									
									//after tax
									//$all_product_data[$s['product_id']]['grand_total'] += ($s['product_price']*$s['order_qty']);
									//$grand_total_order = ($s['product_price']*$s['order_qty']);
									$sub_total = ($s['product_price']*$s['order_qty']);
									//$net_sales = $sub_total;
									$sub_total += $s['tax_total'];
									$sub_total += $s['service_total'];
									
									$grand_total_order = $sub_total;
									$sub_total -= $s['discount_total'];
									
									//update-2001.002
									//$net_sales = $total_billing_order - $s['discount_total'];
								
								}
								
								//update-2001.002
								$net_sales = $total_billing_order - $s['discount_total'];
							
								//$all_product_data[$s['product_id']]['total_billing'] += ($s['product_price']*$s['order_qty']);
								//$all_product_data[$s['product_id']]['tax_total'] += $s['tax_total'];
								//$all_product_data[$s['product_id']]['service_total'] += $s['service_total'];
								
							}


							if(empty($data_diskon_awal[$s['product_id']])){
								$data_diskon_awal[$s['product_id']] = array(
									'item'	=> 0,
									'billing'	=> 0,
									'item_before'	=> 0,
									'billing_before'	=> 0,
									'item_after'	=> 0,
									'billing_after'	=> 0,
								);
							}
							
							//cek if discount is disc billing
							$total_discount_product = 0;
							if($s['discount_perbilling'] == 1){

								$get_percentage = $s['billing_discount_percentage'];
								$sub_total_detail = ($s['product_price']*$s['order_qty']);
								
								$s['discount_total'] = priceFormat(($total_billing_order*($get_percentage/100)), 0, ".", "");
								
								if(empty($s['billing_discount_percentage']) OR $s['billing_discount_percentage'] == '0.00'){
									//persentase dr total billing
									$get_percentage = ($sub_total_detail / $s['grand_total']) * 100;
									$get_percentage = number_format($get_percentage,2,'.','');
									$s['discount_total'] = priceFormat(($s['billing_discount_total']*($get_percentage/100)), 0, ".", "");
								}
								
								$all_product_data[$s['product_id']]['discount_billing_total'] += $s['discount_total'];
								$total_discount_product = $s['discount_total'];
								//echo '1. total_billing_order = '.$total_billing_order.',get_percentage = '.$get_percentage.',total_discount_product = '.$total_discount_product.'<br/>';
								$data_diskon_awal[$s['product_id']]['billing'] += $total_discount_product;

								if($s['diskon_sebelum_pajak_service'] == 1){
									$data_diskon_awal[$s['product_id']]['billing_before'] += $total_discount_product;
									$all_product_data[$s['product_id']]['discount_billing_total_before'] += $s['discount_total'];
								}else{
									$data_diskon_awal[$s['product_id']]['billing_after'] += $total_discount_product;
									$all_product_data[$s['product_id']]['discount_billing_total_after'] += $s['discount_total'];
								}
								
							}else{
								$all_product_data[$s['product_id']]['discount_total'] += $s['discount_total'];
								$total_discount_product = $s['discount_total'];
								//echo '2. total_discount_product = '.$total_discount_product.'<br/>';
								$data_diskon_awal[$s['product_id']]['item'] += $total_discount_product;
								
								if($s['diskon_sebelum_pajak_service'] == 1){
									$data_diskon_awal[$s['product_id']]['item_before'] += $total_discount_product;
									$all_product_data[$s['product_id']]['discount_total_before'] += $s['discount_total'];
								}else{
									$data_diskon_awal[$s['product_id']]['item_after'] += $total_discount_product;
									$all_product_data[$s['product_id']]['discount_total_after'] += $s['discount_total'];
								}
								
							}
							
							if($s['free_item'] == 1){
								if(!empty($include_tax) OR !empty($include_service)){
									$total_billing_order = ($s['product_price_real']*$s['order_qty']); 
								}else{
									$total_billing_order = ($s['product_price']*$s['order_qty']); 
								}
								//$total_billing_order = ($s['product_price']*$s['order_qty']); 
								$grand_total_order = $s['discount_total'];
								$total_billing = $grand_total_order;
							}else{
								$total_billing = $grand_total_order;
							}

							//echo '$total_billing_order = '.$total_billing_order.'<br/>';
							//echo '$tax_total_order = '.$tax_total_order.'<br/>';
							//echo '$service_total_order = '.$service_total_order.'<br/>';
							
							$all_product_data[$s['product_id']]['total_hpp'] += ($s['product_price_hpp']*$s['order_qty']);
							$all_product_data[$s['product_id']]['total_billing'] += $total_billing_order;
							$all_product_data[$s['product_id']]['tax_total'] += $tax_total_order;
							$all_product_data[$s['product_id']]['service_total'] += $service_total_order;
								
							//$all_product_data[$s['product_id']]['grand_total'] += $s['tax_total'];
							//$all_product_data[$s['product_id']]['grand_total'] += $s['service_total'];
							
							$skip_balancing = false;
								
							
							//COMPLIMENT
							$compliment_total = 0;
							if(!empty($s['is_compliment'])){
								
								$compliment_total = $grand_total_order;
								
								$s['service_total'] = 0;
								$s['tax_total'] = 0;
								$sub_total = 0;
								$net_sales = 0;
								$grand_total_order = 0;
								$skip_balancing = true;
								
								$all_product_data[$s['product_id']]['compliment_total'] += $compliment_total;
								//$all_product_data[$s['product_id']]['grand_total'] -= $compliment_total;
								$all_product_data[$s['product_id']]['is_compliment'] = 1;
								
							}
							
							$all_product_data[$s['product_id']]['net_sales'] += $net_sales;
							$all_product_data[$s['product_id']]['sub_total'] += $sub_total;
							$all_product_data[$s['product_id']]['grand_total'] += $grand_total_order;
							
							//OVERRIDE PEMBULATAN PERITEM
							$total_pembulatan = 0;
								
							$all_product_data[$s['product_id']]['total_pembulatan'] += $total_pembulatan;
							$all_product_data[$s['product_id']]['grand_total'] += $total_pembulatan;
							
							$grand_total_order += $total_pembulatan;
							
							/*$compliment_total = 0;
							if(!empty($s['is_compliment'])){
								$compliment_total = $grand_total_order;
								$grand_total_order -= $compliment_total;
								$all_product_data[$s['product_id']]['compliment_total'] += $compliment_total;
								$all_product_data[$s['product_id']]['grand_total'] -= $compliment_total;
								$all_product_data[$s['product_id']]['is_compliment'] = 1;
							}*/
							
							if(!empty($s['payment_id'])){
								if(empty($all_product_data[$s['product_id']]['payment_'.$s['payment_id']])){
									$all_product_data[$s['product_id']]['payment_'.$s['payment_id']] = 0;
								}
								if(empty($all_product_data[$s['product_id']]['payment_1'])){
									$all_product_data[$s['product_id']]['payment_1'] = 0;
								}
								
								//$all_product_data[$s['product_id']]['payment_'.$s['payment_id']] += $grand_total_order;
								//echo '$grand_total_order, '.$s['is_half_payment'].', '.$s['payment_id'].' += '.$grand_total_order.'<br/><br/>';
								
								//credit half payment
								if(!empty($s['is_half_payment']) AND $s['payment_id'] != 1){
									
									if(!empty($s['total_dp'])){
										$s['total_cash'] += $s['total_dp'];
										$s['total_credit'] -= $s['total_dp'];
									}
									
									if(empty($balancing_payment_billing[$s['billing_id']])){
										
										$total_payment = $s['total_cash']+$s['total_credit'];
										$percent_halfpayment_cash = priceFormat(($s['total_cash']/$total_payment), 2, ".", "");
										$percent_halfpayment_credit = 1-$percent_halfpayment_cash;
										$balancing_payment_billing[$s['billing_id']] = array(
											'total_cash'	=> $s['total_cash'],
											'total_credit'	=> $s['total_credit'],
											'total_payment'	=> $total_payment,
											'percent_cash'	=> $percent_halfpayment_cash,
											'percent_credit'=> $percent_halfpayment_credit,
											'curr_cash'		=> 0,
											'curr_credit'	=> 0,
											'curr_total_payment'	=> 0,
											'detail'		=> array(),
											'total_item'	=> 0
										);
									}
									
									$grand_total_order_cash = $grand_total_order*$balancing_payment_billing[$s['billing_id']]['percent_cash'];
									$grand_total_order_cash = ceil($grand_total_order_cash);
									$grand_total_order_credit = $grand_total_order-$grand_total_order_cash;
									
									//echo '+cash = '.$grand_total_order_cash.'<br/><br/>';
									if($balancing_payment_billing[$s['billing_id']]['curr_cash']+$grand_total_order_cash <= $s['total_cash']){
										//echo 'cash = '.$balancing_payment_billing[$s['billing_id']]['curr_cash']+$grand_total_order_cash.' < '.$s['total_cash'].'<br/><br/>';
									
										$all_product_data[$s['product_id']]['payment_1'] += $grand_total_order_cash;
										$all_product_data[$s['product_id']]['payment_'.$s['payment_id']] += $grand_total_order_credit;
										$balancing_payment_billing[$s['billing_id']]['curr_cash'] += $grand_total_order_cash;
										$balancing_payment_billing[$s['billing_id']]['curr_credit'] += $grand_total_order_credit;
										$balancing_payment_billing[$s['billing_id']]['curr_total_payment'] += $grand_total_order_cash;
										$balancing_payment_billing[$s['billing_id']]['curr_total_payment'] += $grand_total_order_credit;
										$balancing_payment_billing[$s['billing_id']]['total_item'] += 1;
										
									}else{
										
										//echo 'curr cash = '.$balancing_payment_billing[$s['billing_id']]['curr_cash'].'<br/><br/>';
										//echo '+cash = '.$grand_total_order_cash.'<br/><br/>';
										//echo 'cash = '.($balancing_payment_billing[$s['billing_id']]['curr_cash']+$grand_total_order_cash).' > '.$s['total_cash'].'<br/><br/>';
									
										$payment_curr_cash = $balancing_payment_billing[$s['billing_id']]['curr_cash']+$grand_total_order_cash;
										//selisih
										$selisih_payment_cash = $payment_curr_cash - $s['total_cash'];
										$grand_total_order_cash = $grand_total_order_cash-$selisih_payment_cash;
										$grand_total_order_credit = $grand_total_order-$grand_total_order_cash;
										//echo 'cash = '.$grand_total_order_cash.', credit = '.$grand_total_order_credit.' dari '.$grand_total_order.'<br/><br/>';
										
										$all_product_data[$s['product_id']]['payment_1'] += $grand_total_order_cash;
										$all_product_data[$s['product_id']]['payment_'.$s['payment_id']] += $grand_total_order_credit;
										$balancing_payment_billing[$s['billing_id']]['curr_cash'] += $grand_total_order_cash;
										$balancing_payment_billing[$s['billing_id']]['curr_credit'] += $grand_total_order_credit;
										$balancing_payment_billing[$s['billing_id']]['curr_total_payment'] += $grand_total_order_cash;
										$balancing_payment_billing[$s['billing_id']]['curr_total_payment'] += $grand_total_order_credit;
										$balancing_payment_billing[$s['billing_id']]['total_item'] += 1;
										
									}
									
									//jika last item
									if(!empty($billing_detail_data[$s['billing_id']])){
										if($balancing_payment_billing[$s['billing_id']]['total_item'] >= count($billing_detail_data[$s['billing_id']])){
											if($balancing_payment_billing[$s['billing_id']]['curr_cash'] != $balancing_payment_billing[$s['billing_id']]['total_cash']){
												$selisih_curr_cash = $balancing_payment_billing[$s['billing_id']]['total_cash'] - $balancing_payment_billing[$s['billing_id']]['curr_cash'];
												if($selisih_curr_cash > 0){
													$balancing_payment_billing[$s['billing_id']]['curr_cash'] += $selisih_curr_cash;
													$balancing_payment_billing[$s['billing_id']]['curr_credit'] -= $selisih_curr_cash;
													$all_product_data[$s['product_id']]['payment_1'] += $selisih_curr_cash;
													$all_product_data[$s['product_id']]['payment_'.$s['payment_id']] -= $selisih_curr_cash;
										
												}else{
													$balancing_payment_billing[$s['billing_id']]['curr_cash'] -= $selisih_curr_cash;
													$balancing_payment_billing[$s['billing_id']]['curr_credit'] += $selisih_curr_cash;
													$all_product_data[$s['product_id']]['payment_1'] -= $selisih_curr_cash;
													$all_product_data[$s['product_id']]['payment_'.$s['payment_id']] += $selisih_curr_cash;
												}
											}
										}
									}
									
									if($balancing_payment_billing[$s['billing_id']]['curr_total_payment'] >= $balancing_payment_billing[$s['billing_id']]['total_payment']){
										//echo 'curr_cash = '.$balancing_payment_billing[$s['billing_id']]['curr_cash'].' = '.$balancing_payment_billing[$s['billing_id']]['total_cash'].'<br/><br/>';
										//check if cash = curr_cash
										if($balancing_payment_billing[$s['billing_id']]['curr_cash'] != $balancing_payment_billing[$s['billing_id']]['total_cash']){
											$selisih_curr_cash = $balancing_payment_billing[$s['billing_id']]['total_cash'] - $balancing_payment_billing[$s['billing_id']]['curr_cash'];
											if($selisih_curr_cash > 0){
												$balancing_payment_billing[$s['billing_id']]['curr_cash'] += $selisih_curr_cash;
												$balancing_payment_billing[$s['billing_id']]['curr_credit'] -= $selisih_curr_cash;
												$all_product_data[$s['product_id']]['payment_1'] += $selisih_curr_cash;
												$all_product_data[$s['product_id']]['payment_'.$s['payment_id']] -= $selisih_curr_cash;
									
											}else{
												$balancing_payment_billing[$s['billing_id']]['curr_cash'] -= $selisih_curr_cash;
												$balancing_payment_billing[$s['billing_id']]['curr_credit'] += $selisih_curr_cash;
												$all_product_data[$s['product_id']]['payment_1'] -= $selisih_curr_cash;
												$all_product_data[$s['product_id']]['payment_'.$s['payment_id']] += $selisih_curr_cash;
											}
										}
									}
									
								}else{
									$all_product_data[$s['product_id']]['payment_'.$s['payment_id']] += $grand_total_order;
								}
								
							}
							
							$discount_total = 0;
							$discount_billing_total = 0;
							if($s['discount_perbilling'] == 1){
								$discount_billing_total = $s['discount_total'];
							}else{
								$discount_total = ($s['discount_total']);
							}
								
							if($has_buyget == 1){
								$discount_total = ($s['discount_total']);
							}

							$dt_bill = $billing_data[$s['billing_id']];

							//PROMO BUY GET --------------------------------------------------------
							if(!empty($summary_promo_bill_id[$s['billing_id']])){
								if(!in_array($s['billing_id'], $summary_promo_bill_id_done)){
									//update 12062018
									$summary_promo_bill_id_done[] = $s['billing_id'];
									
									//$dt_bill = $summary_promo_bill_id[$s['billing_id']];

									$var_promo = 0;
									if(!empty($all_billing_discount_id[$s['billing_id']])){
										$var_promo = $all_billing_discount_id[$s['billing_id']];
									}
									

									$get_discount_type = 'NO PROMO';
									$get_discount_type_var = 'no_promo';
									
									if(!empty($var_promo)){
										
										if(!in_array($var_promo, $all_discount_id)){
											$all_discount_id[] = $var_promo;
										}
										
										if($dt_bill['discount_perbilling'] == 1){
											$get_discount_type = 'BILLING';
											$get_discount_type_var = 'billing';
										}else{
											$get_discount_type = 'ITEM';
											$get_discount_type_var = 'item';
										}
									}
									
									if(!empty($var_promo)){
										
										$discount_item[$s['billing_id']] = $var_promo;
										
										//$all_billing_discount_id[$s['billing_id']] = $var_promo;
										$summary_promo[$var_promo]['total_qty_billing'] += 1;
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

										
										if(!empty($payment_data)){
											foreach($payment_data as $key_id => $dtPay){
										
												$tot_payment = 0;
												$tot_payment_show = 0;
												if($dt_bill['payment_id'] == $key_id){
													//$tot_payment = $dt_bill['grand_total'];
													//$tot_payment_show = $dt_bill['grand_total_show'];
													
													//update AR - 2019-02-15
													if($key_id == 2 OR $key_id == 3 OR $key_id == 4){
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

								}

							}

							if(!empty($all_billing_discount_id[$s['billing_id']])){
								
								$get_disc_id = $all_billing_discount_id[$s['billing_id']];
								
								if(!empty($summary_promo[$get_disc_id])){
									
									$summary_promo[$get_disc_id]['total_qty'] += $s['order_qty'];
									$summary_promo[$get_disc_id]['total_hpp'] += ($s['product_price_hpp'] * $s['order_qty']);
									
									//sort
									$summary_promo_sort_qty[$get_disc_id] += $s['order_qty'];
								}
								
							}else{
								
								//NO PROMO
								$summary_promo[0]['total_qty'] += $s['order_qty'];
								$summary_promo[0]['total_hpp'] += ($s['product_price_hpp'] * $s['order_qty']);

								//sort
								$summary_promo_sort_qty[0] += $s['order_qty'];
								
							}
							//PROMO BUY GET --------------------------------------------------------

							/*$in_array_bil = in_array($s['billing_id'], $move_to_buyget);
							if($in_array_bil == 1){
								if(empty($no_bg)){
									$no_bg = 0;
								}
								$no_bg++;
								echo $no_bg.'. '.$s['id'].','.$s['billing_id'].', is_promo='.$s['promo_id'].', is_buyget='.$s['buyget_id'].',in='.$in_array_bil.', discid = '.$var_promo.', has_discount_on_detail='.$has_discount_on_detail.'<br/>';
							}*/

							//BUYGET ---------------------------------------------------
							if(in_array($s['billing_id'], $move_to_buyget) AND $has_discount_on_detail == 1){

								$get_disc_id = $s['discount_id'];
								
								if($s['is_promo'] == 1){
									$get_disc_id = $s['promo_id'];
								}
								if($s['is_buyget'] == 1){
									$get_disc_id = $s['buyget_id'];
								}
								
								//echo $s['billing_id'].', '.$get_disc_id.' = '.$s['discount_total'].', ref_order_id = '.$s['ref_order_id'].'<br/>';
								
								//$dt_bill = $billing_data[$s['billing_id']];
								if(!empty($summary_promo_buyget[$get_disc_id])){
										
									$s['net_sales'] = $total_billing - $discount_total;
									$s['total_compliment'] = $compliment_total;
									$s['compliment_total'] = $compliment_total;
									$s['total_compliment_show'] = 0;

									if(empty($billing_id_buyget_detail[$get_disc_id])){
										$billing_id_buyget_detail[$get_disc_id] = array();
									}
									if(!in_array($s['billing_id'], $billing_id_buyget_detail[$get_disc_id])){
										$billing_id_buyget_detail[$get_disc_id][] = $s['billing_id'];
										$summary_promo_buyget[$get_disc_id]['total_qty_billing'] += 1;
									}

									$summary_promo_buyget[$get_disc_id]['total_qty'] += $s['order_qty'];
									$summary_promo_buyget[$get_disc_id]['total_hpp'] += ($s['product_price_hpp'] * $s['order_qty']);;
									$summary_promo_buyget[$get_disc_id]['tax_total'] += $s['tax_total'];
									$summary_promo_buyget[$get_disc_id]['service_total'] += $s['service_total'];
									$summary_promo_buyget[$get_disc_id]['discount_total'] += $discount_total;
									$summary_promo_buyget[$get_disc_id]['discount_billing_total'] += $discount_billing_total;
									$summary_promo_buyget[$get_disc_id]['total_billing'] += $total_billing;
									$summary_promo_buyget[$get_disc_id]['sub_total'] += $sub_total;
									$summary_promo_buyget[$get_disc_id]['net_sales'] += $s['net_sales'];
									$summary_promo_buyget[$get_disc_id]['total_compliment'] += $compliment_total;
									$summary_promo_buyget[$get_disc_id]['compliment_total'] += $compliment_total;
									$summary_promo_buyget[$get_disc_id]['total_pembulatan'] += $total_pembulatan;
									$summary_promo_buyget[$get_disc_id]['grand_total'] += $grand_total_order;
									
									$summary_promo_buyget_sort_qty[$get_disc_id] += $s['order_qty'];
		
									if(!empty($payment_data)){
										foreach($payment_data as $key_id => $dtPay){
									
											$tot_payment = 0;
											if($dt_bill['payment_id'] == $key_id){
												if($key_id == 2 OR $key_id == 3 OR $key_id == 4){
													$tot_payment = $grand_total_order;	
												}else{
													$tot_payment = $grand_total_order;	
												}
												
												//credit half payment
												if(!empty($dt_bill['is_half_payment']) AND $key_id != 1){
													$tot_payment = $grand_total_order;
												}else{
													
													$tot_payment_show = priceFormat($tot_payment);	
												}
													
											}else{
												//cash
												if(!empty($dt_bill['is_half_payment']) AND $key_id == 1){
													$tot_payment = $grand_total_order;
												}
											}
									
											if(!empty($s['is_compliment'])){
												$tot_payment = 0;
											}
											
											if(!empty($get_disc_id)){
												$summary_promo_buyget[$get_disc_id]['payment_'.$key_id] += $tot_payment;
											}
																			
										}
									}

								}
									
							}
							//BUYGET ---------------------------------------------------

							//BALANCING DISKON
							if(!empty($s['billing_discount_total'])){
								if(empty($balancing_discount_billing[$s['billing_id']])){
									$balancing_discount_billing[$s['billing_id']] = array(
										'billing_no'			=> $s['billing_no'],
										'discount_total'		=> $s['billing_discount_total'],
										'discount_detail_total'	=> 0,
										'payment_id'			=> 0,
										'total_billing'			=> 0,
										'sub_total'				=> 0,
										'discount_perbilling'	=> $s['discount_perbilling'],
										'buyget'				=> 0,
										'free'					=> 0,
										'package'				=> 0,
										'discount_detail'		=> array(),
										'is_balanced'			=> 0,
										'diskon_sebelum_pajak_service' => $s['diskon_sebelum_pajak_service']
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
								$balancing_discount_billing[$s['billing_id']]['total_billing'] += $total_billing;
								$balancing_discount_billing[$s['billing_id']]['sub_total'] += $sub_total;

								//package
								if($s['package_item'] == 1){
									$balancing_discount_billing[$s['billing_id']]['package'] += 1;
								}
								
								//buyget
								if($s['is_buyget'] == 1){
									$balancing_discount_billing[$s['billing_id']]['buyget'] += 1;
								}

								//free
								if($s['free_item'] == 1){
									$balancing_discount_billing[$s['billing_id']]['free'] += 1;
								}
							}

							if(!empty($total_billing) AND empty($s['is_compliment'])){
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
				}
			}
			
			//echo '$all_qty_billing = '.count($all_qty_billing).'<br/>';
			//echo '$all_qty_item = '.$all_qty_item.'<br/>';
			//echo 'balancing_discount_billing :'.count($balancing_discount_billing).'<br/>';
			//echo '<pre>';
			//print_r($balancing_discount_billing);
			//die();
			
			//summary_promo_buyget
			$summary_promo_buyget_new = $summary_promo_buyget;
			$summary_promo_buyget = array();
			if(!empty($summary_promo_buyget_new)){

				if(!empty($summary_promo_buyget_sort_qty)){

					//sort
					arsort($summary_promo_buyget_sort_qty);
					foreach($summary_promo_buyget_sort_qty as $disc_id => $qty){
						if(!empty($summary_promo_buyget_new[$disc_id])){
							$dt = $summary_promo_buyget_new[$disc_id];

							$dt['total_profit'] = $dt['total_billing']-$dt['total_hpp'];
							$dt['total_hpp_show'] = priceFormat($dt['total_hpp']);
							$dt['total_profit_show'] = priceFormat($dt['total_profit']);
							$dt['total_billing_show'] = priceFormat($dt['total_billing']);
							$dt['discount_total_show'] = priceFormat($dt['discount_total']);
							$dt['discount_billing_total_show'] = priceFormat($dt['discount_billing_total']);
							$dt['tax_total_show'] = priceFormat($dt['tax_total']);
							$dt['service_total_show'] = priceFormat($dt['service_total']);
							$dt['sub_total_show'] = priceFormat($dt['sub_total']);
							$dt['total_pembulatan_show'] = priceFormat($dt['total_pembulatan']);
							$dt['total_compliment_show'] = priceFormat($dt['total_compliment']);
							$dt['grand_total_show'] = priceFormat($dt['grand_total']);
							
							$summary_promo_buyget[] = $dt;
						}
					}
				}

			}
			
			$data_post['summary_promo_buyget'] = $summary_promo_buyget;

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
			//$data_diskon_awal = array();
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
					/*$selisih_diskon_perproduct = 0;
					if($selisih_diskon != 0){
						$selisih_diskon_perproduct = $selisih_diskon/$total_produk;
						$selisih_diskon_perproduct = number_format($selisih_diskon_perproduct, 2);
					}*/
					
					$discount_detail_total = 0;
					$discount_billing_detail_total = 0;
					
					//echo '<br/>$billing_id = '.$billing_id.', total_billing = '.$dt['total_billing'].', discount_total = '.$dt['discount_total'].', discount_perbilling='.$dt['discount_perbilling'].', $total_produk = '.$total_produk.'<br/>';

					if(!empty($dt['discount_detail'])){
						
						$no = 0;
						$persentase_total_billing = 0;
						foreach($dt['discount_detail'] as $product_id => $dt_diskon){
							$no++;
							
							//average
							$discount_billing_detail_total = $dt_diskon['total_discount'];
							
							//PERSENTASE DISKON - average by total billing percentage
							$total_disc_prod = 0;
							$persentase_disc_prod = 0;
							if($dt['discount_perbilling'] == 1){
								$total_disc_prod = 0;
								$persentase_disc_prod = ($dt_diskon['total_billing'] / $dt['total_billing']) * 100;
								$persentase_disc_prod = priceFormat($persentase_disc_prod, 2, ".", "");
								$persentase_total_billing += $persentase_disc_prod;

								if($no == $total_produk){
									if($persentase_total_billing != 100){
										$persentase_disc_prod += (100 - $persentase_total_billing);
									}
								}

								$total_disc_prod = ($persentase_disc_prod*$dt['discount_total'])/100;

								//$discount_billing_detail_total += ($dt_diskon['total_discount']+$total_disc_prod);

								//DISCOUNT > total billing
								//echo '$total_disc_prod = '.$total_disc_prod.' > sub_total = '.$dt_diskon['sub_total'].'<br/>';
								if($total_disc_prod > $dt_diskon['sub_total']){
									//$total_disc_prod = $dt_diskon['sub_total'];
								}

								//$discount_billing_detail_total = ($dt_diskon['total_discount']+$total_disc_prod);
								$discount_billing_detail_total = $total_disc_prod;
							}
							
							$discount_detail_total += $discount_billing_detail_total;
							//echo '$discount_billing_detail_total = '.$discount_billing_detail_total.'<br/>';
							//echo '$discount_detail_total = '.$discount_detail_total.'<br/>';

							//echo 'CEK1 -> '.$product_id.' total_discount = '.$dt_diskon['total_discount'].', total_disc_prod = '.$total_disc_prod.',<br/> discount_billing_detail_total = '.$discount_billing_detail_total.'<br/>';
							//echo 'persentase_disc_prod = '.$persentase_disc_prod.', persentase_total_billing = '.$persentase_total_billing.'<br/>';
							
							/*if(empty($data_diskon_awal[$product_id])){
								$data_diskon_awal[$product_id] = array(
									'item'	=> 0,
									'billing'	=> 0
								);
							}*/

							if(empty($data_balancing_diskon[$product_id])){
								$data_balancing_diskon[$product_id] = array(
									'item'	=> 0,
									'billing'	=> 0,
									'item_before'	=> 0,
									'billing_before'	=> 0,
									'item_after'	=> 0,
									'billing_after'	=> 0,
								);
							}
							
							
							if(empty($data_balancing_diskon_payment[$product_id])){
								$data_balancing_diskon_payment[$product_id] = array();
							}
							if(empty($data_balancing_diskon_payment[$product_id][$dt['payment_id']])){
								$data_balancing_diskon_payment[$product_id][$dt['payment_id']] = 0;
							}

							if($dt['discount_perbilling'] == 1){
								//$data_diskon_awal[$product_id]['billing'] += $discount_billing_detail_total;
								$data_balancing_diskon[$product_id]['billing'] += $discount_billing_detail_total;
								$data_balancing_diskon_payment[$product_id][$dt['payment_id']] += $discount_billing_detail_total;
								
								if($dt['diskon_sebelum_pajak_service'] == 1){
									$data_balancing_diskon[$product_id]['billing_before'] += $discount_billing_detail_total;
								}else{
									$data_balancing_diskon[$product_id]['billing_after'] += $discount_billing_detail_total;
								}
								
							}else{
								//$data_diskon_awal[$product_id]['item'] += $discount_billing_detail_total;
								$data_balancing_diskon[$product_id]['item'] += $discount_billing_detail_total;
								$data_balancing_diskon_payment[$product_id][$dt['payment_id']] += $discount_billing_detail_total;
								
								if($dt['diskon_sebelum_pajak_service'] == 1){
									$data_balancing_diskon[$product_id]['item_before'] += $discount_billing_detail_total;
								}else{
									$data_balancing_diskon[$product_id]['item_after'] += $discount_billing_detail_total;
								}
							}
							
							$balancing_discount_billing[$billing_id]['discount_detail'][$product_id]['total_discount_balance'] = $discount_billing_detail_total;
							
							//echo 'CEK2 -> '.$product_id.' #1 total_billing = '.$dt_diskon['total_billing'].', total_discount_balance = '.$discount_billing_detail_total.' => discount_detail_total = '.$discount_detail_total.'<br/>';

							/*
							//perbilling or package
							if($no == $total_produk AND ($dt['discount_perbilling'] == 1)){

								//$balancing_discount_billing[$billing_id]['discount_detail_total'] = $discount_detail_total;

								if($discount_detail_total != $dt['discount_total']){
								//if($dt['discount_detail_total'] != $dt['discount_total']){
									$discount_detail_total = priceFormat($discount_detail_total, 2, ".", "");	
									$selisih_akhir =  $dt['discount_total'] - $discount_detail_total;
									//$selisih_akhir =  $dt['discount_total'] - $dt['discount_detail_total'];
									
									//echo 'CEK4 -> '.$product_id.', discount_total = '.$dt['discount_total'].', - discount_detail_total = '.$discount_detail_total.' => discount_billing_detail_total '.$discount_billing_detail_total.', selisih_akhir = '.$selisih_akhir.', data_balancing_diskon_billing => '.$data_balancing_diskon[$product_id]['billing'].', total_discount_balance = '.$balancing_discount_billing[$billing_id]['discount_detail'][$product_id]['total_discount_balance'].'<br/>';

									if($dt['discount_perbilling'] == 1){
										$data_balancing_diskon[$product_id]['billing'] += $selisih_akhir;
									}else{
										$data_balancing_diskon[$product_id]['item'] += $selisih_akhir;
									}
									
									$balancing_discount_billing[$billing_id]['discount_detail'][$product_id]['total_discount_balance'] -= $selisih_akhir;

									//echo 'CEK5 -> '.$product_id.', total_billing = '.$dt_diskon['total_billing'].', selisih_akhir = '.$selisih_akhir.'<br/><br/>';
									
								}
							}
							*/

							//echo '<br/>';

						}
						
					}
				}
				
				//SET SELISIH DISKON
				if(!empty($balancing_discount_billing)){
					foreach($balancing_discount_billing as $billing_id => $dt){
						if(!empty($dt['discount_detail'])){
							//echo 'SSD #'.$billing_id.', discount_perbilling = '.$dt['discount_perbilling'].'<br/>';
							//echo '<pre>';
							//print_r($dt);
							$discount_detail_total = 0;
							foreach($dt['discount_detail'] as $product_id => $dt_diskon){
								
								//$sub_total_balance = $dt_diskon['total_billing'] - $dt_diskon['total_discount'];
								$sub_total_balance = $dt_diskon['total_billing'];
								//echo '$sub_total_balance = '.$sub_total_balance.'<br/>';

								if($sub_total_balance <= 0){
									$sub_total_balance = 0;
								}else{
									$sub_total_balance += $dt_diskon['tax_total'];
									$sub_total_balance += $dt_diskon['service_total'];
								}

								$discount_detail_total += $sub_total_balance;
								//echo '$sub_total_balance = '.$sub_total_balance.'<br/>';

								//echo $product_id.' total_billing = '.$dt_diskon['total_billing'].' -  total_discount = '.$dt_diskon['total_discount'].', +tax_total = '.$dt_diskon['tax_total'].', +service_total = '.$dt_diskon['service_total'].' ==> sub_total_balance = '.$sub_total_balance.', discount_detail_total = '.$discount_detail_total.'<br/>';

								$balancing_discount_billing[$billing_id]['discount_detail'][$product_id]['sub_total_balance'] = $sub_total_balance;
								
								$sub_total_selisih = 0;
								//KONDISI SELISIH 1: sub_total > $sub_total_balance
								if($dt_diskon['sub_total'] > $sub_total_balance){
									//echo '$sub_total = '.$dt_diskon['sub_total'].' > $sub_total_balance = '.$sub_total_balance.'<br/>';
									$sub_total_selisih = $dt_diskon['sub_total'] - $sub_total_balance;
								}

								//KONDISI SELISIH 2: total_discount_balance > $sub_total_balance
								if($dt_diskon['total_discount_balance'] > $sub_total_balance){
									//echo '$total_discount_balance = '.$dt_diskon['total_discount_balance'].' > $sub_total_balance = '.$sub_total_balance.'<br/>';
									$sub_total_selisih = $sub_total_balance - $dt_diskon['total_discount_balance'];
								}


								$balancing_discount_billing[$billing_id]['discount_detail'][$product_id]['discount_balance'] = $sub_total_selisih;
								
								//echo 'sub_total_balance = '.$sub_total_balance.' <> sub_total = '.$dt_diskon['sub_total'].', sub_total_selisih = '.$sub_total_selisih.' <br/>';

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
							//echo '<br/>';
						}
					}
				}
			}
			
			
			//echo '<pre>';
			//echo '$data_diskon_awal: <br/>';
			//print_r($data_diskon_awal);
			//echo '$data_balancing_diskon: <br/>';
			//print_r($data_balancing_diskon);
			//echo '$data_balancing_diskon_payment: <br/>';
			//print_r($data_balancing_diskon_payment);
			//echo '$data_selisih_diskon: <br/>';
			//print_r($data_selisih_diskon);
			//echo '$data_selisih_diskon_payment: <br/>';
			//print_r($data_selisih_diskon_payment);
			//echo '$balancing_discount_billing: <br/>';
			//print_r($balancing_discount_billing);
			//echo 'TOTAL = '.count($all_product_data);
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
				'total_profit_show'	=> 0,
				'discount_total_before'	=> 0,
				'discount_total_before_show'	=> 0,
				'discount_billing_total_before'	=> 0,
				'discount_billing_total_before_show'	=> 0,
				'discount_total_after'	=> 0,
				'discount_total_after_show'	=> 0,
				'discount_billing_total_after'	=> 0,
				'discount_billing_total_after_show'	=> 0,
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
				'total_profit_show'	=> 0,
				'discount_total_before'	=> 0,
				'discount_total_before_show'	=> 0,
				'discount_billing_total_before'	=> 0,
				'discount_billing_total_before_show'	=> 0,
				'discount_total_after'	=> 0,
				'discount_total_after_show'	=> 0,
				'discount_billing_total_after'	=> 0,
				'discount_billing_total_after_show'	=> 0,
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
							
							$summary_billing[$tipe]['discount_billing_total_before'] += $dt['discount_billing_total_before'];
							$summary_billing[$tipe]['discount_billing_total_after'] += $dt['discount_billing_total_after'];
							$summary_billing[$tipe]['discount_total_before'] += $dt['discount_total_before'];
							$summary_billing[$tipe]['discount_total_after'] += $dt['discount_total_after'];
							
							if(!empty($payment_data)){
								foreach($payment_data as $key_id => $dtPay){
							
									$tot_payment = 0;
									$tot_payment_show = 0;
									if($dt['payment_id'] == $key_id){
										//$tot_payment = $dt['grand_total'];
										//$tot_payment_show = $dt['grand_total_show'];
										
										if($key_id == 2 OR $key_id == 3 OR $key_id == 4){
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
					
					$detail['discount_total_before_show'] = priceFormat($detail['discount_total_before']);
					$detail['discount_billing_total_before_show'] = priceFormat($detail['discount_billing_total_before']);
					$detail['discount_total_after_show'] = priceFormat($detail['discount_total_after']);
					$detail['discount_billing_total_after_show'] = priceFormat($detail['discount_billing_total_after']);
					
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
						
						//before&after
						$dt['discount_total_before'] -= $data_diskon_awal[$dt['product_id']]['item_before'];
						$dt['discount_billing_total_before'] -= $data_diskon_awal[$dt['product_id']]['billing_before'];
						$dt['discount_total_after'] -= $data_diskon_awal[$dt['product_id']]['item_after'];
						$dt['discount_billing_total_after'] -= $data_diskon_awal[$dt['product_id']]['billing_after'];
					}
					
					if(!empty($data_balancing_diskon[$dt['product_id']])){
						$dt['discount_total'] += $data_balancing_diskon[$dt['product_id']]['item'];
						$dt['discount_billing_total'] += $data_balancing_diskon[$dt['product_id']]['billing'];
						
						//before&after
						$dt['discount_total_before'] += $data_balancing_diskon[$dt['product_id']]['item_before'];
						$dt['discount_billing_total_before'] += $data_balancing_diskon[$dt['product_id']]['billing_before'];
						$dt['discount_total_after'] += $data_balancing_diskon[$dt['product_id']]['item_after'];
						$dt['discount_billing_total_after'] += $data_balancing_diskon[$dt['product_id']]['billing_after'];
					}

					//echo 'sub_total='.$dt['sub_total'].'<br/>';
					//echo 'discount_total='.$dt['discount_total'].'<br/>';
					//echo 'discount_billing_total='.$dt['discount_billing_total'].'<br/>';
					
					$dt['grand_total'] -=$dt['discount_total'];
					$dt['grand_total'] -=$dt['discount_billing_total'];

					//echo 'grandtotal='.$dt['grand_total'].'<br/>';

					
					if(!empty($data_selisih_diskon[$dt['product_id']])){
						//$dt['sub_total'] -= $data_selisih_diskon[$dt['product_id']];
						$dt['grand_total'] -= $data_selisih_diskon[$dt['product_id']];
					}
					
					//BALANCING DISKON PAYMENT
					if(!empty($data_balancing_diskon_payment[$dt['product_id']])){
						foreach($data_balancing_diskon_payment[$dt['product_id']] as $payment_id => $dtP){
							if(!empty($dt['payment_'.$payment_id])){
								$dt['payment_'.$payment_id] -= $dtP;
							}
						}
					}

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
						//$dt['compliment_total'] += $selisih_pembulatan;
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
					
					$dt['discount_total_before_show'] = priceFormat($dt['discount_total_before']);
					$dt['discount_billing_total_before_show'] = priceFormat($dt['discount_billing_total_before']);
					$dt['discount_total_after_show'] = priceFormat($dt['discount_total_after']);
					$dt['discount_billing_total_after_show'] = priceFormat($dt['discount_billing_total_after']);
					
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
						'total_dp_show'	=> 0,
						'discount_total_before'	=> 0,
						'discount_total_before_show'	=> 0,
						'discount_billing_total_before'	=> 0,
						'discount_billing_total_before_show'	=> 0,
						'discount_total_after'	=> 0,
						'discount_total_after_show'	=> 0,
						'discount_billing_total_after'	=> 0,
						'discount_billing_total_after_show'	=> 0,
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
				
				$summary_fnb[$ProdGroup]['discount_billing_total_before'] += $dt['discount_billing_total_before'];
				$summary_fnb[$ProdGroup]['discount_billing_total_after'] += $dt['discount_billing_total_after'];
				$summary_fnb[$ProdGroup]['discount_total_before'] += $dt['discount_total_before'];
				$summary_fnb[$ProdGroup]['discount_total_after'] += $dt['discount_total_after'];
				
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
						'total_dp_show'	=> 0,
						'discount_total_before'	=> 0,
						'discount_total_before_show'	=> 0,
						'discount_billing_total_before'	=> 0,
						'discount_billing_total_before_show'	=> 0,
						'discount_total_after'	=> 0,
						'discount_total_after_show'	=> 0,
						'discount_billing_total_after'	=> 0,
						'discount_billing_total_after_show'	=> 0,
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
				
				$summary_fnb_category[$ProdGroup][$category_id]['discount_billing_total_before'] += $dt['discount_billing_total_before'];
				$summary_fnb_category[$ProdGroup][$category_id]['discount_billing_total_after'] += $dt['discount_billing_total_after'];
				$summary_fnb_category[$ProdGroup][$category_id]['discount_total_before'] += $dt['discount_total_before'];
				$summary_fnb_category[$ProdGroup][$category_id]['discount_total_after'] += $dt['discount_total_after'];
				
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

				if(!empty($summary_promo_sort_qty)){
					//sort
					arsort($summary_promo_sort_qty);
					foreach($summary_promo_sort_qty as $disc_id => $qty){
						if(!empty($summary_promo_new[$disc_id])){
							$detail = $summary_promo_new[$disc_id];

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
							
							//total item discount - not allow NO PROMO
							if(!empty($disc_id)){
								$summary_promo[] = $detail;	
								$data_post['summary_data']['total_of_item_discount'] += $detail['total_qty'];
							}

						}
					}
				}

				/*foreach($summary_promo_new as $key => $detail){
					
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
					
					//total item discount
					if(!empty($key)){
						$summary_promo[$key] = $detail;	
						$data_post['summary_data']['total_of_item_discount'] += $detail['total_qty'];
					}

				}*/
				
			}
			//ksort($summary_promo);
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
			
			
			//SUMMARY CANCEL
			$summary_cancel_new = $summary_cancel;
			$summary_cancel = array();
			if(!empty($summary_cancel_new)){
				foreach($summary_cancel_new as $key => $detail){
					
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
					
					$summary_cancel[$key] = $detail;					
				}
				
			}
			ksort($summary_cancel);
			$data_post['summary_cancel'] = $summary_cancel;
			
			
			$data_post['report_data'] = $newData;
			$data_post['payment_data'] = $dt_payment_name;
			$data_post['display_discount_type'] = $display_discount_type;
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