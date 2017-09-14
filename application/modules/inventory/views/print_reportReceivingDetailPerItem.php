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
		$set_width = 720;
		$total_cols = 6;
		$total_day_col = $total_days*3;
		$total_cols += $total_day_col;		
		$set_width += ($total_day_col*80);
		
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
									
						<div class="title_report xleft"><?php echo $report_name;?></div>
						<div class="subtitle_report xleft">Warehouse: <?php echo $warehouse_name;?></div>			
						<div class="subtitle_report xleft">Periode: <?php echo get_month($month).' '.$year;?></div>			
						<br/>
					</div>
				</tr>	
				<tr class="tbl-header">
					<td class="first xcenter" rowspan="2" width="50">NO</td>
					<td class="xcenter" rowspan="2" width="80">KODE</td>
					<td class="xcenter" rowspan="2" width="250">NAMA BARANG</td>		
					<td class="xcenter" rowspan="2" width="100">SATUAN</td>		
					<?php 
					for($i=1; $i<=$total_days; $i++){
						?>
						<td class="xcenter" colspan="3"><?php echo $i; ?></td>
						<?php 
					}
					?>		
					<td class="xcenter" rowspan="2" width="120">TOTAL<br/>QTY</td>	
					<td class="xcenter" rowspan="2" width="120">TOTAL<br/>HRG.BELI</td>	
				</tr>
				<tr class="tbl-header">
					<?php 
					for($i=1; $i<=$total_days; $i++){
						?>
						<td class="xcenter notop" width="50">QTY</td>
						<td class="xcenter notop" width="90">HRG.BELI</td>
						<td class="xcenter notop" width="100">TOTAL</td>
						<?php 
					}
					?>				
				</tr>
			
			</thead>
			<tbody>
				<?php
				
				$all_total_qty = 0;
				$all_total_price = 0;
				$all_total_perday = array();
				
				for($i=1; $i<=$total_days; $i++){
					$i_txt = $i;
					if(strlen($i_txt) == 1){
						$i_txt = '0'.$i_txt;
					}
					
					$all_total_perday['qty_'.$i_txt] = 0;
					$all_total_perday['price_'.$i_txt] = 0;
					
				}
				
				if(!empty($report_data)){
					
					if(!empty($category_data)){
						foreach($category_data as $key => $dt){

							if(!empty($category_item_data[$key])){
			
								?>
								<tr class="tbl-data" style="background-color:#e8e8e8;">
									<td class="first xbold" colspan="4" style="font-size:12px;"><?php echo $dt; ?></td>
									<td class="xright xbold" colspan="<?php echo ($total_cols-4); ?>">&nbsp;</td>
								</tr>
								<?php 
								$no = 1;
								$cat_total_qty = 0;
								$cat_total_price = 0;
								$cat_total_perday = array();
								
								for($i=1; $i<=$total_days; $i++){
									$i_txt = $i;
									if(strlen($i_txt) == 1){
										$i_txt = '0'.$i_txt;
									}
									
									$cat_total_perday['qty_'.$i_txt] = 0;
									$cat_total_perday['price_'.$i_txt] = 0;
									
								}
								
								
								foreach($category_item_data[$key] as $dtItem){

									if(!empty($report_data[$dtItem])){
										$data = $report_data[$dtItem];
									?>
										<tr class="tbl-data">
											<td class="first xcenter"><?php echo $no; ?></td>
											<td class="xcenter"><?php echo $data['item_code']; ?></td>
											<td class="xleft"><?php echo $data['item_name']; ?></td>
											<td class="xcenter"><?php echo $data['satuan']; ?></td>
											<?php 
											
											$total_item_price = 0;
											for($i=1; $i<=$total_days; $i++){
												$i_txt = $i;
												if(strlen($i_txt) == 1){
													$i_txt = '0'.$i_txt;
												}
												
												$tot_in = $data['in_'.$i_txt];
												//if($tot_in == 0){
												//	$tot_in = '&nbsp;';
												//}
												
												$hpp_in = $data['hpp_in_'.$i_txt];
												$total_in = $data['total_in_'.$i_txt];
												
												$total_item_price += $total_in;
												
												$cat_total_perday['qty_'.$i_txt] += $tot_in;
												$cat_total_perday['price_'.$i_txt] += $total_in;
												
												$all_total_perday['qty_'.$i_txt] += $tot_in;
												$all_total_perday['price_'.$i_txt] += $total_in;
												
												?>
												<td class="xcenter"><?php echo priceFormat($tot_in); ?></td>	
												<td class="xright"><?php echo priceFormat($hpp_in); ?></td>	
												<td class="xright"><?php echo priceFormat($total_in); ?></td>	
												<?php 
											}
											
											
											?>
											
											<td class="xcenter"><?php echo priceFormat($data['total_in']); ?></td>
											<td class="xright"><?php echo priceFormat($total_item_price); ?></td>
										</tr>
										<?php
										$no++;
									}
									
								}
								
								?>
								<tr class="tbl-total">
									<td class="first xright xbold" colspan="3">TOTAL <?php echo $dt; ?> </td>
									<td class="xright xbold">&nbsp;</td>	
									<?php
									$cat_total_qty = 0;
									$cat_total_price = 0;
									for($i=1; $i<=$total_days; $i++){
										$i_txt = $i;
										if(strlen($i_txt) == 1){
											$i_txt = '0'.$i_txt;
										}
										
										$cat_qty = $cat_total_perday['qty_'.$i_txt];
										$cat_total = $cat_total_perday['price_'.$i_txt];
			
										$cat_total_qty += $cat_qty;
										$cat_total_price += $cat_total;
										
										//if($cat_qty == 0){
										//	$cat_qty = '&nbsp;';
										//}
										
										?>
										<td class="xcenter xbold"><?php echo priceFormat($cat_qty); ?></td>	
										<td class="xright xbold">&nbsp;</td>	
										<td class="xright xbold"><?php echo priceFormat($cat_total); ?></td>	
										<?php 
									}
									?>
									<td class="xcenter xbold"><?php echo priceFormat($cat_total_qty); ?></td>
									<td class="xright xbold"><?php echo priceFormat($cat_total_price); ?></td>
								</tr>
								<?php
							
							}
						}
						
						?>
						<tr class="tbl-total">
							<td class="first xright xbold" colspan="3">TOTAL ALL CATEGORY </td>
							<td class="xright xbold">&nbsp;</td>	
							<?php
							$all_total_qty = 0;
							$all_total_price = 0;
							for($i=1; $i<=$total_days; $i++){
								$i_txt = $i;
								if(strlen($i_txt) == 1){
									$i_txt = '0'.$i_txt;
								}
								
								$all_qty = $all_total_perday['qty_'.$i_txt];
								$all_total = $all_total_perday['price_'.$i_txt];
	
								$all_total_qty += $all_qty;
								$all_total_price += $all_total;
								
								//if($cat_qty == 0){
								//	$cat_qty = '&nbsp;';
								//}
								
								?>
								<td class="xcenter xbold"><?php echo priceFormat($all_qty); ?></td>	
								<td class="xright xbold">&nbsp;</td>	
								<td class="xright xbold"><?php echo priceFormat($all_total); ?></td>	
								<?php 
							}
							?>
							<td class="xcenter xbold"><?php echo priceFormat($all_total_qty); ?></td>
							<td class="xright xbold"><?php echo priceFormat($all_total_price); ?></td>
						</tr>
						<?php
					}

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