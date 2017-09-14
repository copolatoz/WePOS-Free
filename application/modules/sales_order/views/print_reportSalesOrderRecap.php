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
		$set_width = 1260;
		$total_cols = 13;
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
						<?php
						if(!empty($storehouse_name)){
							if($storehouse_name == 'Semua Gudang'){
								?>
								<div class="subtitle_report xcenter"><?php echo $storehouse_name; ?></div>	
								<?php
							}else{
								?>
								<div class="subtitle_report xcenter">Gudang: <?php echo $storehouse_name; ?></div>	
								<?php
							}
							
						}	
						?>	
						<br/>						
					</div>
				</tr>	
				<tr class="tbl-header">
					<td class="first xcenter" rowspan="2" width="40">NO</td>
					<td class="xcenter" rowspan="2" width="100">DATE</td>
					<td class="xcenter" rowspan="2" width="80">TOTAL SO</td>
					<td class="xcenter" rowspan="2" width="80">TOTAL BARANG</td>
					<td class="xcenter" rowspan="2" width="80">TOTAL QTY</td>
					<td class="xcenter" rowspan="2" width="110">SUB TOTAL</td>
					<td class="xcenter" rowspan="2" width="110">TOTAL DISCOUNT</td>
					<td class="xcenter" rowspan="2" width="110">TOTAL TAX</td>			
					<td class="xcenter" rowspan="2" width="110">TOTAL SHIPPING</td>			
					<td class="xcenter" rowspan="2" width="110">TOTAL DP</td>			
					<td class="xcenter" rowspan="2" width="110">TOTAL</td>			
					<td class="xcenter" colspan="2">TOTAL PAYMENT</td>
				</tr>
				<tr class="tbl-header">						
					<td class="xcenter" width="110">CASH</td>		
					<td class="xcenter" width="110">CREDIT</td>
				</tr>
			
			</thead>
			<tbody>
				<?php
				if(!empty($report_data)){
				
					$no = 1;
					$total_po = 0;
					$total_item = 0;
					$total_qty = 0;
					$total_sub_total = 0;
					$total_discount = 0;
					$total_tax = 0;
					$total_shipping = 0;
					$total_dp = 0;
					$grand_total = 0;
					$grand_total_cash = 0;
					$grand_total_credit = 0;
					foreach($report_data as $det){

						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xcenter"><?php echo $det['date']; ?></td>
							<td class="xcenter"><?php echo $det['total_po']; ?></td>
							<td class="xcenter"><?php echo priceFormat($det['total_item']); ?></td>
							<td class="xcenter"><?php echo priceFormat($det['total_qty']); ?></td>
							<td class="xright"><?php echo priceFormat($det['total_sub_total']); ?></td>
							<td class="xright"><?php echo priceFormatAcc($det['total_discount']); ?></td>
							<td class="xright"><?php echo priceFormat($det['total_tax']); ?></td>
							<td class="xright"><?php echo priceFormat($det['total_shipping']); ?></td>
							<td class="xright"><?php echo priceFormatAcc($det['total_dp']); ?></td>
							<td class="xright"><?php echo priceFormat($det['grand_total']); ?></td>
							<td class="xright"><?php echo priceFormat($det['total_cash']); ?></td>
							<td class="xright"><?php echo priceFormat($det['total_credit']); ?></td>						
						</tr>
						<?php	

						$total_po += $det['total_po'];
						$total_item += $det['total_item'];
						$total_qty += $det['total_qty'];
						$total_sub_total += $det['total_sub_total'];
						$total_discount += $det['total_discount'];
						$total_tax +=  $det['total_tax'];
						$total_shipping +=  $det['total_shipping'];
						$total_dp +=  $det['total_dp'];
						$grand_total +=  $det['grand_total'];
						$grand_total_cash +=  $det['total_cash'];
						$grand_total_credit +=  $det['total_credit'];
						
						$no++;
					}
					
					?>
					<tr class="tbl-total">
						<td class="first xright xbold" colspan="2">TOTAL</td>
						<td class="xcenter xbold"><?php echo $total_po; ?></td>
						<td class="xcenter xbold"><?php echo $total_item; ?></td>
						<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_sub_total); ?></td>
						<td class="xright xbold"><?php echo priceFormatAcc($total_discount); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_tax); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_shipping); ?></td>
						<td class="xright xbold"><?php echo priceFormatAcc($total_dp); ?></td>
						<td class="xright xbold"><?php echo priceFormat($grand_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($grand_total_cash); ?></td>	
						<td class="xright xbold"><?php echo priceFormat($grand_total_credit); ?></td>
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