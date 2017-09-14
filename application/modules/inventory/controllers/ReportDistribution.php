<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class reportDistribution extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_distributionitem', 'm');
	}
	
	public function print_reportDistribution(){
		
		$this->table = $this->prefix.'distribution';
		$this->table2 = $this->prefix.'distribution_detail';
		$this->storehouse = $this->prefix.'storehouse';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('User Session Expired, Please Re-Login!');
		}
		
		extract($_GET);
		
		if(empty($date_from)){ $date_from = date('Y-m-d'); }
		if(empty($date_till)){ $date_till = date('Y-m-d'); }
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'DISTRIBUTION REPORT',
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
			
			$add_where = "(a.dis_date >= '".$qdate_from."' AND a.dis_date <= '".$qdate_till."')";
			
			$this->db->select("a.*, b.storehouse_name as delivery_from_name,  c.storehouse_name as delivery_to_name, ");
			$this->db->from($this->table." as a");
			$this->db->join($this->storehouse.' as b','b.id = a.delivery_from','LEFT');
			$this->db->join($this->storehouse.' as c','c.id = a.delivery_to','LEFT');
			$this->db->where("a.dis_status", 'done');
			$this->db->where("a.is_deleted", 0);
			$this->db->where($add_where);
			$this->db->order_by("dis_date","ASC");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
						
			$all_dis_id = array();
			$newData = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					$s['created_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['dis_date'] = date("d-m-Y",strtotime($s['dis_date']));
					
					if(!in_array($s['id'], $all_dis_id)){
						$all_dis_id[] = $s['id'];
					}		
										
					$s['total_price'] = 0;					
					$s['total_item'] = 0;
					$s['total_qty'] = 0;
					
					$newData[$s['id']] = $s;
					//array_push($newData, $s);
					
				}
			}
			
			//po_detail
			$data_item_dis = array();
			if(!empty($all_dis_id)){
				$all_dis_id_txt = implode(",", $all_dis_id);
				$this->db->select("disd_diterima as total_qty, item_id, dis_id");
				$this->db->from($this->table2);
				$this->db->where("dis_id IN (".$all_dis_id_txt.")");	
				$get_det = $this->db->get();
				if($get_det->num_rows() > 0){										
					foreach($get_det->result_array() as $dt){
						$newData[$dt['dis_id']]['total_qty'] += $dt['total_qty'];
						
						if(empty($data_item_dis[$dt['dis_id']])){
							$data_item_dis[$dt['dis_id']] = array();
						}
						
						if(!in_array($dt['item_id'], $data_item_dis[$dt['dis_id']])){
							$data_item_dis[$dt['dis_id']][] = $dt['item_id'];
							$newData[$dt['dis_id']]['total_item'] += 1;
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
		
		$useview = 'print_reportDistribution';
		if($do == 'excel'){
			$useview = 'excel_reportDistribution';
		}
				
		$this->load->view('../../inventory/views/'.$useview, $data_post);	
	}
	

}