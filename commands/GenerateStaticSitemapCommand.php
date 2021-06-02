<?php


namespace Sirius\commands;


use Sirius\seo\SitemapManager;

class GenerateStaticSitemapCommand extends Command
{
    public function configure()
    {
        $this->setName('GenerateSitemap')
            ->setAlias('sitemap:g')
            ->setDescription('Generates a sitemap for your static routes');
    }

    public function execute()
    {
        $sitemapManager = new SitemapManager();
        echo "Generating the static sitemap" . PHP_EOL;

        $sitemapManager->generateStaticRoutes();
        echo "Sitemap generated" . PHP_EOL;
    }
}