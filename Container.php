<?php


namespace Sirius;


use Sirius\utils\JsonParser;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Container
{
    protected const TEMPLATES_DIR = ROOT_DIR . '/templates/';
    protected const SERVICES = ROOT_DIR . '\config\services.json';


    private static ?Container $instance = null;
    private ?array $services = null;

    private ?Environment $twig = null;

    private function __construct()
    {
        $this->services = JsonParser::parseFile(self::SERVICES);
        $this->setTwig();
    }

    public static function getInstance():Container
    {
        if (self::$instance == null)
        {
            self::$instance = new Container();
        }

        return self::$instance;
    }

    private function setTwig()
    {
        $loader = new FilesystemLoader(self::TEMPLATES_DIR);
        if (isset($_ENV['ENV']) && $_ENV['ENV'] === 'dev') {
            $this->twig = new Environment($loader, [
                'debug' => true
            ]);
            $this->twig->addExtension(new \Twig\Extension\DebugExtension());
        } else {
            $this->twig = new Environment($loader, [
                'debug' => false
            ]);
        }

        $this->setTwigServices();
    }

    private function setTwigServices()
    {
        if (isset ($this->services['twig']['extensions'])) {
            foreach ($this->services['twig']['extensions'] as $extension) {
                $class = $extension['class'];
                $this->twig->addExtension(new $class());
            }
        }
    }

    /**
     * @return Environment|null
     */
    public function getTwig(): ?Environment
    {
        return $this->twig;
    }
}