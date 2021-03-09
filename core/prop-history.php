<?php
namespace Core;
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Exceptions.php";

use PropCheckHistory\InvalidErrorCode as PropInvalidCode;
use PropCheckHistory\RelatoryError as PropRelatoryError;
use PropCheckHistory\RegisterNotFound as PropRegisterNotFound;
use DatabaseActionsExceptions\AlreadyConnectedError;
use DatabaseActionsExceptions\NotConnectedError;

/**
 * Manages the signatures checking by proprietaries table in the MySQL database. That table storages all the signatures authentications, valid or not,
 * made by proprietaries users. There's also other to manage the authentications made by the normal users.
 *
 * Those classes also creates relatories
 * of the signatures checked in HTML.
 * The authentications can have errors, all they are:
 *      * 0 => There wasn't errors in the authentication
 *      * 1 => The selected file is not a valid .lpgp file, it's checked verifing the extension and the structure
 *      * 2 => Invalid proprietary, if the proprietary don't exists in the database no more.
 *      * 3 => Invalid key (the most common), all is right, but the key is different then the original key
 * ------------------------------------------------------------------------------------------------------------------
 * @var string ERR_CD_MSG1 The error message used in the HTML relatories when the authentication returns error code 1.
 * @var string ERR_CD_MSG2 The error message used in the HTML relatories when the authentication returns error code 2.
 * @var string ERR_CD_MSG3 The error message used in the HTML relatories when the authentication returns error code 3.
 */
class PropCheckHistory extends DatabaseConnection{


    const ERR_CD_MSG1 = "The file selected is not valid. It requires a .lpgp file and got {file_ext} in it. Or the structure of the file is not valid.\nPlease contact the software provider to check this error.\n";
    const ERR_CD_MSG2 = "The proprietary referenced in the signature file doesn't exists!\n";
    const ERR_CD_MSG3 = "The signature key in the file doesn't match with the original.\nPlease check if that is the updated version of the signature/software, if don't contact the proprietary or the provider of the software/signature\n";

    /**
     * Checks if a register exists in the database table using the primary key reference of the register.
     *
     * @param integer $reg_ref The primary key reference of the register.
     * @return bool
     */
    private function checkHisExists(int $reg_ref){
        $this->checkNotConnected();
        $qr_raw = $this->connection->query("SELECT cd_reg FROM tb_signatures_prop_check_h WHERE cd_reg = $reg_ref;");
        while($row = $qr_raw->fetch_array()){
            if($row['cd_reg'] == $reg_ref) return true;
        }
        $qr_raw->close();
        return false;
    }

    /**
     * Adds a register in the database table with the requested data. If you had a mysqli_sql_exception, then i suggest you to check the
     * primary/foreign key references of the parameters.
     * @param integer $id_prop The primary key reference of the proprietary that checked the signature.
     * @param integer $id_sign The primary key reference of the signature thet was checked.
     * @param integer $success If there wasn't errors in the authentication.
     * @param integer|null $error_code If there was a error the code need to be bettween 0 and 3. That code will be storaged as the vl_code in the database table.
     * @throws PropInvalidCode If the error code is more then 0 but there wasn't errors in the authentication, or the code is 0 but the authentication returned errors.
     * @return integer The primary key reference of the added register.
     */
    public function addReg(int $id_prop, int $id_sign, int $success = 1, ?int $error_code = NULL){
        // I didn't maked the null option at the $error_code, same as the same method in the UsersCheckHistory 'cause I was lazy
        $this->checkNotConnected();
        // errors checking
        if($success == 1 && !is_null($error_code)) throw new  PropInvalidCode($error_code, 1);
        if($success == 0 && is_null($error_code)) throw new PropInvalidCode(0, 1);
        // end checking
        $vl = is_null($error_code) ? 0 : (int) $error_code;
        $qr_add = $this->connection->query("INSERT INTO tb_signatures_prop_check_h (id_prop, id_signature, vl_valid, vl_code) VALUES ($id_prop, $id_sign, $success, $vl);");
        $qr_id = $this->connection->query("SELECT MAX(cd_reg) FROM tb_signatures_prop_check_h;");
        echo $this->connection->error;
        $id = (int) $qr_id->fetch_array()[0];
        unset($qr_add);
        unset($qr_id);
        return $id;
    }

