<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.$date_from).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 820;
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
				<td class="tbl_head_td_first_xcenter" rowspan="2" width="40">NO</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="100">DATE</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="80">TOTAL PO</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="80">TOTAL BARANG</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="80">TOTAL QTY</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="110">TOTAL DISCOUNT</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="110">TOTAL TAX</td>			
				<td class="tbl_head_td_xcenter" colspan="2">TOTAL PAYMENT</td>
			</tr>
			<tr>						
				<td class="tbl_head_td_xcenter" width="110">CASH</td>		
				<td class="tbl_head_td_xcenter" width="110">CREDIT</td>
			</tr>
		
		</thead>
		<tbody>
			<?php
			if(!empty($report_data)){
			
				$no = 1;
				$total_po = 0;
				$total_item = 0;
				$total_qty = 0;
				$total_discount = 0;
				$total_tax = 0;
				$grand_total_cash = 0;
				$grand_total_credit = 0;
				foreach($report_data as $det){

					?>
					<tr>
						<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $det['date']; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $det['total_po']; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo priceFormat($det['total_item']); ?></td>
						<td class="tbl_data_td_xcenter"><?php echo priceFormat($det['total_qty']); ?></td>
						<td class="tbl_data_td_xright"><?php echo priceFormat($det['total_discount']); ?></td>
						<td class="tbl_data_td_xright"><?php echo priceFormat($det['total_tax']); ?></td>
						<td class="tbl_data_td_xright"><?php echo priceFormat($det['total_cash']); ?></td>
						<td class="tbl_data_td_xright"><?php echo priceFormat($det['total_credit']); ?></td>						
					</tr>
					<?php	

					$total_po += $det['total_po'];
					$total_item += $det['total_item'];
					$total_qty += $det['total_qty'];
					$total_discount += $det['total_discount'];
					$total_tax +=  $det['total_tax'];
					$grand_total_cash +=  $det['total_cash'];
					$grand_total_credit +=  $det['total_credit'];
					
					$no++;
				}
				
				?>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="2">TOTAL</td>
					<td class="tbl_summary_td_xcenter"><?php echo $total_po; ?></td>
					<td class="tbl_summary_td_xcenter"><?php echo $total_item; ?></td>
					<td class="tbl_summary_td_xcenter"><?php echo priceFormat($total_qty); ?></td>
					<td class="tbl_summary_td_xright"><?php echo priceFormat($total_discount); ?></td>
					<td class="tbl_summary_td_xright"><?php echo priceFormat($total_tax); ?></td>
					<td class="tbl_summary_td_xright"><?php echo priceFormat($grand_total_cash); ?></td>	
					<td class="tbl_summary_td_xright"><?php echo priceFormat($grand_total_credit); ?></td>
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
				<td colspan="<?php echo $total_cols; ?>" class="first xleft">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3">Printed: <?php echo date("d-m-Y H:i:s");?></td>
				<td>&nbsp;</td>
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