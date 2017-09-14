<?php

class Mdl_Backend extends DB_Model {
	
	function __construct()
	{
		parent::__construct();
		
	}
	
	function getVar()
	{
		$prefix = $this->prefix;
		
		$varReturn = array();
		
		//Laporan Param
		$this->db->select("option_var, option_value");
		$this->db->from($prefix."options");
		$this->db->where("option_var IN ('laporan_header_title','laporan_header_title2','laporan_header_alamat',
		'laporan_header_alamat','laporan_header_fax','laporan_header_telepon','laporan_header_email','laporan_header_logo',
		'laporan_sign_tempat')");
		$get_lap_param = $this->db->get();
		
		if($get_lap_param->num_rows() > 0){
			foreach($get_lap_param->result() as $dt_param){
				$varReturn[$dt_param->option_var] = $dt_param->option_value;
			}
		}
		
		//auto check data and return result
		return $varReturn;
		
	}
	
}
