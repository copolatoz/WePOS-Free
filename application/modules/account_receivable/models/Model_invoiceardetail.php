<?php
class Model_invoiceardetail extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix3');
		$this->table = $this->prefix.'invoice_detail';
	}
	
	function invoiceDetail($invoiceDetail = '', $invoice_id = ''){
				
		$session_user = $this->session->userdata('user_username');
		
		if(!empty($invoiceDetail)){
			
			if(empty($invoice_id)){
				$invoice_id = -1;
			}
			
			//insert batch
			$this->db->from($this->prefix.'invoice');
			$this->db->where("id", $invoice_id);
			$get_rowguid = $this->db->get();
			if($get_rowguid->num_rows() > 0){
				$dt_rowguid = $get_rowguid->row_array();
			}
			
			$dtCurrent = array();
			$dtCurrent_ar_id = array();
			$this->db->from($this->prefix.'invoice_detail');
			$this->db->where("invoice_id", $invoice_id);
			$get_det = $this->db->get();
			if($get_det->num_rows() > 0){
				foreach($get_det->result() as $dt){
					if(!in_array($dt->id, $dtCurrent)){
						$dtCurrent[] = $dt->id;
						$dtCurrent_ar_id[$dt->id] = $dt->ar_id;
					}
				}
			}
			
			$all_ar_id = array();
			$dtNew = array();
			$dtInsert = array();
			$dtUpdate = array();
			$dtUpdate_AR = array();
			$dtDelete_AR = array();
			if(!empty($dt_rowguid)){
				foreach($invoiceDetail as $dt){
					
					if(!empty($dt['ar_id'])){
						//$all_ar_id[] = $dt['ar_id'];
					}
					unset($dt['ar_no']);
					unset($dt['ar_name']);
					unset($dt['ar_date']);
					unset($dt['ar_notes']);
					unset($dt['ar_no_name']);
					unset($dt['total_tagihan_show']);
					unset($dt['total_bayar_show']);
					unset($dt['customer_id']);
					unset($dt['customer_name']);
					unset($dt['invoiced_status_text']);
					unset($dt['nomor']);
					unset($dt['no_ref']);
					
					if(empty($dt['invoiced_status']) OR $dt['invoiced_status'] == 'new'){
						$dt['invoiced_status'] = 'unpaid';
					}
					
					//check if new
					if(strstr($dt['id'], 'new_')){
						unset($dt['id']);
					}
					
					$dt['invoice_id'] = $invoice_id;
					
					if(empty($dt['id'])){
						
						unset($dt['id']);	

						$dt['created']		=	date('Y-m-d H:i:s');
						$dt['createdby']	=	$session_user;
						$dt['updated']		=	date('Y-m-d H:i:s');
						$dt['updatedby']	=	$session_user;
					
						$dtInsert[] = $dt;
						
					}else{
					
						
						$dt['updated']		=	date('Y-m-d H:i:s');
						$dt['updatedby']	=	$session_user;
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
			
			//UPDATE AR
			if(!empty($dtInsert) OR !empty($dtUpdate)){
				$this->db->from($this->table);
				$this->db->where("invoice_id", $invoice_id);
				$invoice_detail = $this->db->get();
				if($invoice_detail->num_rows() > 0){
					foreach($invoice_detail->result() as $rAR){
						
						$dtUpdate_AR[] = array(
								'id'  => $rAR->ar_id,
								'ar_status'  => 'invoice',
								'ar_used' => 1,				
								'no_invoice' => $dt_rowguid['invoice_no']
						);						
					}
					
					//UPDATE BATCH AR
					if(!empty($dtUpdate_AR)){
						$this->db->update_batch($this->prefix."account_receivable", $dtUpdate_AR, "id");
					}

				}
			}
			
			if(!empty($dtDelete)){
				$dtDelete_AR = array();
				foreach($dtDelete as $detId){
				
					if(!empty($dtCurrent_ar_id[$detId])){
						$dtDelete_AR[] = array(
							'id'  => $dtCurrent_ar_id[$detId],
							'ar_status'  => 'posting',
							'ar_used' => 0				
						);
					}
					
				}
					
				//UPDATE BATCH AR
				if(!empty($dtDelete_AR)){
					$this->db->update_batch($this->prefix."account_receivable", $dtDelete_AR, "id");
				}
			}
			
			/*
			if(!empty($all_ar_id)){
				$all_AR_id = array();
				$all_ar_id_txt = implode(",", $all_ar_id);
				$this->db->from($this->prefix."account_receivable");
				$this->db->where("id IN (".$all_ar_id_txt.")");
				$getAR = $this->db->get();
				if($getAR->num_rows() > 0){
					foreach($getAR->result() as $dt){
						$all_AR_id[] = $dt->ar_id;
					}
				}
				
				if(!empty($all_AR_id)){
					$all_AR_id_txt = implode(",", $all_AR_id);
					$updateAR_status = array("ar_status"	=> 'invoice');
					$this->db->update($this->prefix."account_receivable",$updateAR_status,"id IN (".$all_AR_id_txt.")");
				}
				
			}
			*/
			
			return array('dtAR' => $dt_rowguid, 'dtInsert' => $dtInsert, 'dtUpdate' => $dtUpdate, 'dtDelete' => $dtDelete, 
			'dtUpdate_AR' => $dtUpdate_AR, 'dtDelete_AR' => $dtDelete_AR);
		}
	}

} 