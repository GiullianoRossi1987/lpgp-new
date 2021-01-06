<?php
require_once "core/Core.php";
require_once "core/changelog-core.php";
require_once "core/Exceptions.php";

use Core\SignaturesChangeLogs;
use Core\ClientsChangeLogs;

if(isset($_GET["t"]) && isset($_GET["ref"])){
    if((int)$_GET["t"] == 0){
        // signatures mode
        $sigcl = new SignaturesChangeLogs(LPGP_CONF["mysql"]["sysuser"], LPGP_CONF["mysql"]["passwd"]);
        
    }
}
 ?>
