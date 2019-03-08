<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class UsageWaste extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		$this->load->model('model_usagewaste', 'm');
		$this->load->model('model_usagewastedetail', 'm2');
		$this->load->model('model_stock', 'stock');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'usagewaste';
		$this->table2 = $this->prefix.'usagewaste_detail';
		
		//is_active_text
		$sortAlias = array(
			//'is_active_text' => 'is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, d.storehouse_name as uw_from_name',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'storehouse as d','d.id = a.uw_from','LEFT')
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
		$uw_status = $this->input->post('uw_status');
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
				
				$params['where'][] = "(a.uw_date >= '".$qdate_from."' AND a.uw_date <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.uw_number' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.uw_number LIKE '%".$searching."%' OR c.divisi_name LIKE '%".$searching."%')";
		}		
		//if(!empty($is_active)){
		//	$params['where'][] = "a.is_active = '".$is_active."'";
		//}
		if(!empty($not_cancel)){
			$params['where'][] = "a.uw_status != 'cancel'";
		}else{
			if(!empty($uw_status)){
				$params['where'][] = "a.uw_status = '".$uw_status."'";
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
			$this->db->select("SUM(1) as total_item, uw_id");
			$this->db->from($this->table2);
			$this->db->where("uw_id IN (".$all_id_txt.")");
			$this->db->group_by("uw_id");
			$get_detail = $this->db->get();
			if($get_detail->num_rows() > 0){
				foreach($get_detail->result() as $dt){
					$total_item[$dt->uw_id] = $dt->total_item;
				}
			}
		}*/
		
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				//$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				
				if($s['uw_status'] == 'progress'){
					$s['uw_status_text'] = '<span style="color:blue;">Progress</span>';
				}else 
				if($s['uw_status'] == 'done'){
					$s['uw_status_text'] = '<span style="color:green;">Done</span>';
				}else{
					$s['uw_status_text'] = '<span style="color:red;">Cancel</span>';
				}
				
				$s['uw_status_old'] = $s['uw_status'];
				$s['uw_date_text'] = date("d-m-Y",strtotime($s['uw_date']));
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
		
		$this->table = $this->prefix.'usagewaste_detail';
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
		$uw_id = $this->input->post('uw_id');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('b.item_name' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.item_name LIKE '%".$searching."%')";
		}
		if(!empty($uw_id)){
			$params['where'] = array('a.uw_id' => $uw_id);
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
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table = $this->prefix.'usagewaste';	
		$this->table2 = $this->prefix.'usagewaste_detail';			
		$session_user = $this->session->userdata('user_username');
		
		$uw_date = $this->input->post('uw_date');
		$uw_memo = $this->input->post('uw_memo');
		
		$uw_from = $this->input->post('uw_from');
		$uw_status = $this->input->post('uw_status');
		
		if(empty($uw_from)){
			$r = array('success' => false, 'info' => 'Input Warehouse From');
			die(json_encode($r));
		}
		
		if(!empty($uw_from)){
			$this->stock->cek_storehouse_access($uw_from);
		}
		
		$total_item = 0;
		$total_usagewaste = 0;
		//usageWasteDetail				
		$usageWasteDetail = $this->input->post('usageWasteDetail');
		$usageWasteDetail = json_decode($usageWasteDetail, true);
		if(!empty($usageWasteDetail)){
			$total_item = count($usageWasteDetail);
			foreach($usageWasteDetail as $dtDet){
				$total_usagewaste += $dtDet['uwd_qty'];
			}
		}	
		
		$get_uw_number = $this->generate_uw_number();
		
		if(empty($get_uw_number)){
			$r = array('success' => false);
			die(json_encode($r));
		}	
		
		if($uw_status == 'done'){
			
			if($total_usagewaste == 0){
				$r = array('success' => false, 'info' => 'Total item masuk = 0!'); 
				die(json_encode($r));
			}
			
		}
		
		$form_type = $this->input->post('form_type_usageWaste', true);
		
		$r = '';
		if($form_type == 'add')
		{
			if($uw_status == 'done'){
				$getItemData = $this->m2->getItem($usageWasteDetail, $uw_from);
				$getItemData['tipe'] = 'add';
				$getStock = $this->stock->get_item_stock($getItemData, $uw_date);
				$validStock = $this->stock->validStock($getItemData, $getStock);
				
				if(!empty($validStock['info'])){
					$r = array('success' => false, 'info'	=> '<br/>'.$validStock['info']);
					die(json_encode($r));
				}
			}
			
			$var = array(
				'fields'	=>	array(
				    'uw_number'  	=> 	$get_uw_number,
				    'uw_date'  		=> 	$uw_date,
				    'uw_memo'  		=> 	$uw_memo,
				    'uw_from'  		=> 	$uw_from,
				    'uw_status'  	=> 	$uw_status,
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
		
			if($uw_status == 'done'){
				
				//cek warehouse
				$default_warehouse = $this->stock->get_primary_storehouse();
				if(empty($default_warehouse)){
					$r = array('success' => false, 'info' => 'Tidak ditemukan Gudang Utama/Primary! Silahkan setup data gudang'); 
					die(json_encode($r));
				}
				
				$do_update_stok = true;
				
				if($uw_date != date("Y-m-d")){
					$warning_update_stok = true;
				}
				
			}
			
			
			if($uw_status == 'progress'){
				if($uw_date != date("Y-m-d")){
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
				$r = array('success' => true, 'id' => $insert_id, 'uw_number'	=> '-'); 		
				$return_data = $this->m2->usageWasteDetail($usageWasteDetail, $insert_id);
				if(!empty($return_data['dtRo']['uw_number'])){
					$r['uw_number'] = $return_data['dtRo']['uw_number'];
				}
				
				
				$do_update_stok = false;
				$do_update_rollback_stok = false;
				$warning_update_stok = false;
				
				if($uw_status == 'done'){
					$do_update_stok = true;
					
					if($uw_date != date("Y-m-d")){
						$warning_update_stok = true;
					}
					
				}
				
				
				if($uw_status == 'progress'){
					if($uw_date != date("Y-m-d")){
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
					
					//get/update ID -> $usageWasteDetail
					$item_id_prod = array();
					$this->db->from($this->prefix.'usagewaste_detail');
					$this->db->where("uw_id", $insert_id);
					$get_det = $this->db->get();
					if($get_det->num_rows() > 0){
						foreach($get_det->result_array() as $dt){
							$item_id_prod[$dt['item_id']] = $dt['id'];
						}
					}
					
					$usageWasteDetail_BU = $usageWasteDetail;
					$usageWasteDetail = array();
					foreach($usageWasteDetail_BU as $dtD){
						
						if(!empty($item_id_prod[$dtD['item_id']])){
							$dtD['id'] = $item_id_prod[$dtD['item_id']];
							$usageWasteDetail[] = $dtD;
						}
						
					}
					
					$return_data = $this->m2->usageWasteDetail($usageWasteDetail, $insert_id, $update_stok);
					$r['update_stock'] = $return_data['update_stock'];
					$post_params = array(
						'storehouse_item'	=> $return_data['update_stock']
					);
					
					$updateStock = $this->stock->update_stock_rekap($post_params);
					
				}
				
				
				
				if($warning_update_stok){
					$r['is_warning'] = 1;
					$r['info'] = 'Silahkan Re-Generate/Perbaiki Stok Transaksi pada List Stock Module!<br/>Perbaiki Stok dari: '.$uw_date;
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
				$r = array('success' => false, 'info' => 'Usage & Waste unidentified!'); 
				die(json_encode($r));	
			}
			
			if($uw_status == 'done'){
				$getItemData = $this->m2->getItem($usageWasteDetail, $uw_from, $id);
				$getItemData['tipe'] = 'edit';
				$getStock = $this->stock->get_item_stock($getItemData, $uw_date);
				$validStock = $this->stock->validStock($getItemData, $getStock);
				
				if(!empty($validStock['info'])){
					$r = array('success' => false, 'info'	=> '<br/>'.$validStock['info']);
					die(json_encode($r));
				}
			}
			
			$var = array('fields'	=>	array(
					//'uw_number'  	=> 	$uw_number,
					'uw_date'  		=> 	$uw_date,
					'uw_memo'  		=> 	$uw_memo,
					'uw_from'  		=> 	$uw_from,
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
			
			
			
			if($old_data['uw_status'] != $uw_status){
				
				
				if($old_data['uw_status'] == 'progress' AND $uw_status == 'done'){
					$do_update_stok = true;
					
					if($total_usagewaste == 0){
						$r = array('success' => false, 'info' => 'Total di terima = 0!'); 
						die(json_encode($r));
					}
					
					if($uw_date != date("Y-m-d")){
						$warning_update_stok = true;
					}
					
				}
				
				
				if($old_data['uw_status'] == 'done' AND $uw_status == 'progress'){
					$do_update_rollback_stok = true;
					
					if($uw_date != date("Y-m-d")){
						$warning_update_stok = true;
					}
				}
				
				
				$var = array('fields'	=>	array(
						//'uw_number'	=> 	$uw_number,
						'uw_date'		=> 	$uw_date,
						'uw_memo'		=> 	$uw_memo,
						'uw_status'  	=> 	$uw_status,
						'updated'		=>	date('Y-m-d H:i:s'),
						'updatedby'		=>	$session_user
					),
					'table'			=>  $this->table,
					'primary_key'	=>  'id'
				);
				
			}else{
				
				if($old_data['uw_status'] == 'done'){
					//$r = array('success' => false, 'info' => 'Cannot Update Usage & Waste Data been Done!'); 
					//die(json_encode($r));	
				}
				
			}
			
			
			$this->lib_trans->begin();
			$save_data = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
			/*if($update)
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
				
				$return_data = $this->m2->usageWasteDetail($usageWasteDetail, $id, $update_stok);
				
				if(!empty($return_data['update_stock'])){
					
					$r['update_stock'] = $return_data['update_stock'];
					$post_params = array(
						'storehouse_item'	=> $return_data['update_stock']
					);
					
					$updateStock = $this->stock->update_stock_rekap($post_params);
					
				}
				
				if($warning_update_stok){
					$r['is_warning'] = 1;
					$r['info'] = 'Silahkan Re-Generate/Perbaiki Stok Transaksi pada List Stock Module!<br/>Perbaiki Stok dari: '.$uw_date;
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
				$q_det = $this->m2->usageWasteDetail($usageWasteDetail, $id);
				
				$old_status = '';
				if(!empty($old_data['uw_status'])){
					$old_status = $old_data['uw_status'];
				}
				
				if($uw_status == 'done' AND $old_status != 'done'){
					
					//get/update ID -> $usageWasteDetail
					$item_id_prod = array();
					$this->db->from($this->prefix.'usagewaste_detail');
					$this->db->where("uw_id", $id);
					$get_det = $this->db->get();
					if($get_det->num_rows() > 0){
						foreach($get_det->result_array() as $dt){
							$item_id_prod[$dt['item_id']] = $dt['id'];
						}
					}
					
					//$update_stok = 'update_add';
					$update_stok = 'update';
					
					$usageWasteDetail_BU = $usageWasteDetail;
					$usageWasteDetail = array();
					foreach($usageWasteDetail_BU as $dtD){
						
						if(!empty($item_id_prod[$dtD['item_id']])){
							$dtD['id'] = $item_id_prod[$dtD['item_id']];
							$usageWasteDetail[] = $dtD;
						}
						
					}
					
					$r['usageWasteDetail_done'] = $usageWasteDetail;
				}
				
				
			//}
				
			$q_det = $this->m2->usageWasteDetail($usageWasteDetail, $id, $update_stok);
			if($q_det == false){
				$r = array('success' => false, 'info' => 'Input Detail Usage & Waste Failed!'); 
				die(json_encode($r));
			}
			
			$r['det_info'] = $q_det;
			
			if($warning_update_stok){
				$r['is_warning'] = 1;
				$r['info'] = 'Silahkan Re-Generate/Perbaiki Stok Transaksi pada List Stock Module!<br/>Perbaiki Stok dari: '.$uw_date;
			}
			
			if(!empty($q_det['dtUW']['uw_number'])){
				$r['uw_number'] = $q_det['dtUW']['uw_number'];
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
		
		$this->table = $this->prefix.'usagewaste';
		$this->table2 = $this->prefix.'usagewaste_detail';
		
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
		$this->db->where("uw_status IN ('done')");
		$get_dt = $this->db->get();
		if($get_dt->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Cannot Delete Usage & Waste, Status is been done!</br>Please Refresh List Usage & Waste'); 
			die(json_encode($r));		
		}		
		
		//delete data
		$update_data = array(
			'uw_status'	=> 'cancel',
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
				'uwd_status'	=> 'cancel'
			);
			
			$this->db->where("uw_id IN ('".$sql_Id."')");
			$this->db->update($this->table2, $update_data2);
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Cancel Usage & Waste Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function deleteDetail()
	{
		$get_opt = get_option_value(array("as_server_backup"));
		cek_server_backup($get_opt);
		
		$this->table = $this->prefix.'usagewaste_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//check data main if been done
		$this->db->where("id IN ('".$sql_Id."')");
		$this->db->where("uwd_status = 1");
		$get_dt = $this->db->get($this->table);
		if($get_dt->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Cannot Delete Data, Usage & Waste been done!'); 
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
            $r = array('success' => false, 'info' => 'Delete Usage & Waste Detail Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function get_total_qty($uw_id){
		$this->table = $this->prefix.'usagewaste_detail';	
		
		$this->db->select('SUM(uwd_dikirim) as total_qty');
		$this->db->from($this->table);
		$this->db->where('uw_id', $uw_id);
		$get_tot = $this->db->get();
		
		$total_qty = 0;
		if($get_tot->num_rows() > 0){
			$data_ro = $get_tot->row();
			$total_qty = $data_ro->total_qty;
		}
		
		return $total_qty;
	}
	
	
	public function generate_uw_number(){
		$this->table = $this->prefix.'usagewaste';						
		
		$default_UW = "UW".date("ym");
		$this->db->from($this->table);
		$this->db->where("uw_number LIKE '".$default_UW."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_ro = $get_last->row();
			//$uw_number = $data_ro->uw_number;
			$uw_number = str_replace($default_UW,"", $data_ro->uw_number);
						
			$uw_number = (int) $uw_number;			
		}else{
			$uw_number = 0;
		}
		
		$uw_number++;
		$length_no = strlen($uw_number);
		switch ($length_no) {
			case 3:
				$uw_number = $uw_number;
				break;
			case 2:
				$uw_number = '0'.$uw_number;
				break;
			case 1:
				$uw_number = '00'.$uw_number;
				break;
			default:
				$uw_number = $uw_number;
				break;
		}
				
		return $default_UW.$uw_number;				
	}
	
	public function printHasilProduksiDetail(){
		
		$this->table  = $this->prefix.'usagewaste'; 
		$this->table2 = $this->prefix.'usagewaste_detail';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		
		if(empty($session_user)){
			die('Sesi Login sudah habis, Silahkan Login ulang!');
		}
		
		extract($_GET);
		
		$data_post = array(
			'do'	=> '',
			'uw_data'	=> array(),
			'usagewaste_detail'	=> array(),
			'report_place_default'	=> '',
			'user_fullname'	=> $user_fullname
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($uw_id)){
			die('Usage & Waste Not Found!');
		}else{
			
			$this->db->select("a.*, c.storehouse_name as uw_from_name");
			$this->db->from($this->table." as a");
			$this->db->join($this->prefix."storehouse as c","c.id = a.uw_from","LEFT");
			
			$this->db->where("a.id = '".$uw_id."'");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['uw_data'] = $get_dt->row_array();
				
				//get detail
				$this->db->select("a.*, b.item_code, b.item_name, b.item_type, c.unit_code, c.unit_name");
				$this->db->from($this->table2." as a");
				$this->db->join($this->prefix."items as b","b.id = a.item_id","LEFT");
				$this->db->join($this->prefix."unit as c","c.id = a.unit_id","LEFT");
				$this->db->where("a.uw_id = '".$uw_id."'");
				$get_det = $this->db->get();
				if($get_det->num_rows() > 0){
					$data_post['uw_detail'] = $get_det->result_array();
				}
				
			}else{
				die('Usage & Waste Not Found!');
			}
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}
		
		$this->load->view('../../inventory/views/printHasilProduksi', $data_post);
		
	}
}