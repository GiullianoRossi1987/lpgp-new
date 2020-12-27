<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/core/Core.php";

use Core\ClientsData;
use const LPGP_CONF;

if(isset($_GET['client'])){
	$cl_id = base64_decode($_GET['client']);
	$obj = new ClientsData("giulliano_php", "");
	$obj->rmClient((int)$cl_id);

	header("Location: my_account.php");

}
