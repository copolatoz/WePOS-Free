<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=".url_title($report_name." - ".get_month($month).' '.$year).".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 900;
$total_cols = 12;
$bulan_mktime = strtotime("01-".$month."-".$year);
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
				<div>	
					<div class="title_report_xcenter"><?php echo $report_name;?></div>
					<div class="subtitle_report_xcenter"><?php echo "01 ".get_month($month)." ".$year." - ".date("t", $bulan_mktime)." ".get_month($month)." ".$year;?></div>			
					<div class="subtitle_report_xcenter"><?php echo 'WAREHOUSE: '.strtoupper($warehouse_name);?></div>			
					<div class="subtitle_report_xcenter"><?php echo 'ITEM: '.$item_code.' / '.strtoupper($item_name);?></div>			
					
				</div>
			</tr>	
			<tr>
				<td class="tbl_head_td_first_xcenter" width="70" rowspan="2">TANGGAL</td>
				<td class="tbl_head_td_xcenter" width="90" rowspan="2">NO.REF</td>		
				<td class="tbl_head_td_xcenter" width="80" rowspan="2">SATUAN</td>	
				<td class="tbl_head_td_xcenter" width="220" colspan="3">STOK MASUK</td>	
				<td class="tbl_head_td_xcenter" width="220" colspan="3">STOK KELUAR</td>	
				<td class="tbl_head_td_xcenter" width="220" colspan="3">STOK AKHIR</td>	
			</tr>
			<tr>
				<td class="tbl_head_td_xcenter" width="60">QTY</td>		
				<td class="tbl_head_td_xcenter" width="80">HPP</td>		
				<td class="tbl_head_td_xcenter" width="80">TOTAL</td>				
				<td class="tbl_head_td_xcenter" width="60">QTY</td>		
				<td class="tbl_head_td_xcenter" width="80">HPP</td>		
				<td class="tbl_head_td_xcenter" width="80">TOTAL</td>				
				<td class="tbl_head_td_xcenter" width="60">QTY</td>		
				<td class="tbl_head_td_xcenter" width="80">HPP</td>		
				<td class="tbl_head_td_xcenter" width="80">TOTAL</td>						
			</tr>
		
		</thead>
		<tbody>
		
			
			<?php
			
			if(!empty($report_data)){
				
				if(!empty($category_data)){
					foreach($category_data as $key => $dt){

						if(!empty($category_item_data[$key])){
							
							$no = 1;
							foreach($category_item_data[$key] as $dtItem){
								if(!empty($report_data[$dtItem])){
									$data = $report_data[$dtItem];
									
									$stok_akhir_qty = 0;
									$stok_akhir_hpp = $data['item_hpp'];
									$stok_akhir_total = 0;
									
									if(!empty($data['stock_trx'])){
										
										
										$stok_akhir_qty += $data['stock_awal'];
										$stok_akhir_total = ($stok_akhir_qty*$stok_akhir_hpp);
										?>
										<tr style="background-color:#e8e8e8; font-size:12px;">
											<td class="tbl_head_td_first" colspan="<?php echo $total_cols-3; ?>" style="border-right:0px;"><b><?php echo $data['item_code'].' / '.$data['item_name']; ?></b></td>
											<td class="tbl_head_td_xright" colspan="3" ><?php echo $dt; ?></td>
										</tr>
										
										<tr>
											<td class="tbl_data_td_first_xcenter">&nbsp;<?php echo date("d-m-Y", $bulan_mktime); ?></td>
											<td class="tbl_data_td">STOK AWAL</td>
											<td class="tbl_data_td"><?php echo $data['satuan']; ?></td>
											<td class="tbl_data_td_xcenter">&nbsp;</td>
											<td class="tbl_data_td_xright">&nbsp;</td>
											<td class="tbl_data_td_xright">&nbsp;</td>
											<td class="tbl_data_td_xcenter">&nbsp;</td>
											<td class="tbl_data_td_xright">&nbsp;</td>
											<td class="tbl_data_td_xright">&nbsp;</td>
											<td class="tbl_data_td_xcenter"><?php echo priceFormat($stok_akhir_qty); ?></td>
											<td class="tbl_data_td_xright"><?php echo ($stok_akhir_hpp); ?></td>
											<td class="tbl_data_td_xright"><?php echo ($stok_akhir_total); ?></td>
										</tr>
										
										<?php
										$total_stok_masuk_qty = 0;
										$total_stok_masuk_hpp = 0;
										$total_stok_masuk = 0;
										$total_stok_keluar_qty = 0;
										$total_stok_keluar_hpp = 0;
										$total_stok_keluar = 0;
										
										foreach($data['stock_trx'] as $dtTrx){
											
											$stok_masuk_qty = '&nbsp;';
											$stok_masuk_hpp = '&nbsp;';
											$stok_masuk_total = '&nbsp;';
											$stok_keluar_qty = '&nbsp;';
											$stok_keluar_hpp = '&nbsp;';
											$stok_keluar_total = '&nbsp;';
											$stok_total_qty = '&nbsp;';
											$stok_total_hpp = '&nbsp;';
											$stok_total_total = '&nbsp;';
											
											if($dtTrx['trx_type'] == 'in'){
												$stok_masuk_qty = ($dtTrx['trx_qty']);
												$stok_masuk_hpp = ($dtTrx['trx_nominal']);
												$stok_masuk_total = ($dtTrx['trx_nominal']*$dtTrx['trx_qty']);
												
												$stok_akhir_qty += $dtTrx['trx_qty'];
												$stok_akhir_total = ($stok_akhir_qty*$stok_akhir_hpp);
												
												$total_stok_masuk_qty += $dtTrx['trx_qty'];
												$total_stok_masuk_hpp = $dtTrx['trx_nominal'];
												$total_stok_masuk += ($dtTrx['trx_qty']*$dtTrx['trx_nominal']);
												
											}
											
											if($dtTrx['trx_type'] == 'out'){
												$stok_keluar_qty = ($dtTrx['trx_qty']);
												$stok_keluar_hpp = ($dtTrx['trx_nominal']);
												$stok_keluar_total = ($dtTrx['trx_nominal']*$dtTrx['trx_qty']);
												
												$stok_akhir_qty -= $dtTrx['trx_qty'];
												$stok_akhir_total = ($stok_akhir_qty*$stok_akhir_hpp);
												
												$total_stok_keluar_qty += $dtTrx['trx_qty'];
												$total_stok_keluar_hpp = $dtTrx['trx_nominal'];
												$total_stok_keluar += ($dtTrx['trx_qty']*$dtTrx['trx_nominal']);
											}
											
											?>
											<tr>
												<td class="tbl_data_td_first_xcenter"><?php echo date("d-m-Y", strtotime($dtTrx['trx_date'])); ?></td>
												<td class="tbl_data_td"><?php echo $dtTrx['trx_ref_data']; ?></td>
												<td class="tbl_data_td"><?php echo $data['satuan']; ?></td>
												<td class="tbl_data_td_xcenter"><?php echo $stok_masuk_qty; ?></td>
												<td class="tbl_data_td_xright"><?php echo $stok_masuk_hpp; ?></td>
												<td class="tbl_data_td_xright"><?php echo $stok_masuk_total; ?></td>
												<td class="tbl_data_td_xcenter"><?php echo $stok_keluar_qty; ?></td>
												<td class="tbl_data_td_xright"><?php echo $stok_keluar_hpp; ?></td>
												<td class="tbl_data_td_xright"><?php echo $stok_keluar_total; ?></td>
												<td class="tbl_data_td_xcenter"><?php echo priceFormat($stok_akhir_qty); ?></td>
												<td class="tbl_data_td_xright"><?php echo ($stok_akhir_hpp); ?></td>
												<td class="tbl_data_td_xright"><?php echo ($stok_akhir_total); ?></td>
											</tr>
											<?php
										}
										
										?>
										<tr>
											<td class="tbl_summary_td_first_xright" colspan="3">TOTAL </td>
											<td class="tbl_summary_td_xcenter"><?php echo priceFormat($total_stok_masuk_qty); ?></td>
											<td class="tbl_summary_td_xright"><?php echo ($total_stok_masuk_hpp); ?></td>
											<td class="tbl_summary_td_xright"><?php echo ($total_stok_masuk); ?></td>
											<td class="tbl_summary_td_xcenter"><?php echo priceFormat($total_stok_keluar_qty); ?></td>
											<td class="tbl_summary_td_xright"><?php echo ($total_stok_keluar_hpp); ?></td>
											<td class="tbl_summary_td_xright"><?php echo ($total_stok_keluar); ?></td>
											<td class="tbl_summary_td_xcenter"><?php echo priceFormat($stok_akhir_qty); ?></td>
											<td class="tbl_summary_td_xright"><?php echo ($stok_akhir_hpp); ?></td>
											<td class="tbl_summary_td_xright"><?php echo ($stok_akhir_total); ?></td>
										</tr>
										<tr>
											<td class="tbl_data_td_first_xcenter" colspan="<?php echo $total_cols; ?>">&nbsp;</td>
										</tr>
										<?php
									}
									
								}
							}
						}
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
			
			<tr>
				<td colspan="<?php echo $total_cols; ?>">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3">Printed: <?php echo date("d-m-Y H:i:s");?></td>
				<td colspan="<?php echo $total_cols-9; ?>">&nbsp;</td>
				<td colspan="3" class="xcenter">
						Prepared by:<br/><br/><br/><br/>
						----------------------------
				</td>
				<td colspan="3" class="xcenter">
					
						Approved by:<br/><br/><br/><br/>
						----------------------------
				</td>
			</tr>
		</tbody>
	</table>
</div>
</body>
</html>