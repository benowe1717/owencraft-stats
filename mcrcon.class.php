<?php

    require_once "logging.class.php";

    class mcrcon {

        private $config_file = "/root/.mcrcon";
        private $path = "";
        private $hostname = "";
        private $password = "";

        public $logger = "";

        public function __construct() {

            $this->logger = new oclogger("/var/log/", "owencraft_stats.log");

            if(file_exists($this->config_file)) {
                if($contents = file($this->config_file)) {
                    $this->hostname = trim($contents[0]);
                    $this->password = trim($contents[1]);
                    $this->path = trim($contents[2]);
                } else {
                    $msg = "Unable to open the config file!";
                    $this->logger->logMsg($msg, 2);
                    exit(1);
                }
            } else {
                $msg = "Config file does not exist!";
                $this->logger->logMsg($msg, 2);
                exit(1);
            }

        }

        private function getPlayers(string $players) {

            if(preg_match("/^.*?online\:\s+(.*)$/", $players, $matches)) {
                unset($matches[0]);
            } else {
                $matches = array();
            }

            return $matches;

        }

        public function listPlayers() {

            $cmd = "{$this->path} -H {$this->hostname} -p {$this->password} list";
            $exec = exec($cmd, $output, $return);
            if($return === 0) {
                return $this->getPlayers($output[0]);
            } else {
                $msg = "Unable to get player list!";
                $this->logger->logMsg($msg, 2);
                return 0;
            }

        }

    }

?>