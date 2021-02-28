<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/js-handler.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/config/configmanager.php";

use function JSHandler\sendUserLogged;
use function JSHandler\setCon1Links;
use Configurations\ConfigManager;

$gblConfig = new ConfigManager("/config/mainvars.json");
if(!defined("LPGP_CONF")) define("LPGP_CONF", $gblConfig->getConfig());

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
    .main-container{
        margin-top: 8% !important;
        border: 1px solid black;
        padding: 20px 21px;
        border-radius: 10px;
    }
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
        </div>
    </div>
    <br>
    <div class="container-fluid container main-container">
        <div class="row opt-row">
            <div class="col-md-12 options-col">
                <form class="form" method="POST">
                    <div class="row form-row">
                        <div class="col-md-12">
                            <div class="input-group input-group-inline">
                                <input type="text" name="qr" id="needle-in" class="form-control" placeholder="Search here...">
                                <div class="input-group-append">
                                    <button type="button" name="button" id="search-bt" class="btn btn-success">
                                        <span>
                                            <i class="fas fa-search"></i>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="row opt-row">
                        <div class="col-md-4">
                            <div class="form-group scope-group">
                                <div class="form-check all-rb form-check-inline qr-chk opt-scope">
                                    <input type="radio" name="scope" value="all" id="scope-all" class="form-check-control" checked>
                                    <label class="form-check-label" for="scope-all"> All the LPGP Site</label>
                                </div>
                                <div class="form-check rs-rb form-check-inline qr-chk opt-scope" id="main-spc-rs">
                                    <input type="radio" name="scope" value="logged" id="scope-rs" class="form-check-control">
                                    <label for="scope-rs" class="form-check-label">Only at my account</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group mode-group form-group-inline">
                                <div class="form-check all-rb form-check-line qr-chk">
                                    <input type="radio" name="mode" value="0" class="form-check-control" id="md-a-ch" checked>
                                    <label for="md-a-ch" class="form-check-label">All results</label>
                                </div>
                                <br>
                                <div class="form-check-inline form-check usr-rb qr-chk">
                                    <input type="radio" name="mode" value="1" class="form-check-control" id="md-u-ch">
                                    <label for="md-u-ch" class="form-check-label">Only users</label>
                                </div>
                                <div class="form-check form-check-inline nrm-rb qr-chk">
                                    <input type="radio" name="mode" value="2" class="form-check-control" id="md-n-ch">
                                    <label for="md-n-ch" class="form-check-label">Only Normal Users</label>
                                </div>
                                <div class="form-check form-check-inline prp-rb qr-chk">
                                    <input type="radio" name="mode" value="3" class="form-check-control" id="md-p-ch">
                                    <label for="md-p-ch" class="form-check-label">Only Proprietaries</label>
                                </div>
                                <div class="form-check form-check-inline cli-rb qr-chk">
                                    <input type="radio" name="mode" value="4" class="form-check-control" id="md-c-ch" >
                                    <label for="md-c-ch" class="form-check-label">Only Clients</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <br>
        <div class="row results-row">
            <div class="col-md-12 results-col">
                <div id="results-dispose">
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="footer-container container">
        <div class="footer-row row">
            <div class="footer col-12">
                <div class="social-options-grp">
                    <div class="social-option">
                        <a href="https://github.com/GiullianoRossi1987" target="_blanck" id="github" class="social-option-footer">
                        <span><i class="fab fa-github"></i></span>Visit our github</a>
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
            setAccountOpts();
            setSignatureOpts();
            applyToA();
            $.post({
                url: "ajx_logged_request.php",
                data: "getJSON=t",
                success: function(response){
                    let mainData = $.parseJSON(response);
                    if(parseInt(mainData['Mode']) != 1 || !mainData['Logged']){
                        $("#scope-rs").prop("disabled", true);
                        $("#main-spc-rs").prop("data-toggle", "tooltip");
                        $("#main-spc-rs").prop("title", "This feature isn't available now");
                        ////////////////////////////////////////////////////////////////////
                        $("#md-c-ch").prop("disabled", true);
                        $(".cli-rb").prop("data-toggle", "tooltip");
                        $(".cli-rb").prop("title", "This feature isn't available for you now");
                    }
                    else{
                        $("#scope-rs").prop("disabled", false);
                        $("#md-c-ch").prop("disabled", false);

                        $(".cli-rb").prop("data-toggle", null);
                        $(".cli-rb").prop("title", null);

                        $("#main-spc-rs").prop("data-toggle", null);
                        $("#main-spc-rs").prop("title", null);
                    }
                },
                error: function(xhr, status, error){ console.error(error); }
            });
        });

        $(document).ready(function(){
            $(".contitle").css("opacity", "1");
            $(".headtitle").css("opacity", "1");
            dpOptionsScope();
        });

        $(document).on("click", "#search-bt", function(){
            let mode = document.getElementsByName('mode');
            let scope = document.getElementsByName('scope');
            requestQuery($('#needle-in').val(), $("input[name='scope']:checked").val(), $("input[name='mode']:checked").val(),  "#results-dispose");
        });

        function dpOptionsScope(){
            if($("input[name='scope']:checked").val() != "all"){
                $("#md-u-ch").prop("disabled", true);
                $(".usr-rb").prop("data-toggle", "tooltip");
                $(".usr-rb").prop("title", "This feature isn't available now");

                $("#md-p-ch").prop("disabled", true);
                $(".prp-rb").prop("data-toggle", "tooltip");
                $(".prp-rb").prop("title", "This feature isn't available now")

                $("#md-n-ch").prop("disabled", true);
                $(".nrm-rb").prop("data-toggle", "tooltip");
                $(".nrm-rb").prop("title", "This feature isn't available now");
            }
            else{
                $("#md-u-ch").prop("disabled", false);
                $("#md-p-ch").prop("disabled", false);
                $("#md-n-ch").prop("disabled", false);

                $(".usr-rb").prop("data-toggle", null);
                $(".usr-rb").prop("title", null);

                $(".prp-rb").prop("data-toggle", null);
                $(".prp-rb").prop("title", null);

                $(".nrm-rb").prop("data-toggle", null);
                $(".nrm-rb").prop("title", null);
            }
        }

        $(document).on("change", $(".opt-scope"), function(){ dpOptionsScope(); });

    </script>
</body>
</html>
