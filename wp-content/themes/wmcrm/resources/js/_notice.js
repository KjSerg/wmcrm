import {hidePreloader, isJsonString, showMassage} from "./_helpers";

$(document).ready(function () {
    getNotice();
    updateNotice();
    $(document).on('click', '.close-notice', function (e) {
        e.preventDefault();
        const $t = $(this);
        const id = $t.attr('data-id');
        console.log(id)
        if (id === undefined) {
            return;
        }
        $t.closest('div').slideUp();
        $.ajax({
            type: "POST",
            url: adminAjax,
            data: {
                action: 'remove_user_notice', id
            },
        }).done(function (r) {
            if (r) {
                $(document).find('.notifications').html(r);
            }
        });
    });
});

function updateNotice() {
    let minute = 60000;
    let time = minute * 60;
    setInterval(getNotice, time);
}

export function getNotice() {
    $.ajax({
        type: "POST",
        url: adminAjax,
        data: {
            action: 'get_user_notice'
        },
    }).done(function (r) {
        if (r) {
            $(document).find('.notifications').html(r);
        }
    });
}