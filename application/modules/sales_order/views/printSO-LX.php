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
		if(!empty($so_data['single_rate'])){
			$is_srv = '-R';
		}
		?>
		
		<table width="<?php echo $set_width; ?>">
			<tr class="">
				<td class="f14" style="border-bottom:1px solid #666;">
					&nbsp;
				</td>
				<td class="f14 xbold" style="border-bottom:1px solid #666; font-size:20px;">
					FAKTUR PENJUALAN
				</td>
				<td class="f14 xright" style="border-bottom:1px solid #666;">
					&nbsp;
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
					<?php echo $session_user; ?> / <?php echo date("d-m-Y H:i:s"); ?>
				</td>
			</tr>
			<tr>
				<td class="f14">
					No.Faktur: <b><?php echo $so_data['so_number'].$is_srv; ?></b><br/>
					Tgl.Faktur: <?php echo date("d/m/Y", strtotime($so_data['so_date'])); ?>
				</td>
				<td class="f14">
					Gudang: <?php echo $so_data['storehouse_code']; ?><br/>
					Payment: <?php echo ucwords(strtolower($so_data['so_payment']));?>
				</td>
				<td class="f14">
					Customer: <?php echo $so_data['so_customer_name']; ?>
					<?php
					if(!empty($so_data['so_customer_address'])){
						echo '/'.$so_data['so_customer_address'];
					}
					if(!empty($so_data['so_customer_phone'])){
						echo '/'.$so_data['so_customer_phone'];
					}
					if(!empty($so_data['so_memo'])){
						echo '<br/>';
						echo 'Memo: '.$so_data['so_memo'];
					}
					?>
				</td>
			</tr>
		</table>
		<table width="<?php echo $set_width; ?>" style="font-size:14px;">
			<!-- HEADER -->
			<thead>
				<tr class="" style="border-top:1px solid #666; border-bottom:1px solid #666;">
					<td class="first xleft xbold" width="40">NO</td>
					<td class="xleft xbold" width="120">KODE</td>
					<td class="xleft xbold">NAMA BARANG</td>
					<td class="xcenter xbold" width="50">QTY</td>
					<td class="xright xbold" width="100">HARGA</td>
					<td class="xright xbold" width="100">DISCOUNT</td>
					<td class="xright xbold" width="100">TOTAL</td>
				</tr>
			</thead>
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
					<tr class="">
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
			<tr class="" style="height:50px;">
				<td class="first xleft" colspan="7">&nbsp;</td>
			</tr>
			<tfoot>
				<tr style="border-top:1px solid #666;">
					<td class="first xright xbold" colspan="3" style="border-top:1px solid #666; font-size:14px;">TOTAL </td>
					<td class="xcenter xbold" style="border-top:1px solid #666; font-size:14px;"><?php echo $so_data['so_total_qty']; ?></td>
					<td class="xright xbold" colspan="2" style="border-top:1px solid #666; font-size:14px;"><?php echo priceFormat($total_discount); ?></td>
					<td class="xright xbold" style="border-top:1px solid #666; font-size:14px;"><?php echo priceFormat($so_sub_total); ?></td>
				</tr>
				<tr style="border-top:1px solid #666;">
					<td class="first xright xbold" colspan="2" style="border-top:1px solid #666; font-size:14px;">
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
					<td class="xright xbold" style="border-top:1px solid #666; font-size:14px;">
						<?php
						if($so_data['so_shipping']){
							echo 'SHIPPING:'.priceFormat($so_data['so_shipping']);
							$so_total_price += $so_data['so_shipping'];
						}else{
							echo '&nbsp;';
						}
						?>
					</td>
					<td class="xright xbold" colspan="2" style="border-top:1px solid #666; font-size:14px;">
						<?php
						if($so_data['so_dp']){
							echo 'DP:'.priceFormat($so_data['so_dp']);
							$so_total_price += $so_data['so_dp'];
						}else{
							echo '&nbsp;';
						}
						?>
					</td>
					<td class="xright xbold" style="border-top:1px solid #666; font-size:14px;">GRAND TOTAL </td>
					<td class="xright xbold" style="border-top:1px solid #666; font-size:14px;"><?php echo priceFormat($so_total_price);?></td>
				</tr>
			</tfoot>			
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