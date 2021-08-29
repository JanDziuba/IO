<?php
    /**
    * listGroupMembers.php GET:
    * Umożliwia właścicielowi grupy pobranie listy członków grupy.
    * obsługiwane parametry:
    *    groupId {liczba}:       id grupy, której członków chcemy wyświetlić
    *    limit* {liczba}:        maksymalna liczba członków do zwrócenia,
    *                            domyślna wartość -1 oznaczająca wszystkich możliwych
    * @return {"result": 0, "data" : {"id":[id użytkownika], "name":[login użytkownika]}}
    *         jeśli nie wystąpił błąd
    * (ZWRACA W "data" PUSTĄ TABLICĘ [] JEŚLI NIE MA ŻADNYCH MEMBERS W DANEJ GRUPIE)
    */

    require "sessionManagement.php";
    require "namingConstraints.php";
    require "groupHelpers.php";

    define("SUCCESS", 0);
    define("NO_LOGGED_USER", 1);
    define("MISSING_PARAM", 2);
    define("INVALID_GROUP_ID_PARAM", 3); // groupId nie jest liczbą
    define("INVALID_LIMIT_PARAM", 4); // limit ma nieprawidłową wartość
    define("GROUP_DOES_NOT_EXIST_OR_PERMISSION_DENIED", 5);
    define("UNEXPECTED_DB_ERROR", 6);

    function listGroupMembers($login, $groupId, $limit) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
        $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
        $result = check_if_group_exists_and_has_permission($conn, $login, $groupId);
        if($result == 0) {
            return array("result" => GROUP_DOES_NOT_EXIST_OR_PERMISSION_DENIED);
        }
        if($result == -1) {
            return array("result" => UNEXPECTED_DB_ERROR);
        }
        if ($limit == -1) {
            $result = pg_query_params(
                $conn,
                'SELECT member_fk AS name FROM GroupMembers WHERE group_fk = $1',
                array($groupId)
            );
        } else {
            $result = pg_query_params(
                $conn,
                'SELECT member_fk AS name FROM GroupMembers WHERE group_fk = $1 LIMIT $2',
                array($groupId, $limit)
            );
        }
        if ($result === false) {
            return array("result" => UNEXPECTED_DB_ERROR);
        }
        if (pg_num_rows($result) == 0) {
            return array("result" => SUCCESS, "data" => array());
        }
        return array("result" => SUCCESS, "data"=> pg_fetch_all($result));
    }

    function handleListGroupMembers() {
        if (!isLogged()) {
            return array("result" => NO_LOGGED_USER);
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

        $result = listGroupMembers($login, $groupId, $limit);
        return $result;
    }
    session_start();
    echo json_encode(handleListGroupMembers());
?>