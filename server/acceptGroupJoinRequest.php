<?php

require 'sessionManagement.php';
require 'namingConstraints.php';
require "resultHelpers.php";
require 'groupHelpers.php';

define("SUCCESS", 0);
define("NO_LOGGED_USER", 1);
define("MISSING_PARAM", 2);
define("INVALID_JOIN_REQUEST_ID_PARAM", 3); // joinRequestId nie jest liczbą
define("REQUEST_DOES_NOT_EXIST_OR_PERMISSION_DENIED", 4);
define("UNEXPECTED_DB_ERROR", 5);


function handleAcceptGroupJoinRequest() {
    if (!isLogged()) {
        return generateResult(NO_LOGGED_USER);
    }
    if (!isset($_POST['joinRequestId'])) {
        return generateResult(MISSING_PARAM);
    }
    $joinRequestId = $_POST['joinRequestId'];
    if (!isRequestId($joinRequestId)) {
        return generateResult(INVALID_JOIN_REQUEST_ID_PARAM);
    }
    $login = getLogin();
    $result = acceptJoinRequest($login, $joinRequestId);
    if ($result == 0) {
        return generateResult(SUCCESS);
    }
    if ($result == 4) {
        return generateResult(REQUEST_DOES_NOT_EXIST_OR_PERMISSION_DENIED);
    }
    if ($result == 5) {
        return generateResult(UNEXPECTED_DB_ERROR);
    }
}

session_start();
echo handleAcceptGroupJoinRequest();
?>