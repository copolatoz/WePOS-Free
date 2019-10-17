<?php

class Mdl_login extends CI_Model {
	
	function __construct()
	{
		parent::__construct();	
	}

	function submit($username, $password, $store_data = '', $mkey = '', $from_apps = 0)
	{
		$errors = '';
		$role_id_kasir = 0;
		if(!empty($from_apps)){
			$opt_value = array(
				'role_id_kasir'
			);
			
			$get_opt = get_option_value($opt_value);
			if(!empty($get_opt['role_id_kasir'])){
				$role_id_kasir = $get_opt['role_id_kasir'];
			}
		}
		
		$prefix	= config_item('db_prefix');
		$this->db->select('id as id_user, client_id');
		$this->db->from($prefix.'users');
		$this->db->where('user_username',$username);
		$this->db->where('user_password',md5($password));
		$this->db->where('is_active', "1");
		if(!empty($role_id_kasir)){
			$this->db->where("role_id IN (".$role_id_kasir.")");
		}
		
		$query 	= 	$this->db->get();
		//log_message('INFO', 'QUERY: '.$this->db->last_query());
		$c = $query->num_rows();
		
		$d = array();
		if($c > 0)
		{
			$row = $query->row();
			$d = $this->get_client($row->client_id, $row->id_user, $mkey);
			$d->is_cloud = $mkey;
			$d->client_ip = '';
			$d->mysql_user = '';
			$d->mysql_pass = '';
			$d->mysql_port = '';
			$d->mysql_database = '';
			$d->view_multiple_store = 0;
			$d->from_apps = $from_apps;

			if(!empty($store_data)){
				$d->client_ip = $store_data[0];
				$d->mysql_user = $store_data[1];
				$d->mysql_pass = $store_data[2];
				$d->mysql_port = $store_data[3];
				$d->mysql_database = $store_data[4];
				$d->view_multiple_store = $store_data[5];
			}
		}else{
			$errors = array('reason'=>'<font color=red><b>Login Failed..<br/>Try Again..</b></font>');
			if(!empty($role_id_kasir)){
				$errors = array('reason'=>'<font color=red><b>Login Cashier Failed..<br/>Try Again..</b></font>');
			}
		}
		
		return array(
			'count' 	=> $c,
			'data'		=> $d,
			'store_data'=> $store_data,
			'errors'	=> $errors
		);
	}
	

	function submit_pin($user_pin = '-1', $store_data = '', $mkey = '', $from_apps = 0)
	{
		$errors = '';
		$role_id_kasir = 0;
		if(!empty($from_apps)){
			$opt_value = array(
				'role_id_kasir'
			);
			
			$get_opt = get_option_value($opt_value);
			if(!empty($get_opt['role_id_kasir'])){
				$role_id_kasir = $get_opt['role_id_kasir'];
			}
		}
		
		$prefix	= config_item('db_prefix');
		$this->db->select('id as id_user, client_id, role_id');
		$this->db->from($prefix.'users');
		$this->db->where('user_pin',$user_pin);
		$this->db->where('is_active', "1");
		if(!empty($role_id_kasir)){
			$this->db->where("role_id IN (".$role_id_kasir.")");
		}
		
		$query 	= 	$this->db->get();
		$c = $query->num_rows();
		//log_message('INFO', 'QUERY: '.$this->db->last_query());
		//log_message('INFO', 'TOTAL: '.$query->num_rows());
		
		$d = array();
		if($c > 0)
		{
			$row = $query->row();
			$d = $this->get_client($row->client_id, $row->id_user, $mkey);
			$d->is_cloud = $mkey;
			$d->client_ip = '';
			$d->mysql_user = '';
			$d->mysql_pass = '';
			$d->mysql_port = '';
			$d->mysql_database = '';
			$d->view_multiple_store = 0;
			$d->from_apps = $from_apps;
			
			if(!empty($store_data)){
				
				$d->client_ip = $store_data[0];
				$d->mysql_user = $store_data[1];
				$d->mysql_pass = $store_data[2];
				$d->mysql_port = $store_data[3];
				$d->mysql_database = $store_data[4];
				$d->view_multiple_store = $store_data[5];
				
				if($d->client_ip == '127.0.0.1'){
					$d->client_ip = 'localhost';
				}
				if($d->mysql_port == ''){
					$d->mysql_port = '3306';
				}
				
			}
		}else{
			$errors = array('reason'=>'<font color=red><b>Login Failed..<br/>Try Again..</b></font>');
			if(!empty($role_id_kasir)){
				$errors = array('reason'=>'<font color=red><b>Login Cashier Failed..<br/>Try Again..</b></font>');
			}
		}
		
		return array(
			'count' 	=> $c,
			'data'		=> $d,
			'store_data'=> $store_data,
			'errors'	=> $errors
		);
	}
	
	function get_client($id_client = 0, $id_user=0, $mkey = '')
	{
		//UNIT KERJA
		$prefix	=	config_item('db_prefix');
		$this->db->select("a.id as id_user,
				a.user_username,
				a.user_pin,
				a.user_firstname,
				a.user_lastname,
				a.client_id,
				a.client_structure_id,
				a.role_id,
				b.client_name,
				b.client_address,
				b.client_phone,
				b.client_fax,
				b.client_email,
				b.client_code,
				b.client_logo,
				b2.client_structure_name,	
				b2.client_unit_id,			
				b3.client_unit_name,
				b3.client_unit_code,
				c.role_name", false);
		$this->db->from($prefix.'users as a');
		$this->db->join($prefix.'clients as b','b.id = a.client_id','LEFT');
		$this->db->join($prefix.'clients_structure as b2','b2.id = a.client_structure_id','LEFT');
		$this->db->join($prefix.'clients_unit as b3','b3.id = b2.client_unit_id','LEFT');
		$this->db->join($prefix.'roles as c','c.id = a.role_id','LEFT');
		$this->db->where('a.client_id',$id_client);
		$this->db->where('a.id',$id_user);
		$this->db->where('a.is_active',1);
		$q = $this->db->get();
		//log_message('INFO', 'QUERY: '.$this->db->last_query());
		
		$ret_data = '';
		if($q->num_rows() > 0){
			$ret_data = $q->row();
			$ret_data->user_fullname = $ret_data->user_firstname.' '.$ret_data->user_lastname;
			
			$opt_val = array(
				'merchant_key'
			);
			
			$get_opt = get_option_value($opt_val);
			
			if(($ret_data->client_code == '' OR empty($get_opt['merchant_key'])) AND !empty($mkey)){
				//update code
				$opt_val = array(
					'merchant_key' => $mkey
				);
				update_option($opt_val);
				
				$update_client = array('client_code' => $mkey);
				$this->db->update($prefix.'clients', $update_client, "id = ".$ret_data->client_id);
				
				$ret_data->client_code = $mkey;
				
			}
			
		}
		
		return $ret_data;
	}
	
	function get_masterstore()
	{
		$prefix	= config_item('db_prefix');
		$this->db->select('*');
		$this->db->from($prefix.'clients');
		$this->db->where('is_deleted', "0");
		
		$query 	= 	$this->db->get();
		//log_message('INFO', 'QUERY: '.$this->db->last_query());
		$c = $query->num_rows();
		
		$d = array();
		if($c > 0)
		{
			$d = $query->result_array();
		} 
		
		return $d;
	}
}
