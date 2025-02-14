<?php

namespace FOF\SVGFAVICON\Services;

use enshrined\svgSanitize\Sanitizer;
use FOF\SVGFAVICON\Models\PluginInfo;
use FOF\SVGFAVICON\Tools\SVGAllowedTags;

class SVGService
{
    private PluginInfo $pluginInfo;
    private string $svg64;

    public function __construct(PluginInfo $pluginInfo)
    {
        $this->pluginInfo = $pluginInfo;
    }

    public function uploadSVG($key, $svg)
    {
        $this->setSvg64($svg);
        return $this->handleSvg($svg,$key);
    }

    public function removeSvg($type)
    {
        if (!isset($this->pluginInfo->svg_map[$type])) {
            return;
        }

        $upload_dir = wp_upload_dir();

        $base_dir = $upload_dir['basedir'] . '/';

        $file_svg_favicon = $base_dir . 'svg_favicon/' . $this->pluginInfo->svg_map[$type] . '.svg';

        if (file_exists($file_svg_favicon)) {
            wp_delete_file($file_svg_favicon);
        }

        return null;
    }

    public function svgToImage(string $img): array {

        $img = str_replace('data:image/png;base64,', '', $img);//replace the name of image
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);

        $paths = $this->prepForUpload('apple-touch-icon', 'png');

        return [
            'status' => $this->upload($paths, $data),
            'link' => $paths['base_url'],
        ];
    }

    private function upload(array $paths, $data) {

        if (!is_dir($paths['file_dir'])) {
            wp_mkdir_p($paths['file_dir']);
        }

        if (file_exists($paths['base_dir'])) {
            wp_delete_file($paths['base_dir']);
        }

        return file_put_contents($paths['base_dir'], $data, FILE_APPEND);
    }

    private function prepForUpload(string $file_name, string $ext): array
    {
        $upload_dir = wp_upload_dir();

        $base_dir = $upload_dir['basedir'].'/';
        $base_url = $upload_dir['baseurl'].'/';

        return [
            'file_dir' => $base_dir . 'svg_favicon',
            'base_dir' => $base_dir . "svg_favicon/$file_name.$ext",
            'base_url' => $base_url."svg_favicon/$file_name.$ext",
        ];
    }

    private function setSvg64(string $svg64)
    {
        $this->svg64 = $svg64;
    }

    private function decodeChunk($svg)
    {
        $svg = explode(';base64,', $svg);

        if (!is_array($svg) || !isset($svg[1])) {
            return false;
        }

        $svg = base64_decode($svg[1]);

        if (!$svg) {
            return false;
        }

        return $svg;
    }

    private function handleSvg(string $svg, string $key = 'svg_favicon')
    {
        if (filter_var($svg, FILTER_VALIDATE_URL)) {
            return $svg;
        }

        $this->removeSvg($key);

        $svg = $this->decodeChunk($svg);

        if ($svg === false) {
            return false;
        }

        $upload_dir = wp_upload_dir();

        $base_dir = $upload_dir['basedir'] . '/';
        $base_url = $upload_dir['baseurl'] . '/';

        $file_dir = $base_dir . 'svg_favicon';
        $file_svg_favicon = $base_dir . 'svg_favicon/' . $this->pluginInfo->svg_map[$key] . '.svg';
        $file_svg_favicon_url = $base_url . 'svg_favicon/' . $this->pluginInfo->svg_map[$key] . '.svg';

        if (!is_dir($file_dir)) {
            wp_mkdir_p($file_dir);
        }

        if (file_exists($file_svg_favicon)) {
            wp_delete_file($file_svg_favicon);
        }

        $sanitizer = new Sanitizer();

        $sanitizer->setAllowedTags(new SVGAllowedTags());
        $svg = $sanitizer->sanitize($svg);

        if (!$svg) {
            return null;
        }

        $results = file_put_contents($file_svg_favicon, $svg, FILE_APPEND); //TODO: Collect results

        return $file_svg_favicon_url;
    }

    function isBase64SVG($base64String): bool
    {
        // Define the prefix to look for
        $prefix = 'data:image/svg+xml;base64,';

        // Check if the string starts with the correct prefix
        if (strpos($base64String, $prefix) !== 0) {
            return false;
        }

        // Remove the prefix
        $base64Data = substr($base64String, strlen($prefix));

        // Decode the base64 string
        $decodedString = base64_decode($base64Data, true);

        // Check if the decoding was successful
        if ($decodedString === false) {
            return false;
        }

        // Check if the decoded string contains the SVG XML tag
        $isSVG = strpos($decodedString, '<svg') !== false && strpos($decodedString, '</svg>') !== false;

        return $isSVG;
    }
}