<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class VoucherList extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_voucherlist', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'discount_voucher';
		
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
		$sales_type = $this->input->post('sales_type');
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$show_all_text = $this->input->post('show_all_text');
		$discount_id = $this->input->post('discount_id');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('id' => 'DESC');
		}
		
		if(!empty($discount_id)){
			$params['where'][] = "discount_id = ".$discount_id;
		}
		
		if(!empty($searching)){
			$params['where'][] = "(voucher_no LIKE '%".$searching."%')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($is_dropdown)){
			
			$show_txt = '-- NO VOUCHER --';
			if(!empty($show_all_text)){
				$show_txt = '-- ALL VOUCHER --';
			}
			
			$s = array(
				'id'			=> 0,
				'discount_id'	=> 	0,
				'voucher_no'	=> $show_txt,		
				'voucher_status'=> 0,		
				'date_used'		=> '',		
				'is_active'		=> 0	
			);
			array_push($newData, $s);
		}
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				$s['voucher_status_text'] = ($s['voucher_status'] == '1') ? '<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>';
					
				if(!empty($s['date_used']) AND $s['date_used'] != '0000-00-00'){
					$s['date_used'] = date("d-m-Y", strtotime($s['date_used']));
				}else{
					$s['date_used'] = '';
				}
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'discount_voucher';				
		$session_user = $this->session->userdata('user_username');
		
		$discount_id = $this->input->post('discount_id');
		$voucher_no = $this->input->post('voucher_no');
		$date_used = $this->input->post('date_used');
		$ref_billing_no = $this->input->post('ref_billing_no');
		
		if(empty($discount_id) OR empty($voucher_no)){
			$r = array('success' => false);
			die(json_encode($r));
		}	
		
		$voucher_status = $this->input->post('voucher_status');
		if(empty($voucher_status)){
			$voucher_status = 0;
		}
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
		//UPDATE
		$id = $this->input->post('id', true);	
			
		$r = '';
		if($this->input->post('form_type_voucherList', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'discount_id' 	=> 	$discount_id,
				    'voucher_no' 	=> $voucher_no,
				    'voucher_status'=> $voucher_status,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active
				),
				'table'		=>  $this->table
			);	
			
			if(!empty($date_used)){
				$var['fields']['date_used'] = $date_used;
			}
			if(!empty($ref_billing_no)){
				$var['fields']['ref_billing_no'] = $ref_billing_no;
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
		if($this->input->post('form_type_voucherList', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'voucher_no' 	=> $voucher_no,
				    'voucher_status'=> $voucher_status,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			if(!empty($date_used)){
				$var['fields']['date_used'] = $date_used;
			}
			
			if(!empty($ref_billing_no)){
				$var['fields']['ref_billing_no'] = $ref_billing_no;
			}
			
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
		$this->table = $this->prefix.'discount_voucher';
		
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
            $r = array('success' => false, 'info' => 'Delete Voucher Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function verifyVoucher()
	{
		$this->table = $this->prefix.'discount_voucher';
		
		$voucher_no = $this->input->post('voucher_no', true);		
		$billing_no = $this->input->post('billing_no', true);		
		$tipe = $this->input->post('tipe', true);		
		
		$this->db->select("a.*, b.discount_name, b.discount_percentage, b.discount_price, 
		b.min_total_billing, b.discount_max_price, b.discount_type");
		$this->db->from($this->table.' as a');
		$this->db->join($this->prefix.'discount as b',"b.id = a.discount_id", "LEFT");
		$this->db->where("a.voucher_no = '".$voucher_no."'");
		$get_dt = $this->db->get();
		$r = '';
		if($get_dt->num_rows() > 0)  
        {  
			$dt_voucher = $get_dt->row();
			if($dt_voucher->voucher_status == 1 AND $billing_no != $dt_voucher->ref_billing_no){
				$r = array('success' => false, 'info' => 'Voucher sudah digunakan pada billing: '.$dt_voucher->ref_billing_no); 
			}else
			if($dt_voucher->is_active == 0){
				$r = array('success' => false, 'info' => 'Voucher tidak aktif'); 
			}else
			{
				if($tipe == 'item'){
					if($dt_voucher->discount_type == 1){
						$r = array('success' => false, 'info' => 'Voucher berlaku untuk Voucher Per-Billing!'); 
					}else
					if($dt_voucher->discount_type == 2){
						$r = array('success' => false, 'info' => 'Voucher berlaku untuk Buy &amp; Get!'); 
					}else{
						$r = array('success' => true, 'data'=> $dt_voucher);
					}
				}
				
				if($tipe == 'billing'){
					if($dt_voucher->discount_type == 0){
						$r = array('success' => false, 'info' => 'Voucher berlaku untuk Voucher Per-Item!'); 
					}else
					if($dt_voucher->discount_type == 2){
						$r = array('success' => false, 'info' => 'Voucher berlaku untuk Buy &amp; Get!'); 
					}else{
						$r = array('success' => true, 'data'=> $dt_voucher);
					}
				}
				
				if($tipe == 'buyget'){
					if($dt_voucher->discount_type == 0){
						$r = array('success' => false, 'info' => 'Voucher berlaku untuk Voucher Per-Item!'); 
					}else
					if($dt_voucher->discount_type == 1){
						$r = array('success' => false, 'info' => 'Voucher berlaku untuk Voucher Per-Billing!'); 
					}else{
						$r = array('success' => true, 'data'=> $dt_voucher);
					}
				}
				
				
			}
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Verify Voucher Failed!'); 
        }
		die(json_encode($r));
	}
	
}