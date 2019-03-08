<?php
class Model_reservationdetail extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'reservation_detail';
	}
	
	function reservationDetail($reservationDetail = '', $reservation_id = ''){
				
		$session_user = $this->session->userdata('user_username');
		
		if(!empty($reservationDetail)){
			
			if(empty($reservation_id)){
				$reservation_id = -1;
				$reservation_no = -1;
			}
			
			//insert batch
			$this->db->from($this->prefix.'reservation');
			$this->db->where("id", $reservation_id);
			$get_rowguid = $this->db->get();
			if($get_rowguid->num_rows() > 0){
				$dt_rowguid = $get_rowguid->row_array();
				
				
				//RESERVATION
				$reservation_no = $dt_rowguid['reservation_number'];
				$reservation_date = $dt_rowguid['reservation_date'];
				$reservation_from = $dt_rowguid['reservation_from'];
					
					
			}
			
			$dtCurrent = array();
			$dtCurrent_resd_qty = array();
			
			$this->db->from($this->prefix.'reservation_detail');
			$this->db->where("reservation_id", $reservation_id);
			$get_det = $this->db->get();
			if($get_det->num_rows() > 0){
				foreach($get_det->result() as $dt){
					if(!in_array($dt->id, $dtCurrent)){
						$dtCurrent[] = $dt->id;
						$dtCurrent_resd_qty[$dt->id] = $dt->resd_qty;
					}
				}
			}
			
			$total_qty = 0;
			$dtNew = array();
			$dtInsert = array();
			$dtUpdate = array();
			
			
			if(!empty($dt_rowguid)){
					
				foreach($reservationDetail as $dt){
					
					//unset($dt['sales_price']);
					unset($dt['product_name']);
					unset($dt['product_name_varian']);
					unset($dt['varian_name']);
					unset($dt['resd_hpp_show']);
					unset($dt['resd_price_show']);
					unset($dt['resd_tax_show']);
					unset($dt['resd_service_show']);
					unset($dt['resd_potongan_show']);
					unset($dt['resd_total_show']);
					unset($dt['resd_grandtotal_show']);
					unset($dt['reservation_status_text']);
					unset($dt['nomor']);
							
					
					//SURE ONLY UPDATE!
					/*
					if(($update_stok == 'update' OR $update_stok == 'rollback') AND !empty($dt['resd_qty'])){
						//DELIVERY
						$reservation_trx_type = 'out';
						$reservation_trx_qty = $dt['resd_qty'];
						
						$total_qty += ($dt['resd_qty']);
						
						if($update_stok == 'rollback'){
							//$reservation_from_swap = $reservation_from;
							//$delivery_to_swap = $delivery_to;
							//$reservation_from = $delivery_to_swap;
							//$delivery_to = $reservation_from_swap;
							unset($dt['resd_qty']);
						}
						
						if(empty($update_stock_product_unit[$reservation_from])){
							$update_stock_product_unit[$reservation_from] = array();
						}
						
						$update_stock_product_unit[$reservation_from][] = $dt['product_id'];
						
						$dtInsert_stock[] = array(
							"product_id" => $dt['product_id'],
							"trx_date" => $reservation_date,
							"trx_type" => $reservation_trx_type,
							"trx_qty" => $reservation_trx_qty,
							"unit_id" => $dt['unit_id'],
							"storehouse_id" => $reservation_from,
							"trx_nominal" => $dt['product_hpp'],
							"trx_note" => 'Sales Order',
							"trx_ref_data" => $reservation_no,
							"trx_ref_det_id" => $dt['id'],
							"is_active" => "1"
						);
						
						
					}
					*/
					
					if(empty($dt['resd_grandtotal'])){
						$dt['resd_grandtotal'] = $dt['resd_total'] + ($dt['resd_tax'] + $dt['resd_service']) - $dt['resd_potongan'];
					}
					
					$dt['resd_status'] = 0;
					if($dt_rowguid['reservation_status'] == 'done'){
						$dt['resd_status'] = 1;
					}
					
					//check if new
					if(strstr($dt['id'], 'new_')){
						unset($dt['id']);
					}
					
					$dt['reservation_id'] = $reservation_id;
					
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
			
			return array('dtReservation' => $dt_rowguid, 'dtInsert' => $dtInsert, 'dtUpdate' => $dtUpdate, 'dtDelete' => $dtDelete);
		}
	}
	

} 