<?php
class Model_MasterProductPackage extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'product_package';
	}

	function product_hpp($package_id = '', $varian_id = 0){
		
		$this->table = $this->prefix.'product_package';
		$this->table_product = $this->prefix.'product';
		$this->table_product_varian = $this->prefix.'product_varian';
		
		$product_hpp = 0;
		$product_price = 0;
		$normal_price = 0;
		if(!empty($package_id)){
			$this->db->from($this->table);
			$this->db->where("package_id", $package_id);
			if(!empty($varian_id)){
				$this->db->where("varian_id", $varian_id);
			}
			$this->db->where("is_deleted", 0);
			$get_all_package = $this->db->get();
			
			if($get_all_package->num_rows() > 0){
				foreach($get_all_package->result() as $dt){
					$product_hpp += $dt->product_qty*$dt->product_hpp;
					$product_price += $dt->product_qty*$dt->product_price;
					$normal_price += $dt->product_qty*$dt->normal_price;
				}
			}
			
			$update_data = array('product_hpp'	=> $product_hpp, 'product_price' => $product_price, 'normal_price' => $normal_price);
			$this->db->update($this->table_product, $update_data, "id = ".$package_id);
			
			//update varian
			if(!empty($varian_id)){
				$update_data = array('product_hpp'	=> $product_hpp, 'product_price' => $product_price, 'normal_price' => $normal_price);
				$this->db->update($this->table_product_varian, $update_data, "product_id = ".$package_id." AND varian_id = ".$varian_id);
			}
			
		}
		
		return array('product_hpp' => $product_hpp, 'product_price' => $product_price, 'normal_price' => $normal_price, 'varian_id' => $varian_id);
		
	}
	
} 