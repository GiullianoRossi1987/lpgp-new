<?php
namespace Core;
require_once $_SERVER['DOCUMENT_ROOT'] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Exceptions.php";

use DatabaseActionsExceptions\AlreadyConnectedError;
use DatabaseActionsExceptions\NotConnectedError;

use UsersSystemExceptions\InvalidUserName;
use UsersSystemExceptions\PasswordAuthError;
use UsersSystemExceptions\UserAlreadyExists;
use UsersSystemExceptions\UserNotFound;
use UsersSystemExceptions\UserKeyNotFound;

/**
 * That class contains the main actions for the users database.
 * @var string DATETIME_FORMAT The format for the date in the database.
 * @var string EMAIL_USING The e-mail address using.
 */
class UsersData extends DatabaseConnection{
    const DATETIME_FORMAT = "H:m:i Y-j-d";
    const EMAIL_USING     = "lpgp@gmail.com";

    /**
     * Starts the class and the connection with the session handler.
     * The params are the same then at the parent::__construct().
     */
    public function __construct(string $usr, string $passwd, string $host = DEFAULT_HOST, string $db = DEFAULT_DB){
        parent::__construct($usr, $passwd, $host, $db);
    }

    /**
     * Just the same thing then the parent::__destruct, but implemented the session_handler destructor.
     */
    public function __destruct(){
        parent::__destruct();
    }

    /**
     * Checks if a user exists in the database.
     * @param string $username The user to search in the database.
     * @param bool $auto_throw If the method will throw a exception if the user don't exists.
     * @throws UserNotFound If there's no such user in the database, and the method's allowed to throw the exception.
     * @return bool
     */
    private function checkUserExists(string $username, bool $auto_throw = false){
        $this->checkNotConnected();
        $qr_all = $this->connection->query("SELECT nm_user FROM tb_users WHERE nm_user = \"$username\";");
        while($row = $qr_all->fetch_array()){
            if($row['nm_user'] == $username) return true;
        }
        if($auto_throw) throw new UserNotFound("There's no user '$username'", 1);
        else return false;
    }

    /**
     * Authenticate a user password, for login or another simple authentication.
     * @param string $user The user to authenticate
     * @param string $password The user password
     * @param bool $encoded_password If the user's password encoded on the database.
     * @throws PasswordAuthError If the passwords doesn't matches
     * @throws UserNotFound If the selected user don't exists.
     * @return bool
     */
    public function authPassword(string $user, string $password, bool $encoded_password = true): bool{
        $this->checkNotConnected();
        if(!$this->checkUserExists($user, false)) throw new UserNotFound("There's no user '$user' in the database", 1);
        $usr_dt  = $this->connection->query("SELECT vl_password FROM tb_users WHERE nm_user = \"$user\";")->fetch_array();
        $from_db = $encoded_password ? base64_decode($usr_dt['vl_password']) : $usr_dt['vl_password'];
        if($password != $from_db) throw new PasswordAuthError("Invalid Password!");
        else return true;
    }

    /**
     * Authenticate the user key at the database.
     *
     * @param string $username The user that's authenticating the account.
     * @param string $key The key received from the user
     * @return bool
     */
    public function authUserKey(string $username, string $key){
        $this->checkNotConnected();
        if(!$this->checkUserExists($username)) throw new UserNotFound("There's no user '$username'!", 1);
        $usr_data = $this->connection->query("SELECT * FROM tb_users WHERE nm_user = \"$username\";")->fetch_array();
        return $key == $usr_data['vl_key'];
    }

    /**
     * Makes the login with a user in the database, with a password authentication and login setup.
     * @param string $user The user to make login.
     * @param string $password The user password.
     * @param bool $encoded_password If the user password is encoded in the database.
     * @return array
     */
    public function login(string $user, string $password, bool $encoded_password = true){
        $rcv = $this->authPassword($user, $password, $encoded_password);
        $checked_usr = $this->connection->query("SELECT checked FROM tb_users WHERE nm_user = \"$user\";")->fetch_array();
        $img_path = $this->connection->query("SELECT vl_img FROM tb_users WHERE nm_user = \"$user\";")->fetch_array();
        $arr_info = [];
        $arr_info['user-logged'] = "true";
        $arr_info['user'] = $user;
        $arr_info['mode'] = "normie";
        $arr_info['user-icon'] = $img_path['vl_img'];
        $arr_info['checked'] = $checked_usr['checked'] == "1" || $checked_usr['checked'] == 1 ? "true": "false";
        return $arr_info;
    }

