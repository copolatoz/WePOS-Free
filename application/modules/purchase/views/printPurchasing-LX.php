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
		$set_width = 800;
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>; line-height: 14px;">
		
		<?php
		/*if(!empty($client['client_logo'])){
			?>
			<img height="100" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $client['client_logo']; ?>">
			<?php
		}*/
		?>
		
		<table width="<?php echo $set_width; ?>" style="font-size:14px; margin:0px;">

			<!-- HEADER -->
			<thead>
				<tr style="border-bottom:1px solid #666;">
					<td colspan="2"class="f14 xbold" style="border-bottom:1px solid #666; font-size:12px;">
					<?php
						if(!empty($client['client_name'])){
							echo '<font style="font-size:16px;"><b>'.$client['client_name'].'</b></font>';
						}
					?>
					</td>
					<td colspan="3"class="f14 xbold xcenter" style="border-bottom:1px solid #666; font-size:16px;">
					<?php echo $report_name;?>
					</td>
					<td colspan="3"class="f14 xright" style="border-bottom:1px solid #666; font-size:12px;">
					Tgl.cetak: <?php echo date("d-m-Y H:i:s"); ?>, By: <?php echo $user_fullname; ?>
					</td>
				</tr>
				<tr>
					<td class="f14" colspan="7">
						<table style="margin:0px;">
							<tr>
								<td width="60" class="f14">No.Faktur</td>
								<td width="5" class="f14" >:</td>
								<td width="380" class="f14"><b>
								<?php echo $purchasing_data['purchasing_number']; 
								if($purchasing_data['purchasing_status'] != 'done'){
									echo ' ('.strtoupper($purchasing_data['purchasing_status']).')';
								}
								?></b>
								</td>
								<td width="50" class="f14">Supplier</td>
								<td width="5" class="f14">:</td>
								<td class="f14"><?php 
									echo substr($purchasing_data['supplier_name'],0,30);
								?></td>
							</tr>
							<tr>
								<td class="f14">Tgl.Faktur</td>
								<td class="f14">:</td>
								<td class="f14"><?php echo date("d/m/Y", strtotime($purchasing_data['purchasing_date'])); ?></td>
								<td class="f14">Alamat</td>
								<td class="f14">:</td>
								<td rowspan="2" class="f14">
								<?php 
									if(!empty($purchasing_data['supplier_address'])){
										echo $purchasing_data['supplier_address'];
									}
									if(!empty($purchasing_data['supplier_city'])){
										echo ', '.$purchasing_data['supplier_address'];
									}
									if(!empty($purchasing_data['supplier_phone'])){
										echo ' / '.$purchasing_data['supplier_phone'];
									}
									if(!empty($purchasing_data['purchasing_memo'])){
										echo '<br/>';
										echo 'Memo: '.$purchasing_data['purchasing_memo'];
									}
								?>
								</td>
							</tr>
							<tr>
								<td class="f14">Nota/S.Jalan</td>
								<td class="f14">:</td>
								<td class="f14"><?php echo $purchasing_data['supplier_invoice'];
								if($purchasing_data['purchasing_payment'] == 'credit'){
									echo ' / '.strtoupper($purchasing_data['purchasing_payment']).' ('.$purchasing_data['purchasing_termin'].' H)';
								}else{
									echo ' / '.$purchasing_data['purchasing_payment'];
								}
								?></td>
								<td colspan="3">&nbsp;</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr class="" style="border-top:1px solid #666; border-bottom:1px solid #666;">
					<td class="first xleft xbold" width="40">NO</td>
					<td class="xleft xbold" width="100">KODE</td>
					<td class="xleft xbold">NAMA BARANG</td>
					<td class="xcenter xbold" width="50">QTY</td>
					<td class="xcenter xbold" width="50">UNIT</td>
					<td class="xright xbold" width="80">HRG.SATUAN</td>
					<td class="xright xbold" width="80">POTONGAN</td>
					<td class="xright xbold" width="100">SUB TOTAL</td>
				</tr>
			</thead>
			<?php
			$total_detail_def = 16;
			if(!empty($purchasing_detail)){
				
				$jml_page = ceil(count($purchasing_detail)/$total_detail_def);
				$page_show_div = count($purchasing_detail)%$total_detail_def;
				
				$purchasing_sub_total = 0;
				$total_discount = 0;
				$no = 1;
				$no_continues = 1;
				foreach($purchasing_detail as $det){
					
					$total_discount += $det['purchasing_detail_potongan'];
					$purchasing_sub_total += ($det['purchasing_detail_total']);
					
				}
				
				foreach($purchasing_detail as $det){
					
					$now_page = ceil($no_continues/$total_detail_def);
					if($no == 1){
						echo '<tbody>';
					}
					
					$detail_text = strtoupper($det['item_name']);
					if(!empty($printdetail) AND !empty($data_kodeunik_varian[$det['id']])){
						$detail_text .= '<i>';
						foreach($data_kodeunik_varian[$det['id']] as $varian => $dtKodeUnik){
							$detail_text .= '<br/>'.$varian.': '.implode(", ", $dtKodeUnik);
						}
						$detail_text .= '</i>';
					}
					
					?>
					<tr class="">
						<td class="first xleft"><?php echo $no_continues; ?></td>
						<td class="xleft"><?php echo strtoupper($det['item_code']); ?></td>
						<td class="xleft"><?php echo $detail_text; ?></td>
						<td class="xcenter"><?php echo $det['purchasing_detail_qty']; ?></td>
						<td class="xcenter"><?php echo strtoupper($det['unit_code']); ?></td>
						<td class="xright"><?php echo priceFormat($det['purchasing_detail_purchase']); ?></td>
						<td class="xright"><?php echo priceFormat($det['purchasing_detail_potongan']); ?></td>
						<td class="xright"><?php echo priceFormat($det['purchasing_detail_total']); ?></td>
					</tr>
					<?php	
					if($no == $total_detail_def){
						$no = 0;
						?>
						<!--<tfoot>-->
							<tr style="border-top:1px solid #666;">
								<td class="first xright xbold" colspan="3" style="border-top:1px solid #666; font-size:14px;">TOTAL QTY</td>
								<td class="xcenter xbold" style="border-top:1px solid #666; font-size:14px;"><?php echo $purchasing_data['purchasing_total_qty']; ?></td>
								<td class="xright xright xbold" style="border-top:1px solid #666; font-size:14px;" colspan="2">&nbsp;</td>
								<td class="xright xbold" style="border-top:1px solid #666; font-size:14px;"><?php echo priceFormat($total_discount); ?></td>
								<td class="xright xbold" style="border-top:1px solid #666; font-size:14px;"><?php echo priceFormat($purchasing_sub_total); ?></td>
							</tr>
							
							<tr>
								<td class="first xleft" colspan="5" style="border-top:1px solid #666; font-size:14px;">
									<?php
									$total_footer = 1;
									$purchasing_total_price = $purchasing_sub_total;
									
									$text_additional = '';
									if(!empty($purchasing_data['purchasing_dp'])){
										$purchasing_total_price -= $purchasing_data['purchasing_dp'];
										$text_additional = 'DP';
										$total_footer++;
									}
									if(!empty($purchasing_data['purchasing_tax'])){
										$purchasing_total_price += $purchasing_data['purchasing_tax'];
										$total_footer++;
										if(!empty($text_additional)){
											$text_additional .= '<br/>PAJAK';
										}else{
											$text_additional = 'PAJAK';
										}
									}
									if(!empty($purchasing_data['purchasing_shipping'])){
										$purchasing_total_price += $purchasing_data['purchasing_shipping'];
										$total_footer++;
										if(!empty($text_additional)){
											$text_additional .= '<br/>SHIPPING';
										}else{
											$text_additional = 'SHIPPING';
										}
									}
									if(!empty($text_additional)){
										$text_additional .= '<br/>GRAND TOTAL';
									}else{
										$text_additional = 'GRAND TOTAL';
									}
									
									?>
									<!--<i>SERATUS TUJUH BELAS JUTA ENAM RATUS RIBU RUPIAH</i>-->
								</td>
								<td class="xright xbold" style="border-top:1px solid #666; font-size:14px;" rowspan="2" colspan="2">
								<?php
									echo $text_additional;
									echo str_repeat('<br/>',(5-$total_footer));
								?>
								</td>
								<td class="xright xbold" style="border-top:1px solid #666; font-size:14px;" rowspan="2">
								<?php 
									$text_additional_nominal = '';
									if(!empty($purchasing_data['purchasing_dp'])){
										$text_additional_nominal = priceFormat($purchasing_data['purchasing_dp']);
									}
									if(!empty($purchasing_data['purchasing_tax'])){
										if(!empty($text_additional_nominal)){
											$text_additional_nominal .= '<br/>'.priceFormat($purchasing_data['purchasing_tax']);
										}else{
											$text_additional_nominal = priceFormat($purchasing_data['purchasing_tax']);
										}
									}
									if(!empty($purchasing_data['purchasing_shipping'])){
										if(!empty($text_additional_nominal)){
											$text_additional_nominal .= '<br/>'.priceFormat($purchasing_data['purchasing_shipping']);
										}else{
											$text_additional_nominal = priceFormat($purchasing_data['purchasing_shipping']);
										}
									}
									if(!empty($text_additional_nominal)){
										$text_additional_nominal .= '<br/>'.priceFormat($purchasing_total_price);
									}else{
										$text_additional_nominal = priceFormat($purchasing_total_price);
									}
									echo $text_additional_nominal;
									echo str_repeat('<br/>',(5-$total_footer));
								?>
								</td>
							</tr>
							<tr>
								<td class="first" colspan="5">
									<!--<table style="text-align:center; margin:5px 0px 0px;">
										<tr>
											<td width="150" class="f14">Penerima</td>
											<td width="150" class="f14">Admin</td>
											<td width="150" class="f14">Hormat Kami</td>
										</tr>
									</table>-->
								</td>
								<td colspan="3">
								</td>
							</tr>
						<!--</tfoot>-->
						
						<tr class="">
							<td class="first xleft" colspan="8">Halaman <?php echo $now_page.'/'.$jml_page; ?></td>
						</tr>
						</tbody>
						<?php
						$cek_sisa_list = false;
					}else{
						$cek_sisa_list = true;
					}
					
					$no++;
					$no_continues++;
				}
				
				if($page_show_div > 0 AND $cek_sisa_list == true){
					$selisih_baris = $total_detail_def-$page_show_div;
					for($i=1; $i<=$selisih_baris; $i++){
						?>
						<tr class="">
							<td class="first xleft" colspan="8">&nbsp;</td>
						</tr>
						<?php
					}
					echo '</tbody>';
					?>
					<!--<tfoot>-->
						<tr style="border-top:1px solid #666;">
							<td class="first xright xbold" colspan="3" style="border-top:1px solid #666; font-size:14px;">TOTAL QTY</td>
							<td class="xcenter xbold" style="border-top:1px solid #666; font-size:14px;"><?php echo $purchasing_data['purchasing_total_qty']; ?></td>
							<td class="xright xright xbold" style="border-top:1px solid #666; font-size:14px;" colspan="2">&nbsp;</td>
							<td class="xright xbold" style="border-top:1px solid #666; font-size:14px;"><?php echo priceFormat($total_discount); ?></td>
							<td class="xright xbold" style="border-top:1px solid #666; font-size:14px;"><?php echo priceFormat($purchasing_sub_total); ?></td>
						</tr>
						
						<tr>
							<td class="first xleft" colspan="5" style="border-top:1px solid #666; font-size:14px;">
								<?php
								
								$total_footer = 1;
								$purchasing_total_price = $purchasing_sub_total;
								
								$text_additional = '';
								if(!empty($purchasing_data['purchasing_dp'])){
									$purchasing_total_price -= $purchasing_data['purchasing_dp'];
									$total_footer += 1;
									$text_additional = 'DP';
								}
								if(!empty($purchasing_data['purchasing_tax'])){
									$purchasing_total_price += $purchasing_data['purchasing_tax'];
									$total_footer += 1;
									if(!empty($text_additional)){
										$text_additional .= '<br/>PAJAK';
									}else{
										$text_additional = 'PAJAK';
									}
								}
								if(!empty($purchasing_data['purchasing_shipping'])){
									$purchasing_total_price += $purchasing_data['purchasing_shipping'];
									$total_footer += 1;
									if(!empty($text_additional)){
										$text_additional .= '<br/>SHIPPING';
									}else{
										$text_additional = 'SHIPPING';
									}
								}
								if(!empty($text_additional)){
									$text_additional .= '<br/>GRAND TOTAL';
								}else{
									$text_additional = 'GRAND TOTAL';
								}
								
								?>
								<!--<i>SERATUS TUJUH BELAS JUTA ENAM RATUS RIBU RUPIAH</i>-->
							</td>
							<td class="xright xbold" style="border-top:1px solid #666; font-size:14px;" rowspan="2" colspan="2">
							<?php
								echo $text_additional;
								echo str_repeat('<br/>',(5-$total_footer));
							?>
							</td>
							<td class="xright xbold" style="border-top:1px solid #666; font-size:14px;" rowspan="2">
							<?php 
								$text_additional_nominal = '';
								if(!empty($purchasing_data['purchasing_dp'])){
									$text_additional_nominal = priceFormat($purchasing_data['purchasing_dp']);
								}
								if(!empty($purchasing_data['purchasing_tax'])){
									if(!empty($text_additional_nominal)){
										$text_additional_nominal .= '<br/>'.priceFormat($purchasing_data['purchasing_tax']);
									}else{
										$text_additional_nominal = priceFormat($purchasing_data['purchasing_tax']);
									}
								}
								if(!empty($purchasing_data['purchasing_shipping'])){
									if(!empty($text_additional_nominal)){
										$text_additional_nominal .= '<br/>'.priceFormat($purchasing_data['purchasing_shipping']);
									}else{
										$text_additional_nominal = priceFormat($purchasing_data['purchasing_shipping']);
									}
								}
								if(!empty($text_additional_nominal)){
									$text_additional_nominal .= '<br/>'.priceFormat($purchasing_total_price);
								}else{
									$text_additional_nominal = priceFormat($purchasing_total_price);
								}
								echo $text_additional_nominal;
								echo str_repeat('<br/>',(5-$total_footer));
							?>
							</td>
						</tr>
						<tr>
							<td class="first" colspan="5">
								<!--<table style="text-align:center; margin:5px 0px 0px;">
									<tr>
										<td width="150" class="f14">Penerima</td>
										<td width="150" class="f14">Admin</td>
										<td width="150" class="f14">Hormat Kami</td>
									</tr>
								</table>-->
							</td>
							<td colspan="3">
							</td>
						</tr>
					<!--</tfoot>-->
					
						<tr class="">
							<td class="first xleft" colspan="8">Halaman <?php echo $now_page.'/'.$jml_page; ?></td>
						</tr>
					<?php
				}
			
			}
			?>
			
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