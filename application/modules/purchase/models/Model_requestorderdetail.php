<?php
class Model_requestorderdetail extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'ro_detail';
	}
	
	function roDetail($roDetail = '', $ro_id = ''){
				
		$session_user = $this->session->userdata('user_username');
		
		if(!empty($roDetail)){
			
			if(empty($ro_id)){
				$ro_id = -1;
			}
			
			$dt_rowguid = array();
			//insert batch
			$this->db->from($this->prefix.'ro');
			$this->db->where("id", $ro_id);
			$get_rowguid = $this->db->get();
			if($get_rowguid->num_rows() > 0){
				$dt_rowguid = $get_rowguid->row_array();
			}
			
			$dtCurrent = array();
			$this->db->from($this->prefix.'ro_detail');
			$this->db->where("ro_id", $ro_id);
			$get_det = $this->db->get();
			if($get_det->num_rows() > 0){
				foreach($get_det->result() as $dt){
					if(!in_array($dt->id, $dtCurrent)){
						$dtCurrent[] = $dt->id;
					}
				}
			}
			
			$dtNew = array();
			$dtInsert = array();
			$dtUpdate = array();
			if(!empty($dt_rowguid)){
				foreach($roDetail as $dt){
					
					unset($dt['item_image']);
					unset($dt['item_price']);
					unset($dt['item_name']);
					unset($dt['unit_name']);
					unset($dt['nomor']);
					
					if(empty($dt['ro_detail_status']) OR $dt['ro_detail_status'] == 'new'){
						$dt['ro_detail_status'] = 'request';
					}
					
					//check if new
					if(strstr($dt['id'], 'new_')){
						unset($dt['id']);
					}
					
					$dt['ro_id'] = $ro_id;
					
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
			
			return array('dtRo' => $dt_rowguid, 'dtInsert' => $dtInsert, 'dtUpdate' => $dtUpdate, 'dtDelete' => $dtDelete);
		}
	}

} 