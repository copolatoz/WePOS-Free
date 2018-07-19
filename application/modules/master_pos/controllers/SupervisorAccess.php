<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class SupervisorAccess extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix');
		$this->load->model('model_supervisoraccess', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'supervisor_access';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'a.is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, b.user_id, c.user_username',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'supervisor as b','b.id = a.supervisor_id','LEFT'),
										array($this->prefix.'users as c','c.id = b.user_id', 'LEFT')
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
		$keywords = $this->input->post('keywords');
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.id' => 'ASC');
		}
		
		if(!empty($searching)){
			$params['where'][] = "(c.user_username LIKE '%".$searching."%' OR a.supervisor_access LIKE '%".$searching."%')";
		}
		
		$params['where'][] = "c.id != 0";
		$params['where'][] = "c.is_deleted = 0";
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
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
		$this->table = $this->prefix.'supervisor_access';				
		$session_user = $this->session->userdata('user_username');
		
		$supervisor_access = $this->input->post('supervisor_access');
		$supervisor_id = $this->input->post('supervisor_id');
		$user_id = $this->input->post('user_id');
		$user_username = $this->input->post('user_username');
		
		if(empty($supervisor_access)){
			$r = array('success' => false, 'info' => 'Select Supervisor Access!');
			die(json_encode($r));
		}		
			
		
		if(empty($user_id)){
			$r = array('success' => false, 'info' => 'User not found!');
			die(json_encode($r));
		}		
		
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
		//check supervisor id
		if(empty($supervisor_id)){
			$this->db->select("*");
			$this->db->from($this->prefix."supervisor");
			$this->db->where("user_id = ".$user_id);
			$get_spv_id = $this->db->get();
			$data_spv = array();
			if($get_spv_id->num_rows() > 0){
				$data_spv = $get_spv_id->row_array();
				$supervisor_id = $data_spv['id'];
			}else{
				
				$new_spv = array(
					'user_id'  		=> 	$user_id,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	1
				);	
				$this->db->insert($this->prefix."supervisor", $new_spv);
				$supervisor_id = $this->db->insert_id();
				
			}
		}
		
		//check jika sudah ada
		$this->db->select("*");
		$this->db->from($this->table);
		$this->db->where("supervisor_id", $supervisor_id);
		$this->db->where("supervisor_access", $supervisor_access);
		$this->db->where("is_deleted", 0);
		$get_data = $this->db->get();
		if($get_data->num_rows() > 0){
			
			if($this->input->post('form_type_supervisorAccess', true) == 'add')
			{
				$r = array('success' => false, 'info' => 'User with Selected Access availabe!');
				die(json_encode($r));
			}
			
		}
			
		$r = '';
		if($this->input->post('form_type_supervisorAccess', true) == 'add')
		{
			
			if(empty($supervisor_id)){
				$r = array('success' => false, 'info' => 'Supervisor id not found!');
				die(json_encode($r));
			}
			
			
			$var = array(
				'fields'	=>	array(
				    'supervisor_access' => $supervisor_access,
				    'supervisor_id'  	=> 	$supervisor_id,
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
		if($this->input->post('form_type_supervisorAccess', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'supervisor_access' => $supervisor_access,
				    'supervisor_id'  	=> 	$supervisor_id,
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
		$this->table = $this->prefix.'supervisor_access';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
		
		//Delete
		$this->db->where("id IN (".$sql_Id.")");
		$q = $this->db->delete($this->table);
		
		/*$data_update = array(
			"is_deleted" => 1
		);
		$q = $this->db->update($this->table, $data_update, "id IN (".$sql_Id.")");
		*/
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Access Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function loadSetup(){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$opt_val = array(
			'spv_access_active'
		);
		
		$get_opt = get_option_value($opt_val);
		
		$retValue = array('success' => true);
			
		$spv_access_active = array();
		if(!empty($get_opt['spv_access_active'])){
			$exp_dt = explode(",",$get_opt['spv_access_active']);
			foreach($exp_dt as $dt){
				$spv_access_active[trim($dt)] = 1;
			}
		}
		
		$retValue['supervisorAccess'] = $spv_access_active;
				
		die(json_encode($retValue));
	}
	
	public function save_setupSupervisorAccess(){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
			
		$open_close_cashier = $this->input->post('open_close_cashier', true);
		$cancel_billing = $this->input->post('cancel_billing', true);	
		$cancel_order = $this->input->post('cancel_order', true);	
		$retur_order = $this->input->post('retur_order', true);	
		$unmerge_billing = $this->input->post('unmerge_billing', true);	
		$change_ppn = $this->input->post('change_ppn', true);	
		$change_service = $this->input->post('change_service', true);	
		$change_dp = $this->input->post('change_dp', true);	
		$set_compliment_item = $this->input->post('set_compliment_item', true);	
		$clear_compliment_item = $this->input->post('clear_compliment_item', true);	
		$approval_po = $this->input->post('approval_po', true);	
		
		$r = array('success' => false);
		
		$supervisorAccess = array(
			'open_close_cashier' => $open_close_cashier,
			'cancel_billing' => $cancel_billing,
			'cancel_order' => $cancel_order,
			'retur_order' => $retur_order,
			'unmerge_billing' => $unmerge_billing,
			'change_ppn' => $change_ppn,
			'change_service' => $change_service,
			'change_dp' => $change_dp,
			'set_compliment_item' => $set_compliment_item,
			'clear_compliment_item' => $clear_compliment_item,
			'approval_po' => $approval_po
		);
		
		$supervisorAccess_name = array();
		foreach($supervisorAccess as $key => $dt){
			if($dt == 1){
				$supervisorAccess_name[] = $key;
			}
		}
		
		$data_option = array('spv_access_active' => implode(",", $supervisorAccess_name));
		
		//UPDATE OPTIONS
		$update_option = update_option($data_option);
		if($update_option){
			$r = array('success' => true, 
				"supervisorAccess" => $supervisorAccess
			);
		}
		
		die(json_encode($r));
	}
}