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
						<div class="subtitle_report xcenter"><?php echo 'Period : '.$date_from;?></div>			
						
					</div>
				</tr>	
				<tr class="tbl-header">
					<td class="first xcenter" width="50">NO</td>	
					<td class="xcenter" width="100">RECEIVE NO</td>	
					<td class="xcenter" width="180">SUPPLIER</td>
					<td class="xcenter" width="90">DITERIMA</td>			
					<td class="xleft" width="100">KODE</td>
					<td class="xleft" width="160">NAMA BARANG</td>
					<td class="xcenter" width="60">QTY</td>	
					<td class="xcenter" width="60">SATUAN</td>	
					<td class="xcenter" width="100">HRG.BELI</td>					
					<td class="xcenter" width="100">TOTAL</td>
				</tr>
			</thead>
			<tbody>
				<?php
				if(!empty($report_data)){
				
					$no = 1;
					$total_qty = 0;
					$total_price = 0;
					foreach($report_data as $dt_det){

						if(!empty($dt_det)){

							foreach($dt_det as $det){
							?>
							<tr class="tbl-data">
								<td class="first xcenter"><?php echo $no; ?></td>
								<td class="xcenter"><?php echo $det['receive_number']; ?></td>
								<td class="xleft"><?php echo $det['supplier_name']; ?></td>
								<td class="xcenter"><?php echo $det['receive_det_date']; ?></td>
								<td class="xleft"><?php echo $det['item_code']; ?></td>
								<td class="xleft"><?php echo $det['item_name']; ?></td>
								<td class="xcenter"><?php echo $det['receive_det_qty']; ?></td>
								<td class="xcenter"><?php echo $det['satuan']; ?></td>
								<td class="xright"><?php echo $det['receive_det_purchase_text']; ?></td>
								<td class="xright"><?php echo $det['receive_det_total_text']; ?></td>
								
							</tr>
							<?php	

								$total_qty += $det['receive_det_qty'];
								$total_price += $det['receive_det_total'];
								$no++;
							}
						}
					}
					?>
					<tr class="tbl-total">
						<td class="first xright xbold" colspan="6">TOTAL</td>
						<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>					
						<td class="xright xbold" colspan="2">&nbsp;</td>
						<td class="xright xbold"><?php echo priceFormat($total_price); ?></td>
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