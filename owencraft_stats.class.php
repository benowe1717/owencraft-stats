<?php

    require_once "logging.class.php";

    class ocstats {

        public $objectives = array();
        public $stats_path = "";
        public $whitelist = "";
        public $files = array();
        public $players = array();

        function __construct(string $stats_path, string $whitelist) {

            $this->objectives = array("minecraft:crafted", "minecraft:mined", "minecraft:custom", "minecraft:dropped", "minecraft:used", "minecraft:broken", "minecraft:killed_by", "minecraft:picked_up", "minecraft:killed");
            $this->stats_path = rtrim($stats_path, "/") . "/";

            self::buildFileList();

            $this->whitelist = $whitelist;
            self::buildPlayerList();

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

        private function buildPlayerList() {

            $json = file_get_contents($this->whitelist);
            $arr = json_decode($json, true);
            foreach($arr as $player) {
                $uuid = $player["uuid"];
                $name = $player["name"];
                $this->players[$uuid] = $name;
            }

        }

        public function getStats(string $file) {

            $player = end(explode($file, "/"));
            $json = file_get_contents($file);
            $arr = json_decode($json, true);
            foreach($arr["stats"] as $key => $value) {

                if(in_array($key, $this->objectives)) {
                    $this->players[$player][$key] = $value;
                }

            }

        }

    }

?>