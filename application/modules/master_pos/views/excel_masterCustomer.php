<?php
header("Content-Type:   application/excel; charset=utf-8");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
//header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=export_master_customer.xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = 1000;
$total_cols = 6;
?>
<html>
<body>
<style>
	<?php include ASSETS_PATH."desktop/css/report.css.php"; ?>
</style>
<div class="report_area" style="width:<?php echo $set_width.'px'; ?>;">
	<div>
					
		<div class="title_report_xcenter"><?php echo $report_name;?></div>			
		
	</div>
		
	<table width="<?php echo $set_width; ?>">
		<!-- HEADER -->
		<thead>
			<tr>
				<td class="tbl_head_td_first_xcenter" width="50">No</td>
				<td class="tbl_head_td" width="250">Customer Name</td>
				<td class="tbl_head_td" width="100">Contact</td>
				<td class="tbl_head_td" width="250">Address</td>
				<td class="tbl_head_td" width="200">Phone</td>
				<td class="tbl_head_td" width="150">Email</td>
			</tr>
		</thead>
		<tbody>
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
					<tr>
						<td class="tbl_data_td_first_xcenter"><?php echo $no; ?></td>
						<td class="tbl_data_td"><?php echo $dt->customer_name; ?></td>
						<td class="tbl_data_td"><?php echo $dt->customer_contact_person; ?></td>
						<td class="tbl_data_td"><?php echo $dt->customer_address; ?></td>
						<td class="tbl_data_td"><?php echo $dt->customer_phone; ?></td>
						<td class="tbl_data_td"><?php echo $dt->customer_email; ?></td>
					</tr>
					<?php
				}
				
			}
		?>	
		</tbody>	
	</table>
</div>
</body>
</html>