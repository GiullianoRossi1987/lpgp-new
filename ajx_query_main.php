<?php
if(session_status() == PHP_SESSION_NONE) session_start();
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/js-handler.php";

use Core\UsersData;
use Core\ProprietariesData;
use Core\SignaturesData;
use Core\ClientsData;
use function JSHandler\genResultNormalUser;
use function JSHandler\genResultProprietary;
use function JSHandler\genResultClient;

function queryAll(string $needle): string{
    // simple handle
    $mainContent = "";

    if($_POST['scope'] != "all"){
        $clients = new ClientsData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
        $cls = $_POST['scope'] == "all" ? $clients->qrAllClients($needle) : $clients->qrClientsOfProp($needle, $_COOKIE['user']);
        foreach($cls as $item) $mainContent .= genResultClient($item);
        $mainContent .= "<br>" . PHP_EOL;
        unset($clients);
        unset($cls);
    }
    else{
        $usr = new UsersData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
        $usrs = $usr->qrUserByName($needle);
        foreach($usrs as $user) $mainContent .= genResultNormalUser($user);
        $mainContent .= "<br>" . PHP_EOL;
        unset($usr);
        unset($usrs);

        $prp = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
        $prps = $prp->qrPropByName($needle);
        foreach($prps as $proprietary) $mainContent .= genResultProprietary($proprietary);
        $mainContent .= "<br>" . PHP_EOL;
        unset($prp);
        unset($prps);

        $clients = new ClientsData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
        $cls = $_POST['scope'] == "all" ? $clients->qrAllClients($needle) : $clients->qrClientsOfProp($needle, $_COOKIE['user']);
        foreach($cls as $item) $mainContent .= genResultClient($item);
        $mainContent .= "<br>" . PHP_EOL;
        unset($clients);
        unset($cls);
    }
    return strlen($mainContent) > 0 ? $mainContent : "<h3>No Results</h3>" . PHP_EOL;
}

function queryUsrs(string $needle): string{
    // simple handle
    $mainContent = "";
    $usr = new UsersData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
    $usrs = $usr->qrUserByName($needle);
    foreach($usrs as $user) $mainContent .= genResultNormalUser($user);
    $mainContent .= "<br>" . PHP_EOL;
    unset($usr);
    unset($usrs);

    $prp = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
    $prps = $prp->qrPropByName($needle);
    foreach($prps as $proprietary) $mainContent .= genResultProprietary($proprietary);
    $mainContent .= "<br>" . PHP_EOL;
    unset($prp);
    unset($prps);

    return $mainContent;
}

if(isset($_POST['scope']) && isset($_POST['mode']) && isset($_POST['needle'])){
    // starts the query's
    $blank_ = [];
    $content = "";
    if($_POST['scope'] == "all"){
        switch((int)$_POST['mode']){
            case 0:
                die(queryAll($_POST['needle']));
                break;
            case 1:
                die(queryUsrs($_POST['needle']));
                break;
            case 2:
                $nrl = new UsersData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
                $blank_ = $nrl->qrUserByName($_POST['needle'], false);
                foreach($blank_ as $item) $content .= genResultNormalUser($item);
                unset($nrl);
                break;
            case 3:
                $prp = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
                $blank_ = $prp->qrPropByName($_POST['needle'], false);
                foreach($blank_ as $data) $content .= genResultProprietary($data);
                unset($prp);
                break;
            case 4:
                $cld = new ClientsData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
                $blank_ = $cld->qrAllClients($_POST['needle']);
                foreach($blank_ as $item) $content .= genResultClient($item);
                unset($cld);
                break;
            default: die("INTERNAL ERROR");
        }
    }
    else{
        switch((int)$_POST['mode']){
            case 0:
                die(queryAll($_POST['needle']));
                break;
            case 4:
                $cld = new ClientsData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
                $blank_ = $cld->qrClientsOfProp($_POST['needle'], $_COOKIE['user']);
                foreach($blank_ as $client) $content .= genResultClient($client);
                unset($cld);
                break;
            default: die('INTERNAL ERROR');
        }
    }
    if(strlen($content) == 0) die("<h3>No Results</h3>" . PHP_EOL);
    else die($content);
}
 ?>
