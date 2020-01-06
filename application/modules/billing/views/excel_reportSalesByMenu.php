<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.$date_from.' to '.$date_till).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 1290;
$total_cols = 13;

//update-0120.001
if(!empty($filter_column)){
	extract($filter_column);
}

$payment_data_content = '';
if($show_payment == true){
	if(!empty($payment_data)){
		foreach($payment_data as $key_id => $dtPay){
			$payment_data_content .= '<td class="tbl_head_td_xcenter" width="100">'.$dtPay.'</td>';
			$set_width += 100;
			$total_cols++;
		}
	}
}
		
if(count($display_discount_type) > 1){
	$set_width += 200;
	$total_cols += 2;
}
		
if($show_tax == false){
	$set_width -= 90;
	$total_cols -= 1;
}
if($show_service == false){
	$set_width -= 90;
	$total_cols -= 1;
}
if($show_compliment == false){
	$set_width -= 110;
	$total_cols -= 1;
}
?>
<html>
<body>
<style>
	<?php include ASSETS_PATH."desktop/css/report.css.php"; ?>
</style>
<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
	<br/>
	<table width="<?php echo $set_width; ?>">
		<!-- HEADER -->
		<thead>
			<tr>
				<td colspan="<?php echo $total_cols ?>">
					<div>
					
						<div class="title_report"><?php echo $report_name;?></div>		
						<?php
						if($date_from == $date_till){
							?>
							<div class="subtitle_report"><?php echo 'Tanggal : '.$date_from; ?></div>		
							<?php
						}else{
							?>
							<div class="subtitle_report"><?php echo 'Tanggal : '.$date_from.' s/d '.$date_till; ?></div>		
							<?php
						}
						
						if(!empty($user_shift)){ 
							?>
							<div class="subtitle_report"><?php echo 'Shift: '.$user_shift; ?></div>		
							<?php 				
						}else{
							?>
							<div class="subtitle_report"><?php echo 'Shift: All Shift'; ?></div>		
							<?php 
							//$total_cols++;
						}
						if(!empty($user_kasir)){ 
							?>
							<div class="subtitle_report"><?php echo 'Kasir: '.$user_kasir; ?></div>		
							<?php 				
						}
						?>	
						
					</div>
				</td>
			</tr>
			<tr>
				<td class="tbl_head_td_first_xcenter" width="40" rowspan="2">NO</td>
				<td class="tbl_head_td_xcenter" width="110" rowspan="2">CODE</td>
				<td class="tbl_head_td_xcenter" width="260" rowspan="2">PRODUCT / ITEM</td>
				<td class="tbl_head_td_xcenter" width="60" rowspan="2">TOTAL QTY</td>
				<td class="tbl_head_td_xcenter" width="110" rowspan="2">TOTAL BILLING</td>
				<?php
				if($diskon_sebelum_pajak_service == 1){
					?>
					<td class="tbl_head_td_xcenter" width="110" colspan="2">DISCOUNT</td>
					<?php
				}
				
				if($show_tax == true){
				?>
				<td class="tbl_head_td_xcenter" width="90" rowspan="2">TAX</td>
				<?php
				}
				if($show_service == true){
				?>
				<td class="tbl_head_td_xcenter" width="90" rowspan="2">SERVICE</td>
				<?php
				}
				?>
				<td class="tbl_head_td_xcenter" width="100" rowspan="2">SUB TOTAL</td>
				<?php
				if($diskon_sebelum_pajak_service == 0){
					?>
					<td class="tbl_head_td_xcenter" width="110" colspan="2">DISCOUNT</td>
					<?php
				}
				?>
				<td class="tbl_head_td_xcenter" width="100" rowspan="2">PEMBULATAN<br/>AVERAGE</td>
				<td class="tbl_head_td_xcenter" width="110" rowspan="2">GRAND TOTAL</td>
				<?php
				if($show_payment == true){
				?>
				<td class="tbl_head_td_xcenter" width="100" colspan="<?php echo count($payment_data); ?>">PAYMENT</td>	
				<?php	
				}
				if($show_compliment == true){
				?>
				<td class="tbl_head_td_xcenter" width="110" rowspan="2">COMPLIMENT</td>
				<?php
				}
				?>
			</tr>
			<tr>
				<?php
				if($diskon_sebelum_pajak_service == 1){
					?>
					<td class="tbl_head_td_xcenter" width="110">ITEM</td>
					<td class="tbl_head_td_xcenter" width="110">BILLING</td>
					<?php
				}
				
				if($diskon_sebelum_pajak_service == 0){
					?>
					<td class="tbl_head_td_xcenter" width="110">ITEM</td>
					<td class="tbl_head_td_xcenter" width="110">BILLING</td>
					<?php
				}
				
				if($show_payment == true){
					echo $payment_data_content;
				}
				
				?>
				
			</tr>
		</thead>
		<tbody>
		<?php
		if(!empty($report_data)){
		
			$no = 1;
			$total_qty = 0;
			$total_billing = 0;
			$total_sub_total = 0;
			$total_tax = 0;
			$total_service = 0;
			$total_pembulatan = 0;
			$grand_total = 0;
			$grand_total_payment = array();
			$discount_total = 0;
			$discount_billing_total = 0;
			$grand_discount_total = 0;
			$grand_discount_billing_total = 0;
			$compliment_total = 0;
			
			$grand_discount_total_before = 0;
			$grand_discount_billing_total_before = 0;
			$grand_discount_total_after = 0;
			$grand_discount_billing_total_after = 0;
			
			foreach($report_data as $det){
				
				if(empty($det['product_name'])){
					$det['product_name'] = '#'.$det['product_id'].' deleted';
				}
						
				if(empty($det['product_code'])){
					$det['product_code'] = 'N/A';
				}
				
				?>
				<tr>
					<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
					<td class="tbl_data_td"><?php echo $det['product_code']; ?></td>
					<td class="tbl_data_td"><?php echo $det['product_name']; ?></td>
					<td class="tbl_data_td_xcenter"><?php echo $det['total_qty']; ?></td>
					<?php
					if($format_nominal == true){
						$det['total_billing_show'] = 'Rp. '.$det['total_billing_show'];
						$det['tax_total_show'] = 'Rp. '.$det['tax_total_show'];
						$det['service_total_show'] = 'Rp. '.$det['service_total_show'];
						$det['sub_total_show'] = 'Rp. '.$det['sub_total_show'];
						$det['total_pembulatan_show'] = 'Rp. '.$det['total_pembulatan_show'];
						$det['grand_total_show'] = 'Rp. '.$det['grand_total_show'];
						$det['total_compliment_show'] = 'Rp. '.$det['total_compliment_show'];
					}else{
						$det['total_billing_show'] = str_replace(".","",$det['total_billing_show']);
						$det['total_billing_show'] = str_replace(",",".",$det['total_billing_show']);
						$det['tax_total_show'] = str_replace(".","",$det['tax_total_show']);
						$det['tax_total_show'] = str_replace(",",".",$det['tax_total_show']);
						$det['service_total_show'] = str_replace(".","",$det['service_total_show']);
						$det['service_total_show'] = str_replace(",",".",$det['service_total_show']);
						$det['sub_total_show'] = str_replace(".","",$det['sub_total_show']);
						$det['sub_total_show'] = str_replace(",",".",$det['sub_total_show']);
						$det['total_pembulatan_show'] = str_replace(".","",$det['total_pembulatan_show']);
						$det['total_pembulatan_show'] = str_replace(",",".",$det['total_pembulatan_show']);
						$det['grand_total_show'] = str_replace(".","",$det['grand_total_show']);
						$det['grand_total_show'] = str_replace(",",".",$det['grand_total_show']);
						$det['total_compliment_show'] = str_replace(".","",$det['total_compliment_show']);
						$det['total_compliment_show'] = str_replace(",",".",$det['total_compliment_show']);
					}
					?>
					<td class="tbl_data_td_xright"><?php echo $det['total_billing_show']; ?></td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						if($format_nominal == true){
							$det['discount_total_show'] = 'Rp. '.$det['discount_total_show'];
							$det['discount_billing_total_show'] = 'Rp. '.$det['discount_billing_total_show'];
						}else{
							$det['discount_total_show'] = str_replace(".","",$det['discount_total_show']);
							$det['discount_total_show'] = str_replace(",",".",$det['discount_total_show']);
							$det['discount_billing_total_show'] = str_replace(".","",$det['discount_billing_total_show']);
							$det['discount_billing_total_show'] = str_replace(",",".",$det['discount_billing_total_show']);
						}
						?>
						<td class="tbl_data_td_xright"><?php echo $det['discount_total_show']; ?></td>
						<td class="tbl_data_td_xright"><?php echo $det['discount_billing_total_show']; ?></td>
						<?php
					}
					
					if($show_tax == true){
					?>
					<td class="tbl_data_td_xright"><?php echo $det['tax_total_show']; ?></td>
					<?php
					}
					if($show_service == true){
					?>
					<td class="tbl_data_td_xright"><?php echo $det['service_total_show']; ?></td>
					<?php
					}
					?>
					<td class="tbl_data_td_xright"><?php echo $det['sub_total_show']; ?></td>
					<?php
					if($diskon_sebelum_pajak_service == 0){
						if($format_nominal == true){
							$det['discount_total_show'] = 'Rp. '.$det['discount_total_show'];
							$det['discount_billing_total_show'] = 'Rp. '.$det['discount_billing_total_show'];
						}else{
							$det['discount_total_show'] = str_replace(".","",$det['discount_total_show']);
							$det['discount_total_show'] = str_replace(",",".",$det['discount_total_show']);
							$det['discount_billing_total_show'] = str_replace(".","",$det['discount_billing_total_show']);
							$det['discount_billing_total_show'] = str_replace(",",".",$det['discount_billing_total_show']);
						}
						?>
						<td class="tbl_data_td_xright"><?php echo $det['discount_total_show']; ?></td>
						<td class="tbl_data_td_xright"><?php echo $det['discount_billing_total_show']; ?></td>
						<?php
					}
					?>
					<td class="tbl_data_td_xright"><?php echo $det['total_pembulatan_show']; ?></td>
					<td class="tbl_data_td_xright"><?php echo $det['grand_total_show']; ?></td>
					<?php
					if($show_payment == true){
						if(!empty($payment_data)){
							foreach($payment_data as $key_id => $dtPay){
								
								$total_payment = 0;
								if(!empty($det['payment_'.$key_id])){
									$total_payment = $det['payment_'.$key_id];
								}
								
								if(empty($grand_total_payment[$key_id])){
									$grand_total_payment[$key_id] = 0;
								}
								
								if(empty($cat_grand_total_payment[$key_id])){
									$cat_grand_total_payment[$key_id] = 0;
								}
								
								$cat_grand_total_payment[$key_id] += $total_payment;
								$grand_total_payment[$key_id] += $total_payment;
								
								$total_payment_show = $total_payment;
								if($format_nominal == true){
									$total_payment_show = 'Rp. '.priceFormat($total_payment);
								}
								
								?>
								<td class="tbl_data_td_xright" width="100"><?php echo $total_payment_show; ?></td>
								<?php
																
							}
						}
					}
					
					if($show_compliment == true){
					?>
					<td class="tbl_data_td_xright"><?php echo $det['total_compliment_show']; ?></td>
					<?php
					}
					?>
				</tr>
				<?php	
				
				$total_qty +=  $det['total_qty'];
				$total_billing +=  $det['total_billing'];
				$total_sub_total +=  $det['sub_total'];
				$total_tax +=  $det['tax_total'];
				$total_service +=  $det['service_total'];
				$total_pembulatan +=  $det['total_pembulatan'];
				$grand_total +=  $det['grand_total'];
				$discount_total +=  $det['discount_total'];
				$discount_billing_total +=  $det['discount_billing_total'];
				$compliment_total +=  $det['compliment_total'];
				$no++;
			}
			
			
			if($format_nominal == true){ 
				$total_qty = priceFormat($total_qty);
				$total_billing = 'Rp. '.priceFormat($total_billing);
				$total_tax = 'Rp. '.priceFormat($total_tax);
				$total_service = 'Rp. '.priceFormat($total_service);
				$total_sub_total = 'Rp. '.priceFormat($total_sub_total);
				$discount_total = 'Rp. '.priceFormat($discount_total);
				$discount_billing_total = 'Rp. '.priceFormat($discount_billing_total);
				$total_pembulatan = 'Rp. '.priceFormat($total_pembulatan);
				$grand_total = 'Rp. '.priceFormat($grand_total);
				$compliment_total = 'Rp. '.priceFormat($compliment_total);
			}
			?>
			<tr>
				<td class="tbl_summary_td_first_xright" colspan="<?php echo 3; ?>">TOTAL</td>
				<td class="tbl_summary_td_xcenter"><?php echo $total_qty; ?></td>
				<td class="tbl_summary_td_xright"><?php echo $total_billing; ?></td>
				<?php
				if($diskon_sebelum_pajak_service == 1){
					?>
					<td class="tbl_summary_td_xright"><?php echo $discount_total; ?></td>
					<td class="tbl_summary_td_xright"><?php echo $discount_billing_total; ?></td>
					<?php
				}
				
				if($show_tax == true){
				?>
				<td class="tbl_summary_td_xright"><?php echo $total_tax; ?></td>
				<?php
				}
				
				if($show_service == true){
				?>
				<td class="tbl_summary_td_xright"><?php $total_service; ?></td>
				<?php
				}
				?>
				<td class="tbl_summary_td_xright"><?php echo $total_sub_total; ?></td>
				<?php
				if($diskon_sebelum_pajak_service == 0){
					?>
					<td class="tbl_summary_td_xright"><?php echo $discount_total; ?></td>
					<td class="tbl_summary_td_xright"><?php echo $discount_billing_total; ?></td>
					<?php
				}
				?>
				<td class="tbl_summary_td_xright"><?php echo $total_pembulatan; ?></td>
				<td class="tbl_summary_td_xright"><?php echo $grand_total; ?></td>
				<?php
				if($show_payment == true){
					if(!empty($payment_data)){
						foreach($payment_data as $key_id => $dtPay){
							
							$total = 0;
							if(!empty($grand_total_payment[$key_id])){
								
								if($format_nominal == true){
									$total = 'Rp. '.priceFormat($grand_total_payment[$key_id]);
								}else{
									$total = $grand_total_payment[$key_id];
								}
								
							}							
							?>
							<td class="tbl_summary_td_xright"><?php echo $total; ?></td>
							<?php
						}
					}
				}
				
				if($show_compliment == true){
				?>
				<td class="tbl_summary_td_xright"><?php echo $compliment_total; ?></td>
				<?php
				}
				?>
			</tr>
			<?php
		}else{
		?>
			<tr>
				<td colspan="<?php echo $total_cols; ?>" class="tbl_data_td_first_xcenter">Data Not Found</td>
			</tr>
		<?php
		}
		?>
		
		<tr>
			<td colspan="<?php echo $total_cols; ?>">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3">Printed: <?php echo date("d-m-Y H:i:s");?></td>
			<td colspan="<?php echo $total_cols-7; ?>" class="xcenter">&nbsp;</td>
			<td colspan="2" class="xcenter">
					Prepared by:<br/><br/><br/><br/>
					----------------------------
			</td>
			<td colspan="2" class="xcenter">
				
					Approved by:<br/><br/><br/><br/>
					----------------------------
			</td>
		</tr>
		</tbody>
	</table>
</div>
</body>
</html>