<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.$date_from.' to '.$date_till).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 670;
$total_cols = 6;

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
				<td class="tbl_head_td_xcenter" width="80">QTY BILLING</td>
				<td class="tbl_head_td_xcenter" width="100">TOTAL BILLING</td>
				<td class="tbl_head_td_xcenter" width="120">TOTAL HPP</td>
				<td class="tbl_head_td_xcenter" width="120">TOTAL PROFIT</td>
			</tr>
		</thead>
		<tbody>
		<?php
		
			if(!empty($report_data)){
			
				$no = 1;
				$total_hpp = 0;
				$total_profit = 0;
				$total_qty = 0;
				$total_billing = 0;
				$total_tax = 0;
				$total_service = 0;
				$grand_total = 0;
				//$grand_total_cash = 0;
				//$grand_total_credit = 0;	
				$grand_sub_total = 0;
				$grand_total_pembulatan = 0;			
				$grand_total_payment = array();
				$grand_total_discount = 0;
				$grand_total_dp = 0;
				$grand_total_compliment = 0;
				foreach($report_data as $det){
					?>
					<tr>
						<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
						<td class="tbl_data_td"><?php echo date("Y-m-d", strtotime($det['date'])); ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $det['qty_billing']; ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo $det['total_billing_show']; ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo $det['total_hpp']; ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo $det['total_profit']; ?></td>
					</tr>
					<?php	
					
					$total_qty +=  $det['qty_billing'];
					$total_billing +=  $det['total_billing'];
					$total_tax +=  $det['tax_total'];
					$total_service +=  $det['service_total'];
					$grand_total +=  $det['grand_total'];
					//$grand_total_cash +=  $det['total_cash'];
					//$grand_total_credit +=  $det['total_credit'];
					$grand_sub_total += $det['sub_total'];
					$grand_total_pembulatan += $det['total_pembulatan'];
					$grand_total_discount +=  $det['discount_total'];
					$grand_total_dp +=  $det['total_dp'];
					$grand_total_compliment +=  $det['total_compliment'];
					$total_hpp +=  $det['total_hpp'];
					$total_profit +=  $det['total_profit'];
					$no++;
				}
				
				?>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="<?php echo 2; ?>">TOTAL</td>
					<td class="tbl_summary_td_xcenter"><?php echo $total_qty; ?></td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_billing); ?></td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_hpp); ?></td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_profit); ?></td>
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
		</tbody>	
	</table>
</div>
</body>
</html>