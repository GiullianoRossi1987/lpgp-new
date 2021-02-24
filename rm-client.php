<?php
require_once "core/Core.php";
require_once "core/clients-data.php";

use Core\ClientsData;
use const LPGP_CONF;

if(isset($_GET['client'])){
	$cl_id = base64_decode($_GET['client']);
	$obj = new ClientsData("giulliano_php", "");
	$obj->rmClient((int)$cl_id);

	header("Location: my_account.html");

}
