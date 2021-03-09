<?php
namespace Core;
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Exceptions.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/control/controllers.php";

use DatabaseActionsExceptions\AlreadyConnectedError;
use DatabaseActionsExceptions\NotConnectedError;

use SignaturesExceptions\InvalidSignatureFile;
use SignaturesExceptions\SignatureAuthError;
use SignaturesExceptions\SignatureNotFound;
use SignaturesExceptions\SignatureFileNotFound;
use SignaturesExceptions\VersionError;
use Control\SignaturesController;

use ProprietariesExceptions\ProprietaryNotFound;

/**
 * That class contains all the uses of the signatures and signatures files.
 * The uploaded files stay at the directory ./usignatures.d and the downloadeble files stay at
 * the directory ./signatures.d
 *
 * @var string|int VERSION_ACT The version the signature will be storaged.
 * @var string|int VERSION_MIN The minimal version accepted.
 * @var array      VERSION_ALL The allowed versions of reading.
 */
class SignaturesData extends DatabaseConnection{
    const VERSION_ACT = "alpha";
    const VERSION_MIN = "alpha";
    const VERSION_ALL = ["alpha"];
    const CODES       = ["md5", "sha1", "sha256"];
    const DELIMITER   = "/";


    /**
     * Checks if a signature exists in the database. It uses the PK at the database.
     *
     * @param int $signature_id The PK for search.
     * @return bool
     */
    public function checkSignatureExists(int $signature_id){
        $this->checkNotConnected();
        $qr = $this->connection->query("SELECT cd_signature FROM tb_signatures WHERE cd_signature = $signature_id;");
        while($row = $qr->fetch_array()){
            if($row['cd_signature'] == $signature_id || $row['cd_signature'] == "" . $signature_id) return true;
        }
        unset($qr);
        return false;
    }

    /**
     * Encodes a password with the specified code
     * @param string $passwd The password to encode
     * @param string|integer $code The code to use
     * @return string The password encoded with the hash specified by the code
     */
    private function encode_passwd(string $passwd, $code): string{
        $algo = is_numeric($code) ? SignaturesData::CODES[$code] : $code;
        return hash($algo, $passwd);
    }

    /**
     * Returns all the options of codes in the HTML format, it can be on input mode, using the select tag, or in the list mode, if the param
     * of the input mode is false. In both case it will return a string with the codes options in HTML.
     *
     * @param boolean $input_mode If the codes will be in the select tag.
     * @param integer|null $spc_ind If the select tag will have a specific code.
     * @return string
     */
    public function getCodesHTML(bool $input_mode = false, int $spc_ind = null){
        if($input_mode){
            $main = "<select class=\"form-control default-select\">";
            for($i=0; $i < count(self::CODES); $i++){
                // using it, the value will be the index of the array
                if(!is_null($spc_ind) && $i == $spc_ind){
                    $main += "\n<option value=\"$i\" selected> " . self::CODES[$i] . "</option>\n";
                }
                else $main += "\n<option value=\"$i\"> " . self::CODES[$i] . "</option>\n";
            }
            return $main;
        }
        else{
            $main = "<ul>";
            for($i = 0; $i < count(self::CODES); $i++) $main += "\n<li>" . self::CODES[$i] . "[$i]</li>\n";
            return $main;
        }
    }

    /**
     * Get all the fields of a signature and return it in a array
     *
     * @throws SignatureNotFound If there's no signature with such primary key
     * @param integer $signature The primary key reference of the signature
     * @return array
     */
    public function getSignatureData(int $signature){
        $this->checkNotConnected();
        if(!$this->checkSignatureExists($signature)) throw new SignatureNotFound("There's no signature #$signature", 1);
        return $this->connection->query("SELECT * FROM tb_signatures WHERE cd_signature = $signature;")->fetch_array();
    }

    /**
     * Creates a filename for the signature file.
     *
     * @param int $initial_counter The first contage of the filename (signature-file-$initial_counter)
     * @return string
     */
    public static function generateFileNm(int $initial_counter = 0){
        $local_counter = $initial_counter;
        while(true){
            if(!file_exists("signatures.d/signature-file-". $local_counter . ".lpgp"))
                break;
            else $local_counter++;
        }
        return "signature-file-".$local_counter . ".lpgp";
    }

