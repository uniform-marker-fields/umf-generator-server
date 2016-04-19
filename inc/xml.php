<?php

require_once('common.php');

function get_xml($data)
{
	$ret_xml = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
	$ret_xml .= "<markers>\n";

		$ret_xml .= "<marker>\n";
		$ret_xml .= "<gid>{$data['gid']}</gid>\n";
		$ret_xml .= "<width>{$data['width']}</width>\n";
		$ret_xml .= "<height>{$data['height']}</height>\n";
		$ret_xml .= "<kernel>{$data['kernel']}</kernel>\n";
		$ret_xml .= "<data>{$data['data']}</data>\n";
		$ret_xml .= "<module_type>".$data['module_type']."</module_type>\n";
		$ret_xml .= "<type>".$data['type']."</type>\n";
		$ret_xml .= "<torus>".((int) isTypeTorus($data['type']))."</torus>\n";
		$ret_xml .= "<colorspace>".((int) isTypeColor($data['type']))."</colorspace>\n";
		$ret_xml .= "<range>".getTypeRange($data['type'])."</range>\n";
		$ret_xml .= "<colors>".$data['colors']."</colors>\n";
		$ret_xml .= "<img_id>".$data['img_id']."</img_id>\n";
		$ret_xml .= "<img_alg>".$data['img_alg']."</img_alg>\n";
		$ret_xml .= "<img_server>".$_SERVER['SERVER_NAME']."</img_server>\n";
		$ret_xml .= "<img_path>".($data['img_id'] > 0 ? DOC_ROOT . getImagePath($data['img_id'],$data['img_extension']) : "")."</img_path>\n";
		$ret_xml .= "<runtime>".$data['runtime']."</runtime>\n";
		$ret_xml .= "<kernel_type>".$data['kernel_type']."</kernel_type>\n";
		$ret_xml .= "<threshold_equal>".$data['threshold_equal']."</threshold_equal>\n";
		$ret_xml .= "<cost_neighbors>".$data['cost_neighbors']."</cost_neighbors>\n";
		$ret_xml .= "<cost_similarity>".$data['cost_similarity']."</cost_similarity>\n";
		$ret_xml .= "<img_conv>".$data['img_conv']."</img_conv>\n";
		$ret_xml .= "</marker>\n";
		
	$ret_xml .= "</markers>\n";
	
	return $ret_xml;
}

?>
