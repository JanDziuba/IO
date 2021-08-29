<?php
    require "sessionManagement.php";
    require "groupHelpers.php";
    require "resultHelpers.php";
    require "namingConstraints.php";

    define("SUCCESS", 0);
    define("NO_LOGGED_USER", 1);
    define("MISSING_REQUEST_PARAM", 2);
    define("USER_MISSING_PRIVILEGES", 3);
    define("INVALID_GROUP_NAME", 4);
    define("INVALID_GROUP_DESCRIPTION", 5);
    define("UNEXPECTED_DB_ERROR", 7);

    function handleGroupUpdateRequest() {
        if (!isLogged()) {
            return generateResult(NO_LOGGED_USER);
        }
        if (!isset($_POST['newName']) && ! isset($_POST['newDescription'])) { 
            return generateResult(MISSING_REQUEST_PARAM);
        }
        $newName = null;
        $newDesc = null;
        if (isset($_POST['newName'])) {
            $newName = $_POST['newName'];
            if (!isValidGroupName($_POST['newName'])) {
                return generateResult(INVALID_GROUP_NAME);
            }
        }
        if (isset($_POST['newDescription'])) {
            $newDesc = $_POST['newDescription'];
            if (!isValidGroupDescription($_POST['newDescription'])) {
                return generateResult(INVALID_GROUP_DESCRIPTION);
            }
        }
        $login = getLogin();
        $groupId = $_POST['groupId'];

        $result = updateGroup($groupId, $login, $newName, $newDesc);
        if ($result == SUCCESS) {
            return generateResult(SUCCESS);
        }
        if ($result == UNEXPECTED_DB_ERROR) {
            return generateResult(UNEXPECTED_DB_ERROR);
        }
        if ($result == USER_MISSING_PRIVILEGES) {
            return generateResult(USER_MISSING_PRIVILEGES);
        }
    }
    session_start();
    echo handleGroupUpdateRequest();
?>