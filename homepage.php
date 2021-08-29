<?php
    require 'sessionHelpers.php';

    redirectNotLogged();

    require 'pageTemplate.php';
    require 'server/lessonsHelpers.php';

    function createLessonList() {
        $lessons = getLessonsList(getLogin(), -1); // -1 = zwróć wszystkie lekcje
        $result = "";
        foreach ($lessons as $lesson) {
            $result .= <<<ENT
            <form class="lesson" action="lessonAction.php" method="get">
                <input type="hidden" name="id" value="{$lesson['id']}"/>
                <button class="content" name="submit" value="Trenuj"> {$lesson['name']} </button>
                <input class="edit" type="submit" name="submit" value="Edytuj">
                <input class="delete" type="submit" name="submit" value="Usuń">
            </form>
            ENT;
        }
        return $result;
    }
    
    $list = createLessonList();
    $content = <<<ENT
    <div class="main-content-homepage">
        <div class="title">Moje lekcje</div>
        <button class="new-lesson-button" onclick="window.location='newLessonForm.php'">Nowa lekcja</button>

        <div class="lessons">
            $list
        </div>
    </div>
    ENT;

    echo genPage('Homepage', $content, $_SESSION['login'], [], ["style.css"]);
?>