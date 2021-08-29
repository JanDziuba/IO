<?php
    require 'sessionHelpers.php';

    redirectNotLogged();

    $groupName = $_GET['name'];
    $groupId = $_GET['id'];
    $isAdmin = $_GET['is-admin'];

    require 'pageTemplate.php';
    $content = <<<ENT
    <div class="main-content-homepage">
        <div class="title" id="title" data-group-id=$groupId data-is-admin=$isAdmin>Lekcje grupy $groupName</div>
    ENT;
    if ($isAdmin) {
        $content .= <<<ENT
        <form class="new-lesson-button" action="groupLessonsShare.php" method="get">
            <input type="hidden" name="id" value="{$groupId}"/>
            <input type="hidden" name="name" value="{$groupName}"/>
            <input type="submit" value="Udostępnij lekcję">
        </form>
        <form class="new-lesson-button" action="groupUsers.php" method="get">
            <input type="hidden" name="id" value="{$groupId}"/>
            <input type="hidden" name="name" value="{$groupName}"/>
            <input type="submit" value="Zarządzaj użytkownikami">
        </form>
        ENT;
    }
    $content .= <<<ENT
        <div class="lessons" id="lessons">
        </div>
    </div>
    ENT;

    echo genPage('Grupa ' . $groupName, $content, $_SESSION['login'], ["scripts/groupLessons.js"], ["style.css"]);
?>