<?php

require_once('defines.php');

function db_connect()
{
	$mysqli = new mysqli(DBHOST, DBUSER, DBPASSWORD, DB);
	/*
	* Use this instead of $connect_error if you need to ensure
	* compatibility with PHP versions prior to 5.2.9 and 5.3.0.
	*/
	if (mysqli_connect_error()) {
		die('Connect Error (' . mysqli_connect_errno() . ') '
				. mysqli_connect_error());
	}
	return $mysqli;
}

function db_queue_push($link, $gid, $type, $width, $height, $kernel, $kernel_type, $module_type, $conflicts, $data, $cost, $colors, $img_id, $img_alg, $t_equal, $c_neighbors, $c_similar, $img_conv, $runtime = 0.0, $force_continue = false)
{
	
    #check for duplicate
	$query = "SELECT COUNT(*) FROM `process_queue` LEFT JOIN `queue_data` ON `process_queue`.`id`=`queue_data`.`id` WHERE `gid`=$gid AND `conflicts`=$conflicts AND STRCMP(\"$data\", `queue_data`.`data`) = 0";
	if($result = $link->query($query))
	{
		echo "checking for fail\n";
		$row = $result->fetch_row();
		echo "found count: ".$row[0];
		if($row[0] > 0)
		{
			return;
		}
	}
	
	if (!$force_continue)
	{
		#check for already finished job
		echo "Check if already finished\n";
		$query = "SELECT COUNT(*) from `marker_gid` WHERE `gid`=$gid";
		if($result = $link->query($query))
		{
			$row = $result->fetch_row();
			if($row[0] == 0)
			{
				echo "Didn't find the specified gid among the active ones: $gid\n";
				return;
			}
		}
	}
	
	$query = "INSERT INTO process_queue VALUES (NULL, $gid, $type, $width, $height, $kernel, $conflicts, NULL, 0, $cost, '$colors', $kernel_type, $img_id, '$img_alg', $module_type)";
	if($link->query($query))
	{
		$query = "INSERT INTO queue_data VALUES (".$link->insert_id.", \"".$data."\", ".$runtime.", ".$t_equal.", \"".$c_neighbors."\", ".$c_similar.", \"".$img_conv."\")";
		if(!$link->query($query)){
			echo "Failed inserting into queue_data";
		}
		
		
		$query = "UPDATE `marker_gid` SET `last_modified`=NOW() WHERE `gid`=$gid";
		if(!$link->query($query)){
			echo "Failed updating marker id";
		}
		
		$query = "SELECT MAX(x) as maxval FROM (SELECT COUNT(*) as x FROM `process_queue` GROUP BY `gid`) AS px";
		if($res = $link->query($query))
		{
			$gidarray = $res->fetch_row();
			$gidcount = intval($gidarray[0]);
			if($gidcount > LIMIT_PER_GID*1.2)
			{
				db_clean_by_group($link);
			}
		} else {
			echo "Failed cleaning up";
		}
	} else {
		echo "Failed inserting into process queue";
	}
}

function db_queue_push_new($link, $name, $type, $width, $height, $kernel, $kernel_type, $module_type, $conflicts, $data, $cost, $colors, $img_id, $img_alg, $t_equal, $c_neighbors, $c_similar, $img_conv, $runtime = 0.0, $force_continue = false)
{
	
	$query = "INSERT INTO `marker_gid` VALUES (NULL, '$name', ".($force_continue?1:0).", NOW(), NULL, '0', ".($force_continue?1:0).")";
	$gid = 0;
	if($link->query($query))
	{
		$gid = $link->insert_id;
	} else {
		return;
	}
	
	$query = "INSERT INTO process_queue VALUES (NULL, $gid, $type, $width, $height, $kernel, $conflicts, NULL, 0, $cost, '$colors', $kernel_type, $img_id, '$img_alg', $module_type)";
	if($link->query($query))
	{
		$query = "INSERT INTO queue_data VALUES ({$link->insert_id}, \"$data\", $runtime, $t_equal, \"$c_neighbors\", $c_similar, \"$img_conv\")";
		$link->query($query);
	}
	
	
	$query = "UPDATE `marker_gid` SET `last_modified`=NOW() WHERE `gid`=$gid";
	$link->query($query);
	
	return $gid;
}

