#!/bin/bash

REQUIRED_FILE="$1"

### BEGIN VAR SETUP ###
DATE=/bin/date
ECHO=/bin/echo
HOST=/bin/hostname

ACK=/usr/bin/ack
AWK=/usr/bin/awk
CAT=/bin/cat
CURL=/usr/bin/curl
HEAD=/usr/bin/head
GREP=/usr/bin/grep
PS=/usr/bin/ps
SYSTEMCTL=/usr/bin/systemctl
WC=/usr/bin/wc

BKP=/opt/MXB/bin/ClientTool

BINARIES=($ACK $AWK $CAT $CURL $HEAD $GREP $PS $SYSTEMCTL $WC $BKP)
### END VAR SETUP ###

### BEGIN FUNCTIONS ###
log() {
    [ -f "$DATE" ] && time=`${DATE}` || { log "ERROR" "Cannot find $DATE! Cannot continue!"; exit 1001; }
    [ -f "$HOST" ] && name=`${HOST}` || { log "ERROR" "Cannot find $HOST! Cannot continue!"; exit 1001; }
    [ -f "$ECHO" ] && ${ECHO} "$time $name [$1] $2"
}

root_check() {
    if [[ "$EUID" -ne 0 ]]; then
        log "ERROR" "Are you running as root?"
        exit 1001
    fi
}

check_file() {
    [ -f "$1" ] || { log "ERROR" "Cannot find $1! Cannot continue!"; exit 1001; }
}

check_folder() {
    [ -d "$1" ] || { log "ERROR" "Cannot find $1! Cannot continue!"; exit 1002; }
}

get_pid() {
    ${CAT} $PIDFILE
}

begin() {
    log "INFO" "Starting script..."

    log "INFO" "Starting root check..."
    root_check
    log "INFO" "Root check complete!"

    log "INFO" "Starting options file check..."
    check_file $REQUIRED_FILE
    source $REQUIRED_FILE
    BINARIES+=($MCRCON)
    log "INFO" "Options file check complete!"

    log "INFO" "Starting dependencies check..."
    for i in ${!BINARIES[@]}; do
        log "DEBUG" "Checking if ${BINARIES[i]} exists..."
        check_file ${BINARIES[i]}
    done
    log "INFO" "Dependency check complete!"

    log "INFO" "Starting Minecraft root directory check..."
    check_folder $MINECRAFT_DIR
    log "INFO" "Minecraft root directory check complete!"
}

main() {
    log "INFO" "Starting backup..."
    log "INFO" "Backup complete!"
}
### END FUNCTIONS ###

### MAIN ###
begin