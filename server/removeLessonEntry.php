<?php
    require "sessionManagement.php";
    require "lessonsHelpers.php";
    require "resultHelpers.php";
    require "namingConstraints.php";

    define("SUCCESS", 0);
    define("NO_LOGGED_USER", 1);
    define("MISSING_REQUEST_PARAM", 2);
    define("LESSON_ID_PARAM_NOT_NUMERIC", 3);
    define("USER_MISSING_PRIVILEGES", 4);
    define("UNEXPECTED_DB_ERROR", 5);

    function handleLessonEntryRemovalRequest() {
        // Sprawdzamy czy użytkownik jest aktualnie zalogowany
        if (!isLogged()) {
            return generateResult(NO_LOGGED_USER);
        }
        if (!isset($_GET["entryId"])) {
            return generateResult(MISSING_REQUEST_PARAM);
        }
        if (!is_numeric($_GET["entryId"])) {
            return generateResult(LESSON_ID_PARAM_NOT_NUMERIC);
        }
        $login = getLogin();
        $entryId = intval($_GET["entryId"]);

        try {
            $success = removeLessonEntry($login, $entryId);
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
    echo handleLessonEntryRemovalRequest();
?>