function db_clean_by_group($link, $limit=LIMIT_PER_GID)
{
	/*
	$query = "SELECT `id`,`gid`,MAX(`conflicts`) FROM `process_queue` 
	WHERE `conflicts`<=(SELECT `conflicts` FROM `process_queue` AS `pq` 
		WHERE `pq`.`gid`=`process_queue`.`gid` ORDER BY `pq`.`conflicts` ASC LIMIT 1 OFFSET $limit)
	GROUP BY `gid` ";
	*/
	$query = "SELECT `gid`,COUNT(*) AS c FROM `process_queue` GROUP BY `gid`";
	if($db_res_gid = $link->query($query))
	{
		while($row_gid = $db_res_gid->fetch_assoc())
		{
			if($row_gid['c'] < LIMIT_PER_GID*1.2)
			{
				continue;
			}
			$query = "SELECT `id`,`conflicts`,`cost` FROM `process_queue` WHERE `gid`={$row_gid['gid']}
				  ORDER BY `conflicts` ASC, `cost` ASC, `id` DESC LIMIT 1 OFFSET $limit";
			if($db_res = $link->query($query))
			{
				while($row = $db_res->fetch_row())
				{
					$query = "DELETE FROM `process_queue`,`queue_data` 
					USING `process_queue` LEFT JOIN `queue_data` ON `process_queue`.`id`=`queue_data`.`id` 
					WHERE `process_queue`.`gid`={$row_gid['gid']} 
						AND (`conflicts`>{$row[1]} OR 
						(`conflicts`={$row[1]} AND `cost`>{$row[2]}) OR
						(`conflicts`={$row[1]} AND `cost`={$row[2]} AND `process_queue`.`id`<{$row[0]}))";
					$link->query($query);
				}
			}
		}
	}
}

function db_clean_too_large($link)
{
	$query = "SELECT * from `process_queue` ORDER BY `process_queue`.`conflicts` ASC,`process_queue`.`id` DESC LIMIT 1 OFFSET ".DB_LIMIT;
	if($db_res = $link->query($query))
	{
		$res_assoc = $db_res->fetch_assoc();
		if($res_assoc)
		{
			$conflict_limit = $res_assoc['conflicts'];
			$query = "DELETE FROM `process_queue`,`queue_data` USING `process_queue` LEFT JOIN `queue_data` ON `process_queue`.`id`=`queue_data`.`id` WHERE `process_queue`.`conflicts`>{$conflict_limit} OR (`process_queue`.`conflicts`={$conflict_limit} AND `process_queue`.`id`<{$res_assoc['id']})";
			$link->query($query);
		}
	}
}

function db_get_queue($link, $page=0, $rows_on_page=ROWS_ON_PAGE)
{
	$offset = $page*$rows_on_page;
	$limit = $rows_on_page;
	/*
	$query = "SELECT `id`,`process_queue`.`gid`,`width`,`height`,`kernel`,`conflicts`,`process_queue`.`last_assigned`,`short_name`,`state`,`boost`
	FROM `process_queue` LEFT JOIN `marker_gid` ON `process_queue`.`gid` = `marker_gid`.`gid`
	WHERE `state`!=0
	ORDER BY `process_queue`.`conflicts` ASC LIMIT ".$limit." OFFSET ".$offset;
	*/
	$link->query("SET @group:=0");
	$link->query("SET @num:=0");
	$query = "SELECT `row_number`,`id`,`spq`.`gid`,`conflicts`,`type`,`width`,`height`,`kernel`,`kernel_type`,`module_type`,`spq`.`last_assigned`,`short_name`,`state`,`boost`,`cost`,`colors`,`img_id`,`img_ext`
	FROM (SELECT `id`,`gid`,`conflicts`,`cost`,`type`,`colors`,`width`,`height`,`kernel`,`kernel_type`,`module_type`,`last_assigned`,`boost`,@num:= if(@group=`gid`, @num + 1, 1) as `row_number`,@group:=`gid` AS `dummy`,
			`process_queue`.`img_id` as `img_id`, `images`.`img_extension` as `img_ext`
		FROM `process_queue`
		LEFT JOIN `images` ON `process_queue`.`img_id` = `images`.`img_id`
		ORDER BY `gid` ASC,`conflicts` ASC,`cost` ASC,`id` DESC) as `spq` 
	LEFT JOIN `marker_gid` ON `spq`.`gid`=`marker_gid`.`gid`
	WHERE `state` != 0
	ORDER BY `row_number` ASC,`conflicts` ASC,`cost` ASC LIMIT ".$limit." OFFSET ".$offset;
	
	//$db_res = $link->query($query);
	//$pres = $db_res->fetch_all(MYSQLI_ASSOC);
	//print_r($pres);
	//$db_res->free_result();
	
	
	$result = array();
	$db_res = $link->query($query);
	while($row = $db_res->fetch_assoc())
	{
		//print_r($row);
		//echo "<br />";
		$result[] = $row;
	}
	return $result;
}

