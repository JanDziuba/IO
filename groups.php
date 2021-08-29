<?php
require 'sessionHelpers.php';

redirectNotLogged();

require 'pageTemplate.php';

$content = <<<ENT
    <div class="main-content">
        <div class="title">Moje Grupy</div>
        <button class="new-group-button" onclick="window.location='newGroupForm.php'">Nowa Grupa</button>

        <div class="text">Grupy w których jestem administratorem:</div>
        <div id="adminGroups" class="groups"></div>
        <div class="text">Grupy w których nie jestem administratorem:</div>
        <div id="nonAdminGroups" class="groups"></div>
    </div>
    ENT;

echo genPage('Grupy', $content, $_SESSION['login'], ['scripts/groups.js'], ["style.css"]);
?>