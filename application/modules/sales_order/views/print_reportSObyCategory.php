<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/desktop/css/report.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'assets/desktop/css/report.css'; ?>" media="print"/>	
	</head>
<body>
	<?php
		$set_width = 1260;
		$total_cols = 12;
		
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<div>
			<div class="logo">
				
				<!-- <img height="80" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>"> -->
				
			</div>
						
			<div class="title_report xcenter"><?php echo $report_name;?></div>
			<div class="subtitle_report xcenter"><?php echo 'Period : '.$date_from.' TO '.$date_till;?></div>			
			<?php
			if(!empty($storehouse_name)){
				if($storehouse_name == 'Semua Gudang'){
					?>
					<div class="subtitle_report xcenter"><?php echo $storehouse_name; ?></div>	
					<?php
				}else{
					?>
					<div class="subtitle_report xcenter">Gudang: <?php echo $storehouse_name; ?></div>	
					<?php
				}
				
			}	
			?>
		</div>
		<br/>
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<tr class="tbl-header">
				<td class="first xcenter" width="40" rowspan="2">NO</td>
				<td class="xcenter" width="110" rowspan="2">KODE</td>
				<td class="xcenter" width="160" rowspan="2">NAMA BARANG</td>
				<td class="xcenter" width="60" rowspan="2">TOTAL QTY</td>
				<td class="xcenter" width="110" rowspan="2">TOTAL SALES</td>
				<td class="xcenter" width="110" rowspan="2">DISCOUNT</td>
				<td class="xcenter" width="100" rowspan="2">SUB TOTAL</td>
				<td class="xcenter" width="90" rowspan="2">TAX</td>
				<td class="xcenter" width="90" rowspan="2">SHIPPING</td>
				<td class="xcenter" width="90" rowspan="2">DP</td>
				<td class="xcenter" width="200" colspan="2">PAYMENT</td>	
			</tr>
			<tr class="tbl-header">
				<td class="xcenter" width="100">CASH</td>
				<td class="xcenter" width="100">CREDIT</td>
			</tr>
			
			<?php
			if(!empty($report_data)){
			
				//SORTING BY QTY TERBANYAK
				$qty_terbanyak = array();
				foreach($report_data as $key => $dtDet){
					if(empty($qty_terbanyak[$key])){
						$qty_terbanyak[$key] = 0;
					}
					
					if(!empty($dtDet)){
						foreach($dtDet as $det){
							$qty_terbanyak[$key] += $det['total_qty'];
						}
					}
					
				}
				arsort($qty_terbanyak);
				
				$nox = 1;
				$total_qty = 0;
				$total_sales = 0;
				$total_discount = 0;
				$sub_total = 0;
				$sub_total_cash = 0;
				$sub_total_credit = 0;
				$total_tax = 0;
				$total_shipping = 0;
				$total_dp = 0;
				
				foreach($qty_terbanyak as $key => $total){
					
					$item_category_name_show = '';
					if(empty($item_category_name[$key])){
						$item_category_name_show = 'Products Deleted';
					}else{
						$item_category_name_show = $item_category_name[$key];
					}
					
					?>
					<tr class="tbl-data">
						<td class="first xcenter xbold"><?php echo $nox; ?></td>
						<td class="xleft xbold" colspan="<?php echo $total_cols-1; ?>"><?php echo $key.' / '.$item_category_name_show; ?></td>
					</tr>
					<?php
					$no = 1;
					
					$cat_total_qty = 0;
					$cat_total_sales = 0;
					$cat_total_discount = 0;
					$cat_sub_total = 0;
					$cat_sub_total_cash = 0;
					$cat_sub_total_credit = 0;
					$cat_total_tax = 0;
					$cat_total_shipping = 0;
					$cat_total_dp = 0;
					
					
					$dtDet = 0;
					if(!empty($report_data[$key])){
						$dtDet = $report_data[$key];
					}
					
					if(!empty($dtDet)){
						foreach($dtDet as $det){
					
							if(empty($det['item_name'])){
								$det['item_name'] = '#'.$det['item_id'];
							}
						
							if(empty($det['item_code'])){
								$det['item_code'] = 'N/A';
							}
							
							?>
							<tr class="tbl-data">
								<td class="first xcenter">&nbsp;</td>
								<td class="xleft"><?php echo $det['item_code']; ?></td>
								<td class="xleft"><?php echo $det['item_name']; ?></td>
								<td class="xcenter"><?php echo $det['total_qty']; ?></td>
								<td class="xright"><?php echo $det['total_sales_show']; ?></td>
								<td class="xright"><?php echo $det['total_discount_show']; ?></td>
								<td class="xright"><?php echo $det['sub_total_show']; ?></td>
								<td class="xright"><?php echo $det['total_tax_show']; ?></td>
								<td class="xright"><?php echo $det['total_shipping_show']; ?></td>
								<td class="xright"><?php echo $det['total_dp_show']; ?></td>
								<td class="xright"><?php echo $det['sub_total_cash_show']; ?></td>
								<td class="xright"><?php echo $det['sub_total_credit_show']; ?></td>
							</tr>
							<?php	
							
							//CAT
							$cat_total_qty +=  $det['total_qty'];
							$cat_total_sales +=  $det['total_sales'];
							$cat_total_discount +=  $det['total_discount'];
							$cat_sub_total +=  $det['sub_total'];
							$cat_sub_total_cash +=  $det['sub_total_cash'];
							$cat_sub_total_credit +=  $det['sub_total_credit'];
							$cat_total_tax +=  $det['total_tax'];
							$cat_total_shipping +=  $det['total_shipping'];
							$cat_total_dp +=  $det['total_dp'];
							
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
					}
					
					$nox++;
					
					?>
					<tr class="tbl-data">
						<td class="first xright xbold" colspan="<?php echo 3; ?>">TOTAL <?php echo $item_category_name_show; ?> </td>
						<td class="xcenter xbold"><?php echo priceFormat($cat_total_qty); ?></td>
						<td class="xright xbold"><?php echo priceFormat($cat_total_sales); ?></td>
						<td class="xright xbold"><?php echo priceFormat($cat_total_discount); ?></td>
						<td class="xright xbold"><?php echo priceFormat($cat_sub_total); ?></td>
						<td class="xright xbold"><?php echo priceFormat($cat_total_tax); ?></td>
						<td class="xright xbold"><?php echo priceFormat($cat_total_shipping); ?></td>
						<td class="xright xbold"><?php echo priceFormat($cat_total_dp); ?></td>
						<td class="xright xbold"><?php echo priceFormat($cat_sub_total_cash); ?></td>
						<td class="xright xbold"><?php echo priceFormat($cat_sub_total_credit); ?></td>
					</tr>
					<?php
				}
				
				?>
				<tr class="tbl-data">
					<td class="first xright xbold" colspan="<?php echo 3; ?>">TOTAL ALL CATEGORY </td>
					<td class="xcenter xbold"><?php echo priceFormat($total_qty); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_sales); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_discount); ?></td>
					<td class="xright xbold"><?php echo priceFormat($sub_total); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_tax); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_shipping); ?></td>
					<td class="xright xbold"><?php echo priceFormat($total_dp); ?></td>
					<td class="xright xbold"><?php echo priceFormat($sub_total_cash); ?></td>
					<td class="xright xbold"><?php echo priceFormat($sub_total_credit); ?></td>
				</tr>
				<?php
			}else{
			?>
				<tr class="tbl-data">
					<td colspan="<?php echo $total_cols; ?>" class="first xleft">Data Not Found</td>
				</tr>
			<?php
			}
			?>
			
			<tr class="tbl-sign">
				<td colspan="<?php echo $total_cols; ?>" class="first xleft">
					<br/>
					<br/>
					<div class="fleft" style="width:200px;">
						<br/><br/><br/><br/>
						Printed: <?php echo date("d-m-Y H:i:s");?>
					</div>
					<div class="fright" style="width:200px;">
						Prepared by:<br/><br/><br/><br/>
						----------------------------
					</div>
					<div class="fright" style="width:200px;">
						Approved by:<br/><br/><br/><br/>
						----------------------------
					</div>
					
					<div class="fclear"></div>
					<br/>
				</td>
			</tr>			
		</table>
				
		
	</div>
	
	<?php
		if($do == 'print' OR $do == true){
		?>
		<script type="text/javascript">
			window.print();
		</script>
		<?php
		}
	?>
</body>
</html>