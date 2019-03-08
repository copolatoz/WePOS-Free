<?php
class Model_mutasi_kas_bank extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		
		$this->prefix = config_item('db_prefix3');
		$this->prefix_app = config_item('db_prefix2');
		$this->table_mutasi_kas_bank = $this->prefix.'mutasi_kas_bank';
		$this->table_po = $this->prefix_app.'po';
		$this->table_po_detail = $this->prefix_app.'po_detail';
		$this->table_supplier = $this->prefix_app.'supplier';
	}
	
	function set_mutasi_kas_bank_PO($ref_id = ''){
		
		$session_user = $this->session->userdata('user_username');
		
		if(empty($ref_id)){
			return false;
		}
		
		$this->db->select("a.*, b.supplier_name, b.supplier_phone, b.supplier_address");
		$this->db->from($this->table_po.' as a');
		$this->db->join($this->table_supplier.' as b', "b.id = a.supplier_id", "LEFT");
		$this->db->where("a.id = '".$ref_id."'");
		$get_po = $this->db->get();
		if($get_po->num_rows() > 0){
			
			$data_PO = $get_po->row();
			
			if($data_PO->po_payment == 'credit'){
				
				$data_post = array();
				
				//get detail PO
				$all_qty_price = 0;
				$data_PO->po_total_price = 0;
				$this->db->from($this->table_po_detail);
				$this->db->where("ref_id = '".$ref_id."'");
				$get_po_det = $this->db->get();
				if($get_po_det->num_rows() > 0){
					foreach($get_po_det->result() as $det){
						//$all_qty_price += (($det->po_detail_purchase - $det->po_detail_potongan)*$det->po_receive_qty);
						$all_qty_price += ($det->po_detail_purchase*$det->po_receive_qty);
					}
					
					$data_PO->po_total_price = $all_qty_price;
					$data_PO->po_total_price -= $data_PO->po_discount;
					$data_PO->po_total_price += $data_PO->po_tax;
					$data_PO->po_total_price += $data_PO->po_shipping;
				}
				
				$this->db->from($this->table_mutasi_kas_bank);
				$this->db->where("mkb_tujuan = 'reservation'");
				$this->db->where("ref_id = '".$ref_id."'");
				$get_ap = $this->db->get();
				if($get_ap->num_rows() > 0){
					
					$data_MKB = $get_ap->row();
					
					//update MKB
					$data_post = array(
						'mkb_name'	=> $data_PO->supplier_name,
						'mkb_date'	=> date('Y-m-d'),
						'mkb_phone'	=> $data_PO->supplier_phone,
						'mkb_address'	=> $data_PO->supplier_address,
						'supplier_id'	=> $data_PO->supplier_id,
						'no_ref'		=> $data_PO->po_number,
						'updated'		=>	date('Y-m-d H:i:s'),
						'updatedby'		=>	$session_user
					);
					
					if($data_PO->po_status == 'done'){
						
						
						if($data_MKB->mkb_status == 'pengakuan'){
							//update MKB
							$data_post['total_tagihan'] = $data_PO->po_total_price;
						}
						
						if($data_MKB->is_deleted == 1){
							$data_post['is_active'] = 1;
							$data_post['is_deleted'] = 0;
						}
						
					}else{
						
						$data_post['total_tagihan'] = $data_PO->po_total_price;
						$data_post['is_active'] = 0;
						$data_post['is_deleted'] = 1;
						
					}
					
				}else{
					
					if($data_PO->po_status == 'done'){
						
						//create new MKB
						$data_post = array(
							'mkb_name'	=> $data_PO->supplier_name,
							'mkb_date'	=> date('Y-m-d'),
							'mkb_phone'	=> $data_PO->supplier_phone,
							'mkb_address'	=> $data_PO->supplier_address,
							'mkb_tujuan'	=> 'reservation',
							'mkb_status'	=> 'pengakuan',
							'ref_id'			=> $data_PO->id,
							'supplier_id'	=> $data_PO->supplier_id,
							'no_ref'		=> $data_PO->po_number,
							'total_tagihan'	=> $data_PO->po_total_price,
							'created'		=>	date('Y-m-d H:i:s'),
							'createdby'		=>	$session_user,
							'updated'		=>	date('Y-m-d H:i:s'),
							'updatedby'		=>	$session_user
						);
						
					}
					
  
				}
				
				$return = true;
				if(!empty($data_post)){
					if(!empty($data_MKB->id)){
						
						$add_ap = $this->db->update($this->table_mutasi_kas_bank, $data_post, "id = '".$data_MKB->id."'");
						if($add_ap == false){
							$return = false;
						}
						
					}else{
						
						$get_mkb_no = $this->generate_mkb_number();
						$data_post['mkb_no'] = $get_mkb_no;
						
						$add_ap = $this->db->insert($this->table_mutasi_kas_bank, $data_post);
						if($add_ap == false){
							$return = false;
						}
						
					}
				}
				
				
			}
			
			
		}
		
	}
	
	public function generate_mkb_number(){
		
		$get_date = 'MKB'.date("Ym");
		
		$this->db->from($this->table_mutasi_kas_bank);
		$this->db->where("mkb_no LIKE '".$get_date."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_ap = $get_last->row();
			$mkb_number = str_replace($get_date,"", $data_ap->mkb_no);
			$mkb_number = (int) $mkb_number;			
		}else{
			$mkb_number = 0;
		}
		
		$mkb_number++;
		$length_no = strlen($mkb_number);
		switch ($length_no) {
			case 3:
				$mkb_number = $mkb_number;
				break;
			case 2:
				$mkb_number = '0'.$mkb_number;
				break;
			case 1:
				$mkb_number = '00'.$mkb_number;
				break;
			default:
				$mkb_number = '00'.$mkb_number;
				break;
		}
				
		return $get_date.$mkb_number;				
	}

} 