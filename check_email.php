<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/core/Core.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/core/js-handler.php";
if(session_status() == PHP_SESSION_NONE) session_start();

use Core\UsersData;
use Core\ProprietariesData;
use function JSHandler\sendUserLogged;
use const LPGP_CONF;

sendUserLogged();
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
    <link href="../bootstrap/dist/css/bootstrap.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid header-container" role="banner">
        <div class="col-12 header" style="height: 71px">
            <div class="opt-dropdown dropdown login-dropdown">
                <button type="button" class="btn btn-lg default-btn-header dropdown-toggle" data-toggle="dropdown" id="account-opts" aria-haspopup="true" aria-expanded="false">
                    <span id="nm-tmp">Account</span>
                </button>
                <div class="dropdown-menu opts" aria-labelledby="account-opts"></div>
            </div>
            <div class="opt-dropdown dropdown after-opt signatures-dropdown">
                <button class="dropdown-toggle btn btn-lg default-btn-header" data-toggle="dropdown" aria-expanded="false" aria-haspopup="true" id="signature-opts">
                    Signatures
                </button>
                <div class="dropdown-menu opts" aria-labelledby="signature-opts"></div>
            </div>
            <div class="opt-dropdown dropdown after-opt help-dropdown" >
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

    <div class="container-fluid container-content" style="position: relative">
        <div class="row-main row">
            <div class="col-12 clear-content center-content" style="position: relative;">
            <?php
else if(isset($_POST['bt-code'])){
    if($_SESSION['mode'] == "normie"){
        $usr = new UsersData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
        if($usr->authUserKey($_SESSION['user'], $_POST['code'])){
            $usr->setUserChecked($_SESSION['user'], true);
            echo "<script>window.location.replace(\"https://localhost/\");</script>";
        }
        else{
            echo "<script>showError(\"Invalid Code!\");</script>";
            echo "<button class=\"darkble-btn btn default-btn\" onclick=\"window.location.replace('https://localhost/check-email-stp1.php');\">Try again!</button>";
        }
    }
    else{
        $prop = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
        if($prop->authPropKey($_SESSION['user'], $_POST['code'])){
            $prop->setProprietaryChecked($_SESSION['user'], true);
            echo "<script>window.location.replace(\"https://localhost\");</script>";
        }
        else{
            echo "<script>showError(\"Invalid Code\");</script>";
            echo "<h1>Error</h1>";
            echo "<button class=\"darkble-btn btn default-btn\" onclick=\"window.location.replace('https://localhost/check-email-stp1.php');\">Return</button>";
        }
    }
}
else{
    echo "Error";
}
?>
            </div>
        </div>
    </div>

    <div class="container-fluid container-content" style="position: relative;margin-left: 23%;">
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
                        <button class="btn btn-lg btn-secondary" name="btn-resend">Resend the email</button>
                        <button class="btn btn-lg btn-success" name="bt-code">Submit code</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Scripts -->
    <script src="../jquery/lib/jquery-3.4.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="../bootstrap/dist/js/bootstrap.js"></script>
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
    </script>
</body>
</html>
