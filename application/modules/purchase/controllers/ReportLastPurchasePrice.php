<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ReportLastPurchasePrice extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_purchaseorder', 'm');
	}
	
	public function print_reportLastPurchasePrice(){
		
		$this->table = $this->prefix.'items';
		$this->table2 = $this->prefix.'item_category';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		
		if(empty($category)){ die(); }			
		
		if(empty($category_name)){
			$category_name = 'ALL';
		}
		
		
		$data_post = array(
			'do'	=> '',
			'report_data'	=> array(),
			'report_place_default'	=> '',
			'report_name'	=> 'LAST PURCHASE PRICE',
			'category'	=> $category,
			'category_name'	=> $category_name,
			'user_fullname'	=> $user_fullname
		);
		
		$get_opt = get_option_value(array('report_place_default','hide_empty_stock_on_report'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		if(!empty($get_opt['hide_empty_stock_on_report'])){
			$data_post['hide_empty_stock_on_report'] = $get_opt['hide_empty_stock_on_report'];
		}
		
		/*
		SELECT `a`.item_name, a.item_code, a.old_last_in, a.last_in, a.item_hpp,`b`.`unit_code` AS `satuan`, c2.po_number, c2.po_date
		FROM `pos_items` AS `a` 
		LEFT JOIN `pos_unit` AS `b` ON `b`.`id` = `a`.`unit_id` 
		LEFT JOIN `pos_po_detail` AS `c` ON `c`.`item_id` = `a`.`id` AND c.po_receive_qty > 0
		LEFT JOIN `pos_po` AS `c2` ON `c2`.`id` = `c`.`po_id` AND c2.po_status = 'done' AND c2.is_deleted = 0
		WHERE `a`.`is_deleted` = 0 
		GROUP BY a.id
		ORDER BY `c2`.`po_date` DESC

		SELECT `a`.item_name, a.item_code, a.old_last_in, a.last_in, a.item_hpp,`b`.`unit_code` AS `satuan`
		FROM `pos_items` AS `a` 
		LEFT JOIN `pos_unit` AS `b` ON `b`.`id` = `a`.`unit_id` 
		WHERE `a`.`is_deleted` = 0 
		GROUP BY a.id
		ORDER BY `a`.`item_name` DESC
		*/
		
		//ITEM
		$all_item = array();
		$all_item_id = array();
		$this->db->select("a.id,a.item_name, a.item_code, a.old_last_in, a.last_in, a.item_hpp,b.unit_code AS satuan, c2.po_number, c2.po_date");
		$this->db->from($this->prefix."items as a");
		$this->db->join($this->prefix.'unit as b','b.id = a.unit_id','LEFT');
		$this->db->join($this->prefix.'po_detail as c','c.item_id = a.id AND c.po_receive_qty > 0','LEFT');
		$this->db->join($this->prefix.'po as c2',"c2.id = c.po_id AND c2.po_status = 'done' AND c2.is_deleted = 0",'LEFT');
		if($category == -1){
			
		}else{
			$this->db->where('a.category_id', $category);
		}
		
		
		$this->db->where("a.is_deleted = 0");
		$this->db->group_by("a.id","ASC");
		$this->db->order_by("c2.po_date","DESC");
		$getItem = $this->db->get();
		
		if($getItem->num_rows() > 0){
			foreach($getItem->result_array() as $dtR){
				if(!in_array($dtR['id'], $all_item_id)){
					$all_item_id[] = $dtR['id'];
					$all_item[] = $dtR;
				}
			}
		}
		
		$data_post['report_data'] = $all_item;
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		$useview = 'print_reportLastPurchasePrice';
		if($do == 'excel'){
			$useview = 'excel_reportLastPurchasePrice';
		}
				
		$this->load->view('../../purchase/views/'.$useview, $data_post);	
	}
	

}