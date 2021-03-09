<?php
namespace Core;
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Exceptions.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/control/controllers.php";

use ClientsExceptions\AccountError;
use ClientsExceptions\AuthenticationError as ClientAuthenticationError;
use ClientsExceptions\ClientNotFound;
use ClientsExceptions\ClientAlreadyExists;
use ClientsExceptions\ProprietaryReferenceError;
use ClientsExceptions\TokenReferenceError;
use DatabaseActionsExceptions\AlreadyConnectedError;
use DatabaseActionsExceptions\NotConnectedError;
use ProprietariesExceptions\ProprietaryKeyNotFound;
use ProprietariesExceptions\AuthenticationError;
use ProprietariesExceptions\InvalidProprietaryName;
use ProprietariesExceptions\ProprietaryNotFound;
use ProprietariesExceptions\ProprietaryAlreadyExists;
use Control\ClientsController;

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
        }while(file_exists("g.clients/tmp/" . $auth_nm) || strlen($auth_nm) == 0);

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
    public function genConfigClient(int $client_pk_ref, bool $html_mode = true): string{
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
        $encoded = implode("/", $encoded_ar);
        // file_put_contents($files, $encoded);
        system("touch $files");
        $fl = fopen($files, "w");
        fwrite($fl, $encoded);
        fclose($fl);
        $controller->addDownloadRecord($client_pk_ref, $tk, $json_aut['Dt'], true);
        unset($controller);
        $file_n = str_replace($_SERVER['DOCUMENT_ROOT'], "", $files);
        return $html_mode ? $this->passHTML($file_n) : $file_n;
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

    /**
     * Searches in the database using parameters of a associative array received
     * and return the results
     * @param array $parameters The associative array with the parameters
     * @return array The results of the query
     */
    public function fastQuery(array $parameters): array{
        $this->checkNotConnected();
        $qr_str = "SELECT * FROM tb_clients ";
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
     * @param integer $client The client primary key reference used
     * @return void
     */
    public function fastUpdate(array $parameters, $client): void{
        $this->checkNotConnected();
        $firstAdded = false;
        $qr_str = "UPDATE tb_clients SET ";
        foreach($parameters as $field => $value){
            if(!$firstAdded) $firstAdded = true;
            else $qr_str .= ",";
            $qr_str .= is_numeric($value) ? " $field = $value" : " $field = \"$value\"";
        }
        $resp = $this->connection->query($qr_str . " WHERE cd_client = $client;");
        return;
    }
}
?>
