<?php
header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.$date_from.' to '.$date_till).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 1000;
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
				<td class="tbl_head_td_first_xcenter" width="50">NO</td>
				<td class="tbl_head_td_xcenter" width="80">DATE</td>
				<td class="tbl_head_td_xcenter" width="80">BILLING NO.</td>
				<td class="tbl_head_td" width="240">PRODUCT</td>
				<td class="tbl_head_td_xcenter" width="50">QTY</td>
				<td class="tbl_head_td_xright" width="100">PRICE</td>
				<td class="tbl_head_td_xright" width="100">TOTAL</td>
				<td class="tbl_head_td" width="100">VOID BY</td>
				<td class="tbl_head_td" width="200">NOTES</td>
			</tr>
		</thead>
		<tbody>
		<?php
		
			if(!empty($report_data)){
			
				$no = 1;
					$total_qty = 0;
					$total_price = 0;
								
					foreach($report_data as $billing_detail){
						
						if(!empty($billing_detail)){
							
							$no_det = 1;
							foreach($billing_detail as $det){
								$order_date_mk = strtotime($det['order_date']);
								$order_date = date("d-m-Y", $order_date_mk);
								
								if($no_det == 1){
									$no_det_txt = $no;
								}else{
									$no_det_txt = '&nbsp;';
									$order_date = '&nbsp;';
									$det['billing_no'] = '&nbsp;';
								}
					
									?>
									<tr>
										<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
										<td class="tbl_data_td"> <?php echo $order_date; ?></td>
										<td class="tbl_data_td_xcenter"><?php echo $det['billing_no']; ?></td>
										<td class="tbl_data_td"><?php echo $det['product_name']; ?></td>
										<td class="tbl_data_td_xcenter"><?php echo $det['order_qty']; ?></td>
										<td class="tbl_data_td_xright">Rp. <?php echo priceFormat($det['product_price']); ?></td>
										<td class="tbl_data_td_xright">Rp. <?php echo priceFormat($det['product_price']*$det['order_qty']); ?></td>
										<td class="tbl_data_td"><?php echo $det['updatedby']; ?></td>
										<td class="tbl_data_td"><?php echo $det['cancel_order_notes']; ?></td>
									</tr>
									<?php	
								$no_det++;
								
								$total_qty += $det['order_qty'];
								$total_price += ($det['product_price']*$det['order_qty']);
							}
						}
						
						$no++;
					}
				?>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="<?php echo 4; ?>">TOTAL </td>
					<td class="tbl_summary_td_xcenter"><?php echo $total_qty; ?></td>
					<td class="tbl_summary_td_xcenter">&nbsp;</td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_price); ?></td>
					<td class="tbl_summary_td_xcenter">&nbsp;</td>
					<td class="tbl_summary_td_xcenter">&nbsp;</td>
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
			<td colspan="4">Printed: <?php echo date("d-m-Y H:i:s");?></td>
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