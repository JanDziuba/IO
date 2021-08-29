<?php
    require 'config.php';
    
    /**
     * Dodaje lekcję o zadanej nazwie i opisie do bazy danych.
     * 
     * @param {String} $creatorLogin Login użytkownika tworzącego lekcję.
     * @param {String} $name Nazwa lekcji.
     * @param {String} $description Opis lekcji.
     * @return Integer Id utworzonej lekcji w przypadku gdy operacja zakończy sie sukcesem.
     * @throws Exception("Unexpected db error", 1)
     *         W przypadku niespodziewanego błędu bazy danych funkcja rzuca wyjątkiem   
     */
    function createLesson($creatorLogin, $name, $description) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
        $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
        $result = pg_query_params($conn, 'INSERT INTO lesson(name, description, user_fk) VALUES ($1, $2, $3) RETURNING id',
            array($name, $description, $creatorLogin));
        if ($result === false || pg_num_rows($result) != 1) {
            throw new Exception("Unexpected db error");
        }
        return intval(pg_fetch_row($result)[0]);
    }

    /**
     * Zwraca listę lekcji.
     * 
     * @param {String} $requestingUserLogin Login użytkownika, dla którego zwracane są lekcje.
     * @param {String} $lessonsNumLimit Limit liczby lekcji do zwrócenia. Dla -1 zwraca wszystkie.
     * @return {Object[]} Tabelka lekcji, czyli obiektów zawierających dwa atrybuty
     *         name - nazwę lekcji i id - identyfikator lekcji.
     */
    function getLessonsList($requestingUserLogin, $lessonsNumLimit) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
        $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
        if ($lessonsNumLimit == -1) {
            $result = pg_query_params($conn, 'SELECT id, name FROM lesson WHERE user_fk = $1', 
                array($requestingUserLogin));
        }
        else {
            $result = pg_query_params($conn, 'SELECT id, name FROM lesson WHERE user_fk = $1 LIMIT $2',
                array($requestingUserLogin, $lessonsNumLimit));
        }
        return pg_fetch_all($result);
    }

    /**
     * Zwraca listę wpisów zadanej lekcji.
     * 
     * @param {String} $requestingUserLogin Login użytkownika, dla którego zwracana jest lista wpisów.
     * @param {String} $lessonId Id lekcji dla której listę wpisów zwracamy.
     * @return {Object[]} Tabelka wpisów, czyli obiektów postaci
     *         {"id":[id wpisu], "question":[pole question], "answer":[pole answer]}
     *         false w przypadku, gdy podana lekcja nie istnieje
     *              lub użytkownik nie ma odpowiednich praw dostępu
     * @throws Exception("Unexpected db error", 1)
     *         W przypadku niespodziewanego błędu bazy danych funkcja rzuca wyjątkiem         
     */
    function getLessonEntries($requestingUserLogin, $lessonId) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
        $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
        // sprawdzenie czy zadana lekcja istnieje
        // i czy użytkownik ma do niej odpowiednie prawa dostępu
        $check = pg_query_params($conn, 'SELECT id FROM lesson WHERE lesson.user_fk = $1 AND lesson.id = $2',
            array($requestingUserLogin, $lessonId));
        if ($check === false) {
            throw new Exception("Unexpected db error");
        }
        if (pg_num_rows($check) == 0) {
            return false;
        }
        $result = pg_query_params($conn, 'SELECT flashcard.id, flashcard.question, flashcard.answer FROM lesson INNER JOIN flashcard ON lesson.id = flashcard.lesson_fk WHERE lesson.user_fk = $1 AND lesson.id = $2',
            array($requestingUserLogin, $lessonId));
        if ($result === false) {
            throw new Exception("Unexpected db error", 1);
        }
        return pg_num_rows($result) == 0 ? array() : pg_fetch_all($result);
    }

    /**
     * Dodaje wpis do lekcji.
     * 
     * @param {String} $requestingUserLogin Login użytkownika, który dodaje wpis
     * @param {Integer} $lessonId Id lekcji, do której dodawany jest wpis
     * @param {String} $question pole pytanie wpisu lekcji do dodania
     * @param {String} $answer pole odpowiedź wpisu lekcji do dodania
     * @return Integer id utworzonego wpisu w przypadku sukcesu
     *         false w przypadku niespodziewanego błędu bazy danych
     * @throws Exception("Unexpected db error")
     *         W przypadku niespodziewanego błędu bazy danych funkcja rzuca wyjątkiem
     */
    function addLessonEntry($requestingUserLogin, $lessonId, $question, $answer) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
        $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
        // sprawdzenie czy zadana lekcja istnieje
        // i czy użytkownik ma do niej odpowiednie prawa dostępu
        $check = pg_query_params($conn, 'SELECT id FROM lesson WHERE lesson.user_fk = $1 AND lesson.id = $2',
            array($requestingUserLogin, $lessonId));
        if ($check === false) {
            throw new Exception("Unexpected db error");
        }
        if (pg_num_rows($check) == 0) {
            return false;
        }
        $result = pg_query_params($conn, 'INSERT INTO flashcard(question, answer, lesson_fk) VALUES ($1, $2, $3) RETURNING id',
            array($question, $answer, $lessonId));
        if ($result === false || pg_num_rows($result) != 1) {
            throw new Exception("Unexpected db error");
        }
        // uaktualnienie czasu modyfikacji lekcji
        $res = pg_query_params($conn, 'UPDATE lesson SET modification_timestamp = CURRENT_TIMESTAMP WHERE id = $1;', array($lessonId));
        if ($res === false) {
            throw new Exception("Unexpected db error");
        }
        return pg_fetch_row($result);
    }

    /**
     * Edytuje wpis lekcji.
     * 
     * @param {String} $requestingUserLogin Login użytkownik wykonującego edycję wpisu
     * @param {Integer} $entryId Id wpisu do edycji
     * @param {String | false} $newQuestion nowe pole pytania lekcji | brak aktualizacji pola pytania
     * @param {String | false} $newAnswer nowe pole odpowiedzi lekcji | brak aktualizacji pola odpowiedzi
     * @return true w przypadku udanej edycji
     *         false jeżeli podany wpis nie istnieje lub użytkownik nie ma praw do jego edycji
     * @throws Exception("Unexpected db error")
     *         W przypadku niespodziewanego błędu bazy danych funkcja rzuca wyjątkiem
     */
    function editLessonEntry($requestingUserLogin, $entryId, $newQuestion, $newAnswer) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
        $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
        // sprawdzenie czy zadana lekcja istnieje
        // i czy użytkownik ma do niej odpowiednie prawa dostępu
        $check = pg_query_params($conn, 'SELECT lesson.id FROM lesson INNER JOIN flashcard ON lesson.id = flashcard.lesson_fk WHERE lesson.user_fk = $1 AND flashcard.id = $2',
            array($requestingUserLogin, $entryId));
        if ($check === false) {
            throw new Exception("Unexpected db error");
        }
        if (pg_num_rows($check) == 0) {
            return false;
        }
        $lessonId = pg_fetch_all($check)[0]['id'];
        $result;
        if ($newQuestion !== false) {
            if ($newAnswer !== false) {
                $result = pg_query_params($conn, 'UPDATE flashcard SET question = $1, answer = $2 WHERE id = $3',
                    array($newQuestion, $newAnswer, $entryId));
            } else {
                $result = pg_query_params($conn, 'UPDATE flashcard SET question = $1 WHERE id = $2',
                    array($newQuestion, $entryId));
            }
        } else {
            $result = pg_query_params($conn, 'UPDATE flashcard SET answer = $1 WHERE id = $2',
                array($newAnswer, $entryId));
        }
        if ($result === false || pg_affected_rows($result) != 1) {
            throw new Exception("Unexpected db error");
        }
        // uaktualnienie czasu modyfikacji lekcji
        $res = pg_query_params($conn, 'UPDATE lesson SET modification_timestamp = CURRENT_TIMESTAMP WHERE id = $1;', array($lessonId));
        if ($res === false) {
            throw new Exception("Unexpected db error");
        }
        return true;
    }

    /**
     * Usuwa wpis lekcji.
     * 
     * @param {String} $requestingUserLogin Login użytkownika usuwającego wpis
     * @param {Integer} $entryId Id wpisu do usunięcia
     * @return true jeżeli usunięcie wpisu zakończyło się sukcesem
     *         false jeżeli użytkownik nie posiadał dostępu do wpisu lub jeśli wpis nie istniał
     * @throws Exception("Unexpected db error")
     *         W przypadku niespodziewanego błędu bazy danych funkcja rzuca wyjątkiem
     */
    function removeLessonEntry($requestingUserLogin, $entryId) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
        $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
        // sprawdzenie czy zadana lekcja istnieje
        // i czy użytkownik ma do niej odpowiednie prawa dostępu
        $check = pg_query_params($conn, 'SELECT lesson.id FROM lesson INNER JOIN flashcard ON lesson.id = flashcard.lesson_fk WHERE lesson.user_fk = $1 AND flashcard.id = $2',
            array($requestingUserLogin, $entryId));
        if ($check === false) {
            throw new Exception("Unexpected db error");
        }
        if (pg_num_rows($check) == 0) {
            return false;
        }
        $lessonId = pg_fetch_all($check)[0]['id'];
        $result = pg_query_params($conn, 'DELETE FROM flashcard WHERE id = $1',
            array($entryId));
        if ($result === false || pg_affected_rows($result) != 1) {
            throw new Exception("Unexpected db error");
        }
        // uaktualnienie czasu modyfikacji lekcji
        $res = pg_query_params($conn, 'UPDATE lesson SET modification_timestamp = CURRENT_TIMESTAMP WHERE id = $1;', array($lessonId));
        if ($res === false) {
            throw new Exception("Unexpected db error");
        }
        return true;
    }
    
    /**
     * Usuwa lekcję (i automatycznie powiązane z nią flashcardy).
     * 
     * TODO do poprawienia.
     * 
     * @param {String} $lesson ID lekcji.
     * @return true W przypadku gdy operacja zakończy sie sukcesem.
     *         false w przeciwnym razie.
     */
    function deleteLesson($lesson) {
        trigger_error("deleteLesson() is deprecated. Use removeLesson() instead.", E_USER_NOTICE);
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
        $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
        $result = pg_query_params($conn, 'DELETE FROM lesson WHERE id = $1', array($lesson));
        return $result !== false;
    }

    /**
     * Usuwa lekcję o zadanym id.
     * 
     * @param {String} $requestingUserLogin Login użytkownika wykonującego zapytanie
     * @param {Integer} $lessonId Id lekcji, którą chcemy usunąć
     * @return true W przypadku gdy operacja zakończy sie sukcesem.
     *         false w przeciwnym razie.
     * @throws Exception("Unexpected db error")
     *         W przypadku niespodziewanego błędu bazy danych funkcja rzuca wyjątkiem
     */
    function removeLesson($requestingUserLogin, $lessonId) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
        $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
        $result = pg_query_params($conn, 'DELETE FROM lesson WHERE id = $1 AND user_fk = $2', array($lessonId, $requestingUserLogin));
        if ($result === false) {
            throw new Exception("Unexpected db error");
        }
        return pg_affected_rows($result) == 1;
    }

    /**
     * Zaczyna naukę wskazanej lekcji.
     * 
     * @param {String} $requestingUserLogin Login użytkownika wykonującego zapytanie
     * @param {Integer} $lessonId Id lekcji, której naukę chcemy rozpocząć
     * @param {Integer} $batchSize Wielkość partii lekcji przy ćwiczeniu
     * @param {integer} $trainingRepetitions Liczba powtórzeń ćwiczenia
     * @param {String} $name Nazwa lekcji
     * @return {Object} Utworzony wpis nauki, czyli obiekt zawierający atrybuty
     *          id - identyfikator nauki
     *          name - nazwa nauki
     *          batchSize - wielkość partii przy nauce
     *          trainingRepetitions - liczba powtórzeń nauki
     *          lesson_fk - lekcje do której odnosi się nauka
     * @throws Exception("Unexpected db error")
     *         W przypadku niespodziewanego błędu bazy danych funkcja rzuca wyjątkiem
     * 
     */
    function startTraining($requestingUserLogin, $lessonId, $batchSize, $trainingRepetitions, $name) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
        $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
        // sprawdzenie czy zadana lekcja istnieje
        // i czy użytkownik ma do niej odpowiednie prawa dostępu
        $check = pg_query_params($conn, 'SELECT id FROM lesson WHERE lesson.user_fk = $1 AND lesson.id = $2',
            array($requestingUserLogin, $lessonId));
        if ($check === false) {
            throw new Exception("Unexpected db error");
        }
        if (pg_num_rows($check) == 0) {
            // sprawdzenie czy użytkownik ma dostęp przez udostępnienie grupy
            $check = pg_query_params($conn, 'SELECT * FROM lesson l INNER JOIN groupslessons gl ON l.id = gl.lesson_fk INNER JOIN groupmembers g ON gl.group_fk = g.group_fk WHERE g.member_fk = $1 AND l.id = $2;',
                array($requestingUserLogin, $lessonId));
            if ($check === false) {
                throw new Exception("Unexpected db error");
            }
            if (pg_num_rows($check) == 0) {
                // sprawdzenie czy użytkownik ma dostęp przez udostępnienie grupy
                return false;
            }
        }
        $result = pg_query_params($conn, 'INSERT INTO training(batchSize, trainingRepetitions, name, lesson_fk, user_fk) VALUES ($1, $2, $3, $4, $5) RETURNING id',
            array($batchSize, $trainingRepetitions, $name, $lessonId, $requestingUserLogin));
        if ($result === false || pg_num_rows($result) != 1) {
            throw new Exception("Unexpected db error");
        }
        $createdTrainingId = pg_fetch_row($result)[0];
        // inicjalizacja danych nauki
        $res = pg_query_params($conn, 'INSERT INTO Progress (value, lessonEntry_fk, training_fk) SELECT 0, f.id, $1 FROM flashcard f LEFT JOIN progress p ON f.id = p.lessonEntry_fk AND training_fk = $1 WHERE f.lesson_fk = $2 AND p.value IS NULL;', array($createdTrainingId, $lessonId));
        if ($res === false) {
            throw new Exception("Unexpected db error");
        }
        return $createdTrainingId;
    }

    /**
     * Usuwa Trening
     *
     * @param {String} $trainingID ID lekcji.
     * @throws Exception("Unexpected db error")
     *         W przypadku niespodziewanego błędu bazy danych funkcja rzuca wyjątkiem
     */
    function deleteTraining($trainingID) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
        $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
        $result = pg_query_params($conn, 'DELETE FROM training WHERE id = $1',
            array($trainingID));
        if ($result === false) {
            throw new Exception("Unexpected db error");
        }
    }

    /**
     * Zaczyna naukę wskazanej lekcji.
     *
     * @param {String} $requestingUserLogin Login użytkownika wykonującego zapytanie
     * @param {Integer} $lessonId Id lekcji, której naukę chcemy edytować
     * @param {Integer} $batchSize Wielkość partii lekcji przy ćwiczeniu
     * @param {integer} $trainingRepetitions Liczba powtórzeń ćwiczenia
     * @param {String} $name Nazwa lekcji
     * @return {Object} Utworzony wpis nauki, czyli obiekt zawierający atrybuty
     *          id - identyfikator nauki
     *          name - nazwa nauki
     *          batchSize - wielkość partii przy nauce
     *          trainingRepetitions - liczba powtórzeń nauki
     *          lesson_fk - lekcje do której odnosi się nauka
     * @throws Exception("Unexpected db error")
     *         W przypadku niespodziewanego błędu bazy danych funkcja rzuca wyjątkiem
     *
     */
    function editTraining($oldTrainingID, $requestingUserLogin, $lessonId,
                          $batchSize, $trainingRepetitions, $name) {
        deleteTraining($oldTrainingID);
        return startTraining($requestingUserLogin, $lessonId, $batchSize,
            $trainingRepetitions, $name);
    }

    /**
     * Pobiera listę nauk użytkownika, opcjonalnie ograniczając liczbę zwróconych wpisów.
     * 
     * @param {String} $requestingUserLogin Login użytkownika wykonującego zapytanie
     * @param {Integer} $limit Ograniczenie liczby, zwróconych wpisów
     *                         Wartość -1 oznacza brak ograniczenia
     * @return {Object[]} Tablica obiektów nauk użytkownika postaci
     *                    {id:[id nauki], name:[nazwa nauki], batchsize:[wielkość partii treningowej],
     *                        trainingrepetitions:[liczba powtórzeń], lessonid:[id bazowej lekcji]}
     * @throws Exception("Unexpected db error")
     *         W przypadku niespodziewanego błędu bazy danych funkcja rzuca wyjątkiem
     */
    function getTrainings($requestingUserLogin, $limit) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
        $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");

        if ($limit == -1) {
            $result = pg_query_params($conn, 'SELECT id, name, batchSize, trainingRepetitions, lesson_fk lessonId FROM training WHERE user_fk = $1', 
                array($requestingUserLogin));
        }
        else {
            $result = pg_query_params($conn, 'SELECT id, name, batchSize, trainingRepetitions, lesson_fk lessonId FROM training WHERE user_fk = $1 LIMIT $2',
                array($requestingUserLogin, $limit));
        }
        $rows = pg_fetch_all($result);
        return $rows === false ? array() : $rows;
    }

