<?php
    require 'sessionHelpers.php';

    redirectNotLogged();

    require 'pageTemplate.php';
    // TODO
    // zrobić jakiś sensowny kod html z tego
    $content = <<<ENT
    <div class = new-lesson>
    <div class="title">Utwórz lekcję</div>
        <form action="server/createNewLesson.php" method="get" id="newLessonForm">
            <div>Nazwa lekcji: </div>
            <input type="text" name="lessonName" required><br> 
            <div>Opis lekcji (opcjonalny):</div>
            <textarea name="description"></textarea><br>
            <div><input type="submit" value="Utwórz lekcję"></div>
        </form>
        <div id="errors"></div>
    <div>
    ENT;

    echo genPage("Nowa lekcja", $content, $_SESSION['login'], ["scripts/newLesson.js"], ["style.css"]);
?>