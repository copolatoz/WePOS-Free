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
 * Copyright (c) 2018 Angga Nugraha  (https://wepos.id)
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

//GET RESETAPP
if(!function_exists('doresetapp')){

	function doresetapp($data = array()){
		
		$prefix = config_item('db_prefix');
		if(empty($scope)){
			$scope =& get_instance();
		}	
		
		$scope->load->helper('directory');
		$scope->load->helper('file');
		
		$scope->db->query("TRUNCATE table ".$prefix."modules");
		$scope->db->query("insert  into ".$prefix."modules (`id`,`module_name`,`module_author`,`module_version`,`module_description`,`module_folder`,`module_controller`,`module_is_menu`,`module_breadcrumb`,`module_order`,`module_icon`,`module_shortcut_icon`,`module_glyph_icon`,`module_glyph_font`,`module_free`,`running_background`,`show_on_start_menu`,`show_on_right_start_menu`,`start_menu_path`,`start_menu_order`,`start_menu_icon`,`start_menu_glyph`,`show_on_context_menu`,`context_menu_icon`,`context_menu_glyph`,`show_on_shorcut_desktop`,`desktop_shortcut_icon`,`desktop_shortcut_glyph`,`show_on_preference`,`preference_icon`,`preference_glyph`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Setup Aplikasi','dev@wepos.id','v.1.0','','systems','setupAplikasiFree',1,'1. Master Aplikasi>Setup Aplikasi',1,'icon-cog','icon-cog','','',1,0,1,0,'1. Master Aplikasi>Setup Aplikasi',1000,'icon-cog','',0,'icon-cog','',1,'icon-cog','',0,'icon-cog','','administrator','2018-07-10 08:52:11','administrator','2018-07-30 00:00:00',1,0),(2,'Client Info','dev@wepos.id','v.1.0.0','Client Info','systems','clientInfo',0,'1. Master Aplikasi>Client Info',1,'icon-home','icon-home','','',1,0,1,0,'1. Master Aplikasi>Client Info',1101,'icon-home','',0,'icon-home','',1,'icon-home','',1,'icon-home','','administrator','2018-07-03 07:47:08','administrator','2018-07-03 07:47:08',1,0),(3,'Client Unit','dev@wepos.id','v.1.0','','systems','DataClientUnit',1,'1. Master Aplikasi>Client Unit',1,'icon-building','icon-building','','',1,0,1,0,'1. Master Aplikasi>Client Unit',1102,'icon-building','',0,'icon-building','',1,'icon-building','',1,'icon-building','','administrator','2018-07-10 08:52:10','administrator','2018-07-30 00:00:00',1,0),(4,'Data Structure','dev@wepos.id','v.1.0','','systems','DataStructure',1,'1. Master Aplikasi>Data Structure',1,'icon-building','icon-building','','',1,0,1,0,'1. Master Aplikasi>Data Structure',1103,'icon-building','',0,'icon-building','',1,'icon-building','',1,'icon-building','','administrator','2018-07-10 08:52:11','administrator','2018-07-30 00:00:00',1,0),(5,'Role Manager','dev@wepos.id','v.1.2','Role Manager','systems','Roles',1,'1. Master Aplikasi>Role Manager',1,'icon-role-modules','icon-role-modules','','',1,0,1,0,'1. Master Aplikasi>Role Manager',1201,'icon-role-modules','',0,'icon-role-modules','',1,'icon-role-modules','',1,'icon-role-modules','','administrator','2018-07-10 08:52:15','administrator','2018-07-30 00:00:00',1,0),(6,'Data User','dev@wepos.id','v.1.0','','systems','UserData',1,'1. Master Aplikasi>Data User',1,'icon-user-data','icon-user-data','','',1,0,1,0,'1. Master Aplikasi>Data User',1203,'icon-user-data','',0,'icon-user-data','',1,'icon-user-data','',0,'icon-user-data','','administrator','2018-07-10 08:52:11','administrator','2018-07-30 00:00:00',1,0),(7,'User Profile','dev@wepos.id','v.1.0','','systems','UserProfile',1,'1. Master Aplikasi>User Profile',1,'user','user','','',1,0,1,1,'1. Master Aplikasi>User Profile',1301,'user','',1,'user','',1,'user','',1,'user','','administrator','2018-07-10 08:52:17','administrator','2018-07-30 00:00:00',1,0),(8,'Desktop Shortcuts','dev@wepos.id','v.1.0','Shortcuts Manager to Desktop','systems','DesktopShortcuts',1,'1. Master Aplikasi>Desktop Shortcuts',1,'icon-preferences','icon-preferences','','',1,0,1,1,'1. Master Aplikasi>Desktop Shortcuts',1302,'icon-preferences','',1,'icon-preferences','',1,'icon-preferences','',1,'icon-preferences','','administrator','2018-07-10 08:52:12','administrator','2018-07-30 00:00:00',1,0),(9,'QuickStart Shortcuts','dev@wepos.id','v.1.0','','systems','QuickStartShortcuts',0,'1. Master Aplikasi>QuickStart Shortcuts',1,'icon-preferences','icon-preferences','','',1,0,1,0,'1. Master Aplikasi>QuickStart Shortcuts',1303,'icon-preferences','',0,'icon-preferences','',1,'icon-preferences','',1,'icon-preferences','','administrator','2018-07-24 07:43:19','administrator','2018-07-21 09:16:19',1,0),(10,'Refresh Aplikasi','dev@wepos.id','v.1.0.0','','systems','refreshModule',0,'Refresh Aplikasi',1,'icon-refresh','icon-refresh','','',1,0,0,0,'Refresh Aplikasi',1304,'icon-refresh','',0,'icon-refresh','',1,'icon-refresh','',0,'icon-refresh','','administrator','2018-07-17 15:00:19','administrator','2018-07-17 15:00:19',1,0),(11,'Lock Screen','dev@wepos.id','v.1.0.0','User Lock Screen','systems','lockScreen',0,'LockScreen',1,'icon-grid','icon-grid','','',1,1,0,0,'LockScreen',1305,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 01:40:20','administrator','2018-07-30 00:00:00',1,0),(12,'Logout','dev@wepos.id','v.1.0.0','Just Logout Module','systems','logoutModule',0,'Logout',1,'icon-grid','icon-grid','','',1,1,0,0,'Logout',1306,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 01:36:16','administrator','2018-07-20 15:06:35',1,0),(13,'WePOS Update','dev@wepos.id','v.1.0.0','WePOS Update','systems','weposUpdate',0,'1. Master Aplikasi>WePOS Update',1,'icon-sync','icon-grid','','',1,0,1,0,'1. Master Aplikasi>WePOS Update',1401,'icon-sync','',0,'icon-sync','',1,'icon-sync','',1,'icon-sync','','administrator','2018-07-22 08:00:58','administrator','2018-07-22 08:00:58',1,0),(14,'Notifikasi Sistem','dev@wepos.id','v.1.0.0','Notifikasi Sistem','systems','systemNotify',0,'Notifikasi Sistem',1,'icon-info','icon-info','','',1,1,0,0,'Notifikasi Sistem',1402,'icon-info','',0,'icon-info','',0,'icon-info','',0,'icon-info','','administrator','2018-07-22 08:00:58','administrator','2018-07-22 08:00:58',1,0),(15,'Menu Category','dev@wepos.id','v.1.0','','master_pos','productCategory',0,'2. Master POS>Menu Category',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Menu Category',2101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:26:07','administrator','2018-07-30 00:00:00',1,0),(16,'Master Menu & Package','dev@wepos.id','v.1.0','Master Menu & Package','master_pos','masterProduct',0,'2. Master POS>Master Menu',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Menu',2102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:24:38','administrator','2018-07-30 00:00:00',1,0),(19,'Master Warehouse','dev@wepos.id','v.1.0.0','Master Warehouse','master_pos','masterStoreHouse',0,'2. Master POS>Master Warehouse',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Warehouse',2201,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:24:56','administrator','2018-07-21 20:05:16',1,0),(20,'Master Unit','dev@wepos.id','v.1.0.0','Master Unit','master_pos','masterUnit',0,'2. Master POS>Master Unit',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Unit',2202,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:25:13','administrator','2018-07-12 22:15:29',1,0),(21,'Master Supplier','dev@wepos.id','v.1.0.0','Master Supplier','master_pos','masterSupplier',0,'2. Master POS>Supplier',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Supplier',2203,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:25:04','administrator','2018-07-21 20:04:34',1,0),(22,'Item Category','dev@wepos.id','v.1.0.0','Item Category','master_pos','itemCategory',0,'2. Master POS>Item Category',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Item Category',2210,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-05 00:36:29','administrator','2018-07-15 20:31:54',1,0),(23,'Item Sub Category','dev@wepos.id','v.1.0.0','Item Sub Category','master_pos','itemSubCategory',0,'2. Master POS>Item Sub Category',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Item Sub Category',2211,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-05 00:36:29','administrator','2018-07-15 20:31:54',1,0),(24,'Master Item','dev@wepos.id','v.1.0.0','Data Item','master_pos','masterItemCafe',0,'2. Master POS>Master Item',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Item',2230,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-13 14:04:34','administrator','2018-07-13 14:04:34',1,0),(25,'Discount Planner','dev@wepos.id','v.1.0','Planning All discount Menu','master_pos','discountPlannerFree',0,'2. Master POS>Discount Planner',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Discount Planner',2301,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:26:01','administrator','2018-07-30 00:00:00',1,0),(26,'Printer Manager','dev@wepos.id','v.1.0','Printer Manager','master_pos','masterPrinter',0,'2. Master POS>Printer Manager',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Printer Manager',2302,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 03:24:50','administrator','2018-07-21 20:06:25',1,0),(28,'Master Bank','dev@wepos.id','v.1.0.0','Master Bank','master_pos','masterBank',0,'2. Master POS>Master Bank',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Bank',2304,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:24:53','administrator','2018-07-21 20:05:03',1,0),(31,'Master Floor Plan','dev@wepos.id','v.1.0','','master_pos','masterFloorplan',0,'2. Master POS>Master Floor Plan',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Floor Plan',2307,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 17:26:51','administrator','2018-07-30 00:00:00',1,0),(32,'Master Room','dev@wepos.id','v.1.0','Master Room','master_pos','masterRoom',0,'2. Master POS>Master Room',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Room',2308,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:24:38','administrator','2018-07-30 00:00:00',1,0),(33,'Master Table','dev@wepos.id','v.1.0.0','','master_pos','masterTable',0,'2. Master POS>Master Table',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Table',2309,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 17:26:54','administrator','2018-07-30 00:00:00',1,0),(34,'Table Inventory','dev@wepos.id','v.1.0.0','','master_pos','tableInventory',0,'2. Master POS>Table Inventory',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Table Inventory',2310,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 17:26:59','administrator','2018-07-30 00:00:00',1,0),(35,'Warehouse Access','dev@wepos.id','v.1.0.0','Warehouse Access','master_pos','warehouseAccess',0,'2. Master POS>User Access>Warehouse Access',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>User Access>Warehouse Access',2401,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-27 19:23:32','administrator','2018-07-21 20:02:49',1,0),(36,'Printer Access','dev@wepos.id','v.1.0.0','Printer Access','master_pos','printerAccess',0,'2. Master POS>User Access>Printer Access',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>User Access>Printer Access',2402,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 06:43:42','administrator','2018-07-21 20:02:38',1,0),(37,'Supervisor Access','dev@wepos.id','v.1.0.0','Supervisor Access','master_pos','supervisorAccess',0,'2. Master POS>User Access>Supervisor Access',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>User Access>Supervisor Access',2403,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-11 22:53:04','administrator','2018-07-21 20:02:58',1,0),(39,'Open Cashier (Shift)','dev@wepos.id','v.1.0','','cashier','openCashierShift',0,'3. Cashier & Reservation>Open Cashier (Shift)',7,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Reservation>Open Cashier (Shift)',3001,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:28:12','administrator','2018-07-30 00:00:00',1,0),(40,'Close Cashier (Shift)','dev@wepos.id','v.1.0','','cashier','closeCashierShift',0,'3. Cashier & Reservation>Close Cashier (Shift)',7,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Reservation>Close Cashier (Shift)',3002,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:28:17','administrator','2018-07-30 00:00:00',1,0),(41,'List Open Close Cashier','dev@wepos.id','v.1.0.0','','cashier','listOpenCloseCashier',0,'3. Cashier & Reservation>List Open Close Cashier',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Reservation>List Open Close Cashier',3003,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-20 07:59:55','administrator','2018-07-20 07:59:55',1,0),(42,'Cashier','dev@wepos.id','v.1.0','Cashier','cashier','billingCashier',0,'3. Cashier & Reservation>Cashier',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Reservation>Cashier',3101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 03:28:03','administrator','2018-07-22 12:58:59',1,0),(48,'Cashier Receipt Setup','dev@wepos.id','v.1.0.0','Cashier Receipt Setup','cashier','cashierReceiptSetup',0,'3. Cashier & Reservation>Cashier Receipt Setup',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Reservation>Cashier Receipt Setup',3301,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-11 06:13:49','administrator','2018-07-22 12:59:09',1,0),(51,'Purchase Order/Pembelian','dev@wepos.id','v.1.0.0','Purchase Order/Pembelian','purchase','purchaseOrder',0,'4. Purchase & Receive>Purchase Order/Pembelian',1,'icon-grid','icon-grid','','',1,0,1,0,'4. Purchase & Receive>Purchase Order/Pembelian',4201,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:27:18','administrator','2018-07-15 15:07:08',1,0),(52,'Receiving List/Penerimaan Barang','dev@wepos.id','v.1.0.0','Receiving List/Penerimaan Barang','inventory','receivingList',0,'4. Purchase & Receive>Receiving List/Penerimaan Barang',1,'icon-grid','icon-grid','','',1,0,1,0,'4. Purchase & Receive>Receiving List/Penerimaan Barang',4301,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 12:05:57','administrator','2018-07-22 13:04:22',1,0),(53,'Daftar Stok Barang','dev@wepos.id','v.1.0.0','Daftar Stok Barang','inventory','listStock',0,'5. Inventory>Daftar Stok Barang',1,'icon-grid','icon-grid','','',1,0,1,0,'5. Inventory>Daftar Stok Barang',5101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 06:43:42','administrator','2018-07-24 13:22:20',1,0),(58,'Stock Opname','dev@wepos.id','v.1.0.0','Module Stock Opname','inventory','stockOpname',0,'5. Inventory>Stock Opname',1,'icon-grid','icon-grid','','',1,0,1,0,'5. Inventory>Stock Opname',5401,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 12:06:05','administrator','2018-07-24 13:22:51',1,0),(77,'Closing Sales','dev@wepos.id','v.1.0.0','Closing Sales','audit_closing','closingSales',0,'8. Closing & Audit>Closing Sales',1,'icon-grid','icon-grid','','',1,0,1,0,'8. Closing & Audit>Closing Sales',8101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 21:43:42','administrator','2018-07-03 21:43:42',1,0),(78,'Closing Purchasing','dev@wepos.id','v.1.0.0','Closing Purchasing','audit_closing','closingPurchasing',0,'8. Closing & Audit>Closing Purchasing',1,'icon-grid','icon-grid','','',1,0,1,0,'8. Closing & Audit>Closing Purchasing',8102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 21:47:56','administrator','2018-07-03 21:51:27',1,0),(81,'Auto Closing Generator','dev@wepos.id','v.1.0.0','Auto Closing Generator','monitoring','generateAutoClosing',0,'9. Sync, Backup, Generate>Auto Closing Generator',1,'icon-grid','icon-grid','','',1,0,1,0,'9. Sync, Backup, Generate>Auto Closing Generator',9102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 21:43:42','administrator','2018-07-03 21:43:42',1,0),(82,'Syncronize Master Data Store','dev@wepos.id','v.1.0.0','Syncronize Master Data Store','sync_backup','syncData',0,'9. Sync, Backup, Generate>Syncronize Master Data Store',1,'icon-sync','icon-sync','','',1,0,1,0,'9. Sync, Backup, Generate>Syncronize Master Data Store',9201,'icon-sync','',0,'icon-sync','',1,'icon-sync','',1,'icon-sync','','administrator','2018-07-25 12:14:44','administrator','2018-07-26 21:05:47',1,0),(83,'Backup Transaksi Store','dev@wepos.id','v.1.0.0','Backup Transaksi Store','sync_backup','backupTrx',0,'9. Sync, Backup, Generate>Backup Transaksi Store',1,'icon-backup','icon-backup','','',1,0,1,0,'9. Sync, Backup, Generate>Backup Transaksi Store',9202,'icon-backup','',0,'icon-backup','',1,'icon-backup','',1,'icon-backup','','administrator','2018-07-25 12:17:26','administrator','2018-07-26 21:06:01',1,0),(85,'Sync & Backup','dev@wepos.id','v.1.0.0','Sync & Backup','sync_backup','syncBackup',0,'9. Sync, Backup, Generate>Sync & Backup',1,'icon-sync','icon-sync','','',1,0,1,0,'9. Sync, Backup, Generate>Sync & Backup',9203,'icon-sync','',0,'icon-sync','',1,'icon-sync','',1,'icon-sync','','administrator','2018-07-25 12:14:44','administrator','2018-07-26 21:05:47',1,0),(86,'Sales Report','dev@wepos.id','v.1.0','Sales Report','billing','reportSales',0,'6. Reports>Sales (Billing)>Sales Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Sales Report',6101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-11 01:28:24','administrator','2018-07-17 17:01:16',1,0),(89,'Sales Report (Recap)','dev@wepos.id','v.1.0.0','','billing','reportSalesRecap',0,'6. Reports>Sales (Billing)>Sales Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Sales Report (Recap)',6104,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:30:29','administrator','2018-07-24 16:38:02',1,0),(90,'Sales By Discount','dev@wepos.id','v.1.0.0','Sales By Discount','billing','salesByDiscount',0,'6. Reports>Sales (Billing)>Sales By Discount',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Sales By Discount',6105,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-15 20:43:42','administrator','2018-07-15 20:43:42',1,0),(92,'Sales Summary Report (SSR)','dev@wepos.id','v.1.0.0','Sales Summary Report (SSR)','billing','salesSummaryReport',0,'6. Reports>Sales (Billing)>Sales Summary Reports (SSR)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Sales Summary Reports (SSR)',6108,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-15 20:43:42','administrator','2018-07-15 20:43:42',1,0),(99,'Cancel Billing Report','dev@wepos.id','v.1.0.0','','billing','reportCancelBill',0,'6. Reports>Sales (Billing)>Report Cancel Billing',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Report Cancel Billing',6113,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-19 09:45:34','administrator','2018-07-24 16:26:54',1,0),(102,'Sales By Menu','dev@wepos.id','v.1.0.0','Sales By Menu','billing','reportSalesByMenu',0,'6. Reports>Sales (Menu)>Sales By Menu',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Menu)>Sales By Menu',6120,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-09 05:51:55','administrator','2018-07-17 17:47:33',1,0),(106,'Sales Profit Report','dev@wepos.id','v.1.0.0','','billing','reportSalesProfit',0,'6. Reports>Sales (Profit)>Sales Profit Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit Report',6131,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:46:57','administrator','2018-07-24 17:21:51',1,0),(109,'Sales Profit Report (Recap)','dev@wepos.id','v.1.0.0','','billing','reportSalesProfitRecap',0,'6. Reports>Sales (Profit)>Sales Profit Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit Report (Recap)',6134,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:58:17','administrator','2018-07-24 17:23:59',1,0),(110,'Sales Profit By Menu','dev@wepos.id','v.1.0.0','Sales Profit By Menu','billing','reportSalesProfitByMenu',0,'6. Reports>Sales (Profit)>Sales Profit By Menu',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit By Menu',6135,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:53:21','administrator','2018-07-17 19:38:07',1,0),(119,'Bagi Hasil','dev@wepos.id','v.1.0.0','Bagi Hasil Detail','billing','reportSalesBagiHasil',0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil',6301,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-15 06:43:42','administrator','2018-07-15 06:43:42',1,0),(120,'Bagi Hasil (Recap)','dev@wepos.id','v.1.0.0','Bagi Hasil (Recap)','billing','reportSalesBagiHasilRecap',0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil (Recap)',6302,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-15 06:43:42','administrator','2018-07-15 06:43:42',1,0),(125,'Purchase Report','dev@wepos.id','v.1.0.0','Purchase Report','purchase','reportPurchase',0,'6. Reports>Purchase/Pembelian>Purchase Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Purchase/Pembelian>Purchase Report',6401,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-16 21:28:58','administrator','2018-07-09 19:08:45',1,0),(127,'Purchase Report (Recap)','dev@wepos.id','v.1.0.0','Purchase Report (Recap)','purchase','reportPurchaseRecap',0,'6. Reports>Purchase/Pembelian>Purchase Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Purchase/Pembelian>Purchase Report (Recap)',6403,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 13:23:40','administrator','2018-07-09 19:08:25',1,0),(128,'Last Purchase Price','dev@wepos.id','v.1.0.0','Last Purchase Price','purchase','reportLastPurchasePrice',0,'6. Reports>Purchase/Pembelian>Last Purchase Price',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Purchase/Pembelian>Last Purchase Price',6404,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 13:23:40','administrator','2018-07-09 19:08:25',1,0),(129,'Receiving Report','dev@wepos.id','v.1.0.0','Receiving Report','inventory','reportReceiving',0,'6. Reports>Receiving (In)>Receiving Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Receiving (In)>Receiving Report',6501,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 13:31:50','administrator','2018-07-09 19:00:32',1,0),(132,'Receiving Report (Recap)','dev@wepos.id','v.1.0.0','Receiving Report (Recap)','inventory','reportReceivingRecap',0,'6. Reports>Receiving (In)>Receiving Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Receiving (In)>Receiving Report (Recap)',6504,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-09 15:57:19','administrator','2018-07-09 19:01:16',1,0),(145,'Monitoring Stock (Actual)','dev@wepos.id','v.1.0.0','Monitoring Stock (Actual)','inventory','reportMonitoringStock',0,'6. Reports>Warehouse>Monitoring Stock (Actual)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Warehouse>Monitoring Stock (Actual)',6642,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-11 23:44:12','administrator','2018-07-18 00:45:36',1,0),(146,'Kartu Stok','dev@wepos.id','v.1.0.0','Kartu Stok','inventory','kartuStok',0,'6. Reports>Warehouse>Kartu Stock',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Warehouse>Kartu Stock',6643,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 06:43:42','administrator','2018-07-18 00:46:03',1,0);");
		
		$scope->db->delete($prefix.'options',"option_var LIKE 'mlog_%'");
	
		$opt_var = array(
			'is_cloud',
		);
		
		$get_opt = get_option_value($opt_var);
		
		//copy module
		if (empty($get_opt['is_cloud'])) {
			
			$minjs_path = BASE_PATH.'/apps.min/modules'; 
			delete_files($minjs_path, TRUE);
			$zip = new ZipArchive;
			
			$apps_default = BASE_PATH.'/apps.min/core/modules.default';
			if($zip->open($apps_default) === TRUE) 
			{
				if (!is_dir($minjs_path)) {
					@mkdir($minjs_path, 0777, TRUE);
				}

				$zip->extractTo($minjs_path);
				$zip->close();
				
			}
				
			$appmod_path = APPPATH.'/modules'; 
			delete_files($appmod_path, TRUE);
			
			$zip = new ZipArchive;
			$file_default = APPPATH.'/core/modules.default';
			if($zip->open($file_default) === TRUE) 
			{
				if (!is_dir($appmod_path)) {
					@mkdir($appmod_path, 0777, TRUE);
				}

				$zip->extractTo($appmod_path);
				$zip->close();
				
			}
			
		}
		
	}
	
}

//GET STATUS CLOSING
if(!function_exists('wepos_log_update')){
	function wepos_log_update($force_update = false){
		
		if(empty($scope)){
			$scope =& get_instance();
		}
			
		$scope->load->library('curl');
		$scope->load->helper('directory');
		$scope->load->helper('file');
		
		$opt_var = array(
			'merchant_key',
			'merchant_cor_token',
			'merchant_acc_token',
			'merchant_mkt_token',
			'produk_key',
			'produk_nama',
			'produk_expired',
			'merchant_last_checkon',
			'is_cloud',
		);
		
		$get_opt = get_option_value($opt_var);
		
		$merchant_key = '';
		if(empty($get_opt['merchant_key'])){
			$get_opt['merchant_key'] = '';
			return true;
		}else{
			$merchant_key = $get_opt['merchant_key'];
		}
		if(empty($get_opt['merchant_cor_token'])){
			$get_opt['merchant_cor_token'] = '';
		}
		if(empty($get_opt['merchant_acc_token'])){
			$get_opt['merchant_acc_token'] = '';
		}
		if(empty($get_opt['merchant_mkt_token'])){
			$get_opt['merchant_mkt_token'] = '';
		}
		if(empty($get_opt['produk_nama'])){
			$get_opt['produk_nama'] = 'Gratis / Free';
		}
		if(empty($get_opt['produk_expired'])){
			$get_opt['produk_expired'] = 'unlimited';
		}
		
		$today_check = strtotime(date("d-m-Y H:i:s"));
		
		$update_last_check = false;
		if(empty($get_opt['merchant_last_checkon'])){
			$get_opt['merchant_last_checkon'] = 0;
			$update_last_check = true;
		}else{
			$merchant_last_checkon_7 = $get_opt['merchant_last_checkon'] + (ONE_DAY_UNIX*7);
			if($merchant_last_checkon_7 < $today_check){
				$update_last_check = true;
			}
		}
		
		if($force_update == true){
			$update_last_check = true;
		}
		
		if($update_last_check){
			
			$opt_var = array(
				'merchant_last_checkon' => $today_check
			);
			update_option($opt_var);
		
			if($get_opt['produk_nama'] != 'Gratis / Free'){
				
				if($get_opt['merchant_mkt_token'] < $today_check){
					
					$opt_var = array(
						'mlog_'.$merchant_key,
						'is_cloud'
					);
					$get_opt = get_option_value($opt_var);
					
					$mlog = '';
					if(empty($get_opt['mlog_'.$merchant_key])){
						$mlog = $get_opt['mlog_'.$merchant_key];
					}
					
					$resetapp = array(
						'merchant_cor_token'=> '',
						'merchant_acc_token'=> '',
						'merchant_mkt_token'=> '',
						'produk_key' 		=> 'GFR-'.strtotime(date("d-m-Y")),
						'produk_nama'		=> 'Gratis / Free',
						'produk_expired'	=> 'unlimited',
						'mlog_'.$merchant_key => ''
					);
					update_option($resetapp);
					
					if(!empty($mlog) AND empty($get_opt['is_cloud'])){
						$minjs_path = BASE_PATH.'/apps.min/modules'; 
						$mlog_json = json_decode($mlog);
						if(!empty($mlog_json)){
							foreach($mlog_json as $v){
								$file_minjs = $minjs_path.'/'.$v;
								@unlink($file_minjs);
							}
						}
					}
					
					$reset = true;
					$allow_reset = true;
					
				}else{
					
					if($force_update){
						
						$mktime_dc = strtotime(date("d-m-Y H:i:s"));
						
						$module_path = RESOURCES_PATH.$merchant_key;
						$file_download = RESOURCES_PATH.$merchant_key.'.zip';
						$fp = fopen ($file_download, 'w+'); 
						
						if($fp){
							$client_url = config_item('website').'/client-download?_dc='.$mktime_dc;
						
							$post_data = array(
								'merchant_key'	=> $merchant_key
							);
							
							$wepos_crt = ASSETS_PATH.config_item('wepos_crt_file');
							$scope->curl->create($client_url);
							$scope->curl->option('connecttimeout', 600);
							$scope->curl->option('RETURNTRANSFER', 1);
							$scope->curl->option('SSL_VERIFYPEER', 1);
							$scope->curl->option('SSL_VERIFYHOST', 2);
							//$scope->curl->option('SSLVERSION', 3);
							$scope->curl->option('POST', 1);
							$scope->curl->option('POSTFIELDS', $post_data);
							$scope->curl->option('CAINFO', $wepos_crt);
							$scope->curl->option('FILE', $fp);
							$curl_ret = $scope->curl->execute();
							
							$scope->curl->close();
							fclose($fp);
							
							//unzip
							$zip = new ZipArchive;
				 
							if ($zip->open($file_download) === TRUE) 
							{
								if (!is_dir($module_path)) {
									@mkdir($module_path, 0777, TRUE);
								}

								$zip->extractTo($module_path);
								$zip->close();
								
								@unlink($file_download);
							}
							
							$appmin_folder = '';
							
							//install
							$dir_mod = directory_map($module_path, 1);
							if(count($dir_mod) > 0)
							{
								foreach($dir_mod as $file_dl)
								{
									if($file_dl == 'db.sql'){
										$sql_contents = file_get_contents($module_path.'/'.$file_dl);
										$sql_contents = explode(";", $sql_contents);
										@unlink($module_path.'/'.$file_dl);
										
										//running query
										foreach($sql_contents as $query)
										{
											$query = trim($query);
											if(!empty($query)){
												@$scope->db->query($query);
											}
										}
										
									}else
									if($file_dl == 'modules.file'){
										
										if(empty($get_opt['is_cloud'])){
											
											$appmod_path = APPPATH.'/modules'; 
											delete_files($appmod_path, TRUE);
											
											$module_file = $module_path.'/'.$file_dl;
											
											$zip = new ZipArchive;
											if($zip->open($module_file) === TRUE) 
											{
												if (!is_dir($appmod_path)) {
													@mkdir($appmod_path, 0777, TRUE);
												}

												$zip->extractTo($appmod_path);
												$zip->close();
											}
											
											
										}
										
										@unlink($module_path.'/'.$file_dl);
										
									}else
									if($file_dl == 'apps.min'){
										
										if(empty($get_opt['is_cloud'])){
											
											$minjs_path = BASE_PATH.'/apps.min/modules'; 
											delete_files($minjs_path, TRUE);
											
											$module_file = $module_path.'/'.$file_dl;
											
											$zip = new ZipArchive;
											if($zip->open($module_file) === TRUE) 
											{
												if (!is_dir($minjs_path)) {
													@mkdir($minjs_path, 0777, TRUE);
												}

												$zip->extractTo($minjs_path);
												$zip->close();
											}
											
											
										}
										
										@unlink($module_path.'/'.$file_dl);
									}
									
								}
							}
							
							
							$filelog = array();
							$minjs_path = BASE_PATH.'/apps.min/modules'; 
							//copy module
							if (is_dir($minjs_path)){
								
								$dir_items = directory_map($minjs_path, 1);
								
								if(count($dir_items) > 0)
								{
									foreach($dir_items as $v)
									{
										$filelog[] = $v;
									}
								}
								
								@rmdir($module_path);
							}
							
							if(!empty($filelog)){
								$filelog_update = json_encode($filelog);
								$opt_var = array(
									'mlog_'.$merchant_key => $filelog_update
								);
								update_option($opt_var);
								
							}
							
							
							return "force_update";
							
						}
					}
					
				}
			}else{
				
				$reset = true;
				$allow_reset = true;
				
			}
					
			
			
			if($reset == true AND $allow_reset == true){
				
				doresetapp();
				return "allow_reset";
				
			}
			
					
		}
		
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