<?php
require 'sessionManagement.php';
require 'namingConstraints.php';
require 'lessonsHelpers.php';
require "resultHelpers.php";

define("SUCCESS", 0);
define("NO_LOGGED_USER", 1);
define("MISSING_REQUEST_PARAM", 2);
define("USER_MISSING_PRIVILEGES", 3);
define("INVALID_BATCH_SIZE_PARAM", 4);
define("INVALID_TRAINING_REPETITIONS_PARAM", 5);
define("INVALID_NAME_PARAM", 6);
define("UNEXPECTED_DB_ERROR", 7);

function handleEditTrainingRequest() {
  // Sprawdzamy czy użytkownik jest zalogowany
  if (!isLogged()) {
    return generateResult(NO_LOGGED_USER);
  }
  // Sprawdzamy czy dostaliśmy poprawnego requesta
  if (!isset($_POST['batchSize']) || !isset($_POST['trainingRepetitions']) ||
      !isset($_POST['lessonId']) || !isset($_POST['trainingId'])) {
    return generateResult(MISSING_REQUEST_PARAM);
  }

  $lessonId = $_POST['lessonId'];
  $trainingId = $_POST['trainingId'];
  $batchSize = $_POST['batchSize'];
  $trainingRepetitions = $_POST['trainingRepetitions'];
  $name = $_POST['name'] ?? '';

  if (!isValidTrainingName($name)) {
    return generateResult(INVALID_NAME_PARAM);
  }

  if (!isValidTrainingBatchSize($batchSize)) {
    return generateResult(INVALID_BATCH_SIZE_PARAM);
  }

  if (!isValidTrainingRepetitions($trainingRepetitions)) {
    return generateResult(INVALID_TRAINING_REPETITIONS_PARAM);
  }

  try {
    $createdTraining = editTraining($trainingId, getLogin(), $lessonId, intval($batchSize), intval($trainingRepetitions), $name);
    if ($createdTraining === false) return generateResult(USER_MISSING_PRIVILEGES);
    return generateResult(SUCCESS, array("startedTrainingId" => intval($createdTraining)));
  } catch (Exception $exception) {
    return generateResult(UNEXPECTED_DB_ERROR);
  }
}

session_start();
echo handleEditTrainingRequest();
?>
