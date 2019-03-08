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
		$set_width = 1680;
		$total_cols = 11;
		
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<thead>
				<tr class="tbl-title">
					<td colspan="<?php echo $total_cols ?>">
						<div>
							<div class="logo">
								
								<!-- <img height="80" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>"> -->
								
							</div>
										
							<div class="title_report xcenter"><?php echo $report_name;?></div>
							<div class="subtitle_report xcenter"><?php echo 'Period : '.$date_from.' TO '.$date_till;?></div>			
							
						</div>
					</td>
				</tr>
				<tr class="tbl-header">
					<td class="first xcenter" width="50">NO</td>
					<td class="xcenter" width="80">DATE</td>
					<td class="xcenter" width="80">BILLING NO.</td>
					<td class="xleft" width="500">KETERANGAN</td>
					<td class="xcenter" width="100">VOID BILL</td>
					<td class="xcenter" width="100">VOID ORDER</td>
					<td class="xcenter" width="130">CURRENT STATUS</td>
					<td class="xright" width="120">CANCEL TOTAL</td>
					<td class="xright" width="120">CURRENT TOTAL</td>
					<td class="xleft" width="100">APPROVED</td>
					<td class="xleft" width="300">HISTORY</td>
				</tr>
			</thead>
			<tbody>
				<?php
				if(!empty($dt_cancel_billing)){
				
					$no = 1;
								
					foreach($dt_cancel_billing as $billing_no){
						
						$current_status = '-';
						if(in_array($billing_no, $all_bill_paid)){
							$current_status = '<font color="red"><b>Paid</b></font>';
						}
						if(in_array($billing_no, $all_bill_cancel)){
							$current_status = '<font color="green"><b>Cancel</b></font>';
						}
						if(in_array($billing_no, $all_bill_hold)){
							$current_status = '<font color="green"><b>Hold/Active</b></font>';
						}
						
						$void_billing = '<font color="red"><b>Tidak</b></font>';
						$void_order = '<font color="red"><b>Tidak</b></font>';
						$keterangan = '';
						$cancel_total = 0;
						$current_total = 0;
						$do_cancel_billing = 0;
						$do_cancel_order = 0;
						
						
						if(!empty($all_bill_data[$billing_no])){
							
							$billing_data = $all_bill_data[$billing_no];
							$order_date_mk = strtotime($billing_data->created);
							$order_date = date("d-m-Y", $order_date_mk);
							$current_total = $billing_data->grand_total;
							
							if(!empty($billing_data->cancel_notes)){
								if(empty($keterangan)){
									$keterangan = 'NOTES:'.$billing_data->cancel_notes;
								}else{
									$keterangan .= '<br/>NOTES:'.$billing_data->cancel_notes;
								}
							}
							
							
						}
						
						
						$data_cancel = array();
						if(!empty($dt_cancel_billing_data[$billing_no])){
							$data_cancel = $dt_cancel_billing_data[$billing_no];
							$do_cancel_billing = 1;
							$void_billing = '<font color="green"><b>Ya</b></font>';
						}
						
						$data_cancel_order = array();
						//if(empty($do_cancel_billing)){
							if(!empty($dt_cancel_order_data[$billing_no])){
								$data_cancel_order = $dt_cancel_order_data[$billing_no];
								$do_cancel_order = 1;
							}
						//}
						
						$approved = '-';
						if(!empty($data_cancel)){
							$approved = $data_cancel->user_username;
						}
						
						if(!empty($data_cancel_order)){
							$approved = $data_cancel_order[0]->user_username;
							$void_order = '<font color="green"><b>Ya</b></font>';
						}
						
						if(!empty($dt_cancel_order_nama[$billing_no])){
							if(empty($keterangan)){
								$keterangan = 'CANCEL ORDER:';
							}else{
								$keterangan .= '<br/><br/>CANCEL ORDER:';
							}
							foreach($dt_cancel_order_nama[$billing_no] as $nama_order){
								$keterangan .= '<br/>'.$nama_order;
							}
						}
						
						$history = '-';
						if(!empty($log_billing[$billing_no])){
							$history = '';
							$noLog = 1;
							foreach($log_billing[$billing_no] as $dtLog){
								
								$display_jam = '';
								if(!empty($dtLog->created)){
									$getTgl = explode(" ", $dtLog->created);
									$getJam = explode(":", $getTgl[1]);
									$display_jam = " / Jam ".$getJam[0].":".$getJam[1]."";
								}
								
								if($noLog == 1){
									$history .= $noLog.'. '.$dtLog->trx_info.$display_jam;
								}else{
									$history .= '<br/>'.$noLog.'. '.$dtLog->trx_info.$display_jam;
								}
								
								if($dtLog->trx_type == 'Paid' AND $cancel_total == 0){
									$dtBillingLog = json_decode($dtLog->log_data, true);
									$cancel_total = $dtBillingLog['grand_total'];
								}
								
								$noLog++;
							}
						}
						
						if(!empty($dt_spv_log[$billing_no])){
							
							if(empty($keterangan)){
								$keterangan = 'BILL-LOG:';
							}else{
								$keterangan .= '<br/><br/>BILL-LOG:';
							}
							
							$noLog = 1;
							foreach($dt_spv_log[$billing_no] as $dtSpvLog){
								
								$display_jam = '';
								if(!empty($dtSpvLog->created)){
									$getTgl = explode(" ", $dtSpvLog->created);
									$getJam = explode(":", $getTgl[1]);
									$display_jam = " / Jam ".$getJam[0].":".$getJam[1]."";
								}
								
								
								if($noLog == 1){
									$keterangan .= '<br/>'.$noLog.'. '.$dtSpvLog->log_data.$display_jam;
								}else{
									$keterangan .= '<br/>'.$noLog.'. '.$dtSpvLog->log_data.$display_jam;
								}
								
								$noLog++;
							}
						}
						
						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xcenter"><?php echo $order_date; ?></td>
							<td class="xcenter"><?php echo $billing_no; ?></td>
							<td class="xleft"><?php echo $keterangan; ?></td>
							<td class="xcenter"><?php echo $void_billing; ?></td>
							<td class="xcenter"><?php echo $void_order; ?></td>
							<td class="xcenter"><?php echo $current_status; ?></td>
							<td class="xright"><?php echo priceFormat($cancel_total); ?></td>
							<td class="xright"><?php echo priceFormat($current_total); ?></td>
							<td class="xleft"><?php echo $approved; ?></td>
							<td class="xleft"><?php echo $history; ?></td>
						</tr>
						<?php	
						
						$no++;
					}
					
				}else{
				?>
					<tr class="tbl-data">
						<td class="first xcenter" colspan="<?php echo $total_cols; ?>">Data Not Found</td>
					</tr>
				<?php
				}
				?>
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