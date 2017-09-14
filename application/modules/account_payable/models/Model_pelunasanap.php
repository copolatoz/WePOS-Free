<?php
class Model_pelunasanap extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		
		$this->prefix = config_item('db_prefix2');
		$this->prefix_acc = config_item('db_prefix3');
		$this->table_pelunasan_ap = $this->prefix_acc.'pelunasan_ap';
		$this->table_kontrabon = $this->prefix_acc.'kontrabon';
		$this->table_account_payable = $this->prefix_acc.'account_payable';
		$this->table_po = $this->prefix.'po';
		$this->table_supplier = $this->prefix.'supplier';
	}
	
	
	public function update_status_kb($pelunasan_no = ''){
		
		if(!empty($pelunasan_no)){
			
			$this->db->from($this->table_pelunasan_ap);
			$this->db->where("pelunasan_no = '".$pelunasan_no."'");
			//$this->db->where("is_deleted = 0");
			$get_ap = $this->db->get();
			
			if($get_ap->num_rows() > 0){
				$dt_ap = $get_ap->row();
				
				//get all kb
				$all_pelunasan_total = 0;
				
				$this->db->from($this->table_pelunasan_ap);
				$this->db->where("kb_id = '".$dt_ap->kb_id."'");
				$this->db->where("is_deleted = 0");
				$this->db->where("pelunasan_status = 'posting'");
				$get_all_ap = $this->db->get();
				if($get_all_ap->num_rows() > 0){
					foreach($get_all_ap->result() as $dt){
						$all_pelunasan_total += $dt->pelunasan_total;
					}
				}
				
				$this->db->from($this->table_kontrabon);
				$this->db->where("id = '".$dt_ap->kb_id."'");
				$this->db->where("is_deleted = 0");
				$get_kb = $this->db->get();
			
				if($get_kb->num_rows() > 0){
					$dt_kb = $get_kb->row();
					
					$update_kb = array(
						'kb_status'		=> 'progress',
						'total_bayar'	=> $all_pelunasan_total,
					);
					
					if($all_pelunasan_total >= $dt_kb->total_tagihan){
						$update_kb = array(
							'kb_status'		=> 'done',
							'total_bayar'	=> $all_pelunasan_total,
						);
					}
					
					$this->db->update($this->table_kontrabon, $update_kb, "id = '".$dt_kb->id."'");
					
				}
				
				
			}
			
		}
		
	}
	
	public function generate_pelunasan_number(){
		
		$get_date = 'PL'.date("Ym");
		
		$this->db->from($this->table_pelunasan_ap);
		$this->db->where("pelunasan_no LIKE '".$get_date."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_ap = $get_last->row();
			$pelunasan_number = str_replace($get_date,"", $data_ap->pelunasan_no);
			$pelunasan_number = (int) $pelunasan_number;			
		}else{
			$pelunasan_number = 0;
		}
		
		$pelunasan_number++;
		$length_no = strlen($pelunasan_number);
		switch ($length_no) {
			case 3:
				$pelunasan_number = $get_date.$pelunasan_number;
				break;
			case 2:
				$pelunasan_number = '0'.$pelunasan_number;
				break;
			case 1:
				$pelunasan_number = '00'.$pelunasan_number;
				break;
			default:
				$pelunasan_number = '00'.$pelunasan_number;
				break;
		}
				
		return $get_date.$pelunasan_number;				
	}

} 