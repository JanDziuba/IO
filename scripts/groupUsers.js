/**
 * Populuje div #user-list listą użytkowników należących do grupy.
 */
 async function displayUsers() {
    try {
        const groupId = $("#title").data('groupId');
        const response = await $.get('server/listGroupMembers.php', {
            groupId: groupId,
            limit: -1
        });
        
        const result = JSON.parse(response);
        console.log(result);
        if (result.result === 0) {
            let html = "";
            for (const user of result.data) {
                html += "<div class='shared-lesson' data-name=" + user['name'] + " data-group=" + groupId + ">";
                html += "<label>" + user['name'] + "</label>";
                html += "<button class='user-delete' value='Usuń'> Usuń </button>";
                html += "</div>";
            }
            $("#user-list").html(html);
        } else {
            // TODO: obsługa błędów
            throw 'Ludzie przecież nikogo tu nie ma';
        }
    } catch(error) {
        console.log(error);   
    }
}

/**
 * Usuwa użytkownika z grupy.
 */
async function deleteUser() {
    let groupId = $(this).closest('div').data('group');
    let id = $(this).closest('div').data('name');
    const response = await $.post('server/kickGroupMember.php', {
        groupId: groupId,
        userId: id
    });
    $(this).closest('div').remove();
}

$(document).ready(async function() {
    await displayUsers();
    $(document).on("click", ".user-delete", deleteUser);
});