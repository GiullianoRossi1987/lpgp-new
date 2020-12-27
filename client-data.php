<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/core/Core.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/core/js-handler.php";

use Core\ClientsData;
use const LPGP_CONF;

if(isset($_GET['client'])){
	$client = (int)base64_decode($_GET['client']);
	$obj = new ClientsData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
	$link = $obj->genConfigClient($client);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LPGP Oficial Server</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <link rel="stylesheet" href="css/new-layout.css">
    <script src="js/main-script.js"></script>
    <script src="js/actions.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <link rel="shortcut icon" href="../media/new-logo.png" type="image/x-icon">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.2/popper.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css">
</head>

<style>
    #home-link{
        top: 0px;
    }
</style>

<body>
	<script>
        $(document).ready(function(){
            setAccountOpts();
            setSignatureOpts();
            applyToA();
        });

        $(document).ready(function(){
            $(".contitle").css("opacity", "1");
            $(".headtitle").css("opacity", "1");
        });

        var pas1 = "text";
        var pas2 = "text";
        var vb = "visible";

        $(document).on("click", "#show-passwd1", function(){
            $("#password1").attr("type", pas1);
            if(pas1 == "text") pas1 = "password";
            else pas1 = "text";
        });

        $(document).on("click", "#show-passwd2", function(){
            $("#password2").attr("type", pas1);
            if(pas2 == "text") pas2 = "password";
            else pas2 = "text";
        });

        $(document).on("change", "#password1", function(){
            var content = $(this).val();
            if(content.length <= 7){
                $("#err-lb-passwd1").text("Please choose a password with more then 7 characters.");
                $("#err-lb-passwd1").show();
            }
            else if(content != $("#password2").val()){
                $("#err-lb-passwd1").text("The passwords doesn't match");
                $("#err-lb-passwd1").show();
            }
            else $("#err-lb-passwd1").hide();
        });

        $(document).on("change", "#username", function(){
            var content = $(this).val();
            if(content.length <= 0){
                $("#err-lb-username").text("Please choose a username!");
                $("#err-lb-username").show();
            }
            else $("#err-lb-username").hide();
        });

        $(document).on("change", "#email", function(){
            var content = $(this).val();
            if(content.length <= 0){
                $("#err-lb-email").text("Please choose a e-amil address");
                $("#err-lb-email").show();
            }
            else if(content.search("@") < 0){
                $("#err-lb-email").text("Please choose a valid e-mail address");
                $("#err-lb-email").show();
            }
            else $("#err-lb-email").hide();
        });

        $(document).on("click", "#default-img", function(){
            $("#upload-img-input").hide();
        });

        $("#mainCarousel").carousel({
            interval: 2000,
            direction: left
		});
	</script>
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
            <br>
        </div>
    </div>
    <div class="content1 container content">
        <div class="row rowcontentn">
            <div class="col-12 col-md-12 col-sm-12 content-nrm" id="con2" style="margin-top: 10%;">
				<h1>Your files had been generated!</h1>
				<?php echo $link;?>
				<br>
				<a href="#howto" class="btn btn-lg btn-secondary" role="button" aria-expanded="false" aria-controls="howto" data-toggle="collapse">
					How to use it
					<span>
						<i class="fas fa-info"></i>
					</span>
                </a>
                <br>
				<div class="collapse" id="howto">
                    <br>
                    <h1>How to implement the client configurations</h1>
                    <ul>
                       <li>
                           <h3>First thing: What's that file?</h3>
                           <hr>
                           <p>
                               That ZIP file contains the configurations files to
                               your client.
                               <br>
                               The file will have two files:
                                <ul>
                                    <li>
                                        <h4>Client Configurations file (.json)</h4>
                                        <p>
                                            That file contains the main information
                                            about your client, what he have permission
                                            to do, your reference and other data.
                                        </p>
                                    </li>
                                    <li>
                                        <h4>Client Authenticatble file (.lpgp)</h4>
                                        <p>
                                            That file is a LPGP signature file, but
                                            with the client token and access data.
                                            That file is used for tell our servers
                                            that's your client and not a copy.
                                        </p>
                                    </li>
                                </ul>
                           </p>
                       </li>
                       <li>
                           <h3>And how I use it?</h3>
                           <p>
                               To use the files is very simple, you must extract
                               the ZIP file downloaded and then move, or copy,
                               the files to your SDK client downloaded source folder.
                               <br>
                               <hr>
                               <h4>If you don't have a LPGP SDK yet download one!</h4>
                               <a class="btn btn-primary btn-lg" type="button" role="button" href="download-sdks.php">Choose my SDK</a>
                               <hr>
                               <br>
                               To work, the files have specific folders on your source folder to stay.
                               <ul>
                                   <li>
                                       <h4>The configurations file (.json)</h4>
                                       <p>Must be at the folder <b>lib/</b></p>
                                   </li>
                                   <li>
                                       <h4>The authentication file (.lpgp)</h4>
                                       <p>Must be at the folder <b>lib/auth</b></p>
                                   </li>
                               </ul>
                           </p>
                       </li>
                    </ul>
				</div>
            </div>
        </div>
        <hr>
        <div class="row rowcontentn">

        </div>
    </div>
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
</body>
</html>
