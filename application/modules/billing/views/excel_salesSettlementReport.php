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

$set_width = 400;
$total_cols = 2;

$set_width += $total_day*120;
$total_cols += $total_day;

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
					
						<div class="title_report"><?php echo $this->session->userdata('client_name'); ?></div>
						<div class="title_report"><?php echo $report_name;?></div>
						<div class="subtitle_report" style="margin-bottom:5px;">
						<?php
						if($date_from == $date_till){
							echo 'Tanggal : '.$date_from;
						}else{
							echo 'Tanggal : '.$date_from.' s/d '.$date_till; 
						}
						?>			
						</div>
					</div>
				</td>
			</tr>
		</thead>
		<tbody>
			
			<tr>
				<td class="tbl_head_td_first_xcenter" width="50">NO</td>
				<td class="tbl_head_td_xcenter" width="350">SALES BY CATEGORY</td>
				<?php
				if(!empty($total_day)){
					for($i=1; $i<=$total_day; $i++){
						$add_mk = ($i-1) * ONE_DAY_UNIX;
						$new_date = date("d/m/Y", ($mk_date_from+$add_mk));
						?>
						<td class="tbl_head_td_xcenter" width="110">&nbsp;<?php echo $new_date; ?></td>
						<?php
					}
				}
				?>
			</tr>
			<?php
			$total_billing_perday = array();
			$total_discount_peritem_perday = array();
			$total_discount_peritem_perday_before = array();
			$total_discount_peritem_perday_after = array();
			$total_all_sales_perday = array();
			$total_discount_billing_perday = array();
			$total_discount_billing_perday_before = array();
			$total_discount_billing_perday_after = array();
			$total_net_sales_perday = array();
			$total_tax_perday = array();
			$total_service_perday = array();
			$sub_total_perday = array();
			$net_after_taxservice_perday = array();
			$pembulatan_perday = array();
			$compliment_perday = array();
			$grand_total_perday = array();
			$total_qty_perday = array();
			if(!empty($report_data)){
			
				$nox = 1;
				
				foreach($report_data as $key => $dtDet){
					
					if(empty($key)){
						$key = 'Products Deleted';
					}
					
					$key = strtoupper($key);
					
					?>
					<tr>
						<td class="tbl_data_td_first_xcenter"><?php echo $nox; ?></td>
						<td class="tbl_data_td"><?php echo $key; ?></td>
						<?php
						if(!empty($total_day)){
							for($i=1; $i<=$total_day; $i++){
								
								$add_mk = ($i-1) * ONE_DAY_UNIX;
								$new_date = date("d/m/Y", ($mk_date_from+$add_mk));
								
								if(empty($total_billing_perday[$new_date])){
									$total_billing_perday[$new_date] = 0;
								}
								if(empty($total_discount_peritem_perday[$new_date])){
									$total_discount_peritem_perday[$new_date] = 0;
								}
								if(empty($total_discount_peritem_perday_before[$new_date])){
									$total_discount_peritem_perday_before[$new_date] = 0;
								}
								if(empty($total_discount_peritem_perday_after[$new_date])){
									$total_discount_peritem_perday_after[$new_date] = 0;
								}
								if(empty($total_all_sales_perday[$new_date])){
									$total_all_sales_perday[$new_date] = 0;
								}
								if(empty($total_discount_billing_perday[$new_date])){
									$total_discount_billing_perday[$new_date] = 0;
								}
								if(empty($total_discount_billing_perday_before[$new_date])){
									$total_discount_billing_perday_before[$new_date] = 0;
								}
								if(empty($total_discount_billing_perday_after[$new_date])){
									$total_discount_billing_perday_after[$new_date] = 0;
								}
								if(empty($total_net_sales_perday[$new_date])){
									$total_net_sales_perday[$new_date] = 0;
								}
								if(empty($total_tax_perday[$new_date])){
									$total_tax_perday[$new_date] = 0;
								}
								if(empty($total_service_perday[$new_date])){
									$total_service_perday[$new_date] = 0;
								}
								if(empty($sub_total_perday[$new_date])){
									$sub_total_perday[$new_date] = 0;
								}
								if(empty($net_after_taxservice_perday[$new_date])){
									$net_after_taxservice_perday[$new_date] = 0;
								}
								if(empty($pembulatan_perday[$new_date])){
									$pembulatan_perday[$new_date] = 0;
								}
								if(empty($compliment_perday[$new_date])){
									$compliment_perday[$new_date] = 0;
								}
								if(empty($grand_total_perday[$new_date])){
									$grand_total_perday[$new_date] = 0;
								}
								if(empty($total_qty_perday[$new_date])){
									$total_qty_perday[$new_date] = 0;
								}
								
								if(!empty($dtDet[$new_date])){
									
									$total_billing_perday[$new_date] += $dtDet[$new_date]['total_billing'];
									
									$total_discount_peritem_perday[$new_date] += ($dtDet[$new_date]['discount_total']);
									$total_discount_peritem_perday_before[$new_date] += ($dtDet[$new_date]['discount_total_before']);
									$total_discount_peritem_perday_after[$new_date] += ($dtDet[$new_date]['discount_total_after']);
									$total_discount_billing_perday[$new_date] += $dtDet[$new_date]['discount_billing_total'];
									$total_discount_billing_perday_before[$new_date] += $dtDet[$new_date]['discount_billing_total_before'];
									$total_discount_billing_perday_after[$new_date] += $dtDet[$new_date]['discount_billing_total_after'];
									
									$sales_today = ($dtDet[$new_date]['total_billing'] - ($dtDet[$new_date]['discount_total']+$dtDet[$new_date]['discount_billing_total']));
									$total_all_sales_perday[$new_date] += $sales_today;
									
									$net_sales_today = ($dtDet[$new_date]['total_billing'] - ($dtDet[$new_date]['discount_total_before']+$dtDet[$new_date]['discount_billing_total_before']));
									$total_net_sales_perday[$new_date] += $net_sales_today;
									
									$total_tax_perday[$new_date] += $dtDet[$new_date]['tax_total'];
									$total_service_perday[$new_date] += $dtDet[$new_date]['service_total'];
									$sub_total_perday[$new_date] += $dtDet[$new_date]['sub_total'];
									
									$net_after_taxservice_perday[$new_date] += $net_sales_today+($dtDet[$new_date]['tax_total']+$dtDet[$new_date]['service_total']);
									$pembulatan_perday[$new_date] += $dtDet[$new_date]['total_pembulatan'];
									$compliment_perday[$new_date] += $dtDet[$new_date]['compliment_total'];
									$grand_total_perday[$new_date] += $dtDet[$new_date]['grand_total'];
									
									$total_qty_perday[$new_date] += $dtDet[$new_date]['total_qty'];
									?>
									<td class="tbl_data_td_xright"><?php echo priceFormat($dtDet[$new_date]['total_billing'],2,".",""); ?></td>
									<?php
								}else{
									?>
									<td class="tbl_data_td_xright">0</td>
									<?php
								}
								
								
							}
						}
						?>
					</tr>
					<?php
					$no = 1;
					
					$nox++;
					
					
				}
				
				?>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="2">TOTAL CATEGORY</td>
					<?php
					if(!empty($total_billing_perday)){
						foreach($total_billing_perday as $dt){
							?>
							<td class="tbl_summary_td_xright"><?php echo priceFormat($dt,2,".",""); ?></td>
							<?php
						}
					}
					?>
				</tr>
				<?php
				if($diskon_sebelum_pajak_service == 1 OR count($display_discount_type) > 1){
					if(count($display_discount_type) > 1){
						?>
						<tr>
							<td class="tbl_data_td_first" colspan="2">DISCOUNT ITEM/MENU BEFORE TAX-SERVICE</td>
							<?php
							if(!empty($total_discount_peritem_perday_before)){
								foreach($total_discount_peritem_perday_before as $dt){
									?>
									<td class="tbl_data_td_xright"><?php echo priceFormat($dt,2,".",""); ?></td>
									<?php
								}
							}
							?>
						</tr>
						<tr class="tbl-data">
							<td class="tbl_data_td_first" colspan="2">DISCOUNT BILLING BEFORE TAX-SERVICE</td>
							<?php
							if(!empty($total_discount_billing_perday_before)){
								foreach($total_discount_billing_perday_before as $dt){
									?>
									<td class="tbl_data_td_xright"><?php echo priceFormat($dt,2,".",""); ?></td>
									<?php
								}
							}
							?>
						</tr>
						<?php
					}else{
						?>
						<tr>
							<td class="tbl_data_td_first" colspan="2">DISCOUNT ITEM/MENU </td>
							<?php
							if(!empty($total_discount_peritem_perday)){
								foreach($total_discount_peritem_perday as $dt){
									?>
									<td class="tbl_data_td_xright"><?php echo priceFormat($dt,2,".",""); ?></td>
									<?php
								}
							}
							?>
						</tr>
						<tr class="tbl-data">
							<td class="tbl_data_td_first" colspan="2">DISCOUNT BILLING </td>
							<?php
							if(!empty($total_discount_billing_perday)){
								foreach($total_discount_billing_perday as $dt){
									?>
									<td class="tbl_data_td_xright"><?php echo priceFormat($dt,2,".",""); ?></td>
									<?php
								}
							}
							?>
						</tr>
						<?php
					}
				}
				
				if($diskon_sebelum_pajak_service == 0 OR count($display_discount_type) > 1){
					if(count($display_discount_type) > 1){
						?>
						<tr>
							<td class="tbl_data_td_first" colspan="2">DISCOUNT ITEM/MENU AFTER TAX-SERVICE</td>
							<?php
							if(!empty($total_discount_peritem_perday_after)){
								foreach($total_discount_peritem_perday_after as $dt){
									?>
									<td class="tbl_data_td_xright"><?php echo priceFormat($dt,2,".",""); ?></td>
									<?php
								}
							}
							?>
						</tr>
						<tr class="tbl-data">
							<td class="tbl_data_td_first" colspan="2">DISCOUNT BILLING AFTER TAX-SERVICE</td>
							<?php
							if(!empty($total_discount_billing_perday_after)){
								foreach($total_discount_billing_perday_after as $dt){
									?>
									<td class="tbl_data_td_xright"><?php echo priceFormat($dt,2,".",""); ?></td>
									<?php
								}
							}
							?>
						</tr>
						<?php
					}else{
						?>
						<tr>
							<td class="tbl_data_td_first" colspan="2">DISCOUNT ITEM/MENU AFTER TAX-SERVICE</td>
							<?php
							if(!empty($total_discount_peritem_perday)){
								foreach($total_discount_peritem_perday as $dt){
									?>
									<td class="tbl_data_td_xright"><?php echo priceFormat($dt,2,".",""); ?></td>
									<?php
								}
							}
							?>
						</tr>
						<tr class="tbl-data">
							<td class="tbl_data_td_first" colspan="2">DISCOUNT BILLING AFTER TAX-SERVICE</td>
							<?php
							if(!empty($total_discount_billing_perday)){
								foreach($total_discount_billing_perday as $dt){
									?>
									<td class="tbl_data_td_xright"><?php echo priceFormat($dt,2,".",""); ?></td>
									<?php
								}
							}
							?>
						</tr>
						<?php
					}
				}
				?>
				
				<tr>
					<td class="tbl_data_td_first" colspan="2">COMPLIMENT</td>
					<?php
					if(!empty($compliment_perday)){
						foreach($compliment_perday as $dt){
							?>
							<td class="tbl_data_td_xright"><?php echo priceFormat($dt,2,".",""); ?></td>
							<?php
						}
					}
					?>
				</tr>
				<tr>
					<td class="tbl_data_td_first" colspan="2">TOTAL NET SALES </td>
					<?php
					if(!empty($total_net_sales_perday)){
						foreach($total_net_sales_perday as $dt){
							?>
							<td class="tbl_data_td_xright"><?php echo priceFormat($dt,2,".",""); ?></td>
							<?php
						}
					}
					?>
				</tr>
				<tr>
					<td class="tbl_data_td_first" colspan="2">TAX </td>
					<?php
					if(!empty($total_tax_perday)){
						foreach($total_tax_perday as $dt){
							?>
							<td class="tbl_data_td_xright"><?php echo priceFormat($dt,2,".",""); ?></td>
							<?php
						}
					}
					?>
				</tr>
				<tr>
					<td class="tbl_data_td_first" colspan="2">SERVICE </td>
					<?php
					if(!empty($total_service_perday)){
						foreach($total_service_perday as $dt){
							?>
							<td class="tbl_data_td_xright"><?php echo priceFormat($dt,2,".",""); ?></td>
							<?php
						}
					}
					?>
				</tr>
				<tr>
					<td class="tbl_data_td_first" colspan="2">SUB TOTAL</td>
					<?php
					if(!empty($sub_total_perday)){
						foreach($sub_total_perday as $dt){
							?>
							<td class="tbl_data_td_xright"><?php echo priceFormat($dt,2,".",""); ?></td>
							<?php
						}
					}
					?>
				</tr>
				<tr>
					<td class="tbl_data_td_first" colspan="2">PEMBULATAN</td>
					<?php
					if(!empty($pembulatan_perday)){
						foreach($pembulatan_perday as $dt){
							?>
							<td class="tbl_data_td_xright"><?php echo priceFormat($dt,2,".",""); ?></td>
							<?php
						}
					}
					?>
				</tr>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="2">GRAND TOTAL SALES</td>
					<?php
					if(!empty($grand_total_perday)){
						foreach($grand_total_perday as $dt){
							?>
							<td class="tbl_summary_td_xright"><?php echo priceFormat($dt,2,".",""); ?></td>
							<?php
						}
					}
					?>
				</tr>
				<tr>
					<td class="tbl_data_td_first" colspan="<?php echo $total_cols; ?>">&nbsp;</td>
				</tr>
				<tr>
					<td class="tbl_data_td_first" colspan="2">TOTAL QTY</td>
					<?php
					if(!empty($total_qty_perday)){
						foreach($total_qty_perday as $dt){
							?>
							<td class="tbl_data_td_xright"><?php echo priceFormat($dt,2,".",""); ?></td>
							<?php
						}
					}
					?>
				</tr>
				<tr>
					<td class="tbl_data_td_first" colspan="2">TOTAL TRX/BILLING</td>
					<?php
					if(!empty($total_day)){
						for($i=1; $i<=$total_day; $i++){
							
							$add_mk = ($i-1) * ONE_DAY_UNIX;
							$new_date = date("d/m/Y", ($mk_date_from+$add_mk));
						
							if(!empty($total_qty_billing[$new_date])){
								?>
								<td class="tbl_data_td_xright"><?php echo priceFormat(count($total_qty_billing[$new_date]),2,".",""); ?></td>
								<?php
							}else{
								?>
								<td class="tbl_data_td_xright">0</td>
								<?php
							}
							
						}
					}
					?>
				</tr>
				<tr>
					<td class="tbl_data_td_first" colspan="<?php echo $total_cols; ?>">&nbsp;</td>
				</tr>
				<tr>
					<td class="tbl_data_td_first" colspan="2"><b>BY SETTLEMENT</b></td>
					<td class="tbl_data_td_xright" colspan="<?php echo $total_day; ?>">&nbsp;</td>
				</tr>
				<?php
				if(!empty($payment_data)){
					foreach($payment_data as $payment_id => $payment_name){
						?>
						<tr>
							<td class="tbl_data_td_first" colspan="2"> &nbsp; <?php echo $payment_name; ?></td>
							
							<?php
							if(!empty($total_day)){
								for($i=1; $i<=$total_day; $i++){
									
									$add_mk = ($i-1) * ONE_DAY_UNIX;
									$new_date = date("d/m/Y", ($mk_date_from+$add_mk));
								
									if(!empty($payment_perday[$payment_id][$new_date])){
										?>
										<td class="tbl_data_td_xright"><?php echo priceFormat($payment_perday[$payment_id][$new_date],2,".",""); ?></td>
										<?php
									}else{
										?>
										<td class="tbl_data_td_xright">0</td>
										<?php
									}
									
								}
							}
							?>
							
						</tr>
						<?php
					}
				}
				?>
				<tr>
					<td class="tbl_data_td_first" colspan="2"> &nbsp; DP / DOWN-PAYMENT</td>
					<?php
					if(!empty($total_day)){
						for($i=1; $i<=$total_day; $i++){
							
							$add_mk = ($i-1) * ONE_DAY_UNIX;
							$new_date = date("d/m/Y", ($mk_date_from+$add_mk));
						
							if(!empty($total_dp_perday[$new_date])){
								?>
								<td class="tbl_data_td_xright"><?php echo priceFormat($total_dp_perday[$new_date],2,".",""); ?></td>
								<?php
							}else{
								?>
								<td class="tbl_data_td_xright">0</td>
								<?php
							}
							
						}
					}
					?>
				</tr>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="2">SETTLEMENT TOTAL</td>
					<?php
					if(!empty($total_day)){
						for($i=1; $i<=$total_day; $i++){
							
							$add_mk = ($i-1) * ONE_DAY_UNIX;
							$new_date = date("d/m/Y", ($mk_date_from+$add_mk));
						
							if(!empty($total_payment_perday[$new_date])){
								?>
								<td class="tbl_summary_td_xright"><?php echo priceFormat($total_payment_perday[$new_date],2,".",""); ?></td>
								<?php
							}else{
								?>
								<td class="tbl_summary_td_xright">0</td>
								<?php
							}
							
						}
					}
					?>
				</tr>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="2">Selisih Sales dan Settlement</td>
					<?php
					if(!empty($total_day)){
						for($i=1; $i<=$total_day; $i++){
							
							$add_mk = ($i-1) * ONE_DAY_UNIX;
							$new_date = date("d/m/Y", ($mk_date_from+$add_mk));
							
							$grand_total = 0;
							if(!empty($grand_total_perday[$new_date])){
								$grand_total = $grand_total_perday[$new_date];
							}
						
							$payment_total = 0;
							if(!empty($total_payment_perday[$new_date])){
								$payment_total = $total_payment_perday[$new_date];
							}
							
							$selisih_perday = $grand_total - $payment_total;
							?>
							<td class="tbl_summary_td_xright"><?php echo priceFormat($selisih_perday,2,".",""); ?></td>
							<?php
						}
					}
					?>
				</tr>
				<tr>
					<td class="tbl_data_td_first" colspan="<?php echo $total_cols; ?>">&nbsp;</td>
				</tr>
				<tr>
					<td class="tbl_summary_td_first" colspan="2">SETTLEMENT - PAYMENT &amp; BANK</td>
					<?php
					if(!empty($total_day)){
						for($i=1; $i<=$total_day; $i++){
							$add_mk = ($i-1) * ONE_DAY_UNIX;
							$new_date = date("d/m/Y", ($mk_date_from+$add_mk));
							?>
							<td class="tbl_summary_td_xright" width="110">&nbsp;<?php echo $new_date; ?></td>
							<?php
						}
					}
					?>
				</tr>
				<?php
				if(!empty($dt_bank_payment)){
					foreach($dt_bank_payment as $payment_id => $data_bank){
						$payment_name = 'n/a';
						
						if(!empty($payment_data[$payment_id])){
							$payment_name = $payment_data[$payment_id];
						}
						
						?>
						<tr>
							<td class="tbl_data_td_first" colspan="<?php echo 2+$total_day; ?>"> &nbsp; <b><?php echo $payment_name; ?></b></td>
						</tr>
						<?php
						if(!empty($data_bank)){
							foreach($data_bank as $bank_id){
								
								$bank_name = 'n/a';
								if(!empty($dt_bank_name[$bank_id])){
									$bank_name = $dt_bank_name[$bank_id];
								}
								
								?>
								<tr>
									<td class="tbl_data_td_first" colspan="2"> &nbsp; &nbsp; &nbsp; <?php echo $bank_name; ?></td>
									
									<?php
									if(!empty($total_day)){
										for($i=1; $i<=$total_day; $i++){
											
											$add_mk = ($i-1) * ONE_DAY_UNIX;
											$new_date = date("d/m/Y", ($mk_date_from+$add_mk));
										
											if(!empty($bank_perday[$bank_id][$new_date])){
												?>
												<td class="tbl_data_td_xright"><?php echo priceFormat($bank_perday[$bank_id][$new_date],2,".",""); ?></td>
												<?php
											}else{
												?>
												<td class="tbl_data_td_xright">0</td>
												<?php
											}
											
										}
									}
									?>
									
								</tr>
								<?php
							}
						}
						
					}
				}
				
			}else{
			?>
				<tr>
					<td colspan="<?php echo $total_cols; ?>" class="tbl_head_td_first_xcenter">Data Not Found</td>
				</tr>
			<?php
			}
			?>
			<tr>
				<td colspan="<?php echo $total_cols; ?>">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="<?php echo $total_cols; ?>">&nbsp;</td>
			</tr>
		</tbody>
	</table>
</div>
</body>
</html>