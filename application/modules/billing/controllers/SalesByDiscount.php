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
			'tipe_sales'	=> 'Semua Tipe Sales',
			'discount_type'	=> 'Semua Diskon',
			'user_shift'	=> 'Semua Shift',
			'cashier_name'	=> '',
			'user_fullname'	=> $user_fullname,
			'sorting'	=> $sorting,
			'diskon_sebelum_pajak_service' => 0,
			'display_discount_type'	=> array()
		);
		
		$display_discount_type = array();
		
		//update-2001.002
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
		$show_dp = json_decode($show_dp);
		$show_pembulatan = json_decode($show_pembulatan);
		$show_note = json_decode($show_note);
		$show_shift_kasir = json_decode($show_shift_kasir);
		$format_nominal = json_decode($format_nominal);
		
		$data_post['filter_column'] = array(
			'show_payment' => $show_payment,
			'show_compliment' => $show_compliment,
			'show_tax' => $show_tax,
			'show_service' => $show_service,
			'show_dp' => $show_dp,
			'show_pembulatan' => $show_pembulatan,
			'show_note' => $show_note,
			'show_shift_kasir' => $show_shift_kasir,
			'format_nominal' => $format_nominal
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
			
			//update-0120.001
			$where_shift_billing = "(a.payment_date >= '".$qdate_from."' AND a.payment_date <= '".$qdate_till_max."')";
			
			//update-0120.001
			if(!empty($shift_billing)){
				$where_shift_billing .= " AND a.shift = ".$shift_billing;
				$data_post['user_shift'] = '';
			}
			if(!empty($kasir_billing)){
				$where_shift_billing .= " AND a.updatedby = '".$kasir_billing."'";
				$data_post['user_kasir'] = '';
			}
			
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
			$this->db->select("a.*, a.id as billing_id, a.updated as billing_date, d.payment_type_name, e.bank_name,
								g.nama_shift, g2.customer_name, g3.sales_name, CONCAT(h.user_firstname,' ',h.user_lastname) as nama_kasir");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT');
			$this->db->join($this->prefix.'bank as e','e.id = a.bank_id','LEFT');
			$this->db->join($this->prefix.'shift as g','g.id = a.shift','LEFT');
			$this->db->join($this->prefix.'customer as g2','g2.id = a.customer_id','LEFT');
			$this->db->join($this->prefix.'sales as g3','g3.id = a.sales_id','LEFT');
			$this->db->join($this->prefix_apps.'users as h','h.user_username = a.updatedby','LEFT');
			$this->db->where("a.billing_status", 'paid');
			$this->db->where("a.is_deleted", 0);
			//$this->db->where($add_where);
			
			//update-0120.001
			$this->db->where($where_shift_billing);
			
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
			
			
			//if(empty($sorting)){
				$this->db->order_by("a.payment_date","ASC");
			//}else{
			//}
				
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
						$this->db->where("(a.is_compliment = 0 AND a.compliment_total = 0)");
						$data_post['tipe_sales'] = 'Tanpa Compliment';
						break;
						
					case 'sales_only_compliment': 
						$this->db->where("((a.is_compliment = 1 AND a.compliment_total > 0) OR (a.is_compliment = 0 AND a.compliment_total > 0))");
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
						$this->db->where("(a.sales_id = 0)");
						$data_post['tipe_sales'] = 'Tanpa Marketing/Sales-Fee';
						break;
					
					case 'sales_only_marketing': 
						$this->db->where("(a.sales_id > 0)");
						$data_post['tipe_sales'] = 'Marketing/Sales-Fee';
						break;
					
					default: 
						//nothing	
						break;
					
				}
				
			}
			
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
			
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
			
			
			//update-2002.003
			$all_bil_id = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
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
				}
			}
			

			//update-2002.003
			$total_billing = array();
			if(!empty($all_bil_id)){
				$all_bil_id_txt = implode(",",$all_bil_id);
				$this->db->from($this->table2);
				$this->db->where('billing_id IN ('.$all_bil_id_txt.')');
				$this->db->where('is_deleted', 0);
				$get_detail = $this->db->get();
				if($get_detail->num_rows() > 0){
					foreach($get_detail->result() as $dtRow){
						
						$total_qty = $dtRow->order_qty;
						
						//update-2002.003
						$dtRow->product_price_real_before = $dtRow->product_price_real;
						if((!empty($dtRow->include_tax) AND empty($dtRow->include_service)) OR (empty($dtRow->include_tax) AND !empty($dtRow->include_service))){
							if($dtRow->product_price != ($dtRow->product_price_real+$dtRow->tax_total+$dtRow->service_total)){
								$all_percentage = 100 + $dtRow->tax_percentage + $dtRow->service_percentage;
								$dtRow->product_price_real = priceFormat(($dtRow->product_price/($all_percentage/100)), 0, ".", "");
							}
						
							if($dtRow->is_compliment == 1){
								$dtRow->product_price_real = $dtRow->product_price_real_before;
							}
						}
						$total_billing[$dtRow->billing_id] += $dtRow->product_price_real * $total_qty;
						
					}
				}
			}
			
			$all_discount_id = array();
			$all_billing_discount_id = array();
			$all_billing_discount_id_buyget = array();
			
			//$all_bil_id = array();
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
					
					$s['billing_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['payment_date'] = date("d-m-Y H:i",strtotime($s['payment_date']));
					
					/*if($discount_type == 'buyget'){
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

					}*/
					
					
					$s['total_billing_awal'] = $s['total_billing'];
					
					//update-2002.003
					//CHECK REAL TOTAL BILLING
					if(!empty($s['include_tax']) OR !empty($s['include_service'])){
						//update-2002.003
						$s['total_billing'] = $total_billing[$s['id']];
						$s['total_billing_awal'] = $s['total_billing'];
						
						/*if(!empty($s['include_tax']) AND !empty($s['include_service'])){
							$s['total_billing'] = $s['total_billing'] - ($s['tax_total'] + $s['service_total']);
						}else{
							if(!empty($s['include_tax'])){
								$s['total_billing'] = $s['total_billing'] - ($s['tax_total']);
							}
							if(!empty($s['include_service'])){
								$s['total_billing'] = $s['total_billing'] - ($s['service_total']);
							}
						}*/
					}
					
					//update-2001.002
					//COMPLIMENT
					if(!empty($s['is_compliment']) OR !empty($s['compliment_total'])){
						//$s['total_billing'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
						if($s['total_billing'] <= $s['compliment_total']){
							$s['service_total'] = 0;
							$s['tax_total'] = 0;
						}
					}
					
					
					//SUBTOTAL : diskon_sebelum_pajak_service
					if($s['diskon_sebelum_pajak_service'] == 1){
						
						//update-2002.003
						//if(!empty($s['include_tax']) OR !empty($s['include_service'])){
						//	$s['total_billing'] = ($s['total_billing_awal'] - ($s['tax_total'] + $s['service_total']));
						//}
						
						//update-2001.002
						$s['sub_total'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'] - $s['discount_total'] - $s['compliment_total'];
						$s['net_sales_total'] = $s['total_billing'] - $s['discount_total'] - $s['compliment_total'];
						
						//GRAND TOTAL
						$s['grand_total'] = $s['sub_total'];
						
					}else{
						
						//update-2001.002
						$s['sub_total'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'] - $s['discount_total'] - $s['compliment_total'];	
						$s['net_sales_total'] = $s['total_billing'] - $s['discount_total'] - $s['compliment_total'];
						
						//GRAND TOTAL
						$s['grand_total'] = $s['sub_total'];
						//$s['grand_total'] -= $s['discount_total'];
						//$s['grand_total'] -= $s['discount_billing_total'];
						
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
					
					$s['sub_total_show'] = priceFormat($s['sub_total']);
					$s['net_sales_total_show'] = priceFormat($s['net_sales_total']);
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
						$s['payment_note'] .= 'COMPLIMENT ';
						//$s['total_compliment'] = $s['grand_total'];
						$s['total_compliment'] = $s['compliment_total'];
						$s['total_compliment_show'] = priceFormat($s['total_compliment']);
					}
					
					//update-2001.002
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
					
					if(!empty($s['customer_id'])){
						if(!empty($s['payment_note'])){
							$s['payment_note'] .= '<br/>';
						}
						$s['payment_note'] .= 'Cust/Member: '.$s['customer_name'];
					}
					
					if(!empty($s['sales_id'])){
						if(!empty($s['payment_note'])){
							$s['payment_note'] .= '<br/>';
						}
						$s['payment_note'] .= 'Marketing/Sales: '.$s['sales_name'];
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
							'net_sales_total'	=> 0,
							'net_sales_total_show'	=> 0,
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
							'discount_total_before'	=> 0,
							'discount_total_before_show'	=> 0,
							'discount_billing_total_before'	=> 0,
							'discount_billing_total_before_show'	=> 0,
							'discount_total_after'	=> 0,
							'discount_total_after_show'	=> 0,
							'discount_billing_total_after'	=> 0,
							'discount_billing_total_after_show'	=> 0,
						
						);
						
						$newData_buyget[$getBillID] = array_merge($billing_data[$getBillID], $addData);

					}
				}
			}
			//BUYGET ---------------------------------------------------
			
			//echo '<pre>';
			//print_r($newData_buyget);
			//die();
			
			//update-2001.002
			$recap_sort = array();
			$recap_sort_buyget = array();
			
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
				$this->db->select("a.*, b.billing_no, b.total_billing, b.discount_perbilling, b.payment_id, b.payment_date, b.discount_notes,
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
						$sub_total = 0;
						$net_sales_total = 0;
						
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
							$s['product_price_real_before'] = $s['product_price_real'];
							if((!empty($include_tax) AND empty($include_service)) OR (empty($include_tax) AND !empty($include_service))){
								if($s['product_price'] != ($s['product_price_real']+$s['tax_total']+$s['service_total'])){
									$s['product_price_real'] = priceFormat(($s['product_price']/($all_percentage/100)), 0, ".", "");
								}
							}
								
							if(!empty($s['is_compliment'])){
								//update-2003.001
								$s['product_price_real'] = $s['product_price_real_before'];
							}
							
							$total_billing_order = ($s['product_price_real']*$s['order_qty']);
							$tax_total_order = $s['tax_total'];
							$service_total_order = $s['service_total'];
							
							if($s['diskon_sebelum_pajak_service'] == 1){
								
								//$grand_total_order = ($s['product_price_real']*$s['order_qty'])- $s['discount_total'];
								
								$sub_total = ($s['product_price_real']*$s['order_qty']);
								$sub_total += $s['tax_total'];
								$sub_total += $s['service_total'];
								
								$grand_total_order = $sub_total;
								$sub_total -= $s['discount_total'];
								
							}else{
								
								//$grand_total_order = ($s['product_price_real']*$s['order_qty']);
							
								$sub_total = ($s['product_price_real']*$s['order_qty']);
								$sub_total += $s['tax_total'];
								$sub_total += $s['service_total'];
								
								$grand_total_order = $sub_total;
								//$grand_total_order -= $s['discount_total'];
								
							}
							
						}else
						{
								
							$total_billing_order = ($s['product_price']*$s['order_qty']);
							$tax_total_order = $s['tax_total'];
							$service_total_order = $s['service_total'];
							
							if($s['diskon_sebelum_pajak_service'] == 1){
								
								//$grand_total_order = ($s['product_price']*$s['order_qty'])- $s['discount_total'];
								$sub_total = ($s['product_price']*$s['order_qty']);
								$sub_total += $s['tax_total'];
								$sub_total += $s['service_total'];
								
								$grand_total_order = $sub_total;
								$sub_total -= $s['discount_total'];
								
								//update-2001.002
								$net_sales_total = $total_billing_order - $s['discount_total'];
							
							}else{
								
								//$grand_total_order = ($s['product_price']*$s['order_qty']);
								$sub_total = ($s['product_price']*$s['order_qty']);
								$sub_total += $s['tax_total'];
								$sub_total += $s['service_total'];
								
								$grand_total_order = $sub_total;
								$sub_total -= $s['discount_total'];
								
								//update-2001.002
								$net_sales_total = $total_billing_order - $s['discount_total'];
								
							}
							
						}
						
						
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
						
						//OVERRIDE PEMBULATAN PERITEM
						$total_pembulatan = 0;
						
						$grand_total_order += $total_pembulatan;
						
						
						if(!empty($s['is_compliment'])){
							$compliment_total = $grand_total_order;
							$grand_total_order -= $compliment_total;
						}
						//ALL DETAIl CALC --------------------------------------------------
					
					
						
						$get_disc_id = 0;
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
						
						//update-2001.002
						if($sortingDesc == 'DESC'){
							if(empty($recap_sort[$get_disc_id])){
								$recap_sort[$get_disc_id] = 0;
							}
							if($sorting == 'qty_menu'){
								$recap_sort[$get_disc_id] += $total_qty;
							}
							if($sorting == 'total_billing'){
								$recap_sort[$get_disc_id] += $total_billing;
							}
							if($sorting == 'discount_total'){
								$recap_sort[$get_disc_id] += $discount_total;
							}
							if($sorting == 'discount_perbilling'){
								$recap_sort[$get_disc_id] += $discount_billing_total;
							}
							if($sorting == 'compliment_total'){
								$recap_sort[$get_disc_id] += $compliment_total;
							}
							if($sorting == 'net_sales_total'){
								$recap_sort[$get_disc_id] += $net_sales_total;
							}
							if($sorting == 'tax_total'){
								$recap_sort[$get_disc_id] += $s['tax_total'];
							}
							if($sorting == 'service_total'){
								$recap_sort[$get_disc_id] += $s['service_total']; 
							}
							if($sorting == 'pembulatan'){
								$recap_sort[$get_disc_id] += $total_pembulatan;
							}
							if($sorting == 'grand_total'){
								$recap_sort[$get_disc_id] += $grand_total_order;
							}
						}else{
							if($sorting == 'payment_date'){
								$recap_sort[$get_disc_id] = strtotime($s['payment_date']);
							}
							if($sorting == 'billing_no'){
								$recap_sort[$get_disc_id] = $s['billing_no'];
							}
							if($sorting == 'discount_notes'){
								$recap_sort[$get_disc_id] = $s['discount_notes'];
							}
							if($sorting == 'discount_type'){
								if(!empty($discount_billing_total)){
									$recap_sort[$get_disc_id] = 2;
								}else
								if(!empty($discount_total)){
									$recap_sort[$get_disc_id] = 1;
								}else{
									$recap_sort[$get_disc_id] = 0;
								}
							}
						}
						
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
									$newData_buyget[$get_billing_id]['net_sales_total'] += $net_sales_total;
									$newData_buyget[$get_billing_id]['total_pembulatan'] += $total_pembulatan;
									$newData_buyget[$get_billing_id]['grand_total'] += $grand_total_order;
									
									//update-2001.002
									if($sortingDesc == 'DESC'){
										if(empty($recap_sort_buyget[$get_billing_id])){
											$recap_sort_buyget[$get_billing_id] = 0;
										}
										if($sorting == 'qty_menu'){
											$recap_sort_buyget[$get_billing_id] += $total_qty;
										}
										if($sorting == 'total_billing'){
											$recap_sort_buyget[$get_billing_id] += $total_billing;
										}
										if($sorting == 'discount_total'){
											$recap_sort_buyget[$get_billing_id] += $discount_total;
										}
										if($sorting == 'discount_perbilling'){
											$recap_sort_buyget[$get_billing_id] += $discount_billing_total;
										}
										if($sorting == 'compliment_total'){
											$recap_sort_buyget[$get_billing_id] += $compliment_total;
										}
										if($sorting == 'net_sales_total'){
											$recap_sort_buyget[$get_billing_id] += $net_sales_total;
										}
										if($sorting == 'tax_total'){
											$recap_sort_buyget[$get_billing_id] += $s['tax_total'];
										}
										if($sorting == 'service_total'){
											$recap_sort_buyget[$get_billing_id] += $s['service_total']; 
										}
										if($sorting == 'pembulatan'){
											$recap_sort_buyget[$get_billing_id] += $total_pembulatan;
										}
										if($sorting == 'grand_total'){
											$recap_sort_buyget[$get_billing_id] += $grand_total_order;
										}
									}else{
										if($sorting == 'payment_date'){
											$recap_sort_buyget[$get_billing_id] = strtotime($s['payment_date']);
										}
										if($sorting == 'billing_no'){
											$recap_sort_buyget[$get_billing_id] = $s['billing_no'];
										}
										if($sorting == 'discount_notes'){
											$recap_sort_buyget[$get_billing_id] = $s['discount_notes'];
										}
										if($sorting == 'discount_type'){
											if(!empty($discount_billing_total)){
												$recap_sort_buyget[$get_billing_id] = 2;
											}else
											if(!empty($discount_total)){
												$recap_sort_buyget[$get_billing_id] = 1;
											}else{
												$recap_sort_buyget[$get_billing_id] = 0;
											}
										}
									}
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
				
				//update-2001.002
				if($sortingDesc == 'ASC'){
					asort($recap_sort);
				}else{
					arsort($recap_sort);
				}
				
				if(!empty($recap_sort)){
					foreach($recap_sort as $disc_id => $val){
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
							$dt['net_sales_total_show'] = priceFormat($dt['net_sales_total']);
							$dt['total_pembulatan_show'] = priceFormat($dt['total_pembulatan']);
							$dt['total_compliment_show'] = priceFormat($dt['total_compliment']);
							$dt['grand_total_show'] = priceFormat($dt['grand_total']);
							
							//if(!empty($dt['grand_total'])){
								$newData[] = $dt;
							//}
						}
					}
				}
			}
			
			//newData_buyget
			$newData_switch = $newData_buyget;
			$newData_buyget = array();
			if(!empty($newData_switch)){
				
				//update-2001.002
				if($sortingDesc == 'ASC'){
					asort($recap_sort_buyget);
				}else{
					arsort($recap_sort_buyget);
				}
				
				if(!empty($recap_sort_buyget)){
					foreach($recap_sort_buyget as $disc_id => $val){
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
							$dt['net_sales_total_show'] = priceFormat($dt['net_sales_total']);
							$dt['total_pembulatan_show'] = priceFormat($dt['total_pembulatan']);
							$dt['total_compliment_show'] = priceFormat($dt['total_compliment']);
							$dt['grand_total_show'] = priceFormat($dt['grand_total']);
							
							//if(!empty($dt['grand_total'])){
								$newData_buyget[] = $dt;
							//}
						}
					}
				}
			}
			
			$data_post['report_data'] = $newData;
			$data_post['buyget_data'] = $newData_buyget;
			$data_post['payment_data'] = $dt_payment_name;
			$data_post['display_discount_type'] = $display_discount_type;
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
			$sorting = 'discount_notes';
		}
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'SALES BY DISCOUNT RECAP',
			'tipe_sales'	=> 'Semua Tipe Sales',
			'discount_type'	=> 'Semua Tipe Diskon',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'user_fullname'	=> $user_fullname,
			'sorting'	=> $sorting,
			'diskon_sebelum_pajak_service' => 0,
			'display_discount_type'	=> array()
		);
		
		$display_discount_type = array();
		
		//update-2001.002
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
		$show_dp = json_decode($show_dp);
		$show_pembulatan = json_decode($show_pembulatan);
		$show_note = json_decode($show_note);
		$show_shift_kasir = json_decode($show_shift_kasir);
		$format_nominal = json_decode($format_nominal);
		
		$data_post['filter_column'] = array(
			'show_payment' => $show_payment,
			'show_compliment' => $show_compliment,
			'show_tax' => $show_tax,
			'show_service' => $show_service,
			'show_dp' => $show_dp,
			'show_pembulatan' => $show_pembulatan,
			'show_note' => $show_note,
			'show_shift_kasir' => $show_shift_kasir,
			'format_nominal' => $format_nominal
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
			
			//update-0120.001
			$where_shift_billing = "(a.payment_date >= '".$qdate_from."' AND a.payment_date <= '".$qdate_till_max."')";
			
			//update-0120.001
			if(!empty($shift_billing)){
				$where_shift_billing .= " AND a.shift = ".$shift_billing;
				$data_post['user_shift'] = '';
			}
			if(!empty($kasir_billing)){
				$where_shift_billing .= " AND a.updatedby = '".$kasir_billing."'";
				$data_post['user_kasir'] = '';
			}
			
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
			$this->db->select("a.*, a.id as billing_id, a.updated as billing_date, d.payment_type_name, e.bank_name,
								g.nama_shift, g2.customer_name, g3.sales_name, CONCAT(h.user_firstname,' ',h.user_lastname) as nama_kasir");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT');
			$this->db->join($this->prefix.'bank as e','e.id = a.bank_id','LEFT');
			$this->db->join($this->prefix.'shift as g','g.id = a.shift','LEFT');
			$this->db->join($this->prefix.'customer as g2','g2.id = a.customer_id','LEFT');
			$this->db->join($this->prefix.'sales as g3','g3.id = a.sales_id','LEFT');
			$this->db->join($this->prefix_apps.'users as h','h.user_username = a.updatedby','LEFT');
			$this->db->where("a.billing_status", 'paid');
			$this->db->where("a.is_deleted", 0);
			//$this->db->where($add_where);
			
			//update-0120.001
			$this->db->where($where_shift_billing);
			
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
			
			//if(empty($sorting)){
				$this->db->order_by("payment_date","ASC");
			//}else{
			//	
			//}
			
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
						$this->db->where("(a.is_compliment = 0 AND a.compliment_total = 0)");
						$data_post['tipe_sales'] = 'Tanpa Compliment';
						break;
						
					case 'sales_only_compliment': 
						$this->db->where("((a.is_compliment = 1 AND a.compliment_total > 0) OR (a.is_compliment = 0 AND a.compliment_total > 0))");
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
						$this->db->where("(a.sales_id = 0)");
						$data_post['tipe_sales'] = 'Tanpa Marketing/Sales-Fee';
						break;
					
					case 'sales_only_marketing': 
						$this->db->where("(a.sales_id > 0)");
						$data_post['tipe_sales'] = 'Marketing/Sales-Fee';
						break;
					
					default: 
						//nothing	
						break;
					
				}
				
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
			
			//update-2001.002
			$recap_sort = array();	
			$recap_sort_buyget = array();	
			
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
				'net_sales_total'	=> 0,
				'net_sales_total_show'	=> 0,
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
				'discount_total_before'	=> 0,
				'discount_total_before_show'	=> 0,
				'discount_billing_total_before'	=> 0,
				'discount_billing_total_before_show'	=> 0,
				'discount_total_after'	=> 0,
				'discount_total_after_show'	=> 0,
				'discount_billing_total_after'	=> 0,
				'discount_billing_total_after_show'	=> 0,
			);
			//NOPROMO DEFAULT -----------------------
			
			//CHECKING BILLING HAS DISCOUNT
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
					if($data_post['diskon_sebelum_pajak_service'] == 1){
						
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
						$s['sub_total'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'] - $s['discount_total'] - $s['compliment_total'];
						$s['net_sales_total'] = $s['total_billing'] - $s['discount_total'] - $s['compliment_total'];
						
						//GRAND TOTAL
						$s['grand_total'] = $s['sub_total'];
					}else{
						$s['sub_total'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'] - $s['discount_total'] - $s['compliment_total'];
						
						//update-2001.002
						$s['net_sales_total'] = $s['total_billing'] - $s['discount_total'] - $s['compliment_total'];
						
						//GRAND TOTAL
						$s['grand_total'] = $s['sub_total'];
						//$s['grand_total'] -= $s['discount_total'];
						//$s['grand_total'] -= $s['discount_billing_total'];	
						
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
					
					$s['sub_total_show'] = priceFormat($s['sub_total']);
					$s['net_sales_total_show'] = priceFormat($s['net_sales_total']);
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
					
					$get_discount_type = '-';
					$get_discount_type_var = 'no_promo';
					
					$discount_billing_total_before = 0;
					$discount_billing_total_after = 0;
					$discount_total_before = 0;
					$discount_total_after = 0;
					
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
							
						if($s['diskon_sebelum_pajak_service'] == 1){
							$discount_total_before = $s['discount_total'];
						}else{
							$discount_total_after = $s['discount_total'];
						}
					}
					
					$s['discount_billing_total_before'] = $discount_billing_total_before;
					$s['discount_billing_total_after'] = $discount_billing_total_after;
					$s['discount_total_before'] = $discount_total_before;
					$s['discount_total_after'] = $discount_total_after;
					
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
							'net_sales_total'	=> 0,
							'net_sales_total_show'	=> 0,
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
							'discount_total_before'	=> 0,
							'discount_total_before_show'	=> 0,
							'discount_billing_total_before'	=> 0,
							'discount_billing_total_before_show'	=> 0,
							'discount_total_after'	=> 0,
							'discount_total_after_show'	=> 0,
							'discount_billing_total_after'	=> 0,
							'discount_billing_total_after_show'	=> 0,
						
						);
						$newData_sort_qty[$new_var] = 0;
					}
					
					//QTY ORDER
					$billing_qty_order = 0;
					
					$all_billing_discount_id[$s['id']] = $new_var;
					
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
							'net_sales_total'	=> 0,
							'net_sales_total_show'	=> 0,
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
							'discount_total_before'	=> 0,
							'discount_total_before_show'	=> 0,
							'discount_billing_total_before'	=> 0,
							'discount_billing_total_before_show'	=> 0,
							'discount_total_after'	=> 0,
							'discount_total_after_show'	=> 0,
							'discount_billing_total_after'	=> 0,
							'discount_billing_total_after_show'	=> 0,
						
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
				$this->db->select("a.*, b.payment_date, b.billing_no, b.total_billing, b.discount_perbilling, b.payment_id, 
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
						$sub_total = 0;
						$net_sales_total = 0;
						
						
						$discount_total = 0;
						$discount_billing_total = 0;
						$discount_total_before = 0;
						$discount_total_after = 0;
						$discount_billing_total_before = 0;
						$discount_billing_total_after = 0;
						if($s['discount_perbilling'] == 1){
							$discount_billing_total = $s['discount_total'];
							
							if($s['diskon_sebelum_pajak_service'] == 1){
								$discount_billing_total_before = $s['discount_total'];
							}else{
								$discount_billing_total_after = $s['discount_total'];
							}

						}else{
							$discount_total = ($s['discount_total']);
							
							if($s['diskon_sebelum_pajak_service'] == 1){
								$discount_total_before = $s['discount_total'];
							}else{
								$discount_total_after = $s['discount_total'];
							}
						}
							
						if($has_buyget == 1){
							
							$discount_total = ($s['discount_total']);
							
							if($s['diskon_sebelum_pajak_service'] == 1){
								$discount_total_before = $s['discount_total'];
							}else{
								$discount_total_after = $s['discount_total'];
							}
						}
						
						
						if(!empty($include_tax) OR !empty($include_service)){
							
							//AUTOFIX-BUGS 1 Jan 2018
							$s['product_price_real_before'] = $s['product_price_real'];
							if((!empty($include_tax) AND empty($include_service)) OR (empty($include_tax) AND !empty($include_service))){
								if($s['product_price'] != ($s['product_price_real']+$s['tax_total']+$s['service_total'])){
									$s['product_price_real'] = priceFormat(($s['product_price']/($all_percentage/100)), 0, ".", "");
								}
							}
								
							if(!empty($s['is_compliment'])){
								//update-2003.001
								$s['product_price_real'] = $s['product_price_real_before'];
							}
							
							$total_billing_order = ($s['product_price_real']*$s['order_qty']);
							$tax_total_order = $s['tax_total'];
							$service_total_order = $s['service_total'];
							
							if($s['diskon_sebelum_pajak_service'] == 1){
								
								//$grand_total_order = ($s['product_price_real']*$s['order_qty'])- $s['discount_total'];
								
								$sub_total = ($s['product_price_real']*$s['order_qty']);
								$sub_total += $s['tax_total'];
								$sub_total += $s['service_total'];
								
								$grand_total_order = $sub_total;
								$sub_total -= $s['discount_total'];
								
							}else{
								
								//$grand_total_order = ($s['product_price_real']*$s['order_qty']);
							
								$sub_total = ($s['product_price_real']*$s['order_qty']);
								$sub_total += $s['tax_total'];
								$sub_total += $s['service_total'];
								
								$grand_total_order = $sub_total;
								//$grand_total_order -= $s['discount_total'];
								
							}
							
							//$total_billing_order = ($s['product_price_real']*$s['order_qty']);
							//$tax_total_order = $s['tax_total'];
							//$service_total_order = $s['service_total'];
							
						}else
						{
								
							$total_billing_order = ($s['product_price']*$s['order_qty']);
							$tax_total_order = $s['tax_total'];
							$service_total_order = $s['service_total'];
							
							if($s['diskon_sebelum_pajak_service'] == 1){
								
								//$grand_total_order = ($s['product_price']*$s['order_qty'])- $s['discount_total'];
								$sub_total = ($s['product_price']*$s['order_qty']);
								$sub_total += $s['tax_total'];
								$sub_total += $s['service_total'];
								
								$grand_total_order = $sub_total;
								$sub_total -= $s['discount_total'];
								
								//update-2001.002
								$net_sales_total = $total_billing_order - $s['discount_total'];
							
							}else{
								
								//$grand_total_order = ($s['product_price']*$s['order_qty']);
								$sub_total = ($s['product_price']*$s['order_qty']);
								$sub_total += $s['tax_total'];
								$sub_total += $s['service_total'];
								
								$grand_total_order = $sub_total;
								$sub_total -= $s['discount_total'];
								
								//update-2001.002
								$net_sales_total = $total_billing_order - $s['discount_total'];
							
							}
							
							//$total_billing_order = ($s['product_price']*$s['order_qty']);
							//$tax_total_order = $s['tax_total'];
							//$service_total_order = $s['service_total'];
							
						}
						
						
						//OVERRIDE PEMBULATAN PERITEM
						$total_pembulatan = 0;
						
						$grand_total_order += $total_pembulatan;
						
						
						if(!empty($s['is_compliment'])){
							$compliment_total = $grand_total_order;
							$grand_total_order -= $compliment_total;
						}
						//ALL DETAIL CALC --------------------------------------------------
					
					
						$get_disc_id = 0;
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
						
						//update-2001.002
						if($sortingDesc == 'DESC'){
							if(empty($recap_sort[$get_disc_id])){
								$recap_sort[$get_disc_id] = 0;
							}
							if($sorting == 'qty_menu'){
								$recap_sort[$get_disc_id] += $total_qty;
							}
							if($sorting == 'qty_billing'){
								$recap_sort[$get_disc_id] += 1;
							}
							if($sorting == 'total_billing'){
								$recap_sort[$get_disc_id] += $total_billing_order;
							}
							if($sorting == 'discount_total'){
								$recap_sort[$get_disc_id] += $discount_total;
							}
							if($sorting == 'discount_perbilling'){
								$recap_sort[$get_disc_id] += $discount_billing_total;
							}
							if($sorting == 'compliment_total'){
								$recap_sort[$get_disc_id] += $compliment_total;
							}
							if($sorting == 'net_sales_total'){
								$recap_sort[$get_disc_id] += $net_sales_total;
							}
							if($sorting == 'tax_total'){
								$recap_sort[$get_disc_id] += $s['tax_total'];
							}
							if($sorting == 'service_total'){
								$recap_sort[$get_disc_id] += $s['service_total']; 
							}
							if($sorting == 'pembulatan'){
								$recap_sort[$get_disc_id] += $s['billing_total_pembulatan'];
							}
							if($sorting == 'grand_total'){
								$recap_sort[$get_disc_id] += $grand_total_order;
							}
						}else{
							if($sorting == 'payment_date'){
								$recap_sort[$get_disc_id] = strtotime($s['payment_date']);
							}
							if($sorting == 'billing_no'){
								$recap_sort[$get_disc_id] = $s['billing_no'];
							}
							if($sorting == 'discount_notes'){
								$recap_sort[$get_disc_id] = $s['discount_notes'];
							}
							if($sorting == 'discount_type'){
								if(!empty($discount_billing_total)){
									$recap_sort[$get_disc_id] = 2;
								}else
								if(!empty($discount_total)){
									$recap_sort[$get_disc_id] = 1;
								}else{
									$recap_sort[$get_disc_id] = 0;
								}
							}
						}
						
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
									$newData[$new_var]['net_sales_total'] += $dtbill['net_sales_total'];
									$newData[$new_var]['total_pembulatan'] += $dtbill['total_pembulatan'];
									$newData[$new_var]['total_compliment'] += $dtbill['total_compliment'];
									$newData[$new_var]['grand_total'] += $dtbill['grand_total'];
									$newData[$new_var]['total_dp'] += $dtbill['total_dp'];
									
									$newData[$new_var]['discount_total_before'] += $dtbill['discount_total_before'];
									$newData[$new_var]['discount_total_after'] += $dtbill['discount_total_after'];
									$newData[$new_var]['discount_billing_total_before'] += $dtbill['discount_billing_total_before'];
									$newData[$new_var]['discount_billing_total_after'] += $dtbill['discount_billing_total_after'];
									
									
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
								$newData_buyget[$get_disc_id]['net_sales_total'] += $net_sales_total;
								$newData_buyget[$get_disc_id]['total_pembulatan'] += $total_pembulatan;
								$newData_buyget[$get_disc_id]['grand_total'] += $grand_total_order;
								
								$newData_buyget_sort_qty[$get_disc_id] += $total_qty;
								
								$newData_buyget[$get_disc_id]['discount_total_before'] += $discount_total_before;
								$newData_buyget[$get_disc_id]['discount_total_after'] += $discount_total_after;
								$newData_buyget[$get_disc_id]['discount_billing_total_before'] += $discount_billing_total_before;
								$newData_buyget[$get_disc_id]['discount_billing_total_after'] += $discount_billing_total_after;
								
								//update-2001.002
								if($sortingDesc == 'DESC'){
									if(empty($recap_sort_buyget[$get_disc_id])){
										$recap_sort_buyget[$get_disc_id] = 0;
									}
									if($sorting == 'qty_menu'){
										$recap_sort_buyget[$get_disc_id] += $total_qty;
									}
									if($sorting == 'qty_billing'){
										$recap_sort_buyget[$get_disc_id] += 1;
									}
									if($sorting == 'total_billing'){
										$recap_sort_buyget[$get_disc_id] += $total_billing;
									}
									if($sorting == 'discount_total'){
										$recap_sort_buyget[$get_disc_id] += $discount_total;
									}
									if($sorting == 'discount_perbilling'){
										$recap_sort_buyget[$get_disc_id] += $discount_billing_total;
									}
									if($sorting == 'compliment_total'){
										$recap_sort_buyget[$get_disc_id] += $compliment_total;
									}
									if($sorting == 'net_sales_total'){
										$recap_sort_buyget[$get_disc_id] += $net_sales_total;
									}
									if($sorting == 'tax_total'){
										$recap_sort_buyget[$get_disc_id] += $s['tax_total'];
									}
									if($sorting == 'service_total'){
										$recap_sort_buyget[$get_disc_id] += $s['service_total']; 
									}
									if($sorting == 'pembulatan'){
										$recap_sort_buyget[$get_disc_id] += $total_pembulatan;
									}
									if($sorting == 'grand_total'){
										$recap_sort_buyget[$get_disc_id] += $grand_total_order;
									}
								}else{
									if($sorting == 'payment_date'){
										$recap_sort_buyget[$get_disc_id] = strtotime($s['payment_date']);
									}
									if($sorting == 'billing_no'){
										$recap_sort_buyget[$get_disc_id] = $s['billing_no'];
									}
									if($sorting == 'discount_notes'){
										$recap_sort_buyget[$get_disc_id] = $s['discount_notes'];
									}
									if($sorting == 'discount_type'){
										if(!empty($discount_billing_total)){
											$recap_sort_buyget[$get_disc_id] = 2;
										}else
										if(!empty($discount_total)){
											$recap_sort_buyget[$get_disc_id] = 1;
										}else{
											$recap_sort_buyget[$get_disc_id] = 0;
										}
									}
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

				//sort
				//arsort($newData_sort_qty);
				
				//update-2001.002
				if($sortingDesc == 'ASC'){
					asort($recap_sort);
				}else{
					arsort($recap_sort);
				}
				
				if(!empty($recap_sort)){
					foreach($recap_sort as $disc_id => $qty){
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
							$dt['net_sales_total_show'] = priceFormat($dt['net_sales_total']);
							$dt['total_pembulatan_show'] = priceFormat($dt['total_pembulatan']);
							$dt['total_compliment_show'] = priceFormat($dt['total_compliment']);
							$dt['grand_total_show'] = priceFormat($dt['grand_total']);
							
							$dt['discount_total_before_show'] = priceFormat($dt['discount_total_before']);
							$dt['discount_billing_total_before_show'] = priceFormat($dt['discount_billing_total_before']);
							$dt['discount_total_after_show'] = priceFormat($dt['discount_total_after']);
							$dt['discount_billing_total_after_show'] = priceFormat($dt['discount_billing_total_after']);
							
							//if(!empty($dt['grand_total'])){
								$newData[] = $dt;
							//}
						}
					}
				}
				
			}
			
			//newData_buyget
			$newData_switch = $newData_buyget;
			$newData_buyget = array();
			if(!empty($newData_switch)){

				if(!empty($newData_buyget_sort_qty)){
					//sort
					//arsort($newData_buyget_sort_qty);
					
					//update-2001.002
					if($sortingDesc == 'ASC'){
						asort($recap_sort_buyget);
					}else{
						arsort($recap_sort_buyget);
					}
				
					foreach($recap_sort_buyget as $disc_id => $qty){
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
							$dt['net_sales_total_show'] = priceFormat($dt['net_sales_total']);
							$dt['total_pembulatan_show'] = priceFormat($dt['total_pembulatan']);
							$dt['total_compliment_show'] = priceFormat($dt['total_compliment']);
							$dt['grand_total_show'] = priceFormat($dt['grand_total']);
							
							$dt['discount_total_before_show'] = priceFormat($dt['discount_total_before']);
							$dt['discount_billing_total_before_show'] = priceFormat($dt['discount_billing_total_before']);
							$dt['discount_total_after_show'] = priceFormat($dt['discount_total_after']);
							$dt['discount_billing_total_after_show'] = priceFormat($dt['discount_billing_total_after']);
							
							//if(!empty($dt['grand_total'])){
								$newData_buyget[] = $dt;
							//}
						}
					}
				}

				
			}
	
			$data_post['report_data'] = $newData;
			$data_post['buyget_data'] = $newData_buyget;
			$data_post['payment_data'] = $dt_payment_name;
			//$data_post['total_hpp'] = $total_hpp;
			$data_post['display_discount_type'] = $display_discount_type;
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