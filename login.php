<?php
session_start();

require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/js-handler.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/proprietaries-data.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/users-data.php";

use Core\ProprietariesData;
use Core\UsersData;
use function JSHandler\sendUserLogged;

use const LPGP_CONF;


if($_POST['account-type'] == "normal"){
    $user_obj = new UsersData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);  // trade for your username and password at MySQL
    $auth = $user_obj->login($_POST['username'], $_POST['password']);
    // tests with cookies too
    foreach($auth as $cookie => $val) setcookie($cookie, $val, time() + 7200);  // two hours of cookies
    $_COOKIE = $auth;
}
else if($_POST['account-type'] == "proprietary"){
    $prop_obj = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
    $auth = $prop_obj->login($_POST['username'], $_POST['password']);
    // tests with cookies too
    foreach($auth as $cookie => $val) setcookie($cookie, $val, time() + 7200);  // two hours of cookies
    $_COOKIE = $auth;
}

if($_COOKIE['checked'] == "false"){

    header("Location: check-email-stp1.php");

}
else{

    header("Location: ../index.php");

}
?>
