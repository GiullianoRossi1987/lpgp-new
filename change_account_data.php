<?php
if(session_status() == PHP_SESSION_NONE) session_start();
require_once "core/Core.php";
require_once "core/js-handler.php";
require_once "core/users-data.php";
require_once "core/proprietaries-data.php";

use Core\UsersData;
use Core\ProprietariesData;
use function JSHandler\sendUserLogged;
use const LPGP_CONF;


if($_SESSION['mode'] == "prop"){
	$prp = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
	if(isset($_POST['passwd']) && strlen($_POST['passwd']) > 0){
		$prp->chProprietaryPasswd($_SESSION['user'], $_POST['passwd']);
	}
	if(isset($_POST['username']) && strlen($_POST['username']) > 0){
		$prp->chProprietaryName($_SESSION['user'], $_POST['username']);
		$_SESSION['user'] = $_POST['username'];
	}
	if(isset($_POST['email']) && strlen($_POST['email']) > 0){
		$prp->chProprietaryEmail($_SESSION['user'], $_POST['email']);
		"<script>window.location.replace(\"./check-email-stp1.php\");</script>";
	}
	if(isset($_FILES['new-img'])){
		move_uploaded_file($_FILES['new-img']['tmp_name'][0], "/u.images/" . $_FILES['new-img']['name'][0]);
		$prp->chProprietaryImg($_SESSION['user'], "/u.images/" . $_FILES['new-img']['name'][0]);
		$_SESSION['user-icon'] = "/u.images/" . $_FILES['new-img']['name'][0];
	}
}
else{
	$usr = new UsersData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
	if(isset($_POST['passwd']) && strlen($_POST['passwd']) > 0){
		$usr->chUserPasswd($_SESSION['user'], $_POST['passwd']);
	}
	if(isset($_POST['username']) && strlen($_POST['username']) > 0){
		$usr->chUserName($user= $_SESSION['user'], $newname= $_POST['username']);
		$_SESSION['user'] = $_POST['username'];
	}
	if(isset($_POST['email']) && strlen($_POST['email']) > 0){
		$usr->chUserEmail($_SESSION['user'], $_POST['email']);
		"<script>window.location.replace(\"./check-email-stp1.php\");</script>";
	}
	if(isset($_FILES['new-img']) && strlen($_FILES['new-img']['name'][0]) > 0){
		move_uploaded_file($_FILES['new-img']['tmp_name'][0], "/u.images/" . $_FILES['new-img']['name'][0]);
		$usr->chImage($_SESSION['user'], "/u.images/" . $_FILES['new-img']['name'][0]);
		$_SESSION['user-icon'] = "/u.images/" . $_FILES['new-img']['name'][0];
	}
}
header("Location: my_account.php");

?>
