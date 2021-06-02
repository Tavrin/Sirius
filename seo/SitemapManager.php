<?php


namespace Sirius\seo;


use DOMDocument;
use Sirius\utils\JsonParser;

class SitemapManager
{
    public const ROUTER_CONFIG = ROOT_DIR . '/config/routes.json';
    public const SITEMAP_PATH = ROOT_DIR . '/public/sitemaptest.xml';

    private ?SeoManager  $seoManager= null;

    public function __construct()
    {
        $this->seoManager =  new SeoManager();
    }

    public function generateStaticRoutes(string $host = null)
    {
        if (!$host) {
            $host = $this->seoManager->getHost();
        }
        if (false === $xmlFile = $this->getSitemapFile()) {
            $xmlFile = $this->setNewSitemap();
        }

        $xmlFile->formatOutput = true;
        $xmlFile->preserveWhiteSpace = false;
        $parsedRoutes = JsonParser::parseFile(self::ROUTER_CONFIG);
        $mainNode = $xmlFile->getElementsByTagName('urlset')[0];
        foreach ($parsedRoutes as $route) {
            if (preg_match('/{(.*?)}/', $route['path'])) {
                continue;
            }
            $route['path'] = $host.$route['path'];
            foreach ($xmlFile->getElementsByTagName('url')  as $url) {
                if ($route['path'] === $url->getElementsByTagName('loc')[0]->nodeValue) {
                    continue 2;
                }
            }

            $nodeField = $xmlFile->createElement('url');
            $locationField = $xmlFile->createElement('loc', $route['path']);
            $nodeField->appendChild($locationField);
            $mainNode->appendChild($nodeField);
        }

        $xmlFile->save(self::SITEMAP_PATH);

    }

    private function setNewSitemap(): DOMDocument
    {
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->formatOutput = true;
        $doc->preserveWhiteSpace = false;
        $mainNode = $doc->createElement('urlset');
        $doc->appendChild($mainNode);

        return $doc;
    }

    private function getSitemapFile()
    {
        if (file_exists(self::SITEMAP_PATH)) {
            $doc = new DOMDocument('1.0', 'utf-8');
            return $doc::load (self::SITEMAP_PATH);
        }

        return false;
    }

    public function addToSitemap(string $url, int $priority)
    {

    }
}