    /**
     * Checks if a key already haves a user, important to checking user key with email and for the creation of another key.
     * @param string $key The key to search.
     * @author Giulliano Rossi <giulliano.scatalon.rossi@gmail.com>
     * @return bool
     */
    public function checkUserKeyExists(string $key){
        $this->checkNotConnected();
        $qr_wt = $this->connection->query("SELECT vl_key FROM tb_users WHERE vl_key = \"$key\";");
        while($row = $qr_wt->fetch_array()){
            if($row['vl_key'] == $key) return true;
        }
        unset($qr_wt);
        return false;
    }

    /**
     * Generate a user key for the database.
     * @return void
     */
    public function createUserKey(){
        $rand_len = mt_rand(1, 5);
        $key = "";
        while(true){
            $arr = array();
            for($i = 0; $i <= $rand_len; $i++){
                $rand = mt_rand(33, 126);
                $arr[] = ord($rand);
                unset($rand);   // maybe removed after
            }
            $key = implode("", $arr);
            if(!$this->checkUserKeyExists($key)) return $key;
            else continue;
        }
    }

    /**
     * Adds a user for the database. Normally made for be used in HTML forms
     * @param string $user The name for the user.
     * @param string $password The user password.
     * @param string $email The user email.
     * @param bool $encode_password If the password needs to be encoded or is already encoded.
     * @throws UserAlreadyExists If there's a user with that name already in the database.
     * @return void
     */
    public function addUser(string $user, string $password, string $email, bool $encode_password = true, string $img){
        $this->checkNotConnected();
        if($this->checkUserExists($user, false)) throw new UserAlreadyExists("There's already a user with the name '$user'", 1);
        $to_db = $encode_password ? base64_encode($password) : $password;
        $usr_key = $this->createUserKey();
        $qr = $this->connection->query("INSERT INTO tb_users (nm_user, vl_email, vl_password, vl_key, vl_img) VALUES (\"$user\", \"$email\", \"$to_db\", \"$usr_key\", \"$img\");");
        if(!$qr) echo mysqli_error($this->connection);
    }

    /**
     * Removes a user from the database.
     * @param string $user the user to remove.
     * @throws UserNotFound If the user selected don't exists in the database.
     * @return void
    */
    public function deleteUser(string $user){
        $this->checkNotConnected();
        if(!$this->checkUserExists($user)) throw new UserNotFound("There's no user with the name '$user'!", 1);
        $qr_dl = $this->connection->query("DELETE FROM tb_users WHERE nm_user = \"$user\";");
        unset($qr_dl);
    }

    /**
     * Changes a user name in the database.
     * @param string $user THe user to change the name
     * @param string $newname The new name of the user
     * @throws UserNotFound If the user selected don't exists.
     * @throws UserAlreadyExists If the name selected is already in use from another user.
     * @return void
     */
    public function chUserName(string $user, string $newname){
        $this->checkNotConnected();
        if(!$this->checkUserExists($user)) throw new UserNotFound("There's no user '$user'", 1);
        if($this->checkUserExists($newname)) throw new UserAlreadyExists("The name '$newname' is already in use", 1);
        $qr = $this->connection->query("UPDATE tb_users SET nm_user = \"$newname\" WHERE nm_user = \"$user\";");
        unset($qr);
    }

    /**
     * Changes a user email in the database.
     * @param string $user The user to change the email.
     * @param string $email The new user email.
     * @throws UserNotFound If the user don't exists in the database.
     * @return void
     */
    public function chUserEmail(string $user, string $new_email){
        $this->checkNotConnected();
        if(!$this->checkUserExists($user)) throw new UserNotFound("There's no user '$user'", 1);
        $qr = $this->connection->query("UPDATE tb_users SET vl_email = \"$new_email\" WHERE nm_user = \"$user\";");
        $this->setUserChecked($user, false);
        unset($qr);
    }

