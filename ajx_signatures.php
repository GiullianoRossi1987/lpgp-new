<?php
require_once "core/Core.php";
require_once "core/signatures-data.php";

use Core\SignaturesData;
$obj = new SignaturesData(LPGP_CONF["mysql"]["sysuser"], LPGP_CONF["mysql"]["passwd"]);
// die(var_dump(json_decode($_POST["test"], true)));
if(isset($_POST["get"])){
    // searches for signatures with the parameters received
    if(isset($_POST["query-params"])){
        $params = json_decode($_POST["query-params"], true);
        $result = $obj->fastQuery($params);
        die(json_encode($result));
    }
}
else if(isset($_POST["add"])){
    $obj->addSignature((int)$_POST["prop"], $_POST["passwd"], (int)$_POST["code"], (bool)$_POST["needsEncode"]);
    die("0");
}
else if(isset($_POST["del"]) && isset($_POST["signature"])){
    $obj->delSignature((int)$_POST["signature"]);
    die("0");
}
else if(isset($_POST["change"]) && isset($_POST["signature"]) && isset($_POST["change-params"])){
    $obj->fastUpdate(json_decode($_POST["change-params"]), (int)$_POST["signature"]);
    die("0");
}
else{
    die("INVALID OPERATION");
}
