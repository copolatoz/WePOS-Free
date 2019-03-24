<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class SyncData extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix');
		$this->prefix_pos = config_item('db_prefix2');
		$this->prefix_acc = config_item('db_prefix3');
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
			'store_connected_id','management_systems','ipserver_management_systems','store_connected_code',
			'store_connected_name','store_connected_email','use_wms','as_server_backup'
		);
		
		$get_opt = get_option_value($opt_val);
		if(empty($get_opt['store_connected_id'])){
			$get_opt['store_connected_id'] = 0;
		}
		if(empty($get_opt['store_connected_code'])){
			$get_opt['store_connected_code'] = 0;
		}
		if(empty($get_opt['store_connected_name'])){
			$get_opt['store_connected_name'] = 0;
		}
		if(empty($get_opt['store_connected_email'])){
			$get_opt['store_connected_email'] = 0;
		}
		if(empty($get_opt['use_wms'])){
			$get_opt['use_wms'] = 0;
		}
		if(empty($get_opt['management_systems'])){
			$r = array('success' => false, 'info' => 'Cek Setup Aplikasi, aktifkan pilihan: <b>Backup Data ke Server</b>');
			die(json_encode($r));
		}
		
		if(empty($get_opt['ipserver_management_systems'])){
			$r = array('success' => false, 'info' => 'Cek Setup Aplikasi, IP server belum disesuaikan');
			die(json_encode($r));
		}
		
		if(!empty($get_opt['as_server_backup'])){
			$r = array('success' => false, 'info' => 'Aplikasi WePOS ini di set sebagai Server Backup!');
			die(json_encode($r));
		}
		
		$store_connected_id = $get_opt['store_connected_id'];
		$management_systems = $get_opt['management_systems'];
		$ipserver_management_systems = $get_opt['ipserver_management_systems'];
		$store_connected_code = $get_opt['store_connected_code'];
		$store_connected_name = $get_opt['store_connected_name'];
		$store_connected_email = $get_opt['store_connected_email'];
		$use_wms = $get_opt['use_wms'];
		
		//CONNECTED TO STORE MANAGEMENT - CURL
		$this->load->library('curl');
		
		$is_connected = 0;
		$mktime_dc = strtotime(date("d-m-Y H:i:s"));
		
		$ipserver_management_systems = prep_url($ipserver_management_systems);
		
		if($use_wms == 1){
			
			$client_url = $ipserver_management_systems.'/systems/masterStore/cekClient?_dc='.$mktime_dc;
			$post_data = array(
				'client_code' => $data_client['client_code'],
				'client_name' => $data_client['client_name'],
				'client_email' => $data_client['client_email']
			);
			
			$crt_file = ASSETS_PATH.config_item('wms_crt_file');
			
		}else{
			
			//wepos.id
			$client_url = config_item('website').'/merchant/checkBackup?_dc='.$mktime_dc;
			
			$post_data = array(
				'client_code' => $data_client['client_code'],
				'client_name' => $data_client['client_name'],
				'client_email' => $data_client['client_email']
			);
			
			
			$crt_file = ASSETS_PATH.config_item('wepos_crt_file');
			
		}
		
		$this->curl->create($client_url);
		$this->curl->option('connecttimeout', 600);
		$this->curl->option('RETURNTRANSFER', 1);
		$this->curl->option('SSL_VERIFYPEER', 1);
		$this->curl->option('SSL_VERIFYHOST', 2);
		//$this->curl->option('SSLVERSION', 3);
		$this->curl->option('POST', 1);
		$this->curl->option('POSTFIELDS', $post_data);
		$this->curl->option('CAINFO', $crt_file);
		$curl_ret = $this->curl->execute();
		
		$data_client['client_ip'] = '-';
		$data_client['mysql_user'] = '-';
		$data_client['mysql_pass'] = '-';
		$data_client['mysql_port'] = '-';
		$data_client['mysql_database'] = '-';
		
		if(!empty($curl_ret)){
			
			if($curl_ret == 'Page Not Found!'){
				$r = array('success' => false, 'info' => 'Tidak ada koneksi ke Server!');
				die(json_encode($r));
			}else{
				$ret_data = json_decode($curl_ret, true);
			
				if(!empty($ret_data['data']) AND $ret_data['success'] == true){
					$store_connected_id = $ret_data['data']['id'];
					$store_connected_code = $ret_data['data']['client_code'];
					$store_connected_name = $ret_data['data']['client_name'];
					$store_connected_email = $ret_data['data']['client_email'];
					$data_client['client_name'] = $ret_data['data']['client_name'];
					$data_client['client_email'] = $ret_data['data']['client_email'];
					$data_client['client_phone'] = $ret_data['data']['client_phone'];
					$data_client['client_address'] = $ret_data['data']['client_address'];
					$data_client['client_ip'] = $ret_data['data']['client_ip'];
					$data_client['mysql_port'] = $ret_data['data']['mysql_port'];
					$data_client['mysql_database'] = $ret_data['data']['mysql_database'];
					$is_connected = 1;
				}else{
					$r = array('success' => false, 'info' => $ret_data['info']);
					die(json_encode($r));
				}
				
			}
			
			
		}else{
			$r = array(
				'success' => false, 
				'info' => 'Data Store/Client: <b>'.$data_client['client_code'].' &mdash; '.$data_client['client_name'].'</b> Tidak teridentifikasi di Server!'
			);
			die(json_encode($r));
		}
		
		if($store_connected_id != $get_opt['store_connected_id'] OR $store_connected_code != $get_opt['store_connected_code']){
			$get_opt['store_connected_id'] = $store_connected_id;
			$get_opt['store_connected_code'] = $store_connected_code;
			$get_opt['store_connected_name'] = $store_connected_name;
			$get_opt['store_connected_email'] = $store_connected_email;
			//update options
			$update_option = update_option($get_opt);
		}
		
		
		$store_connected_id_show = '-';
		if(!empty($store_connected_id)){
			$store_connected_id_show = $store_connected_id;
		}
		
		if($use_wms == 1){
			$data_detail = array(
				'ID' 	=> '<font style="font-weight:bold; color:green;">'.$store_connected_id_show.'</font>',
				'Code' 		=> '<font style="font-weight:bold; color:blue;">'.$data_client['client_code'].'</font>',
				'Name'		=> $data_client['client_name'], 
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
				'Keterangan'	=> 'Curl via WMS',
			);
		}else{
			$data_detail = array(
				'ID' 	=> '<font style="font-weight:bold; color:green;">'.$store_connected_id_show.'</font>',
				'Code' 		=> '<font style="font-weight:bold; color:blue;">'.$data_client['client_code'].'</font>',
				'Name'		=> $data_client['client_name'], 
				'Email'		=> $data_client['client_email'],
				'Phone'		=> $data_client['client_phone'],
				'Address'	=> $data_client['client_address'],
				'&nbsp;&nbsp;'	=> '',
				'DB Status'	=> '',
				'Keterangan'	=> 'via Merchant',
			);
		}
		
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
			'store_connected_name' => $store_connected_name, 
			'store_connected_email' => $store_connected_email, 
			'is_connected' => $is_connected, 
			'data' => $all_data_detail, 
			'totalCount' => count($all_data_detail),
			'use_wms' => $use_wms
		);
		
		die(json_encode($r));
	}
	
	public function syncDetail()
	{
		$client_id = $this->input->post('client_id');
		$client_code = $this->input->post('client_code');
		$client_name = $this->input->post('client_name');
		$client_email = $this->input->post('client_email');
		$is_restore = $this->input->post('is_restore');
		
		if(empty($client_id)){
			$r = array('success' => false, 'info' => 'Store Tidak Teridentifikasi!');
			die(json_encode($r));
		}
		
		//OPT-OPTIONS
		$opt_val = array(
			'store_connected_id','management_systems','ipserver_management_systems','store_connected_code',
			'store_connected_name','store_connected_email','use_wms','as_server_backup','wepos_tipe'
		);
		
		$get_opt = get_option_value($opt_val);
		if(empty($get_opt['store_connected_id'])){
			$get_opt['store_connected_id'] = 0;
		}
		if(empty($get_opt['store_connected_code'])){
			$get_opt['store_connected_code'] = 0;
		}
		if(empty($get_opt['store_connected_name'])){
			$get_opt['store_connected_name'] = 0;
		}
		if(empty($get_opt['store_connected_email'])){
			$get_opt['store_connected_email'] = 0;
		}
		if(empty($get_opt['use_wms'])){
			$get_opt['use_wms'] = 0;
		}
		if(empty($get_opt['wepos_tipe'])){
			$get_opt['wepos_tipe'] = 0;
		}
		if(empty($get_opt['management_systems'])){
			$r = array('success' => false, 'info' => 'Cek Setup Aplikasi, aktifkan pilihan: <b>Backup Data ke Server</b>');
			die(json_encode($r));
		}
		
		if(empty($get_opt['ipserver_management_systems'])){
			$r = array('success' => false, 'info' => 'Cek Setup Aplikasi, IP server belum disesuaikan');
			die(json_encode($r));
		}
		if(!empty($get_opt['as_server_backup'])){
			$r = array('success' => false, 'info' => 'Aplikasi WePOS ini di set sebagai Server Backup!');
			die(json_encode($r));
		}
		
		$store_connected_id = $get_opt['store_connected_id'];
		$store_connected_code = $get_opt['store_connected_code'];
		$store_connected_name = $get_opt['store_connected_name'];
		$store_connected_email = $get_opt['store_connected_email'];
		$management_systems = $get_opt['management_systems'];
		$ipserver_management_systems = $get_opt['ipserver_management_systems'];
		$use_wms = $get_opt['use_wms'];
		$wepos_tipe = $get_opt['use_wms'];
		
		//CONNECTED TO STORE MANAGEMENT - CURL
		$this->load->library('curl');
		
		$mktime_dc = strtotime(date("d-m-Y H:i:s"));
		
		$ipserver_management_systems = prep_url($ipserver_management_systems);
		
		if($use_wms == 1){
			
			$client_url = $ipserver_management_systems.'/sync_backup/syncData/syncDetail?_dc='.$mktime_dc;
			
			$crt_file = ASSETS_PATH.config_item('wms_crt_file');
			
		}else{
			
			//wepos.id
			$client_url = config_item('website').'/merchant/syncDetail?_dc='.$mktime_dc;
			
			$crt_file = ASSETS_PATH.config_item('wepos_crt_file');
			
		}
		
		$post_data = array(
			'client_id' => $client_id,
			'client_code' => $client_code,
			'client_name' => $client_name,
			'client_email' => $client_email,
			'limit' => 9999,
			'page' 	=> 1,
			'start' => 0,
			'akses'=> 'CURL'
		);
		
		$this->curl->create($client_url);
		$this->curl->option('connecttimeout', 600);
		$this->curl->option('RETURNTRANSFER', 1);
		$this->curl->option('SSL_VERIFYPEER', 1);
		$this->curl->option('SSL_VERIFYHOST', 2);
		//$this->curl->option('SSLVERSION', 3);
		$this->curl->option('POST', 1);
		$this->curl->option('POSTFIELDS', $post_data);
		$this->curl->option('CAINFO', $crt_file);
		$curl_ret = $this->curl->execute();
		
		//$curl_ret = $this->curl->simple_post($client_url, $post_data);
		$return_data = json_decode($curl_ret, true);
		
		$available_conn = false;
		if(empty($return_data)){
			$r = array('success' => false, 'info' => 'Cek Data Gagal!');
			die(json_encode($r));
		}else{
			$available_conn = true;
			
			if($return_data['success'] == false){
				$r = $return_data;
				die(json_encode($r));
			}
			
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
			'roles' => 0,
			'roles_module' => 0,
			'data_user' => 0,
			'users_desktop' => 0,
			'users_shortcut' => 0,
			'users_quickstart' => 0,
			'supervisor' => 0,
			'supervisor_access' => 0,
			'varian' => 0,
			'menu' => 0,
			'menu_category' => 0,
			'menu_package' => 0,
			'menu_varian' => 0,
			'payment_bank' => 0,
			'discount' => 0,
			'discount_buyget' => 0,
			'discount_product' => 0,
			'discount_voucher' => 0,
			'sales_marketing' => 0,
			'customer_member' => 0,
			'divisi' => 0,
			'warehouse' => 0,
			'warehouse_access' => 0,
			'unit' => 0,
			'items' => 0,
			'item_category' => 0,
			'item_subcategory' => 0,
			'item_kode_unik' => 0,
			'supplier' => 0,
			'supplier_item' => 0,
			'order_note' => 0,
			'billing_tipe' => 0,
		);
		
		if($wepos_tipe == 'cafe'){
			unset($total_data_lokal['billing_tipe']);
			//$total_data_lokal['table'] = 0;
			//$total_data_lokal['table_inventory'] = 0;
			//$total_data_lokal['floorplan'] = 0;
			//$total_data_lokal['room'] = 0;
		}
		
		$last_id_lokal = array(
			'roles' => 0,
			'roles_module' => 0,
			'data_user' => 0,
			'users_desktop' => 0,
			'users_shortcut' => 0,
			'users_quickstart' => 0,
			'supervisor' => 0,
			'supervisor_access' => 0,
			'varian' => 0,
			'menu' => 0,
			'menu_category' => 0,
			'menu_package' => 0,
			'menu_varian' => 0,
			'payment_bank' => 0,
			'discount' => 0,
			'discount_buyget' => 0,
			'discount_product' => 0,
			'discount_voucher' => 0,
			'sales_marketing' => 0,
			'customer_member' => 0,
			'divisi' => 0,
			'warehouse' => 0,
			'warehouse_access' => 0,
			'unit' => 0,
			'items' => 0,
			'item_category' => 0,
			'item_subcategory' => 0,
			'item_kode_unik' => 0,
			'supplier' => 0,
			'supplier_item' => 0,
			'order_note' => 0,
			'billing_tipe' => 0,
		);
		
		if($wepos_tipe == 'cafe'){
			unset($last_id_lokal['billing_tipe']);
			//$last_id_lokal['table'] = 0;
			//$last_id_lokal['table_inventory'] = 0;
			//$last_id_lokal['floorplan'] = 0;
			//$last_id_lokal['room'] = 0;
		}
		
		//LOAD LOKAL
		//APPS
		$get_roles = $this->db->query("SELECT id FROM ".$this->prefix."roles ORDER BY id DESC");
		if($get_roles->num_rows() > 0){
			$dt_roles = $get_roles->row();
			$last_id_lokal["roles"] = $dt_roles->id;
			$total_data_lokal["roles"] = $get_roles->num_rows();
		}
		
		$dt_roles_module_id = 0;
		$get_roles_module = $this->db->query("SELECT id FROM ".$this->prefix."roles_module ORDER BY id DESC");
		if($get_roles_module->num_rows() > 0){
			$dt_roles_module = $get_roles_module->row();
			$dt_roles_module_id = $dt_roles_module->id;
			$last_id_lokal["roles_module"] = $dt_roles_module->id;
			$total_data_lokal["roles_module"] = $get_roles_module->num_rows();
		}
		
		$get_data_user = $this->db->query("SELECT id FROM ".$this->prefix."users ORDER BY id DESC");
		if($get_data_user->num_rows() > 0){
			$dt_data_user = $get_data_user->row();
			$last_id_lokal["data_user"] = $dt_data_user->id;
			$total_data_lokal["data_user"] = $get_data_user->num_rows();
		}
		
		$get_data_users_desktop = $this->db->query("SELECT id FROM ".$this->prefix."users_desktop ORDER BY id DESC");
		if($get_data_users_desktop->num_rows() > 0){
			$dt_users_desktop = $get_data_users_desktop->row();
			$last_id_lokal["users_desktop"] = $dt_users_desktop->id;
			$total_data_lokal["users_desktop"] = $get_data_users_desktop->num_rows();
		}
		
		$get_data_users_quickstart = $this->db->query("SELECT id FROM ".$this->prefix."users_quickstart ORDER BY id DESC");
		if($get_data_users_quickstart->num_rows() > 0){
			$dt_users_quickstart = $get_data_users_quickstart->row();
			$last_id_lokal["users_quickstart"] = $dt_users_quickstart->id;
			$total_data_lokal["users_quickstart"] = $get_data_users_quickstart->num_rows();
		}
		
		$get_data_users_shortcut = $this->db->query("SELECT id FROM ".$this->prefix."users_shortcut ORDER BY id DESC");
		if($get_data_users_shortcut->num_rows() > 0){
			$dt_users_shortcut = $get_data_users_shortcut->row();
			$last_id_lokal["users_shortcut"] = $dt_users_shortcut->id;
			$total_data_lokal["users_shortcut"] = $get_data_users_shortcut->num_rows();
		}
		
		$get_spv = $this->db->query("SELECT id FROM ".$this->prefix."supervisor ORDER BY id DESC");
		if($get_spv->num_rows() > 0){
			$dt_spv = $get_spv->row();
			$last_id_lokal["supervisor"] = $dt_spv->id;
			$total_data_lokal["supervisor"] = $get_spv->num_rows();
		}
		
		$get_spv_access = $this->db->query("SELECT id FROM ".$this->prefix."supervisor_access ORDER BY id DESC");
		if($get_spv_access->num_rows() > 0){
			$dt_spv_access = $get_spv_access->row();
			$last_id_lokal["supervisor_access"] = $dt_spv_access->id;
			$total_data_lokal["supervisor_access"] = $get_spv_access->num_rows();
		}
		
		//MASTER
		$get_varian = $this->db->query("SELECT id FROM ".$this->prefix_pos."varian ORDER BY id DESC");
		if($get_varian->num_rows() > 0){
			$dt_varian = $get_varian->row();
			$last_id_lokal["varian"] = $dt_varian->id;
			$total_data_lokal["varian"] = $get_varian->num_rows();
		}
		
		$get_product = $this->db->query("SELECT id FROM ".$this->prefix_pos."product ORDER BY id DESC");
		if($get_product->num_rows() > 0){
			$dt_product = $get_product->row();
			$last_id_lokal["menu"] = $dt_product->id;
			$total_data_lokal["menu"] = $get_product->num_rows();
		}
		
		$get_product_category = $this->db->query("SELECT id FROM ".$this->prefix_pos."product_category ORDER BY id DESC");
		if($get_product_category->num_rows() > 0){
			$dt_product_category = $get_product_category->row();
			$last_id_lokal["menu_category"] = $dt_product_category->id;
			$total_data_lokal["menu_category"] = $get_product_category->num_rows();
		}
		
		
		$get_product_package = $this->db->query("SELECT id FROM ".$this->prefix_pos."product_package ORDER BY id DESC");
		if($get_product_package->num_rows() > 0){
			$dt_product_package = $get_product_package->row();
			$last_id_lokal["menu_package"] = $dt_product_package->id;
			$total_data_lokal["menu_package"] = $get_product_package->num_rows();
		}
		
		$get_product_varian = $this->db->query("SELECT id FROM ".$this->prefix_pos."product_varian ORDER BY id DESC");
		if($get_product_varian->num_rows() > 0){
			$dt_product_varian = $get_product_varian->row();
			$last_id_lokal["menu_varian"] = $dt_product_varian->id;
			$total_data_lokal["menu_varian"] = $get_product_varian->num_rows();
		}
		
		$get_bank = $this->db->query("SELECT id FROM ".$this->prefix_pos."bank ORDER BY id DESC");
		if($get_bank->num_rows() > 0){
			$dt_bank = $get_bank->row();
			$last_id_lokal["payment_bank"] = $dt_bank->id;
			$total_data_lokal["payment_bank"] = $get_bank->num_rows();
		}
		
		$get_discount = $this->db->query("SELECT id FROM ".$this->prefix_pos."discount ORDER BY id DESC");
		if($get_discount->num_rows() > 0){
			$dt_discount = $get_discount->row();
			$last_id_lokal["discount"] = $dt_discount->id;
			$total_data_lokal["discount"] = $get_discount->num_rows();
		}
		
		$get_discount_buyget= $this->db->query("SELECT id FROM ".$this->prefix_pos."discount_buyget ORDER BY id DESC");
		if($get_discount_buyget->num_rows() > 0){
			$dt_discount_buyget = $get_discount_buyget->row();
			$last_id_lokal["discount_buyget"] = $dt_discount_buyget->id;
			$total_data_lokal["discount_buyget"] = $get_discount_buyget->num_rows();
		}
		
		$get_discount_product = $this->db->query("SELECT id FROM ".$this->prefix_pos."discount_product ORDER BY id DESC");
		if($get_discount_product->num_rows() > 0){
			$dt_discount_product = $get_discount_product->row();
			$last_id_lokal["discount_product"] = $dt_discount_product->id;
			$total_data_lokal["discount_product"] = $get_discount_product->num_rows();
		}
		
		$get_discount_voucher = $this->db->query("SELECT id FROM ".$this->prefix_pos."discount_voucher ORDER BY id DESC");
		if($get_discount_voucher->num_rows() > 0){
			$dt_discount_voucher = $get_discount_voucher->row();
			$last_id_lokal["discount_voucher"] = $dt_discount_voucher->id;
			$total_data_lokal["discount_voucher"] = $get_discount_voucher->num_rows();
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
		
		$get_divisi = $this->db->query("SELECT id FROM ".$this->prefix_pos."divisi ORDER BY id DESC");
		if($get_divisi->num_rows() > 0){
			$dt_divisi = $get_divisi->row();
			$last_id_lokal["divisi"] = $dt_divisi->id;
			$total_data_lokal["divisi"] = $get_divisi->num_rows();
		}
		
		$get_warehouse = $this->db->query("SELECT id FROM ".$this->prefix_pos."storehouse ORDER BY id DESC");
		if($get_warehouse->num_rows() > 0){
			$dt_warehouse = $get_warehouse->row();
			$last_id_lokal["warehouse"] = $dt_warehouse->id;
			$total_data_lokal["warehouse"] = $get_warehouse->num_rows();
		}
		
		$get_warehouse_access = $this->db->query("SELECT id FROM ".$this->prefix_pos."storehouse_users ORDER BY id DESC");
		if($get_warehouse_access->num_rows() > 0){
			$dt_warehouse_access = $get_warehouse_access->row();
			$last_id_lokal["warehouse_access"] = $dt_warehouse_access->id;
			$total_data_lokal["warehouse_access"] = $get_warehouse_access->num_rows();
		}
		
		$get_unit = $this->db->query("SELECT id FROM ".$this->prefix_pos."unit ORDER BY id DESC");
		if($get_unit->num_rows() > 0){
			$dt_unit = $get_unit->row();
			$last_id_lokal["unit"] = $dt_unit->id;
			$total_data_lokal["unit"] = $get_unit->num_rows();
		}
		
		$get_items = $this->db->query("SELECT id FROM ".$this->prefix_pos."items ORDER BY id DESC");
		if($get_items->num_rows() > 0){
			$dt_items = $get_items->row();
			$last_id_lokal["items"] = $dt_items->id;
			$total_data_lokal["items"] = $get_items->num_rows();
		}
		
		$get_item_category = $this->db->query("SELECT id FROM ".$this->prefix_pos."item_category ORDER BY id DESC");
		if($get_item_category->num_rows() > 0){
			$dt_item_category = $get_item_category->row();
			$last_id_lokal["item_category"] = $dt_item_category->id;
			$total_data_lokal["item_category"] = $get_item_category->num_rows();
		}
		
		$get_item_subcategory = $this->db->query("SELECT id FROM ".$this->prefix_pos."item_subcategory ORDER BY id DESC");
		if($get_item_subcategory->num_rows() > 0){
			$dt_item_subcategory = $get_item_subcategory->row();
			$last_id_lokal["item_subcategory"] = $dt_item_subcategory->id;
			$total_data_lokal["item_subcategory"] = $get_item_subcategory->num_rows();
		}
	
		$get_item_kode_unik = $this->db->query("SELECT id FROM ".$this->prefix_pos."item_kode_unik ORDER BY id DESC");
		if($get_item_kode_unik->num_rows() > 0){
			$dt_item_kode_unik = $get_item_kode_unik->row();
			$last_id_lokal["item_kode_unik"] = $dt_item_kode_unik->id;
			$total_data_lokal["item_kode_unik"] = $get_item_kode_unik->num_rows();
		}
		
		$get_supplier = $this->db->query("SELECT id FROM ".$this->prefix_pos."supplier ORDER BY id DESC");
		if($get_supplier->num_rows() > 0){
			$dt_supplier = $get_supplier->row();
			$last_id_lokal["supplier"] = $dt_supplier->id;
			$total_data_lokal["supplier"] = $get_supplier->num_rows();
		}
		
		$get_supplier_item = $this->db->query("SELECT id FROM ".$this->prefix_pos."supplier_item ORDER BY id DESC");
		if($get_supplier_item->num_rows() > 0){
			$dt_supplier_item = $get_supplier_item->row();
			$last_id_lokal["supplier_item"] = $dt_supplier_item->id;
			$total_data_lokal["supplier_item"] = $get_supplier_item->num_rows();
		}
		
		$get_order_note = $this->db->query("SELECT id FROM ".$this->prefix_pos."order_note ORDER BY id DESC");
		if($get_order_note->num_rows() > 0){
			$dt_order_note = $get_order_note->row();
			$last_id_lokal["order_note"] = $dt_order_note->id;
			$total_data_lokal["order_note"] = $get_order_note->num_rows();
		}
		
		
		if($wepos_tipe == 'cafe'){
			
			$get_table = $this->db->query("SELECT id FROM ".$this->prefix_pos."table ORDER BY id DESC");
			if($get_table->num_rows() > 0){
				$dt_table = $get_table->row();
				$last_id_lokal["table"] = $dt_table->id;
				$total_data_lokal["table"] = $get_table->num_rows();
			}
			
			$get_table_inventory = $this->db->query("SELECT id FROM ".$this->prefix_pos."table_inventory ORDER BY id DESC");
			if($get_table_inventory->num_rows() > 0){
				$dt_table_inventory = $get_table_inventory->row();
				$last_id_lokal["table_inventory"] = $dt_table_inventory->id;
				$total_data_lokal["table_inventory"] = $get_table_inventory->num_rows();
			}
			
			$get_floorplan = $this->db->query("SELECT id FROM ".$this->prefix_pos."floorplan ORDER BY id DESC");
			if($get_floorplan->num_rows() > 0){
				$dt_floorplan = $get_floorplan->row();
				$last_id_lokal["floorplan"] = $dt_floorplan->id;
				$total_data_lokal["floorplan"] = $get_floorplan->num_rows();
			}
			
			$get_room = $this->db->query("SELECT id FROM ".$this->prefix_pos."room ORDER BY id DESC");
			if($get_room->num_rows() > 0){
				$dt_room = $get_room->row();
				$last_id_lokal["room"] = $dt_room->id;
				$total_data_lokal["room"] = $get_room->num_rows();
			}
		
		
		}else{
			
			$get_billing_tipe = $this->db->query("SELECT id FROM ".$this->prefix_pos."table ORDER BY id DESC");
			if($get_billing_tipe->num_rows() > 0){
				$dt_billing_tipe = $get_billing_tipe->row();
				$last_id_lokal["billing_tipe"] = $dt_billing_tipe->id;
				$total_data_lokal["billing_tipe"] = $get_billing_tipe->num_rows();
			}
			
		}
		
		$sync_data_allowed_in = implode("','", $sync_data_allowed);
		
		$sync_data_store = array();
		$sync_data_store_available = array();
		
		$no = 0;
		foreach($sync_data_allowed as $key => $val){
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
			
			$sync_status_text = '<font style="font-weight:bold; color:red;">Update Now</font>';
			$sync_status = 'update now';
			if($updated_total == 1 AND $updated_last_id == 1){
				$sync_status_text = '<font style="font-weight:bold; color:green;">Updated</font>';
				$sync_status = 'updated';
			}
			
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
				$sync_data_store[$val] = array(
					'id'				=> $key,
					'client_id'			=> $client_id,
					'client_code'		=> $client_code,
					'client_name'		=> $client_name,
					'client_email'		=> $client_email,
					'sync_data'			=> $val,
					'sync_data_text'	=> $sync_data_text[$val],
					'total_data_lokal'	=> $total_data_lokal[$val],
					'total_data_server'	=> $total_data_server[$val],
					'last_id_lokal'		=> $last_id_lokal[$val],
					'last_id_server'	=> $last_id_server[$val],
					'last_update'		=> $last_update_text,
					'sync_status'		=> $sync_status,
					'sync_status_text'	=> $sync_status_text,
					'total_data_store'	=> $total_data_store,
					'total_data_on_backup'	=> $total_data_srv,
					'last_id_store'		=> $last_id_store,
					'last_id_on_backup'	=> $last_id_srv
				);
			}
			
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
			'merchant_key' => $client_code, 
			'data' => $sync_data_store, 
			'totalCount' => count($sync_data_store), 
			'use_wms'	=> $use_wms
			//'available_conn' => $available_conn, 
			//'sync_data_allowed' => $sync_data_allowed, 
			//'sync_data_text' 	=> $sync_data_text, 
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
		$this->table_sync = $this->prefix_acc.'sync';
		
		$client_id = $this->input->post('client_id');
		$client_code = $this->input->post('client_code');
		$client_name = $this->input->post('client_name');
		$client_email = $this->input->post('client_email');
		$sync_type = $this->input->post('sync_type');
		$is_syncbackup = $this->input->post('is_syncbackup');
		
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'User Session Expired, Please Re-Login!');
			die(json_encode($r));
		}
				
		if(empty($client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}		
		
		if(empty($sync_type)){
			$sync_type = 'client';
		}
		
		$total_data = $this->input->post('total_data');
		$current_total = $this->input->post('current_total');
		$last_id_on_backup = $this->input->post('last_id_on_backup');
		$total_data_on_backup = $this->input->post('total_data_on_backup');
		
		$sync_data = $this->input->post('sync_data');
		$sync_data = json_decode($sync_data, true);
		
		$sync_data_id = $this->input->post('sync_data_id');
		$sync_data_id = json_decode($sync_data_id, true);
		
		
		//OPT-OPTIONS
		$opt_val = array(
			'store_connected_id','management_systems','ipserver_management_systems','store_connected_code',
			'store_connected_name','store_connected_email','use_wms','as_server_backup','wepos_tipe'
		);
		
		$get_opt = get_option_value($opt_val);
		if(empty($get_opt['store_connected_id'])){
			$get_opt['store_connected_id'] = 0;
		}
		if(empty($get_opt['store_connected_code'])){
			$get_opt['store_connected_code'] = 0;
		}
		if(empty($get_opt['store_connected_name'])){
			$get_opt['store_connected_name'] = 0;
		}
		if(empty($get_opt['store_connected_email'])){
			$get_opt['store_connected_email'] = 0;
		}
		if(empty($get_opt['use_wms'])){
			$get_opt['use_wms'] = 0;
		}
		if(empty($get_opt['wepos_tipe'])){
			$get_opt['wepos_tipe'] = 'cafe';
		}
		
		if(empty($get_opt['management_systems'])){
			$r = array('success' => false, 'info' => 'Cek Setup Aplikasi, aktifkan pilihan: <b>Backup Data ke Server</b>');
			die(json_encode($r));
		}
		
		if(empty($get_opt['ipserver_management_systems'])){
			$r = array('success' => false, 'info' => 'Cek Setup Aplikasi, IP server belum disesuaikan');
			die(json_encode($r));
		}
		
		if(!empty($get_opt['as_server_backup'])){
			$r = array('success' => false, 'info' => 'Aplikasi WePOS ini di set sebagai Server Backup!');
			die(json_encode($r));
		}
		
		$store_connected_id = $get_opt['store_connected_id'];
		$store_connected_code = $get_opt['store_connected_code'];
		$store_connected_name = $get_opt['store_connected_name'];
		$store_connected_email = $get_opt['store_connected_email'];
		$management_systems = $get_opt['management_systems'];
		$ipserver_management_systems = $get_opt['ipserver_management_systems'];
		$use_wms = $get_opt['use_wms'];
		$wepos_tipe = $get_opt['wepos_tipe'];
		
		$backup_data_allowed = array(
			10 => 'roles',
			11 => 'roles_module',
			20 => 'data_user',
			21 => 'users_desktop',
			22 => 'users_shortcut',
			23 => 'users_quickstart',
			30 => 'supervisor',
			31 => 'supervisor_access',
			40 => 'varian',
			50 => 'menu',
			51 => 'menu_category',
			52 => 'menu_package',
			53 => 'menu_varian',
			60 => 'payment_bank',
			70 => 'discount',
			71 => 'discount_buyget',
			72 => 'discount_product',
			73 => 'discount_voucher',
			80 => 'sales_marketing',
			90 => 'customer_member',
			100 => 'divisi',
			110 => 'warehouse',
			111 => 'warehouse_access',
			120 => 'unit',
			130 => 'items',
			131 => 'item_category',
			132 => 'item_subcategory',
			133 => 'item_kode_unik',
			140 => 'supplier',
			141 => 'supplier_item',
			150 => 'order_note',
			160 => 'billing_tipe',
		);
		
		if($wepos_tipe == 'cafe'){
			unset($backup_data_allowed[160]);
			//$backup_data_allowed[160] = 'table';
			//$backup_data_allowed[161] = 'table_inventory';
			//$backup_data_allowed[162] = 'floorplan';
			//$backup_data_allowed[163] = 'room';
		}
		
		
		$sync_data_text = array(
			'roles' => 'Roles',
			'roles_module' => 'Roles Module',
			'data_user' => 'User',
			'users_desktop' => 'User Desktop',
			'users_shortcut' => 'User Shortcut',
			'users_quickstart' => 'User Quickstart',
			'supervisor' => 'Supervisor',
			'supervisor_access' => 'Supervisor Access',
			'varian' => 'Menu Varian',
			'menu' => 'Menu/Product',
			'menu_category' => 'Menu/Product Category',
			'menu_package' => 'Menu/Product Package',
			'menu_varian' => 'Menu/Product Varian',
			'payment_bank' => 'Payment & Bank',
			'discount' => 'Discount',
			'discount_buyget' => 'Discount Buy & Get',
			'discount_product' => 'Discount Menu/Product',
			'discount_voucher' => 'Discount Voucher',
			'sales_marketing' => 'Sales/Marketing',
			'customer_member' => 'Customer/Member',
			'divisi' => 'Divisi/Bagian',
			'warehouse' => 'Warehouse/Gudang',
			'warehouse_access' => 'Warehouse Access',
			'unit' => 'Unit/Satuan',
			'items' => 'Item/Barang',
			'item_category' => 'Item Category',
			'item_subcategory' => 'Sub Category',
			'item_kode_unik' => 'Unique Code',
			'supplier' => 'Supplier',
			'supplier_item' => 'Supplier Items',
			'order_note' => 'Order Note',
			'billing_tipe' => 'Tipe Billing',
		);
		
		if($wepos_tipe == 'cafe'){
			unset($sync_data_text['billing_tipe']);
			//$sync_data_text['table'] = 'Table';
			//$sync_data_text['table_inventory'] = 'Table Inventory';
			//$sync_data_text['floorplan'] = 'Floorplan';
			//$sync_data_text['room'] = 'Room';
		}
		
		$backup_data_allowed_req = array(10,20,30,50,70,110,130,140,160);
		
		$total_data = count($sync_data);
		
		
		//CEK FIRST TO START DATE
		$curr_backup_data = '';
		$curr_backup_id = '';
		
		$i = 0;
		foreach($sync_data as $key => $dtT){
			$i++;
			
			if($i == $current_total){
				$curr_backup_id = array_search($dtT, $backup_data_allowed);
				$curr_backup_data = $dtT;
			}
		}
		
		//if($current_total == 1){
			//check requirement
			if(in_array($curr_backup_id, $backup_data_allowed_req)){
				
				$tot_req = 0;
				$curr_req = $curr_backup_id;
				for($x=1; $x<10; $x++){
					$curr_req = $curr_req+$x;
					if(in_array($curr_req, $sync_data_id)){
						$tot_req++;
					}
				}	
				
				if($tot_req == 0){
					$nama_backup = '-';
					if(!empty($sync_data_text[$curr_backup_data])){
						$nama_backup = $sync_data_text[$curr_backup_data];
					}
					$r = array('success' => false, 'info'	=> 'Pilih Semua <font color="red"><b>Sub '.$nama_backup.'</b></font>');
					die(json_encode($r));
				}
				
			}
		//}
		
		
		if(empty($curr_backup_data)){
			$r = array('success' => false, 'info'	=> 'Invalid Backup Data!');
			die(json_encode($r));
		}
		
		$backup_status = false;
		
		
		//CONNECTED TO STORE MANAGEMENT - CURL
		$this->load->library('curl');
		
		$mktime_dc = strtotime(date("d-m-Y H:i:s"));
		
		$ipserver_management_systems = prep_url($ipserver_management_systems);
		
		$post_data = array(
			'client_id' => $client_id,
			'client_code' => $client_code,
			'client_name' => $client_name,
			'client_email' => $client_email,
			'curr_backup_data' => $curr_backup_data,
			'backup_type' => $sync_type,
			'backup_masterdata' => 1,
			'current_total' => $current_total,
			'backup_data' => json_encode($sync_data),
			'akses'=> 'CURL',
			'last_id_on_backup' => $last_id_on_backup,
			'total_data_on_backup' => $total_data_on_backup,
			'limit_backup_data' => $limit_backup_data
		);
		
		//DATA BACKUP
		if($is_syncbackup == 'sync'){
			
			//---------------------------------SYNC
			unset($post_data['backup_data']);
			unset($post_data['backup_masterdata']);
			unset($post_data['curr_backup_data']);
			$post_data['backup_type'] = $sync_type;
			$post_data['curr_sync_data'] = $curr_backup_data;
			$post_data['sync_data'] = json_encode($sync_data);
			$post_data['sync_masterdata'] = 1;
			$client_url = $ipserver_management_systems.'/sync_backup/syncData/generate?_dc='.$mktime_dc;
			$crt_file = ASSETS_PATH.config_item('wms_crt_file');
			
		}else{
			
			if($use_wms == 1){
				$r = array('success' => false, 'info'	=> 'Fitur Backup Master Data tidak bisa digunakan jika menggunakan WMS<br/>Master Data dikelola secara terpusat oleh aplikasi WMS');
				die(json_encode($r));
			}
			
			//------------------------------BACKUP
			//wepos.id
			$client_url = config_item('website').'/merchant/backupGenerate?_dc='.$mktime_dc;
			$crt_file = ASSETS_PATH.config_item('wepos_crt_file');
		
			//BACKUP DATA - UPLOAD DATA
			$backup_text = '';
			switch($curr_backup_data){
				
				//Roles
				case 'roles':
					$backup_text = 'Roles';
					
					//Roles ON STORE - GET LAST ID
					$last_id_roles_store = 0;
					$total_data_store = 0;
					$get_all_store_roles = $this->db->query("SELECT id FROM ".$this->prefix."roles ORDER BY id DESC");
					if($get_all_store_roles->num_rows() > 0){
						$dt_all_roles_store = $get_all_store_roles->row();
						$last_id_roles_store = $dt_all_roles_store->id;
						$total_data_store = $get_all_store_roles->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_roles_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Roles ON STORE id > $last_id_on_backup
					$data_roles_store = array();
					$all_role_id = array();
					$get_store_roles = $this->db->query("SELECT * FROM ".$this->prefix."roles WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_roles->num_rows() > 0){
						
						foreach($get_store_roles->result() as $dt){
							
							$data_roles_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_role_id)){
								$all_role_id[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_roles_store;
					}
					
					if($last_id_roles_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_roles_store'] = $last_id_roles_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['data_roles_store'] = json_encode($data_roles_store);
					$post_data['all_role_id'] = json_encode($all_role_id);
					
					break;
				
				//Roles Modules
				case 'roles_module':
					$backup_text = 'Roles Modules';
					
					//Roles Modules ON STORE - GET LAST ID
					$last_id_roles_module_store = 0;
					$total_data_store = 0;
					$get_all_store_roles_module = $this->db->query("SELECT id FROM ".$this->prefix."roles_module ORDER BY id DESC");
					if($get_all_store_roles_module->num_rows() > 0){
						$dt_all_roles_module_store = $get_all_store_roles_module->row();
						$last_id_roles_module_store = $dt_all_roles_module_store->id;
						$total_data_store = $get_all_store_roles_module->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_roles_module_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Roles Modules ON STORE id > $last_id_on_backup
					$data_roles_module_store = array();
					$all_roles_module_id = array();
					$get_store_roles_module = $this->db->query("SELECT * FROM ".$this->prefix."roles_module WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_roles_module->num_rows() > 0){
						
						foreach($get_store_roles_module->result() as $dt){
							
							$data_roles_module_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_roles_module_id)){
								$all_roles_module_id[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_roles_module_store;
					}
					
					if($last_id_roles_module_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_roles_module_store'] = $last_id_roles_module_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['data_roles_module_store'] = json_encode($data_roles_module_store);
					$post_data['all_roles_module_id'] = json_encode($all_roles_module_id);
					
					
					break;
				
				//User
				case 'data_user':
					$backup_text = 'User';
					
					//User ON STORE
					$last_id_users_store = 0;
					$total_data_store = 0;
					$get_all_store_users = $this->db->query("SELECT id FROM ".$this->prefix."users ORDER BY id DESC");
					if($get_all_store_users->num_rows() > 0){
						$dt_all_users_store = $get_all_store_users->row();
						$last_id_users_store = $dt_all_users_store->id;
						$total_data_store = $get_all_store_users->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_users_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//User ON STORE id > $last_id_on_backup
					$data_users_store = array();
					$all_user_id = array();
					$get_store_users = $this->db->query("SELECT * FROM ".$this->prefix."users WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_users->num_rows() > 0){
						
						foreach($get_store_users->result() as $dt){
							
							$data_users_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_user_id)){
								$all_user_id[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_users_store;
					}
					
					//NEXT DATA
					if($last_id_users_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_users_store'] = $last_id_users_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['data_users_store'] = json_encode($data_users_store);
					$post_data['all_user_id'] = json_encode($all_user_id);
					
					break;
				
				//User Desktop
				case 'users_desktop':
					$backup_text = 'User Desktop';
					
					//User Desktop ON STORE - GET LAST ID
					$last_id_users_desktop_store = 0;
					$total_data_store = 0;
					$get_all_store_users_desktop = $this->db->query("SELECT id FROM ".$this->prefix."users_desktop ORDER BY id DESC");
					if($get_all_store_users_desktop->num_rows() > 0){
						$dt_all_users_desktop_store = $get_all_store_users_desktop->row();
						$last_id_users_desktop_store = $dt_all_users_desktop_store->id;
						$total_data_store = $get_all_store_users_desktop->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_users_desktop_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//User Desktop ON STORE id > $last_id_on_backup
					$data_users_desktop_store = array();
					$all_users_desktop_id = array();
					$get_store_users_desktop = $this->db->query("SELECT * FROM ".$this->prefix."users_desktop WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_users_desktop->num_rows() > 0){
						
						foreach($get_store_users_desktop->result() as $dt){
							
							$data_users_desktop_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_users_desktop_id)){
								$all_users_desktop_id[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_users_desktop_store;
					}
					
					if($last_id_users_desktop_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_users_desktop_store'] = $last_id_users_desktop_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['data_users_desktop_store'] = json_encode($data_users_desktop_store);
					$post_data['all_users_desktop_id'] = json_encode($all_users_desktop_id);
					
					
					break;
				
				//User Shortcut 
				case 'users_shortcut':
					$backup_text = 'User Shortcut';
					
					//User Shortcut ON STORE - GET LAST ID
					$last_id_users_shortcut_store = 0;
					$total_data_store = 0;
					$get_all_store_users_shortcut = $this->db->query("SELECT id FROM ".$this->prefix."users_shortcut ORDER BY id DESC");
					if($get_all_store_users_shortcut->num_rows() > 0){
						$dt_all_users_shortcut_store = $get_all_store_users_shortcut->row();
						$last_id_users_shortcut_store = $dt_all_users_shortcut_store->id;
						$total_data_store = $get_all_store_users_shortcut->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_users_shortcut_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//User Shortcut ON STORE id > $last_id_on_backup
					$data_users_shortcut_store = array();
					$all_users_shortcut_id = array();
					$get_store_users_shortcut = $this->db->query("SELECT * FROM ".$this->prefix."users_shortcut WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_users_shortcut->num_rows() > 0){
						
						foreach($get_store_users_shortcut->result() as $dt){
							
							$data_users_shortcut_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_users_shortcut_id)){
								$all_users_shortcut_id[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_users_shortcut_store;
					}
					
					if($last_id_users_shortcut_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_users_shortcut_store'] = $last_id_users_shortcut_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['data_users_shortcut_store'] = json_encode($data_users_shortcut_store);
					$post_data['all_users_shortcut_id'] = json_encode($all_users_shortcut_id);
					
					
					break;
					
				//User Quickstart 	
				case 'users_quickstart':
					$backup_text = 'User Quickstart';
					
					//User Quickstart ON STORE - GET LAST ID
					$last_id_users_quickstart_store = 0;
					$total_data_store = 0;
					$get_all_store_users_quickstart = $this->db->query("SELECT id FROM ".$this->prefix."users_quickstart ORDER BY id DESC");
					if($get_all_store_users_quickstart->num_rows() > 0){
						$dt_all_users_quickstart_store = $get_all_store_users_quickstart->row();
						$last_id_users_quickstart_store = $dt_all_users_quickstart_store->id;
						$total_data_store = $get_all_store_users_quickstart->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_users_quickstart_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//User Quickstart ON STORE id > $last_id_on_backup
					$data_users_quickstart_store = array();
					$all_users_quickstart_id = array();
					$get_store_users_quickstart = $this->db->query("SELECT * FROM ".$this->prefix."users_quickstart WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_users_quickstart->num_rows() > 0){
						
						foreach($get_store_users_quickstart->result() as $dt){
							
							$data_users_quickstart_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_users_quickstart_id)){
								$all_users_quickstart_id[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_users_quickstart_store;
					}
					
					if($last_id_users_quickstart_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_users_quickstart_store'] = $last_id_users_quickstart_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['data_users_quickstart_store'] = json_encode($data_users_quickstart_store);
					$post_data['all_users_quickstart_id'] = json_encode($all_users_quickstart_id);
					
					
					break;
					
				//Supervisor
				case 'supervisor':
					$backup_text = 'Supervisor';
					
					//Supervisor ON STORE
					$last_id_supervisor_store = 0;
					$total_data_store = 0;
					$get_all_store_supervisor = $this->db->query("SELECT id FROM ".$this->prefix."supervisor ORDER BY id DESC");
					if($get_all_store_supervisor->num_rows() > 0){
						$dt_all_supervisor_store = $get_all_store_supervisor->row();
						$last_id_supervisor_store = $dt_all_supervisor_store->id;
						$total_data_store = $get_all_store_supervisor->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_supervisor_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Supervisor ON STORE id > $last_id_on_backup
					$supervisors_store = array();
					$all_supervisor = array();
					$get_store_supervisor = $this->db->query("SELECT * FROM ".$this->prefix."supervisor WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_supervisor->num_rows() > 0){
						
						foreach($get_store_supervisor->result() as $dt){
							
							$supervisors_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_supervisor)){
								$all_supervisor[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_supervisor_store;
					}
					
					//NEXT DATA
					if($last_id_supervisor_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_supervisor_store'] = $last_id_supervisor_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['supervisors_store'] = json_encode($supervisors_store);
					$post_data['all_supervisor'] = json_encode($all_supervisor);
					
					break;
				
				//Supervisor Access	
				case 'supervisor_access':
					$backup_text = 'Supervisor Access';
					
					//Supervisor Access ON STORE - GET LAST ID
					$last_id_supervisor_access_store = 0;
					$total_data_store = 0;
					$get_all_store_supervisor_access = $this->db->query("SELECT id FROM ".$this->prefix."supervisor_access ORDER BY id DESC");
					if($get_all_store_supervisor_access->num_rows() > 0){
						$dt_all_supervisor_access_store = $get_all_store_supervisor_access->row();
						$last_id_supervisor_access_store = $dt_all_supervisor_access_store->id;
						$total_data_store = $get_all_store_supervisor_access->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_supervisor_access_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Supervisor Access ON STORE id > $last_id_on_backup
					$data_supervisor_access_store = array();
					$all_supervisor_access_id = array();
					$get_store_supervisor_access = $this->db->query("SELECT * FROM ".$this->prefix."supervisor_access WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_supervisor_access->num_rows() > 0){
						
						foreach($get_store_supervisor_access->result() as $dt){
							
							$data_supervisor_access_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_supervisor_access_id)){
								$all_supervisor_access_id[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_supervisor_access_store;
					}
					
					if($last_id_supervisor_access_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_supervisor_access_store'] = $last_id_supervisor_access_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['data_supervisor_access_store'] = json_encode($data_supervisor_access_store);
					$post_data['all_supervisor_access_id'] = json_encode($all_supervisor_access_id);
					
					break;
					
				//MASTER DATA ----------------------------------------
				//Menu Varian
				case 'varian':
					$backup_text = 'Menu Varian';
					
					//Menu Varian ON STORE
					$last_id_varian_store = 0;
					$total_data_store = 0;
					$get_all_store_varian = $this->db->query("SELECT id FROM ".$this->prefix_pos."varian ORDER BY id DESC");
					if($get_all_store_varian->num_rows() > 0){
						$dt_all_varian_store = $get_all_store_varian->row();
						$last_id_varian_store = $dt_all_varian_store->id;
						$total_data_store = $get_all_store_varian->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_varian_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Menu Varian ON STORE id > $last_id_on_backup
					$varians_store = array();
					$all_varian = array();
					$get_store_varian = $this->db->query("SELECT * FROM ".$this->prefix_pos."varian WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_varian->num_rows() > 0){
						
						foreach($get_store_varian->result() as $dt){
							
							$varians_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_varian)){
								$all_varian[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_varian_store;
					}
					
					//NEXT DATA
					if($last_id_varian_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_varian_store'] = $last_id_varian_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['varians_store'] = json_encode($varians_store);
					$post_data['all_varian'] = json_encode($all_varian);
					
					break;	
				
				//Menu/Product
				case 'menu':
					$backup_text = 'Menu/Product';
					
					//Menu/Product ON STORE
					$last_id_product_store = 0;
					$total_data_store = 0;
					$get_all_store_product = $this->db->query("SELECT id FROM ".$this->prefix_pos."product ORDER BY id DESC");
					if($get_all_store_product->num_rows() > 0){
						$dt_all_product_store = $get_all_store_product->row();
						$last_id_product_store = $dt_all_product_store->id;
						$total_data_store = $get_all_store_product->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_product_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Menu/Product ON STORE id > $last_id_on_backup
					$products_store = array();
					$all_product = array();
					$get_store_product = $this->db->query("SELECT * FROM ".$this->prefix_pos."product WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_product->num_rows() > 0){
						
						foreach($get_store_product->result() as $dt){
							
							$products_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_product)){
								$all_product[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_product_store;
					}
					
					//NEXT DATA
					if($last_id_product_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_product_store'] = $last_id_product_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['products_store'] = json_encode($products_store);
					$post_data['all_product'] = json_encode($all_product);
					
					break;	
					
				//Menu Category
				case 'menu_category':
					$backup_text = 'Menu Category';
					
					//Menu Category ON STORE
					$last_id_menu_category_store = 0;
					$total_data_store = 0;
					$get_all_store_menu_category = $this->db->query("SELECT id FROM ".$this->prefix_pos."product_category ORDER BY id DESC");
					if($get_all_store_menu_category->num_rows() > 0){
						$dt_all_menu_category_store = $get_all_store_menu_category->row();
						$last_id_menu_category_store = $dt_all_menu_category_store->id;
						$total_data_store = $get_all_store_menu_category->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_menu_category_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Menu Category ON STORE id > $last_id_on_backup
					$menu_categorys_store = array();
					$all_menu_category = array();
					$get_store_menu_category = $this->db->query("SELECT * FROM ".$this->prefix_pos."product_category WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_menu_category->num_rows() > 0){
						
						foreach($get_store_menu_category->result() as $dt){
							
							$menu_categorys_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_menu_category)){
								$all_menu_category[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_menu_category_store;
					}
					
					//NEXT DATA
					if($last_id_menu_category_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_menu_category_store'] = $last_id_menu_category_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['menu_categorys_store'] = json_encode($menu_categorys_store);
					$post_data['all_menu_category'] = json_encode($all_menu_category);
					
					break;	
					
				//Menu Package
				case 'menu_package':
					$backup_text = 'Menu Package';
					
					//Menu Package ON STORE
					$last_id_menu_package_store = 0;
					$total_data_store = 0;
					$get_all_store_menu_package = $this->db->query("SELECT id FROM ".$this->prefix_pos."product_package ORDER BY id DESC");
					if($get_all_store_menu_package->num_rows() > 0){
						$dt_all_menu_package_store = $get_all_store_menu_package->row();
						$last_id_menu_package_store = $dt_all_menu_package_store->id;
						$total_data_store = $get_all_store_menu_package->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_menu_package_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Menu Package ON STORE id > $last_id_on_backup
					$menu_packages_store = array();
					$all_menu_package = array();
					$get_store_menu_package = $this->db->query("SELECT * FROM ".$this->prefix_pos."product_package WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_menu_package->num_rows() > 0){
						
						foreach($get_store_menu_package->result() as $dt){
							
							$menu_packages_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_menu_package)){
								$all_menu_package[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_menu_package_store;
					}
					
					//NEXT DATA
					if($last_id_menu_package_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_menu_package_store'] = $last_id_menu_package_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['menu_packages_store'] = json_encode($menu_packages_store);
					$post_data['all_menu_package'] = json_encode($all_menu_package);
					
					break;	
				
				//Menu Varian
				case 'menu_varian':
					$backup_text = 'Menu Varian';
					
					//Menu Varian ON STORE
					$last_id_menu_varian_store = 0;
					$total_data_store = 0;
					$get_all_store_menu_varian = $this->db->query("SELECT id FROM ".$this->prefix_pos."product_varian ORDER BY id DESC");
					if($get_all_store_menu_varian->num_rows() > 0){
						$dt_all_menu_varian_store = $get_all_store_menu_varian->row();
						$last_id_menu_varian_store = $dt_all_menu_varian_store->id;
						$total_data_store = $get_all_store_menu_varian->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_menu_varian_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Menu Varian ON STORE id > $last_id_on_backup
					$menu_varians_store = array();
					$all_menu_varian = array();
					$get_store_menu_varian = $this->db->query("SELECT * FROM ".$this->prefix_pos."product_varian WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_menu_varian->num_rows() > 0){
						
						foreach($get_store_menu_varian->result() as $dt){
							
							$menu_varians_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_menu_varian)){
								$all_menu_varian[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_menu_varian_store;
					}
					
					//NEXT DATA
					if($last_id_menu_varian_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_menu_varian_store'] = $last_id_menu_varian_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['menu_varians_store'] = json_encode($menu_varians_store);
					$post_data['all_menu_varian'] = json_encode($all_menu_varian);
					
					break;	
				
				
				//Payment & Bank
				case 'payment_bank':
					$backup_text = 'Payment & Bank';
					
					//Payment & Bank ON STORE
					$last_id_payment_bank_store = 0;
					$total_data_store = 0;
					$get_all_store_payment_bank = $this->db->query("SELECT id FROM ".$this->prefix_pos."bank ORDER BY id DESC");
					if($get_all_store_payment_bank->num_rows() > 0){
						$dt_all_payment_bank_store = $get_all_store_payment_bank->row();
						$last_id_payment_bank_store = $dt_all_payment_bank_store->id;
						$total_data_store = $get_all_store_payment_bank->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_payment_bank_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Payment & Bank ON STORE id > $last_id_on_backup
					$payment_banks_store = array();
					$all_payment_bank = array();
					$get_store_payment_bank = $this->db->query("SELECT * FROM ".$this->prefix_pos."bank WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_payment_bank->num_rows() > 0){
						
						foreach($get_store_payment_bank->result() as $dt){
							
							$payment_banks_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_payment_bank)){
								$all_payment_bank[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_payment_bank_store;
					}
					
					//NEXT DATA
					if($last_id_payment_bank_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_payment_bank_store'] = $last_id_payment_bank_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['payment_banks_store'] = json_encode($payment_banks_store);
					$post_data['all_payment_bank'] = json_encode($all_payment_bank);
					
					break;	
				
				//Discount
				case 'discount':
					$backup_text = 'Discount';
					
					//Discount ON STORE
					$last_id_discount_store = 0;
					$total_data_store = 0;
					$get_all_store_discount = $this->db->query("SELECT id FROM ".$this->prefix_pos."discount ORDER BY id DESC");
					if($get_all_store_discount->num_rows() > 0){
						$dt_all_discount_store = $get_all_store_discount->row();
						$last_id_discount_store = $dt_all_discount_store->id;
						$total_data_store = $get_all_store_discount->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_discount_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Discount ON STORE id > $last_id_on_backup
					$discounts_store = array();
					$all_discount = array();
					$get_store_discount = $this->db->query("SELECT * FROM ".$this->prefix_pos."discount WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_discount->num_rows() > 0){
						
						foreach($get_store_discount->result() as $dt){
							
							$discounts_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_discount)){
								$all_discount[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_discount_store;
					}
					
					//NEXT DATA
					if($last_id_discount_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_discount_store'] = $last_id_discount_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['discounts_store'] = json_encode($discounts_store);
					$post_data['all_discount'] = json_encode($all_discount);
					
					break;	
				
				//Discount Buyget
				case 'discount_buyget':
					$backup_text = 'Discount Buy & Get';
					
					//Discount Buy & Get ON STORE
					$last_id_discount_buyget_store = 0;
					$total_data_store = 0;
					$get_all_store_discount_buyget = $this->db->query("SELECT id FROM ".$this->prefix_pos."discount_buyget ORDER BY id DESC");
					if($get_all_store_discount_buyget->num_rows() > 0){
						$dt_all_discount_buyget_store = $get_all_store_discount_buyget->row();
						$last_id_discount_buyget_store = $dt_all_discount_buyget_store->id;
						$total_data_store = $get_all_store_discount_buyget->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_discount_buyget_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Discount Buy & Get ON STORE id > $last_id_on_backup
					$discount_buygets_store = array();
					$all_discount_buyget = array();
					$get_store_discount_buyget = $this->db->query("SELECT * FROM ".$this->prefix_pos."discount_buyget WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_discount_buyget->num_rows() > 0){
						
						foreach($get_store_discount_buyget->result() as $dt){
							
							$discount_buygets_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_discount_buyget)){
								$all_discount_buyget[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_discount_buyget_store;
					}
					
					//NEXT DATA
					if($last_id_discount_buyget_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_discount_buyget_store'] = $last_id_discount_buyget_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['discount_buygets_store'] = json_encode($discount_buygets_store);
					$post_data['all_discount_buyget'] = json_encode($all_discount_buyget);
					
					break;	
				
				//Discount Product
				case 'discount_product':
					$backup_text = 'Discount Menu/Product';
					
					//Discount Menu/Product ON STORE
					$last_id_discount_product_store = 0;
					$total_data_store = 0;
					$get_all_store_discount_product = $this->db->query("SELECT id FROM ".$this->prefix_pos."discount_product ORDER BY id DESC");
					if($get_all_store_discount_product->num_rows() > 0){
						$dt_all_discount_product_store = $get_all_store_discount_product->row();
						$last_id_discount_product_store = $dt_all_discount_product_store->id;
						$total_data_store = $get_all_store_discount_product->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_discount_product_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Discount Menu/Product ON STORE id > $last_id_on_backup
					$discount_products_store = array();
					$all_discount_product = array();
					$get_store_discount_product = $this->db->query("SELECT * FROM ".$this->prefix_pos."discount_product WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_discount_product->num_rows() > 0){
						
						foreach($get_store_discount_product->result() as $dt){
							
							$discount_products_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_discount_product)){
								$all_discount_product[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_discount_product_store;
					}
					
					//NEXT DATA
					if($last_id_discount_product_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_discount_product_store'] = $last_id_discount_product_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['discount_products_store'] = json_encode($discount_products_store);
					$post_data['all_discount_product'] = json_encode($all_discount_product);
					
					break;	
					
				//Discount Voucher
				case 'discount_voucher':
					$backup_text = 'Discount Voucher';
					
					//Discount Voucher ON STORE
					$last_id_discount_voucher_store = 0;
					$total_data_store = 0;
					$get_all_store_discount_voucher = $this->db->query("SELECT id FROM ".$this->prefix_pos."discount_voucher ORDER BY id DESC");
					if($get_all_store_discount_voucher->num_rows() > 0){
						$dt_all_discount_voucher_store = $get_all_store_discount_voucher->row();
						$last_id_discount_voucher_store = $dt_all_discount_voucher_store->id;
						$total_data_store = $get_all_store_discount_voucher->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_discount_voucher_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Discount Voucher ON STORE id > $last_id_on_backup
					$discount_vouchers_store = array();
					$all_discount_voucher = array();
					$get_store_discount_voucher = $this->db->query("SELECT * FROM ".$this->prefix_pos."discount_voucher WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_discount_voucher->num_rows() > 0){
						
						foreach($get_store_discount_voucher->result() as $dt){
							
							$discount_vouchers_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_discount_voucher)){
								$all_discount_voucher[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_discount_voucher_store;
					}
					
					//NEXT DATA
					if($last_id_discount_voucher_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_discount_voucher_store'] = $last_id_discount_voucher_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['discount_vouchers_store'] = json_encode($discount_vouchers_store);
					$post_data['all_discount_voucher'] = json_encode($all_discount_voucher);
					
					break;	
				
				//Sales/Marketing
				case 'sales_marketing':
					$backup_text = 'Sales/Marketing';
					
					//Sales/Marketing ON STORE
					$last_id_sales_marketing_store = 0;
					$total_data_store = 0;
					$get_all_store_sales_marketing = $this->db->query("SELECT id FROM ".$this->prefix_pos."sales ORDER BY id DESC");
					if($get_all_store_sales_marketing->num_rows() > 0){
						$dt_all_sales_marketing_store = $get_all_store_sales_marketing->row();
						$last_id_sales_marketing_store = $dt_all_sales_marketing_store->id;
						$total_data_store = $get_all_store_sales_marketing->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_sales_marketing_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Sales/Marketing ON STORE id > $last_id_on_backup
					$sales_marketings_store = array();
					$all_sales_marketing = array();
					$get_store_sales_marketing = $this->db->query("SELECT * FROM ".$this->prefix_pos."sales WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_sales_marketing->num_rows() > 0){
						
						foreach($get_store_sales_marketing->result() as $dt){
							
							$sales_marketings_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_sales_marketing)){
								$all_sales_marketing[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_sales_marketing_store;
					}
					
					//NEXT DATA
					if($last_id_sales_marketing_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_sales_marketing_store'] = $last_id_sales_marketing_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['sales_marketings_store'] = json_encode($sales_marketings_store);
					$post_data['all_sales_marketing'] = json_encode($all_sales_marketing);
					
					break;	
				
				//Customer/Member
				case 'customer_member':
					$backup_text = 'Customer/Member';
					
					//Customer/Member ON STORE
					$last_id_customer_member_store = 0;
					$total_data_store = 0;
					$get_all_store_customer_member = $this->db->query("SELECT id FROM ".$this->prefix_pos."customer ORDER BY id DESC");
					if($get_all_store_customer_member->num_rows() > 0){
						$dt_all_customer_member_store = $get_all_store_customer_member->row();
						$last_id_customer_member_store = $dt_all_customer_member_store->id;
						$total_data_store = $get_all_store_customer_member->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_customer_member_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Customer/Member ON STORE id > $last_id_on_backup
					$customer_members_store = array();
					$all_customer_member = array();
					$get_store_customer_member = $this->db->query("SELECT * FROM ".$this->prefix_pos."customer WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_customer_member->num_rows() > 0){
						
						foreach($get_store_customer_member->result() as $dt){
							
							$customer_members_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_customer_member)){
								$all_customer_member[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_customer_member_store;
					}
					
					//NEXT DATA
					if($last_id_customer_member_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_customer_member_store'] = $last_id_customer_member_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['customer_members_store'] = json_encode($customer_members_store);
					$post_data['all_customer_member'] = json_encode($all_customer_member);
					
					break;	
				
				//Divisi/Bagian
				case 'divisi':
					$backup_text = 'Divisi/Bagian';
					
					//Divisi/Bagian ON STORE
					$last_id_divisi_store = 0;
					$total_data_store = 0;
					$get_all_store_divisi = $this->db->query("SELECT id FROM ".$this->prefix_pos."divisi ORDER BY id DESC");
					if($get_all_store_divisi->num_rows() > 0){
						$dt_all_divisi_store = $get_all_store_divisi->row();
						$last_id_divisi_store = $dt_all_divisi_store->id;
						$total_data_store = $get_all_store_divisi->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_divisi_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Divisi/Bagian ON STORE id > $last_id_on_backup
					$divisis_store = array();
					$all_divisi = array();
					$get_store_divisi = $this->db->query("SELECT * FROM ".$this->prefix_pos."divisi WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_divisi->num_rows() > 0){
						
						foreach($get_store_divisi->result() as $dt){
							
							$divisis_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_divisi)){
								$all_divisi[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_divisi_store;
					}
					
					//NEXT DATA
					if($last_id_divisi_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_divisi_store'] = $last_id_divisi_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['divisis_store'] = json_encode($divisis_store);
					$post_data['all_divisi'] = json_encode($all_divisi);
					
					break;	
				
				//Warehouse/Gudang
				case 'warehouse':
					$backup_text = 'Warehouse/Gudang';
					
					//Warehouse/Gudang ON STORE
					$last_id_warehouse_store = 0;
					$total_data_store = 0;
					$get_all_store_warehouse = $this->db->query("SELECT id FROM ".$this->prefix_pos."storehouse ORDER BY id DESC");
					if($get_all_store_warehouse->num_rows() > 0){
						$dt_all_warehouse_store = $get_all_store_warehouse->row();
						$last_id_warehouse_store = $dt_all_warehouse_store->id;
						$total_data_store = $get_all_store_warehouse->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_warehouse_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Warehouse/Gudang ON STORE id > $last_id_on_backup
					$warehouses_store = array();
					$all_warehouse = array();
					$get_store_warehouse = $this->db->query("SELECT * FROM ".$this->prefix_pos."storehouse WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_warehouse->num_rows() > 0){
						
						foreach($get_store_warehouse->result() as $dt){
							
							$warehouses_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_warehouse)){
								$all_warehouse[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_warehouse_store;
					}
					
					//NEXT DATA
					if($last_id_warehouse_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_warehouse_store'] = $last_id_warehouse_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['warehouses_store'] = json_encode($warehouses_store);
					$post_data['all_warehouse'] = json_encode($all_warehouse);
					
					break;
					
				//Warehouse Access 
				case 'warehouse_access':
					$backup_text = 'Warehouse Access';
					
					//Warehouse Access ON STORE
					$last_id_warehouse_access_store = 0;
					$total_data_store = 0;
					$get_all_store_warehouse_access = $this->db->query("SELECT id FROM ".$this->prefix_pos."storehouse_users ORDER BY id DESC");
					if($get_all_store_warehouse_access->num_rows() > 0){
						$dt_all_warehouse_access_store = $get_all_store_warehouse_access->row();
						$last_id_warehouse_access_store = $dt_all_warehouse_access_store->id;
						$total_data_store = $get_all_store_warehouse_access->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_warehouse_access_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Warehouse Access ON STORE id > $last_id_on_backup
					$warehouse_accesss_store = array();
					$all_warehouse_access = array();
					$get_store_warehouse_access = $this->db->query("SELECT * FROM ".$this->prefix_pos."storehouse_users WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_warehouse_access->num_rows() > 0){
						
						foreach($get_store_warehouse_access->result() as $dt){
							
							$warehouse_accesss_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_warehouse_access)){
								$all_warehouse_access[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_warehouse_access_store;
					}
					
					//NEXT DATA
					if($last_id_warehouse_access_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_warehouse_access_store'] = $last_id_warehouse_access_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['warehouse_accesss_store'] = json_encode($warehouse_accesss_store);
					$post_data['all_warehouse_access'] = json_encode($all_warehouse_access);
					
					break;	
				
				//Unit
				case 'unit':
					$backup_text = 'Unit/Satuan';
					
					//Unit ON STORE
					$last_id_unit_store = 0;
					$total_data_store = 0;
					$get_all_store_unit = $this->db->query("SELECT id FROM ".$this->prefix_pos."unit ORDER BY id DESC");
					if($get_all_store_unit->num_rows() > 0){
						$dt_all_unit_store = $get_all_store_unit->row();
						$last_id_unit_store = $dt_all_unit_store->id;
						$total_data_store = $get_all_store_unit->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_unit_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Unit ON STORE id > $last_id_on_backup
					$units_store = array();
					$all_unit = array();
					$get_store_unit = $this->db->query("SELECT * FROM ".$this->prefix_pos."unit WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_unit->num_rows() > 0){
						
						foreach($get_store_unit->result() as $dt){
							
							$units_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_unit)){
								$all_unit[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_unit_store;
					}
					
					//NEXT DATA
					if($last_id_unit_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_unit_store'] = $last_id_unit_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['units_store'] = json_encode($units_store);
					$post_data['all_unit'] = json_encode($all_unit);
					
					break;
					
				//Item/Barang
				case 'items':
					$backup_text = 'Item/Barang';
					
					//Item/Barang ON STORE
					$last_id_items_store = 0;
					$total_data_store = 0;
					$get_all_store_items = $this->db->query("SELECT id FROM ".$this->prefix_pos."items ORDER BY id DESC");
					if($get_all_store_items->num_rows() > 0){
						$dt_all_items_store = $get_all_store_items->row();
						$last_id_items_store = $dt_all_items_store->id;
						$total_data_store = $get_all_store_items->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_items_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Item/Barang ON STORE id > $last_id_on_backup
					$items_store = array();
					$all_items = array();
					$get_store_items = $this->db->query("SELECT * FROM ".$this->prefix_pos."items WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_items->num_rows() > 0){
						
						foreach($get_store_items->result() as $dt){
							
							$items_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_items)){
								$all_items[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_items_store;
					}
					
					//NEXT DATA
					if($last_id_items_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_items_store'] = $last_id_items_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['items_store'] = json_encode($items_store);
					$post_data['all_items'] = json_encode($all_items);
					
					break;	
				
				//Item Category
				case 'item_category':
					$backup_text = 'Item Category';
					
					//Item Category ON STORE
					$last_id_item_category_store = 0;
					$total_data_store = 0;
					$get_all_store_item_category = $this->db->query("SELECT id FROM ".$this->prefix_pos."item_category ORDER BY id DESC");
					if($get_all_store_item_category->num_rows() > 0){
						$dt_all_item_category_store = $get_all_store_item_category->row();
						$last_id_item_category_store = $dt_all_item_category_store->id;
						$total_data_store = $get_all_store_item_category->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_item_category_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Item Category ON STORE id > $last_id_on_backup
					$item_categorys_store = array();
					$all_item_category = array();
					$get_store_item_category = $this->db->query("SELECT * FROM ".$this->prefix_pos."item_category WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_item_category->num_rows() > 0){
						
						foreach($get_store_item_category->result() as $dt){
							
							$item_categorys_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_item_category)){
								$all_item_category[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_item_category_store;
					}
					
					//NEXT DATA
					if($last_id_item_category_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_item_category_store'] = $last_id_item_category_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['item_categorys_store'] = json_encode($item_categorys_store);
					$post_data['all_item_category'] = json_encode($all_item_category);
					
					break;	
				
				//Item Sub Category
				case 'item_subcategory':
					$backup_text = 'Sub Category';
					
					//Item Sub Category ON STORE
					$last_id_item_subcategory_store = 0;
					$total_data_store = 0;
					$get_all_store_item_subcategory = $this->db->query("SELECT id FROM ".$this->prefix_pos."item_subcategory ORDER BY id DESC");
					if($get_all_store_item_subcategory->num_rows() > 0){
						$dt_all_item_subcategory_store = $get_all_store_item_subcategory->row();
						$last_id_item_subcategory_store = $dt_all_item_subcategory_store->id;
						$total_data_store = $get_all_store_item_subcategory->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_item_subcategory_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Item Sub Category ON STORE id > $last_id_on_backup
					$item_subcategorys_store = array();
					$all_item_subcategory = array();
					$get_store_item_subcategory = $this->db->query("SELECT * FROM ".$this->prefix_pos."item_subcategory WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_item_subcategory->num_rows() > 0){
						
						foreach($get_store_item_subcategory->result() as $dt){
							
							$item_subcategorys_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_item_subcategory)){
								$all_item_subcategory[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_item_subcategory_store;
					}
					
					//NEXT DATA
					if($last_id_item_subcategory_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_item_subcategory_store'] = $last_id_item_subcategory_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['item_subcategorys_store'] = json_encode($item_subcategorys_store);
					$post_data['all_item_subcategory'] = json_encode($all_item_subcategory);
					
					break;	
				
				//Item kode unik
				case 'item_kode_unik':
					$backup_text = 'Unique Code';
					
					//Item kode unik ON STORE
					$last_id_item_kode_unik_store = 0;
					$total_data_store = 0;
					$get_all_store_item_kode_unik = $this->db->query("SELECT id FROM ".$this->prefix_pos."item_kode_unik ORDER BY id DESC");
					if($get_all_store_item_kode_unik->num_rows() > 0){
						$dt_all_item_kode_unik_store = $get_all_store_item_kode_unik->row();
						$last_id_item_kode_unik_store = $dt_all_item_kode_unik_store->id;
						$total_data_store = $get_all_store_item_kode_unik->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_item_kode_unik_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Item kode unik ON STORE id > $last_id_on_backup
					$item_kode_uniks_store = array();
					$all_item_kode_unik = array();
					$get_store_item_kode_unik = $this->db->query("SELECT * FROM ".$this->prefix_pos."item_kode_unik WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_item_kode_unik->num_rows() > 0){
						
						foreach($get_store_item_kode_unik->result() as $dt){
							
							$item_kode_uniks_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_item_kode_unik)){
								$all_item_kode_unik[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_item_kode_unik_store;
					}
					
					//NEXT DATA
					if($last_id_item_kode_unik_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_item_kode_unik_store'] = $last_id_item_kode_unik_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['item_kode_uniks_store'] = json_encode($item_kode_uniks_store);
					$post_data['all_item_kode_unik'] = json_encode($all_item_kode_unik);
					
					break;	
				
				//Supplier
				case 'supplier':
					$backup_text = 'Supplier';
					
					//Supplier ON STORE
					$last_id_supplier_store = 0;
					$total_data_store = 0;
					$get_all_store_supplier = $this->db->query("SELECT id FROM ".$this->prefix_pos."supplier ORDER BY id DESC");
					if($get_all_store_supplier->num_rows() > 0){
						$dt_all_supplier_store = $get_all_store_supplier->row();
						$last_id_supplier_store = $dt_all_supplier_store->id;
						$total_data_store = $get_all_store_supplier->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_supplier_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Supplier ON STORE id > $last_id_on_backup
					$suppliers_store = array();
					$all_supplier = array();
					$get_store_supplier = $this->db->query("SELECT * FROM ".$this->prefix_pos."supplier WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_supplier->num_rows() > 0){
						
						foreach($get_store_supplier->result() as $dt){
							
							$suppliers_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_supplier)){
								$all_supplier[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_supplier_store;
					}
					
					//NEXT DATA
					if($last_id_supplier_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_supplier_store'] = $last_id_supplier_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['suppliers_store'] = json_encode($suppliers_store);
					$post_data['all_supplier'] = json_encode($all_supplier);
					
					break;
				
				//Supplier Item
				case 'supplier_item':
					$backup_text = 'Supplier Item';
					
					//Supplier Item ON STORE
					$last_id_supplier_item_store = 0;
					$total_data_store = 0;
					$get_all_store_supplier_item = $this->db->query("SELECT id FROM ".$this->prefix_pos."supplier_item ORDER BY id DESC");
					if($get_all_store_supplier_item->num_rows() > 0){
						$dt_all_supplier_item_store = $get_all_store_supplier_item->row();
						$last_id_supplier_item_store = $dt_all_supplier_item_store->id;
						$total_data_store = $get_all_store_supplier_item->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_supplier_item_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Supplier Item ON STORE id > $last_id_on_backup
					$supplier_items_store = array();
					$all_supplier_item = array();
					$get_store_supplier_item = $this->db->query("SELECT * FROM ".$this->prefix_pos."supplier_item WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_supplier_item->num_rows() > 0){
						
						foreach($get_store_supplier_item->result() as $dt){
							
							$supplier_items_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_supplier_item)){
								$all_supplier_item[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_supplier_item_store;
					}
					
					//NEXT DATA
					if($last_id_supplier_item_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_supplier_item_store'] = $last_id_supplier_item_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['supplier_items_store'] = json_encode($supplier_items_store);
					$post_data['all_supplier_item'] = json_encode($all_supplier_item);
					
					break;
				
				//Order Note
				case 'order_note':
					$backup_text = 'Order Note';
					
					//Order Note ON STORE
					$last_id_order_note_store = 0;
					$total_data_store = 0;
					$get_all_store_order_note = $this->db->query("SELECT id FROM ".$this->prefix_pos."order_note ORDER BY id DESC");
					if($get_all_store_order_note->num_rows() > 0){
						$dt_all_order_note_store = $get_all_store_order_note->row();
						$last_id_order_note_store = $dt_all_order_note_store->id;
						$total_data_store = $get_all_store_order_note->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_order_note_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Order Note ON STORE id > $last_id_on_backup
					$order_notes_store = array();
					$all_order_note = array();
					$get_store_order_note = $this->db->query("SELECT * FROM ".$this->prefix_pos."order_note WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_order_note->num_rows() > 0){
						
						foreach($get_store_order_note->result() as $dt){
							
							$order_notes_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_order_note)){
								$all_order_note[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_order_note_store;
					}
					
					//NEXT DATA
					if($last_id_order_note_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_order_note_store'] = $last_id_order_note_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['order_notes_store'] = json_encode($order_notes_store);
					$post_data['all_order_note'] = json_encode($all_order_note);
					
					break;
				
				//Billing Tipe
				case 'billing_tipe':
					$backup_text = 'Billing Tipe';
					
					//Order Note ON STORE
					$last_id_billing_tipe_store = 0;
					$total_data_store = 0;
					$get_all_store_billing_tipe = $this->db->query("SELECT id FROM ".$this->prefix_pos."table ORDER BY id DESC");
					if($get_all_store_billing_tipe->num_rows() > 0){
						$dt_all_billing_tipe_store = $get_all_store_billing_tipe->row();
						$last_id_billing_tipe_store = $dt_all_billing_tipe_store->id;
						$total_data_store = $get_all_store_billing_tipe->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_billing_tipe_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Order Note ON STORE id > $last_id_on_backup
					$billing_tipes_store = array();
					$all_billing_tipe = array();
					$get_store_billing_tipe = $this->db->query("SELECT * FROM ".$this->prefix_pos."table WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_billing_tipe->num_rows() > 0){
						
						foreach($get_store_billing_tipe->result() as $dt){
							
							$billing_tipes_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_billing_tipe)){
								$all_billing_tipe[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_billing_tipe_store;
					}
					
					//NEXT DATA
					if($last_id_billing_tipe_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_billing_tipe_store'] = $last_id_billing_tipe_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['billing_tipes_store'] = json_encode($billing_tipes_store);
					$post_data['all_billing_tipe'] = json_encode($all_billing_tipe);
					
					break;
				
				//Table
				case 'table':
					$backup_text = 'Table';
					
					//Table ON STORE
					$last_id_table_store = 0;
					$total_data_store = 0;
					$get_all_store_table = $this->db->query("SELECT id FROM ".$this->prefix_pos."table ORDER BY id DESC");
					if($get_all_store_table->num_rows() > 0){
						$dt_all_table_store = $get_all_store_table->row();
						$last_id_table_store = $dt_all_table_store->id;
						$total_data_store = $get_all_store_table->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_table_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Table ON STORE id > $last_id_on_backup
					$tables_store = array();
					$all_table = array();
					$get_store_table = $this->db->query("SELECT * FROM ".$this->prefix_pos."table WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_table->num_rows() > 0){
						
						foreach($get_store_table->result() as $dt){
							
							$tables_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_table)){
								$all_table[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_table_store;
					}
					
					//NEXT DATA
					if($last_id_table_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_table_store'] = $last_id_table_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['tables_store'] = json_encode($tables_store);
					$post_data['all_table'] = json_encode($all_table);
					
					break;
				
				//Table Inventory
				case 'table_inventory':
					$backup_text = 'Table Inventory';
					
					//Table Inventory ON STORE
					$last_id_table_inventory_store = 0;
					$total_data_store = 0;
					$get_all_store_table_inventory = $this->db->query("SELECT id FROM ".$this->prefix_pos."table_inventory ORDER BY id DESC");
					if($get_all_store_table_inventory->num_rows() > 0){
						$dt_all_table_inventory_store = $get_all_store_table_inventory->row();
						$last_id_table_inventory_store = $dt_all_table_inventory_store->id;
						$total_data_store = $get_all_store_table_inventory->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_table_inventory_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Table Inventory ON STORE id > $last_id_on_backup
					$table_inventorys_store = array();
					$all_table_inventory = array();
					$get_store_table_inventory = $this->db->query("SELECT * FROM ".$this->prefix_pos."table_inventory WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_table_inventory->num_rows() > 0){
						
						foreach($get_store_table_inventory->result() as $dt){
							
							$table_inventorys_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_table_inventory)){
								$all_table_inventory[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_table_inventory_store;
					}
					
					//NEXT DATA
					if($last_id_table_inventory_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_table_inventory_store'] = $last_id_table_inventory_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['table_inventorys_store'] = json_encode($table_inventorys_store);
					$post_data['all_table_inventory'] = json_encode($all_table_inventory);
					
					break;
					
				//Floorplan
				case 'floorplan':
					$backup_text = 'Floorplan';
					
					//Floorplan ON STORE
					$last_id_floorplan_store = 0;
					$total_data_store = 0;
					$get_all_store_floorplan = $this->db->query("SELECT id FROM ".$this->prefix_pos."floorplan ORDER BY id DESC");
					if($get_all_store_floorplan->num_rows() > 0){
						$dt_all_floorplan_store = $get_all_store_floorplan->row();
						$last_id_floorplan_store = $dt_all_floorplan_store->id;
						$total_data_store = $get_all_store_floorplan->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_floorplan_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Floorplan ON STORE id > $last_id_on_backup
					$floorplans_store = array();
					$all_floorplan = array();
					$get_store_floorplan = $this->db->query("SELECT * FROM ".$this->prefix_pos."floorplan WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_floorplan->num_rows() > 0){
						
						foreach($get_store_floorplan->result() as $dt){
							
							$floorplans_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_floorplan)){
								$all_floorplan[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_floorplan_store;
					}
					
					//NEXT DATA
					if($last_id_floorplan_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_floorplan_store'] = $last_id_floorplan_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['floorplans_store'] = json_encode($floorplans_store);
					$post_data['all_floorplan'] = json_encode($all_floorplan);
					
					break;
				
				//Room
				case 'room':
					$backup_text = 'Room';
					
					//Order Note ON STORE
					$last_id_room_store = 0;
					$total_data_store = 0;
					$get_all_store_room = $this->db->query("SELECT id FROM ".$this->prefix_pos."room ORDER BY id DESC");
					if($get_all_store_room->num_rows() > 0){
						$dt_all_room_store = $get_all_store_room->row();
						$last_id_room_store = $dt_all_room_store->id;
						$total_data_store = $get_all_store_room->num_rows();
					}
					
					$last_id_store = 0;
					$total_data_store_detail = 0;
					$last_id_store_detail = 0;
					
					if($last_id_room_store == $last_id_on_backup){
						$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
						die(json_encode($r));
					}
					
					//Order Note ON STORE id > $last_id_on_backup
					$rooms_store = array();
					$all_room = array();
					$get_store_room = $this->db->query("SELECT * FROM ".$this->prefix_pos."room WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
					if($get_store_room->num_rows() > 0){
						
						foreach($get_store_room->result() as $dt){
							
							$rooms_store[] = (array) $dt;
							
							if(!in_array($dt->id, $all_room)){
								$all_room[] = $dt->id;
							}
							
							$last_id_store = $dt->id;
						}
					}
					
					if(empty($last_id_store)){
						$last_id_store = $last_id_room_store;
					}
					
					//NEXT DATA
					if($last_id_room_store > $last_id_store){
						$has_next = 1;
					}
					
					$post_data['last_id_room_store'] = $last_id_room_store;
					$post_data['total_data_store'] = $total_data_store;
					$post_data['last_id_store'] = $last_id_store;
					$post_data['rooms_store'] = json_encode($rooms_store);
					$post_data['all_room'] = json_encode($all_room);
					
					break;
				
					
			}
			
		}
		
		//UPLOAD		
		$this->curl->create($client_url);
		$this->curl->option('connecttimeout', 600);
		$this->curl->option('RETURNTRANSFER', 1);
		$this->curl->option('SSL_VERIFYPEER', 1);
		$this->curl->option('SSL_VERIFYHOST', 2);
		//$this->curl->option('SSLVERSION', 3);
		$this->curl->option('POST', 1);
		$this->curl->option('POSTFIELDS', $post_data);
		$this->curl->option('CAINFO', $crt_file);
		$curl_ret = $this->curl->execute();
		
		//$curl_ret = $this->curl->simple_post($client_url, $post_data);
		$return_data = json_decode($curl_ret, true);
		
		$backup_status = false;
		if(empty($return_data)){
			
			if($is_syncbackup == 'sync'){
				$r = array('success' => false, 'info' => 'Sync Master Data: '.ucwords(str_replace("_"," ",$curr_backup_data)).' Gagal!', 'has_next' => 0);
			}else{
				$r = array('success' => false, 'info' => 'Backup Master Data: '.$backup_text.' Gagal!', 'has_next' => 0);
			}
			
			die(json_encode($r));
		}else{
			
			if(!empty($return_data['last_id_on_backup'])){
				$last_id_on_backup = $return_data['last_id_on_backup'];
			}
			
			if(!empty($return_data['total_data_on_backup'])){
				$total_data_on_backup = $return_data['total_data_on_backup'];
			}
			
			$backup_status = true;
			
			if($use_wms == 1){
				
				if(!empty($return_data['new_data_store'])){
					$new_data_store = $return_data['new_data_store'];
				}
				
				//---------------------------------SYNC
				if($return_data['success'] == false){
					
					$r = array(
						'success' => false, 
						'info'	=> $return_data['info'],
						'has_next' => 0
					);
					die(json_encode($r));
					
				}else{
					
					$total_data_sync = 0;
					$last_id_sync = 0;
					$sync_text = '';
					$sync_status = false;
					
					switch($curr_backup_data){
						
						//Roles
						case 'roles':
							$sync_text = 'Roles';
							
							//MODULES
							//roles
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
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
							
							break;
							
						//Roles Module
						case 'roles_module':
							$sync_text = 'Roles Module';
							
							//MODULES
							//roles_module
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['roles_module'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix."roles_module");
								
								//BATCH
								$this->db->insert_batch($this->prefix."roles_module", $new_data_store['roles_module']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix."roles_module");
									$sync_status = true;
								}
							}
						
							break;
							
						//User
						case 'data_user':
							$sync_text = 'User';
							
							//MODULES
							//data_user
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['data_user'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix."users");
								
								//BATCH
								$this->db->insert_batch($this->prefix."users", $new_data_store['data_user']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix."users");
									$sync_status = true;
								}
							}
						
							break;
						
						//Users Desktop
						case 'users_desktop':
							$sync_text = 'Users Desktop';
							
							//MODULES
							//users_desktop
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['users_desktop'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix."users_desktop");
								
								//BATCH
								$this->db->insert_batch($this->prefix."users_desktop", $new_data_store['users_desktop']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix."users_desktop");
									$sync_status = true;
								}
							}
						
							break;
						
						//Users Shortcut
						case 'users_shortcut':
							$sync_text = 'Users Shortcut';
							
							//MODULES
							//users_shortcut
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['users_shortcut'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix."users_shortcut");
								
								//BATCH
								$this->db->insert_batch($this->prefix."users_shortcut", $new_data_store['users_shortcut']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix."users_shortcut");
									$sync_status = true;
								}
							}
						
							break;
						
						//Users Quickstart
						case 'users_quickstart':
							$sync_text = 'Users Quickstart';
							
							//MODULES
							//users_quickstart
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['users_quickstart'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix."users_quickstart");
								
								//BATCH
								$this->db->insert_batch($this->prefix."users_quickstart", $new_data_store['users_quickstart']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix."users_quickstart");
									$sync_status = true;
								}
							}
						
							break;
						
						//Supervisor
						case 'supervisor':
							$sync_text = 'Supervisor';
							
							//MODULES
							//supervisor
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['supervisor'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix."supervisor");
								
								//BATCH
								$this->db->insert_batch($this->prefix."supervisor", $new_data_store['supervisor']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix."supervisor");
									$sync_status = true;
								}
							}
						
							break;
						
						//Supervisor Access
						case 'supervisor_access':
							$sync_text = 'Supervisor Access';
							
							//MODULES
							//supervisor_access
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
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
						
							break;
						
						//Varian
						case 'varian':
							$sync_text = 'Varian';
							
							//MODULES
							//varian
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['varian'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."varian");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."varian", $new_data_store['varian']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."varian");
									$sync_status = true;
								}
							}
						
							break;
						
						//Menu
						case 'menu':
							$sync_text = 'Menu';
							
							//MODULES
							//menu
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['menu'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."product");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."product", $new_data_store['menu']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."product");
									$sync_status = true;
								}
							}
						
							break;
						
						//Menu Category
						case 'menu_category':
							$sync_text = 'Menu Category';
							
							//MODULES
							//menu_category
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['menu_category'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."product_category");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."product_category", $new_data_store['menu_category']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."product_category");
									$sync_status = true;
								}
							}
						
							break;
						
						//Menu Package
						case 'menu_package':
							$sync_text = 'Menu Package';
							
							//MODULES
							//menu_package
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['menu_package'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."product_package");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."product_package", $new_data_store['menu_package']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."product_package");
									$sync_status = true;
								}
							}
						
							break;
						
						//Menu Varian
						case 'menu_varian':
							$sync_text = 'Menu Varian';
							
							//MODULES
							//menu_varian
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['menu_varian'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."product_varian");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."product_varian", $new_data_store['menu_varian']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."product_varian");
									$sync_status = true;
								}
							}
						
							break;
						
						//Payment Bank
						case 'payment_bank':
							$sync_text = 'Payment Bank';
							
							//MODULES
							//payment_bank
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['payment_bank'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."bank");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."bank", $new_data_store['payment_bank']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."bank");
									$sync_status = true;
								}
							}
						
							break;
						
						//Discount
						case 'discount':
							$sync_text = 'Discount';
							
							//MODULES
							//discount
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
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
						
							break;
						
						//Discount Buy & Get
						case 'discount_buyget':
							$sync_text = 'Discount Buy & Get';
							
							//MODULES
							//discount_buyget
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['discount_buyget'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."discount_buyget");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."discount_buyget", $new_data_store['discount_buyget']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."discount_buyget");
									$sync_status = true;
								}
							}
						
							break;
						
						//Discount Product
						case 'discount_product':
							$sync_text = 'Discount Product';
							
							//MODULES
							//discount_product
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['discount_product'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."discount_product");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."discount_product", $new_data_store['discount_product']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."discount_product");
									$sync_status = true;
								}
							}
						
							break;
						
						//Discount Voucher
						case 'discount_voucher':
							$sync_text = 'Discount Voucher';
							
							//MODULES
							//discount_voucher
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['discount_voucher'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."discount_voucher");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."discount_voucher", $new_data_store['discount_voucher']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."discount_voucher");
									$sync_status = true;
								}
							}
						
							break;
						
						//Sales Marketing
						case 'sales_marketing':
							$sync_text = 'Sales Marketing';
							
							//MODULES
							//sales_marketing
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['sales_marketing'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."sales");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."sales", $new_data_store['sales_marketing']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."sales");
									$sync_status = true;
								}
							}
						
							break;
						
						//Customer Member
						case 'customer_member':
							$sync_text = 'Customer Member';
							
							//MODULES
							//customer_member
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['customer_member'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."customer");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."customer", $new_data_store['customer_member']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."customer");
									$sync_status = true;
								}
							}
						
							break;
						
						//Divisi
						case 'divisi':
							$sync_text = 'Divisi';
							
							//MODULES
							//divisi
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['divisi'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."divisi");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."divisi", $new_data_store['divisi']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."divisi");
									$sync_status = true;
								}
							}
						
							break;
						
						//Warehouse
						case 'warehouse':
							$sync_text = 'Warehouse/Gudang';
							
							//MODULES
							//warehouse
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['warehouse'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."storehouse");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."storehouse", $new_data_store['warehouse']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."storehouse");
									$sync_status = true;
								}
							}
						
							break;
						
						//Warehouse Access
						case 'warehouse_access':
							$sync_text = 'Warehouse Access';
							
							//MODULES
							//warehouse_access
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['warehouse_access'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."storehouse_users");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."storehouse_users", $new_data_store['warehouse_access']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."storehouse_users");
									$sync_status = true;
								}
							}
						
							break;
						
						//Unit
						case 'unit':
							$sync_text = 'Unit';
							
							//MODULES
							//unit
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['unit'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."unit");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."unit", $new_data_store['unit']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."unit");
									$sync_status = true;
								}
							}
						
							break;
						
						//items
						case 'items':
							$sync_text = 'Items';
							
							//MODULES
							//items
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['items'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."items");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."items", $new_data_store['items']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."items");
									$sync_status = true;
								}
							}
						
							break;
						
						//Item Category
						case 'item_category':
							$sync_text = 'Item Category';
							
							//MODULES
							//item_category
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['item_category'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."item_category");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."item_category", $new_data_store['item_category']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."item_category");
									$sync_status = true;
								}
							}
						
							break;
						
						//Item Sub Category
						case 'item_subcategory':
							$sync_text = 'Item Sub Category';
							
							//MODULES
							//item_subcategory
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['item_subcategory'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."item_subcategory");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."item_subcategory", $new_data_store['item_subcategory']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."item_subcategory");
									$sync_status = true;
								}
							}
						
							break;
						
						//Item Kode Unik
						case 'item_kode_unik':
							$sync_text = 'Item Kode Unik';
							
							//MODULES
							//item_kode_unik
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['item_kode_unik'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."item_kode_unik");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."item_kode_unik", $new_data_store['item_kode_unik']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."item_kode_unik");
									$sync_status = true;
								}
							}
						
							break;
						
						//Supplier
						case 'supplier':
							$sync_text = 'Supplier';
							
							//MODULES
							//supplier
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['supplier'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."supplier");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."supplier", $new_data_store['supplier']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."supplier");
									$sync_status = true;
								}
							}
						
							break;
						
						//Supplier Item
						case 'supplier_item':
							$sync_text = 'Supplier Item';
							
							//MODULES
							//supplier_item
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['supplier_item'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."supplier_item");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."supplier_item", $new_data_store['supplier_item']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."supplier_item");
									$sync_status = true;
								}
							}
						
							break;
						
						//Order Note
						case 'order_note':
							$sync_text = 'Order Note';
							
							//MODULES
							//order_note
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['order_note'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."order_note");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."order_note", $new_data_store['order_note']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."order_note");
									$sync_status = true;
								}
							}
						
							break;
						
						//Table
						case 'table':
							$sync_text = 'Table';
							
							//MODULES
							//table
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['table'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."table");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."table", $new_data_store['table']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."table");
									$sync_status = true;
								}
							}
							
						//Table Inventory
						case 'table_inventory':
							$sync_text = 'Table Inventory';
							
							//MODULES
							//table_inventory
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['table_inventory'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."table_inventory");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."table_inventory", $new_data_store['table_inventory']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."table_inventory");
									$sync_status = true;
								}
							}
							
							break;
						
						//Floorplan
						case 'floorplan':
							$sync_text = 'Floorplan';
							
							//MODULES
							//floorplan
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['floorplan'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."floorplan");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."floorplan", $new_data_store['floorplan']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."floorplan");
									$sync_status = true;
								}
							}
							
							break;
						
						//Room
						case 'room':
							$sync_text = 'Room';
							
							//MODULES
							//room
							$last_id_sync = $last_id_on_backup;
							$total_data_sync = $total_data_on_backup;
							
							if(!empty($new_data_store['room'])){
								//TRUNCATE STORE
								$this->db->query("TRUNCATE ".$this->prefix_pos."room");
								
								//BATCH
								$this->db->insert_batch($this->prefix_pos."room", $new_data_store['room']);

								$sync_status = true;
								
							}else{
								if($last_id_sync == 0 AND $total_data_sync == 0){
									//TRUNCATE STORE
									$this->db->query("TRUNCATE ".$this->prefix_pos."room");
									$sync_status = true;
								}
							}
							
							break;
						
					}
					
					
					$r = array(
						'success' => true, 
						'info'	=> 'Syncronize Data: <b>'.$sync_text.'</b> Selesai..',
						'has_next' => 0,
						'last_id_on_backup' => $last_id_on_backup,
						'total_data_on_backup' => $total_data_on_backup,
						'last_id_store' => $last_id_sync,
						'total_data_store' => $total_data_sync,
						'sync_status' => $sync_status,
					);
					die(json_encode($r));
					
				}
			
			}else{
				
				//---------------------------------BACKUP
				if($return_data['success'] == false){
				
					$r = array(
						'success' => false, 
						'info'	=> $return_data['info'], 
						'has_next' => $has_next,
						'last_id_on_backup' => $last_id_on_backup,
						'total_data_on_backup' => $total_data_on_backup,
						'total_data_store' => $total_data_store,
						'last_id_store' => $last_id_store,
						'data' => $return_data['data'],
					);
					die(json_encode($r));
					
				}
			}
			
		}
		
		
		if($backup_status){
			
			$r = array(
				'success' => true, 
				'info'	=> 'Backup Data <b>'.$backup_text.'</b>: '.$total_data_store.' Data - #'.$last_id_store.'..', 
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
				'info'	=> 'Backup Data: <b>'.$backup_text.'</b> - Updated..',
				'has_next' => 0,
				'last_id_on_backup' => $last_id_on_backup,
				'total_data_on_backup' => $total_data_on_backup,
				'total_data_store' => $total_data_store,
				'last_id_store' => $last_id_store
			);
			die(json_encode($r));
			
		}
				
	}
	
	public function syncDataLog()
	{
		
		//OPT-OPTIONS
		$opt_val = array(
			'store_connected_id','management_systems','ipserver_management_systems','store_connected_code',
			'store_connected_name','store_connected_email','use_wms','as_server_backup'
		);
		
		$get_opt = get_option_value($opt_val);
		if(empty($get_opt['store_connected_id'])){
			$get_opt['store_connected_id'] = 0;
		}
		if(empty($get_opt['store_connected_code'])){
			$get_opt['store_connected_code'] = 0;
		}
		if(empty($get_opt['store_connected_name'])){
			$get_opt['store_connected_name'] = 0;
		}
		if(empty($get_opt['store_connected_email'])){
			$get_opt['store_connected_email'] = 0;
		}
		if(empty($get_opt['use_wms'])){
			$get_opt['use_wms'] = 0;
		}
		if(empty($get_opt['management_systems'])){
			$r = array('success' => false, 'info' => 'Cek Setup Aplikasi, aktifkan pilihan: <b>Backup Data ke Server</b>');
			die(json_encode($r));
		}
		
		if(empty($get_opt['ipserver_management_systems'])){
			$r = array('success' => false, 'info' => 'Cek Setup Aplikasi, IP server belum disesuaikan');
			die(json_encode($r));
		}
		if(!empty($get_opt['as_server_backup'])){
			$r = array('success' => false, 'info' => 'Aplikasi WePOS ini di set sebagai Server Backup!');
			die(json_encode($r));
		}
		
		$store_connected_id = $get_opt['store_connected_id'];
		$store_connected_code = $get_opt['store_connected_code'];
		$store_connected_name = $get_opt['store_connected_name'];
		$store_connected_email = $get_opt['store_connected_email'];
		$management_systems = $get_opt['management_systems'];
		$ipserver_management_systems = $get_opt['ipserver_management_systems'];
		$use_wms = $get_opt['use_wms'];
		
		//CONNECTED TO STORE MANAGEMENT - CURL
		$this->load->library('curl');
		
		$mktime_dc = strtotime(date("d-m-Y H:i:s"));
		
		$ipserver_management_systems = prep_url($ipserver_management_systems);
		
		if($use_wms == 1){
			
			$client_url = $ipserver_management_systems.'/sync_backup/backupTrx/backupTrxLog?_dc='.$mktime_dc;
			
			$crt_file = ASSETS_PATH.config_item('wms_crt_file');
			
		}else{
			
			//wepos.id
			$client_url = config_item('website').'/merchant/backupLog?_dc='.$mktime_dc;
			
			$crt_file = ASSETS_PATH.config_item('wepos_crt_file');
			
		}
		
		$client_id = $this->input->post('client_id');
		$client_code = $this->input->post('client_code');
		$client_name = $this->input->post('client_name');
		$client_email = $this->input->post('client_email');
		$limit = $this->input->post('limit');
		$page = $this->input->post('page');
		$start = $this->input->post('start');
		
		$post_data = array(
			'client_id' => $client_id,
			'client_code' => $client_code,
			'client_name' => $client_name,
			'client_email' => $client_email,
			'backup_masterdata' => 1,
			'limit' => $limit,
			'page' => $page,
			'start' => $start
		);
		
		
		$this->curl->create($client_url);
		$this->curl->option('connecttimeout', 600);
		$this->curl->option('RETURNTRANSFER', 1);
		$this->curl->option('SSL_VERIFYPEER', 1);
		$this->curl->option('SSL_VERIFYHOST', 2);
		//$this->curl->option('SSLVERSION', 3);
		$this->curl->option('POST', 1);
		$this->curl->option('POSTFIELDS', $post_data);
		$this->curl->option('CAINFO', $crt_file);
		$curl_ret = $this->curl->execute();
		
		//$curl_ret = $this->curl->simple_post($client_url, $post_data);
		$return_data = json_decode($curl_ret, true);
		  	
  		$get_data = array('data' => array(), 'totalCount' => 0);
		
		if(!empty($return_data['data'])){
			$get_data['data'] = $return_data['data'];
			$get_data['totalCount'] = $return_data['totalCount'];
		}
		
		
      	die(json_encode($return_data));
	}
}