import {renderMain, sendRequest, setCookie} from "./_helpers";

export default class Autocomplete {
    constructor() {
        this.$doc = $(document);
        this.init();
        this.listenEvents();
    }

    listenEvents() {
        const _this = this;
        const $doc = _this.$doc;
        $doc.on('click', '.autocomplete-item', function (e) {
            e.preventDefault();
            const $this = $(this);
            const val = $this.attr('data-val');
            const text = $this.text().trim();
            const $container = $this.closest('.autocomplete');
            const $wrapper = $container.find('.autocomplete-wrapper');
            $container.find('.autocomplete-text').val(text);
            $container.find('.autocomplete-value').val(val);
            $wrapper.hide();
        });
        $doc.mouseup(function (e) {
            let div = $(".autocomplete");
            if (!div.is(e.target)
                && div.has(e.target).length === 0) {
                div.find('.autocomplete-wrapper').hide();
            }
        });
    }

    init() {
        const _this = this;
        const $doc = _this.$doc;
        $doc.on('focus', '.autocomplete-input', function () {
            const $input = $(this);
            const val = $input.val();
            const $container = $input.closest('.autocomplete');
            const $wrapper = $container.find('.autocomplete-wrapper');
            if ($wrapper.find('.autocomplete-item').length > 0) {
                $wrapper.slideDown();
            }
        });
        $doc.on('input', '.autocomplete-input', function () {
            const $input = $(this);
            const val = $input.val();
            const $container = $input.closest('.autocomplete');
            const $wrapper = $container.find('.autocomplete-wrapper');
            const action = $input.attr('data-action');
            const exclude = $input.attr('data-exclude');
            let html = '';
            if (val.length < 3) {
                $wrapper.html(html);
                $wrapper.hide();
                return;
            }
            const args = {
                action: action,
                string: val
            };
            if (exclude !== undefined) {
                args.exclude = exclude;
            }
            if (action !== undefined) {
                sendRequest(adminAjax, args, 'POST').then((res) => {
                    $wrapper.html(html);
                    if (res) {
                        res.forEach(function (item) {
                            const val = item.val || false;
                            const name = item.name;
                            if (val) {
                                html += `<a href="#" data-val="${val}" class="autocomplete-item">${name}</a>`;
                            } else {
                                html += `<span class="autocomplete-item not-active">${name}</span>`;
                            }
                        });
                        $wrapper.html(html);
                        $wrapper.slideDown();
                    } else {
                        $wrapper.hide();
                    }
                });
            }
        });
    }
}