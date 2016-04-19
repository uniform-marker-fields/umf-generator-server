<?php
require_once('../inc/defines.php');
require_once('../inc/database.php');
require_once('../inc/common.php');

session_start();
$logged_in = False;
if(!isset($_SESSION['logged_in']))
{
	$_SESSION['logged_in'] = False;
}

$logged_in = $_SESSION['logged_in'];

?>

<?php
if($logged_in)
{
?>

<!-- *************************************NEW********************************* -->
<div id="generate_new">
	
	<div id="image_picker">
		<div>
			<h5>Choose from the database...</h5>
			<div id="image_database">
			</div>
		</div>
		<div>
			<h5 style="cursor: pointer" id="image_upload_wrapper">...or click here to upload an image</h5>
			<div id="image_upload_process" style="display: none">Uploading (please wait)...</div>
						
			<form action="ajax/image_upload.php" method="post" enctype="multipart/form-data" target="upload_target" >
				<!--p id="image_upload_process">Loading...<br/><img src="loader.gif" /><br/></p-->
				<p id="image_upload_form" align="center"><br/>
					<label>
					<input id="image_upload" name="image_upload" type="file" />
					</label>
					<label>
					<input id="image_upload_submit" type="submit" name="submitBtn" class="sbtn" value="Upload" />
					</label>
				</p>
				<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>
			</form>
			
			<!--<form method="post" enctype="multipart/form-data" action="upload.php" id="image_upload_form">
				<input type="file" name="image_upload" id="image_upload" />
				<button type="submit" id="image_upload_btn">Upload Files!</button>
			</form>
			<div id="image_upload_response" style="display: none"></div>-->
			<script type="text/javascript">
				image_upload_init();
			</script>
    	</div>
		<div>
			<div class="note">if you want to close this dialog click <span style="cursor: pointer; text-decoration: underline" onclick="$.modal.close()">here</span></div>
		</div>
	</div>
	
	<h2>Generate new</h2>
	<table class="generate">
	 <tr>
	   <td>Module type:</td>
	   <td>
			<select id="gen_module_type" onChange="generator_module_changed()">
				<option value="<?=MODULE_TYPE_SQUARE?>">Square</option>
				<option value="<?=MODULE_TYPE_HEXA?>">Hexa</option>
			</select>
		</td>
	 </tr>
	 <tr style="display: none">
	   <td>Type:</td>
	   <td>
			<select id="gen_type">
				<option value="0" selected="selected">Normal</option>
				<option value="1">Torus</option>
			</select>
		</td>
	 </tr>
	 <tr>
	   <td>Colorspace:</td>
	   <td>
			<select id="gen_type_color" onChange="generator_colors_changed()">
				<option value="0">Grayscale</option>
				<option value="1">Color</option>
			</select>
		</td>
	 </tr>
	 <tr>
	   <td>Range:</td>
	   <td><input type="text" name="gen_type_range" id="gen_type_range" value="2" onChange="generator_colors_changed()"/></td>
	 </tr>
	 <tr>
	   <td>Colors:</td>
	   <td><input type="text" name="gen_colors" id="gen_colors" value="" onChange="generator_colors_changed()" disabled="true"/><div id="color_pickers"></div></td>
	 </tr>
	 <tr>
	   <td>Width:</td>
	   <td><input type="text" name="gen_width" id="gen_width" value="50"/></td>
	 </tr>
	 <tr>
	   <td>Height:</td>
	   <td><input type="text" name="gen_height" id="gen_height" value="50"/></td>
	 </tr>
	 <tr id="block_kernel">
	   <td>Kernel size:</td>
	   <td><input type="text" name="gen_kernel" id="gen_kernel" value="4" onChange="kernel_type_init()"/></td>
	 </tr>
	 <tr id="block_kernel_type">
	   <td>Kernel type:</td>
	   <td>
		   <input type="hidden" name="gen_kernel_type" id="gen_kernel_type" value="0"/>
			<div id="kernel_type_container" style="width: 60px; height: 60px">
				<script type="text/javascript">
					kernel_type_init();
				</script>
			</div>
			<div class="note">(click the borders to (not) use them for the kernel)</div>
	   </td>
	 </tr>
	 <tr id="block_img">
	   <td>Image:</td>
	   <td>
			<select id="gen_img_use" onChange="generator_img_use_change()">
				<option value="0">Do NOT use image</option>
				<option value="1">Use image</option>
			</select>
	   </td>
	 </tr>
	 <tr id="image_in_use" style="display: none">
		<td></td>
		<td>
			<input type="hidden" id="gen_img_id" value="0"/>
			<div id="image_selected">
				<div class="image" onclick="image_picker_show()" />
				<div class="note" style="padding-top: 5px;"> &lt;- click the image to choose <span id="image_no_selected" style="color: red">!!! no image chosen</span></div>
			</div>
			Algorithm:
			<select id="gen_img_alg">
			<?php
			foreach ($IMAGE_ALGS as $key => $val)
			{
				echo "<option value=\"$key\">$val</option>";
			}
			?>
			</select> <br/>
			<select id="gen_img_rnd">
				<option value="0">Start from image</option>
				<option value="1">Start from random</option>
			</select> <br/>
		</td>
	 </tr>
	 <tr class="show_advanced_settings">
	   <td></td>
	   <td><span onclick="show_advanced_settings()">Click for ADVANCED SETTINGS</span></td>
	 </tr>
	 <tr class="advanced_settings">
	   <td></td>
	   <td>click the input field for detailed information</td>
	 </tr>
	 <tr class="advanced_settings">
	   <td>Threshold for equal:</td>
	   <td>
		   <input type="text" name="gen_t_equal" id="gen_t_equal" value="0" onfocus="show_note('thresh-equal')" onblur="hide_note('thresh-equal')"/>
		   <div class="note" id="note-thresh-equal" style="display: none; padding-top: 5px;">The gradient will treated equal below this value (0..255)</div>
	   </td>
	 </tr>
	 <tr class="advanced_settings">
	   <td>Cost for neighbors:</td>
	   <td>
			<input type="text" name="gen_c_neighbors" id="gen_c_neighbors" value="8.0,8.0:0;10.0,0.0:128" onfocus="show_note('cost-neigh')" onblur="hide_note('cost-neigh')"/>
			<div class="note" id="note-cost-neigh" style="display: none; padding-top: 5px;">
				CL<sup>1</sup>,CR2<sup>1</sup>:T<sup>1</sup>;CL<sup>2</sup>,CR<sup>2</sup>:T<sup>2</sup>;...;CL<sup>max</sup>,CR2<sup>max</sup>:T<sup>max</sup><br/>
				CL<sup>n</sup> is the left cost, CR<sup>n</sup> is the right cost<br/>
				T<sup>n</sup> is the end of the interval, T<sup>1</sup> &ge; 0, T<sup>max</sup> &le; 255,<br/>
				cost for gradient <i>x<i> will be, if T<sup>n-1</sup> &lt; <i>x<i> &le; T<sup>n</sup> then:<br/>
				(CR<sup>n</sup>-CL<sup>n</sup>)(<i>x</i>-T<sup>n-1</sup>)/(T<sup>n</sup>-T<sup>n-1</sup>) + CL<sup>n</sup><br/>
				if <i>x<i> &gt; T<sup>max</sup>, then the cost is 0
			</div>
		</td>
	 </tr>
	 <tr class="advanced_settings">
	   <td>Cost for similarity:</td>
	   <td>
			<input type="text" name="gen_c_similarity" id="gen_c_similarity" value="1.0" onfocus="show_note('cost-sim')" onblur="hide_note('cost-sim')"/>
			<div class="note" id="note-cost-sim" style="display: none; padding-top: 5px;">
				This value is used to multiply the difference in similarity with the image<br/>
				The difference is a value from 0 to 255 for a module
			</div>
		</td>
	 </tr>
	 <tr class="advanced_settings">
	   <td>Image matrix:</td>
	   <td>
			<input type="text" name="gen_img_conv" id="gen_img_conv" value="1" onfocus="show_note('img-conv')" onblur="hide_note('img-conv')"/>
			<div class="note" id="note-img-conv" style="display: none; padding-top: 5px;">
				V<sup>1</sup>;V<sup>2</sup>;V<sup>3</sup>;...;V<sup>(2k+1)^2</sup><br/>
				This convolution matrix is applied on the marker before comparison with the image<br/>
				Always an odd sided matrix is used, missing values are replaced with zeros.
			</div>
		</td>
	 </tr>
	 <tr>
	   <td>Count:</td>
	   <td><input type="text" name="gen_count" id="gen_count" value="1"/></td>
	 </tr>
	 <tr>
	   <td>Name:</td>
	   <td><input type="text" name="gen_name" id="gen_name" value=""/></td>
	 </tr>
	</table>

	<div id="gen_submit"><input type="button" value="Generate" onclick="post_generate_new()"/></div>
	<div id="gen_loading" style="display:none"> <img src="img/loading.gif" alt="loading"/></div>
	<div id="gen_result">
		...	
	</div>
	<br />
	<h2>Expand existing</h2>
	<table>
		<tr>
			<td>
				<select id="expand_type">
					<option value="0">Normal</option>
					<option value="1">Torus</option>
				</select>
			</td>
			<td>
				<select id="expand_type_color">
					<option value="0">Grayscale</option>
					<option value="1">Color</option>
				</select>
			</td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="text" name="expand_top" id="expand_top" value="1" onchange="calculate_new_size()"/></td>
			<td></td>
		</tr>
		
		<tr>
		
			<td><input type="text" name="expand_right" id="expand_left" value="1" onchange="calculate_new_size()"/></td>
			<td>
				<select id="expand_select" onchange="calculate_new_size()">
<?php
$link = db_connect();

$types = array( 0 => "N", 1 => "T");
$types_color = array( 0 => "G", 1 => "C");

$ready_list = db_get_ready($link, 0, 500);
foreach($ready_list as $row)
{
	echo "				<option value=\"{$row['id']}_{$row['width']}_{$row['height']}\">{$row['short_name']}({$row['gid']}) {$row['width']}x{$row['height']},{$row['kernel']}," . $types[(int)isTypeTorus($row['type'])] . "," . $types_color[(int)isTypeColor($row['type'])] . ",((" . getTypeRange($row['type']) . "))</option>\n";
}

db_close($link);
?>
				</select>
			</td>
			
			<td><input type="text" name="expand_right" id="expand_right" value="1" onchange="calculate_new_size()"/></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="text" name="expand_bottom" id="expand_bottom" value="1" onchange="calculate_new_size()"/></td>
			<td><div id="expanded_size"></div></td>
		</tr>
		<tr>
			<td>Colors:</td>
			<td><input type="text" name="expand_colors" id="expand_colors"/></td>
			<td></td>
		</tr>
	</table>
	<div id="expand_submit"><input type="button" value="Generate" onclick="post_expand_existing()"/></div>
	<div id="expand_loading" style="display:none"> <img src="img/loading.gif" alt="loading"/></div>
	<div id="expand_result">
		...	
	</div>

	<h2>Generate submaps</h2>
	<table>
	 <tr>
	   <td>Children type:</td>
	   <td>
			<select id="reproduction_type">
				<option value="0">Normal</option>
				<option value="1">Torus</option>
			</select>
		</td>
	 </tr>
	 <tr>
	   <td>Parent: </td>
		<td>
		<select id="reproduction_select">
<?php
$link = db_connect();

$types = array( 0 => "N", 1 => "T");
$types_color = array( 0 => "G", 1 => "C");

$ready_list = db_get_ready($link, 0, 500);
foreach($ready_list as $row)
{
	echo "				<option value=\"{$row['id']}_{$row['width']}_{$row['height']}\">{$row['short_name']}({$row['gid']}) {$row['width']}x{$row['height']},{$row['kernel']}," . $types[(int)isTypeTorus($row['type'])] . "," . $types_color[(int)isTypeColor($row['type'])] . ",((" . getTypeRange($row['type']) . "))</option>\n";
}

db_close($link);
?>
				</select>
		</td>
	 </tr>
	 <tr>
	   <td>Minimum width:</td>
	   <td><input type="text" name="reproduction_width" id="reproduction_width" value="20"/></td>
	 </tr>
	 <tr>
	   <td>Minimum height:</td>
	   <td><input type="text" name="reproduction_height" id="reproduction_height" value="20"/></td>
	 </tr>
	 <tr>
		<td colspan="2">The minimum height should be smaller than width since all the maps should be in landscape mode</td>
	 <tr>
	</table>

	<div id="reproduction_submit"><input type="button" value="Generate" onclick="post_reproduct_new()"/></div>
	<div id="reproduction_loading" style="display:none"> <img src="img/loading.gif" alt="loading"/></div>
	<div id="reproduction_result">
		...	
	</div>
	<br />
	
<?php
} //logged in end
else {
?>
	<h2>You should log in to generate new!</h2>
<?php
}
?>
	
</div>
