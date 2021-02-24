<?php
require_once "core/Core.php";
require_once "core/clients-data.php";

use Core\ClientsData;
use const LPGP_CONF;

if(isset($_GET['client'])){
	$cl_id = (int)base64_decode($_GET['client']);
	$obj = new ClientsData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
	$cl_dt = $obj->getClientData((int)$cl_id);

	$name_ip = '<input type="text" name="client-name" id="cl-nm" class="form-control al" value="' . $cl_dt['nm_client'] .'">';
	$tk_ip = '<input type="text" name="client-tk" id="cl-tk" class="form-control al" value="' . $cl_dt['tk_client'] . '" readonly>';
	$sel = '<select class="form-control al" id="permissions-sel" name="permissions">';
	if($cl_dt['vl_root'] == 1){
        $opts = '<option value="1" selected>Root</option>' . '<option value="0">Normal</option>';
    }
    else{
        $opts = '<option value="1">Root</option>' . '<option value="0" selected>Normal</option>';
    }
	$sel .= $opts .  "</select>";
	$id = '<input type="hidden" name="client" value="' . base64_decode($_GET['client']) . '">';
	$del = '<a class="btn btn-lg btn-danger" role="button" type="button" href="rm-client.php?client=' . $_GET['client'] . '">Delete this client</a>';
	$modalLink = '<a href="client-data.php?client=' . base64_decode($_GET['client']) .'" role="button" class="btn btn-lg btn-success" type="button">
						Click here to download the new authentication file.</a>';
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
<body>
	<?php
	if(isset($_GET['alert'])){
		echo "<script>show=true</script>";
	}
	?>
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
	<br>
	<div class="container-fluid container content-container" style="margin-top: 10%;">
		<div class="row main-row">
			<div class="col-12 content" style="position: relative">
				<form action="changing-client.php" method="post">
					<h1>Changing Client configurations</h1>
					<?php echo $id; ?>
					<label for="cl-nm" class="form-label">
						The client name
					</label>
					<?php echo $name_ip; ?>
					<br>
					<label for="permissions-sel" class="form-label">
						Client Permissions Type
					</label>
					<br>
					<?php echo $sel; ?>
					<br>
					<button type="button" class="btn btn-lg btn-secondary" data-toggle="collapse" aria-expanded="false" aria-controls="tk-dv" data-target="#tk-dv">
						See the raw token
					</button>
                    <br>
					<div class="collapse" id="tk-dv">
						<br>
						<div class="input-group">
							<?php echo $tk_ip; ?>
							<br>
							<!-- Button trigger modal -->
							<a type="button" class="btn btn-primary" data-toggle="modal" data-target="#modelToken" style="color: white;">
								<span>
									<i class="fas fa-plus"></i>
								</span>
								Require a new token
							</a>

							<!-- Modal TOKEN -->
							<div class="modal fade" id="modelToken" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
								<div class="modal-dialog" role="document">
									<div class="modal-content">
										<div class="modal-header">
											<h5 class="modal-title">Warning</h5>
												<button type="button" class="close" data-dismiss="modal" aria-label="Close">
													<span aria-hidden="true">&times;</span>
												</button>
										</div>
										<div class="modal-body">
											Changing the client token must have consequences, after doing that, you must download
											the new client authentication file.
											<h1>Are you sure to do that</h1>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
											<button type="submit" class="btn btn-primary" name="chmodal">Yes</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<br>
					<?php echo $del; ?>
					<button type="submit" class="btn btn-lg btn-success disabled" id="go" name="submit">Save changes</button>
					<a href="my_account.html" role="button" type="button" class="btn btn-lg btn-secondary">Cancel</a>
					<!-- Modal Saved Changes -->
					<div class="modal fade" id="modal-done" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title">Saved changes</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
								</div>
								<div class="modal-body">
									Your client changes were saved successfully!
									<?php echo $modalLink; ?>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-success" data-dismiss="modal">Ok</button>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="footer-container container" style="max-width: 100% !important; position: relative; margin-left: 0;">
        <div class="footer-row row">
            <div class="footer col-12" style="height: 150px; background-color: black; margin-top: 100%; position: relative; max-width: 100% !important; margin-left: 0;">
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
        var show = false;
        $(document).ready(function(){
            setAccountOpts(true);
            setSignatureOpts();
            applyToA();
            applyToForms();
            $("#img-user").css("background-image", "url(" + getLinkedUserIcon() + ")");
			if(show){
				$("#modal-done").modal('show');
				show = false;
			}
            applyToA();
        });

		$(document).on("change", ".al", function(){
			$("#go").removeClass("disabled");
		});

    </script>
</body>
</html>