function db_get_queue_count($link)
{
	$query = "SELECT COUNT(*) FROM `process_queue` LEFT JOIN `marker_gid` ON `process_queue`.`gid` = `marker_gid`.`gid` WHERE `state`!=0";
	if($db_res = $link->query($query))
	{
		$res_assoc = $db_res->fetch_row();
		return $res_assoc[0];
	}
	return 1;
}

function db_get_ready_count($link, $where_sql = "")
{
	$query = "SELECT COUNT(*) FROM `markers_ready` " . (empty($where_sql)?"":" WHERE ".$where_sql);
	if($db_res = $link->query($query))
	{
		$res_assoc = $db_res->fetch_row();
		return $res_assoc[0];
	}
	return 1;
}

function db_get_ready($link, $page=0, $rows_on_page=ROWS_ON_PAGE, $order_sql = "", $where_sql = "")
{
	$offset = $page*$rows_on_page;
	$limit = $rows_on_page;
	$query = "SELECT `id`,`gid`,`type`,`width`,`height`,`kernel`,`kernel_type`,`module_type`,`data`,`short_name`,`colors`,`markers_ready`.`img_id`,`images`.`img_extension` as `img_ext`
		FROM `markers_ready`
		LEFT JOIN `images` ON `markers_ready`.`img_id` = `images`.`img_id`
		".(empty($where_sql)?"":" WHERE ".$where_sql) . "
		ORDER BY ".(empty($order_sql)?"`width`*`height` DESC":$order_sql)."
		LIMIT $limit OFFSET $offset";

	$result = array();
	$db_res = $link->query($query);
	while($row = $db_res->fetch_assoc())
	{
		$result[] = $row;
	}
	return $result;
}

function db_get_queue_markers_count($link, $where_sql = "")
{
	$query = "SELECT COUNT(*) FROM (SELECT COUNT(*)
		FROM `process_queue` ".(empty($where_sql)?"":" WHERE ".$where_sql) . " 
		GROUP BY `process_queue`.`gid`) as `x`";
	if($db_res = $link->query($query))
	{
		$res_assoc = $db_res->fetch_row();
		return $res_assoc[0];
	}
	return 1;
}

