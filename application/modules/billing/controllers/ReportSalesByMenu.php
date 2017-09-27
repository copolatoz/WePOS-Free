<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ReportSalesByMenu extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_databilling', 'm');
		$this->load->model('model_billingdetail', 'm2');
	}
	
	public function print_reportSalesByMenu(){
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';		
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		
		if(empty($date_from)){ $date_from = date("Y-m-d"); }
		if(empty($date_till)){ $date_till = date("Y-m-d"); }
		
		if(empty($sorting)){
			$sorting = 'payment_date';
		}
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'SALES REPORT BY MENU',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'cashier_name'	=> '',
			'user_fullname'	=> $user_fullname,
			'diskon_sebelum_pajak_service'	=> 0
		);
		
		if(empty($groupCat)){
			$groupCat = 0;
		}
		
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
		
		if(empty($date_from) OR empty($date_till)){
			die('Billing Data Not Found!');
		}else{
				
			if(empty($date_from)){ $date_from = date('Y-m-d'); }
			if(empty($date_till)){ $date_till = date('Y-m-d'); }
			
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till = date("Y-m-d",strtotime($date_till));
			$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
			
			$add_where = "(b.payment_date >= '".$qdate_from." 07:00:00' AND b.payment_date <= '".$qdate_till_max." 06:00:00')";
			
			//b.tax_total, b.service_total,
			//b.include_tax, b.tax_percentage, b.include_service, b.service_percentage, b.is_compliment,
			$this->db->select("a.*, b.billing_no, b.total_billing, b.discount_perbilling, b.payment_id,
								b.discount_percentage as billing_discount_percentage, b.discount_total as billing_discount_total,
								b.total_pembulatan as billing_total_pembulatan,
								c.product_name, c.product_group, c.category_id, d.product_category_name as category_name");
			$this->db->from($this->table2." as a");
			$this->db->join($this->prefix.'billing as b','b.id = a.billing_id','LEFT');
			$this->db->join($this->prefix.'product as c','c.id = a.product_id','LEFT');
			$this->db->join($this->prefix.'product_category as d','d.id = c.category_id','LEFT');
			$this->db->where("a.is_deleted", 0);
			$this->db->where("b.is_deleted", 0);
			$this->db->where("b.billing_status", "paid");			
			//$this->db->order_by("d.product_category_name", 'ASC');		
			$this->db->order_by("c.product_name", 'ASC');
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
			
			//echo $this->db->last_query();
			$konversi_pembulatan_billing = array();
			$balancing_discount_billing = array();
			$all_product_data = array();
			$newData = array();
			$no = 1;
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					
					$s['item_no'] = $no;
					
					if(empty($all_product_data[$s['product_id']] )){
						
						$all_product_data[$s['product_id']] = array(
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
							'compliment_total'	=> 0
						);
						
						$no++;
						
					}
					
					$all_product_data[$s['product_id']]['total_qty'] += $s['order_qty'];
					
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
						
						if($data_post['diskon_sebelum_pajak_service'] == 1){
							
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
							
						if($data_post['diskon_sebelum_pajak_service'] == 1){
							
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
			//print_r($all_product_data[93]);
			//echo 'TOTAL = '.count($all_product_data);
			//die();
			
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
										
					$newData[$dt['product_id']] = $dt;
					$no++;
				}
			}
		
			arsort($sort_qty);	
			if(!empty($order_qty)){
				//RANK QTY
				if($order_qty == 1){
					arsort($sort_qty);
					$xnewData = array();
					foreach($sort_qty as $key => $dt){
			
						if(!empty($newData[$key])){
							$xnewData[] = $newData[$key];
						}
							
					}
					$newData = $xnewData;
				}
					
				//RANK PROFIT
				if($order_qty == 2){
					arsort($sort_profit);
					$xnewData = array();
					foreach($sort_profit as $key => $dt){
			
						if(!empty($newData[$key])){
							$xnewData[] = $newData[$key];
						}
							
					}
					$newData = $xnewData;
				}
			}else{
				$order_qty = 0;
				$xnewData = array();
				foreach($newData as $dt){
					$xnewData[] = $dt;
				}
					
				$newData = $xnewData;
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
			


			//GROUPING
			$new_GroupData = array();
			if(!empty($groupCat)){
				foreach($newData as $dt){
					if(empty($new_GroupData[$dt['category_name']])){
						$new_GroupData[$dt['category_name']] = array();
					}
						
					$new_GroupData[$dt['category_name']][] = $dt;
				}
				
				ksort($new_GroupData);
				
				$newData = $new_GroupData;
			}
			
			$data_post['report_data'] = $newData;
			$data_post['payment_data'] = $dt_payment_name;
						
		}
		
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}

		if(empty($useview)){
			$useview = 'print_reportSalesByMenu';
			$data_post['report_name'] = 'SALES REPORT BY MENU';
			
			if($do == 'excel'){
				$useview = 'excel_reportSalesByMenu';
			}
			
		}else{
			$useview = 'print_reportProfitSalesByMenu';
			$data_post['report_name'] = 'SALES PROFIT REPORT MENU';
			
			if($do == 'excel'){
				$useview = 'excel_reportProfitSalesByMenu';
			}
			
		}

		if(!empty($groupCat)){
			
			if($groupCat == 'cat'){
				$useview = 'print_reportSalesByMenuCategory';
				$data_post['report_name'] = 'SALES REPORT BY MENU CATEGORY';
				
				if($do == 'excel'){
					$useview = 'excel_reportSalesByMenuCategory';
				}
				
			}else{
				$useview = 'print_reportProfitSalesByMenuCategory';
				$data_post['report_name'] = 'SALES PROFIT REPORT BY MENU CATEGORY';
				
				if($do == 'excel'){
					$useview = 'excel_reportProfitSalesByMenuCategory';
				}
				
			}
		}
		
		
		$this->load->view('../../billing/views/'.$useview, $data_post);
	}
	
}