<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/prop-history.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";

use Core\PropCheckHistory;
use LPGP_CONF;

$obj = new PropCheckHistory(LPGP_CONF["mysql"]["sysuser"], LPGP_CONF["mysql"]["passwd"]);

if(isset($_POST["get"])){
    die(json_encode($obj->fastQuery(json_decode($_POST["get"], true))));
}
else if($_POST["stage"]){
    $params = json_decode($_POST["stage"], true);
    $obj->addReg((int)$params["usr"], (int)$params["signature"], (int)$params["success"], $params["error"]);
}
else die("INVALID OPTION");
?>
