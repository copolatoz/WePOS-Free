<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class OooMenu extends MY_Controller {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_oooMenu', 'm');		
	}

	public function gridData()
	{
		$this->table = $this->prefix.'ooo_menu';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'b.is_active',
			'product_name' => 'b.product_name',
			'category_name' => 'c.product_category_name'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, b.product_name, c.product_category_name as category_name",
			'primary_key'	=> 'a.id',
			'table' => $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'product as b','b.id = a.product_id','LEFT'),
										array($this->prefix.'product_category as c','c.id = b.category_id','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		

		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$keywords = $this->input->post('keywords');
		
		if(!empty($keywords)){
			$searching = $keywords;
		}
		if(!empty($is_dropdown)){
			$params['order'] = array('table_no' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.product_name LIKE '%".$searching."%' OR c.product_category_name LIKE '%".$searching."%')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		 		
  		$newData = array();		
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'ooo_menu';				
		$session_user = $this->session->userdata('user_username');
		$product_id = $this->input->post('product_id');		
		$tanggal_start = $this->input->post('tanggal_start');
		$tanggal_end = $this->input->post('tanggal_end');
		$keterangan = $this->input->post('keterangan');
		$ooo_id = $this->input->post('id');
		
		if(empty($product_id) OR empty($keterangan) OR empty($tanggal_start) OR empty($tanggal_end)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		$tanggal_start = date("Y-m-d", strtotime($tanggal_start));
		$tanggal_end = date("Y-m-d", strtotime($tanggal_end));
		
		$mk_tanggal_start = strtotime($tanggal_start." 00:00:00");
		$mk_tanggal_end = strtotime($tanggal_end." 23:59:59");
		//total days in range
		$total_days = ceil(($mk_tanggal_end - $mk_tanggal_start) / ONE_DAY_UNIX);
			
		//$is_active = 0;
		//if(!empty($_POST['is_active'])){
		//	$is_active = 1;
		//}
			
		//CHECK INV DATA + RANGE
		$this->db->select("*");
		$this->db->from($this->table);
		
		if($product_id > 0){
			$this->db->where("(tanggal >= '".$tanggal_start."' AND tanggal <= '".$tanggal_end."' AND product_id = '".$product_id."')");
		}else{
			$this->db->where("(tanggal >= '".$tanggal_start."' AND tanggal <= '".$tanggal_end."')");
		}
		
		if($ooo_id > 0){
			$this->db->or_where("(id = '".$ooo_id."')");
		}
		
		$dt_inv_range = $this->db->get();
		
		$collecting_data = array();
		//collecting old data
		if($dt_inv_range->num_rows() > 0){
			foreach($dt_inv_range->result() as $dt){
				
				if($product_id > 0){
					$dt->product_id = $product_id;
				}
				
				if(empty($collecting_data[$dt->product_id])){
					$collecting_data[$dt->product_id] = array();
				}
				$mk_tanggal = strtotime($dt->tanggal." 00:00:00");
				
				if(empty($collecting_data[$dt->product_id][$mk_tanggal])){
					$collecting_data[$dt->product_id][$mk_tanggal] = $dt->id;
				}else{
					$del_collecting_data[] = $dt->id;
				}
			}
		}
		
		//re-check within range / table
		$dt_product = array();
		if($product_id == 0 OR $product_id == -1){
			$this->db->select("*");
			$this->db->from($this->prefix."product");
			$get_table = $this->db->get();
			if($get_table->num_rows() > 0){
				foreach($get_table->result() as $dt){
					$dt_product[] = $dt->id;
				}
			}
		}else{
			$dt_product[] = $product_id;
		}
			
		//echo '<pre>';
		//print_r($total_days);
		//die();
		
		$tgl_add_update = date("Y-m-d H:i:s");
		
		$r = '';
		if($this->input->post('form_type_oooMenu', true) == 'add')
		{	
			//loop - range
			$all_ready_insert = array();
			for($i = 0; $i < $total_days; $i++){
				foreach($dt_product as $prod_id){
				
					$mk_tanggal = $mk_tanggal_start + (ONE_DAY_UNIX*$i);
					$tanggal = date("Y-m-d", $mk_tanggal);
					//check to collecting db
					//echo $i.' -> '.$prod_id.' - '.$mk_tanggal."<br/>";
					if(empty($collecting_data[$prod_id][$mk_tanggal])){
						$all_ready_insert[] = array(
												'product_id' 	=> 	$prod_id,
												'tanggal' 		=> 	$tanggal,
												'keterangan' 	=> 	$keterangan,
												'created'		=>	$tgl_add_update,
												'createdby'		=>	$session_user,
												'updated'		=>	$tgl_add_update,
												'updatedby'		=>	$session_user,
												//'is_active'		=>	$is_active
											);
					}else{
					
						$getDt = $collecting_data[$prod_id][$mk_tanggal];
						$all_ready_update[] = array(
												'id' 			=> 	$getDt,
												'product_id' 	=> 	$prod_id,
												'tanggal' 		=> 	$tanggal,
												'keterangan' 	=> 	$keterangan,
												'updated'		=>	$tgl_add_update,
												'updatedby'		=>	$session_user,
												//'is_active'		=>	$is_active
											);
					}
					
				}
			}
			
			/*print_r($collecting_data);
			echo '<br>';
			echo count($all_ready_insert);
			echo '<pre>';
			print_r($all_ready_insert);
			die();*/
						
			//SAVE
			$insert_id = true;			
			if(!empty($all_ready_insert)){
				$insert_id = false;
				$this->lib_trans->begin();
				
					//INSERT BATCH
					$insert_id = $this->db->insert_batch($this->table, $all_ready_insert);
					
				$this->lib_trans->commit();	
			}
			
			if(!empty($all_ready_update)){
				$update_id = false;
				$this->lib_trans->begin();
					
					//UPDATE BATCH
					$update_id = $this->db->update_batch($this->table, $all_ready_update, "id");
					
				$this->lib_trans->commit();	
			}
			
			if($insert_id)
			{  
				$r = array('success' => true, 'total_insert' => count($all_ready_insert)); 
					
				if(!empty($del_collecting_data)){
					$del_collecting_data_sql = implode(",", $del_collecting_data);
					$this->db->delete($this->table, "id IN (".$del_collecting_data_sql.")");
				}
				
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_oooMenu', true) == 'edit'){
		
			//loop - range
			$all_ready_insert = array();
			$all_ready_update = array();
			for($i = 0; $i < $total_days; $i++){
				foreach($dt_product as $prod_id){
				
					$mk_tanggal = $mk_tanggal_start + (ONE_DAY_UNIX*$i);
					$tanggal = date("Y-m-d", $mk_tanggal);
					//check to collecting db
					//echo $i.' -> '.$prod_id.' - '.$mk_tanggal."<br/>";
					if(empty($collecting_data[$prod_id][$mk_tanggal])){
						$all_ready_insert[] = array(
												'product_id' 	=> 	$prod_id,
												'tanggal' 		=> 	$tanggal,
												'keterangan' 	=> 	$keterangan,
												'created'		=>	$tgl_add_update,
												'createdby'		=>	$session_user,
												'updated'		=>	$tgl_add_update,
												'updatedby'		=>	$session_user,
												//'is_active'		=>	$is_active
											);
					}else{
					
						$getDt = $collecting_data[$prod_id][$mk_tanggal];
						$all_ready_update[] = array(
												'id' 			=> 	$getDt,
												'product_id' 	=> 	$prod_id,
												'tanggal' 		=> 	$tanggal,
												'keterangan' 	=> 	$keterangan,
												'updated'		=>	$tgl_add_update,
												'updatedby'		=>	$session_user,
												//'is_active'		=>	$is_active
											);
					}
					
				}
			}
				
			/*
			echo 'collecting_data<pre>';
			print_r($collecting_data);
			echo '<br/>all_ready_insert<pre>';
			echo count($all_ready_insert);
			print_r($all_ready_insert);
			//echo '<br>';
			//echo count($all_ready_update);
			echo '<br/>all_ready_update<pre>';
			print_r($all_ready_update);
			die();
			*/
					
			$insert_id = true;			
			if(!empty($all_ready_insert)){
				$insert_id = false;
				$this->lib_trans->begin();
				
					//INSERT BATCH
					$insert_id = $this->db->insert_batch($this->table, $all_ready_insert);
					
				$this->lib_trans->commit();	
			}	
			
			$update_id = true;			
			if(!empty($all_ready_update)){
				$update_id = false;
				$this->lib_trans->begin();
				
					//UPDATE BATCH
					$update_id = $this->db->update_batch($this->table, $all_ready_update, "id");
					
				$this->lib_trans->commit();	
			}
			
			
			if($update_id OR $insert_id)
			{  
				$r = array('success' => true, 'total_insert' => count($all_ready_insert), 'total_update' => count($all_ready_update));
				if(!empty($del_collecting_data)){
					$del_collecting_data_sql = implode(",", $del_collecting_data);
					$this->db->delete($this->table, "id IN (".$del_collecting_data_sql.")");
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
		$this->table = $this->prefix.'ooo_menu';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
		
		//Delete
		$this->db->where("id IN (".$sql_Id.")");
		$q = $this->db->delete($this->table);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Out Of Order Menu Failed!'); 
        }
		die(json_encode($r));
	}
	
}