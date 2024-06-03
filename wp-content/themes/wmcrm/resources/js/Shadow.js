import {
    renderMain,
    showPreloader,
    openWindow,
    closeWindow,
    showMassage,
    isJsonString,
    hidePreloader
} from "./_helpers";

import {initTriggers, initPlugins} from './_js';

export default class Shadow {
    constructor() {
        this.$doc = $(document);
        this.loading = false;
        this.init();
    }

    openShadowModal(args) {
        const _this = this;
        const loading = _this.loading;
        const $doc = _this.$doc;
        let url = args.url;
        let $container = $doc.find('#shadow-js');
        if (url === undefined) return;
        if (loading) return;
        $doc.find('body').addClass('loading');
        _this.loading = true;
        showPreloader();
        let param = {
            type: 'GET',
            url: url
        };
        $.ajax(param).done(function (r) {
            hidePreloader();
            _this.loading = false;
            if (r) {
                const shadowHost = document.getElementById('shadow-js');
                const shadowRoot = shadowHost.attachShadow({mode: 'open'});
                shadowRoot.innerHTML = r;
                $doc.find('body').removeClass('loading');
                // openWindow($container);
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            _this.openShadowModal({
                url: url
            });
        });
    }

    init() {
        const _this = this;
        const $doc = _this.$doc;
        $doc.on('click', '.shadow-window-open', function (e) {
            e.preventDefault();
            const $t = $(this);
            const url = $t.attr('href');
            _this.openShadowModal({url});
        })
    }
}