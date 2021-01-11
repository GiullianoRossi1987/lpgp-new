<?php
if(session_status() == PHP_SESSION_NONE) session_start();
require_once "core/Core.php";
require_once "core/charts.php";
require_once "core/proprietaries-data.php";

use Core\ProprietariesData;
use Charts_Plots\AccessPlot;
use const Core\LPGP_CONF;

$content = "";

if(isset($_POST['client']) && (int)$_POST['client'] != 0){
    $charter = new AccessPlot("Client: " . $_POST['client']);
    if((int)$_POST['mode'] == 0) $charter->getClientAccesses($_POST['client'], true);
    else if((int)$_POST['mode'] == 1) $charter->getClientSuccessful($_POST['client'], true);
    else $charter->getClientUnsuccessful($_POST['client'], true);
    $content = $charter->generateChart("clients-plot");
}
else{
    $charter = new AccessPlot("Clients of " . $_COOKIE['user']);
    if((int)$_POST['mode'] == 0) $charter->allClientsChart($_COOKIE['user'], true);
    else if((int)$_POST['mode'] == 1) $charter->allClientsSuccessfulChart($_COOKIE['user'], true);
    else $charter->allClientsUnsuccessulChart($_COOKIE['user'], true);
    $content = $charter->generateChart("clients-plot");
}
die($content);
 ?>
