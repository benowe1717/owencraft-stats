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

    var_dump($oc);

    $logger->stopScript();

?>