<?php

namespace Alpha\Support;

class StoreSettings
{
    private array $rawSettings;
    private int $languageId;
    private string $cachePath;

    public function __construct(array $rawSettings, int $languageId = 2)
    {
        $this->rawSettings = $rawSettings;
        $this->languageId = $languageId;
        $this->cachePath = DIR_STORAGE . 'cache/store_settings_formatted.json';
    }

    public function getFormattedSettings(bool $useCache = true): array
    {
        // Ensure cache directory exists
        $cacheDir = dirname($this->cachePath);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        if ($useCache && is_file($this->cachePath)) {
            $cached = json_decode(file_get_contents($this->cachePath), true);
            // Verify if cached language matches the current language
            if (isset($cached['_langId']) && $cached['_langId'] === $this->languageId) {
                unset($cached['_langId']);
                return $cached;
            }
        }

        $formatted = $this->transform();
        
        if ($useCache) {
            $cacheData = $formatted;
            $cacheData['_langId'] = $this->languageId;
            file_put_contents($this->cachePath, json_encode($cacheData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        return $formatted;
    }

    private function transform(): array
    {
        $store = [];

        // Map basic configs
        $store['name'] = $this->rawSettings['config_name'] ?? 'AG Sonhos e Construções';
        $store['email'] = $this->rawSettings['config_email'] ?? '';
        $store['telephone'] = $this->rawSettings['config_telephone'] ?? '';
        $store['address'] = $this->rawSettings['config_address'] ?? '';
        $store['geocode'] = $this->rawSettings['config_geocode'] ?? '';
        $store['open'] = $this->rawSettings['config_open'] ?? '';
        
        // Resolve logo & icon urls (usando caminhos relativos de mesma origem)
        if (!empty($this->rawSettings['config_logo'])) {
            $logoPath = $this->rawSettings['config_logo'];
            if (str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://') || str_starts_with($logoPath, '/')) {
                $store['logo'] = $logoPath;
            } elseif (str_starts_with($logoPath, 'image/')) {
                $store['logo'] = '/' . $logoPath;
            } else {
                $store['logo'] = '/image/' . $logoPath;
            }
        } else {
            $store['logo'] = '/image/logo.png';
        }

        if (!empty($this->rawSettings['config_icon'])) {
            $iconPath = $this->rawSettings['config_icon'];
            if (str_starts_with($iconPath, 'http://') || str_starts_with($iconPath, 'https://') || str_starts_with($iconPath, '/')) {
                $store['icon'] = $iconPath;
            } elseif (str_starts_with($iconPath, 'image/')) {
                $store['icon'] = '/' . $iconPath;
            } else {
                $store['icon'] = '/image/' . $iconPath;
            }
        } else {
            $store['icon'] = '/image/logo2.png';
        }

        // Parse localized descriptions (meta title, description, keywords)
        $metaTitle = '';
        $metaDescription = '';
        $metaKeyword = '';

        if (!empty($this->rawSettings['config_description'])) {
            $descArr = $this->rawSettings['config_description'];
            if (is_string($descArr)) {
                $descArr = json_decode($descArr, true);
            }
            if (is_array($descArr) && isset($descArr[$this->languageId])) {
                $metaTitle = $descArr[$this->languageId]['meta_title'] ?? '';
                $metaDescription = $descArr[$this->languageId]['meta_description'] ?? '';
                $metaKeyword = $descArr[$this->languageId]['meta_keyword'] ?? '';
            }
        }

        // Fallback to top-level keys if description wasn't parsed
        if (empty($metaTitle)) {
            $metaTitle = $this->rawSettings['config_meta_title'] ?? $store['name'];
        }
        if (empty($metaDescription)) {
            $metaDescription = $this->rawSettings['config_meta_description'] ?? '';
        }
        if (empty($metaKeyword)) {
            $metaKeyword = $this->rawSettings['config_meta_keyword'] ?? '';
        }

        $store['metaTitle'] = $metaTitle;
        $store['metaDescription'] = $metaDescription;
        $store['metaKeyword'] = $metaKeyword;

        // Custom socials or extra options
        $store['social'] = [
            'facebook' => $this->rawSettings['config_facebook'] ?? 'https://facebook.com',
            'instagram' => $this->rawSettings['config_instagram'] ?? 'https://instagram.com',
            'youtube' => $this->rawSettings['config_youtube'] ?? 'https://youtube.com',
            'whatsapp' => !empty($store['telephone']) ? 'https://wa.me/55' . preg_replace('/\D/', '', $store['telephone']) : 'https://wa.me/5500000000000'
        ];

        // i18next camelCase format helper
        $store['raw'] = [];
        foreach ($this->rawSettings as $key => $value) {
            if (strpos($key, 'config_') === 0) {
                $cleanKey = substr($key, 7); // Remove 'config_'
                $camelKey = $this->toCamelCase($cleanKey);
                $store['raw'][$camelKey] = $value;
            }
        }

        return $store;
    }

    private function toCamelCase(string $string): string
    {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        return lcfirst($str);
    }
}
