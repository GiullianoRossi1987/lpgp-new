<?php
if(session_status() == PHP_SESSION_NONE) session_start();
require_once "core/js-handler.php";
require_once "core/Core.php";
require_once "core/users-data.php";
require_once "core/proprietaries-data.php";

use function JSHandler\lsSignaturesMA;
use function JSHandler\sendUserLogged;
use function JSHandler\createClientCard;

use Core\ProprietariesData;
use Core\UsersData;
use Core\PropCheckHistory;
use Core\UsersCheckHistory;
use Core\ClientsData;
use Core\ClientsAccessData;

sendUserLogged(); // preventing bugs

$prp = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
$usr = new UsersData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);

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
    #img-user{
        border-radius: 50%;
    }

    .prop-img{
        border: 5px solid green;
        background-color: green;
    }
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
                        <a href="http://localhost/docs/" class="dropdown-item">Documentation</a>
                        <a href="http://localhost/about.html" class="dropdown-item">About Us</a>
                        <a href="http://localhost/contact-us.html" class="dropdown-item">Contact Us</a>
                        <a href="https://www.lpgpofficial.com/report-trouble.html"></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
    <hr>
    <div class="container-fluid container-content" style="position: relative; margin-top: 5%;">
        <div class="row-main row">
            <div class="col-12 clear-content">
                <div class="container user-data-con" >
					<div class="main-row row">
                        <div class="main-col col-12 card" style="; border: none;">
                            <div class="container data-container">
                                <div class="main-row row card-header">
                                    <div class="img-cont card-img-top" style="margin-left: 35%; padding: 20px 21px;">
                                        <div id="img-user"></div>
                                    </div>
                                    <br>
                                    <div class="col-12 data-usr">
                                        <br>
                                        <?php
                                        if($_SESSION['mode'] == "prop"){
                                            $dt = $prp->getPropData($_SESSION['user']);
                                            echo "<h1 class=\"user-name\">Name: " . $dt['nm_proprietary'] . " <span class=\"badge badge-success\">Proprietary</span></h1>";
                                            echo "<h3 class=\"email\">Email: " . $dt['vl_email'] . "</h3>\n";
                                            echo "<h3 class=\"date-creation\">Date of creation: " . date_format(new DateTime($dt['dt_creation']), "Y-m-d") . "</h3>\n";

                                        }
                                        else{
                                            $dt = $usr->getUserData($_SESSION['user']);
                                            echo "<h1 class=\"user-name\"> " . $dt['nm_user'] . "   <span class=\"badge badge-secondary\">Normal User</span></h1>";
                                            echo "<h3 class=\"email\">Email: " . $dt['vl_email'] . "</h3>\n";
                                            echo "<h6 class=\"date-creation\">Date creation: " . date_format(new DateTime($dt['dt_creation']), "Y-m-d") . "</h3>\n";
                                        }
                                        ?>
                                        <a class="account-separator" id="accountopt-sep" href="#moreoptions-section" data-toggle="collapse" aria-expanded="false" aria-controls="moreoptions-section">
                                            <div class="content"><h2>More account options</h2></div>
                                        </a>
                                        <div class="collapse section" id="moreoptions-section">
                                            <br>
                                            <div class="btn-group" style="margin-left: 7%;">
                                                <a class="img-settings btn btn-lg btn-dark" href="ch_my_data.php" role="button">
                                                    Edit Account
                                                    <span>
                                                        <i class="fas fa-cog"></i>
                                                    </span>
                                                </a>
                                                <button class="btn btn-danger" data-toggle="modal" data-target="#modal-delete" type="button">
                                                    Remove account
                                                    <span>
                                                        <i class="fas fa-times"></i>
                                                    </span>
                                                </button>
                                                <div class="modal" id="modal-delete" tabindex="-1" aria-labelledby="del-btn" aria-hidden="true" role="dialog">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h3 class="modal-title">Are you sure about delete your account?</h3>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <hr>
                                                            <div class="modal-body">
                                                                <h3>That action can't be undone!</h3>
                                                                <a href="del_account.php?confirm=y" role="button" class="btn btn-lg btn-danger">Yes, delete my account</a>
                                                                <a href="#" role="button" class="btn btn-lg btn-secondary" data-dismiss="modal">Cancel</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <br>
                                                <?php
                                                if($_SESSION['mode'] == "prop"){
                                                    $id = base64_encode($dt['cd_proprietary']);
                                                    echo "<a href=\"proprietary.php?id=$id\" role=\"button\" target=\"_blanck\" class=\"btn btn-primary btn-lg\">See as another one</a>";
                                                }
                                                else{
                                                    $id = base64_encode($dt['cd_user']);
                                                    echo "<a href=\"user.php?id=$id\" role=\"button\" target=\"_blanck\" class=\"btn  btn-lg btn-primary\">See as another one</a>";
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    </div>
                    <div class="row itens-row">
                        <div class="others-col col-md-7" style="margin-left: 21%;">
                            <div class="signatures-col col-12">
                                <?php if($_SESSION['mode'] == "prop") echo '<a href="#signatures-section" class="account-separator" id="signature-sep" aria-controls="signatures-section" aria-expanded="false" data-toggle="collapse">
                                        <div class="content"><h2 class="mainheader-heading mb-0">My Signatures</h2></div>
                                    </a>';
                                ?>
                                <div id="signatures-section" class="collapse section">
                                    <?php
                                    // Signatures
                                    /////////////////////////////////////////////////////////////////////////////////////////////////
                                    if($_SESSION['mode'] == "prop"){
                                        $prp = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
                                        echo lsSignaturesMA($prp->getPropID($_SESSION['user']));
                                        echo "<br>\n<a href=\"create_signature.php\" role=\"button\" class=\"btn btn-block btn-success\">".
                                                    "Create a new signature <span><i class=\"fas fa-id-card\"></i></span>".
                                                    "</a><br>";
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="history-col col-12" style="position: relative; margin-top: 10%;">
                                <a class="account-separator" href="#history-section" data-toggle="collapse" aria-expanded="false" aria-controls="history-section" id="history-sep">
                                    <div class="content">
                                        <h2>
                                            My History
                                        </h2>
                                    </div>
                                </a>
                                <div class="collapse section" id="history-section">
                                    <?php
                                    // History
                                    ///////////////////////////////////////////////////////////////////////////////////////////////
                                    if($_SESSION['mode'] == "prop"){
                                        $obj = new PropCheckHistory(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
                                        $hist = $obj->getPropHistory($_SESSION['user']);
                                        $hist_e = explode("<br>", $hist);
                                        for($i = 0; $i <= MAX_SIGC; $i++){
                                            if(isset($hist_e[$i])) echo $hist_e[$i] . "<br>";
                                            else break;
                                        }
                                    }
                                    else{
                                        $obj = new UsersCheckHistory(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
                                        $hist = $obj->getUsrHistory($_SESSION['user']);
                                        $hist_e = explode("<br>", $hist);
                                        for($i = 0; $i <= MAX_SIGC; $i++){
                                            if(isset($hist_e[$i])) echo $hist_e[$i] . "<br>";
                                            else break;
                                        }
                                    }
                                    ?>
                                    <a href="my-history.php" role="button" class="btn btn-block btn-primary">
                                        See my history
                                        <span>
                                            <i class="fas fa-history"></i>
                                        </span>
                                    </a>
                                </div>
                            </div>
                            <div class="col-12 clients-col" style="margin-top: 10%;">
                                <?php
                                if($_SESSION['mode'] == "prop") echo '<a href="#clients-section" class="account-separator" data-toggle="collapse" aria-controls="clients-section" aria-expanded="false" id="client-sep">
                                    <div class="content">
                                        <h2>
                                            My Clients
                                        </h2>
                                    </div>
                                </a>'
                                ?>
                                <div class="collapse section" id="clients-section">
                                    <?php
                                    if($_SESSION['mode'] == "prop"){
                                        $obj = new ClientsData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
                                        $clients = $obj->getClientsByOwner($_SESSION['user']);
                                        $hs = new ClientsAccessData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
                                        $dt = "";
                                        if(count($clients) == 0){
                                            echo "<h1>You don't have any clients yet!</h1>";
                                        }
                                        else{
                                            $countLim = 0;
                                            foreach($clients as $client){
                                                $accs = $hs->getAccessClient($client['cd_client']);
                                                $cldt = [$client['cd_client'], $client['nm_client'], count($accs)];
                                                $dt .= createClientCard($cldt) . '<br>';
                                                $countLim++;
                                                if($countLim == 4) break;
                                            }
                                        }
                                        $dt .= '<a href="create-client.php" role="button" class="btn btn-success btn-block">Create a new Client</a>';
                                        echo $dt;
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
					</div>
				</div>
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
            $.post({
                url: "ajx_logged_request.php",
                data: "getJSON=t",
                success: function(json){
                    let brute = $.parseJSON(json);
                    if(brute['Mode'] == 0){
                        $("#img-cont").addClass("usr-img");
                    }
                    else{
                        $("#img-cont").addClass("prop-img");
                    }
                },
                error: function(xhr, status, error){ console.log(error); }
            });
            $("#img-user").css("background-image", "url(" + getLinkedUserIcon() + ")");
            loadSearchButton();
            $.post({
                url: "ajx_logged_request.php",
                data: "getJSON=t",
                success: function(response){
                    let jsonData = $.parseJSON(response);
                    if(jsonData['Logged'] == false){
                        alert("your session expired");
                    }
                }
            });
        });

        $(document).on("click", ".account-separator .content", function(){
            $(this).toggleClass("selected-separator");
        });
    </script>
</body>
</html>
