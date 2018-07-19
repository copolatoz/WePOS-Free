<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class RequestOrder extends MY_Controller {
	
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
		$this->table2 = $this->prefix.'ro_detail';
		
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
		$all_id = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				if(!in_array($s['id'], $all_id)){
					$all_id[] = $s['id'];
				}
			}
		}
		
		//get total
		/*$total_item = array();
		if(!empty($all_id)){
			$all_id_txt = implode(",", $all_id);
			$this->db->select("SUM(1) as total_item, ro_id");
			$this->db->from($this->table2);
			$this->db->where("ro_id IN (".$all_id_txt.")");
			$this->db->group_by("ro_id");
			$get_detail = $this->db->get();
			if($get_detail->num_rows() > 0){
				foreach($get_detail->result() as $dt){
					$total_item[$dt->ro_id] = $dt->total_item;
				}
			}
		}*/
		
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				if($s['ro_status'] == 'request'){
					$s['ro_status_text'] = '<span style="color:green;">Request</span>';
				}else 
				if($s['ro_status'] == 'validated'){
					$s['ro_status_text'] = '<span style="color:orange;">Validated</span>';
				}else
				if($s['ro_status'] == 'take'){
					$s['ro_status_text'] = '<span style="color:blue;">Purchased</span>';
				}else{
					$s['ro_status_text'] = '<span style="color:red;">Cancel</span>';
				}
				
				//$s['total_item'] = 0;
				//if(!empty($total_item[$s['id']])){
				//	$s['total_item'] = $total_item[$s['id']];
				//}
				
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
			'fields'		=> "a.*, b.item_name, b.item_price, b.item_image, c.unit_name",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'items as b','a.item_id = b.id','LEFT'),
										array($this->prefix.'unit as c','a.unit_id = c.id','LEFT')
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
		  		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'ro';	
		$this->table2 = $this->prefix.'ro_detail';			
		$session_user = $this->session->userdata('user_username');
		
		$ro_date = $this->input->post('ro_date');
		$ro_memo = $this->input->post('ro_memo');
		$ro_from = $this->input->post('ro_from');
		$divisi_id = $this->input->post('divisi_id');
		
		$total_item = 0;
		$total_request = 0;
		//roDetail				
		$roDetail = $this->input->post('roDetail');
		$roDetail = json_decode($roDetail, true);
		if(!empty($roDetail)){
			$total_item = count($roDetail);
			foreach($roDetail as $dtDet){
				if($dtDet['ro_detail_status'] == 'new' OR $dtDet['ro_detail_status'] == 'request'){
					$total_request += 1;
				}
			}
		}
				
		$get_ro_number = requestOrder::generate_ro_number();
		
		if(empty($get_ro_number)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
			
		$r = '';
		if($this->input->post('form_type_requestOrder', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'ro_number'  	=> 	$get_ro_number,
				    'ro_date'  		=> 	$ro_date,
				    'ro_memo'  		=> 	$ro_memo,
				    'ro_from'  		=> 	$ro_from,
				    'divisi_id'  	=> 	$divisi_id,
				    'total_item' 	=> $total_item,
				    'total_request' 	=> $total_request,
				    'ro_status'  	=> 	'request',
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active
				),
				'table'		=>  $this->table
			);	
			
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
				$q = $this->m->add($var);
				$insert_id = $this->m->get_insert_id();
			$this->lib_trans->commit();			
			if($q)
			{  
				$r = array('success' => true, 'id' => $insert_id, 'ro_number'	=> '-'); 		
				$q_det = $this->m2->roDetail($roDetail, $insert_id);
				if(!empty($q_det['dtRo']['ro_number'])){
					$r['ro_number'] = $q_det['dtRo']['ro_number'];
				}
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_requestOrder', true) == 'edit'){
			$var = array('fields'	=>	array(
				    //'ro_number'  	=> 	$ro_number,
				    'ro_date'  		=> 	$ro_date,
				    'ro_memo'  		=> 	$ro_memo,
				    'ro_from'  		=> 	$ro_from,
				    'divisi_id'  	=> 	$divisi_id,
				    'total_item' 	=> $total_item,
				    'total_request' 	=> $total_request,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			//UPDATE
			$id = $this->input->post('id', true);
			$this->lib_trans->begin();
				$update = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				$r = array('success' => true, 'id' => $id);
				
				$is_status_done = false;
				//check data main if been take
				$this->db->from($this->table);
				$this->db->where("id IN ('".$id."')");
				$this->db->where("ro_status = 'take'");
				$get_dt = $this->db->get();
				if($get_dt->num_rows() > 0){
					//status is DONE!
					$is_status_done = true;
				}
				
				if($is_status_done == false){
					$q_det = $this->m2->roDetail($roDetail, $id);
				}
				
			}  
			else
			{  
				$r = array('success' => false);
			}
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function saveDetail(){
		$this->table = $this->prefix.'ro_detail';				
		
		$session_user = $this->session->userdata('user_username');
		$session_client_id = $this->session->userdata('client_id');
		
		$ro_id = $this->input->post('ro_id');
		$item_id = $this->input->post('item_id');
		$ro_detail_qty = $this->input->post('ro_detail_qty');
		$unit_id = $this->input->post('unit_id');
		
		if(empty($ro_id) OR empty($ro_detail_qty) OR empty($item_id) OR empty($session_client_id)){
			$r = array('success' => false, 'info' => 'Save Detail Failed!');
			die(json_encode($r));
		}		
		
		$var = array('fields'	=>	array(
				'ro_id'			=> 	$ro_id,
				'item_id' 		=> 	$item_id,
				'ro_detail_qty' => 	$ro_detail_qty,
				'unit_id'	 	=> 	$unit_id
			),
			'table'			=>  $this->table,
			'primary_key'	=>  'id'
		);
		
		//ADD		
		$this->lib_trans->begin();
			$add = $this->m2->save($var);
		$this->lib_trans->commit();
		
		if($add)
		{  
			$r = array('success' => true, 'item_id' => $item_id);
			
			//UPDATE Total Qty
			$var2 = array('fields'	=>	array(
					'ro_total_qty'  => requestOrder::get_total_qty($ro_id)
				),
				'table'			=>  $this->prefix.'ro',
				'primary_key'	=>  'id'
			);
			
			$this->lib_trans->begin();
				$update = $this->m->save($var2, $ro_id);
			$this->lib_trans->commit();
			
		}  
		else
		{  
			$r = array('success' => false, 'info' => 'Save Detail Failed!');
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
		
	public function delete()
	{
		
		$this->table = $this->prefix.'ro';
		$this->table2 = $this->prefix.'ro_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//check data main if been validated
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		$this->db->where("ro_status IN ('validated', 'take')");
		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Request Order number been validated / used!</br>Please Refresh List RO'); 
			die(json_encode($r));		
		}		
		
		//delete data
		$update_data = array(
			'ro_status'	=> 'cancel',
			'is_deleted' => 1
		);
		
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->update($this->table, $update_data);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
			
			//delete detail too
			$update_data2 = array(
				'ro_detail_status'	=> 'cancel'
			);
			
			$this->db->where("ro_id IN ('".$sql_Id."')");
			$this->db->update($this->table2, $update_data2);
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Cancel Request Order Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function deleteDetail()
	{
		
		$this->table = $this->prefix.'ro_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//check data main if been validated
		$this->db->where("id IN ('".$sql_Id."')");
		$this->db->where("ro_detail_status = 'validated'");
		$get_dt = $this->db->get($this->table);
		if($get_dt->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Request Order number been validated / used!'); 
			die();			
		}
		
		//delete data
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->delete($this->table);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Request Order Detail Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function get_total_qty($ro_id){
		$this->table = $this->prefix.'ro_detail';	
		
		$this->db->select('SUM(ro_detail_qty) as total_qty');
		$this->db->from($this->table);
		$this->db->where('ro_id', $ro_id);
		$get_tot = $this->db->get();
		
		$total_qty = 0;
		if($get_tot->num_rows() > 0){
			$data_ro = $get_tot->row();
			$total_qty = $data_ro->total_qty;
		}
		
		return $total_qty;
	}
	
	
	public function generate_ro_number(){
		$this->table = $this->prefix.'ro';						
		
		$this->db->from($this->table);
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_ro = $get_last->row();
			//$ro_number = $data_ro->ro_number;
			$ro_number = str_replace("RO","", $data_ro->ro_number);
						
			$ro_number = (int) $ro_number;			
		}else{
			$ro_number = 0;
		}
		
		$ro_number++;
		$length_no = strlen($ro_number);
		switch ($length_no) {
			case 5:
				$ro_number = '0'.$ro_number;
				break;
			case 4:
				$ro_number = '00'.$ro_number;
				break;
			case 3:
				$ro_number = '000'.$ro_number;
				break;
			case 2:
				$ro_number = '0000'.$ro_number;
				break;
			case 1:
				$ro_number = '00000'.$ro_number;
				break;
			default:
				$ro_number = '00000'.$ro_number;
				break;
		}
				
		return 'RO'.$ro_number;				
	}
	
	public function printRO(){
		
		$this->table  = $this->prefix.'ro'; 
		$this->table2 = $this->prefix.'ro_detail';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		
		$data_post = array(
			'do'	=> '',
			'ro_data'	=> array(),
			'ro_detail'	=> array(),
			'report_place_default'	=> '',
			'user_fullname'	=> $user_fullname
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($ro_id)){
			die('Request Order Not Found!');
		}else{
			
			$this->db->select("a.*, b.divisi_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix."divisi as b","b.id = a.divisi_id","LEFT");
			$this->db->where("a.id = '".$ro_id."'");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['ro_data'] = $get_dt->row_array();
				
				//get detail
				$this->db->select("a.*, b.item_name, b.item_type, c.unit_code, c.unit_name");
				$this->db->from($this->table2." as a");
				$this->db->join($this->prefix."items as b","b.id = a.item_id","LEFT");
				$this->db->join($this->prefix."unit as c","c.id = a.unit_id","LEFT");
				$this->db->where("a.ro_id = '".$ro_id."'");
				$get_det = $this->db->get();
				if($get_det->num_rows() > 0){
					$data_post['ro_detail'] = $get_det->result_array();
				}
				
			}else{
				die('Request Order Not Found!');
			}
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}
		
		$this->load->view('../../purchase/views/printRO', $data_post);
		
	}
}