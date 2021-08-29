<?php

require 'config.php';

/**
 * Dodaje grupę o zadanej nazwie do bazy danych
 * 
 * @param {String} $creatorLogin Login użytkownika tworzącego grupę.
 * @param {String} $name Nazwa grupy.
 */
function createGroup($creatorLogin, $name, $description) {
    global $db_host, $db_port, $db_dbname, $db_user, $db_password;
    $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
    $result = pg_query_params(
        $conn, 
        'INSERT INTO LearningGroup(name, description, admin_fk) 
        VALUES ($1, $2, $3) 
        RETURNING id', 
        array($name, $description, $creatorLogin)
    );
    if ($result == false || pg_num_rows($result) != 1) {
        throw new Exception("Unexpected db error");
    }

    return intval(pg_fetch_row($result)[0]);
}

/**
     * Usuwa grupę 
     * @param {String} $groupID - ID grupy.
     * @return true W przypadku gdy operacja zakończy sie sukcesem.
     *         false w przeciwnym razie.
     */
function removeGroup($login, $groupID) {
    global $db_host, $db_port, $db_dbname, $db_user, $db_password;
    $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
    $result = pg_query_params(
        $conn, 
        'DELETE FROM LearningGroup 
        WHERE id = $1 AND admin_fk = $2',
        array($groupID, $login)
    );
    if ($result == false) {
        throw new Exception("Unexpected db error");
    }
    if (pg_affected_rows($result) == 0) {
        return false;
    }
    return true;
}

/**
 * @param {String} $newName - null jeśli nie zmieniamy
 * @param {String} $newDescription - null jeśli nie zmieniamy
 * @param {String} $user - zalogowany użytkownik
 * @param {liczba} $groupId - id grupy
 * zmienia wartości $newName i $newDescription jeśli nie są null
 * @return 0 jeśli wyszstko dodano poprawnie (lub nie było nic do dodania)
 * @return 3 jeśli żądana grupa nie istnieje albo $user nie ma uprawnień
 * @return 7 jeśli wystąpił niespodziewany błąd aktualizacji grupy
 */
function updateGroup($groupId, $user, $newName, $newDescription) {
    global $db_host, $db_port, $db_dbname, $db_user, $db_password;
    $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
    $check = pg_query_params(
        $conn,
        'SELECT * FROM LearningGroup WHERE id=$1 AND admin_fk=$2',
        array($groupId, $user)
    );
    if ($check == false) {
        return 7;
    }
    if (pg_num_rows($check) == 0) {
        return 3;
    }
    if (!($newName === null)) {
        $result1 = pg_query_params(
            $conn, 
            'UPDATE LearningGroup
            SET name = $2
            WHERE id=$1',
            array($groupId, $newName)
        );
        if ($result1 === false || pg_affected_rows($result1) != 1) {
            return 7;
        }
    }
    if (!($newDescription === null)) {
        $result2 = pg_query_params(
            $conn, 
            'UPDATE LearningGroup
            SET description = $2
            WHERE id=$1',
            array($groupId, $newDescription)
        );
        if ($result2 === false || pg_affected_rows($result2) != 1) {
            return 7;
        }
    }
    return 0;
}

/**
 * Pozwala użytkownikowi opuścić grupę.
 * @return 0 jeśli wyszstko dodano poprawnie (lub nie było nic do dodania)
 * @return 3 jeśli żądana grupa nie istnieje albo użytkownik do niej nie należy
 * @return 6 jeśli wystąpił niespodziewany błąd bazy danych
 */
function leaveGroup($login, $groupId) {
    global $db_host, $db_port, $db_dbname, $db_user, $db_password;
    $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
    $result = pg_query_params(
        $conn,
        'DELETE FROM GroupMembers WHERE group_fk=$1 AND member_fk=$2',
        array($groupId, $login)
    );
    if ($result == false) {
        print("AAA");
        return 6;
    }
    if (pg_affected_rows($result) == 0) {
        return 3;
    }
    return 0;
}

