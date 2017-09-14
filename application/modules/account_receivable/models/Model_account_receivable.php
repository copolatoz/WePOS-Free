<?php
class Model_account_receivable extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		
		$this->prefix = config_item('db_prefix3');
		$this->prefix_app = config_item('db_prefix2');
		$this->table_account_receivable = $this->prefix.'account_receivable';
		$this->table_salesorder = $this->prefix_app.'salesorder';
		$this->table_salesorder_detail = $this->prefix_app.'salesorder_detail';
		$this->table_customer = $this->prefix_app.'customer';
	}
	
	function set_account_receivable_SO($so_id = ''){
		
		$session_user = $this->session->userdata('user_username');
		
		if(empty($so_id)){
			return false;
		}
		
		$this->db->select("a.*, so_customer_name as customer_name, so_customer_phone as customer_phone, so_customer_address as customer_address");
		$this->db->from($this->table_salesorder.' as a');
		//$this->db->join($this->table_customer.' as b', "b.id = a.customer_id", "LEFT");
		$this->db->where("a.id = '".$so_id."'");
		$get_so = $this->db->get();
		if($get_so->num_rows() > 0){
			
			$data_SO = $get_so->row();
			
			if($data_SO->so_payment == 'credit_ar'){
				
				$data_post = array();
				
				//get detail PO
				$all_qty_price = 0;
				$data_SO->so_total_price = 0;
				$this->db->from($this->table_salesorder_detail);
				$this->db->where("so_id = '".$so_id."'");
				$get_so_det = $this->db->get();
				if($get_so_det->num_rows() > 0){
					foreach($get_so_det->result() as $det){
						//$all_qty_price += (($det->sales_price - $det->sod_potongan)*$det->sod_qty);
						$all_qty_price += ($det->sales_price*$det->sod_qty);
					}
					
					$data_SO->so_total_price = $all_qty_price;
					$data_SO->so_total_price -= $data_SO->so_discount;
					$data_SO->so_total_price += $data_SO->so_tax;
					$data_SO->so_total_price += $data_SO->so_shipping;
					$data_SO->so_total_price -= $data_SO->so_dp;
				}
				
				$this->db->from($this->table_account_receivable);
				$this->db->where("ar_tipe = 'salesorder'");
				$this->db->where("so_id = '".$so_id."'");
				$get_ar = $this->db->get();
				if($get_ar->num_rows() > 0){
					
					$data_AR = $get_ar->row();
					
					//update AR
					$data_post = array(
						'ar_name'	=> $data_SO->customer_name,
						'ar_date'	=> date('Y-m-d'),
						'ar_phone'	=> $data_SO->customer_phone,
						'ar_address'	=> $data_SO->customer_address,
						'customer_id'	=> $data_SO->customer_id,
						'no_ref'		=> $data_SO->so_number,
						'updated'		=>	date('Y-m-d H:i:s'),
						'updatedby'		=>	$session_user
					);
					
					if($data_SO->so_status == 'done'){
						
						
						if($data_AR->ar_status == 'pengakuan'){
							//update AR
							$data_post['total_tagihan'] = $data_SO->so_total_price;
						}
						
						if($data_AR->is_deleted == 1){
							$data_post['is_active'] = 1;
							$data_post['is_deleted'] = 0;
						}
						
					}else{
						
						$data_post['total_tagihan'] = $data_SO->so_total_price;
						$data_post['is_active'] = 0;
						$data_post['is_deleted'] = 1;
						
					}
					
				}else{
					
					if($data_SO->so_status == 'done'){
						
						//create new AR
						$data_post = array(
							'ar_name'	=> $data_SO->customer_name,
							'ar_date'	=> date('Y-m-d'),
							'ar_phone'	=> $data_SO->customer_phone,
							'ar_address'	=> $data_SO->customer_address,
							'ar_tipe'	=> 'salesorder',
							'ar_status'	=> 'pengakuan',
							'so_id'			=> $data_SO->id,
							'customer_id'	=> $data_SO->customer_id,
							'no_ref'		=> $data_SO->so_number,
							'total_tagihan'	=> $data_SO->so_total_price,
							'created'		=>	date('Y-m-d H:i:s'),
							'createdby'		=>	$session_user,
							'updated'		=>	date('Y-m-d H:i:s'),
							'updatedby'		=>	$session_user
						);
						
					}
					
  
				}
				
				$return = true;
				if(!empty($data_post)){
					if(!empty($data_AR->id)){
						
						$add_ar = $this->db->update($this->table_account_receivable, $data_post, "id = '".$data_AR->id."'");
						if($add_ar == false){
							$return = false;
						}
						
					}else{
						
						$get_ar_no = $this->generate_ar_number();
						$data_post['ar_no'] = $get_ar_no;
						
						$add_ar = $this->db->insert($this->table_account_receivable, $data_post);
						if($add_ar == false){
							$return = false;
						}
						
					}
				}
				
				
			}
			
			
		}
		
	}
	
	public function generate_ar_number(){
		
		$get_date = 'AR'.date("Ym");
		
		$this->db->from($this->table_account_receivable);
		$this->db->where("ar_no LIKE '".$get_date."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_ar = $get_last->row();
			$ar_number = str_replace($get_date,"", $data_ar->ar_no);
			$ar_number = (int) $ar_number;			
		}else{
			$ar_number = 0;
		}
		
		$ar_number++;
		$length_no = strlen($ar_number);
		switch ($length_no) {
			case 3:
				$ar_number = $ar_number;
				break;
			case 2:
				$ar_number = '0'.$ar_number;
				break;
			case 1:
				$ar_number = '00'.$ar_number;
				break;
			default:
				$ar_number = '00'.$ar_number;
				break;
		}
				
		return $get_date.$ar_number;				
	}

} 