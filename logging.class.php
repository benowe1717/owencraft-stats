<?php

    class oclogger {

        private $date = "";
        private $pid = "";
        private $lvl = array();

        function __construct(string $path, string $file) {

            $this->path = $path;
            $this->file = rtrim($this->path, "/") . "/" . $file;
            $this->lvl = array("INFO", "WARN", "ERROR", "DEBUG");

            try {

                if(!is_dir($this->path)) {
                    mkdir($this->path, 0755, true);
                }

                if(!is_file($this->file)) {
                    touch($this->file);
                }

            } catch(Exception $e) {

                exit($e);

            }

        }

        public function logMsg(string $msg, int $lvl) {

            $this->date = date("M j G:i:s");
            $this->pid = getmypid();
            $content = $this->date . " php[" . $this->pid . "] [" . $this->lvl[$lvl] . "] " . $msg . "\n";
            file_put_contents($this->file, $content, FILE_APPEND | LOCK_EX);

        }

        public function startScript() {

            $content = "START OF SCRIPT - " . date("r") . "\n";
            file_put_contents($this->file, $content, FILE_APPEND | LOCK_EX);

            $content = "-----------------------------------------------------------------------------------------------------\n";
            file_put_contents($this->file, $content, FILE_APPEND | LOCK_EX);

        }

        public function stopScript() {
            
            $content = "-----------------------------------------------------------------------------------------------------\n";
            file_put_contents($this->file, $content, FILE_APPEND | LOCK_EX);

            $content = "END OF SCRIPT - " . date("r") . "\n";
            file_put_contents($this->file, $content, FILE_APPEND | LOCK_EX);

        }

    }

?>