<?php
class Model_purchaseorderdetail extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'po_detail';
	}
	
	function poDetail($poDetail = '', $po_id = ''){
				
		$session_user = $this->session->userdata('user_username');
		
		if(!empty($poDetail)){
			
			if(empty($po_id)){
				$po_id = -1;
			}
			
			//insert batch
			$this->db->from($this->prefix.'po');
			$this->db->where("id", $po_id);
			$get_rowguid = $this->db->get();
			if($get_rowguid->num_rows() > 0){
				$dt_rowguid = $get_rowguid->row_array();
			}
			
			$dtCurrent = array();
			$dtCurrent_ro_det_id = array();
			$this->db->from($this->prefix.'po_detail');
			$this->db->where("po_id", $po_id);
			$get_det = $this->db->get();
			if($get_det->num_rows() > 0){
				foreach($get_det->result() as $dt){
					if(!in_array($dt->id, $dtCurrent)){
						$dtCurrent[] = $dt->id;
						$dtCurrent_ro_det_id[$dt->id] = $dt->ro_detail_id;
					}
				}
			}
			
			$all_ro_detail_id = array();
			$dtNew = array();
			$dtInsert = array();
			$dtUpdate = array();
			$dtUpdate_RO = array();
			$dtDelete_RO = array();
			if(!empty($dt_rowguid)){
				foreach($poDetail as $dt){
					
					if(!empty($dt['ro_detail_id'])){
						$all_ro_detail_id[] = $dt['ro_detail_id'];
					}
					unset($dt['po_receive_qty']);
					unset($dt['po_receive_total']);
					unset($dt['po_receive_total_show']);
					unset($dt['ro_number']);
					unset($dt['ro_id']);
					unset($dt['po_detail_total_show']);
					unset($dt['po_detail_purchase_show']);
					unset($dt['po_detail_potongan_show']);
					unset($dt['supplier_id']);
					unset($dt['supplier_name']);
					unset($dt['item_image']);
					unset($dt['item_hpp']);
					unset($dt['item_price']);
					unset($dt['item_code']);
					unset($dt['item_code_name']);
					unset($dt['item_name']);
					unset($dt['unit_name']);
					unset($dt['nomor']);
					unset($dt['from_supplier_item']);
					
					if(empty($dt['po_detail_status']) OR $dt['po_detail_status'] == 'new'){
						$dt['po_detail_status'] = 'request';
					}
					
					//check if new
					if(strstr($dt['id'], 'new_')){
						unset($dt['id']);
					}
					
					$dt['po_id'] = $po_id;
					
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
			
			$this->db->select("a.*, b.receive_status, b.receive_date, b.storehouse_id");
			$this->db->from($this->prefix."receive_detail as a");
			$this->db->join($this->prefix."receiving as b","b.id = a.receive_id","LEFT");
			$this->db->where("b.po_id = ".$po_id);
			$this->db->where("b.is_deleted = 0");
			$get_rl = $this->db->get();
			$all_receive_id = array();
			$all_receive_id_dt = array();
			$all_receive_detail = array();
			$all_receive_po_det_id = array();
			$default_receive_date = 0;
			if($get_rl->num_rows() > 0){
				foreach($get_rl->result_array() as $dt){
					if($dt['receive_status'] == 'progress'){
						if(!in_array($dt['receive_id'], $all_receive_id)){
							$all_receive_id[] = $dt['receive_id'];
							$all_receive_id_dt[] = array(
								'id'	=> $dt['receive_id'],
								'receive_status'	=> $dt['receive_status'],
								'receive_date'	=> $dt['receive_date'],
								'storehouse_id'	=> $dt['storehouse_id']
							);
						}
						
						if(!in_array($dt['po_detail_id'], $all_receive_po_det_id)){
							$all_receive_po_det_id[] = $dt['po_detail_id'];
						}
						
						if(empty($default_receive_date)){
							$default_receive_date = $dt['receive_date'];
						}
						
						if(strtotime($default_receive_date) < strtotime($dt['receive_date'])){
							$default_receive_date = $dt['receive_date'];
						}
						
						$all_receive_detail[] = $dt;
					}
				}
			}
			
			
			if(!empty($dtDelete)){
				$allRowguid = implode("','", $dtDelete);
				$this->db->where("id IN ('".$allRowguid."')");
				$this->db->delete($this->table); 
				
				//delete on receiving list if available
				if(!empty($all_receive_id)){
					$all_receive_id_sql = implode(",", $all_receive_id);
					$this->db->where("po_detail_id IN ('".$allRowguid."') AND receive_id IN ('".$all_receive_id_sql."')");
					$this->db->delete($this->prefix."receive_detail"); 
				}
				
			}
			
			if(!empty($dtInsert)){
				$this->db->insert_batch($this->table, $dtInsert);
				
				//insert to rl
				$dtInsertRL = array();
				$this->db->select("a.*, b.supplier_id");
				$this->db->from($this->prefix."po_detail as a");
				$this->db->join($this->prefix."po as b","b.id = a.po_id","LEFT");
				$this->db->where("a.po_id = ".$po_id);
				$get_po_det = $this->db->get();
				if($get_po_det->num_rows() > 0){
					foreach($get_po_det->result_array() as $dt){
						
						if(!in_array($dt['id'], $all_receive_po_det_id)){
							
							foreach($all_receive_id_dt as $detRL){
								$dtInsertRL[] = array(
									'receive_id'		=> $detRL['id'],
									'item_id'			=> $dt['item_id'],
									'receive_det_date'	=> $default_receive_date,
									'unit_id'			=> $dt['unit_id'],
									'receive_det_qty'	=> $dt['po_detail_qty'],
									'receive_det_purchase'=> $dt['po_detail_purchase'],
									'po_detail_qty'		=> $dt['po_detail_qty'],
									'po_detail_id'		=> $dt['id'],
									'supplier_item_id'	=> $dt['supplier_item_id'],
									'storehouse_id'		=> $detRL['storehouse_id']
								);
							}
						}
						
					}
				}
				
				if(!empty($dtInsertRL)){
					$this->db->insert_batch($this->prefix."receive_detail", $dtInsertRL);
				}
				
			}
			
			if(!empty($dtUpdate)){
				$this->db->update_batch($this->table, $dtUpdate, 'id');
				
				//update on rl detail
				if(!empty($all_receive_id)){
					$dtUpdateRL = array();
					foreach($dtUpdate as $dt){
						
						if(!empty($all_receive_detail)){
							foreach($all_receive_detail as $detRL){
								
								if($detRL['po_detail_id'] == $dt['id']){
									
									$dtUpdateRL[] = array(
										'item_id'	=> $dt['item_id'],
										'unit_id'=> $dt['unit_id'],
										'receive_det_purchase'=> $dt['po_detail_purchase'],
										'po_detail_qty'=> $dt['po_detail_qty'],
										'po_detail_id'=> $dt['id'],
										'id'=> $detRL['id']
									);
									
								}
							}
						}
						
					}
					
					if(!empty($dtUpdateRL)){
						$this->db->update_batch($this->prefix."receive_detail", $dtUpdateRL, 'id');
					}
					
				}
			}
			
			//UPDATE RO
			if(!empty($dtInsert) OR !empty($dtUpdate)){
				$this->db->from($this->table);
				$this->db->where("po_id", $po_id);
				$po_detail = $this->db->get();
				if($po_detail->num_rows() > 0){
					foreach($po_detail->result() as $rPO){
						
						$dtUpdate_RO[] = array(
								'id'  => $rPO->ro_detail_id,
								'ro_detail_status'  => 'take',
								'take_reff_id' => $rPO->po_id,
								'take_reff_detail_id'  => $rPO->id					
						);						
					}
					
					//UPDATE BATCH RO
					if(!empty($dtUpdate_RO)){
						$this->db->update_batch($this->prefix."ro_detail", $dtUpdate_RO, "id");
					}

				}
			}
			
			if(!empty($dtDelete)){
				$dtDelete_RO = array();
				foreach($dtDelete as $detId){
				
					if(!empty($dtCurrent_ro_det_id[$detId])){
						$dtDelete_RO[] = array(
							'id'  => $dtCurrent_ro_det_id[$detId],
							'ro_detail_status'  => 'validated',
							'take_reff_id' => 0,
							'take_reff_detail_id'  => 0					
						);
					}
					
				}
					
				//UPDATE BATCH RO
				if(!empty($dtDelete_RO)){
					$this->db->update_batch($this->prefix."ro_detail", $dtDelete_RO, "id");
				}
			}
			
			if(!empty($all_ro_detail_id)){
				$all_RO_id = array();
				$all_ro_detail_id_txt = implode(",", $all_ro_detail_id);
				$this->db->from($this->prefix."ro_detail");
				$this->db->where("id IN (".$all_ro_detail_id_txt.")");
				$getRO = $this->db->get();
				if($getRO->num_rows() > 0){
					foreach($getRO->result() as $dt){
						$all_RO_id[] = $dt->ro_id;
					}
				}
				
				if(!empty($all_RO_id)){
					$all_RO_id_txt = implode(",", $all_RO_id);
					$updateRO_status = array("ro_status"	=> 'take');
					$this->db->update($this->prefix."ro",$updateRO_status,"id IN (".$all_RO_id_txt.")");
				}
				
			}
			
			return array('dtPO' => $dt_rowguid, 'dtInsert' => $dtInsert, 'dtUpdate' => $dtUpdate, 'dtDelete' => $dtDelete, 
			'dtUpdate_RO' => $dtUpdate_RO, 'dtDelete_RO' => $dtDelete_RO);
		}
	}

} 