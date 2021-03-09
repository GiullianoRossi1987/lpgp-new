<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/proprietaries-data.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";

use Core\ProprietariesData;
$obj = new ProprietariesData(LPGP_CONF["mysql"]["sysuser"], LPGP_CONF["mysql"]["passwd"]);
if(isset($_POST["get"])){
    die(json_encode($obj->fastQuery(json_decode($_POST["get"], true))));
}
else if(isset($_POST["add"])){
    $params = json_decode($_POST["add"], true);
    $obj->addProprietary($params["name"], $params["passwd"], $params["email"], $params["encodePasswd"], $params["img"]);
    die("0");
}
else if(isset($_POST["del"])){
    $obj->delProprietary($_POST["del"]);
    die("0");
}
else if(isset($_POST["update"])){
    die(json_encode($obj->fastUpdate(json_decode($_POST["update"], true))));
}
else if(isset($_POST["check"])){
    $results = $obj->fastQuery(array("cd_proprietary" => $_POST["check"]));
    die(json_encode(array("exists" => (bool)count($results))));
}
else if(isset($_POST["verify"])){
    $params = json_decode($_POST["verify"], true);
    $results = $obj->fastQuery(array("nm_proprietary" => $params["user"]))[0];
    if((int)$results["checked"] == 0){
        if($obj->authPropKey($params["user"], $params["code"])){
            $obj->setProprietaryChecked($params["user"], true);
            die(json_encode(array("success" => 0)));
        }
        else die(json_encode(array("success" => 1)));
    }
    else die(json_encode(array("success" => 2)));
}
else die("INVALID OPTION");
?>
