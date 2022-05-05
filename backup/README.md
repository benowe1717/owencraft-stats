# ABOUT

Part of good server management is having good backups! This script helps accomplish having good, clean, and consistent backups of just the minecraft server data that can be restored in case of an emergency.

# DEPENDENCIES

- MCRCON: https://github.com/Tiiffi/mcrcon
- N-Able Backup: https://www.n-able.com/products/backup
- Discord: https://support.discord.com/hc/en-us/articles/228383668-Intro-to-Webhooks
- cURL
- ack / ack-grep
- systemctl / init.d

# USAGE

To use this script, make sure that you've:
- Installed the required dependencies
- Filled out the .options file with the appropriate values
- Set the absolute paths to the required binaries in the script

Then you can simply run the script like:
```sudo ./owencraft_backup.sh .options```