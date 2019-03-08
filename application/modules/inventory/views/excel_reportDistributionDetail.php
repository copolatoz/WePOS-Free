<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.$date_from).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 800;
$total_cols = 9;
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
						<div class="subtitle_report_xcenter"><?php echo 'Period : '.$date_from;?></div>		
						
					</div>
				</td>
			</tr>
			<tr>
				<td class="tbl_head_td_first_xcenter" width="50">NO</td>
				<td class="tbl_head_td" width="100">DIKIRIM KE</td>
				<td class="tbl_head_td" width="130">DITERIMA OLEH</td>
				<td class="tbl_head_td_xcenter" width="90">DATE</td>		
				<td class="tbl_head_td_xcenter" width="100">DIS NO</td>			
				<td class="tbl_head_td" width="100">KODE</td>
				<td class="tbl_head_td" width="150">NAMA BARANG</td>
				<td class="tbl_head_td_xcenter" width="80">QTY</td>	
				<td class="tbl_head_td_xcenter" width="100">SATUAN</td>
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
							<td class="tbl_data_td"><?php echo $det['delivery_to_name']; ?></td>
							<td class="tbl_data_td"><?php echo $det['dis_receiver']; ?></td>
							<td class="tbl_data_td_xcenter"><?php echo $det['dis_date']; ?></td>
							<td class="tbl_data_td_xcenter"><?php echo $det['dis_number']; ?></td>
							<td class="tbl_data_td"><?php echo $det['item_code']; ?></td>
							<td class="tbl_data_td"><?php echo $det['item_name']; ?></td>
							<td class="tbl_data_td_xcenter"><?php echo $det['disd_diterima']; ?></td>
							<td class="tbl_data_td_xcenter"><?php echo $det['satuan']; ?></td>							
						</tr>
						<?php	

							$total_qty += $det['disd_diterima'];
							$no++;
						}
					}
				}
				?>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="7">TOTAL</td>
					<td class="tbl_summary_td_xcenter"><?php echo priceFormat($total_qty); ?></td>					
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