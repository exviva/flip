<?php
function get_www_root() {
	$path_array = explode('/', $_SERVER['PHP_SELF']);
	$path_array = array_reverse($path_array);
	$root_depth = 0;

    while (($folder = each($path_array)) && $folder['value'] != 'flip') {
		++$root_depth;
	}
	
	$root_depth -= 1;
	$root = '';
		
	for($i=0; $i<$root_depth; ++$i) {
		$root .= '../';
	}
     
	return $root;
}

$root = get_www_root();

require_once($root.'cfg/database.php');
require_once($root.'lib/display.php');
require_once($root.'lib/mysql.php');
require_once($root.'lib/auth.php');
require_once($root.'cfg/config.php');
?>
