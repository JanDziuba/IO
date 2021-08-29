/**
 * Generuje komunikat o błędzie wyświetlany przy nieudanym logowaniu.
 * 
 * @param {*} resultCode Kod błędu zwrócony przez serwer.
 * @returns Komunikat do wyświetlenia użytkownikowi.
 */
function generateLoginFailureMessage(resultCode) {
    if (resultCode === 1) {
        return `Użytkownik jest już zalogowany.`
    } else if (resultCode === 2 || resultCode === 3 || resultCode === 4) {
        return `Niepoprawny login lub hasło.`
    }
    return `Nieoczekiwany błąd. Przyczyna: ${resultCode}`;
}

/**
 * Obsługuje wysłanie żadania logowania.
 * 
 * Wczytuje wartości odpowiednich pól i wysyła żadanie post na serwer.
 * Następnie w zależności od zwróconego wyniku przekierowuje użytkownika na stronę domową
 * lub wyświetla komunikat o błędzie.
 * @param {Event} event Obiekt wydarzenia js związany z wysłaniem formularza przez użytkownika.
 *                      Używany aby zapobiec domyślnej obsłudze formularz.
 */
async function handleLogin(event) {
    event.preventDefault();
    const login = $("#form-login").val();
    const password = $("#form-password").val();
    try {
        // wysłanie żądania post na serwer i oczekiwanie na odpowiedź
        const response = await $.post("server/login.php", {
            login: login,
            password: password
        });
        // parsowanie wyniku zwróconego przez serwer
        const result = JSON.parse(response);
        if (result.result === 0) {
            window.location = "homepage.php";
        } else {
            reportError(generateLoginFailureMessage(result.result));
        }
    } catch (error) {
        // nie udało się otrzymać od serwera odpowiedzi
        // lub otrzymana odpowiedź nie jest w żądanej postaci
        reportError(UNEXPECTED_SERVER_ERROR_MESSAGE);
    }
}

$(document).ready(function() {
    // Podłączenie funkcji obsługującej logowanie do formularza.
    $("form").submit(handleLogin);
});