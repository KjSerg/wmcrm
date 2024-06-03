import {sendRequest} from "./_helpers";
let $doc = $(document);
$doc.ready(function () {
    $doc.on('click', '.remove-avatar', function (e) {
        e.preventDefault();
        let $t = $(this);
        let $form = $t.closest('form');
        sendRequest(adminAjax, {
            action: 'remove_avatar'
        }).then((res) => {
            $form.removeClass('uploaded-avatar');
            if (res.avatar !== undefined) {
                $doc.find('.profile-head-user__avatar img').attr('src', res.avatar);
                $doc.find('.header-avatar img').attr('src', res.avatar);
                $doc.find('.avatar-modal-image img').attr('src', res.avatar);
            }
        }).catch((error) => {
            console.error('Помилка:', error);
        });
    });
    $doc.on('change', '.upload-avatar', function (e) {
        e.preventDefault();
        let $t = $(this);
        let $form = $t.closest('form');
        let val = $t.val();
        if (val) {
            $form.trigger('submit');
        }
    });
});