<?php
    require "sessionManagement.php";
    require "groupHelpers.php";
    require "resultHelpers.php";
    require "namingConstraints.php";

    define("SUCCESS", 0);
    define("NO_LOGGED_USER", 1);
    define("MISSING_REQUEST_PARAM", 2);
    define("USER_MISSING_PRIVILEGES", 3);
    define("UNEXPECTED_DB_ERROR", 4);

    function handleGroupRemovalRequest() {
        if (!isLogged()) {
            return generateResult(NO_LOGGED_USER);
        }
        if (!isset($_POST["groupId"])) {
            return generateResult(MISSING_REQUEST_PARAM);
        }
        if (!is_numeric($_POST["groupId"])) {
            return generateResult(MISSING_REQUEST_PARAM);
        }
        $login = getLogin();
        $groupId = intval($_POST["groupId"]);

        try {
            $success = removeGroup($login, $groupId);
            if ($success) {
                return generateResult(SUCCESS);
            } else {
                return generateResult(USER_MISSING_PRIVILEGES);
            }
        } catch (Exception $exception) {
            return generateResult(UNEXPECTED_DB_ERROR);
        }
    }

    session_start();
    echo handleGroupRemovalRequest();
?>