<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ReportCancelOrder extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_databilling', 'm');
		$this->load->model('model_billingdetail', 'm2');
	}	
	
	public function print_reportCancelOrder(){
		
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';
		$this->table_product = $this->prefix.'product';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('User Session Expired, Please Re-Login!');
		}
		
		extract($_GET);

		if(empty($date_from)){ $date_from = date('Y-m-d'); }
		if(empty($date_till)){ $date_till = date('Y-m-d'); }
		
		if(empty($sorting)){
			$sorting = 'payment_date';
		}
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'CANCEL ORDER REPORT',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'user_fullname'	=> $user_fullname,
			'sorting'	=> $sorting
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($date_from) OR empty($date_till)){
			die('Billing Paid Not Found!');
		}else{
				
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till = date("Y-m-d",strtotime($date_till));
			$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
			
			$add_where = "(b.created >= '".$qdate_from." 07:00:01' AND b.created <= '".$qdate_till_max." 06:00:00')";
			
			$this->db->select("a.*, a.updated as order_date, b.billing_no, c.product_name");
			$this->db->from($this->table2." as a");
			$this->db->join($this->table." as b","b.id = a.billing_id","LEFT");
			$this->db->join($this->table_product." as c","c.id = a.product_id","LEFT");
			$this->db->where("a.order_status", 'cancel');
			$this->db->where("a.is_deleted", 1);
			$this->db->where($add_where);
			
			//if(empty($sorting)){
				$this->db->order_by("b.created","ASC");
			//}else{
			//	$this->db->order_by($sorting,"ASC");
			//}
			
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
			
			//echo '<pre>';
			//print_r($data_post['report_data']);
			//die();
			
			$all_bil_id = array();
			$newData = array();
			$newData_group = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					$s['order_date'] = date("d-m-Y H:i",strtotime($s['created']));		
					
					if(!in_array($s['id'], $all_bil_id)){
						$all_bil_id[] = $s['id'];
					}	
										
					//$newData[$s['id']] = $s;
					//array_push($newData, $s);
					
					if(empty($newData_group[$s['billing_id']])){
						$newData_group[$s['billing_id']] = array();
					}
					
					$newData_group[$s['billing_id']][] = $s;
					
				}
			}
			
			$data_post['report_data'] = $newData_group;
			//$data_post['total_hpp'] = $total_hpp;
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		$useview = 'print_reportCancelOrder';
		$data_post['report_name'] = 'CANCEL ORDER REPORT';
		
		if($do == 'excel'){
			$useview = 'excel_reportCancelOrder';
		}
		
		$this->load->view('../../billing/views/'.$useview, $data_post);	
	}
	
}