<?php

require_once('../inc/defines.php');
require_once('../inc/database.php');
require_once('../inc/checkmap.php');
require_once('../inc/checkcost.php');
require_once('../inc/resize-class.php');
require_once('../inc/common.php');

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

// TODO starting colors..

$width = intval($_REQUEST["w"]);
$height = intval($_REQUEST["h"]);
$kernel = intval($_REQUEST["k"]);
$count = intval($_REQUEST["c"]);
$name = $_REQUEST["n"];
$type = makeType(intval($_REQUEST["t"]), intval($_REQUEST["t_c"]), intval($_REQUEST["t_r"]));
$colors = strtolower(trim($_REQUEST["colors"]));
$kernel_type = intval($_REQUEST["k_t"]);
$img_id = intval($_REQUEST["img_id"]);
$img_alg = trim($_REQUEST["img_alg"]);
$img_rnd = intval($_REQUEST["img_rnd"]);
$module_type = intval($_REQUEST["m_t"]);
$threshold_equal = intval($_REQUEST["t_e"]);
$cost_neighbors = trim($_REQUEST["c_n"]);
$cost_similarity = floatval($_REQUEST["c_s"]);
$img_conv = trim($_REQUEST["img_conv"]);

$type_color = isTypeColor($type);

$result = array();
$result["success"] = True;

if($width > 0 && $height > 0 && $kernel > 0 && $count > 0 && checkColors($type,$colors))
{
	$result["width"] = $width;
	$result["kernel"] = $kernel;
	$result["height"] = $height;
	$result["count"] = $count;
	$result["name"] = $name;
} else {
	$result["success"] = False;
}

$db = db_connect();

$resizedImage = null;
$newDimensions = array();
if ($img_id > 0)
{
	if ($db_row = $db->query("SELECT * FROM images WHERE img_id = ". $img_id)->fetch_assoc())
	{
		$resizeObj = new resize($_SERVER['DOCUMENT_ROOT'] . getImagePath($img_id, $db_row['img_extension']), $type_color);

		$newDimensions = $resizeObj->resizeImage($width, $height, 'auto');
		
		$resizedImage = $resizeObj->getResizedImage();
		
	} else {
		$result["success"] = False;
	}
}

if($result["success"])
{
	$range = getTypeRange($type);
	
	for($i = 0; $i < $count; $i++)
	{
		$data = "";
	
		for($w_i = 0; $w_i < $width; $w_i++)
		{
			for($h_i = 0; $h_i < $height; $h_i++)
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
		
		if ($img_id > 0 && $img_rnd == 0)
		{
			$all_colors = array();
			
			if ($type_color)
			{
				$all_colors = getColorsRGB($type, $colors);
			} else {
				$range = getTypeRange($type);
				
				for ($i = 0; $i < $range; $i++)
				{
					$c = (int)255*$i/($range-1);
					$all_colors[] = array($c, $c, $c);
				}
			}
			
			$h_start = (int) (($height - $newDimensions['optimalHeight'])/2);
			$w_start = (int) (($width - $newDimensions['optimalWidth'])/2);
			for($h_i = 0; $h_i < $newDimensions['optimalHeight']; $h_i++)
			{
				for($w_i = 0; $w_i < $newDimensions['optimalWidth']; $w_i++)
				{
					$data[($h_i+$h_start) * $width + $w_i + $w_start] = getClosestColor($all_colors, getColorGD(imagecolorat($resizedImage, $w_i, $h_i)));
				}
			}
			
		}
		
		$selfconflicts = array();
		$conlficts_arr = checkMap($type, $width, $height, $kernel, $kernel_type, $module_type, $data, $colors, true, $selfconflicts);
		$conflicts = count($conlficts_arr);
		$plusConflicts = 0;
		$cost = ($img_id > 0 ? 10000000.0 : checkCost($type, $width, $height, $kernel, $module_type, $data, $colors, $conlficts_arr, $selfconflicts, $plusConflicts));
		$conflicts += $plusConflicts;
		
		$ctime = microtime(True);
		$gid = intval($ctime*1000);
		
		db_queue_push_new($db, (empty($name)?rand():($name.($i>0?$i:""))), $type, $width, $height, $kernel, $kernel_type, $module_type, $conflicts, $data, $cost, $colors, $img_id, $img_alg, $threshold_equal, $cost_neighbors, $cost_similarity, $img_conv);
	}
}

db_close($db);

echo json_encode($result);

?>