    /**
     * Creates a signature file and return it link to the file.
     *
     * @param string $signature_id The PK on the database.
     * @param bool $HTML_mode If the method will return a HTML <a>
     * @throws SignatureNotFound If there's no such PK in the database.
     * @return string
     */
    public function createsSignatureFile(int $signature_id, bool $HTML_mode = true, string $file_name){
        $this->checkNotConnected();
        if(!$this->checkSignatureExists($signature_id)) throw new SignatureNotFound("There's no signature #$signature_id !", 1);
        $sig_dt = $this->connection->query("SELECT prop.nm_proprietary, sig.vl_password, sig.vl_code FROM tb_signatures as sig INNER JOIN tb_proprietaries AS prop ON prop.cd_proprietary = sig.id_proprietary WHERE sig.cd_signature = $signature_id;")->fetch_array();
        $controller = new SignaturesController(CONTROL_FILE);
        $dtk = $controller->generateDownloadToken();
        $content = array(
            "Date-Creation" => date(DEFAULT_DATETIME_F),
            "Proprietary" => $sig_dt['nm_proprietary'],
            "ID" => $signature_id,
            "Signature" => $sig_dt['vl_password'],
            "DToken" => $dtk
        );
        $to_json = json_encode($content);
        $arr_ord = array();
        for($char = 0; $char < strlen($to_json); $char++) array_push($arr_ord, "" . ord($to_json[$char]));
        $content_file = implode(self::DELIMITER, $arr_ord);
        file_put_contents("signatures.d/" . $file_name, $content_file);
        $controller->addDownloadRecord($signature_id, $dtk, $content['Date-Creation']);
        unset($controller);
        return $HTML_mode ? "<a href=\"https://lpgpofficial.com/signatures.d/$file_name\" download=\"$file_name\" role=\"button\" class=\"btn btn-lg downloads-btn btn-primary\">Get your signature #$signature_id here!</a>" : "https://www.lpgpofficial.com/signatures.d/$file_name";
    }


    /**
     * Checks if the signature file is a .lpgp file.
     *
     * @param string $file_name The file to verify
     * @return bool
     */
    private static function checkFileValid(string $file_name){
        $sp = explode(".", $file_name);
        return $sp[count($sp) - 1] == "lpgp";
    }

    /**
     * Checks a uploaded signature file. It needs to have the extension .lpgp.
     * All the uploaded signatures files stay at the usignatures.d.
     *
     * @param string $file_path The signature file uploaded path.
     * @throws InvalidSignatureFile if the file is not a .lpgp
     * @throws VersionError if the signature file version is not allowed.
     * @throws SignatureNotFound if the ID of the signature on the file don't exists
     * @throws SignatureAuthError If the file is not valid
     * @return true
     */
    public function checkSignatureFile(string $file_name){
        $this->checkNotConnected();
        if(!$this->checkFileValid($file_name)) throw new InvalidSignatureFile("", 1);
        if(!file_exists("/usignatures.d/$file_name")) throw new SignatureFileNotFound("There's no file '$file_name' on the uploaded signatures folder.", 1);
        $content_file = utf8_encode(file_get_contents("/signatures.d/" . $file_name));
        $controller = new SignaturesController(CONTROL_FILE);
        if(!$controller->authDownloadFile($file_name)) return false;
        $sp_content = explode(self::DELIMITER, $content_file);
        $ascii_none = [];
        for($i = 0; $i < count($sp_content); $i++){
            $ascii_none[] = chr((int) $sp_content[$i]);
        }
        $ascii_none_str = implode("", $ascii_none);
        $json_arr = json_decode(preg_replace("/[[[:cntrl:]]/", "", $ascii_none_str), true);
        if(!$this->checkSignatureExists((int) $json_arr['ID'])) throw new SignatureNotFound("There's no signature #" . $json_arr['Signature'], 1);
        $signautre_data = $this->connection->query("SELECT vl_password FROM tb_signatures WHERE cd_signature = " . $json_arr['ID'])->fetch_array();
        if($signautre_data['vl_password'] != $json_arr['Signature']) throw new SignatureAuthError("The file signature is not valid.", 1);

        return true;
    }

