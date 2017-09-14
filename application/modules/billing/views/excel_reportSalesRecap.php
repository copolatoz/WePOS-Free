<?php
header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.$date_from.' to '.$date_till).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 1330;
$total_cols = 13;

$payment_data_content = '';
if(!empty($payment_data)){
	foreach($payment_data as $key_id => $dtPay){
		$payment_data_content .= '<td class="tbl_head_td_xcenter" width="100">'.$dtPay.'</td>';
		
		$total_cols++;
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
				<td class="tbl_head_td_xcenter" width="130" rowspan="2">DATE</td>
				<td class="tbl_head_td_xcenter" width="80" rowspan="2">QTY BILLING</td>
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
				$grand_discount_total = 0;
				$grand_discount_billing_total = 0;
				$grand_total_dp = 0;
				$grand_total_compliment = 0;
				foreach($report_data as $det){
					?>
					<tr>
						<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
						<td class="tbl_data_td">&nbsp;<?php echo date("Y-m-d", strtotime($det['date'])); ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $det['qty_billing']; ?></td>
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
								?>
								<td class="tbl_data_td_xright">Rp. <?php echo $det['total_payment_'.$key_id.'_show']; ?></td>
								<?php
								if(empty($grand_total_payment[$key_id])){
									$grand_total_payment[$key_id] = 0;
								}
								
								$grand_total_payment[$key_id] += $det['total_payment_'.$key_id];
							}
						}
						?>
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
					$grand_discount_total +=  $det['discount_total'];
					$grand_discount_billing_total +=  $det['discount_billing_total'];
					$grand_total_dp +=  $det['total_dp'];
					$grand_total_compliment +=  $det['total_compliment'];
					$no++;
				}
				
				?>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="<?php echo 2; ?>">TOTAL</td>
					<td class="tbl_summary_td_xcenter"><?php echo $total_qty; ?></td>
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
					foreach($grand_total_payment as $dt){
						?>
						<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($dt); ?></td>
						<?php 
					}
					
					
					?>
					
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