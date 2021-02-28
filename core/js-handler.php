<?php
namespace JSHandler;
if(session_status() == PHP_SESSION_NONE)session_start();
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/config/configmanager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/proprietaries-data.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/signatures-data.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/clients-access-data.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Exceptions.php";


use Core\ProprietariesData;
use Core\SignaturesData;
use ProprietariesExceptions\ProprietaryNotFound;
use Core\ClientsAccessData;
use Configurations\ConfigManager;

$gblConfig = new ConfigManager($_SERVER["DOCUMENT_ROOT"] . "/config/mainvars.json");
if(!defined("LPGP_CONF")) define("LPGP_CONF", $gblConfig->getConfig());
if(!defined("MAX_SIGC")) define("MAX_SIGC", 5);   // the max number of the signatures checked card displayed at the my_account.html page

/**
 * That method sends the $_COOKIE vars about the logged user to the localStorage. In a inexisting session
 * It will set such as no one logged at the system.
 *
 * @return void
 */
function sendUserLogged(){
    if(session_status() == PHP_SESSION_NONE) session_start();
    if(session_status() == PHP_SESSION_NONE || session_status() == PHP_SESSION_DISABLED || empty($_COOKIE) || !isset($_COOKIE['user-logged']) || !$_COOKIE['user-logged']){
        // if there's no one logged.
        echo "";
        $_COOKIE['user-logged']  = true;
        $_COOKIE['mode']         = null;
        $_COOKIE['checked']      = null;
        $_COOKIE['user-icon']    = null;
    }
    else{
        $logged_user = $_COOKIE['user-logged'];
        $mode = $_COOKIE['mode'];
        $checked = $_COOKIE['checked'];
        $img = $_COOKIE['user-icon'];
        echo "<script>\n\n</script>";
        unset($logged_user);
        unset($mode);
        unset($checked);
    }

}

/**
 * Creates a signature card object. That card contains the main data about a signature, the main showed data are:
 *     * The signature ID (Database PK)
 *     * The algo/hash that will be encoded (md5, sha1, sha256 etc)
 *
 * @param int $signature_id
 * @param string $algo The choosed hash
 * @param string|null $opts_link if will have a link to the management of the signature (only for proprietaries)
 * @param string $proprietary_nm The proprietary of the signature
 * @return void
 */
function createSignatureCard(int $signature_id, string $algo, $opts_link, string $proprietary_nm, string $dt_creation){
    $obj = "<div class=\"signature-card\">\n";
    $obj_signature_name = is_null($opts_link) ? "<div class=\"signature-name\">Signature #" . $signature_id . "</div>" : "<div class=\"signature-name\"><a href=\"$opts_link\">Signature #$signature_id</a></div>";
    $obj_prop_nm = "<div class=\"proprietary-ref\">Proprietary: $proprietary_nm</div>";
    $obj_dt = "<div class=\"dt-signature\">Date & time created: $dt_creation</div>";
    $obj_algo = "<div class=\"choosed-hash\">Hashed at: $algo</div>";
    $obj .= $obj_signature_name . "\n" . $obj_prop_nm . "\n" . $obj_algo . "\n" . $obj_dt;
    echo $obj;
}

/**
 * Shows a signature data in a card to be displayed at the My_signatures page
 * @param array $signature The pure signature data.
 * @return string
 */
function genSignatureCard(array $signature): string{
    $badgeEncoding = "";
    switch($signature['vl_code']){
        case 0:
            $badgeEncoding = '<span class="badge badge-primary">MD5</span>';
            break;
        case 1:
            $badgeEncoding = '<span class="badge badge-primary">SHA1</span>';
            break;
        case 2:
            $badgeEncoding = '<span class="badge badge-primary">SHA256</span>';
            break;
        default: $badgeEncoding = '<span class="badge badge-danger" data-toggle="tooltip" title="Something went wrong">???</span>';
    }
    $enc = base64_encode($signature['cd_signature']);
    return '<div class="card sig-card">
                <div class="card-content">
                    <div class="card-header">
                        <h3>Signature #' . $signature['cd_signature'] . '   <small>'. $badgeEncoding .'</small></h3>
                    </div>
                    <div class="card-body">
                        <a href="https://lpgpofficial.com/get_my_signature.php?id='. $enc . '">Download <span><i class="fas fa-file-download"></i></span></a>
                        <br>
                        <a href="https://lpgpofficial.com/ch_signature_data.php?sig_id='. $enc . '">Configurations<span><i class="fas fa-cog"></i></span></a>
                    </div>
                    <div class="card-footer">
                        <h6><b>Date Created:</b> '. $signature['dt_creation'] . '</h6>
                    </div>
                </div>
            </div>';
}