    /**
     * Does the same thing then the checkProprietaryExists on the class ProprietariesData,
     * But this time it uses the PK not the name.
     *
     * @param int $id The PK of the proprietary
     * @return bool
     */
    private function checkProprietaryExists(int $id){
        $this->checkNotConnected();
        $all_rt = $this->connection->query("SELECT cd_proprietary FROM tb_proprietaries WHERE cd_proprietary = $id;");
        while($row = $all_rt->fetch_array()){
            if($row['cd_proprietary'] == $id) return true;
        }
        unset($all_rt);
        return false;
    }

    /**
     * Creates a new signature on the database.
     * @param int $id_proprietary The PK of the signature proprietary.
     * @param string $password The word used to be the signature.
     * @param int $code The algo index on the constant self::CODES
     * @param bool $encode_word If the method will encode the signature
     * @throws ProprietaryNotFound if the $id_proprietary don't exists as a proprietary
     * @return void
     */
    public function addSignature(int $id_proprietary, string $password, int $code, bool $encode_word = true){
        $this->checkNotConnected();
        if(!$this->checkProprietaryExists($id_proprietary)) throw new ProprietaryNotFound("There's no proprietary with the ID #$id_proprietary", 1);
        $to_db = $encode_word ? hash(self::CODES[$code], $password) : $password;
        $qr_vd = $this->connection->query("INSERT INTO tb_signatures (id_proprietary, vl_password, vl_code) VALUES ($id_proprietary, \"$to_db\", $code);");
        unset($qr_vd);
        unset($to_db);
    }

    /**
     * Removes a signature from the database. It uses the PK of the signature tuple at the MySQL database.
     *
     * @param int $signature_id The signature PK on the database.
     * @throws SignatureNotFound If the PK don't exists in the database.
     * @return void
     */
    public function delSignature(int $signature_id){
        $this->checkNotConnected();
        if(!$this->checkSignatureExists($signature_id)) throw new SignatureNotFound("There's no signature with the PK #$signature_id", 1);
        $qr_rm = $this->connection->query("DELETE FROM tb_signatures WHERE cd_signature = $signature_id;");
        unset($qr_rm);
    }

    /**
     * Changes the FK of the database, that contains the proprietary that owns the signature.
     *
     * @param int $signature The PK of the signature.
     * @param int $new_proprietary The new Proprietary ID
     * @throws ProprietaryNotFound If the new ID don't exists has a proprietary
     * @throws SignatureNotFound If the PK don't exists.
     * @return void
     */
    public function chProprietaryId(int $signature, int $new_proprietary){
        $this->checkNotConnected();
        if(!$this->checkSignatureExists($signature)) throw new SignatureNotFound("There's no signature #$signature;", 1);
        if(!$this->checkProprietaryExists($new_proprietary)) throw new ProprietaryNotFound("There's no proprietary with that id #$new_proprietary", 1);
        $qr_ch = $this->connection->query("UPDATE tb_signatures SET id_proprietary = $new_proprietary WHERE cd_signature = $signature;");
        unset($qr_ch);
    }

    /**
     * Changes the algo code used at the signature.
     *
     * @param int $signature The PK for the signature.
     * @param int $code The index of the constant array self::CODES.
     * @param string $word_same The same word in the database. To reupdate the word too. It don't have to be encoded before.
     * @throws SignatureNotFound If the PK don't exists
     * @return void
     */
    public function chSignatureCode(int $signature, int $code, string $word_same){
        $this->checkNotConnected();
        if(!$this->checkSignatureExists($signature)) throw new SignatureNotFound("There's no signature #$signature", 1);
        // $act_code = $this->connection->query("SELECT vl_code FROM tb_signatures WHERE cd_signature = $signature;")->fetch_array();
        $to_db = hash(self::CODES[$code], $word_same);
        $qr_ch = $this->connection->query("UPDATE tb_signatures SET vl_code = $code WHERE cd_signature = $signature;");
        $qr_ch = $this->connection->query("UPDATE tb_signatures SET vl_password = \"$to_db\" WHERE cd_signature = $signature;");
        return;
    }

