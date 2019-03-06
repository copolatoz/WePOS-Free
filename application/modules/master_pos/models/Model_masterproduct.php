<?php
class Model_MasterProduct extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'product';
	}

	function update_sales_price($product_id = ''){
		
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
		if(!empty($product_id)){
			
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
					$persentase_hpp = round($product_price/$product_hpp, 3);
					$total_hpp = $dt->item_qty*$dt->item_price;
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
				
				//echo '$selected_varian_id = '.$selected_varian_id.', $min_product_price = '.$min_product_price;
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
						
						//gramasi isi 1 item -> mrepresentasikan nilai jual
						if(count($all_hpp_item[$varian_id]) == 1){
							
							foreach($all_hpp_item[$varian_id] as $itemId => $dtI){
							
								if(empty($harga_sales_varian[$itemId])){
									$harga_sales_varian[$itemId] = $dtI['sales_price_satuan'];
								}
								
								//ambil paling kecil
								if($harga_sales_varian[$itemId] > $dtI['sales_price_satuan']){
									$harga_sales_varian[$itemId] = $dtI['sales_price_satuan'];
								}
								
							}
							
							//set harga
							if(!empty($harga_sales_varian)){
								$updateItemBatch = array();
								foreach($harga_sales_varian as $dtI => $sales_price_satuan){
									$dtupdate = array('id' => $dtI,'sales_price' => $sales_price_satuan);
									$updateItemBatch[] = $dtupdate;
								}
								//echo '<pre>'; print_r($updateItemBatch);
								if(!empty($updateItemBatch)){
									$this->db->update_batch($this->table_item, $updateItemBatch, "id");
								}
								
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