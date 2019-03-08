<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class distributionItem extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_distributionitem', 'm');
		$this->load->model('model_distributionitemdetail', 'm2');
		$this->load->model('model_stock', 'stock');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'distribution';
		$this->table2 = $this->prefix.'distribution_detail';
		
		//is_active_text
		$sortAlias = array(
			//'is_active_text' => 'is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, c.divisi_name, d.storehouse_name as delivery_from_name, e.storehouse_name as delivery_to_name',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'divisi as c','c.id = a.divisi_id','LEFT'),
										array($this->prefix.'storehouse as d','d.id = a.delivery_from','LEFT'),
										array($this->prefix.'storehouse as e','e.id = a.delivery_to','LEFT')
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
		$dis_status = $this->input->post('dis_status');
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
				if(empty($date_till)){ $date_till = date('Y-m-t'); }
				
				$mktime_dari = strtotime($date_from);
				$mktime_sampai = strtotime($date_till);
							
				$qdate_from = date("Y-m-d",strtotime($date_from));
				$qdate_till = date("Y-m-d",strtotime($date_till));
				
				$params['where'][] = "(a.dis_date >= '".$qdate_from."' AND a.dis_date <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.dis_number' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.dis_number LIKE '%".$searching."%' OR c.divisi_name LIKE '%".$searching."%')";
		}		
		//if(!empty($is_active)){
		//	$params['where'][] = "a.is_active = '".$is_active."'";
		//}
		if(!empty($not_cancel)){
			$params['where'][] = "a.dis_status != 'cancel'";
		}else{
			if(!empty($dis_status)){
				$params['where'][] = "a.dis_status = '".$dis_status."'";
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
			$this->db->select("SUM(1) as total_item, dis_id");
			$this->db->from($this->table2);
			$this->db->where("dis_id IN (".$all_id_txt.")");
			$this->db->group_by("dis_id");
			$get_detail = $this->db->get();
			if($get_detail->num_rows() > 0){
				foreach($get_detail->result() as $dt){
					$total_item[$dt->dis_id] = $dt->total_item;
				}
			}
		}*/
		
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['is_retur_text'] = ($s['is_retur'] == '1') ? '<span style="color:green;">Retur</span>':'&nbsp;';
				
				if($s['dis_status'] == 'progress'){
					$s['dis_status_text'] = '<span style="color:blue;">Progress</span>';
				}else 
				if($s['dis_status'] == 'done'){
					$s['dis_status_text'] = '<span style="color:green;">Done</span>';
				}else{
					$s['dis_status_text'] = '<span style="color:red;">Cancel</span>';
				}
				
				$s['dis_status_old'] = $s['dis_status'];
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
		
		$this->table = $this->prefix.'distribution_detail';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, b.item_code, b.item_name, b.item_price, b.item_image, c.unit_name, a.disd_diterima as disd_diterima_before",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'items as b','a.item_id = b.id','LEFT'),
										array($this->prefix.'unit as c','a.unit_id = c.id','LEFT')
									) 
								),
			'order'			=> array('b.item_name' => 'DESC'),
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$dis_id = $this->input->post('dis_id');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('b.item_name' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.item_name LIKE '%".$searching."%')";
		}
		if(!empty($dis_id)){
			$params['where'] = array('a.dis_id' => $dis_id);
		}
		
		//get data -> data, totalCount
		$get_data = $this->m2->find_all($params);
		
		$newData = array();
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['item_code_name'] = $s['item_code'].' / '.$s['item_name'];
				array_push($newData, $s);
			}
			
			$get_data['data'] = $newData;
		}
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table = $this->prefix.'distribution';	
		$this->table2 = $this->prefix.'distribution_detail';			
		$session_user = $this->session->userdata('user_username');
		
		$dis_date = $this->input->post('dis_date');
		
		//CLOSING DATE
		$var_closing = array(
			'xdate'	=> $dis_date,
			'xtipe'	=> 'inventory'
		);
		$is_closing = is_closing($var_closing);
		if($is_closing){
			$r = array('success' => false, 'info' => 'Transaksi untuk Inventory pada tanggal: '.$dis_date.' sudah ditutup!'); 
			die(json_encode($r));
		}
		
		$dis_memo = $this->input->post('dis_memo');
		
		$dis_deliver = $this->input->post('dis_deliver');
		$dis_receiver = $this->input->post('dis_receiver');
		$delivery_from = $this->input->post('delivery_from');
		$delivery_to = $this->input->post('delivery_to');
		$dis_status = $this->input->post('dis_status');
		
		if(empty($delivery_from)){
			$r = array('success' => false, 'info' => 'Input Delivery From');
			die(json_encode($r));
		}	
		
		if(!empty($delivery_from)){
			$this->stock->cek_storehouse_access($delivery_from);
		}
		
		if(empty($delivery_to)){
			$r = array('success' => false, 'info' => 'Input Delivery To');
			die(json_encode($r));
		}	
		
		if(!empty($delivery_to)){
			$this->stock->cek_storehouse_access($delivery_to);
		}
		
		if($delivery_from == $delivery_to){
			$r = array('success' => false, 'info' => 'Input Delivery From cannot same with Delivery To');
			die(json_encode($r));
		}
		
		$is_retur = $this->input->post('is_retur');
		if(empty($is_retur)){
			$is_retur = 0;
		}
		
		$total_item = 0;
		$total_dikirim = 0;
		//distributionDetail				
		$distributionDetail = $this->input->post('distributionDetail');
		$distributionDetail = json_decode($distributionDetail, true);
		if(!empty($distributionDetail)){
			$total_item = count($distributionDetail);
			foreach($distributionDetail as $dtDet){
				$total_dikirim += $dtDet['disd_diterima'];
			}
		}	
		
		$get_dis_number = $this->generate_dis_number();
		
		if(empty($get_dis_number)){
			$r = array('success' => false);
			die(json_encode($r));
		}	
		
		$form_type = $this->input->post('form_type_distributionItem', true);
		$dis_type = $this->input->post('dis_type', true);
		
		if($dis_type == 'receive'){
			$form_type = $this->input->post('form_type_distributionReceive', true);
		}
		
		
		$get_opt = get_option_value(array('ds_count_stock','ds_auto_terima'));
		
		$ds_count_stock = 0;
		if(!empty($get_opt['ds_count_stock'])){
			$ds_count_stock = $get_opt['ds_count_stock'];
		}
		$ds_auto_terima = 0;
		if(!empty($get_opt['ds_auto_terima'])){
			$ds_auto_terima = $get_opt['ds_auto_terima'];
		}
		
		$r = '';
		if($form_type == 'add')
		{
			
			if($ds_count_stock == 1){
				$getItemData = $this->m2->getItem($distributionDetail, $delivery_from);
				$getItemData['tipe'] = 'add';
				$getStock = $this->stock->get_item_stock($getItemData, $dis_date);
				$validStock = $this->stock->validStock($getItemData, $getStock);
				
				if(!empty($validStock['info'])){
					$r = array('success' => false, 'info'	=> '<br/>'.$validStock['info']);
					die(json_encode($r));
				}
			}
			
			$var = array(
				'fields'	=>	array(
				    'dis_number'  	=> 	$get_dis_number,
				    'dis_date'  		=> 	$dis_date,
				    'dis_memo'  		=> 	$dis_memo,
				    'dis_deliver'  		=> 	$dis_deliver,
				    'dis_receiver'  	=> 	$dis_receiver,
				    'delivery_from'  	=> 	$delivery_from,
				    'delivery_to'  		=> 	$delivery_to,
				    'is_retur'  		=> 	$is_retur,
				    'dis_status'  	=> 	'progress',
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				),
				'table'		=>  $this->table
			);	
			
			$do_update_stok = false;
			$do_update_rollback_stok = false;
			$warning_update_stok = false;
		
			if($dis_status == 'done'){
				
				//cek warehouse
				$default_warehouse = $this->stock->get_primary_storehouse();
				if(empty($default_warehouse)){
					$r = array('success' => false, 'info' => 'Tidak ditemukan Gudang Utama/Primary! Silahkan setup data gudang'); 
					die(json_encode($r));
				}
				
				$do_update_stok = true;
				
				if($dis_date != date("Y-m-d")){
					$warning_update_stok = true;
				}
				
			}
			
			
			if($dis_status == 'progress'){
				if($dis_date != date("Y-m-d")){
					$warning_update_stok = true;
				}
			}
				
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
			$save_data = $this->m->add($var);
			$insert_id = $this->m->get_insert_id();
			$this->lib_trans->commit();			
			if($save_data)
			{  
				$id = $insert_id;
				
				/*
				$r = array('success' => true, 'id' => $insert_id, 'dis_number'	=> '-'); 

				if($ds_auto_terima == 1){
					
					if(!empty($distributionDetail)){
						$distributionDetail_new = array();
						foreach($distributionDetail as $dt){
							$dt['disd_diterima'] = $dt['disd_dikirim'];
							$distributionDetail_new[] = $dt;
						}
						$distributionDetail = $distributionDetail_new;
						
					}
				}
				
				$q_det = $this->m2->distributionDetail($distributionDetail, $insert_id);
				if(!empty($q_det['dtRo']['dis_number'])){
					$r['dis_number'] = $q_det['dtRo']['dis_number'];
				}
				*/
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
				$r = array('success' => false, 'info' => 'Distribution unidentified!'); 
				die(json_encode($r));	
			}
			
			$getItemData = $this->m2->getItem($distributionDetail, $delivery_from, $id);
			$getItemData['tipe'] = 'edit';
			$getStock = $this->stock->get_item_stock($getItemData, $dis_date);
			$validStock = $this->stock->validStock($getItemData, $getStock);
			//echo '<pre>';
			//print_r($validStock);
			//die();
			if(!empty($validStock['info']) AND $dis_status == 'done'){
				$r = array('success' => false, 'info'	=> '<br/>'.$validStock['info']);
				die(json_encode($r));
			}
			
			
			$var = array('fields'	=>	array(
				    //'dis_number'  	=> 	$dis_number,
				    'dis_date'  		=> 	$dis_date,
				    'dis_memo'  		=> 	$dis_memo,
				    'dis_deliver'  		=> 	$dis_deliver,
				    'dis_receiver'  	=> 	$dis_receiver,
				    'delivery_from'  	=> 	$delivery_from,
				    'delivery_to'  		=> 	$delivery_to,
				    'is_retur'  		=> 	$is_retur,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			$old_data = array();
			$do_update_stok = false;
			$warning_update_stok = false;
			$do_update_rollback_stok = false;
			if($dis_type == 'receive'){
				//CEK OLD DATA
				$this->db->from($this->table);
				$this->db->where("id = '".$id."'");
				$get_dt = $this->db->get();
				
				if($get_dt->num_rows() > 0){
					$old_data = $get_dt->row_array();
				}
				
				if($old_data['dis_status'] == 'progress' AND $dis_status == 'done'){
					$do_update_stok = true;
					
					if($total_dikirim == 0){
						$r = array('success' => false, 'info' => 'Total di terima = 0!'); 
						die(json_encode($r));
					}
					
					if($dis_date != date("Y-m-d")){
						$warning_update_stok = true;
						
					}
					
				}
				
				
				if($old_data['dis_status'] == 'done' AND $dis_status == 'progress'){
					$do_update_rollback_stok = true;
					
					if($dis_date != date("Y-m-d")){
						$warning_update_stok = true;
						
					}
				}
				
				
				$var = array('fields'	=>	array(
						//'dis_number'	=> 	$dis_number,
						'dis_date'		=> 	$dis_date,
						'dis_memo'		=> 	$dis_memo,
						'dis_receiver'	=> 	$dis_receiver,
						'dis_status'  	=> 	$dis_status,
						'updated'		=>	date('Y-m-d H:i:s'),
						'updatedby'		=>	$session_user
					),
					'table'			=>  $this->table,
					'primary_key'	=>  'id'
				);
				
			}else{
				//CEK OLD DATA
				$this->db->from($this->table);
				$this->db->where("id = '".$id."'");
				$get_dt = $this->db->get();
				
				if($get_dt->num_rows() > 0){
					$old_data = $get_dt->row_array();
				}
				
				if($old_data['dis_status'] == 'done'){
					//$r = array('success' => false, 'info' => 'Cannot Update Distribution Data been Done!'); 
					//die(json_encode($r));	
				}
			}
			
			
			
			$this->lib_trans->begin();
			$save_data = $this->m->save($var, $id);
			$this->lib_trans->commit();
			/*
			if($update)
			{  
				
				$r = array('success' => true, 'id' => $id, 'is_warning'	=> 0);
				
				$update_stok = '';
				if($do_update_stok){
					$r['info'] = 'Update Stok';
					$update_stok = 'update';
				}
				
				if($do_update_rollback_stok){
					$r['info'] = 'Re-Update Stok';
					$update_stok = 'rollback';
				}
				
				$return_data = $this->m2->distributionDetail($distributionDetail, $id, $update_stok);
				
				if(!empty($return_data['update_stock'])){
					
					$r['update_stock'] = $return_data['update_stock'];
					$post_params = array(
						'storehouse_item'	=> $return_data['update_stock']
					);
					
					$updateStock = $this->stock->update_stock_rekap($post_params);
					
				}
				
				if($warning_update_stok){
					$r['is_warning'] = 1;
					$r['info'] = 'Silahkan Re-Generate/Perbaiki Stok Transaksi pada List Stock Module!<br/>Perbaiki Stok dari: '.$dis_date;
				}
				
			}  
			else
			{  
				$r = array('success' => false);
			}
			*/
		}
		
		if($save_data)
		{ 
	
			$update_stok = '';
			if($do_update_stok){
				$r['info'] = 'Update Stok';
				$update_stok = 'update';
			}
			
			if($do_update_rollback_stok){
				$r['info'] = 'Re-Update Stok';
				$update_stok = 'rollback';
			}
			
			$r = array('success' => true, 'id' => $id);
			
			//from add
			//if($form_type == 'add')
			//{
				
				//if($do_update_stok OR $do_update_rollback_stok){
				$q_det = $this->m2->distributionDetail($distributionDetail, $id);
				
				$old_status = '';
				if(!empty($old_data['dis_status'])){
					$old_status = $old_data['dis_status'];
				}
				
				if($dis_status == 'done' AND $old_status != 'done'){
					
					//get/update ID -> $distributionDetail
					$item_id_prod = array();
					$this->db->from($this->prefix.'distribution_detail');
					$this->db->where("dis_id", $id);
					$get_det = $this->db->get();
					if($get_det->num_rows() > 0){
						foreach($get_det->result_array() as $dt){
							$item_id_prod[$dt['item_id']] = $dt['id'];
						}
					}
					
					//$update_stok = 'update_add';
					$update_stok = 'update';
					
					$distributionDetail_BU = $distributionDetail;
					$distributionDetail = array();
					foreach($distributionDetail_BU as $dtD){
						
						if(!empty($item_id_prod[$dtD['item_id']])){
							$dtD['id'] = $item_id_prod[$dtD['item_id']];
							$distributionDetail[] = $dtD;
						}
						
					}
					
					$r['distributionDetail_done'] = $distributionDetail;
				}
				
				
			//}
				
			$q_det = $this->m2->distributionDetail($distributionDetail, $id, $update_stok);
			if($q_det == false){
				$r = array('success' => false, 'info' => 'Input Detail Distribution Failed!'); 
				die(json_encode($r));
			}
			
			$r['det_info'] = $q_det;
			
			if($warning_update_stok){
				$r['is_warning'] = 1;
				$r['info'] = 'Silahkan Re-Generate/Perbaiki Stok Transaksi pada List Stock Module!<br/>Perbaiki Stok dari: '.$dis_date;
			}
			
			if(!empty($q_det['dtDistibution']['dis_number'])){
				$r['dis_number'] = $q_det['dtDistibution']['dis_number'];
			}
			
			if(!empty($q_det['update_stock'])){
				
				$post_params = array(
					'storehouse_item'	=> $q_det['update_stock']
				);
				
				$updateStock = $this->stock->update_stock_rekap($post_params);
				
			}
			
			//$updateAR = $this->account_receivable->set_account_receivable_usageWaste($id);
			//$r['success'] = false;
		}  
		else
		{  
			$r = array('success' => false);
		}
		
		
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function delete()
	{
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table = $this->prefix.'distribution';
		$this->table2 = $this->prefix.'distribution_detail';
		
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
		$this->db->where("dis_status IN ('done')");
		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Distribution number been done!</br>Please Refresh List Distribution'); 
			die(json_encode($r));		
		}		
		
		//delete data
		$update_data = array(
			'dis_status'	=> 'cancel',
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
				'disd_status'	=> 'cancel'
			);
			
			$this->db->where("dis_id IN ('".$sql_Id."')");
			$this->db->update($this->table2, $update_data2);
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Cancel Distribution Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function deleteDetail()
	{
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table = $this->prefix.'distribution_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//check data main if been done
		$this->db->where("id IN ('".$sql_Id."')");
		$this->db->where("disd_status = 'done'");
		$get_dt = $this->db->get($this->table);
		if($get_dt->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Distribution number been done / used!'); 
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
            $r = array('success' => false, 'info' => 'Delete Distribution Detail Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function get_total_qty($dis_id){
		$this->table = $this->prefix.'distribution_detail';	
		
		$this->db->select('SUM(disd_dikirim) as total_qty');
		$this->db->from($this->table);
		$this->db->where('dis_id', $dis_id);
		$get_tot = $this->db->get();
		
		$total_qty = 0;
		if($get_tot->num_rows() > 0){
			$data_ro = $get_tot->row();
			$total_qty = $data_ro->total_qty;
		}
		
		return $total_qty;
	}
	
	
	public function generate_dis_number(){
		$this->table = $this->prefix.'distribution';						
		
		$default_DS = "DS".date("ym");
		$this->db->from($this->table);
		$this->db->where("dis_number LIKE '".$default_DS."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_ro = $get_last->row();
			//$dis_number = $data_ro->dis_number;
			$dis_number = str_replace($default_DS,"", $data_ro->dis_number);
						
			$dis_number = (int) $dis_number;			
		}else{
			$dis_number = 0;
		}
		
		$dis_number++;
		$length_no = strlen($dis_number);
		switch ($length_no) {
			case 3:
				$dis_number = $dis_number;
				break;
			case 2:
				$dis_number = '0'.$dis_number;
				break;
			case 1:
				$dis_number = '00'.$dis_number;
				break;
			default:
				$dis_number = $dis_number;
				break;
		}
				
		return $default_DS.$dis_number;				
	}
	
	public function printDistribution(){
		
		$this->table  = $this->prefix.'distribution'; 
		$this->table2 = $this->prefix.'distribution_detail';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		
		$data_post = array(
			'do'	=> '',
			'dis_data'	=> array(),
			'distribution_detail'	=> array(),
			'report_place_default'	=> '',
			'user_fullname'	=> $user_fullname
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($dis_id)){
			die('Distribution Not Found!');
		}else{
			
			$this->db->select("a.*, b.divisi_name, c.storehouse_name as delivery_from_name, d.storehouse_name as delivery_to_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix."divisi as b","b.id = a.divisi_id","LEFT");
			$this->db->join($this->prefix."storehouse as c","c.id = a.delivery_from","LEFT");
			$this->db->join($this->prefix."storehouse as d","d.id = a.delivery_to","LEFT");
			
			$this->db->where("a.id = '".$dis_id."'");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['dis_data'] = $get_dt->row_array();
				
				//get detail
				$this->db->select("a.*, b.item_code, b.item_name, b.item_type, c.unit_code, c.unit_name");
				$this->db->from($this->table2." as a");
				$this->db->join($this->prefix."items as b","b.id = a.item_id","LEFT");
				$this->db->join($this->prefix."unit as c","c.id = a.unit_id","LEFT");
				$this->db->where("a.dis_id = '".$dis_id."'");
				$this->db->order_by("b.item_name", "ASC");
				$get_det = $this->db->get();
				if($get_det->num_rows() > 0){
					$data_post['dis_detail'] = $get_det->result_array();
				}
				
			}else{
				die('Distribution Not Found!');
			}
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}
		
		$this->load->view('../../inventory/views/printDistribution', $data_post);
		
	}
}