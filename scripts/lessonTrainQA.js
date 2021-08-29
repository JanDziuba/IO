// Kolejka na pytania które jeszcze trzeba zadać uczniowi.
var questionQueue = [];

// Tablica z id wpisów na które użytkownik już odpowiedział poprawnie
var answeredIds = [];

// Zbiór id pytań, na które użytkownik nie udzielił poprawnej odpowiedzi za pierwszym razem
var badAnswerIds = new Set();

/**
 * Ładuje do questionQueue wpisy z których nastąpi trening.
 */
async function loadQuestions() {
    try {
        const trainingId = $('#lesson-learn').data('training-id');

        const response = await $.get('server/getNextTrainingBatch.php', {
            trainingId: trainingId
        });
        
        console.log(response);
        const result = JSON.parse(response);
        if (result.result === 0) {
            questionQueue = result.data;
        } else {
            // TODO: obsługa błędów
            throw 'Ludzie przecież nikogo tu nie ma';
        }
    } catch(error) {
        console.log(error);   
    }
}

/**
 * Wsadza do #lesson-learn nowe div #question i #response z treścią pytania
 * z początku kolejki questionQueue, polem tekstowym na odpowiedź i guzikiem na wysłanie odpowiedzi.
 * Ustawia focus na pole, w którym trzeba wpisać odpowiedź.
 */
async function displayQuestion() {
    try {
        let newDiv = document.createElement('div');
        newDiv.id = 'question';
        newDiv.className = 'question';
        $('#lesson-learn').append(newDiv); 
        
        let questionSpan = document.createElement('span');
        questionSpan.innerHTML = questionQueue[0].question;

        newDiv.appendChild(questionSpan);

        let answerForm = document.createElement('form');
        let answerFormInput = document.createElement('input');
        answerFormInput.setAttribute('type', "text");
        answerFormInput.setAttribute('name', "answer");
        answerFormInput.setAttribute('id', "answer-form-answer");
        answerFormInput.setAttribute('placeholder', "Odpowiedź");

        let answerFormSubmit = document.createElement('input');
        answerFormSubmit.setAttribute('type',"submit");
        answerFormSubmit.setAttribute('id', "answer-form-submit");
        answerFormSubmit.setAttribute('value',"Sprawdź odpowiedź");

        answerForm.appendChild(answerFormInput);
        answerForm.appendChild(answerFormSubmit);

        $(answerForm).submit(answerQuestion);

        newDiv.appendChild(answerForm);
        answerFormInput.focus();
    } catch(error) {
        console.log(error);   
    }
}


/*  Przesyła na serwer id wpisów z lekcji które przećwiczył użytkownik
    oraz daje użytkownikowi znać o tym ile błędów popełnił w trakcie treningu.
*/
async function finishTraining(event) {
    try {
        event.preventDefault();
        const trainingId = $('#lesson-learn').data('training-id');
        const correctAnswers = JSON.stringify(answeredIds);
        // W tym treningu user odpowie na wszystko chociaż raz dobrze więc puste
        const wrongAnswers = JSON.stringify(Array.from(badAnswerIds));

        const response = await $.post('server/submitTrainingResult.php', {
            trainingId: trainingId,
            correctAnswers: correctAnswers,
            wrongAnswers: wrongAnswers
        });

        const result = JSON.parse(response);
        if (result.result === 0) {
            $('#question').remove();
            $('#response').remove();
            let newDiv = document.createElement('div');
            newDiv.id = 'lesson-learn-finish';
            newDiv.className = 'lesson-learn-finish';
            $('#lesson-learn').append(newDiv);


            let percentage = Math.round(100 * answeredIds.length / (answeredIds.length + badAnswerIds.size));
            let newSpan = document.createElement('span');
            newSpan.innerHTML = 'Brawo! Odpowiedziałeś za pierwszym razem na ' + percentage + '% pytań.';
            newDiv.appendChild(newSpan);

            newDiv.appendChild(document.createElement('br'));

            let newA = document.createElement('a');
            newA.setAttribute('href', "homepage.php");
            newA.innerHTML = 'Powrót do strony głównej';
            newDiv.appendChild(newA);
        } else {
            // TODO: obsługa błędów
            throw 'Ludzie przecież nikogo tu nie ma';
        }

    } catch (error) {
        console.log(error);
    }
}

/**
 * Wyświetla na stronie kolejne pytanie z kolejki questionQueue
 */
async function displayNextQuestion(event) {
    event.preventDefault();
    $('#question').remove();
    $('#response').remove();
    displayQuestion();
}

/**
 * Obsługuje sprawdzenie czy odpowiedź użytkownika jest poprawna.
 * Jeśli odpowiedź jest poprawna to z kolejki questionQueue zdejmowany jest początek
 * i jego id jest wrzucane do tablicy answeredIds, wpp. jest początek questionQueue
 * jest przerzucany na jej koniec.
 * Użytkownik jest powiadamiany o tym czy podał poprawną odpowiedź, jeśli podał złą
 * to strona mówi mu także jaka była poprawna.
 */
async function answerQuestion(event) {
    try {
        event.preventDefault();
        const curQuestion = questionQueue.shift();
        const answer = $("#answer-form-answer").val();
        const expectedAnswer = curQuestion.answer;
        document.getElementById('answer-form-answer').readOnly = true;

        let responseDiv = document.createElement('div');
        responseDiv.id = 'response';
        responseDiv.className = 'response';
        
        let responseMsgSpan = document.createElement('span');
        if (answer !== expectedAnswer) {
            badAnswerIds.add(curQuestion.id);
            responseMsgSpan.setAttribute('class', "wrong-answer");
            responseMsgSpan.innerHTML = 'Zła odpowiedź! Prawidłowa to: ' + expectedAnswer;

            questionQueue.push(curQuestion);
        } else {
            responseMsgSpan.setAttribute('class', "good-answer");
            
            responseMsgSpan.innerHTML = 'Dobra odpowiedź!';
            if (badAnswerIds.has(curQuestion.id) === false) {
                answeredIds.push(curQuestion.id);
            }
        }

        $('#lesson-learn').append(responseDiv);
        $(responseDiv).html(responseMsgSpan);

        let submitBtn = document.getElementById('answer-form-submit');
        if (questionQueue.length === 0) {
            $('form').unbind();
            $('form').submit(finishTraining);
            $(submitBtn).val('Koniec treningu');
        } else {
            $('form').unbind();
            $('form').submit(displayNextQuestion);
            $(submitBtn).val('Następne pytanie');
        }
    } catch (error) {
        console.log("niespodziewany błąd");
    }
}

$(document).ready(async function() {
    await loadQuestions();
    if (questionQueue.length === 0) {
        // TODO: usunąć to o skończonym treningu jak już API to obsłuży poprawnie
        $('#lesson-learn').html("W lekcji nie ma pytań lub trening się już skończył!");
        return;
    }

    await displayQuestion();
});