<?php

namespace FOF\SVGFAVICON\Services;

class i18n
{
    public function loadPluginTextdomain() {

        load_plugin_textdomain(
            'svg-favicon',
            false,
            SVGFAVICON_PLUGIN_PATH . '/languages/'
        );

    }
}