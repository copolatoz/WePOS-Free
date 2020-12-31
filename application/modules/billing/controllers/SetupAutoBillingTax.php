<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class SetupAutoBillingTax extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->prefix_acc = config_item('db_prefix3');
		$this->load->model('model_setupautobillingtax', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'nontrx_target';
		$this->table_periode_laporan = $this->prefix_acc.'periode_laporan';
		
		$sortAlias = array(
			'nontrx_range_sales_from_show' => 'a.nontrx_range_sales_from',
			'nontrx_range_sales_from_show' => 'a.nontrx_range_sales_till',
			'nontrx_bulan_target_show' => 'a.nontrx_bulan_target',
			'nontrx_minggu_target_show' => 'a.nontrx_minggu_target',
			'nontrx_hari_target_show' => 'a.nontrx_hari_target',
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, b.nama_bulan_kalender as nontrx_bulan_text',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->table_periode_laporan.' as b','b.id = a.nontrx_bulan','LEFT'),
									)
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('a.id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$show_valid_date = $this->input->post('show_valid_date');
		$show_all_text = $this->input->post('show_all_text');
		$keywords = $this->input->post('keywords');
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if(!empty($is_dropdown)){
			
		}
		
		$params['order'] = array('a.is_default' => 'DESC','a.nontrx_tahun' => 'ASC','a.nontrx_bulan' => 'ASC');
		
		if(!empty($searching)){
			$params['where'][] = "(a.nontrx_tahun LIKE '%".$searching."%' OR a.nontrx_bulan LIKE '%".$searching."%' OR b.nama_bulan_kalender LIKE '%".$searching."%')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($is_dropdown)){
			
			$show_txt = '-- NO TARGET --';
			if(!empty($show_all_text)){
				$show_txt = '-- ALL TARGET --';
			}
			
			$s = array(
				'id'	=> 0,
				'nontrx_tahun'	=> 	$show_txt,
				'nontrx_bulan'	=> $show_txt,		
				'nontrx_bulan_text'	=> $show_txt,		
				'nontrx_bulan_tahun_text'	=> $show_txt,		
				'nontrx_range_sales_from'	=> 0,		
				'nontrx_range_sales_till'	=> 0,		
				'nontrx_bulan_target'	=> 0,		
				'nontrx_minggu_target'	=> 0,		
				'nontrx_hari_target'	=> 0	
			);
			array_push($newData, $s);
		}
		
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['is_default_text'] = ($s['is_default'] == '1') ? '<span style="color:green;">Ya</span>':'<span style="color:red;">&nbsp;</span>';
				
				$s['nontrx_bulan_tahun_text'] = $s['nontrx_bulan_text'].' '.$s['nontrx_tahun'];
				$s['nontrx_range_sales_from_show'] = priceFormat($s['nontrx_range_sales_from'],0);
				$s['nontrx_range_sales_till_show'] = priceFormat($s['nontrx_range_sales_till'],0);
				$s['nontrx_bulan_target_show'] = priceFormat($s['nontrx_bulan_target'],0);
				$s['nontrx_minggu_target_show'] = priceFormat($s['nontrx_minggu_target'],0);
				$s['nontrx_hari_target_show'] = priceFormat($s['nontrx_hari_target'],0);
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$r = $this->m->addUpdate();
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function delete()
	{
		$this->table = $this->prefix.'nontrx_target';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
		
		if($id == 1 OR in_array(1,$id)){
			 $r = array('success' => false, 'info' => 'Default tidak dapat dihapus'); 
			 die(json_encode($r));
		}
		
		//Delete
		$data_update = array(
			"is_deleted" => 1
		);
		$q = $this->db->update($this->table, $data_update, "id IN (".$sql_Id.")");
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Hapus Target Gagal!'); 
        }
		die(json_encode($r));
	}
	
	public function print_setupAutoBillingTax(){
		
		$this->table = $this->prefix.'nontrx_target';
		$data_post['table'] = $this->table;
		$do = '';
		
		extract($_GET);

		$this->db->from($this->table);
		$this->db->where("is_deleted = 0");
		$this->db->order_by("nontrx_tahun","ASC");
		$this->db->order_by("nontrx_bulan","ASC");
		$get_nontrx_target = $this->db->get();
		
		$data_nontrx_target = array();
		if($get_nontrx_target->num_rows() > 0){
			$data_nontrx_target = $get_nontrx_target->result();
		}
		
		$data_post['do'] = $do;
		$data_post['data_nontrx_target'] = $data_nontrx_target;
		$data_post['report_name'] = 'DATA TARGET NON-TRX/COMPLIMENT';
		
		if($do == 'excel'){
			$this->load->view('../../billing/views/excel_setupAutoBillingTax', $data_post);
		}else{
			$this->load->view('../../billing/views/print_setupAutoBillingTax', $data_post);
		}
		
		
	}
	
	
}