<?php

if( ! function_exists('priceFormatAcc')){
	function priceFormatAcc($nominal = 0, $decimal = 2, $decimal_point = ',', $thousand_separator = '.', $show_00 = false){
		
		$is_minus = false;
		if($nominal < 0){
			$nominal = $nominal*-1;
			$is_minus = true;
		}
		
		$priceFormatAcc = number_format($nominal, $decimal, $decimal_point, $thousand_separator);
		
		if($show_00 == false){
			$priceFormatAcc = str_replace($decimal_point."00", "", $priceFormatAcc);
		}
		
		if($is_minus){
			$priceFormatAcc = "(".$priceFormatAcc.")";
		}
		
		return $priceFormatAcc;
	}
	
}

if( ! function_exists('priceFormat')){
	function priceFormat($nominal = 0, $decimal = 2, $decimal_point = ',', $thousand_separator = '.', $show_00 = false){
		
		$priceFormat = number_format($nominal, $decimal, $decimal_point, $thousand_separator);
		
		if($show_00 == false){
			$priceFormat = str_replace($decimal_point."00", "", $priceFormat);
		}
		
		return $priceFormat;
	}
	
}

if( ! function_exists('numberFormat')){
	function numberFormat($nominal = 0, $thousands_sep = ".", $dec_separator = ","){
					
		$nominal = str_replace($thousands_sep,"", $nominal);
		$nominal = str_replace($dec_separator,".", $nominal);
		$nominal = str_replace(".00","", $nominal);
		
		return $nominal;
	}
	
}