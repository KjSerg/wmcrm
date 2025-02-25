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
        this.showComment();
    }

    getQueryParams() {
        let params = {};
        let queryString = window.location.search.substring(1);
        let queryArray = queryString.split("&");

        for (let i = 0; i < queryArray.length; i++) {
            let pair = queryArray[i].split("=");
            let key = decodeURIComponent(pair[0]);
            let value = decodeURIComponent(pair[1] || '');
            params[key] = value;
        }

        return params;
    }

    getHash() {
        return window.location.hash.substring(1);
    }

    highlightText(element, textToHighlight) {
        $(element).html(function (_, html) {
            let regex = new RegExp('(' + textToHighlight + ')', 'gi');
            return html.replace(regex, '<mark>$1</mark>');
        });
    }

    showComment() {
        const _this = this;
        const hash = _this.getHash();
        const params = _this.getQueryParams();
        if (hash === undefined) return;
        if (hash === '') return;
        const $el = $(document).find('#' + hash);
        if ($el.length === 0) return;
        $el.addClass('showing-element');
        setTimeout(function () {
            $el.removeClass('showing-element');
        }, 5000);
        if (params.string === undefined) return;
        _this.highlightText('.content', params.string);
    }

    readingComment(id) {
        const _this = this;
        const isRequesting = _this.isRequesting;
        if (isRequesting || _this.readedCommentID.includes(id)) {
            const test = !_this.requestQueue.includes(id) || !_this.readedCommentID.includes(id);
            if (!_this.requestQueue.includes(id)) {
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