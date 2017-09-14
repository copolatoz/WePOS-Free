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
		$set_width = 1270;
		$total_cols = 12;
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<thead>
				<tr>
					<div>
						<div class="logo">
							
							<!-- <img height="80" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>"> -->
							
						</div>
									
						<div class="title_report xcenter"><?php echo $report_name;?></div>
						<div class="subtitle_report xcenter"><?php echo 'Period : '.$date_from.' TO '.$date_till;?></div>			
						
					</div>
				</tr>	
				<tr class="tbl-header">
					<td class="first xcenter" rowspan="2" width="50">NO</td>
					<td class="xcenter" rowspan="2" width="90">PO DATE</td>
					<td class="xcenter" rowspan="2" width="80">PO NO.</td>
					<td class="xleft" rowspan="2" width="160">SUPPLIER</td>
					<td class="xleft" rowspan="2" width="150">INVOICE</td>
					<td class="xcenter" rowspan="2" width="80">TOTAL BARANG</td>
					<td class="xcenter" rowspan="2" width="80">TOTAL QTY</td>
					<td class="xcenter" rowspan="2" width="90">DISCOUNT</td>
					<td class="xcenter" rowspan="2" width="90">TAX</td>			
					<td class="xcenter" colspan="2">TOTAL PAYMENT</td>
					<td class="xleft" rowspan="2" width="200">NOTES</td>
				</tr>
				<tr class="tbl-header">						
					<td class="xcenter" width="100">CASH</td>		
					<td class="xcenter" width="100">CREDIT</td>
				</tr>
			
			</thead>
			<tbody>
				<?php
				if(!empty($report_data)){
				
					$no = 1;
					$total_item = 0;
					$total_qty = 0;
					$total_discount = 0;
					$total_tax = 0;
					$grand_total_cash = 0;
					$grand_total_credit = 0;
					foreach($report_data as $det){

						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xcenter"><?php echo $det['po_date']; ?></td>
							<td class="xcenter"><?php echo $det['po_number']; ?></td>
							<td class="xleft"><?php echo $det['supplier_name']; ?></td>
							<td class="xleft"><?php echo $det['supplier_invoice']; ?></td>
							<td class="xcenter"><?php echo priceFormat($det['total_item']); ?></td>
							<td class="xcenter"><?php echo priceFormat($det['total_qty']); ?></td>
							<td class="xright"><?php echo $det['po_discount_text']; ?></td>
							<td class="xright"><?php echo $det['po_tax_text']; ?></td>
							<td class="xright"><?php echo $det['po_total_price_cash_text']; ?></td>
							<td class="xright"><?php echo $det['po_total_price_credit_text']; ?></td>
							<td class="xleft"><?php echo $det['po_memo']; ?></td>
							
						</tr>
						<?php	
											
						$total_item += $det['total_item'];
						$total_qty += $det['total_qty'];
						$total_discount +=  $det['po_discount'];
						$total_tax +=  $det['po_tax'];
						$grand_total_cash +=  $det['po_total_price_cash'];
						$grand_total_credit +=  $det['po_total_price_credit'];
						
						$no++;
					}
					
					?>
					<tr class="tbl-total">
						<td class="first xright xbold" colspan="5">TOTAL</td>
						<td class="xcenter xbold"><?php echo $total_item; ?></td>
						<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_discount); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_tax); ?></td>
						<td class="xright xbold"><?php echo priceFormat($grand_total_cash); ?></td>	
						<td class="xright xbold"><?php echo priceFormat($grand_total_credit); ?></td>					
						<td class="xright xbold">&nbsp;</td>
					</tr>
					<?php
				}else{
				?>
					<tr class="tbl-data">
						<td colspan="<?php echo $total_cols; ?>" class="first xcenter">Data Not Found</td>
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