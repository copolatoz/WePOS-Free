<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MasterTable extends MY_Controller {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_mastertable', 'm');		
	}

	public function gridData()
	{
		$this->table = $this->prefix.'table';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		//MEMCACHED SESSION
		$use_memcached = $this->input->post('use_memcached');
		if($use_memcached == 1){
			//reload memcached
		}else{
			//empty memcached
			
		}
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'a.is_active',
			'table_tipe_text' => 'a.table_tipe'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, b.floorplan_name, c.room_name, c.room_no",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'floorplan as b','b.id = a.floorplan_id','LEFT'),
										array($this->prefix.'room as c','c.id = a.room_id','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$show_all_text = $this->input->post('show_all_text');
		$show_choose_text = $this->input->post('show_choose_text');
		$keywords = $this->input->post('keywords');

		$show_available = $this->input->post('show_available');
		if(empty($show_available)){
			$show_available = false;
		}
		
		$curr_billing = $this->input->post('curr_billing');
		
		if(!empty($keywords)){
			$searching = $keywords;
		}
		if(!empty($is_dropdown)){
			$params['order'] = array('table_no' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(table_name LIKE '%".$searching."%' OR table_no LIKE '%".$searching."%' OR  b.floorplan_name LIKE '%".$searching."%' OR c.room_name LIKE '%".$searching."%'  OR c.room_no LIKE '%".$searching."%' )";
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
		
		//check available check today hold and active
		$date_today = date("Y-m-d");
		$date_today_mk_plus1 = strtotime(date("Y-m-d"))+ONE_DAY_UNIX;
		$hour_today = date("G");
		if($hour_today <= 5){
			$date_today_mk = strtotime(date("Y-m-d"))-ONE_DAY_UNIX;
			$date_today = date("Y-m-d", $date_today_mk);
			$date_today_mk_plus1 = strtotime(date("Y-m-d"));
		}
		
		$date_today_plus1 = date("Y-m-d", $date_today_mk_plus1);
		$available_table = array();
		if($show_available == true){
			$this->db->from($this->prefix.'billing');
			$this->db->where("billing_status IN ('hold', 'unpaid')");
			$this->db->where("updated >= '".$date_today." 06:00:01' AND updated <= '".$date_today_plus1." 06:00:00'");
			$this->db->where("table_id != 0");
			
			if(!empty($curr_billing)){
				$this->db->where("id != ".$curr_billing);
			}
			
			$getAvailable = $this->db->get();
			if($getAvailable->num_rows() > 0){
				foreach($getAvailable->result_array() as $dt){
					if(!in_array($dt['table_id'], $available_table)){
						$available_table[] = $dt['table_id'];
					}
				}
			}
		}
		
		if(!empty($get_data['data'])){
		
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				$text_tipe = 'Dine In';
				if($s['table_tipe'] == 'takeaway'){
					$text_tipe = 'Take Away';
				}
				if($s['table_tipe'] == 'delivery'){
					$text_tipe = 'Delivery';
				}
				$s['table_tipe_text'] = '<span style="color:green;">'.$text_tipe.'</span>';
				
				
				if(!in_array($s['id'], $available_table)){
					array_push($newData, $s);
				}
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'table';				
		$session_user = $this->session->userdata('user_username');
		$floorplan_id = $this->input->post('floorplan_id');		
		$table_name = $this->input->post('table_name');
		$table_desc = $this->input->post('table_desc');
		$table_no = $this->input->post('table_no');
		$room_id = $this->input->post('room_id');
		$kapasitas = $this->input->post('kapasitas');
		$table_tipe = $this->input->post('table_tipe');
		
		if(empty($table_no) OR empty($table_name) OR empty($floorplan_id)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		$is_active = 0;
		if(!empty($_POST['is_active'])){
			$is_active = 1;
		}
		
		if(empty($table_tipe)){
			$table_tipe = 'dinein';
		}
			
		$r = '';
		if($this->input->post('form_type_masterTable', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'table_no' 		=> 	$table_no,
				    'table_name' 	=> 	$table_name,
				    'table_desc' 	=> 	$table_desc,
				    'floorplan_id' 	=> 	$floorplan_id,
				    'room_id' 		=> 	$room_id,
				    'kapasitas' 	=> 	$kapasitas,
				    'table_tipe' 	=> 	$table_tipe,
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
				$r = array('success' => true, 'id' => $insert_id); 
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_masterTable', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'table_no'		=> 	$table_no,
				    'table_name' 	=> 	$table_name,
				    'table_desc'	=> 	$table_desc,
				    'floorplan_id' 	=> 	$floorplan_id,
				    'room_id' 		=> 	$room_id,
				    'kapasitas' 	=> 	$kapasitas,
				    'table_tipe' 	=> 	$table_tipe,
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
			}  
			else
			{  
				$r = array('success' => false);
			}
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function delete()
	{
		$this->table = $this->prefix.'table';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
		
		//Delete
		//$this->db->where("id IN (".$sql_Id.")");
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
            $r = array('success' => false, 'info' => 'Hapus Data Meja Gagal!'); 
        }
		die(json_encode($r));
	}
	
}