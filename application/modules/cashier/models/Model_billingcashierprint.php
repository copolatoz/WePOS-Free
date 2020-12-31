<?php
class Model_BillingCashierPrint extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'billing_detail';
		$this->table_product_gramasi = $this->prefix.'product_gramasi';
		$this->table_product_package = $this->prefix.'product_package';
		$this->table_product = $this->prefix.'product';	
		$this->table_items = $this->prefix.'items';	
	}
	
	public function doPrint($is_void = '', $void_id = 0, $order_detail_id = '', $dtParams = array()){
		//header('Content-Type: text/plain; charset=utf-8');
		
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';
		$this->billing_detail_timer = $this->prefix.'billing_detail_timer';
		$this->table_print_monitoring = $this->prefix.'print_monitoring';
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		
		$tipe = $this->input->post_get('tipe', true);	
		$id = $this->input->post_get('id', true);	
		$is_html = $this->input->post_get('is_html', true);	
		$print_type = $this->input->post_get('print_type', true);	
		$printer_id = $this->input->post_get('printer_id', true);
		$initialize_printing = $this->input->post_get('initialize', true);
		$bill_preview = $this->input->post_get('bill_preview', true);
		$sendEmail = $this->input->post_get('sendEmail', true);
		
		$printer_tipe = $this->input->post_get('printer_tipe', true);	
		$do_print = $this->input->post_get('do_print', true);	
		$new_no = $this->input->post_get('new_no', true);
		$order_apps = $this->input->post_get('order_apps', true);	
		$rawbt_check = $this->input->post_get('rawbt_check', true);	
		
		//update-2008.001
		if(!empty($dtParams)){
			extract($dtParams);
		}
		
		
		if(empty($session_user) AND empty($rawbt_print)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		if(!empty($initialize_printing)){
			die();
		}
		
		if(empty($print_type)){
			$print_type = 0;
		}
		
		$r = array('success' => false);
		
		$opt_value = array(
			'use_pembulatan',
			'cashier_max_pembulatan',
			'cashier_pembulatan_keatas',
			'pembulatan_dinamis',

			'cashierReceipt_layout',
			'cashierReceipt_invoice_layout',
			'cashierReceipt_layout_footer',
			'printer_ip_cashierReceipt_default',
			'printer_pin_cashierReceipt_default',
			'printer_tipe_cashierReceipt_default',
			'printer_id_cashierReceipt_default',
			'printer_id_cashierReceipt_'.$ip_addr,
			
			'qcReceipt_layout',
			'printer_ip_qcReceipt_default',
			'printer_pin_qcReceipt_default',
			'printer_tipe_qcReceipt_default',
			'printer_id_qcReceipt_default',
			'do_print_qcReceipt_'.$ip_addr,
			'printer_id_qcReceipt_'.$ip_addr,
			
			'kitchenReceipt_layout',
			'printer_ip_kitchenReceipt_default',
			'printer_pin_kitchenReceipt_default',
			'printer_tipe_kitchenReceipt_default',
			'printer_id_kitchenReceipt_default',
			'do_print_kitchenReceipt_'.$ip_addr,
			'printer_id_kitchenReceipt_'.$ip_addr,
			
			'barReceipt_layout',
			'printer_ip_barReceipt_default',
			'printer_pin_barReceipt_default',
			'printer_tipe_barReceipt_default',
			'printer_id_barReceipt_default',
			'do_print_barReceipt_'.$ip_addr,
			'printer_id_barReceipt_'.$ip_addr,
			
			'otherReceipt_layout',
			'printer_ip_otherReceipt_default',
			'printer_pin_otherReceipt_default',
			'printer_tipe_otherReceipt_default',
			'printer_id_otherReceipt_default',
			'do_print_otherReceipt_'.$ip_addr,
			'printer_id_otherReceipt_'.$ip_addr,
			
			'print_order_peritem_kitchen',
			'print_order_peritem_bar',
			'print_order_peritem_other',
			
			'printMonitoring_qc',
			'printMonitoring_kitchen',
			'printMonitoring_bar',
			'printMonitoring_other',
			
			'order_timer',
			'produk_nama',
			'produk_expired',
			'custom_print_APS',
			'display_kode_menu_dibilling',
			'theme_print_billing',
			'print_sebaris_product_name',
			'print_preview_billing'
			
		);
		
		if($rawbt_check == 1){
			$opt_value[] = 'merchant_key';
			$opt_value[] = 'is_cloud';
		}

		$get_opt = get_option_value($opt_value);
		
		//update-2003.001
		$print_preview_billing = 0;
		if(!empty($get_opt['print_preview_billing'])){
			$print_preview_billing = $get_opt['print_preview_billing'];
		}
		
		if(!empty($order_apps)){
			$print_preview_billing = 0;
		}
		
		$custom_print_APS = 0;
		if(!empty($get_opt['custom_print_APS'])){
			$custom_print_APS = $get_opt['custom_print_APS'];
		}
		$display_kode_menu_dibilling = 0;
		if(!empty($get_opt['display_kode_menu_dibilling'])){
			$display_kode_menu_dibilling = $get_opt['display_kode_menu_dibilling'];
		}
		$theme_print_billing = 0;
		if(!empty($get_opt['theme_print_billing'])){
			$theme_print_billing = $get_opt['theme_print_billing'];
		}
		$print_sebaris_product_name = 0;
		if(!empty($get_opt['print_sebaris_product_name'])){
			$print_sebaris_product_name = $get_opt['print_sebaris_product_name'];
		}
		
		//DATA PRINTER & SETUP -- update 2019-11-24
		$cashierReceipt_layout = $get_opt['cashierReceipt_layout'];
		if(!empty($print_type)){
			$cashierReceipt_layout = $get_opt['cashierReceipt_invoice_layout'];
		}
		$cashierReceipt_layout_footer = $get_opt['cashierReceipt_layout_footer'];
		
		$qcReceipt_layout = $get_opt['qcReceipt_layout'];
		$kitchenReceipt_layout = $get_opt['kitchenReceipt_layout'];
		$barReceipt_layout = $get_opt['barReceipt_layout'];
		$otherReceipt_layout = $get_opt['otherReceipt_layout'];
		
		$print_qcReceipt = '';
		$print_kitchenReceipt = '';
		$print_barReceipt = '';
		$print_otherReceipt = '';
		
		if(!empty($get_opt['do_print_qcReceipt_'.$ip_addr])){
			$print_qcReceipt = $get_opt['do_print_qcReceipt_'.$ip_addr];
		}
		
		if(!empty($get_opt['do_print_kitchenReceipt_'.$ip_addr])){
			$print_kitchenReceipt = $get_opt['do_print_kitchenReceipt_'.$ip_addr];
		}
		
		if(!empty($get_opt['do_print_barReceipt_'.$ip_addr])){
			$print_barReceipt = $get_opt['do_print_barReceipt_'.$ip_addr];
		}
		
		if(!empty($get_opt['do_print_otherReceipt_'.$ip_addr])){
			$print_otherReceipt = $get_opt['do_print_otherReceipt_'.$ip_addr];
		}
		
		
		
		//Cashier Printer ---------------------- update -- 2018-01-24
		$all_printer_id = array();
		
		//cashierReceipt
		$printer_id_cashierReceipt = $get_opt['printer_id_cashierReceipt_default'];
		if(!empty($get_opt['printer_id_cashierReceipt_'.$ip_addr])){
			$printer_id_cashierReceipt = $get_opt['printer_id_cashierReceipt_'.$ip_addr];
		}
		
		if(!in_array($printer_id_cashierReceipt, $all_printer_id)){
			$all_printer_id[] = $printer_id_cashierReceipt;
		}
		
		//qcReceipt
		$printer_id_qcReceipt = $get_opt['printer_id_qcReceipt_default'];
		if(!empty($get_opt['printer_id_qcReceipt_'.$ip_addr])){
			$printer_id_qcReceipt = $get_opt['printer_id_qcReceipt_'.$ip_addr];
		}
		
		if(!in_array($printer_id_qcReceipt, $all_printer_id)){
			$all_printer_id[] = $printer_id_qcReceipt;
		}
		
		//kitchenReceipt
		$printer_id_kitchenReceipt = $get_opt['printer_id_kitchenReceipt_default'];
		if(!empty($get_opt['printer_id_kitchenReceipt_'.$ip_addr])){
			$printer_id_kitchenReceipt = $get_opt['printer_id_kitchenReceipt_'.$ip_addr];
		}
		
		if(!in_array($printer_id_kitchenReceipt, $all_printer_id)){
			$all_printer_id[] = $printer_id_kitchenReceipt;
		}
		
		//barReceipt
		$printer_id_barReceipt = $get_opt['printer_id_barReceipt_default'];
		if(!empty($get_opt['printer_id_barReceipt_'.$ip_addr])){
			$printer_id_barReceipt = $get_opt['printer_id_barReceipt_'.$ip_addr];
		}
		
		if(!in_array($printer_id_barReceipt, $all_printer_id)){
			$all_printer_id[] = $printer_id_barReceipt;
		}
		
		//otherReceipt
		$printer_id_otherReceipt = $get_opt['printer_id_otherReceipt_default'];
		if(!empty($get_opt['printer_id_otherReceipt_'.$ip_addr])){
			$printer_id_otherReceipt = $get_opt['printer_id_otherReceipt_'.$ip_addr];
		}
		
		if(!in_array($printer_id_otherReceipt, $all_printer_id)){
			$all_printer_id[] = $printer_id_otherReceipt;
		}
		
		
		$rawbt_printer = 0;
		$data_printer = array();
		if(!empty($all_printer_id)){
			$all_printer_id_sql = implode(",", $all_printer_id);
			$this->db->from($this->prefix.'printer');		
			$this->db->where("id IN (".$all_printer_id_sql.")");		
			$get_all_printer = $this->db->get();

			$data_printer = array();
			if($get_all_printer->num_rows() > 0){
				foreach($get_all_printer->result_array() as $dt){
					$data_printer[$dt['id']] = $dt;
					
					if($dt['print_method'] == 'RAWBT'){
						$rawbt_printer += 1;
					}
				}
			}
		}
		
		if(empty($data_printer)){
			echo 'Printer Tidak Ditemukan!';
			die();
		}
		
		//IP PRINTER --- update 2018-01-24
		$printer_ip_cashierReceipt = $data_printer[$printer_id_cashierReceipt]['printer_ip'];			
		if(strstr($printer_ip_cashierReceipt, '\\')){
			$printer_ip_cashierReceipt = "\\\\".$printer_ip_cashierReceipt;
		}
		
		$printer_ip_qcReceipt = $data_printer[$printer_id_qcReceipt]['printer_ip'];			
		if(strstr($printer_ip_qcReceipt, '\\')){
			$printer_ip_qcReceipt = "\\\\".$printer_ip_qcReceipt;
		}	
		
		$printer_ip_kitchenReceipt = $data_printer[$printer_id_kitchenReceipt]['printer_ip'];			
		if(strstr($printer_ip_kitchenReceipt, '\\')){
			$printer_ip_kitchenReceipt = "\\\\".$printer_ip_kitchenReceipt;
		}		
		
		$printer_ip_barReceipt = $data_printer[$printer_id_barReceipt]['printer_ip'];			
		if(strstr($printer_ip_barReceipt, '\\')){
			$printer_ip_barReceipt = "\\\\".$printer_ip_barReceipt;
		}			
		
		$printer_ip_otherReceipt = $data_printer[$printer_id_otherReceipt]['printer_ip'];			
		if(strstr($printer_ip_otherReceipt, '\\')){
			$printer_ip_otherReceipt = "\\\\".$printer_ip_otherReceipt;
		}	

		//PIN PRINTER --- update 2018-01-24
		$printer_pin_cashierReceipt = $get_opt['printer_pin_cashierReceipt_default'];
		if(!empty($data_printer[$printer_id_cashierReceipt]['printer_pin'])){
			$printer_pin_cashierReceipt = $data_printer[$printer_id_cashierReceipt]['printer_pin'];
		}
		
		$printer_pin_qcReceipt = $get_opt['printer_pin_qcReceipt_default'];
		if(!empty($data_printer[$printer_id_cashierReceipt]['printer_pin'])){
			$printer_pin_qcReceipt = $data_printer[$printer_id_qcReceipt]['printer_pin'];
		}
		
		$printer_pin_kitchenReceipt = $get_opt['printer_pin_kitchenReceipt_default'];
		if(!empty($data_printer[$printer_id_cashierReceipt]['printer_pin'])){
			$printer_pin_kitchenReceipt = $data_printer[$printer_id_kitchenReceipt]['printer_pin'];
		}
		
		$printer_pin_barReceipt = $get_opt['printer_pin_barReceipt_default'];
		if(!empty($data_printer[$printer_id_cashierReceipt]['printer_pin'])){
			$printer_pin_barReceipt = $data_printer[$printer_id_barReceipt]['printer_pin'];
		}
		
		$printer_pin_otherReceipt = $get_opt['printer_pin_otherReceipt_default'];
		if(!empty($data_printer[$printer_id_cashierReceipt]['printer_pin'])){
			$printer_pin_otherReceipt = $data_printer[$printer_id_otherReceipt]['printer_pin'];
		}
		
		$printer_pin_cashierReceipt = trim(str_replace("CHAR", "", $printer_pin_cashierReceipt));
		$printer_pin_qcReceipt = trim(str_replace("CHAR", "", $printer_pin_qcReceipt));
		$printer_pin_kitchenReceipt = trim(str_replace("CHAR", "", $printer_pin_kitchenReceipt));
		$printer_pin_barReceipt = trim(str_replace("CHAR", "", $printer_pin_barReceipt));
		$printer_pin_otherReceipt = trim(str_replace("CHAR", "", $printer_pin_otherReceipt));
		
		//TIPE PRINTER --- update 2018-01-24
		$printer_type_cashier = $get_opt['printer_tipe_cashierReceipt_default'];
		if(!empty($data_printer[$printer_id_cashierReceipt]['printer_tipe'])){
			$printer_type_cashier = $data_printer[$printer_id_cashierReceipt]['printer_tipe'];
		}
		
		$printer_type_qc = $get_opt['printer_tipe_qcReceipt_default'];
		if(!empty($data_printer[$printer_id_qcReceipt]['printer_tipe'])){
			$printer_type_qc = $data_printer[$printer_id_qcReceipt]['printer_tipe'];
		}
		
		$printer_type_kitchen = $get_opt['printer_tipe_kitchenReceipt_default'];
		if(!empty($data_printer[$printer_id_kitchenReceipt]['printer_tipe'])){
			$printer_type_kitchen = $data_printer[$printer_id_kitchenReceipt]['printer_tipe'];
		}
		
		$printer_type_bar = $get_opt['printer_tipe_barReceipt_default'];
		if(!empty($data_printer[$printer_id_barReceipt]['printer_tipe'])){
			$printer_type_bar = $data_printer[$printer_id_barReceipt]['printer_tipe'];
		}
		
		$printer_type_other = $get_opt['printer_tipe_otherReceipt_default'];
		if(!empty($data_printer[$printer_id_otherReceipt]['printer_tipe'])){
			$printer_type_other = $data_printer[$printer_id_otherReceipt]['printer_tipe'];
		}
		
		
		$no_limit_text = false;
		if($data_printer[$printer_id_cashierReceipt]['print_method'] == 'ESC/POS'){
			//$no_limit_text = false;
		}
		
		//printMonitoring
		$printMonitoring_qc = 0;
		if(!empty($get_opt['printMonitoring_qc'])){
			$printMonitoring_qc = $get_opt['printMonitoring_qc'];
		}
		$printMonitoring_kitchen = 0;
		if(!empty($get_opt['printMonitoring_kitchen'])){
			$printMonitoring_kitchen = $get_opt['printMonitoring_kitchen'];
		}
		$printMonitoring_bar = 0;
		if(!empty($get_opt['printMonitoring_bar'])){
			$printMonitoring_bar = $get_opt['printMonitoring_bar'];
		}
		$printMonitoring_other = 0;
		if(!empty($get_opt['printMonitoring_other'])){
			$printMonitoring_other = $get_opt['printMonitoring_other'];
		}
		
		//PRINTE ANYWHERE
		$print_anywhere = array();
		if(!empty($printer_id)){
			
			$this->db->from($this->prefix.'printer');
			$this->db->where('id', $printer_id);
			$getPrinter = $this->db->get();
			if($getPrinter->num_rows() > 0){
				$print_anywhere = $getPrinter->row();
			}
			
		}
		
		if(!empty($print_anywhere)){
			
			if(strstr($print_anywhere->printer_ip, '\\')){
				$print_anywhere->printer_ip = "\\\\".$print_anywhere->printer_ip;
			}
			
			$printer_ip_cashierReceipt = $print_anywhere->printer_ip;
			$printer_ip_qcReceipt = $print_anywhere->printer_ip;
			$printer_ip_kitchenReceipt = $print_anywhere->printer_ip;
			$printer_ip_barReceipt = $print_anywhere->printer_ip;
			$printer_ip_otherReceipt = $print_anywhere->printer_ip;
			
			$printer_pin_cashierReceipt = $print_anywhere->printer_pin;
			$printer_pin_qcReceipt = $print_anywhere->printer_pin;
			$printer_pin_kitchenReceipt = $print_anywhere->printer_pin;
			$printer_pin_barReceipt = $print_anywhere->printer_pin;
			$printer_pin_otherReceipt = $print_anywhere->printer_pin;
			
			$printer_type_cashier = $print_anywhere->printer_tipe;
			$printer_type_qc = $print_anywhere->printer_tipe;
			$printer_type_kitchen = $print_anywhere->printer_tipe;
			$printer_type_bar = $print_anywhere->printer_tipe;
			$printer_type_other = $print_anywhere->printer_tipe;
		}
		
		//die($printer_ip_qcReceipt);
		
		if(($tipe == 'payBilling' AND !empty($id)) OR (!empty($is_void) AND !empty($void_id))){
		
			if(!empty($void_id)){
				$id = $void_id;
			}
			
			if($is_void == 'void_paid_hold' OR $is_void == 'void_paid_cancel'){
				$print_type = 99;
			}
			
			$is_void_order = false; 
			if($is_void == 'void_order'){
				$print_type = -234;
				$is_void_order = true;
			}
			
			$billingData = getBilling($id);
			
			if(!empty($billingData)){
				
				//update-2008.001
				if(!empty($rawbt_printer) AND !empty($rawbt_check)){
					if(!empty($sendEmail) OR !empty($is_html) OR !empty($bill_preview)){
						
					}else{
						$r['success'] = true;
						$r['info'] = '';
						$r['rawbt_print'] = 1;
						
						$is_voidx = 0;
						if(empty($is_void)){
							$is_voidx = 0;
						}
						
						$order_detail_idx = 0;
						if(empty($order_detail_id)){
							$order_detail_idx = 0;
						}
						$r['url_print'] = BASE_URL.'cashier/rawbt/doPrint/trx-'.$billingData->billing_no.'-'.$tipe.'-'.$id.'-'.$is_voidx.'-'.$void_id.'-'.$order_detail_idx.'.txt';
						
						//update-2009.001
						if(!empty($get_opt['merchant_key']) AND !empty($get_opt['is_cloud'])){
							$r['url_print'] = BASE_URL.'cashier/rawbt/doPrint/trx-'.$billingData->billing_no.'-'.$tipe.'-'.$id.'-'.$is_voidx.'-'.$void_id.'-'.$order_detail_idx.'/'.$get_opt['merchant_key'].'.txt';
						}
						
						echo json_encode($r);
						die();
					}
				}
				
				$is_print_error = false;
				
				$this->db->select("a.*, d.table_no, a2.billing_no, a2.discount_perbilling,
								b.product_name, b.product_code, b.product_chinese_name, b.product_desc, b.product_type, b.product_image, 
								b.category_id, b.product_group, c.product_category_name,
								e.varian_name
								");
				$this->db->from($this->table2.' as a');
				$this->db->join($this->prefix.'billing as a2','a2.id = a.billing_id','LEFT');
				$this->db->join($this->prefix.'product as b','b.id = a.product_id','LEFT');
				$this->db->join($this->prefix.'product_category as c','c.id = b.category_id','LEFT');
				$this->db->join($this->prefix.'table as d','d.id = a2.table_id','LEFT');
				$this->db->join($this->prefix.'varian as e','e.id = a.varian_id','LEFT');
				//$this->db->where('a.is_deleted', 0); -- view all cancel order
				
				if($print_type == 1 OR $print_type == 0 OR $print_type == 99){
					$this->db->where('a.is_deleted', 0);
				}
				$this->db->where("a.billing_id = ".$id);
				$this->db->where("a.order_qty > 0");
				
				if(!empty($order_detail_id)){
					$this->db->where("(a.id IN (".$order_detail_id.") OR a.ref_order_id IN (".$order_detail_id."))");
				}
				
				
				$get_detail = $this->db->get();
		
				$order_data = "";	
				$order_data2 = "";	
				//update-1912-001
				$template_order_data = "";
				if(!empty($theme_print_billing)){
					if($theme_print_billing == 1){
						$template_order_data = "[list_order_tipe1]";
						//$print_sebaris_product_name = 1;
					}
					
					if($theme_print_billing == 2){
						$template_order_data = "";
						//$print_sebaris_product_name = 1;
						$no_limit_text = true;
					}
				}else{
					$template_order_data = "[set_tab1]";
				}
				
				$order_data_APS = "";
				$order_data_kitchen = array();	
				$order_data_bar = array();
				$order_data_other = array();
				
				$order_data_kitchen_peritem = array();
				$order_data_bar_peritem = array();
				$order_data_other_peritem = array();
				
				$order_data_kitchen_update = array();	
				$order_data_bar_update = array();	
				$order_data_other_update = array();	
				
				$order_data_package = array();	
				$order_data_package_item = array();	
				$order_data_free_buyget = array();	
				
				$subtotal = 0;
				$tax_total = 0;
				$service_total = 0;
				$discount_total = 0;
				$total = 0;
				
				$order_qc_id = array();
				$all_update_id_order = array();
				
				//trim prod name
				$max_text = 18; //42
				$max_number_1 = 9;
				$max_number_2 = 11;
				$max_number_3 = 13;

				if($printer_pin_cashierReceipt == 32){
					$max_text -= 6;
					$max_number_1 = 7;
					$max_number_2 = 8;
					$max_number_3 = 13;
				}
				if($printer_pin_cashierReceipt == 40){
					$max_text -= 2;
					$max_number_1 = 8;
					$max_number_2 = 11;
					$max_number_3 = 13;
				}
				if($printer_pin_cashierReceipt == 42){
					//$max_text -= 2;
					$max_number_1 = 8;
					$max_number_2 = 11;
					$max_number_3 = 13;
				}
				if($printer_pin_cashierReceipt == 46){
					$max_text += 2;
					$max_number_1 = 10;
					$max_number_2 = 12;
					$max_number_3 = 13;
				}
				if($printer_pin_cashierReceipt == 48){
					$max_text += 4;
					$max_number_1 = 10;
					$max_number_2 = 12;
					$max_number_3 = 13;
				}
				
				//use x separator 
				//update 2003.001
				$x_separator = 1;
				
				if($get_detail->num_rows() > 0){
	
					//echo '<pre>';
					//print_r($get_detail->result());
					//die();
					
					$no = 1;
					$skip_no = 0;
					foreach($get_detail->result() as $bil_det){

						$allow_QC = false;
						
						if($bil_det->product_type == 'package'){
							if(empty($order_data_package[$bil_det->id])){
								$order_data_package[$bil_det->id] = $bil_det;
							}
						}
						
						if($bil_det->is_buyget == 1){
							if(empty($order_data_free_buyget[$bil_det->id])){
								$order_data_free_buyget[$bil_det->id] = $bil_det;
							}
						}
						
						if($no > 1){
							if(($no+$skip_no) <= $get_detail->num_rows()){
								//update 2018-02-14
								if($bil_det->package_item == 0){
									$order_data .= "\n";
									$order_data2 .= "\n";
									//update-1912-001
									$template_order_data .= "\n";
									
									//custom_print_APS
									if(!empty($custom_print_APS)){
										$order_data_APS .= "\n";
									}
								}
							}
						}
						
						
						//SET ORDER DONE
						if(!in_array($bil_det->order_status, array('done','cancel'))){
							
							if($bil_det->product_group == 'food'){
								if(!in_array($bil_det->id, $order_data_kitchen_update)){
									$order_data_kitchen_update[] = $bil_det->id;
								}
							}else
							if($bil_det->product_group == 'beverage'){
								if(!in_array($bil_det->id, $order_data_bar_update)){
									$order_data_bar_update[] = $bil_det->id;
								}
							}else{
								if(!in_array($bil_det->id, $order_data_other_update)){
									$order_data_other_update[] = $bil_det->id;
								}
							}
							
							//if($bil_det->print_qc == 0){
							$order_qc_id[] = $bil_det->id;
							$allow_QC = true;
							//}
							
							
						}else{
							
							//DONE
							if($bil_det->print_qc == 0){
								
								if($bil_det->order_status == 'done'){
									$order_qc_id[] = $bil_det->id;
									$allow_QC = true;
								}else{
									//cancel other
									if($bil_det->cancel_order_notes != 'cancel order - unpaid' AND $is_void_order == true){
										$order_qc_id[] = $bil_det->id;
										$allow_QC = true;
									}
								}
								
							}
							
							
							
						}
						
						$order_notes = '';
						if(!empty($bil_det->order_notes)){
							$order_notes = " (".$bil_det->order_notes.")";
						}
						
						//varian
						$varian_name = '';
						$varian_name_2 = '';
						if(!empty($bil_det->varian_name)){
							$varian_name = " (".$bil_det->varian_name.")";
							$varian_name_2 = $bil_det->varian_name;
						}
						
						//product_chinese_name
						$product_chinese_name = '';
						if(!empty($bil_det->product_chinese_name) AND $bil_det->product_chinese_name != '-'){
							//$product_chinese_name = " / ".$bil_det->product_chinese_name."";
						}
						
						$diskon_name = '';
						if(!empty($bil_det->discount_id) AND $bil_det->discount_perbilling == 0){
							if(!empty($bil_det->discount_percentage)){
								//DISCOUNT %
								$diskon_name = 'Disc '.priceFormat($bil_det->discount_percentage, 2, ".", "").'%';
								
								if($bil_det->free_item == 1){
									$diskon_name = 'Disc/Free';
								}
								
							}else{
								if(!empty($bil_det->discount_price)){
									//DISCOUNT PRICE
									$diskon_name = 'Disc '.priceFormat($bil_det->discount_price);
								}
							}
						}
						
						//Promo
						if(!empty($bil_det->promo_id) AND $bil_det->discount_perbilling == 0){
							if(!empty($bil_det->promo_percentage)){
								//promo %
								$diskon_name = 'Promo '.priceFormat($bil_det->promo_percentage, 2, ".", "").'%';
								//$diskon_name .= ', @'.priceFormat($bil_det->promo_price);
							}else{
								if(!empty($bil_det->promo_price)){
									//promo PRICE
									//$diskon_name = 'Promo '.priceFormat($bil_det->promo_price*$bil_det->order_qty);
									$diskon_name = 'Promo '.priceFormat($bil_det->promo_price);
								}
							}
						}
						
						$takeaway_name = '';
						if(!empty($bil_det->is_takeaway)){
							$takeaway_name = " T/A";
						}
						
						$compliment_name = '';
						if(!empty($bil_det->is_compliment)){
							$compliment_name = " /COMPLIMENT";
						}
						
						//PROMO
						$promo_name = '';
						if($bil_det->is_promo == 1 AND !empty($bil_det->promo_id)){
							//$promo_name = ' Promo';
							$bil_det->product_price = $bil_det->product_price;
							$bil_det->discount_price = $bil_det->promo_price;
							$bil_det->discount_total = $bil_det->promo_price*$bil_det->order_qty;
						}
						
						
						$all_text_array = array();
						//$product_name = $bil_det->product_name.$promo_name.$product_chinese_name.$varian_name.$diskon_name.$takeaway_name.$compliment_name;
						$product_name = $bil_det->product_name.$promo_name.$product_chinese_name.$varian_name.$takeaway_name.$compliment_name;
						
						//update-1912-001
						if(!empty($display_kode_menu_dibilling)){
							$product_name = $bil_det->product_code.' '.$bil_det->product_name.$promo_name.$product_chinese_name.$varian_name.$takeaway_name.$compliment_name;
						}
						
						//update-1912-001
						if(!empty($theme_print_billing)){
							if($theme_print_billing == 1 OR $theme_print_billing == 2){
								if($printer_pin_cashierReceipt == 32){
									$max_text = 16;
									$max_number_1 = 0;
									$max_number_2 = 11;
									$max_number_3 = 13;
									
								}
								if($printer_pin_cashierReceipt == 40){
									$max_text = 24;
									$max_number_1 = 0;
									$max_number_2 = 11;
									$max_number_3 = 13;
								}
								if($printer_pin_cashierReceipt == 42){
									$max_text = 26;
									$max_number_1 = 0;
									$max_number_2 = 11;
									$max_number_3 = 13;
								}
								if($printer_pin_cashierReceipt == 46){
									$max_text = 28;
									$max_number_1 = 0;
									$max_number_2 = 13;
									$max_number_3 = 13;
								}
								if($printer_pin_cashierReceipt == 48){
									$max_text = 30;
									$max_number_1 = 0;
									$max_number_2 = 13;
									$max_number_3 = 13;
								}
							}
							
							if($theme_print_billing == 1){
								if(!empty($print_sebaris_product_name)){
									$last_text_perline = 3;
									if(strlen($product_name) >= $max_text){
										$product_name = substr($product_name,0,($max_text-$last_text_perline)).'..';
									}
								}
							}
							
							if($theme_print_billing == 2){
								$max_text = $printer_pin_cashierReceipt;
								if(!empty($print_sebaris_product_name)){
									$last_text_perline = 3;
									if(strlen($product_name) >= $printer_pin_cashierReceipt){
										$product_name = substr($product_name,0,($printer_pin_cashierReceipt-$last_text_perline)).'..';
									}
								}
							}
							
						}else{
							if(!empty($print_sebaris_product_name)){
								$last_text_perline = 3;
								if(strlen($product_name) >= $max_text){
									$product_name = substr($product_name,0,($max_text-$last_text_perline)).'..';
								}
							}
						} 
						
						//custom_print_APS
						if(!empty($custom_print_APS)){
							$product_name = $bil_det->product_name;
							
							if($printer_pin_cashierReceipt == 32){
								$max_text = 14;
								$max_text_APS = 11;
								$max_number_1 = 0;
								$max_number_2 = 13;
								$max_number_3 = 13;
								
							}
							if($printer_pin_cashierReceipt == 40){
								$max_text = 22;
								$max_text_APS = 22;
								$max_number_1 = 0;
								$max_number_2 = 13;
								$max_number_3 = 13;
							}
							if($printer_pin_cashierReceipt == 42){
								$max_text = 24;
								$max_text_APS = 24;
								$max_number_1 = 0;
								$max_number_2 = 13;
								$max_number_3 = 13;
							}
							if($printer_pin_cashierReceipt == 46){
								$max_text = 28;
								$max_text_APS = 28;
								$max_number_1 = 0;
								$max_number_2 = 13;
								$max_number_3 = 13;
							}
							if($printer_pin_cashierReceipt == 48){
								$max_text = 30;
								$max_text_APS = 30;
								$max_number_1 = 0;
								$max_number_2 = 13;
								$max_number_3 = 13;
							}
							
							$last_text = 7;
							if(strlen($product_name) > $max_text_APS){
								$product_name = substr($product_name,0,($max_text_APS-$last_text)).'..'.substr($product_name,($last_text*-1));
							}
							
							 $user_id = $this->session->userdata('id_user');
							 if(strlen($user_id) == 1){
								 $user_id = '0'.$user_id;
							 }
							 if(strlen($user_id) > 2){
								 $user_id = substr($user_id,0,2);
							 }
							 
							 $billno_APS = $billingData->billing_no;
							 
							 //LX1911.1205.000003
							 $billno_thn_bln = substr($billno_APS,0,4);
							 $billno_tgl = substr($billno_APS,4,2);
							 
							 $billno_az1 = substr($billno_APS,0,3);
							 $billno_az2 = substr($billno_APS,3,3);
							 
							 $billno_az1_div = $billno_az1%26;
							 $billno_az2_div = $billno_az2%26;
							 $billno_alp =  no2alphabet($billno_az1_div).no2alphabet($billno_az2_div);
							 
							 $billno_APS =  $billno_alp.$billno_thn_bln.'.'. $billno_tgl.$user_id.'.00'.substr($billno_APS,-4);
						}
						
						////update 2018-02-14 PACKAGE Item
						if($bil_det->package_item == 1 AND ($print_type == 1 OR $print_type == 0)){
							$bil_det->product_price = 0;
							$bil_det->product_name = '';
							$product_name = '';
						}
						
						if(strlen($product_name) >= $max_text AND $no_limit_text == false){
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
									$lnTxt = strlen($txt);
									$tot_txt += $lnTxt;
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
						
						//update-2001.002
						$order_total = $bil_det->order_qty * $bil_det->product_price;
						if(!empty($bil_det->include_tax) OR !empty($bil_det->include_service)){
							$bil_det->product_price = $bil_det->product_price_real;
							$order_total = $bil_det->order_qty * $bil_det->product_price_real;
						}	
						
						//'@'.priceFormat($bil_det->product_price)
						$product_price_show = printer_command_align_right(priceFormat($bil_det->product_price), $max_number_1);
						//$product_price_show = printer_command_align_right('@'.priceFormat($bil_det->product_price), $max_number_1);
						//$order_total_show = printer_command_align_right(priceFormat($order_total), 10);
						$order_total_show = printer_command_align_right(priceFormat($order_total), $max_number_2);
						$order_total_show_APS = printer_command_align_right(priceFormat($order_total), $max_number_2);
						
						if(in_array($printer_pin_cashierReceipt, array(32,40)) AND $no_limit_text == false){
							//'@'.$bil_det->product_price
							$product_price_show = printer_command_align_right($bil_det->product_price, $max_number_1);
							$order_total_show = printer_command_align_right($order_total, $max_number_2);
						}
						
						//update-1912-001
						if(!empty($theme_print_billing)){
							$product_price_show_theme = printer_command_align_right(priceFormat($bil_det->product_price), $max_number_1);
							$order_total_show_theme = printer_command_align_right(priceFormat($order_total), $max_number_2);
						}
						
						
						//update-1912-001
						if($bil_det->package_item == 0){
							$order_data .= "[align=0]".$bil_det->order_qty."[tab]".$product_name."[tab]".$product_price_show."[tab]".$order_total_show;
							$order_data2 .= "[align=0]".$bil_det->order_qty."[tab]".$product_name."[tab]".$order_total_show;
							
							if(!empty($theme_print_billing)){
								if($theme_print_billing == 2){
									$template_order_data .= "[clear_set_tab][align=0]".$product_name."\n";
									//update-2003.001
									if(strlen($bil_det->order_qty) == 4){
										$x_separator = 0;
										$template_order_data .= "[list_order_tipe2][align=0][tab]".$bil_det->order_qty."x".$product_price_show_theme."[tab]".$order_total_show_theme;
									}else
									if(strlen($bil_det->order_qty) == 1){
										$template_order_data .= "[list_order_tipe2][align=0][tab]".$bil_det->order_qty."  x ".$product_price_show_theme."[tab]".$order_total_show_theme;
									}else{
										$template_order_data .= "[list_order_tipe2][align=0][tab]".$bil_det->order_qty." x ".$product_price_show_theme."[tab]".$order_total_show_theme;
									}
								}else{
									//update-2003.001
									if(strlen($bil_det->order_qty) == 4){
										$x_separator = 0;
										$template_order_data .= "[align=0]".$bil_det->order_qty."[tab]".$product_name."[tab]".$order_total_show_theme;
									}else
									if(strlen($bil_det->order_qty) == 1){
										$template_order_data .= "[align=0]".$bil_det->order_qty."  x[tab]".$product_name."[tab]".$order_total_show_theme;
									}else{
										$template_order_data .= "[align=0]".$bil_det->order_qty." x[tab]".$product_name."[tab]".$order_total_show_theme;
									}
								}
							}else{
								$template_order_data .= "[align=0]".$bil_det->order_qty."[tab]".$product_name."[tab]".$product_price_show."[tab]".$order_total_show;
							}
							
							//custom_print_APS
							if(!empty($custom_print_APS)){
								//update-2003.001
								if(strlen($bil_det->order_qty) == 4){
									$x_separator = 0;
									$order_data_APS .= "[align=0]".$bil_det->order_qty."[tab]".$product_name."[tab]".$order_total_show_APS;
								}else
								if(strlen($bil_det->order_qty) == 1){
									$order_data_APS .= "[align=0]".$bil_det->order_qty."  x[tab]".$product_name."[tab]".$order_total_show_APS;
								}else{
									$order_data_APS .= "[align=0]".$bil_det->order_qty." x[tab]".$product_name."[tab]".$order_total_show_APS;
								}
							}
							
						}
						
						$product_name_package = '';
						if(!empty($order_data_package[$bil_det->ref_order_id]) AND $bil_det->package_item == 1){
							$product_name_package = $order_data_package[$bil_det->ref_order_id]->product_name.' / ';
							if(empty($order_data_package_item[$bil_det->ref_order_id])){
								$order_data_package_item[$bil_det->ref_order_id] = array();
							}
							$order_data_package_item[$bil_det->ref_order_id][] = $bil_det->id;
						}
						
						$product_name_free_buyget = '';
						if(!empty($order_data_free_buyget[$bil_det->ref_order_id])){
							$product_name_free_buyget = ' (Free)';
						}
						
						//not substr $bil_det->product_name for kitchen and bar
						if($bil_det->product_group == 'food' AND $bil_det->product_type == 'item'){
							
							//khusus cancel order
							if($is_void_order){
								
								if(empty($order_data_kitchen[$bil_det->id])){
									$order_data_kitchen[$bil_det->id] = '';
								}
								
								if(empty($cancel_order_kitchen_text)){
									$cancel_order_kitchen_text = "[size=1]CANCEL ORDER[tab] \n[size=0]";
									$order_data_kitchen[$bil_det->id] .= "[size=1]CANCEL ORDER[tab] \n[size=0]";
								}else{
									$order_data_kitchen[$bil_det->id] .= "[size=0]";
								}
								
								$order_data_kitchen[$bil_det->id] .= $bil_det->order_qty."[tab]".$product_name_package.$bil_det->product_name.$product_name_free_buyget.$product_chinese_name.$varian_name.$takeaway_name.$order_notes."\n";
								
								//PER-ITEM KITCHEN
								$order_data_kitchen_peritem_format = "[size=1][align=1]CANCEL ORDER\n";
								$order_data_kitchen_peritem_format .= "[size=1][align=1]".$product_name_package.$bil_det->product_name.$product_name_free_buyget.$product_chinese_name."\n";
								$order_data_kitchen_peritem_format .= "[size=1][align=1]".$bil_det->order_qty." X ".$varian_name_2."\n";
								$order_data_kitchen_peritem_format .= "[size=0][align=1]".$takeaway_name.$order_notes."\n";
								$order_data_kitchen_peritem[$bil_det->id] = $order_data_kitchen_peritem_format;
								$order_data_kitchen_update[] = $bil_det->id;
								
							}else{
								if((!empty($order_data_kitchen_update) AND in_array($bil_det->id, $order_data_kitchen_update)) OR $allow_QC == true){
									
									//if(empty($order_data_kitchen)){
									//	$order_data_kitchen .= "KITCHEN[tab] \n";
									//}
									$order_data_kitchen[$bil_det->id] = $bil_det->order_qty."[tab]".$product_name_package.$bil_det->product_name.$product_name_free_buyget.$product_chinese_name.$varian_name.$takeaway_name.$order_notes."\n";
									
									//PER-ITEM KITCHEN
									$order_data_kitchen_peritem_format = "[size=1][align=1]".$product_name_package.$bil_det->product_name.$product_name_free_buyget.$product_chinese_name."\n";
									$order_data_kitchen_peritem_format .= "[size=1][align=1]".$bil_det->order_qty." X ".$varian_name_2."\n";
									$order_data_kitchen_peritem_format .= "[size=0][align=1]".$takeaway_name.$order_notes."\n";
									$order_data_kitchen_peritem[$bil_det->id] = $order_data_kitchen_peritem_format;
									
								}
							}
							
							
						}else
						if($bil_det->product_group == 'beverage' AND $bil_det->product_type == 'item'){
							
							//khusus cancel order
							if($is_void_order){
								
								if(empty($order_data_bar[$bil_det->id])){
									$order_data_bar[$bil_det->id] = '';
									
								}
								
								if(empty($cancel_order_bar_text)){
									$cancel_order_bar_text = "[size=1]CANCEL ORDER[tab] \n[size=0]";
									$order_data_bar[$bil_det->id] .= "[size=1]CANCEL ORDER[tab] \n[size=0]";
								}else{
									$order_data_bar[$bil_det->id] .= "[size=0]";
								}
								
								$order_data_bar[$bil_det->id] .= $bil_det->order_qty."[tab]".$product_name_package.$bil_det->product_name.$product_name_free_buyget.$product_chinese_name.$varian_name.$takeaway_name.$order_notes."\n";
								
								//PER-ITEM BAR
								$order_data_bar_peritem_format = "[size=1][align=1]CANCEL ORDER\n";
								$order_data_bar_peritem_format .= "[size=1][align=1]".$product_name_package.$bil_det->product_name.$product_name_free_buyget.$product_chinese_name."\n";
								$order_data_bar_peritem_format .= "[size=1][align=1]".$bil_det->order_qty." X ".$varian_name_2."\n";
								$order_data_bar_peritem_format .= "[size=0][align=1]".$takeaway_name.$order_notes."\n";
								$order_data_bar_peritem[$bil_det->id] = $order_data_bar_peritem_format;
								$order_data_bar_update[] = $bil_det->id;
								
							}else{
								if((!empty($order_data_bar_update) AND in_array($bil_det->id, $order_data_bar_update)) OR $allow_QC == true){
									
									//if(empty($order_data_bar)){
									//	$order_data_bar .= "BAR[tab] \n";
									//}
									$order_data_bar[$bil_det->id] = $bil_det->order_qty."[tab]".$product_name_package.$bil_det->product_name.$product_name_free_buyget.$product_chinese_name.$varian_name.$takeaway_name.$order_notes."\n";
									
									//PER-ITEM BAR
									$order_data_bar_peritem_format = "[size=1][align=1]".$product_name_package.$bil_det->product_name.$product_name_free_buyget.$product_chinese_name."\n";
									$order_data_bar_peritem_format .= "[size=1][align=1]".$bil_det->order_qty." X ".$varian_name_2."\n";
									$order_data_bar_peritem_format .= "[size=0][align=1]".$takeaway_name.$order_notes."\n";
									$order_data_bar_peritem[$bil_det->id] = $order_data_bar_peritem_format;
									
								}
							}
							
							
						}else
						{
							if($bil_det->product_type == 'item'){
								if($is_void_order){
									if(empty($order_data_other[$bil_det->id])){
										$order_data_other[$bil_det->id] = '';
									}
								
									if(empty($cancel_order_other_text)){
										$cancel_order_other_text = "[size=1]CANCEL ORDER[tab] \n[size=0]";
										$order_data_other[$bil_det->id] .= "[size=1]CANCEL ORDER[tab] \n[size=0]";
									}else{
										$order_data_other[$bil_det->id] .= "[size=0]";
									}
									
									$order_data_other[$bil_det->id] .= $bil_det->order_qty."[tab]".$product_name_package.$bil_det->product_name.$product_name_free_buyget.$product_chinese_name.$varian_name.$takeaway_name.$order_notes."\n";
									
									//PER-ITEM OTHER
									$order_data_other_peritem_format = "[size=1][align=1]CANCEL ORDER\n";
									$order_data_other_peritem_format .= "[size=1][align=1]".$product_name_package.$bil_det->product_name.$product_name_free_buyget.$product_chinese_name."\n";
									$order_data_other_peritem_format .= "[size=1][align=1]".$bil_det->order_qty." X ".$varian_name_2."\n";
									$order_data_other_peritem_format .= "[size=0][align=1]".$takeaway_name.$order_notes."\n";
									$order_data_other_peritem[$bil_det->id] = $order_data_other_peritem_format;
									
								}else{
									if((!empty($order_data_other_update) AND in_array($bil_det->id, $order_data_other_update)) OR $allow_QC == true){
										
										//if(empty($order_data_other)){
										//	$order_data_other .= "OTHER[tab] \n";
										//}
										$order_data_other[$bil_det->id] = $bil_det->order_qty."[tab]".$product_name_package.$bil_det->product_name.$product_name_free_buyget.$product_chinese_name.$varian_name.$takeaway_name.$order_notes."\n";
										
										//PER-ITEM OTHER
										$order_data_other_peritem_format = "[size=1][align=1]".$product_name_package.$bil_det->product_name.$product_name_free_buyget.$product_chinese_name."\n";
										$order_data_other_peritem_format .= "[size=1][align=1]".$bil_det->order_qty." X ".$varian_name_2."\n";
										$order_data_other_peritem_format .= "[size=0][align=1]".$takeaway_name.$order_notes."\n";
										$order_data_other_peritem[$bil_det->id] = $order_data_other_peritem_format;
										
									}
								}
							}
							
						}
						
						//echo '<pre>';
						//print_r($all_text_array);
						
						//other text - continue 
						foreach($all_text_array as $no_dt => $product_name_extend){
						
							if($no_dt > 0){
								
								$order_data .= "\n"; 
								$order_data .= "[align=0][tab]".$product_name_extend."[tab] [tab]";
								
								$order_data2 .= "\n"; 
								$order_data2 .= "[align=0][tab]".$product_name_extend."[tab]";
								
								//update-1912-001
								if(!empty($theme_print_billing)){
									$template_order_data .= "\n"; 
									$template_order_data .= "[align=0][tab]".$product_name_extend."[tab]";
								}else{
									$template_order_data .= "\n"; 
									$template_order_data .= "[align=0][tab]".$product_name_extend."[tab] [tab]";
								}
								
								//custom_print_APS
								if(!empty($custom_print_APS)){
									$order_data_APS .= "\n"; 
									$order_data_APS .= "[align=0][tab]".$product_name_extend."[tab]";
								}
								
								if($bil_det->product_group == 'beverage'){
									//$order_data_bar .= "[tab]".$product_name_extend."\n";
								}else{
									//$order_data_kitchen .= "[tab]".$product_name_extend."\n";
								}
							}
							
						}
						
						//NEW DISC
						if(!empty($diskon_name)){
							
							if($bil_det->free_item == 1){
								$bil_det->discount_total = $order_total;
							}
							
							$discount_total_print = printer_command_align_right(priceFormat($bil_det->discount_total*-1), $max_number_2);
						
							if(in_array($printer_pin_cashierReceipt, array(32,40))){
								$discount_total_print = printer_command_align_right(($bil_det->discount_total*-1), $max_number_2);
							}
							
							//update-1912-001
							$discount_total_print_Theme = printer_command_align_right(priceFormat($bil_det->discount_total*-1), $max_number_2);
							$discount_total_print_APS = printer_command_align_right(priceFormat($bil_det->discount_total*-1), $max_number_2);
							$discount_price_show_theme = printer_command_align_right(priceFormat($bil_det->discount_price*-1), $max_number_1);
									

							//update-1912-001
							$order_data .= "\n"."[align=0] [tab]".$diskon_name."[tab]".($bil_det->discount_price*-1)."[tab]".$discount_total_print;
							$order_data2 .= "\n"."[align=0] [tab]".$diskon_name."[tab]".$discount_total_print;
							
							//update-1912-001
							if(!empty($theme_print_billing)){
								if($printer_pin_cashierReceipt >= 42){
									$template_order_data .= "\n"."[align=0][tab]".$diskon_name." @".$discount_price_show_theme."[tab]".$discount_total_print_Theme;
								}else{
									$template_order_data .= "\n"."[align=0][tab]".$diskon_name."[tab]".$discount_total_print_Theme;
								}
							}else{
								$template_order_data .= "\n"."[align=0] [tab]".$diskon_name."[tab]".$discount_price_show_theme."[tab]".$discount_total_print;
							}
							
							//custom_print_APS
							if(!empty($custom_print_APS)){
								$order_data_APS .= "\n"."[align=0][tab]".$diskon_name."[tab]".$discount_total_print_APS;
							}
							
						}
						
						$subtotal += $order_total;
						$tax_total += $bil_det->tax_total;
						$service_total += $bil_det->service_total;
						$discount_total += $bil_det->discount_total;
						//$total += $subtotal;
						
						if($bil_det->package_item == 0){
							$no++;
						}else{
							$skip_no++;
						}
						
					}				
				}
				
				
				$total = $subtotal + $tax_total + $service_total;
				if(!empty($billingData->include_tax) OR !empty($billingData->include_service)){
					$total = $subtotal;
				}
				
				if($billingData->discount_perbilling == 1){
					$discount_total = $billingData->discount_total;
				}
				
				$single_rate_txt = '';
				if($billingData->single_rate == 1){
					$discount_total = 0;
					$single_rate_txt = '-S';
				}
				
				$total = $total - $discount_total;
				
				$total_dp = 0;
				if(!empty($billingData->total_dp)){
					$total_dp = $billingData->total_dp;
				}
				$total = $total - $total_dp;
				
				if($total <= 0){
					$total = 0;
				}
				
				//PEMBULATAN
				//update.2003-001		
				$data_pembulatan = array(
					'total' 					=> $total,
					'cashier_max_pembulatan' 	=> $get_opt['cashier_max_pembulatan'],
					'cashier_pembulatan_keatas' => $get_opt['cashier_pembulatan_keatas'],
					'pembulatan_dinamis' 		=> $get_opt['pembulatan_dinamis'],
					'use_pembulatan' 			=> $get_opt['use_pembulatan'],
				);
				$total_pembulatan = hitungPembulatan($data_pembulatan);	
									
				$pembulatan_show = priceFormat($total_pembulatan);
				
				
				if($total_pembulatan < 0){
					$pembulatan_show = "(".$pembulatan_show.")";
				}
				
				//$grand_total = $total + $total_pembulatan;
				
				if($billingData->single_rate == 1){
					$billingData->total_paid += $billingData->discount_total;
					$billingData->grand_total += $billingData->discount_total;
				}
				
				
				$cash = $billingData->total_paid;
				//$return = $cash - $grand_total;
				
				$grand_total = $billingData->grand_total;
				$return = $billingData->total_return;
				$compliment_total = $billingData->compliment_total_tax_service;
								
				$subtotal_show = printer_command_align_right(priceFormat($subtotal), $max_number_3);
				$total_show = printer_command_align_right(priceFormat($total), $max_number_3);
				$tax_total_show = printer_command_align_right(priceFormat($tax_total), $max_number_3);
				$service_total_show = printer_command_align_right(priceFormat($service_total), $max_number_3);
				$pembulatan_show = printer_command_align_right($pembulatan_show, $max_number_3);
				$grand_total_show = printer_command_align_right(priceFormat($grand_total), $max_number_3);
				$cash_show = printer_command_align_right(priceFormat($cash), $max_number_3);
				$return_show = printer_command_align_right(priceFormat($return), $max_number_3);
				$compliment_total_show = printer_command_align_right(priceFormat($compliment_total), $max_number_3);
				
				//PENGURANG-------------
				$discount_total_show = 0;
				if($discount_total > 0){
					$discount_total_show = '('.priceFormat($discount_total).')';
				}
				$discount_total_show = printer_command_align_right($discount_total_show, $max_number_3);
				
				$total_dp_show = 0;
				if($total_dp > 0){
					$total_dp_show = '('.priceFormat($total_dp).')';
					//$total_dp_show = "\n[tab]DP[tab]".$total_dp_show;
				}
				$total_dp_show = printer_command_align_right($total_dp_show, $max_number_3);
				
				
				$payment_type_show = '-';
				if(!empty($billingData->payment_type_name)){
					$payment_type_show = $billingData->payment_type_name;
					if($payment_type_show == 'Cash'){
						$payment_type_show .= '/Tunai';
					}
				}
				
				if(!empty($billingData->bank_name) AND $billingData->payment_type_name != 'Cash'){
					$payment_type_show = $billingData->bank_name;
				}
				
				//update-2001.002
				$is_half_payment = $billingData->is_half_payment;
				if(!empty($is_half_payment)){
					
					$total_cash_show = priceFormat($billingData->total_cash);
					$total_credit_show = priceFormat($billingData->total_credit);
					//$half_payment_show = "";
					//$half_payment_show .= '[tab]Cash/Tunai[tab]'.$total_cash_show."\n";
					//$half_payment_show .= '[tab]'.$payment_type_show.'[tab]'.$total_credit_show."\n";
					$half_payment_show = "[align=0] Half-Payment\n";
					$half_payment_show .= "[align=0] Cash/Tunai: ".$total_cash_show."\n";
					$half_payment_show .= "[align=0] ".$payment_type_show.": ".$total_credit_show;
					$payment_type_show = $half_payment_show;
					
					//card_no
					if(!empty($billingData->card_no)){
						$payment_type_show .= "\n";
						$payment_type_show .= "[align=0] No/Trx: ".$billingData->card_no;
					}
					
				}else{
					$payment_type_show = $payment_type_show;
					
					if(!empty($billingData->bank_name) AND $billingData->payment_type_name != 'Cash'){
						
						//card_no
						if(!empty($billingData->card_no)){
							$payment_type_show .= "\n";
							$payment_type_show .= "[align=0]No/Trx: ".$billingData->card_no;
						}
						
					}
				}
				
				//table no
				$table_no_receipt = $billingData->table_no;
				$table_no_title = 'MEJA:';
				if(strstr($cashierReceipt_layout,'{table_no=')){
					$exp_tableno = explode('{table_no=', $cashierReceipt_layout);
					if(!empty($exp_tableno[1])){
						$exp_tableno2 = explode('}', $exp_tableno[1]);
						if(!empty($exp_tableno2[0])){
							$table_no_title = $exp_tableno2[0];
							$table_no_title = str_replace('"',"",$table_no_title);
							$table_no_title = str_replace('\'',"",$table_no_title);
						}
					}
					$cashierReceipt_layout = str_replace('{table_no='.$table_no_title.'}',"{table_no}",$cashierReceipt_layout);
					$cashierReceipt_layout = str_replace('{table_no="'.$table_no_title.'"}',"{table_no}",$cashierReceipt_layout);
					$cashierReceipt_layout = str_replace('{table_no=\''.$table_no_title.'\'}',"{table_no}",$cashierReceipt_layout);
				}
				$table_no_receipt = $table_no_title.$billingData->table_no;
				$table_no_receipt = printer_command_align_right($table_no_receipt, 15);
				
				if(!empty($new_no)){
					$billingData->billing_no = $new_no;
				}
				
				//update-2008.001
				if(!empty($billingData->txmark_no)){
					$billingData->billing_no = $billingData->txmark_no;
				}
				
				//$billingData->billing_no
				$billing_no_receipt = $billingData->billing_no;
				$billing_no_title = 'NO:';
				if(strstr($cashierReceipt_layout,'{billing_no=')){
					$exp_billingno = explode('{billing_no=', $cashierReceipt_layout);
					if(!empty($exp_billingno[1])){
						$exp_billingno2 = explode('}', $exp_billingno[1]);
						if(!empty($exp_billingno2[0])){
							$billing_no_title = $exp_billingno2[0];
							$billing_no_title = str_replace('"',"",$billing_no_title);
							$billing_no_title = str_replace('\'',"",$billing_no_title);
						}
					}
					$cashierReceipt_layout = str_replace('{billing_no='.$billing_no_title.'}',"{billing_no}",$cashierReceipt_layout);
					$cashierReceipt_layout = str_replace('{billing_no="'.$billing_no_title.'"}',"{billing_no}",$cashierReceipt_layout);
					$cashierReceipt_layout = str_replace('{billing_no=\''.$billing_no_title.'\'}',"{billing_no}",$cashierReceipt_layout);
				}
				$billing_no_receipt = $billing_no_title.$billingData->billing_no;
				
				//update-2008.001
				if(!empty($billingData->txmark_no)){
					$billing_no_receipt = str_replace($billing_no_title,"TRX-",$billing_no_receipt);
				}
				$billing_no_APS = str_replace($billing_no_title,"",$billing_no_receipt);
				
				//custom_print_APS
				if(!empty($custom_print_APS)){
					$billing_no_APS = $billno_APS;
				}
				
				if(empty($grand_total)){
					$grand_total_show = '.0';
				}
				
				$total_paid = $cash;
				$total_paid_show = $cash_show;
				if(empty($total_paid)){
					$total_paid_show = '.0';
					if($billingData->billing_status == 'paid'){
						$payment_type_show = "Free / Compliment";
					}else{
						$payment_type_show = "[set_tab2]";
					}
					
				}
				//$payment_type_show .= "\n";
				
				$customer_show = '';
				$customer_code_show = '';
				if(!empty($billingData->customer_id)){
					$customer_show .= $billingData->customer_name;
					$customer_code_show .= $billingData->customer_code;
				}
				
				//update-2003.001
				if($x_separator == 0){
					$template_order_data = str_replace("x[tab]"," [tab]", $template_order_data);
					$order_data_APS = str_replace("x[tab]"," [tab]", $order_data_APS);
				}
				
				$print_attr = array(
					"{date}"	=> date("d/m/Y"),
					"{date_time}"	=> date("d/m/Y H:i"),
					"{date_time_APS}"	=> date("H:i, d/m/y"),
					"{date_time_full}"	=> date("Y-m-d H:i:s"),
					"{user}"	=> $session_user,
					"{table_no}"	=> $table_no_receipt,
					"{billing_no}"	=> $billing_no_receipt,
					"{billing_no_APS}"	=> $billing_no_APS,
					"{order_data}"	=> $order_data,
					"{order_data2}"	=> $order_data2,
					"{template_order_data}"	=> $template_order_data,
					"{order_data_APS}"	=> $order_data_APS,
					"{subtotal}"	=> $subtotal_show,
					//"{additional_total}" => $additional_total,
					"{tax_total}" => $tax_total_show,
					"{service_total}" => $service_total_show,
					"{total}"	=> $total_show,
					"{rounded}"	=> $pembulatan_show,
					"{pembulatan}"	=> $pembulatan_show,
					"{potongan}"	=> $discount_total_show,
					"{grand_total}"	=> $grand_total_show,
					"{cash}"	=> $cash_show,
					"{total_paid}"	=> $total_paid_show,
					"{return}"	=> $return_show,
					"{payment_type}"=> $payment_type_show,
					"{customer}"=> $customer_show,
					"{customer_code}"=> $customer_code_show,
					"{dp_total}"=> $total_dp_show,
					"{notes}"=> $billingData->billing_notes,
					"{guest}"=> $billingData->total_guest,
					"{compliment}"=> $compliment_total_show
				);
				
				//DATE PAID
				if($billingData->billing_status == 'paid'){
					$print_attr['{date}'] = date("d/m/Y",strtotime($billingData->payment_date));
					$print_attr['{date_time}'] = date("d/m/Y H:i",strtotime($billingData->payment_date));
					$print_attr['{user}'] = $billingData->updatedby;
				}
				
				if(!empty($single_rate_txt)){
					$print_attr["{billing_no}"] = $billing_no_receipt.$single_rate_txt;
				}
				if(!empty($is_void)){
					$print_attr["{billing_no}"] = $billing_no_receipt.' (VOID)';
				}
				
				if($tax_total == 0){
					$cashierReceipt_layout = empty_value_printer_text($cashierReceipt_layout, '{tax_total}');
				}
				
				//update-1912-001
				if($service_total == 0){
					$cashierReceipt_layout = empty_value_printer_text($cashierReceipt_layout, '{service_total}');
				}
				
				if($discount_total == 0){
					$cashierReceipt_layout = empty_value_printer_text($cashierReceipt_layout, '{potongan}');
				}
				
				if($compliment_total == 0){
					$cashierReceipt_layout = empty_value_printer_text($cashierReceipt_layout, '{compliment}');
				}
				
				if($total_dp == 0){
					$cashierReceipt_layout = empty_value_printer_text($cashierReceipt_layout, '{dp_total}');
				}
				
				if($total_pembulatan == 0){
					$cashierReceipt_layout = empty_value_printer_text($cashierReceipt_layout, '{pembulatan}');
				}
				
				if($total_pembulatan == 0){
					$cashierReceipt_layout = empty_value_printer_text($cashierReceipt_layout, '{rounded}');
				}
				
				if($return == 0){
					$cashierReceipt_layout = empty_value_printer_text($cashierReceipt_layout, '{return}');
				}
				
				if(empty($billingData->customer_id)){
					$cashierReceipt_layout = empty_value_printer_text($cashierReceipt_layout, '{customer}');
				}
				
				if(empty($billingData->customer_id)){
					$cashierReceipt_layout = empty_value_printer_text($cashierReceipt_layout, '{customer_code}');
				}
				
				$cashierReceipt_layout = str_replace("{hide_empty}","", $cashierReceipt_layout);
				
				$cashierReceipt_layout .= $cashierReceipt_layout_footer;
				
				if(!empty($get_opt['produk_nama']) AND !empty($get_opt['produk_expired'])){
					$produk_nama_spell = array('G','r','a','t','i','s',' / ','F','r','e','e');
					$produk_nama_spell_imp = implode("",$produk_nama_spell);
					if($get_opt['produk_nama'] == $produk_nama_spell_imp AND $get_opt['produk_expired'] == 'unlimited'){
						$str1 = 'U3VwcG9y'; $str2 = 'dGVkIEJ5IF'; $str3 = 'dlUE9TLmlk';
						$extra_watermark = "\n[align=1]".base64_decode($str1.$str2.$str3);
						$cashierReceipt_layout .= $extra_watermark;
					}
				}
				
				$print_content_cashierReceipt = strtr($cashierReceipt_layout, $print_attr);
				$print_content_cashierReceipt_monitoring = strtr($cashierReceipt_layout, $print_attr);
				
				$print_content = replace_to_printer_command($print_content_cashierReceipt, $printer_type_cashier, $printer_pin_cashierReceipt);
				
				$r = array('success' => false, 'info' => '', 'print' => array());
									
				if($print_type == 1 OR $print_type == 0 OR $print_type == 99){
					$r['print'][] = $print_content;
					//DIRECT PRINT USING PHP - CASHIER PRINTER				
					$is_print_error = false;
					
					//SAVE to Print Monitoring
					$data_printMonitoring = array(
						'tipe'			=> 'billing',
						'peritem'		=> '0',
						'print_date'	=> date("Y-m-d"),
						'print_datetime'=> date("Y-m-d H:i:s"),
						'user'			=> $session_user,
						'table_no'		=> $billingData->table_no,
						'billing_no'	=> $billingData->billing_no,
						'receiptTxt'	=> $print_content_cashierReceipt_monitoring,
						'printer'		=> $printer_ip_cashierReceipt,
						'tipe_printer'	=> $printer_type_cashier,
						'tipe_pin'		=> $printer_pin_cashierReceipt,
						'status_print'	=> 1
					);
					//$this->db->insert($this->table_print_monitoring, $data_printMonitoring);
					
					$monitoring_id = 0;
					if(!empty($sendEmail)){
						$data_printMonitoring['tipe'] = 'email';
						$this->db->insert($this->table_print_monitoring, $data_printMonitoring);
						$monitoring_id = $this->db->insert_id();
					}else{
						if(!empty($bill_preview)){
							$data_printer[$printer_id_cashierReceipt]['print_method'] = 'BROWSER';
						}else{
							$this->db->insert($this->table_print_monitoring, $data_printMonitoring);
							$monitoring_id = $this->db->insert_id();
						}
					}
					
					if($data_printer[$printer_id_cashierReceipt]['print_method'] == 'ESC/POS'){
						try {
							@$ph = printer_open($printer_ip_cashierReceipt);
						} catch (Exception $e) {
							$ph = false;
						}
						
						//$ph = @printer_open($printer_ip_cashierReceipt);
						
						if($ph)
						{	
							printer_start_doc($ph, "CASHIER RECEIPT - PAYMENT");
							printer_start_page($ph);
							printer_set_option($ph, PRINTER_MODE, "RAW");
							printer_write($ph, $print_content);
							printer_end_page($ph);
							printer_end_doc($ph);
							printer_close($ph);
							$r['success'] = true;
							
						}else{
							$is_print_error = true;
						}
						
						$data_printer[$printer_id_cashierReceipt]['escpos_pass'] = 1;
						
						if($is_print_error){					
							$r['info'] .= 'Komunikasi dengan Printer Kasir Gagal!<br/>';
							$r['success'] = false;
							if($print_preview_billing == 0){
								//echo json_encode($r);
							}else{
								//printing_process_error($r['info']);
							}
							//die();
						}
					}
					
					//update-2008.001
					if($data_printer[$printer_id_cashierReceipt]['print_method'] == 'RAWBT' AND !empty($rawbt_print)){
						echo $print_content;
						die();
					}
					
					
					$custom_print_data = '';
					
					//update-1912-001
					if(!empty($theme_print_billing)){
						$custom_print_data = 'theme'.$theme_print_billing;
					}
					if(!empty($custom_print_APS)){
						$custom_print_data = 'APS';
					}
					
					//update-2003.001
					if($is_print_error == false){
						if(!empty($print_preview_billing)){
							if(!empty($sendEmail)){
								//$ret_print = printing_process($data_printer[$printer_id_cashierReceipt], $print_content_cashierReceipt,'noprint', 'email');
								
								$r = array();
								$r['success'] = true;
								$r['printer_pin'] = $printer_pin_cashierReceipt;
								$r['monitoring_id'] = $monitoring_id;
								
								$r['raw_content'] = array(
									'monitoring_id' => $monitoring_id,
									'id' => $billingData->id,
									'billing_no' => $billingData->billing_no,
									'table_no' => $billingData->table_no,
									'payment_date' => $billingData->payment_date,
									'total_billing' => $billingData->total_billing,
									'total_pembulatan' => $billingData->total_pembulatan,
									'grand_total' => $billingData->grand_total,
									'total_paid' => $billingData->total_paid,
									'total_return' => $billingData->total_return,
									'tax_total' => $billingData->tax_total,
									'service_total' => $billingData->service_total,
									'discount_total' => $billingData->discount_total,
									'total_dp' => $billingData->total_dp,
									'compliment_total' => $billingData->compliment_total,
									'total_cash' => $billingData->total_cash,
									'total_credit' => $billingData->total_credit,
									'payment_type_name' => $billingData->payment_type_name,
									'bank_name' => $billingData->bank_name,
									'card_no' => $billingData->card_no,
									'is_half_payment' => $billingData->is_half_payment,
								);
								
								$r['success'] = true;
								echo json_encode($r);
								die();
							}else{
								if(!empty($bill_preview)){
									printing_process($data_printer[$printer_id_cashierReceipt], $print_content_cashierReceipt,'noprint', $custom_print_data,$billingData);
								}else{
									//printing_process($data_printer[$printer_id_cashierReceipt], $print_content_cashierReceipt, 'print', 1);
									printing_process($data_printer[$printer_id_cashierReceipt], $print_content_cashierReceipt, 'print', $custom_print_data,$billingData);
								}
							}
							
						}else{
							echo json_encode($r);
						}
						
						die();
					}
				}
				
				//update-2003.001
				//over-ruled, QC-other
				$print_preview_billing = 0;
				
				if($print_type == 2 OR $print_type == -234){
					
					//if(empty($print_qcReceipt) AND $printMonitoring_qc == 0){
					if(empty($print_qcReceipt)){
						$r['info'] = 'IP: '.$ip_addr.' tidak dapat melakukan print QC ke '.$printer_ip_qcReceipt;
						$r['success'] = false;
						if($print_preview_billing == 0){
							//echo json_encode($r);
						}else{
							//printing_process_error($r['info']);
							//die();
						}
						//die();
					}
					
					//QC PRINTER ---------------
					if(!empty($print_qcReceipt) AND (!empty($order_data_kitchen) OR !empty($order_data_bar) OR !empty($order_data_other) OR !empty($order_qc_id))){
					
						if(!empty($order_data_kitchen_update) OR !empty($order_data_bar_update) OR !empty($order_data_other_update) OR !empty($order_qc_id) OR $print_type == -234){
							
							
							//MERGE ALL ORDER
							$order_data_kitchen_qc = '';
							if(!empty($order_data_kitchen)){
								$order_data_kitchen_qc = "KITCHEN[tab]\n";
								foreach($order_data_kitchen as $dt){
									$order_data_kitchen_qc .= $dt;
								}
							}
							
							$order_data_bar_qc = '';
							if(!empty($order_data_bar)){
								$order_data_bar_qc = "BAR[tab]\n";
								foreach($order_data_bar as $dt){
									$order_data_bar_qc .= $dt;
								}
							}
							
							$order_data_other_qc = '';
							if(!empty($order_data_other)){
								$order_data_other_qc = "OTHER[tab]\n";
								foreach($order_data_other as $dt){
									$order_data_other_qc .= $dt;
								}
							}
							
							$order_qc_notes = '';
							if(!empty($billingData->qc_notes)){
								$order_qc_notes = 'Notes: '.$billingData->qc_notes;
							}
									
							$total_guest = '';
							if(!empty($billingData->total_guest)){
								$total_guest = printer_command_align_right('Guest: '.$billingData->total_guest, 15);
							}
							
							$is_print_error = false;
							$print_attr = array(
								"{date}"	=> date("d/m/Y"),
								"{date_time}"	=> date("d/m/Y H:i"),
								"{user}"	=> $session_user,
								"{table_no}"	=> $table_no_receipt,
								"{order_data_kitchen}"	=> $order_data_kitchen_qc,
								"{order_data_bar}"	=> $order_data_bar_qc,
								"{order_data_other}"	=> $order_data_other_qc,
								"{guest}"		=> $total_guest,
								"{qc_notes}"	=> $order_qc_notes
							);
							
							$print_content_qcReceipt = strtr($qcReceipt_layout, $print_attr);	
							$print_content_qcReceipt_monitoring = $print_content_qcReceipt;	
							
							$print_content = replace_to_printer_command($print_content_qcReceipt, $printer_type_qc, $printer_pin_qcReceipt);
							
							$r['print'][] = $print_content;
							
							//echo $print_content_qcReceipt;
							//die();
							
							//$printMonitoring_qc
							if($printMonitoring_qc == 1){
								
								$r['success'] = true;
									
								//update status qc
								if(!empty($order_qc_id)){
									$order_qc_id_txt = implode(",", $order_qc_id);
									$data_update = array(
										'print_qc' => 1
									);
									$this->db->update($this->table2, $data_update, "id IN (".$order_qc_id_txt.")");
								}
								
								//SAVE to Print Monitoring
								$data_printMonitoring = array(
									'tipe'			=> 'qc',
									'peritem'		=> '0',
									'print_date'	=> date("Y-m-d"),
									'print_datetime'=> date("Y-m-d H:i:s"),
									'user'			=> $session_user,
									'table_no'		=> $billingData->table_no,
									'billing_no'	=> $billingData->billing_no,
									'receiptTxt'	=> $print_content_qcReceipt_monitoring,
									'printer'		=> $printer_ip_qcReceipt,
									'tipe_printer'	=> $printer_type_qc,
									'tipe_pin'		=> $printer_pin_qcReceipt
								);
								$this->db->insert($this->table_print_monitoring, $data_printMonitoring);
								
								//update-2003.001
								//if(!empty($order_apps)){
								if(empty($print_preview_billing)){
									//echo json_encode($r);
								}
								//die();
								
							}else{
								
								if($data_printer[$printer_id_qcReceipt]['print_method'] == 'ESC/POS'){
									
									try {
										@$ph = printer_open($printer_ip_qcReceipt);
									} catch (Exception $e) {
										$ph = false;
									}
									
									//$ph = @printer_open($printer_ip_qcReceipt);
									if($ph)
									{
										
										printer_start_doc($ph, "QC RECEIPT FROM CASHIER");
										printer_start_page($ph);
										printer_set_option($ph, PRINTER_MODE, "RAW");
										printer_write($ph, $print_content);
										printer_end_page($ph);
										printer_end_doc($ph);
										printer_close($ph);
										
										$r['success'] = true;
										
										//update status qc
										if(!empty($order_qc_id)){
											$order_qc_id_txt = implode(",", $order_qc_id);
											$data_update = array(
												'print_qc' => 1
											);
											$this->db->update($this->table2, $data_update, "id IN (".$order_qc_id_txt.")");
										}
										
										
									}else{
										$is_print_error = true;
									}
									
									$data_printer[$printer_id_qcReceipt]['escpos_pass'] = 1;
									
									if($is_print_error){					
										$r['info'] .= 'Komunikasi dengan Printer QC Gagal!<br/>';
										$r['success'] = false;
										if($is_void_order == 0){
											if($print_preview_billing == 0){
												//echo json_encode($r);
											}else{
												printing_process_error($r['info']);
												die();
											}
											//die();
										}
									}
								}
								
								//update-2008.001
								if($data_printer[$printer_id_qcReceipt]['print_method'] == 'RAWBT' AND !empty($rawbt_print)){
									echo $print_content;
									die();
								}
								
								//update-2003.001
								if(!empty($print_preview_billing)){
									printing_process($data_printer[$printer_id_qcReceipt], $print_content_qcReceipt, 'print');
								}else{
									//echo json_encode($r);
									//die();
								}
								
								if($is_void_order == 0){
									//die();
								}
								
							}
							
							if($is_print_error){					
								$r['info'] .= 'Komunikasi dengan Printer QC Gagal!<br/>';
								$r['success'] = false;
								if($is_void_order == 0){
									if($print_preview_billing == 0){
										//echo json_encode($r);
									}else{
										//printing_process_error($r['info']);
										//die();
									}
									//die();
								}
							}
		
						}else{
							
							if($is_void_order == 0){
								$r['info'] .= 'Semua Order Kitchen dan Bar utk QC Sudah diPrint<br/>';
								$r['success'] = true;
								if($print_preview_billing == 0){
									//echo json_encode($r);
								}else{
									$r['info'] = '';
									//printing_process_error($r['info']);
									//die();
								}
								//die();
							}
						}
					
					}else{
						
						if($is_void_order == 0){
							$r['info'] .= 'Belum ada order';
							$r['success'] = true;
							if($print_preview_billing == 0){
								//echo json_encode($r); 
							}else{
								$r['info'] = '';
								//printing_process_error('');
								//die();
								
							}
							//die();
						}
					}
				}
				
				if($print_type == 3 OR $print_type == -234){
					//KITCHEN PRINTER ---------------
					
					//if(empty($print_kitchenReceipt) AND $printMonitoring_kitchen == 0){
					if(empty($print_kitchenReceipt)){
						$r['info'] = 'IP: '.$ip_addr.' tidak dapat melakukan print Kitchen ke '.$printer_ip_kitchenReceipt;
						$r['success'] = false;
						if($print_preview_billing == 0){
							//echo json_encode($r);
						}else{
							//printing_process_error($r['info']);
							//die();
						}
						//die();
					}
					
					if(!empty($print_kitchenReceipt) AND !empty($order_data_kitchen) AND
						(!empty($order_data_kitchen_update) OR $print_type == -234)
					){
						$is_print_error = false;
						
						//echo $print_content_kitchenReceipt;
						//die();
						
						if(!empty($get_opt['print_order_peritem_kitchen']) AND $printMonitoring_kitchen == 0){
							$r['info'] = 'Print Order Kitchen Per-Item Hanya Bisa Berjalan pada Fitur Print Monitoring (Print to DB)';
							
							if($print_preview_billing == 0){
								//echo json_encode($r);
							}else{
								//printing_process_error($r['info']);
								//die();
							}
							//die();
						}
						
						
						//$printMonitoring_kitchen
						if($printMonitoring_kitchen == 1){
							
							$data_printMonitoring = array();
							
							//print_order_peritem_kitchen
							if(!empty($get_opt['print_order_peritem_kitchen'])){
								
								if(!empty($order_data_kitchen_update)){
									
									$update_id_order = array();
									foreach($order_data_kitchen_update as $idO){
										
										if(!empty($order_data_kitchen_peritem[$idO])){
											
											
											if(!in_array($idO, $update_id_order) AND $print_type != -234){
												$update_id_order[] = $idO;
											}
											if(!in_array($idO, $all_update_id_order) AND $print_type != -234){
												$all_update_id_order[] = $idO;
											}
											
											$order_qc_notes = '';
											if(!empty($billingData->qc_notes)){
												$order_qc_notes = 'Notes: '.$billingData->qc_notes;
											}
									
											$total_guest = '';
											if(!empty($billingData->total_guest)){
												$total_guest = printer_command_align_right('Guest: '.$billingData->total_guest, 15);
											}
											
											$print_attr = array(
												"{date}"	=> date("d/m/Y"),
												"{date_time}"	=> date("d/m/Y H:i"),
												"{user}"	=> $session_user,
												"{table_no}"	=> $table_no_receipt,
												"{order_data}"	=> $order_data_kitchen_peritem[$idO],
												"{guest}"		=> $total_guest,
												"{qc_notes}"	=> $order_qc_notes
											);
											
											$print_content_kitchenReceipt = strtr($kitchenReceipt_layout, $print_attr);	
											$print_content_kitchenReceipt_monitoring = $print_content_kitchenReceipt;	
											$print_content_kitchenReceipt = replace_to_printer_command($print_content_kitchenReceipt, $printer_type_kitchen, $printer_pin_kitchenReceipt);
											
											//SAVE to Print Monitoring
											$data_printMonitoring[] = array(
												'tipe'			=> 'kitchen',
												'peritem'		=> '1',
												'print_date'	=> date("Y-m-d"),
												'print_datetime'	=> date("Y-m-d H:i:s"),
												'user'			=> $session_user,
												'table_no'		=> $billingData->table_no,
												'billing_no'	=> $billingData->billing_no,
												'receiptTxt'	=> $print_content_kitchenReceipt_monitoring,
												'printer'		=> $printer_ip_kitchenReceipt,
												'tipe_printer'	=> $printer_type_kitchen,
												'tipe_pin'		=> $printer_pin_kitchenReceipt
											);
											
										}
										
										
									}
									
									
									if(!empty($update_id_order)){

										$order_data_kitchen_update_txt = implode(",", $update_id_order);
										$data_update = array(
											'order_status' => 'done'
										);
										$this->db->update($this->table2, $data_update, "id IN (".$order_data_kitchen_update_txt.")");
										
									}
								}
								
							}else{
								
								if(!empty($order_data_kitchen_update)){
									
									$update_id_order = array();
									$order_data_kitchen_Receipt = '';
									foreach($order_data_kitchen_update as $idO){
										
										if(!empty($order_data_kitchen[$idO])){
											
											if(!in_array($idO, $update_id_order) AND $print_type != -234){
												$update_id_order[] = $idO;
											}
											if(!in_array($idO, $all_update_id_order) AND $print_type != -234){
												$all_update_id_order[] = $idO;
											}
											
											$order_data_kitchen_Receipt .= $order_data_kitchen[$idO];
										
										}
									}
									
									
									$order_data_kitchen_Receipt = str_replace("KITCHEN[tab]","[tab]",$order_data_kitchen_Receipt);
									
									$order_qc_notes = '';
									if(!empty($billingData->qc_notes)){
										$order_qc_notes = 'Notes: '.$billingData->qc_notes;
									}
									
									$total_guest = '';
									if(!empty($billingData->total_guest)){
										$total_guest = printer_command_align_right('Guest: '.$billingData->total_guest, 15);
									}
											
									$print_attr = array(
										"{date}"	=> date("d/m/Y"),
										"{date_time}"	=> date("d/m/Y H:i"),
										"{user}"	=> $session_user,
										"{table_no}"	=> $table_no_receipt,
										"{order_data}"	=> $order_data_kitchen_Receipt,
										"{guest}"		=> $total_guest,
										"{qc_notes}"	=> $order_qc_notes
									);
									
									$print_content_kitchenReceipt = strtr($kitchenReceipt_layout, $print_attr);	
									$print_content_kitchenReceipt_monitoring = $print_content_kitchenReceipt;
									
									$print_content_kitchenReceipt = replace_to_printer_command($print_content_kitchenReceipt, $printer_type_kitchen, $printer_pin_kitchenReceipt);
									
									$r['print'][] = $print_content_kitchenReceipt;
								
									//SAVE to Print Monitoring
									$data_printMonitoring[] = array(
										'tipe'			=> 'kitchen',
										'peritem'		=> '0',
										'print_date'	=> date("Y-m-d"),
										'print_datetime'	=> date("Y-m-d H:i:s"),
										'user'			=> $session_user,
										'table_no'		=> $billingData->table_no,
										'billing_no'	=> $billingData->billing_no,
										'receiptTxt'	=> $print_content_kitchenReceipt_monitoring,
										'printer'		=> $printer_ip_kitchenReceipt,
										'tipe_printer'	=> $printer_type_kitchen,
										'tipe_pin'		=> $printer_pin_kitchenReceipt
									);

									if(!empty($update_id_order)){

										$order_data_kitchen_update_txt = implode(",", $update_id_order);
										$data_update = array(
											'order_status' => 'done'
										);
										$this->db->update($this->table2, $data_update, "id IN (".$order_data_kitchen_update_txt.")");
										
									}
									
								}
							}
							
							$r['success'] = true;
							$this->db->insert_batch($this->table_print_monitoring, $data_printMonitoring);
							
							//update-2003.001
							//if(!empty($order_apps)){
							if(empty($print_preview_billing)){
								//echo json_encode($r);
							}
							//die();
							
						}else{
							
							$data_print_kitchen_peritem_html = '';
							$data_print_kitchen_peritem_escpos = array();
							
							//print_order_peritem_kitchen
							if(!empty($get_opt['print_order_peritem_kitchen'])){
								
								if(!empty($order_data_kitchen_update)){
									
									$update_id_order = array();
									foreach($order_data_kitchen_update as $idO){
										
										if(!empty($order_data_kitchen_peritem[$idO])){
											
											if(!in_array($idO, $update_id_order) AND $print_type != -234){
												$update_id_order[] = $idO;
											}
											
											if(!in_array($idO, $all_update_id_order) AND $print_type != -234){
												$all_update_id_order[] = $idO;
											}
											
											$order_qc_notes = '';
											if(!empty($billingData->qc_notes)){
												$order_qc_notes = 'Notes: '.$billingData->qc_notes;
											}
									
											$total_guest = '';
											if(!empty($billingData->total_guest)){
												$total_guest = printer_command_align_right('Guest: '.$billingData->total_guest, 15);
											}
											
											$print_attr = array(
												"{date}"	=> date("d/m/Y"),
												"{date_time}"	=> date("d/m/Y H:i"),
												"{user}"	=> $session_user,
												"{table_no}"	=> $table_no_receipt,
												"{order_data}"	=> $order_data_kitchen_peritem[$idO],
												"{guest}"		=> $total_guest,
												"{qc_notes}"	=> $order_qc_notes
											);
											
											$print_content_kitchenReceipt = strtr($kitchenReceipt_layout, $print_attr);	
											$print_content = replace_to_printer_command($print_content_kitchenReceipt, $printer_type_kitchen, $printer_pin_kitchenReceipt);
											
											if(empty($data_print_kitchen_peritem_html)){
												$data_print_kitchen_peritem_html = $print_content_kitchenReceipt;
											}else{
												$data_print_kitchen_peritem_html .= '<p style="page-break-before: always">';
												$data_print_kitchen_peritem_html .= "\n";
												$data_print_kitchen_peritem_html .= $print_content_kitchenReceipt;
											}
											
											$data_print_kitchen_peritem_escpos[] = $print_content;
											
											
										}
										
										
									}
									
									
									if(!empty($update_id_order)){

										$order_data_kitchen_update_txt = implode(",", $update_id_order);
										$data_update = array(
											'order_status' => 'done'
										);
										$this->db->update($this->table2, $data_update, "id IN (".$order_data_kitchen_update_txt.")");
										
									}
								}
								
							}else{
								
								if(!empty($order_data_kitchen_update)){
								
									$update_id_order = array();
									$order_data_kitchen_Receipt = '';
									foreach($order_data_kitchen_update as $idO){
										
										if(!empty($order_data_kitchen[$idO])){
											
											if(!in_array($idO, $update_id_order) AND $print_type != -234){
												$update_id_order[] = $idO;
											}
											
											if(!in_array($idO, $all_update_id_order) AND $print_type != -234){
												$all_update_id_order[] = $idO;
											}
											
											$order_data_kitchen_Receipt .= $order_data_kitchen[$idO];
										
										}
									}
									
									
									$order_data_kitchen_Receipt = str_replace("KITCHEN[tab]","[tab]",$order_data_kitchen_Receipt);
									
									$order_qc_notes = '';
									if(!empty($billingData->qc_notes)){
										$order_qc_notes = 'Notes: '.$billingData->qc_notes;
									}
									
									$total_guest = '';
									if(!empty($billingData->total_guest)){
										$total_guest = printer_command_align_right('Guest: '.$billingData->total_guest, 15);
									}
									
									$print_attr = array(
										"{date}"	=> date("d/m/Y"),
										"{date_time}"	=> date("d/m/Y H:i"),
										"{user}"	=> $session_user,
										"{table_no}"	=> $table_no_receipt,
										"{order_data}"	=> $order_data_kitchen_Receipt,
										"{guest}"		=> $total_guest,
										"{qc_notes}"	=> $order_qc_notes
									);
									
									$print_content_kitchenReceipt = strtr($kitchenReceipt_layout, $print_attr);	
									$print_content_kitchenReceipt_monitoring = $print_content_kitchenReceipt;
									
									$print_content = replace_to_printer_command($print_content_kitchenReceipt, $printer_type_kitchen, $printer_pin_kitchenReceipt);
									
									$r['print'][] = $print_content;
									
									$data_print_kitchen_peritem_html = $print_content_kitchenReceipt;
									$data_print_kitchen_peritem_escpos = $print_content;
									

									if(!empty($update_id_order)){

										$order_data_kitchen_update_txt = implode(",", $update_id_order);
										$data_update = array(
											'order_status' => 'done'
										);
										$this->db->update($this->table2, $data_update, "id IN (".$order_data_kitchen_update_txt.")");
										
									}
									
								}
								
								
							}
							
							if($data_printer[$printer_id_kitchenReceipt]['print_method'] == 'ESC/POS'){
								try {
									@$ph = printer_open($printer_ip_kitchenReceipt);
								} catch (Exception $e) {
									$ph = false;
								}
								
								//$ph = @printer_open($printer_ip_kitchenReceipt);
								if($ph)
								{
									if(!empty($get_opt['print_order_peritem_kitchen'])){
										
										foreach($data_print_kitchen_peritem_escpos as $print_content){
											printer_start_doc($ph, "KITCHEN RECEIPT FROM ".$printer_ip_kitchenReceipt);
											printer_start_page($ph);
											printer_set_option($ph, PRINTER_MODE, "RAW");
											printer_write($ph, $print_content);
											printer_end_page($ph);
											printer_end_doc($ph);
										}
										
										
									}else{
										printer_start_doc($ph, "KITCHEN RECEIPT FROM ".$printer_ip_kitchenReceipt);
										printer_start_page($ph);
										printer_set_option($ph, PRINTER_MODE, "RAW");
										printer_write($ph, $data_print_kitchen_peritem_escpos);
										printer_end_page($ph);
										printer_end_doc($ph);
										printer_close($ph);
									}
									
									$r['success'] = true;
								}else{
									$is_print_error = true;
								}
									
								$data_printer[$printer_id_kitchenReceipt]['escpos_pass'] = 1;
								
							}
								
							//update-2008.001
							if($data_printer[$printer_id_kitchenReceipt]['print_method'] == 'RAWBT' AND !empty($rawbt_print)){
								echo $print_content;
								die();
							}
							
							//update-2003.001
							if(!empty($print_preview_billing)){
								printing_process($data_printer[$printer_id_kitchenReceipt], $data_print_kitchen_peritem_html, 'print');
							}else{
								//echo json_encode($r);
								//die();
							}
							
							if($is_void_order == 0){
								//die();
							}
						}
						
						
						if($is_print_error){					
							$r['info'] .= 'Komunikasi dengan Printer Kitchen Gagal!<br/>';
							$r['success'] = false;
							if($is_void_order == 0){
								if($print_preview_billing == 0){
									//echo json_encode($r);
								}else{
									//printing_process_error($r['info']);
									//die();
								}
								//die();
							}
						}
						
					}else{
						
						if(empty($order_data_kitchen) AND !empty($order_data_kitchen_update)){
							
							if($is_void_order == 0){
								$r['info'] .= 'Semua Order Kitchen Sudah diPrint<br/>';
								$r['success'] = true;
								if($print_preview_billing == 0){
									//echo json_encode($r);
								}else{
									$r['info'] = '';
									//printing_process_error($r['info']);
									//die();
								}
								//die();
							}
						}else{
							
							if($is_void_order == 0){
								$r['info'] .= 'Belum ada order Kitchen';
								$r['success'] = true;
								if($print_preview_billing == 0){
									//echo json_encode($r);
								}else{
									$r['info'] = '';
									//printing_process_error('');
									//die();
								}
								//die();
							}
						}
					}
					
					
				}
				
				
				if($print_type == 4 OR $print_type == -234){
					//BAR PRINTER ---------------
					
					//if(empty($print_barReceipt) AND $printMonitoring_bar == 0){
					if(empty($print_barReceipt)){
						$r['info'] = 'IP: '.$ip_addr.' tidak dapat melakukan print Bar ke '.$printer_ip_barReceipt;
						$r['success'] = false;
						if($print_preview_billing == 0){
							//echo json_encode($r);
						}else{
							//printing_process_error($r['info']);
							//die();
						}
						//die();
					}
					
					if(!empty($print_barReceipt) AND !empty($order_data_bar) AND 
						(!empty($order_data_bar_update) OR $print_type == -234)
					){
						$is_print_error = false;			
						
						
						if(!empty($get_opt['print_order_peritem_bar']) AND $printMonitoring_bar == 0){
							$r['info'] = 'Print Order Bar Per-Item Hanya Bisa Berjalan pada Fitur Print Monitoring (Print to DB)';
							if($print_preview_billing == 0){
								//echo json_encode($r);
							}else{
								//printing_process_error($r['info']);
								//die();
							}
							//die();
						}
						
						//$printMonitoring_bar
						if($printMonitoring_bar == 1){
							
							$data_printMonitoring = array();
							
							//print_order_peritem_bar
							if(!empty($get_opt['print_order_peritem_bar'])){
								
								if(!empty($order_data_bar_update)){
									
									$update_id_order = array();
									foreach($order_data_bar_update as $idO){
										
										if(!empty($order_data_bar_peritem[$idO])){
											
											if(!in_array($idO, $update_id_order) AND $print_type != -234){
												$update_id_order[] = $idO;
											}
											
											if(!in_array($idO, $all_update_id_order) AND $print_type != -234){
												$all_update_id_order[] = $idO;
											}
											
											$order_qc_notes = '';
											if(!empty($billingData->qc_notes)){
												$order_qc_notes = 'Notes: '.$billingData->qc_notes;
											}
									
											$total_guest = '';
											if(!empty($billingData->total_guest)){
												$total_guest = printer_command_align_right('Guest: '.$billingData->total_guest, 15);
											}
											
											$print_attr = array(
												"{date}"	=> date("d/m/Y"),
												"{date_time}"	=> date("d/m/Y H:i"),
												"{user}"	=> $session_user,
												"{table_no}"	=> $table_no_receipt,
												"{order_data}"	=> $order_data_bar_peritem[$idO],
												"{guest}"		=> $total_guest,
												"{qc_notes}"	=> $order_qc_notes
											);
											
											$print_content_barReceipt = strtr($barReceipt_layout, $print_attr);	
											$print_content_barReceipt_monitoring = $print_content_barReceipt;	
											$print_content_barReceipt = replace_to_printer_command($print_content_barReceipt, $printer_type_bar, $printer_pin_barReceipt);
											
											//SAVE to Print Monitoring
											$data_printMonitoring[] = array(
												'tipe'			=> 'bar',
												'peritem'		=> '1',
												'print_date'	=> date("Y-m-d"),
												'print_datetime'	=> date("Y-m-d H:i:s"),
												'user'			=> $session_user,
												'table_no'		=> $billingData->table_no,
												'billing_no'	=> $billingData->billing_no,
												'receiptTxt'	=> $print_content_barReceipt_monitoring,
												'printer'		=> $printer_ip_barReceipt,
												'tipe_printer'	=> $printer_type_bar,
												'tipe_pin'		=> $printer_pin_barReceipt
											);
											
										}
										
										
									}
									
									if(!empty($update_id_order)){

										$order_data_bar_update_txt = implode(",", $update_id_order);
										$data_update = array(
											'order_status' => 'done'
										);
										$this->db->update($this->table2, $data_update, "id IN (".$order_data_bar_update_txt.")");
										
									}
								}
								
							}else{
								
								if(!empty($order_data_bar_update)){
								
									$update_id_order = array();
									$order_data_bar_Receipt = '';
									foreach($order_data_bar_update as $idO){
										
										if(!empty($order_data_bar[$idO])){
											
											if(!in_array($idO, $update_id_order) AND $print_type != -234){
												$update_id_order[] = $idO;
											}
											
											if(!in_array($idO, $all_update_id_order) AND $print_type != -234){
												$all_update_id_order[] = $idO;
											}
											
											$order_data_bar_Receipt .= $order_data_bar[$idO];
										
										}
									}
									
									
									$order_data_bar_Receipt = str_replace("BAR[tab]","[tab]",$order_data_bar_Receipt);
									
									$order_qc_notes = '';
									if(!empty($billingData->qc_notes)){
										$order_qc_notes = 'Notes: '.$billingData->qc_notes;
									}
									
									$total_guest = '';
									if(!empty($billingData->total_guest)){
										$total_guest = printer_command_align_right('Guest: '.$billingData->total_guest, 15);
									}
									
									$print_attr = array(
										"{date}"	=> date("d/m/Y"),
										"{date_time}"	=> date("d/m/Y H:i"),
										"{user}"	=> $session_user,
										"{table_no}"	=> $table_no_receipt,
										"{order_data}"	=> $order_data_bar_Receipt,
										"{guest}"		=> $total_guest,
										"{qc_notes}"	=> $order_qc_notes
									);
									
									$print_content_barReceipt = strtr($barReceipt_layout, $print_attr);			
									$print_content_barReceipt_monitoring = $print_content_barReceipt;
									$print_content_barReceipt = replace_to_printer_command($print_content_barReceipt, $printer_type_bar, $printer_pin_barReceipt);
									
									$r['print'][] = $print_content_barReceipt;
									
									//SAVE to Print Monitoring
									$data_printMonitoring[] = array(
										'tipe'			=> 'bar',
										'peritem'		=> '0',
										'print_date'	=> date("Y-m-d"),
										'print_datetime'	=> date("Y-m-d H:i:s"),
										'user'			=> $session_user,
										'table_no'		=> $billingData->table_no,
										'billing_no'	=> $billingData->billing_no,
										'receiptTxt'	=> $print_content_barReceipt_monitoring,
										'printer'		=> $printer_ip_barReceipt,
										'tipe_printer'	=> $printer_type_bar,
										'tipe_pin'		=> $printer_pin_barReceipt
									);
									
									if(!empty($update_id_order)){

										$order_data_bar_update_txt = implode(",", $update_id_order);
										$data_update = array(
											'order_status' => 'done'
										);
										$this->db->update($this->table2, $data_update, "id IN (".$order_data_bar_update_txt.")");
										
									}
									
								}
								
							}
							
							$r['success'] = true;
							$this->db->insert_batch($this->table_print_monitoring, $data_printMonitoring);
							
							//update-2003.001
							//if(!empty($order_apps)){
							if(empty($print_preview_billing)){
								//echo json_encode($r); 
							}
							//die();
							
						}else{
							
							$data_print_bar_peritem_escpos = array();
							$data_print_bar_peritem_html = '';
										
							//print_order_peritem_bar
							if(!empty($get_opt['print_order_peritem_bar'])){
								
								if(!empty($order_data_bar_update)){
									
									$update_id_order = array();
									foreach($order_data_bar_update as $idO){
										
										if(!empty($order_data_bar_peritem[$idO])){
											
											if(!in_array($idO, $update_id_order) AND $print_type != -234){
												$update_id_order[] = $idO;
											}
											
											if(!in_array($idO, $all_update_id_order) AND $print_type != -234){
												$all_update_id_order[] = $idO;
											}
											
											$order_qc_notes = '';
											if(!empty($billingData->qc_notes)){
												$order_qc_notes = 'Notes: '.$billingData->qc_notes;
											}
									
											$total_guest = '';
											if(!empty($billingData->total_guest)){
												$total_guest = printer_command_align_right('Guest: '.$billingData->total_guest, 15);
											}
											
											$print_attr = array(
												"{date}"	=> date("d/m/Y"),
												"{date_time}"	=> date("d/m/Y H:i"),
												"{user}"	=> $session_user,
												"{table_no}"	=> $table_no_receipt,
												"{order_data}"	=> $order_data_bar_peritem[$idO],
												"{guest}"		=> $total_guest,
												"{qc_notes}"	=> $order_qc_notes
											);
											
											$print_content_barReceipt = strtr($barReceipt_layout, $print_attr);	
											$print_content = replace_to_printer_command($print_content_barReceipt, $printer_type_bar, $printer_pin_barReceipt);
											
											if(empty($data_print_bar_peritem_html)){
												$data_print_bar_peritem_html = $print_content_barReceipt;
											}else{
												$data_print_bar_peritem_html .= '<div style="page-break-before: always;"></div>';
												$data_print_bar_peritem_html .= $print_content_barReceipt;
											}
											
											$data_print_bar_peritem_escpos[] = $print_content;
											
											
										}
										
										
									}
									
									if(!empty($update_id_order)){

										$order_data_bar_update_txt = implode(",", $update_id_order);
										$data_update = array(
											'order_status' => 'done'
										);
										$this->db->update($this->table2, $data_update, "id IN (".$order_data_bar_update_txt.")");
										
									}
								}
								
							}else{
								
								if(!empty($order_data_bar_update)){
								
									$update_id_order = array();
									$order_data_bar_Receipt = '';
									foreach($order_data_bar_update as $idO){
										
										if(!empty($order_data_bar[$idO])){
											
											if(!in_array($idO, $update_id_order) AND $print_type != -234){
												$update_id_order[] = $idO;
											}
											
											if(!in_array($idO, $all_update_id_order) AND $print_type != -234){
												$all_update_id_order[] = $idO;
											}
											
											$order_data_bar_Receipt .= $order_data_bar[$idO];
										
										}
									}
									
									
									$order_data_bar_Receipt = str_replace("BAR[tab]","[tab]",$order_data_bar_Receipt);
									
									$order_qc_notes = '';
									if(!empty($billingData->qc_notes)){
										$order_qc_notes = 'Notes: '.$billingData->qc_notes;
									}
									
									$total_guest = '';
									if(!empty($billingData->total_guest)){
										$total_guest = printer_command_align_right('Guest: '.$billingData->total_guest, 15);
									}
									
									$print_attr = array(
										"{date}"	=> date("d/m/Y"),
										"{date_time}"	=> date("d/m/Y H:i"),
										"{user}"	=> $session_user,
										"{table_no}"	=> $table_no_receipt,
										"{order_data}"	=> $order_data_bar_Receipt,
										"{guest}"		=> $total_guest,
										"{qc_notes}"	=> $order_qc_notes
									);
									
									$print_content_barReceipt = strtr($barReceipt_layout, $print_attr);			
									$print_content_barReceipt_monitoring = $print_content_barReceipt;
									$print_content = replace_to_printer_command($print_content_barReceipt, $printer_type_bar, $printer_pin_barReceipt);
									
									$r['print'][] = $print_content;
									
									$data_print_bar_peritem_html = $print_content_barReceipt;
									$data_print_bar_peritem_escpos = $print_content;
									
									
									if(!empty($update_id_order)){

										$order_data_bar_update_txt = implode(",", $update_id_order);
										$data_update = array(
											'order_status' => 'done'
										);
										$this->db->update($this->table2, $data_update, "id IN (".$order_data_bar_update_txt.")");
										
									}
									
								}
							}
								
								
							if($data_printer[$printer_id_barReceipt]['print_method'] == 'ESC/POS'){
								
								try {
									@$ph = printer_open($printer_ip_barReceipt);
								} catch (Exception $e) {
									$ph = false;
								}
								
								//$ph = @printer_open($printer_ip_barReceipt);
								if($ph)
								{
									if(!empty($get_opt['print_order_peritem_bar'])){
										
										foreach($data_print_bar_peritem_escpos as $print_content){
											printer_start_doc($ph, "BAR RECEIPT FROM ".$printer_ip_barReceipt);
											printer_start_page($ph);
											printer_set_option($ph, PRINTER_MODE, "RAW");
											printer_write($ph, $print_content);
											printer_end_page($ph);
											printer_end_doc($ph);
										}
										
										
									}else{
										printer_start_doc($ph, "BAR RECEIPT FROM ".$printer_ip_barReceipt);
										printer_start_page($ph);
										printer_set_option($ph, PRINTER_MODE, "RAW");
										printer_write($ph, $data_print_bar_peritem_escpos);
										printer_end_page($ph);
										printer_end_doc($ph);
										printer_close($ph);
									}
									
									$r['success'] = true;
								}else{
									$is_print_error = true;
								}
									
								$data_printer[$printer_id_barReceipt]['escpos_pass'] = 1;
								
							}
							
							//update-2008.001
							if($data_printer[$printer_id_barReceipt]['print_method'] == 'RAWBT' AND !empty($rawbt_print)){
								echo $print_content;
								die();
							}
							
							//update-2003.001
							if(!empty($print_preview_billing)){
								printing_process($data_printer[$printer_id_barReceipt], $data_print_bar_peritem_html, 'print');
							}else{
								//echo json_encode($r);
								//die();
							}
							
							if($is_void_order == 0){
								//die();
							}
							
							
						}
						
						if($is_print_error){					
							$r['info'] .= 'Komunikasi dengan Printer Bar Gagal!<br/>';
							$r['success'] = false;
							if($is_void_order == 0){
								if($print_preview_billing == 0){
									//echo json_encode($r);
								}else{
									//printing_process_error($r['info']);
									//die();
								}
								//die();
							}
						}
						
					}else{
						
						if(empty($order_data_bar) AND !empty($order_data_bar_update)){
							
							if($is_void_order == 0){
								$r['info'] .= 'Semua Order Bar Sudah diPrint<br/>';
								$r['success'] = true;
								if($print_preview_billing == 0){
									//echo json_encode($r);
								}else{
									$r['info'] = '';
									//printing_process_error($r['info']);
									//die();
								}
								//die();
							}
						}else{
							
							if($is_void_order == 0){
								$r['info'] .= 'Belum ada order Bar';
								$r['success'] = true;
								if($print_preview_billing == 0){
									//echo json_encode($r);
								}else{
									$r['info'] = '';
									//printing_process_error('');
									//die();
								}
								//die();
								
							}
						}
					}
				}
				
				if($print_type == 5 OR $print_type == -234){
					//OTHER PRINTER ---------------
					
					//if(empty($print_otherReceipt) AND $printMonitoring_other == 0){
					if(empty($print_otherReceipt)){
						$r['info'] = 'IP: '.$ip_addr.' tidak dapat melakukan print Other ke '.$printer_ip_otherReceipt;
						if($print_preview_billing == 0){
							//echo json_encode($r);
						}else{
							//printing_process_error($r['info']);
							//die();
						}
						//die();
					}
					
					if(!empty($print_otherReceipt) AND !empty($order_data_other) AND 
						(!empty($order_data_other_update) OR $print_type == -234)
					){
						$is_print_error = false;			
						
						
						if(!empty($get_opt['print_order_peritem_other']) AND $printMonitoring_other == 0){
							$r['info'] = 'Print Order Other/Lainnya Per-Item Hanya Bisa Berjalan pada Fitur Print Monitoring (Print to DB)';
							if($print_preview_billing == 0){
								//echo json_encode($r);
							}else{
								//printing_process_error($r['info']);
								//die();
							}
							//die();
						}
						
						//$printMonitoring_other
						if($printMonitoring_other == 1){
							
							$data_printMonitoring = array();
							
							//print_order_peritem_other
							if(!empty($get_opt['print_order_peritem_other'])){
								
								if(!empty($order_data_other_update)){
									
									$update_id_order = array();
									foreach($order_data_other_update as $idO){
										
										if(!empty($order_data_other_peritem[$idO])){
											
											if(!in_array($idO, $update_id_order)){
												$update_id_order[] = $idO;
											}
											
											if(!in_array($idO, $all_update_id_order)){
												$all_update_id_order[] = $idO;
											}
											
											$order_qc_notes = '';
											if(!empty($billingData->qc_notes)){
												$order_qc_notes = 'Notes: '.$billingData->qc_notes;
											}
									
											$total_guest = '';
											if(!empty($billingData->total_guest)){
												$total_guest = printer_command_align_right('Guest: '.$billingData->total_guest, 15);
											}
											
											$print_attr = array(
												"{date}"	=> date("d/m/Y"),
												"{date_time}"	=> date("d/m/Y H:i"),
												"{user}"	=> $session_user,
												"{table_no}"	=> $table_no_receipt,
												"{order_data}"	=> $order_data_other_peritem[$idO],
												"{guest}"		=> $total_guest,
												"{qc_notes}"	=> $order_qc_notes
											);
											
											$print_content_otherReceipt = strtr($otherReceipt_layout, $print_attr);	
											$print_content_otherReceipt_monitoring = $print_content_otherReceipt;	
											$print_content_otherReceipt = replace_to_printer_command($print_content_otherReceipt, $printer_type_other, $printer_pin_otherReceipt);
											
											//SAVE to Print Monitoring
											$data_printMonitoring[] = array(
												'tipe'			=> 'other',
												'peritem'		=> '1',
												'print_date'	=> date("Y-m-d"),
												'print_datetime'	=> date("Y-m-d H:i:s"),
												'user'			=> $session_user,
												'table_no'		=> $billingData->table_no,
												'billing_no'	=> $billingData->billing_no,
												'receiptTxt'	=> $print_content_otherReceipt_monitoring,
												'printer'		=> $printer_ip_otherReceipt,
												'tipe_printer'	=> $printer_type_other,
												'tipe_pin'		=> $printer_pin_otherReceipt
											);
											
										}
										
										
									}
									
									
									if(!empty($update_id_order)){

										$order_data_other_update_txt = implode(",", $update_id_order);
										$data_update = array(
											'order_status' => 'done'
										);
										$this->db->update($this->table2, $data_update, "id IN (".$order_data_other_update_txt.")");
										
									}
								}
								
							}else{
								
								if(!empty($order_data_other_update)){
								
									$update_id_order = array();
									$order_data_other_Receipt = '';
									foreach($order_data_other_update as $idO){
										
										if(!empty($order_data_other[$idO])){
											
											if(!in_array($idO, $update_id_order) AND $print_type != -234){
												$update_id_order[] = $idO;
											}
											
											if(!in_array($idO, $all_update_id_order) AND $print_type != -234){
												$all_update_id_order[] = $idO;
											}
											
											$order_data_other_Receipt .= $order_data_other[$idO];
										
										}
									}
									
									
									$order_data_other_Receipt = str_replace("OTHER[tab]","[tab]",$order_data_other_Receipt);
									
									$order_qc_notes = '';
									if(!empty($billingData->qc_notes)){
										$order_qc_notes = 'Notes: '.$billingData->qc_notes;
									}
									
									$total_guest = '';
									if(!empty($billingData->total_guest)){
										$total_guest = printer_command_align_right('Guest: '.$billingData->total_guest, 15);
									}
									
									$print_attr = array(
										"{date}"	=> date("d/m/Y"),
										"{date_time}"	=> date("d/m/Y H:i"),
										"{user}"	=> $session_user,
										"{table_no}"	=> $table_no_receipt,
										"{order_data}"	=> $order_data_other_Receipt,
										"{guest}"		=> $total_guest,
										"{qc_notes}"	=> $order_qc_notes
									);
									
									$print_content_otherReceipt = strtr($otherReceipt_layout, $print_attr);	
									$print_content_otherReceipt_monitoring = $print_content_otherReceipt;
									$print_content_otherReceipt = replace_to_printer_command($print_content_otherReceipt, $printer_type_other, $printer_pin_otherReceipt);
									
									$r['print'][] = $print_content_otherReceipt;
									
									//SAVE to Print Monitoring
									$data_printMonitoring[] = array(
										'tipe'			=> 'other',
										'peritem'		=> '0',
										'print_date'	=> date("Y-m-d"),
										'print_datetime'	=> date("Y-m-d H:i:s"),
										'user'			=> $session_user,
										'table_no'		=> $billingData->table_no,
										'billing_no'	=> $billingData->billing_no,
										'receiptTxt'	=> $print_content_otherReceipt_monitoring,
										'printer'		=> $printer_ip_otherReceipt,
										'tipe_printer'	=> $printer_type_other,
										'tipe_pin'		=> $printer_pin_otherReceipt
									);
									
									if(!empty($update_id_order)){

										$order_data_bar_update_txt = implode(",", $update_id_order);
										$data_update = array(
											'order_status' => 'done'
										);
										$this->db->update($this->table2, $data_update, "id IN (".$order_data_bar_update_txt.")");
										
									}
									
								}
								
								
							
							}
							
							$r['success'] = true;
							$this->db->insert_batch($this->table_print_monitoring, $data_printMonitoring);
							
							//update-2003.001
							//if(!empty($order_apps)){
							if(empty($print_preview_billing)){
								//echo json_encode($r);
							}
							//die();
						
						}else{
								
							$data_print_other_peritem_escpos = array();
							$data_print_other_peritem_html = '';
									
							//print_order_peritem_other
							if(!empty($get_opt['print_order_peritem_other'])){
								
								if(!empty($order_data_other_update)){
									
									$update_id_order = array();
									foreach($order_data_other_update as $idO){
										
										if(!empty($order_data_other_peritem[$idO])){
											
											if(!in_array($idO, $update_id_order)){
												$update_id_order[] = $idO;
											}
											
											if(!in_array($idO, $all_update_id_order)){
												$all_update_id_order[] = $idO;
											}
											
											$order_qc_notes = '';
											if(!empty($billingData->qc_notes)){
												$order_qc_notes = 'Notes: '.$billingData->qc_notes;
											}
									
											$total_guest = '';
											if(!empty($billingData->total_guest)){
												$total_guest = printer_command_align_right('Guest: '.$billingData->total_guest, 15);
											}
											
											$print_attr = array(
												"{date}"	=> date("d/m/Y"),
												"{date_time}"	=> date("d/m/Y H:i"),
												"{user}"	=> $session_user,
												"{table_no}"	=> $table_no_receipt,
												"{order_data}"	=> $order_data_other_peritem[$idO],
												"{guest}"		=> $total_guest,
												"{qc_notes}"	=> $order_qc_notes
											);
											
											$print_content_otherReceipt = strtr($otherReceipt_layout, $print_attr);	
											$print_content = replace_to_printer_command($print_content_otherReceipt, $printer_type_other, $printer_pin_otherReceipt);
											
											if(empty($data_print_other_peritem_html)){
												$data_print_other_peritem_html = $print_content_otherReceipt;
											}else{
												$data_print_other_peritem_html .= '<div style="page-break-before: always;"></div>';
												$data_print_other_peritem_html .= $print_content_otherReceipt;
											}
											
											$data_print_other_peritem_escpos[] = $print_content;
											
										}
										
										
									}
									
									if(!empty($update_id_order)){

										$order_data_other_update_txt = implode(",", $update_id_order);
										$data_update = array(
											'order_status' => 'done'
										);
										$this->db->update($this->table2, $data_update, "id IN (".$order_data_other_update_txt.")");
										
									}
								}
								
							}else{
								
								if(!empty($order_data_other_update)){
							
									$update_id_order = array();
									$order_data_other_Receipt = '';
									foreach($order_data_other_update as $idO){
										
										if(!empty($order_data_other[$idO])){
											
											if(!in_array($idO, $update_id_order) AND $print_type != -234){
												$update_id_order[] = $idO;
											}
											
											if(!in_array($idO, $all_update_id_order) AND $print_type != -234){
												$all_update_id_order[] = $idO;
											}
											
											$order_data_other_Receipt .= $order_data_other[$idO];
										
										}
									}
									
									
									$order_data_other_Receipt = str_replace("OTHER[tab]","[tab]",$order_data_other_Receipt);
							
									$order_qc_notes = '';
									if(!empty($billingData->qc_notes)){
										$order_qc_notes = 'Notes: '.$billingData->qc_notes;
									}
									
									$total_guest = '';
									if(!empty($billingData->total_guest)){
										$total_guest = printer_command_align_right('Guest: '.$billingData->total_guest, 15);
									}
									
									$print_attr = array(
										"{date}"	=> date("d/m/Y"),
										"{date_time}"	=> date("d/m/Y H:i"),
										"{user}"	=> $session_user,
										"{table_no}"	=> $table_no_receipt,
										"{order_data}"	=> $order_data_other_Receipt,
										"{guest}"		=> $total_guest,
										"{qc_notes}"	=> $order_qc_notes
									);
									
									$print_content_otherReceipt = strtr($otherReceipt_layout, $print_attr);	
									$print_content_otherReceipt_monitoring = $print_content_otherReceipt;
									$print_content = replace_to_printer_command($print_content_otherReceipt, $printer_type_other, $printer_pin_otherReceipt);
									
									$r['print'][] = $print_content;
									
									$data_print_other_peritem_html = $print_content_otherReceipt;
									$data_print_other_peritem_escpos = $print_content;
									
									if(!empty($update_id_order)){

										$order_data_bar_update_txt = implode(",", $update_id_order);
										$data_update = array(
											'order_status' => 'done'
										);
										$this->db->update($this->table2, $data_update, "id IN (".$order_data_bar_update_txt.")");
										
									}
									
								}
								
							}

								
								
							if($data_printer[$printer_id_otherReceipt]['print_method'] == 'ESC/POS'){
								
								try {
									@$ph = printer_open($printer_ip_otherReceipt);
								} catch (Exception $e) {
									$ph = false;
								}
								
								//$ph = @printer_open($printer_ip_otherReceipt);
								if($ph)
								{
									if(!empty($get_opt['print_order_peritem_other'])){
										
										foreach($data_print_other_peritem_escpos as $print_content){
											printer_start_doc($ph, "OTHER RECEIPT FROM ".$printer_ip_otherReceipt);
											printer_start_page($ph);
											printer_set_option($ph, PRINTER_MODE, "RAW");
											printer_write($ph, $print_content);
											printer_end_page($ph);
											printer_end_doc($ph);
										}
										
										
									}else{
										printer_start_doc($ph, "OTHER RECEIPT FROM ".$printer_ip_otherReceipt);
										printer_start_page($ph);
										printer_set_option($ph, PRINTER_MODE, "RAW");
										printer_write($ph, $data_print_other_peritem_escpos);
										printer_end_page($ph);
										printer_end_doc($ph);
										printer_close($ph);
									}
									
									$r['success'] = true;
								}else{
									$is_print_error = true;
								}
								
								$data_printer[$printer_id_otherReceipt]['escpos_pass'] = 1;
								
							}
							
							//update-2008.001
							if($data_printer[$printer_id_otherReceipt]['print_method'] == 'RAWBT' AND !empty($rawbt_print)){
								echo $print_content;
								die();
							}
							
							//update-2003.001
							if(!empty($print_preview_billing)){
								printing_process($data_printer[$printer_id_otherReceipt], $data_print_other_peritem_html, 'print');
							}else{
								//echo json_encode($r);
								//die();
							}
							
							if($is_void_order == 0){
								//die();
							}
							
						 
						}
						
						if($is_print_error){					
							$r['info'] .= 'Komunikasi dengan Printer Other (Lainnya) Gagal!<br/>';
							$r['success'] = false;
							if($is_void_order == 0){
								if($print_preview_billing == 0){
									//echo json_encode($r);
								}else{
									//printing_process_error($r['info']);
									//die();
								}
								//die();
							}
						}	
					
					}else{
						
						if(empty($order_data_other) AND !empty($order_data_other_update)){
							
							
							if($is_void_order == 0){
								$r['info'] .= 'Semua Order Other Sudah diPrint<br/>';
								$r['success'] = true;
								if($print_preview_billing == 0){
									//echo json_encode($r);
								}else{
									$r['info'] = '';
									//printing_process_error($r['info']);
									//die();
								}
								//die();
							}
							
						}else{
							
							if($is_void_order == 0){
								$r['info'] .= 'Belum ada order Other';
								$r['success'] = true;
								if($print_preview_billing == 1){
									//echo json_encode($r);
								}else{
									$r['info'] = '';
									//printing_process_error('');
									//die();
								}
								//die();
							}
						}
					}
				}
				
				//AFTER PRINT - SET STATUS
				if($print_type == 3 OR $print_type == 4 OR $print_type == 5 OR $print_type == -234){
					
					//update-2003.001
					$all_package_id = array();
					$all_package_item_id = array();
					if(!empty($order_data_package_item)){
						foreach($order_data_package_item as $idPack => $dtItem){
							if(!in_array($idPack, $all_package_id)){
								$all_package_id[] = $idPack;
							}
							
							if(!empty($dtItem)){
								foreach($dtItem as $dtI){
									if(!in_array($dtI, $all_package_item_id)){
										$all_package_item_id[] = $dtI;
									}
								}
							}
							
						}
					}
					
					if(!empty($all_package_id)){
						$package_not_done = array();
						$all_package_id_sql = implode(",", $all_package_id);
						$this->db->select("id, ref_order_id, package_item, order_status");
						$this->db->from($this->table2);
						$this->db->where("ref_order_id IN (".$all_package_id_sql.")");
						$this->db->where("package_item = 1");
						$this->db->where("order_status != 'done'");
						$this->db->where("is_deleted = 0");
						$get_item_package = $this->db->get();
						if($get_item_package->num_rows() > 0){
							foreach($get_item_package->result() as $dtItem){
								if(!in_array($dtItem->ref_order_id, $package_not_done)){
									$package_not_done[] = $dtItem->ref_order_id;
								}
							}
						}
						
						$package_is_done = array();
						foreach($all_package_id as $idPack){
							if(!in_array($idPack, $package_not_done)){
								$package_is_done[] = $idPack;
							}
						}
						
						
						if(!empty($package_not_done)){

							$package_not_done_sql = implode(",", $package_not_done);
							$data_update = array(
								'order_status' => 'order'
							);
							$this->db->update($this->table2, $data_update, "id IN (".$package_not_done_sql.")");
							
						}
						
						if(!empty($package_is_done)){

							$package_is_done_sql = implode(",", $package_is_done);
							$data_update = array(
								'order_status' => 'done'
							);
							$this->db->update($this->table2, $data_update, "id IN (".$package_is_done_sql.")");
							
						}
					}
					
					//SAVE ORDER TIMER	
					$r['order_timer'] = $get_opt['order_timer'];
					$r['all_update_id_order'] = $all_update_id_order;
						
					if(!empty($get_opt['order_timer']) AND !empty($all_update_id_order)){
						//check on timer
						$order_data_kitchen_update_txt = implode(",", $all_update_id_order);
						$this->db->select("id, bild_id");
						$this->db->from($this->billing_detail_timer);
						$this->db->where("bild_id IN (".$order_data_kitchen_update_txt.")");
						$get_det = $this->db->get();
						$available_timer = array();
						if($get_det->num_rows() > 0){
							foreach($get_det->result() as $dt){
								if(!in_array($dt->bild_id, $available_timer)){
									$available_timer[] = $dt->bild_id;
								}
							}
						}
						
						$new_det_timer = array();
						foreach($all_update_id_order as $bild_id){
							if(!in_array($bild_id, $available_timer)){
								$new_det_timer[] = array(
									'bild_id'		=> $bild_id,
									'order_start'	=> date("Y-m-d H:i:s"),
									'order_done'	=> NULL,
									'order_time'	=> 0,
									'done_by'		=> '',
									'created'		=>	date('Y-m-d H:i:s'),
									'createdby'		=>	$session_user,
									'updated'		=>	date('Y-m-d H:i:s'),
									'updatedby'		=>	$session_user
								);
							}
						}
						
						if(!empty($new_det_timer)){
							$this->db->insert_batch($this->billing_detail_timer, $new_det_timer);
						}
						
					}
				}
				
				if($is_void_order == 0){
					//die();
				}
				
			}else{
				$r = array('success' => false, 'info' => 'Load data detail gagal, data tidak ditemukan!');
			}
			
		}else{
			$r = array('success' => false, 'info' => 'Load data detail gagal, data tidak ditemukan!');
		}
		
		//echo '<pre>';
		//print_r($r);
		//die();
		
		if(!empty($is_void) AND !empty($void_id)){
			return $r;
		}
		
		if($print_preview_billing == 0){
			echo json_encode($r);
		}else{
			printing_process_error($r['info']);
		}
		
		die();
	}
	
	public function testPrinter($dtParams = array()){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		
		$do_print = $this->input->post_get('do_print', true);	
		$rawbt_check = $this->input->post_get('rawbt_check', true);	
		
		//TIPE
		$printSetting = $this->input->post_get('printSetting', true);	
		if(empty($printSetting)){
			$printSetting = 'cashierReceipt';
		}
		
		$cutting_only = $this->input->post('cutting_only', true);
		
		//update-2009.001
		$opt_value = array(
			'printer_id_'.$printSetting.'_default',
			'printer_id_'.$printSetting.'_'.$ip_addr,
			'print_preview_billing',
		);
		
		if($rawbt_check == 1){
			$opt_value[] = 'merchant_key';
			$opt_value[] = 'is_cloud';
		}

		$get_opt = get_option_value($opt_value);
		
		//update-2008.001
		if(!empty($dtParams)){
			extract($dtParams);
		}
		
		if(empty($session_user) AND empty($rawbt_print)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
				
		$print_preview_billing = 0;
		if(!empty($get_opt['print_preview_billing'])){
			$print_preview_billing = $get_opt['print_preview_billing'];
		}
		
		//ID Printer ----------------------
		$printer_id_test = $get_opt['printer_id_'.$printSetting.'_default'];
		if(!empty($get_opt['printer_id_'.$printSetting.'_'.$ip_addr])){
			$printer_id_test = $get_opt['printer_id_'.$printSetting.'_'.$ip_addr];
		}

		//GET PRINTER DATA
		$this->db->from($this->prefix.'printer');		
		$this->db->where("id", $printer_id_test);		
		$get_printer = $this->db->get();

		$data_printer = array();
		$r = array('success' => false, 'info' => 'IP: '.$ip_addr.' tidak dapat melakukan print '.$printSetting, 'ip_addr' => $ip_addr);
			
		if($get_printer->num_rows() > 0){
			$data_printer = $get_printer->row_array();
		}else{
			echo json_encode($r);
			die();
		}	
		
		$printer_device = $data_printer['printer_ip'];			
		if(strstr($printer_device, '\\')){
			$printer_device = "\\\\".$printer_device;
		}	

		$printer_pin = $data_printer['printer_pin'];
		$printer_tipe = $data_printer['printer_tipe'];
		
		//update-2001.002
		$print_content = " TEST: ".$printSetting."\n TO PRINTER: ".$printer_device."\n FROM IP ".$ip_addr."\n\n";
		if($cutting_only == true){
			$print_content = "\n";
		}
		
		$is_print_error = false;
		
		//update-2008.001
		if($data_printer['print_method'] == 'RAWBT' AND !empty($rawbt_check)){
			$r['success'] = true;
			$r['info'] = '';
			$r['rawbt_print'] = 1;
			$r['url_print'] = BASE_URL.'cashier/rawbt/testPrinter/'.$printSetting.'.txt';
			
			//update-2009.001
			if(!empty($get_opt['merchant_key']) AND !empty($get_opt['is_cloud'])){
				$r['url_print'] = BASE_URL.'cashier/rawbt/testPrinter/'.$printSetting.'/'.$get_opt['merchant_key'].'.txt';
			}
			
			echo json_encode($r);
			die();
		}
		
		if(!empty($return_data)){
			return $print_content;
		}
		
		if($data_printer['print_method'] == 'ESC/POS'){
			
			try {
				@$ph = printer_open($printer_device);
			} catch (Exception $e) {
				$ph = false;
			}
			
			if($ph)
			{
				printer_start_doc($ph, "TEST PRINTER ".ucwords($printSetting));
				printer_start_page($ph);
				printer_set_option($ph, PRINTER_MODE, "RAW");
				printer_write($ph, $print_content);
				printer_end_page($ph);
				printer_end_doc($ph);
				printer_close($ph);
				
			}else{
				$is_print_error = true;
			}
			
			$data_printer['escpos_pass'] = 1;
			$r['success'] = true;
			$r['info'] = '';
			
			if($is_print_error){					
				$r['info'] .= 'Komunikasi dengan Printer Gagal!<br/>';
				$r['success'] = false;
				if(empty($print_preview_billing)){
					echo json_encode($r);
				}else{
					printing_process_error($r['info']);
				}
				die();
			}
			
		}
		
		if($data_printer['print_method'] == 'RAWBT' AND !empty($rawbt_print)){
			$print_content .= "\n";
			$print_content .= "\n";
			echo $print_content;
			die();
		}
		
		//update-2003.001
		if(empty($print_preview_billing)){
			echo json_encode($r);
			die();
		}
				
		printing_process($data_printer, $print_content, 'print');
		
	}
	
	public function loadingSetting(){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$opt_value = array(
			'printer_ip_cashierReceipt_default',
			'printer_ip_cashierReceipt_'.$ip_addr,
			'printer_pin_cashierReceipt_'.$ip_addr,
			'printer_tipe_cashierReceipt_'.$ip_addr,
			'printer_id_cashierReceipt_'.$ip_addr,
			
			'printer_ip_qcReceipt_default',
			'do_print_qcReceipt_'.$ip_addr,
			'printer_ip_qcReceipt_'.$ip_addr,
			'printer_pin_qcReceipt_'.$ip_addr,
			'printer_tipe_qcReceipt_'.$ip_addr,
			'printer_id_qcReceipt_'.$ip_addr,
			
			'printer_ip_kitchenReceipt_default',
			'do_print_kitchenReceipt_'.$ip_addr,
			'printer_ip_kitchenReceipt_'.$ip_addr,
			'printer_pin_kitchenReceipt_'.$ip_addr,
			'printer_tipe_kitchenReceipt_'.$ip_addr,
			'printer_id_kitchenReceipt_'.$ip_addr,
			
			'printer_ip_barReceipt_default',
			'do_print_barReceipt_'.$ip_addr,
			'printer_ip_barReceipt_'.$ip_addr,
			'printer_pin_barReceipt_'.$ip_addr,
			'printer_tipe_barReceipt_'.$ip_addr,
			'printer_id_barReceipt_'.$ip_addr,
			
			'printer_ip_otherReceipt_default',
			'do_print_otherReceipt_'.$ip_addr,
			'printer_ip_otherReceipt_'.$ip_addr,
			'printer_pin_otherReceipt_'.$ip_addr,
			'printer_tipe_otherReceipt_'.$ip_addr,
			'printer_id_otherReceipt_'.$ip_addr
		);
		$get_opt = get_option_value($opt_value);
		
		//GET PRINTER DATA
		$this->db->from($this->prefix.'printer');		
		$this->db->where("is_deleted", 0);		
		$get_printer = $this->db->get();

		$data_printer = array();
		if($get_printer->num_rows() > 0){
			foreach($get_printer->result_array() as $dt){
				$data_printer[$dt['id']] = $dt; 
			}
		}
		
		//Cashier Receipt ----------		
		$cashierReceipt = array(
			'use_local_default_printer'	=> true,
			'printer_ip'	=> '',
			'printer_pin'	=> '',
			'printer_tipe'	=> '',
			'printer_name'	=> '',
			'print_method'	=> '',
		);
		
		//$printer_ip_cashierReceipt = $ip_addr.'\\'.$get_opt['printer_ip_cashierReceipt_default'];
		$printer_ip_cashierReceipt = $get_opt['printer_ip_cashierReceipt_default'];
		if(!empty($get_opt['printer_ip_cashierReceipt_'.$ip_addr])){
			$printer_ip_cashierReceipt = $get_opt['printer_ip_cashierReceipt_'.$ip_addr];
			$cashierReceipt['use_local_default_printer'] = false;
		}else{
			$cashierReceipt['use_local_default_printer'] = true;
		}
		
		$cashierReceipt['printer_ip'] = $printer_ip_cashierReceipt;					
		if(!empty($get_opt['printer_pin_cashierReceipt_'.$ip_addr])){
			$cashierReceipt['printer_pin']  = $get_opt['printer_pin_cashierReceipt_'.$ip_addr];
		}
						
		if(!empty($get_opt['printer_tipe_cashierReceipt_'.$ip_addr])){
			$cashierReceipt['printer_tipe']  = $get_opt['printer_tipe_cashierReceipt_'.$ip_addr];
		}
		
		$getPrinter = array();
		if(!empty($get_opt['printer_id_cashierReceipt_'.$ip_addr])){
			$printer_id = $get_opt['printer_id_cashierReceipt_'.$ip_addr];
			if(!empty($data_printer[$printer_id])){
				$getPrinter = $data_printer[$printer_id];
				$cashierReceipt['printer_pin'] = $getPrinter['printer_pin'];
				$cashierReceipt['printer_tipe'] = $getPrinter['printer_tipe'];
				$cashierReceipt['printer_name'] = $getPrinter['printer_name'];
				$cashierReceipt['print_method'] = $getPrinter['print_method'];
			}
		}
		
		//-------- Cashier Receipt
		
		//QC Receipt -------		
		$qcReceipt = array(
			'use_local_default_printer'	=> true,
			'print_qcReceipt'	=> '',
			'printer_ip'	=> '',
			'printer_pin'	=> '',
			'printer_tipe'	=> '',
			'printer_name'	=> '',
			'print_method'=> '',
		);
		
		$printer_ip_qcReceipt = $ip_addr.'\\'.$get_opt['printer_ip_qcReceipt_default'];
		if(!empty($get_opt['printer_ip_qcReceipt_'.$ip_addr])){
			$printer_ip_qcReceipt = $get_opt['printer_ip_qcReceipt_'.$ip_addr];
			$qcReceipt['use_local_default_printer'] = false;
		}else{
			$qcReceipt['use_local_default_printer'] = true;
		}
		
		if(!empty($get_opt['do_print_qcReceipt_'.$ip_addr])){
			$qcReceipt['print_qcReceipt'] = true;
		}else{
			$qcReceipt['print_qcReceipt'] = false;
		}
		
		$qcReceipt['printer_ip'] = $printer_ip_qcReceipt;					
		if(!empty($get_opt['printer_pin_qcReceipt_'.$ip_addr])){
			$qcReceipt['printer_pin']  = $get_opt['printer_pin_qcReceipt_'.$ip_addr];
		}				
		if(!empty($get_opt['printer_tipe_qcReceipt_'.$ip_addr])){
			$qcReceipt['printer_tipe']  = $get_opt['printer_tipe_qcReceipt_'.$ip_addr];
		}
		
		$getPrinter = array();
		if(!empty($get_opt['printer_id_qcReceipt_'.$ip_addr])){
			$printer_id = $get_opt['printer_id_qcReceipt_'.$ip_addr];
			if(!empty($data_printer[$printer_id])){
				$getPrinter = $data_printer[$printer_id];
				$qcReceipt['printer_pin'] = $getPrinter['printer_pin'];
				$qcReceipt['printer_tipe'] = $getPrinter['printer_tipe'];
				$qcReceipt['printer_name'] = $getPrinter['printer_name'];
				$qcReceipt['print_method'] = $getPrinter['print_method'];
			}
		}
		//------- QC Receipt
		
		//Kitchen Receipt -------
		$kitchenReceipt = array(
			'use_local_default_printer'	=> true,
			'print_kitchenReceipt'	=> '',
			'printer_ip'	=> '',
			'printer_pin'	=> '',
			'printer_tipe'	=> '',
			'printer_name'	=> '',
			'print_method'=> '',
		);
		
		$printer_ip_kitchenReceipt = $ip_addr.'\\'.$get_opt['printer_ip_kitchenReceipt_default'];
		if(!empty($get_opt['printer_ip_kitchenReceipt_'.$ip_addr])){
			$printer_ip_kitchenReceipt = $get_opt['printer_ip_kitchenReceipt_'.$ip_addr];
			$kitchenReceipt['use_local_default_printer'] = false;
		}else{
			$kitchenReceipt['use_local_default_printer'] = true;
		}
		
		if(!empty($get_opt['do_print_kitchenReceipt_'.$ip_addr])){
			$kitchenReceipt['print_kitchenReceipt'] = true;
		}else{
			$kitchenReceipt['print_kitchenReceipt'] = false;
		}
		
		$kitchenReceipt['printer_ip'] = $printer_ip_kitchenReceipt;					
		if(!empty($get_opt['printer_pin_kitchenReceipt_'.$ip_addr])){
			$kitchenReceipt['printer_pin']  = $get_opt['printer_pin_kitchenReceipt_'.$ip_addr];
		}				
		if(!empty($get_opt['printer_tipe_kitchenReceipt_'.$ip_addr])){
			$kitchenReceipt['printer_tipe']  = $get_opt['printer_tipe_kitchenReceipt_'.$ip_addr];
		}
		
		$getPrinter = array();
		if(!empty($get_opt['printer_id_kitchenReceipt_'.$ip_addr])){
			$printer_id = $get_opt['printer_id_kitchenReceipt_'.$ip_addr];
			if(!empty($data_printer[$printer_id])){
				$getPrinter = $data_printer[$printer_id];
				$kitchenReceipt['printer_pin'] = $getPrinter['printer_pin'];
				$kitchenReceipt['printer_tipe'] = $getPrinter['printer_tipe'];
				$kitchenReceipt['printer_name'] = $getPrinter['printer_name'];
				$kitchenReceipt['print_method'] = $getPrinter['print_method'];
			}
		}
		//------- Kitchen Receipt
		
		//Bar Receipt -------
		$barReceipt = array(
			'use_local_default_printer'	=> true,
			'print_barReceipt'	=> '',
			'printer_ip'	=> '',
			'printer_pin'	=> '',
			'printer_tipe'	=> '',
			'printer_name'	=> '',
			'print_method'=> '',
		);
		
		$printer_ip_barReceipt = $ip_addr.'\\'.$get_opt['printer_ip_barReceipt_default'];
		if(!empty($get_opt['printer_ip_barReceipt_'.$ip_addr])){
			$printer_ip_barReceipt = $get_opt['printer_ip_barReceipt_'.$ip_addr];
			$barReceipt['use_local_default_printer'] = false;
		}else{
			$barReceipt['use_local_default_printer'] = true;
		}
		
		if(!empty($get_opt['do_print_barReceipt_'.$ip_addr])){
			$barReceipt['print_barReceipt'] = true;
		}else{
			$barReceipt['print_barReceipt'] = false;
		}
		
		$barReceipt['printer_ip'] = $printer_ip_barReceipt;					
		if(!empty($get_opt['printer_pin_barReceipt_'.$ip_addr])){
			$barReceipt['printer_pin']  = $get_opt['printer_pin_barReceipt_'.$ip_addr];
		}				
		if(!empty($get_opt['printer_tipe_barReceipt_'.$ip_addr])){
			$barReceipt['printer_tipe']  = $get_opt['printer_tipe_barReceipt_'.$ip_addr];
		}
		
		$getPrinter = array();
		if(!empty($get_opt['printer_id_barReceipt_'.$ip_addr])){
			$printer_id = $get_opt['printer_id_barReceipt_'.$ip_addr];
			if(!empty($data_printer[$printer_id])){
				$getPrinter = $data_printer[$printer_id];
				$barReceipt['printer_pin'] = $getPrinter['printer_pin'];
				$barReceipt['printer_tipe'] = $getPrinter['printer_tipe'];
				$barReceipt['printer_name'] = $getPrinter['printer_name'];
				$barReceipt['print_method'] = $getPrinter['print_method'];
			}
		}
		//------- Bar Receipt
		
		//Other Receipt -------
		$otherReceipt = array(
			'use_local_default_printer'	=> true,
			'print_otherReceipt'	=> '',
			'printer_ip'	=> '',
			'printer_pin'	=> '',
			'printer_tipe'	=> '',
			'printer_name'	=> '',
			'print_method'=> '',
		);
		
		$printer_ip_otherReceipt = $ip_addr.'\\'.$get_opt['printer_ip_otherReceipt_default'];
		if(!empty($get_opt['printer_ip_otherReceipt_'.$ip_addr])){
			$printer_ip_otherReceipt = $get_opt['printer_ip_otherReceipt_'.$ip_addr];
			$otherReceipt['use_local_default_printer'] = false;
		}else{
			$otherReceipt['use_local_default_printer'] = true;
		}
		
		if(!empty($get_opt['do_print_otherReceipt_'.$ip_addr])){
			$otherReceipt['print_otherReceipt'] = true;
		}else{
			$otherReceipt['print_otherReceipt'] = false;
		}
		
		$otherReceipt['printer_ip'] = $printer_ip_otherReceipt;					
		if(!empty($get_opt['printer_pin_otherReceipt_'.$ip_addr])){
			$otherReceipt['printer_pin']  = $get_opt['printer_pin_otherReceipt_'.$ip_addr];
		}					
		if(!empty($get_opt['printer_tipe_otherReceipt_'.$ip_addr])){
			$otherReceipt['printer_tipe']  = $get_opt['printer_tipe_otherReceipt_'.$ip_addr];
		}
		
		$getPrinter = array();
		if(!empty($get_opt['printer_id_otherReceipt_'.$ip_addr])){
			$printer_id = $get_opt['printer_id_otherReceipt_'.$ip_addr];
			if(!empty($data_printer[$printer_id])){
				$getPrinter = $data_printer[$printer_id];
				$otherReceipt['printer_pin'] = $getPrinter['printer_pin'];
				$otherReceipt['printer_tipe'] = $getPrinter['printer_tipe'];
				$otherReceipt['printer_name'] = $getPrinter['printer_name'];
				$otherReceipt['print_method'] = $getPrinter['print_method'];
			}
		}
		//------- Bar Receipt
		
		$returnData = array(
			'success' => true,
			'IP'	=> $ip_addr,
			'cashierReceipt' => $cashierReceipt,
			'qcReceipt' => $qcReceipt,
			'kitchenReceipt' => $kitchenReceipt,
			'barReceipt' => $barReceipt,
			'otherReceipt' => $otherReceipt,
		);
		
		die(json_encode($returnData));
	}
	/*print_MultipleQC*/
	public function print_MultipleQC(){
		
		$this->prefix = config_item('db_prefix');
		$this->table = $this->prefix.'options';
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$opt_var = array('show_multiple_print_qc');
		$get_opt = get_option_value($opt_var);
		
		$show_multiple_print_qc = 0;
		if(!empty($get_opt['show_multiple_print_qc'])){
			$show_multiple_print_qc = $get_opt['show_multiple_print_qc'];
		}
		
		
		if($show_multiple_print_qc == 0){
			$r = array('success' => false, 'info' => 'Opsi Print Multiple QC tidak aktif!');
			echo json_encode($r);
			die();
		}
		
		$this->db->from($this->table);
		$this->db->where("option_var = 'multiple_print_qc'");
		$get_opt = $this->db->get();
		
		$data_opt = array();
		$r = array('success' => true, 'total_printer' => 0, 'data_printer' => $data_opt);
		
		if($get_opt->num_rows() > 0){
			
			$data_opt = $get_opt->result();
			$r = array('success' => true, 'total_printer' => $get_opt->num_rows(), 'data_printer' => $data_opt);
		
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
		
	}
	
	/*print_MultipleBilling*/
	public function print_MultipleBilling(){
		
		$this->prefix = config_item('db_prefix');
		$this->table = $this->prefix.'options';
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$opt_var = array('show_multiple_print_billing');
		$get_opt = get_option_value($opt_var);
		
		$show_multiple_print_billing = 0;
		if(!empty($get_opt['show_multiple_print_billing'])){
			$show_multiple_print_billing = $get_opt['show_multiple_print_billing'];
		}
		
		
		if($show_multiple_print_billing == 0){
			$r = array('success' => false, 'info' => 'Opsi Print Multiple Billing tidak aktif!');
			echo json_encode($r);
			die();
		}
		
		$this->db->from($this->table);
		$this->db->where("option_var = 'multiple_print_billing'");
		$get_opt = $this->db->get();
		
		$data_opt = array();
		$r = array('success' => true, 'total_printer' => 0, 'data_printer' => $data_opt);
		
		if($get_opt->num_rows() > 0){
			
			$data_opt = $get_opt->result();
			$r = array('success' => true, 'total_printer' => $get_opt->num_rows(), 'data_printer' => $data_opt);
		
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
		
	}
	
	
	public function printSettlement($params = array()){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		
		//backup rekap trx
		
		$get_date = $this->input->post_get('date');
		$reprint = $this->input->post_get('reprint');
		$show_txmark = $this->input->post_get('show_txmark');
		$test = $this->input->post_get('test', true);	
		$pershift = $this->input->post_get('pershift', true);
		$return_data = $this->input->post_get('return_data', true);
		$rawbt_check = $this->input->post_get('rawbt_check', true);	
		
		if(!empty($params)){
			extract($params);
		}
		
		if(empty($session_user) AND empty($rawbt_print)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$r = array('success' => false);
		
		$opt_value = array(
			'cashierReceipt_settlement_layout',
			'printer_ip_cashierReceipt_default',
			'printer_pin_cashierReceipt_default',
			'printer_tipe_cashierReceipt_default',
			'printer_id_cashierReceipt_default',
			'printer_id_cashierReceipt_'.$ip_addr,
			'print_preview_billing',
			'report_place_default','diskon_sebelum_pajak_service',
			'cashier_max_pembulatan','cashier_pembulatan_keatas','pembulatan_dinamis',
			'jam_operasional_from','jam_operasional_to','jam_operasional_extra','jumlah_shift','settlement_per_shift',
			'nontrx_override_on'
		);
		
		if($rawbt_check == 1){
			$opt_value[] = 'merchant_key';
			$opt_value[] = 'is_cloud';
		}

		$get_opt = get_option_value($opt_value);
		
		//update-2007.001
		if(!empty($get_opt['nontrx_override_on'])){
			$show_txmark = 1;
		}
		
		$print_preview_billing = 0;
		if(!empty($get_opt['print_preview_billing'])){
			$print_preview_billing = 1;
		}
		
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

		if(!empty($print_anywhere)){
			$printer_type_cashier = $print_anywhere->printer_tipe;
		}

		$cashierReceipt_settlement_layout = $get_opt['cashierReceipt_settlement_layout'];
		if(!empty($print_type)){
			$cashierReceipt_settlement_layout = $get_opt['cashierReceipt_settlement_layout'];
		}

		$printer_pin_cashierReceipt = trim(str_replace("CHAR", "", $printer_pin_cashierReceipt));

		$no_limit_text = false;
		if($data_printer['print_method'] == 'ESC/POS'){
			//$no_limit_text = false;
		}
		
		//trim prod name
		$max_text = 18; //44
		$max_number_1 = 9;
		$max_number_2 = 11;
		$max_number_3 = 13;

		if($printer_pin_cashierReceipt == 32){
			$max_text -= 6;
			$max_number_1 = 7;
			$max_number_2 = 8;
			$max_number_3 = 13;
		}
		if($printer_pin_cashierReceipt == 40){
			$max_text -= 2;
			$max_number_1 = 8;
			$max_number_2 = 11;
			$max_number_3 = 13;
		}
		if($printer_pin_cashierReceipt == 42){
			//$max_text -= 2;
			$max_number_1 = 8;
			$max_number_2 = 11;
			$max_number_3 = 13;
		}
		if($printer_pin_cashierReceipt == 46){
			$max_text += 2;
			$max_number_1 = 10;
			$max_number_2 = 12;
			$max_number_3 = 13;
		}
		if($printer_pin_cashierReceipt == 48){
			$max_text += 4;
			$max_number_1 = 10;
			$max_number_2 = 12;
			$max_number_3 = 13;
		}
		
		//TOTAL BILLING - SSR
		$data_post = array();
		$this->table_billing = $this->prefix.'billing';
		$this->table_billing_detail = $this->prefix.'billing_detail';
		$this->table_print_monitoring = $this->prefix.'print_monitoring';
		
		//update-1912-001
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		if(!empty($get_opt['diskon_sebelum_pajak_service'])){
			$data_post['diskon_sebelum_pajak_service'] = $get_opt['diskon_sebelum_pajak_service'];
		}else{
			$data_post['diskon_sebelum_pajak_service'] = 0;
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
		
		//update-1912-001
		$jumlah_shift = 1;
		if(!empty($get_opt['jumlah_shift'])){
			$jumlah_shift = $get_opt['jumlah_shift'];
		}
		
		$settlement_per_shift = 0;
		if(!empty($get_opt['settlement_per_shift'])){
			$settlement_per_shift = $get_opt['settlement_per_shift'];
		}
		
		//update-2007.001
		if($test == 1 OR $show_txmark == 1){
			$settlement_per_shift = 0;
			if($pershift == 1){
				$settlement_per_shift = 1;
			}
		}
		
		$date_from = date("d-m-Y");
		$date_till = date("d-m-Y");
		
		//STILL ON CURR DAY
		$billing_time = date('G');
		$datenowstr = strtotime(date("d-m-Y H:i:s"));
		
		$start_newbilling = 4;
		if(!empty($get_opt['jam_operasional_from'])){
			$getdate_billing_time = strtotime(date("d-m-Y ".$get_opt['jam_operasional_from'].":00"));
			$start_newbilling = date('G', $getdate_billing_time);
		}
		if($billing_time < $start_newbilling){
			$datenowstr = strtotime(date("d-m-Y H:i:s"))-ONE_DAY_UNIX;
			$date_from = date("d-m-Y", $datenowstr);
			$date_till = date("d-m-Y", $datenowstr);
		}
		
		$mktime_dari = strtotime($date_from);
		$mktime_sampai = strtotime($date_till);
		
		//TXMARK
		//if((!empty($get_date) AND !empty($show_txmark)) or (!empty($get_date) AND !empty($test))){
		if(!empty($get_date)){
			$mktime_dari = strtotime($get_date);
			$mktime_sampai = strtotime($get_date);
			$datenowstr = strtotime($get_date);
			$date_from = date("d-m-Y", $datenowstr);
			$date_till = date("d-m-Y", $datenowstr);
		}
				
		$ret_dt = check_report_jam_operasional($get_opt, $mktime_dari, $mktime_sampai);
						
		//$qdate_from = date("Y-m-d",strtotime($date_from));
		//$qdate_till = date("Y-m-d",strtotime($date_till));
		//$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
		//$add_where = "(a.payment_date >= '".$qdate_from." 07:00:01' AND a.payment_date <= '".$qdate_till_max." 06:00:00')";
			
		//update-2008.001
		if($data_printer['print_method'] == 'RAWBT' AND !empty($rawbt_check)){
			if(!empty($test) OR !empty($return_data)){
				
			}else{
				
				$get_datex = date("dmY", $mktime_dari);
				$r['success'] = true;
				$r['info'] = '';
				$r['rawbt_print'] = 1;
				//$param_url = 'date='.$xget_date.'&reprint='.$reprint.'&show_txmark='.$show_txmark.'&pershift='.$pershift.'&rawbt_print='.$rawbt_print;
				
				$reprintx = 0;
				if(!empty($reprint)){
					$reprintx = $reprint;
				}
				$show_txmarkx = 0;
				if(!empty($show_txmark)){
					$show_txmarkx = $show_txmark;
				}
				$pershiftx = 0;
				if(!empty($pershift)){
					$pershiftx = $pershift;
				}
				$r['url_print'] = BASE_URL.'cashier/rawbt/printSettlement/settlement-'.$get_datex.'-'.$reprintx.'-'.$show_txmarkx.'-'.$pershiftx.'.txt';
				
				//update-2009.001
				if(!empty($get_opt['merchant_key']) AND !empty($get_opt['is_cloud'])){
					$r['url_print'] = BASE_URL.'cashier/rawbt/printSettlement/settlement-'.$get_datex.'-'.$reprintx.'-'.$show_txmarkx.'-'.$pershiftx.'/'.$get_opt['merchant_key'].'.txt';
				}
				
				echo json_encode($r);
				die();
			}
		}
		
		//update-1912-001
		//SHIFT
		$nama_shift = '-';
		$tanggal_cetak = date("d/m/Y"); //d/m/Y
		$jam_cetak = date("H:i");
		$user_shift = 1;
		if($jumlah_shift > 1 AND $settlement_per_shift == 1){
			$tanggal_shift = date("Y-m-d", $datenowstr);
			$this->db->select('a.*, b.nama_shift');
			$this->db->from($this->prefix.'shift_log as a');
			$this->db->join($this->prefix.'shift as b',"b.id = a.user_shift","LEFT");
			$this->db->where("a.tanggal_shift", $tanggal_shift);
			$this->db->order_by("a.id", 'DESC');
			$getShiftLog = $this->db->get();
			if($getShiftLog->num_rows() > 0){
				$dataShiftLog = $getShiftLog->row_array();
				
				$tanggal_jam_start = $dataShiftLog['tanggal_jam_start'];
				$jam_shift_end = $dataShiftLog['jam_shift_end'];
				if(empty($jam_shift_end)){
					$jam_shift_end = date("H:i", $datenowstr);
				}
				$nama_shift = $dataShiftLog['nama_shift'];
				$jam_cetak = $jam_shift_end;
				
				//$qdate_from = $tanggal_jam_start;
				//$qdate_till = $tanggal_jam_end;
				//$qdate_till_max = $tanggal_jam_end;
				
				$user_shift = $dataShiftLog['user_shift'];
			}
		}
		
		//laporan = jam_operasional
		$qdate_from = $ret_dt['qdate_from'];
		$qdate_till = $ret_dt['qdate_till'];
		$qdate_till_max = $ret_dt['qdate_till_max'];
		
		//update-1912-001
		$add_where = "(a.payment_date >= '".$qdate_from."' AND a.payment_date <= '".$qdate_till_max."')";
		if($jumlah_shift > 1 AND $settlement_per_shift == 1){
			$add_where = "(a.payment_date >= '".$qdate_from."' AND a.payment_date <= '".$qdate_till_max."') AND shift = ".$user_shift;
		}
		
		$this->db->select("a.*, a.id as billing_id, a.updated as billing_date, d.payment_type_name, e.bank_name");
		$this->db->from($this->table_billing." as a");
		$this->db->join($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT');
		$this->db->join($this->prefix.'bank as e','e.id = a.bank_id','LEFT');
		$this->db->where("a.billing_status", 'paid');
		$this->db->where("a.is_deleted", 0);
		$this->db->where($add_where);
		
		//TXMARK
		if(!empty($show_txmark)){
			$this->db->where("a.txmark", 1);
		}
		
		//update-2001.002
		$this->db->order_by("a.payment_id","ASC");
		
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
		
		//update-2003.001
		$all_bil_id = array();
		if(!empty($data_post['report_data'])){
			foreach ($data_post['report_data'] as $s){
				
				if(!in_array($s['id'], $all_bil_id)){
					$all_bil_id[] = $s['id'];
				}		
				
			}
		}
		
		//update-2003.001
		$total_billing = array();
		if(!empty($all_bil_id)){
			$all_bil_id_txt = implode(",",$all_bil_id);
			$this->db->from($this->table_billing_detail);
			$this->db->where('billing_id IN ('.$all_bil_id_txt.')');
			$this->db->where('is_deleted', 0);
			$this->db->where("(ref_order_id IS NULL OR ref_order_id = 0)");
			$get_detail = $this->db->get();
			if($get_detail->num_rows() > 0){
				foreach($get_detail->result() as $dtRow){
					
					$total_qty = $dtRow->order_qty;
					
					//update-2003.001
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
		
		//$all_bil_id = array();
		$all_discount_id = array();
		$summary_payment = array();
		$summary_payment[0] = array(
			'payment_id'	=> 1,
			'payment_name'	=> 'CASH',
			'bank_id'	=> 0,
			'bank_name'	=> 'CASH',
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
		);
		
		$konversi_pembulatan_billing = array();
		$balancing_discount_billing = array();
		
		$data_post['summary_data'] = array(
			'total_billing'	=> 0,
			'total_discount_item'	=> 0,
			'total_discount_billing'	=> 0,
			'net_sales'	=> 0,
			'service_total'	=> 0,
			'tax_total'	=> 0,
			'total_pembulatan'	=> 0,
			'compliment_total'	=> 0,
			'grand_total'	=> 0,
			'total_of_item_discount'	=> 0,
			'total_of_billing'	=> 0,
			'total_of_guest'	=> 0,
			'total_day'	=> 1,
			'sales_without_service'	=> 0,
			'sales_without_tax'	=> 0,
			'sales_per_guest'	=> 0,
			'sales_per_bill'	=> 0,
			'average_daily_guest'	=> 0,
			'average_daily_billing'	=> 0,
			'average_daily_sales'	=> 0,
			'total_dp'	=> 0,
		);
		
		if(!empty($data_post['report_data'])){
			foreach ($data_post['report_data'] as $s){
				$s['billing_date'] = date("d-m-Y H:i",strtotime($s['created']));					
				$s['payment_date'] = date("d-m-Y H:i",strtotime($s['payment_date']));
				
				//if(!in_array($s['id'], $all_bil_id)){
				//	$all_bil_id[] = $s['id'];
				//}		
				
				$s['total_billing_awal'] = $s['total_billing'];
				
				//update-2003.001
				//CHECK REAL TOTAL BILLING
				if(!empty($s['include_tax']) OR !empty($s['include_service'])){
					//update-2003.001
					$s['total_billing'] = $total_billing[$s['id']];
					$s['total_billing_awal'] = $s['total_billing'];
				}
					
				//CHECK REAL TOTAL BILLING
				/*if(!empty($s['include_tax']) OR !empty($s['include_service'])){
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
				}*/
				
				if(!empty($s['is_compliment'])){
					//$s['total_billing'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
					if($s['total_billing'] <= $s['compliment_total']){
						$s['service_total'] = 0;
						$s['tax_total'] = 0;
					}
				}
				
				//diskon_sebelum_pajak_service
				if($data_post['diskon_sebelum_pajak_service'] == 1){
					$s['sub_total'] = $s['total_billing'] - $s['discount_total'] + $s['tax_total'] + $s['service_total']- $s['compliment_total'];
					$s['net_sales'] = $s['total_billing'] - $s['discount_total'] - $s['compliment_total'];
					
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
					
					//GRAND TOTAL
					$s['grand_total'] = $s['sub_total'];
					
				}else{
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
				
				//if(!empty($s['include_tax']) OR !empty($s['include_service'])){
				//	$s['sub_total'] = $s['total_billing'];
				//}
				
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
					//$s['is_compliment'] = 1;
				}
				
				if(!empty($s['is_half_payment'])){
					if(!empty($s['payment_note'])){
						$s['payment_note'] .= ', ';
					}
					$s['payment_note'] .= 'HALF PAYMENT';
				}
				
				if(strtolower($s['payment_type_name']) != 'cash'){
					if(!empty($s['payment_note'])){
						$s['payment_note'] .= '<br/>';
					}
					$s['payment_note'] .= strtoupper($s['payment_type_name']) .': '.strtoupper($s['bank_name']).' '.$card_no;
				}
				
				if(!empty($s['billing_notes'])){
					if(!empty($s['payment_note'])){
						$s['payment_note'] .= '<br/>';
					}
					$s['payment_note'] .= $s['billing_notes'];
				}
				
				$data_post['summary_data']['total_billing'] += $s['total_billing'];
				$data_post['summary_data']['total_discount_item'] += $s['discount_total'];
				$data_post['summary_data']['total_discount_billing'] += $s['discount_billing_total'];
				$data_post['summary_data']['net_sales'] += $s['net_sales'];
				$data_post['summary_data']['total_dp'] += $s['total_dp'];
				$data_post['summary_data']['service_total'] += $s['service_total'];
				$data_post['summary_data']['tax_total'] += $s['tax_total'];
				$data_post['summary_data']['total_pembulatan'] += $s['total_pembulatan'];
				$data_post['summary_data']['compliment_total'] += $s['compliment_total'];
				$data_post['summary_data']['grand_total'] += $s['grand_total'];
				$data_post['summary_data']['total_of_guest'] += $s['total_guest'];
				$data_post['summary_data']['total_of_billing'] += 1;
				
				if($s['service_total'] == 0){
					$data_post['summary_data']['sales_without_service'] += $s['grand_total'];
				}
				if($s['tax_total'] == 0){
					$data_post['summary_data']['sales_without_tax'] += $s['grand_total'];
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
					
					//update AR - 2019-02-15
					$bank_name = 'CASH';
					if(!empty($bank_data[$s['bank_id']])){
						$bank_name = $bank_data[$s['bank_id']];
					}
					
					$payment_name = 'CASH';
					if(!empty($dt_payment_name[$s['payment_id']])){
						$payment_name = $dt_payment_name[$s['payment_id']];
						
						if($s['payment_id'] == 4){
							//$bank_name = 'AR / PIUTANG';
						}
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
				$summary_payment[$var_payment]['grand_total'] += $s['grand_total'];
				$summary_payment[$var_payment]['total_compliment'] += $s['total_compliment'];
				$summary_payment[$var_payment]['compliment_total'] += $s['compliment_total'];
				$summary_payment[$var_payment]['total_dp'] += $s['total_dp'];
				
				
				if(!empty($payment_data)){
					foreach($payment_data as $key_id => $dtPay){
				
						$tot_payment = 0;
						//update-1912-001
						$tot_payment_halfpayment = 0; 
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
								$tot_payment_halfpayment = $s['total_cash'];
								//$tot_payment_show = priceFormat($s['total_credit']);
								$tot_payment_show = priceFormat($tot_payment+$tot_payment_halfpayment);
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
						
						$summary_payment[$var_payment]['payment_'.$key_id] += $tot_payment;
						if(!empty($tot_payment_halfpayment)){
							$summary_payment[0]['payment_1'] += $tot_payment_halfpayment;
						}
					}
				}
				
				//BALANCING DISKON
				if(!empty($s['billing_discount_total'])){
					if(empty($balancing_discount_billing[$s['billing_id']])){
						$balancing_discount_billing[$s['billing_id']] = array(
							'discount_total'	=> $s['billing_discount_total'],
							'discount_detail_total'	=> 0,
							'payment_id'	=> 0,
							'bank_id'	=> 0,
							'discount_perbilling'	=> $s['discount_perbilling'],
							'discount_detail'	=> array(),
							'billing_date'	=> $s['billing_date']
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
					$balancing_discount_billing[$s['billing_id']]['bank_id'] = $s['bank_id'];
				}
				
				//$newData[$s['id']] = $s;
				if(!empty($total_billing)){
					//KONVERSI PEMBULATAN PER-ITEM
					if(empty($konversi_pembulatan_billing[$s['billing_id']])){
						$konversi_pembulatan_billing[$s['billing_id']] = array(
							'total_qty'	=> 0,
							'billing_total_pembulatan'	=> $s['billing_total_pembulatan'],
							'total_pembulatan_product'	=> array(),
							'billing_date'	=> $s['billing_date']
						);
					}
					
					$konversi_pembulatan_billing[$s['billing_id']]['total_qty'] += $s['order_qty'];
					if(empty($konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']])){
						$konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']] = array(
							'total_pembulatan'	=> 0,
							'payment'	=> array(),
							'bank'	=> array()
						);
					}
					$konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']]['total_pembulatan'] = $total_pembulatan;
					if(!empty($s['payment_id'])){
						if(empty($konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']]['payment'][$s['payment_id']])){
							$konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']]['payment'][$s['payment_id']] = 0;
						}
						$konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']]['payment'][$s['payment_id']] += $total_pembulatan;
					}
					
					//bank_id
					if(!empty($s['bank_id'])){
						if(empty($konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']]['bank'][$s['bank_id']])){
							$konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']]['bank'][$s['bank_id']] = 0;
						}
						$konversi_pembulatan_billing[$s['billing_id']]['total_pembulatan_product'][$s['product_id']]['bank'][$s['bank_id']] += $total_pembulatan;
					}
				}
			}
		}
		
		
			
		//PEMBAGIAN PEMBULATAN AVERAGE
		$konversi_pembulatan_product = array();
		$konversi_pembulatan_product_payment = array();
		$konversi_pembulatan_product_bank = array();
		$pembulatan_awal_product = array();
		$pembulatan_awal_product_payment = array();
		$pembulatan_awal_product_bank = array();
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
								//'total_pembulatan' => 0
							);
						}
						if(empty($konversi_pembulatan_product[$product_id][$dt['billing_date']])){
							$konversi_pembulatan_product[$product_id][$dt['billing_date']] = array(
								'total_pembulatan' => 0
							);
						}
						if(empty($pembulatan_awal_product[$product_id])){
							$pembulatan_awal_product[$product_id] = array();
						}
						if(empty($pembulatan_awal_product[$product_id][$dt['billing_date']])){
							$pembulatan_awal_product[$product_id][$dt['billing_date']] = 0;
						}
						
						$pembulatan_awal_product[$product_id][$dt['billing_date']] += $data['total_pembulatan'];
						
						$konversi_pembulatan_product[$product_id][$dt['billing_date']]['total_pembulatan'] += $pembagian_pembulatan;
						if($no == 1 AND $selisih_pembagian != 0){
							$konversi_pembulatan_product[$product_id][$dt['billing_date']]['total_pembulatan'] -= $selisih_pembagian;
						}
						
						//PAYMENT
						if(!empty($data['payment'])){
							foreach($data['payment'] as $payment_id => $dtP){
								if(empty($konversi_pembulatan_product_payment[$product_id][$dt['billing_date']])){
									$konversi_pembulatan_product_payment[$product_id][$dt['billing_date']] = array();
								}
								if(empty($konversi_pembulatan_product_payment[$product_id][$dt['billing_date']][$payment_id])){
									$konversi_pembulatan_product_payment[$product_id][$dt['billing_date']][$payment_id] = 0;
								}
								$konversi_pembulatan_product_payment[$product_id][$dt['billing_date']][$payment_id] += $pembagian_pembulatan;
								if($no == 1 AND $selisih_pembagian != 0){
									$konversi_pembulatan_product_payment[$product_id][$dt['billing_date']][$payment_id] -= $selisih_pembagian;
								}
								
								if(empty($pembulatan_awal_product_payment[$product_id][$dt['billing_date']])){
									$pembulatan_awal_product_payment[$product_id][$dt['billing_date']] = array();
								}
								if(empty($pembulatan_awal_product_payment[$product_id][$dt['billing_date']][$payment_id])){
									$pembulatan_awal_product_payment[$product_id][$dt['billing_date']][$payment_id] = 0;
								}
								$pembulatan_awal_product_payment[$product_id][$dt['billing_date']][$payment_id] += $dtP;
								
								
							}
							
						}
						//$konversi_data = $data['total_pembulatan'] - $pembagian_pembulatan;
						
						//BANK
						if(!empty($data['bank'])){
							foreach($data['bank'] as $bank_id => $dtP){
								if(empty($konversi_pembulatan_product_bank[$product_id][$dt['billing_date']])){
									$konversi_pembulatan_product_bank[$product_id][$dt['billing_date']] = array();
								}
								if(empty($konversi_pembulatan_product_bank[$product_id][$dt['billing_date']][$bank_id])){
									$konversi_pembulatan_product_bank[$product_id][$dt['billing_date']][$bank_id] = 0;
								}
								$konversi_pembulatan_product_bank[$product_id][$dt['billing_date']][$bank_id] += $pembagian_pembulatan;
								if($no == 1 AND $selisih_pembagian != 0){
									$konversi_pembulatan_product_bank[$product_id][$dt['billing_date']][$bank_id] -= $selisih_pembagian;
								}
								
								if(empty($pembulatan_awal_product_bank[$product_id][$dt['billing_date']])){
									$pembulatan_awal_product_bank[$product_id][$dt['billing_date']] = array();
								}
								if(empty($pembulatan_awal_product_bank[$product_id][$dt['billing_date']][$bank_id])){
									$pembulatan_awal_product_bank[$product_id][$dt['billing_date']][$bank_id] = 0;
								}
								$pembulatan_awal_product_bank[$product_id][$dt['billing_date']][$bank_id] += $dtP;
								
								
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
		$data_diskon_awal_bank = array();
		$data_balancing_diskon = array();
		$data_balancing_diskon_payment = array();
		$data_balancing_diskon_bank = array();
		$data_selisih_diskon = array();
		$data_selisih_diskon_payment = array();
		$data_selisih_diskon_bank = array();
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
								//'item'	=> 0,
								//'billing'	=> 0
							);
						}
						if(empty($data_balancing_diskon[$product_id])){
							$data_balancing_diskon[$product_id] = array(
								//'item'	=> 0,
								//'billing'	=> 0
							);
						}
						
						if(empty($data_diskon_awal[$product_id][$dt['billing_date']])){
							$data_diskon_awal[$product_id][$dt['billing_date']] = array(
								'item'	=> 0,
								'billing'	=> 0
							);
						}
						if(empty($data_balancing_diskon[$product_id][$dt['billing_date']])){
							$data_balancing_diskon[$product_id][$dt['billing_date']] = array(
								'item'	=> 0,
								'billing'	=> 0
							);
						}
						
						
						if($dt['discount_perbilling'] == 1){
							$data_diskon_awal[$product_id][$dt['billing_date']]['billing'] += $dt_diskon['total_discount'];
						}else{
							$data_diskon_awal[$product_id][$dt['billing_date']]['item'] += $dt_diskon['total_discount'];
						}
						
						if($dt['discount_perbilling'] == 1){
							$data_balancing_diskon[$product_id][$dt['billing_date']]['billing'] += ($dt_diskon['total_discount']+$selisih_diskon_perproduct);
						}else{
							$data_balancing_diskon[$product_id][$dt['billing_date']]['item'] += ($dt_diskon['total_discount']+$selisih_diskon_perproduct);
						}
						
						$balancing_discount_billing[$billing_id]['discount_detail'][$product_id]['total_discount_balance'] = ($dt_diskon['total_discount']+$selisih_diskon_perproduct);
						
						if($no == count($dt['discount_detail'])){
							if($discount_detail_total != $dt['discount_total']){
								$selisih_akhir = $dt['discount_total'] - $discount_detail_total;
								
								if($dt['discount_perbilling'] == 1){
									$data_balancing_diskon[$product_id][$dt['billing_date']]['billing'] += $selisih_akhir;
								}else{
									$data_balancing_diskon[$product_id][$dt['billing_date']]['item'] += $selisih_akhir;
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
								$data_selisih_diskon[$product_id] = array();
							}
							if(empty($data_selisih_diskon[$product_id][$dt['billing_date']])){
								$data_selisih_diskon[$product_id][$dt['billing_date']] = 0;
							}
							
							$data_selisih_diskon[$product_id][$dt['billing_date']] += $sub_total_selisih;
							
							if(empty($data_selisih_diskon_payment[$product_id])){
								$data_selisih_diskon_payment[$product_id] = array();
							}
							if(empty($data_selisih_diskon_payment[$product_id][$dt['billing_date']])){
								$data_selisih_diskon_payment[$product_id][$dt['billing_date']] = array();
							}
							
							if(empty($data_selisih_diskon_payment[$product_id][$dt['billing_date']][$dt['payment_id']])){
								$data_selisih_diskon_payment[$product_id][$dt['billing_date']][$dt['payment_id']] = 0;
							}
							
							//echo $product_id.' -> '.$dt['payment_id'].' <br/>';
							$data_selisih_diskon_payment[$product_id][$dt['billing_date']][$dt['payment_id']] += $sub_total_selisih;
							
							if(empty($data_selisih_diskon_bank[$product_id])){
								$data_selisih_diskon_bank[$product_id] = array();
							}
							if(empty($data_selisih_diskon_bank[$product_id][$dt['billing_date']])){
								$data_selisih_diskon_bank[$product_id][$dt['billing_date']] = array();
							}
							
							if(empty($data_selisih_diskon_bank[$product_id][$dt['billing_date']][$dt['bank_id']])){
								$data_selisih_diskon_bank[$product_id][$dt['billing_date']][$dt['bank_id']] = 0;
							}
							
							//echo $product_id.' -> '.$dt['bank_id'].' <br/>';
							$data_selisih_diskon_bank[$product_id][$dt['billing_date']][$dt['bank_id']] += $sub_total_selisih;
							
						}
					}
				}
			}
		}
		
		//GROUP PAYMENT
		$summary_payment_group = array();
		if(!empty($summary_payment)){
			foreach($summary_payment as $dt){
				
				//BALANCING DISKON
				if(!empty($data_diskon_awal[$dt['bank_id']])){
					if(!empty($data_diskon_awal[$dt['bank_id']][$billing_date])){
						$dt['discount_total'] -= $data_diskon_awal[$dt['bank_id']][$billing_date]['item'];
						$dt['discount_billing_total'] -= $data_diskon_awal[$dt['bank_id']][$billing_date]['billing'];
					}
				}
				
				if(!empty($data_balancing_diskon[$dt['bank_id']])){
					if(!empty($data_balancing_diskon[$dt['bank_id']][$billing_date])){
						$dt['discount_total'] += $data_balancing_diskon[$dt['bank_id']][$billing_date]['item'];
						$dt['discount_billing_total'] += $data_balancing_diskon[$dt['bank_id']][$billing_date]['billing'];
					}
				}
				
				if(!empty($data_selisih_diskon[$dt['bank_id']])){
					if(!empty($data_selisih_diskon[$dt['bank_id']][$billing_date])){
						$dt['sub_total'] -= $data_selisih_diskon[$dt['bank_id']][$billing_date];
						$dt['grand_total'] -= $data_selisih_diskon[$dt['bank_id']][$billing_date];
					}
				}
				
				//BALANCING DISKON PAYMENT
				if(!empty($data_selisih_diskon_payment[$dt['bank_id']])){
					if(!empty($data_selisih_diskon_payment[$dt['bank_id']][$billing_date])){
						foreach($data_selisih_diskon_payment[$dt['bank_id']][$billing_date] as $payment_id => $dtP){
							if(!empty($dt['payment_'.$payment_id])){
								$dt['payment_'.$payment_id] -= $dtP;
							}
						}
					}
				}
				
				//BALANCING DISKON BANK
				if(!empty($data_selisih_diskon_bank[$dt['bank_id']])){
					if(!empty($data_selisih_diskon_bank[$dt['bank_id']][$billing_date])){
						foreach($data_selisih_diskon_bank[$dt['bank_id']][$billing_date] as $bank_id => $dtP){
							if(!empty($dt['bank_'.$bank_id])){
								$dt['bank_'.$bank_id] -= $dtP;
							}
						}
					}
				}
				
				
				//KONVERSI PEMBULATAN
				$selisih_pembulatan = 0;
				if(!empty($pembulatan_awal_product[$dt['bank_id']])){
					if(!empty($pembulatan_awal_product[$dt['bank_id']][$billing_date])){
						$selisih_pembulatan -= $pembulatan_awal_product[$dt['bank_id']][$billing_date];
						$dt['grand_total'] -= $pembulatan_awal_product[$dt['bank_id']][$billing_date];
					}
				}
				
				if(!empty($konversi_pembulatan_product[$dt['bank_id']])){
					if(!empty($konversi_pembulatan_product[$dt['bank_id']][$billing_date])){
						$dt['total_pembulatan'] = $konversi_pembulatan_product[$dt['bank_id']][$billing_date]['total_pembulatan'];
						$dt['grand_total'] += $konversi_pembulatan_product[$dt['bank_id']][$billing_date]['total_pembulatan'];
						$selisih_pembulatan += $konversi_pembulatan_product[$dt['bank_id']][$billing_date]['total_pembulatan'];
					}
				}
				
				if(!empty($dt['compliment_total'])){
					//$dt['compliment_total'] += $selisih_pembulatan;
				}
				
				//KONVERSI PEMBULATAN PAYMENT
				if(!empty($pembulatan_awal_product_payment[$dt['bank_id']])){
					if(!empty($pembulatan_awal_product_payment[$dt['bank_id']][$billing_date])){
						foreach($pembulatan_awal_product_payment[$dt['bank_id']][$billing_date] as $payment_id => $dtP){
							if(!empty($dt['payment_'.$payment_id])){
								$dt['payment_'.$payment_id] -= $dtP;
							}
						}
					}
				}
				
				if(!empty($konversi_pembulatan_product_payment[$dt['bank_id']])){
					if(!empty($konversi_pembulatan_product_payment[$dt['bank_id']][$billing_date])){
						foreach($konversi_pembulatan_product_payment[$dt['bank_id']][$billing_date] as $payment_id => $dtP){
							if(!empty($dt['payment_'.$payment_id])){
								$dt['payment_'.$payment_id] += $dtP;
							}
						}
					}
				}
				
				
				//KONVERSI PEMBULATAN BANK
				if(!empty($pembulatan_awal_product_bank[$dt['bank_id']])){
					if(!empty($pembulatan_awal_product_bank[$dt['bank_id']][$billing_date])){
						foreach($pembulatan_awal_product_bank[$dt['bank_id']][$billing_date] as $bank_id => $dtP){
							if(!empty($dt['bank_'.$bank_id])){
								$dt['bank_'.$bank_id] -= $dtP;
							}
						}
					}
				}
				
				if(!empty($konversi_pembulatan_product_bank[$dt['bank_id']])){
					if(!empty($konversi_pembulatan_product_bank[$dt['bank_id']][$billing_date])){
						foreach($konversi_pembulatan_product_bank[$dt['bank_id']][$billing_date] as $bank_id => $dtP){
							if(!empty($dt['bank_'.$bank_id])){
								$dt['bank_'.$bank_id] += $dtP;
							}
						}
					}
				}
				
				if(empty($summary_payment_group[$dt['payment_id']])){
					$summary_payment_group[$dt['payment_id']] = array();
				}
				
				$summary_payment_group[$dt['payment_id']][] = $dt;
			}
		}
		
		//echo '<pre>';
		//print_r($data_post['summary_data']);
		//echo '<pre>';
		//print_r($summary_payment);
		//die();
		
		$menu_sales = printer_command_align_right(priceFormat($data_post['summary_data']['total_billing']), $max_number_3);
		$disc_per_item = printer_command_align_right(priceFormat($data_post['summary_data']['total_discount_item']), $max_number_3);
		
		$menu_net_sales_count = ($data_post['summary_data']['total_billing']-$data_post['summary_data']['total_discount_item']);
		$menu_net_sales = printer_command_align_right(priceFormat($menu_net_sales_count), $max_number_3);
		$disc_per_billing = printer_command_align_right(priceFormat($data_post['summary_data']['total_discount_billing']), $max_number_3);
		
		//$total_net_sales_count = ($menu_net_sales_count-$data_post['summary_data']['total_discount_item']);
		$total_net_sales_count = $menu_net_sales_count - $data_post['summary_data']['total_discount_billing'];
		$total_net_sales = printer_command_align_right(priceFormat($data_post['summary_data']['net_sales']), $max_number_3);
		
		$service_total = printer_command_align_right(priceFormat($data_post['summary_data']['service_total']), $max_number_3);
		$tax_total = printer_command_align_right(priceFormat($data_post['summary_data']['tax_total']), $max_number_3);
		$total_pembulatan = printer_command_align_right(priceFormat($data_post['summary_data']['total_pembulatan']), $max_number_3);
		$compliment_total = printer_command_align_right(priceFormat($data_post['summary_data']['compliment_total']), $max_number_3);
		$grand_total = printer_command_align_right(priceFormat($data_post['summary_data']['grand_total']), $max_number_3);
		
		//update-1912-001
		$total_of_billing = printer_command_align_right(priceFormat($data_post['summary_data']['total_of_billing']), $max_number_3);
		$total_of_guest = printer_command_align_right(priceFormat($data_post['summary_data']['total_of_guest']), $max_number_3);
		$total_dp = printer_command_align_right(priceFormat($data_post['summary_data']['total_dp']), $max_number_3);
		
		//update-2007.001
		if(!empty($return_data)){
			return $data_post;
			die();
		}
		
		//update-1912-001
		$all_summary_data = "[align=0][size=1][tab]SALES SUMMARY[tab]\n";
		$all_summary_data .= "[size=0]";
		if($show_txmark == 0){
			$all_summary_data .= "[align=0][tab]QTY BILLING[tab]".$total_of_billing."\n"; 
			$all_summary_data .= "[align=0][tab]TOTAL GUEST[tab]".$total_of_guest."\n"; 
		}
		$all_summary_data .= "[align=0][tab]MENU SALES[tab]".$menu_sales."\n"; 
		
		
		if($data_post['diskon_sebelum_pajak_service'] == 0){
			$all_summary_data .= "[align=0][tab]DISC/ITEM (AT)[tab]".$disc_per_item."\n"; 
			$all_summary_data .= "[align=0][tab]DISC/BILLING (AT)[tab]".$disc_per_billing."\n";
		}else{
			$all_summary_data .= "[align=0][tab]DISC/ITEM[tab]".$disc_per_item."\n"; 
			$all_summary_data .= "[align=0][tab]DISC/BILLING[tab]".$disc_per_billing."\n";
		}
			 
		if(!empty($data_post['summary_data']['compliment_total'])){
			$all_summary_data .= "[align=0][tab]COMPLIMENT[tab]".$compliment_total."\n"; 
		}
		$all_summary_data .= "[align=0][tab]NET SALES[tab]".$total_net_sales."\n";
		
		$all_summary_data .= "[align=0][tab]TAX[tab]".$tax_total."\n"; 
		$all_summary_data .= "[align=0][tab]SERVICE[tab]".$service_total."\n"; 
		
		$all_summary_data .= "[align=0][tab]PEMBULATAN[tab]".$total_pembulatan."\n"; 
		$all_summary_data .= "[align=0][tab]TOTAL SALES[tab]".$grand_total; 
		
		//sort index
		asort($summary_payment_group);
		
		$all_payment_data = '';
		if(!empty($summary_payment_group)){
			foreach($summary_payment_group as $key => $dt_detail){
				
				$no_payment = 0;
				if(!empty($dt_detail)){
					foreach($dt_detail as $dt){
						
						$no_payment++;
						$payment_name = ucwords(str_replace("_"," ",$dt['payment_name']));
						$data_name = ucwords(str_replace("_"," ",$dt['bank_name']));
						
						//update-2001.002
						if(strlen($data_name) > $max_text){
							$data_name = substr($data_name,0,$max_text);
						}
						
						if(empty($all_payment_data)){
							$all_payment_data = "[align=0][size=1][tab]PAYMENT SUMMARY[tab]\n";
							$all_payment_data .= "[size=0]";
							if(!empty($data_post['summary_data']['total_dp'])){
								$all_payment_data .= "[align=0][tab]DOWN-PAYMENT[tab]".$total_dp."\n"; 
							}
						}
						
						$value_show = printer_command_align_right(priceFormat($dt['payment_'.$key]), $max_number_3);
						
						if($payment_name == 'CASH'){
							$all_payment_data .= "[align=0][tab]".$payment_name."[tab]".$value_show."\n"; 
						}else{
							if($no_payment == 1){
								//$all_payment_data .= $payment_name."\n";
								$all_payment_data .= "[align=0][tab]".$payment_name."[tab] \n"; 
							}
							$all_payment_data .= "[align=0][tab] *".$data_name."[tab]".$value_show."\n";
						}
						
					}
				}
				
			}
		}
		
		//update-1912-001
		$print_attr = array(
			"{user}"	=> $session_user,
			"{tanggal_settlement}"	=> date("d/m/Y", $datenowstr),
			"{tanggal_shift}"		=> $tanggal_cetak,
			"{jam_shift}"			=> $jam_cetak,
			"{summary_data}"		=> $all_summary_data,
			"{payment_data}"		=> $all_payment_data,
			"{nama_shift}"			=> $nama_shift
		);
		
		//TXMARK
		if(!empty($get_date)){
			$print_attr["{user}"] = 'kasir';
			$print_attr["{tanggal_settlement}"] = date("d/m/Y", strtotime($get_date));
			$print_attr["{tanggal_shift}"] = date("d/m/Y", strtotime($get_date));
			$print_attr["{jam_shift}"] = $jam_cetak;
		}
		
		$print_content_cashierReceipt = strtr($cashierReceipt_settlement_layout, $print_attr);
		$print_content_cashierReceipt_monitoring = strtr($cashierReceipt_settlement_layout, $print_attr);
		
		$print_content = replace_to_printer_command($print_content_cashierReceipt, $printer_type_cashier, $printer_pin_cashierReceipt);
		
		$r = array('success' => false, 'info' => '', 'print' => array());
		
		//$r['print'][] = $print_content_cashierReceipt;
		if(!empty($test)){
			echo '<pre>';
			print_r($print_content_cashierReceipt);
			die();
		}
		
		//DIRECT PRINT USING PHP - CASHIER PRINTER				
		$is_print_error = false;
		
		if($data_printer['print_method'] == 'ESC/POS'){
			try {
				@$ph = @printer_open($printer_ip_cashierReceipt);
			} catch (Exception $e) {
				$ph = false;
			}
			
			//$ph = @printer_open($printer_ip_cashierReceipt);
			
			if($ph)
			{	
				printer_start_doc($ph, "CLOSE CASHIER - SETTLEMENT");
				printer_start_page($ph);
				printer_set_option($ph, PRINTER_MODE, "RAW");
				printer_write($ph, $print_content);
				printer_end_page($ph);
				printer_end_doc($ph);
				printer_close($ph);
				$r['success'] = true;
				$r['print'] = $print_content;
				
			}else{
				$is_print_error = true;
			}
			
			$data_printer['escpos_pass'] = 1;
			$r['success'] = true;
			$r['info'] = '';
				
			if($is_print_error){					
				$r['info'] = 'Komunikasi dengan Printer Gagal!<br/>';
				$r['success'] = false;
				if($print_preview_billing == 0){
					echo json_encode($r);
				}else{
					printing_process_error($r['info']);
				}
				die();
			}
		}
		
		$data_printer['is_settlement'] = 1;
		
		$monitoring_id = 0;
		if($data_printer['print_method'] == 'RAWBT' AND (empty($rawbt_check) OR !empty($rawbt_print))){
			//SAVE to Print Monitoring
			$data_printMonitoring = array(
				'tipe'			=> 'settlement',
				'peritem'		=> '0',
				'print_date'	=> date("Y-m-d", $mktime_dari),
				'print_datetime'=> date("Y-m-d H:i:s"),
				'user'			=> $session_user,
				'table_no'		=> '0',
				'billing_no'	=> date("dmY",$mktime_dari),
				'receiptTxt'	=> $print_content_cashierReceipt_monitoring,
				'printer'		=> $printer_ip_cashierReceipt,
				'tipe_printer'	=> $printer_type_cashier,
				'tipe_pin'		=> $printer_pin_cashierReceipt,
				'status_print'	=> 1
			);
			$this->db->insert($this->table_print_monitoring, $data_printMonitoring);
			$monitoring_id = $this->db->insert_id();
		}
		
		//TXMARK
		if(!empty($reprint)){
			echo json_encode($r);
			die();
		}
		
		//update-2003.001
		if(empty($print_preview_billing)){
			echo json_encode($r);
			die();
		}else{
			printing_process($data_printer, $print_content_cashierReceipt, 'print');
		}

	}
	
} 