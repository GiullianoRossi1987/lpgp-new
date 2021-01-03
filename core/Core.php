<?php
namespace Core;
use Exception;
try{
    require_once  "core/Exceptions.php";
    require_once  "config/configmanager.php";
    require_once  "core/control/controllers.php";
}
catch(Exception $e){
    require_once $_SERVER['DOCUMENT_ROOT'] . "/core/Exceptions.php";
}
// add the logs manager after.

use mysqli;
use mysqli_result;
use mysqli_sql_exception;

// Exceptions
use DatabaseActionsExceptions\AlreadyConnectedError;
use DatabaseActionsExceptions\NotConnectedError;

use SignaturesExceptions\InvalidSignatureFile;
use SignaturesExceptions\SignatureAuthError;
use SignaturesExceptions\SignatureNotFound;
use SignaturesExceptions\SignatureFileNotFound;
use SignaturesExceptions\VersionError;

use CheckHistory\InvalidErrorCode;

use CheckHistory\RegisterNotFound;


use PropCheckHistory\InvalidErrorCode as PropInvalidCode;
use PropCheckHistory\RelatoryError as PropRelatoryError;
use PropCheckHistory\RegisterNotFound as PropRegisterNotFound;

use ClientsExceptions\AccountError;
use ClientsExceptions\AuthenticationError as ClientAuthenticationError;
use ClientsExceptions\ClientNotFound;
use ClientsExceptions\ClientAlreadyExists;
use ClientsExceptions\ProprietaryReferenceError;
use ClientsExceptions\TokenReferenceError;

use ClientsAccessExceptions\ReferenceError;
use ClientsAccessExceptions\SuccessValueError;

use Configurations\ConfigManager;
use Control\ClientsController;
use Control\SignaturesController;

$gblConfig = new ConfigManager($_SERVER["DOCUMENT_ROOT"]. "/config/mainvars.json");

define("DEFAULT_HOST", "127.0.0.1");
define("DEFAULT_DB", "LPGP_WEB");
define("ROOT_VAR", $_SERVER['DOCUMENT_ROOT']);
define("EMAIL_USING", "lpgp@gmail.com");
define("DEFAULT_USER_ICON", "/media/user-icon.png");
define("DEFAULT_DATETIME_F", "Y-m-d H:i:s");
define("LPGP_CONF", $gblConfig->getConfig());
define("CONTROL_FILE", "core/control/control.json");


// Clients constants
if(!defined("U_CLIENTS_CONF")) define("U_CLIENTS_CONF", "/u.clients/");
if(!defined("G_CLIENTS_CONF")) define("G_CLIENTS_CONF", "/g.clients/");
if(!defined("TMP_GCLIENTS")) define("TMP_GCLIENTS", "/g.clients/tmp/");
if(!defined("TMP_UCLIENTS")) define("TMP_UCLIENTS", "/u.clients/tmp/");

/**
 * That class contains the main connection to the database and him universal actions,
 * such as connect, disconnect and get connection info.
 * @var mysqli $connection The main connection with the database.
 * @var string $database_connected The database wich is connected.
 * @var string $host_using The host/IP using for the database server connection.
 * @var bool $got_connection If the class is connected to a MySQL database
 * @var string $user_connected The user that's doing the connection.
 * @author Giulliano Ross <giulliano.scatalon.rossi@gmail.com>
 */
class DatabaseConnection{
    protected $connection;
    protected $database_connected;
    protected $host_using;
    protected $got_connection;
    protected $user_connected;

    /**
     * Checks if the class's connected to a database.
     * @param bool $auto_throw If there's no connection, if the method will throw the error by default.
     * @throws NotConnectedError If there's no connection, and the method is allowed to throw that exception.
     * @return bool|void
     */
    public function checkNotConnected(bool $auto_throw = true){
        if(!$this->got_connection){
            if($auto_throw) throw new NotConnectedError("There's no connection with a MySQL database!", 1);
            else return false;
        }
        else return true;
    }

    /**
     * Starts the class and the connection with a MySQL database.
     * @param string $user The user using for the connection.
     * @param string $passwd The user password.
     * @param string $host The host/IP to connect.
     * @param string $db The database to connect.
     * @throws AlreadyConnectedError If the class already haves a connection running.
     */
    public function __construct(string $user, string $passwd, string $host = DEFAULT_HOST, string $db = DEFAULT_DB){
        if($this->got_connection) throw new AlreadyConnectedError("There's a connection with a MySQL database already", 1);
        $this->connection = new mysqli($host, $user, $passwd, $db);
        $this->database_connected = $db;
        $this->host_using = $host;
        $this->user_connected = $user;
        $this->got_connection = true;
        $this->connection->autocommit(true);
    }

    /**
     * Destrois the class and also closes the connection to a MySQL database.
     */
    public function __destruct(){
        mysqli_close($this->connection);
        $this->user = "";
        $this->database_connected = "";
        $this->host_using = "";
        $this->got_connection = false;
    }

