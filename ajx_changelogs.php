<?php
require_once "core/Core.php";
require_once "core/changelog-core.php";

use Core\SignaturesChangeLogs;
use Core\ClientsChangeLogs;

if(isset($_POST["mode"]) && isset($_POST["ref"])){
    $sigcl = new SignaturesChangeLogs(LPGP_CONF['mysql']["sysuser"], LPGP_CONF['mysql']["passwd"]);
    $clicl = new ClientsChangeLogs(LPGP_CONF['mysql']["sysuser"], LPGP_CONF['mysql']["passwd"]);
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
 ?>
