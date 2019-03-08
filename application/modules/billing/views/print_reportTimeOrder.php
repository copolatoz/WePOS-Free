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
		$set_width = 1000;
		$total_cols = 9;
		
		$payment_data_content = '';
		if(!empty($payment_data)){
			foreach($payment_data as $key_id => $dtPay){
				$payment_data_content .= '<td class="xcenter" width="100">'.$dtPay.'</td>';
				$set_width += 100;
				$total_cols++;
			}
		}
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<div>
			<div class="logo">
				
				<!-- <img height="80" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>"> -->
				
			</div>
						
			<div class="title_report xcenter"><?php echo $report_name;?></div>
			<div class="subtitle_report xcenter"><?php echo 'Period : '.$date_from.' TO '.$date_till;?></div>			
			
		</div>
		<br/>
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<tr class="tbl-header">
				<td class="first xcenter" width="40" rowspan="2">NO</td>
				<td class="xcenter" width="100" rowspan="2">TANGGAL</td>
				<td class="xcenter" width="100" rowspan="2">NO BILLING</td>
				<td class="xcenter" width="260" rowspan="2">PRODUCT</td>
				<td class="xcenter" width="60" rowspan="2">TOTAL QTY</td>
				<td class="xcenter" width="300" colspan="2">WAKTU ORDER</td>
				<td class="xcenter" width="110" rowspan="2">LAMA ORDER (MENIT)</td>				
				<td class="xcenter" width="120" rowspan="2">PENGANTAR</td>	
			</tr>
			<tr class="tbl-header">
				<td class="xcenter" width="110">MULAI ORDER</td>
				<td class="xcenter" width="110">ORDER SELESAI</td>
			</tr>
			<?php
			if(!empty($report_data)){
			
				$no = 1;
				$total_qty = 0;
				foreach($report_data as $det){
					
					if(empty($det['product_name'])){
						$det['product_name'] = '#'.$det['product_id'].' deleted';
					}
						
					if(empty($det['item_code'])){
						$det['item_code'] = 'N/A';
					}
					
					$date_order = date("d-m-Y",strtotime($det['payment_date']));
					
					if(!empty($det['order_start'])){
						$order_start = date("H:i:s",strtotime($det['order_start']));
					}else{
						$order_start = '-';
					}
					
					if(!empty($det['order_done'])){
						
						if($det['order_done'] == '0000-00-00 00:00:00'){
							$order_done = '-';
						}else{
							$order_done = date("H:i:s",strtotime($det['order_done']));
						}
						
					}else{
						$order_done = '-';
					}
					
					$order_time = '-';
					$order_time_detik = '-';
					if($order_start == '-' OR $order_done == '-'){
						$order_time = '-';
					}else{
						
						if(empty($det['order_time'])){
							/*$mktime_start = strtotime($det['order_start']);
							$mktime_end = strtotime($det['order_done']);
							$waktu_order = $mktime_end - $mktime_start;
							$order_time_calc = priceFormat($waktu_order/60);
							$order_time_exp = explode(",",$order_time_calc);
							$order_time = $order_time_exp[0].' mnt';
							$order_time_detik = $waktu_order%60;*/
							
						}else{
							$order_time_exp = explode(".",$det['order_time']);
							$order_time = $order_time_exp[0].' mnt';
							if(!empty($order_time_exp[1])){
								$order_time_detik = priceFormat(ceil(($order_time_exp[1]/100) * 60));
								$order_time_detik_exp = explode(",",$order_time_detik);
								$order_time_detik = $order_time_detik_exp[0];
							}
							
						}
						
					}
					
					if(!empty($order_time_detik)){
						if($order_time_detik != '-'){
							$order_time .= ', '.$order_time_detik.' dtk';
						}
					}
					
					?>
					<tr class="tbl-data">
						<td class="first xcenter"><?php echo $no; ?></td>
						<td class="xcenter"><?php echo $date_order; ?></td>
						<td class="xcenter"><?php echo $det['billing_no']; ?></td>
						<td class="xleft"><?php echo $det['product_name']; ?></td>
						<td class="xcenter"><?php echo $det['total_qty']; ?></td>
						<td class="xcenter"><?php echo $order_start; ?></td>
						<td class="xcenter"><?php echo $order_done; ?></td>
						<td class="xcenter"><?php echo $order_time; ?></td>
						<td class="xcenter"><?php echo $det['done_by']; ?></td>
					</tr>
					<?php	
					
					$total_qty +=  $det['total_qty'];
					$no++;
				}
				
				
			}else{
			?>
				<tr class="tbl-data">
					<td colspan="<?php echo $total_cols; ?>" class="first xleft">Data Not Found</td>
				</tr>
			<?php
			}
			?>
			
			<tr class="tbl-sign">
				<td colspan="<?php echo $total_cols; ?>" class="first xleft">
					<br/>
					<br/>
					<div class="fleft" style="width:200px;">
						<br/><br/><br/><br/>
						Printed: <?php echo date("d-m-Y H:i:s");?>
					</div>
					<div class="fright" style="width:200px;">
						Prepared by:<br/><br/><br/><br/>
						----------------------------
					</div>
					<div class="fright" style="width:200px;">
						Approved by:<br/><br/><br/><br/>
						----------------------------
					</div>
					
					<div class="fclear"></div>
					<br/>
				</td>
			</tr>			
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