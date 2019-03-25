<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MasterSupplier extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_mastersupplier', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'supplier';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'is_active',
			'supplier_status_text' => 'supplier_status'
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
		$show_all_text = $this->input->post('show_all_text');
		$show_choose_text = $this->input->post('show_choose_text');
		$supplier_id = $this->input->post('supplier_id');
		$validated = $this->input->post('validated');
		$keywords = $this->input->post('keywords');
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if(!empty($validated)){
			//get supplier
			$all_supp = array();
			$this->db->select("DISTINCT(supplier_id)");
			$this->db->from($this->prefix.'ro_detail');
			$this->db->where('ro_detail_status','validated');
			$get_supp = $this->db->get();
			if($get_supp->num_rows() > 0){
				foreach($get_supp->result() as $dt){
					if(!in_array($dt->supplier_id, $all_supp)){
						$all_supp[] = $dt->supplier_id;
					}
				}
			}
			
			if(!empty($all_supp)){
				$all_supp_txt = implode(",", $all_supp);
				$params['where'][] = "id IN (".$all_supp_txt.")";
			}
		}else{		
			if(!empty($supplier_id)){
				$params['where'][] = "id = ".$supplier_id."";
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('supplier_name' => 'ASC');
		}		
		if(!empty($searching)){
			$params['where'][] = "(supplier_name LIKE '%".$searching."%' OR supplier_code LIKE '%".$searching."%')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($show_all_text)){
			$dt = array('id' => '0', 'supplier_name' => 'Choose All Supplier');
			array_push($newData, $dt);
		}else{
			if(!empty($show_choose_text)){
				$dt = array('id' => '0', 'supplier_name' => 'Choose Supplier');
				array_push($newData, $dt);
			}
		}
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				if($s['supplier_status'] == 'ok'){
					$s['supplier_status_text'] = '<span style="color:green;font-weight:bold;">OK</span>';
				}else
				if($s['supplier_status'] == 'warning'){
					$s['supplier_status_text'] = '<span style="color:orange;font-weight:bold;">Warning</span>';
				}else
				{
					$s['supplier_status_text'] = '<span style="color:red;font-weight:bold;">Blacklist</span>';
				}
				$s['source_from'] = ucwords($s['source_from']);
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'supplier';				
		$session_user = $this->session->userdata('user_username');
		
		$supplier_name = $this->input->post('supplier_name');
		$supplier_code = $this->input->post('supplier_code');
		$supplier_contact_person = $this->input->post('supplier_contact_person');
		$supplier_address = $this->input->post('supplier_address');
		$supplier_phone = $this->input->post('supplier_phone');
		$supplier_fax = $this->input->post('supplier_fax');
		$supplier_email = $this->input->post('supplier_email');
		$supplier_status = $this->input->post('supplier_status');
		$keterangan_blacklist = $this->input->post('keterangan_blacklist');
		
		if(empty($supplier_name)){
			$r = array('success' => false);
			die(json_encode($r));
		}			
		
		if(empty($supplier_status)){
			$supplier_status = 'ok';
		}		
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
		//CHECK CODE
		if(!empty($supplier_code)){
			$id = $this->input->post('id', true);
			$this->db->from($this->table);
			$this->db->where("supplier_code = '".$supplier_code."'");
			if(!empty($id)){
				$this->db->where("id != ".$id);
			}
			$this->db->where("is_deleted = 0");
			$get_last = $this->db->get();
			if($get_last->num_rows() > 0){
				
				//available
				$r = array('success' => false, 'info' => 'Kode sudah digunakan!'); 
				die(json_encode($r));
		
			}else{
				$get_code = $this->generate_supplier_code($supplier_code);
				$supplier_code = $get_code['supplier_code'];
				$supplier_no = $get_code['supplier_no'];
			}
		}else{
			$get_code = $this->generate_supplier_code();
			$supplier_code = $get_code['supplier_code'];
			$supplier_no = $get_code['supplier_no'];
		}
			
		$r = '';
		if($this->input->post('form_type_masterSupplier', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'supplier_code'  	=> 	$supplier_code,
				    'supplier_name'  	=> 	$supplier_name,
				    'supplier_contact_person'  	=> 	$supplier_contact_person,
				    'supplier_address'  => 	$supplier_address,
				    'supplier_phone'  	=> 	$supplier_phone,
				    'supplier_fax'  	=> 	$supplier_fax,
				    'supplier_email'  	=> 	$supplier_email,
				    'supplier_status'  	=> 	$supplier_status,
				    'keterangan_blacklist'  	=> 	$keterangan_blacklist,
					'source_from'  	=> 	'MERCHANT',
				    'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'	=>	$is_active
				),
				'table'		=>  $this->table
			);		
			
			if(!empty($supplier_no)){
				$var['fields']['supplier_no'] = $supplier_no;
			}
			
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
		if($this->input->post('form_type_masterSupplier', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'supplier_code'  	=> 	$supplier_code,
				    'supplier_name'  	=> 	$supplier_name,
				    'supplier_contact_person'  	=> 	$supplier_contact_person,
				    'supplier_address'  => 	$supplier_address,
				    'supplier_phone'  	=> 	$supplier_phone,
				    'supplier_fax'  	=> 	$supplier_fax,
				    'supplier_email'  	=> 	$supplier_email,
				    'supplier_status'  	=> 	$supplier_status,
				    'keterangan_blacklist'  	=> 	$keterangan_blacklist,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			if(!empty($supplier_no)){
				$var['fields']['supplier_no'] = $supplier_no;
			}
			
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
		$this->table = $this->prefix.'supplier';
		
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
            $r = array('success' => false, 'info' => 'Delete Supplier Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function print_masterSupplier(){
		
		$this->table = $this->prefix.'supplier';
		$data_post['table'] = $this->table;
		$do = '';
		
		extract($_GET);

		$this->db->from($this->table);
		$this->db->where("is_deleted = 0");
		$this->db->order_by("supplier_name","ASC");
		$get_supplier = $this->db->get();
		
		$data_supplier = array();
		if($get_supplier->num_rows() > 0){
			$data_supplier = $get_supplier->result();
		}
		
		$data_post['do'] = $do;
		$data_post['data_supplier'] = $data_supplier;
		$data_post['report_name'] = 'DATA SUPPLIER';
		
		if($do == 'excel'){
			$this->load->view('../../master_pos/views/excel_masterSupplier', $data_post);
		}else{
			$this->load->view('../../master_pos/views/print_masterSupplier', $data_post);
		}
		
		
	}
	
	public function generate_supplier_code($cek_code = ''){
		
		$this->table = $this->prefix.'supplier';		

		$getDate = date("ym");
		
		$prefix_supplier_code = 'SPL'.date("ym");
		$code_format = '{Supplier}{SupplierNo}';
		$no_length = 4;
		
		if(!empty($cek_code)){
			$get_supplier_no = substr($cek_code, $no_length*-1);
			$supplier_no = (int) $get_supplier_no;
			return array('supplier_no' => $supplier_no, 'supplier_code' => $cek_code);
		}
		
		$repl_attr = array(
			"{Supplier}" => $prefix_supplier_code,
		);
		
		$supplier_code = strtr($code_format, $repl_attr);
		//$supplier_code = $prefix_supplier_code.'0001';
		
		$this->db->from($this->table);
		$this->db->where("supplier_code LIKE '".$prefix_supplier_code."%'");
		$this->db->where("is_deleted = 0");
		$this->db->order_by('supplier_no', 'DESC');
		$this->db->order_by('supplier_code', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_supplier_code = $get_last->row();
			
			$length_code = strlen($prefix_supplier_code);
			$get_supplier_no = substr($data_supplier_code->supplier_code, $length_code, $no_length);
			$supplier_no = (int) $get_supplier_no;
		
			if(!empty($data_supplier_code->supplier_no)){
				$supplier_no = $data_supplier_code->supplier_no;
			}		
			
		}else{
			$supplier_no = 0;
		}
		
		$supplier_no++;
		
		$supplier_no_add = $supplier_no;
		$length_no = strlen($supplier_no);
		if($length_no <= $no_length){
			$gapTxt = $no_length - $length_no;
			$supplier_no_add = str_repeat("0", $gapTxt).$supplier_no;
		}
		
		$repl_attr = array(
			"{SupplierNo}"		=> $supplier_no_add
		);
		
		$supplier_code = strtr($supplier_code, $repl_attr);
		
		return array('supplier_no' => $supplier_no, 'supplier_code' => $supplier_code);				
	}
	
}