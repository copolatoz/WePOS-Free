<?php
header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=export_master_item_".date("dmY_his").".xls"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

$set_width = '800';
?>
<table width="<?php echo $set_width; ?>">
	<!-- HEADER -->
	<tr>
		<td width="60">item_id</td>
		<td width="250">item_name</td>
		<td width="150">item_code</td>
		<td width="100">satuan</td>
		<td width="200">nama_category</td>
		<td width="200">nama_subcategory</td>
		<td width="200">item_desc</td>
		<td width="100">normal_price</td>
		<td width="100">sell_price</td>
		<td width="100">hpp_price</td>
		<td width="100">main/support</td>
		<td width="100">item/package</td>
		<td width="100">use_tax</td>
		<td width="100">use_service</td>
		<td width="100">use_for_sales</td>
		<td width="100">min_stock</td>
	</tr>
<?php
		
	$this->db->select('a.*, b.unit_name, c.item_category_name, d.item_subcategory_name, e.normal_price, e.product_price, e.product_type');
	$this->db->from($this->prefix.'items as a');
	$this->db->join($this->prefix.'unit as b',"b.id = a.unit_id","LEFT");
	$this->db->join($this->prefix.'item_category as c',"c.id = a.category_id","LEFT");
	$this->db->join($this->prefix.'item_subcategory as d',"d.id = a.subcategory_id","LEFT");
	$this->db->join($this->prefix.'product as e',"e.id_ref_item = a.id AND e.from_item = 1","LEFT");
	$this->db->where('a.is_active = 1');
	$get_item = $this->db->get();
	
	if($get_item->num_rows() > 0){
		
		foreach($get_item->result_array() as $det){
			
			if(empty($det['use_tax'])){
				$det['use_tax'] = 0;
			}
			if(empty($det['use_service'])){
				$det['use_service'] = 0;
			}
			if(empty($det['use_for_sales'])){
				$det['use_for_sales'] = 0;
			}
			?>
			<tr>
				<td><?php echo $det['id']; ?></td>
				<td><?php echo $det['item_name']; ?></td>
				<td><?php echo $det['item_code']; ?></td>
				<td><?php echo $det['unit_name']; ?></td>
				<td><?php echo $det['item_category_name']; ?></td>
				<td><?php echo $det['item_subcategory_name']; ?></td>
				<td><?php echo $det['item_desc']; ?></td>
				<td><?php echo $det['normal_price']; ?></td>
				<td><?php echo $det['product_price']; ?></td>
				<td><?php echo $det['item_hpp']; ?></td>
				<td><?php echo $det['item_type']; ?></td>
				<td><?php echo $det['product_type']; ?></td>
				<td><?php echo $det['use_tax']; ?></td>
				<td><?php echo $det['use_service']; ?></td>
				<td><?php echo $det['use_for_sales']; ?></td>
				<td><?php echo $det['min_stock']; ?></td>
			</tr>
			<?php
		}
		
	}
?>		
</table>