    /**
     * Returns the protected attribute of the mysqli_connection. If it is connected, if doens't connected will return null.
     * @return mysqli|null
     */
    public function getConnectionAttr(){
        return $this->got_connection ? $this->connection : null;
    }
}

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
            if(!file_exists("/signatures.d/signature-file-". $local_counter . ".lpgp"))
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
        $root = $_SERVER['DOCUMENT_ROOT'];
        file_put_contents("/signatures.d/" . $file_name, $content_file);
        $controller->addDownloadRecord($signature_id, $dtk, $content['Date-Creation']);
        unset($controller);
        return $HTML_mode ? "<a href=\"https://lpgpofficial.com/signatures.d/$file_name\" download=\"$file_name\" role=\"button\" class=\"btn btn-lg downloads-btn btn-primary\">Get your signature #$signature_id here!</a>" : "$root/signatures.d/$file_name";
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
        $act_code = $this->connection->query("SELECT vl_code FROM tb_signatures WHERE cd_signature = $signature;")->fetch_array();
        $to_db = hash(self::CODES[(int) $act_code['vl_code']], $word_same);
        $qr_ch = $this->connection->query("UPDATE tb_signatures SET vl_code = $code WHERE cd_signature = $signature;");
        $qr_ch = $this->connection->query("UPDATE tb_signatures SET vl_password = \"$to_db\" WHERE cd_signature = $signature;");
        unset($qr_ch);
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
}

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
            $signature_data_html .= "<div class=\"card-subtitle\"><a href=\"https://localhost/proprietary.php?id=" . $prop_data['cd_proprietary'] . "\">Proprietary: " . $prop_data['nm_proprietary'] . "</a>\n</div>\n";
            $signature_data_html .= "<div class=\"card-footer text-muted\"> Created at: " . $sign_data['dt_creation'] . "</div>\n</div>\n";
            $data_html .= $signature_data_html;
        }
        return $data_html;
    }

    /**
     * Returns all the HTML of the history from a user, using the cards to represents the relatories. That method was created for make faster the
     * development of the profile account page, wich have that history of the checked signatures in both types of accounts.
     *
     * @param string $nm_user The name of the user, normally used with the $_SESSION['user'].
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
            $card_main .= "<a href=\"https://localhost/relatory.php?rel=" . base64_encode($dt['cd_reg']) . "\" target=\"__blanck\" role=\"button\" class=\"btn btn-secondary\">Check the relatory</a>\n";
            $card_main .= "<div class=\"card-footer text-muted\">Checked signature at: " . $dt['dt_reg'] . "</div>\n</div>\n<div>\n<div>\n</div>\n</div>\n</div>";
            $main_pg .= $card_main . "<br><br>";
        }
        return $main_pg;
    }
}

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
            $card_div .= "<h4 class=\"card-subtitle\"> Proprietary: <a href=\"https://localhost/proprietary.php?id=$id_prop\" target=\"_blanck\"> " . $prop_dt['nm_proprietary'] . "</a></div>\n";
            $card_div .= "<div class=\"card-footer\">Created at: " . $sig_dt['dt_creation'] . "</div>\n</div>\n<div>\n</div>";
            $main_data_html .= $card_div;
        }
        return $main_data_html;
    }


    /**
     * Returns all the HTML of the history from a proprietary user, using the cards to represents the relatories. That method was created for make faster the
     * development of the profile account page, wich have that history of the checked signatures in both types of accounts.
     *
     * @param string $nm_proprietary The name of the user, normally used with the $_SESSION['user'].
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
            $img_span = $dt['vl_code'] == 0 ? "https://localhost/media/checked-valid.png" : "https://localhost/media/checked-invalid.png";
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
            $prop_data_html = is_null($prop_dt) ? "<div class=\"prop-nf-err\">(We can't find the proprietary, probabily he deleted him account)</div>\n" : "<a href=\"https://localhost/proprietary.php?id=$cdId\" target=\"_blanck\" class=\"prop-link\">" . $prop_dt['nm_proprietary'] . "</a>\n";
            $card_main .= "Proprietary: " . $prop_data_html;
            $card_main .= "<a href=\"https://localhost/relatory.php?rel=" . $dt['cd_reg'] . "\" target=\"__blanck\" role=\"button\" class=\"btn btn-secondary\">Check the relatory</a>\n";
            $card_main .= "<div class=\"card-footer text-muted\">Checked signature at: " . $dt['dt_reg'] . "</div>\n</div>\n</div>\n<div>\n</div>";
            $main_pg .= $card_main . "<br>";
        }
        return $main_pg;
    }
}

/**
 * That class manages the clients data, creating and authenticating clients files.
 * @var string DELIMITER The standard constant used for the
 */
class ClientsData extends DatabaseConnection{
    const DELIMITER = "/";

    /**
     * That method checks if a client reference exist or not. That reference received as a parameter is the client
     * primary key.
     *
     * @param integer $client_ref The primary key client reference
     * @return boolean
     */
    private function ckClientEx(int $client_ref){
        $this->checkNotConnected();
        $qr = $this->connection->query("SELECT COUNT(cd_client) FROM tb_clients WHERE cd_client = $client_ref;")->fetch_array();
        return $qr[0] > 0;
    }

    /**
     * That method check if a client reference exist or not, but using the client token.
     *
     * @param integer $token_cl The token reference to search.
     * @return boolean
     */
    private function ckTokenClientEx(string $token_cl){
        $this->checkNotConnected();
        $qr = $this->connection->query("SELECT COUNT(cd_client) FROM tb_clients WHERE tk_client = \"$token_cl\";")->fetch_array();
        return $qr[0] > 0;
    }

