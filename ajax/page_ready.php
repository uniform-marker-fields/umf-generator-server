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
	
	
$where_sql_a = array();

//name
if(!empty($_COOKIE['ready_filter_name']))
{
	$where_sql_a[] = " `short_name` LIKE '%".$_COOKIE['ready_filter_name']."%' ";
}


//kernel
if(!empty($_COOKIE['ready_filter_kernel_low']))
{
	$where_sql_a[] = " `kernel` >= ".$_COOKIE['ready_filter_kernel_low']." ";
}
if(!empty($_COOKIE['ready_filter_kernel_high']))
{
	$where_sql_a[] = " `kernel` <= '".$_COOKIE['ready_filter_kernel_high']."' ";
}

//type
if(!empty($_COOKIE['ready_filter_type']))
{
	$typestr = $_COOKIE['ready_filter_type'];
	if($typestr == 'C' || $typestr == 'G')
	{
		$where_sql_a[] = " (`type` & ".TYPE_COLOR_BIT.") = ".((bool)($typestr == 'C')?TYPE_COLOR_BIT:0);
	}
}


//color range
if(!empty($_COOKIE['ready_filter_range_low']))
{
	$where_sql_a[] = " (`type` & ".TYPE_RANGE_BITS.") >= ".makeType(0,0,intval($_COOKIE['ready_filter_range_low']));
}
if(!empty($_COOKIE['ready_filter_range_high']))
{
	$where_sql_a[] = " (`type` & ".TYPE_RANGE_BITS.") <= ".makeType(0,0,intval($_COOKIE['ready_filter_range_high']));;
}

if(isset($_COOKIE['ready_filter_color_enable']) && $_COOKIE['ready_filter_color_enable'] == 'true')
{
	if(!empty($_COOKIE['ready_filter_color_enable']))
	{
		$where_sql_a[] = " `colors` LIKE '%".$_COOKIE['ready_filter_color']."%'";
	}
}


$where_sql = "";
if (count($where_sql_a) > 0)
{
	$where_sql = implode(" AND ", $where_sql_a);
}

	
	$page_count = intval((db_get_ready_count($link, $where_sql) + ROWS_ON_PAGE - 1)/ROWS_ON_PAGE);
	
	$pager = new Pager("ready_get_page", $page_count, $current_page);

?>

<!-- *************************************READY********************************* -->

<div id="ready">
	<h2>Ready</h2>
	<?php if($logged_in){ ?>
	<p>Get all in a zip: <a href="get_zip.php">link</a><p> <br />
	<?php } ?>


<div class="panel">
	<div class="panel_head">Filters <hr /></div>
	<form onSubmit="filter_ready(); return false;">
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
			<input type="button" onClick="update_ready_filters()" value="Filter"/>
			<input type="button" onClick="reset_ready_filters()" value="Reset" />
		</div>
	</div>
</div>
<br class="cleaner" />

<?php
	$pager->doPaging();
	
$sort_order = array(
	'ugid' => " `short_name` ASC, `width`*`height` DESC ",
	'dgid' => " `short_name` DESC, `width`*`height` DESC ",
	'usize' => " `width`*`height` ASC",
	'dsize' => " `width`*`height` DESC",
	'ukernel' => " `kernel` ASC, `width`*`height` DESC ",
	'dkernel' => " `kernel` DESC, `width`*`height` DESC "
);

$active_sort = 'dsize';
if(isset($_COOKIE['ready_sort_by'])) {
	$active_sort = $_COOKIE['ready_sort_by'];
}
if(empty($active_sort))
{
	$active_sort = 'dsize';
}
	
	
	
?>
<table class="data">
  <thead>
  	<tr>
  		<th>Short_name (GID)</th>
  		<th>Type</th>
  		<th>Image</th>
  		<th>Size</th>
  		<th>Kernel</th>
  		<?php if($logged_in){ ?>
			<th>Actions</th>
  		<?php } ?>
  	</tr>
	<tr>
		<td><?php echo get_sort_arrows("ugid", "dgid", $active_sort, 'sort_ready'); ?></td>
		<td></td>
		<td></td>
		<td><?php echo get_sort_arrows("usize", "dsize", $active_sort, 'sort_ready'); ?></td>
		<td><?php echo get_sort_arrows("ukernel", "dkernel", $active_sort, 'sort_ready'); ?></td>
		
  		<?php if($logged_in){ ?>
			<td></td>
  		<?php } ?>
	</tr>
  </thead>
<?php

	$types = array( 0 => "Normal", 1 => "Torus");
	$types_color = array( 0 => "Grayscale", 1 => "Color");

	$results = db_get_ready($link, $current_page-1, ROWS_ON_PAGE, $sort_order[$active_sort], $where_sql);
	$row_index = 0;
	foreach($results as $row)
	{
	  $row_index++;
	 
	  $even = ($row_index % 2) == 0;
	
		echo "<tr class=\"".($even ? "even" : "odd")."\">\n";
		echo "<td><a href=\"index.php?p=ready_details&id={$row['id']}\">{$row['short_name']} ({$row['gid']})</a></td>\n";
		echo "<td>";
		echo "<div style=\"float: left;margin: 0px 1px 1px 0px;\"><img src=\"".common_get_type_img($row['type'])."\" height=\"10px\" /></div><br style=\"clear:both\"/>";
		echo common_get_colors($row['type'], $row['colors']);
		echo "</td>\n";
		echo "<td>".($row['img_id']==0?"<span class=\"no_image\">no image</span>":"<img src=\"".getImageURL($row['img_id'], $row['img_ext'], true)."\" alt=\"\">")."</td>\n";
		echo "<td>{$row['width']}x{$row['height']}</td>\n";
		echo "<td>" . common_get_kernel_type($row['kernel'],$row['kernel_type'],$row['module_type']) . "</td>\n";
		//echo "<td>\n";
		//echo "<a href=\"get_csv.php?id={$row['id']}&ready=1\">csv</a>\n";
		//echo "<a href=\"get_svg.php?id={$row['id']}&ready=1\">svg</a>\n";
		//echo "<a href=\"get_svg_cost.php?id={$row['id']}&ready=1\">cost</a>\n";
		//echo "<a href=\"get_marker_zip.php?id={$row['id']}&ready=1\">zip</a>\n";
		//echo "</td>\n";
		if($logged_in)
		{
			echo "<td><input type=\"button\" value=\"Force continue\" onclick=\"ready_force_continue({$row['id']})\"/></td>";
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
