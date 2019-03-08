<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ClosingAccounting extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->prefix_acc = config_item('db_prefix3');
		$this->load->model('model_closingaccounting', 'm');
		$this->load->model('model_closingaccountingdetail', 'm2');
	}

	public function gridData()
	{
		$this->table_closing = $this->prefix.'closing';
		$this->table_closing_accounting = $this->prefix_acc.'closing_accounting';
		
		//generate_status_text
		$sortAlias = array(
			//'closing_status_text' => 'closing_status'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table_closing.' as a',
			'where'			=> array('a.tipe' => 'accounting'),
			'order'			=> array('a.tanggal' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$generate_status = $this->input->post('generate_status');
		$closing_status = $this->input->post('closing_status');
		$skip_date = $this->input->post('skip_date');
		
		//FILTER
		$date_from = $this->input->post('date_from');
		$date_till = $this->input->post('date_till');
		$keywords = $this->input->post('keywords');
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if($skip_date == true){
		
		}else{
		
			if(empty($date_from) AND empty($date_till)){
				$date_from = date('Y-m-d');
				$date_till = date('Y-m-d');
			}
			
			if(!empty($date_from) OR !empty($date_till)){
			
				if(empty($date_from)){ $date_from = date('Y-m-d'); }
				if(empty($date_till)){ $date_till = date('Y-m-t'); }
				
				$mktime_dari = strtotime($date_from);
				$mktime_sampai = strtotime($date_till);
							
				$qdate_from = date("Y-m-d",strtotime($date_from));
				$qdate_till = date("Y-m-d",strtotime($date_till));
				
				$params['where'][] = "(a.tanggal >= '".$qdate_from."' AND a.tanggal <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.tanggal' => 'DESC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.tanggal LIKE '%".$searching."%')";
		}		
		if(!empty($generate_status)){
			$params['where'][] = "a.generate_status = '".$generate_status."'";
		}
		if(!empty($closing_status)){
			$params['where'][] = "a.closing_status = '".$closing_status."'";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		
  		$newData_update = array();		
		$all_id = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				if(!in_array($s['id'], $all_id)){
					$all_id[] = $s['id'];
				}
			}
		}
		
		$data_tanggal = array();
		$closing_accounting_start_date = '';
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['tanggal'] = date("d-m-Y", strtotime($s['tanggal']));
				
				if(!in_array($s['tanggal'], $data_tanggal)){
					$data_tanggal[] = $s['tanggal'];
				}
				
				if(empty($closing_accounting_start_date)){
					$closing_accounting_start_date = $s['tanggal'];
				}
				
				
				if($s['closing_status'] == 1){
					$s['closing_status_text'] = '<span style="color:green;">Yes</span>';
				}else{
					$s['closing_status_text'] = '<span style="color:red;">No</span>';
				}
				
				if($s['generate_status'] == 1){
					$s['generate_status_text'] = '<span style="color:green;">Yes</span>';
				}else{
					$s['generate_status_text'] = '<span style="color:red;">No</span>';
				}
				
				//echo 'tanggal = '.$s['tanggal'].'<br>';
				if(empty($newData_update[$s['tanggal']])){
					$newData_update[$s['tanggal']] = array();
				}
				
				$newData_update[$s['tanggal']] = $s;
				
				//array_push($newData_update, $s);
			}
		}
		
		//echo '<pre>';
		//print_r($newData_update);
		//die();
		
		//if empty check on opt = closing_accounting_start_date
		$opt_value = array(
			'closing_accounting_start_date'
		);
		$get_opt = get_option_value($opt_value);
		
		
		if(!empty($get_opt['closing_accounting_start_date'])){
			$closing_accounting_start_date = $get_opt['closing_accounting_start_date'];
		}
		
		if(empty($closing_accounting_start_date)){
			$closing_accounting_start_date = date("d-m-Y");
		}
		
		$today_date = date("d-m-Y");
		$today_mktime = strtotime($today_date);
		$closing_mktime = strtotime($closing_accounting_start_date);
		$date_from_mktime = strtotime($date_from);
		$date_till_mktime = strtotime($date_till);
		
		if($date_from_mktime <= $closing_mktime){
			$date_from_mktime = $closing_mktime;
		}
		
		$total_day = 0;
		if(!empty($date_from_mktime)){
			$total_day = ($date_till_mktime - $date_from_mktime) / ONE_DAY_UNIX;
		}
		
		/*echo '$get_opt = '.$get_opt['closing_accounting_start_date'].'<br>';
		echo '$closing_mktime = '.$closing_mktime.'<br>';
		echo '$date_from_mktime = '.$date_from_mktime.'<br>';
		echo '$date_till_mktime = '.$date_till_mktime.'<br>';
		echo '$closing_accounting_start_date = '.$closing_accounting_start_date.'<br>';
		echo '$date_from = '.$date_from.'<br>';
		echo '$date_till = '.$date_till.'<br>';
		echo '$total_day = '.$total_day.'<br>';
		die();*/
		
		$newData = array();	
		if(!empty($total_day)){
			for($i=$total_day; $i >= 0; $i--){
				
				$tanggal = date("d-m-Y", ($date_from_mktime + ($i*ONE_DAY_UNIX)));
				
				if(($date_from_mktime + ($i*ONE_DAY_UNIX)) <= $today_mktime){
					
					$dt_push = array(
						'tanggal'	=> $tanggal,
						'closing_status'	=> 0,
						'closing_status_text'	=> '<span style="color:red;">No</span>',
						'generate_status'	=> 0,
						'generate_status_text'	=> '<span style="color:red;">No</span>'
					);
					
					
					if(!in_array($tanggal, $data_tanggal)){
						$data_tanggal[] = $tanggal;
						array_push($newData, $dt_push);
					}else{
						if(!empty($newData_update[$tanggal])){
							
							$dt_push = array(
								'tanggal'	=> $tanggal,
								'closing_status'	=> $newData_update[$tanggal]['closing_status'],
								'closing_status_text'	=> $newData_update[$tanggal]['closing_status_text'],
								'generate_status'	=> $newData_update[$tanggal]['generate_status'],
								'generate_status_text'	=> $newData_update[$tanggal]['generate_status_text']
							);
							
							array_push($newData, $dt_push);
						}
					}
					
				}
				
			}
		}
		
		
		$get_data['data'] = $newData;
		$get_data['totalCount'] = count($newData);
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetail()
	{
		
		$this->table_closing_accounting = $this->prefix.'closing_accounting';
		$this->table_kode_rekening = $this->prefix_acc.'kode_rekening';
		$this->table_jurnal_detail = $this->prefix_acc.'jurnal_detail';
		$session_client_id = $this->session->userdata('client_id');	
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		$closing_date = $this->input->post('closing_date');
		if(empty($closing_date)){
			$keterangan = array('id' => '', 'keterangan' => 'Choose Closing Date!', 'total' => '');
			die(json_encode(array('data' => array(0 => $keterangan), 'totalCount' => 1)));
		}
		
		$tanggal = date("Y-m-d", strtotime($closing_date));
		
		// Default Parameter
		$this->db->select("a.*, b.kode_rek, b.nama_rek, b.parent, b.status_akun");
		$this->db->from($this->table_closing_accounting.' as a');
		$this->db->join($this->table_kode_rekening.' as b',"b.id = a.rek_id","LEFT");
		$this->db->where("tanggal", $tanggal);
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			$data_closing = array();
			foreach($dt_closing->result_array() as $dt){
				
				if(empty($data_closing[$dt['parent']])){
					$data_closing[$dt['parent']] = array();
				}
				
				$dt['kode_nama_rek_show'] = $dt['kode_rek'].' '.$dt['nama_rek'];
				$data_closing[$dt['parent']][] = $dt;
				
			}
			
			/*---------- SET PARENT - CHILD --------------------- */		
			$data = array(
				'data'		=> $data_closing,
				'parent'	=> 0,
				'coa_level'	=> 0
			);
			$data_akun = $this->kode_rekening_parent_child($data);
			/*---------- SET PARENT - CHILD --------------------- */
			
			//echo '<pre>';
			//print_r($data_akun);
			//die();
			
			//DATA MUTASI REK -- UPDATE HANYA KE DETAIL
			$jumlah_jurnal = 0;
			$jumlah_mutasi = 0;
			$jumlah_jurnal_id = array();
			
			$add_where = "(a.tgl_transaksi = '".$tanggal."')";
			
			$this->db->select("a.*, b.no_registrasi, b.kd_tipe_jurnal, b.is_posting, 
			b.no_jurnal, b.tgl_posting, b.status, b.periode, b.tahun,
			c.kode_rek, c.nama_rek");
			$this->db->from($this->table_jurnal_detail." as a");
			$this->db->join($this->prefix_acc.'jurnal_header as b','b.id = a.jurnal_header_id','LEFT');
			$this->db->join($this->prefix_acc.'kode_rekening as c','c.id = a.rek_id','LEFT');
			$this->db->where("b.is_posting", 1);
			$this->db->where("b.is_deleted", 0);
			$this->db->where($add_where);
			
			if(empty($sorting)){
				$this->db->order_by("a.tgl_transaksi","ASC");
			}else{
				$this->db->order_by($sorting,"ASC");
			}

			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				foreach($get_dt->result_array() as $dtMutasi){
					$jumlah_mutasi += $dtMutasi['jml_debet'];
					
					if(!in_array($dtMutasi['jurnal_header_id'], $jumlah_jurnal_id)){
						$jumlah_jurnal_id[] = $dtMutasi['jurnal_header_id'];
					}
					
				}			
			}
			
			
			$all_data_detail = array();
			
			$data_detail = array(
				'Closing Sales Date' => date("d-m-Y", strtotime($tanggal)),
				'Total Posting'		=> count($jumlah_jurnal_id), 
				'Total Mutasi'		=> priceFormat($jumlah_mutasi), 
				'&nbsp;'			=> '&nbsp;', 
				'<b>REKAP AKUN<b/>'	=> '&nbsp;'
			);
			
			$no = 0;
			foreach($data_detail as $ket => $val){
				$no++;
				
				$keterangan = $ket;
				if($val != '&nbsp;'){
					$keterangan .= ' = <b>'.$val.'</b>';
				}
				
				$all_data_detail [] = array(
					'id'			=> $no,
					'keterangan'	=> $keterangan,
					'saldo_awal'	=> '',
					'mutasi_debet'	=> '',
					'mutasi_kredit' => '',
					'saldo'			=> '',
				);
			}
			
			foreach($data_akun as $dt){
				$all_data_detail [] = array(
					'id'			=> 'rek_id_'.$dt['rek_id'],
					'keterangan'	=> $dt['kode_nama_rek_show'],
					'saldo_awal'	=> priceFormatAcc($dt['saldo_awal']),
					'mutasi_debet'	=> priceFormat($dt['mutasi_debet']),
					'mutasi_kredit' => priceFormat($dt['mutasi_kredit']),
					'saldo'			=> priceFormatAcc($dt['saldo'])
				);
			}
			
			
			
			$get_data = array('data' => $all_data_detail, 'totalCount' => count($all_data_detail));
		}else{
			$keterangan = array('id' => '', 'keterangan' => 'No Data or Not Been Generated!', 'total' => '');
			$get_data = array('data' => array( 0 => $keterangan), 'totalCount' => 1);
		}
		  		
      	die(json_encode($get_data));
	}
	
	public function generate()
	{
		$this->table_closing = $this->prefix.'closing';
		$this->table_closing_accounting = $this->prefix.'closing_accounting';
		$session_client_id = $this->session->userdata('client_id');	
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			die(json_encode($r));
		}
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		$is_check = $this->input->post('is_check');
		$current_total = $this->input->post('current_total');
		$closing_date = $this->input->post('closing_date');
		$closing_date = json_decode($closing_date);
		$tanggal = array();
		if(is_array($closing_date)){
			$tanggal = $closing_date;
		}else{
			$tanggal[] = $closing_date;
		}
		
		if(!empty($tanggal)){
			$tanggal_new = $tanggal;
			$tanggal = array();
			foreach($tanggal_new as $tgl){
				$tanggal[] = date("Y-m-d", strtotime($tgl));
			}
		}
		
		$tanggal_txt = implode("','", $tanggal);
		
		/*$updated_closing_date = array();
		//cek is been closing
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'accounting'");
		$this->db->where("a.tanggal IN ('".$tanggal_txt."')");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result() as $dtC){
				if($dtC->closing_status == 1){
					$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
					$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' Been Closing!');
					die(json_encode($r));
				}else{
					if(!in_array($dtC->tanggal, $updated_closing_date)){
						$updated_closing_date[] = $dtC->tanggal;
					}
				}
			}
			
		}*/
		
		//SORTING DATE -> LOW - HIGH
		$dt_tanggal = array();
		foreach($tanggal as $dt){
			$dt_tanggal[strtotime($dt)] = $dt;
		}
		
		ksort($dt_tanggal);
		
		$total_date = count($dt_tanggal);
		if($total_date > 31){
			//$r = array('success' => false, 'info'	=> 'Max Generate is 31 Days!');
			//die(json_encode($r));
		}
		
		//CEK FIRST TO START DATE
		$opt_value = array(
			'closing_accounting_start_date'
		);
		$get_opt = get_option_value($opt_value);
		
		
		if(!empty($get_opt['closing_accounting_start_date'])){
			$closing_accounting_start_date = $get_opt['closing_accounting_start_date'];
			$closing_accounting_start_date = date("Y-m-d", strtotime($closing_accounting_start_date));
		}
		
		if(empty($closing_accounting_start_date)){
			$closing_accounting_start_date = date("Y-m-d");
		}
		
		$today_date = date("Y-m-d");
		$date_current_total = '';
		$date_from = '';
		$date_till = '';
		$i = 0;
		foreach($dt_tanggal as $dtT){
			$i++;
			
			if($i == 1){
				$date_from = $dtT;
			}
			
			if(count($dt_tanggal) == $i){
				$date_till = $dtT;
			}
			
			if($i == $current_total){
				$date_current_total = $dtT;
			}
		}
		
		$updated_closing_date = array();
		if(!empty($date_current_total)){
			//cek is been closing
			$this->db->from($this->table_closing.' as a');
			$this->db->where("a.tipe = 'accounting'");
			$this->db->where("a.tanggal IN ('".$date_current_total."')");
			$dt_closing = $this->db->get();
			if($dt_closing->num_rows() > 0){
				foreach($dt_closing->result() as $dtC){
					if($dtC->closing_status == 1){
						$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
						$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' Been Closing!');
						die(json_encode($r));
					}else{
						if(!in_array($dtC->tanggal, $updated_closing_date)){
							$updated_closing_date[] = $dtC->tanggal;
						}
					}
				}
				
			}
		}
		
		if(strtotime($date_till) > strtotime($today_date)){
			$r = array('success' => false, 'info'	=> 'Max Generate Date is Today: '.date("d-m-Y").'!');
			die(json_encode($r));
		}
		
		$allowed_generate = false;
		
		//check on db < $date_from
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'accounting'");
		$this->db->where("a.tanggal < '".$date_from."' AND a.generate_status = 0");
		$this->db->order_by("a.tanggal", "DESC");
		$this->db->limit(1);
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			
			$dtC = $dt_closing->row();
			$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
			//echo $dtC->tanggal.' == '.$closing_accounting_start_date.'<br/>';
			
			if(strtotime($dtC->tanggal) < strtotime($closing_accounting_start_date)){
				$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' or < from date '.date("d-m-Y", strtotime($date_from)).' Not Been Generated!');
				die(json_encode($r));
			}
			
			
			
		}else{
			
			if($date_from >= $closing_accounting_start_date){
				//allowed
				$allowed_generate = true;
				//echo 'allowed_generate = closing_accounting_start_date<br/>';
			}else{
				$r = array('success' => false, 'info'	=> 'Date Closing From '.$closing_accounting_start_date.'!');
				die(json_encode($r));
			}
			
			//$allowed_generate = true;
			//echo 'allowed_generate -> ON DB > '.$date_from.'br/>';
		}
		
		if(!empty($is_check)){
			$r = array('success' => true, 'total_hari'	=> count($dt_tanggal));
			die(json_encode($r));
		}
		
		//CURRENT Date
		$date_from = $date_current_total;
		$date_till = $date_current_total;
		
		
		$data_generate = array();
		
		//BEGIN GENERATE ---> FROM REPORT SALES
		$this->table_jurnal_header = $this->prefix_acc.'jurnal_header';
		$this->table_jurnal_detail = $this->prefix_acc.'jurnal_detail';
		$this->table_neraca_saldo = $this->prefix_acc.'neraca_saldo';
		$this->table_kode_rekening = $this->prefix_acc.'kode_rekening';
		
		$mktime_dari = strtotime($date_from);
		$mktime_sampai = strtotime($date_till);
					
		$qdate_bulan = date("m",strtotime($date_from));
		$qdate_tahun = date("Y",strtotime($date_from));
		$qdate_from = date("Y-m-d",strtotime($date_from));
		$qdate_till = date("Y-m-d",strtotime($date_till));
		$qdate_till_max = date("Y-m-d",strtotime($date_till));
		
		
		//REKENING - MUTASI HARIAN / CLOSING HARIAN
		$data_kode_rekening = array();
		$this->db->select("a.*");
		$this->db->from($this->table_kode_rekening." as a");
		$this->db->where("a.is_deleted = 0");
		$get_kode_rekening = $this->db->get();
		if($get_kode_rekening->num_rows() > 0){
			foreach($get_kode_rekening->result_array() as $dt){
				if(empty($data_kode_rekening[$dt['id']])){
					$data_kode_rekening[$dt['id']] = array(
						'rek_id'				=> $dt['id'],
						'parent'				=> $dt['parent'],
						'kd_kel_akun'			=> $dt['kd_kel_akun'],
						'kd_kel_akun_detail'	=> $dt['kd_kel_akun_detail'],
						'kode_rek'				=> $dt['kode_rek'],
						'nama_rek'				=> $dt['nama_rek'],
						'tanggal'				=> $qdate_from,
						'jumlah_transaksi'		=> 0,
						'saldo_awal'			=> 0,
						'mutasi_debet'			=> 0,
						'mutasi_kredit'			=> 0,
						'saldo'					=> 0,
						'saldo_awal_calc'		=> 0,
						'mutasi_debet_calc'		=> 0,
						'mutasi_kredit_calc'	=> 0,
						'saldo_calc'			=> 0,
						'periode'				=> $qdate_bulan,
						'tahun'					=> $qdate_tahun,
						'posisi'				=> $dt['posisi_akun'],
						'status_akun'			=> $dt['status_akun'],
					);
				}
			}
		}
		
		//echo '<pre>';
		//print_r($data_kode_rekening);
		//die();
		
		//DATA NERACA SALDO BULAN SEBELUMNYA
		$bulan_sebelumnya = (int) $qdate_bulan - 1;
		$tahun_sebelumnya = $qdate_tahun;
		if($bulan_sebelumnya <= 0){
			$bulan_sebelumnya = 12;
			$tahun_sebelumnya -= 1;
		}
		
		if(strlen($bulan_sebelumnya) == 1){
			$bulan_sebelumnya = "0".$bulan_sebelumnya;
		}
		
		$this->db->from($this->table_neraca_saldo." as a");
		$this->db->where("a.periode = '".$bulan_sebelumnya."'");
		$this->db->where("a.tahun = '".$tahun_sebelumnya."'");
		$get_neraca_saldo = $this->db->get();
		if($get_neraca_saldo->num_rows() > 0){
			foreach($get_neraca_saldo->result_array() as $dtLastMonth){
				if(!empty($data_kode_rekening[$dtLastMonth['rek_id']])){
					$data_kode_rekening[$dtLastMonth['rek_id']]['saldo_awal'] = $dtLastMonth['saldo'];
					$data_kode_rekening[$dtLastMonth['rek_id']]['saldo'] = $dtLastMonth['saldo'];
				}
			}
				
		}
		
		//echo '<pre>';
		//print_r($data_kode_rekening);
		//die();
		
		$hari_sebelumnya_mk = strtotime($qdate_from) - ONE_DAY_UNIX;
		$hari_sebelumnya = date("Y-m-d", $hari_sebelumnya_mk);
		
		//table_closing_accounting --> MUTASI HARI SEBELUMNYA
		$this->db->from($this->table_closing_accounting." as a");
		$this->db->where("a.tanggal = '".$hari_sebelumnya."'");
		$get_closing_accounting = $this->db->get();
		if($get_closing_accounting->num_rows() > 0){
			foreach($get_closing_accounting->result_array() as $dtYesterday){
				if(!empty($data_kode_rekening[$dtYesterday['rek_id']])){
					$data_kode_rekening[$dtYesterday['rek_id']]['saldo_awal'] = $dtYesterday['saldo'];
					$data_kode_rekening[$dtYesterday['rek_id']]['saldo'] = $dtYesterday['saldo'];
				}
			}
				
		}
		
		
		//DATA MUTASI REK -- UPDATE HANYA KE DETAIL
		$jumlah_jurnal = 0;
		$jumlah_jurnal_id = array();
		
		$add_where = "(a.tgl_transaksi >= '".$qdate_from."' AND a.tgl_transaksi <= '".$qdate_till_max."')";
		
		$this->db->select("a.*, b.no_registrasi, b.kd_tipe_jurnal, b.is_posting, 
		b.no_jurnal, b.tgl_posting, b.status, b.periode, b.tahun,
		c.kode_rek, c.nama_rek");
		$this->db->from($this->table_jurnal_detail." as a");
		$this->db->join($this->prefix_acc.'jurnal_header as b','b.id = a.jurnal_header_id','LEFT');
		$this->db->join($this->prefix_acc.'kode_rekening as c','c.id = a.rek_id','LEFT');
		$this->db->where("b.is_posting", 1);
		$this->db->where("b.is_deleted", 0);
		$this->db->where($add_where);
		
		if(empty($sorting)){
			$this->db->order_by("a.tgl_transaksi","ASC");
		}else{
			$this->db->order_by($sorting,"ASC");
		}

		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			foreach($get_dt->result_array() as $dtMutasi){
				if(!empty($data_kode_rekening[$dtMutasi['rek_id']])){
					$data_kode_rekening[$dtMutasi['rek_id']]['jumlah_transaksi'] += 1;
					$data_kode_rekening[$dtMutasi['rek_id']]['mutasi_debet'] += $dtMutasi['jml_debet'];
					$data_kode_rekening[$dtMutasi['rek_id']]['mutasi_kredit'] += $dtMutasi['jml_kredit'];
					$data_kode_rekening[$dtMutasi['rek_id']]['saldo'] += ($dtMutasi['jml_debet'] - $dtMutasi['jml_kredit']);
				}
				
				if(!in_array($dtMutasi['jurnal_header_id'], $jumlah_jurnal_id)){
					$jumlah_jurnal_id[] = $dtMutasi['jurnal_header_id'];
				}
				
			}			
		}
		
		//echo '<pre>';
		//print_r($data_kode_rekening);
		//die();
		
		$dt_parent = array();
		foreach($data_kode_rekening as $dt){
			if(empty($dt_parent[$dt['parent']])){
				$dt_parent[$dt['parent']] = array();
			}
			
			$dt_parent[$dt['parent']][] = $dt;
			
		}
		
		//echo '<pre>';
		//print_r($dt_parent);
		//die();
		
		//RE-CALCULATING TO HEADER
		/*---------- SET PARENT - CHILD --------------------- */		
		$data = array(
			'data'		=> $dt_parent,
			'parent'	=> 0
		);
		$newData = $this->calculation_parent_child($data);
		/*---------- SET PARENT - CHILD --------------------- */
		
		//echo '<pre>';
		//print_r($newData);
		//die();
		
		$insert_date = array();
		$update_date = array();
		$insert_closing_accounting = array();
		$updated_closing_accounting = array();
		$insert_closing = array();
		$updated_closing = array();
		if(!empty($newData)){
			foreach($newData as $dt){
				
				$dt['saldo_awal'] = $dt['saldo_awal_calc'];
				$dt['mutasi_debet'] = $dt['mutasi_debet_calc'];
				$dt['mutasi_kredit'] = $dt['mutasi_kredit_calc'];
				$dt['saldo'] = $dt['saldo_calc'];
				
				unset($dt['saldo_awal_calc']);
				unset($dt['mutasi_debet_calc']);
				unset($dt['mutasi_kredit_calc']);
				unset($dt['saldo_calc']);
				unset($dt['parent']);
				unset($dt['kd_kel_akun']);
				unset($dt['kd_kel_akun_detail']);
				unset($dt['kode_rek']);
				unset($dt['nama_rek']);
				unset($dt['status_akun']);
				
				if($date_current_total == $dt['tanggal']){
					if(in_array($dt['tanggal'], $updated_closing_date)){
						
						$dt['updated'] = date('Y-m-d H:i:s');
						$dt['updatedby'] = $session_user;
						$updated_closing_accounting[] = $dt;
						
						if(!in_array($dt['tanggal'], $update_date)){
							$update_date[] = $dt['tanggal'];
							
							$bulan = date("m", strtotime($dt['tanggal']));
							$tahun = date("Y", strtotime($dt['tanggal']));
							
							$updated_closing[] = array(
								'tanggal'	=> $dt['tanggal'],
								'bulan'	=> $bulan,
								'tahun'	=> $tahun,
								'tipe'	=> 'accounting',
								'closing_status'	=> 0,
								'generate_status'	=> 1,
								'updated'		=>	date('Y-m-d H:i:s'),
								'updatedby'		=>	$session_user,
							);
						}
						
					}else{
						
						$dt['created'] = date('Y-m-d H:i:s');
						$dt['createdby'] = $session_user;
						$dt['updated'] = date('Y-m-d H:i:s');
						$dt['updatedby'] = $session_user;
						$insert_closing_accounting[] = $dt;
						
						if(!in_array($dt['tanggal'], $insert_date)){
							$insert_date[] = $dt['tanggal'];
							
							$bulan = date("m", strtotime($dt['tanggal']));
							$tahun = date("Y", strtotime($dt['tanggal']));
							
							$insert_closing[] = array(
								'tanggal'	=> $dt['tanggal'],
								'bulan'	=> $bulan,
								'tahun'	=> $tahun,
								'tipe'	=> 'accounting',
								'closing_status'	=> 0,
								'generate_status'	=> 1,
								'created'		=>	date('Y-m-d H:i:s'),
								'createdby'		=>	$session_user,
								'updated'		=>	date('Y-m-d H:i:s'),
								'updatedby'		=>	$session_user
							);
						}
						
					}
				}
				
			}
		}
		
		//echo '<pre>';
		//print_r($updated_closing_accounting);
		//die();
		
		
		if(!empty($insert_closing)){
			//remove if available
			if(!empty($insert_date)){
				$insert_date_txt = implode("','", $insert_date);
				$this->db->delete($this->table_closing, "tanggal IN ('".$insert_date_txt."') AND tipe = 'accounting'");
			}
			
			$this->db->insert_batch($this->table_closing, $insert_closing);
		}
		
		if(!empty($insert_closing_accounting)){
			//remove if available
			if(!empty($insert_date)){
				$insert_date_txt = implode("','", $insert_date);
				$this->db->delete($this->table_closing_accounting, "tanggal IN ('".$insert_date_txt."')");
			}
			
			$this->db->insert_batch($this->table_closing_accounting, $insert_closing_accounting);
		}
		
		if(!empty($updated_closing)){
			
			//REMOVE OLD
			$updated_closing_date_txt = implode("','", $updated_closing_date);
			$this->db->delete($this->table_closing,"tanggal IN ('".$updated_closing_date_txt."')  AND tipe = 'accounting'");
			$this->db->insert_batch($this->table_closing, $updated_closing);
			//$this->db->update_batch($this->table_closing, $updated_closing, 'tanggal');
		}
		
		if(!empty($updated_closing_accounting)){
			
			//REMOVE OLD
			$updated_closing_date_txt = implode("','", $updated_closing_date);
			$this->db->delete($this->table_closing_accounting,"tanggal IN ('".$updated_closing_date_txt."')");
			$this->db->insert_batch($this->table_closing_accounting, $updated_closing_accounting);
			//$this->db->update_batch($this->table_closing_accounting, $updated_closing_accounting, 'tanggal');
		}
		
		
		//UPDATE table_neraca_saldo
		//create bulan ini jika belum ada
		$table_neraca_saldo_update = array();
		$table_neraca_saldo_update_rek_id = array();
		$table_neraca_saldo_insert = array();
		$bulan_ini = date("m",strtotime($date_from));
		$tahun_ini = date("Y",strtotime($date_from));
		$this->db->from($this->table_neraca_saldo." as a");
		$this->db->where("a.periode = '".$bulan_ini."'");
		$this->db->where("a.tahun = '".$tahun_ini."'");
		$get_neraca_saldo = $this->db->get();
		if($get_neraca_saldo->num_rows() > 0){
			
			foreach($get_neraca_saldo->result_array() as $dt){
				
				if(!in_array($dt['rek_id'], $table_neraca_saldo_update_rek_id)){
					$table_neraca_saldo_update_rek_id[] = $dt['rek_id'];
				}
			}
			
			if(!empty($data_kode_rekening)){
				foreach($data_kode_rekening as $dtr){
					
					if(!in_array($dtr['rek_id'], $table_neraca_saldo_update_rek_id)){
						$table_neraca_saldo_insert[] = array(
							'rek_id'		=> $dtr['rek_id'],
							'periode'		=> $dtr['periode'],
							'ket_periode'	=> get_month($dtr['periode']),
							'saldo_awal'	=> 0,
							'mutasi_debet'	=> 0,
							'mutasi_kredit'	=> 0,
							'saldo'			=> 0,
							'posisi'		=> $dtr['posisi'],
							'tahun'			=> $dtr['tahun'],
							'created'		=> date('Y-m-d H:i:s'),
							'createdby'		=> $session_user,
							'updated'		=> date('Y-m-d H:i:s'),
							'updatedby'		=> $session_user
						);
					}
					
				}
			}
				
		}else{
			
			//CREATE
			if(!empty($data_kode_rekening)){
				foreach($data_kode_rekening as $dtr){
					$table_neraca_saldo_insert[] = array(
						'rek_id'		=> $dtr['rek_id'],
						'periode'		=> $dtr['periode'],
						'ket_periode'	=> get_month($dtr['periode']),
						'saldo_awal'	=> 0,
						'mutasi_debet'	=> 0,
						'mutasi_kredit'	=> 0,
						'saldo'			=> 0,
						'posisi'		=> $dtr['posisi'],
						'tahun'			=> $dtr['tahun'],
						'created'		=> date('Y-m-d H:i:s'),
						'createdby'		=> $session_user,
						'updated'		=> date('Y-m-d H:i:s'),
						'updatedby'		=> $session_user
					);
				}
			}
			
		}
		
		if(!empty($table_neraca_saldo_insert)){
			$this->db->insert_batch($this->table_neraca_saldo, $table_neraca_saldo_insert);
		}
		
		//MUTASI NERACA SALDO
		$date_from_ns = date("Y-m-",strtotime($date_from))."01";
		$date_till_ns = date("Y-m-d",strtotime($date_from));
		$date_generate = date("Y-m-d",strtotime($date_from));
		$date_till_this_month = date("Y-m-t",strtotime($date_from));
		
		$saldo_awal = array();
		$mutasi_debet = array();
		$mutasi_kredit = array();
		$total_saldo = array();
		$this->db->select("a.*");
		$this->db->from($this->table_closing_accounting." as a");
		$this->db->where("a.tanggal >= '".$date_from_ns."' AND a.tanggal <= '".$date_till_ns."'");
		$this->db->order_by("a.tanggal","ASC");
		$get_closing_acc = $this->db->get();
		if($get_closing_acc->num_rows() > 0){
			foreach($get_closing_acc->result_array() as $dt){
				if($dt['tanggal'] == $date_from_ns){
					if(empty($saldo_awal[$dt['rek_id']])){
						$saldo_awal[$dt['rek_id']] = 0;
					}
					
					$saldo_awal[$dt['rek_id']] = $dt['saldo_awal'];
				}
				
				if(empty($mutasi_debet[$dt['rek_id']])){
					$mutasi_debet[$dt['rek_id']] = 0;
				}
				$mutasi_debet[$dt['rek_id']] += $dt['mutasi_debet'];
				
				if(empty($mutasi_kredit[$dt['rek_id']])){
					$mutasi_kredit[$dt['rek_id']] = 0;
				}
				$mutasi_kredit[$dt['rek_id']] += $dt['mutasi_kredit'];
				
				if(empty($total_saldo[$dt['rek_id']])){
					$total_saldo[$dt['rek_id']] = 0;
				}
				$total_saldo[$dt['rek_id']] = $dt['saldo'];
				
			}
		}
		
		//echo 'SALDO AWAL = '.$saldo_awal[1].'<br/>';
		//echo 'MUTASI D = '.$mutasi_debet[1].'<br/>';
		//echo 'MUTASI K = '.$mutasi_kredit[1].'<br/>';
		//echo 'SALDO = '.$total_saldo[1].'<br/>';
		//die();
		
		//UPDATE NERACA SALDO
		$all_ns = array();
		$this->db->from($this->table_neraca_saldo." as a");
		$this->db->where("a.periode = '".$bulan_ini."'");
		$this->db->where("a.tahun = '".$tahun_ini."'");
		$get_neraca_saldo = $this->db->get();
		if($get_neraca_saldo->num_rows() > 0){
			foreach($get_neraca_saldo->result_array() as $dt){
				
				$dt['saldo_awal'] = 0;
				$dt['mutasi_debet'] = 0;
				$dt['mutasi_kredit'] = 0;
				$dt['saldo'] = 0;
				
				if(!empty($saldo_awal[$dt['rek_id']])){
					$dt['saldo_awal'] = $saldo_awal[$dt['rek_id']];
				}
				if(!empty($mutasi_debet[$dt['rek_id']])){
					$dt['mutasi_debet'] = $mutasi_debet[$dt['rek_id']];
				}
				if(!empty($mutasi_kredit[$dt['rek_id']])){
					$dt['mutasi_kredit'] = $mutasi_kredit[$dt['rek_id']];
				}
				if(!empty($total_saldo[$dt['rek_id']])){
					$dt['saldo'] = $total_saldo[$dt['rek_id']];
				}
				
				$dt['saldo'] = $dt['saldo_awal'] + ($dt['mutasi_debet'] - $dt['mutasi_kredit']);
				
				$table_neraca_saldo_update[$dt['id']] = $dt;
				$all_ns[$dt['rek_id']] = $dt;
			}
		}
		
		if(!empty($table_neraca_saldo_update)){
			$this->db->update_batch($this->table_neraca_saldo, $table_neraca_saldo_update, "id");
		}
		
		//UPDATE LABA RUGI
		if($date_generate == $date_till_this_month){
			
			//get all akun
			$this->db->select("a.*, b.nama_kel_akun, c.nama_kel_akun_detail");
			$this->db->from($this->table_kode_rekening.' as a');
			$this->db->join($this->prefix_acc.'kel_akun as b','b.kd_kel_akun = a.kd_kel_akun','LEFT');
			$this->db->join($this->prefix_acc.'kel_akun_detail as c','c.kd_kel_akun_detail = a.kd_kel_akun_detail','LEFT');
			//$this->db->join($this->prefix.'saldo_awal as d','d.id_rek = a.id','LEFT');
			
			//KELOMPOK AKUN NERACA 4,5 (pendapatan & Biaya)
			$this->db->where("a.kd_kel_akun IN (4,5,6,7)");
			
			$this->db->where('a.is_deleted', 0);
			$this->db->order_by('a.kode_rek', 'ASC');
			$get_akun = $this->db->get();
			
			//re-assign per-parent
			$main_parent_LR = array();
			$main_parent_kel_akun = array();
			$dt_parent = array();
			$all_akun = array();
			
			if($get_akun->num_rows() > 0){
				foreach ($get_akun->result_array() as $s){
					
					$s['nama_rek_show'] = $s['nama_rek'];
					$s['kode_nama_rek'] = $s['kode_rek'].' - '.$s['nama_rek'];
					$s['kode_nama_rek_show'] = $s['kode_rek'].' - '.$s['nama_rek'];
					
					$s['kode_rek_level'] = $s['kode_rek'];
					
					$s['jumlah_saldo'] = 0;
					$s['jumlah_kredit'] = 0;
					$s['jumlah_debet'] = 0;
					
					/*
					if($s['posisi_akun'] == 'K'){
						$s['jumlah_saldo'] = $s['jumlah_kredit'];
					}
					
					if($s['posisi_akun'] == 'D'){
						$s['jumlah_saldo'] = $s['jumlah_debet'];
					}
					*/
					
					$s['bulan_sebelumnya'] = 0;
					$s['mutasi_debet'] = 0;
					$s['mutasi_kredit'] = 0;
					$s['saldo_akhir'] = 0;				

					$s['bulan_sebelumnya_calc'] = 0;
					$s['mutasi_debet_calc'] = 0;
					$s['mutasi_kredit_calc'] = 0;
					$s['saldo_akhir_calc'] = 0;
					
					if(empty($dt_parent[$s['parent']])){
						$dt_parent[$s['parent']] = array();
					}
					$dt_parent[$s['parent']][] = $s;
					
					if($s['parent'] == 0){
						if(!in_array($s['id'], $main_parent_LR)){
							$main_parent_LR[] = $s['id'];
							
							$main_parent_kel_akun[$s['kd_kel_akun']] = $s['id'];
							
						}
					}
					
					array_push($all_akun, $s);
				}
			}
				
			$all_ns_before = array();
			$data = array(
				'data'		=> $dt_parent,
				'parent'	=> 0,
				'coa_level'	=> 0,
				'all_ns'	=> $all_ns,
				'all_ns_before'	=> $all_ns_before,
				'save_child'	=> 1
			);
			$report_data_LR = $this->kode_rekening_parent_child_LR($data);
			
			$created_date = date("Y-m-d H:i:s");
			$save_dt_LR = array();
			if(!empty($report_data_LR)){
				foreach($report_data_LR as $dtLR){
					
					if($dtLR['mutasi_kredit'] < 0){
						$dtLR['mutasi_kredit'] = $dtLR['mutasi_kredit']*-1;
					}
					
					
					$saldo = $dtLR['bulan_sebelumnya'] + ($dtLR['mutasi_debet']-$dtLR['mutasi_kredit']);
					
					$save_dt_LR[] = array(
						'rek_id'		=> $dtLR['id'],
						'periode'		=> $bulan_ini,
						'ket_periode'	=> get_month($bulan_ini),
						'tahun'			=> $tahun_ini,
						'posisi'		=> $dtLR['posisi_akun'],
						'saldo_awal'	=> $dtLR['bulan_sebelumnya'],
						'mutasi_debet'	=> $dtLR['mutasi_debet'],
						'mutasi_kredit'	=> $dtLR['mutasi_kredit'],
						'saldo'			=> $saldo,
						'createdby'		=> $session_user,
						'created'		=> $created_date,
						'updatedby'		=> $session_user,
						'updated'		=> $created_date,
					);
					
				}
			}
			
			//cek closing_periode
			$status_closing = 'open';
			$this->db->from($this->prefix_acc."closing_periode");
			$this->db->where("periode",$bulan_ini);
			$this->db->where("tahun",$tahun_ini);
			$this->db->where("status", 'close');
			$get_dt_closing = $this->db->get();
			if($get_dt_closing->num_rows() > 0){
				$status_closing = 'close';
			}else{
				if(!empty($save_dt_LR)){
					
					//UPDATE / SAVE
					$this->db->where("periode",$bulan);
					$this->db->where("tahun",$tahun);
					$this->db->delete($this->prefix_acc."laba_rugi");
					
					$this->db->insert_batch($this->prefix_acc."laba_rugi", $save_dt_LR);
					
				}
			}
			
			$get_opt = get_option_value(array("kel_LR_biaya_atas_pendapatan","kel_LR_hpp","bulan_berjalan","tahun_berjalan","lr_tahun_lalu","lr_tahun_berjalan","lr_bulan_berjalan"));
		
			if(!empty($report_data_LR)){
				
					$data_biaya_atas_pendapatan = array();
					$data_biaya_atas_pendapatan_id = array();
					
					if(!empty($get_opt['kel_LR_biaya_atas_pendapatan'])){
						$kel_LR_biaya_atas_pendapatan = $get_opt['kel_LR_biaya_atas_pendapatan'];
						$parent_aktif = 0;
						foreach($report_data_LR as $akun){
							if($akun['kd_kel_akun_detail'] == $kel_LR_biaya_atas_pendapatan){
								
								if(!in_array($akun['id'], $data_biaya_atas_pendapatan_id)){
									$data_biaya_atas_pendapatan_id[] = $akun['id'];
								}
								
								if($akun['status_akun'] == 'detail'){
									$data_biaya_atas_pendapatan[] = $akun;
								}else{
									$parent_aktif = $akun['id'];
									$data_biaya_atas_pendapatan[] = $akun;
								}
								
							}
						}
					}
					
					$data_hpp = array();
					$data_hpp_id = array();
					if(!empty($get_opt['kel_LR_hpp'])){
						$kel_LR_hpp = $get_opt['kel_LR_hpp'];
						foreach($report_data_LR as $akun){
							if($akun['kd_kel_akun_detail'] == $kel_LR_hpp){
								
								if(!in_array($akun['id'], $data_hpp_id)){
									$data_hpp_id[] = $akun['id'];
								}
								
								if($akun['status_akun'] == 'detail'){
									$data_hpp[] = $akun;
								}else{
									$data_hpp[] = $akun;
								}
								
							}
						}
					}
					
					$group_lr = array();
					$group_lr_main = array();
					foreach($report_data_LR as $akun){
						//if(in_array($akun['id'], $main_parent_LR)){
							
							if(!in_array($akun['kd_kel_akun'], $group_lr_main)){
								$group_lr_main[] = $akun['kd_kel_akun'];
							}
							
							//data_biaya_atas_pendapatan
							if(in_array($akun['id'], $data_biaya_atas_pendapatan_id)){
								$akun['kd_kel_akun'] = 4;
							}
							
							//data_hpp_id
							if(in_array($akun['id'], $data_hpp_id)){
								$akun['kd_kel_akun'] = 'hpp';
							}
							
							if(empty($group_lr[$akun['kd_kel_akun']])){
								$group_lr[$akun['kd_kel_akun']] = array();
							}
							
							$group_lr[$akun['kd_kel_akun']][] = $akun;
							
						//}
					}
					
					//echo '<pre>';
					//print_r($data_biaya_atas_pendapatan);
					//die();
			}
			
			//PENDAPATAN = 4
			$total_pendapatan = 0;
			if(!empty($group_lr[4])){
				foreach($group_lr[4] as $akun){
					
					if(in_array($akun['id'], $main_parent_LR)){
						
					}else{
						
						if(in_array($akun['id'], $data_biaya_atas_pendapatan_id)){
							$akun['saldo_akhir'] = $akun['saldo_akhir']*-1;
							$akun['saldo_akhir_calc'] = $akun['saldo_akhir_calc']*-1;
						}
						
						if($akun['status_akun'] == 'detail'){
									
							if(!empty($akun['saldo_akhir'])){
								
								if($akun['coa_level'] <= 2){
									
									$total_pendapatan += $akun['saldo_akhir'];
								}
							}
							
						}else{
						
							if($akun['coa_level'] <= 2){
								$total_pendapatan += $akun['saldo_akhir_calc'];
							}
							
						}
					}
					
				}
			}
			
			//HPP
			$total_hpp = 0;
			if(!empty($group_lr['hpp'])){
				
				$no = 1;
				foreach($group_lr['hpp'] as $akun){
					
						if($akun['status_akun'] == 'detail'){
									
							if(!empty($akun['saldo_akhir'])){
								
								if($akun['coa_level'] <= 2){
								
									$total_hpp += $akun['saldo_akhir'];
								}
							}
							
						}else{
						
							if($akun['coa_level'] <= 2){
								$total_hpp += $akun['saldo_akhir_calc'];
							}
						}
						
					$no++;
					
				}
			}
			
			//BEBAN = 5
			$total_beban = 0;
			if(!empty($group_lr[5])){
				foreach($group_lr[5] as $akun){
					
					if(in_array($akun['id'], $main_parent_LR)){
						
					}else{
						
						if($akun['status_akun'] == 'detail'){
									
							if(!empty($akun['saldo_akhir'])){
								
								if($akun['coa_level'] <= 3){
									
									if($akun['coa_level'] <= 2){
										$total_beban += $akun['saldo_akhir'];
									}
								}
								
							}
							
						}else{
						
							if($akun['coa_level'] <= 2){
								$total_beban += $akun['saldo_akhir_calc'];
							}
						}
					}
					
				}
				
			}
			
			$total_laba_kotor = $total_pendapatan - ($total_hpp+$total_beban);
			
			//PENDAPATAN LAIN-LAIN = 6
			$total_pendapatan_lain = 0;
			if(!empty($group_lr[6])){
				foreach($group_lr[6] as $akun){
					
					if(in_array($akun['id'], $main_parent_LR)){
						
					}else{
						
						if($akun['status_akun'] == 'detail'){
									
							if(!empty($akun['saldo_akhir'])){
								
								if($akun['coa_level'] <= 2){
									$total_pendapatan_lain += $akun['saldo_akhir'];
								}
								
							}
							
						}else{
							
							if($akun['coa_level'] <= 2){
								$total_pendapatan_lain += $akun['saldo_akhir_calc'];
							}
							
						}
					}
					
				}
			}
			
			//BEBAN LAIN-LAIN = 7
			$total_beban_lain = 0;
			if(!empty($group_lr[7])){
				foreach($group_lr[7] as $akun){
					
					if(in_array($akun['id'], $main_parent_LR)){
						
					}else{
						
						if($akun['status_akun'] == 'detail'){
									
							if(!empty($akun['saldo_akhir'])){
								
								if($akun['coa_level'] <= 2){
									$total_beban_lain += $akun['saldo_akhir'];
								}
								
							}
							
						}else{
							
							if($akun['coa_level'] <= 2){
								$total_beban_lain += $akun['saldo_akhir_calc'];
							}
							
						}
					}
					
				}
			}
			
			//LABA(RUGI) BERJALAN
			$berjalan_txt = '';
			
			$bulan_berjalan = 0;
			if(!empty($get_opt['bulan_berjalan'])){
				$bulan_berjalan = $get_opt['bulan_berjalan'];
			}
			
			$tahun_berjalan = 0;
			if(!empty($get_opt['tahun_berjalan'])){
				$tahun_berjalan = $get_opt['tahun_berjalan'];
				$bulan_berjalan_mk = strtotime("01-".$bulan_berjalan."-".$tahun_berjalan);
			}
			
			$selected_date_mk = strtotime("01-".$bulan."-".$tahun);
			if($selected_date_mk >= $bulan_berjalan_mk){
				$berjalan_txt = ' BERJALAN';
			}
			
			$total_laba_bersih = $total_laba_kotor + ($total_pendapatan_lain - $total_beban_lain);
			
			if($status_closing == 'open'){
				//SAVE AKUMULASI DI NERACA SALDO
				
				if(!empty($get_opt['lr_tahun_lalu']) AND !empty($get_opt['lr_tahun_berjalan']) AND !empty($get_opt['lr_bulan_berjalan'])){
					
					$lr_tahun_lalu = $get_opt['lr_tahun_lalu'];
					$lr_tahun_berjalan = $get_opt['lr_tahun_berjalan'];
					$lr_bulan_berjalan = $get_opt['lr_bulan_berjalan'];
					
					//ambil saldo awal bulan kemarin
					$all_ns = array();
					$this->db->from($this->table_neraca_saldo);
					$this->db->where('periode', $bulan_sebelumnya);
					$this->db->where('tahun', $tahun_sebelumnya);
					$this->db->where("rek_id IN (".$lr_tahun_lalu.",".$lr_tahun_berjalan.",".$lr_bulan_berjalan.")");
					$get_ns = $this->db->get();
					if($get_ns->num_rows() > 0){
						foreach($get_ns->result_array() as $dt){
							$all_ns[$dt['rek_id']] = $dt;
						}
					}
					
					//saldo periode
					$all_update_ns = array();
					$this->db->from($this->table_neraca_saldo);
					$this->db->where('periode', $bulan);
					$this->db->where('tahun', $tahun);
					$this->db->where("rek_id IN (".$lr_tahun_lalu.",".$lr_tahun_berjalan.",".$lr_bulan_berjalan.")");
					$get_ns = $this->db->get();
					if($get_ns->num_rows() > 0){
						foreach($get_ns->result_array() as $dt){
							$all_update_ns[$dt['rek_id']] = $dt;
						}
					}
					
					//UPDATE TAHUN LALU PADA PERIODE JANUARI
					$created_date = date("Y-m-d H:i:s");
					$all_new_ns = array();
					if($bulan_sebelumnya == 12){
						
						//JIKA JANUARI
						$new_saldo_awal_lr_tahun_lalu = 0;
						$new_mutasi_debet_lr_tahun_lalu = 0;
						$new_mutasi_kredit_lr_tahun_lalu = 0;
						
						//AMBIL DR TAHUN BERJALAN
						if(!empty($all_ns[$lr_tahun_berjalan])){
							$new_saldo_awal_lr_tahun_lalu = $all_ns[$lr_tahun_berjalan]['saldo_awal'];
							$new_mutasi_debet_lr_tahun_lalu = $all_ns[$lr_tahun_berjalan]['mutasi_debet'];
							$new_mutasi_kredit_lr_tahun_lalu = $all_ns[$lr_tahun_berjalan]['mutasi_kredit'];
						}
						
						$new_saldo_akhir_lr_tahun_lalu = $new_saldo_awal_lr_tahun_lalu + ($new_mutasi_debet_lr_tahun_lalu - $new_mutasi_kredit_lr_tahun_lalu);
					
						if(!empty($all_update_ns[$lr_tahun_lalu])){
							
							$all_update_ns[$lr_tahun_lalu]['saldo_awal'] = $new_saldo_awal_lr_tahun_lalu;
							$all_update_ns[$lr_tahun_lalu]['mutasi_debet'] = $new_mutasi_debet_lr_tahun_lalu;
							$all_update_ns[$lr_tahun_lalu]['mutasi_kredit'] = $new_mutasi_kredit_lr_tahun_lalu;
							$all_update_ns[$lr_tahun_lalu]['saldo'] = $new_saldo_akhir_lr_tahun_lalu;
							$all_update_ns[$lr_tahun_lalu]['updatedby'] = $session_user;
							$all_update_ns[$lr_tahun_lalu]['updated'] = $created_date;
							
						}else{
							
							if(!empty($all_ns[$lr_tahun_lalu])){
								$all_new_ns[$lr_tahun_lalu] = $all_ns[$lr_tahun_lalu];
								
								unset($all_new_ns[$lr_tahun_lalu]['id']);
								$all_new_ns[$lr_tahun_lalu]['periode'] = $bulan;
								$all_new_ns[$lr_tahun_lalu]['ket_periode'] = get_month($bulan);
								$all_new_ns[$lr_tahun_lalu]['tahun'] = $tahun;
								$all_new_ns[$lr_tahun_lalu]['saldo_awal'] = $new_saldo_awal_lr_tahun_lalu;
								$all_new_ns[$lr_tahun_lalu]['mutasi_debet'] = $new_mutasi_debet_lr_tahun_lalu;
								$all_new_ns[$lr_tahun_lalu]['mutasi_kredit'] = $new_mutasi_kredit_lr_tahun_lalu;
								$all_new_ns[$lr_tahun_lalu]['saldo'] = $new_saldo_akhir_lr_tahun_lalu;
								$all_new_ns[$lr_tahun_lalu]['createdby'] = $session_user;
								$all_new_ns[$lr_tahun_lalu]['created'] = $created_date;
								$all_new_ns[$lr_tahun_lalu]['updatedby'] = $session_user;
								$all_new_ns[$lr_tahun_lalu]['updated'] = $created_date;
							}
							
						}
							
						//UPDATE TAHUN BERJALAN
						//$new_saldo_awal_lr_tahun_berjalan = $new_saldo_akhir_lr_tahun_lalu;
						$new_saldo_awal_lr_tahun_berjalan = 0;
						$new_mutasi_debet_lr_tahun_berjalan = 0;
						$new_mutasi_kredit_lr_tahun_berjalan = 0;
						
						//$total_laba_bersih_LR = $total_laba_bersih;
						/*if($total_laba_bersih_LR < 0){
							//K
							$total_laba_bersih_LR = $total_laba_bersih_LR*-1;
							$new_mutasi_kredit_lr_tahun_berjalan += $total_laba_bersih_LR;
						}else{
							$new_mutasi_debet_lr_tahun_berjalan += $total_laba_bersih_LR;
						}*/
						
						$new_saldo_akhir_lr_tahun_berjalan = $new_saldo_awal_lr_tahun_berjalan + ($new_mutasi_debet_lr_tahun_berjalan - $new_mutasi_kredit_lr_tahun_berjalan);
						
						if(!empty($all_update_ns[$lr_tahun_berjalan])){
								
							$all_update_ns[$lr_tahun_berjalan]['saldo_awal'] = $new_saldo_awal_lr_tahun_berjalan;
							$all_update_ns[$lr_tahun_berjalan]['mutasi_debet'] = $new_mutasi_debet_lr_tahun_berjalan;
							$all_update_ns[$lr_tahun_berjalan]['mutasi_kredit'] = $new_mutasi_kredit_lr_tahun_berjalan;
							$all_update_ns[$lr_tahun_berjalan]['saldo'] = $new_saldo_akhir_lr_tahun_berjalan;
							$all_update_ns[$lr_tahun_berjalan]['updatedby'] = $session_user;
							$all_update_ns[$lr_tahun_berjalan]['updated'] = $created_date;
							
						}else{
							
							$all_new_ns[$lr_tahun_berjalan] = $all_ns[$lr_tahun_berjalan];
							
							unset($all_new_ns[$lr_tahun_berjalan]['id']);
							$all_new_ns[$lr_tahun_berjalan]['periode'] = $bulan;
							$all_new_ns[$lr_tahun_berjalan]['ket_periode'] = get_month($bulan);
							$all_new_ns[$lr_tahun_berjalan]['tahun'] = $tahun;
							$all_new_ns[$lr_tahun_berjalan]['saldo_awal'] = $new_saldo_awal_lr_tahun_berjalan;
							$all_new_ns[$lr_tahun_berjalan]['mutasi_debet'] = $new_mutasi_debet_lr_tahun_berjalan;
							$all_new_ns[$lr_tahun_berjalan]['mutasi_kredit'] = $new_mutasi_kredit_lr_tahun_berjalan;
							$all_new_ns[$lr_tahun_berjalan]['saldo'] = $new_saldo_akhir_lr_tahun_berjalan;
							$all_new_ns[$lr_tahun_berjalan]['createdby'] = $session_user;
							$all_new_ns[$lr_tahun_berjalan]['created'] = $created_date;
							$all_new_ns[$lr_tahun_berjalan]['updatedby'] = $session_user;
							$all_new_ns[$lr_tahun_berjalan]['updated'] = $created_date;
							
						}
						
					}else{
						
						//UPDATE TAHUN BERJALAN
						if(!empty($all_ns[$lr_tahun_berjalan])){
							
							$new_saldo_awal = $all_ns[$lr_tahun_berjalan]['saldo'];
							$new_mutasi_debet = $all_ns[$lr_tahun_berjalan]['mutasi_debet'];
							$new_mutasi_kredit = $all_ns[$lr_tahun_berjalan]['mutasi_kredit'];
							
							/*
							$total_laba_bersih_LR = $total_laba_bersih;
							if($total_laba_bersih_LR < 0){
								//K
								$total_laba_bersih_LR = $total_laba_bersih_LR*-1;
								$new_mutasi_kredit += $total_laba_bersih_LR;
							}else{
								$new_mutasi_debet += $total_laba_bersih_LR;
							}*/
							
							//AMBIL DR BULAN BERJALAN
							if(!empty($all_ns[$lr_bulan_berjalan])){
								$new_mutasi_debet += $all_ns[$lr_bulan_berjalan]['mutasi_debet'];
								$new_mutasi_kredit += $all_ns[$lr_bulan_berjalan]['mutasi_kredit'];
							}
							
							$new_saldo_akhir = $new_saldo_awal + ($new_mutasi_debet - $new_mutasi_kredit);
							
							if(!empty($all_update_ns[$lr_tahun_berjalan])){
									
								$all_update_ns[$lr_tahun_berjalan]['saldo_awal'] = $new_saldo_awal;
								$all_update_ns[$lr_tahun_berjalan]['mutasi_debet'] = $new_mutasi_debet;
								$all_update_ns[$lr_tahun_berjalan]['mutasi_kredit'] = $new_mutasi_kredit;
								$all_update_ns[$lr_tahun_berjalan]['saldo'] = $new_saldo_akhir;
								$all_update_ns[$lr_tahun_berjalan]['updatedby'] = $session_user;
								$all_update_ns[$lr_tahun_berjalan]['updated'] = $created_date;
								
							}else{
								
								$all_new_ns[$lr_tahun_berjalan] = $all_ns[$lr_tahun_berjalan];
								
								unset($all_new_ns[$lr_tahun_berjalan]['id']);
								$all_new_ns[$lr_tahun_berjalan]['periode'] = $bulan;
								$all_new_ns[$lr_tahun_berjalan]['ket_periode'] = get_month($bulan);
								$all_new_ns[$lr_tahun_berjalan]['tahun'] = $tahun;
								
								$all_new_ns[$lr_tahun_berjalan]['saldo_awal'] = $new_saldo_awal;
								$all_new_ns[$lr_tahun_berjalan]['mutasi_debet'] = $new_mutasi_debet;
								$all_new_ns[$lr_tahun_berjalan]['mutasi_kredit'] = $new_mutasi_kredit;
								$all_new_ns[$lr_tahun_berjalan]['saldo'] = $new_saldo_akhir;
								$all_new_ns[$lr_tahun_berjalan]['createdby'] = $session_user;
								$all_new_ns[$lr_tahun_berjalan]['created'] = $created_date;
								$all_new_ns[$lr_tahun_berjalan]['updatedby'] = $session_user;
								$all_new_ns[$lr_tahun_berjalan]['updated'] = $created_date;
								
							}
						}
						
						
					}
					
					
					//UPDATE BULAN BERJALAN
					if(!empty($all_ns[$lr_bulan_berjalan])){
						
						//$new_saldo_awal = $all_ns[$lr_bulan_berjalan]['saldo'];
						$new_saldo_awal = 0;
						$new_mutasi_debet = 0;
						$new_mutasi_kredit = 0;
						
						$total_laba_bersih_LR = $total_laba_bersih;
						if($total_laba_bersih_LR < 0){
							//K
							$total_laba_bersih_LR = $total_laba_bersih_LR*-1;
							$new_mutasi_kredit += $total_laba_bersih_LR;
						}else{
							$new_mutasi_debet += $total_laba_bersih_LR;
						}
						
						$new_saldo_akhir = $new_saldo_awal + ($new_mutasi_debet - $new_mutasi_kredit);
						
						if(!empty($all_update_ns[$lr_bulan_berjalan])){
									
							$all_update_ns[$lr_bulan_berjalan]['saldo_awal'] = $new_saldo_awal;
							$all_update_ns[$lr_bulan_berjalan]['mutasi_debet'] = $new_mutasi_debet;
							$all_update_ns[$lr_bulan_berjalan]['mutasi_kredit'] = $new_mutasi_kredit;
							$all_update_ns[$lr_bulan_berjalan]['saldo'] = $new_saldo_akhir;
							$all_update_ns[$lr_bulan_berjalan]['updatedby'] = $session_user;
							$all_update_ns[$lr_bulan_berjalan]['updated'] = $created_date;
							
						}else{
							
							$all_new_ns[$lr_bulan_berjalan] = $all_ns[$lr_bulan_berjalan];
							
							unset($all_new_ns[$lr_bulan_berjalan]['id']);
							$all_new_ns[$lr_bulan_berjalan]['periode'] = $bulan;
							$all_new_ns[$lr_bulan_berjalan]['ket_periode'] = get_month($bulan);
							$all_new_ns[$lr_bulan_berjalan]['tahun'] = $tahun;
							
							$all_new_ns[$lr_bulan_berjalan]['saldo_awal'] = $new_saldo_awal;
							$all_new_ns[$lr_bulan_berjalan]['mutasi_debet'] = $new_mutasi_debet;
							$all_new_ns[$lr_bulan_berjalan]['mutasi_kredit'] = $new_mutasi_kredit;
							$all_new_ns[$lr_bulan_berjalan]['saldo'] = $new_saldo_akhir;
							$all_new_ns[$lr_bulan_berjalan]['createdby'] = $session_user;
							$all_new_ns[$lr_bulan_berjalan]['created'] = $created_date;
							$all_new_ns[$lr_bulan_berjalan]['updatedby'] = $session_user;
							$all_new_ns[$lr_bulan_berjalan]['updated'] = $created_date;
							
						}
						
					}
					
					
					//echo '<pre>';
					//print_r($all_new_ns);
					//die();
					
					if(!empty($all_new_ns)){
						$this->db->insert_batch($this->table_neraca_saldo, $all_new_ns);
					}
					if(!empty($all_update_ns)){
						$this->db->update_batch($this->table_neraca_saldo, $all_update_ns,"id");
					}
					
					
				}
				
				
			}
			
		}
		
		if($date_from == $date_till){
			$r = array('success' => true, 'info'	=> 'Date Closing '.$date_from.' Been Generated!');
			die(json_encode($r));
		}
		
		$r = array('success' => true, 'info'	=> 'Date Closing Generated From '.$date_from.' ~ '.$date_till.'!');
		die(json_encode($r));
				
	}
	
	
	public function kode_rekening_parent_child_LR($data_post){
		
		//global $all_data;
		$data_default = array(
			'data'		=> array(),
			'parent'	=> 0,
			'coa_level'	=> 0,
			'all_ns'	=> array(),
			'all_ns_before'	=> array(),
			'save_child'	=> 0
		);
		$data_post = array_merge($data_default, $data_post);
		extract($data_post);
		
		if($coa_level > 0){
			if($coa_level > 1){
				$separator = str_repeat(' &nbsp; &nbsp; &nbsp; ', ($coa_level-1));
			}else{
				$separator = ' &nbsp; ';
			}
		}
		
		$curr_coa_level = $coa_level;
		$coa_level++;
		
		if(!empty($data[$parent])){
			$get_all_child = array();
			
			foreach($data[$parent] as $dt_child){
				
				if($curr_coa_level > 0){
					$dt_child['nama_rek_show'] = $separator.$dt_child['nama_rek_show'];
					$dt_child['kode_nama_rek_show'] = $separator.$dt_child['kode_nama_rek_show'];
					$dt_child['kode_rek_level'] = $separator.$dt_child['kode_rek'];
				}else{
					$dt_child['nama_rek_show'] = $dt_child['nama_rek_show'];
					$dt_child['kode_nama_rek_show'] = $dt_child['kode_nama_rek_show'];
					$dt_child['kode_rek_level'] = $dt_child['kode_rek'];
				}
				
				$dt_child['status_akun'] = 'parent';
				
				$dt_child['bulan_sebelumnya'] = 0;
				if(!empty($all_ns_before[$dt_child['id']])){
					$dt_child['bulan_sebelumnya'] = $all_ns_before[$dt_child['id']]['saldo'];
				}
				
				$dt_child['mutasi_debet'] = 0;
				$dt_child['mutasi_kredit'] = 0;
				if(!empty($all_ns[$dt_child['id']])){
					$dt_child['mutasi_debet'] = $all_ns[$dt_child['id']]['mutasi_debet'];
					$dt_child['mutasi_kredit'] = $all_ns[$dt_child['id']]['mutasi_kredit'];
					$dt_child['saldo_akhir'] = $all_ns[$dt_child['id']]['saldo'];
					
				}
				
				//re-check saldo
				$cek_saldo_bulan_ini = ($dt_child['bulan_sebelumnya']+$dt_child['mutasi_debet']) - $dt_child['mutasi_kredit'];
				if($cek_saldo_bulan_ini != $dt_child['saldo_akhir']){
					$dt_child['saldo_akhir'] = $cek_saldo_bulan_ini;
				}
				
					
				if($dt_child['posisi_akun'] == 'K'){
					$dt_child['mutasi_debet'] = $dt_child['mutasi_debet']*-1;
					$dt_child['mutasi_kredit'] = $dt_child['mutasi_kredit']*-1;
					$dt_child['saldo_akhir'] = $dt_child['saldo_akhir']*-1;
				}
				
				if(!empty($dt_child['id'])){
				
					$check_parent_id = $dt_child['id'];
					
					$data_default = array(
						'data'		=> $data,
						'parent'	=> $check_parent_id,
						'coa_level'	=> $coa_level,
						'all_ns'	=> $all_ns,
						'all_ns_before'	=> $all_ns_before,
						'save_child'	=> $save_child
					);
					
					$get_child = $this->kode_rekening_parent_child_LR($data_default);
				}
				
				if(empty($get_child)){
					$dt_child['status_akun'] = 'detail';
				}else{
					$bulan_sebelumnya = 0;
					$mutasi_debet = 0;
					$mutasi_kredit = 0;
					$saldo_akhir = 0;
					if(!empty($get_child)){
						foreach($get_child as $dt_get){
							
							if($dt_get['status_akun'] == 'detail'){
								$bulan_sebelumnya += $dt_get['bulan_sebelumnya'];
								$mutasi_debet += $dt_get['mutasi_debet'];
								$mutasi_kredit += $dt_get['mutasi_kredit'];
								$saldo_akhir += $dt_get['saldo_akhir'];
							}
							
						}						
					}
					
					$dt_child['bulan_sebelumnya_calc'] = $bulan_sebelumnya;
					$dt_child['mutasi_debet_calc'] = $mutasi_debet;
					$dt_child['mutasi_kredit_calc'] = $mutasi_kredit;
					$dt_child['saldo_akhir_calc'] = $saldo_akhir;
					
					
					/*if($dt_child['posisi_akun'] == 'K'){
						$dt_child['bulan_sebelumnya_calc'] = $dt_child['bulan_sebelumnya_calc']*-1;
						$dt_child['mutasi_debet_calc'] = $dt_child['mutasi_debet_calc']*-1;
						$dt_child['mutasi_kredit_calc'] = $dt_child['mutasi_kredit_calc']*-1;
						$dt_child['saldo_akhir_calc'] = $dt_child['saldo_akhir_calc']*-1;
					}*/
					
				}
				
				$get_all_child[] = $dt_child;
				
				if($save_child){
					if(!empty($get_child)){
				
						foreach($get_child as $dt_get){
								
							$get_all_child[] = $dt_get;						
								
						}
					
					}
				}		
				
				
			}
			
			return $get_all_child;
			
		}else{
			//child
			return '';		
		}	
	}
	
	public function kode_rekening_parent_child($data_post){
		
		//global $all_data;
		$data_default = array(
			'data'		=> array(),
			'parent'	=> 0,
			'coa_level'	=> 0
		);
		$data_post = array_merge($data_default, $data_post);
		extract($data_post);
		
		if($coa_level > 0){
			if($coa_level > 1){
				$separator = str_repeat(' &nbsp; &nbsp; &nbsp; ', ($coa_level-1));
			}else{
				$separator = ' &nbsp; ';
			}
		}
		
		$curr_coa_level = $coa_level;
		$coa_level++;
		
		if(!empty($data[$parent])){
			$get_all_child = array();
			
			foreach($data[$parent] as $dt_child){
				
				if($curr_coa_level > 0){
					$dt_child['kode_nama_rek_show'] = $separator.$dt_child['kode_nama_rek_show'];
					$dt_child['kode_rek_level'] = $separator.$dt_child['kode_rek'];
				}else{
					$dt_child['kode_nama_rek_show'] = $dt_child['kode_nama_rek_show'];
					$dt_child['kode_rek_level'] = $dt_child['kode_rek'];
				}
				
				$dt_child['status_akun'] = 'parent';
				
				if(!empty($dt_child['rek_id'])){
				
					$check_parent_id = $dt_child['rek_id'];
					
					$data_default = array(
						'data'		=> $data,
						'parent'	=> $check_parent_id,
						'coa_level'	=> $coa_level
					);
					
					$get_child = $this->kode_rekening_parent_child($data_default);
				}
				
				if(empty($get_child)){
					$dt_child['status_akun'] = 'detail';
				}else{
					
				}
				
				$get_all_child[] = $dt_child;
					
				if(!empty($get_child)){
				
					foreach($get_child as $dt_get){
							
						$get_all_child[] = $dt_get;						
							
					}
				
				}
				
			}
			
			return $get_all_child;
			
		}else{
			//child
			return '';		
		}	
	}
	
	
	public function calculation_parent_child($data_post){
		
		//global $all_data;
		$data_default = array(
			'data'		=> array(),
			'parent'	=> 0
		);
		$data_post = array_merge($data_default, $data_post);
		extract($data_post);
		
		if(!empty($data[$parent])){
			$get_all_child = array();
			
			foreach($data[$parent] as $dt_child){
				
				$dt_child['status_akun'] = 'parent';
				
				if(!empty($dt_child['rek_id'])){
				
					$check_parent_id = $dt_child['rek_id'];
					
					$data_default = array(
						'data'		=> $data,
						'parent'	=> $check_parent_id
					);
					
					$get_child = $this->calculation_parent_child($data_default);
				}
				
				if(empty($get_child)){
					$dt_child['status_akun'] = 'detail';
					$dt_child['saldo_awal_calc'] = $dt_child['saldo_awal'];
					$dt_child['mutasi_debet_calc'] = $dt_child['mutasi_debet'];
					$dt_child['mutasi_kredit_calc'] = $dt_child['mutasi_kredit'];
					$dt_child['saldo_calc'] = $dt_child['saldo'];
				}else{
					$saldo_awal = 0;
					$mutasi_debet = 0;
					$mutasi_kredit = 0;
					$saldo = 0;
					if(!empty($get_child)){
						
						foreach($get_child as $dt_get){
							
							if($dt_get['status_akun'] == 'detail'){
								$saldo_awal += $dt_get['saldo_awal'];
								$mutasi_debet += $dt_get['mutasi_debet'];
								$mutasi_kredit += $dt_get['mutasi_kredit'];
								$saldo += $dt_get['saldo'];
								//echo $dt_get['nama_rek'].", A:".$dt_get['saldo_awal']." + D:".$dt_get['mutasi_debet']." - K:".$dt_get['mutasi_kredit']." => ".$dt_get['saldo']."<br/>";
							}
							
							
						}	

						//echo "CHILD -> <b>".$dt_child['nama_rek'].' --> A:'.$saldo_awal.', D:'.$mutasi_debet.', K:'.$mutasi_kredit.', S:'.$saldo.'</b> <br/><br/>';
					}
					
					$dt_child['saldo_awal_calc'] = $saldo_awal;
					$dt_child['mutasi_debet_calc'] = $mutasi_debet;
					$dt_child['mutasi_kredit_calc'] = $mutasi_kredit;
					$dt_child['saldo_calc'] = $saldo;
				}
				
				$get_all_child[] = $dt_child;
					
				if(!empty($get_child)){
				
					foreach($get_child as $dt_get){
							
						$get_all_child[] = $dt_get;						
							
					}
				
				}
				
			}
			
			return $get_all_child;
			
		}else{
			//child
			return '';		
		}	
	}
	
	
	public function closingDate()
	{
		$this->table_closing = $this->prefix.'closing';
		$this->table_jurnal_header = $this->prefix_acc.'jurnal_header';
		$this->table_closing_accounting = $this->prefix.'closing_accounting';
		
		$session_client_id = $this->session->userdata('client_id');	
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			die(json_encode($r));
		}
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		$from_autoclosing = $this->input->post('from_autoclosing');
		$is_check = $this->input->post('is_check');
		$current_total = $this->input->post('current_total');
		$closing_date = $this->input->post('closing_date');
		$closing_date = json_decode($closing_date);
		$tanggal = array();
		if(is_array($closing_date)){
			$tanggal = $closing_date;
		}else{
			$tanggal[] = $closing_date;
		}
		
		if(!empty($tanggal)){
			$tanggal_new = $tanggal;
			$tanggal = array();
			foreach($tanggal_new as $tgl){
				$tanggal[] = date("Y-m-d", strtotime($tgl));
			}
		}
		
		$tanggal_txt = implode("','", $tanggal);
		
		$updated_closing_date = array();
		//cek is been generated
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'accounting'");
		$this->db->where("a.tanggal IN ('".$tanggal_txt."')");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result() as $dtC){
				if($dtC->generate_status == 0){
					$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
					$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' Should Generated First!');
					die(json_encode($r));
				}else{
					if(!in_array($dtC->tanggal, $updated_closing_date)){
						$updated_closing_date[] = $dtC->tanggal;
					}
				}
			}
			
		}
		
		//SORTING DATE -> LOW - HIGH
		$dt_tanggal = array();
		foreach($tanggal as $dt){
			$dt_tanggal[strtotime($dt)] = $dt;
		}
		
		ksort($dt_tanggal);
		
		$total_date = count($dt_tanggal);
		if($total_date > 31){
			$r = array('success' => false, 'info'	=> 'Max Closing is 31 Days!');
			die(json_encode($r));
		}
		
		//CEK FIRST TO START DATE
		$opt_value = array(
			'closing_accounting_start_date'
		);
		$get_opt = get_option_value($opt_value);
		
		
		if(!empty($get_opt['closing_accounting_start_date'])){
			$closing_accounting_start_date = $get_opt['closing_accounting_start_date'];
			//$closing_accounting_start_date = date("Y-m-d", strtotime($closing_accounting_start_date));
		}
		
		if(empty($closing_accounting_start_date)){
			$closing_accounting_start_date = date("Y-m-d");
		}
		
		$closing_month = array();
		$today_date = date("Y-m-d");
		$date_current_total = '';
		$date_from = '';
		$date_till = '';
		$i = 0;
		foreach($dt_tanggal as $dtT){
			
			$i++;
			
			if($i == 1){
				$date_from = $dtT;
			}
			
			if(count($dt_tanggal) == $i){
				$date_till = $dtT;
			}
			
			if($i == $current_total){
				$date_current_total = $dtT;
			}
			
			//update data jika akhir bulan
			if(date("Y-m-d",strtotime($dtT)) == date("Y-m-t",strtotime($dtT))){
				//LAST DAY ON MONTH --> CLOSING/MONTH
				$closing_month[] = date("t-m-Y",strtotime($dtT));
			}
			
		}
		
		if(strtotime($date_till) > strtotime($today_date)){
			$r = array('success' => false, 'info'	=> 'Max Closing Date is Today: '.date("d-m-Y").'!');
			die(json_encode($r));
		}
		
		
		//cek is active and hold
		$is_available_active_jurnal_id = array();
		$is_available_active_jurnal = array();
		$this->db->select("a.*");
		$this->db->from($this->table_jurnal_header.' as a');
		$this->db->where("a.status IN ('jurnal')");
		$this->db->where("a.tgl_registrasi >= '".$date_from."' AND a.tgl_registrasi <= '".$date_till."'");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result() as $dtB){
				$tanggal_active_hold = date("d-m-Y", strtotime($dtB->tgl_registrasi));
				if(!in_array($tanggal_active_hold, $is_available_active_jurnal)){
					$is_available_active_jurnal[] = $tanggal_active_hold;
				}
				
				if(!in_array($dtB->id, $is_available_active_jurnal_id)){
					$is_available_active_jurnal_id[] = $dtB->id;
				}
			}
			
		}
		
		//Auto Cancel From Auto Closing
		$autoclosing_skip_open_jurnal = 0;
		if(!empty($get_opt['autoclosing_skip_open_jurnal'])){
			$autoclosing_skip_open_jurnal = $get_opt['autoclosing_skip_open_jurnal'];
		}
		
		if(!empty($from_autoclosing)){
			if(!empty($autoclosing_skip_open_jurnal)){
				
				$is_available_active_jurnal = array();
				
			}
		}
		
		if(!empty($is_available_active_jurnal)){
			$is_available_active_jurnal_txt = implode(", ", $is_available_active_jurnal);
			$r = array('success' => false, 'info'	=> 'Please Set Jurnal to Cancel if There are no Posting!<br/>Check on Date: '.$is_available_active_jurnal_txt);
			die(json_encode($r));
		}
		
		$closing_bulanan = '';
		if(!empty($closing_month)){
			foreach($closing_month as $dtM){
				$closing_bulanan .= get_month(date("m", strtotime($dtM))).' '.date("Y", strtotime($dtM)).'<br/>';
			}
		}
		
		if(!empty($is_check)){
			$r = array('success' => true, 'total_hari'	=> count($dt_tanggal), 'closing_bulanan' => $closing_bulanan);
			die(json_encode($r));
		}
		
		//CURRENT Date
		//$date_from = $date_current_total;
		//$date_till = $date_current_total;
		
		
		$allowed_closing = false;
		
		$date_from_minus_1 = strtotime($date_from) - ONE_DAY_UNIX;
		$date_from_minus = date("Y-m-d", $date_from_minus_1);
		//check on db < $date_from
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'accounting'");
		$this->db->where("a.tanggal = '".$date_from_minus."'");
		$this->db->order_by("a.tanggal", "DESC");
		$this->db->limit(1);
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			
			$dtC = $dt_closing->row();
			$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
			//echo $dtC->tanggal.' == '.$closing_accounting_start_date.'<br/>';
			
			if($dtC->closing_status == 1){
				$allowed_closing = true;
			}else{
				if(strtotime($dtC->tanggal) < strtotime($closing_accounting_start_date)){
					//max closing is < closing_accounting_start_date
					$allowed_closing = true;
				}else{
					$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' or date < '.date("d-m-Y", strtotime($date_from)).' Still Not Closed!');
					die(json_encode($r));
				}
				
			}
			
			
			
		}else{
			
			//echo "$date_from == $closing_accounting_start_date";die();
			if($date_from <= $closing_accounting_start_date){
				//allowed
				$allowed_closing = true;
				//echo 'allowed_generate = closing_accounting_start_date<br/>';
			}else{
				
				$r = array('success' => false, 'info'	=> 'Please Generate Data First, Start Date Closing From '.$closing_accounting_start_date.'!');
				die(json_encode($r));
			}
			
			//$allowed_closing = true;
			//echo 'allowed_closing -> ON DB > '.$date_from.'br/>';
		}
		
		//get ID
		//check on db < $date_from
		$data_closing = array();
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'accounting'");
		$this->db->where("a.tanggal >= '".$date_from."' AND a.tanggal <= '".$date_till."' ");
		$this->db->order_by("a.tanggal", "DESC");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result_array() as $dt){
				$data_closing[$dt['tanggal']] = $dt['id'];
			}
		}
		
		$updated_closing = array();
		foreach($dt_tanggal as $dtT){
			
			if(!empty($data_closing[$dtT])){
				$updated_closing[] = array(
					'id' => $data_closing[$dtT],
					'closing_status' => 1,
					//'tanggal'	=> $dtT
				);
			}
			
			
		}
		
		//echo '$date_current_total = '.$date_current_total.'<pre>';
		//print_r($dt_tanggal);
		//die();
		
		if(!empty($updated_closing)){
			$this->db->update_batch($this->table_closing, $updated_closing, 'id');
		}
		
		//$closing_bulanan
		$data_options = array();
		if(!empty($closing_month)){
			
			//RESET SETUP ACCOUNING
			$data_options = array(
				'bln_aktif_sebelumnya',
				'thn_aktif_sebelumnya',
				'bln_aktif_saat_ini',
				'thn_aktif_saat_ini',
				'bln_aktif_akan_datang',
				'thn_aktif_akan_datang',
				'bulan_berjalan',
				'tahun_berjalan',
				'tutup_bulan_lap',
				'bln_periode_saldo_awal',
				'thn_periode_saldo_awal',
				'bln_aktif_sebelumnya',
				'thn_aktif_sebelumnya'
			);
			
			$get_opt = get_option_value($data_options);
			
			$bln_aktif_sebelumnya = $get_opt['bln_aktif_saat_ini'];	
			$thn_aktif_sebelumnya = $get_opt['thn_aktif_saat_ini'];
			$bln_aktif_saat_ini = $get_opt['bln_aktif_akan_datang'];
			$thn_aktif_saat_ini = $get_opt['thn_aktif_akan_datang'];
			
			$bln_aktif_akan_datang = (int)$get_opt['bln_aktif_akan_datang'];
			$thn_aktif_akan_datang = $get_opt['thn_aktif_akan_datang'];	
			
			$bulan_berjalan = (int)$get_opt['bulan_berjalan'];
			$tahun_berjalan = $get_opt['tahun_berjalan'];	
			
			$bln_periode_saldo_awal = $get_opt['bln_periode_saldo_awal'];
			$thn_periode_saldo_awal = $get_opt['thn_periode_saldo_awal'];	
			$bln_aktif_sebelumnya = $get_opt['bln_aktif_sebelumnya'];
			$thn_aktif_sebelumnya = $get_opt['thn_aktif_sebelumnya'];	
			
			
			//CHECK acc_closing_periode
			$update_closing_periode = array();
			$insert_closing_periode = array();
			
			$last_closing_periode = 0;
			$last_closing_tahun = 0;
			foreach($closing_month as $dtM){
				
				$dtM_periode = date("m", strtotime($dtM));
				$dtM_tahun = date("Y", strtotime($dtM));
				$dtM_tanggal = date("Y-m-t", strtotime($dtM));
				
				if(empty($last_closing_periode)){
					$last_closing_periode = $dtM_periode;
					$last_closing_tahun = $dtM_tahun;
				}else{
					
					if((int)$last_closing_periode <= (int)$dtM_periode){
						$last_closing_periode = $dtM_periode;
					}
					if((int)$last_closing_tahun <= (int)$dtM_tahun){
						$last_closing_tahun = $dtM_tahun;
					}
					
				}
				
				if($bln_periode_saldo_awal.' '.$thn_periode_saldo_awal == $dtM_periode.' '.$dtM_tahun){
					
				}else{
					
					$this->db->from($this->prefix_acc."closing_periode");
					$this->db->where("periode", $dtM_periode);
					$this->db->where("tahun", $dtM_tahun);
					//$this->db->where("status", 'close');
					$get_dt_closing = $this->db->get();
					if($get_dt_closing->num_rows() > 0){
						$dataM = $get_dt_closing->row_array();
						
						$dataM['status'] = 'close';
						//$dataM['tanggal'] = $dtM_tanggal;
						$dataM['updated'] = date('Y-m-d H:i:s');
						$dataM['updatedby'] = $session_user;
						$update_closing_periode[$dataM['id']] =  $dataM;
						
					}else{
						
						$insert_closing_periode[] = array(
							'periode' 		=> $dtM_periode,
							'ket_periode' 	=> get_month($dtM_periode),
							'tahun' 		=> $dtM_tahun,
							'tanggal' 		=> $dtM_tanggal,
							'status' 		=> 'close',
							'created'		=>	date('Y-m-d H:i:s'),
							'createdby'		=>	$session_user,
							'updated'		=>	date('Y-m-d H:i:s'),
							'updatedby'		=>	$session_user
						);
						
					}
				}
				
			}
			
			
			//SURE LATEST CLOSING
			$bln_aktif_sebelumnya = $last_closing_periode;
			$thn_aktif_sebelumnya = $last_closing_tahun;
			
			$bln_aktif_saat_ini = (int) $last_closing_periode + 1;
			if($bln_aktif_saat_ini > 12){
				$bln_aktif_saat_ini = 1;
				$thn_aktif_saat_ini = (int) $last_closing_tahun + 1;
			}
			if(strlen($bln_aktif_saat_ini) == 1){
				$bln_aktif_saat_ini = '0'.$bln_aktif_saat_ini;
			}
			
			
			$bln_aktif_akan_datang = $bln_aktif_saat_ini + 1;
			if($bln_aktif_akan_datang > 12){
				$bln_aktif_akan_datang = 1;
				$thn_aktif_akan_datang = $thn_aktif_saat_ini+1;
			}
			
			if(strlen($bln_aktif_akan_datang) == 1){
				$bln_aktif_akan_datang = '0'.$bln_aktif_akan_datang;
			}
			
			
			$bulan_berjalan = $bln_aktif_saat_ini;
			$tahun_berjalan = $thn_aktif_saat_ini;
			
			$data_options = array(
				'bln_aktif_sebelumnya' => $bln_aktif_sebelumnya,
				'thn_aktif_sebelumnya' => $thn_aktif_sebelumnya,
				'bln_aktif_saat_ini' => $bln_aktif_saat_ini,
				'thn_aktif_saat_ini' => $thn_aktif_saat_ini,
				'bln_aktif_akan_datang' => $bln_aktif_akan_datang,
				'thn_aktif_akan_datang' => $thn_aktif_akan_datang,
				'bulan_berjalan' => $bulan_berjalan,
				'tahun_berjalan' => $tahun_berjalan,
				'tutup_bulan_lap' => 1
			);
			
			//UPDATE OPTIONS
			$update_option = update_option($data_options);
			
			if(!empty($insert_closing_periode)){
				$this->db->insert_batch($this->prefix_acc."closing_periode", $insert_closing_periode);
			}
			
			if(!empty($update_closing_periode)){
				$this->db->update_batch($this->prefix_acc."closing_periode", $update_closing_periode, "id");
			}
			
			
		}
		
		if(count($updated_closing) == 1){
			$r = array('success' => true, 'info'	=> 'Date '.$date_from.' Been Closed!', 'closing_bulanan' => '', 'data_options' => $data_options);
			die(json_encode($r));
		}
		
		$r = array('success' => true, 'info'	=> 'Date From '.$date_from.' ~ '.$date_till.' Been Closed!', 'closing_bulanan' => '', 'data_options' => $data_options);
		die(json_encode($r));
		
	}
	
	public function openDate()
	{
		$this->table_closing = $this->prefix.'closing';
		$this->table_closing_accounting = $this->prefix.'closing_accounting';
		$session_client_id = $this->session->userdata('client_id');	
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			die(json_encode($r));
		}
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		$is_check = $this->input->post('is_check');
		$closing_date = $this->input->post('closing_date');
		$closing_date = json_decode($closing_date);
		$tanggal = array();
		if(is_array($closing_date)){
			$tanggal = $closing_date;
		}else{
			$tanggal[] = $closing_date;
		}
		
		if(!empty($tanggal)){
			$tanggal_new = $tanggal;
			$tanggal = array();
			foreach($tanggal_new as $tgl){
				$tanggal[] = date("Y-m-d", strtotime($tgl));
			}
		}
		
		$tanggal_txt = implode("','", $tanggal);
		
		$updated_closing_date = array();
		//cek is been generated
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'accounting'");
		$this->db->where("a.tanggal IN ('".$tanggal_txt."')");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result() as $dtC){
				if($dtC->closing_status == 0){
					$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
					$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' Status Not Closed!');
					die(json_encode($r));
				}else{
					if(!in_array($dtC->tanggal, $updated_closing_date)){
						$updated_closing_date[] = $dtC->tanggal;
					}
				}
			}
			
		}
		
		//SORTING DATE -> LOW - HIGH
		$dt_tanggal = array();
		foreach($tanggal as $dt){
			$dt_tanggal[strtotime($dt)] = $dt;
		}
		
		krsort($dt_tanggal);
		
		$total_date = count($dt_tanggal);
		if($total_date > 31){
			$r = array('success' => false, 'info'	=> 'Max Open Date is 31 Days!');
			die(json_encode($r));
		}
		
		//CEK FIRST TO START DATE
		$opt_value = array(
			'closing_accounting_start_date'
		);
		$get_opt = get_option_value($opt_value);
		
		
		if(!empty($get_opt['closing_accounting_start_date'])){
			$closing_accounting_start_date = $get_opt['closing_accounting_start_date'];
			$closing_accounting_start_date = date("Y-m-d", strtotime($closing_accounting_start_date));
		}
		
		if(empty($closing_accounting_start_date)){
			$closing_accounting_start_date = date("Y-m-d");
		}
		
		$today_date = date("Y-m-d");
		$date_from = '';
		$date_till = '';
		$i = 0;
		foreach($dt_tanggal as $dtT){
			$i++;
			
			if($i == 1){
				$date_from = $dtT;
			}
			
			if(count($dt_tanggal) == $i){
				$date_till = $dtT;
			}
			
			//update data jika akhir bulan
			if(date("Y-m-d",strtotime($dtT)) == date("Y-m-t",strtotime($dtT))){
				//LAST DAY ON MONTH --> CLOSING/MONTH
				$closing_month[] = date("t-m-Y",strtotime($dtT));
			}
		}
		
		if(strtotime($date_from) > strtotime($today_date)){
			$r = array('success' => false, 'info'	=> 'Max Open Date is Today: '.date("d-m-Y").'!');
			die(json_encode($r));
		}
		
		
		$closing_bulanan = '';
		if(!empty($closing_month)){
			foreach($closing_month as $dtM){
				$closing_bulanan .= get_month(date("m", strtotime($dtM))).' '.date("Y", strtotime($dtM)).'<br/>';
			}
		}
		
		if(!empty($is_check)){
			$r = array('success' => true, 'total_hari'	=> count($dt_tanggal), 'closing_bulanan' => $closing_bulanan);
			die(json_encode($r));
		}
		
		$allowed_open = false;
		
		$date_from_plus_1 = strtotime($date_from) + ONE_DAY_UNIX;
		$date_from_plus = date("Y-m-d", $date_from_plus_1);
		//check on db < $date_from
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'accounting'");
		$this->db->where("a.tanggal = '".$date_from_plus."'");
		$this->db->order_by("a.tanggal", "DESC");
		$this->db->limit(1);
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			
			$dtC = $dt_closing->row();
			$date_txt = date("d-m-Y", strtotime($dtC->tanggal));
			//echo $dtC->tanggal.' == '.$closing_accounting_start_date.'<br/>';
			
			if($dtC->closing_status == 0){
				$allowed_open = true;
			}else{
				if(strtotime($dtC->tanggal) < strtotime($closing_accounting_start_date)){
					//max closing is < closing_accounting_start_date
					$allowed_open = true;
				}else{
					$r = array('success' => false, 'info'	=> 'Date '.$date_txt.' or > from date '.date("d-m-Y", strtotime($date_from)).' Still Closed!');
					die(json_encode($r));
				}
				
			}
			
			
		}else{
			
			//echo "$date_from == $closing_accounting_start_date";die();
			if($date_from >= $closing_accounting_start_date){
				//allowed
				$allowed_open = true;
				//echo 'allowed_generate = closing_accounting_start_date<br/>';
			}else{
				
				$r = array('success' => false, 'info'	=> 'Please Generate Data First, Start Date Closing From '.$closing_accounting_start_date.'!');
				die(json_encode($r));
			}
			
			//$allowed_open = true;
			//echo 'allowed_open -> ON DB > '.$date_from.'br/>';
		}
		
		
		
		//get ID
		//check on db < $date_from
		$data_closing = array();
		$this->db->from($this->table_closing.' as a');
		$this->db->where("a.tipe = 'accounting'");
		$this->db->where("a.tanggal >= '".$date_till."' AND a.tanggal <= '".$date_from."' ");
		$this->db->order_by("a.tanggal", "DESC");
		$dt_closing = $this->db->get();
		if($dt_closing->num_rows() > 0){
			foreach($dt_closing->result_array() as $dt){
				$data_closing[$dt['tanggal']] = $dt['id'];
			}
		}
		
		$updated_closing = array();
		foreach($dt_tanggal as $dtT){
			
			if(!empty($data_closing[$dtT])){
				$updated_closing[] = array(
					'id' => $data_closing[$dtT],
					'closing_status' => 0,
					//'tanggal'	=> $dtT
				);
			}
			
			
		}
		
		//echo '$allowed_open = '.$allowed_open.'<pre>';
		//print_r($updated_closing);
		//die();
		
		if(!empty($updated_closing)){
			$this->db->update_batch($this->table_closing, $updated_closing, 'id');
		}
		
		//$closing_bulanan
		$data_options = array();
		if(!empty($closing_month)){
			
			//RESET SETUP ACCOUNING
			$data_options = array(
				'bln_aktif_sebelumnya',
				'thn_aktif_sebelumnya',
				'bln_aktif_saat_ini',
				'thn_aktif_saat_ini',
				'bln_aktif_akan_datang',
				'thn_aktif_akan_datang',
				'bulan_berjalan',
				'tahun_berjalan',
				'tutup_bulan_lap',
				'bln_periode_saldo_awal',
				'thn_periode_saldo_awal',
				'bln_aktif_sebelumnya',
				'thn_aktif_sebelumnya'
			);
			
			$get_opt = get_option_value($data_options);
			
			$bln_aktif_sebelumnya = $get_opt['bln_aktif_saat_ini'];	
			$thn_aktif_sebelumnya = $get_opt['thn_aktif_saat_ini'];
			$bln_aktif_saat_ini = $get_opt['bln_aktif_akan_datang'];
			$thn_aktif_saat_ini = $get_opt['thn_aktif_akan_datang'];
			
			$bln_aktif_akan_datang = (int)$get_opt['bln_aktif_akan_datang'];
			$thn_aktif_akan_datang = $get_opt['thn_aktif_akan_datang'];	
			
			$bulan_berjalan = (int)$get_opt['bulan_berjalan'];
			$tahun_berjalan = $get_opt['tahun_berjalan'];	
			
			$bln_periode_saldo_awal = $get_opt['bln_periode_saldo_awal'];
			$thn_periode_saldo_awal = $get_opt['thn_periode_saldo_awal'];	
			$bln_aktif_sebelumnya = $get_opt['bln_aktif_sebelumnya'];
			$thn_aktif_sebelumnya = $get_opt['thn_aktif_sebelumnya'];	
			
			
			//CHECK acc_closing_periode
			$update_closing_periode = array();
			$insert_closing_periode = array();
			
			$last_closing_periode = 0;
			$last_closing_tahun = 0;
			foreach($closing_month as $dtM){
				
				$dtM_periode = date("m", strtotime($dtM));
				$dtM_tahun = date("Y", strtotime($dtM));
				$dtM_tanggal = date("Y-m-t", strtotime($dtM));
				
				if(empty($last_closing_periode)){
					$last_closing_periode = $dtM_periode;
					$last_closing_tahun = $dtM_tahun;
				}else{
					
					//Re-OPEN >= date
					if((int)$last_closing_periode >= (int)$dtM_periode){
						$last_closing_periode = $dtM_periode;
					}
					if((int)$last_closing_tahun >= (int)$dtM_tahun){
						$last_closing_tahun = $dtM_tahun;
					}
					
				}
				
				
				if($bln_periode_saldo_awal.' '.$thn_periode_saldo_awal == $dtM_periode.' '.$dtM_tahun){
					
				}else{
					$this->db->from($this->prefix_acc."closing_periode");
					$this->db->where("periode", $dtM_periode);
					$this->db->where("tahun", $dtM_tahun);
					//$this->db->where("status", 'close');
					$get_dt_closing = $this->db->get();
					if($get_dt_closing->num_rows() > 0){
						$dataM = $get_dt_closing->row_array();
						
						$dataM['status'] = 'open';
						//$dataM['tanggal'] = $dtM_tanggal;
						$dataM['updated'] = date('Y-m-d H:i:s');
						$dataM['updatedby'] = $session_user;
						$update_closing_periode[$dataM['id']] =  $dataM;
						
					}else{
						
						$insert_closing_periode[] = array(
							'periode' 		=> $dtM_periode,
							'ket_periode' 	=> get_month($dtM_periode),
							'tahun' 		=> $dtM_tahun,
							'tanggal' 		=> $dtM_tanggal,
							'status' 		=> 'open',
							'created'		=>	date('Y-m-d H:i:s'),
							'createdby'		=>	$session_user,
							'updated'		=>	date('Y-m-d H:i:s'),
							'updatedby'		=>	$session_user
						);
						
					}
				}
				
				
			}
			
			//SURE LATEST CLOSING
			$bln_aktif_sebelumnya = (int) $last_closing_periode - 1;
			if($bln_aktif_sebelumnya <= 0){
				$bln_aktif_sebelumnya = 12;
				$thn_aktif_sebelumnya = (int) $last_closing_tahun - 1;
			}
			
			if(strlen($bln_aktif_sebelumnya) == 1){
				$bln_aktif_sebelumnya = '0'.$bln_aktif_sebelumnya;
			}
			
			$bln_aktif_saat_ini = (int) $last_closing_periode;
			$thn_aktif_saat_ini = $last_closing_tahun;
			
			if(strlen($bln_aktif_saat_ini) == 1){
				$bln_aktif_saat_ini = '0'.$bln_aktif_saat_ini;
			}
			
			$bln_aktif_akan_datang = $bln_aktif_saat_ini + 1;
			if($bln_aktif_akan_datang > 12){
				$bln_aktif_akan_datang = 1;
				$thn_aktif_akan_datang = $thn_aktif_saat_ini+1;
			}
			
			if(strlen($bln_aktif_akan_datang) == 1){
				$bln_aktif_akan_datang = '0'.$bln_aktif_akan_datang;
			}
			
			
			$bulan_berjalan = $bln_aktif_saat_ini;
			$tahun_berjalan = $thn_aktif_saat_ini;
			
			$data_options = array(
				'bln_aktif_sebelumnya' => $bln_aktif_sebelumnya,
				'thn_aktif_sebelumnya' => $thn_aktif_sebelumnya,
				'bln_aktif_saat_ini' => $bln_aktif_saat_ini,
				'thn_aktif_saat_ini' => $thn_aktif_saat_ini,
				'bln_aktif_akan_datang' => $bln_aktif_akan_datang,
				'thn_aktif_akan_datang' => $thn_aktif_akan_datang,
				'bulan_berjalan' => $bulan_berjalan,
				'tahun_berjalan' => $tahun_berjalan,
				'tutup_bulan_lap' => 1
			);
			
			//UPDATE OPTIONS
			$update_option = update_option($data_options);
			
			if(!empty($insert_closing_periode)){
				$this->db->insert_batch($this->prefix_acc."closing_periode", $insert_closing_periode);
			}
			
			if(!empty($update_closing_periode)){
				$this->db->update_batch($this->prefix_acc."closing_periode", $update_closing_periode, "id");
			}
			
			
		}
		
		
		
		if(count($updated_closing) == 1){
			$r = array('success' => true, 'info'	=> 'Date '.$date_from.' Been Opened!', 'data_options' => $data_options);
			die(json_encode($r));
		}
		
		$r = array('success' => true, 'info'	=> 'Date From '.$date_from.' ~ '.$date_till.' Been Opened!', 'data_options' => $data_options);
		die(json_encode($r));
		
	}
}