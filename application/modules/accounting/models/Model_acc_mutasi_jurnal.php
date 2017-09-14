<?php
class Model_acc_mutasi_jurnal extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'jurnal_header';
	}
	
	function load_setup_periode(){
		
		//GET ALL OPTION
		$opt_val = array(
			'bln_aktif_sebelumnya', 
			'thn_aktif_sebelumnya', 
			'bln_aktif_saat_ini', 
			'thn_aktif_saat_ini', 
			'bln_aktif_akan_datang', 
			'thn_aktif_akan_datang', 
			'bln_periode_saldo_awal', 
			'thn_periode_saldo_awal', 
			'bulan_berjalan', 
			'tahun_berjalan', 
			'tutup_bulan_lap', 
			'tutup_periode_saldo_awal', 
			'tutup_bulan_lap_interim', 
			'tutup_bulan_lap_tahunan', 
			'tutup_bulan_lap_kap', 
			'bulan_baru'
		);
		
		$get_opt = get_option_value($opt_val);
		
		$retAll = array('success' => true);
					
		if(!empty($get_opt['bln_aktif_sebelumnya'])){
			$retValue['bln_aktif_sebelumnya']  = $get_opt['bln_aktif_sebelumnya'];
		}			
		if(!empty($get_opt['thn_aktif_sebelumnya'])){
			$retValue['thn_aktif_sebelumnya']  = $get_opt['thn_aktif_sebelumnya'];
		}else{
			$retValue['thn_aktif_sebelumnya'] = date("Y");
		}
		
		if(!empty($get_opt['bln_aktif_saat_ini'])){
			$retValue['bln_aktif_saat_ini']  = $get_opt['bln_aktif_saat_ini'];
		}		
		if(!empty($get_opt['thn_aktif_saat_ini'])){
			$retValue['thn_aktif_saat_ini']  = $get_opt['thn_aktif_saat_ini'];
		}else{
			$retValue['thn_aktif_saat_ini'] = date("Y");
		}
		
		if(!empty($get_opt['bln_aktif_akan_datang'])){
			$retValue['bln_aktif_akan_datang']  = $get_opt['bln_aktif_akan_datang'];
		}
		if(!empty($get_opt['thn_aktif_akan_datang'])){
			$retValue['thn_aktif_akan_datang']  = $get_opt['thn_aktif_akan_datang'];
		}else{
			$retValue['thn_aktif_akan_datang'] = date("Y");
		}
		
		if(!empty($get_opt['bln_periode_saldo_awal'])){
			$retValue['bln_periode_saldo_awal']  = $get_opt['bln_periode_saldo_awal'];
		}	
		if(!empty($get_opt['thn_periode_saldo_awal'])){
			$retValue['thn_periode_saldo_awal']  = $get_opt['thn_periode_saldo_awal'];
		}else{
			$retValue['thn_periode_saldo_awal'] = date("Y");
		}
		
		if(!empty($get_opt['bulan_berjalan'])){
			$retValue['bulan_berjalan']  = $get_opt['bulan_berjalan'];
		}		
		if(!empty($get_opt['tahun_berjalan'])){
			$retValue['tahun_berjalan']  = $get_opt['tahun_berjalan'];
		}else{
			$retValue['tahun_berjalan'] = date("Y");
		}
		
		$retValue['tutup_bulan_lap'] = 0;		
		if(!empty($get_opt['tutup_bulan_lap'])){
			$retValue['tutup_bulan_lap']  = $get_opt['tutup_bulan_lap'];
		}
		
		$retValue['tutup_periode_saldo_awal'] = 0;
		if(!empty($get_opt['tutup_periode_saldo_awal'])){
			$retValue['tutup_periode_saldo_awal']  = $get_opt['tutup_periode_saldo_awal'];
		}
		
		$retValue['tutup_bulan_lap_interim'] = 0;
		if(!empty($get_opt['tutup_bulan_lap_interim'])){
			$retValue['tutup_bulan_lap_interim']  = $get_opt['tutup_bulan_lap_interim'];
		}
		
		$retValue['tutup_bulan_lap_tahunan'] = 0;
		if(!empty($get_opt['tutup_bulan_lap_tahunan'])){
			$retValue['tutup_bulan_lap_tahunan']  = $get_opt['tutup_bulan_lap_tahunan'];
		}
		
		$retValue['tutup_bulan_lap_kap'] = 0;
		if(!empty($get_opt['tutup_bulan_lap_kap'])){
			$retValue['tutup_bulan_lap_kap']  = $get_opt['tutup_bulan_lap_kap'];
		}
		
		$retValue['bulan_baru'] = 0;
		if(!empty($get_opt['bulan_baru'])){
			$retValue['bulan_baru']  = $get_opt['bulan_baru'];
		}
		
		$retAll['data'] = $retValue;
		
		return $retAll;
		
	}
	
	public function generate_no_registrasi($id = 0){
		
		$this->prefix_acc = config_item('db_prefix3');
		$this->table = $this->prefix_acc.'jurnal_header';						
		
		//get thn bulan
		$retAll = $this->load_setup_periode();
		//bln_aktif_saat_ini
		$bln_aktif_saat_ini = $retAll['data']['bln_aktif_saat_ini'];
		//thn_aktif_saat_ini
		$thn_aktif_saat_ini = $retAll['data']['thn_aktif_saat_ini'];
		
		if(empty($bln_aktif_saat_ini) OR empty($thn_aktif_saat_ini)){
			return '';
		}
		
		$use_prefix_no = "J".$thn_aktif_saat_ini.$bln_aktif_saat_ini;
		$this->db->from($this->table);
		//$this->db->where("periode", $bln_aktif_saat_ini);
		//$this->db->where("tahun", $thn_aktif_saat_ini);
		$this->db->where("no_registrasi LIKE '%".$use_prefix_no."%'");
		
		if(!empty($id)){
			$this->db->where("id != ".$id);
		}
		
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_mj = $get_last->row();
			//$no_registrasi = $data_mj->no_registrasi;
			$no_registrasi = str_replace($use_prefix_no,"", $data_mj->no_registrasi);
						
			$no_registrasi = (int) $no_registrasi;			
		}else{
			$no_registrasi = 0;
		}
		
		$no_registrasi++;
		$length_no = strlen($no_registrasi);
		switch ($length_no) {
			/*case 5:
				$no_registrasi = '0'.$no_registrasi;
				break;
			case 4:
				$no_registrasi = '0'.$no_registrasi;
				break;*/
			case 3:
				$no_registrasi = '0'.$no_registrasi;
				break;
			case 2:
				$no_registrasi = '00'.$no_registrasi;
				break;
			case 1:
				$no_registrasi = '000'.$no_registrasi;
				break;
			default:
				$no_registrasi = '000'.$no_registrasi;
				break;
		}
				
		return $use_prefix_no.$no_registrasi;				
	}
	
	public function generate_no_jurnal($singkatan = '', $id = 0){
		
		$this->prefix_acc = config_item('db_prefix3');
		$this->table = $this->prefix_acc.'jurnal_header';						
		
		if(empty($singkatan)){
			return '';
		}
		
		//get thn bulan
		$retAll = $this->load_setup_periode();
		//bln_aktif_saat_ini
		$bln_aktif_saat_ini = $retAll['data']['bln_aktif_saat_ini'];
		//thn_aktif_saat_ini
		$thn_aktif_saat_ini = $retAll['data']['thn_aktif_saat_ini'];
		
		if(empty($bln_aktif_saat_ini) OR empty($thn_aktif_saat_ini)){
			return '';
		}
		
		$use_prefix_no = $singkatan.$thn_aktif_saat_ini.$bln_aktif_saat_ini;
		$this->db->from($this->table);
		$this->db->where("periode", $bln_aktif_saat_ini);
		$this->db->where("tahun", $thn_aktif_saat_ini);
		$this->db->where("no_jurnal LIKE '%".$use_prefix_no."%'");
		
		if(!empty($id)){
			$this->db->where("id != ".$id);
		}
		
		$this->db->order_by('no_jurnal', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_mj = $get_last->row();
			
			//$no_jurnal = $data_mj->no_jurnal;
			$no_jurnal = str_replace($use_prefix_no,"", $data_mj->no_jurnal);
			$no_jurnal = (int) $no_jurnal;			
			
		}else{
			$no_jurnal = 0;
		}
		
		$no_jurnal++;
		$length_no = strlen($no_jurnal);
		switch ($length_no) {
			/*case 5:
				$no_jurnal = '0'.$no_jurnal;
				break;
			case 4:
				$no_jurnal = '00'.$no_jurnal;
				break;*/
			case 3:
				$no_jurnal = '0'.$no_jurnal;
				break;
			case 2:
				$no_jurnal = '00'.$no_jurnal;
				break;
			case 1:
				$no_jurnal = '000'.$no_jurnal;
				break;
			default:
				$no_jurnal = '000'.$no_jurnal;
				break;
		}
		
				
		return $use_prefix_no.$no_jurnal;				
	}
	
} 