#!/bin/bash
# źródło: https://gist.github.com/akostadinov/33bb2606afe1b334169dfbf202991d36
# argumenty:
# $1: komunikat do wyświetlenia
getStack () {
    STACK=""
    local i message="${1:-""}"
    local stack_size=${#FUNCNAME[@]}
    # to avoid noise we start with 1 to skip the get_stack function
    for (( i=1; i<stack_size; i++ )); do
        local func="${FUNCNAME[$i]}"
        [ x"$func" = x ] && func=MAIN
        local linen="${BASH_LINENO[$(( i - 1 ))]}"
        local src="${BASH_SOURCE[$i]}"
        [ x"$src" = x ] && src=non_file_source

        STACK+=$'\n'"   at: $(printf %-20s "$func") $(printf %-28s "$src") $linen"
    done
    STACK="${message}${STACK}"
}
