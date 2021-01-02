<?php

namespace Configurations{
    use Exception;
    if(!defined("CONFIG_FILE")) define("CONFIG_FILE", "mainvars.json");

    /**
     * <Exception> Thrown when the configurations manager class try to load a configurations file
     * but there's other configurations file loaded already
     */
    class ConfigurationsLoaded extends Exception{

        public function __construct(int $code = 1){
            parent::__construct("There're configurations loaded already", $code);
        }
    }

    /**
     * <Exception> Thrown when the configurations manager class don't have a confgurations
     * file loaded, and a method requires it
     */
    class ConfigurationsNotLoaded extends Exception{

        public function __construct(int $code = 1){
            parent::__construct("There's no configurations file loaded", $code);
        }
    }


    /**
     * <Exception> Thrown when the configurations manager class try to load a configurations
     * file, but it ins't valid.
     */
    class InvalidConfigurations extends Exception{

        public function __construct(string $error, int $code = 1){
            parent::__construct("The configurations aren't valid! $error", $code);
        }
    }

    /**
     * That class loads the configurations file setted by default configurations file.
     *
     * @var string|null $configurationsFile The configuraions file loaded by the class
     * @var array|null $config The configurations values parsed from the file loaded.
     */
    class ConfigManager{
        private $configurationsFile = null;
        private $config = null;

        /**
         * That method check if a configurations file is valid configurations file
         * or not. To be valid the configurations file must have the following structure:
         *
         * apache:
         *  errorLog -> string
         *  virtualhost -> string (www.lpgpofficial.com)
         *  port -> int (443)
         *
         * mysql:
         *  sysuser -> string
         *  passwd -> string
         *  db -> string (LPGP_WEB)
         *  ext_root -> [string, string]
         *  ext_normal -> [string, string]
         *  sock_user -> string
         *  sock_passwd -> string
         *
         * sdk?
         *  {It's a colletion of items with the following structure}
         *  item ->
         *       Name -> string
         *       Link -> string
         *       Version -> string
         *       Language -> string
         *
         * @param string $file The configurations file to load
         * @return boolean
         */
        private function checkConfig(string $file): bool{
            $content = file_get_contents($file);
            $jsonConfig = json_decode($content, true);
            if($jsonConfig === false) return false;
            foreach($jsonConfig as $item => $data){
                if($item == "apache"){
                    try{
                        if(strlen($data['errorLog']) == 0 || strlen($data['virtualhost']) == 0)
                            return false;
                        else if(!is_numeric($data['port'])) return false;
                        else {}  // nothing
                    }
                    catch(Exception $indexNotFound){
                        echo $indexNotFound->getMessage();
                        return false; }
                }
                else if($item == "mysql"){
                    try{
                        if(strlen($data['sysuser']) == 0 || strlen($data['passwd']) == 0)
                            return false;
                        else if(strlen($data['db']) == 0 || strlen($data['sock_user']) == 0 || strlen($data['sock_passwd']) == 0)
                            return false;
                        else if(strlen($data['ext_root'][0]) == 0 || strlen($data['ext_normal'][1]) == 0)
                            return false;
                        else {} // do nothing
                    }
                    catch(Exception $indexNotFound){
                        echo $indexNotFound->getMessage();
                        return false; }
                }
                else if($item == "sdk"){
                    try{
                        foreach($data as $sdk){
                            if(strlen($sdk['Name']) == 0 || strlen($sdk['Link']) == 0)
                                return false;
                            else if(strlen($sdk['Version']) == 0 || strlen($sdk['Language']) == 0)
                                return false;
                            else {}  // nothign
                        }
                    }
                    catch(Exception $indexNotFound){
                        echo $indexNotFound->getMessage();
                        return false; }
                }
                else{ return false;}
            }
            return true;
        }

