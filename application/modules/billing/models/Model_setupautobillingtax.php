<?php
class Model_setupautobillingtax extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'nontrx_target';
	}
	
	public function addUpdate($data_post = array())
	{
		$this->table = $this->prefix.'nontrx_target';				
		$session_user = $this->session->userdata('user_username');
		
		$nontrx_tahun = $this->input->post('nontrx_tahun');
		$nontrx_bulan = $this->input->post('nontrx_bulan');
		$nontrx_bulan_text = $this->input->post('nontrx_bulan_text');
		$nontrx_range_sales_from = $this->input->post('nontrx_range_sales_from');
		$nontrx_range_sales_till = $this->input->post('nontrx_range_sales_till');
		$nontrx_range_jam_from = $this->input->post('nontrx_range_jam_from');
		$nontrx_range_jam_till = $this->input->post('nontrx_range_jam_till');
		$nontrx_bulan_target = $this->input->post('nontrx_bulan_target');
		$nontrx_minggu_target = $this->input->post('nontrx_minggu_target');
		$nontrx_hari_target = $this->input->post('nontrx_hari_target');
		
		$is_default = $this->input->post('is_default');
		if(empty($is_default)){
			$is_default = 0;
		}
		
		//update-2003.001
		$form_type_setupAutoBillingTax = $this->input->post('form_type_setupAutoBillingTax', true);
		$id = $this->input->post('id', true);
		
		//check setup
		$this->db->select("*");
		$this->db->from($this->table);
		$this->db->where("nontrx_tahun = '".$nontrx_tahun."'");
		$this->db->where("nontrx_bulan = '".$nontrx_bulan."'");
		if(!empty($id)){
			$this->db->where("id != '".$id."'");
		}
		$get_other = $this->db->get();
		if($get_other->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Set Auto: '.$nontrx_bulan_text.' '.$nontrx_tahun.' sudah ada!');
			die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
		}
		
		$r = '';
		if($form_type_setupAutoBillingTax == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'nontrx_tahun'  	=> 	$nontrx_tahun,
				    'nontrx_bulan'  	=> 	$nontrx_bulan,
				    'nontrx_range_sales_from' => $nontrx_range_sales_from,
				    'nontrx_range_sales_till'  => 	$nontrx_range_sales_till,
				    'nontrx_bulan_target'  	=> 	$nontrx_bulan_target,
				    'nontrx_minggu_target'  	=> 	$nontrx_minggu_target,
				    'nontrx_hari_target'  	=> 	$nontrx_hari_target,
				    'nontrx_range_jam_from'  	=> 	$nontrx_range_jam_from,
				    'nontrx_range_jam_till'  	=> 	$nontrx_range_jam_till,
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
				$r = array('success' => false, 'info' => 'Simpan data gagal');
			}
      		
		}else
		if($form_type_setupAutoBillingTax == 'edit'){
			$var = array('fields'	=>	array(
				    'nontrx_tahun'  	=> 	$nontrx_tahun,
				    'nontrx_bulan'  	=> 	$nontrx_bulan,
				    'nontrx_range_sales_from' => $nontrx_range_sales_from,
				    'nontrx_range_sales_till'  => 	$nontrx_range_sales_till,
				    'nontrx_bulan_target'  	=> 	$nontrx_bulan_target,
				    'nontrx_minggu_target'  	=> 	$nontrx_minggu_target,
				    'nontrx_hari_target'  	=> 	$nontrx_hari_target,
				    'nontrx_range_jam_from'  	=> 	$nontrx_range_jam_from,
				    'nontrx_range_jam_till'  	=> 	$nontrx_range_jam_till,
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
				$r = array('success' => false, 'info' => 'update data gagal');
			}
		}
		
		if(!empty($is_default)){
			
			//update default
			$var_default = $var['fields'];
			$this->db->update($this->table,$var_default,"is_default = 1");
			
			
			
		}
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}

} 