    /**
     * Checks if a reference of a client exists or not, it's just the ckClientEx
     * with public access and two types of references.
     *
     * @param string|integer $reference The client reference, it can be the client name
     *                                  (string), or the PK (int)
     * @return boolean
     */
    public function checkClientExists($reference): bool{
        $this->checkNotConnected();
        if(is_int($reference))
            $qr = $this->connection->query("SELECT COUNT(cd_client) FROM tb_clients WHERE cd_client = $reference;");
        else if(is_string($reference))
            $qr = $this->connection->query("SELECT COUNT(cd_client) FROM tb_clients WHERE nm_client = \"$reference\";");
        else return null;
        return $qr->fetch_array()[0] > 0;
    }

    /**
     * That method check if a proprietary primary key reference exists in the tb_proprietaries
     *
     * @param integer $reference The primary key reference to check.
     * @return boolean
     */
    private function ckPropRef(int $reference){
        $this->checkNotConnected();
        $qr_ref = $this->connection->query("SELECT COUNT(cd_proprietary) AS counted FROM tb_proprietaries WHERE cd_proprietary = $reference;")->fetch_array();
        return (int)$qr_ref['counted'] > 0;
    }

    /**
     * That method generate the clients configurations file and the clients authentication file name and return the link for
     * those files in array form.
     *
     * @return string
     */
    private static function pathZipGen(): string{
        // Client auth.
        $ind2 = 0;
        $auth_nm = "";
        do{
            $auth_nm = "auth_client_" . $ind2 . ".lpgp";
            $ind2++;
        }while(file_exists(TMP_GCLIENTS . "/" . $auth_nm) || strlen($auth_nm) == 0);

        return TMP_GCLIENTS . "$auth_nm";
    }

    /**
     * That method transforms any file path to a HTML download link
     *
     * @param string $path The file path to load.
     * @return string
     */
    private static function passHTML(string $path): string{
        $nm_get1 = explode("/", $path);
        $nm = $nm_get1[count($nm_get1) - 1];
        return '<a href="' . $path .'" download="' . $nm . '" role="button" class="btn btn-lg btn-primary">Download authentication <i class="far fa-file-archive"></i></a>';
    }

    /**
     * That method generates two clients files, the client configurations file and the .lpgp authentication file.
     * The difference between those files is the use for the system, the configurations file is used by the client
     * (SDK) to him know what kind of client account it isit is.
     *
     * @param integer $client_pk_ref The client primary key reference to generate the file.
     * @throws ClientNotFound If the reference doesn't exist.
     * @return string The zip file with the clients files for downlaod.
     */
    public function genConfigClient(int $client_pk_ref): string{
        $this->checkNotConnected();
        if(!$this->ckClientEx($client_pk_ref)) throw new ClientNotFound("There's no client #$client_pk_ref", 1);
        $cldt = $this->connection->query("SELECT tk_client, vl_root, id_proprietary, nm_client, nm_client FROM tb_clients WHERE cd_client = $client_pk_ref;")->fetch_array();
        $files = $this->pathZipGen();
        $controller = new ClientsController(CONTROL_FILE);
        $tk = $controller->generateDownloadToken();
        $json_aut = array(
            "Client" => $client_pk_ref,
            "Proprietary" => (int)$cldt['id_proprietary'],
            "Token" => $cldt['tk_client'],
            "Dt" => date("Y-m-d H:i:s"),
            "cdtk" => $tk
        );
        $dumped_a = json_encode($json_aut);
        $encoded_ar = [];
        $exp = str_split($dumped_a);
        foreach($exp as $char) $encoded_ar[] = (string)ord($char);
        $encoded = implode(self::DELIMITER, $encoded_ar);
        file_put_contents($files, $encoded);
        $controller->addDownloadRecord($client_pk_ref, $tk, $json_aut['Dt'], true);
        unset($controller);
        $file_n = str_replace($_SERVER['DOCUMENT_ROOT'], "", $files);
        return $this->passHTML($file_n);
    }

    /**
     * That method return the client integer primary key reference quering by his name.
     *
     * @param string $name The client name to query
     * @throws ClientNotFound If the client name doesn't exist.
     * @return integer The client primary key reference.
     */
    private function getClientID(string $name) : int{
        $this->checkNotConnected();
        $qr_all = $this->connection->query("SELECT COUNT(cd_client) AS exist, cd_client FROM tb_clients WHERE nm_client = \"$name\";")->fetch_array();
        if($qr_all['exist'] == 0) throw new ClientNotFound("There's no client '$name'",1 );
        return $qr_all['cd_client'];
    }

    /**
     * That method authenticate a client authentication file. To be valid the data encoded on the file content
     * must be valid.
     *
     * @param string $auth_path The client authentication file path, normally located at the u.clients folder
     * @throws ClientAuthenticationError If the authentication file isn't valid.
     * @return true Only if the file is valid.
     */
    public function authClient(string $auth_path) : bool{
        $this->checkNotConnected();
        $content = file_get_contents($auth_path);
        $exp = explode(self::DELIMITER, $content);
        $controller = new ClientsController(CONTROL_FILE);
        if(!$controller->authExtDownloadFile($auth_path)) return false;
        unset($controller);
        $json_con = "";
        foreach($exp as $chr) $json_con .= chr((int) $chr);
        $data = json_decode($json_con, true);
        if(!$this->ckClientEx((int)$data['Client'])) throw new ClientAuthenticationError("The client authentication file isn't valid. The client doesn't exists.", 1);
        if(!$this->ckPropRef($data['Proprietary'])) throw new ClientAuthenticationError("The client authentication file isn't valid. The proprietary don't exist.", 1);
        $qr_tk = $this->connection->query("SELECT tk_client FROM tb_clients WHERE cd_client = " . $data['Client'] . ";")->fetch_array();
        if($qr_tk['tk_client'] != $data['Token']) throw new ClientAuthenticationError("The client isn't valid. Token error.", 1);
        return true;
    }

