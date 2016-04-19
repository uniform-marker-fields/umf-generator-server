<?php

require_once('inc/defines.php');
require_once('inc/database.php');
require_once('inc/checkmap.php');
require_once('inc/checkcost.php');
require_once('inc/svg.php');
require_once('inc/svg_cost.php');
require_once('inc/csv.php');


$TYPE_READY = 1;
$TYPE_QUEUE = 0;

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
	echo "Trololol";
	return;
}
//==================================================================




$fname = "error";
$zipfile = "temp/{$fname}.zip";
if(!is_dir("temp/csv_svg_temp"))
{
	mkdir("temp/csv_svg_temp");
}
$data_path = "temp/csv_svg_temp/";


$zip = new ZipArchive();


$link = db_connect();

$id = 0;
if(isset($_REQUEST["gid"]))
{
	$id = db_get_smallest_conflict_gid($link, intval($_REQUEST["gid"]));
	$_REQUEST["ready"] = 0;
	$live = true;
} else {
	$id = $_REQUEST["id"];
}

$type = intval($_REQUEST["ready"]);

if($type == $TYPE_READY)
{
    $query = "SELECT `mr`.`type`, `mr`.`width`, `mr`.`height`, `mr`.`kernel`, `mr`.`gid`, `mr`.`data`, `mr`.`conflicts`
				, `mr`.`colors`, `mr`.`img_id`, `mr`.`img_alg`, `img`.`img_extension`, `mr`.`runtime`, `mr`.`kernel_type`, `mr`.`module_type`
				, `mr`.`threshold_equal`, `mr`.`cost_neighbors`, `mr`.`cost_similarity`, `mr`.`img_conv`
				FROM `markers_ready` as `mr`
				LEFT JOIN `images` as `img` ON `mr`.`img_id`=`img`.`img_id`
				WHERE `mr`.`id`=$id";
} else {
    $query = "SELECT `pq`.`type`, `pq`.`width`, `pq`.`height`, `pq`.`kernel`, `pq`.`gid`, `qd`.`data`, `pq`.`conflicts`
				, `pq`.`colors`, `pq`.`img_id`, `pq`.`img_alg`, `img`.`img_extension`, `qd`.`runtime`, `pq`.`kernel_type`, `pq`.`module_type`
				, `qd`.`threshold_equal`, `qd`.`cost_neighbors`, `qd`.`cost_similarity`, `qd`.`img_conv`
				FROM `process_queue` as `pq`
				LEFT JOIN `queue_data` as `qd` ON `pq`.`id`=`qd`.`id`
				LEFT JOIN `images` as `img` ON `pq`.`img_id`=`img`.`img_id`
				WHERE `pq`.`id`=$id";
}

$res = $link->query($query);

if($res)
{

	$row = $res->fetch_assoc();
	
	$selfconflicts = array();
	$conflicts_arr = checkMap($row['type'], $row['width'], $row['height'], 
			$row['kernel'], $row['kernel_type'], $row['module_type'], $row['data'], $row['colors'], true, $selfconflicts);
	$conflicts = count($conlficts_arr);
// TODO !!very later!! cost computings...
	$plusConflicts = 0;
	$cost = checkCost($row['type'], $row['width'], $row['height'],
			$row['kernel'], $row['module_type'], $row['data'], $row['colors'], $conflicts_arr, $selfconflicts, $plusConflicts);
	$conflicts += $plusConflicts;

	$fname = "pm_".$row['width']."x".$row['height']."_".$row['kernel']."_".$cost."_".$row['gid']."_".$id;


//CSV-------------------------------------------------------------
	$csv_str = get_csv($row['type'], $row['width'], $row['height'], $row['kernel'], $row['data'], $row['colors'], $row['kernel_type'], $row['module_type'], $row['threshold_equal']);
	

	$fp = fopen($data_path.$fname.".csv", 'w');
	fwrite($fp, $csv_str);
	fclose($fp);
	unset($csv_str);
		
//SVG------------------------------------------------------------------------
	$svg_str = get_svg($row['type'], $row['width'], $row['height'], $row['kernel'], $row['module_type'], $row['data'], $row['colors'], $conflicts_arr);
		
	$fp = fopen($data_path.$fname.".svg", 'w');
	fwrite($fp, $svg_str);
	fclose($fp);
	unset($svg_str);

//COST-----------------------------------------------------------------------------

	$costs = checkcost($row['type'], $row['width'], $row['height'], $row['kernel'], $row['module_type'], $row['data'], $row['colors'], $conflicts_arr, $selfconflicts, true);
	$cost_str = get_svg_cost($row['type'], $row['width'], $row['height'], $row['kernel'], $row['module_type'], $costs);

	$fp = fopen($data_path.$fname.".cost.svg", 'w');
	fwrite($fp, $cost_str);
	fclose($fp);
	unset($cost_str);

//ZIP--------------------------------------------------------------------
		//open archive

	$zipfile = "temp/".$fname.".zip";
	if($zip->open($zipfile, ZipArchive::CREATE|ZipArchive::OVERWRITE) !== TRUE)
	{
		die("Could not open archive");
	}

	$zip->addFile($data_path.$fname.".svg", $fname.".svg");
	$zip->addFile($data_path.$fname.".cost.svg", $fname.".cost.svg");
	$zip->addFile($data_path.$fname.".csv", $fname.".csv");
	//$zip->addFromString($fname.".csv", $csv_str);
	//$zip->addFromString($fname.".svg", $svg_str);

} else {
	echo $link->error;
}


header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$fname.zip\"");
//header("Content-type: text/plain");


$zip->close();
readfile($zipfile);

?>
