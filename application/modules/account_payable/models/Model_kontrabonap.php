<?php
class Model_kontrabonap extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		
		$this->prefix = config_item('db_prefix2');
		$this->prefix_acc = config_item('db_prefix3');
		$this->table_kontrabon = $this->prefix_acc.'kontrabon';
		$this->table_account_payable = $this->prefix_acc.'account_payable';
		$this->table_po = $this->prefix.'po';
		$this->table_supplier = $this->prefix.'supplier';
	}
	
	
	public function generate_kb_number(){
		
		$get_date = 'KB'.date("Ym");
		
		$this->db->from($this->table_kontrabon);
		$this->db->where("kb_no LIKE '".$get_date."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_ap = $get_last->row();
			$kb_number = str_replace($get_date,"", $data_ap->kb_no);
			$kb_number = (int) $kb_number;			
		}else{
			$kb_number = 0;
		}
		
		$kb_number++;
		$length_no = strlen($kb_number);
		switch ($length_no) {
			case 3:
				$kb_number = $get_date.$kb_number;
				break;
			case 2:
				$kb_number = '0'.$kb_number;
				break;
			case 1:
				$kb_number = '00'.$kb_number;
				break;
			default:
				$kb_number = '00'.$kb_number;
				break;
		}
				
		return $get_date.$kb_number;				
	}

} 