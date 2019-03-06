<?php
class Model_MasterProductGramasi extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'product_gramasi';
	}

	function product_hpp($product_id = '', $varian_id = 0){
		
		$this->table = $this->prefix.'product_gramasi';
		$this->table_product = $this->prefix.'product';
		$this->table_product_varian = $this->prefix.'product_varian';
		$this->table_product_package = $this->prefix.'product_package';
		
		$product_hpp = 0;
		if(!empty($product_id)){
			
			//UPDATE GRAMASI & PRODUCT HPP
			$this->db->from($this->table);
			$this->db->where("product_id", $product_id);
			if(!empty($varian_id)){
				$this->db->where("varian_id", $varian_id);
			}
			$this->db->where("is_deleted", 0);
			$get_all_gramasi = $this->db->get();
			
			if($get_all_gramasi->num_rows() > 0){
				foreach($get_all_gramasi->result() as $dt){
					$product_hpp += $dt->item_qty*$dt->item_price;
				}
			}
			
			$update_data = array('product_hpp'	=> $product_hpp);
			$this->db->update($this->table_product, $update_data, "id = ".$product_id);
			
			//update varian
			if(!empty($varian_id)){
				$update_data = array('product_hpp'	=> $product_hpp);
				$this->db->update($this->table_product_varian, $update_data, "product_id = ".$product_id." AND varian_id = ".$varian_id);
			}
			
			//update package
			if(!empty($varian_id)){
				$update_data = array('product_hpp'	=> $product_hpp);
				$this->db->update($this->table_product_package, $update_data, "product_id = ".$product_id." AND varian_id_item = ".$varian_id);
			}
			
			//get all package & update hpp
			$all_package_id = array();
			$this->db->from($this->table_product_package);
			$this->db->where("product_id", $product_id);
			if(!empty($varian_id)){
				$this->db->where("varian_id_item", $varian_id);
			}
			$this->db->where("is_deleted", 0);
			$get_all_package = $this->db->get();
			
			if($get_all_package->num_rows() > 0){
				foreach($get_all_package->result() as $dt){
					$all_package_id[] = $dt->package_id;
				}
			}
			
			if(!empty($all_package_id)){
				$all_package_id_sql = implode(",", $all_package_id);
				
				$this->db->from($this->table_product_package);
				$this->db->where("package_id IN (".$all_package_id_sql.")");
				if(!empty($varian_id)){
					$this->db->where("varian_id", $varian_id);
				}
				$this->db->where("is_deleted", 0);
				$get_all_package = $this->db->get();
				
				$all_package_update = array();
				if($get_all_package->num_rows() > 0){
					foreach($get_all_package->result() as $dt){
						
						if(empty($all_package_update[$dt->package_id])){
							$all_package_update[$dt->package_id] = array(
								'id'	=> $dt->package_id,
								'product_hpp'	=> 0,
								'product_price'	=> 0,
								'normal_price'	=> 0,
							);
						}
						
						$all_package_update[$dt->package_id]['product_hpp'] += $dt->product_qty*$dt->product_hpp;
						$all_package_update[$dt->package_id]['product_price'] += $dt->product_qty*$dt->product_price;
						$all_package_update[$dt->package_id]['normal_price'] += $dt->product_qty*$dt->normal_price;
						
					}
				}
				
				$update_package_data = array();
				$update_package_varian_data = array();
				if(!empty($all_package_update)){
					foreach($all_package_update as $dt){
						$update_package_data[] = $dt;
						
						$dt['varian_id'] = $varian_id;
						$dt['product_id'] = $dt['id'];
						$update_package_varian_data[] = $dt;
						
					}
				}
				
				if(!empty($update_package_data)){
					$this->db->update_batch($this->table_product, $update_package_data, "id");
				}
				
				//update varian
				if(!empty($varian_id) AND !empty($update_package_varian_data)){
					foreach($update_package_varian_data as $update_varian){
						$update_data = array(
							'product_hpp'	=> $update_varian['product_hpp'], 
							'product_price' => $update_varian['product_price'], 
							'normal_price' => $update_varian['normal_price']
						);
						$this->db->update($this->table_product_varian, $update_data, "product_id = ".$update_varian['product_id']." AND varian_id = ".$update_varian['varian_id']);
					}
					
				}
				
			}
			
		}
		
		return array('product_hpp' => $product_hpp, 'varian_id' => $varian_id);
		
	}

	function update_sales_price($product_id = '', $item_id = '', $varian_id = 0){
		
		$this->table = $this->prefix.'product_gramasi';
		$this->table_product = $this->prefix.'product';
		$this->table_product_varian = $this->prefix.'product_varian';
		$this->table_item = $this->prefix.'items';
		
		$sales_price = 0;
		$all_hpp_item = array();
		$total_product = array();
		$selected_varian_id = 0;
		$min_product_price = 0;
		$selected_item = 0;
		if(!empty($product_id) AND !empty($item_id)){
			
			$total_gramasi = 0;
			$total_item = 0;
			$this->db->select("a.item_id, a.item_qty, a.item_price, a.varian_id, a.product_id, b.product_price, b.product_hpp, b.from_item, b.id_ref_item, c.product_price as product_price_varian");
			$this->db->from($this->table." as a");
			$this->db->join($this->table_product." as b","b.id = a.product_id","LEFT");
			$this->db->join($this->table_product_varian." as c","c.product_id = a.product_id AND c.varian_id = a.varian_id AND c.is_deleted = 0","LEFT");
			$this->db->where("a.product_id", $product_id);
			//$this->db->where("a.varian_id", $varian_id);
			//$this->db->where("a.item_id", $item_id);
			$this->db->where("a.is_deleted", 0);
			$get_all_gramasi = $this->db->get();
			if($get_all_gramasi->num_rows() > 0){
				$no = 0;
				foreach($get_all_gramasi->result() as $dt){
					$no++;
					
					if(empty($min_product_price)){
						$min_product_price = $dt->product_price;
						$selected_varian_id = $dt->varian_id;
					}else{
						if($min_product_price < $dt->product_price){
							$min_product_price = $dt->product_price;
							$selected_varian_id = $dt->varian_id;
						}
					}
					
					if($dt->from_item == 1 AND !empty($dt->id_ref_item) AND empty($selected_item)){
						$selected_item = $dt->id_ref_item;
					}
					
					//persentase
					$product_hpp = $dt->product_hpp;
					$product_price = $dt->product_price;
					$persentase_hpp = round(($product_price/$dt->item_qty) /$product_hpp, 3);
					$total_hpp = $dt->item_qty*$dt->item_price;
					$sales_price = round($persentase_hpp*$total_hpp, 2);
					
					//sales prise sebanyak qty
					$sales_price = round($persentase_hpp*$total_hpp, 2);
					
					$varId = $dt->varian_id;
					
					if(empty($total_product[$varId])){
						$total_product[$varId] = 0;
					}
					
					//balancing
					$total_product[$varId] += $sales_price;
					//echo '$total_product = '.$total_product[$varId].'<br/>';
					if($no == $get_all_gramasi->num_rows()){
						if($total_product[$varId] < $dt->product_price){
							$selisih = $dt->product_price - $total_product[$varId];
							$total_product[$varId]  += $selisih;
							$sales_price += $selisih;
							//echo '$selisih1 = '.$selisih;
						}else						
						if($total_product[$varId] > $dt->product_price){
							$selisih = $total_product[$varId] - $dt->product_price;
							$total_product[$varId]  -= $selisih;
							$sales_price -= $selisih;
							//echo '$total_product2 = '.$total_product[$varId].' -> '.$sales_price.'<br/>';
							//echo '$selisih2 = '.$selisih;
							
						}
					}
					
					$sales_price_satuan = 0;
					//konversi ke 1 satuan
					if($dt->item_qty >= 1){
						$sales_price_satuan = ($sales_price/$dt->item_qty);
					}else{
						$satuan_qty = 1/$dt->item_qty;
						$sales_price_satuan = ($sales_price/$satuan_qty);
					}
					
					
					if(empty($all_hpp_item[$varId])){
						$all_hpp_item[$varId] = array();
					}
					
					if(empty($all_hpp_item[$varId][$dt->item_id])){
						$all_hpp_item[$varId][$dt->item_id] = array(
							'item_id'	=> $dt->item_id,
							'persentase_hpp'	=> $persentase_hpp,
							'total_hpp'	=> $total_hpp,
							'item_price'	=> $dt->item_price,
							'sales_price'	=> $sales_price,
							'sales_price_satuan'	=> $sales_price_satuan,
							'product_hpp'	=> $dt->product_hpp,
							'product_price'	=> $dt->product_price,
							'item_qty'	=> $dt->item_qty
						);
					}
					
				}
				//echo '<pre>'; print_r($all_hpp_item);die();
				
				$varian_id = $selected_varian_id;
				
				$data_item_update = array();
				if(!empty($all_hpp_item[$varian_id])){
					
					$cek_item_data = true;
					if(!empty($selected_item)){
						$cek_item_data = false;
						//single item = product_id
						if(!empty($all_hpp_item[$varian_id][$selected_item])){
							$update_data = array('sales_price'	=> $all_hpp_item[$varian_id][$selected_item]['sales_price_satuan']);
							$this->db->update($this->table_item, $update_data, "id = ".$selected_item);
						}else{
							$cek_item_data = true;
						}
						
					}
					
					if($cek_item_data){
						
						
						if(count($all_hpp_item[$varian_id]) == 1){
						
							//single item = product_id
							if(!empty($all_hpp_item[$varian_id][$item_id])){
								$update_data = array(
									'sales_price'	=> $all_hpp_item[$varian_id][$item_id]['sales_price_satuan'],
									'use_for_sales'	=> 1,
									'id_ref_product'	=> $product_id
								);
								$this->db->update($this->table_item, $update_data, "id = ".$item_id);
							}
							
						}else{
							
							//use for sales = 0
							$use_for_sales_remove = array();
							foreach($all_hpp_item[$varian_id] as $dtI){
								
								$dtupdate = array('id' => $dtI['item_id'],'use_for_sales' => 0, 'id_ref_product' => 0);
								$updateItemBatch[] = $dtupdate;
								
							}
							
							if(!empty($updateItemBatch)){
								$this->db->update_batch($this->table_item, $updateItemBatch, "id");
							}
							
						}
						
						/*DONT UPDATE OTHER ITEM
						$harga_sales_varian = array();
						foreach($all_hpp_item[$varian_id] as $itemId => $dtI){
							
							if(empty($harga_sales_varian[$itemId])){
								$harga_sales_varian[$itemId] = $dtI['sales_price'];
							}
							
							//ambil paling kecil
							if($harga_sales_varian[$itemId] > $dtI['sales_price']){
								$harga_sales_varian[$itemId] = $dtI['sales_price'];
							}
							
						}
						
						//set harga
						if(!empty($harga_sales_varian)){
							$updateItemBatch = array();
							foreach($harga_sales_varian as $dtI => $sP){
								$dtupdate = array('id' => $dtI,'sales_price' => $sP);
								$updateItemBatch[] = $dtupdate;
							}
							//echo '<pre>'; print_r($updateItemBatch);
							if(!empty($updateItemBatch)){
								$this->db->update_batch($this->table_item, $updateItemBatch, "id");
							}
							
						}
						*/
						
					}
				}
					
			}
			
			
		}
		
		
	}

} 