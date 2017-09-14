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
						<div class="subtitle_report xcenter"><?php echo 'WAREHOUSE: '.strtoupper($warehouse_name);?> / <?php echo 'DATE: '.$date_from;?></div>
						<div class="subtitle_report xcenter"><?php echo 'CATEGORY: '.$category_name;?></div>	
						<br/>
					</div>
				</tr>	
				<tr class="tbl-header">
					<td class="first xcenter" width="50" rowspan="2">NO</td>
					<td class="xcenter" width="60" rowspan="2">KODE</td>
					<td class="xleft" width="260" rowspan="2">ITEM</td>
					<td class="xcenter" width="80" rowspan="2">SATUAN</td>	
					<td class="xcenter" colspan="4">TOTAL STOK</td>
					<td class="xright" width="80" rowspan="2">HARGA</td>	
					<td class="xright" width="110" rowspan="2">TOTAL<br/>PERSEDIAAN</td>	
				</tr>
				<tr class="tbl-header">
					<td class="first xcenter" width="80">KEMARIN</td>					
					<td class="xcenter" width="80">MASUK</td>					
					<td class="xcenter" width="80">KELUAR</td>					
					<td class="xcenter" width="80">SAAT INI</td>
				</tr>
			
			</thead>
			<tbody>
				<?php
				if(!empty($report_data)){
				
					$no = 1;
					$all_total_stock_kemarin = 0;
					$all_total_stock_in = 0;
					$all_total_stock_out = 0;
					$all_total_stock = 0;
					$all_total_harga = 0;
					$total_qty = 0;
					foreach($report_data as $det){
											
						if(empty($det['min_stock'])){
							$det['min_stock'] = 0;
						}
						
						if(empty($det['total_qty_stok'])){
							$det['total_qty_stok'] = 0;
						}
						
						$total_stock_kemarin = '';
						if(!empty($det['total_stock_kemarin'])){
							$total_stock_kemarin = $det['total_stock_kemarin'];
						}
						
						$total_stock_in = '';
						if(!empty($det['total_stock_in'])){
							$total_stock_in = $det['total_stock_in'];
						}
						
						$total_stock_out = '';
						if(!empty($det['total_stock_out'])){
							$total_stock_out = $det['total_stock_out'];
						}
						
						$total_stock = '';
						if(!empty($det['total_stock'])){
							$total_stock = $det['total_stock'];
						}
						
						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xleft"><?php echo $det['item_code']; ?></td>
							<td class="xleft"><?php echo $det['item_name']; ?></td>
							<td class="xcenter"><?php echo $det['satuan']; ?></td>
							<td class="xcenter xbold"><?php echo $total_stock_kemarin; ?></td>
							<td class="xcenter xbold"><?php echo $total_stock_in; ?></td>
							<td class="xcenter xbold"><?php echo $total_stock_out; ?></td>
							<td class="xcenter xbold"><?php echo $total_stock; ?></td>
							<td class="xright"><?php echo priceFormat($det['item_hpp']); ?></td>
							<td class="xright"><?php echo priceFormat($det['item_hpp']*$total_stock); ?></td>
							
						</tr>
						<?php	
						$all_total_stock_kemarin += $total_stock_kemarin;
						$all_total_stock_in += $total_stock_in;
						$all_total_stock_out += $total_stock_out;
						$all_total_stock += $total_stock;
						$all_total_harga += ($det['item_hpp']*$total_stock);
						
						$total_qty += $det['total_stock'];
						$no++;
					}
					
					?>
					<tr class="tbl-total">
						<td class="first xcenter" colspan="4">&nbsp;</td>
						<td class="xcenter xbold"><?php echo priceFormat($all_total_stock_kemarin); ?></td>
						<td class="xcenter xbold"><?php echo priceFormat($all_total_stock_in); ?></td>
						<td class="xcenter xbold"><?php echo priceFormat($all_total_stock_out); ?></td>
						<td class="xcenter xbold"><?php echo priceFormat($all_total_stock); ?></td>
						<td class="xright">&nbsp;</td>
						<td class="xright"><?php echo priceFormat($all_total_harga); ?></td>
						
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