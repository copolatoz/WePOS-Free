<?php
class Model_stock extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->prefix_apps = config_item('db_prefix');
		$this->prefix = config_item('db_prefix2');
		$this->table_stock = $this->prefix.'stock';
		$this->table_stock_rekap = $this->prefix.'stock_rekap';
		$this->table_items = $this->prefix.'items';
		$this->table_item_category = $this->prefix.'item_category';
		$this->table_storehouse = $this->prefix.'storehouse';
		$this->table_storehouse_access = $this->prefix.'storehouse_users';
		$this->table_user = $this->prefix_apps.'users';
	}
	
	function get_primary_storehouse(){
		
		//GET PRIMARY HOUSE
		$storehouse_id = 0;
		$opt_value = array(
			'warehouse_primary'
		);
		$get_opt = get_option_value($opt_value);
		if(!empty($get_opt['warehouse_primary'])){
			$storehouse_id = $get_opt['warehouse_primary'];
		}
		
		$this->db->from($this->table_storehouse);
		$this->db->where("is_primary = 1");
		$get_primary_storehouse = $this->db->get();
		
		if(empty($storehouse_id)){
			if($get_primary_storehouse->num_rows() > 0){
				$storehouse_dt = $get_primary_storehouse->row();
				$storehouse_id = $storehouse_dt->id;
			}
		}else{
			if($get_primary_storehouse->num_rows() == 0){
				$storehouse_id = 0;
			}
		}
		
		return $storehouse_id;
		
	}
	
	function cek_storehouse_access($storehouse_id = 0){
		
		$session_user = $this->session->userdata('user_username');
		
		$return_access = false;
		if(!empty($storehouse_id) AND !empty($session_user)){
			$this->db->select("a.id, b.user_username");
			$this->db->from($this->table_storehouse_access." as a");
			$this->db->join($this->table_user." as b","b.id = a.user_id","LEFT");
			$this->db->where("a.storehouse_id = ".$storehouse_id);
			$this->db->where("b.user_username = '".$session_user."'");
			$cek_storehouse_access = $this->db->get();
			if($cek_storehouse_access->num_rows() > 0){
				$return_access = true;
			}
		}
		
		if($return_access == false){
			$r = array('success' => false, 'info' => 'User Tidak Mempunyai Akses Ke Gudang!');
			die(json_encode($r));
		}
		
		
	}
	
	function validStock($getItemData = array(), $getStock = array()){
		
		extract($getItemData);
		
		
		
		//$storehouse
		//$storehouse_item
		//$storehouse_item_qty
		//tipe
		//$getStock
		
		if(empty($storehouse)){
			$ret_data = array('info'	=> 'Warehouse Tidak dikenali!');
			return $ret_data;
		}
		
		if(empty($storehouse_item)){
			$ret_data = array('info'	=> 'Detail Tidak dikenali!');
			return $ret_data;
		}
		
		if(empty($storehouse_item_qty)){
			$ret_data = array('info'	=> 'Detail Qty Tidak dikenali!');
			return $ret_data;
		}
		
		//storehouse_item_qty_before --> edit
		
		if(empty($getStock)){
			$ret_data = array('info'	=> 'Data Stock Tidak dikenali!');
			return $ret_data;
		}
		
		if(empty($tipe)){
			$ret_data = array('info'	=> 'Tipe check stock Tidak dikenali!');
			return $ret_data;
		}
		
		//echo '<pre>';
		//print_r($storehouse_item_qty_before);
		//die();
		
		$ret_data = array('info'	=> '');
		$msg_stock_error = array();
		if(!empty($storehouse_item_qty)){
			
			foreach($storehouse_item_qty as $storehouse => $item){
				if(!empty($item)){
					foreach($item as $id => $qty){
						if($tipe == 'add'){
							if(!empty($getStock[$storehouse][$id])){
								$stok_awal = $getStock[$storehouse][$id]['total_qty_stok'];
								if($qty > $stok_awal){
									$msg_stock_error[] = "Stock ".$getStock[$storehouse][$id]['item_code_name']." = ".$stok_awal;
								}
							}
						}
						
						if($tipe == 'edit'){
							if(!empty($getStock[$storehouse][$id])){
								
								$stok_awal = $getStock[$storehouse][$id]['total_qty_stok'];
								if(!empty($storehouse_item_qty_before[$storehouse][$id])){
									$stok_awal += $storehouse_item_qty_before[$storehouse][$id];
								}
								
								if($qty > $stok_awal){
									$msg_stock_error[] = "Stock ".$getStock[$storehouse][$id]['item_code_name']." = ".$stok_awal;
								}
							}
						}
					}
				}
			}
			
			if(!empty($msg_stock_error)){
				$msg_stock_error_txt = implode("<br/>", $msg_stock_error);
				$ret_data = array('info'	=> $msg_stock_error_txt);
			}
			
		}
		
		return $ret_data;
			
	}
	
	function get_item_stock($params = array(), $date = ''){
		//$storehouse_item = array('storehouse1' => array(item1,item2,item3))
		//$storehouse_id = storehouse1,
		//$item_id = item1 -> require storehouse || all/general
		//$all
		
		extract($params);
		
		if(empty($date)){
			$date = date("Y-m-d");
		}
		
		$yesterday = date("Y-m-d", strtotime($date) - ONE_DAY_UNIX);
		
		$all_storehouse_id = array();
		$all_item_id = array();
		
		
		if(!empty($storehouse_id)){
			$all_storehouse_id[] = $storehouse_id;
		}
		
		if(!empty($item_id) AND !empty($storehouse_id)){
			$all_item_id[] = $item_id;
		}
		
		if(!empty($storehouse_item)){
			foreach($storehouse_item as $dt_storehouse => $dt_item){
				if(!in_array($dt_storehouse, $all_storehouse_id)){
					$all_storehouse_id[] = $dt_storehouse;
				}
				
				foreach($dt_item as $idItem){
					if(!in_array($idItem, $all_item_id)){
						$all_item_id[] = $idItem;
					}
				}
			}
		}else{
			
			if(!empty($item_id) AND !empty($storehouse_id)){
				$storehouse_item = array(
					$storehouse_id => array($item_id)
				);
			}
		}
		
		$all_item_id_txt = '';
		if(!empty($all_item_id)){
			$all_item_id_txt = implode(",", $all_item_id);
		}
		
		$all_items_unit = array();
		if(!empty($all_storehouse_id)){
			$all_storehouse_id_txt = implode(",", $all_storehouse_id);
			
			$storehouse_name = array();
			$this->db->select("a.*");
			$this->db->from($this->table_storehouse." as a");
			$this->db->where("a.id IN (".$all_storehouse_id_txt.")");
			$get_storehouse = $this->db->get();
			if($get_storehouse->num_rows() > 0){
				foreach($get_storehouse->result_array() as $dt){
					$storehouse_name[$dt['id']] = $dt['storehouse_name'];
				}
			}
			
			
			$storehouse_empty_today = array();
			$storehouse_notempty_today = array();
			//TODAY
			$today_rekap = array();
			$this->db->select("a.*");
			$this->db->from($this->table_stock_rekap." as a");
			$this->db->where("a.storehouse_id IN (".$all_storehouse_id_txt.")");
			$this->db->where("a.trx_date = '".$date."'");
			//all_item_id_txt
			if(!empty($all_item_id_txt)){
				$this->db->where("a.item_id IN (".$all_item_id_txt.")");
			}
			
			$get_stock_rekap = $this->db->get();
			if($get_stock_rekap->num_rows() > 0){
				foreach($get_stock_rekap->result_array() as $dt){
					
					if(empty($dt['total_stock'])){
						$dt['total_stock'] = 0;
					}
					if(empty($dt['total_stock_in'])){
						$dt['total_stock_in'] = 0;
					}
					if(empty($dt['total_stock_out'])){
						$dt['total_stock_out'] = 0;
					}
					if(empty($dt['total_stock_kemarin'])){
						$dt['total_stock_kemarin'] = 0;
					}
					
					if(empty($today_rekap[$dt['storehouse_id']])){
						$today_rekap[$dt['storehouse_id']] = array();
					}
					if(empty($today_rekap[$dt['storehouse_id']][$dt['item_id']])){
						$today_rekap[$dt['storehouse_id']][$dt['item_id']] = $dt;
					}
					
					if(!in_array($dt['storehouse_id'], $storehouse_notempty_today)){
						$storehouse_notempty_today[] = $dt['storehouse_id'];
					}
					
				}
				
				foreach($all_storehouse_id as $dt){
					if(!in_array($dt, $storehouse_notempty_today)){
						$storehouse_empty_today[] = $dt;
					}
				}
				
			}else{
				
				$storehouse_empty_today = $all_storehouse_id;
				
			}
			
			
			if(!empty($storehouse_empty_today)){
				
				//all storehouse
				$all_active_storehouse = array();
				$this->db->select("a.*");
				$this->db->from($this->table_storehouse." as a");
				$this->db->where('is_deleted = 0');
				$this->db->where('is_active = 1');
				$get_storehouse = $this->db->get();
				if($get_storehouse->num_rows() > 0){
					foreach($get_storehouse->result_array() as $dt){
						$all_active_storehouse[] = $dt['id'];
					}
				}
				
				//ALL ITEM - EMPTY STOREHOUSE
				//set all_storehouse_item_id_today
				$create_today_rekap = array();
				
				/*
				$this->db->from($this->table_items);
				$this->db->where('is_deleted = 0');
				$this->db->where('is_active = 1');
				$get_item = $this->db->get();
				if($get_item->num_rows() > 0){
					foreach ($get_item->result_array() as $s){
						foreach($all_active_storehouse as $dt){
							
							//if(in_array($dt, $storehouse_empty_today)){
								
								if(empty($create_today_rekap[$dt])){
									$create_today_rekap[$dt] = array();
								}
								
								$create_today_rekap[$dt][] = $s['id'];
								
							//}
							
							
						}
						
					}
				}*/
				
				//TODAY REKAP FROM TABLE STOCK
				$this->db->select("a.item_id, a.storehouse_id");
				$this->db->from($this->table_stock." as a");
				$this->db->join($this->table_items.' as b',"b.id = a.item_id");
				$this->db->where("a.storehouse_id IN (".$all_storehouse_id_txt.")");
				$this->db->where('b.is_deleted = 0');
				$this->db->where('b.is_active = 1');
				$this->db->group_by('a.item_id');
				$this->db->group_by('a.storehouse_id');
				$get_item = $this->db->get();
				if($get_item->num_rows() > 0){
					foreach ($get_item->result_array() as $s){
						foreach($all_active_storehouse as $dt){
							
							if($s['storehouse_id'] == $dt){
								
								if(empty($create_today_rekap[$dt])){
									$create_today_rekap[$dt] = array();
								}
								
								$create_today_rekap[$dt][] = $s['id'];
								
							}
							
							
						}
						
					}
				}
				
				
				$all_active_storehouse_txt = implode(",", $all_active_storehouse);
				
				
				//YESTERDAY 
				$item_yesterday = array();
				$sisa_stok_kemarin = array();
				$this->db->select("a.*");
				$this->db->from($this->table_stock_rekap." as a");
				$this->db->where("a.storehouse_id IN (".$all_active_storehouse_txt.")");
				
				//all_item_id_txt
				if(!empty($all_item_id_txt)){
					$this->db->where("a.item_id IN (".$all_item_id_txt.")");
				}
				
				$this->db->where("a.trx_date = '".$yesterday."'");
				$get_stock_rekap = $this->db->get();
				if($get_stock_rekap->num_rows() > 0){
					foreach($get_stock_rekap->result_array() as $dt){

						//if(!in_array($dt['item_id'], $item_yesterday)){
							//$item_yesterday[] =  $dt['item_id'];
							
							if(empty($sisa_stok_kemarin[$dt['storehouse_id']])){
								$sisa_stok_kemarin[$dt['storehouse_id']] = array();
							}
							
							if(empty($sisa_stok_kemarin[$dt['storehouse_id']][$dt['item_id']])){
								$sisa_stok_kemarin[$dt['storehouse_id']][$dt['item_id']] = 0;
							}
							
							$sisa_stok_kemarin[$dt['storehouse_id']][$dt['item_id']] = $dt['total_stock'];
						//}
						
					}
				}
				
				
				$update_stock_storehouse_item = array();
				//if(!empty($all_item_id)){
					//$all_item_id_txt = implode(",", $all_item_id);
				
					//UPDATE
					$this->db->select("a.*");
					$this->db->from($this->table_stock." as a");
					$this->db->where("a.storehouse_id IN (".$all_active_storehouse_txt.")");
					//$this->db->where("a.item_id IN (".$all_item_id_txt.")");
					
					//all_item_id_txt
					if(!empty($all_item_id_txt)){
						$this->db->where("a.item_id IN (".$all_item_id_txt.")");
					}
					
					$this->db->where("a.trx_date = '".$date."'");
					$get_trx = $this->db->get();
					if($get_trx->num_rows() > 0){
						foreach($get_trx->result_array() as $dtR){
							if(empty($update_stock_storehouse_item[$dtR['storehouse_id']])){
								$update_stock_storehouse_item[$dtR['storehouse_id']] = array();
							}
							
							if(empty($update_stock_storehouse_item[$dtR['storehouse_id']][$dtR['item_id']])){
								$update_stock_storehouse_item[$dtR['storehouse_id']][$dtR['item_id']] = array(
									'in'	=> 0,
									'out'	=> 0,
									'sto'	=> 0
								);
							}
							
							if($dtR['trx_type'] == 'in'){
								$update_stock_storehouse_item[$dtR['storehouse_id']][$dtR['item_id']]['in'] += $dtR['trx_qty'];
							}
							if($dtR['trx_type'] == 'out'){
								$update_stock_storehouse_item[$dtR['storehouse_id']][$dtR['item_id']]['out'] += $dtR['trx_qty'];
							}
							if($dtR['trx_type'] == 'sto'){
								$update_stock_storehouse_item[$dtR['storehouse_id']][$dtR['item_id']]['sto'] += $dtR['trx_qty'];
							}
							
						}
					}
				//}
				
				
				//PREPARE FOR CREATE TODAY PER UNIT, PER ITEM
				$dt_create_rekap = array();
				if(!empty($create_today_rekap)){
					foreach($create_today_rekap as $sId => $dtItem){
						
						//ITEMS / UNIT
						if(!empty($dtItem)){
							foreach($dtItem as $dtx){
								$get_total_stock_kemarin = 0;
								if(!empty($sisa_stok_kemarin[$sId][$dtx])){
									$get_total_stock_kemarin = $sisa_stok_kemarin[$sId][$dtx];
								}
								
								$get_total_stock_in = 0;
								$get_total_stock_out = 0;
								$get_total_stock_sto = 0;
								if(!empty($update_stock_storehouse_item[$sId][$dtx])){
									$get_total_stock_in = $update_stock_storehouse_item[$sId][$dtx]['in'];
									$get_total_stock_out = $update_stock_storehouse_item[$sId][$dtx]['out'];
									$get_total_stock_sto = $update_stock_storehouse_item[$sId][$dtx]['sto'];
								}
								
								$get_total_stock = ($get_total_stock_kemarin + $get_total_stock_in) - $get_total_stock_out;
								
								$dt_create_rekap[] = array(
									'storehouse_id'			=> $sId,
									'item_id'				=> $dtx,
									'total_stock'			=> $get_total_stock,
									'total_stock_in'		=> $get_total_stock_in,
									'total_stock_out'		=> $get_total_stock_out,
									'total_stock_kemarin'	=> $get_total_stock_kemarin,
									'trx_date'				=> $date,
								);
							}
						}
						
						
					}
				}
				
				
				//INSERT
				if(!empty($dt_create_rekap)){
					$this->db->insert_batch($this->table_stock_rekap, $dt_create_rekap);
					
					$this->db->select("a.*");
					$this->db->from($this->table_stock_rekap." as a");
					$this->db->where("a.storehouse_id IN (".$all_storehouse_id_txt.")");
					
					//all_item_id_txt
					if(!empty($all_item_id_txt)){
						$this->db->where("a.item_id IN (".$all_item_id_txt.")");
					}
					
					$this->db->where("a.trx_date = '".$date."'");
					$get_stock_rekap = $this->db->get();
					if($get_stock_rekap->num_rows() > 0){
						foreach($get_stock_rekap->result_array() as $dt){
							
							if(empty($today_rekap[$dt['storehouse_id']])){
								$today_rekap[$dt['storehouse_id']] = array();
							}
							if(empty($today_rekap[$dt['storehouse_id']][$dt['item_id']])){
								$today_rekap[$dt['storehouse_id']][$dt['item_id']] = $dt;
							}
							
						}
					}
				}
			}
			
			//echo $all_item_id_txt.'<pre>';
			//print_r($today_rekap);
			//die();
			
			//GET ITEMS -> table_items
			$this->db->select("a.*,
					b.unit_name as satuan,
					c.item_category_name");
			$this->db->from($this->table_items." as a");
			$this->db->join($this->prefix."unit as b","b.id = a.unit_id","LEFT");
			$this->db->join($this->table_item_category.' as c','c.id = a.category_id','LEFT');
			$this->db->where("a.is_active = 1");
			$this->db->where("a.is_deleted = 0");
			$get_all_items = $this->db->get();
			if($get_all_items->num_rows() > 0){
				foreach($get_all_items->result_array() as $dt){
					
					foreach($all_storehouse_id as $storehouse_id){
						
						if(empty($all_items_unit[$storehouse_id])){
							$all_items_unit[$storehouse_id] = array();
						}
						
						$get_ID = $dt['id'];
						$current_stok = 0;
						if(!empty($today_rekap[$storehouse_id][$get_ID])){
							$current_stok = $today_rekap[$storehouse_id][$get_ID]['total_stock'];
						}
						
						$dt['item_code_name'] = $dt['item_code'].'/'.$dt['item_name'];
						
						$dt['total_qty_stok'] = $current_stok;
						$dt['storehouse_name'] = '';
						
						if(!empty($storehouse_name[$storehouse_id])){
							$dt['storehouse_name'] = $storehouse_name[$storehouse_id];
						}
						
						if(in_array($dt['id'], $all_item_id)){
							$all_items_unit[$storehouse_id][$dt['id']] = $dt;
						}
						
						
					}
					
				}
			}
			
			//echo '<pre>';
			//print_r($all_item_id);
			//die();
			
			
		}
			
		
		return $all_items_unit;
		
	}
	
	function update_stock_rekap($params = array()){
		//$storehouse_item = array('storehouse1' => array(item1,item2,item3))
		//$storehouse_id = storehouse1,
		//$item_id = item1 -> require storehouse
		//$date
		extract($params);
		
		if(empty($date)){
			$date = date("Y-m-d");
		}
		
		$yesterday = date("Y-m-d", strtotime($date) - ONE_DAY_UNIX);
		//echo 'yesterday: '.$yesterday.' <br/>';
		
		$all_storehouse_id = array();
		$all_item_id = array();
		
		
		if(!empty($storehouse_id)){
			$all_storehouse_id[] = $storehouse_id;
		}
		
		if(!empty($item_id) AND !empty($storehouse_id)){
			$all_item_id[] = $item_id;
		}
		
		if(!empty($storehouse_item)){
			foreach($storehouse_item as $dt_storehouse => $dt_item){
				if(!in_array($dt_storehouse, $all_storehouse_id)){
					$all_storehouse_id[] = $dt_storehouse;
				}
				
				foreach($dt_item as $idItem){
					if(!in_array($idItem, $all_item_id)){
						$all_item_id[] = $idItem;
					}
				}
			}
		}else{
			
			if(!empty($item_id) AND !empty($storehouse_id)){
				$storehouse_item = array(
					$storehouse_id => array($item_id)
				);
			}
			
		}
		
		if(!empty($all_storehouse_id)){
			$all_storehouse_id_txt = implode(",", $all_storehouse_id);
			
			if(!empty($all_item_id)){
				$all_item_id_txt = implode(",", $all_item_id);
			}
			
			//echo '<pre>';
			//print_r($sisa_stok_kemarin);
			//die();
			
			//TODAY
			$get_storehouse_item_today = true;
			$all_storehouse_id_today = array();
			$all_storehouse_item_id_today = array();
			$update_today_rekap = array();
			$update_today_rekap_data = array();
			$create_today_rekap = array();
			$this->db->select("a.*");
			$this->db->from($this->table_stock_rekap." as a");
			$this->db->where("a.storehouse_id IN (".$all_storehouse_id_txt.")");
			
			if(!empty($all_item_id)){
				$this->db->where("a.item_id IN (".$all_item_id_txt.")");
			}
			
			$this->db->where("a.trx_date = '".$date."'");
			$get_stock_rekap = $this->db->get();
			if($get_stock_rekap->num_rows() > 0){
				foreach($get_stock_rekap->result_array() as $dt){
					
					if(!in_array($dt['storehouse_id'], $all_storehouse_id_today)){
						$all_storehouse_id_today[] = $dt['storehouse_id'];
					}
					if(empty($all_storehouse_item_id_today[$dt['storehouse_id']])){
						$all_storehouse_item_id_today[$dt['storehouse_id']] = array();
					}
					
					
					//ITEM SHOULD ON STORE
					if(in_array($dt['item_id'], $storehouse_item[$dt['storehouse_id']])){
					
						if(!in_array($dt['storehouse_id'], $all_storehouse_id)){
							
							if(empty($create_today_rekap[['storehouse_id']])){
								$create_today_rekap[$dt['storehouse_id']] = array();
							}
							
							if(!in_array($dt['item_id'], $create_today_rekap[$dt])){
								$create_today_rekap[$dt][] = $dt['item_id'];
							}
							
							//if(!in_array($dt['storehouse_id'], $create_today_rekap)){
							//	$create_today_rekap[$dt['storehouse_id']] = $dt['storehouse_id'];
							//}
							
						}else{
							
							
							if(!in_array($dt['id'], $update_today_rekap)){
							
								if(!in_array($dt['item_id'], $all_storehouse_item_id_today[$dt['storehouse_id']])){
									$all_storehouse_item_id_today[$dt['storehouse_id']][] = $dt['item_id'];
								}
								
								$update_today_rekap[] = $dt['id'];
								$update_today_rekap_data[] = array(
									'id'					=> $dt['id'],
									'storehouse_id'			=> $dt['storehouse_id'],
									'item_id'				=> $dt['item_id'],
									'total_stock'			=> 0,
									'total_stock_in'		=> 0,
									'total_stock_out'		=> 0,
									'total_stock_kemarin'	=> 0
								);
							}
						
						}
						
					}
				}
			}else{
				
				$create_today_rekap = $storehouse_item;
				$get_storehouse_item_today = false;
			}
			
			
			//echo 'get_storehouse_item_today='.$get_storehouse_item_today.'<pre>';
			//print_r($storehouse_item);
			//print_r($create_today_rekap);
			//die();
			//echo '<pre>';
			//print_r($update_today_rekap);
			//die();
			
			//CREATE NEW TODAY REKAP
			if($get_storehouse_item_today == false){
				
				//all storehouse
				$all_active_storehouse = array();
				$this->db->select("a.*");
				$this->db->from($this->table_storehouse." as a");
				$this->db->where('is_deleted = 0');
				$this->db->where('is_active = 1');
				$get_storehouse = $this->db->get();
				if($get_storehouse->num_rows() > 0){
					foreach($get_storehouse->result_array() as $dt){
						$all_active_storehouse[] = $dt['id'];
					}
				}
				
				$all_active_storehouse_txt = implode(",", $all_active_storehouse);
				//cek EMPTY STOREHOUSE
				$storehouse_empty_today = array();
				$storehouse_notempty_today = array();
				
				/*
				$this->db->select("DISTINCT a.storehouse_id");
				$this->db->from($this->table_stock_rekap." as a");
				//$this->db->where("a.storehouse_id IN (".$all_storehouse_id_txt.")");
				$this->db->where("a.trx_date = '".$date."'");
				$get_storehouse_today = $this->db->get();
				if($get_storehouse_today->num_rows() > 0){
					foreach($get_storehouse_today->result() as $dt){
						$storehouse_notempty_today[] = $dt;
					}
					
					foreach($all_storehouse_id as $dt){
						if(!in_array($dt, $storehouse_notempty_today)){
							$storehouse_empty_today[] = $dt;
						}
					}
					
				}else{
					$storehouse_empty_today = $all_storehouse_id;
				}*/
				
				
				//ALL ITEM - EMPTY STOREHOUSE
				//set all_storehouse_item_id_today
				//$create_today_rekap = array();
				
				/*$this->db->from($this->table_items);
				$this->db->where('is_deleted = 0');
				$this->db->where('is_active = 1');
				$get_item = $this->db->get();
				if($get_item->num_rows() > 0){
					foreach ($get_item->result_array() as $s){
						foreach($all_active_storehouse as $dt){
							
							//if(in_array($dt, $storehouse_empty_today)){
								
								if(empty($create_today_rekap[$dt])){
									$create_today_rekap[$dt] = array();
								}
								
								$create_today_rekap[$dt][] = $s['id'];
								
							//}
							
							
						}
						
					}
				}*/
				
				//TODAY REKAP FROM TABLE STOCK
				$this->db->select("a.item_id, a.storehouse_id");
				$this->db->from($this->table_stock." as a");
				$this->db->join($this->table_items.' as b',"b.id = a.item_id");
				$this->db->where("a.storehouse_id IN (".$all_storehouse_id_txt.")");
				$this->db->where('b.is_deleted = 0');
				$this->db->where('b.is_active = 1');
				$this->db->group_by('a.item_id');
				$this->db->group_by('a.storehouse_id');
				$get_item = $this->db->get();
				if($get_item->num_rows() > 0){
					foreach($get_item->result_array() as $s){
						foreach($all_active_storehouse as $dt){
							
							if($s['storehouse_id'] == $dt){
								
								if(empty($create_today_rekap[$dt])){
									$create_today_rekap[$dt] = array();
								}
								
								if(!in_array($s['item_id'], $create_today_rekap[$dt]) AND !empty($s['item_id'])){
									$create_today_rekap[$dt][] = $s['item_id'];
								}
								
								
							}
							
							
						}
						
					}
				}
				
			}
			
			
			//echo '<pre>';
			//print_r($create_today_rekap);
			
			
			//CEK IF NOT AVAILABLE ON TODAY
			if(!empty($storehouse_item)){
				foreach($storehouse_item as $dt_storehouse => $getDataItem){
					
					//MAKE SURE IF EMPTY TODAY 
					if(!in_array($dt_storehouse, $all_storehouse_id_today)){
						if(!empty($getDataItem)){
							if(empty($create_today_rekap[$dt_storehouse])){
								$create_today_rekap[$dt_storehouse] = array();
							}	
								
							foreach($getDataItem as $idItem){
								if(!in_array($idItem, $create_today_rekap[$dt_storehouse]) AND !empty($idItem)){
									$create_today_rekap[$dt_storehouse][] = $idItem;
								}
							}
						}
					}
					
				}
			}
			
			
			//echo '<pre>';
			//print_r($storehouse_item);
			//print_r($all_storehouse_id_today);
			//print_r($create_today_rekap);
			//die();
			
			/*foreach($all_storehouse_id as $dt){
				//if(!in_array($dt, $all_storehouse_id_today)){
					
					if(empty($create_today_rekap[$dt])){
						$create_today_rekap[$dt] = array();
					}
					
					//CEK ON UPDATE
					if(!empty($all_storehouse_item_id_today[$dt])){
						foreach($storehouse_item[$dt] as $dtItem){
							if(!in_array($dtItem, $all_storehouse_item_id_today[$dt])){
								$create_today_rekap[$dt][] = $dtItem;
							}
						}
					}
					
					
				//}
			}*/
			
			//echo '<pre>';
			//print_r($storehouse_item);
			//print_r($all_storehouse_item_id_today);
			//print_r($create_today_rekap);
			//die();
			
			
			
			//YESTERDAY 
			$item_yesterday = array();
			$sisa_stok_kemarin = array();
			$this->db->select("a.*");
			$this->db->from($this->table_stock_rekap." as a");
			
			if($get_storehouse_item_today == false){
				$this->db->where("a.storehouse_id IN (".$all_active_storehouse_txt.")");
			}else{
				$this->db->where("a.storehouse_id IN (".$all_storehouse_id_txt.")");
			
				if(!empty($all_item_id)){
					$this->db->where("a.item_id IN (".$all_item_id_txt.")");
				}
			}
			
			
			$this->db->where("a.trx_date = '".$yesterday."'");
			$get_stock_rekap = $this->db->get();
			if($get_stock_rekap->num_rows() > 0){
				foreach($get_stock_rekap->result_array() as $dt){

					//if(!in_array($dt['item_id'], $item_yesterday)){
					//	$item_yesterday[] =  $dt['item_id'];
						
						if(empty($sisa_stok_kemarin[$dt['storehouse_id']])){
							$sisa_stok_kemarin[$dt['storehouse_id']] = array();
						}
						
						if(empty($sisa_stok_kemarin[$dt['storehouse_id']][$dt['item_id']])){
							$sisa_stok_kemarin[$dt['storehouse_id']][$dt['item_id']] = 0;
						}
						
						$sisa_stok_kemarin[$dt['storehouse_id']][$dt['item_id']] = $dt['total_stock'];
					//}
					
				}
			}
			
			$update_stock_storehouse_item = array();
			if(!empty($all_item_id)){
				$all_item_id_txt = implode(",", $all_item_id);
			
				//UPDATE
				$this->db->select("a.*");
				$this->db->from($this->table_stock." as a");
				
				if($get_storehouse_item_today == false){
					$this->db->where("a.storehouse_id IN (".$all_active_storehouse_txt.")");
				}else{
					$this->db->where("a.storehouse_id IN (".$all_storehouse_id_txt.")");
					$this->db->where("a.item_id IN (".$all_item_id_txt.")");
				}
				
				$this->db->where("a.trx_date = '".$date."'");
				$get_trx = $this->db->get();
				if($get_trx->num_rows() > 0){
					foreach($get_trx->result_array() as $dtR){
						if(empty($update_stock_storehouse_item[$dtR['storehouse_id']])){
							$update_stock_storehouse_item[$dtR['storehouse_id']] = array();
						}
						
						if(empty($update_stock_storehouse_item[$dtR['storehouse_id']][$dtR['item_id']])){
							$update_stock_storehouse_item[$dtR['storehouse_id']][$dtR['item_id']] = array(
								'in'	=> 0,
								'out'	=> 0,
								'sto'	=> 0
							);
						}
						
						if($dtR['trx_type'] == 'in'){
							$update_stock_storehouse_item[$dtR['storehouse_id']][$dtR['item_id']]['in'] += $dtR['trx_qty'];
						}
						if($dtR['trx_type'] == 'out'){
							$update_stock_storehouse_item[$dtR['storehouse_id']][$dtR['item_id']]['out'] += $dtR['trx_qty'];
						}
						if($dtR['trx_type'] == 'sto'){
							$update_stock_storehouse_item[$dtR['storehouse_id']][$dtR['item_id']]['sto'] = $dtR['trx_qty'];
						}
						
					}
				}
			}
				
			//echo '<pre>';
			//print_r($update_stock_storehouse_item);
			//die();
			
			//PREPARE FOR CREATE TODAY PER UNIT, PER ITEM
			$dt_create_rekap = array();
			if(!empty($create_today_rekap)){
				foreach($create_today_rekap as $sId => $dtItem){
					
					//ITEMS / UNIT
					if(!empty($dtItem)){
						foreach($dtItem as $dtx){
							$get_total_stock_kemarin = 0;
							if(!empty($sisa_stok_kemarin[$sId][$dtx])){
								$get_total_stock_kemarin = $sisa_stok_kemarin[$sId][$dtx];
							}
							
							$get_total_stock_in = 0;
							$get_total_stock_out = 0;
							$get_total_stock_sto = 0;
							if(!empty($update_stock_storehouse_item[$sId][$dtx])){
								$get_total_stock_in = $update_stock_storehouse_item[$sId][$dtx]['in'];
								$get_total_stock_out = $update_stock_storehouse_item[$sId][$dtx]['out'];
								$get_total_stock_sto = $update_stock_storehouse_item[$sId][$dtx]['sto'];
							}
							
							$get_total_stock = ($get_total_stock_kemarin + $get_total_stock_in) - $get_total_stock_out;
							
							if(!empty($get_total_stock_sto)){
								$get_total_stock = $get_total_stock_sto;
								$get_total_stock_in = 0;
								$get_total_stock_out = 0;
							}
							
							$dt_create_rekap[] = array(
								'storehouse_id'			=> $sId,
								'item_id'				=> $dtx,
								'total_stock'			=> $get_total_stock,
								'total_stock_in'		=> $get_total_stock_in,
								'total_stock_out'		=> $get_total_stock_out,
								'total_stock_kemarin'	=> $get_total_stock_kemarin,
								'trx_date'				=> $date,
							);
						}
					}
					
					/*if(!empty($storehouse_item[$dt])){
						foreach($storehouse_item[$dt] as $dtx){
							
							$get_total_stock_kemarin = 0;
							if(!empty($sisa_stok_kemarin[$dt][$dtx])){
								$get_total_stock_kemarin = $sisa_stok_kemarin[$dt][$dtx];
							}
							
							$get_total_stock_in = 0;
							$get_total_stock_out = 0;
							if(!empty($update_stock_storehouse_item[$dt][$dtx])){
								$get_total_stock_in = $update_stock_storehouse_item[$dt][$dtx]['in'];
								$get_total_stock_out = $update_stock_storehouse_item[$dt][$dtx]['out'];
							}
							
							$get_total_stock = ($get_total_stock_kemarin + $get_total_stock_in) - $get_total_stock_out;
							
							$dt_create_rekap[] = array(
								'storehouse_id'			=> $dt,
								'item_id'				=> $dtx,
								'total_stock'			=> $get_total_stock,
								'total_stock_in'		=> $get_total_stock_in,
								'total_stock_out'		=> $get_total_stock_out,
								'total_stock_kemarin'	=> $get_total_stock_kemarin,
								'trx_date'				=> $date,
							);
							
						}
					}*/
					
				}
			}
			
			//echo '<pre>';
			//print_r($dt_create_rekap);
			//die();
			
			//INSERT
			if(!empty($dt_create_rekap)){
				$this->db->insert_batch($this->table_stock_rekap, $dt_create_rekap);
			}
			
			
			//UPDATE
			$dt_update_rekap = array();
			if(!empty($update_today_rekap_data)){
				
				foreach($update_today_rekap_data as $dt){
						
						$get_total_stock_kemarin = 0;
						if(!empty($sisa_stok_kemarin[$dt['storehouse_id']][$dt['item_id']])){
							$get_total_stock_kemarin = $sisa_stok_kemarin[$dt['storehouse_id']][$dt['item_id']];
						}
						
						$get_total_stock_in = 0;
						$get_total_stock_out = 0;
						$get_total_stock_sto = 0;
						if(!empty($update_stock_storehouse_item[$dt['storehouse_id']][$dt['item_id']])){
							$get_total_stock_in = $update_stock_storehouse_item[$dt['storehouse_id']][$dt['item_id']]['in'];
							$get_total_stock_out = $update_stock_storehouse_item[$dt['storehouse_id']][$dt['item_id']]['out'];
							$get_total_stock_sto = $update_stock_storehouse_item[$dt['storehouse_id']][$dt['item_id']]['sto'];
						}
						
						$get_total_stock = ($get_total_stock_kemarin + $get_total_stock_in) - $get_total_stock_out;
						
						if(!empty($get_total_stock_sto)){
							$get_total_stock = $get_total_stock_sto;
							$get_total_stock_in = 0;
							$get_total_stock_out = 0;
						}
						
						$dt_update_rekap[] = array(
							'id'					=> $dt['id'],
							'storehouse_id'			=> $dt['storehouse_id'],
							'item_id'				=> $dt['item_id'],
							'total_stock'			=> $get_total_stock,
							'total_stock_in'		=> $get_total_stock_in,
							'total_stock_out'		=> $get_total_stock_out,
							'total_stock_kemarin'	=> $get_total_stock_kemarin
						);
				}
				
			}
			
			/*echo '<pre>';
			print_r($storehouse_item);
			print_r($all_storehouse_item_id_today);
			print_r($create_today_rekap);
			print_r($dt_create_rekap);
			print_r($dt_update_rekap);
			die();*/
			
			//UPDATE
			if(!empty($dt_update_rekap)){
				$this->db->update_batch($this->table_stock_rekap, $dt_update_rekap, "id");
			}
			
			return true;
			
		}
		
		return false;
		
	}
	

} 