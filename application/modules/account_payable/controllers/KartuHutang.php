<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class KartuHutang extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->prefix_acc = config_item('db_prefix3');
		$this->load->model('model_kontrabonap', 'm');
	}
	
	public function print_kartuHutang(){
		
		$this->table = $this->prefix_acc.'kontrabon';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		
		if(empty($month)){ $month = date('m'); }
		if(empty($year)){ $year = date('Y'); }			
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'KARTU HUTANG',
			'month'	=> $month,
			'year'	=> $year,
			'user_fullname'	=> $user_fullname
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		$mkDay = strtotime("01-".$month."-".$year);
		$total_days = date("t", $mkDay);
		$mkLastDay = strtotime($total_days."-".$month."-".$year);
		
		$date_from = $year."-".$month."-01";
		$date_till = $year."-".$month."-".$total_days;
		
		$min_week = date("W", $mkDay);
		
		if($date_from == $year."-01-01"){
			$min_week = '01';
		}
		
		$max_week = date("W", $mkLastDay);
		
		if($month == 12 AND $max_week == '01'){
			
			$get_max_week = 0;
			for($j=1; $j<=7; $j++){
				
				if($get_max_week == 0){
					$total_days -= $j;
					$mkLastDay = strtotime($total_days."-".$month."-".$year);
					$max_week = date("W", $mkLastDay);
					
					if($max_week == '01'){
						$get_max_week = 0;
					}else{
						$get_max_week = $max_week;
					}
				}
				
			}
		}
		
		
		$total_week = ($max_week-$min_week)+1;
		$data_post['total_week'] = $total_week;
		
		
		//echo $date_from.' s/d '.$date_till.'<br/>';
		//echo $min_week.' s/d '.$max_week.'<br/>'.$total_days;
		
		$weekDate = array();
		$dateWeek = array();
		for($i=1;$i<=$total_days;$i++){
			$tgl = $i;
			if(strlen($tgl) < 2){
				$tgl = '0'.$tgl;
			}
			$mkgetDay = strtotime($tgl."-".$month."-".$year);
			
			$getWeek = date("W", $mkgetDay);
			if($tgl."-".$month."-".$year == "01-01-".$year){
				$getWeek = '01';
			}
			$no_week = ($getWeek-$min_week)+1;
			if(empty($weekDate[$no_week])){
				$weekDate[$no_week] = array();
			}
			$weekDate[$no_week][] = $tgl;
			$dateWeek[$tgl] = $no_week;
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
			
			$add_where = "(a.tanggal_jatuh_tempo >= '".$qdate_from."' AND a.tanggal_jatuh_tempo <= '".$qdate_till."')";
			
			$this->db->select("a.*, b.supplier_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix.'supplier as b','b.id = a.supplier_id','LEFT');
			
			$this->db->where("a.kb_status = 'progress'");
			
			$this->db->where("a.is_deleted", 0);
			$this->db->where($add_where);
			$this->db->order_by("tanggal_jatuh_tempo","ASC");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
						
			$all_kb_id = array();
			$newData = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					$s['created_date'] = date("d-m-Y H:i",strtotime($s['created']));		
					$jatuh_tempo_mktime = strtotime($s['tanggal_jatuh_tempo']);
					$s['tanggal_jatuh_tempo'] = date("d-m-Y", $jatuh_tempo_mktime);
					
					$getWeek = date("W", $jatuh_tempo_mktime);
					if($s['tanggal_jatuh_tempo'] == "01-01-".$year){
						$getWeek = '01';
					}
					$no_week = ($getWeek-$min_week)+1;
					$s['minggu_ke'] = $no_week;
					//echo $s['tanggal_jatuh_tempo'].' = '.date("d-m-Y", $jatuh_tempo_mktime).' = '.$getWeek.'-'.$min_week.'<br>';
					
					if(!in_array($s['id'], $all_kb_id)){
						$all_kb_id[] = $s['id'];
					}		
										
					$s['total_tagihan_text'] = priceFormat($s['total_tagihan']);
					$s['total_bayar_text'] = priceFormat($s['total_bayar']);
					
					$newData[$s['id']] = $s;
					//array_push($newData, $s);
					
				}
			}
			
			
			//group berdasarkan supplier dan weekDate
			$dtKartuHutang = array();
			if(!empty($newData)){
				foreach($newData as $dt){
					
					$khID = $dt['kb_name'].'_'.$dt['supplier_id'];
					if(empty($dtKartuHutang[$khID])){
						$dtKartuHutang[$khID] = array(
							'kb_name' => $dt['kb_name'],
							'supplier_id' => $dt['supplier_id'],
							'supplier_name' => $dt['supplier_name']
						);
						
						for($i=1;$i<=$total_week;$i++){
							$dtKartuHutang[$khID]['week_'.$i] = 0;
						}
						
					}
					
					$sisa_hutang = ($dt['total_tagihan']-$dt['total_bayar']);
					$dtKartuHutang[$khID]['week_'.$dt['minggu_ke']] += $sisa_hutang;
				}
				
				$newData = $dtKartuHutang;
			}
			
			$data_post['report_data'] = $newData;
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		$useview = 'print_kartuHutang';
		if($do == 'excel'){
			$useview = 'excel_kartuHutangReport';
		}
				
		$this->load->view('../../account_payable/views/'.$useview, $data_post);	
	}
	

}