    /**
     * Searches all the times when a specific signature was checked by any proprietary user. It returns a array with the tuples, or return null if there're
     * no results.
     *
     * @param integer $sig_ref The primary key reference of the signature to search.
     * @return array|null
     */
    public function getRegBySig(int $sig_ref){
        $this->checkNotConnected();
        $qr = $this->connection->query("SELECT * FROM tb_signatures_prop_check_h WHERE id_signature = $sig_ref;");
        $results = array();
        while($row = $qr->fetch_array()) $results[] = $row;
        $qr->close();
        return count($results) <= 0 ? null : $results;
    }

    /**
     * Searches all the times when a specific proprietary user checked any signature. It returns a array with the tuples, or return null if there're no results.
     *
     * @param integer $prop_ref The primary key reference of the proprietary user account.
     * @return array|null
     */
    public function getRegByProp(int $prop_ref){
        $this->checkNotConnected();
        $qr = $this->connection->query("SELECT * FROM tb_signatures_prop_check_h WHERE id_prop = $prop_ref;");
        if($qr === false) print($this->connection->error);
        $results = array();
        while($row = $qr->fetch_array()) $results[] = $row;
        $qr->close();
        return count($results) <= 0 ? null : $results;
    }

    /**
     * Generates the string with the HTML code of the relatory of the authentication register.
     * @param integer $reg_ref The primary key reference of the register.
     * @return string.
     */
    public function generateRelatory(int $reg_ref){
        $this->checkNotConnected();
        if(!$this->checkHisExists($reg_ref = $reg_ref)) throw new PropRegisterNotFound("There's no register #$reg_ref", 1);
        $reg_data = $this->connection->query("SELECT * FROM tb_signatures_prop_check_h WHERE cd_reg = $reg_ref;")->fetch_array();
        $error_msg = "";
        $extra_cls = $reg_data['vl_code'] == 0 ? "" : "error-msg";
        $extra_card_cls = $reg_data['vl_code'] == 0 ? "valid-card" : "invalid-card";
        $main_data_html = "\n<div class=\"relatory-container\">\n";
        $i_tag = $reg_data['vl_code'] == 0 ? "<i class=\"fas fa-check\"></i>" : "<i class=\"fas fa-times\"></i>";
        $err = false;
        switch ( (int) $reg_data['vl_code']){
            case 0:
                $error_msg = "Signature valid!";
            break;
            case 1:
                $error_msg = self::ERR_CD_MSG1;
                $err = true;
            break;
            case 2:
                $error_msg = self::ERR_CD_MSG2;
                $err = true;
            break;
            case 3:
                $error_msg = self::ERR_CD_MSG3;
                $err = true;
            break;
            default: throw new PropInvalidCode($reg_data);
        }
        if(!$err){
            $card_div = "<div class=\"card relatory-card\">\n";
            $sig_dt = $this->connection->query("SELECT * FROM tb_signatures WHERE cd_signature = " . $reg_data['id_signature'] . ";")->fetch_array();
            $prop_dt = $this->connection->query("SELECT * FROM tb_proprietaries WHERE cd_proprietary = " . $sig_dt['id_proprietary'] . ";")->fetch_array();
            $id_prop = $prop_dt['cd_proprietary'];
            $card_div .= "<div class=\"card-header\">\n";
            $card_div .= "<h1 class=\"card-title\"> Signature #" . $sig_dt['cd_signature'] . "</h1><span>$i_tag</span>" . "\n<div class=\"message-relatory $extra_cls\">$error_msg</div>";
            $card_div .= "</div>\n<div class=\"card-body\">\n";
            $card_div .= "<h4 class=\"card-subtitle\"> Proprietary: <a href=\"https://www.lpgpofficial.com/proprietary.php?id=$id_prop\" target=\"_blanck\"> " . $prop_dt['nm_proprietary'] . "</a></div>\n";
            $card_div .= "<div class=\"card-footer\">Created at: " . $sig_dt['dt_creation'] . "</div>\n</div>\n<div>\n</div>";
            $main_data_html .= $card_div;
        }
        return $main_data_html;
    }


