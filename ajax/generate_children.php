<?php

$start_time = time();
$TIME_LIMIT = 10;

require_once('../inc/defines.php');
require_once('../inc/common.php');
require_once('../inc/database.php');
require_once('../inc/checkmap.php');
require_once('../inc/checkcost.php');


header("Content-type: application/json");


//==================================================================
//SESSION
session_start();

if(!isset($_SESSION['logged_in']))
{
	$_SESSION['logged_in'] = False;
}

$logged_in = $_SESSION['logged_in'];

if(!$logged_in)
{
	$fail = array("success" => False);
	echo json_encode($fail);
	return;
}
//==================================================================


$type = intval($_REQUEST["t"]);
$min_width = intval($_REQUEST["w"]);
$min_height = intval($_REQUEST["h"]);
$id = $_REQUEST["id"];

$start_width = $min_width;
$start_height = $min_height;
$continue = False;
if(isset($_REQUEST["c"]))
{
	$continue = True;
	$start_width = intval($_REQUEST["sw"]);
	$start_height = intval($_REQUEST["sh"]);
}

$width = 1;
$height = 1;
$kernel = 4;
$kernel_type = 0;

$result = array();
$result["success"] = True;

$db = db_connect();

$query="SELECT * FROM markers_ready WHERE `id`=$id";
$db_res = $db->query($query);
$old_data='';
$old_colors="";
$old_type=0;
$old_img_id = 0;
$old_img_alg = "";
$old_module_type = MODULE_TYPE_SQUARE;
$old_threshold_equal = 0;
$old_cost_neighbors = "8.0,8.0:0;10.0,0.0:128";
$old_cost_similarity = 1.0;
$old_img_conv = "1";
if($db_res)
{
	$row = $db_res->fetch_assoc();
	$width = intval($row['width']);
	$height = intval($row['height']);
	$kernel = intval($row['kernel']);
	$kernel_type = intval($row['kernel_type']);
	$old_data = $row['data'];
	$old_type = intval($row['type']);
	$old_colors = $row['colors'];
	$old_img_id = intval($row['img_id']);
	$old_img_alg = $row['img_alg'];
	$old_module_type = $row['module_type'];
	$old_threshold_equal = $row['threshold_equal'];
	$old_cost_neighbors = $row['cost_neighbors'];
	$old_cost_similarity = $row['cost_similarity'];
	$old_img_conv = $row['img_conv'];
} else {
	$result["success"] = False;
	echo json_encode($result);
	exit();
}

$type = makeType($type, (int)isTypeColor($old_type), getTypeRange($old_type));
$colors = $old_colors;

if($min_width < $width && $min_height < $height)
{
	$result["count_ready"] = 0;
	$result["count_queue"] = 0;
} else {
	$result["success"] = False;
}


if($result["success"])
{
	//for easier generation pretend, that height is always smaller than width
	for($new_height = $min_height; $new_height <= $height; $new_height++)
	{
		for($new_width = max($new_height, $min_width); $new_width <= $width; $new_width++)
		{
			if($continue)
			{
				//start from the point were we left of
				$new_width = $start_width;
				$new_height = $start_height;
				$continue = False;
				continue;
			}
		
			$new_data = '';
			for($h_i = 0; $h_i < $new_height; $h_i++)
			{
				for($w_i = 0; $w_i < $new_width; $w_i++)
				{
					$old_index = $h_i*$width + $w_i;
					$new_data .= $old_data[$old_index];
				}
			}
			
			//check conflictsnew_height
			$selfconflicts = array();
			$conlficts_arr = checkMap($type, $new_width, $new_height, $kernel, $kernel_type, $old_module_type, $new_data, $colors, true, $selfconflicts);
			$conflicts = count($conlficts_arr);
			$plusConflicts = 0;
			$cost = ($old_img_id > 0 ? 10000000.0 : checkCost($type, $new_width, $new_height, $kernel, $old_module_type, $new_data, $colors, $conlficts_arr, $selfconflicts, $plusConflicts));
			$conflicts += $plusConflicts;
			
			if($conflicts == 0)
			{
				db_ready_push($db, 0, $type, $new_width, $new_height, $kernel, $kernel_type, $old_module_type, $new_data, $colors, $old_img_id, $old_img_alg, $old_threshold_equal, $old_cost_neighbors, $old_cost_similarity, $old_img_conv);
				$result["count_ready"] += 1;
			} else {
				db_queue_push_new($db, rand(), $type, $new_width, $new_height, $kernel, $kernel_type, $old_module_type, $conflicts, $new_data, $cost, $colors, $old_img_id, $old_img_alg, $old_threshold_equal, $old_cost_neighbors, $old_cost_similarity, $old_img_conv);
				$result["count_queue"] += 1;
			}
			$result["ready_width"] = $new_width;
			$result["ready_height"] = $new_height;
			
			$current_time = time();
			if( ($current_time - $start_time) > $TIME_LIMIT)
			{
				$new_height = $height + 1;
				$new_width = $width + 1;
				$result["id"] = $id;
				$result["min_width"] = $min_width;
				$result["min_height"] = $min_height;
				$result["type"] = $type;
				$result["continue"] = True;
				break;
			}
		}
	}
}

db_close($db);

echo json_encode($result);

?>
