<?php
    require 'sessionManagement.php';
    require 'namingConstraints.php';
    require 'lessonsHelpers.php';
    require "resultHelpers.php";

    define("SUCCESS", 0);
    define("NO_LOGGED_USER", 1);
    define("MISSING_REQUEST_PARAM", 2);
    define("INVALID_NAME_PARAM", 3);
    define("INVALID_DESCRIPTION_PARAM", 4);
    define("UNEXPECTED_DB_ERROR", 5);

    function handleCreateLessonRequest() {
        // Sprawdzamy czy użytkownik jest zalogowany
        if (!isLogged()) {
            return generateResult(NO_LOGGED_USER);
        }
        // Sprawdzamy czy dostaliśmy poprawnego requesta
        if (!isset($_GET['name'])) {
            return generateResult(MISSING_REQUEST_PARAM);
        }
        
        $name = $_GET['name'];
        $description = isset($_GET['description']) ? $_GET['description'] : '';

        if (!isValidLessonName($name)) {
            return generateResult(INVALID_NAME_PARAM);
        }

        if (!isValidLessonDescription($description)) {
            return generateResult(INVALID_DESCRIPTION_PARAM);
        }

        try {
            $createdLessonId = createLesson(getLogin(), $name, $description);
            return generateResult(SUCCESS, array("createdLessonId" => $createdLessonId));
        } catch (Exception $exception) {
            return generateResult(UNEXPECTED_DB_ERROR);
        }
    }

    session_start();
    echo handleCreateLessonRequest();
?>
