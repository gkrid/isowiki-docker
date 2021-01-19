<?php
if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/../../../');

require_once(DOKU_INC.'inc/init.php');
session_write_close();  //close session

try {
    /** @var \helper_plugin_telleveryone_db $db_helper */
    $db_helper = plugin_load('helper', 'telleveryone_db');
    $sqlite = $db_helper->getDB();
} catch (Exception $e) {
    http_response_code(500);
    exit;
}

$res = $sqlite->query("SELECT value FROM config WHERE key='token'");
if ($sqlite->res2single($res) != $INPUT->str('token')) {
    http_response_code(403);
    exit;
}

$res = $sqlite->query('SELECT id, timestamp, user, message_html FROM log
                                ORDER BY timestamp DESC LIMIT ?', $INPUT->int('limit', -1));
$arr = $sqlite->res2arr($res);

echo json_encode($arr);