    /**
     * That method decodes the authentication file. The data decoded is passed to identify the client.
     * If the client doesn't exist, or the file isn't valid, it will return the brute data.
     *
     * @param string $auth_file The authentication file to get the data
     * @return array The array with the following data structure:
     *  'brute' => The brute data extracted from the authentication file.
     *  'soft'  => The client data, if it's valid.
     *  'valid' => If the authentication file is valid.
     */
    public function getClientAuthData(string $auth_file): array{
        $this->checkNotConnected();
        $arr_rt = array();
        $content = file_get_contents($auth_file);
        $exp_brt = explode(self::DELIMITER, $content);
        $bruteJSON = "";
        foreach($exp_brt as $brtChar) $bruteJSON .= chr((int) $brtChar);
        $bruteData = json_decode($bruteJSON, true);
        $arr_rt['brute'] = $bruteData;
        try{
            $res = $this->authClient($auth_file);
            if($res){
                $arr_rt['soft'] = $this->getClientData($bruteData['Client']);
                $arr_rt['valid'] = true;
            }
            else{
                $arr_rt['soft'] = $this->getClientData($bruteData['Client']);
                $arr_rt['valid'] = false;
                $arr_rt['error'] = "Invalid Client Authentication File";
            }
        }
        catch(ClientAuthenticationError $e){
            $arr_rt['soft'] = null;
            $arr_rt['valid'] = false;
            $arr_rt['error'] = $e->getMessage();
        }
        return $arr_rt;
    }

    /**
     * That method generates a client new token. Used when the class creates a new client or when changes the client token.
     * It check if the random token already exists, if it exists will
     *
     * @return string The new token generated.
     */
    private function genTk() : string{
        $this->checkNotConnected();
        $tk = "";
        do{
            for($i = 0; $i < 4; $i++) $tk .= (string)random_int(0, 9);
        }while($this->ckTokenClientEx(base64_encode($tk)));
        return base64_encode($tk);
    }

    /**
     * Method created to get the proprietary reference by the name.
     *
     * @param string $proprietary The proprietary name reference
     * @return integer|null Null if the proprietary doesn't exist.
     */
    private function rtPropID(string $proprietary){
        $this->checkNotConnected();
        $qr_all = $this->connection->query("SELECT cd_proprietary, COUNT(cd_proprietary) AS exists_prop FROM tb_proprietaries WHERE nm_proprietary = \"$proprietary\";")->fetch_array();
        return $qr_all['exists_prop'] > 0 ? (int) $qr_all['cd_proprietary'] : null;
    }

    /**
     * That method adds a new client to the clients database. To add the new client to the
     *
     * @param string $client_name The client name
     * @param string $proprietary The client owner proprietary name reference.
     * @param boolean $root_mode If the client will have root permissions.
     * @param integer|null $tk The client token, if null it will be generated.
     * @throws ClientAlreadyExists If the client name is already in use by another client.
     * @throws ProprietaryReferenceError If the proprietary referenced doesn't exist.
     * @throws TokenReferenceError If the client token selected already exists in the database.
     * @return void
     */
    public function addClient(string $client_name, string $proprietary, bool $root_mode = false, ?string $tk = null) : void{
        $this->checkNotConnected();
        try{
            if($this->ckClientEx($this->getClientID($client_name)))
                throw new ClientAlreadyExists("The name '$client_name' is already in use", 1);
            }
        catch(ClientNotFound $e){
            $prp = $this->rtPropID($proprietary);
            if(!$this->ckPropRef($prp)) throw new ProprietaryReferenceError("There's no proprietary #$proprietary", 1);
            $vl_root = $root_mode ? 1 : 0;
            $tk_client = 0;
            if(!is_null($tk)){
                if($this->ckTokenClientEx($tk)) throw new TokenReferenceError("That token is already in use.", 1);
                $tk_client = $tk;
            }
            else $tk_client = $this->genTk();
            $qr_add = $this->connection->query("INSERT INTO tb_clients (nm_client, id_proprietary, vl_root, tk_client) VALUES (\"$client_name\", $prp, $vl_root, \"$tk_client\");");
            return ;
        }
    }

    /**
     * Removes a client from the database.
     *
     * @param string|integer $client The client reference, it can be the client name (string) or the client primary key (integer)
     * @throws ClientNotFound If the reference isn't valid.
     * @return void
     */
    public function rmClient($client) : void{
        $this->checkNotConnected();
        $client_vl = is_string($client) ? $this->getClientID($client) : $client;
        if(!$this->ckClientEx($client_vl)) throw new ClientNotFound("The client referenced doesn't exist.", 1);
        $qr_rm = $this->connection->query("DELETE FROM tb_clients WHERE cd_client = $client_vl;");
        return ;
    }

