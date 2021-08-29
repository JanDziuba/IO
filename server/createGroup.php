<?php
require 'sessionManagement.php';
require 'namingConstraints.php';
require "resultHelpers.php";
require 'groupHelpers.php';

define("SUCCESS", 0);
define("NO_LOGGED_USER", 1);
define("MISSING_REQUEST_PARAM", 2);
define("INVALID_NAME_PARAM", 3);
define("INVALID_DESCRIPTION_PARAM", 4);
define("UNEXPECTED_DB_ERROR", 5);


function handleCreateGroupRequest() {
    if (!isLogged()) {
        return generateResult(NO_LOGGED_USER);
    }
    if (!isset($_POST['name'])) {
        return generateResult(MISSING_REQUEST_PARAM);
    }
    $name = $_POST['name'];
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    if (!isValidGroupName($name)) {
        return generateResult(INVALID_NAME_PARAM);
    }
    if (!isValidGroupDescription($description)) {
        return generateResult(INVALID_DESCRIPTION_PARAM);
    }
    try {
        $createdGroupID = createGroup(getLogin(), $name, $description);
        return generateResult(SUCCESS, array("createdGroupId" => $createdGroupID));
    } catch (Exception $exception) {
        return generateResult(UNEXPECTED_DB_ERROR);
    }
}

session_start();
echo handleCreateGroupRequest();
?>