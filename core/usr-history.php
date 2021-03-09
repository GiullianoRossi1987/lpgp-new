<?php
namespace Core;
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Exceptions.php";

use DatabaseActionsExceptions\AlreadyConnectedError;
use DatabaseActionsExceptions\NotConnectedError;
use UsersSystemExceptions\InvalidUserName;
use UsersSystemExceptions\PasswordAuthError;
use UsersSystemExceptions\UserAlreadyExists;
use UsersSystemExceptions\UserNotFound;
use UsersSystemExceptions\UserKeyNotFound;
use CheckHistory\InvalidErrorCode;
use CheckHistory\RegisterNotFound;

/**
 * That class manages the signature checking history table in the MySQL database. That table storages all the signatures checkeds in the website, but only
 * signatures checkeds from normal users, for signatures checked by proprietaries history check the class PropCheckHistory. Those classes also creates relatories
 * of the signatures checked in HTML.
 * The authentications can have errors, all they are:
 *      * 0 => There wasn't errors in the authentication
 *      * 1 => The selected file is not a valid .lpgp file, it's checked verifing the extension and the structure
 *      * 2 => Invalid proprietary, if the proprietary don't exists in the database no more.
 *      * 3 => Invalid key (the most common), all is right, but the key is different then the original key
 *
 * @var string ERR_CD_MSG1 The error message used in the HTML relatories when the authentication returns error code 1.
 * @var string ERR_CD_MSG2 The error message used in the HTML relatories when the authentication returns error code 2.
 * @var string ERR_CD_MSG3 The error message used in the HTML relatories when the authentication returns error code 3.
 */
class UsersCheckHistory extends DatabaseConnection{

    const ERR_CD_MSG1 = "The file selected is not valid. It requires a .lpgp file and got {file_ext} in it. Or the structure of the file is not valid.\nPlease contact the software provider to check this error.\n";
    const ERR_CD_MSG2 = "The proprietary referenced in the signature file doesn't exists!\n";
    const ERR_CD_MSG3 = "The signature key in the file doesn't match with the original.\nPlease check if that is the updated version of the signature/software, if don't contact the proprietary or the provider of the software/signature\n";

    /**
     * That function checks if the primary key reference exists in the database. If don't will return false.
     *
     * @param integer $his_id The reference of the register to search
     * @return bool
     */
    private function checkHisExists(int $his_id){
        $this->checkNotConnected();
        $qr_raw = $this->connection->query("SELECT cd_reg FROM tb_signature_check_history WHERE cd_reg = $his_id;");
        while($row = $qr_raw->fetch_array()){
            if($row['cd_reg'] == $his_id) return true;
        }
        unset($qr_raw);
        return false;
    }

    /**
     * That method adds a register in the database. If that have any MySQLI errors, then you should think about the integrity of the primary key references.
     *
     * @param integer $usr_code The primary key reference of the user.
     * @param integer $sig_code The primary key reference of the signature.
     * @param integer $success If the signature is authentic. If it is, the the $error_cd will be null or 0.
     * @param integer|null $error_cd The error code of the authentication result, it can only be between 0 and 3 (integers obiviously). If don't will throw error.
     *
     * @throws InvalidErrorCode If the $err_code is not null, and there don't have errors. Or reverse.
     * @return integer The primary key reference of the added register.
     */
    public function addReg(int $usr_code, int $sig_code, int $success = 1, int $error_cd = null){
        $this->checkNotConnected();
        if($success == 1 && !is_null($error_cd)) throw new InvalidErrorCode($error_cd, 1);
        if($success == 0 && is_null($error_cd)) throw new InvalidErrorCode((int) $error_cd, 1);
        $err_vl = is_null($error_cd) ? 0 : $error_cd;
        $qr_add = $this->connection->query("INSERT INTO tb_signature_check_history (id_user, id_signature, vl_valid, vl_code) VALUES ($usr_code, $sig_code, $success, $err_vl);");
        unset($err_vl);
        $pk_qr = $this->connection->query("SELECT MAX(cd_reg) FROM tb_signature_check_history;");
        $id = (int) $pk_qr->fetch_array()[0];
        $pk_qr->close();
        return $id;
    }

    /**
     * That method returns the entire register in the database, using the primary key reference of the same.
     *
     * @param integer $ref The reference to the primary key
     * @throws RegisterNotFound If the reference doesn't exist at the database.
     * @return array Array of the tuple in the database of the register.
     */
    public function getRegByID(int $ref){
        $this->checkNotConnected();
        if(!$this->checkHisExists($ref)) throw new RegisterNotFound("There's no register #$ref!", 1);
        $qr_rr = $this->connection->query("SELECT * FROM tb_signature_check_history WHERE cd_reg = $ref;");
        $arr = $qr_rr->fetch_array();
        $qr_rr->close();
        return $arr;
    }

