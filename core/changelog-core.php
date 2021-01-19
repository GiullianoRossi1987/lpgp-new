<?php
namespace Core{
    require_once $_SERVER['DOCUMENT_ROOT'] . "/core/Exceptions.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/core/Core.php";

    // use Core\DatabaseConnection;
    use ChangeLogExceptions\SignatureReferenceError;
    use ChangeLogExceptions\ClientReferenceError;
    use ChangeLogExceptions\ChangeLogNotFound;
    use ChangeLogExceptions\JSONChangelogError;
    use DatabaseActionsExceptions\NotConnectedError;
    use DatabaseActionsExceptions\AlreadyConnectedError;

    /**
     * The interface made for all the classes who operates with changelogs
     * and machine time feature.
     */
    interface changelogManager {

        /**
         * Checks if there's a changelog with the same primary key reference
         * as the received
         *
         * @param integer $reference The primary key reference
         * @return boolean If the reference is valid or not
         */
        // protected function existsChangelog(int $reference): bool;

        /**
         * Adds a change log to the system.
         *
         * @param integer $reference The reference of the item who changed.
         * @param integer $code The change code that sinalizes what was changed
         * @param integer|null $waybackRef If the change was a wayback, it must
         *                                 reference which changelog was reset (by default it's null).
         * @param string|null $p_name The name reference of the item before the change.
         * @param string|null $p_key_pass The key/password of the item before the change.
         * @param integer|boolean $p_root_code The root value/code of the item before the change.
         *
         * @throws NotConnectedError If there's no database connected
         * @throws SignatureReferenceError If the signature (item) reference isn't valid
         * @throws ClientReferenceError If the client (item) reference isn't valid
         * @return void
         */
        public function addChangelog(int $reference, $date = null, int $code, $waybackRef = null, string $p_name, string $p_key_pass, $p_root_code): void;

        /**
         * This action removes a changelog item from the database forever.
         * @param integer $changelog The item primary key reference
         * @throws NotConnectedError If there's no database connected
         * @throws ChangeLogNotFound If the changelog reference isn't valid
         * @return void
         */
        public function removeChangelog(int $changelog): void;

        /**
         * Lists all the changelogs in the database
         * @return array
         */
        public function lsChangelogs(): array;

        /**
         * Lists all the changelogs of a specific reference (client/signature)
         * @param integer $reference The reference to search in the database
         * @return array
         */
        public function changesFrom(int $reference): array;

        /**
         * Lists all the changelogs of a specific timestamp
         * @param string $when The timestamp to search
         * @return array
         */
        public function changelogsWhen(string $when): array;

        /**
         * Restores the changelog data to the original reference and storages a new
         * changelog with the wayback id, referencing which changelog was
         * restored.
         * @param integer $changelog The changelog to restore the data
         * @return integer The primary key of the wayback changelog
         */
        public function restore(int $changelog): int;

        /**
         * Gets the raw data of a changelog and dumps't into a JSON array,
         * with the possibility to encode this already
         *
         * @param array $changelogData The raw changelog data
         * @param boolean|false $auto_encode If the method will encode the JSON too.
         * @return array|string
         */
        public static function dumpChangeLog(array $changelogData, bool $auto_encode = false);

        /**
         * Checks if the reference exists or not, that reference can be a client
         * primary key reference or a signature primary key reference
         * @param integer $reference The client/signature reference to check
         * @return boolean
         */
        // protected function checkReferenceExists(int $reference): bool;

    }


    /**
     * Represents all the changelogs of the clients and the transactions of the
     * database table 'tb_changelog_clients'.
     * To be fair, all the process of the changelogs is made with this, except the
     * addition of a changelog after a change on a client, there's a trigger for
     * that in the database.
     *
     * @var array _JSON The JSON schema for a client changelog
     */
    class ClientsChangeLogs extends DatabaseConnection implements changelogManager{

        const __JSON = array(
            "id" => 0,             // integer
            "wayback" => null,     // integer | null
            "client" => 0,         // integer
            "old_name" => "",      // string
            "old_token" => "",     // string
            "old_root" => false,   // bool
            "dt_change" => ''      // string | DateTime
        );

        /**
         * Checks if there's a changelog with the same primary key reference
         * as the received
         *
         * @param integer $reference The primary key reference
         * @return boolean If the reference is valid or not
         */
        private function existsChangelog(int $reference): bool{
            $this->checkNotConnected();
            $qr = $this->connection->query("SELECT COUNT(cd_changelog) FROM tb_changelog_clients WHERE cd_changelog = $reference;");
            return (bool)$qr->fetch_array()['cd_changelog'];   // gets the int and then converts to a boolean
        }

