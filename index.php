<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/js-handler.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/config/configmanager.php";

use function JSHandler\sendUserLogged;
use function JSHandler\setCon1Links;
use Configurations\ConfigManager;

$gblConfig = new ConfigManager("config/mainvars.json");
if(!defined("LPGP_CONF")) define("LPGP_CONF", $gblConfig->getConfig());

// TODO: Fix the query button
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
    <link rel="stylesheet" href="css/cards.css">
</head>
<style>
</style>
<body>
    <div class="container-fluid header-container" role="banner" style="position: relative;">
        <div class="col-md-12 header col-sm-12" style="height: 71px;">
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
                    <a href="./docs/index.php" class="dropdown-item">Documentation</a>
                    <a href="./about.html" class="dropdown-item">About Us</a>
                    <a href="./contact-us.html" class="dropdown-item">Contact Us</a>
                </div>
            </div>
            <div id="notification-container">

            </div>
        </div>
    </div>
    <div class="content1 container content">
        <div class="row rowcontent">
            <div class="col-12 col-md-12 col-sm-12 col-ls-12 main-header" >
                <h1 class="anim-appear masthead-heading text-uppercase mb-0" style="color: black; margin-top: 12%;"><u>LPGP</u></h1>
                <h1 class="masthead-heading text-uppercase mb-0" style="color: black; text-align: center; margin-top: 1%;">Let the golden raven lead you</h1>
            </div>
        </div>
        <div class="row rowcontentn">
            <div class="col-12 col-md-12 col-sm-12 content-nrm" id="con2">
                <div id="logo-ex"></div>
                <h1>What's LPGP?</h1>
                <p>
                    LPGP is a online authenticated certificate. There're so many
                    other
                </p>
            </div>
        </div>
        <hr>
        <div class="row rowcontentn">
        </div>
    </div>
    <div class="footer container" style="max-width: 100% !important; position: relative">
        <div class="footer-row row">
            <div class="footer col-12" style="height: 150px; background-color: black; margin-top: 100%; position: absolute; max-width: 100% !important; margin-left: 0;">
                <div class="social-options-grp">
                    <div class="social-option">
                        <a href="https://github.com/GiullianoRossi1987" target="_blanck" id="github" class="social-option-footer">
                            <span><i class="fab fa-github"></i></span>
                            Visit our github!
                        </a>
                    </div>
                    <br>
                    <div class="social-option-footer">
                        <a href="https://" target='_blanck' id="facebook">
                            <span><i class="fab fa-facebook"></i></span>
                            Visit our facebook!
                        </a>
                    </div>
                    <br>
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
    <script src="./js/main-script.js"></script>
    <script src="js/changelogs.js"></script>
    <script src="./js/actions.js"></script>
    <script src="./js/requester.js"></script>
    <script src="./js/generator.js"></script>
    <script>
        $(document).ready(function(){
            //readCookies();
            readCookies();
            setAccountOpts();
            setSignatureOpts();
            applyToA();
            loadSearchButton();
        });

        $(document).ready(function(){
            $(".contitle").css("opacity", "1");
            $(".headtitle").css("opacity", "1");
        });

    </script>
</body>
</html>
