<?php
require 'sessionManagement.php';
require 'namingConstraints.php';
require "resultHelpers.php";
require 'groupHelpers.php';

define("SUCCESS", 0);
define("NO_LOGGED_USER", 1);
define("MISSING_PARAM", 2);
define("INVALID_GROUP_ID_PARAM", 3);
define("UNKNOWN_GROUP", 4); // spr. w bd - grupa nie istnieje, albo użytkownik do niej nie należy
define("USER_DOES_NOT_EXIST", 5); // spr. w bazie danych
define("UNEXPECTED_DB_ERROR", 6);

function handleLeaveGroup() {
    if (!isLogged()) {
        return generateResul(NO_LOGGED_USER);
    }
    $user = getLogin();
    $groupId = $_POST['groupId'];
    if (!isset($groupId)) {
        return generateResult(MISSING_PARAM);
    }
    if (!is_numeric($groupId)) {
        return generateResult(INVALID_GROUP_ID_PARAM);
    }
    $result = leaveGroup($user, $groupId);
    return generateResult($result);
}
session_start();
echo handleLeaveGroup();
?>