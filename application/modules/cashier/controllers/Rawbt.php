<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Rawbt extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix');
		$this->prefix_pos = config_item('db_prefix2');
		$this->load->model('model_billingcashierprint', 'mprint');
				
	}

	public function index()
	{
		$bill_no = $this->input->post_get('bill_no');
		$date = $this->input->post_get('date');
		
		$bill_no = str_replace(".txt","",$bill_no);
		$date = str_replace(".txt","",$date);
		if(!empty($date)){
			$this->printSettlement($date);
		}else{
			$this->doPrint($bill_no);
		}
		
	}

	public function doPrint($url_trx = '')
	{
		header('Content-Type: text/plain;');
		
		$url_trx = str_replace("trx-","",$url_trx);
		$url_trx = str_replace(".txt","",$url_trx);
		$get_data = explode("-",$url_trx);
		
		if(empty($get_data[0])){
			die();
		}
		
		$billing_no = $get_data[0];
		$tipe = $get_data[1];
		$id = $get_data[2];
		$is_void = $get_data[3];
		$void_id = $get_data[4];
		$order_detail_id = $get_data[5];
		
		$dtParams = array(
			'tipe' 		 => $tipe,
			'id' 		 => $id,
			'rawbt_print'=> true,
		);
		
		//get monitoring settlement
		$this->table_print_monitoring = $this->prefix_pos.'print_monitoring';
		$this->db->select("*");
		$this->db->from($this->table_print_monitoring);
		$this->db->where("tipe = 'billing' AND billing_no = '".$billing_no."'");
		$this->db->order_by("id","DESC");
		$get_data_print = $this->db->get();
		$receiptTxt = '';
		
		if($get_data_print->num_rows() > 0){
			$dt_monitoring = $get_data_print->row_array();
			$receiptTxt = $dt_monitoring['receiptTxt'];
			$printer_type = $dt_monitoring['tipe_printer'];
			$printer_pin = $dt_monitoring['tipe_pin'];
			//echo $receiptTxt;die();
			$print_content = replace_to_printer_command($receiptTxt, $printer_type, $printer_pin);
			echo $print_content;
		}else{
			echo '';
		}
	}

	public function testPrinter($printSetting = '')
	{
		header('Content-Type: text/plain;');
		
		$printSetting = str_replace(".txt","",$printSetting);
		if(empty($printSetting)){
			die();
		}
		
		$dtParams = array(
			'do_print' 		=> true,
			'printSetting' 	=> $printSetting,
			//'return_data' 	=> true,
			'rawbt_print'	=> true
		);
		
		$this->mprint->testPrinter($dtParams);
		
	}

	public function printSettlement($url_trx = '')
	{
		
		header('Content-Type: text/plain;');
		
		$url_trx = str_replace("settlement-","",$url_trx);
		$url_trx = str_replace(".txt","",$url_trx);
		$get_data = explode("-",$url_trx);
		
		if(empty($get_data[0])){
			die();
		}
		
		$get_date = $get_data[0];
		$reprint = $get_data[1];
		$show_txmark = $get_data[2];
		$pershift = $get_data[3];
		
		$dtParams = array(
			'get_date' => $get_date,
			'reprint' => $reprint,
			'show_txmark' => $show_txmark,
			'pershift' => $pershift,
			'rawbt_print'	=> true
		);
		
		
		//get monitoring settlement
		$this->table_print_monitoring = $this->prefix_pos.'print_monitoring';
		$this->db->select("*");
		$this->db->from($this->table_print_monitoring);
		$this->db->where("tipe = 'settlement' AND billing_no = '".$get_date."'");
		$this->db->order_by("id","DESC");
		$get_data_print = $this->db->get();
		$receiptTxt = '';
		
		if($get_data_print->num_rows() > 0){
			$dt_monitoring = $get_data_print->row_array();
			$receiptTxt = $dt_monitoring['receiptTxt'];
			$printer_type = $dt_monitoring['tipe_printer'];
			$printer_pin = $dt_monitoring['tipe_pin'];
			//echo $receiptTxt;die();
			$print_content = replace_to_printer_command($receiptTxt, $printer_type, $printer_pin);
			echo $print_content;
		}else{
			echo '';
		}
		
	}
	
}