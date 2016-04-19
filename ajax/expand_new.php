<?php

require_once('../inc/defines.php');
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


$top = intval($_REQUEST["t"]);
$bottom = intval($_REQUEST["b"]);
$right = intval($_REQUEST["r"]);
$left = intval($_REQUEST["l"]);
$id = $_REQUEST["id"];
$type = makeType(intval($_REQUEST["y"]), intval($_REQUEST["t_c"]), intval($_REQUEST["t_r"]));
$colors = trim($_REQUEST["colors"]);

$width = 1;
$height = 1;
$kernel = 4;
$kernel_type = 0;
$old_type = 1;

$result = array();
$result["success"] = True;

$db = db_connect();

$query="SELECT * FROM markers_ready WHERE `id`=$id";
$db_res = $db->query($query);
$old_data='';
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
	$old_type = intval($row['type']);
	$old_data = $row['data'];
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

$type = makeType(intval($_REQUEST["y"]), intval($_REQUEST["t_c"]), getTypeRange($old_type));

$new_width = $right + $left + $width;
$new_height = $top + $bottom + $height;

if($new_width > 0 && $new_height > 0 && $kernel > 0 && $left >= 0 && $bottom >=0 && $right >=0 && $top >=0 && checkColors($type,$colors))
{
	$result["width"] = $new_width;
	$result["kernel"] = $kernel;
	$result["height"] = $new_height;
} else {
	$result["success"] = False;
}


if($result["success"])
{
	
	$range = getTypeRange($type);
	
	$data = "";
	for($h_i = 0; $h_i < $new_height; $h_i++)
	{
		for($w_i = 0; $w_i < $new_width; $w_i++)
		{
				$v = rand() % $range;
				if ($v < 10)
				{
					$data .= (string)($v);
				} else {
					$data .= chr($v+87);
				}
		}
	}
	
	//copy the old one
	
	for($h_i = 0; $h_i < $height; $h_i++)
	{
		for($w_i = 0; $w_i < $width; $w_i++)
		{
			$new_index = ($h_i + $top)*$new_width + $left + $w_i;
			$old_index = $h_i*$width + $w_i;
			$data[$new_index] = $old_data[$old_index];
		}
	}
	
	$selfconflicts = array();
	$conlficts_arr = checkMap($type, $new_width, $new_height, $kernel, $kernel_type, $old_module_type, $data, $colors, true, $selfconflicts);
	$conflicts = count($conlficts_arr);
	$plusConflicts = 0;
	$cost = ($old_img_id > 0 ? 100000000.0 : checkCost($type, $new_width, $new_height, $kernel, $old_module_type, $data, $colors, $conlficts_arr, $selfconflicts, $plusConflicts));
	$conflicts += $plusConflicts;
	
	$result['width'] = $type . '.' . $new_width . '.' . $new_height . '.' . $kernel . '.' . $conflicts . '.' . $cost . '.' . $colors . '.' . $data;
	
	db_queue_push_new($db, rand(), $type, $new_width, $new_height, $kernel, $kernel_type, $old_module_type, $conflicts, $data, $cost, $colors, $old_img_id, $old_img_alg, $old_threshold_equal, $old_cost_neighbors, $old_cost_similarity, $old_img_conv);
}

db_close($db);

echo json_encode($result);

?>
