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
		$set_width = 770;
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<div class="fleft" style="width:260px; margin-right:10px;">
			<div class="logo">
				
				<img height="100" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>">
				
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
						<td class="f14 xbold" width="40">PO.NO</td>
						<td class="f14 xbold" width="5">:</td>
						<td class="f14 xbold"><?php echo $po_data['po_number']; ?></td>
					</tr>
					<tr>
						<td>Date</td>
						<td>:</td>
						<td><?php echo date("d/m/Y", strtotime($po_data['po_date'])); ?></td>
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
			<div class="title_report xcenter">
				PURCHASE ORDER
			</div>
			<div class="xbox" style="padding:10px;">
				<table>
					<tr>
						<td width="10" class="f14 xbold">TO</td>
						<td width="5" class="f14 xbold">:</td>
						<td colspan="3" class="f14 xbold"><?php echo $po_data['supplier_name']; ?></td>
					</tr>
					<tr>
						<td colspan="2"></td>
						<td colspan="3"><div style="margin-bottom:10px;"><?php echo $po_data['supplier_address']; ?></div></td>
					</tr>
					<tr>
						<td colspan="2"></td>
						<td>Phone</td>
						<td>:</td>
						<td><?php echo $po_data['supplier_phone']; ?></td>
					</tr>
					<tr>
						<td colspan="2"></td>
						<td>Fax</td>
						<td>:</td>
						<td><?php 
						if(empty($po_data['supplier_fax'])){ $po_data['supplier_fax'] = '-';}
						echo $po_data['supplier_fax']; ?></td>
					</tr>
					<tr>
						<td colspan="2"></td>
						<td>Email</td>
						<td>:</td>
						<td><?php
						if(empty($po_data['supplier_email'])){ $po_data['supplier_email'] = '-';}
						echo $po_data['supplier_email']; ?></td>
					</tr>
					<tr>
						<td colspan="2"></td>
						<td>Attn.</td>
						<td>:</td>
						<td><?php echo $po_data['supplier_contact_person']; ?></td>
					</tr>
				</table>
			</div>
		</div>
		<div class="fclear"></div>
		<br/>
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<tr class="tbl-header">
				<td class="first xleft" width="30">NO</td>
				<?php
				if($qty_print == 0){
				?>
					<td class="xleft" width="80">KODE</td>
				<?php
				}
				?>
				<td class="xleft">NAMA BARANG</td>
				<td class="xcenter" width="60">QTY</td>
				<td class="xcenter" width="100">UNIT</td>
				<td class="xcenter" width="100">HRG.BELI</td>
				<td class="xcenter" width="100">TOTAL</td>
				<td class="xcenter" width="100">DISCOUNT</td>
			</tr>
			
			<?php
			if(!empty($po_detail)){
			
				$no = 1;
				foreach($po_detail as $det){
					?>
					<tr class="tbl-data">
						<td class="first xleft"><?php echo $no; ?></td>
						<?php
						if($qty_print == 1){
							?>
							<td class="xleft"><?php echo $det['item_name']; ?></td>
							<td class="xcenter"><?php echo $det['po_detail_qty']; ?></td>
							<td class="xcenter"><?php echo $det['unit_name']; ?></td>
							<td class="xright">&nbsp;</td>
							<td class="xright">&nbsp;</td>
							<td class="xright">&nbsp;</td>
							<?php
						}else
						{
							?>
							<td class="xleft"><?php echo $det['item_code']; ?></td>
							<td class="xleft"><?php echo $det['item_name']; ?></td>
							<td class="xcenter"><?php echo $det['po_detail_qty']; ?></td>
							<td class="xcenter"><?php echo $det['unit_name']; ?></td>
							<td class="xright"><?php echo priceFormat($det['po_detail_purchase']); ?></td>
							<td class="xright"><?php echo priceFormat($det['po_detail_total']); ?></td>
							<td class="xright"><?php echo priceFormat($det['po_detail_potongan']); ?></td>
							<?php
						}
						?>
					</tr>
					<?php	

					$no++;
				}
			
			}
			?>
						
		</table>
		
		<div class="fleft" style="width:330px; margin-top:5px;">
			
			<table>
					<tr>
						<td width="100">Term of Payment</td>
						<td width="10">:</td>
						<td width="110"><?php echo ucwords(strtolower($po_data['po_payment']));?></td>
					</tr>
					<tr>
						<td>Delivery Date</td>
						<td>:</td>
						<td>&nbsp;</td>
					</tr>			
			</table>
			<?php
			if(!empty($po_data['po_memo'])){
			?>
				<div class="fleft" style="width:330px; padding:10px; border:1px solid #d8d8d8;">
					<b>Memo:</b><br/>
					<?php echo $po_data['po_memo']; ?>
				</div>
			<?php
				} 
			?>
			
		</div>
		<div class="fright" style="width:230px;">
			<div class="xbox-total">
				<?php
				if($qty_print == 1){
					?>
					<table>
						<tr>
							<td width="120">Sub Total</td>
							<td width="20">Rp.</td>
							<td class="xright" width="110">&nbsp;</td>
						</tr>
						<tr>
							<td>Discount</td>
							<td>Rp.</td>
							<td class="xright">&nbsp;</td>
						</tr>
						<tr>
							<td>TAX Sales</td>
							<td>Rp.</td>
							<td class="xright">&nbsp;</td>
						</tr>
						<tr>
							<td>Shipping</td>
							<td>Rp.</td>
							<td class="xright">&nbsp;</td>
						</tr>
						<tr>
							<td><b>GRAND TOTAL</b></td>
							<td><b>Rp.</b></td>
							<td class="xright">&nbsp;</td>
						</tr>
					</table>
					<?php
				}else
				{
					?>
					<table>
						<tr>
							<td width="120">Sub Total</td>
							<td width="20">Rp.</td>
							<td class="xright" width="110"><?php echo priceFormat($po_data['po_sub_total']);?></td>
						</tr>
						<tr>
							<td>Discount</td>
							<td>Rp.</td>
							<td class="xright"><?php echo priceFormat($po_data['po_discount']);?></td>
						</tr>
						<tr>
							<td>TAX Sales</td>
							<td>Rp.</td>
							<td class="xright"><?php echo priceFormat($po_data['po_tax']);?></td>
						</tr>
						<tr>
							<td>Shipping</td>
							<td>Rp.</td>
							<td class="xright"><?php echo priceFormat($po_data['po_shipping']);?></td>
						</tr>
						<tr>
							<td><b>GRAND TOTAL</b></td>
							<td><b>Rp.</b></td>
							<td class="xright"><b><?php echo priceFormat($po_data['po_total_price']);?></b></td>
						</tr>
					</table>
					<?php
				}
				?>
				
			</div>
		</div>
		<div class="fclear"></div>
		<br/>

		<div class="fleft" style="width:200px;">
			<table align="center">
				<tr class="tbl-footer">
					<td class="xcenter">Prepared by:<br/><br/><br/><br/></td>
				</tr>
				<tr class="tbl-footer">
					<td class="xcenter">----------------------------</td>
				</tr>
			</table>
		</div>
		<div class="fleft" style="width:250px;">
			<table align="center">
				<tr class="tbl-footer">
					<td class="xcenter">Approved by:<br/><br/><br/><br/></td>
				</tr>
				<tr class="tbl-footer">
					<td class="xcenter">----------------------------</td>
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