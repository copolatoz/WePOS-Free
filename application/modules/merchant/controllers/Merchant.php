<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Merchant extends MY_Controller {
	
	function __construct()
	{
		parent::__construct();
	}

	public function index($mkey = '')
	{
		if($mkey == ''){
			
			if($this->session->userdata('merchant_key') !='' ){ redirect('m/'.$this->session->userdata('merchant_key')); }
		
			$data['title']				=	'Merchant | '.config_item('program_name');
			$data['meta_description'] 	=	config_item('program_name');
			$data['meta_keywords']		=	config_item('program_name');
			$data['meta_author']		=	config_item('program_author');
			$data['program_name']		=	config_item('program_name');
			
			$this->load->view('info', $data);
			
		}else{
			
			$this->load->library('curl');
			$mktime_dc = strtotime(date("d-m-Y H:i:s"));
			$client_url = config_item('website').'/cloud_info?_dc='.$mktime_dc;
			
			$post_data = array(
				'merchant_key'	=> $mkey
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
			
			$ret_data = json_decode($curl_ret, true);
			
			if(!empty($ret_data['success'] === true)){
				
				if(!empty($ret_data['merchant_db']) AND !empty($ret_data['merchant_user']) AND !empty($ret_data['merchant_pass'])){
					
					redirect('login');
					die();
				}
				
			}
			
			
			$data['title']				=	'Merchant | '.config_item('program_name');
			$data['meta_description'] 	=	config_item('program_name');
			$data['meta_keywords']		=	config_item('program_name');
			$data['meta_author']		=	config_item('program_author');
			$data['program_name']		=	config_item('program_name');
			$data['error'] 		= 'Merchant tidak dikenali / Salah Merchant Key!<br/>';
			$this->load->view('info', $data);
		}
	}
	
}