function db_get_queue_markers($link, $page=0, $rows_on_page=ROWS_ON_PAGE,
		$order_str = " ORDER BY `c` ASC, `cost` ASC ", $where_sql = "")
{

	$offset = $page*$rows_on_page;
	$limit = $rows_on_page;
	$result = array();
	$query = "SELECT `process_queue`.`gid` as `gid`,`short_name`,`width`,`height`,`kernel`,`kernel_type`,`module_type`,`colors`,
			`marker_gid`.last_assigned,
			`type`,MIN(`conflicts`) as `c`, CAST(MIN(`cost`) as DECIMAL(12,1)) as `cost`
		FROM `process_queue` LEFT JOIN `marker_gid` ON `marker_gid`.`gid`=`process_queue`.`gid`
		".(empty($where_sql)? "": " WHERE ".$where_sql)."
		GROUP BY `gid` 
		".(empty($order_str)? "": "ORDER BY".$order_str)."
		LIMIT $limit OFFSET $offset";
	
	if($db_res_gid = $link->query($query))
	{
		while($row_gid = $db_res_gid->fetch_assoc())
		{
			$order_sql = " `id` DESC ";
			
			if($row_gid['c'] == 0)
			{
				$order_sql = " `cost` ASC ";
			} else {
				$order_sql = " `conflicts` ASC, `cost` ASC ";
			}
			$query = "SELECT `process_queue`.`id`,`process_queue`.`gid`,
					`short_name`,`type`,`width`,`height`,`kernel`,`kernel_type`,`module_type`,
					`conflicts`,`state`,`marker_gid`.`last_assigned`,
					`marker_gid`.`last_modified`,`testing`,
					CAST(`cost` as DECIMAL(12,1)) as `cost`,`colors`,`force_continue`,`process_queue`.`img_id`, `images`.`img_extension` as `img_ext`
				FROM `process_queue`
				LEFT JOIN `marker_gid` ON `marker_gid`.`gid`=`process_queue`.`gid`
				LEFT JOIN `images` ON `process_queue`.`img_id` = `images`.`img_id`
				WHERE `process_queue`.`gid`={$row_gid['gid']} ".($row_gid['c']>0?" AND `conflicts`={$row_gid['c']}":"")." ORDER BY $order_sql LIMIT 2";
			$db_res = $link->query($query);

			if($row = $db_res->fetch_assoc())
			{
				if($row2 = $db_res->fetch_assoc()) //we got a second one, compute difference
				{
					$row['cost_diff'] = ((float) $row['cost']) - ((float) $row2['cost']);
					$row['conflicts_diff'] = ((float) $row['conflicts']) - ((float) $row2['conflicts']);
				} else {
					$row['cost_diff'] = 0.0;
					$row['conflicts_diff'] = 0.0;
				}
				$result[] = $row;
			}
		}
	}
	/*
	$query = "SELECT `process_queue`.`id`,`process_queue`.`gid`,`short_name`,`width`,`height`,`kernel`,`conflicts`,`state`,`marker_gid`.`last_assigned`,`marker_gid`.`last_modified` 
	FROM `process_queue` LEFT JOIN `marker_gid` ON `marker_gid`.`gid`=`process_queue`.`gid`
	WHERE `conflicts`=(SELECT MIN(`conflicts`) FROM `process_queue` as `pq` WHERE `pq`.`gid`=`process_queue`.`gid`) GROUP BY `process_queue`.`gid` ORDER BY `conflicts`
	LIMIT $limit OFFSET $offset";
	$result = array();
	$db_res = $link->query($query);
	while($row = $db_res->fetch_assoc())
	{
		$result[] = $row;
	}
	*/
	return $result;
}

function db_get_marker_info($link, $gid)
{
	$query = "SELECT `gid`,`short_name`, `state`, `last_modified`, `last_assigned`, `testing`, `force_continue`
		FROM `marker_gid` WHERE `gid`=$gid";
	$result = array();
	if($db_res = $link->query($query))
	{
		$result = $db_res->fetch_assoc();
		$result["success"] = True;
	} else {
		$result["success"] = False;
	}
	return $result;
}

function db_update_marker_name($link, $gid, $new_name)
{
	$query = "UPDATE `marker_gid` SET `short_name`='$new_name' WHERE `gid`=$gid";
	return $link->query($query);
}

function db_update_ready_name($link, $id, $new_name)
{
	$query = "UPDATE `markers_ready` SET `short_name`='$new_name' WHERE `id`=$id";
	return $link->query($query);
}

function db_get_ready_info($link, $id)
{
	$query = "SELECT `id`,`gid`,`short_name`,`type`,`width`,`height`,`kernel`,`data`,`colors`,`runtime`,`kernel_type`,`module_type`,`img_alg`,`markers_ready`.`img_id`,`images`.`img_extension` as `img_ext`
						,`threshold_equal`,`cost_neighbors`,`cost_similarity`,`img_conv`
		FROM `markers_ready`
		LEFT JOIN `images` ON `markers_ready`.`img_id` = `images`.`img_id`
		WHERE `id`=$id";
	$result = array();
	if($db_res = $link->query($query))
	{
		$result = $db_res->fetch_assoc();
		$result["success"] = True;
	} else {
		$result["success"] = False;
	}
	return $result;
}