        /**
         * Checks if the reference exists or not, that reference can be a client
         * primary key reference or a signature primary key reference
         * @param integer $reference The client/signature reference to check
         * @return boolean
         */
        private function checkReferenceExists(int $reference): bool{
            $this->checkNotConnected();
            $qr = $this->connection->query("SELECT COUNT(cd_client) FROM tb_clients WHERE cd_client = $reference;");
            return (bool)$qr->fetch_array()['cd_client'];
        }

        /**
         * Adds a change log to the system.
         *
         * @param integer $reference The reference of the item who changed.
         * @param integer $code The change code that sinalizes what was changed (USELESS)
         * @param integer|null $waybackRef If the change was a wayback, it must
         *                                 reference which changelog was reset (by default it's null).
         * @param string|null $p_name The name reference of the item before the change.
         * @param string|null $p_key_pass The key/password of the item before the change.
         * @param integer|boolean $p_root_code The root value/code of the item before the change.
         *
         * @throws NotConnectedError If there's no database connected
         * @throws ClientReferenceError If the client (item) reference isn't valid
         * @return void
         */
        public function addChangelog(int $reference, $date = null, int $code, $waybackRef = null, string $p_name, string $p_key_pass, $p_root_code): void{
            $this->checkNotConnected();
            if(!$this->checkReferenceExists($reference)) throw new ClientReferenceError($reference);
            $stmt = $this->connection->prepare("INSERT INTO tb_changelog_clients (id_client, id_wayback, vl_oldname, vl_oldkey, vl_oldcode) VALUES (?, ?, ?, ?, ?);");
            $stmt->bind_param("iiissi", $reference, $waybackRef, $p_name, $p_key_pass, $p_root_code);
            $rsp = $stmt->execute();
            $stmt->close();
            return;
        }

        /**
         * This action removes a changelog item from the database forever.
         * @param integer $changelog The item primary key reference
         * @throws NotConnectedError If there's no database connected
         * @throws ChangeLogNotFound If the changelog reference isn't valid
         * @return void
         */
        public function removeChangelog(int $changelog): void{
            $this->checkNotConnected();
            if(!$this->existsChangelog($changelog)) throw new ChangeLogNotFound($changelog);
            $stmt = $this->connection->prepare("DELETE FROM tb_changelog_clients WHERE cd_changelog = ?;");
            $stmt->bind_param("i", $changelog);
            $rsp = $stmt->execute();
            $stmt->close();
            return;
        }

        /**
         * Lists all the changelogs in the database
         * @return array
         */
        public function lsChangelogs(): array{
            $this->checkNotConnected();
            $qr = $this->connection->query("SELECT * FROM tb_changelog_clients;");
            $results = [];
            while($rq = $qr->fetch_array()) $results[] = $rq;
            return $results;
        }

        /**
         * Lists all the changelogs of a specific reference (client/signature)
         * @param integer $reference The reference to search in the database
         * @throws ClientReferenceError If the reference ins't valid
         * @return array
         */
        public function changesFrom(int $reference): array{
            $this->checkNotConnected();
            $results = [];
            if(!$this->checkReferenceExists($reference)) throw new ClientReferenceError($reference);
            $qr = $this->connection->query("SELECT * FROM tb_changelog_clients WHERE cd_client = $reference;");
            while($rq = $qr->fetch_array()) $results[] = $rq;
            return $results;
        }

        /**
         * Lists all the changelogs of a specific timestamp
         * @param string $when The timestamp to search
         * @return array
         */
        public function changelogsWhen(string $when): array{
            $this->checkNotConnected();
            $results = [];
            $qr = $this->connection->query("SELECT * FROM tb_changelog_clients WHERE dt_changelog = '$when';");
            while($rq = $qr->fetch_array()) $results[] = $rq;
            return $results;
        }

