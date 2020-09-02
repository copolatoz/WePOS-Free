<?php

header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=export_purchasing_".$purchasing_data['purchasing_number'].".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);


$set_width = 830;
$total_cols = 9;
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
				<td width="100">&nbsp;</td>
				<td width="150">&nbsp;</td>
				<td width="100">&nbsp;</td>
				<td width="80">&nbsp;</td>
				<td width="80">&nbsp;</td>
				<td width="80">&nbsp;</td>
				<td width="80">&nbsp;</td>
				<td width="100">&nbsp;</td>
			</tr>
			<tr>
				<td class="subtitle_report_xcenter" rowspan="5" colspan="3" style="text-align:left;">
					<img height="40" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>">
					<br/>
					<?php
					if(!empty($client['client_name'])){
						echo $client['client_name'];
					}
					if(!empty($client['client_address'])){
						echo '<br/>'.$client['client_address'];
					}
					if(!empty($client['client_phone'])){
						echo '<br/>'.$client['client_phone'];
					}
					?>
				</td>
				<td class="title_report_xcenter" colspan="5"><b><?php echo $report_name;?></b></td>
			</tr>
			<tr>
				<td colspan="6">&nbsp;</td>
			</tr>
			<tr>
				<td class="xright"><b>NO FAKTUR:</b></td>
				<td colspan="2"><?php echo $purchasing_data['purchasing_number']; ?>
					<?php
					if($purchasing_data['purchasing_status'] != 'done'){
						echo ' ('.strtoupper($purchasing_data['purchasing_status']).')';
					}
					?>
				</td>
				<td class="xright"><b>SUPPLIER:</b></td>
				<td colspan="2"><?php echo $purchasing_data['supplier_name']; ?></td>
			</tr>
			<tr>
				<td class="xright"><b>TANGGAL:</b></td>
				<td colspan="2"><?php echo date("d/m/Y", strtotime($purchasing_data['purchasing_date'])); ?></td>
				<td class="xright"><b>ALAMAT:</b></td>
				<td class="xleft" colspan="2">
					<?php 
					if(empty($purchasing_data['supplier_address'])){ $purchasing_data['supplier_address'] = '-';}
					echo $purchasing_data['supplier_address']; 
					?>
				</td>
			</tr>
			<tr>
				<td class="xright"><b>PAYMENT:</b></td>
				<td colspan="2"><?php echo strtoupper($purchasing_data['purchasing_payment']);?></td>
				<td class="xright"><b>NO.NOTA:</b></td>
				<td class="xleft" colspan="2"><?php echo $purchasing_data['supplier_invoice']; ?></td>
			</tr>
			<tr>
				<td colspan="9">&nbsp;</td>
			</tr>
			
		</thead>
		<tbody>
		
		
			<!-- HEADER -->
			<tr>
				<td class="tbl_head_td_first_xcenter" width="50">NO</td>
				<td class="tbl_head_td" width="80">KODE</td>
				<td class="tbl_head_td" width="200" colspan="2">NAMA BARANG</td>
				<td class="tbl_head_td_xcenter" width="60">QTY</td>
				<td class="tbl_head_td_xcenter" width="60">UNIT</td>
				<td class="tbl_head_td_xcenter" width="100">HARGA</td>
				<td class="tbl_head_td_xcenter" width="100">TOTAL</td>
				<td class="tbl_head_td_xcenter" width="100">POTONGAN</td>
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
					<tr>
						<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
						<td class="tbl_data_td"><?php echo strtoupper($det['item_code']); ?></td>
						<td class="tbl_data_td" colspan="2"><?php echo $detail_text; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo $det['purchasing_detail_qty']; ?></td>
						<td class="tbl_data_td_xcenter"><?php echo strtoupper($det['unit_code']); ?></td>
						<td class="tbl_data_td_xright"><?php echo priceFormat($det['purchasing_detail_purchase']); ?></td>
						<td class="tbl_data_td_xright"><?php echo priceFormat($det['purchasing_detail_total']); ?></td>
						<td class="tbl_data_td_xright"><?php echo priceFormat($det['purchasing_detail_potongan']); ?></td>
					</tr>
					<?php	
					$total_qty += $det['purchasing_detail_qty'];
					$sub_total += $det['purchasing_detail_total'];
					$sub_total_diskon += $det['purchasing_detail_potongan'];
					$no++;
				}
			
			}
			?>
			
			<tr>
				<td class="tbl_head_td_first_xcenter" colspan="3">
					<?php
					if(!empty($purchasing_data['purchasing_memo'])){
						?>
						<b>Memo:</b><br/>
						<?php
						} 
					?>
				</td>
				<td class="tbl_head_td_xcenter">&nbsp;</td>
				<td class="tbl_head_td_xcenter"><?php echo $total_qty; ?></td>
				<td class="tbl_head_td_xright" colspan="2">SUB TOTAL</td>
				<td class="tbl_head_td_xright">Rp. <?php echo priceFormat($sub_total);?></td>
				<td class="tbl_head_td_xright">Rp. <?php echo priceFormat($sub_total_diskon);?></td>
			</tr>
			<tr>
				<td colspan="4">
					<?php
					if(!empty($purchasing_data['purchasing_memo'])){
						echo $purchasing_data['purchasing_memo']; 
					} 
					?>
				</td>
				<td class="xright" colspan="3">POTONGAN</td>
				<td class="xright">Rp. <?php $sub_total -= $sub_total_diskon; echo priceFormat($sub_total_diskon);?></td>
				<td class="xright">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="4">&nbsp;</td>
				<td class="xright" colspan="3">PAJAK</td>
				<td class="xright">Rp. <?php $sub_total -= $purchasing_data['purchasing_tax']; echo priceFormat($purchasing_data['purchasing_tax']);?></td>
				<td class="xright">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="4">&nbsp;</td>
				<td class="xright" colspan="3">PENGIRIMAN</td>
				<td class="xright">Rp. <?php $sub_total -= $purchasing_data['purchasing_shipping']; echo priceFormat($purchasing_data['purchasing_shipping']);?></td>
				<td class="xright">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="4">&nbsp;</td>
				<td class="xright" colspan="3">GRAND TOTAL</td>
				<td class="xright">Rp. <?php echo priceFormat($sub_total);?></td>
				<td class="xright">&nbsp;</td>
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