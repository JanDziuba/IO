#!/bin/bash

# argumenty:
# $1: komenda SQL do wykonania
dbExecute () {
    echo -n "$(PGPASSWORD=1234 psql -h 127.0.0.1 -d learnio -U learnio -t -A -c "$1")"
}
