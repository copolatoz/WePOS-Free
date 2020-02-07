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
		$set_width = 550;
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<div class="fleft" style="width:200px; margin-right:10px;">
			<h1>BILLING: <?php echo $billing_data['billing_no']; ?></h1>
		
			<table>
				<tr>
					<td class="f14 xbold" width="50">TABLE</td>
					<td class="f14 xbold">:</td>
					<td class="f14 xbold"><?php echo $billing_data['table_no']; ?></td>
				</tr>
			</table>
		</div>
		<div class="fright" style="width:170px; align:right;">
			<table style="width:170px; align:right">
				<tr>
					<td class="f14 xbold">&nbsp;</td>
				</tr>
				<tr>
					<td class="f14 xbold" width="50">DATE</td>
					<td class="f14 xbold">:</td>
					<td class="f14 xbold"><?php echo date("d-m-Y H:i", strtotime($billing_data['created'])); ?></td>
				</tr>
			</table>
		</div>
		<div class="fclear"></div>
		
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<tr class="tbl-header">
				<td class="first xleft" width="200">ITEM ORDER</td>
				<td class="xcenter" width="50">QTY</td>
				<td class="xcenter" width="50">T/A</td>
				<td class="xcenter" width="50">COMP</td>
				<td class="xcenter" width="100">TOTAL</td>
				<td class="xcenter" width="100">DISC</td>
			</tr>
			
			<?php
			if(!empty($billing_detail)){
			
				$no = 1;
				$total_qty = 0;
				foreach($billing_detail as $det){
					?>
					<tr class="tbl-data">
						<td class="first xleft"><?php echo $det['product_detail_info']; ?></td>
						<td class="xcenter"><?php echo $det['order_qty']; ?></td>
						<td class="xcenter"><?php echo $det['is_takeaway_text']; ?></td>
						<td class="xcenter"><?php echo $det['is_compliment_text']; ?></td>
						<td class="xright"><?php echo $det['order_total_show']; ?></td>
						<td class="xright"><?php echo $det['discount_total_show']; ?></td>
					</tr>
					<?php
					$total_qty += $det['order_qty'];
				}
				
				$sub_total_incl = '';
				
				$tax_incl = '';
				if($billing_data['include_tax'] == 1){
					$tax_incl = ' (Incl)';
					$sub_total_incl = '(Before Tax)';
				}
				
				$service_incl = '';
				if($billing_data['include_service'] == 1){
					$service_incl = ' (Incl)';
					$sub_total_incl = '(Before Service)';
				}
				
				if($billing_data['include_tax'] == 1 AND $billing_data['include_service'] == 1){
					$sub_total_incl = '(Before Tax &amp; Service)';
				}
				
				?>
				<tr class="tbl-data">
					<td class="first xright"> &nbsp; </td>
					<td class="xcenter"><?php echo priceFormat($total_qty); ?></td>
					<td class="xcenter">&nbsp;</td>
					<td class="xcenter">&nbsp;</td>
					<td class="xright"><?php echo 'Rp. '.priceFormat($curr_billing_total); ?></td>
					<td class="xright"><?php echo 'Rp. '.priceFormat($curr_discount_total); ?></td>
				</tr>
				
				<tr class="tbl-data">
					<td class="first xright" colspan="4"> SUB TOTAL <?php echo $sub_total_incl; ?> </td>
					<td class="xright"><?php echo 'Rp. '.priceFormat($curr_billing_total); ?></td>
					<td class="xcenter">&nbsp;</td>
				</tr>
				<?php
				if(!empty($curr_discount_total)){
					?>
					<tr class="tbl-data">
						<td class="first xright" colspan="4"> DISCOUNT </td>
						<td class="xright"><?php echo 'Rp. '.priceFormat($curr_discount_total); ?></td>
						<td class="xcenter">&nbsp;</td>
					</tr>
					<?php
				}
				
				if(!empty($curr_compliment_total)){
					?>
					<tr class="tbl-data">
						<td class="first xright" colspan="4"> COMPLIMENT </td>
						<td class="xright"><?php echo 'Rp. '.priceFormat($curr_compliment_total); ?></td>
					<td class="xcenter">&nbsp;</td>
					</tr>
					<?php
				}
				?>
				<tr class="tbl-data">
					<td class="first xright" colspan="4"> TAX <?php echo $tax_incl; ?></td>
					<td class="xright"><?php echo 'Rp. '.priceFormat($curr_tax_total); ?></td>
					<td class="xcenter">&nbsp;</td>
				</tr>
				<tr class="tbl-data">
					<td class="first xright" colspan="4"> SERVICE <?php echo $service_incl; ?></td>
					<td class="xright"><?php echo 'Rp. '.priceFormat($curr_service_total); ?></td>
					<td class="xcenter">&nbsp;</td>
				</tr>
				<?php
				if(!empty($curr_dp_total)){
					?>
					<tr class="tbl-data">
						<td class="first xright" colspan="4"> DP </td>
						<td class="xright"><?php echo 'Rp. '.priceFormat($curr_dp_total); ?></td>
					<td class="xcenter">&nbsp;</td>
					</tr>
					<?php
				}
				?>
				
				<tr class="tbl-data">
					<td class="first xright" colspan="4"> PEMBULATAN </td>
					<td class="xright"><?php echo 'Rp. '.priceFormat($curr_pembulatan); ?></td>
					<td class="xcenter">&nbsp;</td>
				</tr>
				
				<tr class="tbl-total">
					<td class="first xright" colspan="4"> GRAND TOTAL </td>
					<td class="xright"><?php echo 'Rp. '.priceFormat($curr_grand_total); ?></td>
					<td class="xcenter">&nbsp;</td>
				</tr>
				<?php	
			}
			?>
			
		</table>
		<br/>
		<div class="fclear"></div>
		<br/>
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