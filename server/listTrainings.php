<?php
    require 'sessionManagement.php';
    require 'namingConstraints.php';
    require 'lessonsHelpers.php';
    require "resultHelpers.php";

    define("SUCCESS", 0);
    define("NO_LOGGED_USER", 1);
    define("INVALID_LIMIT_PARAM", 2);
    define("UNEXPECTED_DB_ERROR", 3);
    define("INVALID_HTTP_METHOD", 4);

    function handleTrainingsListingRequest() {
        if ($_SERVER['REQUEST_METHOD'] != "GET") {
            return generateResult(INVALID_HTTP_METHOD);
        }
        // Sprawdzamy czy użytkownik jest zalogowany
        if (!isLogged()) {
            return generateResult(NO_LOGGED_USER);
        }
        $limit = isset($_GET['limit']) ? $_GET['limit'] : -1;

        if (!isValidTrainingsListLimit($limit)) {
            return generateResult(INVALID_LIMIT_PARAM);
        }

        try {
            $trainingsList = getTrainings(getLogin(), intval($limit));
            return generateResult(SUCCESS, array("data" => $trainingsList));
        } catch (Exception $exception) {
            return generateResult(UNEXPECTED_DB_ERROR);
        }
    }

    session_start();
    echo handleTrainingsListingRequest();
?>
