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
		<div class="fleft" style="width:260px; margin-right:10px;">
			<br>
			<img height="40" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>">
			
			<div class="headoffice xbold">
				<?php
				if(!empty($client['client_name'])){
					echo $client['client_name'];
				}
				?>
				<br>
				<?php
				if(!empty($client['client_address'])){
					echo $client['client_address'];
				}else{
					echo '&nbsp;';
				}
				?>
				<br>
				<br>
				
			</div>
		</div>
		<div class="fright" style="width:530px;">
			<div class="title_report xcenter">
				<?php echo $report_name;?>
			</div>
			
			<table>
				<tr>
					<td colspan="7" height="20px"> &nbsp; </td>
				</tr>
				<tr class="f14">
					<td class="f14 xbold" width="70">NO FAKTUR</td>
					<td class="f14 xbold" width="5">:</td>
					<td class="f14" width="150"><?php echo $purchasing_data['purchasing_number']; ?>
						<?php
						if($purchasing_data['purchasing_status'] != 'done'){
							echo ' ('.strtoupper($purchasing_data['purchasing_status']).')';
						}
						?>
					</td>
					<td class="f14 xbold" width="50">&nbsp;</td>
					<td class="f14 xbold" width="60">SUPPLIER</td>
					<td class="f14 xbold" width="5">:</td>
					<td class="f14"><?php echo $purchasing_data['supplier_name']; ?></td>
				</tr>
				<tr>
					<td class="f14 xbold">TANGGAL</td>
					<td class="f14 xbold">:</td>
					<td class="f14"><?php echo date("d/m/Y", strtotime($purchasing_data['purchasing_date'])); ?></td>
					<td class="f14 xbold">&nbsp;</td>
					<td class="f14 xbold">ALAMAT</td>
					<td class="f14 xbold">:</td>
					<td class="f14"><?php 
					if(empty($purchasing_data['supplier_address'])){ $purchasing_data['supplier_address'] = '-';}
					echo $purchasing_data['supplier_address']; 
					?></td>
				</tr>
				<tr>
					<td class="f14 xbold">PAYMENT</td>
					<td class="f14 xbold">:</td>
					<td class="f14"><?php echo strtoupper($purchasing_data['purchasing_payment']);?></td>
					<td class="f14 xbold">&nbsp;</td>
					<td class="f14 xbold">NO.NOTA</td>
					<td class="f14 xbold">:</td>
					<td class="f14"><?php echo $purchasing_data['supplier_invoice']; ?></td>
				</tr>
				<tr>
					
				</tr>
			</table>
		</div>
		<div class="fclear"></div>
		
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<tr class="tbl-header">
				<td class="first xleft" width="30">NO</td>
				<td class="xleft" width="100">KODE</td>
				<td class="xleft">NAMA BARANG</td>
				<td class="xcenter" width="60">QTY</td>
				<td class="xcenter" width="60">UNIT</td>
				<td class="xright" width="80">HARGA</td>
				<td class="xright" width="80">TOTAL</td>
				<td class="xright" width="80">POTONGAN</td>
			</tr>
			
			<?php
			$total_qty = 0;
			$sub_total = 0;
			$sub_total_diskon = 0;
			if(!empty($purchasing_detail)){
			
				$no = 1;
				foreach($purchasing_detail as $det){

					$detail_text = strtoupper($det['item_name']);
					if(!empty($printdetail) AND !empty($data_kodeunik_varian[$det['id']])){
						$detail_text .= '<i>';
						foreach($data_kodeunik_varian[$det['id']] as $varian => $dtKodeUnik){
							$detail_text .= '<br/>'.$varian.': '.implode(", ", $dtKodeUnik);
						}
						$detail_text .= '</i>';
					}
					?>
					<tr class="tbl-data">
						<td class="first xleft"><?php echo $no; ?></td>
						<td class="xleft"><?php echo strtoupper($det['item_code']); ?></td>
						<td class="xleft"><?php echo $detail_text; ?></td>
						<td class="xcenter"><?php echo $det['purchasing_detail_qty']; ?></td>
						<td class="xcenter"><?php echo strtoupper($det['unit_code']); ?></td>
						<td class="xright"><?php echo priceFormat($det['purchasing_detail_purchase']); ?></td>
						<td class="xright"><?php echo priceFormat($det['purchasing_detail_total']); ?></td>
						<td class="xright"><?php echo priceFormat($det['purchasing_detail_potongan']); ?></td>
					</tr>
					<?php	
					$total_qty += $det['purchasing_detail_qty'];
					$sub_total += $det['purchasing_detail_total'];
					$sub_total_diskon += $det['purchasing_detail_potongan'];
					$no++;
				}
			
			}
			?>
			<tr class="tbl-header">
				<td class="first xright" colspan="3">&nbsp;</td>
				<td class="first xcenter"><?php echo $total_qty; ?> </td>
				<td class="first xright" colspan="2">SUB TOTAL </td>
				<td class="xright" width="80"><?php echo priceFormat($sub_total); ?></td>
				<td class="xright" width="80"><?php echo priceFormat($sub_total_diskon); ?></td>
			</tr>
					
		</table>
		
		<div class="fleft" style="width:230px;">
			
			<?php
			if(!empty($purchasing_data['purchasing_memo'])){
				?>
				<div class="fleft xbox-total" style="width:230px; padding:5px; border:1px solid #d8d8d8;">
					<b>Keterangan:</b><br/>
					<?php echo $purchasing_data['purchasing_memo']; ?>
				</div>
				<?php
			}
			?>
			
		</div>
		
		<div class="fleft" style="width:180px;">
			<table align="center">
				<tr class="tbl-footer">
					<td class="xcenter">Prepared by:<br/><br/><br/><br/></td>
				</tr>
				<tr class="tbl-footer">
					<td class="xcenter">----------------------------</td>
				</tr>
			</table>
		</div>
		<div class="fleft" style="width:180px;">
			<table align="center">
				<tr class="tbl-footer">
					<td class="xcenter">Approved by:<br/><br/><br/><br/></td>
				</tr>
				<tr class="tbl-footer">
					<td class="xcenter">----------------------------</td>
				</tr>
			</table>
		</div>
		
		<div class="fright" style="width:230px;">
			<div class="xbox-total">
				<table>
					<tr>
						<td width="120">SUB TOTAL</td>
						<td width="20">Rp.</td>
						<td class="xright" width="110"><?php echo priceFormat($sub_total);?></td>
					</tr>
					<tr>
						<td width="120">POTONGAN</td>
						<td width="20">Rp.</td>
						<td class="xright" width="110"><?php $sub_total -= $sub_total_diskon; echo priceFormat($sub_total_diskon);?></td>
					</tr>
					<tr>
						<td>PAJAK</td>
						<td>Rp.</td>
						<td class="xright"><?php $sub_total += $purchasing_data['purchasing_tax']; echo priceFormat($purchasing_data['purchasing_tax']);?></td>
					</tr>
					<tr>
						<td>PENGIRIMAN</td>
						<td>Rp.</td>
						<td class="xright"><?php $sub_total += $purchasing_data['purchasing_shipping']; echo priceFormat($purchasing_data['purchasing_shipping']);?></td>
					</tr>
					<tr>
						<td><b>GRAND TOTAL</b></td>
						<td><b>Rp.</b></td>
						<td class="xright"><b><?php echo priceFormat($sub_total);?></b></td>
					</tr>
				</table>
			</div>
		</div>
		<div class="fclear"></div>
		
		
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