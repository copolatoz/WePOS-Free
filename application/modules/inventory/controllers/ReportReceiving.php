<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class reportReceiving extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_receivinglist', 'm');
	}
	
	public function print_reportReceiving(){
		
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
		if(empty($supplier_id)){ $supplier_id = 0; }
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'RECEIVING REPORT',
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
			$qdate_till = date("Y-m-d",strtotime($date_till));
			
			$add_where = array();
			$add_where[] = "(a.receive_date >= '".$qdate_from."' AND a.receive_date <= '".$qdate_till."')";
			
			if(!empty($supplier_id)){
				$add_where[] = "(a.supplier_id = '".$supplier_id."')";
			}
			
			$add_where_txt = implode(" AND ", $add_where);
			
			$this->db->select("a.*, b.supplier_name, c.po_discount as discount");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix.'supplier as b','b.id = a.supplier_id','LEFT');
			$this->db->join($this->prefix.'po as c','c.id = a.po_id','LEFT');
			$this->db->where("a.receive_status", 'done');
			$this->db->where("a.is_deleted", 0);
			$this->db->where($add_where_txt);
			$this->db->order_by("receive_date","ASC");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
						
			$all_receive_id = array();
			$newData = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					$s['created_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['receive_date'] = date("d-m-Y",strtotime($s['receive_date']));
					
					if(!in_array($s['id'], $all_receive_id)){
						$all_receive_id[] = $s['id'];
					}		
										
					$s['discount'] = $s['discount'];					
					$s['total_price'] = 0;					
					$s['total_item'] = 0;
					$s['total_qty'] = 0;
					
					$newData[$s['id']] = $s;
					//array_push($newData, $s);
					
				}
			}
			
			//po_detail
			$data_item_receive = array();
			if(!empty($all_receive_id)){
				$all_receive_id_txt = implode(",", $all_receive_id);
				$this->db->select("receive_det_qty as total_qty, receive_det_purchase as item_price, item_id, receive_id");
				$this->db->from($this->table2);
				$this->db->where("receive_id IN (".$all_receive_id_txt.")");	
				$get_det = $this->db->get();
				if($get_det->num_rows() > 0){										
					foreach($get_det->result_array() as $dt){
						$newData[$dt['receive_id']]['total_qty'] += $dt['total_qty'];
						$newData[$dt['receive_id']]['total_price'] += ($dt['item_price'] * $dt['total_qty']);
						
						if(empty($data_item_receive[$dt['receive_id']])){
							$data_item_receive[$dt['receive_id']] = array();
						}
						
						if(!in_array($dt['item_id'], $data_item_receive[$dt['receive_id']])){
							$data_item_receive[$dt['receive_id']][] = $dt['item_id'];
							$newData[$dt['receive_id']]['total_item'] += 1;
						}
					}
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
		
		$useview = 'print_reportReceiving';
		if($do == 'excel'){
			$useview = 'excel_reportReceiving';
		}
				
		$this->load->view('../../inventory/views/'.$useview, $data_post);	
	}
	

}