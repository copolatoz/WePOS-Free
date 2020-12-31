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
			'is_active_text' => 'a.is_active',
			'customer_status_text' => 'a.customer_status',
			'limit_kredit_show' => 'a.limit_kredit'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, b.sales_code, b.sales_name, b.sales_price, b.sales_percentage, b.sales_type',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array(  
										array($this->prefix.'sales as b','b.id = a.sales_id','LEFT')
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
		$searching = $this->input->post('query');
		$show_valid_date = $this->input->post('show_valid_date');
		$show_all_text = $this->input->post('show_all_text');
		$keywords = $this->input->post('keywords');
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.customer_name' => 'ASC');
		}
		
		if(!empty($searching)){
			$params['where'][] = "(a.customer_name LIKE '%".$searching."%' OR a.customer_email LIKE '%".$searching."%' OR a.customer_code LIKE '%".$searching."%')";
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
				$s['limit_kredit_show'] = priceFormat($s['limit_kredit'],0);
				
				$s['sales_code_name'] = '';
				if(!empty($s['sales_id'])){
					$s['sales_code_name'] = $s['sales_code'].' / '.$s['sales_name'];
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
		$r = $this->m->addUpdate();
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
		$this->db->order_by("customer_code","ASC");
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