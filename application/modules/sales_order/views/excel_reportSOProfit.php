<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.$date_from).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 750;
$total_cols = 8;
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
					
						<div class="title_report_xcenter"><?php echo $report_name;?></div>		
						<div class="subtitle_report_xcenter"><?php echo 'Period : '.$date_from.' TO '.$date_till;?></div>	
						<?php
						if(!empty($storehouse_name)){
							if($storehouse_name == 'Semua Gudang'){
								?>
								<div class="subtitle_report_xcenter"><?php echo $storehouse_name; ?></div>	
								<?php
							}else{
								?>
								<div class="subtitle_report_xcenter">Gudang: <?php echo $storehouse_name; ?></div>	
								<?php
							}
							
						}	
						?>		
					</div>
				</td>
			</tr>
			<tr>
				<td class="tbl_head_td_first_xcenter" width="50">NO</td>
				<td class="tbl_head_td_xcenter" width="100">PO DATE</td>
				<td class="tbl_head_td_xcenter" width="100">PO NO.</td>
				<td class="tbl_head_td_xcenter" width="100">TOTAL SALES</td>
				<td class="tbl_head_td_xcenter" width="100">POTONGAN</td>
				<td class="tbl_head_td_xcenter" width="100">GRAND TOTAL</td>
				<td class="tbl_head_td_xcenter" width="100">TOTAL HPP</td>
				<td class="tbl_head_td_xcenter" width="100">PROFIT</td>	
			</tr>
		
		</thead>
		<tbody>
			<?php
			if(!empty($report_data)){
			
				$no = 1;
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
					<tr>
						<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $det['so_date']; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $det['so_number']; ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo priceFormat($det['total_sales']); ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo priceFormat($det['total_potongan']); ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo priceFormat($det['subtotal']); ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo priceFormat($det['total_hpp']); ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo priceFormat($total_profit); ?></td>
						
					</tr>
					<?php	
										
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
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="3">TOTAL</td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($all_total_sales); ?></td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormatAcc($all_total_potongan); ?></td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($all_subtotal); ?></td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($all_total_hpp); ?></td>	
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($all_total_profit); ?></td>	
				</tr>
				<?php
			}else{
			?>
				<tr>
					<td colspan="<?php echo $total_cols; ?>" class="tbl_data_td_first_xcenter">Data Not Found</td>
				</tr>
			<?php
			}
			?>
		
		</tbody>
	</table>
</div>
</body>
</html>