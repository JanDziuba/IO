
/**
 * Wyświetla użytkownikowi informację o błędzie.
 *
 * @param {String} text Błąd do wyświetlenia użytkownikowi.
 */
function reportError(text) {
    // TODO
    console.error(text);
}

/**
 * Obsługuje wysłanie żadania utworzenia nowej grupy.
 *
 * Wczytuje wartości odpowiednich pól i wysyła żadanie get na serwer.
 * Następnie w zależności od zwróconego wyniku przekierowuje użytkownika na stronę edycji
 * nowo-utworzonej grupy, lub wyświetla mu komunikat o błędzie.
 * @param {Event} event Obiekt wydarzenia js związany z wysłaniem formularza przez użytkownika.
 *                      Używany aby zapobiec domyślnej obsłudze formularza.
 */
async function handleSubmit(event) {
    event.preventDefault();
    const name = event.target.name.value;
    const description = event.target.description.value;
    // TODO
    // sprawdzić czy opis nie jest za długi jeszcze przed wysłaniem go na serwer
    try {
        // wysłanie żądania post na serwer i oczekiwanie na odpowiedź
        const response = await $.post("server/createGroup.php", {
            name: name,
            description: description,
        });
        // parsowanie wyniku zwróconego przez serwer
        const result = JSON.parse(response);
        if (result.result === 0) {
            if (result.createdGroupId === undefined) {
                reportError("Unexpected result format");
            } else {
                console.log("success");
                window.location = `groupUsers.php?id=${result.createdGroupId}&name=${name}`;
            }
        } else {
            reportError(result.result);
        }
    } catch (error) {
        // nie udało się otrzymać od serwera odpowiedzi
        // lub otrzymana odpowiedź nie jest w żądanej postaci
        reportError("niespodziewany błąd serwera");
    }
}

$(document).ready(function() {
    // Podłączenie funkcji obsługi do formularza.
    $("#newGroupForm").submit(handleSubmit);
});
