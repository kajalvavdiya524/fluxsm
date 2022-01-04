<?php
require("Flux.class.php");
// Requests from the same server don't have a HTTP_ORIGIN header
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}
try {
    $API = new Flux($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
    echo $API->processAPI();
} catch (Exception $e) {
	header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(Array('error' => $e->getMessage()));
}
