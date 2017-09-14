<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class accAutoPosting extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix3');
		$this->load->model('model_acc_autoposting', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'autoposting';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'a.is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, b.kode_rek as kode_rek_debet, b.nama_rek as nama_rek_debet, c.nama_rek as nama_rek_kredit, c.kode_rek as kode_rek_kredit',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table." as a",
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'kode_rekening as b','b.id = a.rek_id_debet','LEFT'),
										array($this->prefix.'kode_rekening as c','c.id = a.rek_id_kredit','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('a.autoposting_name' => 'ASC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		$autoposting_tipe = $this->input->post('autoposting_tipe');
		
		if(!empty($searching)){
			$params['where'][] = "(a.autoposting_name LIKE '%".$searching."%')";
		}
		if(!empty($autoposting_tipe)){
			$params['where'][] = "(a.autoposting_tipe = '".$autoposting_tipe."')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				$s['rek_id_debet_name'] = $s['kode_rek_debet'].' - '.$s['nama_rek_debet'];
				$s['rek_id_kredit_name'] = $s['kode_rek_kredit'].' - '.$s['nama_rek_kredit'];
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'autoposting';				
		$session_user = $this->session->userdata('user_username');
		
		$autoposting_name = $this->input->post('autoposting_name');
		$autoposting_tipe = $this->input->post('autoposting_tipe');
		$rek_id_debet = $this->input->post('rek_id_debet');
		$rek_id_kredit = $this->input->post('rek_id_kredit');
		
		if(empty($autoposting_name) OR empty($autoposting_tipe)){
			$r = array('success' => false, 'info' => 'Tipe and Name cannot empty!');
			die(json_encode($r));
		}		
		
		if(empty($rek_id_debet) OR empty($rek_id_kredit)){
			$r = array('success' => false, 'info' => 'Akun Debet and Akun Kredit cannot empty!');
			die(json_encode($r));
		}		
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
		$id = $this->input->post('id', true);
			
			
		$r = '';
		if($this->input->post('form_type_accAutoPosting', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'autoposting_name'  => 	$autoposting_name,
				    'autoposting_tipe' 	=> 	$autoposting_tipe,
				    'rek_id_debet' 		=> 	$rek_id_debet,
				    'rek_id_kredit' 	=> $rek_id_kredit,
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
		if($this->input->post('form_type_accAutoPosting', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'autoposting_name'  => 	$autoposting_name,
				    'autoposting_tipe' 	=> 	$autoposting_tipe,
				    'rek_id_debet' 		=> 	$rek_id_debet,
				    'rek_id_kredit' 	=> $rek_id_kredit,
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
		$this->table = $this->prefix.'autoposting';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//Delete
		//$this->db->where("id IN ('".$sql_Id."')");
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
            $r = array('success' => false, 'info' => 'Hapus Tipe Jurnal Gagal!'); 
        }
		die(json_encode($r));
	}
	
}