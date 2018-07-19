<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class DataBilling extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_databilling', 'm');
		$this->load->model('model_billingdetail', 'm2');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';		
		$session_user = $this->session->userdata('user_username');	
		$role_id = $this->session->userdata('role_id');	
		
		$opt_value = array(
			'no_midnight',
			'cashier_max_pembulatan',
			'cashier_pembulatan_keatas',
			'payment_id_cash',
			'payment_id_debit',
			'payment_id_credit',
			'wepos_tipe'
		);
		$get_opt = get_option_value($opt_value);
		
		$wepos_tipe = 'cafe';
		if(!empty($get_opt['wepos_tipe'])){
			$wepos_tipe = $get_opt['wepos_tipe'];
		}
		
		$no_midnight = 0;
		if(!empty($get_opt['no_midnight'])){
			$no_midnight = 1;
		}
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'a.is_active',
			'billing_date' => 'a.created'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.id, a.table_id, a.table_no, a.billing_no, a.payment_date,
								a.billing_status, a.billing_notes, a.total_pembulatan, a.total_billing, a.grand_total, a.total_paid, a.payment_id, a.bank_id,
								a.card_no, a.include_tax, a.tax_percentage, a.tax_total, a.include_service, a.service_percentage, a.service_total, 
								a.discount_id, a.discount_notes, a.discount_percentage, a.discount_price, a.discount_total, a.voucher_no, a.total_hpp, 
								a.is_active, a.total_dp, a.compliment_total, a.total_cash, a.total_credit, a.createdby, a.updatedby, 
								a.merge_id, a.merge_main_status, a.split_from_id, a.total_guest, a.lock_billing, a.qc_notes,
								a.created, a.updated, a.takeaway_no_tax, a.takeaway_no_service, a.is_compliment,  
								a.discount_perbilling, a.total_return, a.compliment_total_tax_service, a.is_half_payment,
								a.sales_id, a.sales_percentage, a.sales_price, a.sales_type, a.customer_id, a.block_table,
								a.id as billing_id, b.table_name, b.table_no, b.table_desc, b.floorplan_id, c.floorplan_name, a.is_reservation,
								d.payment_type_name, e.user_firstname, e.user_lastname, f.bank_name, 
								g.billing_no as merge_billing_no, h.sales_name, h.sales_company, i.customer_name',
			'primary_key'	=> 'id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'table as b','b.id = a.table_id','LEFT'),
										array($this->prefix.'floorplan as c','c.id = b.floorplan_id','LEFT'),
										array($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT'),
										array($this->prefix_apps.'users as e','e.user_username = a.updatedby','LEFT'),
										array($this->prefix.'bank as f','f.id = a.bank_id','LEFT'),
										array($this->prefix.'billing as g','g.id = a.merge_id','LEFT'),
										array($this->prefix.'sales as h','h.id = a.sales_id','LEFT'),
										array($this->prefix.'customer as i','i.id = a.customer_id','LEFT')
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
		$billing_status = $this->input->post('billing_status');
		$is_peruser = $this->input->post('is_peruser');
		$report_paid_order = $this->input->post('report_paid_order');
		$use_payment_date = $this->input->post('use_payment_date');
		$sorting_by = $this->input->post('sorting_by');
		
		//FILTER
		$shift_billing = $this->input->post('shift_billing');
		$user_cashier = $this->input->post('user_cashier');
		$skip_date = $this->input->post('skip_date');
		$date_from = $this->input->post('date_from');
		$date_till = $this->input->post('date_till');
		$keywords = $this->input->post('keywords');
		$use_range_date = $this->input->post('use_range_date');
		$by_product_order = $this->input->post('by_product_order');
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if(!empty($is_peruser)){
			if(!in_array($role_id, array(1,2))){
				$params['where'][] = "(a.updatedby = '".$session_user."')";
			}
		}
		if(!empty($user_cashier)){
			//$this->db->where('a.updatedby', $user_cashier);
			$params['where'][] = "(a.updatedby = '".$user_cashier."')";
		}
		
		if(!empty($shift_billing)){
			$skip_date = true;
			
			if(empty($date_from)){
				$date_from = date('Y-m-d');
			}
			
			if(!empty($date_from)){
			
				if(empty($date_from)){ $date_from = date('Y-m-d'); }
				
				$mktime_dari = strtotime($date_from);
							
				$date_from = date("Y-m-d",strtotime($date_from));		
			}
			
			$qdate_from_plus1 = date("Y-m-d",strtotime($date_from)+ONE_DAY_UNIX);
			
			//get shift range
			$this->db->from($this->prefix.'open_close_shift');
			$this->db->where("user_shift",$shift_billing);
			$this->db->where("(tanggal_shift = '".$date_from."' OR (tipe_shift = 'close' AND tanggal_shift = '".$qdate_from_plus1."' 
				AND created <= '".$qdate_from_plus1." 06:00:00'))");
			$get_shift = $this->db->get();
			
			if($get_shift->num_rows() > 0){
				
				$data_shift = array();
				foreach($get_shift->result() as $dtS){
					if(empty($data_shift[$dtS->user_shift])){
						$data_shift[$dtS->user_shift] = array(
							'jam_from' => '',
							'jam_till' => ''
						);
					}
					
					if($dtS->tipe_shift == 'open'){
						$data_shift[$dtS->user_shift]['jam_from'] = $dtS->jam_shift;
					}
					
					if($dtS->tipe_shift == 'close'){
						$data_shift[$dtS->user_shift]['jam_till'] = $dtS->jam_shift;
					}
					
				}
				
				if(!empty($data_shift[$shift_billing])){
					//FROM
					if(empty($data_shift[$shift_billing]['jam_from'])){
						if($shift_billing == 1){
							$data_shift[$shift_billing]['jam_from'] = '07:00'; //default
						}
						
						if($shift_billing == 2){
							$data_shift[$shift_billing]['jam_from'] = '07:00:00'; //default
							if(!empty($data_shift[1]['jam_till'])){
								//take from shift 1
								$data_shift[$shift_billing]['jam_from'] = $data_shift[1]['jam_till'].':59';
							}
						}
					}else{
						$data_shift[$shift_billing]['jam_from'] .= ':00';
					}
					
					//TILL
					if(empty($data_shift[$shift_billing]['jam_till'])){
						if($shift_billing == 1){
							$data_shift[$shift_billing]['jam_till'] = '06:00:00'; //default
							if(!empty($data_shift[2]['jam_from'])){
								//take from shift 2
								$data_shift[$shift_billing]['jam_till'] = $data_shift[1]['jam_from'].':00';
							}
						}
						
						if($shift_billing == 2){
							$data_shift[$shift_billing]['jam_till'] = '06:00:00'; //default
						}
						
					}else{
						$data_shift[$shift_billing]['jam_till'] .= ':00';
					}
						
					//$qdate_till_max = date("Y-m-d", strtotime($date_from)+ONE_DAY_UNIX);
					if($shift_billing == 1){
						$qdate_till_max = date("Y-m-d",strtotime($date_from));
					}else
					if($shift_billing == 2){
						$jam_shift = (int)substr($data_shift[$shift_billing]['jam_till'],0,2);
						if(strlen($jam_shift) == 1){
							//asumsi pagi
							$qdate_till_max = date("Y-m-d",strtotime($date_from)+ONE_DAY_UNIX);
						}else{
							$qdate_till_max = date("Y-m-d",strtotime($date_from));
						}
					}else{
						//all shift
						
					}
					
					$params['where'][] = "(a.payment_date >= '".$date_from." ".$data_shift[$shift_billing]['jam_from']."' AND a.payment_date <= '".$qdate_till_max." ".$data_shift[$shift_billing]['jam_till']."')";
					
					/*$params['where'][] = "(DATE_FORMAT(a.payment_date, '%Y-%m-%d') = '".$date_from."') 
					AND (DATE_FORMAT(a.payment_date, '%H:%i:%s') BETWEEN '".$data_shift[$shift_billing]['jam_from']."' AND '".$data_shift[$shift_billing]['jam_till']."')";*/
				}
			}else{
			
				$qdate_till_max = date("Y-m-d",strtotime($date_from)+ONE_DAY_UNIX);
				$params['where'][] = "(a.payment_date >= '".$date_from." 07:00:01' AND a.payment_date <= '".$qdate_till_max." 06:00:00')";
				
				/*$params['where'][] = "(DATE_FORMAT(a.payment_date, '%Y-%m-%d') = '".$date_from."')  AND (DATE_FORMAT(a.payment_date, '%H:%i:%s') BETWEEN '07:00:01' AND '24:00:00')";*/
			}
		}
		
		//echo $where_shift_billing;
		
		if(!empty($report_paid_order)){
			$params['order'] = array('a.id' => $report_paid_order);
		}
		if(!empty($is_dropdown)){
			$params['order'] = array('a.billing_no' => 'ASC');
		}
		if(!empty($searching)){
			
			if(!empty($by_product_order)){
				
			}else{
				$params['where'][] = "(a.billing_no LIKE '%".$searching."%' OR a.billing_no LIKE '%".$searching."%')";
			}
			
		}
		
		//merge_bill_id
		$merge_bill_id = $this->input->post('merge_bill_id', true);
		if(!empty($merge_bill_id)){
			$params['where'][] = "a.id IN (".$merge_bill_id.")";
			$billing_status = 'hold';
		}
		
		if(!empty($billing_status)){
			$params['where'][] = "(a.billing_status = '".$billing_status."')";
			
			if($billing_status == 'cancel'){
				$params['where'][] = "(a.merge_id IS NULL OR a.merge_id = 0)";
			}
			
		}else{
			$params['where'][] = "(a.billing_status = '-')";
		}
		
		
		if(isset($_POST['use_range_date'])){
			$skip_date = false;
			if(empty($use_range_date)){
				$skip_date = true;
			}
		}
		
		if($skip_date == true){
		
		}else{
		
			if(empty($date_from)){
				$date_from = date('Y-m-d');
			}
			
			if(!empty($date_from)){
			
				if(empty($date_from)){ $date_from = date('Y-m-d'); }
				
				$mktime_dari = strtotime($date_from);
							
				$qdate_from = date("Y-m-d",strtotime($date_from));
				$qdate_till = date("Y-m-d",strtotime($date_from));
				
				//if($billing_status == 'paid' || $billing_status == 'cancel'){
					if(empty($date_till)){ $date_till = date('Y-m-d'); }
					$qdate_till = date("Y-m-d",strtotime($date_till));
				//}
				
				$qdate_till_max = date("Y-m-d",strtotime($qdate_till)+ONE_DAY_UNIX);
				
				if(!empty($use_payment_date)){
					//07:00:00
					$params['where'][] = "(a.payment_date >= '".$qdate_from." 00:00:01' AND a.payment_date <= '".$qdate_till_max." 06:00:00')";
				}else{
				
					//exception
					if($billing_status == 'hold' OR $billing_status == 'paid'){						
						
						if($billing_status == 'paid'){
							$qdate_from = date("Y-m-d",strtotime($qdate_from));
						}else{
							$qdate_from = date("Y-m-d",strtotime($qdate_from)-ONE_DAY_UNIX);
						}
					}
					
					if($no_midnight == 1){
						$qdate_from = date("Y-m-d",strtotime($date_from));
					}
				
					if($billing_status == 'paid'){
						$params['where'][] = "(a.payment_date >= '".$qdate_from." 00:00:01' AND a.payment_date <= '".$qdate_till_max." 06:00:00')";
					}else{
						$params['where'][] = "(a.updated >= '".$qdate_from." 00:00:01' AND a.updated <= '".$qdate_till_max." 06:00:00')";
					}
					
					
				}
						
			}
		}
		
		if(!empty($by_product_order)){
			
			$this->db->select("DISTINCT(a.billing_id)");
			$this->db->from($this->table2." as a");
			$this->db->join($this->prefix.'product as b',"b.id = a.product_id","LEFT");
			
			if(!empty($searching)){
				$this->db->where("b.product_name LIKE '%".$searching."%'");
			}else{
				$this->db->where("b.product_id = -1");
			}
			
			$get_det = $this->db->get();
			
			$all_bill_id = array();
			if($get_det->num_rows() > 0){
				foreach($get_det->result() as $dt){
					if(!in_array($dt->billing_id, $all_bill_id)){
						$all_bill_id[] = $dt->billing_id;
					}
				}
			}
			
			if(!empty($all_bill_id)){
				$all_bill_id_txt = implode(",", $all_bill_id);
				$params['where'][] = "a.id IN (".$all_bill_id_txt.")";
			}
			
		}
		
		//SORTING BY
		if(!empty($sorting_by)){
			$params['order'] = array('a.'.$sorting_by => 'DESC');
		}
				
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		
		
		if(empty($get_opt['cashier_max_pembulatan'])){
			$get_opt['cashier_max_pembulatan'] = 0;
		}
		if(empty($get_opt['cashier_pembulatan_keatas'])){
			$get_opt['cashier_pembulatan_keatas'] = 0;
		}
  		
		$payment_id_cash = 1;
		if(empty($get_opt['payment_id_cash'])){
			$payment_id_cash = $get_opt['payment_id_cash'];
		}
  		
		$payment_id_debit = 1;
		if(empty($get_opt['payment_id_debit'])){
			$payment_id_debit = $get_opt['payment_id_debit'];
		}
  		
		$payment_id_credit = 1;
		if(empty($get_opt['payment_id_credit'])){
			$payment_id_credit = $get_opt['payment_id_credit'];
		}
  		
		$all_bil_id = array();
  		$newData = array();
		$no = 1;
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				$s['item_no'] = $no;
				$s['payment_date'] = date("d-m-Y H:i",strtotime($s['payment_date']));
				$s['billing_date'] = date("d-m-Y H:i",strtotime($s['created']));
				$s['created_datetime'] = date("d.m.Y H:i",strtotime($s['created']));
				
				$s['created_date'] = date("d-m-Y H:i",strtotime($s['created']));
				$s['updated_date'] = date("d-m-Y H:i",strtotime($s['updated']));
				
				if(!in_array($s['id'], $all_bil_id)){
					$all_bil_id[] = $s['id'];
				}				
				
				if(empty($s['tax_total'])){
					$s['tax_total'] = 0;
				}
				
				if(empty($s['service_total'])){
					$s['service_total'] = 0;
				}
				
				if(empty($s['discount_total'])){
					$s['discount_total'] = 0;
				}
				
				if(empty($s['total_dp'])){
					$s['total_dp'] = 0;
				}
				
				if(empty($s['compliment_total'])){
					$s['compliment_total'] = 0;
				}
				
				if(!empty($s['include_tax']) OR !empty($s['include_service'])){
					$s['total_billing_display'] = $s['total_billing'];
					
					if(!empty($s['include_tax'])){
						$s['total_billing_display'] += $s['tax_total'];
					}
					if(!empty($s['include_service'])){
						$s['total_billing_display'] += $s['service_total'];
					}
					
				}else{
					$s['total_billing_display'] = $s['total_billing'];
				}
				
				//SUB TOTAL
				$s['subtotal_billing'] = $s['total_billing']-$s['discount_total'];
				$s['total_billing_display'] = $s['subtotal_billing'];
				
				$s['total_billing_show'] = priceFormat($s['total_billing']);
				$s['total_paid_show'] = priceFormat($s['total_paid']);
				$s['tax_total_show'] = priceFormat($s['tax_total']);
				$s['service_total_show'] = priceFormat($s['service_total']);
				$s['discount_total_show'] = priceFormat($s['discount_total']);
				$s['compliment_total_show'] = priceFormat($s['compliment_total']);
				$s['total_dp_show'] = priceFormat($s['total_dp']);
				$s['user_fullname'] = $s['user_firstname'].' '.$s['user_lastname'];
						
					
				if(!empty($s['is_compliment'])){
					$s['total_billing'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
					//DEPRECATED
					//if(!empty($s['include_tax']) OR !empty($s['include_service'])){
					//	$s['total_billing'] = $s['total_billing'];
					//}
					$s['service_total'] = 0;
					$s['tax_total'] = 0;
				}	
				
				if(empty($s['grand_total'] )){
					$s['grand_total'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
					if(!empty($s['include_tax']) OR !empty($s['include_service'])){
						$s['grand_total'] = $s['total_billing'];
					}
				}
				
				
				$s['grand_total_show'] = priceFormat($s['grand_total']);
				
				$s['total_qty_order'] = 0;
				$s['total_qty_deliver'] = 0;
				$s['order_total'] = 0;
				$s['order_total_show'] = 0;
				$s['total_hpp'] = 0;
				$s['total_hpp_show'] = 0;
				$s['total_profit'] = 0;
				$s['total_profit_show'] = 0;
				$s['percent_status_order'] = 0;
				
				//NOTES
				$s['payment_note'] = '';
				if(!empty($s['is_compliment'])){
					$s['payment_note'] = 'COMPLIMENT';
				}
				
				if($s['billing_status'] == 'paid'){
					if(!empty($s['is_half_payment'])){
						$s['payment_note'] = 'HALF PAYMENT';
						
						$s['total_paid'] = $s['total_cash'];
						$s['total_paid_show'] = priceFormat($s['total_paid']);
						
					}else{
											
						if($s['payment_id'] != $payment_id_cash){
							if(empty($s['total_credit'])){
								$s['total_credit'] = $s['total_billing'];
							}
							$s['total_cash'] = 0;
						}else{
							if(empty($s['total_cash'])){
								$s['total_cash'] = $s['total_billing'];
							}
							$s['total_credit'] = 0;
						}
						
					}
				}
				
				//if(strtolower($s['payment_type_name']) != 'cash'){
				if($s['payment_id'] != $payment_id_cash){
					$s['payment_note'] = strtoupper($s['bank_name']).' '.$s['card_no'];
				}
				
				if(empty($s['payment_id'])){
					$s['payment_id'] = 1;
					$s['payment_type_name'] = 'Cash';
				}
				
				$s['total_cash_show'] = priceFormat($s['total_cash']);
				$s['total_credit_show'] = priceFormat($s['total_credit']);
				
				$s['split_merge_status'] = '';
				if(!empty($s['split_from_id'])){
					$s['split_merge_status'] = 'SPLIT';
				}
				if(!empty($s['merge_id'])){
					$s['split_merge_status'] = 'MERGE';
				}
				
				$s['max_pembulatan'] = $get_opt['cashier_max_pembulatan'];
				$s['pembulatan_keatas'] = $get_opt['cashier_pembulatan_keatas'];
				
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
				
				$s['billing_no_show'] = $s['billing_no'];
				if(!empty($s['is_reservation'])){
					$s['billing_no_show'] = 'R'.$s['billing_no'];
				}
				
				$newData[$s['id']] = $s;
				//array_push($newData, $s);
				
				$no++;
			}
		}
		
		$all_bil_id_txt = implode("','", $all_bil_id);
		$this->db->select("billing_id, order_qty, product_price, product_price_hpp, order_status, free_item, package_item");
		$this->db->from($this->table2);
		$this->db->where("billing_id IN ('".$all_bil_id_txt."')");
		$this->db->where("is_deleted = 0");
		$get_detail = $this->db->get();
		if($get_detail->num_rows() > 0){
			foreach($get_detail->result() as $detail){
				
				$total_qty = $detail->order_qty;
				
				
				//FREE				
				if($detail->free_item == 1 AND $detail->package_item == 0){
					$detail->product_price = 0;
				}		

				//package_item
				if($detail->package_item == 1){
					$detail->product_price = 0;
				}
				
				$total_order = $detail->order_qty*$detail->product_price;
				$total_hpp = $detail->order_qty*$detail->product_price_hpp;
				
				if($detail->order_status == 'delivered'){
					$newData[$detail->billing_id]['total_qty_deliver'] += $total_qty;
				}else{
					$newData[$detail->billing_id]['total_qty_order'] += $total_qty;
				}
				
				$newData[$detail->billing_id]['total_hpp'] += $total_hpp;
				$newData[$detail->billing_id]['order_total'] += $total_order;
				$newData[$detail->billing_id]['order_total_show'] = 'Rp '.priceFormat($newData[$detail->billing_id]['order_total']);
								
				$total_qty_order = ($newData[$detail->billing_id]['total_qty_deliver']+$newData[$detail->billing_id]['total_qty_order']);
				$percent_status_order = ($newData[$detail->billing_id]['total_qty_deliver'] / $total_qty_order) * 100;
				$newData[$detail->billing_id]['percent_status_order'] = $percent_status_order;
				
			}
		}	
		
		$newData_switch = $newData;
		$newData = array();
		if(!empty($newData_switch)){
			foreach($newData_switch as $dt){
						
				$dt['total_profit'] = $dt['total_billing']-$dt['total_hpp'];				
				$dt['total_hpp_show'] = 'Rp '.priceFormat($dt['total_hpp']);
				$dt['total_profit_show'] = 'Rp '.priceFormat($dt['total_profit']);
				$newData[] = $dt;
			}
		}

				
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}

	public function gridData_billingDetail()
	{
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';
		$this->product_img_url = RESOURCES_URL.'product/thumb/';
				
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
			'fields'		=> "a.id, a.product_id, a.order_qty, a.product_price, a.product_price_hpp, a.product_normal_price, 
								a.category_id, a.billing_id, a.has_varian, a.product_varian_id, a.varian_id,
								a.include_tax, a.tax_percentage, a.tax_total, a.include_service, a.service_percentage, a.service_total,
								a.discount_id, a.discount_notes, a.discount_percentage, a.discount_price, a.discount_total,
								a.is_takeaway, a.takeaway_no_tax, a.takeaway_no_service, a.is_compliment,
								a.order_status, a.order_notes, a.is_active, a.retur_type, a.retur_qty, a.retur_reason,
								a.is_promo, a.promo_id, a.promo_tipe, a.promo_percentage, a.promo_price, a.promo_desc,
								a.is_buyget, a.buyget_id, a.buyget_tipe, a.buyget_desc, a.buyget_qty, a.buyget_percentage, a.buyget_total, 
								a.buyget_item, a.free_item, a.package_item, a.ref_order_id, a.use_stok_kode_unik, a.data_stok_kode_unik, a.product_price_real,
								a.is_kerjasama, a.supplier_id, a.persentase_bagi_hasil, a.total_bagi_hasil,
								b.product_name, b.product_chinese_name, b.has_varian, b.product_desc, b.product_type, b.product_image, 
								b.category_id, b.product_group, b.use_tax, b.use_service, c.product_category_name, d.varian_name, e.item_code",
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
			'where'			=> array("a.order_qty > 0", 'a.is_deleted' => 0, 'a.billing_id' => $billing_id),
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
		
		
		//cek opt
		$get_opt = get_option_value(array('hide_compliment_order'));
  		$hide_compliment_order = 0;
		if(!empty($get_opt['hide_compliment_order'])){
			$hide_compliment_order = 1;
		}
		
		$product_package = array();
		
  		$newData = array();
		$no = 1;
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				$s['item_no'] = $no;
				$s['order_total'] = $s['order_qty'] * $s['product_price_real'];
				
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
				
				if(!empty($s['item_code'])){
					$s['product_name'] = $s['item_code'].'<br/>'.$s['product_name'];
				}
				$s['product_detail_info'] = $s['product_name'];
				
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
				
				$s['product_name_show'] = $s['product_name'];
				$s['product_detail_info'] .= $additional_text.'<br/>X @ Rp.'.priceFormat($s['product_price']);	
					
				//PROMO UPDATE
				if($s['is_promo'] == 1){
					
						
					//if(empty($s['product_normal_price'])){
						//$s['product_normal_price'] = $s['product_price']+$s['promo_price'];
						$s['product_normal_price_promo'] = $s['product_price'];
					//}
					
					$promo_price = $s['product_price']-$s['promo_price'];
					
					$s['promo_price_show'] = priceFormat($promo_price);
					$s['product_name_show'] = $s['product_name'].' <font color="orange">Promo</font>';
					$s['product_price_show'] = 'Rp <strike>'.priceFormat($s['product_normal_price']).'</strike> <font color="orange">'.$s['promo_price_show'].'</font>';
					$s['product_detail_info'] = $s['product_name'].$additional_text.' <font color="orange">Promo</font><br/>X @ Rp.'.priceFormat($s['product_price']);
					
					$s['discount_id'] = $s['promo_id'];
					$s['discount_notes'] = $s['promo_desc'];
					$s['discount_percentage'] = $s['promo_percentage'];
					$s['discount_price'] = $s['promo_price'];
					//$s['discount_total'] = ($s['order_qty']*$s['discount_price']);
				}

					
				//BUY AND GET
				if($s['is_buyget'] == 1){
					
					$tipe_bg = 'BG';
					if($s['buyget_tipe'] == 'percentage'){
						$tipe_bg = 'BG%';
					}
					
					$s['product_name_show'] = $s['product_name'].' <font color="red">'.$tipe_bg.'</font>';
					
					$s['product_detail_info'] = $s['product_name'].$additional_text.' <font color="red">'.$tipe_bg.'</font><br/>X @ Rp.'.priceFormat($s['product_price']);
					
					if($s['is_promo'] == 1){
						$s['product_detail_info'] = $s['product_name'].$additional_text.' <font color="orange">Promo</font>,<font color="red">'.$tipe_bg.'</font><br/>X @ Rp.'.priceFormat($s['product_price']);
					}
					
				}
				
				//FREE				
				if($s['free_item'] == 1 AND $s['package_item'] == 0){
					$s['product_name_show'] = $s['product_name'].' <font color="red">Free</font>';
					$s['product_detail_info'] = '&#10146; '.$s['product_name'].$additional_text.' <font color="red">Free</font>';
					$s['order_total'] = 0;
					$s['product_price'] = 0;
					$s['product_price_real'] = 0;
				}		

				//package_item
				if($s['package_item'] == 1){
					$s['product_name_show'] = $s['product_name'];
					$s['product_detail_info'] = '&#10146; '.$s['product_name'].$additional_text;
					//9644, 9492, 10146, 10148
					$s['order_total'] = 0;
					$s['product_price'] = 0;
					$s['product_price_real'] = 0;
				}
				
				//update 2018-02-25
				if(in_array($s['id'], $product_package)){
					$s['order_status'] = 'done';
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
				if(empty($s['discount_total'])){
					$s['discount_total_show'] = '-';
				}
					
				$s['order_subtotal'] = $s['order_total']-$s['discount_total'];		
				$s['order_subtotal_show'] = priceFormat($s['order_subtotal']);		
				if(empty($s['order_subtotal'])){
					$s['order_subtotal_show'] = '-';
				}
				
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
				
				if($s['is_promo'] == 1){
					$s['order_subtotal'] = ($s['order_total']);
				}else{
					$s['order_subtotal'] = ($s['order_total'])-$s['discount_total'];
				}
				
				$s['order_subtotal_show'] = priceFormat($s['order_subtotal']);
				$total_taxservice = $s['tax_total']+$s['service_total'];
				$s['total_taxservice_show'] = priceFormat($total_taxservice);
				if(empty($total_taxservice)){
					$s['total_taxservice_show'] = '-';
				}
				
				if($s['free_item'] == 1 AND $s['package_item'] == 0){
					$s['discount_total_show'] = '-';
					$s['order_subtotal_show'] = '<span style="color:red;">Free</span>';
				}
				
				if($s['package_item'] == 1){
					$s['discount_total_show'] = '-';
					$s['order_subtotal_show'] = '-';
				}
				
				$s['is_kerjasama_text'] = ($s['is_kerjasama'] == '1') ? '<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>';
				$s['total_bagi_hasil_show'] = priceFormat($s['total_bagi_hasil']);
				
				$no++;
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
		header('Content-Type: text/plain; charset=utf-8');
      	die(json_encode($get_data));
	}
	
	public function setStatusBilling(){
		$this->table = $this->prefix.'billing';
		
		$get_id = $this->input->post('id', true);		
		$setStatus = $this->input->post('setStatus', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
		
		//change to hold
		$setData = array(
			'billing_status'=> $setStatus
		);
		
		$this->db->where("id IN (".$sql_Id.")");
		$q = $this->db->update($this->table, $setData);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Set Status Billing to: '.$setStatus.' Failed!'); 
        }
		die(json_encode($r));
	}
	
	
	public function paidBillByMenu(){
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';		
		$session_user = $this->session->userdata('user_username');	
		$role_id = $this->session->userdata('role_id');	
		
		//is_active_text
		$sortAlias = array(
			//'is_active_text' => 'a.is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*,
								b.include_tax, b.tax_percentage, b.include_service, b.service_percentage,
								c.product_name, c.product_group",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table2.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'billing as b','b.id = a.billing_id','LEFT'),
										array($this->prefix.'product as c','c.id = a.product_id','LEFT')
									) 
								),
			'where'			=> array('b.is_deleted' => 0),
			'order'			=> array('c.product_name' => 'ASC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$billing_status = $this->input->post('billing_status');
		
		//FILTER
		$skip_date = $this->input->post('skip_date');
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
				if(empty($date_till)){ $date_till = date('Y-m-d'); }
				
				$mktime_dari = strtotime($date_from);
				$mktime_sampai = strtotime($date_till);
							
				$qdate_from = date("Y-m-d 07:00:00",strtotime($date_from));
				$qdate_till = date("Y-m-d 23:59:59",strtotime($date_till));
				$qdate_from_plus1 = date("Y-m-d",strtotime($qdate_till)+ONE_DAY_UNIX);
				
				$params['where'][] = "(b.payment_date >= '".$qdate_from."' AND b.payment_date <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.id' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(c.product_name LIKE '%".$searching."%' OR c.product_name LIKE '%".$searching."%')";
		}
		if(!empty($billing_status)){
			$params['where'][] = "(b.billing_status = '".$billing_status."')";
		}else{
			$params['where'][] = "(b.billing_status = '-')";
		}
				
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  
		$all_bil_id = array();
		$all_product_data = array();
  		$newData = array();
		$no = 1;
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['item_no'] = $no;				
				
				if(!in_array($s['id'], $all_bil_id)){
					$all_bil_id[] = $s['id'];
				}				
				
				if(empty($all_product_data[$s['product_id']] )){
					
					$all_product_data[$s['product_id']] = array(
						'product_id'	=> $s['product_id'],
						'product_name'	=> $s['product_name'],
						'product_group'	=> $s['product_group'],
						'total_qty'	=> 0,
						'total_billing'	=> 0,
						'total_billing_show'	=> 0,
						'grand_total'	=> 0,
						'grand_total_show'	=> 0,
						'tax_total'	=> 0,
						'tax_total_show'	=> 0,
						'service_total'	=> 0,
						'service_total_show'	=> 0,
						'total_qty_order'	=> 0,
						'total_qty_deliver'	=> 0,
						'order_total'	=> 0,
						'order_total_show'	=> 0,
						'total_hpp'	=> 0,
						'total_hpp_show'	=> 0,
						'total_profit'	=> 0,
						'total_profit_show'	=> 0,
						'percent_status_order'	=> 0
					);
					
					$no++;
					
				}
				
				$all_product_data[$s['product_id']]['total_qty'] += $s['order_qty'];
				
				//CHECK IF INCLUDE TAX AND SERVICE
				$is_include = false;
				$all_percentage = 100;
				if($s['include_tax'] == 1){
					$is_include = true;
					$all_percentage += $s['tax_percentage'];
				}
				
				if($s['include_service'] == 1){
					$is_include = true;		
					$all_percentage += $s['service_percentage'];		
				}
				
				if($is_include){
					
					$total = $s['product_price'];
					$one_percent = $s['product_price'] / $all_percentage;
					$tax_total = priceFormat($one_percent * $s['tax_percentage'], 0, ".", "");
					$service_total = priceFormat($one_percent * $s['service_percentage'], 0, ".", "");
					$subtotal = $total - ($tax_total + $service_total);
					
					$all_product_data[$s['product_id']]['total_billing'] += ($subtotal*$s['order_qty']);
					$all_product_data[$s['product_id']]['tax_total'] += ($tax_total*$s['order_qty']);
					$all_product_data[$s['product_id']]['service_total'] += ($service_total*$s['order_qty']);
					
					$all_product_data[$s['product_id']]['grand_total'] += ($total*$s['order_qty']);
					
				}else{
					$all_product_data[$s['product_id']]['total_billing'] += ($s['product_price']*$s['order_qty']);
					$all_product_data[$s['product_id']]['grand_total'] += ($s['product_price']*$s['order_qty']);
				}
				
				//PROFIT
				$total_qty = $s['order_qty'];
				$total_order = ($s['product_price']*$s['order_qty']);
				$total_hpp = ($s['product_price_hpp']*$s['order_qty']);
				
				if($s['order_status'] == 'delivered'){
					$all_product_data[$s['product_id']]['total_qty_deliver'] += $total_qty;
				}else{
					$all_product_data[$s['product_id']]['total_qty_order'] += $total_qty;
				}
				
				$all_product_data[$s['product_id']]['total_hpp'] += $total_hpp;
				$all_product_data[$s['product_id']]['order_total'] += $total_order;
				$all_product_data[$s['product_id']]['order_total_show'] = 'Rp '.priceFormat($all_product_data[$s['product_id']]['order_total']);
								
				$total_qty_order = ($all_product_data[$s['product_id']]['total_qty_deliver']+$all_product_data[$s['product_id']]['total_qty_order']);
				$percent_status_order = ($all_product_data[$s['product_id']]['total_qty_deliver'] / $total_qty_order) * 100;
				$all_product_data[$s['product_id']]['percent_status_order'] = $percent_status_order;
				
			}
		}
		
		$sort_qty = array();
		$no = 1;
		if(!empty($all_product_data)){
			foreach($all_product_data as $dt){
				$dt['item_no'] = $no;
				
				$sort_qty[$dt['product_id']] = $dt['total_qty'];
				$dt['total_billing_show'] = priceFormat($dt['total_billing']);
				$dt['grand_total_show'] = priceFormat($dt['grand_total']);
				$dt['tax_total_show'] = priceFormat($dt['tax_total']);
				$dt['service_total_show'] = priceFormat($dt['service_total']);
				
				$dt['total_profit'] = $dt['total_billing']-$dt['total_hpp'];				
				$dt['total_hpp_show'] = 'Rp '.priceFormat($dt['total_hpp']);
				$dt['total_profit_show'] = 'Rp '.priceFormat($dt['total_profit']);
				$sort_profit[$dt['product_id']] = $dt['total_profit'];
				
				$newData[$dt['product_id']] = $dt;
				$no++;
			}
		}
		
		$order_qty = $this->input->post('order_qty');
		if(!empty($order_qty)){
			
			//RANK QTY
			if($order_qty == 1){
				arsort($sort_qty);		
				$xnewData = array();
				foreach($sort_qty as $key => $dt){
				
					if(!empty($newData[$key])){
						$xnewData[] = $newData[$key];
					}
					
				}			
				$newData = $xnewData;
			}
			
			//RANK PROFIT
			if($order_qty == 2){
				arsort($sort_profit);
				$xnewData = array();
				foreach($sort_profit as $key => $dt){
				
					if(!empty($newData[$key])){
						$xnewData[] = $newData[$key];
					}
					
				}			
				$newData = $xnewData;
			}
		}else{
		$xnewData = array();
			foreach($newData as $dt){
			$xnewData[] = $dt;
			}
			
			$newData = $xnewData;
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function gridData_billingDetail_split()
	{
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail_split';
		$this->product_img_url = RESOURCES_URL.'product/thumb/';
				
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
			'fields'		=> "a.id, a.product_id, a.order_qty, a.order_qty_split, a.product_price, a.product_price_hpp, a.product_normal_price, 
								a.category_id, a.billing_id, a.has_varian, a.product_varian_id, a.varian_id,
								a.include_tax, a.tax_percentage, a.tax_total, a.include_service, a.service_percentage, a.service_total,
								a.discount_id, a.discount_notes, a.discount_percentage, a.discount_price, a.discount_total,
								a.is_takeaway, a.takeaway_no_tax, a.takeaway_no_service, a.is_compliment,
								a.order_status, a.order_notes, a.is_active, a.retur_type, a.retur_qty, a.retur_reason,
								a.is_promo, a.promo_id, a.promo_tipe, a.promo_percentage, a.promo_price, a.promo_desc,
								a.is_kerjasama, a.supplier_id, a.persentase_bagi_hasil, a.total_bagi_hasil, 
								a.buyget_item, a.free_item, a.ref_order_id,
								b.product_name, b.product_chinese_name, b.has_varian, b.product_desc, b.product_type, b.product_image, 
								b.category_id, b.product_group, b.use_tax, b.use_service, c.product_category_name, d.varian_name, e.item_code",
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
			'where'			=> array("a.order_qty > 0", 'a.is_deleted' => 0, 'a.billing_id' => $billing_id),
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
				
				$s['product_detail_info'] = $s['product_name'];
				
				$additional_text = '';
				if(!empty($s['product_chinese_name']) AND $s['product_chinese_name'] != '-'){
					$additional_text = '<br/>'.$s['product_chinese_name'];
				}
				
				if(!empty($s['varian_name'])){
					if($additional_text == ''){
						$additional_text = '<br/>'.$s['varian_name'];
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
					}else{
						$s['order_status_text'] .= 'Bar</b>';
					}
				}
				
				if(!empty($s['order_notes'])){
					$s['product_detail_info'] .= '<br/>Note: <i>'.$s['order_notes'].'</i>';
				}
				
				
				
				//TAX, SERVICE, TAKEAWAY & COMPLIMENT
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
				
				$s['is_kerjasama_text'] = ($s['is_kerjasama'] == '1') ? '<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>';
				$s['total_bagi_hasil_show'] = priceFormat($s['total_bagi_hasil']);
				
				$no++;
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
		header('Content-Type: text/plain; charset=utf-8');
      	die(json_encode($get_data));
	}
	
	//DISCOUNT
	public function gridData_billingDetail_discount()
	{
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';
		$this->table_discount = $this->prefix.'discount';
		$this->table_discount_product = $this->prefix.'discount_product';
		$this->product_img_url = RESOURCES_URL.'product/thumb/';
				
		$billing_id = $this->input->post('billing_id', true);
		if(empty($billing_id)){
			$billing_id = -1;
		}	
		
		$discount_id = $this->input->post('discount_id', true);
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'a.is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.id, a.product_id, a.order_qty, a.product_price, a.product_price_hpp, a.product_normal_price, 
								a.category_id, a.billing_id, a.has_varian, a.product_varian_id, a.varian_id,
								a.include_tax, a.tax_percentage, a.tax_total, a.include_service, a.service_percentage, a.service_total,
								a.discount_id, a.discount_notes, a.discount_percentage, a.discount_price, a.discount_total,
								a.is_takeaway, a.takeaway_no_tax, a.takeaway_no_service, a.is_compliment,
								a.order_status, a.order_notes, a.is_active, a.retur_type, a.retur_qty, a.retur_reason,
								a.is_promo, a.promo_id, a.promo_tipe, a.promo_percentage, a.promo_price, a.promo_desc,
								a.is_buyget, a.buyget_id, a.buyget_tipe, a.buyget_desc, a.buyget_qty, a.buyget_percentage, a.buyget_total,
								a.buyget_item, a.free_item, a.ref_order_id, a.use_stok_kode_unik, a.data_stok_kode_unik,
								a.is_kerjasama, a.supplier_id, a.persentase_bagi_hasil, a.total_bagi_hasil,
								b.product_name, b.product_chinese_name, b.has_varian, b.product_desc, b.product_type, b.product_image, 
								b.category_id, b.product_group, b.use_tax, b.use_service, c.product_category_name, d.varian_name, e.item_code",
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
		
		//check billing
		$data_billing = array();
		if(!empty($billing_id)){
			$this->db->select("total_billing, voucher_no, billing_no, discount_perbilling");
			$this->db->from($this->table);
			$this->db->where("id", $billing_id);
			$get_billing = $this->db->get();
			if($get_billing->num_rows() > 0){
				$data_billing = $get_billing->row();
			}
		}
		
		//CHECK DISCOUNT
		$data_diskon = array();
		$data_diskon_product = array();
		$allow_diskon_product = array();
		if(!empty($discount_id)){
			
			$this->db->select("discount_type, min_total_billing, discount_percentage, discount_price");
			$this->db->from($this->table_discount);
			$this->db->where("id", $discount_id);
			$get_diskon = $this->db->get();
			if($get_diskon->num_rows() > 0){
				$data_diskon = $get_diskon->row();
			}
			
			
			if(!empty($data_diskon)){
				
				if($data_diskon->discount_type == 0){
					
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
				
			}
			
			
		}
		
		//echo '<pre>';
		//print_r($allow_diskon_product);
		//die();
		
  		$newData = array();
		$no = 1;
		if(!empty($get_data['data'])){
			
			//check is buy get free item == 0
			$buyget_not_used = array();
			$buyget_used = array();
			foreach ($get_data['data'] as $key => $s){
				
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
				
				if($s['order_qty'] == 0){
					unset($get_data['data'][$key]);
				}
			
			}
			
			//echo '<pre>';
			//print_r($buyget_not_used);
			//die();
			
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
				
				$s['product_detail_info'] = $s['product_name'];
				
				$additional_text = '';
				if(!empty($s['product_chinese_name']) AND $s['product_chinese_name'] != '-'){
					$additional_text = '<br/>'.$s['product_chinese_name'];
				}
				
				if(!empty($s['varian_name'])){
					if($additional_text == ''){
						$additional_text = '<br/>'.$s['varian_name'];
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
					$s['product_price'] = $s['product_price']-$s['promo_price'];
					
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
					}else{
						$s['order_status_text'] .= 'Bar</b>';
					}
				}
				
				if(!empty($s['order_notes'])){
					$s['product_detail_info'] .= '<br/>Note: <i>'.$s['order_notes'].'</i>';
				}
				
				//TAX, SERVICE, TAKEAWAY & COMPLIMENT
				$s['tax_total_show'] = priceFormat($s['tax_total']);
				$s['service_total_show'] = priceFormat($s['service_total']);
				
				$product_price = $s['product_price'];
				$order_qty = $s['order_qty'];
				
				if(!empty($data_diskon)){
					
					//$s['discount_percentage'] = 0;
					//$s['discount_price'] = 0;
					//$s['discount_total'] = 0;
					$s['status_discount'] = 0;
					
					//discount per product
					$allow_discount = true;
					
					if($data_diskon->min_total_billing > 0){
						if($data_billing->total_billing < $data_diskon->min_total_billing){
							$allow_discount = false;
						}
					}
					
					
					if($allow_discount == true){
						
						
						if(!empty($s['discount_id'])){
							$s['status_discount'] = 3;
						}
						
						//if($data_diskon->discount_product == 1){
						if(!empty($allow_diskon_product)){
							
							$s['status_discount'] = 4;
							
							if(in_array($s['product_id'], $allow_diskon_product)){
								
								//echo $s['product_id'].'<br/>';
								
								//all
								if(!empty($data_diskon->discount_percentage)){
									//$s['discount_percentage'] = $data_diskon->discount_percentage;
									//$product_price_discount = priceFormat(($data_diskon->discount_percentage / 100) * $product_price, 0, ".", "");
									//$s['discount_price'] = $product_price_discount;
									//$s['discount_total'] = $product_price_discount * $order_qty;
									$s['status_discount'] = 1;
								}else
								if(!empty($data_diskon->discount_price)){
									//$s['discount_percentage'] = 0;
									//$product_price_discount = priceFormat($product_price - $data_diskon->discount_price, 0, ".", "");
									//$s['discount_price'] = $product_price_discount;
									//$s['discount_total'] = $product_price_discount * $order_qty;
									$s['status_discount'] = 1;
								}
							}else{
								//$s['status_discount'] = 4;
							}
							
						}else{
							//all
							if(!empty($data_diskon->discount_percentage)){
								//$s['discount_percentage'] = $data_diskon->discount_percentage;
								//$product_price_discount = priceFormat(($data_diskon->discount_percentage / 100) * $product_price, 0, ".", "");
								//$s['discount_price'] = $product_price_discount;
								//$s['discount_total'] = $product_price_discount * $order_qty;
								$s['status_discount'] = 1;
							}else
							if(!empty($data_diskon->discount_price)){
								//$s['discount_percentage'] = 0;
								//$product_price_discount = priceFormat($product_price - $data_diskon->discount_price, 0, ".", "");
								//$s['discount_price'] = $product_price_discount;
								//$s['discount_total'] = $product_price_discount * $order_qty;
								$s['status_discount'] = 1;
							}
					
						}
						
					}else{
						$s['status_discount'] = 4;
					}
					
				}else{
					
					//DEFAULT-CURRENT STATUS
					$s['status_discount'] = 0;
				
					if(!empty($s['discount_id'])){
						$s['status_discount'] = 2;
					}
					
				}
				
				if($s['is_promo'] == 1 AND !empty($s['promo_id'])){
					$s['status_discount'] = 5;
				}
				
				if($s['is_compliment'] == 1){
					$s['status_discount'] = 6;
				}
				
				if($s['is_buyget'] == 1 AND !empty($s['buyget_id'])){
					$s['status_discount'] = 7;
					
					if($s['is_promo'] == 1){
						$s['status_discount'] = 8;
					}
					
					if(!empty($buyget_not_used[$s['id']])){
						$s['status_discount'] = 10;
					}
				}
				
				if($s['free_item'] == 1){
					$s['status_discount'] = 9;
				}
				
				//echo $s['product_id'].' -> '.$s['status_discount'].'<br/>';
							
				$s['discount_price_show'] = priceFormat($s['discount_price']);
				$s['discount_total_show'] = priceFormat($s['discount_total']);
				if($s['status_discount'] == 1){
					$s['status_discount_text'] = '<span style="color:green;"><b>Discount Allowed</b></span>';
				}else
				if($s['status_discount'] == 2){
					$s['status_discount_text'] = '<span style="color:green;"><b>'.$s['discount_notes'].'</b></span>';
				}else
				if($s['status_discount'] == 3){
					$s['status_discount_text'] = '<span style="color:green;"><b>'.$s['discount_notes'].'<br/>Discount Allowed</b></span>';
				}else
				if($s['status_discount'] == 4){
					$s['status_discount_text'] = '<span style="color:red;"><b>No Disc</b></span>';
				}else
				if($s['status_discount'] == 5){
					$s['status_discount_text'] = '<span style="color:orange;"><b>On Promo</b><br/>'.$s['promo_desc'].'</span>';
				}else
				if($s['status_discount'] == 6){
					$s['status_discount_text'] = '<span style="color:orange;"><b>On Compliment</b></span>';
				}else
				if($s['status_discount'] == 7){
					$s['status_discount_text'] = '<span style="color:red;"><b>'.$s['buyget_desc'].'</b></span>';
				}else
				if($s['status_discount'] == 8){
					$s['status_discount_text'] = '<span style="color:orange;"><b>'.$s['promo_desc'].'</b></span><br/><span style="color:red;"><b>'.$s['buyget_desc'].'</b></span>';
				}else
				if($s['status_discount'] == 9){
					$s['status_discount_text'] = '<span style="color:blue;"><b>FREE ITEM<br/>'.$s['discount_notes'].'</b></span>';
				}else
				if($s['status_discount'] == 10){
					$s['status_discount_text'] = '<span style="color:green;"><b>Discount Allowed</b></span>';
				}else{
					$s['status_discount_text'] = '<span><b>&nbsp;</b></span>';
				}
				
				
				
				if(!empty($data_billing->voucher_no)){
					
					if($data_billing->discount_perbilling == 1){
						$s['status_discount_text'] = '<span style="color:green;"><b>Discount Per-Billing</b><br/></span><span style="color:orange;"><b>'.$data_billing->voucher_no.'</b></span>';
					}else{
						$s['status_discount_text'] .= '<br/><span style="color:orange;"><b>'.$data_billing->voucher_no.'</b></span>';
					}
					
				}
				
				
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
				
				
				$s['is_kerjasama_text'] = ($s['is_kerjasama'] == '1') ? '<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>';
				$s['total_bagi_hasil_show'] = priceFormat($s['total_bagi_hasil']);
				
				$no++;
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
		header('Content-Type: text/plain; charset=utf-8');
      	die(json_encode($get_data));
	}
	
	//Compliment
	public function gridData_billingDetail_compliment()
	{
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';
		$this->table_discount = $this->prefix.'discount';
		$this->table_discount_product = $this->prefix.'discount_product';
		$this->product_img_url = RESOURCES_URL.'product/thumb/';
				
		$billing_id = $this->input->post('billing_id', true);
		if(empty($billing_id)){
			$billing_id = -1;
		}	
		
		$discount_id = $this->input->post('discount_id', true);
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'a.is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.id, a.product_id, a.order_qty, a.product_price, a.product_price_hpp, a.product_normal_price, 
								a.category_id, a.billing_id, a.has_varian, a.product_varian_id, a.varian_id,
								a.include_tax, a.tax_percentage, a.tax_total, a.include_service, a.service_percentage, a.service_total,
								a.discount_id, a.discount_notes, a.discount_percentage, a.discount_price, a.discount_total,
								a.is_takeaway, a.takeaway_no_tax, a.takeaway_no_service, a.is_compliment,
								a.order_status, a.order_notes, a.is_active, a.retur_type, a.retur_qty, a.retur_reason,
								a.is_promo, a.promo_id, a.promo_tipe, a.promo_percentage, a.promo_price, a.promo_desc,
								a.is_buyget, a.buyget_id, a.buyget_tipe, a.buyget_desc, a.buyget_qty, a.buyget_percentage, a.buyget_total,
								a.buyget_item, a.free_item, a.ref_order_id, a.ref_order_id, a.use_stok_kode_unik, a.data_stok_kode_unik,
								a.is_kerjasama, a.supplier_id, a.persentase_bagi_hasil, a.total_bagi_hasil,
								b.product_name, b.product_chinese_name, b.has_varian, b.product_desc, b.product_type, b.product_image, 
								b.category_id, b.product_group, b.use_tax, b.use_service, c.product_category_name, d.varian_name, e.item_code",
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
			'where'			=> array("a.order_qty > 0", 'a.is_deleted' => 0, 'a.billing_id' => $billing_id),
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
		
		//check billing
		$data_billing = array();
		if(!empty($billing_id)){
			$this->db->select("total_billing");
			$this->db->from($this->table);
			$this->db->where("id", $billing_id);
			$get_billing = $this->db->get();
			if($get_billing->num_rows() > 0){
				$data_billing = $get_billing->row();
			}
		}
		
		//CHECK DISCOUNT
		$data_diskon = array();
		$data_diskon_product = array();
		$allow_diskon_product = array();
		if(!empty($discount_id)){
			
			$this->db->select("discount_type, min_total_billing, discount_percentage, discount_price");
			$this->db->from($this->table_discount);
			$this->db->where("id", $discount_id);
			$get_diskon = $this->db->get();
			if($get_diskon->num_rows() > 0){
				$data_diskon = $get_diskon->row();
			}
			
			if(!empty($data_diskon)){
				if($data_diskon->discount_type == 0){
					
					$this->db->select("product_id");
					$this->db->from($this->table_discount_product);
					$this->db->where("id", $discount_id);
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
			}
			
		}
		  		
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
				
				$s['product_detail_info'] = $s['product_name'];
				
				$additional_text = '';
				if(!empty($s['product_chinese_name']) AND $s['product_chinese_name'] != '-'){
					$additional_text = '<br/>'.$s['product_chinese_name'];
				}
				
				if(!empty($s['varian_name'])){
					if($additional_text == ''){
						$additional_text = '<br/>'.$s['varian_name'];
					}else{
						$additional_text .= ' ('.$s['varian_name'].')';
					}
				}
				
				$s['product_detail_info'] .= $additional_text.'<br/>X @ Rp.'.priceFormat($s['product_price']);				
				
				$s['order_status_text'] = '<b style="color:orange;">'.ucwords($s['order_status']).'</b>';
				if($s['order_status'] == 'done'){
					$s['order_status_text'] = '<b style="color:green;">Print To<br/>';
					
					if($s['product_group'] == 'food'){
						$s['order_status_text'] .= 'Kitchen</b>';
					}else{
						$s['order_status_text'] .= 'Bar</b>';
					}
				}
				
				if(!empty($s['order_notes'])){
					$s['product_detail_info'] .= '<br/>Note: <i>'.$s['order_notes'].'</i>';
				}
				
				//TAX, SERVICE, TAKEAWAY & COMPLIMENT
				$s['tax_total_show'] = priceFormat($s['tax_total']);
				$s['service_total_show'] = priceFormat($s['service_total']);
				
				$product_price = $s['product_price'];
				$order_qty = $s['order_qty'];
				
				if(!empty($data_diskon)){
					
					//$s['discount_percentage'] = 0;
					//$s['discount_price'] = 0;
					//$s['discount_total'] = 0;
					$s['status_discount'] = 0;
					
					//discount per product
					$allow_discount = true;
					
					if($data_diskon->min_total_billing > 0){
						if($data_billing->total_billing < $data_diskon->min_total_billing){
							$allow_discount = false;
						}
					}
					
					
					if($allow_discount == true){
						
						if(!empty($s['discount_id'])){
							$s['status_discount'] = 3;
						}
						
						//if($data_diskon->discount_product == 1){
						if(!empty($allow_diskon_product)){
							
							$s['status_discount'] = 4;
							
							if(in_array($s['product_id'], $allow_diskon_product)){
								
								//echo $s['product_id'].'<br/>';
								
								//all
								if(!empty($data_diskon->discount_percentage)){
									//$s['discount_percentage'] = $data_diskon->discount_percentage;
									//$product_price_discount = priceFormat(($data_diskon->discount_percentage / 100) * $product_price, 0, ".", "");
									//$s['discount_price'] = $product_price_discount;
									//$s['discount_total'] = $product_price_discount * $order_qty;
									$s['status_discount'] = 1;
								}else
								if(!empty($data_diskon->discount_price)){
									//$s['discount_percentage'] = 0;
									//$product_price_discount = priceFormat($product_price - $data_diskon->discount_price, 0, ".", "");
									//$s['discount_price'] = $product_price_discount;
									//$s['discount_total'] = $product_price_discount * $order_qty;
									$s['status_discount'] = 1;
								}
								
							}else{
								//$s['status_discount'] = 4;
							}
							
						}else{
							//all
							if(!empty($data_diskon->discount_percentage)){
								//$s['discount_percentage'] = $data_diskon->discount_percentage;
								//$product_price_discount = priceFormat(($data_diskon->discount_percentage / 100) * $product_price, 0, ".", "");
								//$s['discount_price'] = $product_price_discount;
								//$s['discount_total'] = $product_price_discount * $order_qty;
								$s['status_discount'] = 1;
							}else
							if(!empty($data_diskon->discount_price)){
								//$s['discount_percentage'] = 0;
								//$product_price_discount = priceFormat($product_price - $data_diskon->discount_price, 0, ".", "");
								//$s['discount_price'] = $product_price_discount;
								//$s['discount_total'] = $product_price_discount * $order_qty;
								$s['status_discount'] = 1;
							}
					
						}
						
					}else{
						$s['status_discount'] = 4;
					}
					
				}else{
					
					//DEFAULT-CURRENT STATUS
					$s['status_discount'] = 0;
				
					if(!empty($s['discount_id'])){
						$s['status_discount'] = 2;
					}
					
				}
				
				
				
				if($s['is_promo'] == 1 AND !empty($s['promo_id'])){
					$s['status_discount'] = 5;
				}
				
				if($s['is_compliment'] == 1){
					//$s['status_discount'] = 6;
				}
				
				
				$s['discount_price_show'] = priceFormat($s['discount_price']);
				$s['discount_total_show'] = priceFormat($s['discount_total']);
				if($s['status_discount'] == 1){
					$s['status_discount_text'] = '<span style="color:green;"><b>Discount Allowed</b></span>';
				}else
				if($s['status_discount'] == 2){
					$s['status_discount_text'] = '<span style="color:green;"><b>'.$s['discount_notes'].'</b></span>';
				}else
				if($s['status_discount'] == 3){
					$s['status_discount_text'] = '<span style="color:green;"><b>'.$s['discount_notes'].'<br/>Discount Allowed</b></span>';
				}else
				if($s['status_discount'] == 4){
					$s['status_discount_text'] = '<span style="color:red;"><b>No Disc</b></span>';
				}else
				if($s['status_discount'] == 5){
					$s['status_discount_text'] = '<span style="color:orange;"><b>On Promo</b><br/>'.$s['promo_desc'].'</span>';
				}else
				if($s['status_discount'] == 6){
					$s['status_discount_text'] = '<span style="color:orange;"><b>On Compliment</span>';
				}else{
					$s['status_discount_text'] = '<span><b>&nbsp;</b></span>';
				}
				
				
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
				
				$s['is_kerjasama_text'] = ($s['is_kerjasama'] == '1') ? '<span style="color:green;">Yes</span>':'<span style="color:red;">No</span>';
				$s['total_bagi_hasil_show'] = priceFormat($s['total_bagi_hasil']);
				
				
				$no++;
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
		header('Content-Type: text/plain; charset=utf-8');
      	die(json_encode($get_data));
	}
	
	public function show_listPaidBilling(){
		
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
								b.category_id, b.product_group, c.product_category_name, d.varian_name, e.item_code",
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
				
				$s['product_detail_info'] = $s['product_name'];
				
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