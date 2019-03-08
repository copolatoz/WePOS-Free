<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name." - ".$warehouse_name." - ".$category_name.".xls")); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 800;
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
				<div class="title_report_xcenter"><?php echo $report_name;?></div>
				<div class="subtitle_report_xcenter"><?php echo 'WAREHOUSE: '.strtoupper($warehouse_name);?> / <?php echo 'CATEGORY: '.$category_name;?></div>
			</tr>	
			<tr>
				<td class="tbl_head_td_first_xcenter" width="50" rowspan="2">NO</td>
				<td class="tbl_head_td_xcenter" width="120" rowspan="2">KODE</td>
				<td class="tbl_head_td" width="400" rowspan="2">ITEM</td>
				<td class="tbl_head_td_xcenter" width="80" rowspan="2">SATUAN</td>	
				<td class="tbl_head_td_xcenter" colspan="2">TOTAL STOK</td>
			</tr>
			<tr>
				<td class="tbl_head_td_first_xcenter" width="80">MIN.STOK</td>			
				<td class="tbl_head_td_xcenter" width="80">SAAT INI</td>
			</tr>
		</thead>
		<tbody>
			<?php
			if(!empty($report_data)){
			
				$no = 1;
				$all_total_min_stock = 0;
				$all_total_stock_kemarin = 0;
				$all_total_stock_in = 0;
				$all_total_stock_out = 0;
				$all_total_stock = 0;
				$all_total_harga = 0;
				$total_qty = 0;
				foreach($report_data as $det){
										
					if(empty($det['min_stock'])){
						$det['min_stock'] = 0;
					}
					
					if(empty($det['total_qty_stok'])){
						$det['total_qty_stok'] = 0;
					}
						
					$min_stock = 0;
					if(!empty($det['min_stock'])){
						$min_stock = $det['min_stock'];
					}
					
					$total_stock_kemarin = 0;
					if(!empty($det['total_stock_kemarin'])){
						$total_stock_kemarin = $det['total_stock_kemarin'];
					}
					
					$total_stock_in = 0;
					if(!empty($det['total_stock_in'])){
						$total_stock_in = $det['total_stock_in'];
					}
					
					$total_stock_out = 0;
					if(!empty($det['total_stock_out'])){
						$total_stock_out = $det['total_stock_out'];
					}
					
					$total_stock = 0;
					if(!empty($det['total_stock'])){
						$total_stock = $det['total_stock'];
					}
					
					?>
					<tr>
						<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
						<td class="tbl_data_td"><?php echo $det['item_code']; ?></td>
						<td class="tbl_data_td"><?php echo $det['item_name']; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $det['satuan']; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $min_stock; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $total_stock; ?></td>
					</tr>
					<?php	
					$all_total_min_stock += $min_stock;
					$all_total_stock_kemarin += $total_stock_kemarin;
					$all_total_stock_in += $total_stock_in;
					$all_total_stock_out += $total_stock_out;
					$all_total_stock += $total_stock;
					$all_total_harga += ($det['item_hpp']*$total_stock);
					
					$total_qty += $det['total_stock'];
					$no++;
				}
				
				?>
				<tr>
					<td class="tbl_summary_td_first_xright" colspan="6">&nbsp;</td>
					
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
	</table>
</div>
</body>
</html>