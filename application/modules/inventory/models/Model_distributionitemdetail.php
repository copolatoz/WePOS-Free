<?php
class Model_distributionitemdetail extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'distribution_detail';
	}
	
	function distributionDetail($distributionDetail = '', $dis_id = '', $update_stok = ''){
				
		$session_user = $this->session->userdata('user_username');
		
		$update_stock_item_unit = array();
		
		if(!empty($distributionDetail)){
			
			if(empty($dis_id)){
				$dis_id = -1;
				$dis_no = -1;
			}
			
			//insert batch
			$this->db->from($this->prefix.'distribution');
			$this->db->where("id", $dis_id);
			$get_rowguid = $this->db->get();
			if($get_rowguid->num_rows() > 0){
				$dt_rowguid = $get_rowguid->row_array();
				
				
				//DISTRIBUTION
				$dis_no = $dt_rowguid['dis_number'];
				$dis_date = $dt_rowguid['dis_date'];
				$delivery_from = $dt_rowguid['delivery_from'];
				$delivery_to = $dt_rowguid['delivery_to'];
					
					
			}
			
			$dtCurrent = array();
			$dtCurrent_disd_diterima = array();
			
			$this->db->from($this->prefix.'distribution_detail');
			$this->db->where("dis_id", $dis_id);
			$get_det = $this->db->get();
			if($get_det->num_rows() > 0){
				foreach($get_det->result() as $dt){
					if(!in_array($dt->id, $dtCurrent)){
						$dtCurrent[] = $dt->id;
						$dtCurrent_disd_diterima[$dt->id] = $dt->disd_diterima;
					}
				}
			}
			
			/*
			//GET CURRENT STOCK
			$all_item_id = array();
			if(!empty($dt_rowguid) AND !empty($distributionDetail)){
				foreach($distributionDetail as $dt){
					if(!in_array($dt['item_id'],$all_item_id)){
						$all_item_id[] = $dt['item_id'];
					}
				}
			}
			
			$all_stock_before_item = array();
			if(!empty($all_item_id)){
				//Get Stock Before
				$all_item_id_sql = implode(",", $all_item_id);
				$this->db->select("id,total_qty_stok");
				$this->db->from($this->prefix."items");
				$this->db->where("id IN (".$all_item_id_sql.")");
				$q_items = $this->db->get();
				if($q_items->num_rows() > 0){
					foreach($q_items->result() as $dt_items){
						if(empty($all_stock_before_item[$dt_items->id])){
							$all_stock_before_item[$dt_items->id] = $dt_items->total_qty_stok;
						}
					}
				}
			}*/
			
			$total_qty = 0;
			$dtNew = array();
			$dtUpdate_Items = array();
			$dtInsert_stock = array();
			$dtInsert = array();
			$dtUpdate = array();
			if(!empty($dt_rowguid)){
					
				foreach($distributionDetail as $dt){
					
					$disd_diterima_before = $dt['disd_diterima_before'];
					unset($dt['disd_diterima_before']);
					//unset($dt['item_price']);
					unset($dt['item_code']);
					unset($dt['item_code_name']);
					unset($dt['item_name']);
					unset($dt['unit_name']);
					unset($dt['dis_status_text']);
					unset($dt['nomor']);
										
					//$dt['current_stock'] = 0;
					//if(!empty($all_stock_before_item[$dt['item_id']])){
					//	$dt['current_stock'] = $all_stock_before_item[$dt['item_id']];
					//}
					
					//SURE ONLY UPDATE!
					if(($update_stok == 'update' OR $update_stok == 'rollback') AND !empty($dt['disd_diterima'])){
						//DELIVERY
						$delivery_trx_type = 'out';
						$delivery_trx_qty = $dt['disd_diterima'];
						$receiving_trx_type = 'in';
						$receiving_trx_qty = $dt['disd_diterima'];
						
						$total_qty += ($dt['disd_diterima']);
						
						if($update_stok == 'rollback'){
							$delivery_from_swap = $delivery_from;
							$delivery_to_swap = $delivery_to;
							$delivery_from = $delivery_to_swap;
							$delivery_to = $delivery_from_swap;
							unset($dt['disd_diterima']);
						}
						
						if(empty($update_stock_item_unit[$delivery_from])){
							$update_stock_item_unit[$delivery_from] = array();
						}
						
						if(empty($update_stock_item_unit[$delivery_to])){
							$update_stock_item_unit[$delivery_to] = array();
						}
						
						$update_stock_item_unit[$delivery_from][] = $dt['item_id'];
						$update_stock_item_unit[$delivery_to][] = $dt['item_id'];
						
						$dtInsert_stock[] = array(
							"item_id" => $dt['item_id'],
							"trx_date" => $dis_date,
							"trx_type" => $delivery_trx_type,
							"trx_qty" => $delivery_trx_qty,
							"unit_id" => $dt['unit_id'],
							"storehouse_id" => $delivery_from,
							"trx_nominal" => $dt['item_hpp'],
							"trx_note" => 'Distribution',
							"trx_ref_data" => $dis_no,
							"trx_ref_det_id" => $dt['id'],
							"is_active" => "1"
						);
						
						//RECEIVING
											
						$dtInsert_stock[] = array(
							"item_id" => $dt['item_id'],
							"trx_date" => $dis_date,
							"trx_type" => $receiving_trx_type,
							"trx_qty" => $receiving_trx_qty,
							"unit_id" => $dt['unit_id'],
							"storehouse_id" => $delivery_to,
							"trx_nominal" => $dt['item_hpp'],
							"trx_note" => 'Distribution',
							"trx_ref_data" => $dis_no,
							"trx_ref_det_id" => $dt['id'],
							"is_active" => "1"
						);
						
						
						//$dtUpdate_Items[] = array(
						//	"id" => $dt['item_id'],
						//	"total_qty_stok" => $dt['current_stock'] + ($dt['disd_diterima'] - $disd_diterima_before)
						//);
			
					}
					
					
					
					$dt['disd_status'] = 0;
					if($dt_rowguid['dis_status'] == 'done'){
						$dt['disd_status'] = 1;
					}
					
					//check if new
					if(strstr($dt['id'], 'new_')){
						unset($dt['id']);
					}
					
					$dt['dis_id'] = $dis_id;
					
					if(empty($dt['id'])){
						
						unset($dt['id']);						
						$dtInsert[] = $dt;
						
					}else{
					
						$dtUpdate[] = $dt;
						
						if(!in_array($dt['id'], $dtNew)){
							$dtNew[] = $dt['id'];
						}
					}
				}
			}
			
			//delete if not exist
			$dtDelete = array();
			if(!empty($dtNew)){
				foreach($dtCurrent as $dtR){
					if(!in_array($dtR, $dtNew)){
						$dtDelete[] = $dtR;
					}
				}
			}else{
				//delete all
				$dtDelete = $dtCurrent;
			}
			
			if(!empty($dtDelete)){
				$allRowguid = implode("','", $dtDelete);
				$this->db->where("id IN ('".$allRowguid."')");
				$this->db->delete($this->table); 
			}
			
			if(!empty($dtInsert)){
				$this->db->insert_batch($this->table, $dtInsert);
			}
			
			if(!empty($dtUpdate)){
				$this->db->update_batch($this->table, $dtUpdate, 'id');
			}
			
			if($update_stok == 'update' OR $update_stok == 'rollback'){
				
				if($update_stok == 'rollback'){
					//DELETE ALL STOCK
					$this->db->where("trx_ref_data", $dis_no);
					$this->db->delete($this->prefix."stock"); 
				}else{
					//UPDATE STOCK TRX
					if(!empty($dtInsert_stock)){
						$this->db->insert_batch($this->prefix.'stock', $dtInsert_stock);
					}
				}
			}
			
			return array('dtDistibution' => $dt_rowguid, 'dtInsert' => $dtInsert, 'dtUpdate' => $dtUpdate, 'dtDelete' => $dtDelete, 'update_stock' => $update_stock_item_unit);
		}
	}
	
	function getItem($distributionDetail = '', $storehouse = '', $dis_id = ''){
		
		if(empty($distributionDetail) OR empty($storehouse)){
			return array();
		}
		
		$storehouse_item = array($storehouse => array());
		$storehouse_item_qty = array($storehouse => array());
		$storehouse_item_qty_before = array($storehouse => array());
		
		if(!empty($distributionDetail)){
			foreach($distributionDetail as $dt){
				if(!in_array($dt['item_id'], $storehouse_item[$storehouse])){
					$storehouse_item[$storehouse][] = $dt['item_id'];
					//$storehouse_item_qty[$storehouse][$dt['item_id']] = $dt['disd_dikirim'];
					$storehouse_item_qty[$storehouse][$dt['item_id']] = $dt['disd_diterima'];
				}
			}
		}
		
		if(!empty($dis_id)){
			$this->db->select("a.*, b.dis_status");
			$this->db->from($this->prefix.'distribution_detail as a');
			$this->db->join($this->prefix.'distribution as b',"b.id = a.dis_id","LEFT");
			$this->db->where("dis_id", $dis_id);
			$get_det = $this->db->get();
			if($get_det->num_rows() > 0){
				foreach($get_det->result_array() as $dt){
					if(in_array($dt['item_id'], $storehouse_item[$storehouse])){
						if($dt['dis_status'] == 'done'){
							//$storehouse_item_qty_before[$storehouse][$dt['item_id']] = $dt['disd_dikirim'];
							$storehouse_item_qty_before[$storehouse][$dt['item_id']] = $dt['disd_diterima'];
						}else{
							$storehouse_item_qty_before[$storehouse][$dt['item_id']] = 0;
						}
						
					}
				}
			}
		}
		
		$ret_data = array(
			'storehouse' => $storehouse, 
			'storehouse_item' => $storehouse_item, 
			'storehouse_item_qty' => $storehouse_item_qty, 
			'storehouse_item_qty_before' => $storehouse_item_qty_before
		);
		
		return $ret_data;
	}
} 