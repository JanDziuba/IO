<?php
    require 'server/sessionManagement.php';

    /**
     * Przekierowuje użytkownika do strony domowej jeśli jest zalogowany.
     * 
     * Do używania na stronach, które wymagają aby użytkownik był niezalogowany
     * np. formularz logowania czy rejestracji.
     * 
     * Ze względu na użycie funkcji header(), powinna być wywołana przed wypisaniem
     * jakiegokolwiek tekstu na stronie.
     */
    function redirectLogged() {
        session_start();
        if (isLogged()) {
            header('Location: homepage.php');
        }
    }
    
    /**
     * Przekierowuje użytkownika do formularza logowania jeżeli nie jest zalogowany.
     * 
     * Do używania na stronach, które wymagają aby użytkownik był zalogowany
     * np. strona domowa itp.
     * 
     * Ze względu na użycie funkcji header(), powinna być wywołana przed wypisaniem
     * jakiegokolwiek tekstu na stronie.
     */
    function redirectNotLogged() {
        session_start();
        if (!isLogged()) {
            header('Location: loginForm.php');
        }
    }
?>