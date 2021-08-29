<?php
require 'server/lessonsHelpers.php';

if (!isset($_GET['trainingId']) || !is_numeric($_GET['trainingId'])) {
  header('Location: homepage.php');
}

deleteTraining($_GET['trainingId']);
header("Location: trainings.php?id=".$_GET['id']);
?>
