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
					
					//if(!empty($data[$opt_var])){
						$option_insert[] = array(
							"option_var" => $opt_var,
							"option_value" => $data[$opt_var]
						);
					//}
					
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
	function replace_to_printer_command($text = '', $tipe_printer = 'EPSON', $tipe_pin = 42){
		
		/*
			0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F => 16
			10,11,12,13,14,15,16,17,18,19,1A,1B,1C,1D,1E,1F
			20,21,22,23,24,25,26,27,28,29,2A,2B,2C,2D,2E,2F
			30,31,32,33,34,35,36,37,38,39,3A,3B,3C,3D,3E,3F
		*/	

		$tipe_pin = str_replace("CHAR", "", $tipe_pin);
		
		$string_to_hexa = array(
			"]\n"	=> "]", //auto trim
			"[align=0]"	=> "\x1b\x61\x00", //left
			"[align=1]"	=> "\x1b\x61\x01", //center
			"[align=2]"	=> "\x1b\x61\x02", //right
			"[size=0]"	=> "\x1d\x21\x00", //all=0
			"[size=1]"	=> "\x1d\x21\x01", //width=0, height=1
			"[size=2]"	=> "\x1d\x21\x11", //width=1, height=2
			"[size=3]"	=> "\x1d\x21\x11", //width =2, height=3
			"[set_tab1]"	=> "\x1b\x44\x04\x10\x18",
			"[set_tab2]"	=> "\x1b\x44\x07\x13",
			"[set_tab3]"	=> "\x1b\x44\x01\x13",
			"[set_tab1a]"	=> "\x1b\x44\x04\x13",
			"[set_tab1b]"	=> "\x1b\x44\x10,x02",
			"[tab]"	=> "\x09",
			"[newline]"	=> "\x0A",
			"[fullcut]"	=> "\x1b\x69",
			"[cut]"	=> "\x1b\x6d",
			"[clear_set_tab]"	=> "\x1b\x44\x00"
		);
		
		//EPSON-DEFAULT
		//32
		if($tipe_pin == 32){
			$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x10\x18";
			//$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x0e\x16";
			$string_to_hexa['[set_tab2]'] = "\x1b\x44\x07\x13";
			$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x13";
			$string_to_hexa['[set_tab1a]'] = "\x1b\x44\x04\x13";
			$string_to_hexa['[set_tab1b]'] = "\x1b\x44\x10";
		}
		
		//40
		if($tipe_pin == 40){
			$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x14\x1c";
			$string_to_hexa['[set_tab2]'] = "\x1b\x44\x0F\x19";
			$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x19";
			$string_to_hexa['[set_tab1a]'] = "\x1b\x44\x04\x19";
			$string_to_hexa['[set_tab1b]'] = "\x1b\x44\x17";
		}
		
		//42
		if($tipe_pin == 42){
			$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x13\x1c";
			$string_to_hexa['[set_tab2]'] = "\x1b\x44\x12\x1c";
			$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x1c";
			$string_to_hexa['[set_tab1a]'] = "\x1b\x44\x04\x1c";
			$string_to_hexa['[set_tab1b]'] = "\x1b\x44\x19";
		}
		
		//46
		if($tipe_pin == 46){
			$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x17\x20";
			$string_to_hexa['[set_tab2]'] = "\x1b\x44\x15\x20";
			$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x20";
			$string_to_hexa['[set_tab1a]'] = "\x1b\x44\x04\x20";
			$string_to_hexa['[set_tab1b]'] = "\x1b\x44\x1d";
		}
		
		//48
		if($tipe_pin == 48){
			$string_to_hexa['[set_tab1]'] = "\x1b\x44\x04\x18\x23";
			$string_to_hexa['[set_tab2]'] = "\x1b\x44\x15\x22"; 
			$string_to_hexa['[set_tab3]'] = "\x1b\x44\x01\x22";
			$string_to_hexa['[set_tab1a]'] = "\x1b\x44\x04\x22";
			$string_to_hexa['[set_tab1b]'] = "\x1b\x44\x1f";
		}
		
		if($tipe_printer == 'BIRCH'){
			$string_to_hexa['[set_tab1]'] .= ",\x00";
			$string_to_hexa['[set_tab2]'] .= ",\x00";
			$string_to_hexa['[set_tab3]'] .= ",\x00";
			$string_to_hexa['[set_tab1a]'] .= ",\x00";
			$string_to_hexa['[set_tab1b]'] .= ",\x00";
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
			$string_to_hexa['[set_tab1a]'] .= ",\x00";
			$string_to_hexa['[set_tab1b]'] .= ",\x00";
			
			
		}
		
		if($tipe_printer == 'SEWOO'){
			$string_to_hexa['[set_tab1]'] .= ",x04";
			$string_to_hexa['[set_tab2]'] .= ",x03";
			$string_to_hexa['[set_tab3]'] .= ",x03";
			$string_to_hexa['[set_tab1a]'] .= ",x03";
			$string_to_hexa['[set_tab1b]'] .= ",x02";
		}
		
		//58mm printer china
		$printerChina58 = array('ENIBIT','QPOS','Zjiyang');
		if(in_array($tipe_printer, $printerChina58)){
			
			$string_to_hexa['[size=2]']	= "\x1d\x21\x10";
			$string_to_hexa['[size=3]']	= "\x1d\x21\x20";
			
			$string_to_hexa['[set_tab1]'] .= ",\x00";
			$string_to_hexa['[set_tab2]'] .= ",\x00";
			$string_to_hexa['[set_tab3]'] .= ",\x00";
			$string_to_hexa['[set_tab1a]'] .= ",\x00";
			$string_to_hexa['[set_tab1b]'] .= ",\x00";
			
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


//printing_process
if(!function_exists('printing_process')){

	function printing_process($data_printer = array(), $print_content = '', $do = 'print', $print_logo = 0){
		
		$objCI =& get_instance();
		
		if(empty($data_printer)){
			echo 'Data Printer Tidak Diketahui!';
			die();
		}
		if(empty($print_content)){
			echo 'Konten Print Kosong!';
			die();
		}
				
		$print_content = str_replace("[tab]","|tab|", $print_content);
		//explode
		$exp_text = explode("\n", $print_content);


		$printer_pin = $data_printer['printer_pin'];
		$printer_pin = trim(str_replace("CHAR", "", $printer_pin));
		
		$align_text = array('[align=0','[align=1','[align=2');
		$size_text = array('[size=0','[size=1','[size=2','[size=2');
		$settab_text = array('[set_tab1','[set_tab2','[set_tab3','[set_tab1a','[set_tab1b','[tab');
		$set_tab = array();
		$set_tab_pixel = array();

		/*
			0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F => 16
			10,11,12,13,14,15,16,17,18,19,1A,1B,1C,1D,1E,1F
			20,21,22,23,24,25,26,27,28,29,2A,2B,2C,2D,2E,2F
			30,31,32,33,34,35,36,37,38,39,3A,3B,3C,3D,3E,3F
		*/	

		$set_width = array(
			'32' => 220,
			'40' => 250,
			'42' => 265,
			'46' => 305,
			'48' => 305,
		);	
		
		//CHAR PIN
		$set_tab[32] = array(
			'1' => array(
						1, 5, 16, 23
					),
			'2' => array(
						1, 4, 18
					),
			'3' => array(
						1, 2, 18
					),	
			'4' => array(
						1, 5, 18
					),	
			'5' => array(
						1, 17
					)	
		);

		$set_tab[40] = array(
			'1' => array(
						1, 5, 21, 29
					),
			'2' => array(
						1, 14, 26
					),
			'3' => array(
						1, 2, 26
					),	
			'4' => array(
						1, 5, 26
					),	
			'5' => array(
						1, 24
					)
		);

		$set_tab[42] = array(
			'1' => array(
						1, 5, 20, 29
					),
			'2' => array(
						1, 16, 28
					),
			'3' => array(
						1, 2, 28	
					),
			'4' => array(
						1, 5, 28	
					),	
			'5' => array(
						1, 26
					)
		);

		$set_tab[46] = array(
			'1' => array(
						1, 5, 24, 33
					),
			'2' => array(
						1, 20, 32
					),
			'3' => array(
						1, 2, 32
					),
			'4' => array(
						1, 5, 32
					),	
			'5' => array(
						1, 30
					)
		);

		$set_tab[48] = array(
			'1' => array(
						1, 5, 26, 35
					),
			'2' => array(
						1, 22, 34
					),
			'3' => array(
						1, 2, 34
					),
			'4' => array(
						1, 5, 34
					),	
			'5' => array(
						1, 32
					)	
		);

		$set_tab_pixel[32] = array(
			'1' => array(
						20, 90, 50, 60
					),
			'2' => array(
						55, 90, 75
					),
			'3' => array(
						1, 144, 75
					),
			'4' => array(
						20, 125, 75
					),
			'5' => array(
						110, 110
					)
		);
		$set_tab_pixel[40] = array(
			'1' => array(
						25, 115, 50, 60
					),
			'2' => array(
						85, 90, 75
					),
			'3' => array(
						1, 174, 75
					),
			'4' => array(
						25, 150, 75
					),
			'5' => array(
						125, 125
					)	
		);
		$set_tab_pixel[42] = array(
			'1' => array(
						25, 110, 60, 70
					),
			'2' => array(
						100, 90, 75
					),
			'3' => array(
						1, 189, 75
					),
			'4' => array(
						25, 165, 75
					),
			'5' => array(
						135, 130
					)		
		);

		$set_tab_pixel[46] = array(
			'1' => array(
						25, 140, 65, 75
					),
			'2' => array(
						140, 90, 75
					),
			'3' => array(
						1, 229, 75
					),
			'4' => array(
						25, 205, 75
					),
			'5' => array(
						155, 150
					)	
		);

		$set_tab_pixel[48] = array(
			'1' => array(
						25, 140, 65, 75
					),
			'2' => array(
						140, 90, 75
					),
			'3' => array(
						1, 229, 75
					),
			'4' => array(
						25, 205, 75
					),
			'5' => array(
						155, 150
					)	
		);
		
		$curr_settab = '';
		$curr_settab_pixel = '';
		if(!empty($set_tab[$printer_pin])){
			$curr_settab = $set_tab[$printer_pin];
			$curr_settab_pixel = $set_tab_pixel[$printer_pin];
		}else{
			$curr_settab = $set_tab[42];
			$curr_settab_pixel = $set_tab_pixel[42];
		}
		
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/desktop/css/report.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/desktop/css/report.css'; ?>" media="print"/>	
	</head>
<body>
<div class="report_area" style="padding:0px; margin:0px auto; text-align:left; border:0px solid #ccc; width:<?php echo $set_width[$printer_pin].'px'; ?>;">
		<?php
		if($data_printer['print_logo'] == 1 AND $print_logo == 1){
			?>
			<center>
				<img height="100" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $objCI->session->userdata('client_logo'); ?>">
			</center>
			<?php
		}

		$curr_settab_text = 0;
		$no_line = 0;
		foreach($exp_text as $kline => $data_line){
			
			if(!empty($data_line)){
				
				if(strstr($data_line, '|tab|')){
					//$data_line = str_replace("[","|*", $data_line);
					//$data_line = str_replace("]","*|", $data_line);
				}
				//check per-line
				$xplode_perline = explode("]", $data_line);

				$font_start_html = '';
				$separator_start_html = '';
				$create_new_line = true;
				
				$no_line++;
				//echo $no_line.'. -> '.$data_line.'<br/>';

				foreach($xplode_perline as $key => $dt_exp){
					
					//echo 'no_line = '.$no_line.', dt_exp = '.$dt_exp.'<br/>';
					
					//--FONT STYLE---------------
					//align
					if(in_array($dt_exp, $align_text)){
						if(empty($font_start_html)){
							$font_start_html .= '<div style="';
						}
						
						if($dt_exp == '[align=0'){
							$font_start_html .= 'text-align:left; ';
						}
						if($dt_exp == '[align=1'){
							$font_start_html .= 'text-align:center; ';
						}
						if($dt_exp == '[align=2'){
							$font_start_html .= 'text-align:right; ';
						}
						
						$dt_exp = '';
						$xplode_perline[$key] = '';
					}

					//size
					if(in_array($dt_exp, $size_text)){
						if(empty($font_start_html)){
							$font_start_html .= '<div style="';
						}
						
						if($dt_exp == '[size=0'){
							$font_start_html .= 'font-size:12px; ';
						}
						if($dt_exp == '[size=1'){
							$font_start_html .= 'font-size:14px; font-weight:bold; ';
						}
						if($dt_exp == '[size=2'){
							$font_start_html .= 'font-size:16px; font-weight:bold; ';
						}
						if($dt_exp == '[size=3'){
							$font_start_html .= 'font-size:18px; font-weight:bold; ';
						}
						
						$dt_exp = '';
						$xplode_perline[$key] = '';
						
					}
					//--FONT STYLE---------------
					
					//--SETTAB---------------
					if(in_array($dt_exp, $settab_text)){
						//echo $dt_exp.'<br/>';
						if($dt_exp == '[set_tab1'){
							$curr_settab_text = 1;
						}
						if($dt_exp == '[set_tab2'){
							$curr_settab_text = 2;
						}
						if($dt_exp == '[set_tab3'){
							$curr_settab_text = 3;
						}
						if($dt_exp == '[set_tab1a'){
							$curr_settab_text = 4;
						}
						if($dt_exp == '[set_tab1b'){
							$curr_settab_text = 5;
						}
						
						$dt_exp = '';
						$xplode_perline[$key] = '';
						$create_new_line = false;
						
					}
					
					//SEPARATOR
					if(!empty($dt_exp)){
						$jml_dt_exp = strlen($dt_exp);
						if(str_repeat("-", $jml_dt_exp) == $dt_exp){
							
							if(empty($separator_start_html)){
								$separator_start_html .= '<div style="border-top:1px solid #444; clear:both;"></div>';
							}
							
							$dt_exp = '';
							$xplode_perline[$key] = '';
						}
						
						if(str_repeat("_", $jml_dt_exp) == $dt_exp){
							
							if(empty($separator_start_html)){
								$separator_start_html .= '<div style="border-bottom:1px solid #444; clear:both;"></div>';
							}
							
							$dt_exp = '';
							$xplode_perline[$key] = '';
						}
					}
					
					
					//TAB
					//echo 'text = '.$dt_exp.'<br/>';
					if(strstr($dt_exp, '|tab|')){
						
						$exp_dt_tab = explode("|tab|", $dt_exp);
						
						//echo 'curr settab: '.$curr_settab_text.' -> exp text: '.count($exp_dt_tab).'<br/>';
						//print_r($curr_settab[$curr_settab_text]);
						//echo '<br/>';
						$curr_tab = 0;
						
						if(!empty($exp_dt_tab)){
							foreach($exp_dt_tab as $key_tab => $dt_tab){
								
								$font_align_style = '';
								
								$dt_tab = trim($dt_tab);
								$jumlah_text_tab = strlen($dt_tab);
								
								//get tab positions
								if(!empty($curr_settab)){
									
									//pixel
									$get_settab_pixel = $curr_settab_pixel[$curr_settab_text];
									$width_tab = $get_settab_pixel[$curr_tab];
									
									$get_settab = $curr_settab[$curr_settab_text];
									$jml_text_awal = $get_settab[$curr_tab];
									
									if(empty($get_settab[$curr_tab+1])){
										$jml_text_akhir = $printer_pin;
										//$curr_tab = 0;
									}else{
										$jml_text_akhir = $get_settab[$curr_tab+1];
									}
									
									$total_text = $jml_text_akhir-$jml_text_awal;
									
									if($curr_tab == 0){
										if($jml_text_akhir == $jml_text_awal){
											$total_text = 1;
										}
									}
									
									$curr_tab_2 = $curr_tab;
									
									$count_text_tab = true;
									if($curr_settab_text == 1){
										//$count_text_tab = false;
									}
									
									//RECURSIVE
									/*if($jumlah_text_tab > $total_text AND $count_text_tab == true){
										$curr_tab++;
										
										//$jml_text_awal = $get_settab[$curr_tab];
									
										if(empty($get_settab[$curr_tab+1])){
											$jml_text_akhir = $printer_pin;
											$curr_tab -= 1;
										}else{
											$jml_text_akhir = $get_settab[$curr_tab+1];
										}
										
										$total_text = $jml_text_akhir-$jml_text_awal;
										if($curr_tab == 0){
											if($jml_text_akhir == $jml_text_awal){
												$total_text = 1;
											}
										}
										
										//pixel
										if(!empty($get_settab_pixel[$curr_tab+1])){
											$width_tab += $get_settab_pixel[$curr_tab+1];
										}
										
									}*/
									
									$gap_text = $total_text - $jumlah_text_tab;
									
									if(empty($dt_tab)){
										$dt_tab = '&nbsp;';
									}
									
									
									if($curr_tab == (count($get_settab)-1)){
										$font_align_style = 'text-align:right;';
									}
									if($curr_settab_text == 1){
										if($curr_tab == (count($get_settab)-2)){
											$font_align_style = 'text-align:right;';
										}
									}
									
									//settab1b
									if($curr_settab_text == 5){
										if($curr_tab > 1){
											$font_align_style = 'text-align:right;';
										}
									}
									
									//persentase
									//$persentase_width = ceil(($total_text/$printer_pin)*$set_width);
									$exp_dt_tab[$key_tab] = '<div curr_tab="'.$curr_tab.'" style="width:'.$width_tab.'px; float:left; '.$font_align_style.'">'.$dt_tab.'</div>';
									
									$curr_tab++;
									
									if($curr_tab >= count($get_settab)){
										$curr_tab = 0;
										$exp_dt_tab[$key_tab] .= '<div style="clear:both;"></div>';
									}
								
								}
								
								
							}
							
							$dt_exp = implode("", $exp_dt_tab);
							$xplode_perline[$key] = implode("", $exp_dt_tab);
						}
						
						//echo $dt_exp.'</br>';
						//die();
						
					}
					
					
				}

				if(!empty($curr_tab)){
					//if($curr_tab > 0 AND $curr_tab < count($get_settab)){
						$curr_tab = 0;
						$xplode_perline[] = '<div style="clear:both;"></div>';
					//}
				}
				
				
				$exp_text[$kline] = implode("", $xplode_perline);

				//--SEPARATOR---------------
				if(!empty($separator_start_html)){
					$exp_text[$kline] = $separator_start_html;
				}
				
				//--FONT STYLE---------------
				if(!empty($font_start_html)){
					$font_start_html .= '">';
					
					$exp_text[$kline] = $font_start_html.$exp_text[$kline].'</div>';
					
				}
				
				if(empty($exp_text[$kline])){
					if($create_new_line == true){
						$exp_text[$kline] = '<br/>';
					}
					
				}
			}else{
				$exp_text[$kline] = "<div>&nbsp;</div>";
			}
			
			echo $exp_text[$kline];
		}

		?>
		&nbsp;<br/>
		&nbsp;
		</div>
		<?php
		if($do == 'print' AND $data_printer['print_method'] == 'JSPRINT'){
		?>
		<script type="text/javascript">

			if(!jsPrintSetup){
				alert('jsPrintSetup Belum Ada, Silahkan Tambahkan Addon Pada Firefox!');
				void(0);
			}
			
			var getPrinter =  jsPrintSetup.getPrintersList();
			var all_printer = getPrinter.split(",");
			var sel_printer_name = '<?php echo $data_printer['printer_name']; ?>';
			
			var is_available_printer = 0;
			for(x in all_printer){
				if(all_printer[x] == sel_printer_name){
					is_available_printer = 1;
				}
			}
			
			if(is_available_printer == 0){
				alert('Printer: '+sel_printer_name+' Tidak ditemukan! Silahkan Cek Print Manager');
				void(0);
			}
			
			jsPrintSetup.setPrinter(sel_printer_name);
			//jsPrintSetup.setOption('orientation', jsPrintSetup.kPortraitOrientation);
			jsPrintSetup.setOption('marginTop', 0);
			jsPrintSetup.setOption('marginBottom', 0);
			jsPrintSetup.setOption('marginLeft', 0);
			jsPrintSetup.setOption('marginRight', 0);
			jsPrintSetup.setOption('headerStrLeft', '');
			jsPrintSetup.setOption('headerStrCenter', '');
			jsPrintSetup.setOption('headerStrRight', '');
			jsPrintSetup.setOption('footerStrLeft', '');
			jsPrintSetup.setOption('footerStrCenter', '');
			jsPrintSetup.setOption('footerStrRight', '');
			
			//jsPrintSetup.setSilentPrint(1);
			jsPrintSetup.setShowPrintProgress(false);
			jsPrintSetup.printWindow(window);
			jsPrintSetup.setSilentPrint(1);
			
		</script> 
		<?php
		}else
		if($do == 'print' AND $data_printer['print_method'] == 'BROWSER'){
		?>
		<script type="text/javascript">
			window.print();
		</script>
		<?php
		}
		?>
</body>
</html>
<?php
	}
}


//printing_process_error
if(!function_exists('printing_process_error')){

	function printing_process_error($error = ''){
		$error = str_replace("<br/>","'\n+'",$error);
		$error = str_replace("<br>","'\n+'",$error);
		if(empty($error)){
			die();
		}
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/desktop/css/report.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/desktop/css/report.css'; ?>" media="print"/>	
	</head>
<body>
<div class="report_area" style="padding:0px; margin:0px auto; text-align:left; border:0px solid #ccc;">
<?php echo $error; ?>
</div>
<script type="text/javascript">
	alert('<?php echo $error;?>');
</script>
</body>
</html>
<?php
	}
}


?>