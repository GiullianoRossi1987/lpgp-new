<?php
if(session_status() == PHP_SESSION_NONE) session_start();
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/js-handler.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/clients-data.php";

use Core\ClientsData;
use const LPGP_CONF;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LPGP Oficial Server</title>
    <link rel="stylesheet" href="css/new-layout.css">
    <link rel="stylesheet" href="css/content-style.css">
    <link rel="shortcut icon" href="media/new-logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.14.0/css/all.css" integrity="sha384-HzLeBuhoNPvSl5KYnjx0BT+WB0QEEqLprO+NBkkk5gbc67FTaL7XIGa2w1L0Xbgc" crossorigin="anonymous">
    <link href="bootstrap/dist/css/bootstrap.css" rel="stylesheet">

</head>
<style media="screen">
    body{ background-color: #3e3d3d;}
    .form-control{
        background-color: #000000;
        color: #ded2d2;
    }

    .form-control:focus, .form-control:hover{
        background-color: #525252;
        color: #ded2d2;
    }

    h1, .form-label, h5{ color: whitesmoke; }

    #help-txt > *{
        color: whitesmoke;
    }

    #help-bt{ padding-left: 10px; float: right;}
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
                    <a href="docs/index.php" class="dropdown-item">Documentation</a>
                    <a href="about.html" class="dropdown-item">About Us</a>
                    <a href="contact-us.html" class="dropdown-item">Contact Us</a>
                </div>
            </div>
            <br>
        </div>
    </div>
	<div class="container container-fluid" style="margin-top: 10%;">
        <div class="row ">
            <div class="col-12 col-md-12 col-sm-12">
                <form>
					<center>
                        <h1 >Client creation</h1>
	                   <h5 >Here you can create your own clients.</h5>
                   </center>
					<br>
					<label for="client-nm-inp" class="form-label">Type a client name</label>
					<br>
					<input type="text" class="form-control" id="client-nm-inp" name="client-name">
					<br>
					<label for="client-permissions" class="form-label">
						Choose the client Type
						<button class="btn btn-secondary btn-sm" data-toggle="collapse" data-target="#help-txt" aria-expanded="false" aria-controls="help-txt" type="button" id="help-bt">
							Help
							<span>
								<i class="fas fa-info"></i>
							</span>
						</button>
					</label>
					<div class="collapse" id="help-txt">
						<h4>Client Permissions</h4>
						<p>
							The client permissions tell the client what he's able to do with the LPGP data access.
							A important thing to say: the data between the modes doesn't change, both client types
							can only access your proprietary account data and your signatures, including check
							a external signature which can be from other proprietary, but just it.
							<br>
							Here we have:
							<ul>
								<li>Normal clients: which are able to just see the LPGP data</li>
								<li>Root clients: which are able to see and change the LPGP data</li>
							</ul>
						</p>
					</div>
                    <br>
                    <select name="root_permissions" id="client-permissions" class="form-control">
                        <option value="normal">Normal Client</option>
                        <option value="root">Root client</option>
                    </select>
                    <br>
                    <button class="btn btn-success btn-block" type="button" id="create-client-bt">Create Client</button>

                    <!-- Modal -->
                    <div class="modal fade" id="finalModal" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Modal title</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                                </div>
                                <div class="modal-body">
                                    Want to create another client or just go to your profile page?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" id="create-more-bt">Create more clients</button>
                                    <button type="button" class="btn btn-primary" onclick="window.location.replace('my_account.html');">Go back to my account</button>
                                </div>
                            </div>
                        </div>
                    </div>
				</form>
            </div>
        </div>
    </div>
    <div class="footer-container container-fluid full-width">
        <div class="footer-row row">
            <div class="footer col-12">
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
    <script src="js/main-script.js"></script>
    <script src="js/actions.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
    <script>
        var show = false;
        $(document).ready(function(){
            setAccountOpts();
            setSignatureOpts();
            applyToA();
            if(show){
                $("#modelId").modal("show");
                show = false;
            }
        });

        $(document).ready(function(){
            $(".contitle").css("opacity", "1");
            $(".headtitle").css("opacity", "1");
        });

        $(document).on("click", "#create-client-bt", function(){
            $.post({
                url: "ajx_clients.php",
                data: {add: JSON.stringify({
                    "name": $("#client-nm-inp").val(),
                    "root": $("#client-permissions").val()
                })},
                dataType: "json",
                success: function(response){
                    if(response["success"] == 0){
                        $("#finalModal").modal("show");
                    }
                },
                error: function(error){ console.error(error); }
            });
        });

        $(document).on("click", "#create-more-bt", function(){
            $("#client-nm-inp").val("");
            $("#client-permissions").val("normal");
            $("#finalModal").modal("hide");
        });
    </script>
</body>
</html>
