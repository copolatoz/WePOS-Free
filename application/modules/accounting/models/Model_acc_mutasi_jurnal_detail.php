<?php
class Model_acc_mutasi_jurnal_detail extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix3');
		$this->table = $this->prefix.'jurnal_detail';
	}
	
	function mjDetail($mjDetail = '', $jurnal_header_id = ''){
				
		$session_user = $this->session->userdata('user_username');
		
		if(!empty($mjDetail)){
			//insert batch
			$this->db->from($this->prefix.'jurnal_header');
			$this->db->where("id", $jurnal_header_id);
			$get_rowguid = $this->db->get();
			if($get_rowguid->num_rows() > 0){
				$dt_rowguid = $get_rowguid->row_array();
			}
			
			$dtCurrent = array();
			$this->db->from($this->prefix.'jurnal_detail');
			$this->db->where("jurnal_header_id", $jurnal_header_id);
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
				foreach($mjDetail as $dt){
					
					unset($dt['kode_rek']);
					unset($dt['nama_rek']);
					unset($dt['kode_nama_rek']);
					unset($dt['jml_debet_show']);
					unset($dt['jml_kredit_show']);
					unset($dt['nomor']);
					
					$dt['tgl_transaksi'] = date("Y-m-d",strtotime($dt['tgl_transaksi']));
					
					if(empty($dt['detail_status']) OR $dt['detail_status'] == 'new'){
						$dt['detail_status'] = 'jurnal';
					}
					
					//check if new
					if(strstr($dt['id'], 'new_')){
						unset($dt['id']);
					}
					
					$dt['jurnal_header_id'] = $jurnal_header_id;
					
					if(empty($dt['id'])){
						
						unset($dt['id']);	
						
						$dt['created'] = date('Y-m-d H:i:s');
						$dt['createdby'] = $session_user;
						$dt['updated'] = date('Y-m-d H:i:s');
						$dt['updatedby'] = $session_user;
					
						$dtInsert[] = $dt;
						
					}else{
					
						
						$dt['updated'] = date('Y-m-d H:i:s');
						$dt['updatedby'] = $session_user;
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
			
			return array('dtMJ' => $dt_rowguid, 'dtInsert' => $dtInsert, 'dtUpdate' => $dtUpdate, 'dtDelete' => $dtDelete);
		}
	}

} 