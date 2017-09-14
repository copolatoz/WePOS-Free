<?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); 
/**
 * Framework System Helper
 *
 * PHP version 5
 *
 * @category  CodeIgniter
 * @package   Framework System
 * @author    angga nugraha (angga.nugraha@gmail.com)
 * @version   0.1
 * Copyright (c) 2011 Angga Nugraha  (http://whazzup.web.id)
*/

/*thumbnail*/
function do_thumb($data, $folder, $thumb_folder, $prefix_thumb = "", $limit_thumb=128, $limit_height=128, $maintain_ratio = TRUE, $master_dim = 'auto')
{
	$objCI =& get_instance();
	
	/* PATH */
	$source = $folder.$data["file_name"];
	$destination_thumb = $thumb_folder;
								
	// Permission Configuration
	chmod($source, 0777) ;
						 
	/* Resizing Processing */
	// Configuration Of Image Manipulation :: Static
	$objCI->load->library('image_lib') ;
	$img['image_library'] = 'GD2';
	$img['create_thumb']  = TRUE;
	$img['maintain_ratio']= $maintain_ratio;
	$img['master_dim']= $master_dim;
				 
	/// Limit Width Resize
	//$limit_thumb    = 64 ;
						 
	// Size Image Limit was using (LIMIT TOP)
	$limit_use  = $data['image_width'] > $data['image_height'] ? $data['image_width'] : $data['image_height'] ;
						 
	// Percentase Resize
	if($data['image_width'] > $data['image_height']){
		if ($limit_use > $limit_thumb) {
			$percent  = $limit_thumb/$limit_use ;
		}else{
			$percent  = 1;
		}
	}else{	
		if ($limit_use > $limit_height) {
			$percent  = $limit_height/$limit_use ;
		}else{
			$percent  = 1;
		}
	}
	
						 
	//// Making THUMBNAIL ///////
	$img['width']  = $limit_use > $limit_thumb ?  $data['image_width'] * $percent : $data['image_width'] ;
	$img['height'] = $limit_use > $limit_height ?  $data['image_height'] * $percent : $data['image_height'] ;
	
	if($maintain_ratio == FALSE){
		
		if(!empty($limit_thumb)){
			$img['width'] = $limit_thumb;
		}
		
		if(!empty($limit_height)){
			$img['height'] = $limit_height;
		}
		
	}
						 
	// Configuration Of Image Manipulation :: Dynamic
	$img['thumb_marker'] = $prefix_thumb;
	$img['quality']      = '100%' ;
	$img['source_image'] = $source ;
	$img['new_image']    = $destination_thumb ;
						 
	// Do Resizing
	$objCI->image_lib->initialize($img);
	$objCI->image_lib->resize();
	$objCI->image_lib->clear() ;	
									
	$img_thumb = $data["raw_name"].$prefix_thumb.$data["file_ext"];
	return $img_thumb;
}

/*OPTIONS*/
if( ! function_exists('get_option_value')){
	function get_option_value($data = array(), $result = 'array'){
		$prefix = config_item('db_prefix');
		if(empty($scope)){
			$scope =& get_instance();
		}
		
		if(empty($data)){
			return false;
		}
		
		$ret_result = 'array';
		if(!empty($result)){
			if($result == 'object'){
				$ret_result = 'object';
			}
		}
		
		if(is_array($data)){
			$all_var = implode("','", $data);
		}else{
			$all_var = $data;
		}
		
		$scope->db->select("option_var, option_value");
		$scope->db->from($prefix."options");
		$scope->db->where("option_var IN ('".$all_var."')");
		$get_lap_param = $scope->db->get();
		
		$all_val = array();
		if($get_lap_param->num_rows() > 0){
			foreach($get_lap_param->result() as $dt){
				$all_val[$dt->option_var] = $dt->option_value;
			}
						
			if($ret_result == 'object'){
				$all_return = (object) $all_val; 
			}else{
				$all_return = $all_val; 
			}
			
			return $all_return;
		}else{
			return false;
		}
		
	}
	
}

