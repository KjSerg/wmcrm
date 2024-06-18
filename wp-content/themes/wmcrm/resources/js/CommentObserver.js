import './_$isInViewPort';
import {sendRequest, isJsonString} from "./_helpers";

export default class CommentObserver {
    constructor() {
        this.requestQueue = [];
        this.readedCommentID = [];
        this.isRequesting = false;
        this.selector = '[data-reading-id]';
        this.$doc = $(document);
        this.obsever();
        this.readingCommentsInViewport();
    }

    readingComment(id) {
        const _this = this;
        const isRequesting = _this.isRequesting;
        if (isRequesting || _this.readedCommentID.includes(id)) {
            const test = !_this.requestQueue.includes(id) || !_this.readedCommentID.includes(id);
            if(!_this.requestQueue.includes(id)) {
                _this.requestQueue.push(id);
            }
            console.log(_this.requestQueue)
            console.log(_this.readedCommentID)
            return;
        }
        _this.readedCommentID.push(id);
        _this.isRequesting = true;
        sendRequest(adminAjax, {
            action: 'reading_discussion',
            id
        }, 'POST', false).then(function (r) {
            _this.$doc.find(`[data-reading-id="${id}"]`).removeClass('unread');
            _this.$doc.find(`[data-reading-id="${id}"]`).addClass('read');
            _this.$doc.find(`[data-reading-id="${id}"] .discussion-item__check.unread`).removeClass('unread');
            _this.$doc.find(`[data-reading-id="${id}"] .discussion-item__check`).addClass('read');
            _this.$doc.find(`[data-reading-id="${id}"]`).removeAttr('data-reading-id');
            _this.isRequesting = false;
            if (_this.requestQueue.length > 0) {
                _this.readingComment(_this.requestQueue.shift());
            }
        });
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