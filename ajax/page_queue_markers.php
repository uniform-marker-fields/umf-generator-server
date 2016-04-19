<?php
require_once('../inc/defines.php');
//require_once('../inc/database.php');
require_once('../inc/spager.php');
require_once('../inc/common.php');


session_start();
$logged_in = False;
if(!isset($_SESSION['logged_in']))
{
	$_SESSION['logged_in'] = False;
}

$logged_in = $_SESSION['logged_in'];


$link = db_connect();


$where_sql_a = array();

//name
if(!empty($_COOKIE['inqueue_filter_name']))
{
	$where_sql_a[] = " `short_name` LIKE '%".$_COOKIE['inqueue_filter_name']."%' ";
}


//kernel
if(!empty($_COOKIE['inqueue_filter_kernel_low']))
{
	$where_sql_a[] = " `kernel` >= ".$_COOKIE['inqueue_filter_kernel_low']." ";
}
if(!empty($_COOKIE['inqueue_filter_kernel_high']))
{
	$where_sql_a[] = " `kernel` <= '".$_COOKIE['inqueue_filter_kernel_high']."' ";
}

//type
if(!empty($_COOKIE['inqueue_filter_type']))
{
	$typestr = $_COOKIE['inqueue_filter_type'];
	if($typestr == 'C' || $typestr == 'G')
	{
		$where_sql_a[] = " (`type` & ".TYPE_COLOR_BIT.") = ".((bool)($typestr == 'C')?TYPE_COLOR_BIT:0);
	}
}


//color range
if(!empty($_COOKIE['inqueue_filter_range_low']))
{
	$where_sql_a[] = " (`type` & ".TYPE_RANGE_BITS.") >= ".makeType(0,0,intval($_COOKIE['inqueue_filter_range_low']));
}
if(!empty($_COOKIE['inqueue_filter_range_high']))
{
	$where_sql_a[] = " (`type` & ".TYPE_RANGE_BITS.") <= ".makeType(0,0,intval($_COOKIE['inqueue_filter_range_high']));;
}

if(isset($_COOKIE['inqueue_filter_color_enable']) && $_COOKIE['inqueue_filter_color_enable'] == 'true')
{
	if(!empty($_COOKIE['inqueue_filter_color_enable']))
	{
		$where_sql_a[] = " `colors` LIKE '%".$_COOKIE['inqueue_filter_color']."%'";
	}
}


$where_sql = "";
if (count($where_sql_a) > 0)
{
	$where_sql = implode(" AND ", $where_sql_a);
}

$current_page = 1;
if(isset($_REQUEST['s']))
{
	$current_page = intval($_REQUEST['s']);
}
$marker_count = db_get_queue_markers_count($link, $where_sql);
$page_count = intval((db_get_queue_markers_count($link, $where_sql) + ROWS_ON_PAGE - 1)/ROWS_ON_PAGE);

$pager = new Pager("queue_markers_get_page", $page_count, $current_page);

?>

<!-- *************************************QUEUE_MARKERS********************************* -->

<div id="queue_markers">
	<h2>In-queue markers</h2>

<div class="panel">
	<div class="panel_head">Filters <hr /></div>
	<form onSubmit="filter_in_queue(); return false;">
	<div class="panel_content">
		<fieldset class="panel_block">
			<legend>General</legend>
			<p>
				Name Contains:<br />
				<input id="filter_name" value="" />
			</p>
			<p>
				Kernel:
				&lt;<input type="text" size="3" id="filter_kernel_low" value="2" />
							; <input type="text" size="3" id="filter_kernel_high" value="32" /> &gt;
			</p>
		</fieldset>
		
		<fieldset class="panel_block">
			<legend>Colors</legend>
			<table>
				<tr>
					<td>Type:</td>
					<td>
						<select id="filter_type">
							<option value="A">All</option>
							<option value="C">Color</option>
							<option value="G">Grayscale</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>Color count:</td>
					<td>
						&lt;<input type="text" size="3" id="filter_range_low" value="2" />
							; <input type="text" size="3" id="filter_range_high" value="32" /> &gt;
					</td>
				</tr>
				<tr>
					<td>
						<input type="checkbox" id="filter_color_enable" checked="false">Color:</input>
					</td>
					<td>
						<input id="filter_color" class="color_picker" type="text" size="6" maxlength="6" value="ffffff"/>
					</td>
				</tr>
			</table>
		</fieldset>
		<div class="panel_block">
			<input type="button" onClick="update_marker_filters()" value="Filter"/>
			<input type="button" onClick="reset_marker_filters()" value="Reset" />
		</div>
	</div>
</div>
<br class="cleaner" />


<?php
	$pager->doPaging();



$sort_order = array(
	'ugid' => " `short_name` ASC, `c` ASC, `cost` ASC ",
	'dgid' => " `short_name` DESC, `c` ASC, `cost` ASC ",
	'usize' => " `width`*`height` ASC, `c` ASC, `cost` ASC ",
	'dsize' => " `width`*`height` DESC, `c` ASC, `cost` ASC ",
	'ukernel' => " `kernel` ASC, `c` ASC, `cost` ASC ",
	'dkernel' => " `kernel` DESC, `c` ASC, `cost` ASC ",
	'ucost' => "`cost` ASC, `c` ASC",
	'dcost' => "`cost` DESC, `c` ASC",
	'uconflict' => "`c` ASC, `cost` ASC",
	'dconflict' => "`c` DESC, `cost` ASC",
	'ulasta' => " `last_assigned` ASC, `c` ASC, `cost` ASC ",
	'dlasta' => " `last_assigned` DESC, `c` ASC, `cost` ASC ",
	'ulastm' => " `last_modified` ASC, `c` ASC, `cost` ASC ",
	'dlastm' => " `last_modified` DESC, `c` ASC, `cost` ASC ",
	'urun' => " `state` ASC, `c` ASC, `cost` ASC ",
	'drun' => " `state` DESC, `c` ASC, `cost` ASC ",
	'utest' => " `testing` ASC, `c` ASC, `cost` ASC ",
	'dtest' => " `testing` DESC, `c` ASC, `cost` ASC ",
	'uforce' => " `force_continue` ASC, `c` ASC, `cost` ASC ",
	'dforce' => " `force_continue` DESC, `c` ASC, `cost` ASC ",
);

