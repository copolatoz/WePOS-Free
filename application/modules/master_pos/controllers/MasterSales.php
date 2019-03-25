<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MasterSales extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_mastersales', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'sales';
		
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
		$show_valid_date = $this->input->post('show_valid_date');
		$show_all_text = $this->input->post('show_all_text');
		$keywords = $this->input->post('keywords');
		
		if(!empty($keywords)){
			$searching = $keywords;
		}
		if(!empty($is_dropdown)){
			$params['order'] = array('sales_name' => 'ASC');
		}
		
		if(!empty($sales_type)){
			
			if($sales_type == -1){
				
			}else{
				$params['where'][] = "sales_type = ".$sales_type;
			}
			
		}
		
		
		if(!empty($searching)){
			$params['where'][] = "(sales_name LIKE '%".$searching."%' OR sales_code LIKE '%".$searching."%')";
		}
		if(!empty($show_valid_date)){
			$today_date = date("Y-m-d H:i:s");
			$params['where'][] = "(sales_contract_type = 'unlimited_date' OR (sales_contract_type = 'limited_date' AND ('".$today_date."' BETWEEN date_start AND date_end)))";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($is_dropdown)){
			
			$show_txt = '-- NO SALES --';
			if(!empty($show_all_text)){
				$show_txt = '-- ALL SALES --';
			}
			
			$s = array(
				'id'	=> 0,
				'sales_name'	=> 	$show_txt,
				'sales_name_company_fee'	=> $show_txt,		
				'sales_price'	=> 0,		
				'sales_percentage'	=> 0,		
				'sales_type'	=> ''		
			);
			array_push($newData, $s);
		}
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				if($s['sales_type'] == 'before_tax'){
					$s['sales_type_text'] = 'Before Tax'; 
					$sales_type_simple = 'B';
				}else{
					$s['sales_type_text'] = 'After Tax'; 
					$sales_type_simple = 'A';
				}
					
					
				if(!empty($s['date_start']) AND $s['date_start'] != '0000-00-00 00:00:00'){
					$s['date_start'] = date("d-m-Y", strtotime($s['date_start']));
				}else{
					$s['date_start'] = '';
				}
				
				if(!empty($s['date_end']) AND $s['date_end'] != '0000-00-00 00:00:00'){
					$s['date_end'] = date("d-m-Y", strtotime($s['date_end']));
				}else{
					$s['date_end'] = '';
				}
				
				$jenis_fee = '';
				if(!empty($s['sales_percentage'])){
					$jenis_fee = $s['sales_percentage'].'%';
				}else{
					$jenis_fee = $s['sales_price'];
				}
				
				$s['sales_name_company_fee'] = $s['sales_name'].' / '.$s['sales_company'].' ('.$sales_type_simple.' '.$jenis_fee.')';
				
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
		$this->table = $this->prefix.'sales';				
		$session_user = $this->session->userdata('user_username');
		
		$sales_code = $this->input->post('sales_code');
		$sales_name = $this->input->post('sales_name');
		$sales_percentage = $this->input->post('sales_percentage');
		$sales_price = $this->input->post('sales_price');
		$sales_type = $this->input->post('sales_type');
		$sales_contract_type = $this->input->post('sales_contract_type');
		$sales_phone = $this->input->post('sales_phone');
		$sales_email = $this->input->post('sales_email');
		$sales_address = $this->input->post('sales_address');
		$sales_company = $this->input->post('sales_company');
		$date_start = $this->input->post('date_start');
		$date_end = $this->input->post('date_end');
		
		if(empty($sales_name)){
			$r = array('success' => false);
			die(json_encode($r));
		}	
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
		if(empty($use_sales_time)){
			$use_sales_time = 0;
		}
		
		$date_start = $date_start.' 00:00:00';
		$date_end = $date_end.' 23:59:59';
		
		//UPDATE
		$id = $this->input->post('id', true);
			

		//CHECK CODE
		if(!empty($sales_code)){
			$id = $this->input->post('id', true);
			$this->db->from($this->table);
			$this->db->where("sales_code = '".$sales_code."'");
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
				$get_code = $this->generate_sales_code($sales_code);
				$sales_code = $get_code['sales_code'];
				$sales_no = $get_code['sales_no'];
			}	
		}else{
			$get_code = $this->generate_sales_code();
			$sales_code = $get_code['sales_code'];
			$sales_no = $get_code['sales_no'];
		}
			
		$r = '';
		if($this->input->post('form_type_masterSales', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'sales_code' => 	$sales_code,
				    'sales_name' => 	$sales_name,
				    'sales_percentage' => $sales_percentage,
				    'sales_price' => $sales_price,
				    'sales_type' => $sales_type,
				    'sales_contract_type' => $sales_contract_type,
				    'sales_phone' => $sales_phone,
				    'sales_email' => $sales_email,
					'sales_address'	=>	$sales_address,
					'sales_company'	=>	$sales_company,
					'date_start'	=>	$date_start,
					'date_end'		=>	$date_end,
				    'source_from'  	=> 	'MERCHANT',
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'	=>	$is_active
				),
				'table'		=>  $this->table
			);	
			
			if(!empty($sales_no)){
				$var['fields']['sales_no'] = $sales_no;
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
		if($this->input->post('form_type_masterSales', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'sales_code' => 	$sales_code,
				    'sales_name' => 	$sales_name,
				    'sales_percentage' => $sales_percentage,
				    'sales_price' => $sales_price,
				    'sales_type' => $sales_type,
				    'sales_contract_type' => $sales_contract_type,
				    'sales_phone' => $sales_phone,
				    'sales_email' => $sales_email,
					'sales_address'	=>	$sales_address,
					'sales_company'	=>	$sales_company,
					'date_start'	=>	$date_start,
					'date_end'		=>	$date_end,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			if(!empty($sales_no)){
				$var['fields']['sales_no'] = $sales_no;
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
		$this->table = $this->prefix.'sales';
		
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
            $r = array('success' => false, 'info' => 'Delete Master Sales Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function generate_sales_code($cek_code = ''){
		
		$this->table = $this->prefix.'sales';		

		$getDate = date("ym");
		
		$prefix_sales_code = 'SLS'.date("ym");
		$code_format = '{Sales}{SalesNo}';
		$no_length = 4;
		
		if(!empty($cek_code)){
			$get_sales_no = substr($cek_code, $no_length*-1);
			$sales_no = (int) $get_sales_no;
			return array('sales_no' => $sales_no, 'sales_code' => $cek_code);
		}
		
		$repl_attr = array(
			"{Sales}" => $prefix_sales_code,
		);
		
		$sales_code = strtr($code_format, $repl_attr);
		//$sales_code = $prefix_sales_code.'0001';
		
		$this->db->from($this->table);
		$this->db->where("sales_code LIKE '".$prefix_sales_code."%'");
		$this->db->where("is_deleted = 0");
		$this->db->order_by('sales_no', 'DESC');
		$this->db->order_by('sales_code', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_sales_code = $get_last->row();
			
			$length_code = strlen($prefix_sales_code);
			$get_sales_no = substr($data_sales_code->sales_code, $length_code, $no_length);
			$sales_no = (int) $get_sales_no;
		
			if(!empty($data_sales_code->sales_no)){
				$sales_no = $data_sales_code->sales_no;
			}		
			
		}else{
			$sales_no = 0;
		}
		
		$sales_no++;
		
		$sales_no_add = $sales_no;
		$length_no = strlen($sales_no);
		if($length_no <= $no_length){
			$gapTxt = $no_length - $length_no;
			$sales_no_add = str_repeat("0", $gapTxt).$sales_no;
		}
		
		$repl_attr = array(
			"{SalesNo}"		=> $sales_no_add
		);
		
		$sales_code = strtr($sales_code, $repl_attr);
		
		return array('sales_no' => $sales_no, 'sales_code' => $sales_code);				
	}
	
}