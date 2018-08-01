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
		$set_width = 1640;
		$total_cols = 15;
		
		
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
										
							<div class="title_report xcenter"><?php echo $report_name;?></div>
							<div class="subtitle_report xcenter"><?php echo 'Period : '.$date_from.' TO '.$date_till;?></div>			
							
						</div>
					</td>
				</tr>
				<tr class="tbl-header">
					<td class="first xcenter" width="50" rowspan="2">NO</td>
					<td class="xcenter" width="130" rowspan="2">PAYMENT DATE</td>
					<td class="xcenter" width="80" rowspan="2">BILLING NO.</td>
					<td class="xcenter" width="130" rowspan="2">DISCOUNT NAME</td>
					<td class="xcenter" width="100" rowspan="2">DISCOUNT TYPE</td>
					<td class="xcenter" width="100" rowspan="2">QTY MENU</td>
					<td class="xcenter" width="110" rowspan="2">TOTAL BILLING</td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xcenter" width="220" colspan="2">DISCOUNT</td>	
						<?php
					}
					?>
					<td class="xcenter" width="100" rowspan="2">TAX</td>
					<td class="xcenter" width="100" rowspan="2">SERVICE</td>
					<td class="xcenter" width="100" rowspan="2">SUB TOTAL</td>
					<?php
					if($diskon_sebelum_pajak_service == 0){
						?>
						<td class="xcenter" width="220" colspan="2">DISCOUNT</td>	
						<?php
					}
					?>
					<td class="xcenter" width="100" rowspan="2">PEMBULATAN</td>	
					<td class="xcenter" width="100" rowspan="2">COMPLIMENT</td>
					<td class="xcenter" width="120" rowspan="2">GRAND TOTAL</td>
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
					$grand_total_pembulatan = 0;
					$grand_discount_total = 0;
					$grand_discount_billing_total = 0;
					$grand_total_compliment = 0;
					$grand_total_payment = array();
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
								if($diskon_sebelum_pajak_service == 1){
									?>
									<td class="xright"><?php echo $det['discount_total_show']; ?></td>
									<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
									<?php
								}
								?>
								<td class="xright"><?php echo $det['tax_total_show']; ?></td>
								<td class="xright"><?php echo $det['service_total_show']; ?></td>
								<td class="xright"><?php echo $det['sub_total_show']; ?></td>
								<?php
								if($diskon_sebelum_pajak_service == 0){
									?>
									<td class="xright"><?php echo $det['discount_total_show']; ?></td>
									<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
									<?php
								}
								?>
								<td class="xright"><?php echo $det['total_pembulatan_show']; ?></td>
								<td class="xright"><?php echo $det['total_compliment_show']; ?></td>
								<td class="xright"><?php echo $det['grand_total_show']; ?></td>
							</tr>
							<?php	
							
							$total_billing +=  $det['total_billing'];
							$total_qty +=  $det['total_qty'];
							$total_tax +=  $det['tax_total'];
							$total_service +=  $det['service_total'];
							$grand_total +=  $det['grand_total'];
							$grand_total_compliment += $det['total_compliment'];
							$grand_sub_total += $det['sub_total'];
							$grand_total_pembulatan += $det['total_pembulatan'];
							$grand_discount_total += $det['discount_total'];
							$grand_discount_billing_total += $det['discount_billing_total'];
							$grand_total_dp += $det['total_dp'];
							$no++;
						}
						
					}
					
					?>
					<tr class="tbl-total">
						<td class="first xright xbold" colspan="<?php echo 5; ?>">TOTAL</td>
						<td class="xcenter xbold"><?php echo $total_qty; ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_billing); ?></td>
						<?php
						if($diskon_sebelum_pajak_service == 1){
							?>
							<td class="xright xbold"><?php echo priceFormat($grand_discount_total); ?></td>
							<td class="xright xbold"><?php echo priceFormat($grand_discount_billing_total); ?></td>
							<?php
						}
						?>
						<td class="xright xbold"><?php echo priceFormat($total_tax); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_service); ?></td>
						<td class="xright xbold"><?php echo priceFormat($grand_sub_total); ?></td>
						<?php
						if($diskon_sebelum_pajak_service == 0){
							?>
							<td class="xright xbold"><?php echo priceFormat($grand_discount_total); ?></td>
							<td class="xright xbold"><?php echo priceFormat($grand_discount_billing_total); ?></td>
							<?php
						}
						?>
						<td class="xright xbold"><?php echo priceFormat($grand_total_pembulatan); ?></td>
						<td class="xright xbold"><?php echo priceFormat($grand_total_compliment); ?></td>
						<td class="xright xbold"><?php echo priceFormat($grand_total); ?></td>
					</tr>
					<?php
				}else{
				?>
					<tr class="tbl-data">
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
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xcenter" width="220" colspan="2">DISCOUNT</td>	
						<?php
					}
					?>
					<td class="xcenter" width="100" rowspan="2">TAX</td>
					<td class="xcenter" width="100" rowspan="2">SERVICE</td>
					<td class="xcenter" width="100" rowspan="2">SUB TOTAL</td>
					<?php
					if($diskon_sebelum_pajak_service == 0){
						?>
						<td class="xcenter" width="220" colspan="2">DISCOUNT</td>	
						<?php
					}
					?>
					<td class="xcenter" width="100" rowspan="2">PEMBULATAN</td>	
					<td class="xcenter" width="100" rowspan="2">COMPLIMENT</td>
					<td class="xcenter" width="120" rowspan="2">GRAND TOTAL</td>
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
					$grand_total_pembulatan = 0;
					$grand_discount_total = 0;
					$grand_discount_billing_total = 0;
					$grand_total_compliment = 0;
					$grand_total_payment = array();
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
							if($diskon_sebelum_pajak_service == 1){
								?>
								<td class="xright"><?php echo $det['discount_total_show']; ?></td>
								<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
								<?php
							}
							?>
							<td class="xright"><?php echo $det['tax_total_show']; ?></td>
							<td class="xright"><?php echo $det['service_total_show']; ?></td>
							<td class="xright"><?php echo $det['sub_total_show']; ?></td>
							<?php
							if($diskon_sebelum_pajak_service == 0){
								?>
								<td class="xright"><?php echo $det['discount_total_show']; ?></td>
								<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
								<?php
							}
							?>
							<td class="xright"><?php echo $det['total_pembulatan_show']; ?></td>
							<td class="xright"><?php echo $det['total_compliment_show']; ?></td>
							<td class="xright"><?php echo $det['grand_total_show']; ?></td>
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
						$grand_total_pembulatan += $det['total_pembulatan'];
						$grand_discount_total += $det['discount_total'];
						$grand_discount_billing_total += $det['discount_billing_total'];
						$grand_total_dp += $det['total_dp'];
						$no++;
					}
					
					?>
					<tr class="tbl-total">
						<td class="first xright xbold" colspan="<?php echo 5; ?>">TOTAL</td>
						<td class="xcenter xbold"><?php echo $total_qty_menu;	; ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_billing); ?></td>
						<?php
						if($diskon_sebelum_pajak_service == 1){
							?>
							<td class="xright xbold"><?php echo priceFormat($grand_discount_total); ?></td>
							<td class="xright xbold"><?php echo priceFormat($grand_discount_billing_total); ?></td>
							<?php
						}
						?>
						<td class="xright xbold"><?php echo priceFormat($total_tax); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_service); ?></td>
						<td class="xright xbold"><?php echo priceFormat($grand_sub_total); ?></td>
						<?php
						if($diskon_sebelum_pajak_service == 0){
							?>
							<td class="xright xbold"><?php echo priceFormat($grand_discount_total); ?></td>
							<td class="xright xbold"><?php echo priceFormat($grand_discount_billing_total); ?></td>
							<?php
						}
						?>
						<td class="xright xbold"><?php echo priceFormat($grand_total_pembulatan); ?></td>
						<td class="xright xbold"><?php echo priceFormat($grand_total_compliment); ?></td>
						<td class="xright xbold"><?php echo priceFormat($grand_total); ?></td>
					</tr>
					<?php
				}else{
					
					?>
					<tr class="tbl-data">
						<td class="first xcenter" colspan="<?php echo $total_cols; ?>">Data Buyget Not Found</td>
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
						<div class="fright" style="width:250px;">
							Prepared by:<br/><br/><br/><br/>
							----------------------------
						</div>
						<div class="fright" style="width:250px;">
							Approved by:<br/><br/><br/><br/>
							----------------------------
						</div>
						
						<div class="fclear"></div>
						<br/>
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