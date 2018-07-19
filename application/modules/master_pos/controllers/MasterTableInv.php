<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MasterTableInv extends MY_Controller {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_tableinv', 'm');		
	}

	public function gridData()
	{
		$this->billing = $this->prefix.'billing';
		$this->floorplan = $this->prefix.'floorplan';
		$this->room = $this->prefix.'room';
		$this->table = $this->prefix.'table';
		$this->table_inventory = $this->prefix.'table_inventory';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		$this->m->cek();
		
		//MEMCACHED SESSION
		$use_memcached = $this->input->post('use_memcached');
		if($use_memcached == 1){
			//reload memcached
		}else{
			//empty memcached
			
		}
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'b.is_active',
			'floorplan_name' => 'c.floorplan_name',
			'room_name' => 'c2.room_name'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.id, a.table_id, a.billing_no, a.tanggal, a.status, b.*, c.floorplan_name, c2.room_name, c2.room_no",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table_inventory.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->table.' as b','b.id = a.table_id','LEFT'),
										array($this->floorplan.' as c','c.id = b.floorplan_id','LEFT'),
										array($this->room.' as c2','c2.id = b.room_id','LEFT'),
										array($this->billing.' as d','d.billing_no = a.billing_no','LEFT')
									)
								),
			'where'			=> array('b.is_deleted' => 0),
			'order'			=> array('c.floorplan_name' => 'ASC', 'b.id' => 'ASC', 'b.table_no' => 'ASC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$show_all_text = $this->input->post('show_all_text');
		$show_choose_text = $this->input->post('show_choose_text');
		$tanggal = $this->input->post('tanggal');
		
		if(empty($tanggal)){
			$tanggal = date("Y-m-d");
		}
		
		$curr_billing = $this->input->post('curr_billing');
		
		$show_available = $this->input->post('show_available');
		if(empty($show_available)){
			$show_available = false;
		}
		
		$show_selected = $this->input->post('show_selected');
		if(empty($show_selected)){
			$show_selected = false;
		}
		
		
		if(!empty($is_dropdown)){
			$params['order'] = array('b.table_no' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.table_name LIKE '%".$searching."%' OR b.table_no LIKE '%".$searching."%')";
		}
		if(!empty($tanggal)){
			$params['where'][] = "a.tanggal = '".$tanggal."'";
		}
		
		if($show_available == true){
			
			if(!empty($curr_billing)){
				$params['where'][] = "(a.status = 'available' OR (a.status != 'available' AND d.id = '".$curr_billing."'))";
			}else{
				$params['where'][] = "a.status = 'available'";
			}
			
		}
		
		if($show_selected == true){
			
			if(!empty($curr_billing)){
				$params['where'][] = "((a.status = 'booked' OR a.status = 'reserved')  AND d.id = '".$curr_billing."')";
			}else{
				$params['where'][] = "a.status = 'available' d.id = -1";
			}
			
		}
		
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();		
		
		if(!empty($show_all_text)){
			$dt = array('id' => '-1', 'table_name' => 'Choose All Table');
			array_push($newData, $dt);
		}else{
			if(!empty($show_choose_text)){
				$dt = array('id' => '', 'table_name' => 'Choose Table');
				array_push($newData, $dt);
			}
		}
		
		if(!empty($get_data['data'])){
		
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				if(!empty($s['kapasitas'])){
					$s['kapasitas_text'] = 'Kapasitas: '.$s['kapasitas'].' org';
				}
				
				$text_tipe = 'Dine In';
				if($s['table_tipe'] == 'takeaway'){
					$text_tipe = 'Take Away';
				}
				if($s['table_tipe'] == 'delivery'){
					$text_tipe = 'Delivery';
				}
				$s['table_tipe_text'] = '<span style="color:green;">'.$text_tipe.'</span>';
				
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	
}