<?php
    // Report all PHP errors
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    require 'sessionManagement.php';
    require 'namingConstraints.php';
    require 'lessonsHelpers.php';
    require "resultHelpers.php";

    define("SUCCESS", 0);
    define("NO_LOGGED_USER", 1);
    define("MISSING_REQUEST_PARAM", 2);
    define("INVALID_CORRECT_ANSWERS_PARAM", 3);
    define("INVALID_WRONG_ANSWERS_PARAM", 4);
    define("USER_MISSING_PRIVILEGES", 5);
    define("TRAINING_HAS_BEEN_MODIFIED_SINCE_LAST_SEEN", 6);
    define("INVALID_ENTRY_ID", 7);
    define("UNEXPECTED_DB_ERROR", 8);

    function handleTrainingResultSubmissionRequest() {
        // Sprawdzamy czy użytkownik jest zalogowany
        if (!isLogged()) {
            return generateResult(NO_LOGGED_USER);
        }
        // Sprawdzamy czy dostaliśmy poprawnego requesta
        if (!isset($_POST['trainingId']) || !isset($_POST['correctAnswers']) || !isset($_POST['wrongAnswers'])) {
            return generateResult(MISSING_REQUEST_PARAM);
        }
        
        $trainingId = $_POST['trainingId'];
        $correctAnswers = @json_decode($_POST['correctAnswers']);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return generateResult(INVALID_CORRECT_ANSWERS_PARAM);
        }
        foreach ($correctAnswers as $id) {
            if (!is_numeric($id)) {
                return generateResult(INVALID_CORRECT_ANSWERS_PARAM);
            }
        }
        $wrongAnswers = @json_decode($_POST['wrongAnswers']);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return generateResult(INVALID_WRONG_ANSWERS_PARAM);
        }
        foreach ($wrongAnswers as $id) {
            if (!is_numeric($id)) {
                return generateResult(INVALID_CORRECT_ANSWERS_PARAM);
            }
        }

        try {
            $result = submitTrainingResult(getLogin(), $trainingId, $correctAnswers, $wrongAnswers);
            if ($result === -1) return generateResult(USER_MISSING_PRIVILEGES);
            if ($result === -2) return generateResult(TRAINING_HAS_BEEN_MODIFIED_SINCE_LAST_SEEN);
            if ($result === -3) return generateResult(INVALID_ENTRY_ID);
            return generateResult(SUCCESS);
        } catch (Exception $exception) {
            return generateResult(UNEXPECTED_DB_ERROR);
        }
    }

    session_start();
    echo handleTrainingResultSubmissionRequest();
?>
