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
		$set_width = 1320;
		$total_cols = 14;
		
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
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<thead>
				<tr class="tbl-title">
					<td colspan="<?php echo $total_cols ?>">
						<div>
							<div class="logo">
								
								<!-- <img height="80" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>"> -->
								
							</div>
										
							<div class="title_report xcenter"><?php echo $this->session->userdata('client_name'); ?></div>
							<div class="title_report xcenter"><?php echo $report_name;?></div>
							<div class="subtitle_report xcenter"><?php echo 'Period : '.$date_from.' TO '.$date_till;?></div>			
							
						</div>
					</td>
				</tr>
			</thead>
			<tbody>
				
				<tr class="tbl-header">
					<td class="first xcenter" width="1000" colspan="9">OVERALL SUMMARY</td>
					<td class="xcenter" colspan="<?php echo $total_cols-9 ;?>">&nbsp;</td>
				</tr>
				<tr class="tbl-header">
					<td class="first xcenter" colspan="2">SALES INFO</td>
					<td class="xcenter" colspan="2">TOTAL</td>
					<td class="xcenter" >&nbsp;</td>
					<td class="xcenter" colspan="2">BILL &amp; GUEST INFO</td>
					<td class="xcenter"colspan="2">TOTAL</td>
					<td class="xcenter" colspan="<?php echo $total_cols-9 ;?>">&nbsp;</td>
				</tr>
				<tr class="tbl-data">
					<td class="first xleft" colspan="2">Menu Sales</td>
					<td class="xright" colspan="2"><?php echo priceFormat($summary_data['total_billing']); ?></td>
					<td class="xleft">&nbsp;</td>
					<td class="xleft" colspan="2">Total of Menu Discount</td>
					<td class="xright" colspan="2"><?php echo $summary_data['total_of_item_discount']; ?></td>
					<td class="xcenter" colspan="<?php echo $total_cols-9; ?>">&nbsp;</td>
				</tr>
				<tr class="tbl-data">
					<td class="first xleft" colspan="2">Discount Per Item</td>
					<td class="xright" colspan="2"><?php echo priceFormat($summary_data['total_discount_item']); ?></td>
					<td class="xleft">&nbsp;</td>
					<td class="xleft" colspan="2">Total Bill</td>
					<td class="xright" colspan="2"><?php echo priceFormat($summary_data['total_of_billing']); ?></td>
					<td class="xcenter" colspan="<?php echo $total_cols-9; ?>">&nbsp;</td>
				</tr>
				<?php
				$net_sales = $summary_data['total_billing'] - $summary_data['total_discount_item'];
				?>
				<tr class="tbl-data">
					<td class="first xleft" colspan="2">Menu Net Sales</td>
					<td class="xright" colspan="2"><?php echo priceFormat($net_sales); ?></td>
					<td class="xleft">&nbsp;</td>
					<td class="xleft" colspan="2">Sales Per Bill</td>
					<td class="xright" colspan="2"><?php echo priceFormat($summary_data['sales_per_bill']); ?></td>
					<td class="xcenter" colspan="<?php echo $total_cols-9; ?>">&nbsp;</td>
				</tr>
				<tr class="tbl-data">
					<td class="first xleft" colspan="2">Discount Per Billing</td>
					<td class="xright" colspan="2"><?php echo priceFormat($summary_data['total_discount_billing']); ?></td>
					<td class="xleft">&nbsp;</td>
					<td class="xleft" colspan="2">Total Guest</td>
					<td class="xright" colspan="2"><?php echo priceFormat($summary_data['total_of_guest']); ?></td>
					<td class="xcenter" colspan="<?php echo $total_cols-9; ?>">&nbsp;</td>
				</tr>
				<?php
				$total_net_sales = $net_sales - $summary_data['total_discount_billing'];
				?>
				<tr class="tbl-data">
					<td class="first xbold" colspan="2">Total Net Sales</td>
					<td class="xright" colspan="2"><?php echo priceFormat($total_net_sales); ?></td>
					<td class="xleft">&nbsp;</td>
					<td class="xleft" colspan="2">Sales Per Guest</td>
					<td class="xright" colspan="2"><?php echo priceFormat($summary_data['sales_per_guest']); ?></td>
					<td class="xcenter" colspan="<?php echo $total_cols-9; ?>">&nbsp;</td>
				</tr>
				<tr class="tbl-data">
					<td class="first xleft" colspan="2">Service Charge</td>
					<td class="xright" colspan="2"><?php echo priceFormat($summary_data['service_total']); ?></td>
					<td class="xleft">&nbsp;</td>
					<td class="xleft" colspan="2">Average Daily Guest</td>
					<td class="xright" colspan="2"><?php echo priceFormat($summary_data['average_daily_guest']); ?></td>
					<td class="xcenter" colspan="<?php echo $total_cols-9; ?>">&nbsp;</td>
				</tr>
				<tr class="tbl-data">
					<td class="first xleft" colspan="2">Tax</td>
					<td class="xright" colspan="2"><?php echo priceFormat($summary_data['tax_total']); ?></td>
					<td class="xleft">&nbsp;</td>
					<td class="xleft" colspan="2">Average Daily Sales</td>
					<td class="xright" colspan="2"><?php echo priceFormat($summary_data['average_daily_sales']); ?></td>
					<td class="xcenter" colspan="<?php echo $total_cols-9; ?>">&nbsp;</td>
				</tr>
				<tr class="tbl-data">
					<td class="first xleft" colspan="2">Pembulatan</td>
					<td class="xright" colspan="2"><?php echo priceFormat($summary_data['total_pembulatan']); ?></td>
					<td class="xleft">&nbsp;</td>
					<td class="xleft" colspan="2">Sales without Service</td>
					<td class="xright" colspan="2"><?php echo priceFormat($summary_data['sales_without_service']); ?></td>
					<td class="xcenter" colspan="<?php echo $total_cols-9; ?>">&nbsp;</td>
				</tr>
				<tr class="tbl-data">
					<td class="first xbold" colspan="2">Grand Total</td>
					<td class="xright xbold" colspan="2"><?php echo priceFormat($summary_data['grand_total']); ?></td>
					<td class="xleft">&nbsp;</td>
					<td class="xleft" colspan="2">Sales without Tax</td>
					<td class="xright" colspan="2"><?php echo priceFormat($summary_data['sales_without_tax']); ?>&nbsp;</td>
					<td class="xcenter" colspan="<?php echo $total_cols-9; ?>">&nbsp;</td>
				</tr>
				<tr class="tbl-data">
					<td class="first" colspan="<?php echo $total_cols; ?>">&nbsp;</td>
				</tr>
				<tr class="tbl-header">
					<td class="first" colspan="<?php echo $total_cols; ?>">BILLING SUMMARY</td>
				</tr>
				<tr class="tbl-header">
					<td class="first xcenter" width="50" rowspan="2">NO</td>
					<td class="xcenter" width="130" rowspan="2">BILLING TYPE</td>
					<td class="xcenter" width="80" rowspan="2">QTY BILLING</td>
					<td class="xcenter" width="120" rowspan="2">SALES</td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xcenter" width="100" colspan="2">DISCOUNT</td>	
						<td class="xcenter" width="100" rowspan="2">NET SALES</td>
						<?php
					}
					?>
					<td class="xcenter" width="100" rowspan="2">TAX</td>
					<td class="xcenter" width="100" rowspan="2">SERVICE</td>
					<td class="xcenter" width="100" rowspan="2">SUB TOTAL</td>
					<?php
					if($diskon_sebelum_pajak_service == 0){
						?>
						<td class="xcenter" width="100" colspan="2">DISCOUNT</td>	
						<?php
					}
					?>
					<td class="xcenter" width="100" rowspan="2">PEMBULATAN</td>
					<td class="xcenter" width="120" rowspan="2">GRAND TOTAL</td>
					<td class="xcenter" width="100" colspan="<?php echo count($payment_data); ?>">PAYMENT</td>
					<td class="xcenter" width="100" rowspan="2">COMPLIMENT</td>
					<td class="xcenter" width="100" rowspan="2">DP</td>
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
				if(!empty($summary_billing)){
					
					$no = 1;
					$total_qty = 0;
					$total_billing = 0;
					$total_sub_total = 0;
					$total_net_sales = 0;
					$total_tax = 0;
					$total_service = 0;
					$total_pembulatan = 0;
					$grand_total = 0;			
					$grand_total_payment = array();
					$discount_total = 0;
					$discount_billing_total = 0;
					$grand_total_dp = 0;
					$compliment_total = 0;
					$total_dp = 0;
					foreach($summary_billing as $det){
						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xleft"><?php echo $det['billing_type']; ?></td>
							<td class="xcenter"><?php echo $det['total_qty']; ?></td>
							<td class="xright"><?php echo $det['total_billing_show']; ?></td>
							<?php
							if($diskon_sebelum_pajak_service == 1){
								?>
								<td class="xright"><?php echo $det['discount_total_show']; ?></td>
								<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
								<td class="xright"><?php echo $det['net_sales_show']; ?></td>
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
							<td class="xright"><?php echo $det['grand_total_show']; ?></td>
							<?php
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
									
									$total_payment_show = priceFormat($total_payment);
									
									?>
									<td class="xright"><?php echo $total_payment_show; ?></td>
									<?php
																	
								}
							}
							?>
							<td class="xright"><?php echo $det['compliment_total_show']; ?></td>
							<td class="xright"><?php echo $det['total_dp_show']; ?></td>
						</tr>
						<?php	
					
						$total_qty +=  $det['total_qty'];
						$total_billing +=  $det['total_billing'];
						$total_sub_total +=  $det['sub_total'];
						$total_net_sales +=  $det['net_sales'];
						$total_tax +=  $det['tax_total'];
						$total_service +=  $det['service_total'];
						$total_pembulatan +=  $det['total_pembulatan'];
						$grand_total +=  $det['grand_total'];
						$discount_total +=  $det['discount_total'];
						$discount_billing_total +=  $det['discount_billing_total'];
						$grand_total_dp +=  $det['total_dp'];
						$compliment_total +=  $det['compliment_total'];
						$total_dp +=  $det['total_dp'];
						$no++;
					}
				
				?>
				<tr class="tbl-total">
					<td class="first xright xbold" colspan="2">TOTAL</td>
					<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_billing); ?></td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xright xbold"><?php echo priceFormat($discount_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($discount_billing_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_net_sales); ?></td>
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
					<td class="xright xbold"><?php echo priceFormat($total_dp); ?></td>
				</tr>
				<?php
				}
				?>
				<tr class="tbl-data">
					<td class="first" colspan="<?php echo $total_cols; ?>">&nbsp;</td>
				</tr>
				
				<tr class="tbl-header">
					<td class="first" colspan="<?php echo $total_cols; ?>">SALES BY CATEGORY</td>
				</tr>
				
				<tr class="tbl-header">
					<td class="first xcenter" width="50" rowspan="2">NO</td>
					<td class="xcenter" width="130" rowspan="2">CATEGORY GROUP</td>
					<td class="xcenter" width="80" rowspan="2">QTY MENU</td>
					<td class="xcenter" width="120" rowspan="2">SALES</td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xcenter" width="220" colspan="2">DISCOUNT</td>	
						<td class="xcenter" width="100" rowspan="2">NET SALES</td>
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
					<td class="xcenter" width="120" rowspan="2">GRAND TOTAL</td>
					<td class="xcenter" width="100" colspan="<?php echo count($payment_data); ?>">PAYMENT</td>
					<td class="xcenter" width="100" rowspan="2">COMPLIMENT</td>
					<td class="xcenter" width="100" rowspan="2">DP</td>
					
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
				if(!empty($summary_fnb)){
			
					$no = 1;
					$total_qty = 0;
					$total_billing = 0;
					$total_sub_total = 0;
					$total_net_sales = 0;
					$total_tax = 0;
					$total_service = 0;
					$total_pembulatan = 0;
					$grand_total = 0;			
					$grand_total_payment = array();
					$discount_total = 0;
					$discount_billing_total = 0;
					$grand_total_dp = 0;
					$compliment_total = 0;
					$total_dp = 0;
					foreach($summary_fnb as $det){
						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xleft"><?php echo $det['group_name']; ?></td>
							<td class="xcenter"><?php echo $det['total_qty']; ?></td>
							<td class="xright"><?php echo $det['total_billing_show']; ?></td>
							<?php
							if($diskon_sebelum_pajak_service == 1){
								?>
								<td class="xright"><?php echo $det['discount_total_show']; ?></td>
								<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
								<td class="xright"><?php echo $det['net_sales_show']; ?></td>
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
							<td class="xright"><?php echo $det['grand_total_show']; ?></td>
							<?php
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
									
									$total_payment_show = priceFormat($total_payment);
									
									?>
									<td class="xright"><?php echo $total_payment_show; ?></td>
									<?php
																	
								}
							}
							?>
							<td class="xright"><?php echo $det['compliment_total_show']; ?></td>
							<td class="xright"><?php echo $det['total_dp_show']; ?></td>
						</tr>
						<?php	
					
						$total_qty +=  $det['total_qty'];
						$total_billing +=  $det['total_billing'];
						$total_sub_total +=  $det['sub_total'];
						$total_net_sales +=  $det['net_sales'];
						$total_tax +=  $det['tax_total'];
						$total_service +=  $det['service_total'];
						$total_pembulatan +=  $det['total_pembulatan'];
						$grand_total +=  $det['grand_total'];
						$discount_total +=  $det['discount_total'];
						$discount_billing_total +=  $det['discount_billing_total'];
						$grand_total_dp +=  $det['total_dp'];
						$compliment_total +=  $det['compliment_total'];
						$total_dp +=  $det['total_dp'];
						$no++;
					}
				
				?>
				<tr class="tbl-total">
					<td class="first xright xbold" colspan="2">TOTAL</td>
					<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_billing); ?></td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xright xbold"><?php echo priceFormat($discount_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($discount_billing_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_net_sales); ?></td>
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
					<td class="xright xbold"><?php echo priceFormat($total_dp); ?></td>
				</tr>
				<?php
				}
				?>
				<tr class="tbl-data">
					<td class="first" colspan="<?php echo $total_cols; ?>">&nbsp;</td>
				</tr>
				
				<tr class="tbl-header">
					<td class="first" colspan="<?php echo $total_cols; ?>">SALES BY SUB CATEGORY</td>
				</tr>
				
				<tr class="tbl-header">
					<td class="first xcenter" width="50" rowspan="2">NO</td>
					<td class="xcenter" width="130" rowspan="2">CATEGORY</td>
					<td class="xcenter" width="80" rowspan="2">QTY MENU</td>
					<td class="xcenter" width="120" rowspan="2">SALES</td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xcenter" width="220" colspan="2">DISCOUNT</td>
						<td class="xcenter" width="100" rowspan="2">NET SALES</td>	
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
					<td class="xcenter" width="120" rowspan="2">GRAND TOTAL</td>
					<td class="xcenter" width="100" colspan="<?php echo count($payment_data); ?>">PAYMENT</td>
					<td class="xcenter" width="100" rowspan="2">COMPLIMENT</td>
					<td class="xcenter" width="100" rowspan="2">DP</td>
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
				if(!empty($summary_fnb_category)){
					
					$nox = 1;
					$total_qty = 0;
					$total_billing = 0;
					$total_sub_total = 0;
					$total_net_sales = 0;
					$total_tax = 0;
					$total_service = 0;
					$total_pembulatan = 0;
					$grand_total = 0;			
					$grand_total_payment = array();
					$discount_total = 0;
					$discount_billing_total = 0;
					$grand_total_dp = 0;
					$compliment_total = 0;
					$total_dp = 0;
					foreach($summary_fnb_category as $key => $dtDet){
						
						?>
						<tr class="tbl-data">
							<td class="first xcenter xbold"><?php echo $nox; ?></td>
							<td class="xleft xbold" colspan="<?php echo $total_cols-1; ?>"><?php echo $key; ?></td>
						</tr>
						<?php
						$no = 1;
						$cat_total_qty = 0;
						$cat_total_billing = 0;
						$cat_total_sub_total = 0;
						$cat_total_net_sales = 0;
						$cat_total_tax = 0;
						$cat_total_service = 0;
						$cat_total_pembulatan = 0;
						$cat_grand_total = 0;
						$cat_grand_total_payment = array();
						$cat_discount_total = 0;
						$cat_discount_billing_total = 0;
						$cat_total_dp = 0;
						$cat_compliment_total = 0;
						$cat_total_dp = 0;
						
						if(!empty($dtDet)){
							foreach($dtDet as $det){						
								?>
								<tr class="tbl-data">
									<td class="first xcenter">&nbsp;</td>
									<td class="xleft"><?php echo $det['category_name']; ?></td>
									<td class="xcenter"><?php echo $det['total_qty']; ?></td>
									<td class="xright"><?php echo $det['total_billing_show']; ?></td>
									<?php
									if($diskon_sebelum_pajak_service == 1){
										?>
										<td class="xright"><?php echo $det['discount_total_show']; ?></td>
										<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
										<td class="xright"><?php echo $det['net_sales_show']; ?></td>
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
									<td class="xright"><?php echo $det['grand_total_show']; ?></td>
									<?php
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
											
											$total_payment_show = priceFormat($total_payment);
											
											?>
											<td class="xright"><?php echo $total_payment_show; ?></td>
											<?php
																			
										}
									}
									?>
									<td class="xright"><?php echo $det['compliment_total_show']; ?></td>
									<td class="xright"><?php echo $det['total_dp_show']; ?></td>
								</tr>
								<?php	
							
								$cat_total_qty +=  $det['total_qty'];
								$cat_total_billing +=  $det['total_billing'];
								$cat_total_sub_total +=  $det['sub_total'];
								$cat_total_net_sales +=  $det['net_sales'];
								$cat_total_tax +=  $det['tax_total'];
								$cat_total_service +=  $det['service_total'];
								$cat_total_pembulatan +=  $det['total_pembulatan'];
								$cat_grand_total +=  $det['grand_total'];
								$cat_discount_total +=  $det['discount_total'];
								$cat_discount_billing_total +=  $det['discount_billing_total'];
								$cat_compliment_total +=  $det['compliment_total'];
								$cat_total_dp +=  $det['total_dp'];
								
								$total_qty +=  $det['total_qty'];
								$total_billing +=  $det['total_billing'];
								$total_sub_total +=  $det['sub_total'];
								$total_net_sales +=  $det['net_sales'];
								$total_tax +=  $det['tax_total'];
								$total_service +=  $det['service_total'];
								$total_pembulatan +=  $det['total_pembulatan'];
								$grand_total +=  $det['grand_total'];
								$discount_total +=  $det['discount_total'];
								$discount_billing_total +=  $det['discount_billing_total'];
								$grand_total_dp +=  $det['total_dp'];
								$compliment_total +=  $det['compliment_total'];
								$total_dp +=  $det['total_dp'];
								$no++;
							}
						}
						
						?>
						<tr class="tbl-total">
							<td class="first xright xbold" colspan="2">TOTAL <?php echo $key; ?> </td>
							<td class="xcenter xbold"><?php echo priceFormat($cat_total_qty); ?></td>
							<td class="xright xbold"><?php echo priceFormat($cat_total_billing); ?></td>
							<?php
							if($diskon_sebelum_pajak_service == 1){
								?>
								<td class="xright xbold"><?php echo priceFormat($cat_discount_total); ?></td>
								<td class="xright xbold"><?php echo priceFormat($cat_discount_billing_total); ?></td>
								<td class="xright xbold"><?php echo priceFormat($cat_total_net_sales); ?></td>
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
									
									$total = 0;
									if(!empty($cat_grand_total_payment[$key_id])){
										$total = priceFormat($cat_grand_total_payment[$key_id]);
									}							
									?>
									<td class="xright xbold"><?php echo $total; ?></td>
									<?php
								}
							}
							?>
							<td class="xright xbold"><?php echo priceFormat($cat_compliment_total); ?></td>
							<td class="xright xbold"><?php echo priceFormat($cat_total_dp); ?></td>
						</tr>
						<?php
						
						$nox++;
					}
				
				?>
				<tr class="tbl-total">
					<td class="first xright xbold" colspan="2">TOTAL</td>
					<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_billing); ?></td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xright xbold"><?php echo priceFormat($discount_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($discount_billing_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_net_sales); ?></td>
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
					<td class="xright xbold"><?php echo priceFormat($total_dp); ?></td>
				</tr>
				<?php
				}
				?>
				<tr class="tbl-data">
					<td class="first" colspan="<?php echo $total_cols; ?>">&nbsp;</td>
				</tr>
				
				<tr class="tbl-header">
					<td class="first" colspan="<?php echo $total_cols; ?>">SALES BY PACKAGE</td>
				</tr>
				
				<tr class="tbl-header">
					<td class="first xcenter" width="50" rowspan="2">NO</td>
					<td class="xcenter" width="130" rowspan="2">PACKAGE</td>
					<td class="xcenter" width="80" rowspan="2">QTY MENU</td>
					<td class="xcenter" width="120" rowspan="2">SALES</td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xcenter" width="220" colspan="2">DISCOUNT</td>
						<td class="xcenter" width="100" rowspan="2">NET SALES</td>	
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
					<td class="xcenter" width="120" rowspan="2">GRAND TOTAL</td>
					<td class="xcenter" width="100" colspan="<?php echo count($payment_data); ?>">PAYMENT</td>
					<td class="xcenter" width="100" rowspan="2">COMPLIMENT</td>
					<td class="xcenter" width="100" rowspan="2">DP</td>
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
				if(!empty($summary_sales_package)){
					
					$no = 1;
					$total_qty = 0;
					$total_billing = 0;
					$total_sub_total = 0;
					$total_net_sales = 0;
					$total_tax = 0;
					$total_service = 0;
					$total_pembulatan = 0;
					$grand_total = 0;			
					$grand_total_payment = array();
					$discount_total = 0;
					$discount_billing_total = 0;
					$grand_total_dp = 0;
					$compliment_total = 0;
					$total_dp = 0;
					foreach($summary_sales_package as $det){
						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xleft"><?php echo $det['product_name']; ?></td>
							<td class="xcenter"><?php echo $det['total_qty']; ?></td>
							<td class="xright"><?php echo $det['total_billing_show']; ?></td>
							<?php
							if($diskon_sebelum_pajak_service == 1){
								?>
								<td class="xright"><?php echo $det['discount_total_show']; ?></td>
								<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
								<td class="xright"><?php echo $det['net_sales_show']; ?></td>
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
							<td class="xright"><?php echo $det['grand_total_show']; ?></td>
							<?php
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
									
									$total_payment_show = priceFormat($total_payment);
									
									?>
									<td class="xright"><?php echo $total_payment_show; ?></td>
									<?php
																	
								}
							}
							?>
							<td class="xright"><?php echo $det['compliment_total_show']; ?></td>
							<td class="xright"><?php echo $det['total_dp_show']; ?></td>
						</tr>
						<?php	
					
						$total_qty +=  $det['total_qty'];
						$total_billing +=  $det['total_billing'];
						$total_sub_total +=  $det['sub_total'];
						$total_net_sales +=  $det['net_sales'];
						$total_tax +=  $det['tax_total'];
						$total_service +=  $det['service_total'];
						$total_pembulatan +=  $det['total_pembulatan'];
						$grand_total +=  $det['grand_total'];
						$discount_total +=  $det['discount_total'];
						$discount_billing_total +=  $det['discount_billing_total'];
						$grand_total_dp +=  $det['total_dp'];
						$compliment_total +=  $det['compliment_total'];
						$total_dp +=  $det['total_dp'];
						$no++;
					}
				
				?>
				<tr class="tbl-total">
					<td class="first xright xbold" colspan="2">TOTAL</td>
					<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_billing); ?></td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xright xbold"><?php echo priceFormat($discount_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($discount_billing_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_net_sales); ?></td>
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
					<td class="xright xbold"><?php echo priceFormat($total_dp); ?></td>
				</tr>
				<?php
				}else{
					?>
					<tr class="tbl-data">
						<td class="first" colspan="<?php echo $total_cols; ?>"> Tidak Ada Data Paket </td>
					</tr>
					<?php
				}
				?>
				
				<tr class="tbl-data">
					<td class="first" colspan="<?php echo $total_cols; ?>">&nbsp;</td>
				</tr>
				
				<tr class="tbl-header">
					<td class="first" colspan="<?php echo $total_cols; ?>">SALES BY PERIOD</td>
				</tr>
				
				<tr class="tbl-header">
					<td class="first xcenter" width="50" rowspan="2">NO</td>
					<td class="xcenter" width="130" rowspan="2">PERIOD</td>
					<td class="xcenter" width="80" rowspan="2">QTY BILLING</td>
					<td class="xcenter" width="120" rowspan="2">SALES</td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xcenter" width="220" colspan="2">DISCOUNT</td>
						<td class="xcenter" width="100" rowspan="2">NET SALES</td>	
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
					<td class="xcenter" width="120" rowspan="2">GRAND TOTAL</td>
					<td class="xcenter" width="100" colspan="<?php echo count($payment_data); ?>">PAYMENT</td>
					<td class="xcenter" width="100" rowspan="2">COMPLIMENT</td>
					<td class="xcenter" width="100" rowspan="2">DP</td>
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
				if(!empty($summary_sales_periode)){
					
					$no = 1;
					$total_qty = 0;
					$total_billing = 0;
					$total_sub_total = 0;
					$total_net_sales = 0;
					$total_tax = 0;
					$total_service = 0;
					$total_pembulatan = 0;
					$grand_total = 0;			
					$grand_total_payment = array();
					$discount_total = 0;
					$discount_billing_total = 0;
					$grand_total_dp = 0;
					$compliment_total = 0;
					$total_dp = 0;
					foreach($summary_sales_periode as $det){
						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xleft"><?php echo $det['periode_name']; ?></td>
							<td class="xcenter"><?php echo $det['total_qty']; ?></td>
							<td class="xright"><?php echo $det['total_billing_show']; ?></td>
							<?php
							if($diskon_sebelum_pajak_service == 1){
								?>
								<td class="xright"><?php echo $det['discount_total_show']; ?></td>
								<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
								<td class="xright"><?php echo $det['net_sales_show']; ?></td>
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
							<td class="xright"><?php echo $det['grand_total_show']; ?></td>
							<?php
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
									
									$total_payment_show = priceFormat($total_payment);
									
									?>
									<td class="xright"><?php echo $total_payment_show; ?></td>
									<?php
																	
								}
							}
							?>
							<td class="xright"><?php echo $det['compliment_total_show']; ?></td>
							<td class="xright"><?php echo $det['total_dp_show']; ?></td>
						</tr>
						<?php	
					
						$total_qty +=  $det['total_qty'];
						$total_billing +=  $det['total_billing'];
						$total_sub_total +=  $det['sub_total'];
						$total_net_sales +=  $det['net_sales'];
						$total_tax +=  $det['tax_total'];
						$total_service +=  $det['service_total'];
						$total_pembulatan +=  $det['total_pembulatan'];
						$grand_total +=  $det['grand_total'];
						$discount_total +=  $det['discount_total'];
						$discount_billing_total +=  $det['discount_billing_total'];
						$grand_total_dp +=  $det['total_dp'];
						$compliment_total +=  $det['compliment_total'];
						$total_dp +=  $det['total_dp'];
						$no++;
					}
				
				?>
				<tr class="tbl-total">
					<td class="first xright xbold" colspan="2">TOTAL</td>
					<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_billing); ?></td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xright xbold"><?php echo priceFormat($discount_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($discount_billing_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_net_sales); ?></td>
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
					<td class="xright xbold"><?php echo priceFormat($total_dp); ?></td>
				</tr>
				<?php
				}
				?>
				
				<tr class="tbl-data">
					<td class="first" colspan="<?php echo $total_cols; ?>">&nbsp;</td>
				</tr>
				
				<tr class="tbl-header">
					<td class="first" colspan="<?php echo $total_cols; ?>">SALES BY DISCOUNT/PROMO</td>
				</tr>
				<tr class="tbl-header">
					<td class="first xcenter" width="50" rowspan="2">NO</td>
					<td class="xcenter" width="180" rowspan="2">DISCOUNT</td>
					<td class="xcenter" width="80" rowspan="2">QTY MENU</td>
					<td class="xcenter" width="120" rowspan="2">SALES</td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xcenter" width="220" colspan="2">DISCOUNT</td>	
						<td class="xcenter" width="100" rowspan="2">NET SALES</td>
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
					<td class="xcenter" width="120" rowspan="2">GRAND TOTAL</td>
					<td class="xcenter" width="100" colspan="<?php echo count($payment_data); ?>">PAYMENT</td>
					<td class="xcenter" width="100" rowspan="2">COMPLIMENT</td>
					<td class="xcenter" width="100" rowspan="2">DP</td>
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
				if(!empty($summary_promo)){
					
					$no = 1;
					$total_qty = 0;
					$total_billing = 0;
					$total_sub_total = 0;
					$total_net_sales = 0;
					$total_tax = 0;
					$total_service = 0;
					$total_pembulatan = 0;
					$grand_total = 0;			
					$grand_total_payment = array();
					$discount_total = 0;
					$discount_billing_total = 0;
					$grand_total_dp = 0;
					$compliment_total = 0;
					$total_dp = 0;
					foreach($summary_promo as $det){
						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xleft"><?php echo $det['discount_name']; ?></td>
							<td class="xcenter"><?php echo $det['total_qty']; ?></td>
							<td class="xright"><?php echo $det['total_billing_show']; ?></td>
							<?php
							if($diskon_sebelum_pajak_service == 1){
								?>
								<td class="xright"><?php echo $det['discount_total_show']; ?></td>
								<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
								<td class="xright"><?php echo $det['net_sales_show']; ?></td>
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
							<td class="xright"><?php echo $det['grand_total_show']; ?></td>
							<?php
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
									
									$total_payment_show = priceFormat($total_payment);
									
									?>
									<td class="xright"><?php echo $total_payment_show; ?></td>
									<?php
																	
								}
							}
							?>
							<td class="xright"><?php echo $det['compliment_total_show']; ?></td>
							<td class="xright"><?php echo $det['total_dp_show']; ?></td>
						</tr>
						<?php	
					
						$total_qty +=  $det['total_qty'];
						$total_billing +=  $det['total_billing'];
						$total_sub_total +=  $det['sub_total'];
						$total_net_sales +=  $det['net_sales'];
						$total_tax +=  $det['tax_total'];
						$total_service +=  $det['service_total'];
						$total_pembulatan +=  $det['total_pembulatan'];
						$grand_total +=  $det['grand_total'];
						$discount_total +=  $det['discount_total'];
						$discount_billing_total +=  $det['discount_billing_total'];
						$grand_total_dp +=  $det['total_dp'];
						$compliment_total +=  $det['compliment_total'];
						$total_dp +=  $det['total_dp'];
						$no++;
					}
				
				?>
				<tr class="tbl-total">
					<td class="first xright xbold" colspan="2">TOTAL</td>
					<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_billing); ?></td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xright xbold"><?php echo priceFormat($discount_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($discount_billing_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_net_sales); ?></td>
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
					<td class="xright xbold"><?php echo priceFormat($total_dp); ?></td>
				</tr>
				<?php
				}
				?>
				

				<tr class="tbl-data">
					<td class="first" colspan="<?php echo $total_cols; ?>">&nbsp;</td>
				</tr>
				
				<tr class="tbl-header">
					<td class="first" colspan="<?php echo $total_cols; ?>">BUY &amp; GET/PROMO</td>
				</tr>
				<tr class="tbl-header">
					<td class="first xcenter" width="50" rowspan="2">NO</td>
					<td class="xcenter" width="180" rowspan="2">PROMO NAME</td>
					<td class="xcenter" width="80" rowspan="2">QTY MENU</td>
					<td class="xcenter" width="120" rowspan="2">SALES</td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xcenter" width="220" colspan="2">DISCOUNT</td>	
						<td class="xcenter" width="100" rowspan="2">NET SALES</td>
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
					<td class="xcenter" width="120" rowspan="2">GRAND TOTAL</td>
					<td class="xcenter" width="100" colspan="<?php echo count($payment_data); ?>">PAYMENT</td>
					<td class="xcenter" width="100" rowspan="2">COMPLIMENT</td>
					<td class="xcenter" width="100" rowspan="2">DP</td>
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
				if(!empty($summary_promo_buyget)){
					
					$no = 1;
					$total_qty = 0;
					$total_billing = 0;
					$total_sub_total = 0;
					$total_net_sales = 0;
					$total_tax = 0;
					$total_service = 0;
					$total_pembulatan = 0;
					$grand_total = 0;			
					$grand_total_payment = array();
					$discount_total = 0;
					$discount_billing_total = 0;
					$grand_total_dp = 0;
					$compliment_total = 0;
					$total_dp = 0;
					foreach($summary_promo_buyget as $det){
						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xleft"><?php echo $det['discount_name']; ?></td>
							<td class="xcenter"><?php echo $det['total_qty']; ?></td>
							<td class="xright"><?php echo $det['total_billing_show']; ?></td>
							<?php
							if($diskon_sebelum_pajak_service == 1){
								?>
								<td class="xright"><?php echo $det['discount_total_show']; ?></td>
								<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
								<td class="xright"><?php echo $det['net_sales_show']; ?></td>
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
							<td class="xright"><?php echo $det['grand_total_show']; ?></td>
							<?php
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
									
									$total_payment_show = priceFormat($total_payment);
									
									?>
									<td class="xright"><?php echo $total_payment_show; ?></td>
									<?php
																	
								}
							}
							?>
							<td class="xright"><?php echo $det['compliment_total_show']; ?></td>
							<td class="xright"><?php echo $det['total_dp_show']; ?></td>
						</tr>
						<?php	
					
						$total_qty +=  $det['total_qty'];
						$total_billing +=  $det['total_billing'];
						$total_sub_total +=  $det['sub_total'];
						$total_net_sales +=  $det['net_sales'];
						$total_tax +=  $det['tax_total'];
						$total_service +=  $det['service_total'];
						$total_pembulatan +=  $det['total_pembulatan'];
						$grand_total +=  $det['grand_total'];
						$discount_total +=  $det['discount_total'];
						$discount_billing_total +=  $det['discount_billing_total'];
						$grand_total_dp +=  $det['total_dp'];
						$compliment_total +=  $det['compliment_total'];
						$total_dp +=  $det['total_dp'];
						$no++;
					}
				
				?>
				<tr class="tbl-total">
					<td class="first xright xbold" colspan="2">TOTAL</td>
					<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_billing); ?></td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xright xbold"><?php echo priceFormat($discount_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($discount_billing_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_net_sales); ?></td>
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
					<td class="xright xbold"><?php echo priceFormat($total_dp); ?></td>
				</tr>
				<?php
				}
				?>
				
				<tr class="tbl-data">
					<td class="first" colspan="<?php echo $total_cols; ?>">&nbsp;</td>
				</tr>
				
				<tr class="tbl-header">
					<td class="first" colspan="<?php echo $total_cols; ?>">PAYMENT SUMMARY</td>
				</tr>
				
				<tr class="tbl-header">
					<td class="first xcenter" width="50" rowspan="2">NO</td>
					<td class="xcenter" width="180" rowspan="2">PAYMENT TYPE</td>
					<td class="xcenter" width="80" rowspan="2">QTY BILLING</td>
					<td class="xcenter" width="120" rowspan="2">SALES</td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xcenter" width="220" colspan="2">DISCOUNT</td>
						<td class="xcenter" width="100" rowspan="2">NET SALES</td>	
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
					<td class="xcenter" width="120" rowspan="2">GRAND TOTAL</td>
					<td class="xcenter" width="100" colspan="<?php echo count($payment_data); ?>">PAYMENT</td>
					<td class="xcenter" width="100" rowspan="2">COMPLIMENT</td>
					<td class="xcenter" width="100" rowspan="2">DP</td>
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
				if(!empty($summary_payment)){
					
					$nox = 1;
					$total_qty = 0;
					$total_billing = 0;
					$total_sub_total = 0;
					$total_net_sales = 0;
					$total_tax = 0;
					$total_service = 0;
					$total_pembulatan = 0;
					$grand_total = 0;			
					$grand_total_payment = array();
					$discount_total = 0;
					$discount_billing_total = 0;
					$grand_total_dp = 0;
					$compliment_total = 0;
					$total_dp = 0;
					foreach($summary_payment as $key => $dtDet){
						
						?>
						<tr class="tbl-data">
							<td class="first xcenter xbold"><?php echo $nox; ?></td>
							<td class="xleft xbold" colspan="<?php echo $total_cols-1; ?>"><?php echo $key; ?></td>
						</tr>
						<?php
						$no = 1;
						$cat_total_qty = 0;
						$cat_total_billing = 0;
						$cat_total_sub_total = 0;
						$cat_total_net_sales = 0;
						$cat_total_tax = 0;
						$cat_total_service = 0;
						$cat_total_pembulatan = 0;
						$cat_grand_total = 0;
						$cat_grand_total_payment = array();
						$cat_discount_total = 0;
						$cat_discount_billing_total = 0;
						$cat_total_dp = 0;
						$cat_compliment_total = 0;
						$cat_total_dp = 0;
						
						if(!empty($dtDet)){
							foreach($dtDet as $det){						
								?>
								<tr class="tbl-data">
									<td class="first xcenter">&nbsp;</td>
									<td class="xleft"><?php echo $det['bank_name']; ?></td>
									<td class="xcenter"><?php echo $det['total_qty']; ?></td>
									<td class="xright"><?php echo $det['total_billing_show']; ?></td>
									<?php
									if($diskon_sebelum_pajak_service == 1){
										?>
										<td class="xright"><?php echo $det['discount_total_show']; ?></td>
										<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
										<td class="xright"><?php echo $det['net_sales_show']; ?></td>
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
									<td class="xright"><?php echo $det['grand_total_show']; ?></td>
									<?php
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
											
											$total_payment_show = priceFormat($total_payment);
											
											?>
											<td class="xright"><?php echo $total_payment_show; ?></td>
											<?php
																			
										}
									}
									?>
									<td class="xright"><?php echo $det['compliment_total_show']; ?></td>
									<td class="xright"><?php echo $det['total_dp_show']; ?></td>
								</tr>
								<?php	
							
								$cat_total_qty +=  $det['total_qty'];
								$cat_total_billing +=  $det['total_billing'];
								$cat_total_sub_total +=  $det['sub_total'];
								$cat_total_net_sales +=  $det['net_sales'];
								$cat_total_tax +=  $det['tax_total'];
								$cat_total_service +=  $det['service_total'];
								$cat_total_pembulatan +=  $det['total_pembulatan'];
								$cat_grand_total +=  $det['grand_total'];
								$cat_discount_total +=  $det['discount_total'];
								$cat_discount_billing_total +=  $det['discount_billing_total'];
								$cat_compliment_total +=  $det['compliment_total'];
								$cat_total_dp +=  $det['total_dp'];
								
								$total_qty +=  $det['total_qty'];
								$total_billing +=  $det['total_billing'];
								$total_sub_total +=  $det['sub_total'];
								$total_net_sales +=  $det['net_sales'];
								$total_tax +=  $det['tax_total'];
								$total_service +=  $det['service_total'];
								$total_pembulatan +=  $det['total_pembulatan'];
								$grand_total +=  $det['grand_total'];
								$discount_total +=  $det['discount_total'];
								$discount_billing_total +=  $det['discount_billing_total'];
								$grand_total_dp +=  $det['total_dp'];
								$compliment_total +=  $det['compliment_total'];
								$total_dp +=  $det['total_dp'];
								$no++;
							}
						}
						
						?>
						<tr class="tbl-total">
							<td class="first xright xbold" colspan="2">TOTAL <?php echo $key; ?> </td>
							<td class="xcenter xbold"><?php echo priceFormat($cat_total_qty); ?></td>
							<td class="xright xbold"><?php echo priceFormat($cat_total_billing); ?></td>
							<?php
							if($diskon_sebelum_pajak_service == 1){
								?>
								<td class="xright xbold"><?php echo priceFormat($cat_discount_total); ?></td>
								<td class="xright xbold"><?php echo priceFormat($cat_discount_billing_total); ?></td>
								<td class="xright xbold"><?php echo priceFormat($cat_total_net_sales); ?></td>
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
									
									$total = 0;
									if(!empty($cat_grand_total_payment[$key_id])){
										$total = priceFormat($cat_grand_total_payment[$key_id]);
									}							
									?>
									<td class="xright xbold"><?php echo $total; ?></td>
									<?php
								}
							}
							?>
							<td class="xright xbold"><?php echo priceFormat($cat_compliment_total); ?></td>
							<td class="xright xbold"><?php echo priceFormat($cat_total_dp); ?></td>
						</tr>
						<?php
						
						$nox++;
					}
				
				?>
				<tr class="tbl-total">
					<td class="first xright xbold" colspan="2">TOTAL</td>
					<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_billing); ?></td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xright xbold"><?php echo priceFormat($discount_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($discount_billing_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_net_sales); ?></td>
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
					<td class="xright xbold"><?php echo priceFormat($total_dp); ?></td>
				</tr>
				<?php
				}
				?>
				<tr class="tbl-data">
					<td class="first" colspan="<?php echo $total_cols; ?>">&nbsp;</td>
				</tr>
				<tr class="tbl-header">
					<td class="first" colspan="<?php echo $total_cols; ?>">CANCEL BILLING</td>
				</tr>
				
				<tr class="tbl-header">
					<td class="first xcenter" width="50" rowspan="2">NO</td>
					<td class="xcenter" width="130" rowspan="2">TIPE CANCEL</td>
					<td class="xcenter" width="80" rowspan="2">QTY BILLING</td>
					<td class="xcenter" width="120" rowspan="2">SALES</td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xcenter" width="220" colspan="2">DISCOUNT</td>
						<td class="xcenter" width="100" rowspan="2">NET SALES</td>	
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
					<td class="xcenter" width="120" rowspan="2">GRAND TOTAL</td>
					<td class="xcenter" width="100" colspan="<?php echo count($payment_data); ?>">PAYMENT</td>
					<td class="xcenter" width="100" rowspan="2">COMPLIMENT</td>
					<td class="xcenter" width="100" rowspan="2">DP</td>
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
				if(!empty($summary_cancel)){
					
					$no = 1;
					$total_qty = 0;
					$total_billing = 0;
					$total_sub_total = 0;
					$total_net_sales = 0;
					$total_tax = 0;
					$total_service = 0;
					$total_pembulatan = 0;
					$grand_total = 0;			
					$grand_total_payment = array();
					$discount_total = 0;
					$discount_billing_total = 0;
					$grand_total_dp = 0;
					$compliment_total = 0;
					$total_dp = 0;
					foreach($summary_cancel as $det){
						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xleft"><?php echo $det['tipe_name']; ?></td>
							<td class="xcenter"><?php echo $det['total_qty']; ?></td>
							<td class="xright"><?php echo $det['total_billing_show']; ?></td>
							<?php
							if($diskon_sebelum_pajak_service == 1){
								?>
								<td class="xright"><?php echo $det['discount_total_show']; ?></td>
								<td class="xright"><?php echo $det['discount_billing_total_show']; ?></td>
								<td class="xright"><?php echo $det['net_sales_show']; ?></td>
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
							<td class="xright"><?php echo $det['grand_total_show']; ?></td>
							<?php
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
									
									$total_payment_show = priceFormat($total_payment);
									
									?>
									<td class="xright"><?php echo $total_payment_show; ?></td>
									<?php
																	
								}
							}
							?>
							<td class="xright"><?php echo $det['compliment_total_show']; ?></td>
							<td class="xright"><?php echo $det['total_dp_show']; ?></td>
						</tr>
						<?php	
					
						$total_qty +=  $det['total_qty'];
						$total_billing +=  $det['total_billing'];
						$total_sub_total +=  $det['sub_total'];
						$total_net_sales +=  $det['net_sales'];
						$total_tax +=  $det['tax_total'];
						$total_service +=  $det['service_total'];
						$total_pembulatan +=  $det['total_pembulatan'];
						$grand_total +=  $det['grand_total'];
						$discount_total +=  $det['discount_total'];
						$discount_billing_total +=  $det['discount_billing_total'];
						$grand_total_dp +=  $det['total_dp'];
						$compliment_total +=  $det['compliment_total'];
						$total_dp +=  $det['total_dp'];
						$no++;
					}
				
				?>
				<tr class="tbl-total">
					<td class="first xright xbold" colspan="2">TOTAL</td>
					<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_billing); ?></td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="xright xbold"><?php echo priceFormat($discount_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($discount_billing_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_net_sales); ?></td>
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
					<td class="xright xbold"><?php echo priceFormat($total_dp); ?></td>
				</tr>
				<?php
				}
				?>
				
				<tr class="tbl-data">
					<td class="first" colspan="<?php echo $total_cols; ?>">&nbsp;</td>
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