/**
 * @return 0 jeśli request wysłany pomyślnie
 * @return 3 jeśli grupa nie istnieje
 * @return 5 jeśli użytkownik już prosił o dołączenie do grupy
 * @return 7 jeśli wystąpił niespodziewany błąd bazy danych
 * @return 8 jeśli użytkownik już należy do grupy lub jest jej adminem
 */
function sendJoinRequest($login, $groupId, $text_request) {
    global $db_host, $db_port, $db_dbname, $db_user, $db_password;
    $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
    $check = pg_query_params(
        $conn,
         'SELECT id, admin_fk FROM LearningGroup WHERE id=$1',
        array($groupId)
    );
    if ($check === false) {
        return 7;
    }
    if (pg_num_rows($check) == 0) {
        return 3;
    }
    if (pg_fetch_row($check)[1] == $login) {
        return 8;
    }
    $check = pg_query_params(
        $conn,
         'SELECT * FROM GroupMembers 
         WHERE member_fk=$1 AND group_fk=$2',
        array($login, $groupId)
    );
    if (pg_num_rows($check) != 0) {
        return 8;
    }
    $check = pg_query_params(
        $conn,
         'SELECT * FROM GroupRequest 
         WHERE requesting_user_fk=$1 AND group_fk=$2',
        array($login, $groupId)
    );
    if ($check === false) {
        return 7;
    }
    if (pg_num_rows($check) != 0) {
        return 5;
    }
    $result = pg_query_params(
        $conn,
        'INSERT INTO GroupRequest(group_fk, requesting_user_fk, text) VALUES ($1, $2, $3)',
        array($groupId, $login, $text_request)
    );
    if ($result === false) {
        print("AA");

        return 7;
    }
    return 0;
}


/**
 * @return 1 jeśli wybrana prośba dołączenia do grupy nie istnieje,
 *           lub dotyczy grupy, do której użytkownik nie ma odpowiednich praw dostępu
 * @return 0 wpp
 * @return -1 jeśli wystąpił niespodziewany błąd bazy danych
 */
function check_if_request_exists_and_has_permission($conn, $login, $requestId){
    // najpierw sprawdzam czy taki join request istnieje
    $result = pg_query_params(
        $conn,
         'SELECT group_fk, requesting_user_fk FROM GroupRequest WHERE id=$1',
        array($requestId)
    );
    if ($result === false) {
        return array("result" => -1);
    }
    if (pg_num_rows($result) == 0) {
        return array("result" => 0); // nie ma requesta o podanym id
    }
    $row = pg_fetch_array($result, 0);
    $groupId = $row["group_fk"];
    $requesting_user = $row["requesting_user_fk"];
    // sprawdzam czy user ma uprawnienia do tej grupy
    $result = pg_query_params(
        $conn,
         'SELECT * FROM LearningGroup WHERE id=$1 AND admin_fk=$2',
        array($groupId, $login)
    );
    if ($result === false) {
        return array("result" => -1);
    }
    if (pg_num_rows($result) == 0) {
        return array("result" => 0); // user nie ma uprawnień admina do tej grupy
    }
    return array("result" => $result, "groupId" => $groupId, "user" => $requesting_user);
}


/**
 * akceptuje prośbę o dodanie do grupy
 * @return 0 jeśli zaakceptowano prośbę pomyślnie
 * @return 4 jeśli wybrana prośba dołączenia do grupy nie istnieje,
 *           lub dotyczy grupy, do której użytkownik nie ma odpowiednich praw dostępu
 * @return 5 jeśli wystąpił niespodziewany błąd bazy danych
 */
function acceptJoinRequest($login, $joinRequestId) {
    global $db_host, $db_port, $db_dbname, $db_user, $db_password;
    $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");

    $ret = check_if_request_exists_and_has_permission($conn, $login, $joinRequestId);
    $result = $ret["result"];
    if($result == 0) {
        return 4;
    }
    if($result == -1) {
        return 5;
    }
    $groupId = $ret["groupId"];
    $requesting_user = $ret["user"];

    // wszystko jest ok, można dodać użytkownika do grupy
    pg_query("BEGIN TRANSACTION;");
    $result = pg_query_params(
        $conn, 
        "DELETE FROM GroupRequest WHERE id=$1", 
        array($joinRequestId)
    );
    if ($result === false) {
        pg_query("ROLLBACK;");
        return 5;
    }
    $result = pg_query_params(
        $conn, 
        "INSERT INTO GroupMembers VALUES($1, $2)", 
        array($groupId, $requesting_user)
    );
    if ($result === false) {
        pg_query("ROLLBACK;");
        return 5;
    }
    pg_query("COMMIT;");
    return 0;
}

