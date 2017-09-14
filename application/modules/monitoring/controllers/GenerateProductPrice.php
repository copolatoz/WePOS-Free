<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class GenerateProductPrice extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->prefix_apps = config_item('db_prefix');
		$this->load->model('model_generateproductprice', 'm');
	}

	public function gridData()
	{
		$this->table_product = $this->prefix.'product';
		$this->table_product_varian = $this->prefix.'product_varian';
		$get_data = array();
		$newData = array();
		
		//GENERATED
		$this->prefix = config_item('db_prefix');
		$this->prefix2 = config_item('db_prefix2');
		$this->prefix3 = config_item('db_prefix3');
		
		
		$up_percentage = $this->input->post('up_percentage');
		$use_current_price = $this->input->post('use_current_price');
		$use_rounded = $this->input->post('use_rounded');
		$product_group = $this->input->post('product_group');
		$except_category_id = $this->input->post('except_category_id');
		$do_generate = $this->input->post('do_generate');
		$is_searching = $this->input->post('is_searching');
		$is_normal = $this->input->post('is_normal');
		$itemId = $this->input->post('itemId');
		$sort = $this->input->post('sort');
		
		$itemId = json_decode($itemId, true);
		
		if(empty($up_percentage)){
			$up_percentage = 0;
		}
		
		if(empty($product_group)){
			$product_group = 'all';
		}
		
		if(empty($except_category_id)){
			$except_category_id = '';
		}
		
		if(empty($is_normal)){
			$is_normal = '';
		}
		
		if(empty($use_current_price)){
			$use_current_price = 0;
		}
		
		$sort = json_decode($sort, true);
		$sortAlias = array(
			'product_category_name' => 'b.product_category_name',
			'product_category_id' => 'a.product_category_id',
			'product_group' => 'a.product_group',
			'product_name' => 'a.product_name'
		);	
		
		
		
		$total_all_product = 0;
		$total_update_product = 0;
		$all_product = array();
		$all_product_display = array();
		$all_product_normal = array();
		$this->db->select("a.*, b.product_category_name");
		$this->db->from($this->prefix2.'product as a');
		$this->db->join($this->prefix2.'product_category as b',"b.id = a.category_id","LEFT");
		
		if(!empty($itemId)){
			//$All_itemId = implode(",", $itemId);
			//$this->db->where("a.id IN (".$All_itemId.")");
		}
		
		if(!empty($sort[0]['property'])){
			$sortDt = strtr($sort[0]['property'], $sortAlias);
			$this->db->order_by($sortDt, $sort[0]['direction']);
		}else{
			$this->db->order_by('a.product_name', 'ASC');
		}
		
		$dt_product = $this->db->get();
		if($dt_product->num_rows() > 0){
			foreach($dt_product->result() as $dt){
				
				$update_product = false;
				
				if(!empty($dt->product_group)){
					if($product_group == $dt->product_group OR $product_group == 'all'){
						$update_product = true;
					}
				}
				
				if(!empty($except_category_id)){
					$except_array = explode(",", $except_category_id);
					if(in_array($dt->category_id,$except_array)){
						$update_product = false;
					}
				}
				
				
				$normal_price = $dt->normal_price;
				
				$total_all_product++;
				
				if($is_searching == 1){
					if(!empty($do_generate)){
						if(!empty($itemId)){
							if(in_array($dt->id, $itemId)){
								$update_product = true;
								$total_update_product++;
							}
						}
					}
				}
				
				if($update_product == true){
					
					if(!empty($use_current_price)){
						$normal_price = $dt->product_price;
					}
					
					$product_price = $normal_price;
					
					if(!empty($itemId)){
						if(in_array($dt->id, $itemId)){
							$up_price = ($product_price * ($up_percentage/100));
							$product_price = $product_price+$up_price;
						}else{
							$product_price = $dt->product_price;
						}
					}
					
					//cek jika ada di belakang koma
					$exp_harga = explode(".", $product_price);
					$product_price = $exp_harga[0];
					
					if(!empty($use_rounded)){
						//PEMBULATAN
						$total_use_rounded = 0;
						$max_use_rounded = 100;
						$use_rounded_keatas = 1;
						$last2digit = substr($product_price,-2);
						$last2digit = intval($last2digit);
						$total_use_rounded = $max_use_rounded - $last2digit;
						
						if($total_use_rounded == 100 OR $total_use_rounded == 0){
							$total_use_rounded = 0;
						}
						
						if(empty($use_rounded_keatas)){
							$total_use_rounded = $total_use_rounded*-1;
						}	
						$product_price += $total_use_rounded;
					}
					
					$data_update = array(
						'id'	=> $dt->id,
						'product_price'	=> $product_price
					);
					
					$all_product[] = $data_update;
					
					
					$data_simulation = array(
						'id'	=> $dt->id,
						'product_name'	=> $dt->product_name,
						'product_group'	=> $dt->product_group,
						'product_category_id'	=> $dt->category_id,
						'product_category_name'	=> $dt->product_category_name,
						'normal_hpp'	=> $dt->product_hpp,
						'normal_price'	=> $dt->normal_price,
						'normal_profit'	=> ($dt->normal_price - $dt->product_hpp),
						'current_hpp'	=> $dt->product_hpp,
						'current_price'	=> $dt->product_price,
						'current_profit'	=> ($dt->product_price - $dt->product_hpp),
						'simulation_hpp'	=> $dt->product_hpp,
						'simulation_price'	=> $product_price,
						'simulation_profit'	=> ($product_price - $dt->product_hpp)
					);
					
					if($do_generate == '' OR $is_searching == 0){
						$data_simulation['simulation_hpp'] = ''; 
						$data_simulation['simulation_price'] = ''; 
						$data_simulation['simulation_profit'] = ''; 
					}
					
					if($do_generate == 'generate' OR $do_generate == 'reset'){
						$data_simulation['current_hpp'] = $dt->product_hpp; 
						$data_simulation['current_price'] = $product_price; 
						$data_simulation['current_profit'] = ($product_price - $dt->product_hpp); 
						$data_simulation['simulation_hpp'] = ''; 
						$data_simulation['simulation_price'] = ''; 
						$data_simulation['simulation_profit'] = ''; 
					}
					
					$all_product_display[] = $data_simulation;
					
				}else{
					
					if(!empty($is_normal)){
						$data_update = array(
							'id'			=> $dt->id,
							'product_price'	=> $normal_price
						);
						
						$all_product_normal[] = $data_update;
					}
					
				}
				
				
			}
		}
		
		$all_product_varian = array();
		$all_product_varian_normal = array();
		
		$this->db->select("x.*, b.product_category_name, a.product_group, a.category_id");
		$this->db->from($this->prefix2.'product_varian as x');
		$this->db->join($this->prefix2.'product as a',"a.id = x.product_id","LEFT");
		$this->db->join($this->prefix2.'product_category as b',"b.id = a.category_id","LEFT");
		
		$dt_product_varian = $this->db->get();
		if($dt_product_varian->num_rows() > 0){
			foreach($dt_product_varian->result() as $dt){
				
				$update_product = false;
				
				if(!empty($dt->product_group)){
					if($product_group == $dt->product_group OR $product_group == 'all'){
						$update_product = true;
					}
				}
				
				
				if(!empty($except_category_id)){
					$except_array = explode(",", $except_category_id);
					if(in_array($dt->category_id,$except_array)){
						$update_product = false;
					}
				}
				
				$normal_price = $dt->normal_price;
				
				
				if($update_product == true){
				
					
					
					if(!empty($use_current_price)){
						$normal_price = $dt->product_price;
					}
					
					$product_price = $normal_price;
					
					if(!empty($itemId)){
						if(in_array($dt->product_id, $itemId)){
							$up_price = ($product_price * ($up_percentage/100));
							$product_price = $product_price+$up_price;
						}else{
							$product_price = $dt->product_price;
						}
					}
					
					//cek jika ada di belakang koma
					$exp_harga = explode(".", $product_price);
					$product_price = $exp_harga[0];
					
					if(!empty($use_rounded)){
						//PEMBULATAN
						$total_use_rounded = 0;
						$max_use_rounded = 100;
						$use_rounded_keatas = 1;
						$last2digit = substr($product_price,-2);
						$last2digit = intval($last2digit);
						$total_use_rounded = $max_use_rounded - $last2digit;
						
						if($total_use_rounded == 100 OR $total_use_rounded == 0){
							$total_use_rounded = 0;
						}
						
						if(empty($use_rounded_keatas)){
							$total_use_rounded = $total_use_rounded*-1;
						}	
						$product_price += $total_use_rounded;
					}
					
					$data_update = array(
						'id'	=> $dt->id,
						'product_price'	=> $product_price
					);
					
					$all_product_varian[] = $data_update;
					
				}else{
					if(!empty($is_normal)){
						$data_update = array(
							'id'			=> $dt->id,
							'product_price'	=> $normal_price
						);
						
						$all_product_varian_normal[] = $data_update;
					}
				}
				
				
			}
		}
		
		if($is_searching == 1){
			if($do_generate == 'generate' OR $do_generate == 'reset'){
				
				if(!empty($all_product)){
				
					$this->db->update_batch($this->prefix2.'product',$all_product,"id");
					
				}
				
				if(!empty($all_product_normal)){
					
					$this->db->update_batch($this->prefix2.'product',$all_product_normal,"id");
					
				}
				
				
				if(!empty($all_product_varian)){
					
					$this->db->update_batch($this->prefix2.'product_varian',$all_product_varian,"id");
					
				}
				
				if(!empty($all_product_varian_normal)){
					
					$this->db->update_batch($this->prefix2.'product_varian',$all_product_varian_normal,"id");
					
				}
				
			}
		}
		
		
		if(!empty($all_product_display)){
			foreach($all_product_display as $dt){
				$newData[] = $dt;
			}
		}
		
		$get_data['1_itemId'] = count($itemId);
		$get_data['1_update'] = $total_update_product;
		$get_data['2_all_product'] = $total_all_product;
		$get_data['3_not_update'] = $total_all_product-$total_update_product;
		$get_data['data'] = $newData;
		$get_data['totalCount'] = count($newData);
		
      	die(json_encode($get_data));
	}
	
	public function print_generateProductPrice()
	{
		$this->table_product = $this->prefix.'product';
		$this->table_product_varian = $this->prefix.'product_varian';
		$get_data = array();
		$newData = array();
		
		//GENERATED
		$this->prefix = config_item('db_prefix');
		$this->prefix2 = config_item('db_prefix2');
		$this->prefix3 = config_item('db_prefix3');
		
		$do = '';
		
		extract($_GET);
		
		$data_product = array();
		$this->db->select("a.*, b.product_category_name");
		$this->db->from($this->prefix2.'product as a');
		$this->db->join($this->prefix2.'product_category as b',"b.id = a.category_id","LEFT");
		$this->db->order_by('a.product_name', 'ASC');
		$dt_product = $this->db->get();
		if($dt_product->num_rows() > 0){
			foreach($dt_product->result() as $dt){
				
				$data_print = array(
					'id'	=> $dt->id,
					'product_name'	=> $dt->product_name,
					'product_group'	=> $dt->product_group,
					'product_category_id'	=> $dt->category_id,
					'product_category_name'	=> $dt->product_category_name,
					'normal_hpp'	=> $dt->product_hpp,
					'normal_price'	=> $dt->normal_price,
					'normal_profit'	=> ($dt->normal_price - $dt->product_hpp),
					'current_hpp'	=> $dt->product_hpp,
					'current_price'	=> $dt->product_price,
					'current_profit'	=> ($dt->product_price - $dt->product_hpp),
					'simulation_hpp'	=> '',
					'simulation_price'	=> '',
					'simulation_profit'	=> ''
				);
				
				$data_product[] = $data_print;
				
			}
		}
		
		//VARIAN
		$data_product_varian = array();
		$this->db->select("x.*, b.product_category_name, a.product_group, a.category_id, a.product_name");
		$this->db->from($this->prefix2.'product_varian as x');
		$this->db->join($this->prefix2.'product as a',"a.id = x.product_id","LEFT");
		$this->db->join($this->prefix2.'product_category as b',"b.id = a.category_id","LEFT");
		$dt_product_varian = $this->db->get();
		if($dt_product_varian->num_rows() > 0){
			foreach($dt_product_varian->result() as $dt){
				
				if(empty($data_product_varian[$dt->product_id])){
					$data_product_varian[$dt->product_id] = array();
				}
				
				$data_print = array(
					'id'	=> $dt->id,
					'product_name'	=> $dt->product_name.' ('.$dt->varian_name.')',
					'product_group'	=> $dt->product_group,
					'product_category_id'	=> $dt->category_id,
					'product_category_name'	=> $dt->product_category_name,
					'normal_hpp'	=> $dt->product_hpp,
					'normal_price'	=> $dt->normal_price,
					'normal_profit'	=> ($dt->normal_price - $dt->product_hpp),
					'current_hpp'	=> $dt->product_hpp,
					'current_price'	=> $dt->product_price,
					'current_profit'	=> ($dt->product_price - $dt->product_hpp),
					'simulation_hpp'	=> '',
					'simulation_price'	=> '',
					'simulation_profit'	=> ''
				);
				
				$data_product_varian[$dt->product_id][] = $data_update;
				
			}
		}
		
		$data_post['do'] = $do;
		$data_post['data_product'] = $data_product;
		$data_post['data_product_varian'] = $data_product_varian;
		$data_post['report_name'] = 'CURRENT PRICE PRODUCT';
		
		if($do == 'excel'){
			$this->load->view('../../monitoring/views/excel_generateProductPrice', $data_post);
		}else{
			$this->load->view('../../monitoring/views/print_generateProductPrice', $data_post);
		}
	}
}