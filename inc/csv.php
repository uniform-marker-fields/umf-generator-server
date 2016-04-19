<?php

function get_csv($type, $width, $height, $tile_size, $data, $colors, $kernel_type = 0, $module_type = 0, $t_equal = 0)
{

	$csv_str = "$width\n$height\n$tile_size;$kernel_type;$module_type;$t_equal\n$type".($colors==""?"":";".$colors)."\n";

	for($y = 0; $y < $height; $y++)
	{
		for($x = 0; $x < $width - 1;$x++)
		{
			$csv_str .= $data[$y*$width + $x].";";
		}
		$csv_str .= $data[$y*$width + $width - 1]."\n";
	}
	return $csv_str;
}

?>
