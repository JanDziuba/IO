<?php
    require 'sessionHelpers.php';

    redirectNotLogged();

    require 'pageTemplate.php';
    require 'server/lessonsHelpers.php';

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        echo genPage('404', "Nie ma takiej lekcji!", $_SESSION['login'], ["scripts/training.js"], ["style.css"]);
        exit();
    }

    $lessonId = $_GET['id'];

    $content = <<<ENT
    <div class="title">Moje treningi</div>
    <div id="main-content-trainings" data-lesson-id=$lessonId>
        <div class="title"Moje treningi</div>
        <button class="new-train-btn" onclick="window.location='newTrainingForm.php?lessonId=$lessonId'">Nowy trening</button><br>
        <div id="trainings" class="trainings"></div>
    </div>
    ENT;

    echo genPage('Moje treningi', $content, $_SESSION['login'], ["scripts/training.js"], ["style.css", "trainings.css"]);
?>