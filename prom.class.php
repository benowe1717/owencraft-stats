<?php

    class ocprom {

        public $store_path = "";
        public $store_file = "";
        public $tmp_path = "";
        public $tmp_file = "";

        function __construct(string $store_path, string $store_file, string $tmp_path, string $tmp_file) {

            $this->store_path = $store_path;
            $this->store_file = rtrim($this->store_path, "/") . "/" . $store_file;
            $this->tmp_path = $tmp_path;
            $this->tmp_file = rtrim($this->tmp_path, "/") . "/" . $tmp_file;

            try {

                if(!is_dir($this->store_path)) {
                    mkdir($this->store_path, 0755, true);
                }

                if(!is_dir($this->tmp_path)) {
                    mkdir($this->tmp_path, 0755, true);
                }

                if(!is_file($this->store_file)) {
                    touch($this->store_file);
                }

                if(!is_file($this->tmp_file)) {
                    touch($this->tmp_file);
                }

            } catch(Exception $e) {

                exit($e);

            }

        }

    }

?>