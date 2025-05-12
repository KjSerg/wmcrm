import {hidePreloader, isJsonString, showMassage} from "./_helpers";

$(document).ready(function () {
    getNotice();
    updateNotice();
    $(document).on('click', '.close-notice', function (e) {
        e.preventDefault();
        const $t = $(this);
        const id = $t.attr('data-id');
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
            $t.closest('div').remove();
            if (r) {
                $(document).find('.notifications').html(r);
                $(document).find('.notifications > *').show();
            }
        });
    });

});
document.addEventListener('visibilitychange', () => {
    console.log(document.visibilityState);
    if (document.visibilityState === 'visible') {
        getNotice();
    }
});

function updateNotice() {
    let time = 60000;
    setInterval(getNotice, time);
}

export function getNotice() {
    console.log('getNotice')
    $.ajax({
        type: "POST",
        url: adminAjax,
        data: {
            action: 'get_user_notice'
        },
    }).done(function (r) {
        if (r) {
            $(document).find('.notifications').html(r);
            $(document).find('.notifications > *').slideDown();
        }
    });
}