    /**
     * It changes the main word of the signature. If the new word is not encoded at the same algo, the method
     * will encode it.
     *
     * @param int $signature The PK of the signature at the database;
     * @param string $word The new word to set.
     * @param bool $encode_here If the method will encode the word, if don't (false) the word must be encoded already.
     * @throws SignatureNotFound If the PK don't exists.
     * @return void
     */
    public function chSignaturePassword(int $signature, string $word, bool $encode_here = true){
        $this->checkNotConnected();
        if(!$this->checkSignatureExists($signature)) throw new SignatureNotFound("There's no signature #$signature", 1);
        $to_db = "";
        if($encode_here){
            $code_arr = $this->connection->query("SELECT vl_code FROM tb_signatures WHERE cd_signature = $signature;")->fetch_array();
            $to_db = hash(self::CODES[(int) $code_arr['vl_code']], $word);
        }
        else $to_db = $word;
        $qr_ch = $this->connection->query("UPDATE tb_signatures SET vl_password = \"$to_db\" WHERE cd_signature = $signature;");
        unset($qr_ch);
        unset($to_db);
    }

    /**
     * Searches in the database for a singature wich the proprietary FK is the same as the parameter
     *
     * @param int $proprietary_needle The FK to search
     * @return array|null
     */
    public function qrSignatureProprietary(int $proprietary_neddle){
        $this->checkNotConnected();
        $qr_all = $this->connection->query("SELECT cd_signature FROM tb_signatures WHERE id_proprietary = $proprietary_neddle");
        $results = array();
        while($row = $qr_all->fetch_array()) array_push($results, $row['cd_signature']);
        return count($results) <= 0 ? null : $results;
    }

    /**
     * Searches in the database for a signature wich the vl_code is the same as the parameter
     *
     * @param int $code The vl_code to search
     * @return array|null
     */
    public function qrSignatureAlgo(int $code){
        $this->checkNotConnected();
        $results = [];
        $qr_all = $this->connection->query("SELECT cd_signature FROM tb_signatures WHERE vl_code = $code");
        while($row = $qr_all->fetch_array()) $results[] = $row['cd_signature'];
        return count($results) <= 0 ? null : $results;
    }

    /**
     * That method sends a e-mail for all the users and proprietaries alerting then that had a change on a signature, with a link to dowload then.
     *
     * @param int $proprietary The proprietary wich changed the signature.
     * @param int $signature_id The signature that the proprietary changed
     * @param array|null $exceptions The users to not send the email
     * @throws SignatureNotFound If the signature don't exists
     * @throws ProprietaryNotFound If the proprietary don't exists,
     * @return void;
     */
    public function sendChSignatureMail(int $proprietary, int $signature_id, string $html_template){
        $this->checkNotConnected();
        if(!$this->checkSignatureExists($signature_id)) throw new SignatureNotFound("The signature #$signature_id don't exists", 1);
        if(!$this->checkProprietaryExists($proprietary)) throw new ProprietaryNotFound("There's no proprietary with the PK #$proprietary", 1);
        $content_raw = file_get_contents($html_template);
        $qr_prp = $this->connection->query("SELECT nm_proprietary FROM tb_proprietaries WHERE cd_proprietary = $proprietary;")->fetch_array();
        $content_1 = str_replace("%prop%", $qr_prp['nm_proprietary'], $content_raw);
        $content_full = str_replace("%signature%", $signature_id, $content_1);
        $all_usr = $this->connection->query("SELECT vl_email FROM tb_users;");
        $all_prop = $this->connection->query("SELECT vl_email FROM tb_proprietaries WHERE cd_proprietary != $proprietary");
        $headers = "MIME-Version: 1.0\nContent-type: text/html; charset=iso-8859-1\nFrom: " . EMAIL_USING . "\n";
        while($row = $all_usr->fetch_array()) mail($row['vl_email'],"Signature Update", $content_full, $headers);
        while($row = $all_prop->fetch_array()) mail($row['vl_email'], "Signature Update", $content_full, $headers);
    }

