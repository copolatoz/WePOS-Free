<?php

class Mdl_login extends CI_Model {
	
	function __construct()
	{
		parent::__construct();	
	}

	function submit($username, $password)
	{
		$prefix	= config_item('db_prefix');
		$this->db->select('id as id_user, client_id');
		$this->db->from($prefix.'users');
		$this->db->where('user_username',$username);
		$this->db->where('user_password',md5($password));
		$this->db->where('is_active', "1");
		
		$query 	= 	$this->db->get();
		//log_message('INFO', 'QUERY: '.$this->db->last_query());
		$c = $query->num_rows();
		
		$d = array();
		if($c > 0)
		{
			$row = $query->row();
			$d = $this->get_client($row->client_id, $row->id_user);
		} 
		
		return array(
			'count' => $c,
			'data'	=> $d
		);
	}
	

	function submit_pin($user_pin = '-1')
	{
		$prefix	= config_item('db_prefix');
		$this->db->select('id as id_user, client_id');
		$this->db->from($prefix.'users');
		$this->db->where('user_pin',$user_pin);
		$this->db->where('is_active', "1");
		
		$query 	= 	$this->db->get();
		//log_message('INFO', 'QUERY: '.$this->db->last_query());
		$c = $query->num_rows();
		
		$d = array();
		if($c > 0)
		{
			$row = $query->row();
			$d = $this->get_client($row->client_id, $row->id_user);
		} 
		
		return array(
			'count' => $c,
			'data'	=> $d
		);
	}
	
	function get_client($id_client = 0, $id_user=0)
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
		}
		
		return $ret_data;
	}
	
}
