<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class SendToEmail extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix');
		$this->prefix_pos = config_item('db_prefix2');
		$this->load->model('model_sendtoemail', 'm');
		$this->load->model('master_pos/model_mastercustomer', 'customer');
				
	}

	public function index()
	{
		//GET STORE INFO
		$this->table = $this->prefix.'clients';
		$this->table_billing = $this->prefix_pos.'billing';
		$this->table_print_monitoring = $this->prefix_pos.'print_monitoring';
		
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
			if(!empty($is_return)){
				return $r;
			}
			die(json_encode($r));
		}
		
		//OPT-OPTIONS
		$opt_val = array(
			'store_connected_id','management_systems','ipserver_management_systems','store_connected_code',
			'store_connected_name','store_connected_email','store_connected_phone',
			'use_wms','as_server_backup','send_billing_to_email','save_email_to_customer','sms_notifikasi'
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
		if(empty($get_opt['store_connected_phone'])){
			$get_opt['store_connected_phone'] = 0;
		}
		if(empty($get_opt['use_wms'])){
			$get_opt['use_wms'] = 0;
		}
		if(empty($get_opt['send_billing_to_email'])){
			$get_opt['send_billing_to_email'] = 0;
		}
		if(empty($get_opt['save_email_to_customer'])){
			$get_opt['save_email_to_customer'] = 0;
		}
		if(empty($get_opt['sms_notifikasi'])){
			$get_opt['sms_notifikasi'] = 0;
		}
		
		$sms_notifikasi = $get_opt['sms_notifikasi'];
		
		//cek_server_backup($get_opt);
		
		$store_connected_id = $get_opt['store_connected_id'];
		$management_systems = $get_opt['management_systems'];
		$ipserver_management_systems = $get_opt['ipserver_management_systems'];
		$store_connected_code = $get_opt['store_connected_code'];
		$store_connected_name = $get_opt['store_connected_name'];
		$store_connected_email = $get_opt['store_connected_email'];
		$store_connected_phone = $get_opt['store_connected_phone'];
		$use_wms = $get_opt['use_wms'];
		$send_billing_to_email = $get_opt['send_billing_to_email'];
		$save_email_to_customer = $get_opt['save_email_to_customer'];
		
		$billing_id = $this->input->post_get('billing_id');
		$billing_no = $this->input->post_get('billing_no');
		$email = $this->input->post_get('email');
		$nama = $this->input->post_get('nama');
		$phone = $this->input->post_get('phone');
		$printer_pin = $this->input->post_get('printer_pin');
		$monitoring_id = $this->input->post_get('monitoring_id');
		$raw_content = $this->input->post_get('raw_content');
		
		$html_content = '';
		if(!empty($monitoring_id)){
			$this->db->from($this->table_print_monitoring);
			$this->db->where("(id = '".$monitoring_id."' OR (tipe = 'email' AND billing_no = '".$billing_no."'))");
			$get_monitoring = $this->db->get();
			if($get_monitoring->num_rows() > 0){
				$dt_monitoring = $get_monitoring->row();
				$html_content = $dt_monitoring->receiptTxt;
				$printer_pin = $dt_monitoring->tipe_pin;
				
			}else{
				$r = array('success' => false, 'info' => 'Data billing tidak ditemukan!');
				die(json_encode($r));
			}
			
		}else{
			$r = array('success' => false, 'info' => 'Data billing tidak ditemukan!');
			die(json_encode($r));
		}
		
		if(!empty($save_email_to_customer)){
			
			$post_data_customer = array(
				'email' => $email,
				'nama' 	=> $nama,
				'phone' => $phone,
				'from_email' => 1,
			);
			
			$ret_customer = $this->customer->addUpdate($post_data_customer);
			if(!empty($ret_customer['id'])){
				$update_billing = array(
					'customer_id'	=> $ret_customer['id']
				);
				$this->db->update($this->table_billing, $update_billing, "id = ".$billing_id." AND (customer_id IS NULL OR customer_id = 0)");
			}
			
		}
		
		//CONNECTED TO STORE MANAGEMENT - CURL
		$this->load->library('curl');
		
		$is_connected = 0;
		$mktime_dc = strtotime(date("d-m-Y H:i:s"));
		
		$ipserver_management_systems = prep_url($ipserver_management_systems);
		
		//wepos.id
		$client_url = config_item('website').'/email/ereceipt?_dc='.$mktime_dc;
		
		$post_data = array(
			'client_code' => $data_client['client_code'],
			'client_name' => $data_client['client_name'],
			'client_email' => $data_client['client_email'],
			'client_phone' => $data_client['client_phone'],
			'billing_id' => $billing_id,
			'billing_no' => $billing_no,
			'email' => $email,
			'nama' => $nama,
			'phone' => $phone,
			'sms_notifikasi' => $sms_notifikasi,
			'printer_pin' => $printer_pin,
			'html_content' => $html_content,
			'raw_content' => $raw_content,
		);
		
		$crt_file = ASSETS_PATH.config_item('wepos_crt_file');
		
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
				
			}else{
				$ret_data = json_decode($curl_ret, true);
			
				if($ret_data['success'] == true){
					$r = array('success' => true, 'info' => $ret_data['info'], 'sms_notifikasi' => $sms_notifikasi, 'ret_data' => $ret_data);
				}else{
					$r = array('success' => false, 'info' => $ret_data['info']);
				}
				
			}
			
		}else{
			$r = array('success' => false, 'info' => 'Data Store/Client: <b>'.$data_client['client_code'].' &mdash; '.$data_client['client_name'].'</b> Tidak teridentifikasi di Server!');
		}
		
		if(!empty($is_return)){
			return $r;
		}
		
		die(json_encode($r));
	}
	
}