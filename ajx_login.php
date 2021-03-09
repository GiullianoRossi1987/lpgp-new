<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Exceptions.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/users-data.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/proprietaries-data.php";

use Core\UsersData;
use Core\ProprietariesData;

if($_POST['account-type'] == "normal"){
    $obj = new UsersData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);  // trade for your username and password at MySQL
    // verifies password
    $valid = $obj->authPassword($_POST["username"], $_POST["password"]);
    if($valid){
        $user_data = $obj->fastQuery(array("nm_user" => $_POST["username"]))[0];
        if((int)$user_data["checked"] == 1) {
            $auth = $obj->login($_POST['username'], $_POST['password']);
            foreach($auth as $cookie => $val) setcookie($cookie, $val, time() + 7200);  // two hours of cookies
            $_COOKIE = $auth;
            die(json_encode(array("success" => 0)));
        }
        else die(json_encode(array("success" => 1, "key_check" => $user_data["vl_key"]))); // shows the check
    }
    else{
        die(json_encode(array("success" => -1))); // invalid data
    }

}
else if($_POST['account-type'] == "proprietary"){
    $obj = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
    $valid = $obj->authPasswd($_POST["username"], $_POST["password"]);
    if($valid){
        $data = $obj->fastQuery(array("nm_proprietary" => $_POST["username"]))[0];
        if((int)$data["checked"] == 1){
            $auth = $obj->login($_POST['username'], $_POST['password']);
            foreach($auth as $cookie => $val) setcookie($cookie, $val, time() + 7200);  // two hours of cookies
            $_COOKIE = $auth;
            die(json_encode(array("success" => 0)));
        }
        else die(json_encode(array("success" => 1, " key_check" => $data["vl_key"])));
    }
    else die(json_encode(array("success" => -1)));
}
else die("-2");
?>
