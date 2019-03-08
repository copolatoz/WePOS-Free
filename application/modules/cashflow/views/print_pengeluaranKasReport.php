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
		$set_width = 1150;
		$total_cols = 9;
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<thead>
				<tr>
					<div>
						<div class="logo">
							
							<!-- <img height="80" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>"> -->
							
						</div>
									
						<div class="title_report xcenter"><?php echo $report_name;?></div>
						<div class="subtitle_report xcenter">
							<?php echo 'Period : '.$date_from.' TO '.$date_till;?>
							<?php 
								if(!empty($jenis)){ 
									echo '<br/>Jenis: '.ucwords($jenis); 
								}else{
									echo '<br/>Semua Jenis'; 
								}
							?>
						</div>			
						
					</div>
				</tr>	
				<tr class="tbl-header">
					<td class="first xcenter" width="50">NO</td>
					<td class="xcenter" width="90">TANGGAL</td>
					<td class="xcenter" width="100">NO.KAS KELUAR</td>
					<td class="xleft" width="200">TUJUAN</td>
					<td class="xleft" width="150">JENIS</td>
					<td class="xcenter" width="120">NO.REF</td>
					<td class="xcenter" width="100">TOTAL</td>
					<td class="xcenter" width="100">OLEH</td>
					<td class="xcenter" width="230">NOTES</td>
				</tr>
			</thead>
			<tbody>
				<?php
				if(!empty($report_data)){
				
					$no = 1;
					$kk_total = 0;
					foreach($report_data as $det){

						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xcenter"><?php echo $det['kk_date']; ?></td>
							<td class="xcenter"><?php echo $det['kk_no']; ?></td>
							<td class="xleft"><?php echo $det['tujuan_cashflow_name']; ?></td>
							<td class="xleft"><?php echo $det['autoposting_name']; ?></td>
							<td class="xleft"><?php echo $det['no_ref']; ?></td>
							<td class="xright"><?php echo priceFormat($det['kk_total']); ?></td>
							<td class="xcenter"><?php echo $det['kk_name']; ?></td>
							<td class="xleft"><?php echo $det['kk_notes']; ?></td>
							
						</tr>
						<?php	
											
						$kk_total += $det['kk_total'];
						$no++;
					}
					
					?>
					<tr class="tbl-total">
						<td class="first xright xbold" colspan="6">TOTAL KAS KELUAR</td>
						<td class="xright xbold"><?php echo priceFormat($kk_total); ?></td>
						<td class="xleft">&nbsp;</td>
						<td class="xleft">&nbsp;</td>
					</tr>
					<?php
				}else{
				?>
					<tr class="tbl-data">
						<td colspan="<?php echo $total_cols; ?>" class="first xcenter">Data Not Found</td>
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
						<div class="fright" style="width:250px;">
							Prepared by:<br/><br/><br/><br/>
							----------------------------
						</div>
						<div class="fright" style="width:250px;">
							Approved by:<br/><br/><br/><br/>
							----------------------------
						</div>
						
						<div class="fclear"></div>
						<br/>
					</td>
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