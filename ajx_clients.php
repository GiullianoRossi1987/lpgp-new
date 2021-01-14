<?php
require_once "core/clients-data.php";
require_once "core/clients-access-data.php";
require_once "core/Core.php";

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

?>
