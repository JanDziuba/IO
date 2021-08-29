
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
        $("#errors").html(`Błąd. Lekcja niedostępna.`);
    } else if (text === 4) {
        $("#errors").html(`Błąd. Niepoprawna wielkość treningu.`);
    } else if (text === 5) {
        $("#errors").html(`Błąd. Niepoprawna liczba powtórzeń.`);
    } else if (text === 6) {
        $("#errors").html(`Błąd. Niepoprawna nazwa treningu.`);
    } else if (text === 7) {
        $("#errors").html(`Niespodziewany błąd serwera.`);
    } else {
        $("#errors").html("Przyczyna błędu: " + text);
    }
    console.error(text);
}

/**
 * Obsługuje wysłanie żadania edycji treningu.
 *
 * Wczytuje wartości odpowiednich pól i wysyła żadanie post na serwer.
 * Następnie w zależności od zwróconego wyniku przekierowuje użytkownika na listę treningów
 * albo zwraca informację o błędzie
 * @param {Event} event Obiekt wydarzenia js związany z wysłaniem formularza przez użytkownika.
 *                      Używany aby zapobiec domyślnej obsłudze formularz.
 */
async function handleSubmit(event) {
    event.preventDefault();
    const lessonId = event.target.lessonId.value;
    const trainingId = event.target.trainingId.value;
    const name = event.target.name.value;
    const batchSize = event.target.batchSize.value;
    const trainingRepetitions = event.target.trainingRepetitions.value;

    // TODO
    // sprawdzić czy opis nie jest za długi jeszcze przed wysłaniem go na serwer
    try {
        // wysłanie żądania get na serwer i oczekiwanie na odpowiedź
        const response = await $.post("server/editTraining.php", {
            lessonId: lessonId,
            trainingId: trainingId,
            batchSize: batchSize,
            trainingRepetitions: trainingRepetitions,
            name: name
        });

        // parsowanie wyniku zwróconego przez serwer
        const result = JSON.parse(response);
        if (result.result === 0) {
            window.location = "trainings.php?id=" + lessonId;
        } else {
            reportError(result.result);
        }
    } catch (error) {
        // nie udało się otrzymać od serwera odpowiedzi
        // lub otrzymana odpowiedź nie jest w żądanej postaci
        reportError("niespodziewany błąd serwera");
    }
}

/**
 * Wyświetla użytkownikowi informację o błędzie.
 *
 * @param {*} text Błąd do wyświetlenia użytkownikowi.
 */
function reportDisplayTrainingDataError(text) {
    if (text === 1) {
        $("#errors").html(`Błąd. Użytkownik nie jest zalogowany.`);
    } else if (text === 2) {
        $("#errors").html(`Błąd. trainingId ma nieprawidłową wartość.`);
    } else if (text === 3) {
        $("#errors").html(`Niespodziewany błąd serwera.`);
    } else if (text === 4) {
        $("#errors").html(`Błąd. Nieprawidłowa metoda http.`);
    } else {
        $("#errors").html("Przyczyna błędu: " + text);
    }
    console.error(text);
}

/**
 * Wyświetla w formularzu dane treningu.
 */
async function displayTrainingData() {
    const trainingId = $('input[name="trainingId"]').val();

    try {
        const response = await $.get("server/getTraining.php", {
            trainingId: trainingId
        });

        // parsowanie wyniku zwróconego przez serwer
        const result = JSON.parse(response);
        if (result.result === 0) {
            $('input[name="name"]').val(result.data.name);
            $('input[name="batchSize"]').val(result.data.batchsize);
            $('input[name="trainingRepetitions"]').val(result.data.trainingrepetitions);
        } else {
            reportDisplayTrainingDataError(result.result);
        }
    } catch (error) {
        // nie udało się otrzymać od serwera odpowiedzi
        // lub otrzymana odpowiedź nie jest w żądanej postaci
        reportDisplayTrainingDataError("niespodziewany błąd serwera");
    }

}

$(document).ready(async function () {
    await displayTrainingData();
    // Podłączenie funkcji obsługi do formularza.
    $("#editTrainingForm").submit(handleSubmit);
});
