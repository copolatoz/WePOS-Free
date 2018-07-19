<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MasterPrinter extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_masterprinter', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'printer';
		
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
		$is_print_anywhere = $this->input->post('is_print_anywhere');
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('printer_name' => 'ASC');
		}
		
		if(!empty($searching)){
			$params['where'][] = "(printer_name LIKE '%".$searching."%' OR printer_ip LIKE '%".$searching."%')";
		}
		
		if(!empty($is_print_anywhere)){
			$params['where'][] = "is_print_anywhere = 1";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				$s['is_print_anywhere_text'] = ($s['is_print_anywhere'] == '1') ? '<span style="color:green;">Ya</span>':'<span style="color:red;">Tidak</span>';
				$s['print_logo_text'] = ($s['print_logo'] == '1') ? '<span style="color:green;">Ya</span>':'<span style="color:red;">Tidak</span>';
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		
		$this->prefix_apps = config_item('db_prefix');
		
		$this->table = $this->prefix.'printer';				
		$session_user = $this->session->userdata('user_username');
		
		$printer_name = $this->input->post('printer_name');
		$printer_ip = $this->input->post('printer_ip');
		$printer_tipe = $this->input->post('printer_tipe');
		$printer_pin = $this->input->post('printer_pin');
		$is_print_anywhere = $this->input->post('is_print_anywhere');
		$print_method = $this->input->post('print_method');
		$print_logo = $this->input->post('print_logo');
		
		if(empty($printer_name)){
			$r = array('success' => false, 'info' => 'Printer Name cannot empty!');
			die(json_encode($r));
		}		
		
		if(empty($printer_ip)){
			$r = array('success' => false, 'info' => 'Printer IP cannot empty!');
			die(json_encode($r));
		}		
		
		if(empty($printer_tipe)){
			$r = array('success' => false, 'info' => 'Select printer tipe!');
			die(json_encode($r));
		}			
		
		if(empty($printer_pin)){
			$r = array('success' => false, 'info' => 'Select printer PIN!');
			die(json_encode($r));
		}		
		
		$print_method = $this->input->post('print_method');
		if(empty($print_method)){
			$print_method = 'ESC/POS';
		}
		
		$print_logo = $this->input->post('print_logo');
		if(empty($print_logo)){
			$print_logo = 0;
		}
		
		$is_print_anywhere = $this->input->post('is_print_anywhere');
		if(empty($is_print_anywhere)){
			$is_print_anywhere = 0;
		}	
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
			
		$r = '';
		if($this->input->post('form_type_masterPrinter', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'printer_name'  => 	$printer_name,
				    'printer_ip'  	=> 	$printer_ip,
				    'printer_tipe'  => 	$printer_tipe,
				    'printer_pin'  	=> 	$printer_pin,
				    'print_method'  => 	$print_method,
				    'print_logo'  	=> 	$print_logo,
				    'is_print_anywhere'  	=> 	$is_print_anywhere,
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
		if($this->input->post('form_type_masterPrinter', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'printer_name'  => 	$printer_name,
				    'printer_ip'  	=> 	$printer_ip,
				    'printer_tipe'  => 	$printer_tipe,
				    'printer_pin'  	=> 	$printer_pin,
				    'print_method'  => 	$print_method,
				    'print_logo'  	=> 	$print_logo,
				    'is_print_anywhere'  	=> 	$is_print_anywhere,
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
				
				//cek di options
				$get_all_varname = array();
				$this->db->from($this->prefix_apps."options");
				$this->db->where("option_var LIKE 'printer_id_%' AND option_value = '".$id."'");
				$get_dt = $this->db->get();
				if($get_dt->num_rows() > 0){
					foreach($get_dt->result() as $dt){
						$varname = str_replace("printer_id_", "", $dt->option_var);
						if(!in_array($varname, $get_all_varname)){
							$get_all_varname[] = $varname;
						}
					}
				}
				
				
				if(!empty($get_all_varname)){
					$all_varname = '';
					foreach($get_all_varname as $dtVar){
						if($all_varname == ''){
							$all_varname = "option_var LIKE '%".$dtVar."%'";
						}else{
							$all_varname .= " OR option_var LIKE '%".$dtVar."%'";
						}
					}
					
					$all_update = array();
					if(!empty($all_varname)){
						$this->db->from($this->prefix_apps."options");
						$this->db->where($all_varname);
						$get_dt = $this->db->get();
						if($get_dt->num_rows() > 0){
							foreach($get_dt->result() as $dt){
								$option_var = explode("_", $dt->option_var);
								
								$option_varname = '';
								if(!empty($option_var[0]) AND !empty($option_var[1])){
									$option_varname = $option_var[0].'_'.$option_var[1];
								}
								
								if($option_varname == 'printer_ip'){
									$all_update[] = array(
										'id'			=> $dt->id,
										'option_value'	=> $printer_ip
									);
								}
								
								if($option_varname == 'printer_pin'){
									$all_update[] = array(
										'id'			=> $dt->id,
										'option_value'	=> $printer_pin
									);
								}
								
								if($option_varname == 'printer_tipe'){
									$all_update[] = array(
										'id'			=> $dt->id,
										'option_value'	=> $printer_tipe
									);
								}
								
							}
						}
					}
					
					
					if(!empty($all_update)){
						
						$this->db->update_batch($this->prefix_apps."options", $all_update,"id");
						//$r['update'] = $all_update;
					}
					
				}
				
				
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
		$this->table = $this->prefix.'printer';
		
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
            $r = array('success' => false, 'info' => 'Delete Printer Failed!'); 
        }
		die(json_encode($r));
	}
	
}