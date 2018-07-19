<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class WarehouseAccess extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_warehouseaccess', 'm');
	}

	public function gridData()
	{
		
		$this->prefix_apps = config_item('db_prefix');
		
		$this->table = $this->prefix.'storehouse_users';
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'a.is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, b.storehouse_name, c.user_username',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'storehouse as b','b.id = a.storehouse_id','LEFT'),
										array($this->prefix_apps.'users as c','c.id = a.user_id', 'LEFT')
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
		$keywords = $this->input->post('keywords');
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.id' => 'ASC');
		}
		
		if(!empty($searching)){
			$params['where'][] = "(b.storehouse_name LIKE '%".$searching."%' OR c.user_username LIKE '%".$searching."%')";
		}
		
		$params['where'][] = "c.id != 0";
		$params['where'][] = "c.is_deleted = 0";
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				$s['is_retail_warehouse_text'] = ($s['is_retail_warehouse'] == '1') ? '<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>';
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'storehouse_users';				
		$session_user = $this->session->userdata('user_username');
		
		$storehouse_id = $this->input->post('storehouse_id');
		$user_id = $this->input->post('user_id');
		$user_username = $this->input->post('user_username');
		
		if(empty($storehouse_id)){
			$r = array('success' => false, 'info' => 'Select Warehouse!');
			die(json_encode($r));
		}		
			
		
		if(empty($user_id)){
			$r = array('success' => false, 'info' => 'User not found!');
			die(json_encode($r));
		}		
		
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
		
		$is_retail_warehouse = $this->input->post('is_retail_warehouse');
		if(empty($is_retail_warehouse)){
			$is_retail_warehouse = 0;
		}
		
		$id = $this->input->post('id', true);
		
		//check jika sudah ada
		$this->db->select("*");
		$this->db->from($this->table);
		$this->db->where("storehouse_id", $storehouse_id);
		$this->db->where("user_id", $user_id);
		
		if(!empty($id)){
			$this->db->where("id != ".$id);
		}
		
		$this->db->where("is_deleted", 0);
		$get_data = $this->db->get();
		if($get_data->num_rows() > 0){
			
			$r = array('success' => false, 'info' => 'User with Warehouse availabe!');
			die(json_encode($r));
			
		}
		
		$r = '';
		if($this->input->post('form_type_warehouseAccess', true) == 'add')
		{	
			
			$var = array(
				'fields'	=>	array(
				    'user_id' 		=> $user_id,
				    'storehouse_id'  	=> 	$storehouse_id,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'	=>	$is_active,
					'is_retail_warehouse'	=>	$is_retail_warehouse
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
				if(!empty($is_retail_warehouse)){
					$update_is_retal_wh = array(
						'is_retail_warehouse'	=> 0
					);
					$this->db->update($this->table,$update_is_retal_wh,"user_id = ".$user_id." AND id != ".$insert_id);
				}
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_warehouseAccess', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'user_id' => $user_id,
				    'storehouse_id'  	=> 	$storehouse_id,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'		=>	$is_active,
					'is_retail_warehouse'		=>	$is_retail_warehouse
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			//UPDATE
			$id = $this->input->post('id', true);
			$this->lib_trans->begin();
				$update = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				$r = array('success' => true, 'id' => $id); 	
				if(!empty($is_retail_warehouse)){
					$update_is_retal_wh = array(
						'is_retail_warehouse'	=> 0
					);
					$this->db->update($this->table,$update_is_retal_wh,"user_id = ".$user_id." AND id != ".$id);
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
		$this->table = $this->prefix.'storehouse_users';
		
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
		
		/*$data_update = array(
			"is_deleted" => 1
		);
		$q = $this->db->update($this->table, $data_update, "id IN (".$sql_Id.")");
		*/
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Access Failed!'); 
        }
		die(json_encode($r));
	}
	
}