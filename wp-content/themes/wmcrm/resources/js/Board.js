import Sortable from "sortablejs";
import {hidePreloader, showPreloader} from "./_helpers";

let parser = new DOMParser();
export default class Board {
    constructor() {
        this.init();
        this.eventListener();
    }

    eventListener() {
        const t = this;
        $(document).on('input', '.board-wrapper-head-form input', function (e) {
            const $i = $(this);
            const val = $i.val().trim();
            const $form = $i.closest('form');
            const status = $form.find('[name="post_status"]').val();
            if (status === undefined) return;
            showPreloader();
            let param = {
                type: $form.attr('method'),
                url: $form.attr('action'),
                data: $form.serialize()
            };
            $.ajax(param).done(function (r) {
                hidePreloader();
                if (r) {
                    let $requestBody = $(parser.parseFromString(r, "text/html"));
                    $(document).find(`.board-column[data-status="${status}"]`).html($requestBody.find(`.board-column[data-status="${status}"]`).html());
                    $(document).find(`.board-wrapper-footer__item[data-status="${status}"]`).html($requestBody.find(`.board-wrapper-footer__item[data-status="${status}"]`).html());
                }
            });
        });
        $(document).on('click', '.get-next-board-projects', function (e) {
            e.preventDefault();
            const $t = $(this);
            const href = $t.attr('href');
            const status = $t.attr('data-status');
            $t.remove();
            showPreloader();
            let param = {
                type: 'GET',
                url: href
            };
            $.ajax(param).done(function (r) {
                hidePreloader();
                if (r) {
                    let $requestBody = $(parser.parseFromString(r, "text/html"));
                    $(document).find(`.board-column[data-status="${status}"]`).append($requestBody.find(`.board-column[data-status="${status}"]`).html());
                    $(document).find(`.board-wrapper-footer__item[data-status="${status}"]`).html($requestBody.find(`.board-wrapper-footer__item[data-status="${status}"]`).html());
                    t.removeDuplicates();
                }
            });
        });
    }

    onEnd(evt) {
        let sortedItems = [];
        let _status = false;
        [...evt.to.children].forEach(function (item) {
            const id = item.id;
            const status = item.closest('.board-column').getAttribute('data-status');
            _status = status;
            item.classList.remove('archive');
            item.classList.remove('pending');
            item.classList.remove('publish');
            item.classList.add(status);
            item.setAttribute('data-status', status);
            sortedItems.push(Number(id));
        });
        sendChanges(sortedItems, _status);
    }


    initBoard() {
        document.querySelectorAll(".board-column").forEach(column => {
            new Sortable(column, {
                group: {
                    name: 'shared'
                },
                animation: 150,
                sort: false,
                ghostClass: "sortable-placeholder",
                onEnd: this.onEnd
            });
        });
    }

    removeDuplicates(selector = ".board-item") {
        const items = document.querySelectorAll(selector);
        const seenIds = new Set();
        items.forEach(item => {
            const itemId = item.id;
            if (seenIds.has(itemId)) {
                item.remove();
            } else {
                seenIds.add(itemId);
            }
        });
    }

    init() {
        $(document).ready(() => this.initBoard());
    }
}

function sendChanges(sortedItems, status) {
    if (!status) return;
    if (sortedItems.length === 0) return;
    let param = {
        type: 'POST',
        url: adminAjax,
        data: {
            action: 'update_status_projects',
            status, sortedItems
        }
    };
    showPreloader();
    $.ajax(param).done(function (r) {
        hidePreloader();
        console.log(r);
    });
}

const b = new Board();
b.init();
