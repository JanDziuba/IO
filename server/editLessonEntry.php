<?php
    require "sessionManagement.php";
    require "lessonsHelpers.php";
    require "resultHelpers.php";
    require "namingConstraints.php";

    define("SUCCESS", 0);
    define("NO_LOGGED_USER", 1);
    define("MISSING_REQUEST_PARAM", 2);
    define("LESSON_ID_PARAM_NOT_NUMERIC", 3);
    define("INVALID_QUESTION_PARAM", 4);
    define("INVALID_ANSWER_PARAM", 5);
    define("USER_MISSING_PRIVILEGES", 6);
    define("UNEXPECTED_DB_ERROR", 7);

    function handleLessonEntryEditionRequest() {
        // Sprawdzamy czy użytkownik jest aktualnie zalogowany
        if (!isLogged()) {
            return generateResult(NO_LOGGED_USER);
        }
        if (!isset($_GET["entryId"]) || (!isset($_GET["newQuestion"]) && !isset($_GET["newAnswer"]))) {
            return generateResult(MISSING_REQUEST_PARAM);
        }
        if (!is_numeric($_GET["entryId"])) {
            return generateResult(LESSON_ID_PARAM_NOT_NUMERIC);
        }
        $login = getLogin();
        $entryId = intval($_GET["entryId"]);
        $newQuestion = isset($_GET["newQuestion"]) ? $_GET["newQuestion"] : false;
        $newAnswer = isset($_GET["newAnswer"]) ? $_GET["newAnswer"] : false;
        if ($newQuestion !== false && !isValidQuestion($newQuestion)) {
            return generateResult(INVALID_QUESTION_PARAM);
        }
        if ($newAnswer !== false && !isValidAnswer($newAnswer)) {
            return generateResult(INVALID_ANSWER_PARAM);
        }

        try {
            $success = editLessonEntry($login, $entryId, $newQuestion, $newAnswer);
            if ($success) {
                return generateResult(SUCCESS);
            } else {
                return generateResult(USER_MISSING_PRIVILEGES);
            }
        } catch (Exception $exception) {
            return generateResult(UNEXPECTED_DB_ERROR);
        }
    }

    session_start();
    echo handleLessonEntryEditionRequest();
?>
