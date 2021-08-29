#!/bin/bash
source getStack.sh

# argumenty:
# $1: nazwa testu
# $2: otrzymany wynik
# $3: oczekiwany wynik
testFail() {
    echo -e "[ \e[31mfailed\e[0m ]       $1"
    getStack
    echo "place:           $STACK"
    echo "expceted output: $3"
    echo "got:             $2"
    echo -e "[  \e[31mabort\e[0m ]"
    exit 1
}