    /**
     * That method changes the client name.
     *
     * @param string|integer $client The client reference, can be the actual name (string) or the client primary key (integer)
     * @param string $new_name The new client name.
     * @throws ClientAlreadyExists If the client name is already in use.
     * @return void
     */
    public function chClientName($client, string $new_name) : void{
        $this->checkNotConnected();
        try{
            $id = $this->getClientID($new_name);
            unset($id);
        }
        catch(ClientNotFound $e){
            $ref = is_string($client) ? $this->getClientID($client) : $client;
            if(!$this->ckClientEx($ref)) throw new ClientNotFound("There's no client ($client)", 1);
            $qr = $this->connection->query("UPDATE tb_clients SET nm_client = \"$new_name\" WHERE cd_client = $ref;");
            unset($ref);
            return ;
        }
        throw new ClientAlreadyExists("The name '$new_name' is already in use;", 1);
    }

    /**
     * That method generates a new token for a client
     *
     * @param string|integer $client The client reference, it can be the name (string) or the primary key (integer)
     * @var integer $ref The client reference
     * @throws ClientNotFound If the client reference doesn't exists.
     * @return void
     */
    public function genNewTK($client) : void{
        $this->checkNotConnected();
        $ref = is_string($client) ? $this->getClientID($client) : $client;
        if(!$this->ckClientEx($ref)) throw new ClientNotFound("There's no client ($client)", 1);
        $new_tk = $this->genTk();
        $qr_ch = $this->connection->query("UPDATE tb_clients SET tk_client = \"$new_tk\" WHERE cd_client = $client;");
        unset($new_tk);
    }

    /**
     * That method changes the root permissions value of the client selected.
     *
     * @param string|integer $client The client reference, it can be the client name (string value) or the client primary key (integer value)
     * @param boolean|integer $grant_root If the method will grant root permissions or revoke root permissions from the client.
     * @throws ClientNotFound If the client reference isn't valid.
     * @return void
     */
    public function chClientPermissions($client, $root = 0) : void{
        $this->checkNotConnected();
        $ref = is_string($client) ? $this->getClientID($client) : (integer) $client;
        if(!$this->ckClientEx($ref)) throw new ClientNotFound("There's no client ($client)", 1);
        $vl_root = is_bool($root) ? (int) $root : $root;
        $qr_ch = $this->connection->query("UPDATE tb_clients SET vl_root = $vl_root WHERE cd_client = $ref;");
        return ;
    }

    /**
     * That method returns all the clients of a proprietary, in a normal array. The array will have the clients name and ID
     *
     * @param string $proprietary The proprietary primary key reference to search in the clients table
     * @return array
     */
    public function getClientsByOwner(string $proprietary): array{
        $this->checkNotConnected();
        $prp = $this->rtPropID($proprietary);
        $qr_all = $this->connection->query("SELECT * FROM tb_clients WHERE id_proprietary = $prp;");
        if($qr_all === false) die($this->connection->error);
        $rt_arr = [];
        while($row = $qr_all->fetch_array()){
            $rt_arr[] = $row;
        }
        return $rt_arr;
    }

    /**
     * That method returns the necessary data of a client to create her card, it's normally used in the my-clients.php page.
     * The required data is the client ID (primary key reference), the client name and the number of access of the client.
     *
     * @param integer $client The client primary key reference to get the required data.
     * @throws ClientNotFound If the client referred don't exist.
     * @return array
     */
    public function getClientCardData(int $client): array{
        $this->checkNotConnected();
        if(!$this->ckClientEx($client)) throw new ClientNotFound("There's no client #$client.", 1);
        $qr_nm = $this->connection->query("SELECT nm_client FROM tb_clients WHERE cd_client = $client;")->fetch_array();
        $qr = $this->connection->query("SELECT COUNT(cd_access) accesses FROM tb_access WHERE id_client = $client;")->fetch_array();
        if($qr_nm === false || $qr === false) die($this->connection->error);
        return [$client, $qr_nm['nm_client'], $qr['accesses']];
    }

    /**
     * That method loads all the data from a client selected.
     *
     * @param integer $client The client primary key reference
     * @throws ClientNotFound If the reference isn't valid
     * @return array
     */
    public function getClientData(int $client): array{
        $this->checkNotConnected();
        if(!$this->ckClientEx($client)) throw new ClientNotFound("There's no client #$client", 1);
        $qr = $this->connection->query("SELECT * FROM tb_clients WHERE cd_client = $client;");
        if($qr === false) die($this->connection->error);
        return $qr->fetch_array();
    }

    /**
     * That method searchs all the clients using a needle at the name.
     * It returns all of then in a array;
     *
     * @param string $needle The name neddle to search
     * @return array
     */
    public function qrAllClients(string $needle): array{
        $this->checkNotConnected();
        $qr_all = $this->connection->query("SELECT * FROM tb_clients WHERE cd_client LIKE \"%$needle%\";");
        $results = [];
        while($row = $qr_all->fetch_array()) $results[] = $row;
        return $results;
    }

