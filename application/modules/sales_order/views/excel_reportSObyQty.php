<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.$date_from.' to '.$date_till).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 1290;
$total_cols = 12;
?>
<html>
<body>
<style>
	<?php include ASSETS_PATH."desktop/css/report.css.php"; ?>
</style>
<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
	<br/>
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
				<td class="tbl_head_td_first_xcenter" width="40" rowspan="2">NO</td>
				<td class="tbl_head_td_xcenter" width="110" rowspan="2">KODE</td>
				<td class="tbl_head_td_xcenter" width="260" rowspan="2">NAMA BARANG</td>
				<td class="tbl_head_td_xcenter" width="60" rowspan="2">TOTAL QTY</td>
				<td class="tbl_head_td_xcenter" width="110" rowspan="2">TOTAL SALES</td>
				<td class="tbl_head_td_xcenter" width="220" rowspan="2">DISCOUNT</td>
				<td class="tbl_head_td_xcenter" width="100" rowspan="2">SUB TOTAL</td>
				<td class="tbl_head_td_xcenter" width="90" rowspan="2">TAX</td>
				<td class="tbl_head_td_xcenter" width="90" rowspan="2">SHIPPING</td>
				<td class="tbl_head_td_xcenter" width="90" rowspan="2">DP</td>
				<td class="tbl_head_td_xcenter" width="200" colspan="2">PAYMENT</td>	
			</tr>
			<tr>
				<td class="tbl_head_td_xcenter" width="100">CASH</td>
				<td class="tbl_head_td_xcenter" width="100">CREDIT</td>
			</tr>
		</thead>
		<tbody>
		<?php
		if(!empty($report_data)){
		
			$no = 1;
			$total_qty = 0;
			$total_sales = 0;
			$total_discount = 0;
			$sub_total = 0;
			$sub_total_cash = 0;
			$sub_total_credit = 0;
			$total_tax = 0;
			$total_shipping = 0;
			$total_dp = 0;
			
			foreach($report_data as $det){
				
				if(empty($det['item_name'])){
					$det['item_name'] = '#'.$det['item_id'].' deleted';
				}
					
				if(empty($det['item_code'])){
					$det['item_code'] = 'N/A';
				}
				
				?>
				<tr>
					<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
					<td class="tbl_data_td"><?php echo $det['item_code']; ?></td>
					<td class="tbl_data_td"><?php echo $det['item_name']; ?></td>
					<td class="tbl_data_td_xcenter"><?php echo $det['total_qty']; ?></td>
					<td class="tbl_data_td_xright">Rp. <?php echo $det['total_sales_show']; ?></td>
					<td class="tbl_data_td_xright">Rp. <?php echo $det['total_discount_show']; ?></td>
					<td class="tbl_data_td_xright">Rp. <?php echo $det['sub_total_show']; ?></td>
					<td class="tbl_data_td_xright">Rp. <?php echo $det['total_tax_show']; ?></td>
					<td class="tbl_data_td_xright">Rp. <?php echo $det['total_shipping_show']; ?></td>
					<td class="tbl_data_td_xright">Rp. <?php echo $det['total_dp_show']; ?></td>
					<td class="tbl_data_td_xright">Rp. <?php echo $det['sub_total_cash_show']; ?></td>
					<td class="tbl_data_td_xright">Rp. <?php echo $det['sub_total_credit_show']; ?></td>
					
				</tr>
				<?php	
				
				$total_qty +=  $det['total_qty'];
				$total_sales +=  $det['total_sales'];
				$total_discount +=  $det['total_discount'];
				$sub_total +=  $det['sub_total'];
				$sub_total_cash +=  $det['sub_total_cash'];
				$sub_total_credit +=  $det['sub_total_credit'];
				$total_tax +=  $det['total_tax'];
				$total_shipping +=  $det['total_shipping'];
				$total_dp +=  $det['total_dp'];
				$no++;
			}
			
			?>
			<tr>
				<td class="tbl_summary_td_first_xright" colspan="<?php echo 3; ?>">TOTAL</td>
				<td class="tbl_summary_td_xcenter"><?php echo priceFormat($total_qty); ?></td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_sales); ?></td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_discount); ?></td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($sub_total); ?></td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_tax); ?></td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_shipping); ?></td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_dp); ?></td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($sub_total_cash); ?></td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($sub_total_credit); ?></td>
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
			<td colspan="2" class="xcenter">&nbsp;</td>
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