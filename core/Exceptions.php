<?php
namespace DatabaseActionsExceptions{
    use Exception;

    class NotConnectedError extends Exception{
        public function showMessage() { return "The system needs to be connected to do that action! {line: ". $this->getLine()."}"; }
    }

    class AlreadyConnectedError extends Exception{
        public function showMessage(){ return "The system's already connected! {line: ".$this->getLine()."}";}
    }
}


namespace UsersSystemExceptions{
    use Exception;

    class UserNotFound extends Exception{
        public function showMessage(string $user){return "There's no user '$user' !{line: ".$this->getLine()."}";}
    }

    class UserAlreadyExists extends Exception{
        public function showMessage(string $user){ return "The user '$user' already exists in the database {line: ". $this->getLine() . "}";}
    }

    class InvalidUserName extends Exception{
        public function showMessage(string $username){ return "'$username' is not a valid username! {line: " . $this->getLine() . "}";}
    }

    class PasswordAuthError extends Exception{
        public function showMessage(){ return "Password authentication error! ERROR: '$this->message' {line: " . $this->getLine() . "}";}
    }

    class UserKeyNotFound extends Exception{
        public function showMessage(string $key){ return "There's no uses from the key '$key'! {line: " . $this->getLine() . "}";}
    }

    class UserAlreadyLogged extends Exception{
        public function showMessage(){ return "There's a user logged already! {line: " . $this->getLine() . "}";}
    }

    class NoUserLogged extends Exception{
        public function showMessage(){ return "There's no user logged already! {line: " . $this->getLine() . "}"; }
    }
}

namespace ProprietariesExceptions{
    use Exception;

    class ProprietaryNotFound extends Exception{
        public function showMessage(string $proprietary){ return "There's no proprietary '$proprietary'! {line: " . $this->getLine() . "}";}
    }

    class ProprietaryAlreadyExists extends Exception{
        public function showMessage(string $proprietary){ return "The proprietary '$proprietary' already exists in the database! {line: ". $this->getLine() . "}";}
    }

    class InvalidProprietaryName extends Exception{
        public function showMessage(string $prop_name){ return "'$prop_name' is not a valid proprietary name! {line: " . $this->getLine() . "}";}
    }

    class AuthenticationError extends Exception{
        public function showMessage(){ return "Authentication error. \nERROR> '$this->message' {line: " . $this->getLine() . "}";}
    }

    class ProprietaryKeyNotFound extends Exception{
        public function showMessage(string $key){return "There's no key '$key' that pertences for any proprietary! {line: " . $this->getLine() . "}";}
    }

    class ProprietaryAlreadyLogged extends Exception{
        public function showMessage(){ return "There's a proprietary user logged already! {line: " . $this->getLine() . "}";}
    }

    class NoProprietaryLogged extends Exception{
        public function showMessage(){ return "There's no proprietary user logged already! {line: " . $this->getLine() . "}"; }
    }
}

namespace SignaturesExceptions{
    use Exception;

    class SignatureNotFound extends Exception{
        public function showMessage(int $sign_id){ return "There's no such signature #$sign_id! {line: " . $this->getLine() . "}";}
    }

    class InvalidSignatureFile extends Exception{
        public function showMessage(string $file_path){ return "'$file_path' is not a valid signature file. {line: " . $this->getLine() . "}";}
    }

    class SignatureAuthError extends Exception{
        public function showMessage(){ return "Signature authentication failed. ERROR> '$this->message' {line: " . $this->getLine() . "}";}
    }

    class SignatureFileNotFound extends Exception{
        public function showMessage(string $file_name) {
            $dft_path = "/usignatures.d";
            return "The signature file '$file_name' don't exists at \"$dft_path\"";
        }
    }

    class VersionError extends Exception{
        public function showMessage(string $version, string $a_version){
            return "The version used by the file is not allowed. The file version is $version, the recent allowed version is $a_version. {line: " . $this->getLine() . "}";
        }
    }
}

namespace LogsErrors{
    use Exception;

    class LogsFileNotLoaded extends Exception{
        public function showMessage(){ return "There's no logs file loaded in the class! {line: " . $this->getLine() . "}";}
    }

    class LogsFileAlreadyLoaded extends Exception{
        public function showMessage(){ return "The class already have a logs file loaded! {line: " . $this->getLine() . "}";}
    }

    class InvalidFile extends Exception{
        public function showMessage(string $file){ return "The file '$file' is not a valid logs file! {line: " . $this->getLine() . "}";}
    }

}

namespace ExctemplateSystem{
    use Exception;

    class InvalidFileType extends Exception{
        public function showMessage(string $file){
            return "The file '$file' is not a valid HTML document for fetching";
        }
    }

    class AlreadyLoadedFile extends Exception{
        public function showMessage(){ return "The class already haves a HTML document parsed!";}
    }

    class NotLoadedFile extends Exception{
        public function showMessage(){ return "The class need a HTML document parsed!";}
    }
}

/**
 * Exceptions used for the UsersCheckHistory on the Core.php
 */
namespace CheckHistory{
    use Exception;

