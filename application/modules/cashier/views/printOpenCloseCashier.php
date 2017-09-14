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
		$set_width = 700;
		$user_shift = 'Morning';
		if($openClose_data['user_shift'] == 2){
			$user_shift = 'Evening';
		}
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<div class="fleft" style="width:260px; margin-right:10px;">
			<h1><?php echo $report_name;?></h1>
		
			<table>
				<tr class="f14 xbold">
					<td class="f14 xbold" width="40">KASIR</td>
					<td class="f14 xbold" width="5">:</td>
					<td class="f14 xbold"><?php echo $openClose_data['kasir_user']; ?></td>
				</tr>
				<tr>
					<td>TANGGAL</td>
					<td>:</td>
					<td><?php echo date("d/m/Y", strtotime($openClose_data['tanggal_shift'])); ?></td>
				</tr>
				<tr>
					<td>JAM</td>
					<td>:</td>
					<td><?php echo $openClose_data['jam_shift'].' ('.$user_shift.')'; ?></td>
				</tr>
			</table>
		</div>
		<div class="fright" style="width:330px; margin-top:20px;">
			<table width="330">
				<tr>
					<td width="150" class="f14 xbold">JUMLAH UANG KERTAS</td>
					<td width="5" class="f14 xbold">:</td>
					<td class="f14 xbold"> Rp. <?php echo priceFormat($openClose_data['jumlah_uang_kertas']); ?></td>
				</tr>
				<tr>
					<td class="f14 xbold">JUMLAH UANG KOIN</td>
					<td class="f14 xbold">:</td>
					<td class="f14 xbold">  Rp. <?php echo priceFormat($openClose_data['jumlah_uang_koin']); ?></td>
				</tr>
				<tr>
					<td class="f14 xbold">JUMLAH UANG</td>
					<td class="f14 xbold">:</td>
					<td class="f14 xbold">  Rp. <?php echo priceFormat($openClose_data['jumlah_uang_kertas']+$openClose_data['jumlah_uang_koin']); ?></td>
				</tr>
			</table>
		</div>
		<div class="fclear"></div>
		<br/>
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<tr class="tbl-header">
				<td class="first xcenter" width="350" colspan="3" rowspan="1">UANG KERTAS</td>
				<td class="xcenter" width="350"  colspan="3" rowspan="1">UANG KOIN</td>
			</tr>
			<tr class="tbl-header">
				<td class="first xleft" width="150">NOMINAL</td>
				<td class="xcenter" width="50">QTY</td>
				<td class="xright" width="150">JUMLAH</td>
				<td class="xleft" width="150">NOMINAL</td>
				<td class="xcenter" width="50">QTY</td>
				<td class="xright" width="150">JUMLAH</td>
			</tr>
			<tr class="tbl-data">
				<td class="first xleft" width="100">Rp. 100.000 </td>
				<td class="xcenter"><?php echo $openClose_data['uang_kertas_100000']; ?></td>
				<td class="xright">Rp. <?php echo priceFormat($openClose_data['uang_kertas_100000']*100000); ?></td>
				<td class="xleft">Rp. 1000</td>
				<td class="xcenter"><?php echo $openClose_data['uang_koin_1000']; ?></td>
				<td class="xright">Rp. <?php echo priceFormat($openClose_data['uang_koin_1000']*1000); ?></td>
			</tr>
			<tr class="tbl-data">
				<td class="first xleft" width="100">Rp. 50.000 </td>
				<td class="xcenter"><?php echo $openClose_data['uang_kertas_50000']; ?></td>
				<td class="xright">Rp. <?php echo priceFormat($openClose_data['uang_kertas_50000']*50000); ?></td>
				<td class="xleft">Rp. 500</td>
				<td class="xcenter"><?php echo $openClose_data['uang_koin_500']; ?></td>
				<td class="xright">Rp. <?php echo priceFormat($openClose_data['uang_koin_500']*500); ?></td>
			</tr>
			<tr class="tbl-data">
				<td class="first xleft" width="100">Rp. 20.000 </td>
				<td class="xcenter"><?php echo $openClose_data['uang_kertas_20000']; ?></td>
				<td class="xright">Rp. <?php echo priceFormat($openClose_data['uang_kertas_20000']*20000); ?></td>
				<td class="xleft">Rp. 200</td>
				<td class="xcenter"><?php echo $openClose_data['uang_koin_200']; ?></td>
				<td class="xright">Rp. <?php echo priceFormat($openClose_data['uang_koin_200']*200); ?></td>
			</tr>
			<tr class="tbl-data">
				<td class="first xleft" width="100">Rp. 10.000 </td>
				<td class="xcenter"><?php echo $openClose_data['uang_kertas_10000']; ?></td>
				<td class="xright">Rp. <?php echo priceFormat($openClose_data['uang_kertas_10000']*10000); ?></td>
				<td class="xleft">Rp. 100</td>
				<td class="xcenter"><?php echo $openClose_data['uang_koin_100']; ?></td>
				<td class="xright">Rp. <?php echo priceFormat($openClose_data['uang_koin_100']*100); ?></td>
			</tr>
			<tr class="tbl-data">
				<td class="first xleft" width="100">Rp. 5.000 </td>
				<td class="xcenter"><?php echo $openClose_data['uang_kertas_5000']; ?></td>
				<td class="xright">Rp. <?php echo priceFormat($openClose_data['uang_kertas_5000']*5000); ?></td>
				<td class="xleft">&nbsp;</td>
				<td class="xcenter">&nbsp;</td>
				<td class="xright">&nbsp;</td>
			</tr>
			<tr class="tbl-data">
				<td class="first xleft" width="100">Rp. 2.000 </td>
				<td class="xcenter"><?php echo $openClose_data['uang_kertas_2000']; ?></td>
				<td class="xright">Rp. <?php echo priceFormat($openClose_data['uang_kertas_2000']*2000); ?></td>
				<td class="xleft">&nbsp;</td>
				<td class="xcenter">&nbsp;</td>
				<td class="xright">&nbsp;</td>
			</tr>
			<tr class="tbl-data">
				<td class="first xleft" width="100">Rp. 1.000 </td>
				<td class="xcenter"><?php echo $openClose_data['uang_kertas_1000']; ?></td>
				<td class="xright">Rp. <?php echo priceFormat($openClose_data['uang_kertas_1000']*1000); ?></td>
				<td class="xleft">&nbsp;</td>
				<td class="xcenter">&nbsp;</td>
				<td class="xright">&nbsp;</td>
			</tr>
			
			<tr class="tbl-total">
				<td class="first xright" colspan="2"> JUMLAH UANG KERTAS </td>
				<td class="xright"> Rp. <?php echo priceFormat($openClose_data['jumlah_uang_kertas']); ?></td>
				<td class="xright" colspan="2"> JUMLAH UANG KOIN </td>
				<td class="xright"> Rp. <?php echo priceFormat($openClose_data['jumlah_uang_koin']); ?></td>				
			</tr>
			
		</table>
		<br/>
		<div class="fright" style="width:200px;">
			<table>
				<tr class="tbl-footer">
					<td class="xcenter" width="200"><?php echo $report_place_default.', '.date("d")." ".get_month(date("m"))." ".date("Y"); ?></td>
				</tr>
				<tr class="tbl-footer">
					<td class="xcenter">Supervisor<br/><br/><br/></td>
				</tr>
				<tr class="tbl-footer">
					<td class="xcenter"><?php echo $openClose_data['spv_user']; ?></td>
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