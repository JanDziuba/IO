<?php
    require 'sessionHelpers.php';

    redirectNotLogged();

    require 'pageTemplate.php';

    if (!isset($_GET['trainingId']) || !is_numeric($_GET['trainingId'])) {
        header('Location: homepage.php');
    }

    $content = '<div id="lesson-learn" class="lesson-learn" data-training-id='.$_GET['trainingId'].'></div>';
        
    echo genPage("Trening", $content, $_SESSION['login'], ["scripts/lessonTrainQA.js"], ["style.css", "trainingQA.css"]);
?>