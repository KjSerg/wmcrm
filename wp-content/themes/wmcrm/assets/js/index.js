let doc = document;
let $doc = $(doc);
let load = false;
let loading = false;
let w = window;
let $w = $(w);
let parser = new DOMParser();
let $body = $('body');
let collectionUsers = [];
let quill = null;
let costs = {};
let interval = null;

$doc.ready(function () {
    initPlugins();
    initTriggers();
    $doc.on('submit', '.form-js', function (e) {
        e.preventDefault();
        let $form = $(this);
        let this_form = $form.attr('id');
        let test = true,
            thsInputs = $form.find('input, textarea'),
            $select = $form.find('select[required]');
        let $address = $form.find('input.address-js[required]');
        $select.each(function () {
            let $ths = $(this);
            let $label = $ths.closest('.form-group');
            let val = $ths.val();
            console.log(val);
            if (Array.isArray(val) && val.length === 0) {
                console.log(1);
                test = false;
                $label.addClass('error');
            } else {
                console.log(2);
                $label.removeClass('error');
                if (val === null || val === undefined) {
                    console.log(3);
                    test = false;
                    $label.addClass('error');
                }
            }
        });
        thsInputs.each(function () {
            let thsInput = $(this),
                $label = thsInput.closest('.form_element'),
                thsInputType = thsInput.attr('type'),
                thsInputVal = thsInput.val().trim(),
                inputReg = new RegExp(thsInput.data('reg')),
                inputTest = inputReg.test(thsInputVal);
            if (thsInput.attr('required')) {
                if (thsInputVal.length <= 0) {
                    test = false;
                    thsInput.addClass('error');
                    $label.addClass('error');
                    thsInput.focus();
                    if (thsInputType === 'file') {
                        $form.find('.cabinet-item__photo-item').eq(0).addClass('error');
                        $('html, body').animate({
                            scrollTop: $form.find('.cabinet-item__photo-item').eq(0).offset().top
                        });
                    }
                } else {
                    thsInput.removeClass('error');
                    $label.removeClass('error');
                    if (thsInput.data('reg')) {
                        if (inputTest === false) {
                            test = false;
                            thsInput.addClass('error');
                            $label.addClass('error');
                            thsInput.focus();
                        } else {
                            thsInput.removeClass('error');
                            $label.removeClass('error');
                        }
                    }
                    if (thsInputType === 'file') {
                        $form.find('.cabinet-item__photo-item').eq(0).removeClass('error');
                    }
                }
            }
        });
        let $password = $form.find('input[name="password"]');
        let $passwordRepeat = $form.find('input[name="repeat_password"]');
        let $passwordOld = $form.find('input[name="old_password"]');
        let $passwordNew = $form.find('input[name="new_password"]');
        if (!$form.hasClass('login-form')) {
            if ($password.length > 0 && $passwordRepeat.length > 0) {
                if ($password.val() !== $passwordRepeat.val()) {
                    $password.addClass('error');
                    $passwordRepeat.addClass('error');
                    return;
                }
                if (!isValidPassword($password.val())) {
                    showMassage(errorPswMsg);
                    $password.addClass('error');
                    $passwordRepeat.addClass('error');
                    return;
                }
                $password.removeClass('error');
                $passwordRepeat.removeClass('error');
            } else if ($password.length > 0 && $password.val().length > 0) {
                if (!isValidPassword($password.val())) {
                    showMassage(errorPswMsg);
                    $password.addClass('error');
                    $passwordRepeat.addClass('error');
                    return;
                }
                $password.removeClass('error');
                $passwordRepeat.removeClass('error');
            }
            if ($passwordOld.length > 0 && $passwordNew.length > 0) {
                if (!isValidPassword($passwordNew.val())) {
                    showMassage(errorPswMsg);
                    $passwordNew.addClass('error');
                    return;
                }
                $passwordNew.removeClass('error');
            }
        }
        let $inp = $form.find('input[name="consent"]');
        if ($inp.length > 0) {
            if ($inp.prop('checked') === false) {
                $inp.closest('.form-consent').addClass('error');
                return;
            }
            $inp.closest('.form-consent').removeClass('error');
        }
        if ($address.length > 0) {
            let addressTest = true;
            $address.each(function (index) {
                let $el = $(this);
                let val = $el.val() || '';
                let selected = $el.attr('data-selected') || '';
                if (selected.trim() !== val.trim()) {
                    test = false;
                    addressTest = false;
                    $el.addClass('error');
                } else {
                    $el.removeClass('error');
                }
                if (val.length === 0) {
                    test = false;
                    $el.addClass('error');
                }
            });
            if (!addressTest) showMassage(locationErrorString);
        }
        if ($form.hasClass('comment-form')) {
            if ($form.find('.value-field').val().trim().length === 0) return;
        }
        if (test) {
            let thisForm = document.getElementById(this_form);
            let formData = new FormData(thisForm);
            showPreloader();
            $.ajax({
                type: $form.attr('method'),
                url: adminAjax,
                processData: false,
                contentType: false,
                data: formData,
            }).done(function (r) {
                $form.trigger('reset');
                if (quill !== null) quill.setText('');
                if (r) {
                    if (isJsonString(r)) {
                        let res = JSON.parse(r);
                        if ($form.hasClass('login-form') && res.type === 'success' || res.is_reload === 'true') {
                            window.location.reload();
                            return;
                        }
                        if (res.comment_html !== '' && res.comment_html !== undefined) {
                            $doc.find('.section-comments-list').prepend(res.comment_html);
                        }
                        if (res.comments_html !== '' && res.comments_html !== undefined) {
                            $doc.find('.section-comments-content').html(res.comments_html);
                        }
                        if (res.comment_html_update !== '' && res.comment_html_update !== undefined) {
                            let comment_id = res.comment_id;
                            $doc.find('#comment-' + comment_id).replaceWith(res.comment_html_update);
                        }
                        if (res.msg !== '' && res.msg !== undefined) {
                            showMassage(res.msg);
                        }
                        if (res.url !== undefined) {
                            showPreloader();
                            renderMain({url: res.url, addToHistory: true});
                        }
                    } else {
                        showMassage(r);
                    }
                }
                hidePreloader();
            });
        }
    });
    $doc.on('click', '.next-post-link', function (e) {
        e.preventDefault();
        let $button = $(this);
        let href = $button.attr('href');
        appendContainer(href);
    });
    $doc.on('submit', '.filter-project-form', function (e) {
        e.preventDefault();
        let $form = $(this);
        let url = $form.attr('action');
        let serialize = $form.serialize();
        renderContainer(url + '?' + serialize);
    });
    $doc.on('keypress', '.projects-head__input', function (e) {
        let $this = $(this);
        if (e.key === 'Enter') $this.closest('form').trigger('submit');
    });
    $doc.on('click', '.invite', function (e) {
        e.preventDefault();
        let $t = $(this);
        let userRel = $t.attr('rel');
        if (userRel === undefined) return;
        renderMain({
            url: projectsUrl + '?performer=' + userRel,
            addToHistory: true
        })
    });
    $doc.on('click', '.link-js', function (e) {
        e.preventDefault();
        let $t = $(this);
        let url = $t.attr('href');
        renderMain({
            url: url,
            addToHistory: true
        })
    });
    $doc.on('click', 'a[href^="http"]', function (e) {
        var $t = $(this);
        if (this.hostname !== window.location.hostname) {
            e.preventDefault();
            console.log(this)
            console.log(this.hostname)
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
    $doc.on('click', '.comment-change-js', function (e) {
        e.preventDefault();
        let $this = $(this);
        let id = $this.attr('data-id');
        let $wrapper = $this.closest('.comment');
        let $text = $wrapper.find('.comment-content');
        let html = $text.html();
        let $html = $(html);
        $html.find('.invite').removeAttr('data-user-id');
        html = $html.html();
        html = html.replaceAll('<span class="invite">', '@[');
        html = html.replaceAll('</span>', ']@');
        $doc.find('#editor').html(html);
        $doc.find('.ql-toolbar').remove();
        $doc.find('#editor').removeClass('ql-container').removeClass('ql-snow');
        $doc.find('.comment-field-id').val(id);
        initQuill();
        $('html,body').animate({
            scrollTop: $doc.find('#editor').offset().top - ($doc.find('.header').outerHeight() + 50)
        }, 400);
    });
    $doc.on('keypress', '#editor', function (e) {
        let currentTarget = e.currentTarget;
        let $currentTarget = $(currentTarget);
        let $form = $currentTarget.closest('form');
        if (e.key === '@') {
            $form.addClass('open-users-select');
            $form.find('.select-user-quill-js').selectric('open');
        }
    });
    $doc.on('change', '.select-user-quill-js', function (e) {
        let $t = $(this);
        let val = $t.val();
        let name = $t.find('option:selected').text().trim();
        var currentIndex = quill.getSelection().index;
        console.log(currentIndex)
        $doc.find('.open-users-select').removeClass('open-users-select');
        quill.insertText(currentIndex, '[' + name + ']@');
    });
    $doc.on('click', '.costs-button', function (e) {
        e.preventDefault();
        let $t = $(this);
        let ID = $t.attr('data-id');
        if ($t.hasClass('play')) {
            startContinueCostTime(ID);
            $t.removeClass('play');
            $t.addClass('pause');
            runTick(ID);
        } else {
            $t.addClass('play');
            $t.removeClass('pause');
            clearInterval(interval);
            stopCostTime(ID);
        }
    });
    $doc.mouseup(function (e) {
        var div = $(".text-editor-list");
        if (!div.is(e.target)
            && div.has(e.target).length === 0) {
            div.closest('form').removeClass('open-users-select');
        }
    });
});

function getCurrentTimestamp() {
    return Date.now();
}

function getTimestampInSeconds() {
    return Math.floor(Date.now() / 1000);
}

function runTick(ID) {
    interval = setInterval(function () {
        let unixTime = getCurrentTimestamp();
        let sum = costs[ID].sum;
        sum = sum + 1000;
        costs[ID].sum = sum;
        let $button = $doc.find('.costs-button[data-id="' + ID + '"]');
        if ($button.length > 0) {
            let time = convertMillisecondsToTime(sum);
            $button.find('.costs-button-hour').text(time.hours);
            $button.find('.costs-button-minutes').text(time.minutes);
            $button.find('.costs-button-seconds').text(time.seconds);
            $doc.find('.costs-sum[data-id="' + ID + '"]').text(time.hours + ':' + time.minutes);
        }
        saveCostsInStorage();
    }, 1000);
}

function stopCostTime(ID) {
    let unixTime = getCurrentTimestamp();
    if (costs[ID] === undefined) {
        showMassage('Error');
    } else {
        let el = costs[ID];
        let stops = el.stops;
        let lastIndex = stops.length - 1;
        costs[ID].status = 'stop';
        if (stops.length > 0) {
            stops[lastIndex].stop = unixTime;
            costs[ID].stops = stops;
        }
    }
    console.log(costs)
    saveCostsInStorage();
}

function convertMillisecondsToTime(milliseconds) {
    var date = new Date(milliseconds);
    var hours = date.getUTCHours();
    var minutes = date.getUTCMinutes();
    var seconds = date.getUTCSeconds();
    hours = (hours < 10) ? "0" + hours : hours;
    minutes = (minutes < 10) ? "0" + minutes : minutes;
    seconds = (seconds < 10) ? "0" + seconds : seconds;
    return {
        hours: hours,
        minutes: minutes,
        seconds: seconds,
    };
}

function startContinueCostTime(ID) {
    let unixTime = getCurrentTimestamp();
    if (costs[ID] === undefined) {
        costs[ID] = {
            sum: 0,
            status: 'start',
            stops: [
                {
                    start: unixTime,
                    stop: 0
                }
            ]
        }
    } else {
        costs[ID].stops.push(
            {
                start: unixTime,
                stop: 0
            }
        );
    }
    console.log(costs);
    saveCostsInStorage();
}

function getCurrentDate() {
    var today = new Date();
    var day = today.getDate();
    var month = today.getMonth() + 1;
    var year = today.getFullYear();
    day = (day < 10) ? "0" + day : day;
    month = (month < 10) ? "0" + month : month;
    return day + "-" + month + "-" + year;
}

function saveCostsInStorage() {
    localStorage.setItem('costs', JSON.stringify(costs));
    localStorage.setItem('costs-date', getCurrentDate());
}

function initUsersInText() {
    setCollectionUsers();
    outputUserData();
}

function outputUserData() {
    if (collectionUsers.length === 0) return;
    let data = {
        action: 'get_users_data',
        users: collectionUsers
    };
    $.ajax({
        type: "POST",
        url: adminAjax,
        data: data,
    }).done(function (r) {
        if (isJsonString(r)) {
            let res = JSON.parse(r);
            let array = res.array;
            if (array) {
                array.forEach(function (element) {
                    var userName = element.user_name;
                    var userLink = element.user_link;
                });
            }
        } else {
            showMassage(r);
        }
    });
}

function setCollectionUsers() {
    let $elem = $doc.find('.invite');
    $elem.each(function () {
        let $t = $(this);
        let userRel = $t.attr('data-rel');
        if (!collectionUsers.includes(userRel)) collectionUsers.push(userRel);
    });
}

function initTriggers() {
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
}

function initPlugins() {
    $doc.find('.selectric').selectric();
    AOS.init({
        once: true,
    });
    initQuill();
    $doc.find(".section-project-status-archive, .section-project-status-open").sortable({
        connectWith: ".project-status__item",
        stop: updateSortableCallback
    }).disableSelection();
}

function updateSortableCallback(event, ui) {
    let target = ui.item;
    let $target = $(target);
    let $container = $target.closest('.section-project-status');
    let id = $container.attr('data-id');
    let $el = $container.find('.project-rabbit');
    let $wrapper = $el.closest('.project-status__item');
    let isArchive = $wrapper.hasClass('section-project-status-archive');
    changeProjectStatus({
        id: id,
        status: isArchive ? 'archive' : 'publish',
    });
}

function changeProjectStatus(args) {
    args.action = 'change_project_status';
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
            } else {
                showMassage(r);
            }
        }
        hidePreloader();
    });
}

