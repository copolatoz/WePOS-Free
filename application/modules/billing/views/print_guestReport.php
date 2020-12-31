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
		$set_width = 800;
		$total_cols = 9;
		
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
					<td class="first xcenter" width="50">NO</td>
					<td class="xcenter" width="100">DATE</td>
					<td class="xcenter" width="50">IN</td>
					<td class="xcenter" width="50">OUT/PAY</td>
					<td class="xcenter" width="100">BILLING NO.</td>
					<td class="xcenter" width="100">GRAND TOTAL</td>
					<td class="xcenter" width="70">GUEST</td>
					<td class="xcenter" width="100">TABLE NO</td>
					<td class="xleft" width="180">KETERANGAN</td>
				</tr>
			</thead>
			<tbody>
				<?php
				if(!empty($report_data)){
				
					$no = 1;
					$total_guest = 0;
					$total_billing = 0;
					$grand_total = 0;
					foreach($report_data as $det){
						
						$date_bill_mk = strtotime($det['payment_date']);
						$date_bill = date("d-m-Y", $date_bill_mk);
						$date_created_mk = strtotime($det['created']);
						$in_bill = date("H:i", $date_created_mk);
						$out_bill = date("H:i", $date_bill_mk);
						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xcenter"><?php echo $date_bill; ?></td>
							<td class="xcenter"><?php echo $in_bill; ?></td>
							<td class="xcenter"><?php echo $out_bill; ?></td>
							<td class="xcenter"><?php echo $det['billing_no']; ?></td>
							<td class="xright"><?php echo priceFormat($det['grand_total']); ?></td>
							<td class="xcenter"><?php echo priceFormat($det['total_guest']); ?></td>
							<td class="xcenter"><?php echo $det['table_no']; ?></td>
							<td class="xleft"><?php echo $det['billing_notes']; ?></td>
						</tr>
						<?php	
						
						$total_billing++;
						$total_guest +=  $det['total_guest'];
						$grand_total +=  $det['grand_total'];
						$no++;
					}
					
					?>
					<tr class="tbl-total">
						<td class="first xright xbold" colspan="<?php echo 5; ?>">TOTAL</td>
						<td class="xright xbold"><?php echo priceFormat($grand_total); ?></td>
						<td class="xcenter xbold"><?php echo priceFormat($total_guest); ?></td>
						<td class="xright xbold">&nbsp;</td>
						<td class="xright xbold">&nbsp;</td>
					</tr>
					<tr class="tbl-total">
						<td class="first xright xbold" colspan="<?php echo 5; ?>">AVERAGE</td>
						<td class="xright xbold"><?php echo priceFormat($grand_total/$total_guest,2); ?></td>
						<td class="xcenter xbold"><?php echo priceFormat($total_guest/$total_billing,2); ?></td>
						<td class="xright xbold">&nbsp;</td>
						<td class="xright xbold">&nbsp;</td>
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
						<div class="fright" style="width:150px;">
							Prepared by:<br/><br/><br/><br/>
							----------------------------
						</div>
						<div class="fright" style="width:150px;">
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