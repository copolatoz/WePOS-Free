<?php
class Model_BillingCashierDetail extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'billing_detail';
		$this->table_product_gramasi = $this->prefix.'product_gramasi';
		$this->table_product_package = $this->prefix.'product_package';
		$this->table_product = $this->prefix.'product';	
		$this->table_items = $this->prefix.'items';	
	}
	
	function billingDetail($billing_id = '', $retail_warehouse, $update_stok = ''){
		
		$session_user = $this->session->userdata('user_username');
		
		$update_stock_item_unit = array();
		
		
		if(!empty($billing_id) AND !empty($retail_warehouse)){
			
			
			$billingDetail = array();
			$this->db->select("a.*, b.billing_status, c.from_item, c.id_ref_item, c.product_type, d.item_hpp, d.unit_id");
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
			
			//update 2019-02-13
			$all_product_order = array();
			$all_product_order_package = array();
			$all_product_gramasi_package = array();
			$all_product_gramasi_package_qty = array();
			$all_product_gramasi_package_varian_item = array();
			$all_product_package_varian = array();
			$all_product_package_qty = array();
			$all_product_package_empty = array();
			$all_product_gramasi = array();
			$all_product_varian = array();
			$all_product_qty = array();
			
			if(!empty($dt_rowguid) AND !empty($billingDetail)){
				
				foreach($billingDetail as $dt){
					
					$dtRow = (object) $dt;
					
					$total_qty = $dtRow->order_qty;
					
					//update 2019-02-11
					//NO-PACKAGE
					if($dtRow->product_type == 'item' AND !empty($dtRow->order_qty)){
						if(empty($dtRow->varian_id)){
							$dtRow->varian_id = 0;
						}
						$key_prod_varian = $dtRow->product_id.'_'.$dtRow->varian_id;
						if(empty($all_product_order[$key_prod_varian])){
							$all_product_order[$key_prod_varian] = array(
								'product_id'	=> $dtRow->product_id,
								'from_item'		=> $dtRow->from_item,
								'id_ref_item'	=> $dtRow->id_ref_item,
								'unit_id'		=> $dtRow->unit_id,
								'varian_id'		=> $dtRow->varian_id,
								'price_hpp'		=> 0,
								'product_price'	=> 0,
								'qty'			=> 0
							);
						}
						
						$all_product_order[$key_prod_varian]['qty'] += $total_qty;
						$all_product_order[$key_prod_varian]['price_hpp'] += ($dtRow->product_price_hpp * $total_qty);
						$all_product_order[$key_prod_varian]['product_price'] += 0;
						
						if(!in_array($dt['product_id'], $all_product_gramasi)){
							$all_product_gramasi[] = $dt['product_id'];
						}
						
						if(!in_array($key_prod_varian, $all_product_varian)){
							$all_product_varian[] = $key_prod_varian;
						}
						
						if(empty($all_product_qty[$key_prod_varian])){
							$all_product_qty[$key_prod_varian] = 0;
						}
						
						$all_product_qty[$key_prod_varian] += $total_qty;
						
					}
					
					//PACKAGE
					if($dtRow->product_type == 'package' AND !empty($dtRow->order_qty)){
						//get all product package / default product
						if(empty($dtRow->varian_id)){
							$dtRow->varian_id = 0;
						}
						$key_prod_varian = $dtRow->product_id.'_'.$dtRow->varian_id;
						if(empty($all_product_order_package[$key_prod_varian])){
							$all_product_order_package[$key_prod_varian] = array(
								'product_id'	=> $dtRow->product_id,
								'from_item'		=> $dtRow->from_item,
								'id_ref_item'	=> $dtRow->id_ref_item,
								'unit_id'		=> $dtRow->unit_id,
								'varian_id'		=> $dtRow->varian_id,
								'price_hpp'		=> 0,
								'product_price'	=> 0,
								'qty'			=> 0
							);
						}
						
						$all_product_order_package[$key_prod_varian]['qty'] += $total_qty;
						$all_product_order_package[$key_prod_varian]['price_hpp'] += ($dtRow->product_price_hpp * $total_qty);
						$all_product_order_package[$key_prod_varian]['product_price'] += 0;
						
						if(!in_array($key_prod_varian, $all_product_package_varian)){
							$all_product_package_varian[] = $key_prod_varian;
						}
						
						if(empty($all_product_package_qty[$key_prod_varian])){
							$all_product_package_qty[$key_prod_varian] = 0;
						}
						
						$all_product_package_qty[$key_prod_varian] += $total_qty;
						
						$this->db->select("a.*");
						$this->db->from($this->table_product_package." as a");
						$this->db->where("a.package_id IN (".$dtRow->product_id.") AND a.varian_id = '".$dtRow->varian_id."'");
						$this->db->where("a.is_deleted = 0");
						$get_package = $this->db->get();
						if($get_package->num_rows() > 0){
							foreach($get_package->result() as $dtRow){
								
								//$key_prod_varian_item = $dtRow->product_id.'_'.$dtRow->varian_id_item;
								if(empty($all_product_gramasi_package[$key_prod_varian])){
									$all_product_gramasi_package[$key_prod_varian] = array();
									$all_product_gramasi_package_qty[$key_prod_varian] = array();
									$all_product_gramasi_package_varian_item[$key_prod_varian] = array();
								}
								
								//get all product gramasi 
								if(!in_array($dtRow->product_id, $all_product_gramasi_package[$key_prod_varian])){
									$all_product_gramasi_package[$key_prod_varian][] = $dtRow->product_id;
									$all_product_gramasi_package_qty[$key_prod_varian][$dtRow->product_id] = 0;
									$all_product_gramasi_package_varian_item[$key_prod_varian][$dtRow->product_id] = $dtRow->varian_id_item;
								}
								
								$all_product_gramasi_package_qty[$key_prod_varian][$dtRow->product_id] += $dtRow->product_qty;
								
							}
							
						}else{
							
							if(!in_array($dtRow->product_id, $all_product_package_empty)){
								$all_product_package_empty[] = $dtRow->product_id;
							}
						}
					}
					
					//UNIK KODE
					if(!empty($dt['use_stok_kode_unik'])){
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
					}
				}
			}
			
			//update 2019-02-11
			//ROLLBACK STOK
			$all_item_usage = array();
			
			//collection stock from gramasi
			if(!empty($all_product_gramasi_package)){
						
				foreach($all_product_gramasi_package as $packageId => $productId){
					
					if(!empty($productId)){
						$all_product_gramasi_package_sql = implode(",", $productId);
						$this->db->select("a.*, b.unit_id, b.item_hpp, b.sales_price");
						$this->db->from($this->table_product_gramasi." as a");
						$this->db->join($this->table_items." as b","b.id = a.item_id","LEFT");
						$this->db->where("a.product_id IN (".$all_product_gramasi_package_sql.")");
						$this->db->where('a.is_deleted', 0);
						$get_gramasi_package = $this->db->get();
						if($get_gramasi_package->num_rows() > 0){
							foreach($get_gramasi_package->result_array() as $dtRow){
								
								$key_prod_varian = $packageId;
								
								if(in_array($key_prod_varian, $all_product_package_varian)){
									
									$get_qty_order = 0;
									if(!empty($all_product_package_qty[$key_prod_varian])){
										$get_qty_order = $all_product_package_qty[$key_prod_varian];
									}
									
									$get_qty_package = 0;
									if(!empty($all_product_gramasi_package_qty[$key_prod_varian][$dtRow['product_id']])){
										$get_qty_package = $all_product_gramasi_package_qty[$key_prod_varian][$dtRow['product_id']];
									}
									
									$get_varian_item = 0;
									if(!empty($all_product_gramasi_package_varian_item[$key_prod_varian][$dtRow['product_id']])){
										$get_varian_item = $all_product_gramasi_package_varian_item[$key_prod_varian][$dtRow['product_id']];
									}
									
									if(!empty($get_varian_item)){
										if($get_varian_item == $dtRow['varian_id']){
											if(empty($all_item_usage[$dtRow['item_id']])){
												$all_item_usage[$dtRow['item_id']] = array(
													'id'	=> $dtRow['item_id'],
													'unit_id'	=> $dtRow['unit_id'],
													'item_hpp'	=> 0,
													'item_price'=> 0,
													'qty'		=> 0,
												);
											}
											
											$total_gramasi_qty = $dtRow['item_qty']*$get_qty_package*$get_qty_order;
											$total_gramasi_item_hpp = $dtRow['item_price']*$total_gramasi_qty;
											
											//*gramasi tidak ada hpp -> asumsi = item price
											$all_item_usage[$dtRow['item_id']]['qty'] += $total_gramasi_qty;
											$all_item_usage[$dtRow['item_id']]['item_hpp'] += $total_gramasi_item_hpp;
											$all_item_usage[$dtRow['item_id']]['item_price'] += 0;
											
										}
									}else{
										if(empty($all_item_usage[$dtRow['item_id']])){
											$all_item_usage[$dtRow['item_id']] = array(
												'id'	=> $dtRow['item_id'],
												'unit_id'	=> $dtRow['unit_id'],
												'item_hpp'	=> 0,
												'item_price'=> 0,
												'qty'		=> 0,
											);
										}
										
										$total_gramasi_qty = $dtRow['item_qty']*$get_qty_package*$get_qty_order;
										$total_gramasi_item_hpp = $dtRow['item_price']*$total_gramasi_qty;
										
										//*gramasi tidak ada hpp -> asumsi = item price
										$all_item_usage[$dtRow['item_id']]['qty'] += $total_gramasi_qty;
										$all_item_usage[$dtRow['item_id']]['item_hpp'] += $total_gramasi_item_hpp;
										$all_item_usage[$dtRow['item_id']]['item_price'] += 0;
										
									}
									
								}
								
							}
						}
					}
				}
				
			}
			
			
			//collection stock from gramasi
			if(!empty($all_product_gramasi)){
				$all_product_gramasi_sql = implode(",", $all_product_gramasi);
				$this->db->select("a.*, b.unit_id, b.item_hpp");
				$this->db->from($this->table_product_gramasi." as a");
				$this->db->join($this->table_items." as b","b.id = a.item_id","LEFT");
				$this->db->where("a.product_id IN (".$all_product_gramasi_sql.")");
				$this->db->where('a.is_deleted', 0);
				$get_gramasi = $this->db->get();
				if($get_gramasi->num_rows() > 0){
					foreach($get_gramasi->result_array() as $dtRow){
						
						if(empty($dtRow['varian_id'])){
							$dtRow['varian_id'] = 0;
						}
						
						$key_prod_varian = $dtRow['product_id'].'_'.$dtRow['varian_id'];
						
						if(in_array($key_prod_varian, $all_product_varian)){
							
							$get_qty = 0;
							if(!empty($all_product_qty[$key_prod_varian])){
								$get_qty = $all_product_qty[$key_prod_varian];
							}
							
							if(empty($all_item_usage[$dtRow['item_id']])){
								$all_item_usage[$dtRow['item_id']] = array(
									'id'	=> $dtRow['item_id'],
									'unit_id'	=> $dtRow['unit_id'],
									'item_hpp'	=> 0,
									'item_price'=> 0,
									'qty'		=> 0,
								);
							}
							
							$total_gramasi_qty = $dtRow['item_qty']*$get_qty;
							$total_gramasi_item_hpp = $dtRow['item_price']*$dtRow['item_qty']*$get_qty;
							
							//*gramasi tidak ada hpp -> asumsi = item price
							$all_item_usage[$dtRow['item_id']]['qty'] += $total_gramasi_qty;
							$all_item_usage[$dtRow['item_id']]['item_hpp'] += $total_gramasi_item_hpp;
							$all_item_usage[$dtRow['item_id']]['item_price'] += 0;
							
						}
						
					}
				}
				
			}
			
			//update 2019-02-11
			//if product from_item, id_ref_item
			if(!empty($all_product_order)){
				foreach($all_product_order as $dt){
					
					//FROM ITEM
					if(!empty($dt['id_ref_item']) AND !in_array($dt['product_id'], $all_product_gramasi)){
						if(empty($all_item_usage[$dt['id_ref_item']])){
							$all_item_usage[$dt['id_ref_item']] = array(
								'id'	=> $dt['id_ref_item'],
								'unit_id'	=> $dt['unit_id'],
								'item_hpp'	=> 0,
								'item_price'=> 0,
								'qty'		=> 0,
							);
						}
						
						$all_item_usage[$dt['id_ref_item']]['qty'] += $dt['qty'];
						$all_item_usage[$dt['id_ref_item']]['item_hpp'] += $dt['price_hpp'];
						$all_item_usage[$dt['id_ref_item']]['item_price'] += 0;
						
					}
				}
			}
			
			if(!empty($all_product_order_package)){
				foreach($all_product_order_package as $dt){
					
					//FROM ITEM
					if(!empty($dt['id_ref_item']) AND in_array($dt['product_id'], $all_product_package_empty)){
						
						if(empty($all_item_usage[$dt['id_ref_item']])){
							$all_item_usage[$dt['id_ref_item']] = array(
								'id'	=> $dt['id_ref_item'],
								'unit_id'	=> $dt['unit_id'],
								'item_hpp'	=> 0,
								'item_price'=> 0,
								'qty'		=> 0,
							);
						}
						
						
						
						$all_item_usage[$dt['id_ref_item']]['qty'] += $dt['qty'];
						$all_item_usage[$dt['id_ref_item']]['item_hpp'] += $dt['price_hpp'];
						$all_item_usage[$dt['id_ref_item']]['item_price'] += 0;
						
					}
				}
			}
			
			$dtInsert_stock = array();
			if(!empty($all_item_usage) AND !empty($retail_warehouse) AND ($update_stok == 'update' OR $update_stok == 'rollback')){
				
				foreach($all_item_usage as $item_id => $dt){
					//DELIVERY
					$billing_trx_type = 'out';
					$billing_trx_qty = $dt['qty'];
					
					if(empty($update_stock_item_unit[$retail_warehouse])){
						$update_stock_item_unit[$retail_warehouse] = array();
					}
					
					if(!in_array($item_id, $update_stock_item_unit[$retail_warehouse])){
						$update_stock_item_unit[$retail_warehouse][] = $item_id;
					}
					
					$dtInsert_stock[] = array(
						"item_id" => $item_id,
						"trx_date" => $payment_date,
						"trx_type" => $billing_trx_type,
						"trx_qty" => $billing_trx_qty,
						"unit_id" => $dt['unit_id'],
						"storehouse_id" => $retail_warehouse,
						"trx_nominal" => $dt['item_hpp'],
						"trx_note" => 'Sales',
						"trx_ref_data" => $billing_no,
						"trx_ref_det_id" => $item_id,
						"is_active" => "1"
					);
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