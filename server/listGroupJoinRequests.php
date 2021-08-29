<?php
    require "sessionManagement.php";
    require "namingConstraints.php";
    require "groupHelpers.php";

    define("SUCCESS", 0);
    define("NO_LOGGED_USER", 1);
    define("MISSING_PARAM", 2);
    define("INVALID_GROUP_ID_PARAM", 3); // groupId nie jest liczbą
    define("INVALID_LIMIT_PARAM", 4);
    define("REQUEST_DOES_NOT_EXIST_OR_PERMISSION_DENIED", 5);
    define("UNEXPECTED_DB_ERROR", 6);
    define("INCORRECT_HTTP_METHOD", 7);

    function handleListGroupJoinRequest() {
        if (!isLogged()) {
            return array("result" => NO_LOGGED_USER);
        }
        if($_SERVER['REQUEST_METHOD'] != 'GET') {
            return array("result" => INCORRECT_HTTP_METHOD);
        }
        if (!isset($_GET['groupId'])) {
            return array("result" => MISSING_PARAM);
        }
    
        $groupId = $_GET['groupId'];
        if(!isGroupId($groupId)) {
            return array("result" => INVALID_GROUP_ID_PARAM);
        }

        $limit = isset($_GET['limit']) ? $_GET['limit'] : -1;
        if(!isValidLimit($limit)){
            return array("result" => INVALID_LIMIT_PARAM);
        }
        $login = getLogin();

        $result = getGroupJoinRequestsList($login, $limit, $groupId);
        if($result == 5) {
            return array("result" => REQUEST_DOES_NOT_EXIST_OR_PERMISSION_DENIED);
        }
        if($result == 6) {
            return array("result" => UNEXPECTED_DB_ERROR);
        }
        if ($result === false) {
            return array("result" => SUCCESS, "data" => array());
        }
        return array("result" => SUCCESS, "data" => $result);
    }

    session_start();
    echo json_encode(handleListGroupJoinRequest());

?>