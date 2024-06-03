import {sendRequest} from './_helpers';
import './_$isInViewPort';

let $doc = $(document);
// $(window).on('load resize scroll', discussionObserver)

function discussionObserver() {
    let $items = $doc.find('[data-reading-id]').not('.read-discussion');
    console.log($items)
    $items.each(function () {
        let $item = $(this);
        let id = $item.attr('data-reading-id');
        if (id !== undefined && $item.isInViewport()) {
            let args = {
                action: 'reading_discussion',
                id: id
            };
            sendRequest(adminAjax, args).then((result) => {
                $item.removeAttr('data-reading-id');
                $item.addClass('read');
                $item.find('.discussion-item__check').removeClass('unread');
                $item.find('.discussion-item__check').addClass('read');
            }).catch((error) => {
                console.warn('Помилка:', error);
            });
        }
    });
}