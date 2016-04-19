<?php

header("Content-type: text/plain");
echo "122;8;8;3;";


$m_z = time();
$m_w = rand();

function get_random()
{
   global $m_z, $m_w;
    $m_z = 36969 * ($m_z & 65535) + ($m_z >> 16);
    $m_w = 18000 * ($m_w & 65535) + ($m_w >> 16);
    return ($m_z << 16) + $m_w; 
}

for($i = 0; $i < 8; $i++)
{
	for($j = 0; $j < 8; $j++)
	{
		echo get_random()%2;

	}
}
?>