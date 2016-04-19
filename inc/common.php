<?php

require_once('defines.php');

function makeType($type_torus, $type_color, $type_range) {
	$bit_torus = ($type_torus ? TYPE_TORUS_BIT : 0);
	$bit_color = ($type_color ? TYPE_COLOR_BIT : 0);
	$type_range = intval($type_range);
	$bit_range = ($type_range < TYPE_RANGE_START ? 0 : $type_range-TYPE_RANGE_START);
	$bit_range <<= TYPE_RANGE_BIT_START;
	
	return ($bit_torus | $bit_color | $bit_range);
}

function isTypeColor($type)
{
	return (boolean)($type & TYPE_COLOR_BIT);
}

function isTypeTorus($type)
{
	return (boolean)($type & TYPE_TORUS_BIT);
}

function getTypeRange($type)
{
	return TYPE_RANGE_START+(($type & TYPE_RANGE_BITS) >> TYPE_RANGE_BIT_START);
}

function checkColors($type,$colors)
{
	if (isTypeColor($type))
	{
		$r = getTypeRange($type);
		$c_a = explode(COLORS_SEPARATOR, $colors);
		$ret = true;
		if (count($c_a) == $r)
		{
			foreach ($c_a as $a)
			{
				if (preg_match('/^#[0-9a-f]{6}$/', $a) == 0)
				{
					$ret = false;
					break;
				}
			}
		} else {
			$ret = false;
		}
		return $ret;
	} else {
		return ($colors == "");
	}
}

function getColors($type,$colors) {
	if (checkColors($type,$colors))
	{
		return explode(COLORS_SEPARATOR, $colors);
	} else {
		return false;
	}
}

function getColorsRGB($type,$colors) {
	$hexcolors = getColors($type,$colors);
	if ($hexcolors === false)
	{
		return false;
	} else {
		$deccolors = array();
		
		$deccolors = array_map("html2rgb", $hexcolors);
		
		return $deccolors;
	}
}

function getColorGD($color) {
	$r = ($color >> 16) & 0xFF;
	$g = ($color >> 8) & 0xFF;
	$b = $color & 0xFF;
	
	return array($r, $g, $b);
}

function getClosestColor($all, $color) {
	$i = 0;
	$min_i = 0;
	$min = 255*255*3;
	$new_min = 0;
	foreach ($all as $c) {
		$new_min = ($c[0]-$color[0])*($c[0]-$color[0]) + ($c[1]-$color[1])*($c[1]-$color[1]) + ($c[2]-$color[2])*($c[2]-$color[2]);
		if ($min > $new_min) {
			$min_i = $i;
			$min = $new_min;
		}
		$i++;
	}
	
	if ($min_i < 10)
	{
		return (string)($min_i);
	} else {
		return chr($min_i+87);
	}
}

function html2rgb($color)
{
	if (strlen($color) == 0)
		return false;
	
    if ($color[0] == '#')
        $color = substr($color, 1);

    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return false;

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    return array($r, $g, $b);
}

function getImagePath($id, $ext, $thumb = false)
{
	return IMAGE_PATH . ($thumb ? IMAGE_PATH_THUMB : IMAGE_PATH_ORIGINAL) . '/' . $id . '.' . $ext;
}

function getImageURL($id, $ext, $thumb = false)
{
	return "http://" . $_SERVER['SERVER_NAME'] . DOC_ROOT . getImagePath($id, $ext, $thumb);
}

function getToIntData($n)
{
	$ord_n = ord($n);
	if ($ord_n >= 48 && $ord_n <= 57)
	{
		return intval($n);
	}
	else
	{
		return $ord_n-87;
	}
}


/********************************************************************/
/********************************************************************/
/*HEXA ORIENTATION
/********************************************************************/
/********************************************************************/

function getHexaAbsolutePosition($index, $orientation, $width, $distance = 1)
{
	$relative = getHexaRelativePosition($index[1], $orientation, $width, $distance);
	return array($index[0]+$relative[0], $relative[1]);
}

