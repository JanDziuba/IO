/**
 * Populuje form #lessons listą lekcji, które użytkownik może udostępnić grupie,
 * i checkboxów, żeby je wybrać do udostępnienia.
 */
 async function displayLessons() {
    try {
        const response = await $.get('server/listLessons.php', {
            limit: -1
        });
        
        const result = JSON.parse(response);
        console.log(result);
        if (result.result === 0) {
            let html = "";
            for (const lesson of result.data) {
                html += "<label for=" + lesson['id'] + ">" + lesson['name'] + "</label>";
                html += "<input type='checkbox' id= " + lesson['id'] + " name=" + lesson['id'] + ">";
            }
            html += "<button type='button' id='submit'> Udostępnij </button>";
            $("#lessons").html(html);
        } else {
            // TODO: obsługa błędów
            throw 'Ludzie przecież nikogo tu nie ma';
        }
    } catch(error) {
        console.log(error);   
    }
}

/**
 * Udostępnia wszystkie zaznaczone lekcje.
 */
async function shareLessons() {
    let groupId = $("#title").data('groupId');
    let groupName = $("#title").data('groupName');
    let ids = $(this).closest('form').serializeArray();
    for (const id of ids) {
        await $.post('server/shareLessonWithGroup.php', {
            groupId: groupId,
            lessonId: id['name']
        });
    }
    window.location.replace("groupLessons.php?id=" + groupId + "&name=" + groupName + "&is-admin=1");
}

$(document).ready(async function() {
    await displayLessons();
    $("#submit").click(shareLessons);
});