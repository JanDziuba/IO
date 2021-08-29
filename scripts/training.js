/**
 * Populuje div #trainings listą treningów danego użytkownika
 */
 async function displayTrainings() {
    try {
        const lessonId = $("#main-content-trainings").data('lesson-id');
        const response = await $.get('server/listTrainings.php', {
            limit: -1
        });
        
        const result = JSON.parse(response);
        console.log(result);
        if (result.result === 0) {
            // Treningi które są powiązane z obecną lekcją
            for (let i = 0; i < result.data.length; i++) {
                if (result.data[i].lessonid != lessonId)
                    continue;

                let newForm = document.createElement('form');
                newForm.setAttribute('class', "training");
                newForm.setAttribute('action', "trainingAction.php");
                newForm.setAttribute('method', "get");

                let lessonIdInput = document.createElement('input');
                lessonIdInput.setAttribute('type', "hidden");
                lessonIdInput.setAttribute('name', "id");
                lessonIdInput.setAttribute('value', lessonId);

                let idInput = document.createElement('input');
                idInput.setAttribute('type', "hidden");
                idInput.setAttribute('name', "trainingId");
                idInput.setAttribute('value', result.data[i].id);

                let trainBtn = document.createElement('button');
                trainBtn.setAttribute('name', "submit");
                trainBtn.setAttribute('value', "Trenuj");
                trainBtn.innerHTML = result.data[i].name;

                let editBtn = trainBtn.cloneNode(true);
                editBtn.setAttribute('value', "Edytuj");
                editBtn.innerHTML = 'Edytuj';

                let removeBtn = trainBtn.cloneNode(true);
                removeBtn.setAttribute('value', "Usuń");
                removeBtn.innerHTML = 'Usuń';

                trainBtn.setAttribute('class', "train-btn");
                newForm.appendChild(lessonIdInput);
                newForm.appendChild(idInput);
                newForm.appendChild(trainBtn);
                newForm.appendChild(editBtn);
                newForm.appendChild(removeBtn);

                document.getElementById("trainings").appendChild(newForm);
            }
        } else {
            // TODO: obsługa błędów
            throw 'Ludzie przecież nikogo tu nie ma';
        }
    } catch(error) {
        console.log(error);   
    }
}

$(document).ready(async function() {
    await displayTrainings();
});