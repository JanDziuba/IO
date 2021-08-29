/**
 * Populuje div #lessons listą lekcji udostępnionych grupie.
 * Jeżeli użytkownik jest adminem grupy, dodaje też przycisk "usuń".
 */
 async function displayLessons() {
    try {
        const groupId = $("#title").data('groupId');
        const isAdmin = $("#title").data('isAdmin');
        const response = await $.get('server/listGroupSharedLessons.php', {
            groupId: groupId,
            limit: -1
        });
        
        const result = JSON.parse(response);
        console.log(result);
        if (result.result === 0) {
            let html = "";
            for (const lesson of result.data) {
                html += "<form class='shared-lesson' data-id=" + lesson['id'] + " data-group=" + groupId + ">";
                html += "<button class='shared-train' type='button' value='Trenuj'>" + lesson['name'] + "</button>";
                if (isAdmin)
                    html += "<button class='shared-delete' type='button' value='Usuń'> Usuń </button>";
                html += "</form>";
            }
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
 * Usuwa lekcję z listy udostępnianych lekcji.
 */
async function deleteLesson() {
    let group = $(this).closest('form').data('group');
    let id = $(this).closest('form').data('id');
    const response = await $.post('server/removeLessonSharing.php', {
        lessonId: id,
        groupId: group
    });
    $(this).closest('form').remove();
}

/**
 * Przekierowuje do treningu.
 */
async function trainLesson() {
    let id = $(this).closest('form').data('id');
    window.location.replace("trainings.php?id=" + id);
}

$(document).ready(async function() {
    await displayLessons();
    $(".shared-delete").click(deleteLesson);
    $(".shared-train").click(trainLesson);
});