<?php

require 'sessionManagement.php';
require 'namingConstraints.php';
require "resultHelpers.php";
require 'groupHelpers.php';

define("SUCCESS", 0);
define("NO_LOGGED_USER", 1);
define("MISSING_PARAM", 2);
define("INVALID_GROUP_ID_PARAM", 3);
define("INVALID_USER_ID_PARAM", 4);
define("NOONE_TO_KICK", 5); // nie ma takiego usera, abo nie mamy uprawnień, albo nie należy do grupy
define("UNEXPECTED_DB_ERROR", 6);


function kickGroupMember($admin, $userToKick, $groupId) {
    global $db_host, $db_port, $db_dbname, $db_user, $db_password;
    $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");

    $check = pg_query_params(
        $conn,
        'SELECT id FROM LearningGroup WHERE admin_fk=$1 AND id=$2',
        array($admin, $groupId)
    );
    if ($check === false) {
        return generateResult(UNEXPECTED_DB_ERROR);
    }
    if (pg_num_rows($check) == 0) {
        return generateReut(NOONE_TO_KICK); 
        // czyli kod RÓWNIEŻ znaczący, że nie jestesmy adminem grupy
        // (ale długa była by nazwa kodu błędu xd)
    }
    $result = pg_query_params(
        $conn,
        'DELETE FROM GroupMembers WHERE member_fk=$1 AND group_fk=$2',
        array($userToKick, $groupId)
    );
    if ($result === false) {
        return generateResult(UNEXPECTED_DB_ERROR);
    }
    if (pg_affected_rows($result) == 0) {
        return generateResult(NOONE_TO_KICK);
    }
    return generateResult(SUCCESS);
}

function handleKickGroupMember() {
    if (!isLogged()) {
        return generateResult(NO_LOGGED_USER);
    }
    if (!isset($_POST['groupId']) || !isset($_POST['userId'])) {
        return generateResult(MISSING_PARAM);
    }
    $groupId = $_POST['groupId'];
    $userToKick = $_POST['userId'];
    $admin = getLogin();

    if (!isGroupId($groupId)) {
        return generateResult(INVALID_GROUP_ID_PARAM);
    }
    if (!is_string($userToKick)) {
        return generateResult(INVALID_USER_ID_PARAM);
    }
    $result = kickGroupMember($admin, $userToKick, $groupId);
    return $result;
}
session_start();
echo handleKickGroupMember();
?>