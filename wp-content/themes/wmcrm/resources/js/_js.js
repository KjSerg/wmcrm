import 'selectric';
import 'jquery-ui/ui/widgets/datepicker';
import {
    renderMain,
    showPreloader,
    openWindow,
    closeWindow,
    showMassage,
    isJsonString,
    hidePreloader,
    changeProjectStatus, renderMainInModal, sendRequest, bytesToMB, bytesToKB, copyToClipboard
} from "./_helpers";
import {setQiullText} from './_quill-init';
import Invite from "./Invite";
import Stopwatch from "./Stopwatch";
import Shadow from "./Shadow";
import CommentObserver from "./CommentObserver";
import checkingNotifications, {newMessageSoundPlay} from "./_check-notification";
import BulkEdit from "./BulkEdit";
import './_forms';
import './_profile';
import Autocomplete from "./Autocomplete";

let $doc = $(document);

export function initTriggers() {
    $doc.on('change', '.trigger-on-select', function (e) {
        let $this = $(this);
        let $option = $this.find('option:selected');
        if ($option.length === 0) return;
        let selector = $option.attr('data-selector');
        let val = $option.attr('data-val');
        let $selector = $doc.find(selector);
        if ($selector.length === 0) return;
        $selector.val(val);
    });
    $doc.on('change', '.submit-on-select', function (e) {
        $(this).closest('form').trigger('submit');
    });
    $doc.on('click', '.modal-open', function (e) {
        e.preventDefault();
        console.log(e)
        let $t = $(this);
        let href = $t.attr('href');
        if (href === undefined) return;
        let $el = $doc.find(href);
        if ($el.length === 0) return;
        openWindow($el);
    });
    $doc.on('click', '.close-window', function (e) {
        e.preventDefault();
        closeWindow();
    });
    $doc.on('change', '.show-on-change', function (e) {
        let $t = $(this);
        let elem = $t.attr('data-element');
        if (elem === undefined) return;
        let $el = $doc.find(elem);
        if ($el.length === 0) return;
        if ($t.prop('checked') === true) {
            $el.slideDown(500);
            $el.find('input[type="text"]').attr('required', 'required')
        } else {
            $el.slideUp(500);
            $el.find('input[type="text"]').removeAttr('required');
        }
    });
    $doc.on('focus', '.copy-on-change', function (e) {
        let $t = $(this);
        let isCopied = $t.attr('data-copied') !== undefined;
        if (!isCopied) {
            let $copiedElement = $t.clone();
            $copiedElement.insertAfter($t);
            $t.attr('data-copied', 'copied');
            $copiedElement.removeAttr('required');
        }
    });
}

export function initPlugins() {
    $('.selectric').selectric();
    $('input.date-input').datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd-mm-yy"
    });
}