    /**
     * That method searchs all the clients of a proprietary using a needle of
     * the client name.
     *
     * @param string $neddle The client name neddle to search
     * @param string|integer $proprietary The proprietary name/ID of the client
     * @return array
     */
    public function qrClientsOfProp(string $needle, $proprietary): array{
        $this->checkNotConnected();
        $results = [];
        $qr_all = null;
        if(!is_numeric($proprietary)){
            $qr_all = $this->connection->query("SELECT cl.* FROM tb_clients AS cl INNER JOIN tb_proprietaries AS p ON p.cd_proprietary = cl.id_proprietary WHERE p.nm_proprietary = \"$proprietary\" AND cl.nm_client LIKE \"%$needle%\";");
        }
        else{
            $qr_all = $this->connection->query("SELECT * FROM tb_clients WHERE id_proprietary = $proprietary AND nm_client LIKE \"%$needle%\";");
        }
        if($qr_all !== false && !is_null($qr_all)){
            while($row = $qr_all->fetch_array()) $results[] = $row;
        }
        return $results;
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
     * Uses the internal database procedure AZClientsFrom to sort the clients alphabetically of
     * a specific proprietary
     * @param string|integer $proprietary The proprietary who owns the clients
     * @return array
     */
    public function sortAZ($proprietary): array{
        $this->checkNotConnected();
        $results = [];
        $id_P = $this->hndProprietaryId($proprietary);
        if(is_null($id_P) || $id_P === null) return null;
        $sorted = $this->connection->query("CALL AZClientsFrom($id_P);");
        while($row = $sorted->fetch_array()) $results[] = $row;
        return $results;
    }

    /**
     * Uses the internal database procedure ZAClientsFrom to sort the clients alphabetically reversed
     * of a specific proprietary.
     * @param string|integer $proprietary The proprietary who owns the clients
     * @return array
     */
    public function sortZA($proprietary): array{
        $this->checkNotConnected();
        $results = [];
        $id_P = $this->hndProprietaryId($proprietary);
        if(is_null($id_P) || $id_P === null) return null;
        $sorted = $this->connection->query("CALL ZAClientsFrom($id_P);");
        while($row = $sorted->fetch_array()) $results[] = $row;
        return $results;
    }
}

/**
 * That class represents the access of the clients, all the clients which accessed the main server are here. Only the success full
 * access are storaged at the access table.
 * In that class we have the main methods for querys and a special method which returns the main data to the access plot.
 *
 */
class ClientsAccessData extends DatabaseConnection{

    /**
     * That method check if the client referenced is valid or not. To be valid the client must exist.
     *
     * @param integer $client_ref The client primary key received.
     * @return boolean
     */
    private function ckClientRef(int $client_ref): bool{
        $this->checkNotConnected();
        $qr = $this->connection->query("SELECT COUNT(cd_client) AS Tot FROM tb_clients WHERE cd_client = $client_ref;")->fetch_array();
        return $qr['Tot'] > 0;
    }

    /**
     * That method adds a access record in the clients access table.
     *
     * @param integer $client The client primary key reference.
     * @param integer|boolean $success If the access was successfull
     * @throws ReferenceError If the client referred isn't valid
     * @throws SuccessValueError If the success value received isn't valid.
     * @return void
     */
    public function addRecord(int $client, $success = 1){
        $this->checkNotConnected();
        if(!$this->ckClientRef($client)) throw new ReferenceError("The client referred doesn't exist.", 1);
        $suc = is_bool($success) ? (int) $success : $success;
        if($success != 0 && $success != 1) throw new SuccessValueError("Invalid success value!", 1);
        $qr_add = $this->connection->query("INSERT INTO tb_access (id_client, vl_success) VALUES ($client, $suc);");
        unset($suc);
    }

    /**
     * That method returns all the access of a client. If you want to get the total of access, just count the array returned
     *
     * @param integer $client The client reference to search
     * @throws ReferenceError If the client referece isn't valid.
     * @return array
     */
    public function getAccessClient(int $client): array{
        $this->checkNotConnected();
        if(!$this->ckClientRef($client)) throw new ReferenceError("There's no client #$client", 1);
        $qr_all = $this->connection->query("SELECT * FROM tb_access WHERE id_client = $client;");
        $rt_arr = array();
        while($row = $qr_all->fetch_array()) $rt_arr[] = $row;
        return $rt_arr;
    }

    /**
     * That method returns all the needed data of the client access to create a plot ordered by the year.
     * @param integer $client The client to get those data.
     * @throws ReferenceError If the client reference isn't valid
     * @return array
     */
    public function getPlotAccessData(int $client): array{
        $this->checkNotConnected();
        if(!$this->ckClientRef($client)) throw new ReferenceError("There's no client #$client", 1);
        $raw_query = $this->connection->query("SELECT Year(dt_access) AS Years, COUNT(cd_access) AS total_access FROM tb_access WHERE id_client = $client GROUP BY Years;");
        $rt = array();
        while($row = $raw_query->fetch_array()) $rt[$row['Years']] = $row;
        return $rt;
    }

    /**
     * That method return the main data needed to generate a chart of all the clients
     *
     * @param string $proprietary The proprietary logged name to get the data
     * @throws ProprietaryReferenceError If the referred proprietary don't exist.
     * @return array
     */
    public function getAllClientsChart(string $proprietary): array{
        $this->checkNotConnected();
        $objCl = new ClientsData("giulliano_php", "");
        $clients = $objCl->getClientsByOwner($proprietary);
        $arrData = [
            "Clients" => $clients
        ];

        $years = $this->connection->query("SELECT DISTINCT year(dt_access) AS Year FROM tb_access");
        while($yearRow = $years->fetch_array()) $arrData[$yearRow['Year']] = [];

        foreach($arrData as $year => $value){
            if($year != "Clients"){
                $clCount = [];
                for($i = 0; $i < count($arrData['Clients']); $i++){
                    $client = $arrData['Clients'][$i]['cd_client'];
                    $qrYear = $this->connection->query("SELECT Count(cd_access) AS access FROM tb_access WHERE year(dt_access) = $year AND id_client = $client GROUP BY id_client;")->fetch_array();
                    $clCount[] = (int)$qrYear['access'];
                }
                $arrData[$year] = $clCount;
            }
        }
        return $arrData;
    }

