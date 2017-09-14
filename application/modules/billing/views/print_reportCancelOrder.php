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
		$set_width = 1000;
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
					<td class="xcenter" width="80">DATE</td>
					<td class="xcenter" width="80">BILLING NO.</td>
					<td class="xleft" width="240">PRODUCT</td>
					<td class="xcenter" width="50">QTY</td>
					<td class="xright" width="100">PRICE</td>
					<td class="xright" width="100">TOTAL</td>
					<td class="xleft" width="100">VOID BY</td>
					<td class="xleft" width="200">NOTE</td>
				</tr>
			</thead>
			<tbody>
				<?php
				if(!empty($report_data)){
				
					$no = 1;
					$total_qty = 0;
					$total_price = 0;
								
					foreach($report_data as $billing_detail){
						
						if(!empty($billing_detail)){
							
							$no_det = 1;
							foreach($billing_detail as $det){
								$order_date_mk = strtotime($det['order_date']);
								$order_date = date("d-m-Y", $order_date_mk);
								
								if($no_det == 1){
									$no_det_txt = $no;
								}else{
									$no_det_txt = '&nbsp;';
									$order_date = '&nbsp;';
									$det['billing_no'] = '&nbsp;';
								}
								?>
								<tr class="tbl-data">
									<td class="first xcenter"><?php echo $no_det_txt; ?></td>
									<td class="xcenter"><?php echo $order_date; ?></td>
									<td class="xcenter"><?php echo $det['billing_no']; ?></td>
									<td class="xleft"><?php echo $det['product_name']; ?></td>
									<td class="xcenter"><?php echo $det['order_qty']; ?></td>
									<td class="xright"><?php echo priceFormat($det['product_price']); ?></td>
									<td class="xright"><?php echo priceFormat($det['product_price']*$det['order_qty']); ?></td>
									<td class="xleft"><?php echo $det['updatedby']; ?></td>
									<td class="xleft"><?php echo $det['cancel_order_notes']; ?></td>
								</tr>
								<?php	
								$no_det++;
								
								$total_qty += $det['order_qty'];
								$total_price += ($det['product_price']*$det['order_qty']);
							}
						}
						
						$no++;
					}
					
					?>
					<tr class="tbl-total">
						<td class="first xright xbold" colspan="<?php echo 4; ?>">TOTAL</td>
						<td class="xcenter xbold"><?php echo $total_qty; ?></td>
						<td class="xright xbold">&nbsp;</td>
						<td class="xright xbold"><?php echo priceFormat($total_price); ?></td>
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