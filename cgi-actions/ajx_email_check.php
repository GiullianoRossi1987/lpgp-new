<?php
/** NOT IMPLEMENTED YET! WARNING, ON TESTS*/
require_once $_SERVER['DOCUMENT_ROOT'] . "/core/Core.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/core/Exceptions.php";

use Core\UsersData;
use Core\ProprietariesData;

$resp = array(
    "valid" => null,   // boolean
    "error" => null,   // string
    "success" => false // boolean
);
if(isset($_POST['cd'])){
    try{
        if(isset($_POST['usr']){
            $obj = new UsersData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
            $resp['valid'] = $obj->authUserKey($_POST['usr'], $_POST['cd']);
            if($resp["valid"]) $obj->setUserChecked($_POST['usr'], true);
            $resp["success"] = true;
        }
        else if(isset($_POST['prp'])){
            $obj = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
            $resp["valid"] = $obj->authPropKey($_POST['prp'], $_POST['cd']);
            if($resp["valid"]) $obj->setProprietaryChecked($_POST['prp'], true);
            $resp["success"] = true;
        }
        else{
            $resp["valid"]   = null;
            $resp["error"]   = "INVALID PARAMETERS";
            $resp["success"] = false;
        }
    }
    catch(Exception $e){
        $resp["valid"]   = null;
        $resp["error"]   = $e->getMessage();
        $resp["success"] = false;
    }
    finally{ die(json_encode($resp)); }
}
 ?>
