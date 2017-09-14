<?php
class Model_invoicear extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		
		$this->prefix = config_item('db_prefix2');
		$this->prefix_acc = config_item('db_prefix3');
		$this->table_invoice = $this->prefix_acc.'invoice';
		$this->table_account_receivable = $this->prefix_acc.'account_receivable';
		$this->table_salesorder = $this->prefix.'salesorder';
		$this->table_customer = $this->prefix.'customer';
	}
	
	
	public function generate_invoice_number(){
		
		$get_date = 'INV'.date("Ym");
		
		$this->db->from($this->table_invoice);
		$this->db->where("invoice_no LIKE '".$get_date."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_ar = $get_last->row();
			$invoice_number = str_replace($get_date,"", $data_ar->invoice_no);
			$invoice_number = (int) $invoice_number;			
		}else{
			$invoice_number = 0;
		}
		
		$invoice_number++;
		$length_no = strlen($invoice_number);
		switch ($length_no) {
			case 3:
				$invoice_number = $get_date.$invoice_number;
				break;
			case 2:
				$invoice_number = '0'.$invoice_number;
				break;
			case 1:
				$invoice_number = '00'.$invoice_number;
				break;
			default:
				$invoice_number = '00'.$invoice_number;
				break;
		}
				
		return $get_date.$invoice_number;				
	}

} 