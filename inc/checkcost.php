<?php

require_once('common.php');

function getGrayScaleColors($range)
{
	$grays = array();
	for ($i = 0; $i < $range; $i++) {
		$grays[] = intval($i * 255 / ($range - 1));
	}
	
	return $grays;
}

function addPenaltyForNeighbors($v1, $v2, &$notSameFlag = null)
{
	if ($v1 == $v2) {
		return MARKERFIELD_PEN_NEIGHBORS_SAME;
	}
	
	if ($notSameFlag === false)
	{
		$notSameFlag = true;
	}
	
	$absolute = abs($v1 - $v2);
	
	if ($absolute < 128) {
		return MARKERFIELD_PEN_NEIGHBORS_DIFF * (2 - $absolute / 128);
	}
	
	return 0.0;
}

function checkCostHexa($type, $width, $height, $k, $data, $colors, $conflicts, $selfconflicts, &$plusConflictCount = null, $return_fields = false)
{
	$penalty = array_fill(0, $width * $height, 0.0);
	
	$plusConflictCount = 0;
	
	$kernelModulesLater = array(0);
	$kernelModulesSooner = array(0);
	
	
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
	
	for ($n = 1; $n < (int)($k+1)/2+1; $n++)
	{
		$lInd1 = getHexaRelativePosition(false, 'NORTHWEST', $width, (int)$n);
		$lInd2 = getHexaRelativePosition(false, 'NORTHEAST', $width, (int)$n);
		$lInd3 = getHexaRelativePosition(false, 'EAST', $width, (int)$n);
		$lInd4 = getHexaRelativePosition(false, 'SOUTHEAST', $width, (int)$n);
		$lInd5 = getHexaRelativePosition(false, 'SOUTHWEST', $width, (int)$n);
		$lInd6 = getHexaRelativePosition(false, 'WEST', $width, (int)$n);
		
		$sInd1 = getHexaRelativePosition(true, 'NORTHWEST', $width, (int)$n);
		$sInd2 = getHexaRelativePosition(true, 'NORTHEAST', $width, (int)$n);
		$sInd3 = getHexaRelativePosition(true, 'EAST', $width, (int)$n);
		$sInd4 = getHexaRelativePosition(true, 'SOUTHEAST', $width, (int)$n);
		$sInd5 = getHexaRelativePosition(true, 'SOUTHWEST', $width, (int)$n);
		$sInd6 = getHexaRelativePosition(true, 'WEST', $width, (int)$n);
		
		for ($i = 0; $i < $n; $i++)
		{
			$kernelModulesLater[] = $lInd1[0];
			$kernelModulesLater[] = $lInd2[0];
			$kernelModulesLater[] = $lInd3[0];
			$kernelModulesLater[] = $lInd4[0];
			$kernelModulesLater[] = $lInd5[0];
			$kernelModulesLater[] = $lInd6[0];
			
			$kernelModulesSooner[] = $sInd1[0];
			$kernelModulesSooner[] = $sInd2[0];
			$kernelModulesSooner[] = $sInd3[0];
			$kernelModulesSooner[] = $sInd4[0];
			$kernelModulesSooner[] = $sInd5[0];
			$kernelModulesSooner[] = $sInd6[0];
			
			$lInd1 = getHexaAbsolutePosition($lInd1, 'EAST', $width);
			$lInd2 = getHexaAbsolutePosition($lInd2, 'SOUTHEAST', $width);
			$lInd3 = getHexaAbsolutePosition($lInd3, 'SOUTHWEST', $width);
			$lInd4 = getHexaAbsolutePosition($lInd4, 'WEST', $width);
			$lInd5 = getHexaAbsolutePosition($lInd5, 'NORTHWEST', $width);
			$lInd6 = getHexaAbsolutePosition($lInd6, 'NORTHEAST', $width);
			
			$sInd1 = getHexaAbsolutePosition($sInd1, 'EAST', $width);
			$sInd2 = getHexaAbsolutePosition($sInd2, 'SOUTHEAST', $width);
			$sInd3 = getHexaAbsolutePosition($sInd3, 'SOUTHWEST', $width);
			$sInd4 = getHexaAbsolutePosition($sInd4, 'WEST', $width);
			$sInd5 = getHexaAbsolutePosition($sInd5, 'NORTHWEST', $width);
			$sInd6 = getHexaAbsolutePosition($sInd6, 'NORTHEAST', $width);
		}
	}
	
	foreach ($conflicts as $i)
	{
		$markerfield_cost_here = (in_array($i, $selfconflicts) ? MARKERFIELD_PEN_CONFLICTS * MARKERFIELD_PEN_SELF_CONFLICTS : MARKERFIELD_PEN_CONFLICTS);
	
		if ((($i / $width) & 1) == MARKERFIELD_HEXA_LINE_START_LATER)
		{
			foreach ($kernelModulesLater as $it)
			{
				$penalty[$i + $it] += $penaltyConflictsHere;
			}
		}
		else
		{
			foreach ($kernelModulesSooner as $it)
			{
				$penalty[$i + $it] += $penaltyConflictsHere;
			}
		}
	}
	
	$type_color = isTypeColor($type);
	$colors_rgb = getColorsRGB($type,$colors);
	if ($type_color) {
		for ($y = 0; $y < $height; $y++) {
			$y_times_width = $y * $width;
			for ($x = 0; $x < $width; $x++) {
				$index = $x + $y_times_width;
				
				if ($y < $height-1) {
					$notSameFlag = false;
					$pen = addPenaltyForNeighbors($colors_rgb[$data[$index]][0], $colors_rgb[$data[$index + $width]][0], $notSameFlag);
					$pen += addPenaltyForNeighbors($colors_rgb[$data[$index]][1], $colors_rgb[$data[$index + $width]][1], $notSameFlag);
					$pen += addPenaltyForNeighbors($colors_rgb[$data[$index]][2], $colors_rgb[$data[$index + $width]][2], $notSameFlag);
					
					$penalty[$index] += $pen + ($notSameFlag ? 0.0 : MARKERFIELD_PEN_CONFLICTS * 800);
					$penalty[$index + $width] += $pen + ($notSameFlag ? 0.0 : MARKERFIELD_PEN_CONFLICTS * 800);
					if (!$notSameFlag) $plusConflictCount++;
					
					if (($y & 1) == MARKERFIELD_HEXA_LINE_START_LATER)
					{
						if ($x < $width-1)
						{
							$notSameFlag = false;
							$pen = addPenaltyForNeighbors($colors_rgb[$data[$index]][0], $colors_rgb[$data[$index + $width +1]][0], $notSameFlag);
							$pen += addPenaltyForNeighbors($colors_rgb[$data[$index]][1], $colors_rgb[$data[$index + $width +1]][1], $notSameFlag);
							$pen += addPenaltyForNeighbors($colors_rgb[$data[$index]][2], $colors_rgb[$data[$index + $width +1]][2], $notSameFlag);
							
							$penalty[$index] += $pen + ($notSameFlag ? 0.0 : MARKERFIELD_PEN_CONFLICTS * 800);
							$penalty[$index + $width +1] += $pen + ($notSameFlag ? 0.0 : MARKERFIELD_PEN_CONFLICTS * 800);
							if (!$notSameFlag) $plusConflictCount++;
						}
					}
					else
					{
						if ($x > 0)
						{
							$notSameFlag = false;
							$pen = addPenaltyForNeighbors($colors_rgb[$data[$index]][0], $colors_rgb[$data[$index + $width -1]][0], $notSameFlag);
							$pen += addPenaltyForNeighbors($colors_rgb[$data[$index]][1], $colors_rgb[$data[$index + $width -1]][1], $notSameFlag);
							$pen += addPenaltyForNeighbors($colors_rgb[$data[$index]][2], $colors_rgb[$data[$index + $width -1]][2], $notSameFlag);
							
							$penalty[$index] += $pen + ($notSameFlag ? 0.0 : MARKERFIELD_PEN_CONFLICTS * 800);
							$penalty[$index + $width -1] += $pen + ($notSameFlag ? 0.0 : MARKERFIELD_PEN_CONFLICTS * 800);
							if (!$notSameFlag) $plusConflictCount++;
						}
					}
				}
				
				if ($x < $width-1) {
					$notSameFlag = false;
					$pen = addPenaltyForNeighbors($colors_rgb[$data[$index]][0], $colors_rgb[$data[$index + 1]][0], $notSameFlag);
					$pen += addPenaltyForNeighbors($colors_rgb[$data[$index]][1], $colors_rgb[$data[$index + 1]][1], $notSameFlag);
					$pen += addPenaltyForNeighbors($colors_rgb[$data[$index]][2], $colors_rgb[$data[$index + 1]][2], $notSameFlag);
					
					$penalty[$index] += $pen + ($notSameFlag ? 0.0 : MARKERFIELD_PEN_CONFLICTS * 800);
					$penalty[$index + 1] += $pen + ($notSameFlag ? 0.0 : MARKERFIELD_PEN_CONFLICTS * 800);
					if (!$notSameFlag) $plusConflictCount++;
				}
			}
		}
	} else {
		$grays = getGrayScaleColors(getTypeRange($type));
		
		for ($y = 0; $y < $height; $y++) {
			$y_times_width = $y * $width;
			for ($x = 0; $x < $width; $x++) {
				$index = $x + $y_times_width;
				
				if ($y < $height-1) {
					$notSameFlag = false;
					$pen = addPenaltyForNeighbors($grays[$data[$index]], $grays[$data[$index + $width]], $notSameFlag);
					
					$penalty[$index] += $pen + ($notSameFlag ? 0.0 : MARKERFIELD_PEN_CONFLICTS * 800);
					$penalty[$index + $width] += $pen + ($notSameFlag ? 0.0 : MARKERFIELD_PEN_CONFLICTS * 800);
					if (!$notSameFlag) $plusConflictCount++;
					
					if (($y & 1) == MARKERFIELD_HEXA_LINE_START_LATER)
					{
						if ($x < $width-1)
						{
							$notSameFlag = false;
							$pen = addPenaltyForNeighbors($grays[$data[$index]], $grays[$data[$index + $width +1]], $notSameFlag);
							
							$penalty[$index] += $pen + ($notSameFlag ? 0.0 : MARKERFIELD_PEN_CONFLICTS * 800);
							$penalty[$index + $width +1] += $pen + ($notSameFlag ? 0.0 : MARKERFIELD_PEN_CONFLICTS * 800);
							if (!$notSameFlag) $plusConflictCount++;
						}
					}
					else
					{
						if ($x > 0)
						{
							$notSameFlag = false;
							$pen = addPenaltyForNeighbors($grays[$data[$index]], $grays[$data[$index + $width -1]], $notSameFlag);
							
							$penalty[$index] += $pen + ($notSameFlag ? 0.0 : MARKERFIELD_PEN_CONFLICTS * 800);
							$penalty[$index + $width -1] += $pen + ($notSameFlag ? 0.0 : MARKERFIELD_PEN_CONFLICTS * 800);
							if (!$notSameFlag) $plusConflictCount++;
						}
					}
				}
				
				if ($x < $width-1) {
					$notSameFlag = false;
					$pen = addPenaltyForNeighbors($grays[$data[$index]], $grays[$data[$index + 1]], $notSameFlag);
					
					$penalty[$index] += $pen + ($notSameFlag ? 0.0 : MARKERFIELD_PEN_CONFLICTS * 800);
					$penalty[$index + 1] += $pen + ($notSameFlag ? 0.0 : MARKERFIELD_PEN_CONFLICTS * 800);
					if (!$notSameFlag) $plusConflictCount++;
				}
			}
		}
	}
	
	if ($return_fields) {
		return $penalty;
	}
	return array_sum($penalty);
}

