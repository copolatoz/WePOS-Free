<?php
header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=export_master_product_".date("dmY_his").".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = '800';
?>
<table width="<?php echo $set_width; ?>">
	<!-- HEADER -->
	<tr>
		<td width="50">id</td>
		<td width="200">product_code</td>
		<td width="200">product_name</td>
		<td width="200">product_desc</td>
		<td width="150">normal_price</td>
		<td width="150">product_price</td>
		<td width="150">product_hpp</td>
		<td width="100">product_type</td>
		<td width="100">product_group</td>
		<td width="100">category_id</td>
		<td width="100">use_tax</td>
		<td width="100">use_service</td>
		<td width="100">is_active</td>
	</tr>
<?php
	$this->db->select('*');
	$this->db->from($table);
	$this->db->where('is_active = 1');
	$get_product = $this->db->get();
	
	if($get_product->num_rows() > 0){
		
		//echo '<pre>';
		//print_r($get_product);
		
		foreach($get_product->result_array() as $det){
			?>
			<tr>
				<td><?php echo $det['id']; ?></td>
				<td><?php echo $det['product_code']; ?></td>
				<td><?php echo $det['product_name']; ?></td>
				<td><?php echo $det['product_desc']; ?></td>
				<td><?php echo $det['normal_price']; ?></td>
				<td><?php echo $det['product_price']; ?></td>
				<td><?php echo $det['product_hpp']; ?></td>
				<td><?php echo $det['product_type']; ?></td>
				<td><?php echo $det['product_group']; ?></td>				
				<td><?php echo $det['category_id']; ?></td>
				<td><?php echo $det['use_tax']; ?></td>
				<td><?php echo $det['use_service']; ?></td>
				<td><?php echo $det['is_active']; ?></td>
			</tr>
			<?php
		}
		
	}
?>		
</table>