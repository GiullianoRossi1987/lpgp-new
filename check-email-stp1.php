<?php
if(session_status() == PHP_SESSION_NONE) session_start();
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
<body>
    <div class="container-fluid header-container" role="banner" style="position: relative;">
        <div class="col-12 header" style="height: 71px">
            <div class="opt-dropdown dropdown login-dropdown">
                <button type="button" class="btn btn-lg default-btn-header dropdown-toggle" data-toggle="dropdown" id="account-opts" aria-haspopup="true" aria-expanded="false">
                    Account
                </button>
                <div class="dropdown-menu opts" aria-labelledby="account-opts"></div>
            </div>
            <div class="opt-dropdown dropdown after-opt signatures-dropdown">
                <button class="dropdown-toggle btn btn-lg default-btn-header" data-toggle="dropdown" aria-expanded="false" aria-haspopup="true" id="signature-opts">
                    Signatures
                </button>
                <div class="dropdown-menu opts" aria-labelledby="signature-opts"></div>
            </div>
            <div class="opt-dropdown dropdown after-opt help-dropdown" style="relative !important;">
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
    <br>

    <div class="container-fluid container-content" style="margin-left: 23%; margin-top: 10%;">
        <div class="row-main row">
            <div class="col-7 clear-content">
                <h1>Check your email <?php echo $_SESSION['user'];?></h1>
                <br>
                <form action="check_email.php" method="post">
                    <label for="code" class="form-label">
                        <h4>Insert your e-mail code</h4>
                    </label>
                    <br>
                    <input type="text" id="code" name="code" placeholder="Your code" class="form-control">
                    <br>
                    <label for="code" class="form-label">
                        <small class="error-label" id="err-lb-code"></small>
                    </label>
                    <br>
                    <div class="button-group default-group">
                        <button class="btn btn-lg btn-secondary" id="bt-resend" type="button">Resend the email</button>
                        <button class="btn btn-lg btn-success" name="bt-code">Submit code</button>
                    </div>
                </form>
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

        $(document).on("change", "#code", function(){
            var content = $(this).val();
            if(content.length <= 0){
                $("#err-lb-code").text("Please insert a valid code!");
                $("#err-lb-code").show();
            }
            else $("#err-lb-code").hide();
        });

        $(document).on("click", "#bt-resend", function(){
            $.post({
                url: "ajx_resend_mail.php",
                data: "resend=t",
                success: function(response){
                    $("#bt-resend").prop("data-toggle", "tooltip");
                    $("#bt-resend").prop("title", "E-mail Sent");
                    $("#bt-resend").tooltip("show");
                },
                error: function(xhr, status, error){ console.error(error); }
            });
        });
    </script>
</body>
</html>
