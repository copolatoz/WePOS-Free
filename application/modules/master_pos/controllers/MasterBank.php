<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MasterBank extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_masterbank', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'bank';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, b.payment_type_name as payment_text',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'payment_type as b','b.id = a.payment_id','LEFT')
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
		$payment_id = $this->input->post('payment_id');
		$searching = $this->input->post('query');
		$show_all_text = $this->input->post('show_all_text');
		$show_choose_text = $this->input->post('show_choose_text');
		$keywords = $this->input->post('keywords');
		
		if(!empty($keywords)){
			$searching = $keywords;
		}
		if(!empty($is_dropdown)){
			$params['order'] = array('bank_code' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(bank_name LIKE '%".$searching."%' OR bank_code LIKE '%".$searching."%')";
		}
		if(!empty($payment_id)){
			$params['where'][] = "a.payment_id = ".$payment_id;
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($show_all_text)){
			$dt = array('id' => '-1', 'payment_name' => 'Choose All Payment');
			array_push($newData, $dt);
		}else{
			if(!empty($show_choose_text)){
				$dt = array('id' => '', 'payment_name' => 'Choose Payment');
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
		$this->table = $this->prefix.'bank';				
		$session_user = $this->session->userdata('user_username');
		
		$bank_code = $this->input->post('bank_code');
		$bank_name = $this->input->post('bank_name');
		$payment_id = $this->input->post('payment_id');
		
		if(empty($bank_name)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
			
		$r = '';
		if($this->input->post('form_type_masterBank', true) == 'add')
		{
			
			//check kode
			$this->db->from($this->table);
			$this->db->where("bank_code = '".$bank_code."'");
			$get_bank = $this->db->get();
			if($get_bank->num_rows() > 0){
				$r = array('success' => false, 'info'	=> "Kode Bank: ".$bank_code." sudah Ada!");
				die(json_encode($r));
			}
			
			$var = array(
				'fields'	=>	array(
					'bank_code'		=>	$bank_code,
				    'bank_name'  	=> 	$bank_name,
					'payment_id'	=>	$payment_id,
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
		if($this->input->post('form_type_masterBank', true) == 'edit'){
			$id = $this->input->post('id', true);
			
			//check kode
			$this->db->from($this->table);
			$this->db->where("bank_code = '".$bank_code."' AND id = '".$id."'");
			$get_bank = $this->db->get();
			if($get_bank->num_rows() > 0){
				$r = array('success' => false, 'info'	=> "Kode Bank: ".$bank_code." sudah Ada!");
				die(json_encode($r));
			}
			
			$var = array('fields'	=>	array(
					'bank_code'		=>	$bank_code,
				    'bank_name'  	=> 	$bank_name,
					'payment_id'	=>	$payment_id,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			//UPDATE
			
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
		$this->table = $this->prefix.'bank';
		
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
            $r = array('success' => false, 'info' => 'Delete Bank Failed!'); 
        }
		die(json_encode($r));
	}
	
}