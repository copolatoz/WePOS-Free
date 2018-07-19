<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class reportMonitoringStock extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_receivinglist', 'm');
	}
	
	public function print_reportMonitoringStock(){
		
		$this->table = $this->prefix.'items';
		$this->table2 = $this->prefix.'item_category';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		
		if(empty($category)){ die(); }			
		
		if($storehouse_id == "null"){
			die('Select Warehouse!');
		}
		if(empty($storehouse_id)){
			die('select Warehouse!');
		}
		
		if(empty($category_name)){
			$category_name = 'ALL';
		}
		
		if(empty($date_from)){ $date_from = date('Y-m-d'); }
		if(empty($date_till)){ $date_till = date('Y-m-d'); }			
		
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'MONITORING STOCK (ACTUAL)',
			'category'	=> $category,
			'category_name'	=> $category_name,
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'user_fullname'	=> $user_fullname
		);
		
		$get_opt = get_option_value(array('report_place_default','hide_empty_stock_on_report'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		if(!empty($get_opt['hide_empty_stock_on_report'])){
			$data_post['hide_empty_stock_on_report'] = $get_opt['hide_empty_stock_on_report'];
		}
		
		if(empty($date_from) OR empty($date_till)){
			die('Select Date!');
		}else{
				
			if(empty($date_from)){ $date_from = date('Y-m-d'); }
			if(empty($date_till)){ $date_till = date('Y-m-d'); }
			
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till = date("Y-m-d",strtotime($date_from));
			
		}
		
		//GET WAREHOUSE
		$this->db->select("a.*");
		$this->db->from($this->prefix."storehouse as a");
		
		if(!empty($storehouse_id)){
			$this->db->where('a.id', $storehouse_id);	
		}
		
		
		$getWarehouse = $this->db->get();
		$warehouse_name = '';
		if($getWarehouse->num_rows() > 0){
			$dt_warehouse = $getWarehouse->row();
			$warehouse_name = $dt_warehouse->storehouse_name;
		}
		
		$data_post['warehouse_name'] = $warehouse_name;
		
		
		//GET STOCK
		$all_item_stock = array();
		$tgl_trx = date("Y-m-d");
		$this->db->select("a.*");
		$this->db->from($this->prefix."stock_rekap as a");
		
		if(!empty($storehouse_id)){
			$this->db->where('a.storehouse_id', $storehouse_id);	
		}
		
		$this->db->where("(a.trx_date >= '".$qdate_from."' AND a.trx_date <= '".$qdate_till."')");
		
		$getItemStock = $this->db->get();
		
		if($getItemStock->num_rows() > 0){
			foreach($getItemStock->result_array() as $dtR){
				
				if(empty($all_item_stock[$dtR['item_id']])){
					$all_item_stock[$dtR['item_id']] = $dtR;
				}
			}
		}
		
		//ITEM
		$all_item = array();
		$all_item_id = array();
		$this->db->select("x.item_id, a.*, b.unit_name as satuan");
		$this->db->from($this->prefix."stock as x");
		$this->db->join($this->table." as a","a.id = x.item_id");
		$this->db->join($this->prefix.'unit as b','b.id = a.unit_id','LEFT');
		if($category == -1){
			
		}else{
			$this->db->where('a.category_id', $category);
		}
		
		
		if(!empty($storehouse_id)){
			$this->db->where('x.storehouse_id', $storehouse_id);	
		}else{
			$this->db->where('x.storehouse_id', -1);
		}
		
		$this->db->where("a.is_deleted = 0");
		$this->db->group_by("x.item_id");
		$this->db->order_by("a.item_code","ASC");
		$getItem = $this->db->get();
		
		if($getItem->num_rows() > 0){
			foreach($getItem->result_array() as $dtR){
				if(!in_array($dtR['id'], $all_item_id)){
					$all_item_id[] = $dtR['id'];
					$dtR['total_stock'] = 0;
					$dtR['total_stock_in'] = 0;
					$dtR['total_stock_out'] = 0;
					$dtR['total_stock_kemarin'] = 0;
					
					if(!empty($all_item_stock[$dtR['id']])){
						$dtR['total_stock'] = $all_item_stock[$dtR['id']]['total_stock'] ;
						$dtR['total_stock_in'] = $all_item_stock[$dtR['id']]['total_stock_in'] ;
						$dtR['total_stock_out'] = $all_item_stock[$dtR['id']]['total_stock_out'] ;
						$dtR['total_stock_kemarin'] = $all_item_stock[$dtR['id']]['total_stock_kemarin'] ;
					}
					
					$show_item = true;
					if(!empty($get_opt['hide_empty_stock_on_report'])){
						if(empty($dtR['total_stock']) AND empty($dtR['total_stock_in']) AND empty($dtR['total_stock_out']) AND empty($dtR['total_stock_kemarin'])){
							$show_item = false;
						}
					}
					
					if($show_item){
						$all_item[] = $dtR;
					}
					
				}
			}
		}
		
		$data_post['report_data'] = $all_item;
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		$useview = 'print_reportMonitoringStock';
		if($do == 'excel'){
			$useview = 'excel_reportMonitoringStock';
		}
				
		$this->load->view('../../inventory/views/'.$useview, $data_post);	
	}
	

}