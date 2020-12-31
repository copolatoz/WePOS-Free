<?php
class Model_MasterProductPriceQty extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'product_price';
	}

	public function cekPriceQty($data_post = array())
	{
		$this->table_product = $this->prefix.'product';
		$this->table_product_price = $this->prefix.'product_price';
		$this->table_product_varian = $this->prefix.'product_varian';
		
		if(empty($data_post)){
			$product_id = $this->input->post('product_id', true);		
			$order_qty = $this->input->post('order_qty', true);		
			$has_varian = $this->input->post('has_varian', true);		
			$varian_id = $this->input->post('varian_id', true);		
		}else{
			extract($data_post);
		}
		
		
		$r = array('success' => false, 'info' => 'Hapus Price Qty Gagal!'); 
		if(empty($product_id) OR empty($order_qty)){
			if(!empty($return_data)){
				return $r;
			}else{
				die(json_encode($r));
			}
		}
		
		$this->db->select('a.*');
		$this->db->from($this->table_product_price.' as a');
		$this->db->where("('".$order_qty."' BETWEEN a.qty_from AND a.qty_till)");
		$this->db->where("a.product_id = '".$product_id."' AND a.is_deleted = 0 AND a.is_active = 1");
		
		if(!empty($has_varian) AND !empty($varian_id)){
			$this->db->where("a.varian_id = '".$varian_id."'");
		}
		
		$this->db->order_by("a.qty_from","ASC");
		$get_dt_price = $this->db->get();
		
		$data_priceqty = array();
		if($get_dt_price->num_rows() > 0){
			$data_priceqty = $get_dt_price->row();
			$r = array('success' => true, 'product_price' => $data_priceqty->product_price); 
		} 
        else
        {  
			if(!empty($has_varian) AND !empty($varian_id)){
				$this->db->select('a.*');
				$this->db->from($this->table_product_varian.' as a');
				$this->db->where("a.product_id = '".$product_id."' AND a.varian_id = '".$varian_id."'");
				$get_dt_price = $this->db->get();
				if($get_dt_price->num_rows() > 0){
					$data_priceqty = $get_dt_price->row();
				}
			}
			
			if(empty($data_priceqty)){
				$this->db->select('a.*');
				$this->db->from($this->table_product.' as a');
				$this->db->where("a.id = '".$product_id."'");
				$get_dt_price = $this->db->get();
				if($get_dt_price->num_rows() > 0){
					$data_priceqty = $get_dt_price->row();
				}
			}
			
            $r = array('success' => true, 'product_price' => $data_priceqty->product_price); 
        }
		
		if(!empty($return_data)){
			return $r;
		}else{
			die(json_encode($r));
		}
	}
} 