<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class AccountReceivableReport extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->prefix_acc = config_item('db_prefix3');
		$this->load->model('model_account_receivable', 'm');
	}
	
	public function print_accountReceivableReport(){
		
		$this->table = $this->prefix_acc.'account_receivable';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'ACCOUNT RECEIVABLE REPORT',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'user_fullname'	=> $user_fullname
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($date_from) OR empty($date_till)){
			die('Data Not Found!');
		}else{
				
			if(empty($date_from)){ $date_from = date('Y-m-d'); }
			if(empty($date_till)){ $date_till = date('Y-m-d'); }
			
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till = date("Y-m-d",strtotime($date_till));
			
			$add_where = "(a.ar_date >= '".$qdate_from."' AND a.ar_date <= '".$qdate_till."')";
			
			$this->db->select("a.*, b.customer_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix.'customer as b','b.id = a.customer_id','LEFT');
			
			if(!empty($status)){
				$this->db->where("a.ar_status = '".$status."'");
				$data_post['ar_status'] = $status;
			}
			
			$this->db->where("a.is_deleted", 0);
			$this->db->where($add_where);
			$this->db->order_by("ar_date","ASC");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
						
			$all_ar_id = array();
			$newData = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					$s['created_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['ar_date'] = date("d-m-Y",strtotime($s['ar_date']));
					
					if(!in_array($s['id'], $all_ar_id)){
						$all_ar_id[] = $s['id'];
					}		
										
					$s['total_tagihan_text'] = priceFormat($s['total_tagihan']);
					
					$newData[$s['id']] = $s;
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
		
		$useview = 'print_accountReceivableReport';
		if($do == 'excel'){
			$useview = 'excel_accountReceivableReport';
		}
				
		$this->load->view('../../account_receivable/views/'.$useview, $data_post);	
	}
	

}