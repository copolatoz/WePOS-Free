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
		$set_width = 900;
		$total_cols = 12;
		$bulan_mktime = strtotime("01-".$month."-".$year);
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
									
						<div class="title_report xcenter"><?php echo $report_name;?></div>
						<div class="subtitle_report xcenter"><?php echo "01 ".get_month($month)." ".$year." - ".date("t", $bulan_mktime)." ".get_month($month)." ".$year;?></div>				
						<div class="subtitle_report xcenter"><?php echo 'WAREHOUSE: '.strtoupper($warehouse_name);?></div>					
						
					</div>
				</tr>	
				<tr class="tbl-header">
					<td class="first xcenter" width="70" rowspan="2">TANGGAL</td>
					<td class="xcenter" width="90" rowspan="2">NO.REF</td>		
					<td class="xcenter" width="80" rowspan="2">SATUAN</td>	
					<td class="xcenter" width="220" colspan="3">STOK MASUK</td>	
					<td class="xcenter" width="220" colspan="3">STOK KELUAR</td>	
					<td class="xcenter" width="220" colspan="3">STOK AKHIR</td>	
				</tr>
				<tr class="tbl-header">
					<td class="xcenter" width="60">QTY</td>		
					<td class="xcenter" width="80">HPP</td>		
					<td class="xcenter" width="80">TOTAL</td>				
					<td class="xcenter" width="60">QTY</td>		
					<td class="xcenter" width="80">HPP</td>		
					<td class="xcenter" width="80">TOTAL</td>				
					<td class="xcenter" width="60">QTY</td>		
					<td class="xcenter" width="80">HPP</td>		
					<td class="xcenter" width="80">TOTAL</td>						
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
											<tr class="tbl-data" style="background-color:#e8e8e8; font-size:12px;">
												<td class="first xleft xbold" colspan="6" style="border-right:0px;"><?php echo $data['item_code'].' / '.$data['item_name']; ?></td>
												<td class="xright xbold" colspan="6" ><?php echo $dt; ?></td>
											</tr>
											
											<tr class="tbl-data">
												<td class="first xleft"><?php echo date("d-m-Y", $bulan_mktime); ?></td>
												<td class="xleft">STOK AWAL</td>
												<td class="xleft"><?php echo $data['satuan']; ?></td>
												<td class="xcenter">&nbsp;</td>
												<td class="xright">&nbsp;</td>
												<td class="xright">&nbsp;</td>
												<td class="xcenter">&nbsp;</td>
												<td class="xright">&nbsp;</td>
												<td class="xright">&nbsp;</td>
												<td class="xcenter"><?php echo priceFormat($stok_akhir_qty); ?></td>
												<td class="xright"><?php echo priceFormat($stok_akhir_hpp); ?></td>
												<td class="xright"><?php echo priceFormat($stok_akhir_total); ?></td>
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
													$stok_masuk_qty = priceFormat($dtTrx['trx_qty']);
													$stok_masuk_hpp = priceFormat($dtTrx['trx_nominal']);
													$stok_masuk_total = priceFormat($dtTrx['trx_nominal']*$dtTrx['trx_qty']);
													
													$stok_akhir_qty += $dtTrx['trx_qty'];
													$stok_akhir_total = ($stok_akhir_qty*$stok_akhir_hpp);
													
													$total_stok_masuk_qty += $dtTrx['trx_qty'];
													$total_stok_masuk_hpp = $dtTrx['trx_nominal'];
													$total_stok_masuk += ($dtTrx['trx_qty']*$dtTrx['trx_nominal']);
													
												}
												
												if($dtTrx['trx_type'] == 'out'){
													$stok_keluar_qty = priceFormat($dtTrx['trx_qty']);
													$stok_keluar_hpp = priceFormat($dtTrx['trx_nominal']);
													$stok_keluar_total = priceFormat($dtTrx['trx_nominal']*$dtTrx['trx_qty']);
													
													$stok_akhir_qty -= $dtTrx['trx_qty'];
													$stok_akhir_total = ($stok_akhir_qty*$stok_akhir_hpp);
													
													$total_stok_keluar_qty += $dtTrx['trx_qty'];
													$total_stok_keluar_hpp = $dtTrx['trx_nominal'];
													$total_stok_keluar += ($dtTrx['trx_qty']*$dtTrx['trx_nominal']);
												}
												
												?>
												<tr class="tbl-data">
													<td class="first xleft"><?php echo date("d-m-Y", strtotime($dtTrx['trx_date'])); ?></td>
													<td class="xleft"><?php echo $dtTrx['trx_ref_data']; ?></td>
													<td class="xleft"><?php echo $data['satuan']; ?></td>
													<td class="xcenter"><?php echo $stok_masuk_qty; ?></td>
													<td class="xright"><?php echo $stok_masuk_hpp; ?></td>
													<td class="xright"><?php echo $stok_masuk_total; ?></td>
													<td class="xcenter"><?php echo $stok_keluar_qty; ?></td>
													<td class="xright"><?php echo $stok_keluar_hpp; ?></td>
													<td class="xright"><?php echo $stok_keluar_total; ?></td>
													<td class="xcenter"><?php echo priceFormat($stok_akhir_qty); ?></td>
													<td class="xright"><?php echo priceFormat($stok_akhir_hpp); ?></td>
													<td class="xright"><?php echo priceFormat($stok_akhir_total); ?></td>
												</tr>
												<?php
											}
											
											?>
											<tr class="tbl-data">
												<td class="first xright xbold" colspan="3">TOTAL </td>
												<td class="xcenter xbold"><?php echo priceFormat($total_stok_masuk_qty); ?></td>
												<td class="xright xbold"><?php echo priceFormat($total_stok_masuk_hpp); ?></td>
												<td class="xright xbold"><?php echo priceFormat($total_stok_masuk); ?></td>
												<td class="xcenter xbold"><?php echo priceFormat($total_stok_keluar_qty); ?></td>
												<td class="xright xbold"><?php echo priceFormat($total_stok_keluar_hpp); ?></td>
												<td class="xright xbold"><?php echo priceFormat($total_stok_keluar); ?></td>
												<td class="xcenter xbold"><?php echo priceFormat($stok_akhir_qty); ?></td>
												<td class="xright xbold"><?php echo priceFormat($stok_akhir_hpp); ?></td>
												<td class="xright xbold"><?php echo priceFormat($stok_akhir_total); ?></td>
											</tr>
											<tr class="tbl-data">
												<td class="first xleft" colspan="12">&nbsp;</td>
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
						<div class="fright" style="width:250px;">
							Prepared by:<br/><br/><br/><br/>
							----------------------------
						</div>
						<div class="fright" style="width:250px;">
							Approved by:<br/><br/><br/><br/>
							----------------------------
						</div>
						
						<div class="fclear"></div>
						<br/>
					</td>
				</tr>
			</tbody>
		</table>
				
		
	</div>
	
	<?php
		if($do == 'print'){
		?>
		<script type="text/javascript">
			window.print();
		</script>
		<?php
		}
	?>
</body>
</html>