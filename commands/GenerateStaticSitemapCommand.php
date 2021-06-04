<?php


namespace Sirius\commands;


use Sirius\seo\SitemapManager;

class GenerateStaticSitemapCommand extends Command
{
    public function configure()
    {
        $this->setName('GenerateSitemap')
            ->setAlias('sitemap:g')
            ->addArgument('type', 'Specify a type [static,entity,all].')
            ->addArgument('hostname', 'Specify a hostname.')
            ->addArgument('sitemap_name', 'Specify a hostname.')
            ->setDescription('Generates a sitemap for your static routes');
    }

    public function execute()
    {
        $type = $this->arguments['type']['value'];
        if(!$type) {
            $type = 'static';
        }


        $host = $this->arguments['hostname']['value'] ?? null;
        $sitemapManager = new SitemapManager($host, $this->arguments['sitemap_name']['value']);
        echo "Generating the static sitemap" . PHP_EOL;

        if ('static' === $type || 'all' === $type) {
            $sitemapManager->generateStaticRoutes();
        }
        if ('entity' === $type || 'all' === $type) {
            $sitemapManager->generateEntityRoutes();
        }
        echo "Sitemap generated" . PHP_EOL;
    }
}