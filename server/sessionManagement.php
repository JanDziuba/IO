<?php
    /**
     * Sprawdza czy w danej sesji jest zalogowany użytkownik.
     * 
     * @return true jeżeli użytkownik jest zalogowany
     *         false jeżeli użytkownik nie jest zalogowany
     */
    function isLogged() {
        return isset($_SESSION['login']) && !empty($_SESSION['login']);
    }

    /**
     * Loguje użytkownika o podanym loginie.
     */
    function login($login) {
	    $_SESSION['login'] = $login;
    }

    /**
     * Zwraca login aktualnie zalogowanego użytkownika.
     * 
     * @return Login zalogowanego użytkownika
     *         lub pusty ciąg znaków jeżeli użytkownik jest niezalogowany.
     */
    function getLogin() {
        return isLogged() ? $_SESSION['login'] : '';
    }
?>