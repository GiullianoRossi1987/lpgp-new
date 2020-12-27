<?php
namespace Core{
    require_once $_SERVER['DOCUMENT_ROOT'] . "/core/Exceptions.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/core/Core.php";

    use Core\DatabaseConnection;
    use ChangeLogExceptions\SignatureReferenceError;
    use ChangeLogExceptions\ClientReferenceError;
    use ChangeLogExceptions\ChangeLogNotFound;
    use ChangeLogExceptions\JSONChangelogError;
    use DatabaseActionsExceptions\NotConnectedError;
    use DatabaseActionsExceptions\AlreadyConnectedError;

    /**
     * The interface made for all the classes who operates with changelogs
     * and machine time feature
     */
    interface changelogManager {

        /**
         * Checks if there's a changelog with the same primary key reference
         * as the received
         *
         * @param integer $reference The primary key reference
         * @throws NotConnectedError If there's no database connected
         * @return boolean If the reference is valid or not
         */
        private function existsChangelog(int $reference): bool;

        /**
         * Adds a change log to the system
         */
    }

}
 ?>
