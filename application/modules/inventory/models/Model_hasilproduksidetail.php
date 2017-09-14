<?php
class Model_hasilproduksidetail extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'production_detail';
	}
	
	function hasilProduksiDetail($hasilProduksiDetail = '', $pr_id = '', $update_stok = ''){
				
		$session_user = $this->session->userdata('user_username');
		
		$update_stock_item_unit = array();
		
		if(!empty($hasilProduksiDetail)){
			
			if(empty($pr_id)){
				$pr_id = -1;
				$pr_no = -1;
			}
			
			//insert batch
			$this->db->from($this->prefix.'production');
			$this->db->where("id", $pr_id);
			$get_rowguid = $this->db->get();
			if($get_rowguid->num_rows() > 0){
				$dt_rowguid = $get_rowguid->row_array();
				
				
				//DISTRIBUTION
				$pr_no = $dt_rowguid['pr_number'];
				$pr_date = $dt_rowguid['pr_date'];
				$pr_to = $dt_rowguid['pr_to'];
					
					
			}
			
			$dtCurrent = array();
			$dtCurrent_prd_qty = array();
			
			$this->db->from($this->prefix.'production_detail');
			$this->db->where("pr_id", $pr_id);
			$get_det = $this->db->get();
			if($get_det->num_rows() > 0){
				foreach($get_det->result() as $dt){
					if(!in_array($dt->id, $dtCurrent)){
						$dtCurrent[] = $dt->id;
						$dtCurrent_prd_qty[$dt->id] = $dt->prd_qty;
					}
				}
			}
			
			$total_qty = 0;
			$dtNew = array();
			$dtUpdate_Items = array();
			$dtInsert_stock = array();
			$dtInsert = array();
			$dtUpdate = array();
			if(!empty($dt_rowguid)){
					
				foreach($hasilProduksiDetail as $dt){
					
					//unset($dt['item_price']);
					unset($dt['item_code']);
					unset($dt['item_name']);
					unset($dt['item_code_name']);
					unset($dt['unit_name']);
					unset($dt['item_hpp_show']);
					unset($dt['pr_status_text']);
					unset($dt['nomor']);
										
					//$dt['current_stock'] = 0;
					//if(!empty($all_stock_before_item[$dt['item_id']])){
					//	$dt['current_stock'] = $all_stock_before_item[$dt['item_id']];
					//}
					
					//SURE ONLY UPDATE!
					if(($update_stok == 'update' OR $update_stok == 'rollback') AND !empty($dt['prd_qty'])){
						//DELIVERY
						$pr_trx_type = 'in';
						$pr_trx_qty = $dt['prd_qty'];
						
						$total_qty += ($dt['prd_qty']);
						
						if($update_stok == 'rollback'){
							//$pr_to_swap = $pr_to;
							//$delivery_to_swap = $delivery_to;
							//$pr_to = $delivery_to_swap;
							//$delivery_to = $pr_to_swap;
							unset($dt['prd_qty']);
						}
						
						if(empty($update_stock_item_unit[$pr_to])){
							$update_stock_item_unit[$pr_to] = array();
						}
						
						$update_stock_item_unit[$pr_to][] = $dt['item_id'];
						
						$dtInsert_stock[] = array(
							"item_id" => $dt['item_id'],
							"trx_date" => $pr_date,
							"trx_type" => $pr_trx_type,
							"trx_qty" => $pr_trx_qty,
							"unit_id" => $dt['unit_id'],
							"storehouse_id" => $pr_to,
							"trx_nominal" => $dt['item_hpp'],
							"trx_note" => 'Production',
							"trx_ref_data" => $pr_no,
							"trx_ref_det_id" => $dt['id'],
							"is_active" => "1"
						);
						
						
					}
					
					
					
					$dt['prd_status'] = 0;
					if($dt_rowguid['pr_status'] == 'done'){
						$dt['prd_status'] = 1;
					}
					
					//check if new
					if(strstr($dt['id'], 'new_')){
						unset($dt['id']);
					}
					
					$dt['pr_id'] = $pr_id;
					
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
					$this->db->where("trx_ref_data", $pr_no);
					$this->db->delete($this->prefix."stock"); 
				}else{
					//UPDATE STOCK TRX
					if(!empty($dtInsert_stock)){
						$this->db->insert_batch($this->prefix.'stock', $dtInsert_stock);
					}
				}
			}
			
			return array('dtRo' => $dt_rowguid, 'dtInsert' => $dtInsert, 'dtUpdate' => $dtUpdate, 'dtDelete' => $dtDelete, 'update_stock' => $update_stock_item_unit);
		}
	}
	
	function getItem($hasilProduksiDetail = '', $storehouse = '', $pr_id = ''){
		
		if(empty($hasilProduksiDetail) OR empty($storehouse)){
			return array();
		}
		
		$storehouse_item = array($storehouse => array());
		$storehouse_item_qty = array($storehouse => array());
		$storehouse_item_qty_before = array($storehouse => array());
		
		if(!empty($hasilProduksiDetail)){
			foreach($hasilProduksiDetail as $dt){
				if(!in_array($dt['item_id'], $storehouse_item[$storehouse])){
					$storehouse_item[$storehouse][] = $dt['item_id'];
					$storehouse_item_qty[$storehouse][$dt['item_id']] = $dt['prd_qty'];
				}
			}
		}
		
		if(!empty($pr_id)){
			$this->db->select("a.*, b.pr_status");
			$this->db->from($this->prefix.'production_detail as a');
			$this->db->join($this->prefix.'production as b',"b.id = a.pr_id","LEFT");
			$this->db->where("pr_id", $pr_id);
			$get_det = $this->db->get();
			if($get_det->num_rows() > 0){
				foreach($get_det->result_array() as $dt){
					if(in_array($dt['item_id'], $storehouse_item[$storehouse])){
						
						if($dt['pr_status'] == 'done'){
							$storehouse_item_qty_before[$storehouse][$dt['item_id']] = $dt['prd_qty'];
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