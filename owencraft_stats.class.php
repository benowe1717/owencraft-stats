<?php

    class ocstats {

        public $objectives = array();
        public $stats_path = "";
        public $files = array();

        function __construct(string $stats_path) {

            $this->objectives = array("minecraft:crafted", "minecraft:mined", "minecraft:custom", "minecraft:dropped", "minecraft:used", "minecraft:broken", "minecraft:killed_by", "minecraft:picked_up", "minecraft:killed");
            $this->stats_path = rtrim($stats_path, "/") . "/";

            self::buildFileList();

        }

        private function buildFileList() {

            $array = scandir($this->stats_path);
            foreach($array as $key => $value) {

                if(preg_match("/^.*?\.json/", $value)) {
                    $file = $this->stats_path . $value;
                    $this->files[] = $file;
                }

            }

        }

    }

?>