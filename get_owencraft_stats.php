<?php

    require_once "logging.class.php";
    $logger = new oclogger("/var/log/", "owencraft_stats.log");

    require_once "prom.class.php";
    $prom = new ocprom("/var/prometheus/", "owencraft_stats.prom", "/tmp/prometheus/", "owencraft_stats-tmp.prom");

    require_once "owencraft.class.php";
    $oc = new owencraft();

    $logger->startScript();

    var_dump($logger);
    var_dump($prom);
    var_dump($oc);

    $logger->stopScript();

?>