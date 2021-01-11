<?php
require_once "core/Core.php";
require_once "core/Exceptions.php";
require_once "core/js-handler.php";
require_once "core/users-data.php";
require_once "core/proprietaries-data.php";

use Core\UsersData;
use Core\ProprietariesData;
use function JSHandler\sendUserLogged;

$status = "none";
try{
    if($_COOKIE['mode'] == "prop"){
    	$prp = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
        $data = $prp->getPropData($_COOKIE["user"]);
    	if(isset($_POST['new-name']))
            $prp->chProprietaryName($data["nm_proprietary"], $_POST['new-name']);
        if(isset($_POST["new-img"]))
            $prp->chIMage($data["nm_proprietary"], $_POST["new-img"]);
        if(isset($_POST["new-email"]))
            $prp->chProprietaryEmail($data["nm_proprietary"], $_POST["new-email"]);
        if(isset($_POST["new-passwd"]))
            $prp->chProprietaryPasswd($data["nm_proprietary"], $_POST["new-passwd"]);
    }
    else{
    	$usr = new UsersData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
    	$data = $usr->getUserData($_COOKIE['user']);
    	if(isset($_POST['new-name']))
            $usr->chUserName($data["nm_user"], $_POST['new-name']);
        if(isset($_POST["new-img"]))
            $usr->chImage($data["nm_user"], $_POST["new-img"]);
        if(isset($_POST["new-email"]))
            $usr->chUserEmail($data["nm_user"], $_POST["new-email"]);
        if(isset($_POST["new-passwd"]))
            $usr->chUserPasswd($data["nm_user"], $_POST["new-passwd"]);
    }
    $status = "success";
}
catch(Exception $e){
    $status = $e->getMessage();
}
die($status);
?>
