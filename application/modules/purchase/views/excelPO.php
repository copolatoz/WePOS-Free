<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=export_po_".$po_data['po_number'].".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 750;
$total_cols = 7;
?>
<html>
<body>
<style>
	<?php include ASSETS_PATH."desktop/css/report.css.php"; ?>
</style>

	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<table width="<?php echo $set_width; ?>">
		<!-- HEADER -->
		<thead>
			<tr style="height:5px;">
				<td width="50">&nbsp;</td>
				<td width="300">&nbsp;</td>
				<td width="80">&nbsp;</td>
				<td width="100">&nbsp;</td>
				<td width="120">&nbsp;</td>
				<td width="120">&nbsp;</td>
			</tr>
			
			<tr>
				<td class="subtitle_report_xcenter" width="350" colspan="3" style="text-align:left;">
					<?php
					if(!empty($client['client_name'])){
						echo $client['client_name'];
					}else{
						echo '&nbsp;';
					}
					?>
				</td>
				<td>&nbsp;</td>
				<td class="title_report_xcenter" style="text-align:left;" colspan="3"><b><?php echo $report_name;?></b></td>
			</tr>
			
			<tr>
				<td colspan="3">
					<?php
					if(!empty($client['client_address'])){
						echo $client['client_address'];
					}else{
						echo '&nbsp;';
					}
					?>
				</td>
				<td>&nbsp;</td>
				<td>TO: </td>
				<td colspan="2"><?php echo $po_data['supplier_name']; ?></td>
			</tr>
			<tr>
				<td colspan="3">PO.NO: <?php echo $po_data['po_number']; ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td colspan="2"><?php echo $po_data['supplier_address']; ?></td>
			</tr>
			<tr>
				<td colspan="3">Date: <?php echo date("d/m/Y", strtotime($po_data['po_date'])); ?></td>
				<td>&nbsp;</td>
				<td>Phone:</td>
				<td colspan="2"><?php echo $po_data['supplier_phone']; ?></td>
			</tr>
			<tr>
				<td colspan="4">&nbsp;</td>
				<td>Fax:</td>
				<td colspan="2">
					<?php 
					if(empty($po_data['supplier_fax'])){ $po_data['supplier_fax'] = '-';}
					echo $po_data['supplier_fax']; 
					?>
				</td>
			</tr>
			<tr>
				<td colspan="4">&nbsp;</td>
				<td>Email:</td>
				<td colspan="2">
					<?php 
					if(empty($po_data['supplier_email'])){ $po_data['supplier_email'] = '-';}
					echo $po_data['supplier_email']; 
					?>
				</td>
			</tr>
			<tr>
				<td colspan="4">&nbsp;</td>
				<td>Attn:</td>
				<td colspan="2">
					<?php echo $po_data['supplier_contact_person']; ?>
				</td>
			</tr>
			
			<tr>
				<td colspan="6">&nbsp;</td>
			</tr>
			
		</thead>
		<tbody>
		
		
			<!-- HEADER -->
			<tr>
				<td class="tbl_head_td_first_xcenter" width="50">NO</td>
				<td class="tbl_head_td"  width="100">KODE</td>
				<td class="tbl_head_td"  width="200">NAMA BARANG</td>
				<td class="tbl_head_td_xcenter" width="80">QTY</td>
				<td class="tbl_head_td_xcenter" width="100">UNIT</td>
				<td class="tbl_head_td_xcenter" width="100">HRG.BELI</td>
				<td class="tbl_head_td_xcenter" width="100">TOTAL</td>
				<td class="tbl_head_td_xcenter" width="100">DISCOUNT</td>
			</tr>
			
			<?php
			$total_qty = 0;
			$total_subtotal = 0;
			$total_potongan = 0;
			if(!empty($po_detail)){
			
				$no = 1;
				foreach($po_detail as $det){
					?>
					<tr>
						<td class="tbl_data_td_first"><?php echo $no; ?></td>
						<td class="tbl_data_td"><?php echo $det['item_code']; ?></td>
						<td class="tbl_data_td"><?php echo $det['item_name']; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $det['po_detail_qty']; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $det['unit_name']; ?></td>
						<td class="tbl_data_td_xright">&nbsp;<?php echo priceFormat($det['po_detail_purchase']); ?></td>
						<td class="tbl_data_td_xright">&nbsp;<?php echo priceFormat($det['po_detail_total']); ?></td>
						<td class="tbl_data_td_xright">&nbsp;<?php echo priceFormat($det['po_detail_potongan']); ?></td>
					</tr>
					<?php	
					$total_qty += $det['po_detail_qty'];
					$total_subtotal += $det['po_detail_total'];
					$total_potongan += $det['po_detail_potongan'];
					$no++;
				}
				?>
				<tr>
					<td class="tbl_head_td_first_xright" colspan="3">TOTAL </td>
					<td class="tbl_head_td_xcenter"><?php echo priceFormat($total_qty); ?></td>
					<td class="tbl_head_td_xcenter" colspan="2">&nbsp;</td>
					<td class="tbl_head_td_xright">&nbsp;<?php echo priceFormat($total_subtotal); ?></td>
					<td class="tbl_head_td_xright">&nbsp;<?php echo priceFormat($total_potongan); ?></td>
				</tr>
				<?php
			}
			?>
			
			<tr>
				<td colspan="<?php echo $total_cols; ?>">
				&nbsp;
				</td>
			</tr>
			<tr>
				<td colspan="3">Term of Payment: <?php echo ucwords(strtolower($po_data['po_payment']));?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>Sub Total</td>
				<td class="xright">Rp. <?php echo priceFormat($po_data['po_sub_total']);?></td>
			</tr>
			<tr>
				<td colspan="3">
					<?php
					if(!empty($po_data['po_memo'])){
						?>
						<b>Memo:</b><br/>
						<?php echo $po_data['po_memo']; ?>
						<?php
						} 
					?>
				</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>Discount</td>
				<td class="xright">Rp. <?php echo priceFormat($po_data['po_discount']);?></td>
			</tr>
			<tr>
				<td colspan="5">&nbsp;</td>
				<td>TAX Sales</td>
				<td class="xright">Rp. <?php echo priceFormat($po_data['po_tax']);?></td>
			</tr>
			<tr>
				<td colspan="5">&nbsp;</td>
				<td>Shipping</td>
				<td class="xright">Rp. <?php echo priceFormat($po_data['po_shipping']);?></td>
			</tr>
			<tr>
				<td colspan="5">&nbsp;</td>
				<td>GRAND TOTAL</td>
				<td class="xright">Rp. <?php echo priceFormat($po_data['po_total_price']);?></td>
			</tr>
			
		</tbody>
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