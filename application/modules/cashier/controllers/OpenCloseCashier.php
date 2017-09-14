<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class OpenCloseCashier extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_openclosecashier', 'm');
	}
	
	public function gridData()
	{
		$this->table = $this->prefix.'open_close_shift';
		
		//is_active_text
		$sortAlias = array(
			//'is_active_text' => 'a.is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			/*'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'product as b','b.id = a.product_id','LEFT'),
										array($this->prefix.'product_category as c','c.id = b.category_id','LEFT')
									) 
								),*/
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('a.id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$tipe_shift = $this->input->post('tipe_shift');
		
		//FILTER
		$kasir_user = $this->input->post('kasir_user');
		$spv_user = $this->input->post('spv_user');
		$skip_date = $this->input->post('skip_date');
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
				if(empty($date_till)){ $date_till = date('Y-m-d'); }
				
				$mktime_dari = strtotime($date_from);
				$mktime_sampai = strtotime($date_till);
							
				$qdate_from = date("Y-m-d 00:00:00",strtotime($date_from));
				$qdate_till = date("Y-m-d 23:59:59",strtotime($date_till));
				$qdate_till_max = date("Y-m-d 06:00:00",strtotime($qdate_till)+ONE_DAY_UNIX);
				
				$params['where'][] = "(a.tanggal_shift >= '".$qdate_from."' AND a.tanggal_shift <= '".$qdate_till_max."')";
						
			}
		}
		
		if(!empty($report_paid_order)){
			$params['order'] = array('a.id' => $report_paid_order);
		}
		if(!empty($is_dropdown)){
			$params['order'] = array('a.id' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.kasir_user LIKE '%".$searching."%' OR a.spv_user LIKE '%".$searching."%')";
		}
		if(!empty($tipe_shift)){
			$params['where'][] = "(a.tipe_shift = '".$tipe_shift."')";
		}
		if(!empty($user_cashier)){
			$this->db->where('a.kasir_user', $user_cashier);
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		$no = 1;
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['item_no'] = $no;
				$s['tanggal_shift'] = date("d-m-Y",strtotime($s['tanggal_shift']));
				$s['jumlah_uang_kertas_show'] = priceFormat($s['jumlah_uang_kertas']);
				$s['jumlah_uang_koin_show'] = priceFormat($s['jumlah_uang_koin']);
				
				$no++;
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}	
	
	public function printOpenCloseCashier(){
		
		$this->table = $this->prefix.'open_close_shift';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('User Session Expired, Please Re-Login!');
		}
				
		extract($_GET);
		
		if(empty($id)){
			die('Data unidentified!');
		}
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> strtoupper($type).' CASHIER',
			'user_fullname'	=> $user_fullname
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($id)){
			die('Data Not Found!');
		}else{
			
			$openClose_data = array();			
			
			$this->db->from($this->table);
			$this->db->where("id", $id);
			$get_openClose = $this->db->get();
			if($get_openClose->num_rows() > 0){
				$openClose_data = $get_openClose->row_array();	
			}
	
			$data_post['openClose_data'] = $openClose_data;
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}
		
		$this->load->view('../../cashier/views/printOpenCloseCashier', $data_post);	
	}
}