<?php
$date_title = $date_from;
if($date_from != $date_till){
	$date_title = $date_from.' to '.$date_till;
}

header("Content-Type:   application/excel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.$discount_type.' '.$tipe_sales.' '.$date_title).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 2090;
$total_cols = 17;

//update-0120.001
if(!empty($filter_column)){
	extract($filter_column);
}

if(count($display_discount_type) > 1){
	$set_width += 200;
	$total_cols += 2;
}

if($show_tax == false){
	$set_width -= 100;
	$total_cols -= 1;
}
if($show_service == false){
	$set_width -= 100;
	$total_cols -= 1;
}
if($show_compliment == false){
	$set_width -= 100;
	$total_cols -= 1;
}
if($show_pembulatan == false){
	$set_width -= 100;
	$total_cols -= 1;
}
if($show_note == false){
	$set_width -= 300;
	$total_cols -= 1;
}
if($show_shift_kasir == false){
	$set_width -= 150;
	$total_cols -= 1;
}
?>
<html>
<body>
<style>
	<?php include ASSETS_PATH."desktop/css/report.css.php"; ?>
</style>
<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		
	<table width="<?php echo $set_width; ?>">
		<!-- HEADER -->
		<thead>
			<tr>
				<td colspan="<?php echo $total_cols ?>">
					<div>
					
						<div class="title_report"><?php echo $report_name;?></div>		
						<div class="subtitle_report" style="margin-bottom:5px;">
						<?php
						if($date_from == $date_till){
							echo 'Tanggal : '.$date_from;
						}else{
							echo 'Tanggal : '.$date_from.' s/d '.$date_till;
						}
						if(!empty($user_shift)){ 
							echo ' &nbsp; | &nbsp; Shift: '.$user_shift;
						}else{
							echo ' &nbsp; | &nbsp; Shift: Semua Shift';
						}
						if(!empty($user_kasir)){ 
							echo ' &nbsp; | &nbsp; Kasir: '.$user_kasir;
						}else{
							echo ' &nbsp; | &nbsp; Kasir: Semua Kasir';
						}
						if(!empty($tipe_sales)){ 
							echo ' &nbsp; | &nbsp; Tipe Sales: '.$tipe_sales;
						}
							
						if(!empty($discount_type)){ 
							if($discount_type == 'no_promo'){
								echo ' &nbsp; | &nbsp; Tipe Diskon: Tanpa Promo/Diskon'; 
							}else
							if($discount_type == 'buyget'){
								echo ' &nbsp; | &nbsp; Tipe Diskon: Buy &amp; Get Promo'; 
							}else
							{
								echo ' &nbsp; | &nbsp; Tipe Diskon: Diskon Per-'.ucwords($discount_type); 
							}
							
						}else{
							echo ' &nbsp; | &nbsp; Tipe Diskon: Semua'; 
						}
						?>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td class="tbl_head_td_first_xcenter" width="50" rowspan="2">NO</td>
				<td class="tbl_head_td_xcenter" width="130" rowspan="2">PAYMENT DATE</td>
				<td class="tbl_head_td_xcenter" width="80" rowspan="2">BILLING NO.</td>
				<td class="tbl_head_td_xcenter" width="130" rowspan="2">DISCOUNT NAME</td>
				<td class="tbl_head_td_xcenter" width="100" rowspan="2">DISCOUNT TYPE</td>
				<td class="tbl_head_td_xcenter" width="100" rowspan="2">QTY MENU</td>
				<td class="tbl_head_td_xcenter" width="120" rowspan="2">TOTAL BILLING</td>
				<?php
				if($diskon_sebelum_pajak_service == 1 OR count($display_discount_type) > 1){
					if(count($display_discount_type) > 1){
						?>
						<td class="tbl_head_td_xcenter" width="220" colspan="2">DISCOUNT BEFORE TAX-SERVICE</td>	
						<?php
					}else{
						?>
						<td class="tbl_head_td_xcenter" width="220" colspan="2">DISCOUNT</td>	
						<?php
					}
				}
				
				if($diskon_sebelum_pajak_service == 0 OR count($display_discount_type) > 1){
					if(count($display_discount_type) > 1){
						?>
						<td class="tbl_head_td_xcenter" width="220" colspan="2">DISCOUNT AFTER TAX-SERVICE</td>	
						<?php
					}else{
						?>
						<td class="tbl_head_td_xcenter" width="220" colspan="2">DISCOUNT AFTER TAX-SERVICE</td>
						<?php
					}
					
				}
					
				//update-2001.002
				if($show_compliment == true){
					?>
					<td class="tbl_head_td_xcenter" width="100" rowspan="2">COMPLIMENT</td>
					<?php
				}
				?>
				<td class="tbl_head_td_xcenter" width="100" rowspan="2">NET SALES</td>
				<?php
				
				
				if($show_tax == true){
				?>
				<td class="tbl_head_td_xcenter" width="100" rowspan="2">TAX</td>
				<?php
				}
				if($show_service == true){
				?>
				<td class="tbl_head_td_xcenter" width="100" rowspan="2">SERVICE</td>
				<?php
				}
				
				if($show_pembulatan == true){
					?>
					<td class="tbl_head_td_xcenter" width="100" rowspan="2">PEMBULATAN</td>
					<?php
				}
				?>
				<td class="tbl_head_td_xcenter" width="120" rowspan="2">GRAND TOTAL</td>
				<?php
				if($show_note == true){
				?>
				<td class="tbl_head_td_xcenter" width="300" rowspan="2">NOTE</td>
				<?php
				}
				
				if($show_shift_kasir == true){
				?>
				<td class="tbl_head_td_xcenter" width="150" rowspan="2">SHIFT/KASIR</td>
				<?php
				}
				?>
			</tr>
			<tr>
				
				<?php
				if($diskon_sebelum_pajak_service == 1 OR count($display_discount_type) > 1){
					?>
					<td class="tbl_head_td_xcenter" width="110">ITEM</td>
					<td class="tbl_head_td_xcenter" width="110">BILLING</td>
					<?php
				}
				
				if($diskon_sebelum_pajak_service == 0 OR count($display_discount_type) > 1){
					?>
					<td class="tbl_head_td_xcenter" width="110">ITEM</td>
					<td class="tbl_head_td_xcenter" width="110">BILLING</td>
					<?php
				}
				?>
				
			</tr>
		</thead>
		<tbody>
		<?php
		
			if(!empty($report_data) AND $discount_type != 'buyget'){
			
				$no = 1;
				$total_billing = 0;
				$total_qty = 0;
				$total_tax = 0;
				$total_service = 0;
				$grand_total = 0;
				$grand_total_dp = 0;
				$grand_sub_total = 0;
				$grand_net_sales_total = 0;
				$grand_total_pembulatan = 0;
				$grand_discount_total = 0;
				$grand_discount_billing_total = 0;
				$grand_total_compliment = 0;
				$grand_total_payment = array();
			
				$grand_discount_total_before = 0;
				$grand_discount_billing_total_before = 0;
				$grand_discount_total_after = 0;
				$grand_discount_billing_total_after = 0;
					
				foreach($report_data as $det){
					
					
					$discount_name = $det['discount_id'];
					if(!empty($discount_data[$det['discount_id']])){
						$discount_name = $discount_data[$det['discount_id']];
					}
					
					if(!empty($det['total_qty'])){
						?>
						<tr>
							<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
							<td class="tbl_data_td_xcenter">&nbsp;<?php echo date("Y-m-d", strtotime($det['payment_date'])); ?></td>
							<td class="tbl_data_td_xcenter"><?php echo $det['billing_no']; ?></td>
							<td class="tbl_data_td"><?php echo $discount_name; ?></td>
							<td class="tbl_data_td_xcenter"><?php echo $det['discount_type']; ?></td>
							<?php
							if($format_nominal == true){
								$det['total_qty'] = priceFormat($det['total_qty']);
								$det['total_billing_show'] = 'Rp. '.$det['total_billing_show'];
								$det['tax_total_show'] = 'Rp. '.$det['tax_total_show'];
								$det['service_total_show'] = 'Rp. '.$det['service_total_show'];
								$det['sub_total_show'] = 'Rp. '.$det['sub_total_show'];
								$det['net_sales_total_show'] = 'Rp. '.$det['net_sales_total_show'];
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
								$det['sub_total_show'] = str_replace(",","",$det['sub_total_show']);
								$det['net_sales_total_show'] = str_replace(".","",$det['net_sales_total_show']);
								$det['net_sales_total_show'] = str_replace(",",".",$det['net_sales_total_show']);
								$det['total_pembulatan_show'] = str_replace(".","",$det['total_pembulatan_show']);
								$det['total_pembulatan_show'] = str_replace(",",".",$det['total_pembulatan_show']);
								$det['grand_total_show'] = str_replace(".","",$det['grand_total_show']);
								$det['grand_total_show'] = str_replace(",",".",$det['grand_total_show']);
								$det['total_compliment_show'] = str_replace(".","",$det['total_compliment_show']);
								$det['total_compliment_show'] = str_replace(",",".",$det['total_compliment_show']);
							}
							?>
							<td class="tbl_data_td_xcenter"><?php echo $det['total_qty']; ?></td>
							<td class="tbl_data_td_xright"><?php echo $det['total_billing_show']; ?></td>
							<?php
							if($diskon_sebelum_pajak_service == 1 OR count($display_discount_type) > 1){
								if($format_nominal == true){
									$det['discount_total_show'] = 'Rp. '.$det['discount_total_show'];
									$det['discount_billing_total_show'] = 'Rp. '.$det['discount_billing_total_show'];
								}else{
									$det['discount_total_show'] = str_replace(".","",$det['discount_total_show']);
									$det['discount_total_show'] = str_replace(",",".",$det['discount_total_show']);
									$det['discount_billing_total_show'] = str_replace(".","",$det['discount_billing_total_show']);
									$det['discount_billing_total_show'] = str_replace(",",".",$det['discount_billing_total_show']);
								}
								
								if(count($display_discount_type) > 1){
									if(count($display_discount_type) > 1 AND $det['diskon_sebelum_pajak_service'] == 1){
										?>
										<td class="tbl_data_td_xright"><?php echo $det['discount_total_show']; ?></td>
										<td class="tbl_data_td_xright"><?php echo $det['discount_billing_total_show']; ?></td>
										<?php
									}else{
										?>
										<td class="tbl_data_td_xright">0</td>
										<td class="tbl_data_td_xright">0</td>
										<?php
									}
								}else{
									?>
									<td class="tbl_data_td_xright"><?php echo $det['discount_total_show']; ?></td>
									<td class="tbl_data_td_xright"><?php echo $det['discount_billing_total_show']; ?></td>
									<?php
								}
								
							}
							
							if($diskon_sebelum_pajak_service == 0 OR count($display_discount_type) > 1){
								if($format_nominal == true){
									$det['discount_total_show'] = 'Rp. '.$det['discount_total_show'];
									$det['discount_billing_total_show'] = 'Rp. '.$det['discount_billing_total_show'];
								}else{
									$det['discount_total_show'] = str_replace(".","",$det['discount_total_show']);
									$det['discount_total_show'] = str_replace(",",".",$det['discount_total_show']);
									$det['discount_billing_total_show'] = str_replace(".","",$det['discount_billing_total_show']);
									$det['discount_billing_total_show'] = str_replace(",",".",$det['discount_billing_total_show']);
								}
								
								if(count($display_discount_type) > 1){
									if(count($display_discount_type) > 1 AND $det['diskon_sebelum_pajak_service'] == 0){
										?>
										<td class="tbl_data_td_xright"><?php echo $det['discount_total_show']; ?></td>
										<td class="tbl_data_td_xright"><?php echo $det['discount_billing_total_show']; ?></td>
										<?php
									}else{
										?>
										<td class="tbl_data_td_xright">0</td>
										<td class="tbl_data_td_xright">0</td>
										<?php
									}
								}else{
									?>
									<td class="tbl_data_td_xright"><?php echo $det['discount_total_show']; ?></td>
									<td class="tbl_data_td_xright"><?php echo $det['discount_billing_total_show']; ?></td>
									<?php
								}
								
							}
						
							if($show_compliment == true){
							?>
							<td class="tbl_data_td_xright"><?php echo $det['total_compliment_show']; ?></td>
							<?php
							}
							?>
							<td class="tbl_data_td_xright"><?php echo $det['net_sales_total_show']; ?></td>
							<?php
						
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
							
							if($show_pembulatan == true){
							?>
							<td class="tbl_data_td_xright"><?php echo $det['total_pembulatan_show']; ?></td>
							<?php
							}
							?>
							<td class="tbl_data_td_xright"><?php echo $det['grand_total_show']; ?></td>
							<?php
							if($show_note == true){
							?>
							<td class="tbl_data_td"><?php echo $det['payment_note']; ?></td>
							<?php
							}
							
							if($show_shift_kasir == true){
							?>
							<td class="tbl_data_td"><?php echo $det['nama_shift'].'/'.$det['nama_kasir']; ?></td>
							<?php
							}
							?>
						</tr>
						<?php	
					}

					$total_billing +=  $det['total_billing'];
					$total_qty +=  $det['total_qty'];
					$total_tax +=  $det['tax_total'];
					$total_service +=  $det['service_total'];
					$grand_total +=  $det['grand_total'];
					$grand_total_compliment += $det['total_compliment'];
					$grand_sub_total += $det['sub_total'];
					$grand_net_sales_total += $det['net_sales_total'];
					$grand_total_pembulatan += $det['total_pembulatan'];
					//$grand_discount_total += $det['discount_total'];
					//$grand_discount_billing_total += $det['discount_billing_total'];
					$grand_total_dp += $det['total_dp'];
					
					if($diskon_sebelum_pajak_service == 1 OR count($display_discount_type) > 1){
						if(count($display_discount_type) > 1 AND $det['diskon_sebelum_pajak_service'] == 1){
							$grand_discount_total_before += $det['discount_total'];
							$grand_discount_billing_total_before += $det['discount_billing_total'];
						}else{
							$grand_discount_total += $det['discount_total'];
							$grand_discount_billing_total += $det['discount_billing_total'];
						}
					}
				
					if($diskon_sebelum_pajak_service == 0 OR count($display_discount_type) > 1){
						if(count($display_discount_type) > 1 AND $det['diskon_sebelum_pajak_service'] == 0){
							$grand_discount_total_after += $det['discount_total'];
							$grand_discount_billing_total_after += $det['discount_billing_total'];
						}else{
							$grand_discount_total += $det['discount_total'];
							$grand_discount_billing_total += $det['discount_billing_total'];
						}
					}
				
					$no++;
				}
				
				
				if($format_nominal == true){ 
					$total_qty = priceFormat($total_qty);
					$total_billing = 'Rp. '.priceFormat($total_billing);
					$total_tax = 'Rp. '.priceFormat($total_tax);
					$total_service = 'Rp. '.priceFormat($total_service);
					$grand_total = 'Rp. '.priceFormat($grand_total);
					$grand_total_compliment = 'Rp. '.priceFormat($grand_total_compliment);
					$grand_sub_total = 'Rp. '.priceFormat($grand_sub_total);
					$grand_net_sales_total = 'Rp. '.priceFormat($grand_net_sales_total);
					$grand_total_pembulatan = 'Rp. '.priceFormat($grand_total_pembulatan);
					$grand_discount_total = 'Rp. '.priceFormat($grand_discount_total);
					$grand_discount_billing_total = 'Rp. '.priceFormat($grand_discount_billing_total);
					$grand_total_dp = 'Rp. '.priceFormat($grand_total_dp);
					
					$grand_discount_total_before = 'Rp. '.priceFormat($grand_discount_total_before);
					$grand_discount_billing_total_before = 'Rp. '.priceFormat($grand_discount_billing_total_before);
					$grand_discount_total_after = 'Rp. '.priceFormat($grand_discount_total_after);
					$grand_discount_billing_total_after = 'Rp. '.priceFormat($grand_discount_billing_total_after);
				}
				?>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="<?php echo 5; ?>">TOTAL </td>
					<td class="tbl_summary_td_first_xcenter"><?php echo $total_qty; ?></td>
					<td class="tbl_summary_td_xright"><?php echo $total_billing; ?></td>
					<?php
					if($diskon_sebelum_pajak_service == 1 OR count($display_discount_type) > 1){
						if(count($display_discount_type) > 1){
							?>
							<td class="tbl_summary_td_xright"><?php echo $grand_discount_total_before; ?></td>
							<td class="tbl_summary_td_xright"><?php echo $grand_discount_billing_total_before; ?></td>
							<?php
						}else{
							?>
							<td class="tbl_summary_td_xright"><?php echo $grand_discount_total; ?></td>
							<td class="tbl_summary_td_xright"><?php echo $grand_discount_billing_total; ?></td>
							<?php
						}
					}
					
					if($diskon_sebelum_pajak_service == 0 OR count($display_discount_type) > 1){
						if(count($display_discount_type) > 1){
							?>
							<td class="tbl_summary_td_xright"><?php echo $grand_discount_total_after; ?></td>
							<td class="tbl_summary_td_xright"><?php echo $grand_discount_billing_total_after; ?></td>
							<?php
						}else{
							?>
							<td class="tbl_summary_td_xright"><?php echo $grand_discount_total; ?></td>
							<td class="tbl_summary_td_xright"><?php echo $grand_discount_billing_total; ?></td>
							<?php
						}
					}
					
					if($show_compliment == true){
					?>
					<td class="tbl_summary_td_xright"><?php echo $grand_total_compliment; ?></td>
					<?php
					}
					
					?>
					<td class="tbl_summary_td_xright"><?php echo $grand_net_sales_total; ?></td>
					<?php
						
					if($show_tax == true){
					?>
					<td class="tbl_summary_td_xright"><?php echo $total_tax; ?></td>
					<?php
					}
					
					if($show_service == true){
					?>
					<td class="tbl_summary_td_xright"><?php echo $total_service; ?></td>
					<?php
					}
					
					if($show_pembulatan == true){
					?>
					<td class="tbl_summary_td_xright"><?php echo $grand_total_pembulatan; ?></td>
					<?php
					}
					?>
					<td class="tbl_summary_td_xright"><?php echo $grand_total; ?></td>
					<?php
					if($show_note == true){
					?>
					<td class="tbl_summary_td_xright">&nbsp;</td>
					<?php
					}
					
					if($show_shift_kasir == true){
					?>
					<td class="tbl_summary_td_xright">&nbsp;</td>
					<?php
					}
					?>
				</tr>
				<?php
			}else{
			?>
				<tr>
					<td class="tbl_data_td_first_xcenter" colspan="<?php echo $total_cols; ?>">Data Not Found</td>
				</tr>
			<?php
			}
			
		?>	
		
		<tr>
			<td colspan="<?php echo $total_cols; ?>">&nbsp;</td>
		</tr>
		<tr>
			<td class="tbl_head_td_first_xcenter" width="50" rowspan="2">NO</td>
			<td class="tbl_head_td_xcenter" width="130" rowspan="2">PAYMENT DATE</td>
			<td class="tbl_head_td_xcenter" width="80" rowspan="2">BILLING NO.</td>
			<td class="tbl_head_td_xcenter" width="130" rowspan="2" colspan="2">BUY &amp; GET/PROMO</td>
			<td class="tbl_head_td_xcenter" width="100" rowspan="2">QTY MENU</td>
			<td class="tbl_head_td_xcenter" width="120" rowspan="2">TOTAL BILLING</td>
			<?php
			if($diskon_sebelum_pajak_service == 1 OR count($display_discount_type) > 1){
				if(count($display_discount_type) > 1){
					?>
					<td class="tbl_head_td_xcenter" width="220" colspan="2">DISCOUNT BEFORE TAX-SERVICE</td>	
					<?php
				}else{
					?>
					<td class="tbl_head_td_xcenter" width="220" colspan="2">DISCOUNT</td>	
					<?php
				}
			}
			
			if($diskon_sebelum_pajak_service == 0 OR count($display_discount_type) > 1){
				if(count($display_discount_type) > 1){
					?>
					<td class="tbl_head_td_xcenter" width="220" colspan="2">DISCOUNT AFTER TAX-SERVICE</td>	
					<?php
				}else{
					?>
					<td class="tbl_head_td_xcenter" width="220" colspan="2">DISCOUNT AFTER TAX-SERVICE</td>
					<?php
				}
			}
				
			//update-2001.002
			if($show_compliment == true){
				?>
				<td class="tbl_head_td_xcenter" width="100" rowspan="2">COMPLIMENT</td>
				<?php
			}
			?>
			<td class="tbl_head_td_xcenter" width="100" rowspan="2">NET SALES</td>
			<?php
				
			if($show_tax == true){
			?>
			<td class="tbl_head_td_xcenter" width="100" rowspan="2">TAX</td>
			<?php
			}
			if($show_service == true){
			?>
			<td class="tbl_head_td_xcenter" width="100" rowspan="2">SERVICE</td>
			<?php
			}
			
			if($show_pembulatan == true){
				?>
				<td class="tbl_head_td_xcenter" width="100" rowspan="2">PEMBULATAN</td>
				<?php
			}
			?>
			<td class="tbl_head_td_xcenter" width="120" rowspan="2">GRAND TOTAL</td>
			<?php
			if($show_note == true){
			?>
			<td class="tbl_head_td_xcenter" width="200" rowspan="2">NOTE</td>
			<?php
			}
			
			if($show_shift_kasir == true){
			?>
			<td class="tbl_head_td_xcenter" width="150" rowspan="2">SHIFT/KASIR</td>
			<?php
			}
			?>
		</tr>
		<tr>
			
			<?php
			if($diskon_sebelum_pajak_service == 1 OR count($display_discount_type) > 1){
				?>
				<td class="tbl_head_td_xcenter" width="110">ITEM</td>
				<td class="tbl_head_td_xcenter" width="110">BILLING</td>
				<?php
			}
			
			if($diskon_sebelum_pajak_service == 0 OR count($display_discount_type) > 1){
				?>
				<td class="tbl_head_td_xcenter" width="110">ITEM</td>
				<td class="tbl_head_td_xcenter" width="110">BILLING</td>
				<?php
			}
			?>
			
		</tr>
		<?php
		
			if(!empty($buyget_data) AND ($discount_type == '' OR $discount_type == 'buyget')){
			
				$no = 1;
				$total_billing = 0;
				$total_qty = 0;
				$total_tax = 0;
				$total_service = 0;
				$grand_total = 0;
				$grand_total_dp = 0;
				$grand_sub_total = 0;
				$grand_net_sales_total = 0;
				$grand_total_pembulatan = 0;
				$grand_discount_total = 0;
				$grand_discount_billing_total = 0;
				$grand_total_compliment = 0;
				$grand_total_payment = array();
				
				$grand_discount_total_before = 0;
				$grand_discount_billing_total_before = 0;
				$grand_discount_total_after = 0;
				$grand_discount_billing_total_after = 0;
					
				foreach($buyget_data as $det){
					
					
					$discount_name = $det['discount_id'];
					if(!empty($discount_data[$det['discount_id']])){
						$discount_name = $discount_data[$det['discount_id']];
					}
					
					if(!empty($det['total_qty'])){
						?>
						<tr>
							<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
							<td class="tbl_data_td_xcenter">&nbsp;<?php echo date("Y-m-d", strtotime($det['payment_date'])); ?></td>
							<td class="tbl_data_td_xcenter"><?php echo $det['billing_no']; ?></td>
							<?php
							if($format_nominal == true){
								$det['total_qty'] = priceFormat($det['total_qty']);
								$det['total_billing_show'] = 'Rp. '.$det['total_billing_show'];
								$det['tax_total_show'] = 'Rp. '.$det['tax_total_show'];
								$det['service_total_show'] = 'Rp. '.$det['service_total_show'];
								$det['sub_total_show'] = 'Rp. '.$det['sub_total_show'];
								$det['net_sales_total_show'] = 'Rp. '.$det['net_sales_total_show'];
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
								$det['sub_total_show'] = str_replace(",","",$det['sub_total_show']);
								$det['net_sales_total_show'] = str_replace(".","",$det['net_sales_total_show']);
								$det['net_sales_total_show'] = str_replace(",",".",$det['net_sales_total_show']);
								$det['total_pembulatan_show'] = str_replace(".","",$det['total_pembulatan_show']);
								$det['total_pembulatan_show'] = str_replace(",",".",$det['total_pembulatan_show']);
								$det['grand_total_show'] = str_replace(".","",$det['grand_total_show']);
								$det['grand_total_show'] = str_replace(",",".",$det['grand_total_show']);
								$det['total_compliment_show'] = str_replace(".","",$det['total_compliment_show']);
								$det['total_compliment_show'] = str_replace(",",".",$det['total_compliment_show']);
							}
							?>
							<td class="tbl_data_td" colspan="2"><?php echo $discount_name; ?></td>
							<td class="tbl_data_td_xcenter"><?php echo $det['total_qty']; ?></td>
							<td class="tbl_data_td_xright"><?php echo $det['total_billing_show']; ?></td>
							<?php
							if($diskon_sebelum_pajak_service == 1 OR count($display_discount_type) > 1){
								if($format_nominal == true){
									$det['discount_total_show'] = 'Rp. '.$det['discount_total_show'];
									$det['discount_billing_total_show'] = 'Rp. '.$det['discount_billing_total_show'];
								}else{
									$det['discount_total_show'] = str_replace(".","",$det['discount_total_show']);
									$det['discount_total_show'] = str_replace(",",".",$det['discount_total_show']);
									$det['discount_billing_total_show'] = str_replace(".","",$det['discount_billing_total_show']);
									$det['discount_billing_total_show'] = str_replace(",",".",$det['discount_billing_total_show']);
								}
								
								if(count($display_discount_type) > 1){
									if(count($display_discount_type) > 1 AND $det['diskon_sebelum_pajak_service'] == 1){
										?>
										<td class="tbl_data_td_xright"><?php echo $det['discount_total_show']; ?></td>
										<td class="tbl_data_td_xright"><?php echo $det['discount_billing_total_show']; ?></td>
										<?php
									}else{
										?>
										<td class="tbl_data_td_xright">0</td>
										<td class="tbl_data_td_xright">0</td>
										<?php
									}
								}else{
									?>
									<td class="tbl_data_td_xright"><?php echo $det['discount_total_show']; ?></td>
									<td class="tbl_data_td_xright"><?php echo $det['discount_billing_total_show']; ?></td>
									<?php
								}
								
							}
							
							if($diskon_sebelum_pajak_service == 0 OR count($display_discount_type) > 1){
								if($format_nominal == true){
									$det['discount_total_show'] = 'Rp. '.$det['discount_total_show'];
									$det['discount_billing_total_show'] = 'Rp. '.$det['discount_billing_total_show'];
								}else{
									$det['discount_total_show'] = str_replace(".","",$det['discount_total_show']);
									$det['discount_total_show'] = str_replace(",",".",$det['discount_total_show']);
									$det['discount_billing_total_show'] = str_replace(".","",$det['discount_billing_total_show']);
									$det['discount_billing_total_show'] = str_replace(",",".",$det['discount_billing_total_show']);
								}
								
								if(count($display_discount_type) > 1){
									if(count($display_discount_type) > 1 AND $det['diskon_sebelum_pajak_service'] == 0){
										?>
										<td class="tbl_data_td_xright"><?php echo $det['discount_total_show']; ?></td>
										<td class="tbl_data_td_xright"><?php echo $det['discount_billing_total_show']; ?></td>
										<?php
									}else{
										?>
										<td class="tbl_data_td_xright">0</td>
										<td class="tbl_data_td_xright">0</td>
										<?php
									}
								}else{
									?>
									<td class="tbl_data_td_xright"><?php echo $det['discount_total_show']; ?></td>
									<td class="tbl_data_td_xright"><?php echo $det['discount_billing_total_show']; ?></td>
									<?php
								}
								
							}
								
							if($show_compliment == true){
							?>
							<td class="tbl_data_td_xright"><?php echo $det['total_compliment_show']; ?></td>
							<?php
							}
							?>
							<td class="tbl_data_td_xright"><?php echo $det['net_sales_total_show']; ?></td>
							<?php
						
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
							
							if($show_pembulatan == true){
							?>
							<td class="tbl_data_td_xright"><?php echo $det['total_pembulatan_show']; ?></td>
							<?php
							}
							?>
							<td class="tbl_data_td_xright"><?php echo $det['grand_total_show']; ?></td>
							<?php
							if($show_note == true){
							?>
							<td class="tbl_data_td"><?php echo $det['payment_note']; ?></td>
							<?php
							}
							
							if($show_shift_kasir == true){
							?>
							<td class="tbl_data_td"><?php echo $det['nama_shift'].'/'.$det['nama_kasir']; ?></td>
							<?php
							}
							?>
						</tr>
						<?php	
					}

					$total_billing +=  $det['total_billing'];
					$total_qty +=  $det['total_qty'];
					$total_tax +=  $det['tax_total'];
					$total_service +=  $det['service_total'];
					$grand_total +=  $det['grand_total'];
					$grand_total_compliment += $det['total_compliment'];
					$grand_sub_total += $det['sub_total'];
					$grand_net_sales_total += $det['net_sales_total'];
					$grand_total_pembulatan += $det['total_pembulatan'];
					//$grand_discount_total += $det['discount_total'];
					//$grand_discount_billing_total += $det['discount_billing_total'];
					$grand_total_dp += $det['total_dp'];
					
					if($diskon_sebelum_pajak_service == 1 OR count($display_discount_type) > 1){
						if(count($display_discount_type) > 1 AND $det['diskon_sebelum_pajak_service'] == 1){
							$grand_discount_total_before += $det['discount_total'];
							$grand_discount_billing_total_before += $det['discount_billing_total'];
						}else{
							$grand_discount_total += $det['discount_total'];
							$grand_discount_billing_total += $det['discount_billing_total'];
						}
					}
				
					if($diskon_sebelum_pajak_service == 0 OR count($display_discount_type) > 1){
						if(count($display_discount_type) > 1 AND $det['diskon_sebelum_pajak_service'] == 0){
							$grand_discount_total_after += $det['discount_total'];
							$grand_discount_billing_total_after += $det['discount_billing_total'];
						}else{
							$grand_discount_total += $det['discount_total'];
							$grand_discount_billing_total += $det['discount_billing_total'];
						}
					}
				
					$no++;
				}
				
				if($format_nominal == true){ 
					$total_billing = 'Rp. '.priceFormat($total_billing);
					$total_tax = 'Rp. '.priceFormat($total_tax);
					$total_service = 'Rp. '.priceFormat($total_service);
					$grand_total = 'Rp. '.priceFormat($grand_total);
					$grand_total_compliment = 'Rp. '.priceFormat($grand_total_compliment);
					$grand_sub_total = 'Rp. '.priceFormat($grand_sub_total);
					$grand_net_sales_total = 'Rp. '.priceFormat($grand_net_sales_total);
					$grand_total_pembulatan = 'Rp. '.priceFormat($grand_total_pembulatan);
					$grand_discount_total = 'Rp. '.priceFormat($grand_discount_total);
					$grand_discount_billing_total = 'Rp. '.priceFormat($grand_discount_billing_total);
					$grand_total_dp = 'Rp. '.priceFormat($grand_total_dp);
					
					$grand_discount_total_before = 'Rp. '.priceFormat($grand_discount_total_before);
					$grand_discount_billing_total_before = 'Rp. '.priceFormat($grand_discount_billing_total_before);
					$grand_discount_total_after = 'Rp. '.priceFormat($grand_discount_total_after);
					$grand_discount_billing_total_after = 'Rp. '.priceFormat($grand_discount_billing_total_after);
				}
				?>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="<?php echo 5; ?>">TOTAL </td>
					<td class="tbl_summary_td_first_xcenter"><?php echo $total_qty; ?></td>
					<td class="tbl_summary_td_xright"><?php echo $total_billing; ?></td>
					<?php
					if($diskon_sebelum_pajak_service == 1 OR count($display_discount_type) > 1){
						if(count($display_discount_type) > 1){
							?>
							<td class="tbl_summary_td_xright"><?php echo $grand_discount_total_before; ?></td>
							<td class="tbl_summary_td_xright"><?php echo $grand_discount_billing_total_before; ?></td>
							<?php
						}else{
							?>
							<td class="tbl_summary_td_xright"><?php echo $grand_discount_total; ?></td>
							<td class="tbl_summary_td_xright"><?php echo $grand_discount_billing_total; ?></td>
							<?php
						}
					}
					
					if($diskon_sebelum_pajak_service == 0 OR count($display_discount_type) > 1){
						if(count($display_discount_type) > 1){
							?>
							<td class="tbl_summary_td_xright"><?php echo $grand_discount_total_after; ?></td>
							<td class="tbl_summary_td_xright"><?php echo $grand_discount_billing_total_after; ?></td>
							<?php
						}else{
							?>
							<td class="tbl_summary_td_xright"><?php echo $grand_discount_total; ?></td>
							<td class="tbl_summary_td_xright"><?php echo $grand_discount_billing_total; ?></td>
							<?php
						}
					}
					
					if($show_compliment == true){
					?>
					<td class="tbl_summary_td_xright"><?php echo $grand_total_compliment; ?></td>
					<?php
					}
					
					?>
					<td class="tbl_summary_td_xright"><?php echo $grand_net_sales_total; ?></td>
					<?php
						
					if($show_tax == true){
					?>
					<td class="tbl_summary_td_xright"><?php echo $total_tax; ?></td>
					<?php
					}
					
					if($show_service == true){
					?>
					<td class="tbl_summary_td_xright"><?php echo $total_service; ?></td>
					<?php
					}
					
					if($show_pembulatan == true){
					?>
					<td class="tbl_summary_td_xright"><?php echo $grand_total_pembulatan; ?></td>
					<?php
					}
					?>
					<td class="tbl_summary_td_xright"><?php echo $grand_total; ?></td>
					<?php
					if($show_note == true){
					?>
					<td class="tbl_summary_td_xright">&nbsp;</td>
					<?php
					}
					
					if($show_shift_kasir == true){
					?>
					<td class="tbl_summary_td_xright">&nbsp;</td>
					<?php
					}
					?>
				</tr>
				<?php
			}else{
			?>
				<tr>
					<td class="tbl_data_td_first_xcenter" colspan="<?php echo $total_cols; ?>">Data Not Found</td>
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