    /**
     * Returns all the database data of a specific signature, got from a file. Obviously after checking the signature file it will return the data.
     * If the signature is invalid then it will return null, if the checkSignatureFile doesn't throw a Exception before.
     * In the array will be the data of the JSON in the signature file.
     * -------------------------------------------------------------------------------------------------------------------------------------------
     * @param string $file_name The name of the file, wich need to be at the /usignatures.d folder at the root.
     * @return array|null
     */
    public function getSignatureFData(string $file_name){
        $this->checkNotConnected();

        $content_file = file_get_contents("/usignatures.d/" . $file_name);
        $exp_content = explode(self::DELIMITER, $content_file);
        $ascii_pr = array();
        for($i = 0; $i < count($exp_content); $i++) $ascii_pr[] = chr((int) $exp_content[$i]);
        $fn_json = implode("", $ascii_pr);
        $parsed_json = json_decode($fn_json, true);
        return $parsed_json;
    }

    /**
     * Authenticate the signature content, only the signature file content.
     * @param string $content The signature file content to authenticate;
     * @return bool If the signature content is valid or not
     */
    public function authSignatureCon(string $content){
        $this->checkNotConnected();
        $con_k = explode(self::DELIMITER, $content);
        $jsonr_con = "";
        for($i = 0; $i < count($con_k); $i++){
            $jsonr_con .= chr((int) $con_k[$i]);
        }
        $json_con = json_decode($jsonr_con, true);
        $signature_data = $this->connection->query("SELECT vl_password FROM tb_signatures WHERE cd_signature = " . $json_con['ID'] . ";")->fetch_array();
        return $signature_data['vl_password'] == $json_con['Signature'];
    }

    /**
     * Handle the other methods to reach the PK of a specific proprietary
     * @param string|integer $proprietary The proprietary value received
     * @return integer|null If the type of the param is integer or string and
     *                      the proprietary exists it returns the proprietary's
     *                      PK. Otherwise it'll return null
     */
    private function hndProprietaryId($proprietary){
        $this->checkNotConnected();
        if(is_int($proprietary) || is_numeric($proprietary)){
            // checks if the proprietary exists.
            $dp = $this->connection->query("SELECT COUNT(cd_proprietary) FROM tb_proprietaries WHERE cd_proprietary = $proprietary;");
            if((int)$dp->fetch_array()[0] != 1) return null;
            else return (int)$proprietary;
        }
        else if(is_string($proprietary)){
            $dp = $this->connection->query("SELECT COUNT(cd_proprietary), cd_proprietary FROM tb_proprietaries WHERE nm_proprietary = \"$proprietary\";")->fetch_array();
            if((int)$dp[0] != 1) return null;
            else return (int)$dp[1];
        }
        else return null;
    }

    /**
     * Uses the internal database procedure older_signatures to filter the signatures
     * of a specific proprietary and return they sorted by the older date.
     * @param string|integer $proprietary The proprietary who own's the signatures
     * @return array
     */
    public function filterOlder($proprietary): array{
        $this->checkNotConnected();
        $results = [];
        $id_P = $this->hndProprietaryId($proprietary);
        if(is_null($id_P) || $id_P === null) return null;
        $sorted = $this->connection->query("CALL older_Signatures($id_P);");
        while($row = $sorted->fetch_array()) $results[] = $row;
        return $results;
    }

    /**
     * Uses the internal database procedure newer_Signatures to filter the signatures
     * of a specific proprietary and return they sorted by the newer date.
     * @param string|integer $proprietary The proprietary who owns the signatures
     * @return array;
     */
    public function filterNewer($proprietary): array{
        $this->checkNotConnected();
        $results = [];
        $id_P = $this->hndProprietaryId($proprietary);
        if(is_null($id_P) || $id_P === null) return null;
        $sorted = $this->connection->query("CALL newer_Signatures($id_P);");
        while($row = $sorted->fetch_array()) $results[] = $row;
        return $results;
    }

