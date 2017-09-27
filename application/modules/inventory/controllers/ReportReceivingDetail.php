<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class reportReceivingDetail extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_receivinglist', 'm');
	}
	
	public function print_reportReceivingDetail(){
		
		$this->table = $this->prefix.'receiving';
		$this->table2 = $this->prefix.'receive_detail';
		
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
			'report_name'	=> 'RECEIVING DETAIL REPORT',
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
			if(empty($supplier_id)){ $supplier_id = 0; }
			
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till = date("Y-m-d",strtotime($date_from));
			
			$add_where = array();
			$add_where[] = "(a2.receive_date >= '".$qdate_from."' AND a2.receive_date <= '".$qdate_till."')";
			
			if(!empty($supplier_id)){
				$add_where[] = "(a2.supplier_id = '".$supplier_id."')";
			}
			
			$add_where_txt = implode(" AND ", $add_where);
			
			$this->db->select("a.*, 
					a2.receive_number, a2.receive_status, 
					a2.receive_date, a2.created, a2.receive_memo,
					b.supplier_name, c.item_code, c.item_name, d.unit_name as satuan");
			$this->db->from($this->table2." as a");
			$this->db->join($this->table.' as a2','a2.id = a.receive_id','LEFT');
			$this->db->join($this->prefix.'supplier as b','b.id = a2.supplier_id','LEFT');
			$this->db->join($this->prefix.'items as c','c.id = a.item_id','LEFT');
			$this->db->join($this->prefix.'unit as d','d.id = a.unit_id','LEFT');
			$this->db->where("a2.receive_status", 'done');
			$this->db->where("a2.is_deleted", 0);
			$this->db->where($add_where_txt);
			$this->db->order_by("a2.receive_date","ASC");
			$get_dt = $this->db->get();
			
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
									
			$all_receive_no = array();
			$newData = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					
					$s['created_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['receive_date'] = date("d-m-Y",strtotime($s['receive_date']));
					
					if(!in_array($s['receive_number'], $all_receive_no)){
						$all_receive_no[] = $s['receive_number'];
					}		
					
					if(empty($newData[$s['receive_number']])){
						$newData[$s['receive_number']] = array();
					}
					
					$s['receive_det_total'] = $s['receive_det_qty'] * $s['receive_det_purchase'];
															
					$s['receive_det_purchase_text'] = priceFormat($s['receive_det_purchase']);
					$s['receive_det_total_text'] = priceFormat($s['receive_det_total']);
																				
					$newData[$s['receive_number']][] = $s;
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
		
		$useview = 'print_reportReceivingDetail';
		if($do == 'excel'){
			$useview = 'excel_reportReceivingDetail';
		}
				
		$this->load->view('../../inventory/views/'.$useview, $data_post);	
	}
	

}