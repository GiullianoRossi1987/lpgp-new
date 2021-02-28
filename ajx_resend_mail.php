<?php
if(session_status() == PHP_SESSION_NONE) session_start();
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Exceptions.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/proprietaries-data.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/users-data.php";

use Core\UsersData;
use Core\ProprietariesData;

if(isset($_POST['resend'])){
    if($_COOKIE['mode'] === "normie"){
        $usr = new UsersData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
        $usr->sendCheckEmail($_COOKIE['user']);
    }
    else if($_COOKIE['mode'] === "prop"){
        $prp = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
        $prp->sendCheckEmail($_COOKIE['user']);
    }
    else{
        die("INTERNAL ERROR");
    }
}
 ?>
