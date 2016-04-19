<?php

include("server_settings.php");

define('ROWS_ON_PAGE', 40);
define('LIMIT_PER_GID', 50);

define('COLORS_SEPARATOR', ';');
define('TYPE_TORUS_BIT', 1);
define('TYPE_COLOR_BIT', 2);
define('TYPE_RANGE_BIT_START', 2);
define('TYPE_RANGE_BITS', 252);
define('TYPE_RANGE_START', 2);

define('MARKERFIELD_COMPARE_GREATER', '>');
define('MARKERFIELD_COMPARE_EQUAL', '=');
define('MARKERFIELD_COMPARE_SMALLER', '<');

define('MARKERFIELD_PEN_NEIGHBORS_SAME', 8.0);
define('MARKERFIELD_PEN_NEIGHBORS_DIFF', 5.0);
define('MARKERFIELD_PEN_CONFLICTS', 100.0);
define('MARKERFIELD_PEN_SELF_CONFLICTS', 6.0);

define('MODULE_TYPE_SQUARE', 0);
define('MODULE_TYPE_HEXA', 1);

define('MARKERFIELD_HEXA_LINE_START_LATER', 0);
define('MARKERFIELD_HEXA_LINE_START_SOONER', 1);

$IMAGE_ALGS = array( '' => 'default' );

?>