function getHexaRelativePosition($lineStartSooner, $orientation, $width, $distance = 1)
{
	if ($distance == 0)
	{
		return array(0, $lineStartSooner);
	}
	else
	{
		$distance--;
		$tmp = array();
		switch ($orientation)
		{
			case 'NORTHWEST':
				$tmp = getHexaRelativePosition(!$lineStartSooner, $orientation, $width, $distance);
				return array($tmp[0]-$width+($lineStartSooner ? -1 : 0 ), $tmp[1]);
			case 'NORTHEAST':
				$tmp = getHexaRelativePosition(!$lineStartSooner, $orientation, $width, $distance);
				return array($tmp[0]-$width+($lineStartSooner ? 0 : 1 ), $tmp[1]);
			case 'EAST':
				$tmp = getHexaRelativePosition($lineStartSooner, $orientation, $width, $distance);
				return array($tmp[0]+1, $tmp[1]);
			case 'SOUTHEAST':
				$tmp = getHexaRelativePosition(!$lineStartSooner, $orientation, $width, $distance);
				return array($tmp[0]+$width+($lineStartSooner ? 0 : 1 ), $tmp[1]);
			case 'SOUTHWEST':
				$tmp = getHexaRelativePosition(!$lineStartSooner, $orientation, $width, $distance);
				return array($tmp[0]+$width+($lineStartSooner ? -1 : 0 ), $tmp[1]);
			case 'WEST':
				$tmp = getHexaRelativePosition($lineStartSooner, $orientation, $width, $distance);
				return array($tmp[0]-1, $tmp[1]);
			default:
				return array(0, $lineStartSooner);
		}
	}
}

/********************************************************************/
/********************************************************************/
/*COMMON FUNCTIONS that doesn't fit anywhere
/********************************************************************/
/********************************************************************/

function common_get_colors($type, $colorstr)
{
	$result = "";
	if(isTypeColor($type))
	{
		$colors = preg_split('/;/',$colorstr);
		for($i = 0; $i < count($colors);$i++)
		{
			$result .= "	<div style=\"width: 10px; height: 10px; background: {$colors[$i]};float:left; font-size: 8px; color:#ffffff;\">$i</div>\n";
		}
	} else {
		$ccount = getTypeRange($type);
		$step = 255/($ccount - 1);
		$currentGray = 0;
		for($i = 0; $i < $ccount; $i++)
		{
			$ic = (int)$currentGray;
			$c = ($ic + 128)%255;
			$result .= "	<div style=\"width: 10px; height: 10px; background: rgb({$ic}, {$ic}, {$ic});float: left; font-size: 8px; color:rgb({$c}, {$c}, {$c});\">$i</div>\n";
			$currentGray += $step;
		}
	}
	return $result;
}

function common_get_type_img($type)
{
	$result = 'img/type_';
	if(isTypeTorus($type))
	{
		$result .= "torus_";
	} else {
		$result .= "normal_";
	}

	if(isTypeColor($type))
	{
		$result .= "color";
	} else {
		$result .= "gray";
	}
	$result .= ".gif";
	return $result;
}


function get_sort_arrows($upid, $downid, $activeid, $sortfunc)
{

	//up
	$result = "<a onclick=\"$sortfunc('$upid')\"><img ";
	if($upid == $activeid)
	{
		$result .= " class=\"arrow_active\" src=\"img/icon_up_active.gif\" ";
	} else {
		$result .= " class=\"arrow\"  src=\"img/icon_up_hover.gif\" ";
	}

	$result .= " /></a>";
	//down
	$result .= "<a onclick=\"$sortfunc('$downid')\"><img ";
	if($downid == $activeid)
	{
		$result .= " class=\"arrow_active\" src=\"img/icon_down_active.gif\" ";
	} else {
		$result .= " class=\"arrow\"  src=\"img/icon_down_hover.gif\" ";
	}

	$result .= " /></a>";

	return $result;
}