$active_sort = 'ucost';
if(isset($_COOKIE['inqueue_sort_by'])) {
	$active_sort = $_COOKIE['inqueue_sort_by'];
}
if(empty($active_sort))
{
	$active_sort = 'ucost';
}

?>

<table class="data">
  <thead>
  	<tr>
  		<th>Short_name (GID) </th>
  		<th>Type </th>
  		<th>Image</th>
  		<th>Size  </th>
  		<th>Kernel  </th>
  		<th>Cost </th>
  		<th>Conflicts  </th>
  		<th>Last modified  </th>
  		<th>Last assigned  </th>
  		<?php if($logged_in){ ?>
			<th>Run</th>
			<th>Test</th>
			<th>Force</th>
			<th>Actions</th>
  		<?php } ?>
  	</tr>
	<tr>
		<td><?php echo get_sort_arrows("ugid", "dgid", $active_sort, 'sort_inqueue'); ?></td>
		<td></td>
		<td></td>
		<td><?php echo get_sort_arrows("usize", "dsize", $active_sort, 'sort_inqueue'); ?></td>
		<td><?php echo get_sort_arrows("ukernel", "dkernel", $active_sort, 'sort_inqueue'); ?></td>
		<td><?php echo get_sort_arrows("ucost", "dcost", $active_sort, 'sort_inqueue'); ?></td>
		<td><?php echo get_sort_arrows("uconflict", "dconflict", $active_sort, 'sort_inqueue'); ?></td>
		<td><?php echo get_sort_arrows("ulastm", "dlastm", $active_sort, 'sort_inqueue'); ?></td>
		<td><?php echo get_sort_arrows("ulasta", "dlasta", $active_sort, 'sort_inqueue'); ?></td>
		
  		<?php if($logged_in){ ?>
			<td><?php echo get_sort_arrows("urun", "drun", $active_sort, 'sort_inqueue'); ?></td>
			<td><?php echo get_sort_arrows("utest", "dtest", $active_sort, 'sort_inqueue'); ?></td>
			<td><?php echo get_sort_arrows("uforce", "dforce", $active_sort, 'sort_inqueue'); ?></td>
			<td></td>
  		<?php } ?>
	</tr>
	</thead>
<?php

	$types = array( 0 => "Normal", 1 => "Torus");
	$types_color = array( 0 => "Grayscale", 1 => "Color");
	
	$results = db_get_queue_markers($link, $current_page-1,
			ROWS_ON_PAGE,
			$sort_order[$active_sort],
			$where_sql);
			
	$row_index = 0;
	foreach($results as $row)
	{
		$row_index++;
	 
	  $even = ($row_index % 2) == 0;
	
		echo "<tr class=\"".($even ? "even" : "odd")."\">\n";
		echo "<td><a href=\"index.php?p=marker_details&gid={$row['gid']}\">{$row['short_name']} ({$row['gid']})</a></td>\n";
		echo "<td>";
		echo "<div style=\"float: left;margin: 0px 1px 1px 0px;\"><img src=\"".common_get_type_img($row['type'])."\" height=\"10px\" /></div><br style=\"clear:both\"/>";
		echo common_get_colors($row['type'], $row['colors']);
		echo "</td>\n";
		echo "<td>".($row['img_id']==0?"<span class=\"no_image\">no image</span>":"<img src=\"".getImageURL($row['img_id'], $row['img_ext'], true)."\" alt=\"\">")."</td>\n";
		echo "<td>{$row['width']}x{$row['height']}</td>\n";
		echo "<td>" . common_get_kernel_type($row['kernel'],$row['kernel_type'],$row['module_type']) . "</td>\n";
		printf("<td>%.01f (%.01f<img src=\"img/icon_%s.gif\" />)</td>\n", $row['cost'], $row['cost_diff'], ($row['cost_diff'] < 0)?"down_active":"up_hover");
		printf("<td>%d (%d<img src=\"img/icon_%s.gif\" />)</td>\n", $row['conflicts'], $row['conflicts_diff'], ($row['conflicts_diff'] < 0)?"down_active":"up_hover");
		echo "<td>{$row['last_modified']}</td>\n";
		echo "<td>{$row['last_assigned']}</td>\n";
		if($logged_in)
		{
			echo "<td><input type=\"checkbox\" name=\"state_{$row['gid']}\"".($row['state'] != 0 ? " checked=\"true\"": "")." onclick=\"toggle_gid_state({$row['gid']})\" /></td>\n";
			echo "<td><input type=\"checkbox\" name=\"testing_{$row['gid']}\"".($row['testing'] != 0 ? " checked=\"true\"": "")." onclick=\"toggle_gid_testing({$row['gid']})\" /></td>\n";
			echo "<td><input type=\"checkbox\" name=\"force_continue_{$row['gid']}\"".($row['force_continue'] != 0 ? " checked=\"true\"": "")." onclick=\"toggle_gid_force_continue({$row['gid']})\" /></td>\n";
			echo "<td><input type=\"button\" value=\"Remove\" onclick=\"remove_map({$row['gid']})\"/></td>";
		}
		echo "</tr>\n";
	}
	
	db_close($link);
?>
</table>
<?php
	$pager->doPaging();
?>
</div>
