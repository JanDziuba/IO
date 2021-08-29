<?php
    require "sessionManagement.php";
    require "groupHelpers.php";
    require "namingConstraints.php";

    define("SUCCESS", 0);
    define("NO_LOGGED_USER", 1);
    define("INVALID_LIMIT_PARAM", 2);
    define("UNEXPECTED_DB_ERROR", 3);
    define("WRONG_HTTP_METHOD", 4);

    function listGroups($userLogin, $limit) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
        $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
        if ($limit == -1) {
            $result = pg_query_params(
                $conn, 
                'SELECT
                CASE
                    WHEN admin_fk = $1 THEN 1
                    ELSE 0
                END as role,    
                id, name, description 
                FROM LearningGroup LEFT JOIN GroupMembers ON id = group_fk
                WHERE admin_fk = $1 OR member_fk = $1',
                array($userLogin)
            );
        } else {
            $result = pg_query_params(
                $conn, 
                'SELECT
                CASE
                    WHEN admin_fk = $1 THEN 1
                    ELSE 0
                END as role,    
                id, name, description 
                FROM LearningGroup LEFT JOIN GroupMembers ON id = group_fk
                WHERE admin_fk = $1 OR member_fk = $1 LIMIT $2',
                array($userLogin, $limit)
            );
        }
        if ($result === false) {
            return array("result" => UNEXPECTED_DB_ERROR);
        }
        if(pg_fetch_all($result) === false) { // nie ma nic do wypisania
            return array("result" => SUCCESS, "data" => array());
        }
        return array("result" => SUCCESS, "data" => pg_fetch_all($result));
    }

    function handleGroupsListingRequest() {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            return array("result" => WRONG_HTTP_METHOD);
        }
        // Sprawdzamy czy użytkownik jest aktualnie zalogowany
        if (!isLogged()) {
            return array("result" => NO_LOGGED_USER);
        }
        $limit = isset($_GET['limit']) ? $_GET['limit'] : -1;
        if(!isValidLimit($limit)){
            return array("result" => INVALID_LIMIT_PARAM);
        }

        $login = getLogin();

        $result = listGroups($login, $limit);
        return $result;
    }

    session_start();
    echo json_encode(handleGroupsListingRequest());

?>