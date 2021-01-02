<?php
if(session_status()  === PHP_SESSION_NONE) session_start();
require_once "core/Core.php";
require_once "core/Exceptions.php";

use Core\UsersData;
use Core\ProprietariesData;

if(!defined("EMAIL_TO"))          define("EMAIL_TO", "giulliano.scatalon.rossi@gmail.com");
if(!defined("FEEDBACK_TEMPLATE")) define("FEEDBACK_TEMPLATE", "/core/templates/feedback.html");
if(!defined("REPORT_TEMPLATE"))   define("REPORT_TEMPLATE", "/core/templates/report.html");
if(!defined("DUMP_REPORT_TYPES")) define("DUMP_REPORT_TYPES", ["Other", "Signature checking",
                                                               "Client Checking", "My Account",
                                                               "Signatures Management", "Clients Management",
                                                               "Access Plot view", "WebSite Design"]);
if(!defined("DUMP_FEEDBACK_MODE")) define("DUMP_FEEDBACK_MODE", ['Neutral', 'Positive', "Negative"]);
if(!defined("JSON_STATUS_ERR")) define("JSON_STATUS_ERR", -1);
if(!defined("JSON_STATUS_SUC")) define("JSON_STATUS_SUC", 0);

$jsonTemplate = array(
    "status" => null,
    "error" => null
);

/**
 * Replaces the content of the HTML template for e-mails before sending it
 *
 * @param string $content The middle content of the e-mail
 * @param string $user The user who sent those data
 * @param string $template The path to the template .html
 * @param string $usr_email The e-mail of the user who sent
 * @return string The dumped and replaced content
 */
function fetchTemplate(string $content, string $user, string $usr_email, string $template): string{
    $chnd = fopen($template, "r");
    $rawContent = fread($chnd, filesize($template));
    fclose($chnd);
    $replacer = [
        "%content%" => $content,
        "%user%" => $user,
        "%email%" => $usr_email,
        "%tms%" => date("Y-M-d H:m:i")
    ];
    foreach($replacer as $needle => $val)
        $rawContent = str_replace($needle, $val, $rawContent);
    return $rawContent;
}

/**
 * Sends the content in a e-mail using the HTML content.
 *
 * @param string $to The receiver of the e-mail
 * @param string $content The HTML content of the e-mail
 * @param string $from Who's sending it
 * @param string $subject The subject of the e-mail
 * @return void
 */
function send(string $to, string $content, string $from, string $subject){
    $json = &$GLOBALS['jsonTemplate'];
    $headers = "MIME-VERSION: 1.0\nContent-Type: text/html; charset=iso-8859-1;\n";
    $headers .= "From: $from\nCc:$to\n";
    $rc = mail($to, $subject, $content, $headers);
    if($rc === false){
        $json['status'] = JSON_STATUS_ERR;
        $json['error']  = error_get_last()['message'];
    }
    else{
        $json['status'] = JSON_STATUS_SUC;
        $json['error']  = null;
    }
}


$m_usr = new UsersData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
$m_prp = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
$l_name = $_SESSION['user'];
$dt = $_SESSION['mode'] == "normie" ? $m_usr->getUserData($l_name) : $m_prp->getPropData($l_name);

if(isset($_POST['report'])){
    $email = fetchTemplate($_POST['report-content'], $l_name, $dt['vl_email'], REPORT_TEMPLATE);
    $sent = send(EMAIL_TO, $email, $dt['vl_email'], "$l_name is reporting a error as " . DUMP_REPORT_TYPES[(int)$_POST['type']]);
}
else if(isset($_POST['feedback'])){
    $md = DUMP_FEEDBACK_MODE[(int)$_POST['mode']];
    $email = fetchTemplate($_POST['feedback-content'], $l_name, $dt['vl_email'], FEEDBACK_TEMPLATE);
    $sent = send(EMAIL_TO, $email, $dt['vl_email'], "$l_name is sending a $md feedback");
    if($sent === false){ die("Error: "); }
}
else{}  // do nothing
die(json_encode($jsonTemplate));
 ?>
