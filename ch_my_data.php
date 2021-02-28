<?php
if(session_status() == PHP_SESSION_NONE) session_start();
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/js-handler.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Exceptions.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/users-data.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/proprietaries-data.php";

use Core\UsersData;
use Core\ProprietariesData;

$error_msg = "";
$err = false;

if($_COOKIE['mode'] == "prop"){
	$prp = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
	$data = $prp->getPropData($_COOKIE['user']);
}
else{
	$usr = new UsersData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
	$data = $usr->getUserData($_COOKIE['user']);
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
	#avatar-ep{
		border-radius: 50%;
		border: 5px solid black;
		padding: 20px 21px;
		background-color: gray;
		background-size: cover;
		background-repeat: no-repeat;
		width: 300px;
		height: 300px;
	}

	#avatar-container{
		padding: 23px 24px;
		background: linear-gradient(to bottom, black, grey, white, #fdff00);
		width: 350px;
		height: 350px;
		align-items: center;
		margin-left: 33.3%;
		border-radius: 50%;
		cursor: pointer;
	}

	#avatar-container:hover{
		z-index: 99;
		opacity: .67;
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
                            <a href="https://www.lpgpofficial.com/docs/" class="dropdown-item">Documentation</a>
                            <a href="https://www.lpgpofficial.com/about.html" class="dropdown-item">About Us</a>
                            <a href="https://www.lpgpofficial.com/contact-us.html" class="dropdown-item">Contact Us</a>
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
				<form method="post" class="form-group" enctype="multipart/form-data" id="form">
                    <h1>Your configurations</h1>
                    <!-- <h6>If you don't want to change a field just leave it empty</h6> -->
                    <br>
					<div id="avatar-container" data-toggle="tooltip" title="Change Avatar">
						<div id="avatar-ep">
						</div>
					</div>
					<br>
					<label for="new-img" class="form-label">Change the profile image</label>
					<br>
					<input type="file" name="new-img[]" id="new-img" class="form-group" accept="image/*">
					<br>
                    <label for="username" class="form-label">Change your username</label>
                    <br>
					<input type="text" name="new-name" id="username" class="form-control">
					<br>
                    <label for="email" class="form-label">Change your email</label>
                    <br>
					<input type="email" name="new-email" id="email" class="form-control">
					<br>
                    <label for="passwd1" class="form-label">Change your password</label>
                    <br>
					<div class="input-group input-group-inline pass1-grp">
						<input type="password" id="passwd1" name="new-passwd" class="form-control">
						<div class="input-group-append">
							<button type="button" class="btn btn-md btn-secondary" id="spass1">
								<span><i class="fas fa-eye"></i></span>
							</button>
						</div>
					</div>
					<br>
                    <label for="passwd2" class="form-label">Confirm the new password</label>
                    <br>
					<div class="input-group input-group-inline">
						<input type="password" name="passwd-confirm" id="passwd2" class="form-control">
						<div class="input-group-append">
							<button type="button" class="btn btn-md btn-secondary" id="spass2">
								<span>
									<i class="fas fa-eye"></i>
								</span>
							</button>
						</div>
					</div>
					<br>
					<button class="btn btn-lg btn-success" type="button" id="save">Save configurations</button>
					<button class="btn btn-lg btn-secondary" type="submit" onclick="window.location.replace('./my_account.html');">Cancel</button>
					<button type="button" id="reset" class="btn btn-lg btn-warning">Restore default</button>
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
            $("#avatar-ep").css("background-image", "url(" + getLinkedUserIcon() + ")");
        });

        var pas1 = "text";
        var pas2 = "text";
        var vb = "visible";
		var dp_tmp = false;

        $(document).on("click", "#spass1", function(){
            $("#passwd1").attr("type", pas1);
            if(pas1 == "text") pas1 = "password";
            else pas1 = "text";
        });

        $(document).on("click", "#spass2", function(){
            $("#passwd2").attr("type", pas1);
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

		$(document).on("click", "#reset", function(){
			window.location.reload();
		});

		// ajax inputs
		$(document).on("change", "#new-img", function(){
			let files = new FormData();
			files.append("img-auto-load", $("#new-img")[0].files[0]);
			// testing
			console.log(files);
			$.post({
				url: "ajx_img_viewer.php",
				data: files,
				processData: false,
				contentType: false,
				success: function(response){
					$("#avatar-ep").css("background-image", "url(" + response + ")");
				},
				error: function(xhr, status, error){ console.log(error); }
			});
		});

		// send the data using ajx
		$(document).on("click", "#save", function(){
			var newData = new FormData();
			if($("#new-img")[0].files[0] !== undefined)
				newData.append("new-img", "/media/tmp/" + $("#new-img")[0].files[0]["name"]);
			if(dp_tmp) newData.append("dp_tmp_media", true);
			if($("#username").val().length > 0)
				newData.append("new-name", $("#username").val());
			if($("#email").val().length > 0)
				newData.append("new-email", $("#email").val());
			if($("#passwd1").val().length > 0 && $("#passwd2").val().length > 0)
				newData.append("new-passwd", $("passwd1").val());
            for(var i of newData) console.log(i);
            // AJAX part
            // var teste = new FormData($("#form")[0]);
            // for(var i of teste) console.log(i);
            $.post({
                url: "ajx_ch_account.php",
                data: newData,
                processData: false,
                contentType: false,
                success: function(response){
                    console.log(response)
                    if(response == "success"){
                        readCookies();
                        alert("Account data changed");
                        window.location.replace("my_account.html");
                    }
                    else console.error("ERROR: " + response);
                },
                error: function(error){
                    conole.error(error);
                }
            });
		});

        $(document).ready(function(){
            console.log(swp_cookies);
            if(swp_cookies["mode"] == "prop"){
                $.post({
                    url: "ajx_prop.php",
                    data: {get: JSON.stringify({"nm_proprietary": swp_cookies["user"]})},
                    dataType: "json",
                    success: function(data){
                        console.log(data);
                        $("#username").val(data[0]["nm_proprietary"]);
                        $("#email").val(data[0]["vl_email"]);
                        $("#passwd1").val(data[0]["vl_passwd"]);
                    },
                    error: function(error){ console.log(error); }
                });
            }
            else{
                $.post({
                    url: "ajx_users.php",
                    data: {get: JSON.stringify({"nm_user": swp_cookies["user"]})},
                    dataType: "json",
                    success: function(data){
                        console.log(data);
                    },
                    error: function(error){ console.log(error); }
                });
            }
        });

		$(document).on("click", "#avatar-container", function(){ $("#opt-avatar").collapse("toggle");})
    </script>
</body>
</html>
