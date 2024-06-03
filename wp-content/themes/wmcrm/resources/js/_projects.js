import {showPreloader, hidePreloader} from "./_helpers";
import CommentObserver from "./CommentObserver";

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

