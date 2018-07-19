<?php
class Model_penerimaan_kas extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		
		$this->prefix = config_item('db_prefix3');
		$this->prefix_app = config_item('db_prefix2');
		$this->table_penerimaan_kas = $this->prefix.'penerimaan_kas';
		$this->table_reservation = $this->prefix_app.'reservation';
		$this->table_reservation_detail = $this->prefix_app.'reservation_detail';
		$this->table_po = $this->prefix_app.'po';
		$this->table_po_detail = $this->prefix_app.'po_detail';
		$this->table_supplier = $this->prefix_app.'supplier';
	}
	
	function set_penerimaan_kas_PO($ref_id = ''){
		
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
				
				$this->db->from($this->table_penerimaan_kas);
				$this->db->where("km_tujuan = 'reservation'");
				$this->db->where("ref_id = '".$ref_id."'");
				$get_ap = $this->db->get();
				if($get_ap->num_rows() > 0){
					
					$data_KM = $get_ap->row();
					
					//update KM
					$data_post = array(
						'km_name'	=> $data_PO->supplier_name,
						'km_date'	=> date('Y-m-d'),
						'km_phone'	=> $data_PO->supplier_phone,
						'km_address'	=> $data_PO->supplier_address,
						'supplier_id'	=> $data_PO->supplier_id,
						'no_ref'		=> $data_PO->po_number,
						'updated'		=>	date('Y-m-d H:i:s'),
						'updatedby'		=>	$session_user
					);
					
					if($data_PO->po_status == 'done'){
						
						
						if($data_KM->km_status == 'pengakuan'){
							//update KM
							$data_post['total_tagihan'] = $data_PO->po_total_price;
						}
						
						if($data_KM->is_deleted == 1){
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
						
						//create new KM
						$data_post = array(
							'km_name'	=> $data_PO->supplier_name,
							'km_date'	=> date('Y-m-d'),
							'km_phone'	=> $data_PO->supplier_phone,
							'km_address'	=> $data_PO->supplier_address,
							'km_tujuan'	=> 'reservation',
							'km_status'	=> 'pengakuan',
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
					if(!empty($data_KM->id)){
						
						$add_ap = $this->db->update($this->table_penerimaan_kas, $data_post, "id = '".$data_KM->id."'");
						if($add_ap == false){
							$return = false;
						}
						
					}else{
						
						$get_km_no = $this->generate_km_number();
						$data_post['km_no'] = $get_km_no;
						
						$add_ap = $this->db->insert($this->table_penerimaan_kas, $data_post);
						if($add_ap == false){
							$return = false;
						}
						
					}
				}
				
				
			}
			
			
		}
		
	}
	
	function set_DP_Reservation($ref_id = '', $old_status = ''){
		
		$session_user = $this->session->userdata('user_username');
		$session_firstname = $this->session->userdata('user_firstname');
		
		if(empty($ref_id)){
			return false;
		}
		
		//get opt
		$opt_val = array(
			'tujuan_penerimaan_dp_reservation','jenis_penerimaan_dp_reservation'
		);
		
		$get_opt = get_option_value($opt_val);
		$tujuan_penerimaan_dp_reservation = 0;
		$jenis_penerimaan_dp_reservation = 0;
		
		if(!empty($get_opt['tujuan_penerimaan_dp_reservation'])){
			$tujuan_penerimaan_dp_reservation = $get_opt['tujuan_penerimaan_dp_reservation'];
		}
		if(!empty($get_opt['jenis_penerimaan_dp_reservation'])){
			$jenis_penerimaan_dp_reservation = $get_opt['jenis_penerimaan_dp_reservation'];
		}
		
		if(empty($tujuan_penerimaan_dp_reservation) OR empty($jenis_penerimaan_dp_reservation)){
			return false;
		}
		
		$this->db->select("a.*, reservation_customer_name as customer_name, reservation_customer_phone as customer_phone, reservation_customer_address as customer_address");
		$this->db->from($this->table_reservation.' as a');
		//$this->db->join($this->table_customer.' as b', "b.id = a.customer_id", "LEFT");
		$this->db->where("a.id = '".$ref_id."'");
		$get_so = $this->db->get();
		if($get_so->num_rows() > 0){
			
			$data_Reservation = $get_so->row();
			
			if($data_Reservation->reservation_payment == 'credit_ar' AND $old_status != 'done'){
				
				$data_post = array();
				
				//get detail Res
				$all_qty_price = 0;
				$data_Reservation->reservation_total_dp = 0;
				$this->db->from($this->table_reservation_detail);
				$this->db->where("reservation_id = '".$ref_id."'");
				$get_reservation_det = $this->db->get();
				if($get_reservation_det->num_rows() > 0){
					foreach($get_reservation_det->result() as $det){
						//$all_qty_price += (($det->resd_price - $det->resd_potongan)*$det->resd_qty);
						$all_qty_price += ($det->resd_price*$det->resd_qty);
					}
					
					$data_Reservation->reservation_total_dp += $data_Reservation->reservation_dp;
				}
				
				$this->db->from($this->table_penerimaan_kas);
				$this->db->where("km_tipe = 'salesorder'");
				$this->db->where("ref_id = '".$ref_id."'");
				$get_ar = $this->db->get();
				if($get_ar->num_rows() > 0){
					
					$data_KM = $get_ar->row();
					
					//update AR
					$data_post = array(
						'km_atasnama'	=> strtoupper($data_Reservation->customer_name),
						'km_date'		=> date('Y-m-d'),
						'km_notes'		=> $data_Reservation->customer_phone,
						//'ref_id'		=> $data_Reservation->id,
						//'no_ref'		=> $data_Reservation->reservation_number,
						'km_tujuan'		=> $tujuan_penerimaan_dp_reservation,
						'autoposting_id'=> $jenis_penerimaan_dp_reservation,
						'km_name'		=> strtoupper($session_firstname),
						'updated'		=> date('Y-m-d H:i:s'),
						'updatedby'		=> $session_user
					);
					
					if($data_Reservation->reservation_status == 'done'){
						
						if($data_KM->km_status == 'pengakuan'){
							//update AR
							$data_post['km_total'] = $data_Reservation->reservation_dp;
						}
						
						if($data_KM->is_deleted == 1){
							$data_post['km_status'] = 'pengakuan';
							$data_post['is_active'] = 1;
							$data_post['is_deleted'] = 0;
						}
						
					}else{
						
						$data_post['km_total'] = $data_Reservation->reservation_dp;
						$data_post['is_active'] = 0;
						$data_post['is_deleted'] = 1;
						
					}
					
				}else{
					
					if($data_Reservation->reservation_status == 'done'){
						
						//create new AR
						$data_post = array(
							'km_atasnama'	=> strtoupper($data_Reservation->customer_name),
							'km_date'		=> date('Y-m-d'),
							'km_notes'		=> $data_Reservation->customer_phone,
							'km_tipe'		=> 'salesorder',
							'km_status'		=> 'pengakuan',
							'ref_id'		=> $data_Reservation->id,
							'no_ref'		=> $data_Reservation->reservation_number,
							'km_tujuan'		=> $tujuan_penerimaan_dp_reservation,
							'autoposting_id'=> $jenis_penerimaan_dp_reservation,
							'km_name'		=> strtoupper($session_firstname),
							'km_total'		=> $data_Reservation->reservation_dp,
							'created'		=>	date('Y-m-d H:i:s'),
							'createdby'		=>	$session_user,
							'updated'		=>	date('Y-m-d H:i:s'),
							'updatedby'		=>	$session_user
						);
						
					}
					
  
				}
				
				$return = true;
				if(!empty($data_post)){
					if(!empty($data_KM->id)){
						
						$add_ar = $this->db->update($this->table_penerimaan_kas, $data_post, "id = '".$data_KM->id."'");
						if($add_ar == false){
							$return = false;
						}
						
					}else{
						
						$get_km_no = $this->generate_km_number();
						$data_post['km_no'] = $get_km_no;
						
						$add_ar = $this->db->insert($this->table_penerimaan_kas, $data_post);
						if($add_ar == false){
							$return = false;
						}
						
					}
				}
				
				
			}else{
				
				$this->db->from($this->table_penerimaan_kas);
				$this->db->where("km_tipe = 'salesorder'");
				$this->db->where("ref_id = '".$ref_id."'");
				$get_ar = $this->db->get();
				if($get_ar->num_rows() > 0){
					
					$data_KM = $get_ar->row();
					
					//update AP
					$data_post = array(
						'km_atasnama'	=> strtoupper($data_Reservation->customer_name),
						'km_date'	=> date('Y-m-d'),
						'km_notes'	=> $data_Reservation->customer_phone,
						//'no_ref'	=> $data_Reservation->reservation_number,
						//'ref_id'	=> $data_Reservation->id,
						'km_tujuan'		=> $tujuan_penerimaan_dp_reservation,
						'autoposting_id'=> $jenis_penerimaan_dp_reservation,
						'km_name'		=> strtoupper($session_firstname),
						'updated'	=>	date('Y-m-d H:i:s'),
						'updatedby'	=>	$session_user
					);
					
					$return = false;
					if($old_status == 'done'){
						
						if($data_KM->km_status == 'pengakuan' OR $data_KM->km_status == 'posting'){
							//update AP
							$data_post['km_total'] = $data_Reservation->reservation_dp;
							$data_post['is_active'] = 0;
							$data_post['is_deleted'] = 1;
							$data_post['km_status'] = 'pengakuan';
							
							$return = true;
							
							$this->db->update($this->table_penerimaan_kas, $data_post, "id = '".$data_KM->id."'");
							
							return $return;
							
						}else{
							$return = 'invoice';
							return $return;
						}
						
					}
					
					return $return;
				}
			}
			
			
		}
		
	}
	
	
	function set_DP_Sales($ref_id = '', $old_status = ''){
		
		$session_user = $this->session->userdata('user_username');
		$session_firstname = $this->session->userdata('user_firstname');
		
		if(empty($ref_id)){
			return false;
		}
		
		//get opt
		$opt_val = array(
			'tujuan_penerimaan_dp_sales','jenis_penerimaan_dp_sales'
		);
		
		$get_opt = get_option_value($opt_val);
		$tujuan_penerimaan_dp_billing = 0;
		$jenis_penerimaan_dp_billing = 0;
		
		if(!empty($get_opt['tujuan_penerimaan_dp_billing'])){
			$tujuan_penerimaan_dp_billing = $get_opt['tujuan_penerimaan_dp_billing'];
		}
		if(!empty($get_opt['jenis_penerimaan_dp_billing'])){
			$jenis_penerimaan_dp_billing = $get_opt['jenis_penerimaan_dp_billing'];
		}
		
		if(empty($tujuan_penerimaan_dp_billing) OR empty($jenis_penerimaan_dp_billing)){
			return false;
		}
		
		$this->db->select("a.*, billing_customer_name as customer_name, billing_customer_phone as customer_phone, billing_customer_address as customer_address");
		$this->db->from($this->table_billing.' as a');
		//$this->db->join($this->table_customer.' as b', "b.id = a.customer_id", "LEFT");
		$this->db->where("a.id = '".$ref_id."'");
		$get_so = $this->db->get();
		if($get_so->num_rows() > 0){
			
			$data_Reservation = $get_so->row();
			
			if($data_Reservation->billing_payment == 'credit_ar' AND $old_status != 'done'){
				
				$data_post = array();
				
				//get detail Res
				$all_qty_price = 0;
				$data_Reservation->billing_total_dp = 0;
				$this->db->from($this->table_billing_detail);
				$this->db->where("billing_id = '".$ref_id."'");
				$get_billing_det = $this->db->get();
				if($get_billing_det->num_rows() > 0){
					foreach($get_billing_det->result() as $det){
						//$all_qty_price += (($det->resd_price - $det->resd_potongan)*$det->resd_qty);
						$all_qty_price += ($det->resd_price*$det->resd_qty);
					}
					
					$data_Reservation->billing_total_dp += $data_Reservation->billing_dp;
				}
				
				$this->db->from($this->table_penerimaan_kas);
				$this->db->where("km_tipe = 'salesorder'");
				$this->db->where("ref_id = '".$ref_id."'");
				$get_ar = $this->db->get();
				if($get_ar->num_rows() > 0){
					
					$data_KM = $get_ar->row();
					
					//update AR
					$data_post = array(
						'km_atasnama'	=> strtoupper($data_Reservation->customer_name),
						'km_date'		=> date('Y-m-d'),
						'km_notes'		=> $data_Reservation->customer_phone,
						//'ref_id'		=> $data_Reservation->id,
						//'no_ref'		=> $data_Reservation->billing_number,
						'km_tujuan'		=> $tujuan_penerimaan_dp_billing,
						'autoposting_id'=> $jenis_penerimaan_dp_billing,
						'km_name'		=> strtoupper($session_firstname),
						'updated'		=> date('Y-m-d H:i:s'),
						'updatedby'		=> $session_user
					);
					
					if($data_Reservation->billing_status == 'done'){
						
						if($data_KM->km_status == 'pengakuan'){
							//update AR
							$data_post['km_total'] = $data_Reservation->billing_dp;
						}
						
						if($data_KM->is_deleted == 1){
							$data_post['km_status'] = 'pengakuan';
							$data_post['is_active'] = 1;
							$data_post['is_deleted'] = 0;
						}
						
					}else{
						
						$data_post['km_total'] = $data_Reservation->billing_dp;
						$data_post['is_active'] = 0;
						$data_post['is_deleted'] = 1;
						
					}
					
				}else{
					
					if($data_Reservation->billing_status == 'done'){
						
						//create new AR
						$data_post = array(
							'km_atasnama'	=> strtoupper($data_Reservation->customer_name),
							'km_date'		=> date('Y-m-d'),
							'km_notes'		=> $data_Reservation->customer_phone,
							'km_tipe'		=> 'salesorder',
							'km_status'		=> 'pengakuan',
							'ref_id'		=> $data_Reservation->id,
							'no_ref'		=> $data_Reservation->billing_number,
							'km_tujuan'		=> $tujuan_penerimaan_dp_billing,
							'autoposting_id'=> $jenis_penerimaan_dp_billing,
							'km_name'		=> strtoupper($session_firstname),
							'km_total'		=> $data_Reservation->billing_dp,
							'created'		=>	date('Y-m-d H:i:s'),
							'createdby'		=>	$session_user,
							'updated'		=>	date('Y-m-d H:i:s'),
							'updatedby'		=>	$session_user
						);
						
					}
					
  
				}
				
				$return = true;
				if(!empty($data_post)){
					if(!empty($data_KM->id)){
						
						$add_ar = $this->db->update($this->table_penerimaan_kas, $data_post, "id = '".$data_KM->id."'");
						if($add_ar == false){
							$return = false;
						}
						
					}else{
						
						$get_km_no = $this->generate_km_number();
						$data_post['km_no'] = $get_km_no;
						
						$add_ar = $this->db->insert($this->table_penerimaan_kas, $data_post);
						if($add_ar == false){
							$return = false;
						}
						
					}
				}
				
				
			}else{
				
				$this->db->from($this->table_penerimaan_kas);
				$this->db->where("km_tipe = 'salesorder'");
				$this->db->where("ref_id = '".$ref_id."'");
				$get_ar = $this->db->get();
				if($get_ar->num_rows() > 0){
					
					$data_KM = $get_ar->row();
					
					//update AP
					$data_post = array(
						'km_atasnama'	=> strtoupper($data_Reservation->customer_name),
						'km_date'	=> date('Y-m-d'),
						'km_notes'	=> $data_Reservation->customer_phone,
						//'no_ref'	=> $data_Reservation->billing_number,
						//'ref_id'	=> $data_Reservation->id,
						'km_tujuan'		=> $tujuan_penerimaan_dp_billing,
						'autoposting_id'=> $jenis_penerimaan_dp_billing,
						'km_name'		=> strtoupper($session_firstname),
						'updated'	=>	date('Y-m-d H:i:s'),
						'updatedby'	=>	$session_user
					);
					
					$return = false;
					if($old_status == 'done'){
						
						if($data_KM->km_status == 'pengakuan' OR $data_KM->km_status == 'posting'){
							//update AP
							$data_post['km_total'] = $data_Reservation->billing_dp;
							$data_post['is_active'] = 0;
							$data_post['is_deleted'] = 1;
							$data_post['km_status'] = 'pengakuan';
							
							$return = true;
							
							$this->db->update($this->table_penerimaan_kas, $data_post, "id = '".$data_KM->id."'");
							
							return $return;
							
						}else{
							$return = 'invoice';
							return $return;
						}
						
					}
					
					return $return;
				}
			}
			
			
		}
		
	}
	
	public function generate_km_number(){
		
		$get_date = 'KM'.date("Ym");
		
		$this->db->from($this->table_penerimaan_kas);
		$this->db->where("km_no LIKE '".$get_date."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_ap = $get_last->row();
			$km_number = str_replace($get_date,"", $data_ap->km_no);
			$km_number = (int) $km_number;			
		}else{
			$km_number = 0;
		}
		
		$km_number++;
		$length_no = strlen($km_number);
		switch ($length_no) {
			case 3:
				$km_number = $km_number;
				break;
			case 2:
				$km_number = '0'.$km_number;
				break;
			case 1:
				$km_number = '00'.$km_number;
				break;
			default:
				$km_number = '00'.$km_number;
				break;
		}
				
		return $get_date.$km_number;				
	}

} 