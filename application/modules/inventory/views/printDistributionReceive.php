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
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<div class="fleft" style="width:360px; margin-right:10px;">
			<h1>DISTRIBUTION (RECEIVE)</h1>
		
			<table>
				<tr class="f14 xbold">
					<td class="f14 xbold" width="40">NO</td>
					<td class="f14 xbold" width="5">:</td>
					<td class="f14 xbold"><?php echo $dis_data['dis_number']; ?></td>
				</tr>
				
			</table>
		</div>
		<div class="fright" style="width:230px;">
			<table>
				<tr>
					<td>DATE</td>
					<td>:</td>
					<td><?php echo date("d/m/Y", strtotime($dis_data['dis_date'])); ?></td>
				</tr>
				<tr>
					<td>FROM</td>
					<td>:</td>
					<td><?php echo $dis_data['delivery_from_name']; ?></td>
				</tr>
				<tr>
					<td>TO</td>
					<td>:</td>
					<td><?php echo $dis_data['delivery_to_name']; ?></td>
				</tr>
			</table>
		</div>
		<div class="fclear"></div>
		<br/>
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<tr class="tbl-header">
				<td class="first xleft" width="30">NO</td>
				<td class="xleft" width="100">KODE</td>
				<td class="xleft" >NAMA BARANG</td>
				<td class="xcenter" width="100">QTY KIRIM</td>
				<td class="xcenter" width="100">QTY TERIMA</td>
				<td class="xcenter" width="150">UNIT</td>
			</tr>
			
			<?php
			if(!empty($dis_detail)){
			
				$no = 1;
				$total_qty = 0;
				$total_qty_terima = 0;
				foreach($dis_detail as $det){
					?>
					<tr class="tbl-data">
						<td class="first xleft"><?php echo $no; ?></td>
						<td class="xleft"><?php echo $det['item_code']; ?></td>
						<td class="xleft"><?php echo $det['item_name']; ?></td>
						<td class="xcenter"><?php echo $det['disd_dikirim']; ?></td>
						<td class="xcenter"><?php echo $det['disd_diterima']; ?></td>
						<td class="xcenter"><?php echo $det['unit_name']; ?></td>
					</tr>
					<?php	
					$total_qty += $det['disd_dikirim'];
					$total_qty_terima += $det['disd_diterima'];
					$no++;
				}
				
				?>
				<tr class="tbl-total">
					<td class="first xright" colspan="3"> TOTAL </td>
					<td class="xcenter"><?php echo $total_qty; ?></td>
					<td class="xcenter"><?php echo $total_qty_terima; ?></td>
					<td class="xcenter">&nbsp;</td>
				</tr>
				<?php	
			}
			?>
			
		</table>
		<br/>
		<br/>
		<?php
			if(!empty($dis_data['dis_memo'])){
		?>
			<div class="fleft" style="width:250px; padding:10px; border:1px solid #d8d8d8;">
				<b>Memo:</b><br/>
				<?php echo $dis_data['dis_memo']; ?>
			</div>
		<?php
			} 
		?>
		<div class="fright" style="width:400px;">
			<table>
				<tr class="tbl-footer">
					<td class="xcenter" width="200">&nbsp;</td>
					<td class="xcenter" width="200"><?php echo $report_place_default.', '.date("d")." ".get_month(date("m"))." ".date("Y"); ?></td>
				</tr>
				<tr class="tbl-footer">
					<td class="xcenter">di Terima Oleh<br/><?php echo $dis_data['delivery_to_name']; ?><br/><br/><br/></td>
					<td class="xcenter">di Kirim Oleh<br/><?php echo $dis_data['delivery_from_name']; ?><br/><br/><br/></td>
				</tr>
				<tr class="tbl-footer">
					<td class="xcenter">
						
						<?php
						if(!empty($dis_data['dis_receiver'])){
							echo $dis_data['dis_receiver'];
						}else{
							echo '___________________';
						}
						?>
						
					</td>
					<td class="xcenter">
						
						<?php echo $dis_data['dis_deliver']; ?>
					</td>
				</tr>
			</table>
		</div>
		<div class="fclear"></div>
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