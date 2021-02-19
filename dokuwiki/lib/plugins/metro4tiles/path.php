<?php

//include base config
$inc = realpath(__DIR__.'/../../..');
define('DOKU_INC', $inc.'/');

// load and initialize the core system
require_once DOKU_INC.'inc/init.php';

define('METRO4TILES_DATA_PATH', $conf['metadir'] . '/metro4tiles');