<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/desktop/css/report.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/desktop/css/report.css'; ?>" media="print"/>	
	</head>
<body>
	<?php
		$set_width = 1290;
		$total_cols = 13;
		
		$payment_data_content = '';
		if(!empty($payment_data)){
			foreach($payment_data as $key_id => $dtPay){
				$payment_data_content .= '<td class="xcenter" width="100">'.$dtPay.'</td>';
				$set_width += 100;
				$total_cols++;
			}
		}
		
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<div>
			<div class="logo">
				
				<!-- <img height="80" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>"> -->
				
			</div>
						
			<div class="title_report xcenter"><?php echo $report_name;?></div>
			<div class="subtitle_report xcenter"><?php echo 'Period : '.$date_from.' TO '.$date_till;?><br/>
			</div>			
			
		</div>
		<br/>
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<tr class="tbl-header">
				<td class="first xcenter" width="40" rowspan="2">NO</td>
				<td class="xcenter" width="100" rowspan="2">ITEM SKU</td>
				<td class="xcenter" width="290" rowspan="2">PRODUCT / ITEM</td>
				<td class="xcenter" width="60" rowspan="2">TOTAL QTY</td>
				<td class="xcenter" width="110" rowspan="2">TOTAL BILLING</td>
				<?php
				if($diskon_sebelum_pajak_service == 1){
					?>
					<td class="xcenter" width="110" colspan="2">DISCOUNT</td>
					<?php
				}
				?>
				<td class="xcenter" width="90" rowspan="2">TAX</td>
				<td class="xcenter" width="90" rowspan="2">SERVICE</td>
				<td class="xcenter" width="100" rowspan="2">SUB TOTAL</td>
				<?php
				if($diskon_sebelum_pajak_service == 0){
					?>
					<td class="xcenter" width="110" colspan="2">DISCOUNT</td>
					<?php
				}
				?>
				<td class="xcenter" width="100" rowspan="2">PEMBULATAN<br/>AVERAGE</td>	
				<td class="xcenter" width="110" rowspan="2">GRAND TOTAL</td>
				<td class="xcenter" width="<?php echo count($payment_data)*100; ?>" colspan="<?php echo count($payment_data); ?>">PAYMENT</td>	
				<td class="xcenter" width="110" rowspan="2">COMPLIMENT</td>
			</tr>
			<tr class="tbl-header">
				<?php
				if($diskon_sebelum_pajak_service == 1){
					?>
					<td class="xcenter" width="110">ITEM</td>
					<td class="xcenter" width="110">BILLING</td>
					<?php
				}
				
				if($diskon_sebelum_pajak_service == 0){
					?>
					<td class="xcenter" width="110">ITEM</td>
					<td class="xcenter" width="110">BILLING</td>
					<?php
				}
				
				echo $payment_data_content;
				?>
			</tr>
			
			<?php
			if(!empty($report_data)){
				
				//SORTING BY QTY TERBANYAK
				$qty_terbanyak = array();
				$qty_terbanyak_SKU = array();
				foreach($report_data as $key => $dtDetSKU){
					if(empty($qty_terbanyak[$key])){
						$qty_terbanyak[$key] = 0;
					}
					
					//SKU
					if(!empty($dtDetSKU)){
						foreach($dtDetSKU as $keySKU => $dtDet){
							
							if(empty($qty_terbanyak_SKU[$key])){
								$qty_terbanyak_SKU[$key] = array();
							}
							
							if(empty($qty_terbanyak_SKU[$key][$keySKU])){
								$qty_terbanyak_SKU[$key][$keySKU] = 0;
							}
							
							if(!empty($dtDet)){
								foreach($dtDet as $det){
									$qty_terbanyak[$key] += $det['total_qty'];
									$qty_terbanyak_SKU[$key][$keySKU] += $det['total_qty'];
									
								}
							}
						}
						
						arsort($qty_terbanyak_SKU[$key]);
					}
					
				}
				arsort($qty_terbanyak);
				
				$nox = 1;
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
				$compliment_total = 0;
				foreach($qty_terbanyak as $key => $totalCat){
					
					if(!empty($qty_terbanyak_SKU[$key])){
						
						$category_name_show = '';
					
						if(!empty($groupCat)){
							if($groupCat == 'skuRecap'){
								if(!empty($item_sku_name[$key])){
									$category_name = $item_sku_name[$key];
								}
							}
						}
						
						if($category_name_show != 'Unknown Category'){
							$category_name_show = $key.' / '.$category_name;
						}
						
						?>
						<tr class="tbl-data">
							<td class="first xcenter xbold"><?php echo $nox; ?></td>
							<td class="xleft xbold" colspan="<?php echo $total_cols-1; ?>"><?php echo $category_name_show; ?></td>
						</tr>
						<?php
						$no = 1;
						$cat_total_qty = 0;
						$cat_total_billing = 0;
						$cat_total_sub_total = 0;
						$cat_total_tax = 0;
						$cat_total_service = 0;
						$cat_total_pembulatan = 0;
						$cat_grand_total = 0;
						$cat_grand_total_payment = array();
						$cat_discount_total = 0;
						$cat_discount_billing_total = 0;
						$cat_compliment_total = 0;
						foreach($qty_terbanyak_SKU[$key] as $keySKU => $total){
							
							$dtDet = 0;
							if(!empty($report_data[$key][$keySKU])){
								$dtDet = $report_data[$key][$keySKU];
							}
							
							$nama_SKU_show = '';
							if(empty($nama_sku[$keySKU])){
								$nama_sku[$keySKU] = '#'.$keySKU;
							}
							
							$nama_SKU_show = $nama_sku[$keySKU];
							
							$rekap_total_qty = 0;
							$rekap_total_billing = 0;
							$rekap_total_sub_total = 0;
							$rekap_total_tax = 0;
							$rekap_total_service = 0;
							$rekap_total_pembulatan = 0;
							$rekap_grand_total = 0;
							$rekap_grand_total_payment = array();
							$rekap_discount_total = 0;
							$rekap_discount_billing_total = 0;
							$rekap_compliment_total = 0;
							
							if(!empty($dtDet)){
								foreach($dtDet as $det){
							
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
											
											if(empty($rekap_grand_total_payment[$key_id])){
												$rekap_grand_total_payment[$key_id] = 0;
											}
											
											$rekap_grand_total_payment[$key_id] += $total_payment;
											$cat_grand_total_payment[$key_id] += $total_payment;
											$grand_total_payment[$key_id] += $total_payment;
											
											$total_payment_show = priceFormat($total_payment);
											
																			
										}
									}	
									
									//CAT
									$rekap_total_qty +=  $det['total_qty'];
									$rekap_total_billing +=  $det['total_billing'];
									$rekap_total_sub_total +=  $det['sub_total'];
									$rekap_total_tax +=  $det['tax_total'];
									$rekap_total_service +=  $det['service_total'];
									$rekap_total_pembulatan +=  $det['total_pembulatan'];
									$rekap_grand_total +=  $det['grand_total'];
									$rekap_discount_total +=  $det['discount_total'];
									$rekap_discount_billing_total +=  $det['discount_billing_total'];
									$rekap_compliment_total +=  $det['compliment_total'];
									
									$cat_total_qty +=  $det['total_qty'];
									$cat_total_billing +=  $det['total_billing'];
									$cat_total_sub_total +=  $det['sub_total'];
									$cat_total_tax +=  $det['tax_total'];
									$cat_total_service +=  $det['service_total'];
									$cat_total_pembulatan +=  $det['total_pembulatan'];
									$cat_grand_total +=  $det['grand_total'];
									$cat_discount_total +=  $det['discount_total'];
									$cat_discount_billing_total +=  $det['discount_billing_total'];
									$cat_compliment_total +=  $det['compliment_total'];
									
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
								
								?>
								<tr class="tbl-data">
									<td class="first xcenter">&nbsp;</td>
									<td class="xleft"><?php echo $keySKU; ?></td>
									<td class="xleft"><?php echo $nama_SKU_show; ?></td>
									<td class="xcenter"><?php echo $rekap_total_qty; ?></td>
									<td class="xright"><?php echo priceFormat($rekap_total_billing); ?></td>
									<?php
									if($diskon_sebelum_pajak_service == 1){
										?>
										<td class="xright"><?php echo priceFormat($rekap_discount_total); ?></td>
										<td class="xright"><?php echo priceFormat($rekap_discount_billing_total); ?></td>
										<?php
									}
									?>
									<td class="xright"><?php echo priceFormat($rekap_total_tax); ?></td>
									<td class="xright"><?php echo priceFormat($rekap_total_service); ?></td>
									<td class="xright"><?php echo priceFormat($rekap_total_sub_total); ?></td>
									<?php
									if($diskon_sebelum_pajak_service == 0){
										?>
										<td class="xright"><?php echo priceFormat($rekap_discount_total); ?></td>
										<td class="xright"><?php echo priceFormat($rekap_discount_billing_total); ?></td>
										<?php
									}
									?>
									<td class="xright"><?php echo priceFormat($rekap_total_pembulatan); ?></td>
									<td class="xright"><?php echo priceFormat($rekap_grand_total); ?></td>
									<?php
									if(!empty($payment_data)){
										foreach($payment_data as $key_id => $dtPay){
											?>
											<td class="xright"><?php echo priceFormat($rekap_grand_total_payment[$key_id]); ?></td>
											<?php
																			
										}
									}
									
									?>
									<td class="xright"><?php echo priceFormat($rekap_compliment_total); ?></td>
								</tr>
								<?php	
							}
							
							
						}
						
						?>
						<tr class="tbl-data">
							<td class="first xright xbold" colspan="<?php echo 3; ?>">TOTAL ORDER: <?php echo $category_name_show; ?> </td>
							<td class="xcenter xbold"><?php echo priceFormat($cat_total_qty); ?></td>
							<td class="xright xbold"><?php echo priceFormat($cat_total_billing); ?></td>
							<?php
							if($diskon_sebelum_pajak_service == 1){
								?>
								<td class="xright xbold"><?php echo priceFormat($cat_discount_total); ?></td>
								<td class="xright xbold"><?php echo priceFormat($cat_discount_billing_total); ?></td>
								<?php
							}
							?>
							<td class="xright xbold"><?php echo priceFormat($cat_total_tax); ?></td>
							<td class="xright xbold"><?php echo priceFormat($cat_total_service); ?></td>
							<td class="xright xbold"><?php echo priceFormat($cat_total_sub_total); ?></td>
							<?php
							if($diskon_sebelum_pajak_service == 0){
								?>
								<td class="xright xbold"><?php echo priceFormat($cat_discount_total); ?></td>
								<td class="xright xbold"><?php echo priceFormat($cat_discount_billing_total); ?></td>
								<?php
							}
							?>
							<td class="xright xbold"><?php echo priceFormat($cat_total_pembulatan); ?></td>
							<td class="xright xbold"><?php echo priceFormat($cat_grand_total); ?></td>
							<?php
							if(!empty($payment_data)){
								foreach($payment_data as $key_id => $dtPay){
									
									$total_payment = 0;
									if(!empty($cat_grand_total_payment[$key_id])){
										$total_payment = $cat_grand_total_payment[$key_id];
									}
									
									$total_payment_show = priceFormat($total_payment);
									
									?>
									<td class="xright xbold"><?php echo $total_payment_show; ?></td>
									<?php
																	
								}
							}
							
							?>
							<td class="xright xbold"><?php echo priceFormat($cat_compliment_total); ?></td>
						</tr>
						<?php
						
						$nox++;
					}
					
					
				}
				
				?>
				<tr class="tbl-data">
					<td class="first xright xbold" colspan="<?php echo 3; ?>">TOTAL ALL ITEM SKU </td>
					<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_billing); ?></td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xright xbold"><?php echo priceFormat($discount_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($discount_billing_total); ?></td>
						<?php
					}
					?>
					<td class="xright xbold"><?php echo priceFormat($total_tax); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_service); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_sub_total); ?></td>
					<?php
					if($diskon_sebelum_pajak_service == 0){
						?>
						<td class="xright xbold"><?php echo priceFormat($discount_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($discount_billing_total); ?></td>
						<?php
					}
					?>
					<td class="xright xbold"><?php echo priceFormat($total_pembulatan); ?></td>
					<td class="xright xbold"><?php echo priceFormat($grand_total); ?></td>
					<?php
					if(!empty($payment_data)){
						foreach($payment_data as $key_id => $dtPay){
							
							$total = 0;
							if(!empty($grand_total_payment[$key_id])){
								$total = priceFormat($grand_total_payment[$key_id]);
							}							
							?>
							<td class="xright xbold"><?php echo $total; ?></td>
							<?php
						}
					}
					
					?>
					<td class="xright xbold"><?php echo priceFormat($compliment_total); ?></td>
				</tr>
				<?php
			}else{
			?>
				<tr class="tbl-data">
					<td colspan="<?php echo $total_cols; ?>" class="first xleft">Data Not Found</td>
				</tr>
			<?php
			}
			?>
			
			<tr class="tbl-sign">
				<td colspan="<?php echo $total_cols; ?>" class="first xleft">
					<br/>
					<br/>
					<div class="fleft" style="width:200px;">
						<br/><br/><br/><br/>
						Printed: <?php echo date("d-m-Y H:i:s");?>
					</div>
					<div class="fright" style="width:300px;">
						Prepared by:<br/><br/><br/><br/>
						----------------------------
					</div>
					<div class="fright" style="width:300px;">
						Approved by:<br/><br/><br/><br/>
						----------------------------
					</div>
					
					<div class="fclear"></div>
					<br/>
				</td>
			</tr>			
		</table>
				
		
	</div>
	
	<?php
		if($do == 'print' OR $do == true){
		?>
		<script type="text/javascript">
			window.print();
		</script>
		<?php
		}
	?>
</body>
</html>