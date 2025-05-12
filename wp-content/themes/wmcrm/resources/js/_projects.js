import 'selectric';
import {showPreloader, hidePreloader, isJsonString, showMassage, closeWindow} from "./_helpers";
import CommentObserver from "./CommentObserver";
import {initPlugins} from "./_js";
import Invite from "./Invite";
import {isValidForm} from "./_forms";
import {initProjectQuill, setProjectQuillText} from './_quill-init';

let $doc = $(document);
let load = false;
let loading = false;
let parser = new DOMParser();
let $body = $('body');

$doc.ready(function () {
    $doc.on('click', '.project-item-status', function (e) {
        e.preventDefault();
        let $this = $(this);
        let status = $this.attr('data-status');
        let id = $this.attr('data-id');
        let $wrapper = $this.closest('.project-item-statuses');
        let $elements = $wrapper.find('.project-item-status');
        let $showed = $wrapper.find('.project-item-status').not('.active');
        let $active = $wrapper.find('.project-item-status.active');
        let activeStatus = $active.attr('data-status');
        if ($wrapper.hasClass('active')) {
            $wrapper.removeClass('active');
            $showed.slideUp();
        } else {
            $wrapper.addClass('active');
            $showed.slideDown();
        }
        if (!$this.hasClass('active')) {
            if (activeStatus !== status) {
                $active.attr('data-status', status);
                $active.attr('style', $this.attr('style'));
                $active.text($this.text());
                if (status === undefined) return;
                if (id === undefined) return;
                showPreloader();
                $.ajax({
                    type: "POST",
                    url: adminAjax,
                    data: {
                        action: 'change_project_status',
                        status, id
                    },
                }).done(function (r) {
                    if (r) {
                        if (isJsonString(r)) {
                            let res = JSON.parse(r);
                            if (res) {
                                if (res.msg !== undefined && res.msg !== '') {
                                    showMassage(res.msg);
                                }
                            }
                        } else {
                            showMassage(r);
                        }
                    }
                    hidePreloader();
                });
            }
        }
    });
    $doc.on('click', '.project-colors__item', function (e) {
        e.preventDefault();
        let $this = $(this);
        let isActive = $this.hasClass('active');
        let $wrapper = $this.closest('.project-colors');
        let $active = $wrapper.find('.project-colors-active');
        let $list = $wrapper.find('.project-colors-list');
        let id = $this.attr('data-id');
        let tagID = $this.attr('data-tag-id');
        let color = $this.attr('data-color');
        $wrapper.find('.project-colors__item').removeClass('active');
        $wrapper.find('.project-colors__item').text('+');
        if (isActive) {
            $this.removeClass('active');
            $this.text('+');
            $active.css('background-color', '#fff');
        } else {
            $this.addClass('active');
            $this.text('✕');
            if (color !== undefined) $active.css('background-color', color);
        }
        $list.slideUp();
        console.table(id, tagID)
        if (id === undefined) return;
        if (tagID === undefined) return;
        $.ajax({
            type: "POST",
            url: adminAjax,
            data: {
                action: 'change_color_tag', id, tagID,
                'type': isActive ? 'remove' : 'add'
            },
        }).done(function (r) {
            hidePreloader();
            if (r) {
                if (isJsonString(r)) {
                    const res = JSON.parse(r);
                    if (res) {
                        console.log(res.color)
                        if (res.msg !== undefined && res.msg !== '') {
                            showMassage(res.msg);
                        }
                        if (res.color !== undefined && res.color !== '') {
                            $active.css('background-color', res.color);
                        }
                    }
                } else {
                    showMassage(r);
                }
            }
        });
    })
    $doc.on('click', '.next-post-link', function (e) {
        e.preventDefault();
        let $button = $(this);
        let href = $button.attr('href');
        appendContainer(href, $button);
    });
    $doc.on('submit', '.filter-project-form', function (e) {
        e.preventDefault();
        let $form = $(this);
        let url = $form.attr('action');
        let serialize = $form.serialize();
        renderContainer(url + '?' + serialize);
    });
    $doc.on('click', '.save-preset', function (e) {
        e.preventDefault();
        const $t = $(this);
        const $form = $t.closest('form');
        if (isValidForm($form)) {
            let this_form = $form.attr('id');
            let thisForm = document.getElementById(this_form);
            let formData = new FormData(thisForm);
            formData.append('type', 'preset');
            showPreloader();
            $.ajax({
                type: $form.attr('method'),
                url: adminAjax,
                processData: false,
                contentType: false,
                data: formData,
            }).done(function (r) {
                if (r) {
                    if (isJsonString(r)) {
                        let res = JSON.parse(r);
                        if (res.presets_select_html !== undefined && res.presets_select_html !== '') {
                            $doc.find('.presets-wrapper').html(res.presets_select_html);
                            initPlugins();
                        }
                    } else {
                        showMassage(r);
                    }
                }
                hidePreloader();
                const invite = new Invite();
            });
        }
    });
    $doc.on('change', '.presets-select', function (e) {
        const $t = $(this);
        const val = $t.val();
        const $form = $doc.find('.create-form');
        $form.trigger('reset');
        $form.find('.autocomplete-value').val('');
        setProjectQuillText('Опис');
        const $select = $form.find('select.selectric');
        $select.each(function () {
            $(this).prop('selectedIndex', 0).selectric('refresh');
        });
        if (val === undefined || val === "") return;
        showPreloader();
        $.ajax({
            type: "POST",
            url: adminAjax,
            data: {
                action: 'get_preset_data',
                id: val,
            },
        }).done(function (r) {
            if (r) {
                if (isJsonString(r)) {
                    let res = JSON.parse(r);

                    if (res) {
                        if (res.title !== undefined) $form.find('input[name="title"]').val(res.title);
                        if (res.text !== undefined) {
                            let html = res.text;
                            let $html = $('<div>').html(html);
                            $html.find('.invite').removeAttr('data-user-id');
                            $html.find('.invite__image').remove();
                            html = $html.html();
                            html = html.replaceAll('<span class="invite">', '@[');
                            html = html.replaceAll('</span>', ']@');
                            $doc.find('#project-editor').html(html);
                            $doc.find('.ql-toolbar').remove();
                            $doc.find('#project-editor').removeClass('ql-container').removeClass('ql-snow');

                            $form.find('#project-editor').html(html);
                            $form.find('.value-field').val(html);

                            initProjectQuill();
                        }
                        if (res.parent_id !== undefined) {
                            $form.find('.autocomplete-value').val(res.parent_id);
                            $form.find('.autocomplete-input').val(res.parent_title);
                        }
                        if (res.observers !== undefined) {
                            const $select = $form.find('select[name="observers[]"]');
                            if ($select.length > 0) {
                                $select.find('option').removeAttr('selected');
                                res.observers.forEach(function (item) {
                                    $select.find(`option[value="${item}"]`).attr('selected', 'selected');
                                });
                                $select.selectric('refresh');
                            }
                        }
                        if (res.performers !== undefined) {
                            const $select = $form.find('select[name="responsible[]"]');
                            if ($select.length > 0) {
                                $select.find('option').removeAttr('selected');
                                res.performers.forEach(function (item) {
                                    $select.find(`option[value="${item}"]`).attr('selected', 'selected');
                                });
                                $select.selectric('refresh');
                            }
                        }
                        if (res.tags !== undefined) {
                            const $select = $form.find('select[name="tags[]"]');
                            if ($select.length > 0) {
                                $select.find('option').removeAttr('selected');
                                res.tags.forEach(function (item) {
                                    $select.find(`option[value="${item}"]`).attr('selected', 'selected');
                                });
                                $select.selectric('refresh');
                            }
                        }
                        if (res.status !== undefined) {
                            const $select = $form.find('select[name="post_status"]');
                            if ($select.length > 0) {
                                $select.find('option').removeAttr('selected');
                                $select.find(`option[value="${res.status}"]`).attr('selected', 'selected');
                                $select.selectric('refresh');
                            }
                        }
                    }
                } else {
                    showMassage(r);
                }
            }
            hidePreloader();
        });
    });
    $doc.on('click', '.deleting-project', function (e) {
        e.preventDefault();
        let $t = $(this);
        const $wrapper = $t.closest('.project-item');
        const id = $t.attr('data-id');
        if (id === undefined) return;
        showPreloader();
        $.ajax({
            type: "POST",
            url: adminAjax,
            data: {
                action: 'remove_project', id
            },
        }).done(function (r) {
            hidePreloader();
            if (r) {
                if (isJsonString(r)) {
                    const res = JSON.parse(r);
                    if (res) {
                        if (res.msg !== undefined && res.msg !== '') {
                            showMassage(res.msg);
                        }
                        if (res.deleted !== undefined) {
                            $doc.find('.project-item[data-id="' + res.deleted + '"]').remove();
                            closeWindow($doc.find('.deleting-window'));
                        }
                    }
                } else {
                    showMassage(r);
                }
            }
        });
    });
});

function appendContainer(href, $button = false) {
    let $container = $doc.find('.container-js');
    let $pagination = $doc.find('.pagination-js');
    if($button){
        const $section = $button.closest('section');
        $container = $section.find('.container-js');
        $pagination = $section.find('.pagination-js');
    }
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
        loading = false;
        if (r) {
            let $requestBody = $(parser.parseFromString(r, "text/html"));
            $container.append($requestBody.find('.container-js').html());
            $pagination.html($requestBody.find('.pagination-js').html());
            initPlugins();
        } else {
            $pagination.html('');
        }
        const commentObserver = new CommentObserver();
        hidePreloader();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        appendContainer(href, $button);
    });
}

export function renderContainer(url) {
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
        loading = false;
        if (r) {
            let $requestBody = $(parser.parseFromString(r, "text/html"));
            $container.html($requestBody.find('.container-js').html());
            $pagination.html($requestBody.find('.pagination-js').html());
            $postsCounter.html($requestBody.find('.found-posts').html());
            initPlugins();
        } else {
            $pagination.html('');
        }
        const commentObserver = new CommentObserver();
        hidePreloader();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        renderContainer(url);
    });
}