function initQuill() {
    if (quill !== null) quill = null;
    if ($doc.find('#editor').length === 0) return;

    let html = $doc.find('#editor').html().trim();
    if(html.length > 0){
        let $html = $(html);
        $html.find('.invite').removeAttr('data-user-id');
        html = $html.html();
        html = html.replaceAll('<span class="invite">', '@[');
        html = html.replaceAll('</span>', ']@');
        $doc.find('#editor').html(html);
    }
    quill = new Quill('#editor', {
        theme: 'snow',
    });
    quill.on('text-change', (delta, oldDelta, source) => {
        let val = quill.getSemanticHTML();
        $doc.find('.value-field').val(val);
    });

}

function renderContainer(url) {
    let $container = $doc.find('.container-js');
    let $pagination = $doc.find('.pagination-js');
    let $postsCounter = $doc.find('.found-posts');
    if (url === undefined) return;
    if (loading) return;
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
            $container.html($requestBody.find('.container-js').html());
            $pagination.html($requestBody.find('.pagination-js').html());
            $postsCounter.html($requestBody.find('.found-posts').html());
        } else {
            $pagination.html('');
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        renderContainer(url);
    });
}

function renderMain(args) {

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
            initPlugins();
            if (addToHistory) history.pushState({}, "", url);
            $doc.find('body').removeClass('loading');
            $('html,body').animate({
                scrollTop: 0
            }, 400);
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        renderMain({
            url: url
        });
    });
}

