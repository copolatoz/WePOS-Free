<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.$date_from).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 1270;
$total_cols = 12;
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
				<td class="tbl_head_td_first_xcenter" rowspan="2" width="50">NO</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="90">PO DATE</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="80">PO NO.</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="160">SUPPLIER</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="150">INVOICE</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="80">TOTAL BARANG</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="80">TOTAL QTY</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="90">DISCOUNT</td>
				<td class="tbl_head_td_xcenter" rowspan="2" width="90">TAX</td>			
				<td class="tbl_head_td_xcenter" colspan="2">TOTAL PAYMENT</td>
				<td class="tbl_head_td" rowspan="2" width="200">NOTES</td>
			</tr>
			<tr class="tbl-header">						
				<td class="tbl_head_td_xcenter" width="100">CASH</td>		
				<td class="tbl_head_td_xcenter" width="100">CREDIT</td>
			</tr>
		
		</thead>
		<tbody>
			<?php
			if(!empty($report_data)){
			
				$no = 1;
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
						<td class="tbl_data_td_xcenter"><?php echo $det['po_date']; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $det['po_number']; ?></td>
						<td class="tbl_data_td"><?php echo $det['supplier_name']; ?></td>
						<td class="tbl_data_td"><?php echo $det['supplier_invoice']; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo priceFormat($det['total_item']); ?></td>
						<td class="tbl_data_td_xcenter"><?php echo priceFormat($det['total_qty']); ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo $det['po_discount_text']; ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo $det['po_tax_text']; ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo $det['po_total_price_cash_text']; ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo $det['po_total_price_credit_text']; ?></td>
						<td class="tbl_data_td"><?php echo $det['po_memo']; ?></td>
						
					</tr>
					<?php	
										
					$total_item += $det['total_item'];
					$total_qty += $det['total_qty'];
					$total_discount +=  $det['po_discount'];
					$total_tax +=  $det['po_tax'];
					$grand_total_cash +=  $det['po_total_price_cash'];
					$grand_total_credit +=  $det['po_total_price_credit'];
					
					$no++;
				}
				
				?>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="5">TOTAL</td>
					<td class="tbl_summary_td_xcenter"><?php echo $total_item; ?></td>
					<td class="tbl_summary_td_xcenter"><?php echo priceFormat($total_qty); ?></td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_discount); ?></td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_tax); ?></td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($grand_total_cash); ?></td>	
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($grand_total_credit); ?></td>					
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
				<td colspan="<?php echo $total_cols; ?>" class="first xleft">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3">Printed: <?php echo date("d-m-Y H:i:s");?></td>
				<td colspan="4">&nbsp;</td>
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