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
		$set_width = 850;
		$total_cols = 7;
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<div>
			<div class="logo">
				
				<!-- <img height="80" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>"> -->
				
			</div>
						
			<div class="title_report xcenter"><?php echo $report_name;?></div>
			<div class="subtitle_report xcenter"><?php echo 'Period : '.$date_from.' TO '.$date_till;?></div>			
			
		</div>
		<br/>
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<tr class="tbl-header">
				<td class="first xcenter" width="50">NO</td>
				<td class="xleft" width="100">CODE</td>
				<td class="xleft" width="350">ITEM NAME</td>
				<td class="xcenter" width="80">SATUAN</td>
				<td class="xcenter" width="70">TOTAL QTY</td>
				<td class="xcenter" width="70">TOTAL HPP</td>
				<td class="xcenter" width="80">AVERAGE QTY</td>
			</tr>
			
			<?php
			if(!empty($report_data)){
			
				$no = 1;
				$total_order = 0;
				$total_qty = 0;
				
				foreach($report_data as $det){
					
					$det['item_qty_average'] = $det['total_qty']/$det['total_order'];
					?>
					<tr class="tbl-data">
						<td class="first xcenter"><?php echo $no; ?></td>
						<td class="xleft"><?php echo $det['item_code']; ?></td>
						<td class="xleft"><?php echo $det['item_name']; ?></td>
						<td class="xcenter"><?php echo $det['unit_code']; ?></td>
						<td class="xcenter"><?php echo priceFormat($det['total_qty']); ?></td>
						<td class="xcenter"><?php echo priceFormat($det['total_order']); ?></td>
						<td class="xcenter"><?php echo priceFormat($det['item_qty_average']); ?></td>
					</tr>
					<?php	
					
					$total_order +=  $det['total_order'];
					$total_qty +=  $det['total_qty'];
					$no++;
				}
				
				?>
				<tr class="tbl-total">
					<td class="first xright xbold" colspan="<?php echo 4; ?>">TOTAL</td>
					<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>
					<td class="xcenter xbold"><?php echo priceFormat($total_order); ?></td>
					<td class="xleft xbold">&nbsp;</td>
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