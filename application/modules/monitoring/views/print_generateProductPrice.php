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
		$set_width = 1200;
		$total_cols = 11;
	?>
	<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
		<div>
			<div class="logo">
				
				<!-- <img height="80" src="<?php echo base_url(); ?>assets/resources/client_logo/<?php echo $this->session->userdata('client_logo'); ?>"> -->
				
			</div>
						
			<div class="title_report xcenter"><?php echo $report_name;?></div>	
			<div class="title_report xcenter">Date: <?php echo date("d-m-Y");?></div>		
			
		</div>
		<br/>
		<table width="<?php echo $set_width; ?>">
			<!-- HEADER -->
			<tr class="tbl-header">
				<td class="first xcenter" width="50" rowspan="2">NO</td>
				<td class="xcenter" width="250" rowspan="2">Product Name</td>
				<td class="xcenter" width="80" rowspan="2">Product Group</td>
				<td class="xcenter" width="60" rowspan="2">#Cat.ID</td>
				<td class="xcenter" width="160" rowspan="2">Cat.Name</td>
				<td class="xcenter" colspan="3">Normal Price</td>
				<td class="xcenter" colspan="3">Current Price</td>
			</tr>
			<tr class="tbl-header">
				<td class="xcenter" width="100">HPP</td>
				<td class="xcenter" width="100">Price</td>
				<td class="xcenter" width="100">Profit</td>
				<td class="xcenter" width="100">HPP</td>
				<td class="xcenter" width="100">Price</td>
				<td class="xcenter" width="100">Profit</td>
			</tr>
			
			<?php
			$no = 0;
			if(!empty($data_product)){
				foreach($data_product as $dt){
					
					$dt = (object) $dt;
					
					if(!empty($data_product_varian[$dt->id])){
						foreach($data_product_varian[$dt->id] as $dtV){
							$no++;
							
							$dtV = (object) $dtV;
							?>
							<tr class="tbl-data">
								<td class="first xcenter"><?php echo $no; ?></td>
								<td class="xleft"><?php echo $dtV->product_name; ?></td>
								<td class="xleft"><?php echo $dtV->product_group; ?></td>
								<td class="xleft"><?php echo $dtV->product_category_id; ?></td>
								<td class="xleft"><?php echo $dtV->product_category_name; ?></td>
								<td class="xright"><?php echo $dtV->normal_hpp; ?></td>
								<td class="xright"><?php echo $dtV->normal_price; ?></td>
								<td class="xright"><?php echo $dtV->normal_profit; ?></td>
								<td class="xright"><?php echo $dtV->current_hpp; ?></td>
								<td class="xright"><?php echo $dtV->current_price; ?></td>
								<td class="xright"><?php echo $dtV->current_profit; ?></td>
							</tr>
							<?php
						}
					}else{
						$no++;
						?>
						<tr class="tbl-data">
							<td class="first xcenter"><?php echo $no; ?></td>
							<td class="xleft"><?php echo $dt->product_name; ?></td>
							<td class="xleft"><?php echo $dt->product_group; ?></td>
							<td class="xcenter"><?php echo $dt->product_category_id; ?></td>
							<td class="xleft"><?php echo $dt->product_category_name; ?></td>
							<td class="xright"><?php echo $dt->normal_hpp; ?></td>
							<td class="xright"><?php echo $dt->normal_price; ?></td>
							<td class="xright"><?php echo $dt->normal_profit; ?></td>
							<td class="xright"><?php echo $dt->current_hpp; ?></td>
							<td class="xright"><?php echo $dt->current_price; ?></td>
							<td class="xright"><?php echo $dt->current_profit; ?></td>
						</tr>
						<?php
					}
					
				}
				
			}else{
			?>
				<tr class="tbl-data">
					<td colspan="<?php echo $total_cols; ?>" class="first xcenter">Data Not Found</td>
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