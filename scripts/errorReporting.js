const UNEXPECTED_SERVER_ERROR_MESSAGE = 'Niespodziewany błąd serwera.';

/**
 * Informuje użytkownika o błędzie.
 * 
 * @param {String} text Tekst błędu wyświetlany użytkownikowi.
 */
function reportError(text) {
    $('.form-message').text(text);
}