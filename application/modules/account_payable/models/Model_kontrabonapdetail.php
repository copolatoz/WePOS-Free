<?php
class Model_kontrabonapdetail extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix3');
		$this->table = $this->prefix.'kontrabon_detail';
	}
	
	function kbDetail($kbDetail = '', $kb_id = ''){
				
		$session_user = $this->session->userdata('user_username');
		
		if(!empty($kbDetail)){
			
			if(empty($kb_id)){
				$kb_id = -1;
			}
			
			//insert batch
			$this->db->from($this->prefix.'kontrabon');
			$this->db->where("id", $kb_id);
			$get_rowguid = $this->db->get();
			if($get_rowguid->num_rows() > 0){
				$dt_rowguid = $get_rowguid->row_array();
			}
			
			$dtCurrent = array();
			$dtCurrent_ap_id = array();
			$this->db->from($this->prefix.'kontrabon_detail');
			$this->db->where("kb_id", $kb_id);
			$get_det = $this->db->get();
			if($get_det->num_rows() > 0){
				foreach($get_det->result() as $dt){
					if(!in_array($dt->id, $dtCurrent)){
						$dtCurrent[] = $dt->id;
						$dtCurrent_ap_id[$dt->id] = $dt->ap_id;
					}
				}
			}
			
			$all_ap_id = array();
			$dtNew = array();
			$dtInsert = array();
			$dtUpdate = array();
			$dtUpdate_AP = array();
			$dtDelete_AP = array();
			if(!empty($dt_rowguid)){
				foreach($kbDetail as $dt){
					
					if(!empty($dt['ap_id'])){
						//$all_ap_id[] = $dt['ap_id'];
					}
					unset($dt['ap_no']);
					unset($dt['ap_name']);
					unset($dt['ap_date']);
					unset($dt['ap_notes']);
					unset($dt['ap_no_name']);
					unset($dt['total_tagihan_show']);
					unset($dt['total_bayar_show']);
					unset($dt['supplier_id']);
					unset($dt['supplier_name']);
					unset($dt['kbd_status_text']);
					unset($dt['nomor']);
					unset($dt['no_ref']);
					
					if(empty($dt['kbd_status']) OR $dt['kbd_status'] == 'new'){
						$dt['kbd_status'] = 'unpaid';
					}
					
					//check if new
					if(strstr($dt['id'], 'new_')){
						unset($dt['id']);
					}
					
					$dt['kb_id'] = $kb_id;
					
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
			
			//UPDATE AP
			if(!empty($dtInsert) OR !empty($dtUpdate)){
				$this->db->from($this->table);
				$this->db->where("kb_id", $kb_id);
				$kontrabon_detail = $this->db->get();
				if($kontrabon_detail->num_rows() > 0){
					foreach($kontrabon_detail->result() as $rAP){
						
						$dtUpdate_AP[] = array(
								'id'  => $rAP->ap_id,
								'ap_status'  => 'kontrabon',
								'ap_used' => 1,				
								'no_kontrabon' => $dt_rowguid['kb_no']	
						);						
					}
					
					//UPDATE BATCH AP
					if(!empty($dtUpdate_AP)){
						$this->db->update_batch($this->prefix."account_payable", $dtUpdate_AP, "id");
					}

				}
			}
			
			if(!empty($dtDelete)){
				$dtDelete_AP = array();
				foreach($dtDelete as $detId){
				
					if(!empty($dtCurrent_ap_id[$detId])){
						$dtDelete_AP[] = array(
							'id'  => $dtCurrent_ap_id[$detId],
							'ap_status'  => 'posting',
							'ap_used' => 0				
						);
					}
					
				}
					
				//UPDATE BATCH AP
				if(!empty($dtDelete_AP)){
					$this->db->update_batch($this->prefix."account_payable", $dtDelete_AP, "id");
				}
			}
			
			/*
			if(!empty($all_ap_id)){
				$all_AP_id = array();
				$all_ap_id_txt = implode(",", $all_ap_id);
				$this->db->from($this->prefix."account_payable");
				$this->db->where("id IN (".$all_ap_id_txt.")");
				$getAP = $this->db->get();
				if($getAP->num_rows() > 0){
					foreach($getAP->result() as $dt){
						$all_AP_id[] = $dt->ap_id;
					}
				}
				
				if(!empty($all_AP_id)){
					$all_AP_id_txt = implode(",", $all_AP_id);
					$updateAP_status = array("ap_status"	=> 'kontrabon');
					$this->db->update($this->prefix."account_payable",$updateAP_status,"id IN (".$all_AP_id_txt.")");
				}
				
			}
			*/
			
			return array('dtAP' => $dt_rowguid, 'dtInsert' => $dtInsert, 'dtUpdate' => $dtUpdate, 'dtDelete' => $dtDelete, 
			'dtUpdate_AP' => $dtUpdate_AP, 'dtDelete_AP' => $dtDelete_AP);
		}
	}

} 