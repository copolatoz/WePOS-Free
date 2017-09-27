<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ReportPurchaseRecap extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_purchaseorder', 'm');
		$this->load->model('model_purchaseorderdetail', 'm2');
	}
	
	public function print_reportPurchaseRecap(){
		
		$this->table = $this->prefix.'po';
		$this->table2 = $this->prefix.'po_detail';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'PURCHASE REPORT (RECAP)',
			'date_from'	=> $date_from,
			'date_till'	=> $date_till,
			'user_fullname'	=> $user_fullname
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($date_from) OR empty($date_till)){
			die('Purchase Not Found!');
		}else{
				
			if(empty($date_from)){ $date_from = date('Y-m-d'); }
			if(empty($date_till)){ $date_till = date('Y-m-d'); }
			
			$mktime_dari = strtotime($date_from);
			$mktime_sampai = strtotime($date_till);
						
			$qdate_from = date("Y-m-d",strtotime($date_from));
			$qdate_till = date("Y-m-d",strtotime($date_till));
			
			$add_where = "(a.po_date >= '".$qdate_from."' AND a.po_date <= '".$qdate_till."')";
			
			$this->db->select("a.*, b.supplier_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix.'supplier as b','b.id = a.supplier_id','LEFT');
			$this->db->where("a.po_status IN ('done','progress')");
			$this->db->where("a.is_deleted", 0);
			$this->db->where($add_where);
			$this->db->order_by("po_date","ASC");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['report_data'] = $get_dt->result_array();				
			}
						
			$all_po_id = array();
			$all_po_id_date = array();
			$newData = array();
			if(!empty($data_post['report_data'])){
				foreach ($data_post['report_data'] as $s){
					$s['created_date'] = date("d-m-Y H:i",strtotime($s['created']));					
					$s['po_date'] = date("d-m-Y",strtotime($s['po_date']));
					
					if(!in_array($s['id'], $all_po_id)){
						$all_po_id[] = $s['id'];
					}		
										
					$s['po_discount_text'] = priceFormat($s['po_discount']);
					$s['po_tax_text'] = priceFormat($s['po_tax']);
					$s['po_total_price_text'] = priceFormat($s['po_total_price']);
										
					$s['payment_note'] = ucfirst($s['po_payment']);

					$s['po_total_price_cash'] = 0;
					$s['po_total_price_credit'] = 0;
					if($s['po_payment'] == 'cash'){
						$s['po_total_price_cash'] = $s['po_total_price'];
					}else{
						$s['po_total_price_credit'] = $s['po_total_price'];
					}

					$s['po_total_price_cash_text'] = priceFormat($s['po_total_price_cash']);
					$s['po_total_price_credit_text'] = priceFormat($s['po_total_price_credit']);
					
					if(empty($newData[$s['po_date']])){
						$newData[$s['po_date']] = array(
							'date'			=> $s['po_date'],
							'total_po'		=> 0,
							'total_item'	=> 0,
							'total_qty'		=> 0,
							'total_discount'=> 0,
							'total_tax'		=> 0,
							'total_cash'	=> 0,
							'total_credit'	=> 0	
						);
					}
					
					$newData[$s['po_date']]['total_po'] += 1;
					$newData[$s['po_date']]['total_discount'] += $s['po_discount'];
					$newData[$s['po_date']]['total_tax'] += $s['po_tax'];
					$newData[$s['po_date']]['total_cash'] += $s['po_total_price_cash'];
					$newData[$s['po_date']]['total_credit'] += $s['po_total_price_credit'];
					//array_push($newData, $s);
					
					if(empty($all_po_id_date[$s['id']])){
						$all_po_id_date[$s['id']] = $s['po_date'];
					}
					
				}
			}
						
			//po_detail
			$data_item_po = array();
			if(!empty($all_po_id)){
				$all_po_id_txt = implode(",", $all_po_id);
				$this->db->select("po_detail_qty as total_qty, item_id, po_id");
				$this->db->from($this->table2);
				$this->db->where("po_id IN (".$all_po_id_txt.")");
				$get_det = $this->db->get();
				if($get_det->num_rows() > 0){										
					foreach($get_det->result_array() as $dt){
						if(!empty($all_po_id_date[$dt['po_id']])){
							$getDate = $all_po_id_date[$dt['po_id']];
							
							$newData[$getDate]['total_qty'] += $dt['total_qty'];							

							if(empty($data_item_po[$dt['po_id']])){
								$data_item_po[$dt['po_id']] = array();
							}
						
							if(!in_array($dt['item_id'], $data_item_po[$dt['po_id']])){
								$data_item_po[$dt['po_id']][] = $dt['item_id'];
								$newData[$getDate]['total_item'] += 1;
							}
							
						}
					}
				}
			}
			
			$data_post['report_data'] = $newData;
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		$useview = 'print_reportPurchaseRecap';
		if($do == 'excel'){
			$useview = 'excel_reportPurchaseRecap';
		}
				
		$this->load->view('../../purchase/views/'.$useview, $data_post);	
	}
	

}