if( ! function_exists('get_option')){

	function get_option($data, $echoed = true){
		
		//DEFAULT
		/*$data = array(
			'var' 		=> '',
			'result'	=> 'array',
			'scope'		=> '',
			'echoed'	=> true
		);*/
		
		/*single, echoed*/
		$prefix = config_item('db_prefix');
		if(empty($scope)){
			$scope =& get_instance();
		}	
				
		$tipe = 'single';
		if(is_array($data)){
			extract($data);
			$tipe = 'data';
		}else{
			//string
			$var = $data;
		}
		
		//single condition
		if(empty($data['echoed']) AND $tipe == 'data'){
			$echoed = true;
		}
		
		$ret_result = 'array';
		if(!empty($result)){
			if($result == 'object'){
				$ret_result = 'object';
			}
		}
		
		$data_res = array();
				
		$scope->db->select('a.*');
		$scope->db->from($prefix.'options as a');		
		$scope->db->where('a.is_deleted', 0);
		$scope->db->where('a.is_active', 1);
		
		if(is_array($var)){
			$var_all = implode("','", $var);
			$scope->db->where("a.option_var IN ('".$var_all."')");
		}else{
			$scope->db->where('a.option_var', $var);	
		}
		
		$query = $scope->db->get();
		if($query->num_rows() > 0){
			
			$newData = array();
			foreach($query->result_array() as $dt){
				$newData = $dt;
			}
			
			if($tipe == 'data'){
				if($ret_result == 'object'){
					$data_res = (object) $newData; 
				}else{
					$data_res = $newData; 
				}
			}else{
				$data_res = (object) $newData; 
			}
			
		}
		
		if(!empty($data_res)){
			if($tipe == 'data'){
				return $data_res;
			}else{
				if($echoed == true){
					echo $data_res->option_value;
				}else{
					return $data_res->option_value;
				}
			}
		}
		
		return '';
	}
	
}

if( ! function_exists('update_option')){

	function update_option($data = array()){
		
		//DEFAULT
		/*$data = array(
			'var' 		=> '' (var | array)
		);*/
		
		/*single, echoed*/
		$prefix = config_item('db_prefix');
		if(empty($scope)){
			$scope =& get_instance();
		}	
		
		if(empty($data)){
			return false;
		}
		
		$get_var = array();
		foreach($data as $key => $dt){
			if(!in_array($key, $get_var)){
				$get_var[] = $key;
			}
		}
				
		$option_update = array();
		$option_update_key = array();
				
		$scope->db->select('a.*');
		$scope->db->from($prefix.'options as a');		
		$scope->db->where('a.is_deleted', 0);
		$scope->db->where('a.is_active', 1);
		
		if(is_array($get_var)){
			$var_all = implode("','", $get_var);
			$scope->db->where("a.option_var IN ('".$var_all."')");
		}else{
			$scope->db->where("a.option_var != '-1' ");	//just for skip
		}
		
		$query = $scope->db->get();
		if($query->num_rows() > 0){
			
			//UPDATE OPTION
			foreach($query->result_array() as $dt){
			
				if(!in_array($dt['option_var'], $option_update_key)){
					$option_update_key[] = $dt['option_var'];
				}
				
				if(!empty($data[$dt['option_var']])){
					$option_update[] = array(
						"option_var" => $dt['option_var'],
						"option_value" => $data[$dt['option_var']]
					);
				}else{
					$option_update[] = array(
						"option_var" => $dt['option_var'],
						"option_value" => ""
					);
				}
				
			}
			
		}
		
		//UPDATE ALL
		if(!empty($option_update)){
			$scope->db->update_batch($prefix.'options', $option_update, 'option_var'); 
		}
		
		$all_insert_key = array();
		$option_insert = array();
		//INSERT ALL
		foreach($get_var as $opt_var){
			if(!in_array($opt_var, $option_update_key)){
				
				if(!in_array($opt_var, $all_insert_key)){
					$all_insert_key[] = $opt_var;
					
					if(!empty($data[$opt_var])){
						$option_insert[] = array(
							"option_var" => $opt_var,
							"option_value" => $data[$opt_var]
						);
					}
					
				}
				
			}
		}
		
		if(!empty($option_insert)){
			$scope->db->insert_batch($prefix.'options', $option_insert); 
		}		
		
		return true;
	}
	
}