    /**
     * Changes the user password, but it need to be authenticated by the user password.
     * @param string $user The user to change the password
     * @param string $new_passwd The new password.
     * @param bool $encode If the method will need to encode the password before updating it, if don't the password need to be encoded on base64
     * @throws UserNotFound If there's no user such the selected in the database.
     * @return void
     */
    public function chUserPasswd(string $user, string $new_passwd, bool $encode = true){
        $this->checkNotConnected();
        if(!$this->checkUserExists($user)) throw new UserNotFound("There's no user '$user'", 1);
        $to_db = $encode ? base64_encode($new_passwd) : $new_passwd;
        $qr = $this->connection->query("UPDATE tb_users SET vl_password = \"$to_db\" WHERE nm_user = \"$user\";");
        unset($qr);
        unset($to_db);
    }

    /**
     * Changes the User image at the database.
     * @param string $user The user to change the image.
     * @param string $new_img The new image path
     * @throws UserNotFound If there's no user with the given name.
     * @return void
     */
    public function chImage(string $user, string $new_img = DEFAULT_USER_ICON){
        $this->checkNotConnected();
        if(!$this->checkUserExists($user, false)) throw new UserNotFound("There's no user '$user'", 1);
        $qr = $this->connection->query("UPDATE tb_users SET vl_img  = \"$new_img\" WHERE nm_user = \"$user\";");
        unset($qr);
    }

    /**
     * Sets if a user haves the email checked in the database.
     */
    public function setUserChecked(string $user, bool $checked = true){
	    $this->checkNotConnected();
        if(!$this->checkUserExists($user)) throw new UserNotFound("There's no user '$user'!", 1);
        $to_db = $checked ? 1 : 0;
        $qr = $this->connection->query("UPDATE tb_users SET checked = $to_db WHERE nm_user = \"$user\";");
        unset($qr);
        unset($to_db);
    }

    /**
     * Checks if the user haves the email checked on the database.
     * Checking the field 'checked' on the MySQL Database.
     * @param string $user The user to check
     * @throws UserNotFound If the user don't exists
     * @return bool
     */
    public function checkUserCheckedEmail(string $user){
        $this->checkNotConnected();
        if(!$this->checkUserExists($user)) throw new UserNotFound("There's no user '$user'!", 1);
        $usr_data = $this->connection->query("SELECT checked FROM tb_users WHERE nm_user = \"$user\";")->fetch_array();
        return $usr_data['checked'] == 1;
    }

    /**
     * That function returns the content of the email template to send in HTML.
     * Wich template will be used to send the checking email, it will replace
     * The username and the user key.
     *
     * @param string $user The user that the server will send the email.
     * @param string $key The user key, storaged at the database.
     * @return string
     */
    public function fetchTemplateEmail(string $user, string $key){
        $raw_content = file_get_contents("core/templates/template-email-2.html");
        $cont1 = str_replace("%user%", $user, $raw_content);
        return str_replace("%key%", $key, $cont1);
    }


    /**
     * Sends a email to the selected user.
     * That email will contain the users key storaged on the database.
     * @param string $user The user to send the checking email
     * @throws UserNotFound If the selected/referencied user don't exists.
     * @return bool
     */
    public function sendCheckEmail(string $user){
        $this->checkNotConnected();
        if(!$this->checkUserExists($user)) throw new UserNotFound("There's no user '$user'!", 1);
        $usr_data = $this->connection->query("SELECT vl_key, vl_email, checked FROM tb_users WHERE nm_user = \"$user\";")->fetch_array();
        if($usr_data['checked'] == 1) return true;  // will end the execution
        $headers = "MIME-Version: 1.0\n";
        $headers .= "Content-Type: text/html; charset=iso-8859-1\n";
        $headers .= "From: " . self::EMAIL_USING . "\n";
        $headers .= "Cc: " . $usr_data['vl_email'] . "\n";
        $content = $this->fetchTemplateEmail($user, $usr_data['vl_key']);
        return mail($usr_data['vl_email'], "Your LPGP account!", $content, $headers);
    }

    /**
     * Query all the users by the name.
     * @param string $name_needle The string to search
     * @param bool $exactly If the method will search for te exact string in the database.
     * @return array  in that array will have all the names.
     */
    public function qrUserByName(string $name_needle, bool $exactly = false){
        $this->checkNotConnected();
        $arr = array();
        if($exactly) $qr = $this->connection->query("SELECT nm_user FROM tb_users WHERE nm_user = \"$name_needle\";");
        else $qr = $this->connection->query("SELECT nm_user FROM tb_users WHERE nm_user LIKE \"%$name_needle%\";");
        while($row = $qr->fetch_array()) array_push($arr, $row['nm_user']);
        return $arr;
    }

