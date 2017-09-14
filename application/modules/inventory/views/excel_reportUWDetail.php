<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.$date_from).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 1170;
$total_cols = 10;
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
				<td class="tbl_head_td_first_xcenter" width="50">NO</td>
				<td class="tbl_head_td_xcenter" width="90">DATE</td>
				<td class="tbl_head_td_xcenter" width="80">UW.NO</td>
				<td class="tbl_head_td" width="100">FROM</td>		
				<td class="tbl_head_td" width="100">KODE</td>
				<td class="tbl_head_td" width="150">NAMA BARANG</td>
				<td class="tbl_head_td_xcenter" width="80">QTY</td>	
				<td class="tbl_head_td_xcenter" width="100">SATUAN</td>
				<td class="tbl_head_td_xcenter" width="100">HPP</td>
				<td class="tbl_head_td" width="100">USER</td>
			</tr>
		</thead>
		<tbody>
			<?php
			if(!empty($report_data)){
			
				$no = 1;
				$total_qty = 0;
				foreach($report_data as $dt_det){

					if(!empty($dt_det)){

						foreach($dt_det as $det){
						?>
						<tr>
							<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
							<td class="tbl_data_td_xcenter"><?php echo $det['uw_date']; ?></td>
							<td class="tbl_data_td_xcenter"><?php echo $det['uw_number']; ?></td>
							<td class="tbl_data_td"><?php echo $det['uw_from_name']; ?></td>
							<td class="tbl_data_td"><?php echo $det['item_code']; ?></td>
							<td class="tbl_data_td"><?php echo $det['item_name']; ?></td>
							<td class="tbl_data_td_xcenter"><?php echo $det['uwd_qty']; ?></td>
							<td class="tbl_data_td_xcenter"><?php echo $det['satuan']; ?></td>	
							<td class="tbl_data_td_xcenter">Rp. <?php echo priceFormat($det['item_hpp']); ?></td>	
							<td class="tbl_data_td"><?php echo $det['createdby']; ?></td>	
								
						</tr>
						<?php	

							$total_qty += $det['uwd_qty'];
							$no++;
						}
					}
				}
				?>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="6">TOTAL</td>
					<td class="tbl_summary_td_xcenter">Rp. <?php echo priceFormat($total_qty); ?></td>					
					<td class="tbl_summary_td_xright">&nbsp;</td>
					<td class="tbl_summary_td_xright">&nbsp;</td>
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
				<td colspan="3" class="xcenter">
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