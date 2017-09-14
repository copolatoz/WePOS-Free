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
		$set_width = 770;
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<div class="fleft" style="width:260px; margin-right:10px;">
			<div class="logo">
				
				<?php
				if(!empty($client['client_logo'])){
					?>
					<img height="100" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $client['client_logo']; ?>">
					<?php
				}
				
				$is_srv = '';
				if(!empty($so_data['single_rate'])){
					$is_srv = '-<font color="red">R</font>';
				}
				?>
				
				
				<table>
					<tr class="f14 xbold">
						<td class="f14 xbold" colspan="3">
						<?php
						if(!empty($client['client_name'])){
							echo '<b>'.$client['client_name'].'</b>';
						}
						if(!empty($client['client_address'])){
							echo '<br/>'.$client['client_address'];
						}
						if(!empty($client['client_phone'])){
							echo '<br/>'.$client['client_phone'];
						}
						?>
						</td>
					</tr>
					<tr class="f14 xbold">
						<td class="f14 xbold" width="40">No.Faktur</td>
						<td class="f14 xbold" width="5">:</td>
						<td class="f14 xbold"><?php echo $so_data['so_number'].$is_srv; ?></td>
					</tr>
					<tr>
						<td>Tgl.Faktur</td>
						<td>:</td>
						<td><?php echo date("d/m/Y", strtotime($so_data['so_date'])); ?></td>
					</tr>
					<tr>
						<td>Gudang</td>
						<td>:</td>
						<td><?php echo $so_data['storehouse_code']; ?><br/></td>
					</tr>
				</table>
			</div>
			
			
			<div class="headoffice xbold">
				<?php 
				//echo $report_place_default;
				?>
			</div>
			
		</div>
		<div class="fright" style="width:330px;">
			<div class="title_report xcenter">
				FAKTUR PENJUALAN
			</div>
			<div class="xbox" style="padding:10px;">
				<table>
					<tr>
						<td width="10" class="f14 xbold" colspan="2">Customer: </td>
						<td colspan="3" class="f14">
						<?php echo $so_data['so_customer_name']; ?>
						<?php
						if(!empty($so_data['so_customer_address'])){
							echo '<br/>'.$so_data['so_customer_address'];
						}
						if(!empty($so_data['so_customer_phone'])){
							echo '<br/>'.$so_data['so_customer_phone'];
						}
						?>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="f14 xbold" >Payment: </td>
						<td colspan="2"> By <?php echo ucwords(strtolower($so_data['so_payment']));?></td>
					</tr>
					
					<?php
					if(!empty($so_data['so_memo'])){
					?>	
						<tr>
							<td colspan="2" class="f14 xbold" >Memo: </td>
							<td colspan="3"><i><?php echo $so_data['so_memo']; ?></i></td>
						</tr>
					<?php
					}	
					?>
				</table>
				
				
			</div>
		</div>
		<div class="fclear"></div>
		
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<tr class="tbl-header">
				<td class="first xleft" width="30">NO</td>
				<td class="xleft" width="80">KODE</td>
				<td class="xleft">NAMA BARANG</td>
				<td class="xcenter" width="60">QTY</td>
				<td class="xcenter" width="100">HARGA</td>
				<td class="xcenter" width="100">DISCOUNT</td>
				<td class="xcenter" width="100">TOTAL</td>
			</tr>
			
			<?php
			if(!empty($so_detail)){
			
				$so_sub_total = 0;
				$total_discount = 0;
				$no = 1;
				foreach($so_detail as $det){
					
					if(!empty($is_srv)){
						$det['sod_potongan'] = 0;
					}
					
					$total_discount += $det['sod_potongan'];
					$total = $det['sod_total'] - $det['sod_potongan'];
					$so_sub_total += $total;
					
					$item_name = $det['item_name'];
					
					if(!empty($det['subcat1'])){
						//$item_name .= ' - '.$det['subcat1'];
					}
					if(!empty($det['subcat2'])){
						$item_name .= ' - '.$det['subcat2'];
					}
					if(!empty($det['subcat3'])){
						//$item_name .= ' - '.$det['subcat3'];
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
					<tr class="tbl-data">
						<td class="first xleft"><?php echo $no; ?></td>
						<td class="xleft"><?php echo $det['item_code']; ?></td>
						<td class="xleft"><?php echo $item_name; ?></td>
						<td class="xcenter"><?php echo $det['sod_qty']; ?></td>
						<td class="xright"><?php echo priceFormat($det['sales_price']); ?></td>
						<td class="xright"><?php echo priceFormat($det['sod_potongan']); ?></td>
						<td class="xright"><?php echo priceFormat($total); ?></td>
					</tr>
					<?php	

					$no++;
				}
			
			}
			?>
			<tr class="tbl-total">
				<td class="first xright xbold" colspan="3" style="border-top:1px solid #ccc; font-size:14px;">TOTAL </td>
				<td class="xcenter xbold" style="border-top:1px solid #ccc; font-size:14px;"><?php echo $so_data['so_total_qty']; ?></td>
				<td class="xright xbold" colspan="2" style="border-top:1px solid #ccc; font-size:14px;"><?php echo priceFormat($total_discount); ?></td>
				<td class="xright xbold" style="border-top:1px solid #ccc; font-size:14px;"><?php echo priceFormat($so_sub_total); ?></td>
			</tr>
			<tr class="tbl-total">
				<td class="first xright xbold" colspan="2" style="border-top:1px solid #ccc; border-bottom:1px solid #ccc; font-size:14px;">
						<?php
						$so_total_price = $so_sub_total;
						
						if($so_data['so_tax']){
							echo 'TAX:'.priceFormat($so_data['so_tax']);
							$so_total_price += $so_data['so_tax'];
						}else{
							echo '&nbsp;';
						}
						?>
					</td>
					<td class="xright xbold" style="border-top:1px solid #ccc; border-bottom:1px solid #ccc; font-size:14px;">
						<?php
						if($so_data['so_shipping']){
							echo 'SHIPPING:'.priceFormat($so_data['so_shipping']);
							$so_total_price += $so_data['so_shipping'];
						}else{
							echo '&nbsp;';
						}
						?>
					</td>
					<td class="xright xbold" colspan="2" style="border-top:1px solid #ccc; border-bottom:1px solid #ccc; font-size:14px;">
						<?php
						if($so_data['so_dp']){
							echo 'DP:'.priceFormat($so_data['so_dp']);
							$so_total_price += $so_data['so_dp'];
						}else{
							echo '&nbsp;';
						}
						?>
					</td>
					<td class="xright xbold" style="border-top:1px solid #ccc; border-bottom:1px solid #ccc; font-size:14px;">GRAND TOTAL </td>
					<td class="xright xbold" style="border-top:1px solid #ccc; border-bottom:1px solid #ccc; font-size:14px;"><?php echo priceFormat($so_total_price);?></td>
				</tr>
		</table>
		
		<br/>
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