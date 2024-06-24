#!/bin/bash

# Set the default host and port
HOST="localhost"
PORT="8000"

# Function to start the PHP built-in server
start_server() {
    if pgrep -f "php -S $HOST:$PORT" > /dev/null
    then
        echo "PHP server is already running on $HOST:$PORT"
    else
        echo "Starting PHP server on $HOST:$PORT..."
        php -S $HOST:$PORT &
        echo "PHP server started on $HOST:$PORT"
    fi
}

# Function to stop the PHP built-in server
stop_server() {
    PID=$(pgrep -f "php -S $HOST:$PORT")
    if [ -z "$PID" ]
    then
        echo "No PHP server running on $HOST:$PORT"
    else
        echo "Stopping PHP server on $HOST:$PORT with PID $PID..."
        kill $PID
        echo "PHP server stopped"
    fi
}

# Function to show usage
usage() {
    echo "Usage: $0 {start|stop|restart}"
    exit 1
}

# Check the command-line argument
case "$1" in
    start)
        start_server
        ;;
    stop)
        stop_server
        ;;
    restart)
        stop_server
        start_server
        ;;
    *)
        usage
        ;;
esac
