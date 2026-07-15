<?php

declare(strict_types=1);

namespace Webkul\OpenGraphMeta\Services;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

final class OpenGraphMetaService
{
    private ?string $title = null;

    private ?string $description = null;

    private ?string $image = null;

    private ?string $url = null;

    private bool $hasExplicit = false;

    public function __construct()
    {
        $this->url = Request::fullUrl();

        try {
            $channel = core()->getCurrentChannel();
        } catch (\Throwable $e) {
            $channel = null;
        }

        try {
            $settings = core()->getBackEndSettings();
        } catch (\Throwable $e) {
            $settings = [];
        }

        $this->title = $settings['meta_title'] ?? ($channel->name ?? config('app.name'));
        $this->description = $settings['meta_description'] ?? ($channel->home_seo['meta_description'] ?? '');
        $this->image = $channel->logo_url ?? $channel->logo ?? $settings['logo'] ?? null;
    }

    public function set(?string $title = null, ?string $description = null, ?string $image = null): void
    {
        if ($title !== null) {
            $this->title = strip_tags($title);
        }

        if ($description !== null) {
            $clean = strip_tags($description);
            $this->description = Str::limit($clean, 160, '');
        }

        if ($image !== null) {
            $this->image = strip_tags($image);
        }

        if ($title !== null || $description !== null || $image !== null) {
            $this->hasExplicit = true;
        }
    }

    public function setFromEntity(mixed $entity): void
    {
        if (is_array($entity)) {
            $title = $entity['meta_title'] ?? $entity['title'] ?? $entity['name'] ?? null;
            $description = $entity['meta_description'] ?? $entity['description'] ?? null;
            $image = $entity['image_url'] ?? $entity['image'] ?? $entity['base_image_url'] ?? $entity['logo_url'] ?? null;

            $this->set($title, $description, $image);

            return;
        }

        if (! is_object($entity)) {
            return;
        }

        $title = null;
        $description = null;
        $image = null;

        foreach (['meta_title', 'title', 'name'] as $key) {
            if (property_exists($entity, $key) && ! empty($entity->{$key})) {
                $title = $entity->{$key};
                break;
            }

            if (method_exists($entity, 'getAttribute')) {
                try {
                    $value = $entity->getAttribute($key);
                    if (! empty($value)) {
                        $title = $value;
                        break;
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        }

        foreach (['meta_description', 'description'] as $key) {
            if (property_exists($entity, $key) && ! empty($entity->{$key})) {
                $description = $entity->{$key};
                break;
            }

            if (method_exists($entity, 'getAttribute')) {
                try {
                    $value = $entity->getAttribute($key);
                    if (! empty($value)) {
                        $description = $value;
                        break;
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        }

        foreach (['image_url', 'image', 'base_image_url', 'logo_url'] as $key) {
            if (property_exists($entity, $key) && ! empty($entity->{$key})) {
                $image = $entity->{$key};
                break;
            }

            if (method_exists($entity, 'getAttribute')) {
                try {
                    $value = $entity->getAttribute($key);
                    if (! empty($value)) {
                        $image = $value;
                        break;
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        }

        $this->set($title, $description, $image);
    }

    public function render(): string
    {
        $title = e($this->title ?? '');
        $description = e($this->description ?? '');
        $image = e($this->image ?? '');
        $url = e($this->url ?? url()->to('/'));

        $html = [];

        $fbAppId = config('opengraph.fb_app_id');
        if (! empty($fbAppId)) {
            $html[] = '<meta property="fb:app_id" content="' . e($fbAppId) . '">';
        }

        if ($this->hasExplicit) {
            $html[] = '<meta property="og:type" content="product">';
            $html[] = '<meta property="og:url" content="' . $url . '">';
            $html[] = '<meta property="og:title" content="' . $title . '">';
            $html[] = '<meta property="og:description" content="' . $description . '">';

            if (! empty($image)) {
                $html[] = '<meta property="og:image" content="' . $image . '">';
            }
        } elseif (config('opengraph.render_fallback', true)) {
            $html[] = '<meta property="og:type" content="website">';
            $html[] = '<meta property="og:url" content="' . $url . '">';
            $html[] = '<meta property="og:title" content="' . $title . '">';
            $html[] = '<meta property="og:description" content="' . $description . '">';

            if (! empty($image)) {
                $html[] = '<meta property="og:image" content="' . $image . '">';
            }
        }

        $html[] = '<meta name="twitter:card" content="summary_large_image">';
        $html[] = '<meta name="twitter:url" content="' . $url . '">';
        $html[] = '<meta name="twitter:title" content="' . $title . '">';
        $html[] = '<meta name="twitter:description" content="' . $description . '">';

        if (! empty($image)) {
            $html[] = '<meta name="twitter:image" content="' . $image . '">';
        }

        return implode("\n", $html);
    }
}
