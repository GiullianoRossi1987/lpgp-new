<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/users-data.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";

use Core\UsersData;
$obj = new UsersData(LPGP_CONF["mysql"]["sysuser"], LPGP_CONF["mysql"]["passwd"]);
if(isset($_POST["get"])){
    die(json_encode($obj->fastQuery(json_decode($_POST["get"], true))));
}
else if(isset($_POST["add"])){
    $params = json_decode($_POST["add"], true);
    $obj->addUser($params["name"], $params["passwd"], $params["email"], $params["encodePasswd"], $params["img"]);
    die("0");
}
else if(isset($_POST["del"])){
    $obj->deleteUser($_POST["del"]);
    die("0");
}
else if(isset($_POST["update"])){
    die(json_encode($obj->fastUpdate(json_decode($_POST["update"], true))));
}
else if(isset($_POST["verify"])){
    $params = json_decode($_POST["verify"], true);
    $results = $obj->fastQuery(array("nm_user" => $params["user"]))[0];
    if((int)$results["checked"] == 0){
        if($obj->authUserKey($params["user"], $params["code"])){
            $obj->setUserChecked($params["user"], true);
            die(json_encode(array("success" => 0)));
        }
        else die(json_encode(array("success" => 1)));
    }
    else die(json_encode(array("success" => 2)));
}
else die("INVALID OPTION");
?>
