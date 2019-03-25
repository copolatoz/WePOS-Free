<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MasterCustomer extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_mastercustomer', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'customer';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'is_active',
			'customer_status_text' => 'customer_status'
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
		$show_valid_date = $this->input->post('show_valid_date');
		$show_all_text = $this->input->post('show_all_text');
		$keywords = $this->input->post('keywords');
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('customer_name' => 'ASC');
		}
		
		if(!empty($searching)){
			$params['where'][] = "(customer_name LIKE '%".$searching."%' OR customer_code LIKE '%".$searching."%')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($is_dropdown)){
			
			$show_txt = '-- NO CUSTOMER --';
			if(!empty($show_all_text)){
				$show_txt = '-- ALL CUSTOMER --';
			}
			
			$s = array(
				'id'	=> 0,
				'customer_name'	=> 	$show_txt,
				'customer_contact_person'	=> $show_txt,		
				'customer_price'	=> 0,		
				'customer_percentage'	=> 0,		
				'customer_type'	=> ''		
			);
			array_push($newData, $s);
		}
		
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				if($s['customer_status'] == 'ok'){
					$s['customer_status_text'] = '<span style="color:green;font-weight:bold;">OK</span>';
				}else
				if($s['customer_status'] == 'warning'){
					$s['customer_status_text'] = '<span style="color:orange;font-weight:bold;">Warning</span>';
				}else
				{
					$s['customer_status_text'] = '<span style="color:red;font-weight:bold;">Blacklist</span>';
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
		$this->table = $this->prefix.'customer';				
		$session_user = $this->session->userdata('user_username');
		
		$customer_name = $this->input->post('customer_name');
		$customer_code = $this->input->post('customer_code');
		$customer_contact_person = $this->input->post('customer_contact_person');
		$customer_address = $this->input->post('customer_address');
		$customer_phone = $this->input->post('customer_phone');
		$customer_email = $this->input->post('customer_email');
		$customer_status = $this->input->post('customer_status');
		$keterangan_blacklist = $this->input->post('keterangan_blacklist');
		
		if(empty($customer_name)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		if(empty($customer_status)){
			$customer_status = 'ok';
		}		
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
		//CHECK CODE
		if(!empty($customer_code)){
			$id = $this->input->post('id', true);
			$this->db->from($this->table);
			$this->db->where("customer_code = '".$customer_code."'");
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
				$get_code = $this->generate_customer_code($customer_code);
				$customer_code = $get_code['customer_code'];
				$customer_no = $get_code['customer_no'];
			}
		}else{
			$get_code = $this->generate_customer_code();
			$customer_code = $get_code['customer_code'];
			$customer_no = $get_code['customer_no'];
		}
		
		$r = '';
		if($this->input->post('form_type_masterCustomer', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'customer_code'  	=> 	$customer_code,
				    'customer_name'  	=> 	$customer_name,
				    'customer_contact_person'  => 	$customer_contact_person,
				    'customer_address'  => 	$customer_address,
				    'customer_phone'  	=> 	$customer_phone,
				    'customer_email'  	=> 	$customer_email,
				    'customer_status'  	=> 	$customer_status,
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
			
			if(!empty($customer_no)){
				$var['fields']['customer_no'] = $customer_no;
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
		if($this->input->post('form_type_masterCustomer', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'customer_code'  	=> 	$customer_code,
				    'customer_name'  	=> 	$customer_name,
				    'customer_contact_person'  => 	$customer_contact_person,
				    'customer_address'  => 	$customer_address,
				    'customer_phone'  	=> 	$customer_phone,
				    'customer_email'  	=> 	$customer_email,
				    'customer_status'  	=> 	$customer_status,
				    'keterangan_blacklist'  	=> 	$keterangan_blacklist,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			if(!empty($customer_no)){
				$var['fields']['customer_no'] = $customer_no;
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
		$this->table = $this->prefix.'customer';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
		
		//Delete
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
            $r = array('success' => false, 'info' => 'Delete Customer Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function print_masterCustomer(){
		
		$this->table = $this->prefix.'customer';
		$data_post['table'] = $this->table;
		$do = '';
		
		extract($_GET);

		$this->db->from($this->table);
		$this->db->where("is_deleted = 0");
		$this->db->order_by("customer_name","ASC");
		$get_customer = $this->db->get();
		
		$data_customer = array();
		if($get_customer->num_rows() > 0){
			$data_customer = $get_customer->result();
		}
		
		$data_post['do'] = $do;
		$data_post['data_customer'] = $data_customer;
		$data_post['report_name'] = 'DATA CUSTOMER';
		
		if($do == 'excel'){
			$this->load->view('../../master_pos/views/excel_masterCustomer', $data_post);
		}else{
			$this->load->view('../../master_pos/views/print_masterCustomer', $data_post);
		}
		
		
	}
	
	public function generate_customer_code($cek_code = ''){
		
		$this->table = $this->prefix.'customer';		

		$getDate = date("ym");
		
		$prefix_customer_code = 'CST'.date("ym");
		$code_format = '{Customer}{CustomerNo}';
		$no_length = 4;
		
		if(!empty($cek_code)){
			$get_customer_no = substr($cek_code, $no_length*-1);
			$customer_no = (int) $get_customer_no;
			return array('customer_no' => $customer_no, 'customer_code' => $cek_code);
		}
		
		$repl_attr = array(
			"{Customer}" => $prefix_customer_code,
		);
		
		$customer_code = strtr($code_format, $repl_attr);
		//$customer_code = $prefix_customer_code.'0001';
		
		$this->db->from($this->table);
		$this->db->where("customer_code LIKE '".$prefix_customer_code."%'");
		$this->db->where("is_deleted = 0");
		$this->db->order_by('customer_no', 'DESC');
		$this->db->order_by('customer_code', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_customer_code = $get_last->row();
			
			$length_code = strlen($prefix_customer_code);
			$get_customer_no = substr($data_customer_code->customer_code, $length_code, $no_length);
			$customer_no = (int) $get_customer_no;
		
			if(!empty($data_customer_code->customer_no)){
				$customer_no = $data_customer_code->customer_no;
			}		
			
		}else{
			$customer_no = 0;
		}
		
		$customer_no++;
		
		$customer_no_add = $customer_no;
		$length_no = strlen($customer_no);
		if($length_no <= $no_length){
			$gapTxt = $no_length - $length_no;
			$customer_no_add = str_repeat("0", $gapTxt).$customer_no;
		}
		
		$repl_attr = array(
			"{CustomerNo}"		=> $customer_no_add
		);
		
		$customer_code = strtr($customer_code, $repl_attr);
		
		return array('customer_no' => $customer_no, 'customer_code' => $customer_code);				
	}
	
}