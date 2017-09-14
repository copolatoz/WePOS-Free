<?php
class Model_BillingCashierDetail extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'billing_detail';
	}
	
	function billingDetail($billing_id = '', $retail_warehouse, $update_stok = ''){
		
		$session_user = $this->session->userdata('user_username');
		
		$update_stock_item_unit = array();
		
		
		if(!empty($billing_id) AND !empty($retail_warehouse)){
			
			
			$billingDetail = array();
			$this->db->select("a.*, b.billing_status, c.from_item, c.id_ref_item, d.item_hpp, d.unit_id");
			$this->db->from($this->prefix.'billing_detail as a');
			$this->db->join($this->prefix.'billing as b',"b.id = a.billing_id","LEFT");
			$this->db->join($this->prefix.'product as c',"c.id = a.product_id","LEFT");
			$this->db->join($this->prefix.'items as d',"d.id = c.id_ref_item","LEFT");
			$this->db->where("a.billing_id", $billing_id);
			$this->db->where("a.order_status != 'cancel'");
			$get_det = $this->db->get();
			if($get_det->num_rows() > 0){
				$billingDetail = $get_det->result_array();
			}
			
			//insert batch
			$this->db->from($this->prefix.'billing');
			$this->db->where("id", $billing_id);
			$get_rowguid = $this->db->get();
			if($get_rowguid->num_rows() > 0){
				$dt_rowguid = $get_rowguid->row_array();
				
				
				//BILLING
				$billing_no = $dt_rowguid['billing_no'];
				$payment_date = strtotime($dt_rowguid['payment_date']);
				$payment_date = date("Y-m-d", $payment_date);
					
				$update_all_detail_storehouse = array(
					'storehouse_id'	=> $retail_warehouse
				);
				$this->db->update($this->prefix."billing_detail", $update_all_detail_storehouse, "billing_id = ".$billing_id); 	
			}
			
			$dtInsert_stock = array();
			
			$dtUpdate_kode_unik = array();
			$all_unik_kode = array();
			
			if(!empty($dt_rowguid) AND !empty($billingDetail)){
				
				foreach($billingDetail as $dt){
					
					//UNIK KODE
					if($dt['use_stok_kode_unik'] == 1){
						$list_dt_kode = explode("\n",$dt['data_stok_kode_unik']);
						foreach($list_dt_kode as $kode_unik){
							if(!empty($kode_unik)){
								if(!in_array($kode_unik, $all_unik_kode)){
									$all_unik_kode[] = $kode_unik;
									
									$dtUpdate_kode_unik[] = array(
										"item_id" => $dt['id_ref_item'],
										"kode_unik" => $kode_unik,
										"ref_out" => $billing_no,
										"date_out" => $payment_date.' '.date("H:i:s"),
										"storehouse_id" => $retail_warehouse
									);
									
								}
							}
							
						}
						
					}
					
					//SURE ONLY UPDATE!
					if(($update_stok == 'update' OR $update_stok == 'rollback') AND !empty($dt['order_qty'])){
						//DELIVERY
						$billing_trx_type = 'out';
						$billing_trx_qty = $dt['order_qty'];
						
						if(empty($update_stock_item_unit[$retail_warehouse])){
							$update_stock_item_unit[$retail_warehouse] = array();
						}
						
						$update_stock_item_unit[$retail_warehouse][] = $dt['id_ref_item'];
						
						$dtInsert_stock[] = array(
							"item_id" => $dt['id_ref_item'],
							"trx_date" => $payment_date,
							"trx_type" => $billing_trx_type,
							"trx_qty" => $billing_trx_qty,
							"unit_id" => $dt['unit_id'],
							"storehouse_id" => $retail_warehouse,
							"trx_nominal" => $dt['item_hpp'],
							"trx_note" => 'Sales',
							"trx_ref_data" => $billing_no,
							"trx_ref_det_id" => $dt['id'],
							"is_active" => "1"
						);
						
						
					}
					
				}
			}
			
			if($update_stok == 'update' OR $update_stok == 'rollback'){
				
				if($update_stok == 'rollback'){
					//DELETE ALL STOCK
					$this->db->where("trx_ref_data", $billing_no);
					$this->db->delete($this->prefix."stock"); 
					
					$unik_stok = array(
						'ref_out' => NULL,
						'date_out' => NULL,
					);
					$this->db->update($this->prefix."item_kode_unik", $unik_stok, "ref_out = '".$billing_no."'"); 
					
				}else{
					//UPDATE STOCK TRX
					if(!empty($dtInsert_stock)){
						$this->db->insert_batch($this->prefix.'stock', $dtInsert_stock);
						
						if(!empty($dtUpdate_kode_unik)){
							$this->db->update_batch($this->prefix.'item_kode_unik', $dtUpdate_kode_unik, "kode_unik");
						}
					}
				}
			}
			
			return array('update_stock' => $update_stock_item_unit);
		}
	}
	
	function getItem($billing_id = '', $storehouse = ''){
		
		if(empty($billing_id) OR empty($storehouse)){
			return array();
		}
		
		$storehouse_item = array($storehouse => array());
		$storehouse_item_qty = array($storehouse => array());
		$storehouse_item_qty_before = array($storehouse => array());
		
		if(!empty($billing_id)){
			$this->db->select("a.*, b.billing_status, c.from_item, c.id_ref_item");
			$this->db->from($this->prefix.'billing_detail as a');
			$this->db->join($this->prefix.'billing as b',"b.id = a.billing_id","LEFT");
			$this->db->join($this->prefix.'product as c',"c.id = a.product_id","LEFT");
			$this->db->where("a.billing_id", $billing_id);
			$this->db->where("b.order_status", 'done');
			$get_det = $this->db->get();
			if($get_det->num_rows() > 0){
				foreach($get_det->result_array() as $dt){
					
					if($dt['billing_status'] == 'paid'){
						if(!in_array($dt['id_ref_item'], $storehouse_item[$storehouse])){
							$storehouse_item[$storehouse][] = $dt['id_ref_item'];
							$storehouse_item_qty[$storehouse][$dt['id_ref_item']] = $dt['order_qty'];
							$storehouse_item_qty_before[$storehouse][$dt['id_ref_item']] = $dt['order_qty'];
						}
					}
				}
			}
		}
		
		$ret_data = array(
			'storehouse' => $storehouse, 
			'storehouse_item' => $storehouse_item, 
			'storehouse_item_qty' => $storehouse_item_qty, 
			'storehouse_item_qty_before' => $storehouse_item_qty_before
		);
		
		return $ret_data;
	}

} 