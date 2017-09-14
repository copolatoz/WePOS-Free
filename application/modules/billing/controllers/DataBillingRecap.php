<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class DataBillingRecap extends MY_Controller {
	
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
		
		//is_active_text
		$sortAlias = array(
			'is_active_text' => 'a.is_active',
			'billing_date' => 'a.created',
			'date' => 'a.updated'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, a.id as billing_id, b.table_no, b.table_desc, b.floorplan_id, c.floorplan_name, 
								d.payment_type_name, e.user_firstname, e.user_lastname, f.bank_name',
			'primary_key'	=> 'id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'table as b','b.id = a.table_id','LEFT'),
										array($this->prefix.'floorplan as c','c.id = b.floorplan_id','LEFT'),
										array($this->prefix.'payment_type as d','d.id = a.payment_id','LEFT'),
										array($this->prefix_apps.'users as e','e.user_username = a.createdby','LEFT'),
										array($this->prefix.'bank as f','f.id = a.bank_id','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('a.payment_date' => 'ASC'),
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
		
		//FILTER
		$shift_billing = $this->input->post('shift_billing');
		$user_cashier = $this->input->post('user_cashier');
		$skip_date = $this->input->post('skip_date');
		$date_from = $this->input->post('date_from');
		$date_till = $this->input->post('date_till');
		$keywords = $this->input->post('keywords');
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if(!empty($is_peruser)){
			if(!in_array($role_id, array(1,2))){
				$params['where'][] = "(a.createdby = '".$session_user."')";
			}
		}
		if(!empty($user_cashier)){
			//$this->db->where('a.createdby', $user_cashier);
			$params['where'][] = "(a.createdby = '".$user_cashier."')";
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
		
		if(!empty($report_paid_order)){
			$params['order'] = array('a.id' => $report_paid_order);
		}
		if(!empty($is_dropdown)){
			$params['order'] = array('a.billing_no' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.billing_no LIKE '%".$searching."%' OR a.billing_no LIKE '%".$searching."%')";
		}
		if(!empty($billing_status)){
			$params['where'][] = "(a.billing_status = '".$billing_status."')";
		}else{
			$params['where'][] = "(a.billing_status = 'paid')"; //default
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
							
				$qdate_from = date("Y-m-d 00:00:00",strtotime($date_from));
				$qdate_till = date("Y-m-d 23:59:59",strtotime($date_till));
				$qdate_till_max = date("Y-m-d 06:00:00",strtotime($qdate_till)+ONE_DAY_UNIX);
				
				$params['where'][] = "(a.updated >= '".$qdate_from."' AND a.updated <= '".$qdate_till_max."')";
						
			}
		}
				
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  
		$all_group_date = array();		  
		$billing_id_group = array();		  
		$all_bil_id = array();
  		$newData = array();
		$no = 1;
		$no_id = 1;
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				$s['item_no'] = $no;
				$s['payment_date'] = date("d-m-Y H:i",strtotime($s['payment_date']));
				$s['billing_date'] = date("d-m-Y H:i",strtotime($s['created']));
				$s['created_datetime'] = date("d.m.Y H:i",strtotime($s['created']));
								
				$s['created_date'] = date("d-m-Y H:i",strtotime($s['created']));
				$s['updated_date'] = date("d-m-Y H:i",strtotime($s['updated']));
									
				$s['grand_total'] = $s['total_billing'] + $s['tax_total'] + $s['service_total'];
				$s['grand_total_show'] = priceFormat($s['grand_total']);
								
				$payment_date = date("d-m-Y",strtotime($s['payment_date']));
				
				//< 6:00
				$payment_date_hour = (int) date("H",strtotime($s['payment_date']));
				//echo $payment_date_hour.'<br>';
				if($payment_date_hour < 6){
					$payment_date = date("d-m-Y",strtotime($s['payment_date'])-ONE_DAY_UNIX);
				}
				
				if(!in_array($s['id'], $all_bil_id)){
					$all_bil_id[] = $s['id'];
				}		
				
				$billing_id_group[$s['id']] = $payment_date;
				
				if(empty($all_group_date[$payment_date])){
					$all_group_date[$payment_date] = array(
						'id'		=> $no_id, 
						'item_no'	=> $no_id, 
						'date'		=> $payment_date, 
						'qty_billing'		=> 0, 
						'total_billing'		=> 0, 
						'total_billing_show'=> 0,
						'tax_total'			=> 0, 
						'tax_total_show'	=> 0, 
						'service_total'		=> 0, 
						'service_total_show'=> 0, 
						'grand_total'		=> 0, 
						'grand_total_show'	=> 0, 
						'total_cash'		=> 0, 
						'total_cash_show'	=> 0,
						'total_credit'		=> 0, 
						'total_credit_show'	=> 0,
						'total_compliment'		=> 0, 
						'total_compliment_show'	=> 0,
						'total_qty_order'		=> 0, 
						'total_qty_deliver'		=> 0, 
						'order_total'		=> 0, 
						'total_hpp'		=> 0, 
						'total_hpp_show'	=> 0,
						'total_profit'		=> 0, 
						'total_profit_show'	=> 0, 
						'percent_status_order'	=> 0
					);
					
					$no_id++;
				}
				
				$all_group_date[$payment_date]['qty_billing'] += 1;
				$all_group_date[$payment_date]['total_billing'] += $s['total_billing'];
				$all_group_date[$payment_date]['tax_total'] += $s['tax_total'];
				$all_group_date[$payment_date]['service_total'] += $s['service_total'];
				$all_group_date[$payment_date]['grand_total'] += $s['grand_total'];
				
				if(!empty($s['is_compliment'])){
					$all_group_date[$payment_date]['total_compliment'] += $s['grand_total'];
				}else{
				
					if(!empty($s['is_half_payment'])){
						$all_group_date[$payment_date]['total_cash'] += $s['total_cash'];
						$all_group_date[$payment_date]['total_credit'] += $s['total_credit'];
					}else{
						if($s['payment_id'] == 1){
							//cash
							$all_group_date[$payment_date]['total_cash'] += $s['grand_total'];
						}else{
							$all_group_date[$payment_date]['total_credit'] += $s['grand_total'];
						}
					}
				}
				
				$newData[$s['id']] = $s;
				//array_push($newData, $s);
				
				$no++;
			}
		}
		
		$all_bil_id_txt = implode("','", $all_bil_id);
		$this->db->from($this->table2);
		$this->db->where("billing_id IN ('".$all_bil_id_txt."')");
		$get_detail = $this->db->get();
		if($get_detail->num_rows() > 0){
			
			foreach($get_detail->result() as $detail){
				
				$total_qty = $detail->order_qty;
				$total_order = $detail->order_qty*$detail->product_price;
				$total_hpp = $detail->order_qty*$detail->product_price_hpp;
				
				if(!empty($billing_id_group[$detail->billing_id])){
					$tgl_group = $billing_id_group[$detail->billing_id];
				}
				
				if(!empty($tgl_group)){
					if($detail->order_status == 'delivered'){
						$all_group_date[$tgl_group]['total_qty_deliver'] += $total_qty;
					}else{
						$all_group_date[$tgl_group]['total_qty_order'] += $total_qty;
					}
					
					$all_group_date[$tgl_group]['total_hpp'] += $total_hpp;
					$all_group_date[$tgl_group]['order_total'] += $total_order;
					$all_group_date[$tgl_group]['order_total_show'] = 'Rp '.priceFormat($all_group_date[$tgl_group]['order_total']);
									
					$total_qty_order = ($all_group_date[$tgl_group]['total_qty_deliver']+$all_group_date[$tgl_group]['total_qty_order']);
					$percent_status_order = ($all_group_date[$tgl_group]['total_qty_deliver'] / $total_qty_order) * 100;
					$all_group_date[$tgl_group]['percent_status_order'] = $percent_status_order;
					
				}
				
			}
		}
		
		$newData = array();
		if(!empty($all_group_date)){
			foreach($all_group_date as $key => $detail){
				
				$detail['total_billing_show'] = priceFormat($detail['total_billing']);
				$detail['tax_total_show'] = priceFormat($detail['tax_total']);
				$detail['service_total_show'] = priceFormat($detail['service_total']);
				$detail['grand_total_show'] = priceFormat($detail['grand_total']);
				$detail['total_cash_show'] = priceFormat($detail['total_cash']);
				$detail['total_credit_show'] = priceFormat($detail['total_credit']);
				$detail['total_compliment_show'] = priceFormat($detail['total_compliment']);
				$detail['total_profit'] = $detail['total_billing']-$detail['total_hpp'];				
				$detail['total_hpp_show'] = 'Rp '.priceFormat($detail['total_hpp']);
				$detail['total_profit_show'] = 'Rp '.priceFormat($detail['total_profit']);
				
				$newData[$key] = $detail;
				
			}
		}	
		
		$newData_switch = $newData;
		$newData = array();
		if(!empty($newData_switch)){
			foreach($newData_switch as $dt){
				$newData[] = $dt;
			}
		}

				
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	
}