<?php


namespace Sirius\seo;


use DOMDocument;
use Sirius\utils\JsonParser;

class SitemapManager
{
    public const ROUTER_CONFIG = ROOT_DIR . '/config/routes.json';
    public const SITEMAP_PATH = ROOT_DIR . '/public/sitemap.xml';

    public function generateStaticRoutes()
    {
        $xmlFile = $this->getSitemapFile();
        $mainNode = $xmlFile->createElement('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">');
        $xmlFile->appendChild($mainNode);

        $parsedRoutes = JsonParser::parseFile(self::ROUTER_CONFIG);
        foreach ($parsedRoutes as $route) {
            if (preg_match('/{(.*?)}/', $route['path'])) {
                continue;
            }

            $nodeField = $xmlFile->createElement('url');
            $locationField = $xmlFile->createElement('loc', $route['path']);
            $nodeField->appendChild($locationField);
            $mainNode->appendChild($nodeField);
        }

        $xmlFile->save(self::SITEMAP_PATH);

    }

    private function getSitemapFile()
    {
        $doc = new DOMDocument('1.0', 'utf-8');
        if (file_exists(self::SITEMAP_PATH)) {
            dd($doc->load (self::SITEMAP_PATH));
            return $doc->load (self::SITEMAP_PATH);
        }

        return $doc;
    }

    public function addToSitemap(string $url, int $priority)
    {

    }
}