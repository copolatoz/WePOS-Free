<?php
header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.$date_from.' to '.$date_till).".xls"); 
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
						<div class="subtitle_report_xcenter"><?php echo 'Period : '.$date_from.' TO '.$date_till;?></div>		
						
					</div>
				</td>
			</tr>
			<tr>
				<td class="tbl_head_td_first_xcenter" width="100">NO</td>
				<td class="tbl_head_td_xcenter" width="100">DATE</td>
				<td class="tbl_head_td_xcenter" width="50">IN</td>
				<td class="tbl_head_td_xcenter" width="50">OUT/PAY</td>
				<td class="tbl_head_td_xcenter" width="100">BILLING NO.</td>
				<td class="tbl_head_td_xcenter" width="100">GRAND TOTAL</td>
				<td class="tbl_head_td_xcenter" width="70">GUEST</td>
				<td class="tbl_head_td_xcenter" width="100">TABLE NO</td>
				<td class="tbl_head_td" width="180">KETERANGAN</td>
			</tr>
		</thead>
		<tbody>
		<?php
		
			if(!empty($report_data)){
			
				$no = 1;
				$total_guest = 0;
				$total_billing = 0;
				$grand_total = 0;
				foreach($report_data as $det){
					
					$date_bill_mk = strtotime($det['payment_date']);
					$date_bill = date("d-m-Y", $date_bill_mk);
					$date_created_mk = strtotime($det['created']);
					$in_bill = date("H:i", $date_created_mk);
					$out_bill = date("H:i", $date_bill_mk);
					?>
					<tr>
						<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $date_bill; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $in_bill; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $out_bill; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $det['billing_no']; ?></td>
						<td class="tbl_data_td_xright">&nbsp;<?php echo priceFormat($det['grand_total']); ?></td>
						<td class="tbl_data_td_xcenter">&nbsp;<?php echo priceFormat($det['total_guest']); ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $det['table_no']; ?></td>
						<td class="tbl_data_td"><?php echo $det['billing_notes']; ?></td>
					</tr>
					<?php
					
					$total_billing++;
					$total_guest +=  $det['total_guest'];
					$grand_total +=  $det['grand_total'];
					$no++;
				}
				
				?>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="<?php echo 5; ?>">TOTAL</td>
					<td class="tbl_summary_td_xright">&nbsp;<?php echo priceFormat($grand_total); ?></td>
					<td class="tbl_summary_td_xcenter">&nbsp;<?php echo priceFormat($total_guest); ?></td>
					<td class="tbl_summary_td_xright">&nbsp;</td>
					<td class="tbl_summary_td_xright">&nbsp;</td>
				</tr>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="<?php echo 5; ?>">AVERAGE</td>
					<td class="tbl_summary_td_xright">&nbsp;<?php echo priceFormat($grand_total/$total_guest,2); ?></td>
					<td class="tbl_summary_td_xcenter">&nbsp;<?php echo priceFormat($total_guest/$total_billing,2); ?></td>
					<td class="tbl_summary_td_xright">&nbsp;</td>
					<td class="tbl_summary_td_xright">&nbsp;</td>
				</tr>
				<?php
			}else{
			?>
				<tr>
					<td class="tbl_data_td_first_xcenter" colspan="<?php echo $total_cols; ?>">Data Not Found</td>
				</tr>
			<?php
			}
			
		?>	
		
		<tr>
			<td colspan="<?php echo $total_cols; ?>">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3">Printed: <?php echo date("d-m-Y H:i:s");?></td>
			<td colspan="<?php echo $total_cols-7; ?>">&nbsp;</td>
			<td class="xcenter" colspan="2">
					Prepared by:<br/><br/><br/><br/>
					----------------------------
			</td>
			<td class="xcenter" colspan="2">
				
					Approved by:<br/><br/><br/><br/>
					----------------------------
			</td>
		</tr>
		</tbody>
	</table>
</div>
</body>
</html>