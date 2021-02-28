<?php
namespace Core;
use Exception;
try{
    require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Exceptions.php";
    require_once  "config/configmanager.php";
    require_once  "core/control/controllers.php";
}
catch(Exception $e){
    require_once $_SERVER['DOCUMENT_ROOT'] . "/core/Exceptions.php";
}

use mysqli;
use mysqli_result;
use mysqli_sql_exception;

// Exceptions
use DatabaseActionsExceptions\AlreadyConnectedError;
use DatabaseActionsExceptions\NotConnectedError;

use UsersSystemExceptions\InvalidUserName;
use UsersSystemExceptions\PasswordAuthError;
use UsersSystemExceptions\UserAlreadyExists;
use UsersSystemExceptions\UserNotFound;
use UsersSystemExceptions\UserKeyNotFound;

use ProprietariesExceptions\ProprietaryKeyNotFound;
use ProprietariesExceptions\AuthenticationError;
use ProprietariesExceptions\InvalidProprietaryName;
use ProprietariesExceptions\ProprietaryNotFound;
use ProprietariesExceptions\ProprietaryAlreadyExists;

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

use ZipArchive;

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
if(!defined("U_CLIENTS_CONF")) define("U_CLIENTS_CONF", "u.clients/");
if(!defined("G_CLIENTS_CONF")) define("G_CLIENTS_CONF", "g.clients/");
if(!defined("TMP_GCLIENTS")) define("TMP_GCLIENTS", "g.clients/tmp/");
if(!defined("TMP_UCLIENTS")) define("TMP_UCLIENTS", "u.clients/tmp/");

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

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
namespace templateSystem;
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Exceptions.php";

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
    private $btn_rt = "<button class=\"default-btn btn darkble-btn\" onclick=\"window.location.replace('https://www.lpgpofficial.com/');\">Return to the index</button>";
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