/**
 * Pobiera nauke o podanym id.
 *
 * @param {String} $requestingUserLogin
 * @param {Integer} $trainingId
 * @return object Obiekt postaci
 *              {id:[id nauki], name:[nazwa nauki], batchsize:[wielkość partii treningowej],
 *               trainingrepetitions:[liczba powtórzeń], lessonid:[id bazowej lekcji]}
 * @throws Exception ("Unexpected db error")
 *         W przypadku niespodziewanego błędu bazy danych funkcja rzuca wyjątkiem
 */
    function getTraining($requestingUserLogin, $trainingId) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
        $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
        $result = pg_query_params($conn, 'SELECT id, name, batchsize, trainingrepetitions, lesson_fk lessonid FROM training WHERE user_fk = $1 AND id = $2',
            array($requestingUserLogin, $trainingId));
        if ($result === false || pg_num_rows($result) != 1) {
            throw new Exception("Unexpected db error");
        }
        return pg_fetch_object($result);
    }

    /**
     * Pobiera następną partię treningową ze wskazanej nauki.
     * 
     * @param {String} $requestingUserLogin Login użytkownika wykonującego zapytanie
     * @param {Integer} $trainingId Id treningu, dla którego partię treningową chcemy pobrać
     * @return {{modified:Boolean, rows:Object[]}} Tabelkę zawierającą dwa pola.
     *              modified:Boolean flaga określająca czy nauka została zmodyfikowana od
     *                  czasu pobrania poprzedniej partii treningowej
     *              rows:Object[] listę wpisów do nauki postaci
     *                  {id:[id wpisu lekcji], question:[pytanie], answer:[odpowiedź]}
     *              w przypadku sukcesu
     *         false wpp.
     * @throws Exception("Unexpected db error")
     *         W przypadku niespodziewanego błędu bazy danych funkcja rzuca wyjątkiem
     */
    function getNextTrainingBatch($requestingUserLogin, $trainingId) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
        $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");

        $info = pg_query_params($conn, 'SELECT batchSize, trainingRepetitions, lesson_fk, last_seen_timestamp, modification_timestamp FROM training t LEFT JOIN lesson l ON t.lesson_fk = l.id WHERE t.id = $1 AND t.user_fk = $2;', array($trainingId, $requestingUserLogin));
        if ($info === false) {
            throw new Exception("Unexpected db error");
        }
        if (pg_num_rows($info) != 1) {
            return -1;
        }
        $row = pg_fetch_all($info)[0];
        $batchSize = $row["batchsize"];
        $trainingRepetitions = $row["trainingrepetitions"];
        $last_seen_timestamp = $row["last_seen_timestamp"];
        $modification_timestamp = $row["modification_timestamp"];
        $lesson_fk = $row["lesson_fk"];
        if ($lesson_fk === null) return -2;
        $modified = $modification_timestamp > $last_seen_timestamp;
        if ($modified) {
            // uaktualnienie danych nauki
            $res = pg_query_params($conn, 'INSERT INTO Progress (value, lessonEntry_fk, training_fk) SELECT 0, f.id, $1 FROM flashcard f LEFT JOIN progress p ON f.id = p.lessonEntry_fk AND training_fk = $1 WHERE f.lesson_fk = $2 AND p.value IS NULL;', array($trainingId, $lesson_fk));
            if ($res === false) {
                throw new Exception("Unexpected db error");
            }
            $res = pg_query_params($conn, 'UPDATE training SET update_timestamp = CURRENT_TIMESTAMP WHERE id = $1;', array($trainingId));
            if ($res === false) {
                throw new Exception("Unexpected db error");
            }
        }
        $result = pg_query_params($conn, 'SELECT f.* FROM flashcard f INNER JOIN progress p ON f.id = p.lessonEntry_fk AND training_fk = $1 WHERE f.lesson_fk = $2 AND p.value < $3 ORDER BY p.lessonEntry_fk ASC LIMIT $4;', array($trainingId, $lesson_fk, $trainingRepetitions, $batchSize));
        $rows = pg_fetch_all($result);
        return array("modified" => $modified, "rows" => ($rows === false ? array() : $rows));
    }

    /**
     * Zapisuje wynik ćwiczenia lekcji.
     * 
     * Zwiększa o 1 poziom wszystkich słów, na które użytkownik odpowiedział dobrze
     * i zmniejsza o 1 poziom wszystkich słów, na które użytkownik odpowiedział źle
     * 
     * @param {String} $requestingUserLogin Login użytkownika wykonującego zapytanie
     * @param {Integer} $trainingId Id treningu, którego dotyczy operacja
     * @param {Integer[]} $correctAnswers id wpisów, na które użytkownik odpowiedział dobrze
     * @param {Integer[]} $wrongAnswers id wpisów, na które użytkownik odpowiedział błędnie
     * @return -1 w przypadku gdy użytkownik nie ma dostępu do zadanego treningu
     *         -2 w przypadku gdy lekcja bazowa została zmodyfikowana od czasu porania ostatniej partii treningowej
     *         -3 w przypadku gdy któreś z id dotyczy niepoprawnego wpisu
     *         true w przypadku sukcesu
     * @throws Exception("Unexpected db error")
     *         W przypadku niespodziewanego błędu bazy danych funkcja rzuca wyjątkiem
     */
    function submitTrainingResult($requestingUserLogin, $trainingId, $correctAnswers, $wrongAnswers) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
        $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
        // sprawdzenie czy trening należy do użytkownika
        $check = pg_query_params("SELECT * FROM training WHERE id = $1 AND user_fk = $2", array($trainingId, $requestingUserLogin));
        if ($check === false || pg_affected_rows($check) != 1) {
            return -1;
        } 
        // sprawdzenie czy lekcja bazowa nie została zmodyfikowana od czasu ostatniego pobrania partii treningowej
        $check = pg_query_params("SELECT * FROM training t INNER JOIN lesson l ON t.lesson_fk = l.id WHERE t.id = $1 AND t.update_timestamp >= l.modification_timestamp", array($trainingId));
        if ($check === false || pg_affected_rows($check) != 1) {
            return -2;
        } 
        // sprawdzenie czy zadane id są poprawne (czyli czy istnieją i są w trakcie nauki)
        $query = "SELECT COUNT(*) FROM progress WHERE training_fk = $1 AND value < (SELECT trainingRepetitions FROM training WHERE id = $1) AND (lessonEntry_fk IN (";
        foreach ($correctAnswers as $id) {
            $query .= "$id, ";
        }
        $query .= "NULL) OR lessonEntry_fk IN (";
        foreach ($wrongAnswers as $id) {
            $query .= "$id, ";
        }
        $query .= "NULL));";
        $affectedRows = pg_query_params($conn, $query, array($trainingId));
        if ($affectedRows === false) {
            throw new Exception("Unexpected db error");
        }
        if (intval(pg_fetch_row($affectedRows)[0]) != count($correctAnswers) + count($wrongAnswers)) {
            return -3;
        }
        pg_query("BEGIN TRANSACTION;");
        // zwiększenie poziomu słów na które użytkownik odpowiedział dobrze
        $query2 = "UPDATE progress SET value = value + 1 WHERE training_fk = $1 AND lessonEntry_fk IN (";
        foreach ($correctAnswers as $id) {
            $query2 .= "$id, ";
        }
        $query2 .= "NULL);";
        $res = pg_query_params($conn, $query2, array($trainingId));
        if ($res === false) {
            pg_query("ROLLBACK;");
            throw new Exception("Unexpected db error");
        }
        // zmniejszenie poziomu słów na które użytkownik odpowiedział źle
        $query2 = "UPDATE progress SET value = value - 1 WHERE training_fk = $1 AND value > 0 AND lessonEntry_fk IN (";
        foreach ($wrongAnswers as $id) {
            $query2 .= "$id, ";
        }
        $query2 .= "NULL);";
        $res = pg_query_params($conn, $query2, array($trainingId));
        if ($res === false) {
            pg_query("ROLLBACK;");
            throw new Exception("Unexpected db error");
        }
        pg_query("COMMIT;");
        return true;
    }

    /**
     * Kończy zadany trening.
     * 
     * @param {String} $requestingUserLogin Login użytkownika wykonującego zapytanie
     * @param {Integer} $trainingId Id treningu, którego dotyczy operacja
     * @return true w przypadku, gdy usunięcie nauki zakończyło się sukcesem
     *         false wpp.
     * @throws Exception("Unexpected db error")
     *         W przypadku niespodziewanego błędu bazy danych funkcja rzuca wyjątkiem
     * 
     */
    function endTraining($requestingUserLogin, $trainingId) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
        $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
        $result = pg_query_params($conn, 'DELETE FROM training WHERE id = $1 AND user_fk = $2', array($trainingId, $requestingUserLogin));
        if ($result === false) {
            throw new Exception("Unexpected db error");
        }
        return pg_affected_rows($result) == 1;
    }
?>
