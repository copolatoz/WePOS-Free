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
		$set_width = 780;
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<div class="fleft" style="width:260px; margin-right:10px;">
			<div class="logo">
				
				<img height="80" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>">
				
				<table>
					<tr class="f14 xbold">
						<td class="f14 xbold" colspan="3">
						<?php
						if(!empty($client['client_address'])){
							echo $client['client_address'];
						}else{
							echo '&nbsp;';
						}
						?>
						</td>
					</tr>
					<tr class="f14 xbold">
						<td class="f14 xbold" width="90">No Invoice</td>
						<td class="f14 xbold" width="5">:</td>
						<td class="f14 xbold"><?php echo $invoice_data['invoice_no']; ?></td>
					</tr>
					<tr>
						<td class="f14 xbold"><font color="red">Jatuh Tempo</font></td>
						<td class="f14 xbold"><font color="red">:</font></td>
						<td class="f14 xbold"><font color="red"><?php echo date("d-m-Y", strtotime($invoice_data['tanggal_jatuh_tempo'])); ?></font></td>
					</tr>
					<tr>
						<td class="f14 xbold"><font color="blue">Total Tagihan</font></td>
						<td class="f14 xbold"><font color="blue">:</font></td>
						<td class="f14 xbold"><font color="blue"><?php echo 'Rp. '.priceFormat($invoice_data['total_tagihan']); ?></font></td>
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
			<br/>
			<br/>
			<br/>
			<div class="title_report xcenter">
				<?php echo $report_name; ?>
			</div>
			<br/>
			<div class="xbox" style="padding:10px;">
				<table>
					<tr>
						<td colspan="5">Kepada,</td>
					</tr>
					
					<?php
					if(!empty($invoice_data['customer_name'])){
						
						if(empty($invoice_data['customer_phone'])){
							$invoice_data['customer_phone'] = '-';
						}
						?>
						<tr>
							<td colspan="5" class="f14 xbold"><?php echo $invoice_data['customer_name']; ?></td>
						</tr>
						<tr>
							<td colspan="5"><div style="margin-bottom:10px;"><?php echo $invoice_data['customer_address']; ?></div></td>
						</tr>
						<tr>
							<td colspan="5"><div style="margin-bottom:10px;">Phone: <?php echo $invoice_data['customer_phone']; ?></div></td>
						</tr>
						<?php
					}else{
						
						if(empty($invoice_data['invoice_phone'])){
							$invoice_data['invoice_phone'] = '-';
						}
						
						?>
						<tr>
							<td colspan="5" class="f14 xbold"><?php echo $invoice_data['invoice_name']; ?></td>
						</tr>
						<tr>
							<td colspan="5"><div style="margin-bottom:10px;"><?php echo $invoice_data['invoice_address']; ?></div></td>
						</tr>
						<tr>
							<td colspan="5"><div style="margin-bottom:10px;">Phone: <?php echo $invoice_data['invoice_phone']; ?></div></td>
						</tr>
						<?php
					}
					?>
					
				</table>
			</div>
		</div>
		<div class="fclear"></div>
		<br/>
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<tr class="tbl-header">
				<td class="first xleft" width="30">NO</td>
				<td class="xleft" width="100">NO.PEMBAYARAN</td>
				<td class="xleft" width="80">TANGGAL</td>
				<td class="xleft" width="150">No.BUKTI</td>
				<td class="xcenter" width="120">JUMLAH</td>
				<td class="xleft" width="100">NO.REF</td>
				<td class="xleft" width="200">KETERANGAN</td>
			</tr>
			
			<?php
			$total_pembayaran = 0;
			if(!empty($invoice_detail)){
			
				$no = 1;
				foreach($invoice_detail as $det){
					
					if(empty($det['pembayaran_notes'])){
						$det['pembayaran_notes'] = '&nbsp;';
					}
					
					$total_pembayaran += $det['pembayaran_total'];
					$pembayaran_date = date("d-m-Y", strtotime($det['pembayaran_date']));
					
					if(empty($det['no_jurnal'])){
						$det['no_jurnal'] = '-';
					}
					
					?>
					<tr class="tbl-data">
						<td class="first xleft"><?php echo $no; ?></td>
						<td class="xleft"><?php echo $det['pembayaran_no']; ?></td>
						<td class="xleft"><?php echo $pembayaran_date; ?></td>
						<td class="xleft"><?php echo $det['no_bukti']; ?></td>
						<td class="xright"><?php echo 'Rp. '.priceFormat($det['pembayaran_total']); ?></td>
						<td class="xleft"><?php echo $det['no_jurnal']; ?></td>
						<td class="xleft"><?php echo $det['pembayaran_notes']; ?></td>
					</tr>
					<?php	

					$no++;
				}
			
			}
			?>
			
			<tr class="tbl-total">
				<td class="first xright" colspan="4">TOTAL </td>
				<td class="xright"><?php echo 'Rp. '.priceFormat($total_pembayaran); ?></td>
				<td class="xleft" colspan="2">&nbsp;</td>
			</tr>
			
		</table>
		
		<div class="fleft" style="width:250px; margin-top:5px;">
			
			<?php
			if(!empty($invoice_data['invoice_notes'])){
			?>
				<div class="fleft" style="width:250px; padding:10px; border:1px solid #d8d8d8;">
					<b>Keterangan:</b><br/>
					<?php echo $invoice_data['invoice_notes']; ?>
				</div>
			<?php
				} 
				
				
			$status_kb = '<font color="red">Belum Lunas</font>';
			$invoice_data['sisa_tagihan'] = $invoice_data['total_tagihan'] - $invoice_data['total_bayar'];
			if($invoice_data['total_bayar'] >= $invoice_data['total_tagihan']){
				$invoice_data['sisa_tagihan'] = 0;
				$status_kb = '<font color="green">Lunas</font>';
			}
			?>
			
			<div class="fleft" style="width:300px; font-size:10px;">
				<br/>
				<div class="f14 xbold">Status Invoice: <?php echo $status_kb; ?></div>
				<?php
				if($invoice_data['sisa_tagihan'] > 0){
					?>
					<div class="f14 xbold">Sisa Tagihan: <font color="blue"><?php echo 'Rp. '.priceFormat($invoice_data['sisa_tagihan']); ?></font></div>
					<?php
				}
				?>
				
			</div>
		</div>
		<div class="fright" style="width:230px;">
			
			<br/>
			<br/>
			<table align="center">
				<tr class="tbl-footer">
					<td class="xcenter">Approved by:<br/><br/><br/><br/><br/></td>
				</tr>
				<tr class="tbl-footer">
					<td class="xcenter">----------------------------</td>
				</tr>
			</table>
			
		</div>
		<div class="fclear"></div>
		<br/>
		<br/>
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