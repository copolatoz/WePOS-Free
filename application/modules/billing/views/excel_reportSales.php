<?php
header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.$date_from.' to '.$date_till).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 1540;
$total_cols = 14;

$payment_data_content = '';
if(!empty($payment_data)){
	foreach($payment_data as $key_id => $dtPay){
		$payment_data_content .= '<td class="tbl_head_td_xcenter" width="100">'.$dtPay.'</td>';
		
		$total_cols ++;
		$set_width += 100;
	}
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
					
						<div class="title_report_xcenter"><?php echo $report_name;?></div>		
						<div class="subtitle_report_xcenter"><?php echo 'Period : '.$date_from.' TO '.$date_till;?></div>		
						
					</div>
				</td>
			</tr>
			<tr>
				<td class="tbl_head_td_first_xcenter" width="50" rowspan="2">NO</td>
				<td class="tbl_head_td_xcenter" width="130" rowspan="2">PAYMENT DATE</td>
				<td class="tbl_head_td_xcenter" width="80" rowspan="2">BILLING NO.</td>
				<td class="tbl_head_td_xcenter" width="120" rowspan="2">TOTAL BILLING</td>
				<?php
				if($diskon_sebelum_pajak_service == 1){
					?>
					<td class="tbl_head_td_xcenter" width="220" colspan="2">DISCOUNT</td>	
					<?php
				}
				?>
				<td class="tbl_head_td_xcenter" width="100" rowspan="2">TAX</td>
				<td class="tbl_head_td_xcenter" width="100" rowspan="2">SERVICE</td>
				<td class="tbl_head_td_xcenter" width="100" rowspan="2">SUB TOTAL</td>
				<?php
				if($diskon_sebelum_pajak_service == 0){
					?>
					<td class="tbl_head_td_xcenter" width="220" colspan="2">DISCOUNT</td>	
					<?php
				}
				?>
				<td class="tbl_head_td_xcenter" width="100" rowspan="2">PEMBULATAN</td>
				<td class="tbl_head_td_xcenter" width="100" rowspan="2">COMPLIMENT</td>
				<td class="tbl_head_td_xcenter" width="120" rowspan="2">GRAND TOTAL</td>
				<td class="tbl_head_td_xcenter" width="100" rowspan="2">DP</td>
				<td class="tbl_head_td_xcenter" width="100" colspan="<?php echo count($payment_data); ?>">PAYMENT</td>	
				<td class="tbl_head_td_xcenter" width="200" rowspan="2">NOTE</td>
			</tr>
			<tr>
				
				<?php
				if($diskon_sebelum_pajak_service == 1){
					?>
					<td class="tbl_head_td_xcenter" width="110">ITEM</td>
					<td class="tbl_head_td_xcenter" width="110">BILLING</td>
					<?php
				}
				
				if($diskon_sebelum_pajak_service == 0){
					?>
					<td class="tbl_head_td_xcenter" width="110">ITEM</td>
					<td class="tbl_head_td_xcenter" width="110">BILLING</td>
					<?php
				}
				
				echo $payment_data_content;
				?>
				
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
				$grand_total_dp = 0;
				$grand_sub_total = 0;
				$grand_total_pembulatan = 0;
				$grand_discount_total = 0;
				$grand_discount_billing_total = 0;
				$grand_total_compliment = 0;
				$grand_total_payment = array();
				foreach($report_data as $det){
						
						if(!empty($only_txmark)){
							$det['billing_no'] = $det['txmark_no'];
						}
					?>
					<tr>
						<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
						<td class="tbl_data_td">&nbsp;<?php echo date("Y-m-d", strtotime($det['payment_date'])); ?></td>
						<td class="tbl_data_td"><?php echo $det['billing_no']; ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo $det['total_billing_show']; ?></td>
						<?php
						if($diskon_sebelum_pajak_service == 1){
							?>
							<td class="tbl_data_td_xright">Rp. <?php echo $det['discount_total_show']; ?></td>
							<td class="tbl_data_td_xright">Rp. <?php echo $det['discount_billing_total_show']; ?></td>
							<?php
						}
						?>
						<td class="tbl_data_td_xright">Rp. <?php echo $det['tax_total_show']; ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo $det['service_total_show']; ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo $det['sub_total_show']; ?></td>
						<?php
						if($diskon_sebelum_pajak_service == 0){
							?>
							<td class="tbl_data_td_xright">Rp. <?php echo $det['discount_total_show']; ?></td>
							<td class="tbl_data_td_xright">Rp. <?php echo $det['discount_billing_total_show']; ?></td>
							<?php
						}
						?>
						<td class="tbl_data_td_xright">Rp. <?php echo $det['total_pembulatan_show']; ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo $det['total_compliment_show']; ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo $det['grand_total_show']; ?></td>
						<td class="tbl_data_td_xright">Rp. <?php echo $det['total_dp_show']; ?></td>
						<?php
						if(!empty($payment_data)){
							foreach($payment_data as $key_id => $dtPay){
								
								$tot_payment = 0;
								$tot_payment_show = 0;
								if($det['payment_id'] == $key_id){
									
									//$tot_payment = $det['grand_total'];	
									//$tot_payment_show = $det['grand_total_show'];	
									
									if($key_id == 2 OR $key_id == 3 OR $key_id == 4){
										$tot_payment = $det['total_credit'];	
									}else{
										$tot_payment = $det['total_cash'];	
									}
									
									//FIX PEMBULATAN
									/*if($tot_payment < $det['grand_total']){
										$gap = ($det['grand_total'] - $det['total_dp']) - $tot_payment;
										if($gap < 100){
											//$tot_payment += $det['total_pembulatan'];
										}
									}*/
										
									if($tot_payment <= 0){
										$tot_payment = 0;
									}
									
									$tot_payment_show = priceFormat($tot_payment);
									
									//credit half payment
									if(!empty($det['is_half_payment']) AND $key_id != 1){
										$tot_payment = $det['total_credit'];
										$tot_payment_show = priceFormat($tot_payment);
									}else{
										
										/*
										if($tot_payment <= $det['grand_total']){
											//$tot_payment_show .= '='.$tot_payment.'x'.$det['grand_total'];
											$tot_payment = $det['grand_total'];
											$tot_payment_show = priceFormat($tot_payment);
											
											if(!empty($det['discount_total'])){
												$tot_payment = $tot_payment - $det['discount_total'];
												$tot_payment_show = priceFormat($tot_payment);
											}
											if(!empty($det['total_dp'])){
												$tot_payment = $tot_payment - $det['total_dp'];
												$tot_payment_show = priceFormat($tot_payment);
											}
										}else{
											//$tot_payment = $det['grand_total'];
											$tot_payment_show = priceFormat($tot_payment);								
										}
										*/
										
										$tot_payment_show = priceFormat($tot_payment);	
									}
									
								}else{
									//cash
									if(!empty($det['is_half_payment']) AND $key_id == 1){
										$tot_payment = $det['total_cash'];
										$tot_payment_show = priceFormat($tot_payment);
									}
								}
								
								
								if(empty($grand_total_payment[$key_id])){
									$grand_total_payment[$key_id] = 0;
								}									
								
								if(!empty($det['is_compliment'])){
									$tot_payment = 0;
									$tot_payment_show = 0;
								}
								
								if(!empty($det['discount_total']) AND !empty($tot_payment)){
									//$tot_payment = $tot_payment - $det['discount_total'];
									//$tot_payment_show = priceFormat($tot_payment);
								}
								
								$grand_total_payment[$key_id] += $tot_payment;
								?>
								<td class="tbl_data_td_xright">Rp. <?php echo $tot_payment_show; ?></td>
								<?php
																
							}
						}
						?>
						<td class="tbl_data_td"><?php echo $det['payment_note']; ?></td>
					</tr>
					<?php	
					
					$total_billing +=  $det['total_billing'];
					$total_tax +=  $det['tax_total'];
					$total_service +=  $det['service_total'];
					$grand_total +=  $det['grand_total'];
					$grand_total_compliment += $det['total_compliment'];
					$grand_sub_total += $det['sub_total'];
					$grand_total_pembulatan += $det['total_pembulatan'];
					$grand_discount_total += $det['discount_total'];
					$grand_discount_billing_total += $det['discount_billing_total'];
					$grand_total_dp += $det['total_dp'];
					$no++;
				}
				
				?>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="<?php echo 3; ?>">TOTAL</td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_billing); ?></td>
					<?php
					if($diskon_sebelum_pajak_service == 1){
						?>
						<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($grand_discount_total); ?></td>
						<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($grand_discount_billing_total); ?></td>
						<?php
					}
					?>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_tax); ?></td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_service); ?></td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($grand_sub_total); ?></td>
					<?php
					if($diskon_sebelum_pajak_service == 0){
						?>
						<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($grand_discount_total); ?></td>
						<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($grand_discount_billing_total); ?></td>
						<?php
					}
					?>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($grand_total_pembulatan); ?></td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($grand_total_compliment); ?></td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($grand_total); ?></td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($grand_total_dp); ?></td>
					
					<?php
					if(!empty($payment_data)){
						foreach($payment_data as $key_id => $dtPay){
							
							$total = 0;
							if(!empty($grand_total_payment[$key_id])){
								$total = priceFormat($grand_total_payment[$key_id]);
							}							
							?>
							<td class="tbl_summary_td_xright">Rp. <?php echo $total; ?></td>
							<?php
						}
					}
					
					?>
					
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
	</table>
</div>
</body>
</html>