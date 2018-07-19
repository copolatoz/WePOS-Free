<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name." - ".$warehouse_name." - ".$category_name).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 970;
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
				<div class="title_report_xcenter"><?php echo $report_name;?></div>
				<div class="subtitle_report_xcenter"><?php echo 'CATEGORY: '.$category_name;?></div>
			</tr>	
			<tr>
				<td class="tbl_head_td_first_xcenter" width="50" rowspan="2">NO</td>
				<td class="tbl_head_td_xcenter" width="120" rowspan="2">KODE</td>
				<td class="tbl_head_td" width="400" rowspan="2">ITEM</td>
				<td class="tbl_head_td_xcenter" width="80" rowspan="2">SATUAN</td>	
				<td class="tbl_head_td_xcenter" colspan="3">LAST PURCHASE</td>
				<td class="tbl_head_td_xcenter" width="80" rowspan="2">AVERAGE</td>	
			</tr>
			<tr>
				<td class="tbl_head_td_first_xcenter" width="80">SEBELUMNYA</td>			
				<td class="tbl_head_td_xcenter" width="80">TERAKHIR</td>
				<td class="tbl_head_td_xcenter" width="80">TANGGAL PO</td>
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
					<tr>
						<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
						<td class="tbl_data_td"><?php echo $det['item_code']; ?></td>
						<td class="tbl_data_td"><?php echo $det['item_name']; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $det['satuan']; ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo priceFormat($det['old_last_in']); ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo priceFormat($det['last_in']); ?></td>
						<td class="tbl_data_td_xright">&nbsp;<?php echo $det['po_date']; ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo priceFormat($det['item_hpp']); ?></td>
					</tr>
					<?php	
					$no++;
				}
				
				?>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="8">&nbsp;</td>
					
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
				<td colspan="3" class="xcenter">
						Prepared by:<br/><br/><br/><br/>
						----------------------------
				</td>
				<td colspan="3" class="xcenter">
					
						Approved by:<br/><br/><br/><br/>
						----------------------------
				</td>
			</tr>
		</tbody>
	</table>
</div>
</body>
</html>