import Quill from './_quill';
import 'selectric';

let doc = document;
let $doc = $(doc);
let load = false;
let loading = false;
let quill = null;
let quillProject = null;

$(document).ready(function () {
    $doc.mouseup(function (e) {
        var div = $(".text-editor-list");
        if (!div.is(e.target)
            && div.has(e.target).length === 0) {
            div.closest('form').removeClass('open-users-select');
        }
    });
    $doc.on('change', '.select-user-quill-js', function (e) {
        let $t = $(this);
        let val = $t.val();
        let name = $t.find('option:selected').text().trim();
        $doc.find('.open-users-select').removeClass('open-users-select');

        if ($t.closest('form').hasClass('create-form')) {
            let currentIndex = quillProject.getSelection().index;
            quillProject.insertText(currentIndex, '[' + name + ']@');
        } else {
            let currentIndex = quill.getSelection().index;
            quill.insertText(currentIndex, '[' + name + ']@');
        }
        $t.prop('selectedIndex', 0).selectric('refresh');
    });
    $doc.on('keypress', '#editor, #project-editor', function (e) {
        let currentTarget = e.currentTarget;
        let $currentTarget = $(currentTarget);
        let $form = $currentTarget.closest('form');
        if (e.key === '@') {
            $form.addClass('open-users-select');
            $form.find('.select-user-quill-js').selectric('open');
        }
    });
    $doc.on('keydown', '#editor, #project-editor', function (event) {
        if (event.ctrlKey && event.key === 'Enter') {
            $(this).closest('form').trigger('submit');
        }
    });
    $doc.on('click', '.comment-change-js', function (e) {
        e.preventDefault();
        let $this = $(this);
        let id = $this.attr('data-id');
        let $wrapper = $this.closest('.comment');
        let $text = $wrapper.find('.comment-content');
        let html = $text.html();
        let $html = $('<div>').html(html);
        $html.find('.invite').removeAttr('data-user-id');
        $html.find('.invite__image').remove();
        html = $html.html();
        html = html.replaceAll('<span class="invite">', '@[');
        html = html.replaceAll('</span>', ']@');
        $doc.find('#editor').html(html);
        $doc.find('.ql-toolbar').remove();
        $doc.find('#editor').removeClass('ql-container').removeClass('ql-snow');
        $doc.find('.comment-field-id').val(id);
        $doc.find('.value-field').val(html);
        initQuill(false);
        if($this.closest('.window-main').length > 0){
            $('.window-main').animate({
                scrollTop: $doc.find('#editor').offset().top - ($doc.find('.header').outerHeight() + 50)
            }, 400);
        }else {
            $('html,body').animate({
                scrollTop: $doc.find('#editor').offset().top - ($doc.find('.header').outerHeight() + 50)
            }, 400);
        }
    });
    initQuill();
    initProjectQuill();

});

export function setQiullText(text = '') {
    if (quill !== null) quill.setText(text);
}

export function setProjectQuillText(text = '') {
    if (quillProject !== null) quillProject.setText(text);
}

export function initProjectQuill() {
    const $editor = $doc.find('#project-editor');
    if ($editor.length === 0) return;
    const projectID = $editor.attr('data-project-id');
    if (projectID !== undefined) {
        let text = localStorage.getItem('comment-for-project-' + projectID);
        if (text !== null) {
            if (text.trim().length !== 0) {

                $editor.closest('form').find('.value-field').val(text);
                $editor.html(text);
            }
        }

    }
    quillProject = new Quill('#project-editor', {
        theme: 'snow',
    });
    quillProject.on('text-change', (delta, oldDelta, source) => {
        let val = quillProject.getSemanticHTML();
        $editor.closest('form').find('.value-field').val(val);
        if ($editor.attr('data-project-id') !== undefined) {
            if (val && val !== '<p></p>') {
                localStorage.setItem('comment-for-project-' + $editor.attr('data-project-id'), val);
            } else {
                localStorage.removeItem('comment-for-project-' + $editor.attr('data-project-id'));
            }
        }
    });

}

export default function initQuill(checkHTML = true) {
    if (quill !== null) quill = null;
    const $editor = $doc.find('#editor');
    if ($editor.length === 0) return;
    if ($editor.html().trim().length === 0) {
        const projectID = $editor.attr('data-project-id');
        if (projectID !== undefined) {
            let text = localStorage.getItem('comment-for-project-' + projectID);
            if (text !== null) {
                if (text.trim().length !== 0) {

                    $editor.closest('form').find('.value-field').val(text);
                    $editor.html(text);
                }
            }

        }
    }

    if (checkHTML) {
        $editor.find('span.invite').each(function () {
            let text = $(this).text();
            $(this).replaceWith('@[' + text + ']@');
        });
        let html = $editor.html().trim();
        if (html.length > 0) {
            $editor.html(html);
            $editor.closest('form').find('.value-field').val(html);
        }
    }

    quill = new Quill('#editor', {
        theme: 'snow',
    });
    quill.on('text-change', (delta, oldDelta, source) => {
        let val = quill.getSemanticHTML();
        $editor.closest('form').find('.value-field').val(val);
        if ($editor.attr('data-project-id') !== undefined) {
            if (val && val !== '<p></p>') {
                localStorage.setItem('comment-for-project-' + $editor.attr('data-project-id'), val);
            } else {
                localStorage.removeItem('comment-for-project-' + $editor.attr('data-project-id'));
            }
        }
    });

}

