<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class SyncData extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix');
		$this->prefix_pos = config_item('db_prefix2');
		$this->prefix_store = config_item('db_prefix3');
		$this->load->model('model_syncdata', 'm');
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
	
	public function syncDetail()
	{
		$client_id = $this->input->post('client_id');
		
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
		
		$client_url = $ipserver_management_systems.'/sync_backup/syncData/syncDetail?_dc='.$mktime_dc;
		
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
		
		$sync_data_allowed = array();
		$sync_data_text = array();
		$total_data_server = array();
		$last_id_server = array();
		$last_update = array();
		
		if(!empty($return_data['sync_data_allowed'])){
			$sync_data_allowed = $return_data['sync_data_allowed'];
		}
		if(!empty($return_data['sync_data_text'])){
			$sync_data_text = $return_data['sync_data_text'];
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
		
		$total_data_lokal = array(
			'modules' => 0,
			'roles' => 0,
			'data_user' => 0,
			'menu_category' => 0,
			'menu' => 0,
			'payment_bank' => 0,
			'discount' => 0,
			'supervisor_access' => 0,
			'sales_marketing' => 0,
			'customer_member' => 0,
			'warehouse_access' => 0
		);
		
		$last_id_lokal = array(
			'modules' => 0,
			'roles' => 0,
			'data_user' => 0,
			'menu_category' => 0,
			'menu' => 0,
			'payment_bank' => 0,
			'discount' => 0,
			'supervisor_access' => 0,
			'sales_marketing' => 0,
			'customer_member' => 0,
			'warehouse_access' => 0
		);
		
		//LOAD LOKAL
		$get_store_modules = $this->db->query("SELECT id FROM ".$this->prefix."modules ORDER BY id DESC");
		if($get_store_modules->num_rows() > 0){
			$dt_store_modules = $get_store_modules->row();
			$last_id_lokal["modules"] = $dt_store_modules->id;
			$total_data_lokal["modules"] = $get_store_modules->num_rows();
		}
		
		$get_store_roles = $this->db->query("SELECT id FROM ".$this->prefix."roles ORDER BY id DESC");
		if($get_store_roles->num_rows() > 0){
			$dt_store_roles = $get_store_roles->row();
			$last_id_lokal["roles"] = $dt_store_roles->id;
			$total_data_lokal["roles"] = $get_store_roles->num_rows();
		}
		
		$get_store_data_user = $this->db->query("SELECT id FROM ".$this->prefix."users ORDER BY id DESC");
		if($get_store_data_user->num_rows() > 0){
			$dt_store_data_user = $get_store_data_user->row();
			$last_id_lokal["data_user"] = $dt_store_data_user->id;
			$total_data_lokal["data_user"] = $get_store_data_user->num_rows();
		}
		
		$get_store_product_category = $this->db->query("SELECT id FROM ".$this->prefix_pos."product_category WHERE is_active = 1 ORDER BY id DESC");
		if($get_store_product_category->num_rows() > 0){
			$dt_store_product_category = $get_store_product_category->row();
			$last_id_lokal["menu_category"] = $dt_store_product_category->id;
			$total_data_lokal["menu_category"] = $get_store_product_category->num_rows();
		}
		
		$get_store_product = $this->db->query("SELECT id FROM ".$this->prefix_pos."product WHERE is_active = 1 ORDER BY id DESC");
		if($get_store_product->num_rows() > 0){
			$dt_store_product = $get_store_product->row();
			$last_id_lokal["menu"] = $dt_store_product->id;
			$total_data_lokal["menu"] = $get_store_product->num_rows();
		}
		
		$get_store_bank = $this->db->query("SELECT id FROM ".$this->prefix_pos."bank ORDER BY id DESC");
		if($get_store_bank->num_rows() > 0){
			$dt_store_bank = $get_store_bank->row();
			$last_id_lokal["payment_bank"] = $dt_store_bank->id;
			$total_data_lokal["payment_bank"] = $get_store_bank->num_rows();
		}
		
		$get_store_discount = $this->db->query("SELECT id FROM ".$this->prefix_pos."discount WHERE is_active = 1 ORDER BY id DESC");
		if($get_store_discount->num_rows() > 0){
			$dt_store_discount = $get_store_discount->row();
			$last_id_lokal["discount"] = $dt_store_discount->id;
			$total_data_lokal["discount"] = $get_store_discount->num_rows();
		}

		$get_spv_access = $this->db->query("SELECT id FROM ".$this->prefix."supervisor_access ORDER BY id DESC");
		if($get_spv_access->num_rows() > 0){
			$dt_spv_access = $get_spv_access->row();
			$last_id_lokal["supervisor_access"] = $dt_spv_access->id;
			$total_data_lokal["supervisor_access"] = $get_spv_access->num_rows();
		}
		
		$get_sales = $this->db->query("SELECT id FROM ".$this->prefix_pos."sales ORDER BY id DESC");
		if($get_sales->num_rows() > 0){
			$dt_sales = $get_sales->row();
			$last_id_lokal["sales_marketing"] = $dt_sales->id;
			$total_data_lokal["sales_marketing"] = $get_sales->num_rows();
		}
		
		$get_customer = $this->db->query("SELECT id FROM ".$this->prefix_pos."customer ORDER BY id DESC");
		if($get_customer->num_rows() > 0){
			$dt_customer = $get_customer->row();
			$last_id_lokal["customer_member"] = $dt_customer->id;
			$total_data_lokal["customer_member"] = $get_customer->num_rows();
		}
		
		$get_warehouse_access = $this->db->query("SELECT id FROM ".$this->prefix_pos."storehouse_users ORDER BY id DESC");
		if($get_warehouse_access->num_rows() > 0){
			$dt_warehouse_access = $get_warehouse_access->row();
			$last_id_lokal["warehouse_access"] = $dt_warehouse_access->id;
			$total_data_lokal["warehouse_access"] = $get_warehouse_access->num_rows();
		}
		
		$sync_data_allowed_in = implode("','", $sync_data_allowed);
		
		$sync_data_store = array();
		$sync_data_store_available = array();
		
		$no = 0;
		foreach($sync_data_allowed as $val){
			$no++;
			
			$updated_total = 0;
			$updated_last_id = 0;
			
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
			
			$sync_status_text = '<font style="font-weight:bold; color:red;">Update Now</font>';
			$sync_status = 'update now';
			if($updated_total == 1 AND $updated_last_id){
				$sync_status_text = '<font style="font-weight:bold; color:green;">Updated</font>';
				$sync_status = 'updated';
			}
			
			$last_update_text = '-';
			if(!empty($last_update[$val])){
				$last_update_text = $last_update[$val]['last_update'];
			}
			
			$sync_data_store[$val] = array(
				'id'			=> $no,
				'client_id'		=> $client_id,
				'sync_data'		=> $val,
				'sync_data_text'	=> $sync_data_text[$val],
				'total_data_lokal'	=> $total_data_lokal[$val],
				'total_data_server'	=> $total_data_server[$val],
				'last_id_lokal'		=> $last_id_lokal[$val],
				'last_id_server'	=> $last_id_server[$val],
				'last_update'		=> $last_update_text,
				'sync_status'		=> $sync_status,
				'sync_status_text'	=> $sync_status_text
			);
		}
		
		if(!empty($sync_data_store)){
			$sync_data_store_new = array();
			foreach($sync_data_store as $dt){
				$sync_data_store_new[] = $dt;
			}
			
			$sync_data_store = $sync_data_store_new;
		}
		
		$info = '';
		if($available_conn == false){
			$sync_data_store = array();
			$info = 'Connection Failed!';
		}
		
		$get_data = array(
			'success' => $available_conn, 
			'info' => $info, 
			'client_id' => $client_id, 
			'data' => $sync_data_store, 
			'totalCount' => count($sync_data_store), 
			'available_conn' => $available_conn, 
			'sync_data_allowed' => $sync_data_allowed, 
			'sync_data_text' 	=> $sync_data_text, 
			'total_data_server' => $total_data_server, 
			'last_id_server' 	=> $last_id_server, 
			'total_data_lokal' 	=> $total_data_lokal, 
			'last_id_lokal' 	=> $last_id_lokal
		);
		die(json_encode($get_data));
		
	}
	
	public function generate()
	{
		$this->table_client = $this->prefix.'clients';
		$this->table_sync = $this->prefix_store.'sync';
		
		$client_id = $this->input->post('client_id');
		$sync_type = $this->input->post('sync_type');
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'User Session Expired, Please Re-Login!');
			die(json_encode($r));
		}
				
		if(empty($client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}	
		
		$sync_type = 'client';
		
		$total_data = $this->input->post('total_data');
		$current_total = $this->input->post('current_total');
		$sync_data = $this->input->post('sync_data');
		$sync_data = json_decode($sync_data, true);
		
		$sync_data_allowed = array(
			1 => 'modules',
			2 => 'roles',
			3 => 'data_user',
			4 => 'menu_category',
			5 => 'menu',
			6 => 'payment_bank',
			7 => 'discount',
			8 => 'supervisor_access',
			9 => 'sales_marketing',
			10 => 'customer_member',
			11 => 'warehouse_access'
		);
		
		$total_data = count($sync_data);
		
		
		//CEK FIRST TO START DATE
		$curr_sync_data = '';
		$i = 0;
		foreach($sync_data as $dtT){
			$i++;
			
			if($i == $current_total){
				
				if(!empty($sync_data_allowed[$dtT])){
					$curr_sync_data = $sync_data_allowed[$dtT];
				}
				
			}
		}
		
		if(empty($curr_sync_data)){
			$r = array('success' => false, 'info'	=> 'Invalid Sync Data!');
			die(json_encode($r));
		}
		
		$sync_status = false;
		
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
		
		$client_url = $ipserver_management_systems.'/sync_backup/syncData/generate?_dc='.$mktime_dc;
		
		$post_data = array(
			'client_id' => $client_id,
			'curr_sync_data' => $curr_sync_data,
			'sync_type' => $sync_type,
			'current_total' => $current_total,
			'sync_data' 	=> json_encode($sync_data)
		);
		
		$curl_ret = $this->curl->simple_post($client_url, $post_data);
		$return_data = json_decode($curl_ret, true);
		
		$get_last_id_sync = $return_data['last_id_sync'];
		$get_total_data_sync = $return_data['total_data_sync'];
		$new_data_store = $return_data['new_data_store'];
		
		if(empty($new_data_store)){
			$r = array('success' => false, 'info'	=> 'Connection Failed!');
			die(json_encode($r));
		}
		
		//SYNC DB
		$total_data_sync = 0;
		$last_id_sync = 0;
		$sync_text = '';
		switch($curr_sync_data){
			case 'modules':
				$sync_text = 'Modules';
				
				//MODULES
				//apps_pos_modules
				$last_id_sync = $get_last_id_sync['modules'];
				$total_data_sync = $get_total_data_sync['modules'];
				
				if(!empty($new_data_store['modules'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix."modules");
					
					//BATCH
					$this->db->insert_batch($this->prefix."modules", $new_data_store['modules']);

					$sync_status = true;
					
				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix."modules");
						$sync_status = true;
					}
				}
				
				
				//apps_pos_modules_method
				if(!empty($new_data_store['modules_method'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix."modules_method");
					
					//BATCH
					$this->db->insert_batch($this->prefix."modules_method", $new_data_store['modules_method']);

				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix."modules_method");
					}
				}
				
				
				//apps_pos_modules_preload
				if(!empty($new_data_store['modules_preload'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix."modules_preload");
					
					//BATCH
					$this->db->insert_batch($this->prefix."modules_preload", $new_data_store['modules_preload']);

				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix."modules_preload");
					}
				}
				
				break;
			case 'roles':
				$sync_text = 'Roles';
				
				//MODULES
				//apps_pos_roles
				$last_id_sync = $get_last_id_sync['roles'];
				$total_data_sync = $get_total_data_sync['roles'];
				
				if(!empty($new_data_store['roles'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix."roles");
					
					//BATCH
					$this->db->insert_batch($this->prefix."roles", $new_data_store['roles']);

					$sync_status = true;
					
				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix."roles");
						$sync_status = true;
					}
				}
				
				
				//apps_pos_roles_module
				if(!empty($new_data_store['roles_module'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix."roles_module");
					
					//BATCH
					$this->db->insert_batch($this->prefix."roles_module", $new_data_store['roles_module']);

				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix."roles_module");
					}
				}
				
				
				//apps_pos_roles_widget
				if(!empty($new_data_store['roles_widget'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix."roles_widget");
					
					//BATCH
					$this->db->insert_batch($this->prefix."roles_widget", $new_data_store['roles_widget']);

				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix."roles_widget");
					}
				}
				
				break;
			case 'data_user':
				$sync_text = 'Data User';
				
				//MODULES
				//apps_pos_users
				$last_id_sync = $get_last_id_sync['data_user'];
				$total_data_sync = $get_total_data_sync['data_user'];
				
				$get_all_user_id = array();
				if(!empty($new_data_store['users'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix."users");
					
					//BATCH
					$this->db->insert_batch($this->prefix."users", $new_data_store['users']);
					
					foreach($new_data_store['users'] as $dt){
						if(!in_array($dt['id'], $get_all_user_id)){
							$get_all_user_id[] = $dt['id'];
						}
					}

					$sync_status = true;
					
				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix."users");
						$this->db->query("TRUNCATE ".$this->prefix."users_quickstart");
						$this->db->query("TRUNCATE ".$this->prefix."users_shortcut");
						$sync_status = true;
					}
				}
				
				
				//apps_pos_users_desktop
				if(!empty($new_data_store['users_desktop'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix."users_desktop");
					
					//BATCH
					$this->db->insert_batch($this->prefix."users_desktop", $new_data_store['users_desktop']);

				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix."users_desktop");
					}
				}
				
				
				//apps_pos_users_quickstart
				if(!empty($new_data_store['users_quickstart'])){
					//TRUNCATE STORE
					//$this->db->query("TRUNCATE ".$this->prefix."users_quickstart");
					
					//BATCH
					//$this->db->insert_batch($this->prefix."users_quickstart", $new_data_store['users_quickstart']);

				}
				
				//apps_pos_users_shortcut
				if(!empty($new_data_store['users_shortcut'])){
					//TRUNCATE STORE
					//$this->db->query("TRUNCATE ".$this->prefix."users_shortcut");
					
					//BATCH
					//$this->db->insert_batch($this->prefix."users_shortcut", $new_data_store['users_shortcut']);

				}
				
				if(!empty($get_all_user_id)){
					$get_all_user_id_sql = implode(",", $get_all_user_id);
					$this->db->delete($this->prefix."users_quickstart", "user_id IN (".$get_all_user_id_sql.")");
					$this->db->delete($this->prefix."users_shortcut", "user_id IN (".$get_all_user_id_sql.")");
				}
				
				break;
			case 'menu_category':
				$sync_text = 'Menu Category';
				
				//MODULES
				//pos_menu_category
				$last_id_sync = $get_last_id_sync['menu_category'];
				$total_data_sync = $get_total_data_sync['menu_category'];
				
				if(!empty($new_data_store['product_category'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix_pos."product_category");
					
					//BATCH
					$this->db->insert_batch($this->prefix_pos."product_category", $new_data_store['product_category']);

					$sync_status = true;
					
				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix_pos."product_category");
						$sync_status = true;
					}
				}
				
				break;
			case 'menu':
				$sync_text = 'Menu';
				
				
				//MODULES
				//pos_product
				$last_id_sync = $get_last_id_sync['menu'];
				$total_data_sync = $get_total_data_sync['menu'];
				
				if(!empty($new_data_store['product'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix_pos."product");
					
					//BATCH
					$this->db->insert_batch($this->prefix_pos."product", $new_data_store['product']);

					$sync_status = true;
					
				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix_pos."product");
						$sync_status = true;
					}
				}
				
				//MENU GRAMASI - pos_product_gramasi
				if(!empty($new_data_store['product_gramasi'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix_pos."product_gramasi");
					
					//BATCH
					$this->db->insert_batch($this->prefix_pos."product_gramasi", $new_data_store['product_gramasi']);

				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix_pos."product_gramasi");
					}
				}
				
				//MENU  PACKAGE - pos_product_package
				if(!empty($new_data_store['product_package'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix_pos."product_package");
					
					//BATCH
					$this->db->insert_batch($this->prefix_pos."product_package", $new_data_store['product_package']);

				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix_pos."product_package");
					}
				}
				
				//VARIAN - pos_product_varian
				if(!empty($new_data_store['product_varian'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix_pos."product_varian");
					
					//BATCH
					$this->db->insert_batch($this->prefix_pos."product_varian", $new_data_store['product_varian']);

				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix_pos."product_varian");
					}
				}
				
				//MASTER VARIAN - pos_varian
				if(!empty($new_data_store['varian'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix_pos."varian");
					
					//BATCH
					$this->db->insert_batch($this->prefix_pos."varian", $new_data_store['varian']);

				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix_pos."varian");
					}
				}
				
				
				break;
			case 'payment_bank':
				$sync_text = 'Payment Bank';
				
				
				//MODULES
				//pos_bank
				$last_id_sync = $get_last_id_sync['payment_bank'];
				$total_data_sync = $get_total_data_sync['payment_bank'];
				
				if(!empty($new_data_store['bank'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix_pos."bank");
					
					//BATCH
					$this->db->insert_batch($this->prefix_pos."bank", $new_data_store['bank']);

					$sync_status = true;
					
				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix_pos."bank");
						$sync_status = true;
					}
				}
				
				//payment_type
				if(!empty($new_data_store['payment_type'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix_pos."payment_type");
					
					//BATCH
					$this->db->insert_batch($this->prefix_pos."payment_type", $new_data_store['payment_type']);

				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix_pos."payment_type");
					}
				}
				
				
				
				break;
			case 'discount':
				$sync_text = 'Discount';
				
				//MODULES
				//pos_discount
				$last_id_sync = $get_last_id_sync['discount'];
				$total_data_sync = $get_total_data_sync['discount'];
				
				if(!empty($new_data_store['discount'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix_pos."discount");
					
					//BATCH
					$this->db->insert_batch($this->prefix_pos."discount", $new_data_store['discount']);

					$sync_status = true;
					
				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix_pos."discount");
						$sync_status = true;
					}
				}
				
				
				//pos_discount_buyget
				if(!empty($new_data_store['discount_buyget'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix_pos."discount_buyget");
					
					//BATCH
					$this->db->insert_batch($this->prefix_pos."discount_buyget", $new_data_store['discount_buyget']);

				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix_pos."discount_buyget");
					}
				}
				
				//pos_discount_product
				if(!empty($new_data_store['discount_product'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix_pos."discount_product");
					
					//BATCH
					$this->db->insert_batch($this->prefix_pos."discount_product", $new_data_store['discount_product']);

				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix_pos."discount_product");
					}
				}
				
				//pos_discount_voucher
				if(!empty($new_data_store['discount_voucher'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix_pos."discount_voucher");
					
					//BATCH
					$this->db->insert_batch($this->prefix_pos."discount_voucher", $new_data_store['discount_voucher']);

				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix_pos."discount_voucher");
					}
				}
				
				
				break;
			case 'supervisor_access':
				$sync_text = 'Supervisor Access';
				
				//MODULES
				//apps_supervisor_access
				$last_id_sync = $get_last_id_sync['supervisor_access'];
				$total_data_sync = $get_total_data_sync['supervisor_access'];
				
				if(!empty($new_data_store['supervisor_access'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix."supervisor_access");
					
					//BATCH
					$this->db->insert_batch($this->prefix."supervisor_access", $new_data_store['supervisor_access']);

					$sync_status = true;
					
				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix."supervisor_access");
						$sync_status = true;
					}
				}
				
				//MASTER - supervisor
				if(!empty($new_data_store['supervisor'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix."supervisor");
					
					//BATCH
					$this->db->insert_batch($this->prefix."supervisor", $new_data_store['supervisor']);
					
				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix."supervisor");
					}
				}
				
				break;
			case 'sales_marketing':
				$sync_text = 'Sales Marketing';
				
				//MODULES
				//pos_sales
				$last_id_sync = $get_last_id_sync['sales_marketing'];
				$total_data_sync = $get_total_data_sync['sales_marketing'];
				
				if(!empty($new_data_store['sales'])){
					
					$insert_data = array();
					foreach($new_data_store['sales'] as $dt){
						$insert_data[] = $dt;
					}
					
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix_pos."sales");
					
					//BATCH
					$this->db->insert_batch($this->prefix_pos."sales", $insert_data);
					
					$sync_status = true;
					
				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix_pos."sales");
						$sync_status = true;
					}
				}
				
				
				break;
			case 'customer_member':
				$sync_text = 'Customer Member';
				
				//MODULES
				//pos_sales
				$last_id_sync = $get_last_id_sync['customer_member'];
				$total_data_sync = $get_total_data_sync['customer_member'];
				
				if(!empty($new_data_store['customer'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix_pos."customer");
					
					//BATCH
					$this->db->insert_batch($this->prefix_pos."customer", $new_data_store['customer']);

					$sync_status = true;
					
				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix_pos."customer");
						$sync_status = true;
					}
				}
				
				
				break;
			
			case 'warehouse_access':
				$sync_text = 'Warehouse Access';
				
				//MODULES
				//pos_sales
				$last_id_sync = $get_last_id_sync['warehouse_access'];
				$total_data_sync = $get_total_data_sync['warehouse_access'];
				
				if(!empty($new_data_store['storehouse_users'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix_pos."storehouse_users");
					
					//BATCH
					$this->db->insert_batch($this->prefix_pos."storehouse_users", $new_data_store['storehouse_users']);

					$sync_status = true;
					
				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix_pos."storehouse_users");
						$sync_status = true;
					}
				}
				
				if(!empty($new_data_store['storehouse'])){
					//TRUNCATE STORE
					$this->db->query("TRUNCATE ".$this->prefix_pos."storehouse");
					
					//BATCH
					$this->db->insert_batch($this->prefix_pos."storehouse", $new_data_store['storehouse']);

				}else{
					if($last_id_sync == 0 AND $total_data_sync == 0){
						//TRUNCATE STORE
						$this->db->query("TRUNCATE ".$this->prefix_pos."storehouse");
					}
				}
				
				
				break;
		}
	
		
		
		if($sync_status){
			
			$save_sync = array(
				'client_id'		=> $client_id,
				'sync_data'		=> $curr_sync_data,
				'total_data'	=> $total_data_sync,
				'last_id'		=> $last_id_sync,
				'sync_status'	=> 'done',
				'auto_manual'	=> 1,
				'scheduled'		=> 0,
				'sync_type'		=> $sync_type,
				'created'		=> date('Y-m-d H:i:s'),
				'createdby'		=> $session_user,
				'updated'		=> date('Y-m-d H:i:s'),
				'updatedby'		=> $session_user,
				'is_active'		=> 1
			);
  
			//$this->db->insert($this->prefix_store."sync", $save_sync);
			
			$client_url = $ipserver_management_systems.'/sync_backup/syncData/saveSyncServer?_dc='.$mktime_dc;
			
			$post_data = array(
				'client_id' => $client_id,
				'sync_text' => $sync_text,
				'save_sync' => json_encode($save_sync)
			);
			
			$curl_ret = $this->curl->simple_post($client_url, $post_data);
			echo $curl_ret;
			
			die();
		}else{
			$r = array('success' => true, 'info'	=> 'Sync Data: '.$sync_text.' Failed!..');
			die(json_encode($r));
		}
				
	}
	
	public function syncDataLog()
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
		$client_url = $ipserver_management_systems.'/sync_backup/syncData/syncDataLog?_dc='.$mktime_dc;
		
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
				$s['sync_data_text'] = '<span style="color:green;font-weight:bold;">'.ucwords(str_replace("_"," ",$s['sync_data'])).'</span>';
				$s['auto_manual_text'] = ($s['auto_manual'] == '1') ? '<span style="color:blue;font-weight:bold;">Manual</span>':'<span style="color:orange;font-weight:bold;">Auto</span>';
				$s['sync_type_text'] = ($s['sync_type'] == 'server') ? '<span style="color:orange;font-weight:bold;">Server</span>':'<span style="color:blue;font-weight:bold;">Client</span>';
				
				$s['total_data'] = '<span style="color:green;font-weight:bold;">'.$s['total_data'].'</span>';
				$s['last_id'] = '<span style="color:green;font-weight:bold;">#'.$s['last_id'].'</span>';
				
				$s['sync_date'] = date("d-m-Y H:i:s", strtotime($s['created']));
				array_push($newData, $s);
			}*/
			
			$get_data['data'] = $return_data['data'];
			$get_data['totalCount'] = $return_data['totalCount'];
		}
		
      	die(json_encode($get_data));
	}
}