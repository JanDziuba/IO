const PASSWORD_DIFFER_ERROR_MESSAGE = 'Hasła nie są identyczne.';

/**
 * Generuje komunikat o błędzie wyświetlany przy nieudanej rejestracji.
 * 
 * @param {*} resultCode Kod błędu zwrócony przez serwer.
 * @returns Komunikat do wyświetlenia użytkownikowi.
 */
function generateRegistrationFailureMessage(resultCode) {
    if (resultCode === 1) {
        return `Użytkownik jest już zalogowany.`
    } else if (resultCode === 2 || resultCode === 3) {
        return `Niepoprawny login lub hasło.`
    } else if (resultCode === 4) {
        return `Niepoprawny login.`
    } else if (resultCode === 5) {
        return `Niepoprawne hasło.`
    } else if (resultCode === 6) {
        return `Login zajęty.`
    } else if (resultCode === 7) {
        return `Błąd serwera.`
    }
    return `Nieoczekiwany błąd. Przyczyna: ${resultCode}`;
}

/**
 * Obsługuje wysłanie żadania rejestracji.
 * 
 * Wczytuje wartości odpowiednich pól i wysyła żadanie post na serwer.
 * Następnie w zależności od zwróconego wyniku przekierowuje użytkownika na stronę domową
 * lub wyświetla komunikat o błędzie.
 * @param {Event} event Obiekt wydarzenia js związany z wysłaniem formularza przez użytkownika.
 *                      Używany aby zapobiec domyślnej obsłudze formularz.
 */
async function handleRegistration(event) {
    event.preventDefault();
    const login = $("#form-login").val();
    const password = $("#form-password").val();
    const passwordCheck = $("#form-password-check").val();
    if (password !== passwordCheck) {
        reportError(PASSWORD_DIFFER_ERROR_MESSAGE)
        return;
    }
    try {
        // wysłanie żądania post na serwer i oczekiwanie na odpowiedź
        const response = await $.post("server/register.php", {
            login: login,
            password: password
        });
        // parsowanie wyniku zwróconego przez serwer
        const result = JSON.parse(response);
        if (result.result === 0) {
            window.location = "homepage.php";
        } else {
            reportError(generateRegistrationFailureMessage(result.result));
        }
    } catch (error) {
        // nie udało się otrzymać od serwera odpowiedzi
        // lub otrzymana odpowiedź nie jest w żądanej postaci
        reportError(UNEXPECTED_SERVER_ERROR_MESSAGE);
    }
}

$(document).ready(function() {
    // Podłączenie funkcji obsługującej logowanie do formularza.
    $("form").submit(handleRegistration);
});