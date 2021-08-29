<?php
    require 'sessionHelpers.php';

    redirectNotLogged();

    $groupName = $_GET['name'];
    $groupId = $_GET['id'];
    $currenturl = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $urls = explode('/', $currenturl);
    array_pop($urls);
    $invurl = implode('/', $urls) . "/joinGroup.php?id=" . $groupId;

    require 'pageTemplate.php';

    $content = <<<ENT
    <div class="main-content-homepage">
        <div class="title" id="title" data-group-id=$groupId>Użytkownicy grupy $groupName</div>
        <div class="text">
            Zaproszenie do grupy:<br>
            $invurl
        </div>

        <div class="text">Członkowie grupy:</div>
        <div class="lessons" id="user-list">
        </div>

        <div class="text">Prośby o dołączenie:</div>
        <div class="lessons" id="invite-list">
        </div>
    </div>
    ENT;

    echo genPage('Grupa ' . $groupName, $content, $_SESSION['login'], ["scripts/groupUsers.js", "scripts/groupInvites.js"], ["style.css"]);
?>