function db_get_smallest_conflict_gid($link, $gid)
{
	$query = "SELECT `process_queue`.`id`,`process_queue`.`cost` FROM `process_queue` 
	WHERE `process_queue`.`gid`=$gid AND `conflicts`=(SELECT MIN(`conflicts`) FROM `process_queue` as `pq` WHERE `pq`.`gid`=`process_queue`.`gid`) ORDER BY `cost` LIMIT 1";
	
	if($db_res = $link->query($query))
	{
		$result = $db_res->fetch_assoc();
		return $result['id'];
	}
	return 0;
}

function db_remove_gid($link, $gid)
{
	$query = "DELETE FROM `process_queue`,`queue_data`,`marker_gid` 
	USING `process_queue` LEFT JOIN `queue_data` ON `process_queue`.`id`=`queue_data`.`id` 
	LEFT JOIN `marker_gid` ON `process_queue`.`gid`=`marker_gid`.`gid`
	WHERE `process_queue`.`gid`=$gid";
	if($link->query($query))
	{
		return $link->affected_rows;
	}
	return 0;
}

function db_clean_gid_non_zero_conflict($link, $gid)
{
	$query = "DELETE FROM `process_queue`,`queue_data`
	USING `process_queue` LEFT JOIN `queue_data` ON `process_queue`.`id`=`queue_data`.`id`
	WHERE `process_queue`.`gid`=$gid AND `process_queue`.`conflicts` > 0";
	if($link->query($query))
	{
		return $link->affected_rows;
	}
	return 0;
}

