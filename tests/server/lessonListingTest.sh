#!/bin/bash
cd helpers || exit
# shellcheck source=helpers/testCmd.sh
source testCmd.sh
# shellcheck source=helpers/testCmdRegex.sh
source testCmdRegex.sh
# shellcheck source=helpers/testDb.sh
source testDb.sh
# shellcheck source=helpers/reset.sh
source ./reset.sh
cd ..

# Czyszczenie bazy danych
reset

# Sprawdzenie, że nie ma lekcji
testDb "db reset check" "SELECT COUNT(*) FROM lesson" "0"

# Utworzenie nowego użytkownika abcd i zapisanie sesji
testCmd "user1 creation" "curl -s --cookie-jar session.tmp --data-urlencode login=abcd --data-urlencode password=testtest $SERVER_URL/server/register.php" "{\"result\":0}"

# Utworzenie lekcji lekcja1 użytkownika abcd
testCmdRegex "user1 lesson creation" "curl -s --cookie session.tmp --cookie-jar session.tmp $SERVER_URL/server/createNewLesson.php?name=lekcja1" "createdLessonId[0-9]*result0"
# Sprawdzenie utworzenia lekcji
testDb "db lesson1 creation check" "SELECT COUNT(*) FROM lesson" "1"
testDb "db lesson1 creation check" "SELECT COUNT(*) FROM lesson WHERE name='lekcja1'" "1"

# Sprawdzenie wyniku rządania wypisania lekcji
testCmdRegex "user1 lesson listing" "curl -s --cookie session.tmp --cookie-jar session.tmp $SERVER_URL/server/listLessons.php" "result0dataid[0-9]*namelekcja1"

# Sprawdzenie wyniku rządania wypisania lekcji od użytkownika niezalogowanego
testCmd "not logged user lesson listing" "curl -s $SERVER_URL/server/listLessons.php" "{\"result\":1}"

# Utworzenie nowego użytkownika abcd2 i zapisanie sesji
testCmd "user2 creation" "curl -s --cookie-jar session2.tmp --data-urlencode login=abcd2 --data-urlencode password=testtest $SERVER_URL/server/register.php" "{\"result\":0}"

# Utworzenie lekcji lekcja1 użytkownika abcd2
testCmdRegex "user2 lesson creation" "curl -s --cookie session2.tmp --cookie-jar session2.tmp $SERVER_URL/server/createNewLesson.php?name=lekcja1" "createdLessonId[0-9]*result0"

# Sprawdzenie utworzenia lekcji
testDb "db lesson1 creation check[2]" "SELECT COUNT(*) FROM lesson" "2"
testDb "db lesson1 creation check[2]" "SELECT COUNT(*) FROM lesson WHERE name='lekcja1'" "2"

# Sprawdzenie czy użytkownicy nie mają wzajemnie dostępu do swoich lekcji
testCmdRegex "users mutual lessons listing access restriction" "curl -s --cookie session.tmp --cookie-jar session.tmp $SERVER_URL/server/listLessons.php" "result0dataid[0-9]*namelekcja1"
testCmdRegex "users mutual lessons listing access restriction" "curl -s --cookie session2.tmp --cookie-jar session2.tmp $SERVER_URL/server/listLessons.php" "result0dataid[0-9]*namelekcja1"

# Utworzenie lekcji lekcja2 użytkownika abcd
testCmdRegex "user1 lesson2 creation" "curl -s --cookie session.tmp --cookie-jar session.tmp $SERVER_URL/server/createNewLesson.php?name=lekcja2" "createdLessonId[0-9]*result0"

# Sprawdzenie utworzenia lekcji
testDb "db lesson2 creation check" "SELECT COUNT(*) FROM lesson" "3"
testDb "db lesson2 creation check" "SELECT COUNT(*) FROM lesson WHERE name='lekcja2'" "1"

# Sprawdzenie wyniku rządania wypisania wielu lekcji
testCmdRegex "user1 multiple lesson listing" "curl -s --cookie session.tmp --cookie-jar session.tmp $SERVER_URL/server/listLessons.php" "result0dataid[0-9]*namelekcja1id[0-9]*namelekcja2"

# Utworzenie kolejnych 10 lekcji
for lessonNum in "3" "4" "5" "6" "7" "8" "9" "10" "11" "12"
do
    testCmdRegex "user1 lesson$lessonNum creation" "curl -s --cookie session.tmp --cookie-jar session.tmp $SERVER_URL/server/createNewLesson.php?name=lekcja$lessonNum" "createdLessonId[0-9]*result0"
done

# Sprawdzenie utworzenia powyższych lekcji
testDb "db multiple lessons creation check" "SELECT COUNT(*) FROM lesson" "13"

# Sprawdzenie limitu wyniku rządania wypisania wielu lekcji
testCmdRegex "user1 multiple lesson listing limit" "curl -s --cookie session.tmp --cookie-jar session.tmp $SERVER_URL/server/listLessons.php" "result0dataid[0-9]*namelekcja1id[0-9]*namelekcja2id[0-9]*namelekcja3id[0-9]*namelekcja4id[0-9]*namelekcja5id[0-9]*namelekcja6id[0-9]*namelekcja7id[0-9]*namelekcja8id[0-9]*namelekcja9id[0-9]*namelekcja10"

# Sprawdzenie czy użytkownik abcd2 nie ma dostępu do nowo-utworzonych lekcji
testCmdRegex "user2 mutual lessons listing access restriction" "curl -s --cookie session2.tmp --cookie-jar session2.tmp $SERVER_URL/server/listLessons.php" "result0dataid[0-9]*namelekcja1"