        /**
         * Restores the changelog data to the original reference and storages a new
         * changelog with the wayback id, referencing which changelog was
         * restored.
         * @param integer $changelog The changelog to restore the data
         * @return integer The primary key of the wayback changelog
         */
        public function restore(int $changelog): int{
            $this->checkNotConnected();
            if(!$this->existsChangelog($changelog)) throw new ChangeLogNotFound($changelog);
            $qr = $this->connection->query("SELECT * FROM tb_changelog_clients WHERE cd_changelog = $changelog;");
            $changelogData = $qr->fetch_array();
            $stmt = $this->connection->prepare("UPDATE tb_clients SET tk_client = ?, nm_client = ?, vl_root = ? WHERE cd_client = ?;");
            $stmt->bind_param("ssii", $changelogData['vl_oldtoken'], $changelogData['vl_oldname'], $changelogData['vl_oldroot'], $changelogData['id_client']);
            $stmt->execute();
            $stmt->close();
            $qr_final = $this->connection->query("SELECT cd_changelog FROM tb_changelog_clients ORDER BY cd_changelog ASC LIMIT 1;");
            $ls_ch = (int)$qr_final->fetch_array()['cd_changelog'];
            $qr_waybc = $this->connection->query("UPDATE tb_changelog_clients SET id_wayback = $changelog WHERE cd_changelog = $ls_ch;");
            return $ls_ch;
        }

        /**
         * Gets the raw data of a changelog and dumps't into a JSON array,
         * with the possibility to encode this already
         *
         * @param array $changelogData The raw changelog data
         * @param boolean|false $auto_encode If the method will encode the JSON too.
         * @return array|string
         */
        public static function dumpChangeLog(array $changelogData, bool $auto_encode = false){
            $ref = ClientsChangeLogs::__JSON;
            $ref['id']        = $changelogData['cd_changelog'];
            $ref['client']    = $changelogData['id_client'];
            $ref['old_name']  = $changelogData['vl_oldname'];
            $ref['old_root']  = (bool)$changelogData['vl_oldroot'];
            $ref['old_token'] = $changelogData['vl_oldtoken'];
            $ref['wayback']   = $changelogData['id_wayback'];
            $ref['dt_change'] = $changelogData['dt_changelog'];
            return $auto_encode ? json_encode($ref) : $ref;
        }
    }

    /**
     * Represents all the changelogs of the signatures and the transactions of the
     * database table 'tb_changelog_signatures'.
     * To be fair, all the process of the changelogs is made with this, except the
     * addition of a changelog after a change on a signature, there's a trigger for
     * that in the database.
     *
     * @var array _JSON The JSON schema for a signature changelog
     */
    class SignaturesChangeLogs extends DatabaseConnection implements changelogManager{
        const _JSON = array(
            'id' => 0, // integer
            'wayback' => null, // integer | null
            'signature' => 0,  // integer
            'oldkey' => '',    // string
            'oldcode' => 0,    // integer
            'dt' => ''  // string | DateTime
        );

        /**
         * Checks if there's a changelog with the same primary key reference
         * as the received
         *
         * @param integer $reference The primary key reference
         * @return boolean If the reference is valid or not
         */
        private function existsChangelog(int $reference): bool{
            $this->checkNotConnected();
            $qr = $this->connection->query("SELECT COUNT(cd_changelog) FROM tb_changelog_signatures WHERE cd_changelog = $reference;");
            return (bool)$qr->fetch_array()['cd_changelog'];
        }

        /**
         * Checks if the reference exists or not, that reference can be a client
         * primary key reference or a signature primary key reference
         * @param integer $reference The client/signature reference to check
         * @return boolean
         */
        private function checkReferenceExists(int $reference): bool{
            $this->checkNotConnected();
            $qr = $this->connection->query("SELECT COUNT(cd_signature) FROM tb_signatures WHERE cd_signature = $reference;");
            return (bool)$qr->fetch_array()['cd_signature'];
        }

        /**
         * Adds a change log to the system.
         *
         * @param integer $reference The reference of the item who changed.
         * @param integer $code The change code that sinalizes what was changed
         * @param integer|null $waybackRef If the change was a wayback, it must
         *                                 reference which changelog was reset (by default it's null).
         * @param string|null $p_name The name reference of the item before the change. (Not used here)
         * @param string|null $p_key_pass The key/password of the item before the change.
         * @param integer|boolean $p_root_code The root value/code of the item before the change.
         *
         * @throws NotConnectedError If there's no database connected
         * @throws SignatureReferenceError If the client (item) reference isn't valid
         * @return void
         */
        public function addChangelog(int $reference, $date = null,int $code, $waybackRef = null, string $p_name = '', string $p_key_pass, $p_root_code): void{
            $this->checkNotConnected();
            if(!$this->checkReferenceExists($reference)) throw new ClientReferenceError($reference);
            $stmt = $this->connection->prepare("INSERT INTO tb_changelog_signatures (id_signature, id_wayback, vl_oldkey, vl_oldcode) VALUES (?, ?, ?, ?);");
            $stmt->bind_param("iiissi", $reference, $waybackRef, $p_key_pass, $p_root_code);
            $rsp = $stmt->execute();
            $stmt->close();
            return;
        }

