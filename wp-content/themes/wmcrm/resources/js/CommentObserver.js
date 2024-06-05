import './_$isInViewPort';
import {sendRequest} from "./_helpers";

export default class CommentObserver {
    constructor() {
        this.selector = '[data-reading-id]';
        this.$doc = $(document);
        this.obsever();
        this.readingCommentsInViewport();
    }

    readingComment(id) {
        const _this = this;
        const res = sendRequest(adminAjax, {
            action: 'reading_discussion',
            id
        }, 'POST', false).then(function () {
            _this.$doc.find(`[data-reading-id="${id}"]`).removeClass('unread');
            _this.$doc.find(`[data-reading-id="${id}"]`).addClass('read');
            _this.$doc.find(`[data-reading-id="${id}"] .discussion-item__check.unread`).removeClass('unread');
            _this.$doc.find(`[data-reading-id="${id}"] .discussion-item__check`).addClass('read');
            _this.$doc.find(`[data-reading-id="${id}"]`).removeAttr('data-reading-id');
        });
        console.log(res);

    }

    obsever() {
        const _this = this;
        $(window).on('load resize scroll', function () {
            _this.readingCommentsInViewport();
        });
    }

    readingCommentsInViewport() {
        const _this = this;
        const $elements = _this.$doc.find(_this.selector);
        $elements.each(function () {
            const $t = $(this);
            if ($t.isInViewport()) {
                const id = $t.attr('data-reading-id');
                _this.readingComment(id);
            }
        });
    }
}