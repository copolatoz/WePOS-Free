<?php
class Model_receivedetail extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'receive_detail';
		$this->table_storehouse = $this->prefix.'storehouse';
	}
	
	function receiveDetail($receiveDetail = '', $receive_id = '', $update_stok = ''){
				
		$session_user = $this->session->userdata('user_username');
		
		$update_stock_item_unit = array();
		$all_item_updated = array();
		$all_item_updated_price = array();
		
		$from_add = false;
		$storehouse_id = 0;
		
		//form type
		$from_type = '';
		if($update_stok == 'add'){
			$update_stok = '';
			$from_type = $update_stok;
		}
		
		if($update_stok == 'update_add'){
			$update_stok = 'update';
			$from_add = true;
		}
		
		
		if(!empty($receiveDetail)){
			
			if(empty($receive_id)){
				$receive_id = -1;
				$receive_number = -1;
			}
			
			$dt_rowguid = array();
			//insert batch
			$this->db->from($this->prefix.'receiving');
			$this->db->where("id", $receive_id);
			$get_rowguid = $this->db->get();
			if($get_rowguid->num_rows() > 0){
				$dt_rowguid = $get_rowguid->row_array();
				$receive_number = $dt_rowguid['receive_number'];
				$storehouse_id = $dt_rowguid['storehouse_id'];
			}
			
			$receive_status = 'progress';
			
			//get PO QTY
			$all_po_det_id = array();
			$all_po_item_qty = array();
			$all_receive_po_det_qty = array();
			if(!empty($dt_rowguid['po_id'])){
				$this->db->select("a.*");
				$this->db->from($this->prefix."po_detail as a");
				$this->db->join($this->prefix."po as a2","a2.id = a.po_id","LEFT");
				$this->db->where("a.po_id", $dt_rowguid['po_id']);
				$this->db->where("a2.is_deleted", 0);
				$get_po_det = $this->db->get();
				if($get_po_det->num_rows() > 0){
					foreach($get_po_det->result() as $det_po){
							
						if(!in_array($det_po->id, $all_po_det_id)){
							$all_po_det_id[] = $det_po->id;
						}
							
						$all_po_item_qty[$det_po->id] = $det_po->po_detail_qty;
						$all_receive_po_det_qty[$det_po->id] = 0;
					}
				}
				
				if($from_type == 'add'){
					$receive_status = 'progress';
				}else{
					$receive_status = $dt_rowguid['receive_status'];
				}
				
			}
			
			
			$dtCurrent = array();
			$dtCurrent_qty_before = array();
			
			$this->db->from($this->prefix.'receive_detail');
			$this->db->where("receive_id", $receive_id);
			$get_det = $this->db->get();
			if($get_det->num_rows() > 0){
				foreach($get_det->result() as $dt){
					if(!in_array($dt->id, $dtCurrent) AND $from_add == false){
						$dtCurrent[] = $dt->id;
						if($receive_status == 'done'){
							$dtCurrent_qty_before[$dt->id] = $dt->receive_det_qty;
						}else{
							$dtCurrent_qty_before[$dt->id] = 0;
						}
					}
				}
			}
			
			
			//get Receive QTY
			//if(!empty($all_po_det_id) AND $from_add == false){
			if(!empty($all_po_det_id) AND $receive_status == 'done'){
					
				$all_po_det_id_sql = implode(",", $all_po_det_id);
				$this->db->select("a.*");
				$this->db->from($this->prefix."receive_detail as a");
				$this->db->join($this->prefix."receiving as a2","a2.id = a.receive_id","LEFT");
				$this->db->where("po_detail_id IN (".$all_po_det_id_sql.")");
				$this->db->where("a2.is_deleted = 0");
				$this->db->where("a2.receive_status = 'done'");
				
				if($from_add){
					$this->db->where("a.receive_id != ".$receive_id);
				}
				
				$get_rec_po_det = $this->db->get();
				if($get_rec_po_det->num_rows() > 0){
					foreach($get_rec_po_det->result() as $det_rec){
						if(empty($all_receive_po_det_qty[$det_rec->po_detail_id])){
							$all_receive_po_det_qty[$det_rec->po_detail_id] = 0;
						}
							
						$all_receive_po_det_qty[$det_rec->po_detail_id] += $det_rec->receive_det_qty;
					}
				}
			}
			
			/*
			//GET CURRENT STOCK
			$all_item_id = array();
			if(!empty($dt_rowguid) AND !empty($receiveDetail)){
				foreach($receiveDetail as $dt){
					if(!in_array($dt['item_id'],$all_item_id)){
						$all_item_id[] = $dt['item_id'];
					}
				}
			}
			
			$all_stock_before_item = array();
			if(!empty($all_item_id)){
				//Get Stock Before
				$all_item_id_sql = implode(",", $all_item_id);
				$this->db->select("id,total_qty_stok");
				$this->db->from($this->prefix."items");
				$this->db->where("id IN (".$all_item_id_sql.")");
				$q_items = $this->db->get();
				if($q_items->num_rows() > 0){
					foreach($q_items->result() as $dt_items){
						if(empty($all_stock_before_item[$dt_items->id])){
							$all_stock_before_item[$dt_items->id] = $dt_items->total_qty_stok;
						}
					}
				}
			}*/
			
			//$rl_date = date("Y-m-d");
			$rl_date = $dt_rowguid['receive_date'];
			//$receive_number = $dt_rowguid['receive_number'];
			
			//GET PRIMARY HOUSE
			if(empty($storehouse_id)){
				$storehouse_id = 0;
				$opt_value = array(
					'warehouse_primary'
				);
				$get_opt = get_option_value($opt_value);
				if(!empty($get_opt['warehouse_primary'])){
					$storehouse_id = $get_opt['warehouse_primary'];
				}
				
				if(empty($storehouse_id)){
					$this->db->from($this->table_storehouse);
					$this->db->where("is_primary = 1");
					$get_primary_storehouse = $this->db->get();
					if($get_primary_storehouse->num_rows() > 0){
						$storehouse_dt = $get_primary_storehouse->row();
						$storehouse_id = $storehouse_dt->id;
					}
				}
			}
			
			if(empty($storehouse_id)){
				return false;
			}
			
			$opt_value = array(
				'using_item_average_as_hpp'
			);
			$get_opt = get_option_value($opt_value);
			
			$using_item_average_as_hpp = 0;
			if(!empty($get_opt['using_item_average_as_hpp'])){
				$using_item_average_as_hpp = $get_opt['using_item_average_as_hpp'];
			}
			
			
			$total_qty = 0;
			$dtNew = array();
			$dtInsert_stock = array();
			$dtInsert = array();
			$dtUpdate = array();
			
			$dtInsert_kode_unik = array();
			$all_unik_kode = array();
			
			if(!empty($dt_rowguid) AND !empty($receiveDetail)){
				foreach($receiveDetail as $dt){
					
					$dt['storehouse_id'] = $storehouse_id;
					$receive_det_qty_before = $dt['receive_det_qty_before'];
					$item_id_real = $dt['item_id_real'];
					unset($dt['receive_number']);
					unset($dt['item_id_real']);
					unset($dt['item_code']);
					unset($dt['item_code_name']);
					unset($dt['item_name']);
					unset($dt['item_image']);
					unset($dt['unit_name']);
					unset($dt['item_price']);
					unset($dt['nomor']);
					unset($dt['receive_detail_status']);
					unset($dt['po_detail_qty_sisa']);
					unset($dt['po_receive_qty']);
					unset($dt['receive_det_purchase_show']);
					unset($dt['receive_det_total']);
					unset($dt['receive_det_qty_before']);
					
					$receive_det_date = date("Y-m-d",strtotime($dt['receive_det_date']));
					
					//UNIK KODE
					if($dt['use_stok_kode_unik'] == 1){
						$list_dt_kode = explode("\n",$dt['data_stok_kode_unik']);
						foreach($list_dt_kode as $kode_unik){
							if(!empty($kode_unik)){
								if(!in_array($kode_unik, $all_unik_kode)){
									$all_unik_kode[] = $kode_unik;
									
									$dtInsert_kode_unik[] = array(
										"item_id" => $item_id_real,
										"kode_unik" => $kode_unik,
										"ref_in" => $receive_number,
										"date_in" => $rl_date.' '.date("H:i:s"),
										"storehouse_id" => $storehouse_id
									);
									
								}
							}
							
						}
						
					}
					
					//SURE ONLY UPDATE!
					if(($update_stok == 'update' OR $update_stok == 'rollback') AND !empty($dt['receive_det_qty'])){
						
						
						if(empty($update_stock_item_unit[$storehouse_id])){
							$update_stock_item_unit[$storehouse_id] = array();
						}
						
						$update_stock_item_unit[$storehouse_id][] = $item_id_real;
						
						if(!in_array($item_id_real,$all_item_updated)){
							$all_item_updated[] = $item_id_real;
						}
						
						if(empty($all_item_updated_price[$item_id_real])){
							$all_item_updated_price[$item_id_real] = 0;
							$all_item_updated_price[$item_id_real] = $dt['receive_det_purchase'];
						}else{
							$all_item_updated_price[$item_id_real] = ($all_item_updated_price[$item_id_real] + $dt['receive_det_purchase']) / 2;
						}
						
						$all_item_updated_price[$item_id_real] = priceFormat($all_item_updated_price[$item_id_real]);
						$all_item_updated_price[$item_id_real] = numberFormat($all_item_updated_price[$item_id_real]);
						
						$dtInsert_stock[] = array(
							"item_id" => $item_id_real,
							"trx_date" => $receive_det_date,
							"trx_type" => 'in',
							"trx_qty" => $dt['receive_det_qty'],
							"unit_id" => $dt['unit_id'],
							"trx_nominal" => $dt['receive_det_purchase'],
							"storehouse_id" => $storehouse_id,
							"trx_note" => 'Receiving',
							"trx_ref_data" => $receive_number,
							"trx_ref_det_id" => $dt['id'],
							"is_active" => "1"
						);
					}
					
					if(!empty($dtCurrent_qty_before[$dt['id']])){
						//$receive_det_qty_before = $dtCurrent_qty_before[$dt['id']];
					}
					
					$dt['receive_det_date'] = $receive_det_date;
										
					//$dt['current_stock'] = 0;
					//if(!empty($all_stock_before_item[$item_id_real])){
					//	$dt['current_stock'] = $all_stock_before_item[$item_id_real];
					//}
			
					if(empty($all_receive_po_det_qty[$dt['po_detail_id']])){
						$all_receive_po_det_qty[$dt['po_detail_id']] = 0;
					}
					
					$all_receive_po_det_qty[$dt['po_detail_id']] += ($dt['receive_det_qty'] - $receive_det_qty_before);
					
					/* DEPRECATED -- USE STOCK REKAP
					$dtUpdate_Items[] = array(
							"id" => $item_id_real,
							"total_qty_stok" => $dt['current_stock'] + ($dt['receive_det_qty'] - $receive_det_qty_before)
					);
					*/
					
					$total_qty += ($dt['receive_det_qty']);
					
					//check if new
					if(strstr($dt['id'], 'new_')){
						unset($dt['id']);
					}
						
					$dt['receive_id'] = $receive_id;
						
					if(empty($dt['id'])){
					
						unset($dt['id']);
						$dtInsert[] = $dt;
					
					}else{
							
						$dtUpdate[] = $dt;
					
						if(!in_array($dt['id'], $dtNew)){
							$dtNew[] = $dt['id'];
						}
					}
				}
			}
			
			//delete if not exist
			$dtDelete = array();
			if(!empty($dtNew)){
				foreach($dtCurrent as $dtR){
					if(!in_array($dtR, $dtNew)){
						$dtDelete[] = $dtR;
					}
				}
			}else{
				//delete all
				$dtDelete = $dtCurrent;
			}

			//if($update_stok){
			//	echo '<pre>';
			//	print_r($receiveDetail);
			//	print_r($all_receive_po_det_qty);
			//	die();
			//}
			
			if(!empty($dtDelete)){
				$allRowguid = implode("','", $dtDelete);
				$this->db->where("id IN ('".$allRowguid."')");
				$this->db->delete($this->table); 
			}
			
			if(!empty($dtInsert)){
				$this->db->insert_batch($this->table, $dtInsert);
			}
			
			if(!empty($dtUpdate)){
				$this->db->update_batch($this->table, $dtUpdate, 'id');
			}			
			

			//UPDATE BATCH total Items
			if(!empty($dtUpdate_Items)){
				$this->db->update_batch($this->prefix."items", $dtUpdate_Items, "id");
			}
			
			//UPDATE PO DETAIL
			if(!empty($all_receive_po_det_qty)){
				
				$allow_update_rec_qty = false;
				
				if($update_stok == 'rollback' OR  $receive_status == 'done'){
					$allow_update_rec_qty = true;
				}
				
				if($allow_update_rec_qty){
					$updatePO_detail = array();
					foreach($all_receive_po_det_qty as $det_key => $det_val){
						$updatePO_detail[] = array(
								'id'			=> $det_key,
								'po_receive_qty'=> $det_val
						);
					}
						
					if(!empty($updatePO_detail)){
						$this->db->update_batch($this->prefix."po_detail", $updatePO_detail, "id");
					}
				}			
				
			}
			
			if($update_stok == 'update' OR $update_stok == 'rollback'){
				
				if($update_stok == 'rollback'){
					//DELETE ALL STOCK
					$this->db->where("trx_ref_data", $receive_number);
					$this->db->delete($this->prefix."stock"); 
					
					$this->db->where("ref_in", $receive_number);
					$this->db->delete($this->prefix."item_kode_unik"); 
				}else{
					//UPDATE STOCK TRX
					if(!empty($dtInsert_stock)){
						$this->db->insert_batch($this->prefix.'stock', $dtInsert_stock);
						
						if(!empty($dtInsert_kode_unik)){
							$this->db->insert_batch($this->prefix.'item_kode_unik', $dtInsert_kode_unik);
						}
						
					}
				}
				
				
				//ITEM AVERAGE	
				if(!empty($all_item_updated)){
					//AVERAGE Items
					$update_item_price_average = array();
					$update_item_hpp = array();
					$all_item_updated_txt = implode("','", $all_item_updated);
					$this->db->where("id IN ('".$all_item_updated_txt."')");
					$this->db->from($this->prefix.'items'); 
					$get_items = $this->db->get();
					if($get_items->num_rows() > 0){
						foreach($get_items->result() as $dt){
							
							if(!empty($all_item_updated_price[$dt->id])){
							
								if(empty($dt->item_hpp)){
									$dt->item_hpp = $dt->item_price;
								}
								
								$item_hpp = $dt->item_hpp;
								$last_in  = $all_item_updated_price[$dt->id];
								$old_last_in  = $dt->last_in;
								
								if($update_stok == 'rollback'){
									$item_hpp = ($dt->item_hpp * 2) - $all_item_updated_price[$dt->id];
									$item_hpp = priceFormat($item_hpp);
									$item_hpp = numberFormat($item_hpp);
									
									$last_in = $dt->old_last_in;
									
								}else{
									$item_hpp = ($all_item_updated_price[$dt->id] + $dt->item_hpp) / 2;
									$item_hpp = priceFormat($item_hpp);
									$item_hpp = numberFormat($item_hpp);
								}
								
								$item_price = $dt->item_price;
								if(!empty($using_item_average_as_hpp)){
									$item_price = $item_hpp;
								}
								
								$update_item_price_average[] = array(
									'id'			=> $dt->id,
									'item_price'	=> $item_price,
									'item_hpp'		=> $item_hpp,
									'last_in'		=> $all_item_updated_price[$dt->id],
									'old_last_in'	=> $old_last_in
								);
								
								$update_item_hpp[$dt->id] = array(
									'id'			=> $dt->id,
									'item_hpp'		=> $item_hpp,
									'last_in'		=> $all_item_updated_price[$dt->id],
									'old_last_in'	=> $old_last_in
								);
								
							}
							
						}
					}
					
					if(!empty($update_item_price_average)){
						$this->db->update_batch($this->prefix."items", $update_item_price_average, "id");
					}
					
					//SUPPLIER ITEM
					$supplier_id = $dt_rowguid['supplier_id'];
					if(!empty($supplier_id)){
						$update_supplier_item_price = array();
						$all_item_updated_txt = implode("','", $all_item_updated);
						$this->db->where("item_id IN ('".$all_item_updated_txt."') AND supplier_id = '".$supplier_id."'");
						$this->db->from($this->prefix.'supplier_item'); 
						$get_items = $this->db->get();
						if($get_items->num_rows() > 0){
							foreach($get_items->result() as $dt){
								
								if(!empty($all_item_updated_price[$dt->item_id])){
									
									if(empty($dt->item_hpp)){
										$dt->item_hpp = $dt->item_price;
									}
									
									$item_hpp = $dt->item_hpp;
									$last_in  = $all_item_updated_price[$dt->item_id];
									$old_last_in  = $dt->last_in;
									
									if($update_stok == 'rollback'){
										$item_hpp = ($dt->item_hpp * 2) - $all_item_updated_price[$dt->item_id];
										$item_hpp = priceFormat($item_hpp);
										$item_hpp = numberFormat($item_hpp);
										
										$last_in = $dt->old_last_in;
										
									}else{
										$item_hpp = ($all_item_updated_price[$dt->item_id] + $dt->item_hpp) / 2;
										$item_hpp = priceFormat($item_hpp);
										$item_hpp = numberFormat($item_hpp);
									}
								
									$item_price = $dt->item_price;
									if(!empty($using_item_average_as_hpp)){
										$item_price = $item_hpp;
									}
									
									$update_supplier_item_price[] = array(
										'id'			=> $dt->id,
										'item_hpp'		=> $item_hpp,
										'item_price'	=> $item_price,
										'last_in'		=> $all_item_updated_price[$dt->item_id],
										'old_last_in'	=> $old_last_in
									);
									
								}
								
							}
						}
						
						if(!empty($update_supplier_item_price)){
							$this->db->update_batch($this->prefix."supplier_item", $update_supplier_item_price, "id");
						}
					}
					
					
					//CEK GRAMASI -> ITEM
					$update_hpp_gramasi = array();
					$gramasi_perproduct = array();
					$product_varian_hpp = array();
					$get_product_hpp = array();
					if(!empty($all_item_updated)){
						
						$all_item_updated_txt = implode(",", $all_item_updated);
						
						$this->db->select("a.id as gramasi_id, a.item_id, a.item_qty, a.item_price, a.varian_id, a.product_id, 
						b.product_price, b.product_hpp, b.from_item, b.id_ref_item, 
						c.id as product_varian_id, c.product_price as product_price_varian, c.product_hpp as product_hpp_varian");
						$this->db->from($this->prefix."product_gramasi as a");
						$this->db->join($this->prefix."product as b","b.id = a.product_id","LEFT");
						$this->db->join($this->prefix."product_varian as c","c.product_id = a.product_id AND c.varian_id = a.varian_id AND c.is_deleted = 0","LEFT");
						$this->db->where("a.item_id IN (".$all_item_updated_txt.")");
						$this->db->where("a.is_deleted", 0);
						$get_all_gramasi = $this->db->get();
						if($get_all_gramasi->num_rows() > 0){
							//$update_item_hpp
							foreach($get_all_gramasi->result() as $dt){
								
								if(!empty($update_item_hpp[$dt->item_id])){
									
									$varID = $dt->product_id."_".$dt->varian_id;
									$update_item = $update_item_hpp[$dt->item_id];
									
									//UPDATE TO ALL GRAMASI - ITEM
									$update_hpp_gramasi[] = array("id" => $dt->gramasi_id, "item_price" => $update_item['item_hpp']);
									
									$gramasi_perproduct[$varID][] = array("product_id" => $dt->product_id, "item_id" => $dt->item_id, "item_qty" => $dt->item_qty, "varian_id" => $dt->varian_id, "item_hpp" => $update_item['item_hpp']);
									if($dt->from_item == 1 AND $dt->id_ref_item == $dt->item_id){
										//PRODUK -> FROM ITEM & REF_ITEM --> PASTI ITEM = PRODUCT (GRAMASI TOTAL = 0)
										$product_from_item[$varID] =  array("product_id" => $dt->product_id, "item_id" => $dt->item_id, "item_qty" => $dt->item_qty, "varian_id" => $dt->varian_id, "item_hpp" => $update_item['item_hpp']);
									}
									
									if($dt->varian_id != 0){
									
										if(empty($product_varian_hpp[$dt->product_varian_id])){
											$product_varian_hpp[$dt->product_varian_id] = 0;
										}
										
										$selisih = ($dt->item_qty*$update_item['item_hpp']) - ($dt->item_qty*$dt->item_price);
										$product_varian_hpp[$dt->product_varian_id] += ($dt->product_hpp_varian+$selisih);
										
									}else{
										
										if(empty($get_product_hpp[$dt->product_id])){
											$get_product_hpp[$dt->product_id] = array("product_hpp" => $dt->product_hpp, "old_hpp" => 0, "update_hpp" => 0);
										}
										
										$get_product_hpp[$dt->product_id]["old_hpp"] += ($dt->item_qty*$dt->item_price);
										$get_product_hpp[$dt->product_id]["update_hpp"] += ($dt->item_qty*$update_item['item_hpp']);
										
									}
								}
							}
						}
						
						//UPDATE GRAMASI
						if(!empty($update_hpp_gramasi)){
							$this->db->update_batch($this->prefix."product_gramasi", $update_hpp_gramasi, "id");
						}
						
						//UPDATE TO ALL PRODUCT = ITEM
						$update_product_id = array();
						$update_product_hpp = array();
						if(!empty($product_from_item)){
							foreach($product_from_item as $varId => $dt){
								//cek jika gramasi total == 1
								if(!empty($gramasi_perproduct[$varID])){
									if(count($gramasi_perproduct[$varID]) == 1){
										//update product hpp
										$product_hpp = $dt['item_qty'] * $dt['item_hpp'];
										$update_product_hpp[] = array("id" => $dt['product_id'],"product_hpp" => $product_hpp);
										
										if(!in_array($dt['product_id'], $update_product_id)){
											$update_product_id[] = $dt['product_id'];
										}
										
									}
								}
							}
						}
						
						//UPDATE PRODUCT HPP FROM GRAMASI ITEM
						if(!empty($get_product_hpp)){
							foreach($get_product_hpp as $prodID => $dt){
								
								$selisih = $dt['update_hpp'] - $dt['old_hpp'];
								$product_hpp = $dt['product_hpp'] + $selisih;
								
								if(!in_array($prodID, $update_product_id)){
									$update_product_id[] = $prodID;
									$update_product_hpp[] = array("id" => $prodID,"product_hpp" => $product_hpp);
								}
								
							}
						}
						
						if(!empty($update_product_hpp)){
							$this->db->update_batch($this->prefix."product", $update_product_hpp, "id");
						}
						
						//UPDATE TO ALL PRODUCT VARIAN
						$update_product_varian_hpp = array();
						if(!empty($product_varian_hpp)){
							foreach($product_varian_hpp as $pvarId => $product_hpp){
								$update_product_varian_hpp[] = array("id" => $pvarId, "product_hpp" => $product_hpp);
							}
						}
						
						if(!empty($update_product_varian_hpp)){
							$this->db->update_batch($this->prefix."product_varian", $update_product_varian_hpp, "id");
						}
						
						
						//PRODUCT -> UPDATE KE PAKET (INCLUDE PRODUCT)
						$update_hpp_product_package = array();
						$package_varian_hpp = array();
						if(!empty($update_product_id)){
							$update_product_id_sql = implode(",", $update_product_id);
							$this->db->select("a.id as product_package_id, a.package_id, a.product_qty, a.product_price, a.product_hpp, a.varian_id, a.varian_id_item, 
							b.product_hpp as update_hpp, b2.product_hpp as package_hpp,
							c.id as package_varian_id, c.product_price as package_price_varian, c.product_hpp as package_hpp_varian,
							d.id as product_varian_id, d.product_price as product_price_varian, d.product_hpp as product_hpp_varian");
							$this->db->from($this->prefix."product_package as a");
							$this->db->join($this->prefix."product as b","b.id = a.product_id","LEFT");
							$this->db->join($this->prefix."product as b2","b2.id = a.package_id","LEFT");
							$this->db->join($this->prefix."product_varian as c","c.product_id = a.package_id AND c.varian_id = a.varian_id AND c.is_deleted = 0","LEFT"); //package varian
							$this->db->join($this->prefix."product_varian as d","d.product_id = b.id AND d.varian_id = a.varian_id_item AND d.is_deleted = 0","LEFT"); //product varian
							$this->db->where("a.product_id IN ($update_product_id_sql)");
							$this->db->where("a.is_deleted", 0);
							$get_all_package = $this->db->get();
							if($get_all_package->num_rows() > 0){
								
								foreach($get_all_package->result() as $dt){
									$varID = $dt->package_id."_".$dt->varian_id;
									$update_hpp = $dt->update_hpp;
									
									if(!empty($dt->varian_id_item)){
										$update_hpp = $dt->product_hpp_varian;
									}
									
									//UPDATE TO ALL PRODUCT PACKAGE
									$update_hpp_product_package[] = array("id" => $dt->product_package_id, "product_hpp" => $update_hpp);
									
									if($dt->varian_id != 0){
										
										if(empty($package_varian_hpp[$dt->package_varian_id])){
											$package_varian_hpp[$dt->package_varian_id] = 0;
										}
										
										$selisih = ($dt->product_qty*$update_hpp) - ($dt->product_qty*$dt->product_hpp);
										$package_varian_hpp[$dt->package_varian_id] += ($dt->package_hpp_varian+$selisih);
										
									}else{
										
										if(empty($get_package_hpp[$dt->package_id])){
											$get_package_hpp[$dt->package_id] = array("product_hpp" => $dt->package_hpp, "old_hpp" => 0, "update_hpp" => 0);
										}
										
										//????
										$get_package_hpp[$dt->package_id]["old_hpp"] += ($dt->product_qty*$dt->product_hpp);
										$get_package_hpp[$dt->package_id]["update_hpp"] += ($dt->product_qty*$update_hpp);
										
									}
								}
							}
						}
						
						//UPDATE PRODUCT PACKAGE
						if(!empty($update_hpp_product_package)){
							$this->db->update_batch($this->prefix."product_package", $update_hpp_product_package, "id");
						}
						
						//UPDATE PACKAGE HPP FROM PRODUCT PACKAGE
						$update_package_id = array();
						if(!empty($get_package_hpp)){
							foreach($get_package_hpp as $prodID => $dt){
								
								$selisih = $dt['update_hpp'] - $dt['old_hpp'];
								$product_hpp = $dt['product_hpp'] + $selisih;
								
								if(!in_array($prodID, $update_product_id)){
									$update_product_id[] = $prodID;
									$update_product_hpp[] = array("id" => $prodID,"product_hpp" => $product_hpp);
								}
								
							}
						}
						
						if(!empty($update_product_hpp)){
							$this->db->update_batch($this->prefix."product", $update_product_hpp, "id");
						}
						
							
						//UPDATE TO ALL PACKAGE VARIAN
						$update_package_varian_hpp = array();
						if(!empty($package_varian_hpp)){
							foreach($package_varian_hpp as $pvarId => $product_hpp){
								$update_package_varian_hpp[] = array("id" => $pvarId, "product_hpp" => $product_hpp);
							}
						}
						
						if(!empty($update_package_varian_hpp)){
							$this->db->update_batch($this->prefix."product_varian", $update_package_varian_hpp, "id");
						}
						
						
					}
					
				}
			}
			
			return array('dtReceive' => $dt_rowguid, 'dtInsert' => $dtInsert, 'dtUpdate' => $dtUpdate, 'dtDelete' => $dtDelete, 
			'all_po_item_qty' => $all_po_item_qty, 'all_receive_po_det_qty' => $all_receive_po_det_qty, 
			'dtCurrent_qty_before' => $dtCurrent_qty_before, 'update_stock' => $update_stock_item_unit, 'receive_status' => $receive_status);
		}
	}

} 