<?php

    require_once "logging.class.php";
    $logger = new oclogger("/var/log/", "owencraft_stats.log");

    $logger->startScript();

    $msg = "Testing logging class...";
    $logger->logMsg($msg, 0);

    $logger->stopScript();

?>