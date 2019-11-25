<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class SetupAplikasi extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix');
		$this->load->model('model_setupaplikasi', 'm');
	}
	
	public function loadSetup(){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$opt_val = array(
			'wepos_tipe','timezone_default','report_place_default','warehouse_primary','retail_warehouse','spv_access_active',
			'use_login_pin','supervisor_pin_mode','auto_logout_time','input_chinese_text','management_systems',
			'ipserver_management_systems','view_multiple_store','autobackup_on_settlement','use_wms','as_server_backup','mode_bazaar_tenant',
			
			'billing_no_simple','role_id_kasir','include_tax','include_service','default_tax_percentage','default_service_percentage',
			'set_ta_table_ta','takeaway_no_tax','takeaway_no_service','diskon_sebelum_pajak_service',
			'use_pembulatan','pembulatan_dinamis','cashier_pembulatan_keatas','cashier_max_pembulatan','autohold_create_billing','billing_log',
			'table_available_after_paid','hide_takeaway_order_apps','default_discount_payment','save_order_note','must_choose_customer','allow_add_customer',
			'order_timer','hide_button_invoice','hide_button_halfpayment','hide_button_mergebill','hide_button_splitbill','hide_button_logoutaplikasi',
			'cashier_credit_ar','min_noncash','no_hold_billing','print_preview_billing','default_tipe_billing',
			'maxday_cashier_report','jam_operasional_from','jam_operasional_to','jam_operasional_extra',
			'hide_detail_taxservice','hide_detail_takeaway','hide_detail_compliment','hold_table_timer','use_block_table',
		
			'print_order_peritem_kitchen','print_order_peritem_bar','print_order_peritem_other','print_chinese_text','show_multiple_print_qc',
			'multiple_print_qc','show_multiple_print_billing','multiple_print_billing',
			'printMonitoring_qc','printMonitoring_kitchen','printMonitoring_bar','printMonitoring_other','print_qc_then_order',
			'print_qc_order_when_payment','opsi_no_print_when_payment','send_billing_to_email',
			'tandai_pajak_billing','override_pajak_billing','reset_billing_yesterday','custom_print_APS',
			
			'use_approval_po','approval_change_payment_po_done','purchasing_request_order','auto_add_supplier_item_when_purchasing','auto_add_supplier_ap','receiving_select_warehouse',
			'stock_rekap_start_date','persediaan_barang',
			'auto_item_code','item_code_separator','item_code_format','item_no_length','so_count_stock','ds_count_stock','ds_auto_terima','hide_empty_stock_on_report',
			'use_item_sku','item_sku_from_code','autocut_stok_sales','autocut_stok_sales_to_usage','reservation_cek_stok','using_item_average_as_hpp',
			'auto_pengakuan_hutang','default_pengakuan_hutang',
			'auto_pengakuan_piutang','default_pengakuan_piutang',
			
			'autoclosing_generate_timer','autoclosing_closing_time',
			'closing_sales_start_date','autoclosing_generate_sales','autoclosing_closing_sales','autoclosing_auto_cancel_billing','closing_purchasing_start_date',
			'autoclosing_generate_purchasing','autoclosing_closing_purchasing','autoclosing_auto_cancel_receiving',
			'closing_inventory_start_date','autoclosing_generate_inventory','autoclosing_generate_stock',
			'autoclosing_closing_inventory','autoclosing_auto_cancel_distribution','autoclosing_auto_cancel_production',
			
			'closing_accounting_start_date','autoclosing_generate_accounting','autoclosing_closing_accounting','autoclosing_skip_open_jurnal',
			'account_payable_non_accounting','account_receivable_non_accounting','cashflow_non_accounting',
			'tujuan_penerimaan_dp_reservation','jenis_penerimaan_dp_reservation', 'reservation_cashier', 'default_discount_id_reservation'
			
		);
		
		$get_opt = get_option_value($opt_val);
		
		if(empty($get_opt['jam_operasional_extra'])){
			$get_opt['jam_operasional_extra'] = 0;
		}
		$get_opt['wepos_tipe_display'] = '-';
		if(!empty($get_opt['wepos_tipe'])){
			$get_opt['wepos_tipe_display'] = $get_opt['wepos_tipe'];
		}
		
		$spv_access_active = array();
		if(!empty($get_opt['spv_access_active'])){
			$exp_dt = explode(",",$get_opt['spv_access_active']);
			foreach($exp_dt as $dt){
				$spv_access_active[trim($dt)] = 1;
				
				$get_opt[$dt] = 1;
			}
			
			
		}

		$retValue = array('success' => true);
			
		$retValue['setupAplikasi'] = $get_opt;
				
		die(json_encode($retValue));
	}
	
	public function save_setupAplikasi(){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		
		$r = array('success' => false);
		
		$opt_val = array(
			'wepos_tipe','timezone_default','report_place_default','warehouse_primary','retail_warehouse','spv_access_active',
			'use_login_pin','supervisor_pin_mode','auto_logout_time','input_chinese_text','management_systems',
			'ipserver_management_systems','view_multiple_store','autobackup_on_settlement','use_wms','as_server_backup','mode_bazaar_tenant',
			
			'billing_no_simple','role_id_kasir','include_tax','include_service','default_tax_percentage','default_service_percentage',
			'set_ta_table_ta','takeaway_no_tax','takeaway_no_service','diskon_sebelum_pajak_service',
			'use_pembulatan','pembulatan_dinamis','cashier_pembulatan_keatas','cashier_max_pembulatan','autohold_create_billing','billing_log',
			'table_available_after_paid','hide_takeaway_order_apps','default_discount_payment','save_order_note','must_choose_customer','allow_add_customer',
			'order_timer','hide_button_invoice','hide_button_halfpayment','hide_button_mergebill','hide_button_splitbill','hide_button_logoutaplikasi',
			'cashier_credit_ar','min_noncash','no_hold_billing','print_preview_billing','default_tipe_billing',
			'maxday_cashier_report','jam_operasional_from','jam_operasional_to','jam_operasional_extra',
			'hide_detail_taxservice','hide_detail_takeaway','hide_detail_compliment','hold_table_timer','use_block_table',
			
			'print_order_peritem_kitchen','print_order_peritem_bar','print_order_peritem_other','print_chinese_text','show_multiple_print_qc',
			'multiple_print_qc','show_multiple_print_billing','multiple_print_billing',
			'printMonitoring_qc','printMonitoring_kitchen','printMonitoring_bar','printMonitoring_other','print_qc_then_order',
			'print_qc_order_when_payment','opsi_no_print_when_payment','send_billing_to_email',
			'tandai_pajak_billing','override_pajak_billing','reset_billing_yesterday','custom_print_APS',
			
			'use_approval_po','approval_change_payment_po_done','purchasing_request_order','auto_add_supplier_item_when_purchasing','auto_add_supplier_ap','receiving_select_warehouse',
			'stock_rekap_start_date','persediaan_barang',
			'auto_item_code','item_code_separator','item_code_format','item_no_length','so_count_stock','ds_count_stock','ds_auto_terima','hide_empty_stock_on_report',
			'use_item_sku','item_sku_from_code','autocut_stok_sales','autocut_stok_sales_to_usage','reservation_cek_stok','using_item_average_as_hpp',
			'auto_pengakuan_hutang','default_pengakuan_hutang',
			'auto_pengakuan_piutang','default_pengakuan_piutang',
			
			'autoclosing_generate_timer','autoclosing_closing_time',
			'closing_sales_start_date','autoclosing_generate_sales','autoclosing_closing_sales','autoclosing_auto_cancel_billing','closing_purchasing_start_date',
			'autoclosing_generate_purchasing','autoclosing_closing_purchasing','autoclosing_auto_cancel_receiving',
			'closing_inventory_start_date','autoclosing_generate_inventory','autoclosing_generate_stock',
			'autoclosing_closing_inventory','autoclosing_auto_cancel_distribution','autoclosing_auto_cancel_production',
			
			'closing_accounting_start_date','autoclosing_generate_accounting','autoclosing_closing_accounting','autoclosing_skip_open_jurnal',
			'account_payable_non_accounting','account_receivable_non_accounting','cashflow_non_accounting',
			'tujuan_penerimaan_dp_reservation','jenis_penerimaan_dp_reservation', 'reservation_cashier', 'default_discount_id_reservation'
			
		);
		
		$default_option = array(
			'wepos_tipe'					=> 'cafe',
			'timezone_default'				=> 'Asia/Jakarta',
			'report_place_default'			=> 'Bandung',
			'warehouse_primary'				=> 1,
			'retail_warehouse'				=> 0,
			'spv_access_active'				=> '',
			
			'use_login_pin'					=> 0,
			'supervisor_pin_mode'			=> 0,
			'auto_logout_time'				=> 0, //3600000
			'input_chinese_text'			=> 0,
			'management_systems'			=> 0,
			'ipserver_management_systems' 	=> '',
			'view_multiple_store' 			=> 0,
			'autobackup_on_settlement' 		=> 0,
			'use_wms' 						=> 0,
			'as_server_backup' 				=> 0,
			'mode_bazaar_tenant' 			=> 0,
			
			'billing_no_simple'				=> 0,
			'role_id_kasir'					=> '1,2,3',
			'include_tax'					=> 0,
			'include_service'				=> 0,
			'default_tax_percentage'		=> 0,
			'default_service_percentage'	=> 0,
			'set_ta_table_ta'				=> 0,
			'takeaway_no_tax'				=> 0,
			'takeaway_no_service'			=> 0,
			'diskon_sebelum_pajak_service'	=> 0,
			'use_pembulatan'				=> 0,
			'pembulatan_dinamis'			=> 0,
			'cashier_pembulatan_keatas'		=> 0,
			'cashier_max_pembulatan'		=> 100,
			'autohold_create_billing'		=> 0,
			'billing_log'					=> 0,
			'table_available_after_paid'	=> 0,
			'hide_takeaway_order_apps'		=> 0,
			'default_discount_payment'		=> 0, 
			'save_order_note'				=> 0,
			'must_choose_customer'			=> 0,
			'allow_add_customer'			=> 0,
			'order_timer'					=> 0,
			'hide_button_invoice'			=> 0,
			'hide_button_halfpayment'		=> 0,
			'hide_button_mergebill'			=> 0,
			'hide_button_splitbill'			=> 0,
			'hide_button_logoutaplikasi'	=> 0,
			'cashier_credit_ar'				=> 0,
			'min_noncash'					=> 0,
			'no_hold_billing'				=> 0,
			'default_tipe_billing'			=> 0,
			'print_preview_billing'			=> 0,
			'maxday_cashier_report'			=> 1,
			'jam_operasional_from'			=> '07:00',
			'jam_operasional_to'			=> '23:00',
			'jam_operasional_extra'			=> 0,
			'hide_detail_taxservice'		=> 0,
			'hide_detail_takeaway'			=> 0,
			'hide_detail_compliment'		=> 0,
			'hold_table_timer'				=> 0,
			'use_block_table'				=> 0,
					
			'print_order_peritem_kitchen'	=> 0,
			'print_order_peritem_bar'		=> 0,
			'print_order_peritem_other'		=> 0,
			'print_chinese_text'			=> 0,
			'show_multiple_print_qc'		=> 0,
			'multiple_print_qc'				=> '1,2',
			'show_multiple_print_billing'	=> 0,
			'multiple_print_billing'		=> '1,2',
			'printMonitoring_qc'			=> 0,
			'printMonitoring_kitchen'		=> 0,
			'printMonitoring_bar'			=> 0,
			'printMonitoring_other'			=> 0,
			'print_qc_then_order'			=> 0,
			'print_qc_order_when_payment'	=> 0,
			'opsi_no_print_when_payment'	=> 0,
			'send_billing_to_email'			=> 0,
			'tandai_pajak_billing'			=> 0,
			'override_pajak_billing'		=> 0,
			'reset_billing_yesterday'		=> 0,
			'custom_print_APS'				=> 0,
						
			'use_approval_po'				=> 0,
			'approval_change_payment_po_done' => 0,
			'purchasing_request_order'		=> 0,
			'auto_add_supplier_item_when_purchasing'=> 0,
			'auto_add_supplier_ap'			=> 0,
			'receiving_select_warehouse'	=> 0,
			'stock_rekap_start_date'		=> '01-'.date("m-Y"),
			'persediaan_barang'				=> 'average',
			'auto_item_code'				=> 0,
			'item_code_separator'			=> '.',
			'item_code_format'				=> '{Cat}.{SubCat}.{ItemNo}',
			'item_no_length'				=> 4,
			'so_count_stock'				=> 0,
			'ds_count_stock'				=> 0,
			'ds_auto_terima'				=> 0,
			'hide_empty_stock_on_report'	=> 0,
			'use_item_sku'					=> 0,
			'item_sku_from_code'			=> 0,
			'autocut_stok_sales'			=> 0,
			'autocut_stok_sales_to_usage'	=> 0,
			'using_item_average_as_hpp'		=> 0,
			'auto_pengakuan_hutang'			=> 0,
			'auto_pengakuan_piutang'		=> 0,
			'default_pengakuan_hutang'		=> 0,
			'default_pengakuan_piutang'		=> 0,
			
			'autoclosing_generate_timer'			=> 600000,
			'autoclosing_closing_time'				=>'03:00',
			'closing_sales_start_date'				=> '01-'.date("m-Y"),
			'autoclosing_generate_sales'			=> 0,
			'autoclosing_closing_sales'				=> 0,
			'autoclosing_auto_cancel_billing'		=> 0,
			'closing_purchasing_start_date'			=> '01-'.date("m-Y"),
			'autoclosing_generate_purchasing'		=> 0,
			'autoclosing_closing_purchasing'		=> 0,
			'autoclosing_auto_cancel_receiving'		=> 0,
			'closing_inventory_start_date'			=> '01-'.date("m-Y"),
			'autoclosing_generate_inventory'		=> 0,
			'autoclosing_generate_stock'			=> 0,
			'autoclosing_closing_inventory'			=> 0,
			'autoclosing_auto_cancel_distribution'	=> 0,
			'autoclosing_auto_cancel_production'	=> 0,
						
			'closing_accounting_start_date'			=> '01-'.date("m-Y"),
			'autoclosing_generate_accounting'		=> 0,
			'autoclosing_closing_accounting'		=> 0,
			'autoclosing_skip_open_jurnal'			=> 0,
			'account_payable_non_accounting'		=> 0,
			'account_receivable_non_accounting'		=> 0,
			'cashflow_non_accounting'				=> 0,
			
			'reservation_cek_stok'					=> 0,
			'reservation_cashier'					=> 0,
			'tujuan_penerimaan_dp_reservation'		=> 0,
			'jenis_penerimaan_dp_reservation'		=> 0,
			'default_discount_id_reservation'		=> 0,
		);
		
		$data_option = array();
		$setupAplikasi = array();
		foreach($opt_val as $val){
			
			$get_val = $this->input->post($val, true);
			if(empty($get_val)){
				$get_val = $default_option[$val];
			}
			
			$data_option[$val] = $get_val;
			$setupAplikasi[$val] = $get_val;
			
			if($val == 'wepos_tipe'){
				$setupAplikasi['wepos_tipe_display'] = $get_val;
			}
			
		}
		
		if(empty($data_option['item_code_format']) AND !empty($data_option['auto_item_code'])){
			$data_option['auto_item_code'] = 1;
			$data_option['item_code_separator'] = '.';
			$data_option['item_code_format'] = '{Cat}.{SubCat}.{ItemNo}';
			$data_option['item_no_length'] = 4;
		}	
		
		//supervisorAccess_name
		$opt_supervisorAccess = array('open_close_cashier','cancel_billing','cancel_order','retur_order','unmerge_billing',
		'change_ppn','change_service','change_dp','set_compliment_item','clear_compliment_item','approval_po','change_payment_po',
		'reservation_to_cashier','sales_autocut_stock_approval');
		
		$supervisorAccess_name = array();
		foreach($opt_supervisorAccess as $val){
			$get_dt = $this->input->post($val, true);
			if(!empty($get_dt)){
				$supervisorAccess_name[] = $val;
				$setupAplikasi[$val] = $get_dt;
			}
		}
		
		$data_option['spv_access_active'] = implode(",", $supervisorAccess_name);
		
		//CLEAR OPTIONS
		if($data_option){
				
			$get_var = array();
			foreach($data_option as $key => $dt){
				if(!in_array($key, $get_var)){
					$get_var[] = $key;
				}
			}
			
				
			$this->db->select('a.*');
			$this->db->from($this->prefix.'options as a');	
			$var_all = implode("','", $get_var);
			$this->db->where("a.option_var IN ('".$var_all."')");
			
			$query = $this->db->get();
			if($query->num_rows() > 0){
				
				$check_double_var = array();
				$check_double_var_no = array();
				$check_double_id = array();
				//UPDATE OPTION
				foreach($query->result_array() as $dt){
				
					if(!in_array($dt['option_var'],$check_double_var)){
						$check_double_var[] = $dt['option_var'];
						$check_double_var_no[$dt['option_var']] = 0;
					}
					
					$check_double_var_no[$dt['option_var']] += 1;
					
					if($check_double_var_no[$dt['option_var']] > 1){
						$check_double_id[] = $dt['id'];
					}
					
				}
				
				if(!empty($check_double_id)){
					$all_id_opt = implode(",", $check_double_id);
					
					$this->db->delete($this->prefix.'options', 'id IN ('.$all_id_opt.')');
				}
				
				
			}
			
		}
		
		//UPDATE OPTIONS
		$update_option = update_option($data_option);
		if($update_option){
			$r = array('success' => true, 
				"setupAplikasi" => $setupAplikasi,
				//"check_double_var_no" => $check_double_var_no,
				"check_double_id" => $check_double_id
			);
		}
		
		die(json_encode($r));
	}
}