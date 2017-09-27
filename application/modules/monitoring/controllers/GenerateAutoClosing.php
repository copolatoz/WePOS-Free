<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class GenerateAutoClosing extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->prefix_apps = config_item('db_prefix');
		$this->load->model('model_generateautoclosing', 'm');
		$this->load->model('model_generateautoclosingdetail', 'm2');
	}

	public function gridData()
	{
		
		//if empty check on opt = closing_sales_start_date
		$opt_value = array(
			'autoclosing_generate_sales',
			'autoclosing_closing_sales',
			'autoclosing_auto_cancel_billing',
			'autoclosing_generate_purchasing',
			'autoclosing_closing_purchasing',
			'autoclosing_auto_cancel_receiving',
			'autoclosing_generate_inventory',
			'autoclosing_generate_stock',
			'autoclosing_closing_inventory',
			'autoclosing_auto_cancel_distribution',
			'autoclosing_auto_cancel_production',
			'autoclosing_generate_accounting',
			'autoclosing_closing_accounting',
			'autoclosing_skip_open_jurnal',
			'autoclosing_generate_timer',
			'autoclosing_closing_time'
		);
		$get_opt = get_option_value($opt_value);
		
		$opt_value_name = array(
			'autoclosing_generate_sales' => 'Auto Generate Sales',
			'autoclosing_generate_purchasing' => 'Auto Generate Purchasing',
			'autoclosing_generate_inventory' => 'Auto Generate Inventory',
			'autoclosing_generate_stock' => 'Auto Generate List Stock',
			'autoclosing_generate_accounting' => 'Auto Generate Accounting',
			'autoclosing_closing_sales' => 'Auto Closing Sales',
			'autoclosing_closing_purchasing' => 'Auto Closing Purchasing',
			'autoclosing_closing_inventory' => 'Auto Closing Inventory',
			'autoclosing_closing_accounting' => 'Auto Closing Accounting',
			'autoclosing_auto_cancel_billing' => 'Auto Cancel Active/Hold Billing',
			'autoclosing_auto_cancel_receiving' => 'Auto Cancel Active/Open Receiving',
			'autoclosing_auto_cancel_distribution' => 'Auto Cancel Active/Open Distribution',
			'autoclosing_auto_cancel_production' => 'Auto Cancel Active/Open Production',
			'autoclosing_skip_open_jurnal' => 'Auto Cancel Active/Open Jurnal',
			'autoclosing_generate_timer' => 'Generate Timer/Loop',
			'autoclosing_closing_time' => 'Closing Time',
		);
		
		$opt_value_default = array(
			'autoclosing_generate_sales' => 1,
			'autoclosing_generate_purchasing' => 1,
			'autoclosing_generate_inventory' => 1,
			'autoclosing_generate_stock' => 1,
			'autoclosing_generate_accounting' => 0,
			'autoclosing_closing_sales' => 1,
			'autoclosing_closing_purchasing' => 1,
			'autoclosing_closing_inventory' => 1,
			'autoclosing_closing_accounting' => 0,
			'autoclosing_auto_cancel_billing' => 1,
			'autoclosing_auto_cancel_receiving' => 1,
			'autoclosing_auto_cancel_distribution' => 1,
			'autoclosing_auto_cancel_production' => 1,
			'autoclosing_skip_open_jurnal' => 0,
			'autoclosing_generate_timer' => '360000', //second
			'autoclosing_closing_time' => "03:00",
		);
		
		$opt_value_tipe = array(
			'autoclosing_generate_sales' => 'bool',
			'autoclosing_generate_purchasing' => 'bool',
			'autoclosing_generate_inventory' => 'bool',
			'autoclosing_generate_stock' => 'bool',
			'autoclosing_generate_accounting' => 'bool',
			'autoclosing_closing_sales' => 'bool',
			'autoclosing_closing_purchasing' => 'bool',
			'autoclosing_closing_inventory' => 'bool',
			'autoclosing_closing_accounting' => 'bool',
			'autoclosing_auto_cancel_billing' => 'bool',
			'autoclosing_auto_cancel_receiving' => 'bool',
			'autoclosing_auto_cancel_distribution' => 'bool',
			'autoclosing_auto_cancel_production' => 'bool',
			'autoclosing_skip_open_jurnal' => 'bool',
			'autoclosing_generate_timer' => 'text',
			'autoclosing_closing_time' => 'text',
		);
		
		
		$insert_value = array();
		
		if(!isset($get_opt['autoclosing_generate_sales'])){
			$insert_value[] = array("option_var" => "autoclosing_generate_sales", "option_value" => $opt_value_default['autoclosing_generate_sales']);
		}else{
			$opt_value_default['autoclosing_generate_sales'] = $get_opt['autoclosing_generate_sales'];
		}
		
		if(!isset($get_opt['autoclosing_generate_purchasing'])){
			$insert_value[] = array("option_var" => "autoclosing_generate_purchasing", "option_value" => $opt_value_default['autoclosing_generate_purchasing']);
		}else{
			$opt_value_default['autoclosing_generate_purchasing'] = $get_opt['autoclosing_generate_purchasing'];
		}
		
		if(!isset($get_opt['autoclosing_generate_inventory'])){
			$insert_value[] = array("option_var" => "autoclosing_generate_inventory", "option_value" => $opt_value_default['autoclosing_generate_inventory']);
		}else{
			$opt_value_default['autoclosing_generate_inventory'] = $get_opt['autoclosing_generate_inventory'];
		}
		
		if(!isset($get_opt['autoclosing_generate_stock'])){
			$insert_value[] = array("option_var" => "autoclosing_generate_stock", "option_value" => $opt_value_default['autoclosing_generate_stock']);
		}else{
			$opt_value_default['autoclosing_generate_stock'] = $get_opt['autoclosing_generate_stock'];
		}
		
		if(!isset($get_opt['autoclosing_generate_accounting'])){
			$insert_value[] = array("option_var" => "autoclosing_generate_accounting", "option_value" => $opt_value_default['autoclosing_generate_accounting']);
		}else{
			$opt_value_default['autoclosing_generate_accounting'] = $get_opt['autoclosing_generate_accounting'];
		}
		
		if(!isset($get_opt['autoclosing_closing_sales'])){
			$insert_value[] = array("option_var" => "autoclosing_closing_sales", "option_value" => $opt_value_default['autoclosing_closing_sales']);
		}else{
			$opt_value_default['autoclosing_closing_sales'] = $get_opt['autoclosing_closing_sales'];
		}
		
		if(!isset($get_opt['autoclosing_closing_purchasing'])){
			$insert_value[] = array("option_var" => "autoclosing_closing_purchasing", "option_value" => $opt_value_default['autoclosing_closing_purchasing']);
		}else{
			$opt_value_default['autoclosing_closing_purchasing'] = $get_opt['autoclosing_closing_purchasing'];
		}
		
		if(!isset($get_opt['autoclosing_closing_inventory'])){
			$insert_value[] = array("option_var" => "autoclosing_closing_inventory", "option_value" => $opt_value_default['autoclosing_closing_inventory']);
		}else{
			$opt_value_default['autoclosing_closing_inventory'] = $get_opt['autoclosing_closing_inventory'];
		}
		
		if(!isset($get_opt['autoclosing_closing_accounting'])){
			$insert_value[] = array("option_var" => "autoclosing_closing_accounting", "option_value" => $opt_value_default['autoclosing_closing_accounting']);
		}else{
			$opt_value_default['autoclosing_closing_accounting'] = $get_opt['autoclosing_closing_accounting'];
		}
		
		if(!isset($get_opt['autoclosing_auto_cancel_billing'])){
			$insert_value[] = array("option_var" => "autoclosing_auto_cancel_billing", "option_value" => $opt_value_default['autoclosing_auto_cancel_billing']);
		}else{
			$opt_value_default['autoclosing_auto_cancel_billing'] = $get_opt['autoclosing_auto_cancel_billing'];
		}
		
		if(!isset($get_opt['autoclosing_auto_cancel_receiving'])){
			$insert_value[] = array("option_var" => "autoclosing_auto_cancel_receiving", "option_value" => $opt_value_default['autoclosing_auto_cancel_receiving']);
		}else{
			$opt_value_default['autoclosing_auto_cancel_receiving'] = $get_opt['autoclosing_auto_cancel_receiving'];
		}
		
		if(!isset($get_opt['autoclosing_auto_cancel_distribution'])){
			$insert_value[] = array("option_var" => "autoclosing_auto_cancel_distribution", "option_value" => $opt_value_default['autoclosing_auto_cancel_distribution']);
		}else{
			$opt_value_default['autoclosing_auto_cancel_distribution'] = $get_opt['autoclosing_auto_cancel_distribution'];
		}
		
		if(!isset($get_opt['autoclosing_auto_cancel_production'])){
			$insert_value[] = array("option_var" => "autoclosing_auto_cancel_production", "option_value" => $opt_value_default['autoclosing_auto_cancel_production']);
		}else{
			$opt_value_default['autoclosing_auto_cancel_production'] = $get_opt['autoclosing_auto_cancel_production'];
		}
		
		if(!isset($get_opt['autoclosing_skip_open_jurnal'])){
			$insert_value[] = array("option_var" => "autoclosing_skip_open_jurnal", "option_value" => $opt_value_default['autoclosing_skip_open_jurnal']);
		}else{
			$opt_value_default['autoclosing_skip_open_jurnal'] = $get_opt['autoclosing_skip_open_jurnal'];
		}
		
		if(!isset($get_opt['autoclosing_generate_timer'])){
			$insert_value[] = array("option_var" => "autoclosing_generate_timer", "option_value" => $opt_value_default['autoclosing_generate_timer']);
		}else{
			$opt_value_default['autoclosing_generate_timer'] = $get_opt['autoclosing_generate_timer'];
		}
		
		if(!isset($get_opt['autoclosing_closing_time'])){
			$insert_value[] = array("option_var" => "autoclosing_closing_time", "option_value" => $opt_value_default['autoclosing_closing_time']);
		}else{
			$opt_value_default['autoclosing_closing_time'] = $get_opt['autoclosing_closing_time'];
		}
		
		$newData = array();	
		if(!empty($opt_value)){
			foreach($opt_value as $dt){
				
				$option_value_show = '-';
				if($opt_value_tipe[$dt] == 'bool'){
					$option_value_show = '<font style="color:red; font-weight:bold;">No</font>';
					if($opt_value_default[$dt] == 1){
						$option_value_show = '<font style="color:green; font-weight:bold;">Yes</font>';
					}
				}else{
					if(!empty($opt_value_default[$dt])){
						$option_value_show = '<font style="color:green; font-weight:bold;">'.$opt_value_default[$dt].'</font>';
					}
				}
				
				$dt_push = array(
					'option_name'	=> $opt_value_name[$dt],
					'option_var'	=> $dt,
					'option_value'	=> $opt_value_default[$dt],
					'option_tipe'	=> $opt_value_tipe[$dt],
					'option_value_show'	=> $option_value_show
				);
				
				array_push($newData, $dt_push);
				
			}
		}
		
		
		/*
		echo '<pre>';
		print_r($newData);
		die();
		*/
		
		if(!empty($insert_value)){
			$this->db->insert_batch($this->prefix_apps.'options', $insert_value, "option_var");
		}
		
		$get_data['data'] = $newData;
		$get_data['totalCount'] = count($newData);
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetail()
	{
		
		$this->table_closing_log = $this->prefix.'closing_log';
		
		$session_client_id = $this->session->userdata('client_id');	
				
		//is_active_text
		$sortAlias = array(
			//'is_active_text' => 'is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> '*',
			'primary_key'	=> 'id',
			'table'			=> $this->table_closing_log,
			'where'			=> array(),
			'order'			=> array('id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['time'] = date("d-m-Y H:i", strtotime($s['created']));
				
				if($s['task'] == 'closingDate'){
					$s['task'] = 'closing';
				}
				
				$s['task'] = ucwords($s['task']);
				
				if($s['task_status'] == 'true'){
					$s['task_status'] = '<font style="color:green; font-weight:bold">Success</font>';
				}else{
					$s['task_status'] = '<font style="color:red; font-weight:bold">Failed</font>';
				}
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
		
	}
	
	public function saveLog()
	{
		$this->table_closing_log = $this->prefix.'closing_log';
		
		$session_client_id = $this->session->userdata('client_id');	
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			die(json_encode($r));
		}
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		$task_status = $this->input->post('task_status');
		$notes = $this->input->post('notes');
		$task_data = $this->input->post('task_data');
		$task_data = json_decode($task_data, true);
		
		//echo '<pre>';
		//print_r($task_data);
		//die();
		
		$saveLog = '';
		$tanggal = date("Y-m-d");
		
		if(!empty($task_data)){
			$data_log = array(
				'tanggal'	=> $tanggal,
				'tipe'		=> $task_data['file'],
				'task'		=>  $task_data['action'],
				'task_status'=>  $task_status,
				'notes'		=>  $notes,
				'created'	=>	date('Y-m-d H:i:s'),
				'createdby'	=>	$session_user,
				'updated'	=>	date('Y-m-d H:i:s'),
				'updatedby'	=>	$session_user
			);
			
			$saveLog = $this->db->insert($this->table_closing_log, $data_log);
		}
		
		if($saveLog)
		{  
			$r = array('success' => true, 'id' => $insert_id); 				
		}  
		else
		{  
			$r = array('success' => false);
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function generate()
	{
		
		
		$session_client_id = $this->session->userdata('client_id');	
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			die(json_encode($r));
		}
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		//if empty check on opt = closing_sales_start_date
		$opt_value = array(
			'autoclosing_generate_sales',
			'autoclosing_closing_sales',
			'autoclosing_auto_cancel_billing',
			'autoclosing_generate_purchasing',
			'autoclosing_closing_purchasing',
			'autoclosing_auto_cancel_receiving',
			'autoclosing_generate_inventory',
			'autoclosing_generate_stock',
			'autoclosing_closing_inventory',
			'autoclosing_auto_cancel_distribution',
			'autoclosing_generate_accounting',
			'autoclosing_closing_accounting',
			'autoclosing_skip_open_jurnal',
			'autoclosing_generate_timer',
			'autoclosing_closing_time'
		);
		$get_opt = get_option_value($opt_value);
		
		$is_check = $this->input->post('is_check');
		
		
		$loop_timer = $get_opt['autoclosing_generate_timer'];
		$closing_time = $get_opt['autoclosing_closing_time'];
		
		$no_task = 1;
		$task_data = array();
		if($get_opt['autoclosing_generate_sales'] == 1){
			$task_data[$no_task] = 'autoclosing_generate_sales';
			$no_task++;
		}
		
		if($get_opt['autoclosing_closing_sales'] == 1){
			$task_data[$no_task] = 'autoclosing_closing_sales';
			$no_task++;
		}
		
		if($get_opt['autoclosing_generate_purchasing'] == 1){
			$task_data[$no_task] = 'autoclosing_generate_purchasing';
			$no_task++;
		}
		
		if($get_opt['autoclosing_closing_purchasing'] == 1){
			$task_data[$no_task] = 'autoclosing_closing_purchasing';
			$no_task++;
		}
		
		if($get_opt['autoclosing_generate_inventory'] == 1){
			$task_data[$no_task] = 'autoclosing_generate_inventory';
			$no_task++;
		}
		
		if($get_opt['autoclosing_generate_stock'] == 1){
			$task_data[$no_task] = 'autoclosing_generate_stock';
			$no_task++;
		}
		
		if($get_opt['autoclosing_closing_inventory'] == 1){
			$task_data[$no_task] = 'autoclosing_closing_inventory';
			$no_task++;
		}
		
		if($get_opt['autoclosing_generate_accounting'] == 1){
			$task_data[$no_task] = 'autoclosing_generate_accounting';
			$no_task++;
		}
		
		if($get_opt['autoclosing_closing_accounting'] == 1){
			$task_data[$no_task] = 'autoclosing_closing_accounting';
			$no_task++;
		}
		
		$task_total = count($task_data);
		
		
		if(!empty($is_check)){
			$r = array(
				'success' => true, 
				'loop_timer'	=> $loop_timer, 
				'closing_time'	=> $closing_time, 
				'task_data'		=> $task_data, 
				'task_total'	=> $task_total 
			);
			
			die(json_encode($r));
		}
		
		
		$do_generate_closing_text = '';
		$preparing_next_generate_text = '';
		$dt_return = array();
		
		$use_date = date("d-m-Y");
		$is_on_closing_time = false;
		$is_range_do_generate = false;
		
		$check_datetime_00 = strtotime(date("d-m-Y 00:00:01"));
		$check_datetime_closing = strtotime(date("d-m-Y ".$closing_time.":00"));
		$check_datetime_closing_plus1 = $check_datetime_closing + 3600;
		$check_datetime_closing_plus4 = $check_datetime_closing + 14400;
		$check_datetime_closing_min1 = $check_datetime_closing - 3600;
		$check_datetime_now = strtotime(date("d-m-Y H:i:s"));
		
		//cek latest task
		$date_latest_task = strtotime(date("d-m-Y 23:59:59"));
		
		//RANGE ANTAR CLOSING -- USE CURRENT DATE
		if($check_datetime_now >= $date_latest_task){
			$use_date = date("d-m-Y");
		}else{
			$use_date = date("d-m-Y", $date_latest_task);
		}
		
		if($check_datetime_now >= $check_datetime_closing AND $check_datetime_now <= $check_datetime_closing_plus1){
			//in range closing on 1 hour
			$is_on_closing_time = true;
		}
		$is_on_closing_time = true;
		
		if($check_datetime_now <= $check_datetime_closing_min1){
			//in range closing on 1 hour
			$is_range_do_generate = true;
		}else{
			
			//after 4 house idle
			if($check_datetime_now >= $check_datetime_closing_plus4){
				$is_range_do_generate = true;
			}
			
		}
		
		$status_process_text = 'on Progress';
		$generate_current_task = $this->input->post('generate_current_task');
		if(!empty($task_data[$generate_current_task])){
			
			//DO GENERATE
			switch($task_data[$generate_current_task]){
				case 'autoclosing_generate_sales': 
					$do_generate_closing_text = 'Generate Sales';
					$preparing_next_generate_text = 'Closing Sales';
					
					if($is_range_do_generate){
						
						$dt_return = array(
							'module'	=> 'audit_closing',
							'file'		=> 'closingSales',
							'action'	=> 'generate',
							'is_check'	=> 	0,
							'current_total'	=> 1,
							'closing_date'	=> $use_date,
							'auto_cancel'	=> ''
						);
						
					}else{
						$dt_return = array();
						$status_process_text = 'on Idle';
					}
					
					break;
				case 'autoclosing_closing_sales': 
					$do_generate_closing_text = 'Closing Sales';
					$preparing_next_generate_text = 'Generate Purchasing';
					
					if($is_on_closing_time){
						
						$dt_return = array(
							'module'	=> 'audit_closing',
							'file'		=> 'closingSales',
							'action'	=> 'closingDate',
							'is_check'	=> 	0,
							'current_total'	=> 1,
							'closing_date'	=> $use_date,
							'auto_cancel'	=> $get_opt['autoclosing_auto_cancel_billing']
						);
						
					}else{
						$dt_return = array();
						$status_process_text = 'on Idle';
					}
					
					break;
				
				case 'autoclosing_generate_purchasing': 
					$do_generate_closing_text = 'Generate Purchasing';
					$preparing_next_generate_text = 'Closing Purchasing';
					
					if($is_range_do_generate){
						
						$dt_return = array(
							'module'	=> 'audit_closing',
							'file'		=> 'closingPurchasing',
							'action'	=> 'generate',
							'is_check'	=> 	0,
							'current_total'	=> 1,
							'closing_date'	=> $use_date,
							'auto_cancel'	=> ''
						);
						
					}else{
						$dt_return = array();
						$status_process_text = 'on Idle';
					}
					
					break;
					
				case 'autoclosing_closing_purchasing': 
					$do_generate_closing_text = 'Closing Purchasing';
					$preparing_next_generate_text = 'Generate Inventory';
					
					if($is_on_closing_time){
						
						$dt_return = array(
							'module'	=> 'audit_closing',
							'file'		=> 'closingPurchasing',
							'action'	=> 'closingDate',
							'is_check'	=> 	0,
							'current_total'	=> 1,
							'closing_date'	=> $use_date,
							'auto_cancel'	=> $get_opt['autoclosing_auto_cancel_receiving']
						);
						
					}else{
						$dt_return = array();
						$status_process_text = 'on Idle';
					}
					
					break;
				
				case 'autoclosing_generate_inventory': 
					$do_generate_closing_text = 'Generate Inventory';
					$preparing_next_generate_text = 'Generate Stock List';
					
					if($is_range_do_generate){
						
						$dt_return = array(
							'module'	=> 'audit_closing',
							'file'		=> 'closingInventory',
							'action'	=> 'generate',
							'is_check'	=> 	0,
							'current_total'	=> 1,
							'closing_date'	=> $use_date,
							'auto_cancel'	=> ''
						);
						
					}else{
						$dt_return = array();
						$status_process_text = 'on Idle';
					}
					
					break;
				
				case 'autoclosing_generate_stock': 
					$do_generate_closing_text = 'Generate Stock List';
					$preparing_next_generate_text = 'Closing Inventory';
					
					if($is_range_do_generate){
						
						$dt_return = array(
							'module'	=> 'inventory',
							'file'		=> 'listStock',
							'action'	=> 'generate',
							'is_check'	=> 	0,
							'current_total'	=> 1,
							'closing_date'	=> $use_date,
							'auto_cancel'	=> ''
						);
						
					}else{
						$dt_return = array();
						$status_process_text = 'on Idle';
					}
					
					
					break;
				
				case 'autoclosing_closing_inventory': 
					$do_generate_closing_text = 'Closing Inventory';
					$preparing_next_generate_text = 'Generate Accounting';
					
					if($is_on_closing_time){
						
						$dt_return = array(
							'module'	=> 'audit_closing',
							'file'		=> 'closingPurchasing',
							'action'	=> 'closingDate',
							'is_check'	=> 	0,
							'current_total'	=> 1,
							'closing_date'	=> $use_date,
							'auto_cancel'	=> $get_opt['autoclosing_auto_cancel_distribution']
						);
						
					}else{
						$dt_return = array();
						$status_process_text = 'on Idle';
					}
					
					break;
				
				case 'autoclosing_generate_accounting': 
					$do_generate_closing_text = 'Generate Accounting';
					$preparing_next_generate_text = 'Closing Accounting';
					
					if($is_range_do_generate){
						
						$dt_return = array(
							'module'	=> 'audit_closing',
							'file'		=> 'closingAccounting',
							'action'	=> 'generate',
							'is_check'	=> 	0,
							'current_total'	=> 1,
							'closing_date'	=> $use_date,
							'auto_cancel'	=> ''
						);
						
					}else{
						$dt_return = array();
						$status_process_text = 'on Idle';
					}
					
					break;
				
				case 'autoclosing_closing_accounting': 
					$do_generate_closing_text = 'Closing Accounting';
					
					if($is_on_closing_time){
						
						$dt_return = array(
							'module'	=> 'audit_closing',
							'file'		=> 'closingAccounting',
							'action'	=> 'closingDate',
							'is_check'	=> 	0,
							'current_total'	=> 1,
							'closing_date'	=> $use_date,
							'auto_cancel'	=> $get_opt['autoclosing_skip_open_jurnal']
						);
						
					}else{
						$dt_return = array();
						$status_process_text = 'on Idle';
					}
					
					break;
				
				default: 
					$do_generate_closing_text = 'No Task';
			}
			
			
		}
		
		if(!empty($preparing_next_generate_text)){
			$preparing_next_generate_text = ', Next Task: '.$preparing_next_generate_text;
		}
		
		$r = array(
			'success' => true, 
			'info'	=> $do_generate_closing_text.' '.$status_process_text.$preparing_next_generate_text, 
			'dt_return' => $dt_return,
			'status' => $status_process_text
		);
		
		die(json_encode($r));
				
	}
}