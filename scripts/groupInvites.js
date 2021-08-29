/**
 * Populuje div #invite-list listą próśb o dołączenie do grupy.
 */
 async function displayInvites() {
    try {
        const groupId = $("#title").data('groupId');
        const response = await $.get('server/listGroupJoinRequests.php', {
            groupId: groupId,
            limit: -1
        });
        
        const result = JSON.parse(response);
        console.log(result);
        console.log(result.data)
        if (result.result === 0) {
            let html = "";
            for (const invite of result.data) {
                html += "<div class='invites' data-id=" + invite['id'] + " data-name=" + invite['userName'] + ">";
                html += "<label>" + invite['userName'] + "</label>";
                html += "<button class='invite-accept' value='Akceptuj'> Akceptuj </button>";
                html += "<button class='invite-deny' value='Odrzuć'> Odrzuć </button>";
                html += "</div>";
            }
            $("#invite-list").html(html);
        } else {
            // TODO: obsługa błędów
            throw 'Ludzie przecież nikogo tu nie ma';
        }
    } catch(error) {
        console.log(error);   
    }
}

/**
 * Akceptuje prośbę.
 */
async function accept() {
    let id = $(this).closest('div').data('id');
    console.log(id);
    const response = await $.post('server/acceptGroupJoinRequest.php', {
        joinRequestId: id
    });
    $(this).closest('div').remove();

    let groupId = $("#title").data('group-id');
    let name = $(this).closest('div').data('name');
    let html = "<div class='shared-lesson' data-name=" + name + " data-group=" + groupId + ">";
    html += "<label>" + name + "</label>";
    html += "<button class='user-delete' value='Usuń'> Usuń </button>";
    html += "</div>";
    $("#user-list").append(html);
}

/**
 * Odrzuca prośbę.
 */
async function deny() {
    let id = $(this).closest('div').data('id');
    const response = await $.post('server/denyGroupJoinRequest.php', {
        joinRequestId: id
    });
    $(this).closest('div').remove();
}

$(document).ready(async function() {
    await displayInvites();
    $(".invite-accept").click(accept);
    $(".invite-deny").click(deny);
});