/**
 * odmawia dodania użytkownika do grupy
 * @return 0 jeśli zaakceptowano prośbę pomyślnie
 * @return 4 jeśli wybrana prośba dołączenia do grupy nie istnieje,
 *           lub dotyczy grupy, do której użytkownik nie ma odpowiednich praw dostępu
 * @return 5 jeśli wystąpił niespodziewany błąd bazy danych
 */
function denyJoinRequest($login, $joinRequestId) {
    global $db_host, $db_port, $db_dbname, $db_user, $db_password;
    $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");

    $ret = check_if_request_exists_and_has_permission($conn, $login, $joinRequestId);
    $result = $ret["result"];
    if($result == 0) {
        return 4;
    }
    if($result == -1) {
        return 5;
    }
    $groupId = $ret["groupId"];
    $requesting_user = $ret["user"];

    pg_query("BEGIN TRANSACTION;");
    $result = pg_query_params($conn, "DELETE FROM GroupRequest WHERE id=$1", array($joinRequestId));
    if ($res === false) {
        pg_query("ROLLBACK;");
        return 5;
    }
    pg_query("COMMIT;");
    return 0;
}

// Zwraca -1 jeśli był błąd w bazie danych, 0 jeśli lekcja nie istnieje lub użytkownik
// nie ma do niej praw dostępu, 1 wpp
function check_if_lesson_exists_and_has_permission($conn, $lessonId, $login) {
    $result = pg_query_params(
        $conn,
        'SELECT id, user_fk FROM Lesson WHERE id = $1', 
        array($lessonId)
    );
    if($result === false) {
        return -1;
    }
    if (pg_num_rows($result) == 0) {
        return 0; // nie ma takiej lekcji
    }
    $row = pg_fetch_array($result, 0);
    $user_fk = $row["user_fk"];
    if($user_fk != $login) {
        return 0;
    }
    return 1;
}

/**
 * @return 0 jeśli dana grupa nie istnieje lub
 *           lub użytkownik nie ma do niej odpowiednich praw dostępu
 * @return -1 jeśli wystąpił niespodziewany błąd bazy danych
 * @return 1 jeśli operacja przebiegła pomyślnie
 */
function check_if_group_exists_and_has_permission($conn, $login, $groupId){
    // najpierw sprawdzam czy taka grupa istnieje
    $result = pg_query_params(
        $conn,
         'SELECT id, admin_fk FROM LearningGroup WHERE id=$1',
        array($groupId)
    );
    if ($result === false) {
        return -1;
    }
    if (pg_num_rows($result) == 0) {
        return 0; // nie ma takiej grupy
    }
    $row = pg_fetch_array($result, 0);
    $admin = $row["admin_fk"];
    if($admin != $login) {
        return 0; // user nie ma uprawnień admina do tej grupy
    }
    return 1;
}


function getGroupJoinRequestsList($login, $limit, $groupId) {
    global $db_host, $db_port, $db_dbname, $db_user, $db_password;
    $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");

    $result = check_if_group_exists_and_has_permission($conn, $login, $groupId);
    if($result == 0) {
        return 5;
    }
    if($result == -1) {
        return 6;
    }

    if ($limit == -1) {
        $result = pg_query_params(
            $conn,
            'SELECT id, text as description, requesting_user_fk as "userName" FROM GroupRequest WHERE group_fk = $1', 
            array($groupId));
    }
    else {
        $result = pg_query_params($conn, 'SELECT id, text as description, requesting_user_fk as userName FROM GroupRequest WHERE group_fk = $1 LIMIT $2',
            array($groupId, $limit));
    }
    if($result === false) {
        return 6;
    }
    return pg_fetch_all($result);
}

?>