<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class BackupTrx extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix');
		$this->prefix_pos = config_item('db_prefix2');
		$this->prefix_store = config_item('db_prefix3');
		$this->load->model('model_backuptrx', 'm');
	}

	public function storeInfo()
	{
		//GET STORE INFO
		$this->table = $this->prefix.'clients';
		
		//Delete
		//$this->db->where("id = 1");
		$q = $this->db->get($this->table);
		
		if($q->num_rows() > 0)  
        {  
			$dt = $q->row();
			$data_client = array(
				'client_code'  	=> 	$dt->client_code,
				'client_name'  	=> 	$dt->client_name,
				'client_email'	=>	$dt->client_email,
				'client_phone'	=>	$dt->client_phone,
				'client_address'=>	$dt->client_address
			);
			
        }else{
			$r = array('success' => false, 'info' => 'Store/Client Tidak teridentifikasi!');
			die(json_encode($r));
		}
		
		//OPT-OPTIONS
		$opt_val = array(
			'store_connected_id','management_systems','ipserver_management_systems','store_connected_code'
		);
		
		$get_opt = get_option_value($opt_val);
		if(empty($get_opt['store_connected_id'])){
			$get_opt['store_connected_id'] = 0;
		}
		if(empty($get_opt['store_connected_code'])){
			$get_opt['store_connected_code'] = 0;
		}
		if(empty($get_opt['management_systems'])){
			$r = array('success' => false, 'info' => 'Cek Setup Aplikasi, aktifkan pilihan Management System!');
			die(json_encode($r));
		}
		
		if(empty($get_opt['ipserver_management_systems'])){
			$r = array('success' => false, 'info' => 'Cek Setup Aplikasi, IP server belum disesuaikan');
			die(json_encode($r));
		}
		
		$store_connected_id = $get_opt['store_connected_id'];
		$management_systems = $get_opt['management_systems'];
		$ipserver_management_systems = $get_opt['ipserver_management_systems'];
		$store_connected_code = $get_opt['store_connected_code'];
		
		//CONNECTED TO STORE MANAGEMENT - CURL
		$this->load->library('curl');
		
		$is_connected = 0;
		$mktime_dc = strtotime(date("d-m-Y H:i:s"));
		
		if(!strstr("http://", $ipserver_management_systems)){
			$ipserver_management_systems = 'http://'.$ipserver_management_systems;
		}
		
		$client_url = $ipserver_management_systems.'/systems/masterStore/cekClient?_dc='.$mktime_dc;
		
		$post_data = array(
			'client_code' => $data_client['client_code']
		);
		
		$curl_ret = $this->curl->simple_post($client_url, $post_data);
		
		$data_client['client_ip'] = '-';
		$data_client['mysql_user'] = '-';
		$data_client['mysql_pass'] = '-';
		$data_client['mysql_port'] = '-';
		$data_client['mysql_database'] = '-';
		
		if(!empty($curl_ret)){
			
			if($curl_ret == 'Page Not Found!'){
				
			}else{
				$ret_data = json_decode($curl_ret, true);
			
				if(!empty($ret_data['data']) AND $ret_data['success'] == true){
					$store_connected_id = $ret_data['data']['id'];
					$store_connected_code = $ret_data['data']['client_code'];
					$data_client['client_name'] = $ret_data['data']['client_name'];
					$data_client['client_email'] = $ret_data['data']['client_email'];
					$data_client['client_phone'] = $ret_data['data']['client_phone'];
					$data_client['client_address'] = $ret_data['data']['client_address'];
					$data_client['client_ip'] = $ret_data['data']['client_ip'];
					$data_client['mysql_port'] = $ret_data['data']['mysql_port'];
					$data_client['mysql_database'] = $ret_data['data']['mysql_database'];
					$is_connected = 1;
				}
				
			}
			
			
		}else{
			$r = array('success' => false, 'info' => 'Data Store/Client Tidak teridentifikasi di Server!');
			die(json_encode($r));
		}
		
		if($store_connected_id != $get_opt['store_connected_id']){
			$get_opt['store_connected_id'] = $store_connected_id;
			//update options
			$update_option = update_option($get_opt);
		}
		
		
		$store_connected_id_show = '-';
		if(!empty($store_connected_id)){
			$store_connected_id_show = $store_connected_id;
		}
		
		$data_detail = array(
			'Connected ID' 	=> '<font style="font-weight:bold; color:green;">'.$store_connected_id_show.'</font>',
			'Code' 		=> '<font style="font-weight:bold; color:blue;">'.$data_client['client_code'].'</font>',
			'Store'		=> $data_client['client_name'], 
			'Email'		=> $data_client['client_email'],
			'Phone'		=> $data_client['client_phone'],
			'Address'	=> $data_client['client_address'],
			'&nbsp;'	=> '',
			'Mysql IP'		=> '<i>'.$data_client['client_ip'].'</i>',
			'Mysql Port'	=> '<i>'.$data_client['mysql_port'].'</i>',
			'Mysql User'	=> '<i>****</i>',
			'Mysql Pass'	=> '<i>****</i>',
			'Database'		=> '<i>'.$data_client['mysql_database'].'</i>',
			'&nbsp;&nbsp;'	=> '',
			'DB Status'	=> '',
			'Keterangan'	=> 'Curl via Store',
		);
		
		//SAVE STORE_CONNECTED_ID
		$data_detail['DB Status'] = '<font style="font-weight:bold; color:red;">Not Connected!</font>';
		if($is_connected == 1){
			$data_detail['DB Status'] = '<font style="font-weight:bold; color:blue;">Connected!</font>';
		}
		
		$no = 0;
		foreach($data_detail as $ket => $val){
			$no++;
			$all_data_detail[] = array(
				'id'			=> $no,
				'no'			=> $no,
				'list_info'		=> $ket,
				'store_info'	=> $val
			);
		}
		
		
		$r = array(
			'success' => true, 
			'info' => 'Store/Client: '.$store_connected_id, 
			'store_connected_id' => $store_connected_id, 
			'store_connected_code' => $store_connected_code, 
			'is_connected' => $is_connected, 
			'data' => $all_data_detail, 
			'totalCount' => count($all_data_detail)
		);
		
		
		die(json_encode($r));
	}
	
	public function backupDetail()
	{
		
		$client_id = $this->input->post('client_id');
		$only_backup = $this->input->post('only_backup');
		
		if(empty($client_id)){
			$r = array('success' => false, 'info' => 'Store Tidak Teridentifikasi!');
			die(json_encode($r));
		}
		
		//OPT-OPTIONS
		$opt_val = array(
			'store_connected_id','management_systems','ipserver_management_systems'
		);
		
		$get_opt = get_option_value($opt_val);
		if(empty($get_opt['store_connected_id'])){
			$get_opt['store_connected_id'] = 0;
		}
		if(empty($get_opt['management_systems'])){
			$r = array('success' => false, 'info' => 'Cek Setup Aplikasi, aktifkan pilihan Management System!');
			die(json_encode($r));
		}
		
		if(empty($get_opt['ipserver_management_systems'])){
			$r = array('success' => false, 'info' => 'Cek Setup Aplikasi, IP server belum disesuaikan');
			die(json_encode($r));
		}
		
		$store_connected_id = $get_opt['store_connected_id'];
		$management_systems = $get_opt['management_systems'];
		$ipserver_management_systems = $get_opt['ipserver_management_systems'];
		
		//CONNECTED TO STORE MANAGEMENT - CURL
		$this->load->library('curl');
		
		$mktime_dc = strtotime(date("d-m-Y H:i:s"));
		
		if(!strstr("http://", $ipserver_management_systems)){
			$ipserver_management_systems = 'http://'.$ipserver_management_systems;
		}
		
		$client_url = $ipserver_management_systems.'/sync_backup/backupTrx/backupDetail?_dc='.$mktime_dc;
		
		$post_data = array(
			'client_id' => $client_id,
			'limit' => 9999,
			'page' 	=> 1,
			'start' => 0,
			'akses'=> 'CURL'
		);
		
		$curl_ret = $this->curl->simple_post($client_url, $post_data);
		$return_data = json_decode($curl_ret, true);
		
		$available_conn = false;
		if(empty($return_data)){
			$r = array('success' => false, 'info' => 'Cek Data Failed!');
			die(json_encode($r));
		}else{
			$available_conn = true;
		}
		
		$backup_data_allowed = array();
		$backup_data_text = array();
		$total_data_server = array();
		$last_id_server = array();
		$last_update = array();
		
		if(!empty($return_data['backup_data_allowed'])){
			$backup_data_allowed = $return_data['backup_data_allowed'];
		}
		if(!empty($return_data['backup_data_text'])){
			$backup_data_text = $return_data['backup_data_text'];
		}
		if(!empty($return_data['total_data_server'])){
			$total_data_server = $return_data['total_data_server'];
		}
		if(!empty($return_data['last_id_server'])){
			$last_id_server = $return_data['last_id_server'];
		}
		if(!empty($return_data['last_update'])){
			$last_update = $return_data['last_update'];
		}
		
		//TRX - PURCHASING
		//TRX - FINANCE
		//TRX - AP
		//TRX - AR
		//TRX - INVENTORY
		//TRX - DISTRIBUSI
		//TRX - PRODUCTION
		//TRX - MEMBER
		
		$total_data_lokal = array(
			'sales' => 0,
			'purchasing' => 0,
			'finance' => 0,
			'ar' => 0,
			'ap' => 0,
			'inventory' => 0,
			'distribution' => 0,
			'production' => 0,
			'member_point' => 0
		);
		
		$last_id_lokal = array(
			'sales' => 0,
			'purchasing' => 0,
			'finance' => 0,
			'ar' => 0,
			'ap' => 0,
			'inventory' => 0,
			'distribution' => 0,
			'production' => 0,
			'member_point' => 0
		);
		
		
		//LOAD LOKAL
		$get_sales = $this->db->query("SELECT id FROM ".$this->prefix_pos."billing ORDER BY id DESC");
		if($get_sales->num_rows() > 0){
			$dt_sales = $get_sales->row();
			$last_id_lokal["sales"] = $dt_sales->id;
			$total_data_lokal["sales"] = $get_sales->num_rows();
		}
		
		$backup_data_allowed_in = implode("','", $backup_data_allowed);
		
		$backup_data_store = array();
		$backup_data_store_available = array();
		
		$no = 0;
		foreach($backup_data_allowed as $val){
			$no++;
			
			$updated_total = 0;
			$updated_last_id = 0;
			
			$total_data_store = $total_data_lokal[$val];
			$last_id_store = $last_id_lokal[$val];
			
			$total_data_srv = $total_data_server[$val];
			$last_id_srv = $last_id_server[$val];
			
			if($total_data_lokal[$val] == $total_data_server[$val]){
				$total_data_lokal[$val] = '<font style="font-weight:bold; color:green;">'.$total_data_lokal[$val].'</font>';
				$total_data_server[$val] = '<font style="font-weight:bold; color:green;">'.$total_data_server[$val].'</font>';
				$updated_total = 1;
				
			}else{
				$total_data_lokal[$val] = '<font style="font-weight:bold; color:red;">'.$total_data_lokal[$val].'</font>';
				$total_data_server[$val] = '<font style="font-weight:bold; color:red;">'.$total_data_server[$val].'</font>';
			}
			
			if($last_id_lokal[$val] == $last_id_server[$val]){
				$last_id_lokal[$val] = '<font style="font-weight:bold; color:green;">#'.$last_id_lokal[$val].'</font>';
				$last_id_server[$val] = '<font style="font-weight:bold; color:green;">#'.$last_id_server[$val].'</font>';
				$updated_last_id = 1;
				
			}else{
				$last_id_lokal[$val] = '<font style="font-weight:bold; color:red;">#'.$last_id_lokal[$val].'</font>';
				$last_id_server[$val] = '<font style="font-weight:bold; color:red;">#'.$last_id_server[$val].'</font>';
			}
			
			$backup_status_text = '<font style="font-weight:bold; color:red;">Backup Now</font>';
			$backup_status = 'update now';
			if($updated_total == 1 AND $updated_last_id == 1){
				$backup_status_text = '<font style="font-weight:bold; color:green;">Updated</font>';
				$backup_status = 'updated';
			}
			
			//echo '<pre>';
			//print_r($return_data);
			//die();
			
			$last_update_text = '-';
			if(!empty($last_update[$val])){
				$last_update_text = $last_update[$val]['last_update'];
			}
			
			if($total_data_store == 0 AND $last_id_store == 0){
				$last_id_lokal[$val] = '-';
				$last_id_server[$val] = '-';
				$total_data_lokal[$val] = '-';
				$total_data_server[$val] = '-';
				$backup_status_text = '<font style="font-weight:bold; color:red;">N/A</font>';
			}
			
			$allow_backup_list = false;
			if(!empty($only_backup)){
				if($val == $only_backup){
					$allow_backup_list = true;
				}
				
			}else{
				$allow_backup_list = true;
			}
			
			if($allow_backup_list == true){
				$backup_data_store[$val] = array(
					'id'			=> $no,
					'client_id'		=> $client_id,
					'backup_data'		=> $val,
					'backup_data_text'	=> $backup_data_text[$val],
					'total_data_lokal'	=> $total_data_lokal[$val],
					'total_data_server'	=> $total_data_server[$val],
					'last_id_lokal'		=> $last_id_lokal[$val],
					'last_id_server'	=> $last_id_server[$val],
					'last_update'		=> $last_update_text,
					'backup_status'		=> $backup_status,
					'backup_status_text'=> $backup_status_text,
					'total_data_store'	=> $total_data_store,
					'total_data_on_backup'	=> $total_data_srv,
					'last_id_store'		=> $last_id_store,
					'last_id_on_backup'	=> $last_id_srv
				);
			}
			
		}
		
		
		if(!empty($backup_data_store)){
			$backup_data_store_new = array();
			foreach($backup_data_store as $dt){
				$backup_data_store_new[] = $dt;
			}
			
			$backup_data_store = $backup_data_store_new;
		}
		
		$info = '';
		if($available_conn == false){
			$backup_data_store = array();
			$info = 'Connection Failed!';
		}
		
		$get_data = array(
			'success' => $available_conn, 
			'info' => $info, 
			'client_id' => $client_id, 
			'data' => $backup_data_store, 
			'totalCount' => count($backup_data_store), 
			//'available_conn' => $available_conn, 
			//'backup_data_allowed' => $backup_data_allowed, 
			//'backup_data_text' 	=> $backup_data_text, 
			//'total_data_server' => $total_data_server, 
			//'last_id_server' 	=> $last_id_server, 
			//'total_data_lokal' 	=> $total_data_lokal, 
			//'last_id_lokal' 	=> $last_id_lokal
		);
		
		die(json_encode($get_data));
		
	}
	
	public function generate()
	{
		$limit_backup_data = 100;
		$this->table_client = $this->prefix.'clients';
		$this->table_backup = $this->prefix_store.'backup';
		
		$client_id = $this->input->post('client_id');
		$backup_type = $this->input->post('backup_type');
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'User Session Expired, Please Re-Login!');
			die(json_encode($r));
		}
				
		if(empty($client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}	
		
		if(empty($backup_type)){
			$backup_type = 'client';
		}
		
		$total_data = $this->input->post('total_data');
		$current_total = $this->input->post('current_total');
		$last_id_on_backup = $this->input->post('last_id_on_backup');
		$total_data_on_backup = $this->input->post('total_data_on_backup');
		
		$backup_data = $this->input->post('backup_data');
		$backup_data = json_decode($backup_data, true);
		
		
		$backup_data_allowed = array(
			1 => 'sales',
			2 => 'purchasing',
			3 => 'finance',
			4 => 'ar',
			5 => 'ap',
			6 => 'inventory',
			7 => 'distribution',
			8 => 'production',
			9 => 'member_point'
		);
		
		$total_data = count($backup_data);
		
		
		//CEK FIRST TO START DATE
		$curr_backup_data = '';
		$i = 0;
		foreach($backup_data as $key => $dtT){
			$i++;
			
			if($i == $current_total){
				
				if(!empty($backup_data_allowed[$dtT])){
					$curr_backup_data = $backup_data_allowed[$dtT];
				}
				
			}
		}
		
		
		if(empty($curr_backup_data)){
			$r = array('success' => false, 'info'	=> 'Invalid Backup Data!');
			die(json_encode($r));
		}
		
		$backup_status = false;
		$has_next = 0;
		
		//OPT-OPTIONS
		$opt_val = array(
			'store_connected_id','management_systems','ipserver_management_systems'
		);
		
		$get_opt = get_option_value($opt_val);
		if(empty($get_opt['store_connected_id'])){
			$get_opt['store_connected_id'] = 0;
		}
		if(empty($get_opt['management_systems'])){
			$r = array('success' => false, 'info' => 'Cek Setup Aplikasi, aktifkan pilihan Management System!');
			die(json_encode($r));
		}
		
		if(empty($get_opt['ipserver_management_systems'])){
			$r = array('success' => false, 'info' => 'Cek Setup Aplikasi, IP server belum disesuaikan');
			die(json_encode($r));
		}
		
		$store_connected_id = $get_opt['store_connected_id'];
		$management_systems = $get_opt['management_systems'];
		$ipserver_management_systems = $get_opt['ipserver_management_systems'];
		
		//CONNECTED TO STORE MANAGEMENT - CURL
		$this->load->library('curl');
		
		$mktime_dc = strtotime(date("d-m-Y H:i:s"));
		
		if(!strstr("http://", $ipserver_management_systems)){
			$ipserver_management_systems = 'http://'.$ipserver_management_systems;
		}
		
		
		$backup_text = '';
		switch($curr_backup_data){
			case 'sales':
				$backup_text = 'Sales';
				
				//BILLING ON STORE
				$last_id_billing_store = 0;
				$get_all_store_billing = $this->db->query("SELECT id FROM ".$this->prefix_pos."billing ORDER BY id DESC LIMIT 1");
				if($get_all_store_billing->num_rows() > 0){
					$dt_all_billing_store = $get_all_store_billing->row();
					$last_id_billing_store = $dt_all_billing_store->id;
				}
				
				$total_data_store = 0;
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_billing_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: '.$backup_text.' Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//BILLING ON STORE
				$data_billing_store = array();
				$all_billing_id = array();
				$get_store_billing = $this->db->query("SELECT * FROM ".$this->prefix_pos."billing WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_billing->num_rows() > 0){
					
					foreach($get_store_billing->result() as $dt){
						
						$data_billing_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_billing_id)){
							$all_billing_id[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
					
					//$total_data_store = $get_store_billing->num_rows();
				}
				
				$get_store_billing2 = $this->db->query("SELECT id FROM ".$this->prefix_pos."billing ORDER BY id DESC");
				if($get_store_billing2->num_rows() > 0){
					$total_data_store = $get_store_billing2->num_rows();
					$dt_billing = $get_store_billing2->row();
					//$last_id_store = $dt_billing->id;
				}
				
				if($last_id_billing_store > $last_id_store){
					$has_next = 1;
				}
				
				//BILLING DETAIL ON STORE
				$data_billing_detail_store = array();
				if(!empty($all_billing_id)){
					$all_billing_id_sql = implode(",", $all_billing_id);
					
					$get_store_billing_detail = $this->db->query("SELECT * FROM ".$this->prefix_pos."billing_detail WHERE billing_id IN (".$all_billing_id_sql.") ORDER BY id ASC");
					if($get_store_billing_detail->num_rows() > 0){
						
						foreach($get_store_billing_detail->result() as $dt){
							
							$data_billing_detail_store[] = (array) $dt;
							
							//$last_id_store_detail = $dt->id;
						}
						
						//$total_data_store_detail = $get_store_billing->num_rows();
					}
					
				}
				
				//DATA BACKUP
				$client_url = $ipserver_management_systems.'/sync_backup/backupTrx/generate?_dc='.$mktime_dc;
		
				$post_data = array(
					'client_id' => $client_id,
					'curr_backup_data' => $curr_backup_data,
					'backup_type' => $backup_type,
					'current_total' => $current_total,
					'backup_data' => 'sales',
					'akses'=> 'CURL',
					'last_id_billing_store' => $last_id_billing_store,
					//'last_id_billing_detail_store' => $last_id_billing_detail_store,
					'total_data_store' => $total_data_store,
					'last_id_store' => $last_id_store,
					'data_billing_store' => json_encode($data_billing_store),
					'data_billing_detail_store' => json_encode($data_billing_detail_store),
					'all_billing_id' => json_encode($all_billing_id),
					'last_id_on_backup' => $last_id_on_backup,
					'total_data_on_backup' => $total_data_on_backup,
					'limit_backup_data' => $limit_backup_data
				);
				
				$curl_ret = $this->curl->simple_post($client_url, $post_data);
				$return_data = json_decode($curl_ret, true);
				
				$backup_status = false;
				if(empty($return_data)){
					$r = array('success' => false, 'info' => 'Backup Data: '.$backup_text.' Failed!', 'has_next' => 0);
					die(json_encode($r));
				}else{
					$backup_status = true;
				}
				
				if(!empty($return_data['last_id_on_backup'])){
					$last_id_on_backup = $return_data['last_id_on_backup'];
				}
				
				if(!empty($return_data['total_data_on_backup'])){
					$total_data_on_backup = $return_data['total_data_on_backup'];
				}
				
				//$r = $return_data;
				//die(json_encode($r));
				
				break;
			case 'purchasing':
				$backup_text = 'Purchasing';
				break;
			case 'finance':
				$backup_text = 'Finance/Cashflow';
				break;
			case 'ar':
				$backup_text = 'Account Receivable';
				break;
			case 'ap':
				$backup_text = 'Account Payable';
				break;
			case 'inventory':
				$backup_text = 'Inventory';
				break;
			case 'distribution':
				$backup_text = 'Distribution';
				break;
			case 'production':
				$backup_text = 'Production';
				break;
			case 'member_point':
				$backup_text = 'Member Point';
				break;
			
		}
	
		
		if($backup_status){
			
			$r = array(
				'success' => true, 
				'info'	=> 'Backup '.$total_data_store.' Data - #'.$last_id_store.'..', 
				'has_next' => $has_next,
				'last_id_on_backup' => $last_id_on_backup,
				'total_data_on_backup' => $total_data_on_backup,
				'total_data_store' => $total_data_store,
				'last_id_store' => $last_id_store
			);
			die(json_encode($r));
  
		}else{
			
			$r = array(
				'success' => true, 
				'info'	=> 'Backup Data: '.$backup_text.' - Updated..',
				'has_next' => 0,
				'last_id_on_backup' => $last_id_on_backup,
				'total_data_on_backup' => $total_data_on_backup,
				'total_data_store' => $total_data_store,
				'last_id_store' => $last_id_store
			);
			die(json_encode($r));
			
		}
		
				
	}
	
	public function backupTrxLog()
	{
		//OPT-OPTIONS
		$opt_val = array(
			'store_connected_id','management_systems','ipserver_management_systems'
		);
		
		$get_opt = get_option_value($opt_val);
		if(empty($get_opt['store_connected_id'])){
			$get_opt['store_connected_id'] = 0;
		}
		if(empty($get_opt['management_systems'])){
			$r = array('success' => false, 'info' => 'Cek Setup Aplikasi, aktifkan pilihan Management System!');
			die(json_encode($r));
		}
		
		if(empty($get_opt['ipserver_management_systems'])){
			$r = array('success' => false, 'info' => 'Cek Setup Aplikasi, IP server belum disesuaikan');
			die(json_encode($r));
		}
		
		$store_connected_id = $get_opt['store_connected_id'];
		$management_systems = $get_opt['management_systems'];
		$ipserver_management_systems = $get_opt['ipserver_management_systems'];
		
		//CONNECTED TO STORE MANAGEMENT - CURL
		$this->load->library('curl');
		
		$mktime_dc = strtotime(date("d-m-Y H:i:s"));
		
		if(!strstr("http://", $ipserver_management_systems)){
			$ipserver_management_systems = 'http://'.$ipserver_management_systems;
		}
		$client_url = $ipserver_management_systems.'/sync_backup/backupTrx/backupTrxLog?_dc='.$mktime_dc;
		
		$client_id = $this->input->post('client_id');
		$limit = $this->input->post('limit');
		$page = $this->input->post('page');
		$start = $this->input->post('start');
		
		$post_data = array(
			'client_id' => $client_id,
			'limit' => $limit,
			'page' => $page,
			'start' => $start
		);
		
		$curl_ret = $this->curl->simple_post($client_url, $post_data);
		$return_data = json_decode($curl_ret, true);
		  	
  		$get_data = array('data' => array(), 'totalCount' => 0);
		
		if(!empty($return_data['data'])){
			
			/*foreach ($return_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;font-weight:bold;">Active</span>':'<span style="color:red;font-weight:bold;">Inactive</span>';
				$s['backup_data_text'] = '<span style="color:green;font-weight:bold;">'.ucwords(str_replace("_"," ",$s['backup_data'])).'</span>';
				$s['auto_manual_text'] = ($s['auto_manual'] == '1') ? '<span style="color:blue;font-weight:bold;">Manual</span>':'<span style="color:orange;font-weight:bold;">Auto</span>';
				$s['backup_type_text'] = ($s['backup_type'] == 'server') ? '<span style="color:orange;font-weight:bold;">Server</span>':'<span style="color:blue;font-weight:bold;">Client</span>';
				
				$s['total_data'] = '<span style="color:green;font-weight:bold;">'.$s['total_data'].'</span>';
				$s['last_id'] = '<span style="color:green;font-weight:bold;">#'.$s['last_id'].'</span>';
				
				$s['backup_date'] = date("d-m-Y H:i:s", strtotime($s['created']));
				array_push($newData, $s);
			}*/
			$get_data['data'] = $return_data['data'];
			$get_data['totalCount'] = $return_data['totalCount'];
		}
		
		
      	die(json_encode($get_data));
	}
}