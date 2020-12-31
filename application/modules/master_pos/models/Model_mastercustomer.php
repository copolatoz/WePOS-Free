<?php
class Model_mastercustomer extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'customer';
	}
	
	public function addUpdate($data_post = array())
	{
		$this->table = $this->prefix.'customer';				
		$session_user = $this->session->userdata('user_username');
		
		$customer_name = $this->input->post('customer_name');
		$customer_code = $this->input->post('customer_code');
		if($customer_code == '- AUTO -'){
			$customer_code = '';
		}
		$customer_contact_person = $this->input->post('customer_contact_person');
		$customer_address = $this->input->post('customer_address');
		$customer_city = $this->input->post('customer_city');
		$customer_phone = $this->input->post('customer_phone');
		$customer_email = $this->input->post('customer_email');
		$customer_status = $this->input->post('customer_status');
		$keterangan_blacklist = $this->input->post('keterangan_blacklist');
		$limit_kredit = $this->input->post('limit_kredit');
		$termin = $this->input->post('termin');
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
		//update-2011.001
		$link_customer_dan_sales = $this->input->post('link_customer_dan_sales');
		$sales_id = $this->input->post('sales_id');
		if(empty($sales_id)){
			$sales_id = 0;
		}
		
		if(!empty($link_customer_dan_sales) AND empty($sales_id)){
			$r = array('success' => false, 'info' => 'Pilih sales!');
			die(json_encode($r));
		}
		
		//update-2003.001
		$form_type_masterCustomer = $this->input->post('form_type_masterCustomer', true);
		$id = $this->input->post('id', true);
		$is_from_email = false;
		
		if(!empty($data_post)){
			$customer_name = $data_post['nama'];
			$customer_email = $data_post['email'];
			$customer_phone = $data_post['phone'];
			$is_from_email = $data_post['from_email'];
			
			if(empty($customer_name)){
				$customer_name_exp = explode("@", $customer_email);
				if(!empty($customer_name_exp[0])){
					$customer_name = str_replace("."," ",$customer_name_exp[0]);
				}
			}
			
			$form_type_masterCustomer = 'add';
			
			//cek with email
			$this->db->from($this->table);
			$this->db->where("source_from = 'MERCHANT'");
			$this->db->where("customer_email = '".$customer_email."'");
			$get_cust_email = $this->db->get();
			if($get_cust_email->num_rows() > 0){
				$data_customer_email = $get_cust_email->row();
				$id = $data_customer_email->id;
				$customer_code = $data_customer_email->customer_code;
				$form_type_masterCustomer = 'edit';
			}
			
		}
		
		if(empty($customer_name)){
			$r = array('success' => false, 'info' => 'Nama Customer Harus diisi');
			
			if(!empty($is_from_email)){
				return $r;
			}
			die(json_encode($r));
		}		
		
		if(empty($customer_status)){
			$customer_status = 'ok';
		}		
		
		//CHECK CODE
		if(!empty($customer_code)){
			
			$this->db->from($this->table);
			$this->db->where("customer_code = '".$customer_code."'");
			if(!empty($id)){
				$this->db->where("id != ".$id);
			}
			//$this->db->where("is_deleted = 0");
			$get_last = $this->db->get();
			if($get_last->num_rows() > 0){
				//available
				$r = array('success' => false, 'info' => 'Kode sudah digunakan!'); 
				if(!empty($is_from_email)){
					return $r;
				}
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
		if($form_type_masterCustomer == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'customer_code'  	=> 	$customer_code,
				    'customer_name'  	=> 	$customer_name,
				    'customer_contact_person' => $customer_contact_person,
				    'customer_address'  => 	$customer_address,
				    'customer_city'  	=> 	$customer_city,
				    'customer_phone'  	=> 	$customer_phone,
				    'customer_email'  	=> 	$customer_email,
				    'customer_status'  	=> 	$customer_status,
				    'keterangan_blacklist'  => 	$keterangan_blacklist,
				    'source_from'  	=> 	'MERCHANT',
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active,
					'limit_kredit'	=>	$limit_kredit,
					'termin'		=>	$termin,
					'sales_id'		=>	$sales_id,
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
				$r = array('success' => false, 'info' => 'Simpan data customer gagal');
			}
      		
		}else
		if($form_type_masterCustomer == 'edit'){
			$var = array('fields'	=>	array(
				    'customer_code'  	=> 	$customer_code,
				    'customer_name'  	=> 	$customer_name,
				    'customer_contact_person'  => 	$customer_contact_person,
				    'customer_address'  => 	$customer_address,
				    'customer_city'  	=> 	$customer_city,
				    'customer_phone'  	=> 	$customer_phone,
				    'customer_email'  	=> 	$customer_email,
				    'customer_status'  	=> 	$customer_status,
				    'keterangan_blacklist'  	=> 	$keterangan_blacklist,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active,
					'limit_kredit'	=>	$limit_kredit,
					'termin'		=>	$termin,
					'sales_id'		=>	$sales_id,
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			if(!empty($customer_no)){
				$var['fields']['customer_no'] = $customer_no;
			}
			
			if($is_from_email == true){
				$var['fields'] = array(
					'customer_name'  	=> 	$customer_name,
					'customer_email'  	=> 	$customer_email,
					'customer_phone'  	=> 	$customer_phone,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_deleted'  		=> 	0,
					'is_active'  		=> 	1,
				);
			}
			
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
				$r = array('success' => false, 'info' => 'update data customer gagal');
			}
		}
		
		if(!empty($is_from_email)){
			return $r;
		}
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
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
		//$this->db->where("is_deleted = 0");
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