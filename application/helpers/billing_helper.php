<?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); 
/**
 * Framework Billing Helper
 *
 * PHP version 5
 *
 * @category  CodeIgniter
 * @package   Framework System
 * @author    angga nugraha (angga.nugraha@gmail.com)
 * @version   0.1
 * Copyright (c) 2020 Angga Nugraha  (https://wepos.id)
*/


/*GET BILLING*/
if(!function_exists('getBilling')){
	function getBilling($billing_id = ''){
		
		$scope =& get_instance();
		
		$scope->prefix_apps = config_item('db_prefix');
		$scope->prefix = config_item('db_prefix2');
		$scope->table = $scope->prefix.'billing';	
		$session_user = $scope->session->userdata('user_username');					
		
		$table_id = $scope->input->post('table_id', true);
		
		if(empty($session_user)){
			return false;
		}
		
		//update-1912-001
		$opt_var = array('include_tax','include_service',
		'default_tax_percentage','default_service_percentage',
		'takeaway_no_tax','takeaway_no_service','autohold_create_billing',
		'default_tipe_billing','diskon_sebelum_pajak_service',
		'jumlah_shift','shift_active');
		$get_opt = get_option_value($opt_var);
		
		$diskon_sebelum_pajak_service = 0;
		if(!empty($get_opt['diskon_sebelum_pajak_service'])){
			$diskon_sebelum_pajak_service = $get_opt['diskon_sebelum_pajak_service'];
		}
		
		$include_tax = 0;
		if(!empty($get_opt['include_tax'])){
			$include_tax = $get_opt['include_tax'];
		}
		
		$include_service = 0;
		if(!empty($get_opt['include_service'])){
			$include_service = $get_opt['include_service'];
		}
		
		$default_tax_percentage = 0;
		if(!empty($get_opt['default_tax_percentage'])){
			$default_tax_percentage = $get_opt['default_tax_percentage'];
		}		
		
		$default_service_percentage = 0;
		if(!empty($get_opt['default_service_percentage'])){
			$default_service_percentage = $get_opt['default_service_percentage'];
		}		
		
		$takeaway_no_tax = 0;
		if(!empty($get_opt['takeaway_no_tax'])){
			$takeaway_no_tax = $get_opt['takeaway_no_tax'];
		}	
		
		$takeaway_no_service = 0;
		if(!empty($get_opt['takeaway_no_service'])){
			$takeaway_no_service = $get_opt['takeaway_no_service'];
		}
		
		//autohold_create_billing
		$autohold_create_billing = 0;
		if(!empty($get_opt['autohold_create_billing'])){
			$autohold_create_billing = $get_opt['autohold_create_billing'];
		}
		
		//default_tipe_billing
		$default_tipe_billing = 0;
		if(!empty($get_opt['default_tipe_billing']) AND empty($table_id)){
			$default_tipe_billing = $get_opt['default_tipe_billing'];
			$table_id = $get_opt['default_tipe_billing'];
		}
		
		//update-1912-001
		$shift = 0;
		$jumlah_shift = 1;
		if(!empty($get_opt['jumlah_shift'])){
			$jumlah_shift = $get_opt['jumlah_shift'];
		}
		if(!empty($get_opt['shift_active'])){
			$shift = $get_opt['shift_active'];
		}
		
		if($jumlah_shift > 1 AND empty($shift)){
			$scope->db->select('a.*, b.nama_shift');
			$scope->db->from($scope->prefix.'shift_log as a');
			$scope->db->join($scope->prefix.'shift as b',"b.id = a.user_shift","LEFT");
			$scope->db->where("a.tanggal_shift", date("Y-m-d"));
			$scope->db->order_by("a.id", 'DESC');
			$getShiftLog = $scope->db->get();
			if($getShiftLog->num_rows() > 0){
				$dataShiftLog = $getShiftLog->row_array();
				$shift = $dataShiftLog['user_shift'];
			}
		}
		
		if($jumlah_shift == 1 AND empty($shift)){
			$shift = 1;
		}
		
		$is_new = false;
		if(empty($billing_id)){
			//CREATE BILLING
			$get_no_billing = generate_billing_no();
			$date_now = date('Y-m-d H:i:s');
			
			//update-1912-001
			$var = array(
				'fields'	=>	array(
				    'billing_no'  	=> 	$get_no_billing,
					'include_tax'	=>	$include_tax,
					'include_service'=>	$include_service,
					'tax_percentage'	=>	$default_tax_percentage,
					'service_percentage'=>	$default_service_percentage,
					'takeaway_no_tax'	=>	$takeaway_no_tax,
					'takeaway_no_service'=>	$takeaway_no_service,
					'diskon_sebelum_pajak_service'	=>	$diskon_sebelum_pajak_service,
					'created'		=>	$date_now,
					'createdby'		=>	$session_user,
					'updated'		=>	$date_now,
					'updatedby'		=>	$session_user,
					'shift'			=>	$shift
				),
				'table'		=>  $scope->table
			);
			
			if(!empty($table_id)){
				$var['fields']['table_id'] = $table_id;
			}
			
			if($autohold_create_billing == 1){
				$var['fields']['billing_status'] = 'hold';
			}
			
			$insert_id = false;
			$scope->lib_trans->begin();
				$q = $scope->db->insert($scope->table, $var['fields']);
				$billing_id = $scope->db->insert_id();
			$scope->lib_trans->commit();			
			if($q == false)
			{  
				return false;
			}else{
				$is_new = true;
			}
			
		}
		
		//update-2001.002
		$billingData = array();
		$scope->db->select('a.id, a.table_id, a.table_no, a.billing_no, a.payment_date,
			a.billing_status, a.billing_notes, a.total_pembulatan, a.total_billing, a.grand_total, a.total_paid, a.payment_id, a.bank_id,
			a.card_no, a.include_tax, a.tax_percentage, a.tax_total, a.include_service, a.service_percentage, a.service_total, 
			a.discount_id, a.discount_notes, a.discount_percentage, a.discount_price, a.discount_total, a.total_hpp, 
			a.is_active, a.total_dp, a.compliment_total, a.total_cash, a.total_credit, a.createdby, a.updatedby, 
			a.merge_id, a.merge_main_status, a.split_from_id, a.total_guest, a.lock_billing, a.qc_notes,
			a.created, a.updated, a.takeaway_no_tax, a.takeaway_no_service, a.is_compliment, 
			a.discount_perbilling, a.total_return, a.compliment_total_tax_service, a.is_half_payment,
			a.sales_id, a.sales_percentage, a.sales_price, a.sales_type, a.customer_id,  a.block_table,
			a.id as billing_id, a.voucher_no, a.is_sistem_tawar, a.single_rate, a.is_reservation,
			a.txmark, a.txmark_no,
			b.table_name, b.table_no as table_no_real, b.table_tipe, b.table_desc, b.floorplan_id, c.floorplan_name, 
			d.payment_type_name, e.user_firstname, e.user_lastname, f.bank_name, 
			g.billing_no as merge_billing_no, h.sales_name, h.sales_company, i.customer_name, i.customer_code');
		$scope->db->from($scope->table." as a");
		$scope->db->join($scope->prefix.'table as b','b.id = a.table_id','LEFT');
		$scope->db->join($scope->prefix.'floorplan as c','c.id = b.floorplan_id','LEFT');
		$scope->db->join($scope->prefix.'payment_type as d','d.id = a.payment_id','LEFT');
		$scope->db->join($scope->prefix_apps.'users as e','e.user_username = a.updatedby','LEFT');
		$scope->db->join($scope->prefix.'bank as f','f.id = a.bank_id','LEFT');
		$scope->db->join($scope->prefix.'billing as g','g.id = a.merge_id','LEFT');
		$scope->db->join($scope->prefix.'sales as h','h.id = a.sales_id','LEFT');
		$scope->db->join($scope->prefix.'customer as i','i.id = a.customer_id','LEFT');
		
		$scope->db->where('a.id', $billing_id);
		//$scope->db->where('createdby', $session_user);
		$get_last = $scope->db->get();
		if($get_last->num_rows() > 0){
			$billingData = $get_last->row();	

			if(empty($billingData->merge_billing_no)){
				$billingData->merge_billing_no = '';
			}
			if(empty($billingData->payment_type_name)){
				$billingData->payment_type_name = '';
			}
			if(empty($billingData->floorplan_name)){
				$billingData->floorplan_name = '';
			}
			if(empty($billingData->table_name)){
				$billingData->table_name = '';
			}
			if(empty($billingData->bank_name)){
				$billingData->bank_name = '';
			}
			
			$billingData->billing_no_show = $billingData->billing_no;
			if(!empty($billingData->is_reservation)){
				$billingData->billing_no_show = 'R'.$billingData->billing_no;
			}
			
			//sales
			//$billingData->sales_name = '';
			if(!empty($billingData->sales_id)){
				$sales_type_simple = 'A';
				if($billingData->sales_type == 'before_tax'){
					$sales_type_simple = 'B';
				}
				if(!empty($billingData->sales_percentage)){
					$jenis_fee = $billingData->sales_percentage.'%';
				}else{
					$jenis_fee = $billingData->sales_price;
				}
				
				$billingData->sales_name = $billingData->sales_name.' / '.$billingData->sales_company;
			}
		
		
			if(empty($billingData->payment_id)){
				$billingData->payment_id = 1;
				$billingData->payment_type_name = 'Cash';
			}
			
			//update-2001.002
			if($billingData->table_no_real != $billingData->table_no){
				$billingData->table_no = $billingData->table_no_real;
				$scope->db->update($scope->table, array('table_no' => $billingData->table_no),"id = ".$billingData->id);
			}
			
			//update table-inventory
			$all_takeaway = 0;
			if($billingData->table_tipe == 'takeaway'){
				$all_takeaway = 1;
			}
			
			$data_create = array(
				'billing_id'	=> $billingData->id,
				'table_id'	=> $billingData->table_id,
				'is_all_takeaway'	=> $all_takeaway,
			);
			$return_inv = updateTable($data_create, $billingData);
		}
		
		if($is_new AND !empty($billingData)){
			logBilling($billingData, 'Create', 'Membuat Billing '.$billingData->billing_no);
		}
		
		return $billingData;
	}
}	

if(!function_exists('get_current_date')){
	function get_current_date($show_error = true){
		$billing_date = date('ymd');
		$billing_time = date('G');
		$datenowstr = strtotime(date("d-m-Y H:i:s"));
		$datenowstr0 = strtotime(date("d-m-Y 00:00:00"));
		
		$get_opt_var = array('jam_operasional_from','jam_operasional_to','jam_operasional_extra');
		$get_opt = get_option_value($get_opt_var);
		
		$jam_operasional_from = 7;
		$jam_operasional_from_Hi = '07:00';
		if(!empty($get_opt['jam_operasional_from'])){
			$jm_opr_mktime = strtotime(date("d-m-Y")." ".$get_opt['jam_operasional_from']);
			$jam_operasional_from = date('G',$jm_opr_mktime);
			$jam_operasional_from_Hi = date('H:i',$jm_opr_mktime);
		}
		
		$jam_operasional_to = 23;
		$jam_operasional_to_Hi = '23:00';
		if(!empty($get_opt['jam_operasional_to'])){
			if($get_opt['jam_operasional_to'] == '24:00'){
				$get_opt['jam_operasional_to'] = '23:59:59';
			}
			$jm_opr_mktime = strtotime(date("d-m-Y")." ".$get_opt['jam_operasional_to']);
			$jam_operasional_to = date('G',$jm_opr_mktime);
			$jam_operasional_to_Hi = date('H:i',$jm_opr_mktime);
		}
		
		$jam_operasional_extra = 0;
		if(!empty($get_opt['jam_operasional_extra'])){
			$jam_operasional_extra = $get_opt['jam_operasional_extra'];
		}
		
		if($billing_time < $jam_operasional_from){
			//extra / early??
			
			//check extra
			$datenowstrmin1 = $datenowstr0-ONE_DAY_UNIX;
			$datenowstr_oprfrom = strtotime(date("d-m-Y", $datenowstrmin1)." ".$jam_operasional_from_Hi.":00");
			$datenowstr_oprto_org = strtotime(date("d-m-Y", $datenowstrmin1)." ".$jam_operasional_to_Hi.":00");
			$datenowstr_oprto = strtotime(date("d-m-Y", $datenowstrmin1)." ".$jam_operasional_to_Hi.":00");
			//add extra
			if(!empty($jam_operasional_extra)){
				$datenowstr_oprto += ($jam_operasional_extra*3600);
			}
			
			if($datenowstr < $datenowstr_oprto){
				$billing_date = date('ymd', $datenowstrmin1);
				$datenowstr = $datenowstrmin1;
			}else{
				
				if(!empty($jam_operasional_extra)){
					$r = array('success' => false, 'info' => 'Jam Operasional: '.date("d-m-Y H:i",$datenowstr_oprfrom).' s/d '.date("H:i",$datenowstr_oprto_org).'<br/>Jam Operasional Extra = '.date("d-m-Y H:i",$datenowstr_oprto));
				}else{
					$r = array('success' => false, 'info' => 'Jam Operasional: '.date("d-m-Y H:i",$datenowstr_oprfrom).' s/d '.date("H:i",$datenowstr_oprto));
				}
				
				if($show_error == true){
					echo json_encode($r);
					die();
				}
			}
			
		}else{
			
			$datenowstr_oprfrom = strtotime(date("d-m-Y", $datenowstr0)." ".$jam_operasional_from_Hi.":00");
			$datenowstr_oprto_org = strtotime(date("d-m-Y", $datenowstr0)." ".$jam_operasional_to_Hi.":00");
			$datenowstr_oprto = strtotime(date("d-m-Y", $datenowstr0)." ".$jam_operasional_to_Hi.":00");
			//add extra
			if(!empty($jam_operasional_extra)){
				$datenowstr_oprto += ($jam_operasional_extra*3600);
			}
			
			if($datenowstr < $datenowstr_oprto){
				$billing_date = date('ymd', $datenowstr0);
				$datenowstr = $datenowstr0;
			}else{
				if(!empty($jam_operasional_extra)){
					$r = array('success' => false, 'info' => 'Jam Operasional: '.date("d-m-Y H:i",$datenowstr_oprfrom).' s/d '.date("H:i",$datenowstr_oprto_org).'<br/>Jam Operasional Extra = '.date("d-m-Y H:i",$datenowstr_oprto));
				}else{
					$r = array('success' => false, 'info' => 'Jam Operasional: '.date("d-m-Y H:i",$datenowstr_oprfrom).' s/d '.date("H:i",$datenowstr_oprto));
				}
				if($show_error == true){
					echo json_encode($r);
					die();
				}
			}
			
		}
		
		$return_data = array(
			'billing_date' => $billing_date,
			'datenowstr' => $datenowstr,
		);
		return $return_data;
	}
}

if(!function_exists('generate_billing_no')){
	function generate_billing_no(){
		
		$scope =& get_instance();
		
		$scope->prefix_apps = config_item('db_prefix');
		$scope->prefix = config_item('db_prefix2');
		$scope->table = $scope->prefix.'billing';	

		$get_current_date = get_current_date();
		if(!empty($get_current_date['billing_date'])){
			$billing_date = $get_current_date['billing_date'];
		}
		if(!empty($get_current_date['datenowstr'])){
			$datenowstr = $get_current_date['datenowstr'];
		}
		
		if(empty($get_current_date['billing_time']) OR empty($get_current_date['datenowstr'])){
			$billing_date = date('ymd');
			$billing_time = date('G');
			$datenowstr = strtotime(date("d-m-Y H:i:s"));
			$datenowstr0 = strtotime(date("d-m-Y 00:00:00"));
			
			$get_opt_var = array('jam_operasional_from','jam_operasional_to','jam_operasional_extra');
			$get_opt = get_option_value($get_opt_var);
			
			$jam_operasional_from = 7;
			$jam_operasional_from_Hi = '07:00';
			if(!empty($get_opt['jam_operasional_from'])){
				$jm_opr_mktime = strtotime(date("d-m-Y")." ".$get_opt['jam_operasional_from']);
				$jam_operasional_from = date('G',$jm_opr_mktime);
				$jam_operasional_from_Hi = date('H:i',$jm_opr_mktime);
			}
			
			$jam_operasional_to = 23;
			$jam_operasional_to_Hi = '23:00';
			if(!empty($get_opt['jam_operasional_to'])){
				if($get_opt['jam_operasional_to'] == '24:00'){
					$get_opt['jam_operasional_to'] = '23:59:59';
				}
				$jm_opr_mktime = strtotime(date("d-m-Y")." ".$get_opt['jam_operasional_to']);
				$jam_operasional_to = date('G',$jm_opr_mktime);
				$jam_operasional_to_Hi = date('H:i',$jm_opr_mktime);
			}
			
			$jam_operasional_extra = 0;
			if(!empty($get_opt['jam_operasional_extra'])){
				$jam_operasional_extra = $get_opt['jam_operasional_extra'];
			}
			
			if($billing_time < $jam_operasional_from){
				//extra / early??
				
				//check extra
				$datenowstrmin1 = $datenowstr0-ONE_DAY_UNIX;
				$datenowstr_oprfrom = strtotime(date("d-m-Y", $datenowstrmin1)." ".$jam_operasional_from_Hi.":00");
				$datenowstr_oprto_org = strtotime(date("d-m-Y", $datenowstrmin1)." ".$jam_operasional_to_Hi.":00");
				$datenowstr_oprto = strtotime(date("d-m-Y", $datenowstrmin1)." ".$jam_operasional_to_Hi.":00");
				//add extra
				if(!empty($jam_operasional_extra)){
					$datenowstr_oprto += ($jam_operasional_extra*3600);
				}
				
				if($datenowstr < $datenowstr_oprto){
					$billing_date = date('ymd', $datenowstrmin1);
					$datenowstr = $datenowstrmin1;
				}else{
					
					if(!empty($jam_operasional_extra)){
						$r = array('success' => false, 'info' => 'Jam Operasional: '.date("d-m-Y H:i",$datenowstr_oprfrom).' s/d '.date("H:i",$datenowstr_oprto_org).'<br/>Jam Operasional Extra = '.date("d-m-Y H:i",$datenowstr_oprto));
					}else{
						$r = array('success' => false, 'info' => 'Jam Operasional: '.date("d-m-Y H:i",$datenowstr_oprfrom).' s/d '.date("H:i",$datenowstr_oprto));
					}
					echo json_encode($r);
					die();
				}
				
			}else{
				
				$datenowstr_oprfrom = strtotime(date("d-m-Y", $datenowstr0)." ".$jam_operasional_from_Hi.":00");
				$datenowstr_oprto_org = strtotime(date("d-m-Y", $datenowstr0)." ".$jam_operasional_to_Hi.":00");
				$datenowstr_oprto = strtotime(date("d-m-Y", $datenowstr0)." ".$jam_operasional_to_Hi.":00");
				//add extra
				if(!empty($jam_operasional_extra)){
					$datenowstr_oprto += ($jam_operasional_extra*3600);
				}
				
				if($datenowstr < $datenowstr_oprto){
					$billing_date = date('ymd', $datenowstr0);
					$datenowstr = $datenowstr0;
				}else{
					if(!empty($jam_operasional_extra)){
						$r = array('success' => false, 'info' => 'Jam Operasional: '.date("d-m-Y H:i",$datenowstr_oprfrom).' s/d '.date("H:i",$datenowstr_oprto_org).'<br/>Jam Operasional Extra = '.date("d-m-Y H:i",$datenowstr_oprto));
					}else{
						$r = array('success' => false, 'info' => 'Jam Operasional: '.date("d-m-Y H:i",$datenowstr_oprfrom).' s/d '.date("H:i",$datenowstr_oprto));
					}
					echo json_encode($r);
					die();
				}
				
			}
		}
		
		//if($billing_time < 7){
		//	$datenowstr = strtotime(date("d-m-Y H:i:s"))-ONE_DAY_UNIX;
		//	$billing_date = date('ymd', $datenowstr);
		//}
		
		$scope->db->select("id,billing_no");
		$scope->db->from($scope->table);
		$scope->db->where("billing_no LIKE '".$billing_date."%'");
		$scope->db->order_by('id', 'DESC');
		$get_last = $scope->db->get();
		if($get_last->num_rows() > 0){
			$data_billing = $get_last->row();
			$billing_no = $data_billing->billing_no;
			$billing_date = date('ymd', $datenowstr);
			
			//CHECK IF VALID
			if(date('ymd', $datenowstr) != substr($billing_no, 0, 6)){
				if(strtotime(date('d-m-Y')) <= strtotime(substr($billing_no, 0, 2)."-".substr($billing_no, 2, 2)."-".substr($billing_no, 4, 2))){
					//INCREMENT IF OLD DATE
					$billing_date = substr($billing_no, 0, 6);
					$billing_no = str_replace($billing_date,"",$billing_no);	
					
				}else{
					//ZERO IF NEXT DATE
					$billing_date = date('ymd', $datenowstr);
					$billing_no = 0;
				}
				
			}else{			
				$billing_date = date('ymd', $datenowstr);
				$billing_no = str_replace($billing_date,"",$billing_no);	
			}			
			$billing_no = (int) $billing_no;			
		}else{
			$billing_date = date('ymd', $datenowstr);
			$billing_no = 0;
		}
		
		$billing_no++;
		$length_no = strlen($billing_no);
		switch ($length_no) {
			case 3:
				$billing_no = '0'.$billing_no;
				break;
			case 2:
				$billing_no = '00'.$billing_no;
				break;
			case 1:
				$billing_no = '000'.$billing_no;
				break;
			default:
				$billing_no = '000'.$billing_no;
				break;
		}
		
		$billing_no = $billing_date.$billing_no;
		
		return $billing_no;		
		
	}
}	

if(!function_exists('calculateBilling')){	
	function calculateBilling($billing_id = ''){
		
		$scope =& get_instance();
		
		$scope->prefix_apps = config_item('db_prefix');
		$scope->prefix = config_item('db_prefix2');
		$scope->table_billing = $scope->prefix.'billing';
		$scope->table_billing_detail = $scope->prefix.'billing_detail';
		$scope->table_discount = $scope->prefix.'discount';
		$scope->table_discount_product = $scope->prefix.'discount_product';
		
		$calculate = $scope->input->post('calculate');
		if(empty($billing_id)){
			$billing_id = $scope->input->post('billing_id');
		}
		
		if(!empty($billing_id)){
			
			
			$get_opt_var = array('use_pembulatan','cashier_max_pembulatan','cashier_pembulatan_keatas','pembulatan_dinamis','diskon_sebelum_pajak_service');
			$get_opt = get_option_value($get_opt_var);
			
			$diskon_sebelum_pajak_service = 0;
			if(!empty($get_opt['diskon_sebelum_pajak_service'])){
				$diskon_sebelum_pajak_service = $get_opt['diskon_sebelum_pajak_service'];
			}
			
			//check billing
			$billingData = array();
			if(!empty($billing_id)){
				$scope->db->select("id, takeaway_no_tax, takeaway_no_service, 
				is_compliment, total_dp, discount_perbilling, discount_total,
				include_tax, include_service, tax_percentage, service_percentage, diskon_sebelum_pajak_service");
				$scope->db->from($scope->table_billing);
				$scope->db->where("id", $billing_id);
				$get_billing = $scope->db->get();
				if($get_billing->num_rows() > 0){
					$billingData = $get_billing->row();
					$diskon_sebelum_pajak_service = $billingData->diskon_sebelum_pajak_service;
				}
			}
			
			//UPDATE DETAIL
			$grand_total_all = 0;
			$total_billing_all = 0;
			$tax_total_all = 0;
			$service_total_all = 0;
			$discount_total_all = 0;
			$compliment_total_all = 0;
			$compliment_total_tax_service_all = 0;
			
			$all_detail_update = array();
			$scope->db->select("id, product_price, product_price_real, order_qty, 
				is_takeaway, is_compliment, discount_price, discount_percentage, discount_total, 
				include_tax, include_service, tax_percentage, service_percentage, is_promo, promo_percentage, promo_price, free_item, package_item");
			$scope->db->from($scope->table_billing_detail);
			$scope->db->where('billing_id', $billing_id);
			$scope->db->where('is_deleted = 0');
			$get_detail = $scope->db->get();
			if($get_detail->num_rows() > 0){
				foreach($get_detail->result() as $dt){
					
					$product_price = $dt->product_price;
					$order_qty = $dt->order_qty;
					$is_takeaway = $dt->is_takeaway;
					$is_compliment = $dt->is_compliment;
					
					//TAX, SERVICE, TAKE AWAY & COMPLIMENT
					$include_tax = $dt->include_tax;
					$include_service = $dt->include_service;
					$tax_percentage = $dt->tax_percentage;
					$service_percentage = $dt->service_percentage;
					$discount_percentage = $dt->discount_percentage;
					$discount_price = $dt->discount_price;
					$discount_total = $dt->discount_price*$order_qty;
					//$include_tax = $billingData->include_tax;
					//$include_service = $billingData->include_service;
					//$tax_percentage = $billingData->tax_percentage;
					//$service_percentage = $billingData->service_percentage;
					$takeaway_no_tax = $billingData->takeaway_no_tax;
					$takeaway_no_service = $billingData->takeaway_no_service;
					$billing_is_compliment = $billingData->is_compliment;
					
					//BALANCING OLD DATA
					if($is_compliment == 1){
						if($tax_percentage == '0.00' AND !empty($billingData->tax_percentage)){
							$tax_percentage = $billingData->tax_percentage;
						}
						if($service_percentage == '0.00' AND !empty($billingData->service_percentage)){
							$service_percentage = $billingData->service_percentage;
						}
					}
					
					if(empty($billingData->total_dp)){
						$billingData->total_dp = 0;
					}
					$total_dp = $billingData->total_dp;
					
					
					$tax_total = 0;
					$service_total = 0;
					$tax_total2 = 0;
					$service_total2 = 0;
					$product_price_real = 0;
					$product_price_real_before = 0;
					if(!empty($include_tax) OR !empty($include_service)){
						if(!empty($include_tax) AND !empty($include_service)){
							
							$all_percentage = 100 + $tax_percentage + $service_percentage;
							$one_percent = $product_price / $all_percentage;
							$tax_total = priceFormat($one_percent * $tax_percentage, 0, ".", "");
							$service_total = priceFormat($one_percent * $service_percentage, 0, ".", "");
							//$product_price_real = $product_price;
							$product_price_real = $product_price - ($tax_total + $service_total);
							
						}else{
							
							if(!empty($include_tax)){
								$all_percentage = 100 + $tax_percentage;
								$one_percent = $product_price / $all_percentage;
								$tax_total = priceFormat($one_percent * $tax_percentage, 0, ".", "");
								//$product_price_real = $product_price;
								$product_price_real = $product_price - ($tax_total);
							}
							
							if(!empty($include_service)){
								$all_percentage = 100 + $service_percentage;
								$one_percent = $product_price / $all_percentage;
								$service_total = priceFormat($one_percent * $service_percentage, 0, ".", "");
								//$product_price_real = $product_price;
								$product_price_real = $product_price - ($service_total);
							}
							
						}
						
						
					}else
					{
						$product_price_real = $product_price;
					}
					
					//update-2003.001
					//adjustment price
					if($diskon_sebelum_pajak_service == 1){
						
						//BEFORE TAX
						$product_price = $product_price_real;
						if($dt->is_promo == 1){
							if($dt->promo_percentage == '0.00' AND !empty($dt->promo_price)){
								$promo_price = $dt->promo_price;
							}else{
								$promo_price = priceFormat($product_price * ($dt->promo_percentage/100), 0, ".", "");
							}
							$discount_price = $promo_price;
							
						}
						
						
						$product_price = $product_price - $discount_price;
						$tax_total = priceFormat($product_price * ($tax_percentage /100), 0, ".", "");
						$service_total = priceFormat($product_price * ($service_percentage/100), 0, ".", "");
						
					}else{
						
						//AFTER TAX
						$product_price = $product_price_real;
						$tax_total = priceFormat($product_price * ($tax_percentage /100), 0, ".", "");
						$service_total = priceFormat($product_price * ($service_percentage/100), 0, ".", "");
						
						if($dt->is_promo == 1){
							if($dt->promo_percentage == '0.00' AND !empty($dt->promo_price)){
								$promo_price = $dt->promo_price;
							}else{
								$product_price_tax_service = $product_price+$tax_total+$service_total;
								$promo_price = priceFormat($product_price_tax_service * ($dt->promo_percentage/100), 0, ".", "");
							}
							$discount_price = $promo_price;
						}
						
						$product_price = $product_price - $discount_price;
					}
					
					if(empty($is_takeaway)){
						$is_takeaway = 0;
					}else{
						$is_takeaway = 1;
						
						//get takeaway config tas service default
						if(!empty($takeaway_no_tax)){
							$tax_percentage = 0;
							$tax_total = 0;
							$tax_total2 = 0;
						}
						
						if(!empty($takeaway_no_service)){
							$service_percentage = 0;
							$service_total = 0;
							$service_total2 = 0;
						}
						
					}
					
					//update-2001.002
					//BILLING COMPLIMENT
					if(empty($billing_is_compliment)){
						//$is_compliment = 0;
						
						if(empty($is_compliment)){
							$is_compliment = 0;
						}else{
							$is_compliment = 1;
							
							if(!empty($include_tax) OR !empty($include_service)){
								
								//update-2001.002
								//$product_price_real = $product_price_real-($tax_total+$service_total);
								
								$tax_total = 0;
								$service_total = 0;
								$tax_total2 = 0;
								$service_total2 = 0;
								//$tax_percentage = 0;
								//$service_percentage = 0;
							}else{
								
								//update-2001.002
								//$product_price_real = $product_price_real-($tax_total+$service_total);
								
								$tax_percentage = 0;
								$tax_total = 0;
								$tax_total2 = 0;
								$service_percentage = 0;
								$service_total = 0;
								$service_total2 = 0;
								//$product_price = 0;
								//$product_price_real = 0;
								//$discount_total = 0;
								
							}
							
						}
					}else{

						$is_compliment = 1;
						
						if(!empty($include_tax) OR !empty($include_service)){
							
							//update-2001.002
							//$product_price_real = $product_price_real-($tax_total+$service_total);
							
							$tax_total = 0;
							$tax_total2 = 0;
							$service_total = 0;
							$service_total2 = 0;
							//$tax_percentage = 0;
							//$service_percentage = 0;
						}else
						{
							
							//update-2001.002
							//$product_price_real = $product_price_real-($tax_total+$service_total);
								
							$tax_percentage = 0;
							$tax_total = 0;
							$tax_total2 = 0;
							$service_percentage = 0;
							$service_total = 0;
							$service_total2 = 0;
							//$product_price = 0;
							//$product_price_real = 0;
							//$discount_total = 0;
							
						}
						
					
					}
					
					if($dt->free_item == 1 AND $dt->package_item == 0){
						$tax_percentage = 0;
						$tax_total = 0;
						$tax_total2 = 0;
						$service_percentage = 0;
						$service_total = 0;
						$service_total2 = 0;
						//$product_price = $dt->product_price;
						$product_price_real = $dt->product_price;
						//$discount_price = $product_price;
					}
					
					//update-2003.001
					if($dt->package_item == 1){
						$tax_percentage = 0;
						$tax_total = 0;
						$tax_total2 = 0;
						$service_percentage = 0;
						$service_total = 0;
						$service_total2 = 0;
						$product_price = 0;
						$product_price_real = 0;
						$dt->product_price = 0;
						//$discount_price = $product_price;
					}
					
					//$product_price_real belum di tambah $tax_total+$service_total
					//$product_price_real = $product_price_real+($tax_total+$service_total);
					$product_price_total = $product_price+($tax_total+$service_total);	
					
					$tax_total_update = ($tax_total*$order_qty);
					$service_total_update = ($service_total*$order_qty);
					$discount_total = ($discount_price*$order_qty);
					//echo 'is_compliment = '.$is_compliment.'<br/>';
					//echo 'grand_total_all = '.$grand_total_all.', product_price = '.$product_price_real.', tax = '.$tax_total2.', service_total = '.$service_total2.' <br/>';
					
					//override package
					if($dt->package_item == 1){
						$tax_percentage = 0;
						$tax_total = 0;
						$tax_total2 = 0;
						$service_percentage = 0;
						$service_total = 0;
						$service_total2 = 0;
						$product_price = 0;
						$product_price_real = 0;
						$dt->product_price = 0;
					}
					
					//REAL TOTAL
					$grand_total_all += ($product_price_total*$order_qty);
					//$total_billing_all += ($product_price_real*$order_qty);
					$tax_total_all += ($tax_total*$order_qty);
					$service_total_all += ($service_total*$order_qty);
					
					//$grand_total_all += ($tax_total*$order_qty);
					//$grand_total_all += ($service_total*$order_qty);
					
					$discount_total_all += $discount_total;
					
					//if(!empty($include_tax) OR !empty($include_service)){
					//	$dt->product_price = $product_price_real;
					//}
					
					if($dt->is_promo == 1){
						$grand_total_all += $discount_total;
						//$total_billing_all += ($dt->product_price*$order_qty);
						$total_billing_all += ($product_price_real*$order_qty);
					}else{
						$grand_total_all += $discount_total;
						//$total_billing_all += ($dt->product_price*$order_qty);
						$total_billing_all += ($product_price_real*$order_qty);
					}
					
					//echo 'grand_total_all = '.$grand_total_all.' - '.$discount_total_all.'<br/>';
					
					if(!empty($is_compliment)){
						//COMPLIMENT -------------
						$compliment_total = ($product_price*$order_qty);
						$compliment_total_all += $compliment_total;
						$compliment_total_tax_service = ($product_price*$order_qty);
						$compliment_total_tax_service_all += $compliment_total_tax_service;
					}
					
					if(empty($billingData->discount_perbilling)){
						//$grand_total_all -= $discount_total;
					}
					
					
					$all_detail_update[] = array(
						'id'			=> $dt->id,
						//'product_price_real'	=> $product_price_real,
						//'order_qty'	=> $order_qty,
						//'discount_price'	=> $discount_price,
						'discount_total'	=> $discount_total,
						'tax_total'			=> $tax_total_update,
						//'tax_total_update'			=> $tax_total_update,
						//'tax_percentage'	=> $tax_percentage,
						'service_total'			=> $service_total_update,
						//'service_total_update'			=> $service_total_update,
						//'service_percentage'	=> $service_percentage
					);
					
					
				}
				
				//UPDATE DETAIL
				if(!empty($all_detail_update)){
					$scope->db->update_batch($scope->table_billing_detail,$all_detail_update,"id");
					//echo '<pre>';
					//print_r($all_detail_update);
					//die();
				}
				
				
				
			}
			
			//DP
			$total_dp = $billingData->total_dp;
			$grand_total_all -= $total_dp;
			
			//discount
			if(!empty($billingData->discount_perbilling)){
				$discount_total_all = $billingData->discount_total;
			}
			$grand_total_all -= $discount_total_all;
			
			//compliment
			//if(!empty($billingData->compliment_total)){
			//	$compliment_total_all = $billingData->compliment_total;
			//}
			
			//if(!empty($billingData->compliment_total_tax_service)){
			//	$compliment_total_tax_service_all = $billingData->compliment_total_tax_service;
			//}
			
			//echo 'compliment_total_all = '.$compliment_total_all.'<br/>';
			//echo 'grand_total_all = '.$grand_total_all.'<br/>';
			
			$grand_total_all -= $compliment_total_all;
			
			if($grand_total_all <= 0){
				$grand_total_all = 0;
			}
			
			//PEMBULATAN
			//update.2003-001
			$data_pembulatan = array(
				'total' 					=> $grand_total_all,
				'cashier_max_pembulatan' 	=> $get_opt['cashier_max_pembulatan'],
				'cashier_pembulatan_keatas' => $get_opt['cashier_pembulatan_keatas'],
				'pembulatan_dinamis' 		=> $get_opt['pembulatan_dinamis'],
				'use_pembulatan' 			=> $get_opt['use_pembulatan'],
			);
			$total_pembulatan = hitungPembulatan($data_pembulatan);	
			
			$grand_total_all_awal = $grand_total_all;
			$grand_total_all = $grand_total_all_awal+$total_pembulatan;
			
			//die($grand_total_all);
			$update_total = array(
				'grand_total'	=> $grand_total_all,
				'total_billing'	=> $total_billing_all,
				'tax_total'		=> $tax_total_all,
				'service_total'	=> $service_total_all,
				'discount_total' => $discount_total_all,
				'total_pembulatan' => $total_pembulatan,
				'compliment_total' => $compliment_total_all,
				'compliment_total_tax_service' => $compliment_total_tax_service_all,
				'total_dp'		=> $total_dp
			);
			$scope->db->update($scope->table_billing, $update_total, "id = ".$billing_id);
			
			
			
			$total_billing_display = 0;
			if(!empty($billingData->include_tax) OR !empty($billingData->include_service)){
				$total_billing_display = $total_billing_all;
				
				if(!empty($billingData->include_tax)){
					$total_billing_display += $tax_total_all;
				}
				if(!empty($billingData->include_service)){
					$total_billing_display += $service_total_all;
				}
				
			}else{
				$total_billing_display = $total_billing_all;
			}
			
			$total_billing_display = $grand_total_all;
			
			$update_total['total_billing_display'] = $total_billing_display;
			$update_total['compliment_total'] = $compliment_total_all;
			$update_total['compliment_total_tax_service'] = $compliment_total_tax_service_all;
			
			//echo '<pre>';
			//print_r($update_total);
			//die();
			
			if($calculate == 1){
				$r = array('success' => true);
				echo json_encode($r);
				die();
			}
			
			return $update_total;
		}
		
	}
}


if(!function_exists('logBilling')){	
	function logBilling($billingData = array(), $type = '',  $info = ''){
		
		$scope =& get_instance();
		
		$scope->prefix_apps = config_item('db_prefix');
		$scope->prefix = config_item('db_prefix2');
		$session_user = $scope->session->userdata('user_username');
		
		$opt_var = array('billing_log');
		$get_opt = get_option_value($opt_var);
		
		if(!empty($billingData) AND !empty($info) AND !empty($session_user) AND !empty($get_opt['billing_log'])){
			$data_log = array(
					'billing_id' => $billingData->id,
					'trx_type' => $type,
					'trx_info' => $info,
					'log_data' => json_encode($billingData),
					'createdby' => $session_user,
					'created' => date("Y-m-d H:i:s")
			);
			$scope->db->insert($scope->prefix.'billing_log', $data_log);
		}
	}
}

if(!function_exists('updateTable')){	
	
	function updateTable($data_create = array(), $billingData = array()){
		
		$scope =& get_instance();
		
		$session_user = $scope->session->userdata('user_username');
		$id_user = $scope->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$scope->prefix_apps = config_item('db_prefix');
		$scope->prefix = config_item('db_prefix2');
		$scope->table = $scope->prefix.'billing';		
		$scope->table_billing = $scope->prefix.'billing';		
		$scope->table_detail = $scope->prefix.'billing_detail';		
		$scope->table_inv = $scope->prefix.'table_inventory';		
		$scope->table_master = $scope->prefix.'table';		
		$billing_id = $scope->input->post('billing_id', true);
		$table_id = $scope->input->post('table_id', true);
		$is_block_table = $scope->input->post('is_block_table', true);
		$is_all_takeaway = $scope->input->post('is_all_takeaway', true);
		$is_delete = $scope->input->post('is_delete', true);
		$set_default = $scope->input->post('set_default', true);
		
		if(empty($is_block_table)){
			$is_block_table = 0;
		}
		if(empty($is_all_takeaway)){
			$is_all_takeaway = 0;
		}
		
		//update-2001.002
		if(!empty($data_create)){
			$billing_id = $data_create['billing_id'];
			$table_id = $data_create['table_id'];
			$is_all_takeaway = $data_create['is_all_takeaway'];
		}
		
		$r = array('success' => false);
		
		if(!empty($billingData)){
			$billing_id = $billingData->billing_id;
			$table_id = $billingData->table_id;
			$table_no = $billingData->table_no;
		}else{
			$billingData = getBilling($billing_id);	
		}
		
		if(empty($billing_id)){
			$r = array('success' => false, 'info' => 'Please Pilih Table/Meja!');
		}else{
			
			//CLOSING DATE
			$var_closing = array(
				'xdate'	=> $billingData->created,
				'xtipe'	=> 'sales'
			);
			$is_closing = is_closing($var_closing);
			if($is_closing){
				$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal tersebut sudah ditutup!'); 
				die(json_encode($r));
			}
			
			//INV
			$date_today = date("Y-m-d");
			$date_time_today = date("Y-m-d H:i:s");
			
			//update-2001.002
			$get_opt_var = array('jam_operasional_from','jam_operasional_to','jam_operasional_extra');
			$get_opt = get_option_value($get_opt_var);
			
			$billing_time = date('G');
			$datenowstr = strtotime(date("d-m-Y H:i:s"));
			$datenowstr0 = strtotime(date("d-m-Y 00:00:00"));
			
			$jam_operasional_from = 7;
			$jam_operasional_from_Hi = '07:00';
			if(!empty($get_opt['jam_operasional_from'])){
				$jm_opr_mktime = strtotime(date("d-m-Y")." ".$get_opt['jam_operasional_from']);
				$jam_operasional_from = date('G',$jm_opr_mktime);
				$jam_operasional_from_Hi = date('H:i',$jm_opr_mktime);
			}
			
			$jam_operasional_to = 23;
			$jam_operasional_to_Hi = '23:00';
			if(!empty($get_opt['jam_operasional_to'])){
				if($get_opt['jam_operasional_to'] == '24:00'){
					$get_opt['jam_operasional_to'] = '23:59:59';
				}
				$jm_opr_mktime = strtotime(date("d-m-Y")." ".$get_opt['jam_operasional_to']);
				$jam_operasional_to = date('G',$jm_opr_mktime);
				$jam_operasional_to_Hi = date('H:i',$jm_opr_mktime);
			}
			
			$jam_operasional_extra = 0;
			if(!empty($get_opt['jam_operasional_extra'])){
				$jam_operasional_extra = $get_opt['jam_operasional_extra'];
			}
			
			if($billing_time < $jam_operasional_from){
				//extra / early??
	
				//check extra
				$datenowstrmin1 = $datenowstr0-ONE_DAY_UNIX;
				$datenowstr_oprfrom = strtotime(date("d-m-Y", $datenowstrmin1)." ".$jam_operasional_from_Hi.":00");
				$datenowstr_oprto_org = strtotime(date("d-m-Y", $datenowstrmin1)." ".$jam_operasional_to_Hi.":00");
				$datenowstr_oprto = strtotime(date("d-m-Y", $datenowstrmin1)." ".$jam_operasional_to_Hi.":00");
				//add extra
				if(!empty($jam_operasional_extra)){
					$datenowstr_oprto += ($jam_operasional_extra*3600);
				}
				
				if($datenowstr < $datenowstr_oprto){
					$date_today = date('Y-m-d', $datenowstr_oprfrom);
				}else{
					$date_today = date('Y-m-d', $datenowstr_oprfrom+ONE_DAY_UNIX);
				}
				
			}else{
	
				$datenowstr_oprfrom = strtotime(date("d-m-Y", $datenowstr0)." ".$jam_operasional_from_Hi.":00");
				$datenowstr_oprto_org = strtotime(date("d-m-Y", $datenowstr0)." ".$jam_operasional_to_Hi.":00");
				$datenowstr_oprto = strtotime(date("d-m-Y", $datenowstr0)." ".$jam_operasional_to_Hi.":00");
				//add extra
				if(!empty($jam_operasional_extra)){
					$datenowstr_oprto += ($jam_operasional_extra*3600);
				}
				
				if($datenowstr < $datenowstr_oprto){
					$date_today = date('Y-m-d', $datenowstr_oprfrom);
				}
				
			}
			
			if(!empty($is_delete)){
				$data_table = array(
					'status' => 'available',
					'billing_no' => '',
					'updated' => $date_time_today,
					'updatedby' => $session_user
				);
				$scope->db->update($scope->table_inv, $data_table, "billing_no = '".$billingData->billing_no."' AND  table_id = '".$table_id."' AND tanggal = '".$date_today."'");
				
				$r = array('success' => true, 'set_default' => 0);
				
			}else{
				
				if(empty($set_default)){
					if(empty($is_block_table)){
						$data_table = array(
							'table_id' => $table_id,
							'block_table' => 0
						);
								
						//UPDATE OPTIONS
						$scope->db->update($scope->table_billing, $data_table, "id = '".$billing_id."'");
					
					
						//clear inv $billingData->billing_no
						if($billingData->table_id != $table_id){
							$data_table = array(
								'status' => 'available',
								'billing_no' => '',
								'updated' => $date_time_today,
								'updatedby' => $session_user
							);
							$scope->db->update($scope->table_inv, $data_table, "billing_no = '".$billingData->billing_no."' AND tanggal = '".$date_today."'");
						}
					}else{
						$data_table = array(
							'block_table' => 1
						);
								
						//UPDATE OPTIONS
						$scope->db->update($scope->table_billing, $data_table, "id = '".$billing_id."'");
					}
				
				
					//echo '<pre>';
					//print_r($billingData);
					//$r = array('success' => false, 'data' => $billingData );
					//die(json_encode($r));
							
					//UPDATE OPTIONS
					$scope->db->where("table_id = '".$table_id."' AND tanggal = '".$date_today."'");
					$get_inv = $scope->db->get($scope->table_inv);
					if($get_inv->num_rows() > 0){
						$data_table = array(
							'status' => 'booked',
							'billing_no' => $billingData->billing_no,
							'updated' => $date_time_today,
							'updatedby' => $session_user
						);
						$scope->db->update($scope->table_inv, $data_table, "table_id = '".$table_id."' AND tanggal = '".$date_today."'");
					}else{
						$data_table = array(
							'status' => 'booked',
							'table_id' => $table_id,
							'billing_no' => $billingData->billing_no,
							'tanggal' => $date_today,
							'created' => $date_time_today,
							'createdby' => $session_user,
							'updated' => $date_time_today,
							'updatedby' => $session_user
						);
						$scope->db->insert($scope->table_inv, $data_table);
					}
					
				}else{
					if(!empty($is_block_table)){
						$data_table = array(
							'block_table' => 1,
							//'table_id' => $table_id,
						);
								
						//UPDATE OPTIONS
						$scope->db->update($scope->table_billing, $data_table, "id = '".$billing_id."'");
						
						//UPDATE OPTIONS
						$scope->db->where("table_id = '".$table_id."' AND tanggal = '".$date_today."'");
						$get_inv = $scope->db->get($scope->table_inv);
						if($get_inv->num_rows() > 0){
							$data_table = array(
								'status' => 'booked',
								'billing_no' => $billingData->billing_no,
								'updated' => $date_time_today,
								'updatedby' => $session_user
							);
							$scope->db->update($scope->table_inv, $data_table, "table_id = '".$table_id."' AND tanggal = '".$date_today."'");
						}else{
							$data_table = array(
								'status' => 'booked',
								'table_id' => $table_id,
								'billing_no' => $billingData->billing_no,
								'tanggal' => $date_today,
								'created' => $date_time_today,
								'createdby' => $session_user,
								'updated' => $date_time_today,
								'updatedby' => $session_user
							);
							$scope->db->insert($scope->table_inv, $data_table);
						}
					}
				}	
				
				$r = array('success' => true, 'set_default' => 0);
			}
			
			if(($billingData->table_id == $table_id AND $is_delete == 1) OR empty($billingData->table_id) OR !empty($set_default)){
				//ganti default
				$table_id_default = 0;
				$table_no_default = '';
				
				$scope->db->select("a.id,a.table_id, b.table_no");
				$scope->db->from($scope->table_inv.' as a');
				$scope->db->join($scope->table_master.' as b',"b.id = a.table_id","LEFT");
				$scope->db->where("a.billing_no = '".$billingData->billing_no."' AND a.tanggal = '".$date_today."'");
				
				if(!empty($set_default)){
					$scope->db->where("a.table_id = '".$table_id."'");
				}
				
				$scope->db->order_by("a.updated","ASC");
				$get_inv = $scope->db->get();
				if($get_inv->num_rows() > 0){
					$get_table_id = $get_inv->row();
					$table_id_default = $get_table_id->table_id;
					$table_no_default = $get_table_id->table_no;
					
					$data_table = array(
						'table_id' => $table_id_default,
						'table_no' => $table_no_default,
					);
							
					//UPDATE OPTIONS
					$scope->db->update($scope->table_billing, $data_table, "id = '".$billing_id."'");
					
				}
				
				$r = array('success' => true, 'set_default' => 1, 'table_id' => $table_id_default, 'table_no' => $table_no_default);
			
			}
			
			if(empty($is_block_table) AND (!empty($billing_id) OR !empty($set_default))){
				$get_opt_var = array('takeaway_no_tax','takeaway_no_service','set_ta_table_ta','as_server_backup');
				$get_opt = get_option_value($get_opt_var);
				
				cek_server_backup($get_opt);
				
				$set_ta_table_ta = 0;
				if(!empty($get_opt['set_ta_table_ta'])){
					$set_ta_table_ta = $get_opt['set_ta_table_ta'];
				}
				
				$takeaway_no_tax = 0;
				if(!empty($get_opt['takeaway_no_tax'])){
					$takeaway_no_tax = $get_opt['takeaway_no_tax'];
				}
				
				$takeaway_no_service = 0;
				if(!empty($get_opt['takeaway_no_service'])){
					$takeaway_no_service = $get_opt['takeaway_no_service'];
				}
				
				if($set_ta_table_ta == 1){
					
					//update 2018-02-25
					if(!empty($is_all_takeaway)){
						$data_takeaway = array(
							'is_takeaway' => 1,
							'takeaway_no_tax' => $takeaway_no_tax,
							'takeaway_no_service' => $takeaway_no_service,
						);
					}else{
						
						if(!empty($billingData->table_tipe)){
							//old
							if($billingData->table_tipe == 'takeaway'){
								$data_takeaway = array(
									'is_takeaway' => 0,
									'takeaway_no_tax' => 0,
									'takeaway_no_service' => 0,
								);
							}
							
						}
						
					}
					
					if(!empty($data_takeaway)){
						$scope->db->update($scope->table_detail, $data_takeaway, "billing_id = '".$billing_id."' AND is_deleted = 0");
					}
				}
				
				$r['is_all_takeaway'] = $is_all_takeaway; 
			}
			
			//update-2001.002
			//optimazing table inv: $table_id, $$billingData->table_id
			$scope->billing = $scope->prefix.'billing';
			$scope->floorplan = $scope->prefix.'floorplan';
			$scope->room = $scope->prefix.'room';
			$scope->table = $scope->prefix.'table';
			$scope->table_inventory = $scope->prefix.'table_inventory';	
			
			$scope->db->select('a.id, a.id as invid, a.table_id, a.billing_no, a.tanggal, a.status, a.total_billing, b.*, 
									c.floorplan_name, c.list_no, c2.room_name, c2.room_no, 
									d.id as billing_id, d.billing_status, d.total_guest, d.table_id as billing_table');
			$scope->db->from($scope->table_inventory.' as a');
			$scope->db->join($scope->table.' as b','b.id = a.table_id','LEFT');
			$scope->db->join($scope->floorplan.' as c','c.id = b.floorplan_id','LEFT');
			$scope->db->join($scope->room.' as c2','c2.id = b.room_id','LEFT');
			$scope->db->join($scope->billing.' as d','d.billing_no = a.billing_no','LEFT');
			$scope->db->where("b.is_deleted = 0 AND a.tanggal = '".$date_today."'");
			$scope->db->order_by('c.list_no','ASC');
			$scope->db->order_by('b.id','ASC');
			$scope->db->order_by('b.table_no','ASC');
			
			$get_invtable = $scope->db->get();
			
			$get_data = array('data' => array());
			if($get_invtable->num_rows() > 0){
				$get_data['data'] = $get_invtable->result_array();
			}
			
			$tanggalexp = explode("-", $date_today);
			$tanggalmk = strtotime($tanggalexp[2].'-'.$tanggalexp[1].'-'.$tanggalexp[0]);
			
			//update-2001.002
			//check hold billing
			$data_billing = array();
			//$tanggalmk = strtotime($tanggal);
			$billno = date("ymd", $tanggalmk);
			$scope->db->select('*');
			$scope->db->from($scope->billing);
			$scope->db->where("billing_no LIKE '".$billno."%' AND billing_status = 'hold' AND is_deleted = 0 AND table_id > 0");
			$get_bill = $scope->db->get();
			if($get_bill->num_rows() > 0){
				foreach($get_bill->result() as $dt){
					if(empty($data_billing[$dt->table_id])){
						$data_billing[$dt->table_id] = array();
					}
					
					$data_billing[$dt->table_id][] = array(
						'billing_id'	=> $dt->id,
						'billing_no'	=> $dt->billing_no,
						'table_no'		=> $dt->table_no
					);
				}
			}
			
			$update_table_booked_paid = array();
			$update_table_hold = array();
			if(!empty($get_data['data'])){
				foreach ($get_data['data'] as $s){
					
					if(!empty($data_billing[$s['table_id']])){
						$get_billno = '';
						if(!empty($data_billing[$s['table_id']][0]['billing_no'])){
							$get_billno = $data_billing[$s['table_id']][0]['billing_no'];
						}
						$update_table_hold[] = array(
							'id'			=> $s['invid'],
							'billing_no'	=> $get_billno,
							'total_billing'	=> count($data_billing[$s['table_id']]),
							'status'		=> 'booked',
						);
					}else{
						//if booked and paid -> table should available
						if($s['status'] == 'booked' AND !empty($s['billing_id']) AND $s['billing_status'] != 'hold'){
							$update_table_booked_paid[] = array(
								'id'		=> $s['invid'],
								'status'	=> 'available',
								'billing_no'=> ''
							);
							$s['status'] = 'available';
							$s['billing_id'] = '';
							$s['billing_no'] = '';
							$s['billing_status'] = '';
						}
						
						if($s['status'] == 'booked' AND $s['billing_table'] != $s['table_id']){
							$update_table_booked_paid[] = array(
								'id'		=> $s['invid'],
								'status'	=> 'available',
								'billing_no'=> ''
							);
							$s['status'] = 'available';
							$s['billing_id'] = '';
							$s['billing_no'] = '';
							$s['billing_status'] = '';
						}
						
						//if booked and paid -> table should available
						if($s['status'] == 'booked' AND empty($s['billing_id']) AND empty($s['billing_status'])){
							$update_table_booked_paid[] = array(
								'id'		=> $s['invid'],
								'status'	=> 'available',
								'billing_no'=> ''
							);
							$s['status'] = 'available';
							$s['billing_id'] = '';
							$s['billing_no'] = '';
							$s['billing_status'] = '';
						}
					}
				}
				
				//update-2001.002
				if(!empty($update_table_hold)){
					$scope->db->update_batch($scope->table_inventory, $update_table_hold, "id");
				}
				if(!empty($update_table_booked_paid)){
					$scope->db->update_batch($scope->table_inventory, $update_table_booked_paid, "id");
				}
			}
			
		}
		
		//update-2001.002
		if(!empty($data_create)){
			return $r;
		}
		
		die(json_encode($r));
	}
}

//PEMBULATAN
if(!function_exists('hitungPembulatan')){	
	
	function hitungPembulatan($data_pembulatan = array()){
		
		if(empty($data_pembulatan)){
			return 0;
		}
		if(empty($data_pembulatan['total'])){
			$data_pembulatan['total'] = 0;
			return 0;
		}
		if(empty($data_pembulatan['cashier_max_pembulatan'])){
			$data_pembulatan['cashier_max_pembulatan'] = 0;
			return 0;
		}
		if(empty($data_pembulatan['cashier_pembulatan_keatas'])){
			$data_pembulatan['cashier_pembulatan_keatas'] = 0;
		}
		if(empty($data_pembulatan['pembulatan_dinamis'])){
			$data_pembulatan['pembulatan_dinamis'] = 0;
		}
		
		$total = $data_pembulatan['total'];
		
		//check koma
		$total_exp = explode(".",$total);
		$koma = 0;
		if(!empty($total_exp[1])){
			$total = $total_exp[0];
			$koma = "0.".$total_exp[1];
		}
		
		$total_pembulatan = 0;
		$max_pembulatan = $data_pembulatan['cashier_max_pembulatan'];
		$pembulatan_keatas = $data_pembulatan['cashier_pembulatan_keatas'];
		$pembulatan_dinamis = $data_pembulatan['pembulatan_dinamis'];
		
		//update-2003.001
		$max_pembulatan_half = ceil($max_pembulatan/2);
		$max_pembulatan_length = strlen($max_pembulatan);
		if($max_pembulatan_length > 3){
			$last2digit = substr($total, (($max_pembulatan_length-1)*-1));
		}else{
			$last2digit = substr($total,-2);
		}
		
		$last2digit = intval($last2digit);
		
		if(!empty($koma)){
			$last2digit += floatval($koma);
		}	
		
		//dibawah max pembulatan
		if($last2digit > 0){
			if(empty($pembulatan_keatas)){
				
				//$total_pembulatan = $last2digit;
				$total_pembulatan = $last2digit*-1;
				
				if(!empty($pembulatan_dinamis)){
					if($last2digit <= $max_pembulatan_half){
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
		
		if(empty($data_pembulatan['use_pembulatan'])){
			$total_pembulatan = 0;
		}
		
		return $total_pembulatan;
		
	}
	
}

?>