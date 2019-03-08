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
		$set_width = 700;
		$total_cols = 5;
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
					<td class="first xcenter" width="50">NO</td>
					<td class="xcenter" width="150">DATE</td>
					<td class="xcenter" width="160">TOTAL PRODUCTION</td>
					<td class="xcenter" width="170">TOTAL ITEM</td>
					<td class="xcenter" width="170">TOTAL QTY</td>		
				</tr>
			</thead>
			<tbody>
				<?php
				if(!empty($report_data)){
				
					$no = 1;
					$total_usage = 0;
					$total_item = 0;
					$total_qty = 0;
					foreach($report_data as $det){

						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xcenter"><?php echo $det['date']; ?></td>
							<td class="xcenter"><?php echo $det['total_usage']; ?></td>
							<td class="xcenter"><?php echo priceFormat($det['total_item']); ?></td>
							<td class="xcenter"><?php echo priceFormat($det['total_qty']); ?></td>				
						</tr>
						<?php	

						$total_usage += $det['total_usage'];
						$total_item += $det['total_item'];
						$total_qty += $det['total_qty'];
						
						$no++;
					}
					
					?>
					<tr class="tbl-total">
						<td class="first xright xbold" colspan="2">TOTAL</td>
						<td class="xcenter xbold"><?php echo $total_usage; ?></td>
						<td class="xcenter xbold"><?php echo $total_item; ?></td>
						<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>
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
						<div class="fright" style="width:200px;">
							Prepared by:<br/><br/><br/><br/>
							----------------------------
						</div>
						<div class="fright" style="width:200px;">
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