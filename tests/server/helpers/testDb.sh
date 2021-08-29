#!/bin/bash
source ./testResult.sh
source ./dbExecute.sh

# argumenty:
# $1: nazwa testu
# $2: komenda sql do wykonania
# $3: oczekiwany wynik
testDb () {
    local result
    result=$(dbExecute "$2")
    testResult "$1" "$result" "$3"
}