/**
 * That method sets all the signatures from a proprietary
 * @param int $proprietary The primary key reference of the proprietary to get him signatures
 * @return void
 */
function lsSignaturesMA(int $proprietary){
    $all = "";
    $sig = new SignaturesData("giulliano_php", "");
    $signatures = $sig->qrSignatureProprietary($proprietary);
    if(is_null($signatures)){ return "<h1>You don't have any signature yet!</h1>";}
    foreach($signatures as $cd){
        $sig_data = $sig->getSignatureData($cd);
        // TODO Change all that code for the function created before
        $card = "<div class=\"card sig-card\">\n";
        $card .= "<div class=\"card-header\">\n";
        $card .= "<h3 class=\"card-title\"> Signature #$cd</h3>\n";
        $card .= "<h5 class=\"card-subtitle\">" . $sig_data['dt_creation'] . "</h5>\n";
        $card .= "</div> <div class=\"card-body\">";
        $card .= "<div class=\"card-text\">\n";
        $card .= "<a href=\"https://www.lpgpofficial.com/get_my_signature.php?id=" . base64_encode($cd). "\">Download <i class=\"fas fa-file-download\"></i></a>" . "<br><br>".
                 "<a href=\"https://www.lpgpofficial.com/ch_signature_data.php?sig_id=" . base64_encode($cd). "\">Configurations<i class=\"fas fa-cog\"></i></a>\n"
                . "</div>\n</div>\n</div><br>";
        $all .= "\n$card\n";
    }
    return $all;
}



/**
 * Do the same thing then the lsSignaturesMA, but from a different proprietary and without the Download & Configurations options in the
 * Signature card.
 *
 * @param integer $proprietary The primary key reference of the other proprietary
 * @return string
 */
function lsExtSignatures(int $proprietary){
    $all = "";
    $sig = new SignaturesData("giulliano_php", "");
    $signatures = $sig->qrSignatureProprietary($proprietary);
    if(is_null($signatures)){ return "<h1>You don't have any signature yet!</h1>";}
    foreach($signatures as $cd){
        $sig_data = $sig->getSignatureData($cd);
        // TODO Upgrade the layout of the signature card
        $card = "<div class=\"card sig-card\">\n<div class=\"card-header\">\n<h3 class=\"card-title\"> Signature #$cd</h3>\n<h5 class=\"card-subtitle\">" . $sig_data['dt_creation'] . "\n</h5></div>";
        $all .= "\n$card\n";
    }
    return $all;
}

/**
 * Returns a valid path to the image file of any user/proprietary.
 *
 * @param string $raw_path The raw path of the image.
 * @param bool $ext_root If the script that's calling the method is in the server root.
 * @return string
 */
function getImgPath(string $raw_path, bool $ext_root = true){
    $exp = explode("/", $raw_path);
    return $ext_root ? "../" . $exp[count($exp) - 2] . "/" . $exp[count($exp) - 1] : "./" . $exp[count($exp) - 2] . "/" . $exp[count($exp) - 1];
}

/**
 * That method sets all the values of a signature, it is used for the configurations of the signature, at the file signature_config.php
 * @param integer $signature The Primary key reference of the signature at the database.
 * @return string The string with all the inputs of the signature data.
 */
function inputsGets(int $signature){
    $sign = new SignaturesData("giulliano_php", "");
    $dt = $sign->getSignatureData($signature);
    $main_str = "<h1>Signature #" . $dt['cd_signature'] . "</h1>\n";
    $passwd = $dt['vl_password'];
    $main_str .= "<input value=\"$passwd\" name=\"vl-passwd\" class=\"form-control\" label=\"The raw signature\">\n";
    $main_str .= $sign->getCodesHTML(true, (int)$dt['vl_code']) . "\n";
    return $main_str;
}

/**
 * Returns the HTML code of a signature card after the validation.
 * **WARNING**: all the anchor HTML links gonna work only at the check_signature.php page.
 * @param integer $sign_ref The primary key reference of the signature to create the card.
 * @param bool $valid If the signature is valid, used after the authentication.
 * @return string The HTML code.
 */
