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
		$set_width = 1250;
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<div class="fleft" style="width:360px; margin-right:10px;">
			<h1>STOCK OPNAME</h1>
		
			<table>
				<tr class="f14 xbold">
					<td class="f14 xbold" width="50">STO No</td>
					<td class="f14 xbold" width="5">:</td>
					<td class="f14 xbold"><?php echo $sto_data['sto_number']; ?></td>
				</tr>
				
			</table>
		</div>
		<div class="fright" style="width:230px;">
			<table>
				<tr>
					<td>DATE</td>
					<td>:</td>
					<td><?php echo date("d/m/Y", strtotime($sto_data['sto_date'])); ?></td>
				</tr>
				<tr>
					<td>FROM</td>
					<td>:</td>
					<td><?php echo $sto_data['storehouse_name']; ?></td>
				</tr>
			</table>
		</div>
		<div class="fclear"></div>
		<br/>
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<tr class="tbl-header">
				<td class="first xleft" width="50">NO</td>
				<td class="xleft" width="200">ITEM</td>
				<td class="xcenter" width="100">CURRENT QTY</td>
				<td class="xcenter" width="100">REAL QTY</td>
				<td class="xcenter" width="100">GAP QTY</td>
				<td class="xleft" width="100">UNIT</td>
				<td class="xright" width="100">LAST IN</td>
				<td class="xright" width="100">TOTAL LAST IN</td>
				<td class="xright" width="100">AVERAGE</td>
				<td class="xright" width="100">TOTAL AVERAGE</td>
				<td class="xleft" width="200">NOTE</td>
			</tr>
			
			<?php
			if(!empty($sto_detail)){
			
				$no = 1;
				$total_qty_awal = 0;
				$total_qty_fisik = 0;
				$total_qty_selisih = 0;
				$total_last_in = 0;
				$total_average = 0;
				foreach($sto_detail as $det){
					?>
					<tr class="tbl-data">
						<td class="first xleft"><?php echo $no; ?></td>
						<td class="xleft"><?php echo $det['item_name']; ?></td>
						<td class="xcenter"><?php echo $det['jumlah_awal']; ?></td>
						<td class="xcenter"><?php echo $det['jumlah_fisik']; ?></td>
						<td class="xcenter"><?php echo $det['selisih']; ?></td>
						<td class="xleft"><?php echo $det['unit_name']; ?></td>
						<td class="xright"><?php echo priceFormat($det['last_in']); ?></td>
						<td class="xright"><?php echo priceFormat($det['last_in']*$det['jumlah_fisik']); ?></td>
						<td class="xright"><?php echo priceFormat($det['current_hpp_avg']); ?></td>
						<td class="xright"><?php echo priceFormat($det['current_hpp_avg']*$det['jumlah_fisik']); ?></td>
						<td class="xleft"><?php echo $det['description']; ?></td>
					</tr>
					<?php	
					$total_qty_awal += $det['jumlah_awal'];
					$total_qty_fisik += $det['jumlah_fisik'];
					$total_qty_selisih += $det['selisih'];
					$total_last_in += ($det['last_in']*$det['jumlah_fisik']);
					$total_average += (($det['current_hpp_avg']*$det['jumlah_fisik']));
					$no++;
				}
				
				?>
				<tr class="tbl-total">
					<td class="first xright" colspan="2"> TOTAL </td>
					<td class="xcenter"><?php echo $total_qty_awal; ?></td>
					<td class="xcenter"><?php echo $total_qty_fisik; ?></td>
					<td class="xcenter"><?php echo $total_qty_selisih; ?></td>
					<td class="xright">&nbsp;</td>
					<td class="xright"><?php echo $total_last_in; ?></td>
					<td class="xright">&nbsp;</td>
					<td class="xright"><?php echo $total_average; ?></td>
					<td class="xcenter">&nbsp;</td>
					<td class="xcenter">&nbsp;</td>
				</tr>
				<?php	
			}
			?>
			
		</table>
		<br/>
		<br/>
		<?php
			if(!empty($sto_data['sto_memo'])){
		?>
			<div class="fleft" style="width:250px; padding:10px; border:1px solid #d8d8d8;">
				<b>Memo:</b><br/>
				<?php echo $sto_data['sto_memo']; ?>
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
					<td class="xcenter">&nbsp;<br/><br/><br/></td>
					<td class="xcenter">Stock Opname By<br/><br/><br/><br/></td>
				</tr>
				<tr class="tbl-footer">
					<td class="xcenter">
						
						&nbsp;
						
					</td>
					<td class="xcenter">
						
						<?php
						if(!empty($sto_data['createdby'])){
							echo $sto_data['createdby'];
						}else{
							echo '___________________';
						}
						?>
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