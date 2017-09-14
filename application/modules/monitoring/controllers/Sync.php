<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sync extends MY_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
	}

	public function index($call_tools = '')
	{
		$this->table_sync = $this->prefix.'closing';
			
		//get total data
		extract($_POST);
		
		if($reqdata == 'ready_to_sync'){
			//cari yang masuk hari ini
			if(empty($sync_date)){
				$sync_date = date("Y-m-d");
			}
			
			if(empty($limit)){
				$limit = 25;
			}
			
			if(empty($ordering)){
				$ordering = 'DESC';
			}
			
			if(empty($all_tipe)){
				if(empty($tipe)){
					//return array();
				}
			}else{
				
			}
			
			$data_closing_id = array();
			$data_closing_total = 0;
			$data_closing_detail = array();
			
			
			if(!empty($sync_task)){
				
				$table_name = $this->table_sync;
				switch($sync_task){
					case 1: $table_name = $this->prefix.'closing_sales';
							break;
					case 2: $table_name = $this->prefix.'closing_purchasing';
							break;
					case 3: $table_name = $this->prefix.'closing_inventory';
							break;
					case 4: $table_name = $this->prefix.'closing_accounting';
							break;
				}
				
				$this->db->select('a.*');
				$this->db->from($table_name.' as a');
				
			}else{
				
				$this->db->select('a.*');
				$this->db->from($this->table_sync.' as a');
				if(!empty($tipe)){
					$this->db->where("a.tipe = '".$tipe."'");
				}
				//$this->db->where("a.tanggal = '".$sync_date."'");
			}
			
			$this->db->order_by("a.id", $ordering);
			//$this->db->limit($limit);
			$get_dt = $this->db->get();
			
			$closing_data = array();
			$last_data = array();
			if($get_dt->num_rows() > 0){
				$closing_data = $get_dt->result();
				$last_data = $get_dt->row();
			}
			
			$ret_data = array();
			$ret_data['info'] = '';
			$ret_data['total'] = count($closing_data);
			
			$ret_data['last_id'] = 0;
			$ret_data['last_tanggal'] = '';
			
			if(!empty($last_data)){
				$ret_data['last_id'] = $last_data->id;
				$ret_data['last_tanggal'] = date("d-m-Y", strtotime($last_data->tanggal));
			}
			
			
			echo json_encode($ret_data);
			die();
		}
		
		if($reqdata == 'update_live_closing'){
			
			$add_id = array();
			$remove_id = array();
			$update_id = array();
			$data_closing = array();
			$data_closing_new = array();
			
			$table_name = $this->table_sync;
			switch($sync_task){
				case 1: $table_name = $this->prefix.'closing_sales';
						break;
				case 2: $table_name = $this->prefix.'closing_purchasing';
						break;
				case 3: $table_name = $this->prefix.'closing_inventory';
						break;
				case 4: $table_name = $this->prefix.'closing_accounting';
						break;
			}
			
			
			if($last_id_lokal == $last_id){
				
				if($total == $total_lokal){
					//asumsi sama dgn live
				}else{
					
					$this->db->select('a.*');
					$this->db->from($table_name.' as a');
					$this->db->where("a.id <= '".$last_id_lokal."'");
					$get_dt = $this->db->get();
					if($get_dt->num_rows() > 0){
						$data_closing = $get_dt->result();
					}
					
				}
				
			}else{
				
				$this->db->select('a.*');
				$this->db->from($table_name.' as a');
				$this->db->where("a.id <= '".$last_id_lokal."'");
				$get_dt = $this->db->get();
				if($get_dt->num_rows() > 0){
					$data_closing = $get_dt->result();
				}
				
				$this->db->select('a.*');
				$this->db->from($table_name.' as a');
				$this->db->where("a.id > '".$last_id_lokal."'");
				$get_dt = $this->db->get();
				if($get_dt->num_rows() > 0){
					$data_closing_new = $get_dt->result();
				}
				
			}
			
			//GET LIVE ID
			$post = array(
				'reqdata' => 'ready_to_sync', 
				'sync_date' => $sync_date, 
				'sync_task' => $sync_task
			);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $monUrl.'/sync/check');
			curl_setopt($ch, CURLOPT_POST,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			$result_check = curl_exec ($ch);
			curl_close ($ch);
			
			$result_check = json_decode($result_check, true);
			
			$liveId = array();
			if(empty($result_check['total'])){
				$result_check['total'] = 0;
			}
			
			if(!empty($result_check['liveId'])){
				$liveId = $result_check['liveId'];
				if(empty($liveId)){
					$liveId = array();
				}
			}
			
			$liveId_data = array();
			if(!empty($liveId)){
				$liveId_data = explode(",", $liveId);
			}
			
			$lokalId_data = array();
			
			if(!empty($data_closing)){
				foreach($data_closing as $dt){
					
					if(!in_array($dt->id, $lokalId_data)){
						$lokalId_data[] = $dt->id;
					}
					
					//NEW LIVE
					if(!in_array($dt->id, $liveId_data)){
						$add_id[] = (array) $dt;
					}else{
						
						//UPDATE LIVE
						$update_id[] = (array) $dt;
					}
					
				}
			}
			
			if(!empty($data_closing_new)){
				foreach($data_closing_new as $dt){
					
					if(!in_array($dt->id, $lokalId_data)){
						$lokalId_data[] = $dt->id;
					}
					
					//NEW LIVE
					if(!in_array($dt->id, $liveId_data)){
						$add_id[] = (array) $dt;
					}else{
						
						//UPDATE LIVE
						$update_id[] = (array) $dt;
					}
					
				}
			}
			
			
			foreach($liveId_data as $liveId){
				//REMOVE LIVE
				if(!in_array($liveId, $lokalId_data)){
					$remove_id[] = $liveId;
				}
			}
			
			
			$ret_data = array('success' => true);
			$post = array(
				'success' => true, 
				'sync_task' => $sync_task
			);
			
			$post['add_data'] = '';
			$post['update_data'] = '';
			$post['remove_data'] = '';
			
			if(!empty($add_id)){
				$post['add_data'] = json_encode($add_id);
			}
			if(!empty($update_id)){
				$post['update_data'] = json_encode($update_id);
			}
			if(!empty($remove_id)){
				$post['remove_data'] = json_encode($remove_id);
			}
			
			//echo '<pre>';
			//print_r($data_closing);
			//die();
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $monUrl.'/sync/liveupdate');
			curl_setopt($ch, CURLOPT_POST,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			$result = curl_exec ($ch);
			curl_close ($ch);
			
			//echo '<pre>';
			//print_r($post);
			//die();
			
			$ret_data['total_add'] = count($add_id);
			$ret_data['total_update'] = count($update_id);
			$ret_data['total_remove'] = count($remove_id);
			
			echo json_encode($ret_data);
			die();
		}
		
	}
	
}
