#!/bin/bash
source ./testResult.sh

# argumenty:
# $1: nazwa testu
# $2: komenda do wykonania
# $3: oczekiwany wynik
testCmd () {
    local result
    result=$($2)
    testResult "$1" "$result" "$3"
}
