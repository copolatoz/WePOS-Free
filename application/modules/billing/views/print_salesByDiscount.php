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
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<thead>
				<tr class="tbl-title">
					<td colspan="<?php echo $total_cols ?>">
						<div>
							<div class="logo">
								
								<!-- <img height="80" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>"> -->
								
							</div>
										
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
				<tr class="tbl-header">
					<td class="first xcenter" width="50" rowspan="2">NO</td>
					<td class="xcenter" width="130" rowspan="2">TANGGAL-JAM</td>
					<td class="xcenter" width="80" rowspan="2">NO BILLING</td>
					<td class="xcenter" width="150" rowspan="2">NAMA DISKON</td>
					<td class="xcenter" width="100" rowspan="2">TIPE DISKON</td>
					<td class="xcenter" width="100" rowspan="2">QTY MENU</td>
					<td class="xcenter" width="110" rowspan="2">TOTAL BILLING</td>
					<?php
					if($diskon_sebelum_pajak_service == 1 OR count($display_discount_type) > 1){
						if(count($display_discount_type) > 1){
							?>
							<td class="xcenter" width="220" colspan="2">DISCOUNT BEFORE TAX-SERVICE</td>	
							<?php
						}else{
							?>
							<td class="xcenter" width="220" colspan="2">DISCOUNT</td>	
							<?php
						}
					}
					
					if($diskon_sebelum_pajak_service == 0 OR count($display_discount_type) > 1){
						if(count($display_discount_type) > 1){
							?>
							<td class="xcenter" width="220" colspan="2">DISCOUNT AFTER TAX-SERVICE</td>	
							<?php
						}else{
							?>
							<td class="xcenter" width="220" colspan="2">DISCOUNT AFTER TAX-SERVICE</td>	
							<?php
						}
					}
					
					if($show_compliment == true){
					?>
					<td class="xcenter" width="100" rowspan="2">COMPLIMENT</td>
					<?php
					}
					?>
					<td class="xcenter" width="100" rowspan="2">NET SALES</td>
					<?php
					if($show_tax == true){
					?>
					<td class="xcenter" width="90" rowspan="2">TAX</td>
					<?php
					}
					if($show_service == true){
					?>
					<td class="xcenter" width="90" rowspan="2">SERVICE</td>
					<?php
					}
					
					if($show_pembulatan == true){
						?>
						<td class="xcenter" width="100" rowspan="2">PEMBULATAN</td>	
						<?php
					}
					?>
					<td class="xcenter" width="120" rowspan="2">GRAND TOTAL</td>
					<?php
					if($show_note == true){
					?>
					<td class="xcenter" width="300" rowspan="2">NOTE</td>
					<?php
					}
					
					if($show_shift_kasir == true){
					?>
					<td class="xcenter" width="150" rowspan="2">SHIFT/KASIR</td>
					<?php
					}
					?>
				</tr>
				<tr class="tbl-header">
					
					<?php
					if($diskon_sebelum_pajak_service == 1 OR count($display_discount_type) > 1){
						?>
						<td class="xcenter" width="110">ITEM</td>
						<td class="xcenter" width="110">BILLING</td>
						<?php
					}
					
					if($diskon_sebelum_pajak_service == 0 OR count($display_discount_type) > 1){
						?>
						<td class="xcenter" width="110">ITEM</td>
						<td class="xcenter" width="110">BILLING</td>
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
							<tr class="tbl-data">
								<td class="first xcenter"><?php echo $no; ?></td>
								<td class="xcenter"><?php echo $det['payment_date']; ?></td>
								<td class="xcenter"><?php echo $det['billing_no']; ?></td>
								<td class="xleft"><?php echo $discount_name; ?></td>
								<td class="xcenter"><?php echo $det['discount_type']; ?></td>
								<td class="xcenter"><?php echo $det['total_qty']; ?></td>
								<td class="xright"><?php echo $det['total_billing_show']; ?></td>
								<?php
								if($diskon_sebelum_pajak_service == 1 OR count($display_discount_type) > 1){
									if(count($display_discount_type) > 1){
										if(count($display_discount_type) > 1 AND $det['diskon_sebelum_pajak_service'] == 1){
											?>
											<td class="xright"><?php echo $det['discount_total_show']; ?></td>
											<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
											<?php
										}else{
											?>
											<td class="xright">-</td>
											<td class="xright">-</td>
											<?php
										}
									}else{
										?>
										<td class="xright"><?php echo $det['discount_total_show']; ?></td>
										<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
										<?php
									}
								}
								
								if($diskon_sebelum_pajak_service == 0 OR count($display_discount_type) > 1){
									if(count($display_discount_type) > 1){
										if(count($display_discount_type) > 1 AND $det['diskon_sebelum_pajak_service'] == 0){
											?>
											<td class="xright"><?php echo $det['discount_total_show']; ?></td>
											<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
											<?php
										}else{
											?>
											<td class="xright">-</td>
											<td class="xright">-</td>
											<?php
										}
									}else{
										?>
										<td class="xright"><?php echo $det['discount_total_show']; ?></td>
										<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
										<?php
									}
								}
								
								if($show_compliment == true){
								?>
								<td class="xright"><?php echo $det['total_compliment_show']; ?></td>
								<?php
								}
								?>
								<td class="xright"><?php echo $det['net_sales_total_show']; ?></td>
								<?php
								if($show_tax == true){
								?>
								<td class="xright"><?php echo $det['tax_total_show']; ?></td>
								<?php
								}
								if($show_service == true){
								?>
								<td class="xright"><?php echo $det['service_total_show']; ?></td>
								<?php
								}
								
								if($show_pembulatan == true){
									?>
									<td class="xright"><?php echo $det['total_pembulatan_show']; ?></td>
									<?php
								}
								?>
								<td class="xright"><?php echo $det['grand_total_show']; ?></td>
								<?php
								if($show_note == true){
								?>
								<td class="xleft"><?php echo $det['payment_note']; ?></td>
								<?php
								}
								
								if($show_shift_kasir == true){
								?>
								<td class="xleft"><?php echo $det['nama_shift'].'/'.$det['nama_kasir']; ?></td>
								<?php
								}
								?>
							</tr>
							<?php	
							
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
						
					}
					
					?>
					<tr class="tbl-total">
						<td class="first xright xbold" colspan="<?php echo 5; ?>">TOTAL</td>
						<td class="xcenter xbold"><?php echo $total_qty; ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_billing); ?></td>
						<?php
						if($diskon_sebelum_pajak_service == 1 OR count($display_discount_type) > 1){
							if(count($display_discount_type) > 1){
								?>
								<td class="xright xbold"><?php echo priceFormat($grand_discount_total_before); ?></td>
								<td class="xright xbold"><?php echo priceFormat($grand_discount_billing_total_before); ?></td>
								<?php
							}else{
								
								?>
								<td class="xright xbold"><?php echo priceFormat($grand_discount_total); ?></td>
								<td class="xright xbold"><?php echo priceFormat($grand_discount_billing_total); ?></td>
								<?php
								
							}
							
						}
						
						if($diskon_sebelum_pajak_service == 0 OR count($display_discount_type) > 1){
							if(count($display_discount_type) > 1){
								?>
								<td class="xright xbold"><?php echo priceFormat($grand_discount_total_after); ?></td>
								<td class="xright xbold"><?php echo priceFormat($grand_discount_billing_total_after); ?></td>
								<?php
							}else{
								
								?>
								<td class="xright xbold"><?php echo priceFormat($grand_discount_total); ?></td>
								<td class="xright xbold"><?php echo priceFormat($grand_discount_billing_total); ?></td>
								<?php
								
							}
							
						}
						
						if($show_compliment == true){
						?>
						<td class="xright xbold"><?php echo priceFormat($grand_total_compliment); ?></td>
						<?php
						}
						?>
						<td class="xright xbold"><?php echo priceFormat($grand_net_sales_total); ?></td>
						<?php
						if($show_tax == true){
						?>
						<td class="xright xbold"><?php echo priceFormat($total_tax); ?></td>
						<?php
						}
						if($show_service == true){
						?>
						<td class="xright xbold"><?php echo priceFormat($total_service); ?></td>
						<?php
						}
						
						if($show_pembulatan == true){
							?>
							<td class="xright xbold"><?php echo priceFormat($grand_total_pembulatan); ?></td>
							<?php
						}
						?>
						<td class="xright xbold"><?php echo priceFormat($grand_total); ?></td>
						<?php
						if($show_note == true){
						?>
						<td class="xright xbold">&nbsp;</td>
						<?php
						}
						
						if($show_shift_kasir == true){
						?>
						<td class="xright xbold">&nbsp;</td>
						<?php
						}
						?>
					</tr>
					<?php
				}else{
				?>
					<tr class="tbl-total">
						<td class="first xcenter" colspan="<?php echo $total_cols; ?>">Data Not Found</td>
					</tr>
				<?php
				}
				?>
				<tr class="tbl-data">
					<td class="first xcenter" colspan="<?php echo $total_cols; ?>">&nbsp;</td>
				</tr>
				<tr class="tbl-header">
					<td class="first xcenter" width="50" rowspan="2">NO</td>
					<td class="xcenter" width="130" rowspan="2">PAYMENT DATE</td>
					<td class="xcenter" width="80" rowspan="2">BILLING NO.</td>
					<td class="xcenter" width="130" rowspan="2" colspan="2">BUY &amp; GET/PROMO</td>
					<td class="xcenter" width="100" rowspan="2">QTY MENU</td>
					<td class="xcenter" width="110" rowspan="2">TOTAL BILLING</td>
					<?php
					if($diskon_sebelum_pajak_service == 1 OR count($display_discount_type) > 1){
						if(count($display_discount_type) > 1){
							?>
							<td class="xcenter" width="220" colspan="2">DISCOUNT BEFORE TAX-SERVICE</td>	
							<?php
						}else{
							?>
							<td class="xcenter" width="220" colspan="2">DISCOUNT</td>	
							<?php
						}
						
					}
							
					if($diskon_sebelum_pajak_service == 0 OR count($display_discount_type) > 1){
						if(count($display_discount_type) > 1){
							?>
							<td class="xcenter" width="220" colspan="2">DISCOUNT AFTER TAX-SERVICE</td>	
							<?php
						}else{
							?>
							<td class="xcenter" width="220" colspan="2">DISCOUNT AFTER TAX-SERVICE</td>	
							<?php
						}
					}
					
					if($show_compliment == true){
					?>
					<td class="xcenter" width="100" rowspan="2">COMPLIMENT</td>
					<?php
					}
					?>
					<td class="xcenter" width="100" rowspan="2">NET SALES</td>
					<?php
						
					if($show_tax == true){
					?>
					<td class="xcenter" width="100" rowspan="2">TAX</td>
					<?php
					}
					if($show_service == true){
					?>
					<td class="xcenter" width="100" rowspan="2">SERVICE</td>
					<?php
					}
					
					if($show_pembulatan == true){
						?>
						<td class="xcenter" width="100" rowspan="2">PEMBULATAN</td>	
						<?php
					}
					?>
					<td class="xcenter" width="120" rowspan="2">GRAND TOTAL</td>
					<?php
					if($show_note == true){
					?>
					<td class="xcenter" width="200" rowspan="2">NOTE</td>
					<?php
					}
					
					if($show_shift_kasir == true){
					?>
					<td class="xcenter" width="150" rowspan="2">SHIFT/KASIR</td>
					<?php
					}
					?>
				</tr>
				<tr class="tbl-header">
					
					<?php
					if($diskon_sebelum_pajak_service == 1 OR count($display_discount_type) > 1){
						?>
						<td class="xcenter" width="110">ITEM</td>
						<td class="xcenter" width="110">BILLING</td>
						<?php
					}
					
					if($diskon_sebelum_pajak_service == 0 OR count($display_discount_type) > 1){
						?>
						<td class="xcenter" width="110">ITEM</td>
						<td class="xcenter" width="110">BILLING</td>
						<?php
					}
					?>
					
				</tr>
				<?php
				
				if(!empty($buyget_data) AND ($discount_type == '' OR $discount_type == 'buyget')){
				
					$no = 1;
					$total_qty_billing = 0;
					$total_qty_menu = 0;
					$total_billing = 0;
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
						
						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xcenter"><?php echo $det['payment_date']; ?></td>
							<td class="xcenter"><?php echo $det['billing_no']; ?></td>
							<td class="xleft" colspan="2"><?php echo $discount_name; ?></td>
							<td class="xcenter"><?php echo $det['total_qty']; ?></td>
							<td class="xright"><?php echo $det['total_billing_show']; ?></td>
							<?php
							if($diskon_sebelum_pajak_service == 1 OR count($display_discount_type) > 1){
								if(count($display_discount_type) > 1){
									if(count($display_discount_type) > 1 AND $det['diskon_sebelum_pajak_service'] == 1){
										?>
										<td class="xright"><?php echo $det['discount_total_show']; ?></td>
										<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
										<?php
									}else{
										?>
										<td class="xright">-</td>
										<td class="xright">-</td>
										<?php
									}
								}else{
									?>
									<td class="xright"><?php echo $det['discount_total_show']; ?></td>
									<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
									<?php
								}
						
							}
							
							if($diskon_sebelum_pajak_service == 0 OR count($display_discount_type) > 1){
								if(count($display_discount_type) > 1){
									if(count($display_discount_type) > 1 AND $det['diskon_sebelum_pajak_service'] == 0){
										?>
										<td class="xright"><?php echo $det['discount_total_show']; ?></td>
										<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
										<?php
									}else{
										?>
										<td class="xright">-</td>
										<td class="xright">-</td>
										<?php
									}
								}else{
									?>
									<td class="xright"><?php echo $det['discount_total_show']; ?></td>
									<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
									<?php
								}
							}
							
							if($show_compliment == true){
							?>
							<td class="xright"><?php echo $det['total_compliment_show']; ?></td>
							<?php
							}
							?>
							<td class="xright"><?php echo $det['net_sales_total_show']; ?></td>
							<?php	
							if($show_tax == true){
							?>
							<td class="xright"><?php echo $det['tax_total_show']; ?></td>
							<?php
							}
							if($show_service == true){
							?>
							<td class="xright"><?php echo $det['service_total_show']; ?></td>
							<?php
							}
							
							if($show_pembulatan == true){
								?>
								<td class="xright"><?php echo $det['total_pembulatan_show']; ?></td>
								<?php
							}
							?>
							<td class="xright"><?php echo $det['grand_total_show']; ?></td>
							<?php
							if($show_note == true){
							?>
							<td class="xleft"><?php echo $det['payment_note']; ?></td>
							<?php
							}
							
							if($show_shift_kasir == true){
							?>
							<td class="xleft"><?php echo $det['nama_shift'].'/'.$det['nama_kasir']; ?></td>
							<?php
							}
							?>
						</tr>
						<?php	
						
						$total_qty_billing +=  $det['total_qty_billing'];
						$total_qty_menu +=  $det['total_qty'];
						$total_billing +=  $det['total_billing'];
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
					
					?>
					<tr class="tbl-total">
						<td class="first xright xbold" colspan="<?php echo 5; ?>">TOTAL</td>
						<td class="xcenter xbold"><?php echo $total_qty_menu;	; ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_billing); ?></td>
						<?php
						if($diskon_sebelum_pajak_service == 1 OR count($display_discount_type) > 1){
							if(count($display_discount_type) > 1){
								?>
								<td class="xright xbold"><?php echo priceFormat($grand_discount_total_before); ?></td>
								<td class="xright xbold"><?php echo priceFormat($grand_discount_billing_total_before); ?></td>
								<?php
							}else{
								?>
								<td class="xright xbold"><?php echo priceFormat($grand_discount_total); ?></td>
								<td class="xright xbold"><?php echo priceFormat($grand_discount_billing_total); ?></td>
								<?php
							}
						
						}
						
						if($diskon_sebelum_pajak_service == 0 OR count($display_discount_type) > 1){
							if(count($display_discount_type) > 1){
								?>
								<td class="xright xbold"><?php echo priceFormat($grand_discount_total_after); ?></td>
								<td class="xright xbold"><?php echo priceFormat($grand_discount_billing_total_after); ?></td>
								<?php
							}else{
								?>
								<td class="xright xbold"><?php echo priceFormat($grand_discount_total); ?></td>
								<td class="xright xbold"><?php echo priceFormat($grand_discount_billing_total); ?></td>
								<?php
							}
						
						}
						
						if($show_compliment == true){
						?>
						<td class="xright xbold"><?php echo priceFormat($grand_total_compliment); ?></td>
						<?php
						}
						?>
						<td class="xright xbold"><?php echo priceFormat($grand_net_sales_total); ?></td>
						<?php		
						if($show_tax == true){
						?>
						<td class="xright xbold"><?php echo priceFormat($total_tax); ?></td>
						<?php
						}
						if($show_service == true){
						?>
						<td class="xright xbold"><?php echo priceFormat($total_service); ?></td>
						<?php
						}
						
						if($show_pembulatan == true){
							?>
							<td class="xright xbold"><?php echo priceFormat($grand_total_pembulatan); ?></td>
							<?php
						}
						?>
						<td class="xright xbold"><?php echo priceFormat($grand_total); ?></td>
						<?php
						if($show_note == true){
						?>
						<td class="xright xbold">&nbsp;</td>
						<?php
						}
						
						if($show_shift_kasir == true){
						?>
						<td class="xright xbold">&nbsp;</td>
						<?php
						}
						?>
					</tr>
					<?php
				}else{
					
					?>
					<tr class="tbl-total">
						<td class="first xcenter" colspan="<?php echo $total_cols; ?>">Data Buyget Not Found</td>
					</tr>
					<?php
				}
				?>
				<tr>
					<td colspan="<?php echo $total_cols-4; ?>" class="first xleft">
						<br/>
						<br/>
						<br/>
						<br/>
						Printed: <?php echo date("d-m-Y H:i:s"); ?>
						<br/>
					</td>
					<td colspan="2" class="xcenter">
						<br/>
						Prepared by:<br/><br/><br/><br/>
						----------------------------
					</td>
					<td colspan="2" class="xcenter">
						<br/>
						Approved by:<br/><br/><br/><br/>
						----------------------------
					</td>
				</tr>			
			</tbody>
		</table>
				
		
	</div>
	
	<?php
		if($do == 'print'){
		?>
		<script type="text/javascript">
			window.print();
		</script>
		<?php
		}
	?>
</body>
</html>