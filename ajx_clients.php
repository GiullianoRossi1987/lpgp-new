<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/clients-data.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/clients-access-data.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";

use Core\ClientsData;
use Core\ClientsAccessData;

$obj = new ClientsData(LPGP_CONF["mysql"]["sysuser"], LPGP_CONF["mysql"]["passwd"]);
$acc = new ClientsAccessData(LPGP_CONF["mysql"]["sysuser"], LPGP_CONF["mysql"]["passwd"]);

if(isset($_POST["get"])){
    $params = json_decode($_POST["get"], true);
    $results = $obj->fastQuery($params);
    if(isset($_POST["acesses"])){
        foreach($results as $pos => $data){
            $results[$pos]["acesses"] = count($acc->getAccessClient((int)$data["cd_client"]));
        }
    }
    die(json_encode($results));
}
else if(isset($_POST["add"])){
    // TODO: add method to AJAX
}
else if(isset($_POST["update"]) && isset($_POST["client"])){
    $params = json_decode($_POST["update"], true);
    $obj->fastUpdate($params, (int)$_POST["client"]);
    die(json_encode(array("success" => 0)));
}
else if(isset($_POST["download"]) && is_numeric($_POST["download"])){
    $path = $obj->genConfigClient((int)$_POST["download"], false);
    die(json_encode(array("path" => $path)));
}

?>
