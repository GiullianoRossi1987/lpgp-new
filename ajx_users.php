<?php
require_once "core/users-data.php";
require_once "core/Core.php";

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
else die("INVALID OPTION");