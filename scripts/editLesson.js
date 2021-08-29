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
 * Dodaje na koniec tabeli wiersz o podanych danych na koniec tabeli.
 * 
 * @param {Integer} id id pary pytanie-odpowiedź
 * @param {String} question treść pytania 
 * @param {String} answer treść odpowiedzi
 */
function appendRow(id, question, answer) {
    var table = document.getElementById('lesson-contents');
    var newRow = table.insertRow(-1);
    newRow.setAttribute("data-id", id);

    var insertTextField = function(contents) {
        var newCell = newRow.insertCell();
        var newSpan = document.createElement("span");
        newSpan.innerHTML = contents;

        var newInput = document.createElement("input");
        newInput.value = contents;
        newInput.style.display = "none";

        newCell.appendChild(newSpan);
        newCell.appendChild(newInput);
    };

    insertTextField(question);
    insertTextField(answer);

    var actionsCell = newRow.insertCell();
    var editButton = document.createElement("button");
    $(editButton).click(editEntry);
    $(editButton).toggleClass("edit-btn");
    editButton.innerHTML = 'Edytuj';

    actionsCell.appendChild(editButton);

    var removeButton = document.createElement("button");
    $(removeButton).click(removeEntry);
    $(removeButton).toggleClass("remove-btn");
    removeButton.innerHTML = 'Usuń';

    actionsCell.appendChild(removeButton);
}

/**
 * Wczytuje dane lekcji z bazy danych i tworzy z nich tabelę.
 */
async function drawTable() {
    try {
        const lessonId = $("#edit-lesson").data("lesson-id");
        const response = await $.get("server/getLessonEntries.php", {
            lessonId: lessonId
        });

        const result = JSON.parse(response);

        // === ?
        if (result.result == 0) {
            const data = result.data;
            for (var i = 0; i < data.length; i++) {
                appendRow(data[i].id, data[i].question, data[i].answer);
            }
        } else {
            // TODO: obsługa błędów
            throw 'Ludzie przecież nikogo tu nie ma';
        }

    } catch(error) {
        console.log(error);   
    }
}

/**
 * Obsługuje wysłanie żadania dodania pary pytanie-odpowiedź.
 * 
 * Dodaje wiersz na koniec tabeli.
 * @param {Event} event Obiekt wydarzenia js związany z wysłaniem formularza przez użytkownika.
 *                      Używany aby zapobiec domyślnej obsłudze formularza.
 */
async function addEntry(event) {
    try {
        event.preventDefault();
        const lessonId = $("#edit-lesson").data("lesson-id");
        const question = $("#add-entry-form-question").val();
        const answer = $("#add-entry-form-answer").val();

        const response = await $.get("server/addLessonEntry.php", {
            lessonId: lessonId,
            question: question,
            answer: answer
        });

        const result = JSON.parse(response);

        // === ?
        if(result.result == 0) {
            appendRow(result.createdEntryId, question, answer);
        } else {
            // TODO obsługa błędów
            throw 'Ludzie przecież nikogo tu nie ma';
        }
    } catch (error) {
        // nie udało się otrzymać od serwera odpowiedzi
        // lub otrzymana odpowiedź nie jest w żądanej postaci
        reportError("niespodziewany błąd serwera");
    }
}

/**
 * Obsługuje przejście wiersza tabeli w tryb edycji.
 *
 * Podmienia widoczne pola w tabeli ze spanów na input,
 * zmienia guziki: Edycja na Zapisz, Usuń na Anuluj.
 */
async function editEntry() {
    try {
        // Znajduje elementy span i input za które odpowiada kliknięty button,
        // ustawia style tak, żeby w miejscu spana pojawił się input.
        for(var i = 0; i < 2; i++) {
            var td = $(this).parent().parent().children()[i];
            var spanElement = td.firstElementChild;
            spanElement.style.display = "none";
            var inputElement = spanElement.nextElementSibling;
            inputElement.style.display = "initial";
        }

        // Guzik edycji przechodzi w guzik zapisu.
        $(this).removeClass("edit-btn");
        $(this).unbind();
        $(this).click(saveEntry);
        $(this).toggleClass("save-btn");
        $(this).html("Zapisz");

        // Zmieniamy guzik do usuwania w guzik anulowania zmian.
        var removeButton = this.nextElementSibling;
        $(removeButton).removeClass("remove-btn");
        $(removeButton).unbind();
        $(removeButton).click(cancelEdit);
        $(removeButton).toggleClass("cancel-btn");
        $(removeButton).html("Anuluj");
    } catch (error) {
        // nie udało się otrzymać od serwera odpowiedzi
        // lub otrzymana odpowiedź nie jest w żądanej postaci
        reportError("niespodziewany błąd serwera");
    }
}

/**
 * Cofa zmiany w wierszu i wychodzi z trybu edycji.
 *
 * Podmienia widoczne pola w tabeli z inputów na span,
 * zmienia guziki: Zapisz na Edycja, Anuluj na Usuń.
 */
