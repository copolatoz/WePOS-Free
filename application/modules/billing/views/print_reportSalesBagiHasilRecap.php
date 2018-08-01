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
		$set_width = 750;
		$total_cols = 7;
		
		
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
							<div class="title_report xcenter">SUPPLIER: <?php echo $supplier_name; ?></div>
							<div class="subtitle_report xcenter"><?php echo 'Period : '.$date_from.' TO '.$date_till;?></div>			
							
						</div>
					</td>
				</tr>
				<tr class="tbl-header">
					<td class="first xcenter" width="50">NO</td>
					<td class="xcenter" width="80">DATE</td>
					<td class="xcenter" width="80">QTY</td>
					<td class="xcenter" width="110">TOTAL BILLING</td>
					<td class="xcenter" width="110">TOKO</td>
					<td class="xcenter" width="110">SUPPLIER</td>
				</tr>
			</thead>
			<tbody>
				<?php
				if(!empty($report_data)){
				
					$no = 1;
					$total_qty = 0;
					$total_all = 0;
					$total_toko = 0;
					$total_supplier = 0;
					
					foreach($report_data as $det){
						
						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xcenter"><?php echo $det['date']; ?></td>
							<td class="xcenter"><?php echo $det['qty_billing']; ?></td>
							<td class="xright"><?php echo priceFormat($det['total_billing']); ?></td>
							<td class="xright"><?php echo priceFormat($det['total_price_toko']); ?></td>
							<td class="xright"><?php echo priceFormat($det['total_price_supplier']); ?></td>
						</tr>
						<?php	
						
						$total_qty +=  $det['qty_billing'];
						$total_all +=  $det['total_billing'];
						$total_toko +=  $det['total_price_toko'];
						$total_supplier +=  $det['total_price_supplier'];
						$no++;
					}
					
					?>
					<tr class="tbl-total">
						<td class="first xright xbold" colspan="<?php echo 2; ?>">TOTAL</td>
						<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_all); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_toko); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_supplier); ?></td>
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
				
				<tr class="tbl-sign">
					<td colspan="<?php echo $total_cols; ?>" class="first xleft">
						<br/>
						<br/>
						<div class="fleft" style="width:200px;">
							<br/><br/><br/><br/>
							Printed: <?php echo date("d-m-Y H:i:s");?>
						</div>
						<div class="fright" style="width:250px;">
							Printed by:<br/><br/><br/><br/>
							<?php echo $session_user; ?>
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