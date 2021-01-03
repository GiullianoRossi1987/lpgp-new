<?php
/**
 * That file sends the user data directly from the database and send to the
 * page that requested those data in JSON format. It's in experimental status,
 * but it can work normally once it's done.
 *
 * The JSON data returned have the following structure:
 *  - Logged => If there's a user logged (boolean)
 *  - Mode => If the user is a normal user (0) or a proprietary (1) (int)
 *  - Username => The full username from the logged user (string)
 *  - Password => The encoded user password (base64/string)
 *  - Email => The user email (string)
 */
if(session_status() == PHP_SESSION_NONE) session_start();
require_once "core/Core.php";
require_once "core/js-handler.php";
require_once "core/users-data.php";
require_once "core/proprietaries-data.php";

use Core\UsersData;
use Core\ProprietariesData;

if(isset($_POST['getJSON'])){
    if((bool)$_SESSION['user-logged'] && isset($_SESSION['user'])){
        $prp = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
        $usr = new UsersData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
        $mainArr = array(
            "Logged" => true,
        );
        if($_SESSION['mode'] == "normie"){
            $mainArr['Mode'] = 0;
            $bruteData = $usr->getUserData($_SESSION['user']);
            $mainArr['Username'] = $_SESSION['user'];
            $mainArr['Email'] = $bruteData['vl_email'];
            $mainArr['Password'] = $bruteData['vl_password']; // already encoded at the database
            $mainArr['ImgUrlPath'] = $bruteData['vl_img'];
            $mainArr["Checked"] = $bruteData["vl_checked"];
        }
        else{
            // proprietary then
            $mainArr['Mode'] = 1;
            $bruteData = $prp->getPropData($_SESSION['user']);
            $mainArr['Username'] = $_SESSION['user'];
            $mainArr['Email'] = $bruteData['vl_email'];
            $mainArr['Password'] = $bruteData['vl_password']; // already encoded at the database
            $mainArr['ImgUrlPath'] = $bruteData['vl_img'];
            $mainArr["Checked"] = $bruteData["vl_checked"];
        }
        echo json_encode($mainArr);
    }
    else {
        $emptyData = [];
        $emptyData['Logged'] = false;
        die(json_encode($emptyData));
    }
}
 ?>
