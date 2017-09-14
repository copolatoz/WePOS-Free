<?php
//header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-type: application/excel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name.'-'.$storehouse_name.'-'.date("dmY")).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

?>
<html>
<body>
<style>
	<?php include ASSETS_PATH."desktop/css/report.css.php"; ?>
</style>
<?php
	$set_width = 1040;
	$total_cols = 9;
?>
<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
	
	<table width="<?php echo $set_width; ?>">
		<!-- HEADER -->
		<thead>
			<tr>
				<div>
					<div class="logo">
						
						<!-- <img height="80" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>"> -->
						
					</div>
								
					<div class="title_report_xcenter"><?php echo $report_name." - ".$storehouse_name;?></div>		
					
				</div>
			</tr>	
			<tr>
				<td class="tbl_head_td_first_xcenter" width="60">#ID</td>
				<td class="tbl_head_td" width="150">CATEGORY/ITEM CODE</td>
				<td class="tbl_head_td" width="200">ITEM NAME</td>
				<td class="tbl_head_td" width="140">UNIT</td>
				<td class="tbl_head_td_xright" width="70">QTY AWAL</td>
				<td class="tbl_head_td_xright" width="70">QTY FISIK</td>
				<td class="tbl_head_td_xright" width="70">LAST IN</td>
				<td class="tbl_head_td_xright" width="70">AVERAGE</td>
				<td class="tbl_head_td" width="200">NOTES</td>
			</tr>
		
		</thead>
		<tbody>
			<?php
			if(!empty($report_data)){
			
				$no = 1;
				
				foreach($report_data as $cat_id => $dt_det){
					
					if(!empty($dt_det)){
						
						$nama_category = '-';
						if(!empty($cat_name[$cat_id])){
							$nama_category = $cat_name[$cat_id];
						}
						?>
						<tr>
							<td class="tbl_head_td_first_xcenter" width="60">&nbsp;</td>
							<td class="tbl_head_td" colspan="2"><?php echo $nama_category; ?></td>
							<td class="tbl_head_td" colspan="6">&nbsp;</td>
						</tr>
						<?php
						foreach($dt_det as $dt){
							?>
							<tr>
								<td class="tbl_data_td_first_xright"><?php echo $dt['id']; ?> </td>
								<td class="tbl_data_td"><?php echo $dt['item_code']; ?></td>
								<td class="tbl_data_td"><?php echo $dt['item_name']; ?></td>
								<td class="tbl_data_td"><?php echo $dt['unit_name']; ?></td>
								<td class="tbl_data_td_xright"><?php echo $dt['total_qty_stok']; ?></td>
								<td class="tbl_data_td_xright"><?php echo $dt['total_qty_stok']; ?></td>
								<td class="tbl_data_td_xright"><?php echo $dt['last_in']; ?></td>
								<td class="tbl_data_td_xright">Rp. <?php echo $dt['item_hpp']; ?></td>
								<td class="tbl_data_td">&nbsp;</td>
							</tr>
							<?php
						}
						
					}
					$no++;
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