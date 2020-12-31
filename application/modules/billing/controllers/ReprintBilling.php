<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ReprintBilling extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_databilling', 'm');
		$this->load->model('model_billingdetail', 'm2');
	}

	public function tandaiBilling()
	{
		
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';		
		$session_user = $this->session->userdata('user_username');	
		$role_id = $this->session->userdata('role_id');	
		
		$get_data = array(
			"success" => true,
			"info" => "ok"
		);
		
		$opt_value = array('tandai_pajak_billing','nontrx_sales_auto','nontrx_allow_zero');
		$get_opt = get_option_value($opt_value);
		
		if(empty($get_opt['tandai_pajak_billing'])){
			$get_data['success'] = false;
			$get_data['info'] = 'Fitur Tandai Billing Belum Aktif!';
			die(json_encode($get_data));
		}
		
		$id = $this->input->post('id');
		$tandai = $this->input->post('tandai');
		
		if(empty($id)){
			$get_data['success'] = false;
			$get_data['info'] = 'ID not found';
			die(json_encode($get_data));
		}
		
		
		$id = json_decode($id, true);
		$all_id = implode(",", $id);
		
		//update-2008.001---------------
		if(!empty($tandai) AND empty($get_opt['nontrx_allow_zero'])){
			$no_allowed_id = array();
			$this->db->select("billing_id");
			$this->db->from($this->table2);
			$this->db->where("billing_id IN (".$all_id.") AND order_status = 'done' AND tax_total = 0");
			$get_billing_detail_null = $this->db->get();
			if($get_billing_detail_null->num_rows() > 0){
				foreach($get_billing_detail_null->result() as $dt){
					if(!in_array($dt->billing_id, $no_allowed_id)){
						$no_allowed_id[] = $dt->billing_id;
					}
				}
			}
			
			$new_id = array();
			if(!empty($no_allowed_id)){
				foreach($id as $xid){
					if(in_array($xid, $no_allowed_id)){
						
					}else{
						if(!in_array($xid, $new_id)){
							$new_id[] = $xid;
						}
					}
				}
				$id = $new_id;
			}
		
			if(empty($id)){
				$get_data['success'] = false;
				$get_data['info'] = 'Tax Total/Detail Item Tax = 0';
				die(json_encode($get_data));
			}
			$all_id = implode(",", $id);
		}
		
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where("id IN (".$all_id.")");
		$get_billing = $this->db->get();
		if($get_billing->num_rows() > 0){
			
			//curr no -> reorder no
			$update_billing_data = array();
			$reorder_date = array();
			$mk_from_date = 0;
			$mk_till_date = 0;
			$tgl_cek_mk   = 0;
			
			foreach($get_billing->result() as $dt){
				
				$billing_no = substr($dt->billing_no, 0, 6);
				
				//update-2009.002
				$billing_no_Y = 2000 + substr($dt->billing_no, 0, 2);
				$billing_no_m = substr($dt->billing_no, 2, 2);
				$billing_no_d = substr($dt->billing_no, 4, 2);
				$tgl_cek_mk = strtotime($billing_no_Y."-".$billing_no_m."-".$billing_no_d);
				
				if(!in_array($billing_no, $reorder_date)){
					$reorder_date[] = $billing_no;
				}
				
				$data_billing = array(
					'id'		=> $dt->id,
					'txmark'	=> $tandai,
					'txmark_no'	=> ''
				);
				
				$update_billing_data[] = $data_billing;
			}
			
				
			if(!empty($update_billing_data)){
				
				//reset
				$this->db->update_batch($this->table, $update_billing_data, "id");
				
				//reorder billing no per-date
				if(!empty($reorder_date)){
					$sql_in_date = '';
					foreach($reorder_date as $dt){
						if(empty($sql_in_date)){
							$sql_in_date = "billing_no LIKE '".$dt."%'";
						}else{
							$sql_in_date .= " OR billing_no LIKE '".$dt."%'";
						}
					}
					
					
					$reorder_no = array();
					$update_billing_no = array();
					$this->db->select();
					$this->db->from($this->table);
					$this->db->where("txmark = 1 AND is_deleted = 0 AND billing_status = 'paid' AND (".$sql_in_date.")");
					$this->db->order_by("id","ASC");
					$get_billing2 = $this->db->get();
					if($get_billing2->num_rows() > 0){
						
						foreach($get_billing2->result() as $dt_billing){
							
							$billing_no = substr($dt_billing->billing_no, 0, 6);
							if(empty($reorder_no[$billing_no])){
								$reorder_no[$billing_no] = 0;
							}
							
							$reorder_no[$billing_no]++;
							
							$max_str = 4;
							$tot_str = strlen($reorder_no[$billing_no]);
							$repeat_zero = str_repeat("0",($max_str-$tot_str));
							$new_bill_no = $billing_no.$repeat_zero.$reorder_no[$billing_no];
							
							$data_billing = array(
								'id'		=> $dt_billing->id,
								'txmark_no'	=> $new_bill_no
							);
							
							$update_billing_no[] = $data_billing;
						}
						
					}
					
					if(!empty($update_billing_no)){
						//update txmark_no
						$this->db->update_batch($this->table, $update_billing_no, "id");
						
					}
					
					
				}
				
			}
		
			
		}else{
			$get_data['success'] = false;
			$get_data['info'] = 'Data Billing not found';
		}
		
						
		//update-2009.002
		//nontrx-realisasi vs target
		if(!empty($get_opt['nontrx_sales_auto'])){
			if(function_exists('realisasi_nontrx')){
				$update_realisasi = realisasi_nontrx($tgl_cek_mk);
			}
			
		}
		
      	die(json_encode($get_data));
	}

	public function prepareSettlement()
	{
		
		$get_data = array(
			"success" => true,
			"info" => "ok"
		);
		
		$date_from = $this->input->post('date_from');
		$date_till = $this->input->post('date_till');
		$get_data['total_days'] = 0;
		
		if(empty($date_from)){
			$get_data['success'] = false;
			$get_data['info'] = 'Pilih Tanggal Awal';
			die(json_encode($get_data));
		}
		if(empty($date_till)){
			$get_data['success'] = false;
			$get_data['info'] = 'Pilih Tanggal Akhir';
			die(json_encode($get_data));
		}
		
		$date_from_mk = strtotime($date_from);
		$date_till_mk = strtotime($date_till);
		
		$date_total = ceil(($date_till_mk - $date_from_mk) / ONE_DAY_UNIX);
		if($date_total <= 0){
			$date_total = 1;
		}else{
			$date_total += 1;
		}
		
		$get_data['total_days'] = $date_total;
		
		$dt_date = array();
		for($i=0;$i<$date_total;$i++){
			 $curr_mk = $date_from_mk + ($i*ONE_DAY_UNIX);
			 $dt_date[] = date("d-m-Y", $curr_mk);
		}
		$get_data['dt_date'] = $dt_date;
		
      	die(json_encode($get_data));
	}

	public function printTandaiBilling()
	{
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';		
		$session_user = $this->session->userdata('user_username');	
		$role_id = $this->session->userdata('role_id');	
		
		$get_data = array(
			"success" => true,
			"info" => "ok"
		);
		
		$opt_value = array('tandai_pajak_billing');
		$get_opt = get_option_value($opt_value);
		
		if(empty($get_opt['tandai_pajak_billing'])){
			$get_data['success'] = false;
			$get_data['info'] = 'Fitur Tandai Billing Belum Aktif!';
			die(json_encode($get_data));
		}
		
		$id = $this->input->post('id');
		$reorder = $this->input->post('reorder');
		
		if(empty($id)){
			$get_data['success'] = false;
			$get_data['info'] = 'ID not found';
		}
		
		$id = json_decode($id, true);
		$all_id = implode(",", $id);
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where("id IN (".$all_id.")");
		$get_billing = $this->db->get();
		if($get_billing->num_rows() > 0){
			//curr no -> reorder no
			$all_billing_id = array();
			$all_billing_no = array();
			$reorder_no = array();
			
			foreach($get_billing->result() as $dt){
				
				if($dt->txmark == 1 AND !empty($dt->txmark_no)){
					$new_bill_no = $dt->txmark_no;
				}else{
					$new_bill_no = $dt->billing_no;
				}
				
				$all_billing_id[$dt->id] = $new_bill_no;
				$all_billing_no[$dt->billing_no] = $new_bill_no;
			}
			
			$get_data['billing_no'] = $all_billing_no;
			
		}else{
			$get_data['success'] = false;
			$get_data['info'] = 'Billing tidak ditemukan<br/>Pastikan billing sudah ditandai pajak';
		}
		
      	die(json_encode($get_data));
	}
	
	public function show_reprintBillingTax(){
		
		$this->table  = $this->prefix.'billing'; 
		$this->table2 = $this->prefix.'billing_detail';
		$this->product_img_url = RESOURCES_URL.'product/thumb/';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		$client_id = $this->session->userdata('client_id');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		
		if(empty($id)){
			die('Billing data not found!');
		}
		
		$post_data = array(
			'do'	=> '',
			'billing_data'	=> array(),
			'billing_detail'	=> array(),
			'report_name'	=> 'PRINT BILLING',
			'report_place_default'	=> '',
			'session_user'	=> $session_user
		);
		
		$post_data['curr_billing_no'] = 0;
		$post_data['curr_billing_id'] = 0;
		$post_data['curr_table_id'] = 0;
		$post_data['curr_total_guest'] = 0;
		$post_data['curr_billing_notes'] = 0;
		$post_data['dt_curr_billing'] = 0;
		$post_data['curr_billing_date'] = 0;
		$post_data['curr_billing_total'] = 0;
		$post_data['curr_tax_total'] = 0;
		$post_data['curr_service_total'] = 0;
		$post_data['curr_sub_total'] = 0;
		$post_data['curr_grand_total'] = 0;
		$post_data['curr_discount_total'] = 0;
		$post_data['curr_dp_total'] = 0;
		$post_data['curr_pembulatan'] = 0;
		$post_data['curr_compliment_total'] = 0;
		$post_data['curr_table_no'] = 0;
		
		//GET Billing
		$this->db->select('a.*');
		$this->db->from($this->table.' as a');
		$this->db->where('a.is_active = 1');
		$this->db->where('a.id = '.$id);
		$get_billing = $this->db->get();
		if($get_billing->num_rows() > 0){
			$dt_billing = $get_billing->row();
			$post_data['curr_billing_total'] = $dt_billing->total_billing;
			$post_data['curr_tax_total'] = $dt_billing->tax_total;
			$post_data['curr_service_total'] = $dt_billing->service_total;
			$post_data['curr_discount_total'] = $dt_billing->discount_total;
			$post_data['curr_dp_total'] = $dt_billing->total_dp;
			$post_data['curr_grand_total'] = $dt_billing->grand_total;
			$post_data['curr_compliment_total'] = $dt_billing->compliment_total;
			$post_data['curr_pembulatan'] = $dt_billing->total_pembulatan;
		}
		
		$post_data['billing_data'] = (array) $dt_billing;
		
		$opt_value = array(
			'cashier_max_pembulatan',
			'cashier_pembulatan_keatas'
			
		);
		$get_opt = get_option_value($opt_value);
		
		$post_data['curr_sub_total'] = $post_data['curr_billing_total'] + $post_data['curr_tax_total'] + $post_data['curr_service_total'] - $post_data['curr_discount_total'];
		
		//PEMBULATAN				
		/*$total_pembulatan = 0;
		$max_pembulatan = $get_opt['cashier_max_pembulatan'];
		$pembulatan_keatas = $get_opt['cashier_pembulatan_keatas'];
		$last2digit = substr($post_data['curr_sub_total'],-2);
		$last2digit = intval($last2digit);
		$total_pembulatan = $max_pembulatan - $last2digit;
		
		if($last2digit == 100 OR $last2digit == 0){
			$total_pembulatan = 0;
		}
		$pembulatan_show = priceFormat($total_pembulatan);
		
		if(empty($pembulatan_keatas)){
			$total_pembulatan = $total_pembulatan*-1;
		}
		$post_data['curr_pembulatan'] = $total_pembulatan;
		*/
		
		$total_pembulatan = $post_data['curr_pembulatan'];
		$pembulatan_show = priceFormat($total_pembulatan);
		if($total_pembulatan < 0){
			$pembulatan_show = "(".$pembulatan_show.")";
		}
		
		//$post_data['curr_grand_total'] += $post_data['curr_pembulatan'];
		
		
		//is_active_text
		$sortAlias = array(
			//'is_active_text' => 'a.is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, 
								b.product_name, b.product_chinese_name, b.has_varian, b.product_desc, b.product_type, b.product_image, 
								b.category_id, b.product_group, c.product_category_name, d.varian_name, e.item_code, b.product_code",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table2.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'product as b','b.id = a.product_id','LEFT'),
										array($this->prefix.'product_category as c','c.id = b.category_id','LEFT'),
										array($this->prefix.'varian as d','d.id = a.varian_id','LEFT'),
										array($this->prefix.'items as e','e.id = b.id_ref_item','LEFT')
									) 
								),
			'where'			=> array("a.order_qty > 0", 'a.is_deleted' => 0, 'a.billing_id' => $id),
			'order'			=> array('a.id' => 'ASC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		$get_data = $this->m->find_all($params);
		
		
		//cek opt
		$get_opt = get_option_value(array('hide_compliment_order'));
  		$hide_compliment_order = 0;
		if(!empty($get_opt['hide_compliment_order'])){
			$hide_compliment_order = 1;
		}
		
  		$newData = array();
		$no = 1;
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				$s['item_no'] = $no;
				$s['order_total'] = $s['order_qty'] * $s['product_price'];
				
				$s['order_total_real'] = $s['order_qty'] * $s['product_price'];
				if(!empty($s['product_price_real'])){
					$s['order_total_real'] = $s['order_qty'] * $s['product_price_real'];
				}
				
				if(empty($s['product_image'])){
					$s['product_image'] = 'no-image.jpg';
				}
				$s['product_image_show'] = '<img src="'.$this->product_img_url.$s['product_image'].'" style="max-width:80px; max-height:60px;"/>';
				$s['product_image_src'] = $this->product_img_url.$s['product_image'];
				
				$s['product_price_show'] = 'Rp '.priceFormat($s['product_price']);		
				$s['order_total_show'] = 'Rp '.priceFormat($s['order_total']);		
				
				if(empty($s['product_code'])){
					$s['product_code'] = $s['item_code'];
				}
				
				$s['product_detail_info'] = $s['product_code'].'<br/>'.$s['product_name'];
				
				$additional_text = '';
				if(!empty($s['product_chinese_name']) AND $s['product_chinese_name'] != '-'){
					$additional_text = '<br/>'.$s['product_chinese_name'];
				}
				
				if(!empty($s['varian_name'])){
					if($additional_text == ''){
						$additional_text = '<br/>('.$s['varian_name'].')';
					}else{
						$additional_text .= ' ('.$s['varian_name'].')';
					}
				}
				
				//$s['product_detail_info'] .= $additional_text.'<br/>X @ Rp.'.priceFormat($s['product_price']);				
				
				
					
				//PROMO UPDATE
				if($s['is_promo'] == 1){
					
					//if(empty($s['product_normal_price'])){
						//$s['product_normal_price_promo'] = $s['product_price']+$s['promo_price'];
						$s['product_normal_price_promo'] = $s['product_price'];
					//}
					
					$promo_price = $s['product_price']-$s['promo_price'];
					
					$s['promo_price_show'] = priceFormat($promo_price);
					$s['product_name_show'] = $s['product_name'].' <font color="orange">Promo</font>';
					$s['product_price_show'] = 'Rp <strike>'.priceFormat($s['product_normal_price_promo']).'</strike> <font color="orange">'.$s['promo_price_show'].'</font>';
					$s['product_detail_info'] = $s['product_name'].$additional_text.' <font color="orange">Promo</font><br/>X @ Rp.'.priceFormat($s['product_price']);
					


				}
						
				//BUY AND GET
				if($s['is_buyget'] == 1){
					
					$s['product_name_show'] = $s['product_name'].' <font color="red">BG</font>';
					
					$s['product_detail_info'] = $s['product_name'].$additional_text.' <font color="red">BG</font><br/>X @ Rp.'.priceFormat($s['product_price']);
					
					if($s['is_promo'] == 1){
						$s['product_detail_info'] = $s['product_name'].$additional_text.' <font color="orange">Promo</font>,<font color="red">BG</font><br/>X @ Rp.'.priceFormat($s['product_price']);
					}
					
				}
				
				//FREE				
				if($s['free_item'] == 1){
					$s['product_name_show'] = $s['product_name'].' <font color="red">Free</font>';
					$s['product_detail_info'] = $s['product_name'].$additional_text.' <font color="red">Free</font><br/>X @ Rp.'.priceFormat($s['product_price']);
					
				}
				
				$s['order_status_text'] = '<b style="color:orange;">'.ucwords($s['order_status']).'</b>';
				if($s['order_status'] == 'done'){
					$s['order_status_text'] = '<b style="color:green;">Print To<br/>';
					
					if($s['product_group'] == 'food'){
						$s['order_status_text'] .= 'Kitchen</b>';
					}else
					if($s['product_group'] == 'beverage'){
						$s['order_status_text'] .= 'Bar</b>';
					}else
					if($s['product_group'] == 'other'){
						$s['order_status_text'] .= 'Other</b>';
					}else{
						$s['order_status_text'] .= '??</b>';
					}
				}
				
				if(!empty($s['order_notes'])){
					$s['product_detail_info'] .= '<br/>Note: <i>'.$s['order_notes'].'</i>';
				}
				
				//TAX, SERVICE, TAKEAWAY & COMPLIMENT
				if(empty($s['tax_total'])){
					$s['tax_total'] = 0;
				}
				
				if(empty($s['service_total'])){
					$s['service_total'] = 0;
				}
				
				if(empty($s['discount_total'])){
					$s['discount_total'] = 0;
				}
				
				$s['tax_total_show'] = priceFormat($s['tax_total']);
				$s['service_total_show'] = priceFormat($s['service_total']);
				$s['discount_total_show'] = priceFormat($s['discount_total']);
				
				if($s['is_takeaway'] == '1'){
					$s['is_takeaway_text'] = '<span style="color:green;">Yes</span>';
					
					if($s['takeaway_no_tax'] == 1){
						$s['include_tax'] = 0;
						$s['tax_percentage'] = 0;
						$s['tax_total'] = 0;
					}
					
					if($s['takeaway_no_service'] == 1){
						$s['include_service'] = 0;
						$s['service_percentage'] = 0;
						$s['service_total'] = 0;
					}
					
				}else{
					$s['is_takeaway_text'] = '<span style="color:red;">No</span>';
				}
				
				if($s['is_compliment'] == '1'){
					$s['is_compliment_text'] = '<span style="color:green;">Yes</span>';
					$s['include_service'] = 0;
					$s['service_percentage'] = 0;
					$s['service_total'] = 0;
					
					$s['include_tax'] = 0;
					$s['tax_percentage'] = 0;
					$s['tax_total'] = 0;
					
					$s['tax_total_show'] = priceFormat($s['tax_total']);
					$s['service_total_show'] = priceFormat($s['service_total']);
				}else{
					$s['is_compliment_text'] = '<span style="color:red;">No</span>';
				}
				
				$s['hide_compliment_order'] = $hide_compliment_order;
				
				$no++;
				array_push($newData, $s);
			}
		}
		
		$post_data['billing_detail'] = $newData;
		
		//echo '<pre>';
		//print_r($post_data);
		//die();
		
		//DO-PRINT
		if(!empty($do)){
			$post_data['do'] = $do;
		}else{
			$do = '';
		}
		
		$useview = 'printPaidBilling';
		$this->load->view('../../billing/views/'.$useview, $post_data);
		
	}
	
}