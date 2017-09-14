<?php
class Model_pembayaranar extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		
		$this->prefix = config_item('db_prefix2');
		$this->prefix_acc = config_item('db_prefix3');
		$this->table_pembayaran_ar = $this->prefix_acc.'pembayaran_ar';
		$this->table_invoice = $this->prefix_acc.'invoice';
		$this->table_account_receivable = $this->prefix_acc.'account_receivable';
		$this->table_salesorder = $this->prefix.'salesorder';
		$this->table_customer = $this->prefix.'customer';
	}
	
	
	public function update_status_invoice($pembayaran_no = ''){
		
		if(!empty($pembayaran_no)){
			
			$this->db->from($this->table_pembayaran_ar);
			$this->db->where("pembayaran_no = '".$pembayaran_no."'");
			//$this->db->where("is_deleted = 0");
			$get_ar = $this->db->get();
			
			if($get_ar->num_rows() > 0){
				$dt_ar = $get_ar->row();
				
				//get all kb
				$all_pembayaran_total = 0;
				
				$this->db->from($this->table_pembayaran_ar);
				$this->db->where("invoice_id = '".$dt_ar->invoice_id."'");
				$this->db->where("is_deleted = 0");
				$this->db->where("pembayaran_status = 'posting'");
				$get_all_ap = $this->db->get();
				if($get_all_ap->num_rows() > 0){
					foreach($get_all_ap->result() as $dt){
						$all_pembayaran_total += $dt->pembayaran_total;
					}
				}
				
				$this->db->from($this->table_invoice);
				$this->db->where("id = '".$dt_ar->invoice_id."'");
				$this->db->where("is_deleted = 0");
				$get_inv = $this->db->get();
			
				if($get_inv->num_rows() > 0){
					$dt_inv = $get_inv->row();
					
					$update_inv = array(
						'invoice_status'		=> 'progress',
						'total_bayar'	=> $all_pembayaran_total,
					);
					
					if($all_pembayaran_total >= $dt_inv->total_tagihan){
						$update_inv = array(
							'invoice_status'		=> 'done',
							'total_bayar'	=> $all_pembayaran_total,
						);
					}
					
					$this->db->update($this->table_invoice, $update_inv, "id = '".$dt_inv->id."'");
					
				}
				
				
			}
			
		}
		
	}
	
	public function generate_pembayaran_number(){
		
		$get_date = 'PB'.date("Ym");
		
		$this->db->from($this->table_pembayaran_ar);
		$this->db->where("pembayaran_no LIKE '".$get_date."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_ar = $get_last->row();
			$pembayaran_number = str_replace($get_date,"", $data_ar->pembayaran_no);
			$pembayaran_number = (int) $pembayaran_number;			
		}else{
			$pembayaran_number = 0;
		}
		
		$pembayaran_number++;
		$length_no = strlen($pembayaran_number);
		switch ($length_no) {
			case 3:
				$pembayaran_number = $get_date.$pembayaran_number;
				break;
			case 2:
				$pembayaran_number = '0'.$pembayaran_number;
				break;
			case 1:
				$pembayaran_number = '00'.$pembayaran_number;
				break;
			default:
				$pembayaran_number = '00'.$pembayaran_number;
				break;
		}
				
		return $get_date.$pembayaran_number;				
	}

} 