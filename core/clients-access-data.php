<?php
namespace Core;
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Core.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/core/Exceptions.php";

use ClientsExceptions\AccountError;
use ClientsExceptions\AuthenticationError as ClientAuthenticationError;
use ClientsExceptions\ClientNotFound;
use ClientsExceptions\ClientAlreadyExists;
use ClientsExceptions\ProprietaryReferenceError;
use ClientsExceptions\TokenReferenceError;

use ClientsAccessExceptions\ReferenceError;
use ClientsAccessExceptions\SuccessValueError;

use ProprietariesExceptions\ProprietaryKeyNotFound;
use ProprietariesExceptions\AuthenticationError;
use ProprietariesExceptions\InvalidProprietaryName;
use ProprietariesExceptions\ProprietaryNotFound;
use ProprietariesExceptions\ProprietaryAlreadyExists;
use DatabaseActionsExceptions\AlreadyConnectedError;
use DatabaseActionsExceptions\NotConnectedError;

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
?>
