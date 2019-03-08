<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Reservation extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_reservation', 'm');
		$this->load->model('model_reservationdetail', 'm2');
		$this->load->model('inventory/model_stock', 'stock');
		$this->load->model('account_receivable/model_account_receivable', 'account_receivable');
		$this->load->model('cashflow/model_penerimaan_kas', 'penerimaan_kas');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'reservation';
		$this->table2 = $this->prefix.'reservation_detail';
		$session_client_id = $this->session->userdata('client_id');
		$session_user = $this->session->userdata('user_username');		
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		if(empty($session_user)){
			$r = array('success' => false, 'data' => array(), 'totalCount' => 0, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		//is_active_text
		$sortAlias = array(
			//'is_active_text' => 'is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, a.id as reservation_id, e.sales_name, e.sales_company, f.bank_name',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'sales as e','e.id = a.sales_id','LEFT'),
										array($this->prefix.'bank as f','f.id = a.bank_id','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('a.id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		//$is_active = $this->input->post('is_active');
		$reservation_status = $this->input->post('reservation_status');
		$not_cancel = $this->input->post('not_cancel');
		$from_cashier = $this->input->post('from_cashier');
		$skip_date = $this->input->post('skip_date');
		
		//FILTER
		$date_from = $this->input->post('date_from');
		$date_till = $this->input->post('date_till');
		$keywords = $this->input->post('keywords');
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if($skip_date == true){
		
		}else{
		
			if(empty($date_from) AND empty($date_till)){
				$date_from = date('Y-m-d');
				$date_till = date('Y-m-d');
			}
			
			if(!empty($date_from) OR !empty($date_till)){
			
				if(empty($date_from)){ $date_from = date('Y-m-d'); }
				if(empty($date_till)){ $date_till = date('Y-m-t'); }
				
				$mktime_dari = strtotime($date_from);
				$mktime_sampai = strtotime($date_till);
							
				$qdate_from = date("Y-m-d",strtotime($date_from));
				$qdate_till = date("Y-m-d",strtotime($date_till));
				
				$params['where'][] = "(a.reservation_date >= '".$qdate_from."' AND a.reservation_date <= '".$qdate_till."') OR (a.preparing_date >= '".$qdate_from."' AND a.preparing_date <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.reservation_number' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.reservation_number LIKE '%".$searching."%' OR a.reservation_customer_name LIKE '%".$searching."%')";
		}		
		if(!empty($from_cashier)){
			//$params['where'][] = "(a.billing_id IS NULL OR a.billing_id = 0)";
			$params['where'][] = "(a.reservation_status = 'done' OR a.billing_id IS NOT NULL)";
		}		
		//if(!empty($is_active)){
		//	$params['where'][] = "a.is_active = '".$is_active."'";
		//}
		if(!empty($not_cancel)){
			$params['where'][] = "a.reservation_status != 'cancel'";
		}else{
			if(!empty($reservation_status)){
				$params['where'][] = "a.reservation_status = '".$reservation_status."'";
			}
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		
  		$newData = array();		
		$all_id = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				if(!in_array($s['id'], $all_id)){
					$all_id[] = $s['id'];
				}
			}
		}
		
		
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				//$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				$s['reservation_number_show'] = $s['reservation_number'];
				if(!empty($s['billing_id'])){
					$s['reservation_number_show'] = '<span style="color:red;">'.$s['reservation_number'].'</span>';
				}
				
				if($s['reservation_status'] == 'progress'){
					$s['reservation_status_text'] = '<span style="color:blue;">Progress</span>';
				}else 
				if($s['reservation_status'] == 'done'){
					$s['reservation_status_text'] = '<span style="color:green;">Done</span>';
				}else{
					$s['reservation_status_text'] = '<span style="color:red;">Cancel</span>';
				}
				
				$s['reservation_total_price_show'] = '<span style="color:blue; font-weight:bold;">'.priceFormat($s['reservation_total_price']).'</span>';
				$s['reservation_sub_total_show'] = priceFormat($s['reservation_sub_total']);
				$s['reservation_discount_show'] = '<span style="color:orange; font-weight:bold;">'.priceFormat($s['reservation_discount']).'</span>';
				$s['reservation_tax_show'] = priceFormat($s['reservation_tax']);
				$s['reservation_service_show'] = priceFormat($s['reservation_service']);
				$s['reservation_total_hpp_show'] = priceFormat($s['reservation_total_hpp']);
				$s['reservation_dp_show'] = '<span style="color:green; font-weight:bold;">'.priceFormat($s['reservation_dp']).'</span>';
				
				$s['reservation_status_old'] = $s['reservation_status'];
				$s['reservation_date_text'] = date("d-m-Y",strtotime($s['reservation_date']));
				$s['reservation_date_time'] = date("d-m-Y",strtotime($s['reservation_date'])).'<br/>'.$s['reservation_time'];
				$s['preparing_date_text'] = date("d-m-Y",strtotime($s['preparing_date']));
				$s['preparing_date_time'] = date("d-m-Y",strtotime($s['preparing_date'])).'<br/>'.$s['preparing_time'];
				$s['tanggal_pesan'] = date("d-m-Y H:i",strtotime($s['created']));
				
				if($s['reservation_payment'] == 'credit_ar'){
					$s['reservation_payment_text'] = '<span style="color:red;">CREDIT - AR</span>';
				}else{
					
					$s['reservation_payment_text'] = '<span style="color:green;">'.strtoupper($s['reservation_payment']).'</span>';
					if($s['reservation_payment'] == 'debit' OR $s['reservation_payment'] == 'credit'){
						$s['reservation_payment_text'] = '<span style="color:blue;">'.strtoupper($s['reservation_payment']).' CARD</span>';
					}
					
				}
				
				$s['reservation_from_text'] = '<span style="color:blue;">'.ucwords($s['reservation_from']).'</span>';
				
				$text_tipe = 'Dine In';
				if($s['reservation_tipe'] == 'takeaway'){
					$text_tipe = 'Take Away';
				}
				if($s['reservation_tipe'] == 'delivery'){
					$text_tipe = 'Delivery';
				}
				$s['reservation_tipe_text'] = '<span style="color:green;">'.$text_tipe.'</span>';
				
				
				$s['reservation_customer_phone_all'] = '';
				if(!empty($s['reservation_customer_phone'])){
					$s['reservation_customer_phone_all'] = $s['reservation_customer_phone'];
				}
				
				if(!empty($s['reservation_customer_phone2'])){
					if(empty($s['reservation_customer_phone_all'])){
						$s['reservation_customer_phone_all'] = $s['reservation_customer_phone2'];
					}else{
						$s['reservation_customer_phone_all'] .= '<br/>'.$s['reservation_customer_phone2'];
					}
				}
				
				if(!empty($s['reservation_customer_phone3'])){
					if(empty($s['reservation_customer_phone_all'])){
						$s['reservation_customer_phone_all'] = $s['reservation_customer_phone3'];
					}else{
						$s['reservation_customer_phone_all'] .= '<br/>'.$s['reservation_customer_phone3'];
					}
				}
				
				$s['reservation_customer_name_address'] = $s['reservation_customer_name'].'<br/>'.$s['reservation_customer_address'];
				
				//$s['total_item'] = 0;
				//if(!empty($total_item[$s['id']])){
				//	$s['total_item'] = $total_item[$s['id']];
				//}
				
				//sales
				$s['sales_name_company_fee'] = '-- NO SALES --';
				if(!empty($s['sales_id'])){
					$sales_type_simple = 'A';
					if($s['sales_type'] == 'before_tax'){
						$sales_type_simple = 'B';
					}
					if(!empty($s['sales_percentage'])){
						$jenis_fee = $s['sales_percentage'].'%';
					}else{
						$jenis_fee = $s['sales_price'];
					}
					
					$s['sales_name_company_fee'] = $s['sales_name'].' / '.$s['sales_company'].' ('.$sales_type_simple.' '.$jenis_fee.')';
				}
				
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetail()
	{
		
		$this->table = $this->prefix.'reservation_detail';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, b.product_name, c.varian_name",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'product as b','a.product_id = b.id','LEFT'),
										array($this->prefix.'varian as c','a.varian_id = c.id','LEFT'),
									) 
								),
			'order'			=> array('a.id' => 'DESC'),
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$reservation_id = $this->input->post('reservation_id');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('b.product_name' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.product_name LIKE '%".$searching."%')";
		}
		if(!empty($reservation_id)){
			$params['where'] = array('a.reservation_id' => $reservation_id);
		}
		
		//get data -> data, totalCount
		$get_data = $this->m2->find_all($params);
		
		$newData = array();	
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['resd_hpp_show'] = priceFormat($s['resd_hpp']);
				$s['resd_price_show'] = priceFormat($s['resd_price']);
				$s['resd_tax_show'] = priceFormat($s['resd_tax']);
				$s['resd_service_show'] = priceFormat($s['resd_service']);
				$s['resd_potongan_show'] = priceFormat($s['resd_potongan']);
				$s['resd_total_show'] = priceFormat($s['resd_total']);
				$s['resd_grandtotal_show'] = priceFormat($s['resd_grandtotal']);
				$s['product_name_varian'] = $s['product_name'];
				
				if(!empty($s['varian_name'])){
					$s['product_name_varian'] = $s['product_name'].' - '.$s['varian_name'];
				}
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		  		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'reservation';	
		$this->table2 = $this->prefix.'reservation_detail';			
		
		$this->prefix_acc = config_item('db_prefix3');
		$this->table_account_receivable = $this->prefix_acc.'account_receivable';	
		$session_user = $this->session->userdata('user_username');
		
		$reservation_date = $this->input->post('reservation_date');
		$reservation_time = $this->input->post('reservation_time');
		$preparing_date = $this->input->post('preparing_date');
		$preparing_time = $this->input->post('preparing_time');
		$reservation_memo = $this->input->post('reservation_memo');
		
		$reservation_tipe = $this->input->post('reservation_tipe');
		$reservation_from = $this->input->post('reservation_from');
		$reservation_status = $this->input->post('reservation_status');
		
		$reservation_customer_name = $this->input->post('reservation_customer_name');
		$reservation_customer_address = $this->input->post('reservation_customer_address');
		$reservation_customer_phone = $this->input->post('reservation_customer_phone');
		$reservation_customer_phone2 = $this->input->post('reservation_customer_phone2');
		$reservation_customer_phone3 = $this->input->post('reservation_customer_phone3');
		
		$reservation_total_hpp = $this->input->post('reservation_total_hpp');
		$reservation_sub_total = $this->input->post('reservation_sub_total');
		$reservation_discount = $this->input->post('reservation_discount');
		$reservation_tax = $this->input->post('reservation_tax');
		$reservation_service = $this->input->post('reservation_service');
		$reservation_total_price = $this->input->post('reservation_total_price');
		$reservation_dp = $this->input->post('reservation_dp');
		$reservation_payment = $this->input->post('reservation_payment');
		
		$customer_id = $this->input->post('customer_id');
		$bank_id = $this->input->post('bank_id');
		$card_no = $this->input->post('card_no');
		$card_no_display = $this->input->post('card_no_display');
		
		if(!empty($card_no_display)){
			$card_no = $card_no_display;
		}
		
		//sales
		$sales_id = $this->input->post('sales_id');
		$sales_percentage = $this->input->post('sales_percentage');
		$sales_price = $this->input->post('sales_price');
		$sales_type = $this->input->post('sales_type');
		
		$total_guest = $this->input->post('total_guest');
		
		if(empty($total_guest)){
			$total_guest = 1;
			//$r = array('success' => false, 'info' => 'Input Total Guest!');
			//die(json_encode($r));
		}
		
		if(empty($reservation_from)){
			$r = array('success' => false, 'info' => 'Select Reserve From!');
			die(json_encode($r));
		}
		
		$total_item = 0;
		$total_reservation = 0;
		//reservationDetail				
		$reservationDetail = $this->input->post('reservationDetail');
		$reservationDetail = json_decode($reservationDetail, true);
		if(!empty($reservationDetail)){
			$total_item = count($reservationDetail);
			foreach($reservationDetail as $key => $dtDet){
				$total_reservation += $dtDet['resd_qty'];
			}
		}

		
		$get_reservation_number = $this->generate_reservation_number();
		
		if(empty($get_reservation_number)){
			$r = array('success' => false);
			die(json_encode($r));
		}	
		
		if($reservation_status == 'done'){
			
			if($total_reservation == 0){
				$r = array('success' => false, 'info' => 'Total item masuk = 0!'); 
				die(json_encode($r));
			}
			
		}
		
		
		$form_type = $this->input->post('form_type_reservation', true);
		
		$r = '';
		if($form_type == 'add')
		{
			
			$var = array(
				'fields'	=>	array(
				    'reservation_number'  	=> 	$get_reservation_number,
				    'reservation_date'  		=> 	$reservation_date,
				    'reservation_time'  		=> 	$reservation_time,
				    'preparing_date'  			=> 	$preparing_date,
				    'preparing_time'  			=> 	$preparing_time,
				    'reservation_memo'  		=> 	$reservation_memo,
				    'reservation_tipe'  		=> 	$reservation_tipe,
				    'reservation_from'  		=> 	$reservation_from,
				    'reservation_status'  	=> 	$reservation_status,
					'customer_id'			=>	$customer_id,
				    'reservation_customer_name'  	=> 	$reservation_customer_name,
				    'reservation_customer_address'  	=> 	$reservation_customer_address,
				    'reservation_customer_phone'  	=> 	$reservation_customer_phone,
				    'reservation_customer_phone2'  	=> 	$reservation_customer_phone2,
				    'reservation_customer_phone3'  	=> 	$reservation_customer_phone3,
				    'reservation_total_qty'  => 	$total_reservation,
				    'reservation_discount'  	=> $reservation_discount,
				    'reservation_tax'  		=> $reservation_tax,
				    'reservation_service'  	=> $reservation_service,
				    'reservation_sub_total'  => $reservation_sub_total,
				    'reservation_total_price'  => $reservation_total_price,
				    'reservation_dp'  		=> $reservation_dp,
					'reservation_payment'  	=> 	$reservation_payment,
					'reservation_total_hpp'  	=> 	$reservation_total_hpp,
					'sales_id'		=>	$sales_id,
					'sales_percentage'	=>	$sales_percentage,
					'sales_price'		=>	$sales_price,
					'sales_type'		=>	$sales_type,
					'bank_id'		=>	$bank_id,
					'card_no'		=>	$card_no,
					'total_guest'		=>	$total_guest,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				),
				'table'		=>  $this->table
			);	
			
			
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
			$save_data = $this->m->add($var);
			$insert_id = $this->m->get_insert_id();
			$this->lib_trans->commit();			
			if($save_data)
			{  
				$id = $insert_id;
				
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($form_type == 'edit'){
			
			//UPDATE
			$id = $this->input->post('id', true);
			
			
			if(empty($id)){
				$r = array('success' => false, 'info' => 'Reservation unidentified!'); 
				die(json_encode($r));	
			}
			
			
			$var = array('fields'	=>	array(
					//'reservation_number'  	=> 	$reservation_number,
					'reservation_date'  		=> 	$reservation_date,
					'reservation_time'  		=> 	$reservation_time,
				    'preparing_date'  			=> 	$preparing_date,
				    'preparing_time'  			=> 	$preparing_time,
				    'reservation_memo'  		=> 	$reservation_memo,
				    'reservation_tipe'  		=> 	$reservation_tipe,
				    'reservation_from'  		=> 	$reservation_from,
					'customer_id'			=>	$customer_id,
				    'reservation_customer_name'  	=> 	$reservation_customer_name,
				    'reservation_customer_address'  	=> 	$reservation_customer_address,
				    'reservation_customer_phone'  	=> 	$reservation_customer_phone,
				    'reservation_customer_phone2'  	=> 	$reservation_customer_phone2,
				    'reservation_customer_phone3'  	=> 	$reservation_customer_phone3,
					'reservation_memo'  		=> 	$reservation_memo,
					'reservation_from'  		=> 	$reservation_from,
					'reservation_total_qty'  => 	$total_reservation,
				    'reservation_discount'  	=> $reservation_discount,
				    'reservation_tax'  		=> $reservation_tax,
				    'reservation_service'  	=> $reservation_service,
				    'reservation_sub_total'  => $reservation_sub_total,
				    'reservation_total_price'  => $reservation_total_price,
				    'reservation_dp'  		=> $reservation_dp,
					'reservation_payment'  	=> 	$reservation_payment,
					'reservation_total_hpp'  	=> 	$reservation_total_hpp,
					'sales_id'		=>	$sales_id,
					'sales_percentage'	=>	$sales_percentage,
					'sales_price'		=>	$sales_price,
					'sales_type'		=>	$sales_type,
					'bank_id'		=>	$bank_id,
					'card_no'		=>	$card_no,
					'total_guest'		=>	$total_guest,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			
			$old_data = array();
			
			//CEK OLD DATA
			$this->db->from($this->table);
			$this->db->where("id = '".$id."'");
			$get_dt = $this->db->get();
			
			if($get_dt->num_rows() > 0){
				$old_data = $get_dt->row_array();
			}
			
			if($old_data['reservation_status'] != $reservation_status){
				
				
				$var['fields']['reservation_status'] = $reservation_status;
				
				if($old_data['reservation_status'] == 'done' AND $reservation_status == 'progress'){
					//CEK PEMBAYARAN AP != kontrabon
					$this->db->from($this->prefix_acc.'account_receivable');
					$this->db->where("ref_id = '".$id."'");
					$this->db->where("ar_tipe = 'salesorder' AND is_deleted = 0");
					$get_stat_ar = $this->db->get();	
					if($get_stat_ar->num_rows() > 0){
						
						$dt_ar = $get_stat_ar->row();
						
						if($dt_ar->ar_status == 'pengakuan' OR $dt_ar->ar_status == 'posting'){
							
						}else
						if($dt_ar->ar_status == 'invoice'){
							$r = array('success' => false, 'info' => 'Tidak boleh update status ke Progress<br/>Silahkan Cek Status AR/Piutang: '.$dt_ar->ar_no.', AR/Piutang sudah dibuat Invoice: '.$dt_ar->no_invoice); 
							die(json_encode($r));
						}else
						if($dt_ar->ar_status == 'pembayaran'){
							$r = array('success' => false, 'info' => 'Tidak boleh update status ke Progress<br/>Silahkan Cek Status AR/Piutang: '.$dt_ar->ar_no.', AR/Piutang sudah selesai s/d pembayaran'); 
							die(json_encode($r));
						}else{
							$r = array('success' => false, 'info' => 'Tidak boleh update status ke Progress<br/>Silahkan Cek Status AR/Piutang: '.$dt_ar->ar_no.', AR/Piutang sudah sampai tahap Jurnal/Posting ke Bag.Keuangan'); 
							die(json_encode($r));
						}
						
						
					}
					
					//DP - Cashflow
					$this->db->from($this->prefix_acc.'penerimaan_kas');
					$this->db->where("ref_id = '".$id."'");
					$this->db->where("km_tipe = 'salesorder' AND is_deleted = 0");
					$get_stat_km = $this->db->get();	
					if($get_stat_km->num_rows() > 0){
						
						$dt_km = $get_stat_km->row();
						
						if($dt_km->km_status == 'pengakuan'){
							
						}else
						{
							$r = array('success' => false, 'info' => 'Tidak boleh update status ke Progress<br/>Silahkan Cek Status Penerimaan Kas/DP: '.$dt_km->km_no.', <br/>Penerimaan Kas/DP sudah sampai tahap Jurnal/Posting ke Bag.Keuangan'); 
							die(json_encode($r));
						}
						
						
					}
					
					//Cek billing
					$this->db->from($this->prefix.'billing');
					$this->db->where("id = '".$old_data['billing_id']."'");
					$get_stat_billing = $this->db->get();	
					if($get_stat_billing->num_rows() > 0){
						
						$dt_billing = $get_stat_billing->row();
						
						if($dt_billing->billing_status == 'paid'){
							$r = array('success' => false, 'info' => 'Tidak boleh update status ke Progress<br/>Reservasi sudah digunakan pada billing: <b>'.$dt_billing->billing_no.'</b>,<br/>Status billing sudah dibayar/lunas'); 
							die(json_encode($r));
						}else
						{
							//$r = array('success' => false, 'info' => 'Tidak boleh update status ke Progress<br/>Reservasi sudah digunakan pada billing: '.$dt_billing->billing_no.', status billing masih progress'); 
							//die(json_encode($r));
							//$var['fields']['billing_id'] = '';
							//$var['fields']['billing_no'] = '';
							
						}
						
						
					}
					
				}
				
				//update 2018-01-07
				if($old_data['reservation_status'] == 'progress' AND $reservation_status == 'done'){
					//check bill re-lock billing to reservation
				}
				
			}else{
				
				if($old_data['reservation_status'] == 'done'){
					//$r = array('success' => false, 'info' => 'Cannot Update Reservation Data been Done!'); 
					//die(json_encode($r));	
				}
				
			}
			
			
			$this->lib_trans->begin();
			$save_data = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
		}
		
		if($save_data)
		{ 
			$r = array('success' => true, 'id' => $id);
			
			$old_status = '';
			if(!empty($old_data['reservation_status'])){
				$old_status = $old_data['reservation_status'];
			}
			
			$q_det = $this->m2->reservationDetail($reservationDetail, $id);
			
			if($q_det == false){
				$r = array('success' => false, 'info' => 'Input Detail Reservation Failed!'); 
				die(json_encode($r));
			}
			
			$r['det_info'] = $q_det;
			
			if(!empty($q_det['dtReservation']['reservation_number'])){
				$r['reservation_number'] = $q_det['dtReservation']['reservation_number'];
			}
				
			//$updateAR = $this->account_receivable->set_account_receivable_Reservation($id);
			if(!empty($old_data)){
					
				if(($old_data['reservation_status'] == 'done' AND $reservation_status == 'progress') OR ($old_data['reservation_status'] == 'progress' AND $reservation_status == 'done')){
					if($old_data['reservation_payment'] == 'credit_ar' AND ($old_data['reservation_status'] == 'done' AND $reservation_status == 'progress')){
						$updateAR = $this->account_receivable->set_account_receivable_Reservation($id, $old_data['reservation_status']);
						$updateCF = $this->penerimaan_kas->set_DP_Reservation($id, $old_data['reservation_status']);
						
						if($updateAR === true || $updateAR === false){
							$r['updateReservation'] = $old_data['reservation_status'].' to '.$reservation_status;
						}else
						if($updateAR == 'invoice'){
							
							$no_invoice = '-';
							$this->db->from($this->table_account_receivable);
							$this->db->where("ar_tipe = 'salesorder'");
							$this->db->where("ref_id = '".$id."'");
							$get_ar = $this->db->get();
							if($get_ar->num_rows() > 0){
								
								$data_AR = $get_ar->row();
								$no_invoice = $data_AR->no_invoice;
								
							}
							
							$r['success'] = false;
							$r['info'] = 'Silahkan Cek dan Hapus Invoice: '.$no_invoice.' terkait Reservation: '.$old_data['reservation_number'];
							$r['updateReservation'] = $old_data['reservation_status'].' to '.$reservation_status;
							$r['updateAR'] = $updateAR;
							
							$rollback_reservation_status = array(
								'reservation_status'	=> $old_data['reservation_status']
							);
							$this->db->update($this->table, $rollback_reservation_status, "id = '".$id."'");
							
						}
						
					}else{
						$updateAR = $this->account_receivable->set_account_receivable_Reservation($id);
						$updateCF = $this->penerimaan_kas->set_DP_Reservation($id);
					}
				}
				
			}else{
				$updateAR = $this->account_receivable->set_account_receivable_Reservation($id);
				$updateCF = $this->penerimaan_kas->set_DP_Reservation($id);
			}
			
			
		}  
		else
		{  
			$r = array('success' => false);
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function delete()
	{
		
		$this->table = $this->prefix.'reservation';
		$this->table_billing = $this->prefix.'billing';
		$this->table2 = $this->prefix.'reservation_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		$billing_id = 0;
		//check data main if been validated
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		//$this->db->where("reservation_status IN ('done')");
		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			$dt_old = $get_dt->row();
			if($dt_old->reservation_status == 'done'){
				$r = array('success' => false, 'info' => 'Cannot Delete Reservation, Status is been done!</br>Please Refresh List Reservation'); 
				die(json_encode($r));
			}	
			
			//update 2018-01-07
			if(!empty($dt_old->billing_id)){
				$billing_id = $dt_old->billing_id;
			}
		}		
		
		//update 2018-01-07
		if(!empty($billing_id)){
			$this->db->from($this->table_billing);
			$this->db->where("id IN ('".$billing_id."')");
			//$this->db->where("reservation_status IN ('done')");
			$get_bill = $this->db->get();
			if($get_bill->num_rows() > 0){
				$dt_bill = $get_bill->row();
				if($dt_bill->billing_status == 'paid' OR $dt_bill->billing_status == 'hold'){
					$r = array('success' => false, 'info' => 'Cannot Delete Reservation, Reservation connected to Billing: #'.$dt_bill->billing_no.'!</br>Please Set Reservation Status to Done OR set Cancel Related Billing!'); 
					die(json_encode($r));
				}
			}
		}
		
		//delete data
		$update_data = array(
			'reservation_status'	=> 'cancel',
			'is_deleted' => 1
		);
		
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->update($this->table, $update_data);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
			
			//delete detail too
			//$update_data2 = array(
			//	'resd_status'	=> 'cancel'
			//);
			
			//$this->db->where("reservation_id IN ('".$sql_Id."')");
			//$this->db->update($this->table2, $update_data2);
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Cancel Reservation Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function deleteDetail()
	{
		
		$this->table = $this->prefix.'reservation_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//check data main if been done
		$this->db->where("id IN ('".$sql_Id."')");
		$this->db->where("resd_status = 1");
		$get_dt = $this->db->get($this->table);
		if($get_dt->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Cannot Delete Data, Reservation been done!'); 
			die(json_encode($r));		
		}
		
		//delete data
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->delete($this->table);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Reservation Detail Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function get_total_qty($reservation_id){
		$this->table = $this->prefix.'reservation_detail';	
		
		$this->db->select('SUM(resd_dikirim) as total_qty');
		$this->db->from($this->table);
		$this->db->where('reservation_id', $reservation_id);
		$get_tot = $this->db->get();
		
		$total_qty = 0;
		if($get_tot->num_rows() > 0){
			$data_ro = $get_tot->row();
			$total_qty = $data_ro->total_qty;
		}
		
		return $total_qty;
	}
	
	
	public function generate_reservation_number(){
		$this->table = $this->prefix.'reservation';						
		
		$default_RSV = "RSV".date("ym");
		$this->db->from($this->table);
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_ro = $get_last->row();
			//$reservation_number = $data_ro->reservation_number;
			$reservation_number = str_replace($default_RSV,"", $data_ro->reservation_number);
						
			$reservation_number = (int) $reservation_number;			
		}else{
			$reservation_number = 0;
		}
		
		$reservation_number++;
		$length_no = strlen($reservation_number);
		switch ($length_no) {
			case 3:
				$reservation_number = $reservation_number;
				break;
			case 2:
				$reservation_number = '0'.$reservation_number;
				break;
			case 1:
				$reservation_number = '00'.$reservation_number;
				break;
			default:
				$reservation_number = $reservation_number;
				break;
		}
				
		return $default_RSV.$reservation_number;				
	}
	
	public function printReservation(){
		
		$this->table  = $this->prefix.'reservation'; 
		$this->table2 = $this->prefix.'reservation_detail';
		$this->table_client  = config_item('db_prefix').'clients';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		
		$client_id = $this->session->userdata('client_id');					
		
		//get client
		$this->db->from($this->table_client);
		$this->db->where("id",$client_id);
		$get_client = $this->db->get();
		$dt_client = array();
		if($get_client->num_rows() > 0){
			$dt_client = $get_client->row_array();
		}
		
		$data_post = array(
			'do'	=> '',
			'reservation_data'	=> array(),
			'reservation_detail'	=> array(),
			'report_place_default'	=> '',
			'user_fullname'	=> $user_fullname,
			'session_user'	=> $session_user,
			'client'	=> $dt_client
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($reservation_id)){
			die('Reservation Not Found!');
		}else{
			
			$this->db->select("a.*, d.bank_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix."bank as d","d.id = a.bank_id","LEFT");
			
			$this->db->where("a.id = '".$reservation_id."'");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['reservation_data'] = $get_dt->row_array();
				
				//get detail
				$this->db->select("a.*, b.product_name, b.product_type, c.varian_name");
				$this->db->from($this->table2." as a");
				$this->db->join($this->prefix."product as b","b.id = a.product_id","LEFT");
				$this->db->join($this->prefix."varian as c","c.id = a.varian_id AND a.has_varian = 1","LEFT");
				$this->db->where("a.reservation_id = '".$reservation_id."'");
				$get_det = $this->db->get();
				if($get_det->num_rows() > 0){
					$data_post['reservation_detail'] = $get_det->result_array();
				}
				
			}else{
				die('Reservation Not Found!');
			}
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}
		
		$print_layout = 'printReservation';
		if(!empty($lx_print)){
			$print_layout = 'printReservation-LX';
		}
		if(!empty($thermal_print)){
			//$print_layout = 'printReservation-Thermal';
			
			extract($data_post);
			
			$session_user = $this->session->userdata('user_username');
			$id_user = $this->session->userdata('id_user');
			$ip_addr = get_client_ip();

			$opt_value = array(
				'reservationReceipt_layout',
				'reservationReceipt_invoice_layout',
				'printer_id_reservationReceipt_default',
				'printer_id_reservationReceipt_'.$ip_addr,
				
			);
			$get_opt = get_option_value($opt_value);

			//ID Printer ----------------------
			$printer_id_reservationReceipt = $get_opt['printer_id_reservationReceipt_default'];
			if(!empty($get_opt['printer_id_reservationReceipt_'.$ip_addr])){
				$printer_id_reservationReceipt = $get_opt['printer_id_reservationReceipt_'.$ip_addr];
			}

			//GET PRINTER DATA
			$this->db->from($this->prefix.'printer');		
			$this->db->where("id", $printer_id_reservationReceipt);		
			$get_printer = $this->db->get();

			$data_printer = array();
			if($get_printer->num_rows() > 0){
				$data_printer = $get_printer->row_array();
			}else{
				echo 'Printer Tidak Ditemukan!';
				die();
			}	

			//update -- 2018-01-23
			$printer_ip_reservationReceipt = $data_printer['printer_ip'];			
			if(strstr($printer_ip_reservationReceipt, '\\')){
				$printer_ip_reservationReceipt = "\\\\".$printer_ip_reservationReceipt;
			}	

			$printer_pin_reservationReceipt = $data_printer['printer_pin'];
			$printer_type_reservation = $data_printer['printer_tipe'];

			if(!empty($print_anywhere)){
				$printer_type_reservation = $print_anywhere->printer_tipe;
			}

			$reservationReceipt_layout = $get_opt['reservationReceipt_layout'];
			if(!empty($print_type)){
				$reservationReceipt_layout = $get_opt['reservationReceipt_invoice_layout'];
			}

			$printer_pin_reservationReceipt = trim(str_replace("CHAR", "", $printer_pin_reservationReceipt));

			$no_limit_text = false;
			if($data_printer['print_method'] == 'ESC/POS'){
				//$no_limit_text = false;
			}

			$grand_total = 0;
			$subtotal = 0;
			$discount_total = 0;
			$total_subtotal = 0;
			$tax_total = 0;
			$service_total = 0;
			$total_dp = 0;
			$order_data = '';
			$no = 1;

			//trim prod name
			$max_text = 18; //44
			$max_number_1 = 9;
			$max_number_2 = 13;
			$max_number_3 = 14;

			if($printer_pin_reservationReceipt == 32){
				$max_text -= 7;
				$max_number_1 = 7;
				$max_number_2 = 9;
				$max_number_3 = 14;
			}
			if($printer_pin_reservationReceipt == 40){
				$max_text -= 4;
				$max_number_1 = 7;
				$max_number_2 = 11;
				$max_number_3 = 14;
			}
			if($printer_pin_reservationReceipt == 42){
				$max_text -= 3;
				$max_number_1 = 9;
				$max_number_2 = 13;
				$max_number_3 = 14;
			}
			if($printer_pin_reservationReceipt == 46){
				$max_text += 2;
				$max_number_1 = 9;
				$max_number_2 = 13;
				$max_number_3 = 14;
			}
			if($printer_pin_reservationReceipt == 48){
				$max_text += 4;
				$max_number_1 = 9;
				$max_number_2 = 13;
				$max_number_3 = 14;
			}

			if(!empty($reservation_detail)){
				
				foreach($reservation_detail as $det){
					
					$discount_total += $det['resd_potongan'];
					$total = $det['resd_total'];
					$total += $det['resd_tax'];
					$total += $det['resd_service'];
					$total -= $det['resd_potongan'];
					
					$tax_total += $det['resd_tax'];
					$service_total += $det['resd_service'];
					$grand_total += $total;
					
					
					
					$all_text_array = array();
								
					$product_name = $det['product_name'];
					
					if(!empty($det['varian_name'])){
						$product_name .= ' - '.$det['varian_name'];
					}
					
					if(strlen($product_name) > $max_text AND $no_limit_text == false){
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
					
					$order_total = $det['resd_price']*$det['resd_qty'];
					$subtotal += $order_total;
					
					//'@'.priceFormat($det['resd_price'])
					$product_price_show = printer_command_align_right(priceFormat($det['resd_price']), $max_number_1);
					$order_total_show = printer_command_align_right(priceFormat($order_total), $max_number_2);
					
					if(in_array($printer_pin_reservationReceipt, array(32,40)) AND $no_limit_text == false){
						//'@'.priceFormat($bil_det->product_price)
						$product_price_show = printer_command_align_right($det['resd_price'], $max_number_1);
						$order_total_show = printer_command_align_right($order_total, $max_number_2);
					}
					
					$order_data .= "[align=0]".$det['resd_qty']."[tab]".$product_name."[tab]".$product_price_show."[tab]".$order_total_show;
					
					//other text - continue 
					foreach($all_text_array as $no_dt => $product_name_extend){
					
						if($no_dt > 0){
							$order_data .= "\n"; 
							$order_data .= "[align=0][tab]".$product_name_extend."[tab] [tab]";
						}
						
					}
							
					if($no < count($reservation_detail)){
						$order_data .= "\n";
					}				
									
					$no++;
				}

			}

			if($reservation_data['reservation_dp']){
				$total_dp = $reservation_data['reservation_dp'];					
				$grand_total -= $reservation_data['reservation_dp'];					
			}

			$subtotal_show = printer_command_align_right(priceFormat($subtotal), $max_number_3);
			$tax_total_show = printer_command_align_right(priceFormat($tax_total), $max_number_3);
			$service_total_show = printer_command_align_right(priceFormat($service_total), $max_number_3);
			$grand_total_show = printer_command_align_right(priceFormat($grand_total), $max_number_3);

			//PENGURANG-------------
			$discount_total_show = 0;
			if($discount_total > 0){
				$discount_total_show = '('.priceFormat($discount_total).')';
			}
			$discount_total_show = printer_command_align_right($discount_total_show, $max_number_3);

			$total_dp_show = 0;
			if($total_dp > 0){
				$total_dp_show = '('.priceFormat($total_dp).')';
				//$total_dp_show = "\n[tab]DP[tab]".$total_dp_show;
			}
			$total_dp_show = printer_command_align_right($total_dp_show, $max_number_3);

			$payment_type_show = '';
			if($reservation_data['reservation_payment'] == 'debit' OR $reservation_data['reservation_payment'] == 'credit'){
				$payment_type_show = strtoupper($reservation_data['reservation_payment'].' CARD');
			}else
			if($reservation_data['reservation_payment'] == 'credit_ar'){
				$payment_type_show = 'CREDIT AR';
			}else{
				$payment_type_show = strtoupper($reservation_data['reservation_payment']);
			}	

			$no_rek = '';
			if(!empty($reservation_data['bank_name'])){
				$no_rek = strtoupper($reservation_data['reservation_payment'].' CARD: ').$reservation_data['bank_name'];
			}
			if(!empty($reservation_data['card_no'])){
				$no_rek .= ' / '.$reservation_data['card_no'];
			}

			$print_attr = array(
				"{date}"	=> date("d/m/Y", strtotime($reservation_data['reservation_date'])),
				"{user}"	=> $session_user,
				"{reservation_no}"	=> $reservation_data['reservation_number'],
				"{order_data}"	=> $order_data,
				"{subtotal}"	=> $subtotal_show,
				"{tax_total}" => $tax_total_show,
				"{service_total}" => $service_total_show,
				"{potongan}"	=> $discount_total_show,
				"{grand_total}"	=> $grand_total_show,
				"{payment_type}"=> $payment_type_show,
				"{dp_total}"=> $total_dp_show,
				"{guest}"=> $reservation_data['total_guest'].' pax'
			);


			if($discount_total == 0){
				$reservationReceipt_layout = empty_value_printer_text($reservationReceipt_layout, '{potongan}');
			}

			if($total_dp == 0){
				$reservationReceipt_layout = empty_value_printer_text($reservationReceipt_layout, '{dp_total}');
			}

			$reservationReceipt_layout = str_replace("{hide_empty}","", $reservationReceipt_layout);


			$print_content_reservationReceipt = strtr($reservationReceipt_layout, $print_attr);



			$print_content = replace_to_printer_command($print_content_reservationReceipt, $printer_type_reservation, $printer_pin_reservationReceipt);



			if($do == 'print' AND $data_printer['print_method'] == 'ESC/POS'){
				//header('Content-Type: text/plain; charset=utf-8');
				
				try {
					$ph = printer_open($printer_ip_reservationReceipt);
				} catch (Exception $e) {
					$ph = false;
				}
				
				//$ph = @printer_open($printer_ip_reservationReceipt);
				
				if($ph)
				{	
					printer_start_doc($ph, "RESERVATION RECEIPT");
					printer_start_page($ph);
					printer_set_option($ph, PRINTER_MODE, "RAW");
					printer_write($ph, $print_content);
					printer_end_page($ph);
					printer_end_doc($ph);
					printer_close($ph);
					$r['success'] = true;
					
				}else{
					$is_print_error = true;
					echo 'Print Error';
					die();
				}
				
				//die();
			}


			printing_process($data_printer, $print_content_reservationReceipt, $do);
			die();
		}
		
		$this->load->view('../../reservation/views/'.$print_layout, $data_post);
		
	}
}