<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class KontrabonAP extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix3');
		$this->load->model('model_kontrabonap', 'm');
		$this->load->model('model_kontrabonapdetail', 'm2');
	}	
	
	public function gridData(){
		
		$this->table = $this->prefix.'kontrabon';
		
		$sortAlias = array(
			'kb_status_text'	=> 'kb_status',
			'total_tagihan_show'	=> 'total_tagihan'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										//array($this->prefix.'jurnal_header as b','b.id = a.jurnal_id','LEFT'),
										//array($this->prefix.'autoposting as c','c.id = a.autoposting_id','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted = 0'),
			'order'			=> array('a.id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$status = $this->input->post('status');
		$skip_date = $this->input->post('skip_date');
		
		//FILTER
		$date_from = $this->input->post('date_from');
		$date_till = $this->input->post('date_till');
		$keywords = $this->input->post('keywords');
		if(!empty($keywords)){
			$searching = $keywords;
		}
		
		if(empty($skip_date)){
			if(empty($date_from) AND empty($date_till)){
				$skip_date = true;
			}
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
							
				$qdate_from = date("Y-m-d 00:00:00",strtotime($date_from));
				$qdate_till = date("Y-m-d 23:59:59",strtotime($date_till));
				
				$params['where'][] = "(a.created >= '".$qdate_from."' AND a.created <= '".$qdate_till."')";
						
			}
		}
		
		if(!empty($is_dropdown)){
			$params['order'] = array('a.kb_no' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(a.kb_no LIKE '%".$searching."%' OR a.kb_name LIKE '%".$searching."%')";
		}		
		if(!empty($status)){
			$params['where'][] = "a.kb_status = '".$status."'";
		}
		
		//$params['where'][] = "a.is_deleted = 0";
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		
  		$newData = array();	
		
		if(!empty($get_data['data'])){
			
			$all_kb_id = array();
			foreach ($get_data['data'] as $s){
				$all_kb_id[] = $s['id'];
			}
			
			$used_kb_id = array();
			if(!empty($all_kb_id)){
				$this->table_pelunasan_ap = $this->prefix.'pelunasan_ap';	
				$this->db->select('kb_id');
				$this->db->from($this->table_pelunasan_ap);
				$this->db->where("kb_id IN ('".implode(",", $all_kb_id)."')");
				$get_dt = $this->db->get();
				if($get_dt->num_rows() > 0){
					foreach($get_dt->result() as $dt){
						if(!in_array($dt->kb_id, $used_kb_id)){
							$used_kb_id[] = $dt->kb_id;
						}
					}
				}
			}
			
			foreach ($get_data['data'] as $s){
				
				if($s['kb_status'] == 'progress'){
					$s['kb_status_text'] = '<span style="color:blue;">Progress</span>';
					
					if(in_array($s['id'],$used_kb_id)){
						$s['kb_status_text'] = '<span style="color:green;">Used</span>';
					}
					
				}else 
				if($s['kb_status'] == 'done'){
					$s['kb_status_text'] = '<span style="color:red;">Done</span>';
				}
				
				$s['created'] = date("d-m-Y",strtotime($s['created']));
				$s['total_tagihan_show'] = 'Rp. '.priceFormat($s['total_tagihan']);
				$s['total_bayar_show'] = 'Rp. '.priceFormat($s['total_bayar']);
				
				$s['kb_name_supplier'] = $s['kb_name'];
				if(!empty($s['supplier_id'])){
					$s['kb_name_supplier'] = $s['kb_name']." (Supplier)";
				}
				
				if(empty($s['tanggal_jatuh_tempo']) OR $s['tanggal_jatuh_tempo'] == '0000-00-00'){
					$s['tanggal_jatuh_tempo'] = '-';
				}else{
					$s['tanggal_jatuh_tempo'] = date("d-m-Y",strtotime($s['tanggal_jatuh_tempo']));
				}
				
				
				$s['status_pelunasan'] = '<span style="color:red;">Belum Lunas</span>';
				if($s['total_bayar'] >= $s['total_tagihan']){
					$s['status_pelunasan'] = '<span style="color:green;">Lunas</span>';
				}
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetail(){
		
		$this->table_kontrabon_detail = $this->prefix.'kontrabon_detail';
		$session_client_id = $this->session->userdata('client_id');
				
		if(empty($session_client_id)){
			die(json_encode(array('data' => array(), 'totalCount' => 0)));
		}
		
		// Default Parameter
		$params = array(
			'fields'		=> "a.*, b.ap_no, b.ap_name, b.ap_date, b.no_ref, b.supplier_id, b.ap_notes",
			'primary_key'	=> 'a.id',
			'table'			=> $this->table_kontrabon_detail.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'account_payable as b','b.id = a.ap_id','LEFT')
									) 
								),
			'order'			=> array('a.id' => 'DESC'),
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$ap_id = $this->input->post('ap_id');
		$kb_id = $this->input->post('kb_id');
		
		if(!empty($is_dropdown)){
			$params['order'] = array('b.ap_no' => 'ASC');
		}
		if(!empty($searching)){
			$params['where'][] = "(b.ap_no LIKE '%".$searching."%' OR b.ap_name LIKE '%".$searching."%')";
		}
		if(!empty($ap_id)){
			$params['where'][] = array('a.ap_id' => $ap_id);
		}
		if(!empty($kb_id)){
			$params['where'][] = array('a.kb_id' => $kb_id);
		}
		
		//get data -> data, totalCount
		$get_data = $this->m2->find_all($params);
		  		
		$newData = array();
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['total_tagihan_show'] = 'Rp '.priceFormat($s['total_tagihan']);
				$s['total_bayar_show'] = 'Rp '.priceFormat($s['total_bayar']);
				
				$s['ap_date'] = date("d-m-Y",strtotime($s['ap_date']));
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	public function gridDataDetail_AP(){
		
		
		$this->prefix_pos = config_item('db_prefix2');
		$this->table = $this->prefix.'_kontrabon';
		$this->table_account_payable = $this->prefix.'account_payable';
		$this->table_supplier = $this->prefix_pos.'supplier';
		
		$get_opt = get_option_value(array("auto_add_supplier_ap"));
		
		$auto_add_supplier_ap = 0;
		if(!empty($get_opt['auto_add_supplier_ap'])){
			$auto_add_supplier_ap = 1;
		}
		
		$ap_name = $this->input->post('ap_name');
		$supplier_id = $this->input->post('supplier_id');
		$all_ap_no = $this->input->post('all_ap_no');
		
		$this->db->select("a.*, a.id as ap_id, b.supplier_name");
		$this->db->from($this->table_account_payable." as a");
		$this->db->join($this->table_supplier." as b", "b.id = a.supplier_id", "LEFT");
		$this->db->where("a.ap_used = 0");
		$this->db->where("a.ap_status = 'posting'");
		$this->db->where("a.is_deleted = 0");
		
		if(empty($all_ap_no)){
			
			$this->db->where("a.ap_name = '".$ap_name."'");
				
			if(!empty($supplier_id)){
				$this->db->where("a.supplier_id = '".$supplier_id."'");
			}
			
		}
		
		$get_ap = $this->db->get();
		
		
		$nama_supplier_id = array();
		$newData = array();
		if($get_ap->num_rows() > 0){
			foreach($get_ap->result() as $dt){
				
				if(empty($dt->supplier_id)){
					$dt->supplier_id = 0;
				}
				
				if(empty($all_ap_no)){
					
					if($auto_add_supplier_ap == 1){
						
						$dt->ap_name_supplier = $dt->ap_name." ".$dt->supplier_name;
							
						if(!empty($dt->supplier_name)){
							$dt->ap_name_supplier = $dt->supplier_name." (Supplier)";
						}
						
						$dt->total_tagihan_show = priceFormat($dt->total_tagihan);
						
						$dt->total_bayar = 0;
						$dt->total_bayar_show = priceFormat($dt->total_bayar);
						$dt->ap_date = date("d-m-Y",strtotime($dt->ap_date));
						
						$dt->id = 'new_'.$dt->id;
						
						$newData[] = $dt;
						
					}else{
						$nama_supplier_id_cek = $dt->ap_name." ".$dt->supplier_id;
						if(!in_array($nama_supplier_id_cek, $nama_supplier_id)){
							$nama_supplier_id[] = $nama_supplier_id_cek;
							$dt->ap_name_supplier = $dt->ap_name." ".$dt->supplier_name;
							
							if(!empty($dt->supplier_name)){
								$dt->ap_name_supplier = $dt->supplier_name." (Supplier)";
							}
							
							$dt->total_tagihan_show = priceFormat($dt->total_tagihan);
							
							$dt->total_bayar = 0;
							$dt->total_bayar_show = priceFormat($dt->total_bayar);
							$dt->ap_date = date("d-m-Y",strtotime($dt->ap_date));
							
							$dt->id = 'new_'.$dt->id;
							
							$newData[] = $dt;
						}
					}
					
					
					
				}else{
					
					$dt->ap_name_supplier = $dt->ap_name." ".$dt->supplier_name;
						
					if(!empty($dt->supplier_name)){
						$dt->ap_name_supplier = $dt->supplier_name." (Supplier)";
					}
					
					$dt->ap_no_name = $dt->ap_no." / ".$dt->ap_name_supplier;
					
					
					$dt->total_tagihan_show = priceFormat($dt->total_tagihan);
					
					$dt->total_bayar = 0;
					$dt->total_bayar_show = priceFormat($dt->total_bayar);
					$dt->ap_date = date("d-m-Y",strtotime($dt->ap_date));
					
					$dt->id = 'new_'.$dt->id;
					
					$newData[] = $dt;
					
				}
				
				
				
			}
		}
		
		$get_data = array();
		$get_data['data'] = $newData;
		$get_data['totalCount'] = count($newData);
		
      	die(json_encode($get_data));
	}
	
	public function save(){
		
		$this->prefix = config_item('db_prefix3');
		$this->table_account_payable = $this->prefix.'account_payable';	
		$this->table_kontrabon = $this->prefix.'kontrabon';		
		$session_user = $this->session->userdata('user_username');
		
		$r = array('success' => false,"info" => "Save Kontrabon Failed!");
		
		$id = $this->input->post('id');
		$kb_date = $this->input->post('kb_date');
		$tanggal_jatuh_tempo = $this->input->post('tanggal_jatuh_tempo');
		$kb_name = $this->input->post('kb_name');
		$kb_address = $this->input->post('kb_address');
		$kb_phone = $this->input->post('kb_phone');
		$kb_no = $this->input->post('kb_no');
		$supplier_id = $this->input->post('supplier_id');
		$kb_notes = $this->input->post('kb_notes');
		$kb_status = $this->input->post('kb_status');
		$total_tagihan = $this->input->post('total_tagihan');
		$total_bayar = $this->input->post('total_bayar');
		$created = $this->input->post('created');
		
		if(empty($kb_name)){
			$r = array('success' => false, "info" => "KB name cannot empty!");
			die(json_encode($r));
		}	
		
		if(empty($total_tagihan)){
			$r = array('success' => false, "info" => "Total Tagihan empty!");
			die(json_encode($r));
		}
		
		if(empty($total_bayar)){
			$total_bayar = 0;
		}
		
		//poDetail				
		$kbDetail = $this->input->post('kbDetail');
		$kbDetail = json_decode($kbDetail, true);
		if(!empty($kbDetail)){
			$total_ap = count($kbDetail);
		}
		
		$is_active = $this->input->post('is_active');
		if(empty($is_active)){
			$is_active = 0;
		}
			
		$r = '';
		if($this->input->post('form_type_kontrabonAP', true) == 'add')
		{
			
			$kb_no = $this->m->generate_kb_number();

			$var = array(
				'fields'	=>	array(
				    'kb_no'  		=> 	$kb_no,
				    'kb_date' 		=> 	$kb_date,
				    'tanggal_jatuh_tempo' 		=> 	$tanggal_jatuh_tempo,
				    'kb_name' 		=> 	$kb_name,
				    'kb_address' 	=> 	$kb_address,
				    'kb_phone' 		=> 	$kb_phone,
				    'supplier_id' 		=> 	$supplier_id,
				    'kb_status' 		=> 	$kb_status,
				    'kb_notes' 		=> 	$kb_notes,
				    'total_tagihan' => 	$total_tagihan,
				    'total_bayar' => 	$total_bayar,
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user,
					'is_active'	=>	$is_active
				),
				'table'		=>  $this->table_kontrabon
			);	
			
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
				$q = $this->m->add($var);
				$insert_id = $this->m->get_insert_id();
			$this->lib_trans->commit();			
			if($q)
			{  
				$r = array('success' => true, 'id' => $insert_id, 'kb_no'	=> '-', 'det_info' => array()); 		
				$q_det = $this->m2->kbDetail($kbDetail, $insert_id);
				if(!empty($q_det['dtAP']['kb_no'])){
					$r['kb_no'] = $q_det['dtAP']['kb_no'];
				}
				$r['det_info'] = $q_det;
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_kontrabonAP', true) == 'edit'){
			
			$var = array('fields'	=>	array(
					'kb_date'=> 	$kb_date,
					'tanggal_jatuh_tempo'=> 	$tanggal_jatuh_tempo,
					'kb_name'=> 	$kb_name,
				    'kb_address' 	=> 	$kb_address,
				    'kb_phone' 		=> 	$kb_phone,
					'supplier_id' 	=> 	$supplier_id,
					'kb_notes' 		=> 	$kb_notes,
					'total_tagihan' => 	$total_tagihan,
					'total_bayar' => 	$total_bayar,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				),
				'table'			=>  $this->table_kontrabon,
				'primary_key'	=>  'id'
			);
			
			$id = $this->input->post('id', true);
			
			//CEK OLD DATA
			$this->db->from($this->table_kontrabon);
			$this->db->where("id = '".$id."'");
			$get_dt = $this->db->get();	
			if($get_dt->num_rows() > 0){
				$old_data = $get_dt->row_array();
			}		
			
			if($old_data['kb_status'] == 'done'){
				$r = array('success' => false, 'info' => 'Cannot Update, Kontrabon Been Done!'); 
				die(json_encode($r));
			}
			
			$kb_used = $this->check_kb_used($id);
			if($kb_used == true){
				//$r = array('success' => false, 'info' => 'Tidak bisa diubah<br/>Kontrabon sedang digunakan pada pelunasan!'); 
				//die(json_encode($r));
				
				unset($var['fields']['total_tagihan']);
				unset($var['fields']['total_bayar']);
				
			}
			
			
			//UPDATE
			$this->lib_trans->begin();
				$update = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				$r = array('success' => true, 'id' => $id, 'det_info' => array());
				
				
				$is_status_done = false;
				
				//check data main if been take
				$this->db->from($this->table_kontrabon);
				$this->db->where("id IN ('".$id."')");
				$this->db->where("kb_status = 'done'");
				$get_dt = $this->db->get();
				if($get_dt->num_rows() > 0){
					//status is DONE!
					$is_status_done = true;
				}
				
				if($kb_used == true){
					$is_status_done = false;
				}
				
				if($is_status_done == false){
					$q_det = $this->m2->kbDetail($kbDetail, $id);
					$r['det_info'] = $q_det;
				}
				
				
			}  
			else
			{  
				$r = array('success' => false);
			}
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
		
		
	}
	
	public function closing_kontrabonAP()
	{
		
		$this->table_kontrabon = $this->prefix.'kontrabon';
		$this->table_kontrabon_detail = $this->prefix.'kontrabon_detail';
		$this->table_account_payable = $this->prefix.'account_payable';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//Get PO
		$this->db->select('*');
		$this->db->from($this->table_kontrabon);
		$this->db->where("id IN ('".$sql_Id."')");
		$get_kb = $this->db->get();
		
		//delete data
		$update_data = array(
			'kb_status'	=> 'done'
		);
		
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->update($this->table_kontrabon, $update_data);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
			
			$this->db->select('*');
			$this->db->from($this->table_kontrabon_detail);
			$this->db->where("kb_id IN ('".$sql_Id."')");
			$get_kb_det = $this->db->get();
			$ap_id = array();
			
			if($get_kb_det->num_rows() > 0){
				foreach($get_kb_det->result() as $dt){
					if($dt->kbd_status == 'paid'){
						if(!in_array($dt->ap_id, $ap_id)){
							$ap_id[] = $dt->ap_id;
						}
					}
				}
			}
			
			if(!empty($ap_id)){
				$ap_id_txt = implode(",", $ap_id);
				
				
				$update_data = array(
					'ap_status'	=> 'pembayaran'
				);
				$this->db->where("id IN ('".$ap_id_txt."')");
				$q = $this->db->update($this->table_account_payable, $update_data);
				
			}
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Closing Kontrabon Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function generate_kb_number(){
		$this->table = $this->prefix.'kontrabon';		

		$getDate = date("ym");
		
		$this->db->from($this->table);
		$this->db->where("kb_no LIKE 'KB".$getDate."%'");
		$this->db->order_by('id', 'DESC');
		$get_last = $this->db->get();
		if($get_last->num_rows() > 0){
			$data_kb = $get_last->row();
			$kb_no = str_replace("KB".$getDate,"", $data_kb->kb_no);
			$kb_no = str_replace("KB","", $kb_no);
						
			$kb_no = (int) $kb_no;			
		}else{
			$kb_no = 0;
		}
		
		$kb_no++;
		$length_no = strlen($kb_no);
		switch ($length_no) {
			case 3:
				$kb_no = $kb_no;
				break;
			case 2:
				$kb_no = '0'.$kb_no;
				break;
			case 1:
				$kb_no = '00'.$kb_no;
				break;
			default:
				$kb_no = '00'.$kb_no;
				break;
		}
				
		return 'KB'.$getDate.$kb_no;				
	}
	
	public function delete()
	{
		
		$this->table = $this->prefix.'kontrabon';
		$this->table2 = $this->prefix.'kontrabon_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		$kb_used = $this->check_kb_used($sql_Id);
		if($kb_used == true){
			$r = array('success' => false, 'info' => 'Cannot Delete, Status Kontrabon Used on Pelunasan!'); 
			die(json_encode($r));
		}
		
		//Get KB
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		$get_kb = $this->db->get();
		
		$data_kb = array();
		if($get_kb->num_rows() > 0){
			
			$data_kb = $get_kb->row();
			if($data_kb->kb_status == 'done'){
				$r = array('success' => false, 'info' => 'Status Kontrabon Been Paid!'); 
			}
			
		}else{
			$r = array('success' => false, 'info' => 'Data Not Found!'); 
		}
		
		if(empty($data_kb)){
			$r = array('success' => false, 'info' => 'Data Not Found!'); 
		}
		
		//get detail
		$this->db->select('*');
		$this->db->from($this->table2);
		$this->db->where("kb_id IN ('".$sql_Id."')");
		$get_data = $this->db->get();
		
		$all_ap_id = array();
		if($get_data->num_rows() > 0){
			
			foreach($get_data->result() as $det){
				if($det->kbd_status == 'paid'){
					$r = array('success' => false, 'info' => 'Status Detail been Paid!'); 
					die(json_encode($r));
				}
				
				if(!in_array($det->ap_id, $all_ap_id)){
					$all_ap_id[] = $det->ap_id;
				}
			}
			
		}
		
		
		//delete data
		$update_data = array(
			'kb_status'	=> 'cancel',
			'is_deleted'=> 1
		);
		
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->update($this->table, $update_data);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
			
			if(!empty($all_ap_id)){
				$all_ap_id_txt  = implode(", ", $all_ap_id);
				
				//Update AP
				$update_AP = array(
						'ap_status'  => 'posting',
						'ap_used'  => 0				
				);
				
				$this->lib_trans->begin();
					$this->db->where("id IN (".$all_ap_id_txt.")");
					$this->db->update($this->prefix.'account_payable', $update_AP);
				$this->lib_trans->commit();
				
			}
			
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Kontrabon Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function deleteDetail(){
		$this->table = $this->prefix.'kontrabon_detail';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//Get ap_id
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where("id IN ('".$sql_Id."')");
		$get_data = $this->db->get();
		
		$data_det = array();
		if($get_data->num_rows() > 0){
			
			$data_det = $get_data->row();
			if($data_det->kbd_status == 'paid'){
				$r = array('success' => false, 'info' => 'Status Detail been Paid!'); 
				die(json_encode($r));
			}
			
		}else{
			$r = array('success' => false, 'info' => 'Data Not Found!'); 
			die(json_encode($r));
		}
		
		if(empty($data_det)){
			$r = array('success' => false, 'info' => 'Data Not Found!'); 
			die(json_encode($r));
		}
		
		if(!empty($data_det->kb_id)){
			$kb_used = $this->check_kb_used($data_det->kb_id);
			if($kb_used == true){
				$r = array('success' => false, 'info' => 'Cannot Delete, Status Kontrabon Used on Pelunasan!'); 
				die(json_encode($r));
			}
		}
		
		//delete data
		$this->db->where("id IN ('".$sql_Id."')");
		$q = $this->db->delete($this->table);
		
		$r = '';
		if($q)  
        {  
			//Update AP
			$update_AP = array(
					'ap_status'  => 'posting',
					'ap_used'  => 0				
			);
			
			$this->lib_trans->begin();
				$this->db->where("id IN ('".$data_det->ap_id."')");
				$this->db->update($this->prefix.'account_payable', $update_AP);
			$this->lib_trans->commit();
			
			//Update detail calc
			$kb_total_tagihan = $this->update_total_tagihan($data_det->kb_id);
			
            $r = array('success' => true, 'ap_id' => $data_det->ap_id, 'kb_total_tagihan' => $kb_total_tagihan); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Kontrabon Detail Failed!'); 
        }
		die(json_encode($r));
	}
	
	public function check_kb_used($kb_id){
		
		if(!empty($kb_id)){
			$this->table_pelunasan_ap = $this->prefix.'pelunasan_ap';	
			$this->db->select('id');
			$this->db->from($this->table_pelunasan_ap);
			$this->db->where("kb_id IN ('".$kb_id."')");
			$get_tot = $this->db->get();
			if($get_tot->num_rows() > 0){
				return true;
			}else{
				return false;
			}
			
		}else{
			return false;
		}
		
	}
	
	public function update_total_tagihan($kb_id){
		
		$this->table_kontrabon = $this->prefix.'kontrabon';	
		$this->table_kontrabon_detail = $this->prefix.'kontrabon_detail';	
		
		$this->db->select('SUM(total_tagihan) as total_tagihan_all');
		$this->db->from($this->table_kontrabon_detail);
		$this->db->where('kb_id', $kb_id);
		$get_tot = $this->db->get();
		
		$total_tagihan_all = 0;
		if($get_tot->num_rows() > 0){
			$data_kb = $get_tot->row();
			$total_tagihan_all = $data_kb->total_tagihan_all;
		}
		
		//Update KB
		$update_KB = array(
			'total_tagihan'  => $total_tagihan_all				
		);
		
		$this->db->update($this->table_kontrabon, $update_KB, "id = ".$kb_id);
		
		return $total_tagihan_all;
	}
	
	public function printKontrabon(){
		$this->prefix_pos = config_item('db_prefix2');
		$this->table_kontrabon = $this->prefix.'kontrabon';	
		$this->table_kontrabon_detail = $this->prefix.'kontrabon_detail';	
		$this->table_account_payable = $this->prefix.'account_payable';	
		$this->table_supplier = $this->prefix_pos.'supplier';	
		$this->table_client  = config_item('db_prefix').'clients';
		
		$session_user = $this->session->userdata('user_username');					
		$user_fullname = $this->session->userdata('user_fullname');					
		$client_id = $this->session->userdata('client_id');					
		
		//get client
		$this->db->from($this->table_client);
		$this->db->where("id",$client_id);
		$get_client = $this->db->get();
		$dt_client = array();
		if($get_client->num_rows() > 0){
			$dt_client = $get_client->row_array();
		}
		
		if(empty($session_user)){
			die('User Session Expired, Please Re-Login!');
		}
		
		extract($_GET);
		
		$data_post = array(
			'do'	=> '',
			'po_data'	=> array(),
			'po_detail'	=> array(),
			'report_name'	=> 'KONTRA BON',
			'report_place_default'	=> '',
			'user_fullname'	=> $user_fullname,
			'client'	=> $dt_client
		);
		
		$get_opt = get_option_value(array('report_place_default'));
		if(!empty($get_opt['report_place_default'])){
			$data_post['report_place_default'] = $get_opt['report_place_default'];
		}
		
		if(empty($kontrabon_id)){
			die('Kontrabon Not Found!');
		}else{
			
			$this->db->select("a.*, b.supplier_name, b.supplier_address, b.supplier_phone, b.supplier_fax, b.supplier_email, b.supplier_contact_person");
			$this->db->from($this->table_kontrabon." as a");
			$this->db->join($this->table_supplier." as b","b.id = a.supplier_id", "LEFT");
			$this->db->where("a.id = '".$kontrabon_id."'");
			$this->db->where("a.is_deleted = 0");
			$get_dt = $this->db->get();
			if($get_dt->num_rows() > 0){
				$data_post['kb_data'] = $get_dt->row_array();
				
				//get detail
				$this->db->select("a.*, b.ap_no, b.ap_name, b.ap_date, b.no_ref, b.ap_notes");
				$this->db->from($this->table_kontrabon_detail." as a");
				$this->db->join($this->table_account_payable." as b","b.id = a.ap_id", "LEFT");
				$this->db->where("a.kb_id = '".$kontrabon_id."'");
				$this->db->where("a.is_deleted = 0");
				$get_det = $this->db->get();
				if($get_det->num_rows() > 0){
					$data_post['kb_detail'] = $get_det->result_array();
				}
				
			}else{
				die('Kontrabon Detail Not Found!');
			}
		}
		
		//DO-PRINT
		if(!empty($do)){
			$data_post['do'] = $do;
		}else{
			$do = '';
		}
		
		$useview = 'printKontrabon';
		if($do == 'excel'){
			$useview = 'excelKontrabon';
		}
		
		$this->load->view('../../account_payable/views/'.$useview, $data_post);
		
	}
}