<?php
if(session_status() == PHP_SESSION_NONE) session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . "/core/Core.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/core/js-handler.php";

use Core\SignaturesData;
use function JSHandler\sendUserLogged;
use const LPGP_CONF;
sendUserLogged();

if(isset($_GET['sig_id'])){
	$sig = new SignaturesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
	$data = $sig->getSignatureData((int)base64_decode($_GET['sig_id']));
	$select_vl = "<select name=\"code\" id=\"code-sel\" class=\"form-control\">";
	$opt = "<option value=\"" . $data['vl_code'] . "\" selected>" . $sig::CODES[$data['vl_code']] . "</option>";
	// others options
	$opts_o = [];
	for($i = 0; $i < count($sig::CODES); $i++){
		if($i != $data['vl_code']){
			$opts_o[] = "<option value=\"" . $i . "\">" . $sig::CODES[$i] . "</option>";
		}
	}
	$hidden = "<input type=\"hidden\" value=\"" . base64_decode($_GET['sig_id']) . "\" name=\"sig_id\">";
	echo "<script> const code = " . $data['vl_code'] . ";</script>";
	$raw_inpt = "<input readonly=\"true\" value=\"" . $data['vl_password'] . "\" id=\"raw_code\" style=\"visibility: hidden;\" class=\"form-control\">";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LPGP Oficial Server</title>
    <link rel="stylesheet" href="../css/new-layout.css">
    <link rel="stylesheet" href="../css/content-style.css">
    <link rel="shortcut icon" href="media/new-logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.14.0/css/all.css" integrity="sha384-HzLeBuhoNPvSl5KYnjx0BT+WB0QEEqLprO+NBkkk5gbc67FTaL7XIGa2w1L0Xbgc" crossorigin="anonymous">
    <link href="../bootstrap/dist/css/bootstrap.css" rel="stylesheet">
</head>
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
                        </div>
                    </div>
                </div>
			</div>
		</div>
	</div>
    <br>
    <hr>
    <div class="container-fluid container-content" style="position: relative;">
        <div class="row-main row">
            <div class="col-7 clear-content" style="position: relative; margin-left: 21%; margin-top: 10%;">
				<form action="./change_signature_data.php" method="post" class="form-group">
                    <label for="passcode" class="form-label">The code</label>
					<br>
					<input type="password" name="passcode" id="passcode" class="form-control">
					<label for="passcode" class="form-label">
						<small>If you don't want to change the code, then just leave the field empty</small>
					</label>
					<br>
					<label for="passcode" class="form-label">
						<button type="button" class="btn btn-sm btn-secondary" id="show-code">Show the code</button>
					</label>
					<br>
					<?php
						echo $select_vl;
						echo $opt;
						foreach($opts_o as $o) echo $o;
						echo "</select>";
						echo "<br>";
						echo $raw_inpt;
					?>
					<br>
					<label for="raw_code" class="form-label">
						<button type="button" class="btn btn-secondary" id="see-raw">See raw key</button>
					</label>
					<br>
					<input type="password" name="conf-pass" id="confirm-passcode" class="form-control" placeholder="Confirm the passcode" style="visibility: hidden;">
					<button type="submit" class="btn btn-lg btn-success" name="save-btn">Save changes</button>
					<button type="button" class="btn btn-lg btn-secondary" name="cancel-btn">Cancel</button>
					<button class="btn btn-lg btn-danger" type="submit" name="rm-btn">Delete signature</button>
					<br>
					<?php echo $hidden; ?>
				</form>
            </div>
		</div>
	</div>
    <br>
    <div class="footer-container container">
        <div class="footer-row row">
            <div class="footer col-12" style="height: 150px; background-color: black; margin-top: 190%; position: relative !important; max-width: 100%; left: 0;">
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
    <script src="../jquery/lib/jquery-3.4.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="../bootstrap/dist/js/bootstrap.js"></script>
    <script src="../js/autoload.js" charset="utf-8"></script>
    <script src="../js/main-script.js"></script>
    <script src="../js/actions.js"></script>
    <script>
        $(document).ready(function(){
            setAccountOpts(true);
            setSignatureOpts();
        });

        var pas1 = "text";
        var pas2 = "text";
        var vb = "visible";

        $(document).on("click", "#show-passwd1", function(){
            $("#password1").attr("type", pas1);
            if(pas1 == "text") pas1 = "password";
            else pas1 = "text";
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

		$(document).on("change", "#code-sel", function(){
			if($("#code-sel").val() != code){
				$("#confirm-passcode").css("visibility", "visible");
			}
			else $("#confirm-passcode").css("visibility", "hidden");
		});

		$(document).on("click", "#see-raw", function(){
			if($("#raw_code").css("visibility") == "hidden"){
				$("#raw_code").css("visibility", "visible");
			}
			else $("#raw_code").css("visibility", "hidden");
		});

        $(document).scroll(function(){
            $(".header-container").toggleClass("scrolled", $(this).scrollTop() > $(".header-container").height());
            $(".default-btn-header").toggleClass("default-btn-header-scrolled", $(this).scrollTop() > $(".header-container").height());
            $(".opts").toggleClass("opts-scrolled", $(this).scrollTop() > $(".header-container").height());
        });
    </script>
</body>
</html>
