<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class FraudCancelOrder extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_databilling', 'm');
		$this->load->model('model_billingdetail', 'm2');
	}	
	
	public function print_fraudCancelOrder(){
		
		$this->table = $this->prefix.'billing';
		$this->table2 = $this->prefix.'billing_detail';
		$this->table_product = $this->prefix.'product';
		$this->table_user = $this->prefix_apps.'users';
		$this->table_supervisor = $this->prefix_apps.'supervisor';
		$this->table_supervisor_log = $this->prefix_apps.'supervisor_log';
		$this->table_billing_log = $this->prefix.'billing_log';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);

		if(empty($date_from)){ $date_from = date('Y-m-d'); }
		if(empty($date_till)){ $date_till = date('Y-m-d'); }
		
		if(empty($sorting)){
			$sorting = 'payment_date';
		}
		if(empty($tipe_cancel)){
			$tipe_cancel = 'all';
		}
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'FRAUD VOID/CANCEL ORDER',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'tipe_cancel'	=> $tipe_cancel,
			'user_fullname'	=> $user_fullname,
			'sorting'	=> $sorting
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($date_from) OR empty($date_till)){
			die('Billing Paid Not Found!');
		}else{
				
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till = date("Y-m-d",strtotime($date_till));
			$qdate_till_max = date("Y-m-d",strtotime($date_till)+ONE_DAY_UNIX);
			
			$add_where = "(b.created >= '".$qdate_from." 07:00:01' AND b.created <= '".$qdate_till_max." 06:00:00')";
			
			$this->db->select("a.*, a.updated as order_date, b.billing_no, c.product_name");
			$this->db->from($this->table2." as a");
			$this->db->join($this->table." as b","b.id = a.billing_id","LEFT");
			$this->db->join($this->table_product." as c","c.id = a.product_id","LEFT");
			$this->db->where("(a.order_status = 'cancel')");
			
			if($tipe_cancel == 'paid'){
				$this->db->where("a.cancel_order_notes NOT LIKE 'cancel order unpaid:%'");
			}
			
			//$this->db->where("a.is_deleted", 1);
			$this->db->where($add_where);
			
			//if(empty($sorting)){
				$this->db->order_by("b.billing_no","ASC");
			//}else{
			//	$this->db->order_by($sorting,"ASC");
			//}
			
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
			
			//echo '<pre>';
			//print_r($data_post['report_data']);
			//die();
			
			$all_bil_id = array();
			$all_bil_no = array();
			$newData = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					$s['order_date'] = date("d-m-Y H:i",strtotime($s['created']));		
					
					if(!in_array($s['id'], $all_bil_id)){
						$all_bil_id[] = $s['id'];
					}	
							
					if(!in_array($s['billing_no'], $all_bil_no)){
						$all_bil_no[] = $s['billing_no'];
					}	
										
				}
			}
			
			//Billing
			$this->db->select("b.*");
			$this->db->from($this->table." as b");
			$this->db->where("(b.billing_status = 'cancel')");
			$this->db->where($add_where);
			$this->db->order_by("b.billing_no","ASC");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				foreach ($get_dt->result_array() as $s){
							
					if(!in_array($s['billing_no'], $all_bil_no)){
						$all_bil_no[] = $s['billing_no'];
					}	
										
				}
			}
			
			//echo '<pre>';
			//print_r($all_bil_no);
			//die();
			
			//check on supervisor log -checking fraud - lvl 1
			$sql_billing_no = '';
			if(!empty($all_bil_no)){
				foreach($all_bil_no as $dt){
					if(empty($sql_billing_no)){
						$sql_billing_no = '(';
					}
					
					if($sql_billing_no == '('){
						$sql_billing_no .= "a.log_data LIKE '%#".$dt."%'";
					}else{
						$sql_billing_no .= " OR a.log_data LIKE '%#".$dt."%'";
					}
					
				}
				
				if(!empty($sql_billing_no)){
					$sql_billing_no .= ')';
				}
			}
			
			$dt_cancel_billing = array();
			$dt_cancel_billing_data = array();
			$dt_cancel_billing_data_more = array();
			$dt_cancel_order_id = array();
			$dt_cancel_order_nama = array();
			$dt_cancel_order_data = array();
			$dt_spv_log = array();
			if(!empty($sql_billing_no)){
				$this->db->select("a.*, c.user_username, c.user_firstname, c.user_lastname");
				$this->db->from($this->table_supervisor_log." as a");
				$this->db->join($this->table_supervisor." as b","b.id = a.supervisor_id","LEFT");
				$this->db->join($this->table_user." as c","c.id = b.user_id","LEFT");
				//$add_where = "(a.created >= '".$qdate_from." 07:00:01' AND a.created <= '".$qdate_till_max." 06:00:00')";
				//$this->db->where($sql_billing_no.' OR '.$add_where);
				$this->db->where($sql_billing_no);
				
				$this->db->order_by("a.id", "ASC");
				$get_spv_log = $this->db->get();
				if($get_spv_log->num_rows() > 0){
					foreach($get_spv_log->result() as $dt){
						
						$spv_billing_no = '';
						//cancel_billing
						if($dt->supervisor_access == 'cancel_billing'){
							if(!empty($dt->ref_id_1)){
								$spv_billing_no = $dt->ref_id_1;
								if(!in_array($dt->ref_id_1, $dt_cancel_billing)){
									$dt_cancel_billing[] = $dt->ref_id_1;
									$dt_cancel_billing_data[$dt->ref_id_1] = $dt;
								}else{
									
									if(empty($dt_cancel_billing_data[$dt->ref_id_1])){
										$dt_cancel_billing_data[$dt->ref_id_1] = $dt;
									}else{
										if(empty($dt_cancel_billing_data_more[$dt->ref_id_1])){
											$dt_cancel_billing_data_more[$dt->ref_id_1] = array();
										}
										$dt_cancel_billing_data_more[$dt->ref_id_1] = $dt;
									}
									
								}
							}else{
								//old data
								$getBillno = explode("#", $dt->log_data);
								if(!empty($getBillno[1])){
									$spv_billing_no = $getBillno[1];
									if(!in_array($getBillno[1], $dt_cancel_billing)){
										$dt_cancel_billing[] = $getBillno[1];
										$dt_cancel_billing_data[$getBillno[1]] = $dt;
									}else{
										
										if(empty($dt_cancel_billing_data[$getBillno[1]])){
											$dt_cancel_billing_data[$getBillno[1]] = $dt;
										}else{
											if(empty($dt_cancel_billing_data_more[$getBillno[1]])){
												$dt_cancel_billing_data_more[$getBillno[1]] = array();
											}
											$dt_cancel_billing_data_more[$getBillno[1]] = $dt; 
										}
									}
								}
								
							}
							
						}
						
						//cancel order
						if($dt->supervisor_access == 'cancel_order'){
							if(!empty($dt->ref_id_1) AND !empty($dt->ref_id_2)){
								
								$bill_no = $dt->ref_id_1;
								$spv_billing_no = $bill_no;
								if(!in_array($dt->ref_id_1, $dt_cancel_billing)){
									$dt_cancel_billing[] = $dt->ref_id_1;
								}
								
								if(!empty($bill_no)){
									if(empty($dt_cancel_order_id[$bill_no])){
										$dt_cancel_order_id[$bill_no] = array();
									}
									
									if(!in_array($dt->ref_id_2, $dt_cancel_order_id[$bill_no])){
										$dt_cancel_order_id[$bill_no][] = $dt->ref_id_2;
									}
								}
								
								if(empty($dt_cancel_order_data[$bill_no])){
									$dt_cancel_order_data[$bill_no] = array();
								}
								$dt_cancel_order_data[$bill_no][] = $dt;
								
								
							}else{
								
								//old data
								$bill_no = '';
								$getBillno = explode("#", $dt->log_data);
								if(!empty($getBillno[1])){
									if(!in_array($getBillno[1], $dt_cancel_billing)){
										$dt_cancel_billing[] = $getBillno[1];
										$bill_no = $getBillno[1];
									}
								}
								
								$nama_menu_order = '';
								if(!empty($bill_no)){
									$spv_billing_no = $bill_no;
									$getDetailOrder = explode(" on ", $dt->log_data);
									if(!empty($getDetailOrder[0])){
										$getDetailOrder2 = explode("Cancel Order ", $getDetailOrder[0]);
										if(!empty($getDetailOrder2[1])){
											$nama_menu_order = $getDetailOrder2[1];
										}
									}
									
									if(!empty($nama_menu_order)){
										
										if(empty($dt_cancel_order_nama[$bill_no])){
											$dt_cancel_order_nama[$bill_no] = array();
										}
										
										if(!in_array($nama_menu_order, $dt_cancel_order_nama[$bill_no])){
											$dt_cancel_order_nama[$bill_no][] = $nama_menu_order;
										}
									}
								}
								
								$dt_cancel_order_data[$bill_no][] = $dt;
								
							}
						}
						
						if(!empty($spv_billing_no)){
							if(empty($dt_spv_log[$spv_billing_no])){
								$dt_spv_log[$spv_billing_no] = array();
							}
							$dt_spv_log[$spv_billing_no][] = $dt;
						}
						
					}
				}
			}
			
			//check paid billing after cancel
			$all_bill_paid = array();
			$all_bill_cancel = array();
			$all_bill_hold = array();
			$all_bill_data = array();
			$sql_billing_no = '';
			if(!empty($dt_cancel_billing)){
				$sql_billing_no = implode("','", $dt_cancel_billing);
			}
			
			if(!empty($sql_billing_no)){
					
				$this->db->from($this->table);
				$this->db->where("billing_no IN ('".$sql_billing_no."')");
				//$this->db->where("billing_status = 'paid'");
				$this->db->order_by("id", "ASC");
				$get_bill_paid = $this->db->get();
				if($get_bill_paid->num_rows() > 0){
					foreach($get_bill_paid->result() as $dt){
						
						if($dt->billing_status == 'paid'){
							if(!in_array($dt->billing_no, $all_bill_paid)){
								$all_bill_paid[] = $dt->billing_no;
							}
						}else
						if($dt->billing_status == 'cancel'){
							if(!in_array($dt->billing_no, $all_bill_cancel)){
								$all_bill_cancel[] = $dt->billing_no;
							}
						}else{
							if(!in_array($dt->billing_no, $all_bill_hold)){
								$all_bill_hold[] = $dt->billing_no;
							}
						}
						
						$all_bill_data[$dt->billing_no] = $dt;
						
					}
				}
			}
			
			//check billing log
			//"billing_no":"1802170002"
			$sql_billing_no = '';
			if(!empty($dt_cancel_billing)){
				$sql_billing_no = implode("','", $dt_cancel_billing);
			}
			
			$log_billing = array();
			if(!empty($sql_billing_no)){
				$this->db->select("a.*, b.billing_no");
				$this->db->from($this->table_billing_log." as a");
				$this->db->join($this->table." as b","b.id = a.billing_id","LEFT");
				//$add_where = "(a.created >= '".$qdate_from." 07:00:01' AND a.created <= '".$qdate_till_max." 06:00:00')";
				$this->db->where("b.billing_no IN ('".$sql_billing_no."')");
				$this->db->where($add_where);
				$this->db->order_by("id", "ASC");
				$get_billing_log = $this->db->get();
				if($get_billing_log->num_rows() > 0){
					foreach($get_billing_log->result() as $dt){
						if(empty($log_billing[$dt->billing_no])){
							$log_billing[$dt->billing_no] = array();
						}
						
						$log_billing[$dt->billing_no][] = $dt;
					}
				}
			}
			
			$data_post['dt_cancel_billing'] = $dt_cancel_billing;
			$data_post['dt_cancel_order_id'] = $dt_cancel_order_id;
			$data_post['dt_cancel_order_nama'] = $dt_cancel_order_nama;
			$data_post['all_bill_paid'] = $all_bill_paid;
			$data_post['all_bill_cancel'] = $all_bill_cancel;
			$data_post['all_bill_hold'] = $all_bill_hold;
			$data_post['log_billing'] = $log_billing;
			$data_post['dt_cancel_billing_data'] = $dt_cancel_billing_data;
			$data_post['dt_cancel_billing_data_more'] = $dt_cancel_billing_data_more;
			$data_post['dt_cancel_order_data'] = $dt_cancel_order_data;
			$data_post['all_bill_data'] = $all_bill_data;
			$data_post['dt_spv_log'] = $dt_spv_log;
			//$data_post['total_hpp'] = $total_hpp;
			
			//echo '<pre>';
			//print_r($dt_cancel_order_data);
			//die();
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		$useview = 'print_fraudCancelOrder';
		$data_post['report_name'] = 'FRAUD VOID/CANCEL ORDER';
		
		if($do == 'excel'){
			$useview = 'excel_fraudCancelOrder';
		}
		
		$this->load->view('../../billing/views/'.$useview, $data_post);	
	}
	
}