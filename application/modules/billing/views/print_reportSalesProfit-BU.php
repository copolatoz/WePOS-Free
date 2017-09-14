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
							<div class="subtitle_report xcenter"><?php echo 'Period : '.$date_from.' TO '.$date_till;?></div>			
							
						</div>
					</td>
				</tr>
				<tr class="tbl-header">
					<td class="first xcenter" width="50">NO</td>
					<td class="xcenter" width="130">PAYMENT DATE</td>
					<td class="xcenter" width="80">BILLING NO.</td>
					<td class="xcenter" width="120">TOTAL BILLING</td>
					<td class="xcenter" width="120">TOTAL HPP</td>
					<td class="xcenter" width="120">TOTAl PROFIT</td>
					<td class="xcenter" width="180">NOTE</td>
				</tr>
			</thead>
			<tbody>
				<?php
				if(!empty($report_data)){
				
					$no = 1;
					$total_billing = 0;
					$grand_total_hpp = 0;
					$grand_total_profit = 0;
					$grand_total_compliment = 0;
					$grand_total_payment = array();
					foreach($report_data as $det){
						
						$bill_hpp = 0;
						if(!empty($total_hpp[$det['billing_id']])){
							$bill_hpp = $total_hpp[$det['billing_id']];
						}
						
						$bill_hpp_show = priceFormat($bill_hpp);
						
						$total_profit = $det['total_billing'] - $bill_hpp;
						$total_profit_show = priceFormat($total_profit);
						
						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xcenter"><?php echo $det['payment_date']; ?></td>
							<td class="xcenter"><?php echo $det['billing_no']; ?></td>
							<td class="xright"><?php echo $det['total_billing_show']; ?></td>
							<td class="xright"><?php echo $bill_hpp_show; ?></td>
							<td class="xright"><?php echo $total_profit_show; ?></td>
							<td class="xleft"><?php echo $det['payment_note']; ?></td>
						</tr>
						<?php	
						
						$total_billing +=  $det['total_billing'];
						$grand_total_hpp += $bill_hpp;
						$grand_total_profit += $total_profit;
						$grand_total_compliment += $det['total_compliment'];
						$no++;
					}
					
					?>
					<tr class="tbl-total">
						<td class="first xright xbold" colspan="<?php echo $total_cols-5; ?>">TOTAL</td>
						<td class="xright xbold"><?php echo priceFormat($total_billing); ?></td>
						<td class="xright xbold"><?php echo priceFormat($grand_total_hpp); ?></td>
						<td class="xright xbold"><?php echo priceFormat($grand_total_profit); ?></td>
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