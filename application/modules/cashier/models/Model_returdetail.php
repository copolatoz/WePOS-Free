<?php
class Model_returdetail extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'retur_detail';
		$this->table_storehouse = $this->prefix.'storehouse';
	}
	
	function returDetail($returDetail = '', $retur_id = '', $update_stok = ''){
				
		$session_user = $this->session->userdata('user_username');
		
		$update_stock_item_unit = array();
		$all_item_updated = array();
		$all_item_updated_price = array();
		
		$from_add = false;
		$storehouse_id = 0;
		$retur_date = '';
		$retur_ref = '';
		
		if($update_stok == 'update_add'){
			$update_stok = 'update';
			$from_add = true;
		}
		
		if(!empty($returDetail)){
			
			if(empty($retur_id)){
				$retur_id = -1;
				$retur_number = -1;
			}
			
			$dt_rowguid = array();
			//insert batch
			$this->db->from($this->prefix.'retur');
			$this->db->where("id", $retur_id);
			$get_rowguid = $this->db->get();
			if($get_rowguid->num_rows() > 0){
				$dt_rowguid = $get_rowguid->row_array();
				$retur_number = $dt_rowguid['retur_number'];
				$storehouse_id = $dt_rowguid['storehouse_id'];
				$retur_ref = $dt_rowguid['retur_ref'];
				$retur_ref_text = ucwords(str_replace("_"," ",$retur_ref));
				$retur_type = $dt_rowguid['retur_type'];
				$retur_type_text = ucwords(str_replace("_"," ",$retur_type));
				$ref_no = $dt_rowguid['ref_no'];
				$retur_date = $dt_rowguid['retur_date'];
			}
			
			$retur_status = 'progress';
			
			//get REF QTY
			$all_returd_refd_id = array();
			$all_ref_item_qty = array();
			$all_retur_refd_qty = array();
			if(!empty($dt_rowguid['ref_no'])){
				
				if($retur_ref == 'penjualan_so'){
					$this->db->select("a.*");
					$this->db->from($this->prefix."salesorder_detail as a");
					$this->db->join($this->prefix."salesorder as a2","a2.id = a.so_id","LEFT");
					$this->db->where("a2.so_no", $dt_rowguid['ref_no']);
					$this->db->where("a2.is_deleted", 0);
					$get_so_det = $this->db->get();
					if($get_so_det->num_rows() > 0){
						foreach($get_so_det->result() as $det_so){
								
							if(!in_array($det_so->id, $all_returd_refd_id)){
								$all_returd_refd_id[] = $det_so->id;
							}
								
							$all_ref_item_qty[$det_so->id] = $det_so->sod_qty;
							$all_retur_refd_qty[$det_so->id] = 0;
						}
					}
					
					//get ref detail item
					
				}else{
					
					$all_id_product = array();
					$all_id_product_item = array();
					$all_id_package = array();
					$all_id_package_varian = array();
					$all_detail_item_package = array();
					$all_detail_varian_id= array();
					$all_detail_product_varian_id= array();
					//$all_detail_varian_id_item= array();
					//$all_detail_product_varian_id_item= array();
					
					$this->db->select("a.*, b.product_type, b.product_code as item_code, b.product_name as item_name, c.varian_name");
					$this->db->from($this->prefix."billing_detail as a");
					$this->db->join($this->prefix."billing as a2","a2.id = a.billing_id","LEFT");
					$this->db->join($this->prefix."product as b","b.id = a.product_id","LEFT");
					$this->db->join($this->prefix."varian as c","c.id = a.varian_id","LEFT");
					$this->db->where("a2.billing_no", $dt_rowguid['ref_no']);
					$this->db->where("a2.is_deleted", 0);
					$get_billing_det = $this->db->get();
					if($get_billing_det->num_rows() > 0){
						foreach($get_billing_det->result() as $det_billing){
								
							if(!in_array($det_billing->id, $all_returd_refd_id)){
								$all_returd_refd_id[] = $det_billing->id;
							}
								
							$all_ref_item_qty[$det_billing->id] = $det_billing->order_qty;
							$all_retur_refd_qty[$det_billing->id] = 0;
							
							$all_detail_item_package[$det_billing->id] = $det_billing->product_type;
							$all_detail_varian_id[$det_billing->id] = $det_billing->varian_id;
							$all_detail_product_varian_id[$det_billing->id] = $det_billing->product_varian_id;
							
							//$all_detail_varian_id_item[$det_billing->id] = $det_billing->varian_id_item;
							//$all_detail_product_varian_id_item[$det_billing->id] = $det_billing->product_varian_id_item;
							
							if($det_billing->product_type == 'package'){
								
								$all_id_package[$det_billing->id] = $det_billing->product_id;
								$all_id_package_varian[$det_billing->id] = $det_billing->product_varian_id;
								
							}else{
								
								$all_id_product_item[$det_billing->id] = $det_billing->product_id;
								
								if(!in_array($det_billing->product_id, $all_id_product)){
									$all_id_product[] = $det_billing->product_id;
								}
								
							}
							
						}
					}
					
					//get ref detail item from package
					$product_package = array();
					$product_package_varian = array();
					$product_package_varian_peritem = array();
					$product_package_varian_productvarianid = array();
					
					$all_product_package_name = array();
					$product_package_has_varian = array();
					if(!empty($all_id_package)){
						
						$all_id_package_sql = implode(",", $all_id_package);
						
						$this->db->select("a.*");
						$this->db->from($this->prefix."product_package as a");
						$this->db->where("a.package_id IN (".$all_id_package_sql.")");
						$this->db->where("a.is_deleted", 0);
						$get_package_detail = $this->db->get();
						if($get_package_detail->num_rows() > 0){
							foreach($get_package_detail->result() as $dpackage){
								
								if(empty($product_package[$dpackage->package_id])){
									$product_package[$dpackage->package_id] = array();
									$product_package_varian[$dpackage->package_id] = array();
									$product_package_varian_peritem[$dpackage->package_id] = array();
									$product_package_varian_productvarianid[$dpackage->package_id] = array();
								}
								
								if(empty($product_package_varian[$dpackage->package_id][$dpackage->product_varian_id])){
									$product_package_varian[$dpackage->package_id][$dpackage->product_varian_id] = array();
									$product_package_varian_peritem[$dpackage->package_id][$dpackage->product_varian_id] = array();
									$product_package_varian_productvarianid[$dpackage->package_id][$dpackage->product_varian_id] = array();
								}
								
								if(!empty($dpackage->product_varian_id)){
									if(!empty($product_package_has_varian[$dpackage->product_id])){
										$product_package_has_varian[$dpackage->product_id] = array();
									}
									$product_package_has_varian[$dpackage->product_id][] = $dpackage->product_varian_id;
								}
								
								$product_package[$dpackage->package_id][] = $dpackage->product_id;
								$product_package_varian[$dpackage->package_id][$dpackage->product_varian_id][] = $dpackage->product_id;
								$product_package_varian_peritem[$dpackage->package_id][$dpackage->product_varian_id][$dpackage->product_id] = $dpackage->varian_id_item;
								$product_package_varian_productvarianid[$dpackage->package_id][$dpackage->product_varian_id][$dpackage->product_id] = $dpackage->product_varian_id_item;
								
								
								if(!in_array($dpackage->product_id, $all_id_product)){
									$all_id_product[] = $dpackage->product_id;
									$all_product_package_name[$dpackage->product_id] = $dpackage;
								}
								
							}
						}
						
					}
					
					//get gramasi product
					$product_gramasi = array();
					$product_gramasi_varian = array();
					$product_gramasi_has_varian = array();
					if(!empty($all_id_product)){
						
						$all_id_product_sql = implode(",", $all_id_product);
						$this->db->select("a.*, b.unit_id");
						$this->db->from($this->prefix."product_gramasi as a");
						$this->db->join($this->prefix."items as b","b.id = a.item_id","LEFT");
						$this->db->where("a.product_id IN (".$all_id_product_sql.")");
						$this->db->where("a.is_deleted = 0");
						$get_product_gramasi = $this->db->get();
						if($get_product_gramasi->num_rows() > 0){
							foreach($get_product_gramasi->result() as $dtgramasi){
								
								if(empty($product_gramasi[$dtgramasi->product_id])){
									$product_gramasi[$dtgramasi->product_id] = array();
									$product_gramasi_varian[$dtgramasi->product_id] = array();
								}
								
								if(empty($product_gramasi_varian[$dtgramasi->product_id][$dtgramasi->product_varian_id])){
									$product_gramasi_varian[$dtgramasi->product_id][$dtgramasi->product_varian_id] = array();
								}
								
								if(!empty($dtgramasi->product_varian_id)){
									if(!empty($product_gramasi_has_varian[$dtgramasi->product_id])){
										$product_gramasi_has_varian[$dtgramasi->product_id] = array();
									}
									$product_gramasi_has_varian[$dtgramasi->product_id][] = $dtgramasi->product_varian_id;
								}
								
								
								$product_gramasi[$dtgramasi->product_id][] = $dtgramasi;
								$product_gramasi_varian[$dtgramasi->product_id][$dtgramasi->product_varian_id][] = $dtgramasi;
								
							}
						}
						
					}
					
					
					
				}
				
				
				$retur_status = $dt_rowguid['retur_status'];
			}
			
			//preparing qty on old status = done
			$dtCurrent = array();
			$dtCurrent_qty_before = array();
			
			$this->db->from($this->prefix.'retur_detail');
			$this->db->where("retur_id", $retur_id);
			$get_det = $this->db->get();
			if($get_det->num_rows() > 0){
				foreach($get_det->result() as $dt){
					if(!in_array($dt->id, $dtCurrent) AND $from_add == false){
						$dtCurrent[] = $dt->id;
						if($retur_status == 'done'){
							$dtCurrent_qty_before[$dt->id] = $dt->returd_qty;
						}else{
							$dtCurrent_qty_before[$dt->id] = 0;
						}
					}
				}
			}
			
			
			//get Retur QTY - from similar ref
			//if(!empty($all_returd_refd_id) AND $from_add == false){
			if(!empty($all_returd_refd_id)){
					
				$all_returd_refd_id_sql = implode(",", $all_returd_refd_id);
				$this->db->select("a.*");
				$this->db->from($this->prefix."retur_detail as a");
				$this->db->join($this->prefix."retur as a2","a2.id = a.retur_id","LEFT");
				$this->db->where("returd_refd_id IN (".$all_returd_refd_id_sql.")");
				$this->db->where("a2.is_deleted = 0");
				$this->db->where("a2.retur_status = 'done'");
				$this->db->where("a2.ref_no = '".$ref_no."'");
				$this->db->where("a2.retur_ref = '".$retur_ref."'");
				$this->db->where("a.retur_id != ".$retur_id);
				
				if($from_add){
					//$this->db->where("a.retur_id != ".$retur_id);
				}
				
				$get_rec_so_det = $this->db->get();
				if($get_rec_so_det->num_rows() > 0){
					foreach($get_rec_so_det->result() as $det_rec){
						if(empty($all_retur_refd_qty[$det_rec->returd_refd_id])){
							$all_retur_refd_qty[$det_rec->returd_refd_id] = 0;
						}
							
						$all_retur_refd_qty[$det_rec->returd_refd_id] += $det_rec->returd_qty;
					}
				}
			}
			
			//$rl_date = date("Y-m-d");
			$rl_date = $dt_rowguid['retur_date'];
			//$retur_number = $dt_rowguid['retur_number'];
			
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
			
			
			//$total_qty = 0;
			//$total_price = 0;
			$dtNew = array();
			$dtInsert_stock = array();
			$dtInsert_stock_rekap = array();
			$dtInsert = array();
			$dtUpdate = array();
			
			$dtInsert_kode_unik = array();
			$all_unik_kode = array();
			
			if(!empty($dt_rowguid) AND !empty($returDetail)){
				foreach($returDetail as $dt){
					
					$item_product_id = $dt['item_product_id'];
					$returd_refd_id = $dt['returd_refd_id'];
					
					if(empty($dt['returd_qty_before'])){
						$dt['returd_qty_before'] = 0;
					}
					
					$dt['storehouse_id'] = $storehouse_id;
					$returd_qty_before = $dt['returd_qty_before'];
					
					unset($dt['retur_number']);
					unset($dt['item_code']);
					unset($dt['item_code_name']);
					unset($dt['item_name']);
					unset($dt['unit_id']);
					unset($dt['unit_name']);
					unset($dt['sales_price_show']);
					unset($dt['returd_price_show']);
					unset($dt['returd_total_show']);
					unset($dt['nomor']);
					//unset($dt['returd_qty_before']);
					unset($dt['returd_qty_sisa']);
					unset($dt['retur_type']);
					unset($dt['returd_hpp_show']);
					unset($dt['returd_tax_show']);
					unset($dt['storehouse_id']);
					
					$returd_date = date("Y-m-d",strtotime($retur_date));
					
					//UNIK KODE
					if($dt['use_stok_kode_unik'] == 1){
						$list_dt_kode = explode("\n",$dt['data_stok_kode_unik']);
						foreach($list_dt_kode as $kode_unik){
							if(!empty($kode_unik)){
								if(!in_array($kode_unik, $all_unik_kode)){
									$all_unik_kode[] = $kode_unik;
									
									$dtInsert_kode_unik[] = array(
										"item_id" => $item_product_id,
										"kode_unik" => $kode_unik,
										"ref_in" => $retur_number,
										"date_in" => $rl_date.' '.date("H:i:s"),
										"storehouse_id" => $storehouse_id
									);
									
								}
							}
							
						}
						
					}
					
					//SURE ONLY UPDATE!
					if(($update_stok == 'update' OR $update_stok == 'rollback') AND !empty($dt['returd_qty'])){
						
						if(empty($update_stock_item_unit[$storehouse_id])){
							$update_stock_item_unit[$storehouse_id] = array();
						}
						
						$returd_price = $dt['returd_hpp']+$dt['returd_tax']; 
						
						//ref item from product & package
						if(!empty($all_detail_item_package[$returd_refd_id])){
							
							if($all_detail_item_package[$returd_refd_id] == 'package'){
								
								$get_product_varian_id = 0;
								if(!empty($all_detail_product_varian_id[$returd_refd_id])){
									$get_product_varian_id = $all_detail_product_varian_id[$returd_refd_id];
								}
								
								//untuk cek gramasi = varian_id
								//$get_product_varian_id_item = 0;
								//if(!empty($all_detail_product_varian_id_item[$returd_refd_id])){
								//	$get_product_varian_id_item = $all_detail_product_varian_id_item[$returd_refd_id];
								//}
								
								//get product on package
								if(!empty($product_package_varian[$item_product_id][$get_product_varian_id])){
									//get all item - gramasi product
									foreach($product_package_varian[$item_product_id][$get_product_varian_id] as $prodid){
										
										$varian_id_item = 0;
										if(!empty($product_package_varian_peritem[$item_product_id][$get_product_varian_id][$prodid])){
											$varian_id_item = $product_package_varian_peritem[$item_product_id][$get_product_varian_id][$prodid];
										}
										
										$product_varian_id_item = 0;
										if(!empty($product_package_varian_productvarianid[$item_product_id][$get_product_varian_id][$prodid])){
											$product_varian_id_item = $product_package_varian_productvarianid[$item_product_id][$get_product_varian_id][$prodid];
										}
										
										//echo '$item_product_id -> '.$item_product_id.'<br/>';
										//echo '$get_product_varian_id -> '.$get_product_varian_id.'<br/>';
										//echo '$prodid -> '.$prodid.'<br/>';
										//echo '$product_varian_id_item -> '.$product_varian_id_item.'<br/>';
										//echo '$product_gramasi_varian _'.$prodid.'_'.$product_varian_id_item.' = '.count($product_gramasi_varian[$prodid][$product_varian_id_item]).'<br/>';
										
										//cek on gramasi
										if(!empty($product_gramasi_varian[$prodid][$product_varian_id_item])){
											//echo '$product_gramasi_varian -> '.$prodid.'_'.$product_varian_id_item.'<br/>';
											//get all item - gramasi product
											$total_hpp_real = 0;
											foreach($product_gramasi_varian[$prodid][$product_varian_id_item] as $dtgramasi){
												
												$allow_calc = true;
												if(!empty($varian_id_item)){
													$allow_calc = false;
													if($varian_id_item == $dtgramasi->varian_id){
														$allow_calc = true;
													}
												}
												
												if($allow_calc){
													if(!in_array($dtgramasi->item_id,$all_item_updated)){
														$all_item_updated[] = $dtgramasi->item_id;
													}
													
													if(!in_array($dtgramasi->item_id,$update_stock_item_unit[$storehouse_id])){
														$update_stock_item_unit[$storehouse_id][] = $dtgramasi->item_id;
													}
													
													$total_hpp_real += ($dtgramasi->item_price*$dtgramasi->item_qty);
												}
											}
											
											$total_returd_price = 0;
											foreach($product_gramasi_varian[$prodid][$product_varian_id_item] as $dtgramasi){
												
												$allow_calc = true;
												if(!empty($varian_id_item)){
													$allow_calc = false;
													if($varian_id_item == $dtgramasi->varian_id){
														$allow_calc = true;
													}
												}
												
												if($allow_calc){
														
													if(empty($dtInsert_stock_rekap[$returd_refd_id])){
														$dtInsert_stock_rekap[$returd_refd_id] = array();
													}
													
													$item_qty = $dtgramasi->item_qty*$dt['returd_qty'];
													$item_price = $dtgramasi->item_price*$item_qty;
													
													$item_hpp = ($dtgramasi->item_price*$dtgramasi->item_qty);
													
													if(empty($total_hpp_real)){
														$total_hpp_real = 1;
														$persentase_hpp = round(($item_hpp/$total_hpp_real),2);
													}else{
														$persentase_hpp = round(($item_hpp/$total_hpp_real),2);
													}
													
													$returd_price_percent = round($returd_price*$persentase_hpp,0);
													$total_returd_price += $returd_price_percent;
													
													//average item hpp
													if(empty($all_item_updated_price[$dtgramasi->item_id])){
														$all_item_updated_price[$dtgramasi->item_id] = 0;
														$all_item_updated_price[$dtgramasi->item_id] = $returd_price_percent;
													}else{
														$all_item_updated_price[$dtgramasi->item_id] = ($all_item_updated_price[$dtgramasi->item_id] + $returd_price_percent) / 2;
													}
													$all_item_updated_price[$dtgramasi->item_id] = priceFormat($all_item_updated_price[$dtgramasi->item_id]);
													$all_item_updated_price[$dtgramasi->item_id] = numberFormat($all_item_updated_price[$dtgramasi->item_id]);
													
													//balancing
													if($total_returd_price > $returd_price){
														$selisih = $total_returd_price - $returd_price;
														$total_returd_price -= $selisih;
														$returd_price_percent -= $selisih;
													}
													
													if(empty($dtInsert_stock_rekap[$returd_refd_id][$dtgramasi->item_id])){
														
														//stok sesuai returd_refd_id
														$dtInsert_stock_rekap[$returd_refd_id][$dtgramasi->item_id] = array(
															"item_id" => $dtgramasi->item_id,
															"trx_date" => $returd_date,
															"trx_type" => 'in',
															"trx_qty" => 0,
															"unit_id" => $dtgramasi->unit_id,
															"trx_nominal" => 0,
															"storehouse_id" => $storehouse_id,
															"trx_note" => 'Retur '.$retur_ref_text.': '.$retur_type_text,
															"trx_ref_data" => $retur_number,
															"trx_ref_det_id" => $returd_refd_id,
															"is_active" => "1"
														);
														
													}
													
													$dtInsert_stock_rekap[$returd_refd_id][$dtgramasi->item_id]['trx_qty'] += $item_qty;
													$dtInsert_stock_rekap[$returd_refd_id][$dtgramasi->item_id]['trx_nominal'] += $returd_price_percent;
													
												}
											}
										}
									}
								}
								
								/*
								if(!empty($get_product_varian_id)){
									
									

								}else{
									//get product on package
									if(!empty($product_package[$item_product_id])){
										//get all item - gramasi product
										foreach($product_package[$item_product_id] as $prodid){
											
											$varian_id_item = 0;
											if(!empty($product_package_varian_peritem[$item_product_id][0][$prodid])){
												$varian_id_item = $product_package_varian_peritem[$item_product_id][0][$prodid];
											}
											
											//cek on gramasi
											if(!empty($product_gramasi[$prodid])){
												
												//get all item - gramasi product
												$total_hpp_real = 0;
												foreach($product_gramasi[$prodid] as $dtgramasi){
													
													$allow_calc = true;
													if(!empty($varian_id_item)){
														$allow_calc = false;
														if($varian_id_item == $dtgramasi->varian_id){
															$allow_calc = true;
														}
													}
													
													if($allow_calc){
														if(!in_array($dtgramasi->item_id,$all_item_updated)){
															$all_item_updated[] = $dtgramasi->item_id;
														}
														
														if(!in_array($dtgramasi->item_id,$update_stock_item_unit[$storehouse_id])){
															$update_stock_item_unit[$storehouse_id][] = $dtgramasi->item_id;
														}
														
														$total_hpp_real += ($dtgramasi->item_price*$dtgramasi->item_qty);
													}
												}
												
												$total_returd_price = 0;
												foreach($product_gramasi[$prodid] as $dtgramasi){
													
													$allow_calc = true;
													if(!empty($varian_id_item)){
														$allow_calc = false;
														if($varian_id_item == $dtgramasi->varian_id){
															$allow_calc = true;
														}
													}
													
													if($allow_calc){
														
														if(empty($dtInsert_stock_rekap[$returd_refd_id])){
															$dtInsert_stock_rekap[$returd_refd_id] = array();
														}
														
														$item_qty = $dtgramasi->item_qty*$dt['returd_qty'];
														$item_price = $dtgramasi->item_price*$item_qty;
														
														$item_hpp = ($dtgramasi->item_price*$dtgramasi->item_qty);
														$persentase_hpp = round(($item_hpp/$total_hpp_real),2);
														$returd_price_percent = round($returd_price*$persentase_hpp,0);
														$total_returd_price += $returd_price_percent;
														
														//average item hpp
														if(empty($all_item_updated_price[$dtgramasi->item_id])){
															$all_item_updated_price[$dtgramasi->item_id] = 0;
															$all_item_updated_price[$dtgramasi->item_id] = $returd_price_percent;
														}else{
															$all_item_updated_price[$dtgramasi->item_id] = ($all_item_updated_price[$dtgramasi->item_id] + $returd_price_percent) / 2;
														}
														$all_item_updated_price[$dtgramasi->item_id] = priceFormat($all_item_updated_price[$dtgramasi->item_id]);
														$all_item_updated_price[$dtgramasi->item_id] = numberFormat($all_item_updated_price[$dtgramasi->item_id]);
														
														//balancing
														if($total_returd_price > $returd_price){
															$selisih = $total_returd_price - $returd_price;
															$total_returd_price -= $selisih;
															$returd_price_percent -= $selisih;
														}
														
														if(empty($dtInsert_stock_rekap[$returd_refd_id][$dtgramasi->item_id])){
															
															//stok sesuai returd_refd_id
															$dtInsert_stock_rekap[$returd_refd_id][$dtgramasi->item_id] = array(
																"item_id" => $dtgramasi->item_id,
																"trx_date" => $returd_date,
																"trx_type" => 'in',
																"trx_qty" => 0,
																"unit_id" => $dtgramasi->unit_id,
																"trx_nominal" => 0,
																"storehouse_id" => $storehouse_id,
																"trx_note" => 'Retur '.$retur_ref_text.': '.$retur_type_text,
																"trx_ref_data" => $retur_number,
																"trx_ref_det_id" => $returd_refd_id,
																"is_active" => "1"
															);
															
														}
														
														$dtInsert_stock_rekap[$returd_refd_id][$dtgramasi->item_id]['trx_qty'] += $item_qty;
														$dtInsert_stock_rekap[$returd_refd_id][$dtgramasi->item_id]['trx_nominal'] += $returd_price_percent;
													
													}
												}
											}
										}
									}

								}
								*/
								
							}else{
								
								
								$get_product_varian_id = 0;
								if(!empty($all_detail_product_varian_id[$returd_refd_id])){
									$get_product_varian_id = $all_detail_product_varian_id[$returd_refd_id];
								}
								
								//untuk cek gramasi = varian_id
								//$get_product_varian_id_item = 0;
								//if(!empty($all_detail_varian_id_item[$returd_refd_id])){
								//	$get_product_varian_id_item = $all_detail_varian_id_item[$returd_refd_id];
								//}
								
								//echo '$item_product_id -> '.$item_product_id.'<br/>';
								//echo '$get_product_varian_id -> '.$get_product_varian_id.'<br/>';
								
								if(!empty($product_gramasi_varian[$item_product_id][$get_product_varian_id])){
									
									//get all item - gramasi product
									$total_hpp_real = 0;
									foreach($product_gramasi_varian[$item_product_id][$get_product_varian_id] as $dtgramasi){
												
										if(!in_array($dtgramasi->item_id,$all_item_updated)){
											$all_item_updated[] = $dtgramasi->item_id;
										}
												
										if(!in_array($dtgramasi->item_id,$update_stock_item_unit[$storehouse_id])){
											$update_stock_item_unit[$storehouse_id][] = $dtgramasi->item_id;
										}
										
										$total_hpp_real += ($dtgramasi->item_price*$dtgramasi->item_qty);
									}
									
									$total_returd_price = 0;
									foreach($product_gramasi_varian[$item_product_id][$get_product_varian_id] as $dtgramasi){
										
										if(empty($dtInsert_stock_rekap[$returd_refd_id])){
											$dtInsert_stock_rekap[$returd_refd_id] = array();
										}
										
										$item_qty = $dtgramasi->item_qty*$dt['returd_qty'];
										$item_price = $dtgramasi->item_price*$item_qty;
										
										$item_hpp = ($dtgramasi->item_price*$dtgramasi->item_qty);
										
										if(empty($total_hpp_real)){
											$total_hpp_real = 1;
											$persentase_hpp = round(($item_hpp/$total_hpp_real),2);
										}else{
											$persentase_hpp = round(($item_hpp/$total_hpp_real),2);
										}
										
										$returd_price_percent = round($returd_price*$persentase_hpp,0);
										$total_returd_price += $returd_price_percent;
										
										//average item hpp
										if(empty($all_item_updated_price[$dtgramasi->item_id])){
											$all_item_updated_price[$dtgramasi->item_id] = 0;
											$all_item_updated_price[$dtgramasi->item_id] = $returd_price_percent;
										}else{
											$all_item_updated_price[$dtgramasi->item_id] = ($all_item_updated_price[$dtgramasi->item_id] + $returd_price_percent) / 2;
										}
										$all_item_updated_price[$dtgramasi->item_id] = priceFormat($all_item_updated_price[$dtgramasi->item_id]);
										$all_item_updated_price[$dtgramasi->item_id] = numberFormat($all_item_updated_price[$dtgramasi->item_id]);
										
										//balancing
										if($total_returd_price > $returd_price){
											$selisih = $total_returd_price - $returd_price;
											$total_returd_price -= $selisih;
											$returd_price_percent -= $selisih;
										}
										
										if(empty($dtInsert_stock_rekap[$returd_refd_id][$dtgramasi->item_id])){
											
											//stok sesuai returd_refd_id
											$dtInsert_stock_rekap[$returd_refd_id][$dtgramasi->item_id] = array(
												"item_id" => $dtgramasi->item_id,
												"trx_date" => $returd_date,
												"trx_type" => 'in',
												"trx_qty" => 0,
												"unit_id" => $dtgramasi->unit_id,
												"trx_nominal" => 0,
												"storehouse_id" => $storehouse_id,
												"trx_note" => 'Retur '.$retur_ref_text.': '.$retur_type_text,
												"trx_ref_data" => $retur_number,
												"trx_ref_det_id" => $returd_refd_id,
												"is_active" => "1"
											);
											
										}
										
										$dtInsert_stock_rekap[$returd_refd_id][$dtgramasi->item_id]['trx_qty'] += $item_qty;
										$dtInsert_stock_rekap[$returd_refd_id][$dtgramasi->item_id]['trx_nominal'] += $returd_price_percent;
										
										
									}
									
								}
								
								/*if(!empty($get_product_varian_id)){
									
								}else{
									if(!empty($product_gramasi[$item_product_id])){
									
										//get all item - gramasi product
										$total_hpp_real = 0;
										foreach($product_gramasi[$item_product_id] as $dtgramasi){
													
											if(!in_array($dtgramasi->item_id,$all_item_updated)){
												$all_item_updated[] = $dtgramasi->item_id;
											}
													
											if(!in_array($dtgramasi->item_id,$update_stock_item_unit[$storehouse_id])){
												$update_stock_item_unit[$storehouse_id][] = $dtgramasi->item_id;
											}
											
											$total_hpp_real += ($dtgramasi->item_price*$dtgramasi->item_qty);
										}
										
										$total_returd_price = 0;
										foreach($product_gramasi[$item_product_id] as $dtgramasi){
											
											if(empty($dtInsert_stock_rekap[$returd_refd_id])){
												$dtInsert_stock_rekap[$returd_refd_id] = array();
											}
											
											$item_qty = $dtgramasi->item_qty*$dt['returd_qty'];
											$item_price = $dtgramasi->item_price*$item_qty;
											
											$item_hpp = ($dtgramasi->item_price*$dtgramasi->item_qty);
											$persentase_hpp = round(($item_hpp/$total_hpp_real),2);
											$returd_price_percent = round($returd_price*$persentase_hpp,0);
											$total_returd_price += $returd_price_percent;
											
											//average item hpp
											if(empty($all_item_updated_price[$dtgramasi->item_id])){
												$all_item_updated_price[$dtgramasi->item_id] = 0;
												$all_item_updated_price[$dtgramasi->item_id] = $returd_price_percent;
											}else{
												$all_item_updated_price[$dtgramasi->item_id] = ($all_item_updated_price[$dtgramasi->item_id] + $returd_price_percent) / 2;
											}
											$all_item_updated_price[$dtgramasi->item_id] = priceFormat($all_item_updated_price[$dtgramasi->item_id]);
											$all_item_updated_price[$dtgramasi->item_id] = numberFormat($all_item_updated_price[$dtgramasi->item_id]);
											
											//balancing
											if($total_returd_price > $returd_price){
												$selisih = $total_returd_price - $returd_price;
												$total_returd_price -= $selisih;
												$returd_price_percent -= $selisih;
											}
											
											if(empty($dtInsert_stock_rekap[$returd_refd_id][$dtgramasi->item_id])){
												
												//stok sesuai returd_refd_id
												$dtInsert_stock_rekap[$returd_refd_id][$dtgramasi->item_id] = array(
													"item_id" => $dtgramasi->item_id,
													"trx_date" => $returd_date,
													"trx_type" => 'in',
													"trx_qty" => 0,
													"unit_id" => $dtgramasi->unit_id,
													"trx_nominal" => 0,
													"storehouse_id" => $storehouse_id,
													"trx_note" => 'Retur '.$retur_ref_text.': '.$retur_type_text,
													"trx_ref_data" => $retur_number,
													"trx_ref_det_id" => $returd_refd_id,
													"is_active" => "1"
												);
												
											}
											
											$dtInsert_stock_rekap[$returd_refd_id][$dtgramasi->item_id]['trx_qty'] += $item_qty;
											$dtInsert_stock_rekap[$returd_refd_id][$dtgramasi->item_id]['trx_nominal'] += $returd_price_percent;
											
											
										}
										
									}
								}*/
								
								
							}
						}
						
					}
					
					if(empty($all_retur_refd_qty[$returd_refd_id])){
						$all_retur_refd_qty[$returd_refd_id] = 0;
					}
					
					$all_retur_refd_qty[$returd_refd_id] += ($dt['returd_qty'] - $returd_qty_before);
					
					
					//$total_qty += ($dt['returd_qty']);
					//$total_price += ($dt['returd_qty']*$dt['returd_price']);
					
					//check if new
					if(strstr($dt['id'], 'new_')){
						unset($dt['id']);
					}
						
					$dt['retur_id'] = $retur_id;
						
					if(empty($dt['id'])){
					
						if(!empty($dt['returd_qty'])){
							unset($dt['id']);
							$dtInsert[] = $dt;
						}
					
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

						
			//generate data stok
			if(!empty($dtInsert_stock_rekap)){
				foreach($dtInsert_stock_rekap as $refdID => $perItemId){
					if(!empty($perItemId)){
						foreach($perItemId as $dtStokItem){
							$dtInsert_stock[] = $dtStokItem;
						}
					}
					
				}
			}
			
			/*if(!empty($update_stok)){
				echo $update_stok.'<pre>';
				
				echo 'product_package_varian<br/>';
				print_r($product_package_varian);
				
				echo 'product_gramasi_varian<br/>';
				print_r($product_gramasi_varian);
				
				echo 'dtInsert_stock_rekap<br/>';
				print_r($dtInsert_stock_rekap);
				
				echo 'dtInsert_stock<br/>';
				print_r($dtInsert_stock);
				die();
			}*/
			
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
			
			if($update_stok == 'update' OR $update_stok == 'rollback'){
				
				if($update_stok == 'rollback'){
					//DELETE ALL STOCK
					$this->db->where("trx_ref_data", $retur_number);
					$this->db->delete($this->prefix."stock"); 
					
					$this->db->where("ref_in", $retur_number);
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
					$all_item_updated_txt = implode("','", $all_item_updated);
					$this->db->where("id IN ('".$all_item_updated_txt."')");
					$this->db->from($this->prefix.'items'); 
					$get_items = $this->db->get();
					if($get_items->num_rows() > 0){
						foreach($get_items->result() as $dt){
							
							if(!empty($all_item_updated_price[$dt->id])){
								
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
								
								$update_item_price_average[] = array(
									'id'			=> $dt->id,
									'item_hpp'		=> $item_hpp,
									//'last_in'		=> $all_item_updated_price[$dt->id],
									//'old_last_in'	=> $old_last_in
								);
								
								//update varian, gramasi hpp
								
							}
							
						}
					}
					
					if(!empty($update_item_price_average)){
						$this->db->update_batch($this->prefix."items", $update_item_price_average, "id");
					}
					
				}
			}
			
			return array('dtRetur' => $dt_rowguid, 'dtInsert' => $dtInsert, 'dtUpdate' => $dtUpdate, 'dtDelete' => $dtDelete, 
			'all_ref_item_qty' => $all_ref_item_qty, 'all_retur_refd_qty' => $all_retur_refd_qty, 
			'dtCurrent_qty_before' => $dtCurrent_qty_before, 'update_stock' => $update_stock_item_unit, 'retur_status' => $retur_status);
		}
	}

} 