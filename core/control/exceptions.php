<?php
namespace Control{
    use Exception;

    /**
     * <Exception> Thrown when the controller class already got a control file,
     * and tries to override it. The overriding of the control file loaded isn't
     * allowed by the default class.
     */
    class ControlFileLoadedError extends Exception{

        public function __construct(){ parent::__construct("There's a control file loaded already!", 1);}
    }

    /**
     * <Exception> Thrown when the controller class tries to access a control file,
     * but there's no control file loaded yet.
     */
    class ControlFileNotFound extends Exception{

        public function __construct(){ parent::__construct("There's no control file loaded yet!", 1);}
    }

    /**
     * <Exception> Thrown when the controller class can't access the control
     * file.
     */
    class ControlFileUnreachable extends Exception{

        public function __construct(string $error, string $controlF){
            parent::__construct("Can't access the control file \"$controlF\": $error", 1);
        }
    }

    /**
     * <Exception> Thrown when the controller class can't access the JSON content
     * of the control file. Normally it's caused by a syntax error on the control
     * file.
     */
    class ControlFileParsingError extends Exception{

        public function __construct(string $contf, string $msg){
            parent::__construct("Can't parse the JSON content of the file \"$contf\": $msg", 1);
        }
    }

    /**
     * <Exception> Thrown at the Clients Control Manager class, it happens when
     * the client reference doesn't exists at the main database.
     */
    class ClientReferenceError extends Exception{

        public function __construct($ref){
            parent::__construct("Can't access the client reference \"$ref\"", 1);
        }
    }

    /**
     * <Exception> Thrown at the Signatures Control Manager class, it happens when
     * a signature reference doesn't exists in the main database.
     */
    class SignatureReferenceError extends Exception{

        public function __construct(int $ref){
            parent::__construct("Can't access signature reference \"$ref\".", 1);
        }
    }

    /**
     * <Exception> Thrown at the Accounts Control Manager, it happens when the
     * user reference isn't found at the main database.
     */
    class UserReferenceError extends Exception{

        public function __construct($ref){
            parent::__construct("Can't access the user reference \"$ref\"", 1);
        }
    }

    /**
     * <Exception> Thrown at the Accounts Control Manager, it happens when the
     * proprietary reference can't be find at the main database.
     */
    class ProprietaryReferenceError extends Exception{

        public function __construct($ref){
            parent::__construct("Can't access the proprietary reference \"$ref\"", 1);
        }
    }


    class DownloadTokenNotFound extends Exception{

        public function __construct(string $token, int $mode = 0){
            $rf = $mode == 0 ? "signature" : "client authentication file";
            parent::__construct("Can't find the download token '$token' at the $rf control file", 1);
        }
    }

    class DownloadTokenDuplicate extends Exception{

        public function __construct(string $token, int $mode = 0){
            $rf = $mode == 0 ? "signature" : "client authentication file";
            parent::__construct("Duplicated download token reference '$token' at the $rf control file", 1);
        }
    }
}
?>
