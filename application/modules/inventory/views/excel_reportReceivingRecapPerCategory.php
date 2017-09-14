<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.get_month($month).' '.$year).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 450;
$total_cols = 3;
$total_day_col = $total_days*3;
$total_cols += $total_day_col;		
$set_width += ($total_day_col*80);
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
						<div class="subtitle_report">Warehouse: <?php echo $warehouse_name;?></div>		
						<div class="subtitle_report">Periode: <?php echo get_month($month).' '.$year;?></div>		
						
					</div>
				</td>
			</tr>
			<tr>
				<td class="tbl_head_td_first_xcenter" rowspan="2" width="250">CATEGORY NAME</td>			
				<?php 
				for($i=1; $i<=$total_days; $i++){
					?>
					<td class="tbl_head_td_xcenter" colspan="3"><?php echo $i; ?></td>
					<?php 
				}
				?>			
				<td class="tbl_head_td_xcenter" rowspan="2" width="100">TOTAL<br/>QTY</td>			
				<td class="tbl_head_td_xcenter" rowspan="2" width="100">TOTAL<br/>HRG.BELI</td>
			</tr>
			<tr>
				<?php 
				for($i=1; $i<=$total_days; $i++){
					?>
					<td class="tbl_head_td_xcenter" width="50">QTY</td>
					<td class="tbl_head_td_xcenter" width="90">HRG.BELI</td>
					<td class="tbl_head_td_xcenter" width="100">TOTAL</td>
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
										
									}
									
									$no++;
								}
								
							}
							
							?>
							<tr>
								<td class="tbl_data_td_first"><?php echo $dt; ?> </td>
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
									<td class="tbl_data_td_xcenter"><?php echo priceFormat($cat_qty); ?></td>	
									<td class="tbl_data_td_xright">&nbsp;</td>	
									<td class="tbl_data_td_xright">Rp. <?php echo priceFormat($cat_total); ?></td>	
									<?php 
								}
								?>
								<td class="tbl_data_td_xcenter"><?php echo priceFormat($cat_total_qty); ?></td>
								<td class="tbl_data_td_xright">Rp. <?php echo priceFormat($cat_total_price); ?></td>
							</tr>
							<?php
						}
					}
					
					?>
					<tr>
						<td class="tbl_summary_td_first_xright">TOTAL </td>
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
							<td class="tbl_summary_td_xcenter"><?php echo priceFormat($all_qty); ?></td>	
							<td class="tbl_summary_td_xright">&nbsp;</td>	
							<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($all_total); ?></td>	
							<?php 
						}
						?>
						<td class="tbl_summary_td_xcenter"><?php echo priceFormat($all_total_qty); ?></td>
						<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($all_total_price); ?></td>
					</tr>
					<?php
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
				<td colspan="<?php echo $total_cols-8; ?>">Printed: <?php echo date("d-m-Y H:i:s");?></td>
				<td colspan="4" class="xcenter">
						Prepared by:<br/><br/><br/><br/>
						----------------------------
				</td>
				<td colspan="4" class="xcenter">
					
						Approved by:<br/><br/><br/><br/>
						----------------------------
				</td>
			</tr>
		</tbody>
	</table>
</div>
</body>
</html>