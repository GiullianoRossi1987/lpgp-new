<?php
if(session_status() == PHP_SESSION_NONE) session_start();
require_once "core/Core.php";
require_once "core/js-handler.php";
require_once "core/clients-data.php";

use function JSHandler\sendUserLogged;
use function JSHandler\createClientCard;
use Core\ClientsData;
use const LPGP_CONF;


$obj = new ClientsData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
$clients = $obj->getClientsByOwner($_COOKIE['user']);

$content = "";

foreach($clients as $client){
    $dt = $obj->getClientCardData($client['cd_client']);
	$content .= createClientCard($dt) . '<br>';
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
    <link rel="stylesheet" href="css/account.css">
    <link rel="stylesheet" href="css/content-style.css">
    <link rel="shortcut icon" href="media/new-logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.14.0/css/all.css" integrity="sha384-HzLeBuhoNPvSl5KYnjx0BT+WB0QEEqLprO+NBkkk5gbc67FTaL7XIGa2w1L0Xbgc" crossorigin="anonymous">
    <link href="bootstrap/dist/css/bootstrap.css" rel="stylesheet">
</head>
<style>
</style>
<body>
    <div class="container-fluid header-container" role="banner" style="position: fixed;">
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
    </div>
    <br>
    <hr>
    <div class="container-fluid container-content" style="position: relative; margin-top: 5%;">
        <div class="row-main row">
            <div class="col-6 clear-content" style="position: relative; margin-left: 23%; max-width: 100% !important">
                <div class="container container-fluid filter-container">
                    <div class="form-row row">
                        <div class="col-12 form-col">
                            <div class="input-group input-group-inline">
                                <div class="input-group-prepend">
                                    <label for="filter-main" class="form-label">Filter by</label>
                                </div>
                                <select class="form-control" name="filter" id="filter-main">
                                    <option value="0">No Filter</option>
                                    <option value="21">A-Z</option>
                                    <option value="22">Z-A</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <div id="dispose-items"></div>
                <br>
                <a href="create-client.php" role="button" class="btn btn-block btn-success">Create new client</a>
            </div>
        </div>
    </div>
    <br>
    <div class="footer-container container" style="max-width: 100% !important; position: relative; margin-left: 0;">
        <div class="footer-row row">
            <div class="footer col-12" style="height: 150px; background-color: black; margin-top: 100%; position: relative; max-width: 100% !important; margin-left: 0;">
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
    <!--Scripts -->
    <script src="jquery/lib/jquery-3.4.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="bootstrap/dist/js/bootstrap.js"></script>
    <script src="js/autoload.js" charset="utf-8"></script>
    <script src="js/main-script.js"></script>
    <script src="js/actions.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
    <script>
        $(document).ready(function(){
            setAccountOpts(true);
            setSignatureOpts();
            applyToA();
            applyToForms();
            $("#img-user").css("background-image", "url(" + getLinkedUserIcon() + ")");
            loadSearchButton();
            requestFilter(1, 0, "#dispose-items");
        });

        $(document).ready(function(){
            applyToA();
        })

    </script>
</body>
</html>