        /**
         * This action removes a changelog item from the database forever.
         * @param integer $changelog The item primary key reference
         * @throws NotConnectedError If there's no database connected
         * @throws ChangeLogNotFound If the changelog reference isn't valid
         * @return void
         */
        public function removeChangelog(int $changelog): void{
            $this->checkNotConnected();
            if(!$this->existsChangelog($changelog)) throw new ChangeLogNotFound($changelog);
            $stmt = $this->connection->prepare("DELETE FROM tb_changelog_signatures WHERE cd_changelog = ?;");
            $stmt->bind_param("i", $changelog);
            $rsp = $stmt->execute();
            $stmt->close();
            return;
        }



        /**
         * Lists all the changelogs in the database
         * @return array
         */
        public function lsChangelogs(): array{
            $this->checkNotConnected();
            $qr = $this->connection->query("SELECT * FROM tb_changelog_signatures;");
            $results = [];
            while($row = $qr->fetch_array()) $results[] = $row;
            return $row;
        }


        /**
         * Lists all the changelogs from a specific signature
         * @param integer $reference The signature Primary key reference
         * @throws SignatureReferenceError If the signature doesn't exist
         * @return array;
         */
        public function changesFrom(int $reference): array{
            $this->checkNotConnected();
            $qr = $this->connection->query("SELECT * FROM tb_changelog_signatures WHERE id_signature = $reference;");
            $results = [];
            while($row = $qr->fetch_array()) $results[] = $row;
            return $results;
        }

        /**
         * Lists all the changelogs of a specific timestamp
         * @param string $when The timestamp to search
         * @return array
         */
        public function changelogsWhen(string $when): array{
            $this->checkNotConnected();
            $results = [];
            $qr = $this->connection->query("SELECT * FROM tb_changelog_signatures WHERE dt_changelog = '$when';");
            while($rq = $qr->fetch_array()) $results[] = $rq;
            return $results;
        }

        /**
         * Restores the changelog data to the original reference and storages a new
         * changelog with the wayback id, referencing which changelog was
         * restored.
         * @param integer $changelog The changelog to restore the data
         * @return integer The primary key of the wayback changelog
         */
        public function restore(int $changelog): int{
            $this->checkNotConnected();
            if(!$this->existsChangelog($changelog)) throw new ChangeLogNotFound($changelog);
            $qr = $this->connection->query("SELECT * FROM tb_changelog_signatures WHERE cd_changelog = $changelog;");
            $changelogData = $qr->fetch_array();
            $stmt = $this->connection->prepare("UPDATE tb_signatures SET vl_code = ?, vl_password = ?  WHERE cd_signature = ?;");
            $stmt->bind_param("isi", $changelogData['vl_oldcode'], $changelogData['vl_oldkey'], $changelogData['id_signature']);
            $stmt->execute();
            $stmt->close();
            $qr_final = $this->connection->query("SELECT cd_changelog FROM tb_changelog_signatures ORDER BY cd_changelog ASC LIMIT 1;");
            $ls_ch = (int)$qr_final->fetch_array()['cd_changelog'];
            $qr_waybc = $this->connection->query("UPDATE tb_changelog_signatures SET id_wayback = $changelog WHERE cd_changelog = $ls_ch;");
            return $ls_ch;
        }

        /**
         * Sets the data of a valid array to the JSON valid array
         * @param array $changelogData The changelog data to set as JSON
         * @param boolean $auto_encode If it'll stringify the JSON automaticly
         * @return string|array
         */
        public static function dumpChangeLog(array $changelogData, bool $auto_encode = false){
            $repr = SignaturesChangeLogs::_JSON;
            $repr["id"]        = $changelogData["cd_changelog"];
            $repr["wayback"]   = $changelogData["id_wayback"];
            $repr["signature"] = $changelogData["id_signature"];
            $repr["oldkey"]    = $changelogData["vl_oldkey"];
            $repr["oldcode"]   = $changelogData["vl_oldcode"];
            $repr["dt"]        = $changelogData["dt_changelog"];
            return $repr;
        }
    }
}
 ?>
