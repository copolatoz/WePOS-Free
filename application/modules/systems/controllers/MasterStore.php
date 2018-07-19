<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MasterStore extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix');
		$this->load->model('model_masterstore', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'clients';
		$this->client_user = $this->prefix.'client_users';
		$session_id_user = $this->session->userdata('id_user');
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> '*',
			'primary_key'	=> 'id',
			'table'			=> $this->table,
			'where'			=> array('is_deleted' => 0),
			'order'			=> array('id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$keywords = $this->input->post('keywords');
		$except_primary = $this->input->post('except_primary');
		$is_active = $this->input->post('is_active');
		$show_all_text = $this->input->post('show_all_text');
		$show_choose_text = $this->input->post('show_choose_text');
		$all_store = $this->input->post('all_store');
		
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('client_name' => 'ASC');
		}
		
		if(!empty($searching)){
			$params['where'][] = "(client_name LIKE '%".$searching."%' OR client_code LIKE '%".$searching."%')";
		}
		
		
		if(!empty($is_active)){
			$params['where'][] = "is_active = 1";
		}
		
		if($all_store == 1){
			
		}else{
			$params['where'][] = "id != 1";
		}
		
		
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($show_all_text)){
			$dt = array('id' => '-1', 'client_name' => 'Semua Store');
			array_push($newData, $dt);
		}else{
			if(!empty($show_choose_text)){
				$dt = array('id' => '', 'client_name' => 'Pilih Store');
				array_push($newData, $dt);
			}
		}
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'clients';				
		$session_user = $this->session->userdata('user_username');
		
		$client_code = $this->input->post('client_code');
		$client_name = $this->input->post('client_name');
		$client_ip = $this->input->post('client_ip');
		$client_address = $this->input->post('client_address');
		$client_phone = $this->input->post('client_phone');
		$client_email = $this->input->post('client_email');
		$client_notes = $this->input->post('client_notes');
		
		$mysql_port = $this->input->post('mysql_port');
		$mysql_user = $this->input->post('mysql_user');
		$mysql_pass = $this->input->post('mysql_pass');
		$mysql_database = $this->input->post('mysql_database');
		
		if(empty($client_name) OR empty($client_name)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		if(empty($mysql_port)){
			$mysql_port = '3306';
		}
		
		if(empty($mysql_database)){
			$r = array('success' => false, 'info' => 'Input Mysql Database');
			die(json_encode($r));
		}		
		if(empty($mysql_user)){
			$r = array('success' => false, 'info' => 'Input Mysql Database');
			die(json_encode($r));
		}		
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
		
		//check code
		//Delete
		$this->db->from($this->table);
		$this->db->where("client_code = '".$client_code."'");
		if($this->input->post('form_type_masterStore', true) == 'edit'){
			$id = $this->input->post('id', true);
			$this->db->where("id != ".$id);
		}
		$q = $this->db->get();
		if($q->num_rows() > 0){
			$r = array('success' => false, 'info' => "Master Store Code Available!");
			die(json_encode($r));
		}
			
		$r = '';
		if($this->input->post('form_type_masterStore', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
					'client_code'	=>	$client_code,
				    'client_name'  	=> 	$client_name,
				    'client_ip'  	=> 	$client_ip,
				    'client_address'  	=> 	$client_address,
				    'client_phone'  	=> 	$client_phone,
				    'client_email'  	=> 	$client_email,
				    'client_notes'  	=> 	$client_notes,
				    'mysql_port'  	=> 	$mysql_port,
				    'mysql_user'  	=> 	$mysql_user,
				    'mysql_pass'  	=> 	$mysql_pass,
				    'mysql_database' => $mysql_database,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'	=>	$is_active
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
		if($this->input->post('form_type_masterStore', true) == 'edit'){
			$var = array('fields'	=>	array(
					'client_code'	=>	$client_code,
				    'client_name'  	=> 	$client_name,
				    'client_ip'  	=> 	$client_ip,
				    'client_address'  	=> 	$client_address,
				    'client_phone'  	=> 	$client_phone,
				    'client_email'  	=> 	$client_email,
				    'client_notes'  	=> 	$client_notes,
				    'mysql_port'  	=> 	$mysql_port,
				    'mysql_user'  	=> 	$mysql_user,
				    'mysql_pass'  	=> 	$mysql_pass,
				    'mysql_database' => $mysql_database,
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
		$this->table = $this->prefix.'clients';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			
			if(in_array(1,$id)){
				$r = array('success' => false, 'info' => 'Delete Store Failed, Main Store Cannot Deleted'); 
				die(json_encode($r));
			}
			
			$sql_Id = implode(',', $id);
		}
		
		//Delete
		$data_update = array(
			"is_deleted" => 1
		);
		$q = $this->db->update($this->table,$data_update,"id IN (".$sql_Id.")");
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Store Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function cekClient()
	{
		$this->table = $this->prefix.'clients';
		
		$client_code = $this->input->get_post('client_code', true);		
		
		$this->db->from($this->table);
		$this->db->where("client_code = '".$client_code."'");
		$q = $this->db->get();
		if($q->num_rows() > 0){
			$dt = $q->row_array();
			unset($dt['mysql_pass']);
			unset($dt['mysql_user']);
			$r = array('success' => true, 'info' => "Client Available!", "data" => $dt);
			die(json_encode($r));
		}else{
			$r = array('success' => false, 'info' => 'Cek Store Failed!'); 
		}
		
		die(json_encode($r));
	}
	
}