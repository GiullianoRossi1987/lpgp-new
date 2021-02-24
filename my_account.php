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
    <link rel="stylesheet" href="css/new-features.css">
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
    <div class="test-cover" style="visibility: hidden"></div>
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
                                        <h1 id="username-ttl"></h1>
                                        <h3 id="email-ttl"></h3>
                                        <h3 id="date-creation-ttl"></h3>
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
                                                <!-- Modal -->
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
                            <div class="signatures-col col-12" id="signatures-col">
                                <a href="#signatures-section" class="account-separator" id="signature-sep" aria-controls="signatures-section" aria-expanded="false" data-toggle="collapse">
                                    <div class="content"><h2 class="mainheader-heading mb-0">My Signatures</h2></div>
                                </a>
                                <div id="signatures-section" class="collapse section"></div>
                            </div>
                            <div class="history-col col-12" style="position: relative; margin-top: 10%;">
                                <a class="account-separator" href="#history-section" data-toggle="collapse" aria-expanded="false" aria-controls="history-section" id="history-sep">
                                    <div class="content">
                                        <h2>
                                            My History <a href="my-history.php" role="button" class="btn btn-primary" data-toggle="tooltip" title="See all the history">
                                                <span>
                                                    <i class="fas fa-history"></i>
                                                </span>
                                            </a>
                                        </h2>
                                    </div>
                                </a>

                                <div class="collapse section" id="history-section">
                                </div>
                            </div>
                            <div class="col-12 clients-col" style="margin-top: 10%;">
                                <a href="#clients-section" class="account-separator" data-toggle="collapse" aria-controls="clients-section" aria-expanded="false" id="client-sep">
                                    <div class="content">
                                        <h2>
                                            My Clients
                                        </h2>
                                    </div>
                                </a>
                                <div class="collapse section" id="clients-section"></div>
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

    <div class="relatory-modal modal fade" tabindex="-1" aria-hidden="true" id="rel-modal">
        <div class="modal-dialog" role="dialog">
            <div class="modal-content">
                <div class="modal-body" id="relatory-dispose">

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" aria-hidden="true" id="dsm-modal">
        <div class="modal-dialog" role="dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 id="title-dsm" class="modal-title"></h1>
                    <button type="button" data-dismiss="modal" class="btn">&times;</button>
                </div>
                <div class="modal-body">
                    <a href="#" id="alink-dsm" download="" role="button" class="btn btn-block btn-success"></a>
                </div>
                <div class="modal-footer">
                    <a href="send_report.html">
                        <span class="fas fa-exclamation-triangle"></span>
                        Report an error
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" aria-hidden="true" id="dcm-modal">
        <div class="modal-dialog" role="dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 id="title-dcm" class="modal-title"></h1>
                    <button type="button" data-dismiss="modal" class="btn">&times;</button>
                </div>
                <div class="modal-body">
                    <a href="#" id="alink-dcm" download="" role="button" class="btn btn-block btn-success"></a>
                </div>
                <div class="modal-footer">
                    <a href="send_report.html">
                        <span class="fas fa-exclamation-triangle"></span>
                        Report an error
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" aria-hidden="true" id="csm-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title" id="title-csm"></h1>
                    <button type="button" data-dismiss="modal" class="btn">&times;</button>
                </div>
                <div class="modal-body">
                    <form class="form" id="form-csm">
                        <div class="form-group" >
                            <input type="hidden" name="signature-to" value="" id="csm-signature-to">
                            <br>
                            <div class="row from-group">
                                <label for="csm-passcode" class="form-label col-6">The passcode</label>
                                <input type="password" name="passcode" value="" class="form-control col-6" id="csm-passcode">
                            </div>
                            <br>
                            <div class="from-group row">
                                <label for="csm-confirm" class="form-label col-6">Confirm the code</label>
                                <input type="password" name="conf-passcode" value="" class="form-control col-6" id="csm-confirm">
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary" id="csm-show-code">Show the code</button>
                            <small class="input-group-append">If you don't want to change the passcode, leave it empty</small>
                            <br>
                            <div class="row form-group">
                                <label for="csm-codes" class="form-label col-6">Select the hash code</label>
                                <select class="form-control col-6" name="code-sel" id="csm-codes"></select>
                            </div>
                            <small>If you change the code, please confirm the password</small>
                            <br>
                            <button type="button" name="btn-tgl" data-toggle="collapse" data-target="#show-hash" aria-controls="show-hash" class="btn btn-primary">Show hased code</button>
                            <div class="collapse input-group input-group-inline" id="show-hash">
                                <label for="csm-hashed" class="form-label input-group-prepend">Hash encoded code</label>
                                <input type="text" value="" id="csm-hased" readonly class="form-control">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <button type="button" data-toggle="collapse" data-target="#confirm-coll" class="btn btn-success btn-lg" aria-controls="confirm-coll">
                            Save changes
                        </button>
                        <button type="button" data-dismiss="modal" class="btn btn-secondary btn-lg">Discard</button>
                    </div>
                    <div class="collapse row confirm-row" id="confirm-coll">
                        <h4 class="col-12">Confirm the changes?</h4>
                        <button type="button" id="csm-save" class="btn btn-success btn-lg col-6">Confirm and save</button>
                        <button type="button" data-toggle="collapse" data-target="#confirm-coll" aria-controls="confirm-coll" class="btn btn-danger btn-lg col-6">
                            Don't confirm
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="ccm-modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title" id="ccm-title"></h1>
                    <button type="button" class="btn" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form class="form">
                        <input type="hidden" id="ccm-client" value="" class="form-control">
                        <div class="form-group">
                            <div class="row">
                                <label for="ccm-name" class="form-label col-6">Client name</label>
                                <input type="text" id="ccm-name" value="" class="form-control col-6">
                            </div>
                            <div class="row">
                                <label for="ccm-token" class="form-label col-6">Client Token</label>
                                <input type="text" id="ccm-token" value="" readonly class="form-control col-6">
                            </div>
                            <div class="row">
                                <label for="ccm-permissions" class="form-label col-4">Client Permissions</label>
                                <div class="col-8 input-group input-group-inline">
                                    <select class="form-control col-6" id="ccm-permissions">
                                        <option value="0">Normal</option>
                                        <option value="1">Root</option>
                                    </select>
                                    <button type="button" class="btn btn-sm input-group-append" data-toggle="collapse" data-target="#ccm-help" aria-controls="ccm-help">
                                        <span class="fas fa-question-circle"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" data-toggle="collapse" data-target="#conf-coll" aria-controls="conf-coll" aria-expanded="false" class="btn btn-lg btn-success">
                        Save
                    </button>
                    <br>
                    <button type="button" data-dismiss="modal" class="btn btn-lg btn-secondary">Discard</button>
                    <div class="row collapse" id="conf-coll">
                        <button type="button" class="btn btn-lg btn-success" id="ccm-save">Confirm & Save</button>
                        <button type="button" data-toggle="collapse" data-target="#conf-coll" aria-controls="conf-coll" class="btn btn-lg btn-danger">
                            Don't Save
                        </button>
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
    <script src="./js/generator.js"></script>
    <script>
        var csm_data = {};
        var csm_error = false;

        $(document).ready(function(){
            setAccountOpts(true);
            setSignatureOpts();
            $.post({
                url: "ajx_signatures.php",
                data: {"get-opts": true},
                dataType: "json",
                success: function(resp){
                    resp.forEach((item, i) => {
                        var opt = document.createElement("option");
                        opt.value = i;
                        opt.innerText = item;
                        $("#csm-codes").append(opt);
                    });
                },
                error: function(error){ alert(error); }
            });
        });

        $(document).on("change keyup keydown", "#csm-passcode #csm-confirm", function(){
            if($(this).val() != $("#passcode").val() && $("#passcode").val().length > 0){
                err_pc = true;
                $("#csm-passcode").addClass("field-error");
                $("#csm-confirm").addClass("field-error");
            }
            else{
                err_pc = false;
                $("#csm-passcode").removeClass("field-error");
                $("#csm-confirm").removeClass("field-error");
            }
        });

        $(document).on("click", ".account-separator .content", function(){
            $(this).toggleClass("selected-separator");
        });

        $(document).on("click", ".relatory-mt", function(){
            var data;
            if($(this).data("mode") == "prop"){
                $.post({
                    url: "ajx_prp_history.php",
                    data: {get: JSON.stringify({"cd_reg": $(this).data("reg")})},
                    dataType: "json",
                    async: false,
                    success: function(resp){ data = resp; },
                    error: function(error){ console.error(error)}
                });
            }
            else{
                $.post({
                    url: "ajx_usr_history.php",
                    data: {get: JSON.stringify({"cd_reg": $(this).data("reg")})},
                    dataType: "json",
                    async: false,
                    success: function(resp){ data = resp; },
                    error: function(error){ console.error(error)}
                });
            }
            console.log(data);
            $("#relatory-dispose").empty();
            genRelatoryCard(data[0], "relatory-dispose");
            $("#rel-modal").modal("show");
        });

        $(document).ready(function(){
            if(swp_cookies.mode == "prop"){
                var data = {};
                $("#signature-sep").css("visibility", "visible");
                $.post({
                    url: "ajx_prop.php",
                    data: {get: JSON.stringify({"nm_proprietary": swp_cookies["user"]})},
                    dataType: 'json',
                    beforeSend: function(xhr){
                        // console.log("waiting");
                        $(".test-cover").css("visibility", "visible");
                    },
                    success: function(resp){
                        // console.log(resp);
                        data = resp;
                        setTimeout(function(){ $(".test-cover").css("visibility", "hidden"); }, 3600);
                        $("#username-ttl").html(resp[0]["nm_proprietary"] + "<span class=\"badge badge-success badge-account\">Proprietary</span>");
                        $("#email-ttl").text(resp[0]["vl_email"]);
                        $("#date-creation-ttl").text(resp[0]["dt_creation"]);
                        $("#img-user").css("background-image", "url(" + getLinkedUserIcon() + ")");
                    },
                    error: function(error){ alert(error); }
                });
                $.post({
                    url: "ajx_signatures.php",
                    data: {get: JSON.stringify({id_proprietary: data["cd_proprietary"]})},
                    dataType: "json",
                    success: function(resp){
                        for(var i = 0; i < 5; ++i){
                            if(i > resp.length || resp[i] == undefined) break;
                            else genSignatureCard(resp[i], "signatures-section");
                        }
                    },
                    error: function(xhr, status, error){ console.error(error); }
                });
                $.post({
                    url: "ajx_clients.php",
                    data: {get: JSON.stringify({id_proprietary: data["cd_proprietary"]}), acesses: true},
                    dataType: "json",
                    success: function(resp){
                        console.log(data);
                        for(var i = 0; i < 5; ++i){
                            // DEBUG: console.log(resp[i]);
                            if(i > resp.length || resp[i] == undefined) break;
                            else genClientCard(resp[i], "clients-section");
                        }
                    },
                    error: function(xhr, status, error){ alert(error); }
                });
                $.post({
                    url: "ajx_prp_history.php",
                    data: {get: JSON.stringify({id_proprietary: data["cd_proprietary"]})},
                    dataType: "json",
                    success: function(resp){
                        console.log(resp);
                        for(var i = 0; i < 5; ++i){
                            console.log(resp[i]);
                            if(i > resp.length || resp[i] == undefined) break;
                            else genHistoryCard_p(resp[i], "history-section");
                        }
                    },
                    error: function(xhr, status, error){ alert(xhr); }
                })
            }
            else{
                $("#signature-sep").css("visibility", "hidden");
                $("#clients-sep").css("visibility", "hidden");
                $.post({
                    url: "ajx_user.php",
                    data: {get: JSON.stringify({"nm_user": swp_cookies["user"]})},
                    dataType: 'json',
                    beforeSend: function(xhr){
                        // DEBUG: console.log("waiting");
                        $(".test-cover").css("visibility", "visible");
                    },
                    success: function(resp){
                        // DEBUG: console.log(resp);
                        $("#img-user").attr("src", getLinkedUserIcon());
                    },
                    error: function(error){ alert(error); }
                });
            }
        });

        $(document).on("click", ".dsm-trigger", function(){
            var id = atob($(this).data("id"));
            $.post({
                url: "ajx_signatures.php",
                data: {download: id},
                dataType: "json",
                success: function(resp){
                    $("#title-dsm").text("Download signature #" + id);
                    $("#alink-dsm").text("Download signature #" + id);
                    $("#alink-dsm").attr("href", resp["path"]);
                    $("#alink-dsm").attr("download", "signature_" + id + ".lpgp");
                    $("#dsm-modal").modal("show");
                },
                error: function(error){ console.error(error); }
            });
        });

        $(document).on("click", ".dcm-trigger", function(){
            var id = atob($(this).data("id"));
            $.post({
                url: "ajx_clients.php",
                data: {download: id},
                dataType: "json",
                success: function(resp){
                    $("#title-dcm").text("Download client #" + id);
                    $("#alink-dcm").text("Download client #" + id);
                    $("#alink-dcm").attr("href", resp["path"]);
                    $("#alink-dcm").attr("download", "Client_" + id + ".lpgp");
                    $("#dcm-modal").modal("show");
                },
                error: function(error){ console.error(error); }
            });
        });

        $(document).on("click", ".csm-trigger", function(){
            var id = atob($(this).data("id"));
            // var sig_data = {};
            $("#csm-signature-to").val(id);
            $.post({
                url: "ajx_signatures.php",
                data: {get: JSON.stringify({cd_signature: $("#sig_id").val()})},
                dataType: "json",
                async: false,
                success: function(resp){
                    csm_data = resp[0];
                },
                error: function(error){ alert(error); }
            });
            $("#csm-hased").val(csm_data["vl_password"]);
            $("#csm-codes").val(csm_data["vl_code"]);
            $("#title-csm").text("Configurations of signature #" + id);
            $("#csm-modal").modal("show");
        });

        $(document).on("click", "#csm-save", function(){
            var to_save = {};
            if($("#csm-passcode").val().length > 0 && !csm_error){
                to_save["vl_password"] = $("#csm-passcode").val();
            }
            if($("#csm-codes").val() != csm_data["vl_code"] && !csm_error){
                 to_save["vl_code"] = parseInt($("#csm-codes").val());
            }
            console.log(to_save);
            $.post({
                url: "ajx_signatures.php",
                data: {change: JSON.stringify(to_save), "signature": $("#csm-signature-to").val()},
                dataType: "json",
                success: function(resp){
                    $("#csm-modal").modal("hide");
                },
                error: function(error){ console.error(error); }
            });
        });

        $(document).on("click", ".ccm-trigger", function(){
            var id = atob($(this).data("id"));
            $.post({
                url: "ajx_clients.php",
                data: {get: JSON.stringify({"cd_client": id})},
                dataType: "json",
                async: false,
                success: function(resp){
                    // DEBUG: console.log(resp[0]);
                    $("#ccm-title").text("Configurations of client #" + id);
                    $("#ccm-client").val(id);
                    $("#ccm-name").val(resp[0]["nm_client"]);
                    $("#ccm-token").val(resp[0]["tk_client"]);
                    $("#ccm-permissions").val(resp[0]["vl_root"]);
                    $("#ccm-modal").modal("show");
                },
                error: function(error){ console.error(error);}
            });
        });

        $(document).on("click", "#ccm-save", function(){
            var data = {
                "nm_client": $("#ccm-name").val(),
                "vl_permissions": $("#ccm-permissions").val()
            };
            $.post({
                url: "ajx_clients.php",
                data: {update: JSON.stringify(data), client: $("#ccm-client").val()},
                dataType: "json",
                async: false,
                success: function(resp){
                    if(resp[0] == 0){
                        $("#ccm-modal").modal("hide");
                    }
                },
                error: function(error){ console.error(error); }
            });
        });
    </script>
</body>
</html>