    /**
     * Searchs all the users with a string in the email.
     * @param string $email_needle The string to search on the email field
     * @param bool $exactly Searchs for the exact string in the email.
     * @return array
     */
    public function qrUserByEmail(string $email_needle, bool $exactly = false){
        $this->checkNotConnected();
        $arr = array();
        if($exactly) $qr = $this->connection->query("SELECT nm_user FROM tb_users WHERE vl_email = \"$email_needle\";");
        else $qr = $this->connection->query("SELECT nm_user FROM tb_users WHERE vl_email LIKE \"%$email_needle%\";");
        while($row = $qr->fetch_array()) array_push($arr, $row['nm_user']);
        return $arr;
    }

    /**
     * Searches the user name by a string on him key, it'll be used at the web, but at the admin on the server.
     * @param string $key_needle The string to search on the key field;
     * @param bool $exactly If the search will be the exactly the string.
     * @return array.
     */
    public function qrUserByKey(string $key_needle, bool $exactly = false){
        $this->checkNotConnected();
        $arr = array();
        if($exactly) $qr = $this->connection->query("SELECT nm_user FROM tb_users WHERE vl_key = \"$key_needle\";");
        else $qr = $this->connection->query("SELECT nm_user FROM tb_users WHERE vl_key LIKE \"%$key_needle%\";");
        while($row = $qr->fetch_array()) array_push($arr, $row['nm_user']);
        return $arr;
    }

    /**
     * Returns all the data of a specific user in the database.
     * @param string $user The name of the user to get in the database.
     * @throws UserNotFound If there's no user with such name.
     * @return array
     */
    public function getUserData(string $user){
        $this->checkNotConnected();
        if(!$this->checkUserExists($user)) throw new UserNotFound("There's no user '$user'", 1);
        return $this->connection->query("SELECT * FROM tb_users WHERE nm_user = \"$user\";")->fetch_array();
    }

    /**
     * Returns all the data of a specific user in the database using him primary key (ID);
     *
     * @param integer $usr_pk The primary key reference of the user
     * @throws UserNotFound If there's no user with such primary key
     * @return array
     */
    public function getUserDataByID(int $usr_pk){
        $this->checkNotConnected();
        // error checking
        $qr_tmp = $this->connection->query("SELECT cd_user FROM tb_users WHERE cd_user = $usr_pk;");
        $exists = false;
        while($row = $qr_tmp->fetch_array()){
            if($row['cd_user'] == $usr_pk){
                $exists = true;
                break;
            }
        }
        if(!$exists) throw new UserNotFound("There's no user with primary key #$usr_pk", 1);
        $qr_tmp->close();
        $dt_qr = $this->connection->query("SELECT * FROM tb_users WHERE cd_user = $usr_pk;");
        $data = $dt_qr->fetch_array();
        $dt_qr->close();
        return $data;
    }

    /**
     * Searches in the database using parameters of a associative array received
     * and return the results
     * @param array $parameters The associative array with the parameters
     * @return array The results of the query
     */
    public function fastQuery(array $parameters): array{
        $this->checkNotConnected();
        $qr_str = "SELECT * FROM tb_users ";
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

    /**
     * Updates a user account data, using parameters refernce from a associative
     * array. The array will have the parameters that references what's the new value
     * of the data specified data as a field. That field name must be the same as the
     * one in the database.
     * @param array $parameters The associative array with the parameters
     * @param integer|string $user The user reference it can be the name or the primary key
     * @return void
     */
    public function fastUpdate(array $parameters, $user): void{
        $this->checkNotConnected();
        $firstAdded = false;
        $qr_str = "UPDATE tb_users SET ";
        foreach($parameters as $field => $value){
            if(!$firstAdded) $firstAdded = true;
            else $qr_str .= ",";
            $qr_str .= is_numeric($value) ? " $field = $value" : " $field = \"$value\"";
        }
        $qr_str .= is_numeric($user) || is_integer($user) ? " WHERE cd_user = $user" : " WHERE nm_user = \"$user\"";
        $resp = $this->connection->query($qr_str . ";");
        return;
    }
}