function checkCost($type, $width, $height, $k, $module_type, $data, $colors, $conflicts, $selfconflicts, &$plusConflictCount = null, $return_fields = false)
{
	$data = array_map("getToIntData", str_split($data));
	
	if ($module_type == MODULE_TYPE_HEXA)
	{
		return checkCostHexa($type, $width, $height, $k, $data, $colors, $conflicts, $selfconflicts, $plusConflictCount, $return_fields);
	}
	
	$penalty = array_fill(0, $width * $height, 0.0);
	
	foreach ($conflicts as $i)
	{
		$markerfield_pen_self_conflicts = MARKERFIELD_PEN_CONFLICTS * MARKERFIELD_PEN_SELF_CONFLICTS;
		if (in_array($i, $selfconflicts)) {
			for ($y = 0; $y < $k; $y++)
			{
				$y_times_width = $y * $width;
				for ($x = 0; $x < $k; $x++)
				{
					$penalty[$i+$y_times_width+$x] += $markerfield_pen_self_conflicts;
				}
			}
		} else {
			for ($y = 0; $y < $k; $y++)
			{
				$y_times_width = $y * $width;
				for ($x = 0; $x < $k; $x++)
				{
					$penalty[$i+$y_times_width+$x] += MARKERFIELD_PEN_CONFLICTS;
				}
			}
		}
	}
	
	$type_color = isTypeColor($type);
	$colors_rgb = getColorsRGB($type,$colors);
	if ($type_color) {
		for ($y = 0; $y < $height; $y++) {
			$y_times_width = $y * $width;
			for ($x = 0; $x < $width; $x++) {
				$index = $x + $y_times_width;
				
				if ($y < $height-1) {
					$pen = addPenaltyForNeighbors($colors_rgb[$data[$index]][0], $colors_rgb[$data[$index + $width]][0]);
					$pen += addPenaltyForNeighbors($colors_rgb[$data[$index]][1], $colors_rgb[$data[$index + $width]][1]);
					$pen += addPenaltyForNeighbors($colors_rgb[$data[$index]][2], $colors_rgb[$data[$index + $width]][2]);
					
					$penalty[$index] += $pen;
					$penalty[$index + $width] += $pen;
				}
				
				if ($x < $width-1) {
					$pen = addPenaltyForNeighbors($colors_rgb[$data[$index]][0], $colors_rgb[$data[$index + 1]][0]);
					$pen += addPenaltyForNeighbors($colors_rgb[$data[$index]][1], $colors_rgb[$data[$index + 1]][1]);
					$pen += addPenaltyForNeighbors($colors_rgb[$data[$index]][2], $colors_rgb[$data[$index + 1]][2]);
					
					$penalty[$index] += $pen;
					$penalty[$index + 1] += $pen;
				}
			}
		}
	} else {
		$grays = getGrayScaleColors(getTypeRange($type));
		
		for ($y = 0; $y < $height; $y++) {
			$y_times_width = $y * $width;
			for ($x = 0; $x < $width; $x++) {
				$index = $x + $y_times_width;
				
				if ($y < $height-1) {
					$pen = addPenaltyForNeighbors($grays[$data[$index]], $grays[$data[$index + $width]]);
					
					$penalty[$index] += $pen;
					$penalty[$index + $width] += $pen;
				}
				
				if ($x < $width-1) {
					$pen = addPenaltyForNeighbors($grays[$data[$index]], $grays[$data[$index + 1]]);
					
					$penalty[$index] += $pen;
					$penalty[$index + 1] += $pen;
				}
			}
		}
	}
	
	if ($return_fields) {
		return $penalty;
	}
	return array_sum($penalty);
}

?>
