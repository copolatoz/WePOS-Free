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
				<td class="xcenter" width="250">Supplier Name</td>
				<td class="xcenter" width="100">Contact</td>
				<td class="xcenter" width="250">Address</td>
				<td class="xcenter" width="200">Phone</td>
				<td class="xcenter" width="150">Email</td>
			</tr>
			
			<?php
			$no = 0;
			if(!empty($data_supplier)){
				foreach($data_supplier as $dt){
					$no++;
					
					if(empty($dt->supplier_name)){
						$dt->supplier_name = '-';
					}
					if(empty($dt->supplier_contact_person)){
						$dt->supplier_contact_person = '-';
					}
					if(empty($dt->supplier_address)){
						$dt->supplier_address = '-';
					}
					if(empty($dt->supplier_phone)){
						$dt->supplier_phone = '-';
					}
					if(empty($dt->supplier_email)){
						$dt->supplier_email = '-';
					}
					?>
					<tr class="tbl-data">
						<td class="first xcenter"><?php echo $no; ?></td>
						<td class="xleft"><?php echo $dt->supplier_name; ?></td>
						<td class="xleft"><?php echo $dt->supplier_contact_person; ?></td>
						<td class="xleft"><?php echo $dt->supplier_address; ?></td>
						<td class="xleft"><?php echo $dt->supplier_phone; ?></td>
						<td class="xleft"><?php echo $dt->supplier_email; ?></td>
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