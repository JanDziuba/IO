<?php
    require "sessionManagement.php";
    require "lessonsHelpers.php";

    function handleLessonsListingRequest() {
        // Sprawdzamy czy użytkownik jest aktualnie zalogowany
        if (!isLogged()) {
            return array("result" => 1);
        }
        // TODO
        // w przyszłości będzie można rozwinąć pobranie listy lekcji o jakieś filtry
        $lessonsNumLimit = 10;
        $login = getLogin();

        $result = getLessonsList($login, $lessonsNumLimit);
        return $result !== false ? array("result" => 0, "data" => $result) : array("result" => 2);
    }

    session_start();
    echo json_encode(handleLessonsListingRequest());

?>
