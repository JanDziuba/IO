<?php

    require 'sessionHelpers.php';

    redirectNotLogged();

    require 'pageTemplate.php';
    require 'server/groupHelpers.php';

    $result = sendJoinRequest(getLogin(), $_GET['id'], "");
    $message;
    if ($result == 0) $message = 'Prośba o dołączenie została wysłana';
    else if ($result == 3) $message = 'Grupa nie istnieje';
    else if ($result == 5) $message = 'Masz już aktywną prośbę dołączenia do żądanej grupy';
    else if ($result == 8) $message = 'Już należysz do tej grupy';
    else $message = 'Niespodziewany błąd';
    $content = <<<ENT
    <div class="main-content-homepage">
        <div class="title">$message</div>
        <button class="new-lesson-button" onclick="window.location='homepage.php'">Powrót do strony domowej</button>
    </div>
    ENT;

    echo genPage('Homepage', $content, $_SESSION['login'], [], ["style.css"]);
?>
