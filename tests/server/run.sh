#!/bin/bash
export SERVER_URL="http://localhost:3000"

tests=("./registrationTest.sh" "./lessonListingTest.sh" "./lessonEntriesListingTest.sh")
if [ $# -ge 1 ];
then
    tests=( "$@" )
fi

for test in "${tests[@]}";
do
    echo "Starting $test"
    $test
    echo "$test result code: $?"
    echo

    # Czyszczenie danych tymczasowych po testach
    rm -- *.tmp
done

echo "Running python tests"
for pyTest in test*.py;
do
    echo "Starting $pyTest"
    python3 "$pyTest"
    echo
done
