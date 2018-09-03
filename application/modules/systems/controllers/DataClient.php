<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class DataClient extends MY_Controller {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('model_DataClient', 'm');
	}

	public function gridData()
	{
		$prefix = $this->prefix;
		$this->table = $this->prefix.'clients';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> '*',
			'primary_key'	=> 'id',
			'table'			=> $this->table,
			'where'			=> array('is_deleted' => 0),
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
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'clients';				
		$session_user = $this->session->userdata('user_username');
		
		$client_code = $this->input->post('client_code');
		$client_name = $this->input->post('client_name');
		$client_address = $this->input->post('client_address');
		$client_logo = $this->input->post('client_logo');
		
		if(empty($client_code) OR empty($client_name)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		$is_active = 0;
		if(!empty($_POST['is_active'])){
			$is_active = 1;
		}
			
		$r = '';
		if($this->input->post('form_type_DataClient', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'id' 		=> 	null,  
				    'client_code'  	=> 	$client_code,
				    'client_name'  	=> 	$client_name,
					'client_address'	=>	$client_address,
					'client_logo'	=>	$client_logo,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'	=>	$is_active
				),
				'table'		=>  $this->table
			);	
			
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
				$q = $this->m->add($var);
				$insert_id = $this->m->get_insert_id();
			$this->lib_trans->commit();			
			if($q)
			{  
				$r = array('success' => true, 'id' => $insert_id); 
				
				//$verified = $this->weposID($insert_id);
				
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_DataClient', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'client_code'  	=> 	$client_code,
					'client_name'  	=> 	$client_name,
					'client_address'	=>	$client_address,
					'client_logo'	=>	$client_logo,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			//UPDATE
			$id = $this->input->post('id', true);
			$this->lib_trans->begin();
				$update = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				$r = array('success' => true, 'id' => $id);
				//$verified = $this->weposID($id);
			}  
			else
			{  
				$r = array('success' => false);
			}
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function delete()
	{
		$prefix = $this->prefix;
		$this->table = $this->prefix.'clients';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
		
		//Delete
		$this->db->where("id IN (".$sql_Id.")");
		$this->db->where("id != 1");
		$q = $this->db->delete($this->table);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Client Failed!'); 
        }
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
		if(empty($get_opt['produk_expired'])){
			$get_opt['produk_expired'] = 'unlimited';
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
			'merchant_cor_token' 	=> $get_opt['merchant_cor_token'],
			'merchant_acc_token' 	=> $get_opt['merchant_acc_token'],
			'merchant_mkt_token' 	=> $get_opt['merchant_mkt_token'],
			'produk_nama' 			=> $get_opt['produk_nama'],
			'produk_expired' 		=> $get_opt['produk_expired'],
			'merchant_verified' 	=> $dt->merchant_verified,
			'merchant_xid' 			=> $dt->merchant_xid
		);
		
		$this->m->checkClient($post_dt);
		
		$r = array('success' => true); 
		die(json_encode($r));
	}
	
	public function clientInfo()
	{
		$prefix = $this->prefix;
		$this->table = $this->prefix.'clients';
		
		$client_name = config_item('client_name');
		
		$opt_var = array(
			'wepos_tipe',
			'merchant_tipe',
			'merchant_key',
			'merchant_cor_token',
			'merchant_acc_token',
			'merchant_mkt_token',
			'produk_key',
			'produk_nama',
			'produk_expired',
			'share_membership');
			
		$get_opt = get_option_value($opt_var);
		
		$merchant_tipe = $get_opt['wepos_tipe'];
		if(!empty($get_opt['merchant_tipe'])){
			$merchant_tipe = $get_opt['merchant_tipe'];
		}
		
		$produk_nama = 'Gratis / Free';
		if(!empty($get_opt['produk_nama'])){
			$produk_nama = $get_opt['produk_nama'];
		}
		
		$produk_key = 'GFR-'.strtotime(date("d-m-Y"));
		if(!empty($get_opt['produk_key'])){
			$produk_key = $get_opt['produk_key'];
		}
		
		$produk_expired = 'unlimited';
		if(!empty($get_opt['produk_expired'])){
			$produk_expired = $get_opt['produk_expired'];
		}
		
		$produk_expired_show = $produk_expired;
		if($produk_expired_show == 'unlimited'){
			$produk_expired_show = 'Selamanya';
		}
		
		$data_client = array(
			'client_code'  	=> 	'FREE',
			'client_code_show'  	=> 	'FREE',
			'client_name'  	=> 	$client_name,
			'client_email'	=>	'',
			'client_phone'	=>	'',
			'client_address'	=>	'',
			'merchant_tipe'		=>	$merchant_tipe,
			'merchant_verified'	=>	'unverified',
			'merchant_verified_show'=>	'<font color="red"><b>Unverified</b></font>',
			'produk_nama'		=>	$produk_nama,
			'produk_key'		=>	$produk_key,
			'produk_expired'	=>	$produk_expired,
			'produk_expired_show'	=>	'<font color="blue"><b>'.$produk_expired_show.'</b></font>',
			'merchant_tipe_show'=>	'<font color="blue"><b>'.strtoupper($merchant_tipe).'</b></font>',
			'produk_nama_show'	=>	'<font color="blue"><b>'.$produk_nama.' ('.$produk_key.') </b></font>'
		);
		
		$r = array('success' => true, 'data' => $data_client, 'info' => 'Get Info Client Failed!'); 
		
		$this->db->from($this->table);
		$this->db->where("id = 1");
		$q = $this->db->get();
		if($q->num_rows() > 0)  
        {  
			$dt = $q->row();
			
			$dt->merchant_verified_show = '<font color="red"><b>Unverified</b></font>';
			if($dt->merchant_verified == 'verified'){
				$dt->merchant_verified_show = '<font color="green"><b>Verified</b></font>';
			}
			
			$data_client = array(
				'client_code'  	=> 	$dt->client_code,
				'client_code_show'  => 	$dt->client_code,
				'client_name'  	=> 	$dt->client_name,
				'client_email'	=>	$dt->client_email,
				'client_phone'	=>	$dt->client_phone,
				'client_address'	=>	$dt->client_address,
				'merchant_tipe'		=>	$merchant_tipe,
				'merchant_verified'	=>	$dt->merchant_verified,
				'merchant_verified_show'	=>	$dt->merchant_verified_show,
				'produk_nama'		=>	$produk_nama,
				'produk_key'		=>	$produk_key,
				'produk_expired'	=>	$produk_expired,
				'produk_expired_show'	=>	'<font color="green"><b>'.$produk_expired_show.'</b></font>',
				'merchant_tipe_show'=>	'<font color="green"><b>'.strtoupper($merchant_tipe).'</b></font>',
				'produk_nama_show'	=>	'<font color="green"><b>'.$produk_nama.' ('.$produk_key.') </b></font>'
			);
			
            $r = array('success' => true, 'data' => $data_client, 'info' => 'Get Info Client Success!'); 
        } 
		
		die(json_encode($r));
	}
	
	public function updateClientInfo()
	{
		$prefix = $this->prefix;
		$this->table = $this->prefix.'clients';
				
		$session_user = $this->session->userdata('user_username');
		
		$verify = $this->input->post('verify');
		$client_code = $this->input->post('client_code');
		$client_name = $this->input->post('client_name');
		$client_email = $this->input->post('client_email');
		$client_phone = $this->input->post('client_phone');
		$client_address = $this->input->post('client_address');
		$merchant_tipe = $this->input->post('merchant_tipe');
		$merchant_xid = $this->input->post('merchant_xid');
		$merchant_verified = $this->input->post('merchant_verified');
		
		if(empty($client_email) OR empty($client_name) OR empty($client_phone)){
			$r = array('success' => false, "info" => "Update Info Failed!");
			die(json_encode($r));
		}		
		
		$var = array('fields'	=>	array(
				'client_code'  	=> 	$client_code,
				'client_name'  	=> 	$client_name,
				'client_email'  	=> 	$client_email,
				'client_phone'  	=> 	$client_phone,
				'client_address'	=>	$client_address,
				'updated'		=>	date('Y-m-d H:i:s'),
				'updatedby'		=>	$session_user
			),
			'table'			=>  $this->table,
			'primary_key'	=>  'id'
		);
		
		//UPDATE
		$id = 1;
		$this->lib_trans->begin();
			$update = $this->m->save($var, $id);
		$this->lib_trans->commit();
		
		$data_client = array(
			'client_code'  	=> 	$client_code,
			'client_name'  	=> 	$client_name,
			'client_email'  	=> 	$client_email,
			'client_phone'  	=> 	$client_phone,
			'client_address'	=>	$client_address,
		);
		
		
		$r = array('success' => true, 'data' => $data_client, 'info' => 'Save Client Info Failed!'); 
		if($update)
		{  
			if($verify == true){
				
				if(empty($client_code)){
					$r = array('success' => false, "info" => "Merchant Key Tidak Boleh Kosong!");
					die(json_encode($r));
				}
				
				$verified = $this->weposID($id, true);
				
				$produk_nama = 'Gratis / Free';
				$produk_key = 'GFR-'.strtotime(date("d-m-Y"));
				$produk_expired = 'unlimited';
				
				$produk_expired_show = $produk_expired;
				if($produk_expired_show == 'unlimited'){
					$produk_expired_show = 'Selamanya';
				}
				if(!empty($verified['data_option'])){
					$merchant_tipe = $verified['data_option']['merchant_tipe'];
					$produk_nama = $verified['data_option']['produk_nama'];
					$produk_key = $verified['data_option']['produk_key'];
					$produk_expired = $verified['data_option']['produk_expired'];
					$produk_expired_show = $verified['data_option']['produk_expired'];
				}
				
				if(!empty($verified['merchant_xid']) AND !empty($verified['data_option'])){
					
					$data_client['merchant_verified_show'] = $verified['merchant_verified_show'];
					$data_client['merchant_verified'] = $verified['merchant_verified'];
					$data_client['merchant_xid'] = $verified['merchant_xid'];
					$data_client['merchant_tipe'] = $merchant_tipe;
					$data_client['produk_nama'] = $produk_nama;
					$data_client['produk_key'] = $produk_key;
					$data_client['produk_expired'] = $produk_expired;
					$data_client['produk_expired_show'] = '<font color="green"><b>'.$produk_expired_show.'</b></font>';
					$data_client['merchant_tipe_show'] = '<font color="green"><b>'.strtoupper($merchant_tipe).'</b></font>';
					$data_client['produk_nama_show'] = '<font color="green"><b>'.$produk_nama.' ('.$produk_key.')</b></font>';
					
					$data_client['info_koneksi'] = '<font color="blue"><b>Merchant Terdaftar di WePOS.id</b></font>';
					
				}else{
					
					if(!empty($verified['merchant_verified'])){
						
						$data_client['merchant_verified_show'] = $verified['merchant_verified_show'];
						$data_client['merchant_verified'] = $verified['merchant_verified'];
						$data_client['merchant_xid'] = $verified['merchant_xid'];
						$data_client['merchant_tipe'] = $merchant_tipe;
						$data_client['produk_nama'] = $produk_nama;
						$data_client['produk_key'] = $produk_key;
						$data_client['produk_expired'] = $produk_expired;
						$data_client['produk_expired_show'] = '<font color="blue"><b>'.$produk_expired_show.'</b></font>';
						$data_client['merchant_tipe_show'] = '<font color="blue"><b>'.strtoupper($merchant_tipe).'</b></font>';
						$data_client['produk_nama_show'] = '<font color="blue"><b>'.$produk_nama.' ('.$produk_key.')</b></font>';
						
						$data_client['info_koneksi'] = '<font color="blue"><b>Merchant Terdaftar di WePOS.id</b></font>';
						if($verified['merchant_verified'] == 'unverified'){
							$data_client['info_koneksi'] = '<font color="red"><b>Merchant Key Tidak Terdaftar di WePOS.id</b></font>';
						}
						
					}else{
						$merchant_verified= 'unverified';
						$merchant_verified_show = '<font color="red"><b>'.ucwords($merchant_verified).'</b></font>';
						$data_client['merchant_verified_show'] = $merchant_verified_show;
						$data_client['merchant_verified'] = $merchant_verified;
						$data_client['merchant_xid'] = '';
						$data_client['merchant_tipe'] = $merchant_tipe;
						$data_client['produk_nama'] = $produk_nama;
						$data_client['produk_key'] = $produk_key;
						$data_client['produk_expired'] = $produk_expired;
						$data_client['produk_expired_show'] = '<font color="blue"><b>'.$produk_expired_show.'</b></font>';
						$data_client['merchant_tipe_show'] = '<font color="blue"><b>'.strtoupper($merchant_tipe).'</b></font>';
						$data_client['produk_nama_show'] = '<font color="blue"><b>'.$produk_nama.' ('.$produk_key.')</b></font>';
						
						$data_client['info_koneksi'] = '<font color="red"><b>Merchant Key Tidak Terdaftar di WePOS.id</b></font>';
					}
					
					if($verified == 'koneksi'){
						$data_client['info_koneksi'] = '<font color="red"><b>Koneksi ke WePOS.id Gagal!</b></font>';
					}
					if($verified == 'user'){
						$data_client['info_koneksi'] = '<font color="red"><b>Kode/Merchant Tidak Dikenali</b></font>';
					}
					
				}
				
			}
			
			$r = array('success' => true, 'data' => $data_client, 'info' => 'Client Info Updated!');
			
			
		}  
		
		die(json_encode($r));
	}
	
	public function notifcheck()
	{
		
		$prefix = $this->prefix;
		$this->table_options = $this->prefix.'options';
		$session_user = $this->session->userdata('user_username');
		
		$opt_val = array(
			'merchant_last_checkon',
			'merchant_last_check',
			'merchant_tipe',
			'merchant_key',
			'merchant_cor_token',
			'merchant_acc_token',
			'merchant_mkt_token',
			'produk_key',
			'produk_nama',
			'produk_expired',
			'share_membership'
		);
		
		$this->db->select('a.*');
		$this->db->from($this->prefix.'options as a');	
		$var_all = implode("','", $opt_val);
		$this->db->where("a.option_var IN ('".$var_all."')");
		
		$available_opt = array();
		$query = $this->db->get();
		if($query->num_rows() > 0){
			foreach($query->result() as $dt){
				$available_opt[] = $dt->option_var;
			}
		}
		
		//sure available_opt
		$opt_val_na = array();
		foreach($opt_val as $dto){
			if(!in_array($dto, $available_opt)){
				$opt_val_na[] = $dto;
			}
		}
		
		$insert_opt = array();
		if(!empty($opt_val_na)){
			foreach($opt_val_na as $dt){
				$insert_opt[] = array(
					'option_var' 	=>  $dt,
					'option_value'	=>  '',
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	1
				
				);
			}
			
			if(!empty($insert_opt)){
				$this->db->insert_batch($this->prefix.'options', $insert_opt);
			}
		}
		
	}
	
	public function weposID($id = 1, $is_return = false)
	{
		$prefix = $this->prefix;
		$this->table = $this->prefix.'clients';
		$session_user = $this->session->userdata('user_username');
		
		$opt_var = array('wepos_tipe');
		$get_opt = get_option_value($opt_var);
		$merchant_tipe = $get_opt['wepos_tipe'];
		
		$this->db->from($this->table);
		$this->db->where("id = $id");
		$q = $this->db->get();
		
		if($q->num_rows() > 0)  
        {  
			$dt = $q->row();
			
			$programName = config_item('program_name_short');
			$programVersion = config_item('program_version');
			$programRelease = config_item('program_release');
			$cloud_access = config_item('cloud_access');
			
			$this->load->library('curl');
			$mktime_dc = strtotime(date("d-m-Y H:i:s"));
			$client_url = config_item('website').'/client-info?_dc='.$mktime_dc;
			
			$post_data = array(
				'client_code'	=> $dt->client_code,
				'merchant_xid'	=> $dt->merchant_xid,
				'merchant_tipe'	=> $merchant_tipe,
				'client_name'	=> $dt->client_name,
				'client_phone'	=> $dt->client_phone,
				'client_email'	=> $dt->client_email,
				'programName'	=> $programName,
				'programVersion'	=> $programVersion,
				'programRelease'	=> $programRelease,
				'client_address'	=> $dt->client_address,
				'cloud_access'		=> $cloud_access
			);
			
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
			
			//notifcheck
			$this->notifcheck();
			
			$ret_data = json_decode($curl_ret, true);
			
			if(!empty($ret_data['success'])){
				
				if(!empty($ret_data['merchant_xid'])){
					$merchant_xid = $ret_data['merchant_xid'];
					$merchant_verified = $ret_data['merchant_verified'];
					
					$var = array('fields'	=>	array(
							'merchant_verified'	=> 	$merchant_verified,
							'merchant_xid'  	=> 	$merchant_xid,
							'updated'			=>	date('Y-m-d H:i:s'),
							'updatedby'			=>	'system'
						),
						'table'			=>  $this->table,
						'primary_key'	=>  'id'
					);
					$update = $this->m->save($var, $id);
					
					$merchant_verified_show = '<font color="red"><b>'.ucwords($merchant_verified).'</b></font>';
					if($merchant_verified == 'verified'){
						$merchant_verified_show = '<font color="green"><b>'.ucwords($merchant_verified).'</b></font>';
					}
					
					$force_update = false;
					if($is_return == true){
						$force_update = true;
					}
		
					$return_data = array(
						'merchant_xid'	=> $merchant_xid,
						'merchant_verified'	=> $merchant_verified,
						'merchant_verified_show'	=> $merchant_verified_show,
						'force_update'	=> $force_update,
					);
					
					//data_option
					if(!empty($ret_data['data_option'])){
						$update_option = update_option($ret_data['data_option']);
						$return_data['data_option'] = $ret_data['data_option'];
					}
					
					if(!function_exists('wepos_log_update')){
						$ret = $this->m->wepos_log_update($force_update);
					}else{
						$ret = wepos_log_update($force_update);
					}
					
					if($is_return){
						return $return_data;
					}
					
				}else{
					
					$merchant_xid = $ret_data['merchant_xid'];
					$merchant_verified = $ret_data['merchant_verified'];
					
					$var = array('fields'	=>	array(
							'merchant_verified'	=> 	$merchant_verified,
							'merchant_xid'  	=> 	$merchant_xid,
							'updated'			=>	date('Y-m-d H:i:s'),
							'updatedby'			=>	'system'
						),
						'table'			=>  $this->table,
						'primary_key'	=>  'id'
					);
					$update = $this->m->save($var, $id);
					
					$merchant_verified_show = '<font color="red"><b>'.ucwords($merchant_verified).'</b></font>';
					if($merchant_verified == 'verified'){
						$merchant_verified_show = '<font color="green"><b>'.ucwords($merchant_verified).'</b></font>';
					}
					
					$force_update = false;
					if($is_return == true){
						$force_update = true;
					}
		
					$return_data = array(
						'merchant_xid'	=> $merchant_xid,
						'merchant_verified'	=> $merchant_verified,
						'merchant_verified_show'	=> $merchant_verified_show,
						'force_update'	=> $force_update,
					);
					
					//data_option
					if(!empty($ret_data['data_option'])){
						$update_option = update_option($ret_data['data_option']);
						$return_data['data_option'] = $ret_data['data_option'];
					}
					
					if(!function_exists('wepos_log_update')){
						$ret = $this->m->wepos_log_update($force_update);
					}else{
						$ret = wepos_log_update($force_update);
					}
					
					if($is_return){
						return $return_data;
					}
				}
				
				if($is_return){
					return 'user';
				}
				
			}else{
				if($is_return){
					return 'koneksi';
				}
			}
		
			
        } 
		
		$r = array('success' => true); 
		die(json_encode($r));
	}
	
}