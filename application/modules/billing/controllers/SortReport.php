<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class SortReport extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
	}
	
	public function sortData(){
		
		$tipe = $this->input->post_get('tipe');
		$recap = $this->input->post_get('recap');
		
		$sortValue = array("" 	=> "Semua");
		if($tipe == 'discount'){
			
			$sortValue = array(
				"payment_date#ASC" 	=> "Tanggal-Jam",
				"billing_no#ASC" 	=> "No Billing",
				"discount_notes#ASC"=> "Nama Diskon",
				"discount_type#ASC" => "Tipe Diskon",
				"qty_menu" 			=> "Qty Menu",
				"total_billing" 	=> "Total Billing",
				"discount_total"	=> "Diskon Item",
				"discount_perbilling"=> "Diskon Billing",
				"compliment_total" 	=> "Total Compliment",
				"net_sales_total" 	=> "Total Net Sales",
				"tax_total" 		=> "Total Tax",
				"service_total" 	=> "Total Service",
				"total_pembulatan" 	=> "Total Pembulatan",
				"grand_total" 		=> "Grand Total"
			);
			
			if(!empty($recap)){
				$sortValue = array(
					"discount_notes#ASC"=> "Nama Diskon",
					"qty_billing" 		=> "Qty Billing",
					"qty_menu" 			=> "Qty Menu",
					"total_billing" 	=> "Total Billing",
					"discount_total"	=> "Diskon Item",
					"discount_perbilling"=> "Diskon Billing",
					"compliment"		=> "Total Compliment",
					"net_sales_total" 	=> "Total Net Sales",
					"tax_total" 		=> "Total Tax",
					"service_total" 	=> "Total Service",
					"pembulatan" 		=> "Total Pembulatan",
					"grand_total" 		=> "Grand Total"
				);
			}
			
		}else
		if($tipe == 'profit'){
			$sortValue = array(
				"payment_date#ASC" 	=> "Tanggal-Jam",
				"billing_no#ASC" 	=> "No Billing",
				"total_hpp" 		=> "Total HPP",
				"total_billing" 	=> "Total Billing",
				"all_discount_total"	=> "Total Diskon",
				"compliment_total" 	=> "Total Compliment",
				"net_sales_total" 	=> "Total Net Sales",
				"total_profit" 		=> "Total Profit",
			);
			
			if(!empty($recap)){
				$sortValue = array(
					"payment_date#ASC"	=> "Tanggal-Jam",
					"qty_billing" 		=> "Qty Billing",
					"total_hpp" 		=> "Total HPP",
					"total_billing" 	=> "Total Billing",
					"all_discount_total"	=> "Total Diskon",
					"compliment_total" 	=> "Total Compliment",
					"net_sales_total" 	=> "Total Net Sales",
					"total_profit" 		=> "Total Profit",
				);
			}
		}else
		if($tipe == 'menu_profit'){
			$sortValue = array(
				"a-z#ASC" 			=> "Nama Menu (A-Z)",
				"code#ASC" 			=> "Code Menu",
				"qty" 				=> "Total Qty",
				"total_hpp" 		=> "Total HPP",
				"total_billing" 	=> "Total Billing",
				"all_discount_total"	=> "Total Diskon",
				"compliment_total" 	=> "Total Compliment",
				"net_sales_total" 	=> "Total Net Sales",
				"total_profit" 		=> "Total Profit",
			);
			
		}else
		if($tipe == 'menu'){
			$sortValue = array(
				"a-z#ASC" 			=> "Nama Menu (A-Z)",
				"code#ASC" 			=> "Code Menu",
				"qty" 				=> "Total Qty",
				"total_billing" 	=> "Total Billing",
				"discount_total"	=> "Diskon Item",
				"discount_perbilling"=> "Diskon Billing",
				"compliment_total"	=> "Total Compliment",
				"net_sales_total" 	=> "Total Net Sales",
				//"tax_total" 		=> "Total Tax",
				//"service_total" 	=> "Total Service",
				//"total_pembulatan" 	=> "Total Pembulatan",
				//"grand_total" 		=> "Grand Total",
			);
			
			if(!empty($recap)){
				$sortValue = array(
					"tanggal#ASC"	=> "Tanggal",
					"total_billing" 	=> "Total Billing",
					"all_discount_total"	=> "Total Diskon",
					"compliment_total" 	=> "Total Compliment",
					"net_sales_total" 	=> "Total Net Sales",
					"total_profit" 		=> "Total Profit",
				);
			}
			
		}else{
			$sortValue = array(
				"payment_date#ASC" 	=> "Tanggal-Jam",
				"billing_no#ASC" 	=> "No Billing",
				"total_billing" 	=> "Total Billing",
				"discount_total"	=> "Diskon Item",
				"discount_perbilling"=> "Diskon Billing",
				"compliment_total" 	=> "Total Compliment",
				"net_sales_total" 	=> "Total Net Sales",
				"tax_total" 		=> "Total Tax",
				"service_total" 	=> "Total Service",
				"total_pembulatan" 	=> "Total Pembulatan",
				"grand_total" 		=> "Grand Total",
				"total_dp" 			=> "Total DP",
				"payment_cash" 		=> "Payment-Cash",
				"payment_debit" 	=> "Payment-Debit",
				"payment_credit" 	=> "Payment-Credt",
				"payment_ar" 		=> "Payment-AR",
				"half_payment" 		=> "Half-Payment"
			);
			
			if(!empty($recap)){
				$sortValue = array(
					"payment_date#ASC"	=> "Tanggal-Jam",
					"qty_billing" 		=> "Qty Billing",
					"total_billing" 	=> "Total Billing",
					"discount_total"	=> "Diskon Item",
					"discount_perbilling"=> "Diskon Billing",
					"compliment_total"	=> "Total Compliment",
					"net_sales_total" 	=> "Total Net Sales",
					"tax_total" 		=> "Total Tax",
					"service_total" 	=> "Total Service",
					"total_pembulatan" 	=> "Total Pembulatan",
					"grand_total" 		=> "Grand Total",
					"total_dp" 			=> "Total DP",
					"payment_cash" 		=> "Payment-Cash",
					"payment_debit" 	=> "Payment-Debit",
					"payment_credit"	=> "Payment-Credt",
					"payment_ar" 		=> "Payment-AR",
					"half_payment" 		=> "Half-Payment"
				);
			}
		}
		
		$retValue = array();
		if(!empty($sortValue)){
			foreach($sortValue as $val => $name){
				
				$exp_val = explode("#", $val);
				$sort = 'DESC';
				if(!empty($exp_val[1])){
					if($exp_val[1] == 'ASC'){
						$sort = 'ASC';
					}
				}
				
				$retValue[] = array(
					'val'	=> $exp_val[0],
					'name'	=> $name,
					'sort'	=> $sort
				);
			}
		}
		
		die(json_encode($retValue));
		
	}
	
}