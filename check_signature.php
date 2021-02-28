<?php
if(session_status() == PHP_SESSION_NONE) session_start();
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/js-handler.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Exceptions.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/signatures-data.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/proprietaries-data.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/prop-history.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/users-data.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/usr-history.php";


use Core\SignaturesData;
use function JSHandler\sendUserLogged;
use function JSHandler\createSignatureCardAuth;
use Core\PropCheckHistory;
use Core\UsersCheckHistory;
use Core\ProprietariesData;
use Core\UsersData;

use const LPGP_CONF;

use SignaturesExceptions\InvalidSignatureFile;
use SignaturesExceptions\SignatureAuthError;
use SignaturesExceptions\SignatureFileNotFound;
use SignaturesExceptions\SignatureNotFound;
use SignaturesExceptions\VersionError;

   // Just preventing any error in the localStorage.
$signature_img = "<img src=\"%path%\" alt=\"%alt%\">";
$signature_msg = "";
$prp_obj = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
$usr_obj = new UsersData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
$domAdd = "";

// uploads the file
move_uploaded_file($_FILES['signature-ext']['tmp_name'][0], "../usignatures.d/" . $_FILES['signature-ext']['name'][0]);


if($_COOKIE['mode'] == 'prop'){
	$prp_c = new PropCheckHistory(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
	$sig = new SignaturesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
	$prop_id = $prp_obj->getPropID($_COOKIE['user']);
	$data = $sig->getSignatureFData("../usignatures.d/".$_FILES['signature-ext']['name'][0]);
	$rp1 = "";
	$vl = false;
	try{
		if($sig->checkSignatureFile("../usignatures.d/".$_FILES['signature-ext']['name'][0])){
			$data = $sig->getSignatureFData("../usignatures.d/".$_FILES['signature-ext']['name'][0]);
			$rp = str_replace("%path%", "src1", $signature_img);
			$rp1 = str_replace("%alt%", "valid signature", $rp);
			$signature_msg = "The signature is valid!";
			$rel_id = $prp_c->addReg($prop_id, $data['ID'], 1, null);
			$vl = true;
		}
		else{
			$rel_id = $prp_c->addReg($prop_id, $data['ID'], 0, 2);
		}
	}
	catch(InvalidSignatureFile $e){
		$rel_id = $prp_c->addReg($prop_id, $data['ID'], 0, 1);
		$rp = str_replace("%path%", "src2", $signature_img);
		$rp1 = str_replace("%alt%", "invalid signature", $rp);
		$signature_msg = "The signature is invalid!";
	}
	catch(SignatureNotFound $e){
		$rel_id = $prp_c->addReg($prop_id, $data['ID'], 0, 2);
		$rp = str_replace("%path%", "src2", $signature_img);
		$rp1 = str_replace("%alt%", "invalid signature", $rp);
		$signature_msg = "The signature is invalid!";
	}
	catch(SignatureFileNotFound $e) {
		$rel_id = $prp_c->addReg($prop_id, $data['ID'], 0, 1);
		$rp = str_replace("%path%", "src2", $signature_img);
		$rp1 = str_replace("%alt%", "invalid signature", $rp);
		$signature_msg = "The signature is invalid!";
	}
	catch(SignatureAuthError $e){
		$rel_id = $prp_c->addReg($prop_id, $data['ID'], 0, 3);
		$rp = str_replace("%path%", "src2", $signature_img);
		$rp1 = str_replace("%alt%", "invalid signature", $rp);
		$signature_msg = "The signature is invalid!";
	}
	finally{
		$signature_img = $rp1;
		unset($rp);
		unset($rp1);
		$rr = base64_encode($rel_id);
		$domAdd .= createSignatureCardAuth($data['ID'], $vl) . "<a href=\"relatory.php?rel=$rr\" role=\"button\" class=\"btn btn-block btn-primary\">See relatory</a><br><hr>";
	}
}
else{
	$usr_c = new UsersCheckHistory(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
	$sig = new SignaturesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
	$usr_id = $usr_obj->getUserData($_COOKIE['user'])['cd_user'];
	$vl = false;
	try{
		if($sig->checkSignatureFile("../usignatures.d/".$_FILES['signature-ext']['name'][0])){
			$data = $sig->getSignatureFData("../usignatures.d/".$_FILES['signature-ext']['name'][0]);
			$rp = str_replace("%path%", "src1", $signature_img);
			$rp1 = str_replace("%alt%", "valid signature", $rp);
			$signature_msg = "Valid signature";
			$rel_id = $usr_c->addReg($usr_id, $data['ID']);
			$vl = true;
		}
	}
	catch(InvalidSignatureFile $e){
		$data = $sig->getSignatureFData("../usignatures.d/".$_FILES['signature-ext']['name'][0]);
		$rel_id = $usr_c->addReg($usr_id, $data['ID'], 0, 1);
		$rp = str_replace("%path%", "src2", $signature_img);
		$rp1 = str_replace("%alt%", "invalid signature", $rp);
		$signature_msg = "The signature is invalid!";
	}
	catch(SignatureNotFound $e){
		$rel_id = $usr_c->addReg($usr_id, $data['ID'], 0, 2);
		$rp = str_replace("%path%", "src2", $signature_img);
		$rp1 = str_replace("%alt%", "invalid signature", $rp);
		$signature_msg = "The signature is invalid!";
	}
	catch(SignatureFileNotFound $e){
		$rel_id = $usr_c->addReg($usr_id, $data['ID'], 0, 1);
		$rp = str_replace("%path%", "src2", $signature_img);
		$rp1 = str_replace("%alt%", "invalid signature", $rp);
		$signature_msg = "The signature is invalid!";
	}
	catch(SignatureAuthError $e){
		$data = $sig->getSignatureFData("../usignatures.d/".$_FILES['signature-ext']['name'][0]);
		die(var_dump($data));
		$rp = str_replace("%path%", "src2", $signature_img);
		$rp1 = str_replace("%alt%", "invalid signature", $rp);
		$rel_id = $usr_c->addReg($usr_id, $data['ID'], 0, 3);
	}
	finally{
        $signature_img = $rp1;
		unset($rp);
		unset($rp1);
		$rr = base64_encode($rel_id);
		$domAdd .= createSignatureCardAuth($data['ID'], $vl) ."\n<a href=\"relatory.php?rel=$rr\" role=\"button\" class=\"btn btn-sm btn-primary\">See relatory</a><br>";
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LPGP Oficial Server</title>
    <link rel="stylesheet" href="css/new-layout.css">
    <link rel="stylesheet" href="css/content-style.css">
    <link rel="shortcut icon" href="media/new-logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.14.0/css/all.css" integrity="sha384-HzLeBuhoNPvSl5KYnjx0BT+WB0QEEqLprO+NBkkk5gbc67FTaL7XIGa2w1L0Xbgc" crossorigin="anonymous">
    <link href="bootstrap/dist/css/bootstrap.css" rel="stylesheet">
</head>
<style>
</style>
<body>

    <div class="container-fluid header-container" role="banner" style="position: relative;">
        <div class="col-12 header" style="height: 71px; transition: background-color 200ms linear;">
            <div class="opt-dropdown dropdown login-dropdown">
                <button type="button" class="btn btn-lg default-btn-header dropdown-toggle" data-toggle="dropdown" id="account-opts" aria-haspopup="true" aria-expanded="false">
                    <span class="nm-tmp">Account</span>
                </button>
                <div class="dropdown-menu opts" aria-labelledby="account-opts"></div>
            </div>
            <div class="opt-dropdown dropdown after-opt signatures-dropdown">
                <button class="dropdown-toggle btn btn-lg default-btn-header" data-toggle="dropdown" aria-expanded="false" aria-haspopup="true" id="signature-opts">
                    Signatures
                </button>
                <div class="dropdown-menu opts" aria-labelledby="signature-opts"></div>
            </div>
            <div class="opt-dropdown dropdown after-opt help-dropdown">
                <button class="dropdown-toggle btn btn-lg default-btn-header" data-toggle="dropdown" aria-expanded="false" aria-haspopup="true" id="help-opt">
                    Help
                </button>
                <div class="dropdown-menu opts" aria-labelledby="help-opt">
                    <a href="https://www.lpgpofficial.com/docs/" class="dropdown-item">Documentation</a>
                    <a href="https://www.lpgpofficial.com/about.html" class="dropdown-item">About Us</a>
                    <a href="https://www.lpgpofficial.com/contact-us.html" class="dropdown-item">Contact Us</a>
                </div>
            </div>
        </div>

    </div>
    <br>
    <hr>
    <div class="container-fluid container-content" style="position: relative; margin-top: 10%;">
        <div class="row-main row">
            <div class="col-7 clear-content" style="position: relative; margin-left: 21%; margin-top: 10%;">
				<?php
					echo $domAdd;
				?>
                <br>
            </div>
        </div>
    </div>
    <br>
    <div class="footer-container container">
        <div class="footer-row row">
            <div class="footer col-12" style="height: 150px; background-color: black; margin-top: 100%; position: relative; max-width: 100%; left: 0;">
                <div class="social-options-grp">
                    <div class="social-option">
                        <a href="https://github.com/GiullianoRossi1987" target="_blanck" id="github" class="social-option-footer">
                        <span><i class="fab fa-github"></i></span></a>
                    </div>
                    <div class="social-option-footer">
                        <a href="https://" target='_blanck' id="facebook">

                        </a>
                    </div>
                    <div class="social-option-footer">
                        <a href="https://" target='_blanck' id="twitter"></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Scripts -->
    <script src="jquery/lib/jquery-3.4.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="bootstrap/dist/js/bootstrap.js"></script>
    <script src="js/autoload.js" charset="utf-8"></script>
    <script src="js/main-script.js"></script>
    <script src="js/actions.js"></script>
    <script>
        $(document).ready(function(){
            setAccountOpts(true);
            setSignatureOpts();
        });
    </script>
</body>
</html>
