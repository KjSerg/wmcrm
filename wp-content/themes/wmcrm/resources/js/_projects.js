import 'selectric';
import {showPreloader, hidePreloader, isJsonString, showMassage} from "./_helpers";
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
});

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
        const commentObserver = new CommentObserver();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        appendContainer(href);
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
        const commentObserver = new CommentObserver();
    }).fail(function (jqXHR, textStatus, errorThrown) {
        renderContainer(url);
    });
}