if( ! function_exists('replace_to_printer_command')){
	function replace_to_printer_command($text = '', $tipe_printer = 'EPSON', $tipe_pin = '42 CHAR'){
		
		//STANDARD 42 CHAR
		
		//set tab - calculation -----------------
		//get param set tab
		$use_set_tab = false;
		$set_tab = array(4,17,20);
		$getParamSetTab = explode("[set_tab=",$text);
		
		$newText_setTab = array();
		$newText_setTab[] = $getParamSetTab[0];
		if(!empty($getParamSetTab[1])){
			//exp by "]" then trim
			$use_set_tab = true;
			$getParamSetTab2 = explode("]",$getParamSetTab[1]);
			if(!empty($getParamSetTab2[1])){
				//exp by ","
				$getParamSetTab3 = explode(",", trim($getParamSetTab2[0]));
				if(!empty($getParamSetTab3)){
					$set_tab = $getParamSetTab3;
				}
				
				$getParamSetTab2[0] = implode(",", $set_tab);
			}
			
			$newText_setTab[] = implode("]",$getParamSetTab2);
			
			//renew text
			$text = implode("[set_tab=", $newText_setTab);
		}
				
		if(!empty($use_set_tab)){
			$set_tab_hexa_array = array();
			
			$set_tab_hexa_array[] = "1b";
			$set_tab_hexa_array[] = "44";
			
			foreach($set_tab as $col_tab){
			
				if(strlen($col_tab) == 1){
					$set_tab_hexa_array[] = "0".$col_tab;
				}else{
					$set_tab_hexa_array[] = $col_tab;
				}
				
			}
			
			//count cols
			$totCols_hexa_array = "";
			$totCols = count($set_tab) + 1;
			if(strlen($totCols) == 1){
				$totCols_hexa_array = "0".$totCols;
			}else{
				$totCols_hexa_array = $totCols;
			}					
			
		}
		
		//chinese txt =
		//\x1b\x52\x15
		
		//DEFAULT -- 42
		$string_to_hexa = array(
			"]\n"	=> "]", //auto trim
			"[align=0]"	=> "\x1b\x61\x00", //left
			"[align=1]"	=> "\x1b\x61\x01", //center
			"[align=2]"	=> "\x1b\x61\x02", //right
			"[size=0]"	=> "\x1d\x21\x00", //all=0
			"[size=1]"	=> "\x1d\x21\x01", //width=0, height=1
			"[size=2]"	=> "\x1d\x21\x11", //width=1, height=2
			"[size=3]"	=> "\x1d\x21\x11", //width =2, height=3
			"[set_tab1]"	=> "\x1b\x44\x04\x19\x21,x04",
			//"[set_tab2]"	=> "\x1b\x44\x0f\x21,x03",
			"[set_tab2]"	=> "\x1b\x44\x12\x1e,x03",
			"[set_tab3]"	=> "\x1b\x44\x01\x1a,x03",
			"[tab]"	=> "\x09",
			"[newline]"	=> "\x0A",
			"[fullcut]"	=> "\x1b\x69",
			"[cut]"	=> "\x1b\x6d",
			"[clear_set_tab]"	=> "\x1b\x44\x00"
		);
		
		//EPSON-DEFAULT
		//32
		if($tipe_pin == '32 CHAR'){
			$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x10\x18";
			//$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x0e\x16";
			$string_to_hexa['[set_tab2]'] = "\x1b\x44\x07\x13";
			$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x13";
		}
		
		//40
		if($tipe_pin == '40 CHAR'){
			$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x17\x20";
			$string_to_hexa['[set_tab2]'] = "\x1b\x44\x10\x1c";
			$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x1c";
		}
		
		//42
		if($tipe_pin == '42 CHAR'){
			$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x19\x22";
			$string_to_hexa['[set_tab2]'] = "\x1b\x44\x12\x1e";
			$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x1e";
		}
		
		//46
		if($tipe_pin == '46 CHAR'){
			$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x1b\x24";
			$string_to_hexa['[set_tab2]'] = "\x1b\x44\x12\x1e";
			$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x1e";
		}
		
		//48
		if($tipe_pin == '48 CHAR'){
			$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x1d\x26";
			$string_to_hexa['[set_tab2]'] = "\x1b\x44\x12\x24";
			$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x24";
		}
		
		if($tipe_printer == 'BIRCH'){
			$string_to_hexa['[set_tab1]'] .= ",\x00";
			$string_to_hexa['[set_tab2]'] .= ",\x00";
			$string_to_hexa['[set_tab3]'] .= ",\x00";
		}
		
		if($tipe_printer == 'STAR'){
			
			$string_to_hexa['[align=0]']= "\x1b\x1d\x61\x00"; //left
			$string_to_hexa['[align=1]']= "\x1b\x1d\x61\x01"; //center
			$string_to_hexa['[align=2]']= "\x1b\x1d\x61\x02"; //right
			
			$string_to_hexa['[size=0]']	= "\x1b\x1d\x21\x00";
			$string_to_hexa['[size=1]']	= "\x1b\x1d\x21\x11";
			$string_to_hexa['[size=2]']	= "\x1b\x1d\x21\x00";
			$string_to_hexa['[size=3]']	= "\x1b\x1d\x21\x00";
			
			$string_to_hexa['[set_tab1]'] .= ",\x00";
			$string_to_hexa['[set_tab2]'] .= ",\x00";
			$string_to_hexa['[set_tab3]'] .= ",\x00";
			
			
		}
		
		if($tipe_printer == 'SEWOO'){
			//32
			if($tipe_pin == '32 CHAR'){
				$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x10\x18,x04";
				//$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x0e\x16";
				$string_to_hexa['[set_tab2]'] = "\x1b\x44\x07\x13,x03";
				$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x13,x03";
			}
			
			//40
			if($tipe_pin == '40 CHAR'){
				$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x17\x20,x04";
				$string_to_hexa['[set_tab2]'] = "\x1b\x44\x10\x1c,x03";
				$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x1c,x03";
			}
			
			//42
			if($tipe_pin == '42 CHAR'){
				$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x19\x22,x04";
				$string_to_hexa['[set_tab2]'] = "\x1b\x44\x12\x1e,x03";
				$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x1e,x03";
			}
			
			//46
			if($tipe_pin == '46 CHAR'){
				$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x1b\x24,x04";
				$string_to_hexa['[set_tab2]'] = "\x1b\x44\x12\x22,x03";
				$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x22,x03";
			}
				
			//48
			if($tipe_pin == '48 CHAR'){
				$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x1d\x26,x04";
				$string_to_hexa['[set_tab2]'] = "\x1b\x44\x12\x24,x03";
				$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x24,x03";
			}
		}
		
		//58mm printer china
		$printerChina58 = array('ENIBIT','QPOS','Zjiyang');
		if(in_array($tipe_printer, $printerChina58)){
			
			$string_to_hexa['[size=2]']	= "\x1d\x21\x10";
			$string_to_hexa['[size=3]']	= "\x1d\x21\x20";
			
			//$string_to_hexa['[set_tab1]'] .= "\x1F,\x00";
			$string_to_hexa['[set_tab1]'] .= ",\x00";
			$string_to_hexa['[set_tab2]'] .= ",\x00";
			$string_to_hexa['[set_tab3]'] .= ",\x00";
			
		}
		
		if(!empty($use_set_tab) AND !empty($set_tab)){
			//--replace set tab
			$setTabHexa = '$setTabHexa = "\x'.implode('\x',$set_tab_hexa_array).',x'.$totCols_hexa_array.'";';
			$set_tab_txt = implode(",", $set_tab);
			//echo $setTabHexa;
			eval($setTabHexa);
			$string_to_hexa["[set_tab=".$set_tab_txt."]"] = $setTabHexa;
			
		}
		
		//echo "<pre>";
		//print_r($string_to_hexa);
		//die();
		
		$newText = strtr($text, $string_to_hexa);
		
		return $newText;
	}
}

