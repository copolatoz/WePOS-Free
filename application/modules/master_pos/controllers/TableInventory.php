<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class TableInventory extends MY_Controller {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_tableInventory', 'm');		
	}

	public function gridData()
	{
		$this->table = $this->prefix.'table_inventory';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'b.is_active',
			'room_name' => 'c.room_name',
			'floorplan_name' => 'd.floorplan_name'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, b.room_id, b.floorplan_id, b.table_name, b.table_no, b.kapasitas, c.room_name, c.room_no, d.floorplan_name",
			'primary_key'	=> 'a.id',
			'table' => $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'table as b','b.id = a.table_id','LEFT'),
										array($this->prefix.'room as c','c.id = b.room_id','LEFT'),
										array($this->prefix.'floorplan as d','d.id = b.floorplan_id','LEFT')
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
		$show_all_text = $this->input->post('show_all_text');
		$show_choose_text = $this->input->post('show_choose_text');
		$keywords = $this->input->post('keywords');
		
		if(!empty($keywords)){
			$searching = $keywords;
		}
		if(!empty($is_dropdown)){
			$params['order'] = array('table_no' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.table_name LIKE '%".$searching."%' OR b.table_no LIKE '%".$searching."%' OR  d.floorplan_name LIKE '%".$searching."%' OR c.room_name LIKE '%".$searching."%'  OR c.room_no LIKE '%".$searching."%' )";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		 		
  		$newData = array();		
		
		if(!empty($show_all_text)){
			$dt = array('id' => '-1', 'table_name' => 'Choose All Table');
			array_push($newData, $dt);
		}else{
			if(!empty($show_choose_text)){
				$dt = array('id' => '', 'table_name' => 'Choose Table');
				array_push($newData, $dt);
			}
		}
		
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
		$this->table = $this->prefix.'table_inventory';				
		$session_user = $this->session->userdata('user_username');
		$table_id = $this->input->post('table_id');		
		$tanggal_start = $this->input->post('tanggal_start');
		$tanggal_end = $this->input->post('tanggal_end');
		$status = $this->input->post('status');
		
		if(empty($table_id) OR empty($status) OR empty($tanggal_start) OR empty($tanggal_end)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		$tanggal_start = date("Y-m-d", strtotime($tanggal_start));
		$tanggal_end = date("Y-m-d", strtotime($tanggal_end));
		
		$mk_tanggal_start = strtotime($tanggal_start." 00:00:00");
		$mk_tanggal_end = strtotime($tanggal_end." 23:59:59");
		//total days in range
		$total_days = ceil(($mk_tanggal_end - $mk_tanggal_start) / ONE_DAY_UNIX);
			
		$is_active = 0;
		if(!empty($_POST['is_active'])){
			$is_active = 1;
		}
			
		//CHECK INV DATA + RANGE
		$this->db->select("*");
		$this->db->from($this->table);
		$this->db->where("tanggal >= '".$tanggal_start."' AND tanggal <= '".$tanggal_end."'");
		
		if($table_id > 0){
			$this->db->where("table_id = '".$table_id."'");
		}
		$dt_inv_range = $this->db->get();
		
		$collecting_data = array();
		//collecting old data
		if($dt_inv_range->num_rows() > 0){
			foreach($dt_inv_range->result() as $dt){
				if(empty($collecting_data[$dt->table_id])){
					$collecting_data[$dt->table_id] = array();
				}
				$mk_tanggal = strtotime($dt->tanggal." 00:00:00");
				
				if(empty($collecting_data[$dt->table_id][$mk_tanggal])){
					$collecting_data[$dt->table_id][$mk_tanggal] = $dt->id;
				}else{
					$del_collecting_data[] = $dt->id;
				}
			}
		}
		
		//re-check within range / table
		$dt_table = array();
		if($table_id == 0 OR $table_id == -1){
			$this->db->select("*");
			$this->db->from($this->prefix."table");
			$get_table = $this->db->get();
			if($get_table->num_rows() > 0){
				foreach($get_table->result() as $dt){
					$dt_table[] = $dt->id;
				}
			}
		}else{
			$dt_table[] = $table_id;
		}
			
		//echo '<pre>';
		//print_r($total_days);
		//die();
		
		$tgl_add_update = date("Y-m-d H:i:s");
		
		$r = '';
		if($this->input->post('form_type_tableInventory', true) == 'add')
		{	
			//loop - range
			$all_ready_insert = array();
			for($i = 0; $i < $total_days; $i++){
				foreach($dt_table as $tbl_id){
				
					$mk_tanggal = $mk_tanggal_start + (ONE_DAY_UNIX*$i);
					$tanggal = date("Y-m-d H:i:s", $mk_tanggal);
					//check to collecting db
					//echo $i.' -> '.$tbl_id.' - '.$mk_tanggal."<br/>";
					if(empty($collecting_data[$tbl_id][$mk_tanggal])){
						$all_ready_insert[] = array(
												'table_id' => 	$tbl_id,
												'tanggal' => 	$tanggal,
												'status' 	=> 	$status,
												'created'		=>	$tgl_add_update,
												'createdby'		=>	$session_user,
												'updated'		=>	$tgl_add_update,
												'updatedby'		=>	$session_user,
												'is_active'		=>	$is_active
											);
					}else{
					
						$getDt = $collecting_data[$tbl_id][$mk_tanggal];
						$all_ready_update[] = array(
												'id' 	=> 	$getDt,
												'table_id' => 	$tbl_id,
												'tanggal' => 	$tanggal,
												'status' 	=> 	$status,
												'updated'		=>	$tgl_add_update,
												'updatedby'		=>	$session_user,
												'is_active'		=>	$is_active
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
		if($this->input->post('form_type_tableInventory', true) == 'edit'){
		
			//loop - range
			$all_ready_insert = array();
			$all_ready_update = array();
			for($i = 0; $i < $total_days; $i++){
				foreach($dt_table as $tbl_id){
				
					$mk_tanggal = $mk_tanggal_start + (ONE_DAY_UNIX*$i);
					$tanggal = date("Y-m-d H:i:s", $mk_tanggal);
					//check to collecting db
					//echo $i.' -> '.$tbl_id.' - '.$mk_tanggal."<br/>";
					if(empty($collecting_data[$tbl_id][$mk_tanggal])){
						$all_ready_insert[] = array(
												'table_id' => 	$tbl_id,
												'tanggal' => 	$tanggal,
												'status' 	=> 	$status,
												'created'		=>	$tgl_add_update,
												'createdby'		=>	$session_user,
												'updated'		=>	$tgl_add_update,
												'updatedby'		=>	$session_user,
												'is_active'		=>	$is_active
											);
					}else{
					
						$getDt = $collecting_data[$tbl_id][$mk_tanggal];
						$all_ready_update[] = array(
												'id' 	=> 	$getDt,
												'table_id' => 	$tbl_id,
												'tanggal' => 	$tanggal,
												'status' 	=> 	$status,
												'updated'		=>	$tgl_add_update,
												'updatedby'		=>	$session_user,
												'is_active'		=>	$is_active
											);
					}
					
				}
			}
						
			$insert_id = true;			
			if(!empty($all_ready_insert)){
				$insert_id = false;
				$this->lib_trans->begin();
				
					//INSERT BATCH
					$insert_id = $this->db->insert_batch($this->table, $all_ready_insert);
					
				$this->lib_trans->commit();	
			}	
			
			/*print_r($collecting_data);
			echo '<br>';
			echo count($all_ready_insert);
			echo '<pre>';
			print_r($all_ready_insert);
			echo '<br>';
			echo count($all_ready_update);
			echo '<pre>';
			print_r($all_ready_update);
			die();*/
			
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
		$this->table = $this->prefix.'table_inventory';
		
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
            $r = array('success' => false, 'info' => 'Delete Table Inventory Failed!'); 
        }
		die(json_encode($r));
	}
	
}