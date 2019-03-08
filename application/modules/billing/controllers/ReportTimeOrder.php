<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ReportTimeOrder extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_databilling', 'm');
		$this->load->model('model_billingdetail', 'm2');
	}
	
	public function print_reportTimeOrder(){
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';		
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		
		if(empty($date_from)){ $date_from = date("Y-m-d"); }
		if(empty($date_till)){ $date_till = date("Y-m-d"); }
		
		if(empty($sorting)){
			$sorting = 'payment_date';
		}
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'TIME ORDER REPORT',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'cashier_name'	=> '',
			'user_fullname'	=> $user_fullname,
			'diskon_sebelum_pajak_service'	=> 0
		);
		
		if(empty($groupCat)){
			$groupCat = 0;
		}
		
		$get_opt = get_option_value(array('report_place_default','diskon_sebelum_pajak_service','cashier_max_pembulatan',
		'cashier_pembulatan_keatas','pembulatan_dinamis'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		if(!empty($get_opt['diskon_sebelum_pajak_service'])){
			$data_post['diskon_sebelum_pajak_service'] = $get_opt['diskon_sebelum_pajak_service'];
		}
		if(empty($get_opt['cashier_max_pembulatan'])){
			$get_opt['cashier_max_pembulatan'] = 0;
		}
		if(empty($get_opt['cashier_pembulatan_keatas'])){
			$get_opt['cashier_pembulatan_keatas'] = 0;
		}
		if(empty($get_opt['pembulatan_dinamis'])){
			$get_opt['pembulatan_dinamis'] = 0;
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
			
			$add_where = "(b.payment_date >= '".$qdate_from." 07:00:00' AND b.payment_date <= '".$qdate_till_max." 06:00:00')";
			
			//b.tax_total, b.service_total,
			//b.include_tax, b.tax_percentage, b.include_service, b.service_percentage, b.is_compliment,
			$this->db->select("a.*, b.billing_no, b.total_billing, b.discount_perbilling, b.payment_id,
								b.discount_percentage as billing_discount_percentage, b.discount_total as billing_discount_total,
								b.total_pembulatan as billing_total_pembulatan, b.payment_date,
								c.product_name, c.product_group, c.category_id, d.product_category_name as category_name,
								e.id as timer_id, e.order_start, e.order_done, e.order_time, e.done_by");
			$this->db->from($this->table2." as a");
			$this->db->join($this->prefix.'billing as b','b.id = a.billing_id','LEFT');
			$this->db->join($this->prefix.'product as c','c.id = a.product_id','LEFT');
			$this->db->join($this->prefix.'product_category as d','d.id = c.category_id','LEFT');
			$this->db->join($this->prefix.'billing_detail_timer as e','e.bild_id = a.id','LEFT');
			$this->db->where("a.is_deleted", 0);
			$this->db->where("b.is_deleted", 0);
			$this->db->where("b.billing_status", "paid");			
			//$this->db->order_by("d.product_category_name", 'ASC');		
			//$this->db->order_by("c.product_name", 'ASC');
			$this->db->where($add_where);
			
			if(empty($sorting)){
				$this->db->order_by("a.id","ASC");
			}else{
				$this->db->order_by($sorting,"ASC");
			}
			
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();
				
			}
			
			//echo $this->db->last_query();
			$konversi_pembulatan_billing = array();
			$balancing_discount_billing = array();
			$all_product_data = array();
			$newData = array();
			$no = 1;
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					
					$s['item_no'] = $no;
					
					if(empty($all_product_data[$s['id']]) AND !empty($s['timer_id'])){
						
						$all_product_data[$s['id']] = array(
							'id'			=> $s['id'],
							'billing_no'	=> $s['billing_no'],
							'product_id'	=> $s['product_id'],
							'product_name'	=> $s['product_name'],
							'product_group'	=> $s['product_group'],
							'category_id'	=> $s['category_id'],
							'category_name'	=> $s['category_name'],
							'order_start'	=> $s['order_start'],
							'order_done'	=> $s['order_done'],
							'order_time'	=> $s['order_time'],
							'done_by'		=> $s['done_by'],
							'payment_date'	=> $s['payment_date'],
							'total_qty'		=> 0
						);
						
						$no++;
						
					}
					
					if(!empty($all_product_data[$s['id']])){
						$all_product_data[$s['id']]['total_qty'] += $s['order_qty'];
					}
					
					
					
				}
			}
			
			//echo '<pre>';
			//print_r($all_product_data);
			//die();
			
			$sort_qty = array();
			$sort_profit = array();
			$no = 1;
			if(!empty($all_product_data)){
				foreach($all_product_data as $dt){
					$dt['item_no'] = $no;
					$sort_qty[$dt['id']] = $dt['total_qty'];
					$newData[$dt['id']] = $dt;
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

			//GROUPING
			$new_GroupData = array();
			if(!empty($groupCat)){
				foreach($newData as $dt){
					if(empty($new_GroupData[$dt['category_name']])){
						$new_GroupData[$dt['category_name']] = array();
					}
						
					$new_GroupData[$dt['category_name']][] = $dt;
				}
				
				ksort($new_GroupData);
				
				$newData = $new_GroupData;
			}
			
			$data_post['report_data'] = $newData;
						
		}
		
		
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		if(empty($useview)){
			$useview = 'print_reportTimeOrder';
			$data_post['report_name'] = 'TIME ORDER';
			
			if($tipe_report == 'QTY'){
				$data_post['report_name'] = 'TIME ORDER BY QTY';
			}
			
			if($do == 'excel'){
				$useview = 'excel_reportTimeOrder';
			}
			
		}
		
		
		$this->load->view('../../billing/views/'.$useview, $data_post);
	}
	
}