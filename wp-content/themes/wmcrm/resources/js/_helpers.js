import initQuill, {setQiullText} from "./_quill-init";
import Invite from "./Invite";
import {initPlugins} from './_js';
import CommentObserver from "./CommentObserver";

let $doc = $(document);
let load = false;
let loading = false;
let parser = new DOMParser();

export function openWindow($window) {
    $window.addClass('active');
    $('body').addClass('open-window');
    if ($window.find('.close-window').length > 0) return;
    $window.append('<button class="close-window"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none">\n' +
        '<path d="M0.732457 15C0.3282 15 0.000106331 14.6719 0.000106331 14.2677C0.000106331 14.0743 0.0762708 13.8868 0.213953 13.7492L13.7478 0.215311C14.0349 -0.0717704 14.4977 -0.0717704 14.7848 0.215311C15.0719 0.502393 15.0719 0.965239 14.7848 1.25232L1.25096 14.7862C1.11328 14.9238 0.925798 15 0.732457 15Z" fill="white"/>\n' +
        '<path d="M14.2647 15C14.0714 15 13.8839 14.9238 13.7462 14.7862L0.212381 1.25232C-0.0747001 0.965239 -0.0747001 0.502393 0.212381 0.215311C0.499463 -0.0717704 0.962309 -0.0717704 1.24939 0.215311L14.7832 13.7492C15.0703 14.0362 15.0703 14.4991 14.7832 14.7862C14.6456 14.9238 14.4581 15 14.2647 15Z" fill="white"/>\n' +
        '</svg></button>');

}

export function closeWindow($window = $('.modal-window, .dialog-window, .window-main')) {
    $window.removeClass('active');
    $('body').removeClass('open-window');
    $doc.find('.window-main:not(.active)').html('');
    $doc.find('.report.window-main').remove();
}

export function isJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

export function showPreloader() {
    $('.preloader').addClass('active');
}

export function hidePreloader() {
    $('.preloader').removeClass('active');
}

export function showMassage(message) {
    // closeWindow();
    $('#dialog-js .dialog-title').html(message);
    openWindow($('#dialog-js'));
    setTimeout(function () {
        closeWindow($('#dialog-js'));
    }, 3000);
}

