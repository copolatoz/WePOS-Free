<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class KartuStok extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_receivinglist', 'm');
		$this->load->model('model_stock', 'stock');
	}
	
	public function print_kartuStok(){
		
		$this->table_receiving = $this->prefix.'receiving';
		$this->table_receiving_detail = $this->prefix.'receive_detail';
		$this->table_distribution = $this->prefix.'distribution';
		$this->table_distribution_detail = $this->prefix.'distribution_detail';
		$this->table_items = $this->prefix.'items';
		$this->table_item_category = $this->prefix.'item_category';
		$this->table_stock = $this->prefix.'stock';
		$this->table_stock_rekap = $this->prefix.'stock_rekap';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		
		if(empty($month)){ $month = date('m'); }
		if(empty($year)){ $year = date('Y'); }			
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'KARTU STOK',
			'month'	=> $month,
			'year'	=> $year,
			'user_fullname'	=> $user_fullname
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		//PREPARING DAYS
		$mkDay = strtotime("01-".$month."-".$year);
		$total_days = date("t", $mkDay);
		$default_data = array(
			'item_id'	=> '',
			'item_name'	=> '',
			'item_code'	=> '',
			'item_hpp'	=> '',
			'category_id'	=> '',
			'category_name'	=> '',
			'satuan'		=> '',
			'stock_awal'	=> 0,
			'stock_trx' 	=> array()
		);
		
		//echo '<pre>';
		//print_r($default_data);
		//die();
				
		$qdate_from = date($year."-".$month."-01");
		$qdate_till = date($year."-".$month."-".$total_days);
		
		$all_item = array();
		$all_item_id = array();
		
		//GET CATEGORY
		$allCat = array();
		$allCat_item = array();
		$allCat_item_id = array();
		$this->db->from($this->table_item_category);
		$getCat = $this->db->get();
		if($getCat->num_rows() > 0){
			foreach($getCat->result_array() as $dt){
				$allCat[$dt['id']] = $dt['item_category_name'];
			}
		}
		
		
		if(empty($storehouse_id)){
			//$storehouse_id = $this->stock->get_primary_storehouse();
			//$storehouse_id = -1;
			die('Select Warehouse');
		}
		if($storehouse_id == "null"){
			die('Select Warehouse!');
		}
		
		//die($storehouse_id);
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
		
		
		
		
		//echo count($get_rekap->result_array()).'<pre>';
		//print_r($get_rekap->result_array());
		//die();
		
		//VERSI STOCK TRX
		$available_stok_trx = array();
		$add_where = "(a.trx_date >= '".$qdate_from."' AND a.trx_date <= '".$qdate_till."')";
		$this->db->select("a.*, c.item_name, c.item_hpp, c.item_code, c.category_id, d.unit_name as satuan, e.item_category_name");
		$this->db->from($this->table_stock." as a");
		$this->db->join($this->table_items.' as c','c.id = a.item_id','LEFT');
		$this->db->join($this->prefix.'unit as d','d.id = c.unit_id','LEFT');
		$this->db->join($this->table_item_category.' as e','e.id = c.category_id','LEFT');
		//$this->db->where("a.trx_note != 'Stock Opname'");
		$this->db->where($add_where);
		if(!empty($storehouse_id)){
			$this->db->where('a.storehouse_id', $storehouse_id);
		}
		
		$this->db->order_by("a.trx_date","ASC");
		$this->db->order_by("a.trx_type","ASC");
		$this->db->order_by("a.trx_ref_data","ASC");
		$this->db->order_by("a.id","ASC");
		//$this->db->order_by("c.item_name","ASC");
		$get_trx = $this->db->get();
		if($get_trx->num_rows() > 0){
			foreach($get_trx->result_array() as $dtR){
				
				if(!in_array($dtR['item_id'], $all_item_id)){
					//create data
					$all_item_id[] = $dtR['item_id'];
					$preparing_data = $default_data;
					$preparing_data['item_id'] = $dtR['item_id'];
					$preparing_data['item_name'] = $dtR['item_name'];
					$preparing_data['item_hpp'] = $dtR['item_hpp'];
					$preparing_data['category_name'] = $dtR['item_category_name'];
					$preparing_data['category_id'] = $dtR['category_id'];
					$preparing_data['item_code'] = $dtR['item_code'];
					$preparing_data['satuan'] = $dtR['satuan'];
					$all_item[$dtR['item_id']] = $preparing_data;
				}
		
				if(!in_array($dtR['item_id'], $allCat_item_id)){
					$allCat_item_id[] = $dtR['item_id'];
		
					if(empty($allCat_item[$dtR['category_id']])){
						$allCat_item[$dtR['category_id']] = array();
					}
						
					$allCat_item[$dtR['category_id']][] = $dtR['item_id'];
				}
				
				
				if(!empty($all_item[$dtR['item_id']])){
					
					$data_trx = array(
						'trx_date'			=> $dtR['trx_date'],
						'trx_note'			=> $dtR['trx_note'],
						'trx_ref_data'		=> $dtR['trx_ref_data'],
						'trx_type'			=> $dtR['trx_type'],
						'trx_qty'			=> $dtR['trx_qty'],
						'trx_nominal'		=> $dtR['trx_nominal'],
					);
					
					$all_item[$dtR['item_id']]['stock_trx'][] = $data_trx;
					
				}
			}
		}
		
		
		//cek stock rekap
		$all_item_rekap = array();
		$add_where = "(a.trx_date = '".$qdate_from."')";
		$this->db->select("a.*");
		$this->db->from($this->table_stock_rekap." as a");
		$this->db->where($add_where);
		if(!empty($storehouse_id)){
			$this->db->where('a.storehouse_id', $storehouse_id);
		}
		$this->db->order_by("a.trx_date","ASC");
		$get_rekap = $this->db->get();
		if($get_rekap->num_rows() > 0){
			foreach($get_rekap->result_array() as $dtR){
				//$all_item_rekap[$dtR['item_id']] = $dtR;
				
				//STOK AWAL
				if(!empty($all_item[$dtR['item_id']])){
					$all_item[$dtR['item_id']]['stock_awal'] += $dtR['total_stock_kemarin'];
				}
				
				
			}
		}
		
		//echo '<pre>';
		//print_r($all_item);
		//print_r($allCat_item);
		//die();
		
		//GROUPING BY
		
		$data_post['report_data'] = $all_item;
		$data_post['total_days'] = $total_days;
		$data_post['category_data'] = $allCat;
		$data_post['category_item_data'] = $allCat_item;
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		$useview = 'print_kartuStok';
		if($do == 'excel'){
			$useview = 'excel_kartuStok';
		}else{
			if(count($data_post['report_data']) > 1500){
				die('data item '.count($data_post['report_data']).', this report has long time execution<br/>Please try export to Excel!');
			}
		}
				
				
		$this->load->view('../../inventory/views/'.$useview, $data_post);	
	}
	

}