    /**
     * That method returns the chart data of all the client unsuccessful access record
     *
     * @param string $proprietary The proprietary name reference to search
     * @return array The chart needed data
     */
    public function getAllUnsuccesfulChart(string $proprietary): array{
        $this->checkNotConnected();
        $objCl = new ClientsData("giulliano_php", "");
        $clients = $objCl->getClientsByOwner($proprietary);
        $arrData = [
            "Clients" => $clients
        ];

        $years = $this->connection->query("SELECT DISTINCT year(dt_access) AS Year FROM tb_access");
        while($yearRow = $years->fetch_array()) $arrData[$yearRow['Year']] = [];

        foreach($arrData as $year => $value){
            if($year != "Clients"){
                $clCount = [];
                for($i = 0; $i < count($arrData['Clients']); $i++){
                    $client = $arrData['Clients'][$i]['cd_client'];
                    $qrYear = $this->connection->query("SELECT Count(cd_access) AS access FROM tb_access WHERE year(dt_access) = $year AND id_client = $client AND vl_success = 0 GROUP BY id_client;")->fetch_array();
                    $clCount[] = (int)$qrYear['access'];
                }
                $arrData[$year] = $clCount;
            }
        }
        return $arrData;
    }

    /**
     * That method returns the chart data of all the client successful access records
     *
     * @param string $proprietary The proprietary name reference to search
     * @return array The chart data.
     */
    public function getAllSuccessfulChart(string $proprietary): array{
        $this->checkNotConnected();
        $objCl = new ClientsData("giulliano_php", "");
        $clients = $objCl->getClientsByOwner($proprietary);
        $arrData = [
            "Clients" => $clients
        ];

        $years = $this->connection->query("SELECT DISTINCT year(dt_access) AS Year FROM tb_access");
        while($yearRow = $years->fetch_array()) $arrData[$yearRow['Year']] = [];

        foreach($arrData as $year => $value){
            if($year != "Clients"){
                $clCount = [];
                for($i = 0; $i < count($arrData['Clients']); $i++){
                    $client = $arrData['Clients'][$i]['cd_client'];
                    $qrYear = $this->connection->query("SELECT Count(cd_access) AS access FROM tb_access WHERE year(dt_access) = $year AND id_client = $client AND vl_success = 1 GROUP BY id_client;")->fetch_array();
                    $clCount[] = (int)$qrYear['access'];
                }
                $arrData[$year] = $clCount;
            }
        }
        return $arrData;
    }

    /**
     * That method get all the access of a client among the years.
     *
     * @param integer $client_cd The client primary key to search
     * @throws ClientNotFound If the client reference don't exist.
     * @return array The chart needed data
     */
    public function getClientAllAccess(int $client_cd): array{
        $this->checkNotConnected();
        if(!$this->ckClientRef($client_cd)) throw new ClientNotFound("There's no client #$client_cd", 1);
        $qrDt = new ClientsData("giulliano_php", "");
        $arrData = [
            "Clients" => [$qrDt->getClientData($client_cd)]
        ];

        $years = $this->connection->query("SELECT DISTINCT year(dt_access) AS Year FROM tb_access");
        while($yearRow = $years->fetch_array()) $arrData[$yearRow['Year']] = [];

        foreach($arrData as $year => $value){
            if($year != "Clients"){
                $clCount = [];
                $qrYear = $this->connection->query("SELECT Count(cd_access) AS access FROM tb_access WHERE year(dt_access) = $year AND id_client = $client_cd GROUP BY id_client;")->fetch_array();
                $clCount[] = (int)$qrYear['access'];
                $arrData[$year] = $clCount;
            }
        }
        return $arrData;
    }

    /**
     * That method returns the chart data of only the successful access of a specified client
     *
     * @param integer $client_cd The client primary key
     * @throws ClientNotFound If the referred client don't exist.
     * @return array
     */
    public function getClientSuccessfulAc(int $client_cd): array{
        $this->checkNotConnected();
        if(!$this->ckClientRef($client_cd)) throw new ClientNotFound("There's no client #$client_cd", 1);
        $qrDt = new ClientsData("giulliano_php", "");
        $arrData = [
            "Clients" => [$qrDt->getClientData($client_cd)]
        ];

        $years = $this->connection->query("SELECT DISTINCT year(dt_access) AS Year FROM tb_access");
        while($yearRow = $years->fetch_array()) $arrData[$yearRow['Year']] = [];

        foreach($arrData as $year => $value){
            if($year != "Clients"){
                $clCount = [];
                $qrYear = $this->connection->query("SELECT Count(cd_access) AS access FROM tb_access WHERE year(dt_access) = $year AND id_client = $client_cd AND vl_success = 1 GROUP BY id_client;")->fetch_array();
                $clCount[] = (int)$qrYear['access'];
                $arrData[$year] = $clCount;
            }
        }
        return $arrData;
    }

