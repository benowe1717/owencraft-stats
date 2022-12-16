# ABOUT

I run a Minecraft Java Server (called Owencraft) for myself and my family and friends to play on, and we love to brag about all the things we've done (mainly how many Diamonds we have or how many Creepers we've killed) but pausing the game, loading up the Statistics page, and scrolling through lines of text was boring.

So I decided to spruce things up a bit by creating this set of classes to parse each of the User's statistics files stored on the server (uuid.json), place them into a Node Exporter and Prometheus compatible text file, and then pull those results into a Grafana Dashboard.

Now we have fun graphs and visualizations to brag about in real time!

# USAGE

To use these scripts you are going to need a few things:
- Linux ( I wrote and tested on Ubuntu 20.04 LTS, this should work for other Linux flavors and distributions )
- PHP 7 ( I wrote and tested on PHP 7.4 )
- Prometheus ( https://prometheus.io/ )
- Node Exporter ( https://prometheus.io/download/#node_exporter )
- Grafana ( https://grafana.com/ )
- McRCON ( https://github.com/Tiiffi/mcrcon )

Either download and unpack the zip file, or git clone the repo, and run the main script like:
```php get_owencraft_stats.php```

# CONFIGURATION

In the `get_owencraft_stats.php` script, you will want to review each classes' initialization to ensure the folder paths and file paths work for your system. Each class, respectively, will attempt to create the folders and files if they do not exist, so in that case you may want to run this with root privileges.

In the .mcrcon file, be sure to replace each example line with the appropriate values for your configuration. I also recommend settin g this file to readonly with this command:
```
chmod 0400 .mcrcon
```
To prevent anyone other than your user from reading the file

Example:
```
require_once "logging.class.php";
$logger = new oclogger("/var/log/", "owencraft_stats.log");
```

Could be changed to
```
require_once "logging.class.php";
$logger = new oclogger("/home/pi/", "owencraft_stats.log");
```
