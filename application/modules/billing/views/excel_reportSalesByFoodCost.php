<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.$date_from.' to '.$date_till).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 850;
$total_cols = 7;
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
				<td class="tbl_head_td" width="100">CODE</td>
				<td class="tbl_head_td" width="350">ITEM NAME</td>
				<td class="tbl_head_td_xcenter" width="80">SATUAN</td>
				<td class="tbl_head_td_xcenter" width="70">TOTAL QTY</td>
				<td class="tbl_head_td_xcenter" width="70">TOTAL HPP</td>
				<td class="tbl_head_td_xcenter" width="70">AVERAGE QTY</td>
			</tr>
		</thead>
		<tbody>
		<?php
		if(!empty($report_data)){
		
			$no = 1;
			$total_order = 0;
			$total_qty = 0;
			
			foreach($report_data as $det){
				$det['item_qty_average'] = $det['total_qty']/$det['total_order'];
					
				?>
				<tr>
					<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
					<td class="tbl_data_td">&nbsp;<?php echo $det['item_code']; ?></td>
					<td class="tbl_data_td"><?php echo $det['item_name']; ?></td>
					<td class="tbl_data_td_xcenter"><?php echo $det['unit_code']; ?></td>
					<td class="tbl_data_td_xcenter">&nbsp; <?php echo priceFormat($det['total_qty']); ?></td>
					<td class="tbl_data_td_xcenter">&nbsp; <?php echo priceFormat($det['total_order']); ?></td>
					<td class="tbl_data_td_xcenter">&nbsp; <?php echo priceFormat($det['item_qty_average']); ?></td>
				</tr>
				<?php	
				
				$total_order +=  $det['total_order'];
				$total_qty +=  $det['total_qty'];
				$no++;
			}
			
			?>
			<tr>
				<td class="tbl_summary_td_first_xright" colspan="<?php echo 4; ?>">TOTAL</td>
				<td class="tbl_summary_td_xcenter">&nbsp; <?php echo priceFormat($total_qty); ?></td>
				<td class="tbl_summary_td_xcenter">&nbsp; <?php echo priceFormat($total_order); ?></td>
				<td class="tbl_summary_td_xcenter">&nbsp;</td>
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