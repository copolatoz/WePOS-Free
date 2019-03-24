<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class BackupTrx extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix');
		$this->prefix_pos = config_item('db_prefix2');
		$this->prefix_acc = config_item('db_prefix3');
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
		
		cek_server_backup($get_opt);
		
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
			$r = array('success' => false, 'info' => 'Data Store/Client: <b>'.$data_client['client_code'].' &mdash; '.$data_client['client_name'].'</b> Tidak teridentifikasi di Server!');
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
				'Keterangan'	=> 'Curl via Merchant',
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
			'totalCount' => count($all_data_detail)
		);
		
		
		die(json_encode($r));
	}
	
	public function backupDetail()
	{
		
		$client_id = $this->input->post('client_id');
		$client_code = $this->input->post('client_code');
		$client_name = $this->input->post('client_name');
		$client_email = $this->input->post('client_email');
		$only_backup = $this->input->post('only_backup');
		
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
		
		//CONNECTED TO STORE MANAGEMENT - CURL
		$this->load->library('curl');
		
		$mktime_dc = strtotime(date("d-m-Y H:i:s"));
		
		$ipserver_management_systems = prep_url($ipserver_management_systems);
		
		if($use_wms == 1){
			
			$client_url = $ipserver_management_systems.'/sync_backup/backupTrx/backupDetail?_dc='.$mktime_dc;
			
			$crt_file = ASSETS_PATH.config_item('wms_crt_file');
			
		}else{
			
			//wepos.id
			$client_url = config_item('website').'/merchant/backupDetail?_dc='.$mktime_dc;
			
			$crt_file = ASSETS_PATH.config_item('wepos_crt_file');
			
		}
		
		$post_data = array(
			'client_id' => $client_id,
			'client_code' => $client_code,
			'client_name' => $client_name,
			'client_email' => $client_email,
			'only_backup' => $only_backup,
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
		
		if(!empty($only_backup)){
			if($only_backup == 'sales'){
				$only_backup = array('sales','billing','billing_detail','billing_log');
			}
		}
		
		$total_data_lokal = array(
			'sales' => 0,
			'billing' => 0,
			'billing_detail' => 0,
			'billing_log' => 0,
			'salesorder' => 0,
			'salesorder_detail' => 0,
			'purchasing' => 0,
			'ro' => 0,
			'ro_detail' => 0,
			'po' => 0,
			'po_detail' => 0,
			'receiving' => 0,
			'receive_detail' => 0,
			'stock' => 0,
			'stock_koreksi' => 0,
			'stock_opname' => 0,
			'stock_opname_detail' => 0,
			'stock_rekap' => 0,
			'inventory' => 0,
			'usagewaste' => 0,
			'usagewaste_detail' => 0,
			'distribution' => 0,
			'distribution_detail' => 0,
			'production' => 0,
			'production_detail' => 0,
			'retur' => 0,
			'retur_detail' => 0,
			'closing' => 0,
			'closing_sales' => 0,
			'closing_purchasing' => 0,
			'closing_inventory' => 0,
			'closing_log' => 0,
			'cashflow' => 0,
			'penerimaan_kas' => 0,
			'pengeluaran_kas' => 0,
			'mutasi_kas_bank' => 0,
			'account_payable' => 0,
			'kontrabon' => 0,
			'kontrabon_detail' => 0,
			'pelunasan_ap' => 0,
			'account_receivable' => 0,
			'invoice' => 0,
			'invoice_detail' => 0,
			'pembayaran_ar' => 0,
		);
		
		if($wepos_tipe == 'cafe'){
			unset($total_data_lokal['salesorder']);
			unset($total_data_lokal['salesorder_detail']);
			$total_data_lokal['reservation'] = 0;
			$total_data_lokal['reservation_detail'] = 0;
		}
		
		$last_id_lokal = array(
			'sales' => 0,
			'billing' => 0,
			'billing_detail' => 0,
			'billing_log' => 0,
			'salesorder' => 0,
			'salesorder_detail' => 0,
			'purchasing' => 0,
			'ro' => 0,
			'ro_detail' => 0,
			'po' => 0,
			'po_detail' => 0,
			'receiving' => 0,
			'receive_detail' => 0,
			'stock' => 0,
			'stock_koreksi' => 0,
			'stock_opname' => 0,
			'stock_opname_detail' => 0,
			'stock_rekap' => 0,
			'inventory' => 0,
			'usagewaste' => 0,
			'usagewaste_detail' => 0,
			'distribution' => 0,
			'distribution_detail' => 0,
			'production' => 0,
			'production_detail' => 0,
			'retur' => 0,
			'retur_detail' => 0,
			'closing' => 0,
			'closing_sales' => 0,
			'closing_purchasing' => 0,
			'closing_inventory' => 0,
			'closing_log' => 0,
			'cashflow' => 0,
			'penerimaan_kas' => 0,
			'pengeluaran_kas' => 0,
			'mutasi_kas_bank' => 0,
			'account_payable' => 0,
			'kontrabon' => 0,
			'kontrabon_detail' => 0,
			'pelunasan_ap' => 0,
			'account_receivable' => 0,
			'invoice' => 0,
			'invoice_detail' => 0,
			'pembayaran_ar' => 0,
		);
		
		if($wepos_tipe == 'cafe'){
			unset($last_id_lokal['salesorder']);
			unset($last_id_lokal['salesorder_detail']);
			$last_id_lokal['reservation'] = 0;
			$last_id_lokal['reservation_detail'] = 0;
		}
		
		$today_mk = strtotime(date("d-m-Y 06:00:00"));
		$today_date = date("Y-m-d H:i:s", $today_mk);
		$today_date_plus1_mk = $today_mk+ONE_DAY_UNIX;
		$today_date_plus1 = date("Y-m-d H:i:s", $today_date_plus1_mk);
		
		//SALES --------------------------
		$last_id_lokal["sales"] = 0;
		$total_data_lokal["sales"] = 0;
		
		//SALES - BILLING
		$get_store_sales = $this->db->query("SELECT id FROM ".$this->prefix_pos."billing ORDER BY id DESC");
		if($only_backup == 'sales'){
			$get_store_sales = $this->db->query("SELECT id FROM ".$this->prefix_pos."billing WHERE created <= '".$today_date_plus1."' ORDER BY id DESC");
		}
		
		if($get_store_sales->num_rows() > 0){
			$dt_store_sales = $get_store_sales->row();
			$last_id_lokal["billing"] = $dt_store_sales->id;
			$total_data_lokal["billing"] = $get_store_sales->num_rows();
		}
		
		//BILLING DETAIL
		$get_store_billing_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."billing_detail ORDER BY id DESC");
		if($only_backup == 'sales'){
			$get_store_billing_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."billing_detail WHERE created <= '".$today_date_plus1."' ORDER BY id DESC");
		}
		
		if($get_store_billing_detail->num_rows() > 0){
			$dt_store_billing_detail = $get_store_billing_detail->row();
			$last_id_lokal["billing_detail"] = $dt_store_billing_detail->id;
			$total_data_lokal["billing_detail"] = $get_store_billing_detail->num_rows();
		}
		
		//BILLING LOG
		$get_store_billing_log = $this->db->query("SELECT id FROM ".$this->prefix_pos."billing_log ORDER BY id DESC");
		if($only_backup == 'sales'){
			$get_store_billing_log = $this->db->query("SELECT id FROM ".$this->prefix_pos."billing_log WHERE created <= '".$today_date_plus1."' ORDER BY id DESC");
		}
		
		if($get_store_billing_log->num_rows() > 0){
			$dt_store_billing_log = $get_store_billing_log->row();
			$last_id_lokal["billing_log"] = $dt_store_billing_log->id;
			$total_data_lokal["billing_log"] = $get_store_billing_log->num_rows();
		}
		
		if($wepos_tipe == 'cafe'){
			
			//RESERVATION
			$get_store_reservation = $this->db->query("SELECT id FROM ".$this->prefix_pos."reservation ORDER BY id DESC");
			if($get_store_reservation->num_rows() > 0){
				$dt_store_reservation = $get_store_reservation->row();
				$last_id_lokal["reservation"] = $dt_store_reservation->id;
				$total_data_lokal["reservation"] = $get_store_reservation->num_rows();
			}
			
			//RESERVATION - DETAIL
			$get_store_reservation_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."reservation_detail ORDER BY id DESC");
			if($get_store_reservation_detail->num_rows() > 0){
				$dt_store_reservation_detail = $get_store_reservation_detail->row();
				$last_id_lokal["reservation_detail"] = $dt_store_reservation_detail->id;
				$total_data_lokal["reservation_detail"] = $get_store_reservation_detail->num_rows();
			}
			
		}else{
			
			//SALES ORDER
			$get_store_salesorder = $this->db->query("SELECT id FROM ".$this->prefix_pos."salesorder ORDER BY id DESC");
			if($get_store_salesorder->num_rows() > 0){
				$dt_store_salesorder = $get_store_salesorder->row();
				$last_id_lokal["salesorder"] = $dt_store_salesorder->id;
				$total_data_lokal["salesorder"] = $get_store_salesorder->num_rows();
			}
			
			//SALES ORDER - DETAIL
			$get_store_salesorder_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."salesorder_detail ORDER BY id DESC");
			if($get_store_salesorder_detail->num_rows() > 0){
				$dt_store_salesorder_detail = $get_store_salesorder_detail->row();
				$last_id_lokal["salesorder_detail"] = $dt_store_salesorder_detail->id;
				$total_data_lokal["salesorder_detail"] = $get_store_salesorder_detail->num_rows();
			}
			
		}
		
	
		//PURCHASING --------------------------
		$last_id_lokal["purchasing"] = 0;
		$total_data_lokal["purchasing"] = 0;
		
		//REQUEST ORDER
		$get_store_ro = $this->db->query("SELECT id FROM ".$this->prefix_pos."ro ORDER BY id DESC");
		if($get_store_ro->num_rows() > 0){
			$dt_store_ro = $get_store_ro->row();
			$last_id_lokal["ro"] = $dt_store_ro->id;
			$total_data_lokal["ro"] = $get_store_ro->num_rows();
		}
		
		$get_store_ro_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."ro_detail ORDER BY id DESC");
		if($get_store_ro_detail->num_rows() > 0){
			$dt_store_ro_detail = $get_store_ro_detail->row();
			$last_id_lokal["ro_detail"] = $dt_store_ro_detail->id;
			$total_data_lokal["ro_detail"] = $get_store_ro_detail->num_rows();
		}
		
		//PURCHASE ORDER
		$get_store_po = $this->db->query("SELECT id FROM ".$this->prefix_pos."po ORDER BY id DESC");
		if($get_store_po->num_rows() > 0){
			$dt_store_po = $get_store_po->row();
			$last_id_lokal["po"] = $dt_store_po->id;
			$total_data_lokal["po"] = $get_store_po->num_rows();
		}
		
		$get_store_po_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."po_detail ORDER BY id DESC");
		if($get_store_po_detail->num_rows() > 0){
			$dt_store_po_detail = $get_store_po_detail->row();
			$last_id_lokal["po_detail"] = $dt_store_po_detail->id;
			$total_data_lokal["po_detail"] = $get_store_po_detail->num_rows();
		}
		
		//RECEIVING
		$get_store_receiving = $this->db->query("SELECT id FROM ".$this->prefix_pos."receiving ORDER BY id DESC");
		if($get_store_receiving->num_rows() > 0){
			$dt_store_receiving = $get_store_receiving->row();
			$last_id_lokal["receiving"] = $dt_store_receiving->id;
			$total_data_lokal["receiving"] = $get_store_receiving->num_rows();
		}
		
		$get_store_receive_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."receive_detail ORDER BY id DESC");
		if($get_store_receive_detail->num_rows() > 0){
			$dt_store_receive_detail = $get_store_receive_detail->row();
			$last_id_lokal["receive_detail"] = $dt_store_receive_detail->id;
			$total_data_lokal["receive_detail"] = $get_store_receive_detail->num_rows();
		}
		
		//STOCK
		$get_store_stock = $this->db->query("SELECT id FROM ".$this->prefix_pos."stock ORDER BY id DESC");
		if($get_store_stock->num_rows() > 0){
			$dt_store_stock = $get_store_stock->row();
			$last_id_lokal["stock"] = $dt_store_stock->id;
			$total_data_lokal["stock"] = $get_store_stock->num_rows();
		}
		
		//STOCK KOREKSI
		$get_store_stock_koreksi = $this->db->query("SELECT id FROM ".$this->prefix_pos."stock_koreksi ORDER BY id DESC");
		if($get_store_stock_koreksi->num_rows() > 0){
			$dt_store_stock_koreksi = $get_store_stock_koreksi->row();
			$last_id_lokal["stock_koreksi"] = $dt_store_stock_koreksi->id;
			$total_data_lokal["stock_koreksi"] = $get_store_stock_koreksi->num_rows();
		}
		
		//STOCK OPNAME
		$get_store_stock_opname = $this->db->query("SELECT id FROM ".$this->prefix_pos."stock_opname ORDER BY id DESC");
		if($get_store_stock_opname->num_rows() > 0){
			$dt_store_stock_opname = $get_store_stock_opname->row();
			$last_id_lokal["stock_opname"] = $dt_store_stock_opname->id;
			$total_data_lokal["stock_opname"] = $get_store_stock_opname->num_rows();
		}
		
		$get_store_stock_opname_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."stock_opname_detail ORDER BY id DESC");
		if($get_store_stock_opname_detail->num_rows() > 0){
			$dt_store_stock_opname_detail = $get_store_stock_opname_detail->row();
			$last_id_lokal["stock_opname_detail"] = $dt_store_stock_opname_detail->id;
			$total_data_lokal["stock_opname_detail"] = $get_store_stock_opname_detail->num_rows();
		}
		
		//STOCK REKAP
		$get_store_stock_rekap = $this->db->query("SELECT id FROM ".$this->prefix_pos."stock_rekap ORDER BY id DESC");
		if($get_store_stock_rekap->num_rows() > 0){
			$dt_store_stock_rekap = $get_store_stock_rekap->row();
			$last_id_lokal["stock_rekap"] = $dt_store_stock_rekap->id;
			$total_data_lokal["stock_rekap"] = $get_store_stock_rekap->num_rows();
		}
		
		//INVENTORY --------------------------
		$last_id_lokal["inventory"] = 0;
		$total_data_lokal["inventory"] = 0;
		
		//USAGE & WASTE
		$get_store_usagewaste = $this->db->query("SELECT id FROM ".$this->prefix_pos."usagewaste ORDER BY id DESC");
		if($get_store_usagewaste->num_rows() > 0){
			$dt_store_usagewaste = $get_store_usagewaste->row();
			$last_id_lokal["usagewaste"] = $dt_store_usagewaste->id;
			$total_data_lokal["usagewaste"] = $get_store_usagewaste->num_rows();
		}
		
		$get_store_usagewaste_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."usagewaste_detail ORDER BY id DESC");
		if($get_store_usagewaste_detail->num_rows() > 0){
			$dt_store_usagewaste_detail = $get_store_usagewaste_detail->row();
			$last_id_lokal["usagewaste_detail"] = $dt_store_usagewaste_detail->id;
			$total_data_lokal["usagewaste_detail"] = $get_store_usagewaste_detail->num_rows();
		}
		
		//DISTRIBUTION
		$get_store_distribution = $this->db->query("SELECT id FROM ".$this->prefix_pos."distribution ORDER BY id DESC");
		if($get_store_distribution->num_rows() > 0){
			$dt_store_distribution = $get_store_distribution->row();
			$last_id_lokal["distribution"] = $dt_store_distribution->id;
			$total_data_lokal["distribution"] = $get_store_distribution->num_rows();
		}
		
		$get_store_distribution_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."distribution_detail ORDER BY id DESC");
		if($get_store_distribution_detail->num_rows() > 0){
			$dt_store_distribution_detail = $get_store_distribution_detail->row();
			$last_id_lokal["distribution_detail"] = $dt_store_distribution_detail->id;
			$total_data_lokal["distribution_detail"] = $get_store_distribution_detail->num_rows();
		}
		
		//PRODUCTION
		$get_store_production = $this->db->query("SELECT id FROM ".$this->prefix_pos."production ORDER BY id DESC");
		if($get_store_production->num_rows() > 0){
			$dt_store_production = $get_store_production->row();
			$last_id_lokal["production"] = $dt_store_production->id;
			$total_data_lokal["production"] = $get_store_production->num_rows();
		}
		
		$get_store_production_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."production_detail ORDER BY id DESC");
		if($get_store_production_detail->num_rows() > 0){
			$dt_store_production_detail = $get_store_production_detail->row();
			$last_id_lokal["production_detail"] = $dt_store_production_detail->id;
			$total_data_lokal["production_detail"] = $get_store_production_detail->num_rows();
		}
		
		//RETUR - PENJUALAN
		$get_store_retur = $this->db->query("SELECT id FROM ".$this->prefix_pos."retur ORDER BY id DESC");
		if($get_store_retur->num_rows() > 0){
			$dt_store_retur = $get_store_retur->row();
			$last_id_lokal["retur"] = $dt_store_retur->id;
			$total_data_lokal["retur"] = $get_store_retur->num_rows();
		}
		
		$get_store_retur_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."retur_detail ORDER BY id DESC");
		if($get_store_retur_detail->num_rows() > 0){
			$dt_store_retur_detail = $get_store_retur_detail->row();
			$last_id_lokal["retur_detail"] = $dt_store_retur_detail->id;
			$total_data_lokal["retur_detail"] = $get_store_retur_detail->num_rows();
		}
		
		//CLOSING
		$get_store_closing = $this->db->query("SELECT id FROM ".$this->prefix_pos."closing ORDER BY id DESC");
		if($get_store_closing->num_rows() > 0){
			$dt_store_closing = $get_store_closing->row();
			$last_id_lokal["closing"] = $dt_store_closing->id;
			$total_data_lokal["closing"] = $get_store_closing->num_rows();
		}
		
		//CLOSING SALES
		$get_store_closing_sales = $this->db->query("SELECT id FROM ".$this->prefix_pos."closing_sales ORDER BY id DESC");
		if($get_store_closing_sales->num_rows() > 0){
			$dt_store_closing_sales = $get_store_closing_sales->row();
			$last_id_lokal["closing_sales"] = $dt_store_closing_sales->id;
			$total_data_lokal["closing_sales"] = $get_store_closing_sales->num_rows();
		}
		
		//CLOSING PURCHASING
		$get_store_closing_purchasing = $this->db->query("SELECT id FROM ".$this->prefix_pos."closing_purchasing ORDER BY id DESC");
		if($get_store_closing_purchasing->num_rows() > 0){
			$dt_store_closing_purchasing = $get_store_closing_purchasing->row();
			$last_id_lokal["closing_purchasing"] = $dt_store_closing_purchasing->id;
			$total_data_lokal["closing_purchasing"] = $get_store_closing_purchasing->num_rows();
		}
		
		//CLOSING INVENTORY
		$get_store_closing_inventory = $this->db->query("SELECT id FROM ".$this->prefix_pos."closing_inventory ORDER BY id DESC");
		if($get_store_closing_inventory->num_rows() > 0){
			$dt_store_closing_inventory = $get_store_closing_inventory->row();
			$last_id_lokal["closing_inventory"] = $dt_store_closing_inventory->id;
			$total_data_lokal["closing_inventory"] = $get_store_closing_inventory->num_rows();
		}
		
		//CLOSING LOG
		$get_store_closing_log = $this->db->query("SELECT id FROM ".$this->prefix_pos."closing_log ORDER BY id DESC");
		if($get_store_closing_log->num_rows() > 0){
			$dt_store_closing_log = $get_store_closing_log->row();
			$last_id_lokal["closing_log"] = $dt_store_closing_log->id;
			$total_data_lokal["closing_log"] = $get_store_closing_log->num_rows();
		}
		
		//CASHFLOW --------------
		$last_id_lokal["cashflow"] = 0;
		$total_data_lokal["cashflow"] = 0;
		
		$get_store_penerimaan_kas = $this->db->query("SELECT id FROM ".$this->prefix_acc."penerimaan_kas ORDER BY id DESC");
		if($get_store_penerimaan_kas->num_rows() > 0){
			$dt_store_penerimaan_kas = $get_store_penerimaan_kas->row();
			$last_id_lokal["penerimaan_kas"] = $dt_store_penerimaan_kas->id;
			$total_data_lokal["penerimaan_kas"] = $get_store_penerimaan_kas->num_rows();
		}
		
		$get_store_pengeluaran_kas = $this->db->query("SELECT id FROM ".$this->prefix_acc."pengeluaran_kas ORDER BY id DESC");
		if($get_store_pengeluaran_kas->num_rows() > 0){
			$dt_store_pengeluaran_kas = $get_store_pengeluaran_kas->row();
			$last_id_lokal["pengeluaran_kas"] = $dt_store_pengeluaran_kas->id;
			$total_data_lokal["pengeluaran_kas"] = $get_store_pengeluaran_kas->num_rows();
		}
		
		$get_store_mutasi_kas_bank = $this->db->query("SELECT id FROM ".$this->prefix_acc."mutasi_kas_bank ORDER BY id DESC");
		if($get_store_mutasi_kas_bank->num_rows() > 0){
			$dt_store_mutasi_kas_bank = $get_store_mutasi_kas_bank->row();
			$last_id_lokal["mutasi_kas_bank"] = $dt_store_mutasi_kas_bank->id;
			$total_data_lokal["mutasi_kas_bank"] = $get_store_mutasi_kas_bank->num_rows();
		}
		
		//ACCOUNT PAYABLE --------------
		$get_store_account_payable = $this->db->query("SELECT id FROM ".$this->prefix_acc."account_payable ORDER BY id DESC");
		if($get_store_account_payable->num_rows() > 0){
			$dt_store_account_payable = $get_store_account_payable->row();
			$last_id_lokal["account_payable"] = $dt_store_account_payable->id;
			$total_data_lokal["account_payable"] = $get_store_account_payable->num_rows();
		}
		
		$get_store_kontrabon = $this->db->query("SELECT id FROM ".$this->prefix_acc."kontrabon ORDER BY id DESC");
		if($get_store_kontrabon->num_rows() > 0){
			$dt_store_kontrabon = $get_store_kontrabon->row();
			$last_id_lokal["kontrabon"] = $dt_store_kontrabon->id;
			$total_data_lokal["kontrabon"] = $get_store_kontrabon->num_rows();
		}
		
		$get_store_kontrabon_detail = $this->db->query("SELECT id FROM ".$this->prefix_acc."kontrabon_detail ORDER BY id DESC");
		if($get_store_kontrabon_detail->num_rows() > 0){
			$dt_store_kontrabon_detail = $get_store_kontrabon_detail->row();
			$last_id_lokal["kontrabon_detail"] = $dt_store_kontrabon_detail->id;
			$total_data_lokal["kontrabon_detail"] = $get_store_kontrabon_detail->num_rows();
		}
		
		$get_store_pelunasan_ap = $this->db->query("SELECT id FROM ".$this->prefix_acc."pelunasan_ap ORDER BY id DESC");
		if($get_store_pelunasan_ap->num_rows() > 0){
			$dt_store_pelunasan_ap = $get_store_pelunasan_ap->row();
			$last_id_lokal["pelunasan_ap"] = $dt_store_pelunasan_ap->id;
			$total_data_lokal["pelunasan_ap"] = $get_store_pelunasan_ap->num_rows();
		}
		
		//ACCOUNT RECEIVABLE --------------
		$get_store_account_receivable = $this->db->query("SELECT id FROM ".$this->prefix_acc."account_receivable ORDER BY id DESC");
		if($get_store_account_receivable->num_rows() > 0){
			$dt_store_account_receivable = $get_store_account_receivable->row();
			$last_id_lokal["account_receivable"] = $dt_store_account_receivable->id;
			$total_data_lokal["account_receivable"] = $get_store_account_receivable->num_rows();
		}
		
		$get_store_invoice = $this->db->query("SELECT id FROM ".$this->prefix_acc."invoice ORDER BY id DESC");
		if($get_store_invoice->num_rows() > 0){
			$dt_store_invoice = $get_store_invoice->row();
			$last_id_lokal["invoice"] = $dt_store_invoice->id;
			$total_data_lokal["invoice"] = $get_store_invoice->num_rows();
		}
		
		$get_store_invoice_detail = $this->db->query("SELECT id FROM ".$this->prefix_acc."invoice_detail ORDER BY id DESC");
		if($get_store_invoice_detail->num_rows() > 0){
			$dt_store_invoice_detail = $get_store_invoice_detail->row();
			$last_id_lokal["invoice_detail"] = $dt_store_invoice_detail->id;
			$total_data_lokal["invoice_detail"] = $get_store_invoice_detail->num_rows();
		}
		
		$get_store_pembayaran_ar = $this->db->query("SELECT id FROM ".$this->prefix_acc."pembayaran_ar ORDER BY id DESC");
		if($get_store_pembayaran_ar->num_rows() > 0){
			$dt_store_pembayaran_ar = $get_store_pembayaran_ar->row();
			$last_id_lokal["pembayaran_ar"] = $dt_store_pembayaran_ar->id;
			$total_data_lokal["pembayaran_ar"] = $get_store_pembayaran_ar->num_rows();
		}
		
		
		$backup_data_allowed_in = implode("','", $backup_data_allowed);
		
		$backup_data_store = array();
		$backup_data_store_available = array();
		
		$no = 0;
		foreach($backup_data_allowed as $key => $val){
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
				if(in_array($val, $only_backup)){
					$allow_backup_list = true;
				}
				
			}else{
				$allow_backup_list = true;
			}
			
			if($allow_backup_list == true){
				$backup_data_store[$val] = array(
					'id'				=> $key,
					'client_id'			=> $client_id,
					'client_code'		=> $client_code,
					'client_name'		=> $client_name,
					'client_email'		=> $client_email,
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
			'merchant_key' => $client_code, 
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
		$this->table_backup = $this->prefix_acc.'backup';
		
		$client_id = $this->input->post('client_id');
		$client_code = $this->input->post('client_code');
		$client_name = $this->input->post('client_name');
		$client_email = $this->input->post('client_email');
		$backup_type = $this->input->post('backup_type');
		$only_backup = $this->input->post('only_backup');
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
		
		$backup_data_id = $this->input->post('backup_data_id');
		$backup_data_id = json_decode($backup_data_id, true);
		
		//OPT-OPTIONS
		$opt_val = array(
			'wepos_tipe'
		);
		
		$get_opt = get_option_value($opt_val);
		if(empty($get_opt['wepos_tipe'])){
			$get_opt['wepos_tipe'] = 'cafe';
		}
		$wepos_tipe = $get_opt['wepos_tipe'];
		
		$backup_data_allowed = array(
			10 => 'sales',
			11 => 'billing',
			12 => 'billing_detail',
			13 => 'billing_log',
			14 => 'salesorder',
			15 => 'salesorder_detail',
			20 => 'purchasing',
			21 => 'ro',
			22 => 'ro_detail',
			23 => 'po',
			24 => 'po_detail',
			25 => 'receiving',
			26 => 'receive_detail',
			30 => 'stock',
			31 => 'stock_koreksi',
			32 => 'stock_opname',
			33 => 'stock_opname_detail',
			34 => 'stock_rekap',
			40 => 'inventory',
			41 => 'usagewaste',
			42 => 'usagewaste_detail',
			43 => 'distribution',
			44 => 'distribution_detail',
			45 => 'production',
			46 => 'production_detail',
			47 => 'retur',
			48 => 'retur_detail',
			50 => 'closing',
			51 => 'closing_sales',
			52 => 'closing_purchasing',
			53 => 'closing_inventory',
			54 => 'closing_log',
			60 => 'cashflow',
			61 => 'penerimaan_kas',
			62 => 'pengeluaran_kas',
			63 => 'mutasi_kas_bank',
			70 => 'account_payable',
			71 => 'kontrabon',
			72 => 'kontrabon_detail',
			73 => 'pelunasan_ap',
			80 => 'account_receivable',
			81 => 'invoice',
			82 => 'invoice_detail',
			83 => 'pembayaran_ar',
		);
		
		if($wepos_tipe == 'cafe'){
			$backup_data_allowed[14] = 'reservation';
			$backup_data_allowed[15] = 'reservation_detail';
		}
		
		$backup_data_text = array(
			'sales' => 'Sales/Penjualan',
			'billing' => 'Billing/Cashier',
			'billing_detail' => 'Billing Detail',
			'billing_log' => 'Billing Log',
			'salesorder' => 'Sales Order',
			'salesorder_detail' => 'Sales Order Detail',
			'purchasing' => 'Purchasing',
			'ro' => 'Request Order',
			'ro_detail' => 'Request Order Detail',
			'po' => 'Purchase Order',
			'po_detail' => 'Purchase Order Detail',
			'receiving' => 'Receiving',
			'receive_detail' => 'Receiving Detail',
			'stock' => 'Pengelolaan Stock',
			'stock_koreksi' => 'Koreksi Stock',
			'stock_opname' => 'Stock Opname',
			'stock_opname_detail' => 'Stock Opname Detail',
			'stock_rekap' => 'Stock Rekap',
			'inventory' => 'Transaksi Inventory',
			'usagewaste' => 'Usage & Waste',
			'usagewaste_detail' => 'Usage & Waste Detail',
			'distribution' => 'Distribution',
			'distribution_detail' => 'Distribution Detail',
			'production' => 'Production',
			'production_detail' => 'Production Detail',
			'retur' => 'Retur',
			'retur_detail' => 'Retur Detail',
			'closing' => 'Closing',
			'closing_sales' => 'Closing Sales',
			'closing_purchasing' => 'Closing Purchasing',
			'closing_inventory' => 'Closing Inventory',
			'closing_log' => 'Closing Log',
			'cashflow' => 'Cashflow',
			'penerimaan_kas' => 'Penerimaan Kas',
			'pengeluaran_kas' => 'Pengeluaran Kas',
			'mutasi_kas_bank' => 'Mutasi Kas Bank',
			'account_payable' => 'Account Payable',
			'kontrabon' => 'Kontrabon',
			'kontrabon_detail' => 'Kontrabon Detail',
			'pelunasan_ap' => 'Pelunasan AP',
			'account_receivable' => 'Account Receivable',
			'invoice' =>  'Invoice',
			'invoice_detail' => 'Invoice Detail',
			'pembayaran_ar' => 'Pembayaran AR'
		);
		
		if($wepos_tipe == 'cafe'){
			unset($backup_data_allowed['salesorder']);
			unset($backup_data_allowed['salesorder_detail']);
			$backup_data_allowed['reservation'] = 'Reservation';
			$backup_data_allowed['reservation'] = 'Reservation Detail';
		}
		
		
		$backup_data_allowed_req = array(10,20,30,40,50,60,70,80);
		
		$total_data = count($backup_data);
		
		
		//CEK FIRST TO START DATE
		$curr_backup_data = '';
		$curr_backup_id = '';
		
		$i = 0;
		foreach($backup_data as $key => $dtT){
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
					if(in_array($curr_req, $backup_data_id)){
						$tot_req++;
					}
				}	
				
				if($tot_req == 0){
					$nama_backup = '-';
					if(!empty($backup_data_text[$curr_backup_data])){
						$nama_backup = $backup_data_text[$curr_backup_data];
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
		
		//CONNECTED TO STORE MANAGEMENT - CURL
		$this->load->library('curl');
		
		$mktime_dc = strtotime(date("d-m-Y H:i:s"));
		
		$ipserver_management_systems = prep_url($ipserver_management_systems);
		
		//DATA BACKUP
		if($use_wms == 1){
			
			$client_url = $ipserver_management_systems.'/sync_backup/backupTrx/generate?_dc='.$mktime_dc;
			
			$crt_file = ASSETS_PATH.config_item('wms_crt_file');
			
		}else{
			
			//wepos.id
			$client_url = config_item('website').'/merchant/backupGenerate?_dc='.$mktime_dc;
			
			$crt_file = ASSETS_PATH.config_item('wepos_crt_file');
			
		}
		
		$post_data = array(
			'client_id' => $client_id,
			'client_code' => $client_code,
			'client_name' => $client_name,
			'client_email' => $client_email,
			'curr_backup_data' => $curr_backup_data,
			'backup_type' => $backup_type,
			'only_backup' => $only_backup,
			'backup_masterdata' => 0,
			'current_total' => $current_total,
			'backup_data' => json_encode($backup_data),
			'akses'=> 'CURL',
			'last_id_on_backup' => $last_id_on_backup,
			'total_data_on_backup' => $total_data_on_backup,
			'limit_backup_data' => $limit_backup_data
		);
		
		$has_next = 0;
		
		$today_mk = strtotime(date("d-m-Y 06:00:00"));
		$today_date = date("Y-m-d H:i:s", $today_mk);
		$today_date_plus1_mk = $today_mk+ONE_DAY_UNIX;
		$today_date_plus1 = date("Y-m-d H:i:s", $today_date_plus1_mk);
		
		
		//LOAD DATA
		$backup_text = '';
		switch($curr_backup_data){
			case 'sales':
				$backup_text = 'Sales/Penjualan';
				
				$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b>', 'has_next' => 0);
				die(json_encode($r));
				
				break;
			
			//Billing/Cashier
			case 'billing':
				$backup_text = 'Billing/Cashier';
				
				//Billing/Cashier ON STORE
				$last_id_billing_store = 0;
				$total_data_store = 0;
				$get_all_store_billing = $this->db->query("SELECT id FROM ".$this->prefix_pos."billing ORDER BY id DESC");
				
				if($only_backup == 'sales'){
					$get_all_store_billing = $this->db->query("SELECT id FROM ".$this->prefix_pos."billing WHERE created <= '".$today_date_plus1."' ORDER BY id DESC");
				}
				
				if($get_all_store_billing->num_rows() > 0){
					$dt_all_billing_store = $get_all_store_billing->row();
					$last_id_billing_store = $dt_all_billing_store->id;
					$total_data_store = $get_all_store_billing->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_billing_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Billing/Cashier ON STORE id > $last_id_on_backup
				$billing_store = array();
				$all_billing = array();
				$get_store_billing = $this->db->query("SELECT * FROM ".$this->prefix_pos."billing WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				
				if($only_backup == 'sales'){
					$get_store_billing = $this->db->query("SELECT * FROM ".$this->prefix_pos."billing WHERE id > ".$last_id_on_backup." AND created <= '".$today_date_plus1."' ORDER BY id ASC LIMIT ".$limit_backup_data);
				}
				
				
				if($get_store_billing->num_rows() > 0){
					
					foreach($get_store_billing->result() as $dt){
						
						$billing_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_billing)){
							$all_billing[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_billing_store;
				}
				
				//NEXT DATA
				if($last_id_billing_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_billing_store'] = $last_id_billing_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['billing_store'] = json_encode($billing_store);
				$post_data['all_billing'] = json_encode($all_billing);
				
				break;
			
			//Billing Detail
			case 'billing_detail':
				$backup_text = 'Billing Detail';
				
				//Billing Detail ON STORE
				$last_id_billing_detail_store = 0;
				$total_data_store = 0;
				$get_all_store_billing_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."billing_detail ORDER BY id DESC");
				
				if($only_backup == 'sales'){
					$get_all_store_billing_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."billing_detail WHERE created <= '".$today_date_plus1."' ORDER BY id DESC");
				}
				
				if($get_all_store_billing_detail->num_rows() > 0){
					$dt_all_billing_detail_store = $get_all_store_billing_detail->row();
					$last_id_billing_detail_store = $dt_all_billing_detail_store->id;
					$total_data_store = $get_all_store_billing_detail->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_billing_detail_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Billing Detail ON STORE id > $last_id_on_backup
				$billing_detail_store = array();
				$all_billing_detail = array();
				$get_store_billing_detail = $this->db->query("SELECT * FROM ".$this->prefix_pos."billing_detail WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				
				if($only_backup == 'sales'){
					$get_store_billing_detail = $this->db->query("SELECT * FROM ".$this->prefix_pos."billing_detail WHERE id > ".$last_id_on_backup." AND created <= '".$today_date_plus1."' ORDER BY id ASC LIMIT ".$limit_backup_data);
				}
				
				if($get_store_billing_detail->num_rows() > 0){
					
					foreach($get_store_billing_detail->result() as $dt){
						
						$billing_detail_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_billing_detail)){
							$all_billing_detail[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_billing_detail_store;
				}
				
				//NEXT DATA
				if($last_id_billing_detail_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_billing_detail_store'] = $last_id_billing_detail_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['billing_detail_store'] = json_encode($billing_detail_store);
				$post_data['all_billing_detail'] = json_encode($all_billing_detail);
				
				break;
			
			case 'billing_log':
				$backup_text = 'Billing Log';
				
				//Billing Detail ON STORE
				$last_id_billing_log_store = 0;
				$total_data_store = 0;
				$get_all_store_billing_log = $this->db->query("SELECT id FROM ".$this->prefix_pos."billing_log ORDER BY id DESC");
				
				if($only_backup == 'sales'){
					$get_all_store_billing_log = $this->db->query("SELECT id FROM ".$this->prefix_pos."billing_log WHERE created <= '".$today_date_plus1."' ORDER BY id DESC");
				}
				
				if($get_all_store_billing_log->num_rows() > 0){
					$dt_all_billing_log_store = $get_all_store_billing_log->row();
					$last_id_billing_log_store = $dt_all_billing_log_store->id;
					$total_data_store = $get_all_store_billing_log->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_billing_log_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Billing Detail ON STORE id > $last_id_on_backup
				$billing_log_store = array();
				$all_billing_log = array();
				$get_store_billing_log = $this->db->query("SELECT * FROM ".$this->prefix_pos."billing_log WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				
				if($only_backup == 'sales'){
					$get_store_billing_log = $this->db->query("SELECT * FROM ".$this->prefix_pos."billing_log WHERE id > ".$last_id_on_backup." AND created <= '".$today_date_plus1."' ORDER BY id ASC LIMIT ".$limit_backup_data);
				}
				
				if($get_store_billing_log->num_rows() > 0){
					
					foreach($get_store_billing_log->result() as $dt){
						
						$billing_log_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_billing_log)){
							$all_billing_log[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_billing_log_store;
				}
				
				//NEXT DATA
				if($last_id_billing_log_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_billing_log_store'] = $last_id_billing_log_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['billing_log_store'] = json_encode($billing_log_store);
				$post_data['all_billing_log'] = json_encode($all_billing_log);
				
				break;
				
			//Sales Order
			case 'salesorder':
				$backup_text = 'Billing/Cashier';
				
				//Sales Order ON STORE
				$last_id_salesorder_store = 0;
				$total_data_store = 0;
				$get_all_store_salesorder = $this->db->query("SELECT id FROM ".$this->prefix_pos."salesorder ORDER BY id DESC");
				if($get_all_store_salesorder->num_rows() > 0){
					$dt_all_salesorder_store = $get_all_store_salesorder->row();
					$last_id_salesorder_store = $dt_all_salesorder_store->id;
					$total_data_store = $get_all_store_salesorder->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_salesorder_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Sales Order ON STORE id > $last_id_on_backup
				$salesorder_store = array();
				$all_salesorder = array();
				$get_store_salesorder = $this->db->query("SELECT * FROM ".$this->prefix_pos."salesorder WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_salesorder->num_rows() > 0){
					
					foreach($get_store_salesorder->result() as $dt){
						
						$salesorder_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_salesorder)){
							$all_salesorder[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_salesorder_store;
				}
				
				//NEXT DATA
				if($last_id_salesorder_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_salesorder_store'] = $last_id_salesorder_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['salesorder_store'] = json_encode($salesorder_store);
				$post_data['all_salesorder'] = json_encode($all_salesorder);
				
				break;
			
			//Sales Order
			case 'salesorder_detail':
				$backup_text = 'Sales Order Detail';
				
				//Sales Order ON STORE
				$last_id_salesorder_detail_store = 0;
				$total_data_store = 0;
				$get_all_store_salesorder_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."salesorder_detail ORDER BY id DESC");
				if($get_all_store_salesorder_detail->num_rows() > 0){
					$dt_all_salesorder_detail_store = $get_all_store_salesorder_detail->row();
					$last_id_salesorder_detail_store = $dt_all_salesorder_detail_store->id;
					$total_data_store = $get_all_store_salesorder_detail->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_salesorder_detail_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Sales Order ON STORE id > $last_id_on_backup
				$salesorder_detail_store = array();
				$all_salesorder_detail = array();
				$get_store_salesorder_detail = $this->db->query("SELECT * FROM ".$this->prefix_pos."salesorder_detail WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_salesorder_detail->num_rows() > 0){
					
					foreach($get_store_salesorder_detail->result() as $dt){
						
						$salesorder_detail_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_salesorder_detail)){
							$all_salesorder_detail[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_salesorder_detail_store;
				}
				
				//NEXT DATA
				if($last_id_salesorder_detail_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_salesorder_detail_store'] = $last_id_salesorder_detail_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['salesorder_detail_store'] = json_encode($salesorder_detail_store);
				$post_data['all_salesorder_detail'] = json_encode($all_salesorder_detail);
				
				break;
			
			//Reservation
			case 'reservation':
				$backup_text = 'Billing/Cashier';
				
				//Reservation ON STORE
				$last_id_reservation_store = 0;
				$total_data_store = 0;
				$get_all_store_reservation = $this->db->query("SELECT id FROM ".$this->prefix_pos."reservation ORDER BY id DESC");
				if($get_all_store_reservation->num_rows() > 0){
					$dt_all_reservation_store = $get_all_store_reservation->row();
					$last_id_reservation_store = $dt_all_reservation_store->id;
					$total_data_store = $get_all_store_reservation->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_reservation_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Reservation ON STORE id > $last_id_on_backup
				$reservation_store = array();
				$all_reservation = array();
				$get_store_reservation = $this->db->query("SELECT * FROM ".$this->prefix_pos."reservation WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_reservation->num_rows() > 0){
					
					foreach($get_store_reservation->result() as $dt){
						
						$reservation_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_reservation)){
							$all_reservation[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_reservation_store;
				}
				
				//NEXT DATA
				if($last_id_reservation_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_reservation_store'] = $last_id_reservation_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['reservation_store'] = json_encode($reservation_store);
				$post_data['all_reservation'] = json_encode($all_reservation);
				
				break;
			
			//Reservation
			case 'reservation_detail':
				$backup_text = 'Reservation Detail';
				
				//Reservation ON STORE
				$last_id_reservation_detail_store = 0;
				$total_data_store = 0;
				$get_all_store_reservation_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."reservation_detail ORDER BY id DESC");
				if($get_all_store_reservation_detail->num_rows() > 0){
					$dt_all_reservation_detail_store = $get_all_store_reservation_detail->row();
					$last_id_reservation_detail_store = $dt_all_reservation_detail_store->id;
					$total_data_store = $get_all_store_reservation_detail->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_reservation_detail_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Reservation ON STORE id > $last_id_on_backup
				$reservation_detail_store = array();
				$all_reservation_detail = array();
				$get_store_reservation_detail = $this->db->query("SELECT * FROM ".$this->prefix_pos."reservation_detail WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_reservation_detail->num_rows() > 0){
					
					foreach($get_store_reservation_detail->result() as $dt){
						
						$reservation_detail_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_reservation_detail)){
							$all_reservation_detail[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_reservation_detail_store;
				}
				
				//NEXT DATA
				if($last_id_reservation_detail_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_reservation_detail_store'] = $last_id_reservation_detail_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['reservation_detail_store'] = json_encode($reservation_detail_store);
				$post_data['all_reservation_detail'] = json_encode($all_reservation_detail);
				
				break;
			
			case 'purchasing':
				$backup_text = 'Purchasing';
				
				$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b>', 'has_next' => 0);
				die(json_encode($r));
				
				break;
			
			//Request Order
			case 'ro':
				$backup_text = 'Request Order';
				
				//Request Order ON STORE
				$last_id_ro_store = 0;
				$total_data_store = 0;
				$get_all_store_ro = $this->db->query("SELECT id FROM ".$this->prefix_pos."ro ORDER BY id DESC");
				if($get_all_store_ro->num_rows() > 0){
					$dt_all_ro_store = $get_all_store_ro->row();
					$last_id_ro_store = $dt_all_ro_store->id;
					$total_data_store = $get_all_store_ro->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_ro_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Request Order ON STORE id > $last_id_on_backup
				$ro_store = array();
				$all_ro = array();
				$get_store_ro = $this->db->query("SELECT * FROM ".$this->prefix_pos."ro WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_ro->num_rows() > 0){
					
					foreach($get_store_ro->result() as $dt){
						
						$ro_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_ro)){
							$all_ro[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_ro_store;
				}
				
				//NEXT DATA
				if($last_id_ro_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_ro_store'] = $last_id_ro_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['ro_store'] = json_encode($ro_store);
				$post_data['all_ro'] = json_encode($all_ro);
				
				break;
			
			//Request Order Detail
			case 'ro_detail':
				$backup_text = 'Request Order Detail';
				
				//Request Order Detail ON STORE
				$last_id_ro_detail_store = 0;
				$total_data_store = 0;
				$get_all_store_ro_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."ro_detail ORDER BY id DESC");
				if($get_all_store_ro_detail->num_rows() > 0){
					$dt_all_ro_detail_store = $get_all_store_ro_detail->row();
					$last_id_ro_detail_store = $dt_all_ro_detail_store->id;
					$total_data_store = $get_all_store_ro_detail->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_ro_detail_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Request Order Detail ON STORE id > $last_id_on_backup
				$ro_detail_store = array();
				$all_ro_detail = array();
				$get_store_ro_detail = $this->db->query("SELECT * FROM ".$this->prefix_pos."ro_detail WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_ro_detail->num_rows() > 0){
					
					foreach($get_store_ro_detail->result() as $dt){
						
						$ro_detail_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_ro_detail)){
							$all_ro_detail[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_ro_detail_store;
				}
				
				//NEXT DATA
				if($last_id_ro_detail_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_ro_detail_store'] = $last_id_ro_detail_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['ro_detail_store'] = json_encode($ro_detail_store);
				$post_data['all_ro_detail'] = json_encode($all_ro_detail);
				
				break;
				
			//Purchase Order
			case 'po':
				$backup_text = 'Purchase Order';
				
				//Purchase Order ON STORE
				$last_id_po_store = 0;
				$total_data_store = 0;
				$get_all_store_po = $this->db->query("SELECT id FROM ".$this->prefix_pos."po ORDER BY id DESC");
				if($get_all_store_po->num_rows() > 0){
					$dt_all_po_store = $get_all_store_po->row();
					$last_id_po_store = $dt_all_po_store->id;
					$total_data_store = $get_all_store_po->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_po_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Purchase Order ON STORE id > $last_id_on_backup
				$po_store = array();
				$all_po = array();
				$get_store_po = $this->db->query("SELECT * FROM ".$this->prefix_pos."po WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_po->num_rows() > 0){
					
					foreach($get_store_po->result() as $dt){
						
						$po_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_po)){
							$all_po[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_po_store;
				}
				
				//NEXT DATA
				if($last_id_po_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_po_store'] = $last_id_po_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['po_store'] = json_encode($po_store);
				$post_data['all_po'] = json_encode($all_po);
				
				break;
				
			//Purchase Order Detail
			case 'po_detail':
				$backup_text = 'Purchase Order Detail';
				
				//Purchase Order Detail ON STORE
				$last_id_po_detail_store = 0;
				$total_data_store = 0;
				$get_all_store_po_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."po_detail ORDER BY id DESC");
				if($get_all_store_po_detail->num_rows() > 0){
					$dt_all_po_detail_store = $get_all_store_po_detail->row();
					$last_id_po_detail_store = $dt_all_po_detail_store->id;
					$total_data_store = $get_all_store_po_detail->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_po_detail_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Purchase Order Detail ON STORE id > $last_id_on_backup
				$po_detail_store = array();
				$all_po_detail = array();
				$get_store_po_detail = $this->db->query("SELECT * FROM ".$this->prefix_pos."po_detail WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_po_detail->num_rows() > 0){
					
					foreach($get_store_po_detail->result() as $dt){
						
						$po_detail_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_po_detail)){
							$all_po_detail[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_po_detail_store;
				}
				
				//NEXT DATA
				if($last_id_po_detail_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_po_detail_store'] = $last_id_po_detail_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['po_detail_store'] = json_encode($po_detail_store);
				$post_data['all_po_detail'] = json_encode($all_po_detail);
				
				break;
			
			//Receiving
			case 'receiving':
				$backup_text = 'Receiving';
				
				//Receiving ON STORE
				$last_id_receiving_store = 0;
				$total_data_store = 0;
				$get_all_store_receiving = $this->db->query("SELECT id FROM ".$this->prefix_pos."receiving ORDER BY id DESC");
				if($get_all_store_receiving->num_rows() > 0){
					$dt_all_receiving_store = $get_all_store_receiving->row();
					$last_id_receiving_store = $dt_all_receiving_store->id;
					$total_data_store = $get_all_store_receiving->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_receiving_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Receiving ON STORE id > $last_id_on_backup
				$receiving_store = array();
				$all_receiving = array();
				$get_store_receiving = $this->db->query("SELECT * FROM ".$this->prefix_pos."receiving WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_receiving->num_rows() > 0){
					
					foreach($get_store_receiving->result() as $dt){
						
						$receiving_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_receiving)){
							$all_receiving[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_receiving_store;
				}
				
				//NEXT DATA
				if($last_id_receiving_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_receiving_store'] = $last_id_receiving_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['receiving_store'] = json_encode($receiving_store);
				$post_data['all_receiving'] = json_encode($all_receiving);
				
				break;
			
			//Receiving Detail
			case 'receive_detail':
				$backup_text = 'Receiving Detail';
				
				//Receiving Detail ON STORE
				$last_id_receive_detail_store = 0;
				$total_data_store = 0;
				$get_all_store_receive_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."receive_detail ORDER BY id DESC");
				if($get_all_store_receive_detail->num_rows() > 0){
					$dt_all_receive_detail_store = $get_all_store_receive_detail->row();
					$last_id_receive_detail_store = $dt_all_receive_detail_store->id;
					$total_data_store = $get_all_store_receive_detail->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_receive_detail_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Receiving Detail ON STORE id > $last_id_on_backup
				$receive_detail_store = array();
				$all_receive_detail = array();
				$get_store_receive_detail = $this->db->query("SELECT * FROM ".$this->prefix_pos."receive_detail WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_receive_detail->num_rows() > 0){
					
					foreach($get_store_receive_detail->result() as $dt){
						
						$receive_detail_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_receive_detail)){
							$all_receive_detail[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_receive_detail_store;
				}
				
				//NEXT DATA
				if($last_id_receive_detail_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_receive_detail_store'] = $last_id_receive_detail_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['receive_detail_store'] = json_encode($receive_detail_store);
				$post_data['all_receive_detail'] = json_encode($all_receive_detail);
				
				break;
			
			//STOCK----------------
			case 'stock':
				$backup_text = 'Pengelolaan Stock';
				
				//Pengelolaan Stock ON STORE
				$last_id_stock_store = 0;
				$total_data_store = 0;
				$get_all_store_stock = $this->db->query("SELECT id FROM ".$this->prefix_pos."stock ORDER BY id DESC");
				if($get_all_store_stock->num_rows() > 0){
					$dt_all_stock_store = $get_all_store_stock->row();
					$last_id_stock_store = $dt_all_stock_store->id;
					$total_data_store = $get_all_store_stock->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_stock_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Pengelolaan Stock ON STORE id > $last_id_on_backup
				$stock_store = array();
				$all_stock = array();
				$get_store_stock = $this->db->query("SELECT * FROM ".$this->prefix_pos."stock WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_stock->num_rows() > 0){
					
					foreach($get_store_stock->result() as $dt){
						
						$stock_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_stock)){
							$all_stock[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_stock_store;
				}
				
				//NEXT DATA
				if($last_id_stock_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_stock_store'] = $last_id_stock_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['stock_store'] = json_encode($stock_store);
				$post_data['all_stock'] = json_encode($all_stock);
				
				break;
			
			case 'stock_koreksi':
				$backup_text = 'Koreksi Stock';
				
				//Koreksi Stock ON STORE
				$last_id_stock_koreksi_store = 0;
				$total_data_store = 0;
				$get_all_store_stock_koreksi = $this->db->query("SELECT id FROM ".$this->prefix_pos."stock_koreksi ORDER BY id DESC");
				if($get_all_store_stock_koreksi->num_rows() > 0){
					$dt_all_stock_koreksi_store = $get_all_store_stock_koreksi->row();
					$last_id_stock_koreksi_store = $dt_all_stock_koreksi_store->id;
					$total_data_store = $get_all_store_stock_koreksi->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_stock_koreksi_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Koreksi Stock ON STORE id > $last_id_on_backup
				$stock_koreksi_store = array();
				$all_stock_koreksi = array();
				$get_store_stock_koreksi = $this->db->query("SELECT * FROM ".$this->prefix_pos."stock_koreksi WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_stock_koreksi->num_rows() > 0){
					
					foreach($get_store_stock_koreksi->result() as $dt){
						
						$stock_koreksi_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_stock_koreksi)){
							$all_stock_koreksi[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_stock_koreksi_store;
				}
				
				//NEXT DATA
				if($last_id_stock_koreksi_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_stock_koreksi_store'] = $last_id_stock_koreksi_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['stock_koreksi_store'] = json_encode($stock_koreksi_store);
				$post_data['all_stock_koreksi'] = json_encode($all_stock_koreksi);
				
				break;
				
			case 'stock_opname':
				$backup_text = 'Stock Opname';
				
				//Stock Opname ON STORE
				$last_id_stock_opname_store = 0;
				$total_data_store = 0;
				$get_all_store_stock_opname = $this->db->query("SELECT id FROM ".$this->prefix_pos."stock_opname ORDER BY id DESC");
				if($get_all_store_stock_opname->num_rows() > 0){
					$dt_all_stock_opname_store = $get_all_store_stock_opname->row();
					$last_id_stock_opname_store = $dt_all_stock_opname_store->id;
					$total_data_store = $get_all_store_stock_opname->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_stock_opname_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Stock Opname ON STORE id > $last_id_on_backup
				$stock_opname_store = array();
				$all_stock_opname = array();
				$get_store_stock_opname = $this->db->query("SELECT * FROM ".$this->prefix_pos."stock_opname WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_stock_opname->num_rows() > 0){
					
					foreach($get_store_stock_opname->result() as $dt){
						
						$stock_opname_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_stock_opname)){
							$all_stock_opname[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_stock_opname_store;
				}
				
				//NEXT DATA
				if($last_id_stock_opname_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_stock_opname_store'] = $last_id_stock_opname_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['stock_opname_store'] = json_encode($stock_opname_store);
				$post_data['all_stock_opname'] = json_encode($all_stock_opname);
				
				break;
				
			case 'stock_opname_detail':
				$backup_text = 'Stock Opname Detail';
				
				//Stock Opname Detail ON STORE
				$last_id_stock_opname_detail_store = 0;
				$total_data_store = 0;
				$get_all_store_stock_opname_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."stock_opname_detail ORDER BY id DESC");
				if($get_all_store_stock_opname_detail->num_rows() > 0){
					$dt_all_stock_opname_detail_store = $get_all_store_stock_opname_detail->row();
					$last_id_stock_opname_detail_store = $dt_all_stock_opname_detail_store->id;
					$total_data_store = $get_all_store_stock_opname_detail->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_stock_opname_detail_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Stock Opname Detail ON STORE id > $last_id_on_backup
				$stock_opname_detail_store = array();
				$all_stock_opname_detail = array();
				$get_store_stock_opname_detail = $this->db->query("SELECT * FROM ".$this->prefix_pos."stock_opname_detail WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_stock_opname_detail->num_rows() > 0){
					
					foreach($get_store_stock_opname_detail->result() as $dt){
						
						$stock_opname_detail_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_stock_opname_detail)){
							$all_stock_opname_detail[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_stock_opname_detail_store;
				}
				
				//NEXT DATA
				if($last_id_stock_opname_detail_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_stock_opname_detail_store'] = $last_id_stock_opname_detail_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['stock_opname_detail_store'] = json_encode($stock_opname_detail_store);
				$post_data['all_stock_opname_detail'] = json_encode($all_stock_opname_detail);
				
				break;
				
			case 'stock_rekap':
				$backup_text = 'Stock Rekap';
				
				//Stock Rekap  ON STORE
				$last_id_stock_rekap_store = 0;
				$total_data_store = 0;
				$get_all_store_stock_rekap = $this->db->query("SELECT id FROM ".$this->prefix_pos."stock_rekap ORDER BY id DESC");
				if($get_all_store_stock_rekap->num_rows() > 0){
					$dt_all_stock_rekap_store = $get_all_store_stock_rekap->row();
					$last_id_stock_rekap_store = $dt_all_stock_rekap_store->id;
					$total_data_store = $get_all_store_stock_rekap->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_stock_rekap_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Stock Rekap ON STORE id > $last_id_on_backup
				$stock_rekap_store = array();
				$all_stock_rekap = array();
				$get_store_stock_rekap = $this->db->query("SELECT * FROM ".$this->prefix_pos."stock_rekap WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_stock_rekap->num_rows() > 0){
					
					foreach($get_store_stock_rekap->result() as $dt){
						
						$stock_rekap_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_stock_rekap)){
							$all_stock_rekap[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_stock_rekap_store;
				}
				
				//NEXT DATA
				if($last_id_stock_rekap_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_stock_rekap_store'] = $last_id_stock_rekap_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['stock_rekap_store'] = json_encode($stock_rekap_store);
				$post_data['all_stock_rekap'] = json_encode($all_stock_rekap);
				
				break;
			
			//Transaksi Inventory ------------------------------		
			case 'inventory':
				$backup_text = 'Transaksi Inventory';
				
				$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b>', 'has_next' => 0);
				die(json_encode($r));
				
				break;
			
			case 'usagewaste':
				$backup_text = 'Usage & Waste';
				
				//Usage & Waste  ON STORE
				$last_id_usagewaste_store = 0;
				$total_data_store = 0;
				$get_all_store_usagewaste = $this->db->query("SELECT id FROM ".$this->prefix_pos."usagewaste ORDER BY id DESC");
				if($get_all_store_usagewaste->num_rows() > 0){
					$dt_all_usagewaste_store = $get_all_store_usagewaste->row();
					$last_id_usagewaste_store = $dt_all_usagewaste_store->id;
					$total_data_store = $get_all_store_usagewaste->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_usagewaste_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Usage & Waste ON STORE id > $last_id_on_backup
				$usagewaste_store = array();
				$all_usagewaste = array();
				$get_store_usagewaste = $this->db->query("SELECT * FROM ".$this->prefix_pos."usagewaste WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_usagewaste->num_rows() > 0){
					
					foreach($get_store_usagewaste->result() as $dt){
						
						$usagewaste_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_usagewaste)){
							$all_usagewaste[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_usagewaste_store;
				}
				
				//NEXT DATA
				if($last_id_usagewaste_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_usagewaste_store'] = $last_id_usagewaste_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['usagewaste_store'] = json_encode($usagewaste_store);
				$post_data['all_usagewaste'] = json_encode($all_usagewaste);
				
				break;
				
			case 'usagewaste_detail':
				$backup_text = 'Usage & Waste Detail';
				
				//Usage & Waste Detail  ON STORE
				$last_id_usagewaste_detail_store = 0;
				$total_data_store = 0;
				$get_all_store_usagewaste_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."usagewaste_detail ORDER BY id DESC");
				if($get_all_store_usagewaste_detail->num_rows() > 0){
					$dt_all_usagewaste_detail_store = $get_all_store_usagewaste_detail->row();
					$last_id_usagewaste_detail_store = $dt_all_usagewaste_detail_store->id;
					$total_data_store = $get_all_store_usagewaste_detail->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_usagewaste_detail_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Usage & Waste Detail ON STORE id > $last_id_on_backup
				$usagewaste_detail_store = array();
				$all_usagewaste_detail = array();
				$get_store_usagewaste_detail = $this->db->query("SELECT * FROM ".$this->prefix_pos."usagewaste_detail WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_usagewaste_detail->num_rows() > 0){
					
					foreach($get_store_usagewaste_detail->result() as $dt){
						
						$usagewaste_detail_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_usagewaste_detail)){
							$all_usagewaste_detail[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_usagewaste_detail_store;
				}
				
				//NEXT DATA
				if($last_id_usagewaste_detail_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_usagewaste_detail_store'] = $last_id_usagewaste_detail_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['usagewaste_detail_store'] = json_encode($usagewaste_detail_store);
				$post_data['all_usagewaste_detail'] = json_encode($all_usagewaste_detail);
				
				break;
				
			case 'distribution':
				$backup_text = 'Distribution';
				
				//Distribution  ON STORE
				$last_id_distribution_store = 0;
				$total_data_store = 0;
				$get_all_store_distribution = $this->db->query("SELECT id FROM ".$this->prefix_pos."distribution ORDER BY id DESC");
				if($get_all_store_distribution->num_rows() > 0){
					$dt_all_distribution_store = $get_all_store_distribution->row();
					$last_id_distribution_store = $dt_all_distribution_store->id;
					$total_data_store = $get_all_store_distribution->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_distribution_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Distribution ON STORE id > $last_id_on_backup
				$distribution_store = array();
				$all_distribution = array();
				$get_store_distribution = $this->db->query("SELECT * FROM ".$this->prefix_pos."distribution WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_distribution->num_rows() > 0){
					
					foreach($get_store_distribution->result() as $dt){
						
						$distribution_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_distribution)){
							$all_distribution[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_distribution_store;
				}
				
				//NEXT DATA
				if($last_id_distribution_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_distribution_store'] = $last_id_distribution_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['distribution_store'] = json_encode($distribution_store);
				$post_data['all_distribution'] = json_encode($all_distribution);
				
				break;
				
			case 'distribution_detail':
				$backup_text = 'Distribution Detail';
				
				//Distribution Detail  ON STORE
				$last_id_distribution_detail_store = 0;
				$total_data_store = 0;
				$get_all_store_distribution_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."distribution_detail ORDER BY id DESC");
				if($get_all_store_distribution_detail->num_rows() > 0){
					$dt_all_distribution_detail_store = $get_all_store_distribution_detail->row();
					$last_id_distribution_detail_store = $dt_all_distribution_detail_store->id;
					$total_data_store = $get_all_store_distribution_detail->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_distribution_detail_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Distribution Detail ON STORE id > $last_id_on_backup
				$distribution_detail_store = array();
				$all_distribution_detail = array();
				$get_store_distribution_detail = $this->db->query("SELECT * FROM ".$this->prefix_pos."distribution_detail WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_distribution_detail->num_rows() > 0){
					
					foreach($get_store_distribution_detail->result() as $dt){
						
						$distribution_detail_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_distribution_detail)){
							$all_distribution_detail[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_distribution_detail_store;
				}
				
				//NEXT DATA
				if($last_id_distribution_detail_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_distribution_detail_store'] = $last_id_distribution_detail_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['distribution_detail_store'] = json_encode($distribution_detail_store);
				$post_data['all_distribution_detail'] = json_encode($all_distribution_detail);
				
				break;
				
			case 'production':
				$backup_text = 'Production';
				
				//Production  ON STORE
				$last_id_production_store = 0;
				$total_data_store = 0;
				$get_all_store_production = $this->db->query("SELECT id FROM ".$this->prefix_pos."production ORDER BY id DESC");
				if($get_all_store_production->num_rows() > 0){
					$dt_all_production_store = $get_all_store_production->row();
					$last_id_production_store = $dt_all_production_store->id;
					$total_data_store = $get_all_store_production->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_production_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Production ON STORE id > $last_id_on_backup
				$production_store = array();
				$all_production = array();
				$get_store_production = $this->db->query("SELECT * FROM ".$this->prefix_pos."production WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_production->num_rows() > 0){
					
					foreach($get_store_production->result() as $dt){
						
						$production_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_production)){
							$all_production[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_production_store;
				}
				
				//NEXT DATA
				if($last_id_production_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_production_store'] = $last_id_production_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['production_store'] = json_encode($production_store);
				$post_data['all_production'] = json_encode($all_production);
				
				break;
				
			case 'production_detail':
				$backup_text = 'Production Detail';
				
				//Production Detail  ON STORE
				$last_id_production_detail_store = 0;
				$total_data_store = 0;
				$get_all_store_production_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."production_detail ORDER BY id DESC");
				if($get_all_store_production_detail->num_rows() > 0){
					$dt_all_production_detail_store = $get_all_store_production_detail->row();
					$last_id_production_detail_store = $dt_all_production_detail_store->id;
					$total_data_store = $get_all_store_production_detail->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_production_detail_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Production Detail ON STORE id > $last_id_on_backup
				$production_detail_store = array();
				$all_production_detail = array();
				$get_store_production_detail = $this->db->query("SELECT * FROM ".$this->prefix_pos."production_detail WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_production_detail->num_rows() > 0){
					
					foreach($get_store_production_detail->result() as $dt){
						
						$production_detail_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_production_detail)){
							$all_production_detail[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_production_detail_store;
				}
				
				//NEXT DATA
				if($last_id_production_detail_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_production_detail_store'] = $last_id_production_detail_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['production_detail_store'] = json_encode($production_detail_store);
				$post_data['all_production_detail'] = json_encode($all_production_detail);
				
				break;
				
			case 'retur':
				$backup_text = 'Retur';
				
				//Retur ON STORE
				$last_id_retur_store = 0;
				$total_data_store = 0;
				$get_all_store_retur = $this->db->query("SELECT id FROM ".$this->prefix_pos."retur ORDER BY id DESC");
				if($get_all_store_retur->num_rows() > 0){
					$dt_all_retur_store = $get_all_store_retur->row();
					$last_id_retur_store = $dt_all_retur_store->id;
					$total_data_store = $get_all_store_retur->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_retur_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Retur ON STORE id > $last_id_on_backup
				$retur_store = array();
				$all_retur = array();
				$get_store_retur = $this->db->query("SELECT * FROM ".$this->prefix_pos."retur WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_retur->num_rows() > 0){
					
					foreach($get_store_retur->result() as $dt){
						
						$retur_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_retur)){
							$all_retur[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_retur_store;
				}
				
				//NEXT DATA
				if($last_id_retur_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_retur_store'] = $last_id_retur_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['retur_store'] = json_encode($retur_store);
				$post_data['all_retur'] = json_encode($all_retur);
				
				break;
				
			case 'retur_detail':
				$backup_text = 'Retur Detail';
				
				//Retur Detail ON STORE
				$last_id_retur_detail_store = 0;
				$total_data_store = 0;
				$get_all_store_retur_detail = $this->db->query("SELECT id FROM ".$this->prefix_pos."retur_detail ORDER BY id DESC");
				if($get_all_store_retur_detail->num_rows() > 0){
					$dt_all_retur_detail_store = $get_all_store_retur_detail->row();
					$last_id_retur_detail_store = $dt_all_retur_detail_store->id;
					$total_data_store = $get_all_store_retur_detail->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_retur_detail_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Retur Detail ON STORE id > $last_id_on_backup
				$retur_detail_store = array();
				$all_retur_detail = array();
				$get_store_retur_detail = $this->db->query("SELECT * FROM ".$this->prefix_pos."retur_detail WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_retur_detail->num_rows() > 0){
					
					foreach($get_store_retur_detail->result() as $dt){
						
						$retur_detail_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_retur_detail)){
							$all_retur_detail[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_retur_detail_store;
				}
				
				//NEXT DATA
				if($last_id_retur_detail_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_retur_detail_store'] = $last_id_retur_detail_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['retur_detail_store'] = json_encode($retur_detail_store);
				$post_data['all_retur_detail'] = json_encode($all_retur_detail);
				
				break;
				
			//CLOSING -------------------
			case 'closing':
				$backup_text = 'Closing';
				
				//Closing ON STORE
				$last_id_closing_store = 0;
				$total_data_store = 0;
				$get_all_store_closing = $this->db->query("SELECT id FROM ".$this->prefix_pos."closing ORDER BY id DESC");
				if($get_all_store_closing->num_rows() > 0){
					$dt_all_closing_store = $get_all_store_closing->row();
					$last_id_closing_store = $dt_all_closing_store->id;
					$total_data_store = $get_all_store_closing->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_closing_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Closing ON STORE id > $last_id_on_backup
				$closing_store = array();
				$all_closing = array();
				$get_store_closing = $this->db->query("SELECT * FROM ".$this->prefix_pos."closing WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_closing->num_rows() > 0){
					
					foreach($get_store_closing->result() as $dt){
						
						$closing_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_closing)){
							$all_closing[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_closing_store;
				}
				
				//NEXT DATA
				if($last_id_closing_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_closing_store'] = $last_id_closing_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['closing_store'] = json_encode($closing_store);
				$post_data['all_closing'] = json_encode($all_closing);
				
				break;
				
			case 'closing_sales':
				$backup_text = 'Closing Sales';
				
				//Closing Sales ON STORE
				$last_id_closing_sales_store = 0;
				$total_data_store = 0;
				$get_all_store_closing_sales = $this->db->query("SELECT id FROM ".$this->prefix_pos."closing_sales ORDER BY id DESC");
				if($get_all_store_closing_sales->num_rows() > 0){
					$dt_all_closing_sales_store = $get_all_store_closing_sales->row();
					$last_id_closing_sales_store = $dt_all_closing_sales_store->id;
					$total_data_store = $get_all_store_closing_sales->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_closing_sales_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Closing Sales ON STORE id > $last_id_on_backup
				$closing_sales_store = array();
				$all_closing_sales = array();
				$get_store_closing_sales = $this->db->query("SELECT * FROM ".$this->prefix_pos."closing_sales WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_closing_sales->num_rows() > 0){
					
					foreach($get_store_closing_sales->result() as $dt){
						
						$closing_sales_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_closing_sales)){
							$all_closing_sales[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_closing_sales_store;
				}
				
				//NEXT DATA
				if($last_id_closing_sales_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_closing_sales_store'] = $last_id_closing_sales_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['closing_sales_store'] = json_encode($closing_sales_store);
				$post_data['all_closing_sales'] = json_encode($all_closing_sales);
				
				break;	
				
			case 'closing_purchasing':
				$backup_text = 'Closing Purchasing';
				
				//Closing Purchasing ON STORE
				$last_id_closing_purchasing_store = 0;
				$total_data_store = 0;
				$get_all_store_closing_purchasing = $this->db->query("SELECT id FROM ".$this->prefix_pos."closing_purchasing ORDER BY id DESC");
				if($get_all_store_closing_purchasing->num_rows() > 0){
					$dt_all_closing_purchasing_store = $get_all_store_closing_purchasing->row();
					$last_id_closing_purchasing_store = $dt_all_closing_purchasing_store->id;
					$total_data_store = $get_all_store_closing_purchasing->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_closing_purchasing_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Closing Purchasing ON STORE id > $last_id_on_backup
				$closing_purchasing_store = array();
				$all_closing_purchasing = array();
				$get_store_closing_purchasing = $this->db->query("SELECT * FROM ".$this->prefix_pos."closing_purchasing WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_closing_purchasing->num_rows() > 0){
					
					foreach($get_store_closing_purchasing->result() as $dt){
						
						$closing_purchasing_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_closing_purchasing)){
							$all_closing_purchasing[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_closing_purchasing_store;
				}
				
				//NEXT DATA
				if($last_id_closing_purchasing_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_closing_purchasing_store'] = $last_id_closing_purchasing_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['closing_purchasing_store'] = json_encode($closing_purchasing_store);
				$post_data['all_closing_purchasing'] = json_encode($all_closing_purchasing);
				
				break;
				
			case 'closing_inventory':
				$backup_text = 'Closing Inventory';
				
				//Closing Inventory ON STORE
				$last_id_closing_inventory_store = 0;
				$total_data_store = 0;
				$get_all_store_closing_inventory = $this->db->query("SELECT id FROM ".$this->prefix_pos."closing_inventory ORDER BY id DESC");
				if($get_all_store_closing_inventory->num_rows() > 0){
					$dt_all_closing_inventory_store = $get_all_store_closing_inventory->row();
					$last_id_closing_inventory_store = $dt_all_closing_inventory_store->id;
					$total_data_store = $get_all_store_closing_inventory->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_closing_inventory_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Closing Inventory ON STORE id > $last_id_on_backup
				$closing_inventory_store = array();
				$all_closing_inventory = array();
				$get_store_closing_inventory = $this->db->query("SELECT * FROM ".$this->prefix_pos."closing_inventory WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_closing_inventory->num_rows() > 0){
					
					foreach($get_store_closing_inventory->result() as $dt){
						
						$closing_inventory_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_closing_inventory)){
							$all_closing_inventory[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_closing_inventory_store;
				}
				
				//NEXT DATA
				if($last_id_closing_inventory_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_closing_inventory_store'] = $last_id_closing_inventory_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['closing_inventory_store'] = json_encode($closing_inventory_store);
				$post_data['all_closing_inventory'] = json_encode($all_closing_inventory);
				
				break;
				
			case 'closing_log':
				$backup_text = 'Closing Log';
				
				//Closing Log ON STORE
				$last_id_closing_log_store = 0;
				$total_data_store = 0;
				$get_all_store_closing_log = $this->db->query("SELECT id FROM ".$this->prefix_pos."closing_log ORDER BY id DESC");
				if($get_all_store_closing_log->num_rows() > 0){
					$dt_all_closing_log_store = $get_all_store_closing_log->row();
					$last_id_closing_log_store = $dt_all_closing_log_store->id;
					$total_data_store = $get_all_store_closing_log->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_closing_log_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Closing Log ON STORE id > $last_id_on_backup
				$closing_log_store = array();
				$all_closing_log = array();
				$get_store_closing_log = $this->db->query("SELECT * FROM ".$this->prefix_pos."closing_log WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_closing_log->num_rows() > 0){
					
					foreach($get_store_closing_log->result() as $dt){
						
						$closing_log_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_closing_log)){
							$all_closing_log[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_closing_log_store;
				}
				
				//NEXT DATA
				if($last_id_closing_log_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_closing_log_store'] = $last_id_closing_log_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['closing_log_store'] = json_encode($closing_log_store);
				$post_data['all_closing_log'] = json_encode($all_closing_log);
				
				break;
			
			//Cashflow ---------------------
			case 'cashflow':
				$backup_text = 'Cashflow';
				
				$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b>', 'has_next' => 0);
				die(json_encode($r));
				
				break;
			
			case 'penerimaan_kas':
				$backup_text = 'Penerimaan Kas';
				
				//Penerimaan Kas ON STORE
				$last_id_penerimaan_kas_store = 0;
				$total_data_store = 0;
				$get_all_store_penerimaan_kas = $this->db->query("SELECT id FROM ".$this->prefix_acc."penerimaan_kas ORDER BY id DESC");
				if($get_all_store_penerimaan_kas->num_rows() > 0){
					$dt_all_penerimaan_kas_store = $get_all_store_penerimaan_kas->row();
					$last_id_penerimaan_kas_store = $dt_all_penerimaan_kas_store->id;
					$total_data_store = $get_all_store_penerimaan_kas->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_penerimaan_kas_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Penerimaan Kas ON STORE id > $last_id_on_backup
				$penerimaan_kas_store = array();
				$all_penerimaan_kas = array();
				$get_store_penerimaan_kas = $this->db->query("SELECT * FROM ".$this->prefix_acc."penerimaan_kas WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_penerimaan_kas->num_rows() > 0){
					
					foreach($get_store_penerimaan_kas->result() as $dt){
						
						$penerimaan_kas_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_penerimaan_kas)){
							$all_penerimaan_kas[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_penerimaan_kas_store;
				}
				
				//NEXT DATA
				if($last_id_penerimaan_kas_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_penerimaan_kas_store'] = $last_id_penerimaan_kas_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['penerimaan_kas_store'] = json_encode($penerimaan_kas_store);
				$post_data['all_penerimaan_kas'] = json_encode($all_penerimaan_kas);
				
				break;
				
			case 'pengeluaran_kas':
				$backup_text = 'Pengeluaran Kas';
				
				//Pengeluaran Kas ON STORE
				$last_id_pengeluaran_kas_store = 0;
				$total_data_store = 0;
				$get_all_store_pengeluaran_kas = $this->db->query("SELECT id FROM ".$this->prefix_acc."pengeluaran_kas ORDER BY id DESC");
				if($get_all_store_pengeluaran_kas->num_rows() > 0){
					$dt_all_pengeluaran_kas_store = $get_all_store_pengeluaran_kas->row();
					$last_id_pengeluaran_kas_store = $dt_all_pengeluaran_kas_store->id;
					$total_data_store = $get_all_store_pengeluaran_kas->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_pengeluaran_kas_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Pengeluaran Kas ON STORE id > $last_id_on_backup
				$pengeluaran_kas_store = array();
				$all_pengeluaran_kas = array();
				$get_store_pengeluaran_kas = $this->db->query("SELECT * FROM ".$this->prefix_acc."pengeluaran_kas WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_pengeluaran_kas->num_rows() > 0){
					
					foreach($get_store_pengeluaran_kas->result() as $dt){
						
						$pengeluaran_kas_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_pengeluaran_kas)){
							$all_pengeluaran_kas[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_pengeluaran_kas_store;
				}
				
				//NEXT DATA
				if($last_id_pengeluaran_kas_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_pengeluaran_kas_store'] = $last_id_pengeluaran_kas_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['pengeluaran_kas_store'] = json_encode($pengeluaran_kas_store);
				$post_data['all_pengeluaran_kas'] = json_encode($all_pengeluaran_kas);
				
				break;
			
			case 'mutasi_kas_bank':
				$backup_text = 'Mutasi Kas Bank';
				
				//Mutasi Kas Bank ON STORE
				$last_id_mutasi_kas_bank_store = 0;
				$total_data_store = 0;
				$get_all_store_mutasi_kas_bank = $this->db->query("SELECT id FROM ".$this->prefix_acc."mutasi_kas_bank ORDER BY id DESC");
				if($get_all_store_mutasi_kas_bank->num_rows() > 0){
					$dt_all_mutasi_kas_bank_store = $get_all_store_mutasi_kas_bank->row();
					$last_id_mutasi_kas_bank_store = $dt_all_mutasi_kas_bank_store->id;
					$total_data_store = $get_all_store_mutasi_kas_bank->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_mutasi_kas_bank_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Mutasi Kas Bank ON STORE id > $last_id_on_backup
				$mutasi_kas_bank_store = array();
				$all_mutasi_kas_bank = array();
				$get_store_mutasi_kas_bank = $this->db->query("SELECT * FROM ".$this->prefix_acc."mutasi_kas_bank WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_mutasi_kas_bank->num_rows() > 0){
					
					foreach($get_store_mutasi_kas_bank->result() as $dt){
						
						$mutasi_kas_bank_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_mutasi_kas_bank)){
							$all_mutasi_kas_bank[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_mutasi_kas_bank_store;
				}
				
				//NEXT DATA
				if($last_id_mutasi_kas_bank_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_mutasi_kas_bank_store'] = $last_id_mutasi_kas_bank_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['mutasi_kas_bank_store'] = json_encode($mutasi_kas_bank_store);
				$post_data['all_mutasi_kas_bank'] = json_encode($all_mutasi_kas_bank);
				
				break;
				
			//ACCOUNT PAYABLE ---------------------
			case 'account_payable':
				$backup_text = 'Account Payable';
				
				//Account Payable ON STORE
				$last_id_account_payable_store = 0;
				$total_data_store = 0;
				$get_all_store_account_payable = $this->db->query("SELECT id FROM ".$this->prefix_acc."account_payable ORDER BY id DESC");
				if($get_all_store_account_payable->num_rows() > 0){
					$dt_all_account_payable_store = $get_all_store_account_payable->row();
					$last_id_account_payable_store = $dt_all_account_payable_store->id;
					$total_data_store = $get_all_store_account_payable->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_account_payable_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Account Payable ON STORE id > $last_id_on_backup
				$account_payable_store = array();
				$all_account_payable = array();
				$get_store_account_payable = $this->db->query("SELECT * FROM ".$this->prefix_acc."account_payable WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_account_payable->num_rows() > 0){
					
					foreach($get_store_account_payable->result() as $dt){
						
						$account_payable_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_account_payable)){
							$all_account_payable[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_account_payable_store;
				}
				
				//NEXT DATA
				if($last_id_account_payable_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_account_payable_store'] = $last_id_account_payable_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['account_payable_store'] = json_encode($account_payable_store);
				$post_data['all_account_payable'] = json_encode($all_account_payable);
				
				break;
			
			case 'kontrabon':
				$backup_text = 'Kontrabon';
				
				//Kontrabon ON STORE
				$last_id_kontrabon_store = 0;
				$total_data_store = 0;
				$get_all_store_kontrabon = $this->db->query("SELECT id FROM ".$this->prefix_acc."kontrabon ORDER BY id DESC");
				if($get_all_store_kontrabon->num_rows() > 0){
					$dt_all_kontrabon_store = $get_all_store_kontrabon->row();
					$last_id_kontrabon_store = $dt_all_kontrabon_store->id;
					$total_data_store = $get_all_store_kontrabon->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_kontrabon_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Kontrabon ON STORE id > $last_id_on_backup
				$kontrabon_store = array();
				$all_kontrabon = array();
				$get_store_kontrabon = $this->db->query("SELECT * FROM ".$this->prefix_acc."kontrabon WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_kontrabon->num_rows() > 0){
					
					foreach($get_store_kontrabon->result() as $dt){
						
						$kontrabon_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_kontrabon)){
							$all_kontrabon[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_kontrabon_store;
				}
				
				//NEXT DATA
				if($last_id_kontrabon_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_kontrabon_store'] = $last_id_kontrabon_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['kontrabon_store'] = json_encode($kontrabon_store);
				$post_data['all_kontrabon'] = json_encode($all_kontrabon);
				
				break;
				
			case 'kontrabon_detail':
				$backup_text = 'Kontrabon Detail';
				
				//Kontrabon Detail ON STORE
				$last_id_kontrabon_detail_store = 0;
				$total_data_store = 0;
				$get_all_store_kontrabon_detail = $this->db->query("SELECT id FROM ".$this->prefix_acc."kontrabon_detail ORDER BY id DESC");
				if($get_all_store_kontrabon_detail->num_rows() > 0){
					$dt_all_kontrabon_detail_store = $get_all_store_kontrabon_detail->row();
					$last_id_kontrabon_detail_store = $dt_all_kontrabon_detail_store->id;
					$total_data_store = $get_all_store_kontrabon_detail->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_kontrabon_detail_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Kontrabon Detail ON STORE id > $last_id_on_backup
				$kontrabon_detail_store = array();
				$all_kontrabon_detail = array();
				$get_store_kontrabon_detail = $this->db->query("SELECT * FROM ".$this->prefix_acc."kontrabon_detail WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_kontrabon_detail->num_rows() > 0){
					
					foreach($get_store_kontrabon_detail->result() as $dt){
						
						$kontrabon_detail_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_kontrabon_detail)){
							$all_kontrabon_detail[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_kontrabon_detail_store;
				}
				
				//NEXT DATA
				if($last_id_kontrabon_detail_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_kontrabon_detail_store'] = $last_id_kontrabon_detail_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['kontrabon_detail_store'] = json_encode($kontrabon_detail_store);
				$post_data['all_kontrabon_detail'] = json_encode($all_kontrabon_detail);
				
				break;
					
			case 'pelunasan_ap':
				$backup_text = 'Pelunasan AP';
				
				//Pelunasan AP ON STORE
				$last_id_pelunasan_ap_store = 0;
				$total_data_store = 0;
				$get_all_store_pelunasan_ap = $this->db->query("SELECT id FROM ".$this->prefix_acc."pelunasan_ap ORDER BY id DESC");
				if($get_all_store_pelunasan_ap->num_rows() > 0){
					$dt_all_pelunasan_ap_store = $get_all_store_pelunasan_ap->row();
					$last_id_pelunasan_ap_store = $dt_all_pelunasan_ap_store->id;
					$total_data_store = $get_all_store_pelunasan_ap->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_pelunasan_ap_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Pelunasan AP ON STORE id > $last_id_on_backup
				$pelunasan_ap_store = array();
				$all_pelunasan_ap = array();
				$get_store_pelunasan_ap = $this->db->query("SELECT * FROM ".$this->prefix_acc."pelunasan_ap WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_pelunasan_ap->num_rows() > 0){
					
					foreach($get_store_pelunasan_ap->result() as $dt){
						
						$pelunasan_ap_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_pelunasan_ap)){
							$all_pelunasan_ap[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_pelunasan_ap_store;
				}
				
				//NEXT DATA
				if($last_id_pelunasan_ap_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_pelunasan_ap_store'] = $last_id_pelunasan_ap_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['pelunasan_ap_store'] = json_encode($pelunasan_ap_store);
				$post_data['all_pelunasan_ap'] = json_encode($all_pelunasan_ap);
				
				break;
			
			//ACCOUNT RECEIVABLE
			case 'account_receivable':
				$backup_text = 'Account Receivable';
				
				//Account Receivable ON STORE
				$last_id_account_receivable_store = 0;
				$total_data_store = 0;
				$get_all_store_account_receivable = $this->db->query("SELECT id FROM ".$this->prefix_acc."account_receivable ORDER BY id DESC");
				if($get_all_store_account_receivable->num_rows() > 0){
					$dt_all_account_receivable_store = $get_all_store_account_receivable->row();
					$last_id_account_receivable_store = $dt_all_account_receivable_store->id;
					$total_data_store = $get_all_store_account_receivable->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_account_receivable_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Account Receivable ON STORE id > $last_id_on_backup
				$account_receivable_store = array();
				$all_account_receivable = array();
				$get_store_account_receivable = $this->db->query("SELECT * FROM ".$this->prefix_acc."account_receivable WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_account_receivable->num_rows() > 0){
					
					foreach($get_store_account_receivable->result() as $dt){
						
						$account_receivable_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_account_receivable)){
							$all_account_receivable[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_account_receivable_store;
				}
				
				//NEXT DATA
				if($last_id_account_receivable_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_account_receivable_store'] = $last_id_account_receivable_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['account_receivable_store'] = json_encode($account_receivable_store);
				$post_data['all_account_receivable'] = json_encode($all_account_receivable);
				
				break;
			
			case 'invoice':
				$backup_text = 'Invoice';
				
				//Invoice ON STORE
				$last_id_invoice_store = 0;
				$total_data_store = 0;
				$get_all_store_invoice = $this->db->query("SELECT id FROM ".$this->prefix_acc."invoice ORDER BY id DESC");
				if($get_all_store_invoice->num_rows() > 0){
					$dt_all_invoice_store = $get_all_store_invoice->row();
					$last_id_invoice_store = $dt_all_invoice_store->id;
					$total_data_store = $get_all_store_invoice->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_invoice_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Invoice ON STORE id > $last_id_on_backup
				$invoice_store = array();
				$all_invoice = array();
				$get_store_invoice = $this->db->query("SELECT * FROM ".$this->prefix_acc."invoice WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_invoice->num_rows() > 0){
					
					foreach($get_store_invoice->result() as $dt){
						
						$invoice_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_invoice)){
							$all_invoice[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_invoice_store;
				}
				
				//NEXT DATA
				if($last_id_invoice_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_invoice_store'] = $last_id_invoice_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['invoice_store'] = json_encode($invoice_store);
				$post_data['all_invoice'] = json_encode($all_invoice);
				
				break;
				
			case 'invoice_detail':
				$backup_text = 'Invoice Detail';
				
				//Invoice Detail ON STORE
				$last_id_invoice_detail_store = 0;
				$total_data_store = 0;
				$get_all_store_invoice_detail = $this->db->query("SELECT id FROM ".$this->prefix_acc."invoice_detail ORDER BY id DESC");
				if($get_all_store_invoice_detail->num_rows() > 0){
					$dt_all_invoice_detail_store = $get_all_store_invoice_detail->row();
					$last_id_invoice_detail_store = $dt_all_invoice_detail_store->id;
					$total_data_store = $get_all_store_invoice_detail->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_invoice_detail_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Invoice Detail ON STORE id > $last_id_on_backup
				$invoice_detail_store = array();
				$all_invoice_detail = array();
				$get_store_invoice_detail = $this->db->query("SELECT * FROM ".$this->prefix_acc."invoice_detail WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_invoice_detail->num_rows() > 0){
					
					foreach($get_store_invoice_detail->result() as $dt){
						
						$invoice_detail_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_invoice_detail)){
							$all_invoice_detail[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_invoice_detail_store;
				}
				
				//NEXT DATA
				if($last_id_invoice_detail_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_invoice_detail_store'] = $last_id_invoice_detail_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['invoice_detail_store'] = json_encode($invoice_detail_store);
				$post_data['all_invoice_detail'] = json_encode($all_invoice_detail);
				
				break;
					
			case 'pembayaran_ar':
				$backup_text = 'Pembayaran AR';
				
				//Pembayaran AR ON STORE
				$last_id_pembayaran_ar_store = 0;
				$total_data_store = 0;
				$get_all_store_pembayaran_ar = $this->db->query("SELECT id FROM ".$this->prefix_acc."pembayaran_ar ORDER BY id DESC");
				if($get_all_store_pembayaran_ar->num_rows() > 0){
					$dt_all_pembayaran_ar_store = $get_all_store_pembayaran_ar->row();
					$last_id_pembayaran_ar_store = $dt_all_pembayaran_ar_store->id;
					$total_data_store = $get_all_store_pembayaran_ar->num_rows();
				}
				
				$last_id_store = 0;
				$total_data_store_detail = 0;
				$last_id_store_detail = 0;
				
				if($last_id_pembayaran_ar_store == $last_id_on_backup){
					$r = array('success' => true, 'info' => 'Backup Data: <b>'.$backup_text.'</b> Updated!', 'has_next' => 0);
					die(json_encode($r));
				}
				
				//Pembayaran AR ON STORE id > $last_id_on_backup
				$pembayaran_ar_store = array();
				$all_pembayaran_ar = array();
				$get_store_pembayaran_ar = $this->db->query("SELECT * FROM ".$this->prefix_acc."pembayaran_ar WHERE id > ".$last_id_on_backup." ORDER BY id ASC LIMIT ".$limit_backup_data);
				if($get_store_pembayaran_ar->num_rows() > 0){
					
					foreach($get_store_pembayaran_ar->result() as $dt){
						
						$pembayaran_ar_store[] = (array) $dt;
						
						if(!in_array($dt->id, $all_pembayaran_ar)){
							$all_pembayaran_ar[] = $dt->id;
						}
						
						$last_id_store = $dt->id;
					}
				}
				
				if(empty($last_id_store)){
					$last_id_store = $last_id_pembayaran_ar_store;
				}
				
				//NEXT DATA
				if($last_id_pembayaran_ar_store > $last_id_store){
					$has_next = 1;
				}
				
				$post_data['last_id_pembayaran_ar_store'] = $last_id_pembayaran_ar_store;
				$post_data['total_data_store'] = $total_data_store;
				$post_data['last_id_store'] = $last_id_store;
				$post_data['pembayaran_ar_store'] = json_encode($pembayaran_ar_store);
				$post_data['all_pembayaran_ar'] = json_encode($all_pembayaran_ar);
				
				break;
				
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
			$r = array('success' => false, 'info' => 'Backup Transaksi: '.$backup_text.' Gagal!', 'has_next' => 0);
			die(json_encode($r));
		}else{
			$backup_status = true;
			
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
		
		if(!empty($return_data['last_id_on_backup'])){
			$last_id_on_backup = $return_data['last_id_on_backup'];
		}
		
		if(!empty($return_data['total_data_on_backup'])){
			$total_data_on_backup = $return_data['total_data_on_backup'];
		}
		
		if($backup_status){
			
			$r = array(
				'success' => true, 
				'info'	=> 'Backup Transaksi <b>'.$backup_text.'</b>: '.$total_data_store.' Data - #'.$last_id_store.'..', 
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
				'info'	=> 'Backup Transaksi: <b>'.$backup_text.'</b> - Updated..',
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
			'backup_masterdata' => 0,
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