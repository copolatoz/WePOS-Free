<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class SalesByDiscount extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_databilling', 'm');
		$this->load->model('model_billingdetail', 'm2');
	}
	
	public function print_salesByDiscount(){
		
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
			'report_name'	=> 'SALES BY DISCOUNT',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'user_fullname'	=> $user_fullname,
			'sorting'	=> $sorting,
			'diskon_sebelum_pajak_service' => 0
		);
		
		$get_opt = get_option_value(array('report_place_default','diskon_sebelum_pajak_service','cashier_max_pembulatan','cashier_pembulatan_keatas'));
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
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till = date("Y-m-d",strtotime($date_till));
			$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
			
			$add_where = "(a.payment_date >= '".$qdate_from." 07:00:01' AND a.payment_date <= '".$qdate_till_max." 06:00:00')";
			
			$billing_promo = array();
			$billing_promo_discount_id = array();
			$billing_buyget = array();
			$billing_buyget_discount_id = array();
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
			
			$all_discount_id = array();
			$all_billing_discount_id = array();
			$summary_promo_bill_id = array();
			
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
					if($data_post['diskon_sebelum_pajak_service'] == 0){
						$s['sub_total'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];		
					}else{
						$s['sub_total'] = $s['total_billing'] - $s['discount_total'] + $s['tax_total'] + $s['service_total'];
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
					
					$get_discount_type = '-';
					$get_discount_type_var = 'no_promo';
					
					if(empty($s['discount_id'])){
						$s['discount_id'] = 0;
						
						//if(!empty($billing_buyget_discount_id[$s['id']])){
						//	$s['discount_id'] = $billing_buyget_discount_id[$s['id']];
						//}
					}
					
					if(!empty($s['discount_id'])){
						if($s['discount_perbilling'] == 1){
							$get_discount_type = 'BILLING';
							$get_discount_type_var = 'billing';
						}else{
							$get_discount_type = 'ITEM';
							$get_discount_type_var = 'item';
						}
					}
					
					//ALL
					if(!empty($discount_type) AND in_array($discount_type, array('item','billing'))){
						
						$has_promo = false;
						if($discount_type == 'item'){
							
							if(!empty($billing_buyget_discount_id[$s['id']])){
								$summary_promo_bill_id[$s['id']] = $s;
								$has_promo = true;
							}
							if(!empty($billing_promo_discount_id[$s['id']])){
								$summary_promo_bill_id[$s['id']] = $s;
								$has_promo = true;
							}
						}
						
						if(!empty($s['discount_id']) AND $has_promo == false){
							if(!empty($s['discount_total'])){
								$all_billing_discount_id[$s['billing_id']] = $s['discount_id'];
							}
							
							$newData[$s['id']] = $s;
						}else{
							//echo $s['id'].'';
							//echo '<br/>';
						}
						
					}else{
						
						//BUYGET
						if(!empty($billing_buyget_discount_id[$s['id']])){
							if(!in_array($discount_type, array('no_promo','billing'))){
								$summary_promo_bill_id[$s['id']] = $s;
							}else{
								//echo $s['id'].'';
								//echo '<br/>';
							}
						}else{
							
							//PROMO
							if(!empty($billing_promo_discount_id[$s['id']])){
								if(!in_array($discount_type, array('no_promo','billing'))){
									$summary_promo_bill_id[$s['id']] = $s;
								}else{
									//echo $s['id'].'';
									//echo '<br/>';
								}
							}else{
								
								if(!empty($s['discount_total'])){
									$all_billing_discount_id[$s['billing_id']] = $s['discount_id'];
								}
								
								$newData[$s['id']] = $s;
								//array_push($newData, $s);
							}
						}
						
					}					
					
				}
			}
			
			//echo $discount_type.'<pre>TOT:'.count($data_post['report_data']);
			//print_r($data_post['report_data']);
			//die();
			
			//calc detail
			$total_hpp = array();
			$discount_item = array();
			$summary_promo_bill_id_done = array();
			if(!empty($all_bil_id)){
				$all_bil_id_txt = implode(",",$all_bil_id);
				$this->db->from($this->table2);
				$this->db->where('billing_id IN ('.$all_bil_id_txt.')');
				$this->db->where('is_deleted', 0);
				$get_detail = $this->db->get();
				if($get_detail->num_rows() > 0){
					foreach($get_detail->result() as $dtRow){
						
						$total_qty = $dtRow->order_qty;
						
						if(empty($total_hpp[$dtRow->billing_id])){
							$total_hpp[$dtRow->billing_id] = 0;
						}
						
						$total_hpp[$dtRow->billing_id] += $dtRow->product_price_hpp * $total_qty;

						$get_total_hpp = $dtRow->product_price_hpp * $total_qty;
						
						if(empty($dtRow->discount_id)){
							$dtRow->discount_id = 0;
						}

						//PROMO BUY GET
						if(!empty($summary_promo_bill_id[$dtRow->billing_id])){
							
							if(!in_array($dtRow->billing_id, $summary_promo_bill_id_done)){
								
								$has_discount_on_detail = $dtRow->discount_id;
								
								if(empty($has_discount_on_detail)){
									$has_discount_on_detail = 0;
								}
								
								if($dtRow->is_buyget == 1 AND !empty($dtRow->buyget_id)){
									$has_discount_on_detail = $dtRow->buyget_id;
								}
								if($dtRow->is_promo == 1 AND !empty($dtRow->promo_id)){
									$has_discount_on_detail = $dtRow->promo_id;
								}
								
								if(!empty($billing_buyget_discount_id[$dtRow->billing_id])){
									$has_discount_on_detail = $billing_buyget_discount_id[$dtRow->billing_id];
								}
								if(!empty($billing_promo_discount_id[$dtRow->billing_id])){
									$has_discount_on_detail = $billing_promo_discount_id[$dtRow->billing_id];
								}
								
								if(!empty($has_discount_on_detail)){
									$summary_promo_bill_id_done[] = $dtRow->billing_id;
								}
								
								$dt_bill = $summary_promo_bill_id[$dtRow->billing_id];
								
								$new_var = $dtRow->billing_id;
								
								$get_discount_type = 'NO PROMO';
								$get_discount_type_var = 'no_promo';
								
								if(!empty($has_discount_on_detail)){
									
									if(!empty($has_discount_on_detail)){
										if(!in_array($has_discount_on_detail, $all_discount_id)){
											$all_discount_id[] = $has_discount_on_detail;
										}
									}
									
									if($dt_bill['discount_perbilling'] == 1){
										$get_discount_type = 'BILLING';
										$get_discount_type_var = 'billing';
									}else{
										$get_discount_type = 'ITEM';
										$get_discount_type_var = 'item';
									}
								}
								
								if(!empty($has_discount_on_detail)){
									if(empty($newData[$new_var])){
										$newData[$new_var] = $dt_bill;
										//$newData[$new_var]['total_qty'] = 0;
										$newData[$new_var]['total_hpp'] = 0;
									}
									
									if(empty($discount_item[$dtRow->billing_id])){
										$discount_item[$dtRow->billing_id] = $has_discount_on_detail;
									}
								}
						
								if(!empty($newData[$new_var])){
									//if(!in_array($dtRow->billing_id, $summary_promo_bill_id_done)){
										//$newData[$new_var]['total_qty'] += $total_qty;
										//$newData[$new_var]['total_hpp'] += $get_total_hpp;
									//}
								}
								
							}
							
						}else{
							
							if(!empty($dtRow->discount_id)){
								if(!in_array($dtRow->discount_id, $all_discount_id)){
									$all_discount_id[] = $dtRow->discount_id;
								}
							}
							
							if(empty($discount_item[$dtRow->billing_id])){
								$discount_item[$dtRow->billing_id] = $dtRow->discount_id;
							}
							
							$new_var = $dtRow->billing_id;
							if(!empty($newData[$new_var])){
								//$newData[$new_var]['total_qty'] += $total_qty;
								//$newData[$new_var]['total_hpp'] += $get_total_hpp;
							}
						
						}
						
					}
				}
			}
			
			$discount_data = array();
			$discount_data[0] = 'NO PROMO';
			if(!empty($all_discount_id)){
				
				$all_discount_id_sql = implode(",", $all_discount_id);
				$this->db->from($this->prefix.'discount');
				$this->db->where('id IN ('.$all_discount_id_sql.')');
				$get_discount = $this->db->get();
				if($get_discount->num_rows() > 0){
					foreach($get_discount->result() as $dtRow){
						$discount_data[$dtRow->id] = $dtRow->discount_name;
					}
				}
				
			}
			
			
			$data_post['discount_item'] = $discount_item;
			$data_post['discount_data'] = $discount_data;
			
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
		
		$useview = 'print_salesByDiscount';
		$data_post['report_name'] = 'SALES BY DISCOUNT';
		
		if($do == 'excel'){
			$useview = 'excel_salesByDiscount';
		}
		
		$this->load->view('../../billing/views/'.$useview, $data_post);	
	}
	
	public function print_salesByDiscountRecap(){
		
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
			'report_name'	=> 'SALES BY DISCOUNT RECAP',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'user_fullname'	=> $user_fullname,
			'sorting'	=> $sorting,
			'diskon_sebelum_pajak_service' => 0
		);
		
		$get_opt = get_option_value(array('report_place_default','diskon_sebelum_pajak_service','cashier_max_pembulatan','cashier_pembulatan_keatas'));
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
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till = date("Y-m-d",strtotime($date_till));
			$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
			
			$add_where = "(a.payment_date >= '".$qdate_from." 07:00:01' AND a.payment_date <= '".$qdate_till_max." 06:00:00')";
			
			$billing_promo = array();
			$billing_promo_discount_id = array();
			$billing_buyget = array();
			$billing_buyget_discount_id = array();
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
								if(!in_array($dtRow->billing_id, $billing_promo)){
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
			
			$all_discount_id = array();
			$all_billing_discount_id = array();
			
			$all_bil_id = array();
			$newData = array();
			$dt_payment = array();
			$summary_promo_bill_id = array();
			
			$discount_data = array();
			$discount_data[0] = 'NO PROMO';
				
			if(!empty($discount_type)){
				if($discount_type == 'no_promo'){
					$newData['0'] = array(
						'discount_id'	=> 0,
						'discount_name'	=> 'NO PROMO',
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
						'total_dp'	=> 0,
						'total_dp_show'	=> 0,
					);
				}
			}else{
				$newData['0'] = array(
					'discount_id'	=> 0,
					'discount_name'	=> 'NO PROMO',
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
					'total_dp'	=> 0,
					'total_dp_show'	=> 0,
				);
			}
			
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
					
					$get_discount_type = '-';
					$get_discount_type_var = 'no_promo';
					
					if(empty($s['discount_id'])){
						$s['discount_id'] = 0;
						
						//if(!empty($billing_buyget_discount_id[$s['id']])){
						//	$s['discount_id'] = $billing_buyget_discount_id[$s['id']];
						//}
					}
					
					if(!empty($s['discount_id'])){
						if($s['discount_perbilling'] == 1){
							$get_discount_type = 'BILLING';
							$get_discount_type_var = 'billing';
						}else{
							$get_discount_type = 'ITEM';
							$get_discount_type_var = 'item';
						}
					}
					
					$new_var = $s['discount_id'];
					if(empty($newData[$new_var])){
						$newData[$new_var] = array(
							'discount_id'	=> $s['discount_id'],
							'discount_name'	=> 'NO PROMO',
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
							'total_dp'	=> 0,
							'total_dp_show'	=> 0,
						
						);
						
					}
					
					//ALL
					if(!empty($discount_type) AND in_array($discount_type, array('item','billing'))){
						
						$has_promo = false;
						if($discount_type == 'item'){
							
							if(!empty($billing_buyget_discount_id[$s['id']])){
								$summary_promo_bill_id[$s['id']] = $s;
								$has_promo = true;
							}
							if(!empty($billingpromo_discount_id[$s['id']])){
								$summary_promo_bill_id[$s['id']] = $s;
								$has_promo = true;
							}
						}
						
						if(!empty($s['discount_id']) AND $has_promo == false){
							$all_billing_discount_id[$s['id']] = $new_var;
							$newData[$new_var]['total_qty_billing'] += 1;
							$newData[$new_var]['total_billing'] += $s['total_billing'];
							$newData[$new_var]['discount_total'] += $s['discount_total'];
							$newData[$new_var]['discount_billing_total'] += $s['discount_billing_total'];
							$newData[$new_var]['tax_total'] += $s['tax_total'];
							$newData[$new_var]['service_total'] += $s['service_total'];
							$newData[$new_var]['sub_total'] += $s['sub_total'];
							$newData[$new_var]['total_pembulatan'] += $s['total_pembulatan'];
							$newData[$new_var]['total_compliment'] += $s['total_compliment'];
							$newData[$new_var]['grand_total'] += $s['grand_total'];
							$newData[$new_var]['total_dp'] += $s['total_dp'];
							//array_push($newData, $s);
						}else{
							//echo $s['id'].'';
							//echo '<br/>';
						}
						
					}else{
						
						if(!empty($billing_buyget_discount_id[$s['id']])){
							if(!in_array($discount_type, array('no_promo','billing'))){
								$summary_promo_bill_id[$s['id']] = $s;
							}else{
								//echo $s['id'].'';
								//echo '<br/>';
							}
						}else{
							
							if(!empty($billing_promo_discount_id[$s['id']])){
								if(!in_array($discount_type, array('no_promo','billing'))){
									$summary_promo_bill_id[$s['id']] = $s;
								}else{
									//echo $s['id'].'';
									//echo '<br/>';
								}
							}else{
								
								$all_billing_discount_id[$s['billing_id']] = $new_var;
								$newData[$new_var]['total_qty_billing'] += 1;
								$newData[$new_var]['total_billing'] += $s['total_billing'];
								$newData[$new_var]['discount_total'] += $s['discount_total'];
								$newData[$new_var]['discount_billing_total'] += $s['discount_billing_total'];
								$newData[$new_var]['tax_total'] += $s['tax_total'];
								$newData[$new_var]['service_total'] += $s['service_total'];
								$newData[$new_var]['sub_total'] += $s['sub_total'];
								$newData[$new_var]['total_pembulatan'] += $s['total_pembulatan'];
								$newData[$new_var]['total_compliment'] += $s['total_compliment'];
								$newData[$new_var]['grand_total'] += $s['grand_total'];
								$newData[$new_var]['total_dp'] += $s['total_dp'];
								//array_push($newData, $s);
							}
						}
							
						
					}
				}
			}
			
			//echo '<pre>TOT:'.count($newData);
			//print_r($newData);
			//die();
			
			//calc detail
			$total_hpp = array();
			$discount_item = array();
			$summary_promo_bill_id_done = array();
			
			
			if(!empty($all_bil_id)){
				$all_bil_id_txt = implode(",",$all_bil_id);
				$this->db->select("*");
				$this->db->from($this->table2);
				$this->db->where('billing_id IN ('.$all_bil_id_txt.')');
				$this->db->where('is_deleted', 0);
				$this->db->order_by('id', "ASC");
				$get_detail = $this->db->get();
				if($get_detail->num_rows() > 0){
					foreach($get_detail->result() as $dtRow){
						
						$total_qty = $dtRow->order_qty;
						
						$total_hpp = $dtRow->product_price_hpp * $total_qty;

						//PROMO BUY GET
						if(!empty($summary_promo_bill_id[$dtRow->billing_id])){
							
							if(!in_array($dtRow->billing_id, $summary_promo_bill_id_done)){
								
								$has_discount_on_detail = $dtRow->discount_id;
								
								if(empty($has_discount_on_detail)){
									$has_discount_on_detail = 0;
								}
								
								if($dtRow->is_buyget == 1 AND !empty($dtRow->buyget_id)){
									$has_discount_on_detail = $dtRow->buyget_id;
								}
								if($dtRow->is_promo == 1 AND !empty($dtRow->promo_id)){
									$has_discount_on_detail = $dtRow->promo_id;
								}
								
								if(!empty($billing_buyget_discount_id[$dtRow->billing_id])){
									$has_discount_on_detail = $billing_buyget_discount_id[$dtRow->billing_id];
								}
								if(!empty($billing_promo_discount_id[$dtRow->billing_id])){
									$has_discount_on_detail = $billing_promo_discount_id[$dtRow->billing_id];
								}
								
								if(!empty($has_discount_on_detail)){
									$summary_promo_bill_id_done[] = $dtRow->billing_id;
								}
								
								$dt_bill = $summary_promo_bill_id[$dtRow->billing_id];
								
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
								
								if(!empty($has_discount_on_detail)){
									if(empty($newData[$new_var])){
										$newData[$new_var] = array(
											'discount_id'	=> $has_discount_on_detail,
											'discount_name'	=> '',
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
											'total_dp'	=> 0,
											'total_dp_show'	=> 0,
										
										);
										
									}
									
									$all_billing_discount_id[$dtRow->billing_id] = $new_var;
									$newData[$new_var]['total_qty_billing'] += 1;
									$newData[$new_var]['total_billing'] += $dt_bill['total_billing'];
									$newData[$new_var]['discount_total'] += $dt_bill['discount_total'];
									$newData[$new_var]['discount_billing_total'] += $dt_bill['discount_billing_total'];
									$newData[$new_var]['tax_total'] += $dt_bill['tax_total'];
									$newData[$new_var]['service_total'] += $dt_bill['service_total'];
									$newData[$new_var]['sub_total'] += $dt_bill['sub_total'];
									$newData[$new_var]['total_pembulatan'] += $dt_bill['total_pembulatan'];
									$newData[$new_var]['total_compliment'] += $dt_bill['total_compliment'];
									$newData[$new_var]['grand_total'] += $dt_bill['grand_total'];
									$newData[$new_var]['total_dp'] += $dt_bill['total_dp'];

									//echo '<br/>BILLING #'.$dtRow->billing_id.' -> '.$has_discount_on_detail.' :'.$newData[$new_var]['total_qty_billing'];
								}
						
								if(!empty($newData[$has_discount_on_detail])){
									//if(!in_array($dtRow->billing_id, $summary_promo_bill_id_done)){
										$newData[$has_discount_on_detail]['total_qty'] += $total_qty;
										$newData[$has_discount_on_detail]['total_hpp'] += $total_hpp;
									//}
								}
								
							}
							
						}else{
							
							if(!empty($all_billing_discount_id[$dtRow->billing_id])){
								$get_disc_id = $all_billing_discount_id[$dtRow->billing_id];
								if(!empty($newData[$get_disc_id])){
									$newData[$get_disc_id]['total_qty'] += $total_qty;
									$newData[$get_disc_id]['total_hpp'] += $total_hpp;
								}
							}
						
						}
						
					}
					
					
				}
			}
			
			
			//echo '<pre>TOT:'.count($newData);
			//print_r($newData);
			//die();
			
			if(!empty($all_discount_id)){
				
				$all_discount_id_sql = implode(",", $all_discount_id);
				$this->db->from($this->prefix.'discount');
				$this->db->where('id IN ('.$all_discount_id_sql.')');
				$get_discount = $this->db->get();
				if($get_discount->num_rows() > 0){
					foreach($get_discount->result() as $dtRow){
						$discount_data[$dtRow->id] = $dtRow->discount_name;
					}
				}
				
			}
			
			$data_post['discount_item'] = $discount_item;
			$data_post['discount_data'] = $discount_data;
			
			$newData_switch = $newData;
			$newData = array();
			if(!empty($newData_switch)){
				foreach($newData_switch as $dt){
					
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
					
					if(!empty($dt['grand_total'])){
						$newData[] = $dt;
					}
					
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
		

		$useview = 'print_salesByDiscountRecap';
		$data_post['report_name'] = 'SALES BY DISCOUNT (RECAP)';
		
		if($do == 'excel'){
			$useview = 'excel_salesByDiscountRecap';
		}
		
		$this->load->view('../../billing/views/'.$useview, $data_post);	
	}
	
}