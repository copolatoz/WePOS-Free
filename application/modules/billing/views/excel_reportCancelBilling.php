<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.$date_from.' to '.$date_till).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 1130;
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
				<td class="tbl_head_td_xcenter" width="130">DATE</td>
				<td class="tbl_head_td_xcenter" width="130">TIPE</td>
				<td class="tbl_head_td_xcenter" width="80">BILLING NO.</td>
				<td class="tbl_head_td_xcenter" width="110">TOTAL BILLING</td>
				<td class="tbl_head_td_xcenter" width="90">TAX</td>
				<td class="tbl_head_td_xcenter" width="90">SERVICE</td>
				<td class="tbl_head_td_xcenter" width="110">GRAND TOTAL</td>
				<td class="tbl_head_td_xcenter" width="110">CANCELED BY</td>
				<td class="tbl_head_td" width="230">NOTES</td>
			</tr>
		</thead>
		<tbody>
		
		<?php
		if(!empty($report_data)){
		
			$no = 1;
			$total_billing = 0;
			$total_tax = 0;
			$total_service = 0;
			$grand_total = 0;
			$grand_total_payment = array();
			foreach($report_data as $det){
				$tipe_cancel = 'AFTER PAYMENT';
				if(empty($det['payment_date'])){
					$tipe_cancel = 'BEFORE PAYMENT';
				}
				?>
				<tr>
					<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
					<td class="tbl_data_td_xcenter"><?php echo $det['billing_date']; ?></td>
					<td class="tbl_data_td_xcenter"><?php echo $tipe_cancel; ?></td>
					<td class="tbl_data_td_xcenter"><?php echo $det['billing_no']; ?></td>
					<td class="tbl_data_td_xright">Rp. <?php echo $det['total_billing_show']; ?></td>
					<td class="tbl_data_td_xright">Rp. <?php echo $det['tax_total_show']; ?></td>
					<td class="tbl_data_td_xright">Rp. <?php echo $det['service_total_show']; ?></td>
					<td class="tbl_data_td_xright">Rp. <?php echo $det['grand_total_show']; ?></td>
					<td class="tbl_data_td"><?php echo $det['updatedby']; ?></td>
					<td class="tbl_data_td"><?php echo $det['cancel_notes']; ?></td>
				</tr>
				<?php	
				
				$total_billing +=  $det['total_billing'];
				$total_tax +=  $det['tax_total'];
				$total_service +=  $det['service_total'];
				$grand_total +=  $det['grand_total'];
				$no++;
			}
			
			?>
			<tr>
				<td class="tbl_summary_td_first_xright" colspan="<?php echo 4; ?>">TOTAL</td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_billing); ?></td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_tax); ?></td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_service); ?></td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($grand_total); ?></td>					
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
			<td colspan="2">Printed: <?php echo date("d-m-Y H:i:s");?></td>
			<td colspan="<?php echo $total_cols-6; ?>" class="xcenter">&nbsp;</td>
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