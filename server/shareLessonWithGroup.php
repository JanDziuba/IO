<?php
    require 'sessionManagement.php';
    require 'namingConstraints.php';
    require "resultHelpers.php";
    require 'groupHelpers.php';

    define("SUCCESS", 0); // udostępnienie lekcji zakończone sukcesem
    define("NO_LOGGED_USER", 1); // w danej sesji, nie ma zalogowanego użytkownika
    define("MISSING_PARAM", 2);// brak któregoś z wymaganych parametrów żądania
    define("INVALID_GROUP_ID_PARAM", 3); // parametr groupId nie jest liczbą
    define("INVALID_LESSON_ID_PARAM", 4); // parametr lessonId nie jest liczbą
    define("LESSON_DOES_NOT_EXISTS_OR_PERSMISSION_DENIED", 5); // zadana lekcja nie istnieje, lub
                                                               // użytkownik nie ma uprawnień aby udostępnić zadaną lekcję, lub
                                                               // użytkownik nie ma uprawnień by udostępnić lekcję w danej grupie, lub
                                                               // grupa nie istnieje
    define("UNEXPECTED_DB_ERROR", 6); // niespodziewany błąd przy próbie modyfikacji bazy danych



    function shareLessonWithGroup($login, $groupId, $lessonId) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
        $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
        
        $check1 = check_if_lesson_exists_and_has_permission($conn, $lessonId, $login);
        $check2 = check_if_group_exists_and_has_permission($conn, $login, $groupId);

        if($check1 == -1 || $check2 == -1) {
            return generateResult(UNEXPECTED_DB_ERROR);
        }
        if($check1 == 0 || $check2 == 0) {
            return generateResult(LESSON_DOES_NOT_EXISTS_OR_PERSMISSION_DENIED);
        }
    
        $result = pg_query_params(
            $conn,
            'INSERT INTO GroupsLessons VALUES ($1, $2)', 
            array($groupId, $lessonId)
        );

        if($result === false) {
            return generateResult(UNEXPECTED_DB_ERROR);
        }
        return generateResult(SUCCESS);
    }


    function handleShareLessonWithGroup() {
        if (!isLogged()) {
            return generateResult(NO_LOGGED_USER);
        }
        if (!isset($_POST['groupId'])) {
            return generateResult(MISSING_PARAM);
        }
        if (!isset($_POST['lessonId'])) {
            return generateResult(MISSING_PARAM);
        }
        $groupId = $_POST['groupId'];
        $lessonId = $_POST['lessonId'];
        $login = getLogin();

        if (!isGroupId($groupId)) {
            return generateResult(INVALID_GROUP_ID_PARAM);
        }
        if (!isLessonId($lessonId)) {
            return generateResult(INVALID_LESSON_ID_PARAM);
        }

        return shareLessonWithGroup($login, $groupId, $lessonId);
    }

    session_start();
    echo handleShareLessonWithGroup();
?>