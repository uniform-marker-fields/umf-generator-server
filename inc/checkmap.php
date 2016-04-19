<?php

require_once('common.php');

function getPos($type, $x, $y, $width, $height)
{
	if ($type == 1) //torus
	{
		return ($y % $height)*$width + ($x % $width);
	}
	
	return $y*$width + $x;
}

function checkMapCompareWindow($w1, $w2, $checkAllDirections = true)
{
	$not_same = true;
	
	$all_directions = count($w1);
	
	for ($direction = 0; $direction < $all_directions && $not_same; $direction++)
	{
		if ($direction == 0 && !$checkAllDirections)
		{
			continue;
		}
		
		$no_difference = true;
		
		for ($rotate = 0; $rotate < $all_directions && $no_difference; $rotate++)
		{
			$rotated = ($direction+$rotate) % $all_directions;
			if ($w1[$rotated] != $w2[$rotate])
			{
				$no_difference = false;
			}
			
		}
		
		if ($no_difference)
		{
			$not_same = false;
		}
	}
	
	return !$not_same;
}

function checkMapColorCompareTwo($v1, $v2)
{
	$ret = "";
	
	for ($i = 0; $i < 3; $i++)
	{
		if ($v1[$i] > $v2[$i])
		{
			$ret .= MARKERFIELD_COMPARE_GREATER;
		} else if ($v1[$i] == $v2[$i])
		{
			$ret .= MARKERFIELD_COMPARE_EQUAL;
		} else {
			$ret .= MARKERFIELD_COMPARE_SMALLER;
		}
	}
	
	return $ret;
}

function checkMapGrayCompareTwo($v1, $v2)
{
	if ($v1 > $v2)
	{
		return MARKERFIELD_COMPARE_GREATER;
	} else if ($v1 == $v2)
	{
		return MARKERFIELD_COMPARE_EQUAL;
	} else {
		return MARKERFIELD_COMPARE_SMALLER;
	}
}

