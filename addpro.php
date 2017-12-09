
	<div id="content">
		<div id="inside-content">
           <!-- <div id="top-content"> -->
           	<div id="account">
					<div id="top">
						<h2>Create New Account</h2>
					</div>
					<div id="middle">
					<?php
					//echo validation_errors();
					?>
						<form action="" method="post">
							<table cellspacing="10">
								<tr>
									<td>Product Name</td>
									<td><input type="text" class="text" placeholder="Product Name" name="pro_name" value="<?php echo set_value('pro_name'); ?>"></td>
								</tr>
								<tr>
									<td></td>
									<td class="error "><?php echo form_error("pro_name"); ?></td>
								</tr>
								
								<tr>
									<td>Price</td>
									<td><input type="text" class="text" placeholder="Price" name="price" value="<?php echo set_value('price'); ?>"></td>
								</tr>
								<tr>
									<td></td>
									<td class="error"><?php echo form_error("price"); ?></td>
								</tr>
								
								<tr>
									<td>Category</td>
									<td><select name="category">
										<option value="">Select</option>
										<?php
										foreach($obj->result_array() as $data)
										{?>
<option <?php echo set_select('category', $data['id']); ?> value="<?php echo $data['id']; ?>"><?php echo $data['category_name']; ?></option>
										<?php }
										?>
									    </select>
									</td>
								</tr>
								<tr>
									<td></td>
									<td class="error"><?php echo form_error("category"); ?></td>
								</tr>
								
								
								
								<tr>
									<td>Description</td>
									<td><textarea placeholder="Description" name="desc"><?php echo set_value('desc'); ?></textarea></td>
								</tr>
								<tr>
									<td></td>
									<td class="error"><?php echo form_error("desc"); ?></td>
								</tr>
								
								
								<tr>
									<td></td>
									<td colspan="2"><input type="submit" value="Signup" id="signup"></td>
								</tr>
								
							</table>
						</form>
					</div>
					<?php
					echo $this->session->flashdata("msg");
					?>
				</div>
           </div>
            
	</div>
	