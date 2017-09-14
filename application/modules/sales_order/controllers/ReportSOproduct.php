<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ReportSOProduct extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_salesorder', 'm');
		$this->load->model('model_salesorderdetail', 'm2');
	}
	
	public function print_reportSObyQty(){
		$this->table = $this->prefix.'salesorder';
		$this->table2 = $this->prefix.'salesorder_detail';		
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('User Session Expired, Please Re-Login!');
		}
		
		extract($_GET);
		
		if(empty($storehouse_id)){
			die('Select Warehouse!');
			//$storehouse_id = $this->stock->get_primary_storehouse();
			//$storehouse_id = -1;
		}	
		
		
		$storehouse_name = '-';
		if($storehouse_id == -1){
			$storehouse_name = 'Semua Gudang';
		}		
		
		if(empty($date_from)){ $date_from = date("Y-m-d"); }
		if(empty($date_till)){ $date_till = date("Y-m-d"); }
		
		if(empty($sorting)){
			$sorting = 'so_date';
		}
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'SALES REPORT BY QTY',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'cashier_name'	=> '',
			'user_fullname'	=> $user_fullname,
			'storehouse_name'	=> $storehouse_name
		);
		
		if(empty($groupCat)){
			$groupCat = 0;
		}
		
		
		if(empty($date_from) OR empty($date_till)){
			die('Billing Data Not Found!');
		}else{
				
			if(empty($date_from)){ $date_from = date('Y-m-d'); }
			if(empty($date_till)){ $date_till = date('Y-m-d'); }
			
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till = date("Y-m-d",strtotime($date_till));
			$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
			
			$add_where = "(b.so_date >= '".$qdate_from."' AND b.so_date <= '".$qdate_till."')";
			
			//b.tax_total, b.service_total,
			//b.include_tax, b.tax_percentage, b.include_service, b.service_percentage, b.is_compliment,
			$this->db->select("a.*, b.so_number, b.so_total_qty, b.so_discount, b.so_sub_total,
								b.so_tax, b.so_shipping, b.so_dp, b.so_total_price, b.so_payment,
								c.item_name, c.item_code, c.id_ref_product, 
								d.item_departemen_code, d.item_departemen_name,
								e.item_category_code, e.item_category_name,
								f.item_size_code, f.item_size_name, 
								g.item_color_code, g.item_color_name, 
								h.item_age_code, h.item_age_name, i.storehouse_name");
			$this->db->from($this->table2." as a");
			$this->db->join($this->prefix.'salesorder as b','b.id = a.so_id','LEFT');
			$this->db->join($this->prefix.'items as c','c.id = a.item_id','LEFT');
			$this->db->join($this->prefix.'item_departemen as d','d.id = c.departemen_id','LEFT');
			$this->db->join($this->prefix.'item_category as e','e.id = c.category_id','LEFT');
			$this->db->join($this->prefix.'item_size as f','f.id = c.size_id','LEFT');
			$this->db->join($this->prefix.'item_color as g','g.id = c.color_id','LEFT');
			$this->db->join($this->prefix.'item_age as h','h.id = c.age_id','LEFT');
			$this->db->join($this->prefix.'storehouse as i','i.id = b.so_from','LEFT');
			//$this->db->where("a.is_deleted", 0);
			$this->db->where("b.is_deleted", 0);
			$this->db->where("b.so_status", "done");			
			$this->db->order_by("c.item_name", 'ASC');
			$this->db->where($add_where);
			
			if(!empty($storehouse_id)){
				if($storehouse_id > 0){
					$this->db->where('b.so_from', $storehouse_id);
				}
			}
			
			if(empty($sorting)){
				$this->db->order_by("so_date","ASC");
			}else{
				$this->db->order_by($sorting,"ASC");
			}
			
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();
				
			}
			
			//echo $this->db->last_query();
			$all_so_number = array();
			$all_item_data = array();
			$newData = array();
			$no = 1;
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					
					$s['item_no'] = $no;
					
					if(!empty($storehouse_id)){
						if($storehouse_id > 0 AND $storehouse_name == '-'){
							$storehouse_name = $s['storehouse_name'];
						}
					}
					
					if(empty($all_item_data[$s['item_id']] )){
						$all_item_data[$s['item_id']] = array(
							'item_id'	=> $s['item_id'],
							'item_name'	=> $s['item_name'],
							'item_code'		=> $s['item_code'],
							'item_category_code'=> $s['item_category_code'],
							'item_category_name'=> $s['item_category_name'],
							'item_departemen_code'=> $s['item_departemen_code'],
							'item_departemen_name'=> $s['item_departemen_name'],
							'item_size_code'	=> $s['item_size_code'],
							'item_size_name'	=> $s['item_size_name'],
							'item_color_code'	=> $s['item_color_code'],
							'item_color_name'	=> $s['item_color_name'],
							'item_age_code'		=> $s['item_age_code'],
							'item_age_name'		=> $s['item_age_name'],
							'total_qty'	=> 0,
							'total_sales'	=> 0,
							'total_sales_show'	=> 0,
							'total_discount'	=> 0,
							'total_discount_show'	=> 0,
							'sub_total'	=> 0,
							'sub_total_show'	=> 0,
							'sub_total_cash'	=> 0,
							'sub_total_cash_show'	=> 0,
							'sub_total_credit'	=> 0,
							'sub_total_credit_show'	=> 0,
							'total_hpp'	=> 0,
							'total_hpp_show'	=> 0,
							'total_tax'	=> 0,
							'total_tax_show'	=> 0,
							'total_shipping'	=> 0,
							'total_shipping_show'	=> 0,
							'total_dp'	=> 0,
							'total_dp_show'	=> 0,
							'total_profit'	=> 0,
							'total_profit_show'	=> 0
						);
						
						$no++;
						
					}
					
					$detail_sales = ($s['sales_price']*$s['sod_qty']);
					$detail_hpp = ($s['item_hpp']*$s['sod_qty']);
					
					$all_item_data[$s['item_id']]['total_qty'] += $s['sod_qty'];
					$all_item_data[$s['item_id']]['total_discount'] += $s['sod_potongan'];
					$all_item_data[$s['item_id']]['total_sales'] += ($s['sales_price']*$s['sod_qty']);
					$all_item_data[$s['item_id']]['total_hpp'] += ($s['item_hpp']*$s['sod_qty']);
					$all_item_data[$s['item_id']]['total_profit'] += ($detail_sales - $detail_hpp);
					
					$sub_total = ($s['sales_price']*$s['sod_qty']) - $s['sod_potongan'];
					$all_item_data[$s['item_id']]['sub_total'] += $sub_total;
					
					if(!in_array($s['so_number'], $all_so_number)){
						$all_so_number[] = $s['so_number'];
						$all_item_data[$s['item_id']]['total_tax'] += $s['so_tax'];
						$all_item_data[$s['item_id']]['total_shipping'] += $s['so_shipping'];
						$all_item_data[$s['item_id']]['total_dp'] += $s['so_dp'];
						
					}
					
					if($s['so_payment'] == 'credit'){
						$all_item_data[$s['item_id']]['sub_total_credit'] += $sub_total;
					}else{
						$all_item_data[$s['item_id']]['sub_total_cash'] += $sub_total;
					}
						
				}
			}
			
			//echo '<pre>';
			//print_r($all_item_data);
			//echo 'TOTAL = '.count($all_item_data);
			//die();
			
			$sort_qty = array();
			$sort_profit = array();
			$no = 1;
			if(!empty($all_item_data)){
				foreach($all_item_data as $dt){
					$dt['item_no'] = $no;
					
					$sort_qty[$dt['item_id']] = $dt['total_qty'];
							
					$dt['total_sales_show'] = priceFormat($dt['total_sales']);
					$dt['sub_total_show'] = priceFormat($dt['sub_total']);
					$dt['sub_total_cash_show'] = priceFormat($dt['sub_total_cash']);
					$dt['sub_total_credit_show'] = priceFormat($dt['sub_total_credit']);
					$dt['total_discount_show'] = priceFormat($dt['total_discount']);
					$dt['total_hpp_show'] = priceFormat($dt['total_hpp']);
					$dt['total_tax_show'] = priceFormat($dt['total_tax']);
					$dt['total_shipping_show'] = priceFormat($dt['total_shipping']);
					$dt['total_dp_show'] = priceFormat($dt['total_dp']);
					$dt['total_profit_show'] = priceFormat($dt['total_profit']);
										
					$newData[$dt['item_id']] = $dt;
					$no++;
				}
			}
		
			arsort($sort_qty);	
			$tipe_report = '';
			if(!empty($order_qty)){
				$tipe_report = 'QTY';
				//RANK QTY
				if($order_qty == 1){
					arsort($sort_qty);
					$xnewData = array();
					foreach($sort_qty as $key => $dt){
			
						if(!empty($newData[$key])){
							$xnewData[] = $newData[$key];
						}
							
					}
					$newData = $xnewData;
				}
					
				//RANK PROFIT
				if($order_qty == 2){
					$tipe_report = 'PROFIT';
					arsort($sort_profit);
					$xnewData = array();
					foreach($sort_profit as $key => $dt){
			
						if(!empty($newData[$key])){
							$xnewData[] = $newData[$key];
						}
							
					}
					$newData = $xnewData;
				}
			}else{
				$order_qty = 0;
				$xnewData = array();
				foreach($newData as $dt){
					$xnewData[] = $dt;
				}
					
				$newData = $xnewData;
			}
			
			//PAYMENT DATA
			$dt_payment_name = array();
			$this->db->select('*');
			$this->db->from($this->prefix.'payment_type');
			$get_dt_p = $this->db->get();
			if($get_dt_p->num_rows() > 0){
				foreach($get_dt_p->result_array() as $dtP){
					$dt_payment_name[$dtP['id']] = strtoupper($dtP['payment_type_name']);
				}
			}
			


			//GROUPING
			$new_GroupData = array();
			$item_category_name = array();
			$item_departemen_name = array();
			$item_size_name = array();
			$item_color_name = array();
			$item_age_name = array();
			if(!empty($groupCat)){
				
				if($groupCat == 'cat' OR $groupCat == 'cat_profit'){
					foreach($newData as $dt){
						if(empty($new_GroupData[$dt['item_category_code']])){
							$new_GroupData[$dt['item_category_code']] = array();
						}
							
						$new_GroupData[$dt['item_category_code']][] = $dt;
						$item_category_name[$dt['item_category_code']] = $dt['item_category_name'];
					}
				}
				
				if($groupCat == 'dept'){
					foreach($newData as $dt){
						if(empty($new_GroupData[$dt['item_departemen_code']])){
							$new_GroupData[$dt['item_departemen_code']] = array();
						}
							
						$new_GroupData[$dt['item_dept_code']][] = $dt;
						$item_departemen_name[$dt['item_dept_code']] = $dt['item_departemen_name'];
					}
				}
				
				if($groupCat == 'size'){
					foreach($newData as $dt){
						if(empty($new_GroupData[$dt['item_size_code']])){
							$new_GroupData[$dt['item_size_code']] = array();
						}
							
						$new_GroupData[$dt['item_size_code']][] = $dt;
						$item_size_name[$dt['item_size_code']] = $dt['item_size_name'];
					}
				}
				
				if($groupCat == 'color'){
					foreach($newData as $dt){
						if(empty($new_GroupData[$dt['item_color_code']])){
							$new_GroupData[$dt['item_color_code']] = array();
						}
							
						$new_GroupData[$dt['item_color_code']][] = $dt;
						$item_color_name[$dt['item_color_code']] = $dt['item_color_name'];
					}
				}
				
				if($groupCat == 'age'){
					foreach($newData as $dt){
						if(empty($new_GroupData[$dt['item_age_code']])){
							$new_GroupData[$dt['item_age_code']] = array();
						}
							
						$new_GroupData[$dt['item_age_code']][] = $dt;
						$item_age_name[$dt['item_age_code']] = $dt['item_age_name'];
					}
				}
				
				
				
				$newData = $new_GroupData;
				
			}
			
			$data_post['report_data'] = $newData;
			$data_post['payment_data'] = $dt_payment_name;
			$data_post['item_category_name'] = $item_category_name;
			$data_post['item_departemen_name'] = $item_departemen_name;
			$data_post['item_size_name'] = $item_size_name;
			$data_post['item_color_name'] = $item_color_name;
			$data_post['item_age_name'] = $item_age_name;
						
		}
		
		$data_post['storehouse_name'] = $storehouse_name;
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}

		if(empty($useview)){
			$useview = 'print_reportSObyQty';
			$data_post['report_name'] = 'SALES PRODUCT';
			
			if($tipe_report == 'QTY'){
				$data_post['report_name'] = 'SALES ORDER BY QTY';
			}
			
			if($do == 'excel'){
				$useview = 'excel_reportSObyQty';
			}
			
		}else{
			$useview = 'print_reportProfitSalesByMenu';
			$data_post['report_name'] = 'SALES PROFIT BY PRODUCT';
			
			if($tipe_report == 'QTY'){
				$data_post['report_name'] = 'SALES PROFIT BY PRODUCT QTY';
			}
			if($tipe_report == 'PROFIT'){
				$data_post['report_name'] = 'SALES PROFIT BY PRODUCT PROFIT';
			}
			
			if($do == 'excel'){
				$useview = 'excel_reportProfitSalesByMenu';
			}
			
		}

		if(!empty($groupCat)){
			
			if($groupCat == 'cat'){
				$useview = 'print_reportSObyCategory';
				$data_post['report_name'] = 'SALES ORDER BY CATEGORY';
				
				if($do == 'excel'){
					$useview = 'excel_reportSObyCategory';
				}
				
			}else
			if($groupCat == 'dept'){
				$useview = 'print_reportSObyQtyDepartemen';
				$data_post['report_name'] = 'SALES ORDER BY DEPARTEMEN';
				
				if($do == 'excel'){
					$useview = 'excel_reportSObyQtyDepartemen';
				}
				
			}else
			if($groupCat == 'size'){
				$useview = 'print_reportSObySize';
				$data_post['report_name'] = 'SALES ORDER BY SIZE';
				
				if($do == 'excel'){
					$useview = 'excel_reportSObySize';
				}
				
			}else
			if($groupCat == 'color'){
				$useview = 'print_reportSObyColor';
				$data_post['report_name'] = 'SALES ORDER BY COLOR';
				
				if($do == 'excel'){
					$useview = 'excel_reportSObyColor';
				}
				
			}else
			if($groupCat == 'age'){
				$useview = 'print_reportSObyAge';
				$data_post['report_name'] = 'SALES ORDER BY AGE';
				
				if($do == 'excel'){
					$useview = 'excel_reportSObyAge';
				}
				
			}else{
				$useview = 'print_reportProfitSalesByMenuCategory';
				$data_post['report_name'] = 'SALES PROFIT BY PRODUCT CATEGORY';
				
				if($do == 'excel'){
					$useview = 'excel_reportProfitSalesByMenuCategory';
				}
				
			}
		}
		
		
		$this->load->view('../../sales_order/views/'.$useview, $data_post);
	}
	
}