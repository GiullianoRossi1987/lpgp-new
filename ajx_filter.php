<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/core/Core.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/core/js-handler.php";

use Core\SignaturesData;
use Core\ClientsData;
use Core\ProprietariesData;
use function JSHandler\genSignatureCard;
use function JSHandler\createClientCard;

define("RESULT_LESS", '<div class="result-error"><h1>No Results</h1><span></span></div>');

if(isset($_POST['scope']) && isset($_POST['sortType'])){
    $prop = new ProprietariesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
    if($_POST['scope'] == 'c'){
        // clients
        $cls = new ClientsData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
        switch((int)$_POST['sortType']){
            case 0:
                $clients = $cls->getClientsByOwner($_SESSION['user']);
                $content = "";
                foreach($clients as $client) $content .= createClientCard($cls->getClientCardData($client['cd_client']));
                die(strlen($content) > 0 ? $content : RESULT_LESS);
                break;
            case 21:
                $clients = $cls->sortAZ($_SESSION['user']);
                $content = "";
                foreach($clients as $client) $content .= createClientCard($cls->getClientCardData($client['cd_client']));
                die(strlen($content) > 0 ? $content : RESULT_LESS);
                break;
            case 22:
                $clients = $cls->sortZA($_SESSION['user']);
                $content = "";
                foreach($clients as $client) $content .= createClientCard($cls->getClientCardData($client['cd_client']));
                die(strlen($content) > 0 ? $content : RESULT_LESS);
                break;
            default: die("INTERNAL ERROR");
        }
    }
    else if($_POST['scope'] == 's'){
        // signatures
        $sig = new SignaturesData(LPGP_CONF['mysql']['sysuser'], LPGP_CONF['mysql']['passwd']);
        switch((int)$_POST['sortType']){
            case 0:
                $id = $prop->getPropID($_SESSION['user']);
                $items = $sig->qrSignatureProprietary($id);
                $content = "";
                foreach($items as $signId) {
                    $signatureItem = $sig->getSignatureData($signId);
                    $content .= genSignatureCard($signatureItem);
                }
                die(strlen($content) > 0 ? $content : RESULT_LESS);
                break;
            case 11:
                $items = $sig->filterNewer($_SESSION['user']);
                $content = "";
                foreach($items as $sigItem)
                    $content .= genSignatureCard($sigItem);
                die(strlen($content) > 0 ? $content : RESULT_LESS);
                break;
            case 12:
                $items = $sig->filterOlder($_SESSION['user']);
                $content = "";
                foreach($items as $sigItem)
                    $content .= genSignatureCard($sigItem);
                die(strlen($content) > 0 ? $content : RESULT_LESS);
                break;
            case 13:
                $items = $sig->filterMd5($_SESSION['user']);
                $content = "";
                foreach($items as $sigItem)
                    $content .= genSignatureCard($sigItem);
                die(strlen($content) > 0 ? $content : RESULT_LESS);
                break;
            case 14:
                $items = $sig->filterSha1($_SESSION['user']);
                $content = "";
                foreach($items as $sigItem)
                    $content .= genSignatureCard($sigItem);
                die(strlen($content) > 0 ? $content : RESULT_LESS);
                break;
            case 15:
                $items = $sig->filterSha256($_SESSION['user']);
                $content = "";
                foreach($items as $sigItem)
                    $content .= genSignatureCard($sigItem);
                die(strlen($content) > 0 ? $content : RESULT_LESS);
                break;
            default: die("INTERNAL ERROR");
        }
    }
    else die("INTERNAL ERROR");
}
 ?>
