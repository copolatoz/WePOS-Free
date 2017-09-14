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
					
					<div class="title_report_xcenter"><?php echo $report_name;?></div>
					<div class="subtitle_report_xcenter"><?php echo 'Period : '.$date_from.' TO '.$date_till;?></div>
					
				</td>
			</tr>
			<tr>
				<td class="tbl_head_td_first_xcenter" width="40">NO</td>
				<td class="tbl_head_td_xcenter" width="260">PRODUCT / MENU</td>
				<td class="tbl_head_td_xcenter" width="60">TOTAL QTY</td>
				<td class="tbl_head_td_xcenter" width="110">TOTAL BILLING</td>
				<td class="tbl_head_td_xcenter" width="90">TOTAL HPP</td>
				<td class="tbl_head_td_xcenter" width="110">TOTAL PROFIT</td>
			</tr>
		</thead>
		<tbody>
		<?php
		if(!empty($report_data)){
		
			$nox = 1;
			$total_qty = 0;
			$total_billing = 0;
			$total_hpp = 0;
			$total_profit = 0;
			$total_tax = 0;
			$total_service = 0;
			$grand_total = 0;
			$grand_total_payment = array();
			foreach($report_data as $key => $dtDet){
				
				if(empty($key)){
					$key = 'Products Deleted';
				}
				
				?>
				<tr>
					<td class="tbl_head_td_first_xcenter"><?php echo $nox; ?></td>
					<td class="tbl_head_td" colspan="<?php echo $total_cols-1; ?>"><?php echo $key; ?></td>
				</tr>
				<?php
				
				$no = 1;
				$cat_total_qty = 0;
				$cat_total_billing = 0;
				$cat_total_hpp = 0;
				$cat_total_profit = 0;
				$cat_total_tax = 0;
				$cat_total_service = 0;
				$cat_grand_total = 0;
				
				if(!empty($dtDet)){
					foreach($dtDet as $det){
								
						if(empty($det['product_name'])){
							$det['product_name'] = '#'.$det['product_id'];
						}
						
						?>
						<tr>
							<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
							<td class="tbl_data_td"><?php echo $det['product_name']; ?></td>
							<td class="tbl_data_td_xcenter"><?php echo $det['total_qty']; ?></td>
							<td class="tbl_data_td_xright">Rp. <?php echo $det['total_billing_show']; ?></td>
							<td class="tbl_data_td_xright">Rp. <?php echo $det['total_hpp_show']; ?></td>
							<td class="tbl_data_td_xright">Rp. <?php echo $det['total_profit_show']; ?></td>
						</tr>
						<?php	
						
						$cat_total_qty +=  $det['total_qty'];
						$cat_total_billing +=  $det['total_billing'];
						$cat_total_hpp +=  $det['total_hpp'];
						$cat_total_profit +=  $det['total_profit'];
						$cat_total_tax +=  $det['tax_total'];
						$cat_total_service +=  $det['service_total'];
						$cat_grand_total +=  $det['grand_total'];
						
						$total_qty +=  $det['total_qty'];
						$total_billing +=  $det['total_billing'];
						$total_hpp +=  $det['total_hpp'];
						$total_profit +=  $det['total_profit'];
						$total_tax +=  $det['tax_total'];
						$total_service +=  $det['service_total'];
						$grand_total +=  $det['grand_total'];
						$no++;
					}
				}
				
				$nox++;
				
				?>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="<?php echo 2; ?>">TOTAL <?php echo $key; ?></td>
					<td class="tbl_summary_td_xcenter"><?php echo priceFormat($cat_total_qty); ?></td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($cat_total_billing); ?></td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($cat_total_hpp); ?></td>
					<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($cat_total_profit); ?></td>
				</tr>
				<?php
			}
			
			?>
			<tr>
				<td class="tbl_summary_td_first_xright" colspan="<?php echo 2; ?>">TOTAL</td>
				<td class="tbl_summary_td_xcenter"><?php echo priceFormat($total_qty); ?></td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_billing); ?></td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_hpp); ?></td>
				<td class="tbl_summary_td_xright">Rp. <?php echo priceFormat($total_profit); ?></td>
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
	</table
</div>
</body>
</html>