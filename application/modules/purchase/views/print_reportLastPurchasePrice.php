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
		$set_width = 960;
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
						<div class="subtitle_report xcenter"><?php echo 'CATEGORY: '.$category_name;?></div>
						<br/>
					</div>
				</tr>	
				<tr class="tbl-header">
					<td class="first xcenter" width="50" rowspan="2">NO</td>
					<td class="xcenter" width="120" rowspan="2">KODE</td>
					<td class="xleft" width="300" rowspan="2">ITEM</td>
					<td class="xcenter" width="80" rowspan="2">SATUAN</td>	
					<td class="xcenter" colspan="3">LAST PURCHASE</td>
					<td class="xcenter" width="80" rowspan="2">AVERAGE</td>	
				</tr>
				<tr class="tbl-header">
					<td class="first xcenter" width="80">SEBELUMNYA</td>					
					<td class="xcenter" width="80">TERAKHIR</td>
					<td class="xcenter" width="80">TANGGAL PO</td>
				</tr>
			
			</thead>
			<tbody>
				<?php
				if(!empty($report_data)){
				
					$no = 1;
					foreach($report_data as $det){
						$tanggal_po = '-';		
						if(!empty($det['po_date'])){
							$tanggal_po = $det['po_date'];
						}
						
						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xleft"><?php echo $det['item_code']; ?></td>
							<td class="xleft"><?php echo $det['item_name']; ?></td>
							<td class="xcenter"><?php echo $det['satuan']; ?></td>
							<td class="xright"><?php echo priceFormat($det['old_last_in']); ?></td>
							<td class="xright"><?php echo priceFormat($det['last_in']); ?></td>
							<td class="xcenter"><?php echo $tanggal_po; ?></td>
							<td class="xright"><?php echo priceFormat($det['item_hpp']); ?></td>
						</tr>
						<?php	
						$no++;
					}
					
					?>
					<tr class="tbl-total">
						<td class="first xcenter" colspan="8">&nbsp;</td>
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