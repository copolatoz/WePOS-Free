<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class logSupervisorActivity extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix');
		//$this->load->model('model_datasystems', 'm');
	}	
	
	public function print_logSupervisorActivity(){
		
		$this->table = $this->prefix.'supervisor_log';
		
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
				'report_name'	=> 'LOG SUPERVISOR ACTIVITY',
				'date_from'	=> $date_from,
				'date_till'	=> $date_till,
				'user_fullname'	=> $user_fullname
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		$mktime_dari = strtotime($date_from);
		$mktime_sampai = strtotime($date_till);
		
		$qdate_from = date("Y-m-d",strtotime($date_from));
		$qdate_till = date("Y-m-d",strtotime($date_till));
			
		$add_where = "(a.created >= '".$qdate_from." 00:00:00' AND a.created <= '".$qdate_till." 23:59:59')";
			
		if(!empty($text_search)){
			$add_where .= " AND (a.createdby = '".$text_search."' 
					OR c.user_username = '".$text_search."' 
					OR a.supervisor_access LIKE '%".$text_search."%' 
					OR a.log_data LIKE '%".$text_search."%')";
		}
		
		$this->db->select("a.*, c.user_username");
		$this->db->from($this->table." as a");
		$this->db->join($this->prefix.'supervisor as b','b.id = a.supervisor_id','LEFT');
		$this->db->join($this->prefix.'users as c','c.id = b.user_id','LEFT');
		$this->db->where($add_where);
		$this->db->order_by("a.created","ASC");
		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			$data_post['report_data'] = $get_dt->result_array();
		}
		
		$all_receive_id = array();
		$newData = array();
		if(!empty($data_post['report_data'])){
			foreach ($data_post['report_data'] as $s){
				$s['created_date'] = date("d-m-Y H:i:s",strtotime($s['created']));
					
				$newData[$s['id']] = $s;
				//array_push($newData, $s);
					
			}
			$data_post['report_data'] = $newData;
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}
		
		$useview = 'print_logSupervisorActivity';
				
		$this->load->view('../../systems/views/'.$useview, $data_post);	
	}
	

}