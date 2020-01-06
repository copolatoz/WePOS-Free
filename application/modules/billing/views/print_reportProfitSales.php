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
		$set_width = 1250;
		$total_cols = 11;
		
		//update-0120.001
		if(!empty($filter_column)){
			extract($filter_column);
		}
		if($show_compliment == false){
			$set_width -= 100;
			$total_cols -= 1;
		}
		if($show_note == false){
			$set_width -= 200;
			$total_cols -= 1;
		}
		if($show_shift_kasir == false){
			$set_width -= 200;
			$total_cols -= 1;
		}
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
										
							<div class="title_report"><?php echo $report_name; ?></div>
							<?php
							if($date_from == $date_till){
								?>
								<div class="subtitle_report"><?php echo 'Tanggal : '.$date_from; ?></div>		
								<?php
							}else{
								?>
								<div class="subtitle_report"><?php echo 'Tanggal : '.$date_from.' s/d '.$date_till; ?></div>		
								<?php
							}
							
							if(!empty($user_shift)){ 
								?>
								<div class="subtitle_report"><?php echo 'Shift: '.$user_shift; ?></div>		
								<?php 				
							}else{
								?>
								<div class="subtitle_report"><?php echo 'Shift: All Shift'; ?></div>		
								<?php 
								//$total_cols++;
							}
							if(!empty($user_kasir)){ 
								?>
								<div class="subtitle_report"><?php echo 'Kasir: '.$user_kasir; ?></div>		
								<?php 				
							}
							?>			
							
						</div>
					</td>
				</tr>
				<tr class="tbl-header">
					<td class="first xcenter" width="50">NO</td>
					<td class="xcenter" width="130">PAYMENT DATE</td>
					<td class="xcenter" width="80">BILLING NO.</td>
					<td class="xcenter" width="100">TOTAL BILLING</td>
					<td class="xcenter" width="110">DISCOUNT</td>	
					<?php
					if($show_compliment == true){
					?>
					<td class="xcenter" width="110">COMPLIMENT</td>	
					<?php
					}
					?>
					<td class="xcenter" width="120">NET SALES</td>
					<td class="xcenter" width="110">TOTAL HPP</td>
					<td class="xcenter" width="110">TOTAL PROFIT</td>
					<?php
					if($show_note == true){
					?>
					<td class="xcenter" width="200">NOTE</td>
					<?php
					}
					if($show_shift_kasir == true){
					?>
					<td class="xcenter" width="200">SHIFT/KASIR</td>
					<?php
					}
					?>
					
				</tr>
			</thead>
			<tbody>
				<?php
				if(!empty($report_data)){
				
					$no = 1;
					$total_hpp = 0;
					$total_profit = 0;
					$total_billing = 0;
					$total_tax = 0;
					$total_service = 0;
					$grand_total = 0;
					$grand_total_dp = 0;
					$grand_sub_total = 0;
					$grand_total_pembulatan = 0;
					$grand_discount_total = 0;
					$grand_discount_billing_total = 0;
					$grand_total_compliment = 0;
					$grand_total_payment = array();
					foreach($report_data as $det){
						
						$discount_total = $det['discount_total']+$det['discount_billing_total'];
						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class=""><?php echo $det['payment_date']; ?></td>
							<td class=""><?php echo $det['billing_no']; ?></td>
							<td class="xright"><?php echo $det['total_billing_show']; ?></td>
							<td class="xright"><?php echo priceFormat($discount_total); ?></td>
							<?php
							if($show_compliment == true){
							?>
							<td class="xright"><?php echo $det['total_compliment_show']; ?></td>
							<?php
							}
							?>
							<td class="xright"><?php echo $det['total_billing_profit_show']; ?></td>
							<td class="xright"><?php echo $det['total_hpp_show']; ?></td>
							<td class="xright"><?php echo $det['total_profit_show']; ?></td>
							<?php
							if($show_note == true){
							?>
							<td class=""><?php echo $det['payment_note']; ?></td>
							<?php
							}
							
							if($show_shift_kasir == true){
							?>
							<td class=""><?php echo $det['nama_shift'].'/'.$det['nama_kasir']; ?></td>
							<?php
							}
							?>
							
						</tr>
						<?php	
						
						$total_billing +=  $det['total_billing'];
						$total_tax +=  $det['tax_total'];
						$total_service +=  $det['service_total'];
						$grand_total +=  $det['total_billing_profit'];
						$grand_total_compliment += $det['total_compliment'];
						$grand_sub_total += $det['sub_total'];
						$grand_total_pembulatan += $det['total_pembulatan'];
						$grand_discount_total += $det['discount_total'];
						$grand_discount_billing_total += $det['discount_billing_total'];
						$grand_total_dp += $det['total_dp'];
						$total_hpp +=  $det['total_hpp'];
						$total_profit +=  $det['total_profit'];
						
						$no++;
					}
					
					$discount_total = $grand_discount_total+$grand_discount_billing_total;
					?>
					<tr class="tbl-total">
						<td class="first xright xbold" colspan="3">TOTAL</td>
						<td class="xright xbold"><?php echo priceFormat($total_billing); ?></td>
						<td class="xright xbold"><?php echo priceFormat($discount_total); ?></td>
						<?php
						if($show_compliment == true){
						?>
						<td class="xright xbold"><?php echo priceFormat($grand_total_compliment); ?></td>
						<?php
						}
						?>
						<td class="xright xbold"><?php echo priceFormat($grand_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_hpp); ?></td>
						<td class="xright xbold"><?php echo priceFormat($total_profit); ?></td>
						<?php
						if($show_note == true){
						?>
						<td class="">&nbsp;</td>
						<?php
						}
						
						if($show_shift_kasir == true){
						?>
						<td class="">&nbsp;</td>
						<?php
						}
						?>
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