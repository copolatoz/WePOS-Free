<?php
header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=export_receiving_persupplier_".$date_from."_sd_".$date_till.".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 1000;
$total_cols = 6;
?>
<html>
<body>
<style>
	<?php include ASSETS_PATH."desktop/css/report.css.php"; ?>
</style>
<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
	<div>
					
		<div class="title_report_xcenter"><?php echo $report_name;?></div>	
		<div class="subtitle_report_xcenter"><?php echo 'Period : '.$date_from.' TO '.$date_till; ?></div>			
		
	</div>
		
	<table width="<?php echo $set_width; ?>">
		<!-- HEADER -->
		<thead>
			<tr>
				<td class="tbl_head_td_first_xcenter" width="50">No</td>
				<td class="tbl_head_td" width="300">SUPPLIER</td>
				<td class="tbl_head_td_xcenter" width="150">TOTAL RECEIVING</td>
				<td class="tbl_head_td_xcenter" width="150">TOTAL BARANG</td>
				<td class="tbl_head_td_xcenter" width="150">TOTAL QTY</td>
				<td class="tbl_head_td_xright" width="200">TOTAL HRG.BELI</td>
			</tr>
		</thead>
		<tbody>
		<?php
			$no = 0;
			$total_receiving = 0;
			$total_item = 0;
			$total_qty = 0;
			$total_price = 0;
			if(!empty($report_data)){
				foreach($report_data as $det){
					$no++;
					
					if(empty($det['supplier_name'])){
						$det['supplier_name'] = '-';
					}
					if(empty($det['total_receiving'])){
						$det['total_receiving'] = 0;
					}
					if(empty($det['total_item'])){
						$det['total_item'] = 0;
					}
					if(empty($det['total_qty'])){
						$det['total_qty'] = 0;
					}
					if(empty($det['total_price'])){
						$det['total_price'] = 0;
					}
					?>
					<tr>
						<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
						<td class="tbl_data_td"><?php echo $det['supplier_name']; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $det['total_receiving']; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo priceFormat($det['total_item']); ?></td>
						<td class="tbl_data_td_xcenter"><?php echo priceFormat($det['total_qty']); ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo priceFormat($det['total_price']); ?></td>
					</tr>
					<?php
					
					$total_receiving += $det['total_receiving'];
					$total_item += $det['total_item'];
					$total_qty += $det['total_qty'];
					$total_price += $det['total_price'];
					
				}
				
				?>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="2">TOTAL &nbsp; </td>
					<td class="tbl_summary_td_xcenter"><?php echo $total_receiving; ?></td>
					<td class="tbl_summary_td_xcenter"><?php echo $total_item; ?></td>
					<td class="tbl_summary_td_xcenter"><?php echo priceFormat($total_qty); ?></td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_price); ?></td>
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
				<td colspan="2">Printed: <?php echo date("d-m-Y H:i:s");?></td>
				<td colspan="2" class="xcenter">
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