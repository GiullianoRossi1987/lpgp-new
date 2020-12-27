<?php
require_once "devcenter/devcore/server.php";
use Server\ServerSocket;

$sck = new ServerSocket();
$sck->loop();
unset($sck);
?>
