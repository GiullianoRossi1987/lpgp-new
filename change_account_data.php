<?php
if(session_status() == PHP_SESSION_NONE) session_start();
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/js-handler.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/users-data.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/proprietaries-data.php";

use Core\UsersData;
use Core\ProprietariesData;
use function JSHandler\sendUserLogged;
use const LPGP_CONF;


if($_COOKIE['mode'] == "prop"){
	$prp = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
	if(isset($_POST['passwd']) && strlen($_POST['passwd']) > 0){
		$prp->chProprietaryPasswd($_COOKIE['user'], $_POST['passwd']);
	}
	if(isset($_POST['username']) && strlen($_POST['username']) > 0){
		$prp->chProprietaryName($_COOKIE['user'], $_POST['username']);
		$_COOKIE['user'] = $_POST['username'];
	}
	if(isset($_POST['email']) && strlen($_POST['email']) > 0){
		$prp->chProprietaryEmail($_COOKIE['user'], $_POST['email']);
		"<script>window.location.replace(\"./check-email-stp1.php\");</script>";
	}
	if(isset($_FILES['new-img'])){
		move_uploaded_file($_FILES['new-img']['tmp_name'][0], "/u.images/" . $_FILES['new-img']['name'][0]);
		$prp->chProprietaryImg($_COOKIE['user'], "/u.images/" . $_FILES['new-img']['name'][0]);
		$_COOKIE['user-icon'] = "/u.images/" . $_FILES['new-img']['name'][0];
	}
}
else{
	$usr = new UsersData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
	if(isset($_POST['passwd']) && strlen($_POST['passwd']) > 0){
		$usr->chUserPasswd($_COOKIE['user'], $_POST['passwd']);
	}
	if(isset($_POST['username']) && strlen($_POST['username']) > 0){
		$usr->chUserName($user= $_COOKIE['user'], $newname= $_POST['username']);
		$_COOKIE['user'] = $_POST['username'];
	}
	if(isset($_POST['email']) && strlen($_POST['email']) > 0){
		$usr->chUserEmail($_COOKIE['user'], $_POST['email']);
		"<script>window.location.replace(\"./check-email-stp1.php\");</script>";
	}
	if(isset($_FILES['new-img']) && strlen($_FILES['new-img']['name'][0]) > 0){
		move_uploaded_file($_FILES['new-img']['tmp_name'][0], "/u.images/" . $_FILES['new-img']['name'][0]);
		$usr->chImage($_COOKIE['user'], "/u.images/" . $_FILES['new-img']['name'][0]);
		$_COOKIE['user-icon'] = "/u.images/" . $_FILES['new-img']['name'][0];
	}
}
header("Location: my_account.html");

?>
