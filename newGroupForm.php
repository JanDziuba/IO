<?php
require 'sessionHelpers.php';

redirectNotLogged();

require 'pageTemplate.php';

$content = <<<ENT
    <div class = new-group>
    <div class="title">Utwórz grupę</div>
        <form action="server/createGroup.php" method="post" id="newGroupForm">
            <div>Nazwa grupy: </div>
            <input type="text" name="name" required><br> 
            <div>Opis grupy (opcjonalny):</div>
            <textarea name="description"></textarea><br>
            <div><input type="submit" value="Utwórz grupę"></div>
        </form>
    <div>
    ENT;

echo genPage("Nowa grupa", $content, $_SESSION['login'], ["scripts/newGroup.js"], ["style.css"]);
?>