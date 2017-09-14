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
		$set_width = 700;
		$total_cols = 5;
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<div>
			<div class="logo">
				
				<!-- <img height="80" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>"> -->
				
			</div>
						
			<div class="title_report xcenter"><?php echo $report_name; ?></div>		
			<div class="subtitle_report xcenter"><?php echo $supplier_name; ?></div>		
			
		</div>
		<br/>
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<tr class="tbl-header">
				<td class="first xcenter" width="50">NO</td>
				<td class="xcenter" width="250">Item Name</td>
				<td class="xcenter" width="160">Unit</td>
				<td class="xright" width="120">Price</td>
				<td class="xright" width="120">HPP</td>
			</tr>
			
			<?php
			$no = 0;
			if(!empty($data_supplieritem)){
				foreach($data_supplieritem as $dt){
					$no++;
					?>
					<tr class="tbl-data">
						<td class="first xcenter"><?php echo $no; ?></td>
						<td class="xleft"><?php echo $dt->item_name; ?></td>
						<td class="xleft"><?php echo $dt->unit_name; ?></td>
						<td class="xright"><?php echo priceFormat($dt->item_price); ?></td>
						<td class="xright"><?php echo priceFormat($dt->item_hpp); ?></td>
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