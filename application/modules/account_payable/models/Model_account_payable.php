<?php
class Model_account_payable extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		
		$this->prefix = config_item('db_prefix3');
		$this->prefix_app = config_item('db_prefix2');
		$this->table_account_payable = $this->prefix.'account_payable';
		$this->table_po = $this->prefix_app.'po';
		$this->table_po_detail = $this->prefix_app.'po_detail';
		$this->table_supplier = $this->prefix_app.'supplier';
	}
	
	function set_account_payable_PO($po_id = ''){
		
		$session_user = $this->session->userdata('user_username');
		
		if(empty($po_id)){
			return false;
		}
		
		$this->db->select("a.*, b.supplier_name, b.supplier_phone, b.supplier_address");
		$this->db->from($this->table_po.' as a');
		$this->db->join($this->table_supplier.' as b', "b.id = a.supplier_id", "LEFT");
		$this->db->where("a.id = '".$po_id."'");
		$get_po = $this->db->get();
		if($get_po->num_rows() > 0){
			
			$data_PO = $get_po->row();
			
			if($data_PO->po_payment == 'credit'){
				
				$data_post = array();
				
				//get detail PO
				$all_qty_price = 0;
				$data_PO->po_total_price = 0;
				$this->db->from($this->table_po_detail);
				$this->db->where("po_id = '".$po_id."'");
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
				
				$this->db->from($this->table_account_payable);
				$this->db->where("ap_tipe = 'purchasing'");
				$this->db->where("po_id = '".$po_id."'");
				$get_ap = $this->db->get();
				if($get_ap->num_rows() > 0){
					
					$data_AP = $get_ap->row();
					
					//update AP
					$data_post = array(
						'ap_name'	=> $data_PO->supplier_name,
						'ap_date'	=> date('Y-m-d'),
						'ap_phone'	=> $data_PO->supplier_phone,
						'ap_address'	=> $data_PO->supplier_address,
						'supplier_id'	=> $data_PO->supplier_id,
						'no_ref'		=> $data_PO->po_number,
						'updated'		=>	date('Y-m-d H:i:s'),
						'updatedby'		=>	$session_user
					);
					
					if($data_PO->po_status == 'done'){
						
						
						if($data_AP->ap_status == 'pengakuan'){
							//update AP
							$data_post['total_tagihan'] = $data_PO->po_total_price;
						}
						
						if($data_AP->is_deleted == 1){
							$data_post['is_active'] = 1;
							$data_post['is_deleted'] = 0;
						}
						
						if($data_AP->total_tagihan != $data_PO->po_total_price){
							$data_post['total_tagihan'] = $data_PO->po_total_price;
						}
						
					}else{
						
						$data_post['total_tagihan'] = $data_PO->po_total_price;
						$data_post['is_active'] = 0;
						$data_post['is_deleted'] = 1;
						
					}
					
				}else{
					
					if($data_PO->po_status == 'done'){
						
						//create new AP
						$data_post = array(
							'ap_name'	=> $data_PO->supplier_name,
							'ap_date'	=> date('Y-m-d'),
							'ap_phone'	=> $data_PO->supplier_phone,
							'ap_address'	=> $data_PO->supplier_address,
							'ap_tipe'	=> 'purchasing',
							'ap_status'	=> 'pengakuan',
							'po_id'			=> $data_PO->id,
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
					if(!empty($data_AP->id)){
						
						$add_ap = $this->db->update($this->table_account_payable, $data_post, "id = '".$data_AP->id."'");
						if($add_ap == false){
							$return = false;
						}
						
					}else{
						
						$get_ap_no = $this->generate_ap_number();
						$data_post['ap_no'] = $get_ap_no;
						
						$add_ap = $this->db->insert($this->table_account_payable, $data_post);
						if($add_ap == false){
							$return = false;
						}
						
					}
				}
				
				return $return;
				
			}else{
				
				$this->db->from($this->table_account_payable);
				$this->db->where("ap_tipe = 'purchasing'");
				$this->db->where("po_id = '".$po_id."'");
				$get_ap = $this->db->get();
				if($get_ap->num_rows() > 0){
					
					$data_AP = $get_ap->row();
					
					//update AP
					$data_post = array(
						'ap_name'	=> $data_PO->supplier_name,
						'ap_date'	=> date('Y-m-d'),
						'ap_phone'	=> $data_PO->supplier_phone,
						'ap_address'	=> $data_PO->supplier_address,
						'supplier_id'	=> $data_PO->supplier_id,
						'no_ref'		=> $data_PO->po_number,
						'updated'		=>	date('Y-m-d H:i:s'),
						'updatedby'		=>	$session_user
					);
					
					$return = false;
					if($data_PO->po_status == 'done'){
						
						if($data_AP->total_tagihan != $data_PO->po_total_price){
							$data_AP->total_tagihan = $data_PO->po_total_price;
							
							//update AP
							$data_post = array();
							$data_post['total_tagihan'] = $data_PO->po_total_price;
							
							$this->db->update($this->table_account_payable, $data_post, "id = '".$data_AP->id."'");
						}
						
						if($data_AP->ap_status == 'pengakuan' OR $data_AP->ap_status == 'posting'){
							//update AP
							$data_post['total_tagihan'] = $data_PO->po_total_price;
							$data_post['is_active'] = 0;
							$data_post['is_deleted'] = 1;
							
							$return = true;
							
							$this->db->update($this->table_account_payable, $data_post, "id = '".$data_AP->id."'");
							
							return $return;
							
						}else{
							$return = 'kontrabon';
							return $return;
						}
						
					}
					
					return $return;
				}
			}
			
			
		}
		
	}
	
	public function generate_ap_number(){
		
		$get_date = 'AP'.date("Ym");
		
		$this->db->from($this->table_account_payable);
		$this->db->where("ap_no LIKE '".$get_date."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_ap = $get_last->row();
			$ap_number = str_replace($get_date,"", $data_ap->ap_no);
			$ap_number = (int) $ap_number;			
		}else{
			$ap_number = 0;
		}
		
		$ap_number++;
		$length_no = strlen($ap_number);
		switch ($length_no) {
			case 3:
				$ap_number = $ap_number;
				break;
			case 2:
				$ap_number = '0'.$ap_number;
				break;
			case 1:
				$ap_number = '00'.$ap_number;
				break;
			default:
				$ap_number = '00'.$ap_number;
				break;
		}
				
		return $get_date.$ap_number;				
	}

} 