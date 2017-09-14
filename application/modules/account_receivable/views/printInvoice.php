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
		$set_width = 850;
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
						<td class="f14 xbold" width="50">Nomor</td>
						<td class="f14 xbold" width="5">:</td>
						<td class="f14 xbold"><?php echo $invoice_data['invoice_no']; ?></td>
					</tr>
					<tr>
						<td>Tanggal</td>
						<td>:</td>
						<td><?php echo date("d-m-Y", strtotime($invoice_data['invoice_date'])); ?></td>
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
				<td class="xleft" width="100">AR NO</td>
				<td class="xleft" width="140">NO ORDER</td>
				<td class="xleft" width="80">TGL.ORDER</td>
				<td class="xleft" width="180">NAMA/CUSTOMER</td>
				<td class="xcenter" width="120">JUMLAH TAGIHAN</td>
				<td class="xleft" width="200">KETERANGAN</td>
			</tr>
			
			<?php
			if(!empty($invoice_detail)){
			
				$no = 1;
				foreach($invoice_detail as $det){
					?>
					<tr class="tbl-data">
						<td class="first xleft"><?php echo $no; ?></td>
						<td class="xleft"><?php echo $det['ar_no']; ?></td>
						<td class="xleft"><?php echo $det['no_ref']; ?></td>
						<td class="xleft"><?php echo $det['ar_date']; ?></td>
						<td class="xleft"><?php echo $det['ar_name']; ?></td>
						<td class="xright">Rp. <?php echo priceFormat($det['total_tagihan']); ?></td>
						<td class="xleft"><?php echo $det['ar_notes']; ?></td>
					</tr>
					<?php	

					$no++;
				}
				
				?>
				<tr class="tbl-total">
					<td class="first xright" colspan="5">TOTAL </td>
					<td class="xright">Rp. <?php echo priceFormat($invoice_data['total_tagihan']);?></td>
					<td class="xright">&nbsp;</td>
				</tr>	
				<?php
			}
			?>

					
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
			?>
			
			<div class="fleft" style="width:300px;">
				<br/>
				<b>JATUH TEMPO: </b><b><font color="red"><?php echo date("d-m-Y", strtotime($invoice_data['tanggal_jatuh_tempo'])); ?></font></b>
				<br/>
				<br/>
				<div style="font-size:10px;">
				
				</div>
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