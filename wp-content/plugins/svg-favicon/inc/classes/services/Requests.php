<?php

namespace FOF\SVGFAVICON\Services;

use FOF\SVGFAVICON\Models\PluginInfo;
use FOF\SVGFAVICON\Tools\Ajax;
use FOF\SVGFAVICON\Tools\Utils;

class Requests
{
    private Ajax $ajax;
    private PluginInfo $plugin_info;
    private SVGService $SVGService;
    private Utils $utils;
    private OptionsService $optionsService;

    public function __construct(Ajax $ajax, PluginInfo $plugin_info, Utils $utils, SVGService $SVGService)
    {
        $this->ajax = $ajax;
        $this->plugin_info = $plugin_info;
        $this->SVGService = $SVGService;
        $this->utils = $utils;
        $this->optionsService = new OptionsService();
    }

    public function saveImage()
    {
        check_ajax_referer($this->plugin_info->domain, $this->plugin_info->query_arg);

        $payload = $this->setPayload();

        $result = $this->SVGService->svgToImage($payload['pngDataUrl']);

        wp_die();
    }

    public function removeSVG()
    {
        check_ajax_referer($this->plugin_info->domain, $this->plugin_info->query_arg);

        $payload = $this->setPayload();

        $options = $this->optionsService->getOptions();

        $this->SVGService->removeSvg('svg_favicon');

        if( $payload['key'] === 'svg_favicon' ) {
            $options['manifest'] = "";
            ( new ManifestService($options) )->removeManifest();
        }

        $options[$payload['key']] = "";

        update_option($this->plugin_info->option, $options);

        $this->ajax->jsonHeader($options);

        wp_die();
    }

    public function saveData()
    {
        check_ajax_referer($this->plugin_info->domain, $this->plugin_info->query_arg);

        $payload = $this->setPayload();

        $updatedForm = $payload['form'][$payload['selected']['tab']];
        $uploadFields = $this->utils->extractKeys($updatedForm, ['mask_icon', 'svg_favicon']);

        $options = $this->optionsService->getOptions();

        $newOptions = array_merge($options, $updatedForm);

        $manifest = new ManifestService($newOptions);

        foreach ($uploadFields as $key => $value )
        {
            $svgUrl = $this->SVGService->uploadSVG($key, $value);
            $newOptions[$key] = $svgUrl;

            if( $key === 'svg_favicon') {
                $manifest->handleManifest($svgUrl);
                $manifest->writeManifest();
            }
        }

        $newOptions['date'] = date( DATE_ATOM, time() );

        update_option($this->plugin_info->option, $newOptions);

        $base64SVGs = array_filter( $uploadFields, fn($uploadField) => $this->SVGService->isBase64SVG($uploadField) );

        $this->ajax->jsonHeader($base64SVGs);

        wp_die();
    }

    public function fetchData()
    {
        check_ajax_referer($this->plugin_info->domain, $this->plugin_info->query_arg);

        $options = $this->optionsService->getOptions();

        $this->ajax->jsonHeader($options);
        wp_die();
    }

    private function setPayload() {
        $payload = is_string($_POST['payload']) ? json_decode(stripslashes($_POST['payload']), true) : $_POST['payload'];

        return $this->utils->arrayMapRecursive('sanitize_text_field', $payload);
    }
}