        /**
         * Debugs the configurations file, it will check if the file is valid
         * or not, if it isn't valid, will do a echo command with the error.
         * @param string $file The configurations file to debug
         * @return boolean
         */
        public static function debugConfig(string $file): bool{
            $content = file_get_contents($file);
            $jsonConfig = json_decode($content, true);
            if($jsonConfig === false){
                echo "Invalid syntax";
                return false;
            }
            foreach($jsonConfig as $item => $data){
                if($item == "apache"){
                    try{
                        if(strlen($data['errorLog']) == 0 || strlen($data['virtualhost'])){
                            echo "Invalid errorLog or virtualhost";
                            return false;
                        }
                        else if(!is_numeric($data['port'])) {
                            echo "Invalid port value";
                            return false;
                        }
                        else {}  // nothing
                    }
                    catch(Exception $indexNotFound){
                        echo $indexNotFound->getMessage();
                        return false;
                    }
                }
                else if($item == "mysql"){
                    try{
                        if(strlen($data['sysuser']) == 0 || strlen($data['passwd']) == 0){
                            echo "Invalid user password";
                            return false;
                        }
                        else if(strlen($data['db']) == 0 || strlen($data['sock_user']) == 0 || strlen($data['sock_passwd']) == 0){
                            echo "Invalid DB or the socket user configurations";
                            return false;
                        }
                        else if(strlen($data['ext_root'][0]) == 0 || strlen($data['ext_root'][1]) == 0){
                            echo "Invalid external user data ::mysql";
                            return false;
                        }
                        else if(strlen($data['ext_normal'][0]) == 0 || strlen($data['ext_normal'][1]) == 0){
                            echo "Invalid external normal login :mysql";
                            return false;
                        }
                        else {} // do nothing
                    }
                    catch(Exception $indexNotFound){
                        echo $indexNotFound->getMessage();
                        return false; }
                }
                else if($item == "sdk"){
                    try{
                        $index = 0;
                        foreach($data as $sdk){
                            if(strlen($sdk['Name']) == 0 || strlen($sdk['Link']) == 0){
                                echo "Invalid name/link at index $index";
                                return false;
                            }
                            else if(strlen($sdk['Version']) == 0 || strlen($sdk['Language']) == 0){
                                echo "Invalid version/language at $index";
                                return false;
                            }
                            else {
                                $index++;
                            }  // nothign
                        }
                    }
                    catch(Exception $indexNotFound){
                        echo $indexNotFound->getMessage();
                        return false; }
                }
                else{
                    echo "Invalid field";
                    return false;
                }
            }
            return true;
        }

        /**
         * Class constructor that loads a configurations file and set up the configurations data
         * to the class attribute;
         * @param string $file The configurations file to load.
         */
        public function __construct(string $file){
            if(!is_null($this->configurationsFile)) throw new ConfigurationsLoaded();
            if(!$this->checkConfig($file)) throw new InvalidConfigurations("Invalid configurations file", 1);
            $this->configurationsFile = $file;
            $this->config = json_decode(file_get_contents($file), true);
        }

        /**
         * Writes the configurations,overriding with all the changes on the configurations data
         * @throws ConfigurationsNotLoaded If there's no configurations file loaded.
         * @return void;
         */
        public function commit(){
            if(is_null($this->configurationsFile)) throw new ConfigurationsNotLoaded();
            else{
                $buffer = json_encode($this->config);
                file_put_contents($this->configurationsFile, $buffer);
                unset($buffer);
                return;
            }
        }

        /**
         * Default class destructor, used for garbage collection
         */
        public function __destruct(){
            if(!is_null($this->configurationsFile)){
                $this->commit();
                $this->configurationsFile = null;
                $this->config = null;
            }
        }

        /**
         * **Warning** To avoid a overflow on the localStorage, the quantity of
         * options loaded was limited. Now the database variables aren't visible
         * to the JavaScript localStorage.
         *
         * Dumps the configurations to the localStorage on the JavaScript system.
         * Those configurations values'll be added to JavaScript using the folloing structure:
         *
         * 'apache_virtualhost': apache::virtualhost
         * 'apache_port': apache::port
         * 'apache_error_log': apache::errorLog
         *
         * 'sdks': sdks::(eachOne) # array
         *
         * @throws ConfigurationsNotLoaded If there's no configurations file loaded.
         * @return void;
         */
        public function pushJS(){
            if(is_null($this->configurationsFile)) throw new ConfigurationsNotLoaded();
            $JS = "<script>\n";
            foreach($this->config['apache'] as $apacheConfig => $apacheVl)
                $JS .= "    localStorage.setItem(\"apache_$apacheConfig\", \"$apacheVl\");\n";
            $jsonSdk = json_encode($this->config['sdk']);
            $JS .= "    localStorage.setItem(\"sdks\", \"$jsonSdk\");\n</script>";
            echo $JS;
        }

        /**
         * Method to get the configurations content loaded.
         * Just return the configuratins values.
         * @throws ConfigurationsNotLoaded If there's no configurations file loaded
         * @return array
         */
        public function getConfig(){
            return $this->config;
        }
    }
}
