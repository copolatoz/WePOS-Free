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
		$total_cols = 6;
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<div>
			<div class="logo">
				
				<!-- <img height="80" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>"> -->
				
			</div>
						
			<div class="title_report xcenter"><?php echo $report_name;?></div>		
			
		</div>
		<br/>
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<tr class="tbl-header">
				<td class="first xcenter" width="50">NO</td>
				<td class="xcenter" width="250">Customer Name</td>
				<td class="xcenter" width="100">Contact</td>
				<td class="xcenter" width="250">Address</td>
				<td class="xcenter" width="200">Phone</td>
				<td class="xcenter" width="150">Email</td>
			</tr>
			
			<?php
			$no = 0;
			if(!empty($data_customer)){
				foreach($data_customer as $dt){
					$no++;
					
					if(empty($dt->customer_name)){
						$dt->customer_name = '-';
					}
					if(empty($dt->customer_contact_person)){
						$dt->customer_contact_person = '-';
					}
					if(empty($dt->customer_address)){
						$dt->customer_address = '-';
					}
					if(empty($dt->customer_phone)){
						$dt->customer_phone = '-';
					}
					if(empty($dt->customer_email)){
						$dt->customer_email = '-';
					}
					?>
					<tr class="tbl-data">
						<td class="first xcenter"><?php echo $no; ?></td>
						<td class="xleft"><?php echo $dt->customer_name; ?></td>
						<td class="xleft"><?php echo $dt->customer_contact_person; ?></td>
						<td class="xleft"><?php echo $dt->customer_address; ?></td>
						<td class="xleft"><?php echo $dt->customer_phone; ?></td>
						<td class="xleft"><?php echo $dt->customer_email; ?></td>
					</tr>
					<?php
				}
				
			}else{
			?>
				<tr class="tbl-data">
					<td class="first xcenter" colspan="<?php echo $total_cols; ?>" class="first xcenter">Data Not Found</td>
				</tr>
			<?php
			}
			?>
			
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