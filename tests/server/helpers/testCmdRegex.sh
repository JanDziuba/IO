#!/bin/bash
source ./testResultRegex.sh

# argumenty:
# $1: nazwa testu
# $2: komenda do wykonania
# $3: wzór oczekiwanego wyniku po usunięciu znaków niealfanumerycznych
testCmdRegex () {
    local result
    result=$(echo $($2) | tr -cd '[:alnum:]._-')
    testResultRegex "$1" "$result" "$3"
}
