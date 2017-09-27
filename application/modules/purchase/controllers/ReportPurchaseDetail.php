<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ReportPurchaseDetail extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_purchaseorder', 'm');
		$this->load->model('model_purchaseorderdetail', 'm2');
	}
	
	public function print_reportPurchaseDetail(){
		
		$this->table = $this->prefix.'po';
		$this->table2 = $this->prefix.'po_detail';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		
		if(empty($date_from)){ $date_from = date('Y-m-d'); }
		if(empty($date_till)){ $date_till = date('Y-m-d'); }			
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'PURCHASE DETAIL REPORT',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'user_fullname'	=> $user_fullname
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($date_from) OR empty($date_till)){
			die('Purchase Not Found!');
		}else{
				
			if(empty($date_from)){ $date_from = date('Y-m-d'); }
			if(empty($date_till)){ $date_till = date('Y-m-d'); }
			
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till = date("Y-m-d",strtotime($date_from));
			
			$add_where = "(a2.po_date >= '".$qdate_from."' AND a2.po_date <= '".$qdate_till."')";
			
			$this->db->select("a.*, 
					a2.po_number, a2.supplier_invoice, a2.po_status, 
					a2.po_date, a2.created, a2.po_memo,
					b.supplier_name, c.item_code, c.item_name, d.unit_name as satuan");
			$this->db->from($this->table2." as a");
			$this->db->join($this->table.' as a2','a2.id = a.po_id','LEFT');
			$this->db->join($this->prefix.'supplier as b','b.id = a2.supplier_id','LEFT');
			$this->db->join($this->prefix.'items as c','c.id = a.item_id','LEFT');
			$this->db->join($this->prefix.'unit as d','d.id = a.unit_id','LEFT');
			$this->db->where("a2.po_status IN ('done','progress')");
			$this->db->where("a2.is_deleted", 0);
			$this->db->where($add_where);
			$this->db->order_by("a2.po_date","ASC");
			$get_dt = $this->db->get();
			
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
									
			$all_po_no = array();
			$newData = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					
					$s['created_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['po_date'] = date("d-m-Y",strtotime($s['po_date']));
					
					if(!in_array($s['po_number'], $all_po_no)){
						$all_po_no[] = $s['po_number'];
					}		
					
					if(empty($newData[$s['po_number']])){
						$newData[$s['po_number']] = array();
					}
										
					$s['po_detail_purchase_text'] = priceFormat($s['po_detail_purchase']);
					$s['po_detail_total_text'] = priceFormat($s['po_detail_total']);
																				
					$newData[$s['po_number']][] = $s;
					//array_push($newData, $s);
					
				}
			}
				
			$data_post['report_data'] = $newData;
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		$useview = 'print_reportPurchaseDetail';
		if($do == 'excel'){
			$useview = 'excel_reportPurchaseDetail';
		}
		
		$this->load->view('../../purchase/views/'.$useview, $data_post);	
	}
	

}