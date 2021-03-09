<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Exceptions.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/users-data.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/proprietaries-data.php";

use Core\UsersData;
use Core\ProprietariesData;

if(isset($_POST["send-email"])){
    if($_POST["account-mode"] == "normal"){
        $obj = new UsersData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
        $obj->sendCheckEmail($_POST["username"]);
        die("0");
    }
    else if($_POST["account-mode"] == "proprietary"){
        $obj = new ProprietariesData(LPGP_CONF["mysql"]["sysuser"], LPGP_CONF["mysql"]["passwd"]);
        $obj->sendCheckEmail($_POST["username"]);
        die("0");
    }
    else die("ERROR");
}

?>
