<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/core/Core.php";
use Core\ClientsData;
use const LPGP_CONF;

if(isset($_POST['submit']) && isset($_POST['client'])){
	$cl = (int)base64_decode($_POST['client']);
	$obj = new ClientsData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
	$original_data = $obj->getClientData($cl);
	if($_POST['client-name'] != $original_data['nm_client']) $obj->chClientName($cl, $_POST['client-name']);
	if((int)$_POST['permissions'] != $original_data['vl_root']) $obj->chClientPermissions($cl, (int)$_POST['permissions']);

	header("Location: ch-client.php?client=" . $_POST['client'] . "&alert=1");
}

else if(isset($_POST['chmodal']) && isset($_POST['client'])){
	$cl = (int)base64_decode($_POST['client']);
	$obj = new ClientsData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
	$obj->genNewTK($cl);

	header("Location: ch-client.php?client=" . $_POST['client'] . "&alert=1");
}
