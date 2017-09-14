<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class HasilProduksi extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_hasilproduksi', 'm');
		$this->load->model('model_hasilproduksidetail', 'm2');
		$this->load->model('model_stock', 'stock');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'production';
		$this->table2 = $this->prefix.'production_detail';
		
		//is_active_text
		$sortAlias = array(
			//'is_active_text' => 'is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, d.storehouse_name as pr_to_name',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'storehouse as d','d.id = a.pr_to','LEFT')
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
		$pr_status = $this->input->post('pr_status');
		$not_cancel = $this->input->post('not_cancel');
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
				if(empty($date_till)){ $date_till = date('Y-m-td'); }
				
				$mktime_dari = strtotime($date_from);
				$mktime_sampai = strtotime($date_till);
							
				$qdate_from = date("Y-m-d",strtotime($date_from));
				$qdate_till = date("Y-m-d",strtotime($date_till));
				
				$params['where'][] = "(a.pr_date >= '".$qdate_from."' AND a.pr_date <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.pr_number' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.pr_number LIKE '%".$searching."%' OR c.divisi_name LIKE '%".$searching."%')";
		}		
		//if(!empty($is_active)){
		//	$params['where'][] = "a.is_active = '".$is_active."'";
		//}
		if(!empty($not_cancel)){
			$params['where'][] = "a.pr_status != 'cancel'";
		}else{
			if(!empty($pr_status)){
				$params['where'][] = "a.pr_status = '".$pr_status."'";
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
		
		//get total
		/*$total_item = array();
		if(!empty($all_id)){
			$all_id_txt = implode(",", $all_id);
			$this->db->select("SUM(1) as total_item, pr_id");
			$this->db->from($this->table2);
			$this->db->where("pr_id IN (".$all_id_txt.")");
			$this->db->group_by("pr_id");
			$get_detail = $this->db->get();
			if($get_detail->num_rows() > 0){
				foreach($get_detail->result() as $dt){
					$total_item[$dt->pr_id] = $dt->total_item;
				}
			}
		}*/
		
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				//$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				if($s['pr_status'] == 'progress'){
					$s['pr_status_text'] = '<span style="color:blue;">Progress</span>';
				}else 
				if($s['pr_status'] == 'done'){
					$s['pr_status_text'] = '<span style="color:green;">Done</span>';
				}else{
					$s['pr_status_text'] = '<span style="color:red;">Cancel</span>';
				}
				
				$s['pr_status_old'] = $s['pr_status'];
				$s['pr_date_text'] = date("d-m-Y",strtotime($s['pr_date']));
				//$s['total_item'] = 0;
				//if(!empty($total_item[$s['id']])){
				//	$s['total_item'] = $total_item[$s['id']];
				//}
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetail()
	{
		
		$this->table = $this->prefix.'production_detail';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, b.item_name, b.item_code, b.item_image, c.unit_name",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'items as b','a.item_id = b.id','LEFT'),
										array($this->prefix.'unit as c','a.unit_id = c.id','LEFT')
									) 
								),
			'order'			=> array('a.id' => 'DESC'),
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$pr_id = $this->input->post('pr_id');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('b.item_name' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.item_name LIKE '%".$searching."%')";
		}
		if(!empty($pr_id)){
			$params['where'] = array('a.pr_id' => $pr_id);
		}
		
		//get data -> data, totalCount
		$get_data = $this->m2->find_all($params);
		
		$newData = array();	
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['item_hpp_show'] = 'Rp '.priceFormat($s['item_hpp']);
				$s['item_code_name'] = $s['item_code'].' / '.$s['item_name'];
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		  		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'production';	
		$this->table2 = $this->prefix.'production_detail';			
		$session_user = $this->session->userdata('user_username');
		
		$pr_date = $this->input->post('pr_date');
		$pr_memo = $this->input->post('pr_memo');
		
		$pr_to = $this->input->post('pr_to');
		$pr_status = $this->input->post('pr_status');
		
		if(empty($pr_to)){
			$r = array('success' => false, 'info' => 'Input Warehouse From');
			die(json_encode($r));
		}
		
		
		$total_item = 0;
		$total_production = 0;
		//hasilProduksiDetail				
		$hasilProduksiDetail = $this->input->post('hasilProduksiDetail');
		$hasilProduksiDetail = json_decode($hasilProduksiDetail, true);
		if(!empty($hasilProduksiDetail)){
			$total_item = count($hasilProduksiDetail);
			foreach($hasilProduksiDetail as $dtDet){
				$total_production += $dtDet['prd_qty'];
			}
		}	
		
		$get_pr_number = $this->generate_pr_number();
		
		if(empty($get_pr_number)){
			$r = array('success' => false);
			die(json_encode($r));
		}	
		
		if($pr_status == 'done'){
			
			if($total_production == 0){
				$r = array('success' => false, 'info' => 'Total item masuk = 0!'); 
				die(json_encode($r));
			}
			
		}
		
		$form_type = $this->input->post('form_type_hasilProduksi', true);
		
		$r = '';
		if($form_type == 'add')
		{
			/*
			$getItemData = $this->m2->getItem($hasilProduksiDetail, $pr_to);
			$getItemData['tipe'] = 'add';
			$getStock = $this->stock->get_item_stock($getItemData, $pr_date);
			$validStock = $this->stock->validStock($getItemData, $getStock);
			
			if(!empty($validStock['info'])){
				$r = array('success' => false, 'info'	=> '<br/>'.$validStock['info']);
				die(json_encode($r));
			}
			*/
			
			$var = array(
				'fields'	=>	array(
				    'pr_number'  	=> 	$get_pr_number,
				    'pr_date'  		=> 	$pr_date,
				    'pr_memo'  		=> 	$pr_memo,
				    'pr_to'  		=> 	$pr_to,
				    'pr_status'  	=> 	$pr_status,
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
				$q = $this->m->add($var);
				$insert_id = $this->m->get_insert_id();
			$this->lib_trans->commit();			
			if($q)
			{  
				$r = array('success' => true, 'id' => $insert_id, 'pr_number'	=> '-'); 		
				$return_data = $this->m2->hasilProduksiDetail($hasilProduksiDetail, $insert_id);
				if(!empty($return_data['dtRo']['pr_number'])){
					$r['pr_number'] = $return_data['dtRo']['pr_number'];
				}
				
				
				$do_update_stok = false;
				$do_update_rollback_stok = false;
				$warning_update_stok = false;
				
				if($pr_status == 'done'){
					$do_update_stok = true;
					
					if($pr_date != date("Y-m-d")){
						$warning_update_stok = true;
					}
					
				}
				
				
				if($pr_status == 'progress'){
					if($pr_date != date("Y-m-d")){
						$warning_update_stok = true;
					}
				}
				
				
				$update_stok = '';
				if($do_update_stok){
					$r['info'] = 'Update Stok';
					$update_stok = 'update';
				}
				
				if($do_update_rollback_stok){
					$r['info'] = 'Re-Update Stok';
					$update_stok = 'rollback';
				}
				
				
				
				if($do_update_stok OR $do_update_rollback_stok){
					
					//get/update ID -> $hasilProduksiDetail
					$item_id_prod = array();
					$this->db->from($this->prefix.'production_detail');
					$this->db->where("pr_id", $insert_id);
					$get_det = $this->db->get();
					if($get_det->num_rows() > 0){
						foreach($get_det->result_array() as $dt){
							$item_id_prod[$dt['item_id']] = $dt['id'];
						}
					}
					
					$hasilProduksiDetail_BU = $hasilProduksiDetail;
					$hasilProduksiDetail = array();
					foreach($hasilProduksiDetail_BU as $dtD){
						
						if(!empty($item_id_prod[$dtD['item_id']])){
							$dtD['id'] = $item_id_prod[$dtD['item_id']];
							$hasilProduksiDetail[] = $dtD;
						}
						
					}
					
					$return_data = $this->m2->hasilProduksiDetail($hasilProduksiDetail, $insert_id, $update_stok);
					$r['update_stock'] = $return_data['update_stock'];
					$post_params = array(
						'storehouse_item'	=> $return_data['update_stock']
					);
					
					$updateStock = $this->stock->update_stock_rekap($post_params);
					
				}
				
				
				
				if($warning_update_stok){
					$r['is_warning'] = 1;
					$r['info'] = 'Stock Been Changed (Realtime)<br/>Please Re-Generate/Fix Stock Transaction on List Stock Module!<br/>Re-generate/fix from: '.$pr_date;
				}
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
				$r = array('success' => false, 'info' => 'Production unidentified!'); 
				die(json_encode($r));	
			}
			
			/*
			$getItemData = $this->m2->getItem($hasilProduksiDetail, $pr_to, $id);
			$getItemData['tipe'] = 'edit';
			$getStock = $this->stock->get_item_stock($getItemData, $pr_date);
			$validStock = $this->stock->validStock($getItemData, $getStock);
			
			if(!empty($validStock['info'])){
				$r = array('success' => false, 'info'	=> '<br/>'.$validStock['info']);
				die(json_encode($r));
			}
			*/
			
			$var = array('fields'	=>	array(
					//'pr_number'  	=> 	$pr_number,
					'pr_date'  		=> 	$pr_date,
					'pr_memo'  		=> 	$pr_memo,
					'pr_to'  		=> 	$pr_to,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			
			
			$old_data = array();
			$do_update_stok = false;
			$do_update_rollback_stok = false;
			$warning_update_stok = false;
			
			//CEK OLD DATA
			$this->db->from($this->table);
			$this->db->where("id = '".$id."'");
			$get_dt = $this->db->get();
			
			if($get_dt->num_rows() > 0){
				$old_data = $get_dt->row_array();
			}
			
			
			
			if($old_data['pr_status'] != $pr_status){
				
				
				if($old_data['pr_status'] == 'progress' AND $pr_status == 'done'){
					$do_update_stok = true;
					
					if($total_production == 0){
						$r = array('success' => false, 'info' => 'Total di terima = 0!'); 
						die(json_encode($r));
					}
					
					if($pr_date != date("Y-m-d")){
						$warning_update_stok = true;
					}
					
				}
				
				
				if($old_data['pr_status'] == 'done' AND $pr_status == 'progress'){
					$do_update_rollback_stok = true;
					
					if($pr_date != date("Y-m-d")){
						$warning_update_stok = true;
					}
				}
				
				
				$var = array('fields'	=>	array(
						//'pr_number'	=> 	$pr_number,
						'pr_date'		=> 	$pr_date,
						'pr_memo'		=> 	$pr_memo,
						'pr_status'  	=> 	$pr_status,
						'updated'		=>	date('Y-m-d H:i:s'),
						'updatedby'		=>	$session_user
					),
					'table'			=>  $this->table,
					'primary_key'	=>  'id'
				);
				
			}else{
				
				if($old_data['pr_status'] == 'done'){
					//$r = array('success' => false, 'info' => 'Cannot Update Production Data been Done!'); 
					//die(json_encode($r));	
				}
				
			}
			
			
			$this->lib_trans->begin();
				$update = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				
				$r = array('success' => true, 'id' => $id);
				
				$update_stok = '';
				if($do_update_stok){
					$r['info'] = 'Update Stok';
					$update_stok = 'update';
				}
				
				if($do_update_rollback_stok){
					$r['info'] = 'Re-Update Stok';
					$update_stok = 'rollback';
				}
				
				$return_data = $this->m2->hasilProduksiDetail($hasilProduksiDetail, $id, $update_stok);
				
				if(!empty($return_data['update_stock'])){
					
					$r['update_stock'] = $return_data['update_stock'];
					$post_params = array(
						'storehouse_item'	=> $return_data['update_stock']
					);
					
					$updateStock = $this->stock->update_stock_rekap($post_params);
					
				}
				
				if($warning_update_stok){
					$r['is_warning'] = 1;
					$r['info'] = 'Stock Been Changed (Realtime)<br/>Please Re-Generate/Fix Stock Transaction on List Stock Module!<br/>Re-generate/fix from: '.$pr_date;
				}
			}  
			else
			{  
				$r = array('success' => false);
			}
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function delete()
	{
		
		$this->table = $this->prefix.'production';
		$this->table2 = $this->prefix.'production_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//check data main if been validated
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		$this->db->where("pr_status IN ('done')");
		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Cannot Delete Production, Status is been done!</br>Please Refresh List Production'); 
			die(json_encode($r));		
		}		
		
		//delete data
		$update_data = array(
			'pr_status'	=> 'cancel',
			'is_deleted' => 1
		);
		
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->update($this->table, $update_data);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
			
			//delete detail too
			$update_data2 = array(
				'prd_status'	=> 'cancel'
			);
			
			$this->db->where("pr_id IN ('".$sql_Id."')");
			$this->db->update($this->table2, $update_data2);
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Cancel Production Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function deleteDetail()
	{
		
		$this->table = $this->prefix.'production_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//check data main if been done
		$this->db->where("id IN ('".$sql_Id."')");
		$this->db->where("prd_status = 1");
		$get_dt = $this->db->get($this->table);
		if($get_dt->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Cannot Delete Data, Production been done!'); 
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
            $r = array('success' => false, 'info' => 'Delete Production Detail Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function get_total_qty($pr_id){
		$this->table = $this->prefix.'production_detail';	
		
		$this->db->select('SUM(prd_dikirim) as total_qty');
		$this->db->from($this->table);
		$this->db->where('pr_id', $pr_id);
		$get_tot = $this->db->get();
		
		$total_qty = 0;
		if($get_tot->num_rows() > 0){
			$data_ro = $get_tot->row();
			$total_qty = $data_ro->total_qty;
		}
		
		return $total_qty;
	}
	
	
	public function generate_pr_number(){
		$this->table = $this->prefix.'production';						
		
		$default_PR = "PR".date("ym");
		$this->db->from($this->table);
		$this->db->where("pr_number LIKE '".$default_PR."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_ro = $get_last->row();
			//$pr_number = $data_ro->pr_number;
			$pr_number = str_replace($default_PR,"", $data_ro->pr_number);
						
			$pr_number = (int) $pr_number;			
		}else{
			$pr_number = 0;
		}
		
		$pr_number++;
		$length_no = strlen($pr_number);
		switch ($length_no) {
			case 3:
				$pr_number = $pr_number;
				break;
			case 2:
				$pr_number = '0'.$pr_number;
				break;
			case 1:
				$pr_number = '00'.$pr_number;
				break;
			default:
				$pr_number = $pr_number;
				break;
		}
				
		return $default_PR.$pr_number;				
	}
	
	public function printHasilProduksiDetail(){
		
		$this->table  = $this->prefix.'production'; 
		$this->table2 = $this->prefix.'production_detail';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('User Session Expired, Please Re-Login!');
		}
		
		extract($_GET);
		
		$data_post = array(
			'do'	=> '',
			'pr_data'	=> array(),
			'production_detail'	=> array(),
			'report_place_default'	=> '',
			'user_fullname'	=> $user_fullname
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($pr_id)){
			die('Production Not Found!');
		}else{
			
			$this->db->select("a.*, c.storehouse_name as pr_to_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix."storehouse as c","c.id = a.pr_to","LEFT");
			
			$this->db->where("a.id = '".$pr_id."'");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['pr_data'] = $get_dt->row_array();
				
				//get detail
				$this->db->select("a.*, b.item_code, b.item_name, b.item_type, c.unit_code, c.unit_name");
				$this->db->from($this->table2." as a");
				$this->db->join($this->prefix."items as b","b.id = a.item_id","LEFT");
				$this->db->join($this->prefix."unit as c","c.id = a.unit_id","LEFT");
				$this->db->where("a.pr_id = '".$pr_id."'");
				$get_det = $this->db->get();
				if($get_det->num_rows() > 0){
					$data_post['pr_detail'] = $get_det->result_array();
				}
				
			}else{
				die('Production Not Found!');
			}
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}
		
		$this->load->view('../../inventory/views/printHasilProduksi', $data_post);
		
	}
}