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
		$set_width = 770;
		$total_cols = 8;
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<thead>
				<tr>
					<div>
						<div class="logo">
							
							<!-- <img height="80" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>"> -->
							
						</div>
									
						<div class="title_report xcenter"><?php echo $report_name;?></div>
						<div class="subtitle_report xcenter"><?php echo 'Period : '.$date_from.' TO '.$date_till;?></div>			
						<?php
						if(!empty($storehouse_name)){
							if($storehouse_name == 'Semua Gudang'){
								?>
								<div class="subtitle_report xcenter"><?php echo $storehouse_name; ?></div>	
								<?php
							}else{
								?>
								<div class="subtitle_report xcenter">Gudang: <?php echo $storehouse_name; ?></div>	
								<?php
							}
							
						}	
						?>	
						<br/>						
					</div>
				</tr>	
				<tr class="tbl-header">
					<td class="first xcenter" width="40">NO</td>
					<td class="xcenter" width="100">DATE</td>
					<td class="xcenter" width="80">TOTAL SO</td>
					<td class="xcenter" width="110">TOTAL SALES</td>
					<td class="xcenter" width="110">TOTAL POTONGAN</td>
					<td class="xcenter" width="110">GRAND TOTAL</td>			
					<td class="xcenter" width="110">TOTAL HPP</td>			
					<td class="xcenter" width="110">TOTAL PROFIT</td>
				</tr>
			
			</thead>
			<tbody>
				<?php
				if(!empty($report_data)){
				
					$no = 1;
					$total_so = 0;
					$total_item = 0;
					$total_qty = 0;
					$all_total_sales = 0;
					$all_total_potongan = 0;
					$all_subtotal = 0;
					$all_total_hpp = 0;
					$all_total_profit = 0;
					foreach($report_data as $det){
						$total_profit = $det['subtotal'] - $det['total_hpp'];
						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xcenter"><?php echo $det['date']; ?></td>
							<td class="xcenter"><?php echo $det['total_so']; ?></td>
							<td class="xright"><?php echo priceFormat($det['total_sales']); ?></td>
							<td class="xright"><?php echo priceFormat($det['total_potongan']); ?></td>
							<td class="xright"><?php echo priceFormat($det['subtotal']); ?></td>
							<td class="xright"><?php echo priceFormat($det['total_hpp']); ?></td>
							<td class="xright"><?php echo priceFormat($total_profit); ?></td>						
						</tr>
						<?php	

						$total_so += $det['total_so'];
						$total_item += $det['total_item'];
						$total_qty += $det['total_qty'];
						$all_total_sales +=  $det['total_sales'];
						$all_total_potongan += $det['total_potongan'];
						$all_subtotal += $det['subtotal'];
						$all_total_hpp += $det['total_hpp'];
						$all_total_profit += $total_profit;
						
						$no++;
					}
					
					?>
					<tr class="tbl-total">
						<td class="first xright xbold" colspan="2">TOTAL</td>
						<td class="xcenter xbold"><?php echo $total_so; ?></td>
						<td class="xright xbold"><?php echo priceFormat($all_total_sales); ?></td>
						<td class="xright xbold"><?php echo priceFormat($all_total_potongan); ?></td>
						<td class="xright xbold"><?php echo priceFormat($all_subtotal); ?></td>
						<td class="xright xbold"><?php echo priceFormat($all_total_hpp); ?></td>
						<td class="xright xbold"><?php echo priceFormat($all_total_profit); ?></td>
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