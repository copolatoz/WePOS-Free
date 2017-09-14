<?php
class Model_purchaseorder extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'po';
		$this->table_detail = $this->prefix.'po_detail';
		$this->table_account_payable = $this->prefix.'account_payable';
		$this->table_supplier = $this->prefix.'supplier';
	}
	
	function update_status_PO($po_id = ''){
		
		if(empty($po_id)){
			return false;
		}
		
		//CEK Current PO Detail
		$not_done = false;
		$this->db->from($this->table_detail);
		$this->db->where("po_id = '".$po_id."'");
		$get_detail = $this->db->get();
		if($get_detail->num_rows() > 0){
			foreach($get_detail->result() as $det){
				if($det->po_detail_qty > $det->po_receive_qty){
					$not_done = true;
				}
			}
		}
		
		
		$status = 'done';
		if($not_done){
			$status = 'progress';
		}
		
		$dt_update = array('po_status'  => $status);
		$update = $this->db->update($this->table, $dt_update, "id = '".$po_id."'");
		
		return $update;
		
		
	}
	
	

} 