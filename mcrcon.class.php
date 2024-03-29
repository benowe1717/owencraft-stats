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

            $loggedIn = array();
            if(preg_match("/^.*?online\:\s+(.*)$/", $players, $matches)) {
                unset($matches[0]); // this is set to remove the entire string
                foreach($matches as $match) {
                    // the string in the $match variable may appear to be empty
                    // but it is only visually empty and is actually 4 bytes in length
                    // according to the strlen() function. so doing an empty(), is_null(),
                    // or $match !== "" will not work here

                    // Also worth noting that the mcrcon output is strange and contains some
                    // ANSI escape characters in it, possible for coloring or just a result
                    // of the program, unsure. but this preg_replace() function will remove
                    // those characters and give us a sanitized string
                    if(strlen($match) > 4) {
                        // When multiple users are logged in, we get a single string that is comma-
                        // separated with each user represented like this:
                        // username1, username2, username3
                        // so we check for a comma and if so explode the string and parse on each
                        // set, making sure to trim any whitespace found at the beginning or end
                        if(strpos($match, ",")) {
                            $players = explode(",", $match);
                            foreach($players as $player) {
                                $loggedIn[] = trim(preg_replace("/\e[[][A-Za-z0-9];?[0-9]*m?/", "", $player)); // update the array with the logged in user
                            }
                        } else {
                            $loggedIn[] = trim(preg_replace("/\e[[][A-Za-z0-9];?[0-9]*m?/", "", $match)); // update the array with the logged in user
                        }
                    }
                }
            }

            return $loggedIn;

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
