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

$id = intval($_REQUEST["id"]);

$result = array();
$result["success"] = True;

$db = db_connect();

$query="SELECT * FROM markers_ready WHERE `id`=$id";
$db_res = $db->query($query);
if($db_res)
{
	if ($row = $db_res->fetch_assoc())
	{	
		$selfconflicts = array();
		$conlficts_arr = array();
		
		// TODO maybe a more elegant way is needed to set the initial cost value...
		$cost = (intval($img_id) > 0 ? 1000000.0 : checkCost($row['type'], $row['width'], $row['height'], $row['kernel'], $row['module_type'], $row['data'], $row['colors'], $conlficts_arr, $selfconflicts));
		
		db_queue_push_new($db, rand(), $row['type'], $row['width'], $row['height'], $row['kernel'], $row['kernel_type'], $row['module_type'], 0, $row['data'], $cost, $row['colors'], $row['img_id'], $row['img_alg'], $row['threshold_equal'], $row['cost_neighbors'], $row['cost_similarity'], $row['img_conv'], $row['runtime'], true);
		db_ready_marker_remove($db, $id);
		
	} else {
		$result["success"] = False;
	}
	
} else {
	$result["success"] = False;
}

db_close($db);

echo json_encode($result);

?>
