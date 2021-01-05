<?php
if(session_status() == PHP_SESSION_NONE) session_start();

require_once "core/Core.php";
require_once "core/js-handler.php";
require_once "core/proprietaries-data.php";

use Core\ProprietariesData;
use function JSHandler\lsExtSignatures;
use function JSHandler\getImgPath;
use function JSHandler\sendUserLogged;
use const LPGP_CONF;

sendUserLogged();  // Just for fixing a error that i don't know why is going on.
$prp = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
if(isset($_GET['id'])) $data = $prp->getPropDataByID(base64_decode($_GET['id']));
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
    <link rel="stylesheet" href="bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/font-awesome.min.css">
    <script src="bootstrap/jquery-3.3.1.slim.min.js"></script>
    <script src="bootstrap/bootstrap.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <link rel="shortcut icon" href="../media/new-logo.png" type="image/x-icon">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.2/popper.min.js"></script>
</head>
<style>
</style>
<body>
    <script>
        $(document).ready(function(){
            setAccountOpts(true);
            setSignatureOpts();
        });

        $(document).scroll(function(){
            $(".header-container").toggleClass("scrolled", $(this).scrollTop() > $(".header-container").height());
            $(".default-btn-header").toggleClass("default-btn-header-scrolled", $(this).scrollTop() > $(".header-container").height());
            $(".opts").toggleClass("opts-scrolled", $(this).scrollTop() > $(".header-container").height());
        });
    </script>
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
                    <a href="../docs/index.php" class="dropdown-item">Documentation</a>
                    <a href="../about.html" class="dropdown-item">About Us</a>
                    <a href="../contact-us.html" class="dropdown-item">Contact Us</a>
                </div>
            </div>
        </div>

    </div>
    <br>
    <hr>
    <div class="container-fluid container-content" style="position: relative;">
        <div class="row-main row">
            <div class="col-7 clear-content" style="position: relative; margin-left: 21%; margin-top: 10%;">
                <div class="prop-main-data-container container">
					<div class="data-row row">
						<div class="col-12 prop-data">
                        <div class="container data-container">
                                <div class="main-row row">
                                    <div class="img-cont">
                                        <?php
                                        $img_src = getImgPath($data['vl_img'], true);
                                        echo "<img src=\"$img_src\" alt=\"\" width=\"200px\" height=\"200px\">";
                                        ?>
                                    </div>
                                    <div class="col-6 data">
                                        <?php

                                        echo "<h1 class=\"user-name\"> " . $data['nm_proprietary'] . "</h1>\n";
                                        echo "<h4 class=\"mode\">Proprietary</h4>\n";
                                        echo "<h4 class=\"email\">Email: " . $data['vl_email'] . "</h3>\n";
                                        echo "<h5 class=\"date-creation\">Date of creation: " . $data['dt_creation'] . "</h3>\n";
                                        ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="others-col col-12">
                        <?php
                            // Signatures
                            ////////////////////////////////////////////////////////////////////////////////////////////////
                            $nm = $data['nm_proprietary'];
                            echo "<h1 class=\"section-title\">Signatures of $nm</h1><br>";
                            $prp = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
                            echo lsExtSignatures($_GET['id']);
                        ?>
                        </div>
						</div>
					</div>
				</div>
        </div>
            </div>
        </div>
    </div>
    <br>
    <div class="footer-container container">
        <div class="footer-row row">
            <div class="footer col-12"  style="height: 150px; background-color: black; margin-top: 100%; position: relative; max-width: 100%; left: 0;">
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
</body>
</html>
