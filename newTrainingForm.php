<?php
    require 'sessionHelpers.php';

    redirectNotLogged();

    require 'pageTemplate.php';

    if (!isset($_GET['lessonId']) || !is_numeric($_GET['lessonId'])) {
        header('Location: homepage.php');
    }

    $lessonId = $_GET['lessonId'];
    $content = <<<ENT
    <div id="new-training">
        <div class="title">Utwórz trening</div>
        <form action="server/startTraining.php" method="post" id="newTrainingForm">
            <input type="hidden" name="lessonId" value=$lessonId>
            <div>Nazwa treningu: </div>
            <input type="text" name="name" required><br> 
            <div>Ile pytań w partii treningowej: </div>
            <input type="number" name="batchSize" required><br> 
            <div>Ile razy chcesz powtórzyć każde pytanie: </div>
            <input type="number" name="trainingRepetitions" required><br> 
            <div><input type="submit" value="Utwórz trening"></div>
        </form>
        <div id="errors"></div>
    <div>
    ENT;

    echo genPage("Nowy trening", $content, $_SESSION['login'], ["scripts/newTraining.js"], ["style.css"]);
?>