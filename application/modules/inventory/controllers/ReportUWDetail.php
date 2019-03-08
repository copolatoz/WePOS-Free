<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class reportUWDetail extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_usagewaste', 'm');
	}
	
	public function print_reportUWDetail(){

		$this->table = $this->prefix.'usagewaste';
		$this->table2 = $this->prefix.'usagewaste_detail';
		$this->storehouse = $this->prefix.'storehouse';
		
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
			'report_name'	=> 'USAGE & WASTE DETAIL REPORT',
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
			
			$add_where = "(a2.uw_date >= '".$qdate_from."' AND a2.uw_date <= '".$qdate_till."')";
			
			$this->db->select("a.*, 
					a2.uw_number, a2.uw_status, a2.createdby,
					a2.uw_date, a2.created, a2.uw_memo, 
					b.storehouse_name as uw_from_name, 
					c.item_code, c.item_name, d.unit_name as satuan");
			$this->db->from($this->table2." as a");
			$this->db->join($this->table.' as a2','a2.id = a.uw_id','LEFT');
			$this->db->join($this->storehouse.' as b','b.id = a2.uw_from','LEFT');
			$this->db->join($this->prefix.'items as c','c.id = a.item_id','LEFT');
			$this->db->join($this->prefix.'unit as d','d.id = a.unit_id','LEFT');
			$this->db->where("a2.uw_status", 'done');
			$this->db->where("a2.is_deleted", 0);
			$this->db->where($add_where);
			$this->db->order_by("a2.uw_date","ASC");
			$get_dt = $this->db->get();
			
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
									
			$all_uw_no = array();
			$newData = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					
					$s['created_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['uw_date'] = date("d-m-Y",strtotime($s['uw_date']));
					
					if(!in_array($s['uw_number'], $all_uw_no)){
						$all_uw_no[] = $s['uw_number'];
					}		
					
					if(empty($newData[$s['uw_number']])){
						$newData[$s['uw_number']] = array();
					}
					
																				
					$newData[$s['uw_number']][] = $s;
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
		
		$useview = 'print_reportUWDetail';
		if($do == 'excel'){
			$useview = 'excel_reportUWDetail';
		}
				
		$this->load->view('../../inventory/views/'.$useview, $data_post);	
	}
	

}