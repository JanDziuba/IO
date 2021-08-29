<?php
require 'sessionManagement.php';
require 'namingConstraints.php';
require 'groupHelpers.php';

define("SUCCESS", 0);
define("NO_LOGGED_USER", 1);
define("MISSING_PARAM", 2);
define("INVALID_LESSON_ID_PARAM", 3);
define("INVALID_LIMIT_PARAM", 4);
define("LESSON_DOES_NOT_EXIST_OR_PERMISSION_DENIED", 5);
define("UNEXPECTED_DB_ERROR", 6);


function listLessonSharings($login, $lessonId, $limit) {
    global $db_host, $db_port, $db_dbname, $db_user, $db_password;
    $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
    
    $check = check_if_lesson_exists_and_has_permission($conn, $lessonId, $login);
    if($check == -1) {
        return array("result" => UNEXPECTED_DB_ERROR);
    }
    if($check == 0) {
        return array("result" => LESSON_DOES_NOT_EXIST_OR_PERMISSION_DENIED);
    }

    if ($limit == -1) {
        $result = pg_query_params(
            $conn,
            'SELECT id, name FROM GroupsLessons JOIN LearningGroup ON group_fk = id WHERE lesson_fk = $1',
            array($lessonId)
        );
    } else {
        $result = pg_query_params(
            $conn,
            'SELECT id, name FROM GroupsLessons JOIN LearningGroup ON group_fk = id WHERE lesson_fk = $1 LIMIT $2',
            array($lessonId, $limit)
        );
    }
    if($result === false) {
        return array("result" => UNEXPECTED_DB_ERROR);
    }
    if (pg_num_rows($result) == 0) {
        return array("result" => SUCCESS, "data" => array());
    }
    return array("result" => SUCCESS, "data"=> pg_fetch_all($result));
}

function handleListLessonSharings() {
    if (!isLogged()) {
        return array("result" => NO_LOGGED_USER);
    }
    if (!isset($_GET['lessonId'])) {
        return array("result" => MISSING_PARAM);
    }
    $lessonId = $_GET['lessonId'];
    if (!isLessonId($lessonId)) {
        return array("result" => INVALID_LESSON_ID_PARAM);
    }
    $limit = isset($_GET['limit']) ? $_GET['limit'] : -1;
    if(!isValidLimit($limit)){
        return array("result" => INVALID_LIMIT_PARAM);
    }

    $login = getLogin();
    $result = listLessonSharings($login, $lessonId, $limit);
    return $result;
}

session_start();
echo json_encode(handleListLessonSharings());
?>