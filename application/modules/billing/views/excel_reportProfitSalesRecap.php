<?php
$date_title = $date_from;
if($date_from != $date_till){
	$date_title = $date_from.' to '.$date_till;
}
header("Content-Type:   application/excel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.$date_title).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 900;
$total_cols = 9;

//update-0120.001
if(!empty($filter_column)){
	extract($filter_column);
}
if($show_compliment == false){
	$set_width -= 100;
	$total_cols -= 1;
}
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
					
						<div class="title_report"><?php echo $report_name; ?></div>
						<div class="subtitle_report" style="margin-bottom:5px;">
						<?php
						if($date_from == $date_till){
							echo 'Tanggal : '.$date_from;
						}else{
							echo 'Tanggal : '.$date_from.' s/d '.$date_till;
						}
						if(!empty($user_shift)){ 
							echo ' &nbsp; | &nbsp; Shift: '.$user_shift;
						}else{
							echo ' &nbsp; | &nbsp; Shift: Semua Shift';
						}
						if(!empty($user_kasir)){ 
							echo ' &nbsp; | &nbsp; Kasir: '.$user_kasir;
						}else{
							echo ' &nbsp; | &nbsp; Kasir: Semua Kasir';
						}
						if(!empty($tipe_sales)){ 
							echo ' &nbsp; | &nbsp; Tipe Sales: '.$tipe_sales;
						}
						?>
						</div>
					</div>
				</td>
			</tr>
			<tr>
				<td class="tbl_head_td_first_xcenter" width="50">NO</td>
				<td class="tbl_head_td_xcenter" width="130">DATE</td>
				<td class="tbl_head_td_xcenter" width="80">QTY BILLING</td>
				<td class="tbl_head_td_xcenter" width="120">TOTAL HPP</td>
				<td class="tbl_head_td_xcenter" width="100">TOTAL BILLING</td>
				<td class="tbl_head_td_xcenter" width="100">DISCOUNT</td>
				<?php
				if($show_compliment == true){
				?>
				<td class="tbl_head_td_xcenter" width="100">COMPLIMENT</td>
				<?php
				}
				?>
				<td class="tbl_head_td_xcenter" width="100">NET SALES</td>
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
				$grand_net_sales_total = 0;
				$grand_total_pembulatan = 0;			
				$grand_total_payment = array();
				$grand_discount_total = 0;
				$grand_discount_billing_total = 0;
				$grand_total_dp = 0;
				$grand_total_compliment = 0;
				
				foreach($report_data as $det){
					$discount_total = $det['discount_total']+$det['discount_billing_total'];
					?>
					<tr>
						<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
						<td class="tbl_data_td">&nbsp;<?php echo date("d-m-Y", strtotime($det['date'])); ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $det['qty_billing']; ?></td>
						<?php
						if($format_nominal == true){
							$det['total_billing_show'] = 'Rp. '.$det['total_billing_show'];
							$discount_total = 'Rp. '.priceFormat($discount_total);
							$det['total_billing_profit_show'] = 'Rp. '.$det['total_billing_profit_show'];
							$det['total_hpp_show'] = 'Rp. '.$det['total_hpp_show'];
							$det['total_profit_show'] = 'Rp. '.$det['total_profit_show'];
							$det['total_compliment_show'] = 'Rp. '.$det['total_compliment_show'];
						}else{
							$det['total_billing_show'] = str_replace(".","",$det['total_billing_show']);
							$det['total_billing_show'] = str_replace(",",".",$det['total_billing_show']);
							$det['total_billing_profit_show'] = str_replace(".","",$det['total_billing_profit_show']);
							$det['total_billing_profit_show'] = str_replace(",",".",$det['total_billing_profit_show']);
							$det['total_hpp_show'] = str_replace(".","",$det['total_hpp_show']);
							$det['total_hpp_show'] = str_replace(",",".",$det['total_hpp_show']);
							$det['total_profit_show'] = str_replace(".","",$det['total_profit_show']);
							$det['total_profit_show'] = str_replace(",",".",$det['total_profit_show']);
							$det['total_compliment_show'] = str_replace(".","",$det['total_compliment_show']);
							$det['total_compliment_show'] = str_replace(",",".",$det['total_compliment_show']);
						}
						?>
						<td class="tbl_data_td_xright"><?php echo $det['total_hpp_show']; ?></td>
						<td class="tbl_data_td_xright"><?php echo $det['total_billing_show']; ?></td>
						<td class="tbl_data_td_xright"><?php echo $discount_total; ?></td>
						<?php
						if($show_compliment == true){
						?>
						<td class="tbl_data_td_xright"><?php echo $det['total_compliment_show']; ?></td>
						<?php
						}
						?>
						<td class="tbl_data_td_xright"><?php echo $det['net_sales_total_show']; ?></td>
						<td class="tbl_data_td_xright"><?php echo $det['total_profit_show']; ?></td>
					</tr>
					<?php	
					
					$total_qty +=  $det['qty_billing'];
					$total_billing +=  $det['total_billing'];
					$total_tax +=  $det['tax_total'];
					$total_service +=  $det['service_total'];
					$grand_total +=  $det['total_billing_profit'];
					//$grand_total_cash +=  $det['total_cash'];
					//$grand_total_credit +=  $det['total_credit'];
					$grand_sub_total += $det['sub_total'];
					$grand_net_sales_total += $det['net_sales_total'];
					$grand_total_pembulatan += $det['total_pembulatan'];
					$grand_discount_total += $det['discount_total'];
					$grand_discount_billing_total += $det['discount_billing_total'];
					$grand_total_dp +=  $det['total_dp'];
					$grand_total_compliment +=  $det['total_compliment'];
					$total_hpp +=  $det['total_hpp'];
					$total_profit +=  $det['total_profit'];
					$no++;
				}
				
				$discount_total = $grand_discount_total+$grand_discount_billing_total;
				
				if($format_nominal == true){ 
					$total_qty =  priceFormat($total_qty);
					$discount_total = 'Rp. '.priceFormat($discount_total);
					$total_billing = 'Rp. '.priceFormat($total_billing);
					$grand_total_compliment = 'Rp. '.priceFormat($grand_total_compliment);
					$grand_total = 'Rp. '.priceFormat($grand_total);
					
					$total_hpp = 'Rp. '.priceFormat($total_hpp);
					$total_profit = 'Rp. '.priceFormat($total_profit);
				}
				?>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="2">TOTAL</td>
					<td class="tbl_summary_td_xcenter"><?php echo $total_qty; ?></td>
					<td class="tbl_summary_td_xright"><?php echo $total_hpp; ?></td>
					<td class="tbl_summary_td_xright"><?php echo $total_billing; ?></td>
					<td class="tbl_summary_td_xright"><?php echo $discount_total; ?></td>
					<?php
					if($show_compliment == true){
					?>
					<td class="tbl_summary_td_xright"><?php echo $grand_total_compliment; ?></td>
					<?php
					}
					?>
					<td class="tbl_summary_td_xright"><?php echo $grand_net_sales_total; ?></td>
					<td class="tbl_summary_td_xright"><?php echo $total_profit; ?></td>
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
			<td colspan="<?php echo $total_cols-7; ?>" class="xcenter">&nbsp;</td>
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