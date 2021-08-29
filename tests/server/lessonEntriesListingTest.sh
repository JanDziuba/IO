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


# Próba wypisania wpisów nieistniejącej lekcji
testCmd "nonexisting lesson entries listing" "curl -s --cookie session.tmp --cookie-jar session.tmp $SERVER_URL/server/getLessonEntries.php?lessonId=0" "{\"result\":4}"

# Utworzenie lekcji lekcja1 użytkownika abcd
testCmdRegex "user1 lesson creation" "curl -s --cookie session.tmp --cookie-jar session.tmp $SERVER_URL/server/createNewLesson.php?name=lekcja1" "createdLessonId[0-9]*result0"
# Sprawdzenie utworzenia lekcji
testDb "db lesson1 creation check" "SELECT COUNT(*) FROM lesson" "1"
testDb "db lesson1 creation check" "SELECT COUNT(*) FROM lesson WHERE name='lekcja1'" "1"

# Pobranie id dodanej lekcji
lesson1Id=$(dbExecute "SELECT id FROM lesson WHERE name='lekcja1' AND user_fk='abcd';")

# Sprawdzenie wyniku żądania wypisania listy słów lekcji lekcja1
testCmd "lesson1 entries listing" "curl -s --cookie session.tmp --cookie-jar session.tmp $SERVER_URL/server/getLessonEntries.php?lessonId=$lesson1Id" "{\"data\":[],\"result\":0}"

# Utworzenie pierwszego wpisu w lekcji lekcja1
testCmdRegex "lesson1 entry1 adding" "curl -s --cookie session.tmp --cookie-jar session.tmp $SERVER_URL/server/addLessonEntry.php?lessonId=$lesson1Id&question=question1&answer=answer1" "createdEntryId[0-9]*result0"

# Sprawdzenie utworzenia wpisu lekcji w bazie danych
testDb "db entry1 creation check" "SELECT COUNT(*) FROM flashcard WHERE lesson_fk = $lesson1Id" "1"

# Pobranie id dodanego wpisu
entry1Id=$(dbExecute "SELECT id FROM flashcard WHERE lesson_fk=$lesson1Id")

# Sprawdzenie wyniku żądania wypisania listy słów lekcji lekcja1 po utworzeniu wpisu
testCmdRegex "lesson1 1 entry listing" "curl -s --cookie session.tmp --cookie-jar session.tmp $SERVER_URL/server/getLessonEntries.php?lessonId=$lesson1Id" "dataid[0-9]*questionquestion1answeranswer1result0"

# Utworzenie drugiego wpisu w lekcji lekcja1
testCmdRegex "lesson1 entry2 adding" "curl -s --cookie session.tmp --cookie-jar session.tmp $SERVER_URL/server/addLessonEntry.php?lessonId=$lesson1Id&question=question2&answer=answer2" "createdEntryId[0-9]*result0"

# Sprawdzenie utworzenia wpisu lekcji w bazie danych
testDb "db entry2 creation check" "SELECT COUNT(*) FROM flashcard WHERE lesson_fk = $lesson1Id" "2"

# Sprawdzenie wyniku żądania wypisania listy słów lekcji lekcja1 po utworzeniu wpisu
testCmdRegex "lesson1 2 entries listing" "curl -s --cookie session.tmp --cookie-jar session.tmp $SERVER_URL/server/getLessonEntries.php?lessonId=$lesson1Id" "dataid[0-9]*questionquestion1answeranswer1id[0-9]*questionquestion2answeranswer2result0"

# Edycja wpisu lekcji
testCmd "lesson1 entry1 edition" "curl -s --cookie session.tmp --cookie-jar session.tmp $SERVER_URL/server/editLessonEntry.php?entryId=$entry1Id&newQuestion=newQuestion1&newAnswer=newAnswer1" "{\"result\":0}"

# Sprawdzenie wyniku żądania wypisania listy słów lekcji lekcja1 po edycji wpisu
testCmdRegex "lesson1 2 entries listing after edition" "curl -s --cookie session.tmp --cookie-jar session.tmp $SERVER_URL/server/getLessonEntries.php?lessonId=$lesson1Id" "dataid[0-9]*questionquestion2answeranswer2id[0-9]*questionnewQuestion1answernewAnswer1result0"

# Usunięcie wpisu lekcji
testCmd "lesson1 entry1 removal" "curl -s --cookie session.tmp --cookie-jar session.tmp $SERVER_URL/server/removeLessonEntry.php?entryId=$entry1Id" "{\"result\":0}"

# Sprawdzenie wyniku żądania wypisania listy słów lekcji lekcja1 po usunięciu wpisu
testCmdRegex "lesson1 1 entry listing after removal" "curl -s --cookie session.tmp --cookie-jar session.tmp $SERVER_URL/server/getLessonEntries.php?lessonId=$lesson1Id" "dataid[0-9]*questionquestion2answeranswer2result0"

# TODO sprawdzenie wszystkich wymogów api
