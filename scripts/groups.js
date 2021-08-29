
async function displayGroups() {
    try {
        const response = await $.get('server/listGroups.php', {
            limit: -1
        });

        const result = JSON.parse(response);
        console.log(result);
        if (result.result === 0) {
            // Treningi które są powiązane z obecną lekcją
            for (let i = 0; i < result.data.length; i++) {

                let newForm = document.createElement('form');
                newForm.setAttribute('class', "group");
                newForm.setAttribute('action', "groupAction.php");
                newForm.setAttribute('method', "get");

                let idInput = document.createElement('input');
                idInput.setAttribute('type', "hidden");
                idInput.setAttribute('name', "id");
                idInput.setAttribute('value', result.data[i].id);
                newForm.appendChild(idInput);

                let nameInput = document.createElement('input');
                nameInput.setAttribute('type', "hidden");
                nameInput.setAttribute('name', "name");
                nameInput.setAttribute('value', result.data[i].name);
                newForm.appendChild(nameInput);

                let isAdminInput = document.createElement('input');
                isAdminInput.setAttribute('type', "hidden");
                isAdminInput.setAttribute('name', "is-admin");
                isAdminInput.setAttribute('value', result.data[i].role);
                newForm.appendChild(isAdminInput);
              
                let lessonsInput = document.createElement('button');
                lessonsInput.setAttribute('name', "submit");
                lessonsInput.setAttribute('value', "Lekcje");
                lessonsInput.innerHTML = result.data[i].name;
                newForm.appendChild(lessonsInput);

                if (result.data[i].role === "1") {
                    let editInput = document.createElement('input');
                    editInput.setAttribute('type', "submit");
                    editInput.setAttribute('name', "submit");
                    editInput.setAttribute('value', "Edytuj");
                    newForm.appendChild(editInput);

                    let deleteInput = document.createElement('input');
                    deleteInput.setAttribute('type', "submit");
                    deleteInput.setAttribute('name', "submit");
                    deleteInput.setAttribute('value', "Usuń");
                    newForm.appendChild(deleteInput);

                    document.getElementById("adminGroups").appendChild(newForm);
                } else {
                    let leaveInput = document.createElement('input');
                    leaveInput.setAttribute('type', "submit");
                    leaveInput.setAttribute('name', "submit");
                    leaveInput.setAttribute('value', "Opuść");
                    newForm.appendChild(leaveInput);

                    document.getElementById("nonAdminGroups").appendChild(newForm);
                }

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
    await displayGroups();
});