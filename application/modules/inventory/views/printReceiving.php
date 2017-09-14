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
		$set_width = 960;
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<div class="fleft" style="width:260px; margin-right:10px;">
			<br>
			<img height="100" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>">
			
			<div class="headoffice xbold">
				<?php
				if(!empty($client['client_address'])){
					echo $client['client_address'];
				}else{
					echo '&nbsp;';
				}
				?>
				<br><br>
			</div>
		</div>
		<div class="fright" style="width:320px;">
			<div class="title_report">
				RECEIVING LIST
			</div>
			
			<table>
				<tr>
					<td colspan="3" height="20px"> &nbsp; </td>
				</tr>
				<tr class="f14 xbold">
					<td class="f14 xbold" width="80">RL.NO</td>
					<td class="f14 xbold" width="5">:</td>
					<td class="f14 xbold"><?php echo $receive_data['receive_number']; ?></td>
				</tr>
				<tr>
					<td class="f14 xbold" >DATE</td>
					<td class="f14 xbold" >:</td>
					<td class="f14 xbold" ><?php echo date("d/m/Y", strtotime($receive_data['receive_date'])); ?></td>
				</tr>
				<tr>
					<td class="f14 xbold" >SURAT JALAN</td>
					<td class="f14 xbold" >:</td>
					<td class="f14 xbold" ><?php echo $receive_data['no_surat_jalan']; ?></td>
				</tr>
				<tr>
					<td class="f14 xbold" >SUPPLIER</td>
					<td class="f14 xbold" >:</td>
					<td class="f14 xbold" ><?php echo $receive_data['supplier_name']; ?></td>
				</tr>
			</table>
		</div>
		<div class="fclear"></div>
		
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<tr class="tbl-header">
				<td class="first xleft" rowspan="2" width="30">NO</td>
				<td class="xleft" rowspan="2" width="100">KODE</td>
				<td class="xleft" rowspan="2" >NAMA BARANG</td>
				<td class="xcenter" rowspan="2" width="100">UNIT</td>
				<td class="xcenter" rowspan="2" width="80">QTY<br/>PESANAN</td>
				<td class="xcenter" rowspan="2" width="80">TOTAL<br/>DITERIMA</td>
				<td class="xcenter" colspan="2" width="140">PENERIMAAN<br/>SAAT INI</td>
				<td class="xcenter" rowspan="2" width="80">SISA BARANG</td>
			</tr>
			<tr class="tbl-header">		
				<td class="xcenter" width="120">QTY</td>
				<td class="xcenter" width="120">TANGGAL</td>
			</tr>
			
			<?php
			if(!empty($receive_detail)){
			
				$no = 1;
				foreach($receive_detail as $det){

					$total_received = 0;
					if(!empty($all_receive_po_det_qty[$det['po_detail_id']])){
						$total_received = $all_receive_po_det_qty[$det['po_detail_id']] - $det['receive_det_qty'];
					}
					
					$rest_qty = $det['po_detail_qty'] - ($total_received + $det['receive_det_qty']);
					
					?>
					<tr class="tbl-data">
						<td class="first xleft"><?php echo $no; ?></td>
						<td class="xleft"><?php echo $det['item_code']; ?></td>
						<td class="xleft"><?php echo $det['item_name']; ?></td>
						<td class="xcenter"><?php echo $det['unit_name']; ?></td>
						<td class="xcenter"><?php echo $det['po_detail_qty']; ?></td>
						<td class="xcenter"><?php echo $total_received; ?></td>
						<td class="xcenter"><?php echo $det['receive_det_qty']; ?></td>
						<td class="xcenter"><?php echo date("d/m/Y", strtotime($det['receive_det_date'])); ?></td>
						<td class="xcenter"><?php echo $rest_qty; ?></td>
					</tr>
					<?php	

					$no++;
				}
			
			}
			?>
						
		</table>
		
		<div class="fleft" style="width:330px; margin-top:10px;">
			
			<?php
			if(!empty($receive_data['receive_memo'])){
				?>
				<div class="fleft" style="width:330px; padding:10px; border:1px solid #d8d8d8;">
					<b>Memo:</b><br/>
					<?php echo $receive_data['receive_memo']; ?>
				</div>
				<?php
			}
			?>
			
		</div>
		
		<div class="fright" style="width:230px; margin-top:10px;">
			<table>
				<tr class="tbl-footer">
					<td class="xcenter">HEAD WAREHOUSE,<br/><br/><br/><br/></td>
				</tr>
				<tr class="tbl-footer">
					<td class="xcenter">----------------------------</td>
				</tr>
			</table>
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