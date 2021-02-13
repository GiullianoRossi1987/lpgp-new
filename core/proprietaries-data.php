<?php
namespace Core;
require_once $_SERVER['DOCUMENT_ROOT'] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Exceptions.php";

use DatabaseActionsExceptions\AlreadyConnectedError;
use DatabaseActionsExceptions\NotConnectedError;

use ProprietariesExceptions\ProprietaryKeyNotFound;
use ProprietariesExceptions\AuthenticationError;
use ProprietariesExceptions\InvalidProprietaryName;
use ProprietariesExceptions\ProprietaryNotFound;
use ProprietariesExceptions\ProprietaryAlreadyExists;

/**
 * That class contains the main actions with the propriearies on the system.
 * The main methods to manage the proprietaries accounts in the database are here.
 * The constants are the same then the in UsersData class.
 *
 * @var string DATETIME_FORMAT The format of the date and time using in the method.
 * @var string EMAIL_USING The email address used to send the emails.
 */
class ProprietariesData extends DatabaseConnection{

    const DATETIME_FORMAT = "H:m:i Y-M-d";
    const EMAIL_USING     = "lpgp@gmail.com";

    /**
     * Checks if a proprietary account exists in the database.
     * @param string $nm_proprietary The name of the proprietary to search.
     * @return bool
     */
     private function checkProprietaryExists(string $nm_proprietary){
          $this->checkNotConnected();
          $qr = $this->connection->query("SELECT nm_proprietary FROM tb_proprietaries WHERE nm_proprietary = \"$nm_proprietary\";");
          while($row = $qr->fetch_array()){
              if($row['nm_proprietary'] == $nm_proprietary) return true;
              else continue;
          }
          return false;
     }


