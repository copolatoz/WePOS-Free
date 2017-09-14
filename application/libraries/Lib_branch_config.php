<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class lib_branch_config {
	
	var $CI;

	function __construct() 
	{
		$this->CI = get_instance();
	}
	
	function get_branch_config($v){
		
		$v_default = array(
			'key' => '', //branch config key',
			'client_id' => '' //'set or auto get branch id'
		);
		
		$getVal = array_merge($v_default, $v);
		
		$prefix = config_item('db_prefix');
		//$this->CI->db->select('branch_config_value');
		$this->CI->db->from($prefix.'client_config');
		
		if(!empty($getVal['key'])){
			$this->CI->db->where('client_config_key', $getVal['key']);
		}
		
		$this->CI->db->where('client_id', id_clean($getVal['client_id']));
		
		$q = $this->CI->db->get();
		
		if($q->num_rows() > 0){
			
			if($q->num_rows() == 1){
				$data = $q->row();
				return $data->branch_config_value;
			}else{
				return $q->result();
			}
			
		}else{
			return '';
		}
	}
	
}