function db_ready_push($link, $gid, $type, $width, $height, $kernel, $kernel_type, $module_type, $data, $colors, $img_id, $img_alg, $t_equal, $c_neighbors, $c_similar, $img_conv, $runtime = 0.0)
{	
    #check for duplicate
    
	$query = "SELECT COUNT(*) from `markers_ready`
	WHERE `width`=$width AND `height`=$height AND `kernel`=$kernel AND `type`=$type 
	AND STRCMP(\"$data\", `markers_ready`.`data`)=0";
	
	if($result = $link->query($query))
	{
		$row = $result->fetch_row();
		if($row[0] > 0)
		{
			return;
		}
	}
	
	$type_map = array( 0 => "N", 1 => "T");

	
	//get name from markers_ready or marker_gid
	$name = "";
	
	$query = "SELECT `short_name` FROM `marker_gid` WHERE `gid`=$gid";
		
	if($result = $link->query($query))
	{
		if($result->num_rows > 0)
		{
			$row = $result->fetch_row();
			$name = $row[0];
		}
	}
	
	//if for some reason the marker_gid didn't contain this one -> try find it in markers ready
	if(empty($name))
	{
		$query = "SELECT `short_name` FROM `markers_ready` WHERE `gid`=$gid";
		if($result = $link->query($query))
		{
			if($result->num_rows > 0)
			{
				$row = $result->fetch_row();
				$name = $row[0];
			}
		}
	}
	

	if(empty($name))
	{
		$name = "{$width}x{$height}_{$kernel}_{$type_map[$type]}";
	}
	
	$query = "INSERT INTO markers_ready VALUES (NULL, $gid, \"{$name}\", $type, $width, $height, $kernel, \"$data\", \"$colors\", $runtime, $kernel_type, $img_id, \"$img_alg\", $module_type, $t_equal, \"$c_neighbors\", $c_similar, \"$img_conv\")";
	return $link->query($query);
	
	//this isn't necessary, since it gets removed anyways
	//$query = "UPDATE `marker_gid` SET `last_modified`=NOW() WHERE `gid`=$gid";
	//$link->query($query);
	
	//$query = "UPDATE `marker_gid` SET `state`=2 WHERE `gid`=$gid";
	//$link->query($query);
}

function db_get_weighted($link, $version = 1, $testing = true)
{
	/*
    $pquery = "select `gid`,`conflicts`,`id`
	from `process_queue`
	where (
	select count(*) from `process_queue` as f
	where `f`.`gid` =`process_queue`.`gid` and f.`conflicts` < `process_queue`.`conflicts`
	) <= 2 ORDER BY `gid`";
	
	
	$query = "SELECT `id`,`conflicts`,`boost` FROM `process_queue`
	LEFT JOIN `marker_gid` ON `process_queue`.`gid` = `marker_gid`.`gid` WHERE `state`!=0
	ORDER BY `process_queue`.`conflicts` ASC LIMIT ".DB_LIMIT;
	*/
	
	$type_sql = "1";
	if ($version == 1) {
		$type_sql = "`type` <= 1"; //only black and white
	} elseif ($version == 2) {
		$type_sql = "(`type` & " . TYPE_TORUS_BIT . ") = 0"; //without torus
	}
	
	$where_sql = "`state`!=0";
	if ($testing) {
		$where_sql = "`testing`!=0";
	}
	
	$link->query("SET @group:=0");
	$link->query("SET @num:=0");
	$query = "SELECT `id`,`conflicts`,`boost`,`row_number`
	FROM (SELECT `id`,`gid`,`conflicts`,`boost`,@num:= if(@group=`gid`, @num + 1, 1) as `row_number`,@group:=`gid` AS `dummy` 
		FROM `process_queue`
		WHERE $type_sql
		ORDER BY `gid` ASC,`conflicts` ASC,`id` DESC) as `spq`
	LEFT JOIN `marker_gid` ON `spq`.`gid` = `marker_gid`.`gid`
	WHERE $where_sql
	ORDER BY `row_number` ASC,`conflicts` ASC LIMIT ".DB_LIMIT;
	
	$result["success"] = False;
	$db_res = $link->query($query);
	
	$ids = array();
	$probabilities = array();
	$probsum = 0.0;
	
	$row_index = 0;
	//sum probabilites
	while($row = $db_res->fetch_assoc())
	{
		//$conf = $row['conflicts']/100.0;
		//$currprob = 1.0/(2.0 + $conf*$conf);
		$row_index++;
		$currprob = 1.0/(20 + $row_index*$row_index);
		if(intval($row['boost']))
		{
			$currprob = 1.0/(1 + $row_index*$row_index);
		}
		$probsum += $currprob;
		$probabilities[] = $probsum;
		$ids[] = $row['id'];
	}
	
	$db_res->free();
	
	$result["success"] = True;
	$result["id"] = -1;
	
	$rprob = rand()*$probsum/getrandmax();
	for($i = 0; $i < count($probabilities); $i++)
	{
		if($rprob < $probabilities[$i])
		{
			$result["id"] = $ids[$i];
			break;
		}
	}
	
	if($result["id"] == -1)
	{
		$result["success"] = False;
		return $result;
	}
	
	$query = "SELECT `pq`.`type`, `pq`.`width`, `pq`.`height`, `pq`.`kernel`, `pq`.`gid`, `qd`.`data`, `pq`.`conflicts`
				, `pq`.`colors`, `pq`.`img_id`, `pq`.`img_alg`, `img`.`img_extension`, `qd`.`runtime`, `pq`.`kernel_type`, `pq`.`module_type`
				, `qd`.`threshold_equal`, `qd`.`cost_neighbors`, `qd`.`cost_similarity`, `qd`.`img_conv`
				FROM `process_queue` as `pq`
				LEFT JOIN `queue_data` as `qd` ON `pq`.`id`=`qd`.`id`
				LEFT JOIN `images` as `img` ON `pq`.`img_id`=`img`.`img_id`
				WHERE `pq`.`id`={$result['id']}";
	$db_res = $link->query($query);
	
	$row = $db_res->fetch_assoc();
	
	$db_res->free();
	
	if($row)
	{
		$result["type"] = $row["type"];
		$result["width"] = $row["width"];
		$result["height"] = $row["height"];
		$result["kernel"] = $row["kernel"];
		$result["gid"] = $row["gid"];
		$result["data"] = $row["data"];
		$result["conflicts"] = $row["conflicts"];
		$result["colors"] = $row["colors"];
		$result["img_id"] = $row["img_id"];
		$result["img_alg"] = $row["img_alg"];
		$result["img_extension"] = $row["img_extension"];
		$result["runtime"] = $row["runtime"];
		$result["kernel_type"] = $row["kernel_type"];
		$result["module_type"] = $row["module_type"];
		$result["threshold_equal"] = $row["threshold_equal"];
		$result["cost_neighbors"] = $row["cost_neighbors"];
		$result["cost_similarity"] = $row["cost_similarity"];
		$result["img_conv"] = $row["img_conv"];
		
		$query = "UPDATE `marker_gid` SET `last_assigned`=NOW() WHERE `gid`={$result['gid']}";
		$link->query($query);
		$query = "UPDATE `process_queue` SET `last_assigned`=NOW() WHERE `id`={$result['id']}";
		$link->query($query);
	} else {
		$result["success"] = False;
	}
	
	
	return $result;
}

function db_get_instance_info($link, $id)
{

	$result['id'] = $id;

	$query = "SELECT `process_queue`.`id` as `id`,`type`,
			`process_queue`.`gid` as gid,`short_name`, `boost`,
			`width`,`height`,`kernel`,`kernel_type`,`module_type`,`data`,`conflicts`,`cost`,`colors`,`runtime`,
			`process_queue`.`last_assigned`,`marker_gid`.`last_modified`,
			`process_queue`.`img_id`, `process_queue`.`img_alg`, `images`.`img_extension` as `img_ext`,
			`queue_data`.`threshold_equal`, `queue_data`.`cost_neighbors`, `queue_data`.`cost_similarity`, `queue_data`.`img_conv`
		FROM `process_queue` 
		LEFT JOIN `queue_data` ON `process_queue`.`id`=`queue_data`.`id`
		LEFT JOIN `marker_gid` ON `marker_gid`.`gid`=`process_queue`.`gid`
		LEFT JOIN `images` ON `process_queue`.`img_id` = `images`.`img_id`
		WHERE `process_queue`.`id`={$result['id']}";
	$db_res = $link->query($query);
	
	$row = $db_res->fetch_assoc();
	
	$db_res->free();
	
	if($row)
	{
		$result = $row;
		$result["success"] = True;
	} else {
		$result["success"] = False;
	}
	
	return $result;
}

function db_toggle_gid($link, $gid)
{
	$query = "UPDATE `marker_gid` SET `state`=1-`state` WHERE `gid`=$gid";
	return $link->query($query);
}

function db_toggle_gid_testing($link, $gid)
{
	$query = "UPDATE `marker_gid` SET `testing`=1-`testing` WHERE `gid`=$gid";
	return $link->query($query);
}

function db_toggle_gid_force_continue($link, $gid)
{
	$query = "UPDATE `marker_gid` SET `force_continue`=1-`force_continue` WHERE `gid`=$gid";
	return $link->query($query);
}

function db_get_gid_force_continue($link, $gid)
{
	$query = "SELECT `force_continue` FROM `marker_gid` WHERE `gid`=$gid";
	if($db_res_force = $link->query($query))
	{
		if ($row = $db_res_force->fetch_assoc())
		{
			return (bool) $row["force_continue"];
		}
	}
	return false;
}

function db_toggle_boost($link, $id)
{
	$query = "UPDATE `process_queue` SET `boost`=1-`boost` WHERE `id`=$id";
	return $link->query($query);
}


function db_enable_next($link, $count = 1)
{
	$query = "SELECT `process_queue`.`gid`,MIN(`conflicts`) as `c`
	FROM `process_queue` LEFT JOIN `marker_gid` ON `process_queue`.`gid`=`marker_gid`.`gid`
	WHERE `state`=0
	GROUP BY `process_queue`.`gid` ORDER BY `c`
	LIMIT $count";
	if($db_res_gid = $link->query($query))
	{
		
		while($row = $db_res_gid->fetch_row())
		{
			$gid = $row[0];
			db_toggle_gid($link, $gid);
		}
	}
}

function db_ready_marker_remove($link, $id)
{
	$query="DELETE FROM `markers_ready` WHERE `id`=$id";
	return $link->query($query);
}

function db_image_add($link, $extension, $name)
{
	$query="INSERT INTO `images` (`img_id`, `img_extension`, `img_name`) VALUES (NULL, '$extension', '$name')";
	if($link->query($query))
	{
		return $link->insert_id;
	} else
	{
		return false;
	}
}

function db_image_delete($link, $id)
{
	$query="DELETE FROM `pmgen_new`.`images` WHERE `images`.`img_id` = " . $id;
	return $link->query($query);
}

function db_close($link)
{
	$link->close();
}

?>