function createSignatureCardAuth(int $sign_ref, bool $valid){
    $sign_obj = new SignaturesData("giulliano_php", "");
    $data = $sign_obj->getSignatureData($sign_ref);
    $prp_obj = new ProprietariesData("giulliano_php", "");
    try{
        $prop_nm = $prp_obj->getPropDataByID($data['id_proprietary'])['nm_proprietary'];
    }
    catch(ProprietaryNotFound $e){
        $prop_nm = "(Proprietary not found)";
    }
    $card_str = "<div class=\"card signature-vl-card\">\n";
    if($valid){
        $card_str .= "<div class=\"card-header\">\n";
        $card_str .= "<span class=\"span-card-vl\">\n<i class=\"fas fa-check\"></i>\n</span>\n";
        $card_str .= "<h1 class=\"card-title\">Signature #$sign_ref</h1>\n";
        $card_str .= "</div>\n";
        // end of the header (card)
        // start of the body (card)
        $card_str .= "<div class=\"card-body\">\n";
        $card_str .= "<h3>Proprietary: ";
        $id = base64_encode($data['id_proprietary']);
        $prp_a = "<a href=\"proprietary.php?id=$id\" target=\"_blanck\">$prop_nm</a>";
        $card_str .= $prp_a . "</h3>\n";
        unset($prp_a);
        $card_str .= "<h3>Created in: " . $data['dt_creation'] . "</h3>\n";
        $card_str .= "</div>";
        // end of the body
        // start of the footer
        $card_str .= "<div class=\"card-footer\"></div>";
        // end of the card
        $card_str .= "</div>";
    }
    else{
        $card_str .= "<div class=\"card-header\">\n";
        $card_str .= "<span class=\"span-card-vl\">\n<i class=\"fas fa-times\"></i>\n</span>\n";
        $card_str .= "<h1 class=\"card-title\">Signature #$sign_ref</h1>\n";
        $card_str .= "</div>\n";
        // end of the header (card)
        // start of the body (card)
        $card_str .= "<div class=\"card-body\">\n";
        $card_str .= "<h3>Proprietary: ";
        $id = base64_encode($data['id_proprietary']);
        $prp_a = "<a href=\"proprietary.php?id=$id\" target=\"_blanck\">$prop_nm</a>";
        $card_str .= $prp_a . "</h3>\n";
        unset($prp_a);
        $card_str .= "<h3>Created in: " . $data['dt_creation'] . "</h3>\n";
        $card_str .= "</div>";
        // end of the body
        // start of the footer
        $card_str .= "<div class=\"card-footer\"></div>";
        // end of the card
        $card_str .= "</div>";
    }
    return $card_str;
}

/**
 * That method adds the links of the option content of the SDK's.
 * The links can be right to the SDK download page or to the login page.
 *
 * @return string
 */
function setCon1Links(){
    if($_COOKIE['user-logged'] && $_COOKIE['mode'] == "prop"){
        return "<ul>\n" .
                "   <li>\n<a href=\"./devcenter/sdks/sdks.php\">See our SDK'S</a>\n</li>\n" .
                "   <li>\n<a href=\"./devcenter/add-client.php\">First add a client for the System</a></li>\n" .
                "   <li>\n>a href=\"./devcenter/help.php\">If you have any doubt about the clients</a></li>\n</ul>";
    }
    else{
        return "<ul>\n" .
                "   <li>\n<a href=\"./login.html\">See our SDK'S</a>\n</li>\n" .
                "   <li>\n<a href=\"./login.html\">First add a client for the System</a></li>\n" .
                "   <li>\n><a href=\"./login.html\">If you have any doubt about the clients</a></li>\n</ul>\n<h1> But before accessing it you'll need to make login with a proprietary account</h1>\n";
    }
}


/**
 * That function creates a bootstrap card for the clients that the proprietary have.
 * That's a function like the CreateSignatureCard;
 *
 * @param array $clientData A array with the clients data. The array must be:
 *      [0] => The client ID
 *      [1] => The client Name
 *      [2] => The total of access
 * @return string
 */
function createClientCard(array $clientData): string{
    $link_change = '<a href="ch-client.php?client=' . base64_encode($clientData[0]) . '" class="dft-link">' .
                    'Client configurations <span><i class="fas fa-cog"></i></span></a><br>';
    $link_access = '<a href="client-accesses.php?client=' . base64_encode($clientData[0]) . '" class="dft-link">' .
                    'Client Accesses <span><i class="fas fa-chart-bar"></i></span></a><br>';
    $link_data = '<a href="client-data.php?client=' . base64_encode($clientData[0]) . '" class="dft-link">'.
                    'Download Client Data <span><i class="fas fa-box"></i></span></a>';
    $content_card = '<div class="card client-card">
        <div class="card-body">
            <h4 class="card-title">Client <b>' . $clientData[1] .'</b> </h4>
            <h6 class="card-subtitle mb-2 client-subtitle">#' . $clientData[0] . '</h6>
            <hr>
            <p class="card-text">
                '. $link_change .'
                <br>
                '. $link_access .'
                <br>
                '. $link_data . '
            </p>
        </div>
        <div class="card-footer">
            <h5>Access number: ' . $clientData[2] .'</h5>
        </div>
    </div>';
    return $content_card;
}


