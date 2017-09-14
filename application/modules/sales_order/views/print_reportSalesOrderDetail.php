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
					</div>
				</tr>	
				<tr class="tbl-header">
					<td class="first xcenter" width="50">NO</td>
					<td class="xleft" width="180">CUSTOMER</td>	
					<td class="xcenter" width="100">SO.NO</td>		
					<td class="xleft" width="150">KODE</td>
					<td class="xleft" width="150">NAMA BARANG</td>
					<td class="xcenter" width="60">QTY</td>		
					<td class="xcenter" width="100">HARGA</td>					
					<td class="xcenter" width="100">SUB TOTAL</td>
					<td class="xcenter" width="100">POTONGAN</td>
					<td class="xcenter" width="100">TOTAL</td>
				</tr>
			
			</thead>
			<tbody>
				<?php
				if(!empty($report_data)){
				
					$no = 1;
					$total_qty = 0;
					$total_price = 0;
					$total_potongan = 0;
					$grand_total = 0;
					foreach($report_data as $so_date => $dt_det){

						if(!empty($dt_det)){
							
							?>
							<tr class="tbl-data">
								<td class="first xleft xbold" colspan="<?php echo $total_cols; ?>"> TANGGAL: <?php echo $so_date; ?></td>
							</tr>
							<?php
							$sub_total_qty = 0;
							$sub_total_price = 0;
							$sub_total_potongan = 0;
							$sub_grand_total = 0;
							
							foreach($dt_det as $det){
								
								$total_det = $det['sod_total'] - $det['sod_potongan'];
							?>
							<tr class="tbl-data">
								<td class="first xcenter"><?php echo $no; ?></td>
								<td class="xleft"><?php echo $det['so_customer_name']; ?></td>
								<td class="xcenter"><?php echo $det['so_number']; ?></td>
								<td class="xleft"><?php echo $det['item_code']; ?></td>
								<td class="xleft"><?php echo $det['item_name']; ?></td>
								<td class="xcenter"><?php echo $det['sod_qty']; ?></td>
								<td class="xright"><?php echo $det['sales_price_show']; ?></td>
								<td class="xright"><?php echo $det['sod_total_show']; ?></td>
								<td class="xright"><?php echo $det['sod_potongan_show']; ?></td>
								<td class="xright"><?php echo priceFormat($total_det); ?></td>
								
							</tr>
							<?php	

								$sub_total_qty += $det['sod_qty'];
								$sub_total_price += $det['sod_total'];
								$sub_total_potongan += $det['sod_potongan'];
								$sub_grand_total += $total_det;
								
								$total_qty += $det['sod_qty'];
								$total_price += $det['sod_total'];
								$total_potongan += $det['sod_potongan'];
								$grand_total += $total_det;
								$no++;
							}
							
							?>
							<tr class="tbl-total">
								<td class="first xright" colspan="5"> TOTAL: <?php echo $so_date; ?></td>
								<td class="xcenter xbold"><?php echo priceFormat($sub_total_qty); ?></td>					
								<td class="xright xbold">&nbsp;</td>
								<td class="xright xbold"><?php echo priceFormat($sub_total_price); ?></td>				
								<td class="xright xbold"><?php echo priceFormat($sub_total_potongan); ?></td>
								<td class="xright xbold"><?php echo priceFormat($sub_grand_total); ?></td>
							</tr>
							<?php	
						}
					}
					?>
					<tr class="tbl-total">
						<td class="first xright xbold" colspan="5">ALL TOTAL</td>
						<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>					
						<td class="xright xbold" colspan="2">&nbsp;</td>
						<td class="xright xbold"><?php echo priceFormat($total_price); ?></td>				
						<td class="xright xbold"><?php echo priceFormat($total_potongan); ?></td>	
						<td class="xright xbold"><?php echo priceFormat($grand_total); ?></td>	
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