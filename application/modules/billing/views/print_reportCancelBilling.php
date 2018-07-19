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
		$set_width = 1130;
		$total_cols = 10;
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
				<td class="xcenter" width="130">DATE</td>
				<td class="xcenter" width="130">TIPE</td>
				<td class="xcenter" width="80">BILLING NO.</td>
				<td class="xcenter" width="110">TOTAL BILLING</td>
				<td class="xcenter" width="90">TAX</td>
				<td class="xcenter" width="90">SERVICE</td>
				<td class="xcenter" width="110">GRAND TOTAL</td>
				<td class="xcenter" width="110">CANCELED BY</td>
				<td class="xleft" width="230">NOTES</td>
			</tr>
			
			<?php
			if(!empty($report_data)){
			
				$no = 1;
				$total_billing = 0;
				$total_tax = 0;
				$total_service = 0;
				$grand_total = 0;
				$grand_total_payment = array();
				foreach($report_data as $det){
					$tipe_cancel = 'AFTER PAYMENT';
					if(empty($det['payment_date'])){
						$tipe_cancel = 'BEFORE PAYMENT';
					}
					
					?>
					<tr class="tbl-data">
						<td class="first xcenter"><?php echo $no; ?></td>
						<td class="xcenter"><?php echo $det['billing_date']; ?></td>
						<td class="xcenter"><?php echo $tipe_cancel; ?></td>
						<td class="xcenter"><?php echo $det['billing_no']; ?></td>
						<td class="xright"><?php echo $det['total_billing_show']; ?></td>
						<td class="xright"><?php echo $det['tax_total_show']; ?></td>
						<td class="xright"><?php echo $det['service_total_show']; ?></td>
						<td class="xright"><?php echo $det['grand_total_show']; ?></td>
						<td class="xleft"><?php echo $det['updatedby']; ?></td>
						<td class="xleft"><?php echo $det['cancel_notes']; ?></td>
					</tr>
					<?php	
					
					$total_billing +=  $det['total_billing'];
					$total_tax +=  $det['tax_total'];
					$total_service +=  $det['service_total'];
					$grand_total +=  $det['grand_total'];
					$no++;
				}
				
				?>
				<tr class="tbl-data">
					<td class="first xright xbold" colspan="<?php echo 4; ?>">TOTAL</td>
					<td class="xright xbold"><?php echo priceFormat($total_billing); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_tax); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_service); ?></td>
					<td class="xright xbold"><?php echo priceFormat($grand_total); ?></td>					
					<td class="xright xbold">&nbsp;</td>
					<td class="xright xbold">&nbsp;</td>
				</tr>
				<?php
			}else{
			?>
				<tr class="tbl-data">
					<td colspan="<?php echo $total_cols; ?>" class="first xleft">Data Not Found</td>
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