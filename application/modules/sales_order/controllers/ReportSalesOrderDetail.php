<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ReportSalesOrderDetail extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_salesorder', 'm');
		$this->load->model('model_salesorderdetail', 'm2');
	}
	
	public function print_reportSalesOrderDetail(){
		
		$this->table = $this->prefix.'salesorder';
		$this->table2 = $this->prefix.'salesorder_detail';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('User Session Expired, Please Re-Login!');
		}
		
		extract($_GET);
		
		if(empty($date_from)){ $date_from = date('Y-m-d'); }
		if(empty($date_till)){ $date_till = date('Y-m-d'); }			
		
		
		if(empty($storehouse_id)){
			die('Select Warehouse!');
			//$storehouse_id = $this->stock->get_primary_storehouse();
			//$storehouse_id = -1;
		}		
		
		if($storehouse_id == "null"){
			die('Select Warehouse!');
		}	
		
		$storehouse_name = '-';
		if($storehouse_id == -1){
			$storehouse_name = 'Semua Gudang';
		}		
		
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'SALES ORDER DETAIL REPORT',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'user_fullname'	=> $user_fullname,
			'storehouse_name'	=> $storehouse_name
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($date_from) OR empty($date_till)){
			die('Sales Order Not Found!');
		}else{
				
			if(empty($date_from)){ $date_from = date('Y-m-d'); }
			if(empty($date_till)){ $date_till = date('Y-m-d'); }
			
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till = date("Y-m-d",strtotime($date_till));
			
			$add_where = "(a2.so_date >= '".$qdate_from."' AND a2.so_date <= '".$qdate_till."')";
			
			$this->db->select("a.*, 
					a2.so_number, a2.so_status, a2.so_customer_name,
					a2.so_date, a2.created, a2.so_memo,
					c.item_code, c.item_name, d.unit_name as satuan, i.storehouse_name");
			$this->db->from($this->table2." as a");
			$this->db->join($this->table.' as a2','a2.id = a.so_id','LEFT');
			$this->db->join($this->prefix.'items as c','c.id = a.item_id','LEFT');
			$this->db->join($this->prefix.'unit as d','d.id = a.unit_id','LEFT');
			$this->db->join($this->prefix.'storehouse as i','i.id = a2.so_from','LEFT');
			$this->db->where("a2.so_status IN ('done','progress')");
			$this->db->where("a2.is_deleted", 0);
			$this->db->where($add_where);
			
			if(!empty($storehouse_id)){
				if($storehouse_id > 0){
					$this->db->where('a2.so_from', $storehouse_id);
				}
			}
			
			$this->db->order_by("a2.so_date","ASC");
			$get_dt = $this->db->get();
			
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
									
			$all_so_no = array();
			$newData = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					
					if(!empty($storehouse_id)){
						if($storehouse_id > 0 AND $storehouse_name == '-'){
							$storehouse_name = $s['storehouse_name'];
						}
					}
					
					$s['created_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['so_date'] = date("d-m-Y",strtotime($s['so_date']));
					
					if(!in_array($s['so_date'], $all_so_no)){
						$all_so_no[] = $s['so_date'];
					}		
					
					if(empty($newData[$s['so_date']])){
						$newData[$s['so_date']] = array();
					}
										
					$s['sales_price_show'] = priceFormat($s['sales_price']);
					$s['sod_total_show'] = priceFormat($s['sod_total']);
					$s['sod_potongan_show'] = priceFormat($s['sod_potongan']);
																				
					$newData[$s['so_date']][] = $s;
					//array_push($newData, $s);
					
				}
			}
				
			$data_post['report_data'] = $newData;
		}
		
		$data_post['storehouse_name'] = $storehouse_name;
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		$useview = 'print_reportSalesOrderDetail';
		if($do == 'excel'){
			$useview = 'excel_reportSalesOrderDetail';
		}
		
		$this->load->view('../../sales_order/views/'.$useview, $data_post);	
	}
	

}