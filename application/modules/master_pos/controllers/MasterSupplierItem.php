<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MasterSupplierItem extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_mastersupplieritem', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'supplier_item';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'is_active'
		);		
		
		$from_supplier_item = $this->input->post('from_supplier_item');
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$supplier_id = $this->input->post('supplier_id');
		$item_id = $this->input->post('item_id');
		$keywords = $this->input->post('keywords');
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if(!empty($from_supplier_item)){
			// Default Parameter
			$params = array(
				'fields'		=> "a.*, a.id as supplier_item_id, b.item_code, b.item_name, c.unit_name, d.supplier_name, d.supplier_address, 1 as from_supplier_item",
				'primary_key'	=> 'a.id',
				'table'			=> $this->table.' as a',
				'join'			=> array(
										'many', 
										array( 
											array($this->prefix.'items as b','a.item_id = b.id','LEFT'),
											array($this->prefix.'unit as c','a.unit_id = c.id','LEFT'),
											array($this->prefix.'supplier as d','a.supplier_id = d.id','LEFT')
										) 
									),
				'order'			=> array('b.item_name' => 'ASC'),
				'single'		=> false,
				'output'		=> 'array' //array, object, json
			);
			
			$params['where'][] = "b.is_active = 1";
			$params['where'][] = "b.is_deleted = 0";
			$params['where'][] = "a.is_deleted = 0";
			
			if(!empty($is_dropdown)){
				$params['order'] = array('d.supplier_name' => 'ASC');
			}
			if(!empty($supplier_id)){
				$params['where'][] = "a.supplier_id = ".$supplier_id."";
			}		
			if(!empty($item_id)){
				$params['where'][] = "a.item_id = ".$item_id."";
			}		
			if(!empty($searching)){
				$params['where'][] = "(d.supplier_name LIKE '%".$searching."%' OR b.item_name LIKE '%".$searching."%')";
			}
			
		}else{
			
			
			if(!empty($item_id)){
				//dari validasi RO
				// Default Parameter
				$params = array(
					'fields'		=> "a.*, a.id as supplier_item_id, b.item_code, b.item_name, c.unit_name, d.supplier_name, d.supplier_address, 0 as from_supplier_item",
					'primary_key'	=> 'a.id',
					'table'			=> $this->table.' as a',
					'join'			=> array(
											'many', 
											array( 
												array($this->prefix.'items as b','a.item_id = b.id','LEFT'),
												array($this->prefix.'unit as c','a.unit_id = c.id','LEFT'),
												array($this->prefix.'supplier as d','a.supplier_id = d.id','LEFT')
											) 
										),
					'order'			=> array('b.item_name' => 'ASC'),
					'single'		=> false,
					'output'		=> 'array' //array, object, json
				);
				
				if(!empty($is_dropdown)){
					$params['order'] = array('b.item_name' => 'ASC');
				}
				
				$params['where'][] = "a.id = ".$item_id."";
				
				if(!empty($searching)){
					$params['where'][] = "(b.item_name LIKE '%".$searching."%')";
				}
				
			}else{
				//dari PO
				// Default Parameter
				// Default Parameter
				$params = array(
					'fields'		=> "a.*, a.id as item_id, b.unit_name, '' as supplier_item_id, '' as supplier_name, '' as supplier_address, 0 as from_supplier_item",
					'primary_key'	=> 'a.id',
					'table'			=> $this->prefix.'items as a',
					'join'			=> array(
											'many', 
											array( 
												array($this->prefix.'unit as b','b.id = a.unit_id','LEFT')
											) 
										),
					'order'			=> array('a.item_name' => 'ASC'),
					'single'		=> false,
					'output'		=> 'array' //array, object, json
				);
				
				if(!empty($is_dropdown)){
					$params['order'] = array('a.item_name' => 'ASC');
				}
				
				if(!empty($searching)){
					$params['where'][] = "(a.item_name LIKE '%".$searching."%')";
				}
			}		
			
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($get_data['data'])){
			$no = 0;
			foreach ($get_data['data'] as $s){
				
				if($s['item_name'] != ''){
					$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
					
					$s['item_price_show'] = priceFormat($s['item_price']);
					$s['item_hpp_show'] = priceFormat($s['item_hpp']);
					$s['item_code_name'] = $s['item_code'].' / '.$s['item_name'];
					
					if(empty($s['last_in'])){
						$s['last_in'] = $s['item_hpp'];
					}
					
					$s['last_in_show'] = priceFormat($s['last_in']);
					
					if(empty($s['supplier_item_id'])){
						$no++;
						$s['supplier_item_id'] = 'x'.$no;
					}
					
					array_push($newData, $s);
				}
				
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'supplier_item';				
		$session_user = $this->session->userdata('user_username');
				
		$supplier_id = $this->input->post('supplier_id');
		$item_id = $this->input->post('item_id');
		$unit_id = $this->input->post('unit_id');
		$item_price = $this->input->post('item_price');
		$item_hpp = $this->input->post('item_hpp');		
		
		if(empty($item_id) OR empty($supplier_id)){
			$r = array('success' => false);
			die(json_encode($r));
		}
		
		//check supplier item
		$deleted_id = 0;
		$this->db->select('id');
		$this->db->from($this->table);
		$this->db->where('supplier_id', $supplier_id);
		$this->db->where('item_id', $item_id);
		$get_supplier_item = $this->db->get();
		if($get_supplier_item->num_rows() > 0){
			$old_item = $get_supplier_item->row();
			if($old_item->is_deleted = 1){
				$deleted_id = $old_item->id;
			}else{
				$r = array('success' => false, 'info'	=> "Item sudah ditambahkan dilist<br/>double click untuk ubah item");
				die(json_encode($r));
				die();
			}
		}
		
		$r = '';
		if($this->input->post('form_type_masterSupplierItem', true) == 'add' AND $deleted_id == 0)
		{	
			
			$var = array(
				'fields'	=>	array(
				    'supplier_id'  => 	$supplier_id,
				    'item_id'  => 	$item_id,
				    'unit_id'  => 	$unit_id,
					'item_price'	=>	$item_price,
					'item_hpp'	=>	$item_hpp,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_deleted'	=>	0
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
		if($this->input->post('form_type_masterSupplierItem', true) == 'edit' OR $deleted_id > 0){
			$var = array('fields'	=>	array(
				    'supplier_id'  => 	$supplier_id,
				    'item_id'  => 	$item_id,
				    'unit_id'  => 	$unit_id,
					'item_price'	=>	$item_price,
					'item_hpp'	=>	$item_hpp,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_deleted'	=>	0
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
								
			//UPDATE
			$id = $this->input->post('id', true);
			
			if(!empty($deleted_id)){
				$id = $deleted_id;
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
		$this->table = $this->prefix.'supplier_item';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
				
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
            $r = array('success' => false, 'info' => 'Delete Supplier Item Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function print_masterSupplierItem(){
		
		$this->table = $this->prefix.'supplier_item';
		$data_post['table'] = $this->table;
		$do = '';
		
		extract($_GET);

		if(empty($supplier_id)){
			$supplier_id = -1;
		}
		
		$this->db->select("a.*, a.id as supplier_item_id, b.item_name, c.unit_name, d.supplier_name, d.supplier_address");
		$this->db->from($this->table." as a");
		$this->db->join($this->prefix.'items as b','a.item_id = b.id','LEFT');
		$this->db->join($this->prefix.'unit as c','a.unit_id = c.id','LEFT');
		$this->db->join($this->prefix.'supplier as d','a.supplier_id = d.id','LEFT');
		$this->db->where("a.supplier_id = ".$supplier_id);
		$this->db->where("a.is_deleted = 0");
		$this->db->order_by("b.item_name","ASC");
		$get_supplieritem = $this->db->get();
		
		$data_supplieritem = array();
		$supplier_name = '';
		if($get_supplieritem->num_rows() > 0){
			foreach($get_supplieritem->result() as $dt){
				if($dt->item_name != ''){
					$data_supplieritem[] = $dt;
					if(empty($supplier_name)){
						$supplier_name = $dt->supplier_name;
					}
				}
			}
		}
		
		//echo '<re>';
		//print_r($data_supplieritem);
		//die();
		
		$data_post['do'] = $do;
		$data_post['data_supplieritem'] = $data_supplieritem;
		$data_post['report_name'] = 'DATA SUPPLIER ITEM';
		$data_post['supplier_name'] = $supplier_name;
		
		if($do == 'excel'){
			$this->load->view('../../master_pos/views/excel_masterSupplierItem', $data_post);
		}else{
			$this->load->view('../../master_pos/views/print_masterSupplierItem', $data_post);
		}
		
		
	}
	
}