export function renderMain(args) {
    closeWindow();
    let url = args.url;
    let addToHistory = args.addToHistory || false;
    let $container = $doc.find('main.content');
    if (url === undefined) return;
    if (loading) return;
    $doc.find('body').addClass('loading');
    loading = true;
    showPreloader();
    let param = {
        type: 'GET',
        url: url
    };
    $.ajax(param).done(function (r) {
        hidePreloader();
        loading = false;
        if (r) {
            let $requestBody = $(parser.parseFromString(r, "text/html"));
            $container.html($requestBody.find('main.content').html());
            $doc.find('title').html($requestBody.find('title').html());
            if (addToHistory) history.pushState({}, "", url);
            $doc.find('body').removeClass('loading');
            initPlugins();
            initQuill();
            const invite = new Invite();
            let hash = undefined;
            if (typeof url === 'object') {
                hash = url.hash || undefined;
            } else {
                hash = url.split('#')[1];
            }
            if (hash !== undefined) {
                const $el = $doc.find('#' + hash);
                if ($el.length === 0) return;
                $('html, body').animate({
                    scrollTop: $el.offset().top
                }, 500)
            } else {
                $('html,body').animate({
                    scrollTop: 0
                }, 400);
            }
            const $menuItem = $doc.find(`.header-menu a[href="${url}"]`);
            if ($menuItem.length > 0) {
                $doc.find(`.header-menu li`).removeClass('current-menu-item');
                $menuItem.closest('li').addClass('current-menu-item');
            }
            const commentObserver = new CommentObserver();
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        renderMain({
            url: url
        });
    });
}

export function renderMainInModal(args) {
    closeWindow();
    let url = args.url;
    let $container = $doc.find('#window-main-js');
    $container.html('');
    if (url === undefined) return;
    if (loading) return;
    $doc.find('body').addClass('loading');
    loading = true;
    showPreloader();
    let param = {
        type: 'GET',
        url: url,
        data: {
            subtype: 'modal'
        }
    };
    $.ajax(param).done(function (r) {
        hidePreloader();
        loading = false;
        if (r) {
            let $requestBody = $(parser.parseFromString(r, "text/html"));
            $container.html(r);
            $doc.find('body').removeClass('loading');
            initPlugins();
            initQuill();
            const invite = new Invite();
            openWindow($container);
            const commentObserver = new CommentObserver();
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR)
        console.log(textStatus)
        console.log(errorThrown)
        renderMainInModal({
            url: url
        });
    });
}

export function renderInModalHTML(html) {
    let $container = $doc.find('#window-main-js');
    $container.html('');
    $container.html(html);
    $doc.find('body').removeClass('loading');
    initPlugins();
    initQuill();
    const invite = new Invite();
    openWindow($container);
    const commentObserver = new CommentObserver();
}

export function changeProjectStatus(args) {
    args.action = 'change_project_status';
    showPreloader();
    $.ajax({
        type: 'POST',
        url: adminAjax,
        data: args,
    }).done(function (r) {
        if (r) {
            if (isJsonString(r)) {
                let res = JSON.parse(r);
                if (res.comment_html !== '' && res.comment_html !== undefined) {
                    $doc.find('.section-comments-list').prepend(res.comment_html);
                }
                if (res.msg !== '' && res.msg !== undefined) {
                    showMassage(res.msg);
                }
                if (res.comments_html !== '' && res.comments_html !== undefined) {
                    $doc.find('.section-comments-content').html(res.comments_html);
                }
                if (res.url !== undefined) {
                    showPreloader();
                    setTimeout(function () {
                        window.location.href = res.url;
                        return;
                    }, 3100);
                }
                if (res.button_text !== undefined) {
                    $doc.find('.project-button-action').text(res.button_text);
                }
            } else {
                showMassage(r);
            }
        }
        hidePreloader();
    });
}

export const isObjectEmpty = (objectName) => {
    return JSON.stringify(objectName) === "{}";
};

export function isElementInViewport(el) {
    if (typeof jQuery === "function" && el instanceof jQuery) {
        el = el[0];
    }
    let rect = el.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

export function sendRequest(url, args = {}, method = 'POST', isShowPreloader = true) {
    return new Promise((resolve, reject) => {
        $doc.find('body').addClass('loading');
        if (isShowPreloader) showPreloader();
        let param = {
            type: method,
            url: url,
            success: function (r) {
                hidePreloader();
                $doc.find('body').removeClass('loading');
                if (r) {
                    if (isJsonString(r)) {
                        resolve(JSON.parse(r));
                    } else {
                        resolve(r);
                    }
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                hidePreloader();
                console.log(jqXHR, textStatus, errorThrown);
                reject(errorThrown);
            }
        };
        if (args) param.data = args;
        $.ajax(param);
    });
}

export function getCurrentDate() {
    let today = new Date();
    let day = today.getDate();
    let month = today.getMonth() + 1;
    let year = today.getFullYear();
    day = (day < 10) ? "0" + day : day;
    month = (month < 10) ? "0" + month : month;
    return day + "-" + month + "-" + year;
}

export function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        let date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + encodeURIComponent(value) + expires + "; path=/; Secure; SameSite=None";
}

export function getCookie(name) {
    let matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}

export function removeArrayElement(element, array) {
    array = array.filter(function (item) {
        return item !== element;
    });
    return array;
}

export const bytesToMB = (bytes) => {
    return (bytes / (1024 * 1024)).toFixed(2);
}
export const bytesToKB = (bytes) => {
    return Math.floor(bytes/1000);
}

export function copyToClipboard(text) {
    const tempInput = document.createElement('textarea');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    tempInput.setSelectionRange(0, 99999);
    document.execCommand('copy');
    document.body.removeChild(tempInput);
    console.log('Скопійовано в буфер обміну: ' + text);
    showMassage('Copied 🖇️');
}