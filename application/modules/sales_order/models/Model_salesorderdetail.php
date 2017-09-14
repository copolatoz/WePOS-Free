<?php
class Model_salesorderdetail extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'salesorder_detail';
	}
	
	function salesOrderDetail($salesOrderDetail = '', $so_id = '', $update_stok = ''){
				
		$session_user = $this->session->userdata('user_username');
		
		$update_stock_item_unit = array();
		
		if(!empty($salesOrderDetail)){
			
			if(empty($so_id)){
				$so_id = -1;
				$so_no = -1;
			}
			
			//insert batch
			$this->db->from($this->prefix.'salesorder');
			$this->db->where("id", $so_id);
			$get_rowguid = $this->db->get();
			if($get_rowguid->num_rows() > 0){
				$dt_rowguid = $get_rowguid->row_array();
				
				
				//DISTRIBUTION
				$so_no = $dt_rowguid['so_number'];
				$so_date = $dt_rowguid['so_date'];
				$so_from = $dt_rowguid['so_from'];
					
					
			}
			
			$dtCurrent = array();
			$dtCurrent_sod_qty = array();
			
			$this->db->from($this->prefix.'salesorder_detail');
			$this->db->where("so_id", $so_id);
			$get_det = $this->db->get();
			if($get_det->num_rows() > 0){
				foreach($get_det->result() as $dt){
					if(!in_array($dt->id, $dtCurrent)){
						$dtCurrent[] = $dt->id;
						$dtCurrent_sod_qty[$dt->id] = $dt->sod_qty;
					}
				}
			}
			
			$total_qty = 0;
			$dtNew = array();
			$dtUpdate_Items = array();
			$dtInsert_stock = array();
			$dtInsert = array();
			$dtUpdate = array();
			
			$dtUpdate_kode_unik = array();
			$all_unik_kode = array();
			
			if(!empty($dt_rowguid)){
					
				foreach($salesOrderDetail as $dt){
					
					//unset($dt['sales_price']);
					unset($dt['item_code']);
					unset($dt['item_name']);
					unset($dt['item_code_name']);
					unset($dt['unit_name']);
					unset($dt['item_hpp_show']);
					unset($dt['sales_price_show']);
					unset($dt['sod_potongan_show']);
					unset($dt['sod_total_show']);
					unset($dt['so_status_text']);
					unset($dt['nomor']);
										
					//$dt['current_stock'] = 0;
					//if(!empty($all_stock_before_item[$dt['item_id']])){
					//	$dt['current_stock'] = $all_stock_before_item[$dt['item_id']];
					//}
					
					//UNIK KODE
					if($dt['use_stok_kode_unik'] == 1){
						$list_dt_kode = explode("\n",$dt['data_stok_kode_unik']);
						foreach($list_dt_kode as $kode_unik){
							if(!empty($kode_unik)){
								if(!in_array($kode_unik, $all_unik_kode)){
									$all_unik_kode[] = $kode_unik;
									
									$dtUpdate_kode_unik[] = array(
										"item_id" => $dt['item_id'],
										"kode_unik" => $kode_unik,
										"ref_out" => $so_no,
										"date_out" => $so_date.' '.date("H:i:s"),
										"storehouse_id" => $so_from
									);
									
								}
							}
							
						}
						
					}
					
					//SURE ONLY UPDATE!
					if(($update_stok == 'update' OR $update_stok == 'rollback') AND !empty($dt['sod_qty'])){
						//DELIVERY
						$so_trx_type = 'out';
						$so_trx_qty = $dt['sod_qty'];
						
						$total_qty += ($dt['sod_qty']);
						
						if($update_stok == 'rollback'){
							//$so_from_swap = $so_from;
							//$delivery_to_swap = $delivery_to;
							//$so_from = $delivery_to_swap;
							//$delivery_to = $so_from_swap;
							unset($dt['sod_qty']);
						}
						
						if(empty($update_stock_item_unit[$so_from])){
							$update_stock_item_unit[$so_from] = array();
						}
						
						$update_stock_item_unit[$so_from][] = $dt['item_id'];
						
						$dtInsert_stock[] = array(
							"item_id" => $dt['item_id'],
							"trx_date" => $so_date,
							"trx_type" => $so_trx_type,
							"trx_qty" => $so_trx_qty,
							"unit_id" => $dt['unit_id'],
							"storehouse_id" => $so_from,
							"trx_nominal" => $dt['item_hpp'],
							"trx_note" => 'Sales Order',
							"trx_ref_data" => $so_no,
							"trx_ref_det_id" => $dt['id'],
							"is_active" => "1"
						);
						
						
					}
					
					
					
					$dt['sod_status'] = 0;
					if($dt_rowguid['so_status'] == 'done'){
						$dt['sod_status'] = 1;
					}
					
					//check if new
					if(strstr($dt['id'], 'new_')){
						unset($dt['id']);
					}
					
					$dt['so_id'] = $so_id;
					
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
					$this->db->where("trx_ref_data", $so_no);
					$this->db->delete($this->prefix."stock"); 
					
					$unik_stok = array(
						'ref_out' => NULL,
						'date_out' => NULL,
					);
					$this->db->update($this->prefix."item_kode_unik", $unik_stok, "ref_out = '".$so_no."'"); 
					
				}else{
					//UPDATE STOCK TRX
					if(!empty($dtInsert_stock)){
						$this->db->insert_batch($this->prefix.'stock', $dtInsert_stock);
						
						if(!empty($dtUpdate_kode_unik)){
							$this->db->update_batch($this->prefix.'item_kode_unik', $dtUpdate_kode_unik, "kode_unik");
						}
						
					}
				}
			}
			
			return array('dtRo' => $dt_rowguid, 'dtInsert' => $dtInsert, 'dtUpdate' => $dtUpdate, 'dtDelete' => $dtDelete, 'update_stock' => $update_stock_item_unit);
		}
	}
	
	function getItem($salesOrderDetail = '', $storehouse = '', $so_id = ''){
		
		if(empty($salesOrderDetail) OR empty($storehouse)){
			return array();
		}
		
		$storehouse_item = array($storehouse => array());
		$storehouse_item_qty = array($storehouse => array());
		$storehouse_item_qty_before = array($storehouse => array());
		
		if(!empty($salesOrderDetail)){
			foreach($salesOrderDetail as $dt){
				if(!in_array($dt['item_id'], $storehouse_item[$storehouse])){
					$storehouse_item[$storehouse][] = $dt['item_id'];
					$storehouse_item_qty[$storehouse][$dt['item_id']] = $dt['sod_qty'];
				}
			}
		}
		
		if(!empty($so_id)){
			$this->db->select("a.*, b.so_status");
			$this->db->from($this->prefix.'salesorder_detail as a');
			$this->db->join($this->prefix.'salesorder as b',"b.id = a.so_id","LEFT");
			$this->db->where("so_id", $so_id);
			$get_det = $this->db->get();
			if($get_det->num_rows() > 0){
				foreach($get_det->result_array() as $dt){
					if(in_array($dt['item_id'], $storehouse_item[$storehouse])){
						if($dt['so_status'] == 'done'){
							$storehouse_item_qty_before[$storehouse][$dt['item_id']] = $dt['sod_qty'];
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