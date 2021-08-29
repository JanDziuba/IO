<?php
    require 'sessionManagement.php';
    require 'namingConstraints.php';
    require 'lessonsHelpers.php';
    require "resultHelpers.php";

    define("SUCCESS", 0);
    define("NO_LOGGED_USER", 1);
    define("MISSING_REQUEST_PARAM", 2);
    define("USER_MISSING_PRIVILEGES", 3);
    define("BASE_LESSON_HAS_BEEN_DELETED", 4);
    define("UNEXPECTED_DB_ERROR", 5);
    define("INVALID_HTTP_METHOD", 6);

    function handleTrainingBatchFetchingRequest() {
        if ($_SERVER['REQUEST_METHOD'] != "GET") {
            return generateResult(INVALID_HTTP_METHOD);
        }
        // Sprawdzamy czy użytkownik jest zalogowany
        if (!isLogged()) {
            return generateResult(NO_LOGGED_USER);
        }
        
        if (!isset($_GET['trainingId'])) {
            return generateResult(MISSING_REQUEST_PARAM);
        }

        $trainingId = $_GET['trainingId'];

        try {
            $result = getNextTrainingBatch(getLogin(), $trainingId);
            if ($result === -1) return generateResult(USER_MISSING_PRIVILEGES);
            if ($result === -2) return generateResult(BASE_LESSON_HAS_BEEN_DELETED);
            if ($result["modified"]) return generateResult(SUCCESS, array("data" => $result["rows"], "modifiedSinceLastSeen" => true));
            return generateResult(SUCCESS, array("data" => $result["rows"]));
        } catch (Exception $exception) {
            return generateResult(UNEXPECTED_DB_ERROR);
        }
    }

    session_start();
    echo handleTrainingBatchFetchingRequest();
?>
