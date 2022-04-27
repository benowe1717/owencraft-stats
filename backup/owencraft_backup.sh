#!/bin/bash

### Uncomment the DEBUG line to get debug logs ###
DEBUG="TRUE"
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
SED=/usr/bin/sed
SYSTEMCTL=/usr/bin/systemctl
WC=/usr/bin/wc

BKP=/opt/MXB/bin/ClientTool

BINARIES=($ACK $AWK $CAT $CURL $HEAD $GREP $PS $SED $SYSTEMCTL $WC $BKP)
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

check_pid() {
    ${PS} -ef | ${GREP} "$1" | ${HEAD} -n1 | ${WC} -l
}

strip_escape_chars() {
    # This is here because, I found, that when passing in names to MCRCON,
    # there were special characters at the end of the string messing with the command.
    # They looked something like usernameESC[0m
    # I don't know what these are, but they shouldn't be there, and this sed command
    # strips them and leaves us with the string we want
    RESULT=$(${ECHO} "$1" | ${SED} 's/\x1B\[[0-9;]*[JKmsu]//g')
    ${ECHO} "$RESULT"
}

send_msg() {
    RESULT=$(${MCRCON} -H "$HOSTNAME" -p "$PASSWORD" "say $1")
    [ ! -z "$DEBUG" ] && log "DEBUG" "$RESULT"
    [ ! -z "$RESULT" ] && { log "ERROR" "Unable to send message to the server through MCRCON! Consider enabling DEBUG logging..."; exit 1004; }
}

list_users() {
    RESULT=$(${MCRCON} -H "$HOSTNAME" -p "$PASSWORD" list)
    ${ECHO} "$RESULT"
}

kick_users() {
    USER=$(strip_escape_chars "$1")
    RESULT=$(${MCRCON} -H "$HOSTNAME" -p "$PASSWORD" "kick $USER")
    ${ECHO} "$RESULT"
}


parse_users() {
    DATA=($(${ECHO} "$1" | ${ACK} "^.*online\:\s+(.*?$)" --output '$1'))
    [ ! -z "$DEBUG" ] && log "DEBUG" "Found ${#DATA[@]} users logged in: ${DATA[*]}"
    for i in ${!DATA[@]}; do
        # If there are multiple users, the user list will be separated by commas, so we just
        # strip the commas if they exist as they're not part of the actual username
        USER=$(${ECHO} ${DATA[i]} | ${SED} 's/\,//g')
        [ ! -z "$DEBUG" ] && log "DEBUG" "Kicking user: $USER..."
        RESULT=$(kick_users "$USER")
        [ ! -z "$DEBUG" ] && log "DEBUG" "$RESULT"
        if [[ ! "$RESULT" =~ .*"Kicked by an operator" ]]; then
            log "ERROR" "Unable to kick player $USER through MCRCON! Consider enabling DEBUG logging..."
            # Uncomment this if you want the server to stop online when everyone has been manually kicked
            # I choose not to do this here b/c the server stopping will kick everyone anyways, this is just a niceity
            # If you find it is causing problems with users, then exiting here is important
            # exit 1006
        fi
    done
}

get_users() {
    USERS=$(list_users)
    [ ! -z "$DEBUG" ] && log "DEBUG" "Player list: $USERS"
    if [[ ! "$USERS" =~ .*"players online:" ]]; then
        log "ERROR" "Unable to get player list through MCRCON! Consider enabling DEBUG logging..."
        exit 1005
    fi
    COUNT=$(${ECHO} "$USERS" | ${AWK} '{ print $3 }')
    [ ! -z "$DEBUG" ] && log "DEBUG" "Count of online players: $COUNT"
    if [[ "$COUNT" = "0" ]]; then
        log "INFO" "No players online!"
    elif [[ "$COUNT" -ge "1" ]]; then
        log "INFO" "Found players online! Logging out all users!"
        parse_users "$USERS"
    fi
}

post_discord() {
    RESULT=$(${CURL} -s -XPOST -H "Content-Type: application/json" -d "{\"content\":\"$1\"}" $WEBHOOK 2>&1)
    ${ECHO} "$RESULT"
}

set_minecraft_status() {
    ${SYSTEMCTL} "$1" "$PROG" || { log "ERROR" "Unable to $1 the Owencraft Minecraft Server! Cannot continue!"; exit 1008; }
}

begin() {
    log "INFO" "Starting script..."

    log "INFO" "Starting root check..."
    [ ! -z "$DEBUG" ] && log "DEBUG" "Comparing EUID: $EUID against 0..."
    root_check
    log "INFO" "Root check complete!"

    log "INFO" "Starting options file check..."
    check_file $REQUIRED_FILE
    source $REQUIRED_FILE
    BINARIES+=($MCRCON)
    log "INFO" "Options file check complete!"

    log "INFO" "Starting dependencies check..."
    for i in ${!BINARIES[@]}; do
        [ ! -z "$DEBUG" ] && log "DEBUG" "Checking if ${BINARIES[i]} exists..."
        check_file ${BINARIES[i]}
    done
    log "INFO" "Dependency check complete!"

    log "INFO" "Starting Minecraft root directory check..."
    check_folder $MINECRAFT_DIR
    log "INFO" "Minecraft root directory check complete!"
}

main() {
    log "INFO" "Starting backup..."

    ### BEGIN PLAYER WARNING ###
    [ ! -z "$DEBUG" ] && log "DEBUG" "Sending 5 minute warning message to server..."
    send_msg "Backup will start in 5 minutes! Please log out in a safe place or you will be forcibly kicked..."
    # sleep 180

    [ ! -z "$DEBUG" ] && log "DEBUG" "Sending 2 minute warning message to server..."
    send_msg "Backup will start in 2 minutes! Please log out in a safe place or you will be forcibly kicked..."
    # sleep 60

    [ ! -z "$DEBUG" ] && log "DEBUG" "Sending 1 minute warning message to server..."
    send_msg "Backup will start in 1 minute! Please log out in a safe place or you will be forcibly kicked..."
    # sleep 60
    ### END PLAYER WARNING ###

    ### BEGIN ONLINE PLAYER CHECK ###
    log "INFO" "Checking for any online players..."
    # get_users
    log "INFO" "Online player check complete!"
    ### END ONLINE PLAYER CHECK ###

    ### BEGIN SERVER SHUTDOWN ###
    log "INFO" "Stopping the Owencraft Minecraft server..."
    [ ! -z "$DEBUG" ] && log "DEBUG" "Posting message to Discord channel..."
    [ ! -z "$DEBUG" ] && log "DEBUG" "The Owencraft Server is now offline! Category: Maintenance Reason: Daily Backup"
    RESULT=$(post_discord "The Owencraft Server is now offline! Category: Maintenance Reason: Daily Backup")
    [ ! -z "$DEBUG" ] && log "$RESULT"
    [ ! -z "$RESULT" ] && { log "ERROR" "Unable to post message to the Discord channel! Consider enabling DEBUG logging..."; exit 1007; }
    PID=$(get_pid)
    set_minecraft_status "stop"
    sleep 30 # give the server plenty of time to shutdown, you may want to adjust this
    log "INFO" "Owencraft Minecraft server stopped successfully!"
    ### END SERVER SHUTDOWN ###

    ### BEGIN RUN BACKUP ###
    log "INFO" "Running backup with the N-Able Backup Manager Client Tool..."
    log "INFO" "Backup complete!"
    ### END RUN BACKUP ###
}
### END FUNCTIONS ###

### MAIN ###
begin
main