    /**
     * Exception thrown when the error code of a register is not in range(0, 3)
     */
    class InvalidErrorCode extends Exception{
        public function __construct(int $code_vl, int $code = 1){
            parent::__construct("The error code '$code_vl' is invalid, expecting a number in 0, 1, 2 or 3", $code);
        }
    }
    /**
     * Exception thrown when the class try to get some register using a primary key reference, but that reference don't exist at the database table.
     */
    class RegisterNotFound extends Exception{

        /**
         * Personalized class constructor, standardize the error message.
         * @param integer $vl_ref The value of the primary key reference
         * @param integer $code The standard parameter of the parent::
         * @return void
         */
        public function _construct(int $vl_ref, int $code = 1){ parent::__construct("Can't find the register using the PK reference #$vl_ref!", $code);}
    }
}
/**
 * Exceptions for the ProprietariesCheckHistory class on the Core.php
 */
namespace PropCheckHistory{
    use Exception;

    /**
     * Exception thrown when the error code of a register is not in range(0, 3)
     */
    class InvalidErrorCode extends Exception{
        public function __construct(int $code_vl = null, int $code = 1){
            $vl = is_null($code_vl) ? "null_value" : $code_vl;
            parent::__construct("The error code '$vl' is invalid, expecting a number in 0, 1, 2 or 3", $code);
        }
    }

    /**
     * Exception thrown when the class try to get some register using a primary key reference, but that reference don't exist at the database table.
     */
    class RegisterNotFound extends Exception{

        /**
         * Personalized class constructor, standardize the error message.
         * @param integer $vl_ref The value of the primary key reference
         * @param integer $code The standard parameter of the parent::
         * @return void
         */
        public function _construct(int $vl_ref, int $code = 1){ parent::__construct("Can't find the register using the PK reference #$vl_ref!", $code);}
    }
}
namespace ClientsExceptions{
    use Exception;

    /**
     * <Exception> thrown when the client database try to access a client, which the reference doesn't exists.
     * Like a primary key of a client, but the client doesn't exist.
     * @access public
     */
    class ClientNotFound extends Exception{
        /**
         * Starts the exception with the main info to throw with a good error message,
         *
         * @param integer $ref The client primary reference
         * @param integer $code The error code to throw the exception, 1 is to fatal (by default)
         * @return void
         */
        public function __contruct(int $ref, int $code = 1){
            parent::__construct("The client #$ref doesn't exist.", $code);
        }
    }

    /**
     * <Exception> thrown when the class try to authenticate a client file but the authentication failed.
     * The authentication can fail by many causes.
     */
    class AuthenticationError extends Exception{
        // empty because there're so many possibilities to the error message that is better leave it blank.
    }

    /**
     * <Exception> thrown when the logged user try to create a client, but he isn't a prorprietary, only
     * proprietaries can create the clients.
     */
    class AccountError extends Exception{}

    /**
     * <Exception> thrown when the logged user try to create a client, but the client name is already in use.
     * Also used when the logged user try to change the client name, but the new name is already in use.
     */
    class ClientAlreadyExists extends Exception{}

    /**
     * <Exception> thrown when the class try to access a client, or create a client using the proprietary reference,
     * but the reference don't exists or isn't valid
     */
    class ProprietaryReferenceError extends Exception{}

    /**
     * <Exception> thrown when the clients class try to access a client using the token reference but the token doesn't exist.
     */
    class TokenReferenceError extends Exception{}
}

namespace ClientsAccessExceptions{
    use Exception;

    /**
     * That class is a exception, thrown when the clients access try to access a record on the database
     * but the record isn't valid.
     */
    class RecordError extends Exception{}

    /**
     * That class is a exception, thrown when the clients access try to insert a value, but the success value
     * isn't valid, which must be integer or boolean
     */
    class SuccessValueError extends Exception{}

    /**
     * That exception is thrown when the client referred in the record isn't valid.
     */
    class ReferenceError extends Exception{}
}

namespace ChangeLogExceptions{
    use Exception;

    /**
     * <Exception> Thrown when a changelog class tried to use a invalid signature
     * primary key reference.
     */
    class SignatureReferenceError extends Exception{
        public function __construct($reference){ parent::__construct("SIGNATURE REFERENCE ERROR: $reference -> reference"); }
    }

    /**
     * <Exception> Thrown when a changelog class tried to use a invalid client
     * primary key reference.
     */
    class ClientReferenceError extends Exception{
        public function __construct($reference){ parent::__construct("CLIENT REFERENCE ERROR: $reference -> reference"); }
    }

    /**
     * <Exception> Thrown when the changelog code received isn't valid
     */
    class InvalidChangelogCode extends Exception{
        public function __construct(int $changelogCode){ parent::__construct("INVALID CHANGELOG CODE '$changelogCode'"); }
    }

    /**
     * <Exception> Thrown when a changelog isn't found in the database
     */
    class ChangeLogNotFound extends Exception{
        public function __construct(int $changelog){ parent::__construct("CHANGELOG #$changelog NOT FOUND"); }
    }

    /**
     * <Exception> Thrown when there're errors using a JSON dumped changelog
     */
    class JSONChangelogError extends Exception{
        public function __construct(string $error){ parent::__construct("JSON CHANGELOG ERROR: \"$error\""); }
    }
}
?>
