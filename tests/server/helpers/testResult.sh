#!/bin/bash
source ./testPass.sh
source ./testFail.sh

# argumenty:
# $1: nazwa testu
# $2: otrzymany wynik
# $3: oczekiwany wynik
# shellcheck disable=SC2119
testResult () {
    if [ "$2" == "$3" ]; then
        testPass "$1"
    else
        testFail "$1" "$2" "$3"
    fi
}
