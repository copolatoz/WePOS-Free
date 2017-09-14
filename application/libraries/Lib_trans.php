<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class lib_trans{

	var $CI;

	function __construct() {
		$this->CI = get_instance();
	}
	
	function begin(){
		$this->CI->db->trans_begin();
	}
	
	function commit(){
		if ($this->CI->db->trans_status() === FALSE){
		    $this->CI->db->trans_rollback();
		}else{
		    $this->CI->db->trans_commit();
		}
	}
	
	function rollback(){
		$this->CI->db->trans_rollback();
	}
	
}