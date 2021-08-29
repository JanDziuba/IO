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

    function handleLessonEntryAdditionRequest() {
        // Sprawdzamy czy użytkownik jest aktualnie zalogowany
        if (!isLogged()) {
            return generateResult(NO_LOGGED_USER);
        }
        if (!isset($_GET["lessonId"]) || !isset($_GET["question"]) || !isset($_GET["answer"])) {
            return generateResult(MISSING_REQUEST_PARAM);
        }
        if (!is_numeric($_GET["lessonId"])) {
            return generateResult(LESSON_ID_PARAM_NOT_NUMERIC);
        }
        $login = getLogin();
        $lessonId = intval($_GET["lessonId"]);
        $question = $_GET["question"];
        $answer = $_GET["answer"];
        if (!isValidQuestion($question)) {
            return generateResult(INVALID_QUESTION_PARAM);
        }
        if (!isValidAnswer($answer)) {
            return generateResult(INVALID_ANSWER_PARAM);
        }

        try {
            $createdEntryId = addLessonEntry($login, $lessonId, $question, $answer);
            if ($createdEntryId === false) return generateResult(USER_MISSING_PRIVILEGES);
            return generateResult(SUCCESS, array("createdEntryId" => $createdEntryId));
        } catch (Exception $exception) {
            return generateResult(UNEXPECTED_DB_ERROR);
        }
    }

    session_start();
    echo handleLessonEntryAdditionRequest();
?>