function common_get_kernel_type($kernel, $kernel_type, $module_type)
{
	if ($module_type == MODULE_TYPE_HEXA)
	{
		return "<div style=\"margin: auto; padding: 3px 0; width: 24px; background: url('img/hexa.png') \">$kernel</div>\n";
	}
	else
	{
		static $borders = array();
		static $defaults = array();
		$kernel = intval($kernel);
		$kernel_type = intval($kernel_type);
		
		if ($kernel < 1 || $kernel > 6)
		{
			return $kernel;
		}
		
		if ($kernel_type < 1)
		{
			if (!isset($defaults[$kernel]))
			{
				$kernel_type = 0;
				for ($x = 0; $x < $kernel * ($kernel-1) / 2; $x++)
				{
					$kernel_type <<= 1;
					$kernel_type += 1;
				}
				$defaults[$kernel] = $kernel_type;
			}
			else
			{
				$kernel_type = $defaults[$kernel];
			}
		}
		
		if (!isset($borders[$kernel]))
		{
			$borders[$kernel] = array();
		
			$index = ($kernel-1) * $kernel / 2;
				
			for ($n = 0; $n < $kernel-1; $n++)
			{
				$n_2 = (int)($n/2);
				$ws_n_2 = $kernel - 1 - (int)($n/2);
				
				if ($n % 2 == 0)
				{
					for ($i = 0; $i < $kernel-$n-1; $i++)
					{
						$index--;
						$borders[$kernel][($n_2 * $kernel + $n_2 + $i) . "_" . ($n_2 * $kernel + $n_2 + $i+1)] = $index;
						$borders[$kernel][(($n_2 + $i) * $kernel + $ws_n_2) . "_" . (($n_2 + $i+1) * $kernel + $ws_n_2)] = $index;
						$borders[$kernel][($ws_n_2 * $kernel + $ws_n_2 - $i-1) . "_" . ($ws_n_2 * $kernel + $ws_n_2 - $i)] = $index;
						$borders[$kernel][(($ws_n_2 - $i-1) * $kernel + $n_2) . "_" . (($ws_n_2 - $i) * $kernel + $n_2)] = $index;
					}
				} else {
					
					for ($i = 0; $i < $kernel-$n-1; $i++)
					{
						$index--;
						$borders[$kernel][($n_2 * $kernel + $n_2 + $i+1) . "_" . (($n_2+1) * $kernel + $n_2 + $i+1)] = $index;
						$borders[$kernel][(($n_2 + $i+1) * $kernel + $ws_n_2-1) . "_" . (($n_2 + $i+1) * $kernel + $ws_n_2)] = $index;
						$borders[$kernel][(($ws_n_2-1) * $kernel + $ws_n_2 - $i-1) . "_" . ($ws_n_2 * $kernel + $ws_n_2 - $i-1)] = $index;
						$borders[$kernel][(($ws_n_2 - $i-1) * $kernel + $n_2) . "_" . (($ws_n_2 - $i-1) * $kernel + $n_2+1)] = $index;
					}
				}
			}
		}
		
		$kernel_type_array = array();
		for ($i = (int) $kernel * ($kernel-1) / 2 -1; $i >= 0; $i--)
		{
			$kernel_type_array[$i] = (($kernel_type & 1) == 1);
			$kernel_type >>= 1;
		}
		
		$k_size = $kernel * 4 - 1;
		$html = "<div style=\"margin: auto; width: " . $k_size ."px; height: " . $k_size . "px; border: 1px solid #cccccc;\">\n";
		$border_index = 0;
		
		for ($y = 0; $y < $kernel; $y++)
		{
			for ($x = 0; $x < $kernel; $x++)
			{
				$html .= "<div class=\"k_t_small";
				if ($x+1 < $kernel)
				{
					if ($kernel_type_array[$borders[$kernel][($y * $kernel + $x) . "_" . ($y * $kernel + $x+1)]])
					{
						$html .= " k_t_small_right";
					}
					else
					{
						$html .= " k_t_small_right_n";
					}
				}
				
				if ($y+1 < $kernel)
				{
					if ($kernel_type_array[$borders[$kernel][($y * $kernel + $x) . "_" . (($y+1) * $kernel + $x)]])
					{
						$html .= " k_t_small_bottom";
					}
					else
					{
						$html .= " k_t_small_bottom_n";
					}
				}
				$html .= "\" />";
			}
			
			$html .= "\n";
		}
		
		$html .= "</div>\n";
		return $html;
	}
}

?>
