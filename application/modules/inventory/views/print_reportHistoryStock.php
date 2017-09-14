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
		$set_width = 660;
		$total_cols = 8;
		$total_day_col = $total_days * 2;
		$total_cols += $total_day_col;		
		$set_width += ($total_day_col*50);
		
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
						<div class="subtitle_report xleft">Periode: <?php echo get_month($month).' '.$year;?></div>			
						
					</div>
				</tr>	
				<tr class="tbl-header">
					<td class="first xcenter" rowspan="3" width="50">NO</td>
					<td class="xcenter" rowspan="3" width="80">KODE</td>
					<td class="xcenter" rowspan="3" width="250">NAMA BARANG</td>		
					<td class="xcenter" rowspan="3" width="100">SATUAN</td>				
					<td class="xcenter" rowspan="3" width="80">STOCK AWAL</td>		
					<td class="xcenter" colspan="<?php echo $total_day_col; ?>">TANGGAL</td>			
					<td class="xcenter" rowspan="2" colspan="2" width="120">TOTAL MUTASI</td>			
					<td class="xcenter" rowspan="3" width="80">STOCK AKHIR</td>
				</tr>
				<tr class="tbl-header">
					<?php 
					for($i=1; $i<=$total_days; $i++){
						?>
						<td class="xcenter notop" colspan="2"><?php echo $i; ?></td>
						<?php 
					}
					?>
				</tr>
				<tr class="tbl-header">
					<?php 
					for($i=1; $i<=$total_days; $i++){
						?>
						<td class="xcenter notop" width="50">In</td>
						<td class="xcenter notop" width="50">Out</td>
						<?php 
					}
					?>
					<td class="xcenter notop" width="50">In</td>
					<td class="xcenter notop" width="50">Out</td>					
				</tr>
			
			</thead>
			<tbody>
				<?php
				
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
								foreach($category_item_data[$key] as $dtItem){

									if(!empty($report_data[$dtItem])){
										$data = $report_data[$dtItem];
									?>
										<tr class="tbl-data">
											<td class="first xcenter"><?php echo $no; ?></td>
											<td class="xleft"><?php echo $data['item_code']; ?></td>
											<td class="xleft"><?php echo $data['item_name']; ?></td>
											<td class="xcenter"><?php echo $data['satuan']; ?></td>
											<td class="xcenter"><?php echo $data['stock_awal']; ?></td>
											
											<?php 
											for($i=1; $i<=$total_days; $i++){
												$i_txt = $i;
												if(strlen($i_txt) == 1){
													$i_txt = '0'.$i_txt;
												}
												
												$tot_in = $data['in_'.$i_txt];
												if($tot_in == 0){
													$tot_in = '&nbsp;';
												}
												
												$tot_out = $data['out_'.$i_txt];
												if($tot_out == 0){
													$tot_out = '&nbsp;';
												}
												?>
												<td class="xcenter"><?php echo $tot_in; ?></td>												
												<td class="xcenter"><?php echo $tot_out; ?></td>
												<?php 
											}
											
											$total_stock_in_out = priceFormat($data['total_in']-$data['total_out']);
											$total_stock_in_out = numberFormat($total_stock_in_out);
											$data['stock_akhir'] = $data['stock_awal'] + $total_stock_in_out;
											
											if($data['stock_akhir'] <= 0.0001){
												if($data['stock_akhir'] < 0){
													
												}else{
													$data['stock_akhir'] = 0;
												}
											}
											
											?>
											
											<td class="xcenter"><?php echo $data['total_in']; ?></td>
											<td class="xcenter"><?php echo $data['total_out']; ?></td>
											<td class="xcenter"><?php echo $data['stock_akhir']; ?></td>
										</tr>
										<?php
										$no++;
									}
									
								}
							}
						}
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