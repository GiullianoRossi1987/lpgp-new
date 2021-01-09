<?php
require_once "core/Core.php";
require_once "core/changelog-core.php";

use Core\SignaturesChangeLogs;
use Core\ClientsChangeLogs;

$sigcl = new SignaturesChangeLogs(LPGP_CONF['mysql']["sysuser"], LPGP_CONF['mysql']["passwd"]);
$clicl = new ClientsChangeLogs(LPGP_CONF['mysql']["sysuser"], LPGP_CONF['mysql']["passwd"]);
if(isset($_POST["mode"]) && isset($_POST["ref"])){
    switch((int)$_POST["mode"]){
        case 0:
            // signature mode
            $data = $sigcl->changesFrom((int)$_POST["ref"]);
            die(json_encode($data));
            break;
        case 1:
            // client  mode
            $data = $clicl->changesFrom((int)$_POST["ref"]);
            die(json_encode($data));
        default:
            die("INVALID MODE");
            break;
    }
}
else if(isset($_POST["mode"]) && isset($_POST["wayback"])){
    switch((int)$_POST["mode"]){
        case 0:
            $sigcl->restore((int)$_POST["wayback"]);
            die("0");
            break;
        case 1:
            $clicl->restore((int)$_POST["wayback"]);
            die("0");
            break;
        default: die("INVALID MODE");
    }
}
else die("INVALID OPTION");
 ?>
