<?php
    /**
     * Sprawdza poprawność zadanego loginu.
     * 
     * @param {String} $login Login, którego poprawność jest sprawdzana.
     * @return true jeżeli login jest poprawny
     *         false jeżeli login jest niepoprawny
     */
    function isValidLogin($login) {
        return strlen($login) >= 3 && strlen($login) <= 32 && ctype_alnum($login);
    }

    /**
     * Sprawdza poprawność zadanego hasła.
     * 
     * @param {String} $password Hasło, którego poprawność jest sprawdzana.
     * @return true jeżeli hasło jest poprawne
     *         false jeżeli hasło jest niepoprawne
     */
    function isValidPassword($password) {
        return strlen($password) >= 8 && strlen($password) <= 64 && ctype_alnum($password);
    }

    /**
     * Sprawdza poprawność zadanej nazwy grupy.
     * 
     * @param {String} $name Nazwa grupy, której poprawność jest sprawdzana.
     * @return true jeżeli nazwa grupy jest poprawna
     *         false jeżeli nazwa grupy jest niepoprawna
     */
    function isValidGroupName($name) {
        return is_string($name) && strlen($name) > 0 && strlen($name) <= 30;
    }

    /**
     * Sprawdza poprawność zadanego opisu grupy.
     * 
     * @param {String} $description Opis grupy, którego poprawność jest sprawdzana.
     * @return true jeżeli opis grupy jest poprawny
     *         false jeżeli opis grupy jest niepoprawny 
     */
    function isValidGroupDescription($description) {
        return is_string($description) && strlen($description) <= 200;
    }

    function isValidLimit($limit) {
        return is_numeric($limit) && intval($limit) >= -1;
    }

    function isValidTextRequest($text) {
        return is_string($text) && strlen($text) <= 300;
    }

    function isRequestId($joinRequestId) {
        return is_numeric($joinRequestId) && intval($joinRequestId) >= 0;
    }

    function isGroupId($groupId) {
        return is_numeric($groupId) && intval($groupId) >= 0;
    }

    function isLessonId($lessonId) {
        return is_numeric($lessonId) && intval($lessonId) >= 0;
    }
    
    /**
     * Sprawdza poprawność zadanej nazwy lekcji.
     * 
     * @param {String} $name Nazwa lekcji, której poprawność jest sprawdzana.
     * @return true jeżeli nazwa lekcji jest poprawna
     *         false jeżeli nazwa lekcji jest niepoprawna
     */
    function isValidLessonName($name) {
        // TODO
        // najlepiej sprawdzenia pewnie zrobić za pomocą wyrażeń regularnych
        return true;
    }

    /**
     * Sprawdza poprawność zadanego opisu lekcji.
     * 
     * @param {String} $description Opis lekcji, którego poprawność jest sprawdzana.
     * @return true jeżeli opis lekcji jest poprawny
     *         false jeżeli opis lekcji jest niepoprawny 
     */
    function isValidLessonDescription($description) {
        // TODO
        // najlepiej sprawdzenia pewnie zrobić za pomocą wyrażeń regularnych
        return true;
    }

    /**
     * Sprawdza poprawność zadanego pytania (wpisu lekcji).
     * 
     * @param {String} $question Pytanie, którego poprawność jest sprawdzana.
     * @return true jeżeli pytanie jest poprawne
     *         false wpp.
     */
    function isValidQuestion($question) {
        return is_string($question) && strlen($question) <= 300;
    }

    /**
     * Sprawdza poprawność zadanej odpowiedzi (wpisu lekcji).
     * 
     * @param {String} $answer Odpowiedź, której poprawność jest sprawdzana.
     * @return true jeżeli odpowiedź jest poprawna
     *         false wpp.
     */
    function isValidAnswer($answer) {
        return is_string($answer) && strlen($answer) <= 300;
    }

    /**
     * Sprawdza poprawność zadanej nazwy treningu.
     * 
     * @param {String} $trainingName Nazwa, której poprawność jest sprawdzana.
     * @return true jeżeli nazwa jest poprawna
     *         false wpp.
     */
    function isValidTrainingName($trainingName) {
        return is_string($trainingName) && strlen($trainingName) <= 64;
    }

    /**
     * Sprawdza poprawność zadanej wielkości partii ćwiczeniowej.
     * 
     * @param {String} $batchSize Wielkość partii ćwiczeniowej, której poprawność jest sprawdzana.
     * @return true jeżeli wielkość jest poprawna
     *         false wpp.
     */
    function isValidTrainingBatchSize($batchSize) {
        return is_numeric($batchSize) && intval($batchSize) > 0;
    }

    /**
     * Sprawdza poprawność zadanej liczby powtórzeń treningu.
     * 
     * @param {String} $trainingRepetitions Liczba powtórzeń treningu.
     * @return true jeżeli wielkość jest poprawna
     *         false wpp.
     */
    function isValidTrainingRepetitions($trainingRepetitions) {
        return is_numeric($trainingRepetitions) && intval($trainingRepetitions) > 0;
    }

    /**
     * Sprawdza poprawność zdanego limitu liczby wypisywanych nauk.
     * 
     * @param {String} $limit Ograniczenie liczby, nauk, którego poprawność jest sprawdzana
     * @return true jeżeli wartość jest poprawna
     *         false wpp.
     */
    function isValidTrainingsListLimit($limit) {
        return is_numeric($limit) && (intval($limit) == -1 || intval($limit) > 0); 
    }
?>
