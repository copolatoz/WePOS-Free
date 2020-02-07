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
		$set_width = 1110;
		$total_cols = 10;
		
		//update-0120.001
		if(!empty($filter_column)){
			extract($filter_column);
		}
		
		if($show_compliment == false){
			$set_width -= 110;
			$total_cols -= 1;
		}
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<div>
			<div class="logo">
				
				<!-- <img height="80" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>"> -->
				
			</div>
						
			<div class="title_report"><?php echo $report_name; ?></div>
			<div class="subtitle_report" style="margin-bottom:5px;">
			<?php
			if($date_from == $date_till){
				echo 'Tanggal : '.$date_from;
			}else{
				echo 'Tanggal : '.$date_from.' s/d '.$date_till; 
			}
			
			if(!empty($user_shift)){ 
				echo ' &nbsp; | &nbsp; Shift: '.$user_shift; 
			}else{
				echo ' &nbsp; | &nbsp; Shift: Semua Shift';
			}
			
			if(!empty($user_kasir)){ 
				echo ' &nbsp; | &nbsp; Kasir: '.$user_kasir;
			}else{
				echo ' &nbsp; | &nbsp; Kasir: Semua Kasir';
			}
			
			if(!empty($tipe_sales)){ 
				echo ' &nbsp; | &nbsp; Tipe Sales: '.$tipe_sales; 
			}
			?>			
			</div>
		</div>
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<tr class="tbl-header">
				<td class="first xcenter" width="40">NO</td>
				<td class="xcenter" width="100">CODE</td>
				<td class="xcenter" width="260">PRODUCT / MENU</td>
				<td class="xcenter" width="60">TOTAL QTY</td>
				<td class="xcenter" width="90">TOTAL HPP</td>
				<td class="xcenter" width="110">TOTAL BILLING</td>
				<td class="xcenter" width="110">DISCOUNT</td>	
				<?php
				if($show_compliment == true){
				?>
				<td class="xcenter" width="110">COMPLIMENT</td>	
				<?php
				}
				?>
				<td class="xcenter" width="120">NET SALES</td>
				<td class="xcenter" width="110">TOTAL PROFIT</td>
			</tr>
			
			<?php
			if(!empty($report_data)){
			
				$no = 1;
				$total_qty = 0;
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
					
					if(empty($det['product_name'])){
						$det['product_name'] = '#'.$det['product_id'].' deleted';
					}
				
					if(empty($det['product_code'])){
						$det['product_code'] = 'N/A';
					}
					
					$discount_total = $det['discount_total']+$det['discount_billing_total'];
					?>
					<tr class="tbl-data">
						<td class="first xcenter"><?php echo $no; ?></td>
						<td class="xleft"><?php echo $det['product_code']; ?></td>
						<td class="xleft"><?php echo $det['product_name']; ?></td>
						<td class="xcenter"><?php echo $det['total_qty']; ?></td>
						<td class="xright"><?php echo $det['total_hpp_show']; ?></td>
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
						<td class="xright"><?php echo $det['total_profit_show']; ?></td>
					</tr>
					<?php	
					
					$total_qty +=  $det['total_qty'];
					$total_billing +=  $det['total_billing'];
					$total_tax +=  $det['tax_total'];
					$total_service +=  $det['service_total'];
					$grand_total +=  $det['total_billing_profit'];
					$grand_total_compliment += $det['total_compliment'];
					$grand_sub_total += $det['sub_total'];
					$grand_total_pembulatan += $det['total_pembulatan'];
					$grand_discount_total += $det['discount_total'];
					$grand_discount_billing_total += $det['discount_billing_total'];
					//$grand_total_dp += $det['total_dp'];
					$total_hpp +=  $det['total_hpp'];
					$total_profit +=  $det['total_profit'];
					
					$no++;
				}
				
				$discount_total = $grand_discount_total+$grand_discount_billing_total;
				?>
				<tr class="tbl-total">
					<td class="first xright xbold" colspan="3">TOTAL</td>
					<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_hpp); ?></td>
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
					<td class="xright xbold"><?php echo priceFormat($total_profit); ?></td>
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
			
			<tr>
				<td colspan="<?php echo $total_cols-4; ?>" class="first xleft">
					<br/>
					<br/>
					<br/>
					<br/>
					Printed: <?php echo date("d-m-Y H:i:s"); ?>
					<br/>
				</td>
				<td colspan="2" class="xcenter">
					<br/>
					Prepared by:<br/><br/><br/><br/>
					----------------------------
				</td>
				<td colspan="2" class="xcenter">
					<br/>
					Approved by:<br/><br/><br/><br/>
					----------------------------
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