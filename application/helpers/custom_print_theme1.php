<?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); 

$objCI =& get_instance();

$is_preview_error = false;
$order_apps = $objCI->input->get_post('order_apps', true);	
if(!empty($order_apps)){
	$r = array('success' => true);
	echo json_encode($r); 
	$is_preview_error = true;
	//die();
}

if(empty($data_printer)){
	echo 'Data Printer Tidak Diketahui!';
	$is_preview_error = true;
	//die();
}
if(empty($print_content)){
	echo 'Konten Print Kosong!';
	$is_preview_error = true;
	//die();
}

//echo '<pre>';
//print_r($print_content);
//die();

if($is_preview_error == false){		
$print_content = str_replace("[tab]","|tab|", $print_content);
//explode
$exp_text = explode("\n", $print_content);


$printer_pin = $data_printer['printer_pin'];
$printer_pin = trim(str_replace("CHAR", "", $printer_pin));

$align_text = array('[align=0','[align=1','[align=2');
$size_text = array('[size=0','[size=1','[size=2','[size=2');
$settab_text = array('[list_order_tipe1','[list_order_tipe2','[clear_set_tab','[set_tab1','[set_tab2','[set_tab3','[set_tab1a','[set_tab1b','[tab');
$set_tab = array();
$set_tab_chr = array();
$set_tab_pxl = array();

$set_width = array(
	'32' => 192,
	'40' => 240,
	'42' => 252,
	'46' => 276,
	'48' => 288,
);	

//CHAR PIN
$set_tab[32] = array(
	'1' => array(
				1, 5, 17, 24
			),
	'2' => array(
				1, 4, 22
			),
	'3' => array(
				1, 2, 22
			),	
	'4' => array(
				1, 5, 22
			),	
	'5' => array(
				1, 20
			),	
	'6' => array(
				1, 6, 22
			),	
	'7' => array(
				1, 2, 22
			),
	'8' => array(
				1, 31
			)	
);

$set_tab[40] = array(
	'1' => array(
				1, 5, 21, 30
			),
	'2' => array(
				1, 10, 28
			),
	'3' => array(
				1, 2, 28
			),	
	'4' => array(
				1, 5, 28
			),	
	'5' => array(
				1, 26
			),	
	'6' => array(
				1, 6, 28
			),	
	'7' => array(
				1, 2, 28
			),
	'8' => array(
				1, 39
			)	
);

$set_tab[42] = array(
	'1' => array(
				1, 5, 23, 32
			),
	'2' => array(
				1, 12, 30
			),
	'3' => array(
				1, 2, 30	
			),
	'4' => array(
				1, 5, 30	
			),	
	'5' => array(
				1, 28
			),	
	'6' => array(
				1, 6, 30
			),	
	'7' => array(
				1, 2, 30
			),
	'8' => array(
				1, 41
			)
);

$set_tab[46] = array(
	'1' => array(
				1, 5, 25, 35
			),
	'2' => array(
				1, 16, 34
			),
	'3' => array(
				1, 2, 34
			),
	'4' => array(
				1, 5, 34
			),	
	'5' => array(
				1, 32
			),	
	'6' => array(
				1, 6, 34
			),	
	'7' => array(
				1, 2, 34
			),
	'8' => array(
				1, 45
			)
);

$set_tab[48] = array(
	'1' => array(
				1, 5, 27, 37
			),
	'2' => array(
				1, 18, 36
			),
	'3' => array(
				1, 2, 36
			),
	'4' => array(
				1, 5, 36
			),	
	'5' => array(
				1, 34
			),	
	'6' => array(
				1, 6, 36
			),	
	'7' => array(
				1, 2, 36
			),
	'8' => array(
				1, 47
			)
);

$set_tab_chr[32] = array(
	'1' => array(
				4, 12, 7, 9
			),
	'2' => array(
				3, 18, 11
			),
	'3' => array(
				1, 20, 11
			),	
	'4' => array(
				4, 17, 11
			),	
	'5' => array(
				19, 13
			),	
	'6' => array(
				5, 16, 11
			),	
	'7' => array(
				1, 20, 11
			),	
	'8' => array(
				31,1
			)	
);

$set_tab_chr[40] = array(
	'1' => array(
				4, 16, 9, 11
			),
	'2' => array(
				9, 18, 13
			),
	'3' => array(
				1, 26, 13
			),	
	'4' => array(
				4, 23, 13
			),	
	'5' => array(
				25, 15
			),	
	'6' => array(
				5, 24, 11
			),	
	'7' => array(
				1, 28, 11
			),	
	'8' => array(
				39,1
			)
);

$set_tab_chr[42] = array(
	'1' => array(
				4, 18, 9, 11
			),
	'2' => array(
				11, 18, 13
			),
	'3' => array(
				1, 28, 13	
			),
	'4' => array(
				4, 25, 13	
			),	
	'5' => array(
				27, 15
			),	
	'6' => array(
				5, 26, 11
			),	
	'7' => array(
				1, 30, 11
			),	
	'8' => array(
				41,1
			)
);

$set_tab_chr[46] = array(
	'1' => array(
				4, 20, 10, 12
			),
	'2' => array(
				15, 18, 13
			),
	'3' => array(
				1, 32, 13
			),
	'4' => array(
				4, 29, 13
			),	
	'5' => array(
				31, 15
			),	
	'6' => array(
				5, 28, 13
			),	
	'7' => array(
				1, 32, 13
			),	
	'8' => array(
				45,1
			)
);

$set_tab_chr[48] = array(
	'1' => array(
				4, 22, 10, 12
			),
	'2' => array(
				17, 18, 13
			),
	'3' => array(
				1, 34, 13
			),
	'4' => array(
				4, 31, 13
			),	
	'5' => array(
				33, 15
			),	
	'6' => array(
				5, 30, 13
			),	
	'7' => array(
				1, 34, 13
			),	
	'8' => array(
				47,1
			)
);

$set_tab_pxl[32] = array(
	'1' => array(
				24, 72, 42, 54
			),
	'2' => array(
				18, 108, 66
			),
	'3' => array(
				6, 120, 66
			),	
	'4' => array(
				24, 102, 66
			),	
	'5' => array(
				114, 78
			),	
	'6' => array(
				30, 96, 66
			),	
	'7' => array(
				6, 120, 66
			),	
	'8' => array(
				186, 6
			)	
);

$set_tab_pxl[40] = array(
	'1' => array(
				24, 96, 54, 66
			),
	'2' => array(
				54, 108, 78
			),
	'3' => array(
				6, 156, 78
			),	
	'4' => array(
				24, 138, 78
			),	
	'5' => array(
				150, 90
			),	
	'6' => array(
				30, 144, 66
			),	
	'7' => array(
				6, 168, 66
			),	
	'8' => array(
				234, 6
			)	
);

$set_tab_pxl[42] = array(
	'1' => array(
				24, 108, 54, 66
			),
	'2' => array(
				66, 108, 78
			),
	'3' => array(
				6, 168, 78	
			),
	'4' => array(
				24, 150, 78	
			),	
	'5' => array(
				162, 90
			),	
	'6' => array(
				30, 156, 66
			),	
	'7' => array(
				6, 180, 66
			),	
	'8' => array(
				246, 6
			)
);

$set_tab_pxl[46] = array(
	'1' => array(
				24, 120, 60, 72
			),
	'2' => array(
				90, 108, 78
			),
	'3' => array(
				6, 192, 78
			),
	'4' => array(
				24, 174, 78
			),	
	'5' => array(
				186, 90
			),	
	'6' => array(
				30, 168, 78
			),	
	'7' => array(
				6, 192, 78
			),	
	'8' => array(
				270, 6
			)
);

$set_tab_pxl[48] = array(
	'1' => array(
				24, 132, 60, 72
			),
	'2' => array(
				102, 108, 78
			),
	'3' => array(
				6, 204, 78
			),
	'4' => array(
				24, 186, 78
			),	
	'5' => array(
				198, 90
			),	
	'6' => array(
				30, 180, 78
			),	
	'7' => array(
				6, 204, 78
			),	
	'8' => array(
				282, 6
			)
);

$curr_settab = '';
$curr_settab_pixel = '';
if(!empty($set_tab[$printer_pin])){
	$curr_settab = $set_tab[$printer_pin];
	$curr_settab_pixel = $set_tab_pxl[$printer_pin];
}else{
	$curr_settab = $set_tab[42];
	$curr_settab_pixel = $set_tab_pxl[42];
}
		
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'apps.min/helper/reports/report.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'apps.min/helper/reports/report.css'; ?>" media="print"/>	
	</head>
<body>
<div class="report_area" style="font-family: UbuntuMono; padding:0px; font-size:12px; margin:0px auto; text-align:left; border:0px solid #ccc; width:<?php echo $set_width[$printer_pin].'px'; ?>;">
	<?php
	if($data_printer['print_logo'] == 1){
		$print_logo_image = $objCI->session->userdata('client_logo');
		if(empty($print_logo_image)){
			$print_logo_image = 'logo-default.png';
		}
		?>
		<center>
			<img height="100" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $print_logo_image; ?>">
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
				$dt_exp = trim($dt_exp);
				
				//--FONT STYLE---------------
				//align
				if(in_array($dt_exp, $align_text)){
					if(empty($font_start_html)){
						$font_start_html .= '<div style="padding:0px 0px 2px 0px;';
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
						$font_start_html .= '<div style="padding:0px 0px 2px 0px;';
					}
					
					if($dt_exp == '[size=0'){
						$font_start_html .= 'font-size:12px !important;';
					}
					if($dt_exp == '[size=1'){
						$font_start_html .= 'font-size:13px !important;';
					}
					if($dt_exp == '[size=2'){
						$font_start_html .= 'font-size:16px !important;';
					}
					if($dt_exp == '[size=3'){
						$font_start_html .= 'font-size:18px !important;';
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
					if($dt_exp == '[list_order_tipe1'){
						$curr_settab_text = 6;
					}
					if($dt_exp == '[list_order_tipe2'){
						$curr_settab_text = 7;
					}
					if($dt_exp == '[clear_set_tab'){
						$curr_settab_text = 8;
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
							$separator_start_html .= '<div style="padding:0px 0px 2px 0px; clear:both;">'.str_repeat("-", $jml_dt_exp).'</div>';
							//$separator_start_html .= '<div style="border-top:1px dashed #444; margin: 5px 0px; clear:both;"></div>';
						}
						
						$dt_exp = '';
						$xplode_perline[$key] = '';
					}
					
					if(str_repeat("_", $jml_dt_exp) == $dt_exp){
						
						if(empty($separator_start_html)){
							$separator_start_html .= '<div style="padding:0px 0px 2px 0px; clear:both;">'.str_repeat("_", $jml_dt_exp).'</div>';
							//$separator_start_html .= '<div style="border-bottom:1px dashed #444; margin: 5px 0px; clear:both;"></div>';
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
								
								$dt_tab = str_replace("  ","&nbsp;&nbsp;", $dt_tab);
								
								//persentase
								//$persentase_width = ceil(($total_text/$printer_pin)*$set_width);
								$exp_dt_tab[$key_tab] = '<div curr_tab="'.$curr_tab.'" style="padding:0px 0px 2px 0px;width:'.$width_tab.'px; float:left; '.$font_align_style.'">'.$dt_tab.'</div>';
								
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
?>