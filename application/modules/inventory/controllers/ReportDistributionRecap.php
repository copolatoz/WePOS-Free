<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class reportDistributionRecap extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_distributionitem', 'm');
	}
	
	public function print_reportDistributionRecap(){

		$this->table = $this->prefix.'distribution';
		$this->table2 = $this->prefix.'distribution_detail';
		
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
			'report_name'	=> 'DISTRIBUTION REPORT (RECAP)',
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
			
			$this->db->select("a.*, b.divisi_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix.'divisi as b','b.id = a.divisi_id','LEFT');
			$this->db->where("a.dis_status", 'done');
			$this->db->where("a.is_deleted", 0);
			$this->db->where($add_where);
			$this->db->order_by("dis_date","ASC");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
						
			$all_dis_id = array();
			$all_dis_id_date = array();
			$newData = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					$s['created_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['dis_date'] = date("d-m-Y",strtotime($s['dis_date']));
					
					if(!in_array($s['id'], $all_dis_id)){
						$all_dis_id[] = $s['id'];
					}		
										
					if(empty($newData[$s['dis_date']])){
						$newData[$s['dis_date']] = array(
							'date'			=> $s['dis_date'],
							'total_dis'		=> 0,
							'total_item'	=> 0,
							'total_qty'		=> 0	
						);
					}
					
					$newData[$s['dis_date']]['total_dis'] += 1;
					//array_push($newData, $s);
					
					if(empty($all_dis_id_date[$s['id']])){
						$all_dis_id_date[$s['id']] = $s['dis_date'];
					}
					
				}
			}
						
			//dis_detail
			$data_item_dis = array();
			if(!empty($all_dis_id)){
				$all_dis_id_txt = implode(",", $all_dis_id);
				$this->db->select("disd_dikirim as total_qty, item_id, dis_id");
				$this->db->from($this->table2);
				$this->db->where("dis_id IN (".$all_dis_id_txt.")");
				$get_det = $this->db->get();
				if($get_det->num_rows() > 0){										
					foreach($get_det->result_array() as $dt){
						if(!empty($all_dis_id_date[$dt['dis_id']])){
							$getDate = $all_dis_id_date[$dt['dis_id']];
							
							$newData[$getDate]['total_qty'] += $dt['total_qty'];							

							if(empty($data_item_dis[$dt['dis_id']])){
								$data_item_dis[$dt['dis_id']] = array();
							}
						
							if(!in_array($dt['item_id'], $data_item_dis[$dt['dis_id']])){
								$data_item_dis[$dt['dis_id']][] = $dt['item_id'];
								$newData[$getDate]['total_item'] += 1;
							}
							
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
		
		$useview = 'print_reportDistributionRecap';
		if($do == 'excel'){
			$useview = 'excel_reportDistributionRecap';
		}
				
		$this->load->view('../../inventory/views/'.$useview, $data_post);	
	}
	

}