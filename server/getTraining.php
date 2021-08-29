<?php
require 'sessionManagement.php';
require 'namingConstraints.php';
require 'lessonsHelpers.php';
require "resultHelpers.php";

define("SUCCESS", 0);
define("NO_LOGGED_USER", 1);
define("INVALID_TRAINING_ID_PARAM", 2);
define("UNEXPECTED_DB_ERROR", 3);
define("INVALID_HTTP_METHOD", 4);

function handleGetTrainingRequest() {
  if ($_SERVER['REQUEST_METHOD'] != "GET") {
    return generateResult(INVALID_HTTP_METHOD);
  }
  // Sprawdzamy czy użytkownik jest zalogowany
  if (!isLogged()) {
    return generateResult(NO_LOGGED_USER);
  }

  if (!isset($_GET['trainingId']) || !is_numeric($_GET['trainingId'])) {
    return generateResult(INVALID_TRAINING_ID_PARAM);
  }

  $trainingId = $_GET['trainingId'];

  try {
    $training = getTraining(getLogin(), intval($trainingId));
    return generateResult(SUCCESS, array("data" => $training));
  } catch (Exception $exception) {
    return generateResult(UNEXPECTED_DB_ERROR);
  }
}

session_start();
echo handleGetTrainingRequest();
?>
