<?php


namespace Sirius\commands;


use Sirius\seo\SitemapManager;

class GenerateStaticSitemapCommand extends Command
{
    public function configure()
    {
        $this->setName('GenerateSitemap')
            ->setAlias('sitemap:g')
            ->addArgument('hostname', 'Specify a hostname.')
            ->setDescription('Generates a sitemap for your static routes');
    }

    public function execute()
    {

        $host = $this->arguments['hostname']['value'] ?? $host = null;
        $sitemapManager = new SitemapManager();
        echo "Generating the static sitemap" . PHP_EOL;

        $sitemapManager->generateStaticRoutes($host);
        echo "Sitemap generated" . PHP_EOL;
    }
}