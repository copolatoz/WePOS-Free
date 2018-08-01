<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.$date_from.' to '.$date_till).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 850;
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
						
					</div>
				</td>
			</tr>	
			<tr>
				<td class="tbl_head_td_first_xcenter" width="40">NO</td>
				<td class="tbl_head_td_xcenter" width="90">RECEIVING DATE</td>
				<td class="tbl_head_td_xcenter" width="80">RECEIVING NO.</td>
				<td class="tbl_head_td" width="160">SUPPLIER</td>
				<td class="tbl_head_td_xcenter" width="80">TOTAL BRG</td>
				<td class="tbl_head_td_xcenter" width="80">TOTAL QTY</td>		
				<td class="tbl_head_td_xcenter" width="120">TOTAL PRICE</td>
				<td class="tbl_head_td" width="200">NOTES</td>
			</tr>
		
		</thead>
		<tbody>
			<?php
			if(!empty($report_data)){
			
				$no = 1;
				$discount = 0;
				$total_item = 0;
				$total_qty = 0;
				$total_price = 0;
				foreach($report_data as $det){

					?>
					<tr>
						<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
						<td class="tbl_data_td_xright"><?php echo $det['receive_date']; ?></td>
						<td class="tbl_data_td_xright"><?php echo $det['receive_number']; ?></td>
						<td class="tbl_data_td"><?php echo $det['supplier_name']; ?></td>
						<td class="tbl_data_td_xright"><?php echo priceFormat($det['total_item']); ?></td>
						<td class="tbl_data_td_xright"><?php echo priceFormat($det['total_qty']); ?></td>
						<td class="tbl_data_td_xright"><?php echo priceFormat($det['total_price']-$det['discount']); ?></td>
						<td class="tbl_data_td"><?php echo $det['receive_memo']; ?></td>
						
					</tr>
					<?php	
										
					$total_item += $det['total_item'];
					$total_qty += $det['total_qty'];
					$total_price +=  ($det['total_price']-$det['discount']);
					$discount +=  $det['discount'];
					
					$no++;
				}
				
				?>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="4">TOTAL</td>
					<td class="tbl_summary_td_xcenter"><?php echo $total_item; ?></td>
					<td class="tbl_summary_td_xcenter"><?php echo priceFormat($total_qty); ?></td>
					<td class="tbl_summary_td_xright"><?php echo priceFormat($total_price); ?></td>					
					<td class="tbl_summary_td_xright">&nbsp;</td>
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
			
			<tr>
				<td colspan="<?php echo $total_cols; ?>">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3">Printed: <?php echo date("d-m-Y H:i:s");?></td>
				<td class="xcenter">&nbsp;</td>
				<td colspan="2"class="xcenter">
						Prepared by:<br/><br/><br/><br/>
						----------------------------
				</td>
				<td colspan="2" class="xcenter">
					
						Approved by:<br/><br/><br/><br/>
						----------------------------
				</td>
			</tr>
		</tbody>
	</table>
			
	
</div>
</body>
</html>