async function cancelEdit() {
    try {
        // Znajduje elementy span i input za które odpowiada kliknięty button,
        // ustawia style tak, żeby w miejscu inputu pojawił się span.
        // Ustawia w inpucie wartość sprzed zmian użytkownika.
        for(var i = 0; i < 2; i++) {
            // <tr> -> <td> -> <button>(this), nas interesują dwa pierwsze.
            var td = $(this).parent().parent().children()[i];
            var spanElement = td.firstElementChild;
            spanElement.style.display = "initial";

            var inputElement = spanElement.nextElementSibling;
            inputElement.style.display = "none";
            // Wartość sprzed zmiany była schowana w spanie.
            inputElement.value = spanElement.innerHTML;
        }

        // Podmiana funkcji i stylu guzika z Anuluj na Usuń.
        $(this).removeClass();
        $(this).unbind();
        $(this).click(removeEntry);
        $(this).toggleClass("remove-btn");
        $(this).html("Usuń");

        // Podmiana funkcji i stylu guzika z Zapisz na Edytuj.
        var saveButton = this.previousElementSibling;
        $(saveButton).removeClass();
        $(saveButton).unbind();
        $(saveButton).click(editEntry);
        $(saveButton).toggleClass("edit-btn");
        $(saveButton).html("Edytuj");
    } catch (error) {
        // nie udało się otrzymać od serwera odpowiedzi
        // lub otrzymana odpowiedź nie jest w żądanej postaci
        reportError("niespodziewany błąd serwera");
    }
}

/**
 * Obsługuje żądanie zapisania zmiany w parze pytanie-odpowiedź.
 * 
 * Zapisuje zmiany użytkowika do bazy danych.
 * Podmienia widoczne pola w tabeli z inputów na span,
 * zmienia guziki: Zapisz na Edycja, Anuluj na Usuń.
 */
async function saveEntry() {
    try {
        // Znajdujemy wiersz tabeli odpowiadający guzikowi
        var tr = $(this).parent().parent();

        // Znajdujemy kolumny wiersza odpowiadające pytaniu i odpowiedzi.
        var tdQuestion = tr.children()[0];
        var tdAnswer = tr.children()[1];

        // Wyciągamy dane wiersza po modyfikacji użytkownika.
        var entryId = tr.data("id");
        var question = $(tdQuestion).children()[1].value;
        var answer = $(tdAnswer).children()[1].value;

        const response = await $.get("server/editLessonEntry.php", {
            entryId: entryId,
            newQuestion: question,
            newAnswer: answer
        });

        const result = JSON.parse(response);
        if (result.result == 0) { // Jak się uda zrobić zmianę w bazie.
            // W elementach <span> aktualizujemy wartości bo są nieaktualne.
            $(tdQuestion).children()[0].innerHTML = question;
            $(tdAnswer).children()[0].innerHTML = answer;

            // Znajduje elementy span i input za które odpowiada kliknięty button,
            // ustawia style tak, żeby w miejscu inputu pojawił się span.
            for(var i = 0; i < 2; i++) {
                // <tr> -> <td> -> <button>(this), nas interesują dwa pierwsze.
                var td = $(this).parent().parent().children()[i];
                var spanElement = td.firstElementChild;
                spanElement.style.display = "initial";

                var inputElement = spanElement.nextElementSibling;
                inputElement.style.display = "none";
            }

            // Podmiana funkcji i stylu guzika z Zapisz na Edytuj.
            $(this).removeClass("save-btn");
            $(this).unbind();
            $(this).click(editEntry);
            $(this).toggleClass("edit-btn");
            $(this).html("Edytuj");

            // Podmiana funkcji i stylu guzika z Anuluj na Usuń.
            var removeButton = this.nextElementSibling;
            $(removeButton).removeClass();
            $(removeButton).unbind();
            $(removeButton).click(removeEntry);
            $(removeButton).toggleClass("remove-btn");
            $(removeButton).html("Usuń");
        } else {
            // TODO obsługa błędów
            throw 'Ludzie przecież nikogo tu nie ma';
        }
    
    } catch (error) {
        // nie udało się otrzymać od serwera odpowiedzi
        // lub otrzymana odpowiedź nie jest w żądanej postaci
        reportError("niespodziewany błąd serwera");
    }
}

/**
 * Obsługuje żądanie usunięcia pary pytanie-odpowiedź.
 *
 * Usuwa odpowiadający wierszowi wpis z bazy danych.
 * Podmienia widoczne pola w tabeli z inputów na span,
 * zmienia guziki: Zapisz na Edycja, Anuluj na Usuń.
 */
async function removeEntry() {
    if (confirm("Czy chcesz usunąć?")) {
        try {
            var tr = $(this).parent().parent();
            var entryId = tr.data("id");

            const response = await $.get("server/removeLessonEntry.php", {
                entryId: entryId
            });

            const result = JSON.parse(response);

            if(result.result == 0) {
                $(this).parent().parent().remove();
            } else {
                // TODO obsługa błędów
                throw 'Ludzie przecież nikogo tu nie ma';
            }
        } catch (error) {

            // nie udało się otrzymać od serwera odpowiedzi
            // lub otrzymana odpowiedź nie jest w żądanej postaci
            reportError("niespodziewany błąd serwera");
        }
    }
}

$(document).ready(function() {
    drawTable();
    // Podłączenie funkcji obsługi do formularzy.
    $("#add-entry-form").submit(addEntry);
});