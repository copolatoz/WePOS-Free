<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class BillingCashier extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_billingcashier', 'm');
		$this->load->model('model_billingcashierdetail', 'm2');
		$this->load->model('inventory/model_stock', 'stock');
				
	}

	public function gridData_billingDetail()
	{
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';
		$this->product_img_url = RESOURCES_URL.'product/thumb/';
				
		$session_user = $this->session->userdata('user_username');		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$billing_id = $this->input->post('billing_id', true);
		if(empty($billing_id)){
			$billing_id = -1;
		}
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'a.is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, 
								b.product_name, b.product_desc, b.product_type, b.product_image, 
								b.category_id, c.product_category_name",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table2.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'product as b','b.id = a.product_id','LEFT'),
										array($this->prefix.'product_category as c','c.id = b.category_id','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted' => 0, 'a.billing_id' => $billing_id),
			'order'			=> array('a.id' => 'ASC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.id' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.product_name  LIKE '%".$searching."%' OR a.product_name LIKE '%".$searching."%')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();
		$no = 1;
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				$s['item_no'] = $no;
				$s['order_total'] = $s['order_qty'] * $s['product_price'];
				
				if(empty($s['product_image'])){
					$s['product_image'] = 'no-image.jpg';
				}
				$s['product_image_show'] = '<img src="'.$this->product_img_url.$s['product_image'].'" style="max-width:80px; max-height:60px;"/>';
				$s['product_image_src'] = $this->product_img_url.$s['product_image'];
				
				$s['product_price_show'] = 'Rp '.priceFormat($s['product_price']);		
				$s['order_total_show'] = 'Rp '.priceFormat($s['order_total']);		
				
				$s['product_detail_info'] = $s['product_name'].'<br/>X @ Rp.'.priceFormat($s['product_price']);				
				
				$no++;
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function cancelOrder()
	{
		$this->table = $this->prefix.'billing_detail';
		$this->table2 = $this->prefix.'billing';
				
		$session_user = $this->session->userdata('user_username');		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		
		$opt_value = array(
			'wepos_tipe','retail_warehouse'
		);
		
		$get_opt = get_option_value($opt_value);
		
		$wepos_tipe = 'cafe';
		if(!empty($get_opt['wepos_tipe'])){
			$wepos_tipe = $get_opt['wepos_tipe'];
		}
		
		$get_id = $this->input->post('id', true);		
		$spv_valid = $this->input->post('spv_valid', true);		
		$keterangan = $this->input->post('keterangan', true);		
		$qty = $this->input->post('qty', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
		
		//CHECK IF BILLING IS NOT PAID
		$this->db->select("a.id, a.created, a.order_status, a.order_qty,
		a.product_price, a.discount_price, 
		b.id as billing_id, b.billing_no, b.billing_status, b.include_tax, b.include_service, 
		b.tax_percentage, b.service_percentage, b.takeaway_no_tax, b.takeaway_no_service,
		b.is_compliment");
		$this->db->from($this->table." as a");
		$this->db->join($this->table2." as b", "b.id = a.billing_id", "LEFT");
		$this->db->where("a.id IN (".$sql_Id.")");
		$this->db->where("a.is_deleted = 0");
		//$this->db->where("b.billing_status = 'paid'");
		$get_billing = $this->db->get();
		if($get_billing->num_rows() > 0){
			$billingData = $get_billing->row();
			
			if($billingData->billing_status == 'paid'){
				$r = array('success' => false, 'info' => 'Billing: '.$billingData->billing_no.' Been Paid!<br/>Cannot Hold Billing, Please Refresh List Billing'); 
				echo json_encode($r);
				die();
			}
			
			//$r = array('success' => false, 'info' => 'Billing: '.$billingData->billing_no.' Been Paid!<br/>Cannot Cancel Order, Please Refresh List Billing');
			//die(json_encode($r));
		}
		
		$r = array('success' => false, 'info' => 'Cancel Order Failed!'); 
		if(!empty($billingData)){
			
			//CLOSING DATE
			$var_closing = array(
				'xdate'	=> $billingData->created,
				'xtipe'	=> 'sales'
			);
			$is_closing = is_closing($var_closing);
			if($is_closing){
				$r = array('success' => false, 'info' => 'Sales Date Been Closed!'); 
				die(json_encode($r));
			}
			
			if($billingData->order_status == 'done'){
				
				$r = array('success' => false, 'info' => 'Cancel Order Failed!'); 
				if(!empty($spv_valid)){
					
					//CHECK
					if($billingData->order_qty < $qty){
						$r = array('success' => false, 'info' => 'Max Qty Cancel is '.$billingData->order_qty); 
						die(json_encode($r));
					}
					
					if($billingData->order_qty == $qty){
						//update to deleted = 0
						$update_order = array(
							'order_status'	=> 'cancel',
							'is_deleted'	=> 1,
							'cancel_order_notes'=> 'cancel order paid: '.$keterangan
						);
						$q = $this->db->update($this->table, $update_order, "id IN (".$sql_Id.")");
						$cancel_billing_detail_id = $sql_Id;
						 
					}else{
						
						$selisih_qty = $billingData->order_qty - $qty;
						$product_price = $billingData->product_price;
						$discount_price = $billingData->discount_price;
						
						//TAX, SERVICE, TAKE AWAY & COMPLIMENT
						$include_tax = $billingData->include_tax;
						$include_service = $billingData->include_service;
						$tax_percentage = $billingData->tax_percentage;
						$service_percentage = $billingData->service_percentage;
						$takeaway_no_tax = $billingData->takeaway_no_tax;
						$takeaway_no_service = $billingData->takeaway_no_service;
						$billing_is_compliment = $billingData->is_compliment;
						
						$tax_total = 0;
						$service_total = 0;
						$product_price_real = 0;
						if(!empty($include_tax) OR !empty($include_service)){
							if(!empty($include_tax) AND !empty($include_service)){
								$all_percentage = 100 + $tax_percentage + $service_percentage;
								$one_percent = $product_price / $all_percentage;
								$tax_total = priceFormat($one_percent * $tax_percentage, 0, ".", "");
								$service_total = priceFormat($one_percent * $service_percentage, 0, ".", "");
								$product_price_real = $product_price - ($tax_total + $service_total);
							}else{
								if(!empty($include_tax)){
									$all_percentage = 100 + $tax_percentage;
									$one_percent = $product_price / $all_percentage;
									$tax_total = priceFormat($one_percent * $tax_percentage, 0, ".", "");
									$product_price_real = $product_price - ($tax_total);
								}
								
								if(!empty($include_service)){
									$all_percentage = 100 + $service_percentage;
									$one_percent = $product_price / $all_percentage;
									$service_total = priceFormat($one_percent * $service_percentage, 0, ".", "");
									$product_price_real = $product_price - ($service_total);
								}
								
							}
						}else
						{
							$product_price_real = $product_price;
							$tax_percent = $tax_percentage/100;
							$service_percent = $service_percentage/100;
							$tax_total = priceFormat($product_price * $tax_percent, 0, ".", "");
							$service_total = priceFormat($product_price * $service_percent, 0, ".", "");
						}
						
						
						//copy product and cancel
						$this->db->select("a.*");
						$this->db->from($this->table." as a");
						$this->db->where("a.id IN (".$sql_Id.")");
						$get_data_detail = $this->db->get();
						$dt_detail = $get_data_detail->row_array();
						//update qty and status cancel
						$tax_total_cancel = $tax_total*$qty;
						$service_total_cancel = $service_total*$qty;
						$discount_total_cancel = $discount_price*$qty;
						
						$dt_detail['order_qty'] = $qty;
						$dt_detail['tax_total'] = $tax_total_cancel;
						$dt_detail['service_total'] = $service_total_cancel;
						$dt_detail['discount_total'] = $discount_total_cancel;
						$dt_detail['order_status'] = 'cancel';
						$dt_detail['is_deleted'] = '1';
						$dt_detail['cancel_order_notes'] = 'cancel order paid: '.$keterangan;
						unset($dt_detail['id']);
						$q = $this->db->insert($this->table, $dt_detail);
						$cancel_billing_detail_id = $this->db->insert_id();
						
						//echo 'insert_id = '.$cancel_billing_detail_id;
						//die();
						
						//UPDATE
						$tax_total_selisih = $tax_total*$selisih_qty;
						$service_total_selisih = $service_total*$selisih_qty;
						$discount_total_selisih = $discount_price*$selisih_qty;
						
						$update_order = array(
							'order_qty'	=> $selisih_qty,
							'tax_total'	=> $tax_total_selisih,
							'service_total'	=> $service_total_selisih,
							'discount_total'=> $discount_total_selisih
						);
						$q = $this->db->update($this->table, $update_order, "id IN (".$sql_Id.")");
						
						//$q = true; 
						//update main billing
						//$update_billing = $this->calculateBilling($billingData->billing_id);
					}
					
					if($q)  
					{  
						$getBilling = $this->getBilling($billingData->billing_id);	
						$update_billing = $this->calculateBilling($billingData->billing_id);
						if(!empty($update_billing)){
					
							$getBilling->total_billing = $update_billing['total_billing'];
							$getBilling->tax_total = $update_billing['tax_total'];
							$getBilling->service_total = $update_billing['service_total'];
							$getBilling->discount_total = $update_billing['discount_total'];
							$getBilling->grand_total = $update_billing['grand_total'];
							$getBilling->total_pembulatan = $update_billing['total_pembulatan'];
							$getBilling->total_dp = $update_billing['total_dp'];
							$getBilling->compliment_total = $update_billing['compliment_total'];
							$getBilling->compliment_total_tax_service = $update_billing['compliment_total_tax_service'];
							$getBilling->total_billing_display = $update_billing['total_billing_display'];
							
							$getBilling->total_billing_show =  priceFormat($getBilling->total_billing);
							$getBilling->tax_total_show =  priceFormat($getBilling->tax_total);
							$getBilling->service_total_show =  priceFormat($getBilling->service_total);
							$getBilling->discount_total_show =  priceFormat($getBilling->discount_total);
							$getBilling->grand_total_show =  priceFormat($getBilling->grand_total);
							$getBilling->total_pembulatan_show =  priceFormat($getBilling->total_pembulatan);
							$getBilling->total_dp_show =  priceFormat($getBilling->total_dp);
							$getBilling->compliment_total_show =  priceFormat($getBilling->compliment_total);
							$getBilling->compliment_total_tax_service_show =  priceFormat($getBilling->compliment_total_tax_service);
							
							$r['billingData'] = $getBilling;
						}
						
						//$r = array('success' => true);
						//echo json_encode($r);
						//PRINT CANCEL ORDER TO QC/BAR/KITCHEN
						//$this->doPrint('void_order', $billingData->id, $sql_Id);
						if($billingData->order_status == 'done' AND !empty($cancel_billing_detail_id)){
							
							if($wepos_tipe != 'retail'){
								//PRINT CANCEL ORDER TO QC/BAR/KITCHEN
								$r = $this->doPrint('void_order', $billingData->billing_id, $cancel_billing_detail_id);
							}else{
								$r = array('success' => true);
							}
							
							//print_r($r);
							$r['billingData'] = $billingData;
							
							
						}
						
						echo json_encode($r);
						die();
					}  
					else
					{  
						$r = array('success' => false, 'info' => 'Cancel Order Failed!', 'billingData' => $billingData); 
					}
				}
				
				
			}else{
				
				//Delete
				//$this->db->where("id IN (".$sql_Id.")");
				//$q = $this->db->delete($this->table);
				$update_order = array(
					'order_status'	=> 'cancel',
					'is_deleted'	=> 1,
					'cancel_order_notes'	=> 'cancel order unpaid: '.$keterangan
				);
				
				$q = $this->db->update($this->table, $update_order, "id IN (".$sql_Id.")");
				
				if($q)  
				{  
					$r = array('success' => true);
					
					$getBilling = $this->getBilling($billingData->billing_id);	
					$update_billing = $this->calculateBilling($billingData->billing_id);
					if(!empty($update_billing)){
				
						$getBilling->total_billing = $update_billing['total_billing'];
						$getBilling->tax_total = $update_billing['tax_total'];
						$getBilling->service_total = $update_billing['service_total'];
						$getBilling->discount_total = $update_billing['discount_total'];
						$getBilling->grand_total = $update_billing['grand_total'];
						$getBilling->total_pembulatan = $update_billing['total_pembulatan'];
						$getBilling->total_dp = $update_billing['total_dp'];
						$getBilling->compliment_total = $update_billing['compliment_total'];
						$getBilling->compliment_total_tax_service = $update_billing['compliment_total_tax_service'];
						$getBilling->total_billing_display = $update_billing['total_billing_display'];
						
						$getBilling->total_billing_show =  priceFormat($getBilling->total_billing);
						$getBilling->tax_total_show =  priceFormat($getBilling->tax_total);
						$getBilling->service_total_show =  priceFormat($getBilling->service_total);
						$getBilling->discount_total_show =  priceFormat($getBilling->discount_total);
						$getBilling->grand_total_show =  priceFormat($getBilling->grand_total);
						$getBilling->total_pembulatan_show =  priceFormat($getBilling->total_pembulatan);
						$getBilling->total_dp_show =  priceFormat($getBilling->total_dp);
						$getBilling->compliment_total_show =  priceFormat($getBilling->compliment_total);
						$getBilling->compliment_total_tax_service_show =  priceFormat($getBilling->compliment_total_tax_service);
						
						$r['billingData'] = $getBilling;
					}
				}  
				else
				{  
					$r = array('success' => false, 'info' => 'Cancel Order Failed!', 'billingData' => array()); 
				}
			}
		}
		
		die(json_encode($r));
	}
	
	public function createNewBilling(){
		$this->table = $this->prefix.'billing';		
				
		$session_user = $this->session->userdata('user_username');		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		//CLOSING DATE
		$var_closing = array(
			'xdate'	=> date("Y-m-d"),
			'xtipe'	=> 'sales'
		);
		$is_closing = is_closing($var_closing);
		if($is_closing){
			$r = array('success' => false, 'info' => 'Sales Date Been Closed!'); 
			die(json_encode($r));
		}
		
		/*$get_no_billing = $this->generate_billing_no();
		$r = array('success' => false, 'info' => 'User: '.$get_no_billing);
		echo json_encode($r);
		die();*/
		
		//hold_billing_id
		$hold_billing_id = $this->input->post('hold_billing_id', true);
		$table_id = $this->input->post('table_id', true);
		$holdBilling = false;
		if(!empty($hold_billing_id)){
			
			//CHECK IF BILLING IS NOT PAID
			$this->db->select("b.id, b.id as billing_id, b.billing_no, b.billing_status");
			$this->db->from($this->table." as b");
			$this->db->where("b.id = ".$hold_billing_id);
			//$this->db->where("b.billing_status = 'paid'");
			$get_billing = $this->db->get();
			if($get_billing->num_rows() > 0){
				$billingData = $get_billing->row();
			
				if($billingData->billing_status == 'unpaid' OR $billingData->billing_status == 'hold'){
					$holdBilling = $this->doHoldBilling($hold_billing_id);
					if($holdBilling == false){
						$r = array('success' => false, 'info' => 'Hold Billing Failed!');
						echo json_encode($r);
						die();
					}
				}
				
			}else{
				$r = array('success' => false, 'info' => 'Billing Id: #'.$hold_billing_id.' Not Found!');
				echo json_encode($r);
				die();
			}
			
			
		}
		
		$billingData = $this->getBilling();
		if($billingData == false OR empty($billingData->billing_id)){
			$r = array('success' => false, 'info' => 'Create New Billing Failed!');
			echo json_encode($r);
			die();
		}
		
		if(!empty($billingData->created)){
			$billingData->created_datetime = date('d.m.Y H:i', strtotime($billingData->created));
			
			//SAVE TO LOG
			//$this->logBilling($billingData, 'Create', 'Create Billing '.$billingData->billing_no);
		}
		
		$r = array('success' => true, 'billingData' => $billingData); 
		echo json_encode($r);
		die();
	}
	
	public function loadBilling(){
		
		
		$billing_id = $this->input->post('billing_id', true);
		
		$r = array('success' => false, 'info' => 'Billing Id Not Found!');
		if(empty($billing_id)){
			echo json_encode($r);
			die();
		}
		
		$getBilling = $this->getBilling($billing_id);	
		
		$update_billing = $this->calculateBilling($billing_id);
		if(!empty($update_billing)){
	
			$getBilling->total_billing = $update_billing['total_billing'];
			$getBilling->tax_total = $update_billing['tax_total'];
			$getBilling->service_total = $update_billing['service_total'];
			$getBilling->discount_total = $update_billing['discount_total'];
			$getBilling->grand_total = $update_billing['grand_total'];
			$getBilling->total_pembulatan = $update_billing['total_pembulatan'];
			$getBilling->total_dp = $update_billing['total_dp'];
			$getBilling->compliment_total = $update_billing['compliment_total'];
			$getBilling->compliment_total_tax_service = $update_billing['compliment_total_tax_service'];
			$getBilling->total_billing_display = $update_billing['total_billing_display'];
			
			$getBilling->total_billing_show =  priceFormat($getBilling->total_billing);
			$getBilling->tax_total_show =  priceFormat($getBilling->tax_total);
			$getBilling->service_total_show =  priceFormat($getBilling->service_total);
			$getBilling->discount_total_show =  priceFormat($getBilling->discount_total);
			$getBilling->grand_total_show =  priceFormat($getBilling->grand_total);
			$getBilling->total_pembulatan_show =  priceFormat($getBilling->total_pembulatan);
			$getBilling->total_dp_show =  priceFormat($getBilling->total_dp);
			$getBilling->compliment_total_show =  priceFormat($getBilling->compliment_total);
			$getBilling->compliment_total_tax_service_show =  priceFormat($getBilling->compliment_total_tax_service);
			
		}
		
		if(!empty($getBilling->total_billing)){
			$getBilling->total_billing_rp = 'Rp '.priceFormat($getBilling->total_billing);
		}
		
		$r = array('success' => true, 'billingData'	=> $getBilling);
		echo json_encode($r);
		die();
		
	}
	
	public function getBilling($billing_id = ''){
		
		$this->prefix_apps = config_item('db_prefix');
		$this->table = $this->prefix.'billing';	
		$session_user = $this->session->userdata('user_username');					
		
		$table_id = $this->input->post('table_id', true);
		
		if(empty($session_user)){
			return false;
		}
		
		$opt_var = array('include_tax','include_service',
		'default_tax_percentage','default_service_percentage',
		'takeaway_no_tax','takeaway_no_service','autohold_create_billing');
		$get_opt = get_option_value($opt_var);
		
		$include_tax = 0;
		if(!empty($get_opt['include_tax'])){
			$include_tax = $get_opt['include_tax'];
		}
		
		$include_service = 0;
		if(!empty($get_opt['include_service'])){
			$include_service = $get_opt['include_service'];
		}
		
		$default_tax_percentage = 10;
		if(!empty($get_opt['default_tax_percentage'])){
			$default_tax_percentage = $get_opt['default_tax_percentage'];
		}		
		
		$default_service_percentage = 5;
		if(!empty($get_opt['default_service_percentage'])){
			$default_service_percentage = $get_opt['default_service_percentage'];
		}		
		
		$takeaway_no_tax = 0;
		if(!empty($get_opt['takeaway_no_tax'])){
			$takeaway_no_tax = $get_opt['takeaway_no_tax'];
		}	
		
		$takeaway_no_service = 0;
		if(!empty($get_opt['takeaway_no_service'])){
			$takeaway_no_service = $get_opt['takeaway_no_service'];
		}
		
		//autohold_create_billing
		$autohold_create_billing = 0;
		if(!empty($get_opt['autohold_create_billing'])){
			$autohold_create_billing = $get_opt['autohold_create_billing'];
		}
		
		$is_new = false;
		if(empty($billing_id)){
			//CREATE BILLING
			$get_no_billing = $this->generate_billing_no();
			$date_now = date('Y-m-d H:i:s');
			$var = array(
				'fields'	=>	array(
				    'billing_no'  	=> 	$get_no_billing,
					'include_tax'	=>	$include_tax,
					'include_service'=>	$include_service,
					'tax_percentage'	=>	$default_tax_percentage,
					'service_percentage'=>	$default_service_percentage,
					'takeaway_no_tax'	=>	$takeaway_no_tax,
					'takeaway_no_service'=>	$takeaway_no_service,
					'created'		=>	$date_now,
					'createdby'		=>	$session_user,
					'updated'		=>	$date_now,
					'updatedby'		=>	$session_user
				),
				'table'		=>  $this->table
			);
			
			if(!empty($table_id)){
				$var['fields']['table_id'] = $table_id;
			}
			
			if($autohold_create_billing == 1){
				$var['fields']['billing_status'] = 'hold';
			}
			
			$insert_id = false;
			$this->lib_trans->begin();
				$q = $this->m->add($var);
				$billing_id = $this->m->get_insert_id();
			$this->lib_trans->commit();			
			if($q == false)
			{  
				return false;
			}else{
				$is_new = true;
			}
			
		}
		
		$billingData = array();
		$this->db->select('a.id, a.table_id, a.table_no, a.billing_no, a.payment_date,
			a.billing_status, a.billing_notes, a.total_pembulatan, a.total_billing, a.grand_total, a.total_paid, a.payment_id, a.bank_id,
			a.card_no, a.include_tax, a.tax_percentage, a.tax_total, a.include_service, a.service_percentage, a.service_total, 
			a.discount_id, a.discount_notes, a.discount_percentage, a.discount_price, a.discount_total, a.total_hpp, 
			a.is_active, a.total_dp, a.compliment_total, a.total_cash, a.total_credit, a.createdby, a.updatedby, 
			a.merge_id, a.merge_main_status, a.split_from_id, a.total_guest, a.lock_billing, a.qc_notes,
			a.created, a.updated, a.takeaway_no_tax, a.takeaway_no_service, a.is_compliment, 
			a.discount_perbilling, a.total_return, a.compliment_total_tax_service, a.is_half_payment,
			a.sales_id, a.sales_percentage, a.sales_price, a.sales_type, a.customer_id,  
			a.id as billing_id, a.voucher_no, a.is_sistem_tawar, a.single_rate, 
			b.table_name, b.table_no, b.table_desc, b.floorplan_id, c.floorplan_name, 
			d.payment_type_name, e.user_firstname, e.user_lastname, f.bank_name, 
			g.billing_no as merge_billing_no, h.sales_name, h.sales_company, i.customer_name');
		$this->db->from($this->table." as a");
		$this->db->join($this->prefix.'table as b','b.id = a.table_id','LEFT');
		$this->db->join($this->prefix.'floorplan as c','c.id = b.floorplan_id','LEFT');
		$this->db->join($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT');
		$this->db->join($this->prefix_apps.'users as e','e.user_username = a.updatedby','LEFT');
		$this->db->join($this->prefix.'bank as f','f.id = a.bank_id','LEFT');
		$this->db->join($this->prefix.'billing as g','g.id = a.merge_id','LEFT');
		$this->db->join($this->prefix.'sales as h','h.id = a.sales_id','LEFT');
		$this->db->join($this->prefix.'customer as i','i.id = a.customer_id','LEFT');
		
		$this->db->where('a.id', $billing_id);
		//$this->db->where('createdby', $session_user);
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$billingData = $get_last->row();	

			if(empty($billingData->merge_billing_no)){
				$billingData->merge_billing_no = '';
			}
			if(empty($billingData->payment_type_name)){
				$billingData->payment_type_name = '';
			}
			if(empty($billingData->floorplan_name)){
				$billingData->floorplan_name = '';
			}
			if(empty($billingData->table_name)){
				$billingData->table_name = '';
			}
			if(empty($billingData->bank_name)){
				$billingData->bank_name = '';
			}
			
			//sales
			//$billingData->sales_name = '';
			if(!empty($billingData->sales_id)){
				$sales_type_simple = 'A';
				if($billingData->sales_type == 'before_tax'){
					$sales_type_simple = 'B';
				}
				if(!empty($billingData->sales_percentage)){
					$jenis_fee = $billingData->sales_percentage.'%';
				}else{
					$jenis_fee = $billingData->sales_price;
				}
				
				$billingData->sales_name = $billingData->sales_name.' / '.$billingData->sales_company;
			}
		}
		
		if($is_new AND !empty($billingData)){
			$this->logBilling($billingData, 'Create', 'Create Billing '.$billingData->billing_no);
		}
		
		return $billingData;
	}
	
	public function getBillingDetailCounter(){
		
		$this->table = $this->prefix.'billing_detail';	
		$session_user = $this->session->userdata('user_username');					
				
		if(empty($session_user)){
			return false;
		}
		
		$this->table = $this->prefix.'billing_detail';						
		
		$dateToday = date('Ymd');
		$order_counter = 1;
		
		$this->db->select("order_counter");
		$this->db->from($this->table);
		$this->db->where('order_day_counter', $dateToday);
		$this->db->order_by('order_counter', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_billing_detail = $get_last->row();
			$order_counter = $data_billing_detail->order_counter;
			
			$order_counter += 1;
			
		}
		
		return $order_counter;	
	}
	
	public function generate_billing_no(){
		$this->table = $this->prefix.'billing';						
		$billing_date = date('ymd');
		
		$this->db->select("id,billing_no");
		$this->db->from($this->table);
		$this->db->where("billing_no LIKE '".$billing_date."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_billing = $get_last->row();
			$billing_no = $data_billing->billing_no;
			$billing_date = date('ymd');
			//CHECK IF VALID
			if(date('ymd') != substr($billing_no, 0, 6)){
				if(strtotime(date('Y-m-d')) <= strtotime(substr($billing_no, 0, 2)."-".substr($billing_no, 2, 2)."-".substr($billing_no, 4, 2))){
					//INCREMENT IF OLD DATE
					$billing_date = substr($billing_no, 0, 6);
					$billing_no = str_replace($billing_date,"",$billing_no);	
					
				}else{
					//ZERO IF NEXT DATE
					$billing_date = date('ymd');
					$billing_no = 0;
				}
				
			}else{			
				$billing_date = date('ymd');
				$billing_no = str_replace($billing_date,"",$billing_no);	
			}			
			$billing_no = (int) $billing_no;			
		}else{
			$billing_date = date('ymd');
			$billing_no = 0;
		}
		
		$billing_no++;
		$length_no = strlen($billing_no);
		switch ($length_no) {
			case 3:
				$billing_no = '0'.$billing_no;
				break;
			case 2:
				$billing_no = '00'.$billing_no;
				break;
			case 1:
				$billing_no = '000'.$billing_no;
				break;
			default:
				$billing_no = '000'.$billing_no;
				break;
		}
		
		$billing_no = $billing_date.$billing_no;
		
		return $billing_no;		
		
	}
		
	public function cancelBillingPaid(){
		$this->cancelBilling(true);
	}
	
	public function cancelBilling($is_paid = false){
		$this->table = $this->prefix.'billing';		
		
		$session_user = $this->session->userdata('user_username');					
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		//cancel_billing_id
		$cancel_billing_id = $this->input->post('cancel_billing_id', true);
		$cancel_billing_no = $this->input->post('cancel_billing_no', true);
		$cancel_notes = $this->input->post('cancel_notes', true);
		
		$billingData = array();
		
		//CHECK IF BILLING IS NOT PAID
		$this->db->select("b.id, b.billing_no, b.billing_status, b.created");
		$this->db->from($this->table." as b");
		$this->db->where("b.id = ".$cancel_billing_id);
		//$this->db->where("b.billing_status = 'paid'");
		$get_billing = $this->db->get();
		if($get_billing->num_rows() > 0){
			$billingData = $get_billing->row();
			
			//if($billingData->billing_status == 'paid'){
			//	$r = array('success' => false, 'info' => 'Billing: '.$billingData->billing_no.' Been Paid!<br/>Cannot Cancel Billing, Please Refresh List Billing'); 
			//	echo json_encode($r);
			//	die();
			//}
		}else{
			$r = array('success' => false, 'info' => 'Billing Id: #'.$cancel_billing_id.' Not Found!');
			echo json_encode($r);
			die();
		}
		
		//CLOSING DATE
		$var_closing = array(
			'xdate'	=> $billingData->created,
			'xtipe'	=> 'sales'
		);
		$is_closing = is_closing($var_closing);
		if($is_closing){
			$r = array('success' => false, 'info' => 'Sales Date Been Closed!'); 
			die(json_encode($r));
		}
		
		$cancelBilling = false;
		if(!empty($cancel_billing_id)){
			$cancelBilling = $this->doCancelBilling($cancel_billing_id, $is_paid, $cancel_notes);
			if($cancelBilling == false){
				$r = array('success' => false, 'info' => 'Cancel Billing Failed!');
				echo json_encode($r);
				die();
			}
		}
				
		//$billingData = array();
		$r = array('success' => true, 'billingData' => $billingData); 
		
		//SAVE TO LOG
		$this->logBilling($billingData, 'Cancel', 'Cancel Billing '.$billingData->billing_no);
		
		echo json_encode($r);
		die();
	}
	
	public function doCancelBilling($billing_id = '', $is_paid = false, $cancel_notes = ''){
		
		$this->table = $this->prefix.'billing';	
		$session_user = $this->session->userdata('user_username');	
		$role_id = $this->session->userdata('role_id');
		
		//hanya bisa cancel oleh cashier
		$get_opt_var = array('role_id_kasir');
		$get_opt = get_option_value($get_opt_var);
		
		//IF ONLY ROLE KASIR
		$role_id_kasir = 0;		
		if(!empty($get_opt['role_id_kasir'])){
			//$role_id_kasir = $get_opt['role_id_kasir'];
			
			$role_id_kasir = explode(",", $get_opt['role_id_kasir']);
			
		}
		
		if(!empty($role_id_kasir)){
			if(in_array($this->session->userdata('role_id'), $role_id_kasir) OR $role_id == 1){
				
			}else
			{
				return false;
			}
		}else
		{
			return false;
		}
		
		if(empty($session_user)){
			return false;	
		}
		
		if(empty($billing_id)){
			return false;			
		}
		
		
		$this->db->select('id, id as billing_id');
		$this->db->from($this->table);
		$this->db->where('id', $billing_id);
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_billing = $get_last->row();		
		}
		
		$date_now = date('Y-m-d H:i:s');
		
		$billing_status = 'cancel';
		if($is_paid){
			//$billing_status = 'unpaid';
		}
		
		if(!empty($data_billing)){
			//update status to cancel
			$var = array('fields'	=>	array(
				    'billing_status'  => $billing_status,
					'updated'		=>	$date_now,
					'updatedby'		=>	$session_user
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			if($billing_status == 'cancel'){
				$var['fields']['cancel_notes'] = $cancel_notes;
			}
			
			//UPDATE BILLING
			$this->lib_trans->begin();
				$update = $this->m->save($var, $billing_id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				
				//PRINT VOID PAID - CANCEL
				if($is_paid == true AND !empty($billing_id)){
					//$this->doPrint('void_paid_cancel', $billing_id);
				}
				
				$opt_value = array(
					'wepos_tipe','retail_warehouse'
				);
				
				$get_opt = get_option_value($opt_value);
				
				$wepos_tipe = 'cafe';
				if(!empty($get_opt['wepos_tipe'])){
					$wepos_tipe = $get_opt['wepos_tipe'];
				}
				
				$retail_warehouse = 0;
				if(!empty($get_opt['retail_warehouse'])){
					$retail_warehouse = $get_opt['retail_warehouse'];
				}
				
				//stok
				if($wepos_tipe == 'retail' AND !empty($retail_warehouse)){
					$update_stok = 'rollback';
					$return_data = $this->m2->billingDetail($billing_id, $retail_warehouse, $update_stok);
					
					if(!empty($return_data['update_stock'])){
						
						$r['update_stock'] = $return_data['update_stock'];
						$post_params = array(
							'storehouse_item'	=> $return_data['update_stock']
						);
						
						$updateStock = $this->stock->update_stock_rekap($post_params);
						
					}
					
				}
				
				return true;	
			}  
			else
			{  
				return false;	
			}
		}
		
		return false;
	}
	
		
	public function holdBillingPaid(){
		$this->holdBilling('cancel');
	}
	
	public function holdBilling($is_cancel = ''){
		$this->table = $this->prefix.'billing';		
							
		$session_user = $this->session->userdata('user_username');		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$billingData = array();
		
		//hold_billing_id
		$hold_billing_id = $this->input->post('hold_billing_id', true);
		if($is_cancel == 'cancel'){
			$hold_billing_id = $this->input->post('cancel_billing_id', true);
			
			$this->db->select("b.id, b.billing_no, b.billing_status, b.created");
			$this->db->from($this->table." as b");
			$this->db->where("b.id = ".$hold_billing_id);
			//$this->db->where("b.billing_status = 'paid'");
			$get_billing = $this->db->get();
			if($get_billing->num_rows() > 0){
				$billingData = $get_billing->row();
				
				//PRINT VOID PAID TO HOLD
				//$this->doPrint('void_paid_hold', $billingData->id);
				
			}else{
				$r = array('success' => false, 'info' => 'Billing Id: #'.$hold_billing_id.' Not Found!');
				echo json_encode($r);
				die();
			}
			
		}else{		
			//CHECK IF BILLING IS NOT PAID
			//$this->db->select("b.id, b.billing_no, b.billing_status");
			$this->db->from($this->table." as b");
			$this->db->where("b.id = ".$hold_billing_id);
			//$this->db->where("b.billing_status = 'paid'");
			$get_billing = $this->db->get();
			if($get_billing->num_rows() > 0){
				$billingData = $get_billing->row();
				
				if($billingData->billing_status == 'paid'){
					$r = array('success' => false, 'info' => 'Billing: '.$billingData->billing_no.' Been Paid!<br/>Cannot Hold Billing, Please Refresh List Billing'); 
					echo json_encode($r);
					die();
				}
			}else{
				$r = array('success' => false, 'info' => 'Billing Id: #'.$hold_billing_id.' Not Found!');
				echo json_encode($r);
				die();
			}
		}
		
		//CLOSING DATE
		$var_closing = array(
			'xdate'	=> $billingData->created,
			'xtipe'	=> 'sales'
		);
		$is_closing = is_closing($var_closing);
		if($is_closing){
			$r = array('success' => false, 'info' => 'Sales Date Been Closed!'); 
			die(json_encode($r));
		}
		
		$holdBilling = false;
		if(!empty($hold_billing_id)){
			$holdBilling = $this->doHoldBilling($hold_billing_id);
			if($holdBilling == false){
				$r = array('success' => false, 'info' => 'Hold Billing Failed!');
				echo json_encode($r);
				die();
			}
		}
		
		//$billingData = array();
		$r = array('success' => true, 'billingData' => $billingData); 
		
		if(!empty($billingData)){
			//SAVE TO LOG
			//$this->logBilling($billingData, 'Hold', 'Hold Billing '.$billingData->billing_no);
		}
		
		echo json_encode($r);
		die();
	}
	
	public function doHoldBilling($billing_id = ''){
		
		$this->table = $this->prefix.'billing';	
		$session_user = $this->session->userdata('user_username');					
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		if(empty($billing_id)){
			return false;			
		}
		
		
		$opt_value = array(
			'wepos_tipe','retail_warehouse'
		);
		
		$get_opt = get_option_value($opt_value);
		
		$wepos_tipe = 'cafe';
		if(!empty($get_opt['wepos_tipe'])){
			$wepos_tipe = $get_opt['wepos_tipe'];
		}
		
		$retail_warehouse = 0;
		if(!empty($get_opt['retail_warehouse'])){
			$retail_warehouse = $get_opt['retail_warehouse'];
		}
		
		$billingData = array();
		$this->db->select('*, id as billing_id');
		$this->db->from($this->table);
		$this->db->where('id', $billing_id);
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$billingData = $get_last->row();		
		}
		
		$date_now = date('Y-m-d H:i:s');
		
		if(!empty($billingData)){
			//update status to hold
			$var = array('fields'	=>	array(
				    'billing_status'  => 'hold',
					'updated'		=>	$date_now,
					'updatedby'		=>	$session_user
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			//UPDATE BILLING
			$this->lib_trans->begin();
				$update = $this->m->save($var, $billing_id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				if($billingData->billing_status == 'paid'){
					if($wepos_tipe == 'retail' AND !empty($retail_warehouse)){
						$update_stok = 'rollback';
						$return_data = $this->m2->billingDetail($billing_id, $retail_warehouse, $update_stok);
						
						if(!empty($return_data['update_stock'])){
							
							$r['update_stock'] = $return_data['update_stock'];
							$post_params = array(
								'storehouse_item'	=> $return_data['update_stock']
							);
							
							$updateStock = $this->stock->update_stock_rekap($post_params);
							
						}
						
					}
				}
			
				//SAVE TO LOG
				$this->logBilling($billingData, 'Hold', 'Hold Billing '.$billingData->billing_no);
				
				return true;	
			}  
			else
			{  
				return false;	
			}
		}
		
		return false;
	}
	
	/*SAVE ORDER*/
	public function save_orderProduct(){
		$this->table_billing = $this->prefix.'billing';				
		$this->table = $this->prefix.'billing';				
		$this->table2 = $this->prefix.'billing_detail';				
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$main_billing_id = $this->input->post('main_billing_id');
		
		//PRODUCT
		$billing_id = $this->input->post('billing_id');
		$product_id = $this->input->post('product_id');
		$category_id = $this->input->post('category_id');
		$product_price = $this->input->post('product_price');
		$product_price_hpp = $this->input->post('product_price_hpp');
		$product_normal_price = $this->input->post('product_normal_price');
		$product_name = $this->input->post('product_name');
		$order_qty = $this->input->post('order_qty');
		$order_notes = $this->input->post('order_notes');
		$product_varian_id = $this->input->post('product_varian_id');
		$varian_id = $this->input->post('varian_id');
		$has_varian = $this->input->post('has_varian');
		$is_takeaway = $this->input->post('is_takeaway');
		$is_compliment = $this->input->post('is_compliment');
		$is_promo = $this->input->post('is_promo');
		$promo_id = $this->input->post('promo_id');
		$promo_tipe = $this->input->post('promo_tipe');
		$promo_percentage = $this->input->post('promo_percentage');
		$promo_price = $this->input->post('promo_price');
		$promo_desc = $this->input->post('promo_desc');
		$use_tax = $this->input->post('use_tax');
		$use_service = $this->input->post('use_service');
		$is_kerjasama = $this->input->post('is_kerjasama');
		$supplier_id = $this->input->post('supplier_id');
		$persentase_bagi_hasil = $this->input->post('persentase_bagi_hasil');
		$total_bagi_hasil = $this->input->post('total_bagi_hasil');
		
		$is_buyget = $this->input->post('is_buyget');
		$buyget_id = $this->input->post('buyget_id');
		$buyget_tipe = $this->input->post('buyget_tipe');
		$buyget_percentage = $this->input->post('buyget_percentage');
		$buyget_qty = $this->input->post('buyget_qty');
		$buyget_desc = $this->input->post('buyget_desc');
		$buyget_item = $this->input->post('buyget_item');
		$free_item = $this->input->post('free_item');
		
		if($is_promo == 0 OR empty($promo_id)){
			$promo_tipe = 0;
			$promo_percentage = 0;
			$promo_price = 0;
			$promo_desc = '';
		}
		
		if($is_kerjasama == 0 OR empty($is_kerjasama)){
			$is_kerjasama = 0;
			$supplier_id = 0;
			$persentase_bagi_hasil = 0;
			$total_bagi_hasil = 0;
		}
		
		
		$form_type_orderProduct = $this->input->post('form_type_orderProduct');
		
		if(empty($main_billing_id)){
			
			$date_now = date('Y-m-d');
			//CLOSING DATE
			$var_closing = array(
				'xdate'	=> $date_now,
				'xtipe'	=> 'sales'
			);
			$is_closing = is_closing($var_closing);
			if($is_closing){
				$r = array('success' => false, 'info' => 'Sales Date Been Closed!', 'billingData' => ''); 
				die(json_encode($r));
			}
		}
		
		//CREATE BILLING WITH USER - IF EMPTY
		$billingData = $this->getBilling($main_billing_id);	
			
		if($form_type_orderProduct == 'add'){
			$main_billing_id = $billingData->billing_id;
			if(!empty($billingData->created)){
				$billingData->created_datetime = date('d-m-Y H:i', strtotime($billingData->created));
				
				//SAVE TO LOG
				//$this->logBilling($billingData, 'Create', 'Create Billing '.$billingData->billing_no);
			}			
		}
		
		if($billingData->lock_billing == 1){
			$r = array('success' => false, 'info' => 'Billing is locked by cashier<br/>Cannot do order!');
			echo json_encode($r);
			die();
		}
		
		if($billingData == false OR empty($main_billing_id)){
			$r = array('success' => false, 'info' => 'Billing not Found!', 'billingData' => $billingData);
			echo json_encode($r);
			die();
		}
		
		//CLOSING DATE
		$var_closing = array(
			'xdate'	=> $billingData->created,
			'xtipe'	=> 'sales'
		);
		$is_closing = is_closing($var_closing);
		if($is_closing){
			$r = array('success' => false, 'info' => 'Sales Date Been Closed!'); 
			die(json_encode($r));
		}
		
		//CHECK IF BILLING IS NOT PAID
		/*
		$this->db->select("b.*, b.id as billing_id");
		$this->db->from($this->table." as b");
		$this->db->where("b.id = ".$main_billing_id);
		//$this->db->where("b.billing_status = 'paid'");
		$get_billing = $this->db->get();
		if($get_billing->num_rows() > 0){
			$billingData = $get_billing->row();
			
			if($billingData->billing_status == 'paid'){
				$r = array('success' => false, 'info' => 'Billing: '.$billingData->billing_no.' Been Paid!<br/>Cannot Order, Please Refresh List Billing'); 
				echo json_encode($r);
				die();
			}
		}
		*/
		
		if($billingData->billing_status == 'paid'){
			$r = array('success' => false, 'info' => 'Billing: '.$billingData->billing_no.' Been Paid!<br/>Cannot Order, Please Refresh List Billing'); 
			echo json_encode($r);
			die();
		}
		
		//CHECK MEGRE
		$billing_id_before_merge = '';
		if(!empty($billingData->merge_id) AND !empty($billingData->merge_main_status)){
			$billing_id_before_merge = $billingData->merge_id;
		}
		
		//TAX, SERVICE, TAKE AWAY & COMPLIMENT
		$include_tax = $billingData->include_tax;
		$include_service = $billingData->include_service;
		$tax_percentage = $billingData->tax_percentage;
		$service_percentage = $billingData->service_percentage;
		$takeaway_no_tax = $billingData->takeaway_no_tax;
		$takeaway_no_service = $billingData->takeaway_no_service;
		$billing_is_compliment = $billingData->is_compliment;
		
		$tax_total = 0;
		$service_total = 0;
		$product_price_real = 0;
		if(!empty($include_tax) OR !empty($include_service)){
			if(!empty($include_tax) AND !empty($include_service)){
				$all_percentage = 100 + $tax_percentage + $service_percentage;
				$one_percent = $product_price / $all_percentage;
				$tax_total = priceFormat($one_percent * $tax_percentage, 0, ".", "");
				$service_total = priceFormat($one_percent * $service_percentage, 0, ".", "");
				$product_price_real = $product_price - ($tax_total + $service_total);
				
				$tax_percent = $tax_percentage/100;
				$service_percent = $service_percentage/100;
				$tax_total = priceFormat($product_price_real * $tax_percent, 0, ".", "");
				$service_total = priceFormat($product_price_real * $service_percent, 0, ".", "");
			
			}else{
				if(!empty($include_tax)){
					$all_percentage = 100 + $tax_percentage;
					$one_percent = $product_price / $all_percentage;
					$tax_total = priceFormat($one_percent * $tax_percentage, 0, ".", "");
					$product_price_real = $product_price - ($tax_total);
					
					$tax_percent = $tax_percentage/100;
					$tax_total = priceFormat($product_price_real * $tax_percent, 0, ".", "");
					
				}
				
				if(!empty($include_service)){
					$all_percentage = 100 + $service_percentage;
					$one_percent = $product_price / $all_percentage;
					$service_total = priceFormat($one_percent * $service_percentage, 0, ".", "");
					$product_price_real = $product_price - ($service_total);
					
					$service_percent = $service_percentage/100;
					$service_total = priceFormat($product_price_real * $service_percent, 0, ".", "");
					
				}
				
			}
		}else
		{
			$product_price_real = $product_price;
			$tax_percent = $tax_percentage/100;
			$service_percent = $service_percentage/100;
			$tax_total = priceFormat($product_price* $tax_percent, 0, ".", "");
			$service_total = priceFormat($product_price* $service_percent, 0, ".", "");
		}
		
		$tax_total = $tax_total*$order_qty;
		$service_total = $service_total*$order_qty;
		
		$buyget_total_peritem = 0;
		$buyget_total = 0;
		if($is_buyget == 1 OR !empty($buyget_id)){
			if($buyget_tipe == 'percentage'){
				$buyget_total_peritem = ($product_price_real * $buyget_percentage/100);
				$buyget_total = $buyget_total_peritem*$order_qty;
			}
		}
		
		$promo_total_peritem = 0;
		$promo_total = 0;
		if($is_promo == 1 OR !empty($promo_id)){
			if($promo_tipe == 1){
				$promo_total_peritem = $promo_price;
				$promo_total = $promo_total_peritem*$order_qty;
			}
		}
			
			
		if(empty($is_takeaway)){
			$is_takeaway = 0;
		}else{
			$is_takeaway = 1;
			
			//get takeaway config tas service default
			if(!empty($takeaway_no_tax)){
				$tax_percentage = 0;
				$tax_total = 0;
				$include_tax = 0;
			}
			
			if(!empty($takeaway_no_service)){
				$service_percentage = 0;
				$service_total = 0;
				$include_service = 0;
			}
			
		}
		
		//use_tax
		if(empty($use_tax)){
			$tax_percentage = 0;
			$tax_total = 0;
			$include_tax = 0;
		}
		
		//use_service
		if(empty($use_service)){
			$service_percentage = 0;
			$service_total = 0;
			$include_service = 0;
		}
		
		if(empty($is_compliment)){
			$is_compliment = 0;
		}else{
			$is_compliment = 1;
			
			if(!empty($include_tax) OR !empty($include_service)){
				$tax_total = 0;
				$service_total = 0;
				//$tax_percentage = 0;
				//$service_percentage = 0;
			}else{
				$tax_percentage = 0;
				$tax_total = 0;
				$service_percentage = 0;
				$service_total = 0;
			}
			
		
		}
		
		//BILLING COMPLIMENT
		if(empty($billing_is_compliment)){
			//$is_compliment = 0;
		}else{
			$is_compliment = 1;
			
			if(!empty($include_tax) OR !empty($include_service)){
				$tax_total = 0;
				$service_total = 0;
				//$tax_percentage = 0;
				//$service_percentage = 0;
			}else{
				$tax_percentage = 0;
				$tax_total = 0;
				$service_percentage = 0;
				$service_total = 0;
			}
		
		}
		
		$date_now = date('Y-m-d H:i:s');
		$created_datetime = date('d.m.Y H:i');
		
		if($is_kerjasama == 1){
			
			$total_bagi_hasil = numberFormat($product_price * $persentase_bagi_hasil / 100);
			
		}
		
		$r = '';
		if($form_type_orderProduct == 'add')
		{
			
			$opt_value = array(
				'cashier_max_pembulatan',
				'cashier_pembulatan_keatas',
				'pembulatan_dinamis',
				'use_order_counter',
				'wepos_tipe'
			);
			
			$get_opt = get_option_value($opt_value);
			
			$wepos_tipe = 'cafe';
			if(!empty($get_opt['wepos_tipe'])){
				$wepos_tipe = $get_opt['wepos_tipe'];
			}
			
			
			if(empty($get_opt['use_order_counter'])){
				$get_opt['use_order_counter'] = 0;
			}
			
			//GET COUNTER
			$order_day_counter = date('Ymd');
			if($get_opt['use_order_counter'] == 1){
				$order_counter = $this->getBillingDetailCounter();
			}else{
				$order_counter = 0;
			}
			
			$var = array(
				'fields'	=>	array(
				    'billing_id'  	=> 	$main_billing_id,
				    'billing_id_before_merge'  	=> 	$billing_id_before_merge,
					'product_id'	=>	$product_id,
					'category_id'	=>	$category_id,
					'product_varian_id'	=>	$product_varian_id,
					'varian_id'		=>	$varian_id,
					'has_varian'	=>	$has_varian,
					'include_tax'	=>	$include_tax,
					'tax_percentage'	=>	$tax_percentage,
					'tax_total'	=>	$tax_total,
					'include_service'	=>	$include_service,
					'service_percentage'	=>	$service_percentage,
					'service_total'	=>	$service_total,
					'is_takeaway'	=>	$is_takeaway,
					'takeaway_no_tax'	=>	$takeaway_no_tax,
					'takeaway_no_service'	=>	$takeaway_no_service,
					'is_compliment'	=>	$is_compliment,
					'product_price_real'	=>	$product_price_real,
					'product_price'	=>	$product_price,
					'product_price_hpp'		=>	$product_price_hpp,
					'product_normal_price'	=>	$product_normal_price,
					'order_qty'		=>	$order_qty,
					'order_notes'	=>	$order_notes,
					'order_status'	=>	'order',
					'order_counter'	=>	$order_counter,
					'order_day_counter'	=>	$order_day_counter,
					'created'		=>	$date_now,
					'createdby'		=>	$session_user,
					'updated'		=>	$date_now,
					'updatedby'		=>	$session_user,
					'is_promo'			=>	$is_promo,
					'promo_id'			=>	$promo_id,
					'promo_tipe'		=>	$promo_tipe,
					'promo_percentage'	=>	$promo_percentage,
					'promo_price'		=>	$promo_price,
					'promo_desc'		=>	$promo_desc,
					'is_buyget'			=>	$is_buyget,
					'buyget_id'			=>	$buyget_id,
					'buyget_tipe'		=>	$buyget_tipe,
					'buyget_percentage'	=>	$buyget_percentage,
					'buyget_total'		=>	$buyget_total,
					'buyget_qty'		=>	$buyget_qty,
					'buyget_desc'		=>	$buyget_desc,
					'buyget_item'		=>	$buyget_item,
					'is_kerjasama'		=>	$is_kerjasama,
					'supplier_id'		=>	$supplier_id,
					'persentase_bagi_hasil'		=>	$persentase_bagi_hasil,
					'total_bagi_hasil'			=>	$total_bagi_hasil,
					'grandtotal_bagi_hasil'		=>	$total_bagi_hasil*$order_qty
				),
				'table'		=>  $this->table2
			);	
			
			if($is_buyget == 1 OR !empty($buyget_id)){
				if($buyget_tipe == 'percentage'){
					$var['fields']['discount_id'] = $buyget_id;
					$var['fields']['discount_notes'] = $buyget_desc;
					$var['fields']['discount_percentage'] = $buyget_percentage;
					$var['fields']['discount_price'] = $buyget_total_peritem;
					$var['fields']['discount_total'] = $buyget_total;
				}
			}
			
			if($is_promo == 1 OR !empty($promo_id)){
				if($promo_tipe == 1){
					$var['fields']['product_price'] = $product_price_before_promo;
					$var['fields']['discount_id'] = $promo_id;
					$var['fields']['discount_notes'] = $promo_desc;
					$var['fields']['discount_percentage'] = $promo_percentage;
					$var['fields']['discount_price'] = $promo_price;
					$var['fields']['discount_total'] = $promo_total;
				}
			}
		
			if($wepos_tipe == 'retail'){
				$var['fields']['order_status'] = 'done';
			}
			
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
				$q = $this->m2->add($var);
				$insert_id = $this->m2->get_insert_id();
			$this->lib_trans->commit();			
			if($q)
			{  
				if(!empty($billingData->id)){
					//$update_billing = billingCashier->calculateBilling($billingData->id);
				}
				
				if($is_buyget == 1 OR !empty($buyget_id)){
					if($buyget_tipe == 'item'){
						
						//if($buyget_item != $product_id){
							//$category_id = 0;
							//$buyget_item = $product_id;
						//}
						
						if(!empty($buyget_item) AND $buyget_item != $product_id){
							//get product detail
							$this->db->select("*");
							$this->db->from($this->prefix.'product');
							$this->db->where("id",$buyget_item);
							$get_prod = $this->db->get();
							if($get_prod->num_rows() > 0){
								$data_prod = $get_prod->row();
								$category_id = $data_prod->category_id;
								$product_varian_id = 0;
								$varian_id = 0;
								$has_varian = $data_prod->has_varian;
								$product_price_hpp = $data_prod->product_hpp;
								$product_price = $data_prod->product_price;
								$product_price_real = $data_prod->product_price;
								$product_normal_price = $data_prod->normal_price;
							}
						}
						
						$data_free_item = array(
							'billing_id'  	=> 	$main_billing_id,
							'billing_id_before_merge'  	=> 	$billing_id_before_merge,
							'product_id'	=>	$buyget_item,
							'category_id'	=>	$category_id,
							'product_varian_id'	=>	$product_varian_id,
							'varian_id'		=>	$varian_id,
							'has_varian'	=>	$has_varian,
							'include_tax'	=>	$include_tax,
							'tax_percentage'	=>	$tax_percentage,
							'tax_total'			=>	0,
							'include_service'	=>	$include_service,
							'service_percentage'	=>	$service_percentage,
							'service_total'	=>	0,
							'is_takeaway'	=>	$is_takeaway,
							'takeaway_no_tax'	=>	$takeaway_no_tax,
							'takeaway_no_service'	=>	$takeaway_no_service,
							'is_compliment'	=>	$is_compliment,
							'product_price_real'	=>	$product_price_real,
							'product_price'	=>	$product_price,
							'product_price_hpp'		=>	$product_price_hpp,
							'product_normal_price'	=>	$product_normal_price,
							'order_qty'		=>	$buyget_qty,
							'order_notes'	=>	$order_notes,
							'order_status'	=>	'order',
							'order_counter'	=>	$order_counter,
							'order_day_counter'	=>	$order_day_counter,
							'created'		=>	$date_now,
							'createdby'		=>	$session_user,
							'updated'		=>	$date_now,
							'updatedby'		=>	$session_user,
							//'is_buyget'			=>	$is_buyget,
							'discount_id'		=>	$buyget_id,
							'discount_percentage'	=>	100,
							//'discount_price'		=>	$product_price_real,
							//'discount_total'		=>	($product_price_real*$buyget_qty),
							'discount_price'		=>	$product_price,
							'discount_total'		=>	($product_price*$buyget_qty),
							'discount_notes'		=>	$buyget_desc,
							'free_item'			=>	1,
							'ref_order_id'		=>	$insert_id
						);
						
						if(!empty($data_free_item)){
							$this->db->insert($this->table2,$data_free_item);
						}
					
					}
				}
				
				$r = array('success' => true, 'id' => $insert_id, 'billingData' => $billingData); 
				
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($form_type_orderProduct == 'edit'){
			
			$opt_value = array(
				'cashier_max_pembulatan',
				'cashier_pembulatan_keatas',
				'pembulatan_dinamis',
				'use_order_counter',
				'wepos_tipe'
			);
			
			$get_opt = get_option_value($opt_value);
			
			$wepos_tipe = 'cafe';
			if(!empty($get_opt['wepos_tipe'])){
				$wepos_tipe = $get_opt['wepos_tipe'];
			}
			
			$var = array('fields'	=>	array(
				    'billing_id'  	=> 	$main_billing_id,
					'product_id'	=>	$product_id,
					'category_id'	=>	$category_id,
					'product_varian_id'	=>	$product_varian_id,
					'varian_id'		=>	$varian_id,
					'has_varian'	=>	$has_varian,
					'include_tax'	=>	$include_tax,
					'tax_percentage'	=>	$tax_percentage,
					'tax_total'	=>	$tax_total,
					'include_service'	=>	$include_service,
					'service_percentage'	=>	$service_percentage,
					'service_total'	=>	$service_total,
					'is_takeaway'	=>	$is_takeaway,
					'takeaway_no_tax'	=>	$takeaway_no_tax,
					'takeaway_no_service'	=>	$takeaway_no_service,
					'is_compliment'	=>	$is_compliment,
					'product_price_real'	=>	$product_price_real,
					'product_price'	=>	$product_price,
					'product_price_real'	=>	$product_price_real,
					'product_price_hpp'		=>	$product_price_hpp,
					'product_normal_price'	=>	$product_normal_price,
					'order_qty'		=>	$order_qty,
					'order_notes'	=>	$order_notes,
					'updated'		=>	$date_now,
					'updatedby'		=>	$session_user,
					'is_promo'			=>	$is_promo,
					'promo_id'			=>	$promo_id,
					'promo_tipe'		=>	$promo_tipe,
					'promo_percentage'	=>	$promo_percentage,
					'promo_price'		=>	$promo_price,
					'promo_desc'		=>	$promo_desc,
					'is_buyget'			=>	$is_buyget,
					'buyget_id'			=>	$buyget_id,
					'buyget_tipe'		=>	$buyget_tipe,
					'buyget_percentage'	=>	$buyget_percentage,
					'buyget_total'		=>	$buyget_total,
					'buyget_qty'		=>	$buyget_qty,
					'buyget_desc'		=>	$buyget_desc,
					'buyget_item'		=>	$buyget_item,
					'is_kerjasama'		=>	$is_kerjasama,
					'supplier_id'		=>	$supplier_id,
					'persentase_bagi_hasil'		=>	$persentase_bagi_hasil,
					'total_bagi_hasil'			=>	$total_bagi_hasil,
					'grandtotal_bagi_hasil'		=>	$total_bagi_hasil*$order_qty
				),
				'table'			=>  $this->table2,
				'primary_key'	=>  'id'
			);
			
			if($wepos_tipe == 'retail'){
				$var['fields']['order_status'] = 'done';
			}
			
			if($is_buyget == 1 OR !empty($buyget_id)){
				if($buyget_tipe == 'percentage'){
					$var['fields']['discount_id'] = $buyget_id;
					$var['fields']['discount_notes'] = $buyget_desc;
					$var['fields']['discount_percentage'] = $buyget_percentage;
					$var['fields']['discount_price'] = $buyget_total_peritem;
					$var['fields']['discount_total'] = $buyget_total;
				}
			}
						
			if(!empty($free_item)){
				$var['fields']['tax_total'] = 0;
				$var['fields']['service_total'] = 0;
				$var['fields']['discount_price'] = $product_price_real;
				$var['fields']['discount_total'] = ($product_price_real*$order_qty);
			}
			
			if($is_promo == 1 OR !empty($promo_id)){
				if($promo_tipe == 1){
					$var['fields']['product_price'] = $product_price_before_promo;
					$var['fields']['discount_id'] = $promo_id;
					$var['fields']['discount_notes'] = $promo_desc;
					$var['fields']['discount_percentage'] = $promo_percentage;
					$var['fields']['discount_price'] = $promo_price;
					$var['fields']['discount_total'] = $promo_total;
				}
			}
			
			//echo $is_compliment.'<pre>';
			//print_r($var['fields']);
			//die($wepos_tipe);
			
			//UPDATE
			$id = $this->input->post('id', true);
			$this->lib_trans->begin();
				$q = $this->m2->save($var, $id);
			$this->lib_trans->commit();
			
			if($q)
			{  
				if(!empty($billingData->id)){
					
					//$update_billing = billingCashier->calculateBilling($billingData->id);
					
				}
				
				if($is_buyget == 1 OR !empty($buyget_id)){
					if($buyget_tipe == 'item'){
						
						//get item
						$this->db->from($this->table2);
						$this->db->where("ref_order_id = ".$id);
						$this->db->where("is_deleted = 0");
						$get_promo_item = $this->db->get();
						if($get_promo_item->num_rows() > 0){
							$dt_promo_item = $get_promo_item->row();
							
							$update_ref_free_item = array(
								'order_qty'	=> $buyget_qty,
								'tax_total'	=> 0,
								'service_total'	=> 0,
								'discount_price'	=> $dt_promo_item->product_price,
								'discount_total'	=> ($dt_promo_item->product_price*$buyget_qty),
							);
							
							$this->db->update($this->table2,$update_ref_free_item,"ref_order_id = ".$id);
						}
						
						
						
					}
				}
				
				$r = array('success' => true, 'id' => $id, 'billingData' => $billingData);
			}  
			else
			{  
				$r = array('success' => false);
			}
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function calculateBilling($billing_id = ''){
		
		$this->table_billing = $this->prefix.'billing';
		$this->table_billing_detail = $this->prefix.'billing_detail';
		$this->table_discount = $this->prefix.'discount';
		$this->table_discount_product = $this->prefix.'discount_product';
		
		$calculate = $this->input->post('calculate');
		if(empty($billing_id)){
			$billing_id = $this->input->post('billing_id');
		}
		
		if(!empty($billing_id)){
			
			
			$get_opt_var = array('use_pembulatan','cashier_max_pembulatan','cashier_pembulatan_keatas','pembulatan_dinamis','diskon_sebelum_pajak_service');
			$get_opt = get_option_value($get_opt_var);
			
			$diskon_sebelum_pajak_service = 0;
			if(!empty($get_opt['diskon_sebelum_pajak_service'])){
				$diskon_sebelum_pajak_service = $get_opt['diskon_sebelum_pajak_service'];
			}
			
			//check billing
			$billingData = array();
			if(!empty($billing_id)){
				$this->db->select("id, takeaway_no_tax, takeaway_no_service, 
				is_compliment, total_dp, discount_perbilling, discount_total,
				include_tax, include_service, tax_percentage, service_percentage");
				$this->db->from($this->table_billing);
				$this->db->where("id", $billing_id);
				$get_billing = $this->db->get();
				if($get_billing->num_rows() > 0){
					$billingData = $get_billing->row();
				}
			}
			
			//UPDATE DETAIL
			$grand_total_all = 0;
			$total_billing_all = 0;
			$tax_total_all = 0;
			$service_total_all = 0;
			$discount_total_all = 0;
			$compliment_total_all = 0;
			$compliment_total_tax_service_all = 0;
			
			$all_detail_update = array();
			$this->db->select("id, product_price, order_qty, 
				is_takeaway, is_compliment, discount_price, discount_total, 
				include_tax, include_service, tax_percentage, service_percentage, is_promo, promo_price, free_item");
			$this->db->from($this->table_billing_detail);
			$this->db->where('billing_id', $billing_id);
			$this->db->where('is_deleted = 0');
			$get_detail = $this->db->get();
			if($get_detail->num_rows() > 0){
				foreach($get_detail->result() as $dt){
					
					$product_price = $dt->product_price;
					$order_qty = $dt->order_qty;
					$is_takeaway = $dt->is_takeaway;
					$is_compliment = $dt->is_compliment;
					
					//TAX, SERVICE, TAKE AWAY & COMPLIMENT
					$include_tax = $dt->include_tax;
					$include_service = $dt->include_service;
					$tax_percentage = $dt->tax_percentage;
					$service_percentage = $dt->service_percentage;
					$discount_price = $dt->discount_price;
					$discount_total = $dt->discount_price*$order_qty;
					//$include_tax = $billingData->include_tax;
					//$include_service = $billingData->include_service;
					//$tax_percentage = $billingData->tax_percentage;
					//$service_percentage = $billingData->service_percentage;
					$takeaway_no_tax = $billingData->takeaway_no_tax;
					$takeaway_no_service = $billingData->takeaway_no_service;
					$billing_is_compliment = $billingData->is_compliment;
					
					//BALANCING OLD DATA
					if($is_compliment == 1){
						if($tax_percentage == '0.00' AND !empty($billingData->tax_percentage)){
							$tax_percentage = $billingData->tax_percentage;
						}
						if($service_percentage == '0.00' AND !empty($billingData->service_percentage)){
							$service_percentage = $billingData->service_percentage;
						}
					}
					
					if(empty($billingData->total_dp)){
						$billingData->total_dp = 0;
					}
					$total_dp = $billingData->total_dp;
					
					//Promo
					if($dt->is_promo == 1){
						$product_price = ($dt->product_price - $dt->promo_price);
					}
					
					$tax_total = 0;
					$service_total = 0;
					$product_price_real = 0;
					if(!empty($include_tax) OR !empty($include_service)){
						if(!empty($include_tax) AND !empty($include_service)){
							$all_percentage = 100 + $tax_percentage + $service_percentage;
							$one_percent = $product_price / $all_percentage;
							$tax_total = priceFormat($one_percent * $tax_percentage, 0, ".", "");
							$service_total = priceFormat($one_percent * $service_percentage, 0, ".", "");
							$product_price_real = $product_price - ($tax_total + $service_total);
							
							//re-calculate tax service
							if($diskon_sebelum_pajak_service == 1 AND !empty($discount_price) AND $dt->is_promo == 0){
								$product_price_real_disc = $product_price_real-$discount_price;
								$tax_total = priceFormat($product_price_real_disc * ($tax_percentage/100), 0, ".", "");
								$service_total = priceFormat($product_price_real_disc * ($service_percentage/100), 0, ".", "");
							}
							
							
						}else{
							
							if(!empty($include_tax)){
								$all_percentage = 100 + $tax_percentage;
								$one_percent = $product_price / $all_percentage;
								$tax_total = priceFormat($one_percent * $tax_percentage, 0, ".", "");
								$product_price_real = $product_price - ($tax_total);
								
								//re-calculate tax service
								if($diskon_sebelum_pajak_service == 1 AND !empty($discount_price) AND $dt->is_promo == 0){
									$product_price_real_disc = $product_price_real-$discount_price;
									$tax_total = priceFormat($product_price_real_disc * ($tax_percentage/100), 0, ".", "");
								}
								
							}
							
							if(!empty($include_service)){
								$all_percentage = 100 + $service_percentage;
								$one_percent = $product_price / $all_percentage;
								$service_total = priceFormat($one_percent * $service_percentage, 0, ".", "");
								$product_price_real = $product_price - ($service_total);
								
								//re-calculate tax service
								if($diskon_sebelum_pajak_service == 1 AND !empty($discount_price) AND $dt->is_promo == 0){
									$product_price_real_disc = $product_price_real-$discount_price;
									$service_total = priceFormat($product_price_real_disc * ($service_percentage/100), 0, ".", "");
								}
								
							}
							
						}
					}else
					{
						$product_price_real = $product_price;
						$tax_percent = $tax_percentage/100;
						$service_percent = $service_percentage/100;
						$tax_total = priceFormat($product_price * $tax_percent, 0, ".", "");
						$service_total = priceFormat($product_price * $service_percent, 0, ".", "");
						
						//re-calculate tax service
						if($diskon_sebelum_pajak_service == 1 AND !empty($discount_price) AND $dt->is_promo == 0){
							$product_price_real_disc = $product_price_real-$discount_price;
							$tax_total = priceFormat($product_price_real_disc * ($tax_percentage/100), 0, ".", "");
							$service_total = priceFormat($product_price_real_disc * ($service_percentage/100), 0, ".", "");
						}
						
					}
					
					if(empty($is_takeaway)){
						$is_takeaway = 0;
					}else{
						$is_takeaway = 1;
						
						//get takeaway config tas service default
						if(!empty($takeaway_no_tax)){
							$tax_percentage = 0;
							$tax_total = 0;
						}
						
						if(!empty($takeaway_no_service)){
							$service_percentage = 0;
							$service_total = 0;
						}
						
					}
					
					
					
					if(empty($is_compliment)){
						$is_compliment = 0;
					}else{
						$is_compliment = 1;
						
						if(!empty($include_tax) OR !empty($include_service)){
							$tax_total = 0;
							$service_total = 0;
							//$tax_percentage = 0;
							//$service_percentage = 0;
						}else{
							$tax_percentage = 0;
							$tax_total = 0;
							$service_percentage = 0;
							$service_total = 0;
							//$product_price = 0;
							//$product_price_real = 0;
							//$discount_total = 0;
							
						}
						
					}
					
					//BILLING COMPLIMENT
					if(empty($billing_is_compliment)){
						//$is_compliment = 0;
					}else{
						$is_compliment = 1;
						
						if(!empty($include_tax) OR !empty($include_service)){
							$tax_total = 0;
							$service_total = 0;
							//$tax_percentage = 0;
							//$service_percentage = 0;
						}else
						{
							
							$tax_percentage = 0;
							$tax_total = 0;
							$service_percentage = 0;
							$service_total = 0;
							//$product_price = 0;
							//$product_price_real = 0;
							//$discount_total = 0;
							
						}
						
					
					}
					
					if($dt->free_item == 1){
						$tax_percentage = 0;
						$tax_total = 0;
						$service_percentage = 0;
						$service_total = 0;
						$product_price = $dt->product_price;
						$product_price_real = $dt->product_price;
						//$discount_price = $product_price;
					}
					
					
					$tax_total_update = ($tax_total*$order_qty);
					$service_total_update = ($service_total*$order_qty);
					$discount_total = ($discount_price*$order_qty);
					//echo 'is_compliment = '.$is_compliment.'<br/>';
					//echo 'grand_total_all = '.$grand_total_all.' +'.$product_price_real.' => '.($grand_total_all+$product_price_real).'<br/>';
					
					//REAL TOTAL
					$grand_total_all += ($product_price_real*$order_qty);
					//$total_billing_all += ($product_price_real*$order_qty);
					$tax_total_all += ($tax_total*$order_qty);
					$grand_total_all += ($tax_total*$order_qty);
					$service_total_all += ($service_total*$order_qty);
					$grand_total_all += ($service_total*$order_qty);
					
					$discount_total_all += $discount_total;
					
					if($dt->is_promo == 1){
						$grand_total_all += $discount_total;
						$total_billing_all += ($dt->product_price*$order_qty);
					}else{
						$total_billing_all += ($dt->product_price*$order_qty);
						//$total_billing_all += ($product_price_real*$order_qty);
					}
					
					if(empty($include_tax) OR empty($include_service)){
						if(empty($include_tax)){
							//$grand_total_all += ($tax_total*$order_qty);
						}
						if(empty($include_service)){
							//$grand_total_all += ($service_total*$order_qty);
						}
					}
					
					//echo 'grand_total_all = '.$grand_total_all.' - '.$discount_total_all.'<br/>';
					
					if(!empty($is_compliment)){
						//COMPLIMENT -------------
						$compliment_total = ($product_price_real*$order_qty);
						$compliment_total_all += $compliment_total;
						$compliment_total_tax_service = ($product_price*$order_qty);
						$compliment_total_tax_service_all += $compliment_total_tax_service;
					}
					
					if(empty($billingData->discount_perbilling)){
						//$grand_total_all -= $discount_total;
					}
					
					
					$all_detail_update[] = array(
						'id'			=> $dt->id,
						//'product_price_real'	=> $product_price_real,
						//'order_qty'	=> $order_qty,
						//'discount_price'	=> $discount_price,
						'discount_total'	=> $discount_total,
						'tax_total'			=> $tax_total_update,
						//'tax_total_update'			=> $tax_total_update,
						//'tax_percentage'	=> $tax_percentage,
						'service_total'			=> $service_total_update,
						//'service_total_update'			=> $service_total_update,
						//'service_percentage'	=> $service_percentage
					);
					
					
				}
				
				//UPDATE DETAIL
				if(!empty($all_detail_update)){
					$this->db->update_batch($this->table_billing_detail,$all_detail_update,"id");
					//echo '<pre>';
					//print_r($all_detail_update);
					//die();
				}
				
				
				
			}
			
			//DP
			$total_dp = $billingData->total_dp;
			$grand_total_all -= $total_dp;
			
			//discount
			if(!empty($billingData->discount_perbilling)){
				$discount_total_all = $billingData->discount_total;
			}
			$grand_total_all -= $discount_total_all;
			
			//compliment
			//if(!empty($billingData->compliment_total)){
			//	$compliment_total_all = $billingData->compliment_total;
			//}
			
			//if(!empty($billingData->compliment_total_tax_service)){
			//	$compliment_total_tax_service_all = $billingData->compliment_total_tax_service;
			//}
			
			//echo 'compliment_total_all = '.$compliment_total_all.'<br/>';
			//echo 'grand_total_all = '.$grand_total_all.'<br/>';
			
			$grand_total_all -= $compliment_total_all;
			
			if($grand_total_all <= 0){
				$grand_total_all = 0;
			}
			
			//PEMBULATAN				
			$total_pembulatan = 0;
			$max_pembulatan = $get_opt['cashier_max_pembulatan'];
			$pembulatan_keatas = $get_opt['cashier_pembulatan_keatas'];
			$pembulatan_dinamis = $get_opt['pembulatan_dinamis'];
			$grand_total_all_awal = $grand_total_all;
			$grand_total_all_exp = explode(".",$grand_total_all);
			$koma = 0;
			if(!empty($grand_total_all_exp[1])){
				$grand_total_all = $grand_total_all_exp[0];
				$koma = "0.".$grand_total_all_exp[1];
			}
			$last2digit = substr($grand_total_all,-2);
			$last2digit = intval($last2digit);
			
			if(!empty($koma)){
				$last2digit += floatval($koma);
			}
			
			//dibawah max pembulatan
			if($last2digit > 0){
				if(empty($pembulatan_keatas)){
					
					//$total_pembulatan = $last2digit;
					$total_pembulatan = $last2digit*-1;
					
					if(!empty($pembulatan_dinamis)){
						if($last2digit <= 50){
							$total_pembulatan = $last2digit*-1;
						}else{
							$total_pembulatan = $max_pembulatan - $last2digit;
						}
					}
					
				}else{
					
					$total_pembulatan = $max_pembulatan - $last2digit;
					
				}
			}
			
			if($total_pembulatan == $max_pembulatan OR $total_pembulatan == 0){
				$total_pembulatan = 0;
			}
			
			if(empty($get_opt['use_pembulatan'])){

 				$total_pembulatan = 0;

 			}
			
			$grand_total_all = $grand_total_all_awal+$total_pembulatan;
			
			//die($grand_total_all);
			$update_total = array(
				'grand_total'	=> $grand_total_all,
				'total_billing'	=> $total_billing_all,
				'tax_total'		=> $tax_total_all,
				'service_total'	=> $service_total_all,
				'discount_total' => $discount_total_all,
				'total_pembulatan' => $total_pembulatan,
				'compliment_total' => $compliment_total_all,
				'compliment_total_tax_service' => $compliment_total_tax_service_all,
				'total_dp'		=> $total_dp
			);
			$this->db->update($this->table_billing, $update_total, "id = ".$billing_id);
			
			
			
			$total_billing_display = 0;
			if(!empty($billingData->include_tax) OR !empty($billingData->include_service)){
				$total_billing_display = $total_billing_all;
				
				if(!empty($billingData->include_tax)){
					$total_billing_display += $tax_total_all;
				}
				if(!empty($billingData->include_service)){
					$total_billing_display += $service_total_all;
				}
				
			}else{
				$total_billing_display = $total_billing_all;
			}
			
			$total_billing_display = $grand_total_all;
			
			$update_total['total_billing_display'] = $total_billing_display;
			$update_total['compliment_total'] = $compliment_total_all;
			$update_total['compliment_total_tax_service'] = $compliment_total_tax_service_all;
			
			//echo '<pre>';
			//print_r($update_total);
			//die();
			
			if($calculate == 1){
				$r = array('success' => true);
				echo json_encode($r);
				die();
			}
			
			return $update_total;
		}
		
	}
	
	/*PAY BILLING*/
	public function save_payBilling(){
		$this->table = $this->prefix.'billing';				
		$this->table2 = $this->prefix.'billing_detail';				
		$this->table_inv = $this->prefix.'table_inventory';				
		$this->table_storehouse_users = $this->prefix.'storehouse_users';				
		$session_user = $this->session->userdata('user_username');
		$role_id = $this->session->userdata('role_id');
		$id_user = $this->session->userdata('id_user');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$get_opt_var = array('role_id_kasir','table_available_after_paid','include_tax','include_service,',
		'cashier_max_pembulatan','cashier_pembulatan_keatas','pembulatan_dinamis','wepos_tipe','retail_warehouse');
		$get_opt = get_option_value($get_opt_var);
		
		//IF ONLY ROLE KASIR
		$role_id_kasir = 0;		
		if(!empty($get_opt['role_id_kasir'])){
			//$role_id_kasir = $get_opt['role_id_kasir'];
			
			$role_id_kasir = explode(",", $get_opt['role_id_kasir']);
			
		}	
		
		$wepos_tipe = 'cafe';
		if(!empty($get_opt['wepos_tipe'])){
			$wepos_tipe = $get_opt['wepos_tipe'];
		}
		
		$retail_warehouse = 0;
		//if(!empty($get_opt['retail_warehouse'])){
		//	$retail_warehouse = $get_opt['retail_warehouse'];
		//}
		
		if(empty($id_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}else{
			$this->db->from($this->table_storehouse_users);
			$this->db->where("user_id = ".$id_user." AND is_retail_warehouse = 1");
			$get_retail_warehouse = $this->db->get();
			if($get_retail_warehouse->num_rows() > 0){
				$dt_retail_warehouse = $get_retail_warehouse->row();
				$retail_warehouse = $dt_retail_warehouse->storehouse_id;
			}
		}
		
		if(empty($retail_warehouse) AND $wepos_tipe != 'cafe'){
			$r = array('success' => false, 'info' => 'Please set retail warehouse!');
			echo json_encode($r);
			die();
		}
		
		//Cashier or Superadmin
		if(!empty($role_id_kasir)){
			if(in_array($this->session->userdata('role_id'), $role_id_kasir) OR $role_id == 1){
				
			}else
			{
				$r = array('success' => false, 'info' => 'Only Cashier Can Paid Billing!');
				echo json_encode($r);
				die();
			}
		}else
		{
			$r = array('success' => false, 'info' => 'Only Cashier Can Paid Billing!');
			echo json_encode($r);
			die();
		}
		
		/*if($role_id == $role_id_kasir OR $role_id == 1){
			//accepted
		}else
		{
			$r = array('success' => false, 'info' => 'Only Cashier Can Paid Billing!');
			echo json_encode($r);
			die();
		}*/
		
		$table_id = $this->input->post('table_id');
		$table_no = $this->input->post('table_no');
		$total_guest = $this->input->post('total_guest');
		
		if($wepos_tipe != 'retail'){
			if(empty($table_id) OR empty($table_no)){
				$r = array('success' => false, 'info' => 'Select Table!');
				echo json_encode($r);
				die();
			}
			
			if(empty($total_guest)){
				$r = array('success' => false, 'info' => 'Total Guest Cannot Empty!');
				echo json_encode($r);
				die();
			}
		}
				
		//BILLING
		$billingData = array();
		$get_total = 0;
		$billing_id = $this->input->post('billing_id');
		$billing_no = $this->input->post('billing_no');
		$this->db->select("b.id, b.billing_no, b.billing_status, b.created, b.include_tax, b.include_service, 
		b.discount_perbilling, b.merge_id, b.merge_main_status,
		b.total_billing, b.grand_total");
		$this->db->from($this->table." as b");
		$this->db->where("b.id = ".$billing_id);
		$this->db->where("b.billing_no = '".$billing_no."'");
		//$this->db->where("b.billing_status = 'paid'");
		$get_billing = $this->db->get();
		if($get_billing->num_rows() > 0){
			$billingData = $get_billing->row();
			
			if($billingData->billing_status == 'paid'){
				$r = array('success' => false, 'info' => 'Billing: '.$billingData->billing_no.' Been Paid Before!<br/>Cannot Re-Pay Billing, Please Refresh List Billing'); 
				echo json_encode($r);
				die();
			}
		}else{
			$r = array('success' => false, 'info' => 'Paid Billing #'.$billing_no.' Failed!<br/>Please refresh and try to Pay billing again');
			echo json_encode($r);
			die();
		}
		
		//CLOSING DATE
		$var_closing = array(
			'xdate'	=> $billingData->created,
			'xtipe'	=> 'sales'
		);
		$is_closing = is_closing($var_closing);
		if($is_closing){
			$r = array('success' => false, 'info' => 'Sales Date Been Closed!'); 
			die(json_encode($r));
		}
		
		$total_billing = $this->input->post('total_billing');
		$grand_total = $this->input->post('grand_total');
		$get_total += $total_billing;
		
		$total_paid = $this->input->post('total_paid');
		$total_paid = numberFormat($total_paid);
		
		$total_return = $total_paid-$grand_total;
		//$total_return = $this->input->post('total_return');
		
		$tax_percentage = $this->input->post('tax_percentage');
		$total_ppn = $this->input->post('total_ppn');
		$get_total += $total_ppn;
		
		$service_percentage = $this->input->post('service_percentage');
		$total_service = $this->input->post('total_service');
		$get_total += $total_service;
		
		if(empty($billingData->include_tax) OR empty($billingData->include_service)){
			if(empty($billingData->include_tax)){
				//$get_total += $total_ppn;
			}
			
			if(empty($billingData->include_service)){
				//$get_total += $total_service;
			}
		}
		
		$payment_id = $this->input->post('payment_id');
		$bank_id = $this->input->post('bank_id');
		$card_no = $this->input->post('card_no');
		
		//sales
		$sales_id = $this->input->post('sales_id');
		$sales_percentage = $this->input->post('sales_percentage');
		$sales_price = $this->input->post('sales_price');
		$sales_type = $this->input->post('sales_type');
		
		//customer
		$customer_id = $this->input->post('customer_id');
		
		$discount_id = $this->input->post('discount_id');
		$discount_notes = $this->input->post('discount_notes');		
		$discount_percentage = $this->input->post('discount_percentage');
		$discount_price = $this->input->post('discount_price');
		$total_discount = $this->input->post('total_discount');
		$get_total -= $total_discount;
		
		$discount_total = $total_discount;
		
		$total_dp = $this->input->post('total_dp');
		$get_total -= $total_dp;
		
		
		$compliment_total = $this->input->post('compliment_total');
		$get_total -= $compliment_total;
		
		$billing_notes = $this->input->post('billing_notes');
		
		$total_pembulatan = $this->input->post('total_pembulatan');	
		
		if($get_total <= 0){
			$get_total = 0;
			$total_pembulatan = 0;
		}
		
		if(!empty($total_pembulatan)){
			$get_total += $total_pembulatan;
		}	
	
		//$grand_total = $this->input->post('grand_total');
		$single_rate = $this->input->post('single_rate');
		$is_compliment = $this->input->post('is_compliment');
		$is_half_payment = $this->input->post('is_half_payment');
		$total_cash = $this->input->post('total_cash');
		$total_credit = $this->input->post('total_credit');
		
		if(!empty($is_half_payment)){
			//paid is same as total billing
			$total_paid = $get_total;
		}else{
			$is_half_payment = 0;
			if($payment_id == 1){
				//CASH
				$total_cash = $get_total;
			}else{
				$total_credit = $get_total;	
			}	
			
		}		

		if(empty($total_paid) AND $discount_percentage < 100){
			//$r = array('success' => false, 'info' => 'Total Paid Cannot Empty!');
			//echo json_encode($r);
			//die();
		}
		
		
		if($billingData->grand_total != $grand_total){
			$r = array('success' => false, 'info' => 'Grand Total doesn\'t match with data<br/>Please refresh order data'); 
			die(json_encode($r));
		}
		if($billingData->total_billing != $total_billing){
			$r = array('success' => false, 'info' => 'Total Billing doesn\'t match with data<br/>Please refresh order data'); 
			die(json_encode($r));
		}
		
		if(empty($total_paid)){
			if($total_paid == $grand_total){
				
			}else{
				if(empty($total_paid) AND $discount_percentage < 100){
					$r = array('success' => false, 'info' => 'Total Paid Cannot Empty!');
					echo json_encode($r);
					die();
				}
				
				if($total_paid != $grand_total){
					$r = array('success' => false, 'info' => 'Grand Total doesn\'t match with Total Paid<br/>Please refresh order data'); 
					die(json_encode($r));
				}
			}
		}
		
		if(empty($single_rate)){
			$single_rate = 0;
		}
		
		if(empty($is_compliment)){
			$is_compliment = 0;
		}
			
		if(empty($billing_id)){
			$r = array('success' => false, 'info' => 'Billing not Found!');
			echo json_encode($r);
			die();
		}
			
		if(empty($billing_id)){
			$r = array('success' => false, 'info' => 'Billing not Found!');
			echo json_encode($r);
			die();
		}
		
		//calc detail
		$total_tax = 0;
		$total_service = 0;
		$total_discount = 0;
		$total_hpp = 0;
		$this->db->select("order_qty, retur_qty, product_price_hpp, tax_total, service_total, discount_total");
		$this->db->from($this->table2);
		$this->db->where('billing_id', $billing_id);
		$this->db->where('is_deleted', 0);
		$get_detail = $this->db->get();
		if($get_detail->num_rows() > 0){
			foreach($get_detail->result() as $dtRow){
				$total_qty = $dtRow->order_qty - $dtRow->retur_qty;
				if($total_qty < 0){
					$total_qty = 0;
				}
				
				$total_hpp += $dtRow->product_price_hpp * $total_qty;
				$total_tax += $dtRow->tax_total;
				$total_service += $dtRow->service_total;
				$total_discount += $dtRow->discount_total;
		
			}
		}
		
		if($billingData->discount_perbilling == 1){
			$total_discount = $discount_total;
		}
		
		$date_now = date('Y-m-d H:i:s');
			
		$r = '';
		$var = array('fields'	=>	array(
				'table_id'		=>	$table_id,
				'table_no'		=>	$table_no,
				'total_guest'		=>	$total_guest,
				//'total_billing'	=>	$total_billing,
				'total_paid'	=>	$total_paid,
				'total_pembulatan'	=>	$total_pembulatan,
				'total_hpp'		=>	$total_hpp,
				'billing_notes'	=>	$billing_notes,
				'payment_id'	=>	$payment_id,
				'bank_id'		=>	$bank_id,
				'card_no'		=>	$card_no,
				'customer_id'	=>	$customer_id,
				'sales_id'		=>	$sales_id,
				'sales_percentage'	=>	$sales_percentage,
				'sales_price'		=>	$sales_price,
				'sales_type'		=>	$sales_type,
				//'tax_percentage'	=>	$tax_percentage,
				//'tax_total'		=>	$total_tax,
				//'service_percentage'	=>	$service_percentage,
				//'service_total'		=>	$total_service,
				///'discount_id'	=>	$discount_id,
				///'discount_notes'	=>	$discount_notes,
				///'discount_percentage'	=>	$discount_percentage,
				///'discount_price'	=>	$discount_price,
				//'discount_total'	=>	$total_discount,
				'billing_status'	=>	'paid',
				'payment_date'		=>	$date_now,
				'single_rate'		=>	$single_rate,
				'is_compliment'		=>	$is_compliment,
				'is_half_payment'	=>	$is_half_payment,
				'total_cash'		=>	$total_cash,
				'total_credit'		=>	$total_credit,
				'grand_total'		=>	$grand_total,
				'total_return'		=>	$total_return,
				'updated'		=>	$date_now,
				'updatedby'		=>	$session_user
			),
			'table'			=>  $this->table,
			'primary_key'	=>  'id'
		);
		
		if(!empty($retail_warehouse)){
			$var['fields']['storehouse_id'] = $retail_warehouse;
		}
		//echo '<pre>';
		//print_r($var['fields']);
		//die();	
		
		//UPDATE
		$id = $this->input->post('id', true);
		$this->lib_trans->begin();
			$update = $this->m->save($var, $billing_id);
		$this->lib_trans->commit();
		
		if($update)
		{  
			$r = array('success' => true, 'id' => $billing_id);
			
			$all_table_id = array();
			$all_table_id[] = $table_id;
			
			//if merge bill save other status as paid
			if(!empty($billingData->merge_id) AND !empty($billingData->merge_main_status)){
				
				
				$data_merge = array(
					//'billing_status' => 'paid'
					'billing_status' => 'cancel'
				);
						
				//UPDATE BILLING
				$this->db->update($this->table, $data_merge, "merge_id = ".$billingData->merge_id." AND id != ".$billing_id);
				
				if($wepos_tipe != 'retail'){
					//get all table
					$this->db->select("table_id");
					$this->db->from($this->table);
					$this->db->where("merge_id = ".$billingData->merge_id);
					$get_merge_table = $this->db->get();
					if($get_merge_table->num_rows() > 0){
						foreach($get_merge_table->result() as $dt){
							if(!empty($dt->table_id)){
								if(!in_array($dt->table_id, $all_table_id)){
									$all_table_id[] = $dt->table_id;
								}
							}
						}
					}
				}
				
				
			}
			
			//$get_opt = get_option_value(array('table_available_after_paid'));
			
			if(!empty($get_opt['table_available_after_paid']) AND $wepos_tipe != 'retail'){
				
				$dt_update = array(
					'status'	=> 'available',
					'billing_no'	=> ''
				);
				
				$all_table_id_txt = implode(",", $all_table_id);
				
				$data_now = date("Y-m-d");
				$update = $this->db->update($this->table_inv, $dt_update, "tanggal = '".$data_now."' AND table_id IN (".$all_table_id_txt.")");
			}
			
			if($wepos_tipe == 'retail' AND !empty($retail_warehouse)){
				
				//$getItemData = $this->m2->getItem($billing_id, $retail_warehouse);
				//$getItemData['tipe'] = 'edit';
				//$getStock = $this->stock->get_item_stock($getItemData, date("Y-m-d"));
				//$validStock = $this->stock->validStock($getItemData, $getStock);
				
				$r['info'] = 'Update Stok';
				$update_stok = 'update';
				
				$return_data = $this->m2->billingDetail($billing_id, $retail_warehouse, $update_stok);
				$r['update_stock'] = $return_data['update_stock'];
				
				$post_params = array(
					'storehouse_item'	=> $return_data['update_stock']
				);
				
				$updateStock = $this->stock->update_stock_rekap($post_params);
				
				
			}else{
				//FORCE UPDATE STOK -> GRAMASI
			}
			
			//SAVE TO LOG
			$this->logBilling($billingData, 'Paid', 'Paid Billing '.$billingData->billing_no);
		}  
		else
		{  
			$r = array('success' => false);
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function doPrint($is_void = '', $void_id = 0, $order_detail_id = ''){
		header('Content-Type: text/plain; charset=utf-8');
		
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';
		$this->table_print_monitoring = $this->prefix.'print_monitoring';
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$tipe = $this->input->get_post('tipe', true);	
		$id = $this->input->get_post('id', true);	
		$is_html = $this->input->get_post('is_html', true);	
		$print_type = $this->input->get_post('print_type', true);	
		$printer_id = $this->input->get_post('printer_id', true);
		
		$printer_tipe = $this->input->get_post('printer_tipe', true);	
		$do_print = $this->input->get_post('do_print', true);	
		
		if(empty($print_type)){
			$print_type = 0;
		}
		
		$r = array('success' => false);
		
		$opt_value = array(
			'use_pembulatan',
			'cashier_max_pembulatan',
			'cashier_pembulatan_keatas',
			'pembulatan_dinamis',

			'cashierReceipt_layout',
			'cashierReceipt_invoice_layout',
			'cashierReceipt_layout_footer',
			'printer_ip_cashierReceipt_default',
			'printer_pin_cashierReceipt_default',
			'printer_tipe_cashierReceipt_default',
			'printer_ip_cashierReceipt_'.$ip_addr,
			'printer_pin_cashierReceipt_'.$ip_addr,
			'printer_tipe_cashierReceipt_'.$ip_addr,
			//'printer_type_cashier',
			
			'qcReceipt_layout',
			'printer_ip_qcReceipt_default',
			'printer_pin_qcReceipt_default',
			'printer_tipe_qcReceipt_default',
			'do_print_qcReceipt_'.$ip_addr,
			'printer_ip_qcReceipt_'.$ip_addr,
			'printer_pin_qcReceipt_'.$ip_addr,
			'printer_tipe_qcReceipt_'.$ip_addr,
			//'printer_type_qc',
			
			'kitchenReceipt_layout',
			'printer_ip_kitchenReceipt_default',
			'printer_pin_kitchenReceipt_default',
			'printer_tipe_kitchenReceipt_default',
			'do_print_kitchenReceipt_'.$ip_addr,
			'printer_ip_kitchenReceipt_'.$ip_addr,
			'printer_pin_kitchenReceipt_'.$ip_addr,
			'printer_tipe_kitchenReceipt_'.$ip_addr,
			//'printer_type_kitchen',
			
			'barReceipt_layout',
			'printer_ip_barReceipt_default',
			'printer_pin_barReceipt_default',
			'printer_tipe_barReceipt_default',
			'do_print_barReceipt_'.$ip_addr,
			'printer_ip_barReceipt_'.$ip_addr,
			'printer_pin_barReceipt_'.$ip_addr,
			'printer_tipe_barReceipt_'.$ip_addr,
			//'printer_type_bar',
			
			'otherReceipt_layout',
			'printer_ip_otherReceipt_default',
			'printer_pin_otherReceipt_default',
			'printer_tipe_otherReceipt_default',
			'do_print_otherReceipt_'.$ip_addr,
			'printer_ip_otherReceipt_'.$ip_addr,
			'printer_pin_otherReceipt_'.$ip_addr,
			'printer_tipe_otherReceipt_'.$ip_addr,
			//'printer_type_other',
			
			'print_order_peritem_kitchen',
			'print_order_peritem_bar',
			'print_order_peritem_other',
			
			'printMonitoring_qc',
			'printMonitoring_kitchen',
			'printMonitoring_bar',
			'printMonitoring_other'
			
		);
		$get_opt = get_option_value($opt_value);
		
		//Cashier Printer ----------------------
		$printer_ip_cashierReceipt = "\\\\".$ip_addr."\\".$get_opt['printer_ip_cashierReceipt_default'];
		if(!empty($get_opt['printer_ip_cashierReceipt_'.$ip_addr])){
			$printer_ip_cashierReceipt = $get_opt['printer_ip_cashierReceipt_'.$ip_addr];			
			if(strstr($printer_ip_cashierReceipt, '\\')){
				$printer_ip_cashierReceipt = "\\\\".$printer_ip_cashierReceipt;
			}			
		}		
		
		$cashierReceipt_layout = $get_opt['cashierReceipt_layout'];
		if(!empty($print_type)){
			$cashierReceipt_layout = $get_opt['cashierReceipt_invoice_layout'];
		}
		$cashierReceipt_layout_footer = $get_opt['cashierReceipt_layout_footer'];
		//---------------------- Cashier Printer
		
		//QC PRINTER ----------------------
		if(!empty($get_opt['do_print_qcReceipt_'.$ip_addr])){
			$print_qcReceipt = $get_opt['do_print_qcReceipt_'.$ip_addr];
		}else{
			$print_qcReceipt = '';
		}
		
		$printer_ip_qcReceipt = "\\\\".$ip_addr."\\".$get_opt['printer_ip_qcReceipt_default'];
		if(!empty($get_opt['printer_ip_qcReceipt_'.$ip_addr])){
			$printer_ip_qcReceipt = $get_opt['printer_ip_qcReceipt_'.$ip_addr];			
			if(strstr($printer_ip_qcReceipt, '\\')){
				$printer_ip_qcReceipt = "\\\\".$printer_ip_qcReceipt;
			}			
		}		
		
		$qcReceipt_layout = $get_opt['qcReceipt_layout'];
		//---------------------- QC PRINTER
		
		//Kitchen PRINTER ----------------------
		if(!empty($get_opt['do_print_kitchenReceipt_'.$ip_addr])){
			$print_kitchenReceipt = $get_opt['do_print_kitchenReceipt_'.$ip_addr];
		}else{
			$print_kitchenReceipt = '';
		}
		$printer_ip_kitchenReceipt = "\\\\".$ip_addr."\\".$get_opt['printer_ip_kitchenReceipt_default'];
		if(!empty($get_opt['printer_ip_kitchenReceipt_'.$ip_addr])){
			$printer_ip_kitchenReceipt = $get_opt['printer_ip_kitchenReceipt_'.$ip_addr];			
			if(strstr($printer_ip_kitchenReceipt, '\\')){
				$printer_ip_kitchenReceipt = "\\\\".$printer_ip_kitchenReceipt;
			}			
		}		
		
		$kitchenReceipt_layout = $get_opt['kitchenReceipt_layout'];
		//---------------------- Kitchen PRINTER
		
		//Bar PRINTER ----------------------
		if(!empty($get_opt['do_print_barReceipt_'.$ip_addr])){
			$print_barReceipt = $get_opt['do_print_barReceipt_'.$ip_addr];
		}else{
			$print_barReceipt = '';
		}
		$printer_ip_barReceipt = "\\\\".$ip_addr."\\".$get_opt['printer_ip_barReceipt_default'];
		if(!empty($get_opt['printer_ip_barReceipt_'.$ip_addr])){
			$printer_ip_barReceipt = $get_opt['printer_ip_barReceipt_'.$ip_addr];			
			if(strstr($printer_ip_barReceipt, '\\')){
				$printer_ip_barReceipt = "\\\\".$printer_ip_barReceipt;
			}			
		}		
		
		$barReceipt_layout = $get_opt['barReceipt_layout'];
		//---------------------- Bar PRINTER
		
		//other PRINTER ----------------------
		if(!empty($get_opt['do_print_otherReceipt_'.$ip_addr])){
			$print_otherReceipt = $get_opt['do_print_otherReceipt_'.$ip_addr];
		}else{
			$print_otherReceipt = '';
		}
		$printer_ip_otherReceipt = "\\\\".$ip_addr."\\".$get_opt['printer_ip_otherReceipt_default'];
		if(!empty($get_opt['printer_ip_otherReceipt_'.$ip_addr])){
			$printer_ip_otherReceipt = $get_opt['printer_ip_otherReceipt_'.$ip_addr];			
			if(strstr($printer_ip_otherReceipt, '\\')){
				$printer_ip_otherReceipt = "\\\\".$printer_ip_otherReceipt;
			}			
		}		
		
		$otherReceipt_layout = $get_opt['otherReceipt_layout'];
		//---------------------- Bar PRINTER
		
		/*CONFIG TEXT PRINT*/
		$printer_pin_cashierReceipt = '42 CHAR';
		if(!empty($get_opt['printer_pin_cashierReceipt_'.$ip_addr])){
			$printer_pin_cashierReceipt = $get_opt['printer_pin_cashierReceipt_'.$ip_addr];
		}
		
		$printer_pin_qcReceipt = '42 CHAR';
		if(!empty($get_opt['printer_pin_qcReceipt_'.$ip_addr])){
			$printer_pin_qcReceipt = $get_opt['printer_pin_qcReceipt_'.$ip_addr];
		}
		
		$printer_pin_kitchenReceipt = '42 CHAR';
		if(!empty($get_opt['printer_pin_kitchenReceipt_'.$ip_addr])){
			$printer_pin_kitchenReceipt = $get_opt['printer_pin_kitchenReceipt_'.$ip_addr];
		}
		
		$printer_pin_barReceipt = '42 CHAR';
		if(!empty($get_opt['printer_pin_barReceipt_'.$ip_addr])){
			$printer_pin_barReceipt = $get_opt['printer_pin_barReceipt_'.$ip_addr];
		}
		
		$printer_pin_otherReceipt = '42 CHAR';
		if(!empty($get_opt['printer_pin_otherReceipt_'.$ip_addr])){
			$printer_pin_otherReceipt = $get_opt['printer_pin_otherReceipt_'.$ip_addr];
		}
		
		//printMonitoring
		$printMonitoring_qc = 0;
		if(!empty($get_opt['printMonitoring_qc'])){
			$printMonitoring_qc = $get_opt['printMonitoring_qc'];
		}
		$printMonitoring_kitchen = 0;
		if(!empty($get_opt['printMonitoring_kitchen'])){
			$printMonitoring_kitchen = $get_opt['printMonitoring_kitchen'];
		}
		$printMonitoring_bar = 0;
		if(!empty($get_opt['printMonitoring_bar'])){
			$printMonitoring_bar = $get_opt['printMonitoring_bar'];
		}
		$printMonitoring_other = 0;
		if(!empty($get_opt['printMonitoring_other'])){
			$printMonitoring_other = $get_opt['printMonitoring_other'];
		}
		
		//PRINTE ANYWHERE
		$print_anywhere = array();
		if(!empty($printer_id)){
			
			$this->db->from($this->prefix.'printer');
			$this->db->where('id', $printer_id);
			$getPrinter = $this->db->get();
			if($getPrinter->num_rows() > 0){
				$print_anywhere = $getPrinter->row();
			}
			
		}
		
		if(!empty($print_anywhere)){
			
			if(strstr($print_anywhere->printer_ip, '\\')){
				$print_anywhere->printer_ip = "\\\\".$print_anywhere->printer_ip;
			}
			
			$printer_ip_cashierReceipt = $print_anywhere->printer_ip;
			$printer_ip_qcReceipt = $print_anywhere->printer_ip;
			$printer_ip_kitchenReceipt = $print_anywhere->printer_ip;
			$printer_ip_barReceipt = $print_anywhere->printer_ip;
			$printer_ip_otherReceipt = $print_anywhere->printer_ip;
			
			$printer_pin_cashierReceipt = $print_anywhere->printer_pin;
			$printer_pin_qcReceipt = $print_anywhere->printer_pin;
			$printer_pin_kitchenReceipt = $print_anywhere->printer_pin;
			$printer_pin_barReceipt = $print_anywhere->printer_pin;
			$printer_pin_otherReceipt = $print_anywhere->printer_pin;
		}
		
		//die($printer_ip_qcReceipt);
		
		if(($tipe == 'payBilling' AND !empty($id)) OR (!empty($is_void) AND !empty($void_id))){
		
			if(!empty($void_id)){
				$id = $void_id;
			}
			
			if($is_void == 'void_paid_hold' OR $is_void == 'void_paid_cancel'){
				$print_type = 99;
			}
			
			$is_void_order = false; 
			if($is_void == 'void_order'){
				$print_type = -234;
				$is_void_order = true;
			}
			
			$billingData = $this->getBilling($id);
			if(!empty($billingData)){
				
				$is_print_error = false;
				
				$this->db->select("a.*, d.table_no, a2.billing_no, a2.discount_perbilling,
								b.product_name, b.product_chinese_name, b.product_desc, b.product_type, b.product_image, 
								b.category_id, b.product_group, c.product_category_name,
								e.varian_name
								");
				$this->db->from($this->table2.' as a');
				$this->db->join($this->prefix.'billing as a2','a2.id = a.billing_id','LEFT');
				$this->db->join($this->prefix.'product as b','b.id = a.product_id','LEFT');
				$this->db->join($this->prefix.'product_category as c','c.id = b.category_id','LEFT');
				$this->db->join($this->prefix.'table as d','d.id = a2.table_id','LEFT');
				$this->db->join($this->prefix.'varian as e','e.id = a.varian_id','LEFT');
				//$this->db->where('a.is_deleted', 0); -- view all cancel order
				
				if($print_type == 1 OR $print_type == 0 OR $print_type == 99){
					$this->db->where('a.is_deleted', 0);
				}
				$this->db->where("a.billing_id = ".$id);
				
				if(!empty($order_detail_id)){
					$this->db->where("a.id IN (".$order_detail_id.")");
				}
				
				
				$get_detail = $this->db->get();
		
				$order_data = "";	
				$order_data2 = "";	
				$order_data_kitchen = array();	
				$order_data_bar = array();
				$order_data_other = array();
				
				$order_data_kitchen_peritem = array();
				$order_data_bar_peritem = array();
				$order_data_other_peritem = array();
				
				$order_data_kitchen_update = array();	
				$order_data_bar_update = array();	
				$order_data_other_update = array();	
				
				$subtotal = 0;
				$tax_total = 0;
				$service_total = 0;
				$discount_total = 0;
				$total = 0;
				
				$order_qc_id = array();
				
				if($get_detail->num_rows() > 0){
	
					//echo '<pre>';
					//print_r($get_detail->result());
					//die();
					
					$no = 1;
					foreach($get_detail->result() as $bil_det){

						$allow_QC = false;
						
						//SET ORDER DONE
						if(!in_array($bil_det->order_status, array('done','cancel'))){
							
							if($bil_det->product_group == 'food'){
								if(!in_array($bil_det->id, $order_data_kitchen_update)){
									$order_data_kitchen_update[] = $bil_det->id;
								}
							}else
							if($bil_det->product_group == 'beverage'){
								if(!in_array($bil_det->id, $order_data_bar_update)){
									$order_data_bar_update[] = $bil_det->id;
								}
							}else{
								if(!in_array($bil_det->id, $order_data_other_update)){
									$order_data_other_update[] = $bil_det->id;
								}
							}
							
							//if($bil_det->print_qc == 0){
							$order_qc_id[] = $bil_det->id;
							$allow_QC = true;
							//}
							
							
						}else{
							
							//DONE
							if($bil_det->print_qc == 0){
								
								if($bil_det->order_status == 'done'){
									$order_qc_id[] = $bil_det->id;
									$allow_QC = true;
								}else{
									//cancel other
									if($bil_det->cancel_order_notes != 'cancel order - unpaid' AND $is_void_order == true){
										$order_qc_id[] = $bil_det->id;
										$allow_QC = true;
									}
								}
								
							}
							
							
							
						}
						
						$order_notes = '';
						if(!empty($bil_det->order_notes)){
							$order_notes = " (".$bil_det->order_notes.")";
						}
						
						//varian
						$varian_name = '';
						$varian_name_2 = '';
						if(!empty($bil_det->varian_name)){
							$varian_name = " (".$bil_det->varian_name.")";
							$varian_name_2 = $bil_det->varian_name;
						}
						
						//product_chinese_name
						$product_chinese_name = '';
						if(!empty($bil_det->product_chinese_name) AND $bil_det->product_chinese_name != '-'){
							//$product_chinese_name = " / ".$bil_det->product_chinese_name."";
						}
						
						$diskon_name = '';
						if(!empty($bil_det->discount_id) AND $bil_det->discount_perbilling == 0){
							if(!empty($bil_det->discount_percentage)){
								//DISCOUNT %
								$diskon_name = '    disc '.priceFormat($bil_det->discount_percentage, 2, ".", "").'%';
								
								if($bil_det->free_item == 1){
									$diskon_name = '    FREE';
								}
								
							}else{
								if(!empty($bil_det->discount_price)){
									//DISCOUNT PRICE
									$diskon_name = '    disc '.priceFormat($bil_det->discount_price);
								}
							}
						}
						
						//Promo
						if(!empty($bil_det->promo_id) AND $bil_det->discount_perbilling == 0){
							if(!empty($bil_det->promo_percentage)){
								//promo %
								$diskon_name = '    promo '.priceFormat($bil_det->promo_percentage, 2, ".", "").'%';
								$diskon_name .= ', @'.priceFormat($bil_det->promo_price);
							}else{
								if(!empty($bil_det->promo_price)){
									//promo PRICE
									$diskon_name = '    promo '.priceFormat($bil_det->promo_price*$bil_det->order_qty);
								}
							}
						}
						
						$takeaway_name = '';
						if(!empty($bil_det->is_takeaway)){
							$takeaway_name = " T/A";
						}
						
						$compliment_name = '';
						if(!empty($bil_det->is_compliment)){
							$compliment_name = " - COMPLIMENT";
						}
						
						//PROMO
						$promo_name = '';
						if($bil_det->is_promo == 1 AND !empty($bil_det->promo_id)){
							$promo_name = ' Promo';
							$bil_det->product_price = $bil_det->product_price;
							$bil_det->discount_price = $bil_det->promo_price;
							$bil_det->discount_total = $bil_det->promo_price*$bil_det->order_qty;
						}
						
						$order_total = $bil_det->order_qty * $bil_det->product_price;
						
						//trim prod name
						$max_text = 18;
						
						if($printer_pin_cashierReceipt == '32 CHAR'){
							$max_text -= 7;
						}
						if($printer_pin_cashierReceipt == '40 CHAR'){
							$max_text -= 2;
						}
						if($printer_pin_cashierReceipt == '48 CHAR'){
							$max_text += 6;
						}
						
						$all_text_array = array();
						$product_name = $bil_det->product_name.$promo_name.$product_chinese_name.$varian_name.$diskon_name.$takeaway_name.$compliment_name;

						
						
						//echo $product_name.' = '.strlen($product_name).'<br/>';
						
						if(strlen($product_name) > $max_text){
							//skip on last space
							$explTxt = explode(" ",$product_name);
							
							$no_exp = 1;
							$tot_txt = 0;
							$text_display = '';
							foreach($explTxt as $txt){
								$lnTxt = strlen($txt);
								$tot_txt += $lnTxt;
								
								if($tot_txt > 0){
									$tot_txt+=1; //space
								}
								
								if($tot_txt > $max_text){
									$all_text_array[] = $text_display;
									$tot_txt = 0;
									$text_display = $txt;
									
									//echo '2. '.$text_display.' '.$tot_txt.'<br/>';
									
								}else{
								
									if(empty($text_display)){
										$text_display = $txt;
									}else{
										$text_display .= ' '.$txt;										
									}
									
									//echo '1. '.$text_display.' '.$tot_txt.'<br/>';
									
								}
								
								if(count($explTxt) == $no_exp){
									$all_text_array[] = $text_display;
								}
								
								$no_exp++;
							}
							
							if(empty($all_text_array[0])){
								$product_name = substr($product_name, 0, $max_text);
							}else{
								$product_name = $all_text_array[0];
							}
						}
												
						$product_price_show = printer_command_align_right('@'.$bil_det->product_price, 7);
						//$product_price_show = printer_command_align_right('@'.priceFormat($bil_det->product_price), 7);
						//$order_total_show = printer_command_align_right(priceFormat($order_total), 10);
						$order_total_show = printer_command_align_right($order_total, 9);
						
						if($printer_pin_cashierReceipt == '32 CHAR'){
							$product_price_show = printer_command_align_right('@'.$bil_det->product_price, 6);
							$order_total_show = printer_command_align_right($order_total, 8);
						}
						
						$order_data .= "[align=0]".$bil_det->order_qty."[tab]".$product_name."[tab]".$product_price_show."[tab]".$order_total_show;
						$order_data2 .= "[align=0]".$bil_det->order_qty."[tab]".$product_name."[tab] [tab]".$order_total_show;

						


						
						//not substr $bil_det->product_name for kitchen and bar
						if($bil_det->product_group == 'food'){
							
							//khusus cancel order
							if($is_void_order){
								if(empty($order_data_kitchen[$bil_det->id])){
									$order_data_kitchen[$bil_det->id] = '';
									$order_data_kitchen[$bil_det->id] .= "[size=2]CANCEL ORDER[tab] \n[size=0]";
								}
								$order_data_kitchen[$bil_det->id] .= $bil_det->order_qty."[tab]".$bil_det->product_name.$product_chinese_name.$varian_name.$takeaway_name.$order_notes."\n";
								
								//PER-ITEM KITCHEN
								$order_data_kitchen_peritem_format = "[size=2][align=1]CANCEL ORDER\n";
								$order_data_kitchen_peritem_format .= "[size=2][align=1]".$bil_det->product_name.$product_chinese_name."\n";
								$order_data_kitchen_peritem_format .= "".$bil_det->order_qty." X ".$varian_name_2."\n";
								$order_data_kitchen_peritem_format .= "[size=1][align=1]".$takeaway_name.$order_notes."\n";
								$order_data_kitchen_peritem[$bil_det->id] = $order_data_kitchen_peritem_format;
								$order_data_kitchen_update[] = $bil_det->id;
								
							}else{
								if((!empty($order_data_kitchen_update) AND in_array($bil_det->id, $order_data_kitchen_update)) OR $allow_QC == true){
									
									//if(empty($order_data_kitchen)){
									//	$order_data_kitchen .= "KITCHEN[tab] \n";
									//}
									$order_data_kitchen[$bil_det->id] = $bil_det->order_qty."[tab]".$bil_det->product_name.$product_chinese_name.$varian_name.$takeaway_name.$order_notes."\n";
									
									//PER-ITEM KITCHEN
									$order_data_kitchen_peritem_format = "[size=2][align=1]".$bil_det->product_name.$product_chinese_name."\n";
									$order_data_kitchen_peritem_format .= "".$bil_det->order_qty." X ".$varian_name_2."\n";
									$order_data_kitchen_peritem_format .= "[size=1][align=1]".$takeaway_name.$order_notes."\n";
									$order_data_kitchen_peritem[$bil_det->id] = $order_data_kitchen_peritem_format;
									
								}
							}
							
							
						}else
						if($bil_det->product_group == 'beverage'){
							
							//khusus cancel order
							if($is_void_order){
								if(empty($order_data_bar[$bil_det->id])){
									$order_data_bar[$bil_det->id] = '';
									$order_data_bar[$bil_det->id] .= "[size=2]CANCEL ORDER[tab] \n[size=0]";
								}
								$order_data_bar[$bil_det->id] .= $bil_det->order_qty."[tab]".$bil_det->product_name.$product_chinese_name.$varian_name.$takeaway_name.$order_notes."\n";
								
								//PER-ITEM BAR
								$order_data_bar_peritem_format = "[size=2][align=1]CANCEL ORDER\n";
								$order_data_bar_peritem_format .= "[size=2][align=1]".$bil_det->product_name.$product_chinese_name."\n";
								$order_data_bar_peritem_format .= "".$bil_det->order_qty." X ".$varian_name_2."\n";
								$order_data_bar_peritem_format .= "[size=1][align=1]".$takeaway_name.$order_notes."\n";
								$order_data_bar_peritem[$bil_det->id] = $order_data_bar_peritem_format;
								$order_data_bar_update[] = $bil_det->id;
								
							}else{
								if((!empty($order_data_bar_update) AND in_array($bil_det->id, $order_data_bar_update)) OR $allow_QC == true){
									
									//if(empty($order_data_bar)){
									//	$order_data_bar .= "BAR[tab] \n";
									//}
									$order_data_bar[$bil_det->id] = $bil_det->order_qty."[tab]".$bil_det->product_name.$product_chinese_name.$varian_name.$takeaway_name.$order_notes."\n";
									
									//PER-ITEM BAR
									$order_data_bar_peritem_format = "[size=2][align=1]".$bil_det->product_name.$product_chinese_name."\n";
									$order_data_bar_peritem_format .= "".$bil_det->order_qty." X ".$varian_name_2."\n";
									$order_data_bar_peritem_format .= "[size=1][align=1]".$takeaway_name.$order_notes."\n";
									$order_data_bar_peritem[$bil_det->id] = $order_data_bar_peritem_format;
									
								}
							}
							
							
						}else{
							
							if($is_void_order){
								if(empty($order_data_other[$bil_det->id])){
									$order_data_other[$bil_det->id] = '';
									$order_data_other[$bil_det->id] .= "[size=2]CANCEL ORDER[tab] \n[size=0]";
								}
								$order_data_other[$bil_det->id] .= $bil_det->order_qty."[tab]".$bil_det->product_name.$product_chinese_name.$varian_name.$takeaway_name.$order_notes."\n";
								
								//PER-ITEM OTHER
								$order_data_other_peritem_format = "[size=2][align=1]CANCEL ORDER\n";
								$order_data_other_peritem_format .= "[size=2][align=1]".$bil_det->product_name.$product_chinese_name."\n";
								$order_data_other_peritem_format .= "".$bil_det->order_qty." X ".$varian_name_2."\n";
								$order_data_other_peritem_format .= "[size=1][align=1]".$takeaway_name.$order_notes."\n";
								$order_data_other_peritem[$bil_det->id] = $order_data_other_peritem_format;
								
							}else{
								if((!empty($order_data_other_update) AND in_array($bil_det->id, $order_data_other_update)) OR $allow_QC == true){
									
									//if(empty($order_data_other)){
									//	$order_data_other .= "OTHER[tab] \n";
									//}
									$order_data_other[$bil_det->id] = $bil_det->order_qty."[tab]".$bil_det->product_name.$product_chinese_name.$varian_name.$takeaway_name.$order_notes."\n";
									
									//PER-ITEM OTHER
									$order_data_other_peritem_format = "[size=2][align=1]".$bil_det->product_name.$product_chinese_name."\n";
									$order_data_other_peritem_format .= "".$bil_det->order_qty." X ".$varian_name_2."\n";
									$order_data_other_peritem_format .= "[size=1][align=1]".$takeaway_name.$order_notes."\n";
									$order_data_other_peritem[$bil_det->id] = $order_data_other_peritem_format;
									
								}
							}
							
						}
						
						//echo '<pre>';
						//print_r($all_text_array);
						
						//other text - continue 
						foreach($all_text_array as $no_dt => $product_name_extend){
						
							if($no_dt > 0){
								$order_data .= "\n"; 
								$order_data .= "[align=0][tab]".$product_name_extend."[tab] [tab]";
								
								$order_data2 .= "\n"; 
								$order_data2 .= "[align=0][tab]".$product_name_extend."[tab] [tab]";
								
								if($bil_det->product_group == 'beverage'){
									//$order_data_bar .= "[tab]".$product_name_extend."\n";
								}else{
									//$order_data_kitchen .= "[tab]".$product_name_extend."\n";
								}
							}
							
						}
						
						//NEW DISC
						if(!empty($diskon_name)){
							
							if($bil_det->free_item == 1){
								$bil_det->discount_total = $order_total;
							}
							
							$discount_total_print = printer_command_align_right($bil_det->discount_total, 9);
						
							if($printer_pin_cashierReceipt == '32 CHAR'){
								$discount_total_print = printer_command_align_right($bil_det->discount_total, 8);
							}

							if($bil_det->is_promo == 1 AND !empty($bil_det->promo_id)){
								
								$order_data .= "\n"."[align=0] [tab]".$diskon_name."[tab] [tab]".$discount_total_print*-1;
								$order_data2 .= "\n"."[align=0] [tab]".$diskon_name."[tab] [tab]".$discount_total_print*-1;

							}else{
								
								$order_data .= "\n"."[align=0] [tab]".$diskon_name."[tab] [tab]".$discount_total_print*-1;
								$order_data2 .= "\n"."[align=0] [tab]".$diskon_name."[tab] [tab]".$discount_total_print*-1;
								
							}
						}
						
						//echo '<pre>';
						//print_r($all_text_array);
				
						if($no < $get_detail->num_rows()){
							$order_data .= "\n";
							$order_data2 .= "\n";
						}
						
						$subtotal += $order_total;
						$tax_total += $bil_det->tax_total;
						$service_total += $bil_det->service_total;
						$discount_total += $bil_det->discount_total;
						//$total += $subtotal;
						$no++;
					}				
				}
				
				
				$total = $subtotal + $tax_total + $service_total;
				if(!empty($billingData->include_tax) OR !empty($billingData->include_service)){
					$total = $subtotal;
				}
				
				if($billingData->discount_perbilling == 1){
					$discount_total = $billingData->discount_total;
				}
				
				$total = $total - $discount_total;
				
				$total_dp = 0;
				if(!empty($billingData->total_dp)){
					$total_dp = $billingData->total_dp;
				}
				$total = $total - $total_dp;
				
				if($total <= 0){
					$total = 0;
				}
				
				//PEMBULATAN				
				$total_pembulatan = 0;
				$max_pembulatan = $get_opt['cashier_max_pembulatan'];
				$pembulatan_keatas = $get_opt['cashier_pembulatan_keatas'];
				$pembulatan_dinamis = $get_opt['pembulatan_dinamis'];
				$last2digit = substr($total,-2);
				$last2digit = intval($last2digit);
				
				//dibawah max pembulatan
				if($last2digit > 0){
					if(empty($pembulatan_keatas)){
						
						//$total_pembulatan = $last2digit;
						$total_pembulatan = $last2digit*-1;
						
						if(!empty($pembulatan_dinamis)){
							if($last2digit <= 50){
								$total_pembulatan = $last2digit*-1;
							}else{
								$total_pembulatan = $max_pembulatan - $last2digit;
							}
						}
						
					}else{
						
						$total_pembulatan = $max_pembulatan - $last2digit;
						
					}
				}
				
				if($total_pembulatan == $max_pembulatan OR $total_pembulatan == 0){
					$total_pembulatan = 0;
				}
				
				if(empty($get_opt['use_pembulatan'])){
					$total_pembulatan = 0;
				}
				
				$pembulatan_show = priceFormat($total_pembulatan);
				
				
				if($total_pembulatan < 0){
					$pembulatan_show = "(".$pembulatan_show.")";
				}
				


				//$grand_total = $total + $total_pembulatan;
				
				
				$cash = $billingData->total_paid;
				//$return = $cash - $grand_total;
				
				$grand_total = $billingData->grand_total;
				$return = $billingData->total_return;
				$compliment_total = $billingData->compliment_total_tax_service;
								
				$subtotal_show = printer_command_align_right(priceFormat($subtotal), 11);
				$total_show = printer_command_align_right(priceFormat($total), 11);
				$tax_total_show = printer_command_align_right(priceFormat($tax_total), 11);
				$service_total_show = printer_command_align_right(priceFormat($service_total), 11);
				$pembulatan_show = printer_command_align_right($pembulatan_show, 11);
				$grand_total_show = printer_command_align_right(priceFormat($grand_total), 11);
				$cash_show = printer_command_align_right(priceFormat($cash), 11);
				$return_show = printer_command_align_right(priceFormat($return), 11);
				$compliment_total_show = printer_command_align_right(priceFormat($compliment_total), 11);
				
				//PENGURANG-------------
				$discount_total_show = 0;
				if($discount_total > 0){
					$discount_total_show = '('.priceFormat($discount_total).')';
				}
				$discount_total_show = printer_command_align_right($discount_total_show, 11);
				
				$total_dp_show = 0;
				if($total_dp > 0){
					$total_dp_show = '('.priceFormat($total_dp).')';
					//$total_dp_show = "\n[tab]DP[tab]".$total_dp_show;
				}
				$total_dp_show = printer_command_align_right($total_dp_show, 11);
				
				
				$payment_type_show = '-';
				if(!empty($billingData->payment_type_name)){
					$payment_type_show = $billingData->payment_type_name;
				}
				
				$is_half_payment = $billingData->is_half_payment;
				if(!empty($is_half_payment)){
					
					$total_cash_show = printer_command_align_right(priceFormat($billingData->total_cash), 11);
					$total_credit_show = printer_command_align_right(priceFormat($billingData->total_credit), 11);
					$half_payment_show = 'Cash[tab]'.$total_cash_show."\n";
					$half_payment_show .= '[tab]'.$payment_type_show.'[tab]'.$total_credit_show;
					$payment_type_show = $half_payment_show;
					
				}
				
				$print_attr = array(
					"{date}"	=> date("d/m/Y"),
					"{date_time}"	=> date("d/m/Y H:i"),
					"{user}"	=> $session_user,
					"{table_no}"	=> $billingData->table_no,
					"{billing_no}"	=> $billingData->billing_no,
					"{order_data}"	=> $order_data,
					"{order_data2}"	=> $order_data2,
					"{subtotal}"	=> $subtotal_show,
					//"{additional_total}" => $additional_total,
					"{tax_total}" => $tax_total_show,
					"{service_total}" => $service_total_show,
					"{total}"	=> $total_show,
					"{rounded}"	=> $pembulatan_show,
					"{pembulatan}"	=> $pembulatan_show,
					"{potongan}"	=> $discount_total_show,
					"{grand_total}"	=> $grand_total_show,
					"{cash}"	=> $cash_show,
					"{return}"	=> $return_show,
					"{payment_type}"=> $payment_type_show,
					//"\n{dp_total}"=> $total_dp_show,
					"{dp_total}"=> $total_dp_show,
					"{notes}"=> $billingData->billing_notes,
					"{guest}"=> $billingData->total_guest,
					"{compliment}"=> $compliment_total_show
				);
				
				if(!empty($is_void)){
					$print_attr["billing_no}"] = $billingData->billing_no.' (VOID)';
				}
				
				if($discount_total == 0){
					$cashierReceipt_layout = empty_value_printer_text($cashierReceipt_layout, '{potongan}');
				}
				
				if($compliment_total == 0){
					$cashierReceipt_layout = empty_value_printer_text($cashierReceipt_layout, '{compliment}');
				}
				
				if($total_dp == 0){
					$cashierReceipt_layout = empty_value_printer_text($cashierReceipt_layout, '{dp_total}');
				}
				
				$cashierReceipt_layout = str_replace("{hide_empty}","", $cashierReceipt_layout);
				
				$cashierReceipt_layout .= $cashierReceipt_layout_footer;
				$print_content_cashierReceipt = strtr($cashierReceipt_layout, $print_attr);
				$print_content_cashierReceipt_monitoring = strtr($cashierReceipt_layout, $print_attr);
				
				//TYPE PRINTER
				$printer_type_cashier = '';
				$printer_tipe_cashierReceipt_default = '';
				if(!empty($get_opt['printer_tipe_cashierReceipt_default'])){
					$printer_tipe_cashierReceipt_default = $get_opt['printer_tipe_cashierReceipt_default'];
				}
				if(!empty($get_opt['printer_tipe_cashierReceipt_'.$ip_addr])){
					$printer_type_cashier = $get_opt['printer_tipe_cashierReceipt_'.$ip_addr];
				}
				
				if(empty($printer_type_cashier)){
					$printer_type_cashier = $printer_tipe_cashierReceipt_default;
				}
				
				if(!empty($print_anywhere)){
					$printer_type_cashier = $print_anywhere->printer_tipe;
				}
				
				$print_content_cashierReceipt = replace_to_printer_command($print_content_cashierReceipt, $printer_type_cashier, $printer_pin_cashierReceipt);
				
				//echo $printer_type_cashier." ".$printer_pin_cashierReceipt;
				//print_r($print_content_cashierReceipt);
				//die();
				
				//$print_content_cashierReceipt = "鸡丝鱼翅";
				//echo $print_content_cashierReceipt;

				$r = array('success' => false, 'info' => '', 'print' => array());
									
				if($print_type == 1 OR $print_type == 0 OR $print_type == 99){
					$r['print'][] = $print_content_cashierReceipt;
					//DIRECT PRINT USING PHP - CASHIER PRINTER				
					$is_print_error = false;
					
					//SAVE to Print Monitoring
					$data_printMonitoring = array(
						'tipe'			=> 'billing',
						'peritem'		=> '0',
						'print_date'	=> date("Y-m-d"),
						'print_datetime'=> date("Y-m-d H:i:s"),
						'user'			=> $session_user,
						'table_no'		=> $billingData->table_no,
						'billing_no'	=> $billingData->billing_no,
						'receiptTxt'	=> $print_content_cashierReceipt_monitoring,
						'printer'		=> $printer_ip_cashierReceipt,
						'tipe_printer'	=> $printer_type_cashier,
						'tipe_pin'		=> $printer_pin_cashierReceipt,
						'status_print'	=> 1
					);
					$this->db->insert($this->table_print_monitoring, $data_printMonitoring);
					
					header('Content-Type: text/plain; charset=utf-8');
					try {
						$ph = printer_open($printer_ip_cashierReceipt);
					} catch (Exception $e) {
						$ph = false;
					}
					
					//$ph = @printer_open($printer_ip_cashierReceipt);
					
					if($ph)
					{	
						printer_start_doc($ph, "CASHIER RECEIPT - PAYMENT");
						printer_start_page($ph);
						printer_set_option($ph, PRINTER_MODE, "RAW");
						printer_write($ph, $print_content_cashierReceipt);
						printer_end_page($ph);
						printer_end_doc($ph);
						printer_close($ph);
						$r['success'] = true;
						
					}else{
						$is_print_error = true;
					}
					
					//DEV
					/* $r['success'] = false;
					$r['info'] = $print_content_cashierReceipt; */
						
					
					if($is_print_error){					
						$r['info'] .= 'Communication with Printer Cashier Failed!<br/>';
					}	
					
					
				}
				
				if($print_type == 2 OR $print_type == -234){
					
					if(empty($print_qcReceipt)){
						$r['info'] = 'IP: '.$ip_addr.' cant print to '.$printer_ip_qcReceipt;
						echo json_encode($r);
						die();
					}
					
					//QC PRINTER ---------------
					if(!empty($print_qcReceipt) AND (!empty($order_data_kitchen) OR !empty($order_data_bar) OR !empty($order_data_other) OR !empty($order_qc_id))){
					
						if(!empty($order_data_kitchen_update) OR !empty($order_data_bar_update) OR !empty($order_data_other_update) OR !empty($order_qc_id) OR $print_type == -234){
							
							
							//MERGE ALL ORDER
							$order_data_kitchen_qc = '';
							if(!empty($order_data_kitchen)){
								$order_data_kitchen_qc = "KITCHEN[tab]\n";
								foreach($order_data_kitchen as $dt){
									$order_data_kitchen_qc .= $dt;
								}
							}
							
							$order_data_bar_qc = '';
							if(!empty($order_data_bar)){
								$order_data_bar_qc = "BAR[tab]\n";
								foreach($order_data_bar as $dt){
									$order_data_bar_qc .= $dt;
								}
							}
							
							$order_data_other_qc = '';
							if(!empty($order_data_other)){
								$order_data_other_qc = "OTHER[tab]\n";
								foreach($order_data_other as $dt){
									$order_data_other_qc .= $dt;
								}
							}
							
							$is_print_error = false;
							$print_attr = array(
								"{date}"	=> date("d/m/Y"),
								"{date_time}"	=> date("d/m/Y H:i"),
								"{user}"	=> $session_user,
								"{table_no}"	=> $billingData->table_no,
								"{order_data_kitchen}"	=> $order_data_kitchen_qc,
								"{order_data_bar}"	=> $order_data_bar_qc,
								"{order_data_other}"	=> $order_data_other_qc,
								"{guest}"	=> $billingData->total_guest,
								"{qc_notes}"	=> $billingData->qc_notes
							);
							
							$print_content_qcReceipt = strtr($qcReceipt_layout, $print_attr);	
							$print_content_qcReceipt_monitoring = $print_content_qcReceipt;	
							
							//TYPE PRINTER
							$printer_type_qc = '';
							$printer_tipe_qcReceipt_default = '';
							if(!empty($get_opt['printer_tipe_qcReceipt_default'])){
								$printer_tipe_qcReceipt_default = $get_opt['printer_tipe_qcReceipt_default'];
							}
							if(!empty($get_opt['printer_tipe_qcReceipt_'.$ip_addr])){
								$printer_type_qc = $get_opt['printer_tipe_qcReceipt_'.$ip_addr];
							}
							
							if(empty($printer_type_qc)){
								$printer_type_qc = $printer_tipe_qcReceipt_default;
							}
				
							if(!empty($print_anywhere)){
								$printer_type_qc = $print_anywhere->printer_tipe;
							}
							
							$print_content_qcReceipt = replace_to_printer_command($print_content_qcReceipt, $printer_type_qc, $printer_pin_qcReceipt);
							
							$r['print'][] = $print_content_qcReceipt;
							//DIRECT PRINT USING PHP - QC PRINTER
							
							//TESTER
							/*$r['success'] = true;
							$r['info'] = 'Semua Cancel Order Kitchen dan Bar Sudah diPrint<br/>';
							
							if(!empty($is_void) AND !empty($void_id)){
								return $r;
							}
							echo json_encode($r);
							die();*/
							
							//echo $print_content_qcReceipt;
							//die();
							
							//$printMonitoring_qc
							if($printMonitoring_qc == 1){
								
								$r['success'] = true;
									
								//update status qc
								if(!empty($order_qc_id)){
									$order_qc_id_txt = implode(",", $order_qc_id);
									$data_update = array(
										'print_qc' => 1
									);
									$this->db->update($this->table2, $data_update, "id IN (".$order_qc_id_txt.")");
								}
								
								//SAVE to Print Monitoring
								$data_printMonitoring = array(
									'tipe'			=> 'qc',
									'peritem'		=> '0',
									'print_date'	=> date("Y-m-d"),
									'print_datetime'=> date("Y-m-d H:i:s"),
									'user'			=> $session_user,
									'table_no'		=> $billingData->table_no,
									'billing_no'	=> $billingData->billing_no,
									'receiptTxt'	=> $print_content_qcReceipt_monitoring,
									'printer'		=> $printer_ip_qcReceipt,
									'tipe_printer'	=> $printer_type_qc,
									'tipe_pin'		=> $printer_pin_qcReceipt
								);
								$this->db->insert($this->table_print_monitoring, $data_printMonitoring);
								
							}else{
								try {
									$ph = printer_open($printer_ip_qcReceipt);
								} catch (Exception $e) {
									$ph = false;
								}
								
								$ph = @printer_open($printer_ip_qcReceipt);
								if($ph)
								{
									
									printer_start_doc($ph, "QC RECEIPT FROM CASHIER");
									printer_start_page($ph);
									printer_set_option($ph, PRINTER_MODE, "RAW");
									printer_write($ph, $print_content_qcReceipt);
									printer_end_page($ph);
									printer_end_doc($ph);
									printer_close($ph);
									
									$r['success'] = true;
									
									//update status qc
									if(!empty($order_qc_id)){
										$order_qc_id_txt = implode(",", $order_qc_id);
										$data_update = array(
											'print_qc' => 1
										);
										$this->db->update($this->table2, $data_update, "id IN (".$order_qc_id_txt.")");
									}
									
									
								}else{
									$is_print_error = true;
								}
							}
							
							
							
							//DEV SHOW PRINT
							/* 
							$r['success'] = false;
							$r['info'] = $print_content_qcReceipt;
										 */				
							
							if($is_print_error){
								$r['info'] .= 'Communication with Printer QC Failed!<br/>';
							}

						}else{
							$r['info'] .= 'Semua Order Kitchen dan Bar utk QC Sudah diPrint<br/>';
						}
					
					}else{
						$r['info'] .= 'Belum ada order';
					}
				}
				
				if($print_type == 3 OR $print_type == -234){
					//KITCHEN PRINTER ---------------
					
					if(empty($print_kitchenReceipt)){
						$r['info'] = 'IP: '.$ip_addr.' cant print to '.$printer_ip_kitchenReceipt;
						echo json_encode($r);
						die();
					}
					
					if(!empty($print_kitchenReceipt) AND !empty($order_data_kitchen) AND
						(!empty($order_data_kitchen_update) OR $print_type == -234)
					){
						$is_print_error = false;
						
						//TYPE PRINTER
						$printer_type_kitchen = '';
						$printer_tipe_kitchenReceipt_default = '';
						if(!empty($get_opt['printer_tipe_kitchenReceipt_default'])){
							$printer_tipe_kitchenReceipt_default = $get_opt['printer_tipe_kitchenReceipt_default'];
						}
						if(!empty($get_opt['printer_tipe_kitchenReceipt_'.$ip_addr])){
							$printer_type_kitchen = $get_opt['printer_tipe_kitchenReceipt_'.$ip_addr];
						}
						
						if(empty($printer_type_kitchen)){
							$printer_type_kitchen = $printer_tipe_kitchenReceipt_default;
						}
				
						if(!empty($print_anywhere)){
							$printer_type_kitchen = $print_anywhere->printer_tipe;
						}
						
						/*
						$order_data_kitchen = str_replace("KITCHEN[tab]","[tab]",$order_data_kitchen);
						
						$print_attr = array(
							"{date}"	=> date("d/m/Y"),
							"{date_time}"	=> date("d/m/Y H:i"),
							"{user}"	=> $session_user,
							"{table_no}"	=> $billingData->table_no,
							"{order_data}"	=> $order_data_kitchen
						);
						
						$print_content_kitchenReceipt = strtr($kitchenReceipt_layout, $print_attr);	
						$print_content_kitchenReceipt_monitoring = $print_content_kitchenReceipt;
						
						$print_content_kitchenReceipt = replace_to_printer_command($print_content_kitchenReceipt, $printer_type_kitchen, $printer_pin_kitchenReceipt);
						
						$r['print'][] = $print_content_kitchenReceipt;
						*/
						
						//DIRECT PRINT USING PHP - QC PRINTER
						
						
						//echo $print_content_kitchenReceipt;
						//die();
						
						//$printMonitoring_kitchen
						if($printMonitoring_kitchen == 1){
							
							$data_printMonitoring = array();
							
							//print_order_peritem_kitchen
							if(!empty($get_opt['print_order_peritem_kitchen'])){
								
								if(!empty($order_data_kitchen_update)){
									
									$update_id_order = array();
									foreach($order_data_kitchen_update as $idO){
										
										if(!empty($order_data_kitchen_peritem[$idO])){
											
											
											if(!in_array($idO, $update_id_order) AND $print_type != -234){
												$update_id_order[] = $idO;
											}
											
											$print_attr = array(
												"{date}"	=> date("d/m/Y"),
												"{date_time}"	=> date("d/m/Y H:i"),
												"{user}"	=> $session_user,
												"{table_no}"	=> $billingData->table_no,
												"{order_data}"	=> $order_data_kitchen_peritem[$idO],
												"{guest}"=> $billingData->total_guest,
												"{qc_notes}"	=> $billingData->qc_notes
											);
											
											$print_content_kitchenReceipt = strtr($kitchenReceipt_layout, $print_attr);	
											$print_content_kitchenReceipt_monitoring = $print_content_kitchenReceipt;	
											$print_content_kitchenReceipt = replace_to_printer_command($print_content_kitchenReceipt, $printer_type_kitchen, $printer_pin_kitchenReceipt);
											
											//SAVE to Print Monitoring
											$data_printMonitoring[] = array(
												'tipe'			=> 'kitchen',
												'peritem'		=> '1',
												'print_date'	=> date("Y-m-d"),
												'print_datetime'	=> date("Y-m-d H:i:s"),
												'user'			=> $session_user,
												'table_no'		=> $billingData->table_no,
												'billing_no'	=> $billingData->billing_no,
												'receiptTxt'	=> $print_content_kitchenReceipt_monitoring,
												'printer'		=> $printer_ip_kitchenReceipt,
												'tipe_printer'	=> $printer_type_kitchen,
												'tipe_pin'		=> $printer_pin_kitchenReceipt
											);
											
										}
										
										
									}
									
									
									if(!empty($update_id_order)){

										$order_data_kitchen_update_txt = implode(",", $update_id_order);
										$data_update = array(
											'order_status' => 'done'
										);
										$this->db->update($this->table2, $data_update, "id IN (".$order_data_kitchen_update_txt.")");
										
									}
								}
								
							}else{
								
								if(!empty($order_data_kitchen_update)){
									
									$update_id_order = array();
									$order_data_kitchen_Receipt = '';
									foreach($order_data_kitchen_update as $idO){
										
										if(!empty($order_data_kitchen[$idO])){
											
											if(!in_array($idO, $update_id_order) AND $print_type != -234){
												$update_id_order[] = $idO;
											}
											
											$order_data_kitchen_Receipt .= $order_data_kitchen[$idO];
										
										}
									}
									
									
									$order_data_kitchen_Receipt = str_replace("KITCHEN[tab]","[tab]",$order_data_kitchen_Receipt);
									
									$print_attr = array(
										"{date}"	=> date("d/m/Y"),
										"{date_time}"	=> date("d/m/Y H:i"),
										"{user}"	=> $session_user,
										"{table_no}"	=> $billingData->table_no,
										"{order_data}"	=> $order_data_kitchen_Receipt,
										"{guest}"=> $billingData->total_guest,
										"{qc_notes}"	=> $billingData->qc_notes
									);
									
									$print_content_kitchenReceipt = strtr($kitchenReceipt_layout, $print_attr);	
									$print_content_kitchenReceipt_monitoring = $print_content_kitchenReceipt;
									
									$print_content_kitchenReceipt = replace_to_printer_command($print_content_kitchenReceipt, $printer_type_kitchen, $printer_pin_kitchenReceipt);
									
									$r['print'][] = $print_content_kitchenReceipt;
								
									//SAVE to Print Monitoring
									$data_printMonitoring[] = array(
										'tipe'			=> 'kitchen',
										'peritem'		=> '0',
										'print_date'	=> date("Y-m-d"),
										'print_datetime'	=> date("Y-m-d H:i:s"),
										'user'			=> $session_user,
										'table_no'		=> $billingData->table_no,
										'billing_no'	=> $billingData->billing_no,
										'receiptTxt'	=> $print_content_kitchenReceipt_monitoring,
										'printer'		=> $printer_ip_kitchenReceipt,
										'tipe_printer'	=> $printer_type_kitchen,
										'tipe_pin'		=> $printer_pin_kitchenReceipt
									);

									if(!empty($update_id_order)){

										$order_data_kitchen_update_txt = implode(",", $update_id_order);
										$data_update = array(
											'order_status' => 'done'
										);
										$this->db->update($this->table2, $data_update, "id IN (".$order_data_kitchen_update_txt.")");
										
									}
									
								}
							}
							
							$r['success'] = true;
							$this->db->insert_batch($this->table_print_monitoring, $data_printMonitoring);
							
						}else{
							
							try {
								$ph = printer_open($printer_ip_kitchenReceipt);
							} catch (Exception $e) {
								$ph = false;
							}
							
							//$ph = @printer_open($printer_ip_kitchenReceipt);
							if($ph)
							{
								//print_order_peritem_kitchen
								if(!empty($get_opt['print_order_peritem_kitchen'])){
									
									if(!empty($order_data_kitchen_update)){
										
										$update_id_order = array();
										foreach($order_data_kitchen_update as $idO){
											
											if(!empty($order_data_kitchen_peritem[$idO])){
												
												
												if(!in_array($idO, $update_id_order) AND $print_type != -234){
													$update_id_order[] = $idO;
												}
												
												$print_attr = array(
													"{date}"	=> date("d/m/Y"),
													"{date_time}"	=> date("d/m/Y H:i"),
													"{user}"	=> $session_user,
													"{table_no}"	=> $billingData->table_no,
													"{order_data}"	=> $order_data_kitchen_peritem[$idO],
													"{guest}"=> $billingData->total_guest,
													"{qc_notes}"	=> $billingData->qc_notes
												);
												
												$print_content_kitchenReceipt = strtr($kitchenReceipt_layout, $print_attr);	
												$print_content_kitchenReceipt = replace_to_printer_command($print_content_kitchenReceipt, $printer_type_kitchen, $printer_pin_kitchenReceipt);
												
												//$ph = @printer_open($printer_ip_kitchenReceipt);
												printer_start_doc($ph, "KITCHEN RECEIPT FROM CASHIER");
												printer_start_page($ph);
												printer_set_option($ph, PRINTER_MODE, "RAW");
												printer_write($ph, $print_content_kitchenReceipt);
												printer_end_page($ph);
												printer_end_doc($ph);
												
												//echo $print_content_kitchenReceipt;
												//die();
											}
											
											
										}
										
										
										printer_start_doc($ph, "KITCHEN RECEIPT FROM CASHIER");
										printer_start_page($ph);
										printer_set_option($ph, PRINTER_MODE, "RAW");
										printer_write($ph, "\n");
										printer_end_page($ph);
										printer_end_doc($ph);
										printer_close($ph);
										
										if(!empty($update_id_order)){

											$order_data_kitchen_update_txt = implode(",", $update_id_order);
											$data_update = array(
												'order_status' => 'done'
											);
											$this->db->update($this->table2, $data_update, "id IN (".$order_data_kitchen_update_txt.")");
											
										}
									}
									
								}else{
									
									if(!empty($order_data_kitchen_update)){
									
										$update_id_order = array();
										$order_data_kitchen_Receipt = '';
										foreach($order_data_kitchen_update as $idO){
											
											if(!empty($order_data_kitchen[$idO])){
												
												if(!in_array($idO, $update_id_order) AND $print_type != -234){
													$update_id_order[] = $idO;
												}
												
												$order_data_kitchen_Receipt .= $order_data_kitchen[$idO];
											
											}
										}
										
										
										$order_data_kitchen_Receipt = str_replace("KITCHEN[tab]","[tab]",$order_data_kitchen_Receipt);
										
										$print_attr = array(
											"{date}"	=> date("d/m/Y"),
											"{date_time}"	=> date("d/m/Y H:i"),
											"{user}"	=> $session_user,
											"{table_no}"	=> $billingData->table_no,
											"{order_data}"	=> $order_data_kitchen_Receipt,
											"{guest}"=> $billingData->total_guest,
											"{qc_notes}"	=> $billingData->qc_notes
										);
										
										$print_content_kitchenReceipt = strtr($kitchenReceipt_layout, $print_attr);	
										$print_content_kitchenReceipt_monitoring = $print_content_kitchenReceipt;
										
										$print_content_kitchenReceipt = replace_to_printer_command($print_content_kitchenReceipt, $printer_type_kitchen, $printer_pin_kitchenReceipt);
										
										$r['print'][] = $print_content_kitchenReceipt;
										
										printer_start_doc($ph, "KITCHEN RECEIPT FROM CASHIER");
										printer_start_page($ph);
										printer_set_option($ph, PRINTER_MODE, "RAW");
										printer_write($ph, $print_content_kitchenReceipt);
										printer_end_page($ph);
										printer_end_doc($ph);
										printer_close($ph);
										

										if(!empty($update_id_order)){

											$order_data_kitchen_update_txt = implode(",", $update_id_order);
											$data_update = array(
												'order_status' => 'done'
											);
											$this->db->update($this->table2, $data_update, "id IN (".$order_data_kitchen_update_txt.")");
											
										}
										
									}
									
									
								}
								
								$r['success'] = true;
							}else{
								$is_print_error = true;
							}
						}
						
						//DEV SHOW PRINT
						/* 
						$r['success'] = true;
						if(!empty($order_data_kitchen_update)){

							$order_data_kitchen_update_txt = implode(",", $order_data_kitchen_update);
							$data_update = array(
								'order_status' => 'done'
							);
							$this->db->update($this->table2, $data_update, "id IN (".$order_data_kitchen_update_txt.")");
							
							$r['success'] = false;
							$r['info'] = $print_content_kitchenReceipt;
						}
						 */
						
						if($is_print_error){					
							$r['info'] .= 'Communication with Printer Kitchen Failed!<br/>';
						}	
					}else{
						
						if(empty($order_data_kitchen) AND !empty($order_data_kitchen_update)){
							$r['info'] .= 'Semua Order Kitchen Sudah diPrint<br/>';
						}else{
							$r['info'] .= 'Belum ada order Kitchen';
						}
					}
				}
				
				
				if($print_type == 4 OR $print_type == -234){
					//BAR PRINTER ---------------
					
					if(empty($print_barReceipt)){
						$r['info'] = 'IP: '.$ip_addr.' cant print to '.$printer_ip_barReceipt;
						echo json_encode($r);
						die();
					}
					
					if(!empty($print_barReceipt) AND !empty($order_data_bar) AND 
						(!empty($order_data_bar_update) OR $print_type == -234)
					){
						$is_print_error = false;			
						
						//TYPE PRINTER
						$printer_type_bar = '';
						$printer_tipe_barReceipt_default = '';
						if(!empty($get_opt['printer_tipe_barReceipt_default'])){
							$printer_tipe_barReceipt_default = $get_opt['printer_tipe_barReceipt_default'];
						}
						if(!empty($get_opt['printer_tipe_barReceipt_'.$ip_addr])){
							$printer_type_bar = $get_opt['printer_tipe_barReceipt_'.$ip_addr];
						}
						
						if(empty($printer_type_bar)){
							$printer_type_bar = $printer_tipe_barReceipt_default;
						}		
				
						if(!empty($print_anywhere)){
							$printer_type_bar = $print_anywhere->printer_tipe;
						}					
						
						/*
						$order_data_bar = str_replace("BAR[tab]","[tab]",$order_data_bar);
						
						$print_attr = array(
							"{date}"	=> date("d/m/Y"),
							"{date_time}"	=> date("d/m/Y H:i"),
							"{user}"	=> $session_user,
							"{table_no}"	=> $billingData->table_no,
							"{order_data}"	=> $order_data_bar
						);
						
						$print_content_barReceipt = strtr($barReceipt_layout, $print_attr);			
						$print_content_barReceipt_monitoring = $print_content_barReceipt;
						$print_content_barReceipt = replace_to_printer_command($print_content_barReceipt, $printer_type_bar, $printer_pin_barReceipt);
						
						$r['print'][] = $print_content_barReceipt;
						*/
						
						//DIRECT PRINT USING PHP - QC PRINTER
						
						//$printMonitoring_bar
						if($printMonitoring_bar == 1){
							
							$data_printMonitoring = array();
							
							//print_order_peritem_bar
							if(!empty($get_opt['print_order_peritem_bar'])){
								
								if(!empty($order_data_bar_update)){
									
									$update_id_order = array();
									foreach($order_data_bar_update as $idO){
										
										if(!empty($order_data_bar_peritem[$idO])){
											
											
											if(!in_array($idO, $update_id_order) AND $print_type != -234){
												$update_id_order[] = $idO;
											}
											
											$print_attr = array(
												"{date}"	=> date("d/m/Y"),
												"{date_time}"	=> date("d/m/Y H:i"),
												"{user}"	=> $session_user,
												"{table_no}"	=> $billingData->table_no,
												"{order_data}"	=> $order_data_bar_peritem[$idO],
												"{guest}"=> $billingData->total_guest,
												"{qc_notes}"	=> $billingData->qc_notes
											);
											
											$print_content_barReceipt = strtr($barReceipt_layout, $print_attr);	
											$print_content_barReceipt_monitoring = $print_content_barReceipt;	
											$print_content_barReceipt = replace_to_printer_command($print_content_barReceipt, $printer_type_bar, $printer_pin_barReceipt);
											
											//SAVE to Print Monitoring
											$data_printMonitoring[] = array(
												'tipe'			=> 'bar',
												'peritem'		=> '1',
												'print_date'	=> date("Y-m-d"),
												'print_datetime'	=> date("Y-m-d H:i:s"),
												'user'			=> $session_user,
												'table_no'		=> $billingData->table_no,
												'billing_no'	=> $billingData->billing_no,
												'receiptTxt'	=> $print_content_barReceipt_monitoring,
												'printer'		=> $printer_ip_barReceipt,
												'tipe_printer'	=> $printer_type_bar,
												'tipe_pin'		=> $printer_pin_barReceipt
											);
											
										}
										
										
									}
									
									if(!empty($update_id_order)){

										$order_data_bar_update_txt = implode(",", $update_id_order);
										$data_update = array(
											'order_status' => 'done'
										);
										$this->db->update($this->table2, $data_update, "id IN (".$order_data_bar_update_txt.")");
										
									}
								}
								
							}else{
								
								if(!empty($order_data_bar_update)){
								
									$update_id_order = array();
									$order_data_bar_Receipt = '';
									foreach($order_data_bar_update as $idO){
										
										if(!empty($order_data_bar[$idO])){
											
											if(!in_array($idO, $update_id_order) AND $print_type != -234){
												$update_id_order[] = $idO;
											}
											
											$order_data_bar_Receipt .= $order_data_bar[$idO];
										
										}
									}
									
									
									$order_data_bar_Receipt = str_replace("BAR[tab]","[tab]",$order_data_bar_Receipt);
									
									$print_attr = array(
										"{date}"	=> date("d/m/Y"),
										"{date_time}"	=> date("d/m/Y H:i"),
										"{user}"	=> $session_user,
										"{table_no}"	=> $billingData->table_no,
										"{order_data}"	=> $order_data_bar_Receipt,
										"{guest}"=> $billingData->total_guest,
										"{qc_notes}"	=> $billingData->qc_notes
									);
									
									$print_content_barReceipt = strtr($barReceipt_layout, $print_attr);			
									$print_content_barReceipt_monitoring = $print_content_barReceipt;
									$print_content_barReceipt = replace_to_printer_command($print_content_barReceipt, $printer_type_bar, $printer_pin_barReceipt);
									
									$r['print'][] = $print_content_barReceipt;
									
									//SAVE to Print Monitoring
									$data_printMonitoring[] = array(
										'tipe'			=> 'bar',
										'peritem'		=> '0',
										'print_date'	=> date("Y-m-d"),
										'print_datetime'	=> date("Y-m-d H:i:s"),
										'user'			=> $session_user,
										'table_no'		=> $billingData->table_no,
										'billing_no'	=> $billingData->billing_no,
										'receiptTxt'	=> $print_content_barReceipt_monitoring,
										'printer'		=> $printer_ip_barReceipt,
										'tipe_printer'	=> $printer_type_bar,
										'tipe_pin'		=> $printer_pin_barReceipt
									);
									
									if(!empty($update_id_order)){

										$order_data_bar_update_txt = implode(",", $update_id_order);
										$data_update = array(
											'order_status' => 'done'
										);
										$this->db->update($this->table2, $data_update, "id IN (".$order_data_bar_update_txt.")");
										
									}
									
								}
								
							}
							
							$r['success'] = true;
							$this->db->insert_batch($this->table_print_monitoring, $data_printMonitoring);
							
						}else{
							
							try {
								$ph = printer_open($printer_ip_barReceipt);
							} catch (Exception $e) {
								$ph = false;
							}
							
							//$ph = @printer_open($printer_ip_barReceipt);
							if($ph)
							{
								
								//print_order_peritem_bar
								if(!empty($get_opt['print_order_peritem_bar'])){
									
									if(!empty($order_data_bar_update)){
										
										$update_id_order = array();
										foreach($order_data_bar_update as $idO){
											
											if(!empty($order_data_bar_peritem[$idO])){
												
												
												if(!in_array($idO, $update_id_order) AND $print_type != -234){
													$update_id_order[] = $idO;
												}
												
												$print_attr = array(
													"{date}"	=> date("d/m/Y"),
													"{date_time}"	=> date("d/m/Y H:i"),
													"{user}"	=> $session_user,
													"{table_no}"	=> $billingData->table_no,
													"{order_data}"	=> $order_data_bar_peritem[$idO],
													"{guest}"=> $billingData->total_guest,
													"{qc_notes}"	=> $billingData->qc_notes
												);
												
												$print_content_barReceipt = strtr($barReceipt_layout, $print_attr);	
												$print_content_barReceipt = replace_to_printer_command($print_content_barReceipt, $printer_type_bar, $printer_pin_barReceipt);
												
												//$ph = @printer_open($printer_ip_barReceipt);
												printer_start_doc($ph, "BAR RECEIPT FROM ORDER");
												printer_start_page($ph);
												printer_set_option($ph, PRINTER_MODE, "RAW");
												printer_write($ph, $print_content_barReceipt);
												printer_end_page($ph);
												printer_end_doc($ph);
												
												//echo $print_content_barReceipt;
												//die();
											}
											
											
										}
										
										
										printer_start_doc($ph, "BAR RECEIPT FROM ORDER");
										printer_start_page($ph);
										printer_set_option($ph, PRINTER_MODE, "RAW");
										printer_write($ph, "\n");
										printer_end_page($ph);
										printer_end_doc($ph);
										printer_close($ph);
										
										if(!empty($update_id_order)){

											$order_data_bar_update_txt = implode(",", $update_id_order);
											$data_update = array(
												'order_status' => 'done'
											);
											$this->db->update($this->table2, $data_update, "id IN (".$order_data_bar_update_txt.")");
											
										}
									}
									
								}else{
									
									if(!empty($order_data_bar_update)){
									
										$update_id_order = array();
										$order_data_bar_Receipt = '';
										foreach($order_data_bar_update as $idO){
											
											if(!empty($order_data_bar[$idO])){
												
												if(!in_array($idO, $update_id_order) AND $print_type != -234){
													$update_id_order[] = $idO;
												}
												
												$order_data_bar_Receipt .= $order_data_bar[$idO];
											
											}
										}
										
										
										$order_data_bar_Receipt = str_replace("BAR[tab]","[tab]",$order_data_bar_Receipt);
										
										$print_attr = array(
											"{date}"	=> date("d/m/Y"),
											"{date_time}"	=> date("d/m/Y H:i"),
											"{user}"	=> $session_user,
											"{table_no}"	=> $billingData->table_no,
											"{order_data}"	=> $order_data_bar_Receipt,
											"{guest}"=> $billingData->total_guest,
											"{qc_notes}"	=> $billingData->qc_notes
										);
										
										$print_content_barReceipt = strtr($barReceipt_layout, $print_attr);			
										$print_content_barReceipt_monitoring = $print_content_barReceipt;
										$print_content_barReceipt = replace_to_printer_command($print_content_barReceipt, $printer_type_bar, $printer_pin_barReceipt);
										
										$r['print'][] = $print_content_barReceipt;
										
										printer_start_doc($ph, "BAR RECEIPT FROM ORDER");
										printer_start_page($ph);
										printer_set_option($ph, PRINTER_MODE, "RAW");
										printer_write($ph, $print_content_barReceipt);
										printer_end_page($ph);
										printer_end_doc($ph);
										printer_close($ph);
										
										
										if(!empty($update_id_order)){

											$order_data_bar_update_txt = implode(",", $update_id_order);
											$data_update = array(
												'order_status' => 'done'
											);
											$this->db->update($this->table2, $data_update, "id IN (".$order_data_bar_update_txt.")");
											
										}
										
									}
								}
								
								$r['success'] = true;
							}else{
								$is_print_error = true;
							}					
							 
						}
						
						//DEV SHOW PRINT
						/* 
						$r['success'] = true;
						if(!empty($order_data_bar_update)){
							$order_data_bar_update_txt = implode(",", $order_data_bar_update);
							$data_update = array(
								'order_status' => 'done'
							);
							$this->db->update($this->table2, $data_update, "id IN (".$order_data_bar_update_txt.")");
							
							$r['success'] = false;
							$r['info'] = $print_content_barReceipt;
						}
						  */
						
						if($is_print_error){					
							$r['info'] .= 'Communication with Printer Bar Failed!<br/>';
						}	
					}else{
						
						if(empty($order_data_bar) AND !empty($order_data_bar_update)){
							$r['info'] .= 'Semua Order Bar Sudah diPrint<br/>';
						}else{
							$r['info'] .= 'Belum ada order Bar';
						}
					}
				}
				
				if($print_type == 5 OR $print_type == -234){
					//OTHER PRINTER ---------------
					
					if(empty($print_otherReceipt)){
						$r['info'] = 'IP: '.$ip_addr.' cant print to '.$printer_ip_otherReceipt;
						echo json_encode($r);
						die();
					}
					
					if(!empty($print_otherReceipt) AND !empty($order_data_other) AND 
						(!empty($order_data_other_update) OR $print_type == -234)
					){
						$is_print_error = false;			
						
						//TYPE PRINTER
						$printer_type_other = '';
						$printer_tipe_otherReceipt_default = '';
						if(!empty($get_opt['printer_tipe_otherReceipt_default'])){
							$printer_tipe_otherReceipt_default = $get_opt['printer_tipe_otherReceipt_default'];
						}
						if(!empty($get_opt['printer_tipe_otherReceipt_'.$ip_addr])){
							$printer_type_other = $get_opt['printer_tipe_otherReceipt_'.$ip_addr];
						}
						
						if(empty($printer_type_other)){
							$printer_type_other = $printer_tipe_otherReceipt_default;
						}		
				
						if(!empty($print_anywhere)){
							$printer_type_other = $print_anywhere->printer_tipe;
						}												
						
						/*
						$order_data_other = str_replace("OTHER[tab]","[tab]",$order_data_other);
						
						$print_attr = array(
							"{date}"	=> date("d/m/Y"),
							"{date_time}"	=> date("d/m/Y H:i"),
							"{user}"	=> $session_user,
							"{table_no}"	=> $billingData->table_no,
							"{order_data}"	=> $order_data_other
						);
						
						$print_content_otherReceipt = strtr($otherReceipt_layout, $print_attr);	
						$print_content_otherReceipt_monitoring = $print_content_otherReceipt;
						$print_content_otherReceipt = replace_to_printer_command($print_content_otherReceipt, $printer_type_other, $printer_pin_otherReceipt);
						
						$r['print'][] = $print_content_otherReceipt;
						//DIRECT PRINT USING PHP - other PRINTER
						*/
						
						//$printMonitoring_other
						if($printMonitoring_other == 1){
							
							$data_printMonitoring = array();
							
							//print_order_peritem_other
							if(!empty($get_opt['print_order_peritem_other'])){
								
								if(!empty($order_data_other_update)){
									
									$update_id_order = array();
									foreach($order_data_other_update as $idO){
										
										if(!empty($order_data_other_peritem[$idO])){
											
											
											if(!in_array($idO, $update_id_order)){
												$update_id_order[] = $idO;
											}
											
											$print_attr = array(
												"{date}"	=> date("d/m/Y"),
												"{date_time}"	=> date("d/m/Y H:i"),
												"{user}"	=> $session_user,
												"{table_no}"	=> $billingData->table_no,
												"{order_data}"	=> $order_data_other_peritem[$idO],
												"{guest}"=> $billingData->total_guest,
												"{qc_notes}"	=> $billingData->qc_notes
											);
											
											$print_content_otherReceipt = strtr($otherReceipt_layout, $print_attr);	
											$print_content_otherReceipt_monitoring = $print_content_otherReceipt;	
											$print_content_otherReceipt = replace_to_printer_command($print_content_otherReceipt, $printer_type_other, $printer_pin_otherReceipt);
											
											//SAVE to Print Monitoring
											$data_printMonitoring[] = array(
												'tipe'			=> 'other',
												'peritem'		=> '1',
												'print_date'	=> date("Y-m-d"),
												'print_datetime'	=> date("Y-m-d H:i:s"),
												'user'			=> $session_user,
												'table_no'		=> $billingData->table_no,
												'billing_no'	=> $billingData->billing_no,
												'receiptTxt'	=> $print_content_otherReceipt_monitoring,
												'printer'		=> $printer_ip_otherReceipt,
												'tipe_printer'	=> $printer_type_other,
												'tipe_pin'		=> $printer_pin_otherReceipt
											);
											
										}
										
										
									}
									
									
									if(!empty($update_id_order)){

										$order_data_other_update_txt = implode(",", $update_id_order);
										$data_update = array(
											'order_status' => 'done'
										);
										$this->db->update($this->table2, $data_update, "id IN (".$order_data_other_update_txt.")");
										
									}
								}
								
							}else{
								
								if(!empty($order_data_other_update)){
								
									$update_id_order = array();
									$order_data_other_Receipt = '';
									foreach($order_data_other_update as $idO){
										
										if(!empty($order_data_other[$idO])){
											
											if(!in_array($idO, $update_id_order) AND $print_type != -234){
												$update_id_order[] = $idO;
											}
											
											$order_data_other_Receipt .= $order_data_other[$idO];
										
										}
									}
									
									
									$order_data_other_Receipt = str_replace("OTHER[tab]","[tab]",$order_data_other_Receipt);
									
									$print_attr = array(
										"{date}"	=> date("d/m/Y"),
										"{date_time}"	=> date("d/m/Y H:i"),
										"{user}"	=> $session_user,
										"{table_no}"	=> $billingData->table_no,
										"{order_data}"	=> $order_data_other_Receipt,
										"{guest}"=> $billingData->total_guest,
										"{qc_notes}"	=> $billingData->qc_notes
									);
									
									$print_content_otherReceipt = strtr($otherReceipt_layout, $print_attr);	
									$print_content_otherReceipt_monitoring = $print_content_otherReceipt;
									$print_content_otherReceipt = replace_to_printer_command($print_content_otherReceipt, $printer_type_other, $printer_pin_otherReceipt);
									
									$r['print'][] = $print_content_otherReceipt;
									
									//SAVE to Print Monitoring
									$data_printMonitoring[] = array(
										'tipe'			=> 'other',
										'peritem'		=> '0',
										'print_date'	=> date("Y-m-d"),
										'print_datetime'	=> date("Y-m-d H:i:s"),
										'user'			=> $session_user,
										'table_no'		=> $billingData->table_no,
										'billing_no'	=> $billingData->billing_no,
										'receiptTxt'	=> $print_content_otherReceipt_monitoring,
										'printer'		=> $printer_ip_otherReceipt,
										'tipe_printer'	=> $printer_type_other,
										'tipe_pin'		=> $printer_pin_otherReceipt
									);
									
									if(!empty($update_id_order)){

										$order_data_bar_update_txt = implode(",", $update_id_order);
										$data_update = array(
											'order_status' => 'done'
										);
										$this->db->update($this->table2, $data_update, "id IN (".$order_data_bar_update_txt.")");
										
									}
									
								}
								
								
							
							}
							
							$r['success'] = true;
							$this->db->insert_batch($this->table_print_monitoring, $data_printMonitoring);
							
						}else{
								
							try {
								$ph = printer_open($printer_ip_otherReceipt);
							} catch (Exception $e) {
								$ph = false;
							}
							
							//$ph = @printer_open($printer_ip_otherReceipt);
							if($ph)
							{
								
								//print_order_peritem_other
								if(!empty($get_opt['print_order_peritem_other'])){
									
									if(!empty($order_data_other_update)){
										
										$update_id_order = array();
										foreach($order_data_other_update as $idO){
											
											if(!empty($order_data_other_peritem[$idO])){
												
												
												if(!in_array($idO, $update_id_order)){
													$update_id_order[] = $idO;
												}
												
												$print_attr = array(
													"{date}"	=> date("d/m/Y"),
													"{date_time}"	=> date("d/m/Y H:i"),
													"{user}"	=> $session_user,
													"{table_no}"	=> $billingData->table_no,
													"{order_data}"	=> $order_data_other_peritem[$idO],
													"{guest}"=> $billingData->total_guest,
													"{qc_notes}"	=> $billingData->qc_notes
												);
												
												$print_content_otherReceipt = strtr($otherReceipt_layout, $print_attr);	
												$print_content_otherReceipt = replace_to_printer_command($print_content_otherReceipt, $printer_type_other, $printer_pin_otherReceipt);
												
												//$ph = @printer_open($printer_ip_otherReceipt);
												printer_start_doc($ph, "OTHER RECEIPT FROM ORDER");
												printer_start_page($ph);
												printer_set_option($ph, PRINTER_MODE, "RAW");
												printer_write($ph, $print_content_otherReceipt);
												printer_end_page($ph);
												printer_end_doc($ph);
												
												//echo $print_content_barReceipt;
												//die();
											}
											
											
										}
										
										
										printer_start_doc($ph, "OTHER RECEIPT FROM ORDER");
										printer_start_page($ph);
										printer_set_option($ph, PRINTER_MODE, "RAW");
										printer_write($ph, "\n");
										printer_end_page($ph);
										printer_end_doc($ph);
										printer_close($ph);
										
										if(!empty($update_id_order)){

											$order_data_other_update_txt = implode(",", $update_id_order);
											$data_update = array(
												'order_status' => 'done'
											);
											$this->db->update($this->table2, $data_update, "id IN (".$order_data_other_update_txt.")");
											
										}
									}
									
								}else{
									
									if(!empty($order_data_other_update)){
								
										$update_id_order = array();
										$order_data_other_Receipt = '';
										foreach($order_data_other_update as $idO){
											
											if(!empty($order_data_other[$idO])){
												
												if(!in_array($idO, $update_id_order) AND $print_type != -234){
													$update_id_order[] = $idO;
												}
												
												$order_data_other_Receipt .= $order_data_other[$idO];
											
											}
										}
										
										
										$order_data_other_Receipt = str_replace("OTHER[tab]","[tab]",$order_data_other_Receipt);
										
										$print_attr = array(
											"{date}"	=> date("d/m/Y"),
											"{date_time}"	=> date("d/m/Y H:i"),
											"{user}"	=> $session_user,
											"{table_no}"	=> $billingData->table_no,
											"{order_data}"	=> $order_data_other_Receipt,
											"{guest}"=> $billingData->total_guest,
											"{qc_notes}"	=> $billingData->qc_notes
										);
										
										$print_content_otherReceipt = strtr($otherReceipt_layout, $print_attr);	
										$print_content_otherReceipt_monitoring = $print_content_otherReceipt;
										$print_content_otherReceipt = replace_to_printer_command($print_content_otherReceipt, $printer_type_other, $printer_pin_otherReceipt);
										
										$r['print'][] = $print_content_otherReceipt;
										
										printer_start_doc($ph, "OTHER RECEIPT FROM ORDER");
										printer_start_page($ph);
										printer_set_option($ph, PRINTER_MODE, "RAW");
										printer_write($ph, $print_content_otherReceipt);
										printer_end_page($ph);
										printer_end_doc($ph);
										printer_close($ph);
										
										if(!empty($update_id_order)){

											$order_data_bar_update_txt = implode(",", $update_id_order);
											$data_update = array(
												'order_status' => 'done'
											);
											$this->db->update($this->table2, $data_update, "id IN (".$order_data_bar_update_txt.")");
											
										}
										
									}
									
									
								
								}
								
								$r['success'] = true;
							}else{
								$is_print_error = true;
							}					
						 
						}
						
						//DEV SHOW PRINT
						/* 
						$r['success'] = true;
						if(!empty($order_data_bar_update)){
							$order_data_bar_update_txt = implode(",", $order_data_bar_update);
							$data_update = array(
								'order_status' => 'done'
							);
							$this->db->update($this->table2, $data_update, "id IN (".$order_data_bar_update_txt.")");
							
							$r['success'] = false;
							$r['info'] = $print_content_barReceipt;
						}
						  */
						
						if($is_print_error){					
							$r['info'] .= 'Communication with Printer Other Failed!<br/>';
						}	
					}else{
						
						if(empty($order_data_other) AND !empty($order_data_other_update)){
							$r['info'] .= 'Semua Order Other Sudah diPrint<br/>';
						}else{
							$r['info'] .= 'Belum ada order Other';
						}
					}
				}
				
				
				
			}else{
				$r = array('success' => false, 'info' => 'Load Detail Failed, data not found!');
			}
			
		}else{
			$r = array('success' => false, 'info' => 'Load Detail Failed, data not found!');
		}
		
		//echo '<pre>';
		//print_r($r);
		//die();
		
		if(!empty($is_void) AND !empty($void_id)){
			return $r;
		}
		
		echo json_encode($r);
		die();
	}
	
	public function testPrinter(){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
				
		$do_print = $this->input->get_post('do_print', true);	
		
		//TIPE
		$printSetting = $this->input->get_post('printSetting', true);	
		if(empty($printSetting)){
			$printSetting = 'cashierReceipt';
		}
		
		$cutting_only = $this->input->post('cutting_only', true);
		
		$get_opt = get_option_value(array('printer_ip_'.$printSetting.'_default',
		'printer_ip_'.$printSetting.'_'.$ip_addr,
		'printer_tipe_'.$printSetting.'_default',
		'printer_tipe_'.$printSetting.'_'.$ip_addr));
		
		if($get_opt == false){
			$r = array('success' => false, 'info' => 'IP: '.$ip_addr.' cant print to '.$printer_device.'!', 'ip_addr' => $ip_addr, 'printer_ip' => $printer_device);
			echo json_encode($r);
			die();
		}
		
		$printer_device = "\\\\".$ip_addr."\\".$get_opt['printer_ip_'.$printSetting.'_default'];
		if(!empty($get_opt['printer_ip_'.$printSetting.'_'.$ip_addr])){
			$printer_device = $get_opt['printer_ip_'.$printSetting.'_'.$ip_addr];
			
			if(strstr($printer_device, '\\')){
				$printer_device = "\\\\".$printer_device;
			}
		}	
		
		$printer_tipe = $get_opt['printer_tipe_'.$printSetting.'_default'];
		if(!empty($get_opt['printer_tipe_'.$printSetting.'_'.$ip_addr])){
			$printer_tipe = $get_opt['printer_tipe_'.$printSetting.'_'.$ip_addr];
		}	
		
		$print_content = " TEST: ".$printSetting."\n TO PRINTER: ".$printer_device."\n FROM IP ".$ip_addr;
		if($cutting_only == true){
			$print_content = "\n";
		}
		
		$is_print_error = false;
		//$ph = @printer_open($printer_device);
		//die($do_print);
		
		try {
			$ph = printer_open($printer_device);
		} catch (Exception $e) {
			$ph = false;
		}
		
		if($ph)
		{
			printer_start_doc($ph, "TEST PRINTER ".ucwords($printSetting));
			printer_start_page($ph);
			printer_set_option($ph, PRINTER_MODE, "RAW");
			printer_write($ph, $print_content);
			printer_end_page($ph);
			printer_end_doc($ph);
			printer_close($ph);
			
		}else{
			$is_print_error = true;
		}
		
		if($is_print_error){
			$r = array('success' => false, 'info' => 'Communication with Printer Failed!', 'printer_ip' => $printer_device);
		}else{
			$r = array('success' => true, 'printer_tipe' => $printer_tipe, 'printSetting' => $printSetting, 'printer_ip' => $printer_device);
		}
		
		echo json_encode($r);
		die();
	}
	
	public function loadingSetting(){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$opt_value = array(
			'printer_ip_cashierReceipt_default',
			'printer_ip_cashierReceipt_'.$ip_addr,
			'printer_pin_cashierReceipt_'.$ip_addr,
			'printer_tipe_cashierReceipt_'.$ip_addr,
			
			'printer_ip_qcReceipt_default',
			'do_print_qcReceipt_'.$ip_addr,
			'printer_ip_qcReceipt_'.$ip_addr,
			'printer_pin_qcReceipt_'.$ip_addr,
			'printer_tipe_qcReceipt_'.$ip_addr,
			
			'printer_ip_kitchenReceipt_default',
			'do_print_kitchenReceipt_'.$ip_addr,
			'printer_ip_kitchenReceipt_'.$ip_addr,
			'printer_pin_kitchenReceipt_'.$ip_addr,
			'printer_tipe_kitchenReceipt_'.$ip_addr,
			
			'printer_ip_barReceipt_default',
			'do_print_barReceipt_'.$ip_addr,
			'printer_ip_barReceipt_'.$ip_addr,
			'printer_pin_barReceipt_'.$ip_addr,
			'printer_tipe_barReceipt_'.$ip_addr,
			
			'printer_ip_otherReceipt_default',
			'do_print_otherReceipt_'.$ip_addr,
			'printer_ip_otherReceipt_'.$ip_addr,
			'printer_pin_otherReceipt_'.$ip_addr,
			'printer_tipe_otherReceipt_'.$ip_addr
		);
		$get_opt = get_option_value($opt_value);
		
		//Cashier Receipt ----------		
		$cashierReceipt = array(
			'use_local_default_printer'	=> true,
			'printer_ip'	=> '',
			'printer_pin'	=> '',
			'printer_tipe'	=> ''
		);
		
		//$printer_ip_cashierReceipt = $ip_addr.'\\'.$get_opt['printer_ip_cashierReceipt_default'];
		$printer_ip_cashierReceipt = $get_opt['printer_ip_cashierReceipt_default'];
		if(!empty($get_opt['printer_ip_cashierReceipt_'.$ip_addr])){
			$printer_ip_cashierReceipt = $get_opt['printer_ip_cashierReceipt_'.$ip_addr];
			$cashierReceipt['use_local_default_printer'] = false;
		}else{
			$cashierReceipt['use_local_default_printer'] = true;
		}
		
		$cashierReceipt['printer_ip'] = $printer_ip_cashierReceipt;					
		if(!empty($get_opt['printer_pin_cashierReceipt_'.$ip_addr])){
			$cashierReceipt['printer_pin']  = $get_opt['printer_pin_cashierReceipt_'.$ip_addr];
		}
						
		if(!empty($get_opt['printer_tipe_cashierReceipt_'.$ip_addr])){
			$cashierReceipt['printer_tipe']  = $get_opt['printer_tipe_cashierReceipt_'.$ip_addr];
		}
		//-------- Cashier Receipt
		
		//QC Receipt -------		
		$qcReceipt = array(
			'use_local_default_printer'	=> true,
			'print_qcReceipt'	=> '',
			'printer_ip'	=> '',
			'printer_pin'	=> '',
			'printer_tipe'	=> ''
		);
		
		$printer_ip_qcReceipt = $ip_addr.'\\'.$get_opt['printer_ip_qcReceipt_default'];
		if(!empty($get_opt['printer_ip_qcReceipt_'.$ip_addr])){
			$printer_ip_qcReceipt = $get_opt['printer_ip_qcReceipt_'.$ip_addr];
			$qcReceipt['use_local_default_printer'] = false;
		}else{
			$qcReceipt['use_local_default_printer'] = true;
		}
		
		$qcReceipt['printer_ip'] = $printer_ip_qcReceipt;					
		if(!empty($get_opt['printer_pin_qcReceipt_'.$ip_addr])){
			$qcReceipt['printer_pin']  = $get_opt['printer_pin_qcReceipt_'.$ip_addr];
		}				
		if(!empty($get_opt['printer_tipe_qcReceipt_'.$ip_addr])){
			$qcReceipt['printer_tipe']  = $get_opt['printer_tipe_qcReceipt_'.$ip_addr];
		}
		
		if(!empty($get_opt['do_print_qcReceipt_'.$ip_addr])){
			$qcReceipt['print_qcReceipt'] = true;
		}else{
			$qcReceipt['print_qcReceipt'] = false;
		}
		//------- QC Receipt
		
		//Kitchen Receipt -------
		$kitchenReceipt = array(
			'use_local_default_printer'	=> true,
			'print_kitchenReceipt'	=> '',
			'printer_ip'	=> '',
			'printer_pin'	=> '',
			'printer_tipe'	=> ''
		);
		
		$printer_ip_kitchenReceipt = $ip_addr.'\\'.$get_opt['printer_ip_kitchenReceipt_default'];
		if(!empty($get_opt['printer_ip_kitchenReceipt_'.$ip_addr])){
			$printer_ip_kitchenReceipt = $get_opt['printer_ip_kitchenReceipt_'.$ip_addr];
			$kitchenReceipt['use_local_default_printer'] = false;
		}else{
			$kitchenReceipt['use_local_default_printer'] = true;
		}
		
		$kitchenReceipt['printer_ip'] = $printer_ip_kitchenReceipt;					
		if(!empty($get_opt['printer_pin_kitchenReceipt_'.$ip_addr])){
			$kitchenReceipt['printer_pin']  = $get_opt['printer_pin_kitchenReceipt_'.$ip_addr];
		}				
		if(!empty($get_opt['printer_tipe_kitchenReceipt_'.$ip_addr])){
			$kitchenReceipt['printer_tipe']  = $get_opt['printer_tipe_kitchenReceipt_'.$ip_addr];
		}
		if(!empty($get_opt['do_print_kitchenReceipt_'.$ip_addr])){
			$kitchenReceipt['print_kitchenReceipt'] = true;
		}else{
			$kitchenReceipt['print_kitchenReceipt'] = false;
		}
		//------- Kitchen Receipt
		
		//Bar Receipt -------
		$barReceipt = array(
			'use_local_default_printer'	=> true,
			'print_barReceipt'	=> '',
			'printer_ip'	=> '',
			'printer_pin'	=> '',
			'printer_tipe'	=> ''
		);
		
		$printer_ip_barReceipt = $ip_addr.'\\'.$get_opt['printer_ip_barReceipt_default'];
		if(!empty($get_opt['printer_ip_barReceipt_'.$ip_addr])){
			$printer_ip_barReceipt = $get_opt['printer_ip_barReceipt_'.$ip_addr];
			$barReceipt['use_local_default_printer'] = false;
		}else{
			$barReceipt['use_local_default_printer'] = true;
		}
		
		$barReceipt['printer_ip'] = $printer_ip_barReceipt;					
		if(!empty($get_opt['printer_pin_barReceipt_'.$ip_addr])){
			$barReceipt['printer_pin']  = $get_opt['printer_pin_barReceipt_'.$ip_addr];
		}				
		if(!empty($get_opt['printer_tipe_barReceipt_'.$ip_addr])){
			$barReceipt['printer_tipe']  = $get_opt['printer_tipe_barReceipt_'.$ip_addr];
		}
		
		if(!empty($get_opt['do_print_barReceipt_'.$ip_addr])){
			$barReceipt['print_barReceipt'] = true;
		}else{
			$barReceipt['print_barReceipt'] = false;
		}
		//------- Bar Receipt
		
		//Other Receipt -------
		$otherReceipt = array(
			'use_local_default_printer'	=> true,
			'print_otherReceipt'	=> '',
			'printer_ip'	=> '',
			'printer_pin'	=> '',
			'printer_tipe'	=> ''
		);
		
		$printer_ip_otherReceipt = $ip_addr.'\\'.$get_opt['printer_ip_otherReceipt_default'];
		if(!empty($get_opt['printer_ip_otherReceipt_'.$ip_addr])){
			$printer_ip_otherReceipt = $get_opt['printer_ip_otherReceipt_'.$ip_addr];
			$otherReceipt['use_local_default_printer'] = false;
		}else{
			$otherReceipt['use_local_default_printer'] = true;
		}
		
		$otherReceipt['printer_ip'] = $printer_ip_otherReceipt;					
		if(!empty($get_opt['printer_pin_otherReceipt_'.$ip_addr])){
			$otherReceipt['printer_pin']  = $get_opt['printer_pin_otherReceipt_'.$ip_addr];
		}					
		if(!empty($get_opt['printer_tipe_otherReceipt_'.$ip_addr])){
			$otherReceipt['printer_tipe']  = $get_opt['printer_tipe_otherReceipt_'.$ip_addr];
		}
		
		if(!empty($get_opt['do_print_otherReceipt_'.$ip_addr])){
			$otherReceipt['print_otherReceipt'] = true;
		}else{
			$otherReceipt['print_otherReceipt'] = false;
		}
		//------- Bar Receipt
		
		$returnData = array(
			'success' => true,
			'IP'	=> $ip_addr,
			'cashierReceipt' => $cashierReceipt,
			'qcReceipt' => $qcReceipt,
			'kitchenReceipt' => $kitchenReceipt,
			'barReceipt' => $barReceipt,
			'otherReceipt' => $otherReceipt,
		);
		
		die(json_encode($returnData));
	}
	
	public function loadingSettingRetail(){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$opt_value = array(
			'printer_ip_cashierReceipt_default',
			'printer_ip_cashierReceipt_'.$ip_addr,
			'printer_pin_cashierReceipt_'.$ip_addr,
			'printer_tipe_cashierReceipt_'.$ip_addr,
			'local_printer_cashierReceipt_'.$ip_addr
		);
		$get_opt = get_option_value($opt_value);
		
		//Cashier Receipt ----------		
		$cashierReceipt = array(
			'use_local_default_printer'	=> true,
			'printer_ip'	=> '',
			'printer_pin'	=> '',
			'printer_tipe'	=> ''
		);
		
		//$printer_ip_cashierReceipt = $ip_addr.'\\'.$get_opt['printer_ip_cashierReceipt_default'];
		$printer_ip_cashierReceipt = $get_opt['printer_ip_cashierReceipt_default'];
		if(!empty($get_opt['printer_ip_cashierReceipt_'.$ip_addr])){
			$printer_ip_cashierReceipt = $get_opt['printer_ip_cashierReceipt_'.$ip_addr];
		}
		
		$cashierReceipt['printer_ip'] = $printer_ip_cashierReceipt;					
		if(!empty($get_opt['printer_pin_cashierReceipt_'.$ip_addr])){
			$cashierReceipt['printer_pin']  = $get_opt['printer_pin_cashierReceipt_'.$ip_addr];
		}
						
		if(!empty($get_opt['printer_tipe_cashierReceipt_'.$ip_addr])){
			$cashierReceipt['printer_tipe']  = $get_opt['printer_tipe_cashierReceipt_'.$ip_addr];
		}
		
		$cashierReceipt['use_local_default_printer'] = false;		
		if(!empty($get_opt['local_printer_cashierReceipt_'.$ip_addr])){
			$cashierReceipt['use_local_default_printer']  = true;
		}
		//-------- Cashier Receipt
		
		
		$returnData = array(
			'success' => true,
			'IP'	=> $ip_addr,
			'cashierReceipt' => $cashierReceipt
		);
		
		die(json_encode($returnData));
	}
	
	/*SAVE SETTING CASHIER*/
	public function save_settingCashier(){
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		//TIPE
		$printSetting = $this->input->post('printSetting', true);	
		if(empty($printSetting)){
			$printSetting = 'cashierReceipt';
		}
		
		//use_local_default_printer		
		$use_local_default_printer = $this->input->post('use_local_default_printer', true);	
		
		//printer_receipt		
		$printer_receipt = $this->input->post('print_'.$printSetting, true);
		
		//printer_ip		
		$printer_ip = $this->input->post('printer_ip', true);
		
		//printer_tipe
		$printer_tipe = $this->input->post('printer_tipe', true);
		
		//printer_pin
		$printer_pin = $this->input->post('printer_pin', true);
		
		$r = array('success' => false);
		
		$data_options = array(
			'do_print_'.$printSetting.'_'.$ip_addr => 0,
			'printer_ip_'.$printSetting.'_'.$ip_addr => '',
			'printer_pin_'.$printSetting.'_'.$ip_addr => '',
			'printer_tipe_'.$printSetting.'_'.$ip_addr => ''
		);
		
		
		if($use_local_default_printer == false OR empty($use_local_default_printer)){
			if(empty($printer_ip) OR empty($printer_pin)){
				$r = array('success' => false, 'info' => '<br/>Printer IP and Tipe Print should be selected!');
				die(json_encode($r));
			}else{
				$data_options = array(
					'do_print_'.$printSetting.'_'.$ip_addr => 0,
					'printer_ip_'.$printSetting.'_'.$ip_addr => $printer_ip,
					'printer_pin_'.$printSetting.'_'.$ip_addr => $printer_pin,
					'printer_tipe_'.$printSetting.'_'.$ip_addr => $printer_tipe
				);
			}
		}
		
		if(!empty($printer_receipt)){
			$data_options['do_print_'.$printSetting.'_'.$ip_addr] = $printer_receipt;
		}
		
		//UPDATE OPTIONS
		$update_option = update_option($data_options);
		if($update_option){
			$r = array('success' => true, 'use_local_default_printer' => $use_local_default_printer, 'dt' => $data_options);
		}
		
		die(json_encode($r));
	}
	
	/*SAVE SETTING CASHIER*/
	public function save_settingCashierRetail(){
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		//TIPE
		$printSetting = $this->input->post('printSetting', true);	
		if(empty($printSetting)){
			$printSetting = 'cashierReceipt';
		}
		
		//use_local_default_printer		
		$use_local_default_printer = $this->input->post('use_local_default_printer', true);	
		
		//printer_receipt		
		$printer_receipt = $this->input->post('print_'.$printSetting, true);
		
		//printer_ip		
		$printer_ip = $this->input->post('printer_ip', true);
		
		//printer_tipe
		$printer_tipe = $this->input->post('printer_tipe', true);
		
		//printer_pin
		$printer_pin = $this->input->post('printer_pin', true);
		
		$r = array('success' => false);
		
		$data_options = array(
			'local_printer_'.$printSetting.'_'.$ip_addr => 0,
			'do_print_'.$printSetting.'_'.$ip_addr => 0,
			'printer_ip_'.$printSetting.'_'.$ip_addr => $printer_ip,
			'printer_pin_'.$printSetting.'_'.$ip_addr => $printer_pin,
			'printer_tipe_'.$printSetting.'_'.$ip_addr => $printer_tipe
		);
		
		if(!empty($printer_receipt)){
			$data_options['do_print_'.$printSetting.'_'.$ip_addr] = 1;
		}
		if(!empty($use_local_default_printer)){
			$data_options['local_printer_'.$printSetting.'_'.$ip_addr] = $use_local_default_printer;
		}
		
		//UPDATE OPTIONS
		$update_option = update_option($data_options);
		if($update_option){
			$r = array('success' => true, 'use_local_default_printer' => $use_local_default_printer, 'dt' => $data_options);
		}
		
		die(json_encode($r));
	}
	
	/*SAVE SETTING CASHIER RECEIPT*/
	public function save_cashierReceiptSetup(){
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		
		$opt_value = array(
			'wepos_tipe'
		);
		
		$get_opt = get_option_value($opt_value);
		
		$wepos_tipe = 'cafe';
		if(!empty($get_opt['wepos_tipe'])){
			$wepos_tipe = $get_opt['wepos_tipe'];
		}
		
		//cashierReceipt_layout		
		$cashierReceipt_layout = $this->input->post('cashierReceipt_layout', true);	
		$cashierReceipt_layout_footer = $this->input->post('cashierReceipt_layout_footer', true);	
		$cashierReceipt_invoice_layout = $this->input->post('cashierReceipt_invoice_layout', true);	
		$cashierReceipt_settlement_layout = $this->input->post('cashierReceipt_settlement_layout', true);	
		$cashierReceipt_openclose_layout = $this->input->post('cashierReceipt_openclose_layout', true);	
		$cashierReceipt_bagihasil_layout = $this->input->post('cashierReceipt_bagihasil_layout', true);	
		$qcReceipt_layout = $this->input->post('qcReceipt_layout', true);	
		$kitchenReceipt_layout = $this->input->post('kitchenReceipt_layout', true);	
		$barReceipt_layout = $this->input->post('barReceipt_layout', true);	
		$otherReceipt_layout = $this->input->post('otherReceipt_layout', true);	
		
		$r = array('success' => false);
		
		if($wepos_tipe != 'retail'){
			$data_options = array(
				'cashierReceipt_layout' => $cashierReceipt_layout,
				'cashierReceipt_layout_footer' => $cashierReceipt_layout_footer,
				'cashierReceipt_invoice_layout' => $cashierReceipt_invoice_layout,
				'cashierReceipt_settlement_layout' => $cashierReceipt_settlement_layout,
				'cashierReceipt_openclose_layout' => $cashierReceipt_openclose_layout,
				'cashierReceipt_bagihasil_layout' => $cashierReceipt_bagihasil_layout,
				'qcReceipt_layout' => $qcReceipt_layout,
				'kitchenReceipt_layout' => $kitchenReceipt_layout,
				'barReceipt_layout' => $barReceipt_layout,
				'otherReceipt_layout' => $otherReceipt_layout
			);
			
			//UPDATE OPTIONS
			$update_option = update_option($data_options);
			if($update_option){
				$r = array('success' => true, 
					"cashierReceipt_layout" => $cashierReceipt_layout, 
					"cashierReceipt_layout_footer" => $cashierReceipt_layout_footer, 
					"cashierReceipt_invoice_layout" => $cashierReceipt_invoice_layout, 
					"cashierReceipt_settlement_layout" => $cashierReceipt_settlement_layout, 
					"cashierReceipt_openclose_layout" => $cashierReceipt_openclose_layout, 
					"cashierReceipt_bagihasil_layout" => $cashierReceipt_bagihasil_layout, 
					"qcReceipt_layout" => $qcReceipt_layout, 
					"kitchenReceipt_layout" => $kitchenReceipt_layout, 
					"barReceipt_layout" => $barReceipt_layout, 
					"otherReceipt_layout" => $otherReceipt_layout
				);
			}
		}else{
			$data_options = array(
				'cashierReceipt_layout' => $cashierReceipt_layout,
				'cashierReceipt_layout_footer' => $cashierReceipt_layout_footer,
				'cashierReceipt_invoice_layout' => $cashierReceipt_invoice_layout,
				'cashierReceipt_settlement_layout' => $cashierReceipt_settlement_layout,
				'cashierReceipt_openclose_layout' => $cashierReceipt_openclose_layout,
				'cashierReceipt_bagihasil_layout' => $cashierReceipt_bagihasil_layout
			);
			
			//UPDATE OPTIONS
			$update_option = update_option($data_options);
			if($update_option){
				$r = array('success' => true, 
					"cashierReceipt_layout" => $cashierReceipt_layout, 
					"cashierReceipt_layout_footer" => $cashierReceipt_layout_footer, 
					"cashierReceipt_invoice_layout" => $cashierReceipt_invoice_layout, 
					"cashierReceipt_settlement_layout" => $cashierReceipt_settlement_layout, 
					"cashierReceipt_openclose_layout" => $cashierReceipt_openclose_layout, 
					"cashierReceipt_bagihasil_layout" => $cashierReceipt_bagihasil_layout
				);
			}
		}
		
		die(json_encode($r));
	}
		
	public function loadingCashierReceiptSetup(){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$opt_val = array(
			'cashierReceipt_layout', 
			'cashierReceipt_layout_footer', 
			'cashierReceipt_invoice_layout', 
			'cashierReceipt_settlement_layout', 
			'cashierReceipt_openclose_layout', 
			'cashierReceipt_bagihasil_layout', 
			'qcReceipt_layout', 
			'kitchenReceipt_layout', 
			'barReceipt_layout', 
			'otherReceipt_layout'
		);
		
		$get_opt = get_option_value($opt_val);
		
		$retValue = array('success' => true);
					
		if(!empty($get_opt['cashierReceipt_layout'])){
			$retValue['cashierReceipt_layout']  = $get_opt['cashierReceipt_layout'];
		}
		if(!empty($get_opt['cashierReceipt_layout_footer'])){
			$retValue['cashierReceipt_layout_footer']  = $get_opt['cashierReceipt_layout_footer'];
		}
		if(!empty($get_opt['cashierReceipt_invoice_layout'])){
			$retValue['cashierReceipt_invoice_layout']  = $get_opt['cashierReceipt_invoice_layout'];
		}
		if(!empty($get_opt['cashierReceipt_settlement_layout'])){
			$retValue['cashierReceipt_settlement_layout']  = $get_opt['cashierReceipt_settlement_layout'];
		}
		if(!empty($get_opt['cashierReceipt_openclose_layout'])){
			$retValue['cashierReceipt_openclose_layout']  = $get_opt['cashierReceipt_openclose_layout'];
		}
		if(!empty($get_opt['cashierReceipt_bagihasil_layout'])){
			$retValue['cashierReceipt_bagihasil_layout']  = $get_opt['cashierReceipt_bagihasil_layout'];
		}
		if(!empty($get_opt['qcReceipt_layout'])){
			$retValue['qcReceipt_layout']  = $get_opt['qcReceipt_layout'];
		}
		if(!empty($get_opt['kitchenReceipt_layout'])){
			$retValue['kitchenReceipt_layout']  = $get_opt['kitchenReceipt_layout'];
		}
		if(!empty($get_opt['barReceipt_layout'])){
			$retValue['barReceipt_layout']  = $get_opt['barReceipt_layout'];
		}
		if(!empty($get_opt['otherReceipt_layout'])){
			$retValue['otherReceipt_layout']  = $get_opt['otherReceipt_layout'];
		}
				
		die(json_encode($retValue));
	}
		
	public function updateTable(){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$this->table = $this->prefix.'billing';		
		$this->table_inv = $this->prefix.'table_inventory';		
		$billing_id = $this->input->post('billing_id', true);
		$table_id = $this->input->post('table_id', true);
		
		$r = array('success' => false);
		
		if(empty($billing_id) OR empty($table_id)){
			$r = array('success' => false, 'info' => 'Please Select Table!');
		}else{
			
			$billingData = array();
			$this->db->where("id", $billing_id);
			$getBilling = $this->db->get($this->table);
			if($getBilling->num_rows() > 0){
				$billingData = $getBilling->row();
			}
			
			//CLOSING DATE
			$var_closing = array(
				'xdate'	=> $billingData->created,
				'xtipe'	=> 'sales'
			);
			$is_closing = is_closing($var_closing);
			if($is_closing){
				$r = array('success' => false, 'info' => 'Sales Date Been Closed!'); 
				die(json_encode($r));
			}
			
			$data_table = array(
				'table_id' => $table_id
			);
					
			//UPDATE OPTIONS
			$this->db->update($this->table, $data_table, "id = '".$billing_id."'");
			
			
			//INV
			$date_today = date("Y-m-d");
			$date_time_today = date("Y-m-d H:i:s");
			
			//clear inv $billingData->billing_no
			if($billingData->table_id != $table_id){
				$data_table = array(
					'status' => 'available',
					'billing_no' => '',
					'updated' => $date_time_today,
					'updatedby' => $session_user
				);
				$this->db->update($this->table_inv, $data_table, "billing_no = '".$billingData->billing_no."' AND tanggal = '".$date_today."'");
			}
			
			
			//echo '<pre>';
			//print_r($billingData);
			//$r = array('success' => false, 'data' => $billingData );
			//die(json_encode($r));
					
			//UPDATE OPTIONS
			$this->db->where("table_id = '".$table_id."' AND tanggal = '".$date_today."'");
			$get_inv = $this->db->get($this->table_inv);
			if($get_inv->num_rows() > 0){
				$data_table = array(
					'status' => 'booked',
					'billing_no' => $billingData->billing_no,
					'updated' => $date_time_today,
					'updatedby' => $session_user
				);
				$this->db->update($this->table_inv, $data_table, "table_id = '".$table_id."' AND tanggal = '".$date_today."'");
			}else{
				$data_table = array(
					'status' => 'booked',
					'table_id' => $table_id,
					'billing_no' => $billingData->billing_no,
					'tanggal' => $date_today,
					'created' => $date_time_today,
					'createdby' => $session_user,
					'updated' => $date_time_today,
					'updatedby' => $session_user
				);
				$this->db->insert($this->table_inv, $data_table);
			}
			
			$r = array('success' => true );
		}
		
		die(json_encode($r));
	}
		
	public function updateTotalGuest(){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$this->table = $this->prefix.'billing';			
		$billing_id = $this->input->post('billing_id', true);
		$total_guest = $this->input->post('total_guest', true);
		
		$r = array('success' => false);
		
		if(empty($billing_id) OR empty($total_guest)){
			$r = array('success' => false, 'info' => 'Total Guest Cannot Empty!');
		}else{
			
			$billingData = array();
			$this->db->where("id", $billing_id);
			$getBilling = $this->db->get($this->table);
			if($getBilling->num_rows() > 0){
				$billingData = $getBilling->row();
			}
			
			$data_total_guest = array(
				'total_guest' => $total_guest
			);
			
			//CLOSING DATE
			$var_closing = array(
				'xdate'	=> $billingData->created,
				'xtipe'	=> 'sales'
			);
			$is_closing = is_closing($var_closing);
			if($is_closing){
				$r = array('success' => false, 'info' => 'Sales Date Been Closed!'); 
				die(json_encode($r));
			}
					
			//UPDATE OPTIONS
			$this->db->update($this->table, $data_total_guest, "id = '".$billing_id."'");
			
			$r = array('success' => true );
			
			$getBilling = $this->getBilling($billing_id);	
			$update_billing = $this->calculateBilling($billing_id);
			if(!empty($update_billing)){
		
				$getBilling->total_billing = $update_billing['total_billing'];
				$getBilling->tax_total = $update_billing['tax_total'];
				$getBilling->service_total = $update_billing['service_total'];
				$getBilling->discount_total = $update_billing['discount_total'];
				$getBilling->grand_total = $update_billing['grand_total'];
				$getBilling->total_pembulatan = $update_billing['total_pembulatan'];
				$getBilling->total_dp = $update_billing['total_dp'];
				$getBilling->compliment_total = $update_billing['compliment_total'];
				$getBilling->compliment_total_tax_service = $update_billing['compliment_total_tax_service'];
				$getBilling->total_billing_display = $update_billing['total_billing_display'];
				
				$getBilling->total_billing_show =  priceFormat($getBilling->total_billing);
				$getBilling->tax_total_show =  priceFormat($getBilling->tax_total);
				$getBilling->service_total_show =  priceFormat($getBilling->service_total);
				$getBilling->discount_total_show =  priceFormat($getBilling->discount_total);
				$getBilling->grand_total_show =  priceFormat($getBilling->grand_total);
				$getBilling->total_pembulatan_show =  priceFormat($getBilling->total_pembulatan);
				$getBilling->total_dp_show =  priceFormat($getBilling->total_dp);
				$getBilling->compliment_total_show =  priceFormat($getBilling->compliment_total);
				$getBilling->compliment_total_tax_service_show =  priceFormat($getBilling->compliment_total_tax_service);
				
			}
			
			$r['billingData'] = $getBilling;
		}
		
		die(json_encode($r));
	}
		
	public function updatePPN(){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$this->table = $this->prefix.'billing';		
		$this->table_billing_detail = $this->prefix.'billing_detail';		
		$billing_id = $this->input->post('billing_id', true);
		$tax_percentage = $this->input->post('tax_percentage', true);
		$tax_total = $this->input->post('tax_total', true);
		
		$r = array('success' => false);
		
		if(empty($billing_id)){
			$r = array('success' => false, 'info' => 'Billing not found!');
		}else{
			
			//check billing
			$billingData = array();
			if(!empty($billing_id)){
				$this->db->select("created, tax_percentage, include_tax, include_service,
				tax_percentage, service_percentage, takeaway_no_tax, takeaway_no_service, is_compliment");
				$this->db->from($this->table);
				$this->db->where("id", $billing_id);
				$get_billing = $this->db->get();
				if($get_billing->num_rows() > 0){
					$billingData = $get_billing->row();
				}
			}
			
			//CLOSING DATE
			$var_closing = array(
				'xdate'	=> $billingData->created,
				'xtipe'	=> 'sales'
			);
			$is_closing = is_closing($var_closing);
			if($is_closing){
				$r = array('success' => false, 'info' => 'Sales Date Been Closed!'); 
				die(json_encode($r));
			}
			
			
			$get_opt_var = array('diskon_sebelum_pajak_service');
			$get_opt = get_option_value($get_opt_var);
			
			$diskon_sebelum_pajak_service = 0;
			if(!empty($get_opt['diskon_sebelum_pajak_service'])){
				$diskon_sebelum_pajak_service = $get_opt['diskon_sebelum_pajak_service'];
			}
			
			//UPDATE DETAIL
			$billingData->tax_percentage = $tax_percentage;
			$tax_total_all = 0;
			$all_detail_update = array();
			$this->db->select("id, product_price, order_qty, is_takeaway, is_compliment,
			include_tax, include_service, tax_percentage, service_percentage, discount_total");
			$this->db->from($this->table_billing_detail);
			$this->db->where('billing_id', $billing_id);
			$this->db->where('is_deleted', 0);
			$get_detail = $this->db->get();
			if($get_detail->num_rows() > 0){
				foreach($get_detail->result() as $dt){
					
					$product_price = $dt->product_price;
					$order_qty = $dt->order_qty;
					$is_takeaway = $dt->is_takeaway;
					$is_compliment = $dt->is_compliment;
					
					//TAX, SERVICE, TAKE AWAY & COMPLIMENT
					$include_tax = $dt->include_tax;
					$include_service = $dt->include_service;
					$tax_percentage = $dt->tax_percentage;
					$service_percentage = $dt->service_percentage;
					$discount_total = $dt->discount_total;
					//$include_tax = $billingData->include_tax;
					//$include_service = $billingData->include_service;
					//$tax_percentage = $billingData->tax_percentage;
					//$service_percentage = $billingData->tax_percentage;
					$takeaway_no_tax = $billingData->takeaway_no_tax;
					$takeaway_no_service = $billingData->takeaway_no_service;
					$billing_is_compliment = $billingData->is_compliment;
					
					//BALANCING OLD DATA
					if($is_compliment == 1){
						if($tax_percentage == '0.00' AND !empty($billingData->tax_percentage)){
							$tax_percentage = $billingData->tax_percentage;
						}
						if($service_percentage == '0.00' AND !empty($billingData->service_percentage)){
							$service_percentage = $billingData->service_percentage;
						}
					}
					
					$tax_total = 0;
					$service_total = 0;
					$product_price_real = 0;
					if(!empty($include_tax) OR !empty($include_service)){
						if(!empty($include_tax) AND !empty($include_service)){
							$all_percentage = 100 + $tax_percentage + $service_percentage;
							$one_percent = $product_price / $all_percentage;
							$one_percent_order_qty = $order_qty * $one_percent;
							$tax_total = priceFormat($one_percent_order_qty * $tax_percentage, 0, ".", "");
							$service_total = priceFormat($one_percent_order_qty * $service_percentage, 0, ".", "");
							$product_price_real = $product_price - ($tax_total + $service_total);
							
							//re-calculate tax service
							if($diskon_sebelum_pajak_service == 1){
								$product_price_real_disc = $product_price_real-$discount_total;
								$tax_total = priceFormat($product_price_real_disc * ($tax_percentage/100), 0, ".", "");
								$service_total = priceFormat($product_price_real_disc * ($service_percentage/100), 0, ".", "");
							}
							
						}else{
							if(!empty($include_tax)){
								$all_percentage = 100 + $tax_percentage;
								$one_percent = $product_price / $all_percentage;
								$one_percent_order_qty = $order_qty * $one_percent;
								$tax_total = priceFormat($one_percent_order_qty * $tax_percentage, 0, ".", "");
								$product_price_real = $product_price - ($tax_total);
								
								//re-calculate tax service
								if($diskon_sebelum_pajak_service == 1){
									$product_price_real_disc = $product_price_real-$discount_total;
									$tax_total = priceFormat($product_price_real_disc * ($tax_percentage/100), 0, ".", "");
								}
								
							}
							
							if(!empty($include_service)){
								$all_percentage = 100 + $service_percentage;
								$one_percent = $product_price / $all_percentage;
								$one_percent_order_qty = $order_qty * $one_percent;
								$service_total = priceFormat($one_percent_order_qty * $service_percentage, 0, ".", "");
								$product_price_real = $product_price - ($service_total);
								
								//re-calculate tax service
								if($diskon_sebelum_pajak_service == 1){
									$product_price_real_disc = $product_price_real-$discount_total;
									$service_total = priceFormat($product_price_real_disc * ($service_percentage/100), 0, ".", "");
								}
								
							}
							
						}
					}else
					{
						$product_price_real = $product_price;
						$tax_percent = $tax_percentage/100;
						$service_percent = $service_percentage/100;
						$product_price_order_qty = $order_qty * $product_price;
						$tax_total = priceFormat($product_price_order_qty * $tax_percent, 0, ".", "");
						$service_total = priceFormat($product_price_order_qty * $service_percent, 0, ".", "");
						
						//re-calculate tax service
						if($diskon_sebelum_pajak_service == 1){
							$product_price_real_disc = $product_price_real-$discount_total;
							$tax_total = priceFormat($product_price_real_disc * ($tax_percentage/100), 0, ".", "");
							$service_total = priceFormat($product_price_real_disc * ($service_percentage/100), 0, ".", "");
						}
						
					}
					
					if(empty($is_takeaway)){
						$is_takeaway = 0;
					}else{
						$is_takeaway = 1;
						
						//get takeaway config tas service default
						if(!empty($takeaway_no_tax)){
							$tax_percentage = 0;
							$tax_total = 0;
						}
						
						if(!empty($takeaway_no_service)){
							$service_percentage = 0;
							$service_total = 0;
						}
						
					}
					
					if(empty($is_compliment)){
						$is_compliment = 0;
					}else{
						$is_compliment = 1;
						
						if(!empty($include_tax) OR !empty($include_service)){
							$tax_total = 0;
							$service_total = 0;
							//$tax_percentage = 0;
							//$service_percentage = 0;
						}else{
							$tax_percentage = 0;
							$tax_total = 0;
							$service_percentage = 0;
							$service_total = 0;
						}
						
					
					}
					
					//BILLING COMPLIMENT
					if(empty($billing_is_compliment)){
						//$is_compliment = 0;
					}else{
						$is_compliment = 1;
						
						if(!empty($include_tax) OR !empty($include_service)){
							$tax_total = 0;
							$service_total = 0;
							//$tax_percentage = 0;
							//$service_percentage = 0;
						}else{
							$tax_percentage = 0;
							$tax_total = 0;
							$service_percentage = 0;
							$service_total = 0;
						}
					
					}
					
					$tax_total_all += $tax_total;
					$all_detail_update[] = array(
						'id'			=> $dt->id,
						'tax_total'			=> $tax_total,
						'tax_percentage'	=> $tax_percentage
					);
				}
				
				//UPDATE DETAIL
				if(!empty($all_detail_update)){
					$this->db->update_batch($this->table_billing_detail,$all_detail_update,"id");
				}
				
			}
			
			$data_ppn = array(
				'tax_percentage' => $billingData->tax_percentage,
				'tax_total' => $tax_total_all
			);
					
			//UPDATE OPTIONS
			$this->db->update($this->table, $data_ppn, "id = '".$billing_id."'");
			$r = array('success' => true, 'tax_total' => $tax_total_all);
			
			$getBilling = $this->getBilling($billing_id);	
			$update_billing = $this->calculateBilling($billing_id);
			if(!empty($update_billing)){
		
				$getBilling->total_billing = $update_billing['total_billing'];
				$getBilling->tax_total = $update_billing['tax_total'];
				$getBilling->service_total = $update_billing['service_total'];
				$getBilling->discount_total = $update_billing['discount_total'];
				$getBilling->grand_total = $update_billing['grand_total'];
				$getBilling->total_pembulatan = $update_billing['total_pembulatan'];
				$getBilling->total_dp = $update_billing['total_dp'];
				$getBilling->compliment_total = $update_billing['compliment_total'];
				$getBilling->compliment_total_tax_service = $update_billing['compliment_total_tax_service'];
				$getBilling->total_billing_display = $update_billing['total_billing_display'];
				
				$getBilling->total_billing_show =  priceFormat($getBilling->total_billing);
				$getBilling->tax_total_show =  priceFormat($getBilling->tax_total);
				$getBilling->service_total_show =  priceFormat($getBilling->service_total);
				$getBilling->discount_total_show =  priceFormat($getBilling->discount_total);
				$getBilling->grand_total_show =  priceFormat($getBilling->grand_total);
				$getBilling->total_pembulatan_show =  priceFormat($getBilling->total_pembulatan);
				$getBilling->total_dp_show =  priceFormat($getBilling->total_dp);
				$getBilling->compliment_total_show =  priceFormat($getBilling->compliment_total);
				$getBilling->compliment_total_tax_service_show =  priceFormat($getBilling->compliment_total_tax_service);
				
			}
			
			$r['billingData'] = $getBilling;
			
		}
		
		die(json_encode($r));
	}
		
	public function updateService(){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$this->table = $this->prefix.'billing';		
		$this->table_billing_detail = $this->prefix.'billing_detail';	
		$billing_id = $this->input->post('billing_id', true);
		$service_percentage = $this->input->post('service_percentage', true);
		$service_total = $this->input->post('service_total', true);
		
		$r = array('success' => false);
		
		if(empty($billing_id)){
			$r = array('success' => false, 'info' => 'Billing not found!');
		}else{
			
			
			//check billing
			$billingData = array();
			if(!empty($billing_id)){
				$this->db->select("created, tax_percentage, include_tax, include_service,
				tax_percentage, service_percentage, takeaway_no_tax, takeaway_no_service, is_compliment");
				$this->db->from($this->table);
				$this->db->where("id", $billing_id);
				$get_billing = $this->db->get();
				if($get_billing->num_rows() > 0){
					$billingData = $get_billing->row();
				}
			}
			
			//CLOSING DATE
			$var_closing = array(
				'xdate'	=> $billingData->created,
				'xtipe'	=> 'sales'
			);
			$is_closing = is_closing($var_closing);
			if($is_closing){
				$r = array('success' => false, 'info' => 'Sales Date Been Closed!'); 
				die(json_encode($r));
			}
			
			
			$get_opt_var = array('diskon_sebelum_pajak_service');
			$get_opt = get_option_value($get_opt_var);
			
			$diskon_sebelum_pajak_service = 0;
			if(!empty($get_opt['diskon_sebelum_pajak_service'])){
				$diskon_sebelum_pajak_service = $get_opt['diskon_sebelum_pajak_service'];
			}
			
			//UPDATE DETAIL
			$billingData->service_percentage = $service_percentage;
			$service_total_all = 0;
			$all_detail_update = array();
			$this->db->select("id, product_price, order_qty, is_takeaway, is_compliment, 
				include_tax, include_service, tax_percentage, service_percentage, discount_total");
			$this->db->from($this->table_billing_detail);
			$this->db->where('billing_id', $billing_id);
			$this->db->where('is_deleted', 0);
			$get_detail = $this->db->get();
			if($get_detail->num_rows() > 0){
				foreach($get_detail->result() as $dt){
					
					$product_price = $dt->product_price;
					$order_qty = $dt->order_qty;
					$is_takeaway = $dt->is_takeaway;
					$is_compliment = $dt->is_compliment;
					
					//TAX, SERVICE, TAKE AWAY & COMPLIMENT
					$include_tax = $dt->include_tax;
					$include_service = $dt->include_service;
					$tax_percentage = $dt->tax_percentage;
					$service_percentage = $dt->service_percentage;
					$discount_total = $dt->discount_total;
					//$include_tax = $billingData->include_tax;
					//$include_service = $billingData->include_service;
					//$tax_percentage = $billingData->tax_percentage;
					//$service_percentage = $billingData->service_percentage;
					$takeaway_no_tax = $billingData->takeaway_no_tax;
					$takeaway_no_service = $billingData->takeaway_no_service;
					$billing_is_compliment = $billingData->is_compliment;
					
					//BALANCING OLD DATA
					if($is_compliment == 1){
						if($tax_percentage == '0.00' AND !empty($billingData->tax_percentage)){
							$tax_percentage = $billingData->tax_percentage;
						}
						if($service_percentage == '0.00' AND !empty($billingData->service_percentage)){
							$service_percentage = $billingData->service_percentage;
						}
					}
					
					$tax_total = 0;
					$service_total = 0;
					$product_price_real = 0;
					if(!empty($include_tax) OR !empty($include_service)){
						if(!empty($include_tax) AND !empty($include_service)){
							$all_percentage = 100 + $tax_percentage + $service_percentage;
							$one_percent = $product_price / $all_percentage;
							$one_percent_order_qty = $order_qty * $one_percent;
							$tax_total = priceFormat($one_percent_order_qty * $tax_percentage, 0, ".", "");
							$service_total = priceFormat($one_percent_order_qty * $service_percentage, 0, ".", "");
							$product_price_real = $product_price - ($tax_total + $service_total);
							
							//re-calculate tax service
							if($diskon_sebelum_pajak_service == 1){
								$product_price_real_disc = $product_price_real-$discount_total;
								$tax_total = priceFormat($product_price_real_disc * ($tax_percentage/100), 0, ".", "");
								$service_total = priceFormat($product_price_real_disc * ($service_percentage/100), 0, ".", "");
							}
							
						}else{
							if(!empty($include_tax)){
								$all_percentage = 100 + $tax_percentage;
								$one_percent = $product_price / $all_percentage;
								$one_percent_order_qty = $order_qty * $one_percent;
								$tax_total = priceFormat($one_percent_order_qty * $tax_percentage, 0, ".", "");
								$product_price_real = $product_price - ($tax_total);
								
								//re-calculate tax service
								if($diskon_sebelum_pajak_service == 1){
									$product_price_real_disc = $product_price_real-$discount_total;
									$tax_total = priceFormat($product_price_real_disc * ($tax_percentage/100), 0, ".", "");
								}
								
							}
							
							if(!empty($include_service)){
								$all_percentage = 100 + $service_percentage;
								$one_percent = $product_price / $all_percentage;
								$one_percent_order_qty = $order_qty * $one_percent;
								$service_total = priceFormat($one_percent_order_qty * $service_percentage, 0, ".", "");
								$product_price_real = $product_price - ($service_total);
								
								//re-calculate tax service
								if($diskon_sebelum_pajak_service == 1){
									$product_price_real_disc = $product_price_real-$discount_total;
									$service_total = priceFormat($product_price_real_disc * ($service_percentage/100), 0, ".", "");
								}
								
							}
							
						}
					}else
					{
						$product_price_real = $product_price;
						$tax_percent = $tax_percentage/100;
						$service_percent = $service_percentage/100;
						$product_price_order_qty = $order_qty * $product_price;
						$tax_total = priceFormat($product_price_order_qty * $tax_percent, 0, ".", "");
						$service_total = priceFormat($product_price_order_qty * $service_percent, 0, ".", "");
						
						//re-calculate tax service
						if($diskon_sebelum_pajak_service == 1){
							$product_price_real_disc = $product_price_real-$discount_total;
							$tax_total = priceFormat($product_price_real_disc * ($tax_percentage/100), 0, ".", "");
							$service_total = priceFormat($product_price_real_disc * ($service_percentage/100), 0, ".", "");
						}
						
					}
					
					if(empty($is_takeaway)){
						$is_takeaway = 0;
					}else{
						$is_takeaway = 1;
						
						//get takeaway config tas service default
						if(!empty($takeaway_no_tax)){
							$tax_percentage = 0;
							$tax_total = 0;
						}
						
						if(!empty($takeaway_no_service)){
							$service_percentage = 0;
							$service_total = 0;
						}
						
					}
					
					if(empty($is_compliment)){
						$is_compliment = 0;
					}else{
						$is_compliment = 1;
						
						if(!empty($include_tax) OR !empty($include_service)){
							$tax_total = 0;
							$service_total = 0;
							//$tax_percentage = 0;
							//$service_percentage = 0;
						}else{
							$tax_percentage = 0;
							$tax_total = 0;
							$service_percentage = 0;
							$service_total = 0;
						}
					
					}
					
					//BILLING COMPLIMENT
					if(empty($billing_is_compliment)){
						//$is_compliment = 0;
					}else{
						$is_compliment = 1;
						
						if(!empty($include_tax) OR !empty($include_service)){
							$tax_total = 0;
							$service_total = 0;
							//$tax_percentage = 0;
							//$service_percentage = 0;
						}else{
							$tax_percentage = 0;
							$tax_total = 0;
							$service_percentage = 0;
							$service_total = 0;
						}
					
					}
					
					$service_total_all += $service_total;
					$all_detail_update[] = array(
						'id'			=> $dt->id,
						'service_total'			=> $service_total,
						'service_percentage'	=> $service_percentage
					);
				}
				
				//UPDATE DETAIL
				if(!empty($all_detail_update)){
					$this->db->update_batch($this->table_billing_detail,$all_detail_update,"id");
				}
				
			}
			
			$data_service = array(
				'service_percentage' => $billingData->service_percentage,
				'service_total' => $service_total_all
			);
					
			//UPDATE OPTIONS
			$this->db->update($this->table, $data_service, "id = '".$billing_id."'");
			$r = array('success' => true, 'service_total' => $service_total_all);
			
			$getBilling = $this->getBilling($billing_id);	
			$update_billing = $this->calculateBilling($billing_id);
			if(!empty($update_billing)){
		
				$getBilling->total_billing = $update_billing['total_billing'];
				$getBilling->tax_total = $update_billing['tax_total'];
				$getBilling->service_total = $update_billing['service_total'];
				$getBilling->discount_total = $update_billing['discount_total'];
				$getBilling->grand_total = $update_billing['grand_total'];
				$getBilling->total_pembulatan = $update_billing['total_pembulatan'];
				$getBilling->total_dp = $update_billing['total_dp'];
				$getBilling->compliment_total = $update_billing['compliment_total'];
				$getBilling->compliment_total_tax_service = $update_billing['compliment_total_tax_service'];
				$getBilling->total_billing_display = $update_billing['total_billing_display'];
				
				$getBilling->total_billing_show =  priceFormat($getBilling->total_billing);
				$getBilling->tax_total_show =  priceFormat($getBilling->tax_total);
				$getBilling->service_total_show =  priceFormat($getBilling->service_total);
				$getBilling->discount_total_show =  priceFormat($getBilling->discount_total);
				$getBilling->grand_total_show =  priceFormat($getBilling->grand_total);
				$getBilling->total_pembulatan_show =  priceFormat($getBilling->total_pembulatan);
				$getBilling->compliment_total_show =  priceFormat($getBilling->compliment_total);
				$getBilling->compliment_total_tax_service_show =  priceFormat($getBilling->compliment_total_tax_service);
				
			}
			
			$r['billingData'] = $getBilling;
			
		}
		
		die(json_encode($r));
	}
		
	public function updateDP(){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$this->table = $this->prefix.'billing';		
		$billing_id = $this->input->post('billing_id', true);
		$total_dp = $this->input->post('total_dp', true);
		
		$r = array('success' => false);
		
		if(empty($billing_id)){
			$r = array('success' => false, 'info' => 'Billing not found!');
		}else{
			
			//check billing
			$billingData = array();
			if(!empty($billing_id)){
				$this->db->select("created");
				$this->db->from($this->table);
				$this->db->where("id", $billing_id);
				$get_billing = $this->db->get();
				if($get_billing->num_rows() > 0){
					$billingData = $get_billing->row();
				}
			}
			
			//CLOSING DATE
			$var_closing = array(
				'xdate'	=> $billingData->created,
				'xtipe'	=> 'sales'
			);
			$is_closing = is_closing($var_closing);
			if($is_closing){
				$r = array('success' => false, 'info' => 'Sales Date Been Closed!'); 
				die(json_encode($r));
			}
			
			$data_service = array(
				'total_dp' => $total_dp
			);
			
					
			//UPDATE OPTIONS
			$this->db->update($this->table, $data_service, "id = '".$billing_id."'");
			$r = array('success' => true );
			
			$getBilling = $this->getBilling($billing_id);	
			$update_billing = $this->calculateBilling($billing_id);
			if(!empty($update_billing)){
		
				$getBilling->total_billing = $update_billing['total_billing'];
				$getBilling->tax_total = $update_billing['tax_total'];
				$getBilling->service_total = $update_billing['service_total'];
				$getBilling->discount_total = $update_billing['discount_total'];
				$getBilling->grand_total = $update_billing['grand_total'];
				$getBilling->total_pembulatan = $update_billing['total_pembulatan'];
				$getBilling->total_dp = $update_billing['total_dp'];
				$getBilling->compliment_total = $update_billing['compliment_total'];
				$getBilling->compliment_total_tax_service = $update_billing['compliment_total_tax_service'];
				$getBilling->total_billing_display = $update_billing['total_billing_display'];
				
				$getBilling->total_billing_show =  priceFormat($getBilling->total_billing);
				$getBilling->tax_total_show =  priceFormat($getBilling->tax_total);
				$getBilling->service_total_show =  priceFormat($getBilling->service_total);
				$getBilling->discount_total_show =  priceFormat($getBilling->discount_total);
				$getBilling->grand_total_show =  priceFormat($getBilling->grand_total);
				$getBilling->total_pembulatan_show =  priceFormat($getBilling->total_pembulatan);
				$getBilling->total_dp_show =  priceFormat($getBilling->total_dp);
				$getBilling->compliment_total_show =  priceFormat($getBilling->compliment_total);
				$getBilling->compliment_total_tax_service_show =  priceFormat($getBilling->compliment_total_tax_service);
				
			}
			
			$r['billingData'] = $getBilling;
			
		}
		
		die(json_encode($r));
	}
		
	public function updateDiscount(){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$get_opt_var = array('diskon_sebelum_pajak_service');
		$get_opt = get_option_value($get_opt_var);
		
		$diskon_sebelum_pajak_service = 0;
		if(!empty($get_opt['diskon_sebelum_pajak_service'])){
			$diskon_sebelum_pajak_service = $get_opt['diskon_sebelum_pajak_service'];
		}
		
		$this->table = $this->prefix.'billing';		
		$this->table_detail = $this->prefix.'billing_detail';		
		$this->table_discount = $this->prefix.'discount';		
		$this->table_discount_product = $this->prefix.'discount_product';		
		$this->table_discount_voucher = $this->prefix.'discount_voucher';		
		$billing_id = $this->input->post('billing_id', true);
		$discount_id = $this->input->post('discount_id', true);
		$discount_notes = $this->input->post('discount_notes', true);
		$discount_percentage = $this->input->post('discount_percentage', true);
		$discount_price = $this->input->post('discount_price', true);
		$discount_total = $this->input->post('discount_total', true);
		$discount_perbilling = $this->input->post('discount_perbilling', true);
		$voucher_no = $this->input->post('voucher_no', true);
		$detail_id = $this->input->post('detail_id', true);
		$is_sistem_tawar = $this->input->post('is_sistem_tawar', true);
		
		//check billing
		$billingData = array();
		if(!empty($billing_id)){
			$this->db->select("total_billing, created, include_tax, include_service, tax_percentage, service_percentage,
						takeaway_no_tax, takeaway_no_service, is_compliment, billing_no");
			$this->db->from($this->table);
			$this->db->where("id", $billing_id);
			$get_billing = $this->db->get();
			if($get_billing->num_rows() > 0){
				$billingData = $get_billing->row();
			}
		}
			
		//CLOSING DATE
		$var_closing = array(
			'xdate'	=> $billingData->created,
			'xtipe'	=> 'sales'
		);
		$is_closing = is_closing($var_closing);
		if($is_closing){
			$r = array('success' => false, 'info' => 'Sales Date Been Closed!'); 
			die(json_encode($r));
		}
		
		//CHECK DISCOUNT
		$data_diskon = array();
		$data_diskon_product = array();
		$allow_diskon_product = array();
		if(!empty($discount_id)){
			
			$this->db->select("*");
			$this->db->from($this->table_discount);
			$this->db->where("id", $discount_id);
			$get_diskon = $this->db->get();
			if($get_diskon->num_rows() > 0){
				$data_diskon = $get_diskon->row();
			}
			
			$this->db->select("product_id");
			$this->db->from($this->table_discount_product);
			$this->db->where("discount_id", $discount_id);
			$get_diskon_product = $this->db->get();
			if($get_diskon_product->num_rows() > 0){
				foreach($get_diskon_product->result() as $dt){
					$data_diskon_product[] = $dt;
					if(!in_array($dt->product_id, $allow_diskon_product)){
						$allow_diskon_product[] = $dt->product_id;
					}
				}
			}
			
		}
		
		$r = array('success' => false);
		
		if(empty($billing_id)){
			$r = array('success' => false, 'info' => 'Billing not found!');
		}else{
			
			//CLEAR DETAIL
			$clear_detail = array(
				'discount_id' 			=> 0,
				'discount_notes' 		=> '',
				'discount_percentage' 	=> 0,
				'discount_price' 		=> 0,
				'discount_total' 		=> 0
			);
			
			//BUYGET
			$this->db->update($this->table_detail, $clear_detail, "billing_id = ".$billing_id." AND (is_promo = 0 AND is_buyget = 0 AND free_item = 0)");
			
			$update_detail = array();
			$today_in_no = date("N");
			
			if(!empty($detail_id) OR ($discount_perbilling == 1 AND !empty($billing_id))){
				
				$discount_total = 0;
				//get all detail
				$this->db->select("id, product_price, order_qty, promo_id, is_promo, is_takeaway, is_compliment, product_id,
				include_tax, include_service, tax_percentage, service_percentage, free_item, ref_order_id, is_buyget");
				$this->db->from($this->table_detail);
				
				if(!empty($detail_id)){
					$this->db->where("id IN (".$detail_id.")");
				}
				
				if($discount_perbilling == 1 AND !empty($billing_id)){
					$this->db->where("billing_id IN (".$billing_id.")");
				}
				
				$this->db->where("is_deleted = 0");
				$this->db->where("(is_promo = 0)");
				//AND is_buyget = 0 AND free_item = 0
				
				$get_all_detail = $this->db->get();
				if($get_all_detail->num_rows() > 0){
					
					//GET BUYGET QTY FREE = 0
					$buyget_not_used = array();
					$buyget_used = array();
					$get_all_detail_data = $get_all_detail->result_array();
					foreach($get_all_detail_data as $key => $s){
						
						if($s['free_item'] == 1 AND !empty($s['ref_order_id'])){
							if($s['order_qty'] == 0){
								if(empty($buyget_not_used[$s['ref_order_id']])){
									$buyget_not_used[$s['ref_order_id']] = 1;
								}
							}else{
								if(empty($buyget_used[$s['ref_order_id']])){
									$buyget_used[$s['ref_order_id']] = 1;
								}
							}
							
						}
						
						if($s['order_qty'] == 0 OR $s['free_item'] == 1){
							unset($get_all_detail_data[$key]);
						}
						
					}
			
					foreach($get_all_detail_data as $dt){
						
						$dt = (object) $dt;
						
						$product_price = $dt->product_price;
						$is_compliment = $dt->is_compliment;
						
						//TAX, SERVICE, TAKE AWAY & COMPLIMENT
						$include_tax = $dt->include_tax;
						$include_service = $dt->include_service;
						$tax_percentage = $dt->tax_percentage;
						$service_percentage = $dt->service_percentage;
						//$include_tax = $billingData->include_tax;
						//$include_service = $billingData->include_service;
						//$tax_percentage = $billingData->tax_percentage;
						//$service_percentage = $billingData->service_percentage;
						$takeaway_no_tax = $billingData->takeaway_no_tax;
						$takeaway_no_service = $billingData->takeaway_no_service;
						$billing_is_compliment = $billingData->is_compliment;
						
						//BALANCING OLD DATA
						if($is_compliment == 1){
							if($tax_percentage == '0.00' AND !empty($billingData->tax_percentage)){
								$tax_percentage = $billingData->tax_percentage;
							}
							if($service_percentage == '0.00' AND !empty($billingData->service_percentage)){
								$service_percentage = $billingData->service_percentage;
							}
						}
						
						$tax_total = 0;
						$service_total = 0;
						$product_price_real = 0;
						if(!empty($include_tax) OR !empty($include_service)){
							if(!empty($include_tax) AND !empty($include_service)){
								$all_percentage = 100 + $tax_percentage + $service_percentage;
								$one_percent = $product_price / $all_percentage;
								//$one_percent_order_qty = $order_qty * $one_percent;
								$tax_total = priceFormat($one_percent * $tax_percentage, 0, ".", "");
								$service_total = priceFormat($one_percent * $service_percentage, 0, ".", "");
								$product_price_real = $product_price - ($tax_total + $service_total);
							}else{
								if(!empty($include_tax)){
									$all_percentage = 100 + $tax_percentage;
									$one_percent = $product_price / $all_percentage;
									//$one_percent_order_qty = $order_qty * $one_percent;
									$tax_total = priceFormat($one_percent * $tax_percentage, 0, ".", "");
									$product_price_real = $product_price - ($tax_total);
								}
								
								if(!empty($include_service)){
									$all_percentage = 100 + $service_percentage;
									$one_percent = $product_price / $all_percentage;
									//$one_percent_order_qty = $order_qty * $one_percent;
									$service_total = priceFormat($one_percent * $service_percentage, 0, ".", "");
									$product_price_real = $product_price - ($service_total);
								}
								
							}
						}else
						{
							$product_price_real = $product_price;
							$tax_percent = $tax_percentage/100;
							$service_percent = $service_percentage/100;
							//$product_price_order_qty = $order_qty * $product_price;
							$tax_total = priceFormat($product_price * $tax_percent, 0, ".", "");
							$service_total = priceFormat($product_price * $service_percent, 0, ".", "");
						}
						
						
						$order_qty = $dt->order_qty;
						$s = array(
							'id'					=> $dt->id,
							'discount_id' 			=> 0,
							'discount_notes' 		=> '',
							'discount_percentage' 	=> 0,
							'discount_price' 		=> 0,
							'discount_total' 		=> 0
						);
						
						$discount_percentage_item = 0;
						$discount_price_item = 0;
									
						if(!empty($data_diskon)){
							//discount per product
							$allow_discount = true;
							
							//BILLING
							if($data_diskon->discount_type == 1 AND $diskon_sebelum_pajak_service == 0){
								$allow_discount = false;
							}
							
							if($data_diskon->min_total_billing > 0){
								if($billingData->total_billing < $data_diskon->min_total_billing){
									$allow_discount = false;
								}
							}
							
							//PROMO
							if($dt->is_promo == 1 AND !empty($dt->promo_id)){
								$allow_discount = false;
							}
							
							//BUYGET
							if($dt->is_buyget == 1){
								
								if(!empty($buyget_used[$s['id']])){
									$allow_discount = false;
								}
								
							}
							
							if($dt->is_compliment == 1){
								$allow_discount = false;
							}
							
							
							if(!empty($data_diskon->discount_allow_day)){
								$allow_discount = false;
								//check in day
								if($data_diskon->discount_allow_day >= 1 AND $data_diskon->discount_allow_day <= 7){
									if($today_in_no == $data_diskon->discount_allow_day){
										$allow_discount = true;
									}
								}else
								if($data_diskon->discount_allow_day == 8){
									//weekday
									if($today_in_no >= 1 AND $today_in_no <= 5){
										$allow_discount = true;
									}
								}else
								if($data_diskon->discount_allow_day == 9){
									//weekend
									if($today_in_no >= 6 AND $today_in_no <= 7){
										$allow_discount = true;
									}
								}
							}							
							
							if($allow_discount == true){
								
								$allowed_time = true;
								if($data_diskon->use_discount_time == 1){
									
									$allowed_time = false;
									
									if($data_diskon->discount_time_end == '12:00 AM'){
										$data_diskon->discount_time_end = '11:59 PM';
									}
									
									$time_from = date("d-m-Y")." ".$data_diskon->discount_time_start;
									$time_till = date("d-m-Y")." ".$data_diskon->discount_time_end;
									
									$time_from_mk = strtotime($time_from);
									$time_till_mk = strtotime($time_till);
									
									$time_now = strtotime(date("d-m-Y H:i:s"));
									
									
									
									if($time_now >= $time_from_mk AND $time_now <= $time_till_mk){
										$allowed_time = true;
									}
									
									//echo "allowed_time=".$allowed_time.", $time_from_mk=".$time_from_mk.", $time_till_mk=".$time_till_mk.", $time_now=".$time_now;
									//die();
									
								}
								
								if($allowed_time){
									
									$s['discount_id'] = $discount_id;
									
									$discount_percentage_item = $data_diskon->discount_percentage;
									$discount_price_item = $data_diskon->discount_price;
									
									if($data_diskon->discount_percentage == '0.00'){
										$data_diskon->discount_percentage = 0;
									}
									
									//BILLING
									if($data_diskon->discount_type == 1 AND $diskon_sebelum_pajak_service == 1){
										$persentase_item = ($product_price_real*$order_qty)/$billingData->total_billing;
										
										if(!empty($data_diskon->discount_percentage)){
											$discount_percentage_item = $persentase_item*$data_diskon->discount_percentage;
										}else{
											$discount_price_item = $persentase_item*$data_diskon->discount_price;
											$discount_price_item = priceFormat($discount_price_item/$order_qty, 0, ".", "");
										}
									}
									
									//if($data_diskon->discount_product == 1){
									if(!empty($allow_diskon_product)){
										if(in_array($dt->product_id, $allow_diskon_product)){
											//all
											if(!empty($data_diskon->discount_percentage)){
												$s['discount_notes'] = $data_diskon->discount_name;
												$s['discount_percentage'] = $discount_percentage_item;
												$product_price_discount = priceFormat(($discount_percentage_item / 100) * $product_price_real, 0, ".", "");
												$s['discount_price'] = $product_price_discount;
												$s['discount_total'] = $product_price_discount * $order_qty;
												//$s['status_discount'] = 1;
											}else
											if(!empty($data_diskon->discount_price)){
												$s['discount_notes'] = $data_diskon->discount_name;
												$s['discount_percentage'] = 0;
												//$product_price_discount = priceFormat($product_price_real - $discount_price_item, 0, ".", "");
												$s['discount_price'] = $discount_price_item;
												$s['discount_total'] = $discount_price_item * $order_qty;
												//$s['status_discount'] = 1;
											}
										}else{
											
										}
									}else{
										//all
										if(!empty($data_diskon->discount_percentage)){
											$s['discount_notes'] = $data_diskon->discount_name;
											$s['discount_percentage'] = $discount_percentage_item;
											$product_price_discount = priceFormat(($discount_percentage_item / 100) * $product_price_real, 0, ".", "");
											$s['discount_price'] = $product_price_discount;
											$s['discount_total'] = $product_price_discount * $order_qty;
											//$s['status_discount'] = 1;
										}else
										if(!empty($data_diskon->discount_price)){
											$s['discount_notes'] = $data_diskon->discount_name;
											$s['discount_percentage'] = 0;
											//$product_price_discount = priceFormat($product_price_real - $discount_price_item, 0, ".", "");
											$s['discount_price'] = $discount_price_item;
											$s['discount_total'] = $discount_price_item * $order_qty;
											//$s['status_discount'] = 1;
										}
								
									}
									
								}
								
							}
							
						}
						
						$discount_total += $s['discount_total'];
						$update_detail[] = $s;
						
					}
				}
			}
			
			if(!empty($data_diskon)){
				
				if($data_diskon->discount_type == 1 AND !empty($data_diskon->discount_price)){
					
					$discount_total = $data_diskon->discount_price;
					
				}else{
					
					if($data_diskon->is_sistem_tawar == 1){
						$data_diskon->discount_price = $discount_price;
						$discount_total = $data_diskon->discount_price;
					}
					
				}
				
				if($data_diskon->discount_percentage == 0 AND $data_diskon->discount_price > 0){
					$data_diskon->discount_max_price = $data_diskon->discount_price;
				}
				
				if($data_diskon->discount_max_price > 0){
					if($discount_total >= $data_diskon->discount_max_price){
						$discount_total = $data_diskon->discount_max_price;
					}
				}
			}
			
			if(!empty($update_detail)){
				$this->db->update_batch($this->table_detail, $update_detail, "id");
			}
			
			$data_discount = array(
				'discount_id' => $discount_id,
				'discount_notes' => $discount_notes,
				'discount_percentage' => $discount_percentage,
				'discount_price' => $discount_price,
				'discount_total' => $discount_total,
				'discount_perbilling' => $discount_perbilling,
				'voucher_no' => $voucher_no,
				'is_sistem_tawar' => $is_sistem_tawar
			);
			
			//UPDATE OPTIONS
			$this->db->update($this->table, $data_discount, "id = '".$billing_id."'");
			
			//update voucher list
			if(!empty($voucher_no)){
				$data_discount_voucher = array(
					'voucher_status' => 1,
					'date_used' => date("Y-m-d"),
					'ref_billing_no' => $billingData->billing_no,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				);
				$this->db->update($this->table_discount_voucher, $data_discount_voucher, "voucher_no = '".$voucher_no."'");
			}else{
				$data_discount_voucher = array(
					'voucher_status' => 0,
					'date_used' 	 => '',
					'ref_billing_no' => '',
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				);
				$this->db->update($this->table_discount_voucher, $data_discount_voucher, "ref_billing_no = '".$billingData->billing_no."'");
				
			}
			
			//echo '<pre>';
			//print_r($data_discount);
			//die();
			
			$r = array('success' => true, 'discount_total' => $discount_total);
			
			$getBilling = $this->getBilling($billing_id);	
			$update_billing = $this->calculateBilling($billing_id);
			if(!empty($update_billing)){
		
				$getBilling->total_billing = $update_billing['total_billing'];
				$getBilling->tax_total = $update_billing['tax_total'];
				$getBilling->service_total = $update_billing['service_total'];
				$getBilling->discount_total = $update_billing['discount_total'];
				$getBilling->grand_total = $update_billing['grand_total'];
				$getBilling->total_pembulatan = $update_billing['total_pembulatan'];
				$getBilling->total_dp = $update_billing['total_dp'];
				$getBilling->compliment_total = $update_billing['compliment_total'];
				$getBilling->compliment_total_tax_service = $update_billing['compliment_total_tax_service'];
				$getBilling->total_billing_display = $update_billing['total_billing_display'];
				
				$getBilling->total_billing_show =  priceFormat($getBilling->total_billing);
				$getBilling->tax_total_show =  priceFormat($getBilling->tax_total);
				$getBilling->service_total_show =  priceFormat($getBilling->service_total);
				$getBilling->discount_total_show =  priceFormat($getBilling->discount_total);
				$getBilling->grand_total_show =  priceFormat($getBilling->grand_total);
				$getBilling->total_pembulatan_show =  priceFormat($getBilling->total_pembulatan);
				$getBilling->total_dp_show =  priceFormat($getBilling->total_dp);
				$getBilling->compliment_total_show =  priceFormat($getBilling->compliment_total);
				$getBilling->compliment_total_tax_service_show =  priceFormat($getBilling->compliment_total_tax_service);
				
			}
			
			$r['billingData'] = $getBilling;
		}
		
		die(json_encode($r));
	}
		
	public function updateCompliment(){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$this->table = $this->prefix.'billing';		
		$this->table_detail = $this->prefix.'billing_detail';				
		$billing_id = $this->input->post('billing_id', true);
		$detail_id = $this->input->post('detail_id', true);
		$is_clear = $this->input->post('is_clear', true);
		
		$detail_id_data = array();
		if(!empty($detail_id)){
			$detail_id_data = explode(",", $detail_id);
		}
		
		//check billing
		$billingData = array();
		if(!empty($billing_id)){
			$this->db->select("created, include_tax, include_service, tax_percentage, service_percentage,
						takeaway_no_tax, takeaway_no_service, is_compliment");
			$this->db->from($this->table);
			$this->db->where("id", $billing_id);
			$get_billing = $this->db->get();
			if($get_billing->num_rows() > 0){
				$billingData = $get_billing->row();
			}
		}
			
		//CLOSING DATE
		$var_closing = array(
			'xdate'	=> $billingData->created,
			'xtipe'	=> 'sales'
		);
		$is_closing = is_closing($var_closing);
		if($is_closing){
			$r = array('success' => false, 'info' => 'Sales Date Been Closed!'); 
			die(json_encode($r));
		}
		
		$r = array('success' => false);
		
		if(empty($billing_id)){
			$r = array('success' => false, 'info' => 'Billing not found!');
		}else{
			
			
			$get_opt_var = array('diskon_sebelum_pajak_service');
			$get_opt = get_option_value($get_opt_var);
			
			$diskon_sebelum_pajak_service = 0;
			if(!empty($get_opt['diskon_sebelum_pajak_service'])){
				$diskon_sebelum_pajak_service = $get_opt['diskon_sebelum_pajak_service'];
			}
			
			$update_detail = array();
			$compliment_total = 0;
			$compliment_total_tax_service = 0;
			if(!empty($detail_id)){
				
				$is_compliment = 1;
						
				if(!empty($is_clear)){
					$is_compliment = 0;
				}
				
				$update_compliment = array(
					'is_compliment'	=> $is_compliment
				);
				
				$this->db->update($this->table_detail, $update_compliment, "id IN (".$detail_id.")");
				
				//get all detail
				$this->db->select("id, product_price, order_qty, is_takeaway, is_compliment, 
				include_tax, include_service, tax_percentage, service_percentage, discount_total");
				$this->db->from($this->table_detail);
				//$this->db->where("id IN (".$detail_id.")");
				$this->db->where("billing_id IN (".$billing_id.")");
				$get_all_detail = $this->db->get();
				if($get_all_detail->num_rows() > 0){
					foreach($get_all_detail->result() as $dt){
						
						$product_price = $dt->product_price;
						$order_qty = $dt->order_qty;
							
						$is_takeaway = $dt->is_takeaway;
						$is_compliment = $dt->is_compliment;
						
						//TAX, SERVICE, TAKE AWAY & COMPLIMENT
						$include_tax = $dt->include_tax;
						$include_service = $dt->include_service;
						$tax_percentage = $dt->tax_percentage;
						$service_percentage = $dt->service_percentage;
						$discount_total = $dt->discount_total;
						//$include_tax = $billingData->include_tax;
						//$include_service = $billingData->include_service;
						//$tax_percentage = $billingData->tax_percentage;
						//$service_percentage = $billingData->service_percentage;
						$takeaway_no_tax = $billingData->takeaway_no_tax;
						$takeaway_no_service = $billingData->takeaway_no_service;
						$billing_is_compliment = $billingData->is_compliment;
						
						//BALANCING OLD DATA
						if($is_compliment == 1){
							if($tax_percentage == '0.00' AND !empty($billingData->tax_percentage)){
								$tax_percentage = $billingData->tax_percentage;
							}
							if($service_percentage == '0.00' AND !empty($billingData->service_percentage)){
								$service_percentage = $billingData->service_percentage;
							}
						}
						
						$tax_total = 0;
						$service_total = 0;
						$product_price_real = 0;
						if(!empty($include_tax) OR !empty($include_service)){
							if(!empty($include_tax) AND !empty($include_service)){
								$all_percentage = 100 + $tax_percentage + $service_percentage;
								$one_percent = $product_price / $all_percentage;
								//$one_percent_order_qty = $order_qty * $one_percent;
								$tax_total = priceFormat($one_percent * $tax_percentage, 0, ".", "");
								$service_total = priceFormat($one_percent * $service_percentage, 0, ".", "");
								$product_price_real = $product_price - ($tax_total + $service_total);
								
								//re-calculate tax service
								if($diskon_sebelum_pajak_service == 1){
									$product_price_real_disc = $product_price_real-$discount_total;
									$tax_total = priceFormat($product_price_real_disc * ($tax_percentage/100), 0, ".", "");
									$service_total = priceFormat($product_price_real_disc * ($service_percentage/100), 0, ".", "");
								}
								
								
							}else{
								if(!empty($include_tax)){
									$all_percentage = 100 + $tax_percentage;
									$one_percent = $product_price / $all_percentage;
									//$one_percent_order_qty = $order_qty * $one_percent;
									$tax_total = priceFormat($one_percent * $tax_percentage, 0, ".", "");
									$product_price_real = $product_price - ($tax_total);
									
									//re-calculate tax service
									if($diskon_sebelum_pajak_service == 1){
										$product_price_real_disc = $product_price_real-$discount_total;
										$tax_total = priceFormat($product_price_real_disc * ($tax_percentage/100), 0, ".", "");
									}
									
								}
								
								if(!empty($include_service)){
									$all_percentage = 100 + $service_percentage;
									$one_percent = $product_price / $all_percentage;
									//$one_percent_order_qty = $order_qty * $one_percent;
									$service_total = priceFormat($one_percent * $service_percentage, 0, ".", "");
									$product_price_real = $product_price - ($service_total);
									
									
									//re-calculate tax service
									if($diskon_sebelum_pajak_service == 1){
										$product_price_real_disc = $product_price_real-$discount_total;
										$service_total = priceFormat($product_price_real_disc * ($service_percentage/100), 0, ".", "");
									}
									
								}
								
							}
						}else
						{
							$product_price_real = $product_price;
							$tax_percent = $tax_percentage/100;
							$service_percent = $service_percentage/100;
							//$product_price_order_qty = $order_qty * $product_price;
							$tax_total = priceFormat($product_price * $tax_percent, 0, ".", "");
							$service_total = priceFormat($product_price * $service_percent, 0, ".", "");
							
							//re-calculate tax service
							if($diskon_sebelum_pajak_service == 1){
								$product_price_real_disc = $product_price_real-$discount_total;
								$tax_total = priceFormat($product_price_real_disc * ($tax_percentage/100), 0, ".", "");
								$service_total = priceFormat($product_price_real_disc * ($service_percentage/100), 0, ".", "");
							}
							
						}
						
						
						
						if(empty($is_takeaway)){
							$is_takeaway = 0;
						}else{
							$is_takeaway = 1;
							
							//get takeaway config tas service default
							if(!empty($takeaway_no_tax)){
								$tax_percentage = 0;
								$tax_total = 0;
							}
							
							if(!empty($takeaway_no_service)){
								$service_percentage = 0;
								$service_total = 0;
							}
							
						}
						
						if(empty($is_compliment)){
							$is_compliment = 0;
							$product_price_real = 0;
						}else{
							$is_compliment = 1;
							
							if(!empty($include_tax) OR !empty($include_service)){
								$tax_percentage = 0;
								$tax_total = 0;
								$service_percentage = 0;
								$service_total = 0;
							}else{
								$tax_percentage = 0;
								$tax_total = 0;
								$service_percentage = 0;
								$service_total = 0;
							}
							
						
						}
						
						//REAL TOTAL
						$tax_total = ($tax_total*$order_qty);
						$service_total = ($service_total*$order_qty);
						
						
						if($detail_id == $dt->id){
							$s = array(
								'id'					=> $dt->id,
								'is_compliment' 		=> $is_compliment,
								'tax_percentage' 		=> $tax_percentage,
								'tax_total' 			=> $tax_total,
								'service_percentage' 	=> $service_percentage,
								'service_total' 		=> $service_total,
								//REMOVE ALL DISKON
								'discount_id' 			=> 0,
								'discount_notes' 		=> '',
								'discount_percentage' 	=> 0,
								'discount_price' 		=> 0,
								'discount_total' 		=> 0
							);
						}else{
							$s = array(
								'id'					=> $dt->id,
								'is_compliment' 		=> $is_compliment,
								'tax_percentage' 		=> $tax_percentage,
								'tax_total' 			=> $tax_total,
								'service_percentage' 	=> $service_percentage,
								'service_total' 		=> $service_total
							);
						}
						
						if(!empty($is_compliment)){
							
							//echo 'compliment_total = '.$product_price_real.' X '.$order_qty.' =>'.($product_price_real * $order_qty).'<br/>';
							$compliment_total += ($product_price_real * $order_qty);
							$compliment_total_tax_service += ($product_price * $order_qty);
						}
						
						
						$update_detail[] = $s;
						
					}
				}
			}
			
			if(!empty($update_detail)){
				$this->db->update_batch($this->table_detail, $update_detail, "id");
			}
			
			$data_compliment = array(
				'compliment_total' => $compliment_total,
				'compliment_total_tax_service' => $compliment_total_tax_service
			);
			
			//UPDATE OPTIONS
			$this->db->update($this->table, $data_compliment, "id = '".$billing_id."'");
			
			//echo '<pre>';
			//print_r($data_compliment);
			//die();
			
			$r = array('success' => true, 'compliment_total' => $compliment_total, 'compliment_total_show' => 'Rp '.priceFormat($compliment_total));
			
			$getBilling = $this->getBilling($billing_id);	
			$update_billing = $this->calculateBilling($billing_id);
			if(!empty($update_billing)){
		
				$getBilling->total_billing = $update_billing['total_billing'];
				$getBilling->tax_total = $update_billing['tax_total'];
				$getBilling->service_total = $update_billing['service_total'];
				$getBilling->discount_total = $update_billing['discount_total'];
				$getBilling->grand_total = $update_billing['grand_total'];
				$getBilling->total_pembulatan = $update_billing['total_pembulatan'];
				$getBilling->total_dp = $update_billing['total_dp'];
				$getBilling->compliment_total = $update_billing['compliment_total'];
				$getBilling->compliment_total_tax_service = $update_billing['compliment_total_tax_service'];
				$getBilling->total_billing_display = $update_billing['total_billing_display'];
				
				$getBilling->total_billing_show =  priceFormat($getBilling->total_billing);
				$getBilling->tax_total_show =  priceFormat($getBilling->tax_total);
				$getBilling->service_total_show =  priceFormat($getBilling->service_total);
				$getBilling->discount_total_show =  priceFormat($getBilling->discount_total);
				$getBilling->grand_total_show =  priceFormat($getBilling->grand_total);
				$getBilling->total_pembulatan_show =  priceFormat($getBilling->total_pembulatan);
				$getBilling->total_dp_show =  priceFormat($getBilling->total_dp);
				$getBilling->compliment_total_show =  priceFormat($getBilling->compliment_total);
				$getBilling->compliment_total_tax_service_show =  priceFormat($getBilling->compliment_total_tax_service);
				
			}
			
			$r['billingData'] = $getBilling;
			
		}
		
		die(json_encode($r));
	}
	
	public function returOrder()
	{
		$this->table = $this->prefix.'billing_detail';
				
		$session_user = $this->session->userdata('user_username');		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$billing_id = $this->input->post('billing_id', true);		
		$billing_detail_id = $this->input->post('billing_detail_id', true);		
		$retur_type = $this->input->post('retur_type', true);		
		$retur_qty = $this->input->post('retur_qty', true);		
		$retur_reason = $this->input->post('retur_reason', true);		
		
		if(empty($billing_detail_id)){
			$r = array('success' => false, 'info' => 'Order Item unidentified!');
			echo json_encode($r);
			die();
		}
		
		if($retur_type != 'payment'){
			$retur_type = 'menu';
		}
		
		$data_retur = array(
			'retur_type' => $retur_type,
			'retur_qty' => $retur_qty,
			'retur_reason' => $retur_reason
		);
				
		//UPDATE OPTIONS
		$this->db->update($this->table, $data_retur, "id = '".$billing_detail_id."'");
		$r = array('success' => true );
		
		die(json_encode($r));
	}
	
	public function logBilling($billingData = array(), $type = '',  $info = ''){
		
		$session_user = $this->session->userdata('user_username');
		
		$opt_var = array('billing_log');
		$get_opt = get_option_value($opt_var);
		
		if(!empty($billingData) AND !empty($info) AND !empty($session_user) AND !empty($get_opt['billing_log'])){
			$data_log = array(
					'billing_id' => $billingData->id,
					'trx_type' => $type,
					'trx_info' => $info,
					'log_data' => json_encode($billingData),
					'createdby' => $session_user,
					'created' => date("Y-m-d H:i:s")
			);
			$this->db->insert($this->prefix.'billing_log', $data_log);
		}
	}
	
	public function mergeBill(){
		$this->table = $this->prefix.'billing';
		$this->table_detail = $this->prefix.'billing_detail';
		$this->table_inv = $this->prefix.'table_inventory';
				
		$session_user = $this->session->userdata('user_username');		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$opt_value = array(
			'wepos_tipe'
		);
		
		$get_opt = get_option_value($opt_value);
		
		$wepos_tipe = 'cafe';
		if(!empty($get_opt['wepos_tipe'])){
			$wepos_tipe = $get_opt['wepos_tipe'];
		}
		
		$main_billing_id = $this->input->post('main_billing_id', true);		
		$merge_billing_id = $this->input->post('merge_billing_id', true);		
		
		if(empty($main_billing_id)){
			$r = array('success' => false, 'info' => 'Main Billing unidentified!');
			echo json_encode($r);
			die();
		}
		
		if(empty($merge_billing_id)){
			$r = array('success' => false, 'info' => 'All Merge Billing unidentified!');
			echo json_encode($r);
			die();
		}
		
		
		$cek_detail_done = true;
		$update_detail = array();
		//get all detail
		$this->db->select("id, billing_id, order_status");
		$this->db->from($this->table_detail);
		$this->db->where("billing_id IN (".$merge_billing_id.")");
		$this->db->where("is_deleted = 0");
		$get_all_detail = $this->db->get();
		if($get_all_detail->num_rows() > 0){
			foreach($get_all_detail->result() as $dt){
				$update_detail[] = array(
					'id'						=> $dt->id,
					'billing_id'				=> $main_billing_id,
					'billing_id_before_merge'	=> $dt->billing_id
				);
				
				if($dt->order_status != 'done'){
					$cek_detail_done = false;
					break;
				}
			}
		}
		
		//make sure merge billing - detail are DONE!
		if($cek_detail_done == false){
			$r = array('success' => false, 'info' => 'Make Sure All Order is done!<br/>Merge Bill only used when pay billing');
			echo json_encode($r);
			die();
		}
		
		
		if(!empty($update_detail)){
			$this->db->update_batch($this->table_detail, $update_detail, "id");
		}
		
		
		$all_table_id = array();
		$all_billing_id = array();
		$main_billing_no = '';
		//get all billing
		
		$this->db->select("id, table_id, billing_no");
		$this->db->from($this->table);
		$this->db->where("id IN (".$merge_billing_id.") ");
		$get_billing = $this->db->get();
		if($get_billing->num_rows() > 0){
			foreach($get_billing->result() as $dt){
				if(!in_array($dt->table_id, $all_table_id)){
					$all_table_id[] = $dt->table_id;
				}
				
				if($dt->id != $main_billing_id){
					$all_billing_id[] = $dt->id;
				}else{
					$main_billing_no = $dt->billing_no;
				}
			}
		}
		
		$date_mktime = strtotime(date("Y-m-d H:i:s"));
		$date_now = date("Y-m-d");
		
		//CLOSING DATE
		$var_closing = array(
			'xdate'	=> $date_now,
			'xtipe'	=> 'sales'
		);
		$is_closing = is_closing($var_closing);
		if($is_closing){
			$r = array('success' => false, 'info' => 'Sales Date Been Closed!'); 
			die(json_encode($r));
		}
		
		if(!empty($all_billing_id)){
			$all_billing_id_txt = implode(",", $all_billing_id);
			$data_merge = array(
				'billing_notes' => 'Merge Billing: '.$main_billing_no,
				'billing_status' => 'cancel',
				'merge_id' => $main_billing_id,
				'updatedby' => $session_user,
				'updated' => date("Y-m-d H:i:s", ($date_mktime-1)),
				'total_billing' => 0,
				'tax_total' => 0,
				'service_total' => 0,
				'discount_total' => 0,
				'total_dp' => 0,
				'total_pembulatan' => 0,
				'grand_total' => 0
			);
					
			//UPDATE BILLING
			$this->db->update($this->table, $data_merge, "id IN (".$all_billing_id_txt.")");
		}
		
		//MAIN BILLING
		$data_main_merge = array(
			'merge_main_status' => 1,
			'merge_id' => $main_billing_id,
			'updatedby' => $session_user,
			'updated' => date("Y-m-d H:i:s")
		);
		$this->db->update($this->table, $data_main_merge, "id IN (".$main_billing_id.")");
		
		$update_billing = $this->calculateBilling($main_billing_id);
		
		//SET STATUS TABLE
		if(!empty($all_table_id) AND $wepos_tipe != 'retail'){
			$all_table_id_txt = implode(",", $all_table_id);
			$data_status_table = array(
				'status' => 'booked',
				'billing_no' => $main_billing_no,
				'updatedby' => $session_user,
				'updated' => date("Y-m-d H:i:s")
			);
			$this->db->update($this->table_inv, $data_status_table, "table_id IN (".$all_table_id_txt.") AND tanggal = '".$date_now."'");
		}
		
		$r = array('success' => true );
		die(json_encode($r));
	}
	
	public function unMergeBill(){
		$this->table = $this->prefix.'billing';
		$this->table_detail = $this->prefix.'billing_detail';
		$this->table_inv = $this->prefix.'table_inventory';
				
		$session_user = $this->session->userdata('user_username');		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$main_billing_id = $this->input->post('main_billing_id', true);				
		
		if(empty($main_billing_id)){
			$r = array('success' => false, 'info' => 'Main Billing unidentified!');
			echo json_encode($r);
			die();
		}
		
		$date_now = date("Y-m-d");
		//CLOSING DATE
		$var_closing = array(
			'xdate'	=> $date_now,
			'xtipe'	=> 'sales'
		);
		$is_closing = is_closing($var_closing);
		if($is_closing){
			$r = array('success' => false, 'info' => 'Sales Date Been Closed!'); 
			die(json_encode($r));
		}
		
		$all_billing_id = array();
		$update_detail = array();
		//get all detail
		
		$this->db->select("id, billing_id_before_merge");
		$this->db->from($this->table_detail);
		$this->db->where("billing_id IN (".$main_billing_id.")");
		$this->db->where("is_deleted = 0");
		$get_all_detail = $this->db->get();
		if($get_all_detail->num_rows() > 0){
			foreach($get_all_detail->result() as $dt){
				
				if(empty($dt->billing_id_before_merge)){
					$dt->billing_id_before_merge = $dt->id;
				}
				
				$update_detail[] = array(
					'id'						=> $dt->id,
					'billing_id'				=> $dt->billing_id_before_merge,
					'billing_id_before_merge'	=> ''
				);
				
				if(!in_array($dt->billing_id_before_merge, $all_billing_id)){
					$all_billing_id[] = $dt->billing_id_before_merge;
				}
				
			}
		}
		
		
		if(!empty($update_detail)){
			$this->db->update_batch($this->table_detail, $update_detail, "id");
		}
		
		
		if(!empty($all_billing_id)){
			$merge_billing_id = implode(",",$all_billing_id);
			
			$data_merge = array(
				'billing_status' => 'hold',
				'merge_id' => '',
				'merge_main_status' => 0,
				'updatedby' => $session_user,
				'updated' => date("Y-m-d H:i:s")
			);	
			
			//UPDATE BILLING
			$this->db->update($this->table, $data_merge, "id IN (".$merge_billing_id.")");
			
			$date_mktime = strtotime(date("Y-m-d H:i:s"));
			$date_now = date("Y-m-d");
		
			//SET STATUS TABLE
			//get all billing
			
			$this->db->select("id, billing_no, table_id");
			$this->db->from($this->table);
			$this->db->where("id IN (".$merge_billing_id.")");
			$get_billing = $this->db->get();
			if($get_billing->num_rows() > 0){
				foreach($get_billing->result() as $dt){
					
					$data_status_table = array(
						'status' => 'booked',
						'billing_no' => $dt->billing_no,
						'updatedby' => $session_user,
						'updated' => date("Y-m-d H:i:s")
					);
					
					//its OK - dikit paling yg merge
					$this->db->update($this->table_inv, $data_status_table, "table_id = '".$dt->table_id."' AND tanggal = '".$date_now."'");
					
					$update_billing = $this->calculateBilling($dt->id);
				}
			}
			
		}
		
		$r = array('success' => true );
		die(json_encode($r));
	}
	
	public function cek_mergeBill(){
		
		$this->table = $this->prefix.'billing';
		$this->table_detail = $this->prefix.'billing_detail';
		
		$session_user = $this->session->userdata('user_username');		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$billing_id = $this->input->post('billing_id', true);				
		
		if(empty($billing_id)){
			$r = array('success' => false, 'info' => 'Merge Billing unidentified!');
			echo json_encode($r);
			die();
		}
		
		
		$update_detail = array();
		$update_detail_done = array();
		$this->db->select("a.id, a.billing_no");
		$this->db->from($this->table." as a");
		$this->db->where("a.id IN (".$billing_id.")");
		$get_all = $this->db->get();
		if($get_all->num_rows() > 0){
			foreach($get_all->result() as $dt){
				
				if(empty($update_detail[$dt->billing_no])){
					$update_detail[$dt->billing_no] = 0;
				}
				
				if(empty($update_detail_done[$dt->billing_no])){
					$update_detail_done[$dt->billing_no] = 0;
				}
				
				
			}
		}else{
			$r = array('success' => false, 'info'	=> "Cannot Merge Billing<br/>There is no Billing Identified!!" );
			die(json_encode($r));
		}
		
		$this->db->select("a.order_status, a.billing_id, b.billing_no");
		$this->db->from($this->table_detail." as a");
		$this->db->join($this->table." as b","b.id = a.billing_id","LEFT");
		$this->db->where("a.billing_id IN (".$billing_id.")");
		$this->db->where("a.is_deleted = 0");
		//$this->db->where("order_status != 'done'");
		$get_all_detail = $this->db->get();
		if($get_all_detail->num_rows() > 0){
			foreach($get_all_detail->result() as $dt){
				
				
				$update_detail[$dt->billing_no] += 1;
				
				if($dt->order_status != 'done'){
					$update_detail_done[$dt->billing_no] += 1;
				}
			}
		}else{
			$r = array('success' => false, 'info'	=> "Cannot Merge Billing<br/>There is no Order!" );
			die(json_encode($r));
		}
		
		//print_r($update_detail_done);
		//die();
		
		$no_order = array();
		if(!empty($update_detail)){
			foreach($update_detail as $key => $dt){
				
				if($dt == 0){
					$no_order[] = $key;
				}
				
			}
		}
		
		if(!empty($no_order)){
			$no_order_txt = implode(",", $no_order);
			$r = array('success' => false, 'info'	=> "Cannot Merge Billing: ".$no_order_txt ."<br/>There is no order!" );
			die(json_encode($r));
		}
		
		
		$no_order_done = array();
		if(!empty($update_detail_done)){
			foreach($update_detail_done as $key => $dt){
				
				if($dt > 0){
					$no_order_done[] = $key;
				}
				
			}
		}
		
		if(!empty($no_order_done)){
			$no_order_done_txt = implode(",", $no_order_done);
			$r = array('success' => false, 'info'	=> "Cannot Merge Billing: ".$no_order_done_txt."<br/>All Status Order should be done/printer!" );
			die(json_encode($r));
		}
		
		$r = array('success' => true);
		
		die(json_encode($r));
		
	}
	
	public function cek_splitBill(){
		
		$this->table = $this->prefix.'billing';
		$this->table_detail = $this->prefix.'billing_detail';
		
		$session_user = $this->session->userdata('user_username');		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$billing_id = $this->input->post('billing_id', true);				
		
		if(empty($billing_id)){
			$r = array('success' => false, 'info' => 'Split Billing unidentified!');
			echo json_encode($r);
			die();
		}
		
		$update_detail = 0;
		$this->db->select("id, billing_id, order_status");
		$this->db->from($this->table_detail);
		$this->db->where("billing_id = '".$billing_id."'");
		$this->db->where("is_deleted = 0");
		//$this->db->where("order_status != 'done'");
		$get_all_detail = $this->db->get();
		if($get_all_detail->num_rows() > 0){
			foreach($get_all_detail->result() as $dt){
				if($dt->order_status != 'done'){
					$update_detail++;
				}
			}
			
		}else{
			$r = array('success' => false, 'info'	=> "Cannot Split Billing<br/>There is no Order!" );
			die(json_encode($r));
		}
		
		if(!empty($update_detail)){
			$r = array('success' => false, 'info'	=> "Cannot Split Billing<br/>All Status Order should be done/printer!" );
		}else{
			$r = array('success' => true);
		}
		
		die(json_encode($r));
		
	}
	
	public function splitBill(){
		
		$this->table = $this->prefix.'billing';
		$this->table_detail = $this->prefix.'billing_detail';
		$this->table_detail_split = $this->prefix.'billing_detail_split';
		//$this->table_inv = $this->prefix.'table_inventory';
				
		$session_user = $this->session->userdata('user_username');		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$date_now = date("Y-m-d");
		
		//CLOSING DATE
		$var_closing = array(
			'xdate'	=> $date_now,
			'xtipe'	=> 'sales'
		);
		$is_closing = is_closing($var_closing);
		if($is_closing){
			$r = array('success' => false, 'info' => 'Sales Date Been Closed!'); 
			die(json_encode($r));
		}
		
		$billing_id = $this->input->post('billing_id', true);				
		
		if(empty($billing_id)){
			$r = array('success' => false, 'info' => 'Split Billing unidentified!');
			echo json_encode($r);
			die();
		}
		
		//remove all on split
		$this->db->delete($this->table_detail_split, "billing_id = '".$billing_id."'");
		
		
		$insert_detail = array();
		$status_order = array();
		//get all detail
		$this->db->select("*");
		$this->db->from($this->table_detail);
		$this->db->where("billing_id = '".$billing_id."'");
		$this->db->where("is_deleted = 0");
		$get_all_detail = $this->db->get();
		if($get_all_detail->num_rows() > 0){
			foreach($get_all_detail->result() as $dt){
				
				if($dt->order_status != 'done'){
					$status_order[] = $dt->id;
				}
					
				$dt->billing_detail_id = $dt->id;
				unset($dt->id);
				$insert_detail[] = (array)$dt;
				
			}
		}
		
		if(!empty($status_order)){
			$r = array('success' => false, 'info'	=> "Cannot Split Billing<br/>All Status Order should be done/printer!" );
			die(json_encode($r));
		}
		
		
		if(!empty($insert_detail)){
			$this->db->insert_batch($this->table_detail_split, $insert_detail);
		}
		
		
		$r = array('success' => true );
		die(json_encode($r));
	}
	
	/*SAVE ORDER*/
	public function save_orderProduct_split(){
		$this->table = $this->prefix.'billing';				
		$this->table2 = $this->prefix.'billing_detail_split';				
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		//PRODUCT
		$id = $this->input->post('id');
		$billing_id = $this->input->post('billing_id');
		$order_qty = $this->input->post('order_qty');
		$order_qty_split = $this->input->post('order_qty_split');
				
		if(empty($id)){
			$r = array('success' => false, 'info' => 'Order ID not Found!');
			echo json_encode($r);
			die();
		}
		
		$date_now = date("Y-m-d");
		
		//CLOSING DATE
		$var_closing = array(
			'xdate'	=> $date_now,
			'xtipe'	=> 'sales'
		);
		$is_closing = is_closing($var_closing);
		if($is_closing){
			$r = array('success' => false, 'info' => 'Sales Date Been Closed!'); 
			die(json_encode($r));
		}
			
		$var = array('fields'	=>	array(
				'order_qty_split'		=>	$order_qty_split,
				'updated'		=>	$date_now,
				'updatedby'		=>	$session_user
			),
			'table'			=>  $this->table2,
			'primary_key'	=>  'id'
		);
		
		//UPDATE
		$this->lib_trans->begin();
			$update = $this->m2->save($var, $id);
		$this->lib_trans->commit();
		
		if($update)
		{  
			$r = array('success' => true, 'id' => $id);
		}  
		else
		{  
			$r = array('success' => false);
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	/*SAVE ORDER*/
	public function save_splitBill(){
		$this->table = $this->prefix.'billing';				
		$this->table_detail = $this->prefix.'billing_detail';			
		$this->table_detail_split = $this->prefix.'billing_detail_split';			
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		//billing
		$billing_id = $this->input->post('billing_id');
				
		if(empty($billing_id)){
			$r = array('success' => false, 'info' => 'Billing ID not Found!');
			echo json_encode($r);
			die();
		}
		
		//get all detail
		$total_item_split = 0;
		$status_order = array();
		$data_old_to_new = array();
		$data_old_billing = array();
		$data_new_billing = array();
		$this->db->select("*");
		$this->db->from($this->table_detail_split);
		$this->db->where("billing_id = '".$billing_id."'");
		$get_detail = $this->db->get();
		if($get_detail->num_rows() > 0){
			foreach($get_detail->result() as $dt){
				
				if($dt->order_status != 'done'){
					$status_order[] = $dt->id;
				}
				
				if($dt->order_qty == $dt->order_qty_split){
					//old become split_data
					$total_item_split += $dt->order_qty_split;
					unset($dt->order_qty_split);
					$data_old_to_new[] = array(
						'id'	=> $dt->billing_detail_id,
						'billing_id'	=> ''
					);
				}else{
					
					if($dt->order_qty_split == 0){
						//no change / split
						
						$dt->tax_total = 0;
						$dt->service_total = 0;
						$dt->discount_total = 0;
						$dt->product_price_real = 0;
						if(!empty($dt->include_tax) OR !empty($dt->include_service)){
							if(!empty($dt->include_tax) AND !empty($dt->include_service)){
								$all_percentage = 100 + $dt->tax_percentage + $dt->service_percentage;
								$one_percent = $dt->product_price / $all_percentage;
								$dt->tax_total = priceFormat($one_percent * $dt->tax_percentage, 0, ".", "");
								$dt->tax_total = $dt->tax_total * $dt->order_qty;
								$dt->service_total = priceFormat($one_percent * $dt->service_percentage, 0, ".", "");
								$dt->product_price_real = $dt->product_price - ($dt->tax_total + $dt->service_total);
								$dt->service_total = $dt->service_total * $dt->order_qty;
							}else{
								if(!empty($dt->include_tax)){
									$all_percentage = 100 + $dt->tax_percentage;
									$one_percent = $dt->product_price / $all_percentage;
									$dt->tax_total = priceFormat($one_percent * $dt->tax_percentage, 0, ".", "");
									$dt->tax_total = $dt->tax_total * $dt->order_qty;
									$dt->product_price_real = $dt->product_price - ($dt->tax_total);
								}
								
								if(!empty($dt->include_service)){
									$all_percentage = 100 + $dt->service_percentage;
									$one_percent = $dt->product_price / $all_percentage;
									$dt->service_total = priceFormat($one_percent * $dt->service_percentage, 0, ".", "");
									$dt->service_total = $dt->service_total * $dt->order_qty;
									$dt->product_price_real = $dt->product_price - ($dt->service_total);
								}
								
							}
						}else
						{
							$dt->product_price_real = $dt->product_price;
							$tax_percent = $dt->tax_percentage/100;
							$service_percent = $dt->service_percentage/100;
							$dt->tax_total = priceFormat($dt->product_price * $tax_percent, 0, ".", "");
							$dt->tax_total = $dt->tax_total * $dt->order_qty;
							$dt->service_total = priceFormat($dt->product_price * $service_percent, 0, ".", "");
							$dt->service_total = $dt->service_total * $dt->order_qty;
						}
						
						if(!empty($dt->discount_percentage)){
							$discount_percent = $dt->discount_percentage/100;
							$discount_qty = $dt->product_price * $discount_percent;
							$dt->discount_total = $discount_qty * $dt->order_qty;
						}
						
						unset($dt->order_qty_split);
						$data_old_billing[] = array(
							'id'	=> $dt->billing_detail_id,
							'order_qty'	=> $dt->order_qty,
							'tax_total'	=> $dt->tax_total,
							'service_total'	=> $dt->service_total,
							'discount_total' => $dt->discount_total,
							'product_price_real' => $dt->product_price_real
						);
						
					}else{
						
						$qty_gap = $dt->order_qty - $dt->order_qty_split;
						
						if($qty_gap < 0){
							$qty_gap = 0;
							$dt->order_qty_split = $dt->order_qty;
							$total_item_split += $dt->order_qty_split;
							unset($dt->order_qty_split);
							$data_old_to_new[] = array(
								'id'	=> $dt->billing_detail_id,
								'billing_id'	=> ''
							);
						}else{
							
							//OLD BILLING
							$dt->order_qty = $qty_gap;
							
							$dt->tax_total = 0;
							$dt->service_total = 0;
							$dt->discount_total = 0;
							$dt->product_price_real = 0;
							if(!empty($dt->include_tax) OR !empty($dt->include_service)){
								if(!empty($dt->include_tax) AND !empty($dt->include_service)){
									$all_percentage = 100 + $dt->tax_percentage + $dt->service_percentage;
									$one_percent = $dt->product_price / $all_percentage;
									$dt->tax_total = priceFormat($one_percent * $dt->tax_percentage, 0, ".", "");
									$dt->tax_total = $dt->tax_total * $dt->order_qty;
									$dt->service_total = priceFormat($one_percent * $dt->service_percentage, 0, ".", "");
									$dt->product_price_real = $dt->product_price - ($dt->tax_total + $dt->service_total);
									$dt->service_total = $dt->service_total * $dt->order_qty;
								}else{
									if(!empty($dt->include_tax)){
										$all_percentage = 100 + $dt->tax_percentage;
										$one_percent = $dt->product_price / $all_percentage;
										$dt->tax_total = priceFormat($one_percent * $dt->tax_percentage, 0, ".", "");
										$dt->tax_total = $dt->tax_total * $dt->order_qty;
										$dt->product_price_real = $dt->product_price - ($dt->tax_total);
									}
									
									if(!empty($dt->include_service)){
										$all_percentage = 100 + $dt->service_percentage;
										$one_percent = $dt->product_price / $all_percentage;
										$dt->service_total = priceFormat($one_percent * $dt->service_percentage, 0, ".", "");
										$dt->service_total = $dt->service_total * $dt->order_qty;
										$dt->product_price_real = $dt->product_price - ($dt->service_total);
									}
									
								}
							}else
							{
								$dt->product_price_real = $dt->product_price;
								$tax_percent = $dt->tax_percentage/100;
								$service_percent = $dt->service_percentage/100;
								$dt->tax_total = priceFormat($dt->product_price * $tax_percent, 0, ".", "");
								$dt->tax_total = $dt->tax_total * $dt->order_qty;
								$dt->service_total = priceFormat($dt->product_price * $service_percent, 0, ".", "");
								$dt->service_total = $dt->service_total * $dt->order_qty;
							}
							
							if(!empty($dt->discount_percentage)){
								$discount_percent = $dt->discount_percentage/100;
								$discount_qty = $dt->product_price * $discount_percent;
								$dt->discount_total = $discount_qty * $dt->order_qty;
							}
							
							$data_old_billing[] = array(
								'id'	=> $dt->billing_detail_id,
								'order_qty'	=> $dt->order_qty,
								'tax_total'	=> $dt->tax_total,
								'service_total'	=> $dt->service_total,
								'discount_total' => $dt->discount_total
							);
							
							//NEW BILLING
							$total_item_split += $dt->order_qty_split;
							$dt->order_qty = $dt->order_qty_split;
							
							$dt->tax_total = 0;
							$dt->service_total = 0;
							$dt->discount_total = 0;
							$dt->product_price_real = 0;
							if(!empty($dt->include_tax) OR !empty($dt->include_service)){
								if(!empty($dt->include_tax) AND !empty($dt->include_service)){
									$all_percentage = 100 + $dt->tax_percentage + $dt->service_percentage;
									$one_percent = $dt->product_price / $all_percentage;
									$dt->tax_total = priceFormat($one_percent * $dt->tax_percentage, 0, ".", "");
									$dt->tax_total = $dt->tax_total * $dt->order_qty;
									$dt->service_total = priceFormat($one_percent * $dt->service_percentage, 0, ".", "");
									$dt->product_price_real = $dt->product_price - ($dt->tax_total + $dt->service_total);
									$dt->service_total = $dt->service_total * $dt->order_qty;
								}else{
									if(!empty($dt->include_tax)){
										$all_percentage = 100 + $dt->tax_percentage;
										$one_percent = $dt->product_price / $all_percentage;
										$dt->tax_total = priceFormat($one_percent * $dt->tax_percentage, 0, ".", "");
										$dt->tax_total = $dt->tax_total * $dt->order_qty;
										$dt->product_price_real = $dt->product_price - ($dt->tax_total);
									}
									
									if(!empty($dt->include_service)){
										$all_percentage = 100 + $dt->service_percentage;
										$one_percent = $dt->product_price / $all_percentage;
										$dt->service_total = priceFormat($one_percent * $dt->service_percentage, 0, ".", "");
										$dt->service_total = $dt->service_total * $dt->order_qty;
										$dt->product_price_real = $dt->product_price - ($dt->service_total);
									}
									
								}
							}else
							{
								$dt->product_price_real = $dt->product_price;
								$tax_percent = $dt->tax_percentage/100;
								$service_percent = $dt->service_percentage/100;
								$dt->tax_total = priceFormat($dt->product_price * $tax_percent, 0, ".", "");
								$dt->tax_total = $dt->tax_total * $dt->order_qty;
								$dt->service_total = priceFormat($dt->product_price * $service_percent, 0, ".", "");
								$dt->service_total = $dt->service_total * $dt->order_qty;
							}
							
							if(!empty($dt->discount_percentage)){
								$discount_percent = $dt->discount_percentage/100;
								$discount_qty = $dt->product_price * $discount_percent;
								$dt->discount_total = $discount_qty * $dt->order_qty;
							}
							
							unset($dt->id);
							unset($dt->billing_detail_id);
							unset($dt->order_qty_split);
							$data_new_billing[] = (array) $dt;
							
						}
						
					}
					
				}
			}
		}
		
		if(empty($total_item_split)){
			$r = array('success' => false, 'info'	=> "Cannot Split Billing<br/>Total Order Cannot Empty" );
			die(json_encode($r));
		}
		
		if(!empty($status_order)){
			$r = array('success' => false, 'info'	=> "Cannot Split Billing<br/>All Status Order should be done/printer!" );
			die(json_encode($r));
		}
		
		$date_now = date('Y-m-d H:i:s');
		
		//CLOSING DATE
		$var_closing = array(
			'xdate'	=> $date_now,
			'xtipe'	=> 'sales'
		);
		$is_closing = is_closing($var_closing);
		if($is_closing){
			$r = array('success' => false, 'info' => 'Sales Date Been Closed!'); 
			die(json_encode($r));
		}
			
		$billingData_old = $this->getBilling($billing_id);
		$billingData = $this->getBilling();
		
		
		if($billingData == false OR empty($billingData->billing_id)){
			$r = array('success' => false, 'info' => 'Create New Billing Failed!');
			echo json_encode($r);
			die();
		}else{
			
			//set to hold
			$data_update_billing = array(
				'billing_status'	=> $billingData_old->billing_status,
				'table_id'			=> $billingData_old->table_id,
				'split_from_id'		=> $billing_id
			);
			$this->db->update($this->table, $data_update_billing, "id = '".$billingData->billing_id."'");
			
			//data_new_billing
			if(!empty($data_new_billing)){
				
				$insert_new = array();
				foreach($data_new_billing as $dt){
					
					$dt['billing_id'] = $billingData->billing_id;
					$insert_new[] = $dt;
				}
				
				$this->db->insert_batch($this->table_detail, $insert_new);
				
			}
			
			//$data_old_to_new
			if(!empty($data_old_to_new)){
				
				$update_new = array();
				foreach($data_old_to_new as $dt){
					
					$dt['billing_id'] = $billingData->billing_id;
					$update_new[] = $dt;
				}
				
				$this->db->update_batch($this->table_detail, $update_new, "id");
			}
			
			
			//update billing calc
			$update_billing = $this->calculateBilling($billingData->billing_id);
				
		}
		
		//data_old_billing
		if(!empty($data_old_billing)){
			$this->db->update_batch($this->table_detail, $data_old_billing, "id");
			
			$update_billing_old = $this->calculateBilling($billingData_old->billing_id);
		}
		
		//remove all on split
		$this->db->delete($this->table_detail_split, "billing_id = '".$billing_id."'");
		
		$r = array('success' => true);
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	
	/*print_MultipleQC*/
	public function print_MultipleQC(){
		
		$this->prefix = config_item('db_prefix');
		$this->table = $this->prefix.'options';
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$opt_var = array('show_multiple_print_qc');
		$get_opt = get_option_value($opt_var);
		
		$show_multiple_print_qc = 0;
		if(!empty($get_opt['show_multiple_print_qc'])){
			$show_multiple_print_qc = $get_opt['show_multiple_print_qc'];
		}
		
		
		if($show_multiple_print_qc == 0){
			$r = array('success' => false, 'info' => 'Option Print Multiple QC is not active!');
			echo json_encode($r);
			die();
		}
		
		$this->db->from($this->table);
		$this->db->where("option_var = 'multiple_print_qc'");
		$get_opt = $this->db->get();
		
		$data_opt = array();
		$r = array('success' => true, 'total_printer' => 0, 'data_printer' => $data_opt);
		
		if($get_opt->num_rows() > 0){
			
			$data_opt = $get_opt->result();
			$r = array('success' => true, 'total_printer' => $get_opt->num_rows(), 'data_printer' => $data_opt);
		
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
		
	}
	
	/*print_MultipleBilling*/
	public function print_MultipleBilling(){
		
		$this->prefix = config_item('db_prefix');
		$this->table = $this->prefix.'options';
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$opt_var = array('show_multiple_print_billing');
		$get_opt = get_option_value($opt_var);
		
		$show_multiple_print_billing = 0;
		if(!empty($get_opt['show_multiple_print_billing'])){
			$show_multiple_print_billing = $get_opt['show_multiple_print_billing'];
		}
		
		
		if($show_multiple_print_billing == 0){
			$r = array('success' => false, 'info' => 'Option Print Multiple Billing is not active!');
			echo json_encode($r);
			die();
		}
		
		$this->db->from($this->table);
		$this->db->where("option_var = 'multiple_print_billing'");
		$get_opt = $this->db->get();
		
		$data_opt = array();
		$r = array('success' => true, 'total_printer' => 0, 'data_printer' => $data_opt);
		
		if($get_opt->num_rows() > 0){
			
			$data_opt = $get_opt->result();
			$r = array('success' => true, 'total_printer' => $get_opt->num_rows(), 'data_printer' => $data_opt);
		
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
		
	}
		
	public function lockBilling(){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$this->table = $this->prefix.'billing';			
		$billing_id = $this->input->post('billing_id', true);
		$value = $this->input->post('value', true);
		
		$r = array('success' => false);
		
		if(empty($billing_id)){
			$r = array('success' => false, 'info' => 'Billing Id Cannot Empty!');
		}else{
			
			$billingData = array();
			$this->db->where("id", $billing_id);
			$getBilling = $this->db->get($this->table);
			if($getBilling->num_rows() > 0){
				$billingData = $getBilling->row();
			}
			
			$lock_billing = array(
				'lock_billing' => $value
			);
			
			//CLOSING DATE
			$var_closing = array(
				'xdate'	=> $billingData->created,
				'xtipe'	=> 'sales'
			);
			$is_closing = is_closing($var_closing);
			if($is_closing){
				$r = array('success' => false, 'info' => 'Sales Date Been Closed!'); 
				die(json_encode($r));
			}
					
			//UPDATE OPTIONS
			$this->db->update($this->table, $lock_billing, "id = '".$billing_id."'");
			
			$r = array('success' => true );
			
		}
		
		die(json_encode($r));
	}
	
	public function save_infoBilling(){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$this->table = $this->prefix.'billing';			
		$billing_id = $this->input->post('billing_id', true);
		
		$payment_id = $this->input->post('payment_id', true);
		$bank_id = $this->input->post('bank_id', true);
		$card_no = $this->input->post('card_no', true);
		$billing_notes = $this->input->post('billing_notes', true);
		$qc_notes = $this->input->post('qc_notes', true);
		$single_rate = $this->input->post('single_rate', true);
		$sales_id = $this->input->post('sales_id', true);
		$sales_price = $this->input->post('sales_price', true);
		$sales_percentage = $this->input->post('sales_percentage', true);
		$sales_type = $this->input->post('sales_type', true);
		$customer_id = $this->input->post('customer_id', true);
		
		$update_data = array(
			'payment_id'	=> $payment_id,
			'bank_id'		=> $bank_id,
			'card_no'		=> $card_no,
			'billing_notes'	=> $billing_notes,
			'qc_notes'		=> $qc_notes,
			'single_rate'	=> $single_rate,
			'sales_id'		=> $sales_id,
			'sales_price'		=> $sales_price,
			'sales_percentage'	=> $sales_percentage,
			'sales_type'		=> $sales_type,
			'customer_id'	=> $customer_id,
		);
		
		$r = array('success' => false);
		
		if(empty($billing_id)){
			$r = array('success' => false, 'info' => 'Billing Id Cannot Empty!');
		}else{
					
			//UPDATE BILLING
			$this->db->update($this->table, $update_data, "id = '".$billing_id."'");
			
			$r = array('success' => true, 'info' => 'Bill Info been Saved!', 'retData' => $update_data);
			
		}
		
		die(json_encode($r));
	}
	
	public function printSettlement(){
		
		$session_user = $this->session->userdata('user_username');
		$id_user = $this->session->userdata('id_user');
		$ip_addr = get_client_ip();
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$r = array('success' => false);
		
		$opt_value = array(
			'cashierReceipt_settlement_layout',
			'printer_ip_cashierReceipt_default',
			'printer_pin_cashierReceipt_default',
			'printer_tipe_cashierReceipt_default',
			'printer_ip_cashierReceipt_'.$ip_addr,
			'printer_pin_cashierReceipt_'.$ip_addr,
			'printer_tipe_cashierReceipt_'.$ip_addr
		);
		$get_opt = get_option_value($opt_value);
		
		//Cashier Printer ----------------------
		$printer_ip_cashierReceipt = "\\\\".$ip_addr."\\".$get_opt['printer_ip_cashierReceipt_default'];
		if(!empty($get_opt['printer_ip_cashierReceipt_'.$ip_addr])){
			$printer_ip_cashierReceipt = $get_opt['printer_ip_cashierReceipt_'.$ip_addr];			
			if(strstr($printer_ip_cashierReceipt, '\\')){
				$printer_ip_cashierReceipt = "\\\\".$printer_ip_cashierReceipt;
			}			
		}		
		
		if(empty($get_opt['cashierReceipt_settlement_layout'])){
			$get_opt['cashierReceipt_settlement_layout'] = '';
		}
		$cashierReceipt_settlement_layout = $get_opt['cashierReceipt_settlement_layout'];
		//---------------------- Cashier Printer
		
		$printer_pin_cashierReceipt = '42 CHAR';
		if(!empty($get_opt['printer_pin_cashierReceipt_'.$ip_addr])){
			$printer_pin_cashierReceipt = $get_opt['printer_pin_cashierReceipt_'.$ip_addr];
		}
		
		//trim prod name
		$max_text = 18;
		
		if($printer_pin_cashierReceipt == '32 CHAR'){
			$max_text -= 7;
		}
		if($printer_pin_cashierReceipt == '40 CHAR'){
			$max_text -= 2;
		}
		if($printer_pin_cashierReceipt == '48 CHAR'){
			$max_text += 6;
		}
		
		//TYPE PRINTER
		$printer_type_cashier = '';
		$printer_tipe_cashierReceipt_default = '';
		if(!empty($get_opt['printer_tipe_cashierReceipt_default'])){
			$printer_tipe_cashierReceipt_default = $get_opt['printer_tipe_cashierReceipt_default'];
		}
		if(!empty($get_opt['printer_tipe_cashierReceipt_'.$ip_addr])){
			$printer_type_cashier = $get_opt['printer_tipe_cashierReceipt_'.$ip_addr];
		}
		
		if(empty($printer_type_cashier)){
			$printer_type_cashier = $printer_tipe_cashierReceipt_default;
		}
		
		//TOTAL BILLING - SSR
		$data_post = array();
		$this->table_billing = $this->prefix.'billing';
		$this->table_billing_detail = $this->prefix.'billing_detail';
	
		$get_opt = get_option_value(array('report_place_default','diskon_sebelum_pajak_service','cashier_max_pembulatan','cashier_pembulatan_keatas','pembulatan_dinamis'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(!empty($get_opt['diskon_sebelum_pajak_service'])){
			$data_post['diskon_sebelum_pajak_service'] = $get_opt['diskon_sebelum_pajak_service'];
		}
		if(empty($get_opt['cashier_max_pembulatan'])){
			$get_opt['cashier_max_pembulatan'] = 0;
		}
		if(empty($get_opt['cashier_pembulatan_keatas'])){
			$get_opt['cashier_pembulatan_keatas'] = 0;
		}
		if(empty($get_opt['pembulatan_dinamis'])){
			$get_opt['pembulatan_dinamis'] = 0;
		}
		
		
		$date_from = date("d-m-Y");
		$date_till = date("d-m-Y");
		
		$mktime_dari = strtotime($date_from);
		$mktime_sampai = strtotime($date_till);
					
		$qdate_from = date("Y-m-d",strtotime($date_from));
		$qdate_till = date("Y-m-d",strtotime($date_till));
		$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
		
		$add_where = "(a.payment_date >= '".$qdate_from." 07:00:01' AND a.payment_date <= '".$qdate_till_max." 06:00:00')";
		
		$this->db->select("a.*, a.id as billing_id, a.updated as billing_date, d.payment_type_name, e.bank_name");
		$this->db->from($this->table_billing." as a");
		$this->db->join($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT');
		$this->db->join($this->prefix.'bank as e','e.id = a.bank_id','LEFT');
		$this->db->where("a.billing_status", 'paid');
		$this->db->where("a.is_deleted", 0);
		$this->db->where($add_where);
		$this->db->order_by("payment_date","ASC");
		
		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			$data_post['report_data'] = $get_dt->result_array();				
		}
		
		//PAYMENT DATA
		$dt_payment_name = array();
		$this->db->select('*');
		$this->db->from($this->prefix.'payment_type');
		$get_dt_p = $this->db->get();
		if($get_dt_p->num_rows() > 0){
			foreach($get_dt_p->result_array() as $dtP){
				$dt_payment_name[$dtP['id']] = strtoupper($dtP['payment_type_name']);
			}
		}
		$payment_data = $dt_payment_name;
		
		$default_payment_bank = array();
		//BANK DATA
		$bank_data = array();
		$bank_data[0] = 'CASH';
		$this->db->from($this->prefix.'bank');
		$get_bank = $this->db->get();
		if($get_bank->num_rows() > 0){
			foreach($get_bank->result() as $dtRow){
				$bank_data[$dtRow->id] = $dtRow->bank_name;
				
				if(empty($default_payment_bank[$dtRow->payment_id])){
					$default_payment_bank[$dtRow->payment_id] = $dtRow->id;
				}
				
			}
		}
		
		
		$all_bil_id = array();
		$all_discount_id = array();
		$summary_payment = array();
		
		$data_post['summary_data'] = array(
			'total_billing'	=> 0,
			'total_discount_item'	=> 0,
			'total_discount_billing'	=> 0,
			'net_sales'	=> 0,
			'service_total'	=> 0,
			'tax_total'	=> 0,
			'total_pembulatan'	=> 0,
			'compliment_total'	=> 0,
			'grand_total'	=> 0,
			'total_of_item_discount'	=> 0,
			'total_of_billing'	=> 0,
			'total_of_guest'	=> 0,
			'total_day'	=> 1,
			'sales_without_service'	=> 0,
			'sales_without_tax'	=> 0,
			'sales_per_guest'	=> 0,
			'sales_per_bill'	=> 0,
			'average_daily_guest'	=> 0,
			'average_daily_billing'	=> 0,
			'average_daily_sales'	=> 0,
		);
		
		if(!empty($data_post['report_data'])){
			foreach ($data_post['report_data'] as $s){
				$s['billing_date'] = date("d-m-Y H:i",strtotime($s['created']));					
				$s['payment_date'] = date("d-m-Y H:i",strtotime($s['payment_date']));
				
				if(!in_array($s['id'], $all_bil_id)){
					$all_bil_id[] = $s['id'];
				}		
				
				if(!empty($s['is_compliment'])){
					$s['total_billing'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
					$s['service_total'] = 0;
					$s['tax_total'] = 0;
				}
				
				//diskon_sebelum_pajak_service
				if($data_post['diskon_sebelum_pajak_service'] == 0){
					$s['sub_total'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];		
				}else{
					$s['sub_total'] = $s['total_billing'] - $s['discount_total'] + $s['tax_total'] + $s['service_total'];
					$s['net_sales'] = $s['total_billing'] - $s['discount_total'];
				}
				
				if(!empty($s['discount_id'])){
					if(!in_array($s['discount_id'], $all_discount_id)){
						$all_discount_id[] = $s['discount_id'];
					}
				}
				
				//SPLIT DISCOUNT TYPE
				if(!empty($s['discount_total']) AND $s['discount_perbilling'] == 1){
					$s['discount_billing_total'] = $s['discount_total'];
					$s['discount_total'] = 0;
				}else{
					$s['discount_billing_total'] = 0;
				}
				
				//if(!empty($s['include_tax']) OR !empty($s['include_service'])){
				//	$s['sub_total'] = $s['total_billing'];
				//}
				
				$s['grand_total'] = $s['sub_total'] + $s['total_pembulatan'];
				$s['grand_total'] -= $s['compliment_total'];
				
				//diskon_sebelum_pajak_service
				if($data_post['diskon_sebelum_pajak_service'] == 0){
					$s['grand_total'] -= $s['discount_total'];
					$s['grand_total'] -= $s['discount_billing_total'];
				}
				
				if($s['grand_total'] <= 0){
					$s['grand_total'] = 0;
				}
				
				$s['total_pembulatan_show'] = priceFormat($s['total_pembulatan']);
				
				if($s['total_pembulatan'] < 0){
					$s['total_pembulatan_show'] = "(".priceFormat($s['total_pembulatan']).")";
				}
				
				$s['sub_total_show'] = priceFormat($s['sub_total']);
				$s['net_sales_show'] = priceFormat($s['net_sales']);
				$s['grand_total_show'] = priceFormat($s['grand_total']);
				$s['total_billing_show'] = priceFormat($s['total_billing']);
				$s['total_paid_show'] = priceFormat($s['total_paid']);
				$s['tax_total_show'] = priceFormat($s['tax_total']);
				$s['service_total_show'] = priceFormat($s['service_total']);
				$s['discount_total_show'] = priceFormat($s['discount_total']);
				$s['discount_billing_total_show'] = priceFormat($s['discount_billing_total']);
				
				//DP
				$s['total_dp_show'] = priceFormat($s['total_dp']);
				/*if($s['total_cash'] == 0){
					if($s['total_credit'] > $s['total_dp']){
						$s['total_credit'] -= $s['total_dp'];
					}
				}else{
					if($s['total_cash'] > $s['total_dp']){
						$s['total_cash'] -= $s['total_dp'];
					}
				}*/
				
				$s['total_compliment'] = 0;
				$s['total_compliment_show'] = 0;

				$s['total_hpp'] = 0;
				$s['total_hpp_show'] = 0;
				$s['total_profit'] = 0;
				$s['total_profit_show'] = 0;
				
				//CARD NO 
				$card_no = '';
				if(strlen($s['card_no']) > 30){
					$card_no = $s['card_no'];
					$card_no = str_replace(";","",$card_no);
					$card_no = str_replace("?","",$card_no);
					$card_no_exp = explode("=", $card_no);
					if(!empty($card_no_exp[0])){
						$card_no = trim($card_no_exp[0]);
					}
				}else{
					$card_no = trim($s['card_no']);
				}
				
				//NOTES
				$s['payment_note'] = '';
				if(!empty($s['is_compliment']) OR !empty($s['compliment_total'])){
					$s['payment_note'] = 'COMPLIMENT';
					//$s['total_compliment'] = $s['grand_total'];
					$s['total_compliment'] = $s['compliment_total'];
					$s['total_compliment_show'] = priceFormat($s['total_compliment']);
					//$s['is_compliment'] = 1;
				}else{
				
					if(!empty($s['is_half_payment'])){
						$s['payment_note'] = 'HALF PAYMENT';
					}
					
					if(strtolower($s['payment_type_name']) != 'cash'){
						$s['payment_note'] = strtoupper($s['bank_name']).' '.$card_no;
					}
				}
				
				if(!empty($s['billing_notes'])){
					if(!empty($s['payment_note'])){
						$s['payment_note'] .= '<br/>'.$s['billing_notes'];
					}else{
						$s['payment_note'] .= $s['billing_notes'];
					}
				}
				
				//if($s['billing_no'] == '1601010055'){
					//echo '<pre>';
					//print_r($s);
					//die();
				//}
				
				$data_post['summary_data']['total_billing'] += $s['total_billing'];
				$data_post['summary_data']['total_discount_item'] += $s['discount_total'];
				$data_post['summary_data']['total_discount_billing'] += $s['discount_billing_total'];
				$data_post['summary_data']['service_total'] += $s['service_total'];
				$data_post['summary_data']['tax_total'] += $s['tax_total'];
				$data_post['summary_data']['total_pembulatan'] += $s['total_pembulatan'];
				$data_post['summary_data']['compliment_total'] += $s['compliment_total'];
				$data_post['summary_data']['grand_total'] += $s['grand_total'];
				$data_post['summary_data']['total_of_guest'] += $s['total_guest'];
				$data_post['summary_data']['total_of_billing'] += 1;
				
				if($s['service_total'] == 0){
					$data_post['summary_data']['sales_without_service'] += $s['grand_total'];
				}
				if($s['tax_total'] == 0){
					$data_post['summary_data']['sales_without_tax'] += $s['grand_total'];
				}
				
				
				//SUMMARY PAYMENT
				if(empty($s['bank_id'])){
					$s['bank_id'] = 0;
					
					if($s['payment_id'] == 2 OR $s['payment_id'] == 3){
						if(!empty($default_payment_bank[$s['payment_id']])){
							$s['bank_id'] = $default_payment_bank[$s['payment_id']];
						}
						
					}
					
				}
				
				$var_payment = $s['bank_id'];
				if(empty($summary_payment[$var_payment])){
					
					$bank_name = 'CASH';
					if(!empty($bank_data[$s['bank_id']])){
						$bank_name = $bank_data[$s['bank_id']];
					}
					$payment_name = 'CASH';
					if(!empty($dt_payment_name[$s['payment_id']])){
						$payment_name = $dt_payment_name[$s['payment_id']];
					}
					
					$summary_payment[$var_payment] = array(
						'payment_id'	=> $s['payment_id'],
						'payment_name'	=> $payment_name,
						'bank_id'	=> $s['bank_id'],
						'bank_name'	=> $bank_name,
						'total_billing'	=> 0,
						'total_billing_show'	=> 0,
						'discount_total'	=> 0,
						'discount_total_show'	=> 0,
						'discount_billing_total'	=> 0,
						'discount_billing_total_show'	=> 0,
						'tax_total'	=> 0,
						'tax_total_show'	=> 0,
						'service_total'	=> 0,
						'service_total_show'	=> 0,
						'sub_total'	=> 0,
						'sub_total_show'	=> 0,
						'net_sales'	=> 0,
						'net_sales_show'	=> 0,
						'total_pembulatan'	=> 0,
						'total_pembulatan_show'	=> 0,
						'total_compliment'	=> 0,
						'total_compliment_show'	=> 0,
						'grand_total'	=> 0,
						'grand_total_show'	=> 0,
						'total_qty'	=> 0,
						'total_hpp'	=> 0,
						'total_hpp_show'	=> 0,
						'compliment_total'	=> 0,
						'compliment_total_show'	=> 0,
						'total_dp'	=> 0,
						'total_dp_show'	=> 0,
						'total_profit'	=> 0,
						'total_profit_show'	=> 0
					);
					
					if(!empty($payment_data)){
						foreach($payment_data as $key_id => $dtPay){
							$summary_payment[$var_payment]['payment_'.$key_id] = 0;	
							$summary_payment[$var_payment]['payment_'.$key_id.'_show'] = 0;						
						}
					}
					
				}
				
				$summary_payment[$var_payment]['total_qty'] += 1;
				$summary_payment[$var_payment]['total_billing'] += $s['total_billing'];
				$summary_payment[$var_payment]['discount_total'] += $s['discount_total'];
				$summary_payment[$var_payment]['discount_billing_total'] += $s['discount_billing_total'];
				$summary_payment[$var_payment]['tax_total'] += $s['tax_total'];
				$summary_payment[$var_payment]['service_total'] += $s['service_total'];
				$summary_payment[$var_payment]['sub_total'] += $s['sub_total'];
				$summary_payment[$var_payment]['net_sales'] += $s['net_sales'];
				$summary_payment[$var_payment]['total_pembulatan'] += $s['total_pembulatan'];
				$summary_payment[$var_payment]['grand_total'] += $s['grand_total'];
				$summary_payment[$var_payment]['total_compliment'] += $s['total_compliment'];
				$summary_payment[$var_payment]['compliment_total'] += $s['compliment_total'];
				$summary_payment[$var_payment]['total_dp'] += $s['total_dp'];
				
				
				if(!empty($payment_data)){
					foreach($payment_data as $key_id => $dtPay){
				
						$tot_payment = 0;
						$tot_payment_show = 0;
						if($s['payment_id'] == $key_id){
							//$tot_payment = $s['grand_total'];
							//$tot_payment_show = $s['grand_total_show'];
							
							if($key_id == 3 OR $key_id == 2){
								$tot_payment = $s['total_credit'];	
							}else{
								$tot_payment = $s['total_cash'];	
							}
							
							$tot_payment_show = priceFormat($tot_payment);
							
							//credit half payment
							if(!empty($s['is_half_payment']) AND $key_id != 1){
								$tot_payment = $s['total_credit'];
								$tot_payment_show = priceFormat($s['total_credit']);
							}else{
								
								$tot_payment_show = priceFormat($tot_payment);	
							}
								
						}else{
							//cash
							if(!empty($s['is_half_payment']) AND $key_id == 1){
								$tot_payment = $s['total_cash'];
								$tot_payment_show = priceFormat($s['total_cash']);
							}
						}
				
						if(empty($grand_total_payment[$key_id])){
							$grand_total_payment[$key_id] = 0;
						}
				
						if(!empty($s['is_compliment'])){
							$tot_payment = 0;
							$tot_payment_show = 0;
						}
						
						$summary_payment[$var_payment]['payment_'.$key_id] += $tot_payment;
														
					}
				}
				
				
				//$newData[$s['id']] = $s;
				
			}
		}
		
		//GROUP PAYMENT
		$summary_payment_group = array();
		if(!empty($summary_payment)){
			foreach($summary_payment as $dt){
				if(empty($summary_payment_group[$dt['payment_id']])){
					$summary_payment_group[$dt['payment_id']] = array();
				}
				
				$summary_payment_group[$dt['payment_id']][] = $dt;
			}
		}
		
		//echo '<pre>';
		//print_r($data_post['summary_data']);
		//echo '<pre>';
		//print_r($summary_payment);
		//die();
		
		$menu_sales = printer_command_align_right(priceFormat($data_post['summary_data']['total_billing']), 11);
		$disc_per_item = printer_command_align_right(priceFormat($data_post['summary_data']['total_discount_item']), 11);
		
		$menu_net_sales_count = ($data_post['summary_data']['total_billing']-$data_post['summary_data']['total_discount_item']);
		$menu_net_sales = printer_command_align_right(priceFormat($menu_net_sales_count), 11);
		$disc_per_billing = printer_command_align_right(priceFormat($data_post['summary_data']['total_discount_billing']), 11);
		
		$total_net_sales_count = ($menu_net_sales_count-$data_post['summary_data']['total_discount_item']);
		$total_net_sales = printer_command_align_right(priceFormat($total_net_sales_count), 11);
		
		$service_total = printer_command_align_right(priceFormat($data_post['summary_data']['service_total']), 11);
		$tax_total = printer_command_align_right(priceFormat($data_post['summary_data']['tax_total']), 11);
		$total_pembulatan = printer_command_align_right(priceFormat($data_post['summary_data']['total_pembulatan']), 11);
		$compliment_total = printer_command_align_right(priceFormat($data_post['summary_data']['compliment_total']), 11);
		$grand_total = printer_command_align_right(priceFormat($data_post['summary_data']['grand_total']), 11);
		
		$all_summary_data = "[size=1][align=0]SALES SUMMARY[tab]\n";
		$all_summary_data .= "[size=0]";
		$all_summary_data .= "[align=0][tab]MENU SALES[tab]".$menu_sales."\n"; 
		$all_summary_data .= "[align=0][tab]DISC/ITEM[tab]".$disc_per_item."\n"; 
		$all_summary_data .= "[align=0][tab]NET SALES[tab]".$menu_net_sales."\n"; 
		$all_summary_data .= "[align=0][tab]DISC/BILLING[tab]".$disc_per_billing."\n"; 
		$all_summary_data .= "[align=0][tab]TOTAL NET SALES[tab]".$total_net_sales."\n"; 
		$all_summary_data .= "[align=0][tab]SERVICE[tab]".$service_total."\n"; 
		$all_summary_data .= "[align=0][tab]TAX[tab]".$tax_total."\n"; 
		$all_summary_data .= "[align=0][tab]PEMBULATAN[tab]".$total_pembulatan."\n"; 
		$all_summary_data .= "[align=0][tab]TOTAL SALES[tab]".$grand_total; 
		if(!empty($data_post['summary_data']['compliment_total'])){
			$all_summary_data .= "\n[align=0][tab]COMPLIMENT[tab]".$compliment_total; 
		}
		
		
		$all_payment_data = '';
		if(!empty($summary_payment_group)){
			foreach($summary_payment_group as $key => $dt_detail){
				
				$no_payment = 0;
				if(!empty($dt_detail)){
					foreach($dt_detail as $dt){
						
						$no_payment++;
						$payment_name = ucwords(str_replace("_"," ",$dt['payment_name']));
						$data_name = ucwords(str_replace("_"," ",$dt['bank_name']));
						if(strlen($data_name) > $max_text){
							//skip on last space
							$explTxt = explode(" ",$data_name);
							
							$no_exp = 1;
							$tot_txt = 0;
							$text_display = '';
							foreach($explTxt as $txt){
								$lnTxt = strlen($txt);
								$tot_txt += $lnTxt;
								
								if($tot_txt > 0){
									$tot_txt+=1; //space
								}
								
								if($tot_txt > $max_text){
									$all_text_array[] = $text_display;
									$tot_txt = 0;
									$text_display = $txt;
									
									//echo '2. '.$text_display.' '.$tot_txt.'<br/>';
									
								}else{
								
									if(empty($text_display)){
										$text_display = $txt;
									}else{
										$text_display .= ' '.$txt;										
									}
									
									//echo '1. '.$text_display.' '.$tot_txt.'<br/>';
									
								}
								
								if(count($explTxt) == $no_exp){
									$all_text_array[] = $text_display;
								}
								
								$no_exp++;
							}
							
							if(empty($all_text_array[0])){
								$data_name = substr($data_name, 0, $max_text);
							}else{
								$data_name = $all_text_array[0];
							}
						}
						
						if(empty($all_payment_data)){
							$all_payment_data = "[size=1][align=0]PAYMENT SUMMARY[tab]\n";
							$all_payment_data .= "[size=0]";
						}
						
						$value_show = printer_command_align_right(priceFormat($dt['payment_'.$key]), 11);
						
						if($no_payment == 1 AND count($dt_detail) == 1){
							$all_payment_data .= "[tab]".$payment_name."[tab]".$value_show."\n"; 
						}else{
							if($no_payment == 1){
								$all_payment_data .= $payment_name."\n";
							}
							$all_payment_data .= "[align=0]".$data_name."[tab] [tab]".$value_show."\n";
						}
						
						 
						
					}
				}
				
				
			}
		}
		
		
		$print_attr = array(
			"{tanggal_shift}"		=> date("d/m/Y"),
			"{jam_shift}"			=> date("H:i"),
			"{summary_data}"			=> $all_summary_data,
			"{payment_data}"			=> $all_payment_data
		);
		
		$print_content_cashierReceipt = strtr($cashierReceipt_settlement_layout, $print_attr);
		
		
		$print_content_cashierReceipt = replace_to_printer_command($print_content_cashierReceipt, $printer_type_cashier, $printer_pin_cashierReceipt);
		
		$r = array('success' => false, 'info' => '', 'print' => array());
		
		//echo '<pre>';
		//print_r($print_content_cashierReceipt);
		//die();
		
		
		$r['print'][] = $print_content_cashierReceipt;
		//DIRECT PRINT USING PHP - CASHIER PRINTER				
		$is_print_error = false;
		
		try {
			$ph = printer_open($printer_ip_cashierReceipt);
		} catch (Exception $e) {
			$ph = false;
		}
		
		//$ph = @printer_open($printer_ip_cashierReceipt);
		
		if($ph)
		{	
			printer_start_doc($ph, "CLOSE CASHIER - SETTLEMENT");
			printer_start_page($ph);
			printer_set_option($ph, PRINTER_MODE, "RAW");
			printer_write($ph, $print_content_cashierReceipt);
			printer_end_page($ph);
			printer_end_doc($ph);
			printer_close($ph);
			$r['success'] = true;
			
		}else{
			$is_print_error = true;
		}
		
		if($is_print_error){					
			$r['info'] .= 'Communication with Printer Cashier Failed!<br/>';
		}
		
		echo json_encode($r);
		die();
	}
	
}