     /**
      * Checks if a key already haves a user, important to checking user key with email and for the creation of another key.
      * @param string $key The key to search.
      * @author Giulliano Rossi <giulliano.scatalon.rossi@gmail.com>
      * @return bool
      */
    public function checkProprietaryKeyExists(string $key){
        $this->checkNotConnected();
        $qr_wt = $this->connection->query("SELECT vl_key FROM tb_proprietaries WHERE vl_key = \"$key\";");
        if($qr_wt === false) throw new Exception($this->connection->error);
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
    public function createProprietaryKey(){
        $rand_len = mt_rand(1, 5);
        $key = "";
        while(true){
            $arr = [];
            for($i = 0; $i <= $rand_len; $i++){
                $rand = mt_rand(33, 126);
                $arr[] = ord($rand);
                unset($rand);   // maybe removed after
            }
            $key = implode("", $arr);
            if(!$this->checkProprietaryKeyExists($key)) return $key;
            else continue;
        }
    }

    /**
     * That function checks if the key received is the same key then the proprietary key at
     * the database, important for validate the proprietary email.
     *
     * @param string $proprietary That's checking the key
     * @param string $key_rcv The key received.
     * @throws ProprietaryNotFound If the proprietary don't exists.
     * @return bool If the key is valid or not.
     */
    public function authPropKey(string $proprietary, string $key_rcv){
        $this->checkNotConnected();
        if(!$this->checkProprietaryExists($proprietary)) throw new ProprietaryNotFound("There's no proprietary '$proprietary'!", 1);
        $prop_data = $this->connection->query("SELECT vl_key, checked FROM tb_proprietaries WHERE nm_proprietary = \"$proprietary\";")->fetch_array();
        if($prop_data['checked'] == 1) return null;  // if the proprietary email was checked already
        return $prop_data['vl_key'] == $key_rcv;
    }

    /**
     * Authenticates a proprietary user password, that will be used for every thing, even the user data change.
     *
     * @param string $proprietary The proprietary user to authenticate the password.
     * @param string $password The proprietary password, from a input.
     * @param bool $encoded_password If the password is enconded at the database, by default yes.
     * @throws ProprietaryNotFound If the selected proprietary don't exists.
     * @return bool
     */
    public function authPasswd(string $proprietary, string $password, bool $encoded_password = true){
        $this->checkNotConnected();
        if(!$this->checkProprietaryExists($proprietary)) throw new ProprietaryNotFound("There's no proprietary user '$proprietary'!", 1);
        $prop_data = $this->connection->query("SELECT vl_password FROM tb_proprietaries WHERE nm_proprietary = \"$proprietary\";")->fetch_array();
        if($prop_data === false) throw new Exception($this->connection->error);
        $from_db = $encoded_password ? base64_decode($prop_data['vl_password']) : $prop_data['vl_password'];
        return $password == $from_db;
     }

     /**
      * Makes the authentication and sets the $_COOKIE keys to do the login.
      * Just like the UsersData->login function.
      *
      * @param string $proprietary The proprietary that will do the login.
      * @param string $password The password received from the input at the form
      * @param bool $encoded_password If the password is encoded at the database.
      * @throws ProprietaryNotFound If there's no proprietary such the selected
      * @throws AuthenticationError If the password's incorrect
      * @return array
      */
    public function login(string $proprietary, string $password, bool $encoded_password = true){
        $this->checkNotConnected();
        $auth = $this->authPasswd($proprietary, $password, $encoded_password);
        if(!$auth) throw new AuthenticationError("Invalid password", 1);
        $arr_info = [];
        $checked = $this->connection->query("SELECT checked, vl_img FROM tb_proprietaries WHERE nm_proprietary = \"$proprietary\";")->fetch_array();
        $arr_info['user'] = $proprietary;
        $arr_info['mode'] = "prop";
        $arr_info['user-logged'] = "true";
        $arr_info['checked'] = $checked['checked'] == 1 || $checked == "1" ? "true" : "false";
        $arr_info['user-icon'] = $checked['vl_img'];
        unset($auth);   // min use of memory
        return $arr_info;
     }

     /**
      * Returns the primary key of a proprietary, using him name.
      * @param string $proprietary_nm The name of the proprietary to search
      * @return integer
      */
    public function getPropID(string $proprietary_nm){
        $this->checkNotConnected();
        if(!$this->checkProprietaryExists($proprietary_nm)) throw new ProprietaryNotFound("There's no proprietary '$proprietary_nm'", 1);
        $qr = $this->connection->query("SELECT cd_proprietary FROM tb_proprietaries WHERE nm_proprietary = \"$proprietary_nm\";")->fetch_array();
        return $qr['cd_proprietary'];
    }

     /**
      * Adds a proprietary account in the database, that will be automaticly commited to the MySQL database.
      * @param string $prop_name The proprietary account name.
      * @param string $password The account password.
      * @param bool $encode_password If the method will encode the password before going to the database, if don't the password need to be in bas64.
      * @param string $img_path The path to the image file of the user avatar
      * @throws ProprietaryAlreadyExists If there's a proprietary with that name already.
      * @return void
      */
    public function addProprietary(string $prop_name, string $password, string $email, bool $encode_password = true, string $img = DEFAULT_USER_ICON){
        $this->checkNotConnected();
        if($this->checkProprietaryExists($prop_name)) throw new ProprietaryAlreadyExists("There's the proprietary '$prop_name' already", 1);
        $to_db = $encode_password ? base64_encode($password) : $password;
        $prop_key = $this->createProprietaryKey();
        $qr = $this->connection->query("INSERT INTO tb_proprietaries (nm_proprietary, vl_email, vl_password, vl_key, vl_img) VALUES (\"$prop_name\", \"$email\", \"$to_db\", \"$prop_key\", \"$img\");");
        if($qr === false) throw new Exception($this->connection->error);
        unset($to_db);
     }

     /**
      * Removes a proprietary account from the database.
      * @param string $proprietary The account name to remove.
      * @throws ProprietaryNotFound If the proprietary selected don't exists
      * @return void
      */
    public function delProprietary(string $proprietary){
        $this->checkNotConnected();
        if(!$this->checkProprietaryExists($proprietary)) throw new ProprietaryNotFound("There's no proprietary account '$proprietary'", 1);
        $qr_del = $this->connection->query("DELETE FROM tb_proprietaries WHERE nm_proprietary = \"$proprietary\";");
        unset($qr_del);
     }

     /**
      * Changes a proprietary account name.
      * @param string $proprietary The proprietary account to change the name (name)
      * @param string $new_name The new account name
      * @throws ProprietaryNotFound If the proprietary selected don't exists in the database.
      * @throws ProprietaryAlreadyExists If the new name is already beeing used by another account.
      * @return void
      */
    public function chProprietaryName(string $proprietary, string $new_name){
        $this->checkNotConnected();
        if(!$this->checkProprietaryExists($proprietary)) throw new ProprietaryNotFound("There's no proprietary account '$proprietary'", 1);
        if($this->checkProprietaryExists($new_name)) throw new ProprietaryAlreadyExists("The name '$new_name' is already in use, choose another", 1);
        $qr_ch = $this->connection->query("UPDATE tb_proprietaries SET nm_proprietary = \"$new_name\" WHERE nm_proprietary = \"$proprietary\";");
        unset($qr_ch);
     }

     /**
      * Changes the avatar image of the proprietary.
      * @param string $proprietary The name of the proprietary
      * @param string $img_new The path of the new avatar.
      * @throws ProprietaryNotFound If the proprietary don't exists
      * @return void
      */
    public function chProprietaryImg(string $proprietary, string $img_new){
        $this->checkNotConnected();
        if(!$this->checkProprietaryExists($proprietary)) throw new ProprietaryNotFound("The proprietary '$proprietary' don't exists!", 1);
        $stmt = $this->connection->prepare("UPDATE tb_proprietaries SET vl_img = ? WHERE nm_proprietary = ?;");
        $stmt->bind_param("ss", $img_new, $proprietary);
        $rp = $stmt->execute();
        // $qr_ch = $this->connection->query("UPDATE tb_proprietaries SET vl_img = \"$img_new\" WHERE nm_proprietary = \"$proprietary\";");

        return ;
    }

     /**
      * Changes a proprietary email account.
      *
      * @param string $proprietary The proprietary to change the email.
      * @param string $new_email The new value for the email
      * @throws ProprietaryNotFound If the proprietary selected don't exists in the database
      * @return void
      */
    public function chProprietaryEmail(string $proprietary, string $new_email){
        $this->checkNotConnected();
        if(!$this->checkProprietaryExists($proprietary)) throw new ProprietaryNotFound("There's no proprietary '$proprietary'", 1);
        $qr_ch = $this->connection->query("UPDATE tb_proprietaries SET vl_email = \"$new_email\" WHERE nm_proprietary = \"$proprietary\";");
        unset($qr_ch);
     }

     /**
      * Changes the proprietary use avatar image.
      * @param string $proprietary The name of the proprietary to change the image
      * @param string $new_img The new image for the user icon.
      * @throws ProprietaryNotFound If there's no proprietary with the $proprietary name
      * @return void
      */
    public function chImage(string $proprietary, string $new_img = DEFAULT_USER_ICON){
        $this->checkNotConnected();
        if(!$this->checkProprietaryExists($proprietary)) throw new ProprietaryNotFound("There's no proprietary '$proprietary'", 1);
        $qr = $this->connection->query("UPDATE tb_proprietaries SET vl_img = \"$new_img\" WHERE nm_proprietary = \"$proprietary\";");
        unset($qr);
    }

     /**
      * Changes a proprietary account password, but remember to use it after the authentication (obviously)
      *
      * @param string $proprietary The proprietary to change the password.
      * @param string $new_passwd The new account password
      * @param bool $encode_passwd If the method will encode the password in base64
      * @throws ProprietaryNotFound If the selected account ($proprietary) don't exists
      * @return void
      */
    public function chProprietaryPasswd(string $proprietary, string $new_passwd, bool $encode_passwd = true){
        $this->checkNotConnected();
        if(!$this->checkProprietaryExists($proprietary)) throw new ProprietaryNotFound("There's no proprietary '$proprietary'", 1);
        $to_db = $encode_passwd ? base64_encode($new_passwd) : $new_passwd;
        $qr_ch = $this->connection->query("UPDATE tb_proprietaries SET vl_password = \"$to_db\" WHERE nm_proprietary = \"$proprietary\";");
        unset($to_db);
        unset($qr_ch);
     }

     /**
      * Changes the field checked, used when the key was sended and used at the email. Or when he changes him email.
      *
      * @param string $proprietary The proprietary to change the info.
      * @param bool   $checked     If the email was checked already.
      * @throws ProprietaryNotFound If the choosed account don't exists in the database.
      * @return void
      */
    public function setProprietaryChecked(string $proprietary, bool $checked = true){
        $this->checkNotConnected();
        if(!$this->checkProprietaryExists($proprietary)) throw new ProprietaryNotFound("There's no proprietary '$proprietary'", 1);
        $checked_vl = $checked ? 1: 0;
        $qr_ch = $this->connection->query("UPDATE tb_proprietaries SET checked = $checked_vl WHERE nm_proprietary = \"$proprietary\";");
     }

     /**
      * Sets special names on the HTML file to be used to send the email with the login key.
      * On the HTML file the special names useds are:
      *     * %user% => The proprietary using (or any another user)
      *     * %key% => The account key.
      * @param string $prop The proprietary name to stay on the %user%
      * @param string $key  The proprietary key
      * @return string
      */
    public function parseHTMLTemplateEmailK(string $prop, string $key, string $path){
        $content = file_get_contents($path);
        $r1_content = str_replace("%user%", $prop, $content);
        return str_replace("%key%", $key, $r1_content);
     }

     /**
      * That function sends  a email with the code to the proprietary email. That uses the method mail, and requires the SMTP of the GMAIL.
      * Also that function calls a method to convert the HTML file to the content.
      *
      * @param string $proprietary The proprietary to get the data and send the email.
      * @throws ProprietaryNotFound If the selected proprietary don't exists in the database.
      * @return bool If the email was sended, or if the account already checked the email.
      */
    public function sendCheckEmail(string $proprietary){
        $this->checkNotConnected();
        if(!$this->checkProprietaryExists($proprietary)) throw new ProprietaryNotFound("There's no proprietary account '$proprietary'", 1);
        $prop_dt = $this->connection->query("SELECT vl_key, checked, vl_email FROM tb_proprietaries WHERE nm_proprietary = \"$proprietary\";")->fetch_array();
        $content = $this->parseHTMLTemplateEmailK($proprietary, $prop_dt['vl_key'], "core/templates/template-email-2.html");
        $headers = "MIME-Version: 1.0\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\n";
        $headers .= "From: " . self::EMAIL_USING . "\n";
        $headers .= "Cc: " . $prop_dt['vl_email'] . "\n";
        return mail($prop_dt['vl_email'], "Your LPGP key!", $content, $headers);
     }

     /**
      * Searches in the database for a proprietary with a name like a string or a name exactly equal a string.
      *
      * @param string $name_needle The string to search in the names.
      * @param bool $exactly If will be for the exactly equal names.
      * @return array
      */
    public function qrPropByName(string $name_needle, bool $exactly = false){
        $this->checkNotConnected();
        $results = array();
        if($exactly) $qr = $this->connection->query("SELECT * FROM tb_proprietaries WHERE nm_proprietary = \"$name_needle\";");
        else $qr = $this->connection->query("SELECT * FROM tb_proprietaries WHERE nm_proprietary LIKE  \"%$name_needle%\";");
        while($row = $qr->fetch_array()) array_push($results, $row);
        return $results;
     }

     /**
      * Searches a proprietary for a string in the email field at the database.
      *
      * @param string $email_needle The string to search at the email.
      * @param bool $exactly If will search for the exactly string in the database.
      * @return array
      */
    public function qrPropByEmail(string $email_needle, bool $exactly = false){
        $this->checkNotConnected();
        $results = array();
        if($exactly) $qr = $this->connection->query("SELECT nm_proprietary FROM tb_proprietaries WHERE vl_email = \"$email_needle\";");
        else $qr = $this->connection->query("SELECT nm_proprietary FROM tb_proprietaries WHERE vl_email LIKE \"%$email_needle%\";");
        while($row = $qr->fetch_array()) array_push($results, $row['nm_proprietary']);
        return $results;
     }

     /**
      * That metohod gets all the data at the database about a specific proprietary;
      *
      * @param string $proprietary_nm The name of the proprietary to get the data
      * @throws ProprietaryNotFound If there's no one proprietary with the name at the main parameter
      * @return array
      */
     public function getPropData(string $proprietary_nm){
        $this->checkNotConnected();
        if(!$this->checkProprietaryExists($proprietary_nm)) throw new ProprietaryNotFound("There's no proprietary #$proprietary_nm!", 1);
        return $this->connection->query("SELECT * FROM tb_proprietaries WHERE nm_proprietary = \"$proprietary_nm\";")->fetch_array();
     }

     /**
      * Return all the data of the proprietary by him primary key reference (PK);
      * @param integer $prop_id The primary key reference of the proprietary
      * @throws ProprietaryNotFound If the primary key reference don't exists in the database.
      * @return array;
      */
      public function getPropDataByID(int $prop_id){
          $this->checkNotConnected();
          // checking the primary key
          $qr_check = $this->connection->query("SELECT cd_proprietary FROM tb_proprietaries WHERE cd_proprietary = $prop_id;");
          $valid = false;
          while($row = $qr_check->fetch_array()){
              if($row['cd_proprietary'] == $prop_id) $valid = true;
          }
          if(!$valid) throw new ProprietaryNotFound("There's no proprietary with the ID #$prop_id!", 1);
          // end of checking.
          $dt_qr = $this->connection->query("SELECT * FROM tb_proprietaries WHERE cd_proprietary = $prop_id;");
          $re = $dt_qr->fetch_array();
          $dt_qr->close();
          $qr_check->close();
          return $re;
      }

      /**
       * Searches in the database using parameters to query referred in a associative
       * array and return the results;
       * @param array $parameters The parameters received with the same keys as in the database
       * @return array The content of the query
       */
      public function fastQuery(array $parameters): array{
         $this->checkNotConnected();
         $firstAdded = false;
         $qr_str = "SELECT * FROM tb_proprietaries ";
         foreach($parameters as $field => $value){
             if(!$firstAdded){
                 $qr_str .= "WHERE ";
                 $firstAdded = true;
             }
             $qr_str .= is_numeric($value) ? " $field = $value " : " $field = \"$value\" ";
         }
         $resp = $this->connection->query($qr_str .= ";");
         $results = [];
         while($row = $resp->fetch_array()) $results[] = $row;
         return $results;
     }

     /**
      * Updates a proprietary account data, using the update params from a associative
      * array
      * @param array $parameters The parameters witht the same keys as the database
      * @param integer|string $proprietary The proprietary reference to be updated
      * @return void
      */
     public function fastUpdate(array $parameters, $proprietary): void{
         $this->checkNotConnected();
         $firstAdded = false;
         $qr_str = "UPDATE tb_proprietaries SET ";
         foreach($parameters as $field => $values){
             if(!$firstAdded){
                 $firstAdded = true;
             }
             else $qr_str .= ", ";
             $qr_str .= is_numeric($values) ? " $field = $values" : " $field = \"$values\"";
         }
         $qr_str .= is_int($proprietary) || is_numeric($proprietary) ? " WHERE cd_proprietary = $proprietary;" : " WHERE nm_proprietary = \"$proprietary\";";
         $rep = $this->connection->query($qr_str);
         return;
     }
}