window.onpopstate = (event) => {
    renderMain({
        url: document.location
    });
};

function appendContainer(href) {
    let $container = $doc.find('.container-js');
    let $pagination = $doc.find('.pagination-js');
    if (href === undefined) return;
    if (loading) return;
    loading = true;
    showPreloader();
    let hrefHasType = href.indexOf('next_project_page');
    let param = {
        type: 'GET',
        url: href
    };
    if (hrefHasType === -1) {
        param.data = {type: 'next_project_page'};
    }
    $.ajax(param).done(function (r) {
        hidePreloader();
        loading = false;
        if (r) {
            let $requestBody = $(parser.parseFromString(r, "text/html"));
            $container.append($requestBody.find('.container-js').html());
            $pagination.html($requestBody.find('.pagination-js').html());
        } else {
            $pagination.html('');
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        appendContainer(href);
    });
}

function isJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

function showPreloader() {
    $('.preloader').addClass('active');
}

function hidePreloader() {
    $('.preloader').removeClass('active');
}

function showMassage(message) {
    $('#dialog-js .dialog-title').html(message);
    openWindow($('#dialog-js'));
    setTimeout(function () {
        closeWindow($('#dialog-js'));
    }, 3000);
}

function isValidPassword(password) {
    let regexp = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,20}$/;
    return password.match(regexp);
}