$(document).ready(function () {
    checkingNotifications();
    initTriggers();
    initPlugins();
    $doc.on('click', '.project-button-action', function (e) {
        e.preventDefault();
        let $t = $(this);
        let isOpen = $t.hasClass('project-open');
        let id = $t.attr('data-id');
        changeProjectStatus({
            id: id,
            status: isOpen ? 'pending' : 'archive',
        });
        if (isOpen) {
            $t.removeClass('project-open');
            $t.addClass('project-close');
        } else {
            $t.addClass('project-open');
            $t.removeClass('project-close');
        }
    });
    $doc.on('click', '.link-js', function (e) {
        e.preventDefault();
        let $t = $(this);
        let url = $t.attr('href');
        if ($t.hasClass('open-in-modal') && !$t.closest('section').hasClass('project-section-children')) {
            renderMainInModal({
                url: url
            });
            return;
        }
        renderMain({
            url: url,
            addToHistory: true
        });
    });
    $doc.on('click', '.discussion-item', function (e) {
        e.preventDefault();
        let $t = $(this);
        let $title = $t.find('.discussion-item__title');
        let url = $title.attr('href');
        renderMain({
            url: url,
            addToHistory: true
        });
    });
    $doc.on('click', '.copy-link', function (e) {
        e.preventDefault();
        let $t = $(this);
        let url = $t.attr('href');
        copyToClipboard(url);
    });
    $doc.on('click', 'div.profile-head-user__avatar', function (e) {
        e.preventDefault();
        let $t = $(this);
        let $img = $t.find('img');
        if ($img.length === 0) return;
        let url = $img.attr('src');
        $doc.find('.window-main').html('<div class="window-main-image"><img src="' + url + '"  alt=""></div>');
        openWindow($doc.find('.window-main'));
    });
    $doc.on('click', '.text img', function (e) {
        e.preventDefault();
        let $t = $(this);
        let url = $t.attr('src');
        if (url === undefined) return;
        $doc.find('.window-main').html('<div class="window-main-image"><img src="' + url + '"  alt=""></div>');
        openWindow($doc.find('.window-main'));
    });
    $doc.on('click', '.header-notification-button', function (e) {
        e.preventDefault();
        let $t = $(this);
        $t.find('span').text(0);
    });
    $doc.on('click', '.vote-js', function (e) {
        e.preventDefault();
        let $t = $(this);
        let $form = $t.closest('form');
        let test = 0;
        $form.find('.vote-status-input').val('1');
        $form.find('input[type="checkbox"],input[type="radio"]').each(function () {
            if ($(this).prop('checked') === true) test = test + 1;
        });
        if (test > 0) {
            $form.removeClass('vote-error');
            $form.trigger('submit');
        } else {
            $form.addClass('vote-error');
        }
    });
    $doc.on('click', '.header-menu a', function (e) {
        e.preventDefault();
        let $t = $(this);
        let url = $t.attr('href');
        $('.header-menu li').removeClass('current-menu-item');
        $t.closest('li').addClass('current-menu-item');
        renderMain({
            url: url,
            addToHistory: true
        });
    });
    $doc.on('click', '.project-start', function (e) {
        e.preventDefault();
        let $t = $(this);
        let id = $t.attr('data-id');
        $t.addClass('not-active');
        showPreloader();
        $.ajax({
            type: 'POST',
            url: adminAjax,
            data: {
                action: 'starting_project',
                project_id: id,
            },
        }).done(function (r) {
            if (r) {
                if (isJsonString(r)) {
                    let res = JSON.parse(r);
                    if (res.msg !== '' && res.msg !== undefined) {
                        showMassage(res.msg);
                    }
                    if (res.type === 'success') {
                        if (!$doc.find('.header-timer').hasClass('active')) {
                            $doc.find('.header-timer').trigger('click');
                        }
                    }
                } else {
                    showMassage(r);
                }
                hidePreloader();
            }
        });
    });
    $doc.on('click', 'a[href^="http"]', function (e) {
        var $t = $(this);
        if (this.hostname !== window.location.hostname) {
            e.preventDefault();
            window.open($t.attr('href'), '_blank');
        }
    });
    $doc.on('click', '.comment-remove-js', function (e) {
        e.preventDefault();
        let $t = $(this);
        let id = $t.attr('data-id');
        showPreloader();
        $.ajax({
            type: 'POST',
            url: adminAjax,
            data: {
                action: 'remove_comment',
                comment_id: id,
            },
        }).done(function (r) {
            if (r) {
                if (isJsonString(r)) {
                    let res = JSON.parse(r);
                    if (res.msg !== '' && res.msg !== undefined) {
                        showMassage(res.msg);
                    }
                    if (res.type === 'success') {
                        $t.closest('.comment').remove();
                    } else {
                        $t.remove();
                    }
                } else {
                    showMassage(r);
                }
                hidePreloader();
            }
        });
    });
    $doc.on('input', 'input[type="tel"]', function () {
        $(this).val($(this).val().replace(/[A-Za-zА-Яа-яЁё]/, ''))
    });
    $doc.on('change', 'input.upload-files', function () {
        const t = this;
        const $t = $(t);
        const $form = $t.closest('form');
        let filesList = t.files;
        let HTML = "";
        if (filesList) {
            const l = filesList.length;
            for (let index = 0; index < l; index++) {
                const item = filesList[index];
                const name = item.name;
                let size = item.size;
                size = bytesToKB(size);
                let html = `<li>${name} (${size}KB)</li>`;
                HTML += html;
            }
        }
        $form.find('.form-files-result').html(HTML);
    });
    $doc.mouseup(function (e) {
        let div = $(".window-main.active, .modal-window.active, .dialog-window.active");
        if (!div.is(e.target)
            && div.has(e.target).length === 0) {
            if(!$doc.find('.ui-datepicker').is(':visible')) div.find('.close-window').trigger('click');
        }
    });
    const invite = new Invite();
    const stopwatch = new Stopwatch();
    const shadow = new Shadow();
    const commentObserver = new CommentObserver();
    const bulkEditor = new BulkEdit();
    const autocomplete = new Autocomplete();
});

function validateTime(time) {
    var timeRegex = /^([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/;
    return timeRegex.test(time);
}

window.onpopstate = (event) => {
    renderMain({
        url: document.location
    });
};