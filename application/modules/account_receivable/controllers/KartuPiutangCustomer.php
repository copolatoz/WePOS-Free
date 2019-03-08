<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class KartuPiutangCustomer extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->prefix_acc = config_item('db_prefix3');
		$this->load->model('model_invoicear', 'm');
	}
	
	public function print_kartuPiutangCustomer(){
		
		$this->table = $this->prefix_acc.'invoice';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		
		$customer_id = 0;
		if(empty($invoicename)){ 
			echo 'Pilih Nama/Customer!';
			die();
		}else{
			$invoicename_exp = explode("_", $invoicename);
			$invoicename = $invoicename_exp[0];
			if(!empty($invoicename_exp[1])){
				$customer_id = $invoicename_exp[1];
			}
		}
		
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'KARTU PIUTANG CUSTOMER',
			'year'		=> $year,
			'invoicename'		=> $invoicename,
			'customer_id'	=> $customer_id,
			'user_fullname'	=> $user_fullname
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		$totalWeek = 1;
		$min_weekMonth = array();
		$max_weekMonth = array();
		for($i=1; $i<=12; $i++){
			
			$bulan = $i;
			if(strlen($bulan) <= 1){
				$bulan = '0'.$i;
			}
			
			$mkDay = strtotime("01-".$bulan."-".$year);
			$total_days = date("t", $mkDay);
			$mkLastDay = strtotime($total_days."-".$bulan."-".$year);
			
			$min_week = date("W", $mkDay);
			
			if(date("Y-m-d", $mkDay) == $year."-01-01"){
				$min_week = '01';
			}
			
			$max_week = date("W", $mkLastDay);
			
			
			if($bulan == 12 AND $max_week == '01'){
				
				$get_max_week = 0;
				for($j=1; $j<=7; $j++){
					
					if($get_max_week == 0){
						$total_days -= $j;
						$mkLastDay = strtotime($total_days."-".$bulan."-".$year);
						$max_week = date("W", $mkLastDay);
						
						if($max_week == '01'){
							$get_max_week = 0;
						}else{
							$get_max_week = $max_week;
						}
					}
					
				}
				
			}
			
			
			$min_weekMonth[$bulan] = $min_week;
			$max_weekMonth[$bulan] = $max_week;
			
			$getTotalWeek = ($max_week-$min_week)+1;
			
			if($getTotalWeek > $totalWeek){
				$totalWeek = $getTotalWeek;
			}
			
			//echo $i.' = '.$min_week.' s/d '.$max_week.' ==> '.$getTotalWeek.'<br/> ';
		}
		
		$data_post['totalWeek'] = $totalWeek;
		
		
		//echo $invoicename.'<br/>';
		//echo $date_from.' s/d '.$date_till.'<br/>';
		//echo $min_week.' s/d '.$max_week.'<br/>'.$total_days;
		//echo '<pre>';print_r($min_weekMonth);
		
		
		
		$this->db->select("a.*, b.customer_name");
		$this->db->from($this->table." as a");
		$this->db->join($this->prefix.'customer as b','b.id = a.customer_id','LEFT');
		
		$this->db->where("a.invoice_status = 'progress'");
		$this->db->where("a.invoice_name = '".$invoicename."'");
		$this->db->where("a.customer_id = '".$customer_id."'");
		
		$this->db->where("a.is_deleted", 0);
		$this->db->order_by("tanggal_jatuh_tempo","ASC");
		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			$data_post['report_data'] = $get_dt->result_array();				
		}
			
		//echo '<pre>';print_r($data_post['report_data']);	
		
		$all_invoice_id = array();
		$newData = array();
		if(!empty($data_post['report_data'])){
			foreach ($data_post['report_data'] as $s){
				
				$s['created_date'] = date("d-m-Y H:i",strtotime($s['created']));		
				$jatuh_tempo_mktime = strtotime($s['tanggal_jatuh_tempo']);
				$s['tanggal_jatuh_tempo'] = date("d-m-Y", $jatuh_tempo_mktime);
				
				$getMonth = date("m", $jatuh_tempo_mktime);
				$getWeek = date("W", $jatuh_tempo_mktime);
				if($s['tanggal_jatuh_tempo'] == "01-01-".$year){
					$getWeek = '01';
				}
				
				//echo '$getMonth = '.$getMonth.', '.$s['tanggal_jatuh_tempo'].' = '.date("d-m-Y", $jatuh_tempo_mktime).' = '.$getWeek.'-'.$min_week.'<br>';
				
				$getMinWeek = $min_weekMonth[$getMonth];
				$no_week = ($getWeek-$getMinWeek)+1;
				$s['minggu_ke'] = $no_week;
				$s['bulan'] = $getMonth;
				if(!in_array($s['id'], $all_invoice_id)){
					$all_invoice_id[] = $s['id'];
				}		
									
				$s['total_tagihan_text'] = priceFormat($s['total_tagihan']);
				$s['total_bayar_text'] = priceFormat($s['total_bayar']);
				
				$newData[$s['id']] = $s;
				//array_push($newData, $s);
				
			}
		}
		
		//echo '<pre>';print_r($newData);	die();
		//group berdasarkan customer dan weekDate
		$dtKartuPiutang = array();
		if(!empty($newData)){
			foreach($newData as $dt){
				
				$khID = $dt['bulan'];
				if(empty($dtKartuPiutang[$khID])){
					$dtKartuPiutang[$khID] = array(
						'nama_bulan' => get_month($khID)
					);
					
					if(!empty($max_weekMonth[$khID])){
						$getTotalWeek = ($max_weekMonth[$khID] - $min_weekMonth[$khID])+1;
						
						//echo $khID.' -- '.$max_weekMonth[$khID].'-'.$min_weekMonth[$khID].' == '.$getTotalWeek.'<br/>';
						for($i=1;$i<=$getTotalWeek;$i++){
							$dtKartuPiutang[$khID]['week_'.$i] = 0;
						}
					}
					
				}
				
				$sisa_hutang = ($dt['total_tagihan']-$dt['total_bayar']);
				
				if(empty($dtKartuPiutang[$khID]['week_'.$dt['minggu_ke']])){
					$dtKartuPiutang[$khID]['week_'.$dt['minggu_ke']] = 0;
				}
				$dtKartuPiutang[$khID]['week_'.$dt['minggu_ke']] += $sisa_hutang;
				
			}
			
			$newData = $dtKartuPiutang;
		}
		
		//echo '<pre>';
		//print_r($newData);
		//die();
		
		$data_post['report_data'] = $newData;
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		$useview = 'print_kartuPiutangCustomer';
		if($do == 'excel'){
			$useview = 'excel_kartuPiutangCustomerReport';
		}
				
		$this->load->view('../../account_receivable/views/'.$useview, $data_post);	
	}
	

}