<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.get_month($month).' '.$year).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 660;
$total_cols = 8;
$total_day_col = $total_days * 2;
$total_cols += $total_day_col;		
$set_width += ($total_day_col*50);
?>
<html>
<body>
<style>
	<?php include ASSETS_PATH."desktop/css/report.css.php"; ?>
</style>

<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
	
	<table width="<?php echo $set_width; ?>">
		<!-- HEADER -->
		<thead>
			<tr>
				<td colspan="<?php echo $total_cols ?>">
					<div>
					
						<div class="title_report"><?php echo $report_name;?></div>		
						<div class="subtitle_report">Periode: <?php echo get_month($month).' '.$year;?></div>		
						
					</div>
				</td>
			</tr>
			<tr>
				<td class="tbl_head_td_first_xcenter" rowspan="3" width="50">NO</td>
				<td class="tbl_head_td_xcenter" rowspan="3" width="80">KODE</td>
				<td class="tbl_head_td_xcenter" rowspan="3" width="250">NAMA BARANG</td>		
				<td class="tbl_head_td_xcenter" rowspan="3" width="100">SATUAN</td>				
				<td class="tbl_head_td_xcenter" rowspan="3" width="80">STOCK AWAL</td>		
				<td class="tbl_head_td_xcenter" colspan="<?php echo $total_day_col; ?>">TANGGAL</td>			
				<td class="tbl_head_td_xcenter" rowspan="2" colspan="2" width="120">TOTAL MUTASI</td>			
				<td class="tbl_head_td_xcenter" rowspan="3" width="80">STOCK AKHIR</td>
			</tr>
			<tr>
				<?php 
				for($i=1; $i<=$total_days; $i++){
					?>
					<td class="tbl_head_td_xcenter" colspan="2"><?php echo $i; ?></td>
					<?php 
				}
				?>
			</tr>
			<tr>
				<?php 
				for($i=1; $i<=$total_days; $i++){
					?>
					<td class="tbl_head_td_xcenter" width="50">In</td>
					<td class="tbl_head_td_xcenter" width="50">Out</td>
					<?php 
				}
				?>
				<td class="tbl_head_td_xcenter" width="50">In</td>
				<td class="tbl_head_td_xcenter" width="50">Out</td>					
			</tr>
		</thead>
		<tbody>
			<?php
			
			if(!empty($report_data)){
				
				if(!empty($category_data)){
					foreach($category_data as $key => $dt){

						if(!empty($category_item_data[$key])){
		
							?>
							<tr style="background-color:#e8e8e8;">
								<td class="tbl_head_td_first" colspan="5" style="font-size:12px;"><b><?php echo $dt; ?></b></td>
								<td class="tbl_head_td_xright" colspan="<?php echo ($total_cols-5); ?>">&nbsp;</td>
							</tr>
							<?php 
							$no = 1;
							foreach($category_item_data[$key] as $dtItem){

								if(!empty($report_data[$dtItem])){
								$data = $report_data[$dtItem];
								?>
									<tr>
										<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
										<td class="tbl_data_td"><?php echo $data['item_code']; ?></td>
										<td class="tbl_data_td"><?php echo $data['item_name']; ?></td>
										<td class="tbl_data_td_xcenter"><?php echo $data['satuan']; ?></td>
										<td class="tbl_data_td_xcenter"><?php echo $data['stock_awal']; ?></td>
										
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
											<td class="tbl_data_td_xcenter"><?php echo $tot_in; ?></td>												
											<td class="tbl_data_td_xcenter"><?php echo $tot_out; ?></td>
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
										
										<td class="tbl_data_td_xcenter"><?php echo $data['total_in']; ?></td>
										<td class="tbl_data_td_xcenter"><?php echo $data['total_out']; ?></td>
										<td class="tbl_data_td_xcenter"><?php echo $data['stock_akhir']; ?></td>
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
				<tr>
					<td colspan="<?php echo $total_cols; ?>" class="tbl_data_td_first_xcenter">Data Not Found</td>
				</tr>
			<?php
			}
			?>
			
			<tr>
				<td colspan="<?php echo $total_cols; ?>">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3">Printed: <?php echo date("d-m-Y H:i:s");?></td>
				<td colspan="2" class="xcenter">
						Prepared by:<br/><br/><br/><br/>
						----------------------------
				</td>
				<td colspan="4" class="xcenter">
					
						Approved by:<br/><br/><br/><br/>
						----------------------------
				</td>
				<td colspan="<?php echo $total_cols-9; ?>">&nbsp;</td>
			</tr>
		</tbody>
	</table>
</div>
</body>
</html>