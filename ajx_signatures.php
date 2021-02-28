<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/signatures-data.php";

use Core\SignaturesData;
$obj = new SignaturesData(LPGP_CONF["mysql"]["sysuser"], LPGP_CONF["mysql"]["passwd"]);
// die(var_dump(json_decode($_POST["test"], true)));
if(isset($_POST["get"])){
    // searches for signatures with the parameters received
    $params = json_decode($_POST["get"], true);
    $result = $obj->fastQuery($params);
    die(json_encode($result));
}
else if(isset($_POST["add"])){
    $obj->addSignature((int)$_POST["prop"], $_POST["passwd"], (int)$_POST["code"], (bool)$_POST["needsEncode"]);
    die("0");
}
else if(isset($_POST["del"]) && isset($_POST["signature"])){
    $obj->delSignature((int)$_POST["signature"]);
    die("0");
}
else if(isset($_POST["change"]) && isset($_POST["signature"])){
    $obj->fastUpdate(json_decode($_POST["change"], true), (int)$_POST["signature"]);
    die("0");
}
else if(isset($_POST["get-opts"])){
    die(json_encode(SignaturesData::CODES));
}
else if(isset($_POST["download"])){
    $path = $obj->createsSignatureFile((int)$_POST["download"], false, $obj->generateFileNm(0));
    die(json_encode(array("path" => $path)));
}
else{
    die("INVALID OPERATION");
}
