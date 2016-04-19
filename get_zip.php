<?php

require_once('inc/defines.php');
require_once('inc/database.php');
require_once('inc/common.php');
require_once('inc/svg.php');
require_once('inc/csv.php');
require_once('inc/xml.php');


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


//header("Content-type: text/plain");


$zipfile = "temp/pmg_all_ready.zip";
if(!is_dir("temp/csv_svg_ready"))
{
	mkdir("temp/csv_svg_ready");
}
$data_path = "temp/csv_svg_ready/";


$zip = new ZipArchive();

//open archive

if($zip->open($zipfile, ZipArchive::CREATE|ZipArchive::OVERWRITE) !== TRUE)
{
	die("Could not open archive");
}

$link = db_connect();

//$query="SELECT `type`,`short_name`,`gid`,`id`,`width`,`height`,`kernel`,`data`,`colors`,`kernel_type`,`module_type`,`threshold_equal` FROM `markers_ready`";

$query = "SELECT `mr`.`type`, `mr`.`width`, `mr`.`height`, `mr`.`kernel`, `mr`.`id`, `mr`.`gid`, `mr`.`data`, 0 as `conflicts`
				, `mr`.`colors`, `mr`.`img_id`, `mr`.`img_alg`, `img`.`img_extension`, `mr`.`runtime`, `mr`.`kernel_type`, `mr`.`module_type`
				, `mr`.`threshold_equal`, `mr`.`cost_neighbors`, `mr`.`cost_similarity`, `mr`.`img_conv`
				FROM `markers_ready` as `mr`
				LEFT JOIN `images` as `img` ON `mr`.`img_id`=`img`.`img_id`";

$res = $link->query($query);
if($res)
{
	while($row = $res->fetch_assoc())
	{
		$csv_str = get_csv($row['type'],$row['width'], $row['height'], $row['kernel'], $row['data'],$row['colors'],$row['kernel_type'],$row['module_type'],$row['threshold_equal']);
		$fname = "_w".$row['width']."xh".$row['height']."_k".$row['kernel']."_c".getTypeRange($row['type'])."_".$row['gid']."_".$row['id'];
        $dirname = '';
        if($row['module_type'] == MODULE_TYPE_HEXA) {
            $fname = "hexa".$fname;
            $dirname = 'hexa/';
        } else {
            if(isTypeTorus($row['type'])) {
                $fname = "_torus".$fname;
                $dirname = 'torus/'.$dirname;
            }
            $fname = "umf".$fname;
            if(isTypeColor($row['type'])) {
                $dirname = 'color/'.$dirname;
            } else if(getTypeRange($row['type']) <= 2) {
                $dirname = 'binary/'.$dirname;
            } else {
                $dirname = 'grayscale/'.$dirname;
            }
        }
        $fname = $dirname.$fname;
		$fp = fopen($data_path.$fname.".csv", 'w');
		fwrite($fp, $csv_str);
		fclose($fp);
		unset($csv_str);
        
        $xml_str = get_xml($row);
        $fp = fopen($data_path.$fname.".xml", 'w');
        fwrite($fp, $xml_str);
        fclose($fp);
        unset($xml_str);
		
		$arr_empty = array();
        $svg_str = get_svg($row['type'], $row['width'], $row['height'], $row['kernel'], $row['module_type'], $row['data'], $row['colors'], $arr_empty);
		
		$fp = fopen($data_path.$fname.".svg", 'w');
		fwrite($fp, $svg_str);
		fclose($fp);
		unset($svg_str);
		
		$zip->addFile($data_path.$fname.".svg");
		$zip->addFile($data_path.$fname.".csv");
		//$zip->addFromString($fname.".csv", $csv_str);
		//$zip->addFromString($fname.".svg", $svg_str);
	}
} else {
	echo $link->error;
    die($link->error);
}


$zip->close();

header("Content-type: application/octet-stream");
header('Content-Disposition: attachment; filename="pmg_ready.zip"');

readfile($zipfile);

?>
