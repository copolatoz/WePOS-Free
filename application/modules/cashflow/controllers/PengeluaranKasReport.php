<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class PengeluaranKasReport extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->prefix_acc = config_item('db_prefix3');
		$this->load->model('model_pengeluaran_kas', 'm');
	}
	
	public function print_pengeluaranKasReport(){
		
		$this->table = $this->prefix_acc.'pengeluaran_kas';
		
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
			'report_name'	=> 'PENGELUARAN KAS REPORT',
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
			
			$add_where = "(a.kk_date >= '".$qdate_from."' AND a.kk_date <= '".$qdate_till."')";
			
			$this->db->select("a.*, b.autoposting_name, c.tujuan_cashflow_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix_acc.'autoposting as b','b.id = a.autoposting_id','LEFT');
			$this->db->join($this->prefix_acc.'tujuan_cashflow as c','c.id = a.kk_tujuan','LEFT');
			
			if(!empty($jenis)){
				if($jenis > 0){
					$this->db->where("a.autoposting_id = '".$jenis."'");
					$data_post['jenis'] = $jenis;
				}
			}
			
			$this->db->where("a.is_deleted", 0);
			$this->db->where($add_where);
			$this->db->order_by("kk_date","ASC");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
						
			$all_kk_id = array();
			$newData = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					$s['created_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['kk_date'] = date("d-m-Y",strtotime($s['kk_date']));
					
					if(!in_array($s['id'], $all_kk_id)){
						$all_kk_id[] = $s['id'];
					}		
										
					$s['kk_total_text'] = priceFormat($s['kk_total']);
					
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
		
		$useview = 'print_pengeluaranKasReport';
		if($do == 'excel'){
			$useview = 'excel_pengeluaranKasReport';
		}
				
		$this->load->view('../../cashflow/views/'.$useview, $data_post);	
	}
	

}