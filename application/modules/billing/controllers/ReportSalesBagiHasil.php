<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ReportSalesBagiHasil extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_databilling', 'm');
		$this->load->model('model_billingdetail', 'm2');
	}
	
	public function print_reportSalesBagiHasil(){
		
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);

		if(empty($date_from)){ $date_from = date('Y-m-d'); }
		if(empty($date_till)){ $date_till = date('Y-m-d'); }
		
		if(empty($supplier_id) OR $supplier_id == 0){
			$supplier_id = -1;
		}
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'LAPORAN BAGI HASIL (DETAIL)',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'user_fullname'	=> $user_fullname,
			'session_user'	=> $session_user,
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
			
			$add_where = "(b.payment_date >= '".$qdate_from." 07:00:01' AND b.payment_date <= '".$qdate_till_max." 06:00:00')";
			
			$this->db->select("a.*, b.id as billing_id, a.updated as billing_date, c.product_name, d.item_code, e.supplier_name");
			$this->db->from($this->table2." as a");
			$this->db->join($this->table.' as b','b.id = a.billing_id','LEFT');
			$this->db->join($this->prefix.'product as c','c.id = a.product_id','LEFT');
			$this->db->join($this->prefix.'items as d','d.id = c.id_ref_item','LEFT');
			$this->db->join($this->prefix.'supplier as e','e.id = a.supplier_id','LEFT');
			$this->db->where("b.billing_status", 'paid');
			$this->db->where("b.is_deleted", 0);
			$this->db->where("a.is_deleted", 0);
			$this->db->where($add_where);
			
			if(!empty($tipe)){
				if($tipe != 'null'){
					$this->db->where("b.table_id", $tipe);
				}
			}
			
			if(!empty($supplier_id)){
				$this->db->where("a.supplier_id = '".$supplier_id."'");
			}
			
			$this->db->order_by("b.id","ASC");
			
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
			
			//echo '<pre>';
			//print_r($data_post['report_data']);
			//die();
			
			
			$supplier_name = '-';
			//GROUPING
			$dt_item = array();
			$dt_group = array();
			if(!empty($data_post['report_data'])){
				foreach($data_post['report_data'] as $dt){
					if(!in_array($dt['product_id'], $dt_item)){
						$dt_item[] = $dt['product_id'];
						
						$dt_group[$dt['product_id']] = array(
							'item_code'	=> $dt['item_code'],
							'product_name'	=> $dt['product_name'],
							'total_qty'		=> 0,
							'total_price'	=> 0,
							'total_price_toko'	=> 0,
							'total_price_supplier'	=> 0
						
						);
					}
					
					$total_price = $dt['order_qty']*$dt['product_price'];
					$total_price_toko = $dt['order_qty']*($dt['product_price']-$dt['total_bagi_hasil']);
					$total_price_supplier = ($dt['order_qty']*$dt['total_bagi_hasil']);
					
					
					$dt_group[$dt['product_id']]['total_qty'] += $dt['order_qty'];
					$dt_group[$dt['product_id']]['total_price'] += $total_price;
					$dt_group[$dt['product_id']]['total_price_toko'] += $total_price_toko;
					$dt_group[$dt['product_id']]['total_price_supplier'] += $total_price_supplier;
					
					if($supplier_name == '-'){
						$supplier_name = $dt['supplier_name'];
					}
					
				}
				
				$data_post['report_data'] = $dt_group;
			}
			
			$data_post['supplier_name'] = $supplier_name;
			
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		
		if($do == 'thermal'){
			
			$opt_value = array(
				'cashierReceipt_bagihasil_layout',
				'printer_ip_cashierReceipt_default',
				'printer_pin_cashierReceipt_default',
				'printer_tipe_cashierReceipt_default',
				'printer_id_cashierReceipt_default',
				'printer_id_cashierReceipt_'.$ip_addr
			);
			$get_opt = get_option_value($opt_value);
			
			//ID Printer ----------------------
			$printer_id_cashierReceipt = $get_opt['printer_id_cashierReceipt_default'];
			if(!empty($get_opt['printer_id_cashierReceipt_'.$ip_addr])){
				$printer_id_cashierReceipt = $get_opt['printer_id_cashierReceipt_'.$ip_addr];
			}
			
			//GET PRINTER DATA
			$this->db->from($this->prefix.'printer');		
			$this->db->where("id", $printer_id_cashierReceipt);		
			$get_printer = $this->db->get();

			$data_printer = array();
			if($get_printer->num_rows() > 0){
				$data_printer = $get_printer->row_array();
			}else{
				echo 'Printer Tidak Ditemukan!';
				die();
			}	
			
			//update -- 2018-01-23
			$printer_ip_cashierReceipt = $data_printer['printer_ip'];			
			if(strstr($printer_ip_cashierReceipt, '\\')){
				$printer_ip_cashierReceipt = "\\\\".$printer_ip_cashierReceipt;
			}	

			$printer_pin_cashierReceipt = $data_printer['printer_pin'];
			$printer_type_cashier = $data_printer['printer_tipe'];
			

			$cashierReceipt_bagihasil_layout = $get_opt['cashierReceipt_bagihasil_layout'];
			if(!empty($print_type)){
				$cashierReceipt_bagihasil_layout = $get_opt['cashierReceipt_bagihasil_layout'];
			}

			$printer_pin_cashierReceipt = trim(str_replace("CHAR", "", $printer_pin_cashierReceipt));

			$no_limit_text = false;
			if($data_printer['print_method'] == 'ESC/POS'){
				//$no_limit_text = false;
			}
			
			//trim prod name
			$max_text = 18; //44
			$max_number_1 = 9;
			$max_number_2 = 13;
			$max_number_3 = 14;

			if($printer_pin_cashierReceipt == 32){
				$max_text -= 7;
				$max_number_1 = 7;
				$max_number_2 = 9;
				$max_number_3 = 14;
			}
			if($printer_pin_cashierReceipt == 40){
				$max_text -= 4;
				$max_number_1 = 7;
				$max_number_2 = 11;
				$max_number_3 = 14;
			}
			if($printer_pin_cashierReceipt == 42){
				$max_text -= 3;
				$max_number_1 = 9;
				$max_number_2 = 13;
				$max_number_3 = 14;
			}
			if($printer_pin_cashierReceipt == 46){
				$max_text += 2;
				$max_number_1 = 9;
				$max_number_2 = 13;
				$max_number_3 = 14;
			}
			if($printer_pin_cashierReceipt == 48){
				$max_text += 4;
				$max_number_1 = 9;
				$max_number_2 = 13;
				$max_number_3 = 14;
			}
			
			$sales_data_title = "";
			$sales_data = "";
			$total_qty = 0;
			$total_sales = 0;
			$total_toko = 0;
			$total_supplier = 0;
			$all_text_array = array();
			$no = 0;
			
			if(!empty($data_post['report_data'])){
				
				foreach($data_post['report_data'] as $dt){
					
					$no++;
					$product_name = $dt['product_name'];
					
					if(strlen($product_name) > $max_text){
						//skip on last space
						$explTxt = explode(" ",$product_name);
						
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
							$product_name = substr($product_name, 0, $max_text);
						}else{
							$product_name = $all_text_array[0];
						}
					}
							
					$total_price = printer_command_align_right($dt['total_price'], $max_number_1);		
					$total_price_supplier = printer_command_align_right($dt['total_price_supplier'], $max_number_2);
					
					if(in_array($printer_pin_cashierReceipt, array(32,40)) AND $no_limit_text == false){
						$total_price = printer_command_align_right($dt['total_price'], $max_number_1);
						$total_price_supplier = printer_command_align_right($dt['total_price_supplier'], $max_number_2);
					
						if(empty($sales_data_title)){
							$sales_data_title = "[align=0]QTY[tab]ITEM[tab]".printer_command_align_right("SALES",$max_number_1)."[tab]".printer_command_align_right("SUPPLIER",$max_number_2);
							
						}
						
					}else{
						
						if(empty($sales_data_title)){
							$sales_data_title = "[align=0]QTY[tab]ITEM[tab]".printer_command_align_right("SALES",$max_number_1)."[tab]".printer_command_align_right("SUPPLIER",$max_number_2);
							
						}
					}
					
					
					
					$sales_data .= "[align=0]".$dt['total_qty']."[tab]".$product_name."[tab]".$total_price."[tab]".$total_price_supplier;
					
					foreach($all_text_array as $no_dt => $product_name_extend){
						
						if($no_dt > 0){
							$sales_data .= "\n"; 
							$sales_data .= "[align=0][tab]".$product_name_extend."[tab] [tab]";
						}
						
					}
					
					if($no < count($data_post['report_data'])){
						$sales_data .= "\n";
					}
					$total_qty +=  $dt['total_qty'];
					$total_sales +=  $dt['total_price'];
					$total_toko +=  $dt['total_price_toko'];
					$total_supplier +=  $dt['total_price_supplier'];
					
				}
			}
			
			
			$total_qty = printer_command_align_right(priceFormat($total_qty), $max_number_3);
			$total_sales = printer_command_align_right(priceFormat($total_sales), $max_number_3);
			$total_toko = printer_command_align_right(priceFormat($total_toko), $max_number_3);
			$total_supplier = printer_command_align_right(priceFormat($total_supplier), $max_number_3);
			
			$print_attr = array(
				"{tanggal_shift}"		=> date("d/m/Y"),
				"{jam_shift}"			=> date("H:i"),
				"{sales_data_title}"	=> $sales_data_title,
				"{sales_data}"			=> $sales_data,
				"{supplier_name}"		=> $supplier_name,
				"{total_qty}"			=> $total_qty,
				"{total_sales}"			=> $total_sales,
				"{total_toko}"			=> $total_toko,
				"{total_supplier}"		=> $total_supplier
			);
			
			$print_content_cashierReceipt = strtr($cashierReceipt_bagihasil_layout, $print_attr);
			
			
			$print_content = replace_to_printer_command($print_content_cashierReceipt, $printer_type_cashier, $printer_pin_cashierReceipt);
				
				
			$r = array('success' => false, 'info' => '', 'print' => array());
			
			//echo '<pre>';
			//print_r($print_content);
			//die();
			
			$r['print'][] = $print_content;
			
			//$r['print'][] = $print_content_cashierReceipt;
			//DIRECT PRINT USING PHP - CASHIER PRINTER				
			$is_print_error = false;
			
			if($data_printer['print_method'] == 'ESC/POS'){
				try {
					@$ph = printer_open($printer_ip_cashierReceipt);
				} catch (Exception $e) {
					$ph = false;
				}
				
				//$ph = @printer_open($printer_ip_cashierReceipt);
				
				if($ph)
				{	
					printer_start_doc($ph, "SALES - SUPPLIER");
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
				
				
				$data_printer['escpos_pass'] = 1;
				
				if($is_print_error){					
					$r['info'] .= 'Communication with Printer Cashier Failed!<br/>';
					echo $r['info'];
					die();
				}
			}
			
			//echo json_encode($r);
			
			printing_process($data_printer, $print_content, 'print');
			
			die();
			
		}
		
		$useview = 'print_reportSalesBagiHasil';
		$data_post['report_name'] = 'LAPORAN BAGI HASIL';
		
		if($do == 'excel'){
			$useview = 'excel_reportSalesBagiHasil';
		}
		
		$this->load->view('../../billing/views/'.$useview, $data_post);	
	}
	
	public function print_reportSalesBagiHasilRecap(){
		
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
			'supplier_name'	=> '-',
			'report_name'	=> 'LAPORAN BAGI HASIL (RECAP)',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'user_fullname'	=> $user_fullname,
			'session_user'	=> $session_user,
			'diskon_sebelum_pajak_service' => 0
		);
		
		//$data_post['supplier_name'] = $supplier_name;
		
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
				
			if(empty($date_from)){ $date_from = date('Y-m-d'); }
			if(empty($date_till)){ $date_till = date('Y-m-d'); }
			
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till = date("Y-m-d",strtotime($date_till));
			$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
			
			$add_where = "(a.payment_date >= '".$qdate_from." 07:00:01' AND a.payment_date <= '".$qdate_till_max." 06:00:00')";
			
			$this->db->select("a.*, a.id as billing_id, a.updated as billing_date, d.payment_type_name, e.bank_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT');
			$this->db->join($this->prefix.'bank as e','e.id = a.bank_id','LEFT');
			$this->db->where("a.billing_status", 'paid');
			$this->db->where("a.is_deleted", 0);
			$this->db->where($add_where);
			
			if(!empty($tipe)){
				if($tipe != 'null'){
					$this->db->where("a.table_id", $tipe);
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
					
					
					
					//$total_price = $dt['order_qty']*$dt['product_price'];
					//$total_price_toko = $dt['order_qty']*($dt['product_price']-$dt['total_bagi_hasil']);
					//$total_price_supplier = ($dt['order_qty']*$dt['total_bagi_hasil']);
					
					//REKAP TGL
					$payment_date = date("d-m-Y",strtotime($s['payment_date']));
					if(empty($all_group_date[$payment_date])){
						$all_group_date[$payment_date] = array(
							'id'		=> $no_id, 
							'item_no'	=> $no_id, 
							'date'		=> $payment_date, 
							'qty_billing'		=> 0, 
							'total_billing'		=> 0, 
							'total_price_toko'		=> 0, 
							'total_price_supplier'		=> 0, 
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
							$all_group_date[$payment_date]['total_payment_'.$key_id] = 0;
							$all_group_date[$payment_date]['total_payment_'.$key_id.'_show'] = 0;
						}
						
						$no_id++;
					}
					
					$all_bil_id_date[$s['billing_id']] = $payment_date;
					
					$s['total_price_toko'] = 0;
					$s['total_price_supplier'] = 0;
					
					$all_group_date[$payment_date]['qty_billing'] += 1;
					$all_group_date[$payment_date]['total_billing'] += $s['total_billing'];
					$all_group_date[$payment_date]['total_price_toko'] += $s['total_price_toko'];
					$all_group_date[$payment_date]['total_price_supplier'] += $s['total_price_supplier'];
					$all_group_date[$payment_date]['tax_total'] += $s['tax_total'];
					$all_group_date[$payment_date]['service_total'] += $s['service_total'];
					$all_group_date[$payment_date]['discount_total'] += $s['discount_total'];
					$all_group_date[$payment_date]['discount_billing_total'] += $s['discount_billing_total'];
					$all_group_date[$payment_date]['total_dp'] += $s['total_dp'];
					$all_group_date[$payment_date]['grand_total'] += $s['grand_total'];
					$all_group_date[$payment_date]['grand_total'] -= $s['compliment_total'];
					$all_group_date[$payment_date]['sub_total'] += $s['sub_total'];
					$all_group_date[$payment_date]['total_pembulatan'] += $s['total_pembulatan'];
					$all_group_date[$payment_date]['total_compliment'] += $s['compliment_total'];
					
					/* if(!empty($s['discount_total'])){
						echo '<pre>';
						print_r($s);
					} */
					
					if(!empty($s['is_compliment'])){
						$all_group_date[$payment_date]['total_compliment'] += $s['grand_total'];
					}else{
					
						/* if(!empty($s['is_half_payment'])){
							$all_group_date[$payment_date]['total_cash'] += $s['total_cash'];
							$all_group_date[$payment_date]['total_credit'] += $s['total_credit'];
						}else{
							if($s['payment_id'] == 1){
								//cash
								$all_group_date[$payment_date]['total_cash'] += $s['grand_total'];
							}else{
								$all_group_date[$payment_date]['total_credit'] += $s['grand_total'];
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
								$all_group_date[$payment_date]['total_payment_'.$key_id] += $tot_payment;
																
							}
						}
						
					}
				
					$newData[$s['id']] = $s;
					//array_push($newData, $s);
					
				}
			}
			
			//calc detail
			$total_hpp = array();
			$total_price_toko = array();
			$total_price_supplier = array();
					
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
						$dt = (array) $dtRow;
						$price_toko = $dt['order_qty']*($dt['product_price']-$dt['total_bagi_hasil']);
						$price_supplier = ($dt['order_qty']*$dt['total_bagi_hasil']);
						
						if(!empty($all_bil_id_date[$dtRow->billing_id])){
							$payment_date = $all_bil_id_date[$dtRow->billing_id];
							
							if(empty($total_hpp[$payment_date])){
								$total_hpp[$payment_date] = 0;
							}
							$total_hpp[$payment_date] += $dtRow->product_price_hpp * $total_qty;
							
							if(empty($total_price_toko[$payment_date])){
								$total_price_toko[$payment_date] = 0;
							}
							$total_price_toko[$payment_date] += $price_toko;
							
							if(empty($total_price_supplier[$payment_date])){
								$total_price_supplier[$payment_date] = 0;
							}
							$total_price_supplier[$payment_date] += $price_supplier;
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
					

					if(!empty($total_hpp[$key])){
						$detail['total_hpp'] = $total_hpp[$key];
					}

					if(!empty($total_price_toko[$key])){
						$detail['total_price_toko'] = $total_price_toko[$key];
					}

					if(!empty($total_price_supplier[$key])){
						$detail['total_price_supplier'] = $total_price_supplier[$key];
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
		

		$useview = 'print_reportSalesBagiHasilRecap';
		$data_post['report_name'] = 'LAPORAN BAGI HASIL (RECAP)';
		
		if($do == 'excel'){
			$useview = 'excel_reportSalesBagiHasilRecap';
		}
		
		$this->load->view('../../billing/views/'.$useview, $data_post);	
	}
	
	
	
}