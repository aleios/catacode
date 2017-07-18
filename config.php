<?php

// Returns an array containing default config options as an object allowing $obj->sqlHost syntax.
return (object) array(
	'sqlHost' => 'localhost',
	'sqlUser' => 'root',
	'sqlPass' => '',
	'sqlDatabase' => 'catadb',
	'dryRun', false
);

?>