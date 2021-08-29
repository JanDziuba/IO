#!/bin/bash
source ./testPass.sh
source ./testFail.sh

# argumenty:
# $1: nazwa testu
# $2: otrzymany wynik
# $3: wzorzec oczekiwanego wyniku
# shellcheck disable=SC2119
testResultRegex () {
    if [[ $2 =~ $3 ]]; then
        testPass "$1"
    else
        testFail "$1" "$2" "$3"
    fi
}

