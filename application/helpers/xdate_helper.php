<?php

if( ! function_exists('get_month')){
	function get_month($month){
		$m = array(
			'01'	=>	'Januari',
			'02'	=>	'Februari',
			'03'	=>	'Maret',
			'04'	=>	'April',
			'05'	=>	'Mei',
			'06'	=>	'Juni',
			'07'	=>	'Juli',
			'08'	=>	'Agustus',
			'09'	=>	'September',
			'10'	=>	'Oktober',
			'11'	=>	'November',
			'12'	=>	'Desember'
		);
		
		if(strlen($month) == 1){
			$month = '0'.$month;
		}
		
		return $m[$month];
	}
	
}

if( ! function_exists('getMktime')){
	//Y-m-d H:i:s
	function getMktime($dateVal = ''){
		
		if(empty($dateVal)){
			return now();
		}
		
		$dateExp = explode(" ", $dateVal);
		
		$dateExp_date = array(date('m'),date('d'),date('Y'));
		if($dateExp[0]){
			$dateExp_date = explode("-",$dateExp[0]);
			//smart detection
			if(strlen($dateExp_date[2]) == 4 AND strlen($dateExp_date[0]) == 2){
				$switch_Y = $dateExp_date[0];
				$dateExp_date[0] = $dateExp_date[2];
				$dateExp_date[2] = $switch_Y;
			}
		}
		
		$dateExp_hour = array(0,0,0);
		if(!empty($dateExp[1])){
			$dateExp_hour = explode(":",$dateExp[1]);
		}
		
		$returnVal = mktime($dateExp_hour[0],$dateExp_hour[1],$dateExp_hour[2],$dateExp_date[1],$dateExp_date[2],$dateExp_date[0]);
		
		return $returnVal;
	}
	
}

if( ! function_exists('get_mktime')){
	//Y-m-d H:i:s
	function get_mktime($dateVal = ''){
				
		return getMktime($dateVal);
		
	}
	
}

if( ! function_exists('unix2human')){
	//Y-m-d H:i:s
	function unix2human($dateVal = '', $format = 'd-m-Y H:i:s'){
		
		$returnVal = '';
		if(!empty($dateVal)){		
			$returnVal = date($format, $dateVal);
		}
		
		return $returnVal;
		
	}
	
}

if( ! function_exists('get_periode_tanggal')){
	//Y-m-d H:i:s
	function get_periode_tanggal($dateVal = '', $retType = ''){
		
		$returnVal = '';
		if(!empty($dateVal)){		
			$getDay = date('j', $dateVal);
			$getMonth = date('m', $dateVal);
			$getYear = date('Y', $dateVal);
			
			$periode = 1;
			if($getDay > 20 ){
				$periode = 3;
			}else
			if($getDay > 10 AND $getDay <= 20){
				$periode = 2;
			}else{
				$periode = 1;
			}
			
			if($retType == 'periode'){
				$returnVal = $periode;
			}else{
				$returnVal = get_month($getMonth).' '.$getYear.' - Periode '.$periode;
			}
		}
		
		return $returnVal;
		
	}
	
}

if( ! function_exists('get_diff_date')){
	//Y-m-d H:i:s
	function get_diff_date($start = '', $end = '', $out_in_array = true){
	
		if(empty($start)){ $start = date("Y-m-d H:i:s"); }
		if(empty($end)){ $end = date("Y-m-d H:i:s"); }
		
		$intervalo = date_diff(date_create($start), date_create($end));
		
		$out = $intervalo->format("Y:%Y,m:%M,d:%d,H:%H,i:%i,s:%s");
		
		if(!$out_in_array){
			return $out;
		}else{
		
			$a_out = array();
			
			$a_out_exp = explode(',',$out);			
			
			foreach($a_out_exp as $dt_out){
				$dt_out_exp = explode(':',$dt_out);
				$a_out[$dt_out_exp[0]] = $dt_out_exp[1];
			}
			
			return $a_out;
		}
	}
	
}