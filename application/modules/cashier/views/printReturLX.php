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
		$set_width = 750;
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>; line-height: 20px;">
		
		<?php
		/*if(!empty($client['client_logo'])){
			?>
			<img height="100" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $client['client_logo']; ?>">
			<?php
		}*/
		$is_srv = '';
		if(!empty($retur_detail['single_rate'])){
			$is_srv = '-R';
		}
		?>
		
		<table width="<?php echo $set_width; ?>">
			<tr class="">
				<td class="f14" style="border-bottom:1px solid #666; font-size:20px; text-align:center; font-weight:bold;" colspan="3">
					FAKTUR RETUR PENJUALAN
				</td>
			</tr>
			<tr class="">
				<td class="f14" style="border-bottom:1px solid #666;">
					<?php
					if(!empty($client['client_name'])){
						echo '<font style="font-size:16px;"><b>'.$client['client_name'].'</b></font>';
					}
					if(!empty($client['client_address'])){
						echo '<br/>'.$client['client_address'];
					}
					if(!empty($client['client_phone'])){
						echo '<br/>'.$client['client_phone'];
					}
					?>
				</td>
				<td class="f14 xbold" style="border-bottom:1px solid #666; font-size:20px;">
					&nbsp;
				</td>
				<td class="f14 xright" style="border-bottom:1px solid #666;">
					Customer: <?php echo $retur_data['customer_name']; ?>
					<?php
					if(!empty($retur_data['customer_address'])){
						echo '<br/>'.$retur_data['customer_address'];
					}
					if(!empty($retur_data['customer_phone'])){
						echo '<br/>'.$retur_data['customer_phone'];
					}
					if(!empty($retur_data['retur_memo'])){
						echo '<br/>Memo: '.$retur_data['retur_memo'];
					}
					?>
					
				</td>
			</tr>
			<tr>
				<td class="f14">
					No.Faktur: <b><?php echo $retur_data['retur_number'].$is_srv; ?></b><br/>
					Tgl.Faktur: <?php echo date("d/m/Y", strtotime($retur_data['retur_date'])); ?>
				</td>
				<td class="f14">
					Retur <?php echo $retur_data['retur_ref_text'].': <b>'.$retur_data['ref_no']; ?></b><br/>
					Jenis Retur: <?php echo $retur_data['retur_type_text']; ?><br/>
				</td>
				<td class="f14 xright">
					Gudang: <?php echo $retur_data['storehouse_code'].'/'.$retur_data['storehouse_name']; ?><br/>
					<?php echo $user_fullname; ?> / <?php echo date("d-m-Y H:i:s"); ?>
				</td>
			</tr>
		</table>
		<table width="<?php echo $set_width; ?>" style="font-size:14px;">
			<!-- HEADER -->
			<thead>
				<tr class="" style="border-top:1px solid #666; border-bottom:1px solid #666;">
					<td class="first xleft xbold" width="40">No</td>
					<td class="xleft xbold">Nama Barang</td>
					<td class="xcenter xbold" width="50">Qty</td>
					<td class="xright xbold" width="100">Hrg Satuan</td>
					<td class="xright xbold" width="150">Sub Total</td>
				</tr>
			</thead>
			<?php
			if(!empty($retur_detail)){
				
				$retur_total = 0;
				$total_qty = 0;
				$total_tax = 0;
				$no = 1;
				foreach($retur_detail as $det){
					
					$item_name = $det['item_code'].' / '.$det['item_name'];
					
					if(!empty($det['varian_name'])){
						$item_name .= ' ('.$det['varian_name'].')';
					}
					
					$total = $det['returd_price']*$det['returd_qty'];
					$total_qty += $det['returd_qty'];
					$total_tax += $det['returd_tax'];
					$retur_total += $total;
					
					if($det['product_type'] == 'package'){
						
						if(!empty($det['product_varian_id'])){
							//get product on package
							if(!empty($product_package_varian[$det['item_product_id']][$det['product_varian_id']])){
								//get all item - gramasi product
								foreach($product_package_varian[$det['item_product_id']][$det['product_varian_id']] as $product_id){
									
									$varian_per_item = 0;
									$varian_id_item = 0;
									if(!empty($product_package_varian_peritem[$det['item_product_id']][$det['product_varian_id']][$product_id])){
										$varian_per_item = 1;
										$varian_id_item = $product_package_varian_peritem[$det['item_product_id']][$det['product_varian_id']][$product_id];
										
										if(!empty($all_product_package_name[$product_id])){
											$dt_product = $all_product_package_name[$product_id];
											$item_name .= '<br/> # '.$dt_product->product_code.'/'.$dt_product->product_name;
											if(!empty($dt_product->varian_name)){
												$item_name .= ' ('.$dt_product->varian_name.')';
											}
										}
										
									}
									
									//cek on gramasi
									if(!empty($product_gramasi[$product_id])){
										foreach($product_gramasi[$product_id] as $dtgramasi){
											
											if(!empty($varian_id_item)){
												if($varian_id_item == $dtgramasi->varian_id){
													$item_name .= '<br/> &nbsp; &nbsp; &mdash; '.$dtgramasi->item_name.' ('.$dtgramasi->item_qty.' '.$dtgramasi->unit_code.')';
												}
											}else{
												$item_name .= '<br/> &nbsp; &mdash; '.$dtgramasi->item_name.' ('.$dtgramasi->item_qty.' '.$dtgramasi->unit_code.')';
											}
											
											
										}
									}
								}
							}
						}else{
							//get product on package
							if(!empty($product_package[$det['item_product_id']])){
								//get all item - gramasi product
								foreach($product_package[$det['item_product_id']] as $product_id){
									
									$varian_per_item = 0;
									$varian_id_item = 0;
									if(!empty($product_package_varian_peritem[$det['item_product_id']][0][$product_id])){
										$varian_per_item = 1;
										$varian_id_item = $product_package_varian_peritem[$det['item_product_id']][0][$product_id];
										
										if(!empty($all_product_package_name[$product_id])){
											$dt_product = $all_product_package_name[$product_id];
											$item_name .= '<br/> # '.$dt_product->product_code.'/'.$dt_product->product_name;
											if(!empty($dt_product->varian_name)){
												$item_name .= ' ('.$dt_product->varian_name.')';
											}
										}
										
									}
									
									//cek on gramasi
									if(!empty($product_gramasi[$product_id])){
										foreach($product_gramasi[$product_id] as $dtgramasi){
											
											if(!empty($varian_id_item)){
												if($varian_id_item == $dtgramasi->varian_id){
													$item_name .= '<br/> &nbsp; &nbsp; &mdash; '.$dtgramasi->item_name.' ('.$dtgramasi->item_qty.' '.$dtgramasi->unit_code.')';
												}
											}else{
												$item_name .= '<br/> &nbsp; &mdash; '.$dtgramasi->item_name.' ('.$dtgramasi->item_qty.' '.$dtgramasi->unit_code.')';
											}
											
										}
									}
								}
							}
						}
						
					}else{
						
						if(!empty($det['product_varian_id'])){
							if(!empty($product_gramasi_varian[$det['item_product_id']][$det['product_varian_id']])){
								foreach($product_gramasi_varian[$det['item_product_id']][$det['product_varian_id']] as $dtgramasi){
									
									$item_name .= '<br/> &nbsp; &mdash; '.$dtgramasi->item_name.' ('.$dtgramasi->item_qty*$det['returd_qty'].' '.$dtgramasi->unit_code.')';
									
								}
							}
						}else{
							if(!empty($product_gramasi[$det['item_product_id']]) AND count($product_gramasi[$det['item_product_id']]) > 1){
								foreach($product_gramasi[$det['item_product_id']] as $dtgramasi){
									
									$item_name .= '<br/> &nbsp; &mdash; '.$dtgramasi->item_name.' ('.$dtgramasi->item_qty*$det['returd_qty'].' '.$dtgramasi->unit_code.')';
									
								}
							}
						}
					}
					
					if(!empty($det['data_stok_kode_unik'])){
						$data_stok_kode_unik = explode("\n", $det['data_stok_kode_unik']);
						$no_sn = 0;
						if(!empty($data_stok_kode_unik)){
							foreach($data_stok_kode_unik as $dt){
								if(!empty($dt)){
									$no_sn++;
									$item_name .= '<br/>IMEI/SN #'.$no_sn.': '.$dt;
								}
							}
						}
					}
					
					?>
					<tr class="">
						<td class="first xleft"><?php echo $no; ?></td>
						<td class="xleft"><?php echo $item_name; ?></td>
						<td class="xcenter"><?php echo $det['returd_qty']; ?></td>
						<td class="xright"><?php echo 'Rp. '.priceFormat($det['returd_price']); ?></td>
						<td class="xright"><?php echo 'Rp. '.priceFormat($total); ?></td>
					</tr>
					<?php	

					$no++;
				}
			
			}
			?>
			<tr class="" style="height:50px;">
				<td class="first xleft" colspan="7">&nbsp;</td>
			</tr>
			<tfoot>
				<tr style="border-top:1px solid #666;">
					<td class="first xright xbold" colspan="3" style="border-top:1px solid #666; font-size:14px;">Total Qty = <?php echo $total_qty; ?></td>
					<td class="xright xbold" style="border-top:1px solid #666; font-size:14px;"> T O T A L </td>
					<td class="xright xbold" style="border-top:1px solid #666; font-size:14px;"><?php echo 'Rp. '.priceFormat($retur_total); ?></td>
				</tr>
				<tr style="border-top:1px solid #666;">
					<td class="first" colspan="3" style="border-top:1px solid #666; font-size:14px;">
						<br/>
						<table width="100%">
							<tr>
								<td class="first xcenter"style="font-size:14px;">
									Penerima<br/><br/><br/>(_____________)
								</td>
								<td class="xcenter" style="font-size:14px;">
									Admin<br/><br/><br/>(_____________)
								</td>
								<td class="xcenter" style="font-size:14px;">
									Hormat Kami,<br/><br/><br/>(_____________)
								</td>	
							</tr>
						</table>
					</td>
					<td class="xright xbold" style="border-top:1px solid #666; font-size:14px;">&nbsp;</td>
					<td class="xright xbold" style="border-top:1px solid #666; font-size:14px;">&nbsp;</td>
				</tr>
			</tfoot>			
		</table>
		<?php
		if(!empty($no_rek)){ echo $no_rek; }
		?>
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