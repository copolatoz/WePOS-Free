<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Ppob extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix');
		$this->load->model('model_ppob', 'm');
				
	}

	public function index()
	{
		echo '';
	}
	
	public function storeInfo($is_return = false)
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
			if(!empty($is_return)){
				return $r;
			}
			die(json_encode($r));
		}
		
		//OPT-OPTIONS
		$opt_val = array(
			'store_connected_id','management_systems','ipserver_management_systems','store_connected_code',
			'store_connected_name','store_connected_email','store_connected_phone',
			'use_wms','as_server_backup','use_ppob','ppob_key'
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
		if(empty($get_opt['use_ppob'])){
			$get_opt['use_ppob'] = 0;
		}
		if(empty($get_opt['ppob_key'])){
			$get_opt['ppob_key'] = 0;
		}
		/*
		if(empty($get_opt['management_systems'])){
			$r = array('success' => false, 'info' => 'Cek Setup Aplikasi, aktifkan pilihan: <b>Backup Data ke Server</b>');
			if(!empty($is_return)){
				return $r;
			}
			die(json_encode($r));
		}
		
		if(empty($get_opt['ipserver_management_systems'])){
			$r = array('success' => false, 'info' => 'Cek Setup Aplikasi, IP server belum disesuaikan');
			if(!empty($is_return)){
				return $r;
			}
			die(json_encode($r));
		}
		*/
		
		//cek_server_backup($get_opt);
		
		$store_connected_id = $get_opt['store_connected_id'];
		$management_systems = $get_opt['management_systems'];
		$ipserver_management_systems = $get_opt['ipserver_management_systems'];
		$store_connected_code = $get_opt['store_connected_code'];
		$store_connected_name = $get_opt['store_connected_name'];
		$store_connected_email = $get_opt['store_connected_email'];
		$store_connected_phone = $get_opt['store_connected_phone'];
		$use_wms = $get_opt['use_wms'];
		$use_ppob = $get_opt['use_ppob'];
		$ppob_key = $get_opt['ppob_key'];
		
		//CONNECTED TO STORE MANAGEMENT - CURL
		$this->load->library('curl');
		
		$is_connected = 0;
		$mktime_dc = strtotime(date("d-m-Y H:i:s"));
		
		$ipserver_management_systems = prep_url($ipserver_management_systems);
		
		//wepos.id
		$client_url = config_item('website').'/merchant/checkPPOB?_dc='.$mktime_dc;
		
		$post_data = array(
			'client_code' => $data_client['client_code'],
			'client_name' => $data_client['client_name'],
			'client_email' => $data_client['client_email'],
			'client_phone' => $data_client['client_phone']
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
				if(!empty($is_return)){
					return $r;
				}
				die(json_encode($r));
			}else{
				$ret_data = json_decode($curl_ret, true);
			
				if(!empty($ret_data['data']) AND $ret_data['success'] == true){
					$store_connected_id = $ret_data['data']['id'];
					$store_connected_code = $ret_data['data']['client_code'];
					$store_connected_name = $ret_data['data']['client_name'];
					$store_connected_email = $ret_data['data']['client_email'];
					$store_connected_phone = $ret_data['data']['client_phone'];
					$use_ppob = $ret_data['data']['use_ppob'];
					$ppob_key = $ret_data['data']['ppob_key'];
					$data_client['client_name'] = $ret_data['data']['client_name'];
					$data_client['client_email'] = $ret_data['data']['client_email'];
					$data_client['client_phone'] = $ret_data['data']['client_phone'];
					$data_client['client_address'] = $ret_data['data']['client_address'];
					$data_client['client_ip'] = $ret_data['data']['client_ip'];
					$data_client['mysql_port'] = $ret_data['data']['mysql_port'];
					$data_client['mysql_database'] = $ret_data['data']['mysql_database'];
					$data_client['use_ppob'] = $ret_data['data']['use_ppob'];
					$data_client['ppob_key'] = $ret_data['data']['ppob_key'];
					$is_connected = 1;
				}else{
					$r = array('success' => false, 'info' => $ret_data['info']);
					if(!empty($is_return)){
						return $r;
					}
					die(json_encode($r));
				}
				
			}
			
			
		}else{
			$r = array('success' => false, 'info' => 'Data Store/Client: <b>'.$data_client['client_code'].' &mdash; '.$data_client['client_name'].'</b> Tidak teridentifikasi di Server!');
			if(!empty($is_return)){
				return $r;
			}
			die(json_encode($r));
		}
		
		if($store_connected_id != $get_opt['store_connected_id'] OR $store_connected_code != $get_opt['store_connected_code']){
			$get_opt['store_connected_id'] = $store_connected_id;
			$get_opt['store_connected_code'] = $store_connected_code;
			$get_opt['store_connected_name'] = $store_connected_name;
			$get_opt['store_connected_email'] = $store_connected_email;
			$get_opt['store_connected_phone'] = $store_connected_phone;
			$get_opt['use_ppob'] = $use_ppob;
			$get_opt['ppob_key'] = $ppob_key;
			
			//update options
			$update_option = update_option($get_opt);
		}
		
		
		$store_connected_id_show = '-';
		if(!empty($store_connected_id)){
			$store_connected_id_show = $store_connected_id;
		}
		
		
		$r = array(
			'success' => true, 
			'info' => 'Store/Client: '.$store_connected_id, 
			'store_connected_id' => $store_connected_id, 
			'store_connected_code' => $store_connected_code, 
			'store_connected_name' => $store_connected_name, 
			'store_connected_email' => $store_connected_email, 
			'store_connected_phone' => $store_connected_phone, 
			'use_ppob' => $use_ppob, 
			'ppob_key' => $ppob_key, 
			'is_connected' => $is_connected,
		);
		
		if(!empty($is_return)){
			return $r;
		}
		
		die(json_encode($r));
	}
	
}