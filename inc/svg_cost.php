<?php

require_once('common.php');

function get_color_for($v)
{
	if ($v > 255) {
		$v = 255;
	}
	$i = intval($v);
	$hex = dechex($i);
	if (strlen($hex) < 2) {
		$hex = "0".$hex;
	}
	return "#".$hex."0000";
}

function get_svg_cost($type, $width, $height, $tile_size, $module_type, $costs, $with_offset = True) // $data, $colors, $collisions, $with_offset = True)
{	
	// backwards compatibility
	$type_NEW = $type;
	$type = (int)isTypeTorus($type);
	// ...

	$black = "#000000";
	$white = "#ffffff";
	$lightGray = "#cdcdcd";
	$darkGray = "#565656";
	
	
	$offset = 0;
	if($type == 1 && $with_offset) //torus
	{
		$offset = $tile_size - 1;
	}

	$range = getTypeRange($type_NEW);
	$type_color = isTypeColor($type_NEW);

	$pixel_size = 10;
	
	$svg_width = ($width + 2*$offset)*$pixel_size;
	$svg_height = ($height + 2*$offset)*$pixel_size;
	if ($module_type == MODULE_TYPE_HEXA)
	{
		$svg_width = $width * $pixel_size * M_SQRT3 + 1.0 * $pixel_size * M_SQRT3 / 2;
		$svg_height = $height * $pixel_size * 3 / 2.0 + $pixel_size / 2.0;
	}
	/*$colors[0] = $black;
	$colors[1] = $white;
	$colors[2] = $darkGray;
	$colors[3] = $lightGray;*/
	$svg_str = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>

<svg
   xmlns:dc="http://purl.org/dc/elements/1.1/"
   xmlns:cc="http://creativecommons.org/ns#"
   xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
   xmlns:svg="http://www.w3.org/2000/svg"
   xmlns="http://www.w3.org/2000/svg"
   version="1.1"
   ';
   $svg_str .= "   width=\"".$svg_width."\"\n";
   $svg_str .= "   height=\"".$svg_height."\"\n";
   $svg_str .= '
   id="svg2">
  <defs
     id="defs4" />
  <metadata
     id="metadata7">
    <rdf:RDF>
      <cc:Work
         rdf:about="">
        <dc:format>image/svg+xml</dc:format>
        <dc:type
           rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
        <dc:title></dc:title>
      </cc:Work>
    </rdf:RDF>
  </metadata>
	<!--pattern id="torusPattern0" x="0" y="0" width="0.25" height="0.25">
		<rect x="0" y="0" height="5" width="5" style="fill:black;fill-opacity:1;stroke:none"/>
		<line x1="-0.5" y1="-0.5" x2="5.5" y2="5.5" style="stroke:white;stroke-width:0.5">
	</pattern>
	<pattern id="torusPattern1" x="0" y="0" width="0.25" height="0.25">
		<rect x="0" y="0" height="5" width="5" style="fill:white;fill-opacity:1;stroke:none"/>
		<line x1="-0.5" y1="-0.5" x2="5.5" y2="5.5" style="stroke:black;stroke-width:0.5">
	</pattern-->
  <g id="layer1">
	';


if ($module_type == MODULE_TYPE_HEXA)
{
	$edge = $pixel_size;
	$edge_2 = 1.0 * $pixel_size / 2;
	$edge_d = M_SQRT3 * $pixel_size;
	$edge_d_2 = M_SQRT3 * $pixel_size / 2;
	$y_d = 1.0 * $pixel_size + 1.0 * $pixel_size / 2;
	
	$y_coor = $edge;
	
	for($y = 0; $y < $height; $y++)
	{
		$y_width = $y * $width;
		
		$x_coor = (($y & 1) == MARKERFIELD_HEXA_LINE_START_LATER ? $edge_d : $edge_d_2 );
		for($x = 0; $x < $width; $x++)
		{
			$color = get_color_for($costs[$y_width + $x]);
			$id = $y_width + $x;

			$svg_str .= "<polygon points=\"" . $x_coor . "," . ($y_coor-$edge) . " " . ($x_coor+$edge_d_2) . "," . ($y_coor-$edge_2)  . " " . ($x_coor+$edge_d_2)  . "," . ($y_coor+$edge_2)  . " " . $x_coor  . "," . ($y_coor+$edge)  . " " . ($x_coor-$edge_d_2)  . "," . ($y_coor+$edge_2)  . " " . ($x_coor-$edge_d_2)  . "," . ($y_coor-$edge_2)  . "\" id=\"$id\" fill=\"$color\" stroke=\"none\" stroke-width=\"0\" shape-rendering=\"crispEdges\" />";
			
			$x_coor += $edge_d;
		}
		$y_coor += $y_d;
	}
}
else
{
	for($y = 0; $y < $height; $y++)
	{
		for($x = 0; $x < $width;$x++)
		{
		  $color = get_color_for($costs[$y*$width + $x]);
		  $rect_id = ($y + $offset)*$width + ($x + $offset);
		  $svg_str .= "<rect width=\"$pixel_size\" height=\"$pixel_size\" x=\"".(($x + $offset)*$pixel_size)."\" y =\"".(($y + $offset)*$pixel_size)."\" id=\"$rect_id\" style=\"fill:$color;fill-opacity:1;stroke:none\" />\n";
		}
	}


	if($type == 1 && $with_offset)
	{
		//top & bottom
		for($y = -$offset; $y < 0; $y++)
		{
			for($x = -$offset; $x < $width + $offset;$x++)
			{
				$data_x = (($x + $width) % $width);
				$data_y = (($height + $y) % $height);
				//top
				$color = get_color_for($costs[$data_y*$width + $data_x]);
				$rect_id = ($y + $offset)*$width + ($x + $offset);
				/*if ((intval($costs[$data_y*$width + $data_x]) & 1) == 0) {
					$svg_str .= "<rect fill=\"url(#torusPattern0)\" width=\"$pixel_size\" height=\"$pixel_size\" x=\"".(($x + $offset)*$pixel_size)."\" y =\"".(($y + $offset)*$pixel_size)."\" id=\"$rect_id\" style=\"fill-opacity:1;stroke:none\" />\n";
				} else {
					$svg_str .= "<rect fill=\"url(#torusPattern1)\" width=\"$pixel_size\" height=\"$pixel_size\" x=\"".(($x + $offset)*$pixel_size)."\" y =\"".(($y + $offset)*$pixel_size)."\" id=\"$rect_id\" style=\"fill-opacity:1;stroke:none\" />\n";
				}*/
				$svg_str .= "<rect width=\"$pixel_size\" height=\"$pixel_size\" x=\"".(($x + $offset)*$pixel_size)."\" y =\"".(($y + $offset)*$pixel_size)."\" id=\"$rect_id\" style=\"fill:$color;fill-opacity:1;stroke:none\" />\n";
				
				
				//bottom
				$color = get_color_for($costs[(($height - $y - 1) % $height)*$width + ($x % $width)]);
				$rect_id = ($height - $y)*$width + ($x + $offset);
				$svg_str .= "<rect width=\"$pixel_size\" height=\"$pixel_size\" x=\"".(($x + $offset)*$pixel_size)."\" y =\"".(($height - $y -1 + $offset)*$pixel_size)."\" id=\"$rect_id\" style=\"fill:$color;fill-opacity:1;stroke:none\" />\n";
				
			}
		}
		
		//right & left
		for($y = 0; $y < $height; $y++)
		{
			for($x = -$offset; $x < 0;$x++)
			{
				//left
				$color = get_color_for($costs[$y*$width + (($x + $width) % $width)]);
				$rect_id = ($y + $offset)*$width + ($x + $offset);
				$svg_str .= "<rect width=\"$pixel_size\" height=\"$pixel_size\" x=\"".(($x + $offset)*$pixel_size)."\" y =\"".(($y + $offset)*$pixel_size)."\" id=\"$rect_id\" style=\"fill:$color;fill-opacity:1;stroke:none\" />\n";
				
				//right
				$color = get_color_for($costs[$y*$width + (($width - $x - 1) % $width)]);
				$rect_id = ($y + $offset)*$width + ($width - $x);
				$svg_str .= "<rect width=\"$pixel_size\" height=\"$pixel_size\" x=\"".(($width - $x - 1 + $offset)*$pixel_size)."\" y =\"".(($y + $offset)*$pixel_size)."\" id=\"$rect_id\" style=\"fill:$color;fill-opacity:1;stroke:none\" />\n";
			}
		}
	}
}
	
	$svg_str .= '
  </g>
</svg>
	';
	return $svg_str;

}

?>
