<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.' '.$date_from).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 1050;
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
					
					<div class="title_report_xcenter"><?php echo $report_name;?></div>
					<?php 
					if(!empty($user_shift)){ 
						?>
						<div class="subtitle_report_xcenter"><?php echo 'Shift: '.$user_shift;?></div>		
						<?php 				
					}else{
						?>
						<div class="subtitle_report_xcenter"><?php echo 'Shift: All Shift';?></div>		
						<?php 
						$total_cols++;
					}
					?>
					<div class="subtitle_report_xcenter"><?php echo 'Period : '.$date_from;?></div>
					
				</td>
			</tr>
			<tr>
				<td class="tbl_head_td_first_xcenter" width="40">NO</td>
				<td class="tbl_head_td_xcenter" width="130">PAYMENT DATE</td>
				<td class="tbl_head_td_xcenter" width="80">BILLING NO.</td>
				<td class="tbl_head_td_xcenter" width="110">TOTAL BILLING</td>
				<td class="tbl_head_td_xcenter" width="110">DISCOUNT</td>
				<td class="tbl_head_td_xcenter" width="110">COMPLIMENT</td>
				<td class="tbl_head_td_xcenter" width="110">TOTAL HPP</td>
				<td class="tbl_head_td_xcenter" width="110">TOTAL PROFIT</td>
				<td class="tbl_head_td_xcenter" width="150">NOTE</td>
				<td class="tbl_head_td_xcenter" width="100">CASHIER</td>
			</tr>
		</thead>
		<tbody>
		<?php
		if(!empty($report_data)){
		
			$no = 1;
			$total_billing = 0;
			$total_hpp = 0;
			$total_profit = 0;
			$total_tax = 0;
			$total_service = 0;
			$grand_total = 0;
			$grand_discount_total = 0;
			$grand_total_compliment = 0;
			$grand_total_payment = array();
			foreach($report_data as $det){
				?>
				<tr>
					<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
					<td class="tbl_data_td_xcenter"><?php echo $det['payment_date']; ?></td>
					<td class="tbl_data_td_xcenter"><?php echo $det['billing_no']; ?></td>
					<td class="tbl_data_td_xright">Rp. <?php echo $det['total_billing_show']; ?></td>
					<td class="tbl_data_td_xright">Rp. <?php echo $det['discount_total_show']; ?></td>
					<td class="tbl_data_td_xright">Rp. <?php echo $det['total_compliment_show']; ?></td>
					<td class="tbl_data_td_xright">Rp. ?php echo $det['total_hpp_show']; ?></td>
					<td class="tbl_data_td_xright">Rp. <?php echo $det['total_profit_show']; ?></td>
					<td class="tbl_data_td"><?php echo $det['payment_note']; ?></td>
					<td class="tbl_data_td"><?php echo $det['user_fullname']; ?></td>
					
				</tr>
				<?php	
				
				$total_billing +=  $det['total_billing'];
				$total_hpp +=  $det['total_hpp'];
				$total_profit +=  $det['total_profit'];
				$total_tax +=  $det['tax_total'];
				$total_service +=  $det['service_total'];
				$grand_total +=  $det['grand_total'];
				$grand_total_compliment += $det['total_compliment'];
				$grand_discount_total += $det['discount_total'];
					
				$no++;
			}
			
			?>
			<tr>
				<td class="tbl_summary_td_first_xright" colspan="<?php echo 3; ?>">TOTAL</td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_billing); ?></td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($grand_discount_total); ?></td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($grand_total_compliment); ?></td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_hpp); ?></td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_profit); ?></td>
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