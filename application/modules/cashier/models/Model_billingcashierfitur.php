<?php
class Model_BillingCashierFitur extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'billing_detail';
		$this->table_product_gramasi = $this->prefix.'product_gramasi';
		$this->table_product_package = $this->prefix.'product_package';
		$this->table_product = $this->prefix.'product';	
		$this->table_items = $this->prefix.'items';	
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
			$r = array('success' => false, 'info' => 'Billing Id Tidak Boleh Kosong!');
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
				$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal tersebut sudah ditutup!'); 
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
			$r = array('success' => false, 'info' => 'Billing Id Tidak Boleh Kosong!');
		}else{
					
			//UPDATE BILLING
			$this->db->update($this->table, $update_data, "id = '".$billing_id."'");
			
			$r = array('success' => true, 'info' => 'Info Billing sudah disimpan!', 'retData' => $update_data);
			
		}
		
		die(json_encode($r));
	}
	
	/*public function updateTable($data_create = array()){
		
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
		$this->table_inv = $this->prefix.'table_inventory';		
		$this->table_master = $this->prefix.'table';		
		$billing_id = $this->input->post('billing_id', true);
		$table_id = $this->input->post('table_id', true);
		$is_block_table = $this->input->post('is_block_table', true);
		$is_all_takeaway = $this->input->post('is_all_takeaway', true);
		$is_delete = $this->input->post('is_delete', true);
		$set_default = $this->input->post('set_default', true);
		
		if(empty($is_block_table)){
			$is_block_table = 0;
		}
		if(empty($is_all_takeaway)){
			$is_all_takeaway = 0;
		}
		
		//update-2001.002
		if(!empty($data_create)){
			$billing_id = $data_create['billing_id'];
			$table_id = $data_create['table_id'];
			$is_all_takeaway = $data_create['is_all_takeaway'];
		}
		
		$r = array('success' => false);
		
		if(empty($billing_id) OR empty($table_id)){
			$r = array('success' => false, 'info' => 'Please Pilih Table/Meja!');
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
				$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal tersebut sudah ditutup!'); 
				die(json_encode($r));
			}
			
			//INV
			$date_today = date("Y-m-d");
			$date_time_today = date("Y-m-d H:i:s");
			
			//update-2001.002
			$get_opt_var = array('jam_operasional_from','jam_operasional_to','jam_operasional_extra');
			$get_opt = get_option_value($get_opt_var);
			
			$billing_time = date('G');
			$datenowstr = strtotime(date("d-m-Y H:i:s"));
			$datenowstr0 = strtotime(date("d-m-Y 00:00:00"));
			
			$jam_operasional_from = 7;
			$jam_operasional_from_Hi = '07:00';
			if(!empty($get_opt['jam_operasional_from'])){
				$jm_opr_mktime = strtotime(date("d-m-Y")." ".$get_opt['jam_operasional_from']);
				$jam_operasional_from = date('G',$jm_opr_mktime);
				$jam_operasional_from_Hi = date('H:i',$jm_opr_mktime);
			}
			
			$jam_operasional_to = 23;
			$jam_operasional_to_Hi = '23:00';
			if(!empty($get_opt['jam_operasional_to'])){
				if($get_opt['jam_operasional_to'] == '24:00'){
					$get_opt['jam_operasional_to'] = '23:59:59';
				}
				$jm_opr_mktime = strtotime(date("d-m-Y")." ".$get_opt['jam_operasional_to']);
				$jam_operasional_to = date('G',$jm_opr_mktime);
				$jam_operasional_to_Hi = date('H:i',$jm_opr_mktime);
			}
			
			$jam_operasional_extra = 0;
			if(!empty($get_opt['jam_operasional_extra'])){
				$jam_operasional_extra = $get_opt['jam_operasional_extra'];
			}
			
			if($billing_time < $jam_operasional_from){
				//extra / early??
	
				//check extra
				$datenowstrmin1 = $datenowstr0-ONE_DAY_UNIX;
				$datenowstr_oprfrom = strtotime(date("d-m-Y", $datenowstrmin1)." ".$jam_operasional_from_Hi.":00");
				$datenowstr_oprto_org = strtotime(date("d-m-Y", $datenowstrmin1)." ".$jam_operasional_to_Hi.":00");
				$datenowstr_oprto = strtotime(date("d-m-Y", $datenowstrmin1)." ".$jam_operasional_to_Hi.":00");
				//add extra
				if(!empty($jam_operasional_extra)){
					$datenowstr_oprto += ($jam_operasional_extra*3600);
				}
				
				if($datenowstr < $datenowstr_oprto){
					$date_today = date('Y-m-d', $datenowstr_oprfrom);
				}else{
					$date_today = date('Y-m-d', $datenowstr_oprfrom+ONE_DAY_UNIX);
				}
				
			}else{
	
				$datenowstr_oprfrom = strtotime(date("d-m-Y", $datenowstr0)." ".$jam_operasional_from_Hi.":00");
				$datenowstr_oprto_org = strtotime(date("d-m-Y", $datenowstr0)." ".$jam_operasional_to_Hi.":00");
				$datenowstr_oprto = strtotime(date("d-m-Y", $datenowstr0)." ".$jam_operasional_to_Hi.":00");
				//add extra
				if(!empty($jam_operasional_extra)){
					$datenowstr_oprto += ($jam_operasional_extra*3600);
				}
				
				if($datenowstr < $datenowstr_oprto){
					$date_today = date('Y-m-d', $datenowstr_oprfrom);
				}
				
			}
			
			if(!empty($is_delete)){
				$data_table = array(
					'status' => 'available',
					'billing_no' => '',
					'updated' => $date_time_today,
					'updatedby' => $session_user
				);
				$this->db->update($this->table_inv, $data_table, "billing_no = '".$billingData->billing_no."' AND  table_id = '".$table_id."' AND tanggal = '".$date_today."'");
				
				$r = array('success' => true, 'set_default' => 0);
				
			}else{
				
				if(empty($set_default)){
					if(empty($is_block_table)){
						$data_table = array(
							'table_id' => $table_id,
							'block_table' => 0
						);
								
						//UPDATE OPTIONS
						$this->db->update($this->table, $data_table, "id = '".$billing_id."'");
					
					
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
					}else{
						$data_table = array(
							'block_table' => 1
						);
								
						//UPDATE OPTIONS
						$this->db->update($this->table, $data_table, "id = '".$billing_id."'");
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
					
				}else{
					if(!empty($is_block_table)){
						$data_table = array(
							'block_table' => 1,
							//'table_id' => $table_id,
						);
								
						//UPDATE OPTIONS
						$this->db->update($this->table, $data_table, "id = '".$billing_id."'");
						
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
					}
				}	
				
				$r = array('success' => true, 'set_default' => 0);
			}
			
			if(($billingData->table_id == $table_id AND $is_delete == 1) OR empty($billingData->table_id) OR !empty($set_default)){
				//ganti default
				$table_id_default = 0;
				$table_no_default = '';
				
				$this->db->select("a.id,a.table_id, b.table_no");
				$this->db->from($this->table_inv.' as a');
				$this->db->join($this->table_master.' as b',"b.id = a.table_id","LEFT");
				$this->db->where("a.billing_no = '".$billingData->billing_no."' AND a.tanggal = '".$date_today."'");
				
				if(!empty($set_default)){
					$this->db->where("a.table_id = '".$table_id."'");
				}
				
				$this->db->order_by("a.updated","ASC");
				$get_inv = $this->db->get();
				if($get_inv->num_rows() > 0){
					$get_table_id = $get_inv->row();
					$table_id_default = $get_table_id->table_id;
					$table_no_default = $get_table_id->table_no;
					
					$data_table = array(
						'table_id' => $table_id_default,
						'table_no' => $table_no_default,
					);
							
					//UPDATE OPTIONS
					$this->db->update($this->table, $data_table, "id = '".$billing_id."'");
					
				}
				
				$r = array('success' => true, 'set_default' => 1, 'table_id' => $table_id_default, 'table_no' => $table_no_default);
			
			}
			
			if(empty($is_block_table) AND (!empty($billing_id) OR !empty($set_default))){
				$get_opt_var = array('takeaway_no_tax','takeaway_no_service','set_ta_table_ta','as_server_backup');
				$get_opt = get_option_value($get_opt_var);
				
				cek_server_backup($get_opt);
				
				$set_ta_table_ta = 0;
				if(!empty($get_opt['set_ta_table_ta'])){
					$set_ta_table_ta = $get_opt['set_ta_table_ta'];
				}
				
				$takeaway_no_tax = 0;
				if(!empty($get_opt['takeaway_no_tax'])){
					$takeaway_no_tax = $get_opt['takeaway_no_tax'];
				}
				
				$takeaway_no_service = 0;
				if(!empty($get_opt['takeaway_no_service'])){
					$takeaway_no_service = $get_opt['takeaway_no_service'];
				}
				
				if($set_ta_table_ta == 1){
					
					//update 2018-02-25
					if(!empty($is_all_takeaway)){
						$data_takeaway = array(
							'is_takeaway' => 1,
							'takeaway_no_tax' => $takeaway_no_tax,
							'takeaway_no_service' => $takeaway_no_service,
						);
					}else{
						$data_takeaway = array(
							'is_takeaway' => 0,
							'takeaway_no_tax' => 0,
							'takeaway_no_service' => 0,
						);
					}
					
					$this->db->update($this->table_detail, $data_takeaway, "billing_id = '".$billing_id."' AND is_deleted = 0");
					
				}
				
				$r['is_all_takeaway'] = $is_all_takeaway; 
			}
			
			//update-2001.002
			//optimazing table inv: $table_id, $$billingData->table_id
			$this->billing = $this->prefix.'billing';
			$this->floorplan = $this->prefix.'floorplan';
			$this->room = $this->prefix.'room';
			$this->table = $this->prefix.'table';
			$this->table_inventory = $this->prefix.'table_inventory';	
			
			// Default Parameter
			$params = array(
				'fields'		=> "a.id, a.id as invid, a.table_id, a.billing_no, a.tanggal, a.status, a.total_billing, b.*, 
									c.floorplan_name, c.list_no, c2.room_name, c2.room_no, 
									d.id as billing_id, d.billing_status, d.total_guest, d.table_id as billing_table",
				'primary_key'	=> 'a.id',
				'table'			=> $this->table_inventory.' as a',
				'join'			=> array(
										'many', 
										array( 
											array($this->table.' as b','b.id = a.table_id','LEFT'),
											array($this->floorplan.' as c','c.id = b.floorplan_id','LEFT'),
											array($this->room.' as c2','c2.id = b.room_id','LEFT'),
											array($this->billing.' as d','d.billing_no = a.billing_no','LEFT')
										)
									),
				'where'			=> array('b.is_deleted' => 0),
				'order'			=> array('c.list_no' => 'ASC', 'b.id' => 'ASC', 'b.table_no' => 'ASC'),
				'single'		=> false,
				'output'		=> 'array' //array, object, json
			);
			
			$params['where'][] = "a.tanggal = '".$date_today."'";
			
			//get data -> data, totalCount
			$get_data = $this->find_all($params);
			
			$tanggalexp = explode("-", $date_today);
			$tanggalmk = strtotime($tanggalexp[2].'-'.$tanggalexp[1].'-'.$tanggalexp[0]);
			
			//update-2001.002
			//check hold billing
			$data_billing = array();
			//$tanggalmk = strtotime($tanggal);
			$billno = date("ymd", $tanggalmk);
			$this->db->select('*');
			$this->db->from($this->billing);
			$this->db->where("billing_no LIKE '".$billno."%' AND billing_status = 'hold' AND is_deleted = 0 AND table_id > 0");
			$get_bill = $this->db->get();
			if($get_bill->num_rows() > 0){
				foreach($get_bill->result() as $dt){
					if(empty($data_billing[$dt->table_id])){
						$data_billing[$dt->table_id] = array();
					}
					
					$data_billing[$dt->table_id][] = array(
						'billing_id'	=> $dt->id,
						'billing_no'	=> $dt->billing_no,
						'table_no'		=> $dt->table_no
					);
				}
			}
			
			$update_table_booked_paid = array();
			$update_table_hold = array();
			if(!empty($get_data['data'])){
				foreach ($get_data['data'] as $s){
					
					if(!empty($data_billing[$s['table_id']])){
						$get_billno = '';
						if(!empty($data_billing[$s['table_id']][0]['billing_no'])){
							$get_billno = $data_billing[$s['table_id']][0]['billing_no'];
						}
						$update_table_hold[] = array(
							'id'			=> $s['invid'],
							'billing_no'	=> $get_billno,
							'total_billing'	=> count($data_billing[$s['table_id']]),
							'status'		=> 'booked',
						);
					}else{
						//if booked and paid -> table should available
						if($s['status'] == 'booked' AND !empty($s['billing_id']) AND $s['billing_status'] != 'hold'){
							$update_table_booked_paid[] = array(
								'id'		=> $s['invid'],
								'status'	=> 'available',
								'billing_no'=> ''
							);
							$s['status'] = 'available';
							$s['billing_id'] = '';
							$s['billing_no'] = '';
							$s['billing_status'] = '';
						}
						
						if($s['status'] == 'booked' AND $s['billing_table'] != $s['table_id']){
							$update_table_booked_paid[] = array(
								'id'		=> $s['invid'],
								'status'	=> 'available',
								'billing_no'=> ''
							);
							$s['status'] = 'available';
							$s['billing_id'] = '';
							$s['billing_no'] = '';
							$s['billing_status'] = '';
						}
						
						//if booked and paid -> table should available
						if($s['status'] == 'booked' AND empty($s['billing_id']) AND empty($s['billing_status'])){
							$update_table_booked_paid[] = array(
								'id'		=> $s['invid'],
								'status'	=> 'available',
								'billing_no'=> ''
							);
							$s['status'] = 'available';
							$s['billing_id'] = '';
							$s['billing_no'] = '';
							$s['billing_status'] = '';
						}
					}
				}
				
				//update-2001.002
				if(!empty($update_table_hold)){
					$this->db->update_batch($this->table_inventory, $update_table_hold, "id");
				}
				if(!empty($update_table_booked_paid)){
					$this->db->update_batch($this->table_inventory, $update_table_booked_paid, "id");
				}
			}
			
		}
		
		//update-2001.002
		if(!empty($data_create)){
			return $r;
		}
		
		die(json_encode($r));
	}*/
		
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
			$r = array('success' => false, 'info' => 'Total Guest/Tamu Tidak Boleh Kosong!');
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
				$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal tersebut sudah ditutup!'); 
				die(json_encode($r));
			}
					
			//UPDATE OPTIONS
			$this->db->update($this->table, $data_total_guest, "id = '".$billing_id."'");
			
			$r = array('success' => true );
			
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
			
			$r['billingData'] = $getBilling;
		}
		
		die(json_encode($r));
	}
		
	public function updateBillInfo(){
		
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
		$qc_notes = $this->input->post('qc_notes', true);
		
		$r = array('success' => false);
		
		if(empty($billing_id) OR empty($total_guest)){
			$r = array('success' => false, 'info' => 'Total Guest/Tamu Tidak Boleh Kosong!');
		}else{
			
			$billingData = array();
			$this->db->where("id", $billing_id);
			$getBilling = $this->db->get($this->table);
			if($getBilling->num_rows() > 0){
				$billingData = $getBilling->row();
			}
			
			$data_bill_info = array(
				'total_guest' 	=> $total_guest,
				'qc_notes' 		=> $qc_notes,
				'billing_notes' => $qc_notes,
			);
			
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
					
			//UPDATE OPTIONS
			$this->db->update($this->table, $data_bill_info, "id = '".$billing_id."'");
			
			$r = array('success' => true );
			
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
			$r = array('success' => false, 'info' => 'Billing tidak ditemukan!');
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
				$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal tersebut sudah ditutup!'); 
				die(json_encode($r));
			}
			
			
			$get_opt_var = array('diskon_sebelum_pajak_service','as_server_backup');
			$get_opt = get_option_value($get_opt_var);
			
			cek_server_backup($get_opt);
			
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
					//$tax_percentage = $dt->tax_percentage;
					$service_percentage = $dt->service_percentage;
					$discount_total = $dt->discount_total;
					//$include_tax = $billingData->include_tax;
					//$include_service = $billingData->include_service;
					$tax_percentage = $billingData->tax_percentage;
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
			$r = array('success' => false, 'info' => 'Billing tidak ditemukan!');
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
				$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal tersebut sudah ditutup!'); 
				die(json_encode($r));
			}
			
			
			$get_opt_var = array('diskon_sebelum_pajak_service','as_server_backup');
			$get_opt = get_option_value($get_opt_var);
			
			cek_server_backup($get_opt);
			
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
					//$service_percentage = $dt->service_percentage;
					$discount_total = $dt->discount_total;
					//$include_tax = $billingData->include_tax;
					//$include_service = $billingData->include_service;
					//$tax_percentage = $billingData->tax_percentage;
					$service_percentage = $billingData->service_percentage;
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
			$r = array('success' => false, 'info' => 'Billing tidak ditemukan!');
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
				$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal tersebut sudah ditutup!'); 
				die(json_encode($r));
			}
			
			$data_service = array(
				'total_dp' => $total_dp
			);
			
					
			//UPDATE OPTIONS
			$this->db->update($this->table, $data_service, "id = '".$billing_id."'");
			$r = array('success' => true );
			
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
		
		$get_opt_var = array('diskon_sebelum_pajak_service','as_server_backup');
		$get_opt = get_option_value($get_opt_var);
		
		cek_server_backup($get_opt);
		
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
		$discount_total_post = $this->input->post('discount_total', true);
		$discount_perbilling = $this->input->post('discount_perbilling', true);
		$voucher_no = $this->input->post('voucher_no', true);
		$detail_id = $this->input->post('detail_id', true);
		$is_sistem_tawar = $this->input->post('is_sistem_tawar', true);
		$clearFirst = $this->input->post('clearFirst', true);
		
		//check billing
		$billingData = array();
		if(!empty($billing_id)){
			$this->db->select("total_billing, created, include_tax, include_service, 
						tax_percentage, service_percentage, tax_total, service_total,
						takeaway_no_tax, takeaway_no_service, is_compliment, billing_no, diskon_sebelum_pajak_service");
			$this->db->from($this->table);
			$this->db->where("id", $billing_id);
			$get_billing = $this->db->get();
			if($get_billing->num_rows() > 0){
				$billingData = $get_billing->row();
				$diskon_sebelum_pajak_service = $billingData->diskon_sebelum_pajak_service;
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
			$r = array('success' => false, 'info' => 'Billing tidak ditemukan!');
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
								//$allow_discount = false;
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
									}else{
										$r = array('success' => false, 'info' => 'Diskon Hanya bisa digunakan saat Weekday');
										die(json_encode($r));
									}
								}else
								if($data_diskon->discount_allow_day == 9){
									//weekend
									if($today_in_no >= 6 AND $today_in_no <= 7){
										$allow_discount = true;
									}else{
										$r = array('success' => false, 'info' => 'Diskon Hanya bisa digunakan saat Weekend');
										die(json_encode($r));
									}
								}
							}		
		
							$use_disc_product = 0;
							if(!empty($allow_diskon_product)){
								if(in_array($dt->product_id, $allow_diskon_product)){
									$use_disc_product = 1;
									$allow_discount = true;
								}else{
									$allow_discount = false;
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
									}else{
										$r = array('success' => false, 'info' => 'Waktu Penggunaan Diskon tidak sesuai!<br/>Diskon Berlaku Jam: '.$data_diskon->discount_time_start.' s/d '.$data_diskon->discount_time_end);
										die(json_encode($r));
									}
									
									//echo "allowed_time=".$allowed_time.", $time_from_mk=".$time_from_mk.", $time_till_mk=".$time_till_mk.", $time_now=".$time_now;
									//die();
									
								}
								
								if($allowed_time){
								
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
										}else{
											$r = array('success' => false, 'info' => 'Waktu Penggunaan Diskon tidak sesuai!<br/>Diskon Berlaku Jam: '.$data_diskon->discount_time_start.' s/d '.$data_diskon->discount_time_end);
											die(json_encode($r));
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
									
										
										if(empty($discount_total_perbilling)){
											$discount_total_perbilling = $discount_total_post;
										}
										if(empty($jumlah_total_diskon_peritem)){
											$jumlah_total_diskon_peritem = 0;
										}
										
										if($diskon_sebelum_pajak_service == 0){
											//AFTER TAX
											if($billingData->include_tax == 1 AND $billingData->include_service == 1){
												$discount_total_perbilling = ($data_diskon->discount_percentage/100) * ($billingData->total_billing+$billingData->tax_total+$billingData->service_total);
											}else{
												if($billingData->include_tax == 1){
													$discount_total_perbilling = ($data_diskon->discount_percentage/100) * ($billingData->total_billing+$billingData->service_total);
												}
												
												if($billingData->include_service == 1){
													$discount_total_perbilling = ($data_diskon->discount_percentage/100) * ($billingData->total_billing+$billingData->tax_total);
												}
											}
											
											$discount_total_perbilling = priceFormat($discount_total_perbilling, 0, ".", "");
										}
										
										if($data_diskon->discount_type == 0 OR $use_disc_product == 1){
											
											if($diskon_sebelum_pajak_service == 1){
												//all
												if(!empty($data_diskon->discount_percentage)){
													$s['discount_notes'] = $data_diskon->discount_name;
													$s['discount_percentage'] = $discount_percentage_item;
													$product_price_discount = priceFormat(($discount_percentage_item / 100) * $product_price_real, 0, ".", "");
													$s['discount_price'] = $product_price_discount;
													$s['discount_total'] = $product_price_discount * $order_qty;
												}else
												if(!empty($data_diskon->discount_price)){
													$s['discount_notes'] = $data_diskon->discount_name;
													$s['discount_percentage'] = 0;
													$s['discount_price'] = $discount_price_item;
													$s['discount_total'] = $discount_price_item * $order_qty;
												}
												
											}else{
												
												$get_product_price = $product_price_real;
												//AFTER TAX
												if($billingData->include_tax == 1 OR $billingData->include_service == 1){
													if($billingData->include_tax == 1 AND $billingData->include_service == 1){
														$get_product_price +=  $tax_total;
														$get_product_price +=  $service_total;
													}else{
													
														if($billingData->include_tax == 1){
															$get_product_price +=  $tax_total;
														}
														
														if($billingData->include_service == 1){
															$get_product_price +=  $service_total;
														}
														
													}
												}else{
													$get_product_price +=  $tax_total;
													$get_product_price +=  $service_total;
												}
												
												//all
												if(!empty($data_diskon->discount_percentage)){
													$s['discount_notes'] = $data_diskon->discount_name;
													$s['discount_percentage'] = $discount_percentage_item;
													$product_price_discount = priceFormat(($discount_percentage_item / 100) * $get_product_price, 0, ".", "");
													$s['discount_price'] = $product_price_discount;
													$s['discount_total'] = $product_price_discount * $order_qty;
													
													//update new tax & service
													$get_product_price = $get_product_price-$product_price_discount;
													$tax_total = 0;
													$service_total = 0;
													$product_price_real = 0;
													if(!empty($include_tax) OR !empty($include_service)){
														
														if(!empty($include_tax) AND !empty($include_service)){
															$all_percentage = 100 + $tax_percentage + $service_percentage;
															$one_percent = $get_product_price / $all_percentage;
															//$one_percent_order_qty = $order_qty * $one_percent;
															$tax_total = priceFormat($one_percent * $tax_percentage, 0, ".", "");
															$service_total = priceFormat($one_percent * $service_percentage, 0, ".", "");
															$product_price_real = $get_product_price - ($tax_total + $service_total);
														}else{
															if(!empty($include_tax)){
																$all_percentage = 100 + $tax_percentage;
																$one_percent = $get_product_price / $all_percentage;
																//$one_percent_order_qty = $order_qty * $one_percent;
																$tax_total = priceFormat($one_percent * $tax_percentage, 0, ".", "");
																$product_price_real = $get_product_price - ($tax_total);
															}
															
															if(!empty($include_service)){
																$all_percentage = 100 + $service_percentage;
																$one_percent = $get_product_price / $all_percentage;
																//$one_percent_order_qty = $order_qty * $one_percent;
																$service_total = priceFormat($one_percent * $service_percentage, 0, ".", "");
																$product_price_real = $get_product_price - ($service_total);
															}
															
														}
													}else
													{
														$product_price_real = $get_product_price;
														$tax_percent = $tax_percentage/100;
														$service_percent = $service_percentage/100;
														//$product_price_order_qty = $order_qty * $get_product_price;
														$tax_total = priceFormat($get_product_price * $tax_percent, 0, ".", "");
														$service_total = priceFormat($get_product_price * $service_percent, 0, ".", "");
														
													}
													
												}else
												if(!empty($data_diskon->discount_price)){
													$s['discount_notes'] = $data_diskon->discount_name;
													$s['discount_percentage'] = 0;
													$s['discount_price'] = $discount_price_item;
													$s['discount_total'] = $discount_price_item * $order_qty;
												}
												
												if(empty($update_tax_service)){
													$update_tax_service = array();
												}
												
												$update_tax_service[] = array(
													'id'			=> $s['id'],
													'tax_total'		=> $tax_total* $order_qty,
													'service_total'	=> $service_total* $order_qty
												);
												
											}
											
										}else
										if($data_diskon->discount_type == 1){
											
											//PER-BILLING-------------------------------
											if($diskon_sebelum_pajak_service == 1){
												$total_billing_real = $billingData->total_billing - ($billingData->tax_total+$billingData->service_total);
												$persentase_item = ($product_price_real*$order_qty)/$total_billing_real;
												
												if(!empty($data_diskon->discount_percentage)){
													$discount_price_item = $product_price_real*($data_diskon->discount_percentage/100);
													$discount_price_item = priceFormat($discount_price_item, 0, ".", "");
													$s['discount_notes'] = $data_diskon->discount_name;
													$s['discount_percentage'] = $discount_percentage_item;
													$s['discount_price'] = $discount_price_item;
													$s['discount_total'] = $discount_price_item * $order_qty;
												}else{
													$discount_price_item = $persentase_item*$data_diskon->discount_price;
													$discount_price_item = priceFormat($discount_price_item, 0, ".", "");
													
													if($order_qty > 1){
														$discount_price_item = ($discount_price_item/$order_qty);
													}
													
													$s['discount_notes'] = $data_diskon->discount_name;
													$s['discount_percentage'] = 0;
													$s['discount_price'] = $discount_price_item;
													$s['discount_total'] = $discount_price_item * $order_qty;
												}
												
											}else
											if($data_diskon->discount_type == 1 AND $diskon_sebelum_pajak_service == 0){
												
												$persentase_item = ($product_price_real*$order_qty)/$billingData->total_billing;
												
												if(!empty($data_diskon->discount_percentage)){
													$discount_price_item = $persentase_item*$discount_total_perbilling;
													$discount_price_item = priceFormat($discount_price_item, 0, ".", "");
													
													if($order_qty > 1){
														$discount_price_item = ($discount_price_item/$order_qty);
													}
													
													$s['discount_notes'] = $data_diskon->discount_name;
													$s['discount_percentage'] = $discount_percentage_item;
													$s['discount_price'] = $discount_price_item;
													$s['discount_total'] = $discount_price_item * $order_qty;
												}else{
													$discount_price_item = $persentase_item*$data_diskon->discount_price;
													$discount_price_item = priceFormat($discount_price_item, 0, ".", "");
													$s['discount_notes'] = $data_diskon->discount_name;
													$s['discount_percentage'] = 0;
													$s['discount_price'] = $discount_price_item;
													$s['discount_total'] = $discount_price_item * $order_qty;
												}
												
											}
											
											$discount_price_item = priceFormat($discount_price_item, 0, ".", "");
											
											$jumlah_total_diskon_peritem += $discount_price_item;
											
											if(($discount_total_perbilling-$jumlah_total_diskon_peritem) <= 0){
												$selisih = ($discount_total_perbilling-$jumlah_total_diskon_peritem);
												$discount_price_item = $discount_price_item - $selisih;
												$jumlah_total_diskon_peritem = $discount_total_perbilling;
											}
											
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
				
				//if($data_diskon->discount_type == 1 AND !empty($data_diskon->discount_price)){
				if($data_diskon->discount_type == 1){
					
					if(empty($discount_total_perbilling)){
						$discount_total_perbilling = $discount_total_post;
					}
					
					$discount_total = $discount_total_perbilling;
					
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
			
			if(!empty($update_tax_service)){
				$this->db->update_batch($this->table_detail, $update_tax_service, "id");
			}
			//$r = array('success' => false, 'info' => 'update_tax_service = '.count($update_tax_service),'dt' => $update_tax_service);
			//die(json_encode($r));
			
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
			$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal tersebut sudah ditutup!'); 
			die(json_encode($r));
		}
		
		$r = array('success' => false);
		
		if(empty($billing_id)){
			$r = array('success' => false, 'info' => 'Billing tidak ditemukan!');
		}else{
			
			
			$get_opt_var = array('diskon_sebelum_pajak_service','as_server_backup');
			$get_opt = get_option_value($get_opt_var);
			
			cek_server_backup($get_opt);
		
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
				
				//update-2003.001
				$this->db->update($this->table_detail, $update_compliment, "(id IN (".$detail_id.") OR ref_order_id IN (".$detail_id."))");
				
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
									
									//update-2001.002
									$product_price_real = $product_price_real-$discount_total;
									$tax_total = 0;
									$service_total = 0;
								}else{
									$product_price_real = $product_price;
									$tax_total = 0;
									$service_total = 0;
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
										
										//update-2001.002
										$product_price_real = $product_price_real-$discount_total;
										$tax_total = 0;
										$service_total = 0;
									}else{
										$product_price_real = $product_price;
										$tax_total = 0;
										$service_total = 0;
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
										
										//update-2001.002
										$product_price_real = $product_price_real-$discount_total;
										$tax_total = 0;
										$service_total = 0;
									}else{
										$product_price_real = $product_price;
										$tax_total = 0;
										$service_total = 0;
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
							
							//update-2001.002
							$product_price_real = $product_price_real-($tax_total+$service_total);
							
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
							$compliment_total_tax_service += ($product_price_real * $order_qty);
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
			
			$r['billingData'] = $getBilling;
			
		}
		
		die(json_encode($r));
	}
	
	/*SPLIT & MERGE*/
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
			'wepos_tipe',
			'as_server_backup'
		);
		
		$get_opt = get_option_value($opt_value);
		
		cek_server_backup($get_opt);
		
		$wepos_tipe = 'cafe';
		if(!empty($get_opt['wepos_tipe'])){
			$wepos_tipe = $get_opt['wepos_tipe'];
		}
		
		$main_billing_id = $this->input->post('main_billing_id', true);		
		$merge_billing_id = $this->input->post('merge_billing_id', true);		
		
		if(empty($main_billing_id)){
			$r = array('success' => false, 'info' => 'Billing Utama tidak ada/tidak dikenali!');
			echo json_encode($r);
			die();
		}
		
		if(empty($merge_billing_id)){
			$r = array('success' => false, 'info' => 'Semua Merge Billing tidak dikenali!');
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
			$r = array('success' => false, 'info' => 'Cek kembali pesanan sudah selesai semua<br/>Merge Bill hanya digunakan ketika pembayaran billing');
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
			$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal tersebut sudah ditutup!'); 
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
		
		$update_billing = calculateBilling($main_billing_id);
		
		//SET STATUS TABLE
		//if(!empty($all_table_id) AND $wepos_tipe != 'retail'){
		if(!empty($all_table_id)){
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
			$r = array('success' => false, 'info' => 'Billing Utama tidak ada/tidak dikenali!');
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
			$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal tersebut sudah ditutup!'); 
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
					
					$update_billing = calculateBilling($dt->id);
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
			$r = array('success' => false, 'info' => 'Merge Billing tidak dikenali!');
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
			$r = array('success' => false, 'info'	=> "Tidak dapat melakukan Merge Billing<br/>Tidak ada billing yang dikenali!" );
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
			$r = array('success' => false, 'info'	=> "Tidak dapat melakukan Merge Billing<br/>Tidak ada pesanan/order!" );
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
			$r = array('success' => false, 'info'	=> "Tidak dapat melakukan Merge Billing: ".$no_order_txt ."<br/>Tidak ada pesanan/order!" );
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
			$r = array('success' => false, 'info'	=> "Tidak dapat melakukan Merge Billing: ".$no_order_done_txt."<br/>Semua status pesanan harus sudah tercetak!" );
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
			$r = array('success' => false, 'info' => 'Split Billing tidak dikenali!');
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
			$r = array('success' => false, 'info'	=> "Tidak dapat melakukan Split Billing<br/>Tidak ada pesanan/order!" );
			die(json_encode($r));
		}
		
		if(!empty($update_detail)){
			$r = array('success' => false, 'info'	=> "Tidak dapat melakukan Split Billing<br/>Semua status pesanan harus sudah tercetak!" );
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
			$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal tersebut sudah ditutup!'); 
			die(json_encode($r));
		}
		
		$billing_id = $this->input->post('billing_id', true);				
		
		if(empty($billing_id)){
			$r = array('success' => false, 'info' => 'Split Billing tidak dikenali!');
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
			$r = array('success' => false, 'info'	=> "Tidak dapat melakukan Split Billing<br/>Semua status pesanan harus sudah tercetak!" );
			die(json_encode($r));
		}
		
		
		if(!empty($insert_detail)){
			$this->db->insert_batch($this->table_detail_split, $insert_detail);
		}
		
		
		$r = array('success' => true );
		die(json_encode($r));
	}
	
	public function save_manyOrderProduct_split(){
		$this->table = $this->prefix.'billing';				
		$this->table2 = $this->prefix.'billing_detail';				
		$this->table_split = $this->prefix.'billing_detail_split';				
		$session_user = $this->session->userdata('user_username');
		
		if(empty($session_user)){
			$r = array('success' => false, 'info' => 'Sesi Login sudah habis, Silahkan Login ulang!');
			echo json_encode($r);
			die();
		}
		
		$get_id = $this->input->post('order_id');
		$billing_id = $this->input->post('billing_id');
		$is_reset = $this->input->post('is_reset');
		$is_express = $this->input->post('is_express');
		
		if(empty($get_id)){
			$r = array('success' => false, 'info' => 'Order ID not Found!');
			echo json_encode($r);
			die();
		}
		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
		
		//CHECK IF BILLING IS NOT PAID
		$this->db->select("a.*,
		b.id as billing_id, b.billing_no, b.billing_status, b.include_tax, b.include_service, 
		b.tax_percentage, b.service_percentage, b.takeaway_no_tax, b.takeaway_no_service");
		$this->db->from($this->table_split." as a");
		$this->db->join($this->table." as b", "b.id = a.billing_id", "LEFT");
		$this->db->where("a.id IN (".$sql_Id.")");
		$this->db->where("a.is_deleted = 0");
		//$this->db->where("b.billing_status = 'paid'");
		$get_billing = $this->db->get();
		if($get_billing->num_rows() > 0){
			$billingData = $get_billing->row();
			
			if($is_express == 1){
				$qty = $billingData->order_qty;
				$keterangan = 'bypass';
			}else{
				if($billingData->billing_status == 'paid'){
					$r = array('success' => false, 'info' => 'Billing: '.$billingData->billing_no.' sudah dibayar!<br/>Tidak bisa cancel order, lakukan void billing atau hold billing'); 
					echo json_encode($r);
					die();
				}
			}
			
			if($billingData->package_item == 1 AND $billingData->free_item == 1 AND !empty($billingData->ref_order_id)){
				$r = array('success' => false, 'info' => 'Menu/Product termasuk dalam Paket!<br/>Silahkan Split Menu/Product Utama Paket'); 
				echo json_encode($r);
				die();
			}
			
			if($billingData->package_item == 0 AND $billingData->free_item == 1 AND !empty($billingData->ref_order_id)){
				$r = array('success' => false, 'info' => 'Menu/Product termasuk dalam Promo<br/>Please Split Menu/Product Utama'); 
				echo json_encode($r);
				die();
			}
			
			//$r = array('success' => false, 'info' => 'Billing: '.$billingData->billing_no.' sudah dibayar!<br/>Cannot Cancel Order, Silahkan lakukan Refresh List Billing');
			//die(json_encode($r));
		}
		
		$date_now = date("Y-m-d");
		
		$r = array('success' => false, 'info' => 'Split Order Gagal!', 'qtySplit' => 0, 'priceSplit' => 0); 
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
			
			$data_update = array();
			$data_update_package = array();
			$all_id_package = array();
			if($get_billing->num_rows() > 0){
				foreach($get_billing->result() as $dt){
					$order_qty_split = $dt->order_qty;
					if($is_reset == 1){
						$order_qty_split = 0;
					}
					
					$data_update[] = array(
						'id'				=> $dt->id,
						'order_qty_split'	=> $order_qty_split,
						'updated'			=> $date_now,
						'updatedby'			=> $session_user
					);
					
					if($dt->product_type == 'package' OR $dt->is_buyget == 1){
						if(!in_array($dt->billing_detail_id, $all_id_package)){
							$all_id_package[] = $dt->billing_detail_id;
							
							$data_update_package[] = array(
								'ref_order_id'		=> $dt->billing_detail_id,
								'order_qty_split'	=> $order_qty_split,
								'updated'			=> $date_now,
								'updatedby'			=> $session_user
							);
							
						}
					}
					
				}
			}
			
			if(!empty($data_update)){
				$this->db->update_batch($this->table_split, $data_update, "id");
			}
			
			if(!empty($data_update_package)){
				$this->db->update_batch($this->table_split, $data_update_package, "ref_order_id");
			}
			
			$qtySplit = 0;
			$priceSplit = 0;
			$taxSplit = 0;
			$serviceSplit = 0;
			$discountSplit = 0;
			//load table split
			$this->db->select("a.*");
			$this->db->from($this->table_split." as a");
			$this->db->where("a.billing_id = ".$billing_id);
			$this->db->where("a.ref_order_id = 0");
			$this->db->where("a.is_deleted = 0");
			$get_det_split = $this->db->get();
			if($get_det_split->num_rows() > 0){
				foreach($get_det_split->result() as $dt){
					$qtySplit += $dt->order_qty_split;
					
					//$get_product_price = $dt->product_price + $dt->tax_total + $dt->service_total - $dt->discount_total;
					$get_product_price = $dt->product_price;
					if($dt->is_compliment == 1){
						$get_product_price = 0;
					}
					
					$get_order_total = $dt->order_qty_split * $get_product_price;
					$priceSplit += $get_order_total;
					
					//$taxSplit += $dt->tax_total;
					//$serviceSplit += $dt->service_total;
					//$discountSplit += $dt->discount_total;
					
				}
			}	

			$priceSplit += $taxSplit;
			$priceSplit += $serviceSplit;
			$priceSplit += $discountSplit;
			
			$r = array('success' => true, 'qtySplit' => priceFormat($qtySplit), 'priceSplit' => priceFormat($priceSplit));
		}
		die(json_encode($r));
	}
	
	public function save_orderProduct_split(){
		$this->table = $this->prefix.'billing';				
		$this->table_split = $this->prefix.'billing_detail_split';				
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
			$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal tersebut sudah ditutup!'); 
			die(json_encode($r));
		}
			
		$var = array('fields'	=>	array(
				'order_qty_split'=>	$order_qty_split,
				'updated'		=>	$date_now,
				'updatedby'		=>	$session_user
			),
			'table'			=>  $this->table_split,
			'primary_key'	=>  'id'
		);
		
		//UPDATE
		$this->lib_trans->begin();
			$update = $this->save($var, $id);
		$this->lib_trans->commit();
		
		if($update)
		{  
			$r = array('success' => true, 'id' => $id);
			
			//update-2003.001
			$this->db->select("a.*");
			$this->db->from($this->table_split." as a");
			$this->db->where("a.id = ".$id." AND (a.product_type = 'package' OR is_buyget = 1)");
			$get_dt_split = $this->db->get();
			if($get_dt_split->num_rows() > 0){
				$dt_split = $get_dt_split->row();
				
				$data_update_package = array(
					'order_qty_split'	=> $order_qty_split,
					'updated'			=> $date_now,
					'updatedby'			=> $session_user
				);
				
				if(!empty($data_update_package)){
					$this->db->update($this->table_split, $data_update_package, "ref_order_id = ".$dt_split->billing_detail_id);
				}
			}
			
			$qtySplit = 0;
			$priceSplit = 0;
			$taxSplit = 0;
			$serviceSplit = 0;
			$discountSplit = 0;
			//load table split
			$this->db->select("a.*");
			$this->db->from($this->table_split." as a");
			$this->db->where("a.billing_id = ".$billing_id);
			$this->db->where("a.ref_order_id = 0");
			$this->db->where("a.is_deleted = 0");
			$get_det_split = $this->db->get();
			if($get_det_split->num_rows() > 0){
				foreach($get_det_split->result() as $dt){
					$qtySplit += $dt->order_qty_split;
					
					//$get_product_price = $dt->product_price + $dt->tax_total + $dt->service_total - $dt->discount_total;
					$get_product_price = $dt->product_price;
					if($dt->is_compliment == 1){
						$get_product_price = 0;
					}
					
					$get_order_total = $dt->order_qty_split * $get_product_price;
					$priceSplit += $get_order_total;
					
					//$taxSplit += $dt->tax_total;
					//$serviceSplit += $dt->service_total;
					//$discountSplit += $dt->discount_total;
				}
			}		
			
			$priceSplit += $taxSplit;
			$priceSplit += $serviceSplit;
			$priceSplit += $discountSplit;
			
			$r = array('success' => true, 'id' => $id, 'qtySplit' => priceFormat($qtySplit), 'priceSplit' => priceFormat($priceSplit));
		}  
		else
		{  
			$r = array('success' => false);
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
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
			$r = array('success' => false, 'info' => 'Billing ID tidak ditemukan!');
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
							
							//update-2003.001
							if($dt->product_type == 'package'){
								$dt->ref_order_id = $dt->billing_detail_id;
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
			$r = array('success' => false, 'info'	=> "Tidak dapat melakukan Split Billing<br/>Total pesanan tidak boleh kosong" );
			die(json_encode($r));
		}
		
		if(!empty($status_order)){
			$r = array('success' => false, 'info'	=> "Tidak dapat melakukan Split Billing<br/>Semua status pesanan harus sudah tercetak!" );
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
			$r = array('success' => false, 'info' => 'Transaksi Penjualan pada tanggal tersebut sudah ditutup!'); 
			die(json_encode($r));
		}
			
		$billingData_old = getBilling($billing_id);
		$billingData = getBilling();
		
		
		if($billingData == false OR empty($billingData->billing_id)){
			$r = array('success' => false, 'info' => 'Membuat Billing Baru, Gagal!');
			echo json_encode($r);
			die();
		}else{
			
			//set to hold
			$data_update_billing = array(
				'billing_status'	=> $billingData_old->billing_status,
				'table_id'			=> $billingData_old->table_id,
				'split_from_id'		=> $billing_id
			);
			$this->db->update($this->prefix.'billing', $data_update_billing, "id = '".$billingData->billing_id."'");
			
			//data_new_billing
			if(!empty($data_new_billing)){
				
				$insert_new = array();
				foreach($data_new_billing as $dt){
					$dt['billing_id'] = $billingData->billing_id;
					$insert_new[] = $dt;
				}
				
				$this->db->insert_batch($this->table_detail, $insert_new);
				
				//update package
				$new_package_id = array();
				$new_package_data = array();
				$this->db->select("*");
				$this->db->from($this->table_detail);
				$this->db->where("billing_id = '".$billingData->billing_id."'");
				$get_detail_package = $this->db->get();
				if($get_detail_package->num_rows() > 0){
					foreach($get_detail_package->result() as $dt){
						if(!empty($dt->ref_order_id)){
							if($dt->package_item == 1 OR $dt->free_item == 1){
								$new_package_data[] = array(
									'id' => $dt->id,
									'ref_order_id' => $dt->ref_order_id
								);
							}else{
								$new_package_data[] = array(
									'id' => $dt->id,
									'ref_order_id' => 0
								);
								$new_package_id[$dt->ref_order_id] = $dt->id;
							}
						}
					}
					
					if(!empty($new_package_data)){
						$new_package_data2 = array();
						foreach($new_package_data as $dt){
							//check related ref id
							if(!empty($dt['ref_order_id'])){
								if(!empty($new_package_id[$dt['ref_order_id']])){
									$dt['ref_order_id'] = $new_package_id[$dt['ref_order_id']];
								}
							}	
							
							$new_package_data2[] = $dt;
						}
						$new_package_data = $new_package_data2;
					}
				}
				
				if($new_package_data){
					$this->db->update_batch($this->table_detail, $new_package_data, "id");
				}
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
			$update_billing = calculateBilling($billingData->billing_id);
				
		}
		
		//data_old_billing
		if(!empty($data_old_billing)){
			$this->db->update_batch($this->table_detail, $data_old_billing, "id");
			
			$update_billing_old = calculateBilling($billingData_old->billing_id);
		}
		
		//remove all on split
		$this->db->delete($this->table_detail_split, "billing_id = '".$billing_id."'");
		
		$r = array('success' => true);
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	//update-2011.001
	public function override_nontrx($billing_id = '', $tax_total = 0, $grand_total = 0, $is_void = false, $get_opt = array()){
		
		$this->table = $this->prefix.'billing';	
		
		if(empty($get_opt)){
			$get_opt_var = array('tandai_pajak_billing','nontrx_sales_auto','nontrx_override_on','nontrx_allow_zero','current_date');
			$get_opt = get_option_value($get_opt_var);
			
		}
		
		$force_txmark = false;
		if(!empty($get_opt['nontrx_override_on'])){
			$force_txmark = true;
		}
		
		if(empty($billing_id)){
			return false;
		}
		
		if(!empty($get_opt['nontrx_allow_zero']) OR $force_txmark == true){
			//skip tax_total = 0
		}else{
			
			if(empty($tax_total)){
				return false;
			}
			
			$this->table_billing_detail = $this->prefix.'billing_detail';	
			$this->db->select("*");
			$this->db->from($this->table_billing_detail);
			//$this->db->where("billing_id = ".$billing_id." AND order_status = 'done' AND tax_total = 0");
			$this->db->where("billing_id = ".$billing_id." AND (tax_total = 0 OR is_takeaway = 1 OR is_compliment = 1)");
			$get_billing_detail_null = $this->db->get();
			if($get_billing_detail_null->num_rows() > 0){
				
				//curr no -> reorder no
				$update_billing_data = array();
				$reorder_date = array();
				$mk_from_date = 0;
				$mk_till_date = 0;
				
				$this->db->select('*');
				$this->db->from($this->table);
				$this->db->where("id IN (".$billing_id.")");
				$get_billing = $this->db->get();
				if($get_billing->num_rows() > 0){
					
					foreach($get_billing->result() as $dt){
						
						$billing_no = substr($dt->billing_no, 0, 6);
						
						if(!in_array($billing_no, $reorder_date)){
							$reorder_date[] = $billing_no;
						}
						
					}
				}
				
				$update_billing_data = array(
					'txmark'	=> 0,
					'txmark_no'	=> ''
				);
				$this->db->update($this->table, $update_billing_data, "id = ".$billing_id);
					
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
				
				return false;
			}
			
		}
		
		$nontrx_target_default = array();
		$nontrx_target_current = array();
		
		//update-2011.001
		if(empty($get_opt['current_date'])){
			$get_opt_var = array('current_date');
			$get_opt = get_option_value($get_opt_var);
		}
		
		$current_date = $get_opt['current_date'];
		
		//default & today
		$tahun = date("Y", $current_date);
		$bulan = date("n", $current_date);
		$no_hari = date("N", $current_date);
		$minggu_ke = date("W", $current_date);
		$this->db->select('*');
		$this->db->from($this->prefix.'nontrx_target');
		$this->db->where("is_default = 1 OR (nontrx_tahun = '".$tahun."' AND nontrx_bulan = '".$bulan."')");
		$this->db->order_by("is_default","DESC");
		$get_nontrx_target = $this->db->get();
		if($get_nontrx_target->num_rows() > 0){
			foreach($get_nontrx_target->result_array() as $dt){
				if($dt['is_default'] == 1){
					$dt['nontrx_tahun'] = date("Y");
					$dt['nontrx_bulan'] = date("n");
					$nontrx_target_default = $dt;
				}else{
					$xid = $dt['nontrx_tahun'].'-'.$dt['nontrx_bulan'];
					$nontrx_target_current[$xid] = $dt;
				}
			}
		}
		
		if(empty($nontrx_target_current[$tahun.'-'.$bulan])){
			$nontrx_target_current[$tahun.'-'.$bulan] = $nontrx_target_default;
			unset($nontrx_target_current[$tahun.'-'.$bulan]['id']);
			unset($nontrx_target_current[$tahun.'-'.$bulan]['is_default']);
			$this->db->insert($this->prefix.'nontrx_target', $nontrx_target_current[$tahun.'-'.$bulan]);
		}
		
		
		$this->db->select('*');
		$this->db->from($this->prefix.'nontrx_log');
		$this->db->where("nontrx_tanggal = '".date("Y-m-d", $current_date)."'");
		$get_nontrx_log = $this->db->get();
		if($get_nontrx_log->num_rows() == 0){
			$log_nontrx = array(
				'nontrx_tanggal'	=> date("Y-m-d", $current_date),
				'nontrx_tahun'		=> $tahun,
				'nontrx_bulan'		=> $bulan,
				'nontrx_minggu'		=> $minggu_ke,
				'nontrx_hari_realisasi' => 0,
				'nontrx_shift1_realisasi' => 0,
				'nontrx_shift2_realisasi' => 0,
				'nontrx_shift3_realisasi' => 0,
			);
			$this->db->insert($this->prefix.'nontrx_log', $log_nontrx);
		}
		
		if(!empty($nontrx_target_current[$tahun.'-'.$bulan])){
			
			$nontrx_data = $nontrx_target_current[$tahun.'-'.$bulan];
		
			if($nontrx_data['nontrx_curr_minggu'] != $minggu_ke){
				$nontrx_data['nontrx_curr_minggu'] = $minggu_ke;
				$nontrx_data['nontrx_minggu_realisasi'] = 0;
			}
			
			if($nontrx_data['nontrx_curr_tanggal'] != date("Y-m-d", $current_date)){
				$nontrx_data['nontrx_curr_tanggal'] = date("Y-m-d", $current_date);
				$nontrx_data['nontrx_hari_realisasi'] = 0;
			}
			
			$allow_range_sales = false;
			$allow_range_jam = false;
			$allow_bulan_akumulasi = false;
			$allow_minggu_akumulasi = false;
			$allow_hari_akumulasi = false;
			
			if(!empty($nontrx_data['nontrx_range_sales_from']) AND !empty($nontrx_data['nontrx_range_sales_till'])){
				if($grand_total >= $nontrx_data['nontrx_range_sales_from'] AND $grand_total <= $nontrx_data['nontrx_range_sales_till']){
					$allow_range_sales = true;
				}
			}
			
			//range_jam
			if(!empty($nontrx_data['nontrx_range_jam_from']) AND !empty($nontrx_data['nontrx_range_jam_till'])){
				$nontrx_range_jam_from = strtotime(date("d-m-Y", $current_date)." ".$nontrx_data['nontrx_range_jam_from'].":00");
				$nontrx_range_jam_till = strtotime(date("d-m-Y", $current_date)." ".$nontrx_data['nontrx_range_jam_till'].":00");
				$jam_now = date("d-m-Y", $current_date).' '.date("H:i:s");
				$jam_now_mk = strtotime($jam_now);
				if($jam_now_mk >= $nontrx_range_jam_from AND $jam_now_mk <= $nontrx_range_jam_till){
					$allow_range_jam = true;
				}
			}
			
			//bulan
			if(!empty($nontrx_data['nontrx_bulan_target'])){
				if($nontrx_data['nontrx_bulan_realisasi'] < $nontrx_data['nontrx_bulan_target']){
					//$nontax_bulan_akumulasi += $tax_total;
					$allow_bulan_akumulasi = true;
				}
			}
			
			//minggu
			if(!empty($nontrx_data['nontrx_minggu_target'])){
				if($nontrx_data['nontrx_minggu_realisasi'] < $nontrx_data['nontrx_minggu_target']){
					//$nontax_minggu_akumulasi += $tax_total;
					if($allow_bulan_akumulasi == true){
						$allow_minggu_akumulasi = true;
					}
					
				}
			}
			
			//hari
			if(!empty($nontrx_data['nontrx_hari_target'])){
				if($nontrx_data['nontrx_hari_realisasi'] < $nontrx_data['nontrx_hari_target']){
					
					if($allow_minggu_akumulasi == true){
						$allow_hari_akumulasi = true;
					}else{
						if($allow_bulan_akumulasi == true AND empty($nontrx_data['nontrx_minggu_target'])){
							$allow_hari_akumulasi = true;
						}else{
							if(empty($nontrx_data['nontrx_bulan_target'])){
								$allow_hari_akumulasi = true;
							}
						}
					}
					
				}
			}
				
			if((($allow_range_sales == true AND $allow_range_jam == true) AND $is_void == false) OR $force_txmark == true){
				
				if($allow_hari_akumulasi == true){
					$nontrx_data['nontrx_hari_realisasi'] += $tax_total;
					
					if($allow_minggu_akumulasi == true){
						$nontrx_data['nontrx_minggu_realisasi'] += $tax_total;
					}
					
					if($allow_bulan_akumulasi == true){
						$nontrx_data['nontrx_bulan_realisasi'] += $tax_total;
					}
				}else{
					
					if($allow_minggu_akumulasi == true){
						$nontrx_data['nontrx_minggu_realisasi'] += $tax_total;
						
						if($allow_bulan_akumulasi == true){
							$nontrx_data['nontrx_bulan_realisasi'] += $tax_total;
						}
						
					}else{
						if($allow_bulan_akumulasi == true){
							$nontrx_data['nontrx_bulan_realisasi'] += $tax_total;
						}
					}
				}
			}
			
			if($is_void == true){
				if(!empty($nontrx_data['nontrx_bulan_realisasi'])){
					$nontrx_data['nontrx_bulan_realisasi'] -= $tax_total;
				}
				if(!empty($nontrx_data['nontrx_minggu_realisasi'])){
					$nontrx_data['nontrx_minggu_realisasi'] -= $tax_total;
				}
				if(!empty($nontrx_data['nontrx_hari_realisasi'])){
					$nontrx_data['nontrx_hari_realisasi'] -= $tax_total;
				}
				$allow_hari_akumulasi = true;
			}
			
			if(($allow_hari_akumulasi == true AND (($allow_range_sales == true AND $allow_range_jam == true) OR $is_void == true)) OR $force_txmark == true){
				
				$update_nontrx = array(
					'nontrx_hari_realisasi'	=> $nontrx_data['nontrx_hari_realisasi'],
					'nontrx_minggu_realisasi'	=> $nontrx_data['nontrx_minggu_realisasi'],
					'nontrx_bulan_realisasi'	=> $nontrx_data['nontrx_bulan_realisasi'],
					'nontrx_curr_minggu'	=> $nontrx_data['nontrx_curr_minggu'],
					'nontrx_curr_tanggal'	=> $nontrx_data['nontrx_curr_tanggal'],
				);
				$this->db->update($this->prefix.'nontrx_target', $update_nontrx, "is_default = 0 AND nontrx_tahun = '".$tahun."' AND nontrx_bulan = '".$bulan."'");
				
				$log_nontrx = array(
					'nontrx_hari_realisasi' => $nontrx_data['nontrx_hari_realisasi'],
					'nontrx_shift1_realisasi' => 0,
					'nontrx_shift2_realisasi' => 0,
					'nontrx_shift3_realisasi' => 0,
				);
				$this->db->update($this->prefix.'nontrx_log', $log_nontrx, "nontrx_tanggal = '".date("Y-m-d", $current_date)."'");
				
				//set billing == tandai
				$tandai = 1;
				if($is_void == true){
					$tandai = 0;
				}
				
				$this->db->select('*');
				$this->db->from($this->table);
				$this->db->where("id IN (".$billing_id.")");
				$get_billing = $this->db->get();
				if($get_billing->num_rows() > 0){
					
					//curr no -> reorder no
					$update_billing_data = array();
					$reorder_date = array();
					$mk_from_date = 0;
					$mk_till_date = 0;
					
					foreach($get_billing->result() as $dt){
						
						$billing_no = substr($dt->billing_no, 0, 6);
						
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
				
					
				}
				
			}
			
			return true;
		}
		
		return false;
	}
} 