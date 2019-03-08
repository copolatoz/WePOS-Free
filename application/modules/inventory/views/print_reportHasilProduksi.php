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
		$set_width = 900;
		$total_cols = 9;
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<thead>
				<tr>
					<div class="tbl-title">
						<div class="logo">
							
							<!-- <img height="80" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>"> -->
							
						</div>
									
						<div class="title_report xcenter"><?php echo $report_name;?></div>
						<div class="subtitle_report xcenter"><?php echo 'Period : '.$date_from.' TO '.$date_till;?></div>			
						
					</div>
				</tr>
				<tr class="tbl-header">
					<td class="first xcenter" width="50">NO</td>
					<td class="xcenter" width="90">DATE</td>
					<td class="xcenter" width="80">NO.</td>
					<td class="xleft" width="100">TO</td>
					<td class="xcenter" width="80">TOTAL BARANG</td>
					<td class="xcenter" width="80">TOTAL QTY</td>		
					<td class="xcenter" width="120">TOTAL HPP</td>		
					<td class="xleft" width="100">USER</td>
					<td class="xleft" width="200">NOTES</td>
				</tr>
			</thead>
			<tbody>
				<?php
				if(!empty($report_data)){
				
					$no = 1;
					$total_item = 0;
					$total_qty = 0;
					$total_price = 0;
					$total_hpp = 0;
					foreach($report_data as $det){

						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xcenter"><?php echo $det['pr_date']; ?></td>
							<td class="xcenter"><?php echo $det['pr_number']; ?></td>
							<td class="xleft"><?php echo $det['pr_to_name']; ?></td>
							<td class="xcenter"><?php echo priceFormat($det['total_item']); ?></td>
							<td class="xcenter"><?php echo priceFormat($det['total_qty']); ?></td>
							<td class="xright"><?php echo priceFormat(($det['total_hpp'])); ?></td>
							<td class="xleft"><?php echo $det['createdby']; ?></td>
							<td class="xleft"><?php echo $det['pr_memo']; ?></td>
							
						</tr>
						<?php	
											
						$total_item += $det['total_item'];
						$total_qty += $det['total_qty'];
						$total_hpp +=  $det['total_hpp'];
						
						$no++;
					}
					
					?>
					<tr class="tbl-total">
						<td class="first xright xbold" colspan="4">TOTAL</td>
						<td class="xcenter xbold"><?php echo $total_item; ?></td>
						<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_hpp); ?></td>
						<td class="xright xbold">&nbsp;</td>
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