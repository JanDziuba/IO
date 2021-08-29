<?php
	require 'config.php';

    /**
     * Sprawdza czy użytkownik o podanym loginie i haśle istnieje w systemie.
     * 
     * @param {String} $login Login użytkownika.
     * @param {String} $password Hasło użytkownika.
     * @return true jeżeli zadany użytkownik istnieje
     *         false jeżeli zadany użytkownik nie istnieje
     */
	function isExistingUser($login, $password) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
		// Utworzenie połączenia z bazą danych.
		$conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
        // Przygotowanie i wykonanie odpowiedniego zapytania.
		$result = pg_prepare($conn, 'login_query', 'SELECT * FROM account WHERE login=$1 AND password= crypt($2, password)');
		$result = pg_execute($conn, 'login_query', array($login, $password));
		return pg_num_rows($result) != 0;
	}

	/**
	 * Sprawdza czy nie istnieje użytkownik o zadanym loginie.
	 * 
	 * @param {String} $login Login użytkownika.
	 * @return true jeżeli login jest nieużywany.
	 * 		   false jeżeli login jest używany.
	 */
    function isLoginVacant($login) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
		// Utworzenie połączenia z bazą danych.
		$conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
        // Przygotowanie i wykonanie odpowiedniego zapytania.
		$result = pg_prepare($conn, 'login_query', 'SELECT * FROM account WHERE login=$1');
		$result = pg_execute($conn, 'login_query', array($login));
		return pg_num_rows($result) == 0;
    }

	/**
	 * Dodaje użytkownika o zadanym loginie i haśle do bazy danych.
	 * 
	 * @param {String} $login Login nowego użytkownika.
	 * @param {String} $password Hasło nowego użytkownika.
	 * @return true jeżeli operacja się powiodła
	 * 		   false jeżeli operacja się nie powiodła.
	 */
    function registerUser($login, $password) {
        global $db_host, $db_port, $db_dbname, $db_user, $db_password;
        $conn = pg_connect("host=$db_host port=$db_port dbname=$db_dbname user=$db_user password=$db_password");
		pg_query('BEGIN');
		$result = pg_prepare($conn, 'register_query', 'INSERT INTO account(login, password) VALUES ($1, $2)');
		$result = pg_execute($conn, 'register_query', array($login, $password));
		if (pg_affected_rows($result) == 0) {
			pg_query('ROLLBACK');
            return false;
		} else {
			pg_query('COMMIT');
            return true;
		}
    }
?>