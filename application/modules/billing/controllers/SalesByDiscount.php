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
			$billing_buyget_promo = array();
			
			//BUYGET & PROMO di BILLING DETAIL
			$this->db->select('b.billing_id, b.is_buyget, b.buyget_id, b.buyget_qty, b.is_promo, b.promo_id, b.order_qty, b.ref_order_id, b.discount_total');
			$this->db->from($this->table2.' as b');
			$this->db->join($this->table.' as a',"a.id = b.billing_id","LEFT");
			$this->db->where("a.billing_status", 'paid');
			$this->db->where("a.is_deleted", 0);
			$this->db->where("((b.is_buyget = 1 OR b.is_promo = 1) AND b.order_qty > 0 AND b.order_status = 'done')");
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
			$this->db->where("a.billing_status", 'paid');
			$this->db->where("a.is_deleted", 0);
			$this->db->where($add_where);
			
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
			
			$data_post['discount_type'] = '';
			if(!empty($discount_type)){
				$data_post['discount_type'] = $discount_type;
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
			$all_billing_discount_id_buyget = array();
			
			$all_bil_id = array();
			$newData = array();
			$newData_sort_qty = array();
			$newData_buyget = array();
			
			$dt_payment = array();
			$summary_promo_bill_id = array();
			$all_discount_item = array();
			$all_discount_billing = array();
			$all_discount_buyget = array();
			$all_discount_promo = array();
			
			$move_to_buyget = array();
			$billing_data = array();
			
			$discount_data = array();
			$discount_data[0] = 'NO PROMO';
				
			
			//CHECKING BILLING HAS DISCOUNT
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					$s['billing_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['payment_date'] = date("d-m-Y H:i",strtotime($s['payment_date']));
					
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
							
						}
						
					}else{
						
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
					}
					
					$new_var = $s['id'];
					$newData[$new_var] = $s;
					$newData[$new_var]['discount_type'] = $get_discount_type;
					$newData[$new_var]['total_qty'] = 0;
					$newData[$new_var]['total_hpp'] = 0;

					//sort
					$newData_sort_qty[$new_var]['total_qty'] = 0;
					
					//QTY ORDER
					$billing_qty_order = 0;
					
					$all_billing_discount_id[$s['id']] = $new_var;
					$billing_data[$s['id']] = $s;
				}
			}
				
			//BUYGET ---------------------------------------------------
			//newData_buygets
			if(!empty($move_to_buyget)){
				
				foreach($move_to_buyget as $getBillID){

					if(empty($newData_buyget[$getBillID])){

						$addData = array(
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
						
						$newData_buyget[$getBillID] = array_merge($billing_data[$getBillID], $addData);

					}
				}
			}
			//BUYGET ---------------------------------------------------
			
			//echo '<pre>';
			//print_r($newData_buyget);
			//die();
			
			//calc detail
			$total_hpp = array();
			$discount_item = array();
			$summary_promo_bill_id_done = array();
			
			$billing_discount_data = array();
			$billing_id_buyget_detail = array();
			
			$konversi_pembulatan_billing = array();
			$balancing_discount_billing = array();

			$used_buyget_total = array();
			
				
			$get_opt = get_option_value(array('report_place_default','diskon_sebelum_pajak_service','cashier_max_pembulatan',
			'cashier_pembulatan_keatas','pembulatan_dinamis'));
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
			
			if(!empty($all_bil_id)){
				$all_bil_id_txt = implode(",",$all_bil_id);
				$this->db->select("a.*, b.billing_no, b.total_billing, b.discount_perbilling, b.payment_id, 
								b.discount_percentage as billing_discount_percentage, b.discount_total as billing_discount_total,
								b.total_pembulatan as billing_total_pembulatan");
				$this->db->from($this->table2." as a");
				$this->db->join($this->prefix.'billing as b','b.id = a.billing_id','LEFT');
				$this->db->where('b.id IN ('.$all_bil_id_txt.')');
				$this->db->where("a.order_qty > 0");
				$this->db->where("a.is_deleted", 0);
				$this->db->where("b.is_deleted", 0);
				$this->db->where("b.billing_status", "paid");
				$this->db->order_by('a.id', "ASC");
				$get_detail = $this->db->get();
				if($get_detail->num_rows() > 0){
					foreach($get_detail->result_array() as $s){
						
						$total_qty = $s['order_qty'];
						$total_hpp = $s['product_price_hpp'] * $total_qty;
						
						$has_discount_on_detail = 0;
						if($s['is_buyget'] == 1){
							//SKIP MAIN ORDER BUYGET
							$has_discount_on_detail = 0;
						}
						
						//buyget free item
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
						
						//ALL DETAIl CALC --------------------------------------------------
						
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
						$total_billing_order = 0;
						$tax_total_order = 0;
						$service_total_order = 0;
						$compliment_total = 0;
						
						//cek if discount is disc billing
						/*$total_discount_product = 0;
						if($s['discount_perbilling'] == 1){
							$get_percentage = $s['billing_discount_percentage'];
							if(empty($s['billing_discount_percentage'])){
								$get_percentage = ($s['billing_discount_total'] / $s['total_billing']) * 100;
								$get_percentage = number_format($get_percentage,0);
							}
							
							$s['discount_total'] = priceFormat(($s['product_price_real']*($get_percentage/100)), 0, ".", "");
							$total_discount_product = ($s['discount_total']*$s['order_qty']);
						}else{
							$total_discount_product = ($s['discount_total']);
						}*/
						
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
						
						
						if(!empty($include_tax) OR !empty($include_service)){
							
							//AUTOFIX-BUGS 1 Jan 2018
							if((!empty($include_tax) AND empty($include_service)) OR (empty($include_tax) AND !empty($include_service))){
								if($s['product_price'] != ($s['product_price_real']+$s['tax_total']+$s['service_total'])){
									$s['product_price_real'] = priceFormat(($s['product_price']/($all_percentage/100)), 0, ".", "");
								}
							}
							
							if($data_post['diskon_sebelum_pajak_service'] == 1){
								
								$grand_total_order = ($s['product_price_real']*$s['order_qty'])- $s['discount_total'];
								
							}else{
								
								$grand_total_order = ($s['product_price_real']*$s['order_qty']);
							
							}
							
							$total_billing_order = ($s['product_price_real']*$s['order_qty']);
							$tax_total_order = $s['tax_total'];
							$service_total_order = $s['service_total'];
							
						}else
						{
								
							if($data_post['diskon_sebelum_pajak_service'] == 1){
								
								$grand_total_order = ($s['product_price']*$s['order_qty'])- $s['discount_total'];
								
							}else{
								
								$grand_total_order = ($s['product_price']*$s['order_qty']);
							
							}
							
							$total_billing_order = ($s['product_price']*$s['order_qty']);
							$tax_total_order = $s['tax_total'];
							$service_total_order = $s['service_total'];
							
						}
						
						
						//BALANCING TOTAL BILLING
						$total_billing = $grand_total_order + $s['discount_total'];
						$grand_total_order += $s['tax_total'];
						$grand_total_order += $s['service_total'];
						
						//$sub_total = $grand_total_order;
						
						//diskon_sebelum_pajak_service
						if($data_post['diskon_sebelum_pajak_service'] == 0){
							$sub_total = $total_billing + $s['tax_total'] + $s['service_total'];		
						}else{
							$sub_total = $total_billing - $s['discount_total'] + $s['tax_total'] + $s['service_total'];
						}
						
						//OVERRIDE PEMBULATAN PERITEM
						$total_pembulatan = 0;
						
						$grand_total_order += $total_pembulatan;
						
						
						if(!empty($s['is_compliment'])){
							$compliment_total = $grand_total_order;
							$grand_total_order -= $compliment_total;
						}
						//ALL DETAIl CALC --------------------------------------------------
					
					
						
						//UPDATE TOTAL QTY + HPP --------------------------------- all,perbilling,item
						if(!empty($all_billing_discount_id[$s['billing_id']])){
							
							$get_disc_id = $all_billing_discount_id[$s['billing_id']];
							
							if(!empty($newData[$get_disc_id])){
								
								$newData[$get_disc_id]['total_qty'] += $total_qty;
								$newData[$get_disc_id]['total_hpp'] += $total_hpp;
								
								//sort
								$newData_sort_qty[$get_disc_id]['total_qty'] += $total_qty;
							}
							
						}else{
							
							//NO PROMO
							
						}
						//UPDATE TOTAL QTY + HPP --------------------------------- all,perbilling,item
						
						
						//BUYGET ---------------------------------------------------
						if(in_array($s['billing_id'], $move_to_buyget) AND $has_discount_on_detail == 1){
							
							$get_billing_id = $s['billing_id'];
							$get_disc_id = $s['discount_id'];
							
							if($s['is_promo'] == 1){
								$get_disc_id = $s['promo_id'];
							}
							
							//echo $s['billing_id'].' = '.$get_billing_id.' = '.$s['discount_total'].', ref_order_id = '.$s['ref_order_id'].'<br/>';
							
							//$dt_bill = $billing_data[$s['billing_id']];
							if(!empty($newData_buyget[$get_billing_id])){
									
								if(!in_array($get_billing_id, $used_buyget_total)){
									$used_buyget_total[] = $get_billing_id;
									$newData_buyget[$get_billing_id]['discount_id'] = $get_disc_id;
									$newData_buyget[$get_billing_id]['total_qty'] += $total_qty;
									$newData_buyget[$get_billing_id]['total_hpp'] += $total_hpp;
									$newData_buyget[$get_billing_id]['tax_total'] += $s['tax_total'];
									$newData_buyget[$get_billing_id]['service_total'] += $s['service_total'];
									$newData_buyget[$get_billing_id]['discount_total'] += $discount_total;
									$newData_buyget[$get_billing_id]['discount_billing_total'] += $discount_billing_total;
									
									$newData_buyget[$get_billing_id]['total_compliment'] += $compliment_total;
									
									if(empty($billing_id_buyget_detail[$get_billing_id])){
										$billing_id_buyget_detail[$get_billing_id] = array();
									}
									if(!in_array($s['billing_id'], $billing_id_buyget_detail[$get_billing_id])){
										$billing_id_buyget_detail[$get_billing_id][] = $s['billing_id'];
										$newData_buyget[$get_billing_id]['total_qty_billing'] += 1;
									}
									
									$newData_buyget[$get_billing_id]['total_billing'] += $total_billing;
									$newData_buyget[$get_billing_id]['sub_total'] += $sub_total;
									$newData_buyget[$get_billing_id]['total_pembulatan'] += $total_pembulatan;
									$newData_buyget[$get_billing_id]['grand_total'] += $grand_total_order;
									
								}
							}
								
						}
						//BUYGET ---------------------------------------------------
						
						
					}
					
					
				}
			}
			
			//echo 'all detail = '.$get_detail->num_rows().'<br/>';
			//echo 'buyget = '.count($newData_buyget).'<pre>TOT:';
			//print_r($newData_buyget);
			//die();
			
			//echo 'billing_id_buyget_detail = '.count($billing_id_buyget_detail).'<pre>TOT:';
			//print_r(implode(",",$billing_id_buyget_detail[48]));
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
					
					//if(!empty($dt['grand_total'])){
						$newData[] = $dt;
					//}
					
				}
			}
			
			//newData_buyget
			$newData_switch = $newData_buyget;
			$newData_buyget = array();
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
					
					//if(!empty($dt['grand_total'])){
						$newData_buyget[] = $dt;
					//}
					
				}
			}
			
			$data_post['report_data'] = $newData;
			$data_post['buyget_data'] = $newData_buyget;
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
			$billing_buyget_promo = array();
			
			//BUYGET & PROMO di BILLING DETAIL
			$this->db->select('b.billing_id, b.is_buyget, b.buyget_id, b.buyget_qty, b.is_promo, b.promo_id, b.order_qty, b.ref_order_id, b.discount_total');
			$this->db->from($this->table2.' as b');
			$this->db->join($this->table.' as a',"a.id = b.billing_id","LEFT");
			$this->db->where("a.billing_status", 'paid');
			$this->db->where("a.is_deleted", 0);
			$this->db->where("((b.is_buyget = 1 OR b.is_promo = 1) AND b.order_qty > 0 AND b.order_status != 'done')");
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
			
			//echo count($billing_promo_discount_id).'<pre>';
			//print_r($billing_promo_discount_id);
			//die();
			
			//CHECKING BILLING
			$this->db->select("a.*, a.id as billing_id, a.updated as billing_date, d.payment_type_name, e.bank_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT');
			$this->db->join($this->prefix.'bank as e','e.id = a.bank_id','LEFT');
			$this->db->where("a.billing_status", 'paid');
			$this->db->where("a.is_deleted", 0);
			$this->db->where($add_where);
			
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
			
			if(empty($sorting)){
				$this->db->order_by("payment_date","ASC");
			}else{
				$this->db->order_by($sorting,"ASC");
			}
			
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
			
			$data_post['discount_type'] = '';
			if(!empty($discount_type)){
				$data_post['discount_type'] = $discount_type;
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
			$all_billing_discount_id_buyget = array();
			
			$all_bil_id = array();
			$newData = array();
			$newData_buyget = array();
			
			$dt_payment = array();
			$summary_promo_bill_id = array();
			$all_discount_item = array();
			$all_discount_billing = array();
			$all_discount_buyget = array();
			$all_discount_promo = array();
			
			$move_to_buyget = array();
			$billing_data = array();
			
			$discount_data = array();
			$discount_data[0] = 'NO PROMO';

			$newData_sort_qty = array();
			$newData_sort_qty[0] = 0;
			$newData_buyget_sort_qty = array();
			$newData_buyget_sort_qty[0] = 0;
				
			//NOPROMO DEFAULT -----------------------
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
			//NOPROMO DEFAULT -----------------------
			
			//CHECKING BILLING HAS DISCOUNT
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					$s['billing_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['payment_date'] = date("d-m-Y H:i",strtotime($s['payment_date']));
					
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
							
						}
						
					}else{
						
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
					}
					
					$new_var = $s['discount_id'];
					if(empty($newData[$new_var])){
						$newData[$new_var] = array(
							'discount_id'	=> $new_var,
							'discount_name'	=> '-',
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
						$newData_sort_qty[$new_var] = 0;
					}
					
					//QTY ORDER
					$billing_qty_order = 0;
					
					$all_billing_discount_id[$s['id']] = $new_var;
					
					/*
					$newData[$new_var]['total_qty_billing'] += 1;
					$newData[$new_var]['total_qty'] += $billing_qty_order;
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
					*/
					//$newData[$new_var]['discount_type'] = $discount_type;
					//array_push($newData, $s);
					
					$billing_data[$s['id']] = $s;
				}
			}
				
			//BUYGET ---------------------------------------------------
			//newData_buygets
			if(!empty($all_discount_buyget)){
				
				foreach($all_discount_buyget as $getDiscID){
					
					if(empty($newData_buyget[$getDiscID])){
						$newData_buyget[$getDiscID] = array(
							'discount_id'	=> $getDiscID,
							'discount_name'	=> '-',
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
						$newData_buyget_sort_qty[$getDiscID] = 0;
					}
				}
			}
			//BUYGET ---------------------------------------------------
			
			//print_r($newData_buyget);
			//die();
			
			//calc detail
			$total_hpp = array();
			$discount_item = array();
			$summary_promo_bill_id_done = array();
			
			$billing_discount_data = array();
			$billing_id_buyget_detail = array();
			
			$konversi_pembulatan_billing = array();
			$balancing_discount_billing = array();

			$used_billing_total = array();
			
				
			$get_opt = get_option_value(array('report_place_default','diskon_sebelum_pajak_service','cashier_max_pembulatan',
			'cashier_pembulatan_keatas','pembulatan_dinamis'));
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
			
			if(!empty($all_bil_id)){
				$all_bil_id_txt = implode(",",$all_bil_id);
				$this->db->select("a.*, b.billing_no, b.total_billing, b.discount_perbilling, b.payment_id, 
								b.discount_percentage as billing_discount_percentage, b.discount_total as billing_discount_total,
								b.total_pembulatan as billing_total_pembulatan");
				$this->db->from($this->table2." as a");
				$this->db->join($this->prefix.'billing as b','b.id = a.billing_id','LEFT');
				$this->db->where('b.id IN ('.$all_bil_id_txt.')');
				$this->db->where("a.order_qty > 0");
				$this->db->where("a.is_deleted", 0);
				$this->db->where("b.is_deleted", 0);
				$this->db->where("b.billing_status", "paid");
				$this->db->order_by('a.id', "ASC");
				$get_detail = $this->db->get();
				if($get_detail->num_rows() > 0){
					foreach($get_detail->result_array() as $s){
						
						$total_qty = $s['order_qty'];
						$total_hpp = $s['product_price_hpp'] * $total_qty;
						
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
						
						//ALL DETAIl CALC --------------------------------------------------
						
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
						$total_billing_order = 0;
						$tax_total_order = 0;
						$service_total_order = 0;
						$compliment_total = 0;
						
						//cek if discount is disc billing
						/*$total_discount_product = 0;
						if($s['discount_perbilling'] == 1){
							$get_percentage = $s['billing_discount_percentage'];
							if(empty($s['billing_discount_percentage'])){
								$get_percentage = ($s['billing_discount_total'] / $s['total_billing']) * 100;
								$get_percentage = number_format($get_percentage,0);
							}
							
							$s['discount_total'] = priceFormat(($s['product_price_real']*($get_percentage/100)), 0, ".", "");
							$total_discount_product = ($s['discount_total']*$s['order_qty']);
						}else{
							$total_discount_product = ($s['discount_total']);
						}*/
						
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
						
						
						if(!empty($include_tax) OR !empty($include_service)){
							
							//AUTOFIX-BUGS 1 Jan 2018
							if((!empty($include_tax) AND empty($include_service)) OR (empty($include_tax) AND !empty($include_service))){
								if($s['product_price'] != ($s['product_price_real']+$s['tax_total']+$s['service_total'])){
									$s['product_price_real'] = priceFormat(($s['product_price']/($all_percentage/100)), 0, ".", "");
								}
							}
							
							if($data_post['diskon_sebelum_pajak_service'] == 1){
								
								$grand_total_order = ($s['product_price_real']*$s['order_qty'])- $s['discount_total'];
								
							}else{
								
								$grand_total_order = ($s['product_price_real']*$s['order_qty']);
							
							}
							
							$total_billing_order = ($s['product_price_real']*$s['order_qty']);
							$tax_total_order = $s['tax_total'];
							$service_total_order = $s['service_total'];
							
						}else
						{
								
							if($data_post['diskon_sebelum_pajak_service'] == 1){
								
								$grand_total_order = ($s['product_price']*$s['order_qty'])- $s['discount_total'];
								
							}else{
								
								$grand_total_order = ($s['product_price']*$s['order_qty']);
							
							}
							
							$total_billing_order = ($s['product_price']*$s['order_qty']);
							$tax_total_order = $s['tax_total'];
							$service_total_order = $s['service_total'];
							
						}
						
						
						//BALANCING TOTAL BILLING
						$total_billing = $grand_total_order + $s['discount_total'];
						$grand_total_order += $s['tax_total'];
						$grand_total_order += $s['service_total'];
						
						//$sub_total = $grand_total_order;
						
						//diskon_sebelum_pajak_service
						if($data_post['diskon_sebelum_pajak_service'] == 0){
							$sub_total = $total_billing + $s['tax_total'] + $s['service_total'];		
						}else{
							$sub_total = $total_billing - $s['discount_total'] + $s['tax_total'] + $s['service_total'];
						}
						
						//OVERRIDE PEMBULATAN PERITEM
						$total_pembulatan = 0;
						
						$grand_total_order += $total_pembulatan;
						
						
						if(!empty($s['is_compliment'])){
							$compliment_total = $grand_total_order;
							$grand_total_order -= $compliment_total;
						}
						//ALL DETAIL CALC --------------------------------------------------
					
					
						
						//UPDATE TOTAL QTY + HPP --------------------------------- all,perbilling,item
						if(!empty($all_billing_discount_id[$s['billing_id']])){
							
							$get_disc_id = $all_billing_discount_id[$s['billing_id']];
							
							if(!empty($newData[$get_disc_id])){
								
								$newData[$get_disc_id]['total_qty'] += $total_qty;
								$newData[$get_disc_id]['total_hpp'] += $total_hpp;

								//sort
								$newData_sort_qty[$get_disc_id] += $total_qty;
								
							}
							
						}else{
							
							//NO PROMO
							$newData[0]['total_qty'] += $total_qty;
							$newData[0]['total_hpp'] += $total_hpp;

							//sort
							$newData_sort_qty[0] += $total_qty;
							
						}
						//UPDATE TOTAL QTY + HPP --------------------------------- all,perbilling,item

						//UPDATE DETAIL TO MAIN DATA ----------------------------
						if(!empty($total_qty)){
							if(!in_array($s['billing_id'], $used_billing_total)){
								$used_billing_total[] = $s['billing_id'];
								if(!empty($billing_data[$s['billing_id']])){

									$dtbill = $billing_data[$s['billing_id']];
									
									$new_var = 0;
									if(!empty($all_billing_discount_id[$s['billing_id']])){
										$new_var = $all_billing_discount_id[$s['billing_id']];
									}

									//add calc
									$newData[$new_var]['total_qty_billing'] += 1;
									$newData[$new_var]['total_billing'] += $dtbill['total_billing'];
									$newData[$new_var]['discount_total'] += $dtbill['discount_total'];
									$newData[$new_var]['discount_billing_total'] += $dtbill['discount_billing_total'];
									$newData[$new_var]['tax_total'] += $dtbill['tax_total'];
									$newData[$new_var]['service_total'] += $dtbill['service_total'];
									$newData[$new_var]['sub_total'] += $dtbill['sub_total'];
									$newData[$new_var]['total_pembulatan'] += $dtbill['total_pembulatan'];
									$newData[$new_var]['total_compliment'] += $dtbill['total_compliment'];
									$newData[$new_var]['grand_total'] += $dtbill['grand_total'];
									$newData[$new_var]['total_dp'] += $dtbill['total_dp'];
									
									
								}
							}
						}
						//UPDATE DETAIL TO MAIN DATA ----------------------------
						
						/*$in_array_bil = in_array($s['billing_id'], $move_to_buyget);
						if($in_array_bil == 1 AND $has_discount_on_detail == 1){
							if(empty($no_bg)){
								$no_bg = 0;
							}
							$no_bg++;
							echo $no_bg.'. '.$s['id'].','.$s['billing_id'].', is_promo='.$s['promo_id'].', is_buyget='.$s['buyget_id'].',in='.$in_array_bil.', discid = '.$new_var.', has_discount_on_detail='.$has_discount_on_detail.'<br/>';
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
							if(!empty($newData_buyget[$get_disc_id])){
									
								$newData_buyget[$get_disc_id]['total_qty'] += $total_qty;
								$newData_buyget[$get_disc_id]['total_hpp'] += $total_hpp;
								$newData_buyget[$get_disc_id]['tax_total'] += $s['tax_total'];
								$newData_buyget[$get_disc_id]['service_total'] += $s['service_total'];
								$newData_buyget[$get_disc_id]['discount_total'] += $discount_total;
								$newData_buyget[$get_disc_id]['discount_billing_total'] += $discount_billing_total;
								
								$newData_buyget[$get_disc_id]['total_compliment'] += $compliment_total;
								
								if(empty($billing_id_buyget_detail[$get_disc_id])){
									$billing_id_buyget_detail[$get_disc_id] = array();
								}
								if(!in_array($s['billing_id'], $billing_id_buyget_detail[$get_disc_id])){
									$billing_id_buyget_detail[$get_disc_id][] = $s['billing_id'];
									$newData_buyget[$get_disc_id]['total_qty_billing'] += 1;
								}
								
								$newData_buyget[$get_disc_id]['total_billing'] += $total_billing;
								$newData_buyget[$get_disc_id]['sub_total'] += $sub_total;
								$newData_buyget[$get_disc_id]['total_pembulatan'] += $total_pembulatan;
								$newData_buyget[$get_disc_id]['grand_total'] += $grand_total_order;
								
								$newData_buyget_sort_qty[$get_disc_id] += $total_qty;
							}
								
						}
						//BUYGET ---------------------------------------------------
						
						
					}
					
					
				}
			}
			
			//echo 'all detail = '.$get_detail->num_rows().'<br/>';
			//echo 'buyget = '.count($newData_buyget).'<pre>TOT:';
			//print_r($newData_buyget);
			//die();
			
			//echo 'billing_id_buyget_detail = '.count($billing_id_buyget_detail).'<pre>TOT:';
			//print_r(implode(",",$billing_id_buyget_detail[48]));
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

				//sort
				arsort($newData_sort_qty);
				if(!empty($newData_sort_qty)){
					foreach($newData_sort_qty as $disc_id => $qty){
						if(!empty($newData_switch[$disc_id])){
							$dt = $newData_switch[$disc_id];
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
							
							//if(!empty($dt['grand_total'])){
								$newData[] = $dt;
							//}
						}
					}
				}

				/*foreach($newData_switch as $dt){
					
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
					
					//if(!empty($dt['grand_total'])){
						$newData[] = $dt;
					//}
					
				}*/
			}
			
			//newData_buyget
			$newData_switch = $newData_buyget;
			$newData_buyget = array();
			if(!empty($newData_switch)){

				if(!empty($newData_buyget_sort_qty)){
					//sort
					arsort($newData_buyget_sort_qty);
					foreach($newData_buyget_sort_qty as $disc_id => $qty){
						if(!empty($newData_switch[$disc_id])){
							$dt = $newData_switch[$disc_id];

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
							
							//if(!empty($dt['grand_total'])){
								$newData_buyget[] = $dt;
							//}
						}
					}
				}

				/*
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
					
					//if(!empty($dt['grand_total'])){
						$newData_buyget[] = $dt;
					//}
					
				}
				*/
			}
	
			$data_post['report_data'] = $newData;
			$data_post['buyget_data'] = $newData_buyget;
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