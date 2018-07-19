<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class PrinterAccess extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix');
		$this->load->model('model_printeraccess', 'm');
	}

	public function gridData()
	{
		
		$this->prefix_pos = config_item('db_prefix2');
		
		$this->table = $this->prefix.'options';
		$this->table_printer = $this->prefix_pos.'printer';
		
		
		$keywords = $this->input->post('keywords');

		$all_printer_name = array();
		$this->db->select("*");
		$this->db->from($this->table_printer);
		
		if(!empty($keywords)){
			$this->db->where("(printer_name LIKE '%".$keywords."%' OR printer_tipe LIKE '%".$keywords."%' OR printer_pin LIKE '%".$keywords."%')");
		}
		
		$all_printer = $this->db->get();
		if($all_printer->num_rows() > 0){
			foreach($all_printer->result() as $dt){
				$all_printer_name[$dt->id] = $dt->printer_name; 
			}
		}
		
		
		$this->db->select("*");
		$this->db->from($this->table);
		$this->db->where("(option_var LIKE '%reservationReceipt%' OR option_var LIKE '%qcReceipt%' OR option_var LIKE '%barReceipt%' OR option_var LIKE '%kitchenReceipt%' OR option_var LIKE '%cashierReceipt%' OR option_var LIKE '%otherReceipt%')");
		
		
		$this->db->order_by("id", "DESC");
		$data_access = $this->db->get();
		
		$allow_var = array("qcReceipt","barReceipt","kitchenReceipt","cashierReceipt","otherReceipt","reservationReceipt");
		
		$allow_index = array(
			'id',
			'old_user_ip',
			'old_var_name',
			'user_ip',
			'var_name',
			'do_print',
			'printer_id',
			'printer_name',
			'printer_pin',
			'printer_tipe',
			'printer_ip',
			'print',
			'tipe_printer',
			'printer'
		);
		
		$not_found = array();
		$data_group = array();
		if($data_access->num_rows() > 0){
			foreach($data_access->result() as $dt){
				
				$exp_var = explode("_", $dt->option_var);
				
				if(count($exp_var) > 3){
					$exp_var[0] = $exp_var[0]."_".$exp_var[1];
					$exp_var[1] = $exp_var[2];
					$exp_var[2] = $exp_var[3];
				}
				
				if(in_array($exp_var[1], $allow_var)){
					if(!empty($exp_var[1]) AND !empty($exp_var[2])){
						if(empty($data_group[$exp_var[1]."_".$exp_var[2]])){
							$data_group[$exp_var[1]."_".$exp_var[2]] = array(
								'id'			=> $exp_var[1]."_".$exp_var[2],
								'old_user_ip'	=> $exp_var[2],
								'old_var_name'	=> $exp_var[1],
								'user_ip'	=> $exp_var[2],
								'var_name'	=> $exp_var[1],
								'do_print'		=> '',
								'printer_id'	=> '',
								'printer_name'	=> '',
								'printer_pin'	=> '',
								'printer_tipe'	=> '',
								'printer_ip'	=> ''
							);
						}
					}
					
					if(!empty($exp_var[0])){
						if(in_array($exp_var[0], $allow_index)){
							
							
							if($exp_var[0] == 'printer_id'){
								
								if(!empty($all_printer_name[$dt->option_value])){
									$data_group[$exp_var[1]."_".$exp_var[2]]['printer_name'] = $all_printer_name[$dt->option_value];
									$data_group[$exp_var[1]."_".$exp_var[2]]['printer_id'] = $dt->option_value;
								}else{
									if(!in_array($exp_var[1]."_".$exp_var[2], $not_found)){
										$not_found[] = $exp_var[1]."_".$exp_var[2];
									}
								}
								
							}else
							if($exp_var[0] == 'tipe_printer'){
								$data_group[$exp_var[1]."_".$exp_var[2]]['printer_pin'] = $dt->option_value;
								//print_r($dt); die();
							}else
							if($exp_var[0] == 'print'){
								$data_group[$exp_var[1]."_".$exp_var[2]]['do_print'] = $dt->option_value;
							}else
							if($exp_var[0] == 'printer'){
								$data_group[$exp_var[1]."_".$exp_var[2]]['printer_ip'] = $dt->option_value;
							}else{
								
								$data_group[$exp_var[1]."_".$exp_var[2]][$exp_var[0]] = $dt->option_value;
							}
							
						}
					}
				}
				
				
				
			}
		}
		
		//echo '<pre>';
		//print_r($data_group);
		//die();
		
  		
  		$newData = array();
		
		if(!empty($data_group)){
			foreach ($data_group as $key => $s){
				
				$s['do_print_text'] = ($s['do_print'] == '1') ? '<span style="color:green;">Ya</span>':'<span style="color:red;">Tidak</span>';
				
				if(!in_array($key, $not_found)){
					array_push($newData, $s);
				}
				
			}
		}
		
		$get_data['data'] = $newData;
		$get_data['totalCount'] = count($newData);
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'options';				
		$session_user = $this->session->userdata('user_username');
		
		$user_ip = $this->input->post('user_ip');
		$var_name = $this->input->post('var_name');
		$old_user_ip = $this->input->post('old_user_ip');
		$old_var_name = $this->input->post('old_var_name');
		$printer_name = $this->input->post('printer_name');
		$printer_id = $this->input->post('printer_id');
		$printer_ip = $this->input->post('printer_ip');
		$printer_tipe = $this->input->post('printer_tipe');
		$printer_pin = $this->input->post('printer_pin');
		
		if(empty($user_ip)){
			$r = array('success' => false, 'info' => 'User/PC IP cannot empty!');
			die(json_encode($r));
		}		
			
		
		if(empty($var_name)){
			$r = array('success' => false, 'info' => 'Select Tipe!');
			die(json_encode($r));
		}		
			
		
		if(empty($printer_id)){
			$r = array('success' => false, 'info' => 'Select Printer!');
			die(json_encode($r));
		}		
		
		if(empty($printer_ip)){
			$r = array('success' => false, 'info' => 'Printer IP cannot empty!');
			die(json_encode($r));
		}		
		
		if(empty($printer_tipe)){
			$r = array('success' => false, 'info' => 'Printer Tipe cannot empty!');
			die(json_encode($r));
		}		
		
		if(empty($printer_pin)){
			$r = array('success' => false, 'info' => 'Printer PIN cannot empty!');
			die(json_encode($r));
		}		
		
		
		$do_print = $this->input->post('do_print');
		if(empty($do_print)){
			$do_print = 0;
		}
		
		
		$all_data_option = array();
		
		$all_data_option[] = array(
			'option_var'	=> 	'do_print_'.$var_name.'_'.$user_ip,
			'option_value'	=> 	$do_print,
			'created'		=>	date('Y-m-d H:i:s'),
			'createdby'		=>	$session_user,
			'updated'		=>	date('Y-m-d H:i:s'),
			'updatedby'		=>	$session_user,
			'is_active'		=>	1
		);
		
		$all_data_option[] = array(
			'option_var'	=> 	'printer_tipe_'.$var_name.'_'.$user_ip,
			'option_value'	=> 	$printer_tipe,
			'created'		=>	date('Y-m-d H:i:s'),
			'createdby'		=>	$session_user,
			'updated'		=>	date('Y-m-d H:i:s'),
			'updatedby'		=>	$session_user,
			'is_active'		=>	1
		);
		
		$all_data_option[] = array(
			'option_var'	=> 	'printer_pin_'.$var_name.'_'.$user_ip,
			'option_value'	=> 	$printer_pin,
			'created'		=>	date('Y-m-d H:i:s'),
			'createdby'		=>	$session_user,
			'updated'		=>	date('Y-m-d H:i:s'),
			'updatedby'		=>	$session_user,
			'is_active'		=>	1
		);
		
		$all_data_option[] = array(
			'option_var'	=> 	'printer_ip_'.$var_name.'_'.$user_ip,
			'option_value'	=> 	$printer_ip,
			'created'		=>	date('Y-m-d H:i:s'),
			'createdby'		=>	$session_user,
			'updated'		=>	date('Y-m-d H:i:s'),
			'updatedby'		=>	$session_user,
			'is_active'		=>	1
		);
		
		$all_data_option[] = array(
			'option_var'	=> 	'printer_id_'.$var_name.'_'.$user_ip,
			'option_value'	=> 	$printer_id,
			'created'		=>	date('Y-m-d H:i:s'),
			'createdby'		=>	$session_user,
			'updated'		=>	date('Y-m-d H:i:s'),
			'updatedby'		=>	$session_user,
			'is_active'		=>	1
		);
		
		//REMOVE CURRENT OPTIONS
		$this->db->delete($this->table, "option_var LIKE '%".$var_name.'_'.$user_ip."%'");
		
		if(!empty($old_user_ip) AND !empty($old_var_name)){
			$this->db->delete($this->table, "option_var LIKE '%".$old_var_name.'_'.$old_user_ip."%'");
		}
		
		$r = '';
		
		$q = $this->db->insert_batch($this->table, $all_data_option);
		
		if($q)
		{  
			$r = array('success' => true); 				
		}  
		else
		{  
			$r = array('success' => false);
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function delete()
	{
		$this->table = $this->prefix.'options';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		$sql_Id_txt = '';
		if(is_array($id)){
			//$sql_Id = implode(',', $id);
			foreach($id as $sql_Id){
				if($sql_Id_txt == ''){
					$sql_Id_txt = "option_var LIKE '%".$sql_Id."%'";
				}else{
					$sql_Id_txt .= " OR option_var LIKE '%".$sql_Id."%'"; 
				}
			}
			
			
		}
		
		//Delete
		$q = $this->db->delete($this->table, $sql_Id_txt);
		
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
	
}