    /**
     * That method gets all the registers of the signatures checkeds by one user. Returns array type if the user checked any signature, and null if he doesn't
     * checked a single signature yet.
     * @param integer $usr_ref The primary key reference of the user.
     * @return array|null
     */
    public function getRegByUsr(int $usr_ref){
        $this->checkNotConnected();
        $qr = $this->connection->query("SELECT * FROM tb_signature_check_history WHERE id_user = $usr_ref;");
        $results = array();
        while($row = $qr->fetch_array()) $results[] = $row;
        return count($results) <= 0 ? null : $results;
    }

    /**
     * That method gets all the registers of a single signature that was checked any time from anyone. Returns array type if the signature was checked anytime, and
     * null if the signature wasn't checked yet.
     * @param integer $sig_ref The reference of the primary key of the signature.
     * @return array|null
     */
    public function getRegBySig(int $sig_ref){
        $this->checkNotConnected();
        $qr = $this->connection->query("SELECT * FROM tb_signature_check_history WHERE id_signature = $sig_ref;");
        $results = array();
        while($row = $qr->fetch_array()) $results[] = $row;
        $qr->close();
        return count($results) <= 0 ? null : $results;
    }

    /**
     * That method gets all the registers of a specific date in the database table. Return array with the results, if there's no results then will return null.
     *
     * @param string $tm_needle The datetime to search in the table.
     * @return array|null
     */
    public function qrByDate(string $tm_needle){
        $this->checkNotConnected();
        $qr = $this->connection->query("SELECT * FROM tb_signature_check_history WHERE dt_reg LIKE \"%$tm_needle%\";");
        $results = array();
        while($row = $qr->fetch_array()) $results[] = $row;
        $qr->close();
        return count($results) < 1 ? null : $results;
    }

    /**
     * That method sends the HTML relatory about the signature authentication.
     * @param integer $reg_ref The primary key reference of the register.
     * @throws RegisterNotFound If the reference don't exists.
     * @return string
     */
    public function generateRelatory(int $reg_ref){
        $this->checkNotConnected();
        if(!$this->checkHisExists($reg_ref)) throw new RegisterNotFound("There's no register #$reg_ref", 1);
        $qr_data = $this->connection->query("SELECT * FROM tb_signature_check_history WHERE cd_reg = $reg_ref;");
        $dt = $qr_data->fetch_array();
        $qr_data->close();
        $data_html = "<div class=\"relatory-php\">\n";
        $i_tag = $dt['vl_valid'] == 1 ? "<i class=\"fas fa-check\"></i>" : "<i class\"fas fa-times\"></i>";
        $data_html .= "<div class=\"img-relatory\">\n<span>$i_tag</span>\n</div>\n";
        $msg = "";
        $ext_cls = "";
        switch ((int) $dt['vl_code']){
            case 0:
                $msg = "The signature is valid!";
            break;
            case 1:
                $msg = self::ERR_CD_MSG1;
            break;
            case 2:
                $msg = self::ERR_CD_MSG2;
            break;
            case 3:
                $msg = self::ERR_CD_MSG3;
            break;
            default: throw new InvalidErrorCode($dt['vl_code']);
        }
        $ext_cls = $dt['vl_code'] != 0 ? "error-msg" : "";
        $data_html .= "<div class=\"msg-code $ext_cls\">$msg</div>\n";
        // creates the card of the signature if the authentication returned valid.
        if($dt['vl_code'] == 0){
            $signature_data_html = "<div class=\"card relatory-card\">\n";
            $sig_ref = $dt['id_signature'];
            $sign_data = $this->connection->query("SELECT * FROM tb_signatures WHERE cd_signature = $sig_ref;")->fetch_array();
            $prop_data = $this->connection->query("SELECT * FROM tb_proprietaries WHERE cd_proprietary = " . $sign_data['id_proprietary'] . ";")->fetch_array();
            $signature_data_html .= "<div class=\"card-header\"><h1 class=\"card-title\">Signature #" . $sig_ref . "</h1>\n";
            $signature_data_html .= "<div class=\"card-body\">";
            $signature_data_html .= "<div class=\"card-subtitle\"><a href=\"https://www.lpgpofficial.com/proprietary.php?id=" . $prop_data['cd_proprietary'] . "\">Proprietary: " . $prop_data['nm_proprietary'] . "</a>\n</div>\n";
            $signature_data_html .= "<div class=\"card-footer text-muted\"> Created at: " . $sign_data['dt_creation'] . "</div>\n</div>\n";
            $data_html .= $signature_data_html;
        }
        return $data_html;
    }

