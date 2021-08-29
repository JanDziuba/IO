#!/bin/bash
cd helpers || exit
# shellcheck source=helpers/testCmd.sh
source ./testCmd.sh
# shellcheck source=helpers/testDb.sh
source ./testDb.sh
# shellcheck source=helpers/reset.sh
source ./reset.sh
cd ..

# Czyszczenie bazy danych
reset
testDb "db reset check" "SELECT COUNT(*) FROM account;" "0"

VALID_PASSWORD="testtest"

register () {
    echo "curl -s --data-urlencode login=$1 --data-urlencode password=$2 $SERVER_URL/server/register.php"
}

# Rejestracja nowego użytkownika
testCmd "user1 registration" "$(register abcd $VALID_PASSWORD)" "{\"result\":0}"
testDb "user1 registration db check" "SELECT login FROM account WHERE login='abcd';" "abcd"
testDb "users nums after registration1 db check" "SELECT COUNT(*) FROM account WHERE login='abcd';" "1"

# Rejestracja nowego użytkownika o innym loginie
testCmd "user2 registration" "$(register abcd2 $VALID_PASSWORD)" "{\"result\":0}"
testDb "users nums after registration2 db check" "SELECT COUNT(*) FROM account;" "2"

# Rejestracja nowego użytkownika o powtórzonym loginie
testCmd "duplicated login1 registration" "$(register abcd $VALID_PASSWORD)" "{\"result\":6}"
testCmd "duplicated login2 registration" "$(register abcd2 $VALID_PASSWORD)" "{\"result\":6}"
testDb "users nums after duplicated login registrations db check" "SELECT COUNT(*) FROM account;" "2"

# Sprawdzenie czy serwer zwraca odpowiedni błąd w przypadku braku odpowiedznich parametrów rządania logowania
# Brakujący login
testCmd "missing login" "curl -s --data-urlencode password=$VALID_PASSWORD $SERVER_URL/server/register.php" "{\"result\":2}"
# Brakujące hasło
testCmd "missing password" "curl -s --data-urlencode login=abcd $SERVER_URL/server/register.php" "{\"result\":2}"
testDb "users nums after invalid request db check" "SELECT COUNT(*) FROM account;" "2"

# Sprawdzenie czy serwer wymaga zastrzerzeń odnośnie hasła i loginu przy rejestracji
# Pusty login
testCmd "empty login" "$(register '' $VALID_PASSWORD)" "{\"result\":3}"
# Puste hasło
testCmd "empty password" "$(register abcd)" "{\"result\":3}"

for invalidLogin in "a" "abcdefghijabcdefghijabcdefghijabcdefghij"
do
    testCmd "invalid login '$invalidLogin'" "$(register $invalidLogin $VALID_PASSWORD)" "{\"result\":4}"
done
for invalidPassword in "abcde" "abcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghij"
do
    testCmd "invalid password '$invalidPassword'" "$(register abcd5 $invalidPassword)" "{\"result\":5}"
done
testDb "users nums after invalid user data db check" "SELECT COUNT(*) FROM account;" "2"

# Po rejestracji użytkownik powinien być automatycznie logowany
testCmd "user3 registration" "curl -s --cookie-jar session.tmp --data-urlencode login=abcd3 --data-urlencode password=$VALID_PASSWORD $SERVER_URL/server/register.php" "{\"result\":0}"
testDb "users nums after registration3 db check" "SELECT COUNT(*) FROM account;" "3"
# Sprawdzenie loginu aktualnie zalogowanego użytkownika
testCmd "user3 automatical login check" "curl -s --cookie session.tmp --cookie-jar session.tmp $SERVER_URL/server/getLogin.php" "{\"result\":0,\"login\":\"abcd3\"}"

# Zalogowany użytkownik nie może dokonać rejestracji
testCmd "logged in user registration" "curl -s --cookie session.tmp --data-urlencode login=abcd4 --data-urlencode password=$VALID_PASSWORD $SERVER_URL/server/register.php" "{\"result\":1}"
testDb "users nums logged user db check" "SELECT COUNT(*) FROM account;" "3"
# Rejestracja nowego użytkownika o wcześniejszym loginie, żeby sprawdzić, że blokada faktycznie istaniała ze względu na zalogowanie
testCmd "not logged in user registration check" "$(register abcd4 $VALID_PASSWORD)" "{\"result\":0}"
testDb "users nums after registration4 db check" "SELECT COUNT(*) FROM account;" "4"
