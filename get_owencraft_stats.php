<?php

    require_once "logging.class.php";
    $logger = new oclogger("/var/log/", "owencraft_stats.log");

    require_once "prom.class.php";
    $prom = new ocprom("/var/prometheus/", "owencraft_stats.prom", "/tmp/prometheus/", "owencraft_stats-tmp.prom");

    require_once "owencraft_stats.class.php";
    $oc = new ocstats("/minecraft/Owencraft/stats/");

    $logger->startScript();

    foreach($oc->files as $file) {
        $msg = "Working on $file...";
        $logger->logMsg($msg, 0);
    }

    $logger->stopScript();

?>