    /**
     * Returns all the HTML of the history from a user, using the cards to represents the relatories. That method was created for make faster the
     * development of the profile account page, wich have that history of the checked signatures in both types of accounts.
     *
     * @param string $nm_user The name of the user, normally used with the $_COOKIE['user'].
     * @return string
     */
    public function getUsrHistory(string $nm_user){
        $this->checkNotConnected();
        $usr_id = $this->connection->query("SELECT cd_user FROM tb_users WHERE nm_user = \"$nm_user\";")->fetch_array();
        $all_hs = $this->getRegByUsr((int) $usr_id['cd_user']);
        if(is_null($all_hs)) return "<h1>You don't have checked any signature yet!</h1>\n";
        $main_pg = "";
        for($i = 0; $i < count($all_hs); $i++){
            $card_main = "<div class=\"card signaturep-card\">\n<div class=\"card-header\">\n<span>\n";
            $dt = $all_hs[$i];
            $sign_data = $this->connection->query("SELECT * FROM tb_signatures WHERE cd_signature = " . $dt['id_signature'] . ";")->fetch_array();
            $img_span = $dt['vl_code'] == 0 ? "fas fa-check" : "fas fa-time";
            $card_main .= "<i class=\"$img_span\">\n</i>\n</span>\n<h2 class=\"card-title\">Signature #" . $sign_data['cd_signature'] . "</h2>\n</div>\n";
            $msg_title = $dt['vl_code'] == 0 ? "<h1 class=\"valid-title\">Valid</h1>\n" : "<h1 class=\"invalid-title\">Invalid</h1>\n";
            $sub_msg = "";
            switch ((int) $dt['vl_code']){
                case 0:
                    $sub_msg = "<h4 class=\"card-subtitle no-err\">Valid signature!</h4>\n";
                break;
                case 1:
                    $sub_msg = "<h4 class=\"card-subtitle err\">" . self::ERR_CD_MSG1 . "</h4>\n";
                break;
                case 2:
                    $sub_msg = "<h4 class=\"card-subtitle err\">" . self::ERR_CD_MSG2 . "</h4>\n";
                break;
                case 3:
                    $sub_msg = "<h4 class=\"card-subtitle err\">" . self::ERR_CD_MSG3 . "</h4>\n";
                break;
                default: throw new PropInvalidCode($dt['vl_code']);
            }
            $card_main .= $msg_title . $sub_msg;
            $card_main .= "<div class=\"card-body\">";
            $prop_dt = $this->connection->query("SELECT * FROM tb_proprietaries WHERE cd_proprietary = " . $sign_data['id_proprietary'] . ";")->fetch_array();
            $id = base64_encode($prop_dt['cd_proprietary']);
            $prop_data_html = is_null($prop_dt) ? "<div class=\"prop-nf-err\">(We can't find the proprietary, probabily he deleted him account)</div>\n" : "<a href=\"proprietary.php?id=$id\" target=\"_blanck\" class=\"prop-link\">" . $prop_dt['nm_proprietary'] . "</a>\n";
            $card_main .= "Proprietary: " . $prop_data_html;
            $card_main .= "<a href=\"https://www.lpgpofficial.com/relatory.php?rel=" . base64_encode($dt['cd_reg']) . "\" target=\"__blanck\" role=\"button\" class=\"btn btn-secondary\">Check the relatory</a>\n";
            $card_main .= "<div class=\"card-footer text-muted\">Checked signature at: " . $dt['dt_reg'] . "</div>\n</div>\n<div>\n<div>\n</div>\n</div>\n</div>";
            $main_pg .= $card_main . "<br><br>";
        }
        return $main_pg;
    }

    /**
     * Searches in the database using parameters of a associative array received
     * and return the results
     * @param array $parameters The associative array with the parameters
     * @return array The results of the query
     */
    public function fastQuery(array $parameters): array{
        $this->checkNotConnected();
        $qr_str = "SELECT * FROM tb_signature_check_history ";
        $firstAdded = false;
        foreach($parameters as $field => $value){
            if(!$firstAdded){
                $qr_str .= " WHERE ";
                $firstAdded = true;
            }
            else $qr_str .= ",";
            $qr_str .= is_numeric($value) ? " $field = $value" : " $field = \"$value\"";
        }
        $resp = $this->connection->query($qr_str . ";");
        $results = [];
        while($row = $resp->fetch_array()) $results[] = $row;
        return $results;
    }
}
 ?>
