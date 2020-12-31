<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class BillingCashier extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_billingcashier', 'mcashier');
		$this->load->model('model_billingcashierdetail', 'mdetail');
		$this->load->model('model_billingcashierfitur', 'mfitur');
		$this->load->model('model_billingcashierprint', 'mprint');
		$this->load->model('inventory/model_stock', 'stock');
		$this->load->model('inventory/model_usagewaste', 'usagewaste');
		$this->load->model('account_receivable/model_account_receivable', 'account_receivable');
		$this->load->model('cashflow/model_penerimaan_kas', 'penerimaan_kas');
		$this->load->model('master_pos/model_masterproductpriceqty', 'priceqty');
		
		/*helper billing*/
		$this->load->helper('billing');
	}

	/*GRID DATA*/
	public function gridData_billingDetail(){
		
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
		$get_data = $this->mcashier->find_all($params);
		  		
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
				
				//update-2001.002
				if(!empty($s['include_tax']) OR !empty($s['include_service'])){
					$s['product_detail_info'] = $s['product_name'].'<br/>X @ Rp.'.priceFormat($s['product_price_real']);	
				}else{
					$s['product_detail_info'] = $s['product_name'].'<br/>X @ Rp.'.priceFormat($s['product_price']);	
				}
							
				
				$no++;
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
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
			$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal tersebut sudah ditutup!'); 
			die(json_encode($r));
		}
		
		//NO HOLD BILLING
		$opt_var = array('no_hold_billing','as_server_backup');
		$get_opt = get_option_value($opt_var);
		
		cek_server_backup($get_opt);
		
		if(!empty($get_opt['no_hold_billing'])){
			$this->db->select("b.id, b.billing_no");
			$this->db->from($this->table." as b");
			$this->db->where("b.billing_status = 'hold'");
			$this->db->where("b.created >= '".date("Y-m-d 00:00:00")."'");
			
			$get_hold_billing = $this->db->get();
			if($get_hold_billing->num_rows() > 0){
				$data_hold_billing = $get_hold_billing->row();
				$r = array('success' => false, 'info' => 'Silahkan gunakan/selesaikan billing: <b>'.$data_hold_billing->billing_no.'</b><br/>Tidak boleh ada hold/gantung billing'); 
				die(json_encode($r));
			}
			
		}
		
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
						$r = array('success' => false, 'info' => 'Hold Billing, Gagal!');
						echo json_encode($r);
						die();
					}
				}
				
			}else{
				$r = array('success' => false, 'info' => 'Billing Id: #'.$hold_billing_id.' tidak ditemukan!');
				echo json_encode($r);
				die();
			}
			
			
		}
		
		$billingData = getBilling();
		if($billingData == false OR empty($billingData->billing_id)){
			$r = array('success' => false, 'info' => 'Membuat Billing Baru, Gagal!');
			echo json_encode($r);
			die();
		}
		
		if(!empty($billingData->created)){
			$billingData->created_datetime = date('d.m.Y H:i', strtotime($billingData->created));
			
			//SAVE TO LOG
			//logBilling($billingData, 'Create', 'Membuat Billing '.$billingData->billing_no);
		}
		
		$r = array('success' => true, 'billingData' => $billingData); 
		echo json_encode($r);
		die();
	}
	
	public function loadBilling(){
		
		
		$billing_id = $this->input->post('billing_id', true);
		
		$r = array('success' => false, 'info' => 'Billing Id tidak ditemukan!');
		if(empty($billing_id)){
			echo json_encode($r);
			die();
		}
		
		$getBilling = getBilling($billing_id);	
		
		$update_billing = calculateBilling($billing_id);
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
		
		//update-2001.002
		$getBilling->created_datetime = date('d-m-Y H:i', strtotime($getBilling->created));
		
		$r = array('success' => true, 'billingData'	=> $getBilling);
		echo json_encode($r);
		die();
		
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
			//	$r = array('success' => false, 'info' => 'Billing: '.$billingData->billing_no.' sudah dibayar!<br/>Cannot Void/Cancel Billing, Silahkan lakukan Refresh List Billing'); 
			//	echo json_encode($r);
			//	die();
			//}
		}else{
			$r = array('success' => false, 'info' => 'Billing Id: #'.$cancel_billing_id.' tidak ditemukan!');
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
			$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal tersebut sudah ditutup!'); 
			die(json_encode($r));
		}
		
		$cancelBilling = false;
		if(!empty($cancel_billing_id)){
			$cancelBilling = $this->doCancelBilling($cancel_billing_id, $is_paid, $cancel_notes);
			if($cancelBilling == false){
				$r = array('success' => false, 'info' => 'Void/Cancel Billing, Gagal!');
				echo json_encode($r);
				die();
			}
		}
				
		//$billingData = array();
		$r = array('success' => true, 'billingData' => $billingData); 
		
		//SAVE TO LOG
		logBilling($billingData, 'Cancel', 'Void/Cancel Billing '.$billingData->billing_no);
		
		echo json_encode($r);
		die();
	}
	
	public function doCancelBilling($billing_id = '', $is_paid = false, $cancel_notes = ''){
		
		$this->table = $this->prefix.'billing';				
		$this->table2 = $this->prefix.'billing_detail';				
		$this->table_inv = $this->prefix.'table_inventory';				
		$this->table_storehouse_users = $this->prefix.'storehouse_users';				
		$session_user = $this->session->userdata('user_username');
		$role_id = $this->session->userdata('role_id');
		$id_user = $this->session->userdata('id_user');
		
		//STOCK
		$this->table_usagewaste = $this->prefix.'usagewaste';		
		$this->table_product = $this->prefix.'product';		
		$this->table_items = $this->prefix.'items';		
		$this->table_product_gramasi = $this->prefix.'product_gramasi';		
		$this->table_product_package = $this->prefix.'product_package';		
						
		$this->prefix_acc = config_item('db_prefix3');
		$this->table_account_receivable = $this->prefix_acc.'account_receivable';
		
		//hanya bisa cancel oleh cashier
		$get_opt_var = array('role_id_kasir','as_server_backup');
		$get_opt = get_option_value($get_opt_var);
		
		cek_server_backup($get_opt);
		
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
		
		
		$this->db->select('id, id as billing_id, table_id, billing_no');
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
				$update = $this->mcashier->save($var, $billing_id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				
				//PRINT VOID PAID - CANCEL
				if($is_paid == true AND !empty($billing_id)){
					//$this->doPrint('void_paid_cancel', $billing_id);
				}
				
				//update-2001.002
				$dt_update = array(
					'status'	=> 'available',
					'billing_no'	=> ''
				);
				$this->db->update($this->table_inv, $dt_update, "billing_no = '".$data_billing->billing_no."'");
				
				
				//update-1912-002
				$opt_value = array(
					'wepos_tipe','retail_warehouse','autocut_stok_sales_to_usage','autocut_stok_sales'
				);
				
				$get_opt = get_option_value($opt_value);
				
				$wepos_tipe = 'cafe';
				if(!empty($get_opt['wepos_tipe'])){
					$wepos_tipe = $get_opt['wepos_tipe'];
				}
				
				//update-2003.001
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
				
				if(empty($retail_warehouse)){
					$r = array('success' => false, 'info' => 'Silahkan lakukan Set Stock Warehouse/Gudang!');
					echo json_encode($r);
					die();
				}
				
				$autocut_stok_sales_to_usage = 0;
				if(!empty($get_opt['autocut_stok_sales_to_usage'])){
					$autocut_stok_sales_to_usage = $get_opt['autocut_stok_sales_to_usage'];
				}
				
				//update-1912-002
				$autocut_stok_sales = 0;
				if(!empty($get_opt['autocut_stok_sales'])){
					$autocut_stok_sales = $get_opt['autocut_stok_sales'];
				}
				
				//stok
				if(!empty($retail_warehouse)){
					
					$get_billno_y = substr($data_billing->billing_no,0,2);
					$get_billno_m = substr($data_billing->billing_no,2,2);
					$get_billno_d = substr($data_billing->billing_no,4,2);
					$billing_date = (2000+$get_billno_y)."-".$get_billno_m."-".$get_billno_d;
					
					if($autocut_stok_sales_to_usage == 1){
						
						$update_stok = 'usage_rollback';
						$return_data = $this->mdetail->billingDetail($billing_id, $retail_warehouse, $update_stok);
						
						//$date_now = date("Y-m-d");
						$params = array(
							'date_now'			=> $billing_date,
							'all_item_usage'	=> $return_data['all_item_usage'],
							'retail_warehouse'	=> $retail_warehouse,
							'rollback'			=> true,
						);
						
						$ret_usage = $this->usagewaste->save_sales_usage($params);
						
					}else{
						
						//update-1912-002
						if($autocut_stok_sales == 1){
							
							$update_stok = 'rollback';
							$return_data = $this->mdetail->billingDetail($billing_id, $retail_warehouse, $update_stok);
							
							if(!empty($return_data['update_stock'])){
								
								$r['update_stock'] = $return_data['update_stock'];
								$post_params = array(
									'storehouse_item'	=> $return_data['update_stock'],
									'date'				=> $billing_date,
								);
								
								$updateStock = $this->stock->update_stock_rekap($post_params);
								
							}
						}
						
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
				$r = array('success' => false, 'info' => 'Billing Id: #'.$hold_billing_id.' tidak ditemukan!');
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
					$r = array('success' => false, 'info' => 'Billing: '.$billingData->billing_no.' sudah dibayar!<br/>Tidak dapat melakukan Hold Billing, Silahkan lakukan Refresh List Billing'); 
					echo json_encode($r);
					die();
				}
				
			}else{
				$r = array('success' => false, 'info' => 'Billing Id: #'.$hold_billing_id.' tidak ditemukan!');
				echo json_encode($r);
				die();
			}
			
			//NO HOLD BILLING
			$opt_var = array('no_hold_billing','as_server_backup');
			$get_opt = get_option_value($opt_var);
			
			cek_server_backup($get_opt);
			
			if(!empty($get_opt['no_hold_billing']) AND !empty($billingData)){
				
				if($billingData->billing_status == 'hold'){
					$r = array('success' => false, 'info' => 'Silahkan gunakan/selesaikan billing: <b>'.$billingData->billing_no.'</b><br/>Tidak boleh ada hold/gantung billing'); 
					die(json_encode($r));
				}
				
			}
		}
		
		//CLOSING DATE
		$var_closing = array(
			'xdate'	=> $billingData->created,
			'xtipe'	=> 'sales'
		);
		$is_closing = is_closing($var_closing);
		if($is_closing){
			$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal tersebut sudah ditutup!'); 
			die(json_encode($r));
		}
		
		$holdBilling = false;
		if(!empty($hold_billing_id)){
			$holdBilling = $this->doHoldBilling($hold_billing_id);
			if($holdBilling == false){
				$r = array('success' => false, 'info' => 'Hold Billing, Gagal!');
				echo json_encode($r);
				die();
			}
		}
		
		//$billingData = array();
		$r = array('success' => true, 'billingData' => $billingData); 
		
		if(!empty($billingData)){
			//SAVE TO LOG
			//logBilling($billingData, 'Hold', 'Hold Billing '.$billingData->billing_no);
		}
		
		echo json_encode($r);
		die();
	}
	
	public function doHoldBilling($billing_id = ''){
		
		$this->table = $this->prefix.'billing';				
		$this->table2 = $this->prefix.'billing_detail';				
		$this->table_inv = $this->prefix.'table_inventory';				
		$this->table_storehouse_users = $this->prefix.'storehouse_users';				
		$session_user = $this->session->userdata('user_username');
		$role_id = $this->session->userdata('role_id');
		$id_user = $this->session->userdata('id_user');
		
		//STOCK
		$this->table_usagewaste = $this->prefix.'usagewaste';		
		$this->table_product = $this->prefix.'product';		
		$this->table_items = $this->prefix.'items';		
		$this->table_product_gramasi = $this->prefix.'product_gramasi';		
						
		$this->prefix_acc = config_item('db_prefix3');
		$this->table_account_receivable = $this->prefix_acc.'account_receivable';	
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		if(empty($billing_id)){
			return false;			
		}
		
		$r = array('success' => true, 'id' => $billing_id);
		
		//update-2007.001
		$opt_value = array(
			'wepos_tipe','retail_warehouse','autocut_stok_sales_to_usage','autocut_stok_sales',
			'diskon_sebelum_pajak_service','cashier_credit_ar','no_hold_billing','as_server_backup',
			'tandai_pajak_billing','nontrx_sales_auto','nontrx_override_on','nontrx_allow_zero','current_date'
		);
		
		$get_opt = get_option_value($opt_value);
		
		cek_server_backup($get_opt);
		
		$wepos_tipe = 'cafe';
		if(!empty($get_opt['wepos_tipe'])){
			$wepos_tipe = $get_opt['wepos_tipe'];
		}
		
		//update-2003.001
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
				
		$autocut_stok_sales_to_usage = 0;
		if(!empty($get_opt['autocut_stok_sales_to_usage'])){
			$autocut_stok_sales_to_usage = $get_opt['autocut_stok_sales_to_usage'];
		}
		
		//update-1912-002		
		$autocut_stok_sales = 0;
		if(!empty($get_opt['autocut_stok_sales'])){
			$autocut_stok_sales = $get_opt['autocut_stok_sales'];
		}
		
		$diskon_sebelum_pajak_service = 0;
		if(!empty($get_opt['diskon_sebelum_pajak_service'])){
			$diskon_sebelum_pajak_service = $get_opt['diskon_sebelum_pajak_service'];
		}
		
		$cashier_credit_ar = 0;
		if(!empty($get_opt['cashier_credit_ar'])){
			$cashier_credit_ar = $get_opt['cashier_credit_ar'];
		}
		
		$billingData = array();
		$this->db->select('*, id as billing_id');
		$this->db->from($this->table);
		$this->db->where('id', $billing_id);
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$billingData = $get_last->row();		
		}
		
		//NO HOLD BILLING
		if(!empty($get_opt['no_hold_billing']) AND !empty($billingData)){
			
			if($billingData->billing_status == 'hold'){
				$r = array('success' => false, 'info' => 'Silahkan gunakan/selesaikan billing: <b>'.$billingData->billing_no.'</b><br/>Tidak boleh ada hold/gantung billing'); 
				die(json_encode($r));
			}
			
		}
		
		$date_now = date('Y-m-d H:i:s');
		
		//update-1912-001
		if(!empty($billingData)){
			//update status to hold
			$var = array('fields'	=>	array(
				    'is_half_payment'=> 0,
				    'total_cash'	=> 0,
				    'total_credit'	=> 0,
				    'payment_id'	=> 0,
				    'bank_id'		=> 0,
				    'card_no'  		=> '',
				    'billing_status'  => 'hold',
					'updated'		=>	$date_now,
					'updatedby'		=>	$session_user
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			//UPDATE BILLING
			$this->lib_trans->begin();
				$update = $this->mcashier->save($var, $billing_id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				if($billingData->billing_status == 'paid'){
					if(!empty($retail_warehouse)){
						
						$get_billno_y = substr($billingData->billing_no,0,2);
						$get_billno_m = substr($billingData->billing_no,2,2);
						$get_billno_d = substr($billingData->billing_no,4,2);
						$billing_date = (2000+$get_billno_y)."-".$get_billno_m."-".$get_billno_d;
						
						if($autocut_stok_sales_to_usage == 1){
						
							$update_stok = 'usage_rollback';
							$return_data = $this->mdetail->billingDetail($billing_id, $retail_warehouse, $update_stok);
						
							//save if usage available for today
							//$date_now = date("Y-m-d");
							$params = array(
								'date_now'			=> $billing_date,
								'all_item_usage'	=> $return_data['all_item_usage'],
								'retail_warehouse'	=> $retail_warehouse,
								'rollback'			=> true,
							);
							$ret_usage = $this->usagewaste->save_sales_usage($params);
							
						}else{
							
							//update-1912-002
							if($autocut_stok_sales == 1){
								$update_stok = 'rollback';
								$return_data = $this->mdetail->billingDetail($billing_id, $retail_warehouse, $update_stok);
								
								if(!empty($return_data['update_stock'])){
									
									$r['update_stock'] = $return_data['update_stock'];
									$post_params = array(
										'storehouse_item'	=> $return_data['update_stock'],
										'date'				=> $billing_date,
									);
									
									$updateStock = $this->stock->update_stock_rekap($post_params);
									
								}
							}
							
						}
						
					}
					
					if($billingData->payment_id == 4 AND $cashier_credit_ar == 1){
						//payment done - progres
						$updateAR = $this->account_receivable->set_account_receivable_Sales($billing_id, $billingData->billing_status);
						//$updateCF = $this->penerimaan_kas->set_DP_Sales($billing_id, $billingData->billing_status);
						
						if($updateAR === true || $updateAR === false){
							$r['updateSales'] = $billingData->billing_status.' to Paid';
						}else
						if($updateAR == 'invoice'){
							
							$no_invoice = '-';
							$this->db->from($this->table_account_receivable);
							$this->db->where("ar_tipe = 'sales'");
							$this->db->where("ref_id = '".$billing_id."'");
							$get_ar = $this->db->get();
							if($get_ar->num_rows() > 0){
								
								$data_AR = $get_ar->row();
								$no_invoice = $data_AR->no_invoice;
								
							}
							
							$r['success'] = false;
							$r['info'] = 'Silahkan Cek dan Hapus Invoice: '.$no_invoice.' terkait Sales: '.$billingData->billing_no;
							$r['updateSales'] = $billingData->billing_status.' to Paid';
							$r['updateAR'] = $updateAR;
							
							$rollback_reservation_status = array(
								'billing_status'	=> $billingData->billing_status
							);
							$this->db->update($this->table, $rollback_reservation_status, "id = '".$billing_id."'");
							
						}
						
					}
					
					//update-2011.001
					if(!empty($billingData->txmark)){
						$this->mfitur->override_nontrx($billing_id, $billingData->tax_total, $billingData->total_billing, true, $get_opt);
					}
					
				}
			
				//SAVE TO LOG
				logBilling($billingData, 'Hold', 'Hold Billing '.$billingData->billing_no);
				
				if($r['success'] == false){
					//$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
					echo json_encode($r);
					die();
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
	
	/*ORDER*/
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
		$product_price_before_promo = $this->input->post('product_normal_price');
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
		
		$product_type = $this->input->post('product_type');
		$package_item = $this->input->post('package_item');
		$use_stok_kode_unik = $this->input->post('use_stok_kode_unik');
		$data_stok_kode_unik = $this->input->post('data_stok_kode_unik');
		
		//EDIT ID
		$id = $this->input->post('id', true);
			
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
		$is_express = $this->input->post('is_express');
		
		if(empty($main_billing_id)){
			
			$date_now = date('Y-m-d');
			//CLOSING DATE
			$var_closing = array(
				'xdate'	=> $date_now,
				'xtipe'	=> 'sales'
			);
			$is_closing = is_closing($var_closing);
			if($is_closing){
				$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal tersebut sudah ditutup!', 'billingData' => ''); 
				die(json_encode($r));
			}
		}
		
		if($form_type_orderProduct == 'add'){
			//Check OOO Menu
			$this->db->select("*");
			$this->db->from($this->prefix.'ooo_menu');
			$this->db->where("product_id = ".$product_id." AND tanggal = '".date("Y-m-d")."' AND is_deleted = 0");
			$get_ooo = $this->db->get();
			if($get_ooo->num_rows() > 0){
				$dt_ooo = $get_ooo->row();
				$r = array('success' => false, 'info' => 'Product/Menu Habis (Out of Order)<br/>Ket: '.$dt_ooo->keterangan); 
				die(json_encode($r));
			}
		}	
		
		//update-2003.001
		if($package_item == 1 OR $free_item == 1){
			
			$this->db->from($this->table2);
			$this->db->where("id = ".$id);
			$get_old_detail = $this->db->get();
			if($get_old_detail->num_rows() > 0){
				$dt_old_detail = $get_old_detail->row();
				if($dt_old_detail->order_qty != $order_qty){
					
					if($package_item == 1){
						$r = array('success' => false, 'info' => '<br/>Silahkan Ubah Qty di Menu Paket Terkait', 'billingData' => ''); 
					}else{
						$r = array('success' => false, 'info' => '<br/>Silahkan Ubah Qty di Menu Terkait', 'billingData' => ''); 
					}
					
					die(json_encode($r));
				}
			}else{
				if($package_item == 1){
					$r = array('success' => false, 'info' => 'Ubah Order Item Package Gagal!', 'billingData' => ''); 
				}else{
					$r = array('success' => false, 'info' => 'Ubah Order Item Gagal!', 'billingData' => ''); 
				}
				die(json_encode($r));
			}
			
		}
		
		//update-1912-001
		//NO HOLD BILLING
		$opt_value = array(
			'cashier_max_pembulatan',
			'cashier_pembulatan_keatas',
			'pembulatan_dinamis',
			'use_order_counter',
			'wepos_tipe',
			'save_order_note',
			'as_server_backup',
			'no_hold_billing',
			'all_status_order_printed'
		);
		$get_opt = get_option_value($opt_value);
		
		if(empty($main_billing_id)){
			
			cek_server_backup($get_opt);
			
			if(!empty($get_opt['no_hold_billing'])){
				$this->db->select("b.id, b.billing_no");
				$this->db->from($this->table." as b");
				$this->db->where("b.billing_status = 'hold'");
				$this->db->where("b.created >= '".date("Y-m-d 00:00:07")."'");
				
				$get_hold_billing = $this->db->get();
				if($get_hold_billing->num_rows() > 0){
					$data_hold_billing = $get_hold_billing->row();
					$r = array('success' => false, 'info' => 'Silahkan gunakan/selesaikan billing: <b>'.$data_hold_billing->billing_no.'</b><br/>Tidak boleh ada hold/gantung billing'); 
					die(json_encode($r));
				}
				
			}
		}
		
		//CREATE BILLING WITH USER - IF EMPTY
		$billingData = getBilling($main_billing_id);	
			
		if($form_type_orderProduct == 'add'){
			$main_billing_id = $billingData->billing_id;
			if(!empty($billingData->created)){
				$billingData->created_datetime = date('d-m-Y H:i', strtotime($billingData->created));
				
				//SAVE TO LOG
				//logBilling($billingData, 'Create', 'Membuat Billing '.$billingData->billing_no);
			}			
		}
		
		if($form_type_orderProduct == 'add' AND $is_express == 1 AND !empty($main_billing_id))
		{
			$this->db->from($this->table2);
			$this->db->where("billing_id = ".$main_billing_id." AND product_id = ".$product_id." AND is_deleted = 0");
			$get_old_detail = $this->db->get();
			if($get_old_detail->num_rows() > 0){
				$dt_old_detail = $get_old_detail->row();
				$form_type_orderProduct = 'edit';
				$id = $dt_old_detail->id;
				$order_qty =  $dt_old_detail->order_qty+$order_qty;
			}
			
			//update-2011.001
			$has_list_price = $this->input->post('has_list_price');
			if(!empty($has_list_price)){
				
				$dt_post_price = array(
					'product_id' => $product_id,
					'order_qty' => $order_qty,
					'has_varian' => $has_varian,
					'varian_id' => $varian_id,
					'return_data' => true
				);
				
				$get_cekPriceQty = $this->priceqty->cekPriceQty($dt_post_price);
				
				if(!empty($get_cekPriceQty['success'])){
					$product_price = $get_cekPriceQty['product_price'];
				}
				
			}
			
		}
		
		if($billingData->lock_billing == 1){
			$r = array('success' => false, 'info' => 'Billing di kunci oleh kasir<br/>tidak bisa melakukan pesanan/order!');
			echo json_encode($r);
			die();
		}
		
		if($billingData == false OR empty($main_billing_id)){
			$r = array('success' => false, 'info' => 'Billing tidak ditemukan!', 'billingData' => $billingData);
			echo json_encode($r);
			die();
		}
		
		if($billingData->billing_status == 'paid'){
			$r = array('success' => false, 'info' => 'Billing: '.$billingData->billing_no.' sudah dibayar!<br/>Tidak bisa melakukan pesanan/order, Silahkan lakukan Refresh List Billing'); 
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
		
		//update-2003.001
		//product_price_before_promo -> product_price_real
		$product_price_post = $product_price;
		if($is_promo == 1 OR !empty($promo_id)){
			if($promo_tipe == 1){
				$product_price = $product_price_before_promo;
				$product_price_real = 0;
				if(!empty($include_tax) OR !empty($include_service)){
					if(!empty($include_tax) AND !empty($include_service)){
						$all_percentage = 100 + $tax_percentage + $service_percentage;
						$one_percent = $product_price / $all_percentage;
						$tax_total_2 = priceFormat($one_percent * $tax_percentage, 0, ".", "");
						$service_total_2 = priceFormat($one_percent * $service_percentage, 0, ".", "");
						$product_price_real = $product_price - ($tax_total_2 + $service_total_2);
						
					}else{
						if(!empty($include_tax)){
							$all_percentage = 100 + $tax_percentage;
							$one_percent = $product_price / $all_percentage;
							$tax_total_2 = priceFormat($one_percent * $tax_percentage, 0, ".", "");
							$product_price_real = $product_price - ($tax_total_2);
						}
						
						if(!empty($include_service)){
							$all_percentage = 100 + $service_percentage;
							$one_percent = $product_price / $all_percentage;
							$service_total_2 = priceFormat($one_percent * $service_percentage, 0, ".", "");
							$product_price_real = $product_price - ($service_total_2);
						}
						
					}
				}else
				{
					$product_price_real = $product_price;
				}
			}
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
			
			$total_bagi_hasil = numberFormat($product_price_post * $persentase_bagi_hasil / 100);
			
		}
		
		//update-1912-001
		cek_server_backup($get_opt);
		
		$r = '';
		
		//update-1912-001
		$order_status_default = 'order';
		$all_status_order_printed = 0;
		if(!empty($get_opt['all_status_order_printed'])){
			$all_status_order_printed = 1;
			$order_status_default = 'done';
		}
		
		//update-2003.001
		$create_buyget_item = false;
		$create_buyget_ref_id = 0;
		$create_package_item = false;
		$create_package_ref_id = 0;
		
		if($form_type_orderProduct == 'add')
		{
			
			if(empty($get_opt['use_order_counter'])){
				$get_opt['use_order_counter'] = 0;
			}
			
			if(empty($get_opt['save_order_note'])){
				$get_opt['save_order_note'] = 0;
			}
			
			//GET COUNTER
			$order_day_counter = date('Ymd');
			if($get_opt['use_order_counter'] == 1){
				$order_counter = getBillingDetailCounter();
			}else{
				$order_counter = 0;
			}
			
			$var = array(
				'fields'	=>	array(
				    'billing_id'  	=> 	$main_billing_id,
				    'billing_id_before_merge'  	=> 	$billing_id_before_merge,
					'product_id'	=>	$product_id,
					'product_type'	=>	$product_type,
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
					'order_status'	=>	$order_status_default,
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
					'grandtotal_bagi_hasil'		=>	$total_bagi_hasil*$order_qty,
					'use_stok_kode_unik'		=>	$use_stok_kode_unik,
					'data_stok_kode_unik'		=>	$data_stok_kode_unik
				),
				'table'		=>  $this->table2
			);

			$default_package = array();
			if($product_type == 'package'){
				$default_package = array(
				    'billing_id'  	=> 	$main_billing_id,
				    'billing_id_before_merge'  	=> 	$billing_id_before_merge,
					'product_id'		=>	0,
					'product_type'		=>	'item',
					'category_id'		=>	0,
					'product_varian_id'	=>	0,
					'varian_id'		=>	0,
					'has_varian'	=>	0,
					'include_tax'	=>	$include_tax,
					'tax_percentage'=>	$tax_percentage,
					'tax_total'		=>	0,
					'include_service'	=>	$include_service,
					'service_percentage'=>	$service_percentage,
					'service_total'		=>	0,
					'is_takeaway'		=>	$is_takeaway,
					'takeaway_no_tax'	=>	$takeaway_no_tax,
					'takeaway_no_service'	=>	$takeaway_no_service,
					'is_compliment'		=>	$is_compliment,
					'product_price_real'=>	0,
					'product_price'		=>	0,
					'product_price_hpp'	=>	0,
					'product_normal_price'	=>	0,
					'order_qty'		=>	0,
					'order_notes'	=>	$order_notes,
					'order_status'	=>	$order_status_default,
					'order_counter'	=>	$order_counter,
					'order_day_counter'	=>	$order_day_counter,
					'created'		=>	$date_now,
					'createdby'		=>	$session_user,
					'updated'		=>	$date_now,
					'updatedby'		=>	$session_user,
				);
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
			
			if($is_promo == 1 OR !empty($promo_id)){
				if($promo_tipe == 1){
					//$var['fields']['product_price'] = $product_price_before_promo;
					$var['fields']['discount_id'] = $promo_id;
					$var['fields']['discount_notes'] = $promo_desc;
					$var['fields']['discount_percentage'] = $promo_percentage;
					$var['fields']['discount_price'] = $promo_price;
					$var['fields']['discount_total'] = $promo_total;
				}
			}
		
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
				$q = $this->mdetail->add($var);
				$insert_id = $this->mdetail->get_insert_id();
			$this->lib_trans->commit();			
			if($q)
			{  
				if(!empty($billingData->id)){
					//$update_billing = billingCashier->calculateBilling($billingData->id);
				}
				
				//update-2003.001
				$create_buyget_item = false;
				$create_buyget_ref_id = 0;
				if($is_buyget == 1 OR !empty($buyget_id)){
					if($buyget_tipe == 'item'){
						
						$create_buyget_item = true;
						$create_buyget_ref_id = $insert_id;
						
					}
				}
				
				$create_package_item = false;
				$create_package_ref_id = 0;
				if($product_type == 'package'){
					$create_package_item = true;
					$create_package_ref_id = $insert_id;
				}
				
				$r = array('success' => true, 'id' => $insert_id, 'billingData' => $billingData); 
				
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($form_type_orderProduct == 'edit'){
			
			$wepos_tipe = 'cafe';
			
			//Check OOO Menu
			$this->db->select("*");
			$this->db->from($this->prefix.'ooo_menu');
			$this->db->where("product_id = ".$product_id." AND tanggal = '".date("Y-m-d")."' AND is_deleted = 0");
			$get_ooo = $this->db->get();
			if($get_ooo->num_rows() > 0){
				$dt_ooo = $get_ooo->row();
				
				//get old detail
				if(!empty($id)){
					$this->db->from($this->table2);
					$this->db->where("id = ".$id);
					$get_old_detail = $this->db->get();
					if($get_old_detail->num_rows() > 0){
						$dt_old_detail = $get_old_detail->row();
						
						if($dt_old_detail->order_qty < $order_qty){
							$r = array('success' => false, 'info' => 'Tidak Bisa Menambah Qty, Product/Menu Habis (Out of Order)<br/>Ket: '.$dt_ooo->keterangan); 
							die(json_encode($r));
						}
						
					}
				}
				
			}
			
			$var = array('fields'	=>	array(
				    'billing_id'  	=> 	$main_billing_id,
					'product_id'	=>	$product_id,
					'product_type'	=>	$product_type,
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
					'grandtotal_bagi_hasil'		=>	$total_bagi_hasil*$order_qty,
					'package_item'		=>	$package_item
				),
				'table'			=>  $this->table2,
				'primary_key'	=>  'id'
			);
			
			//update-1912-001
			//if(!empty($order_status_default)){
			//	$var['fields']['order_status'] = $order_status_default;
			//}
			
			if($is_buyget == 1 OR !empty($buyget_id)){
				if($buyget_tipe == 'percentage'){
					$var['fields']['discount_id'] = $buyget_id;
					$var['fields']['discount_notes'] = $buyget_desc;
					$var['fields']['discount_percentage'] = $buyget_percentage;
					$var['fields']['discount_price'] = $buyget_total_peritem;
					$var['fields']['discount_total'] = $buyget_total;
				}
			}
			
			//update-2003.001			
			if(!empty($free_item) OR !empty($package_item)){
				$var['fields'] = array(
					'order_notes'	=>	$order_notes,
					'updated'		=>	$date_now,
					'updatedby'		=>	$session_user
				);
			}
			
			if(!empty($free_item) AND empty($package_item)){
				$var['fields']['tax_total'] = 0;
				$var['fields']['service_total'] = 0;
				$var['fields']['discount_price'] = $product_price_real;
				$var['fields']['discount_total'] = ($product_price_real*$order_qty);
			}
			
			if($is_promo == 1 OR !empty($promo_id)){
				if($promo_tipe == 1){
					//$var['fields']['product_price'] = $product_price_before_promo;
					$var['fields']['discount_id'] = $promo_id;
					$var['fields']['discount_notes'] = $promo_desc;
					$var['fields']['discount_percentage'] = $promo_percentage;
					$var['fields']['discount_price'] = $promo_price;
					$var['fields']['discount_total'] = $promo_total;
				}
			}
			
			$this->lib_trans->begin();
				$q = $this->mdetail->save($var, $id);
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
							
						}else{
							//create buyget
							$create_buyget_item = true;
							$create_buyget_ref_id = $id;
						}
					}
				}
				
				//update-2003.001
				//package
				if($product_type == 'package'){
					
					$product_package_qty = array();
					$this->db->select("*");
					$this->db->from($this->prefix.'product_package');
					$this->db->where("package_id",$product_id);
					$this->db->where("varian_id",$varian_id);
					$get_prod_package = $this->db->get();
					if($get_prod_package->num_rows() > 0){
						foreach($get_prod_package->result_array() as $dt_package){
							$key_prod_var = $dt_package['product_id'].'_'.$dt_package['product_varian_id_item'].'_'.$dt_package['varian_id_item'];
							$product_package_qty[$key_prod_var] = $dt_package['product_qty'];
						}
					}
					
					$update_ref_free_item = array();
					
					$this->db->from($this->table2);
					$this->db->where("ref_order_id = ".$id);
					$this->db->where("package_item = 1");
					$this->db->where("is_deleted = 0");
					$get_package_item = $this->db->get();
					if($get_package_item->num_rows() > 0){
						
						foreach($get_package_item->result_array() as $det_package_item){
							$key_prod_var = $det_package_item['product_id'].'_'.$det_package_item['product_varian_id'].'_'.$det_package_item['varian_id'];
							$order_qty_package_item = 0;
							if(!empty($product_package_qty[$key_prod_var])){
								$order_qty_package_item = $product_package_qty[$key_prod_var];
							}
							
							$order_qty_package_item = $order_qty_package_item*$order_qty;
							
							$update_ref_free_item[] = array(
								'id'	=> $det_package_item['id'],
								'order_qty'	=> $order_qty_package_item
							);
						}
						
						if(!empty($update_ref_free_item)){
							$this->db->update_batch($this->table2,$update_ref_free_item,"id");
						}
						
						
					}else{
						//create package
						$create_package_item = true;
						$create_package_ref_id = $id;
					}
				}
				
				$r = array('success' => true, 'id' => $id, 'billingData' => $billingData);
			}  
			else
			{  
				$r = array('success' => false);
			}
		}
		
		//BUYGET - update-2003.001
		if($create_buyget_item == true){
			//if(!empty($buyget_item) AND $buyget_item != $product_id){
			if(!empty($buyget_item)){
				//get product detail
				$this->db->select("*");
				$this->db->from($this->prefix.'product');
				$this->db->where("id",$buyget_item);
				$get_prod = $this->db->get();
				if($get_prod->num_rows() > 0){
					$data_prod = $get_prod->row();
					$product_type = $data_prod->product_type;
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
				'product_type'	=>	$product_type,
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
				'order_status'	=>	$order_status_default,
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
				'ref_order_id'		=>	$create_buyget_ref_id
			);
			
			if(!empty($data_free_item)){
				$this->db->insert($this->table2,$data_free_item);
			}
		}
		

		//PACKAGE
		if($create_package_item == true){
			
			$data_package = array();
			if($product_type == 'package'){
				
				$total_product_price = 0;
				$total_product_hpp = 0;
				$total_normal_price = 0;
				
				$this->db->select("*");
				$this->db->from($this->prefix.'product_package');
				$this->db->where("package_id",$product_id);
				$this->db->where("varian_id",$varian_id);
				$get_prod_package = $this->db->get();
				if($get_prod_package->num_rows() > 0){
					foreach($get_prod_package->result_array() as $dt_package){
						$new_data_product = $default_package;
						$new_data_product['ref_order_id'] = $insert_id;
						$new_data_product['package_item'] = 1;
						$new_data_product['product_id'] = $dt_package['product_id'];
						//$new_data_product['category_id'] = $dt_package['category_id'];
						$new_data_product['product_varian_id'] = $dt_package['product_varian_id_item'];
						$new_data_product['varian_id'] = $dt_package['varian_id_item'];
						$new_data_product['has_varian'] = $dt_package['has_varian'];
						$new_data_product['product_price_real'] = $dt_package['product_price'];
						$new_data_product['product_price'] = $dt_package['product_price'];
						$new_data_product['product_price_hpp'] = $dt_package['product_hpp'];
						$new_data_product['product_normal_price'] = $dt_package['normal_price'];
						$new_data_product['order_qty'] = $order_qty*$dt_package['product_qty'];
						
						$total_product_price += $dt_package['product_price']*$new_data_product['order_qty'];
						$total_product_hpp += $dt_package['product_hpp'];
						$total_normal_price += $dt_package['normal_price']*$new_data_product['order_qty'];
						
						$data_package[$dt_package['product_id']] = $new_data_product;
						
					}
					
					//balancing
					$persentase_selisih = 0;
					if($product_normal_price != $total_normal_price AND $product_price != $total_product_price){
						$selisih_price = $product_normal_price-$product_price;
						if($selisih_price > 0){
							$persentase_selisih = ($selisih_price/$product_normal_price)*100;
							$persentase_selisih = priceFormat($persentase_selisih, 2, ".", "");
						}
					}
					
					$all_p_product_price = 0;
					$new_data_package = array();
					if(!empty($data_package)){
						$no_p = 0;
						foreach($data_package as $dtp){
							$no_p++;
							
							$new_data_package[] = $dtp;
							
						}
					}
					
					//delete current package
					$update_curent_package = array('is_active' => 0, 'is_deleted' => 1);
					$this->db->update($this->table2, $update_curent_package,"package_item = 1 AND ref_order_id = ".$insert_id);
					
					//insert new package
					if(!empty($new_data_package)){
						$this->db->insert_batch($this->table2, $new_data_package);
					}
					
				}
			}
		}
		
		if(!empty($order_notes) AND !empty($get_opt['save_order_note'])){
			$this->db->from($this->prefix.'order_note');
			$this->db->where("order_note_text = '".$order_notes."'");
			$get_notes = $this->db->get();
			if($get_notes->num_rows() > 0){
				$update_note = array(
					'is_deleted' => 0,
					'updated'		=>	$date_now,
					'updatedby'		=>	$session_user,
				);
				$this->db->update($this->prefix.'order_note',$update_note,"order_note_text = '".$order_notes."'");
			}else{
				$update_note = array(
					'order_note_text'	 => $order_notes,
					'created'		=>	$date_now,
					'createdby'		=>	$session_user,
					'updated'		=>	$date_now,
					'updatedby'		=>	$session_user
				);
				$this->db->insert($this->prefix.'order_note',$update_note);
			}
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function cancelOrder(){
		
		$this->table = $this->prefix.'billing_detail';
		$this->table2 = $this->prefix.'billing';
				
		$session_user = $this->session->userdata('user_username');		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		//update-1912-002
		$opt_value = array(
			'wepos_tipe','retail_warehouse', 'autocut_stok_sales_to_usage','autocut_stok_sales','as_server_backup'
		);
		
		$get_opt = get_option_value($opt_value);
		
		cek_server_backup($get_opt);
		
		$wepos_tipe = 'cafe';
		if(!empty($get_opt['wepos_tipe'])){
			$wepos_tipe = $get_opt['wepos_tipe'];
		}
		
		$is_express = $this->input->post('is_express', true);	
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
		a.product_price, a.discount_price, a.package_item, a.free_item, a.ref_order_id,
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
			
			if($is_express == 1){
				$qty = $billingData->order_qty;
				$keterangan = 'Cancel Order (express)';
			}else{
				if($billingData->billing_status == 'paid'){
					$r = array('success' => false, 'info' => 'Billing: '.$billingData->billing_no.' sudah dibayar!<br/>Tidak bisa cancel order, lakukan void billing atau hold billing'); 
					echo json_encode($r);
					die();
				}
			}
			
			if($billingData->package_item == 1 AND $billingData->free_item == 1 AND !empty($billingData->ref_order_id)){
				$r = array('success' => false, 'info' => 'Menu/Product termasuk dalam Paket!<br/>Silahkan Cancel Menu/Product Utama Paket'); 
				echo json_encode($r);
				die();
			}
			
			if($billingData->package_item == 0 AND $billingData->free_item == 1 AND !empty($billingData->ref_order_id)){
				$r = array('success' => false, 'info' => 'Menu/Product termasuk dalam Promo<br/>Please Cancel Menu/Product Utama'); 
				echo json_encode($r);
				die();
			}
			
			//$r = array('success' => false, 'info' => 'Billing: '.$billingData->billing_no.' sudah dibayar!<br/>Cannot Cancel Order, Silahkan lakukan Refresh List Billing');
			//die(json_encode($r));
		}
		
		$r = array('success' => false, 'info' => 'Cancel Order Gagal!'); 
		if(!empty($billingData)){
			
			//CLOSING DATE
			$var_closing = array(
				'xdate'	=> $billingData->created,
				'xtipe'	=> 'sales'
			);
			$is_closing = is_closing($var_closing);
			if($is_closing){
				$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal tersebut sudah ditutup!'); 
				die(json_encode($r));
			}
			
			//update-2001.002
			if($billingData->order_status == 'done' AND count($id) == 1){
				
				$r = array('success' => false, 'info' => 'Cancel Order Gagal!'); 
				if(!empty($spv_valid)){
					
					//CHECK
					if($billingData->order_qty < $qty){
						$r = array('success' => false, 'info' => 'Max Qty Cancel is '.$billingData->order_qty); 
						die(json_encode($r));
					}
					
					if($billingData->order_qty == $qty){
						//update-2001.002
						//update to deleted = 0
						$update_order = array(
							'order_status'	=> 'cancel',
							'is_deleted'	=> 1,
							'cancel_order_notes'=> 'order:'.$billingData->order_status.', billing:'.$billingData->billing_status.', ket:'.$keterangan
						);
						$q = $this->db->update($this->table, $update_order, "id IN (".$sql_Id.") OR ref_order_id IN (".$sql_Id.")");
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
						$dt_detail['cancel_order_notes'] = 'order:'.$billingData->order_status.', billing:'.$billingData->billing_status.', ket:'.$keterangan;
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
						$q = $this->db->update($this->table, $update_order, "id IN (".$sql_Id.") OR ref_order_id IN (".$sql_Id.")");
						
						//$q = true; 
						//update main billing
						//$update_billing = calculateBilling($billingData->billing_id);
					}
					
					if($q)  
					{  
						if($billingData->order_status == 'done' AND !empty($cancel_billing_detail_id)){
							
							//PRINT CANCEL ORDER TO QC/BAR/KITCHEN
							$r = $this->doPrint('void_order', $billingData->billing_id, $cancel_billing_detail_id);
							
							//print_r($r);
							$r['billingData'] = $billingData;
							
							
						}
						
						echo json_encode($r);
						die();
					}  
					else
					{  
						$r = array('success' => false, 'info' => 'Cancel Order Gagal!', 'billingData' => $billingData); 
					}
				}
				
				
			}else{
				
				//Delete
				//$this->db->where("id IN (".$sql_Id.")");
				//$q = $this->db->delete($this->table);
				
				//update-2001.002
				$update_order = array(
					'order_status'	=> 'cancel',
					'is_deleted'	=> 1,
					'cancel_order_notes'	=> 'order:progress, billing:'.$billingData->billing_status.', ket:'.$keterangan
				);
				
				$q = $this->db->update($this->table, $update_order, "id IN (".$sql_Id.") OR ref_order_id IN (".$sql_Id.")");
				
				if($q)  
				{  
					$r = array('success' => true);
					
				}  
				else
				{  
					$r = array('success' => false, 'info' => 'Cancel Order Gagal!', 'billingData' => array()); 
				}
			}
		}
		
		die(json_encode($r));
	}
	
	public function returOrder(){
		
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
			$r = array('success' => false, 'info' => 'Pesanan tidak ditemukan!');
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
	
	/*PAY BILLING*/
	public function save_payBilling(){
		$this->table = $this->prefix.'billing';				
		$this->table2 = $this->prefix.'billing_detail';				
		$this->table_inv = $this->prefix.'table_inventory';				
		$this->table_storehouse_users = $this->prefix.'storehouse_users';				
		$session_user = $this->session->userdata('user_username');
		$role_id = $this->session->userdata('role_id');
		$id_user = $this->session->userdata('id_user');
		
		//STOCK
		$this->table_usagewaste = $this->prefix.'usagewaste';		
		$this->table_product = $this->prefix.'product';		
		$this->table_items = $this->prefix.'items';		
		$this->table_product_gramasi = $this->prefix.'product_gramasi';		
		$this->table_product_package = $this->prefix.'product_package';		
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		//update-1912-002
		$get_opt_var = array('role_id_kasir','table_available_after_paid','include_tax','include_service,', 
		'diskon_sebelum_pajak_service','cashier_max_pembulatan','cashier_pembulatan_keatas','pembulatan_dinamis',
		'wepos_tipe','retail_warehouse','autocut_stok_sales_to_usage','autocut_stok_sales','cashier_credit_ar','min_noncash',
		'must_choose_customer','as_server_backup','jumlah_shift','shift_active',
		'tandai_pajak_billing','nontrx_sales_auto','nontrx_override_on','nontrx_allow_zero','current_date');
		$get_opt = get_option_value($get_opt_var);
		
		cek_server_backup($get_opt);
		
		//update-1912-001
		$shift = 1;
		$jumlah_shift = 1;
		if(!empty($get_opt['jumlah_shift'])){
			$jumlah_shift = $get_opt['jumlah_shift'];
		}
		if(!empty($get_opt['shift_active'])){
			$shift = $get_opt['shift_active'];
		}
		
		if($jumlah_shift > 1 AND empty($shift)){
			$this->db->select('a.*, b.nama_shift');
			$this->db->from($this->prefix.'shift_log as a');
			$this->db->join($this->prefix.'shift as b',"b.id = a.user_shift","LEFT");
			$this->db->where("a.tanggal_shift", date("Y-m-d"));
			$this->db->order_by("a.id", 'DESC');
			$getShiftLog = $this->db->get();
			if($getShiftLog->num_rows() > 0){
				$dataShiftLog = $getShiftLog->row_array();
				$shift = $dataShiftLog['user_shift'];
			}
		}
		
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
				
		$autocut_stok_sales_to_usage = 0;
		if(!empty($get_opt['autocut_stok_sales_to_usage'])){
			$autocut_stok_sales_to_usage = $get_opt['autocut_stok_sales_to_usage'];
		}
		
		//update-1912-002		
		$autocut_stok_sales = 0;
		if(!empty($get_opt['autocut_stok_sales'])){
			$autocut_stok_sales = $get_opt['autocut_stok_sales'];
		}
		
		$diskon_sebelum_pajak_service = 0;
		if(!empty($get_opt['diskon_sebelum_pajak_service'])){
			$diskon_sebelum_pajak_service = $get_opt['diskon_sebelum_pajak_service'];
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
		
		if(empty($retail_warehouse)){
			$r = array('success' => false, 'info' => 'Silahkan lakukan Set Stock Warehouse/Gudang!');
			echo json_encode($r);
			die();
		}
		
		//Cashier or Superadmin
		if(!empty($role_id_kasir)){
			if(in_array($this->session->userdata('role_id'), $role_id_kasir) OR $role_id == 1){
				
			}else
			{
				$r = array('success' => false, 'info' => 'Hanya User Kasir yang dapat melakukan pembayaran!');
				echo json_encode($r);
				die();
			}
		}else
		{
			$r = array('success' => false, 'info' => 'Hanya User Kasir yang dapat melakukan pembayaran!');
			echo json_encode($r);
			die();
		}
		
		$table_id = $this->input->post('table_id');
		$table_no = $this->input->post('table_no');
		$total_guest = $this->input->post('total_guest');
		
		if(empty($table_id) OR empty($table_no)){
			$r = array('success' => false, 'info' => 'Pilih Table/Meja!');
			echo json_encode($r);
			die();
		}
		
		if(empty($total_guest)){
			$r = array('success' => false, 'info' => 'Total Guest/Tamu Tidak Boleh Kosong!');
			echo json_encode($r);
			die();
		}	
				
		//BILLING
		$billingData = array();
		$get_total = 0;
		$billing_id = $this->input->post('billing_id');
		$billing_no = $this->input->post('billing_no');
		$this->db->select("b.id, b.billing_no, b.billing_status, b.created, b.include_tax, b.include_service, 
		b.discount_perbilling, b.merge_id, b.merge_main_status, b.block_table,
		b.discount_percentage, b.discount_total,
		b.total_billing, b.grand_total");
		$this->db->from($this->table." as b");
		$this->db->where("b.id = ".$billing_id);
		$this->db->where("b.billing_no = '".$billing_no."'");
		//$this->db->where("b.billing_status = 'paid'");
		$get_billing = $this->db->get();
		if($get_billing->num_rows() > 0){
			$billingData = $get_billing->row();
			
			if($billingData->billing_status == 'paid'){
				$r = array('success' => false, 'info' => 'Billing: '.$billingData->billing_no.' sudah dibayar!<br/>tidak dapat melakukan pembayaran pada billing tersebut, Silahkan lakukan refresh pada list billing (paid)'); 
				echo json_encode($r);
				die();
			}
		}else{
			$r = array('success' => false, 'info' => 'Paid Billing #'.$billing_no.' Gagal!<br/>silahkan lakukan refresh dan coba lakukan pembayaran kembali!');
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
			$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal tersebut sudah ditutup!'); 
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
		//$get_total += $total_ppn;
		
		$service_percentage = $this->input->post('service_percentage');
		$total_service = $this->input->post('total_service');
		//$get_total += $include_service;
		
		if(empty($billingData->include_tax) OR empty($billingData->include_service)){
			if(empty($billingData->include_tax)){
				$get_total += $total_ppn;
			}
			
			if(empty($billingData->include_service)){
				$get_total += $total_service;
			}
		}
		
		$payment_id = $this->input->post('payment_id');
		
		//cashier_credit_ar
		$cashier_credit_ar = 0;
		if(!empty($get_opt['cashier_credit_ar'])){
			$cashier_credit_ar = $get_opt['cashier_credit_ar'];
		}
		
		$min_noncash = 0;
		if(!empty($get_opt['min_noncash'])){
			$min_noncash = $get_opt['min_noncash'];
		}
		
		if($payment_id == 4 AND $cashier_credit_ar == 0){
			$r = array('success' => false, 'info' => '<br/>Penggunaan Pembayaran: Credit - AR / Piutang tidak digunakan!');
			echo json_encode($r);
			die();
		}
		
		$bank_id = $this->input->post('bank_id');
		$card_no = $this->input->post('card_no');
		
		//sales
		$sales_id = $this->input->post('sales_id');
		$sales_percentage = $this->input->post('sales_percentage');
		$sales_price = $this->input->post('sales_price');
		$sales_type = $this->input->post('sales_type');
		
		//customer
		$customer_id = $this->input->post('customer_id');
		
		if($payment_id == 4 AND $cashier_credit_ar == 1 AND empty($customer_id)){
			$r = array('success' => false, 'info' => '<br/>Pilih Customer untuk Penggunaan Pembayaran: Credit - AR / Piutang!');
			echo json_encode($r);
			die();
		}
		
		if(!empty($get_opt['must_choose_customer']) AND empty($customer_id)){
			$r = array('success' => false, 'info' => '<br/>Pilih Customer ketika melakukan pembayaran!');
			echo json_encode($r);
			die();
		}
		
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
		
		//update-2003.001
		//if(!empty($get_opt['diskon_sebelum_pajak_service'])){
			$get_total = $grand_total;
		//}
		
		if(!empty($is_half_payment)){
			//paid is same as total billing
			$total_paid = $get_total;
		}else{
			$is_half_payment = 0;
			if($payment_id == 1){
				//update-1912-001
				//CASH
				$total_cash = $get_total;
				$total_credit = 0;
			}else{
				$total_credit = $get_total;	
			}	
			
		}	


		if($payment_id != 1 AND $total_credit < $min_noncash AND !empty($min_noncash)){
			$r = array('success' => false, 'info' => '<br/>Penggunaan Non Cash Minimal: Rp. '.priceFormat($min_noncash));
			echo json_encode($r);
			die();
		}		
		
		if(empty($total_paid) AND $discount_percentage < 100){
			//$r = array('success' => false, 'info' => 'Total Bayar Tidak Boleh Kosong!');
			//echo json_encode($r);
			//die();
		}
		
		
		if($billingData->grand_total != $grand_total){
			$r = array('success' => false, 'info' => 'Grand Total tidak sesuai dengan data pesanan<br/>Silahkan refresh data Order/Pesanan'); 
			die(json_encode($r));
		}
		if($billingData->total_billing != $total_billing){
			$r = array('success' => false, 'info' => 'Total Billing tidak sesuai dengan data pesanan<br/>Silahkan refresh data Order/Pesanan'); 
			die(json_encode($r));
		}
		
		if(empty($total_paid)){
			if($total_paid == $grand_total){
				
			}else{
				if(empty($total_paid) AND $discount_percentage < 100){
					$r = array('success' => false, 'info' => 'Total Bayar Tidak Boleh Kosong!');
					echo json_encode($r);
					die();
				}
				
				if($total_paid != $grand_total){
					$r = array('success' => false, 'info' => 'Grand Total tidak sesuai dengan Total Pembayaran<br/>Silahkan refresh data Order/Pesanan'); 
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
			$r = array('success' => false, 'info' => 'Billing tidak ditemukan!');
			echo json_encode($r);
			die();
		}
		
		$all_item_id = array();
		$all_unik_kode = array();
		$all_unik_kode_perkey = array();
		$all_unik_kode_peritemId = array();
		$same_unik_kode = array();
		$message_same_unik_kode = array();
		$item_name_kode = array();
		
		//update 2019-02-13
		$all_product_order = array();
		$all_product_order_package = array();
		$all_product_gramasi_package = array();
		$all_product_gramasi_package_qty = array();
		$all_product_package_varian = array();
		$all_product_package_qty = array();
		$all_product_package_empty = array();
		$all_product_gramasi = array();
		$all_product_varian = array();
		$all_product_qty = array();
		
		//calc detail
		$total_tax = 0;
		$total_service = 0;
		$total_discount = 0;
		$total_hpp = 0;
		$this->db->select("a.product_id, a.order_qty, a.retur_qty, a.product_price_hpp, a.product_price, a.product_price_real, 
		a.include_tax, a.include_service, a.tax_percentage, a.service_percentage, a.is_compliment, a.product_type, a.varian_id,
		a.use_stok_kode_unik, a.data_stok_kode_unik, b.id_ref_item as item_id, b.product_name as item_name,
		a.tax_total, a.service_total, a.discount_total, b.from_item, b.id_ref_item, c.unit_id");
		$this->db->from($this->table2." as a");
		$this->db->join($this->prefix."product as b","b.id = a.product_id","LEFT");
		$this->db->join($this->table_items.' as c',"c.id = b.id_ref_item AND b.from_item = 1", "LEFT");
		$this->db->where('a.billing_id', $billing_id);
		$this->db->where('a.is_deleted', 0);
		$get_detail = $this->db->get();
		if($get_detail->num_rows() > 0){
			foreach($get_detail->result() as $key => $dtRow){
				$total_qty = $dtRow->order_qty - $dtRow->retur_qty;
				if($total_qty < 0){
					$total_qty = 0;
				}
				
				$total_hpp += $dtRow->product_price_hpp * $total_qty;
				$total_tax += $dtRow->tax_total;
				$total_service += $dtRow->service_total;
				$total_discount += $dtRow->discount_total;
				
				$dt = (array) $dtRow;
				
				//CHECK IF INCLUDE TAX AND SERVICE ---------------------
				$is_include = false;
				$all_percentage = 100;
				if($dt['include_tax'] == 1){
					$is_include = true;
					$all_percentage += $dt['tax_percentage'];
				}
				
				if($dt['include_service'] == 1){
					$is_include = true;		
					$all_percentage += $dt['service_percentage'];		
				}
				
				$grand_total_order = 0;
				if(!empty($dt['is_compliment'])){
					$dt['tax_total'] = 0;
					$dt['service_total'] = 0;
				}
				
				$include_tax = $dt['include_tax'];
				$include_service = $dt['include_service'];
				$tax_percentage = $dt['tax_percentage'];
				$service_percentage = $dt['service_percentage'];
				$tax_total = 0;
				$service_total = 0;
				$product_price_real = 0;
				$total_billing_order = 0;
				$tax_total_order = 0;
				$service_total_order = 0;
				
				//cek if discount is disc billing
				$total_discount_product = 0;
				if($billingData->discount_perbilling == 1){
					$get_percentage = $billingData->discount_percentage;
					if(empty($billingData->discount_percentage) OR $billingData->discount_percentage == '0.00'){
						$get_percentage = ($billingData->discount_total / $billingData->total_billing) * 100;
						$get_percentage = number_format($get_percentage,0);
					}
					
					$dt['discount_total'] = priceFormat(($dt['product_price_real']*($get_percentage/100)), 0, ".", "");
					$total_discount_product = ($dt['discount_total']*$dt['order_qty']);
					
				}else{
					
					$total_discount_product = ($dt['discount_total']);
				}
				
				if(!empty($include_tax) OR !empty($include_service)){
					
					//AUTOFIX-BUGS 1 Jan 2018
					if((!empty($include_tax) AND empty($include_service)) OR (empty($include_tax) AND !empty($include_service))){
						if($dt['product_price'] != ($dt['product_price_real']+$dt['tax_total']+$dt['service_total'])){
							$dt['product_price_real'] = priceFormat(($dt['product_price']/($all_percentage/100)), 0, ".", "");
						}
					}
					
					if($diskon_sebelum_pajak_service == 1){
						
						$grand_total_order = ($dt['product_price_real']*$dt['order_qty']) - $dt['discount_total'];
						
					}else{
						
						$grand_total_order = ($dt['product_price_real']*$dt['order_qty']);
						
					}

					$total_billing_order = ($dt['product_price_real']*$dt['order_qty']);
					$tax_total_order = $dt['tax_total'];
					$service_total_order = $dt['service_total'];
					
				}else
				{
						
					if($diskon_sebelum_pajak_service == 1){
						
						$grand_total_order = ($dt['product_price']*$dt['order_qty']) - $dt['discount_total'];
					
					}else{
						
						$grand_total_order = ($dt['product_price']*$dt['order_qty']);
					
					}
					
					$total_billing_order = ($dt['product_price']*$dt['order_qty']);
					$tax_total_order = $dt['tax_total'];
					$service_total_order = $dt['service_total'];
					
				}
				
				
				//$sub_total = $grand_total_order;
				
				//COMPLIMENT
				if(!empty($dt['is_compliment'])){
					$dt['service_total'] = 0;
					$dt['tax_total'] = 0;
				}
				
				if(empty($dt['order_qty'])){
					$dt['product_price'] = $total_billing_order;
				}else{
					$dt['product_price'] = ($total_billing_order/$dt['order_qty']);
				}
				
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
					
					$this->db->select("a.product_id, a.product_qty, a.product_price");
					$this->db->from($this->table_product_package." as a");
					$this->db->where("a.package_id IN (".$dtRow->product_id.") AND a.varian_id = '".$dtRow->varian_id."'");
					$get_package = $this->db->get();
					if($get_package->num_rows() > 0){
						foreach($get_package->result() as $dtRow){
							
							if(empty($all_product_gramasi_package[$key_prod_varian])){
								$all_product_gramasi_package[$key_prod_varian] = array();
								$all_product_gramasi_package_qty[$key_prod_varian] = array();
							}
							
							//get all product gramasi 
							if(!in_array($dtRow->product_id, $all_product_gramasi_package[$key_prod_varian])){
								$all_product_gramasi_package[$key_prod_varian][] = $dtRow->product_id;
								$all_product_gramasi_package_qty[$key_prod_varian][$dtRow->product_id] = 0;
							}
							
							$all_product_gramasi_package_qty[$key_prod_varian][$dtRow->product_id] += $dtRow->product_qty;
							
						}
						
					}else{
						
						if(!in_array($dtRow->product_id, $all_product_package_empty)){
							$all_product_package_empty[] = $dtRow->product_id;
						}
					}
				}
				
				if(empty($all_item_id[$dtRow->product_id])){
					$all_item_id[$dtRow->product_id] = array();
				}
				
				if(empty($dtRow->item_id)){
					$this->db->select("a.product_id, a.item_id");
					$this->db->from($this->table_product_gramasi." as a");
					$this->db->where("a.product_id = ".$dtRow->product_id." AND a.is_deleted = 0 AND a.is_active = 1");
					$get_dt_item = $this->db->get();
					if($get_dt_item->num_rows() > 0){
						foreach($get_dt_item->result() as $dt){
							if(!in_array($dt->item_id, $all_item_id[$dtRow->product_id])){
								$all_item_id[$dtRow->product_id][] = $dt->item_id;
							}
						}
					}
				}else{
					if(!empty($all_item_id[$dtRow->product_id])){
						if(!in_array($dtRow->item_id, $all_item_id[$dtRow->product_id])){
							$all_item_id[$dtRow->product_id][] = $dtRow->item_id;
						}
					}
				}
				
				//UNIK KODE
				$dtDet = (array) $dtRow;
				if(!empty($dtDet['use_stok_kode_unik'])){
					if($dtDet['use_stok_kode_unik'] == 1){
						$list_dt_kode = explode("\n",$dtDet['data_stok_kode_unik']);
						foreach($list_dt_kode as $dt){
							if(!empty($dt)){
								if(!in_array($dt, $all_unik_kode)){
									$all_unik_kode[] = $dt;
									
									if(empty($all_unik_kode_perkey[$dtRow->product_id])){
										$all_unik_kode_perkey[$dtRow->product_id] = array();
									}
									$all_unik_kode_perkey[$dtRow->product_id][] = $dt;
									
									$item_name_kode[$dtRow->product_id] = $dtRow->product_name;
									
								}else{
									$same_unik_kode[] = $dt;
									if(empty($message_same_unik_kode)){
										$r = array('success' => false, 'info' => 'Unik Kode (SN/IMEI): <b>'.$dt.'</b> lebih dari 1 data<br/>Cek pada Item: '.$dtDet['item_name']); 
										die(json_encode($r));
									}
								}
							}
							
						}
						
					}
				}
			}
		}
		

		if(!empty($all_unik_kode)){
			$all_unik_kode_txt = implode("','", $all_unik_kode);
			
			$this->db->from($this->prefix.'item_kode_unik');
			$this->db->where("kode_unik IN ('".$all_unik_kode_txt."')");
			$get_unik_kode = $this->db->get();
			
			$all_unik_kode_db = array();
			$all_unik_kode_db_peritem = array();
			if($get_unik_kode->num_rows() > 0){
				foreach($get_unik_kode->result() as $dt){
					if(!in_array($dt->kode_unik, $all_unik_kode_db)){
						$all_unik_kode_db[] = $dt->kode_unik;
					}
					
					//cek kode unik per-item
					if(empty($all_unik_kode_db_peritem[$dt->kode_unik])){
						$all_unik_kode_db_peritem[$dt->kode_unik] = '';
					}
					$all_unik_kode_db_peritem[$dt->kode_unik] = $dt->item_id;
					
				}
			}
			
			if(!empty($all_unik_kode_perkey)){
				foreach($all_unik_kode_perkey as $prodID => $dtImei){
					
					if(!empty($dtImei)){
						foreach($dtImei as $snImei){
							$nok_item = false;
							//cek imei valid di prodID
							if(in_array($snImei, $all_unik_kode_db)){
								//valid
								if(in_array($all_unik_kode_db_peritem[$snImei], $all_item_id[$prodID])){
									$nok_item = true;
								}
							}
							
							if($nok_item == false){
								if(empty($item_name_kode[$prodID])){
									$item_name_kode[$prodID] = '#'.$prodID;
								}
								$r = array('success' => false, 'info' => 'Unik Kode (SN/IMEI): '.$snImei.' tidak ada pada '.$item_name_kode[$prodID]); 
								die(json_encode($r));
							}
						}
					}
					
				}
			}
		}
		
		if($billingData->discount_perbilling == 1){
			$total_discount = $discount_total;
		}
		
		$datetime_now = date('Y-m-d H:i:s');
		
		//update-2010.001
		$payment_date = date('Y-m-d H:i:s');
		//if(!empty($billingData->is_salesorder) AND !empty($billingData->billing_datetime)){
		//	$payment_date = $billingData->billing_datetime;
		//}
				
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
				'payment_date'		=>	$payment_date,
				'single_rate'		=>	$single_rate,
				'is_compliment'		=>	$is_compliment,
				'is_half_payment'	=>	$is_half_payment,
				'total_cash'		=>	$total_cash,
				'total_credit'		=>	$total_credit,
				'grand_total'		=>	$grand_total,
				'total_return'		=>	$total_return,
				'updated'			=>	$datetime_now,
				'updatedby'			=>	$session_user,
				'shift'				=>	$shift
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
			$update = $this->mcashier->save($var, $billing_id);
		$this->lib_trans->commit();
		$update = true;
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
				
				//if($wepos_tipe != 'retail'){
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
				//}
				
			}
			
			//$get_opt = get_option_value(array('table_available_after_paid'));
			
			$date_now = date("Y-m-d");
				
			//if(!empty($get_opt['table_available_after_paid']) AND $wepos_tipe != 'retail'){
			if(!empty($get_opt['table_available_after_paid'])){
				
				$dt_update = array(
					'status'	=> 'available',
					'billing_no'	=> ''
				);
				
				$all_table_id_txt = implode(",", $all_table_id);
				
				$update = $this->db->update($this->table_inv, $dt_update, "tanggal = '".$date_now."' AND (table_id IN (".$all_table_id_txt.") OR billing_no = '".$billingData->billing_no."')");
				
			}
			
			if(!empty($retail_warehouse)){
				
				$get_billno_y = substr($billingData->billing_no,0,2);
				$get_billno_m = substr($billingData->billing_no,2,2);
				$get_billno_d = substr($billingData->billing_no,4,2);
				$billing_date = (2000+$get_billno_y)."-".$get_billno_m."-".$get_billno_d;
				
				if($autocut_stok_sales_to_usage == 1){
					
					$r['autocut_stok_sales_to_usage'] = $autocut_stok_sales_to_usage;
					$r['retail_warehouse'] = $retail_warehouse;
					
					$update_stok = 'usage';
					$return_data = $this->mdetail->billingDetail($billing_id, $retail_warehouse, $update_stok);
					
					//$date_now = date("Y-m-d");
					$params = array(
						'date_now'			=> $billing_date,
						'all_item_usage'	=> $return_data['all_item_usage'],
						'retail_warehouse'	=> $retail_warehouse,
						'rollback'			=> false,
					);
					$ret_usage = $this->usagewaste->save_sales_usage($params);
					
					//update-2003.001
					//billing_detail_gramasi
					
				}else{
					
					//update-1912-002
					if($autocut_stok_sales == 1){
						
						$r['info'] = 'Update Stok';
						$update_stok = 'update';
						
						$return_data = $this->mdetail->billingDetail($billing_id, $retail_warehouse, $update_stok);
						$r['update_stock'] = $return_data['update_stock'];
						
						$post_params = array(
							'storehouse_item'	=> $return_data['update_stock'],
							'date'				=> $billing_date,
						);
						
						$updateStock = $this->stock->update_stock_rekap($post_params);
						
					}
				}
			
			}else{
				//FORCE UPDATE STOK -> GRAMASI
			}
			
			//update 2018-02-25
			//Credit - AR
			if($payment_id == 4 AND $cashier_credit_ar == 1){
				//payment progress - done
				$updateAR = $this->account_receivable->set_account_receivable_Sales($billing_id);
				
				//$updateCF = $this->penerimaan_kas->set_DP_Sales($id);
			}
			
			//update-2011.001
			if(!empty($get_opt['tandai_pajak_billing']) AND !empty($get_opt['nontrx_sales_auto'])){
				$this->mfitur->override_nontrx($billing_id, $total_tax, $billingData->total_billing, false, $get_opt);
			}
			
			//SAVE TO LOG
			logBilling($billingData, 'Paid', 'Paid Billing '.$billingData->billing_no);
		}  
		else
		{  
			$r = array('success' => false);
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
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
		$reservationReceipt_layout = $this->input->post('reservationReceipt_layout', true);	
		$qcReceipt_layout = $this->input->post('qcReceipt_layout', true);	
		$kitchenReceipt_layout = $this->input->post('kitchenReceipt_layout', true);	
		$barReceipt_layout = $this->input->post('barReceipt_layout', true);	
		$otherReceipt_layout = $this->input->post('otherReceipt_layout', true);	
		
		$r = array('success' => false);
		
		$data_options = array(
			'cashierReceipt_layout' => $cashierReceipt_layout,
			'cashierReceipt_layout_footer' => $cashierReceipt_layout_footer,
			'cashierReceipt_invoice_layout' => $cashierReceipt_invoice_layout,
			'cashierReceipt_settlement_layout' => $cashierReceipt_settlement_layout,
			'cashierReceipt_openclose_layout' => $cashierReceipt_openclose_layout,
			'cashierReceipt_bagihasil_layout' => $cashierReceipt_bagihasil_layout,
			'reservationReceipt_layout' => $reservationReceipt_layout,
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
				"reservationReceipt_layout" => $reservationReceipt_layout, 
				"qcReceipt_layout" => $qcReceipt_layout, 
				"kitchenReceipt_layout" => $kitchenReceipt_layout, 
				"barReceipt_layout" => $barReceipt_layout, 
				"otherReceipt_layout" => $otherReceipt_layout
			);
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
			'reservationReceipt_layout', 
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
		if(!empty($get_opt['reservationReceipt_layout'])){
			$retValue['reservationReceipt_layout']  = $get_opt['reservationReceipt_layout'];
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
	
	/*FITUR BILLING*/	
	public function lockBilling(){
		
		$this->mfitur->lockBilling();
		
	}
	
	public function save_infoBilling(){
		
		$this->mfitur->save_infoBilling();
		
	}
	
	public function updateTable($data_create = array()){
		
		updateTable($data_create);
		
	}
		
	public function updateTotalGuest(){
		
		$this->mfitur->updateTotalGuest();
		
	}
		
	public function updateBillInfo(){
		
		$this->mfitur->updateBillInfo();
		
	}
		
	public function updatePPN(){
		
		$this->mfitur->updatePPN();
		
	}
		
	public function updateService(){
		
		$this->mfitur->updateService();
		
	}
		
	public function updateDP(){
		
		$this->mfitur->updateDP();
		
	}
		
	public function updateDiscount(){
		
		$this->mfitur->updateDiscount();
		
	}
		
	public function updateCompliment(){
		
		$this->mfitur->updateCompliment();
		
	}
	
	/*SPLIT & MERGE*/
	public function mergeBill(){
		
		$this->mfitur->mergeBill();
		
	}
	
	public function unMergeBill(){
		
		$this->mfitur->unMergeBill();
		
	}
	
	public function cek_mergeBill(){
		
		$this->mfitur->cek_mergeBill();
		
	}
	
	public function cek_splitBill(){
		
		$this->mfitur->cek_splitBill();
		
	}
	
	public function splitBill(){
		
		$this->mfitur->splitBill();
		
	}
	
	public function save_manyOrderProduct_split(){
		
		$this->mfitur->save_manyOrderProduct_split();
		
	}
	
	public function save_orderProduct_split(){
		
		$this->mfitur->save_orderProduct_split();
		
	}
	
	public function save_splitBill(){
		
		$this->mfitur->save_splitBill();
		
	}
	
	/*PRINT*/
	public function doPrint($is_void = '', $void_id = 0, $order_detail_id = ''){
		
		$this->mprint->doPrint($is_void, $void_id, $order_detail_id);
		
	}
	
	public function testPrinter(){
		
		$this->mprint->testPrinter();
		
	}
	
	public function loadingSetting(){
		
		$this->mprint->loadingSetting();
		
	}
	
	public function printSettlement(){
		
		$this->mprint->printSettlement();
	
	}	
	
	public function print_MultipleQC(){
		
		$this->mprint->print_MultipleQC();
		
	}
	
	public function print_MultipleBilling(){
		
		$this->mprint->print_MultipleBilling();
		
	}
	
	/*RESERVASI*/	
	public function billingReservation(){
		$this->table = $this->prefix.'billing';	
		$this->table_billing_detail = $this->prefix.'billing_detail';	
		$this->table_reservation = $this->prefix.'reservation';		
		$this->table_reservation_detail = $this->prefix.'reservation_detail';		
		$this->table_discount = $this->prefix.'discount';		
		$this->table_product = $this->prefix.'product';		
				
		$date_now = date('Y-m-d H:i:s');
		$session_user = $this->session->userdata('user_username');		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$reservation_id = $this->input->post('reservation_id', true);
		$reservation_number = $this->input->post('reservation_number', true);
		if(empty($reservation_id)){
			$r = array('success' => false, 'info' => 'Reservasi: '.$reservation_number.' tidak dikenali!'); 
			die(json_encode($r));
		}
		
		
		//CLOSING DATE
		$var_closing = array(
			'xdate'	=> date("Y-m-d"),
			'xtipe'	=> 'sales'
		);
		$is_closing = is_closing($var_closing);
		if($is_closing){
			$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal: '.date("Y-m-d").' sudah ditutup!'); 
			die(json_encode($r));
		}
		
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
						$r = array('success' => false, 'info' => 'Hold Billing, Gagal!');
						echo json_encode($r);
						die();
					}
				}
				
			}else{
				$r = array('success' => false, 'info' => 'Billing Id: #'.$hold_billing_id.' tidak ditemukan!');
				echo json_encode($r);
				die();
			}
			
			
		}
		
		//LOAD reservation & res.detail
		$this->db->select("a.*");
		$this->db->from($this->table_reservation." as a");
		$this->db->where("a.id = ".$reservation_id);
		$get_reservation = $this->db->get();
		if($get_reservation->num_rows() > 0){
			$ResData = $get_reservation->row();
		
			if($ResData->billing_id > 0 OR $ResData->billing_no != ''){
				$r = array('success' => false, 'info' => 'Reservation: '.$ResData->reservation_number.' sudah diset dengan billing: '.$ResData->billing_no);
				echo json_encode($r);
				die();
			}
			
		}else{
			$r = array('success' => false, 'info' => 'Reservasi: '.$reservation_number.' tidak dikenali!'); 
			die(json_encode($r));
		}
		
		$billingData = getBilling();
		if($billingData == false OR empty($billingData->billing_id)){
			$r = array('success' => false, 'info' => 'Membuat Billing Baru, Gagal!');
			echo json_encode($r);
			die();
		}
		
		
		$opt_value = array(
			'default_discount_id_reservation',
			'use_order_counter',
			'as_server_backup'
		);
		
		$get_opt = get_option_value($opt_value);
		
		cek_server_backup($get_opt);
		
		$default_discount_id_reservation = 0;
		if(!empty($get_opt['default_discount_id_reservation'])){
			$default_discount_id_reservation = $get_opt['default_discount_id_reservation'];
		}
		
		//SAVE FROM RESERVATION
		if(!empty($billingData->billing_no)){
			$billingData->created_datetime = date('d.m.Y H:i', strtotime($billingData->created));
			
			//update billing$
			$updateBilling = array(
				'total_billing' => $ResData->reservation_sub_total,
				'tax_total' => $ResData->reservation_tax,
				'service_total' => $ResData->reservation_service,
				'grand_total' => $ResData->reservation_total_price,
				'total_pembulatan' => 0,
				'total_dp' => $ResData->reservation_dp,
				'compliment_total' => 0,
				
				'discount_id' => 0,
				'discount_notes' => 0,
				'discount_percentage' => 0,
				'discount_price' => $ResData->reservation_discount,
				'discount_total' => $ResData->reservation_discount,
				'discount_perbilling' => 1,
				
				'total_guest' => $ResData->total_guest,
				'sales_id' => $ResData->sales_id,
				'sales_percentage' => $ResData->sales_percentage,
				'sales_price' => $ResData->sales_price,
				'sales_type' => $ResData->sales_type,
				'customer_id' => $ResData->customer_id,
				'billing_notes' => $ResData->reservation_memo,
				
				'is_reservation' => 1,
			);
			
			
			//GET DISCOUNT ID
			if(!empty($ResData->reservation_discount) AND !empty($default_discount_id_reservation)){
				
				$this->db->select("a.*");
				$this->db->from($this->table_discount." as a");
				$this->db->where("a.id = ".$default_discount_id_reservation);
				$get_disc = $this->db->get();
				if($get_disc->num_rows() > 0){
					$DiscData = $get_disc->row();
				
					$updateBilling['discount_id'] = $DiscData->id;
					$updateBilling['discount_notes'] = $DiscData->discount_name;
					$updateBilling['discount_percentage'] = $DiscData->discount_percentage;
					$updateBilling['discount_price'] = $ResData->reservation_discount;
					$updateBilling['discount_total'] = $ResData->reservation_discount;
					$updateBilling['discount_perbilling'] = 1;
					$updateBilling['voucher_no'] = $ResData->reservation_number;
					
				}
				
				
			}
			
			//TAX, SERVICE, TAKE AWAY & COMPLIMENT
			$include_tax = $billingData->include_tax;
			$include_service = $billingData->include_service;
			$tax_percentage = $billingData->tax_percentage;
			$service_percentage = $billingData->service_percentage;
			$takeaway_no_tax = $billingData->takeaway_no_tax;
			$takeaway_no_service = $billingData->takeaway_no_service;
			$billing_is_compliment = $billingData->is_compliment;
			
			if(empty($get_opt['use_order_counter'])){
				$get_opt['use_order_counter'] = 0;
			}
			
			//GET COUNTER
			$order_day_counter = date('Ymd');
			if($get_opt['use_order_counter'] == 1){
				$order_counter = getBillingDetailCounter();
			}else{
				$order_counter = 0;
			}
			
			$qty_order = 0;
			//GET DETAIL RESERVATION
			$data_detail = array();
			$this->db->select("a.*, b.category_id");
			$this->db->from($this->table_reservation_detail." as a");
			$this->db->join($this->table_product." as b","b.id = a.product_id","LEFT");
			$this->db->where("a.reservation_id = ".$reservation_id);
			$get_ResDetail = $this->db->get();
			if($get_ResDetail->num_rows() > 0){
				foreach($get_ResDetail->result() as $dtDetail){
					
					$product_price = $dtDetail->resd_price;
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
					
					$data_detail[] = array(
						'billing_id'  		=> 	$billingData->billing_id,
						'product_id'		=>	$dtDetail->product_id,
						'category_id'		=>	$dtDetail->category_id,
						'product_varian_id'	=>	$dtDetail->product_varian_id,
						'varian_id'			=>	$dtDetail->varian_id,
						'has_varian'		=>	$dtDetail->has_varian,
						'include_tax'		=>	$include_tax,
						'tax_percentage'	=>	$tax_percentage,
						'tax_total'			=>	($tax_total*$dtDetail->resd_qty),
						'include_service'	=>	$include_service,
						'service_percentage'=>	$service_percentage,
						'service_total'		=>	($service_total*$dtDetail->resd_qty),
						'takeaway_no_tax'	=>	$takeaway_no_tax,
						'takeaway_no_service'	=>	$takeaway_no_service,
						'product_price_real'	=>	$product_price_real,
						'product_price'			=>	$dtDetail->resd_price,
						'product_price_hpp'		=>	$dtDetail->resd_hpp,
						'product_normal_price'	=>	$dtDetail->resd_price,
						'order_qty'				=>	$dtDetail->resd_qty,
						'order_notes'			=>	$dtDetail->resd_notes,
						'order_status'			=>	'order',
						'order_counter'			=>	$order_counter,
						'order_day_counter'		=>	$order_day_counter,
						'created'				=>	$date_now,
						'createdby'				=>	$session_user,
						'updated'				=>	$date_now,
						'updatedby'				=>	$session_user,
						'is_kerjasama'			=>	$dtDetail->is_kerjasama,
						'supplier_id'			=>	$dtDetail->supplier_id,
						'persentase_bagi_hasil'	=>	$dtDetail->persentase_bagi_hasil,
						'total_bagi_hasil'		=>	$dtDetail->total_bagi_hasil,
						'grandtotal_bagi_hasil'	=>	$dtDetail->total_bagi_hasil*$dtDetail->resd_qty
					);
					
					if($get_opt['use_order_counter'] == 1){
						$order_counter += 1;
					}
					
					$qty_order += $dtDetail->resd_qty;
				}
			}

			//INSERT DETAIL
			if(!empty($data_detail)){
				$this->db->insert_batch($this->table_billing_detail, $data_detail);
			}
			
			//UPDATE RESERVATION
			$update_res = array(
				'billing_id' => $billingData->id, 
				'billing_no' => $billingData->billing_no
			);
			$this->db->update($this->table_reservation, $update_res, "id = ".$reservation_id);
			
			//UPDATE BILLING
			$this->db->update($this->prefix.'billing', $updateBilling, "id = ".$billingData->billing_id);
			
		}
		
		$r = array('success' => true, 'billing_no' => $billingData->billing_no, 'data_detail' => count($data_detail)); 
		echo json_encode($r);
		die();
	}
	
}