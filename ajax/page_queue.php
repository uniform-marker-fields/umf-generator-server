<?php
require_once('../inc/defines.php');
require_once('../inc/database.php');
require_once('../inc/spager.php');
require_once('../inc/common.php');


//==================================================================
//SESSION
session_start();

if(!isset($_SESSION['logged_in']))
{
	$_SESSION['logged_in'] = False;
}

$logged_in = $_SESSION['logged_in'];

//==================================================================


	$link = db_connect();

	$current_page = 1;
	if(isset($_REQUEST['s']))
	{
		$current_page = intval($_REQUEST['s']);
	}
	$page_count = intval((db_get_queue_count($link) + ROWS_ON_PAGE - 1)/ROWS_ON_PAGE);
	
	$pager = new Pager("queue_get_page", $page_count, $current_page);

?>

<!-- *************************************QUEUE********************************* -->
<div id="queue">
	<h2>Queue</h2>
<?php
	$pager->doPaging();
?>
<table class="data">
	<thead>
	   <tr>
  		<th>Short name (GID)</th>
  		<th>Type</th>
  		<th>Image</th>
  		<th>Size</th>
  		<th>Kernel</th>
  		<th>Conflicts</th>
  		<th>Cost</th>
  		<th>Last assigned</th>
  		<?php if($logged_in){ ?> <th>Boost</th> <?php } ?>
  		<th>Probability</th>
		<th>Info</th>
		</tr>
	</thead>
<?php
	
	$types = array( 0 => "Normal", 1 => "Torus");
	$types_color = array( 0 => "Grayscale", 1 => "Color");
	
	$results = db_get_queue($link, $current_page-1);

	$row_index = ($current_page-1)*ROWS_ON_PAGE;
	foreach($results as $row)
	{
		$row_index++;
		
		$currprob = 1.0/(20 + $row_index*$row_index);
		if($row['boost'])
		{
			$currprob = 1.0/(1 + $row_index*$row_index);
		}
		
		$even = ($row_index % 2) == 0;
		
		echo "<tr class=\"".($even ? "even" : "odd")."\">\n";
		echo "<td>{$row['short_name']} ({$row['gid']})</td>\n";
		echo "<td>";
		echo "<div style=\"float: left;margin-right:3px;\"><img src=\"".common_get_type_img($row['type'])."\" height=\"10px\" /></div><br style=\"clear:both\"/>";
		echo common_get_colors($row['type'], $row['colors']);
		echo "</td>\n";
		echo "<td>".($row['img_id']==0?"<span class=\"no_image\">no image</span>":"<img src=\"".getImageURL($row['img_id'], $row['img_ext'], true)."\" alt=\"\">")."</td>\n";
		echo "<td>{$row['width']}x{$row['height']}</td>\n";
		echo "<td>" . common_get_kernel_type($row['kernel'],$row['kernel_type'],$row['module_type']) . "</td>\n";
		echo "<td>{$row['conflicts']}</td>\n";
		echo "<td>{$row['cost']}</td>\n";
		echo "<td>{$row['last_assigned']}</td>\n";
		if($logged_in){
			echo "<td><input type=\"checkbox\" name=\"state_{$row['id']}\"".($row['boost'] != 0 ? " checked=\"true\"": "")." onclick=\"toggle_map_boost({$row['id']})\" /></td>\n";
		}
		echo "<td>{$currprob}</td>\n";
		echo "<td><a href=\"index.php?p=queue_details&id={$row['id']}\">info</a>\n";
		echo "</tr>\n";
	}
	
	db_close($link);
?>
</table>
<?php
	$pager->doPaging();
?>
</div>