/**
 * That function creates a bootstrap card using the client received soft data.
 *
 * @param array $clientSoftData The soft data of the client, received after the client authentication.
 * @return string
 */
function createClientAuthCard(array $clientSoftData): string{
    $tmpProprietariesObj = new ProprietariesData("giulliano_php", "");
    $nameProprietary = $tmpProprietariesObj->getPropDataByID($clientSoftData['id_proprietary'])['nm_proprietary'];
    $linkProprietary = '<a href="proprietary.php?id=' . base64_encode($clientSoftData['id_proprietary']) . '" target="_blank">' . $nameProprietary . '</a>';
    $cardRet = '<div class="card client-card">
        <div class="card-body">
            <h4 class="card-title">Client <b>' . $clientSoftData['nm_client'] . '</b></h4>
            <h6 class="card-subtitle mb-2 client-subtitle">#' . base64_encode($clientSoftData['cd_client']) . '</h6>
            <hr>
            <p class="card-text">
                Proprietary: ' . $linkProprietary . '
                <br>
            </p>
        </div>
    </div>';
    return $cardRet;
}

/**
 * That function generates a new client access plot. Using a specific data type, normally generated after
 *
 * @param integer $clientID The client used to check
 * @param string $chartID The canvas element id, to generate the chart.
 * @return string The HTML plot content
 */
function createAccessChart(int $clientID, string $chartID = "client-plots"): string{
    $accessObj = new ClientsAccessData("giulliano_php", "");
    $plotData = $accessObj->getPlotAccessData($clientID);
    $mainChart = '<script>';
    $jsonArr = [
        "type" => "bar",
        "data" => [
            "labels" => array_keys($plotData),
            "datasets" => [

            ]
        ],
        "options" => [
            "maintainAspectRadio" => false
        ]
    ];

    // adding the data types
    foreach($plotData as $year => $tot) $jsonArr['data']['datasets'][] = ["label" => "Total", "data" => $tot];

    $plotContent = json_encode($jsonArr);
    $mainChart .= "\n" . 'var chart = document.getElementById("' . $chartID . '");
                          var clientChart = new Chart(chart, ' . $plotContent . ');</script>';
    return $mainChart;
}

/**
 * That method creates a HTML content to be showed when one of the pages had internal errors.
 *
 * @param string $error_message
 * @return string
 */
function errorTemplate(string $error_message): string{
    return '<div class="error-message" >
                <div class="img-fake">
                    <span id="err-span">
                        <i class="fas fa-bug" id="err-i"></i>
                    </span>
                </div>
                <center>
                    <h1>Oops! An error occoured</h1>
                </center>
                <br>
                <h2>For more information about this error please contact us: lpgpofficial@gmail.com</h2>
                <br>
                <div class="message-err collapse" id="error">
                    <b>' . $error_message . '</b>
                </div>
                <br>
                <a href="#error" data-toggle="collapse" role="button" class="btn btn-lg btn-primary" aria-expanded="false" aria-controls="error">
                    Show error (Advanced)
                </a>
            </div>';
}


/**
 * Generates a result of a normal user query in cards
 * @param array $data The user data received
 */
function genResultNormalUser(array $data): string{
    return '<div class="card result-card card-rs-normal text-center">
                <div class="card-content">
                    <div class="card-body">
                        <a href="user.php?id='. base64_encode($data[0]) . '">
                            <h4 class="card-title">
                                    '. $data[1] . '    <span class="bagde badge-primary">Normal User</span>
                                </h4>
                        </a>
                    </div>
                </div>
            </div><br>' . PHP_EOL;
}

/**
 * Generates a result of a proprietary user query in cards
 * @param array $data The proprietary data received
 */
function genResultProprietary(array $data): string{
    return '<div class="card result-card card-rs-prop text-center">
                <div class="card-content">
                    <div class="card-body">
                        <a href="proprietary.php?id='. base64_encode($data[0]) . '">
                            <h4 class="card-title">
                                    '. $data[1] . '   <span class="bagde badge-success">Proprietary</span>
                                </h4>
                        </a>
                    </div>
                </div>
            </div><br>' . PHP_EOL;
}
/**
 * Generates a result of a client query in cards
 */
function genResultClient(array $data): string{
    return '<div class="card result-card card-rs-prop text-center">
                <div class="card-content">
                    <div class="card-body">
                        <a href="proprietary.php?id='. base64_encode($data[3]) . '" data-toggle="tooltip" title="visit the proprietary">
                            <h4 class="card-title">
                                    '. $data[1] . '   <span class="bagde badge-secondary">Client</span>
                                </h4>
                        </a>
                    </div>
                </div>
            </div><br>' . PHP_EOL;
}
?>
