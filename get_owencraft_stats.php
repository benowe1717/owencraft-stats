<?php

    require_once "logging.class.php";
    $logger = new oclogger("/var/log/", "owencraft_stats.log");

    require_once "prom.class.php";
    $prom = new ocprom("/var/prometheus/", "owencraft_stats.prom", "/tmp/prometheus/", "owencraft_stats-tmp.prom");

    require_once "owencraft_stats.class.php";
    $oc = new ocstats("/minecraft/Owencraft/stats/", "/minecraft/whitelist.json");

    $logger->startScript();

    foreach($oc->files as $file) {

        $logger->logMsg("Working on $file...", 0);
        $oc->getStats($file);

    }

    foreach($oc->objectives as $objective) {

        $tmp = explode(":", $objective);
        $obj = "owencraft_" . $tmp[1] . "_counts gauge\n";
        file_put_contents($prom->tmp_file, $obj, FILE_APPEND | LOCK_EX);

        foreach($oc->players as $player) {

            $msg = "Iterating $objective stats for " . $player["name"] . "...";
            $logger->logMsg($msg, 0);

            if(isset($player[$objective])) {
                foreach($player[$objective] as $key => $value) {

                    $contents = "owencraft_" . $obj . "_counts{objective=" . $key . ",player=" . $player["name"] . "} " . $value . "\n";
                    file_put_contents($prom->tmp_file, $contents, FILE_APPEND | LOCK_EX);

                }
            }

        }

    }

    foreach($oc->players as $player) {

        $msg = "Iterating stats for " . $player["name"];
        $logger->logMsg($msg, 0);



    }

    $logger->stopScript();

?>