function checkMapHexa($type, $width, $height, $k, $kernel_type, $module_type, $data, $colors = "", $return_fields=false, &$selfcollisions = null)
{
	$type_color = isTypeColor($type);
	$colors_rgb = getColorsRGB($type,$colors);
	
	$unique_windows = array();
	$conflicts = array();
	
		// SPEED UP
		$passesLater = array(
			"topleft" => array(),
			"topright" => array(),
			"right" => array(),
			"bottomright" => array(),
			"bottomleft" => array(),
			"left" => array()
		);
		
		$passesSooner = array(
			"topleft" => array(),
			"topright" => array(),
			"right" => array(),
			"bottomright" => array(),
			"bottomleft" => array(),
			"left" => array()
		);
	
	$lInd1 = array();
	$lInd2 = array();
	$lInd3 = array();
	$lInd4 = array();
	$lInd5 = array();
	$lInd6 = array();
	
	$sInd1 = array();
	$sInd2 = array();
	$sInd3 = array();
	$sInd4 = array();
	$sInd5 = array();
	$sInd6 = array();
	
	$lInd1n = array();
	$lInd2n = array();
	$lInd3n = array();
	$lInd4n = array();
	$lInd5n = array();
	$lInd6n = array();
	
	$sInd1n = array();
	$sInd2n = array();
	$sInd3n = array();
	$sInd4n = array();
	$sInd5n = array();
	$sInd6n = array();
	
	// check all levels
	for ($n = $k; $n > 0; $n--)
	{
		if ($n % 2 == 1)
		{
			$lInd1 = getHexaRelativePosition(false, 'NORTHWEST', $width, (int)($n+1)/2);
			$lInd2 = getHexaRelativePosition(false, 'NORTHEAST', $width, (int)($n+1)/2);
			$lInd3 = getHexaRelativePosition(false, 'EAST', $width, (int)($n+1)/2);
			$lInd4 = getHexaRelativePosition(false, 'SOUTHEAST', $width, (int)($n+1)/2);
			$lInd5 = getHexaRelativePosition(false, 'SOUTHWEST', $width, (int)($n+1)/2);
			$lInd6 = getHexaRelativePosition(false, 'WEST', $width, (int)($n+1)/2);
			
			$sInd1 = getHexaRelativePosition(true, 'NORTHWEST', $width, (int)($n+1)/2);
			$sInd2 = getHexaRelativePosition(true, 'NORTHEAST', $width, (int)($n+1)/2);
			$sInd3 = getHexaRelativePosition(true, 'EAST', $width, (int)($n+1)/2);
			$sInd4 = getHexaRelativePosition(true, 'SOUTHEAST', $width, (int)($n+1)/2);
			$sInd5 = getHexaRelativePosition(true, 'SOUTHWEST', $width, (int)($n+1)/2);
			$sInd6 = getHexaRelativePosition(true, 'WEST', $width, (int)($n+1)/2);
			
			for ($i = 0; $i < $n; $i++)
			{
				if ($i % 2 == 0)
				{
					$lInd1n = getHexaAbsolutePosition($lInd1, 'SOUTHEAST', $width);
					$lInd2n = getHexaAbsolutePosition($lInd2, 'SOUTHWEST', $width);
					$lInd3n = getHexaAbsolutePosition($lInd3, 'WEST', $width);
					$lInd4n = getHexaAbsolutePosition($lInd4, 'NORTHWEST', $width);
					$lInd5n = getHexaAbsolutePosition($lInd5, 'NORTHEAST', $width);
					$lInd6n = getHexaAbsolutePosition($lInd6, 'EAST', $width);
					
					$sInd1n = getHexaAbsolutePosition($sInd1, 'SOUTHEAST', $width);
					$sInd2n = getHexaAbsolutePosition($sInd2, 'SOUTHWEST', $width);
					$sInd3n = getHexaAbsolutePosition($sInd3, 'WEST', $width);
					$sInd4n = getHexaAbsolutePosition($sInd4, 'NORTHWEST', $width);
					$sInd5n = getHexaAbsolutePosition($sInd5, 'NORTHEAST', $width);
					$sInd6n = getHexaAbsolutePosition($sInd6, 'EAST', $width);
				}
				else
				{
					$lInd1n = getHexaAbsolutePosition($lInd1, 'NORTHEAST', $width);
					$lInd2n = getHexaAbsolutePosition($lInd2, 'EAST', $width);
					$lInd3n = getHexaAbsolutePosition($lInd3, 'SOUTHEAST', $width);
					$lInd4n = getHexaAbsolutePosition($lInd4, 'SOUTHWEST', $width);
					$lInd5n = getHexaAbsolutePosition($lInd5, 'WEST', $width);
					$lInd6n = getHexaAbsolutePosition($lInd6, 'NORTHWEST', $width);
					
					$sInd1n = getHexaAbsolutePosition($sInd1, 'NORTHEAST', $width);
					$sInd2n = getHexaAbsolutePosition($sInd2, 'EAST', $width);
					$sInd3n = getHexaAbsolutePosition($sInd3, 'SOUTHEAST', $width);
					$sInd4n = getHexaAbsolutePosition($sInd4, 'SOUTHWEST', $width);
					$sInd5n = getHexaAbsolutePosition($sInd5, 'WEST', $width);
					$sInd6n = getHexaAbsolutePosition($sInd6, 'NORTHWEST', $width);
				}
				
				
				$passesLater['topleft'][] = array(    'l' => $lInd1[0], 'r' => $lInd1n[0]);
				$passesLater['topright'][] = array(   'l' => $lInd2[0], 'r' => $lInd2n[0]);
				$passesLater['right'][] = array(      'l' => $lInd3[0], 'r' => $lInd3n[0]);
				$passesLater['bottomright'][] = array('l' => $lInd4[0], 'r' => $lInd4n[0]);
				$passesLater['bottomleft'][] = array( 'l' => $lInd5[0], 'r' => $lInd5n[0]);
				$passesLater['left'][] = array(       'l' => $lInd6[0], 'r' => $lInd6n[0]);
				
				
				$passesSooner['topleft'][] = array(    'l' => $sInd1[0], 'r' => $sInd1n[0]);
				$passesSooner['topright'][] = array(   'l' => $sInd2[0], 'r' => $sInd2n[0]);
				$passesSooner['right'][] = array(      'l' => $sInd3[0], 'r' => $sInd3n[0]);
				$passesSooner['bottomright'][] = array('l' => $sInd4[0], 'r' => $sInd4n[0]);
				$passesSooner['bottomleft'][] = array( 'l' => $sInd5[0], 'r' => $sInd5n[0]);
				$passesSooner['left'][] = array(       'l' => $sInd6[0], 'r' => $sInd6n[0]);
				
				$lInd1 = $lInd1n;
				$lInd2 = $lInd2n;
				$lInd3 = $lInd3n;
				$lInd4 = $lInd4n;
				$lInd5 = $lInd5n;
				$lInd6 = $lInd6n;
				
				$sInd1 = $sInd1n;
				$sInd2 = $sInd2n;
				$sInd3 = $sInd3n;
				$sInd4 = $sInd4n;
				$sInd5 = $sInd5n;
				$sInd6 = $sInd6n;
			}
		}
		else
		{
			$lInd1 = getHexaRelativePosition(false, 'NORTHWEST', $width, (int)$n/2);
			$lInd2 = getHexaRelativePosition(false, 'NORTHEAST', $width, (int)$n/2);
			$lInd3 = getHexaRelativePosition(false, 'EAST', $width, (int)$n/2);
			$lInd4 = getHexaRelativePosition(false, 'SOUTHEAST', $width, (int)$n/2);
			$lInd5 = getHexaRelativePosition(false, 'SOUTHWEST', $width, (int)$n/2);
			$lInd6 = getHexaRelativePosition(false, 'WEST', $width, (int)$n/2);
			
			$sInd1 = getHexaRelativePosition(true, 'NORTHWEST', $width, (int)$n/2);
			$sInd2 = getHexaRelativePosition(true, 'NORTHEAST', $width, (int)$n/2);
			$sInd3 = getHexaRelativePosition(true, 'EAST', $width, (int)$n/2);
			$sInd4 = getHexaRelativePosition(true, 'SOUTHEAST', $width, (int)$n/2);
			$sInd5 = getHexaRelativePosition(true, 'SOUTHWEST', $width, (int)$n/2);
			$sInd6 = getHexaRelativePosition(true, 'WEST', $width, (int)$n/2);
			
			for ($i = 0; $i < (int)$n/2; $i++)
			{
				$lInd1n = getHexaAbsolutePosition($lInd1, 'EAST', $width);
				$lInd2n = getHexaAbsolutePosition($lInd2, 'SOUTHEAST', $width);
				$lInd3n = getHexaAbsolutePosition($lInd3, 'SOUTHWEST', $width);
				$lInd4n = getHexaAbsolutePosition($lInd4, 'WEST', $width);
				$lInd5n = getHexaAbsolutePosition($lInd5, 'NORTHWEST', $width);
				$lInd6n = getHexaAbsolutePosition($lInd6, 'NORTHEAST', $width);
				
				$sInd1n = getHexaAbsolutePosition($sInd1, 'EAST', $width);
				$sInd2n = getHexaAbsolutePosition($sInd2, 'SOUTHEAST', $width);
				$sInd3n = getHexaAbsolutePosition($sInd3, 'SOUTHWEST', $width);
				$sInd4n = getHexaAbsolutePosition($sInd4, 'WEST', $width);
				$sInd5n = getHexaAbsolutePosition($sInd5, 'NORTHWEST', $width);
				$sInd6n = getHexaAbsolutePosition($sInd6, 'NORTHEAST', $width);
				
				$passesLater['topleft'][] = array(    'l' => $lInd1[0], 'r' => $lInd1n[0]);
				$passesLater['topright'][] = array(   'l' => $lInd2[0], 'r' => $lInd2n[0]);
				$passesLater['right'][] = array(      'l' => $lInd3[0], 'r' => $lInd3n[0]);
				$passesLater['bottomright'][] = array('l' => $lInd4[0], 'r' => $lInd4n[0]);
				$passesLater['bottomleft'][] = array( 'l' => $lInd5[0], 'r' => $lInd5n[0]);
				$passesLater['left'][] = array(       'l' => $lInd6[0], 'r' => $lInd6n[0]);
				
				
				$passesSooner['topleft'][] = array(    'l' => $sInd1[0], 'r' => $sInd1n[0]);
				$passesSooner['topright'][] = array(   'l' => $sInd2[0], 'r' => $sInd2n[0]);
				$passesSooner['right'][] = array(      'l' => $sInd3[0], 'r' => $sInd3n[0]);
				$passesSooner['bottomright'][] = array('l' => $sInd4[0], 'r' => $sInd4n[0]);
				$passesSooner['bottomleft'][] = array( 'l' => $sInd5[0], 'r' => $sInd5n[0]);
				$passesSooner['left'][] = array(       'l' => $sInd6[0], 'r' => $sInd6n[0]);
				
				$lInd1 = $lInd1n;
				$lInd2 = $lInd2n;
				$lInd3 = $lInd3n;
				$lInd4 = $lInd4n;
				$lInd5 = $lInd5n;
				$lInd6 = $lInd6n;
				
				$sInd1 = $sInd1n;
				$sInd2 = $sInd2n;
				$sInd3 = $sInd3n;
				$sInd4 = $sInd4n;
				$sInd5 = $sInd5n;
				$sInd6 = $sInd6n;
			}
		}
	}
		// SPEED UP
		/*$passesLater = array(
			"topleft" => array(     array('l' => 0, 'r' => -$width),   array('l'=> -$width,   'r' => -$width+1)),
			"topright" => array(    array('l' => 0, 'r' => -$width+1), array('l'=> -$width+1, 'r' => 1)),
			"right" => array(       array('l' => 0, 'r' => 1),         array('l'=> 1,         'r' => $width+1)),
			"bottomright" => array( array('l' => 0, 'r' => $width+1),  array('l'=> $width+1,  'r' => $width)),
			"bottomleft" => array(  array('l' => 0, 'r' => $width),    array('l'=> $width,    'r' => -1)),
			"left" => array(        array('l' => 0, 'r' => -1),        array('l'=> -1,        'r' => -$width))
		);
		
		$passesSooner = array(
			"topleft" => array(     array('l' => 0, 'r' => -$width-1), array('l'=> -$width-1, 'r' => -$width)),
			"topright" => array(    array('l' => 0, 'r' => -$width),   array('l'=> -$width,   'r' => 1)),
			"right" => array(       array('l' => 0, 'r' => 1),         array('l'=> 1,         'r' => $width)),
			"bottomright" => array( array('l' => 0, 'r' => $width),    array('l'=> $width,    'r' => $width-1)),
			"bottomleft" => array(  array('l' => 0, 'r' => $width-1),  array('l'=> $width-1,  'r' => -1)),
			"left" => array(        array('l' => 0, 'r' => -1),        array('l'=> -1,        'r' => -$width-1))
		);*/
		

	if ($type_color)
	{
		
		for ($y = 1; $y < $height - 1; $y++)
		{
			$y_times_width = $y * $width;
			for ($x = 1; $x < $width - 1; $x++)
			{
				$index = $x + $y_times_width;
				//echo ">> $index<br/>";
				
				$currvals = array();
				if (($y & 1) == MARKERFIELD_HEXA_LINE_START_LATER)
				{				
					foreach ($passesLater as $key => $shifts)
					{
						//echo ">> >> $key<br/>";
						$currval = "";
						foreach ($shifts as $p) {
							$currval .= checkMapColorCompareTwo($colors_rgb[$data[$index + $p['l']]], $colors_rgb[$data[$index + $p['r']]]);
						}
						
						$currvals[] = $currval;
					}
				}
				else
				{				
					foreach ($passesSooner as $key => $shifts)
					{
						//echo ">> >> $key<br/>";
						$currval = "";
						foreach ($shifts as $p) {
							$currval .= checkMapColorCompareTwo($colors_rgb[$data[$index + $p['l']]], $colors_rgb[$data[$index + $p['r']]]);
						}
						
						$currvals[] = $currval;
					}
				}
				
				$currvals_for_use = array();
				$currvals_for_use[] = $currvals[0] . $currvals[1] . $currvals[2] . $currvals[3] . $currvals[4] . $currvals[5];
				$currvals_for_use[] = $currvals[1] . $currvals[2] . $currvals[3] . $currvals[4] . $currvals[5] . $currvals[0];
				$currvals_for_use[] = $currvals[2] . $currvals[3] . $currvals[4] . $currvals[5] . $currvals[0] . $currvals[1];
				$currvals_for_use[] = $currvals[3] . $currvals[4] . $currvals[5] . $currvals[0] . $currvals[1] . $currvals[2];
				$currvals_for_use[] = $currvals[4] . $currvals[5] . $currvals[0] . $currvals[1] . $currvals[2] . $currvals[3];
				$currvals_for_use[] = $currvals[5] . $currvals[0] . $currvals[1] . $currvals[2] . $currvals[3] . $currvals[4];
				
				foreach ($currvals_for_use as $c) {
					if (isset($unique_windows[$c])) {
						//echo ">> >> >> i:" . $index . ": " . $unique_windows[$currval] . ", " . $currval . "<br/>";
						if ($index == $unique_windows[$c]) {
							if (is_array($selfcollisions)) {
								$selfcollisions[] = $index;
							}
							$conflicts[] = $index;
						} else {
							$conflicts[] = $index;
							$conflicts[] = $unique_windows[$c];
						}
					} else {
						//echo ">> >> >> i:" . $index . ": " . $currval . "<br/>";
						$unique_windows[$c] = $index;
					}
				}
			}
		}
		
	} else {
		
		for ($y = 1; $y < $height - 1; $y++)
		{
			$y_times_width = $y * $width;
			for ($x = 1; $x < $width - 1; $x++)
			{
				$index = $x + $y_times_width;
				//echo ">> $index<br/>";
				
				$currvals = array();
				if (($y & 1) == MARKERFIELD_HEXA_LINE_START_LATER)
				{				
					foreach ($passesLater as $key => $shifts)
					{
						//echo ">> >> $key<br/>";
						$currval = "";
						foreach ($shifts as $p) {
							$currval .= checkMapGrayCompareTwo($data[$index + $p['l']], $data[$index + $p['r']]);
						}
						
						$currvals[] = $currval;
					}
				}
				else
				{				
					foreach ($passesSooner as $key => $shifts)
					{
						//echo ">> >> $key<br/>";
						$currval = "";
						foreach ($shifts as $p) {
							$currval .= checkMapGrayCompareTwo($data[$index + $p['l']], $data[$index + $p['r']]);
						}
						
						$currvals[] = $currval;
					}
				}
				
				$currvals_for_use = array();
				$currvals_for_use[] = $currvals[0] . $currvals[1] . $currvals[2] . $currvals[3] . $currvals[4] . $currvals[5];
				$currvals_for_use[] = $currvals[1] . $currvals[2] . $currvals[3] . $currvals[4] . $currvals[5] . $currvals[0];
				$currvals_for_use[] = $currvals[2] . $currvals[3] . $currvals[4] . $currvals[5] . $currvals[0] . $currvals[1];
				$currvals_for_use[] = $currvals[3] . $currvals[4] . $currvals[5] . $currvals[0] . $currvals[1] . $currvals[2];
				$currvals_for_use[] = $currvals[4] . $currvals[5] . $currvals[0] . $currvals[1] . $currvals[2] . $currvals[3];
				$currvals_for_use[] = $currvals[5] . $currvals[0] . $currvals[1] . $currvals[2] . $currvals[3] . $currvals[4];
				
				foreach ($currvals_for_use as $c) {
					if (isset($unique_windows[$c])) {
						//echo ">> >> >> i:" . $index . ": " . $unique_windows[$c] . ", " . $c . "<br/>";
						if ($index == $unique_windows[$c]) {
							if (is_array($selfcollisions)) {
								$selfcollisions[] = $index;
							}
							$conflicts[] = $index;
						} else {
							$conflicts[] = $index;
							$conflicts[] = $unique_windows[$c];
						}
					} else {
						//echo ">> >> >> i:" . $index . ": " . $c . "<br/>";
						$unique_windows[$c] = $index;
					}
				}
			}
		}
		
		//die();
	}
	
	if ($return_fields)
	{
		return array_unique($conflicts);
	}
	return count(array_unique($conflicts));
}