    /**
     * Uses the internal database procedure md5_Signatures to filter the signatures
     * of a specific proprietary and return only the MD5 encoded signatures
     * @param string|integer $proprietary The proprietary who owns the signatures
     * @return array
     */
    public function filterMd5($proprietary): array{
        $this->checkNotConnected();
        $results = [];
        $id_P = $this->hndProprietaryId($proprietary);
        if(is_null($id_P) || $id_P === null) return null;
        $sorted = $this->connection->query("CALL md5_Signatures($id_P);");
        while($row = $sorted->fetch_array()) $results[] = $row;
        return $results;
    }

    /**
     * Uses the internal database procedure sha1_Signatures to filter the signatures
     * of a specific proprietary and return only the SHA1 encoded signatures
     * @param string|integer $proprietary The proprietary who owns the signatures
     * @return array
     */
    public function filterSha1($proprietary): array{
        $this->checkNotConnected();
        $results = [];
        $id_P = $this->hndProprietaryId($proprietary);
        if(is_null($id_P) || $id_P === null) return null;
        $sorted = $this->connection->query("CALL sha1_Signatures($id_P);");
        while($row = $sorted->fetch_array()) $results[] = $row;
        return $results;
    }

    /**
     * Uses the internal database procedure sha256_Signatures to filter the signatures
     * of a specific proprietary and return only the SHA256 encoded singatures
     * @param string|integer $proprietary The proprietary who owns the signatures
     * @return array
     */
    public function filterSha256($proprietary): array{
        $this->checkNotConnected();
        $results = [];
        $id_P = $this->hndProprietaryId($proprietary);
        if(is_null($id_P) || $id_P === null) return null;
        $sorted = $this->connection->query("CALL sha256_Signatures($id_P);");
        while($row = $sorted->fetch_array()) $results[] = $row;
        return $results;
    }

    /**
     * Creates a new query based on params received from an array
     * used normally by the AJAX pages, and mainly by the "ajx_signatures.php" page
     * @param array $parameters It can be a array ready to be used
     * @return array The results of the query
     */
    public function fastQuery(array $parameters): array{
        $this->checkNotConnected();
        $addedFirst = false; // if one parameter was already used in the query
        $qr_str = "SELECT * FROM tb_signatures ";
        if(count($parameters) == 0) $qr_str .= ";";
        else{
            $qr_str .= "WHERE ";
            foreach($parameters as $field => $value){
                $q_param = $addedFirst ? " AND $field = " : "$field = ";
                $q_param .= is_numeric($value) && is_string($value) ? "\"$value\"" : "$value";
                $qr_str .= $q_param;
                if(!$addedFirst) $addedFirst = true;
            }
            $qr_str .= ";";
        }
        $results = $this->connection->query($qr_str);
        $a_results = [];
        while($row = $results->fetch_array()) $a_results[] = $row;
        return $a_results;
    }

    /**
     * Creates a update query with many parameters specified using a associative
     * array
     * @param array $parameters The associative array with the parameters
     * @param integer $signature The referred signature to UPDATE
     * @param boolean $encode_passwd If the method will encode the signature password received
     *                               if this option is true, then the code must be included in the parameters
     * @return void
     */
    public function fastUpdate(array $parameters, int $signature, bool $encode_passwd = true): void{
        $this->checkNotConnected();
        if(isset($parameters["vl_password"]) && isset($parameters["vl_code"]) && $encode_passwd){
            if(!$this->checkSignatureExists($signature)) throw new SignatureNotFound($signature);
            // $parameters["vl_password"] = $this->encode_passwd($parameters["vl_password"], (int)$parameters["vl_code"]);
            // $this->chSignaturePassword($signature, $parameters['vl_password'], true);
            $this->chSignatureCode($signature, (int)$parameters["vl_code"], $parameters["vl_password"]);
        }
        return ;
        // TODO: Change the docs of this method (SignaturesData::fastUpdate)
    }
}
?>
