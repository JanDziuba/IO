#!/bin/bash
reset () {
    PGPASSWORD=1234 psql -h 127.0.0.1 -d learnio -U learnio -c 'DELETE FROM account;' > /dev/null
}
