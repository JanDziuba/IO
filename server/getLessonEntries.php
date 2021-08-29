<?php
    require "sessionManagement.php";
    require "lessonsHelpers.php";
    require "resultHelpers.php";

    define("SUCCESS", 0);
    define("NO_LOGGED_USER", 1);
    define("MISSING_REQUEST_PARAM", 2);
    define("LESSON_ID_PARAM_NOT_NUMERIC", 3);
    define("USER_MISSING_PRIVILEGES", 4);
    define("UNEXPECTED_DB_ERROR", 5);

    function handleLessonEntriesListingRequest() {
        // Sprawdzamy czy użytkownik jest aktualnie zalogowany
        if (!isLogged()) {
            return generateResult(NO_LOGGED_USER);
        }
        if (!isset($_GET["lessonId"])) {
            return generateResult(MISSING_REQUEST_PARAM);
        }
        if (!is_numeric($_GET["lessonId"])) {
            return generateResult(LESSON_ID_PARAM_NOT_NUMERIC);
        }
        $lessonId = intval($_GET["lessonId"]);
        $login = getLogin();

        try {
            $entries = getLessonEntries($login, $lessonId);
            if ($entries !== false) return generateResult(SUCCESS, array("data" => $entries));
            else return generateResult(USER_MISSING_PRIVILEGES);
        } catch (Exception $exception) {
            return generateResult(UNEXPECTED_DB_ERROR);
        }
    }

    session_start();
    echo handleLessonEntriesListingRequest();
?>
