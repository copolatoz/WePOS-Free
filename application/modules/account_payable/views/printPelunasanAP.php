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
						<td class="f14 xbold" width="90">No Kontrabon</td>
						<td class="f14 xbold" width="5">:</td>
						<td class="f14 xbold"><?php echo $kb_data['kb_no']; ?></td>
					</tr>
					<tr>
						<td class="f14 xbold"><font color="red">Jatuh Tempo</font></td>
						<td class="f14 xbold"><font color="red">:</font></td>
						<td class="f14 xbold"><font color="red"><?php echo date("d-m-Y", strtotime($kb_data['tanggal_jatuh_tempo'])); ?></font></td>
					</tr>
					<tr>
						<td class="f14 xbold"><font color="blue">Total Tagihan</font></td>
						<td class="f14 xbold"><font color="blue">:</font></td>
						<td class="f14 xbold"><font color="blue"><?php echo 'Rp. '.priceFormat($kb_data['total_tagihan']); ?></font></td>
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
					if(!empty($kb_data['supplier_name'])){
						
						if(empty($kb_data['supplier_phone'])){
							$kb_data['supplier_phone'] = '-';
						}
						?>
						<tr>
							<td colspan="5" class="f14 xbold"><?php echo $kb_data['supplier_name']; ?></td>
						</tr>
						<tr>
							<td colspan="5"><div style="margin-bottom:10px;"><?php echo $kb_data['supplier_address']; ?></div></td>
						</tr>
						<tr>
							<td colspan="5"><div style="margin-bottom:10px;">Phone: <?php echo $kb_data['supplier_phone']; ?></div></td>
						</tr>
						<?php
					}else{
						
						if(empty($kb_data['kb_phone'])){
							$kb_data['kb_phone'] = '-';
						}
						
						?>
						<tr>
							<td colspan="5" class="f14 xbold"><?php echo $kb_data['kb_name']; ?></td>
						</tr>
						<tr>
							<td colspan="5"><div style="margin-bottom:10px;"><?php echo $kb_data['kb_address']; ?></div></td>
						</tr>
						<tr>
							<td colspan="5"><div style="margin-bottom:10px;">Phone: <?php echo $kb_data['kb_phone']; ?></div></td>
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
				<td class="xleft" width="100">NO.PELUNASAN</td>
				<td class="xleft" width="80">TANGGAL</td>
				<td class="xleft" width="150">No.BUKTI</td>
				<td class="xcenter" width="120">JUMLAH</td>
				<td class="xleft" width="100">NO.REF</td>
				<td class="xleft" width="200">KETERANGAN</td>
			</tr>
			
			<?php
			$total_pelunasan = 0;
			if(!empty($kb_detail)){
			
				$no = 1;
				foreach($kb_detail as $det){
					
					if(empty($det['pelunasan_notes'])){
						$det['pelunasan_notes'] = '&nbsp;';
					}
					
					$total_pelunasan += $det['pelunasan_total'];
					$pelunasan_date = date("d-m-Y", strtotime($det['pelunasan_date']));
					
					if(empty($det['no_jurnal'])){
						$det['no_jurnal'] = '-';
					}
					
					?>
					<tr class="tbl-data">
						<td class="first xleft"><?php echo $no; ?></td>
						<td class="xleft"><?php echo $det['pelunasan_no']; ?></td>
						<td class="xleft"><?php echo $pelunasan_date; ?></td>
						<td class="xleft"><?php echo $det['no_bukti']; ?></td>
						<td class="xright"><?php echo 'Rp. '.priceFormat($det['pelunasan_total']); ?></td>
						<td class="xleft"><?php echo $det['no_jurnal']; ?></td>
						<td class="xleft"><?php echo $det['pelunasan_notes']; ?></td>
					</tr>
					<?php	

					$no++;
				}
			
			}
			?>
			
			<tr class="tbl-total">
				<td class="first xright" colspan="4">TOTAL </td>
				<td class="xright"><?php echo 'Rp. '.priceFormat($total_pelunasan); ?></td>
				<td class="xleft" colspan="2">&nbsp;</td>
			</tr>
			
		</table>
		
		<div class="fleft" style="width:250px; margin-top:5px;">
			
			<?php
			if(!empty($kb_data['kb_notes'])){
			?>
				<div class="fleft" style="width:250px; padding:10px; border:1px solid #d8d8d8;">
					<b>Keterangan:</b><br/>
					<?php echo $kb_data['kb_notes']; ?>
				</div>
			<?php
				} 
				
				
			$status_kb = '<font color="red">Belum Lunas</font>';
			$kb_data['sisa_tagihan'] = $kb_data['total_tagihan'] - $kb_data['total_bayar'];
			if($kb_data['total_bayar'] >= $kb_data['total_tagihan']){
				$kb_data['sisa_tagihan'] = 0;
				$status_kb = '<font color="green">Lunas</font>';
			}
			?>
			
			<div class="fleft" style="width:300px; font-size:10px;">
				<br/>
				<div class="f14 xbold">Status Kontrabon: <?php echo $status_kb; ?></div>
				<?php
				if($kb_data['sisa_tagihan'] > 0){
					?>
					<div class="f14 xbold">Sisa Tagihan: <font color="blue"><?php echo 'Rp. '.priceFormat($kb_data['sisa_tagihan']); ?></font></div>
					<?php
				}
				?>
				
				<br/>
				*Pelunasan Kontra Bon merupakan detail pelunasan yang sudah dilakukan
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