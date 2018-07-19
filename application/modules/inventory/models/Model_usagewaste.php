<?php
class Model_usagewaste extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'usagewaste';
		$this->table_detail = $this->prefix.'usagewaste_detail';
		$this->table_storehouse_users = $this->prefix.'storehouse_users';
	}
	
	//update 2018-01-07
	function save_sales_usage($get_params){
		
		extract($get_params);
		
		$session_user = $this->session->userdata('user_username');
		$role_id = $this->session->userdata('role_id');
		$id_user = $this->session->userdata('id_user');
		
		if(empty($date_now)){
			$date_now = date("Y-m-d");
		}
		
		if(empty($retail_warehouse)){
			$this->db->from($this->table_storehouse_users);
			$this->db->where("user_id = ".$id_user." AND is_retail_warehouse = 1");
			$get_retail_warehouse = $this->db->get();
			if($get_retail_warehouse->num_rows() > 0){
				$dt_retail_warehouse = $get_retail_warehouse->row();
				$retail_warehouse = $dt_retail_warehouse->storehouse_id;
			}
		}
		
		if(empty($all_item_usage)){
			return false;
		}
		
		if(empty($rollback)){
			$rollback = false;
		}
		
		$dt_UW = array();
		
		//check_UW_sales
		$this->db->from($this->table);
		$this->db->where("uw_sales = 1 AND uw_date = '".$date_now."'");
		$get_UW = $this->db->get();
		if($get_UW->num_rows() > 0){
			$dt_UW = $get_UW->row_array();
		}
		
		if(empty($dt_UW)){
			$get_uw_number = $this->generate_uw_number();
			
			$dt_UW = array(
				'uw_number'  	=> 	$get_uw_number,
				'uw_date'  		=> 	$date_now,
				'uw_memo'  		=> 	'SALES USAGE STOCK',
				'uw_from'  		=> 	$retail_warehouse,
				'uw_status'  	=> 	'progress',
				'uw_sales'  	=> 	1,
				'created'		=>	date('Y-m-d H:i:s'),
				'createdby'		=>	$session_user,
				'updated'		=>	date('Y-m-d H:i:s'),
				'updatedby'		=>	$session_user
			);
			
			$this->db->insert($this->table, $dt_UW);
			$insert_id = $this->db->insert_id();
			$dt_UW['id'] = $insert_id;
		}
		
		$uw_det = array();
		$uw_det_delete = array();
		//get detail
		$this->db->from($this->table_detail);
		$this->db->where("uw_id = '".$dt_UW['id']."'");
		$get_UW_detail = $this->db->get();
		if($get_UW_detail->num_rows() > 0){
			foreach($get_UW_detail->result_array() as $uwd){
				
				if(empty($uw_det[$uwd['item_id']])){
					$uw_det[$uwd['item_id']] = $uwd;
					$uw_det[$uwd['item_id']]['uwd_qty'] = 0;
					$uw_det[$uwd['item_id']]['item_hpp'] = 0;
					$uw_det[$uwd['item_id']]['item_price'] = 0;
				}else{
					//delete item id
					$uw_det_delete[] = $uwd['id'];
				}
				
				$uw_det[$uwd['item_id']]['uwd_qty'] += $uwd['uwd_qty'];
				$uw_det[$uwd['item_id']]['item_hpp'] += $uwd['item_hpp'];
				$uw_det[$uwd['item_id']]['item_price'] += $uwd['item_price'];
				
			}
		}
		
		//add new
		$new_uwd = array();
		if(!empty($all_item_usage)){
			foreach($all_item_usage as $uw_new){
				
				if($rollback == true){
					
					if(!empty($uw_det[$uw_new['id']])){
						$uw_det[$uw_new['id']]['uwd_qty'] -= $uw_new['qty'];
						$uw_det[$uw_new['id']]['item_hpp'] -= $uw_new['item_hpp'];
						$uw_det[$uw_new['id']]['item_price'] -= $uw_new['item_price'];
					}
					
				}else{
					
					if(!empty($uw_det[$uw_new['id']])){
						$uw_det[$uw_new['id']]['uwd_qty'] += $uw_new['qty'];
						$uw_det[$uw_new['id']]['item_hpp'] += $uw_new['item_hpp'];
						$uw_det[$uw_new['id']]['item_price'] += $uw_new['item_price'];
					}else{
						//add new detail
						$new_uwd[] = array(
							'uw_id'		=> $dt_UW['id'],
							'item_id'	=> $uw_new['id'],
							'unit_id'	=> $uw_new['unit_id'],
							'item_hpp'	=> $uw_new['item_hpp'],
							'item_price'=> $uw_new['item_price'],
							'uwd_qty'	=> $uw_new['qty'],
							'uwd_status'=> 1,
							'uwd_tipe'	=> 'usage',
						);
					}
				}
				
				
			}
		}
		
		//add detail
		if(!empty($new_uwd)){
			$this->db->insert_batch($this->table_detail, $new_uwd);
		}
		
		//update detail
		if(!empty($uw_det)){
			$uw_det_new = array();
			foreach($uw_det as $key => $x){
				
				if($x['uwd_qty'] <= 0){
					//delete
					$uw_det_delete[] = $x['id'];
				}else{
					$uw_det_new[] = $x;
				}
				
			}
			$uw_det = $uw_det_new;
			
			if(!empty($uw_det)){
				$this->db->update_batch($this->table_detail, $uw_det, "id");
			}
		}
		
		//delete detail
		if(!empty($uw_det_delete)){
			$uw_det_delete_sql = implode(",", $uw_det_delete);
			$this->db->delete($this->table_detail, "id IN (".$uw_det_delete_sql.")");
		}
		
		
	}
	
	
	public function generate_uw_number(){
		$this->table = $this->prefix.'usagewaste';						
		
		$default_UW = "UW".date("ym");
		$this->db->from($this->table);
		$this->db->where("uw_number LIKE '".$default_UW."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_ro = $get_last->row();
			//$uw_number = $data_ro->uw_number;
			$uw_number = str_replace($default_UW,"", $data_ro->uw_number);
						
			$uw_number = (int) $uw_number;			
		}else{
			$uw_number = 0;
		}
		
		$uw_number++;
		$length_no = strlen($uw_number);
		switch ($length_no) {
			case 3:
				$uw_number = $uw_number;
				break;
			case 2:
				$uw_number = '0'.$uw_number;
				break;
			case 1:
				$uw_number = '00'.$uw_number;
				break;
			default:
				$uw_number = $uw_number;
				break;
		}
				
		return $default_UW.$uw_number;				
	}
	
} 