    /**
     * Returns all the HTML of the history from a proprietary user, using the cards to represents the relatories. That method was created for make faster the
     * development of the profile account page, wich have that history of the checked signatures in both types of accounts.
     *
     * @param string $nm_proprietary The name of the user, normally used with the $_COOKIE['user'].
     * @return string
     */
    public function getPropHistory(string $nm_proprietary){

        // TODO: refactor the code, turning the HTML card in a complete string.

        $this->checkNotConnected();
        $usr_id = $this->connection->query("SELECT cd_proprietary FROM tb_proprietaries WHERE nm_proprietary = \"$nm_proprietary\";")->fetch_array();
        $all_hs = $this->getRegByProp((int) $usr_id['cd_proprietary']);
        if(is_null($all_hs)) return "<h1>You don't have checked any signature yet!</h1>\n";
        $main_pg = "";  // all the page content
        for($i = 0; $i < count($all_hs); $i++){
            $card_main = "<div class=\"card signature-card\">\n<div class=\"card-header\">\n";
            $dt = $all_hs[$i];
            $sign_data = $this->connection->query("SELECT * FROM tb_signatures WHERE cd_signature = " . $dt['id_signature'] . ";")->fetch_array();
            $i_font = $dt['vl_code'] == 0 ? "<i class=\"fas fa-check\" style=\"color: green;\"></i>" : "<i class=\"fas fa-times\" style=\"color: red;\"></i>";
            $img_span = $dt['vl_code'] == 0 ? "https://www.lpgpofficial.com/media/checked-valid.png" : "https://www.lpgpofficial.com/media/checked-invalid.png";
            $card_main .= "<h2>Signature #" . $sign_data['cd_signature'] . "</h2><span class=\"badge badge-light\">$i_font\n</span></div>\n";
            $sub_msg = "";
            switch ((int) $dt['vl_code']){
                case 0:
                    $sub_msg = " <h4 class=\"card-subtitle no-err\">Valid signature!</h4>\n";
                break;
                case 1:
                    $sub_msg = " <h4 class=\"card-subtitle err\">" . self::ERR_CD_MSG1 . "</h4>\n";
                break;
                case 2:
                    $sub_msg = " <h4 class=\"card-subtitle err\">" . self::ERR_CD_MSG2 . "</h4>\n";
                break;
                case 3:
                    $sub_msg = " <h4 class=\"card-subtitle err\">" . self::ERR_CD_MSG3 . "</h4>\n";
                break;
                default: throw new PropInvalidCode($dt['vl_code']);
            }
            $card_main .= "\n" . $sub_msg;
            $card_main .= "<div class=\"card-body\">";
            $prop_dt = $this->connection->query("SELECT * FROM tb_proprietaries WHERE cd_proprietary = " . $sign_data['id_proprietary'] . ";")->fetch_array();
            $id = $prop_dt['cd_proprietary'];
            $cdId = base64_encode($id);
            $prop_data_html = is_null($prop_dt) ? "<div class=\"prop-nf-err\">(We can't find the proprietary, probabily he deleted him account)</div>\n" : "<a href=\"https://www.lpgpofficial.com/proprietary.php?id=" . base64_encode($cdId) . "\" target=\"_blanck\" class=\"prop-link\">" . $prop_dt['nm_proprietary'] . "</a>\n";
            $card_main .= "Proprietary: " . $prop_data_html;
            $card_main .= "<a href=\"https://www.lpgpofficial.com/relatory.php?rel=" . base64_encode($dt['cd_reg']) . "\" target=\"__blanck\" role=\"button\" class=\"btn btn-secondary\">Check the relatory</a>\n";
            $card_main .= "<div class=\"card-footer text-muted\">Checked signature at: " . $dt['dt_reg'] . "</div>\n</div>\n</div>\n<div>\n</div>";
            $main_pg .= $card_main . "<br>";
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
        $qr_str = "SELECT * FROM tb_signatures_prop_check_h ";
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
