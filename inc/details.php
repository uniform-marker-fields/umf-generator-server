<?php

require_once('defines.php');
require_once('../inc/checkmap.php');
require_once('../inc/svg.php');
require_once('../inc/common.php');

// @TODO add new details
function show_info($info, $ready=0)
{
?>

<table class="info">
	<thead>
	</thead>
	<tbody>
		<tr>
			<td colspan="2">
				<?php
				
				$collisions = array();
				if(isset($info['conflicts']) && $info['conflicts'] > 0)
				{
					$collisions = checkmap($info['type'], $info['width'], $info['height'], $info['kernel'], $info['kernel_type'], $info['module_type'], $info['data'], $info['colors'], true);
				}
				$svg_str = get_svg($info['type'], $info['width'], $info['height'], $info['kernel'], $info['module_type'], $info['data'], $info['colors'], $collisions);
				
				echo $svg_str;
				?>
			</td>
		</tr>
		<tr>
			<td>Type:</td>
			<td>
			<img src="<?php echo common_get_type_img($info['type']); ?>"  height="16px"/>
			<?php

			$types = array( 0 => "Normal", 1 => "Torus");
			$types_color = array( 0 => "Grayscale", 1 => "Color");

			echo $types[(int)isTypeTorus($info['type'])]." - ";
			echo $types_color[(int)isTypeColor($info['type'])];
			echo " (".getTypeRange($info['type']).")<br />\n";
			echo common_get_colors($info['type'], $info['colors']);
			?>
			</td>
		</tr>
		<tr>
			<td>Width, Height (Tile size):</td>
			<td>
				<?php
				echo "{$info['width']} x {$info['height']} ( {$info['kernel']} )\n";
				?>
			</td>
		</tr>
		<tr>
			<td>Window definition:</td>
			<td>
				<?php
				echo common_get_kernel_type($info['kernel'],$info['kernel_type'],$info['module_type']);
				?>
			</td>
		</tr>
		<tr>
			<td>Threshold for equal:</td>
			<td>
				<?php
				echo "{$info['threshold_equal']}\n";
				?>
			</td>
		</tr>
		<?php if(isset($info['img_id']) && $info['img_id'] != 0){ ?>
		<tr>
			<td>Image:</td>
			<td>
				<?php
				echo "<img src=\"".getImageURL($info['img_id'], $info['img_ext'], true)."\" alt=\"\">\n";
				?>
			</td>
		</tr>
		<tr>
			<td>Image algorithm:</td>
			<td>
				<?php
				echo $info['img_alg']."\n";
				?>
			</td>
		</tr>
		<?php } ?>
		<?php if(isset($info['cost'])){ ?>
		<tr>
			<td>Conflicts, Cost:</td>
			<td>
				<?php
				echo "{$info['conflicts']} conflicts - {$info['cost']}\n";
				?>
			</td>
		</tr>
		<?php } 
		if(isset($info['last_assigned']))
		{
		?>
		<tr>
			<td>Last assigned:</td>
			<td>
				<?php
					echo $info['last_assigned'];
				?>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td>Runtime:</td>
			<td>
				<?php echo $info['runtime']; ?>
			</td>
		</tr>
		
		<?php if(isset($info['boost'])) { ?>
		<tr>
			<td>Boosted:</td>
			<td>
				<?php
				
					echo "<input type=\"checkbox\" name=\"state_{$info['id']}\"".($info['boost'] != 0 ? " checked=\"true\"": "")." onclick=\"toggle_map_boost({$info['id']})\" /></td>\n";
				?>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td>Data:</td>
			<td>
				<?php
					echo "	<a href=\"get_xml.php?id={$info['id']}&ready={$ready}\">xml</a>\n";
					echo "  <a href=\"get_svg.php?id={$info['id']}&ready={$ready}\">svg</a>\n"; 
					echo "  <a href=\"get_svg_cost.php?id={$info['id']}&ready={$ready}\">cost</a>\n";
					// TODO make the zip file..
					//echo "  <a href=\"get_marker_zip.php?id={$info['id']}&ready={$ready}\">zip</a><br />\n";
					//echo "|	<a href=\"get_csv.php?id={$info['id']}&ready={$ready}\">csv (deprecated)</a>\n";
				?>
			</td>
		</tr>
	</tbody>
</table>
<?php
}

?>
