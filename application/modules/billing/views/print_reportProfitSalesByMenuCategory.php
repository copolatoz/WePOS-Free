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
		$set_width = 670;
		$total_cols = 6;
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
				<td class="first xcenter" width="40">NO</td>
				<td class="xcenter" width="260">PRODUCT</td>
				<td class="xcenter" width="60">TOTAL QTY</td>
				<td class="xcenter" width="110">TOTAL BILLING</td>
				<td class="xcenter" width="90">TOTAL HPP</td>
				<td class="xcenter" width="110">TOTAL PROFIT</td>
			</tr>
			
			<?php
			if(!empty($report_data)){
			
				//SORTING BY QTY TERBANYAK
				$qty_terbanyak = array();
				foreach($report_data as $key => $dtDet){
					if(empty($qty_terbanyak[$key])){
						$qty_terbanyak[$key] = 0;
					}
					
					if(!empty($dtDet)){
						foreach($dtDet as $det){
							$qty_terbanyak[$key] += $det['total_qty'];
						}
					}
					
				}
				arsort($qty_terbanyak);
				
				
				$nox = 1;
				$total_qty = 0;
				$total_billing = 0;
				$total_hpp = 0;
				$total_profit = 0;
				$total_tax = 0;
				$total_service = 0;
				$grand_total = 0;
				$grand_total_payment = array();
				foreach($qty_terbanyak as $key => $total){
					
					$item_category_name_show = '';
					if(empty($item_category_name[$key])){
						$item_category_name_show = 'Products Deleted';
					}else{
						$item_category_name_show = $item_category_name[$key];
					}
					
					?>
					<tr class="tbl-data">
						<td class="first xcenter xbold"><?php echo $nox; ?></td>
						<td class="xleft xbold" colspan="<?php echo $total_cols-1; ?>"><?php echo $key; ?></td>
					</tr>
					<?php
					
					$no = 1;
					$cat_total_qty = 0;
					$cat_total_billing = 0;
					$cat_total_hpp = 0;
					$cat_total_profit = 0;
					$cat_total_tax = 0;
					$cat_total_service = 0;
					$cat_grand_total = 0;
					
					$dtDet = 0;
					if(!empty($report_data[$key])){
						$dtDet = $report_data[$key];
					}
					
					if(!empty($dtDet)){
						foreach($dtDet as $det){
									
							if(empty($det['product_name'])){
								$det['product_name'] = '#'.$det['product_id'];
							}
							
							?>
							<tr class="tbl-data">
								<td class="first xcenter">&nbsp;</td>
								<td class="xleft"><?php echo $det['product_name']; ?></td>
								<td class="xcenter"><?php echo $det['total_qty']; ?></td>
								<td class="xright"><?php echo $det['total_billing_show']; ?></td>
								<td class="xright"><?php echo $det['total_hpp_show']; ?></td>
								<td class="xright"><?php echo $det['total_profit_show']; ?></td>
							</tr>
							<?php	
							
							$cat_total_qty +=  $det['total_qty'];
							$cat_total_billing +=  $det['total_billing'];
							$cat_total_hpp +=  $det['total_hpp'];
							$cat_total_profit +=  $det['total_profit'];
							$cat_total_tax +=  $det['tax_total'];
							$cat_total_service +=  $det['service_total'];
							$cat_grand_total +=  $det['grand_total'];
							
							$total_qty +=  $det['total_qty'];
							$total_billing +=  $det['total_billing'];
							$total_hpp +=  $det['total_hpp'];
							$total_profit +=  $det['total_profit'];
							$total_tax +=  $det['tax_total'];
							$total_service +=  $det['service_total'];
							$grand_total +=  $det['grand_total'];
							$no++;
						}
					}
					
					$nox++;
					
					?>
					<tr class="tbl-data">
						<td class="first xright xbold" colspan="<?php echo $total_cols-4; ?>">TOTAL <?php echo $item_category_name_show; ?></td>
						<td class="xcenter xbold"><?php echo priceFormat($cat_total_qty); ?></td>
						<td class="xright xbold"><?php echo priceFormat($cat_total_billing); ?></td>
						<td class="xright xbold"><?php echo priceFormat($cat_total_hpp); ?></td>
						<td class="xright xbold"><?php echo priceFormat($cat_total_profit); ?></td>
					</tr>
					<?php
					
				}
				
				?>
				<tr class="tbl-data">
					<td class="first xright xbold" colspan="<?php echo $total_cols-4; ?>">TOTAL</td>
					<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_billing); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_hpp); ?></td>
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
		if($do == 'print' OR $do == true){
		?>
		<script type="text/javascript">
			window.print();
		</script>
		<?php
		}
	?>
</body>
</html>