<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=current_price_product_".date("dmY").".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 1200;
$total_cols = 11;
?>
<html>
<body>
<style>
	<?php include ASSETS_PATH."desktop/css/report.css.php"; ?>
</style>
<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
	<div>
					
		<div class="title_report_xcenter"><?php echo $report_name;?></div>			
		<div class="subtitle_report_xcenter">Date: <?php echo date("d-m-Y");?></div>			
		
	</div>
		
	<table width="<?php echo $set_width; ?>">
		<!-- HEADER -->
		<thead>
			<tr>
				<td class="tbl_head_td_first_xcenter" width="50" rowspan="2">NO</td>
				<td class="tbl_head_td_xcenter" width="250" rowspan="2">Product Name</td>
				<td class="tbl_head_td_xcenter" width="80" rowspan="2">Product Group</td>
				<td class="tbl_head_td_xcenter" width="60" rowspan="2">#Cat.ID</td>
				<td class="tbl_head_td_xcenter" width="160" rowspan="2">Cat.Name</td>
				<td class="tbl_head_td_xcenter" colspan="3">Normal Price</td>
				<td class="tbl_head_td_xcenter" colspan="3">Current Price</td>
			</tr>
			<tr>
				<td class="tbl_head_td_xcenter" width="100">HPP</td>
				<td class="tbl_head_td_xcenter" width="100">Price</td>
				<td class="tbl_head_td_xcenter" width="100">Profit</td>
				<td class="tbl_head_td_xcenter" width="100">HPP</td>
				<td class="tbl_head_td_xcenter" width="100">Price</td>
				<td class="tbl_head_td_xcenter" width="100">Profit</td>
			</tr>
		</thead>
		<tbody>
		<?php
		$no = 0;
		if(!empty($data_product)){
			foreach($data_product as $dt){
				
				$dt = (object) $dt;
				
				if(!empty($data_product_varian[$dt->id])){
					foreach($data_product_varian[$dt->id] as $dtV){
						$no++;
						
						$dtV = (object) $dtV;
						?>
						<tr>
							<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
							<td class="tbl_data_td"><?php echo $dtV->product_name; ?></td>
							<td class="tbl_data_td"><?php echo $dtV->product_group; ?></td>
							<td class="tbl_data_td_xcenter"><?php echo $dtV->product_category_id; ?></td>
							<td class="tbl_data_td"><?php echo $dtV->product_category_name; ?></td>
							<td class="tbl_data_td_xright"><?php echo $dtV->normal_hpp; ?></td>
							<td class="tbl_data_td_xright"><?php echo $dtV->normal_price; ?></td>
							<td class="tbl_data_td_xright"><?php echo $dtV->normal_profit; ?></td>
							<td class="tbl_data_td_xright"><?php echo $dtV->current_hpp; ?></td>
							<td class="tbl_data_td_xright"><?php echo $dtV->current_price; ?></td>
							<td class="tbl_data_td_xright"><?php echo $dtV->current_profit; ?></td>
						</tr>
						<?php
					}
				}else{
					$no++;
					?>
					<tr>
						<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
						<td class="tbl_data_td"><?php echo $dt->product_name; ?></td>
						<td class="tbl_data_td"><?php echo $dt->product_group; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $dt->product_category_id; ?></td>
						<td class="tbl_data_td"><?php echo $dt->product_category_name; ?></td>
						<td class="tbl_data_td_xright"><?php echo $dt->normal_hpp; ?></td>
						<td class="tbl_data_td_xright"><?php echo $dt->normal_price; ?></td>
						<td class="tbl_data_td_xright"><?php echo $dt->normal_profit; ?></td>
						<td class="tbl_data_td_xright"><?php echo $dt->current_hpp; ?></td>
						<td class="tbl_data_td_xright"><?php echo $dt->current_price; ?></td>
						<td class="tbl_data_td_xright"><?php echo $dt->current_profit; ?></td>
					</tr>
					<?php
				}
				
			}
			
		}else{
		?>
			<tr>
				<td colspan="<?php echo $total_cols; ?>" class="tbl_data_td_first_xcenter">Data Not Found</td>
			</tr>
		<?php
		}
		?>
		
		</tbody>	
	</table>
</div>
</body>
</html>