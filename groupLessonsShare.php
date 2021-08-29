<?php
    require 'sessionHelpers.php';

    redirectNotLogged();

    $groupName = $_GET['name'];
    $groupId = $_GET['id'];

    require 'pageTemplate.php';
    $content = <<<ENT
    <div class="main-content-homepage">
        <div class="title" id="title" data-group-id=$groupId data-group-name=$groupName>
            Wybierz lekcje do udostępnienia
        </div>
        <form class="lessons to-share" id="lessons">
        </form>
    </div>
    ENT;

    echo genPage('Grupa ' . $groupName, $content, $_SESSION['login'], ["scripts/groupLessonsShare.js"], ["style.css"]);
?>