function checkMap($type, $width, $height, $k, $kernel_type, $module_type, $data, $colors = "", $return_fields=false, &$selfcollisions = null)
{
	$data = array_map("getToIntData", str_split($data));
	
	if ($module_type == MODULE_TYPE_HEXA)
	{
		return checkMapHexa($type, $width, $height, $k, $kernel_type, $module_type, $data, $colors, $return_fields, $selfcollisions);
	}
	
	if (intval($kernel_type) < 1)
	{
		$kernel_type = 0;
		for ($x = 0; $x < $k * ($k-1) / 2; $x++)
		{
			$kernel_type <<= 1;
			$kernel_type += 1;
		}
	}
	
	if ($type > TYPE_TORUS_BIT)
	{
		$type_color = isTypeColor($type);
		$colors_rgb = getColorsRGB($type,$colors);
		
		$unique_windows = array();
		$conflicts = array();
			// SPEED UP
			$passes_tmp = array(
				"top" => array(),
				"right" => array(),
				"bottom" => array(),
				"left" => array()
			);
			
			$passes = array(
				"top" => array(),
				"right" => array(),
				"bottom" => array(),
				"left" => array()
			);
			
			for ($n = 0; $n < $k-1; $n++)
			{
				$n_2 = (int)($n/2);
				$ws_n_2 = $k - 1 - (int)($n/2);
				
				if ($n % 2 == 0)
				{
					for ($i = 0; $i < $k-$n-1; $i++)
					{
						if (($kernel_type & 1) == 1) {
							$passes_tmp["top"][] = array('l' => $n_2 * $width + $n_2 + $i, 'r' => $n_2 * $width + $n_2 + $i+1);
							$passes_tmp["right"][] = array('l' => ($n_2 + $i) * $width + $ws_n_2, 'r' => ($n_2 + $i+1) * $width + $ws_n_2);
							$passes_tmp["bottom"][] = array('l' => $ws_n_2 * $width + $ws_n_2 - $i, 'r' => $ws_n_2 * $width + $ws_n_2 - $i-1);
							$passes_tmp["left"][] = array('l' => ($ws_n_2 - $i) * $width + $n_2, 'r' => ($ws_n_2 - $i-1) * $width + $n_2);
						}
						$kernel_type >>= 1;
					}
				} else {
					
					for ($i = 0; $i < $k-$n-1; $i++)
					{
						if (($kernel_type & 1) == 1) {
							$passes_tmp["top"][] = array('l' => $n_2 * $width + $n_2 + $i+1, 'r' => ($n_2+1) * $width + $n_2 + $i+1);
							$passes_tmp["right"][] = array('l' => ($n_2 + $i+1) * $width + $ws_n_2, 'r' => ($n_2 + $i+1) * $width + $ws_n_2-1);
							$passes_tmp["bottom"][] = array('l' => $ws_n_2 * $width + $ws_n_2 - $i-1, 'r' => ($ws_n_2-1) * $width + $ws_n_2 - $i-1);
							$passes_tmp["left"][] = array('l' => ($ws_n_2 - $i-1) * $width + $n_2, 'r' => ($ws_n_2 - $i-1) * $width + $n_2+1);
						}
						$kernel_type >>= 1;
					}
				}
			}
			
			$passes["top"] = array_merge($passes_tmp["top"], $passes_tmp["right"], $passes_tmp["bottom"], $passes_tmp["left"]);
			$passes["right"] = array_merge($passes_tmp["right"], $passes_tmp["bottom"], $passes_tmp["left"], $passes_tmp["top"]);
			$passes["bottom"] = array_merge($passes_tmp["bottom"], $passes_tmp["left"], $passes_tmp["top"], $passes_tmp["right"]);
			$passes["left"] = array_merge($passes_tmp["left"], $passes_tmp["top"], $passes_tmp["right"], $passes_tmp["bottom"]);
			

		if ($type_color)
		{
			
			for ($y = 0; $y < $height - $k + 1; $y++)
			{
				$y_times_width = $y * $width;
				for ($x = 0; $x < $width - $k + 1; $x++)
				{
					$index = $x + $y_times_width;
					//echo ">> $index<br/>";
					
					$currvals = array();
					foreach ($passes_tmp as $key => $shifts)
					{
						//echo ">> >> $key<br/>";
						$currval = "";
						foreach ($shifts as $p) {
							$currval .= checkMapColorCompareTwo($colors_rgb[$data[$index + $p['l']]], $colors_rgb[$data[$index + $p['r']]]);
						}
						
						$currvals[] = $currval;
					}
					
					$currvals_for_use = array();
					$currvals_for_use[] = $currvals[0] . $currvals[1] . $currvals[2] . $currvals[3];
					$currvals_for_use[] = $currvals[1] . $currvals[2] . $currvals[3] . $currvals[0]; 
					$currvals_for_use[] = $currvals[2] . $currvals[3] . $currvals[0] . $currvals[1];
					$currvals_for_use[] = $currvals[3] . $currvals[0] . $currvals[1] . $currvals[2];
					
					foreach ($currvals_for_use as $c) {
						if (isset($unique_windows[$c])) {
							//echo ">> >> >> i:" . $index . ": " . $unique_windows[$currval] . ", " . $currval . "<br/>";
							if ($index == $unique_windows[$c]) {								
								if (is_array($selfcollisions)) {
									$selfcollisions[] = $index;
								}
								$conflicts[] = $index;
							} else {
								$conflicts[] = $index;
								$conflicts[] = $unique_windows[$c];
							}
						} else {
							//echo ">> >> >> i:" . $index . ": " . $currval . "<br/>";
							$unique_windows[$c] = $index;
						}
					}
				}
			}
			
		} else {
			
			for ($y = 0; $y < $height - $k + 1; $y++)
			{
				$y_times_width = $y * $width;
				for ($x = 0; $x < $width - $k + 1; $x++)
				{
					$index = $x + $y_times_width;
					//echo ">> $index<br/>";
					
					$currvals = array();
					foreach ($passes_tmp as $key => $shifts)
					{
						//echo ">> >> $key<br/>";
						$currval = "";
						foreach ($shifts as $p) {
							$currval .= checkMapGrayCompareTwo($data[$index + $p['l']], $data[$index + $p['r']]);
						}
						
						$currvals[] = $currval;
					}
					
					$currvals_for_use = array();
					$currvals_for_use[] = $currvals[0] . $currvals[1] . $currvals[2] . $currvals[3];
					$currvals_for_use[] = $currvals[1] . $currvals[2] . $currvals[3] . $currvals[0]; 
					$currvals_for_use[] = $currvals[2] . $currvals[3] . $currvals[0] . $currvals[1];
					$currvals_for_use[] = $currvals[3] . $currvals[0] . $currvals[1] . $currvals[2];
					
					foreach ($currvals_for_use as $c) {
						if (isset($unique_windows[$c])) {
							//echo ">> >> >> i:" . $index . ": " . $unique_windows[$c] . ", " . $c . "<br/>";
						if ($index == $unique_windows[$c]) {
							if (is_array($selfcollisions)) {
								$selfcollisions[] = $index;
							}
							$conflicts[] = $index;
						} else {
							$conflicts[] = $index;
							$conflicts[] = $unique_windows[$c];
						}
						} else {
							//echo ">> >> >> i:" . $index . ": " . $c . "<br/>";
							$unique_windows[$c] = $index;
						}
					}
				}
			}
			
			//die();
		}
		
		if ($return_fields)
		{
			return array_unique($conflicts);
		}
		return count(array_unique($conflicts));
	} else { // backwards compatiblity
		$passes = array(
			"normal" => array(),
			"left" => array(),
			"upside" => array(),
			"right" => array()
			);
		
		//first generate shift values and store them into arrays
		for($i = 0; $i < $k; $i++)
		{
			for($j = 0; $j < $k; $j++)
			{
				$c_index = $i*$k + $j;
				$passes["normal"][$i*$k + $j] = $c_index;
				$passes["upside"][($k - $i - 1)*$k + ($k - $j - 1)] = $c_index; 
				$passes["left"][($k - $j - 1)*$k + $i] = $c_index;
				$passes["right"][$j*$k + ($k - $i - 1)] = $c_index;
			}
		}
		$conflicts = 0;
		$unique_numbers = array();
		$unique_positions = array();
		$conflict_positions = array();
		
		
		$index = 0;
		//for over the image - don't bother with edges on the right and bottom sides
		$smaller = 1;
		if($type > 0)
		{
			$smaller = 0;
		}
		
		for($h_i = 0; $h_i < ($height - $smaller*($k - 1)); $h_i++) //full rows
		{
			for($w_i = 0; $w_i < ($width - $smaller*($k - 1)); $w_i++) //full columns
			{
				//calculate the current int for each rotations
				foreach($passes as $key => $shifts)
				{
					$currval = 0;
					for($k_i = 0; $k_i < $k; $k_i++) //rows
					{
						for($k_j = 0; $k_j < $k; $k_j++) //columns
						{
							$currval |= intval($data[getPos($type, $w_i + $k_j, $h_i + $k_i, $width, $height)]) << $shifts[$k_i*$k + $k_j];
						}
					}
					
					if( isset($unique_numbers[$currval]) )
					{
						//found a conflict
						$conflicts++;
						$pos = $unique_numbers[$currval];
						$conflict_positions[] = $unique_positions[$pos][1]*$width + $unique_positions[$pos][0];
						$conflict_positions[] = $h_i*$width + $w_i;
						//echo "Found on pos: ".$unique_positions[$pos][0].", ".$unique_positions[$pos][1];
						//echo " cp: ".$w_i.", ".$h_i." - ".$currval."\n";
					} else {
						$index++;
						//add our number to the unique array
						$unique_numbers[$currval] = $index;
						$unique_positions[$index] = array($w_i, $h_i);
					}
				}
			}
		}
		//print_r(array_unique($conflict_positions));
		//echo "Unique count: ".count($unique_numbers)."\n";
		//echo "field conflicts: ".count(array_unique($conflict_positions))."\n";
		$conflict_fields = count(array_unique($conflict_positions));
		if($return_fields)
		{
			return array_unique($conflict_positions);
		}
		return $conflict_fields;
	}
}

?>
