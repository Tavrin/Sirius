<?php


namespace Sirius\seo;


use Sirius\utils\JsonParser;

class SeoManager
{
    protected const SEO_CONFIG = ROOT_DIR . '/config/seo.json';

    public ?array $seoConfig = [];

    public function __construct()
    {
        $this->seoConfig = $this->setSeoConfigData();
    }

    private function setSeoConfigData()
    {
        $config = null;
        if (file_exists(self::SEO_CONFIG)) {
            $config = JsonParser::parseFile(self::SEO_CONFIG);
        }

        return $config;
    }

    /**
     * @return array|mixed|null
     */
    public function getSeoConfig()
    {
        return $this->seoConfig;
    }
}