<?php

    $base_path = "/minecraft";
    $world_path = "/Owencraft";
    $base_server_path = $base_path . $world_path;
    $stats_path = $base_server_path . "/stats";
    $whitelist_file = $base_path . "/whitelist.json";

    $server_jar = $base_path . "/vanilla.jar";

    require_once "logging.class.php";
    $logger = new oclogger("/var/log/", "owencraft_stats.log");

    require_once "prom.class.php";
    $prom = new ocprom("/var/prometheus/", "owencraft_stats.prom", "/tmp/prometheus/", "owencraft_stats-tmp.prom");

    require_once "owencraft_stats.class.php";
    $oc = new ocstats($stats_path, $whitelist_file);

    require_once "mcrcon.class.php";

    function getMinecraftServerVersion(string $server_jar) {

        // this should return something like 1.19 or 1.17.1
        $cmd = "/usr/bin/unzip -p " . $server_jar . " version.json | /usr/bin/grep 'name' | /usr/bin/awk '{ print $2 }' | /usr/bin/sed 's/[\",]//g'";
        if(file_exists($server_jar)) {
            $exec = exec($cmd, $output, $return);
            if($return === 0) {
                return $output[0];
            } else {
                return 0.00;
            }
        } else {
            return 0.00;
        }

    }

    function getLoggedInPlayers() {

        $mcrcon = new mcrcon();
        return $mcrcon->listPlayers();

    }

    $logger->startScript();

    foreach($oc->files as $file) {

        $logger->logMsg("Working on $file...", 0);
        $oc->getStats($file);

    }

    foreach($oc->objectives as $objective) {

        $tmp = explode(":", $objective);
        $obj = "# TYPE owencraft_" . $tmp[1] . "_counts gauge\n";
        file_put_contents($prom->tmp_file, $obj, FILE_APPEND | LOCK_EX);

        foreach($oc->players as $player) {

            $msg = "Iterating $objective stats for " . $player["name"] . "...";
            $logger->logMsg($msg, 0);

            if(isset($player[$objective])) {
                foreach($player[$objective] as $key => $value) {

                    $contents = "owencraft_" . $tmp[1] . "_counts{objective=\"" . $key . "\",player=\"" . $player["name"] . "\"} " . $value . "\n";
                    file_put_contents($prom->tmp_file, $contents, FILE_APPEND | LOCK_EX);

                }
            }

        }

    }

    $msg = "Grabbing server version...";
    $logger->logMsg($msg, 0);
    $server_version = getMinecraftServerVersion($server_jar);
    $obj = "# TYPE owencraft_misc_counts gauge\n";
    file_put_contents($prom->tmp_file, $obj, FILE_APPEND | LOCK_EX);
    $contents = "owencraft_misc_counts{objective=\"server_version\"} " . $server_version . "\n";
    file_put_contents($prom->tmp_file, $contents, FILE_APPEND | LOCK_EX);

    $msg = "Grabbing count of logged in players...";
    $logger->logMsg($msg, 0);
    $player_count = count(getLoggedInPlayers());
    $contents = "owencraft_misc_counts{objective=\"logged_in_players\"} " . $player_count . "\n";
    file_put_contents($prom->tmp_file, $contents, FILE_APPEND | LOCK_EX);

    $msg = "Moving " . $prom->tmp_file . " to " . $prom->store_file . "...";
    $logger->logMsg($msg, 0);

    rename($prom->tmp_file, $prom->store_file);

    $logger->stopScript();

?>
