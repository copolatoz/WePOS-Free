<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class accKodeRekening extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix3');
		$this->load->model('model_acc_kode_rekening', 'm');
	}

	public function gridData()
	{
		$this->table = $this->prefix.'kode_rekening';
		
		//is_active_text
		$sortAlias = array(
		//	'is_active_text' => 'is_active'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> 'a.*, b.nama_kel_akun, c.nama_kel_akun_detail',
			'primary_key'	=> 'a.id',
			'table'			=> $this->table.' as a',
			'join'			=> array(
									'many', 
									array( 
										array($this->prefix.'kel_akun as b','b.kd_kel_akun = a.kd_kel_akun','LEFT'),
										array($this->prefix.'kel_akun_detail as c','c.kd_kel_akun_detail = a.kd_kel_akun_detail','LEFT')
									) 
								),
			'where'			=> array('a.is_deleted' => 0),
			'order'			=> array('a.kode_rek' => 'ASC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		
		//DROPDOWN & SEARCHING
		$is_dropdown = $this->input->post('is_dropdown');
		$searching = $this->input->post('query');
		$kd_kel_akun = $this->input->post('kd_kel_akun');
		$only_child = $this->input->post('only_child');
		
		if(empty($only_child)){
			$only_child = 0;
		}
		
		if(!empty($searching)){
			$params['where'][] = "(a.kode_rek LIKE '%".$searching."%' OR a.nama_rek LIKE '%".$searching."%')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		//re-assign per-parent
		$dt_parent = array();
      	$newData = array();
		
		if($is_dropdown){
			$root_data = array(
				'id' => 0,
				'kode_rek' => '',
				'nama_rek' => '-- Parent --',
				'kode_nama_rek' => '-- Parent --',
				'kode_nama_rek_show' => '-- Parent --',
				'parent' => 0,
				'coa_level' => 0,
				'coa_level_add' => 0,
				'status_akun' => 0,
				'posisi_akun' => ''
			);
			$dt_parent[0] = array();
			$dt_parent[0][] = $root_data;
			
			if($only_child == 1){
				$root_data = array(
					'id' => 0,
					'kode_rek' => '',
					'nama_rek' => '-- Pilih Akun --',
					'kode_nama_rek' => '-- Pilih Akun --',
					'kode_nama_rek_show' => '-- Pilih Akun --',
					'parent' => 0,
					'coa_level' => 0,
					'coa_level_add' => 0,
					'status_akun' => 0,
					'posisi_akun' => ''
				);
				$dt_parent[0] = array();
				$dt_parent[0][] = $root_data;
			}
		}
		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				
				$s['kode_nama_rek'] = $s['kode_rek'].' - '.$s['nama_rek'];
				$s['kode_nama_rek_show'] = $s['kode_rek'].' - '.$s['nama_rek'];

				$s['coa_level_add'] = $s['coa_level'] + 1;
				$s['kode_rek_level'] = $s['kode_rek'];
				
				if(empty($dt_parent[$s['parent']])){
					$dt_parent[$s['parent']] = array();
				}
				$dt_parent[$s['parent']][] = $s;
				
				array_push($newData, $s);
			}
		}
		
		$get_data['data'] = $newData;
		
		/*---------- SET PARENT - CHILD --------------------- */		
		$id_edit = 0;
		if(!empty($_POST['id_edit'])){
			$id_edit = $this->input->post('id', true);
		}
		
		//echo '<pre>';
		//print_r($dt_parent);
		//die();
		
		$data = array(
			'data'		=> $dt_parent,
			'parent'	=> 0,
			'coa_level'		=> 0,
			'id_edit'	=> $id_edit
		);
		$newData = $this->kode_rekening_parent_child($data);
		/*---------- SET PARENT - CHILD --------------------- */
	
		
		
		$newData_t = array();
		if(!empty($newData)){
			foreach($newData as $dt){
				
				if($is_dropdown == 0){
					$dt['coa_level_add'] = $dt['coa_level'];
				}
				
				if($only_child == 1){
					if($dt['status_akun'] == 'detail'){
						$dt['kode_nama_rek_show'] = trim(str_replace("&nbsp;","",$dt['kode_nama_rek_show']));
						$newData_t[] = $dt;
					}
				}else{
					$newData_t[] = $dt;
				}
				
			}
		}
		
		$newData = $newData_t;
			
		
		
		$get_data['data'] = $newData;
		$get_data['totalCount'] = count($newData);
		
		//echo '<pre>';
		//print_r($newData);
		//die();
		
      	die(json_encode($get_data));
	}
	
	public function kode_rekening_parent_child($data_post){
		
		//global $all_data;
		$data_default = array(
			'data'		=> array(),
			'parent'	=> 0,
			'coa_level'		=> 0,
			'id_edit'	=> 0
		);
		$data_post = array_merge($data_default, $data_post);
		extract($data_post);
		
		if($coa_level > 0){
			if($coa_level > 1){
				$separator = str_repeat(' &nbsp; &nbsp; &nbsp; ', ($coa_level-1));
			}else{
				$separator = ' &nbsp; ';
			}
		}
		
		$curr_coa_level = $coa_level;
		$coa_level++;
		
		if(!empty($data[$parent])){
			$get_all_child = array();
			
			foreach($data[$parent] as $dt_child){
				
				if($curr_coa_level > 0){
					$dt_child['kode_nama_rek_show'] = $separator.$dt_child['kode_nama_rek_show'];
					$dt_child['kode_rek_level'] = $separator.$dt_child['kode_rek'];
				}else{
					$dt_child['kode_nama_rek_show'] = $dt_child['kode_nama_rek_show'];
					$dt_child['kode_rek_level'] = $dt_child['kode_rek'];
				}
				
				$dt_child['coa_level_add'] = $dt_child['coa_level'] + 1;
				$dt_child['status_akun'] = 'parent';
					
				if(!empty($dt_child['id'])){
				
					$check_parent_id = $dt_child['id'];
										
					$data_default = array(
						'data'		=> $data,
						'parent'	=> $check_parent_id,
						'coa_level'	=> $coa_level,
						'id_edit'	=> $id_edit
					);
					
					$get_child = $this->kode_rekening_parent_child($data_default);
				}
				
				if(!empty($id_edit)){
					$show_data = false;
					if($dt_child['id'] != $id_edit){
						$show_data = true;
					}
				}else{
					$show_data = true;
				}
				
				if($show_data){
								
					if(empty($get_child)){
						$dt_child['status_akun'] = 'detail';
					}
					$get_all_child[] = $dt_child;	
					
					if(!empty($get_child)){
						
						foreach($get_child as $dt_get){
							
							if(!empty($id_edit)){
								if($dt_get['id'] != $id_edit){
									$get_all_child[] = $dt_get;
								}
							}else{
								$get_all_child[] = $dt_get;
							}
							
						}
						
					}	
				}
			}
			
			return $get_all_child;
			
		}else{
			//child
			return '';		
		}	
	}
	
	public function getKodeRekeningBaru(){
		$this->table = $this->prefix.'kode_rekening';				
		$session_user = $this->session->userdata('user_username');
		
		$coa_level = $this->input->post('coa_level');
		$parent = $this->input->post('parent');
		
		if(empty($session_user)){
			$r = array('success' => false);
			die(json_encode($r));
		}
		
		//check kode
		$this->db->from($this->table);
		$this->db->where("coa_level = '".$coa_level."'");
		
		if(!empty($parent)){
			$this->db->where("parent = '".$parent."'");
		}
		$this->db->order_by("kode_rek","DESC");
		$check_akun = $this->db->get();
		if($check_akun->num_rows() > 0){
			$dt_kode = $check_akun->row_array();
		}
		
		
		$kode_rek = '10.00.00.00.00';	
		
		//get parent
		$dt_kode_parent = array();
		if(!empty($parent)){
			$this->db->from($this->table);
			$this->db->where("id = '".$parent."'");
			$check_akun_p = $this->db->get();
			if($check_akun_p->num_rows() > 0){
				$dt_kode_parent = $check_akun_p->row_array();
			}
		}
		
		switch($coa_level){
			case 1 : $new_kode = 0;
				if(!empty($dt_kode['kode_rek'])){
					$new_kode = substr($dt_kode['kode_rek'], 0, 2);
				}
				$kode_rek = ($new_kode+10).".00.00.00.00";
				break;
			case 2 : $new_kode = 0;				
				
				
				if(!empty($dt_kode['kode_rek'])){
					$new_kode = substr($dt_kode['kode_rek'], 0, 2);
					$kode_rek = ($new_kode+1).".00.00.00.00";					
				}else{
				
					if(!empty($dt_kode_parent)){
						$new_kode = substr($dt_kode_parent['kode_rek'], 0, 2);
						//$new_kode = $new_kode_parent.'0';
						$kode_rek = ($new_kode+1).".00.00.00.00";
					}
						
				}					
				break;
			case 3 : $new_kode = 0;		
				
				if(!empty($dt_kode['kode_rek'])){
					$new_kode1 = substr($dt_kode['kode_rek'], 0, 2);
					$new_kode = substr($dt_kode['kode_rek'], 3, 2);
					
					$new_kode = ($new_kode+1);
					
					if(strlen($new_kode) == 1){
						$new_kode = "0".$new_kode;
					}
					
					$kode_rek = $new_kode1.".".($new_kode).".00.00.00";					
				}else{
					if(!empty($dt_kode_parent)){
						$new_kode1 = substr($dt_kode_parent['kode_rek'], 0, 2);
						$new_kode = substr($dt_kode_parent['kode_rek'], 3, 2);
						//$new_kode_parent = substr($dt_kode_parent['kode_rek'], 0, 2);
						//$new_kode = $new_kode_parent.'0';
						$new_kode = ($new_kode+1);
						
						if(strlen($new_kode) == 1){
							$new_kode = "0".$new_kode;
						}
						
						$kode_rek = $new_kode1.".".$new_kode.".00.00.00";	
					}
					
				}
				break;
			case 4 : $new_kode = 0;	$new_kode2_new = 0;	
			
				if(!empty($dt_kode['kode_rek'])){
					$new_kode_parent = substr($dt_kode['kode_rek'], 0, 5);
					$new_kode2 = (int)substr($dt_kode['kode_rek'], 7, 2);
					$new_kode2_new = $new_kode2+1;
					if(strlen($new_kode2_new) == 1){
						$new_kode2_new = "0".$new_kode2_new;
					}
					$kode_rek = $new_kode_parent.".".$new_kode2_new.".00.00";					
				}else{
					if(!empty($dt_kode_parent)){
						$new_kode = substr($dt_kode_parent['kode_rek'], 0, 5);
						$kode_rek = $new_kode.".01.00.00";
					}	
				}
				break;
			case 5 : $new_kode = 0;	$new_kode2_new = 0;			
				if(!empty($dt_kode['kode_rek'])){
					$new_kode = substr($dt_kode['kode_rek'], 0, 8);
					$new_kode2 = (int)substr($dt_kode['kode_rek'], 10, 2);
					$new_kode2_new = $new_kode2+1;
					if(strlen($new_kode2_new) == 1){
						$new_kode2_new = "0".$new_kode2_new;
					}
					$kode_rek = $new_kode.".".$new_kode2_new.".00";					
				}else{
					if(!empty($dt_kode_parent)){
						$new_kode_parent = substr($dt_kode_parent['kode_rek'], 0, 8);
						$new_kode = $new_kode_parent;
						$kode_rek = $new_kode.".01.00";
					}	
				}
				break;
			case 6 : $new_kode = 0;	$new_kode2_new = 0;			
				if(!empty($dt_kode['kode_rek'])){
					$new_kode = substr($dt_kode['kode_rek'], 0, 11);
					$new_kode2 = (int)substr($dt_kode['kode_rek'], 13, 2);
					$new_kode2_new = $new_kode2+1;
					if(strlen($new_kode2_new) == 1){
						$new_kode2_new = "0".$new_kode2_new;
					}
					$kode_rek = $new_kode.".".$new_kode2_new;					
				}else{
					if(!empty($dt_kode_parent)){
						$new_kode_parent = substr($dt_kode_parent['kode_rek'], 0, 11);
						$new_kode = $new_kode_parent;
						$kode_rek = $new_kode.".01";
					}	
				}
				break;
			default: 
				$kode_rek = "";
				break;
		}
		
		$r = array('success' => true, 'kode_rek' => $kode_rek);
		die(json_encode($r));
		
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'kode_rekening';				
		$session_user = $this->session->userdata('user_username');
		
		$kode_rek = $this->input->post('kode_rek');
		$nama_rek = $this->input->post('nama_rek');
		$kd_kel_akun = $this->input->post('kd_kel_akun');
		$kd_kel_akun_detail = $this->input->post('kd_kel_akun_detail');
		$get_parent = $this->input->post('get_parent');
		$parent = $this->input->post('parent');
		$coa_level = $this->input->post('coa_level');
		$coa_level_add = $this->input->post('coa_level_add');
		$posisi_akun = $this->input->post('posisi_akun');
		
		if(empty($kode_rek) OR empty($nama_rek)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
				
		$id = $this->input->post('id', true);
			
		//check kode
		$this->db->from($this->table);
		$this->db->where("kode_rek = '".$kode_rek."'");
		if($this->input->post('form_type_accKodeRekening', true) == 'edit'){
			$this->db->where("id != '".$id."'");
		}
		
		$check_akun = $this->db->get();
		if($check_akun->num_rows() > 0){
			$r = array('success' => false, 'info' => 'Kode Rekening Sudah Ada');
			die(json_encode($r));			
		}
		
		$status_akun = 'parent';
		if(!empty($parent)){
			$status_akun = 'detail';
			
			$this->db->select("a.*");
			$this->db->from($this->table." as a");
			$this->db->where("a.id = '".$parent."'");
			
			$check_parent = $this->db->get();
			if($check_parent->num_rows() > 0){
				
				$dt_parent = $check_parent->row();
				if(empty($kd_kel_akun_detail)){
					$kd_kel_akun_detail = $dt_parent-> kd_kel_akun_detail;
				}
				
			}
		}
		
		
			
		$r = '';
		if($this->input->post('form_type_accKodeRekening', true) == 'add')
		{
			$var = array(
				'fields'	=>	array(
				    'kode_rek'  	=> 	$kode_rek,
				    'nama_rek' 		=> 	$nama_rek,
				    'kd_kel_akun'  	=> 	$kd_kel_akun,
				    'kd_kel_akun_detail' => $kd_kel_akun_detail,
				    'parent' 		=> $parent,
				    'status_akun' 	=> $status_akun,
				    'coa_level' 	=> $coa_level_add,
				    'posisi_akun' 	=> $posisi_akun,
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
				$r = array('success' => true, 'id' => $insert_id); 				
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_accKodeRekening', true) == 'edit'){
			$var = array('fields'	=>	array(
				    'kode_rek'  	=> 	$kode_rek,
				    'nama_rek' 		=> 	$nama_rek,
				    'kd_kel_akun'  	=> 	$kd_kel_akun,
				    'kd_kel_akun_detail' => $kd_kel_akun_detail,
				    'parent' 		=> $parent,
				    'status_akun' 	=> $status_akun,
				    'coa_level' 	=> $coa_level_add,
				    'posisi_akun' 	=> $posisi_akun,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			//UPDATE
			$this->lib_trans->begin();
				$update = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				$r = array('success' => true, 'id' => $id);
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
		$this->table = $this->prefix.'kel_akun';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode("','", $id);
		}
		
		//Delete
		//$this->db->where("id IN ('".$sql_Id."')");
		$data_update = array(
			"is_deleted" => 1
		);
		$q = $this->db->update($this->table, $data_update, "id IN (".$sql_Id.")");
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true); 
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Hapus Kode Rekening Gagal!'); 
        }
		die(json_encode($r));
	}
	
}