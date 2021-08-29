<?php
require 'sessionManagement.php';
require 'namingConstraints.php';
require "resultHelpers.php";
require 'groupHelpers.php';

define("SUCCESS", 0);
define("NO_LOGGED_USER", 1);
define("MISSING_PARAM", 2);
define("GROUP_DOES_NOT_EXIST", 3); //żądana grupa nie istnieje
define("INVALID_REQUEST_TEXT_PARAM", 4);
define("USER_ALREADY_REQUESTED_JOINING_GROUP", 5); // spr. w bazie danych
define("USER_BANNED", 6); // ?
define("UNEXPECTED_DB_ERROR", 7);
define("USER_ALREADY_IN_GROUP", 8);

function handleGroupJoinRequest() {
    if (!isLogged()) {
        return generateResult(NO_LOGGED_USER);
    }
    if (!isset($_POST['groupId'])) {
        return generateResult(MISSING_PARAM);
    }
    $groupId = $_POST['groupId'];
    $request_text = isset($_POST['requestText']) ? $_POST['requestText'] : '';
    if (!isValidTextRequest($request_text)) {
        return generateResult(INVALID_REQUEST_TEXT_PARAM);
    }
    $login = getLogin();
    $result = sendJoinRequest($login, $groupId, $request_text);
    if ($result == 0) {
        return generateResult(SUCCESS);
    }
    if ($result == 3) {
        return generateResult(GROUP_DOES_NOT_EXIST);
    }
    if ($result == 5) {
        return generateResult(USER_ALREADY_REQUESTED_JOINING_GROUP);
    }
    if ($result == 7) {
        return generateResult(UNEXPECTED_DB_ERROR);
    }
    if ($result == 8) {
        return generateResult(USER_ALREADY_IN_GROUP);
    }
}

session_start();
echo handleGroupJoinRequest();
?>