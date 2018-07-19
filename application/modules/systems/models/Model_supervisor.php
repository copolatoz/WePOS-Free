<?php
class Model_Supervisor extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'supervisor';
	}
	
	function verify($spv = ''){
	
		$return_var = array(
			'confirm' => false,
			'data' => array(),
			'info' => 'verify spv failed!'
		);
		
		if(!empty($spv)){
			
			extract($spv);
			$session_user = $this->session->userdata('user_username');	
			
			
			//check spv
			$spv_id = 0;
			
			
			if(!empty($access)){
				$this->db->select("a.*, a.id as supervisor_id, b.user_username, c.id as supervisor_access_id, c.supervisor_access");
			}else{
				$this->db->select("a.*, a.id as supervisor_id, b.user_username, 0 as supervisor_access_id, '-' as supervisor_access");
			}
			
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix."users as b","b.id = a.user_id");
						
			if(!empty($access)){
				//just verify user
				$this->db->join($this->prefix."supervisor_access as c","c.supervisor_id = a.id");
				$this->db->where("c.supervisor_access = '".$access."'");
				$this->db->where("c.is_deleted = 0");
			}
			
			if(empty($pin_mode)){
				$this->db->where("b.user_username = '".$username."'");
				if(!empty($verifyMode)){
					$this->db->where("b.user_password = '".md5($password)."'");
				}
			}else{
				$this->db->where("b.user_pin = '".$user_pin."'");
			}
			
			
			$this->db->where("b.is_deleted = 0");
			$this->db->where("a.is_deleted = 0");
			
			$spv_data = array();
			$get_spv = $this->db->get();
			if($get_spv->num_rows() > 0){
				$spv_data = $get_spv->row_array();
				$return_var['confirm'] = true;
				$return_var['data'] = $spv_data;
				$return_var['info'] = 'ok';
			}else{
				$return_var['confirm'] = false;
				$return_var['info'] = 'check spv failed!';
				$return_var['data'] = $this->db->last_query();
			}
			
			if(!empty($log) AND !empty($spv_data['supervisor_id'])){
				//save log
				$date_now = date('Y-m-d H:i:s');
				
				if(empty($spv_data['supervisor_access'])){
					$spv_data['supervisor_access'] = '';
				}
				
				$saveLog = array(
					'supervisor_id'	=> $spv_data['supervisor_id'],
					'supervisor_access'	=> $spv_data['supervisor_access'],
					'supervisor_access_id'	=> $spv_data['supervisor_id'],
					'log_data'	=> $data,
					'ref_id_1'	=> $ref_id_1,
					'ref_id_2'	=> $ref_id_2,
					'created'	=> $date_now,
					'createdby'	=> $session_user,
					'updated'	=> $date_now,
					'updatedby'	=> $session_user
				);
				
				$savelog_spv = $this->db->insert($this->prefix."supervisor_log", $saveLog);
				if($savelog_spv == false){
					$return_var['info'] = 'save log failed!';
				}
			}
			
			return $return_var;
			
		}
		
		return $return_var;
	}

} 