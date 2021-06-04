<?php


namespace Sirius\seo;


use DOMDocument;
use Sirius\Container;
use Sirius\utils\JsonParser;

class SitemapManager
{
    public const ROUTER_CONFIG = ROOT_DIR . '/config/routes.json';
    public const SITEMAP_PATH = ROOT_DIR . '/public/';

    private ?SeoManager  $seoManager= null;
    private ?array $sitemapData = [];
    private string $sitemapName = 'sitemap.xml';
    private ?Container $container;
    private ?string $host;

    public function __construct(string $host = null, string $sitemapName = null)
    {
        $this->seoManager =  new SeoManager();
        $this->sitemapData = $this->seoManager->getSitemapData();
        $this->sitemapName = $this->sitemapData['defaultName'] . '.xml';
        $this->container = Container::getInstance();

        if (!$host) {
            $this->host = $this->seoManager->getHost();
        }

        if($sitemapName) {
            $this->sitemapName = $sitemapName . '.xml';
        }
    }

    public function generateStaticRoutes()
    {
        $xmlFile = $this->getSitemapFile();


        $xmlFile->formatOutput = true;
        $xmlFile->preserveWhiteSpace = false;
        $parsedRoutes = JsonParser::parseFile(self::ROUTER_CONFIG);
        $mainNode = $xmlFile->getElementsByTagName('urlset')[0];
        foreach ($parsedRoutes as $route) {
            if (preg_match('/{(.*?)}/', $route['path'])) {
                continue;
            }
            $route['path'] = $this->host.$route['path'];
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

        $xmlFile->save(self::SITEMAP_PATH . $this->sitemapName);
    }

    public function generateEntityRoutes()
    {
        if (null === $entityManager = $this->container->getEntityManager()) {
            return false;
        }

        $xmlFile = $this->getSitemapFile();
        $domPath = new \DOMXPath($xmlFile);

        $mainNode = $xmlFile->getElementsByTagName('urlset')[0];


        foreach ($this->sitemapData['routes'] as $route) {
            if (!preg_match_all('/{(.*?)}/', $route['path'], $matches)) {
                continue;
            }

            if (count($matches[1]) !== count($route['entities'])) {
                continue;
            }

            for ($i = 0 ; $i < count($matches[1]); $i++) {
                $entityData = $entityManager->getEntityData($route['entities'][$i]);
                $repository = $entityData['repository'];
                $repository = new $repository();
                $entities = $repository->findAll();
                foreach ($entities as $entity) {
                    $modify = false;
                    $currentPath = $this->host.$route['path'];
                    $method = 'get'.ucfirst($matches[1][$i]);
                    $match = $matches[1][$i];
                    $currentPath = preg_replace("/{($match)}/", $entity->$method(), $currentPath, 1);

                    foreach ($xmlFile->getElementsByTagName('url') as $itemTest) {
                        if ($entityData['name'] !== $itemTest->getAttribute('type')) {
                            continue;
                        }
                        if ($entity->getId() == $itemTest->getAttribute('id')) {
                            $loc = $itemTest->getElementsByTagName('loc')[0];
                            if ($currentPath !== $loc->nodeValue) {
                                $loc->nodeValue = $currentPath;
                            }

                            continue 2;
                        }
                    }
                    $nodeField = $xmlFile->createElement('url');
                    $nodeField->setAttribute("type", $entityData['name']);
                    $nodeField->setAttribute("id", $entity->getId());
                    $locationField = $xmlFile->createElement('loc', $currentPath);
                    $nodeField->appendChild($locationField);
                    $mainNode->appendChild($nodeField);
                }

            }
        }

        $strXml = $xmlFile->saveXML();
        $xmlFile->formatOutput = true;
        $xmlFile->preserveWhiteSpace = false;
        $xmlFile->loadXML($strXml);
        $xmlFile->save(self::SITEMAP_PATH . $this->sitemapName);

    }

    private function setNewSitemap(): DOMDocument
    {
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->formatOutput = true;
        $doc->preserveWhiteSpace = false;
        $mainNode = $doc->createElement('urlset', );
        $urlset = $doc->appendChild($mainNode);
        $urlset->setAttribute("xmlns", 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urlset->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
        $urlset->setAttribute("xsi:schemaLocation", "http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xss");

        return $doc;
    }

    private function getSitemapFile()
    {
        if (file_exists(self::SITEMAP_PATH . $this->sitemapName)) {
            $doc = new DOMDocument('1.0', 'utf-8');
            return $doc::load (self::SITEMAP_PATH . $this->sitemapName);
        }

        return $this->setNewSitemap();
    }

    public function addToSitemap(string $url, int $priority)
    {

    }
}