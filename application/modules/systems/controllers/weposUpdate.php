<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class WeposUpdate extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix');
		$this->prefix_pos = config_item('db_prefix2');
		$this->prefix_store = config_item('db_prefix3');
		$this->load->model('model_weposupdate', 'm');
	}

	public function check()
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
			'wepos_update_version', 'wepos_update_version2', 'wepos_connected_id', 'wepos_update_next_version', 'wepos_update_next_version2'
		);
		
		$get_opt = get_option_value($opt_val);
		
		
		$current_version = 0;
		if(!empty($get_opt['wepos_update_version'])){
			$current_version = $get_opt['wepos_update_version'];
		}
		
		$current_version2 = 0;
		if(!empty($get_opt['wepos_update_version2'])){
			$current_version2 = $get_opt['wepos_update_version2'];
		}
			
		if(empty($current_version)){
			$new_opt = array();
			$new_opt['wepos_update_version'] = 0;
			$new_opt['wepos_update_version2'] = 0;
			$update_option = update_option($new_opt);
		}
		
		if(empty($get_opt['wepos_connected_id'])){
			$get_opt['wepos_connected_id'] = 0;
		}
		
		//CONNECTED TO STORE MANAGEMENT - CURL
		$this->load->library('curl');
		
		$must_update = 0;
		$wepos_connected_id = $get_opt['wepos_connected_id'];
		$mktime_dc = strtotime(date("d-m-Y H:i:s"));
		
		$post_data = array(
			'client_code' => $data_client['client_code'],
			'client_name' => $data_client['client_name'],
			'current_version' => $current_version,
			'current_version2' => $current_version2,
		);
		
		$get_data = '';
		
		$client_url = config_item('website').'/wepos_update/check?_dc='.$mktime_dc.$get_data;
		
		$wepos_crt = ASSETS_PATH.config_item('wepos_crt_file');
		$this->curl->create($client_url);
		$this->curl->option('connecttimeout', 600);
		$this->curl->option('RETURNTRANSFER', 1);
		$this->curl->option('SSL_VERIFYPEER', 1);
		$this->curl->option('SSL_VERIFYHOST', 2);
		//$this->curl->option('SSLVERSION', 3);
		$this->curl->option('POST', 1);
		$this->curl->option('POSTFIELDS', $post_data);
		$this->curl->option('CAINFO', $wepos_crt);
		$curl_ret = $this->curl->execute();
		
		$info = '';
		$is_success = false;
		$must_update = 0;
		if(!empty($curl_ret)){
			
			if($curl_ret == 'Page Not Found!'){
				
				$r = array('success' => false, 'info' => 'Gagal Koneksi Ke Server!');
				die(json_encode($r));
				
			}else{
				$ret_data = json_decode($curl_ret, true);
			
				if(empty($ret_data['must_update'])){
					$ret_data['must_update'] = 0;
				}
				
				if(!empty($ret_data['data']) AND $ret_data['success'] == true){
					$wepos_connected_id = $ret_data['data']['id'];
					
					$must_update = $ret_data['must_update'];
					$info = $ret_data['info'];
					
					//save temporary update db
					if(!empty($ret_data['data']['update_version']) AND !empty($ret_data['data']['update_sql'])){
						$new_opt = array();
						$new_opt['wepos_update_'.$ret_data['data']['update_version']] = $ret_data['data']['update_sql'];
						$new_opt['wepos_update_next_version'] = $ret_data['data']['update_version'];
						$new_opt['wepos_update_next_version2'] = $ret_data['data']['update_version2'];
						$update_option = update_option($new_opt);
						$is_success = true;
					}
					
					if($must_update == 0){
						$is_success = false;
					}
					
				}else{
					$r = array('success' => $ret_data['success'], 'info' => $ret_data['info'], 'must_update' => $ret_data['must_update']);
					die(json_encode($r));
				}
				
			}
			
			
		}else{
			$r = array('success' => false, 'info' => 'Gagal Koneksi Ke Server!');
			die(json_encode($r));
		}
		
		if($wepos_connected_id == 0 AND $must_update == 1){
			$r = array('success' => false, 'info' => 'Data Store/Client Tidak Mendapat Update, Silahkan Hubungi Admin!');
			die(json_encode($r));
		}
		
		if($wepos_connected_id != $get_opt['wepos_connected_id']){
			$get_opt['wepos_connected_id'] = $wepos_connected_id;
			//update options
			$update_option = update_option($get_opt);
		}
		
		
		$r = array(
			'success' => $is_success, 
			'info' => $info, 
			'wepos_connected_id' => $wepos_connected_id, 
			'must_update' => $must_update, 
		);
		
		
		die(json_encode($r));
	}
	
	public function checkClient()
	{
		$this->table = $this->prefix.'clients';
		$opt_var = array(
			'merchant_key',
			'merchant_last_check',
			'merchant_cor_token',
			'merchant_acc_token',
			'merchant_mkt_token',
			'produk_nama',
			'produk_expired'
		);
		$get_opt = get_option_value($opt_var);
		
		if(empty($get_opt['merchant_key'])){
			$get_opt['merchant_key'] = '';
		}
		if(empty($get_opt['merchant_cor_token'])){
			$get_opt['merchant_cor_token'] = '';
		}
		if(empty($get_opt['merchant_acc_token'])){
			$get_opt['merchant_acc_token'] = '';
		}
		if(empty($get_opt['merchant_mkt_token'])){
			$get_opt['merchant_mkt_token'] = '';
		}
		if(empty($get_opt['produk_nama'])){
			$get_opt['produk_nama'] = 'Gratis / Free';
		}
		if(empty($get_opt['merchant_last_check'])){
			$get_opt['merchant_last_check'] = '0';
		}
		
		$this->db->from($this->table);
		$this->db->where("id = 1");
		$q = $this->db->get();
		if($q->num_rows() > 0)  
        { 
			$dt = $q->row();
			
		}else{
			$r = array('success' => true); 
			die(json_encode($r));
		}
		
		$post_dt = array(
			'merchant_key' 			=> $get_opt['merchant_key'],
			'merchant_last_check'	=> $get_opt['merchant_last_check'],
			'merchant_cor_token'	=> $get_opt['merchant_cor_token'],
			'merchant_acc_token'	=> $get_opt['merchant_acc_token'],
			'merchant_mkt_token'	=> $get_opt['merchant_mkt_token'],
			'produk_nama'			=> $get_opt['produk_nama'],
			'produk_expired'		=> $get_opt['produk_expired'],
			'merchant_verified'		=> $dt->merchant_verified,
			'merchant_xid'			=> $dt->merchant_xid
		);
		
		$this->m->checkClient($post_dt);
		
		$r = array('success' => true); 
		die(json_encode($r));
	}
	
	public function updateNow()
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
			'wepos_update_version', 'wepos_update_version2', 'wepos_connected_id', 'wepos_update_next_version', 'wepos_update_next_version2'
		);
		
		$get_opt = get_option_value($opt_val);
		
		
		$current_version = 0;
		if(!empty($get_opt['wepos_update_version'])){
			$current_version = $get_opt['wepos_update_version'];
		}
		
		$current_version2 = 0;
		if(!empty($get_opt['wepos_update_version2'])){
			$current_version2 = $get_opt['wepos_update_version2'];
		}
		
		if(empty($get_opt['wepos_connected_id'])){
			$get_opt['wepos_connected_id'] = 0;
		}
		
		if(empty($get_opt['wepos_update_next_version'])){
			$get_opt['wepos_update_next_version'] = 0;
			
			$r = array('success' => false, 'info' => 'Belum ada Update Terbaru');
			die(json_encode($r));
			
		}
		
		
		if($get_opt['wepos_update_version'] > $get_opt['wepos_update_next_version']){
			$r = array('success' => false, 'info' => 'Saat ini sudah menggunakan update terbaru<br/>Current Version: v.'.$get_opt['wepos_update_version2']);
			die(json_encode($r));
		}
		
		//GET SQL UPDATE
		$opt_val = array(
			'wepos_update_'.$get_opt['wepos_update_next_version'], 
		);
		
		$get_opt2 = get_option_value($opt_val);
		
		$data_update = '';
		if(!empty($get_opt2['wepos_update_'.$get_opt['wepos_update_next_version']])){
			$data_update = $get_opt2['wepos_update_'.$get_opt['wepos_update_next_version']];
		}
		
		
		if(empty($data_update)){
			$r = array('success' => false, 'info' => 'Update Gagal!<br/>Data Update tidak ditemukan');
			die(json_encode($r));
		}else{
			@$update_DB = $this->db->query($data_update);
			if($update_DB){
				
				//remove options sql
				$this->db->delete($this->prefix.'options',"option_var = 'wepos_update_".$get_opt['wepos_update_version']."'");
				
				$new_opt = array();
				$new_opt['wepos_update_version'] = $get_opt['wepos_update_next_version'];
				$new_opt['wepos_update_version2'] = $get_opt['wepos_update_next_version2'];
				$update_option = update_option($new_opt);
				
				$r = array('success' => true, 'info' => 'Sudah Ter-Update ke v.'.$get_opt['wepos_update_next_version2']);
				die(json_encode($r));
				
			}else{
				$r = array('success' => false, 'info' => 'Update Gagal!<br/>Data Update tidak ditemukan');
				die(json_encode($r));
			}
		}
		
		
	}
}