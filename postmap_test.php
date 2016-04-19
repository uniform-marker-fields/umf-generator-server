<?php

ob_start();
?>
<html>
<head>
<title>post result</title>
</head>
<body>
<h1>results</h1>
<?php
echo "<b>Size (w,h):</b>".$_GET["w"].", ".$_GET["h"]."<br />";
echo "<b>Unique size (k):</b> ".$_GET["k"]." <br />";
echo "<b>Type (t): </b>".$_GET["t"]." <br />";
echo "<b>Number of conlficts (c):</b> ".$_GET["c"]."<br />";
echo "<b>Data:</b><br />";
?>
<p style="font-family: monospace">
<?php
$id = $_GET["id"];
$width = intval($_GET["w"]);
$height = intval($_GET["h"]);
$kernel = intval($_GET["k"]);
$data = trim($_REQUEST["data"]);


for($i = 0; $i < $height; $i++)
{
	for($j = 0; $j < $width; $j++)
	{
		echo $_REQUEST["data"][$j + $i*$width];
	}
	echo "<br />";
}
?>
</p>
</body>
</html>
<?php
ob_end_flush();
?>