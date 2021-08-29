
/**
 * Wyświetla użytkownikowi informację o błędzie.
 * 
 * @param {*} text Błąd do wyświetlenia użytkownikowi.
 */
function reportError(text) {
    if (text === 1) {
        $("#errors").html(`Błąd. Użytkownik nie jest zalogowany.`);
    } else if (text === 2) {
        $("#errors").html(`Błąd. Wypełnij wszystkie pola.`);
    } else if (text === 3) {
        $("#errors").html(`Błąd. Niepoprawna nazwa lekcji`);
    } else if (text === 4) {
        $("#errors").html(`Błąd. Niepoprawny opis lekcji.`);
    } else if (text === 5) {
        $("#errors").html(`Niespodziewany błąd serwera.`);
    } else {
        $("#errors").html("Przyczyna błędu: " + text);
    }
    console.error(text);
}

/**
 * Obsługuje wysłanie żadania utworzenia nowej lekcji.
 * 
 * Wczytuje wartości odpowiednich pól i wysyła żadanie get na serwer.
 * Następnie w zależności od zwróconego wyniku przekierowuje użytkownika na stronę edycji
 * nowo-utworzonej lekcji, lub wyświetla mu komunikat o błędzie.
 * @param {Event} event Obiekt wydarzenia js związany z wysłaniem formularza przez użytkownika.
 *                      Używany aby zapobiec domyślnej obsłudze formularz.
 */
 async function handleLogin(event) {
    event.preventDefault();
    const name = event.target.lessonName.value;
    const description = event.target.description.value;
    // TODO
    // sprawdzić czy opis nie jest za długi jeszcze przed wysłaniem go na serwer
    try {
        // wysłanie żądania get na serwer i oczekiwanie na odpowiedź
        const response = await $.get("server/createNewLesson.php", {
            name: name,
            description: description,
        });
        // parsowanie wyniku zwróconego przez serwer
        const result = JSON.parse(response);
        if (result.result === 0) {
            if (result.createdLessonId === undefined) {
                reportError("Unexpected result format");
            } else {
                console.log("success");
                window.location = `editLessonForm.php?id=${result.createdLessonId}`;
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
    $("#newLessonForm").submit(handleLogin);
});
