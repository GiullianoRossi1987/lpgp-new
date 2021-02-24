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
<body>
    <!-- TODO: Try to change this from page to modal -->
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
				<form method="post" class="form-group">
                    <?php if(isset($_GET["sig_id"])) echo "<input type=\"hidden\" value=\"" . base64_decode($_GET['sig_id']) . "\" name=\"sig_id\" id=\"sig_id\">"; ?>
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
                    <input type="password" name="conf-pass" id="confirm-passcode" class="form-control" placeholder="Confirm the passcode" style="visibility: hidden;">
					<br>
                    <select class="form-control" name="opt" id="sel_opt"></select><small>If you change the code, please confirm the password</small>
					<br>
					<label for="raw_code" class="form-label">
						<button type="button" class="btn btn-secondary" id="see-raw" class="form-control">See raw key</button>
					</label>
                    <div class="input-group input-group-inline" id="raw_grp" style="visibility: hidden">
                        <input type="text" name="raw_code" value="" id="raw_code" class="form-control" readonly>
                        <button class="btn btn-primary clipboard-btn input-group-append" id="cp-raw-code" type="button" data-toggle="tooltip" title="copy to clipboard">
                            <span class="fas fa-copy"></span>
                        </button>
                    </div>
					<br>
					<button type="button" class="btn btn-lg btn-success" name="save-btn" id="btn-save">Save changes</button>
					<button class="btn btn-lg btn-danger" type="button" name="rm-btn" id="btn-rm">Delete signature</button>
                    <button type="button" class="btn btn-lg btn-secondary" name="cancel-btn" id="btn-cnc">Cancel</button>
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
    <script src="jquery/lib/jquery-3.4.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="bootstrap/dist/js/bootstrap.js"></script>
    <script src="js/autoload.js" charset="utf-8"></script>
    <script src="js/main-script.js"></script>
    <script src="js/actions.js"></script>
    <script>
        var sig_data = {};
        var err_pc = false;
        $(document).ready(function(){
            setAccountOpts(true);
            setSignatureOpts();

            // loads the signature data
            $.post({
                url: "ajx_signatures.php",
                data: {get: JSON.stringify({cd_signature: $("#sig_id").val()})},
                dataType: "json",
                success: function(resp){
                    sig_data = resp[0];
                    $("#raw_code").val(sig_data["vl_password"]);

                },
                error: function(error){ alert(error); }
            });

            $.post({
                url: "ajx_signatures.php",
                data: {"get-opts": true},
                dataType: "json",
                success: function(resp){
                    resp.forEach((item, i) => {
                        var opt = document.createElement("option");
                        opt.value = i;
                        opt.innerText = item;
                        $("#sel_opt").append(opt);
                    });
                    setTimeout(function(){$("#sel_opt").val(sig_data["vl_code"]);}, 200);
                },
                error: function(error){ alert(error); }
            });
        });

        var pas1 = "text";
        var pas2 = "text";
        var vb = "visible";

        $(document).on("click", "#show-code", function(){
            $("#passcode").attr("type", pas1);
            if(pas1 == "text") pas1 = "password";
            else pas1 = "text";
        });

        $(document).on("change keyup keydown", "#passcode", function(){
            if($("#passcode").val().length > 0){
				$("#confirm-passcode").css("visibility", "visible");
			}
			else $("#confirm-passcode").css("visibility", "hidden");
        });

		$(document).on("change", "#sel_opt", function(){
			if($(this).val() != sig_data["vl_code"]){
				$("#confirm-passcode").css("visibility", "visible");
			}
			else $("#confirm-passcode").css("visibility", "hidden");
		});

		$(document).on("click", "#see-raw", function(){
			if($("#raw_grp").css("visibility") == "hidden"){
				$("#raw_grp").css("visibility", "visible");
			}
			else $("#raw_grp").css("visibility", "hidden");
		});

        $(document).on("click", "#cp-raw-code", function(){
            var raw_code = document.getElementById("raw_code");
            raw_code.select();
            raw_code.setSelectionRange(0, 999999);
            document.execCommand("copy");
            $("#cp-raw-code").attr("title", "Copied UwU");
            $("#cp-raw-code").tooltip("show");
        });

        $(document).on("change", "#confirm-passcode", function(){
            if($(this).val() != $("#passcode").val() && $("#passcode").val().length > 0){
                err_pc = true;
                alert("The passwords aren't the same!");  // use alerts for now
                // TODO: Implement the new modal alerts
            }
            else err_pc = false;
        });

        $(document).on("click", "#btn-save", function(){
            var to_save = {};
            if($("#passcode").val().length > 0){
                if(!err_pc) to_save["vl_password"] = $("#passcode").val();
            }
            if($("#sel_opt").val() != sig_data["vl_code"]){
                 if(!err_pc) to_save["vl_code"] = parseInt($("#sel_opt").val());
            }
            console.log(to_save);
            $.post({
                url: "ajx_signatures.php",
                data: {change: JSON.stringify(to_save), "signature": $("#sig_id").val()},
                dataType: "json",
                success: function(resp){
                    // console.log(resp);
                    alert("Data changed"); // TODO: change the alert methods by the modal creator method
                    setTimeout(function(){window.location.replace("my_account.html");}, 200);
                },
                error: function(error){ console.error(error); }
            })
        });



    </script>
</body>
</html>
