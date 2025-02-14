<?php

namespace FOF\SVGFAVICON\Services;

class ManifestService
{
    private array $options;
    private array $manifest;

    public function __construct(array $options) //key, value, svg
    {
        $this->options = $options;
    }

    public function removeManifest(): ManifestService
    {
        $upload_dir = wp_upload_dir();

        $base_dir = $upload_dir['basedir'] . '/';

        $file_svg_favicon_manifest = $base_dir . 'svg_favicon/manifest.json';

        if (file_exists($file_svg_favicon_manifest)) {
            wp_delete_file($file_svg_favicon_manifest);
        }

        return $this;
    }

    public function generateManifest(string $svg = ''): ManifestService
    {
        foreach ($this->options as $key => $value) {
           $this->createManifest($key, $value, $svg);
        }

        return $this;
    }

    public function getManifest(): array
    {
        return $this->manifest;
    }

    public function createManifest($key = '', $value = '', $svg = ''): ManifestService
    {
        $this->manifest = [
            "name" => empty($this->options['name']) ? get_bloginfo('name') : $this->options['name'],
            "short_name" => empty($this->options['name']) ? get_bloginfo('name') : $this->options['name'],
            "icons" => [
                "src" => empty($svg) ?? $this->options['svg_favicon'] ?? '',
                "sizes" => "512x512"
            ],
            "background_color" => empty($this->options['background_color']) ? '#ffffff' : $this->options['background_color'],
            "theme_color" => empty($this->options['theme_color']) ? '#ffffff' : $this->options['theme_color'],
            "display" => "fullscreen"
        ];

        if (isset($this->manifest[$key])) {
            $this->manifest[$key] = $value;
        }

        if ($key == 'name') {
            $this->manifest['short_name'] = $value;
        }

        if (!empty($svg)) {
            $this->manifest['icons']['src'] = $svg;
        }

        return $this;
    }

    public function writeManifest()
    {
        $upload_dir = wp_upload_dir();

        $base_dir = $upload_dir['basedir'] . '/';
        $file_dir = $base_dir . 'svg_favicon';
        $file_svg_manifest = $base_dir . 'svg_favicon/manifest.json';

        $manifest = json_encode($this->manifest);

        if (!is_dir($file_dir)) {
            wp_mkdir_p($file_dir);
        }

        if (file_exists($file_svg_manifest)) {
            wp_delete_file($file_svg_manifest);
        }

        file_put_contents($file_svg_manifest, $manifest, FILE_APPEND);
    }
    
    public function handleManifest(string $svg = ''): array
    {
        return $this->removeManifest()
            ->generateManifest($svg)
            ->getManifest()
        ;
    }
}