    /**
     * That method returns the chart data of all the unsuccessful accesses records of a client.
     *
     * @param integer $client_cd The client primary key reference
     * @throws ClientNotFound If the client reference isn't valid
     * @return array
     */
    public function getClientUnsuccessfulAc(int $client_cd): array{
        $this->checkNotConnected();
        if(!$this->ckClientRef($client_cd)) throw new ClientNotFound("There's no client #$client_cd", 1);
        $qrDt = new ClientsData("giulliano_php", "");
        $arrData = [
            "Clients" => [$qrDt->getClientData($client_cd)]
        ];

        $years = $this->connection->query("SELECT DISTINCT year(dt_access) AS Year FROM tb_access");
        while($yearRow = $years->fetch_array()) $arrData[$yearRow['Year']] = [];

        foreach($arrData as $year => $value){
            if($year != "Clients"){
                $clCount = [];
                $qrYear = $this->connection->query("SELECT Count(cd_access) AS access FROM tb_access WHERE year(dt_access) = $year AND id_client = $client_cd AND vl_success = 0 GROUP BY id_client;")->fetch_array();
                $clCount[] = (int)$qrYear['access'];
                $arrData[$year] = $clCount;
            }
        }
        return $arrData;
    }
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
namespace templateSystem;
require_once "core/Exceptions.php";

use ExctemplateSystem\AlreadyLoadedFile;
use ExctemplateSystem\InvalidFileType;
use ExctemplateSystem\NotLoadedFile;

/**
 * That class is used to fetch HTML templates at the system, that class works with the errors pages.
 *
 * There's no one especific error, but every big error will be handled by that.
 *
 * That just works replacing strings for other values.
 *
 * There's reserved names at the template, wich can be spoted by the %% at the first and the last character.
 * ---------------------------------------------------------------------------------------------------------
 * Those reserved names/words are:\n
 *      * %message% => The error message, to handling the values
 *      * %file% => The file that was fetching the template
 *      * %line% => The line in the file that the exception was throwed
 *      * %image% => The image that will be showing at the error page
 *      * %title% => The error title, it can be a 500 error or even a login error.
 *      * %btn_rt% => A HTML button to return to some previous page. By default it returns to the index
 *
 * @var string $page_templated The HTML file path, that's the template. By default is the core/templates/500-error-internal.html
 * @var string $error_message The error message to be showed on the template.
 * @var string|null $file_throwed The file that fetched the template.
 * @var string|int|null $line_error The line of the error at the file.
 * @var string $btn_rt The button to return to the previous page.
 * @var bool $got_document If the class haves a HTML document parsed already. Default = false
 * @var string|null $content The parsed file content
 * @author Giulliano Rossi <giulliano.scatalon.rossi@gmail.com>
 * @access public
 */
class ErrorTemplate{
    private $page_templated;
    private $error_message;
    private $file_throwed;
    private $line_error;
    private $btn_rt = "<button class=\"default-btn btn darkble-btn\" onclick=\"window.location.replace('http://localhost/');\">Return to the index</button>";
    private $got_document = false;
    private $content;

    /**
     * Checks if the selected file is a HTML file.
     *
     * @param string $file_path The file path to check
     * @author Giulliano Rossi <giulliano.scatalon.rossi@gmail.com>
     * @return bool
     */
    private static function checkFileValid(string $file_path){
        $exp = explode(".", $file_path);
        return $exp[count($exp) - 1] == "html";
    }

    /**
     * That function return the parsed values of the HTML file content.
     *
     * @throws NotLoadedFile If the class don't haves a file loaded.
     * @return string|null
     */
    final public function parseFile(){
        if(!$this->got_document) throw new NotLoadedFile("There's no HTML document loaded!", 1);
        $rt_str = $this->content;
        $maped_arr = array(
            "%message%" => $this->error_message,
            "%file%" => is_null($this->file_throwed) ? "[Anonymous file]" : $this->file_throwed,
            "%line%" => is_null($this->line_error) ? "[Anonymous Line]" : $this->line_error,
            "%title%" => "Error Unexpected!",
            "%btn_rt%" => $this->btn_rt
        );
        $a = str_replace("%message%", $maped_arr['%message%'], $this->content);
        $b = str_replace("%file%", $maped_arr["%file%"], $a);
        $c = str_replace("%line%", $maped_arr['%line%'], $b);
        $d = str_replace("%title%", $maped_arr['%title%'], $c);
        $rt_str = str_replace("%btn_rt%", $maped_arr['%btn_rt%'], $d);
        return $rt_str;
    }

    /**
     * Starts the class with a document to be parsed
     * @author Giulliano Rossi <giulliano.scatalon.rossi@gmail.com>
     *  ************************************************************
     * @param string $documnetHTML The HTML file to connect and parse.
     * @param string $error_message The error string message.
     * @param string|null $file_throwed The file that throwed the exception
     * @param string|null $btn_rt_lc The button to return to the previous page.
     * @param int|null $line_error The line that showed the error
     */
    final public function __construct(string $documentHTML, string $error_message, string $file_throwed = null, int $line_error = null, string $btn_rt_lc){
        if($this->got_document) throw new AlreadyLoadedFile("The class already have a document loaded", 1);
        if(!$this->checkFileValid($documentHTML)) throw new InvalidFileType("The file '$documentHTML' is not valid!", 1);
        $this->page_templated = $documentHTML;
        $this->file_throwed = $file_throwed;
        $this->lin_error = $line_error;
        $this->btn_rt = $btn_rt_lc;
        $this->error_message = $error_message;
        $this->content = file_get_contents($documentHTML);
        $this->got_document = true;
    }
}
?>
