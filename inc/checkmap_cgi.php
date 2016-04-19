#!/usr/bin/php
<?php

include 'checkmap.php';

if( count($argv) < 2)
{
	echo "Too few arguments, Pleas define a csv file to check";
	return;
}

$filename = $argv[1];
$handle = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
fclose($handle);

$lines = preg_split('/\n/', $contents);

$width = intval($lines[0]);
$height = intval($lines[1]);
$k = 4;

$data = "";

for($i = 0; $i < $height; $i++)
{
	$fields = preg_split('/[,;]/', $lines[$i + 2]);
	$data .= trim(implode('', $fields));
}


echo "Conflicts: ".checkMap(0, $width, $height, $k, $data)."\n";

?>
