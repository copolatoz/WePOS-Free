<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ReportSalesByMenu extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->prefix_apps = config_item('db_prefix');
		$this->load->model('model_databilling', 'm');
		$this->load->model('model_billingdetail', 'm2');
	}
	
	public function print_reportSalesByMenu(){
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';		
		$this->table_varian = $this->prefix.'varian';		
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		
		if(empty($date_from)){ $date_from = date("Y-m-d"); }
		if(empty($date_till)){ $date_till = date("Y-m-d"); }
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'SALES REPORT BY MENU',
			'tipe_sales'	=> 'Semua Tipe Sales',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'user_shift'	=> 'Semua Shift',
			'cashier_name'	=> '',
			'user_fullname'	=> $user_fullname,
			'diskon_sebelum_pajak_service'	=> 0,
			'display_discount_type'	=> array(),
			'filter_column'	=> array(),
			'user_kasir'	=> ''
		);
		
		$display_discount_type = array();

		if(empty($groupCat)){
			$groupCat = 0;
		}
		
		//update-0120.001
		if(empty($sorting)){
			$sorting = 'a-z';
		}
		if(empty($sortingDesc)){
			$sortingDesc = 'ASC';
		}
		if(!empty($shift_billing)){
			if($shift_billing == 'null'){
				$shift_billing = 0;
			}
		}
		if(!empty($kasir_billing)){
			if($kasir_billing == 'null'){
				$kasir_billing = '';
			}
		}
		
		if(empty($tipe_sales)){
			$tipe_sales = 'all_sales';
		}
		
		//filter-column
		$show_payment = json_decode($show_payment);
		$show_compliment = json_decode($show_compliment);
		$show_tax = json_decode($show_tax);
		$show_service = json_decode($show_service);
		$format_nominal = json_decode($format_nominal);
		
		$data_post['filter_column'] = array(
			'show_payment' => $show_payment,
			'show_compliment' => $show_compliment,
			'show_tax' => $show_tax,
			'show_service' => $show_service,
			'format_nominal' => $format_nominal
		);

		$get_opt = get_option_value(array('report_place_default','diskon_sebelum_pajak_service','cashier_max_pembulatan',
		'cashier_pembulatan_keatas','pembulatan_dinamis','role_id_kasir','maxday_cashier_report',
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
						
			$ret_dt = check_maxview_cashierReport($get_opt, $mktime_dari, $mktime_sampai);
			
			//$qdate_from = date("Y-m-d",strtotime($date_from));
			//$qdate_till = date("Y-m-d",strtotime($date_till));
			//$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
			//$add_where = "(b.payment_date >= '".$qdate_from." 07:00:00' AND b.payment_date <= '".$qdate_till_max." 06:00:00')";
			
			//laporan = jam_operasional
			$qdate_from = $ret_dt['qdate_from'];
			$qdate_till = $ret_dt['qdate_till'];
			$qdate_till_max = $ret_dt['qdate_till_max'];
			
			//update-0120.001
			$where_shift_billing = "(b.payment_date >= '".$qdate_from."' AND b.payment_date <= '".$qdate_till_max."')";
				
			//update-0120.001
			if(!empty($shift_billing)){
				$where_shift_billing .= " AND b.shift = ".$shift_billing;
				$data_post['user_shift'] = '';
			}
			if(!empty($kasir_billing)){
				$where_shift_billing .= " AND b.updatedby = '".$kasir_billing."'";
				$data_post['user_kasir'] = '';
			}
			
			//b.tax_total, b.service_total,
			//b.include_tax, b.tax_percentage, b.include_service, b.service_percentage, b.is_compliment,
			$this->db->select("a.*, b.billing_no, b.total_billing, b.grand_total, b.discount_perbilling, b.payment_id, 
								b.is_half_payment, b.total_cash, b.total_credit, b.total_dp,
								b.discount_percentage as billing_discount_percentage, b.discount_total as billing_discount_total,
								b.total_pembulatan as billing_total_pembulatan, b.diskon_sebelum_pajak_service,
								c.product_code, c.product_name, c.product_group, c.category_id, 
								d.product_category_code as category_code, d.product_category_name as category_name, 
								g.nama_shift, CONCAT(h.user_firstname,' ',h.user_lastname) as nama_kasir");
			$this->db->from($this->table2." as a");
			$this->db->join($this->prefix.'billing as b','b.id = a.billing_id','LEFT');
			$this->db->join($this->prefix.'product as c','c.id = a.product_id','LEFT');
			$this->db->join($this->prefix.'product_category as d','d.id = c.category_id','LEFT');
			$this->db->join($this->prefix.'shift as g','g.id = b.shift','LEFT');
			$this->db->join($this->prefix_apps.'users as h','h.user_username = b.updatedby','LEFT');
			$this->db->where("(a.order_status != 'cancel' AND a.order_qty > 0)");	
			$this->db->where("a.is_deleted", 0);
			$this->db->where("b.is_deleted", 0);
			$this->db->where("b.billing_status", "paid");			
			//$this->db->order_by("d.product_category_name", 'ASC');		
			
			//update-0120.001
			$this->db->where($where_shift_billing);
			//$this->db->where("b.billing_no = '2002040006'");
			
			$order_qty = 0;
			$order_code = 0;
			/*if($sorting == 'code'){
				$this->db->order_by("c.product_code", 'ASC');
				$order_code = 1;
			}else
			if($sorting == 'qty'){
				$this->db->order_by("c.product_name", 'ASC');
				$order_qty = 1;
			}else{
				$this->db->order_by("c.product_name", 'ASC');
			}*/
			$this->db->order_by("a.id", 'ASC');
			$this->db->order_by("a.order_qty", 'DESC');
			$this->db->order_by("a.product_price", 'DESC');
			
			//update-2001.002
			if(!empty($tipe_sales)){
				switch($tipe_sales){
					case 'sales_no_discount': 
						$this->db->where("(a.discount_id IS NULL OR a.discount_id = 0)");
						$data_post['tipe_sales'] = 'Tanpa Discount/Potongan';
						break;
					
					case 'sales_only_discount': 
						$this->db->where("(a.discount_id > 0)");
						$data_post['tipe_sales'] = 'Discount/Potongan';
						break;
					
					case 'sales_no_compliment': 
						$this->db->where("(a.is_compliment = 0)");
						$data_post['tipe_sales'] = 'Tanpa Compliment';
						break;
						
					case 'sales_only_compliment': 
						$this->db->where("(a.is_compliment = 1)");
						$data_post['tipe_sales'] = 'Compliment';
						break;
					
					case 'sales_no_customer': 
						$this->db->where("(a.customer_id = 0)");
						$data_post['tipe_sales'] = 'Tanpa Customer/Member';
						break;
					
					case 'sales_only_customer': 
						$this->db->where("(a.customer_id > 0)");
						$data_post['tipe_sales'] = 'Customer/Member';
						break;
					
					case 'sales_no_marketing': 
						$this->db->where("(b.sales_id = 0)");
						$data_post['tipe_sales'] = 'Tanpa Marketing/Sales-Fee';
						break;
					
					case 'sales_only_marketing': 
						$this->db->where("(b.sales_id > 0)");
						$data_post['tipe_sales'] = 'Marketing/Sales-Fee';
						break;
					
					default: 
						//nothing	
						break;
					
				}
				
			}
			
			if(!empty($tipe_laporan)){
				if($tipe_laporan == 'varian'){
					$order_qty = 3;
					
					if(!empty($useview)){
						$order_qty = 4;
					}
				}
				if($tipe_laporan == 'package'){
					$order_qty = 5;
					if(!empty($useview)){
						$order_qty = 6;
					}
				}
				if($tipe_laporan == 'tax_service'){
					$order_qty = 7;
					if(!empty($useview)){
						$order_qty = 8;
					}
				}
				if($tipe_laporan == 'menu_hpp'){
					$order_qty = 9;
					if(!empty($useview)){
						$order_qty = 10;
					}
				}
			}
			
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();
				
			}
			
			//echo $this->db->last_query();
			//echo '<br/>total item = '.$get_dt->num_rows().'<br/>';
			$all_qty_billing = array();
			$all_qty_item = 0;

			$data_diskon_awal = array();
			$konversi_pembulatan_billing = array();
			$balancing_discount_billing = array();
			$balancing_payment_billing = array();
			$all_product_data = array();
			$newData = array();
			$no = 1;
			
			$billing_detail_data = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					if(!empty($billing_detail_data[$s['billing_id']])){
						$billing_detail_data[$s['billing_id']] = array();
					}
					$billing_detail_data[$s['billing_id']][] = $s['product_id'];
				}
			}
			
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					
					//update-0120.001
					if(!empty($shift_billing) AND empty($data_post['user_shift'])){
						if(!empty($s['nama_shift'])){
							$data_post['user_shift'] = $s['nama_shift'];
						}
					}
					if(!empty($kasir_billing) AND empty($data_post['user_kasir'])){
						if(!empty($s['nama_kasir'])){
							$data_post['user_kasir'] = $s['nama_kasir'];
						}
					}
					
					if(empty($display_discount_type[$s['diskon_sebelum_pajak_service']])){
						$display_discount_type[$s['diskon_sebelum_pajak_service']] = array();
					}
					if(!in_array($s['billing_id'], $display_discount_type[$s['diskon_sebelum_pajak_service']])){
						$display_discount_type[$s['diskon_sebelum_pajak_service']][] = $s['billing_id'];
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
					
						$s['item_no'] = $no;
						
						$keyID = $s['product_id'];
						
						if(!empty($order_qty)){
							if($order_qty == 3 OR $order_qty == 4){
								$keyID = $s['product_id']."_".$s['varian_id'];
							}
						}
						
						if(empty($all_product_data[$keyID])){
							
							$all_product_data[$keyID] = array(
								'product_id'	=> $s['product_id'],
								'product_name'	=> $s['product_name'],
								'product_code'	=> $s['product_code'],
								'product_group'	=> $s['product_group'],
								'product_type'	=> $s['product_type'],
								'category_id'	=> $s['category_id'],
								'category_name'	=> $s['category_name'],
								'category_code'	=> $s['category_code'],
								'varian_id'		=> $s['varian_id'],
								'product_price'		=> $s['product_price'],
								'product_price_hpp'	=> $s['product_price_hpp'],
								'varian_name'	=> '',
								'total_qty'	=> 0,
								'total_billing'	=> 0,
								'total_billing_show'	=> 0,
								'sub_total'	=> 0,
								'sub_total_show'	=> 0,
								'net_sales_total'	=> 0,
								'net_sales_total_show'	=> 0,
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
								'all_discount_total'	=> 0,
								'all_discount_total_show'	=> 0,
								'total_hpp'	=> 0,
								'total_hpp_show'	=> 0,
								'total_profit'	=> 0,
								'total_profit_show'	=> 0,
								'is_takeaway'	=> 0,
								'is_compliment'	=> 0,
								'compliment_total'	=> 0,
								'discount_total_before'	=> 0,
								'discount_total_before_show'	=> 0,
								'discount_billing_total_before'	=> 0,
								'discount_billing_total_before_show'	=> 0,
								'discount_total_after'	=> 0,
								'discount_total_after_show'	=> 0,
								'discount_billing_total_after'	=> 0,
								'discount_billing_total_after_show'	=> 0,
							);
							
							$no++;
							
						}
						
						$all_product_data[$keyID]['total_qty'] += $s['order_qty'];
						
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
						$sub_total = 0;
						$net_sales_total = 0;
						$is_balanced = false;

						if(!empty($include_tax) OR !empty($include_service)){
							
							//AUTOFIX-BUGS 1 Jan 2018
							if((!empty($include_tax) AND empty($include_service)) OR (empty($include_tax) AND !empty($include_service))){
								if($s['product_price'] != ($s['product_price_real']+$s['tax_total']+$s['service_total'])){
									$s['product_price_real'] = priceFormat(($s['product_price']/($all_percentage/100)), 0, ".", "");
								}
							}
							
							//update-2001.002
							if(!empty($s['is_compliment'])){
								//$s['product_price_real'] = $s['product_price'];
								$s['tax_total'] = 0;
								$s['service_total'] = 0;
							}
							
							$total_billing_order = ($s['product_price_real']*$s['order_qty']);
							$tax_total_order = $s['tax_total'];
							$service_total_order = $s['service_total'];
							
							if($s['diskon_sebelum_pajak_service'] == 1){
								
								//$all_product_data[$keyID]['grand_total'] += ($s['product_price_real']*$s['order_qty']) - $s['discount_total'];
								//$grand_total_order = ($s['product_price_real']*$s['order_qty']) - $s['discount_total'];
								
								$sub_total = ($s['product_price_real']*$s['order_qty']);
								$sub_total += $s['tax_total'];
								$sub_total += $s['service_total'];
								
								$grand_total_order = $sub_total;
								$sub_total -= $s['discount_total'];
								
							}else{
								
								//$all_product_data[$keyID]['grand_total'] += ($s['product_price_real']*$s['order_qty']);
								//$grand_total_order = ($s['product_price_real']*$s['order_qty']);
								
								$sub_total = ($s['product_price_real']*$s['order_qty']);
								$sub_total += $s['tax_total'];
								$sub_total += $s['service_total'];
								
								$grand_total_order = $sub_total;
								//$grand_total_order -= $s['discount_total'];
								$is_balanced = true;
							}
							
							//update-2001.002
							$net_sales_total = $total_billing_order - $s['discount_total'];
						
							//$all_product_data[$keyID]['total_billing'] += ($s['product_price_real']*$s['order_qty']);
							//$all_product_data[$keyID]['tax_total'] += $s['tax_total'];
							//$all_product_data[$keyID]['service_total'] += $s['service_total'];
							
						}else
						{
								
							$total_billing_order = ($s['product_price']*$s['order_qty']);
							$tax_total_order = $s['tax_total'];
							$service_total_order = $s['service_total'];
							
							if($s['diskon_sebelum_pajak_service'] == 1){
								
								//$all_product_data[$keyID]['grand_total'] += ($s['product_price']*$s['order_qty']) - $s['discount_total'];
								//$grand_total_order = ($s['product_price']*$s['order_qty']) - $s['discount_total'];
								$sub_total = ($s['product_price']*$s['order_qty']);
								$sub_total += $s['tax_total'];
								$sub_total += $s['service_total'];
								
								$grand_total_order = $sub_total;
								$sub_total -= $s['discount_total'];
								
								//update-2001.002
								//$net_sales_total = $total_billing_order - $s['discount_total'];
							
							}else{
								
								//after tax
								//$all_product_data[$keyID]['grand_total'] += ($s['product_price']*$s['order_qty']);
								//$grand_total_order = ($s['product_price']*$s['order_qty']);
								$sub_total = ($s['product_price']*$s['order_qty']);
								$sub_total += $s['tax_total'];
								$sub_total += $s['service_total'];
								
								$grand_total_order = $sub_total;
								$sub_total -= $s['discount_total'];
								
								//update-2001.002
								//$net_sales_total = $total_billing_order - $s['discount_total'];
							
							}
							
							//update-2001.002
							$net_sales_total = $total_billing_order - $s['discount_total'];
						
							//$all_product_data[$keyID]['total_billing'] += ($s['product_price']*$s['order_qty']);
							//$all_product_data[$keyID]['tax_total'] += $s['tax_total'];
							//$all_product_data[$keyID]['service_total'] += $s['service_total'];
							
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
								$get_percentage = ($sub_total_detail / $s['grand_total']) * 100;
								$get_percentage = number_format($get_percentage,2,'.','');
								$s['discount_total'] = priceFormat(($s['billing_discount_total']*($get_percentage/100)), 0, ".", "");
							}
							
							$all_product_data[$keyID]['discount_billing_total'] += $s['discount_total'];
							$total_discount_product = $s['discount_total'];
							//echo '1. total_billing_order = '.$total_billing_order.',get_percentage = '.$get_percentage.',total_discount_product = '.$total_discount_product.'<br/>';
							$data_diskon_awal[$s['product_id']]['billing'] += $total_discount_product;
							
							if($s['diskon_sebelum_pajak_service'] == 1){
								$data_diskon_awal[$s['product_id']]['billing_before'] += $total_discount_product;
								$all_product_data[$keyID]['discount_billing_total_before'] += $s['discount_total'];
							}else{
								$data_diskon_awal[$s['product_id']]['billing_after'] += $total_discount_product;
								$all_product_data[$keyID]['discount_billing_total_after'] += $s['discount_total'];
							}

						}else{
							$all_product_data[$keyID]['discount_total'] += $s['discount_total'];
							$total_discount_product = $s['discount_total'];
							//echo '2. total_discount_product = '.$total_discount_product.'<br/>';
							$data_diskon_awal[$s['product_id']]['item'] += $total_discount_product;
							
							if($s['diskon_sebelum_pajak_service'] == 1){
								$data_diskon_awal[$s['product_id']]['item_before'] += $total_discount_product;
								$all_product_data[$keyID]['discount_total_before'] += $s['discount_total'];
							}else{
								$data_diskon_awal[$s['product_id']]['item_after'] += $total_discount_product;
								$all_product_data[$keyID]['discount_total_after'] += $s['discount_total'];
							}
						}
						
						//BALANCING TOTAL BILLING
						if($s['free_item'] == 1){
							if(!empty($include_tax) OR !empty($include_service)){
								$total_billing_order = ($s['product_price_real']*$s['order_qty']); 
							}else{
								$total_billing_order = ($s['product_price']*$s['order_qty']); 
							}
							$grand_total_order = $s['discount_total'];
							$total_billing = $grand_total_order;
						}else{
							$total_billing = $total_billing_order;
						}

						//echo '$total_billing_order = '.$total_billing_order.'<br/>';
						//echo '$tax_total_order = '.$tax_total_order.'<br/>';
						//echo '$service_total_order = '.$service_total_order.'<br/>';
						
						$all_product_data[$keyID]['total_hpp'] += ($s['product_price_hpp']*$s['order_qty']);
						$all_product_data[$keyID]['total_billing'] += $total_billing_order;
						$all_product_data[$keyID]['tax_total'] += $tax_total_order;
						$all_product_data[$keyID]['service_total'] += $service_total_order;
						
						//$all_product_data[$keyID]['grand_total'] += $s['tax_total'];
						//$all_product_data[$keyID]['grand_total'] += $s['service_total'];
						
						$skip_balancing = false;
						
						//COMPLIMENT
						$compliment_total = 0;
						if(!empty($s['is_compliment'])){
							
							$compliment_total = $grand_total_order;
							//$grand_total_order -= $compliment_total;
							$all_product_data[$keyID]['compliment_total'] += $compliment_total;
							//$all_product_data[$keyID]['grand_total'] -= $compliment_total;
							$all_product_data[$keyID]['is_compliment'] = 1;
							
							$s['service_total'] = 0;
							$s['tax_total'] = 0;
							$sub_total = 0;
							$net_sales_total = 0;
							$grand_total_order = 0;
							$skip_balancing = true;
							
						}
						
						
						$all_product_data[$keyID]['sub_total'] += $sub_total;
						$all_product_data[$keyID]['net_sales_total'] += $net_sales_total;
						$all_product_data[$keyID]['grand_total'] += $grand_total_order;
						
						//OVERRIDE PEMBULATAN PERITEM
						$total_pembulatan = 0;
						
						$all_product_data[$keyID]['total_pembulatan'] += $total_pembulatan;
						$all_product_data[$keyID]['grand_total'] += $total_pembulatan;
						
						$grand_total_order += $total_pembulatan;
						//echo '$total_discount_product = '.$total_discount_product.'<br/>';
						//echo '$is_compliment = '.$s['is_compliment'].'<br/>';
						//echo '$total_pembulatan = '.$total_pembulatan.'<br/>';
						//echo '$grand_total_order = '.$grand_total_order.'<br/>';
						
						if(!empty($s['payment_id'])){
							if(empty($all_product_data[$keyID]['payment_'.$s['payment_id']])){
								$all_product_data[$keyID]['payment_'.$s['payment_id']] = 0;
							}
							if(empty($all_product_data[$keyID]['payment_1'])){
								$all_product_data[$keyID]['payment_1'] = 0;
							}
							
							//$all_product_data[$keyID]['payment_'.$s['payment_id']] += $grand_total_order;
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
								
									$all_product_data[$keyID]['payment_1'] += $grand_total_order_cash;
									$all_product_data[$keyID]['payment_'.$s['payment_id']] += $grand_total_order_credit;
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
									
									$all_product_data[$keyID]['payment_1'] += $grand_total_order_cash;
									$all_product_data[$keyID]['payment_'.$s['payment_id']] += $grand_total_order_credit;
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
												$all_product_data[$keyID]['payment_1'] += $selisih_curr_cash;
												$all_product_data[$keyID]['payment_'.$s['payment_id']] -= $selisih_curr_cash;
									
											}else{
												$balancing_payment_billing[$s['billing_id']]['curr_cash'] -= $selisih_curr_cash;
												$balancing_payment_billing[$s['billing_id']]['curr_credit'] += $selisih_curr_cash;
												$all_product_data[$keyID]['payment_1'] -= $selisih_curr_cash;
												$all_product_data[$keyID]['payment_'.$s['payment_id']] += $selisih_curr_cash;
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
											$all_product_data[$keyID]['payment_1'] += $selisih_curr_cash;
											$all_product_data[$keyID]['payment_'.$s['payment_id']] -= $selisih_curr_cash;
								
										}else{
											$balancing_payment_billing[$s['billing_id']]['curr_cash'] -= $selisih_curr_cash;
											$balancing_payment_billing[$s['billing_id']]['curr_credit'] += $selisih_curr_cash;
											$all_product_data[$keyID]['payment_1'] -= $selisih_curr_cash;
											$all_product_data[$keyID]['payment_'.$s['payment_id']] += $selisih_curr_cash;
										}
									}
								}
								
							}else{
								$all_product_data[$keyID]['payment_'.$s['payment_id']] += $grand_total_order;
							}
							
						}
						
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
									'net_sales_total'		=> 0,
									'is_compliment'			=> $s['is_compliment'],
									'discount_perbilling'	=> $s['discount_perbilling'],
									'buyget'				=> 0,
									'free'					=> 0,
									'package'				=> 0,
									'discount_detail'		=> array(),
									'is_balanced'			=> $is_balanced,
									'diskon_sebelum_pajak_service' => $s['diskon_sebelum_pajak_service']
								);
							}
						}
						
						if(!empty($s['billing_discount_total']) AND $skip_balancing ==  false){
							if(empty($balancing_discount_billing[$s['billing_id']]['discount_detail'][$s['product_id']])){
								$balancing_discount_billing[$s['billing_id']]['discount_detail'][$s['product_id']] = array(
									'total_discount'=> 0,
									'total_discount_balance'=> 0,
									'tax_total'	=> 0,
									'service_total'	=> 0,
									'total_billing'	=> 0,
									'sub_total'	=> 0,
									'net_sales_total'	=> 0,
									'sub_total_balance'=> 0,
									'discount_balance'=> 0
								);
							}
							
							$balancing_discount_billing[$s['billing_id']]['discount_detail'][$s['product_id']]['total_discount'] += $total_discount_product;
							$balancing_discount_billing[$s['billing_id']]['discount_detail'][$s['product_id']]['tax_total'] += $s['tax_total'];
							$balancing_discount_billing[$s['billing_id']]['discount_detail'][$s['product_id']]['service_total'] += $s['service_total'];
							$balancing_discount_billing[$s['billing_id']]['discount_detail'][$s['product_id']]['total_billing'] += $total_billing;
							$balancing_discount_billing[$s['billing_id']]['discount_detail'][$s['product_id']]['sub_total'] += $sub_total;
							$balancing_discount_billing[$s['billing_id']]['discount_detail'][$s['product_id']]['net_sales_total'] += $net_sales_total;
							$balancing_discount_billing[$s['billing_id']]['discount_detail_total'] += $total_discount_product;
							$balancing_discount_billing[$s['billing_id']]['payment_id'] = $s['payment_id'];
							$balancing_discount_billing[$s['billing_id']]['total_billing'] += $total_billing;
							$balancing_discount_billing[$s['billing_id']]['sub_total'] += $sub_total;
							$balancing_discount_billing[$s['billing_id']]['net_sales_total'] += $net_sales_total;

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
			
			//echo '$all_qty_billing = '.count($all_qty_billing).'<br/>';
			//echo '$all_qty_item = '.$all_qty_item.'<br/>';
			//echo 'balancing_discount_billing :'.count($balancing_discount_billing).'<br/>';
			//echo '<pre>';
			//print_r($balancing_discount_billing);
			//die();
			
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
			//$data_diskon_awal_payment = array();
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
									$data_selisih_diskon_payment[$product_id] = array();
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
			//print_r($all_product_data);
			//echo 'TOTAL = '.count($all_product_data).'<br/>';
			//print_r($all_product_data);
			//die();

			$varian_name = array();
			if(!empty($order_qty)){
				if($order_qty == 3 OR $order_qty == 4){
					$this->db->select("*");
					$this->db->from($this->table_varian);
					$get_varian = $this->db->get();
					if($get_varian->num_rows() > 0){
						foreach($get_varian->result() as $dt){
							$varian_name[$dt->id] = $dt->varian_name;
						}
					}
				}
			}
			
			$recap_sort = array();
			$sort_qty = array();
			$sort_profit = array();
			$no = 1;
			if(!empty($all_product_data)){
				foreach($all_product_data as $dt){
					$dt['item_no'] = $no;
					
					$keyID = $dt['product_id'];
					$keyID_sort = $dt['product_id'];
					
					if(!empty($order_qty)){
						if($order_qty == 3 OR $order_qty == 4){
							$keyID = $dt['product_id']."_".$dt['varian_id'];
							$keyID_sort = $dt['product_id']."_".$dt['varian_id'];
							
							if(!empty($varian_name[$dt['varian_id']])){
								//$dt['product_name'] = $dt['product_name'].' - '.$varian_name[$dt['varian_id']];
								$dt['varian_name'] = $varian_name[$dt['varian_id']];
							}
							
						}
					}
					
					if(empty($sort_qty[$keyID_sort])){
						$sort_qty[$keyID_sort]  = 0;
					}
					$sort_qty[$keyID_sort] += $dt['total_qty'];
					
					
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
					//echo 'discount_total_before='.$dt['discount_total_before'].'<br/>';
					//echo 'discount_total_after='.$dt['discount_total_after'].'<br/><br/>';
					//echo 'grandtotal awal='.$dt['grand_total'].'<br/>';
					
					//exclude tax service
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
								//echo 'BALANCING DISKON PAYMENT -= '.$dtP.'<br/>';
							}
						}
					}

					if(!empty($data_selisih_diskon_payment[$dt['product_id']])){
						foreach($data_selisih_diskon_payment[$dt['product_id']] as $payment_id => $dtP){
							if(!empty($dt['payment_'.$payment_id])){
								$dt['payment_'.$payment_id] -= $dtP;
								//echo 'SELISIH DISKON PAYMENT -= '.$dtP.'<br/>';
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
					$dt['net_sales_total_show'] = priceFormat($dt['net_sales_total']);
					$dt['tax_total_show'] = priceFormat($dt['tax_total']);
					$dt['service_total_show'] = priceFormat($dt['service_total']);
					
					$dt['total_pembulatan_show'] = priceFormat($dt['total_pembulatan']);
					$dt['discount_total_show'] = priceFormat($dt['discount_total']);
					$dt['discount_billing_total_show'] = priceFormat($dt['discount_billing_total']);
					
					$dt['all_discount_total'] = ($dt['discount_total']+$dt['discount_billing_total']);
					$dt['all_discount_total_show'] = priceFormat($dt['discount_total']+$dt['discount_billing_total']);
					
					$dt['discount_total_before_show'] = priceFormat($dt['discount_total_before']);
					$dt['discount_billing_total_before_show'] = priceFormat($dt['discount_billing_total_before']);
					$dt['discount_total_after_show'] = priceFormat($dt['discount_total_after']);
					$dt['discount_billing_total_after_show'] = priceFormat($dt['discount_billing_total_after']);
					
					$dt['compliment_total_show'] = priceFormat($dt['compliment_total']);
					$dt['total_compliment'] = $dt['compliment_total'];
					$dt['total_compliment_show'] = priceFormat($dt['compliment_total']);
					
					//update profit
					$dt['total_billing_profit'] = $dt['total_billing'];
					$dt['total_billing_profit'] -= $dt['discount_total'];
					$dt['total_billing_profit'] -= $dt['discount_billing_total'];
					$dt['total_billing_profit'] -= $dt['total_compliment'];
					$dt['total_billing_profit_show'] = priceFormat($dt['total_billing_profit']);
					
					$dt['total_profit'] = $dt['total_billing_profit']-$dt['total_hpp'];
					$dt['total_hpp_show'] = priceFormat($dt['total_hpp']);
					$dt['total_profit_show'] = priceFormat($dt['total_profit']);
					
					if($sortingDesc == 'DESC'){
						if(empty($recap_sort[$keyID_sort])){
							$recap_sort[$keyID_sort] = 0;
						}
						if($sorting == 'qty'){
							$recap_sort[$keyID_sort] = $dt['total_qty'];
						}
						if($sorting == 'total_billing'){
							$recap_sort[$keyID_sort] = $dt['total_billing'];
						}
						if($sorting == 'all_discount_total'){
							$recap_sort[$keyID_sort] =  ($dt['discount_total']+$dt['discount_billing_total']);
						}
						if($sorting == 'discount_total'){
							$recap_sort[$keyID_sort] =  $dt['discount_total'];
						}
						if($sorting == 'discount_perbilling'){
							$recap_sort[$keyID_sort] = $dt['discount_billing_total'];
						}
						if($sorting == 'compliment_total'){
							$recap_sort[$keyID_sort] = $dt['compliment_total'];
						}
						if($sorting == 'net_sales_total'){
							$recap_sort[$keyID_sort] = $dt['net_sales_total'];
						}
						if($sorting == 'tax_total'){
							$recap_sort[$keyID_sort] = $dt['tax_total'];
						}
						if($sorting == 'service_total'){
							$recap_sort[$keyID_sort] = $dt['service_total']; 
						}
						if($sorting == 'total_pembulatan'){
							$recap_sort[$keyID_sort] = $dt['total_pembulatan'];
						}
						if($sorting == 'grand_total'){
							$recap_sort[$keyID_sort] = $dt['grand_total'];
						}
						if($sorting == 'total_dp'){
							$recap_sort[$keyID_sort] = $dt['total_dp'];
						}
						if($sorting == 'payment_cash'){
							$recap_sort[$keyID_sort] = $dt['payment_1'];
						}
						if($sorting == 'payment_debit'){
							$recap_sort[$keyID_sort] = $dt['payment_2'];
						}
						if($sorting == 'payment_credit'){
							$recap_sort[$keyID_sort] = $dt['payment_3'];
						}
						if($sorting == 'payment_ar'){
							$recap_sort[$keyID_sort] = $dt['payment_4'];
						}
						if($sorting == 'total_hpp'){
							$recap_sort[$keyID_sort] = $dt['total_hpp'];
						}
						if($sorting == 'total_profit'){
							$recap_sort[$keyID_sort] = $dt['total_profit'];
						}
					}else{
						if($sorting == 'a-z'){
							$recap_sort[$keyID_sort] = $dt['product_name'];
						}
						if($sorting == 'code'){
							$recap_sort[$keyID_sort] = $dt['product_code'];
						}
					}
					
					if(empty($sort_profit[$keyID_sort])){
						$sort_profit[$keyID_sort] = 0;
					}
					$sort_profit[$keyID_sort] += $dt['total_profit'];
					
					if(!empty($order_qty)){
						if($order_qty == 3 OR $order_qty == 4){
							
							//if(!empty($dt['varian_id'])){
								if(empty($newData[$keyID_sort])){
									$newData[$keyID_sort] = array();
								}
								
								$newData[$keyID_sort][$dt['varian_id']] = $dt;
							//}
							
						}else{
							$newData[$keyID_sort] = $dt;
						}
					}else{
						$newData[$keyID_sort] = $dt;
					}
					
					
					$no++;
				}
			}
			
			//arsort($sort_qty);	
			$use_group = '';
			$tipe_report = '';
			if(!empty($order_qty)){
				$tipe_report = 'QTY';
				//RANK QTY
				
				/*if($order_qty == 1){
					arsort($sort_qty);
					$xnewData = array();
					foreach($sort_qty as $key => $dt){
			
						if(!empty($newData[$key])){
							$xnewData[] = $newData[$key];
						}
							
					}
					$newData = $xnewData;
				}*/
					
				//RANK PROFIT
				if($order_qty == 2){
					$tipe_report = 'PROFIT';
					/*if($sorting == 'qty'){
						arsort($sort_profit);
					}
					$xnewData = array();
					foreach($sort_profit as $key => $dt){
			
						if(!empty($newData[$key])){
							$xnewData[] = $newData[$key];
						}
							
					}
					$newData = $xnewData;*/
				}
				
				//VARIAN 
				if($order_qty == 3){
					$tipe_report = 'VARIAN';
					$use_group = 'varian_id';
					/*if($sorting == 'qty'){
						arsort($sort_qty);
					}
					$new_GroupData = array();
					foreach($sort_qty as $key => $dt){
						
						if(!empty($newData[$key])){
							foreach($newData[$key] as $varID => $dtx){
								if(empty($new_GroupData[$dtx['varian_id']])){
									$new_GroupData[$dtx['varian_id']] = array();
								}
									
								$new_GroupData[$dtx['varian_id']][] = $dtx;
							}
						}
							
					}
					$newData = $new_GroupData;*/
					
				}
				
				//VARIAN PROFIT
				if($order_qty == 4){
					$tipe_report = 'VARIAN PROFIT';
					$use_group = 'varian_id';
					/*if($sorting == 'qty'){
						arsort($sort_profit);
					}
					$new_GroupData = array();
					foreach($sort_profit as $key => $dt){
						
						if(!empty($newData[$key])){
							foreach($newData[$key] as $varID => $dtx){
								if(empty($new_GroupData[$dtx['varian_id']])){
									$new_GroupData[$dtx['varian_id']] = array();
								}
									
								$new_GroupData[$dtx['varian_id']][] = $dtx;
							}
						}
							
					}
					$newData = $new_GroupData;*/
				}
				
				
				//PACKAGE 
				if($order_qty == 5){
					$tipe_report = 'PACKAGE';
					$use_group = 'product_type';
					/*if($sorting == 'qty'){
						arsort($sort_qty);
					}
					
					$new_GroupData = array();
					
					foreach($sort_qty as $key => $dt){
						$dtx = $newData[$key];
						
						if(empty($new_GroupData[$dtx['product_type']])){
							$new_GroupData[$dtx['product_type']] = array();
						}
							
						$new_GroupData[$dtx['product_type']][] = $dtx;
					}
					$newData = $new_GroupData;*/
				}
				
				//PACKAGE PROFIT
				if($order_qty == 6){
					$tipe_report = 'PACKAGE PROFIT';
					$use_group = 'product_type';
					/*if($sorting == 'qty'){
						arsort($sort_profit);
					}
					
					$new_GroupData = array();
					foreach($sort_profit as $key => $dt){
						$dtx = $newData[$key];
						
						if(empty($new_GroupData[$dtx['product_type']])){
							$new_GroupData[$dtx['product_type']] = array();
						}
							
						$new_GroupData[$dtx['product_type']][] = $dtx;
					}
					$newData = $new_GroupData;*/
				}
				
				//TAXSERVICE
				if($order_qty == 7){
					$tipe_report = 'TAXSERVICE';
					$use_group = 'taxservice';
					/*if($sorting == 'qty'){
						arsort($sort_qty);
					}
					
					$new_GroupData = array();
					
					foreach($sort_qty as $key => $dt){
						$dtx = $newData[$key];
						
						$is_taxservice = 0;
						if(!empty($dtx['tax_total'])){
							$is_taxservice = 1;
						}
						
						if(empty($new_GroupData[$is_taxservice])){
							$new_GroupData[$is_taxservice] = array();
						}
							
						$new_GroupData[$is_taxservice][] = $dtx;
					}
					$newData = $new_GroupData;*/
				}
				
				//TAXSERVICE PROFIT
				if($order_qty == 8){
					$tipe_report = 'TAXSERVICE PROFIT';
					$use_group = 'taxservice';
					/*if($sorting == 'qty'){
						arsort($sort_profit);
					}
					
					$new_GroupData = array();
					foreach($sort_profit as $key => $dt){
						$dtx = $newData[$key];
						
						$is_taxservice = 0;
						if(!empty($dtx['tax_total'])){
							$is_taxservice = 1;
						}
						
						if(empty($new_GroupData[$is_taxservice])){
							$new_GroupData[$is_taxservice] = array();
						}
							
						$new_GroupData[$is_taxservice][] = $dtx;
					}
					$newData = $new_GroupData;*/
				}
				
				//MENU HPP
				if($order_qty == 9){
					$tipe_report = 'MENU HPP';
					/*arsort($sort_qty);
					$xnewData = array();
					foreach($sort_qty as $key => $dt){
			
						if(!empty($newData[$key])){
							$xnewData[] = $newData[$key];
						}
							
					}
					$newData = $xnewData;*/
				}
				
				//MENU HPP PROFIT
				if($order_qty == 10){
					$tipe_report = 'MENU HPP PROFIT';
					/*if($sorting == 'qty'){
						arsort($sort_profit);
					}
					$xnewData = array();
					foreach($sort_profit as $key => $dt){
			
						if(!empty($newData[$key])){
							$xnewData[] = $newData[$key];
						}
							
					}
					$newData = $xnewData;*/
				}
				
			}else{
				$order_qty = 0;
				//$xnewData = array();
				//foreach($newData as $dt){
				//	$xnewData[] = $dt;
				//}
				
				
			}
			
			if(!empty($groupCat)){
				$use_group = 'category_id';
			}
			
			//update-2001.002
			if(!empty($use_group)){
				if($sortingDesc == 'ASC'){
					asort($recap_sort);
				}else{
					arsort($recap_sort);
				}
				
				$new_GroupData = array();
				foreach($recap_sort as $key => $dt){
					
					if($use_group == 'taxservice'){
						$dtx = $newData[$key];
						$is_taxservice = 0;
						if(!empty($dtx['tax_total'])){
							$is_taxservice = 1;
						}
						
						if(empty($new_GroupData[$is_taxservice])){
							$new_GroupData[$is_taxservice] = array();
						}
							
						$new_GroupData[$is_taxservice][] = $dtx;
					}else
					if($use_group == 'varian_id'){
						if(!empty($newData[$key])){
							foreach($newData[$key] as $varID => $dtx){
								if(empty($new_GroupData[$dtx[$use_group]])){
									$new_GroupData[$dtx[$use_group]] = array();
								}
									
								$new_GroupData[$dtx[$use_group]][] = $dtx;
							}
						}
					}else{
						$dtx = $newData[$key];
						if(!empty($dtx[$use_group])){
							if(empty($new_GroupData[$dtx[$use_group]])){
								$new_GroupData[$dtx[$use_group]] = array();
							}
								
							$new_GroupData[$dtx[$use_group]][] = $dtx;
						}else{
							
							if(empty($new_GroupData[0])){
								$new_GroupData[0] = array();
							}
								
							$new_GroupData[0][] = $dtx;
						}
					}
						
				}
				$newData = $new_GroupData;
			}else{
				if($sortingDesc == 'ASC'){
					asort($recap_sort);
				}else{
					arsort($recap_sort);
				}
				
				$xnewData = array();
				if(!empty($recap_sort)){
					foreach($recap_sort as $keyId => $val){
						if(!empty($newData[$keyId])){
							$dt = $newData[$keyId];	
							$xnewData[] = $dt;
						}
					}
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
			
			//category_name
			$category_name = array();
			$category_code = array();
			$this->db->select('*');
			$this->db->from($this->prefix.'product_category');
			$get_dt_p = $this->db->get();
			if($get_dt_p->num_rows() > 0){
				foreach($get_dt_p->result_array() as $dtP){
					$category_name[$dtP['id']] = strtoupper($dtP['product_category_name']);
					$category_code[$dtP['id']] = strtoupper($dtP['product_category_code']);
				}
			}
			


			//GROUPING
			/*$new_GroupData = array();
			if(!empty($groupCat)){
				foreach($newData as $dt){
					if(empty($new_GroupData[$dt['category_id']])){
						$new_GroupData[$dt['category_id']] = array();
					}
						
					$new_GroupData[$dt['category_id']][] = $dt;
				}
				
				ksort($new_GroupData);
				
				$newData = $new_GroupData;
			}*/
				
			$data_post['report_data'] = $newData;
			$data_post['payment_data'] = $dt_payment_name;
			$data_post['category_name'] = $category_name;
			$data_post['category_code'] = $category_code;
			$data_post['varian_name'] = $varian_name;
			$data_post['display_discount_type'] = $display_discount_type;
						
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		if(empty($useview)){
			$useview = 'print_reportSalesByMenu';
			$data_post['report_name'] = 'SALES PRODUCT/MENU';
			
			if($do == 'excel'){
				$useview = 'excel_reportSalesByMenu';
			}
			
			if($tipe_report == 'VARIAN'){
				$data_post['report_name'] = 'SALES PRODUCT/MENU - VARIAN';
				$useview = 'print_reportSalesByMenuVarian';
				
				if($do == 'excel'){
					$useview = 'excel_reportSalesByMenuVarian';
				}
			}
			
			if($tipe_report == 'PACKAGE'){
				$data_post['report_name'] = 'SALES PRODUCT/MENU - PACKAGE';
				$useview = 'print_reportSalesByMenuPackage';
				
				if($do == 'excel'){
					$useview = 'excel_reportSalesByMenuPackage';
				}
			}
			
			if($tipe_report == 'TAXSERVICE'){
				$data_post['report_name'] = 'SALES PRODUCT/MENU - TAX &amp; SERVICE';
				$useview = 'print_reportSalesByTaxService';
				
				if($do == 'excel'){
					$useview = 'excel_reportSalesByTaxService';
				}
			}
			
			if($tipe_report == 'MENU HPP'){
				$data_post['report_name'] = 'SALES PRODUCT/MENU - HPP';
				$useview = 'print_reportSalesByMenuHpp';
				
				if($do == 'excel'){
					$useview = 'excel_reportSalesByMenuHpp';
				}
			}
			
		}else{
			$useview = 'print_reportProfitSalesByMenu';
			$data_post['report_name'] = 'SALES PROFIT PRODUCT/MENU';
			
			if($do == 'excel'){
				$useview = 'excel_reportProfitSalesByMenu';
			}
			
			if($tipe_report == 'VARIAN PROFIT'){
				$data_post['report_name'] = 'SALES PROFIT PRODUCT/MENU - VARIAN';
				$useview = 'print_reportProfitSalesByMenuVarian';
			
				if($do == 'excel'){
					$useview = 'excel_reportProfitSalesByMenuVarian';
				}
			}
			
			if($tipe_report == 'PACKAGE PROFIT'){
				$data_post['report_name'] = 'SALES PROFIT PRODUCT/MENU - PACKAGE';
				$useview = 'print_reportProfitSalesByMenuPackage';
			
				if($do == 'excel'){
					$useview = 'excel_reportProfitSalesByMenuPackage';
				}
			}
			
			if($tipe_report == 'TAXSERVICE PROFIT'){
				$data_post['report_name'] = 'SALES PROFIT PRODUCT/MENU - TAX &amp; SERVICE';
				$useview = 'print_reportProfitSalesByTaxService';
			
				if($do == 'excel'){
					$useview = 'excel_reportProfitSalesByTaxService';
				}
			}
			
			if($tipe_report == 'MENU HPP PROFIT'){
				$data_post['report_name'] = 'SALES PROFIT PRODUCT/MENU - HPP';
				$useview = 'print_reportProfitSalesByMenuHpp';
			
				if($do == 'excel'){
					$useview = 'excel_reportProfitSalesByMenuHpp';
				}
			}
			
		}
		

		if(!empty($groupCat)){
			
			if($groupCat == 'subcat' OR $groupCat == 'subcat_profit'){
				
				if($groupCat == 'subcat_profit'){
					
					$useview = 'print_reportProfitSalesBySubMenuCategory';
					$data_post['report_name'] = 'SALES PROFIT BY SUB MENU CATEGORY';
					
					if($do == 'excel'){
						$useview = 'excel_reportProfitSalesBySubMenuCategory';
					}
					
				}else{
					$useview = 'print_reportSalesBySubMenuCategory';
					$data_post['report_name'] = 'SALES BY SUB MENU CATEGORY';
					
					if($do == 'excel'){
						$useview = 'excel_reportSalesBySubMenuCategory';
					}
				}
				
			}else
			{
				
				if($groupCat == 'cat_profit'){
					$useview = 'print_reportProfitSalesByMenuCategory';
					$data_post['report_name'] = 'SALES PROFIT BY MENU CATEGORY';
					
					if($do == 'excel'){
						$useview = 'excel_reportProfitSalesByMenuCategory';
					}
					
				}else{
					$useview = 'print_reportSalesByMenuCategory';
					$data_post['report_name'] = 'SALES BY MENU CATEGORY';
					
					if($do == 'excel'){
						$useview = 'excel_reportSalesByMenuCategory';
					}
				}
				
			}
		}
		
		
		$this->load->view('../../billing/views/'.$useview, $data_post);
	}
	
}