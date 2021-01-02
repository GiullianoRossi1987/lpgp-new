<?php
require_once "core/Core.php";
require_once "core/Exceptions.php";
require_once "core/js-handler.php";

use Core\UsersData;
use Core\ProprietariesData;
use function JSHandler\sendUserLogged;


// sendUserLogged();
// die(var_dump($_SESSION));
$status = "none";
// die(var_dump($_POST));
try{
    if($_SESSION['mode'] == "prop"){
    	$prp = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
        $data = $prp->getPropData($_SESSION["user"]);
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
    	$data = $usr->getUserData($_SESSION['user']);
    	if(isset($_POST['new-name']))
            $usr->chUserName($data["nm_user"], $_POST['new-name']);
        if(isset($_POST["new-img"]))
            $usr->chImage($data["nm_user"], $_POST["new-img"]);
        if(isset($_POST["new-email"]))
            $usr->chUserEmail($data["nm_user"], $_POST["new-email"]);
        if(isset($_POST["new-passwd"]))
            $usr->chUserPasswd($data["nm_user"], $_POST["new-passwd"]);
    }
    // sendUserLogged();
    $status = "success";
}
catch(Exception $e){
    // die($e->getMessage());
    $status = $e->getMessage();
}
die($status);
?>
