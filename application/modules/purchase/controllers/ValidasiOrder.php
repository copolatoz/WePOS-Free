<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ValidasiOrder extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_requestorder', 'm');
		$this->load->model('model_requestorderdetail', 'm2');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'ro';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, c.divisi_name',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'divisi as c','c.id = a.divisi_id','LEFT')
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
		$is_active = $this->input->post('is_active');
		$ro_status = $this->input->post('ro_status');
		$not_cancel = $this->input->post('not_cancel');
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
				
				$params['where'][] = "(a.ro_date >= '".$qdate_from."' AND a.ro_date <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.ro_number' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.ro_number LIKE '%".$searching."%' OR c.divisi_name LIKE '%".$searching."%')";
		}		
		if(!empty($is_active)){
			$params['where'][] = "a.is_active = '".$is_active."'";
		}
		
		if(!empty($not_cancel)){
			$params['where'][] = "a.ro_status != 'cancel'";
		}else{
			if(!empty($ro_status)){
				$params['where'][] = "a.ro_status = '".$ro_status."'";
			}
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		
  		$newData = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				if($s['ro_status'] == 'request'){
					$s['ro_status_text'] = '<span style="color:green;">Request</span>';
				}else 
				if($s['ro_status'] == 'validated'){
					$s['ro_status_text'] = '<span style="color:blue;">Validated</span>';
				}else{
					$s['ro_status_text'] = '<span style="color:red;">Cancel</span>';
				}
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetail()
	{
		
		$this->table = $this->prefix.'ro_detail';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, b.item_name, b.item_image, c.unit_name, d.supplier_name, p.po_number",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'items as b','a.item_id = b.id','LEFT'),
										array($this->prefix.'unit as c','a.unit_id = c.id','LEFT'),
										array($this->prefix.'supplier as d','a.supplier_id = d.id','LEFT'),
										array($this->prefix.'po as p','a.take_reff_id = p.id','LEFT')
									) 
								),
			'order'			=> array('a.id' => 'DESC'),
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$ro_id = $this->input->post('ro_id');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('b.item_name' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.item_name LIKE '%".$searching."%')";
		}
		if(!empty($ro_id)){
			$params['where'] = array('a.ro_id' => $ro_id);
		}
		
		//get data -> data, totalCount
		$get_data = $this->m2->find_all($params);
		  		
		$newData = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
							
				if(empty($s['take_reff_id'])){
					if($s['ro_detail_status'] == 'request'){
						$s['ro_detail_status_text'] = '<span style="color:green;">Request</span>';
					}else 
					if($s['ro_detail_status'] == 'validated'){
						$s['ro_detail_status_text'] = '<span style="color:blue;">Validated</span>';
					}else 
					if($s['ro_detail_status'] == 'take'){
						$s['ro_detail_status_text'] = '<span style="color:blue;">'.$s['po_number'].'</span>';
					}else 
					if($s['ro_detail_status'] == 'cancel'){
						$s['ro_detail_status_text'] = '<span style="color:red;">Cancel</span>';
					}
				}else{
					$s['ro_detail_status_text'] = '<span>'.$s['po_number'].'</span>';
				}
				
				if(empty($s['supplier_id'])){
					$s['item_price'] = 0;
					$s['item_hpp'] = 0;
				}
				
				$s['item_price_show'] = priceFormat($s['item_price']);
				$s['item_hpp_show'] = priceFormat($s['item_hpp']);
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'ro';	
		$this->table2 = $this->prefix.'ro_detail';			
		$session_user = $this->session->userdata('user_username');
		
		$ro_id = $this->input->post('id');
		$ro_number = $this->input->post('ro_number');
		$ro_date = $this->input->post('ro_date');
		$ro_memo = $this->input->post('ro_memo');
		$ro_from = $this->input->post('ro_from');
		$divisi_id = $this->input->post('divisi_id');
		
		
		if(empty($ro_number)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
			
		$r = '';
		
		//roDetail
		$total_validated = 0;
		$total_request = 0;		
		$total_take = 0;		
		$roDetail = $this->input->post('roDetail');
		$roDetail = json_decode($roDetail, true);
		if(!empty($roDetail)){
			
			$total_qty = 0;
			$dtUpdate = array();
			if(!empty($roDetail)){
				foreach($roDetail as $dt){
					
					if(!empty($dt['supplier_id'])){
						$total_validated += 1;
						$ro_detail_status = 'validated';
					}else{
						$total_request += 1;
						$ro_detail_status = 'request';
					}
					
					if(!empty($dt['take_reff_id'])){
						$ro_detail_status = $dt['ro_detail_status'];
						$total_take += 1;
					}
					
					$dtUpdate[] = array(
						"id" => $dt['id'],
						"ro_id" => $dt['ro_id'],
						"supplier_item_id" => $dt['supplier_item_id'],
						"item_id" => $dt['item_id'],
						"supplier_id" => $dt['supplier_id'],
						"take_reff_id" => $dt['take_reff_id'],
						"ro_detail_status" => $ro_detail_status,
						"item_price" => $dt['item_price'],
						"item_hpp" => $dt['item_hpp']
					);
					
					
				}
			}
			
			if(!empty($dtUpdate)){
				$this->db->update_batch($this->table2, $dtUpdate, 'id');
				
				//cek jika status == taken
				$dtRO = array();
				$this->db->from($this->table);
				$this->db->where("id",$ro_id);
				$getDt = $this->db->get();
				if($getDt->num_rows() > 0){
					$dtRO = $getDt->row_array();
				}				
				
				$ro_status = 'request';
				if(!empty($dtRO['ro_status'])){
					$ro_status = $dtRO['ro_status'];
					
				}else{
					echo json_encode(array('success'=>false));
					die();
				}
							
				if(!in_array($ro_status, array('cancel'))){
					if($total_take > 0){
						$ro_status = 'take';
					}else
					if($total_validated > 0){
						$ro_status = 'validated';
					}else{
						$ro_status = 'request';
					}
				}
				
				//UPDATE Total Qty				
				$update_qty_ro = array(
						'total_request'  => $total_request,
						'total_validated'  => $total_validated,
						'ro_status'  => $ro_status
				);
					
				$this->db->update($this->table, $update_qty_ro, "id = ".$ro_id);
				
				$r = array('success' => true, 'id' => $ro_id, 'status' => $ro_status); 		

			}
		}
		
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function deleteDetail()
	{
	
		$this->table = $this->prefix.'ro_detail';
	
		$ro_id = $this->input->post('ro_id', true);
		$get_id = $this->input->post('id', true);
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
	
		//check data main if been validated
		$this->db->where("id IN ('".$sql_Id."') AND ro_id = ".$ro_id);
		$this->db->where("NOT(ro_detail_status = 'validated' OR ro_detail_status = 'request')");
		$get_dt = $this->db->get($this->table);
		if($get_dt->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Cancel Validasi Order Failed, Item been Taken/Used on PO!');
			die();
		}
	
		$dt_update = array(
			'ro_detail_status' => 'request',
			'supplier_id' => 0,
			'item_price' => 0,
			'item_hpp' => 0
		);
		$q = $this->db->update($this->table,$dt_update,"id IN ('".$sql_Id."')");
	
		$r = '';
		if($q)
		{
			$r = array('success' => true);
			
			$total_validated = 0;
			$total_request = 0;
			$total_take = 0;
			//count validation item
			$this->db->select("*");
			$this->db->from($this->table);
			$this->db->where("ro_id = ".$ro_id);
			$get_roDet = $this->db->get();
			if($get_roDet->num_rows() > 0){
				foreach($get_roDet->result_array() as $dt){
					if(!empty($dt['supplier_id'])){
						$total_validated += 1;
						$ro_detail_status = 'validated';
					}else{
						$total_request += 1;
						$ro_detail_status = 'request';
					}
						
					if(!empty($dt['take_reff_id'])){
						$ro_detail_status = $dt['ro_detail_status'];
						$total_take += 1;
					}
				}
			}
			
			if($total_take > 0){
				$ro_status = 'take';
			}else
			if($total_validated > 0){
				$ro_status = 'validated';
			}else{
				$ro_status = 'request';
			}
			
			$update_qty_ro = array(
				'total_request'  => $total_request,
				'total_validated'  => $total_validated,
				'ro_status'  => $ro_status
			);
			
			$this->db->update($this->prefix.'ro', $update_qty_ro, "id = ".$ro_id);
			
		}
		else
		{
			$r = array('success' => false, 'info' => 'Cancel Validasi Order Failed!');
		}
		die(json_encode($r));
	}
		
}