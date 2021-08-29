<?php
    require 'sessionHelpers.php';

    redirectNotLogged();

    require 'pageTemplate.php';

    // TODO: Zapytać bazę o dane lekcji i wygenerować z nich tabelkę


    $content = '<div id="edit-lesson" class="edit-lesson" data-lesson-id='.$_GET['id'].'>';
    $content .= <<<ENT
    <div class="title">Edytuj lekcję</div>
    <form action="" id="add-entry-form">
        <input type="text" id="add-entry-form-question" placeholder="Pytanie" required>
        <input type="text" id="add-entry-form-answer" placeholder="Odpowiedź" required>
        <input type="submit" id="add-entry-form-submit" value="Dodaj pytanie">
    </form>
        <table id="lesson-contents"> 
            <tr>
                <th>Pytanie</th>
                <th>Odpowiedź</th>
                <th>Akcje</th>
            </tr>
        </table>
    <div>
    ENT;

    echo genPage("Edycja lekcji", $content, $_SESSION['login'], ["scripts/editLesson.js"], ["style.css"]);
?>