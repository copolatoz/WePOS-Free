<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class DataClient extends MY_Controller {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('model_DataClient', 'm');
	}

	public function gridData()
	{
		$prefix = $this->prefix;
		$this->table = $this->prefix.'clients';
		
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
		$this->table = $this->prefix.'clients';				
		$session_user = $this->session->userdata('user_username');
		
		$client_code = $this->input->post('client_code');
		$client_name = $this->input->post('client_name');
		$client_address = $this->input->post('client_address');
		$client_logo = $this->input->post('client_logo');
		
		if(empty($client_code) OR empty($client_name)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		$is_active = 0;
		if(!empty($_POST['is_active'])){
			$is_active = 1;
		}
			
		$r = '';
		if($this->input->post('form_type_DataClient', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'id' 		=> 	null,  
				    'client_code'  	=> 	$client_code,
				    'client_name'  	=> 	$client_name,
					'client_address'	=>	$client_address,
					'client_logo'	=>	$client_logo,
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
				
				//CREATE STRUKTUR
				
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_DataClient', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'client_code'  	=> 	$client_code,
					'client_name'  	=> 	$client_name,
					'client_address'	=>	$client_address,
					'client_logo'	=>	$client_logo,
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
		$prefix = $this->prefix;
		$this->table = $this->prefix.'clients';
		
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
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Client Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function clientInfo()
	{
		$prefix = $this->prefix;
		$this->table = $this->prefix.'clients';
		
		//Delete
		//$this->db->where("id = 1");
		$q = $this->db->get($this->table);
		
		$client_name = config_item('client_name');
		
		$data_client = array(
			'client_code'  	=> 	'TRIAL-'.$client_name,
			'client_name'  	=> 	$client_name,
			'client_email'	=>	'',
			'client_phone'	=>	'',
			'client_address'	=>	''
		);
		
		$r = array('success' => true, 'data' => $data_client, 'info' => 'Get Info Client Failed!'); 
		if($q->num_rows() > 0)  
        {  
			$dt = $q->row();
			$data_client = array(
				'client_code'  	=> 	$dt->client_code,
				'client_name'  	=> 	$dt->client_name,
				'client_email'	=>	$dt->client_email,
				'client_phone'	=>	$dt->client_phone,
				'client_address'	=>	$dt->client_address
			);
			
            $r = array('success' => true, 'data' => $data_client, 'info' => 'Get Info Client Success!'); 
        } 
		
		die(json_encode($r));
	}
	
	public function updateClientInfo()
	{
		$prefix = $this->prefix;
		$this->table = $this->prefix.'clients';
				
		$session_user = $this->session->userdata('user_username');
		
		$client_code = $this->input->post('client_code');
		$client_name = $this->input->post('client_name');
		$client_email = $this->input->post('client_email');
		$client_phone = $this->input->post('client_phone');
		$client_address = $this->input->post('client_address');
		
		if(empty($client_email) OR empty($client_name) OR empty($client_phone)){
			$r = array('success' => false, "info" => "Update Info Failed!");
			die(json_encode($r));
		}		
		
		$var = array('fields'	=>	array(
				'client_code'  	=> 	$client_code,
				'client_name'  	=> 	$client_name,
				'client_email'  	=> 	$client_email,
				'client_phone'  	=> 	$client_phone,
				'client_address'	=>	$client_address,
				'updated'		=>	date('Y-m-d H:i:s'),
				'updatedby'		=>	$session_user
			),
			'table'			=>  $this->table,
			'primary_key'	=>  'id'
		);
		
		//UPDATE
		$id = 1;
		$this->lib_trans->begin();
			$update = $this->m->save($var, $id);
		$this->lib_trans->commit();
		
		$data_client = array(
			'client_code'  	=> 	$client_code,
			'client_name'  	=> 	$client_name,
			'client_email'  	=> 	$client_email,
			'client_phone'  	=> 	$client_phone,
			'client_address'	=>	$client_address,
		);
		
		
		$r = array('success' => true, 'data' => $data_client, 'info' => 'Save Client Info Failed!'); 
		if($update)
		{  
			$r = array('success' => true, 'data' => $data_client, 'info' => 'Client Info Updated!');
		}  
		
		die(json_encode($r));
	}
	
	public function weposID()
	{
		$prefix = $this->prefix;
		$this->table = $this->prefix.'clients';
		
		$this->db->where("id = 1");
		$q = $this->db->get($this->table);
		
		if($q->num_rows() > 0)  
        {  
			$dt = $q->row();
			
			$programName = config_item('program_name_short');
			$programVersion = config_item('program_version');
			$programRelease = config_item('program_release');
			
			$this->load->library('curl');
			$mktime_dc = strtotime(date("d-m-Y H:i:s"));
			$client_url = 'https://wepos.id/aplikasi-pos/client-info?_dc='.$mktime_dc;
			$client_url .= '&client_name='.urlencode($dt->client_name);
			$client_url .= '&client_address='.urlencode($dt->client_address);
			$client_url .= '&client_phone='.urlencode($dt->client_phone);
			$client_url .= '&client_fax='.urlencode($dt->client_fax);
			$client_url .= '&client_email='.urlencode($dt->client_email);
			$client_url .= '&programName='.urlencode($programName);
			$client_url .= '&programVersion='.urlencode($programVersion);
			$client_url .= '&programRelease='.urlencode($programRelease);
			$curl_ret = $this->curl->simple_get($client_url);
			
        } 
		
		$r = array('success' => true); 
		die(json_encode($r));
	}
	
}