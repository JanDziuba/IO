#!/bin/bash
source getStack.sh

# argumenty:
# $1: nazwa testu
testPass () {
    echo -e "[ \e[32mpassed\e[0m ]       $1"
}
