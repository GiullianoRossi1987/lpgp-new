<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/changelog-core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Exceptions.php";

use Core\SignaturesChangeLogs;
use Core\ClientsChangeLogs;

if(isset($_GET["t"]) && isset($_GET["ref"])){
    if((int)$_GET["t"] == 0){
        // signatures mode
        $sigcl = new SignaturesChangeLogs(LPGP_CONF["mysql"]["sysuser"], LPGP_CONF["mysql"]["passwd"]);
        
    }
}
 ?>
