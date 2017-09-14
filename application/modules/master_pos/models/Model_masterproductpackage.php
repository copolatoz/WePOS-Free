<?php
class Model_MasterProductPackage extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'product_package';
	}

	function product_hpp($package_id = ''){
		
		$this->table = $this->prefix.'product_package';
		$this->table_product = $this->prefix.'product';
		
		$product_hpp = 0;
		$normal_price = 0;
		if(!empty($package_id)){
			$this->db->from($this->table);
			$this->db->where("package_id", $package_id);
			$this->db->where("is_deleted", 0);
			$get_all_package = $this->db->get();
			
			if($get_all_package->num_rows() > 0){
				foreach($get_all_package->result() as $dt){
					$product_hpp += $dt->product_hpp;
					$normal_price += $dt->product_price;
				}
			}
			
			$update_data = array('product_hpp'	=> $product_hpp, 'normal_price' => $normal_price);
			$this->db->update($this->table_product, $update_data, "id = ".$package_id);
			
		}
		
		return array('product_hpp' => $product_hpp, 'normal_price' => $normal_price);
		
	}
	
} 