if( ! function_exists('printer_command_align_right')){
	function printer_command_align_right($text = '', $length_set = 0, $is_html = 0){
		
		$text_show = $text;
		if(!empty($length_set)){
			$length_txt = strlen($text);
			$text_show = $text;
			if($length_txt < $length_set){
				$gapTxt = $length_set - $length_txt;
				if($is_html){
					$text_show = str_repeat("&nbsp;", $gapTxt).$text_show;
				}else{
					$text_show = str_repeat(" ", $gapTxt).$text_show;
				}
											
			}
		}
		
		return $text_show;
	}
}

if( ! function_exists('get_client_ip')){
	function get_client_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
		
		if($ip == '::1'){
			$ip = '127.0.0.1';
		}
		
        return $ip;
    } 
}

//GET STATUS CLOSING
if( ! function_exists('is_closing')){

	function is_closing($data = array()){
		
		//DEFAULT
		/*$data = array(
			'xdate' 	=> '',
			'xtipe'	=> 'sales'
		);*/
		
		/*single, echoed*/
		$prefix = config_item('db_prefix2');
		if(empty($scope)){
			$scope =& get_instance();
		}	
				
		$xtipe = 'sales';
		$closing_status = 1;
		if(is_array($data)){
			extract($data);
		}else{
			//string
			$xdate = $data;
		}
		
		if(empty($xdate)){
			$xdate = date("Y-m-d");
		}
		
		$xdate = date("Y-m-d", strtotime($xdate));
				
		$scope->db->select('a.*');
		$scope->db->from($prefix.'closing as a');		
		$scope->db->where('a.tipe', $xtipe);
		$scope->db->where('a.tanggal', $xdate);
		$scope->db->where('a.closing_status', $closing_status);
		$query = $scope->db->get();
		if($query->num_rows() > 0){
			//SUDAH CLOSING
			return true;
		}
		
		//SUDAH CLOSING
		return false;
	}
	
}

//GET STATUS CLOSING
if(!function_exists('empty_value_printer_text')){

	function empty_value_printer_text($Receipt_layout = '', $tipe = ''){
		if(empty($tipe)){
			return $Receipt_layout;
		}
		
		$Receipt_layout_exp = explode("\n", $Receipt_layout);
		$new_layout = array();
		if(!empty($Receipt_layout_exp)){
			foreach($Receipt_layout_exp as $dt){
				
				if(strstr($dt, $tipe)){
					if(strstr($dt, '{hide_empty}')){
						
					}else{
						$new_layout[] = $dt;
					}
				}else{
					$new_layout[] = $dt;
				}
				
			}
		}
		
		$new_layout_txt = $Receipt_layout;
		if(!empty($new_layout)){
			$new_layout_txt = implode("\n", $new_layout);
		}
		
		return $new_layout_txt;
	}
}


?>