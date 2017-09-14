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
		$set_width = 1050;
		$total_cols = 10;
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<div>
			<div class="logo">
				
				<!-- <img height="80" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>"> -->
				
			</div>
						
			<div class="title_report xcenter"><?php echo $report_name;?></div>
			<?php 
			if(!empty($user_shift)){ 
				?>
				<div class="subtitle_report xcenter"><?php echo 'Shift: '.$user_shift;?></div>		
				<?php 				
			}else{
				?>
				<div class="subtitle_report xcenter"><?php echo 'Shift: All Shift';?></div>		
				<?php 
				$total_cols++;
			}
			?>
			<div class="subtitle_report xcenter"><?php echo 'Period : '.$date_from;?></div>				
			
		</div>
		<br/>
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<tr class="tbl-header">
				<td class="first xcenter" width="40">NO</td>
				<td class="xcenter" width="130">PAYMENT DATE</td>
				<td class="xcenter" width="80">BILLING NO.</td>
				<td class="xcenter" width="110">TOTAL BILLING</td>
				<td class="xcenter" width="110">DISCOUNT</td>
				<td class="xcenter" width="110">COMPLIMENT</td>
				<td class="xcenter" width="110">TOTAL HPP</td>
				<td class="xcenter" width="110">TOTAL PROFIT</td>
				<td class="xcenter" width="150">NOTE</td>
				<td class="xcenter" width="100">CASHIER</td>
					
			</tr>
			
			<?php
			if(!empty($report_data)){
			
				$no = 1;
				$total_billing = 0;
				$total_hpp = 0;
				$total_profit = 0;
				$total_tax = 0;
				$total_service = 0;
				$grand_total = 0;
				$grand_discount_total = 0;
				$grand_total_compliment = 0;
				$grand_total_payment = array();
				foreach($report_data as $det){
					?>
					<tr class="tbl-data">
						<td class="first xcenter"><?php echo $no; ?></td>
						<td class="xcenter"><?php echo $det['payment_date']; ?></td>
						<td class="xcenter"><?php echo $det['billing_no']; ?></td>
						<td class="xright"><?php echo $det['total_billing_show']; ?></td>
						<td class="xright"><?php echo $det['discount_total_show']; ?></td>
						<td class="xright"><?php echo $det['total_compliment_show']; ?></td>
						<td class="xright"><?php echo $det['total_hpp_show']; ?></td>
						<td class="xright"><?php echo $det['total_profit_show']; ?></td>
						<td class="xleft"><?php echo $det['payment_note']; ?></td>
						<td class="xleft"><?php echo $det['user_fullname']; ?></td>
						
					</tr>
					<?php	
					
					$total_billing +=  $det['total_billing'];
					$total_hpp +=  $det['total_hpp'];
					$total_profit +=  $det['total_profit'];
					$total_tax +=  $det['tax_total'];
					$total_service +=  $det['service_total'];
					$grand_total +=  $det['grand_total'];
					$grand_total_compliment += $det['total_compliment'];
					$grand_discount_total += $det['discount_total'];
						
					$no++;
				}
				
				?>
				<tr class="tbl-data">
					<td class="first xright xbold" colspan="<?php echo 3; ?>">TOTAL</td>
					<td class="xright xbold"><?php echo priceFormat($total_billing); ?></td>
					<td class="xright xbold"><?php echo priceFormat($grand_discount_total); ?></td>
					<td class="xright xbold"><?php echo priceFormat($grand_total_compliment); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_hpp); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_profit); ?></td>
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